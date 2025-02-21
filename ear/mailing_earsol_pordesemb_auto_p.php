<?php
// Hace mailings automaticos reportando solicitudes EARs pendientes de desembolsar.
// El correo se envia a Tesoreria.
header('Content-Type: text/plain; charset=UTF-8');

include 'func.php';

// Tipo de reporte: EAR Pendientes de desembolsar
include 'datos_abrir_bd.php';

$query='SELECT e.ear_id, e.ear_tra_nombres, CONCAT(e.ear_anio, "-", LPAD(e.ear_mes, 2, "0"), "-", LPAD(e.ear_nro, 3, "0"), "/", ud.usu_iniciales) ear_numero,
	z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, e.ear_monto, e.est_id, te.est_nom, e.ear_sol_fec, e.ear_liq_fec,
	ru.usu_nombre usu_act, e.ear_act_fec, e.ear_act_motivo, e.master_usu_id
FROM ear_solicitudes e
LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
LEFT JOIN monedas m ON m.mon_id=e.mon_id
LEFT JOIN tablas_estados te ON te.est_id=e.est_id
JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom="ear_solicitudes"
LEFT JOIN usu_detalle ud ON ud.usu_id=e.usu_id
LEFT JOIN recursos.usuarios ru ON ru.usu_id=e.ear_act_usu
WHERE e.est_id=2
LIMIT 2000';
$result = $mysqli->query($query) or die ($mysqli->error);

$msg = "";
$i = 0;
$arr = array();

while($fila=$result->fetch_array()){
	$msg .= $fila['ear_tra_nombres']." (".$fila['ear_numero'].") ".$fila['ear_monto']." ".$fila['mon_nom']."\n";
	// $arr[] = array($fila['ear_numero'], getCartaEarSol($fila['ear_id'], 'S'));
	// $arr[] = array($fila['ear_id'], $fila['ear_numero']);
	// $arr[] = array(getCartaEarSol($fila['ear_id'], 'S'), $fila['ear_numero']);

	$i++;
}

// echo getCartaEarSol(210, 'S');
// foreach ($arr as $v) {
	// $attachFilename = "EAR_".str_replace("/", "_", $v[1]).".pdf";
	// echo $v[0].' '.$attachFilename."<br>";
// }

include 'datos_cerrar_bd.php';

if ($i > 0) {
	$to = getCorreoUsuario(getUsuTesoreria());
	$cc = array();
	array_push ($cc, 'mngmt@Minapp.com.pe');

	$subject = "Se encontraron EAR sin desembolsar";
	$body = "Las siguientes EAR indicadas a continuacion se encuentran pendientes de desembolsar, favor de entrar al modulo Administracion de la web intranet y efectuar el desembolso:";
	$body .= "\n\n";
	$body .= $msg;
	$body .= "\n$i solicitud(es) pendiente(s) de desembolsar.";

	enviarCorreo($to, $cc, $subject, $body);

	echo $body;
}
else {
	echo "No se encontraron EAR sin desembolsar. No se envio correo.";
}
?>
