<?php
// Incluir la biblioteca FPDF
require('../../../fpdf/fpdf.php');
require_once('../../../util/Configuraciones.php');
require_once('../../../modeloNegocio/almacen/SolicitudRetiroNegocio.php');

isset($_GET["token"]) ? $idComprob = $_GET["token"] : "";

if (ObjectUtil::isEmpty($_GET["token"])) {
    echo("No se encontró solicitud de retiro");
    exit();
}

$igv = 18;
$arrayDetalle = array();

$datoDocumento = SolicitudRetiroNegocio::create()->obtenerRequerimientoXSolicitudId($idComprob);
$datoEstado = SolicitudRetiroNegocio::create()->obtenerHistorialEstadosXSolicitudId($idComprob);
if (ObjectUtil::isEmpty($datoDocumento)) {
    echo("No se encontró el documento");
    exit();
}

$fecha_retiro = $datoDocumento[0]['fecha_entrega'];

// Convertir la fecha de 'YYYY-MM-DD' a 'DD/MM/YYYY'
$fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_retiro);
$fecha_retiro = $fecha_obj->format('d/m/Y');  // Formato 'DD/MM/YYYY'


$fecha_llegada = $datoDocumento[0]['fecha_llegada'];
$fecha_obj2 = DateTime::createFromFormat('Y-m-d', $fecha_llegada);
$fecha_llegada = $fecha_obj2->format('d/m/Y');  // Formato 'DD/MM/YYYY'

//$urlComprobanteQr = 'imagenes/' . $datoDocumento[0]['archivo'];
$usuario = $datoDocumento[0]['nombre'];
$numero = $datoDocumento[0]['requerimiento'];

// Crear una nueva instancia de FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Establecer fuente
$pdf->SetFont('Arial', '', 12);

// Definir la altura de las celdas
$cellHeight = 20;  // Ajusta esta altura para que las celdas tengan el mismo tamaño

// Primera fila: 3 columnas (logo, "Requerimiento", y la tercera columna dividida en 3 filas)
$pdf->Cell(30, $cellHeight, '', 0, 0, 'C');  // Deja espacio para la imagen
$pdf->Cell(110, $cellHeight, 'REGISTRO DE REQUERIMIENTO', 1, 0, 'C');

// Insertar la imagen en la primera columna (en lugar del texto "Logo")
$imageX = 10;  // Posición X para la imagen
$imageY = 10;  // Posición Y para la imagen
$imageWidth = 26; // Ancho de la imagen
$imageHeight = 20; // Alto de la imagen (ajustado al alto de la celda)

$pdf->Image('pepas.png', $imageX, $imageY, $imageWidth, $imageHeight);

// Crear la celda dividida en 3 filas en la tercera columna
$thirdColumnHeight = $cellHeight / 3; // Dividimos la altura de la tercera columna en 3 partes iguales
$pdf->SetFont('Arial', '', 8);
// Fila 1 de la tercera columna
$pdf->Cell(50, $thirdColumnHeight, utf8_decode('Código:'), 1, 0, 'L');
$pdf->Ln();

// Fila 2 de la tercera columna
$pdf->Cell(60, 0, '', 0, 0, 'C');  // Espacio vacío para las celdas de la columna de la izquierda
$pdf->Cell(80, 0, '', 0, 0, 'C');  // Espacio vacío para las celdas de la columna de la izquierda
$pdf->Cell(50, $thirdColumnHeight, utf8_decode('Fecha de Aprobación:'), 1, 0, 'L');
$pdf->Ln();

// Fila 3 de la tercera columna
$pdf->Cell(60, 0, '', 0, 0, 'C');  // Espacio vacío para las celdas de la columna de la izquierda
$pdf->Cell(80, 0, '', 0, 0, 'C');  // Espacio vacío para las celdas de la columna de la izquierda
$pdf->Cell(50, $thirdColumnHeight, utf8_decode('Versión:'), 1, 1, 'L');

