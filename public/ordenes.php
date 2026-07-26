<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multiservicios Cárdenas - Órdenes</title>
  <link rel="stylesheet" href="global.css">
  <style>
    .dos-col { display: grid; grid-template-columns: 1fr 280px; gap: 14px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .total-box { background: #f0f7ff; border-radius: 10px; padding: 14px; display: flex; justify-content: space-between; align-items: center; margin-top: 14px; }
    .total-val { font-size: 26px; font-weight: 700; color: #00a8cc; }
    .estado-box { display: flex; flex-direction: column; gap: 10px; }
    .estado-item { display: flex; justify-content: space-between; align-items: center; }
    .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 6px; }
    .btn-estado { border: none; border-radius: 6px; padding: 4px 10px; font-size: 11px; font-weight: 600; cursor: pointer; }
    .btn-pendiente { background: #fff7ed; color: #e07020; }
    .btn-proceso { background: #eff6ff; color: #1d6fb5; }
    .btn-completado { background: #f0fdf4; color: #16a34a; }
    @media (max-width: 768px) { .dos-col { grid-template-columns: 1fr; } .form-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
<?php $paginaActiva = 'ordenes'; include 'sidebar.php'; ?>
 
  <div class="main">
    <div class="topbar">
      <button class="menu-btn" onclick="toggleMenu()">☰</button>
      <span style="font-size:14px;color:#555">Gestión de Órdenes de Trabajo</span>
      <button class="btn-primary" style="margin-left:auto" onclick="scrollTo(0,0)">+ Nueva Orden</button>
    </div>
    <div class="content">
      <h1>Órdenes de Trabajo</h1>
      <p class="subtitle">Crea y gestiona las órdenes de reparación en tiempo real.</p>
 
      <div class="dos-col">
        <div>
          <div class="panel">
            <div class="panel-title">
              <span>📋 Crear Nueva Orden</span>
              <span style="background:#eff6ff;color:#1d6fb5;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700" id="numOrden">#OT-2026-0001</span>
            </div>
            <div class="msg-ok" id="msgOk">✅ Orden creada correctamente</div>
            <div class="form-grid">
              <div class="form-group"><label>CLIENTE</label><select id="clienteId" onchange="cargarVehiculosCliente()"><option value="">Seleccionar cliente...</option></select></div>
              <div class="form-group"><label>VEHÍCULO</label><select id="vehiculoId" style="background:#f9fafb;color:#888"><option value="">Primero selecciona un cliente...</option></select></div>
            </div>
            <div class="form-group"><label>DESCRIPCIÓN DEL SERVICIO</label><textarea id="descripcion" placeholder="Describe el servicio o reparación..."></textarea></div>
            <div class="form-group">
              <label>REPUESTOS (SELECCIONA DEL INVENTARIO)</label>
              <select id="selectRepuesto" onchange="agregarRepuesto()"><option value="">Seleccionar repuesto...</option></select>
              <div style="font-size:11px;color:#888;margin-top:4px">Selecciona uno y se agrega solo. Repite para agregar más repuestos.</div>
              <div id="listaRepuestos" style="margin-top:10px;display:flex;flex-direction:column;gap:6px"></div>
            </div>
            <div class="form-grid">
              <div class="form-group"><label>FECHA</label><input type="date" id="fecha"></div>
              <div class="form-group"><label>MANO DE OBRA (S/.)</label><input type="number" id="mano_obra" placeholder="0.00" step="0.01" oninput="actualizarTotal()"></div>
            </div>
            <div class="form-grid">
              <div class="form-group"><label>SUBTOTAL REPUESTOS (S/.)</label><input type="number" id="costo_repuestos" placeholder="0.00" step="0.01" readonly style="background:#f9fafb;color:#888"></div>
              <div class="form-group"><label>COSTO TOTAL (S/.)</label><input type="number" id="costo" placeholder="0.00" step="0.01" readonly style="background:#f9fafb;color:#888"></div>
            </div>
            <div class="total-box">
              <div>
                <div style="font-size:11px;color:#888;font-weight:600">TOTAL ESTIMADO</div>
                <div class="total-val" id="totalMostrar">S/. 0.00</div>
              </div>
              <div style="display:flex;gap:10px">
                <button class="btn-cancel" onclick="limpiarForm()">Limpiar</button>
                <button class="btn-primary" onclick="guardarOrden()">Generar Orden</button>
              </div>
            </div>
          </div>
 
          <div class="panel">
            <div class="panel-title">Órdenes Registradas</div>
            <table>
              <thead><tr><th>ORDEN</th><th>CLIENTE</th><th>VEHÍCULO</th><th>ESTADO</th><th>FECHA</th><th>MANO OBRA</th><th>REPUESTOS</th><th>TOTAL</th><th>CAMBIAR ESTADO</th></tr></thead>
              <tbody id="tablaOrdenes"></tbody>
            </table>
          </div>
        </div>
 
        <div>
          <div class="panel">
            <div class="panel-title">Estado Rápido</div>
            <div class="estado-box">
              <div class="estado-item"><div><span class="dot" style="background:#e07020"></span>Pendiente</div><span style="font-size:13px;font-weight:700;color:#e07020" id="cntPendiente">0</span></div>
              <div class="estado-item"><div><span class="dot" style="background:#1d6fb5"></span>En Proceso</div><span style="font-size:13px;font-weight:700;color:#1d6fb5" id="cntProceso">0</span></div>
              <div class="estado-item"><div><span class="dot" style="background:#16a34a"></span>Completado</div><span style="font-size:13px;font-weight:700;color:#16a34a" id="cntCompletado">0</span></div>
              <div class="estado-item"><div><span class="dot" style="background:#6b7280"></span>Finalizado</div><span style="font-size:13px;font-weight:700;color:#6b7280" id="cntFinalizado">0</span></div>
            </div>
          </div>
          <div class="panel" style="background:#003d5c">
            <div style="color:white;font-size:13px;font-weight:700;margin-bottom:10px">💡 Consejo</div>
            <div style="color:#aab;font-size:12px;line-height:1.6">Usa los botones de estado para actualizar el progreso de cada reparación en tiempo real.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
 
  <script src="shared.js"></script>
  <script>
    cargarUsuario();
    document.getElementById('fecha').valueAsDate = new Date();

    let inventarioCache = [];
    let repuestosSeleccionados = []; // { inventario_id, nombre, cantidad, precio, stockDisponible }

    async function cargarInventarioSelect() {
      inventarioCache = await apiFetch('http://localhost:3000/inventario').then(r => r.json());
      const sel = document.getElementById('selectRepuesto');
      sel.innerHTML = '<option value="">Seleccionar repuesto...</option>' +
        inventarioCache.map(i => {
          const usado = repuestosSeleccionados.find(r => r.inventario_id === i.id);
          const disponible = i.cantidad - (usado ? usado.cantidad : 0);
          const sinStock = disponible <= 0;
          return `<option value="${i.id}" ${sinStock ? 'disabled' : ''}>${i.nombre} — Stock: ${disponible} — S/. ${parseFloat(i.precio).toFixed(2)}${sinStock ? ' (sin stock)' : ''}</option>`;
        }).join('');
    }

    function agregarRepuesto() {
      const sel = document.getElementById('selectRepuesto');
      const id = parseInt(sel.value);
      if (!id) return;
      const item = inventarioCache.find(i => i.id === id);
      if (!item) return;

      const existente = repuestosSeleccionados.find(r => r.inventario_id === id);
      if (existente) {
        if (existente.cantidad < item.cantidad) existente.cantidad++;
      } else {
        if (item.cantidad <= 0) { alert('Este repuesto no tiene stock disponible'); return; }
        repuestosSeleccionados.push({ inventario_id: id, nombre: item.nombre, cantidad: 1, precio: parseFloat(item.precio) || 0, stockDisponible: item.cantidad });
      }
      sel.value = '';
      renderRepuestos();
      cargarInventarioSelect();
    }

    function cambiarCantidadRepuesto(id, valor) {
      const r = repuestosSeleccionados.find(x => x.inventario_id === id);
      if (!r) return;
      let cant = parseInt(valor) || 1;
      if (cant < 1) cant = 1;
      if (cant > r.stockDisponible) { cant = r.stockDisponible; alert(`Solo hay ${r.stockDisponible} unidades de "${r.nombre}" en stock`); }
      r.cantidad = cant;
      renderRepuestos();
      cargarInventarioSelect();
    }

    function quitarRepuesto(id) {
      repuestosSeleccionados = repuestosSeleccionados.filter(r => r.inventario_id !== id);
      renderRepuestos();
      cargarInventarioSelect();
    }

    function renderRepuestos() {
      const cont = document.getElementById('listaRepuestos');
      cont.innerHTML = repuestosSeleccionados.length ? repuestosSeleccionados.map(r => `
        <div style="display:flex;align-items:center;gap:8px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px">
          <div style="flex:1;font-size:12px;font-weight:600">${r.nombre}</div>
          <input type="number" value="${r.cantidad}" min="1" max="${r.stockDisponible}" style="width:55px;border:1px solid #e5e7eb;border-radius:6px;padding:3px 5px;font-size:12px"
            onchange="cambiarCantidadRepuesto(${r.inventario_id}, this.value)">
          <div style="font-size:12px;color:#888;width:80px;text-align:right">S/. ${r.precio.toFixed(2)} c/u</div>
          <div style="font-size:12px;font-weight:700;color:#00a8cc;width:75px;text-align:right">S/. ${(r.cantidad*r.precio).toFixed(2)}</div>
          <button type="button" onclick="quitarRepuesto(${r.inventario_id})" style="background:#fef2f2;color:#dc2626;border:none;border-radius:6px;padding:4px 8px;font-size:11px;font-weight:700;cursor:pointer">✕</button>
        </div>
      `).join('') : '<div style="font-size:12px;color:#888">Aún no has agregado repuestos</div>';
      recalcularRepuestos();
    }

    function recalcularRepuestos() {
      const subtotal = repuestosSeleccionados.reduce((s,r) => s + r.cantidad*r.precio, 0);
      document.getElementById('costo_repuestos').value = subtotal.toFixed(2);
      actualizarTotal();
    }

    function actualizarTotal() {
      const mo = parseFloat(document.getElementById('mano_obra').value) || 0;
      const rep = parseFloat(document.getElementById('costo_repuestos').value) || 0;
      const total = mo + rep;
      document.getElementById('costo').value = total.toFixed(2);
      document.getElementById('totalMostrar').textContent = 'S/. ' + total.toFixed(2);
    }

    async function cargarClientes() {
      const clientes = await apiFetch('http://localhost:3000/clientes').then(r => r.json());
      const sel = document.getElementById('clienteId');
      clientes.forEach(c => {
        const op = document.createElement('option');
        op.value = c.id; op.textContent = c.nombre;
        sel.appendChild(op);
      });
    }

    async function cargarVehiculosCliente() {
      const clienteId = document.getElementById('clienteId').value;
      const selV = document.getElementById('vehiculoId');
      selV.innerHTML = '<option value="">Cargando...</option>';
      selV.style.color = '#888';
      if (!clienteId) { selV.innerHTML = '<option value="">Primero selecciona un cliente...</option>'; return; }
      const todos = await apiFetch('http://localhost:3000/vehiculos').then(r => r.json());
      const vehiculos = todos.filter(v => v.cliente_id == clienteId);
      if (!vehiculos.length) { selV.innerHTML = '<option value="">Sin vehículos registrados</option>'; return; }
      selV.style.color = '#0f1f3d';
      selV.innerHTML = '<option value="">Seleccionar vehículo...</option>' +
        vehiculos.map(v => `<option value="${v.placa} ${v.marca} ${v.modelo} ${v.anio}">${v.marca} ${v.modelo} ${v.anio} — ${v.placa}</option>`).join('');
    }

    async function cargarOrdenes() {
      const ordenes = await apiFetch('http://localhost:3000/ordenes').then(r => r.json());
      document.getElementById('numOrden').textContent = '#OT-' + new Date().getFullYear() + '-' + String(ordenes.length + 1).padStart(4, '0');
      document.getElementById('cntPendiente').textContent = ordenes.filter(o => o.estado === 'Pendiente').length;
      document.getElementById('cntProceso').textContent = ordenes.filter(o => o.estado === 'En Proceso').length;
      document.getElementById('cntCompletado').textContent = ordenes.filter(o => o.estado === 'Completado').length;
      document.getElementById('cntFinalizado').textContent = ordenes.filter(o => o.estado === 'Finalizado').length;

      const activas = ordenes.filter(o => o.estado !== 'Finalizado');
      const tbody = document.getElementById('tablaOrdenes');
      tbody.innerHTML = activas.map((o, i) => `
        <tr>
          <td>#OT-${new Date().getFullYear()}-${String(i+1).padStart(4,'0')}</td>
          <td>${o.cliente_nombre}</td>
          <td>${o.vehiculo}</td>
          <td><span class="status ${o.estado==='Completado'?'s-green':o.estado==='En Proceso'?'s-blue':'s-orange'}">${o.estado}</span></td>
          <td>${o.fecha ? o.fecha.substring(0,10) : '-'}</td>
          <td>
            <input type="number" value="${parseFloat(o.mano_obra||0).toFixed(2)}" min="0" step="0.01"
              style="width:70px;border:1px solid #e5e7eb;border-radius:6px;padding:3px 5px;font-size:11px;outline:none"
              onchange="actualizarDesglose(${o.id},'mano_obra',this.value)"
              onfocus="this.style.borderColor='#00a8cc'" onblur="this.style.borderColor='#e5e7eb'">
          </td>
          <td>
            <input type="number" value="${parseFloat(o.costo_repuestos||0).toFixed(2)}" min="0" step="0.01"
              style="width:70px;border:1px solid #e5e7eb;border-radius:6px;padding:3px 5px;font-size:11px;outline:none"
              onchange="actualizarDesglose(${o.id},'costo_repuestos',this.value)"
              onfocus="this.style.borderColor='#00a8cc'" onblur="this.style.borderColor='#e5e7eb'">
          </td>
          <td style="font-weight:700;color:#00a8cc">S/.${(parseFloat(o.mano_obra||0)+parseFloat(o.costo_repuestos||0)).toFixed(2)}</td>
          <td style="display:flex;gap:6px;align-items:center">
            <select onchange="cambiarEstado(${o.id}, this.value)" style="border:1px solid #e5e7eb;border-radius:6px;padding:4px 8px;font-size:12px;outline:none;cursor:pointer">
              <option ${o.estado==='Pendiente'?'selected':''}>Pendiente</option>
              <option ${o.estado==='En Proceso'?'selected':''}>En Proceso</option>
              <option ${o.estado==='Completado'?'selected':''}>Completado</option>
            </select>
            ${o.estado==='Completado'?`<button onclick="finalizarOrden(${o.id})" style="background:#6b7280;color:white;border:none;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:600;cursor:pointer">✓ Finalizar</button>`:''}
            <button onclick="imprimirOrden(${o.id})" style="background:#003d5c;color:white;border:none;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:600;cursor:pointer">🖨️ PDF</button>
            </td>
        </tr>
      `).join('') || '<tr><td colspan="9" style="text-align:center;color:#888;padding:20px">No hay órdenes activas</td></tr>';
    }
async function imprimirOrden(id) {
  const res = await apiFetch(`http://localhost:3000/ordenes/${id}/pdf`);
  if (!res.ok) { alert('No se pudo generar el PDF'); return; }
  const blob = await res.blob();
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `Orden_${id}.pdf`;
  a.click();
}
    async function actualizarDesglose(id, campo, valor) {
      await apiFetch(`http://localhost:3000/ordenes/${id}/desglose`, {
        method: 'PUT', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ campo, valor: parseFloat(valor) || 0 })
      });
    }

    async function cambiarEstado(id, estado) {
      await apiFetch(`http://localhost:3000/ordenes/${id}/estado`, {
        method: 'PUT', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ estado })
      });
      cargarOrdenes();
    }

    async function finalizarOrden(id) {
      if (!confirm('¿Finalizar esta orden? Desaparecerá de la lista pero quedará en el historial del cliente.')) return;
      await apiFetch(`http://localhost:3000/ordenes/${id}/estado`, {
        method: 'PUT', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ estado: 'Finalizado' })
      });
      cargarOrdenes();
    }

    async function guardarOrden() {
  const cliente_id = document.getElementById('clienteId').value;
  const vehiculo = document.getElementById('vehiculoId').value;
  const descripcion = document.getElementById('descripcion').value;
  const fecha = document.getElementById('fecha').value;
  const mano_obra = parseFloat(document.getElementById('mano_obra').value) || 0;
  const costo_repuestos = parseFloat(document.getElementById('costo_repuestos').value) || 0;
  const costo = mano_obra + costo_repuestos;

  limpiarErrores(['clienteId','vehiculoId','descripcion','fecha']);
  let valido = true;
  if (!cliente_id) { marcarError('clienteId', 'Selecciona un cliente'); valido = false; }
  if (!vehiculo) { marcarError('vehiculoId', 'Selecciona un vehículo'); valido = false; }
  if (!descripcion) { marcarError('descripcion', 'La descripción es obligatoria'); valido = false; }
  if (!fecha) { marcarError('fecha', 'La fecha es obligatoria'); valido = false; }
  if (!valido) return;

      const repuestos = repuestosSeleccionados.map(r => ({ inventario_id: r.inventario_id, nombre: r.nombre, cantidad: r.cantidad, precio: r.precio }));
      const res = await apiFetch('http://localhost:3000/ordenes', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cliente_id, vehiculo, descripcion, fecha, costo, mano_obra, costo_repuestos, repuestos })
      });
      const data = await res.json();
      if (data.ok) {
        if (data.aviso) alert(data.aviso);
        document.getElementById('msgOk').style.display = 'block';
        limpiarForm(); cargarOrdenes(); cargarInventarioSelect();
        setTimeout(() => document.getElementById('msgOk').style.display = 'none', 3000);
      }
    }

    function limpiarForm() {
      document.getElementById('clienteId').value = '';
      document.getElementById('vehiculoId').innerHTML = '<option value="">Primero selecciona un cliente...</option>';
      document.getElementById('vehiculoId').style.color = '#888';
      document.getElementById('descripcion').value = '';
      document.getElementById('mano_obra').value = '';
      document.getElementById('costo_repuestos').value = '';
      document.getElementById('costo').value = '';
      document.getElementById('totalMostrar').textContent = 'S/. 0.00';
      document.getElementById('fecha').valueAsDate = new Date();
      repuestosSeleccionados = [];
      renderRepuestos();
    }

    cargarClientes();
    cargarOrdenes();
    cargarInventarioSelect();
  </script>
</body>
</html>