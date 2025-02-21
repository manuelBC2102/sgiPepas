<?php
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
if ($count>0) {
	$god_mode = 1; // YES
}
else {
	$count = count(getCajasChicasEncAcceso($_SESSION['rec_usu_id']));
	
	if ($count==0) {
		echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
		exit;
	}
	else {
		$god_mode = 0; // NO
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Agregar Planillas de Movilidad a Caja Chica - Administraci�n MinappES</title>
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
			"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 6 ] } ],
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

<h1>Agregar Planillas de Movilidad a Caja Chica</h1>

<div class="full_width" style="margin-top: 1em; margin-bottom: 1em; padding-left: 1em; padding-right: 1em; width: auto;">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" width="100%">
	<thead>
		<tr>
			<th>Numero</th>
			<th>Solicita</th>
			<th>Monto PEN</th>
			<th>Estado</th>
			<th>Fecha reg</th>
			<th>Referencia</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
<?php
$arr = getPlanillasMovilidadCCH($god_mode, 2, $_SESSION['rec_usu_id']);

foreach ($arr as $v) {
	echo "\t<tr class='gradeA'>\n";
	echo "\t\t<td>".$v['pla_numero']."</td>\n";
	echo "\t\t<td>".$v['usu_nombre']."</td>\n";
	echo "\t\t<td align='right'>".$v['pla_monto']."</td>\n";
	echo "\t\t<td>".$v['est_nom']."</td>\n";
	echo "\t\t<td>".$v['pla_reg_fec']."</td>\n";
	echo "\t\t<td>".$v['ear_numero']." <span title='".$v['mon_nom']."'>(".$v['mon_iso']." <img src='".$v['mon_img']."' class='iconos'>)</span></td>\n";

	echo "\t\t<td>";
	echo "<a href='movi_consulta_detalle.php?id=".$v['pla_id']."&opc=1'><img src='img/search.png' border='0' title='Consultar' class='iconos'></a>\n";
	echo "<a href='cch_lote_plm_add_p.php?id=".$v['pla_id']."' onClick=\"return confirm('Est&aacute; seguro que desea agregar la planilla?')\"><img src='img/opc-si.gif' border='0' title='Agregar' class='iconos'></a>\n";
	echo "</td>\n";

	echo "\t</tr>\n";
}
?>
	</tbody>
	<tfoot>
		<tr>
			<th>Numero</th>
			<th>Solicita</th>
			<th>Monto PEN</th>
			<th>Estado</th>
			<th>Fecha reg</th>
			<th>Referencia</th>
			<th>Acciones</th>
		</tr>
	</tfoot>
</table>
</div>

<div style="clear:left">
<p><b>Leyenda:</b>
	<img src='img/search.png' title='Consultar'> Consultar la planilla &nbsp;&nbsp;&nbsp;
	<img src='img/opc-si.gif' title='Agregar'> Agregar la planilla &nbsp;&nbsp;&nbsp;
</p>
<b>Nota:</b> M&aacute;ximo se muestran 2000 registros por consulta web.<br>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
