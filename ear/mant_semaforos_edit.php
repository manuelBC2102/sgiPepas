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

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($est_id, $est_nom, $val_min_verde, $val_min_ambar, $val_min_rojo, $tabla_nom, $sema_id) = getSemaforosInfo($id);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Editar semaforo - Administraci�n MinappES</title>
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

<h1>Editar semaforo</h1>

<b>Datos del semaforo:</b><br>

<br>

<form action="mant_semaforos_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">Tabla:</td><td><?php echo $tabla_nom; ?></td></tr>
<tr><td align="right">Estado:</td><td><?php echo $est_nom; ?></td></tr>
<tr><td align="right">Valor minimo para verde:</td><td><input type="text" id="ver" name="ver" maxlength="11" size="16" value="<?php echo $val_min_verde; ?>" /> Se recomienda dejarlo en cero</td></tr>
<tr><td align="right">Valor minimo para ambar:</td><td><input type="text" id="amb" name="amb" maxlength="11" size="16" value="<?php echo $val_min_ambar; ?>" /></td></tr>
<tr><td align="right">Valor minimo para rojo:</td><td><input type="text" id="roj" name="roj" maxlength="11" size="16" value="<?php echo $val_min_rojo; ?>" /></td></tr>
<tr><td></td><td><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
