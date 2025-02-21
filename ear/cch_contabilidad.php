<?php
include ("seguridad.php");
include 'func.php';

$usu_id = $_SESSION['rec_usu_id'];

$count = getPermisosAdministrativos($usu_id, 'SUP_CONT');
$count += getPermisosAdministrativos($usu_id, 'REG_CONT');
$count += getPermisosAdministrativos($usu_id, 'ANA_CONT');
$count += getPermisosAdministrativos($usu_id, 'CONTROLLER');
$count += getPermisosAdministrativos($usu_id, 'TI');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Caja Chica Liquidaciones pendientes de Revisar - Administraci�n MinappES</title>
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

<!--Seccion Date Picker-->
<script src="js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript" src="i18n/jquery.ui.datepicker-es.js"></script>
<script>
$(function() {
	$( "#rfecha" ).datepicker({
		numberOfMonths: 1,
		altField: "#rfecha2",
		altFormat: "yy-mm-dd"
	});
	$( "#rfecha" ).datepicker( $.datepicker.regional[ "es" ] );
	$( "#rfecha" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
	$( '#rfecha' ).datepicker( 'setDate', '<?php echo date("Y-m-d"); ?>' );
	$( "#rfecha" ).datepicker( "option", "dateFormat", "D, d M yy" );
});
</script>
<!--Seccion Date Picker-->

<script>
$(document).ready(function()
{
	$('.cch_lote_excel_liq').click(function(){
		var posting_date = $('#rfecha2').val();
		var ear_id = $(this).attr('fila');
		location.href="cch_lote_excel_liq.php?id="+ear_id+"&pd="+posting_date;
	});
	
	$('.cch_lote_cont_upd').click(function(){
		if (!confirm('Revise si la liquidacion ha sido registrada correctamente en SAP.\n\nEsta seguro de continuar?\n\n(Una vez aceptado no se puede regresar a esta etapa!)')) {
			return false;
		}
		
		var ear_id = $(this).attr('fila');
		location.href="cch_cont_upd_p.php?id="+ear_id;
	});
});
</script>

<style>
.iconos {
	vertical-align:text-top;
}
</style>
</head>
<body class="ex_highlight_row">
<?php include ("header.php"); ?>

<h1>Caja Chica Liquidaciones pendientes de Revisar</h1>

<p>Fijar valor de Posting Date <input type="text" id="rfecha" name="rfecha" readonly><input type="hidden" id="rfecha2" name="rfecha2" /></p>

<div class="full_width" style="margin-top: 1em; margin-bottom: 1em; padding-left: 1em; padding-right: 1em; width: auto;">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" width="100%">
	<thead>
		<tr>
			<th>Caja Chica</th>
			<th>Lote</th>
			<th>Moneda</th>
			<th>Monto Usado</th>
			<th>Estado</th>
			<th>Fecha cierre</th>
			<th>Fecha aprobacion</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
<?php
$arr = getLotesCajaChicaContaLista();

foreach ($arr as $v) {
	echo "\t<tr class='gradeA'>\n";
	
	echo "\t\t<td>$v[1]</td>\n";
	echo "\t\t<td>$v[2]</td>\n";
	echo "\t\t<td><span title='$v[3]'>$v[4] <img src='$v[6]' class='iconos'></span></td>\n";
	echo "\t\t<td align='right'>$v[19]</td>\n";
	
	echo "\t\t<td>$v[21]";
	$est_msj = "Ultima actualizaci�n por $v[18]\nFecha: $v[17]";
	echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";
	echo "</td>\n";
	
	echo "\t\t<td>$v[13]";
	$est_msj = "Realizado por $v[14]";
	echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";
	echo "</td>\n";

	echo "\t\t<td>$v[15]";
	$est_msj = "Realizado por $v[16]";
	echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";
	echo "</td>\n";
	
	echo "\t\t<td style='white-space: nowrap'>";
	echo "<a href='cch_lote_consulta_detalle.php?id=$v[0]'><img src='img/search.png' border='0' title='Detalle del Lote' class='iconos'></a>";
	echo "<a href='cch_lote_contabilidad.php?id=$v[0]'><img src='img/liquidar.png' title='Revisar Lote' border='0' class='iconos'></a> ";
	
	echo "<span class='cch_lote_excel_liq' fila='$v[0]' style='cursor: pointer'><img src='".($v[31] > 0?'img/l.png':'img/transparent.gif')."' border='0' title='Descargar Excel Liquidacion' class='iconos'></span>\n";
	echo "<a href='cch_lote_excel_ret.php?id=$v[0]'><img src='".($v[32] > 0?'img/r.png':'img/transparent.gif')."' border='0' title='Descargar Excel Retenciones' class='iconos'></a>\n";
	echo "<a href='cch_lote_excel_det.php?id=$v[0]'><img src='".($v[33] > 0?'img/d.png':'img/transparent.gif')."' border='0' title='Descargar Excel Detracciones' class='iconos'></a>\n";
	echo "<a href='cch_lote_excel_acf.php?id=$v[0]'><img src='".($v[34] > 0?'img/a.png':'img/transparent.gif')."' border='0' title='Descargar Excel Activo Fijo' class='iconos'></a>\n";
	echo "<a href='cch_lote_excel_aju.php?id=$v[0]'><img src='".($v[35] > 0?'img/aj.jpg':'img/transparent.gif')."' border='0' title='Descargar Excel Ajustes' class='iconos'></a>\n";
	echo "<a href='cch_lote_pdf.php?id=$v[0]'><img src='img/pdf.gif' border='0' title='Descargar PDF Liquidacion CAJA CHICA' class='iconos'></a>\n";
	echo "<span class='cch_lote_cont_upd' fila='$v[0]' style='cursor: pointer'><img src='img/me-gusta.png' title='Actualizar estado' border='0' class='iconos'></span>\n";
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
			<th>Fecha cierre</th>
			<th>Fecha aprobacion</th>
			<th>Acciones</th>
		</tr>
	</tfoot>
</table>
</div>

<div style="clear:left">
<p><b>Leyenda:</b>
	<img src='img/search.png' title='Detalle' class='iconos'> Detalle del Lote &nbsp;&nbsp;&nbsp;
	<img src='img/liquidar.png' title='Revisar' class='iconos'> Revisar Lote &nbsp;&nbsp;&nbsp;
	<img src='img/l.png' title='Liquidacion' class='iconos'>
	<img src='img/r.png' title='Retenciones' class='iconos'>
	<img src='img/d.png' title='Detracciones' class='iconos'>
	<img src='img/a.png' title='Activo Fijo' class='iconos'>
	<img src='img/aj.jpg' title='Ajustes' class='iconos'> Descargar archivos Excel &nbsp;&nbsp;&nbsp;
	<img src='img/pdf.gif' title='Descargar PDF Liquidacion EAR' class='iconos'> Descargar PDF Lote Caja Chica &nbsp;&nbsp;&nbsp;
	<img src='img/me-gusta.png' title='Actualizar estado' class='iconos'> Actualizar estado del Lote &nbsp;&nbsp;&nbsp;
</p>
<b>Nota:</b> M&aacute;ximo se muestran 25000 registros por consulta web.<br>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
