const PDFDocument = require('pdfkit');
const fs = require('fs');
const path = require('path');

const datos = JSON.parse(fs.readFileSync(path.join(__dirname, 'orden_datos.json'), 'utf8'));

const doc = new PDFDocument({ margin: 50 });
doc.pipe(fs.createWriteStream(path.join(__dirname, 'orden_output.pdf')));

// Encabezado del taller
doc.fontSize(18).fillColor('#003d5c').text(datos.taller.nombre || 'Multiservicios Cárdenas', { align: 'left' });
doc.fontSize(10).fillColor('#555').text(datos.taller.direccion || '', { align: 'left' });
doc.text(`${datos.taller.ciudad || ''}  |  Tel: ${datos.taller.telefono || ''}`, { align: 'left' });
doc.moveDown();

doc.fontSize(14).fillColor('#00a8cc').text(`ORDEN DE TRABAJO #OT-${datos.anio}-${String(datos.numero).padStart(4, '0')}`, { align: 'right' });
doc.fontSize(10).fillColor('#888').text(`Fecha: ${datos.fecha}`, { align: 'right' });
doc.moveDown(1.5);

doc.moveTo(50, doc.y).lineTo(545, doc.y).strokeColor('#e5e7eb').stroke();
doc.moveDown();

// Datos del cliente y vehículo
doc.fontSize(11).fillColor('#003d5c').text('Cliente:', { continued: true }).fillColor('#333').text(' ' + datos.cliente_nombre);
doc.fillColor('#003d5c').text('Vehículo:', { continued: true }).fillColor('#333').text(' ' + datos.vehiculo);
doc.fillColor('#003d5c').text('Estado:', { continued: true }).fillColor('#333').text(' ' + datos.estado);
doc.moveDown();

doc.fillColor('#003d5c').fontSize(11).text('Descripción del servicio:');
doc.fillColor('#333').fontSize(10).text(datos.descripcion, { width: 495 });
doc.moveDown();

// Tabla de repuestos utilizados
if (datos.repuestos && datos.repuestos.length) {
  doc.fontSize(11).fillColor('#003d5c').text('Repuestos utilizados:');
  doc.moveDown(0.3);
  const top = doc.y;
  doc.fontSize(9).fillColor('#888');
  doc.text('Repuesto', 50, top, { width: 220 });
  doc.text('Cant.', 270, top, { width: 60 });
  doc.text('P. Unit.', 330, top, { width: 90 });
  doc.text('Subtotal', 420, top, { width: 90 });
  doc.moveDown(0.5);
  doc.moveTo(50, doc.y).lineTo(545, doc.y).strokeColor('#e5e7eb').stroke();
  doc.moveDown(0.3);

  datos.repuestos.forEach(r => {
    const y = doc.y;
    doc.fontSize(9).fillColor('#333');
    doc.text(r.nombre, 50, y, { width: 220 });
    doc.text(String(r.cantidad), 270, y, { width: 60 });
    doc.text('S/. ' + parseFloat(r.precio_unitario).toFixed(2), 330, y, { width: 90 });
    doc.text('S/. ' + parseFloat(r.subtotal).toFixed(2), 420, y, { width: 90 });
    doc.moveDown(0.5);
  });
  doc.moveDown();
}

// Totales
doc.moveTo(50, doc.y).lineTo(545, doc.y).strokeColor('#e5e7eb').stroke();
doc.moveDown(0.5);
doc.fontSize(10).fillColor('#555').text(`Mano de obra: S/. ${parseFloat(datos.mano_obra).toFixed(2)}`, { align: 'right' });
doc.text(`Repuestos: S/. ${parseFloat(datos.costo_repuestos).toFixed(2)}`, { align: 'right' });
doc.fontSize(14).fillColor('#00a8cc').text(`TOTAL: S/. ${parseFloat(datos.costo).toFixed(2)}`, { align: 'right' });

doc.moveDown(3);
doc.fontSize(9).fillColor('#aaa').text('Documento generado por el sistema de gestión del taller.', { align: 'center' });

doc.end();
