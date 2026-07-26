<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multiservicios Cárdenas - Perfil de Usuarios</title>
  <link rel="stylesheet" href="global.css">
  <style>
    .rol-badge { font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; }
  </style>
</head>
<body>
<?php $paginaActiva = 'usuarios'; include 'sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      <button class="menu-btn" onclick="toggleMenu()">☰</button>
      <span style="font-size:14px;color:#555">Perfil de Usuarios</span>
      <button class="btn-primary" style="margin-left:auto" onclick="abrirModalAgregar()">+ Nuevo Usuario</button>
    </div>
    <div class="content">
      <h1>Perfil de Usuarios</h1>
      <p class="subtitle">Crea y administra los accesos del equipo del taller (administradores, recepcionistas y mecánicos).</p>

      <div class="cards" style="margin-bottom:16px">
        <div class="card"><div class="card-label">TOTAL USUARIOS</div><div class="card-val" id="totalUsuarios">0</div></div>
        <div class="card"><div class="card-label">ADMINISTRADORES / GERENTES</div><div class="card-val" style="color:#1d4ed8" id="totalGerentes">0</div></div>
        <div class="card"><div class="card-label">MECÁNICOS</div><div class="card-val" style="color:#15803d" id="totalMecanicos">0</div></div>
        <div class="card"><div class="card-label">RECEPCIONISTAS</div><div class="card-val" style="color:#c2410c" id="totalRecepcion">0</div></div>
      </div>

      <div class="panel">
        <div class="msg-ok" id="msgOk">✅ Operación realizada correctamente</div>
        <div class="panel-title">Usuarios Registrados</div>
        <table>
          <thead><tr><th>NOMBRE</th><th>USUARIO</th><th>ROL</th><th>ACCIONES</th></tr></thead>
          <tbody id="tablaUsuarios"></tbody>
        </table>
      </div>

      <div class="panel" style="margin-top:16px">
        <div class="panel-title">🔒 Permisos por Rol</div>
        <p style="font-size:12px;color:#888;margin:-4px 0 16px">Elige qué puede ver cada rol dentro del sistema. El cambio aplica a todos los usuarios que tengan ese rol.</p>
        <div class="form-group" style="max-width:320px">
          <label>ROL A CONFIGURAR</label>
          <select id="selRolPermiso" onchange="cargarPermisosRol()"></select>
        </div>
        <div id="listaPermisos" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px;margin:16px 0"></div>
        <div style="display:flex;align-items:center;gap:12px">
          <button class="btn-primary" onclick="guardarPermisosRol()">💾 Guardar Permisos</button>
          <span id="msgPermisosOk" style="display:none;color:#15803d;font-size:12px;font-weight:600">✅ Permisos guardados</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal agregar/editar usuario -->
  <div class="modal-bg" id="modalUsuario">
    <div class="modal">
      <h3 id="modalTitulo">Nuevo Usuario</h3>
      <input type="hidden" id="usuarioIdEditar">
      <div class="form-group"><label>NOMBRE COMPLETO</label><input type="text" id="fNombre" placeholder="Ej: Carlos Ramírez"></div>
      <div class="form-group"><label>USUARIO (PARA INICIAR SESIÓN)</label><input type="text" id="fUsuario" placeholder="Ej: carlos.r"></div>
      <div class="form-group"><label id="lblPassword">CONTRASEÑA</label><input type="password" id="fPassword" placeholder="••••••••"></div>
      <div class="form-group" id="grupoPasswordNota" style="display:none;margin-top:-10px">
        <span style="font-size:11px;color:#888">Déjalo en blanco si no quieres cambiar la contraseña.</span>
      </div>
      <div class="form-group">
        <label>ROL</label>
        <select id="fRol" onchange="toggleRolOtro()">
          <option value="Gerente">Administrador / Gerente</option>
          <option value="Mecánico">Mecánico / Trabajador</option>
          <option value="recepcionista">Recepcionista</option>
          <option value="__otro__">Otro...</option>
        </select>
      </div>
      <div class="form-group" id="grupoRolOtro" style="display:none">
        <label>ESCRIBE EL ROL</label>
        <input type="text" id="fRolOtro" placeholder="Ej: Auxiliar de Turno, Practicante...">
      </div>
      <div class="modal-btns">
        <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
        <button class="btn-primary" id="btnGuardar" onclick="guardarUsuario()">Guardar Usuario</button>
      </div>
    </div>
  </div>

  <script src="shared.js"></script>
  <script>
    cargarUsuario();
    let todosUsuarios = [];
    const rolesBase = { 'Gerente': 'Administrador / Gerente', 'recepcionista': 'Recepcionista', 'Mecánico': 'Mecánico' };
    let etiquetasRol = { ...rolesBase };
    const coloresRol = {
      'Gerente': { bg: '#eff6ff', color: '#1d4ed8' },
      'recepcionista': { bg: '#fff7ed', color: '#c2410c' },
      'Mecánico': { bg: '#f0fdf4', color: '#15803d' }
    };
    const colorRolOtro = { bg: '#f3f0ff', color: '#6d28d9' }; // color por defecto para roles personalizados

    const MODULOS = [
      { key:'dashboard', label:'📊 Panel' },
      { key:'clientes', label:'👥 Clientes' },
      { key:'vehiculos', label:'🚗 Vehículos' },
      { key:'ordenes', label:'📋 Órdenes de Trabajo' },
      { key:'inventario', label:'📦 Inventario' },
      { key:'reportes', label:'📈 Reportes' },
      { key:'usuarios', label:'👤 Perfil de Usuarios' },
      { key:'perfil', label:'⚙️ Perfil del Taller' },
    ];

    function poblarSelectRolesPermiso() {
      const sel = document.getElementById('selRolPermiso');
      const rolesPersonalizados = [...new Set(todosUsuarios.map(u => u.rol).filter(r => !rolesBase[r]))];
      const rolesDisponibles = [...Object.keys(rolesBase), ...rolesPersonalizados];
      const actual = sel.value;
      sel.innerHTML = rolesDisponibles.map(r => `<option value="${r}">${etiquetasRol[r] || r}</option>`).join('');
      if (rolesDisponibles.includes(actual)) sel.value = actual;
      cargarPermisosRol();
    }

    async function cargarPermisosRol() {
      const rol = document.getElementById('selRolPermiso').value;
      const cont = document.getElementById('listaPermisos');
      if (!rol) { cont.innerHTML = ''; return; }
      const data = await apiFetch(`http://localhost:3000/permisos/${encodeURIComponent(rol)}`).then(r => r.json());
      cont.innerHTML = MODULOS.map(m => `
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;cursor:pointer">
          <input type="checkbox" value="${m.key}" ${data.permisos.includes(m.key) ? 'checked' : ''}> ${m.label}
        </label>
      `).join('');
    }

    async function guardarPermisosRol() {
      const rol = document.getElementById('selRolPermiso').value;
      if (!rol) return;
      const permisos = Array.from(document.querySelectorAll('#listaPermisos input[type=checkbox]:checked')).map(c => c.value);
      const res = await apiFetch(`http://localhost:3000/permisos/${encodeURIComponent(rol)}`, {
        method: 'PUT', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ permisos })
      });
      const data = await res.json();
      if (data.ok) {
        const msg = document.getElementById('msgPermisosOk');
        msg.style.display = 'inline';
        setTimeout(() => msg.style.display = 'none', 3000);
      } else {
        alert('No se pudieron guardar los permisos');
      }
    }

    function actualizarOpcionesRol() {
      // Roles personalizados = los que ya usan usuarios existentes y no son de la lista base
      const rolesPersonalizados = [...new Set(todosUsuarios.map(u => u.rol).filter(r => !rolesBase[r]))];
      rolesPersonalizados.forEach(r => { if (!etiquetasRol[r]) etiquetasRol[r] = r; });

      const sel = document.getElementById('fRol');
      sel.innerHTML =
        Object.entries(rolesBase).map(([val, label]) => `<option value="${val}">${label}</option>`).join('') +
        rolesPersonalizados.map(r => `<option value="${r}">${r}</option>`).join('') +
        `<option value="__otro__">Otro...</option>`;
    }

    function toggleRolOtro() {
      const esOtro = document.getElementById('fRol').value === '__otro__';
      document.getElementById('grupoRolOtro').style.display = esOtro ? 'block' : 'none';
      if (esOtro) document.getElementById('fRolOtro').focus();
    }

    function estiloRol(rol) {
      return coloresRol[rol] || colorRolOtro;
    }

    async function cargarUsuarios() {
      todosUsuarios = await apiFetch('http://localhost:3000/usuarios').then(r => r.json());
      document.getElementById('totalUsuarios').textContent = todosUsuarios.length;
      document.getElementById('totalGerentes').textContent = todosUsuarios.filter(u => ['gerente','admin','administrador','administrator'].includes((u.rol||'').toLowerCase())).length;
      document.getElementById('totalMecanicos').textContent = todosUsuarios.filter(u => u.rol === 'Mecánico').length;
      document.getElementById('totalRecepcion').textContent = todosUsuarios.filter(u => u.rol === 'recepcionista').length;
      actualizarOpcionesRol();
      poblarSelectRolesPermiso();

      const tbody = document.getElementById('tablaUsuarios');
      tbody.innerHTML = todosUsuarios.map(u => {
        const estilo = estiloRol(u.rol);
        return `
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="av">${u.nombre.charAt(0).toUpperCase()}</div>
              <div style="font-weight:600">${u.nombre}</div>
            </div>
          </td>
          <td>${u.usuario}</td>
          <td><span class="rol-badge" style="background:${estilo.bg};color:${estilo.color}">${etiquetasRol[u.rol] || u.rol}</span></td>
          <td>
            <div class="inline-actions">
              <button class="btn-primary" style="padding:5px 12px;font-size:12px" onclick='abrirModalEditar(${JSON.stringify(u)})'>✏️ Editar</button>
              <button class="btn-eliminar" onclick="eliminarUsuario(${u.id},'${u.nombre.replace(/'/g,"\\'")}')">Eliminar</button>
            </div>
          </td>
        </tr>`;
      }).join('') || '<tr><td colspan="4" style="text-align:center;color:#888;padding:20px">No hay usuarios registrados</td></tr>';
    }

    function abrirModalAgregar() {
      document.getElementById('modalTitulo').textContent = 'Nuevo Usuario';
      document.getElementById('btnGuardar').textContent = 'Guardar Usuario';
      document.getElementById('usuarioIdEditar').value = '';
      document.getElementById('fNombre').value = '';
      document.getElementById('fUsuario').value = '';
      document.getElementById('fPassword').value = '';
      document.getElementById('fPassword').placeholder = '••••••••';
      document.getElementById('lblPassword').textContent = 'CONTRASEÑA';
      document.getElementById('grupoPasswordNota').style.display = 'none';
      actualizarOpcionesRol();
      document.getElementById('fRol').value = 'Mecánico';
      document.getElementById('fRolOtro').value = '';
      toggleRolOtro();
      document.getElementById('modalUsuario').classList.add('show');
    }

    function abrirModalEditar(u) {
      document.getElementById('modalTitulo').textContent = 'Editar Usuario';
      document.getElementById('btnGuardar').textContent = 'Guardar Cambios';
      document.getElementById('usuarioIdEditar').value = u.id;
      document.getElementById('fNombre').value = u.nombre;
      document.getElementById('fUsuario').value = u.usuario;
      document.getElementById('fPassword').value = '';
      document.getElementById('fPassword').placeholder = 'Dejar en blanco para no cambiar';
      document.getElementById('lblPassword').textContent = 'NUEVA CONTRASEÑA (OPCIONAL)';
      document.getElementById('grupoPasswordNota').style.display = 'block';
      actualizarOpcionesRol();
      const sel = document.getElementById('fRol');
      // Si el rol del usuario ya existe como opción (base o personalizado ya usado), se selecciona directo.
      // Si no está en el select por alguna razón, se cae a "Otro..." y se precarga el texto.
      const existeComoOpcion = Array.from(sel.options).some(o => o.value === u.rol);
      if (existeComoOpcion) {
        sel.value = u.rol;
        document.getElementById('fRolOtro').value = '';
      } else {
        sel.value = '__otro__';
        document.getElementById('fRolOtro').value = u.rol;
      }
      toggleRolOtro();
      document.getElementById('modalUsuario').classList.add('show');
    }

    function cerrarModal() {
      document.getElementById('modalUsuario').classList.remove('show');
    }

    async function guardarUsuario() {
      const id = document.getElementById('usuarioIdEditar').value;
      const nombre = document.getElementById('fNombre').value.trim();
      const usuario = document.getElementById('fUsuario').value.trim();
      const password = document.getElementById('fPassword').value;
      let rol = document.getElementById('fRol').value;

      if (rol === '__otro__') {
        rol = document.getElementById('fRolOtro').value.trim();
        if (!rol) { alert('Escribe el nombre del rol'); return; }
      }

     limpiarErrores(['fNombre','fUsuario','fPassword']);
let valido = true;
if (!nombre) { marcarError('fNombre', 'El nombre es obligatorio'); valido = false; }
if (!usuario) { marcarError('fUsuario', 'El usuario es obligatorio'); valido = false; }
if (!id && !password) { marcarError('fPassword', 'La contraseña es obligatoria'); valido = false; }
if (!valido) return;

      const url = id ? `http://localhost:3000/usuarios/${id}` : 'http://localhost:3000/usuarios';
      const method = id ? 'PUT' : 'POST';
      const body = id ? { nombre, rol, password } : { usuario, password, nombre, rol };

      const res = await apiFetch(url, {
        method, headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
      const data = await res.json();
      if (data.ok) {
        cerrarModal();
        document.getElementById('msgOk').style.display = 'block';
        cargarUsuarios();
        setTimeout(() => document.getElementById('msgOk').style.display = 'none', 3000);
      } else {
        alert(data.error || 'No se pudo guardar el usuario');
      }
    }

    async function eliminarUsuario(id, nombre) {
      if (!confirm(`¿Seguro que deseas eliminar el acceso de "${nombre}"?`)) return;
      await apiFetch(`http://localhost:3000/usuarios/${id}`, { method: 'DELETE' });
      cargarUsuarios();
    }

    cargarUsuarios();
  </script>
</body>
</html>
