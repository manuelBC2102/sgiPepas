<?php

include_once __DIR__ . '/../../modeloNegocio/almacen/AlmacenesNegocio.php';

$organizadorId = 81;//Ananquel
$paqueteId = 2;
$usuarioId = 169;
$respuestaAlamcenes = AlmacenesNegocio::create()->almacenarPaquete($organizadorId, $paqueteId, $usuarioId);

echo json_encode($productos);
