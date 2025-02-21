<?php
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

$arr = getUsuLista();
$arr2 = getPermisosGruposLista();

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($grp_id, $usu_id) = getPermisoInfo($id);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Editar permiso - Administraci�n MinappES</title>
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

<h1>Editar permiso</h1>

<b>Datos del permiso:</b><br>

<br>

<form action="mant_permisos_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr>
	<td align="right">Se asigna a:</td>
	<td>
		<select name="usu">
<?php
foreach ($arr as $v) {
	echo "\t\t\t<option value='$v[0]'".($usu_id==$v[0]?' selected':'').">$v[1]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Al grupo de permisos:</td>
	<td>
		<select name="grp">
<?php
foreach ($arr2 as $v) {
	echo "\t\t\t<option value='$v[0]'".($grp_id==$v[0]?' selected':'').">$v[1] ($v[2])</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr><td></td><td><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
