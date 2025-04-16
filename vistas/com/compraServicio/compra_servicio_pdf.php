<?php
require_once('../../../fpdf/fpdf.php');
require_once('../../../modeloNegocio/almacen/MovimientoNegocio.php');
require_once('../../../modeloNegocio/almacen/DocumentoTipoNegocio.php');
require_once('../../../modeloNegocio/almacen/PersonaNegocio.php');
require_once('../../../modeloNegocio/almacen/DocumentoNegocio.php');
require_once('../../../modeloNegocio/almacen/MatrizAprobacionNegocio.php');
require_once('../../../util/phpqrcode/qrlib.php'); // Librería para generar QR

isset($_GET["id"]) ? $documentoId = $_GET["id"] : "";
if (ObjectUtil::isEmpty($_GET["id"]) || $documentoId == "") {
    echo ("No se encontró el Orden de compra o Servicio");
    exit();
}
$documentoTipoId = null;
$dataRelacionada = DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoId($documentoId);
foreach ($dataRelacionada as $itemRelacion) {
    if ($itemRelacion['documento_tipo_id'] == Configuraciones::ORDEN_SERVICIO || $itemRelacion['documento_tipo_id'] == Configuraciones::ORDEN_COMPRA) {
        $documentoId = $itemRelacion['documento_relacionado_id'];
        $documentoTipoId = $itemRelacion['documento_tipo_id'];
    }
}

$data = MovimientoNegocio::create()->imprimir($documentoId, $documentoTipoId);

$dataDocumento = $data->dataDocumento;
$documentoDatoValor = $data->documentoDatoValor;
$detalle = $data->detalle;

$dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);

$ubigeoProveedor = PersonaNegocio::create()->obtenerUbigeoXId($dataDocumento[0]["ubigeo_id"]);

$referencia = null;
$terminos_de_pago = null;
$entrega_en_destino = null;
$entrega_en_destino_id = null;
$U_O = null;
$cuenta = null;

foreach ($documentoDatoValor as $index => $item) {
    switch ($item['tipo'] * 1) {
        case 2:
            if ($item['descripcion'] == "Referencia") {
                $referencia = $item['valor'];
            }
            break;
        case 50:
            $terminos_de_pago = $item['valor'];
            break;
        case 45:
            $entrega_en_destino = $item['valor'];
            $entrega_en_destino_id = $item["valor_codigo"];
            break;
        case 46:
            $U_O = $item['valor'];
            break;
    }
}

$organizador_entrega =  OrganizadorNegocio::create()->getOrganizador($entrega_en_destino_id);
$ubigeoProveedor_entrega = PersonaNegocio::create()->obtenerUbigeoXId($dataDocumento[0]["ubigeo_id"]);


class PDF extends FPDF
{
    protected $B = 0;
    protected $I = 0;
    protected $U = 0;
    protected $HREF = '';

    function WriteHTML($html)
    {
        // Int�rprete de HTML
        $html = str_replace("\n", ' ', $html);
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                // Text
                if ($this->HREF)
                    $this->PutLink($this->HREF, $e);
                else
                    $this->Write(5, $e);
            } else {
                // Etiqueta
                if ($e[0] == '/')
                    $this->CloseTag(strtoupper(substr($e, 1)));
                else {
                    // Extraer atributos
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    function OpenTag($tag, $attr)
    {
        // Etiqueta de apertura
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, true);
        if ($tag == 'A')
            $this->HREF = $attr['HREF'];
        if ($tag == 'BR')
            $this->Ln(5);
    }

    function CloseTag($tag)
    {
        // Etiqueta de cierre
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, false);
        if ($tag == 'A')
            $this->HREF = '';
    }

    function SetStyle($tag, $enable)
    {
        // Modificar estilo y escoger la fuente correspondiente
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (array('B', 'I', 'U') as $s) {
            if ($this->$s > 0)
                $style .= $s;
        }
        $this->SetFont('', $style);
    }

    function PutLink($URL, $txt)
    {
        // Escribir un hiper-enlace
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }
}

$pdf = new PDF('P', 'mm', 'A4');

$pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));
$pdf->SetAuthor('Soluciones Mineras S.A.C.');
$pdf->SetCreator('Soluciones Mineras S.A.C.');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 10);

$pdf->AddPage();

$qrFile = __DIR__ . "../imagen/qr_compra_servicio.png";

QRcode::png($documentoId . '-' . $documentoTipoId, $qrFile,  'L', 2, 1);

$pdf->Image($qrFile, 10, 5, 25, 25);

// Logo
$pdf->Image('C:\wamp64\www\minaApp\vistas\images\logo_pepas_de_oro.png', 150, 10, 45, 20);

// Título
$pdf->SetFont('Arial', 'B', 10);
$titulo = strtoupper("ASOCIACION DE MINEROS ARTESANALES PEPAS DE ORO DE");
$pdf->Cell(170, 10, $titulo, 0, 1, 'C');
$titulo = strtoupper("PAMPAMARCA");
$pdf->Cell(170, 0, $titulo, 0, 1, 'C');

// Dirección
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, 30);
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
$pdf->Cell(30, 5, 'No.', 0, 0, 'C');
$pdf->Cell(50, 5, $dataDocumento[0]['serie'] . " - " . $dataDocumento[0]['numero'], 0, 1, 'C');

// Proveedor
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(10, 45);
$pdf->Cell(30, 5, 'Proveedor');

$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(10, 50);
$pdf->MultiCell(90, 4, $dataDocumento[0]['nombre'], 0, 'L');

$pdf->SetXY(10, 54);
$pdf->MultiCell(90, 4, $dataDocumento[0]['codigo_identificacion'], 0, 'L');

$pdf->SetXY(10, 58);
$pdf->MultiCell(90, 4, utf8_decode($dataDocumento[0]['direccion']), 0, 'L');

$pdf->SetXY(10, 66);
$pdf->MultiCell(90, 4, utf8_decode($ubigeoProveedor[0]['ubigeo_dist']), 0, 'L');

$pdf->SetXY(10, 70);
$pdf->MultiCell(90, 4, utf8_decode($ubigeoProveedor[0]['ubigeo_dep']), 0, 'L');
$pdf->Ln(10);

// Dirección de entrega
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(110, 45);
$pdf->Cell(50, 5, utf8_decode('Dirección de entrega'));

$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(110, 50);
$pdf->MultiCell(100, 4, 'ASOCIACION DE MINEROS ARTESANALES PEPAS DE ORO DE PAMPAMARCA', 0, 'L');

$pdf->SetXY(110, 54);
$pdf->MultiCell(90, 4, '20490115804', 0, 'L');

$pdf->SetXY(110, 58);
$pdf->MultiCell(90, 4, utf8_decode($organizador_entrega[0]["direccion"]), 0, 'L');

$pdf->SetXY(110, 66);
$pdf->MultiCell(90, 4, utf8_decode($ubigeoProveedor_entrega[0]['ubigeo_dist']), 0, 'L');

$pdf->SetXY(110, 70);
$pdf->MultiCell(90, 4, utf8_decode($ubigeoProveedor_entrega[0]['ubigeo_dep']), 0, 'L');

// Cuadro: Entrega en destino
$pdf->SetFillColor(217, 217, 217);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, 77);
$pdf->Cell(45, 5, 'Entrega en destino', 1, 0, 'C', true);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(10, 82);
$pdf->MultiCell(45, 10, $entrega_en_destino, 1, 'C');

// Cuadro: Términos de pago
$pdf->SetFillColor(217, 217, 217);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(55, 77);
$pdf->Cell(45, 5, utf8_decode('Términos de pago'), 1, 0, 'C', true);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(55, 82);
$pdf->MultiCell(45, 10, utf8_decode($terminos_de_pago), 1, 'C');

// Cuadro: Solicitado por
$pdf->SetFillColor(217, 217, 217);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(100, 77);
$pdf->Cell(45, 5, 'Solicitado por', 1, 0, 'C', true);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(100, 82);
$pdf->MultiCell(45, 10, $dataDocumento[0]['usuario'], 1, 'C');

