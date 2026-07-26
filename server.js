require('dotenv').config();
const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');

const JWT_SECRET = process.env.JWT_SECRET || 'taller_cardenas_secret_2026';

const app = express();
app.use(cors());
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ limit: '10mb', extended: true }));
app.use(express.static('public'));

const db = mysql.createConnection({
  host: process.env.DB_HOST,
  port: process.env.DB_PORT,
  user: process.env.DB_USER,
  password: process.env.DB_PASS,
  database: process.env.DB_NAME 
});

// Middleware para verificar token JWT
function verificarToken(req, res, next) {
  const auth = req.headers['authorization'];
  const token = auth && auth.split(' ')[1];
  if (!token) return res.status(401).json({ ok: false, error: 'Sin autorización' });
  try {
    req.usuario = jwt.verify(token, JWT_SECRET);
    next();
  } catch {
    res.status(401).json({ ok: false, error: 'Token inválido' });
  }
}

db.connect((err) => {
  if (err) { console.log('Error conectando:', err); return; }
  console.log('Conectado a la base de datos!');

  // Crear tabla perfil_taller si no existe
  db.query(`
    CREATE TABLE IF NOT EXISTS perfil_taller (
      id INT PRIMARY KEY DEFAULT 1,
      nombre VARCHAR(120),
      telefono VARCHAR(40),
      email VARCHAR(120),
      direccion VARCHAR(200),
      ciudad VARCHAR(80),
      ruc VARCHAR(20),
      horario VARCHAR(120)
    )
  `);
  // Insertar fila inicial si no existe
  db.query(`INSERT IGNORE INTO perfil_taller (id, nombre, ciudad) VALUES (1, 'Multiservicios Cárdenas', 'Chincha Alta')`);
  // Agregar columna imagen a vehiculos si no existe
  db.query(`ALTER TABLE vehiculos ADD COLUMN IF NOT EXISTS imagen LONGTEXT`);
  // Agregar columna costo a ordenes si no existe
  db.query(`ALTER TABLE ordenes ADD COLUMN IF NOT EXISTS costo DECIMAL(10,2) DEFAULT 0`);
  db.query(`ALTER TABLE ordenes ADD COLUMN IF NOT EXISTS mano_obra DECIMAL(10,2) DEFAULT 0`);
  db.query(`ALTER TABLE ordenes ADD COLUMN IF NOT EXISTS costo_repuestos DECIMAL(10,2) DEFAULT 0`);
  db.query(`CREATE TABLE IF NOT EXISTS gastos_inventario (id INT AUTO_INCREMENT PRIMARY KEY, inventario_id INT, nombre VARCHAR(120), cantidad_agregada INT, costo_compra DECIMAL(10,2), fecha DATETIME DEFAULT CURRENT_TIMESTAMP)`);
  // Repuestos del inventario usados en cada orden (permite seleccionar del inventario en vez de escribir el monto a mano)
  db.query(`CREATE TABLE IF NOT EXISTS orden_repuestos (id INT AUTO_INCREMENT PRIMARY KEY, orden_id INT, inventario_id INT, nombre VARCHAR(150), cantidad INT, precio_unitario DECIMAL(10,2), subtotal DECIMAL(10,2))`);
  // Historial de movimientos de inventario (altas, ajustes, eliminaciones y descuentos por órdenes) — persistido para que no se pierda al recargar la página
  db.query(`CREATE TABLE IF NOT EXISTS actividad_inventario (id INT AUTO_INCREMENT PRIMARY KEY, tipo VARCHAR(20), texto VARCHAR(255), fecha DATETIME DEFAULT CURRENT_TIMESTAMP)`);
  // Cuentas de usuario del sistema (admin/gerente, recepcionista, mecánico, etc.) — por si la tabla no existía aún
  db.query(`CREATE TABLE IF NOT EXISTS usuarios (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100), usuario VARCHAR(50) UNIQUE, password VARCHAR(255), rol VARCHAR(30) DEFAULT 'Gerente')`);
  // Qué módulos puede ver cada rol (Panel, Clientes, Reportes, etc.)
  db.query(`CREATE TABLE IF NOT EXISTS permisos_rol (rol VARCHAR(50) PRIMARY KEY, permisos TEXT)`);
  // Aseguramos que las cuentas de administrador (con cualquiera de estas variantes de rol que ya
  // existieran antes de este sistema de permisos) siempre arranquen viendo todo el sistema,
  // sin depender de que el rol se llame exactamente "Gerente".
  const permisosCompletos = JSON.stringify(['dashboard','clientes','vehiculos','ordenes','inventario','reportes','usuarios','perfil']);
  ['Gerente', 'admin', 'Admin', 'Administrador'].forEach(rolAdmin => {
    db.query('INSERT IGNORE INTO permisos_rol (rol, permisos) VALUES (?,?)', [rolAdmin, permisosCompletos]);
  });
});

