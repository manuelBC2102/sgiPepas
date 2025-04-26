<?php
require_once('../../../fpdf/fpdf.php');
require_once('../../../modeloNegocio/almacen/MovimientoNegocio.php');
require_once('../../../modeloNegocio/almacen/DocumentoTipoNegocio.php');
require_once('../../../modeloNegocio/almacen/PersonaNegocio.php');
require_once('../../../modeloNegocio/almacen/DocumentoNegocio.php');
require_once('../../../modeloNegocio/almacen/MatrizAprobacionNegocio.php');
require_once('../../../util/phpqrcode/qrlib.php'); // Librería para generar QR

isset($_GET["documentoTipoId"]) ? $documentoTipoId = $_GET["documentoTipoId"] : "";

if (!ObjectUtil::isEmpty($documentoTipoId)) {
    $documentoId = $_GET["id"];
    $documentoTipoId = $_GET["documentoTipoId"];
}else{
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
$tiempo_entrega = null;
$tiempo = "";
$diasPago = "";

foreach ($documentoDatoValor as $index => $item) {
    switch ($item['tipo'] * 1) {
        case 1:
            if ($item['descripcion'] == "Tiempo") {
                $tiempo = number_format($item['valor'], 0).", ";
            } 
            if ($item['descripcion'] == "Dias de pago") {
                $diasPago = ", ". $item['valor'];
            }             
            break;
        case 2:
            if ($item['descripcion'] == "Referencia") {
                $referencia = $item['valor'];
            }
            if ($item['descripcion'] == "Condición de pago") {
                $terminos_de_pago = $item['valor'];
            }
            if ($item['descripcion'] == "Dias de pago") {
                $diasPago = ", ". $item['valor'];
            } 
            if ($item['descripcion'] == "Tiempo de entrega") {
                $tiempo_entrega = $item['valor'];
            }    
            if ($item['descripcion'] == "Tiempo") {
                $tiempo = number_format($item['valor'], 0)." ".utf8_decode("días ").", ";
            }                      
            break;
        case 4:
            $tiempo_entrega = $item['valor'];
            break;
        case 45:
            $entrega_en_destino = $item['valor'];
            $entrega_en_destino_id = $item["valor_codigo"];
            break;
        case 46:
            $U_O = $item['valor'];
            break;
            break;
        case 50:
            $terminos_de_pago = $item['valor'];
        break;
    }
}

$organizador_entrega =  OrganizadorNegocio::create()->getOrganizador($entrega_en_destino_id);
$ubigeoProveedor_entrega = PersonaNegocio::create()->obtenerUbigeoXId($dataDocumento[0]["ubigeo_id"]);


$serieNumeroCotizacion = '';
$serieNumeroSolicitudRequerimiento = '';
$cuenta = '';
$unidadMinera = '';
$dataRelacionada = DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoId($documentoId);
$banderaUrgencia = 0;
$usuarioRequerimientoArea = 0;
foreach ($dataRelacionada as $itemRelacion) {
    if ($itemRelacion['documento_tipo_id'] == Configuraciones::COTIZACIONES || $itemRelacion['documento_tipo_id'] == Configuraciones::COTIZACION_SERVICIO) {
        $serieNumeroCotizacion = $itemRelacion['serie_numero'];
    }
    if ($itemRelacion['documento_tipo_id'] == Configuraciones::SOLICITUD_REQUERIMIENTO) {
        $serieNumeroSolicitudRequerimiento .= $itemRelacion['serie_numero'] . ", ";

        $documentoDatoValor = DocumentoDatoValorNegocio::create()->obtenerXIdDocumento($itemRelacion["documento_relacionado_id"]);
        foreach ($documentoDatoValor as $index => $item) {
            switch ($item['tipo'] * 1) {
                case 51:
                    $unidadMinera .= $item['valor'] . ", ";
                    break;                
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
        $banderaUrgencia = 1;
    }
    if($documentoTipoId == Configuraciones::ORDEN_COMPRA && $banderaUrgencia == 0){
        if ($itemRelacion['documento_tipo_id'] == Configuraciones::REQUERIMIENTO_AREA) {
            $usuarioRequerimientoArea = $itemRelacion['solicitante_nombre_completo'];
        }
    }
    if($documentoTipoId == Configuraciones::ORDEN_COMPRA && $banderaUrgencia == 1){
        if ($itemRelacion['documento_tipo_id'] == Configuraciones::SOLICITUD_REQUERIMIENTO) {
            $usuarioRequerimientoArea = $itemRelacion['solicitante_nombre_completo'];
        }
    }
    if($documentoTipoId == Configuraciones::ORDEN_SERVICIO){
        if ($itemRelacion['documento_tipo_id'] == Configuraciones::SOLICITUD_REQUERIMIENTO) {
            $usuarioRequerimientoArea = $itemRelacion['solicitante_nombre_completo'];
        }
    }
}

$pdf = new FPDF('P', 'mm', 'A4');

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
$pdf->Image(__DIR__ .'/../../../vistas/images/logo_pepas_de_oro.png', 150, 10, 45, 25);

// Título
$pdf->SetFont('Arial', 'B', 10);
$titulo = strtoupper("ASOCIACION DE MINEROS ARTESANALES PEPAS DE ORO DE");
$pdf->Cell(170, 10, $titulo, 0, 1, 'C');
$titulo = strtoupper("PAMPAMARCA");
$pdf->Cell(170, 0, $titulo, 0, 1, 'C');

// Dirección
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(10, 33);
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
$pdf->MultiCell(45, 10, $usuarioRequerimientoArea, 1, 'C');

function insertarSaltosLinea($texto, $longitudMax = 45) {
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

//Filtrar repetidos
$unidadMinera = implode(', ', array_unique(array_filter(array_map('trim', explode(',', $unidadMinera)))));

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
$pdf->MultiCell(90, 5, $unidadMinera, 1, 'L', true);

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
$pdf->Cell(65, 6, utf8_decode('Descripción'), 1, 0, 'C', true);
$pdf->Cell(15, 6, 'Cantidad', 1, 0, 'C', true);
$pdf->Cell(10, 6, 'U.m', 1, 0, 'C', true);
$pdf->Cell(20, 6, 'Valor Unitario', 1, 0, 'C', true);
$pdf->Cell(20, 6, 'Totales', 1, 0, 'C', true);
$pdf->Cell(30, 6, 'Unidad Minera', 1, 1, 'C', true);

// Filas
foreach ($detalle as $i => $item) {
    $pdf->SetFont('Arial', '', 6);
    $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleObtenerUnidadMinera($item->movimientoBienId, $banderaUrgencia);
    $resultado = [];
    foreach ($resMovimientoBienDetalle as $dato) {
        $unidad = $dato['unidad_minera'];
        if($banderaUrgencia == 0){
            $cantidad = floatval($dato['cantidad_requerimiento']);
        }else{
            $cantidad = floatval($dato['cantidad_requerimiento']);
        }

        if (!isset($resultado[$unidad])) {
            $resultado[$unidad] = 0;
        }
        $resultado[$unidad] += $cantidad;
    }
    // Crear la cadena formateada
    $textoFinal = "";
    foreach ($resultado as $unidad => $total) {
        $textoFinal .= "$unidad: " . rtrim(number_format($total, 0, '.', '')) . "\n";
    }

    $cantidadSaltos = substr_count($textoFinal, "\n");
    $pdf->SetFont('Arial', '', 6);
    // Altura de línea base
    $lineHeight = 4;
    // Calcular altura necesaria para descripcion
    $descripcion = insertarSaltosLinea($item->descripcion, 45);
    $nbLines = (substr_count($descripcion, "\n") + 1);
    $descripcionHeight = max($lineHeight * $cantidadSaltos, $lineHeight * $nbLines);
    
    if($descripcionHeight > $lineHeight){
        $lineHeight = $descripcionHeight;
    }

    // Guardar posición X e Y antes de MultiCell
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    $pdf->Cell(10, $descripcionHeight, $i + 1, 1, 0, 'C');
    $pdf->Cell(20, $descripcionHeight, $item->bien_codigo, 1, 0);
    
    // Usar MultiCell para descripcion
    $pdf->SetXY($x + 30, $y); // Ajustar al punto donde empieza la celda de descripcion
    $pdf->MultiCell(65, ($nbLines == 1 ? $descripcionHeight:($descripcionHeight / 2)), $descripcion, 1);

    // Volver a la posición X al lado derecho de la MultiCell
    $pdf->SetXY($x + 95, $y);
    $pdf->Cell(15, $descripcionHeight, number_format($item->cantidad, 2), 1, 0, 'R');
    $pdf->Cell(10, $descripcionHeight, $item->simbolo, 1, 0, 'C');
    $pdf->Cell(20, $descripcionHeight, number_format($item->precioUnitario, 4), 1, 0, 'R');
    $pdf->Cell(20, $descripcionHeight, number_format($item->importe, 2), 1, 0, 'R');

    $pdf->SetFont('Arial', '', 4);
    $pdf->MultiCell(30,  ($nbLines > 2 ? $descripcionHeight: 4), $textoFinal, 1);
}

// Agrega las celdas vacías si hay menos de 20 filas
for ($i = count($detalle); $i < 15; $i++) {
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(10, 4, $i + 1, 1, 0, 'C');
    $pdf->Cell(20, 4, '', 1);
    $pdf->Cell(65, 4, '', 1);
    $pdf->Cell(15, 4, '', 1);
    $pdf->Cell(10, 4, '', 1);
    $pdf->Cell(20, 4, '', 1);
    $pdf->Cell(20, 4, '', 1);
    $pdf->Cell(30, 4, '', 1, 1);
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

$pdf->SetXY(10, $espacio);
$pdf->SetFont('Arial', 'B', 7);
$pdf->MultiCell(90, 5, 'Tiempo de entrega: ', 1, 'L', true);
$pdf->SetXY(34, $espacio + 1);
$pdf->SetFont('Arial', '', 7);
$pdf->MultiCell(50, 3, $tiempo." ".utf8_decode($tiempo_entrega), 0, 'L', true);

$pdf->SetXY(10, $espacio + 5);
$pdf->SetFont('Arial', 'B', 7);
$pdf->MultiCell(90, 8, 'Sumilla: ', 1, 'L', true);
$pdf->SetXY(20, $espacio + 6);
$pdf->SetFont('Arial', '', 6);
$pdf->MultiCell(75, 2, utf8_decode(str_replace('&nbsp;', ' ', $dataDocumento[0]['comentario'])), 0, 'L', true);

$pdf->SetFont('Arial', 'B', 5);
$pdf->SetXY(10, $espacio + 15);
$pdf->MultiCell(90, 5, 'Intrucciones', 1, 'L', true);
$pdf->SetFont('Arial', '', 4);
$pdf->SetXY(10, $espacio + 19);
$pdf->MultiCell(90, 4, utf8_decode('* Entrega del bien con GR,OC/OS  y  FACTURA, sino no se recepcionará.'), 1, 'L', true);
$pdf->SetXY(10, $espacio + 23);
$pdf->MultiCell(90, 4, '* En la guia de remision mencionar el numero de orden de compra', 1, 'L', true);
$pdf->SetXY(10, $espacio + 27);
$pdf->MultiCell(90, 4, '* incluye IGV', 1, 'L', true);

$pdf->SetFont('Arial', '', 4);
$pdf->SetXY(10, $espacio + 33);
$pdf->MultiCell(70, 3, utf8_decode('*El lugar de entrega se coordinará con el Comprador.'), 0, 'L', true);
$pdf->SetXY(10, $espacio + 36);
$pdf->MultiCell(70, 3, '*Para aclaraciones contactar con el comprador.', 0, 'L', true);
$pdf->SetXY(10, $espacio + 39);
$pdf->SetFont('Arial', 'B', 4);
$pdf->MultiCell(70, 3, utf8_decode('Procedimiento para presentación de facturas y comprobantes de pago:'), 0, 'L', true);
$pdf->SetFont('Arial', '', 4);
$pdf->SetXY(10, $espacio + 42);
$pdf->MultiCell(70, 3, utf8_decode('- Validación SUNAT para Comprobantes electrónicos.'), 10, 'L', true);
$pdf->SetXY(10, $espacio + 45);
$pdf->MultiCell(70, 3, utf8_decode('- Validación de emisor electrónicos para Comprobantes físicos.'), 0, 'L', true);
$pdf->SetXY(10, $espacio + 48);
$pdf->MultiCell(70, 3, utf8_decode('- En el caso de facturas electrónicas deben remitir el archivo en pdf y xml.'), 0, 'L', true);
$pdf->SetXY(10, $espacio + 51);
$pdf->MultiCell(70, 3, '- Copia de la Orden de Compra.', 0, 'L', true);
$pdf->SetXY(10, $espacio + 54);
$pdf->MultiCell(70, 3, utf8_decode('- Acta de conformidad y/o Liquidación en el caso de ser un servicio.'), 0, 'L', true);
$pdf->SetXY(10, $espacio + 57);
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
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(110, $espacio + 30);
$pdf->MultiCell(39, 3, 'JEFE DE LOGISTICA', 0, 'C', true);

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(110, $espacio + 38);
$pdf->MultiCell(39, 5, 'Autorizado por.', 0, 'C', true);
$pdf->SetXY(150, $espacio + 38);
$pdf->MultiCell(50, 10, '', 1, 'C', true); //Revisar
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(110, $espacio + 43);
$pdf->MultiCell(39, 3, 'COMPRADOR', 0, 'C', true);

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetXY(110, $espacio + 51);
$pdf->MultiCell(39, 5, 'Autorizado por.', 0, 'C', true);
$pdf->SetXY(150, $espacio + 51);
$pdf->MultiCell(50, 10, '', 1, 'C', true); //Revisar
$pdf->SetFont('Arial', '', 6);
$pdf->SetXY(110, $espacio + 56);
$pdf->MultiCell(39, 3, 'GERENTE GENERAL', 0, 'C', true);

$pdf->SetFont('Arial', '', 4);
$pdf->SetXY(10, $espacio + 63);
$pdf->MultiCell(150, 2, utf8_decode('El horario de recepción es de lunes a viernes de 8:00 am a 1:00 pm; los documentos que envíen después de este horario o los días sábados, domingos y feriados serán considerados como recibidos a partir'), 0, 'L', true);
$pdf->SetXY(10, $espacio + 65);
$pdf->MultiCell(150, 2, utf8_decode('del siguiente día hábil y deberán ser remitidos a la siguiente dirección de correo electrónico '), 0, 'L', true);
$pdf->SetXY(10, $espacio + 67);
$pdf->MultiCell(150, 2, utf8_decode('El pago es semanal todos los jueves, se programarán todos los comprobantes que cumplan con el procedimiento solicitado y hayan sido emitidos y registrados hasta el martes previo.'), 0, 'L', 1, 0, '', $espacio + 59, true, 0, false, true, 2, 'M');



$pdf->AddPage();
$pdf->Ln(5); // Espacio antes del título
if($documentoTipoId == Configuraciones::ORDEN_SERVICIO && !ObjectUtil::isEmpty($dataDocumento[0]['monto_detraccion_retencion'])){
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, utf8_decode('DETRACCIÓN'), 0, 1, 'C');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(254, 191, 0);
    $pdf->Cell(90, 7, utf8_decode('Descripción'), 1, 0, 'C', true);
    $pdf->Cell(50, 7, 'Importe', 1, 0, 'C', true);
    $pdf->Cell(50, 7, 'Porcentaje %', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 7);
    $pdf->Cell(90, 8, $dataDocumento[0]['descripcion_detraccion'], 1, 0, 'C');
    $pdf->Cell(50, 8, number_format($dataDocumento[0]['monto_detraccion_retencion'], 3), 1, 0, 'C');
    $pdf->Cell(50, 8, number_format($dataDocumento[0]['porcentaje_afecto'], 2), 1, 1, 'C');
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('DISTRIBUCIÓN DE PAGOS'), 0, 1, 'C');

$distribucionPagos = OrdenCompraServicio::create()->obtenerDistribucionPagos($documentoId);
$cont_distribucionPagos = 0;
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(254, 191, 0);
$pdf->Cell(10, 7, 'Item', 1, 0, 'C', true);
$pdf->Cell(90, 7, 'Importe', 1, 0, 'C', true);
$pdf->Cell(90, 7, 'Porcentaje %', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 7);
if (!ObjectUtil::isEmpty($distribucionPagos)) {
    foreach ($distribucionPagos as $index => $item) {
        $pdf->Cell(10, 8, $index + 1, 1, 0, 'C');
        $pdf->Cell(90, 8, number_format($item['importe'], 3), 1, 0, 'C');
        $pdf->Cell(90, 8, number_format($item['porcentaje'], 2), 1, 1, 'C');
    }
}

$pdf->AddPage();

$pdf->Image(__DIR__ .'/../../../vistas/images/Condiciones1.jpg', 0, 0, 210, 350);

$pdf->AddPage();
$pdf->Image(__DIR__ .'/../../../vistas/images/Condiciones2.jpg', 0, 0, 210, 350);

$pdf->Output();