$serieNumeroCotizacion = '';
$serieNumeroSolicitudRequerimiento = '';
$cuenta = '';
$dataRelacionada = DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoId($documentoId);
$banderaUrgencia = 0;
foreach ($dataRelacionada as $itemRelacion) {
    if ($itemRelacion['documento_tipo_id'] == Configuraciones::COTIZACIONES || $itemRelacion['documento_tipo_id'] == Configuraciones::COTIZACION_SERVICIO) {
        $serieNumeroCotizacion = $itemRelacion['serie_numero'];
    }
    if ($itemRelacion['documento_tipo_id'] == Configuraciones::SOLICITUD_REQUERIMIENTO) {
        $serieNumeroSolicitudRequerimiento .= $itemRelacion['serie_numero'] . ", ";

        $documentoDatoValor = DocumentoDatoValorNegocio::create()->obtenerXIdDocumento($itemRelacion["documento_relacionado_id"]);
        foreach ($documentoDatoValor as $index => $item) {
            switch ($item['tipo'] * 1) {
                case 52:
                    $cuenta .= $item['valor'] . ", ";
                    break;
                case 4:
                    if ($item['descripcion'] == "Urgencia" && $item['valor'] == "Si") {
                        $banderaUrgencia = 1;
                    }
                    break;
            }
        }
    }
    if ($itemRelacion['documento_tipo_id'] == Configuraciones::COTIZACION_SERVICIO) {
        $banderaUrgencia = 2;
    }
}

// Separar por comas y eliminar espacios, eliminar repetidos
$cuenta = array_unique(array_filter(array_map('trim', explode(',', $cuenta))));
// Volver a unir en un string
$cuenta = implode(', ', $cuenta);

// Fila: REQUERIMIENTO
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, 92);
$pdf->MultiCell(45, 5, 'REQUERIMIENTO:', 1, 'L', true);

$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(55, 92);
$pdf->MultiCell(90, 5, $serieNumeroSolicitudRequerimiento, 1, 'L', true);

// Fila: U.O
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, 97);
$pdf->MultiCell(45, 5, 'U.O:', 1, 'L', true);

$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(55, 97);
$pdf->MultiCell(90, 5, $U_O, 1, 'L', true);

// Fila: REFERENCIA
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, 102);
$pdf->MultiCell(45, 5, 'REFERENCIA:', 1, 'L', true);

$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(55, 102);
$pdf->MultiCell(90, 5, $referencia, 1, 'L', true);

// Fila: GENERADO POR
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, 107);
$pdf->MultiCell(45, 5, 'GENERADO POR:', 1, 'L', true);

$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(55, 107);
$pdf->MultiCell(90, 5, $dataDocumento[0]['usuario'], 1, 'L', true);

// Fila: COTIZACION
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, 112);
$pdf->MultiCell(45, 5, 'COTIZACION:', 1, 'L', true);

$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(55, 112);
$pdf->MultiCell(90, 5, $serieNumeroCotizacion, 1, 'L', true);

// Fila: CUENTA
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(10, 117);
$pdf->MultiCell(45, 5, 'CUENTA:', 1, 'L', true);

$pdf->SetFont('Arial', '', 7);
$pdf->SetXY(55, 117);
$pdf->MultiCell(90, 5, $cuenta, 1, 'L', true);

// Tabla (debes implementarla manualmente, sin HTML)
$pdf->Ln(5);
// Cabecera
$pdf->SetFillColor(254, 191, 0);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(10, 6, 'Item', 1, 0, 'C', true);
$pdf->Cell(20, 6, utf8_decode('Código'), 1, 0, 'C', true);
$pdf->Cell(60, 6, utf8_decode('Descripción'), 1, 0, 'C', true);
$pdf->Cell(15, 6, 'Cantidad', 1, 0, 'C', true);
$pdf->Cell(10, 6, 'U.m', 1, 0, 'C', true);
$pdf->Cell(25, 6, 'Valor Unitario', 1, 0, 'C', true);
$pdf->Cell(25, 6, 'Totales', 1, 0, 'C', true);
$pdf->Cell(25, 6, 'Unidad Minera', 1, 1, 'C', true);

