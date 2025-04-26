<?php

require_once __DIR__ . '/../core/ModeloBase.php'; 
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

/**
 * Movimiento
 *
 * @author 
 */
class MovimientoBien extends ModeloBase {
    /**
     * 
     * @return MovimientoBien
     */
    static function create() {
        return parent::create();
    }

    public function guardar($movimientoId, $organizadorId, $bienId, $unidadMedidaId, $cantidad, $valorMonetario, $estado, $usuarioCreacionId,$precioTipoId=null,$utilidad=null,$utilidadPorcentaje=null,$checkIgv=1,$adValorem=0,$comentarioDetalle=null,$agenciaId=null, $agrupadorDetalle = null, $ticket = null, $CeCoId = null, $precioPostor1 = null, $precioPostor2 = null, $precioPostor3 = null, $esCompra = null, $cantidad_solicitada = null, $postor_ganador_id = null) {
        $this->commandPrepare("sp_movimiento_bien_guardar");
        $this->commandAddParameter(":vin_movimiento_id", $movimientoId);
        $this->commandAddParameter(":vin_organizador_id", $organizadorId);
        $this->commandAddParameter(":vin_bien_id", $bienId);
        $this->commandAddParameter(":vin_unidad_medida_id", $unidadMedidaId);
        $this->commandAddParameter(":vin_cantidad", $cantidad);
        $this->commandAddParameter(":vin_valor_monetario", $valorMonetario);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacionId);
        $this->commandAddParameter(":vin_precio_tipo_id", $precioTipoId);
        $this->commandAddParameter(":vin_utilidad", $utilidad);
        $this->commandAddParameter(":vin_utilidad_porcentaje", $utilidadPorcentaje);
        $this->commandAddParameter(":vin_incluye_igv", $checkIgv);
        $this->commandAddParameter(":vin_ad_valorem", $adValorem);
        $this->commandAddParameter(":vin_comentario_detalle", $comentarioDetalle);
        $this->commandAddParameter(":vin_agencia_id", $agenciaId);
        $this->commandAddParameter(":vin_agrupador_id", $agrupadorDetalle == ""?null:$agrupadorDetalle);
        $this->commandAddParameter(":vin_ticket", $ticket);
        $this->commandAddParameter(":vin_centro_costo_id", $CeCoId);
        $this->commandAddParameter(":vin_precio_postor1", $precioPostor1);
        $this->commandAddParameter(":vin_precio_postor2", $precioPostor2);
        $this->commandAddParameter(":vin_precio_postor3", $precioPostor3);
        $this->commandAddParameter(":vin_es_compra", $esCompra);
        $this->commandAddParameter(":vin_cantidad_solicitada", $cantidad_solicitada);
        $this->commandAddParameter(":vin_postor_ganador_id", $postor_ganador_id);
        return $this->commandGetData();
    }
    
    public function obtenerXIdMovimiento($movimientoId) {
        $this->commandPrepare("sp_movimiento_bien_obtenerXMovimiento");
        $this->commandAddParameter(":vin_movimiento_id", $movimientoId);
        return $this->commandGetData();
    }
    
    public function obtenerDetalleTransferenciaXIdMovimiento($movimientoId) {// para recibir transferencia
        $this->commandPrepare("sp_movimiento_bien_obtenerDetalleTransferenciaXMovimiento");
        $this->commandAddParameter(":vin_movimiento_id", $movimientoId);
        return $this->commandGetData();
    }
    
    public function movimientoBienDetalleGuardar($movimientoBienId,$columnaCodigo,$valorCadena,$valorFecha,$usuarioId, $valorExtra = null){
        $this->commandPrepare("sp_movimiento_bien_detalle_guardar");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        $this->commandAddParameter(":vin_columna_codigo", $columnaCodigo);
        $this->commandAddParameter(":vin_valor_cadena", $valorCadena);
        $this->commandAddParameter(":vin_valor_fecha", $valorFecha);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_valor_extra", $valorExtra);
        return $this->commandGetData();        
    }
    
    public function obtenerMovimientoBienDetalleXMovimientoBienId($movimientoBienId){
        $this->commandPrepare("sp_movimiento_bien_detalle_obtenerActivosXMovimientoBienId");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        return $this->commandGetData();        
    }

    public function guardarDocumentoAtencionSolicitud($origenId, $destinoId, $cantidadAtendida, $usuarioId)
    {
        $this->commandPrepare("sp_movimiento_bien_guardarAtencion");
        $this->commandAddParameter(":vin_movimiento_origen", $origenId);
        $this->commandAddParameter(":vin_movimiento_destino", $destinoId);
        $this->commandAddParameter(":vin_cantidad", $cantidadAtendida);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);

        return $this->commandGetData();
    }

    public function obtenerBienesIdRelacionadosXDocumentoId($documentoId)
    {
//        $this->commandPrepare("sp_movimiento_bien_obtenerBienesIdRelacionadosXDocumentoId");
        $this->commandPrepare("sp_movimiento_bien_obtenerBienesIdXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);

        return $this->commandGetData();
    }

    public function obtenerMovimientoIdXDocumentoId($documentoId){

        $this->commandPrepare("sp_movimiento_bien_obtenerMovimientoIdXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);

        return $this->commandGetData();
    }
    
    public function obtenerMovimientoBienXId($moviBienId){
        $this->commandPrepare("sp_movimiento_bien_obtenerXId");
        $this->commandAddParameter(":vin_id", $moviBienId);
        return $this->commandGetData();        
    }
    
    public function obtenerXMovimientoIdXMovimientoIdRelacion($movimientoId,$movimientoIdHijos) {
        $this->commandPrepare("sp_movimiento_bien_obtenerXMovimientoIdXMovimientoIdRelacion");
        $this->commandAddParameter(":vin_movimiento_id", $movimientoId);
        $this->commandAddParameter(":vin_movimiento_id_relacion", $movimientoIdHijos);
        return $this->commandGetData();
    }
    
    public function obtenerGRCantidadesPorOCId($ocId) {
        $this->commandPrepare("sp_movimiento_bien_obtenerGRCantidadesPorOCId");
        $this->commandAddParameter(":vin_oc_id", $ocId);
        return $this->commandGetData();
    }
    
    public function obtenerXMovimientoIds($movimientoIds){
        $this->commandPrepare("sp_movimiento_bien_obtenerXMovimientoIds");
        $this->commandAddParameter(":vin_movimiento_ids", $movimientoIds);
        return $this->commandGetData();        
    }
    
    //EDICION DE DOCUMENTO
    public function actualizarEstadoXId($movimientoBienId,$estado){
        $this->commandPrepare("sp_movimiento_bien_actualizarEstadoXId");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();                         
    }
    
    public function editar($movimientoBienId, $movimientoId,$organizadorId, $bienId, $unidadMedidaId, $cantidad, $valorMonetario, $estado, $usuarioCreacionId,$precioTipoId=null,$utilidad=null,$utilidadPorcentaje=null,$checkIgv=1,$adValorem=0,$comentarioDetalle=null,$agenciaId=null, $agrupadorDetalle = null, $ticket = null, $CeCoId = null, $precioPostor1 = null, $precioPostor2 = null, $precioPostor3 = null, $esCompra = null, $cantidad_solicitada = null, $postor_ganador_id = null) {
        $this->commandPrepare("sp_movimiento_bien_editar");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        $this->commandAddParameter(":vin_movimiento_id", $movimientoId);
        $this->commandAddParameter(":vin_organizador_id", $organizadorId);
        $this->commandAddParameter(":vin_bien_id", $bienId);
        $this->commandAddParameter(":vin_unidad_medida_id", $unidadMedidaId);
        $this->commandAddParameter(":vin_cantidad", $cantidad);
        $this->commandAddParameter(":vin_valor_monetario", $valorMonetario == null?0:$valorMonetario);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacionId);
        $this->commandAddParameter(":vin_precio_tipo_id", $precioTipoId);
        $this->commandAddParameter(":vin_utilidad", $utilidad);
        $this->commandAddParameter(":vin_utilidad_porcentaje", $utilidadPorcentaje);
        $this->commandAddParameter(":vin_incluye_igv", $checkIgv);
        $this->commandAddParameter(":vin_ad_valorem", $adValorem == null?0:$adValorem);
        $this->commandAddParameter(":vin_comentario_detalle", $comentarioDetalle);
        $this->commandAddParameter(":vin_agencia_id", $agenciaId);  
        $this->commandAddParameter(":vin_agrupador_id", $agrupadorDetalle == ""?null:$agrupadorDetalle);
        $this->commandAddParameter(":vin_ticket", $ticket);
        $this->commandAddParameter(":vin_centro_costo_id", $CeCoId);
        $this->commandAddParameter(":vin_precio_postor1", $precioPostor1);
        $this->commandAddParameter(":vin_precio_postor2", $precioPostor2);
        $this->commandAddParameter(":vin_precio_postor3", $precioPostor3);
        $this->commandAddParameter(":vin_es_compra", $esCompra);
        $this->commandAddParameter(":vin_cantidad_solicitada", $cantidad_solicitada);
        $this->commandAddParameter(":vin_postor_ganador_id", $postor_ganador_id);
        return $this->commandGetData();
    }

    public function obtenerMovimientoBienXRequerimientoXAreaId($areaId, $tipoRequerimiento, $urgencia){
        $this->commandPrepare("sp_movimiento_bien_obtenerXRQRegistradoXAreaxId");
        $this->commandAddParameter(":vin_area_id", $areaId);
        $this->commandAddParameter(":vin_tipo_requerimiento", $tipoRequerimiento);
        $this->commandAddParameter(":vin_urgencia", $urgencia);
        return $this->commandGetData();                         
    }

    public function obtenerMovimientoBienXRequerimientoXGrupoProductoxId($grupoProductoId, $tipoRequerimiento, $urgencia){
        $this->commandPrepare("sp_movimiento_bien_obtenerXRQRegistradoXGrupoProductoxId");
        $this->commandAddParameter(":vin_bien_tipo_id", $grupoProductoId);
        $this->commandAddParameter(":vin_tipo_requerimiento", $tipoRequerimiento);
        $this->commandAddParameter(":vin_urgencia", $urgencia);
        return $this->commandGetData();                         
    }

    public function editarMovimientoBienConsolidadoRelacionadoxId($movimientoBienIds, $movimientoBienId){
        $this->commandPrepare("sp_movimiento_bien_editarConsolidadoRelacionadoXId");
        $this->commandAddParameter(":vin_movimiento_bien_ids", $movimientoBienIds);
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        return $this->commandGetData(); 
    }

    public function editarMovimientoBienPostorGanadorXId($movimientoBienId, $postor_ganador_id){
        $this->commandPrepare("sp_movimiento_bien_editarPostorGanadorXId");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        $this->commandAddParameter(":vin_postor_ganador_id", $postor_ganador_id);
        return $this->commandGetData(); 
    }

    public function obtenerMovimientoBienXRelacionConsolidado($documentoId){
        $this->commandPrepare("sp_movimiento_bienXRelacionConsolidado");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();                         
    }

    public function movimientoBienDetalleCambiarEstadoXId($movimientoBienId){
        $this->commandPrepare("sp_movimiento_bien_detalle_cambiarEstadoXId");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        return $this->commandGetData();        
    }

    public function movimientoBienDetalleObtenerReservaRequerimientoXMovimientoBienId($movimientoBienId){
        $this->commandPrepare("sp_movimiento_bien_ObtenerReservaRequerimientoXMovimientoBienId");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        return $this->commandGetData();        
    }

    public function movimientoBienDetalleobtenerDetalleXRequerimientoId($movimientoBienId, $documentoTipoOrigenId = null){
        $this->commandPrepare("sp_movimiento_bien_detalle_obtenerDetalleXRequerimientoId");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        $this->commandAddParameter(":vin_documentoTipoOrigenId", $documentoTipoOrigenId);
        return $this->commandGetData();        
    }

    public function movimientoBienDetalleobtenerDetalleXRequerimientoIdEditar($movimientoBienId, $documentoTipoOrigenId = null){
        $this->commandPrepare("sp_movimiento_bien_detalle_obtenerDetalleXRequerimientoIdEditar");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        $this->commandAddParameter(":vin_documentoTipoOrigenId", $documentoTipoOrigenId);
        return $this->commandGetData();        
    }

    public function obtenerMovimientoBienDetalleObtenerSolicitudR($movimientoBienId){
        $this->commandPrepare("sp_movimiento_bien_detalleObtenerSolicitudR");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        return $this->commandGetData();        
    }

    public function obtenerMovimientoBienDetalleObtenerUnidadMinera($movimientoBienId, $banderaUrgencia){
        $this->commandPrepare("sp_movimiento_bien_obtenerUnidadMineraXMovimientoBienId");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        $this->commandAddParameter(":vin_bandera_urgencia", $banderaUrgencia);
        return $this->commandGetData();        
    }

    public function movimientoBienDetalleEditarCadena($movimientoBienId,$columnaCodigo,$valorCadena,$valorFecha,$usuarioId, $valorExtra = null){
        $this->commandPrepare("sp_movimiento_bien_detalle_editar");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        $this->commandAddParameter(":vin_columna_codigo", $columnaCodigo);
        $this->commandAddParameter(":vin_valor_cadena", $valorCadena);
        $this->commandAddParameter(":vin_valor_fecha", $valorFecha);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_valor_extra", $valorExtra);
        return $this->commandGetData();        
    }

    public function movimientoBienDetalleEditarEstado($movimientoBienId, $columnaCodigo){
        $this->commandPrepare("sp_movimiento_bien_detalle_editarEstado");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        $this->commandAddParameter(":vin_columna_codigo", $columnaCodigo);
        return $this->commandGetData();        
    }
}
