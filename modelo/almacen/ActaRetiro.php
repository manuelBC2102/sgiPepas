<?php

require_once __DIR__ . "/../core/ModeloBase.php";
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

/**
 * Description of Persona
 *
 * @author CHL
 */
class ActaRetiro extends ModeloBase {

    /**
     * 
     * @return ActaRetiro
     */
    static function create() {
        return parent::create();
    }

    public function getAllActasRetiro( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona,$zona_id=null,$fecha,$usuario,$vehiculo) {
        $this->commandPrepare("sp_acta_retiro_obtenerXCriterios");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_zona_id", $zona_id);
        $this->commandAddParameter(":vin_fecha", $fecha);
        $this->commandAddParameter(":vin_usuario", $usuario);
        $this->commandAddParameter(":vin_vehiculo", $vehiculo);

        return $this->commandGetData();
    }

    public function getAllRetenciones( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona,$zona_id=null,$fecha,$factura,$proveedor) {
        $this->commandPrepare("sp_retencion_obtenerXCriterios");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_zona_id", $zona_id);
        $this->commandAddParameter(":vin_fecha", $fecha);
        $this->commandAddParameter(":vin_factura", $factura);
        $this->commandAddParameter(":vin_proveedor", $proveedor);

        return $this->commandGetData();
    }

    public function getAllActasRetiroPlanta( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona,$planta_id,$fecha,$usuario,$vehiculo,$solicitud) {
        $this->commandPrepare("sp_acta_retiro_obtenerXCriteriosPlanta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_planta_id", $planta_id);
        
        $this->commandAddParameter(":vin_fecha", $fecha);
        $this->commandAddParameter(":vin_usuario", $usuario);
        $this->commandAddParameter(":vin_vehiculo", $vehiculo);
        $this->commandAddParameter(":vin_solicitud", $solicitud);
        return $this->commandGetData();
    }

    

   
    public function getCantidadAllActasRetiro( $columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,$zona_id=null,$fecha,$usuario,$vehiculo) {
        $this->commandPrepare("sp_acta_retiro_contador_consulta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_zona_id", $zona_id);
        $this->commandAddParameter(":vin_fecha", $fecha);
        $this->commandAddParameter(":vin_usuario", $usuario);
        $this->commandAddParameter(":vin_vehiculo", $vehiculo);
        return $this->commandGetData();
    }

    public function getCantidadAllRetenciones( $columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,$zona_id=null,$fecha,$factura,$proveedor) {
        $this->commandPrepare("sp_retencion_contador_consulta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_zona_id", $zona_id);
        $this->commandAddParameter(":vin_fecha", $fecha);
        $this->commandAddParameter(":vin_factura", $factura);
        $this->commandAddParameter(":vin_proveedor", $proveedor);
        return $this->commandGetData();
    }

    public function getCantidadAllActasRetiroPlanta( $columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,$planta_id,$fecha,$usuario,$vehiculo,$solicitud) {
        $this->commandPrepare("sp_acta_retiro_contador_consultaPlanta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_planta_id", $planta_id);

        $this->commandAddParameter(":vin_fecha", $fecha);
        $this->commandAddParameter(":vin_usuario", $usuario);
        $this->commandAddParameter(":vin_vehiculo", $vehiculo);
        $this->commandAddParameter(":vin_solicitud", $solicitud);

        return $this->commandGetData();
    }

