<?php

header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'reportesPDF.php';
include 'parametros.php';
require_once('../' . $carpetaSGI . '/controlador/almacen/EarControlador.php');

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id) || !isset($f_opcion)) {
    echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
    exit;
}

$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
$opcion = abs((int) filter_var($f_opcion, FILTER_SANITIZE_NUMBER_INT));
if (isset($f_motivo))
    $motivo = ucfirst(trim(filter_var($f_motivo, FILTER_SANITIZE_STRING)));
else
    $motivo = NULL;

if ($opcion != 2 && $opcion != 3 && $opcion != 41) {
    echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
    exit;
}

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
        $ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
        $usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
        $ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
        $ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
        $master_usu_id, $tipo_usu_id) = getSolicitudInfo($id);

$totalDetalleBD = getSolicitudTotal($id);

if ($ear_monto != $totalDetalleBD) {
    echo "<font color='red'><b>ERROR: El monto total de la solicitud ($ear_monto) no coincide con la suma del detalle ($totalDetalleBD), revisar la solicitud, actualizar la informacion e intentar de nuevo.</b></font><br>";
    exit;
}

//INICIO TRANSACCION
$stmt = $mysqli->prepare("SET AUTOCOMMIT=0") or die ($mysqli->error);
$stmt->execute() or die ($mysqli->error);

$query = "SELECT now()";
$result = $mysqli->query($query) or die($mysqli->error);
$fila = $result->fetch_array();
$ahora = $fila[0];


$stmt = $mysqli->prepare("SELECT est_id FROM ear_solicitudes WHERE ear_id=?");
$stmt->bind_param("s", $id);
$stmt->execute() or die ($mysqli->error);
$stmt->store_result();
$fila=fetchAssocStatement($stmt);
$op_estado= $fila['est_id'];


//------------------- REGISTRAR EN SGI -------------------------
$documentoId=null;
if($opcion==2 ){

    if($opcion == $op_estado){
        die('La solicitud ya ah sido aprobada');
    }
    $parametros->documentoTipoId=$documentoTipoIdDesembolsoSGI;
    $parametros->usuarioId=$_SESSION['rec_usu_id'];
    $parametros->monedaId=($mon_iso != 'PEN'?4:2);
    $parametros->usuarioPersona=$usu_id;
    $parametros->tipo_usu_id= $tipo_usu_id;
        $valores->persona = null;
        $valores->numero = $ear_numero;
        $valores->fechaEmision = $ear_liq_fec;//revisar fecha de emisón
            $tipoFecha->fecha = $ear_liq_fec;
        $valores->tipoFecha = $tipoFecha;
        $valores->total = $ear_monto;
        $valores->descripcion = $ear_sol_motivo;
        $valores->cuenta = 'defecto';
        $valores->actividad = 'defecto';
    $parametros->camposDinamicos=$valores;

    $earSgi = new EarControlador();
    $respuesta=$earSgi->registrarDocumentoDesembolso($parametros);

    if($respuesta->status==0){
        //ERROR
        $stmt = $mysqli->prepare("ROLLBACK") or die ($mysqli->error);
        $stmt->execute() or die ($mysqli->error);
        $stmt->close();
        include 'datos_cerrar_bd.php';

        echo utf8_decode($respuesta->mensaje);
        exit();
    }

    $documentoId = $respuesta->documentoId;
}
//------------------- FIN SGI -------------------------------------

$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET est_id=?, ear_act_usu=?, ear_act_fec=?, ear_act_motivo=?, ear_aprob_usu=?,sgi_documento_id=?
	WHERE ear_id=? AND est_id=1") or die($mysqli->error);
$stmt->bind_param('iissiii', $opcion, $_SESSION['rec_usu_id'], $ahora, $motivo, $_SESSION['rec_usu_id'], $documentoId, $id);

$stmt->execute() or die($mysqli->error);
if ($stmt->affected_rows <> 1)
    die('No se actualiz&oacute; el registro EAR'); // antes $mysqli -> $stmt

$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, ?, ?, ?, ?)") or die($mysqli->error);
$stmt->bind_param('iiiss', $id, $opcion, $_SESSION['rec_usu_id'], $ahora, $motivo);
$stmt->execute() or die($mysqli->error);

$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die($mysqli->error);
$desc = "Actualizacion estado solicitud EAR (" . $id . ") a valor " . $opcion;
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);
$stmt->bind_param('issss', $_SESSION['rec_usu_id'], $desc, $ahora, $ip, $host);

$stmt->execute() or die($mysqli->error);

