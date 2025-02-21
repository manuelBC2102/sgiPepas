<?php

include 'func.php';

//nube
//$dbhost = 'localhost';
//$dbuser = 'adminsys';
//$dbpass = '1m4g1n4T3c';
//$dbname = 'bhdt';

//Datos de conexion al servidor de base de datos
$dbhost = '192.168.1.11';
$dbuser = 'root';
$dbpass = 'root';
$dbname = 'bhdt_nube4';

//Abre la conexion
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname) or die('Error de coneccion a mysql');

$stmt = $mysqli->prepare('select codigo_identificacion,
                            concat(nombre," ",apellido_paterno," ",apellido_materno) as nombre_persona
                        from persona where persona_tipo_id=?
                        ');

$var=2;
$stmt->bind_param("i", $var);
$stmt->execute() or die($mysqli->error);
//$result = $stmt->get_result();
$stmt->store_result();

//while ($fila = $result->fetch_array()) {
while ($fila = fetchAssocStatement($stmt)) {
    echo 'IDs -> DNI: ' . $fila['codigo_identificacion'] . ' Nombre: ' . $fila['nombre_persona'].'<br>';
//    echo 'Ind -> DNI: ' . $fila['0'] . ' Nombre: ' . $fila['1'].'<br>';
}

//Para query() se mantiene con fetch_array()
$query = "SELECT now()";
$result = $mysqli->query($query) or die ($mysqli->error);
$fila=$result->fetch_array();
$ahora = $fila[0];

echo 'Hoy: '.$ahora;

//echo 'hola';
?>