// Módulos que existen en el sistema y qué roles los ven si nadie ha configurado permisos todavía
const MODULOS_SISTEMA = ['dashboard','clientes','vehiculos','ordenes','inventario','reportes','usuarios','perfil'];
const ROLES_ADMIN_CONOCIDOS = ['gerente', 'admin', 'administrador', 'administrator'];
function permisosPorDefecto(rol) {
  // Por seguridad, solo los roles de tipo administrador (Gerente, admin, Administrador, etc.)
  // arrancan viendo todo el sistema. Cualquier rol nuevo o desconocido (incluidos los creados con
  // "Otro...") arranca con acceso básico —sin Reportes, Perfil de Usuarios ni Perfil del Taller—
  // hasta que el administrador entre a "Permisos por Rol" y decida ampliarlo. Así nunca queda un
  // rol nuevo con acceso administrativo por accidente, pero tampoco se le corta el acceso a un
  // admin ya existente solo porque su rol no se llame literalmente "Gerente".
  const esAdminConocido = ROLES_ADMIN_CONOCIDOS.includes((rol || '').toLowerCase());
  if (esAdminConocido) return [...MODULOS_SISTEMA];
  return MODULOS_SISTEMA.filter(m => !['reportes','usuarios','perfil'].includes(m));
}

function registrarActividad(tipo, texto) {
  db.query('INSERT INTO actividad_inventario (tipo, texto) VALUES (?,?)', [tipo, texto]);
}

// LOGIN
app.post('/login', (req, res) => {
  const { usuario, password } = req.body;
  db.query('SELECT * FROM usuarios WHERE usuario=?', [usuario], async (err, results) => {
    if (!results || results.length === 0) return res.json({ ok: false });
    const user = results[0];
    // Soporte para contraseñas antiguas (texto plano) y nuevas (bcrypt)
    let valido = false;
    if (user.password.startsWith('$2b$') || user.password.startsWith('$2a$')) {
      valido = await bcrypt.compare(password, user.password);
    } else {
      // Contraseña en texto plano — la migra a bcrypt automáticamente
      valido = (password === user.password);
      if (valido) {
        const hash = await bcrypt.hash(password, 10);
        db.query('UPDATE usuarios SET password=? WHERE id=?', [hash, user.id]);
      }
    }
    if (!valido) return res.json({ ok: false });
    const token = jwt.sign({ id: user.id, usuario: user.usuario, rol: user.rol }, JWT_SECRET, { expiresIn: '8h' });
    res.json({ ok: true, rol: user.rol, nombre: user.nombre, token });
  });
});

// A partir de aquí, TODAS las rutas requieren un token válido
app.use(verificarToken);

