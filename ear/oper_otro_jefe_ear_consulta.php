<?php
include ("seguridad.php");
include 'func.php';

// Valores cons_id:
// 1: Consulta normal (usuarios comunes): si la solicitud esta en estado solicitado se puede editar,
//		no deben ver opciones de aprobacion, solo deben ver sus propios EAR, acceso todos,
//		debe mostrar link de descarga del pdf de la solicitud, y el pdf de la liquidacion (si es que existe)
// 2: Aprobadores de solicitud EAR: deben aparecer opciones de aprobacion (tambien las ya aprobadas),
//		solo ven los EAR de los colaboradores a su cargo, acceso jefes y gerentes.
//		Si entra rol TI puede ver todo.
// 3: Consulta para contabilidad: deben aparecer adicionalmente las opciones de descarga archivos excel. pueden ver los EAR de todos.
//		solo se ven a partir del estado de solicitud aprobada. (estados de ear 4 al 12) acceso usuarios de conta y TI.
// 4: Consulta para tesoreria: opciones para ver pdfs, ver todos los ear en estado solicitud desembolsada y liquidacion final reembolsada efectuadas.
//		(estados de ear 4 y 10) acceso usuario de tesoreria y TI.
// 5: Consulta para compensaciones: opciones para ver pdfs, ver todos los ear en estado descontado. (estado 12) acceso compensaciones y TI.
// 6: Consulta para administracion: opciones para ver pdfs, ver todos los ear en todos los estados, desembolsos pendientes, pendientes de liquidar.
//		acceso admin y TI.

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_cons_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
$cons_id = abs((int) filter_var($f_cons_id, FILTER_SANITIZE_NUMBER_INT));

$opc_id = 0;
if (isset($f_opc_id)) {
	$opc_id = abs((int) filter_var($f_opc_id, FILTER_SANITIZE_NUMBER_INT));
}

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

$count = 0;
$rpt_nom = "";
$col8 = "Fecha liq";
if ($cons_id == 1) {
	$count = 1;
	
	if ($opc_id == 1) {
		$rpt_nom = "EAR Pendientes de liquidar";
		$col8 = "Fecha desemb";
	}
	else if ($opc_id == 2) {
		$rpt_nom = "Liquidaciones pendientes de reembolsar";
		$col8 = "Fecha reemb aprox";
	}
	else if ($opc_id == 3) {
		$rpt_nom = "Liquidaciones pendientes de descontar";
		$col8 = "Fecha desc aprox";
	}
	else if ($opc_id == 10) {
		$rpt_nom = "EAR de usuarios asignados";
	}
	else if ($opc_id == 11) {
		$rpt_nom = "EAR Pendientes de liquidar de usuarios asignados";
		$col8 = "Fecha desemb";
	}
	else if ($opc_id == 12) {
		$rpt_nom = "Liquidaciones pendientes de reembolsar de usuarios asignados";
		$col8 = "Fecha reemb aprox";
	}
	else if ($opc_id == 13) {
		$rpt_nom = "Liquidaciones pendientes de descontar de usuarios asignados";
		$col8 = "Fecha desc aprox";
	}
}
else if ($cons_id == 2) {
	$count = getPermisosAdministrativos($otro_jefe_usu_id, 'ADMINIST');
	$count += getPermisosAdministrativos($otro_jefe_usu_id, 'JEFEOGERENTE');
	$count += getPermisosAdministrativos($otro_jefe_usu_id, 'TI');
}
else if ($cons_id == 3) {
	$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'SUP_CONT');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'REG_CONT');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ANA_CONT');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
	
	if ($opc_id == 1) {
		$rpt_nom = "Descarga Excel Liquidaciones";
	}
	else if ($opc_id == 2) {
		$rpt_nom = "EAR Pendientes de liquidar";
		$col8 = "Fecha desemb";
	}
}
else if ($cons_id == 4) {
	$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TESO');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'REG_CONT');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ANA_CONT');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'SUP_CONT');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
	
	if ($opc_id == 1) {
		$rpt_nom = "Solicitudes desembolsadas";
		$col8 = "Fecha desemb";
	}
	else if ($opc_id == 2) {
		$rpt_nom = "Liquidaciones reembolsadas";
		$col8 = "Fecha reemb";
	}
}
else if ($cons_id == 5) {
	$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'COMP');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'REG_CONT');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ANA_CONT');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'SUP_CONT');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
	
	$rpt_nom = "Liquidaciones descontadas";
	$col8 = "Fecha desc";
}
else if ($cons_id == 6) {
	$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
	$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
	
	if ($opc_id == 2) {
		$rpt_nom = "Todos los EAR";
		$col8 = "Fecha ult act";
		
		$arrSemaforo = getValoresSemaforoEAR();
	}
	else {
		$rpt_nom = "Desembolsos pendientes";
	}
}

