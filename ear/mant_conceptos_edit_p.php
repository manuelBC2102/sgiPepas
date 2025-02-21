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
	$nom = trim(filter_var($f_nom, FILTER_SANITIZE_STRING));
	$cta_cont = trim(filter_var($f_cta_cont, FILTER_SANITIZE_STRING));
	$act = abs((int) filter_var($f_act, FILTER_SANITIZE_NUMBER_INT));
	$acf = abs((int) filter_var($f_acf, FILTER_SANITIZE_NUMBER_INT));
	$cve = abs((int) filter_var($f_cve, FILTER_SANITIZE_NUMBER_INT));
	$fmt_glosa = trim(filter_var($f_fmt_glosa, FILTER_SANITIZE_STRING));

	if (strlen($nom) == 0) {
		echo "<font color='red'><b>ERROR: El nombre del concepto no puede estar vacio</b></font><br>";
		$error = 1;
	}

	if (strlen($cta_cont) == 0) {
		echo "<font color='red'><b>ERROR: La cuenta contable del concepto no puede estar vacia</b></font><br>";
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

$stmt = $mysqli->prepare("UPDATE ear_conceptos SET conc_nom=?, conc_cta_cont=?, conc_act=?, conc_acf=?, conc_cve=?, conc_fmt_glosa=? WHERE conc_id=?") or die ($mysqli->error);
$stmt->bind_param('ssiiisi',
	$nom,
	$cta_cont,
	$act,
	$acf,
	$cve,
	$fmt_glosa,
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Editar concepto de liquidacion ($id), $nom ($cta_cont), hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_conceptos.php");
exit;
?>