// USUARIOS — gestión de perfiles de acceso (admin/gerente y trabajadores)
app.get('/usuarios', (req, res) => {
  db.query('SELECT id, usuario, nombre, rol FROM usuarios ORDER BY nombre', (err, results) => res.json(results || []));
});
app.post('/usuarios', async (req, res) => {
  const { usuario, password, nombre, rol } = req.body;
  if (!usuario || !password || !nombre || !rol) return res.json({ ok: false, error: 'Completa todos los campos' });
  const hash = await bcrypt.hash(password, 10);
  db.query('SELECT id FROM usuarios WHERE usuario=?', [usuario], (errSel, existentes) => {
    if (existentes && existentes.length) return res.json({ ok: false, error: 'Ese nombre de usuario ya existe' });
    db.query('INSERT INTO usuarios (usuario, password, nombre, rol) VALUES (?,?,?,?)', [usuario, hash, nombre, rol], (err, result) => {
      if (err) return res.json({ ok: false, error: err.message });
      res.json({ ok: true, id: result.insertId });
    });
  });
});
app.put('/usuarios/:id', async (req, res) => {
  const { nombre, rol, password } = req.body;
  const campos = ['nombre=?', 'rol=?'];
  const valores = [nombre, rol];
  if (password) {
    const hash = await bcrypt.hash(password, 10);
    campos.push('password=?');
    valores.push(hash);
  }
  valores.push(req.params.id);
  db.query(`UPDATE usuarios SET ${campos.join(', ')} WHERE id=?`, valores, (err) => {
    if (err) return res.json({ ok: false, error: err.message });
    res.json({ ok: true });
  });
});
app.delete('/usuarios/:id', (req, res) => {
  db.query('DELETE FROM usuarios WHERE id=?', [req.params.id], (err) => {
    if (err) return res.json({ ok: false });
    res.json({ ok: true });
  });
});

// PERMISOS POR ROL — qué módulos del sistema puede ver cada rol
app.get('/permisos/:rol', (req, res) => {
  db.query('SELECT permisos FROM permisos_rol WHERE rol=?', [req.params.rol], (err, rows) => {
    if (rows && rows.length) {
      try { return res.json({ rol: req.params.rol, permisos: JSON.parse(rows[0].permisos) }); } catch (e) { /* cae al default */ }
    }
    res.json({ rol: req.params.rol, permisos: permisosPorDefecto(req.params.rol) });
  });
});
app.put('/permisos/:rol', (req, res) => {
  const permisos = Array.isArray(req.body.permisos) ? req.body.permisos.filter(m => MODULOS_SISTEMA.includes(m)) : [];
  const json = JSON.stringify(permisos);
  db.query('INSERT INTO permisos_rol (rol, permisos) VALUES (?,?) ON DUPLICATE KEY UPDATE permisos=?', [req.params.rol, json, json], (err) => {
    if (err) return res.json({ ok: false, error: err.message });
    res.json({ ok: true });
  });
});

// CLIENTES
app.get('/clientes', (req, res) => {
  db.query('SELECT * FROM clientes ORDER BY nombre', (err, results) => res.json(results || []));
});
app.post('/clientes', (req, res) => {
  const { nombre, telefono, email } = req.body;
  db.query('INSERT INTO clientes (nombre, telefono, email) VALUES (?,?,?)', [nombre, telefono, email], (err, result) => {
    if (err) return res.json({ ok: false });
    res.json({ ok: true, id: result.insertId });
  });
});
app.put('/clientes/:id', (req, res) => {
  const { nombre, telefono, email } = req.body;
  db.query('UPDATE clientes SET nombre=?, telefono=?, email=? WHERE id=?', [nombre, telefono, email, req.params.id], (err) => {
    if (err) return res.json({ ok: false });
    res.json({ ok: true });
  });
});
app.delete('/clientes/:id', (req, res) => {
  db.query('DELETE FROM clientes WHERE id=?', [req.params.id], (err) => {
    if (err) return res.json({ ok: false });
    res.json({ ok: true });
  });
});

