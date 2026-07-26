<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multiservicios Cárdenas - Inventario</title>
  <link rel="stylesheet" href="global.css">
  <style>
    .dos-col { display:grid; grid-template-columns:1fr 300px; gap:14px; }
    .stock-bar { height:6px; background:#e5e7eb; border-radius:3px; overflow:hidden; margin-top:6px; }
    .stock-fill { height:100%; border-radius:3px; transition:width .4s; }
    .actividad-item { display:flex; align-items:flex-start; gap:10px; padding:10px 0; border-bottom:1px solid #f3f4f6; }
    .actividad-dot { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; margin-top:2px; }
    .dot-green { background:#f0fdf4; }
    .dot-red { background:#fef2f2; }
    .dot-yellow { background:#fefce8; }
    .actividad-txt { font-size:13px; color:#333; line-height:1.4; }
    .actividad-time { font-size:11px; color:#aaa; margin-top:2px; }
    @media(max-width:768px) { .dos-col { grid-template-columns:1fr; } }
    
    #listaActividad { max-height: 340px; overflow-y: auto; }
.filtros-periodo { display:flex; gap:8px; }
.btn-periodo { background:white; border:1px solid #e5e7eb; border-radius:20px; padding:7px 20px; font-size:13px; font-weight:600; cursor:pointer; color:#555; transition:all .2s; }
.btn-periodo.active { background:#00a8cc; color:white; border-color:#00a8cc; }D
  </style>
</head>
<body>
<?php $paginaActiva = 'inventario'; include 'sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      <button class="menu-btn" onclick="toggleMenu()">☰</button>
      <span style="font-size:14px;color:#555">Control de Inventario</span>
      <button class="btn-primary" style="margin-left:auto" onclick="document.getElementById('modalAgregar').classList.add('show')">+ Nuevo Repuesto</button>
    </div>
    <div class="content">
      <h1>Inventario de Repuestos</h1>
      <p class="subtitle">Controla el stock de repuestos y recibe alertas automáticas.</p>

      <!-- Alerta crítica -->
      <div id="alertaStock" style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:16px;justify-content:space-between;align-items:center">
        <div>
          <span style="font-size:13px;font-weight:700;color:#dc2626">⚠️ Alerta Crítica: </span>
          <span style="font-size:13px;color:#dc2626" id="alertaTexto">Repuestos con stock bajo</span>
        </div>
        <button class="btn-primary" style="background:#dc2626;font-size:12px;padding:6px 14px">ORDEN RÁPIDA</button>
      </div>

      <div class="cards" style="margin-bottom:16px">
        <div class="card"><div class="card-label">TOTAL PRODUCTOS</div><div class="card-val" id="totalProductos">0</div></div>
        <div class="card"><div class="card-label">STOCK BAJO</div><div class="card-val" style="color:#dc2626" id="stockBajo">0</div></div>
        <div class="card"><div class="card-label">CATEGORÍAS</div><div class="card-val" id="totalCategorias">0</div></div>
        <div class="card"><div class="card-label">VALOR TOTAL</div><div class="card-val" id="valorTotal">S/. 0</div></div>
      </div>

      <div class="dos-col">
        <!-- Tabla de stock -->
        <div class="panel">
          <div class="panel-title">Niveles de Stock Actuales</div>
          <div class="msg-ok" id="msgOk">✅ Operación realizada correctamente</div>
          <table>
            <thead><tr><th>PRODUCTO / SKU</th><th>CATEGORÍA</th><th>STOCK</th><th>ESTADO</th><th>ACCIONES</th></tr></thead>
            <tbody id="tablaInventario"></tbody>
          </table>
        </div>

        <!-- Registro de actividad -->
        <div>
          <div class="panel">
            <div class="panel-title">📋 Registro de Actividad</div>
            <div id="listaActividad">
              <div class="actividad-item">
                <div class="actividad-dot dot-green">+</div>
                <div>
                  <div class="actividad-txt">Sistema iniciado correctamente</div>
                  <div class="actividad-time">Hoy</div>
                </div>
              </div>
            </div>
            <div style="margin-top:12px;text-align:center">
              <span style="font-size:12px;color:#1d6fb5;cursor:pointer" onclick="abrirHistorialCompleto()">Ver historial completo →</span>
            </div>
          </div>

          <!-- Distribución por categoría -->
          <div class="panel">
            <div class="panel-title">Distribución por Categoría</div>
            <div id="distribucionCat"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal agregar -->
  <div class="modal-bg" id="modalAgregar">
    <div class="modal">
      <h3>Agregar Repuesto</h3>
      <div class="form-group"><label>NOMBRE DEL REPUESTO</label><input type="text" id="nombre" placeholder="Ej: Aceite Motor 5W-30"></div>
      <div class="form-group"><label>CATEGORÍA</label>
        <select id="categoria">
          <option value="">Seleccionar...</option>
          <option>Lubricantes</option><option>Filtros</option><option>Frenos</option>
          <option>Eléctrico</option><option>Neumáticos</option><option>Motor</option><option>Otros</option>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
        <div class="form-group"><label>CANTIDAD</label><input type="number" id="cantidad" placeholder="0"></div>
        <div class="form-group"><label>MÍNIMO</label><input type="number" id="cantidad_minima" placeholder="5"></div>
        <div class="form-group"><label>PRECIO (S/.)</label><input type="number" id="precio" placeholder="0.00" step="0.01"></div>
      </div>
      <div class="modal-btns">
        <button class="btn-cancel" onclick="document.getElementById('modalAgregar').classList.remove('show')">Cancelar</button>
        <button class="btn-primary" onclick="guardarRepuesto()">Guardar</button>
      </div>
    </div>
  </div>

  <!-- Modal ajustar stock -->
  <div class="modal-bg" id="modalAjustar">
    <div class="modal">
      <h3>Ajustar Stock</h3>
      <p style="color:#888;font-size:13px;margin-bottom:16px" id="nombreAjuste"></p>
      <div class="form-group"><label>NUEVA CANTIDAD</label><input type="number" id="nuevaCantidad" placeholder="0"></div>
      <div class="form-group" id="campoGasto" style="display:none">
        <label>COSTO DE COMPRA (S/.) <span style="color:#888;font-size:11px">— ¿Cuánto pagaste por este reabastecimiento?</span></label>
        <input type="number" id="costoCompra" placeholder="0.00" step="0.01">
      </div>
      <div class="modal-btns">
        <button class="btn-cancel" onclick="document.getElementById('modalAjustar').classList.remove('show')">Cancelar</button>
        <button class="btn-primary" onclick="ajustarStock()">Actualizar Stock</button>
      </div>
    </div>
  </div>

<!-- Modal historial completo -->
<div class="modal-bg" id="modalHistorialCompleto">
  <div class="modal" style="width:520px;max-width:95vw">
    <h3>Historial Completo de Actividad</h3>
    <div class="filtros-periodo" style="margin-bottom:14px">
      <button class="btn-periodo active" onclick="cambiarPeriodoHistorial('mes', this)">Mensual</button>
      <button class="btn-periodo" onclick="cambiarPeriodoHistorial('semana', this)">Semanal</button>
      <button class="btn-periodo" onclick="cambiarPeriodoHistorial('dia', this)">Diario</button>
    </div>
    <div id="listaHistorialCompleto"></div>
    <div class="modal-btns">
      <button class="btn-cancel" onclick="document.getElementById('modalHistorialCompleto').classList.remove('show')">Cerrar</button>
    </div>
  </div>
</div>

  <script src="shared.js"></script>
  <script>
    cargarUsuario();
    let idAjuste = null;

    async function cargarActividad() {
      const actividades = await apiFetch('http://localhost:3000/inventario/actividad').then(r => r.json());
      const lista = document.getElementById('listaActividad');
      lista.innerHTML = actividades.length ? actividades.map(a => {
        const fecha = new Date(a.fecha);
        const hoy = new Date();
        const esHoy = fecha.toDateString() === hoy.toDateString();
        const cuando = (esHoy ? 'Hoy, ' : fecha.toLocaleDateString('es-PE', {day:'2-digit',month:'short'}) + ', ') + fecha.toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit'});
        return `
        <div class="actividad-item">
          <div class="actividad-dot ${a.tipo==='add'?'dot-green':a.tipo==='del'?'dot-red':'dot-yellow'}">${a.tipo==='add'?'+':a.tipo==='del'?'−':'✎'}</div>
          <div>
            <div class="actividad-txt">${a.texto}</div>
            <div class="actividad-time">${cuando}</div>
          </div>
        </div>`;
      }).join('') : `
        <div class="actividad-item">
          <div class="actividad-dot dot-green">+</div>
          <div>
            <div class="actividad-txt">Sistema iniciado correctamente</div>
            <div class="actividad-time">Hoy</div>
          </div>
        </div>`;
    }

let periodoHistorial = 'mes';

function abrirHistorialCompleto() {
  document.getElementById('modalHistorialCompleto').classList.add('show');
  cargarHistorialCompleto(periodoHistorial);
}

function cambiarPeriodoHistorial(periodo, btn) {
  periodoHistorial = periodo;
  btn.parentElement.querySelectorAll('.btn-periodo').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  cargarHistorialCompleto(periodo);
}

async function cargarHistorialCompleto(periodo) {
  const cont = document.getElementById('listaHistorialCompleto');
  cont.innerHTML = '<p style="color:#888;font-size:13px;text-align:center">Cargando...</p>';
  const datos = await apiFetch(`http://localhost:3000/inventario/actividad?periodo=${periodo}`).then(r => r.json());
  if (!datos.length) {
    cont.innerHTML = '<p style="color:#888;font-size:13px;text-align:center;padding:20px">Sin actividad en este período</p>';
    return;
  }
  cont.innerHTML = datos.map(a => {
    const fecha = new Date(a.fecha);
    const fechaTxt = fecha.toLocaleDateString('es-PE', { day:'2-digit', month:'short' }) + ', ' + fecha.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' });
    const dotClass = a.tipo === 'add' ? 'dot-green' : a.tipo === 'del' ? 'dot-red' : 'dot-yellow';
    const simbolo = a.tipo === 'add' ? '+' : a.tipo === 'del' ? '−' : '✎';
    return `<div class="actividad-item">
      <div class="actividad-dot ${dotClass}">${simbolo}</div>
      <div>
        <div class="actividad-txt">${a.texto}</div>
        <div class="actividad-time">${fechaTxt}</div>
      </div>
    </div>`;
  }).join('');
}
    async function cargarInventario() {
      const items = await apiFetch('http://localhost:3000/inventario').then(r => r.json());
      document.getElementById('totalProductos').textContent = items.length;
      const bajos = items.filter(i => i.cantidad <= i.cantidad_minima);
      document.getElementById('stockBajo').textContent = bajos.length;

      if (bajos.length > 0) {
        const alerta = document.getElementById('alertaStock');
        alerta.style.display = 'flex';
        const agotados = bajos.filter(b => b.cantidad === 0);
        const pocas = bajos.filter(b => b.cantidad > 0);
        let txt = '';
        if (agotados.length) txt += agotados.length + ' agotado(s): ' + agotados.map(b=>b.nombre).join(', ');
        if (pocas.length) txt += (txt ? ' — ' : '') + pocas.length + ' con poca existencia: ' + pocas.map(b=>b.nombre).join(', ');
        document.getElementById('alertaTexto').textContent = txt;
      } else {
        document.getElementById('alertaStock').style.display = 'none';
      }

      const cats = [...new Set(items.map(i => i.categoria))];
      document.getElementById('totalCategorias').textContent = cats.length;
      const valor = items.reduce((s,i) => s + (i.cantidad * i.precio), 0);
      document.getElementById('valorTotal').textContent = 'S/. ' + valor.toFixed(2);

      // Tabla
      const tbody = document.getElementById('tablaInventario');
      tbody.innerHTML = items.map((i, idx) => {
        const agotado = i.cantidad === 0;
        const bajo = i.cantidad > 0 && i.cantidad <= i.cantidad_minima;
        const pct = agotado ? 0 : Math.min(100, Math.round((i.cantidad / (i.cantidad_minima * 2 || 10)) * 100));
        const color = agotado ? '#dc2626' : bajo ? '#e07020' : '#16a34a';
        const estadoLabel = agotado ? 'AGOTADO' : bajo ? 'POCA EXISTENCIA' : 'SALUDABLE';
        const estadoClass = agotado ? 's-red' : bajo ? 's-orange' : 's-green';
        return `<tr>
          <td>
            <div style="font-weight:600">${i.nombre}</div>
            <div style="font-size:11px;color:#888">SKU-${String(i.id).padStart(3,'0')}</div>
            <div class="stock-bar"><div class="stock-fill" style="width:${pct}%;background:${color}"></div></div>
          </td>
          <td><span style="background:#f3f4f6;padding:3px 8px;border-radius:12px;font-size:11px">${i.categoria}</span></td>
          <td style="font-weight:700;color:${agotado?'#dc2626':bajo?'#e07020':'#333'}">${i.cantidad} <span style="font-size:11px;color:#aaa">/ ${i.cantidad_minima*2}</span></td>
          <td><span class="status ${estadoClass}">${estadoLabel}</span></td>
          <td style="display:flex;gap:6px">
            <button class="btn-primary" style="padding:4px 10px;font-size:12px" onclick="abrirAjuste(${i.id},'${i.nombre}',${i.cantidad})">Ajustar</button>
            <button class="btn-eliminar" onclick="eliminarItem(${i.id},'${i.nombre}')">Eliminar</button>
          </td>
        </tr>`;
      }).join('') || '<tr><td colspan="5" style="text-align:center;color:#888;padding:20px">No hay repuestos</td></tr>';

      // Distribución por categoría
      const dist = document.getElementById('distribucionCat');
      const totalItems = items.length || 1;
      dist.innerHTML = cats.map(cat => {
        const cnt = items.filter(i=>i.categoria===cat).length;
        const pct = Math.round((cnt/totalItems)*100);
        return `<div style="margin-bottom:12px">
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
            <span>${cat}</span><span style="font-weight:600">${pct}%</span>
          </div>
          <div class="stock-bar" style="height:8px"><div class="stock-fill" style="width:${pct}%;background:#00a8cc"></div></div>
        </div>`;
      }).join('') || '<div style="color:#888;font-size:13px">Sin datos</div>';
    }

   async function guardarRepuesto() {
  const nombre = document.getElementById('nombre').value;
  const categoria = document.getElementById('categoria').value;
  const cantidad = document.getElementById('cantidad').value;
  const cantidad_minima = document.getElementById('cantidad_minima').value;
  const precio = document.getElementById('precio').value;

  limpiarErrores(['nombre','categoria','cantidad']);
  let valido = true;
  if (!nombre) { marcarError('nombre', 'El nombre es obligatorio'); valido = false; }
  if (!categoria) { marcarError('categoria', 'Selecciona una categoría'); valido = false; }
  if (!cantidad) { marcarError('cantidad', 'La cantidad es obligatoria'); valido = false; }
  if (!valido) return;

      const res = await apiFetch('http://localhost:3000/inventario', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({nombre, categoria, cantidad, cantidad_minima: cantidad_minima||5, precio: precio||0})
      });
      const data = await res.json();
      if (data.ok) {
        document.getElementById('modalAgregar').classList.remove('show');
        document.getElementById('msgOk').style.display = 'block';
        document.getElementById('nombre').value = '';
        document.getElementById('cantidad').value = '';
        document.getElementById('precio').value = '';
        cargarInventario(); cargarActividad();
        setTimeout(() => document.getElementById('msgOk').style.display = 'none', 3000);
      }
    }

    function abrirAjuste(id, nombre, cantActual) {
      idAjuste = { id, nombre, cantActual };
      document.getElementById('nombreAjuste').textContent = 'Producto: ' + nombre;
      document.getElementById('nuevaCantidad').value = cantActual;
      document.getElementById('costoCompra').value = '';
      document.getElementById('campoGasto').style.display = 'none';
      document.getElementById('nuevaCantidad').oninput = function() {
        const nueva = parseInt(this.value) || 0;
        document.getElementById('campoGasto').style.display = nueva > cantActual ? 'block' : 'none';
      };
      document.getElementById('modalAjustar').classList.add('show');
    }

    async function ajustarStock() {
      const cantidad = document.getElementById('nuevaCantidad').value;
      const costoCompra = parseFloat(document.getElementById('costoCompra').value) || 0;
      const diff = parseInt(cantidad) - idAjuste.cantActual;
      await apiFetch(`http://localhost:3000/inventario/${idAjuste.id}`, {
        method:'PUT', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ cantidad, costo_compra: diff > 0 ? costoCompra : 0 })
      });
      document.getElementById('modalAjustar').classList.remove('show');
      cargarInventario(); cargarActividad();
    }

    async function eliminarItem(id, nombre) {
      if (!confirm('¿Eliminar este repuesto?')) return;
      await apiFetch(`http://localhost:3000/inventario/${id}`, { method:'DELETE' });
      cargarInventario(); cargarActividad();
    }

    cargarInventario();
    cargarActividad();
  </script>
</body>
</html>
