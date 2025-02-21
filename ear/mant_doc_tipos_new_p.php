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

if (!isset($f_nro)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$nro = trim(filter_var($f_nro, FILTER_SANITIZE_STRING));
	$desc = trim(filter_var($f_desc, FILTER_SANITIZE_STRING));
	$abrv = trim(filter_var($f_abrv, FILTER_SANITIZE_STRING));
	$cod = strtoupper(trim(filter_var($f_cod, FILTER_SANITIZE_STRING)));
	$ruc_req = abs((int) filter_var($f_ruc_req, FILTER_SANITIZE_NUMBER_INT));
	$apl_ret = abs((int) filter_var($f_apl_ret, FILTER_SANITIZE_NUMBER_INT));
	$apl_det = abs((int) filter_var($f_apl_det, FILTER_SANITIZE_NUMBER_INT));
	$tax_code = abs((int) filter_var($f_tax_code, FILTER_SANITIZE_NUMBER_INT));
	$act = abs((int) filter_var($f_act, FILTER_SANITIZE_NUMBER_INT));
}

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("INSERT INTO doc_tipos VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, ?)") or die ($mysqli->error);
$stmt->bind_param('ssssiiiii',
	$nro,
	$desc,
	$abrv,
	$cod,
	$ruc_req,
	$apl_ret,
	$apl_det,
	$tax_code,
	$act);
$stmt->execute() or die ($mysqli->error);
$id = $mysqli->insert_id;

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Nuevo tipo de documento ($id), $abrv ($cod), hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_doc_tipos.php");
exit;
?>
