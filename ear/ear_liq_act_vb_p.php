<?php
header('Content-Type: text/html; charset=utf-8mb4');
include ("seguridad.php");
include 'func.php';
include 'reportesPDF.php';
include 'parametros.php';
require_once('../' . $carpetaSGI . '/controlador/almacen/EarControlador.php');

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
	$master_usu_id, $a,$comodin1, $comodin2, $dua_id,$tipoCambioFechaLiq,$guardarTcSgi,$periodo_id,
        $dua_serie,$dua_numero) = getSolicitudInfo($id);

//$isDua = ($usu_id == $pAXISADUANA || $usu_id == $pAXISGLOBAL);
$contadorPerfil=obtenerPerfilContador($pPERFIL_PROVEEDOR_DUA,$usu_id);
$isDua=($contadorPerfil>0);

if ($est_id <> 6) {//antes 8
	echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud</b></font><br>";
	exit;
}

// Se asigna nuevo estado segun el monto del descuento:
if ($ear_liq_dcto == 0) {
	$est_id = 12;
}
else {
	$est_id = 9;
}

//TRANSACCION
$stmt = $mysqli->prepare("SET AUTOCOMMIT=0") or die ($mysqli->error);
$stmt->execute() or die ($mysqli->error);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];
$ahoramenosuno = date('Y-m-d H:i:s', strtotime($ahora." - 1 second"));

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=?, ear_act_fec=?, ear_act_usu=? WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('isii',
	$est_id,
	$ahora,
	$_SESSION['rec_usu_id'],
	$id);
$stmt->execute() or die ($mysqli->error);

if ($est_id == 9) {
	$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, ?, ?, ?, null)") or die ($mysqli->error);
	$stmt->bind_param('iiis', $id, $est_id, $_SESSION['rec_usu_id'], $ahora);
	$stmt->execute() or die ($mysqli->error);
}
else if ($est_id == 12) {
	$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 9, ?, ?, null)") or die ($mysqli->error);
	$stmt->bind_param('iis', $id, $_SESSION['rec_usu_id'], $ahoramenosuno);
	$stmt->execute() or die ($mysqli->error);

	$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, ?, ?, ?, null)") or die ($mysqli->error);
	$stmt->bind_param('iiis', $id, $est_id, $_SESSION['rec_usu_id'], $ahora);
	$stmt->execute() or die ($mysqli->error);
}

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Liquidacion EAR con VB de Analista de Cuentas (".$id.") hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);

