<?php
require_once __DIR__ . '/../../modelo/almacen/Cuenta.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';

class CuentaNegocio extends ModeloNegocioBase {
    /**
     * 
     * @return CuentaNegocio
     */
    static function create() {
        return parent::create();
    }
    
    public function obtenerCuentasActivas(){
        return Cuenta::create()->obtenerCuentasActivas();
    }
    
    public function obtenerCuentaDefectoXEmpresaId($empresaId){
        return Cuenta::create()->obtenerCuentaDefectoXEmpresaId($empresaId);
    }
    
    public function obtenerCuentaXId($cuentaId){
        return Cuenta::create()->obtenerCuentaXId($cuentaId);
    }
    
    public function obtenerSaldoCuentaXId($cuentaId){
        return Cuenta::create()->obtenerSaldoCuentaXId($cuentaId);
    }
    
    public function obtenerCuentaSaldoTodos($empresaId){
        return Cuenta::create()->obtenerCuentaSaldoTodos($empresaId);
    }
}
