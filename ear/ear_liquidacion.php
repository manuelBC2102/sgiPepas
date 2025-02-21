<?php
include ("seguridad.php");
include 'func.php';
include 'parametros.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (isset($f_opc_id)) $opc_id = abs((int) filter_var($f_opc_id, FILTER_SANITIZE_NUMBER_INT)); else $opc_id=0;

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
<!--<title>EAR pendientes de Liquidar - Administraci�n - Minapp</title>-->
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

<style>
.iconos {
	vertical-align:text-top;
}
</style>
</head>
<body class="ex_highlight_row">
<?php include ("header.php"); ?>

<h1>Registro de liquidaci&oacute;n</h1>

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
			<th>Fecha dep</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
<?php

	if ($opc_id == 10) {
		$q_usu = "AND (e.master_usu_id=".$_SESSION['rec_usu_id']." OR e.usu_id IN (".implode(",", getUsuRegOtroSlavesIds($_SESSION['rec_usu_id']))."))";
	}
	else {
		$q_usu = 'AND e.usu_id='.$_SESSION['rec_usu_persona_id'];
	}

include 'datos_abrir_bd.php';

$query="SELECT e.ear_id, 
   	(case
        when (concat(ifnull(sp3.nombre,''),' ',ifnull(sp3.apellido_paterno,''),' ',ifnull(sp3.apellido_materno,''))) <>'' then
            concat(ifnull(sp3.nombre,'1'),' ',ifnull(sp3.apellido_paterno,''),' ',ifnull(sp3.apellido_materno,''))
        else concat(ifnull(sp1.nombre,'2'),' ',ifnull(sp1.apellido_paterno,''),' ',ifnull(sp1.apellido_materno,''))
        end) as ear_tra_nombres,
    concat(e.ear_anio, '-', LPAD(e.ear_mes, 2, '0'), '-', LPAD(e.ear_nro, 3, '0'), '/',
    fn_obtener_iniciales(concat(ifnull(sp.nombre,''),' ',ifnull(sp.apellido_paterno,''),' ',ifnull(sp.apellido_materno,'')))) as ear_numero,
	z.zona_nom, m.mon_nom, m.mon_iso, m.mon_img, e.ear_monto, e.est_id, te.est_nom, e.ear_sol_fec, e.ear_liq_fec,
	concat(sp2.nombre,' ',sp2.apellido_paterno,' ',sp2.apellido_materno) as usu_act, e.ear_act_fec,
    e.ear_act_motivo, e.master_usu_id,e.ear_sol_motivo
FROM ear_solicitudes e
    LEFT JOIN ear_zonas z ON z.zona_id=e.zona_id
    LEFT JOIN monedas m ON m.mon_id=e.mon_id
    LEFT JOIN tablas_estados te ON te.est_id=e.est_id
    JOIN tablas_nombres tn ON tn.tabla_id=te.tabla_id AND tabla_nom='ear_solicitudes'
    LEFT JOIN ".$baseSGI.".usuario su2 ON su2.id=e.ear_act_usu
    left join ".$baseSGI.".persona sp2 on sp2.id=su2.persona_id
	left join " . $baseSGI . ".usuario su on su.id=e.usuario_reg_id
	left join " . $baseSGI . ".persona sp on sp.id=su.persona_id # para usuario_reg
	left join " . $baseSGI . ".usuario su1 on su1.id=e.usu_id
	left join " . $baseSGI . ".persona sp1 on sp1.id=su1.persona_id #para solicitud normal
	left join " . $baseSGI . ".persona sp3 on sp3.id=e.usu_id #para terceros
WHERE e.est_id=4 ".$q_usu."
LIMIT 2000";
$result = $mysqli->query($query) or die ($mysqli->error);

while($fila=$result->fetch_array()){
	echo "\t<tr class='gradeA'>\n";
	echo "\t\t<td>".(is_null($fila['master_usu_id'])?$fila['ear_tra_nombres']:"<span title='Registrado por ".getUsuarioNombre($fila['master_usu_id'])."'>".$fila['ear_tra_nombres']." <img src='img/por_otro.png' class='iconos'></span>")."</td>\n";
	echo "\t\t<td>".$fila['ear_numero']."</td>\n";
	echo "\t\t<td ".(strlen($fila['ear_sol_motivo'])>$longMotivo?"title='".$fila['ear_sol_motivo']."'":"").">".obtenerCadenaXNumLetras($fila['ear_sol_motivo'],$longMotivo)."</td>\n";
	echo "\t\t<td><span title='".$fila['mon_nom']."'>".$fila['mon_iso']." <img src='".$fila['mon_img']."' class='iconos'></span></td>\n";
	echo "\t\t<td align='right'>".$fila['ear_monto']."</td>\n";

	echo "\t\t<td>".$fila['est_nom'];

	$est_msj = "Ultima actualizaci&oacute;n por ".$fila['usu_act']."\nFecha: ".$fila['ear_act_fec'];
	echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";

	echo "</td>\n";

	echo "\t\t<td>".$fila['ear_sol_fec']."</td>\n";
	echo "\t\t<td>".$fila['ear_liq_fec']."</td>\n";

	echo "\t\t<td>";
	echo "<a href='ear_consulta_detalle.php?id=".$fila['ear_id']."'><img src='img/search.png' border='0' title='Detalle de la solicitud' class='iconos'></a>";
	echo "<a href='ear_liq_registro.php?id=".$fila['ear_id']."'><img src='img/liquidar.png' title='Liquidar' border='0' class='iconos'></a> ";
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
	<img src='img/liquidar.png' title='Liquidar' class='iconos'> Liquidar &nbsp;&nbsp;&nbsp;
	<img src='img/por_otro.png' title='Realizado por otro usuario' class='iconos'> Realizado por otro usuario &nbsp;&nbsp;&nbsp;
</p>
<b>Nota:</b> M&aacute;ximo se muestran 2000 registros por consulta web.<br>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
