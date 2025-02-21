<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'SUP_CONT');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ANA_CONT');
$count += getPermisosPagina($_SESSION['rec_usu_id'], basename(__FILE__));
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Registrar nuevo proveedor - Administraci�n - Minapp</title>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
</style>
<style>
.iconos {
	vertical-align:text-top;
	cursor: pointer;
}
</style>
<script>
function abrir(url) {
	open(url,'','top=150,left=150,width=800,height=600') ;
}
</script>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Registrar nuevo proveedor</h1>

<p>Enlace directo a Consulta RUC en web SUNAT: <a href='javascript:abrir("http://ww1.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias");'><img src='img/sunat.jpg' title='Consulta RUC en web SUNAT (Abre nueva ventana)'></a></p>

<br>

<b>Datos del proveedor:</b><br>

<br>

<form action="mant_proveedores_new_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">RUC:</td><td><input type="text" id="ruc" name="ruc" maxlength="11" size="16" /></td></tr>
<tr><td align="right">Nombre:</td><td><input type="text" id="nom" name="nom" maxlength="128" size="128" /></td></tr>
<tr>
	<td align="right">Activo:</td>
	<td>
		<select name="act">
			<option value="1">Si</option>
			<option value="0">No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Agente Retenci�n:</td>
	<td>
		<select name="ret">
			<option value="1">Si</option>
			<option value="0">No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Habido:</td>
	<td>
		<select name="hab">
			<option value="1">Si</option>
			<option value="0">No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Emite Facturas:</td>
	<td>
		<select name="fac">
			<option value="1">Si</option>
			<option value="0">No</option>
		</select>
	</td>
</tr>
<tr><td align="right">Provincia:</td><td><input type="text" id="pro" name="pro" maxlength="100" size="128" /></td></tr>
<tr><td></td><td><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
