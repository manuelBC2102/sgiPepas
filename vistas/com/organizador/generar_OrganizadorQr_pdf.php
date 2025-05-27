<?php
require_once('../../../fpdf/fpdf.php');
require_once('../../../modelo/almacen/Organizador.php');
require_once('../../../util/phpqrcode/qrlib.php'); // Librería para generar QR

$documentoTipoId = 265;

isset($_GET["id"]) ? $id = $_GET["id"] : "";
if (ObjectUtil::isEmpty($_GET["id"]) || $id == "") {
    echo ("No se encontró Organizador");
    exit();
}


$data = Organizador::create()->getOrganizador($id);
$organizador_id = $data[0]['organizador_padre_id'];
$dataPadre = null;
$bandera_organizador = true;

if (isset($organizador_id)) {
    while ($bandera_organizador) {
        $id_ = $organizador_id;
        $dataPadre = Organizador::create()->getOrganizador($id_);
        $id_ = $dataPadre[0]['organizador_padre_id'];
        $organizador_id = $id_;
        if ($dataPadre[0]['organizador_tipo_id'] == 10) {
            $bandera_organizador = false;
            $organizador_id = $id_;
        }
    }
}


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

$pdf->SetTitle(strtoupper(utf8_decode("QR de Organizador")));
$pdf->SetAuthor('Soluciones Mineras S.A.C.');
$pdf->SetCreator('Soluciones Mineras S.A.C.');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

$pdf->AddPage();

// Logo
$pdf->Image(__DIR__ . '/../../../vistas/images/logo_pepas_de_oro_blanco_negro.png', 5, 0, 30, 30);
$pdf->SetFont('Arial', 'B', 14);

$pdf->SetXY(33, 12);
$pdf->MultiCell(60, 5, $data[0]['codigo'] . " - " . utf8_decode($data[0]['descripcion']), 0, 'C');

$pdf->Line(5, 25, 95, 25); // x1, y1, x2, y2 — margen de 5 mm a cada lado

$qrFile = __DIR__ . "/imagen/" . $data[0]['id'] . "qr_organizador.png";
QRcode::png($data[0]['id'], $qrFile,  'L', 2, 1);
$pdf->Image($qrFile, 30, 30, 40, 40);

$pdf->Line(5, 75, 95, 75); // x1, y1, x2, y2 — margen de 5 mm a cada lado

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetXY(25, 80);
if(isset($dataPadre)){
    $pdf->MultiCell(50, 3, $dataPadre[0]['codigo'] . " - " . utf8_decode($dataPadre[0]['descripcion']), 0, 'C');
}

if (file_exists($qrFile)) {
    unlink($qrFile);
}




$pdf->Output();
