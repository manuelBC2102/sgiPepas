<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'parametros.php';
require_once('../'.$carpetaSGI.'/modeloNegocio/almacen/DocumentoNegocio.php');
require_once('../'.$carpetaSGI.'/modeloNegocio/almacen/PagoNegocio.php');
//include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
	$oper = abs((int) filter_var($f_oper, FILTER_SANITIZE_NUMBER_INT));
	$regresarvb = abs((int) filter_var($f_regresarvb, FILTER_SANITIZE_NUMBER_INT));
}

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
	$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
	$usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
	$ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
	$ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
	$master_usu_id) = getSolicitudInfo($id);

if ($est_id <> 9 && $est_id <> 51) {
	echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud</b></font><br>";
	exit;
}
// if ($usu_id <> $_SESSION['rec_usu_id']) {
	// echo "<font color='red'><b>ERROR: No se puede acceder a la informaci&oacute;n de la liquidaci&oacute;n</b></font><br>";
	// exit;
// }

// Se asigna nuevo estado :
if ($oper==1) {
	$est_id = 10; // Reembolso
	$msj_mov = "Reembolso efectuado";
}
else if ($oper==2) {
	if ($est_id == 51) $est_id = 52; // Descuento de dias excedidos sin liquidar
	else $est_id = 11; // Descuento
	$msj_mov = "Devolucion efectuada";
}
else {
	echo "<font color='red'><b>ERROR: Valor de operacion invalido, se ha notificado esta operacion al administrador</b></font><br>";
	exit;
}

//if(false){//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=?, ear_act_fec=?, ear_act_usu=? WHERE ear_id=?") or die ($mysqli->error);
$stmt->bind_param('isii',
	$est_id,
	$ahora,
	$_SESSION['rec_usu_id'],
	$id);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, ?, ?, ?, ?)") or die ($mysqli->error);
