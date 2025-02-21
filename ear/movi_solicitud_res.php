<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");
if (!isset($f_id)) die ('ERROR: Solicitud no v�lida');
$id = filter_var($f_id, FILTER_SANITIZE_STRING);

list($pla_numero, $est_id, $pla_reg_fec, $ear_numero, $tope_maximo, $usu_id, $ear_id, $est_nom) = getPlanillaMovilidadInfo($id);

if ($f_o=='Borrador') {
	$resul = 'grabada exitosamente, puede volverla a editar posteriormente.';
}
else {
	echo "<font color='red'><b>ERROR: Operaci&oacute;n err&oacute;nea</b></font><br>";
	exit;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!--<title>Registrar Planilla de Movilidad - Administraci�n - Minapp</title>-->
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
	.titulo {font-size: 14pt; font-family: arial,helvetica}
</style>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Registrar planilla de movilidad</h1>

<p>Estado: Su planilla de movilidad <?php echo $pla_numero; ?> ha sido <?php echo $resul; ?>.<br></p>

<?php include ("footer.php"); ?>
</body>
</html>
