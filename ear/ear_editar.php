<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'parametros.php';

// Los usuarios que tienen acceso a a esta pagina: el que genero la solicitud, su jefe directo, su gerente directo, el administ, el de TI.
// Y solo se puede acceder a esta pagina si el estado de la solicitud esta en valor 1

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
	$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
	$usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
	$ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
	$ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
	$master_usu_id, $a1, $a2,$a3,$a4,$a5,$a5,$a6,$a7,$a8,$a9,$usuario_reg_id) = getSolicitudInfo($id);
if ($est_id!=1) {
	echo "<font color='red'><b>ERROR: No se puede editar esta solicitud</b></font><br>";
	exit;
}

list($mon_nom, $mon_iso, $mon_simb, $mon_img) = getNomMoneda($mon_id);
if ($mon_nom=="Sin definir") {
	echo "<font color='red'><b>ERROR: Moneda inv�lida / sin definir</b></font><br>";
	exit;
}

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'JEFEOGERENTE');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pGERENTE);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pASISTENTE_ADMINISTRATIVO);
if ($usu_id != $_SESSION['rec_usu_persona_id']) {
	if($usuario_reg_id != $_SESSION['rec_usu_persona_id']){
		if($count == 0){
			echo "<font color='red'><b>ERROR: No tiene acceso a esta pagina. Su intento de acceso ha sido reportado.</b></font><br>";
			exit;
		}
	}
}

$arrDet = getSolicitudDetalle($id);

$hosp_otros_id = getIdHospOtros($mon_id);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Editar Solicitud EAR - Administraci�n - Minapp</title>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
	.titulo {font-size: 14pt; font-family: arial,helvetica}
</style>

<script type="text/javascript" language="javascript" src="js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.validate.min.js"></script>
<script type="text/javascript" language="javascript" src="js/messages_es.js"></script>

