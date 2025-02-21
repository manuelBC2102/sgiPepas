<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'parametros.php';
//include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

list($dni, $nombres,$apellido_paterno,$apellido_materno, $cargo_id, $fecha_ing,
	$cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador($_SESSION['rec_usu_persona_id']);

$zona_id = filter_var($f_zona_id, FILTER_SANITIZE_STRING);
$mon_id = abs((int) filter_var($f_mon_id, FILTER_SANITIZE_NUMBER_INT));
$fecha2 = filter_var($f_fecha2, FILTER_SANITIZE_STRING);
$fecha3 = filter_var($f_fecha3, FILTER_SANITIZE_STRING);
$motivo = trim(filter_var($f_motivo, FILTER_SANITIZE_STRING));
$cta_alt = trim(filter_var($f_cta_dolares, FILTER_SANITIZE_STRING));
$orden_trabajo = (abs((int) filter_var($f_idOrdenTrabajo, FILTER_SANITIZE_NUMBER_INT)));
$orden_trabajo_id = null;
$empleado_id = (abs((int) filter_var($f_idEmpleado, FILTER_SANITIZE_NUMBER_INT)));
$constante_empleado = (abs((int) filter_var($f_constante_empleado, FILTER_SANITIZE_NUMBER_INT)));
$constante_empleado = isset($constante_empleado)? $constante_empleado : 0;
//$variable = $constante_empleado == 0 ? $_SESSION['rec_usu_id'] : $empleado_id;
$variable = 0;
if($constante_empleado == 0) {
	$variable=  obtenerPersonaIdSGI($_SESSION['rec_usu_persona_id']);
}else{
	$variable = $empleado_id;
}

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

$stmt = $mysqli->prepare("INSERT INTO ear_solicitudes (usu_id, zona_id, mon_id, ear_monto, est_id, ear_sol_fec, ear_liq_fec,ear_liq_estimada,
	ear_sol_motivo, ear_tra_dni, ear_tra_nombres, ear_tra_cargo, ear_tra_area, ear_tra_sucursal, ear_tra_cta,
	ear_anio, ear_mes, sgi_orden_trabajo_id, ear_nro, tipo_usu_id,usuario_reg_id)
	SELECT ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, max_nro, ?, ?
	FROM (
		SELECT IFNULL(MAX(ear_nro), 0)+1 AS max_nro FROM ear_solicitudes
		WHERE usu_id=? AND ear_anio=? AND ear_mes=?) AS sub_tabla") or die ($mysqli->error);
$stmt->bind_param('isidssssssssssiiiiiiii',
	$variable,
	$zona_id,
	$mon_id,
	$f_totalviaticos,
	$ahora,
	$fecha2,
        $fecha3,
	$motivo,
	$dni,
	$nombres,
	$cargo_desc,
	$area_desc,
	$sucursal,
	$ctacte,
	$anio,
	$mes,
    $orden_trabajo_id,
	$constante_empleado,
	$_SESSION['rec_usu_id'],
	$variable,
	$anio,
	$mes);

$stmt->execute() or die ($mysqli->error);

$insertion_id = $mysqli->insert_id;

//Obtiene nro de EAR
$stmt = $mysqli->prepare("SELECT CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/',
		fn_obtener_iniciales(concat(ifnull(p.nombre,''),' ',ifnull(p.apellido_paterno,''),' ',ifnull(p.apellido_materno,'')))) ear_numero
	FROM ear_solicitudes e
	LEFT JOIN ".$baseSGI.".usuario ud ON ud.id=e.usu_id
	LEFT JOIN ".$baseSGI.".persona p ON p.id=ud.persona_id
	WHERE e.ear_id=?");
$stmt->bind_param("i", $insertion_id);
$stmt->execute() or die ($mysqli->error);
$stmt->store_result();
$fila=fetchAssocStatement($stmt);
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
//	$monto = getViaticosMonto('02', $mon_id, $zona_id);
        $monto = abs((float) filter_var($f_alim_montodia, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

	$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_dias, via_monto) VALUES (?, ?, ?, ?)") or die ($mysqli->error);
	$stmt->bind_param('iiid', $insertion_id, $via_id, $alim_dias, $monto);
	$stmt->execute() or die ($mysqli->error);
}

//Agrega detalle hospedaje
if (isset($f_hosp_ciud)) {
	foreach ($f_hosp_ciud as $k => $v) {
		if ($hosp_otros_id != $v) {
//			$monto = getViaticosMontoId($v);
                        $monto = abs((float) filter_var($f_precio_i[$k], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

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
//	$monto = getViaticosMonto('04', $mon_id, $zona_id);
        $monto = abs((float) filter_var($f_movi_montodia, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

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

$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 1, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('iiss', $insertion_id, $_SESSION['rec_usu_id'], $ahora,$motivo);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Registro solicitud EAR (".$insertion_id.") de ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);

//ENVIO DE CORREO
$to = getCorreoUsuario($_SESSION['rec_usu_id']);
$cc = array();
$subject = "Solicitud Registrada de EAR $ear_numero de ".$nombres." ".$apellido_paterno." ".$apellido_materno;
$body = "Se ha registrado la solicitud de EAR $ear_numero de ".$nombres." ".$apellido_paterno." ".$apellido_materno." por el monto de ".number_format($f_totalviaticos, 2, '.', ','). " $mon_nom.";
$body .= "\n\nEsperando la aprobaci&oacute;n del Jefe.";

//obtener la plantilla del email
list($plantillaDestinatario,$plantillaAsunto,$plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
$plantillaCuerpo = str_replace("[|asunto|]", 'Solicitud Registrada de EAR', $plantillaCuerpo);
$plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

//insertar en email_envio de sgi
$emailEnvioId = insertarEmailEnvioSGI($to, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);

//ENVIO CORREO A JEFE
//OBTENER EL JEFE
list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($_SESSION['rec_usu_id']);
array_push ($cc, getCorreoUsuario($usu_id_jefe));
$cc = obtenerCorreosPerfilSGI($pADMINIST, $cc);
$cc = obtenerCorreosPerfilSGI($pGERENTE, $cc);
$cc = obtenerCorreosPerfilSGI($pASISTENTE_ADMINISTRATIVO, $cc);

$correo='';
$cc = array_unique($cc);
foreach ($cc as $index => $valor) {
    $correo=$correo.$valor.';';
}

if($correo!=''){
    $subject = "Solicitud Registrada de EAR $ear_numero de ".$nombres." ".$apellido_paterno." ".$apellido_materno;
    $body = "Se ha registrado la solicitud de EAR $ear_numero de ".$nombres." ".$apellido_paterno." ".$apellido_materno." por el monto de ".number_format($f_totalviaticos, 2, '.', ','). " $mon_nom.";
    $body .= "\n\nFavor de ingresar al m&oacute;dulo Entregas a Rendir de la web, opci&oacute;n Aprobaci&oacute;n de solicitudes y realizar la aprobaci&oacute;n o rechazo respectivo.";

    //obtener la plantilla del email
    list($plantillaDestinatario,$plantillaAsunto,$plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
    $plantillaCuerpo = str_replace("[|asunto|]", 'Solicitud Registrada de EAR', $plantillaCuerpo);
    $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

    //insertar en email_envio de sgi
    $emailEnvioId2 = insertarEmailEnvioSGI($correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);

}

$stmt->close();
$mysqli->commit();
include 'datos_cerrar_bd.php';

	/*
	 * @version 1.0
	 * @function   : enviarCorreoAprobacion
	 * @parametros :
	 * -------------------------
	 * @id                : Id de la tabla a actualizar.
	 * @aprobador         : Usuario AD del aprobador, necesario para el mail y la actualizaci�n.
	 * @location          : Ruta + nombre del archivo php (omitiendo '.php') donde se realiza la aprobaci�n (no el proceso _p). Ejemplo "recursos/aprob_vacadel"
	 * @detalle_operacion : Descripci�n de la operaci�n a realizar.
	 * @operacion         : Nombre de la operacion a realizar. Ajustar seg�n el template a usar
	 * @template          : (Opcional) Nombre del template a utilizar para armar el correo, por defecto "template_1"
	 * @ventana           : (Opcional) Ruta + nombre del archivo donde se muestra la ventana de aprobacion, por defecto la misma que @location
	 */
//	$id                = $insertion_id;
//	$aprobador         = getUsuAd($usu_id_jefe);
//	$location          = "admin/ear_sol_vistobueno";
//	$detalle_operacion = "Se ha registrado la solicitud de EAR $ear_numero de ".$nombres." por el monto de ".number_format($f_totalviaticos, 2, '.', ','). " $mon_nom";
//	$operacion         = "EAR $ear_numero";
//	$template          = "template_1";
//	$ventana           = "admin/ear_consulta.php?cons_id=2&est_id=1";
//	ConstructorMail::enviarCorreoAprobacion($id, $aprobador, $location, $detalle_operacion, $operacion, $template, $ventana, array("subject" => $subject));

$_SESSION['ear_last_id']=$insertion_id;
header("Location: ear_solicitud_res.php?zona_id=$zona_id");
exit;
?>
