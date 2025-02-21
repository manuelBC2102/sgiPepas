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

$arr = getColObjectsTipos();

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($gco_id, $gti_id, $gco_nom, $gco_cobj, $gco_act) = getColObjectsInfo($id);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Editar colobject - Administraci�n MinappES</title>
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

<h1>Editar colobject</h1>

<b>Datos del colobject:</b><br>

<br>

<form action="mant_colobjects_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr>
	<td align="right">Tipo:</td>
	<td>
		<select name="gti">
<?php
foreach ($arr as $v) {
	echo "\t\t\t<option value='$v[0]' ".($gti_id==$v[0]?'selected':'').">$v[1]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr><td align="right">Nombre:</td><td><input type="text" id="nom" name="nom" maxlength="64" size="96" value="<?php echo $gco_nom; ?>" /></td></tr>
<tr><td align="right">GCO COBJ:</td><td><input type="text" id="gco" name="gco" maxlength="48" size="64" value="<?php echo $gco_cobj; ?>" /></td></tr>
<tr>
	<td align="right">Activo:</td>
	<td>
		<select name="act">
			<option value="1" <?php echo ($gco_act==1?'selected':''); ?>>Si</option>
			<option value="0" <?php echo ($gco_act==0?'selected':''); ?>>No</option>
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
