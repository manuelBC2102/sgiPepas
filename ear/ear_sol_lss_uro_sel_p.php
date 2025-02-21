<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

$arr = getUsuRegOtroSlaves($_SESSION['rec_usu_id']);
$count = count($arr);
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_zona_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}

$zona_id = filter_var($f_zona_id, FILTER_SANITIZE_STRING);
$mon_id = abs((int) filter_var($f_mon_id, FILTER_SANITIZE_NUMBER_INT));
$slave_usu_id = abs((int) filter_var($f_slave_usu_id, FILTER_SANITIZE_NUMBER_INT));

header("Location: ear_sol_lss_uro.php?zona_id=$zona_id&mon_id=$mon_id&slave_usu_id=$slave_usu_id");
exit;
?>