//ENVIO DE CORREO
//obteniendo correos
$to = getCorreoUsuario($usu_id);
$cc = array();

// ENVIO DE CORREO A CECILIA Y GIAN CARDENAS y ROLANDO TOMANDO EN CUENTA SUS PERFILES
$cc = obtenerCorreosPerfilSGI($pADMINIST, $cc);
$cc = obtenerCorreosPerfilSGI($pASISTENTE_ADMINISTRATIVO, $cc);
$cc = obtenerCorreosPerfilSGI($pGERENTE, $cc);

$correo = $to . ';';
$cc = array_unique($cc);
foreach ($cc as $index => $valor) {
    $correo = $correo . $valor . ';';
}

if ($opcion == 2) {
    // Adjuntar pdf si es que se aprueba
    $hoy = date("Y_m_d_H_i_s");
    $nombreDocumento = "EAR_" . str_replace("/", "_", $ear_numero);
    $ext = ".pdf";
    $nombreArchivo = $nombreDocumento . $ext;

    $rutaArchivo = __DIR__ . "/Documentos/" . $nombreDocumento . "_" . $hoy . $ext;

    $resPDF = getCartaEarSol($id, 'F', $rutaArchivo);
//	if (is_null($attachString)) die("Error en la generacion del archivo PDF, no se completo la transaccion. (Cadena vacia)");
//	$attachFilename = __DIR__ ."/EAR_".str_replace("/", "_", $ear_numero).".pdf";

    $subject = "Solicitud Aprobada de EAR $ear_numero de " . $ear_tra_nombres;
    $body = "Se ha aprobado la solicitud de EAR $ear_numero de $ear_tra_nombres por el monto de " . number_format($ear_monto, 2, '.', ',') . " $mon_nom.";
    // $body .= "\n\nNota al colaborador: Imprimir, firmar y hacer entregar a la brevedad el documento PDF adjunto a Administracion, gracias.";
    $body .= "\n\nEsperando el desembolso de Tesoreria";

//	ConstructorMail::enviarCorreoInfo($to,$cc,$subject,$body,$var_modulo_ident,"info",$attachString,$attachFilename);
    //obtener la plantilla del email
    list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
    $plantillaCuerpo = str_replace("[|asunto|]", 'Solicitud Aprobada de EAR', $plantillaCuerpo);
    $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

    //insertar en email_envio de sgi
    $emailEnvioId = insertarEmailEnvioSGIConeccion($mysqli,$correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], $rutaArchivo, $nombreArchivo);

    $location = "ear_consulta.php?cons_id=2&est_id=1";
} else if ($opcion == 3) {
    $subject = "Solicitud Rechazada de EAR $ear_numero de " . $ear_tra_nombres;
    $body = "Se ha rechazado la solicitud de EAR $ear_numero de $ear_tra_nombres por el monto de " . number_format($ear_monto, 2, '.', ',') . " $mon_nom.";
    $body .= "\n\nMotivo: $motivo";

    //obtener la plantilla del email
    list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
    $plantillaCuerpo = str_replace("[|asunto|]", 'Solicitud Rechazada de EAR', $plantillaCuerpo);
    $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

    //insertar en email_envio de sgi
    $emailEnvioId = insertarEmailEnvioSGIConeccion($mysqli,$correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);

    $location = "ear_consulta.php?cons_id=2&est_id=1";
} else if ($opcion == 41) {
    $subject = "Solicitud Cancelada de EAR $ear_numero de " . $ear_tra_nombres;
    $body = "Se ha cancelado la solicitud de EAR $ear_numero de $ear_tra_nombres por el monto de " . number_format($ear_monto, 2, '.', ',') . " $mon_nom.";
    $body .= "\n\nMotivo: $motivo";

    //obtener la plantilla del email
    list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
    $plantillaCuerpo = str_replace("[|asunto|]", 'Solicitud Cancelada de EAR', $plantillaCuerpo);
    $plantillaCuerpo = str_replace("[|cuerpo|]", $body, $plantillaCuerpo);

    //insertar en email_envio de sgi
    $emailEnvioId = insertarEmailEnvioSGIConeccion($mysqli,$correo, $subject, $plantillaCuerpo, $_SESSION['rec_usu_id'], null, null);

    $location = "ear_consulta.php?cons_id=1";
}

//COMMIT
$stmt = $mysqli->prepare("COMMIT") or die ($mysqli->error);
$stmt->execute() or die ($mysqli->error);
$stmt->close();
include 'datos_cerrar_bd.php';

header("Location: $location");
exit;