$cellHeight2 = 8;
$pdf->SetFont('Arial', '', 8);
// Segunda fila: 2 columnas

$pdf->Cell(140, $cellHeight2, utf8_decode('N° DE REQUERIMIENTO'), 1, 0, 'R');
$pdf->SetFillColor(	27, 98, 207);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(50, $cellHeight2, 'TRANSP 00'.$numero, 1, 1, 'C',1);
$pdf->SetTextColor(0, 0, 0); 
// Tercera fila: 4 columnas
$pdf->Cell(30, $cellHeight2, 'SOLICITANTE:', 1, 0, 'C');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(100, $cellHeight2, ''.$usuario, 1, 0, 'C');
$pdf->Cell(40, $cellHeight2, 'FECHA DE REQUERIMIENTO:', 1, 0, 'L');
$pdf->Cell(20, $cellHeight2, ''.$fecha_retiro, 1, 1, 'C');

// Cuarta fila: 3 columnas, con la tercera columna unida a la fila siguiente
$pdf->Cell(30, $cellHeight2, 'CARGO:', 1, 0, 'C');
$pdf->Cell(100, $cellHeight2, 'MINERO', 1, 0, 'C');
$pdf->Cell(40, $cellHeight2, utf8_decode('FECHA MÁXIMA DE ENTREGA:'), 1, 0, 'L');
$pdf->Cell(20, $cellHeight2, ''.$fecha_llegada, 1, 1, 'C');
// Quinta fila: 3 columnas, la tercera columna unida con la cuarta fila
$pdf->Cell(30, $cellHeight2, 'AREA DE TRABAJO:', 1, 0, 'C');
$pdf->Cell(160, $cellHeight2, 'TRANSPORTE', 1, 1, 'C');


// Sexta fila: 2 columnas
$pdf->Cell(30, $cellHeight2, 'PROYECTO', 1, 0, 'C');
$pdf->Cell(160, $cellHeight2, 'TRANSPORTE DE MINERAL', 1, 1, 'C');

$pdf->SetFont('Arial', '', 7);
$pdf->SetFillColor(	27, 98, 207);
$pdf->SetTextColor(255, 255, 255); // Blanco (RGB)
// Segunda tabla: 2 x 6 (2 filas x 6 columnas)
$pdf->Cell(10, $cellHeight2, utf8_decode('ÍTEM'), 1, 0, 'C',1);
$pdf->Cell(80, $cellHeight2, utf8_decode('DESCRIPCIÓN DEL PRODUCTO/SERVICIO'), 1, 0, 'C',1);
$pdf->Cell(55, $cellHeight2, utf8_decode('ESPECIFICACIÓN DEL PRODUCTO/SERVICIO'), 1, 0, 'C',1);
$pdf->Cell(15, $cellHeight2, 'CANTIDAD', 1, 0, 'C',1);
$pdf->Cell(10, $cellHeight2, 'U.M.', 1, 0, 'C',1);
$pdf->Cell(20, $cellHeight2, utf8_decode('OBSERVACIÓN'), 1, 1, 'C',1);
$pdf->SetTextColor(0, 0, 0); 
$pdf->Cell(10, $cellHeight2, '1', 1, 0, 'C');
$pdf->Cell(80, $cellHeight2, 'TRANSPORTE DE MINERAL AURIFERO EN BRUTO SIN PROCESAR', 1, 0, 'C');
$pdf->Cell(55, $cellHeight2, '-', 1, 0, 'C');
$pdf->Cell(15, $cellHeight2, '1', 1, 0, 'C');
$pdf->Cell(10, $cellHeight2, 'UND', 1, 0, 'C');
$pdf->Cell(20, $cellHeight2, 'PRIORIDAD', 1, 1, 'C');

