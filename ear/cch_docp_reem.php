<?php
include ("seguridad.php");
include 'func.php';

$usu_id = $_SESSION['rec_usu_id'];

$count = getPermisosAdministrativos($usu_id, 'TESO');
$count += getPermisosAdministrativos($usu_id, 'TI');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe (#1)</b></font><br>";
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Reembolso de Documentos Pendientes vencidos de Caja Chica - Administraci�n MinappES</title>
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

<h1>Reembolso de Documentos Pendientes vencidos de Caja Chica</h1>

<div class="full_width" style="margin-top: 1em; margin-bottom: 1em; padding-left: 1em; padding-right: 1em; width: auto;">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" width="100%">
	<thead>
		<tr>
			<th>Caja Chica</th>
			<th>Numero</th>
			<th>Solicita</th>
			<th>Moneda</th>
			<th>Monto</th>
			<th>Concepto</th>
			<th>Fecha registro</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
<?php
$arr = getDocPendDescontadosLista();

foreach ($arr as $v) {
	if (strlen($v[11])>30) {
		$concepto = substr($v[11], 0, 30)."... <img src='img/info.gif' title='$v[11]' class='iconos'>";
	}
	else {
		$concepto = $v[11];
	}
	
	echo "\t<tr class='gradeA'>\n";
	
	echo "\t\t<td>$v[1]</td>\n";
	echo "\t\t<td style='white-space: nowrap'>RCC $v[2]</td>\n";
	echo "\t\t<td>$v[3]</td>\n";
	echo "\t\t<td><span title='$v[4]'>$v[5] <img src='$v[7]' class='iconos'></span></td>\n";
	echo "\t\t<td align='right'>$v[8]</td>\n";
	
	echo "\t\t<td>$concepto</td>\n";
	echo "\t\t<td>$v[10]</td>\n";
	
	echo "\t\t<td style='white-space: nowrap'>";
	echo "<a href='cch_lote_dp_reem_p.php?id=$v[0]' onClick=\"return confirm('Est&aacute; seguro que desea reembolsar este documento pendiente?')\"><img src='img/money-bag-dollar.png' border='0' title='Reembolsar' class='iconos'></a>\n";
	echo "</td>\n";
	
	echo "\t</tr>\n";
}
?>
	</tbody>
	<tfoot>
		<tr>
			<th>Caja Chica</th>
			<th>Numero</th>
			<th>Solicita</th>
			<th>Moneda</th>
			<th>Monto</th>
			<th>Concepto</th>
			<th>Fecha registro</th>
			<th>Acciones</th>
		</tr>
	</tfoot>
</table>
</div>

<div style="clear:left">
<p><b>Leyenda:</b>
	<img src='img/money-bag-dollar.png' border='0' title='Reembolsar' class='iconos'> Reembolsar &nbsp;&nbsp;&nbsp;
</p>
<b>Nota:</b> M&aacute;ximo se muestran 25000 registros por consulta web.<br>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
