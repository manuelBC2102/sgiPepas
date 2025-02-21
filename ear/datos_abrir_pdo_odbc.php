<?php
include 'parametros.php';
//Datos de conexion al servidor de base de datos

//Asignar modo de operacion
// 1 (prueba) servidor de prueba
// 2 (produccion) conectado con mssql server de nisira real
$debug_db = $debug;

if ($debug_db == 1) {
	$dsn = "nisira";
	//Debe ser de sistema no de usuario
	$usuario = $usuariopruebas;
	$clave = $clavepruebas;
} elseif ($debug_db == 2) {
	$dsn = "nisira";
	//Debe ser de sistema no de usuario
	$usuario = $usuarioproduccion;
	$clave = $claveproduccion;
}

$pdo = new PDO("odbc:".$dsn, $usuario, $clave);
$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
?>
