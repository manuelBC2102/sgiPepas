<?php
include ("seguridad.php");
include 'func.php';
include 'parametros.php';

$es_rrhh = 1;
// $es_rrhh = getPermisosAdministrativos($_SESSION['ldap_user'], 'RRHH');
$count = $es_rrhh;
// $count += getPermisosAdministrativos($_SESSION['ldap_user'], 'COMP');
$aprob = 0;
if ($count==0) {
	// $aprob = getPermisosAdministrativos($_SESSION['ldap_user'], 'GERENTEINMEDIATO');
	// $aprob += getPermisosAdministrativos($_SESSION['ldap_user'], 'JEFEINMEDIATO');
}
if ($aprob > 0) $count += $aprob;
if ($count==0) {
	echo "<b>ERROR:</b> P&aacute;gina no existe";
	exit;
}

$arrListSol = getListaSolicitudes(9);
$arrListSol = array_merge($arrListSol, getListaSolicitudes(51));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!--<title>EAR Actualizar Liquidaciones con Descuentos pendientes - Administraci�n - Minapp</title>-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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
			"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 8 ] } ],
			"bStateSave": true,
			"aaSorting": [[ 6, "desc" ]],
			"bProcessing": true,
			"iDisplayLength": 25,
			"oLanguage": { "sUrl": "i18n/dataTables.spanish.txt" }
		});
	} );
</script>
<script>
$(document).ready(function()
{
	$('.ear_liq_act').click(function(){
		var msj = 'Verifique si ha sido realizado correctamente la devolucion.\n\nEsta seguro de actualizar?\n\n';
		msj += '(Una vez aceptado no se puede regresar a esta etapa!)';
		if (!confirm(msj)) {
			return false;
		}

		var ear_id = $(this).attr('fila');
		location.href="ear_liq_act_tesocomp_p.php?id="+ear_id+"&oper=2";
	});
});
</script>

<script>
function exportar() {
	//var nombre = encodeURIComponent(document.getElementById("nombre").value);
	location.href="ear_liq_act_comp_excel.php";
}
</script>

<style>
.iconos {
	vertical-align:text-top;
}
</style>
</head>
<body class="ex_highlight_row">
<?php include ("header.php"); ?>

<h1>Actualizaci&oacute;n de liquidaciones con devoluciones pendientes</h1>

<p>Exportar: <span onclick='exportar();' style='cursor: pointer'><img src='img/excel.gif' title='Formato Excel' border='0'></span></p>

<div class="full_width" style="margin-top: 1em; margin-bottom: 1em; padding-left: 1em; padding-right: 1em; width: auto;">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" width="100%">
	<thead>
		<tr>
			<th>Colaborador</th>
			<th>Numero</th>
			<th>Motivo</th>
			<th>Moneda</th>
			<th>Monto</th>
			<th>Estado</th>
			<th>Fecha solic</th>
			<th>Fecha liq</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
<?php
/*
	Valores de $v[*] : Lista de solicitudes EAR :
	0  = ear_id
	1  = ear_tra_nombres
	2  = ear_numero
	3  = zona_nom
	4  = mon_nom
	5  = mon_iso
	6  = mon_img
	7  = ear_monto
	8  = est_id
	9  = est_nom
	10 = ear_sol_fec
	11 = ear_liq_fec
	12 = usu_act
	13 = ear_act_fec
	14 = ear_act_motivo
	15 = ear_liq_dcto
	16 = usu_id
	17 = master_usu_id
        18 = ear_sol_motivo
*/

foreach ($arrListSol as $v) {
	if ($v[15]>0) {
		echo "\t<tr class='gradeA'>\n";
		echo "\t\t<td>".(is_null($v[17])?$v[1]:"<span title='Registrado por ".getUsuarioNombre($v[17])."'>".$v[1]." <img src='img/por_otro.png' class='iconos'></span>")."</td>\n";
		echo "\t\t<td>".$v[2]."</td>\n";
                echo "\t\t<td ".(strlen($v[18])>$longMotivo?"title='".$v[18]."'":"").">".obtenerCadenaXNumLetras($v[18],$longMotivo)."</td>\n";
		echo "\t\t<td><span title='".$v[4]."'>".$v[5]." <img src='".$v[6]."' class='iconos'></span></td>\n";
		echo "\t\t<td align='right'>".conComas($v[15])."</td>\n";

		echo "\t\t<td>".$v[9];

		$est_msj = "Ultima actualizaci&oacute;n por ".$v[12]."\nFecha: ".$v[13];
		echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";

		echo "</td>\n";

		echo "\t\t<td>".$v[10]."</td>\n";
		//echo "\t\t<td>".$v[11]."</td>\n";
		echo "\t\t<td>".getFechaEnvioLiq($v[0])."</td>\n";

		echo "\t\t<td>";
		echo "<a href='ear_consulta_detalle.php?id=".$v[0]."'><img src='img/search.png' border='0' title='Detalle de la solicitud' class='iconos'></a>\n";
		echo "<span class='ear_liq_act' fila='".$v[0]."' style='cursor: pointer'><img src='img/opc-si.gif' border='0' title='Actualizar' class='iconos'></span>\n";
		echo "</td>\n";

		echo "\t</tr>\n";
	}
}

?>
	</tbody>
	<tfoot>
		<tr>
			<th>Colaborador</th>
			<th>Numero</th>
			<th>Motivo</th>
			<th>Moneda</th>
			<th>Monto</th>
			<th>Estado</th>
			<th>Fecha solic</th>
			<th>Fecha liq</th>
			<th>Acciones</th>
		</tr>
	</tfoot>
</table>
</div>

<div style="clear:left">
<p><b>Leyenda:</b>
	<img src='img/search.png' title='Detalle' class='iconos'> Detalle de la solicitud &nbsp;&nbsp;&nbsp;
	<img src='img/opc-si.gif' title='Actualizar' class='iconos'> Actualizar &nbsp;&nbsp;&nbsp;
	<img src='img/por_otro.png' title='Realizado por otro usuario' class='iconos'> Realizado por otro usuario &nbsp;&nbsp;&nbsp;
</p>
<b>Nota:</b> M&aacute;ximo se muestran 2000 registros por consulta web.<br>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