// Obtener las fechas de las firmas
$fecha_solicitante = '';
$fecha_firma_2 = '';
$fecha_firma_3 = '';
$orden_servicio = '';
// Recorrer el array para encontrar las fechas correspondientes
foreach ($datoEstado as $row) {
    if ($row['documento_estado_id'] == 1) {
        $fecha_solicitante = $row['fecha_creacion'];  // Fecha del solicitante (estado 1)
    }
    if ($row['documento_estado_id'] == 12) {
        $fecha_firma_2 = $row['fecha_creacion'];  // Fecha de la segunda firma (estado 13)
    }
    if ($row['documento_estado_id'] == 13) {
        $fecha_firma_3 = $row['fecha_creacion'];  // Fecha de la tercera firma (estado 14)
    }
    if ($row['documento_estado_id'] == 14) {
        $orden_servicio = true;  // Fecha de la tercera firma (estado 14)
    }
}

// Convertir las fechas a formato 'DD/MM/YYYY' (si no están vacías)
$fecha_solicitante = $fecha_solicitante ? (new DateTime($fecha_solicitante))->format('d/m/Y') : '';
$fecha_firma_2 = $fecha_firma_2 ? (new DateTime($fecha_firma_2))->format('d/m/Y') : '';
$fecha_firma_3 = $fecha_firma_3 ? (new DateTime($fecha_firma_3))->format('d/m/Y') : '';

// Si el estado es 14 (estado aprobado), duplicamos la página


// Agregar las firmas y otros detalles después
$pdf->Image('firma1.png', 10, 110, 40);  // Firma 1
$pdf->SetXY(10, 130);  
$pdf->Cell(40, 5, '_________________________', 0, 1, 'C');  // Línea de separación
$pdf->Cell(40, 5, 'Firma del solicitante', 0, 1, 'C');  // Nombre de la persona 1
$pdf->Cell(40, 5, 'Fecha: ' . $fecha_solicitante, 0, 1, 'C');    // Fecha del solicitante

// Si el documento_estado_id 13 está presente, mostrar firma 2
if ($fecha_firma_2) {
    $pdf->Image('firma2.png', 80, 110, 40);  // Firma 2
    $pdf->SetXY(80, 130);
    $pdf->Cell(40, 5, '_________________________', 0, 1, 'C');  // Línea de separación
    $pdf->Cell(180, 5, 'Aprobado por jefe zona', 0, 1, 'C');  // Nombre de la persona 2
    $pdf->Cell(180, 5, 'Fecha: ' . $fecha_firma_2, 0, 1, 'C');    // Fecha de la segunda firma
}

// Si el documento_estado_id 14 está presente, mostrar firma 3
if ($fecha_firma_3) {
    $pdf->Image('firma3.png', 150, 110, 40);  // Firma 3
    $pdf->SetXY(150, 130);
    $pdf->Cell(40, 5, '_________________________', 0, 1, 'C');  // Línea de separación
    $pdf->Cell(320, 5, utf8_decode('VºBº de la Junta Directiva'), 0, 1, 'C');  // Nombre de la persona 3
    $pdf->Cell(320, 5, 'Fecha: ' . $fecha_firma_3, 0, 1, 'C');    // Fecha de la tercera firma
}


