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

// Cantidad maxima de registros a mostrar
$max = 5000;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Mantenedor Sem�foros - Administraci�MinappGIES</title>
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
			"aaSorting": [[ 0, "asc" ]],
			"bProcessing": true,
			"iDisplayLength": 50,
			"oLanguage": { "sUrl": "i18n/dataTables.spanish.txt" }
		});
	} );
</script>
<script>
function editar(id) {
	location.href="mant_semaforos_edit.php?id="+id;
}
</script>
<style>
.iconos {
	vertical-align:text-top;
	cursor: pointer;
}
</style>
</head>
<body class="ex_highlight_row">
<?php include ("header.php"); ?>

<h1>Mantenedor Sem�foros</h1>

<div class="full_width" style="margin-top: 1em; margin-bottom: 1em; padding-left: 1em; padding-right: 1em; width: 800px;">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" width="100%">
	<thead>
		<tr>
			<th>Id</th>
			<th>Tabla</th>
			<th>Nombre</th>
			<th title='Valor minimo para verde'>Verde</th>
			<th title='Valor minimo para ambar'>Ambar</th>
			<th title='Valor minimo para rojo'>Rojo</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
<?php
$arr = getSemaforosLista($max);

foreach ($arr as $v) {
	echo "\t<tr class='gradeA'>\n";
	echo "\t\t<td>".$v[6]."</td>\n";
	echo "\t\t<td>".$v[5]."</td>\n";
	echo "\t\t<td>".$v[1]."</td>\n";
	echo "\t\t<td align='right'>".$v[2]."</td>\n";
	echo "\t\t<td align='right'>".$v[3]."</td>\n";
	echo "\t\t<td align='right'>".$v[4]."</td>\n";
	
	echo "\t\t<td>\n";
	echo "\t\t\t<img src='img/edit.png' border='0' title='Editar' class='iconos' onclick='editar(\"$v[6]\");'>\n";
	echo "\t\t</td>\n";
	
	echo "\t</tr>\n";
}
?>
	</tbody>
	<tfoot>
		<tr>
			<th>Id</th>
			<th>Tabla</th>
			<th>Nombre</th>
			<th title='Valor minimo para verde'>Verde</th>
			<th title='Valor minimo para ambar'>Ambar</th>
			<th title='Valor minimo para rojo'>Rojo</th>
			<th>Acciones</th>
		</tr>
	</tfoot>
</table>
</div>

<div style="clear:left">
<p><b>Leyenda:</b>
	<img src='img/edit.png' title='Editar'> Editar &nbsp;&nbsp;&nbsp;
</p>
<b>Nota:</b> M&aacute;ximo se muestran <?php echo $max; ?> registros por consulta web.<br>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
