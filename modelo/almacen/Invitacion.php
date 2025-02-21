<?php

require_once __DIR__ . '/../core/ModeloBase.php';
//require_once __DIR__.'../core/ModeloBase.php';
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

/**
 * Description of Login
 *
 * @author JSC7
 */
class Invitacion extends ModeloBase {
    
    /**
     * 
     * @return Invitacion
     */

    static function create() {
        return parent::create();
    }
    public function insertInvitacion( $personaId, $codigo, $sector, $ubicacion, $estado,$nivel, $usuarioId,$token,$expiracion,$zona,$organizacion) {
        $this->commandPrepare("sp_insert_invitacion");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_sector", $sector);   
        $this->commandAddParameter(":vin_ubicacion", $ubicacion);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_expiracion", $expiracion);
        $this->commandAddParameter(":vin_zona", $zona);
        $this->commandAddParameter(":vin_organizacion", $organizacion);
        return $this->commandGetData();
    }

    public function insertInvitacionTransportista(  $personaId, $codigo, $direccion, $modalidad, $estado,$nivel, $usuarioId,$token,$expiracion) {
        $this->commandPrepare("sp_insert_invitacion_transportista");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_direccion", $direccion);   
        $this->commandAddParameter(":vin_modalidad", $modalidad);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_expiracion", $expiracion);

        return $this->commandGetData();
    }

        public function insertInvitacionPlanta(  $personaId, $fecha, $direccion, $modalidad, $estado,$nivel, $usuarioId,$token,$expiracion) {
        $this->commandPrepare("sp_insert_invitacion_planta");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_fecha", $fecha);
        $this->commandAddParameter(":vin_direccion", $direccion);   
        $this->commandAddParameter(":vin_modalidad", $modalidad);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_expiracion", $expiracion);

        return $this->commandGetData();
    }

    public function insertInvitacionPrincipal(  $personaId, $direccion, $ubigeo, $estado,$nivel, $usuarioId,$token,$expiracion,$tipo) {
        $this->commandPrepare("sp_insert_invitacion_asociativa");
        $this->commandAddParameter(":vin_persona_id", $personaId);
      
        $this->commandAddParameter(":vin_direccion", $direccion);   
        $this->commandAddParameter(":vin_ubigeo", $ubigeo);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_expiracion", $expiracion);
        $this->commandAddParameter(":vin_tipo", $tipo);

        return $this->commandGetData();
    }


    public function insertInvitacionSecundario(  $personaId, $direccion, $ubigeo=null, $estado,$nivel, $usuarioId,$token=null,$expiracion=null,$tipo) {
        $this->commandPrepare("sp_insert_invitacion_asociativa_secundaria");
        $this->commandAddParameter(":vin_persona_id", $personaId);
      
        $this->commandAddParameter(":vin_direccion", $direccion);   
        $this->commandAddParameter(":vin_ubigeo", $ubigeo);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_expiracion", $expiracion);
        $this->commandAddParameter(":vin_tipo", $tipo);

        return $this->commandGetData();
    }
    public function actualizarInvitacion( $personaId, $codigo, $sector, $ubicacion, $estado,$nivel, $usuarioId,$token,$expiracion,$invitacionId,$zona,$organizacion) {
        $this->commandPrepare("sp_actualizar_invitacion");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_sector", $sector);   
        $this->commandAddParameter(":vin_ubicacion", $ubicacion);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_expiracion", $expiracion);
        $this->commandAddParameter(":vin_invitacion_id", $invitacionId);
        $this->commandAddParameter(":vin_zona", $zona);
        $this->commandAddParameter(":vin_organizacion", $organizacion);
        return $this->commandGetData();
    }


    public function actualizarInvitacionTransportista( $personaId, $codigo, $direccion, $modalidad, $estado,$nivel, $usuarioId,$token,$expiracion,$invitacionId) {
        $this->commandPrepare("sp_actualizar_invitacion_transportista");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_direccion", $direccion);   
        $this->commandAddParameter(":vin_modalidad", $modalidad);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_expiracion", $expiracion);
        $this->commandAddParameter(":vin_invitacion_id", $invitacionId);

        return $this->commandGetData();
    }