if ($count == 0) {
	echo "<font color='red'><b>ERROR:</b> P&aacute;gina no existe</font><br>";
	exit;	
}

$zon_id = 255; //Zona por defecto al cargar los resultados
if (isset($_SESSION['t_zona'])) {
	$zon_id = $_SESSION['t_zona'];
	unset($_SESSION['t_zona']);
}

$mon_id = 255; //Moneda por defecto al cargar los resultados
if (isset($_SESSION['t_moneda'])) {
	$mon_id = $_SESSION['t_moneda'];
	unset($_SESSION['t_moneda']);
}

$est_id = 255; //Estado por defecto al cargar los resultados
if (isset($_SESSION['t_estado'])) {
	$est_id = $_SESSION['t_estado'];
	unset($_SESSION['t_estado']);
}
else if (isset($f_est_id)) {
	$est_id = abs((int) filter_var($f_est_id, FILTER_SANITIZE_NUMBER_INT));
}

$date = new DateTime();
$rfecha4 = $date->format("Y-m-d");
$date->modify('-9 month');
$rfecha2 = $date->format("Y-m-01");
if (isset($_SESSION['t_fecha2'])) {
	$rfecha2 = $_SESSION['t_fecha2'];
	$rfecha4 = $_SESSION['t_fecha4'];
	unset($_SESSION['t_fecha2']);
	unset($_SESSION['t_fecha4']);
}

