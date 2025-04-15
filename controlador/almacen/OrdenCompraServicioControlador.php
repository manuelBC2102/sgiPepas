<?php

//require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/OrdenCompraServicioNegocio.php';

class OrdenCompraServicioControlador extends AlmacenIndexControlador {

    public function obtenerConfiguracionInicialListadoDocumentos()
    {
        $data = OrdenCompraServicioNegocio::create()->obtenerConfiguracionInicialListadoDocumentos();
        return $data;
    }

    public function obtenerOrdenCompraServicio()
    {
        $criterios = $this->getParametro("criterios");
        $elementosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");

        $data = OrdenCompraServicioNegocio::create()->obtenerOrdenCompraServicioXCriterios($criterios, $elementosFiltrados, $columns, $order, $start);
        $response_cantidad_total = OrdenCompraServicioNegocio::create()->obtenerCantidadOrdenCompraServicioXCriterios($criterios, $columns, $order);
        $response_cantidad_total[0]['total'];
        $elementosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];

        return $this->obtenerRespuestaDataTable($data, $elementosFiltrados, $elementosTotales);
    }

    public function visualizarOrdenCompraServicio(){
        $id = $this->getParametro("id");
        $movimientoId = $this->getParametro("movimientoId");
        return OrdenCompraServicioNegocio::create()->visualizarOrdenCompraServicio($id, $movimientoId);
    }


    public function obtenerDocumentoAdjuntoXDocumentoId(){
        $documentoId = $this->getParametro("documentoId");
        return DocumentoNegocio::create()->obtenerDocumentoAdjuntoXDocumentoId($documentoId);
    }

    public function cargarArchivosAdjuntos(){
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $lstDocumentoArchivos = $this->getParametro("lstDocumentoArchivos");
        $lstDocEliminado = $this->getParametro("lstDocEliminado");
        $documentoId = $this->getParametro("documentoId");
        return OrdenCompraServicioNegocio::create()->cargarArchivosAdjuntos($documentoId, $lstDocumentoArchivos, $lstDocEliminado,$usuarioId);
    } 

    // public function visualizarDistribucionPagos(){
    //     $documentoId = $this->getParametro("documentoId");
    //     return OrdenCompraServicioNegocio::create()->visualizarDistribucionPagos($documentoId);
    // }

    // public function obtenerDocumentoAdjuntoXDistribucionPagos(){
    //     $distribucionPagoId = $this->getParametro("distribucionPagoId");
    //     return OrdenCompraServicioNegocio::create()->obtenerDocumentoAdjuntoXDistribucionPagos($distribucionPagoId);
    // }

    // public function cargarArchivosAdjuntosDistribucionPagos(){
    //     $this->setTransaction();
    //     $usuarioId = $this->getUsuarioId();
    //     $lstDocumentoArchivos = $this->getParametro("lstDocumentoArchivos");
    //     $lstDocEliminado = $this->getParametro("lstDocEliminado");
    //     $distribucionPagoId = $this->getParametro("distribucionPagoId");
    //     $documentoId = $this->getParametro("documentoId");
    //     return OrdenCompraServicioNegocio::create()->cargarArchivosAdjuntosDistribucionPagos($distribucionPagoId, $documentoId, $lstDocumentoArchivos, $lstDocEliminado,$usuarioId);
    // } 

    public function getCompraServicio(){
        $documento = $this->getParametro("documento");
        $descripcion_documento = explode('-', $documento);
        $documentoId = $descripcion_documento[0];
        $documentoTipoId = $descripcion_documento[1];

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
                    }
                }
            }
        }

        foreach ($detalle as $i => $item) {
            $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleObtenerUnidadMinera($item->movimientoBienId);
            $detalle->cantidad_requerimiento = $resMovimientoBienDetalle[0]['cantidad_requerimiento'];
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
    }
}