// HISTORIAL DEL CLIENTE
app.get('/clientes/:id/ordenes', (req, res) => {
  db.query(
    'SELECT * FROM ordenes WHERE cliente_id = ? ORDER BY id DESC',
    [req.params.id],
    (err, results) => res.json(results || [])
  );
});

app.get('/estadisticas', (req, res) => {
  const ahora = new Date();
  const primerDia = new Date(ahora.getFullYear(), ahora.getMonth(), 1).toISOString().split('T')[0];
  db.query('SELECT COUNT(*) as total FROM clientes', (err, total) => {
    db.query('SELECT COUNT(*) as activos FROM clientes WHERE creado_en >= ?', [primerDia], (err, activos) => {
      db.query("SELECT COUNT(DISTINCT cliente_id) as pendientes FROM ordenes WHERE estado='Pendiente'", (err, pend) => {
        db.query("SELECT COUNT(DISTINCT cliente_id) as en_proceso FROM ordenes WHERE estado='En Proceso'", (err, proc) => {
          db.query("SELECT COUNT(DISTINCT cliente_id) as completados FROM ordenes WHERE estado='Completado'", (err, comp) => {
            res.json({
              total: total[0].total,
              activos_mes: activos[0].activos,
              pendientes: pend[0].pendientes,
              en_proceso: proc[0].en_proceso,
              completados: comp[0].completados
            });
          });
        });
      });
    });
  });
});

// VEHICULOS
app.get('/vehiculos', (req, res) => {
  db.query('SELECT v.*, c.nombre as cliente_nombre FROM vehiculos v JOIN clientes c ON v.cliente_id = c.id ORDER BY v.id DESC', (err, results) => res.json(results || []));
});
app.post('/vehiculos', (req, res) => {
  const { cliente_id, placa, marca, modelo, anio, color, imagen } = req.body;
  db.query('INSERT INTO vehiculos (cliente_id, placa, marca, modelo, anio, color, imagen) VALUES (?,?,?,?,?,?,?)', [cliente_id, placa, marca, modelo, anio, color, imagen || null], (err, result) => {
    if (err) return res.json({ ok: false, error: err.message });
    res.json({ ok: true, id: result.insertId });
  });
});
app.put('/vehiculos/:id', (req, res) => {
  const { placa, marca, modelo, anio, color, imagen } = req.body;
  db.query('UPDATE vehiculos SET placa=?, marca=?, modelo=?, anio=?, color=?, imagen=? WHERE id=?', [placa, marca, modelo, anio, color, imagen || null, req.params.id], (err) => {
    if (err) return res.json({ ok: false });
    res.json({ ok: true });
  });
});
app.delete('/vehiculos/:id', (req, res) => {
  db.query('DELETE FROM vehiculos WHERE id=?', [req.params.id], (err) => {
    if (err) return res.json({ ok: false });
    res.json({ ok: true });
  });
});

