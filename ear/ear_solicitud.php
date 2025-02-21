<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

//if (getHabilitadoRegistrarEAR($_SESSION['rec_usu_id']) == 0) {
//	echo "<font color='red'><b>ERROR: No puede registrar m&aacute;s solicitudes, se ha excedido el m&aacute;ximo de EAR desembolsados sin liquidar.</b></font><br>";
//	exit;
//}

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_zona_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$zona_id = filter_var($f_zona_id, FILTER_SANITIZE_STRING);
}
if (isset($f_terc_id)) $terc_id = abs((int) filter_var($f_terc_id, FILTER_SANITIZE_NUMBER_INT)); else $terc_id=2;
if (isset($f_mon_id)) $mon_id = abs((int) filter_var($f_mon_id, FILTER_SANITIZE_NUMBER_INT)); else $mon_id=2;
if ($f_zona_id!="01") $mon_id=2;

list($mon_nom, $mon_iso, $mon_simb, $mon_img) = getNomMoneda($mon_id);
if ($mon_nom=="Sin definir") {
	echo "<font color='red'><b>ERROR: Moneda inv&aacute;lida / sin definir</b></font><br>";
	exit;
}

list($dni, $nombres,$apellido_paterno,$apellido_materno, $cargo_id, $fecha_ing,
	$cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador($_SESSION['rec_usu_persona_id']);//antes rec_codigogeneral_id
//if(1==1){};
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!--<title>Registrar Solicitud EAR - Administraci&oacute;n - Minapp</title>-->
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
	.titulo {font-size: 14pt; font-family: arial,helvetica}
</style>
 <link href="js/select2/select2.css" rel="stylesheet"/>

<script type="text/javascript" language="javascript" src="js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.validate.min.js"></script>
<script type="text/javascript" language="javascript" src="js/messages_es.js"></script>

<script src="js/jquery-ui-1.9.2/ui/jquery-ui.js"></script>
<!--Seccion Date Picker-->
<link href="js/jquery-ui-1.9.2/themes/ui-lightness/jquery-ui.css" rel="stylesheet">
<script type="text/javascript" src="i18n/jquery.ui.datepicker-es.js"></script>
<script type="text/javascript" src="js/select2/select2.min.js"></script>
<script>
//    function obtenerIdOrdenTrabajo(){
//        var value = $('#txtOrdenTrabajo').val();
//        var id =$('#listOrdenTrabajo [value="' + value + '"]').data('value');
//        $('#idOrdenTrabajo').val(id);
////        console.log($('#idOrdenTrabajo').val());
//    }

    $(function() {
    	$( "#fecha" ).datepicker({
    		numberOfMonths: 2,
    		altField: "#fecha2",
    		altFormat: "yy-mm-dd",
    		minDate: 0,
    		maxDate: 30
    	});

        $( "#fecha_liquidacion" ).datepicker({
    		numberOfMonths: 2,
    		altField: "#fecha3",
    		altFormat: "yy-mm-dd",
    		minDate: 0,
    		maxDate: 60
    	});
    	$( "#fecha" ).datepicker( $.datepicker.regional[ "es" ] );
    	$( "#fecha" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
    	$( "#fecha" ).datepicker( "option", "dateFormat", "D, d M yy" );

        $( "#fecha_liquidacion" ).datepicker( $.datepicker.regional[ "es" ] );
    	$( "#fecha_liquidacion" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
    	$( "#fecha_liquidacion" ).datepicker( "option", "dateFormat", "D, d M yy" );


        $("#txtEmpleado").select2({
                width: "100%",
                placeholder: "Seleccione una empleado",
               // minimumResultsForSearch: Infinity, //removes the search box
                formatResult: formatDesign,
                formatSelection: formatDesign
         });

         function formatDesign(item) {
            var selectionText = item.text.split(".");
            if(selectionText.length > 1){
                 var $returnString = "<b>"+selectionText[0] + "</b><br/>" + selectionText[1];
            }else{
                var $returnString = selectionText[0];
            }

            return $returnString;
         }

        });
</script>

<script>
$(document).ready(function()
{
	i=1;
	hosp_otros_id=<?php echo getIdHospOtros($mon_id); ?>;

	if (<?php echo $mon_id; ?>==1) {
		$('#cta_inp').hide();
		$('#cta_back').hide();
	}
	else {
		$('#cta_span').hide();
		$('#cta_edit').hide();
		$('#cta_back').hide();
	}

	$('#cta_edit').click(function(){
		$('#cta_span').hide();
		$('#cta_inp').show();
		$('#cta_edit').hide();
		$('#cta_back').show();

	});

	$('#cta_back').click(function(){
		$('#cta_span').show();
		$('#cta_inp').hide();
		$('#cta_inp').val('');
		$('#cta_edit').show();
		$('#cta_back').hide();

	});

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
				required: true,
 			},
                        "fecha_liquidacion": {
				required: true,
                                fechaValidacion:true
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


        $.validator.addMethod("fechaValidacion", function(value, element) {
                let fechaDeposito = $("#fecha2").val();
                let fechaLiquidacion = $("#fecha3").val();
                fechaDeposito = new Date(fechaDeposito);
                fechaLiquidacion = new Date(fechaLiquidacion);

                if(fechaDeposito.getTime() > fechaLiquidacion.getTime()){
                    return false;
                }else{
                    return true;
                }
 	}, "La fecha de liquidaci&oacute;n debe ser mayor que la fecha de dep&oacute;sito.");

	$.validator.addMethod("validanum", function(value, element) {
		// Valida que el valor sea un numero con maximo un digito decimal
		return this.optional(element) || /^((\d|[1-9]\d+)(\.\d{1})?|\.\d{1})$/.test(value);
	}, "El numero debe tener maximo un digito decimal.");

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

.iconos_click {
	vertical-align:text-top;
	cursor: pointer;
}

.no-close .ui-dialog-titlebar-close {
display: none;
}
</style>
</head>
<body onload="document.getElementById('enviaform').disabled=false;">
<?php
    $nombreMoneda=($mon_id==1? 'Soles':'D&oacute;lares');
?>
<?php
	if(isset($terc_id) && $terc_id == 1){
?>
<h1>Registro de solicitud Terceros en <?php echo $nombreMoneda ?></h1>
<?php
	}else{
?>
	<h1>Registro de solicitud en <?php echo $nombreMoneda ?></h1>
<?php
	}
?>
<form id="solicitud" action="ear_solicitud_p.php" method="post">

<table>
<?php
	if(isset($terc_id) && $terc_id == 2){
?>
<tr><td>Nombre</td><td><?php echo $nombres.' '.$apellido_paterno.' '.$apellido_materno; ?></td></tr>
<tr><td>DNI</td><td><?php echo $dni; ?></td></tr>
<?php
	}
?>
<!--<tr><td>Cargo</td><td><?php // echo $cargo_desc; ?></td></tr>
<tr><td>Area</td><td><?php // echo $area_desc; ?></td></tr>
<tr><td>Sucursal</td><td><?php // echo $sucursal; ?></td></tr>-->
<tr><td>Fecha de dep&oacute;sito</td><td><input type="text" id="fecha" name="fecha" readonly /><input type="hidden" id="fecha2" name="fecha2" /></td></tr>
<tr><td>Fecha de liquidaci&oacute;n</td><td><input type="text" id="fecha_liquidacion" name="fecha_liquidacion" readonly /><input type="hidden" id="fecha3" name="fecha3" /></td></tr>
<tr><td>Moneda</td><td><?php echo "$mon_nom ($mon_simb) ($mon_iso) <img src='$mon_img' style='vertical-align:text-top'>"; ?></td></tr>
<?php
	if(isset($terc_id) && $terc_id == 1){
?>
<tr>
    <td>Empleado solicitante</td>
    <td>
        <!--<input type="text" id="txtOrdenTrabajo"  list="listOrdenTrabajo" onchange="obtenerIdOrdenTrabajo();" autocomplete="off">-->
        <select id="txtEmpleado" class="select2"  onchange="$('#idEmpleado').val(this.value);" required >
            <?php
            include 'datos_abrir_bd.php';
            $query="call ".$baseSGI.".sp_persona_obtener_XPersonaClaseId(-2)";
            $result = $mysqli->query($query) or die ($mysqli->error);

            $listOrdenTrabajo='';
            $listOrdenTrabajo .= '<option value="">Seleccione un Empleado</option>';

            while($fila=$result->fetch_array()){
//                 $('#' + id).append('<option value="' + item[val] + '">' + sText + '</option>');

                $listOrdenTrabajo .= '<option value="' . $fila['id'] . '">' . $fila['persona_nombre'] . ' | ' . $fila['codigo_identificacion'] . '</option>';
}
            echo $listOrdenTrabajo;
            include 'datos_cerrar_bd.php';
        ?>

        </select>
        <input type="hidden" id="idEmpleado" name="idEmpleado" value="0" />
        <input type="hidden" id="constante_empleado" name="constante_empleado" value="1" />
        <!--<datalist id="listOrdenTrabajo">-->

        <!--</datalist>-->
    </td>
</tr>
<?php
	}
?>
<tr>
<tr>
<!--	<td>Numero de cuenta<br>para la transferencia</td>
	<td>
		<span id="cta_span"><?php // echo $ctacte; ?></span>
		<input id="cta_inp" type='text' name='cta_dolares' size='25' maxlength='32'>
		<?php // echo "Cuenta en $mon_nom\n"; ?>
		<img id="cta_edit" src="img/edit.png" title="Editar numero de cuenta" class="iconos_click">
		<img id="cta_back" src="img/back.png" title="Volver a numero de cuenta predefinida" class="iconos_click">
	</td>-->
</tr>
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
	<td></td>
	<td></td>
	<td><input type="text" size="8" maxlength="7" id="bole_mont" name="bole_mont" /></td>
	<td align="right"><span id="bole_subtotal">0.00</span></td>
	<td></td>
</tr>

<tr>
	<td rowspan="2">Alimentacion / Pension</td>
	<td class="encabezado_h"></td>
	<td class="encabezado_h">D&iacute;as</td>
	<td class="encabezado_h">Monto diario</td>
	<td class="encabezado_h">Subtotal</td>
	<td class="encabezado_h"></td>
</tr>
<tr>
	<td></td>
	<td><input type="text" size="7" maxlength="4" id="alim_dias" name="alim_dias" /></td>
        <td><input type="text" size="8" maxlength="7" id="alim_montodia" name="alim_montodia" value="<?php echo getViaticosMonto('02', $mon_id, $zona_id); ?>" /></td>
	<td align="right"><span id="alim_subtotal">0.00</span></td>
	<td></td>
</tr>

<tbody id="hosp_body">
<tr>
	<td id="hosp_head" rowspan="1" class="encabezado_v">Hospedaje <img src="img/plus.png" id="add" title="Agregar"></td>
	<td class="encabezado_h">Ciudad</td>
	<td class="encabezado_h">D&iacute;as</td>
	<td class="encabezado_h">Monto diario</td>
	<td class="encabezado_h">Subtotal</td>
	<td class="encabezado_h">Borrar</td>
</tr>
</tbody>

<tr>
	<td rowspan="2">Movilidad / Combustible</td>
	<td class="encabezado_h"></td>
	<td class="encabezado_h">D&iacute;as</td>
	<td class="encabezado_h">Monto diario</td>
	<td class="encabezado_h">Subtotal</td>
	<td class="encabezado_h"></td>
</tr>
<tr>
	<td></td>
	<td><input type="text" size="7" maxlength="4" id="movi_dias" name="movi_dias" /></td>
	<td><input type="text" size="8" maxlength="7" id="movi_montodia" name="movi_montodia" value="<?php echo getViaticosMonto('04', $mon_id, $zona_id); ?>"/></td>
	<td align="right"><span id="movi_subtotal">0.00</span></td>
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
	<td></td>
	<td></td>
	<td><input type="text" size="8" maxlength="7" id="gast_mont" name="gast_mont" /></td>
	<td align="right"><span id="gast_subtotal">0.00</span></td>
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
<tr><td><b>Total vi&aacute;ticos</b></td><td colspan="5"><input type="text" readonly value="0.00" id="totalviaticos" name="totalviaticos" size="12" /></td></tr>
</table>

<br>

<div id="total">Total solicitado para transferencia: 0.00 <?php echo $mon_nom; ?></div>

<br>

Motivo de la solicitud: (m&aacute;ximo 300 caracteres, no puede estar en blanco)<br>
<textarea name="motivo" cols="80" rows="6" maxlength="300"></textarea>

<br>

<input type="hidden" name="zona_id" value="<?php echo $zona_id; ?>">
<input type="hidden" name="mon_id" value="<?php echo $mon_id; ?>">
<input type="submit" value="Registrar solicitud" disabled id="enviaform">
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
