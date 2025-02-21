<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

$usu_id = $_SESSION['rec_usu_id'];

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

$arr = getDocPendInfo($id);
// Si no existe el documento pendiente se genera error
if (count($arr) == 0) {
	echo "<font color='red'><b>ERROR: No se encuentra el documento pendiente especificado.</b></font><br>";
	exit;
}
else {
	list($colab_id, $dp_numero, $cldp_ent_fec, $cldp_conc, $cldp_monto, $est_id, $cldp_com1, $cch_nombre, $cldp_reg_fec, $mon_nom, $mon_simb,
		$cldp_prdc_fec, $cldp_prdc_usu, $cldp_desc_fec, $cldp_desc_usu, $cldp_reem_fec, $cldp_reem_usu, $cch_id) = $arr;
}

// Validaciones
if (!is_null($cldp_reem_fec)) {
	echo "<font color='red'><b>ERROR: El documento pendiente vencido seleccionado ya ha pasado a descuento</b></font><br>";
	exit;
}

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("UPDATE cajas_chicas_lote_docp SET cldp_reem_fec=?, cldp_reem_usu=? WHERE cldp_id=?") or die ($mysqli->error);
$stmt->bind_param('sii',
	$ahora,
	$usu_id,
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Reembolso del documento pendiente vencido (".$id.") ".$dp_numero." hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';


$to = getCorreoUsuario(getUsuTesoreria());
$cc = array();
array_push ($cc, getCorreoUsuario(getUsuAdmin()));
array_push ($cc, getCorreoUsuario(getUsuCompensaciones()));
array_push ($cc, getCorreoUsuario(getUsuSupCont()));
array_push ($cc, getCorreoUsuario(getUsuRegCont()));
array_push ($cc, getCorreoUsuario(getUsuAnaCont()));
array_push ($cc, getCorreoUsuario(getUsuController()));
$arr = getEncargadosCaja($cch_id);
foreach ($arr as $v) {
	array_push ($cc, getCorreoUsuario($v));
}
$subject = "Reembolso del documento pendiente vencido $dp_numero de ".getNombreTrabajador(getCodigoGeneral(getUsuAd($colab_id)));
$body = "Se ha realizado el reembolso del documento pendiente vencido $dp_numero de ".getNombreTrabajador(getCodigoGeneral(getUsuAd($colab_id)))." por el monto de $cldp_monto $mon_nom ";
$body .= "de la caja chica $cch_nombre.";
//enviarCorreo($to, $cc, $subject, $body);
ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);

header("Location: cch_docp_reem.php");
exit;
?>
