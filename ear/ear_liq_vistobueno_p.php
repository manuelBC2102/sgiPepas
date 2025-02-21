<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'reportesPDF.php';
include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
	$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
	$usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
	$ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
	$ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
	$master_usu_id) = getSolicitudInfo($id);

if ($est_id <> 6) {
	echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud</b></font><br>";
	exit;
}
// if ($usu_id <> $_SESSION['rec_usu_id']) {
	// echo "<font color='red'><b>ERROR: No se puede acceder a la informaci&oacute;n de la liquidaci&oacute;n</b></font><br>";
	// exit;
// }

$oper = '';
if (isset($f_grabar)) {
	$oper = 'Grabacion';
	$est_id = 6;
}
else if (isset($f_vistobueno)) {
	$oper = 'VistoBueno';
	$est_id = 7;
}
else {
	echo "<font color='red'><b>ERROR: Operaci&oacute;n err&oacute;nea</b></font><br>";
	exit;
}

$tot_mon_liq = abs((float) filter_var($f_tot_mon_liq, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$tot_mon_ret = abs((float) filter_var($f_tot_mon_ret, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$tot_mon_ret_no = abs((float) filter_var($f_tot_mon_ret_no, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$tot_mon_det = abs((float) filter_var($f_tot_mon_det, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$tot_mon_det_no = abs((float) filter_var($f_tot_mon_det_no, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$resul_inp = (float) filter_var($f_resul_inp, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); // Monto a descontar
$ear_liq_gast_asum = abs((float) filter_var($f_tot_mon_gast_asum, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));


include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$query = "SELECT IFNULL(MAX(CAST(lid_nro AS SIGNED)), 0)+1 AS nro_sgte FROM ear_liq_detalle WHERE doc_id=10 AND lid_ser=".date('Y');
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$nro_sgte = $fila[0];

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET ear_liq_mon=?, ear_liq_ret=?, ear_liq_ret_no=?, ear_liq_det=?, ear_liq_det_no=?, ear_liq_dcto=?, est_id=?, ear_act_fec=?, ear_act_usu=?, ear_liq_gast_asum=? WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('ddddddisidi',
	$tot_mon_liq,
	$tot_mon_ret,
	$tot_mon_ret_no,
	$tot_mon_det,
	$tot_mon_det_no,
	$resul_inp,
	$est_id,
	$ahora,
	$_SESSION['rec_usu_id'],
	$ear_liq_gast_asum,
	$id);
$stmt->execute() or die ($mysqli->error);

$query = "SELECT COUNT(*) FROM ear_dist_gast WHERE ear_id=$id";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$dist_gast_count = $fila[0];

if ($dist_gast_count == 0) {
	$stmt = $mysqli->prepare("INSERT INTO ear_dist_gast (ear_id, lid_gti, lid_dg_json) VALUES (?, ?, ?)") or die ($mysqli->error);
	$stmt->bind_param('iis',
		$id,
		$f_lid_gti_def,
		$f_lid_dg_json_def);
	$stmt->execute() or die ($mysqli->error);
}
else {
	$stmt = $mysqli->prepare("UPDATE ear_dist_gast SET lid_gti=?, lid_dg_json=? WHERE ear_id=?") or die ($mysqli->error);
	$stmt->bind_param('isi',
		$f_lid_gti_def,
		$f_lid_dg_json_def,
		$id);
	$stmt->execute() or die ($mysqli->error);
}

$stmt = $mysqli->prepare("DELETE FROM ear_liq_detalle WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('i',
	$id);
$stmt->execute() or die ($mysqli->error);

if ($est_id==7) {
	$stmt = $mysqli->prepare("DELETE FROM ear_liq_detalle_hist WHERE ear_id=? AND hist_id=7") or die ($mysqli->error);
	$stmt->bind_param('i',
		$id);
	$stmt->execute() or die ($mysqli->error);
}

if (isset($f_conc_l)) {
	foreach ($f_conc_l as $k => $v) {
		$conc_id = $f_conc_id[$k];
		$doc_id = $f_tipo_doc[$k];
		$ruc_nro = $f_ruc_nro[$k];
		$lid_fec = $f_fec_doc[$k];
		if (strlen($lid_fec) == 10) { $lid_fec = substr($lid_fec, 6, 4)."-".substr($lid_fec, 3, 2)."-".substr($lid_fec, 0, 2); } // Cambia formato fecha a ISO
		$lid_ser = $f_ser_doc[$k];
		$lid_nro = $f_num_doc[$k];
		$lid_glo = $f_det_doc[$k];
		$mon_id = $f_tipo_mon[$k];
		$lid_afe = $f_afecto_sel[$k];
		$lid_mon_afe = $f_afecto_inp[$k];
		$lid_mon_naf = $f_noafecto_inp[$k];
		$lid_tc = $f_tc_inp[$k];
		$lid_retdet_apl = $f_aplic_retdet[$k];
		$lid_retdet_tip = $f_retdet_tip[$k];
		$lid_retdet_mon = $f_retdet_monto[$k];
		$lid_gti = $f_gti_id[$k];
		$lid_dg_json = $f_dist_gast_json[$k];
		$lid_cta_cont = $v;
		$lid_aprob = $f_aprob_sel[$k];
		$lid_emp_asume = $f_gast_asum[$k];

		$veh_id = null;
		$veh_km = null;
		$lid_glo2 = null;
		if ($f_cve[$k] == 1) {
			if ($f_veh_l[$k] != -1) {
				$veh_id = $f_veh_l[$k];
				$veh_km = $f_km[$k];

				list($veh_id_2, $veh_placa) = getVehiculosInfo($veh_id);

				$lid_glo = $veh_placa.' - '.$veh_km.' KM';
			}
		}
		else if ($f_cve[$k] == 2) {
			$lid_glo2 = $f_peaje[$k];

			if ($f_veh_l[$k] != -1) {
				$veh_id = $f_veh_l[$k];
			}
		}
		else if ($f_cve[$k] == 3) {
			if ($f_veh_l[$k] != -1) {
				$veh_id = $f_veh_l[$k];
			}
		}

		if ($est_id==7 && $doc_id==10 && $lid_ser==0 && $lid_nro==0) {
			$lid_ser = date('Y');
			$lid_nro = $nro_sgte;

			$stmt = $mysqli->prepare("INSERT INTO ear_liq_detalle (ear_id, conc_id, doc_id, ruc_nro, lid_fec, lid_ser, lid_nro, lid_glo, mon_id,
				lid_afe, lid_mon_afe, lid_mon_naf, lid_tc, lid_retdet_apl, lid_retdet_tip, lid_retdet_mon, lid_gti, lid_dg_json, lid_cta_cont, lid_aprob, lid_emp_asume,
				veh_id, veh_km, lid_glo2)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)") or die ($mysqli->error);
			$stmt->bind_param('iiisssssiidddiidissidiis',
				$id,
				$conc_id,
				$doc_id,
				$ruc_nro,
				$lid_fec,
				$lid_ser,
				$lid_nro,
				$lid_glo,
				$mon_id,
				$lid_afe,
				$lid_mon_afe,
				$lid_mon_naf,
				$lid_tc,
				$lid_retdet_apl,
				$lid_retdet_tip,
				$lid_retdet_mon,
				$lid_gti,
				$lid_dg_json,
				$lid_cta_cont,
				$lid_aprob,
				$lid_emp_asume,
				$veh_id,
				$veh_km,
				$lid_glo2);
			$stmt->execute() or die ($mysqli->error);

			$nro_sgte++;
		}
		else {
			$stmt = $mysqli->prepare("INSERT INTO ear_liq_detalle (ear_id, conc_id, doc_id, ruc_nro, lid_fec, lid_ser, lid_nro, lid_glo, mon_id,
				lid_afe, lid_mon_afe, lid_mon_naf, lid_tc, lid_retdet_apl, lid_retdet_tip, lid_retdet_mon, lid_gti, lid_dg_json, lid_cta_cont, lid_aprob, lid_emp_asume,
				veh_id, veh_km, lid_glo2)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)") or die ($mysqli->error);
			$stmt->bind_param('iiisssssiidddiidissidiis',
				$id,
				$conc_id,
				$doc_id,
				$ruc_nro,
				$lid_fec,
				$lid_ser,
				$lid_nro,
				$lid_glo,
				$mon_id,
				$lid_afe,
				$lid_mon_afe,
				$lid_mon_naf,
				$lid_tc,
				$lid_retdet_apl,
				$lid_retdet_tip,
				$lid_retdet_mon,
				$lid_gti,
				$lid_dg_json,
				$lid_cta_cont,
				$lid_aprob,
				$lid_emp_asume,
				$veh_id,
				$veh_km,
				$lid_glo2);
			$stmt->execute() or die ($mysqli->error);
		}

		if ($est_id==7) {
			$stmt = $mysqli->prepare("INSERT INTO ear_liq_detalle_hist (ear_id, hist_id, conc_id, doc_id, ruc_nro, lid_fec, lid_ser, lid_nro, lid_glo, mon_id,
				lid_afe, lid_mon_afe, lid_mon_naf, lid_tc, lid_retdet_apl, lid_retdet_tip, lid_retdet_mon, lid_gti, lid_dg_json, lid_cta_cont, lid_aprob, lid_emp_asume,
				veh_id, veh_km, lid_glo2)
				VALUES (?, 7, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)") or die ($mysqli->error);
			$stmt->bind_param('iiisssssiidddiidissidiis',
				$id,
				$conc_id,
				$doc_id,
				$ruc_nro,
				$lid_fec,
				$lid_ser,
				$lid_nro,
				$lid_glo,
				$mon_id,
				$lid_afe,
				$lid_mon_afe,
				$lid_mon_naf,
				$lid_tc,
				$lid_retdet_apl,
				$lid_retdet_tip,
				$lid_retdet_mon,
				$lid_gti,
				$lid_dg_json,
				$lid_cta_cont,
				$lid_aprob,
				$lid_emp_asume,
				$veh_id,
				$veh_km,
				$lid_glo2);
			$stmt->execute() or die ($mysqli->error);
		}
	}
}

// Distribucion de gastos de planilla de movilidad se guarda aparte en su propia tabla
if (isset($f_gti_id['Splamov'])) {
	$pla_gti = $f_gti_id['Splamov'];
	$pla_dg_json = $f_dist_gast_json['Splamov'];
	$pla_id = $f_pm_id;

	$stmt = $mysqli->prepare("UPDATE pla_mov SET pla_gti=?, pla_dg_json=? WHERE pla_id=?") or die ($mysqli->error);
	$stmt->bind_param('isi',
		$pla_gti,
		$pla_dg_json,
		$pla_id);
	$stmt->execute() or die ($mysqli->error);
}

if ($est_id == 7) {
$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 7, ?, ?, null)") or die ($mysqli->error);
$stmt->bind_param('iis', $id, $_SESSION['rec_usu_id'], $ahora);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET ear_act_usu=? WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('ii', $_SESSION['rec_usu_id'], $id);
$stmt->execute() or die ($mysqli->error);
}

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = $oper." liquidacion EAR (".$id.") de ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

if ($est_id == 7) {
	$dif = count(getDiferenciasDetLiq($id, 5));
	if ($dif==0) $dif = count(getDiferenciasDetLiq($id, 6));

	$to = getCorreoUsuario($usu_id);
	$cc = array();
	list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
	array_push ($cc, getCorreoUsuario($usu_id_jefe));
	array_push ($cc, getCorreoUsuario($usu_id_gerente));
	array_push ($cc, getCorreoUsuario(getUsuController()));
	array_push ($cc, getCorreoUsuario(getUsuAdmin()));
	array_push ($cc, 'mngmt@Minapp.com.pe');
	if(!is_null($master_usu_id)) array_push ($cc, getCorreoUsuario($master_usu_id));
	$subject = "Visto Bueno de Liquidacion de EAR $ear_numero de ".$ear_tra_nombres;
	$body = "Se ha dado visto bueno a la liquidacion del EAR $ear_numero de $ear_tra_nombres.";
	if ($dif!=0) {
		// Adjuntar pdf si es que se aprueba
		$attachString = getCartaEarLiq($id, 'S');
		if (is_null($attachString)) die("Error en la generación del archivo PDF, no se completó la transacción. (Cadena vacía)");
		$attachFilename = __DIR__ ."/LGS_".str_replace("/", "_", $ear_numero).".pdf";

		$body .= "\n\nNota al colaborador: Se han detectado cambios en su liquidacion, revisar la liquidacion actualizada adjunta en este correo para su conformidad, NO ES necesario volver a imprimir.";
	}
	$body .= "\n\nEsperando el registro y control de contabilidad.";
	//R. enviarCorreo($to, $cc, $subject, $body, $attachString, $attachFilename);
	ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident,"info",$attachString,$attachFilename);

	$to = getCorreoUsuario(getUsuSupCont());
	$cc = null;
	$subject = "Visto Bueno de Liquidacion de EAR $ear_numero de ".$ear_tra_nombres;
	$body = "Se ha dado visto bueno a la liquidacion del EAR $ear_numero de $ear_tra_nombres.";
	$body .= "\n\nFavor de ingresar al modulo Administracion de la web intranet, opcion Actualizar Estado y Descarga Excel de Liquidaciones y realizar los ajustes necesarios y actualizar el estado respectivo.";
	//R. enviarCorreo($to, $cc, $subject, $body);
	$ventana   = "admin/ear_contabilidad.php";
	$aprobador = getUsuAd(getUsuSupCont());
	$detalle   = ConstructorMail::armarDetalle($cc, $subject, $body);
	ConstructorMail::enviarCorreoInfoConLink($id, $aprobador, $ventana, $var_modulo_ident, $detalle);
}

include 'datos_cerrar_bd.php';

header("Location: ear_liq_vistobueno_res.php?id=$id&o=$oper");
exit;
?>
