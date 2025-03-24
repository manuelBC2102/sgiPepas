<?php

require_once __DIR__ . '/../core/ModeloBase.php';
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

/**
 * Movimiento
 *
 * @author 
 */
class Movimiento extends ModeloBase {

    /**
     * 
     * @return Movimiento
     */
    static function create() {
        return parent::create();
    }

    public function obtenerDocumentosXCriterios($movimientoTipoId, $documentoTipoId, $personaId, $codigo, $serie, $numero, $fechaEmisionDesde, $fechaEmisionHasta, $fechaVencimientoDesde, $fechaVencimientoHasta, $fechaTentativaDesde, $fechaTentativaHasta, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $monedaId = null, $estadoNegocioPago = null, $proyecto = null, $serieCompra = null, $numeroCompra = null, $progreso = null, $prioridad = null, $responsable = null,$agenciaIds = null, $area = null, $requerimiento_tipo = null, $estado_cotizacion = null) {
        $this->commandPrepare("sp_movimiento_obtenerXCriterios");
        //$this->commandPrepare("sp_reporte_balance");
        $this->commandAddParameter(":vin_movimiento_tipo_id", $movimientoTipoId);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionDesde);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionHasta);
        $this->commandAddParameter(":vin_fecha_vencimiento_desde", $fechaVencimientoDesde);
        $this->commandAddParameter(":vin_fecha_vencimiento_hasta", $fechaVencimientoHasta);
        $this->commandAddParameter(":vin_fecha_tentativa_desde", $fechaTentativaDesde);
        $this->commandAddParameter(":vin_fecha_tentativa_hasta", $fechaTentativaHasta);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_moneda_id", $monedaId == ""? null :$monedaId);
        $this->commandAddParameter(":vin_estado_negocio_pago", $estadoNegocioPago == ""? null :$estadoNegocioPago);
        $this->commandAddParameter(":vin_proyecto", $proyecto == ""? null :$proyecto);
        $this->commandAddParameter(":vin_serieCompra", $serieCompra);
        $this->commandAddParameter(":vin_numeroCompra", $numeroCompra);
        $this->commandAddParameter(":vin_progreso", $progreso == ""? null:$progreso);
        $this->commandAddParameter(":vin_prioridad", $prioridad == ""? null:$prioridad);
        $this->commandAddParameter(":vin_responsable_id", $responsable == ""? null:$responsable);
        $this->commandAddParameter(":vin_agencia_id", $agenciaIds);
        $this->commandAddParameter(":vin_area", $area == ""? null:$area);        
        $this->commandAddParameter(":vin_tipo_requerimiento", $requerimiento_tipo == ""? null:$requerimiento_tipo);        
        $this->commandAddParameter(":vin_estado_cotizacion", $estado_cotizacion == ""? null:$estado_cotizacion);        
        return $this->commandGetData();
    }

    //, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start
    public function obtenerDocumentosXCriteriosExcel($movimientoTipoId, $documentoTipoId, $personaId, $codigo, $serie, $numero, $fechaEmisionDesde, $fechaEmisionHasta, $fechaVencimientoDesde, $fechaVencimientoHasta, $fechaTentativaDesde, $fechaTentativaHasta) {
        $this->commandPrepare("sp_movimiento_obtenerXCriteriosExcel");
        //$this->commandPrepare("sp_reporte_balance");
        $this->commandAddParameter(":vin_movimiento_tipo_id", $movimientoTipoId);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionDesde);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionHasta);
        $this->commandAddParameter(":vin_fecha_vencimiento_desde", $fechaVencimientoDesde);
        $this->commandAddParameter(":vin_fecha_vencimiento_hasta", $fechaVencimientoHasta);
        $this->commandAddParameter(":vin_fecha_tentativa_desde", $fechaTentativaDesde);
        $this->commandAddParameter(":vin_fecha_tentativa_hasta", $fechaTentativaHasta);
        return $this->commandGetData();
    }

    public function obtenerCantidadDocumentosXCriterios($movimientoTipoId, $documentoTipoId, $personaId, $codigo, $serie, $numero, $fechaEmisionDesde, $fechaEmisionHasta, $fechaVencimientoDesde, $fechaVencimientoHasta, $fechaTentativaDesde, $fechaTentativaHasta, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $monedaId = null, $estadoNegocioPago = null) {
        $this->commandPrepare("sp_movimiento_consulta_contador");
        //$this->commandPrepare("sp_reporte_balance");
        $this->commandAddParameter(":vin_movimiento_tipo_id", $movimientoTipoId);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionDesde);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionHasta);
        $this->commandAddParameter(":vin_fecha_vencimiento_desde", $fechaVencimientoDesde);
        $this->commandAddParameter(":vin_fecha_vencimiento_hasta", $fechaVencimientoHasta);
        $this->commandAddParameter(":vin_fecha_tentativa_desde", $fechaTentativaDesde);
        $this->commandAddParameter(":vin_fecha_tentativa_hasta", $fechaTentativaHasta);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        $this->commandAddParameter(":vin_estado_negocio_pago", $estadoNegocioPago);
        //  $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        // $this->commandAddParameter(":vin_tamanio", $start);
        return $this->commandGetData();
    }

    public function ObtenerTotalDeRegistros() {
        $this->commandPrepare("sp_obtener_CantidadDeRegistrosDeConsultas");
        return $this->commandGetData();
    }

    public function ObtenerMovimientoTipoPorOpcion($opcionId) {
        $this->commandPrepare("sp_movimiento_obtenerXOpcionId");
        $this->commandAddParameter(":vin_opcion_id", $opcionId);
        return $this->commandGetData();
    }

    public function ObtenerMovimientoTipoDocumentoTipoPorMovimientoTipoID($movimientoTipoId) {
        $this->commandPrepare("sp_movimiento_tipo_documento_tipoXMovimientoTipoID");
        $this->commandAddParameter(":vin_movimiento_tipo_id", $movimientoTipoId);
        return $this->commandGetData();
    }

    public function guardar($movimientoTipoId, $estado, $usuarioCreacionId) {
        $this->commandPrepare("sp_movimiento_guardar");
        $this->commandAddParameter(":vin_movimiento_tipo_id", $movimientoTipoId);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacionId);
        return $this->commandGetData();
    }

    public function sp_movimiento_tipo_documento_tipo_XMovimientoTipoXDocumentoTipo($movimientoTipoId, $documentoTipoId) {
        $this->commandPrepare("sp_movimiento_tipo_documento_tipo_XMovimientoTipoXDocumentoTipo");
        $this->commandAddParameter(":vin_movimmiento_tipo_id", $movimientoTipoId);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        return $this->commandGetData();
    }

    public function obtenerMovimientoTipoAcciones($movimientoTipoId, $tipoAccion = 1) {
        $this->commandPrepare("sp_movimiento_tipo_accion_obtenerPorMovimientoTipo");
        $this->commandAddParameter(":vin_movimmiento_tipo_id", $movimientoTipoId);
        $this->commandAddParameter(":vin_tipo_accion", $tipoAccion);
        return $this->commandGetData();
    }

    // FUNCIONES PARA COPIAR DOCUMENTO

    public function buscarDocumentoACopiar($empresaId, $documentoTipoIds, $personaId, $serie, $numero, $fechaEmisionInicio,
            $fechaEmisionFin, $fechaVencimientoInicio, $fechaVencimientoFin, $elementosFiltrados,
            $formaOrdenar, $columnaOrdenar, $tamanio, $transferenciaTipo, $movimientoTipoId = null,$estadoId =null, $segunIds =null) {
        $this->commandPrepare("sp_documento_buscarParaCopiar");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_documento_tipo_ids", $documentoTipoIds);
        $this->commandAddParameter(":vin_persona_id", $personaId==""?null:$personaId);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_fecha_vencimiento_desde", $fechaVencimientoInicio);
        $this->commandAddParameter(":vin_fecha_vencimiento_hasta", $fechaVencimientoFin);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limit", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $tamanio);
        $this->commandAddParameter(":vin_transferencia_tipo", $transferenciaTipo);
        $this->commandAddParameter(":vin_movimiento_tipo_id", $movimientoTipoId);
        $this->commandAddParameter(":vin_documento_estado_id", $estadoId);
        $this->commandAddParameter(":vin_segun_ids", $segunIds);
        return $this->commandGetData();
    }

    public function buscarDocumentoACopiarTotal($empresaId, $documentoTipoIds, $personaId, $serie, $numero,
            $fechaEmisionInicio, $fechaEmisionFin, $fechaVencimientoInicio,
            $fechaVencimientoFin, $formaOrdenar, $columnaOrdenar, $transferenciaTipo,
            $movimientoTipoId = null,$estadoId =null, $segunIds =null) {
        $this->commandPrepare("sp_documento_buscarParaCopiar_contador");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_documento_tipo_ids", $documentoTipoIds);
        $this->commandAddParameter(":vin_persona_id", $personaId==""?null:$personaId);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_fecha_vencimiento_desde", $fechaVencimientoInicio);
        $this->commandAddParameter(":vin_fecha_vencimiento_hasta", $fechaVencimientoFin);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_transferencia_tipo", $transferenciaTipo);
        $this->commandAddParameter(":vin_movimiento_tipo_id", $movimientoTipoId);
        $this->commandAddParameter(":vin_documento_estado_id", $estadoId);
        $this->commandAddParameter(":vin_segun_ids", $segunIds);
        return $this->commandGetData();
    }

    //FIN COPIAR DOCUMENTO

    public function registrarTramoBien($unidadMedidaId, $cantidadTramo, $bienId, $usuCreacion) {
        $this->commandPrepare("sp_bien_tramo_guardar");
        $this->commandAddParameter(":vin_unidad_medida_id", $unidadMedidaId);
        $this->commandAddParameter(":vin_cantidad", $cantidadTramo);
        $this->commandAddParameter(":vin_bien_id", $bienId);
        $this->commandAddParameter(":vin_usuario_creacion", $usuCreacion);
        return $this->commandGetData();
    }

    public function obtenerTramoBienXBienId($bienId) {
        $this->commandPrepare("sp_bien_tramo_obtenerXBienId");
        $this->commandAddParameter(":vin_bien_id", $bienId);
        return $this->commandGetData();
    }

    public function actualizarBienTramoEstado($bienTramoId, $movimientoId) {
        $this->commandPrepare("sp_bien_tramo_actualizarEstado");
        $this->commandAddParameter(":vin_bien_tramo_id", $bienTramoId);
        $this->commandAddParameter(":vin_movimiento_id", $movimientoId);
        return $this->commandGetData();
    }

    public function obtenerMovimientoTipoAccionEnvioPredeterminado($movimientoTipoId) {
        $this->commandPrepare("sp_movimiento_tipo_obtenerAccionEnvioPredeterminado");
        $this->commandAddParameter(":vin_movimmiento_tipo_id", $movimientoTipoId);
        return $this->commandGetData();
    }

    public function obtenerMovimientoTipoColumnaListaXMovimientoTipoId($movimientoTipoId) {
        $this->commandPrepare("sp_movimiento_tipo_columna_lista_obtenerXMovimientoTipoId");
        $this->commandAddParameter(":vin_movimiento_tipo_id", $movimientoTipoId);
        return $this->commandGetData();
    }

    public function obtenerMovimientoEntradaSalidaXFechaXBienId($fechaEmision, $bienId) {
        $this->commandPrepare("sp_movimiento_bien_obtenerEntradaSalidaXFechaXBienId");
        $this->commandAddParameter(":vin_fecha", $fechaEmision);
        $this->commandAddParameter(":vin_bien_id", $bienId);
        return $this->commandGetData();
    }

    public function getUserEmailByUserId($id) {
        $this->commandPrepare("sp_movimiento_getUserEmailById");
        $this->commandAddParameter(":vin_user_id", $id);
        return $this->commandGetData();
    }

    public function verificarDocumentoObligatorioExiste($actualId) {
        $this->commandPrepare("sp_movimiento_verificarDocumentoObligatorioExiste");
        $this->commandAddParameter(":vin_actual_id", $actualId);
        return $this->commandGetData();
    }

    public function verificarDocumentoEsObligatorioXOpcionID($opcionId) {
        $this->commandPrepare("sp_movimiento_DocumentoEsObligatorioXOpcionID");
        $this->commandAddParameter(":vin_opcion_id", $opcionId);
        return $this->commandGetData();
    }

    public function obtenerEstadoNegocioXMovimientoId($movimientoId) {
        $this->commandPrepare("sp_movimiento_obtenerEstadoNegocioXMovimientoId");
        $this->commandAddParameter(":vin_movimiento_id", $movimientoId);

        return $this->commandGetData();
    }

    public function obtenerMovimientoTipoRecepcionXEmpresaIdXCodigo($empresaId, $codigo) {
        $this->commandPrepare("sp_movimiento_tipo_recepcion_obtenerXEmpresaIdXCodigo");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_codigo", $codigo);
        return $this->commandGetData();
    }

    public function obtenerDocumentoRelacionadoTipoRecepcion($documentoId) {
        $this->commandPrepare("sp_documento_relacionado_tipo_recepcion_obtenerXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    public function obtenerNroTicketEFACT($documentoId) {
        $this->commandPrepare("sp_obtener_nro_ticket_EFACT");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    public function obtenerDocumentosPorVerificarAnulacionSunat() {
        $this->commandPrepare("sp_obtener_documentosPorVerificarAnulacionSunat");
        return $this->commandGetData();
    }

    public function obtenerProgresoXMovimientoTipo($movimientoTipoId) {
        $this->commandPrepare("sp_obtener_progresoXMovimientoTipo");
        $this->commandAddParameter(":vin_movimientoTipo_id", $movimientoTipoId);
        return $this->commandGetData();
    }

    public function obtenerPrioridadXMovimientoTipo($movimientoTipoId) {
        $this->commandPrepare("sp_obtener_prioridadXMovimientoTipo");
        $this->commandAddParameter(":vin_movimientoTipo_id", $movimientoTipoId);
        return $this->commandGetData();
    }

    public function insertarListaComprobacion($documentoId, $descripcion, $orden, $estado) {
        $this->commandPrepare("sp_insertar_documento_listaComprobacion");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_descripcion", $descripcion);
        $this->commandAddParameter(":vin_orden", $orden);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    public function editarEstadoListaComprobacion($documentoListaId, $estado) {
        $this->commandPrepare("sp_editar_documento_listaComprobacion");
        $this->commandAddParameter(":vin_documentoLista_id", $documentoListaId);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    public function ordenarArribaEstadoListaComprobacion($documentoListaIdActual, $documentoListaIdSiguiente, $ordenActual, $ordenSiguiente) {
        $this->commandPrepare("sp_ordenarArriba_estado_listaComprobacion");
        $this->commandAddParameter(":vin_documentoLista_id_actual", $documentoListaIdActual);
        $this->commandAddParameter(":vin_documentoLista_id_siguiente", $documentoListaIdSiguiente);
        $this->commandAddParameter(":vin_orden_actual", $ordenActual);
        $this->commandAddParameter(":vin_orden_siguiente", $ordenSiguiente);
        return $this->commandGetData();
    }

    public function obtenerhistoricoAcciones() {
        $this->commandPrepare("sp_obtener_historicoAcciones");
        return $this->commandGetData();
    }
    
    public function obtenerHistoricoAccionXId($id) {
        $this->commandPrepare("sp_historico_accion_obtenerXId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    
    public function obtenerDocumentoHistoricoUltimoXDocumentoId($documentoId) {
        $this->commandPrepare("sp_documento_historico_obtenerUltimoXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    public function insertarDocumentoHistorico($documentoId, $idAccion, $valoresjson, $usuarioId, $tipo) {
        $this->commandPrepare("sp_insertar_documento_historico");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_historico_accion_id", $idAccion);
        $this->commandAddParameter(":vin_valor", $valoresjson);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_tipo", $tipo);
        return $this->commandGetData();
    }

    public function obtenerTipoRequerimientoXMovimientoTipo($movimientoTipoId) {
        $this->commandPrepare("sp_obtener_tipoRequerimientoXMovimientoTipo");
        $this->commandAddParameter(":vin_movimientoTipo_id", $movimientoTipoId);
        return $this->commandGetData();
    }
}
