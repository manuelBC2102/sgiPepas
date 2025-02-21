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

if (!in_array($est_id, array(3, 4))) {
	echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud</b></font><br>";
	exit;
}

$est_id = 5; // Se asigna nuevo estado

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
	$usu_id,
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_act VALUES (?, 5, ?, ?, null)") or die ($mysqli->error);
$stmt->bind_param('iis', $id, $usu_id, $ahora);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Liquidacion CCH visto bueno por Contabilidad (".$id.") hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';


$to = getCorreoUsuario(getUsuAnaCont());
$cc = array();
$arr = getEncargadosCaja($cch_id);
foreach ($arr as $v) {
	array_push ($cc, getCorreoUsuario($v));
}
$arr = getResponsablesCaja($cch_id);
foreach ($arr as $v) {
	array_push ($cc, getCorreoUsuario($v));
}
array_push ($cc, getCorreoUsuario(getUsuController()));
array_push ($cc, getCorreoUsuario(getUsuSupCont()));
array_push ($cc, getCorreoUsuario(getUsuAdmin()));
if ($ccl_aju==0) {
	$msj_final = "\n\nEsta liquidacion de CCH ya no tiene operaciones pendientes. Ha llegado a su etapa final.";
}
else if ($ccl_aju>0) {
	//array_push ($cc, getCorreoUsuario(getUsuTesoreria()));
	$msj_final	  = "\n\nPor ajustes realizados a la liquidacion de Caja Chica se reembolsara al encargado el monto de ".conComas($ccl_aju)." $mon_nom.";
	$msj_especial = $msj_final."\n\nFavor de ingresar al modulo Administracion de la web intranet y actualizar el Reembolso pendiente de Caja Chica.";
	$msj_final 	 .= "\n\nEl usuario de Tesoreria debera ingresar al modulo Administracion de la web intranet y actualizar el Reembolso pendiente de Caja Chica.";
}
else if ($ccl_aju<0) {
	//array_push ($cc, getCorreoUsuario(getUsuCompensaciones()));
	$msj_final    = "\n\nPor ajustes realizados a la liquidacion de Caja Chica se descontara al encargado el monto de ".conComas($ccl_aju*-1)." $mon_nom.";
	$msj_especial = $msj_final."\n\nFavor de ingresar al modulo Administracion de la web intranet y actualizar el Descuento pendiente de Caja Chica.";
	$msj_final   .= "\n\nEl usuario de Compensaciones debera ingresar al modulo Administracion de la web intranet y actualizar el Descuento pendiente de Caja Chica.";
}
$subject = "Actualizacion de Estado por Contabilidad (VB) de CCH $ccl_numero de ".$cch_nombre;
$body = "Se ha actualizado el estado por Contabilidad (VB) de la liquidacion de CCH $ccl_numero de $cch_nombre.";
$bespcial = $body.$msj_especial;
$body .= $msj_final;
//enviarCorreo($to, $cc, $subject, $body);
ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);
if ($ccl_aju>0) {
	$ventana   = "admin/ear_liq_act_teso.php";
	$aprobador = getUsuAd(getUsuTesoreria());
	$detalle   = ConstructorMail::armarDetalle(null, $subject, $bespcial, $attachString, $attachFilename);
	ConstructorMail::enviarCorreoInfoConLink($id, $aprobador, $ventana, $var_modulo_ident, $detalle);
}
else if ($ccl_aju<0) {
	$ventana   = "admin/ear_liq_act_teso.php";
	$aprobador = getUsuAd(getUsuCompensaciones());
	$detalle   = ConstructorMail::armarDetalle(null, $subject, $bespcial, $attachString, $attachFilename);
	ConstructorMail::enviarCorreoInfoConLink($id, $aprobador, $ventana, $var_modulo_ident, $detalle);
}

header("Location: cch_contabilidad.php");
exit;
?>