<script src="js/jquery-ui-1.9.2/ui/jquery-ui.js"></script>
<!--Seccion Date Picker-->
<link href="js/jquery-ui-1.9.2/themes/ui-lightness/jquery-ui.css" rel="stylesheet">
<script type="text/javascript" src="i18n/jquery.ui.datepicker-es.js"></script>
<script>
$(function() {
	$( "#fecha" ).datepicker({
		numberOfMonths: 2,
		altField: "#fecha2",
		altFormat: "yy-mm-dd",
		minDate: 0,
		maxDate: 30
	});
	$( "#fecha" ).datepicker( $.datepicker.regional[ "es" ] );
	$( "#fecha" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
<?php
	echo "\t$( '#fecha' ).datepicker( 'setDate', '$ear_liq_fec' );\r\n";
?>
	$( "#fecha" ).datepicker( "option", "dateFormat", "D, d M yy" );
});
</script>

<script>
$(document).ready(function()
{
	i=1;
	hosp_otros_id=<?php echo getIdHospOtros($mon_id); ?>;

	$(document).on('keyup keypress', 'form input[type="text"]', function(e) {
		if(e.which == 13) {
			e.preventDefault();
			return false;
		}
	});

	$( "#dialog-confirm" ).dialog({
		autoOpen: false,
		modal: true,
		dialogClass: "no-close",
		buttons: {
		},
		open: function() {
		}
	});

	$('#add').click(function(){
		var $tbl = $('#hosp_body tr:last');

		var lista = $('#hosp_ciud_template').html();
		var l_hosp_dias = $('#hosp_dias_template').html();
		var hosp_dele = '<img src="img/delete.png" class="dele" title="Borrar">';

//		var precio_html = '<span class="precio">0.00</span><span class="precio_i_span" style="display:none;"><input type="text" name="precio_i[{0}]" id="precio_i[{0}]" class="precio_i" maxlength="7" size="8"></span>';
                var precio_html = '<span class="precio_i_span"><input type="text" name="precio_i[{0}]" id="precio_i[{0}]" class="precio_i" maxlength="7" size="8"></span>';

		var s_add = '<tr class="dato_hosp"><td class="l_hosp_ciud">'+lista+'</td><td class="l_hosp_dias">'+l_hosp_dias+'</td><td class="precio_td">'+precio_html+'</td><td align="right" class="subtotal">0.00</td><td>'+hosp_dele+'</td></tr>';

		var template = jQuery.validator.format(s_add);

		$tbl.after($(template(i++)));

		var $tbla = $('#hosp_body > tr');
		$('#hosp_head').attr('rowspan', $tbla.size());

		$tbl = $('#hosp_body tr:last');
		$tbl.children('.l_hosp_ciud').children('.hosp_ciud').rules('add', {
			required: true,
			ciuddupli: true
		});
		$tbl.children('.l_hosp_dias').children('.hosp_dias').rules('add', {
			required: true,
			validanum: true,
			min: 0.1
		});
		$tbl.children('.l_hosp_ciud').children('.hosp_otro_span').children('.hosp_otro').rules('add', {
			required: true
		});
		$tbl.children('.precio_td').children('.precio_i_span').children('.precio_i').rules('add', {
			required: true,
			number: true,
			min: 0.01
		});
	});

	//For dynamic elements, you need to use event delegation using .on()
	$('#hosp_body').on('click', '.dele', function(){
		$(this).parent().parent().remove();

		var $tbla = $('#hosp_body > tr');
		$('#hosp_head').attr('rowspan', $tbla.size());

		$(this).calc_total();
	});

	$('#hosp_body').on('change', '.hosp_ciud', function(){
		if (hosp_otros_id != $('option:selected', this).val()) {
			$(this).parent().children('.hosp_otro_span').hide();
			$(this).rules('add', 'ciuddupli');
			$(this).parent().parent().children('.precio_td').children('.precio').hide();
			$(this).parent().parent().children('.precio_td').children('.precio_i_span').show();

			var precio = $('option:selected', this).attr('monto');
			$(this).parent().parent().children('.precio_td').children('.precio_i_span').children('.precio_i').val(precio);
		}
		else {
			$(this).parent().children('.hosp_otro_span').show();
			$(this).rules('remove', 'ciuddupli');
			$(this).parent().parent().children('.precio_td').children('.precio').hide();
			$(this).parent().parent().children('.precio_td').children('.precio_i_span').show();

			var precio = $(this).parent().parent().children('.precio_td').children('.precio_i_span').children('.precio_i').val();

			$(this).parent().children('.hosp_otro_span').children('.hosp_otro').focus();
		}

		var dias = $(this).parent().parent().children('.l_hosp_dias').children('.hosp_dias').val();
		var subtotal = dias*precio;
		$(this).parent().parent().children('.subtotal').html(subtotal.toFixed(2));

		$(this).calc_total();
	});

	$('#hosp_body').on('change', '.hosp_dias', function(){
//		if (hosp_otros_id != $(this).parent().parent().children('.l_hosp_ciud').children('.hosp_ciud').val()) {
//			var precio = $(this).parent().parent().children('.precio_td').children('.precio').text();
//		}
//		else {
			var precio = $(this).parent().parent().children('.precio_td').children('.precio_i_span').children('.precio_i').val();
//		}

		var dias = $(this).val();
		var subtotal = dias*precio;

		$(this).parent().parent().children('.subtotal').html(subtotal.toFixed(2));

		$(this).calc_total();
	});

	$('#hosp_body').on('change', '.precio_i', function(){
		var precio = $(this).val();
		var dias = $(this).parent().parent().parent().children('.l_hosp_dias').children('.hosp_dias').val();
		var subtotal = dias*precio;

		$(this).parent().parent().parent().children('.subtotal').html(subtotal.toFixed(2));

		$(this).calc_total();
	});

	$('#alim_dias').change(function() {
                hallarSubTotalAlimentacion();
	});

	$('#alim_montodia').change(function() {
                hallarSubTotalAlimentacion();
	});

        function hallarSubTotalAlimentacion(){
		var precio = $('#alim_montodia').val();
		var dias = $('#alim_dias').val();
		var subtotal = dias*precio;

		$('#alim_subtotal').text(subtotal.toFixed(2));

		$(this).calc_total();
        }

	$('#movi_dias').change(function() {
                hallarSubTotalMovilidad();
	});

	$('#movi_montodia').change(function() {
                hallarSubTotalMovilidad();
	});

        function hallarSubTotalMovilidad(){
		var precio = $('#movi_montodia').val();
		var dias = $('#movi_dias').val();
		var subtotal = dias*precio;

		$('#movi_subtotal').text(subtotal.toFixed(2));

		$(this).calc_total();
        }

	$('#bole_mont').change(function() {
		var floatVal = parseFloat($(this).val()).toFixed(2);

		if (!isNaN(floatVal)) {
			$('#bole_subtotal').text(floatVal);
		}
		else {
			$('#bole_subtotal').text('0.00');
		}

		$(this).calc_total();
	});

	$('#gast_mont').change(function() {
		var floatVal = parseFloat($(this).val()).toFixed(2);

		if (!isNaN(floatVal)) {
			$('#gast_subtotal').text(floatVal);
		}
		else {
			$('#gast_subtotal').text('0.00');
		}

		$(this).calc_total();
	});

	$('#otro_add').click(function(){
		var $tbl = $('#otro_body tr:last');

		var otro_input_1 = '<input type="text" class="otro_item" size="40" maxlength="150" id="otro_item[{0}]" name="otro_item[{0}]">';
		var otro_input_2 = '<input type="text" class="otro_mont" size="8" maxlength="7" id="otro_mont[{0}]" name="otro_mont[{0}]">';
		var otro_dele = '<img src="img/delete.png" class="otro_dele" title="Borrar">';

		var s_add = '<tr class="otro_dato"><td class="otro_item_list">'+otro_input_1+'</td><td></td><td class="otro_mont_list">'+otro_input_2+'</td><td align="right" class="subtotal">0.00</td><td>'+otro_dele+'</td></tr>';

		var template = jQuery.validator.format(s_add);

		$tbl.after($(template(i++)));

		var $tbla = $('#otro_body > tr');
		$('#otro_head').attr('rowspan', $tbla.size());

		$tbl = $('#otro_body tr:last');
		$tbl.children('.otro_item_list').children('.otro_item').rules('add', {
			required: true
		});
		$tbl.children('.otro_mont_list').children('.otro_mont').rules('add', {
			number: true,
			min: 0.01
		});

		$tbl.children('.otro_item_list').children('.otro_item').focus();
	});

	//For dynamic elements, you need to use event delegation using .on()
	$('#otro_body').on('click', '.otro_dele', function(){
		$(this).parent().parent().remove();

		var $tbla = $('#otro_body > tr');
		$('#otro_head').attr('rowspan', $tbla.size());

		$(this).calc_total();
	});

	$('#otro_body').on('change', '.otro_mont', function(){
		var floatVal = parseFloat($(this).val()).toFixed(2);

		if (!isNaN(floatVal)) {
			$(this).parent().parent().children('.subtotal').html(floatVal);
		}
		else {
			$(this).parent().parent().children('.subtotal').html('0.00');
		}

		$(this).calc_total();
	});

	$.fn.calc_total = function() {
		var suma = 0;
		$('#hosp_body tr.dato_hosp').each(function(){
			suma += parseFloat($(this).find('td').eq(3).text());
		});

		suma += parseFloat($('#alim_subtotal').text());
		suma += parseFloat($('#movi_subtotal').text());
		suma += parseFloat($('#bole_subtotal').text());
		suma += parseFloat($('#gast_subtotal').text());

		$('#otro_body tr.otro_dato').each(function(){
			suma += parseFloat($(this).find('td').eq(3).text());
		});

		$('#totalviaticos').val(suma.toFixed(2));
		$('#total').text('Total solicitado para transferencia: '+suma.toFixed(2)+' <?php echo $mon_nom; ?>');

		$('#totalviaticos').valid();
	};

	$( "#solicitud" ).validate({
		rules: {
			"totalviaticos": {
				number: true,
				min: 0.01
			},
			"alim_dias": {
				digits: true
			},
			"movi_dias": {
				digits: true
			},
			"bole_mont": {
				number: true,
				min: 0
			},
			"gast_mont": {
				number: true,
				min: 0
			},
			"motivo": {
				required: true
			},
			"fecha": {
				required: true
			},
			"cta_dolares": {
				required: true
			},
			"alim_montodia": {
				number: true,
				min: 0.01
			},
			"movi_montodia": {
				number: true,
				min: 0.01
			}
		},
		messages: {
			"totalviaticos": {
				min: "Total solicitado debe ser mayor que cero."
			}
		}
	});

	$.validator.addMethod("ciuddupli", function(value, element) {
		j = 0;

		$("#hosp_body .hosp_ciud").each(function(index, item) {
			if (element.name !== item.name && $(element).children(":selected").text() === $(item).children(":selected").text()) {
				j = 1;
			}
		});

		if (j == 0) {
			return true;
		}
		else {
			return false;
		}
	}, "Existen items duplicados.");

	$.validator.addMethod("validanum", function(value, element) {
		// Valida que el valor sea un numero con maximo un digito decimal
		return this.optional(element) || /^((\d|[1-9]\d+)(\.\d{1})?|\.\d{1})$/.test(value);
	}, "El numero debe tener maximo un digito decimal.");

	$(document).ready(function() {
		// executes when HTML-Document is loaded and DOM is ready

		// dinamically auto add previous rows
<?php
$i = 0;
foreach ($arrDet as $v) {
	if (substr($v[0], 0, 2)=='03') {
		if ($hosp_otros_id != $v[5]) {
			$i++;
			echo "\t\t$('#add').click();\n";
			echo "\t\t$('#hosp_ciud\\\\[$i\\\\]').val('$v[5]');\n";
			echo "\t\t$('#hosp_dias\\\\[$i\\\\]').val('$v[3]');\n";
			echo "\t\t$('#hosp_dias\\\\[$i\\\\]').parent().parent().children('.precio_td').children('.precio').html('$v[4]');\n";
			$subtotal = number_format($v[3]*$v[4], 2, '.', '');
			echo "\t\t$('#hosp_dias\\\\[$i\\\\]').parent().parent().children('.subtotal').html('$subtotal');\n";
			echo "\t\t$('#precio_i\\\\[$i\\\\]').parent().parent().children('.precio').hide();\n";
			echo "\t\t$('#precio_i\\\\[$i\\\\]').parent().show();\n";
			echo "\t\t$('#precio_i\\\\[$i\\\\]').val('$v[4]');\n";
		}
		else {
			$i++;
			echo "\t\t$('#add').click();\n";
			echo "\t\t$('#hosp_ciud\\\\[$i\\\\]').val('$v[5]');\n";
			echo "\t\t$('#hosp_ciud\\\\[$i\\\\]').rules('remove', 'ciuddupli');\n";
			echo "\t\t$('#hosp_dias\\\\[$i\\\\]').val('$v[3]');\n";
			echo "\t\t$('#hosp_dias\\\\[$i\\\\]').parent().parent().children('.precio_td').children('.precio').html('$v[4]');\n";
			$subtotal = number_format($v[3]*$v[4], 2, '.', '');
			echo "\t\t$('#hosp_dias\\\\[$i\\\\]').parent().parent().children('.subtotal').html('$subtotal');\n";
			echo "\t\t$('#hosp_otro_span\\\\[$i\\\\]').show();\n";
			echo "\t\t$('#hosp_otro\\\\[$i\\\\]').val('$v[2]');\n";
			echo "\t\t$('#precio_i\\\\[$i\\\\]').parent().parent().children('.precio').hide();\n";
			echo "\t\t$('#precio_i\\\\[$i\\\\]').parent().show();\n";
			echo "\t\t$('#precio_i\\\\[$i\\\\]').val('$v[4]');\n";
		}
	}
	else if (substr($v[0], 0, 2)=='06') {
		$i++;
		echo "\t\t$('#otro_add').click();\n";
		echo "\t\t$('#otro_item\\\\[$i\\\\]').val('$v[2]');\n";
		echo "\t\t$('#otro_mont\\\\[$i\\\\]').val('$v[4]');\n";
		echo "\t\t$('#otro_mont\\\\[$i\\\\]').parent().parent().children('.subtotal').html('$v[4]');\n";
	}
}
?>
		// auto calc

		$(this).calc_total();
	});

	// jQuery plugin to prevent double submission of forms
	jQuery.fn.preventDoubleSubmission = function() {
		$(this).on('submit',function(e){
			var $form = $(this);

			if ($form.data('submitted') === true) {
				// Previously submitted - don't submit again
				e.preventDefault();
			} else {
				// Mark it so that the next submit can be ignored
				if($form.valid()) {
					$form.data('submitted', true);
					$("#dialog-confirm").dialog( "open" );
				}
			}
		});

		// Keep chainability
		return this;
	};

	$('form').preventDoubleSubmission();

});
</script>

<style>
#hosp_ciud_template {
	display:none;
}
#hosp_dias_template {
	display:none;
}

