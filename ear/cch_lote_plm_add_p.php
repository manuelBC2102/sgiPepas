<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));

list($pla_numero, $est_id, $pla_reg_fec, $ear_numero, $tope_maximo, $usu_id, $ear_id,
	$est_nom, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec,
	$pla_exc, $pla_com1, $pla_com2, $pla_com3,
	$pla_tipo, $ccl_id, $cch_id) = getPlanillaMovilidadInfo($id);

// Valida si existe la caja chica
$arr = getCajasChicasInfo($cch_id);
if (empty($arr)) {
	echo "<font color='red'><b>ERROR: Valor no existe</b></font><br>";
	exit;
}
list($cch_id, $cch_nombre, $suc_nombre, $mon_nom, $mon_iso, $mon_img, $cch_monto,
	$cch_abrv, $cch_gti, $cch_dg_json, $cch_cta_bco, $cch_act,
	$suc_id, $mon_id) = $arr;

// Valida si la caja chica esta activa
if ($cch_act==0) {
	echo "<font color='red'><b>ERROR: Caja chica inactiva</b></font><br>";
	exit;
}

$arr = getUltLoteCajaChica($cch_id);
// Si no existen lotes en esa caja chica se muestra error
if (count($arr) == 0) {
	echo "<font color='red'><b>ERROR: Caja chica no cuenta con lotes</b></font><br>";
	exit;
}
else {
	if($arr[20]!=1) {
		echo "<font color='red'><b>ERROR: Caja chica esta cerrada, no se puede agregar la planilla.</b></font><br>";
		exit;
	}
}
$ccl_id = $arr[0];

if ($est_id!=4) {
	echo "<font color='red'><b>ERROR: No se puede modificar planilla, estado incorrecto</b></font><br>";
	exit;
}
$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += count(getCajasChicasEncAcceso($_SESSION['rec_usu_id']));
if ($count == 0) {
	echo "<font color='red'><b>ERROR: No puede modificar planilla de otros usuarios, se ha notificado esta operacion al administrador</b></font><br>";
	exit;
}

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now(), year(now())";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];
$anio = $fila[1];

$stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_act VALUES (?, 31, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('iiss', $ccl_id, $_SESSION['rec_usu_id'], $ahora, $pla_numero);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("UPDATE pla_mov SET est_id=15, ccl_id=? WHERE pla_id=?") or die ($mysqli->error);
$stmt->bind_param('ii', $ccl_id, $id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Agregar Planilla Movilidad $pla_tipo (".$id.") hecho por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: cch_lote_plm_add.php");
exit;
?>
