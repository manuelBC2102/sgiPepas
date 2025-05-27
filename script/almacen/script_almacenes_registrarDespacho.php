<?php

include_once __DIR__ . '/../../modeloNegocio/almacen/AlmacenesNegocio.php';

$paqueteId = 2;
$usuarioId = 169;
$empresaId = 2;

// $campos = array(
//     array(
//         "tipo" => 7,
//         "valor" => ""
//     ),
//     array(
//         "tipo" => 8,
//         "valor" => ""
//     ),
//     array(
//         "tipo" => 9,
//         "valor" => ""
//     ),
//     array(
//         "tipo" => 54,
//         "valor" => 64
//     ),
//     array(
//         "tipo" => 53,
//         "valor" => 71
//     ),
//     array(
//         "tipo" => 41,
//         "valor" => 1
//     ),
//     array(
//         "tipo" => 23,
//         "valor" => 1903
//     ),
//     array(
//         "tipo" => 2,
//         "valor" => "1521MM"
//     )
// );

$alamcen_origen = 71; //fijo = direccion - ciudad - provincia - departamento - ubigeo 
$alamcenDestino = 64; //direccion - ciudad - provincia - departamento - ubigeo 
$vehiculoId = 5; // placa - ruc transportista - razón social - (direccion fiscal - ciudad - provincia - departamento - ubigeo)
$pesaje = 10;
$usuarioId = 1;

$detalle = array(array(
    94
));

$respuestaAlamcenes = AlmacenesNegocio::create()->generarDespacho($alamcenDestino, $vehiculoId, $pesaje, $usuarioId, $detalle);

echo json_encode($productos);


//respuesta
$detalle = array(array(
    "paquete_id" => 94,
    "descripcion_bien" => "",
    "distribucion" => "1X5",
    "unidad_minera" => "Pampamarquino uno"
));

