<?php

require_once __DIR__ . '/../core/ModeloBase.php';


class Almacenes extends ModeloBase
{
    /**
     *
     * @return Almacenes
     */
    static function create()
    {
        return parent::create();
    }

    public function obtenerPersonaOrganizadorXOrganizadorTipo($personaId, $organizadorTipoId)
    {
        $this->commandPrepare("sp_persona_organizadorXOrganizadorTipo");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_organizador_tipo_id", $organizadorTipoId);
        return $this->commandGetData();
    }

    public function obtenerPersonaOrganizadorXOrganizadorPadreXOrganizadorTipo($personaId, $organizadorPadreId, $organizadorTipoId)
    {
        $this->commandPrepare("sp_persona_organizadorXOrganizadorPadreXOrganizadorTipo");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_organizador_padre_id", $organizadorPadreId);
        $this->commandAddParameter(":vin_organizador_tipo_id", $organizadorTipoId);
        return $this->commandGetData();
    }

    public function guardarDetalleRecepcionEstado($movimientoId)
    {
        $this->commandPrepare("sp_movimiento_bien_editarBanderaRecepcionEstado");
        $this->commandAddParameter(":vin_movimiento_id", $movimientoId);
        return $this->commandGetData();
    }

    public function guardarDetalleRecepcion($movimientoBienId, $cantidad_recepcion, $bandera_recepcion)
    {
        $this->commandPrepare("sp_movimiento_bien_editarBanderaRecepcion");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        $this->commandAddParameter(":vin_bandera_recepcion", $bandera_recepcion);
        $this->commandAddParameter(":vin_cantidad_recepcion", $cantidad_recepcion);
        return $this->commandGetData();
    }

