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

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$error = 0;

	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
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

list($prov_id, $ruc_nro, $prov_nom, $ruc_act, $ruc_ret, $ruc_hab, $prov_factura, $prov_provincia, $ruc_chk_fec) = getProveedoresInfo($id);

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("UPDATE proveedores SET prov_nom=?, ruc_act=?, ruc_ret=?, ruc_hab=?, prov_factura=?, prov_provincia=?, ruc_chk_fec=? WHERE prov_id=?") or die ($mysqli->error);
$stmt->bind_param('siiiissi',
	$nom,
	$act,
	$ret,
	$hab,
	$fac,
	$pro,
	$ahora,
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Editar proveedor ($id), nombre $nom (ant $prov_nom), activo $act (ant $ruc_act), retencion $ret (ant $ruc_ret), habido $hab (ant $ruc_hab), factura $fac (ant $prov_factura), provincia $pro (ant $prov_provincia), hecha por ".$_SESSION['rec_usu_nombre'];
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
