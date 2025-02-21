<?php
include ("seguridad.php");
include 'func.php';
include 'reportesPDF.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$id = "";
if (isset($f_id)) $id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
	$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
	$usu_act, $ear_act_fec, $ear_act_motivo, $liq_mon_id, $zona_id, $est_id, $usu_id,
	$ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
	$ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu) = getSolicitudInfo($id);

if ($est_id == 1 || $est_id == 3) {
	echo "<font color='red'><b>ERROR: No se puede generar el documento porque la solicitud no ha sido aprobada</b></font><br>";
	exit;
}

getCartaEarSol($id, 'I');
?>
