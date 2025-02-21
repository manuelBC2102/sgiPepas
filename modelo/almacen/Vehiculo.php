<?php

require_once __DIR__ . "/../core/ModeloBase.php";
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

/**
 * Description of Persona
 *
 * @author CHL
 */
class Vehiculo extends ModeloBase {

    /**
     * 
     * @return Vehiculo
     */
    static function create() {
        return parent::create();
    }
    
    public function getAllVehiculos() {
        $this->commandPrepare("sp_vehiculo_getAll");
        return $this->commandGetData();
    }

    public function getVehiculoXPlaca($placa) {
        $this->commandPrepare("sp_vehiculo_obtenerXPlaca");
        $this->commandAddParameter(":vin_placa", $placa);
        return $this->commandGetData();
    }
    public function guardarVehiculo($id,$placa,$capacidad,$nro_constancia=null,$imageName=null,$marca,$modelo,$tipo,$usuarioId,$personaId=null) {
        $this->commandPrepare("sp_vehiculo_insert_and_update");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_placa", $placa);
        $this->commandAddParameter(":vin_capacidad", $capacidad);
        $this->commandAddParameter(":vin_nro_constancia", $nro_constancia);
        $this->commandAddParameter(":vin_inputFile", $imageName);
        $this->commandAddParameter(":vin_marca", $marca);
        $this->commandAddParameter(":vin_modelo", $modelo);
        $this->commandAddParameter(":vin_tipo", $tipo);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        return $this->commandGetData();
    }


    public function listarvehiculosXId($id) {
        $this->commandPrepare("sp_vehiculo_getAllXId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    
 
    // public function actualizarVehiculo($id,$placa,$marca,$modelo,$capacidad,$tipo) {
    //     $this->commandPrepare("sp_vehiculo_update");
    //     $this->commandAddParameter(":vin_id", $id);
    //     $this->commandAddParameter(":vin_placa", $placa);
    //     $this->commandAddParameter(":vin_marca", $marca);
    //     $this->commandAddParameter(":vin_modelo", $modelo);
    //     $this->commandAddParameter(":vin_capacidad", $capacidad);
    //     $this->commandAddParameter(":vin_tipo", $tipo);
     
    //     return $this->commandGetData();
    // }
    public function actualizarEstadoVehiculo($id,$estado) {
        $this->commandPrepare("sp_vehiculo_updateEstado");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    public function actualizarVehiculoPlaca($placa,$persona,$constancia,$carga) {
        $this->commandPrepare("sp_vehiculo_actualizarVehiculoPlaca");
        $this->commandAddParameter(":vin_placa", $placa);
        $this->commandAddParameter(":vin_persona", $persona);
        $this->commandAddParameter(":vin_constancia", $constancia);
        $this->commandAddParameter(":vin_carga", $carga);
        return $this->commandGetData();
    }

    public function obtenerCarretas() {
        $this->commandPrepare("sp_vehiculo_obtenerCarretas");
        return $this->commandGetData();
    }
    
}
