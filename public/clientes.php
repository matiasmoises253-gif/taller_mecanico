<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multiservicios Cárdenas - Clientes</title>
  <link rel="stylesheet" href="global.css">
  <style>
    .buscador { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
    .buscador input { border:1px solid #e5e7eb; border-radius:8px; padding:9px 14px; font-size:13px; outline:none; width:260px; }
    .buscador input:focus { border-color:#00a8cc; }
    .historial-badge { background:#f0f9ff; color:#1d6fb5; border:none; border-radius:6px; padding:5px 10px; font-size:12px; cursor:pointer; font-weight:600; }
    .historial-badge:hover { background:#e0f0ff; }
    /* Modal historial */
    #modalHistorial .modal { width:560px; max-width:95vw; }
    .historial-item { border-left:3px solid #00a8cc; padding:10px 14px; margin-bottom:10px; background:#f8fafc; border-radius:0 8px 8px 0; }
    .historial-item .hi-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; }
    .historial-item .hi-desc { font-size:12px; color:#555; }
    .hi-estado { font-size:11px; font-weight:700; padding:3px 8px; border-radius:20px; }
    .hi-estado.Pendiente { background:#fef3c7; color:#b45309; }
    .hi-estado.En\ Proceso { background:#dbeafe; color:#1d4ed8; }
    .hi-estado.Completado { background:#dcfce7; color:#15803d; }
  </style>
</head>
<body>
<?php $paginaActiva = 'clientes'; include 'sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      <button class="menu-btn" onclick="toggleMenu()">☰</button>
      <span style="font-size:14px;color:#555">Directorio de Clientes</span>
      <button class="btn-primary" style="margin-left:auto" onclick="abrirModalAgregar()">+ Agregar Cliente</button>
    </div>
    <div class="content">
      <h1>Directorio de Clientes</h1>
      <p class="subtitle">Gestiona las relaciones con tus clientes.</p>

      <div class="cards">
        <div class="card">
          <div class="card-label">TOTAL CLIENTES</div>
          <div class="card-val" id="totalClientes">0</div>
        </div>
        <div class="card">
          <div class="card-label">ACTIVOS ESTE MES</div>
          <div class="card-val" style="color:#00a8cc" id="activosMes">0</div>
          <div style="font-size:11px;color:#888;margin-top:4px">Registrados este mes</div>
        </div>
        <div class="card">
          <div class="card-label">EN TALLER AHORA</div>
          <div class="card-val" style="color:#e07020" id="enTaller">0</div>
          <div style="font-size:11px;color:#888;margin-top:4px">Con orden en proceso</div>
        </div>
        <div class="card">
          <div class="card-label">SERVICIOS COMPLETADOS</div>
          <div class="card-val" style="color:#16a34a" id="completados">0</div>
          <div style="font-size:11px;color:#888;margin-top:4px">Clientes atendidos</div>
        </div>
      </div>

      <div class="panel">
        <div class="msg-ok" id="msgOk">✅ Operación realizada correctamente</div>

        <!-- BÚSQUEDA -->
        <div class="buscador">
          <span style="font-size:15px">🔍</span>
          <input type="text" id="campoBusqueda" placeholder="Buscar cliente por nombre..." oninput="filtrarClientes()">
          <span id="contadorFiltro" style="font-size:12px;color:#888"></span>
        </div>

        <table>
          <thead>
            <tr><th>CLIENTE</th><th>TELÉFONO</th><th>CORREO</th><th>ACCIONES</th></tr>
          </thead>
          <tbody id="tablaClientes"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal agregar/editar cliente -->
  <div class="modal-bg" id="modalCliente">
    <div class="modal">
      <h3 id="modalTitulo">Agregar Nuevo Cliente</h3>
      <input type="hidden" id="clienteIdEditar">
      <div class="form-group"><label>NOMBRE COMPLETO</label><input type="text" id="nombre" placeholder="Ej: Juan Delgado"></div>
      <div class="form-group"><label>TELÉFONO</label><input type="text" id="telefono" placeholder="+51 987 654 321"></div>
      <div class="form-group"><label>CORREO ELECTRÓNICO</label><input type="email" id="email" placeholder="correo@ejemplo.com"></div>
      <div class="modal-btns">
        <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
        <button class="btn-primary" id="btnGuardar" onclick="guardarCliente()">Guardar Cliente</button>
      </div>
    </div>
  </div>

  <!-- Modal historial del cliente -->
  <div class="modal-bg" id="modalHistorial">
    <div class="modal">
      <h3 id="historialTitulo">Historial de Órdenes</h3>
      <div id="historialContenido"></div>
      <div class="modal-btns">
        <button class="btn-cancel" onclick="document.getElementById('modalHistorial').classList.remove('show')">Cerrar</button>
      </div>
    </div>
  </div>

  <script src="shared.js"></script>
  <script>
    cargarUsuario();
    let todosClientes = [];

    async function cargarEstadisticas() {
      const stats = await apiFetch('http://localhost:3000/estadisticas').then(r => r.json());
      document.getElementById('totalClientes').textContent = stats.total;
      document.getElementById('activosMes').textContent = stats.activos_mes;
      document.getElementById('enTaller').textContent = stats.en_proceso;
      document.getElementById('completados').textContent = stats.completados;
    }

    async function cargarClientes() {
      todosClientes = await apiFetch('http://localhost:3000/clientes').then(r => r.json());
      renderTabla(todosClientes);
    }

    function filtrarClientes() {
      const q = document.getElementById('campoBusqueda').value.toLowerCase().trim();
      const filtrados = q ? todosClientes.filter(c => c.nombre.toLowerCase().includes(q)) : todosClientes;
      document.getElementById('contadorFiltro').textContent = q ? `${filtrados.length} resultado(s)` : '';
      renderTabla(filtrados);
    }

    function renderTabla(clientes) {
      const tbody = document.getElementById('tablaClientes');
      tbody.innerHTML = clientes.map(c => `
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="av">${c.nombre.charAt(0).toUpperCase()}</div>
              <div>
                <div style="font-weight:600">${c.nombre}</div>
                <button class="historial-badge" style="margin-top:4px" onclick="verHistorial(${c.id},'${c.nombre.replace(/'/g,"\\'")}')">📋 Ver historial</button>
              </div>
            </div>
          </td>
          <td>${c.telefono}</td>
          <td>${c.email}</td>
          <td>
            <div class="inline-actions">
              <button class="btn-primary" style="padding:5px 12px;font-size:12px" onclick="abrirModalEditar(${c.id},'${c.nombre.replace(/'/g,"\\'")}','${c.telefono}','${c.email}')">✏️ Editar</button>
              <button class="btn-eliminar" onclick="eliminarCliente(${c.id})">Eliminar</button>
            </div>
          </td>
        </tr>
      `).join('') || '<tr><td colspan="4" style="text-align:center;color:#888;padding:20px">No hay clientes que coincidan</td></tr>';
    }

    async function verHistorial(clienteId, nombre) {
      document.getElementById('historialTitulo').textContent = `Historial de ${nombre}`;
      document.getElementById('historialContenido').innerHTML = '<p style="color:#888;font-size:13px">Cargando...</p>';
      document.getElementById('modalHistorial').classList.add('show');

      const ordenes = await apiFetch(`http://localhost:3000/clientes/${clienteId}/ordenes`).then(r => r.json());

      if (!ordenes.length) {
        document.getElementById('historialContenido').innerHTML = '<p style="color:#888;font-size:13px;text-align:center;padding:20px">Este cliente no tiene órdenes registradas.</p>';
        return;
      }

      document.getElementById('historialContenido').innerHTML = ordenes.map(o => `
        <div class="historial-item">
          <div class="hi-head">
            <span style="font-weight:700;font-size:13px">🔧 ${o.vehiculo}</span>
            <span class="hi-estado ${o.estado}">${o.estado}</span>
          </div>
          <div class="hi-desc">${o.descripcion}</div>
          <div style="font-size:11px;color:#999;margin-top:4px">📅 ${o.fecha}</div>
        </div>
      `).join('');
    }

    function abrirModalAgregar() {
      document.getElementById('modalTitulo').textContent = 'Agregar Nuevo Cliente';
      document.getElementById('btnGuardar').textContent = 'Guardar Cliente';
      document.getElementById('clienteIdEditar').value = '';
      document.getElementById('nombre').value = '';
      document.getElementById('telefono').value = '';
      document.getElementById('email').value = '';
      document.getElementById('modalCliente').classList.add('show');
    }

    function abrirModalEditar(id, nombre, telefono, email) {
      document.getElementById('modalTitulo').textContent = 'Editar Cliente';
      document.getElementById('btnGuardar').textContent = 'Guardar Cambios';
      document.getElementById('clienteIdEditar').value = id;
      document.getElementById('nombre').value = nombre;
      document.getElementById('telefono').value = telefono;
      document.getElementById('email').value = email;
      document.getElementById('modalCliente').classList.add('show');
    }

    function cerrarModal() {
      document.getElementById('modalCliente').classList.remove('show');
    }

    async function guardarCliente() {
  const id = document.getElementById('clienteIdEditar').value;
  const nombre = document.getElementById('nombre').value;
  const telefono = document.getElementById('telefono').value;
  const email = document.getElementById('email').value;

  limpiarErrores(['nombre','telefono','email']);
  let valido = true;
  if (!nombre) { marcarError('nombre', 'El nombre es obligatorio'); valido = false; }
  if (!telefono) { marcarError('telefono', 'El teléfono es obligatorio'); valido = false; }
  if (!email) { marcarError('email', 'El correo es obligatorio'); valido = false; }
  if (!valido) return;
  
      const url = id ? `http://localhost:3000/clientes/${id}` : 'http://localhost:3000/clientes';
      const method = id ? 'PUT' : 'POST';

      const res = await apiFetch(url, {
        method, headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre, telefono, email })
      });
      const data = await res.json();
      if (data.ok) {
        cerrarModal();
        document.getElementById('msgOk').style.display = 'block';
        cargarClientes();
        cargarEstadisticas();
        setTimeout(() => document.getElementById('msgOk').style.display = 'none', 3000);
      }
    }

    async function eliminarCliente(id) {
      if (!confirm('¿Seguro que deseas eliminar este cliente?')) return;
      await apiFetch(`http://localhost:3000/clientes/${id}`, { method: 'DELETE' });
      cargarClientes();
      cargarEstadisticas();
    }

    cargarEstadisticas();
    cargarClientes();
  </script>
</body>
</html>
