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
	$ini = strtoupper(trim(filter_var($f_ini, FILTER_SANITIZE_STRING)));
	$est = abs((int) filter_var($f_est, FILTER_SANITIZE_NUMBER_INT));
	$gco = trim(filter_var($f_gco, FILTER_SANITIZE_STRING));
	$rol = trim(filter_var($f_rol, FILTER_SANITIZE_STRING));
	$jefe = abs((int) filter_var($f_jefe, FILTER_SANITIZE_NUMBER_INT));

	if (strlen($ini) == 0) {
		echo "<font color='red'><b>ERROR: El campo de iniciales no puede estar vacio</b></font><br>";
		$error = 1;
	}

	if (strlen($gco) == 0) {
		echo "<font color='red'><b>ERROR: El gco object no puede estar vacio</b></font><br>";
		$error = 1;
	}

	if ($error == 1) exit;
}

list($usu_id, $usu_nombre, $usu_iniciales, $usu_estado, $gco_cobj, $usu_rol, $usu_jefe) = getUsuarioInfo($id);

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

if($ini != $usu_iniciales) {
	$stmt = $mysqli->prepare("SELECT COUNT(*)
		FROM usu_detalle
		WHERE usu_iniciales=?");
	$stmt->bind_param("s", $ini);
	$stmt->execute() or die ($mysqli->error);
	$stmt->store_result();
	$fila=fetchAssocStatement($stmt);
	if ($fila[0] != 0) {
		echo "<font color='red'><b>ERROR: Ya existe un usuario con las iniciales $ini. Elija otra combinacion de iniciales.</b></font><br>";
		exit;
	}
}

if (is_null($usu_id)) {
	$stmt = $mysqli->prepare("INSERT INTO usu_detalle VALUES (?, ?, ?, ?, ?, ?)") or die ($mysqli->error);
	$stmt->bind_param('isissi',
		$id,
		$ini,
		$est,
		$gco,
		$rol,
		$jefe);
	$stmt->execute() or die ($mysqli->error);
}
else {
	$stmt = $mysqli->prepare("UPDATE usu_detalle SET usu_iniciales=?, usu_estado=?, gco_cobj=?, usu_rol=?, usu_jefe=? WHERE usu_id=?") or die ($mysqli->error);
	$stmt->bind_param('sissii',
		$ini,
		$est,
		$gco,
		$rol,
		$jefe,
		$id);
	$stmt->execute() or die ($mysqli->error);
}

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Editar usuario ($id), $usu_nombre, iniciales $ini (anterior $usu_iniciales), gco_obj $gco (anterior $gco_cobj), rol $rol (anterior $usu_rol), jefe $jefe (anterior $usu_jefe), hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_usuarios.php");
exit;
?>
