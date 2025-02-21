<?php
require_once __DIR__ . '/../../modelo/exceptions/BaseException.php';
require_once __DIR__ . '/../../modeloNegocio/scm/AlertaNegocio.php';

class AlertaScript{
    public static function exec() {
        try{
            AlertaNegocio::create()->enviarAlertaLead();
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
}

AlertaScript::exec();