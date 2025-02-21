<?php
header('Content-Type: text/html; charset=UTF-8');

include 'func.php';
include 'reportesPDF.php';

// Verifica la fecha del sistema que sea el primer dia del mes para ejecutar el resto de codigo
$hoy = date('Y-m-d');

if ( substr($hoy, -2) != '01' ) {
	echo "<font color='red'><b>ERROR: La fecha de hoy no es el primer dia del mes. No se realizo nada.</b></font><br>";
	exit;
}

$anio = date('Y');
$mes = date('m');

$arr = getUsuEarAutoListaVigente($hoy);

// Valores de $v
// 0 eua_id
// 1 usu_nombre
// 2 fec_ini
// 3 fec_fin
// 4 zona_nom
// 5 mon_nom
// 6 mon_iso
// 7 mon_img
// 8 eua_monto
// 9 eua_act
// 10 zona_id
// 11 mon_id
// 12 usu_id
// 13 eua_tra_cta

include 'datos_abrir_bd.php';

$mysqli->autocommit(FALSE);

$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

foreach ($arr as $v) {
	list($dni, $nombres, $cargo_id, $fecha_ing,
		$cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador(getCodigoGeneral(getUsuAd($v[12])));

	$fecha2 = date('Y-m-25');
	$motivo = 'Solicitud generada automaticamente por sistema';
	if (strlen($v[13]) > 0) {
		$ctacte2 = $v[13];
	}
	else {
		$ctacte2 = $ctacte;
	}

	$stmt = $mysqli->prepare("INSERT INTO ear_solicitudes (usu_id, zona_id, mon_id, ear_monto, est_id, ear_sol_fec, ear_liq_fec,
		ear_sol_motivo, ear_tra_dni, ear_tra_nombres, ear_tra_cargo, ear_tra_area, ear_tra_sucursal, ear_tra_cta,
		ear_anio, ear_mes, ear_nro)
		SELECT ?, ?, ?, ?, 2, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, max_nro
		FROM (
			SELECT IFNULL(MAX(ear_nro), 0)+1 AS max_nro FROM ear_solicitudes
			WHERE usu_id=? AND ear_anio=? AND ear_mes=?) AS sub_tabla") or die ($mysqli->error);
	$stmt->bind_param('isidsssssssssiiiii', $v[12],
		$v[10],
		$v[11],
		$v[8],
		$ahora,
		$fecha2,
		$motivo,
		$dni,
		$nombres,
		$cargo_desc,
		$area_desc,
		$sucursal,
		$ctacte2,
		$anio,
		$mes,
		$v[12],
		$anio,
		$mes);

	$stmt->execute() or die ($mysqli->error);

	$insertion_id = $mysqli->insert_id;

	//Obtiene nro de EAR
	$stmt = $mysqli->prepare("SELECT CONCAT(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/', ud.usu_iniciales) ear_numero
		FROM ear_solicitudes e
		LEFT JOIN usu_detalle ud ON ud.usu_id=e.usu_id
		LEFT JOIN recursos.usuarios ru ON ru.usu_id=e.ear_act_usu
		WHERE e.ear_id=?");
	$stmt->bind_param("i", $insertion_id);
	$stmt->execute() or die ($mysqli->error);
	$result = $stmt->get_result();
	$fila=$result->fetch_array();
	$ear_numero = $fila[0];

	//Agrega detalle gastos de representacion
	$gast_mont = $v[8];
	if ($gast_mont>0) {
		$via_id = getViaId('05');

		$stmt = $mysqli->prepare("INSERT INTO ear_sol_detalle (ear_id, via_id, via_monto) VALUES (?, ?, ?)") or die ($mysqli->error);
		$stmt->bind_param('iid', $insertion_id, $via_id, $gast_mont);
		$stmt->execute() or die ($mysqli->error);
	}

	list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($v[12]);

	$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 1, ?, ?, 'Solicitud generada automaticamente por sistema')") or die ($mysqli->error);
	$stmt->bind_param('iis', $insertion_id, $usu_id_jefe, $ahora);
	$stmt->execute() or die ($mysqli->error);

	$stmt = $mysqli->prepare("INSERT INTO ear_actualizaciones VALUES (?, 2, ?, ?, 'Solicitud aprobada automaticamente por sistema')") or die ($mysqli->error);
	$stmt->bind_param('iis', $insertion_id, $usu_id_jefe, $ahora);
	$stmt->execute() or die ($mysqli->error);

	$stmt = $mysqli->prepare("UPDATE ear_solicitudes SET ear_act_usu=?, ear_act_fec=?, ear_aprob_usu=?
		WHERE ear_id=?") or die ($mysqli->error);
	$stmt->bind_param('isii', $usu_id_jefe, $ahora, $usu_id_jefe, $insertion_id);
	$stmt->execute() or die ($mysqli->error);

	$stmt = $mysqli->prepare("INSERT INTO movimientos VALUES (null, ?, ?, ?, ?, ?)") or die ($mysqli->error);
	$desc = "Registro solicitud EAR automatica (".$insertion_id.") de ".$nombres;
	//$ip = $_SERVER['REMOTE_ADDR'];
	//$host = gethostbyaddr($ip);
	$ip = '127.0.0.1';
	$host = 'localhost';
	$stmt->bind_param('issss', $usu_id_jefe, $desc, $ahora, $ip, $host);

	$stmt->execute() or die ($mysqli->error);

	$mysqli->commit();

	$to = getCorreoUsuario($v[12]);
	$cc = array();
	array_push ($cc, getCorreoUsuario($usu_id_jefe));
	array_push ($cc, getCorreoUsuario($usu_id_gerente));
	array_push ($cc, getCorreoUsuario(getUsuController()));
	array_push ($cc, getCorreoUsuario(getUsuAdmin()));
	array_push ($cc, 'mngmt@Minapp.com.pe');

	// Adjuntar pdf
	$attachString = getCartaEarSol($insertion_id, 'S');
	if (is_null($attachString)) die("Error en la generaci�n del archivo PDF, no se complet� la transacci�n. (Cadena vac�a)");
	$attachFilename = "EAR_".str_replace("/", "_", $ear_numero).".pdf";

	$subject = "Solicitud Generada y Aprobada automaticamente de EAR $ear_numero de ".$nombres;
	$body = "Se ha generado y aprobado automaticamente la solicitud de EAR $ear_numero de $nombres por el monto de ".number_format($v[8], 2, '.', ','). " $v[5].";
	// $body .= "\n\nNota al colaborador: Imprimir, firmar y hacer entregar a la brevedad el documento PDF adjunto a Administracion, gracias.";
	$body .= "\n\nEsperando el desembolso de Tesoreria";

	enviarCorreo($to, $cc, $subject, $body, $attachString, $attachFilename);


	$to = getCorreoUsuario(getUsuTesoreria());
	$cc = null;
	$subject = "Desembolso pendiente de EAR $ear_numero de ".$nombres;
	$body = "Se ha aprobado la solicitud de EAR $ear_numero de $nombres por el monto de ".number_format($v[8], 2, '.', ','). " $v[5].";
	$body .= "\n\nSe ha adjuntado a este correo el PDF de la solicitud para su revision.";
	$body .= "\n\nFavor de ingresar al modulo Administracion de la web intranet, opcion Desembolsos y realizar el desembolso respectivo.";
	enviarCorreo($to, $cc, $subject, $body, $attachString, $attachFilename);

	echo "Procesado solicitud de: $nombres ($ear_numero)<br>\n";
}

include 'datos_cerrar_bd.php';
?>