// ORDENES — excluye Finalizadas de la lista principal
app.get('/ordenes', (req, res) => {
  db.query('SELECT o.*, c.nombre as cliente_nombre FROM ordenes o JOIN clientes c ON o.cliente_id = c.id ORDER BY o.id DESC', (err, results) => res.json(results || []));
});
app.post('/ordenes', (req, res) => {
  const { cliente_id, vehiculo, descripcion, fecha, costo, mano_obra, costo_repuestos, repuestos } = req.body;
  db.query('INSERT INTO ordenes (cliente_id, vehiculo, descripcion, fecha, estado, costo, mano_obra, costo_repuestos) VALUES (?,?,?,?,?,?,?,?)',
    [cliente_id, vehiculo, descripcion, fecha, 'Pendiente', costo||0, mano_obra||0, costo_repuestos||0],
    (err, result) => {
      if (err) return res.json({ ok: false });
      const ordenId = result.insertId;
      const lista = Array.isArray(repuestos) ? repuestos.filter(r => r && r.inventario_id && parseInt(r.cantidad) > 0) : [];

      if (!lista.length) return res.json({ ok: true, id: ordenId });

      let pendientes = lista.length;
      let errores = [];
      lista.forEach(r => {
        const cantidad = parseInt(r.cantidad) || 0;
        const precio = parseFloat(r.precio) || 0;
        const subtotal = +(cantidad * precio).toFixed(2);

        db.query(
          'INSERT INTO orden_repuestos (orden_id, inventario_id, nombre, cantidad, precio_unitario, subtotal) VALUES (?,?,?,?,?,?)',
          [ordenId, r.inventario_id, r.nombre, cantidad, precio, subtotal]
        );
        // Descuenta el stock usado, sin dejarlo bajar de 0
        db.query('UPDATE inventario SET cantidad = GREATEST(cantidad - ?, 0) WHERE id=?', [cantidad, r.inventario_id], (errInv) => {
          if (errInv) errores.push(r.nombre);
          else registrarActividad('del', `Usado en Orden #${ordenId}: -${cantidad} unidades de ${r.nombre}`);
          pendientes--;
          if (pendientes === 0) {
            res.json({ ok: true, id: ordenId, aviso: errores.length ? `La orden se creó, pero no se pudo actualizar el stock de: ${errores.join(', ')}` : undefined });
          }
        });
      });
    });
});
// Repuestos usados en una orden específica (para mostrar el detalle si se necesita)
app.get('/ordenes/:id/repuestos', (req, res) => {
  db.query('SELECT * FROM orden_repuestos WHERE orden_id=?', [req.params.id], (err, results) => res.json(results || []));
});

// EXPORTAR ORDEN EN PDF
app.get('/ordenes/:id/pdf', (req, res) => {
  const { exec } = require('child_process');
  const path = require('path');
  const fs = require('fs');
  const ordenId = req.params.id;

  db.query('SELECT o.*, c.nombre as cliente_nombre FROM ordenes o JOIN clientes c ON o.cliente_id = c.id WHERE o.id = ?', [ordenId], (err, ordenes) => {
    if (err || !ordenes.length) return res.status(404).json({ error: 'Orden no encontrada' });
    const orden = ordenes[0];

    db.query('SELECT * FROM orden_repuestos WHERE orden_id = ?', [ordenId], (err2, repuestos) => {
      db.query('SELECT * FROM perfil_taller WHERE id = 1', (err3, perfilRows) => {
        const taller = perfilRows && perfilRows.length ? perfilRows[0] : {};
        const mano_obra = parseFloat(orden.mano_obra || 0);
        const costo_repuestos = parseFloat(orden.costo_repuestos || 0);
        const fechaStr = orden.fecha ? new Date(orden.fecha).toISOString().substring(0, 10) : '';

        const datos = {
          numero: orden.id,
          anio: fechaStr ? fechaStr.substring(0, 4) : new Date().getFullYear(),
          fecha: fechaStr,
          cliente_nombre: orden.cliente_nombre,
          vehiculo: orden.vehiculo,
          descripcion: orden.descripcion,
          estado: orden.estado,
          mano_obra,
          costo_repuestos,
          costo: mano_obra + costo_repuestos,
          repuestos: repuestos || [],
          taller: {
            nombre: taller.nombre || 'Multiservicios Cárdenas',
            direccion: taller.direccion || '',
            ciudad: taller.ciudad || '',
            telefono: taller.telefono || ''
          }
        };

        fs.writeFileSync(path.join(__dirname, 'scripts/orden_datos.json'), JSON.stringify(datos));
        exec(`node ${path.join(__dirname, 'scripts/generar_orden_pdf.js')}`, (err4, stdout, stderr) => {
          if (err4) return res.status(500).json({ error: err4.message, detalle: stderr });
          res.download(path.join(__dirname, 'scripts/orden_output.pdf'), `Orden_${datos.numero}_${datos.fecha}.pdf`);
        });
      });
    });
  });
});

