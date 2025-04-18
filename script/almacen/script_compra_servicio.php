<?php

include_once __DIR__ . '../../../modeloNegocio/almacen/MovimientoNegocio.php';
include_once __DIR__ . '../../../modeloNegocio/almacen/DocumentoTipoNegocio.php';
include_once __DIR__ . '../../../modeloNegocio/almacen/PersonaNegocio.php';
include_once __DIR__ . '../../../modeloNegocio/almacen/DocumentoNegocio.php';

//284 = Orden de Servicio
//282 = Orden de Compra

$documentoId = 4971;
$documentoTipoId = 282;

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

foreach ($detalle as $i => $item) {
    $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleObtenerUnidadMinera($item->movimientoBienId, $banderaUrgencia);
    $resultado = [];
    foreach ($resMovimientoBienDetalle as $dato) {
        $unidad = $dato['unidad_minera'];
        $cantidad = floatval($dato['cantidad_requerimiento_area']);
        if (!isset($resultado[$unidad])) {
            $resultado[$unidad] = 0;
        }
        $resultado[$unidad] += $cantidad;
    }

    $detalle[$i]->cantidad_requerimiento = $resultado;
}

$response = [
    'documentoId' => $documentoId,
    'documentoTipoId' => $documentoTipoId,
    'dataDocumento' => $dataDocumento,
    'referencia' => $referencia,
    'terminos_de_pago' => $terminos_de_pago,
    'entrega_en_destino' => $entrega_en_destino,
    'U_O' => $U_O,
    'organizador_entrega' => $organizador_entrega,
    'ubigeoProveedor' => $ubigeoProveedor,
    'ubigeoProveedor_entrega' => $ubigeoProveedor_entrega,
    'serieNumeroCotizacion' => $serieNumeroCotizacion,
    'serieNumeroSolicitudRequerimiento' => $serieNumeroSolicitudRequerimiento,
    'cuenta' => $cuenta,
    'detalle' => $detalle,
];


header('Content-Type: application/json');
echo json_encode($response);