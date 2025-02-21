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

$arr = getLiqConceptosCodigos();

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($conc_cod, $conc_nom, $mon_nom, $mon_iso, $mon_img, $conc_cta_cont, $conc_act, $conc_acf, $conc_cve, $conc_fmt_glosa) = getLiqConceptoInfo($id);
$cod = substr($conc_cod, 0, 2);
$subcod = substr($conc_cod, 2, 2);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Editar concepto - Administraci�n MinappES</title>
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

<h1>Editar concepto</h1>

<b>Datos del concepto:</b><br>

<br>

<form action="mant_conceptos_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr>
	<td align="right">C�digo:</td>
	<td>
		<select name="cod" disabled>
<?php
foreach ($arr as $v) {
	echo "\t\t\t<option value='$v[1]' ".($cod==$v[1]?'selected':'').">($v[1]) $v[2]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr><td align="right">Subc�digo:</td><td><input type="text" id="subcod" name="subcod" maxlength="2" size="3" value="<?php echo $subcod; ?>" disabled /></td></tr>
<tr><td align="right">Nombre:</td><td><input type="text" id="nom" name="nom" maxlength="200" size="128" value="<?php echo $conc_nom; ?>" /></td></tr>
<tr><td align="right">Moneda:</td><td><?php echo $mon_nom." <img src='$mon_img' class='iconos' title='$mon_iso'>";?></td></tr>
<tr><td align="right">Cuenta Contable:</td><td><input type="text" id="cta_cont" name="cta_cont" maxlength="48" size="64" value="<?php echo $conc_cta_cont; ?>" /></td></tr>
<tr>
	<td align="right">Activo:</td>
	<td>
		<select name="act">
			<option value="1" <?php echo ($conc_act==1?'selected':''); ?>>Si</option>
			<option value="0" <?php echo ($conc_act==0?'selected':''); ?>>No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Activo Fijo:</td>
	<td>
		<select name="acf">
			<option value="1" <?php echo ($conc_acf==1?'selected':''); ?>>Si</option>
			<option value="0" <?php echo ($conc_acf==0?'selected':''); ?>>No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Validacion de la glosa:</td>
	<td>
		<select name="cve">
			<option value="1" <?php echo ($conc_cve==1?'selected':''); ?>>Placa + km</option>
			<option value="2" <?php echo ($conc_cve==2?'selected':''); ?>>Placa + peaje</option>
			<option value="3" <?php echo ($conc_cve==3?'selected':''); ?>>Placa + glosa normal sin validacion</option>
			<option value="4" <?php echo ($conc_cve==4?'selected':''); ?>>Origen/Destino</option>
			<option value="0" <?php echo ($conc_cve==0?'selected':''); ?>>No</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Formato exportacion glosa:</td>
	<td>
		<input type="text" id="fmt_glosa" name="fmt_glosa" maxlength="100" size="64" value="<?php echo $conc_fmt_glosa; ?>" />
		<br>
		<span><i>Notas: Si la glosa no cambia de formato no introducir ningun texto.</i></span>
		<br>
		<span><i>Ejemplo de formato: <b>%PL - ESTACIONAMIENTO - %U</b></i></span>
		<br>
		<span><i>Valores reemplazables: <b>%PL</b> - placa, <b>%PE</b> - peaje, <b>%G</b> - glosa original, <b>%U</b> - iniciales del usuario, <b>%K</b> - kilometraje</i></span>
		<br>
		<span><i>Valores reemplazables: <b>%PR</b> - proyecto, <b>%C</b> - ciudad del proveedor</i></span>
	</td>
</tr>
<tr><td></td><td><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="submit" value="Enviar solicitud"></td></tr>
</table>
</form>
<br>

<?php include ("footer.php"); ?>
</body>
</html>
