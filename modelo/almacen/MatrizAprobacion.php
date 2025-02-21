<?php

require_once __DIR__ . '/../core/ModeloBase.php';
//require_once __DIR__.'../core/ModeloBase.php';
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

/**
 * Description of Login
 *
 * @author JSC7
 */
class MatrizAprobacion extends ModeloBase {
    
    /**
     * 
     * @return MatrizAprobacion
     */

    static function create() {
        return parent::create();
    }
    public function guardarAprobador( $documento,$usuario,$zona,$planta,$file ,$nivel,$usuarioId,$comentario) {
        $this->commandPrepare("sp_insert_aprobadorMatriz");
        $this->commandAddParameter(":vin_documento", $documento);
        $this->commandAddParameter(":vin_usuario", $usuario);
        $this->commandAddParameter(":vin_zona", $zona);   
        $this->commandAddParameter(":vin_planta", $planta);
        $this->commandAddParameter(":vin_file", $file);
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_comentario", $comentario);
        return $this->commandGetData();
    }

    public function actualizarAprobador( $documento,$usuario,$zona,$planta,$file ,$nivel,$usuarioId,$comentario,$id) {
        $this->commandPrepare("sp_update_aprobadorMatriz");
        $this->commandAddParameter(":vin_documento", $documento);
        $this->commandAddParameter(":vin_usuario", $usuario);
        $this->commandAddParameter(":vin_zona", $zona);   
        $this->commandAddParameter(":vin_planta", $planta);
        $this->commandAddParameter(":vin_file", $file);
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_comentario", $comentario);
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function actualizarInvitacion( $personaId, $codigo, $sector, $ubicacion, $estado,$nivel, $usuarioId,$token,$expiracion,$invitacionId) {
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
        return $this->commandGetData();
    }

    public function actualizarPersona( $personaId,$telefono, $correo) {
        $this->commandPrepare("sp_actualizar_invitacionPersona");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_telefono", $telefono);
        $this->commandAddParameter(":vin_correo", $correo);   

        return $this->commandGetData();
    }

    public function getAllMatriz( $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$documento,$usuarioAprobador,$planta,$zona) {
        $this->commandPrepare("sp_matriz_obtenerXCriterios");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elemntosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_documento", $documento);
        $this->commandAddParameter(":vin_usuario_aprobador", $usuarioAprobador);
        $this->commandAddParameter(":vin_planta", $planta);
        $this->commandAddParameter(":vin_zona", $zona);

        return $this->commandGetData();
    }

    public function getCantidadAllMatriz( $columnaOrdenar, $formaOrdenar, $usuarioId,$documento,$usuarioAprobador,$planta,$zona) {
        $this->commandPrepare("sp_matriz_contador_consulta");
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_documento", $documento);
        $this->commandAddParameter(":vin_usuario_aprobador", $usuarioAprobador);
        $this->commandAddParameter(":vin_planta", $planta);
        $this->commandAddParameter(":vin_zona", $zona);

        return $this->commandGetData();
    }

    public function getInvitacionXId(  $invitacionId) {
        $this->commandPrepare("sp_invitacion_obtenerXId");
        $this->commandAddParameter(":vin_id", $invitacionId);

        return $this->commandGetData();
    }
  

    public function getInvitacionXToken(  $token) {
        $this->commandPrepare("sp_invitacion_obtenerXToken");
        $this->commandAddParameter(":vin_token", $token);

        return $this->commandGetData();
    }

    public function obtenerDocumentosPlantaXPersona( $persona,$planta) {
        $this->commandPrepare("sp_invitacion_obtenerDocumentosPlantaXPersona");
        $this->commandAddParameter(":vin_persona", $persona);
        $this->commandAddParameter(":vin_planta", $planta);

        return $this->commandGetData();
    }

    public function actualizarInvitacionNivel( $token,$nivel) {
        $this->commandPrepare("sp_invitacion_actualizarNivelInvitacion");
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
    
    public function getMatrizXId( $id) {
        $this->commandPrepare("sp_matriz_obtenerMatrizXId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    
    public function getMatrizXUsuarioXDocumento( $usuarioId,$documento) {
        $this->commandPrepare("sp_matriz_obtenerMatrizXUsuarioXDocumento");
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_documento", $documento);
        return $this->commandGetData();
    }

    public function getMatrizXUsuarioXZona($nivel, $documento,$zona) {
        $this->commandPrepare("sp_matriz_obtenerMatrizXUsuarioXZona");
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_documento", $documento);
        $this->commandAddParameter(":vin_zona", $zona);
        return $this->commandGetData();
    }
    
    public function getMatrizXUsuarioXJunta($nivel, $documento) {
        $this->commandPrepare("sp_matriz_obtenerMatrizXUsuarioXJunta");
        $this->commandAddParameter(":vin_nivel", $nivel);
        $this->commandAddParameter(":vin_documento", $documento);
        return $this->commandGetData();
    }

    public function getMatrizXUsuarioXDocumentoPlantas( $usuarioId,$documento,$nivel) {
        $this->commandPrepare("sp_matriz_obtenerMatrizXUsuarioXDocumentoPlantas");
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_documento", $documento);
        $this->commandAddParameter(":vin_nivel", $nivel);
        return $this->commandGetData();
    }

    public function deleteElementoMatriz($id, $usuarioSesion, $estado){
        $this->commandPrepare("sp_matriz_deleteElementoMatriz");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);
      
        return $this->commandGetData();
    }

    public function obtenerConformidadInvitacionPlanta($planta, $invitacion){
        $this->commandPrepare("sp_matriz_obtenerConformidadInvitacionPlanta");
        $this->commandAddParameter(":vin_planta", $planta);
        $this->commandAddParameter(":vin_invitacion", $invitacion);
      
        return $this->commandGetData();
    }
    

}
