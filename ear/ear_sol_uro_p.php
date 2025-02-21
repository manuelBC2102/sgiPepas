<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$slave_usu_id = abs((int) filter_var($f_slave_usu_id, FILTER_SANITIZE_NUMBER_INT));

list($dni, $nombres, $cargo_id, $fecha_ing,
	$cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador(getCodigoGeneral(getUsuAd($slave_usu_id)));

$zona_id = filter_var($f_zona_id, FILTER_SANITIZE_STRING);
$mon_id = abs((int) filter_var($f_mon_id, FILTER_SANITIZE_NUMBER_INT));
$fecha2 = filter_var($f_fecha2, FILTER_SANITIZE_STRING);
$motivo = trim(filter_var($f_motivo, FILTER_SANITIZE_STRING));
$cta_alt = trim(filter_var($f_cta_dolares, FILTER_SANITIZE_STRING));
if(strlen($cta_alt) > 0) {
	$ctacte = $cta_alt;
}
$anio = date('Y');
$mes = date('m');

list($mon_nom, $mon_iso, $mon_simb, $mon_img) = getNomMoneda($mon_id);

$hosp_otros_id = getIdHospOtros($mon_id);

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("INSERT INTO ear_solicitudes (usu_id, zona_id, mon_id, ear_monto, est_id, ear_sol_fec, ear_liq_fec,
	ear_sol_motivo, ear_tra_dni, ear_tra_nombres, ear_tra_cargo, ear_tra_area, ear_tra_sucursal, ear_tra_cta,
	ear_anio, ear_mes, ear_nro, master_usu_id)
	SELECT ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, max_nro, ?
	FROM (
		SELECT IFNULL(MAX(ear_nro), 0)+1 AS max_nro FROM ear_solicitudes
		WHERE usu_id=? AND ear_anio=? AND ear_mes=?) AS sub_tabla") or die ($mysqli->error);
$stmt->bind_param('isidsssssssssiiiiii', $slave_usu_id,
	$zona_id,
	$mon_id,
	$f_totalviaticos,
	$ahora,
	$fecha2,
	$motivo,
	$dni,
	$nombres,
	$cargo_desc,
	$area_desc,
	$sucursal,
	$ctacte,
	$anio,
	$mes,
	$_SESSION['rec_usu_id'],
	$slave_usu_id,
	$anio,
	$mes);

$stmt->execute() or die ($mysqli->error);

$insertion_id = $mysqli->insert_id;

//Obtiene nro de EAR
$stmt = $mysqli->prepare("SELECT CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/', ud.usu_iniciales) ear_numero
	FROM ear_solicitudes e
	LEFT JOIN usu_detalle ud ON ud.usu_id=e.usu_id
	LEFT JOIN recursos.usuarios ru ON ru.usu_id=e.ear_act_usu
	WHERE e.ear_id=?");
$stmt->bind_param("i", $insertion_id);
$stmt->execute() or die ($mysqli->error);
$result = $stmt->get_result();
$fila=$result->fetch_array();
$ear_numero = $fila[0];

//Agrega detalle boletos de viaje
$bole_mont = abs((float) filter_var($f_bole_mont, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
if ($bole_mont>0) {
	$via_id = getViaId('01');

	$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_monto) VALUES (?, ?, ?)") or die ($mysqli->error);
	$stmt->bind_param('iid', $insertion_id, $via_id, $bole_mont);
	$stmt->execute() or die ($mysqli->error);
}

//Agrega detalle alimentacion
$alim_dias = abs((int) filter_var($f_alim_dias, FILTER_SANITIZE_NUMBER_INT));
if ($alim_dias>0) {
	$via_id = getViaId("02$zona_id", $mon_id);
	$monto = getViaticosMonto('02', $mon_id, $zona_id);

	$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_dias, via_monto) VALUES (?, ?, ?, ?)") or die ($mysqli->error);
	$stmt->bind_param('iiid', $insertion_id, $via_id, $alim_dias, $monto);
	$stmt->execute() or die ($mysqli->error);
}

//Agrega detalle hospedaje
if (isset($f_hosp_ciud)) {
	foreach ($f_hosp_ciud as $k => $v) {
		if ($hosp_otros_id != $v) {
			$monto = getViaticosMontoId($v);

			$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_dias, via_monto) VALUES (?, ?, ?, ?)") or die ($mysqli->error);
			$stmt->bind_param('iidd', $insertion_id, $v, $f_hosp_dias[$k], $monto);
			$stmt->execute() or die ($mysqli->error);
		}
		else {
			$monto = abs((float) filter_var($f_precio_i[$k], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
			$via_desc = strtoupper(trim(filter_var($f_hosp_otro[$k], FILTER_SANITIZE_STRING)));

			$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_dias, via_monto, via_desc) VALUES (?, ?, ?, ?, ?)") or die ($mysqli->error);
			$stmt->bind_param('iidds', $insertion_id, $v, $f_hosp_dias[$k], $monto, $via_desc);
			$stmt->execute() or die ($mysqli->error);
		}
	}
}

//Agrega detalle movilidad
$movi_dias = abs((int) filter_var($f_movi_dias, FILTER_SANITIZE_NUMBER_INT));
if ($movi_dias>0) {
	$via_id = getViaId("04$zona_id", $mon_id);
	$monto = getViaticosMonto('04', $mon_id, $zona_id);

	$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_dias, via_monto) VALUES (?, ?, ?, ?)") or die ($mysqli->error);
	$stmt->bind_param('iiid', $insertion_id, $via_id, $movi_dias, $monto);
	$stmt->execute() or die ($mysqli->error);
}

//Agrega detalle gastos de representacion
$gast_mont = abs((float) filter_var($f_gast_mont, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
if ($gast_mont>0) {
	$via_id = getViaId('05');

	$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_monto) VALUES (?, ?, ?)") or die ($mysqli->error);
	$stmt->bind_param('iid', $insertion_id, $via_id, $gast_mont);
	$stmt->execute() or die ($mysqli->error);
}

//Agrega detalle otros gastos
if (isset($f_otro_item)) {
	$via_id = getViaId('06');
	foreach ($f_otro_item as $k => $v) {
		$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_desc, via_monto) VALUES (?, ?, ?, ?)") or die ($mysqli->error);
		$via_desc = trim($v);
		$stmt->bind_param('iisd', $insertion_id, $via_id, $via_desc, $f_otro_mont[$k]);
		$stmt->execute() or die ($mysqli->error);
	}
}

$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 1, ?, ?, 'Registro por otro usuario')") or die ($mysqli->error);
$stmt->bind_param('iis', $insertion_id, $_SESSION['rec_usu_id'], $ahora);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Registro solicitud EAR (".$insertion_id.") de ".$nombres." por ".getNombreTrabajador($_SESSION['rec_codigogeneral_id']);
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

