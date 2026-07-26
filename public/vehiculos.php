<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multiservicios Cárdenas - Vehículos</title>
  <link rel="stylesheet" href="global.css">
  <style>
    .filtros { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
    .filtro-btn { background:white; border:1px solid #e5e7eb; border-radius:20px; padding:6px 16px; font-size:12px; font-weight:600; cursor:pointer; transition:all .2s; color:#555; }
    .filtro-btn.active, .filtro-btn:hover { background:#00a8cc; color:white; border-color:#00a8cc; }
    .vehiculo-card { background:white; border-radius:14px; border:1px solid #e5e7eb; padding:16px; display:grid; grid-template-columns:120px 1fr auto; gap:16px; align-items:start; margin-bottom:12px; transition:box-shadow .2s; }
    .vehiculo-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.08); }
    .vehiculo-img { width:120px; height:80px; border-radius:10px; object-fit:cover; background:#f0f2f5; display:flex; align-items:center; justify-content:center; font-size:36px; }
    .placa-badge { display:inline-block; background:#f3f4f6; border:1px solid #ddd; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:700; letter-spacing:1px; margin-bottom:6px; }
    .progress-wrap { margin-top:8px; }
    .progress-label { display:flex; justify-content:space-between; font-size:11px; color:#888; margin-bottom:4px; }
    .progress-bar { height:6px; background:#e5e7eb; border-radius:3px; overflow:hidden; }
    .progress-fill { height:100%; border-radius:3px; background:#00a8cc; transition:width .4s; }
    .estado-badge { display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
    .eb-taller { background:#fff7ed; color:#e07020; }
    .eb-entregado { background:#f0fdf4; color:#16a34a; }
    .eb-pendiente { background:#eff6ff; color:#1d6fb5; }
    .card-actions { display:flex; flex-direction:column; gap:6px; }
    .alerta-box { background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:12px 16px; margin-bottom:16px; font-size:13px; color:#c05f10; display:none; }
  </style>
</head>
<body>
<?php $paginaActiva = 'vehiculos'; include 'sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      <button class="menu-btn" onclick="toggleMenu()">☰</button>
      <span style="font-size:14px;color:#555">Flota de Vehículos</span>
      <div style="margin-left:auto;display:flex;gap:10px;align-items:center">
        <span style="font-size:12px;color:#888">Total: <b id="lblTotal">0</b></span>
        <span style="background:#fff7ed;color:#e07020;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600" id="lblActivas">Reparaciones activas: 0</span>
        <button class="btn-primary" onclick="abrirModalAgregar()">+ Registrar Vehículo</button>
      </div>
    </div>
    <div class="content">
      <h1>Flota de Vehículos</h1>
      <p class="subtitle">Gestiona los vehículos activos e historial de servicios.</p>

      <div class="cards" style="margin-bottom:16px">
        <div class="card"><div class="card-label">TOTAL VEHÍCULOS</div><div class="card-val" id="totalVehiculos">0</div></div>
        <div class="card"><div class="card-label">MARCAS DISTINTAS</div><div class="card-val" id="totalMarcas">0</div></div>
        <div class="card"><div class="card-label">AÑO PROMEDIO</div><div class="card-val" id="anioPromedio">-</div></div>
        <div class="card"><div class="card-label">CON ORDEN ACTIVA</div><div class="card-val" style="color:#e07020" id="conOrden">0</div></div>
      </div>

      <!-- Filtros por marca -->
      <div class="filtros" id="filtrosMarca">
        <button class="filtro-btn active" onclick="filtrar('todos',this)">Todos</button>
      </div>

      <div class="alerta-box" id="alertaReparacion">
        ⚠️ Hay vehículos con reparaciones críticas pendientes
      </div>

      <div id="listaVehiculos"></div>

      <div class="msg-ok" id="msgOk" style="margin-top:10px">✅ Operación realizada correctamente</div>
    </div>
  </div>

  <!-- Modal agregar/editar -->
  <div class="modal-bg" id="modalVehiculo">
    <div class="modal">
      <h3 id="modalTitulo">Registrar Vehículo</h3>
      <input type="hidden" id="vehiculoIdEditar">
      <div class="form-group"><label>CLIENTE PROPIETARIO</label><select id="clienteId"><option value="">Seleccionar cliente...</option></select></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group"><label>PLACA</label><input type="text" id="placa" placeholder="ABC-123"></div>
        <div class="form-group"><label>MARCA</label><input type="text" id="marca" placeholder="Toyota"></div>
        <div class="form-group"><label>MODELO</label><input type="text" id="modelo" placeholder="Hilux"></div>
        <div class="form-group"><label>AÑO</label><input type="number" id="anio" placeholder="2022"></div>
      </div>
      <div class="form-group"><label>COLOR</label><input type="text" id="color" placeholder="Blanco"></div>
      <div class="form-group">
        <label>FOTO DEL VEHÍCULO</label>
        <div style="display:flex;align-items:center;gap:12px;margin-top:4px">
          <img id="imagenPreview" src="" style="display:none;width:80px;height:56px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb">
          <div id="imagenPlaceholder" style="width:80px;height:56px;background:#f3f4f6;border-radius:8px;border:2px dashed #d1d5db;display:flex;align-items:center;justify-content:center;font-size:22px">🚗</div>
          <div>
            <input type="file" id="inputImagen" accept="image/*" style="display:none" onchange="previewImagen(event)">
            <button type="button" class="btn-cancel" style="font-size:12px;padding:6px 14px" onclick="document.getElementById('inputImagen').click()">📷 Subir foto</button>
            <div style="font-size:11px;color:#aaa;margin-top:4px">JPG, PNG — máx. 2MB</div>
          </div>
        </div>
      </div>
      <div class="modal-btns">
        <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
        <button class="btn-primary" id="btnGuardar" onclick="guardarVehiculo()">Guardar Vehículo</button>
      </div>
    </div>
  </div>

  <script src="shared.js"></script>
  <script>
    cargarUsuario();
    let todosVehiculos = [];
    let todasOrdenes = [];

    const iconosMarca = {
      'toyota':'🚗','nissan':'🚙','hyundai':'🚘','kia':'🚖','honda':'🏎️',
      'chevrolet':'🚐','ford':'🛻','mitsubishi':'🚕','suzuki':'🛵','default':'🚗'
    };

    function iconoMarca(marca) {
      return iconosMarca[(marca||'').toLowerCase()] || iconosMarca.default;
    }

    function estadoOrden(vehiculo) {
      // Buscar orden activa por cliente_id y que el texto del vehículo coincida con placa o marca/modelo
      const orden = todasOrdenes.find(o => {
        if (o.estado === 'Finalizado') return false;
        const textoOrden = (o.vehiculo || '').toLowerCase();
        return textoOrden.includes(vehiculo.placa.toLowerCase()) ||
               textoOrden.includes(vehiculo.marca.toLowerCase()) ||
               o.cliente_id === vehiculo.cliente_id;
      });
      return orden ? orden.estado : null;
    }

    function progresoEstado(estado) {
      if (estado === 'Pendiente') return { pct: 20, label: 'Diagnóstico', color: '#e07020' };
      if (estado === 'En Proceso') return { pct: 60, label: 'En Reparación', color: '#1d6fb5' };
      if (estado === 'Completado') return { pct: 100, label: 'Listo para entrega', color: '#16a34a' };
      return null;
    }

    async function cargarDatos() {
      [todosVehiculos, todasOrdenes] = await Promise.all([
        apiFetch('http://localhost:3000/vehiculos').then(r => r.json()),
        apiFetch('http://localhost:3000/ordenes').then(r => r.json())
      ]);

      document.getElementById('totalVehiculos').textContent = todosVehiculos.length;
      document.getElementById('lblTotal').textContent = todosVehiculos.length;
      const marcas = [...new Set(todosVehiculos.map(v => v.marca).filter(Boolean))];
      document.getElementById('totalMarcas').textContent = marcas.length;
      if (todosVehiculos.length > 0) {
        const prom = Math.round(todosVehiculos.filter(v=>v.anio).reduce((s,v)=>s+v.anio,0) / todosVehiculos.filter(v=>v.anio).length);
        document.getElementById('anioPromedio').textContent = prom || '-';
      }

      // Filtros de marca
      const filtrosDiv = document.getElementById('filtrosMarca');
      filtrosDiv.innerHTML = '<button class="filtro-btn active" onclick="filtrar(\'todos\',this)">Todos</button>';
      marcas.forEach(m => {
        filtrosDiv.innerHTML += `<button class="filtro-btn" onclick="filtrar('${m}',this)">${m}</button>`;
      });

      renderVehiculos(todosVehiculos);
    }

    function renderVehiculos(vehiculos) {
      const conOrden = vehiculos.filter(v => estadoOrden(v) && estadoOrden(v) !== 'Completado' && estadoOrden(v) !== 'Finalizado').length;
      document.getElementById('conOrden').textContent = conOrden;
      document.getElementById('lblActivas').textContent = 'Reparaciones activas: ' + conOrden;
      if (conOrden > 0) document.getElementById('alertaReparacion').style.display = 'block';

      const lista = document.getElementById('listaVehiculos');
      if (vehiculos.length === 0) {
        lista.innerHTML = '<div style="text-align:center;color:#888;padding:40px">No hay vehículos registrados</div>';
        return;
      }

      lista.innerHTML = vehiculos.map(v => {
        const estado = estadoOrden(v);
        const prog = estado ? progresoEstado(estado) : null;
        const badgeClass = estado === 'En Proceso' ? 'eb-taller' : estado === 'Completado' ? 'eb-entregado' : estado === 'Pendiente' ? 'eb-pendiente' : '';
        const badgeText = estado || 'Sin orden activa';

        if (v.imagen) imagenesVehiculos[v.id] = v.imagen;
        const imgHtml = v.imagen
          ? `<img src="${v.imagen}" style="width:120px;height:80px;object-fit:cover;border-radius:10px">`
          : `<div class="vehiculo-img" style="font-size:36px;display:flex;align-items:center;justify-content:center;background:#f0f2f5;border-radius:10px;width:120px;height:80px">${iconoMarca(v.marca)}</div>`;
        return `<div class="vehiculo-card">
          <div>${imgHtml}</div>
          <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
              <span class="placa-badge">${v.placa}</span>
              <span class="estado-badge ${badgeClass}">${badgeText}</span>
            </div>
            <div style="font-size:17px;font-weight:700;color:#0f1f3d">${v.marca} ${v.modelo} ${v.anio||''}</div>
            <div style="font-size:13px;color:#888;margin-top:2px">
              Propietario: <b>${v.cliente_nombre}</b> &nbsp;|&nbsp; Color: ${v.color||'-'}
            </div>
            ${prog ? `
            <div class="progress-wrap">
              <div class="progress-label"><span>${prog.label}</span><span>${prog.pct}%</span></div>
              <div class="progress-bar"><div class="progress-fill" style="width:${prog.pct}%;background:${prog.color}"></div></div>
            </div>` : ''}
          </div>
          <div class="card-actions">
            <button class="btn-primary" style="padding:6px 14px;font-size:12px" onclick="abrirModalEditar(${v.id},'${v.placa}','${v.marca}','${v.modelo}',${v.anio||0},'${v.color||''}',${v.cliente_id},imagenesVehiculos[${v.id}])">✏️ Editar</button>
            <button class="btn-eliminar" onclick="eliminarVehiculo(${v.id})">Eliminar</button>
          </div>
        </div>`;
      }).join('');
    }

    function filtrar(marca, btn) {
      document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filtrados = marca === 'todos' ? todosVehiculos : todosVehiculos.filter(v => v.marca === marca);
      renderVehiculos(filtrados);
    }

    async function cargarClientes() {
      const clientes = await apiFetch('http://localhost:3000/clientes').then(r => r.json());
      const sel = document.getElementById('clienteId');
      const valorActual = sel.value;
      sel.innerHTML = '<option value="">Seleccionar cliente...</option>';
      clientes.forEach(c => { sel.innerHTML += `<option value="${c.id}">${c.nombre}</option>`; });
      if (valorActual) sel.value = valorActual;
    }

    function limpiarImagen() {
      document.getElementById('inputImagen').value = '';
      document.getElementById('imagenPreview').src = '';
      document.getElementById('imagenPreview').style.display = 'none';
      document.getElementById('imagenPlaceholder').style.display = 'flex';
    }

    function previewImagen(e) {
      const file = e.target.files[0];
      if (!file) return;
      if (file.size > 2 * 1024 * 1024) { alert('La imagen supera 2MB, elige una más pequeña'); return; }
      const reader = new FileReader();
      reader.onload = ev => {
        document.getElementById('imagenPreview').src = ev.target.result;
        document.getElementById('imagenPreview').style.display = 'block';
        document.getElementById('imagenPlaceholder').style.display = 'none';
      };
      reader.readAsDataURL(file);
    }

    function abrirModalAgregar() {
      document.getElementById('modalTitulo').textContent = 'Registrar Vehículo';
      document.getElementById('btnGuardar').textContent = 'Guardar Vehículo';
      document.getElementById('vehiculoIdEditar').value = '';
      ['placa','marca','modelo','anio','color'].forEach(id => document.getElementById(id).value = '');
      document.getElementById('clienteId').value = '';
      limpiarImagen();
      cargarClientes(); // Recarga la lista de clientes cada vez que abres el modal
      document.getElementById('modalVehiculo').classList.add('show');
    }

    function abrirModalEditar(id, placa, marca, modelo, anio, color, cliente_id, imagen) {
      document.getElementById('modalTitulo').textContent = 'Editar Vehículo';
      document.getElementById('btnGuardar').textContent = 'Guardar Cambios';
      document.getElementById('vehiculoIdEditar').value = id;
      document.getElementById('placa').value = placa;
      document.getElementById('marca').value = marca;
      document.getElementById('modelo').value = modelo;
      document.getElementById('anio').value = anio;
      document.getElementById('color').value = color;
      document.getElementById('clienteId').value = cliente_id;
      // Mostrar imagen actual si tiene
      if (imagen) {
        document.getElementById('imagenPreview').src = imagen;
        document.getElementById('imagenPreview').style.display = 'block';
        document.getElementById('imagenPlaceholder').style.display = 'none';
      } else {
        limpiarImagen();
      }
      document.getElementById('modalVehiculo').classList.add('show');
    }

    function cerrarModal() { document.getElementById('modalVehiculo').classList.remove('show'); }

    async function guardarVehiculo() {
      const id = document.getElementById('vehiculoIdEditar').value;
      const cliente_id = document.getElementById('clienteId').value;
      const placa = document.getElementById('placa').value;
      const marca = document.getElementById('marca').value;
      const modelo = document.getElementById('modelo').value;
      const anio = document.getElementById('anio').value;
      const color = document.getElementById('color').value;
      const imagenSrc = document.getElementById('imagenPreview').src;
      const imagen = imagenSrc && imagenSrc.startsWith('data:') ? imagenSrc : (id ? document.getElementById('imagenPreview').src : null);

     limpiarErrores(['placa','marca','modelo','clienteId']);
      let valido = true;
      if (!placa) { marcarError('placa', 'La placa es obligatoria'); valido = false; }
      if (!marca) { marcarError('marca', 'La marca es obligatoria'); valido = false; }
      if (!modelo) { marcarError('modelo', 'El modelo es obligatorio'); valido = false; }
      if (!id && !cliente_id) { marcarError('clienteId', 'Selecciona un cliente'); valido = false; }
      if (!valido) return;

      const url = id ? `http://localhost:3000/vehiculos/${id}` : 'http://localhost:3000/vehiculos';
      const method = id ? 'PUT' : 'POST';
      const body = id
        ? { placa, marca, modelo, anio, color, imagen: imagenSrc || null }
        : { cliente_id, placa, marca, modelo, anio, color, imagen: imagenSrc && imagenSrc.startsWith('data:') ? imagenSrc : null };

      const res = await apiFetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
      const data = await res.json();
      if (data.ok) {
        cerrarModal();
        document.getElementById('msgOk').style.display = 'block';
        cargarDatos();
        setTimeout(() => document.getElementById('msgOk').style.display = 'none', 3000);
      }
    }

    async function eliminarVehiculo(id) {
      if (!confirm('¿Eliminar este vehículo?')) return;
      await apiFetch(`http://localhost:3000/vehiculos/${id}`, { method: 'DELETE' });
      cargarDatos();
    }

    const imagenesVehiculos = {};

    cargarClientes();
    cargarDatos();
  </script>
</body>
</html>
