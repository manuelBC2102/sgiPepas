<?php

require_once __DIR__ . "/../core/ModeloBase.php";

class OrdenCompraServicio extends ModeloBase
{
    /**
     *
     * @return OrdenCompraServicio
     */
    static function create()
    {
        return parent::create();
    }

    public function obtenerOrdenCompraServicioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start)
    {
        $this->commandPrepare("sp_ordenCompraServicio_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_tipo_id", $tipoId);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        return $this->commandGetData();
    }

    public function obtenerCantidadOrdenCompraServicioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $columnaOrdenar, $formaOrdenar)
    {
        $this->commandPrepare("sp_ordenCompraServicio_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_tipo_id", $tipoId);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        return $this->commandGetData();
    }


}
