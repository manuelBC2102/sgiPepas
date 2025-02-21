<?php

require_once __DIR__ . '/../../modelo/contabilidad/Tabla.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';

class TablaNegocio extends ModeloNegocioBase {
    
    const PADRE_DIVISION_ID = 74;
    const PADRE_MODELO_LOCAL_ID = 75;
    const PADRE_UBICACION_GEOGRAFICA_ID = 76;

    /**
     * 
     * @return TablaNegocio
     */
    static function create() {
        return parent::create();
    }

    public function obtenerXPadreId($padreId) {
        return Tabla::create()->obtenerXPadreId($padreId);        
    }
}