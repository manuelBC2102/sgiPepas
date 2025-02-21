<?php

require_once __DIR__ . "/../core/ModeloBase.php";

class RegistroCompras extends ModeloBase {

    /**
     * 
     * @return RegistroCompras
     */
    static function create() {
        return parent::create();
    }  
    

    public function listarRegistroComprasXCriterios($empresaId, $personaId = null, $contLibroId = null, $periodoIdInicio = null, $periodoIdFin = null, $fechaInicio = null, $fechaFin = null) {
        $this->commandPrepare("sp_documento_obtenerRegistroComprasXCriterios");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_cont_libro_id", $contLibroId);
        $this->commandAddParameter(":vin_periodo_id_inicio", $periodoIdInicio);
        $this->commandAddParameter(":vin_periodo_id_fin", $periodoIdFin);
        $this->commandAddParameter(":vin_fecha_inicio", $fechaInicio);
        $this->commandAddParameter(":vin_fecha_fin", $fechaFin);
        return $this->commandGetData();
    }
    
    public function listaRegistroComprasXCriterios($empresaId, $personaId, $serie, $numero, $fechaInicio ,$fechaFin , $mostrarPagados = null) {
        $this->commandPrepare("sp_documento_obtenerRegistroComprasXCriteriosDetraccion");
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);        
        $this->commandAddParameter(":vin_fecha_inicio", $fechaInicio);
        $this->commandAddParameter(":vin_fecha_fin", $fechaFin);
        $this->commandAddParameter(":vin_mostrar_pagados", $mostrarPagados);
        return $this->commandGetData();
    }

}
