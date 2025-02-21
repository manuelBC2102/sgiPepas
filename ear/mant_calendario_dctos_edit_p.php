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

if (!isset($f_anio)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$error = 0;

	$anio = abs((int) filter_var($f_anio, FILTER_SANITIZE_NUMBER_INT));
	$mes = abs((int) filter_var($f_mes, FILTER_SANITIZE_NUMBER_INT));
	$diatope = abs((int) filter_var($f_diatope, FILTER_SANITIZE_NUMBER_INT));

	if ($mes > 12) {
		echo "<font color='red'><b>ERROR: El valor del mes no puede ser mayor que 12</b></font><br>";
		$error = 1;
	}

	if (!checkdate($mes, $diatope, $anio)) {
		echo "<font color='red'><b>ERROR: El valor del dia tope es incorrecto</b></font><br>";
		$error = 1;
	}

	if ($error == 1) exit;
}

list($ant_diatope) = getDiaTopeInfo($anio, $mes);

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("UPDATE calendario_dctos SET dia_tope=? WHERE anio=? AND mes=?") or die ($mysqli->error);
$stmt->bind_param('iii',
	$diatope,
	$anio,
	$mes);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Editar dia tope de calendario dctos, anio $anio, mes $mes, diatope $diatope (ant $ant_diatope), hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_calendario_dctos.php");
exit;
?>