// Filas
foreach ($detalle as $i => $item) {
    $pdf->SetFont('Arial', '', 6);
    $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleObtenerUnidadMinera($item->movimientoBienId, $banderaUrgencia);
    $cantidadSaltos = (substr_count($resMovimientoBienDetalle[0]['cantidad_requerimiento'], "\n") + 1);
    $pdf->Cell(10, (4 * $cantidadSaltos), $i + 1, 1, 0, 'C');
    $pdf->Cell(20, (4 * $cantidadSaltos), $item->bien_codigo, 1);
    $pdf->Cell(60, (4 * $cantidadSaltos), $item->descripcion, 1);
    $pdf->Cell(15, (4 * $cantidadSaltos), number_format($item->cantidad, 2), 1, 0, 'R');
    $pdf->Cell(10, (4 * $cantidadSaltos), $item->simbolo, 1, 0, 'C');
    $pdf->Cell(25, (4 * $cantidadSaltos), number_format($item->precioUnitario, 2), 1, 0, 'R');
    $pdf->Cell(25, (4 * $cantidadSaltos), number_format($item->importe, 2), 1, 0, 'R');
    $pdf->SetFont('Arial', '', 4);
    $pdf->MultiCell(25,  4, $resMovimientoBienDetalle[0]['cantidad_requerimiento'], 1, 1);
}

// Agrega las celdas vacías si hay menos de 20 filas
for ($i = count($detalle); $i < 20; $i++) {
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(10, 4, $i + 1, 1, 0, 'C');
    $pdf->Cell(20, 4, '', 1);
    $pdf->Cell(60, 4, '', 1);
    $pdf->Cell(15, 4, '', 1);
    $pdf->Cell(10, 4, '', 1);
    $pdf->Cell(25, 4, '', 1);
    $pdf->Cell(25, 4, '', 1);
    $pdf->Cell(25, 4, '', 1, 1);
}

$tablaHeight = $pdf->GetY();
$espacio = 0;  // Inicializar el espacio
$paginaAltura = $pdf->getPageHeight();  // Altura total de la página
$alturaDisponible = $paginaAltura - $tablaHeight - 20;
// Ahora puedes ajustar el valor de $espacio basado en el espacio disponible
if ($alturaDisponible > 20) {
    // Si hay mucho espacio, usa ese espacio
    $espacio = $tablaHeight + 2;  // Ajusta un pequeño margen después de la tabla
} else {
    // Si el espacio es limitado, podrías agregar una nueva página
    $pdf->AddPage();
    $espacio = 10;  // Nuevo espacio al inicio de la nueva página
}

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetXY(100, $espacio);
$pdf->MultiCell(18, 5, 'MONEDA:', 0, 'L', true);
$pdf->SetXY(115, $espacio);
$pdf->SetFont('Arial', '', 8);
$pdf->MultiCell(15, 5, utf8_decode($dataDocumento[0]["moneda_descripcion"]), 0, 'L', true);

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(150, $espacio + 5);
$pdf->MultiCell(25, 5, 'SUBTOTAL', 1, 'L', true);
$pdf->SetXY(150, $espacio + 10);
$pdf->MultiCell(25, 5, 'IGV 18%', 1, 'L', true);
$pdf->SetXY(150, $espacio + 15);
$pdf->MultiCell(25, 5, 'TOTAL', 1, 'L', true);

$pdf->SetXY(175, $espacio + 5);
$pdf->MultiCell(25, 5, number_format($dataDocumento[0]['subtotal'], 2), 1, 'R', true);
$pdf->SetXY(175, $espacio + 10);
$pdf->MultiCell(25, 5, number_format($dataDocumento[0]['igv'], 2), 1, 'R', true);
$pdf->SetXY(175, $espacio + 15);
$pdf->MultiCell(25, 5, number_format($dataDocumento[0]['total'], 2), 1, 'R', true);

