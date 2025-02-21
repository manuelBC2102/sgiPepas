<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");
if (!isset($f_id)) die ('ERROR: Solicitud no v�lida');
$id = filter_var($f_id, FILTER_SANITIZE_STRING);

$arr = getLoteCajaChicaInfo($id);
// Si no existe el lote de esa caja chica se genera error
if (count($arr) == 0) {
	echo "<font color='red'><b>ERROR: No se encuentra lote de caja chica.</b></font><br>";
	exit;
}
else {
	list($ccl_id, $cch_nombre, $ccl_numero, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ccl_monto_ini, $ccl_gti, $ccl_dg_json, $ccl_cta_bco,
		$ccl_ape_fec, $ape_usu_nombre, $ccl_cie_fec, $cie_usu_nombre,
		$ccl_aprob_fec, $aprob_usu_nombre, $ccl_act_fec, $act_usu_nombre,
		$ccl_monto_usado, $est_id, $est_nom, $suc_nombre,
		$ccl_ret, $ccl_ret_no, $ccl_det, $ccl_det_no, $ccl_gast_asum, $ccl_pend, $cch_id) = $arr;
}

if ($f_o=='Grabacion') {
	$resul = 'grabada exitosamente';
}
else if ($f_o=='Aprobar') {
	$resul = 'aprobada y enviada a Contabilidad y Tesoreria para su posterior revisi�n, verificacion y desembolso';
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
<title>Revisar Documentos Caja Chica - Administraci�n - Minapp</title>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
	.titulo {font-size: 14pt; font-family: arial,helvetica}
</style>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Revisi�n de Documentos Caja Chica</h1>

<p>Estado: El lote de caja chica <?php echo $ccl_numero; ?> ha sido <?php echo $resul; ?>.<br></p>

<p><a href="cch_lote_aprob.php">Regresar a Caja Chica Lotes pendientes de Aprobar</a><br></p>

<?php include ("footer.php"); ?>
</body>
</html>
