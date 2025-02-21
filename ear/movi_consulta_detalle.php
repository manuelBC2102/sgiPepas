<?php
header('Content-Type: text/html; charset=UTF-8');
include("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
$opc = abs((int) filter_var($f_opc, FILTER_SANITIZE_NUMBER_INT));

list(
	$pla_numero, $est_id, $pla_reg_fec, $ear_numero, $tope_maximo, $usu_id, $ear_id,
	$est_nom, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec,
	$pla_exc, $pla_com1, $pla_com2, $pla_com3,
	$pla_tipo, $ccl_id, $cch_id
) = getPlanillaMovilidadInfo($id);

if ($est_id == 2 && $opc == 2) {
	echo "<font color='red'><b>ERROR: No se puede editar planilla anulada</b></font><br>";
	exit;
}

$close = 0;

if ($opc == 1) {
	$oper = "Consultar";

	if (isset($f_close)) {
		$close = abs((int) filter_var($f_close, FILTER_SANITIZE_NUMBER_INT));
	}
} else if ($opc == 2) {
	$oper = "Editar";
} else if ($opc == 3 || $opc == 4 || $opc == 5) {
	$oper = "Revisar";
} else if ($opc == 13) {
	$oper = "Aprobar";

	if ($est_id == 4) {
		echo "<font color='red'><b>ERROR: No se puede volver a aprobar una planilla aprobada</b></font><br>";
		exit;
	}

	// Valida si existe la caja chica
	$arr = getCajasChicasInfo($cch_id);
	if (empty($arr)) {
		echo "<font color='red'><b>ERROR: Valor no existe</b></font><br>";
		exit;
	}
	list(
		$cch_id, $cch_nombre, $suc_nombre, $mon_nom, $mon_iso, $mon_img, $cch_monto,
		$cch_abrv, $cch_gti, $cch_dg_json, $cch_cta_bco, $cch_act,
		$suc_id, $mon_id
	) = $arr;

	// Valida si la caja chica esta activa
	if ($cch_act == 0) {
		echo "<font color='red'><b>ERROR: Caja chica inactiva</b></font><br>";
		exit;
	}

	$arr = getUltLoteCajaChica($cch_id);
	// Si no existen lotes en esa caja chica se muestra error
	if (count($arr) == 0) {
		echo "<font color='red'><b>ERROR: Caja chica no cuenta con lotes</b></font><br>";
		exit;
	}
	// else {
	// if($arr[20]!=1) {
	// echo "<font color='red'><b>ERROR: Caja chica esta cerrada, no se puede agregar la planilla.</b></font><br>";
	// exit;
	// }
	// }
} else {
	$oper = "Operaci&oacute;n no v&aacute;lida";
	echo "<font color='red'><b>ERROR: $oper</b></font><br>";
	exit;
}

list(
	$dni, $nombres, $apePaterno, $apeMaterno, $cargo_id, $fecha_ing,
	$cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal
) = getInfoTrabajador(obtenerPersonaIdSGI($usu_id));

$mon_id = 1;
list($mon_nom, $mon_iso, $mon_simb, $mon_img) = getNomMoneda($mon_id);

$arrPlaMovDet = getPlanillaMovDetalle($id);
$arrDistribucionDetalle = getPlanillaMovDistribucionContable($id);

$lid_gti_def = $pla_gti;
$lid_dg_json_def = $pla_dg_json;

//$usu_id = $_SESSION['rec_usu_id'];
$rec_usu_nombre = getUsuarioNombre($usu_id);
$adm_usu_gco_cobj = getUsuGcoObj($usu_id);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<meta name="viewport" content="width=1312" />
	<!--<title><?php // echo $oper;
							?> Planilla de Movilidad - Administracion - Minapp</title>-->
	<style type="text/css">
		body {
			font-size: 10pt;
			font-family: arial, helvetica
		}

		.titulo {
			font-size: 14pt;
			font-family: arial, helvetica
		}
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
			$("#fecha").datepicker({
				numberOfMonths: 2,
				altField: "#fecha2",
				altFormat: "yy-mm-dd",
				minDate: 1,
				maxDate: 30
			});
			$("#fecha").datepicker($.datepicker.regional["es"]);
			$("#fecha").datepicker("option", "dateFormat", "yy-mm-dd");
			$("#fecha").datepicker("option", "dateFormat", "D, d M yy");
		});
	</script>

	<script>
		$(document).ready(function() {
			i = 1;
			opc = <?php echo $opc; ?>;
			est_id = <?php echo $est_id; ?>;
			flag = 0;
			distr_obligatoria = 0;
			if (opc == 3 && est_id == 3) {
				distr_obligatoria = 0;
			} else if (opc == 3) {
				distr_obligatoria = 1;
			}

			if (opc == 2 || opc == 3 || opc == 4 || opc == 13) {
				$('#table1').on('focus', ".fecdoc_inp", function() {
					$(this).datepicker({
						numberOfMonths: 2,
						maxDate: 0
					});
				});
			}

			$(document).on('keyup keypress', 'form input[type="text"]', function(e) {
				if (e.which == 13) {
					e.preventDefault();
					return false;
				}
			});

			$("#dialog-confirm").dialog({
				autoOpen: false,
				modal: true,
				dialogClass: "no-close",
				buttons: {},
				open: function() {}
			});

			$('#table1').on('change', '.aprob_sel', function() {
				var fila = $(this).parent().parent();
				var aprob_sel = parseInt($(this).val());
				if (aprob_sel == 1) {
					fila.children('.gast_asum_td').children('.gast_asum_i').show();
				} else {
					fila.children('.gast_asum_td').children('.gast_asum_i').hide();
				}
				$(document).checktopes_redraw();
			});

			$('#table1').on('change', '.gast_asum_i', function() {
				$(document).checktopes_redraw();
			});

			$('#table1').on('change', ".fecdoc_inp", function() {
				$(document).checktopes_redraw();
			});

			$('#table1').on('change', '.monto_inp', function() {
				if (opc != 2) {
					$(document).gast_asum_recalc($(this).parent().parent());
				}
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
				var aprob_sel = 0;
				$('#act3').prop('disabled', false);
				$('#act1').prop('disabled', false);

				// Primera barrida, suma los montos de acuerdo a la fecha, y muestra los mensajes de estado de acuerdo al monto ingresado
				$('.fecdoc_inp').each(function() {
					fecha = $(this).val();
					fila = $(this).parent().parent();
					if (opc == 1 && est_id != 4) {
						monto = parseFloat(fila.children('.monto_td').children('.monto_inp').val()) || 0;
					} else if (opc == 2) {
						monto = parseFloat(fila.children('.monto_td').children('.monto_inp').val()) || 0;
					} else {
						// entra a este camino cuando los valores son:
						//  opc = 3
						//  opc = 4
						//  opc = 5
						//  opc = 13
						//  opc = 1 y est_id = 4
						monto = parseFloat(fila.children('.gast_asum_td').children('.gast_asum_i').val()) || 0;
						aprob_sel = parseInt(fila.children('.aprob_sel_td').children('.aprob_sel').val());
						if (aprob_sel == 0) {
							monto = 0;
						}
					}
					if (fecha.length == 10 && monto > 0) {
						if (typeof arr_fecha[fecha] == 'undefined') {
							arr_fecha[fecha] = monto;
						} else {
							arr_fecha[fecha] += monto;
						}
						total += monto;
					}

					// No importa si el monto se pasa del tope porque eso se verifica en la siguiente barrida
					if (monto <= 0) {
						html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Inv&aacute;lido</div>';
					} else if (fecha.length != 10) {
						html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Error de fecha</div>';
					} else {
						html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Aceptado</div>';
					}
					fila.children('.estado_td').children('.estado_div').html(html);
				});

				// Segunda barrida, pinta de colores los recuadros donde corresponda cuando la suma de la fecha exceda el tope
				$('.fecdoc_inp').each(function() {
					fecha = $(this).val();
					fila = $(this).parent().parent();
					if (arr_fecha[fecha] > tope) {
						fila.children('.estado_td').children('.estado_div').html('<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>');
						$('#exc').val(1);
						$('#advertencia').show();
						if (opc == 4) {
							//					$('#act3').prop('disabled', true);
						}

						flag = 1;
						//				$('#act1').prop('disabled', true);
					}
				});

				// Coloca valor total en el cuadro resumen
				$('#tot_mon_inp').val(total.toFixed(2));
				$('#tot_mon_s').html(total.toFixed(2));

			};

			$.fn.gast_asum_recalc = function(fila) {
				var monto = (parseFloat(fila.children('.monto_td').children('.monto_inp').val())) || 0;

				fila.children('.gast_asum_td').children('.gast_asum_i').val(monto.toFixed(2));
				fila.children('.gast_asum_td').children('.gast_asum_i').rules('add', {
					max: monto
				});
				fila.children('.gast_asum_td').children('.gast_asum_i').valid();
			};

			$('#table1').on('click', '.dele', function() {
				var fila = $(this).parent().parent();
				fila.remove();
				$(document).checktopes_redraw();
			});

			$(document).ready(function() {
				// executes when HTML-Document is loaded and DOM is ready
				$('#act1').prop('disabled', false);
				$('#act2').prop('disabled', false);
				$('#act3').prop('disabled', false);
				$('#act4').prop('disabled', false);
				$('#act5').prop('disabled', false);
				$('#act6').prop('disabled', false);

				// add validation to some input fields
				if (opc == 3 || opc == 4 || opc == 5 || opc == 13) {
					if (opc == 3 || opc == 13) {
						$('#comentario1').prop('readonly', true);
					} else if (opc == 4) {
						$('#comentario1').prop('readonly', true);
						$('#comentario2').prop('readonly', true);
					} else if (opc == 5) {
						$('#comentario1').prop('readonly', true);
						$('#comentario2').prop('readonly', true);
						$('#comentario3').prop('readonly', true);

						$('#form1 input').prop('readonly', true);
						$('#form1 select').prop('disabled', true);
					}

					$('.monto_inp').each(function() {
						var monto = (parseFloat($(this).val())) || 0;

						$(this).rules('add', {
							min: 0.01
						});
						$(this).parent().parent().children('.gast_asum_td').children('.gast_asum_i').rules('add', {
							min: 0,
							max: monto
						});
					});
				}

				// if only querying disable fields
				if (opc == 1) {
					$('#comentario1').prop('readonly', true);
					$('#comentario2').prop('readonly', true);
					$('#comentario3').prop('readonly', true);
				}
				if (opc == 1 && (est_id == 4 || est_id == 5)) {
					$('.aprob_sel').each(function() {
						$(this).prop('disabled', true);
						$(this).parent().parent().children('.gast_asum_td').children('.gast_asum_i').prop('readonly', true);
					});
				}

				// calculates total
				$(document).checktopes_redraw();
			});

			$('#fila1_add').click(function() {
				var $tbl = $('#table1_body1 tr:last');
				var aprob_t = $('#aprob_template').html();
				var gast_asum_t = $('#gast_asum_template').html();
				var dele = '<img src="img/delete.png" class="dele" title="Borrar">';
				var agregarDistribucion = '<input type="hidden" value=""  id="lid_distribucion[{0}]" name="lid_distribucion[{0}]" class="distribucionContable" /><img src="img/plus.png"  onclick="abrirModalDistribucion(\'[{0}]\',1)" title="Agregar distribucion contable">&nbsp;';

				var s_add = '<tr class="fila_dato">';
				s_add += '<td class="motivo_td"><input type="text" value="" size="42" maxlength="100" class="motivo_inp" name="motivo_inp[{0}]"></td>';
				s_add += '<td class="fecdoc_td"><input type="text" value="" size="11" maxlength="10" class="fecdoc_inp" readonly name="fecdoc_inp[{0}]"></td>'; // Fecha
				s_add += '<td class="salida_td"><input type="text" value="" size="28" maxlength="100" class="salida_inp" name="salida_inp[{0}]"></td>';
				s_add += '<td class="destino_td"><input type="text" value="" size="28" maxlength="100" class="destino_inp" name="destino_inp[{0}]"></td>';
				s_add += '<td class="monto_td"><input type="text" value="" size="8" maxlength="9" id="monto_inp[{0}]" class="monto_inp" name="monto_inp[{0}]"></td>'; // Monto
				s_add += '<td class="estado_td"><div class="estado_div"></div></td>';
				if (opc == 3 || opc == 4 || opc == 5 || opc == 13) {
					s_add += '<td class="aprob_sel_td">' + aprob_t + '</td><td class="gast_asum_td">' + gast_asum_t + '</td>';
				}
				s_add += '<td>' + agregarDistribucion + dele + '</td>';
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
				if (opc == 3 || opc == 4 || opc == 5 || opc == 13) {
					$tbl.children('.gast_asum_td').children('.gast_asum_i').rules('add', {
						min: 0,
						max: 0
					});
				}

				$tbl.children('.motivo_td').children('.motivo_inp').focus();

			});

			if (opc == 2 || opc == 3 || opc == 4 || opc == 5 || opc == 13) {
				$("#form1").validate({});
			}

			$('#act1').click(function(event) {
				var saltar = -1;
				$("#table1 .distribucionContable").each(function() {
					var id = $(this).attr("id");
					id = id.replace("lid_distribucion", "");
					if (!validarDistribucion(id)) {
						saltar = 1;
						return false;
					}
				});

				if (saltar == 1) {
					if (!confirm('A\u00fan no completa la distribuci\u00f3n contable, desea continuar?')) {
						event.preventDefault();
					}
				}
				$(document).check_total(event);
			});

			$('#act3').click(function(event) {
				var saltar = 0;
				$("#table1 .distribucionContable").each(function() {
					var id = $(this).attr("id");
					id = id.replace("lid_distribucion", "");
					if (!validarDistribucion(id)) {
						saltar = 1;
						return false;
					}
				});

				if (saltar == 1) {
					if (distr_obligatoria == 1) {
						event.preventDefault();
					} else {
						if (!confirm('A\u00fan no completa la distribuci\u00f3n contable, desea continuar?')) {
							event.preventDefault();
						}
					}
				}
			});

			$('#act2').click(function(event) {
				return confirm('Esta seguro de anular la planilla?');
			});

			$('#act4').click(function(event) {
				window.close();
			});

			$.fn.check_total = function(event) {
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

			$("#dist_gastos").dialog({
				autoOpen: false,
				height: 600,
				width: 800,
				modal: true,
				buttons: {
					"Guardar": function() {
						var cad = "";
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
							gast_info_tooltip += nome + ' (' + porc + '%)\n';
							dist_gast_arr.push([nome, cobj, porc]);
						});

						var sum = 0;
						$('#distribucion input.porc').each(function() {
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
						switch (gast_tipo) {
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
						$("#dist_gastos").data('dist_gast_node').children('.dist_gast_tipo').attr('src', gast_img);
						$("#dist_gastos").data('dist_gast_node').children('.dist_gast_tipo').attr('title', gast_img_tooltip);

						$("#dist_gastos").data('dist_gast_node').children('.dist_gast_info').attr('title', gast_info_tooltip);

						$("#dist_gastos").data('dist_gast_node').children('.gti_id_i').attr('value', gast_tipo);
						$("#dist_gastos").data('dist_gast_node').children('.dist_gast_json_i').attr('value', json_str);

						// Cambia la distribucion por defecto del formulario
						$('#lid_gti_def').attr('value', gast_tipo);
						$('#lid_dg_json_def').attr('value', json_str);
						//

						$(this).dialog("close");
					},
					"Cancelar": function() {
						$(this).dialog("close");
					}
				},
				open: function() {
					var gti_id = $("#dist_gastos").data('dist_gast_node').children('.gti_id_i').attr('value');
					$('#dist_gastos #tg option[value="' + gti_id + '"]').prop('selected', true);

					var dist_gast_arr = JSON.parse($("#dist_gastos").data('dist_gast_node').children('.dist_gast_json_i').attr('value'));

					$('#distribucion').find('tr:gt(0)').remove();

					var $tbl = $('#distribucion tr:last');
					var s_add = '';

					$.each(dist_gast_arr, function(index, value) {
						s_add = '<tr><td class="nome">' + value[0] + '</td>';
						s_add += '<td class="td_porc"><input type="text" class="porc" size="6" gco_cobj="' + value[1] + '" value="' + value[2] + '" /></td>';
						s_add += '<td><img src="img/delete.png" class="dist_gast_dele" title="Borrar"></td></tr>';
						$tbl.after(s_add);
						$tbl = $('#distribucion tr:last');
					});

					$('#primeruso').val('1');
				}
			});

			$("#dialog-confirm").dialog({
				autoOpen: false,
				modal: true,
				dialogClass: "no-close",
				buttons: {},
				open: function() {}
			});

			$('#dist_gast_msj').on('click', '.modal', function() {
				$("#dist_gastos").data('dist_gast_node', $(this).parent()).dialog("open");
				$('#tg').change();
			});

			$('#dist_gastos').on('change', '#tg', function() {
				var id = $(this).val();
				var valor = '#tg' + id;
				var lista = $(valor).html();

				$('#dist_gast_lst').html(lista);
				if ($('#primeruso').val() == '1') {
					$('#primeruso').val('0');
				} else {
					$('#distribucion').find('tr:gt(0)').remove();

					//Si se cambia a 'Personas' se crea una nueva lista y automaticamente se agrega a la lista el usuario logueado a la distribucion de gastos
					if (id == '1') {
						var rec_usu_nombre = '<?php echo $rec_usu_nombre; ?>';
						var adm_usu_gco_cobj = '<?php echo $adm_usu_gco_cobj; ?>';
						var $tbl = $('#distribucion tr:last');
						var s_add = '<tr><td class="nome">' + rec_usu_nombre + '</td>';
						s_add += '<td class="td_porc"><input type="text" class="porc" size="6" gco_cobj="' + adm_usu_gco_cobj + '" value="100.00" /></td>';
						s_add += '<td><img src="img/delete.png" class="dist_gast_dele" title="Borrar"></td></tr>';
						$tbl.after(s_add);
					}
				}
			});

			$('#dist_gast_add').click(function() {
				var itemExists = false;
				var nom = $('#dist_gast_lst option:selected').text();
				var cobj = $('#dist_gast_lst').val();

				$('#distribucion td.nome').each(function() {
					if ($(this).text() == nom) {
						itemExists = true;
					}
				});

				if (!itemExists) {
					var $tbl = $('#distribucion tr:last');

					var s_add = '<tr><td class="nome">' + nom + '</td>';
					s_add += '<td class="td_porc"><input type="text" class="porc" size="6" gco_cobj="' + cobj + '" /></td>';
					s_add += '<td><img src="img/delete.png" class="dist_gast_dele" title="Borrar"></td></tr>';

					$tbl.after(s_add);

					$('#dist_gast_lst option:selected').next().attr('selected', 'selected');
					$(this).dist_porc_recalc();
				}
			});

			//For dynamic elements, you need to use event delegation using .on()
			$('#distribucion').on('click', '.dist_gast_dele', function() {
				$(this).parent().parent().remove();
				$(this).dist_porc_recalc();
			});

			$('#table_distribucion').on('click', '.dele', function() {
				$(this).parent().parent().remove();
				reenumerarFilasDetalleDistribucion();
			});

			$.fn.dist_porc_recalc = function() {
				var rowCount = $('#distribucion tr').length;
				rowCount--;

				var porcent = 100;

				$('#distribucion input.porc').each(function() {
					porcent = porcent - (100 / rowCount).toFixed(2);

					$(this).val((100 / rowCount).toFixed(2));
				});

				porcent = porcent + parseFloat($('#distribucion input.porc:last').val());
				$('#distribucion input.porc:last').val(porcent.toFixed(2));
			};

			// jQuery plugin to prevent double submission of forms
			jQuery.fn.preventDoubleSubmission = function() {
				$(this).on('submit', function(e) {
					var $form = $(this);

					if ($form.data('submitted') === true) {
						// Previously submitted - don't submit again
						e.preventDefault();
					} else {
						// Mark it so that the next submit can be ignored
						if ($form.valid()) {
							$form.data('submitted', true);
							$("#dialog-confirm").dialog("open");
						}
					}
				});

				// Keep chainability
				return this;
			};

			$('form').preventDoubleSubmission();

		});
		/*********************************** DISTRIBUCION CONTABLE *****************************/
		var dist_fila = null;
		var dist_tipo = null;
		var is_dua = 0;

		function abrirModalDistribucion(fila, opcion) {
			$("#distribucion_body tr.fila_dato").remove();
			dist_fila = fila;
			//                dist_tipo = tipo;
			if (obtenerMontoTotalesXFila(fila) > 0) {
				cargarDistribucion(fila, opcion);
				if (opcion == 0) {
					$(".addfilaDistribucion").hide();
					$("#btnGuardarModal").hide();
				}
				document.querySelector('#modalDistribucion').style.display = 'block';
			} else {
				alert('Primero debe ingresar los montos para llenar la distribucion contable.');
			}
		}

		function obtenerMontoTotalesXFila(fila) {
			var monto = document.getElementById("monto_inp" + fila + "").value;
			return (!isEmpty(monto) ? monto * 1 : 0);
		}

		function obtenerMontoXPorcentaje(indice, fila) {
			var porcentajeAcumulado = obtenerAcumuladoPorcentaje_MontoDistribucion(1);
			var porcentaje = (document.getElementById("porcentaje_distribucion" + indice).value) * 1; // $('#porcentaje_distribucion' + indice).val() * 1;

			if (porcentajeAcumulado > 100) {
				var nuevo_porcentaje = redondearDosDecimales(100 - porcentajeAcumulado + porcentaje);
				document.getElementById("porcentaje_distribucion" + indice).value = nuevo_porcentaje;
				document.getElementById("monto_distribucion" + indice).value = redondearDosDecimales(obtenerMontoTotalesXFila(fila) * nuevo_porcentaje / 100);
				alert('Porcentaje maximo 100(%)');
				return;
			}

			if (porcentaje <= 0) {
				document.getElementById("porcentaje_distribucion" + indice).value = 0;
				document.getElementById("monto_distribucion" + indice).value = redondearDosDecimales(0);
				alert('Porcentaje de pago debe ser positivo.');
				return;
			}

			var monto = (obtenerMontoTotalesXFila(fila) * porcentaje) / 100;
			document.getElementById("monto_distribucion" + indice).value = redondearDosDecimales(monto);
		}

		function obtenerPorcentajeXMonto(indice, fila) {
			var importeAcumulado = obtenerAcumuladoPorcentaje_MontoDistribucion(2);
			var importePago = (document.getElementById("monto_distribucion" + indice).value) * 1; //          $('#monto_distribucion' + indice).val() * 1;
			if (importeAcumulado > obtenerMontoTotalesXFila(fila)) {
				var nuevo_importe = (obtenerMontoTotalesXFila(fila) - importeAcumulado + importePago);
				var porcentaje = (nuevo_importe / obtenerMontoTotalesXFila(fila)) * 100;
				document.getElementById("porcentaje_distribucion" + indice).value = redondearDosDecimales(porcentaje);
				document.getElementById("monto_distribucion" + indice).value = redondearDosDecimales(nuevo_importe);
				alert('El monto no puede ser mayor a ' + obtenerMontoTotalesXFila(fila));
				return;
			}

			if (importePago <= 0) {
				document.getElementById("porcentaje_distribucion" + indice).value = redondearDosDecimales(0);
				document.getElementById("monto_distribucion" + indice).value = 0;
				alert('El monto debe ser positivo.');
				return;
			}

			var porcentaje = (importePago / obtenerMontoTotalesXFila(fila)) * 100;
			document.getElementById("porcentaje_distribucion" + indice).value = redondearDosDecimales(porcentaje);

		}

		function obtenerAcumuladoPorcentaje_MontoDistribucion(tipo) {

			var sumaPorcentaje = 0;
			var sumaMontos = 0;
			$('#distribucion_body tr.fila_dato').each(function() {
				var m_porcentaje = $(this).children('td.porcentaje_td').children('input').val();
				m_porcentaje = parseFloat(m_porcentaje);
				m_porcentaje = m_porcentaje.toFixed(2);
				sumaPorcentaje += m_porcentaje * 1;

				var m_monto = $(this).children('td.monto_td').children('input').val();
				m_monto = parseFloat(m_monto);
				m_monto = m_monto.toFixed(2);
				sumaMontos += m_monto * 1;

			});

			return (tipo == 1 ? sumaPorcentaje : sumaMontos);
		}

		function reenumerarFilasDetalleDistribucion() {
			var numerador = 1;
			$('#distribucion_body tr.fila_dato').each(function() {
				$(this).children('td.indice_distribucion_td').html(numerador);
				numerador++;
			});
		}

		function validarDistribucion(fila) {
			var mostrarMensaje = 0;
			mostrarMensaje = distr_obligatoria;
			var dataDistribucion = document.getElementById("lid_distribucion" + fila).value;
			var motivo = $("input[name='motivo_inp" + fila + "']").val();

			if (isEmpty(dataDistribucion)) {
				if (mostrarMensaje == 1)
					alert('Aun no llena la distribucion contable para ' + motivo);
				return false;
			}
			var data = JSON.parse(dataDistribucion);
			var sumaPorcentaje = 0;
			var sumaMontos = 0;
			var bandera_error = false;
			$.each(data, function(index, item) {
				if (isEmpty(item.centro_costo) && is_dua != 1) {
					if (mostrarMensaje == 1)
						alert('Aun no selecciona el centro de costo que corresponde en la fila ' + (index + 1) + ' para ' + motivo);

					bandera_error = true;
					return false;
				}

				if (isEmpty(item.cuenta_contable)) {
					if (mostrarMensaje == 1)
						alert('Aun no selecciona la cuenta contable que corresponde en la fila ' + (index + 1) + ' para ' + motivo);

					bandera_error = true;
					return false;
				}

				if (isEmpty(item.porcentaje) || item.porcentaje * 1 <= 0) {
					if (mostrarMensaje == 1)
						alert('El porcentaje debe ser mayor que cero en la fila ' + (index + 1) + ' para ' + motivo);

					bandera_error = true;
					return false;
				}

				sumaPorcentaje += ((item.porcentaje * 1).toFixed(2)) * 1;

				if (isEmpty(item.monto) || item.monto * 1 <= 0) {
					if (mostrarMensaje == 1)
						alert('El porcentaje debe ser mayor que cero en la fila ' + (index + 1) + ' para ' + motivo);

					bandera_error = true;
					return false;
				}

				sumaMontos += ((item.monto * 1).toFixed(2)) * 1;
			});

			if (bandera_error) {
				return false;
			}

			if (sumaMontos.toFixed(2) != obtenerMontoTotalesXFila(fila)) {
				if (mostrarMensaje == 1)
					alert('El total de los montos ingresados deben ser igual a ' + obtenerMontoTotalesXFila(fila) + ' para ' + motivo);
				return false;
			}

			if (sumaPorcentaje.toFixed(2) != 100) {
				if (mostrarMensaje == 1)
					alert('El total de los porcentajes ingresados deben ser igual a 100% para ' + motivo);
				return false;
			}

			return true;
		}

		function guardarDistribucionContable() {

			var sumaPorcentaje = 0;
			var sumaMontos = 0;
			var arrayDistribucion = [];

			var bandera_error = false;
			$('#distribucion_body tr.fila_dato').each(function() {
				var m_fila = $(this).children('td.indice_distribucion_td').html();
				var m_centro_costo = $(this).children('td.centro_costo_td').children('select').val();
				if (isEmpty(m_centro_costo) && is_dua != 1) {
					alert('Aun no selecciona el centro de costo que corresponde en la fila ' + m_fila);
					bandera_error = true;
					return false;
				}

				var m_cuenta_contable = $(this).children('td.cuenta_contable_td').children('select').val();
				if (isEmpty(m_cuenta_contable) && opc == 3) {
					alert('Aun no selecciona la cuenta contable que corresponde en la fila ' + m_fila);
					bandera_error = true;
					return false;
				}

				var m_porcentaje = $(this).children('td.porcentaje_td').children('input').val();

				if (isEmpty(m_porcentaje) || m_porcentaje * 1 <= 0) {
					alert('El porcentaje debe ser mayor que cero en la fila ' + m_fila);
					bandera_error = true;
					return false;
				}
				m_porcentaje = parseFloat(m_porcentaje);
				m_porcentaje = m_porcentaje.toFixed(2);
				sumaPorcentaje += m_porcentaje * 1;

				var m_monto = $(this).children('td.monto_td').children('input').val();
				if (isEmpty(m_monto) || m_monto * 1 <= 0) {
					alert('El porcentaje debe ser mayor que cero en la fila ' + m_fila);
					bandera_error = true;
					return false;
				}

				m_monto = parseFloat(m_monto);
				m_monto = m_monto.toFixed(2);

				sumaMontos += m_monto * 1;

				arrayDistribucion.push({
					tipo: dist_tipo,
					fila: dist_fila,
					cuenta_contable: m_cuenta_contable,
					centro_costo: m_centro_costo,
					porcentaje: m_porcentaje,
					monto: m_monto
				});

			});

			if (bandera_error) {
				return;
			}

			if (sumaMontos.toFixed(2) != obtenerMontoTotalesXFila(dist_fila)) {
				alert('El total de los montos ingresados deben ser igual a ' + obtenerMontoTotalesXFila(dist_fila) + '.');
				return;
			}

			if (sumaPorcentaje.toFixed(2) != 100) {
				alert('El total de los porcentajes ingresados deben ser igual a 100%');
				return;
			}

			document.getElementById("lid_distribucion" + dist_fila).value = JSON.stringify(arrayDistribucion);
			cerrarModal('modalDistribucion');
		}

		function cerrarModal(idModal) {
			document.querySelector('#' + idModal).style.display = 'none';
		}

		function isEmpty(value) {
			if ($.type(value) === 'undefined')
				return true;
			if ($.type(value) === 'null')
				return true;
			if ($.type(value) === 'string' && value.length <= 0)
				return true;
			if ($.type(value) === 'array' && value.length === 0)
				return true;
			if ($.type(value) === 'number' && isNaN(parseInt(value)))
				return true;

			return false;
		}

		function redondearDosDecimales(numero) {
			return (Math.round(numero * 100) / 100).toFixed(2);
		}

		function cargarDistribucion(fila, opcion) {
			var distribucion = document.getElementById("lid_distribucion" + fila).value;
			if (!isEmpty(distribucion)) {
				var centro_costo_t = $('#centro_costo_template').html();
				var cuenta_contable_t = $('#cuenta_contable_template').html();
				var dele = '<img src="img/delete.png" class="dele" title="Borrar">';
				var $tbl = $('#distribucion_body tr:last');
				var data = JSON.parse(distribucion);
				var indice = data.length;
				$.each(data, function(index, item) {
					var identificador = i;
					var s_add = '<tr class="fila_dato">';
					s_add += '<td class="indice_distribucion_td" >' + indice + '</td>';
					s_add += '<td class="cuenta_contable_td">' + cuenta_contable_t + '</td>'; //Cuenta Contable
					s_add += (is_dua != 1 ? '<td class="centro_costo_td">' + centro_costo_t + '</td>' : ''); //Centro Costo
					s_add += '<td class="porcentaje_td"><input type="number" value="' + (!isEmpty(item.porcentaje) ? (item.porcentaje * 1).toFixed(2) : 0) + '" min="0.1" max="100" id="porcentaje_distribucion[{0}]" name="porcentaje_distribucion[{0}]" onkeyup="obtenerMontoXPorcentaje(\'[{0}]\',\'' + fila + '\')"  ' + (opcion == 0 ? ' readonly ' : '') + '></td>';
					s_add += '<td class="monto_td"><input type="number" value="' + (!isEmpty(item.monto) ? (item.monto * 1).toFixed(2) : 0) + '" min="0.1" id="monto_distribucion[{0}]" name="monto_distribucion[{0}]" onkeyup="obtenerPorcentajeXMonto(\'[{0}]\',\'' + fila + '\')" ' + (opcion == 0 ? ' readonly ' : '') + '></td>';
					if (opcion == 1) {
						s_add += '<td>' + dele + '</td>';
					}
					s_add += '</tr>';
					var template = jQuery.validator.format(s_add);
					$tbl.after($(template(i++)));

					document.getElementById("cuenta_contable[" + identificador + "]").value = item.cuenta_contable;
					if (opcion == 0) {
						document.getElementById("cuenta_contable[" + identificador + "]").disabled = true;
					}
					if (is_dua != 1) {
						document.getElementById("centro_costo[" + identificador + "]").value = item.centro_costo;
						if (opcion == 0) {
							document.getElementById("centro_costo[" + identificador + "]").disabled = true;
						}
					}
					indice--;
				});
			} else if (opcion == 1) {
				agregarFilaDistribucion();
			}
		}

		function agregarFilaDistribucion() {
			if (redondearDosDecimales(obtenerAcumuladoPorcentaje_MontoDistribucion(2)) * 1 >= redondearDosDecimales(obtenerMontoTotalesXFila(dist_fila)) * 1) {
				alert('Ya ingreso el total del documento');
				return;
			}

			if (redondearDosDecimales(obtenerAcumuladoPorcentaje_MontoDistribucion(1)) * 1 >= 100) {
				alert('La suma de porcentaje no debe exceder 100');
				return;
			}

			var centro_costo_t = $('#centro_costo_template').html();
			var cuenta_contable_t = $('#cuenta_contable_template').html();
			var dele = '<img src="img/delete.png" class="dele" title="Borrar">';
			var $tbl = $('#distribucion_body tr:last');
			var identificador = i;
			var s_add = '<tr class="fila_dato">';
			s_add += '<td class="indice_distribucion_td">' + ($('#distribucion_body tr.fila_dato').length + 1) + '</td>';
			s_add += '<td class="cuenta_contable_td">' + cuenta_contable_t + '</td>'; //Cuenta Contable
			s_add += (is_dua != 1 ? '<td class="centro_costo_td">' + centro_costo_t + '</td>' : ''); //Centro Costo
			s_add += '<td class="porcentaje_td"><input type="number" value="0" min="0.1" max="100" id="porcentaje_distribucion[{0}]" name="porcentaje_distribucion[{0}]" onkeyup="obtenerMontoXPorcentaje(\'[{0}]\',\'' + dist_fila + '\')" ></td>';
			s_add += '<td class="monto_td"><input type="number" value="0" min="0.1" id="monto_distribucion[{0}]" name="monto_distribucion[{0}]" onkeyup="obtenerPorcentajeXMonto(\'[{0}]\',\'' + dist_fila + '\')"></td>';
			s_add += '<td>' + dele + '</td>';
			s_add += '</tr>';
			var template = jQuery.validator.format(s_add);
			$tbl.after($(template(i++)));
			document.getElementById("cuenta_contable[" + identificador + "]").value = 619;
			if (is_dua != 1) {
				document.getElementById("centro_costo[" + identificador + "]").value = 6;
			}

			document.getElementById("monto_distribucion[" + identificador + "]").value = redondearDosDecimales(obtenerMontoTotalesXFila(dist_fila)) * 1;
			document.getElementById("porcentaje_distribucion[" + identificador + "]").value = 100.00;

			// 6
			//619
		}
	</script>

	<style>
		#aprob_template,
		#gast_asum_template {
			display: none;
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

		form.xform label.error,
		label.error {
			/* remove the next line when you have trouble in IE6 with labels in list */
			color: red;
			font-style: italic
		}

		.encabezado_h {
			background-color: silver;
			text-align: center;
		}

		.iconos,
		.dele {
			vertical-align: text-top;
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

		#tg1,
		#tg2,
		#tg3,
		#tg4,
		#dist_gastos,
		#centro_costo_template,
		#cuenta_contable_template {
			display: none;
		}

		:root {
			--modal-duration: 0.3s;
			--modal-color: #428bca;
		}

		.button-info {
			background: #428bca;
			padding: 1em 2em;
			color: #fff;
			border: 0;
			border-radius: 5px;
			cursor: pointer;
		}

		.button-info:hover {
			background: #3876ac;
		}


		.button-danger {
			background: #d9534f;
			padding: 1em 2em;
			color: #fff;
			border: 0;
			border-radius: 5px;
			cursor: pointer;
		}

		.button-danger:hover {
			background: #d43f3a;
		}

		.modal {
			display: none;
			position: fixed;
			z-index: 1;
			left: 0;
			top: 0;
			height: 100%;
			width: 100%;
			overflow: auto;
			background-color: rgba(0, 0, 0, 0.5);
		}

		.modal-content {
			margin: 10% auto;
			width: 100%;
			box-shadow: 0 5px 8px 0 rgba(0, 0, 0, 0.2), 0 7px 20px 0 rgba(0, 0, 0, 0.17);
			animation-name: modalopen;
			animation-duration: var(--modal-duration);
		}

		.modal-header h2,
		.modal-footer h3 {
			margin: 0;
		}

		.modal-header {
			background: var(--modal-color);
			padding: 15px;
			color: #fff;
			border-top-left-radius: 5px;
			border-top-right-radius: 5px;
		}

		.modal-body {
			padding: 10px 20px;
			background: #fff;
		}

		.modal-footer {
			background: #fff;
			padding: 10px;
			/*color: #3876ac;*/
			text-align: right;
			border-bottom-left-radius: 5px;
			border-bottom-right-radius: 5px;
		}

		.close-modal {
			color: #ccc;
			float: right;
			font-size: 30px;
			color: #fff;
		}

		.close-modal:hover,
		.close-modal:focus {
			color: #000;
			text-decoration: none;
			cursor: pointer;
		}

		@keyframes modalopen {
			from {
				opacity: 0;
			}

			to {
				opacity: 1;
			}
		}
	</style>
</head>

<body>
	<?php include("header.php"); ?>

	<h1><?php echo $oper; ?> planilla de movilidad</h1>

	<form id="form1" action="movi_actualizar_p.php" method="post">

		<table>
			<tr>
				<td align="right">N&uacute;mero:</td>
				<td><?php echo $pla_numero; ?></td>
			</tr>
			<tr>
				<td align="right">Fecha registro:</td>
				<td><?php echo $pla_reg_fec; ?></td>
			</tr>
			<tr>
				<td align="right">Estado planilla:</td>
				<td><?php echo $est_nom; ?></td>
			</tr>
			<tr>
				<td align="right">Nombre colaborador:</td>
				<td><?php echo $nombres . ' ' . $apePaterno . ' ' . $apeMaterno; ?></td>
			</tr>
			<tr>
				<td align="right">DNI:</td>
				<td><?php echo $dni; ?></td>
			</tr>
			<!--<tr><td align="right">Cargo:</td><td><?php // echo $cargo_desc;
																								?></td></tr>
<tr><td align="right">Area:</td><td><?php // echo $area_desc;
																		?></td></tr>
<tr><td align="right">Sucursal:</td><td><?php // echo $sucursal;
																				?></td></tr>-->
			<tr>
				<td align="right">Tope m&aacute;ximo diario:</td>
				<td><?php echo $tope_maximo . " $mon_nom ($mon_simb) ($mon_iso) <img src='$mon_img' style='vertical-align:text-top'>"; ?></td>
			</tr>
			<tr>
				<td align="right">Tipo:</td>
				<td><?php echo $pla_tipo . ' - ' . $ear_numero; ?></td>
			</tr>
			<!--<tr><td align="right">Distrib. de gasto:</td><td><span id="dist_gast_msj"><?php echo substr(getDistGastTemplate($lid_gti_def, $lid_dg_json_def, "def"), 6, -8); ?></span></td></tr>-->
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
					<?php
					if ($opc == 3 || $opc == 4 || $opc == 5 || $opc == 13 || $est_id == 4 || $est_id == 5) {
					?>
						<td class="encabezado_h" rowspan="2">Aprobar</td>
						<td class="encabezado_h" rowspan="2" title="Gasto asumido por Minapp">Gasto Asumido</td>
					<?php
					}
					?>
					<td class="encabezado_h" rowspan="2">Acciones</td>
				</tr>
				<tr>
					<td class="encabezado_h">Salida</td>
					<td class="encabezado_h">Destino</td>
					<td class="encabezado_h">Monto</td>
				</tr>
				<?php echo getFilasPreviasPlaMovDet($arrPlaMovDet, $opc, $est_id, $arrDistribucionDetalle); ?>
			</tbody>
			<?php
			if ($opc == 2 || $opc == 3 || $opc == 4 || $opc == 13) {
				if ($opc == 2) {
					$colspan = "7";
				} else if ($opc == 3 || $opc == 4 || $opc == 13) {
					$colspan = "9";
				}
			?>
				<tbody>
					<tr>
						<td colspan="<?php echo $colspan; ?>">Agregar nueva fila <img src="img/plus.png" id="fila1_add" title="Agregar" class="iconos" /></td>
					</tr>
				</tbody>
			<?php
			}
			?>
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
		<textarea name="comentario1" cols="80" rows="6" maxlength="300" id="comentario1"><?php echo $pla_com1; ?></textarea>

		<br>
		<br>

		<?php
		if ($opc == 1 || $opc == 3 || $opc == 4 || $opc == 5 || $opc == 13) {
		?>

			Comentarios del jefe/gerente: (m&aacute;ximo 300 caracteres)<br>
			<textarea name="comentario2" cols="80" rows="6" maxlength="300" id="comentario2"><?php echo $pla_com2; ?></textarea>

			<br>
			<br>

		<?php
		}
		?>

		<?php
		if ($opc == 1 || $opc == 4 || $opc == 5) {
		?>

			Comentarios del administrador: (m&aacute;ximo 300 caracteres)<br>
			<textarea name="comentario3" cols="80" rows="6" maxlength="300" id="comentario3"><?php echo $pla_com3; ?></textarea>

			<br>
			<br>

		<?php
		}
		?>

		<?php
		if ($opc != 4) {
		?>
			<div style="display:none;" id="advertencia">
				<div style="background-image:url('img/yell_bl.gif'); height:28px; width:100%;"></div>
				<div style="background-color:yellow; text-align:center; font-weight:bold; width:100%;">PRECAUCION: Se han excedido los montos permitidos diarios.</div>
				<br>
			</div>
		<?php
		} else {
		?>
			<div style="display:none;" id="advertencia">
				<div style="background-image:url('img/red_bl.gif'); height:28px; width:100%;"></div>
				<div style="background-color:red; text-align:center; font-weight:bold; color:white; width:100%;">ALERTA: Se han excedido los montos permitidos diarios de la planilla de movilidad.</div>
				<br>
			</div>
		<?php
		}
		?>

		<?php
		if ($opc == 2) {
		?>

			<input type="hidden" value="<?php echo $id; ?>" name="id">
			<input type="hidden" value="0" name="exc" id="exc">
			<input type="hidden" value="<?php echo $lid_gti_def; ?>" name="lid_gti_def" id="lid_gti_def">
			<input type="hidden" value='<?php echo $lid_dg_json_def; ?>' name="lid_dg_json_def" id="lid_dg_json_def">
			<input type="submit" value="Actualizar Planilla" name="act1" id="act1" disabled>
			<input type="submit" value="Anular Planilla" name="act2" id="act2" disabled>

			<p>Descripci&oacute;n de los botones:<br>
				<b>Actualizar</b> almacena los datos ingresados.<br>
				<b>Anular</b> anula la planilla permanentemente.<br>
			</p>

		<?php
		} else if ($opc == 3 || $opc == 4) {
		?>

			<input type="hidden" value="<?php echo $id; ?>" name="id">
			<input type="hidden" value="0" name="exc" id="exc">
			<input type="hidden" value="<?php echo $lid_gti_def; ?>" name="lid_gti_def" id="lid_gti_def">
			<input type="hidden" value='<?php echo $lid_dg_json_def; ?>' name="lid_dg_json_def" id="lid_dg_json_def">
			<?php
			if ($opc == 3) {
			?>
				<input type="submit" value="Actualizar Planilla" name="act1" id="act1" disabled />
			<?php
			}
			?>
			<input type="submit" value="Conformidad de Revisi&oacute;n de Planilla" name="act3" id="act3" disabled>
			<input type="submit" value="Cerrar ventana" name="act4" id="act4" disabled>

			<p>Descripci&oacute;n de los botones:<br>
				<b>Conformidad</b> los datos mostrados son conformes.<br>
				<b>Cerrar ventana</b> no se grabaran cambios.<br>
			</p>

		<?php
		} else if ($opc == 5 || $close == 1) {
		?>

			<input type="hidden" value="<?php echo $id; ?>" name="id">
			<input type="hidden" value="0" name="exc" id="exc">
			<input type="hidden" value="<?php echo $lid_gti_def; ?>" name="lid_gti_def" id="lid_gti_def">
			<input type="hidden" value='<?php echo $lid_dg_json_def; ?>' name="lid_dg_json_def" id="lid_dg_json_def">
			<input type="submit" value="Cerrar ventana" name="act4" id="act4" disabled>

			<p>Descripci&oacute;n de los botones:<br>
				<b>Cerrar ventana</b> no se grabaran cambios.<br>
			</p>

		<?php
		} else if ($opc == 13) {
		?>

			<input type="hidden" value="" name="plm_cch_aprob">
			<input type="hidden" value="<?php echo $id; ?>" name="id">
			<input type="hidden" value="0" name="exc" id="exc">
			<input type="hidden" value="<?php echo $lid_gti_def; ?>" name="lid_gti_def" id="lid_gti_def">
			<input type="hidden" value='<?php echo $lid_dg_json_def; ?>' name="lid_dg_json_def" id="lid_dg_json_def">
			<input type="submit" value="Actualizar Planilla" name="act1" id="act1" disabled>
			<input type="submit" value="Anular Planilla" name="act6" id="act6" disabled>
			<input type="submit" value="Aprobar Planilla" name="act5" id="act5" disabled>

			<p>Descripci&oacute;n de los botones:<br>
				<b>Actualizar</b> almacena los datos ingresados.<br>
				<b>Anular</b> anula la planilla permanentemente.<br>
				<b>Aprobar</b> almacena los datos ingresados y aprueba la planilla.<br>
			</p>

		<?php
		}
		?>

	</form>

	<div id="centro_costo_template">
		<select class="centro_costo" id="centro_costo[{0}]" name="centro_costo[{0}]">
			<?php
			$arr = getCentroCosto();

			foreach ($arr as $padre) {
				if (isEmpty($padre[2])) {
					echo '<optgroup label="' . $padre[3] . '  |  ' . $padre[1] . '">\n';
					foreach ($arr as $hijo) {
						if ($hijo[2] == $padre[0])
							echo "<option value='$hijo[0]'>$hijo[3] | $hijo[1]</option>\n";
					}
					echo ' </optgroup>\n';
				}
			}
			?>
		</select>
	</div>

	<div id="cuenta_contable_template">
		<select class="cuenta_contable" id="cuenta_contable[{0}]" name="cuenta_contable[{0}]">
			<?php
			$arr = getCuentasContablesPlanillaMovilidad();
			foreach ($arr as $cuenta) {
				echo "<option value='$cuenta[0]' " . ($cuenta[3] == 1 ? "disabled" : "") . "  > $cuenta[1]</option>\n";
			}
			?>
		</select>
	</div>

	<?php
	if ($opc == 2 || $opc == 3 || $opc == 4 || $opc == 13) {
	?>
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
				<tr>
					<td>Seleccione:</td>
					<td><select id="dist_gast_lst"></select> <img src="img/plus.png" id="dist_gast_add" title="Agregar"></td>
				</tr>
			</table>

			<table id="distribucion" border="1">
				<tr>
					<td class="encabezado_h">Nombre</td>
					<td class="encabezado_h">Porcentaje</td>
					<td class="encabezado_h">Borrar</td>
				</tr>
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

		<div id="aprob_template">
			<select id='aprob_sel[{0}]' class='aprob_sel' name='aprob_sel[{0}]'>
				<option value='1'>Si</option>
				<option value='0'>No</option>
			</select>
		</div>

		<div id="gast_asum_template">
			<input type='text' size='8' maxlength='9' class='gast_asum_i' name='gast_asum[{0}]'>
		</div>
	<?php
	}
	?>

	<div id="dialog-confirm" title="Espere que complete el proceso" style="display:none; text-align:center;">
		<p>Por favor espere hasta que se complete la transaccion, procesando...<br><br><img src="img/circle-loader.gif" title="Procesando..." class="iconos"></p>
	</div>
	<div id="modalDistribucion" class="modal">
		<div class="modal-content">
			<div class="modal-header">
				<span class="close-modal" onclick="cerrarModal('modalDistribucion');">&times;</span>
				<h2>Ingreso de la distribuci&oacute;n contable</h2>
			</div>
			<div class="modal-body">
				<table border="1" id="table_distribucion" width=100%>
					<tbody id="distribucion_body">
						<tr>
							<td colspan="<?php echo (!$isDua ? '6' : '5') ?>">Distribuci&oacute;n Contable</td>
						</tr>
						<tr>
							<td class="encabezado_h" width=5%>#</td>
							<td class="encabezado_h" width=20%>Cuenta Contable</td>
							<?php echo (!$isDua ? ' <td class="encabezado_h" width=30%>Centro Costo</td>' : '') ?>
							<td class="encabezado_h" width=15%>Porcentaje(%)</td>
							<td class="encabezado_h" width=10%>Monto</td>
							<td class="encabezado_h" width=5%>Borrar</td>
						</tr>
					</tbody>
					<tbody>
						<tr>
							<td class="addfilaDistribucion" colspan="<?php echo (!$isDua ? '6' : '5') ?>">Nueva fila <img id="agregarFilaDistribucion" src="img/plus.png" title="Agregar" class="iconos" onclick="agregarFilaDistribucion();" /></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button id="btnCerrarModal" class="button-danger" onclick="cerrarModal('modalDistribucion');">Cerrar</button>
				<button id="btnGuardarModal" class="button-info" onclick="guardarDistribucionContable();">Guardar</button>
			</div>
		</div>
	</div>
	<?php include("footer.php"); ?>
</body>

</html>
