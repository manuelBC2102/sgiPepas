<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'parametros.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pASISTENTE_ADMINISTRATIVO);
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_pla)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$error = 0;

	$pla = strtoupper(trim(filter_var($f_pla, FILTER_SANITIZE_STRING)));
	$mar = abs((int) filter_var($f_mar, FILTER_SANITIZE_NUMBER_INT));
	$mod = strtoupper(trim(filter_var($f_mod, FILTER_SANITIZE_STRING)));
	$usu = (int) filter_var($f_usu, FILTER_SANITIZE_NUMBER_INT);
	$act = abs((int) filter_var($f_act, FILTER_SANITIZE_NUMBER_INT));

	if (strlen($pla) != 7) {
		echo "<font color='red'><b>ERROR: La placa debe tener 7 caracteres incluido el guion</b></font><br>";
		$error = 1;
	}

	if (substr($pla, 2, 1) != '-' && substr($pla, 3, 1) != '-') {
		echo "<font color='red'><b>ERROR: La placa debe incluir el guion en la posicion correcta</b></font><br>";
		$error = 1;
	}

	if (strlen($mod) == 0) {
		echo "<font color='red'><b>ERROR: El modelo no puede estar vacio</b></font><br>";
		$error = 1;
	}

	if ($error == 1) exit;
}

if ($usu == -1) $usu = null;

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("SELECT COUNT(*)
	FROM vehiculos
	WHERE usu_id=?");
$stmt->bind_param("i", $usu);
$stmt->execute() or die ($mysqli->error);
$stmt->store_result();
$fila=fetchAssocStatement($stmt);
if ($fila[0] != 0) {
	echo "<font color='red'><b>ERROR: El usuario especificado ya esta asignado a otro vehiculo, no se puede continuar el registro.</b></font><br>";
	exit;
}

$stmt = $mysqli->prepare("INSERT INTO vehiculos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('sisii',
	$pla,
	$mar,
	$mod,
	$usu,
	$act);
$stmt->execute() or die ($mysqli->error);
$id = $mysqli->insert_id;

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Nuevo registrar vehiculo ($id) $pla, $mod, hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_vehiculos.php");
exit;
?>
