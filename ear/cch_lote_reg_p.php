<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'reportesPDF.php';
include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

$usu_id = $_SESSION['rec_usu_id'];

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

// Valida que se haya enviado el parametro obligatoriamente
// Este id es el lote de la caja chica
if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

// list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
	// $ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
	// $usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
	// $ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
	// $ear_liq_gast_asum, $pla_id, $ear_act_obs1) = getSolicitudInfo($id);

$arr = getLoteCajaChicaInfo($id);
// Si no existe el lote de esa caja chica se genera error
if (count($arr) == 0) {
	echo "<font color='red'><b>ERROR: No se encuentra lote de caja chica.</b></font><br>";
	exit;
}
else {
	list($ccl_id, $cch_nombre, $ccl_numero, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ccl_monto_ini, $ccl_gti, $ccl_dg_json, $ccl_cta_bco,
		$ccl_ape_fec, $ape_usu_nombre, $ccl_cie_fec, $cie_usu_nombre,
		$ccl_aprob_fec, $aprob_usu_nombre, $ccl_act_fec, $act_usu_nombre,
		$ccl_monto_usado, $est_id, $est_nom, $suc_nombre,
		$ccl_ret, $ccl_ret_no, $ccl_det, $ccl_det_no, $ccl_gast_asum, $ccl_pend, $cch_id) = $arr;
}

// Valida el acceso
$arr = getCajasChicasEncAcceso($usu_id);
$arr2 = array();
foreach ($arr as $v) {
	$arr2[] = $v[0];
}
if (!in_array($cch_id, $arr2)) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

// Valida el estado
if ($est_id <> 1) {
	echo "<font color='red'><b>ERROR: No se puede modificar este lote de caja chica, ya ha sido cerrada o procesada.</b></font><br>";
	exit;
}

