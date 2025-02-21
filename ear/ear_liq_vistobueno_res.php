<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");
if (!isset($f_id)) die ('ERROR: Solicitud no v�lida');
$id = filter_var($f_id, FILTER_SANITIZE_STRING);

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
	$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
	$usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id) = getSolicitudInfo($id);

if ($f_o=='Grabacion') {
	$resul = 'grabada exitosamente';
}
else if ($f_o=='VistoBueno') {
	$resul = 'enviada a Contabilidad para su procesamiento';
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
<title>Visto Bueno Liquidaci�n EAR - Administraci�n - Minapp</title>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
	.titulo {font-size: 14pt; font-family: arial,helvetica}
</style>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Visto Bueno Liquidaci�n de Vi�ticos y/o Entregas a Rendir (EAR) <?php echo getNomZona($zona_id); ?></h1>

<p>Estado: Su liquidaci�n <?php echo $ear_numero; ?> ha sido <?php echo $resul; ?>.<br></p>

<p><a href="ear_vistobueno.php">Regresar a Visto Bueno de Entregas a Rendir</a><br></p>

<?php include ("footer.php"); ?>
</body>
</html>
