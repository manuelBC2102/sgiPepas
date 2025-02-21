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

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($usu_id, $usu_nombre, $usu_iniciales, $usu_estado, $gco_cobj, $usu_rol, $usu_jefe) = getUsuarioInfo($id);
$arr = getUsuariosListaRecursos();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Editar usuario - Administraci�n MinappES</title>
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

<h1>Editar usuario</h1>

<b>Datos del usuario:</b><br>

<br>

<form action="mant_usuarios_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">Nombres:</td><td><?php echo $usu_nombre; ?></td></tr>
<tr><td align="right">Iniciales:</td><td><input type="text" id="ini" name="ini" maxlength="3" size="6" value="<?php echo $usu_iniciales; ?>" /></td></tr>
<tr>
	<td align="right">Activo:</td>
	<td>
		<select name="est">
			<option value="1" <?php echo ($usu_estado==1?'selected':''); ?>>Si</option>
			<option value="0" <?php echo ($usu_estado==0?'selected':''); ?>>No</option>
		</select>
	</td>
</tr>
<tr><td align="right">GCO COBJ:</td><td><input type="text" id="gco" name="gco" maxlength="48" size="64" value="<?php echo $gco_cobj; ?>" /></td></tr>
<tr>
	<td align="right">Rol:</td>
	<td>
		<select name="rol">
			<option value="USER" <?php echo ($usu_rol=='USER'?'selected':''); ?>>USER</option>
			<option value="SUP_CONT" <?php echo ($usu_rol=='SUP_CONT'?'selected':''); ?>>SUP_CONT</option>
			<option value="REG_CONT" <?php echo ($usu_rol=='REG_CONT'?'selected':''); ?>>REG_CONT</option>
			<option value="ANA_CONT" <?php echo ($usu_rol=='ANA_CONT'?'selected':''); ?>>ANA_CONT</option>
			<option value="TESO" <?php echo ($usu_rol=='TESO'?'selected':''); ?>>TESO</option>
			<option value="COMP" <?php echo ($usu_rol=='COMP'?'selected':''); ?>>COMP</option>
			<option value="CONTROLLER" <?php echo ($usu_rol=='CONTROLLER'?'selected':''); ?>>CONTROLLER</option>
			<option value="ADMINIST" <?php echo ($usu_rol=='ADMINIST'?'selected':''); ?>>ADMINIST</option>
			<option value="TI" <?php echo ($usu_rol=='TI'?'selected':''); ?>>TI</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Jefe:</td>
	<td>
		<select name="jefe">
<?php
foreach ($arr as $v) {
	echo "\t\t\t<option value='$v[0]'".($v[0]==$usu_jefe?' selected':'').">$v[1]</option>\n";
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
