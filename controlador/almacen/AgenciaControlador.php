<?php

require_once __DIR__ . '/../../modeloNegocio/almacen/AgenciaNegocio.php';
require_once __DIR__ . '/AlmacenIndexControlador.php';

class AgenciaControlador extends AlmacenIndexControlador {

    public function listarAgencia() {
        $empresaId = $this->getParametro("empresa_id");
        return AgenciaNegocio::create()->listarAgencia($empresaId);
    }

    public function obterConfiguracionInicialForm() {
        $id = $this->getParametro("id");
        return AgenciaNegocio::create()->obterConfiguracionInicialForm($id);
    }

    public function actualizarEstadoAgencia() {
        $id = $this->getParametro("id");
        $estado = $this->getParametro("estado");
        return AgenciaNegocio::create()->actualizarEstadoAgencia($id, $estado);
    }

    public function guardarAgencia() {
        $this->setTransaction();
        $agencia = $this->getParametro("agencia");
        $usuarioId = $this->getUsuarioId();
        return AgenciaNegocio::create()->guardarAgencia($agencia, $usuarioId);
    }

}
