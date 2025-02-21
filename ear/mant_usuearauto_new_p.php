<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_usu)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$error = 0;

	$usu = abs((int) filter_var($f_usu, FILTER_SANITIZE_NUMBER_INT));
	$fec_ini = filter_var($f_rfecha2, FILTER_SANITIZE_STRING);
	$fec_fin = filter_var($f_rfecha4, FILTER_SANITIZE_STRING);
	$zona = filter_var($f_zona, FILTER_SANITIZE_STRING);
	$moneda = abs((int) filter_var($f_moneda, FILTER_SANITIZE_NUMBER_INT));
	$cta = filter_var($f_cta, FILTER_SANITIZE_STRING);
	$monto = abs((float) filter_var($f_monto, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
	$act = abs((int) filter_var($f_act, FILTER_SANITIZE_NUMBER_INT));

	if ($error == 1) exit;
}

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("INSERT INTO ear_usu_auto VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('isssidis',
	$usu,
	$fec_ini,
	$fec_fin,
	$zona,
	$moneda,
	$monto,
	$act,
	$cta);
$stmt->execute() or die ($mysqli->error);
$id = $mysqli->insert_id;

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Nueva solicitud ear automatica ($id), usuario ($usu), desde ($fec_ini), hasta ($fec_fin), zona ($zona), moneda ($moneda), cta ($cta), monto ($monto), activo ($activo), hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_usuearauto.php");
exit;
?>
