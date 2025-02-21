<?php
// Hace mailings automaticos reportando EARs sin liquidar que se hayan excedido de 60 dias.
// El correo se envia a Administracion.
header('Content-Type: text/html; charset=UTF-8');

include 'func.php';

// Cantidad maxima de registros a mostrar
$max = 25000;

// Tipo de reporte: EAR Pendientes de liquidar
$cons_id = 3;
$logged_usu_id = getUsuAdmin();
$zon_id = 255; //Zona por defecto al cargar los resultados
$mon_id = 255; //Moneda por defecto al cargar los resultados
$est_id = 255; //Estado por defecto al cargar los resultados
$rfecha2 = "2000-01-01";
$rfecha4 = date('Y-m-d');
$opc_id = 2;

$arr = getListaEARs($max, $cons_id, $logged_usu_id, $zon_id, $mon_id, $est_id, $rfecha2, $rfecha4, $opc_id);

$msg = "";
$i = 0;

foreach ($arr as $v) {
	if ($v[15] >= 60) {
		$msg .= $v[1]." (".$v[2].") ".$v[15]." dias sin liquidar.\n";
		$i++;
	}
}

if ($i > 0) {
	$to = getCorreoUsuario(getUsuAdmin());
	$cc = array();
	array_push ($cc, 'mngmt@Minapp.com.pe');

	$subject = "Se encontraron EAR sin liquidar con exceso de 60 dias";
	$body = "Las siguientes EAR indicadas a continuacion tienen un exceso de 60 dias sin liquidar, favor de entrar al modulo Administracion de la web intranet y enviarlas a Descuento:";
	$body .= "\n\n";
	$body .= $msg;

	enviarCorreo($to, $cc, $subject, $body);

	echo $body;
}
else {
	echo "No se encontraron EAR sin liquidar con exceso de 60 dias. No se envio correo.";
}
?>
