
<?php

require_once __DIR__ . '/../../modeloNegocio/almacen/VehiculoNegocio.php';
require_once __DIR__ . '/AlmacenIndexControlador.php';

class VehiculoControlador extends AlmacenIndexControlador {
    public function listarVehiculo() {
 
        return VehiculoNegocio::create()->listarVehiculo();
    }
    public function guardarVehiculo() {

        $this -> setTransaction();
        $id = $this->getParametro("id");
        $placa = $this->getParametro("placa");
        $capacidad = $this->getParametro("capacidad");
        $nro_constancia = $this->getParametro("nro_constancia");
        $inputFile = $this->getParametro("inputFile");
        $marca = $this->getParametro("marca");
        $modelo = $this->getParametro("modelo");
        $tipo = $this->getParametro("tipo");
        $usuarioId = $this->getUsuarioId();
        return VehiculoNegocio::create()->guardarVehiculo($id,$placa,$capacidad,$nro_constancia,$inputFile,$marca,$modelo,$tipo,$usuarioId
        );

    }

    
    public function actualizarVehiculo() {
        $id = $this->getParametro("id");
        $placa = $this->getParametro("placa");
        $marca = $this->getParametro("marca");
        $modelo = $this->getParametro("modelo");
        $capacidad = $this->getParametro("capacidad");
        $tipo = $this->getParametro("tipo");
        
        return VehiculoNegocio::create()->actualizarVehiculo( $id,$placa,$marca,$modelo,$capacidad,$tipo);
    }
    public function actualizarEstadoVehiculo() {
        $id = $this->getParametro("id");
        $estado = $this->getParametro("estado");
        
        return VehiculoNegocio::create()->actualizarEstadoVehiculo( $id,$estado);
    }
    public function obterConfiguracionInicialForm() {
        $id = $this->getParametro("id");
        return VehiculoNegocio::create()->obterConfiguracionInicialForm($id);
    }

    // traer data del endpoint

    public function validarPlacaEndPoint() {
        $placa = $this->getParametro("placa");
        
        return VehiculoNegocio::create()->validarPlacaEndPoint($placa);
    }
}