app.put('/ordenes/:id/desglose', (req, res) => {
  const { campo, valor } = req.body;
  if (!['mano_obra','costo_repuestos'].includes(campo)) return res.json({ ok: false });
  db.query(`UPDATE ordenes SET ${campo}=?, costo=mano_obra+costo_repuestos WHERE id=?`,
    [valor, req.params.id],
    (err) => {
      if (err) return res.json({ ok: false });
      // Recalcular costo total
      db.query('UPDATE ordenes SET costo=mano_obra+costo_repuestos WHERE id=?', [req.params.id]);
      res.json({ ok: true });
    });
});
app.put('/ordenes/:id/estado', (req, res) => {
  const { estado } = req.body;
  db.query('UPDATE ordenes SET estado=? WHERE id=?', [estado, req.params.id], (err) => {
    if (err) return res.json({ ok: false });
    res.json({ ok: true });
  });
});

// INVENTARIO
app.get('/inventario', (req, res) => {
  db.query('SELECT * FROM inventario ORDER BY nombre', (err, results) => res.json(results || []));
});

app.get('/inventario/actividad', (req, res) => {
  const { periodo } = req.query;
  if (!periodo) {
    db.query('SELECT * FROM actividad_inventario ORDER BY id DESC LIMIT 15', (err, results) => res.json(results || []));
    return;
  }
  const ahora = new Date();
  let fechaDesde;
  if (periodo === 'dia') fechaDesde = ahora.toISOString().split('T')[0];
  else if (periodo === 'semana') { const d = new Date(ahora); d.setDate(d.getDate()-7); fechaDesde = d.toISOString().split('T')[0]; }
  else { const d = new Date(ahora); d.setMonth(d.getMonth()-1); fechaDesde = d.toISOString().split('T')[0]; }
  db.query('SELECT * FROM actividad_inventario WHERE fecha >= ? ORDER BY id DESC', [fechaDesde], (err, results) => res.json(results || []));
});

app.post('/inventario', (req, res) => {
  const { nombre, categoria, cantidad, cantidad_minima, precio } = req.body;
  db.query('INSERT INTO inventario (nombre, categoria, cantidad, cantidad_minima, precio) VALUES (?,?,?,?,?)', [nombre, categoria, cantidad, cantidad_minima, precio], (err, result) => {
    if (err) return res.json({ ok: false, error: err.message });
    registrarActividad('add', `Nuevo repuesto: ${nombre} (+${cantidad} unidades)`);
    res.json({ ok: true, id: result.insertId });
  });
});
app.put('/inventario/:id', (req, res) => {
  const { cantidad, costo_compra } = req.body;
  db.query('SELECT cantidad, nombre FROM inventario WHERE id=?', [req.params.id], (err, rows) => {
    const cantAnterior = rows && rows[0] ? rows[0].cantidad : 0;
    const nombre = rows && rows[0] ? rows[0].nombre : '';
    db.query('UPDATE inventario SET cantidad=? WHERE id=?', [cantidad, req.params.id], (err2) => {
      if (err2) return res.json({ ok: false });
      const diff = parseInt(cantidad) - cantAnterior;
      if (diff > 0 && costo_compra > 0) {
        db.query('INSERT INTO gastos_inventario (inventario_id, nombre, cantidad_agregada, costo_compra) VALUES (?,?,?,?)',
          [req.params.id, nombre, diff, costo_compra]);
      }
      if (diff !== 0) {
        const detalleCosto = diff > 0 && costo_compra > 0 ? ` (S/. ${parseFloat(costo_compra).toFixed(2)})` : '';
        registrarActividad(diff > 0 ? 'add' : 'del', `${nombre}: ${diff > 0 ? '+' : ''}${diff} unidades${detalleCosto}`);
      }
      res.json({ ok: true });
    });
  });
});
app.delete('/inventario/:id', (req, res) => {
  db.query('SELECT nombre FROM inventario WHERE id=?', [req.params.id], (errSel, rows) => {
    const nombre = rows && rows[0] ? rows[0].nombre : `#${req.params.id}`;
    db.query('DELETE FROM inventario WHERE id=?', [req.params.id], (err) => {
      if (err) return res.json({ ok: false });
      registrarActividad('del', `Eliminado: ${nombre}`);
      res.json({ ok: true });
    });
  });
});

