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

if (!isset($f_nom)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$error = 0;

	$nom = trim(filter_var($f_nom, FILTER_SANITIZE_STRING));
	$suc = abs((int) filter_var($f_suc, FILTER_SANITIZE_NUMBER_INT));
	$moneda = abs((int) filter_var($f_moneda, FILTER_SANITIZE_NUMBER_INT));
	$mont = abs((float) filter_var($f_mont, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
	$abrv = strtoupper(trim(filter_var($f_abrv, FILTER_SANITIZE_STRING)));
	$gti = abs((int) filter_var($f_lid_gti_def, FILTER_SANITIZE_NUMBER_INT));
	$dg_json = $f_lid_dg_json_def;
	$cta = trim(filter_var($f_cta, FILTER_SANITIZE_STRING));
	$act = abs((int) filter_var($f_act, FILTER_SANITIZE_NUMBER_INT));

	if (strlen($nom) == 0) {
		echo "<font color='red'><b>ERROR: El nombre no puede estar vacio</b></font><br>";
		$error = 1;
	}

	if ($mont == 0) {
		echo "<font color='red'><b>ERROR: El monto debe ser mayor que cero</b></font><br>";
		$error = 1;
	}

	if (strlen($abrv) == 0) {
		echo "<font color='red'><b>ERROR: La abreviatura no puede estar vacia</b></font><br>";
		$error = 1;
	}

	if (strlen($cta) == 0) {
		echo "<font color='red'><b>ERROR: La cuenta no puede estar vacia</b></font><br>";
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

$stmt = $mysqli->prepare("INSERT INTO cajas_chicas VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('siidsissi',
	$nom,
	$suc,
	$moneda,
	$mont,
	$abrv,
	$gti,
	$dg_json,
	$cta,
	$act);
$stmt->execute() or die ($mysqli->error);
$id = $mysqli->insert_id;

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Nuevo registrar caja chica ($id) $nom ($abrv), moneda ($moneda), monto ($mont), hecha por ".$_SESSION['rec_usu_nombre'];
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
