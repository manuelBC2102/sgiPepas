<?php

require_once __DIR__ . '/../../modeloNegocio/almacen/DocumentoEarNegocio.php';

$order[0]['column'] = 8;
$order[0]['dir'] = 'desc';
$start = 0;
$criterios[0]['tipoDocumento'][] = 247;
$opcionId = 321;
$elemntosFiltrados = 10000;
$columns[8]['data'] = 'fecha_creacion_ord';
$columns[8]['name'] = '';
$columns[8]['searchable'] = true;
$columns[8]['orderable'] = true;
$columns[8]['search']['value'] = '';
$columns[8]['search']['regex'] = false;

$respuesta = DocumentoEarNegocio::create()->obtenerDocumentosXCriterios($opcionId, $criterios, $elemntosFiltrados, $columns, $order, $start);

//$respuesta = $respuesta->datos->$arrayTipoMetodo['respuesta'];

$s = '<table border="1">';

//CABECERA
$s .= '<tr>';
foreach ($respuesta[0] as $key => $v) {
    $s .= '<th>' . $key . '</th>';
}
$s .= '</tr>';

//CUERPO
foreach ($respuesta as $r) {
    $s .= '<tr>';
    foreach ($r as $v) {
        $s .= '<td>' . $v . '</td>';
    }
    $s .= '</tr>';
}
$s .= '</table>';
echo $s;


