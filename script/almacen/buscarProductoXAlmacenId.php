<?php

include_once __DIR__ . '/../../modeloNegocio/almacen/AlmacenesNegocio.php';
include_once __DIR__ . '/../../modeloNegocio/almacen/OrganizadorNegocio.php';

$buscar = $_GET['q'];
$almacenId = $_GET['almacenId'];

$buscar1=  str_replace(' ', '%',$buscar);

$buscarArray=explode(" ", $buscar);
$buscarArray2=array_reverse($buscarArray, true);

$buscar2=  implode('%', $buscarArray2);

$productos = [];
$productos = AlmacenesNegocio::create()->obtenerBienXTextoXOrganizadorIds($buscar1, $buscar2, $almacenId);

echo json_encode($productos);
