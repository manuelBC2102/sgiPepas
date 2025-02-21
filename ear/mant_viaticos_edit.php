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

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($via_id, $via_cod, $via_nom, $mon_nom, $mon_iso, $mon_img, $via_monto) = getViaticosInfo($id);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Editar viatico - Administraci�n - Minapp</title>
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

<h1>Editar viatico</h1>

<b>Datos del viatico:</b><br>

<br>

<form action="mant_viaticos_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">C&oacute;digo:</td><td><input type="text" id="cod" name="cod" maxlength="10" size="20" value="<?php echo $via_cod; ?>" disabled /></td></tr>
<tr><td align="right">Nombre:</td><td><input type="text" id="nom" name="nom" maxlength="200" size="128" value="<?php echo $via_nom; ?>" /></td></tr>
<tr><td align="right">Moneda:</td><td><?php echo $mon_nom." <img src='$mon_img' class='iconos' title='$mon_iso'>";?></td></tr>
<tr><td align="right">Tope diario:</td><td><input type="text" id="top" name="top" maxlength="12" size="20" value="<?php echo $via_monto; ?>" /></td></tr>
<tr><td></td><td><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
