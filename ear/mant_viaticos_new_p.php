<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'parametros.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pSUP_CONT);
//$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
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
	$nom = strtoupper(trim(filter_var($f_nom, FILTER_SANITIZE_STRING)));
	$sol = abs((float) filter_var($f_sol, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
	$dol = abs((float) filter_var($f_dol, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

	if (strlen($cod) != 6 || substr($cod, 0, 2) != "03") {
		echo "<font color='red'><b>ERROR: El codigo debe tener seis numeros y empezar con 03</b></font><br>";
		$error = 1;
	}

	if (strlen($nom) == 0) {
		echo "<font color='red'><b>ERROR: El nombre del viatico no puede estar vacio</b></font><br>";
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

$stmt = $mysqli->prepare("SELECT COUNT(*)
	FROM ear_viaticos
	WHERE via_cod=?");
$stmt->bind_param("s", $cod);
$stmt->execute() or die ($mysqli->error);
$stmt->store_result();
$fila=fetchAssocStatement($stmt);
if ($fila[0] != 0) {
	echo "<font color='red'><b>ERROR: Ya existe el viatico con el codigo $cod, ingrese un codigo diferente.</b></font><br>";
	exit;
}

// Inserta viatico en soles
$stmt = $mysqli->prepare("INSERT INTO ear_viaticos VALUES (null, ?, ?, 1, ?)") or die ($mysqli->error);
$stmt->bind_param('ssd',
	$cod,
	$nom,
	$sol);
$stmt->execute() or die ($mysqli->error);
$id = $mysqli->insert_id;

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Nuevo viatico ($id), $nom ($cod) en soles, hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

// Inserta viatico en dolares
$stmt = $mysqli->prepare("INSERT INTO ear_viaticos VALUES (null, ?, ?, 2, ?)") or die ($mysqli->error);
$stmt->bind_param('ssd',
	$cod,
	$nom,
	$dol);
$stmt->execute() or die ($mysqli->error);
$id = $mysqli->insert_id;

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Nuevo viatico ($id), $nom ($cod) en dolares, hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

header("Location: mant_viaticos.php");
exit;
?>
