<?php
include_once __DIR__.'/../../modeloNegocio/almacen/SolicitudRetiroNegocio.php';
include_once __DIR__.'/../../util/EmailEnvioUtil.php';

SolicitudRetiroNegocio::create()->obtenerSolicitudRetiroXIDMTC();

//$email = new EmailEnvioUtil();
//$email->envio("niltoncleonl@hotmail.com", null, "Prueba Envio", "Cuerpo de correo prueba", null, null);
//$email->envio("klujan", null, "Prueba Envio", "Cuerpo de correo prueba");