    public function obtenerRecepcionXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId = null, $serie, $numero, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start)
    {
        $this->commandPrepare("sp_recepcion_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_tipo_id", $tipoId);
        $this->commandAddParameter(":vin_organizador_id", $almacenId);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        return $this->commandGetData();
    }

    public function obtenerCantidadRecepcionXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId = null, $serie, $numero, $columnaOrdenar, $formaOrdenar)
    {
        $this->commandPrepare("sp_recepcion_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_tipo_id", $tipoId);
        $this->commandAddParameter(":vin_organizador_id", $almacenId);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        return $this->commandGetData();
    }

    public function cantidadRecepcion($movimientoBienId)
    {
        $this->commandPrepare("sp_movimiento_bien_detalleCantidadRecepcion");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        return $this->commandGetData();
    }

    public function registrarPaquete($bienId, $organizador_destinoId, $unidad_mineraId, $indice, $cantidad, $usuarioId, $max_grupo_qr)
    {
        $this->commandPrepare("sp_paquete_registrar");
        $this->commandAddParameter(":vin_bien_id", $bienId);
        $this->commandAddParameter(":vin_organizador_destino_id", $organizador_destinoId);
        $this->commandAddParameter(":vin_unidad_minera_id", $unidad_mineraId);
        $this->commandAddParameter(":vin_indice", $indice);
        $this->commandAddParameter(":vin_cantidad", $cantidad);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_grupo_qr", $max_grupo_qr);
        return $this->commandGetData();
    }

    public function registrarPaqueteTraking($movimientoBienId, $paqueteId, $organizadorId, $tipo, $comentario, $usuarioId)
    {
        $this->commandPrepare("sp_paquete_traking_registrar");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        $this->commandAddParameter(":vin_paquete_id", $paqueteId);
        $this->commandAddParameter(":vin_organizador_id", $organizadorId);
        $this->commandAddParameter(":vin_tipo", $tipo);
        $this->commandAddParameter(":vin_comentario", $comentario);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }

    public function paquete_trakingObtenerRecepcionXmovimientoBienId($movimientoBienId)
    {
        $this->commandPrepare("sp_paquete_traking_obtenerRecepcionXmovimientoBienId");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        return $this->commandGetData();
    }

    public function paquete_trakingObtenerDetalle($bienId, $almacenIds)
    {
        $this->commandPrepare("sp_almacenado_obtenerDetalleXCriterios");
        $this->commandAddParameter(":vin_bien_id", $bienId);
        $this->commandAddParameter(":vin_organizador_ids", $almacenIds);
        return $this->commandGetData();
    }

    public function paquete_trakingObtenerXmovimientoBienId($movimientoBienId)
    {
        $this->commandPrepare("sp_paquete_traking_obtenerXmovimientoBienId");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        return $this->commandGetData();
    }

    //Almacenar
    //Almacenado
    public function obtenerPaqueteAlmacenadoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $almacenIds = null, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start, $id = null)
    {
        $this->commandPrepare("sp_almacenado_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_organizador_ids", $almacenIds);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function obtenerCantidadPaqueteAlmacenadoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $almacenIds, $columnaOrdenar, $formaOrdenar, $id = null)
    {
        $this->commandPrepare("sp_almacenado_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_organizador_ids", $almacenIds);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function paquete_traking_cambiarEstadoXPaqueteId($paqueteId, $estadoId)
    {
        $this->commandPrepare("sp_paquete_traking_cambiarEstadoXPaqueteId");
        $this->commandAddParameter(":vin_paquete_id", $paqueteId);
        $this->commandAddParameter(":vin_estado", $estadoId);
        return $this->commandGetData();
    }

    public function paquete_cambiarEstadoXPaqueteId($paqueteId, $estadoId)
    {
        $this->commandPrepare("sp_paquete_cambiarEstadoXPaqueteId");
        $this->commandAddParameter(":vin_paquete_id", $paqueteId);
        $this->commandAddParameter(":vin_estado", $estadoId);
        return $this->commandGetData();
    }

    public function obtenerMovimientoPaqueteTraking($id)
    {
        $this->commandPrepare("sp_obtener_detalle_movimientoPaqueteTraking");
        $this->commandAddParameter(":vin_paquete_id", $id);
        return $this->commandGetData();
    }

    public function obtenerMovimientoPaqueteDetalleTraking($id)
    {
        $this->commandPrepare("sp_obtener_detalle_movimientoPaqueteDetalleTraking");
        $this->commandAddParameter(":vin_paquete_id", $id);
        return $this->commandGetData();
    }

    public function obtenerMaximoGrupoPaquete()
    {
        $this->commandPrepare("sp_paquete_obtenerMaximoGrupoPaquete");
        return $this->commandGetData();
    }

    public function obtenerPaqueteXGrupoPaquete($grupo_qr)
    {
        $this->commandPrepare("sp_paquete_obtenerPaqueteXGrupoProducto");
        $this->commandAddParameter(":vin_grupo_qr", $grupo_qr);
        return $this->commandGetData();
    }

    public function obtener_datosPaquete($paqueteId)
    {
        $this->commandPrepare("sp_paquete_obtenerPaqueteXId");
        $this->commandAddParameter(":vin_paquete_id", $paqueteId);
        return $this->commandGetData();
    }

    //Despacho
    public function obtenerDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenTransitoId, $almacenId = null, $serie, $numero, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start)
    {
        $this->commandPrepare("sp_despacho_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_tipo_id", $tipoId);
        $this->commandAddParameter(":vin_organizador_transito_id", $almacenTransitoId);
        $this->commandAddParameter(":vin_organizador_id", $almacenId);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        return $this->commandGetData();
    }

    public function obtenerCantidadDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenTransitoId, $almacenId = null, $serie, $numero, $columnaOrdenar, $formaOrdenar)
    {
        $this->commandPrepare("sp_despacho_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_tipo_id", $tipoId);
        $this->commandAddParameter(":vin_organizador_transito_id", $almacenTransitoId);
        $this->commandAddParameter(":vin_organizador_id", $almacenId);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        return $this->commandGetData();
    }

    public function obtenerPaqueteDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $almacenIds = null, $estadoId, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start, $id = null)
    {
        $this->commandPrepare("sp_paquete_traking_despacho_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_organizador_ids", $almacenIds);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estadoId);
        return $this->commandGetData();
    }

    public function obtenerCantidadPaqueteDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $almacenIds, $estadoId, $columnaOrdenar, $formaOrdenar, $id = null)
    {
        $this->commandPrepare("sp_paquete_traking_despacho_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_organizador_ids", $almacenIds);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estadoId);
        return $this->commandGetData();
    }

    public function obtener_vehiculoTransportistaXVehiculoId($vehiculoId)
    {
        $this->commandPrepare("sp_obtenerVehiculoTransportistaXVehiculoId");
        $this->commandAddParameter(":vin_vehiculo_id", $vehiculoId);
        return $this->commandGetData();
    }

    public function obtener_vehiculoTransportistaId($transportistaId)
    {
        $this->commandPrepare("sp_vehiculo_obtenerXTransportista");
        $this->commandAddParameter(":vin_transportista_id", $transportistaId);
        return $this->commandGetData();
    }

    public function obtener_paqueteXAlmacenId($resultadoOrganizadorIds)
    {
        $this->commandPrepare("sp_paqueteObtenerXOrganizadorIds");
        $this->commandAddParameter(":vin_organizadorIds", $resultadoOrganizadorIds);
        return $this->commandGetData();
    }

    //Recepcion despacho
    public function obtenerPaqueteRecepcionDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $almacenIds = null, $serie, $numero, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start, $id = null)
    {
        $this->commandPrepare("sp_recepcion_despacho_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_organizador_ids", $almacenIds);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function obtenerCantidadPaqueteRecepcionDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $almacenIds, $serie, $numero, $columnaOrdenar, $formaOrdenar, $id = null)
    {
        $this->commandPrepare("sp_recepcion_despacho_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_organizador_ids", $almacenIds);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function registrarPaqueteDetalle($movimientoId = null, $paqueteId, $bienId, $organizadorId, $tipo, $cantidad, $unidadMedidaId, $comentario, $usuarioId)
    {
        $this->commandPrepare("sp_paquete_detalle_registrar");
        $this->commandAddParameter(":vin_movimiento_id", $movimientoId);
        $this->commandAddParameter(":vin_paquete_id", $paqueteId);
        $this->commandAddParameter(":vin_bien_id", $bienId);
        $this->commandAddParameter(":vin_organizador_id", $organizadorId);
        $this->commandAddParameter(":vin_tipo", $tipo);
        $this->commandAddParameter(":vin_cantidad", $cantidad);
        $this->commandAddParameter(":vin_unidad_medida_id", $unidadMedidaId);
        $this->commandAddParameter(":vin_comentario", $comentario);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }

    //Entrega
    public function obtenerEntregaXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId = null, $bandera, $serie, $numero, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start)
    {
        $this->commandPrepare("sp_entrega_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_tipo_id", $tipoId);
        $this->commandAddParameter(":vin_organizador_id", $almacenId);
        $this->commandAddParameter(":vin_bandera", $bandera);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        return $this->commandGetData();
    }

    public function obtenerCantidadEntregaXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId = null, $bandera, $serie, $numero, $columnaOrdenar, $formaOrdenar)
    {
        $this->commandPrepare("sp_entrega_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_tipo_id", $tipoId);
        $this->commandAddParameter(":vin_organizador_id", $almacenId);
        $this->commandAddParameter(":vin_bandera", $bandera);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        return $this->commandGetData();
    }

    public function getAllAreaXPersonaId($personaId)
    {
        $this->commandPrepare("sp_area_getAllXPersonaId");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }

    public function getAllRequerimientoXatenderXPersonaId($personaId)
    {
        $this->commandPrepare("sp_obtenerRequerimientoXEntregarXPersonaId");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }

    public function obtenerStockActual($bienId, $organizadorId, $unidadMedidaId, $bandera)
    {
        $this->commandPrepare("sp_paquete_detalle_obtenerStockActual");
        $this->commandAddParameter(":vin_bien_id", $bienId);
        $this->commandAddParameter(":vin_organizador_id", $organizadorId);
        $this->commandAddParameter(":vin_unidad_medida_id", $unidadMedidaId);
        $this->commandAddParameter(":vin_bandera", $bandera);
        return $this->commandGetData();
    }

    public function obtenerpaquete_detalleXIdMovimiento($movimientoId)
    {
        $this->commandPrepare("sp_paquete_detalle_obtenerXMovimientoId");
        $this->commandAddParameter(":vin_movimiento_id", $movimientoId);
        return $this->commandGetData();
    }

    public function getDataVehiculoXPlacaId($placaId)
    {
        $this->commandPrepare("sp_vehiculo_obtenerXPlacaId");
        $this->commandAddParameter(":vin_placa_id", $placaId);
        return $this->commandGetData();
    }

    public function obtenerBienXTextoXOrganizadorIds($texto1, $texto2, $organizadorIds)
    {
        $this->commandPrepare("sp_paquete_detalle_buscarXTexto");
        $this->commandAddParameter(":vin_texto1", $texto1);
        $this->commandAddParameter(":vin_texto2", $texto2);
        $this->commandAddParameter(":vin_organizador_ids", $organizadorIds);
        return $this->commandGetData();
    }

    public function obtenerStockActualLogico($bienId, $organizadorId)
    {
        $this->commandPrepare("sp_movimiento_bien_obtenerStockLogico");
        $this->commandAddParameter(":vin_bien_id", $bienId);
        $this->commandAddParameter(":vin_organizador_id", $organizadorId);
        return $this->commandGetData();
    }

    //Inventario
    public function obtenerDataInventarioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $bienIdFormateado, $bienTipoIdFormateado, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start)
    {
        $this->commandPrepare("sp_inventario_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_organizador_ids", $resultadoOrganizadorIds);
        $this->commandAddParameter(":vin_bien_ids", $bienIdFormateado);
        $this->commandAddParameter(":vin_bien_tipo_ids", $bienTipoIdFormateado);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        return $this->commandGetData();
    }

    public function obtenerCantidadDataInventarioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $bienIdFormateado, $bienTipoIdFormateado, $columnaOrdenar, $formaOrdenar)
    {
        $this->commandPrepare("sp_inventario_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_organizador_ids", $resultadoOrganizadorIds);
        $this->commandAddParameter(":vin_bien_ids", $bienIdFormateado);
        $this->commandAddParameter(":vin_bien_tipo_ids", $bienTipoIdFormateado);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        return $this->commandGetData();
    }

    public function obtenerDataInventarioXCriteriosExcel($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $bienIdFormateado, $bienTipoIdFormateado, $columnaOrdenar, $formaOrdenar)
    {
        $this->commandPrepare("sp_inventario_obtenerXCriteriosExcel");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_organizador_ids", $resultadoOrganizadorIds);
        $this->commandAddParameter(":vin_bien_ids", $bienIdFormateado);
        $this->commandAddParameter(":vin_bien_tipo_ids", $bienTipoIdFormateado);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        return $this->commandGetData();
    }

    //Recepcion mina
    public function obtenerPaqueteRecepcionMinaXCriterios($fechaEmisionInicio, $fechaEmisionFin, $almacenIds = null, $serie, $numero, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start, $id = null)
    {
        $this->commandPrepare("sp_recepcion_mina_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_organizador_ids", $almacenIds);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function obtenerCantidadPaqueteRecepcionMinaXCriterios($fechaEmisionInicio, $fechaEmisionFin, $almacenIds = null, $serie, $numero, $columnaOrdenar, $formaOrdenar, $id = null)
    {
        $this->commandPrepare("sp_recepcion_mina_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_organizador_ids", $almacenIds);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
}
