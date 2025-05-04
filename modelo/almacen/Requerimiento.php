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

    public function obtenerRequerimientosXCriterios($fechaEmisionInicio, $fechaEmisionFin, $documentoTipoId, $areaId, $requerimiento_tipo, $estadoId, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start)
    {
        $this->commandPrepare("sp_requerimiento_obtenerXCriterios");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_area_id", $areaId);
        $this->commandAddParameter(":vin_requerimiento_tipo", $requerimiento_tipo);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        return $this->commandGetData();
    }

    public function obtenerCantidadRequerimientosXCriterios($fechaEmisionInicio, $fechaEmisionFin, $documentoTipoId, $areaId, $requerimiento_tipo, $estadoId, $columnaOrdenar, $formaOrdenar)
    {
        $this->commandPrepare("sp_requerimiento_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_documento_tipo_id", $documentoTipoId);
        $this->commandAddParameter(":vin_area_id", $areaId);
        $this->commandAddParameter(":vin_requerimiento_tipo", $requerimiento_tipo);
        $this->commandAddParameter(":vin_estado_id", $estadoId);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        return $this->commandGetData();
    }
 
    //Seguimiento
    public function obtenerSeguimientoRequerimientoXCriterios($bienIds, $bienTipoIds, $fechaInicio, $fechaFin, $serie, $numero)
    {
        $this->commandPrepare("sp_seguimientoRequerimiento");
        $this->commandAddParameter(":vin_bien_ids", $bienIds);
        $this->commandAddParameter(":vin_bien_tipo_ids", $bienTipoIds);
        $this->commandAddParameter(":vin_fecha_inicio", $fechaInicio);
        $this->commandAddParameter(":vin_fecha_fin", $fechaFin);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        return $this->commandGetData();
    }
}
