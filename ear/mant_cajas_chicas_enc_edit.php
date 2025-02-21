<?php
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
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

$arr = getCajasChicasEncInfo($id);
if (empty($arr)) {
	echo "<font color='red'><b>ERROR: Valor no existe</b></font><br>";
	exit;
}
list($cce_id, $cch_nombre, $cch_abrv, $usu_nombre, $cce_act, $cch_id, $usu_id) = $arr;

$arrCajas = getCajasChicasActivasLista();
$arrUsu = getUsuLista();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Editar encargado de caja chica - Administraci�n MinappES</title>
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

<h1>Editar encargado de caja chica</h1>

<b>Datos del encargado de caja chica:</b><br>

<br>

<form action="mant_cajas_chicas_enc_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr>
	<td align="right">Caja chica:</td>
	<td>
		<select name="cch">
			<option value='<?php echo $cch_id; ?>'><?php echo $cch_nombre." ($cch_abrv)"; ?></option>
			<option disabled>-----</option>
<?php
foreach ($arrCajas as $v) {
	echo "\t\t\t<option value='$v[0]'>$v[1] ($v[2])</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Encargado:</td>
	<td>
		<select name="usu">
			<option value='<?php echo $usu_id; ?>'><?php echo $usu_nombre; ?></option>
			<option disabled>-----</option>
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
			<option value="1"<?php echo ($cce_act==1?' selected':''); ?>>Si</option>
			<option value="0"<?php echo ($cce_act==0?' selected':''); ?>>No</option>
		</select>
	</td>
</tr>
<tr><td></td><td><input type="submit" value="Enviar solicitud"></td></tr>
</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
