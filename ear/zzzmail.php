<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include dirname(dirname(__FILE__))."/Mailer/Entidades/ConstructorMail.php";

$insertion_id = 1098;
$usu_id_jefe = 75;
$nombres = 'JEAN PIERE ALBERTO ALZAMORA MONTERO';
$f_totalviaticos = 400.00;
$mon_id = 1;
list($mon_nom, $mon_iso, $mon_simb, $mon_img) = getNomMoneda($mon_id);

include 'datos_abrir_bd.php';

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

$subject = "Solicitud Registrada de EAR $ear_numero de ".$nombres;

include 'datos_cerrar_bd.php';

	/*
	 * @version 1.0
	 * @function   : enviarCorreoAprobacion
	 * @parametros :
	 * -------------------------
	 * @id                : Id de la tabla a actualizar.
	 * @aprobador         : Usuario AD del aprobador, necesario para el mail y la actualizaci�n.
	 * @location          : Ruta + nombre del archivo php (omitiendo '.php') donde se realiza la aprobaci�n (no el proceso _p). Ejemplo "recursos/aprob_vacadel"
	 * @detalle_operacion : Descripci�n de la operaci�n a realizar.
	 * @operacion         : Nombre de la operacion a realizar. Ajustar seg�n el template a usar
	 * @template          : (Opcional) Nombre del template a utilizar para armar el correo, por defecto "template_1"
	 * @ventana           : (Opcional) Ruta + nombre del archivo donde se muestra la ventana de aprobacion, por defecto la misma que @location
	 */
	$id                = $insertion_id;
	$aprobador         = getUsuAd($usu_id_jefe);
	$location          = "admin/ear_sol_vistobueno";
	$detalle_operacion = "Se ha registrado la solicitud de EAR $ear_numero de ".$nombres." por el monto de ".number_format($f_totalviaticos, 2, '.', ','). " $mon_nom";
	$operacion         = "EAR $ear_numero";
	$template          = "template_1";
	$ventana           = "admin/ear_consulta.php?cons_id=2&est_id=1";
	ConstructorMail::enviarCorreoAprobacion($id, $aprobador, $location, $detalle_operacion, $operacion, $template, $ventana, array("subject" => $subject));

?>
