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

if (!isset($f_anio)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$anio = abs((int) filter_var($f_anio, FILTER_SANITIZE_NUMBER_INT));
	$mes = abs((int) filter_var($f_mes, FILTER_SANITIZE_NUMBER_INT));
}

list($diatope) = getDiaTopeInfo($anio, $mes);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Editar dia tope - Administraci�n MinappES</title>
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

<h1>Editar dia tope</h1>

<b>Datos del dia tope:</b><br>

<br>

<form action="mant_calendario_dctos_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">A�o:</td><td><input type="text" id="anio" name="anio" maxlength="4" size="6" value="<?php echo $anio; ?>" readonly /></td></tr>
<tr><td align="right">Mes:</td><td><input type="text" id="mes" name="mes" maxlength="2" size="6" value="<?php echo $mes; ?>" readonly /></td></tr>
<tr><td align="right">Dia tope:</td><td><input type="text" id="diatope" name="diatope" maxlength="2" size="6" value="<?php echo $diatope; ?>" /></td></tr>
<tr><td></td><td><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
