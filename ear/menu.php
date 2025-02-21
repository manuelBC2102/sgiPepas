<?php
header('Content-Type: text/html; charset=utf-8');
include ("seguridad.php");
include 'parametros.php';
include 'func.php';

// Validación para Jefes y Gerentes
$permisoJefeGerente = false;
$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'JEFEOGERENTE');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pGERENTE);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pASISTENTE_ADMINISTRATIVO);//permiso para cecilia
if ($count>0) {
    $permisoJefeGerente = true;
}

// Todas las demas validaciones
// Validación para tesorería ...
$permisoTesoreria = false;
$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTESO);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
if ($count>0) {
    $permisoTesoreria = true;
}

// Validación para administrador
$permisoAdministrador = false;
$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
if ($count>0) {
    $permisoAdministrador = true;
}

// Validación para compensación
$permisoCompensacion = false;
$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], $pCOMP);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
if ($count>0) {
    $permisoCompensacion = true;
}

// Validación para contabilidad
$permisoContabilidad = false;
$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], $pSUP_CONT);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
if ($count>0) {
    $permisoContabilidad = true;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!--<title>Administraci&oacute;n - Minapp</title>-->
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
	.titulo {font-size: 14pt; font-family: arial,helvetica}
</style>
</head>
<body>

<br>

<p class="titulo">V&iacute;aticos y entregas a rendir (EAR)</p>

<p>Opciones:</p>

<p><b>Registro de solicitudes</b></p>
<a href="ear_solicitud.php?zona_id=01&mon_id=1&terc_id=1">Registro de solicitud Terceros Soles</a><br>
<a href="ear_solicitud.php?zona_id=01">Registro de solicitud Terceros D&oacute;lares</a><br>
<a href="ear_solicitud.php?zona_id=01&mon_id=1">Registro de solicitud en Soles</a><br>
<a href="ear_solicitud.php?zona_id=01">Registro de solicitud en D&oacute;lares</a><br>
<a href="ear_consulta.php?cons_id=1">Reporte y edici&oacute;n de solicitudes </a><br>
<?php
if ($permisoJefeGerente){
?>
<a href="ear_consulta.php?cons_id=2&est_id=1">Aprobación de solicitudes</a><br>
<?php
}
if($permisoTesoreria || $permisoContabilidad){
?>
<p><b>Desembolso de solicitudes</b></p>
<!--<a href="ear_pendiente_desemb.php">Desembolso (Solicitudes con desembolsos pendientes)</a><br>-->
<a href="ear_consulta.php?cons_id=4&opc_id=1">Consulta de solicitudes desembolsadas</a><br>
<?php
}
?>
<p><b>Liquidaci&oacute;n</b></p>
<a href="movi_solicitud.php">Registro de planilla de movilidad</a><br>
<a href="movi_consulta.php?cons_id=1">Consulta de planillas de movilidad</a><br>
<a href="ear_liquidacion.php">Registro de liquidaci&oacute;n</a><br>
<?php
if($permisoJefeGerente){
?>
<a href="ear_aprobacion.php">Aprobación de liquidaciones</a><br>
<?php
}
if($permisoContabilidad){
?>
<a href="ear_liq_act_vb.php">Visto bueno de liquidaciones actualizadas por contabilidad</a><br>
<?php
}
if($permisoJefeGerente){
?>
<p><b>Liquidaci&oacute;n de terceros </b></p>
<a href="movi_solicitud_otro.php">Registro de planilla de movilidad de otro usuario</a><br>
<a href="movi_consulta_otro.php?cons_id=1">Consulta de planillas de movilidad de otro usuario</a><br>
<a href="ear_liquidacion_otro.php">Registro de liquidaci&oacute;n de otro usuario</a><br>

<?php
}
if($permisoTesoreria || $permisoCompensacion){
?>
<p><b>Reembolsos</b></p>
<?php
} if($permisoTesoreria){
?>
<!--<a href="ear_liq_act_teso.php">Actualización de liquidaciones con reembolsos pendientes</a><br>-->
<a href="ear_consulta.php?cons_id=4&opc_id=2">Consulta de liquidaciones reembolsadas</a><br>
<?php
} if ($permisoCompensacion) {
?>
<!--<p><b>Devolucion</b></p>
<a href="ear_liq_act_comp.php">Actualización de liquidaciones con devoluciones pendientes</a><br>
<a href="ear_consulta.php?cons_id=5">Consulta de liquidaciones con devoluciones efectuadas</a><br>-->
<?php
}

//Administrador
$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pASISTENTE_ADMINISTRATIVO);//permiso para cecilia
if ($count>0) {
?>
<br>
<p><b>Administrador</b></p>

<a href="mant_viaticos.php">Mantenedor de vi&aacute;ticos</a><br>

<a href="mant_vehiculos.php">Mantenedor de veh&iacute;culos</a><br>

<?php
}
?>
</body>
</html>