    public function obtenerDataPlaca( $placa,$zona_id) {
        $this->commandPrepare("sp_acta_retiro_obtenerXPlaca");
        $this->commandAddParameter(":vin_placa", $placa);
        // $this->commandAddParameter(":vin_zona_id", $zona_id);

        return $this->commandGetData();
    }

    
  
    
    public function guardarActaRetiro($imageName,$vehiculoId,$comentario,$usuarioId,$zona_id=null,$pesaje,$facturarDocumento,
    $comentarioEfact, $pesajeInicial,$pesajeFinal,$fechaInicio,$fechaFinal,$carreta,$facturarDocumentoTransportista,
    $comentarioEfactTransportista,
    $facturadorSerie,$facturadorCorrelativo,$transportistaSerie,$transportistaCorrelativo) {
        $this->commandPrepare("sp_acta_retiro_insert");
        $this->commandAddParameter(":vin_archivo", $imageName);
        $this->commandAddParameter(":vin_vehiculo", $vehiculoId);
        $this->commandAddParameter(":vin_comentario", $comentario);
        $this->commandAddParameter(":vin_zona", $zona_id);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_pesaje", $pesaje);
        $this->commandAddParameter(":vin_factura_documento", $facturarDocumento);
        $this->commandAddParameter(":vin_comentario_efact", $comentarioEfact);

        $this->commandAddParameter(":vin_pesaje_inicial", $pesajeInicial);
        $this->commandAddParameter(":vin_pesaje_final", $pesajeFinal);
        $this->commandAddParameter(":vin_fecha_inicio", $fechaInicio);
        $this->commandAddParameter(":vin_fecha_final", $fechaFinal);
        $this->commandAddParameter(":vin_carreta", $carreta);
        $this->commandAddParameter(":vin_factura_documento_transportista", $facturarDocumentoTransportista);
        $this->commandAddParameter(":vin_comentario_efact_transportista", $comentarioEfactTransportista);
        $this->commandAddParameter(":vin_facturador_serie", $facturadorSerie);
        $this->commandAddParameter(":vin_facturador_correlativo", $facturadorCorrelativo);
        $this->commandAddParameter(":vin_transportista_serie", $transportistaSerie);
        $this->commandAddParameter(":vin_transportista_correlativo", $transportistaCorrelativo);
        return $this->commandGetData();
    }

    public function updateActaRetiro($imageName,$vehiculoId,$comentario,$usuarioId,$zona_id=null,$pesaje,$facturarDocumento,
    $comentarioEfact, $pesajeInicial,$pesajeFinal,$fechaInicio,$fechaFinal,$carreta,$facturarDocumentoTransportista,
    $comentarioEfactTransportista,
    $facturadorSerie,$facturadorCorrelativo,$transportistaSerie,$transportistaCorrelativo,$acta) {
        $this->commandPrepare("sp_acta_retiro_update");
        $this->commandAddParameter(":vin_archivo", $imageName);
        $this->commandAddParameter(":vin_vehiculo", $vehiculoId);
        $this->commandAddParameter(":vin_comentario", $comentario);
        $this->commandAddParameter(":vin_zona", $zona_id);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_pesaje", $pesaje);
        $this->commandAddParameter(":vin_factura_documento", $facturarDocumento);
        $this->commandAddParameter(":vin_comentario_efact", $comentarioEfact);

        $this->commandAddParameter(":vin_pesaje_inicial", $pesajeInicial);
        $this->commandAddParameter(":vin_pesaje_final", $pesajeFinal);
        $this->commandAddParameter(":vin_fecha_inicio", $fechaInicio);
        $this->commandAddParameter(":vin_fecha_final", $fechaFinal);
        $this->commandAddParameter(":vin_carreta", $carreta);
        $this->commandAddParameter(":vin_factura_documento_transportista", $facturarDocumentoTransportista);
        $this->commandAddParameter(":vin_comentario_efact_transportista", $comentarioEfactTransportista);
        $this->commandAddParameter(":vin_facturador_serie", $facturadorSerie);
        $this->commandAddParameter(":vin_facturador_correlativo", $facturadorCorrelativo);
        $this->commandAddParameter(":vin_transportista_serie", $transportistaSerie);
        $this->commandAddParameter(":vin_transportista_correlativo", $transportistaCorrelativo);
        $this->commandAddParameter(":vin_id", $acta);
        return $this->commandGetData();
    }

