<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';
include 'parametros.php';

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
	$ear_liq_gast_asum, $pla_id, $ear_act_obs1,$ear_aprob_usu,
        $master_usu_id, $comodin1, $comodin2, $dua_id,$tipoCambioFechaLiq,$guardarTcSgi,$periodo_id,
        $dua_serie,$dua_numero) = getSolicitudInfo($id);
$arrSolSubt = getSolicitudSubtotales($id);
$arrLiqDet = getLiqDetalle($id);

//$isDua = ($usu_id == $pAXISADUANA || $usu_id == $pAXISGLOBAL);
$contadorPerfil=obtenerPerfilContador($pPERFIL_PROVEEDOR_DUA,$usu_id);
// $isDua=($contadorPerfil>0);
$isDua = false;

$mon_saldo_s = number_format($ear_monto-$ear_liq_mon, 2, '.', '');
$tot_mon_doc_s = number_format($ear_liq_mon+$ear_liq_ret+$ear_liq_det, 2, '.', '');
$gast_asum_cola_s = number_format($tot_mon_doc_s-$ear_liq_gast_asum, 2, '.', '');
switch (true) {
	case ($ear_liq_dcto == 0):
		$resul_msg = "(Saldo cero)";
		$resul_inp_s = "0.00";
		break;
	case ($ear_liq_dcto > 0):
		$resul_msg = "<font color='red'><b>(Devoluci&oacute;n)</b></font>";
		$resul_inp_s = $ear_liq_dcto;
		break;
	case ($ear_liq_dcto < 0):
		$resul_msg = "<font color='green'><b>(Abonar)</b></font>";
		$resul_inp_s = $ear_liq_dcto*-1;
		break;
}

$sol_msj="";
if(!is_null($usu_act)) {
	$sol_msj=" por ".$usu_act." el ".$ear_act_fec;
}
if(!is_null($ear_act_motivo)) {
	$sol_msj.=" (Motivo: ".$ear_act_motivo.")";
}

