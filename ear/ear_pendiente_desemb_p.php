<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'parametros.php';
//include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}

$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
$opcion = 4;
$motivo = NULL;

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

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=?, ear_act_usu=?, ear_act_fec=?, ear_act_motivo=?, ear_dese_fec=?
	WHERE ear_id=? AND est_id=2") or die ($mysqli->error);
$stmt->bind_param('iisssi', $opcion, $_SESSION['rec_usu_id'], $ahora, $motivo, $ahora, $id);

$stmt->execute() or die ($mysqli->error);
if($stmt->affected_rows<>1) die ('No se actualiz&oacute; el registro EAR');

$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, ?, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('iiiss', $id, $opcion, $_SESSION['rec_usu_id'], $ahora, $motivo);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Actualizacion estado solicitud EAR (".$id.") a valor ".$opcion;
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

//-------------ENVIO DE CORREO------------------------
//obteniendo correos
$dataUsuarios = obtenerUsuariosIdXPerfil($pTESO);
$to = array();
$correoTo='';
foreach ($dataUsuarios as $index => $usuarioId) {
    $correoTeso=getCorreoUsuario($usuarioId);

    if(!is_null($correoTeso)){
        $correoTo=$correoTo.$correoTeso.';';
    }
}

$cc = array();
list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
array_push ($cc, getCorreoUsuario($usu_id_jefe));
if(!is_null($master_usu_id)) array_push ($cc, getCorreoUsuario($master_usu_id));

$dataUsuariosAdmin = obtenerUsuariosIdXPerfil($pADMINIST);
foreach ($dataUsuariosAdmin as $index => $usuarioId) {
    $correoAdmin=getCorreoUsuario($usuarioId);

    if(!is_null($correoAdmin) && $correoAdmin!=''){
        array_push ($cc, $correoAdmin);
    }
}

$correo=$correoTo;
$cc = array_unique($cc);
foreach ($cc as $index => $valor) {
    $correo=$correo.$valor.';';
}
//QUITAR CORREO DE USUARIO QUE USA EL SISTEMA
$correoUsuario = getCorreoUsuario($_SESSION['rec_usu_id']);
$correo=  str_replace($correoUsuario, '', $correo);

//--------------------------------------------
$subject = "Solicitud Desembolsada de EAR $ear_numero de ".$ear_tra_nombres;
$body = "Se ha desembolsado la solicitud de EAR $ear_numero de $ear_tra_nombres por el monto de ".number_format($ear_monto, 2, '.', ','). " $mon_nom.";
//enviarCorreo($to, $cc, $subject, $body);
//ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);
//-------registro email en sgi-------
list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
$plantillaCuerpo = str_replace("[|asunto|]", 'Solicitud Desembolsada de EAR', $plantillaCuerpo);
$plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

//insertar en email_envio de sgi
$emailEnvioId = insertarEmailEnvioSGI($correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);
//----------------------------------

$to = getCorreoUsuario($usu_id);
$cc = null;
$subject = "Solicitud Desembolsada de EAR $ear_numero de ".$ear_tra_nombres;
$body = "Se ha desembolsado la solicitud de EAR $ear_numero de $ear_tra_nombres por el monto de ".number_format($ear_monto, 2, '.', ','). " $mon_nom.";
$body .= "\n\nNo se olvide luego de ingresar al modulo de Entregas a Rendir de la web. Y realizar el registro respectivo de la liquidaci&oacute;n para continuar el tr&aacute;mite.";
//enviarCorreo($to, $cc, $subject, $body);
//-------registro email en sgi-------
list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
$plantillaCuerpo = str_replace("[|asunto|]", 'Solicitud Desembolsada de EAR', $plantillaCuerpo);
$plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

//insertar en email_envio de sgi
$emailEnvioId = insertarEmailEnvioSGI($to, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);
//----------------------------------

include 'datos_cerrar_bd.php';

header("Location: ear_pendiente_desemb.php");
exit;
?>

