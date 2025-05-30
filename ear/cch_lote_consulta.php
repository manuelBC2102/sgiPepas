<?php
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_opc)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$opc = abs((int) filter_var($f_opc, FILTER_SANITIZE_NUMBER_INT));
}

$arr_opc = array(0, 4, 5);
if (!in_array($opc, $arr_opc)) {
	echo "<font color='red'><b>ERROR: Opci&oacute;n err&oacute;nea</b></font><br>";
	exit;
}

$usu_id = $_SESSION['rec_usu_id'];

$count = count(getCajasChicasEncAcceso($usu_id));
$count += count(getCajasChicasRespAcceso($usu_id));
$count += getPermisosAdministrativos($usu_id, 'CONTROLLER');
$count += getPermisosAdministrativos($usu_id, 'SUP_CONT');
$count += getPermisosAdministrativos($usu_id, 'REG_CONT');
$count += getPermisosAdministrativos($usu_id, 'ANA_CONT');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe (#1)</b></font><br>";
	exit;
}

if ($opc==0) {
	if (getUserGodMode($usu_id)>0) {
	}
	else {
		$count = getPermisosAdministrativos($usu_id, 'CONTROLLER');
		$count += getPermisosAdministrativos($usu_id, 'SUP_CONT');
		$count += getPermisosAdministrativos($usu_id, 'REG_CONT');
		$count += getPermisosAdministrativos($usu_id, 'ANA_CONT');
		if ($count == 0) {
			echo "<font color='red'><b>ERROR: P&aacute;gina no existe (#2)</b></font><br>";
			exit;
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Consulta de Liquidaciones de Caja Chica - Administraci�n MinappES</title>
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

<h1>Consulta de Liquidaciones de Caja Chica</h1>

<div class="full_width" style="margin-top: 1em; margin-bottom: 1em; padding-left: 1em; padding-right: 1em; width: auto;">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" width="100%">
	<thead>
		<tr>
			<th>Caja Chica</th>
			<th>Liquidacion</th>
			<th>Moneda</th>
			<th>Desembolsar</th>
			<th>Estado</th>
			<th>Fecha cierre</th>
			<th>Fecha aprobacion</th>
			<th>Fecha desembolso</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
<?php
$arr = getLotesCajaChicaLista($opc, $usu_id);

foreach ($arr as $v) {
	echo "\t<tr class='gradeA'>\n";
	
	echo "\t\t<td>$v[1]</td>\n";
	echo "\t\t<td>$v[2]</td>\n";
	echo "\t\t<td><span title='$v[3]'>$v[4] <img src='$v[6]' class='iconos'></span></td>\n";
	echo "\t\t<td align='right'>$v[30]</td>\n";
	
	echo "\t\t<td>$v[21]";
	$est_msj = "Ultima actualizaci�n por $v[18]\nFecha: $v[17]";
	echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";
	echo "</td>\n";
	
	if (!is_null($v[13])) {
		echo "\t\t<td>$v[13]";
		$est_msj = "Realizado por $v[14]";
		echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";
		echo "</td>\n";
	}
	else {
		echo "\t\t<td></td>\n";
	}
	
	if (!is_null($v[15])) {
		echo "\t\t<td>$v[15]";
		$est_msj = "Realizado por $v[16]";
		echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";
		echo "</td>\n";
	}
	else {
		echo "\t\t<td></td>\n";
	}
	
	if (in_array($v[20], array(3, 5, 6, 7)) && $v[34]==1) {
		echo "\t\t<td>$v[35]</td>\n";
	}
	else {
		echo "\t\t<td></td>\n";
	}
	
	echo "\t\t<td style='white-space: nowrap'>";
	echo "<a href='cch_lote_consulta_detalle.php?id=$v[0]'><img src='img/search.png' border='0' title='Detalle de la liquidacion' class='iconos'></a>";
	if ($v[20]>=2) {
		echo "<a href='cch_lote_pdf.php?id=$v[0]'><img src='img/pdf.gif' border='0' title='Resumen' class='iconos'></a>\n";
	}
	else {
		echo "<img src='img/transparent.gif' border='0' class='iconos'>\n";
	}
	if ($v[32]>0) {
		echo "<a href='cch_plm_pdf_all.php?id=$v[0]'><img src='img/page_white_stack.gif' border='0' title='Descargar todas las Planillas de Movilidad' class='iconos'></a>\n";
	}
	else {
		echo "<img src='img/transparent.gif' border='0' class='iconos'>\n";
	}
	if ($v[33]>0) {
		echo "<a href='cch_lote_dp_pdf_all.php?id=$v[0]'><img src='img/list_pages.gif' border='0' title='Descargar todos los Documentos Pendientes' class='iconos'></a>\n";
	}
	echo "</td>\n";
	
	echo "\t</tr>\n";
}
?>
	</tbody>
	<tfoot>
		<tr>
			<th>Caja Chica</th>
			<th>Liquidacion</th>
			<th>Moneda</th>
			<th>Desembolsar</th>
			<th>Estado</th>
			<th>Fecha cierre</th>
			<th>Fecha aprobacion</th>
			<th>Fecha desembolso</th>
			<th>Acciones</th>
		</tr>
	</tfoot>
</table>
</div>

<div style="clear:left">
<p><b>Leyenda:</b>
	<img src='img/search.png' title='Detalle' class='iconos'> Detalle de la Liquidacion &nbsp;&nbsp;&nbsp;
	<img src='img/pdf.gif' title='Desembolsar' class='iconos'> Resumen &nbsp;&nbsp;&nbsp;
	<img src='img/page_white_stack.gif' title='Descargar todas las Planillas de Movilidad' class='iconos'> Planillas de Movilidad &nbsp;&nbsp;&nbsp;
	<img src='img/list_pages.gif' title='Descargar todos los Documentos Pendientes' class='iconos'> Documentos Pendientes &nbsp;&nbsp;&nbsp;
</p>
<b>Nota:</b> M&aacute;ximo se muestran 25000 registros por consulta web.<br>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
