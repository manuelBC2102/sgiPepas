<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'parametros.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Operaci&oacute;n err&oacute;nea</b></font><br>";
	exit;
}

$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));

list($pla_numero, $est_id, $pla_reg_fec, $ear_numero, $tope_maximo, $usu_id, $ear_id,
	$est_nom, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec,
	$pla_exc, $pla_com1, $pla_com2, $pla_com3,
	$pla_tipo, $ccl_id, $cch_id) = getPlanillaMovilidadInfo($id);

if ($est_id!=1) {
	echo "<font color='red'><b>ERROR: No se puede anular planilla, estado incorrecto</b></font><br>";
	exit;
}
if (is_null($cch_id)) {
	// Planillas EAR
        $count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'JEFEOGERENTE');
        $count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
        $count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
        $count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pGERENTE);
        $count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pASISTENTE_ADMINISTRATIVO);
        if ($usu_id!=$_SESSION['rec_usu_persona_id'] && $count == 0) {
		echo "<font color='red'><b>ERROR: No puede anular planilla de otros usuarios, se ha notificado esta operacion al administrador. (#1)</b></font><br>";
		exit;
	}
}
else {
	// Planillas CCH
	list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
	$valid_users = array($usu_id, $usu_id_jefe, $usu_id_gerente, getUsuAdmin());
	$valid_users = array_merge($valid_users, getUsuTI());
	if(!in_array($_SESSION['rec_usu_persona_id'], $valid_users)) {
		echo "<font color='red'><b>ERROR: No puede anular planilla de otros usuarios, se ha notificado esta operacion al administrador. (#2)</b></font><br>";
		exit;
	}
}

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now(), year(now())";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];
$anio = $fila[1];

$stmt = $mysqli->prepare("UPDATE pla_mov SET est_id=2 WHERE pla_id=? AND est_id=1") or die ($mysqli->error);
$stmt->bind_param('i',
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET pla_id=NULL WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('i',
	$ear_id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Anulacion Borrador Planilla Movilidad (".$id.") de ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';

if (isset($f_cch)) {
	header("Location: movi_aprobacion_cch.php");
}
else if ($usu_id == $_SESSION['rec_usu_id']) {
	header("Location: movi_consulta.php?cons_id=1");
}
else {
	header("Location: movi_consulta_otro.php?cons_id=1&usuId=".$usu_id);
}
exit;
?>