$rec_usu_nombre = $_SESSION['rec_usu_nombre'];
$adm_usu_gco_cobj = $_SESSION['adm_usu_gco_cobj'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=1312" />
<!--<title>Detalle de liquidaci&oacute;n</title>-->
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
	mon_id=<?php echo $mon_id; ?>;
	fec_hoy='<?php echo date('d/m/Y'); ?>';

	$('#sol_via_detalle_btn').click(function(){
         if ($(this).attr('src') === 'img/plus.png') {
			$('#sol_via_detalle_tbl').show();
		 }
		 else {
			$('#sol_via_detalle_tbl').hide();
		 }

		 var src = ($(this).attr('src') === 'img/plus.png')
            ? 'img/minus.png'
            : 'img/plus.png';
         $(this).attr('src', src);

         var title = ($(this).attr('title') === 'Mostrar')
            ? 'Ocultar'
            : 'Mostrar';
         $(this).attr('title', title);
	});

	$( "#dist_gastos" ).dialog({
		autoOpen: false,
		height: 600,
		width: 800,
		modal: true,
		buttons: {
			"Cerrar": function() {
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
				s_add += '<td class="td_porc"><input type="text" class="porc" size="6" gco_cobj="'+value[1]+'" value="'+value[2]+'" readonly /></td>';
				s_add += '<td></td></tr>';
				$tbl.after(s_add);
				$tbl = $('#distribucion tr:last');
			});

			$('#primeruso').val('1');
		}
	});

	$('#liquidacion').on('click', '.modal', function(){
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

	$('#liquidacion').on('click', '#act_est_plamov', function(){
		var id = <?php echo (!is_null($pla_id)?$pla_id:-1); ?>;
		$('#pm_estado').html('<b>Cargando</b> <img src="img/loading.gif">');
		var tc_inp = parseFloat($('#tc_inp\\[Splamov\\]').val());
		var conv = 0;

		$.when(
			$.getJSON('ws_pla_mov_info.php', { id:id } , function (data) {
				$('#pla_monto_div').html(data.pla_monto);
				if (data.est_id == 4) {
					$('#pm_estado').html("Falta revisar <a href='movi_consulta_detalle.php?id="+id+"&opc=5' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'>");
				}
				else if (data.est_id == 5) {
					$('#pm_estado').html("Revisado <a href='movi_consulta_detalle.php?id="+id+"&opc=5' target='_blank'><img src='img/modal.gif' id='abrir_plamov' class='iconos' title='Abrir Planilla de Movilidad en una nueva ventana'></a> <img src='img/reload.gif' id='act_est_plamov' class='iconos' title='Actualizar estado'>");
				}
				else {
					$('#pm_estado').html("ERROR: Estado invalido");
				}

				if (mon_id == 1) {
					$('#conv_noafecto_inp\\[Splamov\\]').val(data.pla_monto);
				}
				else if (mon_id == 2) {
					conv = parseFloat(data.pla_monto)/tc_inp;
					$('#conv_noafecto_inp\\[Splamov\\]').val(conv.toFixed(2));
				}
				$('#gast_asum\\[Splamov\\]').val(data.pla_monto);

				$('#pm_rev').val(data.est_id);
			})
		).then(function(){
			$(document).totalizar_recalc();
		});
	});

	$('#doc_sust_detalle').on('change', '.conc_l', function(){
		$(this).ret_det_recalc($(this).parent().parent());
	});

	$('#doc_sust_detalle').on('change', '.tipo_doc', function(){
		var fila = $(this).parent().parent();
		var taxcode = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('taxcode'));

		// Sirve para que a la hora de editar estas filas, los numero de serie y documento no puedan ser editados porque son autogenerados por el sistema
		// Esto solo aplica a los recibos de gastos (RGS)
		if ($(this).prop('name').indexOf('[S') == -1) {
			if (parseInt($(this).val()) == 10) {
				fila.children('.ser_doc_td').children('.ser_doc_inp').prop('readonly', true);
				fila.children('.num_doc_td').children('.num_doc_inp').prop('readonly', true);
				fila.children('.ser_doc_td').children('.ser_doc_inp').val(0);
				fila.children('.num_doc_td').children('.num_doc_inp').val(0);
			}
			else {
				fila.children('.ser_doc_td').children('.ser_doc_inp').prop('readonly', false);
				fila.children('.num_doc_td').children('.num_doc_inp').prop('readonly', false);
			}
		}

		if (taxcode == 3) {
			fila.children('.afecto_sel_td').children('.afecto_sel').show();
                        fila.children('.montOtro_td').children('.montOtro_inp').show();
                        fila.children('.montOtro_td').children('.montOtro_inp').val();
                        fila.children('.montIcbp_td').children('.montIcbp_inp').show();
                        fila.children('.montIcbp_td').children('.montIcbp_inp').val();

			$(this).tc_afec_redraw(fila);
		}else if(taxcode==6){
			fila.children('.afecto_sel_td').children('.afecto_sel').hide();
			fila.children('.afecto_sel_td').children('.afecto_sel').val(2);
			fila.children('.afecto_td').children('.afecto_inp').hide();
                        fila.children('.afecto_td').children('.afecto_inp').val('');
			fila.children('.noafecto_td').children('.noafecto_inp').show();
                        fila.children('.noafecto_td').children('.noafecto_inp').val('');
                        fila.children('.montOtro_td').children('.montOtro_inp').hide();
                        fila.children('.montOtro_td').children('.montOtro_inp').val();
                        fila.children('.montIcbp_td').children('.montIcbp_inp').hide();
                        fila.children('.montIcbp_td').children('.montIcbp_inp').val();

			$(this).ret_det_recalc(fila);
                }
		else {
			fila.children('.afecto_sel_td').children('.afecto_sel').hide();
			fila.children('.afecto_sel_td').children('.afecto_sel').val(1);
			fila.children('.afecto_td').children('.afecto_inp').show();
			fila.children('.noafecto_td').children('.noafecto_inp').hide();
			fila.children('.noafecto_td').children('.noafecto_inp').val('');
			fila.children('.montOtro_td').children('.montOtro_inp').hide();
                        fila.children('.montOtro_td').children('.montOtro_inp').val();
                        fila.children('.montIcbp_td').children('.montIcbp_inp').hide();
                        fila.children('.montIcbp_td').children('.montIcbp_inp').val();

			$(this).ret_det_recalc(fila);
		}
	});

	$('#doc_sust_detalle').on('change', '.ruc_nro_i', function(){
		var ruc_nro = $(this).val();
		var prov_nom_id = $(this).parent().parent().children('.prov_nom_td').children('.prov_nom_i');
		var prov_ret = $(this).parent().parent().children('.prov_nom_td').children('.prov_ret');
		var prov_act = $(this).parent().parent().children('.prov_nom_td').children('.prov_act');
		var fila = $(this).parent().parent();

		prov_nom_id.html('<b>Cargando</b> <img src="img/loading.gif">');

		$.when(
			$.getJSON('ruc_validador.php', { ruc_nro:ruc_nro } , function (data) {
				var prov_msj;

				if (data.ruc_act == -1) {
					prov_msj = "<font color='red'><i>ERROR: RUC no existe. Verificar o cambiar el tipo de documento a Recibo de gastos.</i></font>";
				}
				else if (data.ruc_act == 0) {
					prov_msj = "<font color='red'><i>ERROR: RUC de "+data.prov_nom+" no esta ACTIVO. Debe cambiar el tipo de documento a Otros.</i></font>";
				}
				else {
					if (data.ruc_hab == 0) {
						prov_msj = data.prov_nom+" <img src='img/alert.png' title='RUC NO HABIDO' class='iconos'>";
					}
					else {
						prov_msj = data.prov_nom;
					}
				}
				prov_nom_id.html(prov_msj);
				prov_ret.val(data.ruc_ret);
				prov_act.val(data.ruc_act);
			})
		).then(function(){
			$(document).ret_det_recalc(fila);
		});
	});

	$('#doc_sust_detalle').on('change', '.tipo_mon', function(){
		$(this).tc_afec_redraw($(this).parent().parent());
	});

	$('#doc_sust_detalle').on('change', '.afecto_sel', function(){
		$(this).tc_afec_redraw($(this).parent().parent());
	});

	$('#doc_sust_detalle').on('change', '.afecto_inp', function(){
		$(this).gast_asum_recalc($(this).parent().parent());
		$(this).ret_det_recalc($(this).parent().parent());
	});

	$('#doc_sust_detalle').on('change', '.noafecto_inp', function(){
		$(this).gast_asum_recalc($(this).parent().parent());
		$(this).ret_det_recalc($(this).parent().parent());
	});

        $('#doc_sust_detalle').on('change', '.montOtro_inp', function(){
		$(this).gast_asum_recalc($(this).parent().parent());
		$(this).ret_det_recalc($(this).parent().parent());
	});

	$('#doc_sust_detalle').on('change', '.gast_asum_i', function(){
		$(this).ret_det_recalc($(this).parent().parent());
	});

	$('#doc_sust_detalle').on('change', '.aplic_retdet', function(){
		$(this).ret_det_recalc($(this).parent().parent());
	});

	$('#doc_sust_detalle').on('change', '.aprob_sel', function(){
		var fila = $(this).parent().parent();
		var aprob_sel = parseInt($(this).val());
		if (aprob_sel == 1) {
			fila.children('.gast_asum_td').children('.gast_asum_i').show();
		}
		else {
			fila.children('.gast_asum_td').children('.gast_asum_i').hide();
		}
		$(this).ret_det_recalc(fila);
	});

	$('#doc_sust_detalle').on('focus',".fecha_inp", function(){
		$(this).datepicker({
			numberOfMonths: 2,
			maxDate: 0
		});
	});

	$('#doc_sust_detalle').on('change',".fecha_inp", function(){
		var fila = $(this).parent().parent();
		var mon_id_sel = parseInt(fila.children('.tipo_mon_td').children('.tipo_mon').val());
		var fec_doc_arr = $(this).val().split('/');
		var fec_doc = fec_doc_arr[2] + '-' + fec_doc_arr[1] + '-' + fec_doc_arr[0];
		var tc_div = fila.children('.tc_td').children('.tc_div');
		var tc_inp = fila.children('.tc_td').children('.tc_inp');
		var conc_id = fila.children('.conc_td').children('.conc_l').children('option:selected').attr('conc_id');
		var ret_tasa_inp = fila.children('.conc_td').children('.ret_tasa_inp');
		var ret_min_monto_inp = fila.children('.conc_td').children('.ret_min_monto_inp');
		var det_tasa_inp = fila.children('.conc_td').children('.det_tasa_inp');
		var det_min_monto_inp = fila.children('.conc_td').children('.det_min_monto_inp');

		tc_div.html('<b>Cargando</b> <img src="img/loading.gif">');

		$.when(
			$.getJSON('tipo_cambio.php', { fec:fec_doc } , function (data) {
				if (data.tc_precio == -1) {
					tc_div.html('Error <img src="img/error.png" title="No se encontr&oacute; el tipo de cambio\npara el dia seleccionado,\nnotificar a Contabilidad.\nDe lo contrario no podr&aacute;\ncompletar el registro." class="iconos">');
				}
				else {
					if (mon_id_sel != mon_id) {
						tc_div.text(data.tc_precio);
					}
					else {
						tc_div.text('');
					}
				}
				tc_inp.val(data.tc_precio);
			}),
			$.getJSON('ret_det.php', { conc_id:conc_id, fec:fec_doc } , function (data) {
				ret_tasa_inp.val(data.ret_tasa);
				ret_min_monto_inp.val(data.ret_minmonto);
				det_tasa_inp.val(data.det_tasa);
				det_min_monto_inp.val(data.det_minmonto);
			})
		).then(function(){
			$(document).ret_det_recalc(fila);
		});
	});

	$('#doc_sust_detalle').on('change', '.conc_l', function(){
		var fila = $(this).parent().parent();
		var conc_id = $(this).children('option:selected').attr('conc_id');
		fila.children('.conc_td').children('.conc_id_inp').val(conc_id);
		var mon_id_sel = parseInt(fila.children('.tipo_mon_td').children('.tipo_mon').val());
		var fec_doc_arr = fila.children('.fec_doc_td').children('.fecha_inp').val().split('/');
		var fec_doc = fec_doc_arr[2] + '-' + fec_doc_arr[1] + '-' + fec_doc_arr[0];
		var ret_tasa_inp = fila.children('.conc_td').children('.ret_tasa_inp');
		var ret_min_monto_inp = fila.children('.conc_td').children('.ret_min_monto_inp');
		var det_tasa_inp = fila.children('.conc_td').children('.det_tasa_inp');
		var det_min_monto_inp = fila.children('.conc_td').children('.det_min_monto_inp');

		$.when(
			$.getJSON('ret_det.php', { conc_id:conc_id, fec:fec_doc } , function (data) {
				ret_tasa_inp.val(data.ret_tasa);
				ret_min_monto_inp.val(data.ret_minmonto);
				det_tasa_inp.val(data.det_tasa);
				det_min_monto_inp.val(data.det_minmonto);
			})
		).then(function(){
			$(document).ret_det_recalc(fila);
		});
	});

	$.fn.tc_afec_redraw = function(fila) {
		var mon_id_sel = parseInt(fila.children('.tipo_mon_td').children('.tipo_mon').val());
		var afecto_sel = parseInt(fila.children('.afecto_sel_td').children('.afecto_sel').val());
		fila.children('.conv_afecto_td').children('.conv_afecto_inp').hide();
		fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').hide();
                fila.children('.conv_otro_td').children('.conv_otro_inp').hide();
                fila.children('.conv_icbp_td').children('.conv_icbp_inp').hide();

		switch (afecto_sel) {
			case 1:
				fila.children('.afecto_td').children('.afecto_inp').show();
				fila.children('.noafecto_td').children('.noafecto_inp').hide();
				fila.children('.noafecto_td').children('.noafecto_inp').val('');
				fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').val('');
                                fila.children('.montOtro_td').children('.montOtro_inp').show();
                                if (mon_id_sel != mon_id) {
					fila.children('.conv_afecto_td').children('.conv_afecto_inp').show();
                                        fila.children('.conv_otro_td').children('.conv_otro_inp').show();
                                        fila.children('.conv_icbp_td').children('.conv_icbp_inp').show();
				}


				break;
			case 2:
				fila.children('.afecto_td').children('.afecto_inp').hide();
				fila.children('.afecto_td').children('.afecto_inp').val('');
				fila.children('.conv_afecto_td').children('.conv_afecto_inp').val('');
				fila.children('.noafecto_td').children('.noafecto_inp').show();
				if (mon_id_sel != mon_id) {
					fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').show();
                                        fila.children('.conv_otro_td').children('.conv_otro_inp').show();
                                        fila.children('.conv_icbp_td').children('.conv_icbp_inp').show();
				}
				break;
			case 3:
				fila.children('.afecto_td').children('.afecto_inp').show();
				fila.children('.noafecto_td').children('.noafecto_inp').show();
				if (mon_id_sel != mon_id) {
					fila.children('.conv_afecto_td').children('.conv_afecto_inp').show();
					fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').show();
                                        fila.children('.conv_otro_td').children('.conv_otro_inp').show();
                                        fila.children('.conv_icbp_td').children('.conv_icbp_inp').show();
				}
				break;
		}

		fila.children('.fec_doc_td').children('.fecha_inp').change();
	};

	$.fn.ret_det_recalc = function(fila) {

                var tipo_doc = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('value'));
		var ruc_req = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('rucreq'));
		var ruc_ret = parseInt(fila.children('.prov_nom_td').children('.prov_ret').val());
		var conc_det_tasa = parseFloat(fila.children('.conc_td').children('.det_tasa_inp').val());
		var conc_det_minmonto = parseFloat(fila.children('.conc_td').children('.det_min_monto_inp').val());
		var ret_tasa = parseFloat(fila.children('.conc_td').children('.ret_tasa_inp').val());
		var ret_minmonto = parseFloat(fila.children('.conc_td').children('.ret_min_monto_inp').val());
		var monto_afecto = parseFloat(fila.children('.afecto_td').children('.afecto_inp').val());
		var monto_noafecto = parseFloat(fila.children('.noafecto_td').children('.noafecto_inp').val());
                var monto_otro = parseFloat(fila.children('.montOtro_td').children('.montOtro_inp').val());
                var monto_icbp = parseFloat(fila.children('.montIcbp_td').children('.montIcbp_inp').val());
		var mon_id_sel = parseInt(fila.children('.tipo_mon_td').children('.tipo_mon').val());
		var mon_nom_sel = fila.children('.tipo_mon_td').children('.tipo_mon').children('option:selected').text();
		var tc_inp = parseFloat(fila.children('.tc_td').children('.tc_inp').val());
		var fec_doc = fila.children('.fec_doc_td').children('.fecha_inp').val();
		var aplret = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('aplret'));
		var apldet = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('apldet'));

		if ((ruc_req != 1) || (ruc_ret==1 && conc_det_tasa==0)) {
			fila.children('.aplic_retdet_td').children('.aplic_retdet').hide();
		}
		else {
			fila.children('.aplic_retdet_td').children('.aplic_retdet').show();
		}

		if (mon_id_sel == mon_id) {
			fila.children('.conv_afecto_td').children('.conv_afecto_inp').val(monto_afecto);
			fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').val(monto_noafecto);
                        fila.children('.conv_otro_td').children('.conv_otro_inp').val(monto_otro);
                        fila.children('.conv_icbp_td').children('.conv_icbp_inp').val(monto_icbp);

			if (mon_id == 1) {
				var conv_conc_det_minmonto = conc_det_minmonto;
				var conv_ret_minmonto = ret_minmonto;
			} else if (mon_id == 2) {
				var conv_conc_det_minmonto = conc_det_minmonto/tc_inp;
				var conv_ret_minmonto = ret_minmonto/tc_inp;
			}
		}
		else if (tc_inp>0) {
			if (mon_id_sel==2 && mon_id==1) {
                                let factor = (tipo_doc == "19" ? -1:1);
				var monto_conv_afecto = (monto_afecto*tc_inp) || 0;
				var monto_conv_noafecto = (monto_noafecto*tc_inp) || 0;
                                var monto_conv_otro = (monto_otro*tc_inp) || 0;
                                var monto_conv_icbp = (monto_icbp*tc_inp) || 0;
				var conv_conc_det_minmonto = conc_det_minmonto/tc_inp;
				var conv_ret_minmonto = ret_minmonto/tc_inp;
			}
			else if (mon_id_sel==1 && mon_id==2) {
				var monto_conv_afecto = (monto_afecto/tc_inp) || 0;
				var monto_conv_noafecto = (monto_noafecto/tc_inp) || 0;
                                var monto_conv_otro = (monto_otro/tc_inp) || 0;
                                var monto_conv_icbp = (monto_icbp/tc_inp) || 0;
				var conv_conc_det_minmonto = conc_det_minmonto;
				var conv_ret_minmonto = ret_minmonto;
			}

			fila.children('.conv_afecto_td').children('.conv_afecto_inp').val(monto_conv_afecto.toFixed(2));
			fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').val(monto_conv_noafecto.toFixed(2));
                        fila.children('.conv_otro_td').children('.conv_otro_inp').val(monto_conv_otro.toFixed(2));
                        fila.children('.conv_icbp_td').children('.conv_icbp_inp').val(monto_conv_icbp.Fixed(2));

		}

		fila.children('.retdet_td').children('.retdet_div').text('Exonerado');
		fila.children('.retdet_td').children('.retdet_tip').val(0);
		fila.children('.retdet_td').children('.retdet_monto').val(0);
		if (ruc_req == 1 && tc_inp>0) {
			var otra_moneda = '';
			if (conc_det_tasa > 0 && conv_conc_det_minmonto <= monto_afecto && apldet == 1) {
				var monto_det = monto_afecto*(conc_det_tasa/100)
				if (mon_id_sel==2 && mon_id==1) {
					otra_moneda = ' ('+(monto_det*tc_inp).toFixed(2)+' PEN)';
				}
				else if (mon_id_sel==1 && mon_id==2) {
					otra_moneda = ' ('+(monto_det/tc_inp).toFixed(2)+' USD)';
				}
				fila.children('.retdet_td').children('.retdet_div').text('Aplica detraccion de '+monto_det.toFixed(2)+' '+mon_nom_sel+otra_moneda);
				fila.children('.retdet_td').children('.retdet_tip').val(1);
				fila.children('.retdet_td').children('.retdet_monto').val(monto_det.toFixed(2));
			}
			else if (ruc_ret == 0 && conv_ret_minmonto <= monto_afecto && aplret == 1) {
				var monto_ret = monto_afecto*(ret_tasa/100)
				if (mon_id_sel==2 && mon_id==1) {
					otra_moneda = ' ('+(monto_ret*tc_inp).toFixed(2)+' PEN)';
				}
				else if (mon_id_sel==1 && mon_id==2) {
					otra_moneda = ' ('+(monto_ret/tc_inp).toFixed(2)+' USD)';
				}
				fila.children('.retdet_td').children('.retdet_div').text('Aplica retencion de '+monto_ret.toFixed(2)+' '+mon_nom_sel+otra_moneda);
				fila.children('.retdet_td').children('.retdet_tip').val(2);
				fila.children('.retdet_td').children('.retdet_monto').val(monto_ret.toFixed(2));
			}
		}
		if (fec_doc.length == 0) {
			fila.children('.retdet_td').children('.retdet_div').text('Falta fecha');
		}

		$(document).totalizar_recalc();
	};

	$.fn.totalizar_recalc = function() {
		//Totalizadores
		var tot_mon_liq = 0;
		var tot_mon_ret = 0;
		var tot_mon_ret_no = 0;
		var tot_mon_det = 0;
		var tot_mon_det_no = 0;
		var retdet_tip = 0;
		var aplic_retdet = 0;
		var tc_fila = 0;
		var mon_retdet_fila = 0;
		var tipo_mon_fila = 0;
		var mon_gast_asum_fila = 0;
		var tot_mon_gast_asum = 0;
		var aprob_sel = 0;
		var tipo_doc = 0;
		$('.conv_afecto_inp').each(function(){
                        tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
                        //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
                        let factor = (tipo_doc == "19" ? -1:1);
			tot_mon_liq += factor *(parseFloat($(this).val())) || 0;
 			tot_mon_liq += factor *((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
                        tot_mon_liq += factor *((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
                        tot_mon_liq += factor *((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);

                        retdet_tip = parseInt($(this).parent().parent().children('.retdet_td').children('.retdet_tip').val());
			aplic_retdet = parseInt($(this).parent().parent().children('.aplic_retdet_td').children('.aplic_retdet').val());
			tc_fila = parseFloat($(this).parent().parent().children('.tc_td').children('.tc_inp').val());
			mon_retdet_fila = parseFloat($(this).parent().parent().children('.retdet_td').children('.retdet_monto').val());
			tipo_mon_fila = parseInt($(this).parent().parent().children('.tipo_mon_td').children('.tipo_mon').val());
			mon_gast_asum_fila = (parseFloat($(this).parent().parent().children('.gast_asum_td').children('.gast_asum_i').val())) || 0;
			aprob_sel = parseInt($(this).parent().parent().children('.aprob_sel_td').children('.aprob_sel').val());

			if (tipo_mon_fila == 2 && mon_id == 1) {
				mon_retdet_fila = (mon_retdet_fila*tc_fila).toFixed(2);
				mon_gast_asum_fila = (mon_gast_asum_fila*tc_fila).toFixed(2);
			} else if (tipo_mon_fila == 1 && mon_id == 2) {
				mon_retdet_fila = (mon_retdet_fila/tc_fila).toFixed(2);
				mon_gast_asum_fila = (mon_gast_asum_fila/tc_fila).toFixed(2);
			}

			if (aprob_sel == 0) {
				mon_gast_asum_fila = 0;
			}

			if (retdet_tip == 1) { // Detraccion
				if (aplic_retdet == 1) {
					tot_mon_det += parseFloat(mon_retdet_fila);
				}
				else if (aplic_retdet == 0) {
					tot_mon_det_no += parseFloat(mon_retdet_fila);
				}
			}
			else if (retdet_tip == 2) { // Retencion
				if (aplic_retdet == 1) {
					tot_mon_ret += parseFloat(mon_retdet_fila);
				}
				else if (aplic_retdet == 0) {
					tot_mon_ret_no += parseFloat(mon_retdet_fila);
				}
			}

			tot_mon_gast_asum += factor*(parseFloat(mon_gast_asum_fila));
		});

		var tot_mon_liq_res = tot_mon_liq-tot_mon_ret-tot_mon_det;
		var mon_saldo = <?php echo $ear_monto; ?>-tot_mon_liq_res;
		if (mon_saldo < 0) {
			$('#mon_saldo_s').css('color', 'red');
		}
		else {
			$('#mon_saldo_s').css('color', 'black');
		}
		var tot_mon_gast_asum2 = tot_mon_liq-tot_mon_gast_asum; // Gastos asumidos por el colaborador
		//var mon_abodes = mon_saldo+tot_mon_ret_no+tot_mon_det_no+tot_mon_gast_asum2; // Monto abono o descuento
                var mon_abodes = mon_saldo+tot_mon_gast_asum2; // Monto abono o descuento

		var resul_msg = "";
		var resul_inp = 0;
		switch (true) {
			case (mon_abodes == 0):
				resul_msg = "(Saldo cero)";
				resul_inp = "0.00";
				break;
			case (mon_abodes > 0):
				resul_msg = "<font color='red'><b>(Devoluci&oacute;n)</b></font>";
				resul_inp = mon_abodes.toFixed(2);
				break;
			case (mon_abodes < 0):
				resul_msg = "<font color='green'><b>(Abonar)</b></font>";
				resul_inp = (mon_abodes*-1).toFixed(2);
				break;
		}

		$('#tot_mon_liq').val(tot_mon_liq_res.toFixed(2));
		$('#mon_saldo').val(mon_saldo.toFixed(2));
		$('#tot_mon_ret').val(tot_mon_ret.toFixed(2));
		$('#tot_mon_ret_no').val(tot_mon_ret_no.toFixed(2));
		$('#tot_mon_det').val(tot_mon_det.toFixed(2));
		$('#tot_mon_det_no').val(tot_mon_det_no.toFixed(2));
		$('#tot_mon_doc').val(tot_mon_liq.toFixed(2));
		$('#tot_mon_gast_asum').val(tot_mon_gast_asum.toFixed(2));
		$('#tot_mon_gast_asum2').val(tot_mon_gast_asum2.toFixed(2));
		$('#resul_inp').val(mon_abodes.toFixed(2));

		$('#tot_mon_liq_s').html(tot_mon_liq_res.toFixed(2));
		$('#mon_saldo_s').html(mon_saldo.toFixed(2));
		$('#tot_mon_ret_s').html(tot_mon_ret.toFixed(2));
		$('#tot_mon_ret_no_s').html(tot_mon_ret_no.toFixed(2));
		$('#tot_mon_det_s').html(tot_mon_det.toFixed(2));
		$('#tot_mon_det_no_s').html(tot_mon_det_no.toFixed(2));
		$('#tot_mon_doc_s').html(tot_mon_liq.toFixed(2));
		$('#tot_mon_gast_asum_s').html(tot_mon_gast_asum.toFixed(2));
		$('#tot_mon_gast_asum2_s').html(tot_mon_gast_asum2.toFixed(2));
		$('#resul_msg').html(resul_msg);
		$('#resul_inp_s').html(resul_inp);

		$(document).topes_recalc();
	};

	$.fn.topes_recalc = function() {
		var tot_mon_liq = 0;
		var solsubt = 0;
		var html = '';
                var tipo_doc = 0;
		$('#bole_body .conv_afecto_inp').each(function(){
                        tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
                        let factor = (tipo_doc == "19" ? -1:1);
			tot_mon_liq += factor*(parseFloat($(this).val())) || 0;
			tot_mon_liq += factor*(parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0;
                        tot_mon_liq += factor*((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
                        tot_mon_liq += factor *((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);

                });
		$('#liqsubt01').text(tot_mon_liq.toFixed(2));
		solsubt = parseFloat($('#solsubt01').text());
		if (solsubt >= tot_mon_liq.toFixed(2)) {
			html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
		}
		else {
			html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
		}
		$('#divsubt01').html(html);

		tot_mon_liq = 0;
		$('#alim_body .conv_afecto_inp').each(function(){
                        tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
                        //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
                        let factor = (tipo_doc == "19" ? -1:1);
			tot_mon_liq += factor*((parseFloat($(this).val())) || 0);
                        tot_mon_liq += factor*((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
                        tot_mon_liq += factor*((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
                        tot_mon_liq += factor *((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);
		});
		$('#liqsubt02').text(tot_mon_liq.toFixed(2));
		solsubt = parseFloat($('#solsubt02').text());
		if (solsubt >= tot_mon_liq.toFixed(2)) {
			html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
		}
		else {
			html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
		}
		$('#divsubt02').html(html);

		tot_mon_liq = 0;
		$('#hosp_body .conv_afecto_inp').each(function(){
                        tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
                        //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
                        let factor = (tipo_doc == "19" ? -1:1);
                        tot_mon_liq += factor*((parseFloat($(this).val())) || 0);
                        tot_mon_liq += factor*((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
                        tot_mon_liq += factor*((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
                        tot_mon_liq += factor *((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);
		});
		$('#liqsubt03').text(tot_mon_liq.toFixed(2));
		solsubt = parseFloat($('#solsubt03').text());
		if (solsubt >= tot_mon_liq.toFixed(2)) {
			html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
		}
		else {
			html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
		}
		$('#divsubt03').html(html);

		tot_mon_liq = 0;
		$('#movi_body .conv_afecto_inp').each(function(){
			tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
                        //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
                        let factor = (tipo_doc == "19" ? -1:1);
                        tot_mon_liq += factor*((parseFloat($(this).val())) || 0);
                        tot_mon_liq += factor*((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
                        tot_mon_liq += factor*((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
		});
		$('#liqsubt04').text(tot_mon_liq.toFixed(2));
		solsubt = parseFloat($('#solsubt04').text());
		if (solsubt >= tot_mon_liq.toFixed(2)) {
			html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
		}
		else {
			html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
		}
		$('#divsubt04').html(html);

		tot_mon_liq = 0;
		$('#gast_body .conv_afecto_inp').each(function(){
			tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
                        //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
                        let factor = (tipo_doc == "19" ? -1:1);
                        tot_mon_liq += factor*((parseFloat($(this).val())) || 0);
                        tot_mon_liq += factor*((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
                        tot_mon_liq += factor*((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
                        tot_mon_liq += factor *((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);
                });
		$('#liqsubt05').text(tot_mon_liq.toFixed(2));
		solsubt = parseFloat($('#solsubt05').text());
		if (solsubt >= tot_mon_liq.toFixed(2)) {
			html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
		}
		else {
			html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
		}
		$('#divsubt05').html(html);

		tot_mon_liq = 0;
		$('#otro_body .conv_afecto_inp').each(function(){
			tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
                        //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
                        let factor = (tipo_doc == "19" ? -1:1);
                        tot_mon_liq += factor*((parseFloat($(this).val())) || 0);
                        tot_mon_liq += factor*((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
                        tot_mon_liq += factor*((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
                        tot_mon_liq += factor *((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);
		});
		$('#liqsubt06').text(tot_mon_liq.toFixed(2));
		solsubt = parseFloat($('#solsubt06').text());
		if (solsubt >= tot_mon_liq.toFixed(2)) {
			html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
		}
		else {
			html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
		}
		$('#divsubt06').html(html);
	};

	$.fn.gast_asum_recalc = function(fila) {
		var monto_afecto = (parseFloat(fila.children('.afecto_td').children('.afecto_inp').val())) || 0;
		var monto_noafecto = (parseFloat(fila.children('.noafecto_td').children('.noafecto_inp').val())) || 0;
                                var monto_otro = (parseFloat(fila.children('.montOtro_td').children('.montOtro_inp').val())) || 0;

                var asum = (monto_afecto+monto_noafecto+monto_otro);

		fila.children('.gast_asum_td').children('.gast_asum_i').val(asum.toFixed(2));
		fila.children('.gast_asum_td').children('.gast_asum_i').rules('add', {
			max: asum
		});
		fila.children('.gast_asum_td').children('.gast_asum_i').valid();
	};

	$('#doc_sust_detalle').on('click', '.dele', function(){
		$(this).parent().parent().remove();
		$(this).ret_det_recalc($(this).parent().parent());
	});

	$(document).ready(function() {
		// executes when HTML-Document is loaded and DOM is ready
		$('#liquidacion input').prop('readonly', true);
		$('#liquidacion select').prop('disabled', true);

		// add validation to some input fields
		$('.afecto_inp').each(function(){
			var monto_afecto = (parseFloat($(this).val())) || 0;
			var monto_noafecto = (parseFloat($(this).parent().parent().children('.noafecto_td').children('.noafecto_inp').val())) || 0;
			var monto_otro = (parseFloat($(this).parent().parent().children('.montOtro_td').children('.montOtro_inp').val())) || 0;
                        var monto_icbp = (parseFloat($(this).parent().parent().children('.montIcbp_td').children('.montIcbp_inp').val())) || 0;
			var asum = monto_afecto+monto_noafecto+monto_otro+monto_icbp;

			$(this).rules('add', {
				min: 0.01
			});
			$(this).parent().parent().children('.noafecto_td').children('.noafecto_inp').rules('add', {
				min: 0.01
			});
			$(this).parent().parent().children('.ser_doc_td').children('.ser_doc_inp').rules('add', {
				required: true
			});
			$(this).parent().parent().children('.num_doc_td').children('.num_doc_inp').rules('add', {
				required: true
			});
			$(this).parent().parent().children('.det_doc_td').children('.det_doc_inp').rules('add', {
				required: true
			});
			$(this).parent().parent().children('.gast_asum_td').children('.gast_asum_i').rules('add', {
				min: 0.01,
				max: asum
			});

			var taxcode = parseInt($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('taxcode'));
			if (taxcode != 3) {
				$(this).parent().parent().children('.afecto_sel_td').children('.afecto_sel').hide();
			}
		});

		// calculate entire rows
		$(document).totalizar_recalc();

		// change to readonly some fields
		// aplica para recibos de gastos (RGS)
		$('.tipo_doc').each(function(){
			var fila = $(this).parent().parent();

			if(parseInt($(this).val()) == 10) {
				fila.children('.ser_doc_td').children('.ser_doc_inp').prop('readonly', true);
				fila.children('.num_doc_td').children('.num_doc_inp').prop('readonly', true);
			}
		});
	});

	var s_fecsernumdet = '<td class="fec_doc_td"><input type="text" value="'+fec_hoy+'" size="11" maxlength="10" class="fecha_inp" readonly name="fec_doc[{0}]"></td>'; //Fecha
	s_fecsernumdet += '<td class="ser_doc_td"><input type="text" value="0" size="6" maxlength="5" class="ser_doc_inp" name="ser_doc[{0}]"></td>'; //Serie
	s_fecsernumdet += '<td class="num_doc_td"><input type="text" value="0" size="9" maxlength="7" class="num_doc_inp" name="num_doc[{0}]"></td>'; //Numero
	s_fecsernumdet += '<td class="det_doc_td"><input type="text" value="" size="14" maxlength="200" class="det_doc_inp" name="det_doc[{0}]"></td>'; //Detalle
	var s_afeconretdet = '<td class="afecto_sel_td"><select id="afecto_sel[{0}]" class="afecto_sel" name="afecto_sel[{0}]"><option value="1">Si</option><option value="2">No</option><option value="3">Mixto</option></select></td>'; //Afecto
	s_afeconretdet += '<td class="afecto_td"><input type="text" value="0" size="8" maxlength="9" id="afecto_inp[{0}]" class="afecto_inp" name="afecto_inp[{0}]"></td>'; //Monto Afecto
	s_afeconretdet += '<td class="noafecto_td"><input type="text" value="0" size="8" maxlength="9" id="noafecto_inp[{0}]" class="noafecto_inp" name="noafecto_inp[{0}]" style="display: none;"></td>'; //Monto NoAfecto
	s_afeconretdet += '<td class="montOtro_td"><input type="text" value="0" size="8" maxlength="9" id="montOtro_inp[{0}]" class="montOtro_inp" name="montOtro_inp[{0}]"></td>'; //Monto otro
	s_afeconretdet += '<td class="montIcbp_td"><input type="text" value="0" size="8" maxlength="9" id="montIcbp_inp[{0}]" class="montIcbp_inp" name="montIcbp_inp[{0}]"></td>'; //Monto IBCP
        s_afeconretdet += '<td class="tc_td"><div class="tc_div"></div><input type="hidden" class="tc_inp" name="tc_inp[{0}]" value="-1"></td>'; //T/C;
	s_afeconretdet += '<td class="conv_afecto_td"><input type="text" value="" size="8" maxlength="9" id="conv_afecto_inp[{0}]" class="conv_afecto_inp" name="conv_afecto_inp[{0}]" style="display: none;" readonly></td>'; //Conversion Afecto
	s_afeconretdet += '<td class="conv_noafecto_td"><input type="text" value="" size="8" maxlength="9" id="conv_noafecto_inp[{0}]" class="conv_noafecto_inp" name="conv_noafecto_inp[{0}]" style="display: none;" readonly></td>'; //Conversion NoAfecto
	s_afeconretdet += '<td class="conv_otro_td"><input type="text" value="" size="8" maxlength="9" id="conv_otro_inp[{0}]" class="conv_otro_inp" name="conv_otro_inp[{0}]" style="display: none;" readonly></td>'; //Conversion Otro
	s_afeconretdet += '<td class="conv_icbp_td"><input type="text" value="" size="8" maxlength="9" id="conv_icbp_inp[{0}]" class="conv_icbp_inp" name="conv_icbp_inp[{0}]" style="display: none;" readonly></td>'; //Conversion ICBP
        s_afeconretdet += '<td class="aplic_retdet_td"><select class="aplic_retdet" name="aplic_retdet[{0}]"><option value="1">Si</option><option value="0">No</option></select></td>'; //Ret/Det
	s_afeconretdet += '<td class="retdet_td"><div class="retdet_div"></div><input type="hidden" class="retdet_tip" name="retdet_tip[{0}]" value="0"><input type="hidden" class="retdet_monto" name="retdet_monto[{0}]" value="0"></td>'; //Monto Ret:Det

	$( "#liquidacion" ).validate({
	});

	$('#btn2').click(function( event ){
		history.back();
		return false;
	});

});
</script>

<style>
#tg1, #tg2, #tg3, #tg4, #tipo_doc_template, #tipo_mon_template, #ruc_nro_template, #dist_gastos {
	display:none;
}

#bole_conc_template, #alim_conc_template, #hosp_conc_template, #movi_conc_template, #gast_conc_template, #otro_conc_template {
	display:none;
}

#aprob_template, #gast_asum_template {
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

.encabezado_h {
	background-color: silver;
}

.iconos, .modal, .dele, .dist_gast_dele, #dist_gast_add {
	vertical-align:text-top;
	cursor: pointer;
}

.dist_gast_info, .dist_gast_tipo {
	vertical-align:text-top;
}

.calc_inp {
	text-align: right;
	background-color: #ccffff;
}

.calc_span {
	text-align: right;
	background-color: #ccffff;
	padding-left: 5px;
	padding-right: 5px;
}
</style>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Detalle de liquidaci&oacute;n <?php echo strtolower($zona_nom); ?></h1>

<table>
<tr><td>N&uacute;mero Liquidaci&oacute;n</td><td><?php echo $ear_numero; ?></td></tr>
<tr><td>Fecha Solicitud</td><td><?php echo $ear_sol_fec; ?></td></tr>
<tr><td>Estado Solicitud</td><td><?php echo $est_nom.$sol_msj; ?></td></tr>
<tr><td>Nombre</td><td><?php echo $ear_tra_nombres; ?></td></tr>
<tr><td>DNI</td><td><?php echo $ear_tra_dni; ?></td></tr>
<!--<tr><td>Cargo</td><td><?php // echo $ear_tra_cargo; ?></td></tr>
<tr><td>Area</td><td><?php // echo $ear_tra_area; ?></td></tr>
<tr><td>Sucursal</td><td><?php // echo $ear_tra_sucursal; ?></td></tr>-->
<tr><td>Fecha de Liquidacion</td><td><?php echo $ear_liq_fec; ?></td></tr>
<tr><td>Monto Solicitado</td><td><?php echo $ear_monto; ?></td></tr>
<tr><td>Moneda</td><td><?php echo "$mon_nom ($mon_simb) ($mon_iso) <img src='$mon_img' style='vertical-align:text-top'>"; ?></td></tr>
<!--<tr><td>Numero de cuenta<br>para la transferencia</td><td><?php // echo $ear_tra_cta; ?></td></tr>-->
<tr><td>Motivo</td><td><?php echo $ear_sol_motivo; ?></td></tr>
            <?php
            if ($isDua){
            ?>
            <tr>
                <td>DUA </td>
                <td>
                    <?php
                        $duaSerie=$dua_serie;
                        $duaNumero=$dua_numero;
                        if($dua_id!=null){
                            $arrDua = getDuaXDuaId($dua_id);
                            $duaSerie=$arrDua[0][4];
                            $duaNumero=$arrDua[0][5];
                        }
                        echo $duaSerie." - ".$duaNumero;
                    ?>
                </td>
            </tr>
            <?php
            }
            ?>
<?php
if ($periodo_id!=null) {
    ?>
    <tr>
        <td>Periodo </td>
        <td>
            <?php
            if($periodo_id==-1 || $periodo_id==null){
                echo 'Por documento';
            }else{
                $arrPer = obtenerPeriodoSGIXId($periodo_id);
                $perAnio=$arrPer[0]['anio'];
                $perMes=($arrPer[0]['mes']*1<10?'0'.$arrPer[0]['mes']:$arrPer[0]['mes']);
                echo $perAnio.' | '.$perMes;
            }
            ?>
        </td>
    </tr>
    <?php
}
?>
</table>

<form id="liquidacion">

<br>
<div>Subtotal de los viaticos registrados en la solicitud y la liquidaci&oacute;n (topes) <img src="img/minus.png" id="sol_via_detalle_btn" title="Ocultar" class='iconos'></div>
<table border="1" id="sol_via_detalle_tbl">
<tr>
	<td class="encabezado_h">C&oacute;digo</td>
	<td class="encabezado_h">Nombre</td>
	<td class="encabezado_h">Solicitud</td>
	<td class="encabezado_h">Liquidaci&oacute;n</td>
	<td class="encabezado_h">Estado</td>
</tr>
<tr>
	<td>01</td>
	<td>Boletos de Viaje / Pasajes A&eacute;reos</td>
	<td align='right' id='solsubt01'><?php echo isset($arrSolSubt['01']) ? $arrSolSubt['01'] : '0.00'; ?></td>
	<td align='right' id='liqsubt01'>0.00</td>
	<td><div id='divsubt01'><div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div></div></td>
</tr>
<tr>
	<td>02</td>
	<td>Alimentacion / Pension</td>
	<td align='right' id='solsubt02'><?php echo isset($arrSolSubt['02']) ? $arrSolSubt['02'] : '0.00'; ?></td>
	<td align='right' id='liqsubt02'>0.00</td>
	<td><div id='divsubt02'><div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div></div></td>
</tr>
<tr>
	<td>03</td>
	<td>Hospedaje</td>
	<td align='right' id='solsubt03'><?php echo isset($arrSolSubt['03']) ? $arrSolSubt['03'] : '0.00'; ?></td>
	<td align='right' id='liqsubt03'>0.00</td>
	<td><div id='divsubt03'><div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div></div></td>
</tr>
<tr>
	<td>04</td>
	<td>Movilidad / Combustible</td>
	<td align='right' id='solsubt04'><?php echo isset($arrSolSubt['04']) ? $arrSolSubt['04'] : '0.00'; ?></td>
	<td align='right' id='liqsubt04'>0.00</td>
	<td><div id='divsubt04'><div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div></div></td>
</tr>
<!--<tr>
	<td>05</td>
	<td>Gastos de Representaci&oacute;n</td>
	<td align='right' id='solsubt05'><?php // echo isset($arrSolSubt['05']) ? $arrSolSubt['05'] : '0.00'; ?></td>
	<td align='right' id='liqsubt05'>0.00</td>
	<td><div id='divsubt05'><div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div></div></td>
</tr>-->
<tr>
	<td>05</td>
	<td>Otros</td>
	<td align='right' id='solsubt06'><?php echo isset($arrSolSubt['06']) ? $arrSolSubt['06'] : '0.00'; ?></td>
	<td align='right' id='liqsubt06'>0.00</td>
	<td><div id='divsubt06'><div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div></div></td>
</tr>
</table>

<br>

<div>Detalle de los documentos sustentatorios:</div>
<table border="1" id="doc_sust_detalle">
<tbody id="bole_body">
<tr>
	<td colspan="25">Boletos de Viaje / Pasajes A&eacute;reos</td>
</tr>
<tr>
	<td class="encabezado_h">Concepto</td>
	<td class="encabezado_h" title="Tipo de documento">Tipo Doc</td>
	<td class="encabezado_h">Orden de Trabajo</td>
	<td class="encabezado_h">RUC</td>
	<td class="encabezado_h">Proveedor</td>
	<td class="encabezado_h">Fecha</td>
	<td class="encabezado_h">Serie</td>
	<td class="encabezado_h">Numero</td>
	<td class="encabezado_h" title="Detalle o glosa">Detalle</td>
	<td class="encabezado_h">Moneda</td>
	<td class="encabezado_h">Afecto IGV</td>
	<td class="encabezado_h">Monto Afecto IGV</td>
	<td class="encabezado_h">Monto no Afecto IGV</td>
        <td class="encabezado_h">Monto Otro</td>
        <td class="encabezado_h">Monto ICBP</td>
	<td class="encabezado_h" title="Tipo de cambio">T/C</td>
	<td class="encabezado_h">Conversi&oacute;n Afecto</td>
	<td class="encabezado_h">Conversi&oacute;n no Afecto</td>
        <td class="encabezado_h">Conversi&oacute;n Otro</td>
        <td class="encabezado_h">Conversi&oacute;n ICBP</td>
	<td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
	<td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
	<!--<td class="encabezado_h" title="Distribuci&oacute;n de gasto">Dist de Gasto</td>-->
	<td class="encabezado_h">Aprobar</td>
	<td class="encabezado_h" title="Gasto asumido por ABC MULTISERVICIOS GENERALES S.A.C. (Ingrese valor en moneda del documento)"><span>Gasto Asumido</span></td>
	<td class="encabezado_h">Borrar</td>
</tr>
<?php echo getFilasPrevias($arrLiqDet, $mon_id, '01', 2, 0); ?>
</tbody>

<tr><td colspan="25">&nbsp;</td></tr>

<tbody id="alim_body">
<tr>
	<td colspan="25">Alimentacion / Pension</td>
</tr>
<tr>
	<td class="encabezado_h">Concepto</td>
	<td class="encabezado_h" title="Tipo de documento">Tipo Doc</td>
	<td class="encabezado_h">Orden de Trabajo</td>
	<td class="encabezado_h">RUC</td>
	<td class="encabezado_h">Proveedor</td>
	<td class="encabezado_h">Fecha</td>
	<td class="encabezado_h">Serie</td>
	<td class="encabezado_h">Numero</td>
	<td class="encabezado_h" title="Detalle o glosa">Detalle</td>
	<td class="encabezado_h">Moneda</td>
	<td class="encabezado_h">Afecto IGV</td>
	<td class="encabezado_h">Monto Afecto IGV</td>
	<td class="encabezado_h">Monto no Afecto IGV</td>
        <td class="encabezado_h">Monto Otro</td>
        <td class="encabezado_h">Monto ICBP</td>
	<td class="encabezado_h" title="Tipo de cambio">T/C</td>
	<td class="encabezado_h">Conversi&oacute;n Afecto</td>
	<td class="encabezado_h">Conversi&oacute;n no Afecto</td>
        <td class="encabezado_h">Conversi&oacute;n Otro</td>
        <td class="encabezado_h">Conversi&oacute;n ICBP</td>
	<td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
	<td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
	<!--<td class="encabezado_h" title="Distribuci&oacute;n de gasto (Ingrese valor en moneda del documento)">Dist de Gasto</td>-->
	<td class="encabezado_h">Aprobar</td>
	<td class="encabezado_h" title="Gasto asumido por ABC MULTISERVICIOS GENERALES S.A.C."><span>Gasto Asumido</span></td>
	<td class="encabezado_h">Borrar</td>
</tr>
<?php echo getFilasPrevias($arrLiqDet, $mon_id, '02', 2, 0); ?>
</tbody>

<tr><td colspan="25">&nbsp;</td></tr>

<tbody id="hosp_body">
<tr>
	<td colspan="25">Hospedaje</td>
</tr>
<tr>
	<td class="encabezado_h">Concepto</td>
	<td class="encabezado_h" title="Tipo de documento">Tipo Doc</td>
	<td class="encabezado_h">Orden de Trabajo</td>
	<td class="encabezado_h">RUC</td>
	<td class="encabezado_h">Proveedor</td>
	<td class="encabezado_h">Fecha</td>
	<td class="encabezado_h">Serie</td>
	<td class="encabezado_h">Numero</td>
	<td class="encabezado_h" title="Detalle o glosa">Detalle</td>
	<td class="encabezado_h">Moneda</td>
	<td class="encabezado_h">Afecto IGV</td>
	<td class="encabezado_h">Monto Afecto IGV</td>
	<td class="encabezado_h">Monto no Afecto IGV</td>
        <td class="encabezado_h">Monto Otro</td>
        <td class="encabezado_h">Monto ICBP</td>
	<td class="encabezado_h" title="Tipo de cambio">T/C</td>
	<td class="encabezado_h">Conversi&oacute;n Afecto</td>
	<td class="encabezado_h">Conversi&oacute;n no Afecto</td>
        <td class="encabezado_h">Conversi&oacute;n Otro</td>
        <td class="encabezado_h">Conversi&oacute;n ICBP</td>
	<td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
	<td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
	<!--<td class="encabezado_h" title="Distribuci&oacute;n de gasto (Ingrese valor en moneda del documento)">Dist de Gasto</td>-->
	<td class="encabezado_h">Aprobar</td>
	<td class="encabezado_h" title="Gasto asumido por ABC MULTISERVICIOS GENERALES S.A.C."><span>Gasto Asumido</span></td>
	<td class="encabezado_h">Borrar</td>
</tr>
<?php echo getFilasPrevias($arrLiqDet, $mon_id, '03', 2, 0); ?>
</tbody>

<tr><td colspan="25">&nbsp;</td></tr>

<tbody id="movi_body">
<tr>
	<td colspan="25">Movilidad / Combustible</td>
</tr>
<tr>
	<td class="encabezado_h">Concepto</td>
	<td class="encabezado_h" title="Tipo de documento">Tipo Doc</td>
	<td class="encabezado_h">Orden de Trabajo</td>
	<td class="encabezado_h">RUC</td>
	<td class="encabezado_h">Proveedor</td>
	<td class="encabezado_h">Fecha</td>
	<td class="encabezado_h">Serie</td>
	<td class="encabezado_h">Numero</td>
	<td class="encabezado_h" title="Detalle o glosa">Detalle</td>
	<td class="encabezado_h">Moneda</td>
	<td class="encabezado_h">Afecto IGV</td>
	<td class="encabezado_h">Monto Afecto IGV</td>
	<td class="encabezado_h">Monto no Afecto IGV</td>
        <td class="encabezado_h">Monto Otro</td>
        <td class="encabezado_h">Monto ICBP</td>
	<td class="encabezado_h" title="Tipo de cambio">T/C</td>
	<td class="encabezado_h">Conversi&oacute;n Afecto</td>
	<td class="encabezado_h">Conversi&oacute;n no Afecto</td>
        <td class="encabezado_h">Conversi&oacute;n Otro</td>
        <td class="encabezado_h">Conversi&oacute;n ICBP</td>
	<td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
	<td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
	<!--<td class="encabezado_h" title="Distribuci&oacute;n de gasto (Ingrese valor en moneda del documento)">Dist de Gasto</td>-->
	<td class="encabezado_h">Aprobar</td>
	<td class="encabezado_h" title="Gasto asumido por ABC MULTISERVICIOS GENERALES S.A.C."><span>Gasto Asumido</span></td>
	<td class="encabezado_h">Borrar</td>
</tr>
<?php if(!is_null($pla_id)) echo getFilaPlanillaMovilidad($pla_id, $mon_id, 100); else echo getFilaVaciaPlaMov(2); ?>
<?php echo getFilasPrevias($arrLiqDet, $mon_id, '04', 2, 0); ?>
</tbody>

<!--<tr><td colspan="21">&nbsp;</td></tr>

<tbody id="gast_body">
<tr>
	<td colspan="21">Gastos de Representaci&oacute;n</td>
</tr>
<tr>
	<td class="encabezado_h">Concepto</td>
	<td class="encabezado_h" title="Tipo de documento">Tipo Doc</td>
	<td class="encabezado_h">RUC</td>
	<td class="encabezado_h">Proveedor</td>
	<td class="encabezado_h">Fecha</td>
	<td class="encabezado_h">Serie</td>
	<td class="encabezado_h">Numero</td>
	<td class="encabezado_h" title="Detalle o glosa">Detalle</td>
	<td class="encabezado_h">Moneda</td>
	<td class="encabezado_h">Afecto IGV</td>
	<td class="encabezado_h">Monto Afecto IGV</td>
	<td class="encabezado_h">Monto no Afecto IGV</td>
	<td class="encabezado_h" title="Tipo de cambio">T/C</td>
	<td class="encabezado_h">Conversi&oacute;n Afecto</td>
	<td class="encabezado_h">Conversi&oacute;n no Afecto</td>
	<td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
	<td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
	<td class="encabezado_h" title="Distribuci&oacute;n de gasto (Ingrese valor en moneda del documento)">Dist de Gasto</td>
	<td class="encabezado_h">Aprobar</td>
	<td class="encabezado_h" title="Gasto asumido por ABC MULTISERVICIOS GENERALES S.A.C."><span>Gasto Asumido</span></td>
	<td class="encabezado_h">Borrar</td>
</tr>
<?php // echo getFilasPrevias($arrLiqDet, $mon_id, '05', 2, 0); ?>
</tbody>-->

<tr><td colspan="25">&nbsp;</td></tr>

<tbody id="otro_body">
<tr>
	<td colspan="25">Otros</td>
</tr>
<tr>
	<td class="encabezado_h">Concepto</td>
	<td class="encabezado_h" title="Tipo de documento">Tipo Doc</td>
	<td class="encabezado_h">Orden de Trabajo</td>
	<td class="encabezado_h">RUC</td>
	<td class="encabezado_h">Proveedor</td>
	<td class="encabezado_h">Fecha</td>
	<td class="encabezado_h">Serie</td>
	<td class="encabezado_h">Numero</td>
	<td class="encabezado_h" title="Detalle o glosa">Detalle</td>
	<td class="encabezado_h">Moneda</td>
	<td class="encabezado_h">Afecto IGV</td>
	<td class="encabezado_h">Monto Afecto IGV</td>
	<td class="encabezado_h">Monto no Afecto IGV</td>
        <td class="encabezado_h">Monto Otro</td>
        <td class="encabezado_h">Monto ICBP</td>
	<td class="encabezado_h" title="Tipo de cambio">T/C</td>
	<td class="encabezado_h">Conversi&oacute;n Afecto</td>
	<td class="encabezado_h">Conversi&oacute;n no Afecto</td>
        <td class="encabezado_h">Conversi&oacute;n Otro</td>
        <td class="encabezado_h">Conversi&oacute;n ICBP</td>
	<td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
	<td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
	<!--<td class="encabezado_h" title="Distribuci&oacute;n de gasto (Ingrese valor en moneda del documento)">Dist de Gasto</td>-->
	<td class="encabezado_h">Aprobar</td>
	<td class="encabezado_h" title="Gasto asumido por ABC MULTISERVICIOS GENERALES S.A.C."><span>Gasto Asumido</span></td>
	<td class="encabezado_h">Borrar</td>
</tr>
<?php echo getFilasPrevias($arrLiqDet, $mon_id, '06', 2, 0); ?>
</tbody>
</table>

<br>

<table>
<tr>
	<td align="right">Monto solicitado:</td>
	<td class="calc_span"><span id="mon_sol_s"><?php echo $ear_monto; ?></span><input type="hidden" name="mon_sol" id="mon_sol" value="<?php echo $ear_monto; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto liquidado:</td>
	<td class="calc_span"><span id="tot_mon_liq_s"><?php echo $ear_liq_mon; ?></span><input type="hidden" name="tot_mon_liq" id="tot_mon_liq" value="<?php echo $ear_liq_mon; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Saldo en efectivo:</td>
	<td class="calc_span"><span id="mon_saldo_s"><?php echo $mon_saldo_s; ?></span><input type="hidden" name="mon_saldo" id="mon_saldo" value="<?php echo $mon_saldo_s; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto retenciones efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_ret_s"><?php echo $ear_liq_ret; ?></span><input type="hidden" name="tot_mon_ret" id="tot_mon_ret" value="<?php echo $ear_liq_ret; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto retenciones no efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_ret_no_s"><?php echo $ear_liq_ret_no; ?></span><input type="hidden" name="tot_mon_ret_no" id="tot_mon_ret_no" value="<?php echo $ear_liq_ret_no; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto detracciones efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_det_s"><?php echo $ear_liq_det; ?></span><input type="hidden" name="tot_mon_det" id="tot_mon_det" value="<?php echo $ear_liq_det; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto detracciones no efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_det_no_s"><?php echo $ear_liq_det_no; ?></span><input type="hidden" name="tot_mon_det_no" id="tot_mon_det_no" value="<?php echo $ear_liq_det_no; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto documentos:</td>
	<td class="calc_span"><span id="tot_mon_doc_s"><?php echo $tot_mon_doc_s; ?></span><input type="hidden" name="tot_mon_doc" id="tot_mon_doc" value="<?php echo $tot_mon_doc_s; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total gasto asumido por ABC MULTISERVICIOS GENERALES S.A.C.:</td>
	<td class="calc_span"><span id="tot_mon_gast_asum_s"><?php echo $ear_liq_gast_asum; ?></span><input type="hidden" name="tot_mon_gast_asum" id="tot_mon_gast_asum" value="<?php echo $ear_liq_gast_asum; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total gasto asumido por el colaborador:</td>
	<td class="calc_span"><span id="tot_mon_gast_asum2_s"><?php echo $gast_asum_cola_s; ?></span><input type="hidden" name="tot_mon_gast_asum2" id="tot_mon_gast_asum2" value="<?php echo $gast_asum_cola_s; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Resultado: <span id="resul_msg"><?php echo $resul_msg; ?></span></td>
	<td class="calc_span"><span id="resul_inp_s"><?php echo number_format($resul_inp_s, 2, '.', ''); ?></span><input type="hidden" name="resul_inp" id="resul_inp" value="<?php echo $ear_liq_dcto; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
</table>

<p>Nota: Los montos calculados son referenciales y podr&aacute;n ser reajustados por la administraci&oacute;n.</p>

<?php
if ($est_id>=51 && $est_id<=53) {
	echo "<p><b>Nota: Este EAR ha sido enviado a devoluci&oacute;n por exceso de dias sin enviar liquidacion. Los montos mostrados en esta pagina solo son referenciales.</b></p>";
}
?>

<input type="submit" value="Regresar" name="btn2" id="btn2">

<p>Descripci&oacute;n de los botones:<br>
<b>Regresar</b> a la p&aacute;gina anterior. </p>

</form>

<div id="ruc_nro_template">
<input type="text" class="ruc_nro_i" size="13" maxlength="11" id="ruc_nro[{0}]" name="ruc_nro[{0}]">
</div>

<div id="prov_nom_template">
<div class="prov_nom_i" id="prov_nom[{0}]" name="prov_nom[{0}]"></div>
<input type="hidden" class="prov_ret" value="0">
<input type="hidden" class="prov_act" value="0">
</div>

<div id="dist_gastos" title="Distribuci&oacute;n de Gastos">
	<table>
	<tr>
		<td>Tipo de gasto:</td>
		<td>
			<select id="tg" disabled>
<?php
$arr = getGastosTipos();

foreach ($arr as $v) {
	echo "\t\t\t\t<option value='$v[0]'>$v[1]</option>\n";
}
?>
			</select>
		</td>
	</tr>
	<tr><td>Seleccione:</td><td><select id="dist_gast_lst" disabled></select></td></tr>
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

<div id="bole_conc_template">
<select class="conc_l" id="conc_l[{0}]" name="conc_l[{0}]">
<?php
$arr = getLiqConceptos($mon_id, '01');

foreach ($arr as $v) {
	echo "<option value='$v[0]' conc_id='$v[3]'>$v[1]</option>\n";
}
?>
</select>
<input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
<input type="hidden" class="ret_tasa_inp" name="ret_tasa[{0}]">
<input type="hidden" class="ret_min_monto_inp" name="ret_min_monto[{0}]">
<input type="hidden" class="det_tasa_inp" name="det_tasa[{0}]">
<input type="hidden" class="det_min_monto_inp" name="det_min_monto[{0}]">
</div>

<div id="alim_conc_template">
<select class="conc_l" id="conc_l[{0}]" name="conc_l[{0}]">
<?php
$arr = getLiqConceptos($mon_id, '02');

foreach ($arr as $v) {
	echo "<option value='$v[0]' conc_id='$v[3]'>$v[1]</option>\n";
}
?>
</select>
<input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
<input type="hidden" class="ret_tasa_inp" name="ret_tasa[{0}]">
<input type="hidden" class="ret_min_monto_inp" name="ret_min_monto[{0}]">
<input type="hidden" class="det_tasa_inp" name="det_tasa[{0}]">
<input type="hidden" class="det_min_monto_inp" name="det_min_monto[{0}]">
</div>

<div id="hosp_conc_template">
<select class="conc_l" id="conc_l[{0}]" name="conc_l[{0}]">
<?php
$arr = getLiqConceptos($mon_id, '03');

foreach ($arr as $v) {
	echo "<option value='$v[0]' conc_id='$v[3]'>$v[1]</option>\n";
}
?>
</select>
<input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
<input type="hidden" class="ret_tasa_inp" name="ret_tasa[{0}]">
<input type="hidden" class="ret_min_monto_inp" name="ret_min_monto[{0}]">
<input type="hidden" class="det_tasa_inp" name="det_tasa[{0}]">
<input type="hidden" class="det_min_monto_inp" name="det_min_monto[{0}]">
</div>

<div id="movi_conc_template">
<select class="conc_l" id="conc_l[{0}]" name="conc_l[{0}]">
<?php
$arr = getLiqConceptos($mon_id, '04');

foreach ($arr as $v) {
	echo "<option value='$v[0]' conc_id='$v[3]'>$v[1]</option>\n";
}
?>
</select>
<input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
<input type="hidden" class="ret_tasa_inp" name="ret_tasa[{0}]">
<input type="hidden" class="ret_min_monto_inp" name="ret_min_monto[{0}]">
<input type="hidden" class="det_tasa_inp" name="det_tasa[{0}]">
<input type="hidden" class="det_min_monto_inp" name="det_min_monto[{0}]">
</div>

<div id="gast_conc_template">
<select class="conc_l" id="conc_l[{0}]" name="conc_l[{0}]">
<?php
$arr = getLiqConceptos($mon_id, '05');

foreach ($arr as $v) {
	echo "<option value='$v[0]' conc_id='$v[3]'>$v[1]</option>\n";
}
?>
</select>
<input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
<input type="hidden" class="ret_tasa_inp" name="ret_tasa[{0}]">
<input type="hidden" class="ret_min_monto_inp" name="ret_min_monto[{0}]">
<input type="hidden" class="det_tasa_inp" name="det_tasa[{0}]">
<input type="hidden" class="det_min_monto_inp" name="det_min_monto[{0}]">
</div>

<div id="otro_conc_template">
<select class="conc_l" id="conc_l[{0}]" name="conc_l[{0}]">
<?php
$arr = getLiqConceptos($mon_id, '06');

foreach ($arr as $v) {
	echo "<option value='$v[0]' conc_id='$v[3]'>$v[1]</option>\n";
}
?>
</select>
<input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
<input type="hidden" class="ret_tasa_inp" name="ret_tasa[{0}]">
<input type="hidden" class="ret_min_monto_inp" name="ret_min_monto[{0}]">
<input type="hidden" class="det_tasa_inp" name="det_tasa[{0}]">
<input type="hidden" class="det_min_monto_inp" name="det_min_monto[{0}]">
</div>

<div id="tipo_doc_template">
<select class="tipo_doc" id="tipo_doc[{0}]" name="tipo_doc[{0}]">
<?php
$arr = getTipoDoc();

foreach ($arr as $v) {
	echo "<option value='$v[0]' rucreq='$v[2]' aplret='$v[3]' apldet='$v[4]' taxcode='$v[8]'".($v[0]=="2"?" selected":"").">$v[1]</option>\n";
}
?>
</select>
</div>

<div id="tipo_mon_template">
<select class="tipo_mon" id="tipo_mon[{0}]" name="tipo_mon[{0}]">
<?php
$arr = getTipoMon();

foreach ($arr as $v) {
	echo "<option value='$v[0]'".($mon_id==$v[0]?" selected":"").">$v[1]</option>\n";
}
?>
</select>
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

<?php include ("footer.php"); ?>
</body>
</html>
