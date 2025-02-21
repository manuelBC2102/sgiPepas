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

$slave_usu_id = abs((int) filter_var($f_slave_usu_id, FILTER_SANITIZE_NUMBER_INT));

header("Location: movi_sol_uro.php?slave_usu_id=$slave_usu_id");
exit;
?>
