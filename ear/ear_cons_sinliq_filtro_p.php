<?php
include ("seguridad.php");

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

// $cons_id = abs((int) filter_var($f_cons_id, FILTER_SANITIZE_NUMBER_INT));
// $opc_id = abs((int) filter_var($f_opc_id, FILTER_SANITIZE_NUMBER_INT));
$zona = filter_var($f_zona, FILTER_SANITIZE_STRING);
$moneda = abs((int) filter_var($f_moneda, FILTER_SANITIZE_NUMBER_INT));
$estado = abs((int) filter_var($f_estado, FILTER_SANITIZE_NUMBER_INT));

$_SESSION['t_fecha2'] = $f_rfecha2;
$_SESSION['t_fecha4'] = $f_rfecha4;
$_SESSION['t_zona'] = $zona;
$_SESSION['t_moneda'] = $moneda;
$_SESSION['t_estado'] = $estado;

// header("Location: ear_cons_sinliq.php?cons_id=$cons_id&opc_id=$opc_id");
header("Location: ear_cons_sinliq.php");

exit;
?>