if ($orden_servicio) {
    $pdf->AddPage();  // Nueva página para la orden de servicio
    $pdf->SetFont('Arial', '', 12);

    // Definir la altura de las celdas
    $cellHeight = 20;  // Ajusta esta altura para que las celdas tengan el mismo tamaño
    
    // Primera fila: 3 columnas (logo, "Requerimiento", y la tercera columna dividida en 3 filas)
    $pdf->Cell(60, $cellHeight, '', 0, 0, 'C');  // Deja espacio para la imagen
    $pdf->Cell(75, $cellHeight, 'ORDEN DE SERVICIO', 1, 0, 'C');
    
    // Insertar la imagen en la primera columna (en lugar del texto "Logo")
    $imageX = 10;  // Posición X para la imagen
    $imageY = 10;  // Posición Y para la imagen
    $imageWidth = 26; // Ancho de la imagen
    $imageHeight = 20; // Alto de la imagen (ajustado al alto de la celda)
    
    $pdf->Image('pepas.png', $imageX, $imageY, $imageWidth, $imageHeight);
    
    // Crear la celda dividida en 3 filas en la tercera columna
    $thirdColumnHeight = $cellHeight / 4; // Dividimos la altura de la tercera columna en 3 partes iguales
    $pdf->SetFont('Arial', '', 8);
    // Fila 1 de la tercera columna
    $pdf->SetFillColor(	27, 98, 207);
$pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(55, $thirdColumnHeight, utf8_decode('O/S N° 0614 - 2024'), 1, 0, 'L',1);
    $pdf->Ln();
   
$pdf->SetTextColor(0, 0, 0);
    // Fila 2 de la tercera columna
    $pdf->Cell(60, 0, '', 0, 0, 'C');  // Espacio vacío para las celdas de la columna de la izquierda
    $pdf->Cell(75, 0, '', 0, 0, 'C');  // Espacio vacío para las celdas de la columna de la izquierda
    $pdf->Cell(55, $thirdColumnHeight, utf8_decode('FECHA: 28/11/2024'), 1, 0, 'L');
    $pdf->Ln();
    
    // Fila 3 de la tercera columna
    $pdf->Cell(60, 0, '', 0, 0, 'C');  // Espacio vacío para las celdas de la columna de la izquierda
    $pdf->Cell(75, 0, '', 0, 0, 'C');  // Espacio vacío para las celdas de la columna de la izquierda
    $pdf->Cell(55, $thirdColumnHeight, utf8_decode('REQUERIMIENTO N° TRANSP - 0614'), 1, 0, 'L');
    $pdf->Ln();

    $pdf->Cell(60, 0, '', 0, 0, 'C');  // Espacio vacío para las celdas de la columna de la izquierda
    $pdf->Cell(75, 0, '', 0, 0, 'C');  // Espacio vacío para las celdas de la columna de la izquierda
    $pdf->Cell(55, $thirdColumnHeight, utf8_decode('N° ACTA DE RETIRO DE MINERAL 5622'), 1, 1, 'L');
    $cellHeight2 = 8;
    $pdf->Cell(190, $cellHeight2, utf8_decode(' DATOS DEL PROVEEDOR'), 1, 1, 'L');
    $pdf->SetFillColor(	27, 98, 207);
    $pdf->SetTextColor(255, 255, 255);

    $pdf->SetTextColor(0, 0, 0); 
    // Tercera fila: 4 columnas
    $pdf->Cell(40, $cellHeight2, 'NOMBRE:', 1, 0, 'C');
    $pdf->Cell(150, $cellHeight2, 'W & M SOLUCIONES S.A.C.', 1, 1, 'L');
   
    // Cuarta fila: 3 columnas, con la tercera columna unida a la fila siguiente
    $pdf->Cell(40, $cellHeight2, 'RUC:', 1, 0, 'C');
    $pdf->Cell(150, $cellHeight2, '20607424986', 1, 1, 'L');
   
    // Quinta fila: 3 columnas, la tercera columna unida con la cuarta fila
    $pdf->Cell(40, $cellHeight2, utf8_decode('DIRECCIÓN:'), 1, 0, 'C');
    $pdf->Cell(150, $cellHeight2, 'URB. SOLILUZ MZA.E LOTE.27 TRUJILLO- TRUJILLO - LA LIBERTAD', 1, 1, 'L');

    $pdf->Cell(40, $cellHeight2, utf8_decode('DISTRITO:'), 1, 0, 'C');
    $pdf->Cell(150, $cellHeight2, 'TRUJILLO', 1, 1, 'L');


    $pdf->Cell(40, $cellHeight2, utf8_decode('PROVINCIA:'), 1, 0, 'C');
    $pdf->Cell(150, $cellHeight2, 'TRUJILLO', 1, 1, 'L');

// ** Tabla de precios (productos o servicios facturados) **
$pdf->Ln(10); // Dejar un espacio antes de la tabla
$pdf->SetFont('Arial', '', 7);
$pdf->SetFillColor(	27, 98, 207);
$pdf->SetTextColor(255, 255, 255); // Blanco (RGB)
// Segunda tabla: 2 x 6 (2 filas x 6 columnas)
$pdf->Cell(15, $cellHeight2, utf8_decode('ÍTEM'), 1, 0, 'C',1);
$pdf->Cell(90, $cellHeight2, utf8_decode('DESCRIPCIÓN Y ESPECIFICACIONES'), 1, 0, 'C',1);

$pdf->Cell(15, $cellHeight2, 'CANTIDAD', 1, 0, 'C',1);
$pdf->Cell(20, $cellHeight2, 'U.M.', 1, 0, 'C',1);
$pdf->Cell(25, $cellHeight2, utf8_decode('PREC. UNT.'), 1, 0, 'C',1);
$pdf->Cell(25, $cellHeight2, utf8_decode('TOTAL'), 1, 1, 'C',1);
$pdf->SetTextColor(0, 0, 0); 
$pdf->Cell(15, $cellHeight2, '1', 1, 0, 'C');
$pdf->Cell(90, $cellHeight2, 'TRANSPORTE DE MINERAL AURIFERO EN BRUTO SIN PROCESAR', 1, 0, 'C');
$pdf->Cell(15, $cellHeight2, '1.98', 1, 0, 'C');
$pdf->Cell(20, $cellHeight2, 'UND', 1, 0, 'C');
$pdf->Cell(25, $cellHeight2, 'S/ 211.86', 1, 0, 'C');
$pdf->Cell(25, $cellHeight2, 'S/ 419.49', 1, 1, 'C');


$pdf->Cell(120, $cellHeight2, '', 0, 0, 'C');
$pdf->Cell(45, $cellHeight2, 'V. VENTA', 1, 0, 'C');
$pdf->Cell(25, $cellHeight2, 'S/ 419.49', 1, 1, 'C');

$pdf->Cell(120, $cellHeight2, '', 0, 0, 'C');
$pdf->Cell(45, $cellHeight2, 'IGV (18%)', 1, 0, 'C');
$pdf->Cell(25, $cellHeight2, 'S/ 75.51', 1, 1, 'C');

$pdf->SetFillColor(	27, 98, 207);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(120, $cellHeight2, '', 0, 0, 'C');
$pdf->Cell(45, $cellHeight2, 'TOTAL A PAGAR :', 1, 0, 'C',1);
$pdf->Cell(25, $cellHeight2, 'S/ 495.00', 1, 1, 'C',1);
// ** Sumar total (subtotales y total final)**
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(5);

$pdf->SetFillColor(	27, 98, 207);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(190, $cellHeight2, 'CONDICIONES DE PAGO:', 1, 1, 'L',1);
$pdf->SetTextColor(0, 0, 0);
// Cuarta fila: 3 columnas, con la tercera columna unida a la fila siguiente
$pdf->Cell(190, $cellHeight2, utf8_decode('Depósito a cuenta: INTERBANK N° 616-3003117399'), 1, 1, 'L');

// Quinta fila: 3 columnas, la tercera columna unida con la cuarta fila
$pdf->Cell(190, $cellHeight2, utf8_decode('Moneda: SOLES'), 1, 1, 'L');
    
$pdf->Image('firma3.png', 150, 190, 40);  // Firma 3
$pdf->SetXY(150, 210);
$pdf->Cell(40, 5, '_________________________', 0, 1, 'C');  // Línea de separación
$pdf->Cell(320, 5, utf8_decode('VºBº de la Junta Directiva'), 0, 1, 'C');  // Nombre de la persona 3
$pdf->Cell(320, 5, 'Fecha: ' . $fecha_firma_3, 0, 1, 'C');    // Fecha de la tercera firma
}
$pdf->Output('documento_requerimiento.pdf', 'I');
?> 