    public function guardarActaRetiroInicial($imageName,$vehiculoId,$usuarioId,$zona_id=null,$pesajeInicial,$fechaInicio,$carreta) {
        $this->commandPrepare("sp_acta_retiro_insert_inicial");
        $this->commandAddParameter(":vin_archivo", $imageName);
        $this->commandAddParameter(":vin_vehiculo", $vehiculoId);
        $this->commandAddParameter(":vin_zona", $zona_id);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_pesaje_inicial", $pesajeInicial);
        $this->commandAddParameter(":vin_fecha_inicio", $fechaInicio);
        $this->commandAddParameter(":vin_carreta", $carreta);

        return $this->commandGetData();
    }

    public function guardarTicket($imageName,$vehiculoId,$usuarioId,$pesaje,
    $pesajeInicial,$pesajeFinal,$fechaInicio,$fechaFinal,$carreta,$actaId) {
        $this->commandPrepare("sp_ticket_acta_retiro_insert");
        $this->commandAddParameter(":vin_archivo", $imageName);
        $this->commandAddParameter(":vin_vehiculo", $vehiculoId);
    
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_pesaje", $pesaje);
    

        $this->commandAddParameter(":vin_pesaje_inicial", $pesajeInicial);
        $this->commandAddParameter(":vin_pesaje_final", $pesajeFinal);
        $this->commandAddParameter(":vin_fecha_inicio", $fechaInicio);
        $this->commandAddParameter(":vin_fecha_final", $fechaFinal);
        $this->commandAddParameter(":vin_carreta", $carreta);
        $this->commandAddParameter(":vin_acta", $actaId);
        return $this->commandGetData();
    }

    public function guardarActaXSolicitudRetiro($actaId,$solicitudId,$usuarioId) {
        $this->commandPrepare("sp_acta_retiro_insertActaXSolicitud");
        $this->commandAddParameter(":vin_acta", $actaId);
        $this->commandAddParameter(":vin_solicitud", $solicitudId);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        return $this->commandGetData();
    }
    
    public function obtenerActaRetiroXId($id){
        $this->commandPrepare("sp_acta_retiro_obtenerActaXId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();

    }

    public function actualizarSolicitud($id,$fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId) {
        $this->commandPrepare("sp_solicitud_retiro_update");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_fecha", $fechaEntrega);
        $this->commandAddParameter(":vin_capacidad", $capacidad);
        $this->commandAddParameter(":vin_constancia", $constancia);
        $this->commandAddParameter(":vin_transportista", $transportista);
        $this->commandAddParameter(":vin_conductor", $conductor);
        $this->commandAddParameter(":vin_vehiculo", $vehiculo);
        $this->commandAddParameter(":vin_zona", $zona);
        $this->commandAddParameter(":vin_planta", $planta);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        return $this->commandGetData();
    }

    
    public function guardarEstadoSolicitud( $id, $estado,$usuarioId) {
        $this->commandPrepare("sp_solicitud_retiro_guardar_estado");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario", $usuarioId);

        return $this->commandGetData();
    }

    public function getSolicituRetiroXId( $id) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerXId");
        $this->commandAddParameter(":vin_id", $id);


        return $this->commandGetData();
    }
    
    public function cambiarEstadoSolicitud($id, $usuarioSesion, $estado) {
        $this->commandPrepare("sp_solicitud_retiro_eliminar");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_usuario", $usuarioSesion);
        $this->commandAddParameter(":vin_estado", $estado);

        return $this->commandGetData();
    }
    
    public function cambiarEstadoSolicitudDetalle($id) {
        $this->commandPrepare("sp_solicitud_retiro_detalle_eliminar");
        $this->commandAddParameter(":vin_id", $id);

        return $this->commandGetData();
    }

    public function eliminarActaRetiro($id) {
        $this->commandPrepare("sp_acta_retiro_eliminarXId");
        $this->commandAddParameter(":vin_id", $id);

        return $this->commandGetData();
    }
    
    

