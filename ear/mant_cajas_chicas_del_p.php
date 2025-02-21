<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

$arr = getCajasChicasInfo($id);
if (empty($arr)) {
	echo "<font color='red'><b>ERROR: Valor no existe</b></font><br>";
	exit;
}
list($cch_id, $cch_nombre, $suc_nombre, $mon_nom, $mon_iso, $mon_img, $cch_monto,
	$cch_abrv, $cch_gti, $cch_dg_json, $cch_cta_bco, $cch_act,
	$suc_id, $mon_id) = $arr;

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("DELETE FROM cajas_chicas WHERE cch_id=?") or die ($mysqli->error);
$stmt->bind_param('i',
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Eliminar caja chica ($id), abreviatura $cch_abrv, moneda $mon_nom, monto $cch_monto, cuenta $cch_cta_bco, activo $cch_act, hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_cajas_chicas.php");
exit;
?>
