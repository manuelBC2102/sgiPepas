<?php

require_once __DIR__ . "/../core/ModeloBase.php";
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

/**
 * Description of Persona
 *
 * @author CHL
 */
class Zona extends ModeloBase {

    /**
     * 
     * @return Zona
     */
    static function create() {
        return parent::create();
    }

    public function getAllZonas() {
        $this->commandPrepare("sp_zona_getAll");
        return $this->commandGetData();
    }

    public function listarZonasXId($id) {
        $this->commandPrepare("sp_zona_getAllXId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    public function guardarZona($id, $nombre,  $codigo, $estado,  $usuarioId) {
       
        $this->commandPrepare("sp_zona_insert");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_nombre", $nombre);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        return $this->commandGetData();
    }



    
    public function actualizarEstadoZona($id, $nombre,  $codigo, $estado) {
        $this->commandPrepare("sp_zona_update");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_nombre", $nombre);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    

    public function getComboZona() {
        $this->commandPrepare("sp_zona_getCombo");
        return $this->commandGetData();
    }


    public function actualizarBotonEstadoZona($id, $estado) {
        $this->commandPrepare("sp_zona_cambiarEstadoXId");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    public function getAllZonasReinfoInvitacion($id) {
        $this->commandPrepare("sp_zona_obtenerZonasReinfoXInvitacion");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    
}
