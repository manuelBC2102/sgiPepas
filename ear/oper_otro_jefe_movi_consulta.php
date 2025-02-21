<?php
include ("seguridad.php");
include 'func.php';

// Valores cons_id:
// 1: Consulta normal (usuarios comunes): si la planilla esta en estado borrador se puede editar y anular,
//		solo deben ver sus propias planillas, acceso todos los usuarios.
// 2: Consulta de jefes/gerentes: deben aparecer las planillas de los colaboradores a su cargo,
//		acceso jefes y gerentes, admin, ti. No hay opciones de edicion o anulacion.
//		Si entra rol TI puede ver todas las planillas.
// 3: Consulta de TI/Admin y similares: deben aparecer las planillas de los colaboradores de todos. No hay opciones de edicion o anulacion.

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_cons_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
$cons_id = abs((int) filter_var($f_cons_id, FILTER_SANITIZE_NUMBER_INT));

// INICIO - Valida si esta con permiso de realizar operaciones por otros jefes
if (!isset($f_otro_jefe_usu_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}

$otro_jefe_usu_id = abs((int) filter_var($f_otro_jefe_usu_id, FILTER_SANITIZE_NUMBER_INT));

$arr = getUsuRegOtroSlavesJefesIds($_SESSION['rec_usu_id']);
if(in_array($otro_jefe_usu_id, $arr)) {
	$count = 1;
}
else {
	$count = 0;
}
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}
// FIN - Valida si esta con permiso de realizar operaciones por otros jefes
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Reporte de Planillas de Movilidad para EAR - Administraci�n MinappES</title>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
</style>
<style type="text/css" media="screen">
	@import "css/demo_page.css";
	@import "css/demo_table_jui.css";
	@import "css/ui-lightness/jquery-ui-1.9.2.custom.css";
	/*
	 * Override styles needed due to the mix of three different CSS sources! For proper examples
	 * please see the themes example in the 'Examples' section of this site
	 */
	.dataTables_info { padding-top: 0; }
	.dataTables_paginate { padding-top: 0; }
	.css_right { float: right; }
	#example_wrapper .fg-toolbar { font-size: 0.8em }
	#theme_links span { float: left; padding: 2px 10px; }
</style>
<script type="text/javascript" language="javascript" src="js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf-8">
	$(document).ready(function() {
		var oTable = $('#example').dataTable( {
			"bJQueryUI": true,
			"sPaginationType": "full_numbers",
			"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 5 ] } ],
			"bStateSave": true,
			"aaSorting": [[ 0, "desc" ]],
			"bProcessing": true,
			"iDisplayLength": 25,
			"oLanguage": { "sUrl": "i18n/dataTables.spanish.txt" }
		});
	} );
</script>

<style>
.iconos {
	vertical-align:text-top;
}
</style>
</head>
<body class="ex_highlight_row">
<?php include ("header.php"); ?>

<h1>Reporte de Planillas de Movilidad para EAR</h1>

<b>Jefe seleccionado: <?php echo getUsuarioNombre($otro_jefe_usu_id); ?></b><br>

<div class="full_width" style="margin-top: 1em; margin-bottom: 1em; padding-left: 1em; padding-right: 1em; width: auto;">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" width="100%">
	<thead>
		<tr>
			<th>Numero</th>
			<th>Monto PEN</th>
			<th>Estado</th>
			<th>Fecha reg</th>
			<th>Referencia</th>
			<th>Tipo</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
<?php
$arr = getPlanillasMovilidad($cons_id, $_SESSION['rec_usu_id'], $otro_jefe_usu_id);

foreach ($arr as $v) {
	echo "\t<tr class='gradeA'>\n";
	echo "\t\t<td>".$v['pla_numero']."</td>\n";
	echo "\t\t<td align='right'>".$v['pla_monto']."</td>\n";
	echo "\t\t<td>".$v['est_nom']."</td>\n";
	echo "\t\t<td>".$v['pla_reg_fec']."</td>\n";
	echo "\t\t<td>".$v['ear_numero']." <span title='".$v['mon_nom']."'>(".$v['mon_iso']." <img src='".$v['mon_img']."' class='iconos'>)</span></td>\n";
	echo "\t\t<td>".$v['pla_tipo']."</td>\n";

	echo "\t\t<td>";
	echo "<a href='movi_consulta_detalle.php?id=".$v['pla_id']."&opc=1'><img src='img/search.png' border='0' title='Consultar' class='iconos'></a>\n";
	if ($v['est_id'] == 1 && ($cons_id == 1 || $cons_id == 4)) {
		echo "<a href='movi_consulta_detalle.php?id=".$v['pla_id']."&opc=2'><img src='img/edit.png' border='0' title='Editar' class='iconos'></a>\n";
		echo "<a href='movi_anular_p.php?id=".$v['pla_id']."' onClick=\"return confirm('Est&aacute; seguro que desea anular la planilla?')\"><img src='img/opc-no.gif' border='0' title='Anular' class='iconos'></a>\n";
	}
	echo "</td>\n";

	echo "\t</tr>\n";
}
?>
	</tbody>
	<tfoot>
		<tr>
			<th>Numero</th>
			<th>Monto PEN</th>
			<th>Estado</th>
			<th>Fecha reg</th>
			<th>Referencia</th>
			<th>Tipo</th>
			<th>Acciones</th>
		</tr>
	</tfoot>
</table>
</div>

<div style="clear:left">
<p><b>Leyenda:</b>
	<img src='img/search.png' title='Consultar'> Consultar la planilla &nbsp;&nbsp;&nbsp;
<?php
if ($cons_id == 1 || $cons_id == 4) {
?>
	<img src='img/edit.png' title='Editar'> Editar la planilla &nbsp;&nbsp;&nbsp;
	<img src='img/opc-no.gif' title='Anular'> Anular la planilla &nbsp;&nbsp;&nbsp;
<?php
}
?>
</p>
<b>Nota:</b> M&aacute;ximo se muestran 2000 registros por consulta web.<br>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
