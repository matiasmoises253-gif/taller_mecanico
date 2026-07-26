const { 
  Document, Packer, Paragraph, Table, TableRow, TableCell, TextRun,
  HeadingLevel, AlignmentType, WidthType, ShadingType, BorderStyle,
  PageOrientation
} = require('docx');
const fs = require('fs');

const datos = JSON.parse(fs.readFileSync(require('path').join(__dirname, 'reporte_datos.json'), 'utf8'));
const { resumen, recientes, porSemana, porEstado, desglose, periodo } = datos;

const periodoLabel = { mes: 'Mensual', semana: 'Semanal', dia: 'Diario' }[periodo] || 'Mensual';
const fechaHoy = new Date().toLocaleDateString('es-PE', { day:'2-digit', month:'long', year:'numeric' });

function celda(texto, bold = false, color = '000000', bgColor = null, align = AlignmentType.LEFT) {
  const cell = new TableCell({
    children: [new Paragraph({
      alignment: align,
      children: [new TextRun({ text: String(texto), bold, color, size: 20, font: 'Arial' })]
    })],
    shading: bgColor ? { type: ShadingType.CLEAR, fill: bgColor } : undefined,
    margins: { top: 80, bottom: 80, left: 100, right: 100 }
  });
  return cell;
}

function fila(...celdas) { return new TableRow({ children: celdas }); }

function tablaKPI() {
  return new Table({
    columnWidths: [4500, 4500],
    width: { size: 9000, type: WidthType.DXA },
    rows: [
      fila(celda('INDICADOR OPERATIVO / FINANCIERO', true, 'FFFFFF', '003366', AlignmentType.CENTER),
           celda('VALOR REGISTRADO', true, 'FFFFFF', '003366', AlignmentType.CENTER)),
      fila(celda('Ingresos Totales', true, '003366'), celda(`S/. ${parseFloat(resumen.total).toLocaleString('es-PE', {minimumFractionDigits:2})}`, true, '008000')),
      fila(celda('Órdenes de Servicio Registradas'), celda(String(resumen.total_ordenes))),
      fila(celda('Órdenes Finalizadas'), celda(String(resumen.completadas))),
      fila(celda('Ticket Promedio'), celda(`S/. ${parseFloat(resumen.ticket_promedio).toLocaleString('es-PE', {minimumFractionDigits:2})}`)),
    ]
  });
}

function tablaDesglose() {
  if (!desglose) return null;
  const gananciaNeta = parseFloat(desglose.ganancia_neta);
  const colorFondoGanancia = gananciaNeta >= 0 ? '16a34a' : 'CC0000';
  return new Table({
    columnWidths: [4500, 4500],
    width: { size: 9000, type: WidthType.DXA },
    rows: [
      fila(celda('CONCEPTO', true, 'FFFFFF', '003366', AlignmentType.CENTER),
           celda('MONTO', true, 'FFFFFF', '003366', AlignmentType.CENTER)),
      fila(celda('Ingresos por Mano de Obra', true, '16a34a'), celda(`S/. ${parseFloat(desglose.mano_obra).toFixed(2)}`)),
      fila(celda('Ingresos por Venta de Repuestos', true, '16a34a'), celda(`S/. ${parseFloat(desglose.repuestos_ingreso).toFixed(2)}`)),
      fila(celda('Total de Ingresos', true, '003366', 'E8F4FA'), celda(`S/. ${parseFloat(desglose.total_ingresos).toFixed(2)}`, true, '008000', 'E8F4FA')),
      fila(celda('Gastos por Compra de Repuestos (Inventario)', true, 'dc2626'), celda(`S/. ${parseFloat(desglose.gastos_inventario).toFixed(2)}`)),
      fila(celda('GANANCIA NETA', true, 'FFFFFF', '003366'), celda(`S/. ${parseFloat(desglose.ganancia_neta).toFixed(2)}`, true, 'FFFFFF', colorFondoGanancia)),
    ]
  });
}

function tablaEstados() {
  if (!porEstado) return null;
  const total = Object.values(porEstado).reduce((s, v) => s + v, 0) || 1;
  return new Table({
    columnWidths: [3000, 3000, 3000],
    width: { size: 9000, type: WidthType.DXA },
    rows: [
      fila(celda('ESTADO', true, 'FFFFFF', '003366', AlignmentType.CENTER),
           celda('ÓRDENES', true, 'FFFFFF', '003366', AlignmentType.CENTER),
           celda('PORCENTAJE', true, 'FFFFFF', '003366', AlignmentType.CENTER)),
      ...Object.entries(porEstado).map(([estado, cnt]) => fila(
        celda(estado),
        celda(String(cnt), false, '000000', null, AlignmentType.CENTER),
        celda(`${Math.round((cnt/total)*100)}%`, false, '000000', null, AlignmentType.CENTER)
      ))
    ]
  });
}

