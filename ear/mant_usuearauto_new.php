<?php
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

$rfecha2 = date("Y-m-d");
$rfecha4 = date("Y-m-d", strtotime('+1 years'));

$arr = getUsuLista();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Registrar nueva solicitud EAR automatica - Administraci�n MinappES</title>
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
		onClose: function( selectedDate ) {	$( "#rfecha3" ).datepicker( "option", "minDate", selectedDate ); }
	});
	$( "#rfecha" ).datepicker( $.datepicker.regional[ "es" ] );
	$( "#rfecha" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
	$( '#rfecha' ).datepicker( 'setDate', '<?php echo $rfecha2; ?>' );
	$( "#rfecha" ).datepicker( "option", "dateFormat", "D, d M yy" );
	
	$( "#rfecha3" ).datepicker({
		numberOfMonths: 1,
		altField: "#rfecha4",
		altFormat: "yy-mm-dd",
		onClose: function( selectedDate ) { $( "#rfecha" ).datepicker( "option", "maxDate", selectedDate ); }
	});
	$( "#rfecha3" ).datepicker( $.datepicker.regional[ "es" ] );
	$( "#rfecha3" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
	$( '#rfecha3' ).datepicker( 'setDate', '<?php echo $rfecha4; ?>' );
	$( "#rfecha3" ).datepicker( "option", "dateFormat", "D, d M yy" );
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

<h1>Registrar nueva solicitud EAR automatica</h1>

<b>Datos de la solicitud EAR automatica:</b><br>

<br>

<form action="mant_usuearauto_new_p.php" method="post" enctype="multipart/form-data">
<table>
<tr>
	<td align="right">Colaborador:</td>
	<td>
		<select name="usu">
<?php
foreach ($arr as $v) {
	echo "\t\t\t<option value='$v[0]'>$v[1]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr><td align="right">Fecha inicio:</td><td><input type="text" id="rfecha" name="rfecha" readonly><input type="hidden" id="rfecha2" name="rfecha2" /></td></tr>
<tr><td align="right">Fecha fin:</td><td><input type="text" id="rfecha3" name="rfecha3" readonly><input type="hidden" id="rfecha4" name="rfecha4" /></td></tr>
<tr>
	<td align="right">Zona:</td>
	<td>
		<select name="zona">
			<option value="01">Nacional</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Moneda:</td>
	<td>
		<select name="moneda">
			<option value="1">PEN</option>
			<option value="2">USD</option>
		</select>
	</td>
</tr>
<tr><td align="right">Cuenta de banco:</td><td><input type="text" id="cta" name="cta" maxlength="32" size="16" /> <i>Para soles puede dejarse en blanco y se tomar� la cuenta que esta grabada en nisira, para dolares es obligatorio indicar la cuenta.</i></td></tr>
<tr><td align="right">Monto:</td><td><input type="text" id="monto" name="monto" maxlength="10" size="16" /></td></tr>
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
