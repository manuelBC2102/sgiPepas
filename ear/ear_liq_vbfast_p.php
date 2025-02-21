<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'reportesPDF.php';
include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

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

if ($est_id <> 6) {
	echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud</b></font><br>";
	exit;
}

$oper = 'VistoBueno';
$est_id = 7;


include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=?, ear_act_fec=?, ear_act_usu=? WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('isii',
	$est_id,
	$ahora,
	$_SESSION['rec_usu_id'],
	$id);
$stmt->execute() or die ($mysqli->error);

if ($est_id == 7) {
	if (!is_null($pla_id)) {
		$stmt = $mysqli->prepare("UPDATE pla_mov SET est_id=5 WHERE pla_id=?") or die ($mysqli->error);
		$stmt->bind_param('i',
			$pla_id);
		$stmt->execute() or die ($mysqli->error);

		$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 31, ?, ?, 'VB Rapido')") or die ($mysqli->error);
		$stmt->bind_param('iis', $id, $_SESSION['rec_usu_id'], $ahora);
		$stmt->execute() or die ($mysqli->error);

		$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
		$desc = "Revision Planilla Movilidad VB fast (".$id.") hecho por ".$_SESSION['rec_usu_nombre'];
		$ip = $_SERVER['REMOTE_ADDR'];
		$host = gethostbyaddr($ip);
		$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);
	}

	$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 7, ?, ?, 'VB Rapido')") or die ($mysqli->error);
	$stmt->bind_param('iis', $id, $_SESSION['rec_usu_id'], $ahora);
	$stmt->execute() or die ($mysqli->error);

	// $stmt = $mysqli->prepare("UPDATE ear_solicitudes SET ear_act_usu=? WHERE ear_id=?") or die ($mysqli->error);
	// $stmt->bind_param('ii', $_SESSION['rec_usu_id'], $id);
	// $stmt->execute() or die ($mysqli->error);
}

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = $oper." liquidacion EAR VB fast (".$id.") de ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);
$stmt->execute() or die ($mysqli->error);

$stmt->close();

$mysqli->commit();

if ($est_id == 7) {
	$dif = count(getDiferenciasDetLiq($id, 5));
	if ($dif==0) $dif = count(getDiferenciasDetLiq($id, 6));

	$to = getCorreoUsuario($usu_id);
	$cc = array();
	list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
	array_push ($cc, getCorreoUsuario($usu_id_jefe));
	array_push ($cc, getCorreoUsuario($usu_id_gerente));
	array_push ($cc, getCorreoUsuario(getUsuController()));
	array_push ($cc, getCorreoUsuario(getUsuAdmin()));
	array_push ($cc, 'mngmt@Minapp.com.pe');
	if(!is_null($master_usu_id)) array_push ($cc, getCorreoUsuario($master_usu_id));
	$subject = "Visto Bueno Rapido de Liquidacion de EAR $ear_numero de ".$ear_tra_nombres;
	$body = "Se ha dado visto bueno rapido a la liquidacion del EAR $ear_numero de $ear_tra_nombres.";
	if ($dif!=0) {
		// Adjuntar pdf si es que se aprueba
		$attachString = getCartaEarLiq($id, 'S');
		if (is_null($attachString)) die("Error en la generación del archivo PDF, no se completó la transacción. (Cadena vacía)");
		$attachFilename = __DIR__ ."LGS_".str_replace("/", "_", $ear_numero).".pdf";

		$body .= "\n\nNota al colaborador: Se han detectado cambios en su liquidacion, revisar la liquidacion actualizada adjunta en este correo para su conformidad, NO ES necesario volver a imprimir.";
	}
	$body .= "\n\nEsperando el registro y control de contabilidad.";
	//R. enviarCorreo($to, $cc, $subject, $body, $attachString, $attachFilename);
	ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident,"info",$attachString,$attachFilename);

	$to = getCorreoUsuario(getUsuSupCont());
	$cc = null;
	$subject = "Visto Bueno Rapido de Liquidacion de EAR $ear_numero de ".$ear_tra_nombres;
	$body = "Se ha dado visto bueno rapido a la liquidacion del EAR $ear_numero de $ear_tra_nombres.";
	$body .= "\n\nFavor de ingresar al modulo Administracion de la web intranet, opcion Actualizar Estado y Descarga Excel de Liquidaciones y realizar los ajustes necesarios y actualizar el estado respectivo.";
	//R. enviarCorreo($to, $cc, $subject, $body);
	$ventana   = "admin/ear_contabilidad.php";
	$aprobador = getUsuAd(getUsuSupCont());
	$detalle   = ConstructorMail::armarDetalle($cc, $subject, $body);
	ConstructorMail::enviarCorreoInfoConLink($id, $aprobador, $ventana, $var_modulo_ident, $detalle);
}

include 'datos_cerrar_bd.php';

//header("Location: ear_liq_vistobueno_res.php?id=$id&o=$oper");
header("Location: ear_vistobueno.php");
exit;
?>
