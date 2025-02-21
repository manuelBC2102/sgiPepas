<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
	$oper = abs((int) filter_var($f_oper, FILTER_SANITIZE_NUMBER_INT));
}

$arr = getLoteCajaChicaInfo($id);
// Si no existe el lote de esa caja chica se genera error
if (count($arr) == 0) {
	echo "<font color='red'><b>ERROR: No se encuentra lote de caja chica.</b></font><br>";
	exit;
}
else {
	list($ccl_id, $cch_nombre, $ccl_numero, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ccl_monto_ini, $ccl_gti, $ccl_dg_json, $ccl_cta_bco,
		$ccl_ape_fec, $ape_usu_nombre, $ccl_cie_fec, $cie_usu_nombre,
		$ccl_aprob_fec, $aprob_usu_nombre, $ccl_act_fec, $act_usu_nombre,
		$ccl_monto_usado, $est_id, $est_nom, $suc_nombre,
		$ccl_ret, $ccl_ret_no, $ccl_det, $ccl_det_no, $ccl_gast_asum, $ccl_pend, $cch_id, $liq_mon_id,
		$ccl_ape_usu, $ccl_cie_usu, $ccl_aprob_usu, $ccl_act_usu,
		$ccl_cuadre, $ccl_banco, $ccl_aju) = $arr;
}

if ($est_id <> 5 && $ccl_aju==0) {
	echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud</b></font><br>";
	exit;
}
// if ($usu_id <> $_SESSION['rec_usu_id']) {
	// echo "<font color='red'><b>ERROR: No se puede acceder a la informaci&oacute;n de la liquidaci&oacute;n</b></font><br>";
	// exit;
// }

// Se asigna nuevo estado :
if ($oper==1) {
	$est_id = 6; // Reembolso
	$msj_mov = "Reembolso efectuado";
}
else if ($oper==2) {
	$est_id = 7; // Descuento
	$msj_mov = "Descuento efectuado";
}
else {
	echo "<font color='red'><b>ERROR: Valor de operacion invalido, se ha notificado esta operacion al administrador</b></font><br>";
	exit;
}

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("UPDATE cajas_chicas_lote SET est_id=?, ccl_act_fec=?, ccl_act_usu=? WHERE ccl_id=?") or die ($mysqli->error);
$stmt->bind_param('isii',
	$est_id,
	$ahora,
	$_SESSION['rec_usu_id'],
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_act VALUES (?, ?, ?, ?, null)") or die ($mysqli->error);
$stmt->bind_param('iiis', $id, $est_id, $_SESSION['rec_usu_id'], $ahora);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Liquidacion CCH $msj_mov (".$id.") hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';


if ($oper==1) {
	$to = getCorreoUsuario($ccl_cie_usu);
	$cc = array();
	array_push ($cc, getCorreoUsuario(getUsuTesoreria()));
	array_push ($cc, getCorreoUsuario(getUsuController()));
	array_push ($cc, getCorreoUsuario(getUsuAdmin()));
	$subject = $msj_mov." a la CCH $ccl_numero de ".$cch_nombre;
	$body = "Tesoreria ha efectuado el reembolso correspondiente al encargado $cie_usu_nombre de la CCH $ccl_numero de $cch_nombre por el monto de ".conComas($ccl_aju)." $mon_nom.";
	$body .= "\n\nEsta liquidacion de CCH ya no tiene operaciones pendientes. Ha llegado a su etapa final.";
	//R. enviarCorreo($to, $cc, $subject, $body);
	ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);

	header("Location: cch_lote_act_teso.php");
}
else {
	$to = getCorreoUsuario($ccl_cie_usu);
	$cc = array();
	array_push ($cc, getCorreoUsuario(getUsuCompensaciones()));
	array_push ($cc, getCorreoUsuario(getUsuController()));
	array_push ($cc, getCorreoUsuario(getUsuAdmin()));
	$subject = $msj_mov." a la CCH $ccl_numero de ".$cch_nombre;
	$body = "Compensaciones ha efectuado el descuento correspondiente al encargado $cie_usu_nombre de la CCH $ccl_numero de $cch_nombre por el monto de ".conComas($ccl_aju*-1)." $mon_nom.";
	$body .= "\n\nEsta liquidacion de CCH ya no tiene operaciones pendientes. Ha llegado a su etapa final.";
	//R. enviarCorreo($to, $cc, $subject, $body);
	ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);

	header("Location: cch_lote_act_comp.php");
}

exit;
?>
