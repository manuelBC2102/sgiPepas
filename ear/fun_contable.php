<?php


header('Content-Type: text/html; charset=UTF-8');
include 'func.php';
include 'parametros.php';
require_once('../' . $carpetaSGI . '/modeloNegocio/almacen/EarNegocio.php');

$accion = $_GET["accion"];
//$respuesta = array();
switch ($accion) {
    case "CCXOP":
        $operacionTipoId = $_GET["operacionTipoId"];
        $respuesta = EarNegocio::create()->obtenerPlanContableXEmpresaIdXContOperacionTipoId($operacionTipoId);
        echo json_encode($respuesta);
        break;

    case "OPXDT":
        $documentoTipoId = $_GET["documentoTipoId"];
        $respuesta = EarNegocio::create()->obtenerDocumentoTipoContOperacionTipoXDocumentoTipoId($documentoTipoId);
        echo json_encode($respuesta);
        break;
}
?>
