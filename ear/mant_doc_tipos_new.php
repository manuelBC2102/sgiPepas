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
<title>Registrar nuevo tipo de documento - Administraci�n MinappES</title>
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

<h1>Registrar nuevo tipo de documento</h1>

<b>Datos del tipo de documento:</b><br>

<br>

<form action="mant_doc_tipos_new_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">Numero SUNAT:</td><td><input type="text" id="nro" name="nro" maxlength="2" size="3" /></td></tr>
<tr><td align="right">Descripci�n Larga:</td><td><input type="text" id="desc" name="desc" maxlength="300" size="128" /></td></tr>
<tr><td align="right">Desc. Abreviada:</td><td><input type="text" id="abrv" name="abrv" maxlength="32" size="48" /></td></tr>
<tr><td align="right">C�digo:</td><td><input type="text" id="cod" name="cod" maxlength="3" size="4" /></td></tr>
<tr>
	<td align="right">Requiere RUC:</td>
	<td>
		<select name="ruc_req">
			<option value="1" selected>Si</option>
			<option value="0">No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Aplica Retenci�n:</td>
	<td>
		<select name="apl_ret">
			<option value="1" selected>Si</option>
			<option value="0">No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Aplica Detracci�n:</td>
	<td>
		<select name="apl_det">
			<option value="1" selected>Si</option>
			<option value="0">No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">TAX Code:</td>
	<td>
		<select name="tax_code">
			<option value="1">C1</option>
			<option value="2">C0</option>
			<option value="3" selected>C1 C0</option>
			<option value="4">C9</option>
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
