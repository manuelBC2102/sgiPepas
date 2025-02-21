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

$arr = getUsuLista();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Registrar nuevo registrar por otro usuario - Administraci�n MinappES</title>
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

<h1>Registrar nuevo registrar por otro usuario</h1>

<b>Datos del registrar por otro usuario:</b><br>

<br>

<form action="mant_usuregotr_new_p.php" method="post" enctype="multipart/form-data">
<table>
<tr>
	<td align="right">Se designa a:</td>
	<td>
		<select name="master">
<?php
foreach ($arr as $v) {
	echo "\t\t\t<option value='$v[0]'>$v[1]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Para que registre por:</td>
	<td>
		<select name="slave">
<?php
foreach ($arr as $v) {
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
