<?php

require_once __DIR__ . "/../core/ModeloBase.php";

class OrdenCompraServicio extends ModeloBase
{
    /**
     *
     * @return OrdenCompraServicio
     */
    static function create()
    {
        return parent::create();
    }

    public function obtenerOrdenCompraServicioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId = null, $atencion, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start)
    {
        $this->commandPrepare("sp_ordenCompraServicio_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_tipo_id", $tipoId);
        $this->commandAddParameter(":vin_entrega_destino_id", $almacenId);
        $this->commandAddParameter(":vin_atencion", $atencion);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        return $this->commandGetData();
    }

    public function obtenerCantidadOrdenCompraServicioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId, $atencion, $columnaOrdenar, $formaOrdenar)
    {
        $this->commandPrepare("sp_ordenCompraServicio_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_tipo_id", $tipoId);
        $this->commandAddParameter(":vin_entrega_destino_id", $almacenId);
        $this->commandAddParameter(":vin_atencion", $atencion);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        return $this->commandGetData();
    }

    public function obtenerDistribucionPagos($documentoId, $distribucionPagoId = null)
    {
        $this->commandPrepare("sp_distribucion_pago_obtenerXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_id", $distribucionPagoId);
        return $this->commandGetData();
    }

    public function cambiarEstadoDistribucionPagos($documentoId)
    {
        $this->commandPrepare("sp_distribucion_pagoCambiarEstado");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    public function obtenerDocumentoAdjuntoXDistribucionPagos($distribucionPagoId)
    {
        $this->commandPrepare("sp_documento_adjunto_obtenerXDistribucionPagoId");
        $this->commandAddParameter(":vin_distribucion_pago_id", $distribucionPagoId);
        return $this->commandGetData();
    }

    function insertarActualizarDocumentoAdjunto($archivoAdjuntoId, $distribucionPagoId, $nombreArchivo, $nombreGenerado, $usuarioCreacionId, $estado = null, $tipo_archivoId  = null, $contenido_archivo = null) {
        $this->commandPrepare("sp_distribucion_pago_adjunto_insertarActualizar");
        $this->commandAddParameter(":vin_id", $archivoAdjuntoId);
        $this->commandAddParameter(":vin_distribucion_pago_id", $distribucionPagoId);
        $this->commandAddParameter(":vin_archivo", $nombreArchivo);
        $this->commandAddParameter(":vin_nombre", $nombreGenerado);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacionId);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_tipo_archivoId", $tipo_archivoId);
        $this->commandAddParameter(":vin_contenido_archivo", $contenido_archivo);
        return $this->commandGetData();
    }

    public function aprobarRechazarDocumentoAdjunto($documentoAdjuntoId, $estado, $comentario, $usuarioId)
    {
        $this->commandPrepare("sp_documento_adjuntoAprobarRechazar");
        $this->commandAddParameter(":vin_id", $documentoAdjuntoId);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_comentario", $comentario);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }

    public function facturacion_proveedor_registrar( $facturadorSerie, $facturadorCorrelativo, $subtotal, $igv, $totalFactura, $detraccion, $netoPago, $usuarioId, $comentarioEfact, $transportistaId, $monedaId = null, $detraccion_porcentaje, $codigo_detracion){
        $this->commandPrepare("sp_facturacion_proveedor_registrar");
        $this->commandAddParameter(":vin_serie", $facturadorSerie);
        $this->commandAddParameter(":vin_correlativo", $facturadorCorrelativo);
        $this->commandAddParameter(":vin_subtotal", $subtotal);
        $this->commandAddParameter(":vin_igv", $igv);
        $this->commandAddParameter(":vin_totalFactura", $totalFactura);
        $this->commandAddParameter(":vin_detraccion", $detraccion);
        $this->commandAddParameter(":vin_netoPago", $netoPago);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_comentarioEfact", $comentarioEfact);
        $this->commandAddParameter(":vin_persona", $transportistaId);
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        $this->commandAddParameter(":vin_detraccion_porcentaje", $detraccion_porcentaje);
        $this->commandAddParameter(":vin_codigo_detracion", $codigo_detracion);
        return $this->commandGetData();
    }
}
