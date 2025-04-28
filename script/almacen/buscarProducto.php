<?php

include_once __DIR__ . '/../../modeloNegocio/almacen/BienNegocio.php';
include_once __DIR__ . '/../../modeloNegocio/almacen/OrganizadorNegocio.php';

$buscar = $_GET['q'];
$empresaId = $_GET['empresa'];
$movimiento_tipoId = $_GET['movimiento_tipo_id'];
$tipoRequerimiento = $_GET['tipoRequerimiento'];
$tipoRequerimientoText = $_GET['tipoRequerimientoText'];

$bien_tipo = 0;
if($tipoRequerimientoText == "Compra"){
    $bien_tipo = 2;
}else if($tipoRequerimientoText == "Servicio"){
    $bien_tipo = 1;
}

$buscar1=  str_replace(' ', '%',$buscar);

$buscarArray=explode(" ", $buscar);
$buscarArray2=array_reverse($buscarArray, true);

$buscar2=  implode('%', $buscarArray2);

$productos = [];
$productos = BienNegocio::create()->obtenerBienXTexto($buscar1, $buscar2, $empresaId, $movimiento_tipoId, $bien_tipo);

echo json_encode($productos);
