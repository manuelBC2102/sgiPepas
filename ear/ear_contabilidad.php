<?php
include ("seguridad.php");
include 'func.php';

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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!--<title>EAR Secci�n Contabilidad - Administraci�n - Minapp</title>-->
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
	$('.ear_excel_liq').click(function(){
		var posting_date = $('#rfecha2').val();
		var ear_id = $(this).attr('fila');
		location.href="ear_excel_liq.php?id="+ear_id+"&pd="+posting_date;
	});

	$('.ear_liq_cont_upd').click(function(){
		if (!confirm('Revise si la liquidacion ha sido registrada correctamente en SAP.\n\nEsta seguro de enviar la liquidacion al analista de cuentas?\n\n(Una vez aceptado no se puede regresar a esta etapa!)')) {
			return false;
		}

		var ear_id = $(this).attr('fila');
		location.href="ear_liq_cont_upd_p.php?id="+ear_id;
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

    <h1>Entregas a Rendir (EAR) - Secci&oacute;n Contabilidad</h1>

<p>Fijar valor de Posting Date <input type="text" id="rfecha" name="rfecha" readonly><input type="hidden" id="rfecha2" name="rfecha2" /></p>

<div class="full_width" style="margin-top: 1em; margin-bottom: 1em; padding-left: 1em; padding-right: 1em; width: auto;">
<table cellpadding="0" cellspacing="0" border="0" class="display" id="example" width="100%">
	<thead>
		<tr>
			<th>Colaborador</th>
			<th>Numero</th>
			<th>Zona</th>
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
include 'datos_abrir_bd.php';

// $query='SELECT e.ear_id, e.ear_tra_nombres, CONCAT(e.ear_anio, "-", LPAD(e.ear_mes, 2, "0"), "-", LPAD(e.ear_nro, 3, "0"), "/", ud.usu_iniciales) ear_numero,
	// z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, e.ear_monto, e.est_id, te.est_nom, e.ear_sol_fec, e.ear_liq_fec,
	// ru.usu_nombre usu_act, e.ear_act_fec, e.ear_act_motivo, e.master_usu_id
// FROM ear_solicitudes e
// LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
// LEFT JOIN monedas m ON m.mon_id=e.mon_id
// LEFT JOIN tablas_estados te ON te.est_id=e.est_id
// JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom="ear_solicitudes"
// LEFT JOIN usu_detalle ud ON ud.usu_id=e.usu_id
// LEFT JOIN recursos.usuarios ru ON ru.usu_id=e.ear_act_usu
// WHERE e.est_id=7
// LIMIT 2000';
$query = 'SELECT ear_id, ear_tra_nombres, ear_numero, zona_nom, mon_nom, mon_iso, mon_img, ear_monto, est_id, est_nom, ear_sol_fec, ear_liq_fec,
	usu_act, ear_act_fec, ear_act_motivo, master_usu_id,
	SUM(deta_liq) deta_liq, SUM(deta_ret) deta_ret, SUM(deta_det) deta_det, SUM(deta_acf) deta_acf, SUM(deta_aju) deta_aju
FROM
(
SELECT e.ear_id, e.ear_tra_nombres, CONCAT(e.ear_anio, "-", LPAD(e.ear_mes, 2, "0"), "-", LPAD(e.ear_nro, 3, "0"), "/", ud.usu_iniciales) ear_numero,
	z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, e.ear_monto, e.est_id, te.est_nom, e.ear_sol_fec, e.ear_liq_fec,
	ru.usu_nombre usu_act, e.ear_act_fec, e.ear_act_motivo, e.master_usu_id,
	CASE WHEN eld.lid_aprob = 1 AND eld.lid_retdet_tip = 0 AND ec.conc_acf = 0 THEN 1 ELSE 0 END as deta_liq,
	CASE WHEN eld.lid_aprob = 1 AND eld.lid_retdet_tip = 2 THEN 1 ELSE 0 END as deta_ret,
	CASE WHEN eld.lid_aprob = 1 AND eld.lid_retdet_tip = 1 THEN 1 ELSE 0 END as deta_det,
	CASE WHEN eld.lid_aprob = 1 AND ec.conc_acf = 1 THEN 1 ELSE 0 END as deta_acf,
	CASE WHEN eld.lid_aprob = 1 AND eld.lid_mon_afe + eld.lid_mon_naf - eld.lid_emp_asume > 0 THEN 1 ELSE 0 END as deta_aju
FROM ear_solicitudes e
LEFT JOIN ear_liq_detalle eld ON eld.ear_id=e.ear_id
LEFT JOIN ear_conceptos ec ON ec.conc_id=eld.conc_id
LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
LEFT JOIN monedas m ON m.mon_id=e.mon_id
LEFT JOIN tablas_estados te ON te.est_id=e.est_id
JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom="ear_solicitudes"
LEFT JOIN usu_detalle ud ON ud.usu_id=e.usu_id
LEFT JOIN recursos.usuarios ru ON ru.usu_id=e.ear_act_usu
WHERE e.est_id = 7

UNION ALL

SELECT e.ear_id, e.ear_tra_nombres, CONCAT(e.ear_anio, "-", LPAD(e.ear_mes, 2, "0"), "-", LPAD(e.ear_nro, 3, "0"), "/", ud.usu_iniciales) ear_numero,
	z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, e.ear_monto, e.est_id, te.est_nom, e.ear_sol_fec, e.ear_liq_fec,
	ru.usu_nombre usu_act, e.ear_act_fec, e.ear_act_motivo, e.master_usu_id,
	CASE WHEN pm.pla_monto > 0 THEN 1 ELSE 0 END AS deta_liq,
	0 AS deta_ret,
	0 AS deta_det,
	0 AS deta_acf,
	0 AS deta_aju
FROM ear_solicitudes e
LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
LEFT JOIN monedas m ON m.mon_id=e.mon_id
LEFT JOIN tablas_estados te ON te.est_id=e.est_id
JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom="ear_solicitudes"
LEFT JOIN usu_detalle ud ON ud.usu_id=e.usu_id
LEFT JOIN recursos.usuarios ru ON ru.usu_id=e.ear_act_usu
JOIN pla_mov pm ON pm.ear_id=e.ear_id
WHERE e.est_id = 7 AND e.pla_id IS NOT NULL
) AS A

GROUP BY ear_id, ear_tra_nombres, ear_numero, zona_nom, mon_nom, mon_iso, mon_img, ear_monto, est_id, est_nom, ear_sol_fec, ear_liq_fec,
	usu_act, ear_act_fec, ear_act_motivo, master_usu_id

LIMIT 2000';
$result = $mysqli->query($query) or die ($mysqli->error);

while($fila=$result->fetch_array()){
	echo "\t<tr class='gradeA'>\n";
	echo "\t\t<td>".(is_null($fila['master_usu_id'])?$fila['ear_tra_nombres']:"<span title='Registrado por ".getUsuarioNombre($fila['master_usu_id'])."'>".$fila['ear_tra_nombres']." <img src='img/por_otro.png' class='iconos'></span>")."</td>\n";
	echo "\t\t<td>".$fila['ear_numero']."</td>\n";
	echo "\t\t<td>".$fila['zona_nom']."</td>\n";
	echo "\t\t<td><span title='".$fila['mon_nom']."'>".$fila['mon_iso']." <img src='".$fila['mon_img']."' class='iconos'></span></td>\n";
	echo "\t\t<td align='right'>".$fila['ear_monto']."</td>\n";

	echo "\t\t<td>".$fila['est_nom'];

	$est_msj = "Ultima actualizaci�n por ".$fila['usu_act']."\nFecha: ".$fila['ear_act_fec'];
	echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";

	echo "</td>\n";

	echo "\t\t<td>".$fila['ear_sol_fec']."</td>\n";
	echo "\t\t<td>".getFechaEnvioLiq($fila['ear_id'])."</td>\n";

	echo "\t\t<td style='white-space: nowrap'>";
	echo "<a href='ear_consulta_detalle.php?id=".$fila['ear_id']."'><img src='img/search.png' border='0' title='Detalle de la solicitud' class='iconos'></a>\n";
	echo "<a href='ear_liq_contabilidad.php?id=".$fila['ear_id']."'><img src='img/liquidar.png' title='Revisar Liquidaci�n' border='0' class='iconos'></a> ";

	echo "<span class='ear_excel_liq' fila='".$fila['ear_id']."' style='cursor: pointer'><img src='".($fila['deta_liq'] > 0?'img/l.png':'img/transparent.gif')."' border='0' title='Descargar Excel Liquidacion' class='iconos'></span>\n";
	echo "<a href='ear_excel_ret.php?id=".$fila['ear_id']."'><img src='".($fila['deta_ret'] > 0?'img/r.png':'img/transparent.gif')."' border='0' title='Descargar Excel Retenciones' class='iconos'></a>\n";
	echo "<a href='ear_excel_det.php?id=".$fila['ear_id']."'><img src='".($fila['deta_det'] > 0?'img/d.png':'img/transparent.gif')."' border='0' title='Descargar Excel Detracciones' class='iconos'></a>\n";
	echo "<a href='ear_excel_acf.php?id=".$fila['ear_id']."'><img src='".($fila['deta_acf'] > 0?'img/a.png':'img/transparent.gif')."' border='0' title='Descargar Excel Activo Fijo' class='iconos'></a>\n";
	echo "<a href='ear_excel_aju.php?id=".$fila['ear_id']."'><img src='".($fila['deta_aju'] > 0?'img/aj.jpg':'img/transparent.gif')."' border='0' title='Descargar Excel Ajustes' class='iconos'></a>\n";
	echo "<a href='ear_pdf_liq.php?id=".$fila['ear_id']."'><img src='img/pdf.gif' border='0' title='Descargar PDF Liquidacion EAR' class='iconos'></a>\n";
	echo "<span class='ear_liq_cont_upd' fila='".$fila['ear_id']."' style='cursor: pointer'><img src='img/me-gusta.png' title='Actualizar estado' border='0' class='iconos'></span>\n";
	echo "</td>\n";

	echo "\t</tr>\n";
}

include 'datos_cerrar_bd.php';
?>
	</tbody>
	<tfoot>
		<tr>
			<th>Colaborador</th>
			<th>Numero</th>
			<th>Zona</th>
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
	<img src='img/search.png' title='Detalle'> Detalle de la solicitud &nbsp;&nbsp;&nbsp;
	<img src='img/liquidar.png' title='Revisar'> Revisar Liquidaci�n &nbsp;&nbsp;&nbsp;
	<img src='img/l.png' title='Liquidacion'>
	<img src='img/r.png' title='Retenciones'>
	<img src='img/d.png' title='Detracciones'>
	<img src='img/a.png' title='Activo Fijo'>
	<img src='img/aj.jpg' title='Ajustes'> Descargar archivos Excel &nbsp;&nbsp;&nbsp;
	<img src='img/pdf.gif' title='Descargar PDF Liquidacion EAR'> Descargar PDF Liquidacion EAR &nbsp;&nbsp;&nbsp;
	<img src='img/me-gusta.png' title='Actualizar estado'> Actualizar estado de la liquidaci�n &nbsp;&nbsp;&nbsp;
	<img src='img/por_otro.png' title='Realizado por otro usuario' class='iconos'> Realizado por otro usuario &nbsp;&nbsp;&nbsp;
</p>
<b>Nota:</b> M&aacute;ximo se muestran 2000 registros por consulta web.<br>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
