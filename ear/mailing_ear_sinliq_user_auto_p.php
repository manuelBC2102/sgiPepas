<?php
// Hace mailings automaticos reportando EARs sin liquidar que se hayan excedido del minimo ambar segun semaforo de estado de desembolsos.
// Notificaciones a usuarios por correo electronico.
header('Content-Type: text/html; charset=UTF-8');

include 'func.php';

// Obtiene los valores minimos para ambar y rojo
list($val_min_ambar, $val_min_rojo) = getDiasMinDesembolsado();

// Obtiene la lista de usuarios con EAR excedidos del minimo ambar
$arrUsers = getUsuariosEARsinliqLista($val_min_ambar);

// Cantidad maxima de registros a mostrar
$max = 25000;

// Tipo de reporte: EAR Pendientes de liquidar
$cons_id = 1;
$zon_id = 255; //Zona por defecto al cargar los resultados
$mon_id = 255; //Moneda por defecto al cargar los resultados
$est_id = 255; //Estado por defecto al cargar los resultados
$rfecha2 = "2000-01-01";
$rfecha4 = date('Y-m-d');
$opc_id = 1;

$j = 0;

foreach ($arrUsers as $u) {
	$arr = getListaEARs($max, $cons_id, $u, $zon_id, $mon_id, $est_id, $rfecha2, $rfecha4, $opc_id);

	$msg = "";
	$i = 0;

	foreach ($arr as $v) {
		if ($v[15] >= $val_min_ambar && $v[15] < $val_min_rojo) {
			$msg .= $v[1]." (".$v[2].") ".$v[15]." dias sin liquidar. (ALERTA AMBAR - registre su liquidacion).\n";
			$i++;
			$j++;
		}
		else if ($v[15] >= $val_min_rojo) {
			$msg .= $v[1]." (".$v[2].") ".$v[15]." dias sin liquidar. (ALERTA ROJA - proximo a descuento automatico)\n";
			$i++;
			$j++;
		}
	}

	if ($i > 0) {
		$to = getCorreoUsuario($u);
		$cc = array();
		array_push ($cc, 'mngmt@Minapp.com.pe');

		$subject = "Se encontraron EAR sin liquidar con exceso de 30 dias";
		$body = "Las siguientes EAR indicadas a continuacion tienen un exceso de 30 dias sin liquidar, favor de entrar al modulo Administracion de la web intranet y registre la liquidacion correspondiente:";
		$body .= "\n\n";
		$body .= $msg;

		enviarCorreo($to, $cc, $subject, $body);

		echo $body."\n\n";
	}
}

if ($j == 0) {
	echo "No se encontraron EAR sin liquidar con exceso de 30 dias. No se enviaron correos.";
}
?>