input.valid {
    background: url(img/icon-ok.png) no-repeat right center #e3ffe5;
    color: #002f00;
    border-color: #96b796 !important;
}

input.error {
    background: url(img/icon-fail.png) no-repeat right center #ffebef;
    color: #480000;
}

form.xform label.error, label.error {
	/* remove the next line when you have trouble in IE6 with labels in list */
	color: red;
	font-style: italic
}

.encabezado_v {
	vertical-align: top;
}

.encabezado_h {
	background-color: silver;
}

.iconos {
	vertical-align:text-top;
}

.no-close .ui-dialog-titlebar-close {
display: none;
}
</style>
</head>
<body onload="document.getElementById('enviaform').disabled=false;">
<?php include ("header.php"); ?>

    <h1>Editar solicitud <?php echo strtolower(getNomZona($zona_id)); ?></h1>

<form id="solicitud" action="ear_editar_p.php" method="post">

<table>
    <tr><td>N&uacute;mero Solicitud</td><td><?php echo $ear_numero; ?></td></tr>
<tr><td>Fecha Solicitud</td><td><?php echo $ear_sol_fec; ?></td></tr>
<tr><td>Nombre</td><td><?php echo $ear_tra_nombres.(is_null($master_usu_id)?"":" (Registrado por ".getUsuarioNombre($master_usu_id).")"); ?></td></tr>
<tr><td>DNI</td><td><?php echo $ear_tra_dni; ?></td></tr>
<!--<tr><td>Cargo</td><td><?php // echo $ear_tra_cargo; ?></td></tr>
<tr><td>Area</td><td><?php // echo $ear_tra_area; ?></td></tr>
<tr><td>Sucursal</td><td><?php // echo $ear_tra_sucursal; ?></td></tr>-->
<tr><td>Fecha de dep&oacute;sito</td><td><input type="text" id="fecha" name="fecha" readonly /><input type="hidden" id="fecha2" name="fecha2" /></td></tr>
<tr><td>Moneda</td><td><?php echo "$mon_nom ($mon_simb) ($mon_iso) <img src='$mon_img' style='vertical-align:text-top'>"; ?></td></tr>
<!--<tr>
	<td>Numero de cuenta<br>para la transferencia</td>
	<td><?php // echo "<input type='text' name='cta_dolares' size='25' maxlength='32' value='$ear_tra_cta'> Cuenta en $mon_nom";?></td>