$pdf->SetFont('Arial', '', 5);
$pdf->SetXY(10, $espacio);
$pdf->MultiCell(90, 5, 'Intrucciones', 1, 'L', true);
$pdf->SetXY(10, $espacio + 5);
$pdf->MultiCell(90, 5, utf8_decode('* Entrega del bien con GR,OC/OS  y  FACTURA, sino no se recepcionará.'), 1, 'L', true);
$pdf->SetXY(10, $espacio + 10);
$pdf->MultiCell(90, 5, '* En la guia de remision mencionar el numero de orden de compra', 1, 'L', true);
$pdf->SetXY(10, $espacio + 15);
$pdf->MultiCell(90, 5, '* incluye IGV', 1, 'L', true);

$pdf->SetFont('Arial', '', 4);
$pdf->SetXY(10, $espacio + 23);
$pdf->MultiCell(70, 3, utf8_decode('*El lugar de entrega se coordinará con el Comprador.'), 0, 'L', true);
$pdf->SetXY(10, $espacio + 26);
$pdf->MultiCell(70, 3, '*Para aclaraciones contactar con el comprador :', 0, 'L', true);
$pdf->SetXY(10, $espacio + 29);
$pdf->SetFont('Arial', 'B', 4);
$pdf->MultiCell(70, 3, utf8_decode('Procedimiento para presentación de facturas y comprobantes de pago:'), 0, 'L', true);
$pdf->SetFont('Arial', '', 4);
$pdf->SetXY(10, $espacio + 32);
$pdf->MultiCell(70, 3, utf8_decode('- Validación SUNAT para Comprobantes electrónicos.'), 10, 'L', true);
$pdf->SetXY(10, $espacio + 35);
$pdf->MultiCell(70, 3, utf8_decode('- Validación de emisor electrónicos para Comprobantes físicos.'), 0, 'L', true);
$pdf->SetXY(10, $espacio + 38);
$pdf->MultiCell(70, 3, utf8_decode('- En el caso de facturas electrónicas deben remitir el archivo en pdf y xml.'), 0, 'L', true);
$pdf->SetXY(10, $espacio + 41);
$pdf->MultiCell(70, 3, '- Copia de la Orden de Compra.', 0, 'L', true);
$pdf->SetXY(10, $espacio + 44);
$pdf->MultiCell(70, 3, utf8_decode('- Acta de conformidad y/o Liquidación en el caso de ser un servicio.'), 0, 'L', true);
$pdf->SetXY(10, $espacio + 47);
$pdf->MultiCell(70, 3, utf8_decode('- Guía de remisión con sello de recepción o conformidad.'), 0, 'L', true);


$matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoXArea($documentoTipoId, null);
$usuario_estado = DocumentoNegocio::create()->obtenerDocumentoDocumentoEstadoXdocumentoId($documentoId, "0,1");

$resultadoMatriz = [];

foreach ($matrizUsuario as $key => $value) {
    if ($usuario_estado[$key]["estado_descripcion"] == "Registrado") {
        $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
        $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" => $usuario_estado[$key]["nombre"], "fecha" => $usuario_estado[$key]["usuario_creacion"]);
    } else {
        switch ($value["nivel"]) {
            case "1":
                foreach ($usuario_estado as $val) {
                    if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                        $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                        $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"]);
                    }
                }
                break;
            case "2":
                foreach ($usuario_estado as $val) {
                    if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                        $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                        $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"]);
                    }
                }
                break;
            case "3":
                foreach ($usuario_estado as $val) {
                    if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                        $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                        $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"]);
                    }
                }
                break;
        }
    }
}

$personaFirma0 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[0]['firma_digital'] . "png";
$personaFirma1 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[1]['firma_digital'] . "png";
$personaFirma2 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[2]['firma_digital'] . "png";
$personaFirma3 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[3]['firma_digital'] . "png";


