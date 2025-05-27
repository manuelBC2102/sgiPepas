<?php
require_once('../../../fpdf/fpdf.php');
require_once('../../../modeloNegocio/almacen/MovimientoNegocio.php');
require_once('../../../modeloNegocio/almacen/DocumentoTipoNegocio.php');
require_once('../../../modeloNegocio/almacen/PersonaNegocio.php');
require_once('../../../modeloNegocio/almacen/DocumentoNegocio.php');
require_once('../../../modelo/almacen/Almacenes.php');

$documentoTipoId = 265;

isset($_GET["id"]) ? $documentoId = $_GET["id"] : "";
if (ObjectUtil::isEmpty($_GET["id"]) || $documentoId == "") {
    echo ("No se encontró Orden de compra o Servicio");
    exit();
}


$data = MovimientoNegocio::create()->imprimir($documentoId, $documentoTipoId);

$dataDocumento = $data->dataDocumento;
$documentoDatoValor = $data->documentoDatoValor;
$detalle = $data->detalle;

$dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);

$ubigeoProveedor = PersonaNegocio::create()->obtenerUbigeoXId($dataDocumento[0]["ubigeo_id"]);

$transportista = "";
$almacen_origen = "";
$almacen_destino = "";
$nro_placa = "";
foreach ($documentoDatoValor as $index => $item) {
    switch ($item['tipo'] * 1) {
        case 23:
            $transportista = $item['valor'];
            break;
        case 53:
            $almacen_origen = $item['valor'];
            break;
        case 54:
            $almacen_destino = $item['valor'];
            break;  
        case 2:
            $nro_placa = Almacenes::create()->getDataVehiculoXPlacaId($item['valor'])[0]['placa']; 
            break;        
    }
}

$pdf = new FPDF('P', 'mm', 'A4');

$pdf->SetTitle(strtoupper(utf8_decode($dataDocumentoTipo[0]['descripcion'])));
$pdf->SetAuthor('Soluciones Mineras S.A.C.');
$pdf->SetCreator('Soluciones Mineras S.A.C.');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

$pdf->AddPage();

// Logo
$pdf->Image(__DIR__ . '/../../../vistas/images/logo_pepas_de_oro.png', 150, 10, 45, 25);

// Título
$pdf->SetFont('Arial', 'B', 10);
$titulo = strtoupper("ASOCIACION DE MINEROS ARTESANALES PEPAS DE ORO DE");
$pdf->Cell(170, 10, $titulo, 0, 1, 'C');
$titulo = strtoupper("PAMPAMARCA");
$pdf->Cell(170, 0, $titulo, 0, 1, 'C');

// Dirección
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, 29);
$pdf->Cell(120, 3, 'PZA.PLAZA DE ARMAS PAMPAMARCA NRO. S/N ANX. PAMPAMARCA', 0, 1);
$pdf->SetX(10);
$pdf->Cell(120, 3, '(COMUNIDAD DE PAMPAMARCA) APURIMAC - AYMARAES - COTARUSE', 0, 1);
$pdf->SetX(10);
$pdf->Cell(120, 3, '(051) 950398232', 0, 1);

// Fecha y número
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(115, 34);
$pdf->Cell(30, 5, 'Fecha', 0, 0, 'C');
$pdf->Cell(50, 5, date_format(date_create($dataDocumento[0]['fecha_emision']), 'd/m/Y'), 0, 1, 'C');
$pdf->SetXY(115, 38);
$pdf->Cell(30, 5, utf8_decode('No. Despacho'), 0, 0, 'C');
$pdf->Cell(50, 5, $dataDocumento[0]['serie'] . " - " . $dataDocumento[0]['numero'], 0, 1, 'C');


$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY(10, 45);
$pdf->Cell(30, 5, utf8_decode('Almacén origen :'), 0, 0, 'L');
$pdf->SetXY(10, 52);
$pdf->Cell(30, 5, utf8_decode('Almacén destino :'), 0, 0, 'L');
$pdf->SetXY(10, 59);
$pdf->Cell(30, 5, utf8_decode('N° Placa :'), 0, 0, 'L');
$pdf->SetXY(10, 66);
$pdf->Cell(30, 5, utf8_decode('Transportista :'), 0, 0, 'L');

$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(40, 45);
$pdf->Cell(30, 5, utf8_decode($almacen_origen), 0, 0, 'L');
$pdf->SetXY(40, 52);
$pdf->Cell(30, 5, utf8_decode($almacen_destino), 0, 0, 'L');
$pdf->SetXY(40, 59);
$pdf->Cell(30, 5, utf8_decode($nro_placa), 0, 0, 'L');
$pdf->SetXY(40, 66);
$pdf->Cell(30, 5, utf8_decode($transportista), 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 9);