function tablaSemanas() {
  const semanas = Object.entries(porSemana);
  const total = semanas.reduce((s, [,v]) => s + v, 0);
  return new Table({
    columnWidths: [4500, 4500],
    width: { size: 9000, type: WidthType.DXA },
    rows: [
      fila(celda('PERIODO', true, 'FFFFFF', '003366', AlignmentType.CENTER),
           celda('INGRESOS', true, 'FFFFFF', '003366', AlignmentType.CENTER)),
      ...semanas.map(([sem, val]) => fila(celda(sem), celda(`S/. ${parseFloat(val).toFixed(2)}`))),
      fila(celda('TOTAL', true, '003366'), celda(`S/. ${parseFloat(total).toFixed(2)}`, true, '008000'))
    ]
  });
}

function tablaTransacciones() {
  return new Table({
    columnWidths: [1400, 1600, 1800, 2000, 1200, 1000],
    width: { size: 9000, type: WidthType.DXA },
    rows: [
      fila(
        celda('FECHA', true, 'FFFFFF', '003366', AlignmentType.CENTER),
        celda('CLIENTE', true, 'FFFFFF', '003366', AlignmentType.CENTER),
        celda('VEHÍCULO', true, 'FFFFFF', '003366', AlignmentType.CENTER),
        celda('DESCRIPCIÓN', true, 'FFFFFF', '003366', AlignmentType.CENTER),
        celda('ESTADO', true, 'FFFFFF', '003366', AlignmentType.CENTER),
        celda('MONTO', true, 'FFFFFF', '003366', AlignmentType.CENTER)
      ),
      ...recientes.map(r => fila(
        celda(r.fecha), celda(r.cliente), celda(r.vehiculo),
        celda(r.descripcion), celda(r.estado),
        celda(`S/. ${parseFloat(r.monto).toFixed(2)}`, false, '008000')
      ))
    ]
  });
}

const doc = new Document({
  sections: [{
    properties: {},
    children: [
      new Paragraph({ children: [new TextRun({ text: `REPORTE ${periodoLabel.toUpperCase()} DE RENDIMIENTO`, bold: true, size: 36, font: 'Arial', color: '003366' })], alignment: AlignmentType.CENTER }),
      new Paragraph({ children: [new TextRun({ text: `Multiservicios Cárdenas  |  Gestión y Control Financiero`, size: 22, font: 'Arial', color: '666666', italics: true })], alignment: AlignmentType.CENTER }),
      new Paragraph({ text: '' }),
      new Paragraph({ children: [new TextRun({ text: '1. Información General', bold: true, size: 26, font: 'Arial', color: '003366' })], heading: HeadingLevel.HEADING_2 }),
      new Table({
        columnWidths: [4500, 4500],
        width: { size: 9000, type: WidthType.DXA },
        rows: [
          fila(celda('Periodo seleccionado:', true), celda(`${periodoLabel}`)),
          fila(celda('Fecha de generación:', true), celda(fechaHoy)),
          fila(celda('Usuario emisor:', true), celda('Administrador del Sistema')),
        ]
      }),
      new Paragraph({ text: '' }),
      new Paragraph({ children: [new TextRun({ text: '2. Indicadores Clave de Rendimiento (KPIs)', bold: true, size: 26, font: 'Arial', color: '003366' })], heading: HeadingLevel.HEADING_2 }),
      tablaKPI(),
      new Paragraph({ text: '' }),
      new Paragraph({ children: [new TextRun({ text: '3. Desglose Económico (Ingresos, Gastos y Ganancia Neta)', bold: true, size: 26, font: 'Arial', color: '003366' })], heading: HeadingLevel.HEADING_2 }),
      ...(tablaDesglose() ? [tablaDesglose()] : [new Paragraph({ children: [new TextRun({ text: 'Sin datos de desglose económico para este periodo.', italics: true, color: '888888' })] })]),
      new Paragraph({ text: '' }),
      new Paragraph({ children: [new TextRun({ text: '4. Ingresos por Periodo', bold: true, size: 26, font: 'Arial', color: '003366' })], heading: HeadingLevel.HEADING_2 }),
      tablaSemanas(),
      new Paragraph({ text: '' }),
      new Paragraph({ children: [new TextRun({ text: '5. Distribución de Órdenes por Estado', bold: true, size: 26, font: 'Arial', color: '003366' })], heading: HeadingLevel.HEADING_2 }),
      ...(tablaEstados() ? [tablaEstados()] : [new Paragraph({ children: [new TextRun({ text: 'Sin datos de estados para este periodo.', italics: true, color: '888888' })] })]),
      new Paragraph({ text: '' }),
      new Paragraph({ children: [new TextRun({ text: '6. Últimas Transacciones', bold: true, size: 26, font: 'Arial', color: '003366' })], heading: HeadingLevel.HEADING_2 }),
      tablaTransacciones(),
      new Paragraph({ text: '' }),
      new Paragraph({ children: [new TextRun({ text: '— Fin del Reporte —', italics: true, size: 18, color: '999999' })], alignment: AlignmentType.CENTER }),
    ]
  }]
});

Packer.toBuffer(doc).then(buffer => {
  fs.writeFileSync(require('path').join(__dirname, 'reporte_output.docx'), buffer);
  console.log('OK');
});