$oper = '';
if (isset($f_grabar)) {
	$oper = 'Grabacion';
	$est_id = 1;
}
else if (isset($f_enviar)) {
	$oper = 'Envio';
	$est_id = 2;
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
// $resul_inp = (float) filter_var($f_resul_inp, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); // Monto a desembolsar
$tot_mon_pend = abs((float) filter_var($f_tot_mon_pend, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$tot_mon_cuadre = abs((float) filter_var($f_tot_mon_cuadre, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$tot_mon_banco = abs((float) filter_var($f_tot_mon_banco, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$query = "SELECT IFNULL(MAX(CAST(lid_nro AS SIGNED)), 0)+1 AS nro_sgte FROM cajas_chicas_lote_det WHERE doc_id=10 AND lid_ser=".date('Y');
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$nro_sgte = $fila[0];

$query = "SELECT IFNULL(MAX(CAST(cldp_nro AS SIGNED)), 0)+1 AS nro_sgte2 FROM cajas_chicas_lote_docp WHERE cldp_anio=".date('Y');
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$nro_sgte2 = $fila[0];

// Documentos pendientes se guardan aparte en su propia tabla
if (isset($f_pend_usu_l)) {
	$arrDP = getDocPend($ccl_id);

	foreach ($f_pend_usu_l as $k => $v) {
		$pend_usu_id = $v;
		$pend_fec_ent = $f_pend_fec[$k];
		if (strlen($pend_fec_ent) == 10) { $pend_fec_ent = substr($pend_fec_ent, 6, 4)."-".substr($pend_fec_ent, 3, 2)."-".substr($pend_fec_ent, 0, 2); } // Cambia formato fecha a ISO
		$pend_conc = $f_pend_conc[$k];
		$pend_mont = $f_pend_mont[$k];

		if (!isset($f_pend_id[$k])) {
			$pend_anio = date('Y');
			$pend_nro = $nro_sgte2;

			$stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_docp (ccl_id, usu_id, cldp_anio, cldp_nro, cldp_reg_fec, cldp_ent_fec, cldp_conc, cldp_monto, est_id)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)") or die ($mysqli->error);
			$stmt->bind_param('iiiisssd',
				$id,
				$pend_usu_id,
				$pend_anio,
				$pend_nro,
				$ahora,
				$pend_fec_ent,
				$pend_conc,
				$pend_mont);
			$stmt->execute() or die ($mysqli->error);

			$nro_sgte2++;
		}
		else {
			if (!is_null($arrDP[substr($k, 1)][17]) || ( !is_null($arrDP[substr($k, 1)][13]) && !isset($f_pend_prdc_fec[$k]))) {
				// Si durante el registro se hicieron cambios en los documentos pendientes vencidos entra a esta parte
				// Si entra a grabar la liquidacion se cambia a borrador para que forzar que recalcule el total de nuevo al recargar la pagina de registro
				// No se modifica el registro del documento pendiente

				$oper = 'Borrador';
				$est_id = 1;
			}
			else {
				// Flujo normal
				if (isset($f_pend_cerr[$k])) {
					$pend_est = 2;
					$pend_com1 = $f_pend_com1[$k];
					$pend_doc = $f_pend_tipo_doc[$k];
					$pend_ser = strtoupper($f_ser_docref[$k]);
					$pend_nro = strtoupper($f_nro_docref[$k]);
				}
				else if (isset($f_pend_anul[$k])) {
					$pend_est = 3;
					$pend_com1 = $f_pend_com1[$k];
					$pend_doc = null;
					$pend_ser = null;
					$pend_nro = null;
				}
				else {
					$pend_est = 1;
					$pend_com1 = null;
					$pend_doc = null;
					$pend_ser = null;
					$pend_nro = null;
				}

				$stmt = $mysqli->prepare("UPDATE cajas_chicas_lote_docp SET usu_id=?, cldp_ent_fec=?, cldp_conc=?, cldp_monto=?, est_id=?, cldp_com1=?,
					ref_doc_id=?, ref_ser=?, ref_nro=? WHERE cldp_id=?") or die ($mysqli->error);
				$stmt->bind_param('issdisissi',
					$pend_usu_id,
					$pend_fec_ent,
					$pend_conc,
					$pend_mont,
					$pend_est,
					$pend_com1,
					$pend_doc,
					$pend_ser,
					$pend_nro,
					$f_pend_id[$k]);
				$stmt->execute() or die ($mysqli->error);
			}
		}
	}
}

$stmt = $mysqli->prepare("UPDATE cajas_chicas_lote SET ccl_monto_usado=?, ccl_ret=?, ccl_ret_no=?, ccl_det=?, ccl_det_no=?, est_id=?, ccl_act_fec=?, ccl_act_usu=?, ccl_gti=?, ccl_dg_json=?,
	ccl_pend=?, ccl_cuadre=?, ccl_banco=?,
	ccl_monto_usado_hist=?, ccl_ret_hist=?, ccl_ret_no_hist=?, ccl_det_hist=?, ccl_det_no_hist=? WHERE ccl_id=?") or die ($mysqli->error);
$stmt->bind_param('dddddisiisddddddddi',
	$tot_mon_liq,
	$tot_mon_ret,
	$tot_mon_ret_no,
	$tot_mon_det,
	$tot_mon_det_no,
	$est_id,
	$ahora,
	$usu_id,
	$f_lid_gti_def,
	$f_lid_dg_json_def,
	$tot_mon_pend,
	$tot_mon_cuadre,
	$tot_mon_banco,
	$tot_mon_liq,
	$tot_mon_ret,
	$tot_mon_ret_no,
	$tot_mon_det,
	$tot_mon_det_no,
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("DELETE FROM cajas_chicas_lote_det WHERE ccl_id=?") or die ($mysqli->error);
$stmt->bind_param('i',
	$id);
$stmt->execute() or die ($mysqli->error);

if ($est_id==2) {
	$stmt = $mysqli->prepare("DELETE FROM cajas_chicas_lote_det_hist WHERE ccl_id=? AND hist_id=1") or die ($mysqli->error);
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
		$lid_emp_asume = $f_afecto_inp[$k]+$f_noafecto_inp[$k];

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

		if ($doc_id==10 && $f_ser_doc[$k]==='0' && $f_num_doc[$k]==='0') {
			$lid_ser = date('Y');
			$lid_nro = $nro_sgte;
		}

		$stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_det (ccl_id, conc_id, doc_id, ruc_nro, lid_fec, lid_ser, lid_nro, lid_glo, mon_id,
			lid_afe, lid_mon_afe, lid_mon_naf, lid_tc, lid_retdet_apl, lid_retdet_tip, lid_retdet_mon, lid_gti, lid_dg_json, lid_cta_cont, lid_aprob, lid_emp_asume,
			veh_id, veh_km, lid_glo2)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?)") or die ($mysqli->error);
		$stmt->bind_param('iiisssssiidddiidissdiis',
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
			$lid_emp_asume,
			$veh_id,
			$veh_km,
			$lid_glo2);
		$stmt->execute() or die ($mysqli->error);

		if ($est_id==2) {
			$stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_det_hist (ccl_id, hist_id, conc_id, doc_id, ruc_nro, lid_fec, lid_ser, lid_nro, lid_glo, mon_id,
				lid_afe, lid_mon_afe, lid_mon_naf, lid_tc, lid_retdet_apl, lid_retdet_tip, lid_retdet_mon, lid_gti, lid_dg_json, lid_cta_cont, lid_aprob, lid_emp_asume,
				veh_id, veh_km, lid_glo2)
				VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?)") or die ($mysqli->error);
			$stmt->bind_param('iiisssssiidddiidissdiis',
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
				$lid_emp_asume,
				$veh_id,
				$veh_km,
				$lid_glo2);
			$stmt->execute() or die ($mysqli->error);
		}

		if ($doc_id==10 && $f_ser_doc[$k]==='0' && $f_num_doc[$k]==='0') {
			$nro_sgte++;
		}
	}
}

// Distribucion de gastos de planilla de movilidad se guarda aparte en su propia tabla
if (isset($f_pm_id)) {
	foreach ($f_pm_id as $k => $v) {
		$pla_gti = $f_gti_id[$k];
		$pla_dg_json = $f_dist_gast_json[$k];
		$pla_id = $v;

		$stmt = $mysqli->prepare("UPDATE pla_mov SET pla_gti=?, pla_dg_json=? WHERE pla_id=?") or die ($mysqli->error);
		$stmt->bind_param('isi',
			$pla_gti,
			$pla_dg_json,
			$pla_id);
		$stmt->execute() or die ($mysqli->error);
	}
}

// Cuadre de efectivo se guarda aparte en su propia tabla
foreach ($f_cuadre_tipo as $k => $v) {
	$cant = $f_cuadre_cant[$k];

	if (!isset($f_cuadre_id[$k])) {
		$tipo = $v;
		$deno = $f_cuadre_deno[$k];

		$stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_cuadre (ccl_id, tipo_id, cclc_deno, cclc_cant) VALUES (?, ?, ?, ?)") or die ($mysqli->error);
		$stmt->bind_param('iidi',
			$id,
			$tipo,
			$deno,
			$cant);
		$stmt->execute() or die ($mysqli->error);
	}
	else {
		$cuadre_id = $f_cuadre_id[$k];

		$stmt = $mysqli->prepare("UPDATE cajas_chicas_lote_cuadre SET cclc_cant=? WHERE cclc_id=?") or die ($mysqli->error);
		$stmt->bind_param('ii',
			$cant,
			$cuadre_id);
		$stmt->execute() or die ($mysqli->error);
	}
}

if ($est_id == 2) {
	$stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_act VALUES (?, 2, ?, ?, null)") or die ($mysqli->error);
	$stmt->bind_param('iis', $id, $_SESSION['rec_usu_id'], $ahora);
	$stmt->execute() or die ($mysqli->error);

	$stmt = $mysqli->prepare("UPDATE cajas_chicas_lote SET ccl_cie_fec=?, ccl_cie_usu=? WHERE ccl_id=?") or die ($mysqli->error);
	$stmt->bind_param('sii', $ahora, $_SESSION['rec_usu_id'], $id);
	$stmt->execute() or die ($mysqli->error);

	// $stmt = $mysqli->prepare("UPDATE pla_mov SET pla_env_fec=?, est_id=3 WHERE pla_id=?") or die ($mysqli->error);
	// $stmt->bind_param('si',	$ahora,	$pla_id);
	// $stmt->execute() or die ($mysqli->error);
}
else {
	$stmt = $mysqli->prepare("INSERT INTO cajas_chicas_lote_act VALUES (?, 61, ?, ?, null)") or die ($mysqli->error);
	$stmt->bind_param('iis', $id, $_SESSION['rec_usu_id'], $ahora);
	$stmt->execute() or die ($mysqli->error);
}

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = $oper." lote Caja Chica (".$id.") por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

if ($est_id == 2) {
	// Adjuntar pdf si es que se cierra la caja chica
	$attachString = getCartaCajaChicaLote($id, 'S');
	if (is_null($attachString)) die("Error en la generaci�n del archivo PDF, no se complet� la transacci�n. (Cadena vac�a)");
	$attachFilename = __DIR__ ."CCH_".str_replace("/", "_", $ccl_numero).".pdf";

	$to = getCorreoUsuario($usu_id);
	$cc = array();
	array_push ($cc, getCorreoUsuario(getUsuController()));
	array_push ($cc, getCorreoUsuario(getUsuAdmin()));
	array_push ($cc, 'mngmt@Minapp.com.pe');
	$subject = "Registro de Lote de CAJA CHICA $ccl_numero";
	$body = "Se ha concluido el registro del lote de la CAJA CHICA $ccl_numero de $cch_nombre realizado por ".getNombreTrabajador(getCodigoGeneral(getUsuAd($usu_id))).".";
	$body .= "\n\nEsperando la aprobaci�n del Responsable.";
	//enviarCorreo($to, $cc, $subject, $body, $attachString, $attachFilename);
	ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident,"info",$attachString,$attachFilename);

	$to = null;
	$cc = array();
	$arrResp = getResponsablesCaja($cch_id);
	foreach ($arrResp as $v) {
		array_push ($cc, getCorreoUsuario($v));
	}
	array_push ($cc, 'mngmt@Minapp.com.pe');
	$subject = "Registro de Lote de CAJA CHICA $ccl_numero";
	$body = "Se ha concluido el registro del lote de la CAJA CHICA $ccl_numero de $cch_nombre realizado por ".getNombreTrabajador(getCodigoGeneral(getUsuAd($usu_id))).".";
	$body .= "\n\nFavor de ingresar al modulo Administracion de la web intranet, opcion Aprobacion de Lotes de Caja Chica y realizar los ajustes necesarios y la aprobacion respectiva.";
	//enviarCorreo($to, $cc, $subject, $body, $attachString, $attachFilename);
	$ventana   = "admin/cch_lote_aprob.php";
	$aprobador = $cc;
	$detalle   = ConstructorMail::armarDetalle($cc, $subject, $body, $attachString, $attachFilename);
	ConstructorMail::enviarCorreoInfoConLink($id, $aprobador, $ventana, $var_modulo_ident, $detalle);
}

include 'datos_cerrar_bd.php';

header("Location: cch_lote_reg_res.php?id=$id&o=$oper");
exit;
?>
