<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

$usu_id = $_SESSION['rec_usu_id'];

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

// Valida que se haya enviado el parametro obligatoriamente
// Este id es el lote de la caja chica
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
		$ccl_cuadre, $ccl_banco, $ccl_aju, $ccl_desemb) = $arr;
}

// Valida el acceso
$count = getPermisosAdministrativos($usu_id, 'TESO');
$count += getPermisosAdministrativos($usu_id, 'TI');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

// Valida el estado
if (!in_array($est_id, array(3, 5, 6, 7))) {
	echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud</b></font><br>";
	exit;
}

$oper = 'Desembolsar';
$est_id = 4;

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

// CAMBIO: El desembolso ya no altera el estado del lote, ahora modifica el valor de su propia columna ccl_desemb_est
$stmt = $mysqli->prepare("UPDATE cajas_chicas_lote SET ccl_desemb_est=1, ccl_desemb_fec=? WHERE ccl_id=?") or die ($mysqli->error);
$stmt->bind_param('si',
	$ahora,
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_act VALUES (?, ?, ?, ?, null)") or die ($mysqli->error);
$stmt->bind_param('iiis', $id, $est_id, $usu_id, $ahora);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = $oper." lote Caja Chica (".$id.") por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $usu_id, $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();


if ($ccl_monto_ini < $ccl_desemb) {
	$des_msj = "\n\nDesembolso a Caja Chica: $ccl_monto_ini $mon_nom";
	$des_msj .= "\nDesembolso al Encargado: ".number_format($ccl_desemb-$ccl_monto_ini, 2, '.', '')." $mon_nom";
}
else {
	$des_msj = "\n\nDesembolso a Caja Chica: $ccl_desemb $mon_nom";
}

$to = getCorreoUsuario($usu_id);
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
array_push ($cc, getCorreoUsuario(getUsuAdmin()));
array_push ($cc, 'mngmt@Minapp.com.pe');
$subject = "Lote Desembolsado de CAJA CHICA $ccl_numero";
$body = "Se ha desembolsado el lote de la CAJA CHICA $ccl_numero de $cch_nombre realizado por ".getNombreTrabajador(getCodigoGeneral(getUsuAd($usu_id))).".";
$body .= $des_msj;
$body .= "\n\nLa CAJA CHICA mencionada ya se encuentra habilitada para crear un nuevo lote y registrar documentos.";
//enviarCorreo($to, $cc, $subject, $body);
ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);

include 'datos_cerrar_bd.php';

header("Location: cch_lote_desemb.php");
exit;
?>
