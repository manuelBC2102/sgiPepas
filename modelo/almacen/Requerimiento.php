<?php

require_once __DIR__ . "/../core/ModeloBase.php";

class Requerimiento extends ModeloBase
{
    /**
     *
     * @return Requerimiento
     */
    static function create()
    {
        return parent::create();
    }

    public function obtenerRequerimientosXCriterios($fechaEmisionInicio, $fechaEmisionFin, $documentoTipoId, $areaId, $requerimiento_tipo, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start)
    {
        $this->commandPrepare("sp_requerimiento_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_area_id", $areaId);
        $this->commandAddParameter(":vin_requerimiento_tipo", $requerimiento_tipo);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        return $this->commandGetData();
    }

    public function obtenerCantidadRequerimientosXCriterios($fechaEmisionInicio, $fechaEmisionFin, $documentoTipoId, $areaId, $requerimiento_tipo, $columnaOrdenar, $formaOrdenar)
    {
        $this->commandPrepare("sp_requerimiento_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_area_id", $areaId);
        $this->commandAddParameter(":vin_requerimiento_tipo", $requerimiento_tipo);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        return $this->commandGetData();
    }
 
}