// Cantidad maxima de registros a mostrar
$max = 5000;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Reporte de Entregas a Rendir (EAR) - Administraci�n MinappES</title>
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
<style type="text/css">
	.dataTables_filter {
	display: none;
}
</style>
<script type="text/javascript" language="javascript" src="js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf-8">
$(document).ready(function() {
	var color = '';
	
	var oTable = $('#example').dataTable( {
		"bJQueryUI": true,
		"sPaginationType": "full_numbers",
<?php
if (($cons_id == 3 && $opc_id == 2) || ($cons_id == 1 && $opc_id == 1) || ($cons_id == 1 && $opc_id == 11) || ($cons_id == 6 && $opc_id == 2)) {
?>
		"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 9 ] } ],
<?php
}
else {
?>
		"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 8 ] } ],
<?php
}
?>
		"bStateSave": false,
		"aaSorting": [[ 6, "desc" ]],
		"bProcessing": true,
		"iDisplayLength": 25,
		"oLanguage": { "sUrl": "i18n/dataTables.spanish.txt" }
	});

	$('#nombre').keyup(function(){
		  oTable.fnFilter( $(this).val() + color );
	});

	$('#verde').click(function(){
		color = ' button-green';
		$(document).filtrocolor_redraw();
	});

	$('#ambar').click(function(){
		color = ' button-yellow';
		$(document).filtrocolor_redraw();
	});

	$('#rojo').click(function(){
		color = ' button-red';
		$(document).filtrocolor_redraw();
	});

	$('#blanco').click(function(){
		color = '';
		$(document).filtrocolor_redraw();
	});
	
	$.fn.filtrocolor_redraw = function() {
		var nombre = $('#nombre').val();
		oTable.fnFilter( nombre + color );
	};
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
		altFormat: "yy-mm-dd",
		onClose: function( selectedDate ) {	$( "#rfecha3" ).datepicker( "option", "minDate", selectedDate ); }
	});
	$( "#rfecha" ).datepicker( $.datepicker.regional[ "es" ] );
	$( "#rfecha" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
	$( '#rfecha' ).datepicker( 'setDate', '<?php echo $rfecha2; ?>' );
	$( "#rfecha" ).datepicker( "option", "dateFormat", "D, d M yy" );

	$( "#rfecha3" ).datepicker({
		numberOfMonths: 1,
		altField: "#rfecha4",
		altFormat: "yy-mm-dd",
		onClose: function( selectedDate ) { $( "#rfecha" ).datepicker( "option", "maxDate", selectedDate ); }
	});
	$( "#rfecha3" ).datepicker( $.datepicker.regional[ "es" ] );
	$( "#rfecha3" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
	$( '#rfecha3' ).datepicker( 'setDate', '<?php echo $rfecha4; ?>' );
	$( "#rfecha3" ).datepicker( "option", "dateFormat", "D, d M yy" );
	
	$( "#rfecha5" ).datepicker({
		numberOfMonths: 1,
		altField: "#rfecha6",
		altFormat: "yy-mm-dd"
	});
	$( "#rfecha5" ).datepicker( $.datepicker.regional[ "es" ] );
	$( "#rfecha5" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
	$( '#rfecha5' ).datepicker( 'setDate', '<?php echo date("Y-m-d"); ?>' );
	$( "#rfecha5" ).datepicker( "option", "dateFormat", "D, d M yy" );

});
</script>
<!--Seccion Date Picker-->

<script>
function exportar() {
	var nombre = encodeURIComponent(document.getElementById("nombre").value);
	location.href="invalid_excel.php?fec_ini=<?php echo $rfecha2; ?>&fec_fin=<?php echo $rfecha4; ?>&est_id=<?php echo $est_id; ?>&nom="+nombre;
}
</script>

<script>
function editar(id) {
	var motivo=prompt("Ingrese el motivo de rechazo de la solicitud");
	motivo=motivo.trim();
	
	if(motivo.length==0) { motivo=null; alert("ERROR: No se puede continuar, el motivo no puede estar vacio."); }

	if(motivo!=null) { location.href="oper_otro_jefe_ear_sol_vistobueno_p.php?id="+id+"&opcion=3&motivo="+motivo+"&otro_jefe_usu_id=<?php echo $otro_jefe_usu_id; ?>"; }
}

function cancelar(id) {
	var motivo=prompt("Ingrese el motivo de cancelacion de la solicitud");
	motivo=motivo.trim();
	
	if(motivo.length==0) { motivo=null; alert("ERROR: No se puede continuar, el motivo no puede estar vacio."); }

	if(motivo!=null) { location.href="ear_sol_vistobueno_p.php?id="+id+"&opcion=41&motivo="+motivo; }
}
</script>

<script>
$(document).ready(function()
{
	$('.ear_excel_liq').click(function(){
		var posting_date = $('#rfecha6').val();
		var ear_id = $(this).attr('fila');
		location.href="ear_excel_liq.php?id="+ear_id+"&pd="+posting_date;
	});
});
</script>

<style>
.iconos {
	vertical-align:text-top;
}

.iconos_sel {
	vertical-align:text-top;
	cursor: pointer;
}
</style>
</head>
<body class="ex_highlight_row">
<?php include ("header.php"); ?>

<h1>Reporte de Entregas a Rendir (EAR)</h1>

<b>Jefe seleccionado: <?php echo getUsuarioNombre($otro_jefe_usu_id); ?></b><br>

<?php
if (strlen($rpt_nom)>0) echo "<p style='font-weight:bold;'>Tipo de reporte: $rpt_nom</p>";
?>

<form method="post" action="oper_otro_jefe_ear_consulta_p.php">
<p>Fecha inicio <input type="text" id="rfecha" name="rfecha" value="<?php echo $rfecha2; ?>" readonly><input type="hidden" id="rfecha2" name="rfecha2" /> -
Fecha fin <input type="text" id="rfecha3" name="rfecha3" value="<?php echo $rfecha4; ?>" readonly><input type="hidden" id="rfecha4" name="rfecha4" />
- Zona <select name="zona">
	<option value="01" <?php if($zon_id=="01") echo "selected"; ?>>Nacional</option>
	<option value="255" <?php if($zon_id=="255") echo "selected"; ?>>Todos</option>
</select>
- Moneda <select name="moneda">
	<option value="1" <?php if($mon_id==1) echo "selected"; ?>>PEN</option>
	<option value="2" <?php if($mon_id==2) echo "selected"; ?>>USD</option>
	<option value="255" <?php if($mon_id==255) echo "selected"; ?>>Todos</option>
</select>
- Estado <select name="estado">
	<option value="1" <?php if($est_id==1) echo "selected"; ?>>Solicitado</option>
	<option value="2" <?php if($est_id==2) echo "selected"; ?>>Solicitud Aprobada</option>
	<option value="3" <?php if($est_id==3) echo "selected"; ?>>Solicitud Rechazada</option>
	<option value="4" <?php if($est_id==4) echo "selected"; ?>>Desembolsado</option>
	<option value="5" <?php if($est_id==5) echo "selected"; ?>>Liquidacion Enviada</option>
	<option value="6" <?php if($est_id==6) echo "selected"; ?>>Aprobado Jefatura/Gerencia</option>
	<option value="7" <?php if($est_id==7) echo "selected"; ?>>Visto Bueno Administracion</option>
	<option value="8" <?php if($est_id==8) echo "selected"; ?>>Actualizado por Contabilidad</option>
	<option value="9" <?php if($est_id==9) echo "selected"; ?>>Visto Bueno Analista de Cuentas</option>
	<option value="10" <?php if($est_id==10) echo "selected"; ?>>Reembolso Efectuado</option>
	<option value="11" <?php if($est_id==11) echo "selected"; ?>>Descuento Efectuado</option>
	<option value="12" <?php if($est_id==12) echo "selected"; ?>>Liquidacion Cerrada</option>
	<option value="255" <?php if($est_id==255) echo "selected"; ?>>Todos</option>
</select>
<input type="hidden" name="cons_id" value="<?php echo $cons_id;?>">
<input type="hidden" name="opc_id" value="<?php echo $opc_id;?>">
<input type="submit" value="Enviar"><br>
Busqueda: <input type="text" id="nombre">
<?php
if ($cons_id == 6 && $opc_id == 2) {
?>
<img src='img/button-green-icon.png' title='Verde' id='verde' class='iconos_sel'>
<img src='img/button-yellow-icon.png' title='Ambar' id='ambar' class='iconos_sel'>
<img src='img/button-red-icon.png' title='Rojo' id='rojo' class='iconos_sel'>
<img src='img/button-gray-icon.png' title='Todos' id='blanco' class='iconos_sel'>
<?php
}
?>
<br>
Exportar: <span onclick='exportar();' style='cursor: pointer'><img src='img/excel.gif' title='Formato Excel' border='0'></span></p>

</form>

<p style="display:<?php echo ($cons_id == 3 && $opc_id == 1?"block":"none"); ?>;">Fijar valor de Posting Date <input type="text" id="rfecha5" name="rfecha5" readonly><input type="hidden" id="rfecha6" name="rfecha6" /></p>

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
			<th><?php echo $col8; ?></th>
<?php
if (($cons_id == 3 && $opc_id == 2) || ($cons_id == 1 && $opc_id == 1) || ($cons_id == 1 && $opc_id == 11) || ($cons_id == 6 && $opc_id == 2)) {
?>
			<th>Dias trans</th>
<?php
}
?>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
<?php
$arr = getListaEARs($max, $cons_id, $_SESSION['rec_usu_id'], $zon_id, $mon_id, $est_id, $rfecha2, $rfecha4, $opc_id, $otro_jefe_usu_id);

foreach ($arr as $v) {
	// Valores de v:
	// 0 = ear_id
	// 1 = ear_tra_nombres
	// 2 = ear_numero
	// 3 = zona_nom
	// 4 = mon_nom
	// 5 = mon_iso
	// 6 = mon_img
	// 7 = ear_monto
	// 8 = est_id
	// 9 = est_nom
	// 10 = ear_sol_fec
	// 11 = ear_liq_fec
	// 12 = usu_act
	// 13 = ear_act_fec
	// 14 = ear_act_motivo
	
	// cons 4, opc 1:
	// 15 = ear_dese_fec

	// cons 3, opc 2:
	// 15 = dias_trans
	
	// 17 = el usuario que realizo el registro por otro usuario
	
	// if (strlen($fila['vac_fec_ini'])>0) $vac_fec_fin = date('Y-m-d', strtotime($fila['vac_fec_ini']."+".($fila['vac_dias_sol']-1)." days")); else $vac_fec_fin=null;
	// $nota = "DNI ".$fila['tra_dni']."<br>".$fila['tra_cargo']." ".$fila['tra_area']."<br>Fecha Ingreso ".$fila['tra_fec_ing']."<br>".$fila['tra_banco']." ".$fila['tra_ctacte']."<br><br>";

	// $nota .= "<i>Detalle:</i><br>";
	// $query2 = "SELECT vac_periodo_desde, vac_periodo_hasta, vac_dias_sol FROM vac_detalle WHERE vac_id=".$fila['vac_id'];
	// $result2 = $mysqli->query($query2) or die ($mysqli->error);
	// while($fila2=$result2->fetch_array()){
		// $nota .= "Periodo ".$fila2['vac_periodo_desde']."-".$fila2['vac_periodo_hasta'].": ".$fila2['vac_dias_sol']." dia(s)<br>";
	// }

	// switch ($fila['vac_estado']) {
		// case 1:
			// $estado = "<span style='font-weight:bold;color:black;background-color:yellow;'>&nbsp;Solicitado&nbsp;</span>";
			// break;
		// case 2:
			// $estado = "<span style='font-weight:bold;color:white;background-color:green;'>&nbsp;Aprobado&nbsp;</span> por ".$fila['aprob_nombre']."<br>(".$fila['vac_fec_jefe'].")";
			// break;
		// case 3:
			// $estado = "<span style='font-weight:bold;color:white;background-color:red;'>&nbsp;Rechazado&nbsp;</span> por ".$fila['aprob_nombre']."<br>(".$fila['vac_fec_jefe'].")";
			// if (strlen($fila['vac_rechazo_jefe'])>0) $estado .= "<br>Motivo: <i>".$fila['vac_rechazo_jefe']."</i>";
			// break;
		// case 4:
			// $estado = "<span style='font-weight:bold;color:white;background-color:green;'>&nbsp;Aprobado&nbsp;</span> por ".$fila['aprob_nombre']."<br>(".$fila['vac_fec_jefe'].")";
			// $estado .= "<br><br><span style='font-weight:bold;color:black;background-color:cyan;'>&nbsp;Preparado&nbsp;</span> por Compensaciones<br>(".$fila['vac_fec_comp'].")";
			// break;
		// case 5:
			// $estado = "<span style='font-weight:bold;color:white;background-color:green;'>&nbsp;Aprobado&nbsp;</span> por ".$fila['aprob_nombre']."<br>(".$fila['vac_fec_jefe'].")";
			// $estado .= "<br><br><span style='font-weight:bold;color:black;background-color:cyan;'>&nbsp;Preparado&nbsp;</span> por Compensaciones<br>(".$fila['vac_fec_comp'].")";
			// $estado .= "<br><br><span style='font-weight:bold;color:white;background-color:blue;'>&nbsp;Ejecutado&nbsp;</span> por Tesorer�a<br>(".$fila['vac_fec_teso'].")";
			// break;
		// case 101:
			// $estado = "<span style='font-weight:bold;color:white;background-color:purple;'>&nbsp;Anulado&nbsp;</span> por ".getUsuNom($fila['vac_usu_anul'])."<br>(".$fila['vac_fec_anul'].")";
			// $estado .= "<br>Motivo: <i>".$fila['vac_motivo_anul']."</i>";
			// break;
		// default:
			// $estado = "Invalido";
	// }

	echo "\t<tr class='gradeA'>\n";
	echo "\t\t<td>".(is_null($v[17])?$v[1]:"<span title='Registrado por ".getUsuarioNombre($v[17])."'>$v[1] <img src='img/por_otro.png' class='iconos'></span>")."</td>\n";
	echo "\t\t<td>".$v[2]."</td>\n";
	echo "\t\t<td>".$v[3]."</td>\n";
	echo "\t\t<td><span title='".$v[4]."'>".$v[5]." <img src='".$v[6]."' class='iconos'></span></td>\n";
	if (($cons_id == 1 && $opc_id == 2) || ($cons_id == 1 && $opc_id == 12)) {
		echo "\t\t<td align='right'>".conComas($v[15]*-1)."</td>\n";
	}
	else if (($cons_id == 1 && $opc_id == 3) || ($cons_id == 1 && $opc_id == 13)) {
		echo "\t\t<td align='right'>".conComas($v[15])."</td>\n";
	}
	else if ($cons_id == 4 && $opc_id == 2) {
		echo "\t\t<td align='right'>".conComas($v[15]*-1)."</td>\n";
	}
	else if ($cons_id == 5) {
		echo "\t\t<td align='right'>".conComas($v[15])."</td>\n";
	}
	else {
		echo "\t\t<td align='right'>".$v[7]."</td>\n";
	}
	
	echo "\t\t<td>".$v[9];
	if ($v[8]>=2) {
		$est_msj = "Ultima actualizaci�n por ".$v[12]."\nFecha: ".$v[13];
		if ($v[8]==3) $est_msj .= "\nMotivo rechazo: ".$v[14];
		
		echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";
	}
	echo "</td>\n";
	
	echo "\t\t<td>".$v[10]."</td>\n";
	if ($cons_id == 1) {
		if ($opc_id == 1 || $opc_id == 11) {
			echo "\t\t<td>".$v[13]."</td>\n";
			echo "\t\t<td>".$v[15]."</td>\n";
		}
		else if ($opc_id == 2 || $opc_id == 12) {
			$dia = date('N', strtotime($v[13]));
			if ($dia == 4) {
				$viernes = date('d/m/Y', strtotime('next friday', strtotime($v[13].' + 1 day')));
			}
			else {
				$viernes = date('d/m/Y', strtotime('next friday', strtotime($v[13])));
			}

			echo "\t\t<td>Viernes $viernes</td>\n";
		}
		else if ($opc_id == 3 || $opc_id == 13) {
			$anio = date('Y', strtotime($v[13]));
			$mes = date('m', strtotime($v[13]));
			$fecha_tope = getDiaTopeDescuentos($anio, $mes);
			
			if ($fecha_tope=='') {
				$msj = "Finales de este mes o el siguiente";
			}
			else {
				if (strtotime($v[13])<=strtotime($fecha_tope." 23:59:59")) {
					$msj = "Finales de ".nombreMes($mes)." $anio";
				}
				else {
					$mes_prox = date('m', strtotime($v[13].' + 1 month'));
					$msj = "Finales de ".nombreMes($mes_prox)." $anio";
				}
			}
			
			echo "\t\t<td>$msj</td>\n";
		}
		else {
			if ($v[8] < 5) {
				echo "\t\t<td>".$v[11]."</td>\n";
			}
			else {
				echo "\t\t<td>".getFechaEnvioLiq($v[0])."</td>\n";
			}
		}
	}
	else if ($cons_id == 3) {
		if ($opc_id == 1) {
			//echo "\t\t<td>".$v[11]."</td>\n";
			echo "\t\t<td>".getFechaEnvioLiq($v[0])."</td>\n";
		}
		else {
			echo "\t\t<td>".$v[13]."</td>\n";
			echo "\t\t<td>".$v[15]."</td>\n";
		}
	}
	else if ($cons_id == 4) {
		if ($opc_id == 1) {
			echo "\t\t<td>".$v[15]."</td>\n";
		}
		else {
			echo "\t\t<td>".$v[13]."</td>\n";
		}
	}
	else if ($cons_id == 5) {
		echo "\t\t<td>".$v[13]."</td>\n";
	}
	else if ($cons_id == 6 && $opc_id == 2) {
		echo "\t\t<td>".$v[13]."</td>\n";
		if ($v[8]<10 && $v[8]!=3) {
			if ($v[8]==9 && $v[16]==0) {
				echo "\t\t<td></td>\n";
			}
			else {
				echo "\t\t<td align='right'>".$v[15]."</td>\n";
			}
		}
		else {
			echo "\t\t<td></td>\n";
		}
	}
	else {
		if ($v[8] < 5) {
			echo "\t\t<td>".$v[11]."</td>\n";
		}
		else {
			echo "\t\t<td>".getFechaEnvioLiq($v[0])."</td>\n";
		}
	}

	echo "\t\t<td style='white-space:nowrap;'>";
	echo "<a href='ear_consulta_detalle.php?id=".$v[0]."'><img src='img/search.png' border='0' title='Detalle de la Solicitud' class='iconos'></a> ";
	
	// Columna fija del icono de la edicion de la solicitud EAR
	if (($cons_id==1 || $cons_id==2) && $v[8]==1) {
		echo "<a href='ear_editar.php?id=".$v[0]."'><img src='img/edit.png' border='0' title='Editar solicitud' class='iconos'></a> ";
	}
	
	// Columna dinamica del icono de la cancelacion de la solicitud EAR (por parte del colaborador)
	if ($cons_id==1 && $v[8]==1) {
		echo "<span onclick='cancelar(\"".$v[0]."\");' style='cursor: pointer'><img src='img/delete.png' title='Cancelar solicitud' border='0' class='iconos'></span> ";
	}
	
	// Columna dinamica de iconos aprobar y desaprobar que solo aparece para jefes y gerentes cuando eligen la opcion de aprobar solicitudes EAR
	if ($cons_id==2 && $v[8]==1) {
		echo "<a href='oper_otro_jefe_ear_sol_vistobueno_p.php?id=".$v[0]."&opcion=2&otro_jefe_usu_id=$otro_jefe_usu_id' onClick=\"return confirm('Est&aacute; seguro que desea aprobar la solicitud?')\"><img src='img/opc-si.gif' title='Aprobar' border='0' class='iconos'></a> ";
		echo "<span onclick='editar(\"".$v[0]."\");' style='cursor: pointer'><img src='img/opc-no.gif' title='Rechazar' border='0' class='iconos'></span> ";
	}
	
	// Columna fija del icono de la descarga PDF de la solicitud EAR
	if ($v[8]==1 || $v[8]==3 || $v[8]==41) {
		echo "<img src='img/transparent.gif' border='0' class='iconos'>\n";
	}
	else {
		echo "<a href='ear_pdf_sol.php?id=".$v[0]."'><img src='img/pdf.gif' border='0' title='Descargar PDF Solicitud EAR' class='iconos'></a>\n";
	}
	
	// Columna fija del icono de visualizar y la descarga PDF de la liquidacion EAR
	if ($v[8]>=7 && $v[8]<=12) {
		echo "<a href='ear_liq_consulta.php?id=".$v[0]."'><img src='img/search.png' border='0' title='Visualizar la Liquidacion EAR' class='iconos'></a>\n";
		echo "<a href='ear_pdf_liq.php?id=".$v[0]."'><img src='img/pdf.gif' border='0' title='Descargar PDF Liquidacion EAR' class='iconos'></a>\n";
	}
	else {
		echo "<img src='img/transparent.gif' border='0' class='iconos'>\n";
		echo "<img src='img/transparent.gif' border='0' class='iconos'>\n";
	}
	
	// Columnas dinamicas de los iconos de descarga de excel de liquidacion
	if ($cons_id==3) {
		if ($opc_id==1) {
			echo "<span class='ear_excel_liq' fila='".$v[0]."' style='cursor: pointer'><img src='img/l.png' border='0' title='Descargar Excel Liquidacion' class='iconos'></span>\n";
			echo "<a href='ear_excel_ret.php?id=".$v[0]."'><img src='img/r.png' border='0' title='Descargar Excel Retenciones' class='iconos'></a>\n";
			echo "<a href='ear_excel_det.php?id=".$v[0]."'><img src='img/d.png' border='0' title='Descargar Excel Detracciones' class='iconos'></a>\n";
			echo "<a href='ear_excel_acf.php?id=".$v[0]."'><img src='img/a.png' border='0' title='Descargar Excel Activo Fijo' class='iconos'></a>\n";
			echo "<a href='ear_excel_aju.php?id=".$v[0]."'><img src='img/ajustes.png' border='0' title='Descargar Excel Ajustes' class='iconos'></a>\n";
		}
	}

	// Semaforo
	if (($cons_id == 3 && $opc_id == 2) || ($cons_id == 1 && $opc_id == 1) || ($cons_id == 1 && $opc_id == 11)) {
		switch (true) {
			case ($v[15]>=0 && $v[15]<=20):
				echo "<img src='img/button-green-icon.png' title='Verde' class='iconos'>";
				break;
			case ($v[15]>=21 && $v[15]<=44):
				echo "<img src='img/button-yellow-icon.png' title='Ambar' class='iconos'>";
				break;
			case ($v[15]>=45):
				echo "<img src='img/button-red-icon.png' title='Rojo' class='iconos'>";
				break;
			default:
		}
	}
	if ($cons_id == 6 && $opc_id == 2) {
		if ($v[8]<10 && $v[8]!=3) {
			if ($v[8]==9 && $v[16]<0) {
				$arrValEstado = $arrSemaforo[10];
			}
			else if ($v[8]==9 && $v[16]>0) {
				$arrValEstado = $arrSemaforo[11];
			}
			else {
				$arrValEstado = $arrSemaforo[$v[8]];
			}
			$val_min_verde = $arrValEstado[0];
			$val_min_ambar = $arrValEstado[1];
			$val_min_rojo = $arrValEstado[2];
			switch (true) {
				case ($v[15]>=$val_min_verde && $v[15]<$val_min_ambar):
					echo "<img src='img/button-green-icon.png' title='Verde' class='iconos'>";
					break;
				case ($v[15]>=$val_min_ambar && $v[15]<$val_min_rojo):
					echo "<img src='img/button-yellow-icon.png' title='Ambar' class='iconos'>";
					break;
				case ($v[15]>=$val_min_rojo):
					echo "<img src='img/button-red-icon.png' title='Rojo' class='iconos'>";
					break;
				default:
			}
		}
	}
	
	
	// if ($es_rrhh > 0 && $fila['vac_estado']!=3 && $fila['vac_estado']!=101) {
		// echo "<span onclick='anular(\"".$fila['vac_id']."\");' style='cursor: pointer'><img src='img/anular.gif' title='Anular registro'></span>";
	// }
	echo "</td>\n";

	echo "\t</tr>\n";
}

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
			<th><?php echo $col8; ?></th>
<?php
if (($cons_id == 3 && $opc_id == 2) || ($cons_id == 1 && $opc_id == 1) || ($cons_id == 1 && $opc_id == 11) || ($cons_id == 6 && $opc_id == 2)) {
?>
			<th>Dias trans</th>
<?php
}
?>
			<th>Acciones</th>
		</tr>
	</tfoot>
</table>
</div>

<div style="clear:left">
<p><b>Leyenda:</b>
	<img src='img/search.png' title='Detalle' class='iconos'> Detalle de la solicitud &nbsp;&nbsp;&nbsp;
<?php
if ($cons_id==1 || $cons_id==2) {
?>
	<img src='img/edit.png' title='Editar solicitud' class='iconos'> Editar solicitud &nbsp;&nbsp;&nbsp;
<?php
}
if ($cons_id==1) {
?>
	<img src='img/delete.png' title='Cancelar solicitud'> Cancelar solicitud &nbsp;&nbsp;&nbsp;
<?php
}
if ($cons_id==2) {
?>
	<img src='img/opc-si.gif' title='Aprobar' class='iconos'> Aprobar &nbsp;&nbsp;&nbsp;
	<img src='img/opc-no.gif' title='Rechazar' class='iconos'> Rechazar &nbsp;&nbsp;&nbsp;
<?php
}
?>
	<img src='img/pdf.gif' title='Descargar PDF Solicitud EAR' class='iconos'> Descargar PDF Solicitud EAR &nbsp;&nbsp;&nbsp;
	<img src='img/pdf.gif' title='Descargar PDF Liquidacion EAR' class='iconos'> Descargar PDF Liquidacion EAR &nbsp;&nbsp;&nbsp;
<?php
if ($cons_id==3) {
	if ($opc_id==1) {
?>
	<img src='img/l.png' title='Liquidacion' class='iconos'>
	<img src='img/r.png' title='Retenciones' class='iconos'>
	<img src='img/d.png' title='Detracciones' class='iconos'>
	<img src='img/a.png' title='Activo Fijo' class='iconos'>
	<img src='img/ajustes.png' title='Ajustes' class='iconos'> Descargar archivos Excel &nbsp;&nbsp;&nbsp;
<?php
	}
}
if (($cons_id == 3 && $opc_id == 2) || ($cons_id == 1 && $opc_id == 1) || ($cons_id == 1 && $opc_id == 11) || ($cons_id == 6 && $opc_id == 2)) {
?>
	<img src='img/button-green-icon.png' title='Verde' class='iconos'>
	<img src='img/button-yellow-icon.png' title='Ambar' class='iconos'>
	<img src='img/button-red-icon.png' title='Rojo' class='iconos'> Semaforo &nbsp;&nbsp;&nbsp;
<?php
}
?>
	<img src='img/por_otro.png' title='Realizado por otro usuario' class='iconos'> Realizado por otro usuario &nbsp;&nbsp;&nbsp;
</p>
<b>Nota:</b> M&aacute;ximo se muestran <?php echo $max; ?> registros por consulta web, la exportaci&oacute;n a hoja excel no tiene limites de registros por consulta.<br>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