    public function buscarSolicitudXSociedadXVehiculo($busqueda, $usuarioId ){
        $this->commandPrepare("sp_solicitud_retiro_listar_buscarXSociedadXVehiculo");
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        return $this->commandGetData();

    }
   
    public function obtenerSolicitudesXActaId( $id) {
        $this->commandPrepare("sp_acta_retiro_obtenerSolicitudesXActaId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    
    public function actualizarEstadoSolicitud( $id,$estado) {
        $this->commandPrepare("sp_acta_retiro_actualizarSolicitudes");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }
    
    public function actualizarXUltimoEstadoSolicitud( $id) {
        $this->commandPrepare("sp_acta_retiro_obtenerSolicitudesXActaId2");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    
    
    public function obtenerDataActa( $acta) {
        $this->commandPrepare("sp_acta_retiro_obtenerSolicitudesXActa");
        $this->commandAddParameter(":vin_acta", $acta);

        return $this->commandGetData();
    }
  
    public function obtenerDataActaSolicitud( $solicitud) {
        $this->commandPrepare("sp_acta_retiro_obtenerSolicitudesXId");
        $this->commandAddParameter(":vin_id", $solicitud);

        return $this->commandGetData();
    }

    
    public function obtenerLotes( $id) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerLotesXSolicitud");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function guardarLotes( $usuarioId,$solicitudId,$ticket1,$ticket2,
    $peso_bruto,$peso_tara,$peso_neto,$nombre_lote,$imageName) {
        $this->commandPrepare("sp_solicitud_retiro_insertLotes");
        $this->commandAddParameter(":vin_id", $solicitudId);
        $this->commandAddParameter(":vin_ticket1", $ticket1);
        $this->commandAddParameter(":vin_ticket2", $ticket2);
        $this->commandAddParameter(":vin_peso_bruto", $peso_bruto);
        $this->commandAddParameter(":vin_peso_tara", $peso_tara);
        $this->commandAddParameter(":vin_peso_neto", $peso_neto);
        $this->commandAddParameter(":vin_nombre_lote", $nombre_lote);
        $this->commandAddParameter(":vin_file", $imageName);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        return $this->commandGetData();
    }

    public function eliminarLotes( $id) {
        $this->commandPrepare("sp_solicitud_retiro_eliminarLote");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function actualizarActaRetiroRecepcion( $id) {
        $this->commandPrepare("sp_acta_retiro_actualizaRecepcion");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    
    public function actualizarPesajeSolicitudRetiro( $id) {
        $this->commandPrepare("sp_acta_retiro_actualizarSolicitudId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    
    public function obtenerSolicitudesRetiroEntregaResultados( $planta_id) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesXResultados");
        $this->commandAddParameter(":vin_planta_id", $planta_id);
        return $this->commandGetData();
    }

    public function obtenerSolicitudesRetiroDirimencia( $planta_id) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesXDirimencia");
        $this->commandAddParameter(":vin_planta_id", $planta_id);
        return $this->commandGetData();
    }

    public function obtenerSolicitudesRetiroNegociar( $planta_id) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesXNegociar");
        $this->commandAddParameter(":vin_planta_id", $planta_id);
        return $this->commandGetData();
    }
    
    
    public function obtenerSolicitudesRetiroConfirmadosResultados( $planta_id) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesLotesConfirmados");
        $this->commandAddParameter(":vin_planta_id", $planta_id);
        return $this->commandGetData();
    }

    public function guardarResultadoFinalLoteDirimencia( $id,$usuarioId,$ley,$imageName) {
        $this->commandPrepare("sp_solicitud_retiro_registrarDirimenciaXloteFinal");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_ley", $ley);
        $this->commandAddParameter(":vin_file", $imageName);

        return $this->commandGetData();
    }
    
    public function actualizarLeyLoteDirimencia( $id,$ley,$total_calculado,$total) {
        $this->commandPrepare("sp_solicitud_retiro_actualizarLeyLoteDirimencia");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_ley", $ley);
        $this->commandAddParameter(":vin_total_calculado", $total_calculado);
        $this->commandAddParameter(":vin_total", $total);

        return $this->commandGetData();
    }
    public function actualizarLeyLoteNegociar( $id,$ley,$total_calculado,$total,$imageName) {
        $this->commandPrepare("sp_solicitud_retiro_actualizarLeyLoteNegociar");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_ley", $ley);
        $this->commandAddParameter(":vin_total_calculado", $total_calculado);
        $this->commandAddParameter(":vin_total", $total);
        $this->commandAddParameter(":vin_imagen", $imageName);
        return $this->commandGetData();
    }

    public function actualizarLeyLoteDirimenciaMineral( $id,$ley,$total_calculado,$total) {
        $this->commandPrepare("sp_solicitud_retiro_actualizarLeyLoteDirimenciaMineral");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_ley", $ley);
        $this->commandAddParameter(":vin_total_calculado", $total_calculado);
        $this->commandAddParameter(":vin_total", $total);

        return $this->commandGetData();
    }

    public function registrarValorizacion( $serie,$correlativo,$subtotal,$igv,$totalFactura,
    $detraccion,$netoPago,$usuarioId,$comentarioEfact,$facturarDocumento,$personaId,$codigo,$minero) {
        $this->commandPrepare("sp_valorizacion_registrarValorizacionPlanta");
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_correlativo", $correlativo);
        $this->commandAddParameter(":vin_subtotal", $subtotal);
        $this->commandAddParameter(":vin_igv", $igv);
        $this->commandAddParameter(":vin_totalFactura", $totalFactura);
        $this->commandAddParameter(":vin_detraccion", $detraccion);
        $this->commandAddParameter(":vin_netoPago", $netoPago);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_comentarioEfact", $comentarioEfact);
        $this->commandAddParameter(":vin_facturarDocumento", $facturarDocumento);
        $this->commandAddParameter(":vin_persona", $personaId);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_minero", $minero);
        return $this->commandGetData();
    }

    public function registrarFacturaProveedor( $facturadorSerie,$facturadorCorrelativo,$subtotal,$igv,$totalFactura,
    $detraccion,$netoPago,$usuarioId,$comentarioEfact,$facturarDocumento,$transportistaId,
    $solicitudId) {
        $this->commandPrepare("sp_proveedor_registrarFacturaPlanta");
        $this->commandAddParameter(":vin_serie", $facturadorSerie);
        $this->commandAddParameter(":vin_correlativo", $facturadorCorrelativo);
        $this->commandAddParameter(":vin_subtotal", $subtotal);
        $this->commandAddParameter(":vin_igv", $igv);
        $this->commandAddParameter(":vin_totalFactura", $totalFactura);
        $this->commandAddParameter(":vin_detraccion", $detraccion);
        $this->commandAddParameter(":vin_netoPago", $netoPago);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_comentarioEfact", $comentarioEfact);
        $this->commandAddParameter(":vin_facturarDocumento", $facturarDocumento);
        $this->commandAddParameter(":vin_persona", $transportistaId);
        $this->commandAddParameter(":vin_codigo", $solicitudId);
        return $this->commandGetData();
    }

    public function registrarValorizacionDetalle( $serie,$correlativo,$subtotal,$igv,$totalFactura,
    $detraccion,$netoPago,$usuarioId,$comentarioEfact,$facturarDocumento,$comentario,$valorizacionId) {
        $this->commandPrepare("sp_valorizacion_detalle_registrarValorizacionPlanta");
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_correlativo", $correlativo);
        $this->commandAddParameter(":vin_subtotal", $subtotal);
        $this->commandAddParameter(":vin_igv", $igv);
        $this->commandAddParameter(":vin_totalFactura", $totalFactura);
        $this->commandAddParameter(":vin_detraccion", $detraccion);
        $this->commandAddParameter(":vin_netoPago", $netoPago);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_comentarioEfact", $comentarioEfact);
        $this->commandAddParameter(":vin_facturarDocumento", $facturarDocumento);
        $this->commandAddParameter(":vin_comentario", $comentario);
        $this->commandAddParameter(":vin_valorizacion", $valorizacionId);
        return $this->commandGetData();
    }
    
    public function actualizarLotesValorizados( $valorizacion,$ote) {
        $this->commandPrepare("sp_solicitud_retiro_actualizarLotesValorizados");
        $this->commandAddParameter(":vin_valorizacion", $valorizacion);
        $this->commandAddParameter(":vin_lote", $ote);

        return $this->commandGetData();
    }
    

    public function actualizarCorrelativoFacturador( $facturadorId,$correlativoNuevo) {
        $this->commandPrepare("sp_persona_actualizarCorrelativoFacturador");
        $this->commandAddParameter(":vin_persona_id", $facturadorId);
        $this->commandAddParameter(":vin_correlativo", $correlativoNuevo);

        return $this->commandGetData();
    }

    public function actualizarCorrelativoRetenedor( $facturadorId,$correlativoNuevo) {
        $this->commandPrepare("sp_persona_actualizarCorrelativoRetencion");
        $this->commandAddParameter(":vin_persona_id", $facturadorId);
        $this->commandAddParameter(":vin_correlativo", $correlativoNuevo);

        return $this->commandGetData();
    }

    public function actualizarCorrelativoRemitente( $facturadorId,$correlativoNuevo) {
        $this->commandPrepare("sp_persona_actualizarCorrelativoRemitente");
        $this->commandAddParameter(":vin_persona_id", $facturadorId);
        $this->commandAddParameter(":vin_correlativo", $correlativoNuevo);

        return $this->commandGetData();
    }

    public function actualizarCorrelativoTransportista( $facturadorId,$correlativoNuevo) {
        $this->commandPrepare("sp_persona_actualizarCorrelativoTransportista");
        $this->commandAddParameter(":vin_persona_id", $facturadorId);
        $this->commandAddParameter(":vin_correlativo", $correlativoNuevo);

        return $this->commandGetData();
    }

    public function getAllValorizacion( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$planta_id,$fecha) {
        $this->commandPrepare("sp_valorizacion_obtenerValorizacionesXPlanta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $planta_id);
        $this->commandAddParameter(":vin_fecha", $fecha);


        return $this->commandGetData();
    }

    public function getCantidadAllValorizacion( $columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,$fecha) {
        $this->commandPrepare("sp_valorizacion_contador_obtenerValorizacionesXPlanta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_fecha", $fecha);

        return $this->commandGetData();
    }

    public function obtenerValorizacionesXPlantasXTipo( $planta_id,$tipo) {
        $this->commandPrepare("sp_valorizacion_obtenerXPlantasXTipo");
        $this->commandAddParameter(":vin_planta_id", $planta_id);
        $this->commandAddParameter(":vin_tipo", $tipo);
        return $this->commandGetData();
    }

    public function registrarPagoPlanta( $subtotal,$imageName,$plantaId,$minero,$numeroOperacion,$usuarioId,$tipo) {
        $this->commandPrepare("sp_pago_registrarPagoPlanta");
        $this->commandAddParameter(":vin_subtotal", $subtotal);
        $this->commandAddParameter(":vin_imageName", $imageName);
        $this->commandAddParameter(":vin_planta_id", $plantaId);
        $this->commandAddParameter(":vin_minero", $minero);
        $this->commandAddParameter(":vin_numeroOperacion", $numeroOperacion);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_tipo", $tipo);
        return $this->commandGetData();
    }
    
    public function actualizarValorizacionesPagadas( $valorizacionId, $lote,$tipo) {
        $this->commandPrepare("sp_pago_actualizarValorizacionesPagadas");
        $this->commandAddParameter(":vin_pago_id", $valorizacionId);
        $this->commandAddParameter(":vin_valorizacion_id", $lote);
        $this->commandAddParameter(":vin_tipo", $tipo);

        return $this->commandGetData();
    }

    public function getAllPagoPlanta( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$planta_id,$fecha,$planta) {
        $this->commandPrepare("sp_acta_retiro_obtenerXCriteriosPagoPlanta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_planta_id", $planta_id);
        $this->commandAddParameter(":vin_fecha", $fecha);
        $this->commandAddParameter(":vin_factura", $planta);
        return $this->commandGetData();
    }

    public function getCantidadAllPagoPlanta( $columnaOrdenar, $formaOrdenar, $usuarioId,$planta_id,$fecha,$factura) {
        $this->commandPrepare("sp_acta_retiro_contador_consultaPagoPlanta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_planta_id", $planta_id);
        $this->commandAddParameter(":vin_fecha", $fecha);
        $this->commandAddParameter(":vin_factura", $factura);

        return $this->commandGetData();
    }

    public function obtenerLotesXId($lote){
        $this->commandPrepare("sp_acta_retiro_obtenerLotesXId");
        $this->commandAddParameter(":vin_id", $lote);
        return $this->commandGetData();
    }

    public function registrarRetencion( $facturadorId,$proveedorId,$factura,$montoFactura
    ,$monedaId,$fechaFactura,$montoRetencion,$porcentajeRetencion,$tipoCambio2,$fechaPago,
    $estado,$usuarioId,$facturarDocumento,$comentarioEfact,$retencion) {
        $this->commandPrepare("sp_retencion_registrarRetencionContabilidad");
        $this->commandAddParameter(":vin_facturador", $facturadorId);
        $this->commandAddParameter(":vin_proveedor", $proveedorId);
        $this->commandAddParameter(":vin_factura", $factura);
        $this->commandAddParameter(":vin_monto_factura", $montoFactura);
        $this->commandAddParameter(":vin_moneda", $monedaId);
        $this->commandAddParameter(":vin_fecha_factura", $fechaFactura);
        $this->commandAddParameter(":vin_monto_retencion", $montoRetencion);
        $this->commandAddParameter(":vin_porcentaje_retencion", $porcentajeRetencion);
        $this->commandAddParameter(":vin_tipo_cambio", $tipoCambio2);
        $this->commandAddParameter(":vin_fecha_pago", $fechaPago);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_facturarDocumento", $facturarDocumento);
        $this->commandAddParameter(":vin_comentarioEfact", $comentarioEfact);
        $this->commandAddParameter(":vin_retencion", $retencion);
        return $this->commandGetData();
    }

    public function actualizarValorizacionEfact($comentarioEfactTransporte,$facturarDocumentoTransporte
    ,$comentarioEfactCarguio,$facturarDocumentoCarguio,$valorizacionId){
        $this->commandPrepare("sp_valorizacion_actualizarComprobantesEfact");
        $this->commandAddParameter(":vin_comentario_transporte", $comentarioEfactTransporte);
        $this->commandAddParameter(":vin_documento_transporte", $facturarDocumentoTransporte);
        $this->commandAddParameter(":vin_comentario_carguio", $comentarioEfactCarguio);
        $this->commandAddParameter(":vin_documento_carguio", $facturarDocumentoCarguio);
        $this->commandAddParameter(":vin_valorizacion", $valorizacionId);
        return $this->commandGetData();
    }

    public function obtenerPesajeTotalSolicitud($solicitudId){
        $this->commandPrepare("sp_solicitu_retiro_detalle_obtenerPesajeSolicitud");
        $this->commandAddParameter(":vin_id", $solicitudId);
        return $this->commandGetData();
    }

    public function getAllRetenciones2(){
        $this->commandPrepare("sp_retencion_obtenerAllRetenciones");
        return $this->commandGetData();
    }

    public function obtenerLoteSecundarios($lote){
        $this->commandPrepare("sp_suma_mineralLoteObtener23");
        $this->commandAddParameter(":vin_lote", $lote);
        return $this->commandGetData();
    }
        
}
