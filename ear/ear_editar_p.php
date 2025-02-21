<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
	$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
	$usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
	$ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
	$ear_liq_gast_asum, $pla_id,$earActObs1,$earAprobUsu,$marterUsu,$usuIniciales,$personaId) = getSolicitudInfo($id);
list($dni, $nombres, $cargo_id, $fecha_ing,
	$cargo_desc, $area_id, $area_desc,
        $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador($personaId);

$totalviaticos = abs((float) filter_var($f_totalviaticos, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$fecha2 = filter_var($f_fecha2, FILTER_SANITIZE_STRING);
$motivo = trim(filter_var($f_motivo, FILTER_SANITIZE_STRING));
$ctacte = filter_var($f_cta_dolares, FILTER_SANITIZE_STRING);
$anio = date('Y');
$mes = date('m');
$obs = null;
if(isset($f_obs)) {
	$obs = trim(filter_var($f_obs, FILTER_SANITIZE_STRING));
	if(strlen($obs)==0) $obs = null;
}

$hosp_otros_id = getIdHospOtros($mon_id);

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET ear_monto=?, ear_liq_fec=?, ear_sol_motivo=?, ear_tra_dni=?, ear_tra_nombres=?,
		ear_tra_cargo=?, ear_tra_area=?, ear_tra_sucursal=?, ear_tra_cta=?,
		ear_act_obs1=?
	WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('dsssssssssi',
	$totalviaticos,
	$fecha2,
	$motivo,
	$dni,
	$nombres,
	$cargo_desc,
	$area_desc,
	$sucursal,
	$ctacte,
	$obs,
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("DELETE FROM ear_sol_detalle WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('i', $id);
$stmt->execute() or die ($mysqli->error);

$insertion_id = $id;

//Agrega detalle boletos de viaje
$bole_mont = abs((float) filter_var($f_bole_mont, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
if ($bole_mont>0) {
	$via_id = getViaId('01');

	$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_monto) VALUES (?, ?, ?)") or die ($mysqli->error);
	$stmt->bind_param('iid', $insertion_id, $via_id, $bole_mont);
	$stmt->execute() or die ($mysqli->error);
}

//Agrega detalle alimentacion
$alim_dias = abs((int) filter_var($f_alim_dias, FILTER_SANITIZE_NUMBER_INT));
if ($alim_dias>0) {
	$via_id = getViaId("02$zona_id", $mon_id);
//	$monto = getViaticosMonto('02', $mon_id, $zona_id);
        $monto = abs((float) filter_var($f_alim_montodia, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

	$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_dias, via_monto) VALUES (?, ?, ?, ?)") or die ($mysqli->error);
	$stmt->bind_param('iiid', $insertion_id, $via_id, $alim_dias, $monto);
	$stmt->execute() or die ($mysqli->error);
}

//Agrega detalle hospedaje
if (isset($f_hosp_ciud)) {
	foreach ($f_hosp_ciud as $k => $v) {
		if ($hosp_otros_id != $v) {
//			$monto = getViaticosMontoId($v);
                        $monto = abs((float) filter_var($f_precio_i[$k], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

			$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_dias, via_monto) VALUES (?, ?, ?, ?)") or die ($mysqli->error);
			$stmt->bind_param('iidd', $insertion_id, $v, $f_hosp_dias[$k], $monto);
			$stmt->execute() or die ($mysqli->error);
		}
		else {
			$monto = abs((float) filter_var($f_precio_i[$k], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
			$via_desc = strtoupper(trim(filter_var($f_hosp_otro[$k], FILTER_SANITIZE_STRING)));

			$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_dias, via_monto, via_desc) VALUES (?, ?, ?, ?, ?)") or die ($mysqli->error);
			$stmt->bind_param('iidds', $insertion_id, $v, $f_hosp_dias[$k], $monto, $via_desc);
			$stmt->execute() or die ($mysqli->error);
		}
	}
}

//Agrega detalle movilidad
$movi_dias = abs((int) filter_var($f_movi_dias, FILTER_SANITIZE_NUMBER_INT));
if ($movi_dias>0) {
	$via_id = getViaId("04$zona_id", $mon_id);
//	$monto = getViaticosMonto('04', $mon_id, $zona_id);
        $monto = abs((float) filter_var($f_movi_montodia, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));


	$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_dias, via_monto) VALUES (?, ?, ?, ?)") or die ($mysqli->error);
	$stmt->bind_param('iiid', $insertion_id, $via_id, $movi_dias, $monto);
	$stmt->execute() or die ($mysqli->error);
}

//Agrega detalle gastos de representacion
$gast_mont = abs((float) filter_var($f_gast_mont, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
if ($gast_mont>0) {
	$via_id = getViaId('05');

	$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_monto) VALUES (?, ?, ?)") or die ($mysqli->error);
	$stmt->bind_param('iid', $insertion_id, $via_id, $gast_mont);
	$stmt->execute() or die ($mysqli->error);
}

//Agrega detalle otros gastos
if (isset($f_otro_item)) {
	$via_id = getViaId('06');
	foreach ($f_otro_item as $k => $v) {
		$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_desc, via_monto) VALUES (?, ?, ?, ?)") or die ($mysqli->error);
		$via_desc = trim($v);
		$stmt->bind_param('iisd', $insertion_id, $via_id, $via_desc, $f_otro_mont[$k]);
		$stmt->execute() or die ($mysqli->error);
	}
}

$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 21, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('iiss', $insertion_id, $_SESSION['rec_usu_id'], $ahora,$obs);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Edicion solicitud EAR (".$insertion_id.") hecho por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

$_SESSION['ear_last_id']=$insertion_id;
header("Location: ear_editar_res.php?zona_id=$zona_id");
exit;
?>