</tr>-->
</table>

<p>Detalle de los viaticos:</p>
<table border="1">
<tr>
	<td rowspan="2">Boletos de Viaje</td>
	<td class="encabezado_h"></td>
	<td class="encabezado_h"></td>
	<td class="encabezado_h">Monto</td>
	<td class="encabezado_h">Subtotal</td>
	<td class="encabezado_h"></td>
</tr>
<tr>
<?php
$bole_mont = "0.00";
foreach ($arrDet as $v) {
	if (substr($v[0], 0, 2)=='01') {
		$bole_mont = number_format($v[4], 2, '.', '');
		break;
	}
}
?>
	<td></td>
	<td></td>
	<td><input type="text" size="8" maxlength="7" id="bole_mont" name="bole_mont" value="<?php echo floatval($bole_mont); ?>" /></td>
	<td align="right"><span id="bole_subtotal"><?php echo $bole_mont; ?></span></td>
	<td></td>
</tr>

<tr>
	<td rowspan="2">Alimentacion / Pension</td>
	<td class="encabezado_h"></td>
	<td class="encabezado_h">Dias</td>
	<td class="encabezado_h">Monto diario</td>
	<td class="encabezado_h">Subtotal</td>
	<td class="encabezado_h"></td>
</tr>
<tr>
<?php
$alim_mont = "0";
$getViaticosMonto = getViaticosMonto('02', $mon_id, $zona_id);
$subtotal = "0.00";
foreach ($arrDet as $v) {
	if (substr($v[0], 0, 2)=='02') {
		$alim_mont = $v[3];
                $getViaticosMonto=$v[4];
		$subtotal = number_format($v[3]*$getViaticosMonto, 2, '.', '');
		break;
	}
}
?>
	<td></td>
	<td><input type="text" size="7" maxlength="4" id="alim_dias" name="alim_dias" value="<?php echo $alim_mont; ?>" /></td>
        <td><input type="text" size="8" maxlength="7" id="alim_montodia" name="alim_montodia" value="<?php echo $getViaticosMonto; ?>" /></td>
	<td align="right"><span id="alim_subtotal"><?php echo $subtotal; ?></span></td>
	<td></td>
