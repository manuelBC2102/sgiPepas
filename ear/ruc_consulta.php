<?php
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_ruc)) {
	$ruc = "";
}
else {
	$ruc = strtoupper(trim(filter_var($f_ruc, FILTER_SANITIZE_STRING)));
	
	list($prov_nom, $ruc_act, $ruc_ret, $ruc_hab, $prov_factura, $prov_provincia, $http_status) = getRucDatos($ruc);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Consultar RUC - Administraci�n MinappES</title>
<script>
function checkForm(form) {
	form.btn1.disabled = true;
	form.btn1.value = "Por favor espere...";
	return true;
}
</script>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
</style>
<style>
.iconos {
	vertical-align:text-top;
	cursor: pointer;
}

.encabezado_h {
	background-color: silver;
	text-align: center;
	font-weight: bold;
}
</style>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Consultar RUC</h1>

<b>Ingrese RUC:</b><br>

<br>

<form action="ruc_consulta.php" method="post" enctype="multipart/form-data" onSubmit="return checkForm(this);">
<table>
<tr><td align="right">RUC:</td><td><input type="text" id="ruc" name="ruc" maxlength="11" size="16" /></td></tr>
<tr><td></td><td><input type="submit" name="btn1" value="Enviar solicitud"></td></tr>
</table>
</form>

<br>

<?php
if (isset($ruc_act)) {
	if ($ruc_act!=-1) {
?>
<table>
<tr><td class="encabezado_h"></td><td class="encabezado_h"><b>Resultados consulta RUC</b></td></tr>
<tr><td align="right">RUC:</td><td><?php echo $ruc; ?></td></tr>
<tr><td align="right">Nombre:</td><td><?php echo $prov_nom; ?></td></tr>
<tr><td align="right">Activo:</td><td><?php echo getSino($ruc_act); ?></td></tr>
<tr><td align="right">Agente Retenci�n:</td><td><?php echo getSino($ruc_ret); ?></td></tr>
<tr><td align="right">Habido:</td><td><?php echo getSino($ruc_hab); ?></td></tr>
<tr><td align="right">Emite Facturas:</td><td><?php echo getSino($prov_factura); ?></td></tr>
<tr><td align="right">Provincia:</td><td><?php echo $prov_provincia; ?></td></tr>
</table>
<?php
	}
	else if ($http_status<>'000' && $http_status<>'200') {
		echo "<font color='red'><b>ERROR: $http_status HTTP servidor SUNAT</b></font><br>";
	}
	else {
		echo "<font color='red'><b>ERROR: RUC $ruc no se encontr� o hay problemas con la conexion SUNAT</b></font><br>";
	}
}
?>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
