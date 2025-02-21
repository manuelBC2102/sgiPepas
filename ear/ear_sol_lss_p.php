<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

list($dni, $nombres, $cargo_id, $fecha_ing,
	$cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador($_SESSION['rec_codigogeneral_id']);

$zona_id = filter_var($f_zona_id, FILTER_SANITIZE_STRING);
$mon_id = abs((int) filter_var($f_mon_id, FILTER_SANITIZE_NUMBER_INT));
$fecha2 = filter_var($f_fecha2, FILTER_SANITIZE_STRING);
$motivo = trim(filter_var($f_motivo, FILTER_SANITIZE_STRING));
if($mon_id==2) {
	$ctacte = filter_var($f_cta_dolares, FILTER_SANITIZE_STRING);
}
$anio = date('Y');
$mes = date('m');

list($mon_nom, $mon_iso, $mon_simb, $mon_img) = getNomMoneda($mon_id);

$totalviaticos = 0;

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("INSERT INTO ear_solicitudes (usu_id, zona_id, mon_id, ear_monto, est_id, ear_sol_fec, ear_liq_fec,
	ear_sol_motivo, ear_tra_dni, ear_tra_nombres, ear_tra_cargo, ear_tra_area, ear_tra_sucursal, ear_tra_cta,
	ear_anio, ear_mes, ear_nro)
	SELECT ?, ?, ?, ?, 4, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, max_nro
	FROM (
		SELECT IFNULL(MAX(ear_nro), 0)+1 AS max_nro FROM ear_solicitudes
		WHERE usu_id=? AND ear_anio=? AND ear_mes=?) AS sub_tabla") or die ($mysqli->error);
$stmt->bind_param('isidsssssssssiiiii', $_SESSION['rec_usu_id'],
	$zona_id,
	$mon_id,
	$totalviaticos,
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
$stmt->store_result();
$fila=fetchAssocStatement($stmt);
$ear_numero = $fila[0];

$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 1, ?, ?, 'EAR Cero')") or die ($mysqli->error);
$stmt->bind_param('iis', $insertion_id, $_SESSION['rec_usu_id'], $ahora);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 2, ?, ?, 'EAR Cero')") or die ($mysqli->error);
$stmt->bind_param('iis', $insertion_id, $_SESSION['rec_usu_id'], $ahora);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 4, ?, ?, 'EAR Cero')") or die ($mysqli->error);
$stmt->bind_param('iis', $insertion_id, $_SESSION['rec_usu_id'], $ahora);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET ear_act_usu=?, ear_act_fec=?, ear_aprob_usu=?, ear_dese_fec=?
	WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('isisi', $_SESSION['rec_usu_id'], $ahora, $_SESSION['rec_usu_id'], $ahora, $insertion_id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Registro solicitud EAR Cero (".$insertion_id.") de ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

$to = getCorreoUsuario($_SESSION['rec_usu_id']);
$cc = array();
array_push ($cc, getCorreoUsuario(getUsuController()));
array_push ($cc, getCorreoUsuario(getUsuAdmin()));
array_push ($cc, 'mngmt@Minapp.com.pe');
$subject = "Solicitud Registrada de EAR Cero $ear_numero de ".$nombres;
$body = "Se ha registrado la solicitud de EAR $ear_numero de ".$nombres." por el monto de ".number_format($f_totalviaticos, 2, '.', ','). " $mon_nom.";
$body .= "\n\nEsta solicitud ya puede utilizarse para registrar su PLM y liquidacion respectiva.";
//R. enviarCorreo($to, $cc, $subject, $body);
ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);

list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($_SESSION['rec_usu_id']);
$to = getCorreoUsuario($usu_id_jefe);
$cc = null;
// No existen gerentes en el modulo admin de acuerdo al cambio realizado de fijar columnas jefes exclusivos para modulo admin (25.08.2014)
//$cc = array();
//array_push ($cc, getCorreoUsuario($usu_id_gerente));
$subject = "Solicitud Registrada de EAR Cero $ear_numero de ".$nombres;
$body = "Se ha registrado la solicitud de EAR $ear_numero de ".$nombres." por el monto de ".number_format($f_totalviaticos, 2, '.', ','). " $mon_nom.";
$body .= "\n\nNo es necesario realizar ningun accion, este correo es informativo.";
//R. enviarCorreo($to, $cc, $subject, $body);
ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);

include 'datos_cerrar_bd.php';

$_SESSION['ear_last_id']=$insertion_id;
header("Location: ear_sol_lss_res.php?zona_id=$zona_id");
exit;
?>
