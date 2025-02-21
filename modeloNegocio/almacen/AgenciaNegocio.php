<?php

require_once __DIR__ . '/../../modelo/almacen/Agencia.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/commons/TablaNegocio.php';

class AgenciaNegocio extends ModeloNegocioBase {

    /**
     * 
     * @return AgenciaNegocio
     */
    static function create() {
        return parent::create();
    }

    public function listarAgencia($empresaId) {
        return Agencia::create()->listarAgencia($empresaId);
    }

    public function listarAgenciaActiva($empresaId) {
        return Agencia::create()->listarAgenciaActiva($empresaId);
    }

    public function obterConfiguracionInicialForm($id) {
        $data->dataDivision = TablaNegocio::create()->obtenerXPadreId(TablaNegocio::PADRE_DIVISION_ID);
        $data->dataUbigeo = Persona::create()->obtenerUbigeoActivos();
        $data->dataModeloLocal = TablaNegocio::create()->obtenerXPadreId(TablaNegocio::PADRE_MODELO_LOCAL_ID);
        $data->dataUbicacionGeografica = TablaNegocio::create()->obtenerXPadreId(TablaNegocio::PADRE_UBICACION_GEOGRAFICA_ID);
        $data->dataAgencia = self::obtenerAgenciaXId($id);
        return $data;
    }

    public function obtenerAgenciaXId($id) {
        return Agencia::create()->obtenerAgenciaXId($id);
    }

    public function actualizarEstadoAgencia($id, $estado) {
        return Agencia::create()->actualizarEstadoAgencia($id, $estado);
    }

    public function guardarAgencia($agencia, $usuarioId) {
        $id = $agencia['id'];
        $empresaId = $agencia['empresa_id'];
        $codigo = $agencia['codigo'];
        $descripcion = $agencia['descripcion'];
        $estado = $agencia['estado'];
        $direccion = $agencia['direccion'];
        $divisionId = $agencia['division_id'];
        $modeloLocalId = $agencia['modelo_local_id'];
        $ubicacionGeograficaId = $agencia['ubicacion_geografica_id'];
        $ubigeoId = $agencia['ubigeo_id'];

        $respuestaGuardar = Agencia::create()->guardarAgencia($id, $empresaId, $codigo, $descripcion, $estado, $direccion, $divisionId, $modeloLocalId, $ubicacionGeograficaId, $ubigeoId, $usuarioId);



        return $respuestaGuardar;
    }

}
