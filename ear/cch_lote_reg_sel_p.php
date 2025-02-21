<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

$arr = getCajasChicasEncAcceso($_SESSION['rec_usu_id']);
$count = count($arr);
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}

$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));

header("Location: cch_lote_reg.php?id=$id");
exit;
?>
