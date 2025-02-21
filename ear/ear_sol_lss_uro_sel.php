<?php
include ("seguridad.php");
include 'func.php';

$arr = getUsuRegOtroSlaves($_SESSION['rec_usu_id']);
$count = count($arr);
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_zona_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}

$zona_id = filter_var($f_zona_id, FILTER_SANITIZE_STRING);
$mon_id = abs((int) filter_var($f_mon_id, FILTER_SANITIZE_NUMBER_INT));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Seleccionar colaborador - Administraci�n MinappES</title>
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

<h1>Seleccionar colaborador</h1>

<b>Seleccione el colaborador:</b><br>

<br>

<form action="ear_sol_lss_uro_sel_p.php" method="post" enctype="multipart/form-data">
<table>
<tr>
	<td align="right">Colaborador:</td>
	<td>
		<select name="slave_usu_id">
<?php
foreach ($arr as $v) {
	echo "\t\t\t<option value='$v[0]'>$v[1]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr><td></td><td><input type="hidden" name="zona_id" value="<?php echo $zona_id; ?>"><input type="hidden" name="mon_id" value="<?php echo $mon_id; ?>"><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
