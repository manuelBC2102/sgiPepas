<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
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
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
	$grp = abs((int) filter_var($f_grp, FILTER_SANITIZE_NUMBER_INT));
	$usu = abs((int) filter_var($f_usu, FILTER_SANITIZE_NUMBER_INT));
}

list($grp_id, $usu_id) = getPermisoInfo($id);

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("SELECT COUNT(*)
	FROM paginas_permisos
	WHERE grp_id=? AND usu_id=?");
$stmt->bind_param("ii", $grp, $usu);
$stmt->execute() or die ($mysqli->error);
$stmt->store_result();
$fila=fetchAssocStatement($stmt);
if ($fila[0] != 0) {
	echo "<font color='red'><b>ERROR: Ya existe la asignacion de usuario especificada.</b></font><br>";
	exit;
}

$stmt = $mysqli->prepare("UPDATE paginas_permisos SET grp_id=?, usu_id=? WHERE perm_id=?") or die ($mysqli->error);
$stmt->bind_param('iii',
	$grp,
	$usu,
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Editar permiso ($id), grupo $grp ($grp_id), usuario $usu ($usu_id), hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_permisos.php");
exit;
?>
