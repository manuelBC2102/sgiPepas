<?php
include ("seguridad.php");
include 'func.php';
include 'parametros.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pSUP_CONT);
//$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Registrar nuevo viatico - Administraci�n - Minapp</title>
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

<h1>Registrar nuevo viatico</h1>

<b>Datos del viatico:</b><br>

<br>

<form action="mant_viaticos_new_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">C&oacute;digo:</td><td><input type="text" id="cod" name="cod" maxlength="10" size="20" value="03" /> Solo se pueden crear nuevos viaticos para hospedaje, los codigos deben empezar con 03.</td></tr>
<tr><td align="right">Nombre:</td><td><input type="text" id="nom" name="nom" maxlength="200" size="128" /></td></tr>
<tr><td align="right">Tope diario soles:</td><td><input type="text" id="sol" name="sol" maxlength="12" size="20" /></td></tr>
<tr><td align="right">Tope diario dolares:</td><td><input type="text" id="dol" name="dol" maxlength="12" size="20" /></td></tr>
<tr><td></td><td><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
