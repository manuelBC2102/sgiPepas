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
	$master = abs((int) filter_var($f_master, FILTER_SANITIZE_NUMBER_INT));
	$slave = abs((int) filter_var($f_slave, FILTER_SANITIZE_NUMBER_INT));
	$act = abs((int) filter_var($f_act, FILTER_SANITIZE_NUMBER_INT));

	if ($master == $slave) {
		echo "<font color='red'><b>ERROR: El usuario no puede ser igual en ambos campos.</b></font><br>";
		$error = 1;
	}

	if ($error == 1) exit;
}

list($uro_id, $master_usu_id, $slave_usu_id, $uro_act) = getUsuRegOtrInfo($id);

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

if($master != $master_usu_id && $slave != $slave_usu_id) {
	$stmt = $mysqli->prepare("SELECT COUNT(*)
		FROM usu_reg_otro
		WHERE master_usu_id=? AND slave_usu_id=?");
	$stmt->bind_param("ii", $master, $slave);
	$stmt->execute() or die ($mysqli->error);
	$stmt->store_result();
	$fila=fetchAssocStatement($stmt);
	if ($fila[0] != 0) {
		echo "<font color='red'><b>ERROR: Ya existe la asignacion de usuario especificada.</b></font><br>";
		exit;
	}
}

$stmt = $mysqli->prepare("UPDATE usu_reg_otro SET master_usu_id=?, slave_usu_id=?, uro_act=? WHERE uro_id=?") or die ($mysqli->error);
$stmt->bind_param('iiii',
	$master,
	$slave,
	$act,
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Editar registrar por otro usuario ($id), master $master ($master_usu_id), slave $slave ($slave_usu_id), activo $act ($uro_act), hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_usuregotr.php");
exit;
?>
