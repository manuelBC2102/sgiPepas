<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'reportesPDF.php';
include 'parametros.php';
//include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (isset($f_motivo)) $motivo = ucfirst(trim(filter_var($f_motivo, FILTER_SANITIZE_STRING))); else $motivo = NULL;

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

if ($est_id <> 6) {//antes 8
	echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud</b></font><br>";
	exit;
}

// Se asigna nuevo estado, num 4. Donde el colaborador registra su liquidacion
$est_id=4;
//if(false){//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];
$ahoramenosuno = date('Y-m-d H:i:s', strtotime($ahora." - 1 second")); 
$gastoAsumido=null;
$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=?, ear_act_fec=?, ear_act_usu=?, ear_liq_gast_asum=? WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('isiii',
	$est_id,
	$ahora,
	$_SESSION['rec_usu_id'],
        $gastoAsumido,
	$id);
$stmt->execute() or die ($mysqli->error);

$mensajeActualizacion='Retorno a registro de liquidaci&oacute;n por motivo: '.$motivo;
$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, ?, ?, ?, ?)") or die($mysqli->error);
$stmt->bind_param('iiiss', $id, $est_id, $_SESSION['rec_usu_id'], $ahora,$mensajeActualizacion);
$stmt->execute() or die($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Retorno de liquidacion por Analista de Cuentas (".$id.") hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

//------------------------CORREO-----------------------------------
$to = getCorreoUsuario($usu_id);
$cc = array();

$cc = obtenerCorreosPerfilSGI($pSUP_CONT, $cc);
$cc = obtenerCorreosPerfilSGI($pADMINIST, $cc);
if (!is_null($master_usu_id))
    array_push($cc, getCorreoUsuario($master_usu_id));

$correo = $to . ';';
$cc = array_unique($cc);
foreach ($cc as $index => $valor) {
    $correo = $correo . $valor . ';';
}
//------------------------------------------

$subject = "Observación en el visto bueno de contabilidad del EAR $ear_numero de " . $ear_tra_nombres;
$body = "Se produjo una observación del EAR $ear_numero de $ear_tra_nombres en el visto bueno por el 
    área contable, motivo por el cual se está retornando al proceso de liquidación.
    Observación: $motivo";
//R. enviarCorreo($to, $cc, $subject, $body);
//	ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);
//-------registro email en sgi-------
list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
$plantillaCuerpo = str_replace("[|asunto|]", 'EAR: Observación en el visto bueno de contabilidad', $plantillaCuerpo);
$plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

$emailEnvioId = insertarEmailEnvioSGI($correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);
//-----------------FIN CORREOS-----------------------------------------------------------

header("Location: ear_liq_act_vb.php");
exit;
?>
