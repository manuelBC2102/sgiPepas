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

if (!isset($f_cch)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$error = 0;

	$cch = abs((int) filter_var($f_cch, FILTER_SANITIZE_NUMBER_INT));
	$usu = abs((int) filter_var($f_usu, FILTER_SANITIZE_NUMBER_INT));
	$act = abs((int) filter_var($f_act, FILTER_SANITIZE_NUMBER_INT));

	if ($error == 1) exit;
}

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("SELECT COUNT(*)
	FROM cajas_chicas_enc
	WHERE cch_id=? AND usu_id=?");
$stmt->bind_param("ii", $cch, $usu);
$stmt->execute() or die ($mysqli->error);
$stmt->store_result();
$fila=fetchAssocStatement($stmt);
if ($fila[0] != 0) {
	echo "<font color='red'><b>ERROR: Ya existe la asignacion de usuario especificada.</b></font><br>";
	exit;
}

$stmt = $mysqli->prepare("INSERT INTO cajas_chicas_enc VALUES (null, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('iii',
	$cch,
	$usu,
	$act);
$stmt->execute() or die ($mysqli->error);
$id = $mysqli->insert_id;

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Nuevo registrar encargado caja chica ($id), caja $cch, usuario $usu, activo $act, hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_cajas_chicas_enc.php");
exit;
?>
