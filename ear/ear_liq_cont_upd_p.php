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
}

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
	$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
	$usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
	$ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
	$ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
	$master_usu_id) = getSolicitudInfo($id);

if ($est_id <> 7) {
	echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud</b></font><br>";
	exit;
}
// if ($usu_id <> $_SESSION['rec_usu_id']) {
	// echo "<font color='red'><b>ERROR: No se puede acceder a la informaci&oacute;n de la liquidaci&oacute;n</b></font><br>";
	// exit;
// }

$est_id = 8; // Se asigna nuevo estado

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=?, ear_act_usu=? WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('iii',
	$est_id,
	$_SESSION['rec_usu_id'],
	$id);
$stmt->execute() or die ($mysqli->error);

if ($est_id == 8) {
$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, ?, ?, ?, null)") or die ($mysqli->error);
$stmt->bind_param('iiis', $id, $est_id, $_SESSION['rec_usu_id'], $ahora);
$stmt->execute() or die ($mysqli->error);
}

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Liquidacion EAR actualizada por Contabilidad (".$id.") hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';


$to = getCorreoUsuario(getUsuController());
$cc = array();
array_push ($cc, getCorreoUsuario(getUsuSupCont()));
$subject = "Actualizacion de Estado por Contabilidad de EAR $ear_numero de ".$ear_tra_nombres;
$body = "Se ha actualizado el estado por Contabilidad de la liquidacion del EAR $ear_numero de $ear_tra_nombres.";
$body .= "\n\nEl Analista de Cuentas debe ingresar al modulo Administracion de la web intranet, opcion Visto Bueno de Liquidaciones actualizadas por Contabilidad y realizar el visto bueno respectivo de las liquidaciones pendientes que se muestren.";
//enviarCorreo($to, $cc, $subject, $body);
ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);

/*
 * @version 1.0
 * @function   : enviarCorreoAprobacion
 * @parametros :
 * -------------------------
 * @id                : Id de la tabla a actualizar.
 * @aprobador         : Usuario AD del aprobador, necesario para el mail y la actualizaci�n.
 * @location          : Ruta + nombre del archivo php (omitiendo '.php') donde se realiza la aprobaci�n (no el proceso _p). Ejemplo "recursos/aprob_vacadel"
 * @detalle_operacion : Descripci�n de la operaci�n a realizar.
 * @operacion         : Nombre de la operacion a realizar. Ajustar seg�n el template a usar
 * @template          : (Opcional) Nombre del template a utilizar para armar el correo, por defecto "template_1"
 * @ventana           : (Opcional) Ruta + nombre del archivo donde se muestra la ventana de aprobacion, por defecto la misma que @location
 */
$id                = $id;
$aprobador         = getUsuAd(getUsuAnaCont());
$location          = "admin/ear_liq_act_vb";
$detalle_operacion = "Se ha actualizado el estado por Contabilidad de la liquidacion del EAR $ear_numero de $ear_tra_nombres";
$operacion         = "visto bueno de liquidacion actualizada del EAR $ear_numero";
$detalle           = ConstructorMail::armarDetalle($cc, $subject, $body);
ConstructorMail::enviarCorreoAprobacion($id, $aprobador, $location, $detalle_operacion, $operacion, "template_5","",$detalle);


header("Location: ear_contabilidad.php");
exit;
?>
