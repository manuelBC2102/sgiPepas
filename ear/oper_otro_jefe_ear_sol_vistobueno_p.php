<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'reportesPDF.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id) || !isset($f_opcion)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}

$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
$opcion = abs((int) filter_var($f_opcion, FILTER_SANITIZE_NUMBER_INT));
if (isset($f_motivo)) $motivo = ucfirst(trim(filter_var($f_motivo, FILTER_SANITIZE_STRING))); else $motivo = NULL;
$otro_jefe_usu_id = abs((int) filter_var($f_otro_jefe_usu_id, FILTER_SANITIZE_NUMBER_INT));

if ($opcion!=2 && $opcion!=3 && $opcion!=41) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
	$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
	$usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
	$ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
	$ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
	$master_usu_id) = getSolicitudInfo($id);

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=?, ear_act_usu=?, ear_act_fec=?, ear_act_motivo=?, ear_aprob_usu=?
	WHERE ear_id=? AND est_id=1") or die ($mysqli->error);
$stmt->bind_param('iissii', $opcion, $otro_jefe_usu_id, $ahora, $motivo, $otro_jefe_usu_id, $id);

$stmt->execute() or die ($mysqli->error);
if($mysqli->affected_rows<>1) die ('No se actualiz&oacute; el registro EAR');

$motivo2 = "Hecha por: ".getUsuarioNombre($_SESSION['rec_usu_id']).". ".$motivo;

$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, ?, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('iiiss', $id, $opcion, $otro_jefe_usu_id, $ahora, $motivo2);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Actualizacion estado solicitud EAR (".$id.") a valor ".$opcion;
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

$to = getCorreoUsuario($usu_id);
$cc = array();
list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
array_push ($cc, getCorreoUsuario($usu_id_jefe));
array_push ($cc, getCorreoUsuario($usu_id_gerente));
array_push ($cc, getCorreoUsuario(getUsuController()));
array_push ($cc, getCorreoUsuario(getUsuAdmin()));
array_push ($cc, 'mngmt@Minapp.com.pe');
array_push ($cc, getCorreoUsuario($otro_jefe_usu_id));
if(!is_null($master_usu_id)) array_push ($cc, getCorreoUsuario($master_usu_id));
if ($opcion == 2) {
	// Adjuntar pdf si es que se aprueba
	$attachString = getCartaEarSol($id, 'S');
	if (is_null($attachString)) die("Error en la generaci�n del archivo PDF, no se complet� la transacci�n. (Cadena vac�a)");
	$attachFilename = "EAR_".str_replace("/", "_", $ear_numero).".pdf";

	$subject = "Solicitud Aprobada de EAR $ear_numero de ".$ear_tra_nombres;
	$body = "Se ha aprobado la solicitud de EAR $ear_numero de $ear_tra_nombres por el monto de ".number_format($ear_monto, 2, '.', ','). " $mon_nom.";
	$body .= "\n\nEsta aprobacion fue realizada por ".getUsuarioNombre($_SESSION['rec_usu_id'])." en vez de ".getUsuarioNombre($usu_id_jefe).".";
	// $body .= "\n\nNota al colaborador: Imprimir, firmar y hacer entregar a la brevedad el documento PDF adjunto a Administracion, gracias.";
	$body .= "\n\nEsperando el desembolso de Tesoreria";

	enviarCorreo($to, $cc, $subject, $body, $attachString, $attachFilename);


	$to = getCorreoUsuario(getUsuTesoreria());
	$cc = null;
	$subject = "Desembolso pendiente de EAR $ear_numero de ".$ear_tra_nombres;
	$body = "Se ha aprobado la solicitud de EAR $ear_numero de $ear_tra_nombres por el monto de ".number_format($ear_monto, 2, '.', ','). " $mon_nom.";
	$body .= "\n\nEsta aprobacion fue realizada por ".getUsuarioNombre($_SESSION['rec_usu_id'])." en vez de ".getUsuarioNombre($usu_id_jefe).".";
	$body .= "\n\nSe ha adjuntado a este correo el PDF de la solicitud para su revision.";
	$body .= "\n\nFavor de ingresar al modulo Administracion de la web intranet, opcion Desembolsos y realizar el desembolso respectivo.";
	enviarCorreo($to, $cc, $subject, $body, $attachString, $attachFilename);

	$location = "oper_otro_jefe_ear_consulta.php?cons_id=2&est_id=1&otro_jefe_usu_id=$otro_jefe_usu_id";
}
else if ($opcion == 3) {
	$subject = "Solicitud Rechazada de EAR $ear_numero de ".$ear_tra_nombres;
	$body = "Se ha rechazado la solicitud de EAR $ear_numero de $ear_tra_nombres por el monto de ".number_format($ear_monto, 2, '.', ','). " $mon_nom.";
	$body .= "\n\nEste rechazo fue realizado por ".getUsuarioNombre($_SESSION['rec_usu_id'])." en vez de ".getUsuarioNombre($usu_id_jefe).".";
	$body .= "\n\nMotivo: $motivo";
	enviarCorreo($to, $cc, $subject, $body);

	$location = "oper_otro_jefe_ear_consulta.php?cons_id=2&est_id=1&otro_jefe_usu_id=$otro_jefe_usu_id";
}
else if ($opcion == 41) {
	$subject = "Solicitud Cancelada de EAR $ear_numero de ".$ear_tra_nombres;
	$body = "Se ha cancelado la solicitud de EAR $ear_numero de $ear_tra_nombres por el monto de ".number_format($ear_monto, 2, '.', ','). " $mon_nom.";
	$body .= "\n\nMotivo: $motivo";
	enviarCorreo($to, $cc, $subject, $body);

	$location = "ear_consulta.php?cons_id=1";
}

include 'datos_cerrar_bd.php';

header("Location: $location");
exit;
?>