    public function actualizarInvitacionPlantaEmpresa( $personaId, $fecha, $direccion, $modalidad, $estado,$nivel, $usuarioId,$token,$expiracion,$invitacionId) {
        $this->commandPrepare("sp_actualizar_invitacion_planta");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_fecha", $fecha);
        $this->commandAddParameter(":vin_direccion", $direccion);   
        $this->commandAddParameter(":vin_modalidad", $modalidad);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_expiracion", $expiracion);
        $this->commandAddParameter(":vin_invitacion_id", $invitacionId);

        return $this->commandGetData();
    }

    public function actualizarInvitacionPrincipal( $personaId, $direccion, $ubigeo, $estado,$nivel, $usuarioId,$token,$expiracion,$invitacionId,$tipo) {
        $this->commandPrepare("sp_actualizar_invitacion_principal");
        $this->commandAddParameter(":vin_persona_id", $personaId);
      
        $this->commandAddParameter(":vin_direccion", $direccion);   
        $this->commandAddParameter(":vin_ubigeo", $ubigeo);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_expiracion", $expiracion);
        $this->commandAddParameter(":vin_invitacion_id", $invitacionId);
        $this->commandAddParameter(":vin_tipo", $tipo);

        return $this->commandGetData();
    }

    public function actualizarPersona( $personaId,$telefono, $correo) {
        $this->commandPrepare("sp_actualizar_invitacionPersona");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_telefono", $telefono);
        $this->commandAddParameter(":vin_correo", $correo);   

        return $this->commandGetData();
    }

    public function getAllInvitaciones( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona) {
        $this->commandPrepare("sp_invitacion_obtenerXCriterios");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);

