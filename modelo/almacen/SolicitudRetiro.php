<?php

require_once __DIR__ . "/../core/ModeloBase.php";
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

/**
 * Description of Persona
 *
 * @author CHL
 */
class SolicitudRetiro extends ModeloBase {

    /**
     * 
     * @return SolicitudRetiro
     */
    static function create() {
        return parent::create();
    }

    public function getAllSolicitudes( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona,$planta,$zona,$vehiculo,$transportista) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerXCriterios");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_planta", $planta);
        $this->commandAddParameter(":vin_zona", $zona);
        $this->commandAddParameter(":vin_vehiculo", $vehiculo);
        $this->commandAddParameter(":vin_transportista", $transportista);

        return $this->commandGetData();
    }

    public function getAllSolicitudesAprobacion( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona,$persona_planta_id,$zona,$estado) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerXCriteriosAprobacion");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_persona_planta_id", $persona_planta_id);
        $this->commandAddParameter(":vin_zona", $zona);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    public function getCantidadAllSolicitudes( $columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,$planta,$zona,$vehiculo,$transportista) {
        $this->commandPrepare("sp_solicitud_retiro_contador_consulta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_planta", $planta);
        $this->commandAddParameter(":vin_zona", $zona);
        $this->commandAddParameter(":vin_vehiculo", $vehiculo);
        $this->commandAddParameter(":vin_transportista", $transportista);
        return $this->commandGetData();
    }

    public function getCantidadAllSolicitudesAprobacion( $columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,$persona_planta_id,$zona,$estado) {
        $this->commandPrepare("sp_solicitud_retiro_contador_consulta_aprobacion");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_persona_planta_id", $persona_planta_id);
        $this->commandAddParameter(":vin_zona", $zona);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    
    public function guardarSolicitud($fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId,$reinfo,$fechaLlegada=null,$lotes=null) {
        $this->commandPrepare("sp_solicitud_retiro_insert");
        $this->commandAddParameter(":vin_fecha", $fechaEntrega);
        $this->commandAddParameter(":vin_capacidad", $capacidad);
        $this->commandAddParameter(":vin_constancia", $constancia);
        $this->commandAddParameter(":vin_transportista", $transportista);
        $this->commandAddParameter(":vin_conductor", $conductor);
        $this->commandAddParameter(":vin_vehiculo", $vehiculo);
        $this->commandAddParameter(":vin_zona", $zona);
        $this->commandAddParameter(":vin_planta", $planta);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_reinfo", $reinfo);
        $this->commandAddParameter(":vin_fecha_llegada", $fechaLlegada);
        $this->commandAddParameter(":vin_lotes", $lotes);
        return $this->commandGetData();
    }

    public function guardarRequerimiento($solicitudId,$fechaEntrega,$transportista,$usuarioId,$reinfo,$fechaLlegada) {
        $this->commandPrepare("sp_requerimiento_insert");
        $this->commandAddParameter(":vin_fecha", $fechaEntrega);
        $this->commandAddParameter(":vin_solicitud_id", $solicitudId);
        $this->commandAddParameter(":vin_transportista", $transportista);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_reinfo", $reinfo);
        $this->commandAddParameter(":vin_fecha_llegada", $fechaLlegada);
        return $this->commandGetData();
    }
    public function actualizarSolicitud($id,$fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId,$reinfo,$lotes,$fechaLlegada) {
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
        $this->commandAddParameter(":vin_reinfo", $reinfo);
        $this->commandAddParameter(":vin_lotes", $lotes);
        $this->commandAddParameter(":vin_fecha_llegada", $fechaLlegada);
        
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
    

    public function buscarSolicitudXSociedadXVehiculo($busqueda, $usuarioId ){
        $this->commandPrepare("sp_solicitud_retiro_listar_buscarXSociedadXVehiculo");
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        return $this->commandGetData();

    }

    
    public function insertAprobacionSolicitud($idSolicitud,$documento_estado_id,$nivel){
        $this->commandPrepare("sp_solicitud_retiro_insertaraprobacion");
        $this->commandAddParameter(":vin_id", $idSolicitud);
        $this->commandAddParameter(":vin_estado", $documento_estado_id);
        $this->commandAddParameter(":vin_nivel", $nivel);
        return $this->commandGetData();

    }

    public function insertDesaprobacionSolicitud($idSolicitud,$documento_estado_id,$motivo){
        $this->commandPrepare("sp_solicitud_retiro_insertardesaprobacion");
        $this->commandAddParameter(":vin_id", $idSolicitud);
        $this->commandAddParameter(":vin_estado", $documento_estado_id);
        $this->commandAddParameter(":vin_motivo", $motivo);
        return $this->commandGetData();

    }

    public function listarSolicitudesDocumentario($usuarioId) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesPendientesDocumentos");
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        return $this->commandGetData();
    }
    
    public function listarSolicitudesPorAprobacionPesaje($usuarioId) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesPendientesPesajes");
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        return $this->commandGetData();
    }
    
    public function subirArchivo($id,$imageName,$tipo) {
        $this->commandPrepare("sp_solicitud_retiro_updateDocumentos");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_name", $imageName);
        $this->commandAddParameter(":vin_tipo", $tipo);
        return $this->commandGetData();
    }

    public function eliminarArchivo($id,$tipo) {
        $this->commandPrepare("sp_solicitud_retiro_delateDocumentos");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_tipo", $tipo);
        return $this->commandGetData();
    }
   
    public function obtenerLotesXSolicitudId($id) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesXId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    
    public function obtenerSolicitudesUsuario($personaId) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesXPersonaId");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }

    public function obtenerSolicitudesPesajesUsuario($personaId) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesXPersonaIdXPesaje");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }

    public function obtenerPesajeXSolicitud($solicitudId) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerDatosPesajeXSolicitudId");
        $this->commandAddParameter(":vin_id", $solicitudId);
        return $this->commandGetData();
    }

    public function registrarConformidadPesaje($solicitudId) {
        $this->commandPrepare("sp_solicitud_retiro_registrarconformidadPesaje");
        $this->commandAddParameter(":vin_persona_id", $solicitudId);
        return $this->commandGetData();
    }
    
    public function registrarrechazarPesaje($solicitudId) {
        $this->commandPrepare("sp_solicitud_retiro_registrarrechazoPesaje");
        $this->commandAddParameter(":vin_persona_id", $solicitudId);
        return $this->commandGetData();
    }
    

    

    public function obtenerSolicitudNivel($nivel,$id=null) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudXNivel");
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
   

    public function actualizarLoteResultados($idLote,$tmh,$porcentagua,$merma,$tms,$tipo_mineral,$ley,$unidad,$recuperacion,
    $precio_internacional,$descuento_internacional,$maquila,$penalidad,$flete,$total_mineral,$total_mineral_calculado,$imageName) {
        $this->commandPrepare("sp_solicitud_retiro_actualizarLoteResultados");
        $this->commandAddParameter(":vin_id", $idLote);
        $this->commandAddParameter(":vin_tmh", $tmh);
        $this->commandAddParameter(":vin_porcentagua", $porcentagua);
        $this->commandAddParameter(":vin_merma", $merma);
        $this->commandAddParameter(":vin_tms", $tms);
        $this->commandAddParameter(":vin_tipo_mineral", $tipo_mineral);
        $this->commandAddParameter(":vin_ley", $ley);
        $this->commandAddParameter(":vin_unidad", $unidad);
        $this->commandAddParameter(":vin_recuperacion", $recuperacion);
        $this->commandAddParameter(":vin_precio_internacional", $precio_internacional);
        $this->commandAddParameter(":vin_descuento_internacional", $descuento_internacional);
        $this->commandAddParameter(":vin_maquila", $maquila);
        $this->commandAddParameter(":vin_penalidad", $penalidad);
        $this->commandAddParameter(":vin_flete", $flete);
        $this->commandAddParameter(":vin_total_mineral", $total_mineral);
        $this->commandAddParameter(":vin_total_mineral_calculado", $total_mineral_calculado);
        $this->commandAddParameter(":vin_imageName", $imageName);

        return $this->commandGetData();
    }

    public function actualizarEntregaSolicitud($solicitud_id){
        $this->commandPrepare("sp_solicitud_retiro_actualizarEntregaSolicitud");
        $this->commandAddParameter(":vin_id", $solicitud_id);
        return $this->commandGetData();
    }
    
    public function obtenerSolicitudesPendienteResultados($personaId) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesXPersonaIdXResultados");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }
     

    public function obtenerResultadoXSolicitud($solicitudId) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerDatosResultadoXSolicitudId");
        $this->commandAddParameter(":vin_id", $solicitudId);
        return $this->commandGetData();
    }

    public function obtenerResultadoXSolicitudDemo($solicitudId) {
        $this->commandPrepare("sp_solicitud_retiro_obtenerDatosResultadoXSolicitudIdDemo");
        $this->commandAddParameter(":vin_id", $solicitudId);
        return $this->commandGetData();
    }

    public function actualizarDirimenciaLote($loteId,$estado) {
        $this->commandPrepare("sp_solicitud_retiro_actualizarLoteId");
        $this->commandAddParameter(":vin_id", $loteId);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    public function registrarDirimenciaLote($loteId,$ley_antigua,$ley,$pdfName,$usuarioId,$tipo=null) {
        $this->commandPrepare("sp_solicitud_retiro_registrarDirimenciaLote");
        $this->commandAddParameter(":vin_id", $loteId);
        $this->commandAddParameter(":vin_ley_antigua", $ley_antigua);
        $this->commandAddParameter(":vin_ley", $ley);
        $this->commandAddParameter(":vin_pdf", $pdfName);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_tipo", $tipo);
        return $this->commandGetData();
    }

    public function actualizarFacturacionLote($loteId,$comentarioEfact,$facturarDocumento){
        $this->commandPrepare("sp_solicitud_retiro_actualizarFacturacionLoteId");
        $this->commandAddParameter(":vin_id", $loteId);
        $this->commandAddParameter(":vin_comentario", $comentarioEfact);
        $this->commandAddParameter(":vin_factura", $facturarDocumento);

        return $this->commandGetData();
    }
    
    public function obtenerRequerimientoXSolicitudId($solicitudId){
        $this->commandPrepare("sp_requerimiento_obtenerXSolicitudId");
        $this->commandAddParameter(":vin_solicitud_id", $solicitudId);
       

        return $this->commandGetData();
    }

    public function obtenerHistorialEstadosXSolicitudId($solicitudId){
        $this->commandPrepare("sp_solicitud_retiro_obtenerEstadoXSolicitudId");
        $this->commandAddParameter(":vin_solicitud_id", $solicitudId);
       

        return $this->commandGetData();
    }

    public function guardarValidacionMTC($solicitudId,$usuarioId){
        $this->commandPrepare("sp_solicitud_retiro_registrarvalidacionMTC");
        $this->commandAddParameter(":vin_solicitud_id", $solicitudId);
        $this->commandAddParameter(":vin_usuario", $usuarioId);

        return $this->commandGetData();
    }

    public function obtenerSolicitudesPendienteValidacion(){
        $this->commandPrepare("sp_solicitud_retiro_obtenerSolicitudesMTC");


        return $this->commandGetData();
    }
    
    public function obtenerSolicitudXID($solicitudId){
        $this->commandPrepare("sp_solicitud_retiro_obtenerDatosSolicitudId");
        $this->commandAddParameter(":vin_solicitud_id", $solicitudId);

        return $this->commandGetData();
    }
    
    public function obtenerSolicitudFormatoXID($solicitudId){
        $this->commandPrepare("sp_solicitud_retiro_obtenerDatosSolicitudIdFormato");
        $this->commandAddParameter(":vin_solicitud_id", $solicitudId);

        return $this->commandGetData();
    }
    public function actualizarIntentosSolicitud($id,$mensaje,$intentoFuturo){
        $this->commandPrepare("sp_solicitud_retiro_actualizaIntentosMTC");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_mensaje", $mensaje);
        $this->commandAddParameter(":vin_intento", $intentoFuturo);
        return $this->commandGetData();
    }

    public function actualizarCapturaSoicitudRetiro($id,$captura,$tipo){
        $this->commandPrepare("sp_solicitud_retiro_actualizacapturasMTC");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_captura", $captura);
        $this->commandAddParameter(":vin_tipo", $tipo);
        return $this->commandGetData();
    }
    
    public function actualizarconstanciaCargaPlaca($id,$nro_constancia,$carga_util){
        $this->commandPrepare("sp_solicitud_retiro_actualizarconstanciaCargaPlaca");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_constancia", $nro_constancia);
        $this->commandAddParameter(":vin_carga", $carga_util);
        return $this->commandGetData();
    }

    public function actualizarMTCTransportista($ruc,$codigo){
        $this->commandPrepare("sp_solicitud_retiro_actualizarMTCTransportista");
        $this->commandAddParameter(":vin_ruc", $ruc);
        $this->commandAddParameter(":vin_codigo", $codigo);

        return $this->commandGetData();

    }

    
    public function obtenerActaSumatoriaPesaje($id) {
        $this->commandPrepare("sp_acta_retiro_ObtenerPesajePlantaActas");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function obtenerActaRetiroComparativo($id) {
        $this->commandPrepare("sp_acta_retiro_ObtenerActaComparativo");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function obtenerActaFormatoXID($actaId){
        $this->commandPrepare("sp_acta_obtenerDatosSolicitudIdFormato");
        $this->commandAddParameter(":vin_acta_id", $actaId);

        return $this->commandGetData();
    }

    public function guardarDetalleMineral($idLote,$tipo_mineral,$ley,$unidad,$recuperacion,
    $precio_internacional,$descuento_internacional,$maquila,$penalidad,$flete,$total_mineral,$total_mineral_calculado) {
        $this->commandPrepare("sp_solicitud_retiro_guardarDetalleMineral");
        $this->commandAddParameter(":vin_id", $idLote);
        $this->commandAddParameter(":vin_tipo_mineral", $tipo_mineral);
        $this->commandAddParameter(":vin_ley", $ley);
        $this->commandAddParameter(":vin_unidad", $unidad);
        $this->commandAddParameter(":vin_recuperacion", $recuperacion);
        $this->commandAddParameter(":vin_precio_internacional", $precio_internacional);
        $this->commandAddParameter(":vin_descuento_internacional", $descuento_internacional);
        $this->commandAddParameter(":vin_maquila", $maquila);
        $this->commandAddParameter(":vin_penalidad", $penalidad);
        $this->commandAddParameter(":vin_flete", $flete);
        $this->commandAddParameter(":vin_total_mineral", $total_mineral);
        $this->commandAddParameter(":vin_total_mineral_calculado", $total_mineral_calculado);

        return $this->commandGetData();
    }
}