$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(110, $espacio + 25);
$pdf->MultiCell(39, 5, 'Autorizado por.', 0, 'C', true);
$pdf->SetXY(150, $espacio + 25);
$pdf->MultiCell(50, 10, '', 1, 'C', true); //Revisar
$pdf->SetXY(150, $espacio + 25);
if (!ObjectUtil::isEmpty($resultadoMatriz[0]['firma_digital'])) {
    $pdf->Image($personaFirma1, 150,  $espacio + 25, 45, 20);
}
$pdf->SetFont('helvetica', '', 6);
$pdf->SetXY(110, $espacio + 30);
$pdf->MultiCell(39, 3, 'JEFE DE LOGISTICA', 0, 'C', true);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetXY(110, $espacio + 38);
$pdf->MultiCell(39, 5, 'Autorizado por.', 0, 'C', true);
$pdf->SetXY(150, $espacio + 38);
$pdf->MultiCell(50, 10, '', 1, 'C', true); //Revisar
$pdf->SetFont('helvetica', '', 6);
$pdf->SetXY(110, $espacio + 43);
$pdf->MultiCell(39, 3, 'COMPRADOR', 0, 'C', true);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetXY(110, $espacio + 51);
$pdf->MultiCell(39, 5, 'Autorizado por.', 0, 'C', true);
$pdf->SetXY(150, $espacio + 51);
$pdf->MultiCell(50, 10, '', 1, 'C', true); //Revisar
$pdf->SetFont('helvetica', '', 6);
$pdf->SetXY(110, $espacio + 56);
$pdf->MultiCell(39, 3, 'GERENTE GENERAL', 0, 'C', true);

$pdf->SetFont('helvetica', '', 4);
$pdf->SetXY(10, $espacio + 63);
$pdf->MultiCell(150, 2, utf8_decode('El horario de recepción es de lunes a viernes de 8:00 am a 1:00 pm; los documentos que envíen después de este horario o los días sábados, domingos y feriados serán considerados como recibidos a partir'), 0, 'L', true);
$pdf->SetXY(10, $espacio + 65);
$pdf->MultiCell(150, 2, utf8_decode('del siguiente día hábil y deberán ser remitidos a la siguiente dirección de correo electrónico '), 0, 'L', true);
$pdf->SetXY(10, $espacio + 67);
$pdf->MultiCell(150, 2, utf8_decode('El pago es semanal todos los jueves, se programarán todos los comprobantes que cumplan con el procedimiento solicitado y hayan sido emitidos y registrados hasta el martes previo.'), 0, 'L', 1, 0, '', $espacio + 59, true, 0, false, true, 2, 'M');



// $pdf->AddPage();

// $distribucionPagos = OrdenCompraServicio::create()->obtenerDistribucionPagos($documentoId);
// $cont_distribucionPagos = 0;
// $pdf->SetFont('helvetica', '', 7);
// $tabla_distribucionPagos = '<table cellspacing="0" cellpadding="1" border="1">
//     <tr style="background-color:rgb(254, 191, 0);">
//         <th style="text-align:center;vertical-align:middle;" width="5%"><b>Item</b></th>
//         <th style="text-align:center;vertical-align:middle;" width="45%"><b>Importe</b></th>
//         <th style="text-align:center;vertical-align:middle;" width="50%"><b>Porcentaje</b></th>
//     </tr>
// ';
// if (!ObjectUtil::isEmpty($distribucionPagos)) {
//   foreach ($distribucionPagos as $index => $item) {
//     $cont_distribucionPagos++;

//     $tabla_distribucionPagos = $tabla_distribucionPagos . '<tr>'
//       . '<td style="text-align:center"  width="5%">' . ($index + 1) . '</td>'
//       . '<td style="text-align:center"  width="45%">' . number_format($item['importe'], 2) . '</td>'
//       . '<td style="text-align:center"  width="50%">' . number_format($item['porcentaje'], 2) . '</td>'
//       . '</tr>';
//   }
// }
// $tabla_distribucionPagos = $tabla_distribucionPagos . '</table>';

// $tabla_distribucionPagosTitulo = '<div style="text-align: center;"> <h3>DISTRIBUCIÓN DE PAGOS</h3></div> <div style="text-align: justify;"></div>';
// $pdf->writeHTML($tabla_distribucionPagosTitulo, true, false, true, false, '');

// $pdf->writeHTML($tabla_distribucionPagos, true, false, true, false, '');
$pdf->AddPage();
$pdf->Image('C:\wamp64\www\sgiPepas\vistas\images\Condiciones1.jpg', 0, 0, 210, 350);

$pdf->AddPage();
$pdf->Image('C:\wamp64\www\sgiPepas\vistas\images\Condiciones2.jpg', 0, 0, 210, 350);

$pdf->Output();
