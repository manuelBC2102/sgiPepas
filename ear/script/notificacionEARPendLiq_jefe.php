<?php
// Notificaciones a usuarios por correo electronico.
header('Content-Type: text/html; charset=UTF-8');
include '../func.php';
include '../parametros.php';

// Obtiene la lista de usuarios con EAR
$dataUsuarios = getUsuariosEARsinliqListaJefe();
list($plantillaDestinatario, $plantillaAsunto, $plantillaCuerpo) = obtenerPlantillaEmailSGI(8);
$maximoRegistros = 50;
$subject = "EAR PENDIENTE DE LIQUIDACIÓN";
foreach ($dataUsuarios as $item) {
	$data = getListaEARXId($item["ear_id"], $maximoRegistros);
        $listaEar = "";

	foreach ($data as $v) {
            $listaEar .= $v[1] . " (" . $v[2] . ") " . $v[15] . " dias sin liquidar.<br>";
        }

        $body = "Estimado(a) <b>" . $item['nombre'] . "</b>, las siguientes EAR indicadas a continuación, aun no fueron liquidadas.";
        $body .= "<br><br>";
        $body .= $listaEar;

        $plantillaCuerpoTemp = $plantillaCuerpo;
        $plantillaCuerpoTemp = str_replace("[|asunto|]", $subject, $plantillaCuerpoTemp);
        $plantillaCuerpoTemp = str_replace("[|cuerpo|]", $body, $plantillaCuerpoTemp);


	include '../datos_abrir_bd.php';

        $emailEnvioId = insertarEmailEnvioSGIConeccion($mysqli, $item['email'], $subject, $plantillaCuerpoTemp, $item['usuario_id'], null, null);

        include '../datos_cerrar_bd.php';
        echo $emailEnvioId."<br>";
}
?>