        return $this->commandGetData();
    }

    public function getAllInvitacionesTransportista( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona) {
        $this->commandPrepare("sp_invitacion_obtenerXCriteriosTransportista");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);

        return $this->commandGetData();
    }

    public function getAllInvitacionesPlanta( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona) {
        $this->commandPrepare("sp_invitacion_obtenerXCriteriosPlanta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);

        return $this->commandGetData();
    }

    public function getAllInvitacionesPrincipal( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona,$tipo) {
        $this->commandPrepare("sp_invitacion_obtenerXCriteriosPrincipal");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_tipo", $tipo);

        return $this->commandGetData();
    }
    public function getCantidadAllActasRetiro( $columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona) {
        $this->commandPrepare("sp_invitacion_contador_consulta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);

        return $this->commandGetData();
    }

    public function getCantidadAllActasRetiroTransportista( $columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona) {
        $this->commandPrepare("sp_invitacion_contador_consulta_transportista");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);

        return $this->commandGetData();
    }

    public function getCantidadAllActasRetiroPlanta( $columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona) {
        $this->commandPrepare("sp_invitacion_contador_consulta_planta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);

        return $this->commandGetData();
    }

    public function getCantidadAllActasRetiroPrincipal( $columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,$tipo=null) {
        $this->commandPrepare("sp_invitacion_contador_consulta_principal");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $idPersona);
        $this->commandAddParameter(":vin_tipo", $tipo);

        return $this->commandGetData();
    }

    public function getInvitacionXId(  $invitacionId) {
        $this->commandPrepare("sp_invitacion_obtenerXId");
        $this->commandAddParameter(":vin_id", $invitacionId);

        return $this->commandGetData();
    }

    public function getInvitacionXIdTransportista(  $invitacionId) {
        $this->commandPrepare("sp_invitacion_obtenerXIdTransportista");
        $this->commandAddParameter(":vin_id", $invitacionId);

        return $this->commandGetData();
    }

    public function getInvitacionXIdPlanta(  $invitacionId) {
        $this->commandPrepare("sp_invitacion_obtenerXIdPlanta");
        $this->commandAddParameter(":vin_id", $invitacionId);

        return $this->commandGetData();
    }

    public function getInvitacionXIdPrincipal(  $invitacionId) {
        $this->commandPrepare("sp_invitacion_obtenerXIdPrincipal");
        $this->commandAddParameter(":vin_id", $invitacionId);

        return $this->commandGetData();
    }
  
  

    public function getInvitacionXToken(  $token) {
        $this->commandPrepare("sp_invitacion_obtenerXToken");
        $this->commandAddParameter(":vin_token", $token);

        return $this->commandGetData();
    }

    public function getInvitacionXTokenTransportista(  $token) {
        $this->commandPrepare("sp_invitacion_obtenerXTokenTransportista");
        $this->commandAddParameter(":vin_token", $token);

        return $this->commandGetData();
    }

        public function getInvitacionXTokenPlanta(  $token) {
        $this->commandPrepare("sp_invitacion_obtenerXTokenPlanta");
        $this->commandAddParameter(":vin_token", $token);

        return $this->commandGetData();
    }

    public function getInvitacionXTokenPrincipal(  $token) {
        $this->commandPrepare("sp_invitacion_obtenerXTokenPrincipal");
        $this->commandAddParameter(":vin_token", $token);

        return $this->commandGetData();
    }

    public function obtenerDocumentosPlantaXPersona( $persona,$planta) {
        $this->commandPrepare("sp_invitacion_obtenerDocumentosPlantaXPersona");
        $this->commandAddParameter(":vin_persona", $persona);
        $this->commandAddParameter(":vin_planta", $planta);

        return $this->commandGetData();
    }

    public function obtenerDocumentosAdministracion( $persona) {
        $this->commandPrepare("sp_invitacion_obtenerDocumentosAdministracion");
        $this->commandAddParameter(":vin_persona", $persona);

        return $this->commandGetData();
    }

    public function obtenerDocumentosAdministracionTransportista( $persona) {
        $this->commandPrepare("sp_invitacion_obtenerDocumentosAdministracionTransportista");
        $this->commandAddParameter(":vin_persona", $persona);

        return $this->commandGetData();
    }

    public function obtenerDocumentosAdministracionPlanta( $persona) {
        $this->commandPrepare("sp_invitacion_obtenerDocumentosAdministracionPlanta");
        $this->commandAddParameter(":vin_persona", $persona);

        return $this->commandGetData();
    }

    public function obtenerDocumentosAdministracionAsociativo( $persona) {
        $this->commandPrepare("sp_invitacion_obtenerDocumentosAdministracionAsociativo");
        $this->commandAddParameter(":vin_persona", $persona);

        return $this->commandGetData();
    }

    public function obtenerCoordenadas( $persona,$tipo) {
        $this->commandPrepare("sp_invitacion_obtenerCoordenadas");
        $this->commandAddParameter(":vin_persona", $persona);
        $this->commandAddParameter(":vin_tipo", $tipo);

        return $this->commandGetData();
    }

    public function obtenerCoordenadasVehiculos( $persona,$tipo) {
        $this->commandPrepare("sp_invitacion_obtenerCoordenadasVehiculos");
        $this->commandAddParameter(":vin_persona", $persona);
        $this->commandAddParameter(":vin_tipo", $tipo);

        return $this->commandGetData();
    }

    public function actualizarInvitacionNivel( $token,$nivel) {
        $this->commandPrepare("sp_invitacion_actualizarNivelInvitacion");
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_nivel", $nivel);

        return $this->commandGetData();
    }

    public function actualizarInvitacionNivelTransportista( $token,$nivel) {
        $this->commandPrepare("sp_invitacion_actualizarNivelInvitacionTransportista");
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_nivel", $nivel);

        return $this->commandGetData();
    }

    public function actualizarInvitacionNivelPlanta( $token,$nivel) {
        $this->commandPrepare("sp_invitacion_actualizarNivelInvitacionPlanta");
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_nivel", $nivel);

        return $this->commandGetData();
    }

    public function actualizarInvitacionNivelAsociativa( $token,$nivel) {
        $this->commandPrepare("sp_invitacion_actualizarNivelInvitacionAsociativa");
        $this->commandAddParameter(":vin_token", $token);
        $this->commandAddParameter(":vin_nivel", $nivel);

        return $this->commandGetData();
    }

    public function eliminarInvitacion( $id,$estado) {
        $this->commandPrepare("sp_invitacion_eliminarInvitacion");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);

        return $this->commandGetData();
    }
    public function eliminarInvitacionAsociativo( $id,$estado) {
        $this->commandPrepare("sp_invitacion_eliminarInvitacionAsociativa");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);

        return $this->commandGetData();
    }
    
    public function eliminarInvitacionTransportista( $id,$estado) {
        $this->commandPrepare("sp_invitacion_eliminarInvitacionTransportista");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);

        return $this->commandGetData();
    }

    public function eliminarInvitacionPlanta( $id,$estado) {
        $this->commandPrepare("sp_invitacion_eliminarInvitacionPlanta");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);

        return $this->commandGetData();
    }

    public function eliminarInvitacionPrincipal( $id,$estado) {
        $this->commandPrepare("sp_invitacion_eliminarInvitacionPrincipal");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);

        return $this->commandGetData();
    }

    public function obtenerInvitacionesNivel($nivel){
        $this->commandPrepare("sp_invitacion_obtenerInvitacionNivel");
        $this->commandAddParameter(":vin_nivel", $nivel);

        return $this->commandGetData();
    }

    public function obtenerInvitacionesNivelTransportista($nivel){
        $this->commandPrepare("sp_invitacion_obtenerInvitacionNivelTransportista");
        $this->commandAddParameter(":vin_nivel", $nivel);

        return $this->commandGetData();
    }

    public function obtenerInvitacionesNivelPlanta2($nivel){
        $this->commandPrepare("sp_invitacion_obtenerInvitacionNivelPlanta2");
        $this->commandAddParameter(":vin_nivel", $nivel);

        return $this->commandGetData();
    }

    public function obtenerInvitacionesNivelAsociativo($nivel){
        $this->commandPrepare("sp_invitacion_obtenerInvitacionNivelAsociativo");
        $this->commandAddParameter(":vin_nivel", $nivel);

        return $this->commandGetData();
    }

    
    public function obtenerInvitacionesNivelPlanta($nivel,$plantaId){
        $this->commandPrepare("sp_invitacion_obtenerInvitacionNivelPlanta");
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_planta", $plantaId);
        return $this->commandGetData();
    }

    public function obtenerInvitacionesNivelPlantaAsociativa($nivel,$plantaId){
        $this->commandPrepare("sp_invitacion_obtenerInvitacionNivelPlantaAsociativa");
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_planta", $plantaId);
        return $this->commandGetData();
    }

    public function finalizarAprobacion($usuarioId,$nivel,$invitacionId){
        $this->commandPrepare("sp_invitacion_actualizarNivelInvitacionXId");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        $this->commandAddParameter(":vin_nivel", $nivel);
        return $this->commandGetData();
    }
    public function finalizarAprobacionAsociativa($usuarioId,$nivel,$invitacionId){
        $this->commandPrepare("sp_invitacion_actualizarNivelInvitacionXIdAsociativa");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        $this->commandAddParameter(":vin_nivel", $nivel);
        return $this->commandGetData();
    }

    public function finalizarAprobacionTransportista($usuarioId,$nivel,$invitacionId){
        $this->commandPrepare("sp_invitacion_actualizarNivelInvitacionXIdT");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        $this->commandAddParameter(":vin_nivel", $nivel);
        return $this->commandGetData();
    }
    
    public function finalizarAprobacionPlanta($usuarioId,$nivel,$invitacionId){
        $this->commandPrepare("sp_invitacion_actualizarNivelInvitacionXIdP");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        $this->commandAddParameter(":vin_nivel", $nivel);
        return $this->commandGetData();
    }
    
   
    public function culminarAprobacion($invitacionId){
        $this->commandPrepare("sp_invitacion_culminarInvitacionXId");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        return $this->commandGetData();
    }
    public function culminarAprobacionTransportista($invitacionId){
        $this->commandPrepare("sp_invitacion_culminarInvitacionXIdP");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        return $this->commandGetData();
    }

    public function culminarAprobacionPlanta($invitacionId){
        $this->commandPrepare("sp_invitacion_culminarInvitacionPlantaXId");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        return $this->commandGetData();
    }

    public function culminarAprobacionAsociativa($invitacionId){
        $this->commandPrepare("sp_invitacion_culminarInvitacionXIdAsociativa");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        return $this->commandGetData();
    }
    

    public function actualizarInvitacionPlanta($invitacionId,$planta){
        $this->commandPrepare("sp_invitacion_actualizarInvitacionPlanta");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        $this->commandAddParameter(":vin_planta", $planta);
        return $this->commandGetData();
    }

    public function actualizarInvitacionPlantaRechazar($invitacionId,$planta){
        $this->commandPrepare("sp_invitacion_actualizarInvitacionPlantaRechazar");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        $this->commandAddParameter(":vin_planta", $planta);
        return $this->commandGetData();
    }

    public function registrarLogInvitacion( $invitacionId,$descripcion,$comentario,$usuarioId,$tipo) {
        $this->commandPrepare("sp_invitacion_registrarLogInvitacion");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        $this->commandAddParameter(":vin_descripcion", $descripcion);
        $this->commandAddParameter(":vin_comentario", $comentario);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        $this->commandAddParameter(":vin_tipo", $tipo);

        return $this->commandGetData();
    }

    public function solicitudactualizacionDatos($invitacionId,$nivel){
        $this->commandPrepare("sp_invitacion_solicitudActualizacionDatos");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        $this->commandAddParameter(":vin_nivel", $nivel);
        return $this->commandGetData();

    }

    public function solicitudactualizacionDatosAsociativo($invitacionId,$nivel){
        $this->commandPrepare("sp_invitacion_solicitudActualizacionDatosAsociativo");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        $this->commandAddParameter(":vin_nivel", $nivel);
        return $this->commandGetData();

    }
    

    public function solicitudactualizacionDatosTransportisa($invitacionId,$nivel){
        $this->commandPrepare("sp_invitacion_solicitudActualizacionDatosTransportista");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        $this->commandAddParameter(":vin_nivel", $nivel);
        return $this->commandGetData();

    }

    public function solicitudactualizacionDatosPlanta($invitacionId,$nivel){
        $this->commandPrepare("sp_invitacion_solicitudActualizacionDatosPlanta");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        $this->commandAddParameter(":vin_nivel", $nivel);
        return $this->commandGetData();

    }

    public function obtenerPostulacionPlantasXSolicitudId($invitacionId){
        $this->commandPrepare("sp_invitacion_obtenerPostulacionPlantasXSolicitudId");
        $this->commandAddParameter(":vin_invitacion", $invitacionId);
        return $this->commandGetData();

    }

    public function obtenerInvitacionesPlanta($usuarioId, $invitacionId){
        $this->commandPrepare("sp_invitacion_obtenerInvitacionesPlanta");
        $this->commandAddParameter(":vin_id", $invitacionId);
        return $this->commandGetData();
    }

    public function obtenerInvitacionesPlantaMatriz($usuarioId, $invitacionId){
        $this->commandPrepare("sp_invitacion_obtenerInvitacionesPlantaMatriz");
        $this->commandAddParameter(":vin_id", $invitacionId);
        $this->commandAddParameter(":vin_usuario", $usuarioId);
        return $this->commandGetData();
    }
    
    public function buscarCriteriosBusquedaSolicitud($busqueda, $usuarioId ){
        $this->commandPrepare("sp_invitacion_buscarCriteriosBusquedaSolicitud");
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        
        return $this->commandGetData();

    }

    public function buscarCriteriosBusquedaSolicitudTransportista($busqueda, $usuarioId ){
        $this->commandPrepare("sp_invitacion_buscarCriteriosBusquedaSolicitudTransportista");
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        
        return $this->commandGetData();

    }

    public function buscarCriteriosBusquedaSolicitudPrincipal($busqueda, $usuarioId ){
        $this->commandPrepare("sp_invitacion_buscarCriteriosBusquedaSolicitudPrincipal");
        $this->commandAddParameter(":vin_busqueda", $busqueda);
        
        return $this->commandGetData();

        
    }

    
    public function obtenerCuentasBancos( $usuarioId ){
        $this->commandPrepare("sp_invitacion_obtenerCuentasBancos");
        
        
        return $this->commandGetData();

        
    }

}
