<?php

require_once __DIR__ . '/../core/ModeloBase.php';
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

/**
 * Documento
 *
 * @author 
 */
class Documento extends ModeloBase {

    /**
     *
     * @return Documento
     */
    static function create() {
        return parent::create();
    }

  // TODO: Inicio Guardar Documento - Percepcion
  public function guardar($documentoTipoId, $movimientoId, $personaId, $direccionId, $organizadorId, $adjuntoId, $codigo, $serie, $numero, $fechaEmision, $fechaVencimiento, $fechaTentativa, $descripcion, $comentario, $importeTotal, $importeIgv, $importeSubTotal, $estado, $monedaId, $usuarioCreacionId, $cuentaId = null, $actividadId = null, $retencionDetraccionId = null, $utilidadTotal = null, $utilidadPorcentajeTotal = null, $cambioPersonalizado = null, $tipoPago = null, $importeNoAfecto = null, $periodoId = null, $banderaProductoDuplicado = 0, $detraccionId = null, $afectoDetraccionRetencion = null, $porcentajeDetraccionRetencion = null, $montoDetraidoRetencion = null, $contOperacionTipoId = null, $esEar = 0, $importeOtros = 0, $importeExoneracion = 0, $importeIcbp = 0,$afectoAImpuesto = null,$percepcion = null, $igv_porcentaje = null, $es_rq = null, $tipoCambio = null) {
    $this->commandPrepare("sp_documento_guardar");
    $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
    $this->commandAddParameter(":vin_movimiento_id", $movimientoId);
    $this->commandAddParameter(":vin_persona_id", $personaId);
    $this->commandAddParameter(":vin_direccion_id", $direccionId);
    $this->commandAddParameter(":vin_organizador_id", $organizadorId);
    $this->commandAddParameter(":vin_adjunto_id", $adjuntoId);
    $this->commandAddParameter(":vin_codigo", $codigo);
    $this->commandAddParameter(":vin_serie", $serie);
    $this->commandAddParameter(":vin_numero", $numero);
    $this->commandAddParameter(":vin_fecha_emision", $fechaEmision);
    $this->commandAddParameter(":vin_fecha_vencimiento", $fechaVencimiento);
    $this->commandAddParameter(":vin_fecha_tentativa", $fechaTentativa);
    $this->commandAddParameter(":vin_descripcion", $descripcion);
    $this->commandAddParameter(":vin_comentario", $comentario);
    $this->commandAddParameter(":vin_importe_total", $importeTotal);
    $this->commandAddParameter(":vin_importe_igv", $importeIgv);
    $this->commandAddParameter(":vin_importe_sub_total", $importeSubTotal);
    $this->commandAddParameter(":vin_estado", $estado);
    $this->commandAddParameter(":vin_moneda", $monedaId);
    $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacionId);
    $this->commandAddParameter(":vin_cuenta_id", $cuentaId);
    $this->commandAddParameter(":vin_actividad_id", $actividadId);
    $this->commandAddParameter(":vin_tipo_retencion_detraccion", $retencionDetraccionId);
    $this->commandAddParameter(":vin_utilidad_total", $utilidadTotal);
    $this->commandAddParameter(":vin_utilidad_porcentaje_total", $utilidadPorcentajeTotal);
    $this->commandAddParameter(":vin_cambio_personalizado", $cambioPersonalizado);
    $this->commandAddParameter(":vin_tipo_pago", $tipoPago);
    $this->commandAddParameter(":vin_noafecto", $importeNoAfecto);
    $this->commandAddParameter(":vin_periodo_id", $periodoId);
    $this->commandAddParameter(":vin_bandera_producto_duplicado", $banderaProductoDuplicado);
    $this->commandAddParameter(":vin_detraccion_id", $detraccionId);
    $this->commandAddParameter(":vin_afecto_detraccion_retencion", $afectoDetraccionRetencion == ""? null : $afectoDetraccionRetencion);
    $this->commandAddParameter(":vin_porcentaje_afecto", $porcentajeDetraccionRetencion == ""? null : $porcentajeDetraccionRetencion);
    $this->commandAddParameter(":vin_monto_detraccion_retencion", $montoDetraidoRetencion == ""? null : $montoDetraidoRetencion);
    $this->commandAddParameter(":vin_cont_operacion_tipo_id", $contOperacionTipoId);
    $this->commandAddParameter(":vin_es_ear", $esEar);
    $this->commandAddParameter(":vin_importe_otro", $importeOtros);
    $this->commandAddParameter(":vin_importe_exoneracion", $importeExoneracion);
    $this->commandAddParameter(":vin_icbp", $importeIcbp);
    $this->commandAddParameter(":vin_afecto_impuesto", $afectoAImpuesto);
    $this->commandAddParameter(":vin_percepcion", $percepcion);
    $this->commandAddParameter(":vin_igv_porcentaje", $igv_porcentaje);
    $this->commandAddParameter(":vin_es_rq", $es_rq);
    $this->commandAddParameter(":vin_tipo_cambio", $tipoCambio);
    return $this->commandGetData();
  }

    function obtenerXId($documentoId, $documentoTipoId) {
        $this->commandPrepare("sp_documento_obtenerXid");
        $this->commandAddParameter(":vin_id", $documentoId);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        return $this->commandGetData();
    }

    function anular($documentoId) {
        $this->commandPrepare("sp_documento_anularXId");
        $this->commandAddParameter(":vin_id", $documentoId);
        return $this->commandGetData();
    }

    function eliminar($documentoId) {
        $this->commandPrepare("sp_documento_eliminarXId");
        $this->commandAddParameter(":vin_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerDocumentoAPagar($documentoId, $fechaPago = null) {
        $this->commandPrepare("sp_documento_obtenerAPagar");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_fecha_pago", $fechaPago);
        return $this->commandGetData();
    }

    function obtenerDocumentoDatos($documentoId) {
        $this->commandPrepare("sp_documento_obtener_datosxId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerFechaPrimerDocumento() {
        $this->commandPrepare("sp_documento_obtenerFechaPrimerDocumento");
        return $this->commandGetData();
    }

    function obtenerDetalleDocumento($documentoId) {
        $this->commandPrepare("sp_movimiento_obtenerDetalle");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerComentarioDocumento($documentoId) {
        $this->commandPrepare("sp_documento_obtenerComentarioXid");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerNumeroAutoXDocumentoTipo($documentoTipoId,$serie) {
        $this->commandPrepare("sp_documento_obtenerNumeroAutoXDocumentoTipo");
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_serie", $serie);
        return $this->commandGetData();
    }
    //agregado
    public function actualizarEARDocumentoReembolso($earId,$idsgireembolso) {
        $this->commandPrepare("sp_ear_solicitudes_actualizarXsgi_documento_reembolso_id");
        $this->commandAddParameter("vin_ear_id", $earId);
        $this->commandAddParameter("vin_id", $idsgireembolso);
        return $this->commandGetData();
    }
    function obtenerNumeroAutoIncrementalXDocumentoTipo($documentoTipoId) {
        $this->commandPrepare("sp_documento_obtenerNumeroAutoIncrementalXDocumentoTipo");
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        return $this->commandGetData();
    }

    function obtenerXSerieNumero($documentoTipoId, $serie, $numero) {
        $this->commandPrepare("sp_documento_obtenerXSerieNumero");
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        return $this->commandGetData();
    }

    function obtenerDetalleDocumentoPago($documentoId) {
        $this->commandPrepare("sp_documento_pago_viasualizarDetalle");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerDataDocumentoACopiar($documentoTipoOrigenId, $documentoTipoDestinoId, $documentoId) {
        $this->commandPrepare("sp_documento_obtenerDataDocumentoACopiar");
        $this->commandAddParameter(":vin_documento_tipo_origen_id", $documentoTipoOrigenId);
        $this->commandAddParameter(":vin_documento_tipo_destino_id", $documentoTipoDestinoId);
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function guardarDocumentoRelacionado($documentoId, $documentoRelacionadoId, $valorCheck, $estado, $usuarioCreacion, $relacionEar = null) {
        $this->commandPrepare("sp_documento_relacionado_guardar");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_documento_relacionado_id", $documentoRelacionadoId);
        $this->commandAddParameter(":vin_valor_check", $valorCheck);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacion);
        $this->commandAddParameter(":vin_relacion_ear", $relacionEar);
        return $this->commandGetData();
    }

    function insertarDocumentoDocumentoEstado($documentoId, $documento_estado, $usuarioId, $comentario = NULL) {
        $this->commandPrepare("sp_documento_documento_estado_insertar");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_documento_estado_id", $documento_estado);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_comentario", $comentario);
        return $this->commandGetData();
    }

    function ActualizarDocumentoEstadoId($documentoId, $documentoEstadoId, $usuarioId, $accion = NULL, $comentario = NULL) {
        $this->commandPrepare("sp_documento_documento_estadoActualizarDocumentoEstadoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_documento_estado_id", $documentoEstadoId);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_accion", $accion);
        $this->commandAddParameter(":vin_comentario", $comentario);
        return $this->commandGetData();
    }

    function obtenerDocumentosRelacionadosXDocumentoId($documentoId) {
        $this->commandPrepare("sp_documento_relacionado_obtenerXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerSoloDocumentosRelacionados($documentoId) {
        $this->commandPrepare("sp_documento_relacion_obtenerSoloXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerDocumentosRelacionados($documentoId) {
        $this->commandPrepare("sp_documento_relacionado_obtenerRelacionadosXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);

        return $this->commandGetData();
    }

    function obtenerDataDocumentoACopiarRelacionada($documentoOrigenId, $documentoDestinoId, $documentoId) {
        $this->commandPrepare("sp_documento_tipo_dato_copia_obtenerXdocumento");
        $this->commandAddParameter(":vin_documento_origen_id", $documentoOrigenId);
        $this->commandAddParameter(":vin_documento_destino_id", $documentoDestinoId);
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerDireccionEmpresa($documentoId) {
        $this->commandPrepare("sp_documento_obtenerDireccionEmpresaXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function actualizarTipoRetencionDetraccion($documentoId, $tipoRetencionDetraccion) {
        $this->commandPrepare("sp_documento_actualizarTipoRetencionDetraccion");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_tipo_retencion_detraccion", $tipoRetencionDetraccion);
        return $this->commandGetData();
    }

    function actualizarComentarioDocumento($documentoId, $comentario) {
        $this->commandPrepare("sp_documento_actualizarComentario");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_comentario", $comentario);
        return $this->commandGetData();
    }

    function buscarDocumentosXOpcionXSerieNumero($opcionId, $busqueda) {
        $this->commandPrepare("sp_documento_buscarXOpcionXSerieNumero");
        $this->commandAddParameter(":vin_opcion_id", $opcionId);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

  // TODO: Inicio Guardar Edicion
  public function editarDocumento($documentoId, $personaId, $direccionId, $organizadorId, $adjuntoId, $codigo, $serie, $numero, $fechaEmision, $fechaVencimiento, $fechaTentativa, $descripcion, $comentario, $importeTotal, $importeIgv, $importeSubTotal, $monedaId, $cuentaId = null, $actividadId = null, $retencionDetraccionId = null, $percepcion = null, $periodoId = null, $tipoPago = null, $banderaProductoDuplicado = 0, $detraccionId = null, $afectoDetraccionRetencion = null, $porcentajeDetraccionRetencion = null, $montoDetraidoRetencion = null, $contOperacionTipoId = null, $importeOtros = 0, $importeExoneracion = 0, $importeIcbp = 0, $afectoAImpuesto = null, $igv_porcentaje = null) {
    $this->commandPrepare("sp_documento_editar");
    $this->commandAddParameter(":vin_documento_id", $documentoId);
    $this->commandAddParameter(":vin_persona_id", $personaId);
    $this->commandAddParameter(":vin_direccion_id", $direccionId);
    $this->commandAddParameter(":vin_organizador_id", $organizadorId);
    $this->commandAddParameter(":vin_adjunto_id", $adjuntoId);
    $this->commandAddParameter(":vin_codigo", $codigo);
    $this->commandAddParameter(":vin_serie", $serie);
    $this->commandAddParameter(":vin_numero", $numero);
    $this->commandAddParameter(":vin_fecha_emision", $fechaEmision);
    $this->commandAddParameter(":vin_fecha_vencimiento", $fechaVencimiento);
    $this->commandAddParameter(":vin_fecha_tentativa", $fechaTentativa);
    $this->commandAddParameter(":vin_descripcion", $descripcion);
    $this->commandAddParameter(":vin_comentario", $comentario);
    $this->commandAddParameter(":vin_importe_total", $importeTotal);
    $this->commandAddParameter(":vin_importe_igv", $importeIgv);
    $this->commandAddParameter(":vin_importe_sub_total", $importeSubTotal);
    $this->commandAddParameter(":vin_moneda", $monedaId);
    $this->commandAddParameter(":vin_cuenta_id", $cuentaId);
    $this->commandAddParameter(":vin_actividad_id", $actividadId);
    $this->commandAddParameter(":vin_tipo_retencion_detraccion", $retencionDetraccionId);
    $this->commandAddParameter(":vin_percepcion", $percepcion);
    $this->commandAddParameter(":vin_periodo_id", $periodoId);
    $this->commandAddParameter(":vin_tipo_pago", $tipoPago);
    $this->commandAddParameter(":vin_bandera_producto_duplicado", $banderaProductoDuplicado);
    $this->commandAddParameter(":vin_detraccion_id", $detraccionId);
    $this->commandAddParameter(":vin_afecto_detraccion_retencion", $afectoDetraccionRetencion == ""? null : $afectoDetraccionRetencion);
    $this->commandAddParameter(":vin_porcentaje_afecto", $porcentajeDetraccionRetencion);
    $this->commandAddParameter(":vin_monto_detraccion_retencion", $montoDetraidoRetencion);
    $this->commandAddParameter(":vin_cont_operacion_tipo_id", $contOperacionTipoId);
    $this->commandAddParameter(":vin_importe_otro", $importeOtros);
    $this->commandAddParameter(":vin_importe_exoneracion", $importeExoneracion);
    $this->commandAddParameter(":vin_icbp", $importeIcbp);
    $this->commandAddParameter(":vin_afecto_impuesto", $afectoAImpuesto);
    $this->commandAddParameter(":vin_igv_porcentaje", $igv_porcentaje);
    return $this->commandGetData();
  }
  // TODO: Fin Guardar Edicion

    public function obtenerXDocumentoTipoXGrupoUnico($documentoTipoId, $grupoUnico, $valor) {
        $this->commandPrepare("sp_documento_obtenerXDocumentoTipoXGrupoUnico");
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_grupo_unico", $grupoUnico);
        $this->commandAddParameter(":vin_valor", $valor);
        return $this->commandGetData();
    }

    function buscarDocumentosOperacionXOpcionXSerieNumero($opcionId, $busqueda) {
        $this->commandPrepare("sp_documento_operacion_buscarXOpcionXSerieNumero");
        $this->commandAddParameter(":vin_opcion_id", $opcionId);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    function obtenerPersonaXDocumentoId($documentoId) {
        $this->commandPrepare("sp_persona_obtenerXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function buscarDocumentosXTipoDocumentoXSerieNumero($documentoTipoIdStringArray, $busqueda) {
        $this->commandPrepare("sp_documento_buscarXTipoDocumentoXSerieNumero");
        $this->commandAddParameter(":vin_tipo_documento_set", $documentoTipoIdStringArray);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    public function buscarDocumentosXDocumentoPagar($empresaId, $tipo, $tipoProvisionPago, $busqueda) {
        $this->commandPrepare("sp_documento_buscarXDocumentoPagar");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_tipo", $tipo);
        $this->commandAddParameter(":vin_tipoProvisionPago", $tipoProvisionPago);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    public function buscarDocumentosXDocumentoPago($empresaId, $tipo, $tipoProvisionPago, $busqueda) {
        $this->commandPrepare("sp_documento_buscarXDocumentoPago");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_tipo", $tipo);
        $this->commandAddParameter(":vin_tipoProvisionPago", $tipoProvisionPago);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    public function buscarDocumentosXDocumentoPagado($empresaId, $tipo, $tipoProvisionPago, $busqueda) {
        $this->commandPrepare("sp_documento_buscarXDocumentoPagado");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_tipo", $tipo);
        $this->commandAddParameter(":vin_tipoProvisionPago", $tipoProvisionPago);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    public function obtenerRelacionesDocumento($documentoId) {
        $this->commandPrepare("sp_documento_relacionadoObtenerTodosXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    public function obtenerDocumentoRelacionadoImpresion($documentoId) {
        $this->commandPrepare("sp_documento_relacionado_obtenerParaImpresion");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    public function validarCorrelatividadNumericaConFechaEmision($documentoTipoId, $serie, $numero, $fechaEmision) {
        $this->commandPrepare("sp_documento_validarCorrelatividadNumericaFecha");
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_fecha_emision", $fechaEmision);
        return $this->commandGetData();
    }

  // TODO: Inicio obtener para editar
  public function obtenerDocumentoXDocumentoId($documentoId) {
    $this->commandPrepare("sp_documento_obtenerXDocumentoId");
    $this->commandAddParameter(":vin_documento_id", $documentoId);
    return $this->commandGetData();
  }
  // TODO: Fin obtener para editar

    public function obtenerFechasPosterioresDocumentosSalidas($fechaEmision, $bienId, $organizadorId) {
        $this->commandPrepare("sp_documento_obtenerFechasPosterioresDocumentosSalidas");
        $this->commandAddParameter(":vin_fecha_emision", $fechaEmision);
        $this->commandAddParameter(":vin_bien_id", $bienId);
        $this->commandAddParameter(":vin_organizador_id", $organizadorId);
        return $this->commandGetData();
    }

    //operaciones: modal de copia
    function buscarDocumentosOperacionXTipoDocumentoXSerieNumero($documentoTipoIdStringArray, $busqueda) {
        $this->commandPrepare("sp_documento_operacion_buscarXTipoDocumentoXSerieNumero");
        $this->commandAddParameter(":vin_tipo_documento_set", $documentoTipoIdStringArray);
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        return $this->commandGetData();
    }

    function obtenerNumeroSerieCorelativoPagosCobransa($documentoTipo_Tipo, $letraIdentificador) {
        $this->commandPrepare("sp_documento_obtenerNumeroSerieCorelativoPagosCobransa");
        $this->commandAddParameter(":vin_tipo_documento_tipo", $documentoTipo_Tipo);
        $this->commandAddParameter(":vin_caracterIdentificador", $letraIdentificador);
        return $this->commandGetData();
    }

    function obtenerNumeroSerieCorrelativoPagos($documentoTipo_Tipo, $letraIdentificador) {
        $this->commandPrepare("sp_pago_obtenerNumeroSerieCorelativo");
        $this->commandAddParameter(":vin_tipo_documento_tipo", $documentoTipo_Tipo);
        $this->commandAddParameter(":vin_caracterIdentificador", $letraIdentificador);
        return $this->commandGetData();
    }

    function obtenerIdTipoDocumentoXIdDocumento($idDocumento) {
        $this->commandPrepare("sp_documento_obtenerTipoDocumentoXidDocumento");
        $this->commandAddParameter(":vin_documento_id", $idDocumento);
        return $this->commandGetData();
    }

    function actualizarEstadoQRXDocumentoId($documentoId, $estadoQR) {
        $this->commandPrepare("sp_documento_actualizar_estado_qr");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_estado_qr", $estadoQR);
        return $this->commandGetData();
    }

    function obtenerDocumentoIdXMovimientoBienId($movimientoBienId) {
        $this->commandPrepare("sp_documento_obtenerDocumentoIdXMovimientoBienId");
        $this->commandAddParameter(":vin_movimiento_bien_id", $movimientoBienId);
        return $this->commandGetData();
    }

    function insertarDocumentoAdjunto($documentoId, $nombreArchivo, $nombreGenerado, $usuarioCreacionId, $contenidoArchivo) {
        $this->commandPrepare("sp_documento_adjunto_insertar");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_archivo", $nombreArchivo);
        $this->commandAddParameter(":vin_nombre", $nombreGenerado);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacionId);
        $this->commandAddParameter(":vin_contenido_archivo", $contenidoArchivo);
        return $this->commandGetData();
    }

    function insertarActualizarDocumentoAdjunto($archivoAdjuntoId, $documentoId, $nombreArchivo, $nombreGenerado, $usuarioCreacionId, $estado = null, $tipo_archivoId  = null, $contenido_archivo = null, $serie_numero = null, $codigo_detraccion = null, $porcentaje_detraccion = null, $monto_detraccion = null, $monedaId = null) {
        $this->commandPrepare("sp_documento_adjunto_insertarActualizar");
        $this->commandAddParameter(":vin_id", $archivoAdjuntoId);
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_archivo", $nombreArchivo);
        $this->commandAddParameter(":vin_nombre", $nombreGenerado);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacionId);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_tipo_archivoId", $tipo_archivoId);
        $this->commandAddParameter(":vin_contenido_archivo", $contenido_archivo);
        $this->commandAddParameter(":vin_serie_numero", $serie_numero);
        $this->commandAddParameter(":vin_codigo_detraccion", $codigo_detraccion);
        $this->commandAddParameter(":vin_porcentaje_detraccion", $porcentaje_detraccion);
        $this->commandAddParameter(":vin_monto_detraccion", $monto_detraccion);
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        return $this->commandGetData();
    }

    function obtenerDocumentoAdjuntoXDocumentoId($documentoId) {
        $this->commandPrepare("sp_documento_adjunto_obtenerXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerAnticiposPendientesXPersonaId($personaId, $monedaId) {
        $this->commandPrepare("sp_documento_obtenerAnticiposPendientesXPersonaId");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        return $this->commandGetData();
    }

    function obtenerPlanillaImportacionXDocumentoId($documentoId) {
        $this->commandPrepare("sp_documento_obtenerPlanillaImportacionXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerDuaXTicketEXT($documentoId) {
        $this->commandPrepare("sp_documento_obtenerDuaXTicketEXT");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function actualizarTipoCambioMontoNoAfectoXDocumentoId($documentoId, $tc, $montoNoAfecto) {
        $this->commandPrepare("sp_documento_actualizarTipoCambioMontoNoAfectoXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_cambio_personalizado", $tc);
        $this->commandAddParameter(":vin_monto_no_afecto", $montoNoAfecto);
        return $this->commandGetData();
    }

    function obtenerDocumentoRelacionadoXDocumentoIdXDocumentoRelacionadoId($documentoId, $documentoRelacionId) {
        $this->commandPrepare("sp_documento_relacionado_obtenerXDocumentoIdXDocumentoRelacionId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_documento_relacion_id", $documentoRelacionId);
        return $this->commandGetData();
    }

    function obtenerDataAlmacenVirtualXDocumentoId($documentoTipoId) {
        $this->commandPrepare("sp_movimiento_obtenerPendientesPorReposicionXDocumentoTipoId");
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        return $this->commandGetData();
    }

    function obtenerDocumentoDuaXDocumentoId($documentoId) {
        $this->commandPrepare("sp_documento_dua_obtenerXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerDocumentoRelacionadoActivoXDocumentoId($documentoId) {
        $this->commandPrepare("sp_documento_relacionado_obtenerActivosXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerDocumentoFEXId($documentoId) {
        $this->commandPrepare("sp_documento_obtenerFExId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerSerieNumeroXDocumentoId($documentoId) {
        $this->commandPrepare("sp_documento_obtenerSerieNumeroXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function actualizarNroSecuencialBajaXDocumentoId($documentoId, $nroSecuencialBaja, $ticket) {
        $this->commandPrepare("sp_documento_actualizarNroSecuenciaBajaXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_nro_secuencial_baja", $nroSecuencialBaja);
        $this->commandAddParameter(":vin_nro_efact_ticket", $ticket);
        return $this->commandGetData();
    }

    function actualizarMotivoAnulacionXDocumentoId($documentoId, $motivoAnulacion) {
        $this->commandPrepare("sp_documento_actualizarMotivoAnulacionXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_motivo_anulacion", $motivoAnulacion);
        return $this->commandGetData();
    }

    function obtenerNumeroNotaCredito($documentoTipoId, $documentoRelacionadoTipo) {
        $this->commandPrepare("sp_documento_obtenerNumeroAutoXNotaCreditoTipo");
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_documento_relacionado_tipo", $documentoRelacionadoTipo);
        return $this->commandGetData();
    }

    function actualizarEfactEstadoAnulacionXDocumentoId($documentoId, $estado) {
        $this->commandPrepare("sp_documento_actualizarEfactEstadoAnulacionXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    function obtenerIdDocumentosResumenDiario() {
        $this->commandPrepare("sp_obtenerIdDocumentosResumenDiario");
        return $this->commandGetData();
    }

    function actualizarEstadoEfactAnulacionXDocumentoId($documentoId, $estado, $ticket) {
        $this->commandPrepare("sp_documento_actualizarEstadoEfactAnulacionXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_efact_estado_anulacion", $estado);
        $this->commandAddParameter(":vin_nro_efact_ticket", $ticket);
        return $this->commandGetData();
    }

  // EDICION
  // TODO: Inicio Obtener para Editar
  function obtenerDataDocumentoACopiarEdicion($documentoTipoOrigenId, $documentoTipoDestinoId, $documentoId) {
    $this->commandPrepare("sp_documento_obtenerDataDocumentoACopiarEdicion");
    $this->commandAddParameter(":vin_documento_tipo_origen_id", $documentoTipoOrigenId);
    $this->commandAddParameter(":vin_documento_tipo_destino_id", $documentoTipoDestinoId);
    $this->commandAddParameter(":vin_documento_id", $documentoId);
    return $this->commandGetData();
  }
  // TODO: Fin Obtener para Editar

    function actualizarEstadoXDocumentoIdXEstado($documentoId, $estado) {
        $this->commandPrepare("sp_documento_actualizar_estadoXDocumentoIdXEstado");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    public function obtenerDocumentosEarXCriterios($movimientoTipoId, $documentoTipoId, $personaId, $codigo, $serie, $numero, $fechaEmisionDesde, $fechaEmisionHasta, $fechaVencimientoDesde, $fechaVencimientoHasta, $fechaTentativaDesde, $fechaTentativaHasta, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $monedaId = null, $estadoNegocioPago = null, $serieDoc, $numeroDoc) {
        $this->commandPrepare("sp_documento_ear_obtenerXCriterios");
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
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        $this->commandAddParameter(":vin_estado_negocio_pago", $estadoNegocioPago);
        $this->commandAddParameter(":vin_serie_doc", $serieDoc);
        $this->commandAddParameter(":vin_numero_doc", $numeroDoc);
        return $this->commandGetData();
    }

    function validarImportePago($documentoIdSumaImporte, $documentoId) {
        $this->commandPrepare("sp_documento_validar_importe_pago");
        $this->commandAddParameter(":vin_documento_id_suma_importe", $documentoIdSumaImporte);
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    public function actualizarEstadoXId($documentoId, $estado) {
        $this->commandPrepare("sp_documento_actualizarEstadoXId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    function actualizarEstadoEfactAnulacionValido($documentoId, $estado) {
        $this->commandPrepare("sp_documento_actualizarEstadoEfactAnulacionValido");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    function actualizarEfactPdfNombre($documentoId, $nombrePDF) {
        $this->commandPrepare("sp_documento_actualizarEfactPdfNombre");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_pdf_nombre", $nombrePDF);
        return $this->commandGetData();
    }

    function actualizarEfactEstadoRegistro($documentoId, $estadoRegistro, $resultado) {
        $this->commandPrepare("sp_documento_actualizarEfactEstadoRegistro");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_efact_estado_registro", $estadoRegistro);
        $this->commandAddParameter(":vin_resultado", $resultado);
        return $this->commandGetData();
    }

    function obtenerDocumentosPendientesDeGeneracionEfact($contadorMaximoRegistro) {
        $this->commandPrepare("sp_documento_obtenerDocumentosPendientesDeGeneracionEfact");
        $this->commandAddParameter(":vin_contador_maximo", $contadorMaximoRegistro);
        return $this->commandGetData();
    }

    function actualizarEfactContadorRegistro($documentoId) {
        $this->commandPrepare("sp_documento_actualizarEfactContadorRegistro");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerFechaPrimeraOrdenTrabajo() {
        $this->commandPrepare("sp_documento_obtenerFechaPrimeraOrdenTrabajo");
        return $this->commandGetData();
    }

    function obtenerCabeceraOrdenTrabajo($documentoId) {
        $this->commandPrepare("sp_documento_ordenTrabajo_visualizarCabecera");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerDetalleFacturacionOrdenTrabajo($documentoId) {
        $this->commandPrepare("sp_documento_ordenTrabajo_visualizarDetalleFacturacion");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerDetalleSolicitadoOrdenTrabajo($documentoId) {
        $this->commandPrepare("sp_documento_ordenTrabajo_visualizarDetalleSolicitado");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerDetalleEAROrdenTrabajo($documentoId) {
        $this->commandPrepare("sp_documento_ordenTrabajo_visualizarDetalleEAR");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    public function obtenerListaComprobacion($documentoId) {
        $this->commandPrepare("sp_documento_obtenerListaComprobacion");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    public function obtenerDocumentoHistorial($documentoId) {
        $this->commandPrepare("sp_documento_historico_obtenerXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }
    public function obtenerDocumentoHistorialXId($id) {
        $this->commandPrepare("sp_documento_historico_obtenerXId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function obtenerNotificacionesOTParaEmail($empresaId, $dias) {
        $this->commandPrepare("sp_obtener_notificacionesOT_para_email");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_numero_dias", $dias);
        return $this->commandGetData();
    }

    public function obtenerCorreosSegunPerfilAsistenteyGerente() {
        $this->commandPrepare("sp_obtener_correos_segun_perfil");
        return $this->commandGetData();
    }

    public function obtenerCorreosSegunPerfilAsistente() {
        $this->commandPrepare("sp_obtener_correos_segun_perfil_asistente");
        return $this->commandGetData();
    }

    public function obtenerCorreoResponsableEnvioEmail($responsable) {
        $this->commandPrepare("sp_obtener_correo_responsable_envio_email");
        $this->commandAddParameter(":vin_responsable", $responsable);
        return $this->commandGetData();
    }

    public function obtenerDocumentoLiquidacionDetalle($documentId) {
        $this->commandPrepare("sp_documento_liquidacion_obtenerDataExcel");
        $this->commandAddParameter(":vin_documento_id", $documentId);
        return $this->commandGetData();
    }

    public function obtenerDocumentoCotizacionDetalle($documentId) {
        $this->commandPrepare("sp_documento_cotizacion_obtenerDataExcel");
        $this->commandAddParameter(":vin_documento_id", $documentId);
        return $this->commandGetData();
    }

    public function eliminarDocumentoRelacionado($documentoIdOrigen, $documentoIdARelacionar, $usuarioId) {
        $this->commandPrepare("sp_documento_relacion_eliminarXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoIdOrigen);
        $this->commandAddParameter(":vin_documento_relacionado_id", $documentoIdARelacionar);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        return $this->commandGetData();
    }

    public function obtenerDocumentoEstadoLista() {
        $this->commandPrepare("sp_documento_estado_obtenerLista");
        return $this->commandGetData();
    }

    function eliminarDocumentosAdjuntosXDocumentoId($documentoId) {
        $this->commandPrepare("sp_documento_adjunto_eliminarXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }


    public function obtenerDocumentosRevisionContabilidadXCriterios($movimientoTipoId, $documentoTipoId, $personaId, $codigo, $serie, $numero, $fechaEmisionDesde, $fechaEmisionHasta, $fechaVencimientoDesde, $fechaVencimientoHasta, $fechaTentativaDesde, $fechaTentativaHasta, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $monedaId = null, $estadoNegocioPago = null, $serieDoc, $numeroDoc) {
        $this->commandPrepare("sp_documento_contabilidad_obtenerXCriterios");
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
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        $this->commandAddParameter(":vin_estado_negocio_pago", $estadoNegocioPago);
        $this->commandAddParameter(":vin_serie_doc", $serieDoc);
        $this->commandAddParameter(":vin_numero_doc", $numeroDoc);
        return $this->commandGetData();
    }

    function obtenerDocumentoXRucXSerieNumero($empresaId, $documentoTipoId, $codigoIdentifacion, $serieNumero) {
        $this->commandPrepare("sp_documento_validarExisteXRucXSerieNumero");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_codigo_identificacion", $codigoIdentifacion);
        $this->commandAddParameter(":vin_serie_numero", $serieNumero);
        return $this->commandGetData();
    }

    function obtenerDocumentoPagoImportacionXInvoiceComercialXTipoDocumentoSUNAT($documentoId, $documentoTipoSunat) {
        $this->commandPrepare("sp_documento_obtenerDocumentoPagoXInvoiceCommercialXCodigoSUNAT");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_codigo_sunat", $documentoTipoSunat);
        return $this->commandGetData();
    }

    function obtenerTiposDocumentoXMatriz() {
        $this->commandPrepare("sp_documento_obtenerTiposDocumentoXMatriz");
        return $this->commandGetData();
    }

    function obtenerDocumentoDocumentoEstadoXdocumentoId($documentoId, $estado) {
        $this->commandPrepare("sp_documento_documento_estadoXDocumentoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    function obtenerDocumentosXAreaId($areaId, $tipoRequerimiento, $urgencia) {
        $this->commandPrepare("sp_documentoXAreaId");
        $this->commandAddParameter(":vin_area_id", $areaId);
        $this->commandAddParameter(":vin_tipo_requerimiento", $tipoRequerimiento);
        $this->commandAddParameter(":vin_urgencia", $urgencia);
        return $this->commandGetData();
    }

    function obtenerDocumentosRelacionadosXIngresoSalidaReserva($documentoId) {
        $this->commandPrepare("sp_documento_relacionado_obtenerXIngresoSalidaReserva");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function guardar_documento_detalle($documentoId, $personaId, $monedaId, $tipoCambio, $igv, $uoId, $tiempoEntrega, $tiempo, $condicionPago, $diasPago, $sumilla, $referecnia, $usuarioId) {
        $this->commandPrepare("sp_documento_detalle_guardar");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        $this->commandAddParameter(":vin_tipo_cambio", $tipoCambio);
        $this->commandAddParameter(":vin_igv", $igv);
        $this->commandAddParameter(":vin_uoId", $uoId);
        $this->commandAddParameter(":vin_tiempo_entrega", $tiempoEntrega);
        $this->commandAddParameter(":vin_tiempo", $tiempo);
        $this->commandAddParameter(":vin_condicion_pago", $condicionPago);
        $this->commandAddParameter(":vin_dias_pago", $diasPago);
        $this->commandAddParameter(":vin_sumilla", $sumilla);
        $this->commandAddParameter(":vin_referencia", $referecnia);
        $this->commandAddParameter(":vin_usuario_registro", $usuarioId);
        return $this->commandGetData();
    }

    function obtenerDocumentoDetalleDatos($documentoId) {
        $this->commandPrepare("sp_documento_detalle_obtener_datosxId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function editar_documento_detalle($documentoId, $personaId, $monedaId, $tipoCambio, $igv, $uoId, $tiempoEntrega, $tiempo, $condicionPago, $diasPago, $sumilla, $usuarioId, $banderaIgv, $porcentajeIgv) {
        $this->commandPrepare("sp_documento_detalle_editar");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        $this->commandAddParameter(":vin_tipo_cambio", $tipoCambio);
        $this->commandAddParameter(":vin_igv", $igv);
        $this->commandAddParameter(":vin_uoId", $uoId);
        $this->commandAddParameter(":vin_tiempo_entrega", $tiempoEntrega);
        $this->commandAddParameter(":vin_tiempo", $tiempo);
        $this->commandAddParameter(":vin_condicion_pago", $condicionPago);
        $this->commandAddParameter(":vin_dias_pago", $diasPago);
        $this->commandAddParameter(":vin_sumilla", $sumilla);
        $this->commandAddParameter(":vin_usuario_registro", $usuarioId);
        $this->commandAddParameter(":vin_bandera_igv", $banderaIgv);
        $this->commandAddParameter(":vin_porcentaje_igv", $porcentajeIgv);
        return $this->commandGetData();
    }

    public function guardarDocumentoDetalleDistribucionPagos($documento_detalle_id, $fechaPago, $importePago, $dias, $porcentaje, $glosa, $usuarioId) {
        $this->commandPrepare("sp_documento_detalle_distribucion_pago_guardar");
        $this->commandAddParameter("vin_documento_detalle_id", $documento_detalle_id);
        $this->commandAddParameter("vin_fecha_pago", $fechaPago);
        $this->commandAddParameter("vin_importe_pago", $importePago);
        $this->commandAddParameter("vin_dias", $dias);
        $this->commandAddParameter("vin_porcentaje", $porcentaje);
        $this->commandAddParameter("vin_glosa", $glosa);
        $this->commandAddParameter("vin_usuario_creacion", $usuarioId);
        return $this->commandGetData();
    }

    function obtenerDocumentoDetalledistribucionPagoxId($id) {
        $this->commandPrepare("sp_documento_detalle_obtener_distribucion_pagoxId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function editarDocumentoDetalleDistribucionPagos($documento_detalle_id, $fechaPago, $importePago, $dias, $porcentaje, $glosa, $usuarioId, $id = null) {
        $this->commandPrepare("sp_documento_detalle_distribucion_pago_editar");
        $this->commandAddParameter("vin_documento_detalle_id", $documento_detalle_id);
        $this->commandAddParameter("vin_fecha_pago", $fechaPago);
        $this->commandAddParameter("vin_importe_pago", $importePago);
        $this->commandAddParameter("vin_dias", $dias);
        $this->commandAddParameter("vin_porcentaje", $porcentaje);
        $this->commandAddParameter("vin_glosa", $glosa);
        $this->commandAddParameter("vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter("vin_id", $id == ""?null:$id);
        return $this->commandGetData();
    }

    public function editar_documento_detalleEstado($documentoId) {
        $this->commandPrepare("sp_documento_detalleCambiarEstado");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    public function cambiarEstadoArchivoAdjunto($documentoId, $proveedorId){
        $this->commandPrepare("sp_documento_adjuntoCambiarEstadoXdocumnetoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_proveedor_id", $proveedorId);
        return $this->commandGetData();
    }

    public function editarDocumentoDetalleDistribucionPagosEstado($documento_detalle_id) {
        $this->commandPrepare("sp_documento_detalle_distribucion_pagoCambiarEstado");
        $this->commandAddParameter("vin_documento_detalle_id", $documento_detalle_id);
        return $this->commandGetData();
    }

    public function obtenerAreaConSolicitudes() {
        $this->commandPrepare("sp_obtenerAreaConSolicitudes");
        return $this->commandGetData();
    }

    function obtenerDocumentosRelacionadosXDocumentoIdSeguimiento($documentoId) {
        $this->commandPrepare("sp_documento_relacionado_obtenerXDocumentoIdSeguimiento");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerDocumentosRelacionadosXDocumentoIdXDt($documentoId, $documentoTipoId) {
        $this->commandPrepare("sp_documento_relacionado_obtenerXDocumentoIdXDt");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        return $this->commandGetData();
    }

    function obtenerDocumentoAdjuntoXDocumentoCotizacion($Id, $documentoId) {
        $this->commandPrepare("sp_documento_adjunto_obtenerXDocumentoCotizacion");
        $this->commandAddParameter(":vin_id", $Id);
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        return $this->commandGetData();
    }

    function obtenerUltimoAprobadorXDocumentoIdXDocumentoTipoId($documentoId, $documentoTipoId) {
        $this->commandPrepare("sp_obtener_ultimoAprobadorDocumentoIdXDocumentoTipoId");
        $this->commandAddParameter(":vin_documento_id", $documentoId);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        return $this->commandGetData();
    }
    
}