if ($ear_liq_dcto == 0) {
	$to = getCorreoUsuario($usu_id);
	$cc = array();

        $cc=obtenerCorreosPerfilSGI($pSUP_CONT,$cc);
        $cc=obtenerCorreosPerfilSGI($pADMINIST,$cc);
	if(!is_null($master_usu_id)) array_push ($cc, getCorreoUsuario($master_usu_id));

        $correo=$to.';';
        $cc = array_unique($cc);
        foreach ($cc as $index => $valor) {
            $correo=$correo.$valor.';';
        }

	$subject = "Visto Bueno de Analista de Cuentas al EAR $ear_numero de ".$ear_tra_nombres." (Sin pendientes)";
	$body = "El Analista de Cuentas ha dado Visto Bueno al EAR $ear_numero de $ear_tra_nombres.";
	$body .= "\n\nEste EAR ya no tiene operaciones pendientes. Ha llegado a su etapa final. Diferencia cero.";

        //-------registro email en sgi-------
        list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
        $plantillaCuerpo = str_replace("[|asunto|]", 'Visto Bueno de Analista de Cuentas al EAR', $plantillaCuerpo);
        $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

        $emailEnvioId = insertarEmailEnvioSGIConeccion($mysqli,$correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);
        //---------------------------------
}
else if ($ear_liq_dcto < 0) {
	$dia = date('N', strtotime($ahora));
	if ($dia == 4) {
		$viernes = date('d/m/Y', strtotime('next friday', strtotime($ahora.' + 1 day')));
	}
	else {
		$viernes = date('d/m/Y', strtotime('next friday', strtotime($ahora)));
	}

	$to = getCorreoUsuario($usu_id);
	$cc = array();
        $cc=obtenerCorreosPerfilSGI($pSUP_CONT,$cc);
        $cc=obtenerCorreosPerfilSGI($pADMINIST,$cc);
	if(!is_null($master_usu_id)) array_push ($cc, getCorreoUsuario($master_usu_id));

        $correo=$to.';';
        $cc = array_unique($cc);
        foreach ($cc as $index => $valor) {
            $correo=$correo.$valor.';';
        }
	$subject = "Visto Bueno de Analista de Cuentas al EAR $ear_numero de ".$ear_tra_nombres." (Reembolso pendiente)";
	$body = "El Analista de Cuentas ha dado Visto Bueno al EAR $ear_numero de $ear_tra_nombres.";
	$body .= "\n\nEste EAR tiene un reembolso pendiente de ".conComas($ear_liq_dcto*-1)." $mon_nom ";
                //. "el cual sera realizado por Tesoreria aproximadamente el dia viernes $viernes.";

        //-------registro email en sgi-------
        list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
        $plantillaCuerpo = str_replace("[|asunto|]", 'Visto Bueno de Analista de Cuentas al EAR', $plantillaCuerpo);
        $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

        $emailEnvioId = insertarEmailEnvioSGIConeccion($mysqli,$correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);

	// Mandar correo y adjuntar pdf para Tesoreria
        $hoy = date("Y_m_d_H_i_s");
        $nombreDocumento = "LGS_".str_replace("/", "_", $ear_numero);
        $ext = ".pdf";
        $nombreArchivo=$nombreDocumento.$ext;

        $rutaArchivo = __DIR__ . "/Documentos/" . $nombreDocumento ."_". $hoy . $ext;

        $resPDF = getCartaEarLiq($id, 'F',$rutaArchivo);
        //--------------
        $to=array();
        $to=obtenerCorreosPerfilSGI($pTESO,$to);
        $correo='';
        $to = array_unique($to);
        foreach ($to as $index => $valor) {
            $correo=$correo.$valor.';';
        }

	$subject = "Visto Bueno de Analista de Cuentas al EAR $ear_numero de ".$ear_tra_nombres." (Reembolso pendiente)";
	$body = "El Analista de Cuentas ha dado Visto Bueno al EAR $ear_numero de $ear_tra_nombres.";
	$body .= "\n\nEste EAR tiene un reembolso pendiente de ".conComas($ear_liq_dcto*-1)." $mon_nom ";
                //. "el cual sera realizado por Tesoreria aproximadamente el dia viernes $viernes.";
	$body .= "\n\nSe ha adjuntado a este correo el PDF de la liquidacion para su revision.";
	//$body .= "\n\nEl usuario de Tesoreria debe ingresar al modulo Entregas a Rendir de la web, y efectuar la actualizacion correspondiente a esta liquidacion.";

        //-------registro email en sgi-------
        list($plantillaDestinatario,$plantillaAsunto,$plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
        $plantillaCuerpo = str_replace("[|asunto|]", 'Visto Bueno de Analista de Cuentas al EAR', $plantillaCuerpo);
        $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

        $emailEnvioId = insertarEmailEnvioSGIConeccion($mysqli,$correo,$subject,$plantillaCuerpo,$_SESSION['rec_usu_id'],$rutaArchivo,$nombreArchivo);
}
else {
	$anio = date('Y', strtotime($ahora));
	$mes = date('m', strtotime($ahora));

	if ($fecha_tope=='') {
		$msj = "probablemente este fin de mes.";
	}

	$to = getCorreoUsuario($usu_id);
	$cc = array();
        $cc=obtenerCorreosPerfilSGI($pSUP_CONT,$cc);
        $cc=obtenerCorreosPerfilSGI($pADMINIST,$cc);
	if(!is_null($master_usu_id)) array_push ($cc, getCorreoUsuario($master_usu_id));

        $correo=$to.';';
        $cc = array_unique($cc);
        foreach ($cc as $index => $valor) {
            $correo=$correo.$valor.';';
        }

	$subject = "Visto Bueno de Analista de Cuentas al EAR $ear_numero de ".$ear_tra_nombres." (Devoluci&oacute;n pendiente)";
	$body = "El Analista de Cuentas ha dado Visto Bueno al EAR $ear_numero de $ear_tra_nombres.";
	$body .= "\nEste EAR tiene una devoluci&oacute;n pendiente de ".conComas($ear_liq_dcto)." $mon_nom ";
//                . "el cual sera realizado por Tesorer&iacute;a ".$msj;

        //-------registro email en sgi-------
        list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
        $plantillaCuerpo = str_replace("[|asunto|]", 'Visto Bueno de Analista de Cuentas al EAR', $plantillaCuerpo);
        $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

        $emailEnvioId = insertarEmailEnvioSGIConeccion($mysqli,$correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);
        //---------------------------------

	// Mandar correo y adjuntar pdf para Compensaciones
        $hoy = date("Y_m_d_H_i_s");
        $nombreDocumento = "LGS_".str_replace("/", "_", $ear_numero);
        $ext = ".pdf";
        $nombreArchivo=$nombreDocumento.$ext;

        $rutaArchivo = __DIR__ . "/Documentos/" . $nombreDocumento ."_". $hoy . $ext;

        $resPDF = getCartaEarLiq($id, 'F',$rutaArchivo);
        //--------------

//	$to = getCorreoUsuario(getUsuCompensaciones());
        $to=array();
        $to=obtenerCorreosPerfilSGI($pCOMP,$to);
        $correo='';
        $to = array_unique($to);
        foreach ($to as $index => $valor) {
            $correo=$correo.$valor.';';
        }

	$subject = "Visto Bueno de Analista de Cuentas al EAR $ear_numero de ".$ear_tra_nombres." (Devolucion pendiente)";
	$body = "El Analista de Cuentas ha dado Visto Bueno al EAR $ear_numero de $ear_tra_nombres.";
	$body .= "\nEste EAR tiene una devoluci&oacute;n pendiente de ".conComas($ear_liq_dcto)." $mon_nom.";
//                . "el cual sera realizado por Tesorer&iacute;a ".$msj;
	$body .= "\n\nSe ha adjuntado a este correo el PDF de la liquidacion para su revisi&oacute;n.";
//	$body .= "\n\nEl usuario de Tesorer&iacute;a debe ingresar al modulo Entregas a Rendir de la web, y efectuar la actualizaci&oacute;n correspondiente a esta liquidaci&oacute;n.";

         //-------registro email en sgi-------
        list($plantillaDestinatario,$plantillaAsunto,$plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
        $plantillaCuerpo = str_replace("[|asunto|]", 'Visto Bueno de Analista de Cuentas al EAR', $plantillaCuerpo);
        $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

        $emailEnvioId = insertarEmailEnvioSGIConeccion($mysqli,$correo,$subject,$plantillaCuerpo,$_SESSION['rec_usu_id'],$rutaArchivo,$nombreArchivo);
}

//ACTUALIZAR REEMBOLSO EN EAR
if ($est_id == 9) {
    IF($ear_liq_dcto < 0){
        $est_id = 10; // Reembolso
        $msj_mov = "Reembolso efectuado";
    }else if ($ear_liq_dcto > 0){
        $est_id = 11; // Descuento
	$msj_mov = "Devolucion efectuada";
    }

    $query = "SELECT now()";
    $result = $mysqli->query($query) or die($mysqli->error);
    $fila = $result->fetch_array();
    $ahora = $fila[0];

    $stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=?, ear_act_fec=?, ear_act_usu=? WHERE ear_id=?") or die($mysqli->error);
    $stmt->bind_param('isii', $est_id, $ahora, $_SESSION['rec_usu_id'], $id);
    $stmt->execute() or die($mysqli->error);

    $stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, ?, ?, ?, ?)") or die($mysqli->error);
    $stmt->bind_param('iiiss', $id, $est_id, $_SESSION['rec_usu_id'], $ahora, $msj_mov);
    $stmt->execute() or die($mysqli->error);

    $stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die($mysqli->error);
    $desc = "Liquidacion EAR $msj_mov (" . $id . ") hecha por " . $_SESSION['rec_usu_nombre'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $host = gethostbyaddr($ip);
    $stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

    $stmt->execute() or die($mysqli->error);
}


// ------------------------- REGISTRAR LOS DOCUMENTOS EN SGI ------------------------------
$listaEarSolicitud = earSolicitudObtenerXEarId($id);
$documentoIdPago = $listaEarSolicitud[0]['sgi_documento_id'];

$parametros->documentoTipoIdReembolsoSGI = $documentoTipoIdReembolsoSGI;
$parametros->documentoTipoIdDevolucionSGI = $documentoTipoIdDevolucionSGI;
$parametros->documentoTipoIdDevolucionSisSGI = $documentoTipoIdDevolucionSisSGI;
$parametros->earNumero=$ear_numero;
$parametros->earLiqDcto = $ear_liq_dcto;//PARA REEMBOLSO o DEVOLUCION
$parametros->usuId = $usu_id;
$parametros->usuarioId = $_SESSION['rec_usu_id'];
$parametros->isDua = $isDua;
$parametros->duaId = $dua_id;
$parametros->duaSerie = $dua_serie;
$parametros->duaNumero = $dua_numero;
$parametros->monedaId = ($mon_iso != 'PEN' ? 4 : 2); //MONEDA ID EN SGI
$parametros->documentoIdPago = $documentoIdPago;
$parametros->periodoIdEar = $periodo_id;
$parametros->fechaLiquidacion = getFechaEnvioLiq($id);

// SI HAY UN DESEMBOLSO REGISTRAMOS LOS DOCUMENTOS DE LA LIQUIDACION.
if ($documentoIdPago != null && $documentoIdPago != '') {
    //OBTENEMOS LAS LIQUIDACIONES
    if ($isDua && $dua_id != null && $dua_id != '') {
      $arrLiqDet = getLiqDetalleAsumidos($id); // PARA OBTENER TODAS LAS LIQUIDACIONES SIN AGRUPAR, POR LOS RECIBOS DE GASTO
      // $arrLiqDet = getLiqDetalle($id); // PARA OBTENER TODAS LAS LIQUIDACIONES SIN AGRUPAR, POR LOS RECIBOS DE GASTO
    } else {
        $arrLiqDet = getLiqDetalleGrupo($id);
    }
    $parametros->arrLiqDet = $arrLiqDet;
    $parametros->distribucionContable = getDistribucionDetalle($id);

    //OBTENEMOS EL DOC DE PLANILLA SI HUBIERA
    if (!is_null($pla_id)) {
        list($pla_numero, $est_id_2, $pla_reg_fec, $ear_numero_2, $tope_maximo, $usu_id_2, $ear_id,
                $est_nom_2, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec,
                $pla_exc, $pla_com1, $pla_com2, $pla_com3) = getPlanillaMovilidadInfo($pla_id);

        //SE OBTIENE EL ID DEL DOCUMENTO TIPO SGI DE LA PLANILLA MOVILIDAD
        $documentoTipoIdSGI = $documentoTipoIdPlanillaSGI;
        $parametrosPlanilla->documentoTipoIdSgi = $documentoTipoIdSGI;

        //------------------- OBTENIENDO LOS PARAMETROS DE LA PLANILLA -------------------------
        $parametrosPlanilla->usuarioPersona = $usu_id;
        $valores->persona = null;
        $valores->numero = 'auto';
        $valores->serie = 'defecto';
        $valores->fechaEmision = $pla_reg_fec;
        $valores->fechaVencimiento = $pla_env_fec;
        $valores->subtotal = $pla_monto / 1.18;
        $valores->igv = ($pla_monto / 1.18) * 0.18;
        $valores->total = $pla_monto;
        $tipoLista->motivo = 'defecto';
        $valores->tipoLista = $tipoLista;
        $valores->percepcion = 0;

        $posNumero = strpos($pla_numero, '-');
        $seriePla = substr($pla_numero, $posNumero - 4, 4);
        $numeroPla = substr($pla_numero, $posNumero + 1);
        $tipoCadena->serie = $seriePla;
        $tipoCadena->numero = $numeroPla;
        $valores->tipoCadena = $tipoCadena;

        $parametrosPlanilla->camposDinamicos = $valores;
        $parametrosPlanilla->usuarioId = $_SESSION['rec_usu_id'];
        $parametrosPlanilla->monedaId = ($mon_iso != 'PEN' ? 4 : 2);

        $comentario = ($pla_com1 == null ? '' : $pla_com1) . ' ' . ($pla_com2 == null ? '' : $pla_com2) . ' ' . ($pla_com3 == null ? '' : $pla_com3);
        $parametrosPlanilla->comentario = $comentario;
        $parametrosPlanilla->distribucionContable = getPlanillaMovDistribucionContable($pla_id);
    }

    $parametros->parametrosPlanilla = $parametrosPlanilla;

    $earSgi = new EarControlador();
    $respuesta = $earSgi->registrarLiquidacionVistoBueno($parametros);

    if ($respuesta->status == 0) {
        //ERROR
        $stmt = $mysqli->prepare("ROLLBACK") or die($mysqli->error);
        $stmt->execute() or die($mysqli->error);
        $stmt->close();
        include 'datos_cerrar_bd.php';

        echo utf8_decode($respuesta->mensaje);
        exit();
    }else{
        //SI SE REGISTRO TODO BIEN EN EL SGI ACTUALIZAMOS LOS ID DE DET LIQUIDACION Y PLANILLA EN EL EAR
        $dataEarSgi=$respuesta->data;
        $arrayLiqSGI=$dataEarSgi->arrLiqDet;
        if (count($arrayLiqSGI) > 0) {
            foreach ($arrayLiqSGI as $itemLiq) {
                earLiqDetalleActualizarSgiDocumentoIdXEldIdXDocumentoId($itemLiq[39], $itemLiq['documento_id_sgi']);
            }
        }

        $documentoPlanillaIdSgi=$dataEarSgi->documentoPlanillaId;
        if($documentoPlanillaIdSgi!=null && $documentoPlanillaIdSgi!=''){
            plaMovActualizarSgiDocumentoIdXPlaIdXDocumentoId($pla_id,$documentoPlanillaIdSgi);
        }

        //GUARDAMOS EL ID DE DEVOLUCION GENERADO
        if($ear_liq_dcto > 0) {
            $documentoDevolucionIdSgi = $dataEarSgi->documentoDevolucionId;
            if ($documentoDevolucionIdSgi != null && $documentoDevolucionIdSgi != '') {
                $stmt = $mysqli->prepare("update ear_solicitudes set sgi_documento_id_2=? where ear_id=?");
                $stmt->bind_param("ii", $documentoDevolucionIdSgi,$id);
                $stmt->execute() or die ($mysqli->error);
            }
        }
    }
} else {
    $stmt = $mysqli->prepare("ROLLBACK") or die($mysqli->error);
    $stmt->execute() or die($mysqli->error);
    $stmt->close();
    include 'datos_cerrar_bd.php';

    echo 'No existe documento de pago en sgi.';
    exit();
}
//-------------------------- FIN REGISTRAR PAGO SGI -------------------------------------


$stmt = $mysqli->prepare("COMMIT") or die ($mysqli->error);
$stmt->execute() or die ($mysqli->error);

$stmt->close();
include 'datos_cerrar_bd.php';

//if($est_id!=9){
    header("Location: ear_liq_act_vb.php");
//}else{
//    header("Location: ear_liq_act_tesocomp_p.php?id=".$id."&oper=1&regresarvb=1");
//}

exit;
