<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'SUP_CONT');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
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
	$error = 0;

	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
	$ver = abs((int) filter_var($f_ver, FILTER_SANITIZE_NUMBER_INT));
	$amb = abs((int) filter_var($f_amb, FILTER_SANITIZE_NUMBER_INT));
	$roj = abs((int) filter_var($f_roj, FILTER_SANITIZE_NUMBER_INT));

	if ($error == 1) exit;
}

list($est_id, $est_nom, $val_min_verde, $val_min_ambar, $val_min_rojo) = getSemaforosInfo($id);

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("UPDATE ear_semaforo SET val_min_verde=?, val_min_ambar=?, val_min_rojo=? WHERE sema_id=?") or die ($mysqli->error);
$stmt->bind_param('iiii',
	$ver,
	$amb,
	$roj,
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Editar semaforo ($id), verde $ver (ant $val_min_verde), ambar $amb (ant $val_min_ambar), rojo $roj (ant $val_min_rojo), hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_semaforos.php");
exit;
?>