</tr>

<tbody id="hosp_body">
<tr>
	<td id="hosp_head" rowspan="1" class="encabezado_v">Hospedaje <img src="img/plus.png" id="add" title="Agregar"></td>
	<td class="encabezado_h">Ciudad</td>
	<td class="encabezado_h">Dias</td>
	<td class="encabezado_h">Monto diario</td>
	<td class="encabezado_h">Subtotal</td>
	<td class="encabezado_h">Borrar</td>
</tr>
</tbody>

<tr>
	<td rowspan="2">Movilidad / Combustible</td>
	<td class="encabezado_h"></td>
	<td class="encabezado_h">Dias</td>
	<td class="encabezado_h">Monto diario</td>
	<td class="encabezado_h">Subtotal</td>
	<td class="encabezado_h"></td>
</tr>
<tr>
<?php
$movi_mont = "0";
$getViaticosMonto = getViaticosMonto('04', $mon_id, $zona_id);
$subtotal = "0.00";
foreach ($arrDet as $v) {
	if (substr($v[0], 0, 2)=='04') {
		$movi_mont = $v[3];
                $getViaticosMonto=$v[4];
		$subtotal = number_format($v[3]*$getViaticosMonto, 2, '.', '');
		break;
	}
}
?>
	<td></td>
	<td><input type="text" size="7" maxlength="4" id="movi_dias" name="movi_dias" value="<?php echo $movi_mont; ?>" /></td>
        <td><input type="text" size="8" maxlength="7" id="movi_montodia" name="movi_montodia" value="<?php echo $getViaticosMonto; ?>"/></td>
	<td align="right"><span id="movi_subtotal"><?php echo $subtotal; ?></span></td>
	</td>
	<td></td>
