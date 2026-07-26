<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Multiservicios Cárdenas - Reportes</title>
  <link rel="stylesheet" href="global.css">
  <style>
    .filtros-periodo { display:flex; gap:8px; margin-bottom:20px; }
    .btn-periodo { background:white; border:1px solid #e5e7eb; border-radius:20px; padding:7px 20px; font-size:13px; font-weight:600; cursor:pointer; color:#555; transition:all .2s; }
    .btn-periodo.active { background:#00a8cc; color:white; border-color:#00a8cc; }
    .reportes-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .grafica-wrap { background:white; border-radius:14px; border:1px solid #e5e7eb; padding:20px; }
    .grafica-titulo { font-size:13px; font-weight:700; color:#0f1f3d; margin-bottom:4px; }
    .grafica-sub { font-size:11px; color:#888; margin-bottom:16px; }
    .barras { display:flex; align-items:flex-end; gap:8px; height:160px; padding-bottom:24px; position:relative; border-bottom:2px solid #f3f4f6; }
    .barra-wrap { display:flex; flex-direction:column; align-items:center; flex:1; height:100%; justify-content:flex-end; }
    .barra { width:100%; border-radius:6px 6px 0 0; transition:height .5s; cursor:pointer; min-height:4px; }
    .barra:hover { opacity:.8; }
    .barra-label { font-size:10px; color:#888; margin-top:6px; }
    .barra-val { font-size:10px; color:#555; font-weight:600; margin-bottom:3px; }
    .transacciones-tabla th { font-size:11px; }
    .transacciones-tabla td { font-size:12px; padding:10px 8px; }
    .categoria-row { margin-bottom:14px; }
    .categoria-header { display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px; }
    .categoria-bar { height:8px; background:#e5e7eb; border-radius:4px; overflow:hidden; }
    .categoria-fill { height:100%; border-radius:4px; transition:width .6s; }
    .resumen-num { font-size:28px; font-weight:700; }
    .resumen-label { font-size:11px; color:#888; font-weight:600; margin-top:2px; }
    .resumen-diff { font-size:12px; font-weight:600; margin-top:4px; }
    @media(max-width:768px) { .reportes-grid { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<?php $paginaActiva = 'reportes'; include 'sidebar.php'; ?>

<div class="main">
  <div class="topbar">
    <button class="menu-btn" onclick="toggleMenu()">☰</button>
    <span style="font-size:14px;color:#555">Reportes Financieros</span>
    <div style="margin-left:auto;display:flex;gap:8px">
      <button class="btn-cancel" onclick="exportarExcel()" style="background:#1d6f42;color:white;border-color:#1d6f42">📊 Exportar Excel</button>
      <button class="btn-cancel" onclick="exportarWord()" style="background:#1d4ed8;color:white;border-color:#1d4ed8">📄 Exportar Word</button>
    </div>
  </div>
  <div class="content">
    <h1>Reportes Financieros</h1>
    <p class="subtitle">Analiza el rendimiento y tendencias de ingresos del taller.</p>

    <!-- Filtros de periodo -->
    <div class="filtros-periodo">
      <button class="btn-periodo active" onclick="cambiarPeriodo('mes', this)">Mensual</button>
      <button class="btn-periodo" onclick="cambiarPeriodo('semana', this)">Semanal</button>
      <button class="btn-periodo" onclick="cambiarPeriodo('dia', this)">Diario</button>
    </div>

    <!-- Tarjetas resumen -->
    <div class="cards" style="margin-bottom:16px">
      <div class="card">
        <div class="card-label">INGRESOS TOTALES</div>
        <div class="resumen-num" style="color:#00a8cc" id="totalIngresos">S/. 0</div>
        <div class="resumen-diff" style="color:#16a34a" id="diffIngresos"></div>
      </div>
      <div class="card">
        <div class="card-label">ÓRDENES DE SERVICIO</div>
        <div class="resumen-num" id="totalOrdenes">0</div>
        <div class="resumen-label">en el periodo</div>
      </div>
      <div class="card">
        <div class="card-label">TICKET PROMEDIO</div>
        <div class="resumen-num" style="color:#e07020" id="ticketPromedio">S/. 0</div>
        <div class="resumen-label">por orden completada</div>
      </div>
      <div class="card">
        <div class="card-label">COMPLETADAS</div>
        <div class="resumen-num" style="color:#16a34a" id="completadas">0</div>
        <div class="resumen-label">órdenes finalizadas</div>
      </div>
    </div>

    <!-- Desglose económico -->
    <div class="grafica-wrap" style="margin-bottom:14px">
      <div class="grafica-titulo">💰 Desglose Económico</div>
      <div class="grafica-sub">Ingresos vs Gastos del periodo</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px" id="desgloseWrap">
        <div>
          <div style="font-size:12px;font-weight:700;color:#16a34a;margin-bottom:10px;border-bottom:2px solid #16a34a;padding-bottom:4px">INGRESOS</div>
          <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:13px"><span>Mano de Obra</span><span style="font-weight:600" id="dManoObra">S/. 0.00</span></div>
          <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:13px"><span>Venta de Repuestos</span><span style="font-weight:600" id="dRepuestosIng">S/. 0.00</span></div>
          <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:13px;font-weight:700;color:#16a34a"><span>TOTAL INGRESOS</span><span id="dTotalIng">S/. 0.00</span></div>
        </div>
        <div>
          <div style="font-size:12px;font-weight:700;color:#dc2626;margin-bottom:10px;border-bottom:2px solid #dc2626;padding-bottom:4px">GASTOS</div>
          <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:13px"><span>Compra de Repuestos</span><span style="font-weight:600" id="dGastosInv">S/. 0.00</span></div>
          <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:13px;font-weight:700;color:#dc2626"><span>TOTAL GASTOS</span><span id="dTotalGas">S/. 0.00</span></div>
          <div style="display:flex;justify-content:space-between;padding:10px;margin-top:10px;background:#f0fdf4;border-radius:8px;font-size:14px;font-weight:700;color:#15803d"><span>GANANCIA NETA</span><span id="dGanancia">S/. 0.00</span></div>
        </div>
      </div>
    </div>

    <!-- Gráfica + categorías -->
    <div class="reportes-grid" style="margin-bottom:14px">
      <div class="grafica-wrap">
        <div class="grafica-titulo">📈 Crecimiento de Ingresos</div>
        <div class="grafica-sub" id="graficaSub">Distribución por semana</div>
        <div class="barras" id="barrasGrafica"></div>
      </div>

      <div class="grafica-wrap">
        <div class="grafica-titulo">🗂️ Ingresos por Estado</div>
        <div class="grafica-sub">Distribución de órdenes por estado</div>
        <div id="categoriasWrap"></div>
      </div>
    </div>

    <!-- Transacciones recientes -->
    <div class="panel">
      <div class="panel-title">
        <span>🧾 Transacciones Recientes</span>
        <span style="font-size:12px;color:#888;font-weight:400" id="lblPeriodo">Último mes</span>
      </div>
      <table class="transacciones-tabla">
        <thead><tr><th>FECHA</th><th>CLIENTE</th><th>VEHÍCULO</th><th>DESCRIPCIÓN</th><th>ESTADO</th><th>MONTO</th></tr></thead>
        <tbody id="tablaTransacciones"></tbody>
      </table>
    </div>
  </div>
</div>

<script src="shared.js"></script>
<script>
  cargarUsuario();
  let periodoActual = 'mes';
  let datosActuales = null;

  async function cargarReportes(periodo) {
    const res = await apiFetch(`http://localhost:3000/reportes?periodo=${periodo}`);
    const data = await res.json();
    datosActuales = data;

    // Tarjetas
    document.getElementById('totalIngresos').textContent = 'S/. ' + parseFloat(data.total).toLocaleString('es-PE', {minimumFractionDigits:2});
    document.getElementById('totalOrdenes').textContent = data.total_ordenes;
    document.getElementById('ticketPromedio').textContent = 'S/. ' + parseFloat(data.ticket_promedio).toLocaleString('es-PE', {minimumFractionDigits:2});
    document.getElementById('completadas').textContent = data.completadas;

    // Desglose económico
    if (data.desglose) {
      const d = data.desglose;
      document.getElementById('dManoObra').textContent = 'S/. ' + parseFloat(d.mano_obra).toFixed(2);
      document.getElementById('dRepuestosIng').textContent = 'S/. ' + parseFloat(d.repuestos_ingreso).toFixed(2);
      document.getElementById('dTotalIng').textContent = 'S/. ' + parseFloat(d.total_ingresos).toFixed(2);
      document.getElementById('dGastosInv').textContent = 'S/. ' + parseFloat(d.gastos_inventario).toFixed(2);
      document.getElementById('dTotalGas').textContent = 'S/. ' + parseFloat(d.gastos_inventario).toFixed(2);
      const ganancia = parseFloat(d.ganancia_neta);
      const elGan = document.getElementById('dGanancia');
      elGan.textContent = 'S/. ' + ganancia.toFixed(2);
      elGan.style.color = ganancia >= 0 ? '#15803d' : '#dc2626';
      elGan.parentElement.style.background = ganancia >= 0 ? '#f0fdf4' : '#fef2f2';
    }

    // Gráfica de barras
    const porSemana = data.por_semana;
    const labels = Object.keys(porSemana);
    const valores = Object.values(porSemana);
    const maxVal = Math.max(...valores, 1);
    const colores = ['#00a8cc','#1d6fb5','#00a8cc','#1d6fb5','#00a8cc','#1d6fb5','#e07020'];

    document.getElementById('barrasGrafica').innerHTML = labels.length
      ? labels.map((label, i) => {
          const pct = Math.max(4, Math.round((valores[i] / maxVal) * 100));
          return `<div class="barra-wrap">
            <div class="barra-val">S/${valores[i].toFixed(0)}</div>
            <div class="barra" style="height:${pct}%;background:${colores[i % colores.length]}" title="${label}: S/${valores[i].toFixed(2)}"></div>
            <div class="barra-label">${label}</div>
          </div>`;
        }).join('')
      : '<div style="color:#888;font-size:13px;text-align:center;width:100%;padding:40px 0">Sin datos para este período</div>';

    // Distribución por estado
    const recientes = data.recientes || [];
    const estados = ['Pendiente','En Proceso','Completado','Finalizado'];
    const coloresEstado = { 'Pendiente':'#e07020','En Proceso':'#1d6fb5','Completado':'#16a34a','Finalizado':'#6b7280' };
    const total = data.total_ordenes || 1;
    document.getElementById('categoriasWrap').innerHTML = estados.map(est => {
      const cnt = recientes.filter(r => r.estado === est).length;
      const pct = Math.round((cnt / total) * 100);
      return `<div class="categoria-row">
        <div class="categoria-header">
          <span style="font-weight:600;font-size:13px">${est}</span>
          <span style="font-weight:700;color:${coloresEstado[est]}">${pct}% <span style="color:#888;font-weight:400">(${cnt})</span></span>
        </div>
        <div class="categoria-bar"><div class="categoria-fill" style="width:${pct}%;background:${coloresEstado[est]}"></div></div>
      </div>`;
    }).join('');

    // Tabla transacciones
    const lbls = { mes:'Último mes', semana:'Última semana', dia:'Hoy' };
    document.getElementById('lblPeriodo').textContent = lbls[periodo];
    document.getElementById('tablaTransacciones').innerHTML = recientes.length
      ? recientes.map(r => `<tr>
          <td>${r.fecha}</td>
          <td>${r.cliente}</td>
          <td style="font-size:11px">${r.vehiculo}</td>
          <td style="max-width:160px;font-size:11px;color:#555">${r.descripcion}</td>
          <td><span class="status ${r.estado==='Completado'||r.estado==='Finalizado'?'s-green':r.estado==='En Proceso'?'s-blue':'s-orange'}">${r.estado}</span></td>
          <td style="font-weight:700;color:#00a8cc">S/. ${parseFloat(r.monto).toFixed(2)}</td>
        </tr>`).join('')
      : '<tr><td colspan="6" style="text-align:center;color:#888;padding:20px">Sin transacciones en este período</td></tr>';
  }

  function cambiarPeriodo(periodo, btn) {
    periodoActual = periodo;
    document.querySelectorAll('.btn-periodo').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    cargarReportes(periodo);
  }

async function exportarExcel() {
  const res = await apiFetch(`http://localhost:3000/reportes/excel?periodo=${periodoActual}`);
  if (!res.ok) { alert('No se pudo exportar el reporte'); return; }
  const blob = await res.blob();
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `Reporte_${periodoActual}_${new Date().toISOString().split('T')[0]}.xlsx`;
  a.click();
}

async function exportarWord() {
  const res = await apiFetch(`http://localhost:3000/reportes/word?periodo=${periodoActual}`);
  if (!res.ok) { alert('No se pudo exportar el reporte'); return; }
  const blob = await res.blob();
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `Reporte_${periodoActual}_${new Date().toISOString().split('T')[0]}.docx`;
  a.click();
}

  cargarReportes('mes');
</script>
</body>
</html>
