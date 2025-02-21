<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
//include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";


extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$oper = '';
if (isset($f_act1)) {
	$oper = 'Borrador';
	$est_id = 1;
}
else {
	echo "<font color='red'><b>ERROR: Operaci&oacute;n err&oacute;nea</b></font><br>";
	exit;
}

$tot_mon = abs((float) filter_var($f_tot_mon_inp, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$tipo = abs((int) filter_var($f_tipo_inp, FILTER_SANITIZE_NUMBER_INT));
$ear_id = 0;
if (isset($f_tipo1_sel)) $ear_id = abs((int) filter_var($f_tipo1_sel, FILTER_SANITIZE_NUMBER_INT));
$cch_id = 0;
if (isset($f_tipo2_sel)) $cch_id = abs((int) filter_var($f_tipo2_sel, FILTER_SANITIZE_NUMBER_INT));
$tope = abs((float) filter_var($f_tope_inp, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$exc = abs((int) filter_var($f_exc, FILTER_SANITIZE_NUMBER_INT));
$gti = abs((int) filter_var($f_lid_gti_def, FILTER_SANITIZE_NUMBER_INT));
$dg_json = $f_lid_dg_json_def;

if (isset($f_slave_usu_id)) {
	$slave_usu_id = abs((int) filter_var($f_slave_usu_id, FILTER_SANITIZE_NUMBER_INT));
}
else {
	$slave_usu_id = $_SESSION['rec_usu_persona_id'];
}

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now(), year(now())";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];
$anio = $fila[1];

if ($tipo == 1) {
	// Planillas de movilidad de EAR
	$stmt = $mysqli->prepare("INSERT INTO pla_mov (usu_id, pla_serie, pla_nro, mon_id, pla_monto, est_id, pla_reg_fec, ear_id, pla_tope, pla_exc, pla_com1, pla_gti, pla_dg_json)
		SELECT ?, ?, max_nro, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?
		FROM (
			SELECT IFNULL(MAX(pla_nro), 0)+1 AS max_nro FROM pla_mov
			WHERE pla_serie=?) AS sub_tabla") or die ($mysqli->error);
	$stmt->bind_param('iidisidisisi',
		$slave_usu_id,
		$anio,
		$tot_mon,
		$est_id,
		$ahora,
		$ear_id,
		$tope,
		$exc,
		$f_comentario1,
		$gti,
		$dg_json,
		$anio);
}
else if ($tipo == 2) {
	// Planillas de movilidad de Caja Chica
	$stmt = $mysqli->prepare("INSERT INTO pla_mov (usu_id, pla_serie, pla_nro, mon_id, pla_monto, est_id, pla_reg_fec, cch_id, pla_tope, pla_exc, pla_com1, pla_gti, pla_dg_json)
		SELECT ?, ?, max_nro, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?
		FROM (
			SELECT IFNULL(MAX(pla_nro), 0)+1 AS max_nro FROM pla_mov
			WHERE pla_serie=?) AS sub_tabla") or die ($mysqli->error);
	$stmt->bind_param('iidisidisisi',
		$slave_usu_id,
		$anio,
		$tot_mon,
		$est_id,
		$ahora,
		$cch_id,
		$tope,
		$exc,
		$f_comentario1,
		$gti,
		$dg_json,
		$anio);
}
$stmt->execute() or die ($mysqli->error);
$insertion_id = $mysqli->insert_id;

if (isset($f_motivo_inp)) {
	foreach ($f_motivo_inp as $k => $v) {
		$motivo_inp = $v;
		$fecdoc_inp = $f_fecdoc_inp[$k];
		if (strlen($fecdoc_inp) == 10) { $fecdoc_inp = substr($fecdoc_inp, 6, 4)."-".substr($fecdoc_inp, 3, 2)."-".substr($fecdoc_inp, 0, 2); } // Cambia formato fecha a ISO
		$salida_inp = $f_salida_inp[$k];
		$destino_inp = $f_destino_inp[$k];
		$monto_inp = $f_monto_inp[$k];

		$stmt = $mysqli->prepare("INSERT INTO pla_mov_detalle (pla_id, pmd_motivo, pmd_fec, pmd_salida, pmd_destino, pmd_monto, pmd_aprob, pmd_emp_asume)
			VALUES (?, ?, ?, ?, ?, ?, 1, ?)") or die ($mysqli->error);
		$stmt->bind_param('issssdd',
			$insertion_id,
			$motivo_inp,
			$fecdoc_inp,
			$salida_inp,
			$destino_inp,
			$monto_inp,
			$monto_inp);
		$stmt->execute() or die ($mysqli->error);
	}
}

if ($tipo == 1) {
	$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET pla_id=? WHERE ear_id=?") or die ($mysqli->error);
	$stmt->bind_param('ii', $insertion_id, $f_tipo1_sel);
	$stmt->execute() or die ($mysqli->error);
}

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$tipo_desc = ($tipo==1?'EAR':'CCH');
$desc = $oper." Planilla Movilidad $tipo_desc (".$insertion_id.") de ".getUsuarioNombre($slave_usu_id);
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

if ($tipo == 2 && false) {//falta hacer email. Luego quitar el false
	list($cch_id, $cch_nombre, $suc_nombre, $mon_nom, $mon_iso, $mon_img, $cch_monto,
		$cch_abrv, $cch_gti, $cch_dg_json, $cch_cta_bco, $cch_act,
		$suc_id, $mon_id) = getCajasChicasInfo($cch_id);
	list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($slave_usu_id);
	$arr = getUsuMastersJefesIdsLista($usu_id_jefe);

	// Enviar correo al jefe del usuario si la planilla de movilidad fue de caja chica
	$to = getCorreoUsuario($slave_usu_id);
	$cc = array();
	array_push ($cc, getCorreoUsuario(getUsuAdmin()));
	array_push ($cc, 'mngmt@Minapp.com.pe');
	$subject = "Solicitud pendiente de planilla de movilidad de CAJA CHICA de ".getNombreTrabajador(getCodigoGeneral(getUsuAd($slave_usu_id)));
	$body = "Se ha registrado la planilla de movilidad de CAJA CHICA $cch_nombre, realizado por ".getNombreTrabajador(getCodigoGeneral(getUsuAd($slave_usu_id))).".";
	$body .= "\n\nEsperando la aprobacion del Jefe.";
	//R. enviarCorreo($to, $cc, $subject, $body);
	ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);

	$to = null;
	$cc = array();
	//array_push ($cc, getUsuAd($usu_id_jefe));
	foreach ($arr as $v) {
		array_push ($cc, getCorreoUsuario($v));
	}
	$subject = "Solicitud pendiente de planilla de movilidad de CAJA CHICA de ".getNombreTrabajador(getCodigoGeneral(getUsuAd($slave_usu_id)));
	$body = "Se ha registrado la planilla de movilidad de CAJA CHICA $cch_nombre, realizado por ".getNombreTrabajador(getCodigoGeneral(getUsuAd($slave_usu_id))).".";
	$body .= "\n\nFavor de ingresar al modulo de administracion y seleccionar la opcion 'Aprobar Planillas de Movilidad de Caja Chica'.";
	//R. enviarCorreo($to, $cc, $subject, $body);

	$ventana   = "admin/movi_consulta_detalle.php?id=$insertion_id&opc=13";
	$aprobador = getUsuAd($usu_id_jefe);
	$detalle   = ConstructorMail::armarDetalle(null, $subject, $body);
	ConstructorMail::enviarCorreoInfoConLink($insertion_id, $aprobador, $ventana, $var_modulo_ident, $detalle);

	ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);

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
	/*$id                = $insertion_id;
	$aprobador         = getUsuAd($usu_id_jefe);
	$location          = "admin/index";
	$detalle_operacion = "Se ha registrado la planilla de movilidad de CAJA CHICA $cch_nombre, realizado por ".getNombreTrabajador(getCodigoGeneral(getUsuAd($slave_usu_id)));
	$operacion         = "planilla de movilidad de CAJA CHICA de ".getNombreTrabajador(getCodigoGeneral(getUsuAd($slave_usu_id)));
	$template          = "template_2";
	$ventana           = "admin/movi_consulta_detalle.php?id=$insertion_id&opc=13";
	$detalle           = ConstructorMail::armarDetalle($cc, $subject, $body);*/
	//ConstructorMail::enviarCorreoAprobacion($id, $aprobador, $location, $detalle_operacion, $operacion, $template, $ventana, $detalle);


}

include 'datos_cerrar_bd.php';

header("Location: movi_solicitud_res.php?id=$insertion_id&o=$oper");
exit;
?>