// REPORTES
// Función compartida: calcula TODOS los datos del reporte (resumen, desglose económico,
// ingresos por semana, distribución por estado y transacciones) para un periodo dado.
// La usan tanto la vista web (/reportes) como las exportaciones (Excel y Word), para que
// nunca queden desincronizadas ni le falte información a los reportes exportados.
function calcularDatosReporte(periodo, limiteRecientes, callback) {
  let fechaDesde;
  const ahora = new Date();
  if (periodo === 'dia') fechaDesde = ahora.toISOString().split('T')[0];
  else if (periodo === 'semana') { const d = new Date(ahora); d.setDate(d.getDate()-7); fechaDesde = d.toISOString().split('T')[0]; }
  else { const d = new Date(ahora); d.setMonth(d.getMonth()-1); fechaDesde = d.toISOString().split('T')[0]; }

  db.query(`SELECT o.*, c.nombre as cliente_nombre FROM ordenes o JOIN clientes c ON o.cliente_id = c.id WHERE o.fecha >= ? ORDER BY o.fecha DESC`, [fechaDesde], (err, ordenes) => {
    if (err) return callback(err);
    ordenes = ordenes || [];

    const total_mano_obra = ordenes.reduce((s,o) => s + parseFloat(o.mano_obra||0), 0);
    const total_repuestos_ingreso = ordenes.reduce((s,o) => s + parseFloat(o.costo_repuestos||0), 0);
    const total = ordenes.reduce((s,o) => s + parseFloat(o.costo||0), 0);
    const completadas = ordenes.filter(o => o.estado==='Completado'||o.estado==='Finalizado');
    const ticketProm = completadas.length ? completadas.reduce((s,o)=>s+parseFloat(o.costo||0),0)/completadas.length : 0;

    const porSemana = {};
    ordenes.forEach(o => { const f = new Date(o.fecha); const s = `S${Math.ceil(f.getDate()/7)}`; porSemana[s] = (porSemana[s]||0)+parseFloat(o.costo||0); });

    // Distribución de órdenes por estado (Pendiente, En Proceso, Completado, Finalizado)
    const estados = ['Pendiente','En Proceso','Completado','Finalizado'];
    const porEstado = {};
    estados.forEach(e => { porEstado[e] = ordenes.filter(o => o.estado === e).length; });

    const recientes = ordenes.slice(0, limiteRecientes).map(o => ({
      fecha: o.fecha ? o.fecha.toString().substring(0,10) : '-',
      descripcion: o.descripcion,
      vehiculo: o.vehiculo,
      cliente: o.cliente_nombre,
      estado: o.estado,
      monto: parseFloat(o.costo||0),
      mano_obra: parseFloat(o.mano_obra||0),
      costo_repuestos: parseFloat(o.costo_repuestos||0)
    }));

    // Gastos de inventario en el periodo
    db.query(`SELECT SUM(costo_compra) as total_gastos FROM gastos_inventario WHERE fecha >= ?`, [fechaDesde], (err2, gastos) => {
      if (err2) return callback(err2);
      const total_gastos_inventario = gastos && gastos[0] ? parseFloat(gastos[0].total_gastos||0) : 0;
      const ganancia_neta = total - total_gastos_inventario;

      callback(null, {
        total: total.toFixed(2),
        total_ordenes: ordenes.length,
        ticket_promedio: ticketProm.toFixed(2),
        completadas: completadas.length,
        por_semana: porSemana,
        por_estado: porEstado,
        recientes,
        desglose: {
          mano_obra: total_mano_obra.toFixed(2),
          repuestos_ingreso: total_repuestos_ingreso.toFixed(2),
          total_ingresos: total.toFixed(2),
          gastos_inventario: total_gastos_inventario.toFixed(2),
          ganancia_neta: ganancia_neta.toFixed(2)
        }
      });
    });
  });
}

