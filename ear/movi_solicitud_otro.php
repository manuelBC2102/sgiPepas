<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

$arrayUsuario = obtenerUsuariosSGI();

$usuarioIdInicial=$arrayUsuario[0]['usuario_id'];
$personaIdInicial=$arrayUsuario[0]['persona_id'];

if (isset($_GET["usuId"])) {
    $usuarioIdInicial=$_GET["usuId"];
    $personaIdInicial=  obtenerPersonaIdSGI($usuarioIdInicial);
}

$arr = getLiqDesembolsadas($usuarioIdInicial);

list($dni, $nombres, $apePaterno,$apeMaterno,$cargo_id, $fecha_ing,
	$cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador($personaIdInicial);

$tope_maximo = getValorSistema(1);
$mon_id = 1;
list($mon_nom, $mon_iso, $mon_simb, $mon_img) = getNomMoneda($mon_id);

$usu_id = $usuarioIdInicial;
$id = -1;
$rec_usu_nombre = getUsuarioNombre($usu_id);
$adm_usu_gco_cobj = getUsuGcoObj($usu_id);
list($lid_gti_def, $lid_dg_json_def) = getDistGastDefault($id, $rec_usu_nombre, $adm_usu_gco_cobj);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=1312" />
<!--<title>Registrar Planilla de Movilidad - Administraci�n - Minapp</title>-->
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
$(function() {
	$( "#fecha" ).datepicker({
		numberOfMonths: 2,
		altField: "#fecha2",
		altFormat: "yy-mm-dd",
		minDate: 1,
		maxDate: 30
	});
	$( "#fecha" ).datepicker( $.datepicker.regional[ "es" ] );
	$( "#fecha" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
	$( "#fecha" ).datepicker( "option", "dateFormat", "D, d M yy" );
});
</script>

<script>
$(document).ready(function()
{
	i=1;
	flag=0;

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

	$('#table1').on('focus',".fecdoc_inp", function(){
		$(this).datepicker({
			numberOfMonths: 2,
			maxDate: 0
		});
	});

	$('#table1').on('change',".fecdoc_inp", function(){
		$(document).checktopes_redraw();
	});

	$('#table1').on('change', '.monto_inp', function(){
		$(document).checktopes_redraw();
	});

	$.fn.checktopes_redraw = function() {
		var fecha = "";
		var monto = 0.00;
		var tope = <?php echo $tope_maximo; ?>;
		var fila;
		var html = "";
		var arr_fecha = new Array();
		var total = 0.00;
		$('#exc').val(0);
		$('#advertencia').hide();
		$('#act1').prop('disabled', false);

		// Primera barrida, suma los montos de acuerdo a la fecha, y muestra los mensajes de estado de acuerdo al monto ingresado
		$('.fecdoc_inp').each(function(){
			fecha = $(this).val();
			fila = $(this).parent().parent();
			monto = parseFloat(fila.children('.monto_td').children('.monto_inp').val()) || 0;
			if(fecha.length == 10 && monto > 0) {
				if(typeof arr_fecha[fecha] == 'undefined') {
					arr_fecha[fecha] = monto;
				}
				else {
					arr_fecha[fecha] += monto;
				}
				total += monto;
			}

			// No importa si el monto se pasa del tope porque eso se verifica en la siguiente barrida
			if (monto <= 0) {
				html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Inv&aacute;lido</div>';
			}
			else if (fecha.length != 10) {
				html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Error de fecha</div>';
			}
			else {
				html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Aceptado</div>';
			}
			fila.children('.estado_td').children('.estado_div').html(html);
		});

		// Segunda barrida, pinta de colores los recuadros donde corresponda cuando la suma de la fecha exceda el tope
		$('.fecdoc_inp').each(function(){
			fecha = $(this).val();
			fila = $(this).parent().parent();
			if(arr_fecha[fecha] > tope) {
				fila.children('.estado_td').children('.estado_div').html('<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>');
				$('#exc').val(1);
				$('#advertencia').show();

				flag=1;
//				$('#act1').prop('disabled', true);
			}
		});

		// Coloca valor total en el cuadro resumen
		$('#tot_mon_inp').val(total.toFixed(2));
		$('#tot_mon_s').html(total.toFixed(2));

	};

	$('#table1').on('click', '.dele', function(){
		var fila = $(this).parent().parent();
		fila.remove();
		$(document).checktopes_redraw();
	});

	$(document).ready(function() {
		// executes when HTML-Document is loaded and DOM is ready

		// auto add one new row
		$('#fila1_add').click();
	});

	$('#fila1_add').click(function(){
		var $tbl = $('#table1_body1 tr:last');
		var dele = '<img src="img/delete.png" class="dele" title="Borrar">';

		var s_add = '<tr class="fila_dato">';
		s_add += '<td class="motivo_td"><input type="text" value="" size="42" maxlength="100" class="motivo_inp" name="motivo_inp[{0}]"></td>';
		s_add += '<td class="fecdoc_td"><input type="text" value="" size="11" maxlength="10" class="fecdoc_inp" readonly name="fecdoc_inp[{0}]"></td>'; // Fecha
		s_add += '<td class="salida_td"><input type="text" value="" size="28" maxlength="100" class="salida_inp" name="salida_inp[{0}]"></td>';
		s_add += '<td class="destino_td"><input type="text" value="" size="28" maxlength="100" class="destino_inp" name="destino_inp[{0}]"></td>';
		s_add += '<td class="monto_td"><input type="text" value="" size="8" maxlength="9" id="monto_inp[{0}]" class="monto_inp" name="monto_inp[{0}]"></td>'; // Monto
		s_add += '<td class="estado_td"><div class="estado_div"></div></td>';
		s_add += '<td>'+dele+'</td>';
		s_add += '</tr>';

		var template = jQuery.validator.format(s_add);

		$tbl.after($(template(i++)));

		$tbl = $('#table1_body1 tr:last');
		$tbl.children('.monto_td').children('.monto_inp').rules('add', {
			min: 0.01
		});
		$tbl.children('.motivo_td').children('.motivo_inp').rules('add', {
			required: true
		});
		$tbl.children('.fecdoc_td').children('.fecdoc_inp').rules('add', {
			required: true
		});
		$tbl.children('.salida_td').children('.salida_inp').rules('add', {
			required: true
		});
		$tbl.children('.destino_td').children('.destino_inp').rules('add', {
			required: true
		});

		$tbl.children('.motivo_td').children('.motivo_inp').focus();
	});

	$( "#form1" ).validate({
	});

	$('#act1').click(function( event ){
                if ($('#tipo1_sel').val() == null) {
			alert('Seleccione un EAR que previamente fue desembolsado por tesoreria.');
			event.preventDefault();
		}

		$(document).check_total(event);
	});

	$.fn.check_total = function( event ) {
		var total = parseFloat($('#tot_mon_inp').val());

		if (total == 0) {
			alert('ERROR: El monto total no puede ser cero. No se puede continuar.');
			event.preventDefault();
		}

		if (flag == 1 && $('#comentario1').val().length == 0) {
			alert('ERROR: Se detecto un exceso. Ingrese un comentario obligatorio de porque ocurrio.');
			event.preventDefault();
		}
	};

	$('#abrir_ear').click(function(){
		var ear_id = parseInt($('#tipo1_sel').val());
		window.open('ear_consulta_detalle.php?id='+ear_id+'&close=1');
	});

	$( "#dist_gastos" ).dialog({
		autoOpen: false,
		height: 600,
		width: 800,
		modal: true,
		buttons: {
			"Guardar": function() {
				var cad ="";
				var gast_info_tooltip = '';
				var nome = "";
				var cobj = "";
				var porc = "";
				var dist_gast_arr = [];
				$('#distribucion tr:gt(0)').each(function() {
					nome = $(this).children('.nome').html();
					cobj = $(this).children('.td_porc').children('.porc').attr('gco_cobj');
					porc = $(this).children('.td_porc').children('.porc').val();
					porc = parseFloat(porc);
					porc = porc.toFixed(2);
					cad += nome;
					gast_info_tooltip += nome+' ('+porc+'%)\n';
					dist_gast_arr.push( [ nome , cobj , porc ] );
				});

				var sum = 0;
				$('#distribucion input.porc').each(function(){
					sum += parseFloat($(this).val());
				});
				if (sum != 100) {
					alert('ERROR: La suma de los porcentajes no es igual a 100, no se puede continuar.');
					return false;
				}

				gast_info_tooltip = gast_info_tooltip.slice(0, -1); //Quita el ultimo 'Enter' de la cadena
				var json_str = JSON.stringify(dist_gast_arr);

				var gast_tipo = parseInt($('#dist_gastos #tg').val());
				var gast_img = '';
				var gast_img_tooltip = '';
				switch(gast_tipo) {
					case 1:
						gast_img = 'img/persona.png';
						gast_img_tooltip = 'Personas';
						break;
					case 2:
						gast_img = 'img/centro-costo.png';
						gast_img_tooltip = 'Centro de Costos';
						break;
					case 3:
						gast_img = 'img/wbs.png';
						gast_img_tooltip = 'Proyectos WBS';
						break;
					case 4:
						gast_img = 'img/internal-order.png';
						gast_img_tooltip = 'Internal Order';
						break;
					default:
						gast_img = 'img/error.png';
						gast_img_tooltip = 'ERROR';
				}
				$( "#dist_gastos" ).data('dist_gast_node').children('.dist_gast_tipo').attr('src', gast_img);
				$( "#dist_gastos" ).data('dist_gast_node').children('.dist_gast_tipo').attr('title', gast_img_tooltip);

				$( "#dist_gastos" ).data('dist_gast_node').children('.dist_gast_info').attr('title', gast_info_tooltip);

				$( "#dist_gastos" ).data('dist_gast_node').children('.gti_id_i').attr('value', gast_tipo);
				$( "#dist_gastos" ).data('dist_gast_node').children('.dist_gast_json_i').attr('value', json_str);

				// Cambia la distribucion por defecto del formulario
				$('#lid_gti_def').attr('value', gast_tipo);
				$('#lid_dg_json_def').attr('value', json_str);
				//

				$( this ).dialog( "close" );
			},
			"Cancelar": function() {
				$( this ).dialog( "close" );
			}
		},
		open: function() {
			var gti_id = $( "#dist_gastos" ).data('dist_gast_node').children('.gti_id_i').attr('value');
			$('#dist_gastos #tg option[value="'+gti_id+'"]').prop('selected', true);

			var dist_gast_arr = JSON.parse($( "#dist_gastos" ).data('dist_gast_node').children('.dist_gast_json_i').attr('value'));

			$('#distribucion').find('tr:gt(0)').remove();

			var $tbl = $('#distribucion tr:last');
			var s_add = '';

			$.each(dist_gast_arr, function ( index, value ) {
				s_add = '<tr><td class="nome">'+value[0]+'</td>';
				s_add += '<td class="td_porc"><input type="text" class="porc" size="6" gco_cobj="'+value[1]+'" value="'+value[2]+'" /></td>';
				s_add += '<td><img src="img/delete.png" class="dist_gast_dele" title="Borrar"></td></tr>';
				$tbl.after(s_add);
				$tbl = $('#distribucion tr:last');
			});

			$('#primeruso').val('1');
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

	$('#dist_gast_msj').on('click', '.modal', function(){
		$( "#dist_gastos" ).data('dist_gast_node', $(this).parent() ).dialog( "open" );
		$('#tg').change();
	});

	$('#dist_gastos').on('change', '#tg', function(){
		var id = $(this).val();
		var valor = '#tg'+id;
		var lista = $(valor).html();

		$('#dist_gast_lst').html(lista);
		if($('#primeruso').val() == '1') {
			$('#primeruso').val('0');
		}
		else {
			$('#distribucion').find('tr:gt(0)').remove();

			//Si se cambia a 'Personas' se crea una nueva lista y automaticamente se agrega a la lista el usuario logueado a la distribucion de gastos
			if(id == '1') {
				var rec_usu_nombre = '<?php echo $rec_usu_nombre; ?>';
				var adm_usu_gco_cobj = '<?php echo $adm_usu_gco_cobj; ?>';
				var $tbl = $('#distribucion tr:last');
				var s_add = '<tr><td class="nome">'+rec_usu_nombre+'</td>';
				s_add += '<td class="td_porc"><input type="text" class="porc" size="6" gco_cobj="'+adm_usu_gco_cobj+'" value="100.00" /></td>';
				s_add += '<td><img src="img/delete.png" class="dist_gast_dele" title="Borrar"></td></tr>';
				$tbl.after(s_add);
			}
		}
	});

	$('#dist_gast_add').click(function(){
		var itemExists = false;
		var nom = $('#dist_gast_lst option:selected').text();
		var cobj = $('#dist_gast_lst').val();

		$('#distribucion td.nome').each(function(){
			if ($(this).text() == nom) {
				itemExists = true;
			}
		});

		if(!itemExists) {
			var $tbl = $('#distribucion tr:last');

			var s_add = '<tr><td class="nome">'+nom+'</td>';
			s_add += '<td class="td_porc"><input type="text" class="porc" size="6" gco_cobj="'+cobj+'" /></td>';
			s_add += '<td><img src="img/delete.png" class="dist_gast_dele" title="Borrar"></td></tr>';

			$tbl.after(s_add);

			$('#dist_gast_lst option:selected').next().attr('selected', 'selected');
			$(this).dist_porc_recalc();
		}
	});

	//For dynamic elements, you need to use event delegation using .on()
	$('#distribucion').on('click', '.dist_gast_dele', function(){
		$(this).parent().parent().remove();
		$(this).dist_porc_recalc();
	});

	$('#slave_usu_id').on('change', function(){
            var usuId=document.getElementById("slave_usu_id").value;
            location.href="movi_solicitud_otro.php?usuId="+usuId;
	});
	$("#slave_usu_id").select2({
            width: "300%",
            placeholder: "Seleccione una colaborador",
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
	$.fn.dist_porc_recalc = function() {
		var rowCount = $('#distribucion tr').length;
		rowCount--;

		var porcent = 100;

		$('#distribucion input.porc').each(function(){
			porcent = porcent - (100/rowCount).toFixed(2);

			$(this).val((100/rowCount).toFixed(2));
		});

		porcent = porcent + parseFloat($('#distribucion input.porc:last').val());
		$('#distribucion input.porc:last').val(porcent.toFixed(2));
	};

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

.encabezado_h {
	background-color: silver;
	text-align: center;
}

.iconos, .dele {
	vertical-align:text-top;
	cursor: pointer;
}

.calc_span {
	text-align: right;
	background-color: #ccffff;
	padding-left: 5px;
	padding-right: 5px;
}

.no-close .ui-dialog-titlebar-close {
display: none;
}

#tg1, #tg2, #tg3, #tg4, #dist_gastos {
	display:none;
}
</style>
</head>
<body onload="document.getElementById('act1').disabled=false;">
<?php include ("header.php");

    $nombresUsuario = '';
    foreach ($arrayUsuario as $item) {
        if ($item['id'] == $_GET["usuId"]) {
			$nombresUsuario = $item['persona_nombre'];
        }
    }
    if ($nombresUsuario == '') {
        $nombresUsuario = $item['persona_nombre'];
    }
?>

    <h1>Registro de planilla de movilidad <span id="nombreUsuario">(<?php echo strtoupper($nombresUsuario); ?>)</span></h1>

<form id="form1" action="movi_solicitud_p.php" method="post">

<table>
<tr>
    <td align="right">Colaborador:</td>
    <td>
        <select name="slave_usu_id" id="slave_usu_id" class="select2">
            <?php
            echo '<option value="">Seleccione un Colaborador</option>';
            foreach ($arrayUsuario as $item) {
                $selected='';
                if($item['id']==$_GET["usuId"]){
                    $selected ='selected';
                }
                //echo "\t\t\t<option value='".$item['usuario_id']."' $selected >".strtoupper ($item['nombre']." ".$item['apellidos'])."</option>\n";
				echo '<option value="' . $item['id'] . '" '.$selected.'>' . $item['codigo_identificacion'] . ' | ' . $item['persona_nombre'] . '</option>';
			}
            ?>
        </select>
    </td>
</tr>
<!--<tr><td align="right">Nombre colaborador:</td><td><?php // echo strtoupper($nombres.' '.$apePaterno.' '.$apeMaterno); ?></td></tr>-->
<tr><td align="right">DNI:</td><td><?php echo $dni; ?></td></tr>
<!--<tr><td align="right">Cargo:</td><td><?php // echo $cargo_desc; ?></td></tr>
<tr><td align="right">Area:</td><td><?php // echo $area_desc; ?></td></tr>
<tr><td align="right">Sucursal:</td><td><?php // echo $sucursal; ?></td></tr>-->
<tr><td align="right">Tope m&aacute;ximo diario:</td><td><?php echo $tope_maximo." $mon_nom ($mon_simb) ($mon_iso) <img src='$mon_img' style='vertical-align:text-top'>"; ?></td></tr>
<tr>
	<td align="right">Seleccione tipo:</td>
	<td>
		<input type="radio" name="tipo_inp" value="1" <?php echo (count($arr)>0?'checked':'disabled'); ?>> EAR
		<select name="tipo1_sel" id="tipo1_sel" <?php echo (count($arr)!=0?'':'disabled'); ?>>
<?php
foreach ($arr as $v) {
	echo "\t\t\t<option value='$v[0]'>$v[1]</option>\n";
}
?>
		</select>
<?php
if (count($arr)>0) {
?>
		<img src='img/modal.gif' id='abrir_ear' class='iconos' title='Consultar EAR en una nueva ventana'>
<?php
}
?>


	</td>
</tr>
<!--<tr><td align="right">Distrib. de gasto:</td><td><span id="dist_gast_msj"><?php // echo substr(getDistGastTemplate($lid_gti_def, $lid_dg_json_def, "def"), 6, -8); ?></span></td></tr>-->
</table>

<br>

<div>Detalle de los desplazamientos realizados:</div>
<table border="1" id="table1">
<tbody id="table1_body1">
<tr>
	<td class="encabezado_h" rowspan="2">Motivo</td>
	<td class="encabezado_h" rowspan="2">Fecha</td>
	<td class="encabezado_h" colspan="3">Desplazamiento</td>
	<td class="encabezado_h" rowspan="2">Estado</td>
	<td class="encabezado_h" rowspan="2">Borrar</td>
</tr>
<tr>
	<td class="encabezado_h">Salida</td>
	<td class="encabezado_h">Destino</td>
	<td class="encabezado_h">Monto</td>
</tr>
</tbody>
<tbody>
<tr>
	<td colspan="7">Agregar nueva fila <img src="img/plus.png" id="fila1_add" title="Agregar" class="iconos"></td>
</tr>
</tbody>
</table>

<br>

<table>
<tr>
	<td align="right">Total monto planilla:</td>
	<td class="calc_span"><span id="tot_mon_s">0.00</span><input type="hidden" name="tot_mon_inp" id="tot_mon_inp" value="0.00"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
</table>

    <p>Nota: Los montos calculados son referenciales y podr&iacute;an ser reajustados por la administraci&oacute;n.</p>

    Comentarios del colaborador: (m&aacute;ximo 300 caracteres)<br>
<textarea name="comentario1" cols="80" rows="6" maxlength="300" id="comentario1"></textarea>

<br>
<br>

<div style="display:none;" id="advertencia">
<div style="background-image:url('img/yell_bl.gif'); height:28px; width:100%;"></div>
<div style="background-color:yellow; text-align:center; font-weight:bold; width:100%;">PRECAUCION: Se han excedido los montos permitidos diarios.</div>
<br>
</div>

<input type="hidden" value="<?php echo $tope_maximo; ?>" name="tope_inp">
<input type="hidden" value="0" name="exc" id="exc">
<input type="hidden" value="<?php echo $lid_gti_def; ?>" name="lid_gti_def" id="lid_gti_def">
<input type="hidden" value='<?php echo $lid_dg_json_def; ?>' name="lid_dg_json_def" id="lid_dg_json_def">
<input type="submit" value="Grabar Planilla" name="act1" id="act1" disabled>

    <p>Descripci&oacute;n de los botones:<br>
<b>Grabar</b> almacena los datos ingresados.<br>
</p>

</form>

<div id="dist_gastos" title="Distribucion de Gastos">
	<table>
	<tr>
		<td>Tipo de gasto:</td>
		<td>
			<select id="tg">
<?php
$arr = getGastosTipos();

foreach ($arr as $v) {
	echo "\t\t\t\t<option value='$v[0]'>$v[1]</option>\n";
}
?>
			</select>
		</td>
	</tr>
	<tr><td>Seleccione:</td><td><select id="dist_gast_lst"></select> <img src="img/plus.png" id="dist_gast_add" title="Agregar"></td></tr>
	</table>

	<table id="distribucion" border="1">
	<tr><td class="encabezado_h">Nombre</td><td class="encabezado_h">Porcentaje</td><td class="encabezado_h">Borrar</td></tr>
	</table>

	<input type="hidden" id="primeruso" value="0">
</div>

<div id="tg1">
<?php
$arr = getGastosColObjects(1);

foreach ($arr as $v) {
	echo "\t<option value='$v[0]'>$v[1]</option>\n";
}
?>
</div>

<div id="tg2">
<?php
$arr = getGastosColObjects(2);

foreach ($arr as $v) {
	echo "\t<option value='$v[0]'>$v[0] - $v[1]</option>\n";
}
?>
</div>

<div id="tg3">
<?php
$arr = getGastosColObjects(3);

foreach ($arr as $v) {
	echo "\t<option value='$v[0]'>$v[0] - $v[1]</option>\n";
}
?>
</div>

<div id="tg4">
<?php
$arr = getGastosColObjects(4);

foreach ($arr as $v) {
	echo "\t<option value='$v[0]'>$v[0] - $v[1]</option>\n";
}
?>
</div>

<div id="dialog-confirm" title="Espere que complete el proceso" style="display:none; text-align:center;">
    <p>Por favor espere hasta que se complete la transacci&oacute;n, procesando...<br><br><img src="img/circle-loader.gif" title="Procesando..." class="iconos"></p>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
