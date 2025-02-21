<?php
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'SUP_CONT');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Importar Gastos Colobjects (GCO COBJ) - Administraci�n MinappES</title>
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

<h1>Importar Gastos Colobjects (GCO COBJ) Formato Excel</h1>

<b>Seleccione el archivo excel de Gastos Colobjects (GCO COBJ) para importar al sistema:</b><br>

<br>

<form action="colobjects_imp_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">Archivo excel:</td><td><input name="file" type="file" id="file" /></td></tr>
<tr><td></td><td><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<p>El formato aceptado del archivo excel es el siguiente: (no usar encabezados)<br></p>
<ul>
	<li>Columna A: Nombre o descripcion del colobject
	<li>Columna B: Codigo del colobject (automaticamente a partir del codigo se determina si es Centro de Costos, WBS o Internal Order
	<li>Columna C: Estado del colobject (0 = inactivo, 1 = activo, 2 = eliminar de la base)
</ul>
<p><img src='img/ejemplo_formato_excel.png' border='1' title='Ejemplo de formato excel valido'><br></p>

<?php include ("footer.php"); ?>
</body>
</html>
