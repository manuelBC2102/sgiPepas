<?php

header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'parametros.php';
//include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";
//var_dump($_REQUEST);
//exit();
//$isDua = ($_SESSION['rec_usu_id'] == $pAXISADUANA || $_SESSION['rec_usu_id'] == $pAXISGLOBAL);
$contadorPerfil = obtenerPerfilContador($pPERFIL_PROVEEDOR_DUA, $_SESSION['rec_usu_id']);
$isDua = ($contadorPerfil > 0);

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
    echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
    exit;
} else {
    $id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
        $ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
        $usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
        $ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
        $ear_liq_gast_asum, $pla_id, $ear_act_obs1) = getSolicitudInfo($id);

if ($est_id <> 4) {
    echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud, ya ha sido enviada.</b></font><br>";
    exit;
}

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'JEFEOGERENTE');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pGERENTE);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pASISTENTE_ADMINISTRATIVO);
if ($usu_id != $_SESSION['rec_usu_persona_id'] && $count == 0) {
    echo "<font color='red'><b>ERROR: No se puede acceder a la informaci&oacute;n de la liquidaci&oacute;n</b></font><br>";
    exit;
}

$oper = '';
if (isset($f_grabar)) {
    $oper = 'Grabacion';
    $est_id = 4;
} else if (isset($f_enviar)) {
    $oper = 'Envio';
    $est_id = 5;
} else {
    echo "<font color='red'><b>ERROR: Operaci&oacute;n err&oacute;nea</b></font><br>";
    exit;
}