</tr>

<tr style="display: none;">
    <td rowspan="2">Gastos de Representaci&oacute;n</td>
	<td class="encabezado_h"></td>
	<td class="encabezado_h"></td>
	<td class="encabezado_h">Monto</td>
	<td class="encabezado_h">Subtotal</td>
	<td class="encabezado_h"></td>
</tr>
<tr style="display: none;">
<?php
$gast_mont = "0.00";
foreach ($arrDet as $v) {
	if (substr($v[0], 0, 2)=='05') {
		$gast_mont = $v[4];
		break;
	}
}
?>
	<td></td>
	<td></td>
	<td><input type="text" size="8" maxlength="7" id="gast_mont" name="gast_mont" value="<?php echo floatval($gast_mont); ?>" /></td>
	<td align="right"><span id="gast_subtotal"><?php echo $gast_mont; ?></span></td>
	<td></td>
</tr>

<tbody id="otro_body">
<tr>
	<td id="otro_head" rowspan="1" class="encabezado_v">Otros <img src="img/plus.png" id="otro_add" title="Agregar"></td>
        <td class="encabezado_h">Item / Descripci&oacute;n</td>
	<td class="encabezado_h"></td>
	<td class="encabezado_h">Monto</td>
	<td class="encabezado_h">Subtotal</td>
	<td class="encabezado_h">Borrar</td>
