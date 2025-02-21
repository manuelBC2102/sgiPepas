<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'SUP_CONT');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ANA_CONT');
$count += getPermisosPagina($_SESSION['rec_usu_id'], basename(__FILE__));
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_ruc)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$error = 0;

	$ruc = strtoupper(trim(filter_var($f_ruc, FILTER_SANITIZE_STRING)));
	$nom = strtoupper(trim(filter_var($f_nom, FILTER_SANITIZE_STRING)));
	$act = abs((int) filter_var($f_act, FILTER_SANITIZE_NUMBER_INT));
	$ret = abs((int) filter_var($f_ret, FILTER_SANITIZE_NUMBER_INT));
	$hab = abs((int) filter_var($f_hab, FILTER_SANITIZE_NUMBER_INT));
	$fac = abs((int) filter_var($f_fac, FILTER_SANITIZE_NUMBER_INT));
	$pro = strtoupper(trim(filter_var($f_pro, FILTER_SANITIZE_STRING)));

	if (strlen($nom) == 0) {
		echo "<font color='red'><b>ERROR: El nombre del proveedor no puede estar vacio</b></font><br>";
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

$stmt = $mysqli->prepare("INSERT INTO proveedores VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('ssiiisis',
	$ruc,
	$nom,
	$act,
	$ret,
	$hab,
	$ahora,
	$fac,
	$pro);
$stmt->execute() or die ($mysqli->error);
$id = $mysqli->insert_id;

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Nuevo proveedor ($id), ruc $ruc, nombre $nom, activo $act, retencion $ret, habido $hab, factura $fac, provincia $pro, hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_proveedores.php");
exit;
?>
