<?php
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_otro_jefe_usu_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}

$otro_jefe_usu_id = abs((int) filter_var($f_otro_jefe_usu_id, FILTER_SANITIZE_NUMBER_INT));

$arr = getUsuRegOtroSlavesJefesIds($_SESSION['rec_usu_id']);
if(in_array($otro_jefe_usu_id, $arr)) {
	$count = 1;
}
else {
	$count = 0;
}
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Menu operaciones por otro jefe - Administraci�n MinappES</title>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
</style>
<style>
.iconos {
	vertical-align:text-top;
	cursor: pointer;
}
</style>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Menu operaciones por otro jefe</h1>

<b>Jefe seleccionado: <?php echo getUsuarioNombre($otro_jefe_usu_id); ?></b><br>

<br>

<a href="oper_otro_jefe_ear_consulta.php?cons_id=2&est_id=1&otro_jefe_usu_id=<?php echo $otro_jefe_usu_id; ?>">Aprobar Solicitudes de EAR</a><br>
<a href="oper_otro_jefe_ear_aprobacion.php?otro_jefe_usu_id=<?php echo $otro_jefe_usu_id; ?>">Aprobar Liquidaciones de EAR</a><br>
<a href="oper_otro_jefe_ear_consulta.php?cons_id=2&otro_jefe_usu_id=<?php echo $otro_jefe_usu_id; ?>">Reportes de EAR (Todos las solicitudes y liquidaciones)</a><br>
<a href="oper_otro_jefe_ear_consulta.php?cons_id=2&est_id=2&otro_jefe_usu_id=<?php echo $otro_jefe_usu_id; ?>">Reportes de Solicitudes EAR aprobadas</a><br>
<a href="oper_otro_jefe_ear_consulta.php?cons_id=2&est_id=3&otro_jefe_usu_id=<?php echo $otro_jefe_usu_id; ?>">Reportes de Solicitudes EAR rechazadas</a><br>
<a href="oper_otro_jefe_movi_consulta.php?cons_id=2&otro_jefe_usu_id=<?php echo $otro_jefe_usu_id; ?>">Consultar Planillas de Movilidad de los colaboradores a cargo del jefe seleccionado</a><br>

<br>

<?php include ("footer.php"); ?>
</body>
</html>