app.get('/reportes', (req, res) => {
  const { periodo } = req.query;
  calcularDatosReporte(periodo, 10, (err, data) => {
    if (err) return res.json({ ok: false });
    res.json(data);
  });
});

// EXPORTAR REPORTE EXCEL
app.get('/reportes/excel', (req, res) => {
  const { periodo } = req.query;
  const { exec } = require('child_process');
  const path = require('path');
  const fs = require('fs');

  calcularDatosReporte(periodo, 20, (err, data) => {
    if (err) return res.status(500).json({ error: err.message });
    const datos = {
      resumen: { total: data.total, total_ordenes: data.total_ordenes, ticket_promedio: data.ticket_promedio, completadas: data.completadas },
      recientes: data.recientes,
      porSemana: data.por_semana,
      porEstado: data.por_estado,
      desglose: data.desglose,
      periodo: periodo || 'mes'
    };
    fs.writeFileSync(path.join(__dirname, 'scripts/reporte_datos.json'), JSON.stringify(datos));
    exec(`python3 ${path.join(__dirname, 'scripts/generar_excel.py')}`, (err2, stdout, stderr) => {
      if (err2) return res.status(500).json({ error: err2.message, detalle: stderr });
      const fecha = new Date().toISOString().split('T')[0];
      res.download(path.join(__dirname, 'scripts/reporte_output.xlsx'), `Reporte_${periodo||'mes'}_${fecha}.xlsx`);
    });
  });
});

// EXPORTAR REPORTE WORD
app.get('/reportes/word', (req, res) => {
  const { periodo } = req.query;
  const { exec } = require('child_process');
  const path = require('path');
  const fs = require('fs');

  calcularDatosReporte(periodo, 20, (err, data) => {
    if (err) return res.status(500).json({ error: err.message });
    const datos = {
      resumen: { total: data.total, total_ordenes: data.total_ordenes, ticket_promedio: data.ticket_promedio, completadas: data.completadas },
      recientes: data.recientes,
      porSemana: data.por_semana,
      porEstado: data.por_estado,
      desglose: data.desglose,
      periodo: periodo || 'mes'
    };
    fs.writeFileSync(path.join(__dirname, 'scripts/reporte_datos.json'), JSON.stringify(datos));
    exec(`node ${path.join(__dirname, 'scripts/generar_reporte.js')}`, (err2, stdout, stderr) => {
      if (err2) return res.status(500).json({ error: err2.message, detalle: stderr });
      const fecha = new Date().toISOString().split('T')[0];
      res.download(path.join(__dirname, 'scripts/reporte_output.docx'), `Reporte_${periodo||'mes'}_${fecha}.docx`);
    });
  });
});

// PERFIL DEL TALLER
app.get('/perfil', (req, res) => {
  db.query('SELECT * FROM perfil_taller WHERE id = 1', (err, results) => {
    if (err || !results.length) return res.json({});
    res.json(results[0]);
  });
});
app.put('/perfil', (req, res) => {
  const { nombre, telefono, email, direccion, ciudad, ruc, horario } = req.body;
  db.query(
    'UPDATE perfil_taller SET nombre=?, telefono=?, email=?, direccion=?, ciudad=?, ruc=?, horario=? WHERE id=1',
    [nombre, telefono, email, direccion, ciudad, ruc, horario],
    (err) => {
      if (err) return res.json({ ok: false, error: err.message });
      res.json({ ok: true });
    }
  );
});

app.listen(3000, () => console.log('Servidor corriendo en http://localhost:3000'));