// Cuadro: Entrega en destino
$pdf->SetFillColor(217, 217, 217);

function insertarSaltosLinea($texto, $longitudMax = 45)
{
    $palabras = explode(' ', $texto);
    $linea = '';
    $resultado = '';

    foreach ($palabras as $palabra) {
        if (strlen($linea . ' ' . $palabra) > $longitudMax) {
            $resultado .= trim($linea) . "\n";
            $linea = $palabra . ' ';
        } else {
            $linea .= $palabra . ' ';
        }
    }

    $resultado .= trim($linea); // Agrega lo último
    return $resultado;
}

// Tabla (debes implementarla manualmente, sin HTML)
$pdf->Ln(15);
// Cabecera
$pdf->SetFillColor(254, 191, 0);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(10, 6, 'Item', 1, 0, 'C', true);
$pdf->Cell(20, 6, utf8_decode('Código'), 1, 0, 'C', true);
$pdf->Cell(115, 6, utf8_decode('Descripción'), 1, 0, 'C', true);
$pdf->Cell(10, 6, 'U.m', 1, 0, 'C', true);
$pdf->Cell(20, 6, 'Cantidad', 1, 1, 'C', true);

// Filas
foreach ($detalle as $i => $item) {
    $pdf->SetFont('Arial', '', 6);
    $res = Almacenes::create()->paquete_trakingObtenerXmovimientoBienId($item->movimientoBienId);

    // Altura de línea base
    $lineHeight = 4;
    // Calcular altura necesaria para descripcion
    $descripcion = insertarSaltosLinea($item->descripcion, 45);
    $nbLines = (substr_count($descripcion, "\n") + 1);
    $descripcionHeight = ($lineHeight * $nbLines);
    // Guardar posición X e Y antes de MultiCell
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    $pdf->Cell(10, $descripcionHeight, $i + 1, 1, 0, 'C');
    $pdf->Cell(20, $descripcionHeight, $item->bien_codigo, 1, 0);

    // Usar MultiCell para descripcion
    $pdf->SetXY($x + 30, $y); // Ajustar al punto donde empieza la celda de descripcion
    $pdf->MultiCell(115, ($nbLines == 1 ? $descripcionHeight : ($descripcionHeight / 2)), $descripcion, 1);

    // Volver a la posición X al lado derecho de la MultiCell
    $pdf->SetXY($x + 145, $y);
    $pdf->Cell(10, $descripcionHeight, $item->simbolo, 1, 0, 'R');
    $pdf->Cell(20, $descripcionHeight, number_format($res[0]['cantidad'], 2), 1, 1, 'C');
}

// Agrega las celdas vacías si hay menos de 20 filas
for ($i = count($detalle); $i < 30; $i++) {
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(10, 4, $i + 1, 1, 0, 'C');
    $pdf->Cell(20, 4, '', 1);
    $pdf->Cell(115, 4, '', 1);
    $pdf->Cell(10, 4, '', 1);
    $pdf->Cell(20, 4, '', 1, 1);
}

$tablaHeight = $pdf->GetY();
$espacio = 0;  // Inicializar el espacio
$paginaAltura = $pdf->getPageHeight();  // Altura total de la página
$alturaDisponible = $paginaAltura - $tablaHeight - 20;
// Ahora puedes ajustar el valor de $espacio basado en el espacio disponible
if ($alturaDisponible > 70) {
    // Si hay mucho espacio, usa ese espacio
    $espacio = $tablaHeight + 2;  // Ajusta un pequeño margen después de la tabla
} else {
    // Si el espacio es limitado, podrías agregar una nueva página
    $pdf->AddPage();
    $espacio = 10;  // Nuevo espacio al inicio de la nueva página
}

$pdf->SetFillColor(255, 255, 255);


$persona = Persona::create()->obtenerPersonaXUsuarioId($dataDocumento[0]["usuario_creacion"]);
$personaFirma0 = __DIR__ . "/../../vistas/com/persona/firmas/" . $persona[0]['firma_digital'] . "png";


$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(60, $espacio + 30);
$pdf->MultiCell(39, 5, 'Recepcionado por.', 0, 'C', true);
$pdf->SetFont('Arial', '', 8);
$pdf->SetXY(60, $espacio + 40);
$pdf->MultiCell(39, 5, $dataDocumento[0]['nombre'], 0, 'C', true);

$pdf->SetXY(100, $espacio + 25);
$pdf->MultiCell(50, 30, '', 1, 'C', true); //Revisar
$pdf->SetXY(100, $espacio + 25);
if (!ObjectUtil::isEmpty($$persona[0]['firma_digital'])) {
    $pdf->Image($personaFirma0, 163,  $espacio + 25, 25, 10);
}

$pdf->Output();