$tot_mon_liq = abs((float) filter_var($f_tot_mon_liq, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$tot_mon_ret = abs((float) filter_var($f_tot_mon_ret, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$tot_mon_ret_no = abs((float) filter_var($f_tot_mon_ret_no, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$tot_mon_det = abs((float) filter_var($f_tot_mon_det, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$tot_mon_det_no = abs((float) filter_var($f_tot_mon_det_no, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
$resul_inp = (float) filter_var($f_resul_inp, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); // Monto a descontar
$fecha_liquidacion = $f_fecha_liquidacion;
$tipoCambioLiq = (float) filter_var($f_txtTipoCambioLiq, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);


include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die($mysqli->error);
$fila = $result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET ear_liq_mon=?, ear_liq_ret=?, ear_liq_ret_no=?, ear_liq_det=?, ear_liq_det_no=?, ear_liq_dcto=?, est_id=?, ear_act_fec=?, ear_act_usu=? WHERE ear_id=?") or die($mysqli->error);
$stmt->bind_param('ddddddisii', $tot_mon_liq, $tot_mon_ret, $tot_mon_ret_no, $tot_mon_det, $tot_mon_det_no, $resul_inp, $est_id, $ahora, $_SESSION['rec_usu_id'], $id);
$stmt->execute() or die($mysqli->error);

$query = "SELECT COUNT(*) FROM ear_dist_gast WHERE ear_id=$id";
$result = $mysqli->query($query) or die($mysqli->error);
$fila = $result->fetch_array();
$dist_gast_count = $fila[0];

if ($dist_gast_count == 0) {
    $stmt = $mysqli->prepare("INSERT INTO ear_dist_gast (ear_id, lid_gti, lid_dg_json) VALUES (?, ?, ?)") or die($mysqli->error);
    $stmt->bind_param('iis', $id, $f_lid_gti_def, $f_lid_dg_json_def);
    $stmt->execute() or die($mysqli->error);
} else {
    $stmt = $mysqli->prepare("UPDATE ear_dist_gast SET lid_gti=?, lid_dg_json=? WHERE ear_id=?") or die($mysqli->error);
    $stmt->bind_param('isi', $f_lid_gti_def, $f_lid_dg_json_def, $id);
    $stmt->execute() or die($mysqli->error);
}

$stmt = $mysqli->prepare("DELETE dc FROM cont_distribucion_contable dc inner join ear_liq_detalle ed on ed.eld_id = dc.eld_id where ed.ear_id=?") or die($mysqli->error);
$stmt->bind_param('i', $id);
$stmt->execute() or die($mysqli->error);

$stmt = $mysqli->prepare("DELETE FROM ear_liq_detalle WHERE ear_id=?") or die($mysqli->error);
$stmt->bind_param('i', $id);
$stmt->execute() or die($mysqli->error);

//$stmt = $mysqli->prepare("DELETE FROM ear_documento_relacion WHERE ear_id=?") or die($mysqli->error);
//$stmt->bind_param('i', $id);
//$stmt->execute() or die($mysqli->error);

if ($est_id == 5) {
    $stmt = $mysqli->prepare("DELETE FROM ear_liq_detalle_hist WHERE ear_id=? AND hist_id=5") or die($mysqli->error);
    $stmt->bind_param('i', $id);
    $stmt->execute() or die($mysqli->error);
}

if (isset($f_conc_l)) {
    foreach ($f_conc_l as $k => $v) {
      $monto_igv = intval($f_monto_igv[$k]) / 100;
        $conc_id = $f_conc_id[$k];
        $doc_id = $f_tipo_doc[$k];
        $orden_trabajo_id = $f_lid_orden_trabajo[$k];
        $ruc_nro = $f_ruc_nro[$k];
        $lid_fec = $f_fec_doc[$k];
        if (strlen($lid_fec) == 10) {
            $lid_fec = substr($lid_fec, 6, 4) . "-" . substr($lid_fec, 3, 2) . "-" . substr($lid_fec, 0, 2);
        } // Cambia formato fecha a ISO
        $lid_ser = $f_ser_doc[$k];
        $lid_nro = $f_num_doc[$k];
        $lid_glo = $f_det_doc[$k];
        $mon_id = $f_tipo_mon[$k]; // la moneda en el formulario esta habilitado
        $lid_afe = $f_afecto_sel[$k];
        $lid_mon_afe = $f_afecto_inp[$k];
        $lid_mon_naf = $f_noafecto_inp[$k];
        $lid_mon_otro = $f_montOtro_inp[$k];
        $lid_mon_icbp = $f_montIcbp_inp[$k];
        $lid_tc = $f_tc_inp[$k];
        $lid_retdet_apl = $f_aplic_retdet[$k];
        $lid_retdet_tip = $f_retdet_tip[$k];
        $lid_retdet_mon = $f_retdet_monto[$k];
        $lid_gti = $f_lid_gti_def; //$f_gti_id[$k];
        $lid_dg_json = $f_lid_dg_json_def; //$f_dist_gast_json[$k];
//        $bandera =$f_bandera[$k];
        $documento_relacionado_id = $f_documento_relacion[$k];
        $lid_cta_cont = $v;
        $lid_emp_asume = $f_afecto_inp[$k] + $f_noafecto_inp[$k] + $f_montOtro_inp[$k]+$f_montIcbp_inp[$k];

        $veh_id = null;
        $veh_km = null;
        $lid_glo2 = null;
        if ($f_cve[$k] == 1) {
            if ($f_veh_l[$k] != -1) {
                $veh_id = $f_veh_l[$k];
                $veh_km = $f_km[$k];

                list($veh_id_2, $veh_placa) = getVehiculosInfo($veh_id);

                $lid_glo = $veh_placa . ' - ' . $veh_km . ' KM';
            }
        } else if ($f_cve[$k] == 2) {
            $lid_glo2 = $f_peaje[$k];

            if ($f_veh_l[$k] != -1) {
                $veh_id = $f_veh_l[$k];
            }
        } else if ($f_cve[$k] == 3) {
            if ($f_veh_l[$k] != -1) {
                $veh_id = $f_veh_l[$k];
            }
        }

        $stmt = $mysqli->prepare("INSERT INTO ear_liq_detalle (ear_id, conc_id, doc_id, ruc_nro, lid_fec, lid_ser, lid_nro, lid_glo, mon_id,
			lid_afe, lid_mon_afe, lid_mon_naf, lid_tc, lid_retdet_apl, lid_retdet_tip, lid_retdet_mon, lid_gti, lid_dg_json, lid_cta_cont, lid_aprob, lid_emp_asume,
			veh_id, veh_km, lid_glo2,lid_mon_otro, documento_relacionado_id, lid_mon_icbp, monto_igv, orden_trabajo_id)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?)") or die($mysqli->error);
        $stmt->bind_param('iiisssssiidddiidissdiisdsddi', $id, $conc_id, $doc_id, $ruc_nro, $lid_fec, $lid_ser, $lid_nro, $lid_glo, $mon_id, $lid_afe, $lid_mon_afe, $lid_mon_naf, $lid_tc, $lid_retdet_apl, $lid_retdet_tip, $lid_retdet_mon, $lid_gti, $lid_dg_json, $lid_cta_cont, $lid_emp_asume, $veh_id, $veh_km, $lid_glo2, $lid_mon_otro, $documento_relacionado_id,$lid_mon_icbp, $monto_igv, $orden_trabajo_id);
        $stmt->execute() or die($mysqli->error);

        $idDetalleLiquidacion =  $mysqli->insert_id;
        $idAnterior =$mysqli->insert_id;

        foreach (json_decode($f_lid_distribucion[$k]) as $index => $item) {
                $stmt = $mysqli->prepare("INSERT INTO cont_distribucion_contable(eld_id,plan_contable_id,centro_costo_id,porcentaje,monto,usuario_creacion) VALUES (?,?, ?, ?, ?, ?)") or die($mysqli->error);
                $stmt->bind_param('iiiddi',$idDetalleLiquidacion,$item->cuenta_contable,$item->centro_costo,$item->porcentaje,$item->monto, $_SESSION['rec_usu_id']);
                $stmt->execute() or die($mysqli->error);
        }

        if ($est_id == 5) {
            $stmt = $mysqli->prepare("INSERT INTO ear_liq_detalle_hist (ear_id, hist_id, conc_id, doc_id, ruc_nro, lid_fec, lid_ser, lid_nro, lid_glo, mon_id,
				lid_afe, lid_mon_afe, lid_mon_naf, lid_tc, lid_retdet_apl, lid_retdet_tip, lid_retdet_mon, lid_gti, lid_dg_json, lid_cta_cont, lid_aprob, lid_emp_asume,
				veh_id, veh_km, lid_glo2, lid_mon_otro,documento_relacionado_id,lid_mon_icbp, orden_trabajo_id)
				VALUES (?, 5, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?)") or die($mysqli->error);
            $stmt->bind_param('iiisssssiidddiidissdiisdsdi', $id, $conc_id, $doc_id, $ruc_nro, $lid_fec, $lid_ser, $lid_nro, $lid_glo, $mon_id, $lid_afe, $lid_mon_afe, $lid_mon_naf, $lid_tc, $lid_retdet_apl, $lid_retdet_tip, $lid_retdet_mon, $lid_gti, $lid_dg_json, $lid_cta_cont, $lid_emp_asume, $veh_id, $veh_km, $lid_glo2, $lid_mon_otro,$documento_relacionado_id,$lid_mon_icbp, $orden_trabajo_id);
            $stmt->execute() or die($mysqli->error);
        }

    }
}

// Distribucion de gastos de planilla de movilidad se guarda aparte en su propia tabla
if (isset($f_gti_id['Splamov'])) {
    $pla_gti = $f_gti_id['Splamov'];
    $pla_dg_json = $f_dist_gast_json['Splamov'];
    $pla_id = $f_pm_id;

    $stmt = $mysqli->prepare("UPDATE pla_mov SET pla_gti=?, pla_dg_json=? WHERE pla_id=?") or die($mysqli->error);
    $stmt->bind_param('isi', $pla_gti, $pla_dg_json, $pla_id);
    $stmt->execute() or die($mysqli->error);
}

if ($est_id == 5) {
    if ($usu_id <> $_SESSION['rec_usu_id']) {
        $stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 5, ?, ?, 'Registro por otro usuario')") or die($mysqli->error);
        $stmt->bind_param('iis', $id, $_SESSION['rec_usu_id'], $ahora);
        $stmt->execute() or die($mysqli->error);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 5, ?, ?, null)") or die($mysqli->error);
        $stmt->bind_param('iis', $id, $_SESSION['rec_usu_id'], $ahora);
        $stmt->execute() or die($mysqli->error);
    }

    $stmt = $mysqli->prepare("UPDATE ear_solicitudes SET ear_act_usu=? WHERE ear_id=?") or die($mysqli->error);
    $stmt->bind_param('ii', $_SESSION['rec_usu_id'], $id);
    $stmt->execute() or die($mysqli->error);

    $orden_trabajo = $f_lid_orden_trabajo['Splamov'];
	if(isset($orden_trabajo)){
        $stmt = $mysqli->prepare("UPDATE pla_mov SET pla_env_fec=?, est_id=3,orden_trabajo_id=? WHERE pla_id=?") or die($mysqli->error);
        $stmt->bind_param('sii', $ahora, $orden_trabajo, $pla_id);
        $stmt->execute() or die($mysqli->error);
    }
}
if ($est_id == 4) {
    $orden_trabajo = $f_lid_orden_trabajo['Splamov'];
    if(isset($orden_trabajo)){
        $stmt = $mysqli->prepare("UPDATE pla_mov SET orden_trabajo_id=? WHERE pla_id=?") or die($mysqli->error);
        $stmt->bind_param('ii', $orden_trabajo, $pla_id);
        $stmt->execute() or die($mysqli->error);
    }
}
//if ($f_dua*1 == -1){
//    $f_dua = null;
//}
//$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET dua_id=? WHERE ear_id=?") or die ($mysqli->error);
//$stmt->bind_param('ii',
//	$f_dua,
//	$id);
//$stmt->execute() or die ($mysqli->error);

if ($isDua) {
    $stmt = $mysqli->prepare("UPDATE ear_solicitudes SET dua_id=null,dua_serie=?,dua_numero=? WHERE ear_id=?") or die($mysqli->error);
    $stmt->bind_param('ssi', $f_dua_serie, $f_dua_numero, $id);
    $stmt->execute() or die($mysqli->error);
}

//REGISTRO TIPO DE CAMBIO (YA NO SE REGISTRA)
//if ($isDua) {
//    $fechaLiqArray = explode('/', $fecha_liquidacion);
//    $fecha_liquidacion_form = $fechaLiqArray[2] . '-' . $fechaLiqArray[1] . '-' . $fechaLiqArray[0];
//
//    $guardarTipoCambioSGI=0;
//    if($f_chkTipoCambio){
//        $guardarTipoCambioSGI=1;
//    }
//
//    $stmt = $mysqli->prepare("UPDATE ear_solicitudes SET ear_liq_fec=?,tipo_cambio=?,guardar_tc_sgi=? WHERE ear_id=?") or die($mysqli->error);
//    $stmt->bind_param('sdii', $fecha_liquidacion_form, $tipoCambioLiq,$guardarTipoCambioSGI, $id);
//    $stmt->execute() or die($mysqli->error);
//}

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die($mysqli->error);
$desc = $oper . " liquidacion EAR (" . $id . ") de " . $_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die($mysqli->error);
$stmt->close();

$mysqli->commit();

if ($est_id == 5) {
    if ($usu_id <> $_SESSION['rec_usu_id']) {
//		$msg_adic = " Realizado por ".getNombreTrabajador($_SESSION['rec_codigogeneral_id']).".";
        $msg_adic = " Realizado por " . getUsuarioNombre($_SESSION['rec_usu_id']) . ".";
    } else {
        $msg_adic = "";
    }

    $to = getCorreoUsuario($usu_id);

//	if ($usu_id <> $_SESSION['rec_usu_id']) array_push ($cc, getCorreoUsuario($_SESSION['rec_usu_id']));
    $subject = "Registro de Liquidacion de EAR $ear_numero de " . $ear_tra_nombres;
    $body = "Se ha registrado la liquidacion del EAR $ear_numero de $ear_tra_nombres." . $msg_adic;
    $body .= "\n\nEsperando la aprobaci&oacute;n del Jefe.";
    //enviarCorreo($to, $cc, $subject, $body);
//	ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident);
    //-------registro email en sgi-------
    list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
    $plantillaCuerpo = str_replace("[|asunto|]", 'Registro de Liquidaci&oacute;n de EAR', $plantillaCuerpo);
    $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

    //insertar en email_envio de sgi
    $emailEnvioId = insertarEmailEnvioSGI($to, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);

    //---------------------------------
    list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
    $cc = array();
    array_push($cc, getCorreoUsuario($usu_id_jefe));
    $cc = obtenerCorreosPerfilSGI($pADMINIST, $cc);
    $cc = obtenerCorreosPerfilSGI($pGERENTE, $cc);

    $correo = '';
    $cc = array_unique($cc);
    foreach ($cc as $index => $valor) {
        $correo = $correo . $valor . ';';
    }
    //--------------------------------------------

    $subject = "Registro de Liquidacion de EAR $ear_numero de " . $ear_tra_nombres;
    $body = "Se ha registrado la liquidacion del EAR $ear_numero de $ear_tra_nombres." . $msg_adic;
    $body .= "\n\nFavor de ingresar al modulo Entregas a rendir de la web, opci&oacute;n Aprobaci&oacute;n de liquidaciones y realizar los ajustes necesarios y la aprobacion respectiva.";

    //-------registro email en sgi-------
    list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
    $plantillaCuerpo = str_replace("[|asunto|]", 'Registro de Liquidaci&oacute;n de EAR', $plantillaCuerpo);
    $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

    //insertar en email_envio de sgi
    $emailEnvioId = insertarEmailEnvioSGI($correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);
    //---------------------------------
}

include 'datos_cerrar_bd.php';


header("Location: ear_liq_registro_res.php?id=$id&o=$oper");
exit;
?>
