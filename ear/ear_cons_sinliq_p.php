<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'reportesPDF.php';

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
	$ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
	$master_usu_id) = getSolicitudInfo($id);

if ($est_id <> 4) {
	echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud</b></font><br>";
	exit;
}


include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

if ($ear_monto == 0) {
	$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=53, ear_act_fec=?, ear_act_usu=?, ear_liq_mon=0, ear_liq_ret=0, ear_liq_ret_no=0, ear_liq_det=0, ear_liq_det_no=0, ear_liq_dcto=0 WHERE ear_id=?") or die ($mysqli->error);
	$stmt->bind_param('sii',
		$ahora,
		$_SESSION['rec_usu_id'],
		$id);
	$stmt->execute() or die ($mysqli->error);

	$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 53, ?, ?, 'Solicitud cerrada por exceso de dias sin liquidar')") or die ($mysqli->error);
	$stmt->bind_param('iis', $id, $_SESSION['rec_usu_id'], $ahora);
	$stmt->execute() or die ($mysqli->error);
}
else {
	$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=51, ear_act_fec=?, ear_act_usu=?, ear_liq_mon=0, ear_liq_ret=0, ear_liq_ret_no=0, ear_liq_det=0, ear_liq_det_no=0, ear_liq_dcto=? WHERE ear_id=?") or die ($mysqli->error);
	$stmt->bind_param('sidi',
		$ahora,
		$_SESSION['rec_usu_id'],
		$ear_monto,
		$id);
	$stmt->execute() or die ($mysqli->error);

	$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 51, ?, ?, 'Enviado a Descuento por exceso de dias sin liquidar')") or die ($mysqli->error);
	$stmt->bind_param('iis', $id, $_SESSION['rec_usu_id'], $ahora);
	$stmt->execute() or die ($mysqli->error);
}


$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Solicitud EAR enviada a descuento (".$id.") de ".$ear_tra_nombres. "por exceso de dias sin liquidar";
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);
$stmt->execute() or die ($mysqli->error);

$stmt->close();

$mysqli->commit();

if ($ear_monto == 0) {
	$to = getCorreoUsuario($usu_id);
	$cc = array();
	list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
	array_push ($cc, getCorreoUsuario($usu_id_jefe));
	array_push ($cc, getCorreoUsuario($usu_id_gerente));
	array_push ($cc, getCorreoUsuario(getUsuController()));
	array_push ($cc, getCorreoUsuario(getUsuAdmin()));
	array_push ($cc, 'mngmt@Minapp.com.pe');
	if(!is_null($master_usu_id)) array_push ($cc, getCorreoUsuario($master_usu_id));
	$subject = "EAR $ear_numero de ".$ear_tra_nombres." se ha cerrado por exceso de dias sin liquidar";
	$body = "Se ha cerrado por exceso de dias sin liquidar el EAR $ear_numero de $ear_tra_nombres.";

	enviarCorreo($to, $cc, $subject, $body);
}
else {
	$to = getCorreoUsuario($usu_id);
	$cc = array();
	list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
	array_push ($cc, getCorreoUsuario($usu_id_jefe));
	array_push ($cc, getCorreoUsuario($usu_id_gerente));
	array_push ($cc, getCorreoUsuario(getUsuController()));
	array_push ($cc, getCorreoUsuario(getUsuAdmin()));
	array_push ($cc, 'mngmt@Minapp.com.pe');
	if(!is_null($master_usu_id)) array_push ($cc, getCorreoUsuario($master_usu_id));
	$subject = "EAR $ear_numero de $ear_tra_nombres enviado a Descuento por exceso de dias sin liquidar";
	$body = "Se ha enviado a Descuento por exceso de dias sin liquidar el EAR $ear_numero de $ear_tra_nombres por el monto de $ear_monto $mon_nom.";

	// Adjuntar pdf si es que se aprueba
	$attachString = getCartaEarLiq($id, 'S');
	if (is_null($attachString)) die("Error en la generación del archivo PDF, no se completó la transacción. (Cadena vacía)");
	$attachFilename = "LGS_".str_replace("/", "_", $ear_numero).".pdf";

	$body .= "\n\nEsperando el descuento de Compensaciones.";
	enviarCorreo($to, $cc, $subject, $body, $attachString, $attachFilename);

	$to = getCorreoUsuario(getUsuSupCont());
	$cc = array();
	array_push ($cc, getCorreoUsuario(getUsuAnaCont()));
	enviarCorreo($to, $cc, $subject, $body, $attachString, $attachFilename);

	$to = getCorreoUsuario(getUsuCompensaciones());
	$cc = null;
	$subject = "EAR $ear_numero de $ear_tra_nombres enviado a Descuento por exceso de dias sin liquidar";
	$body = "Se ha enviado a Descuento por exceso de dias sin liquidar el EAR $ear_numero de $ear_tra_nombres por el monto de $ear_monto $mon_nom.";
	$body .= "\n\nFavor de ingresar al modulo Administracion de la web intranet, opcion Actualizar Liquidaciones con Descuentos pendientes y actualizar el estado respectivo.";
	enviarCorreo($to, $cc, $subject, $body);
}

include 'datos_cerrar_bd.php';

header("Location: ear_cons_sinliq.php");
exit;
?>
