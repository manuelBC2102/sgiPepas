<?php
include ("seguridad.php");
include 'func.php';

$usu_id = $_SESSION['rec_usu_id'];

$arr = getCajasChicasRespAcceso($usu_id);
$count = count($arr);
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Caja Chica Liquidaciones pendientes de Aprobar - Administraci�n MinappES</title>
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
			"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 7 ] } ],
			"bStateSave": true,
			"aaSorting": [[ 1, "desc" ]],
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

<h1>Caja Chica Liquidaciones pendientes de Aprobar</h1>

<div class="full_width" style="margin-top: 1em; margin-bottom: 1em; padding-left: 1em; padding-right: 1em; width: auto;">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" width="100%">
	<thead>
		<tr>
			<th>Caja Chica</th>
			<th>Lote</th>
			<th>Moneda</th>
			<th>Monto Usado</th>
			<th>Estado</th>
			<th>Fecha apertura</th>
			<th>Fecha cierre</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
<?php
$arr = getLotesCajaChicaLista(2, $usu_id);

foreach ($arr as $v) {
	$total_usado = number_format($v[19]+$v[28], 2, '.', '');;
	
	echo "\t<tr class='gradeA'>\n";
	
	echo "\t\t<td>$v[1]</td>\n";
	echo "\t\t<td>$v[2]</td>\n";
	echo "\t\t<td><span title='$v[3]'>$v[4] <img src='$v[6]' class='iconos'></span></td>\n";
	echo "\t\t<td align='right'>$total_usado</td>\n";
	
	echo "\t\t<td>$v[21]";
	$est_msj = "Ultima actualizaci�n por $v[18]\nFecha: $v[17]";
	echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";
	echo "</td>\n";
	
	echo "\t\t<td>$v[11]";
	$est_msj = "Realizado por $v[12]";
	echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";
	echo "</td>\n";

	echo "\t\t<td>$v[13]";
	$est_msj = "Realizado por $v[14]";
	echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";
	echo "</td>\n";
	
	echo "\t\t<td>";
	echo "<a href='cch_lote_consulta_detalle.php?id=$v[0]'><img src='img/search.png' border='0' title='Detalle del lote' class='iconos'></a>";
	echo "<a href='cch_lote_revision.php?id=$v[0]'><img src='img/liquidar.png' title='Revisar lote' border='0' class='iconos'></a> ";
	echo "</td>\n";
	
	echo "\t</tr>\n";
}
?>
	</tbody>
	<tfoot>
		<tr>
			<th>Caja Chica</th>
			<th>Lote</th>
			<th>Moneda</th>
			<th>Monto Usado</th>
			<th>Estado</th>
			<th>Fecha apertura</th>
			<th>Fecha cierre</th>
			<th>Acciones</th>
		</tr>
	</tfoot>
</table>
</div>

<div style="clear:left">
<p><b>Leyenda:</b>
	<img src='img/search.png' title='Detalle' class='iconos'> Detalle del Lote &nbsp;&nbsp;&nbsp;
	<img src='img/liquidar.png' title='Revisar Liquidaci�n' class='iconos'> Revisar Lote &nbsp;&nbsp;&nbsp;
</p>
<b>Nota:</b> M&aacute;ximo se muestran 25000 registros por consulta web.<br>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
