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

if (!isset($f_gti)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$error = 0;

	$gti = abs((int) filter_var($f_gti, FILTER_SANITIZE_NUMBER_INT));
	$nom = trim(filter_var($f_nom, FILTER_SANITIZE_STRING));
	$gco = strtoupper(trim(filter_var($f_gco, FILTER_SANITIZE_STRING)));
	$act = abs((int) filter_var($f_act, FILTER_SANITIZE_NUMBER_INT));

	if (strlen($nom) == 0) {
		echo "<font color='red'><b>ERROR: El nombre del colobject no puede estar vacio</b></font><br>";
		$error = 1;
	}

	if (strlen($gco) == 0) {
		echo "<font color='red'><b>ERROR: El gco colobject no puede estar vacio</b></font><br>";
		$error = 1;
	}

	if ($error == 1) exit;
}

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("INSERT INTO gastos_colobjects VALUES (null, ?, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('issi',
	$gti,
	$nom,
	$gco,
	$act);
$stmt->execute() or die ($mysqli->error);
$id = $mysqli->insert_id;

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Nuevo colobject ($id), tipo $gti, nombre $nom ($gco), hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_colobjects.php");
exit;
?>
