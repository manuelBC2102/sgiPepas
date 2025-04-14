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
}
