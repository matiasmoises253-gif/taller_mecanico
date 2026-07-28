<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multiservicios Cárdenas - Panel</title>
  <link rel="stylesheet" href="global.css">
</head>
<body>
<?php $paginaActiva = 'dashboard'; include 'sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      <button class="menu-btn" onclick="toggleMenu()">☰</button>
      <span style="font-size:14px;color:#555">Bienvenido al panel de control</span>
      <button class="btn-primary" style="margin-left:auto" onclick="location.href='ordenes.php'">+ Nueva Orden de Trabajo</button>
    </div>
    <div class="content">
      <h1>Resumen Operacional</h1>
      <p class="subtitle">Estado en tiempo real de Multiservicios Cárdenas.</p>

      <div class="cards">
        <div class="card">
          <div class="card-label">TOTAL CLIENTES</div>
          <span class="card-badge" id="badgeClientes" style="color:#00a8cc">0 nuevos</span>
          <div class="card-val" id="totalClientes">0</div>
        </div>
        <div class="card">
          <div class="card-label">VEHÍCULOS REGISTRADOS</div>
          <div class="card-val" id="totalVehiculos">0</div>
        </div>
        <div class="card">
          <div class="card-label">ÓRDENES ACTIVAS</div>
          <span class="card-badge" id="ordenesEnProceso" style="color:#e07020">0 en proceso</span>
          <div class="card-val" id="totalOrdenes">0</div>
        </div>
        <div class="card" id="cardIngresos">
          <div class="card-label">COMPLETADAS HOY</div>
          <div class="card-val" style="color:#16a34a" id="completadasHoy">0</div>
        </div>
      </div>

      <!-- Resumen de estados -->
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
        <div class="panel" style="text-align:center;padding:20px">
          <div style="font-size:32px;font-weight:700;color:#e07020" id="cntPendiente">0</div>
          <div style="font-size:12px;color:#888;margin-top:4px"> Órdenes Pendientes</div>
        </div>
        <div class="panel" style="text-align:center;padding:20px">
          <div style="font-size:32px;font-weight:700;color:#1d6fb5" id="cntProceso">0</div>
          <div style="font-size:12px;color:#888;margin-top:4px"> En Proceso</div>
        </div>
        <div class="panel" style="text-align:center;padding:20px">
          <div style="font-size:32px;font-weight:700;color:#16a34a" id="cntCompletado">0</div>
          <div style="font-size:12px;color:#888;margin-top:4px"> Completadas</div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-title">
          Actividades Recientes
          <span class="panel-link" onclick="location.href='ordenes.php'">Ver Todo</span>
        </div>
        <table>
          <thead><tr><th>ORDEN</th><th>CLIENTE</th><th>VEHÍCULO</th><th>ESTADO</th><th>FECHA</th></tr></thead>
          <tbody id="tablaOrdenes"></tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="shared.js"></script>
  <script>
    cargarUsuario();

    async function cargarDatos() {
      // Clientes
      const clientes = await apiFetch('http://localhost:3000/clientes').then(r => r.json());
      document.getElementById('totalClientes').textContent = clientes.length;
      const ahora = new Date();
      const esteMes = clientes.filter(c => {
        const f = new Date(c.creado_en);
        return f.getMonth() === ahora.getMonth() && f.getFullYear() === ahora.getFullYear();
      }).length;
      document.getElementById('badgeClientes').textContent = '+' + esteMes + ' nuevos';

      // Vehículos
      const vehiculos = await apiFetch('http://localhost:3000/vehiculos').then(r => r.json());
      document.getElementById('totalVehiculos').textContent = vehiculos.length;

      // Órdenes
      const ordenes = await apiFetch('http://localhost:3000/ordenes').then(r => r.json());
      const pendientes = ordenes.filter(o => o.estado === 'Pendiente').length;
      const enProceso = ordenes.filter(o => o.estado === 'En Proceso').length;
      const completadas = ordenes.filter(o => o.estado === 'Completado').length;

      document.getElementById('totalOrdenes').textContent = ordenes.length;
      document.getElementById('ordenesEnProceso').textContent = enProceso + ' en proceso';
      document.getElementById('cntPendiente').textContent = pendientes;
      document.getElementById('cntProceso').textContent = enProceso;
      document.getElementById('cntCompletado').textContent = completadas;
      document.getElementById('completadasHoy').textContent = completadas;

      // Tabla
      const tbody = document.getElementById('tablaOrdenes');
      tbody.innerHTML = ordenes.slice(0, 5).map((o, i) => `
        <tr>
          <td>#OT-${new Date().getFullYear()}-${String(i+1).padStart(4,'0')}</td>
          <td>${o.cliente_nombre}</td>
          <td>${o.vehiculo}</td>
          <td><span class="status ${o.estado==='Completado'?'s-green':o.estado==='En Proceso'?'s-blue':'s-orange'}">${o.estado}</span></td>
          <td>${o.fecha ? o.fecha.substring(0,10) : '-'}</td>
        </tr>
      `).join('') || '<tr><td colspan="5" style="text-align:center;color:#888;padding:20px">No hay órdenes aún</td></tr>';
    }

    if (localStorage.getItem('rol') === 'recepcionista') {
      document.getElementById('cardIngresos').style.display = 'none';
    }

    cargarDatos();
  </script>
</body>
</html>