$stmt->bind_param('iiiss', $id, $est_id, $_SESSION['rec_usu_id'], $ahora,$msj_mov);
$stmt->execute() or die ($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
$desc = "Liquidacion EAR $msj_mov (".$id.") hecha por ".$_SESSION['rec_usu_nombre'];
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die ($mysqli->error);
$stmt->close();

$mysqli->commit();

include 'datos_cerrar_bd.php';


if ($oper==1) {
        //----------------EL ENVIO DE CORREO SE DESHABILITO---------------------
    IF(false){
        $to = getCorreoUsuario($usu_id);
	$cc = array();
        $cc=obtenerCorreosPerfilSGI($pTESO,$cc);
        $cc=obtenerCorreosPerfilSGI($pADMINIST,$cc);
	if(!is_null($master_usu_id)) array_push ($cc, getCorreoUsuario($master_usu_id));

        $correo=$to.';';
        $cc = array_unique($cc);
        foreach ($cc as $index => $valor) {
            $correo=$correo.$valor.';';
        }
        //-------------------------------------
	$subject = $msj_mov." al EAR $ear_numero de ".$ear_tra_nombres;
	$body = "Tesoreria ha efectuado el reembolso correspondiente al EAR $ear_numero de $ear_tra_nombres por el monto de ".conComas($ear_liq_dcto*-1)." $mon_nom.";
	$body .= "\n\nEste EAR ya no tiene operaciones pendientes. Ha llegado a su etapa final.";
	//R. enviarCorreo($to, $cc, $subject, $body);
	//-------registro email en sgi-------
        list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
        $plantillaCuerpo = str_replace("[|asunto|]", 'Tesoreria ha efectuado el reembolso correspondiente al EAR', $plantillaCuerpo);
        $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

        $emailEnvioId = insertarEmailEnvioSGI($correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);
    }
        //---------------------------------

//	header("Location: ear_liq_act_teso.php");
}
else {
        //-------------------------------------
	$to = getCorreoUsuario($usu_id);
	$cc = array();
        $cc=obtenerCorreosPerfilSGI($pCOMP,$cc);
        $cc=obtenerCorreosPerfilSGI($pADMINIST,$cc);
	if(!is_null($master_usu_id)) array_push ($cc, getCorreoUsuario($master_usu_id));

        $correo=$to.';';
        $cc = array_unique($cc);
        foreach ($cc as $index => $valor) {
            $correo=$correo.$valor.';';
        }
        //-------------------------------------

	$subject = $msj_mov." al EAR $ear_numero de ".$ear_tra_nombres;
	$body = "Tesorer&iacute;a ha efectuado la devoluci&oacute;n correspondiente al EAR $ear_numero de $ear_tra_nombres por el monto de ".conComas($ear_liq_dcto)." $mon_nom.";
	$body .= "\n\nEste EAR ya no tiene operaciones pendientes. Ha llegado a su etapa final.";
	//R. enviarCorreo($to, $cc, $subject, $body);
	//-------registro email en sgi-------
        list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
        $plantillaCuerpo = str_replace("[|asunto|]", 'Tesorer&iacute;a ha efectuado la devoluci&oacute;n correspondiente al EAR', $plantillaCuerpo);
        $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

        $emailEnvioId = insertarEmailEnvioSGI($correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);
        //---------------------------------

//	header("Location: ear_liq_act_comp.php");
}
//}//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

//---------------------- REGISTRAR REEMBOLSO O DESEMBOLSO EN SGI --------------------
//-- DEVOLUCION DEL COLABORADOR:
if ($oper==2) {
    //-------------------------- REGISTRAR PAGO EN SGI --------------------------------------
    $proveedor=obtenerPersonaIdSGI($usu_id);
    $fecha=date("d/m/Y");
    $usuarioId=$_SESSION['rec_usu_id'];
    $montoAPagar=0;//para pago efectivo
    $monedaPago=2;
    if($mon_iso!='PEN'){
        $monedaPago=4;
    }

    $retencion=1;//siempre, en sgi todavia no se usa
    $empresaId=2;//para pago efectivo
    $actividadEfectivo=null;//para pago efectivo

    //-- DOCUMENTO A PAGAR,OBTENIENDO ID DE DOCUMENTO SGI
    $listaEarLiqDetalle=earLiqDetalleObtenerXEarId($id);
    $tipoCambio=$listaEarLiqDetalle[0]['lid_tc'];

    $documentoAPagar=array();
    $listaEarSolicitud=earSolicitudObtenerXEarId($id);
    $documentoId=$listaEarSolicitud[0]['sgi_documento_id_2'];
    if ($documentoId != null && $documentoId != '') {
        $dataDoc = DocumentoNegocio::create()->obtenerDocumentoAPagar($documentoId);

        //DAR FORMATO DOCUMENTOS A PAGAR
        $arrayDocumentoAPagar = array();
        $arrayDocumentoAPagar['documentoId'] = $dataDoc[0]['documento_id'];
        $arrayDocumentoAPagar['tipoDocumento'] = $dataDoc[0]['documento_tipo'];
        $arrayDocumentoAPagar['numero'] = $dataDoc[0]['numero'];
        $arrayDocumentoAPagar['serie'] = $dataDoc[0]['serie'];
        $arrayDocumentoAPagar['pendiente'] = $dataDoc[0]['pendiente'];
        $arrayDocumentoAPagar['dolares'] = $dataDoc[0]['mdolares'];
        $arrayDocumentoAPagar['total'] = $dataDoc[0]['total'];
        $arrayDocumentoAPagar['tipo'] = $dataDoc[0]['tipo'];

        array_push($documentoAPagar, $arrayDocumentoAPagar);
//        $montoAPagar=$dataDoc[0]['pendiente'];


    //SE OBTIENE EL ID DEL DOCUMENTO TIPO SGI DE PAGO: TICKET TRANSFERENCIA - DEPOSITO
    $documentoTipoIdSGI=$documentoTipoIdTicketSGI;

    //------------------- REGISTRAR EN EL SGI EL DOCUMENTO DE PAGO -------------------------
    $valores->persona=obtenerPersonaIdSGI($usu_id);
    $valores->numero='auto';
    $valores->fechaEmision=date("Y-m-d");
        $tipoCadena->numero='';
    $valores->tipoCadena=$tipoCadena;
    $valores->total=$ear_liq_dcto;
    $valores->cuenta='defecto';
    $valores->actividad=$actividadIdSGI;

    $camposDinamicos=obtenerCamposDinamicosSGI($documentoTipoIdSGI,$valores);

    $usuarioId=$_SESSION['rec_usu_id'];
    $monedaId=2;
    if($mon_iso!='PEN'){
        $monedaId=4;
    }
    $movimientoId=null;
    $comentario=null;
    $descripcion=null;
    $resDocId = DocumentoNegocio::create()->guardar($documentoTipoIdSGI, $movimientoId, null, $camposDinamicos, 1, $usuarioId,$monedaId,$comentario,$descripcion);
    $documentoIdPago=(int)$resDocId[0]['vout_id'];
    //------------------- FIN REGISTRO -------------------------------------

    $documentoPagoConDocumento = array();
    $dataDocPago= DocumentoNegocio::create()->obtenerDocumentoAPagar($documentoIdPago);

    //DAR FORMATO AL DOCUMENTO DE PAGO
    $arrayDocumentoPago=array();
    $arrayDocumentoPago['documentoId'] = $dataDocPago[0]['documento_id'];
    $arrayDocumentoPago['tipoDocumento'] = $dataDocPago[0]['documento_tipo'];
    $arrayDocumentoPago['tipoDocumentoId'] = $dataDocPago[0]['documento_tipo_id'];
    $arrayDocumentoPago['numero'] = $dataDocPago[0]['numero'];
    $arrayDocumentoPago['serie'] = $dataDocPago[0]['serie'];
    $arrayDocumentoPago['pendiente'] = $dataDocPago[0]['pendiente'];
    $arrayDocumentoPago['total'] = $dataDocPago[0]['total'];
    $arrayDocumentoPago['dolares'] = $dataDocPago[0]['dolares'];
    $arrayDocumentoPago['moneda'] = $dataDocPago[0]['dolares'] * 1 === 0 ? "Soles" : "Dolares";
    $arrayDocumentoPago['monto'] = $dataDocPago[0]['monto'];

    array_push($documentoPagoConDocumento, $arrayDocumentoPago);

    $resPago = PagoNegocio::create()->registrarPago($proveedor, $fecha, $documentoAPagar, $documentoPagoConDocumento,
            $usuarioId, $montoAPagar, $tipoCambio, $monedaPago, $retencion, $empresaId, $actividadEfectivo);

    }

    header("Location: ear_liq_act_teso.php");
}

//------------------------------------ FIN SGI DEVOLUCION--------------------------------------

//-- REEMBOLSO
if ($oper==1) {
    //-------------------------- REGISTRAR PAGO EN SGI REEMBOLSO--------------------------------------
    $proveedor=obtenerPersonaIdSGI($usu_id);
    $fecha=date("d/m/Y");
    $usuarioId=$_SESSION['rec_usu_id'];
    $montoAPagar=0;//para pago efectivo
    $monedaPago=2;
    if($mon_iso!='PEN'){
        $monedaPago=4;
    }

    $retencion=1;//siempre, en sgi todavia no se usa
    $empresaId=2;//para pago efectivo
    $actividadEfectivo=null;//para pago efectivo

    //-- DOCUMENTOS A PAGAR,OBTENIENDO LOS IDS DE LOS DOCUMENTOS SGI
    //-- DEL DETALLE DE LA LIQUIDACION
    $listaEarLiqDetalle=earLiqDetalleObtenerXEarId($id);
    $tipoCambio=$listaEarLiqDetalle[0]['lid_tc'];

    $documentoAPagar=array();
    foreach ($listaEarLiqDetalle as $item){
        $documentoId=$item['sgi_documento_id'];
        if ($documentoId != null && $documentoId != '') {
            $dataDoc = DocumentoNegocio::create()->obtenerDocumentoAPagar($documentoId);

            //DAR FORMATO DOCUMENTOS A PAGAR
            $arrayDocumentoAPagar = array();
            $arrayDocumentoAPagar['documentoId'] = $dataDoc[0]['documento_id'];
            $arrayDocumentoAPagar['tipoDocumento'] = $dataDoc[0]['documento_tipo'];
            $arrayDocumentoAPagar['numero'] = $dataDoc[0]['numero'];
            $arrayDocumentoAPagar['serie'] = $dataDoc[0]['serie'];
            $arrayDocumentoAPagar['pendiente'] = $dataDoc[0]['pendiente'];
            $arrayDocumentoAPagar['dolares'] = $dataDoc[0]['mdolares'];
            $arrayDocumentoAPagar['total'] = $dataDoc[0]['total'];
            $arrayDocumentoAPagar['tipo'] = $dataDoc[0]['tipo'];

            if($dataDoc[0]['pendiente']>0){
                array_push($documentoAPagar, $arrayDocumentoAPagar);
            }
        }
    }

    //-- DE LA PLANILLA DE MOVILIDAD
    $listaPlaMov = plaMovObtenerXEarId($id);
    if (count($listaPlaMov) > 0) {
        $documentoId = $listaPlaMov[0]['sgi_documento_id'];
        if ($documentoId != null && $documentoId != '') {
            $dataDoc = DocumentoNegocio::create()->obtenerDocumentoAPagar($documentoId);

            //DAR FORMATO DOCUMENTOS A PAGAR
            $arrayDocumentoAPagar = array();
            $arrayDocumentoAPagar['documentoId'] = $dataDoc[0]['documento_id'];
            $arrayDocumentoAPagar['tipoDocumento'] = $dataDoc[0]['documento_tipo'];
            $arrayDocumentoAPagar['numero'] = $dataDoc[0]['numero'];
            $arrayDocumentoAPagar['serie'] = $dataDoc[0]['serie'];
            $arrayDocumentoAPagar['pendiente'] = $dataDoc[0]['pendiente'];
            $arrayDocumentoAPagar['dolares'] = $dataDoc[0]['mdolares'];
            $arrayDocumentoAPagar['total'] = $dataDoc[0]['total'];
            $arrayDocumentoAPagar['tipo'] = $dataDoc[0]['tipo'];

            if($dataDoc[0]['pendiente']>0){
                array_push($documentoAPagar, $arrayDocumentoAPagar);
            }
        }
    }

    if (count($documentoAPagar) > 0) {
        //SE OBTIENE EL ID DEL DOCUMENTO TIPO SGI DE PAGO: EAR Reembolso (PAGOS)
        $documentoTipoIdSGI = $documentoTipoIdReembolsoSGI;

        //------------------- REGISTRAR EN EL SGI EL DOCUMENTO DE PAGO -------------------------
        $valores->persona = obtenerPersonaIdSGI($usu_id);
        $valores->numero = 'auto';
        $valores->fechaEmision = date("Y-m-d");
        $valores->total = $ear_liq_dcto * -1;
        $valores->cuenta = 'defecto';
        $valores->actividad = 'defecto';

        $camposDinamicos = obtenerCamposDinamicosSGI($documentoTipoIdSGI, $valores);

        $usuarioId = $_SESSION['rec_usu_id'];
        $monedaId = 2;
        if ($mon_iso != 'PEN') {
            $monedaId = 4;
        }
        $movimientoId = null;
        $comentario = null;
        $descripcion = null;
        $resDocId = DocumentoNegocio::create()->guardar($documentoTipoIdSGI, $movimientoId, null, $camposDinamicos, 1, $usuarioId, $monedaId, $comentario, $descripcion);
        $documentoIdPago = (int) $resDocId[0]['vout_id'];
        //------------------- FIN REGISTRO -------------------------------------

        $documentoPagoConDocumento = array();
        $dataDocPago = DocumentoNegocio::create()->obtenerDocumentoAPagar($documentoIdPago);

        //DAR FORMATO AL DOCUMENTO DE PAGO
        $arrayDocumentoPago = array();
        $arrayDocumentoPago['documentoId'] = $dataDocPago[0]['documento_id'];
        $arrayDocumentoPago['tipoDocumento'] = $dataDocPago[0]['documento_tipo'];
        $arrayDocumentoPago['tipoDocumentoId'] = $dataDocPago[0]['documento_tipo_id'];
        $arrayDocumentoPago['numero'] = $dataDocPago[0]['numero'];
        $arrayDocumentoPago['serie'] = $dataDocPago[0]['serie'];
        $arrayDocumentoPago['pendiente'] = $dataDocPago[0]['pendiente'];
        $arrayDocumentoPago['total'] = $dataDocPago[0]['total'];
        $arrayDocumentoPago['dolares'] = $dataDocPago[0]['dolares'];
        $arrayDocumentoPago['moneda'] = $dataDocPago[0]['dolares'] * 1 === 0 ? "Soles" : "Dolares";
        $arrayDocumentoPago['monto'] = $dataDocPago[0]['monto'];

        array_push($documentoPagoConDocumento, $arrayDocumentoPago);

        $resPago = PagoNegocio::create()->registrarPago($proveedor, $fecha, $documentoAPagar, $documentoPagoConDocumento, $usuarioId, $montoAPagar, $tipoCambio, $monedaPago, $retencion, $empresaId, $actividadEfectivo);
    }

    if($regresarvb==1){
        header("Location: ear_liq_act_vb.php");
    }else{
        header("Location: ear_liq_act_teso.php");
    }
}
exit;
?>