</tr>
</tbody>

<tr><td colspan="6">&nbsp;</td></tr>
<tr><td><b>Total viaticos</b></td><td colspan="5"><input type="text" readonly value="0.00" id="totalviaticos" name="totalviaticos" size="12" /></td></tr>
</table>

<br>

<div id="total">Total solicitado para transferencia: 0.00 <?php echo $mon_nom; ?></div>

<br>
<?php
$bandera_motivo = false;
if ($usu_id != $_SESSION['rec_usu_persona_id']) {
	if($usuario_reg_id != $_SESSION['rec_usu_persona_id']){
		if($count == 0){
			$bandera_motivo = true;
		}
	}
}
?>
    Motivo de la solicitud: (m&aacute;ximo 300 caracteres, no puede estar en blanco)<br>
<textarea name="motivo" cols="80" rows="6" maxlength="300"<?php echo ($bandera_motivo?" readonly":"");?>><?php echo $ear_sol_motivo; ?></textarea>

<br>

<?php
// Mostrar este textarea solo si es el aprobador o admin o TI
list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
$arrTI=obtenerUsuariosIdXPerfil($pTI);
$arrAdmin=obtenerUsuariosIdXPerfil($pADMINIST);
$valid_users = array($usu_id, $usu_id_jefe, $usu_id_gerente);
$valid_users = array_merge($valid_users, $arrTI);
$valid_users = array_merge($valid_users, $arrAdmin);

if(in_array($_SESSION['rec_usu_id'], $valid_users)) {
?>
<br>
    Si realiz&oacute; una modificaci&oacute;n de datos de la solicitud, ingrese sus observaciones: (m&aacute;ximo 300 caracteres)<br>
<textarea name="obs" cols="80" rows="6" maxlength="300"><?php echo $ear_act_obs1; ?></textarea>

<br>
<?php
}
?>

<input type="hidden" name="zona_id" value="<?php echo $zona_id; ?>">
<input type="hidden" name="mon_id" value="<?php echo $mon_id; ?>">
<input type="hidden" value="<?php echo $id; ?>" name="id">
<input type="submit" value="Actualizar solicitud" disabled id="enviaform">

</form>

<div id="hosp_ciud_template">
<select class="hosp_ciud" id="hosp_ciud[{0}]" name="hosp_ciud[{0}]">
<option disabled selected="selected">Seleccione...</option>
<?php
$arr = getViaHospedajes($mon_id, $zona_id);

foreach ($arr as $v) {
	echo "<option value='$v[0]' monto='$v[1]'>$v[2]</option>\n";
}
?>
</select>
<span class="hosp_otro_span" id="hosp_otro_span[{0}]" style="display:none;"><br><input type="text" class="hosp_otro" size="40" maxlength="200" id="hosp_otro[{0}]" name="hosp_otro[{0}]"><br></span>
</div>

<div id="hosp_dias_template">
<input type="text" class="hosp_dias" size="7" maxlength="4" id="hosp_dias[{0}]" name="hosp_dias[{0}]">
</div>

<div id="dialog-confirm" title="Espere que complete el proceso" style="display:none; text-align:center;">
<p>Por favor espere hasta que se complete la transaccion, procesando...<br><br><img src="img/circle-loader.gif" title="Procesando..." class="iconos"></p>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
