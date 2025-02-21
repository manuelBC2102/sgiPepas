<?php

require_once __DIR__ . '/../core/ModeloBase.php';
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

class Agencia extends ModeloBase {

    /**
     * 
     * @return Agencia
     */
    static function create() {
        return parent::create();
    }

    public function listarAgencia($empresaId) {
        $this->commandPrepare("sp_agencia_obtenerXEmpresaId");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        return $this->commandGetData();
    }

    public function listarAgenciaActiva($empresaId) {
        $this->commandPrepare("sp_agencia_activa_obtenerXEmpresaId");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        return $this->commandGetData();
    }

    public function obtenerAgenciaXId($id) {
        $this->commandPrepare("sp_agencia_obtenerXId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function actualizarEstadoAgencia($id, $estado) {
        $this->commandPrepare("sp_agencia_cambiarEstadoXId");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    public function guardarAgencia($id, $empresaId, $codigo, $descripcion, $estado, $direccion, $divisionId, $modeloLocalId, $ubicacionGeograficaId, $ubigeoId, $usuarioId) {
        $this->commandPrepare("sp_agencia_guardar");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_division_id", $divisionId);
        $this->commandAddParameter(":vin_ubigeo_id", $ubigeoId);
        $this->commandAddParameter(":vin_modelo_local_id", $modeloLocalId);
        $this->commandAddParameter(":vin_ubicacion_geografica_id", $ubicacionGeograficaId);
        $this->commandAddParameter(":vin_codigo", $codigo);
        $this->commandAddParameter(":vin_descripcion", $descripcion);
        $this->commandAddParameter(":vin_direccion", $direccion);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioId);
        return $this->commandGetData();
    }

}
