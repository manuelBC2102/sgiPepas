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

if (!isset($f_cod)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$error = 0;

	$cod = trim(filter_var($f_cod, FILTER_SANITIZE_STRING));
	$subcod = trim(filter_var($f_subcod, FILTER_SANITIZE_STRING));
	$nom = trim(filter_var($f_nom, FILTER_SANITIZE_STRING));
	$cta_cont = trim(filter_var($f_cta_cont, FILTER_SANITIZE_STRING));
	$act = abs((int) filter_var($f_act, FILTER_SANITIZE_NUMBER_INT));
	$acf = abs((int) filter_var($f_acf, FILTER_SANITIZE_NUMBER_INT));
	$cve = abs((int) filter_var($f_cve, FILTER_SANITIZE_NUMBER_INT));
	$fmt_glosa = trim(filter_var($f_fmt_glosa, FILTER_SANITIZE_STRING));

	if (strlen($subcod) == 1) $subcod = '0'.$subcod;
	else if (strlen($subcod) == 0) {
		echo "<font color='red'><b>ERROR: El subcodigo no puede estar vacio</b></font><br>";
		$error = 1;
	}

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

$conc_cod = $cod.$subcod;
$stmt = $mysqli->prepare("SELECT COUNT(*)
	FROM ear_conceptos
	WHERE conc_cod=?");
$stmt->bind_param("s", $conc_cod);
$stmt->execute() or die ($mysqli->error);
$stmt->store_result();
$fila=fetchAssocStatement($stmt);
if ($fila[0] != 0) {
	echo "<font color='red'><b>ERROR: Ya existe el concepto con el codigo $conc_cod, ingrese un subcodigo diferente.</b></font><br>";
	exit;
}

// Inserta concepto en soles
$stmt = $mysqli->prepare("INSERT INTO ear_conceptos VALUES (null, ?, ?, 1, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('sssiiis',
	$conc_cod,
	$nom,
	$cta_cont,
	$act,
	$acf,
	$cve,
	$fmt_glosa);
$stmt->execute() or die ($mysqli->error);
$id = $mysqli->insert_id;

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Nuevo concepto de liquidacion ($id), $nom ($conc_cod) en soles, hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

// Inserta concepto en dolares
$stmt = $mysqli->prepare("INSERT INTO ear_conceptos VALUES (null, ?, ?, 2, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('sssiiis',
	$conc_cod,
	$nom,
	$cta_cont,
	$act,
	$acf,
	$cve,
	$fmt_glosa);
$stmt->execute() or die ($mysqli->error);
$id = $mysqli->insert_id;

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Nuevo concepto de liquidacion ($id), $nom ($conc_cod) en dolares, hecha por ".$_SESSION['rec_usu_nombre'];
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
