<?php
include ("seguridad.php");
include 'func.php';
include 'parametros.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

$arr = getVehiculosInfo($id);
if (empty($arr)) {
	echo "<font color='red'><b>ERROR: Valor no existe</b></font><br>";
	exit;
}
list($veh_id, $veh_placa, $vm_id, $veh_modelo, $usu_id, $veh_act, $vm_nombre, $usu_nombre) = $arr;

$arrUsu = getUsuLista();
$arrMar = getVehMarcasLista();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!--<title>Editar vehiculo - Administraci�n - Minapp</title>-->
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

<h1>Editar vehiculo</h1>

<b>Datos del vehiculo:</b><br>

<br>

<form action="mant_vehiculos_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">Placa:</td><td><input type="text" id="pla" name="pla" maxlength="7" size="12" value="<?php echo $veh_placa; ?>" /> <i>Incluir el guion (Ejemplo: AAA-999)</i></td></tr>
<tr>
	<td align="right">Marca:</td>
	<td>
		<select name="mar">
<?php
foreach ($arrMar as $v) {
	echo "\t\t\t<option value='$v[0]'".($vm_id==$v[0]?' selected':'').">$v[1]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr><td align="right">Modelo:</td><td><input type="text" id="mod" name="mod" maxlength="64" size="32" value="<?php echo $veh_modelo; ?>" /></td></tr>
<tr>
	<td align="right">Usuario asignado:</td>
	<td>
		<select name="usu">
			<option value='-1'>Ninguno</option>
<?php
foreach ($arrUsu as $v) {
	echo "\t\t\t<option value='$v[0]'".($usu_id==$v[0]?' selected':'').">$v[1]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Activo:</td>
	<td>
		<select name="act">
			<option value="1"<?php echo ($veh_act==1?' selected':''); ?>>Si</option>
			<option value="0"<?php echo ($veh_act==0?' selected':''); ?>>No</option>
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
