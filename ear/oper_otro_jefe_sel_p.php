<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

$arr = getUsuRegOtroSlavesJefes($_SESSION['rec_usu_id']);
$count = count($arr);
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_otro_jefe_usu_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}

$otro_jefe_usu_id = abs((int) filter_var($f_otro_jefe_usu_id, FILTER_SANITIZE_NUMBER_INT));

header("Location: oper_otro_jefe_menu.php?otro_jefe_usu_id=$otro_jefe_usu_id");
exit;
?>
