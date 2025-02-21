<?php
header('Content-Type: text/html; charset=UTF-8');
include("seguridad.php");
include 'func.php';
include 'parametros.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_parametro1)) {
  echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
  exit;
} else {
  $parametro1 = filter_var($f_parametro1, FILTER_SANITIZE_STRING);
  $parametro2 = filter_var($f_parametro2, FILTER_SANITIZE_STRING);
}

$dataOrden = getOrdenTrabajo($parametro1, $parametro2);
echo json_encode($dataOrden, true);