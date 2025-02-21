<?php
// Hace mailings automaticos reportando Documentos Pendientes vencidos que se hayan excedido de 15 dias.
// El correo se envia a Administracion.
header('Content-Type: text/html; charset=UTF-8');

include 'func.php';

// Tipo de reporte: EAR Pendientes de liquidar
$opc = 0;

$arr = getDocPendLista($opc, null);
$arrSemaforo = getValoresSemaforoDP();
$arrValEstado = $arrSemaforo[1];
$val_min_ambar = $arrValEstado[1];
$val_min_rojo = $arrValEstado[2];

$msgAdmAmbar = "";
$msgAdmRojo = "";
$i = 0;
$j = 0;

foreach ($arr as $v) {
	if (is_null($v[14])) {
		switch (true) {
			case ($v[13]>=$val_min_ambar && $v[13]<$val_min_rojo):
				$msgAdmAmbar .= getNombreTrabajador(getCodigoGeneral(getUsuAd($v[20])))." (".$v[2].") ".$v[13]." dias sin liquidar.\n";
				$i++;

				$to = getCorreoUsuario($v[20]);
				$cc = array();
				$arrEnc = getEncargadosCaja($v[21]);
				foreach ($arrEnc as $w) {
					array_push ($cc, getCorreoUsuario($w));
				}
				$arrResp = getResponsablesCaja($v[21]);
				foreach ($arrResp as $w) {
					array_push ($cc, getCorreoUsuario($w));
				}
				array_push ($cc, 'mngmt@Minapp.com.pe');
				$diasporvencer = $val_min_rojo-$v[13];
				$subject = "Alerta: Documento Pendiente de Caja Chica (".$v[2].") de ".getNombreTrabajador(getCodigoGeneral(getUsuAd($v[20])))." proximo a vencer en $diasporvencer dias";
				$body = "El Documento Pendiente de Caja Chica (".$v[2].") de ".getNombreTrabajador(getCodigoGeneral(getUsuAd($v[20])))." esta proximo a vencer en $diasporvencer dias. Realizar la liquidacion correspondiente con el encargado de caja chica, de lo contrario sera enviado a descuento.";

				enviarCorreo($to, $cc, $subject, $body);

				break;
			case ($v[13]>=$val_min_rojo):
				$msgAdmRojo .= getNombreTrabajador(getCodigoGeneral(getUsuAd($v[20])))." (".$v[2].") ".$v[13]." dias sin liquidar.\n";
				$j++;

				$to = getCorreoUsuario($v[20]);
				$cc = array();
				$arrEnc = getEncargadosCaja($v[21]);
				foreach ($arrEnc as $w) {
					array_push ($cc, getCorreoUsuario($w));
				}
				$arrResp = getResponsablesCaja($v[21]);
				foreach ($arrResp as $w) {
					array_push ($cc, getCorreoUsuario($w));
				}
				array_push ($cc, 'mngmt@Minapp.com.pe');
				$subject = "Alerta: Documento Pendiente de Caja Chica (".$v[2].") de ".getNombreTrabajador(getCodigoGeneral(getUsuAd($v[20])))." ha vencido";
				$body = "El Documento Pendiente de Caja Chica (".$v[2].") de ".getNombreTrabajador(getCodigoGeneral(getUsuAd($v[20])))." ha excedido los $val_min_rojo dias sin liquidar. Se procedera a realizar el descuento.";

				enviarCorreo($to, $cc, $subject, $body);

				break;
			default:
		}
	}
}

if ($i+$j > 0) {
	// Envia correo al Admin
	$to = getCorreoUsuario(getUsuAdmin());
	$cc = array();
	array_push ($cc, 'mngmt@Minapp.com.pe');
	$subject = "Se encontraron Documentos Pendientes vencidos de Caja Chica con exceso de $val_min_ambar dias";
	$body = "";
	if ($j > 0) {
		$body .= "Los siguientes Documentos Pendientes de Caja Chica indicados a continuacion tienen un exceso de $val_min_rojo dias sin liquidar, favor de entrar al modulo Administracion de la web intranet y enviarlas a Descuento:";
		$body .= "\n\n";
		$body .= $msgAdmRojo;
		$body .= "\n";
	}
	if ($i > 0) {
		$body .= "Los siguientes Documentos Pendientes de Caja Chica indicados a continuacion estan proximos a vencerse:";
		$body .= "\n\n";
		$body .= $msgAdmAmbar;
		$body .= "\n";
	}

	enviarCorreo($to, $cc, $subject, $body);

	echo $body;


	// Envia correo al Controller
	$to = getCorreoUsuario(getUsuController());
	$cc = array();
	array_push ($cc, 'mngmt@Minapp.com.pe');
	$subject = "Se encontraron Documentos Pendientes vencidos de Caja Chica con exceso de $val_min_ambar dias";
	$body = "";
	if ($j > 0) {
		$body .= "Los siguientes Documentos Pendientes de Caja Chica indicados a continuacion tienen un exceso de $val_min_rojo dias sin liquidar, se procedera a enviarlas a Descuento:";
		$body .= "\n\n";
		$body .= $msgAdmRojo;
		$body .= "\n";
	}
	if ($i > 0) {
		$body .= "Los siguientes Documentos Pendientes de Caja Chica indicados a continuacion estan proximos a vencerse:";
		$body .= "\n\n";
		$body .= $msgAdmAmbar;
		$body .= "\n";
	}

	enviarCorreo($to, $cc, $subject, $body);
}
else {
	echo "No se encontraron Documentos Pendientes vencidos de Caja Chica con exceso de $val_min_ambar dias. No se envio correo.";
}
?>
