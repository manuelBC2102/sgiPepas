<?php
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

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($prov_id, $ruc_nro, $prov_nom, $ruc_act, $ruc_ret, $ruc_hab, $prov_factura, $prov_provincia, $ruc_chk_fec) = getProveedoresInfo($id);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Editar proveedor - Administraci�n MinappES</title>
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

<h1>Editar proveedor</h1>

<b>Datos del proveedor:</b><br>

<br>

<form action="mant_proveedores_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">RUC:</td><td><input type="text" id="ruc" name="ruc" maxlength="11" size="16" value="<?php echo $ruc_nro; ?>" disabled /></td></tr>
<tr><td align="right">Nombre:</td><td><input type="text" id="nom" name="nom" maxlength="128" size="128" value="<?php echo $prov_nom; ?>" /></td></tr>
<tr>
	<td align="right">Activo:</td>
	<td>
		<select name="act">
			<option value="1" <?php echo ($ruc_act==1?'selected':''); ?>>Si</option>
			<option value="0" <?php echo ($ruc_act==0?'selected':''); ?>>No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Agente Retenci�n:</td>
	<td>
		<select name="ret">
			<option value="1" <?php echo ($ruc_ret==1?'selected':''); ?>>Si</option>
			<option value="0" <?php echo ($ruc_ret==0?'selected':''); ?>>No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Habido:</td>
	<td>
		<select name="hab">
			<option value="1" <?php echo ($ruc_hab==1?'selected':''); ?>>Si</option>
			<option value="0" <?php echo ($ruc_hab==0?'selected':''); ?>>No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Emite Facturas:</td>
	<td>
		<select name="fac">
			<option value="1" <?php echo ($prov_factura==1?'selected':''); ?>>Si</option>
			<option value="0" <?php echo ($prov_factura==0?'selected':''); ?>>No</option>
		</select>
	</td>
</tr>
<tr><td align="right">Provincia:</td><td><input type="text" id="pro" name="pro" maxlength="100" size="128" value="<?php echo $prov_provincia; ?>" /></td></tr>
<tr><td align="right">Ultima fecha de revisi�n:</td><td><input type="text" id="ult" name="ult" maxlength="11" size="16" value="<?php echo $ruc_chk_fec; ?>" disabled /></td></tr>
<tr><td></td><td><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
