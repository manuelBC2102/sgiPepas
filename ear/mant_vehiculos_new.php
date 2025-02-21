<?php
include ("seguridad.php");
include 'func.php';
include 'parametros.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pASISTENTE_ADMINISTRATIVO);//permiso para cecilia
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

$arrUsu = getUsuLista();
$arrMar = getVehMarcasLista();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!--<title>Registrar nuevo vehiculo - Administraci�n - Minapp</title>-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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

<h1>Registrar nuevo vehiculo</h1>

<p><b>Nota: Solo registrar vehiculos <u>propios</u> de la empresa</b></p>

<b>Datos del nuevo vehiculo:</b><br>

<br>

<form action="mant_vehiculos_new_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">Placa:</td><td><input type="text" id="pla" name="pla" maxlength="7" size="12" /> <i>Incluir el guion (Ejemplo: AAA-999)</i></td></tr>
<tr>
	<td align="right">Marca:</td>
	<td>
		<select name="mar">
<?php
foreach ($arrMar as $v) {
	echo "\t\t\t<option value='$v[0]'>$v[1]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr><td align="right">Modelo:</td><td><input type="text" id="mod" name="mod" maxlength="64" size="32" /></td></tr>
<tr>
	<td align="right">Usuario asignado:</td>
	<td>
		<select name="usu">
			<option value='-1'>Ninguno</option>
<?php
foreach ($arrUsu as $v) {
	echo "\t\t\t<option value='$v[0]'>$v[1]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Activo:</td>
	<td>
		<select name="act">
			<option value="1" selected>Si</option>
			<option value="0">No</option>
		</select>
	</td>
</tr>
<tr><td></td><td><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
