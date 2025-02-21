<?php

include_once __DIR__ . '/../../modelo/almacen/Persona.php';
include_once __DIR__ . '/../../modeloNegocio/almacen/MovimientoNegocio.php';
//include_once __DIR__.'/../../modeloNegocio/almacen/OperacionNegocio.php';
try {
  //FACTURAS
  //    $res=  MovimientoNegocio::create()->generarDocumentoElectronico(15713,4); // bien

  $res = MovimientoNegocio::create()->generarDocumentoElectronico(50558, 4, 1, 2); // bien
  //    $res = MovimientoNegocio::create()->generarDocumentoElectronico(35422, 5, 0, 1); // bien
  var_dump($res);

  //BOLETAS
  //$res=  MovimientoNegocio::create()->generarDocumentoElectronico(6732,3);
  //NOTA DE CREDITO
  //$res=  MovimientoNegocio::create()->generarDocumentoElectronico(14167,5);//Bien
  //NOTA DE DEBITO
  //$res=  MovimientoNegocio::create()->generarDocumentoElectronico(6739,25);
  //ANULAR FACTURA
  //$res=  MovimientoNegocio::create()->anularFacturaElectronica(6732); // BOLETA
  //FACTURA CON NEGOCIO DE OPERACION
  //$res= OperacionNegocio::create()->generaFacturaElectronica(6651);
} catch (Exception $ex) {
  echo $ex->getMessage();
}
