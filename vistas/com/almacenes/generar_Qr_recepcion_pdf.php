<?php
require_once('../../../fpdf/fpdf.php');
require_once('../../../modeloNegocio/almacen/MovimientoNegocio.php');
require_once('../../../modeloNegocio/almacen/DocumentoTipoNegocio.php');
require_once('../../../modeloNegocio/almacen/PersonaNegocio.php');
require_once('../../../modeloNegocio/almacen/DocumentoNegocio.php');
require_once('../../../modelo/almacen/Almacenes.php');
require_once('../../../modelo/almacen/Organizador.php');
require_once('../../../util/phpqrcode/qrlib.php'); // Librería para generar QR

isset($_GET["id"]) ? $id = $_GET["id"] : "";
if (ObjectUtil::isEmpty($_GET["id"]) || $id == "") {
    echo ("No se encontró Orden de compra o Servicio");
    exit();
}


$datoDocumento = DocumentoNegocio::create()->obtenerXId($id, null);
$documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($datoDocumento[0]['movimiento_id']);

$detalle = [];
foreach ($documentoDetalle as $item) {
    $datos = Almacenes::create()->paquete_trakingObtenerXmovimientoBienId($item['movimiento_bien_id']);
    $detalle = array_merge($detalle, $datos);
}

// $detalle = Almacenes::create()->obtenerPaqueteXGrupoPaquete($id);

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



$pdf = new FPDF('P', 'mm', [100, 100]); // ancho x alto

$pdf->SetTitle(strtoupper(utf8_decode("QR de paquetes")));
$pdf->SetAuthor('Soluciones Mineras S.A.C.');
$pdf->SetCreator('Soluciones Mineras S.A.C.');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

foreach ($detalle as $item) {
    $data = Organizador::create()->getOrganizador($item['organizador_id']);
    $organziador_id = $data[0]['organizador_padre_id'];
    $dataPadre = null;
    $bandera_organizador = true;

    while ($bandera_organizador) {
        $id_ = $organziador_id;
        $dataPadre = Organizador::create()->getOrganizador($id_);
        $id_ = $dataPadre[0]['organizador_padre_id'];
        $organziador_id = $id_;
        if ($dataPadre[0]['organizador_tipo_id'] == 10) {
            $bandera_organizador = false;
            $organziador_id = $id_;
        }
    }

    $pdf->AddPage();
    // Logo
    $pdf->Image(__DIR__ . '/../../../vistas/images/logo_pepas_de_oro_blanco_negro.png', 70, 1, 25, 25);
    // $pdf->SetXY(55, 10);
    // $pdf->MultiCell(40, 3, $itemdataPaquete['serie_numero'], 0, 'C');

    $pdf->SetXY(5, 10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->MultiCell(80, 3, "Almacen Origen:", 0, '');
    $pdf->SetXY(5, 15);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(60, 5, $dataPadre[0]['codigo'] . " | " . utf8_decode($dataPadre[0]['descripcion']), 0, '');
    $pdf->SetXY(5, 20);
    $pdf->MultiCell(60, 5, utf8_decode($item['codigo_descripcion_organizador']), 0, '');

    $pdf->Line(5, 27, 95, 27); // x1, y1, x2, y2 — margen de 5 mm a cada lado

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(46, 45);
    $pdf->MultiCell(50, 4, utf8_decode($item['bien_codigo_descripcion']), 0, 'C');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetXY(45, 65);
    $pdf->MultiCell(45, 3, number_format($item['indice'], 0) . " x " . number_format($item['cantidad'], 2) . " | " . $item['unida_medida_tipo_descripcion'], 0, 'C');


    $qrFile = __DIR__ . "/imagen/" . $item['paquete_id'] . "qr_paquete.png";
    QRcode::png($item['paquete_id'], $qrFile,  'L', 2, 1);

    $pdf->Image($qrFile, 5, 30, 38, 38);

    if (file_exists($qrFile)) {
        unlink($qrFile);
    }

    $pdf->SetXY(5, 68);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->MultiCell(38, 3, $item['paquete_id'], 0, 'C');

    $pdf->SetXY(5, 73);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->MultiCell(80, 3, "Almacen destino:", 0, '');
    $pdf->SetXY(5, 78);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(90, 3, utf8_decode($item['codigo_descripcion_organizador_destino']) . " - " . utf8_decode($item['unidad_minera_descripcion']), 0, '');

    $pdf->Line(5, 83, 95, 83); // x1, y1, x2, y2 — margen de 5 mm a cada lado
    $pdf->Image(__DIR__ . '/../../../vistas/images/iconos_paquete.png', 5, 85, 50, 10);
}


$pdf->Output();
