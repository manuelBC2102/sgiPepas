<?php

require_once __DIR__ . '/../../modeloNegocio/almacen/ZonaNegocio.php';
require_once __DIR__ . '/AlmacenIndexControlador.php';

class ZonaControlador extends AlmacenIndexControlador {

    // public function listarAgencia() {
    //     $empresaId = $this->getParametro("empresa_id");
    //     return AgenciaNegocio::create()->listarAgencia($empresaId);
    // }

    public function listarZona() {
 
        return ZonaNegocio::create()->listarZona();
    }


    public function guardarZona() {

        $this->setTransaction();
        $id = $this->getParametro("id");
        $nombre = $this->getParametro("nombre");
        $codigo = $this->getParametro("codigo");
        $estado = $this->getParametro("estado");
        $usuarioId = $this->getUsuarioId();
        return ZonaNegocio::create()->guardarZona($id,$nombre,$codigo, $estado, $usuarioId);
    }

    
    public function actualizarEstadoZona() {
        $id = $this->getParametro("id");
        $nombre = $this->getParametro("nombre");
        $codigo = $this->getParametro("codigo");
        $estado = $this->getParametro("estado");
        return ZonaNegocio::create()->actualizarEstadoZona($id ,$nombre,$codigo, $estado);
    }








    public function obterConfiguracionInicialForm() {
        $id = $this->getParametro("id");
        return ZonaNegocio::create()->obterConfiguracionInicialForm($id);
    }

    public function actualizarBotonEstadoZona() {
        $id = $this->getParametro("id");
        $estado = $this->getParametro("estado");
        return ZonaNegocio::create()->actualizarBotonEstadoZona($id, $estado);
    }



}
