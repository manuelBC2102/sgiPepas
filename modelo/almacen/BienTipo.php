<?php

require_once __DIR__ . '/../core/ModeloBase.php';

/**
 * Description of Login
 *
 * @author 
 */
class BienTipo extends ModeloBase {

    /**
     * 
     * @return BienTipo
     */
    static function create() {
        return parent::create();
    }

    public function obtener() {
        $this->commandPrepare("sp_bien_tipo_obtener");
        return $this->commandGetData();
    }
    
    public function obtenerXId($id) {
        $this->commandPrepare("sp_bien_tipo_obtenerXId");
        $this->commandAddParameter("vin_id", $id);
        return $this->commandGetData();
    }

    public function obtenerBienTipoPadre() {
        $this->commandPrepare("sp_bien_tipo_obtenerPadres");
        return $this->commandGetData();
    }
    
    public function obtenerBienTipoXBienTipoPadreId($bienTipoPadreId){
        $this->commandPrepare("sp_bien_tipo_obtenerXBienTipoPadreId");
        $this->commandAddParameter("vin_bien_tipo_padre_id", $bienTipoPadreId);
        return $this->commandGetData();        
    }

    public function obtenerBienTipoXRequermientoXArea() {
        $this->commandPrepare("sp_bien_tipo_obtenerXRequermientoXArea");
        return $this->commandGetData();
    }

    public function obtenerXIdPadre($id) {
        $this->commandPrepare("sp_bien_tipo_obtenerXIdPadre");
        $this->commandAddParameter("vin_id", $id);
        return $this->commandGetData();
    }

    public function obtenerBienTipoXTipo() {
        $this->commandPrepare("sp_bien_tipo_obtenerXtipo");
        return $this->commandGetData();
    }
}
