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

$arr = getLiqConceptosSubcodigos();

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($detr_id, $conc_cod, $conc_nom, $detr_desde_fec, $detr_tasa, $detr_min_monto) = getDetraccionInfo($id);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Editar detraccion - Administraci�n MinappES</title>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
</style>

<!--Seccion jQuery-->
<script src="js/jquery-1.8.3.min.js"></script>
<script src="js/jquery-ui-1.9.2.custom.js"></script>
<!--Seccion jQuery-->

<!--Seccion Date Picker-->
<link href="css/ui-lightness/jquery-ui-1.9.2.custom.css" rel="stylesheet">
<script type="text/javascript" src="i18n/jquery.ui.datepicker-es.js"></script>
<script>
$(function() {
	$( "#rfecha" ).datepicker({
		numberOfMonths: 1,
		altField: "#rfecha2",
		altFormat: "yy-mm-dd",
	});
	$( "#rfecha" ).datepicker( $.datepicker.regional[ "es" ] );
	$( "#rfecha" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
	$( '#rfecha' ).datepicker( 'setDate', '<?php echo $detr_desde_fec; ?>' );
	$( "#rfecha" ).datepicker( "option", "dateFormat", "D, d M yy" );
});
</script>

<style>
.iconos {
	vertical-align:text-top;
	cursor: pointer;
}
</style>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Editar detraccion</h1>

<b>Datos de la detraccion:</b><br>

<br>

<form action="mant_detracciones_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr>
	<td align="right">Concepto:</td>
	<td>
		<select name="cod">
<?php
echo "\t\t\t<option value='$conc_cod'>($conc_cod) $conc_nom</option>\n";
echo "\t\t\t<option disabled>---</option>\n";

foreach ($arr as $v) {
	echo "\t\t\t<option value='$v[0]'>($v[0]) $v[1]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr><td align="right">Aplica desde:</td><td><input type="text" id="rfecha" name="rfecha" readonly><input type="hidden" id="rfecha2" name="rfecha2" /></td></tr>
<tr><td align="right">Tasa (en %):</td><td><input type="text" id="tas" name="tas" maxlength="7" size="16" value="<?php echo $detr_tasa; ?>" /></td></tr>
<tr><td align="right">Monto minimo:</td><td><input type="text" id="min" name="min" maxlength="10" size="16" value="<?php echo $detr_min_monto; ?>" /></td></tr>
<tr><td></td><td><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