$to = getCorreoUsuario($slave_usu_id);
$cc = array();
array_push ($cc, getCorreoUsuario($_SESSION['rec_usu_id']));
array_push ($cc, getCorreoUsuario(getUsuController()));
array_push ($cc, getCorreoUsuario(getUsuAdmin()));
array_push ($cc, 'mngmt@Minapp.com.pe');
$subject = "Solicitud Registrada de EAR $ear_numero de ".$nombres." por ".getNombreTrabajador($_SESSION['rec_codigogeneral_id']);
$body = "Se ha registrado la solicitud de EAR $ear_numero de ".$nombres." hecha por ".getNombreTrabajador($_SESSION['rec_codigogeneral_id'])." por el monto de ".number_format($f_totalviaticos, 2, '.', ','). " $mon_nom.";
$body .= "\n\nEsperando la aprobaci�n del Jefe.";
enviarCorreo($to, $cc, $subject, $body);

list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($_SESSION['rec_usu_id']);
$to = getCorreoUsuario($usu_id_jefe);
$cc = null;
// No existen gerentes en el modulo admin de acuerdo al cambio realizado de fijar columnas jefes exclusivos para modulo admin (25.08.2014)
//$cc = array();
//array_push ($cc, getCorreoUsuario($usu_id_gerente));
$subject = "Solicitud Registrada de EAR $ear_numero de ".$nombres." por ".getNombreTrabajador($_SESSION['rec_codigogeneral_id']);
$body = "Se ha registrado la solicitud de EAR $ear_numero de ".$nombres." hecha por ".getNombreTrabajador($_SESSION['rec_codigogeneral_id'])." por el monto de ".number_format($f_totalviaticos, 2, '.', ','). " $mon_nom.";
$body .= "\n\nFavor de ingresar al modulo Administracion de la web intranet, opcion Aprobaciones de Solicitud EAR y realizar la aprobacion o rechazo respectivo.";
enviarCorreo($to, $cc, $subject, $body);

include 'datos_cerrar_bd.php';

$_SESSION['ear_last_id']=$insertion_id;
header("Location: ear_solicitud_res.php?zona_id=$zona_id");
exit;
?>
