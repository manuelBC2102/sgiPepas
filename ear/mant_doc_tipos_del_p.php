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
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($doc_id, $doc_abrv, $doc_ruc_req, $doc_apl_ret, $doc_apl_det, $doc_nro, $doc_desc, $doc_cod, $doc_tax_code, $doc_edit, $doc_borr, $doc_act) = getTipoDocInfo($id);
if ($doc_borr==0) {
	echo "<font color='red'><b>ERROR: No se puede borrar este tipo de documento</b></font><br>";
	exit;
}

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$query = "SELECT COUNT(*) FROM ear_liq_detalle WHERE doc_id=".$id;
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$doc_count = $fila[0];
if ($doc_count > 0) {
	echo "<font color='red'><b>ERROR: No se puede eliminar este tipo de documento porque ya ha sido utilizado en las liquidaciones</b></font><br>";
	exit;
}

$stmt = $mysqli->prepare("DELETE FROM doc_tipos WHERE doc_id=?") or die ($mysqli->error);
$stmt->bind_param('i', $id);
$stmt->execute() or die ("<b>ERROR (Consulte con TI)</b> ".$mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Tipo de documento eliminado ($id), $doc_abrv ($doc_cod), hecha por ".$_SESSION['rec_usu_nombre'];
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
