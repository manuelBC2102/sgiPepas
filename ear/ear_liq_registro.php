<?php
header('Content-Type: text/html; charset=UTF-8');
include("seguridad.php");
include 'func.php';
include 'parametros.php';

//$isDua = ($_SESSION['rec_usu_id'] == $pAXISADUANA || $_SESSION['rec_usu_id'] == $pAXISGLOBAL);
//VALIDAR SI TIENE COMO PERFIL PROVEEDOR EAR PARA QUE PUEDA SELECCIONAR LA DUA
$contadorPerfil = obtenerPerfilContador($pPERFIL_PROVEEDOR_DUA, $_SESSION['rec_usu_id']);
//$isDua=($contadorPerfil>0);
$isDua = false;

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
  echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
  exit;
} else {
  $id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

list(
  $ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
  $ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
  $usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
  $ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
  $ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
  $master_usu_id, $comodin1, $comodin2, $dua_id, $tipoCambioFechaLiq, $guardarTcSgi,
  $periodo_id, $dua_serie, $dua_numero
) = getSolicitudInfo($id);
$arrSolSubt = getSolicitudSubtotales($id);
$arrLiqDet = getLiqDetalle($id);
$arrDistribucionDetalle = getDistribucionDetalle($id);
$pla_exc = 0;
if (!is_null($pla_id)) {
  list(
    $pla_numero, $est_id_2, $pla_reg_fec, $ear_numero_2, $tope_maximo, $usu_id_2, $ear_id,
    $est_nom_2, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec,
    $pla_exc, $pla_com1, $pla_com2, $pla_com3, $pla_com4, $pla_com5, $pla_com6, $orden_trabajo_id
  ) = getPlanillaMovilidadInfo($pla_id);
}

$mon_saldo_s = number_format($ear_monto - $ear_liq_mon, 2, '.', '');
$tot_mon_doc_s = number_format($ear_liq_mon + $ear_liq_ret + $ear_liq_det, 2, '.', '');
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
    $resul_inp_s = $ear_liq_dcto * -1;
    break;
}

if ($est_id <> 4) {
  echo "<font color='red'><b>ERROR: No se puede modificar la liquidaci&oacute;n de esta solicitud</b></font><br>";
  exit;
}

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'JEFEOGERENTE');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pTI);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pADMINIST);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pGERENTE);
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], $pASISTENTE_ADMINISTRATIVO);
if ($usu_id != $_SESSION['rec_usu_persona_id'] && $count == 0) {
  echo "<font color='red'><b>ERROR: No se puede acceder a la informaci&oacute;n de la liquidaci&oacute;n</b></font><br>";
  exit;
}

$sol_msj = "";
if (!is_null($usu_act)) {
  $sol_msj = " por " . $usu_act . " el " . $ear_act_fec;
}
if (!is_null($ear_act_motivo)) {
  $sol_msj .= " (Motivo: " . $ear_act_motivo . ")";
}

$rec_usu_nombre = getUsuarioNombre($usu_id);
$adm_usu_gco_cobj = ''; //$adm_usu_gco_cobj = getUsuGcoObj($usu_id);
list($lid_gti_def, $lid_dg_json_def) = getDistGastDefault($id, $rec_usu_nombre, $adm_usu_gco_cobj);

list($alim_dias, $alim_monto) = getSolicitudAlimDiasTope($id);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=1312" />
  <!--<title>Registrar Liquidaci&oacute;n EAR - Administraci&oacute;n - Minapp</title>-->
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
  <link href="js/jquery-ui-1.9.2/themes/ui-lightness/jquery-ui.css" rel="stylesheet" />
  <script type="text/javascript" language="javascript" src="js/jquery.dataTables.min.js"></script>
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

      $("#fecha_orden").datepicker({
        numberOfMonths: 2,
        altField: "#fecha_orden2",
        altFormat: "yy-mm-dd",
        showButtonPanel: true,   // Muestra el panel de botones
        changeMonth: true,       // Permite cambiar el mes
        changeYear: true,        // Permite cambiar el año
        yearRange: '1900:2050'
      });
      $("#fecha_orden").datepicker($.datepicker.regional["es"]);
      $("#fecha_orden").datepicker("option", "dateFormat", "yy-mm-dd");
      $("#fecha_orden").datepicker("option", "dateFormat", "D, d M yy");
    });
  </script>

  <script>
    const opAfectoIGV = [
      { id: 1, descripcion: 'Si 18%', igv: 18},
      { id: 2, descripcion: 'Si 10%', igv: 10},
      { id: 3, descripcion: 'No', igv: 0},
      { id: 4, descripcion: 'Mixto 18%', igv: 18},
      { id: 5, descripcion: 'Mixto 10%', igv: 10}
    ];

    function formatearFecha(s) {
      var ss = (s.split('-'));
      var y = parseInt(ss[0], 10);
      var m = parseInt(ss[1], 10);
      var d = parseInt(ss[2].substr(0, 2), 10);
      return (d < 10 ? ('0' + d) : d) + '/' + (m < 10 ? ('0' + m) : m) + '/' + y;
    }

    function onChangeDua() {
      //ELIMINO LOS DOCUMENTOS RECIBO DE GASTO
      $("#otro_body .fila_dato .tipo_doc").each(function() {
        if ($(this).val() * 1 == 14) {
          $(this).parent().parent().remove();
          $(this).ret_det_recalc($(this).parent().parent());
        }
      });

      var conceptoId = 58;
      var documentoTipoId = 14;

      var duaId = document.getElementById('dua').value;

      $.when(
        $.getJSON('dua_obtener_otros_gastos_igv.php', {
          duaIdDoc: duaId
        }, function(data) {
          // console.log(data);
          if (data.fecha_emision != '') {
            otrosGastosReciboGastos(
              conceptoId,
              documentoTipoId,
              formatearFecha(data.fecha_emision),
              data.tipo_cambio,
              'IGV',
              data.igv_soles
            );
          }
        }),
        $.getJSON('dua_obtener_otros_gastos_advalorem.php', {
          duaIdDoc: duaId
        }, function(data) {
          // console.log(data);
          if (data.fecha_emision != '' && data.importe * 1 != 0) {
            otrosGastosReciboGastos(conceptoId,
              documentoTipoId,
              formatearFecha(data.fecha_emision),
              data.tipo_cambio,
              'AD VALOREM',
              data.importe);
          }
        }),
        $.getJSON('dua_obtener_otros_gastos_percepcion.php', {
          duaIdDoc: duaId
        }, function(data) {
          // console.log(data);
          if (data.fecha_emision != '') {
            otrosGastosReciboGastos(
              conceptoId,
              documentoTipoId,
              formatearFecha(data.fecha_emision),
              data.tipo_cambio,
              'PERCEPCION',
              data.importe
            );
          }
        })
      );
    }

    function otrosGastosReciboGastos(conceptoId, documentoTipoId, fechaEmision, tipoCambio, detalle, montoNoAfecto) {
      if (tipoCambio == '' || tipoCambio == null) {
        tipoCambio = tc_hoy;
      }
      montoNoAfecto = (montoNoAfecto * 1).toFixed(2);

      var veh_t = $('#veh_template').html();

      var s_fecsernumdet = '<td class="fec_doc_td"><input type="text" value="' + fechaEmision + '" size="11" maxlength="10" class="fecha_inp" readonly name="fec_doc[{0}]"></td>'; //Fecha
      s_fecsernumdet += '<td class="ser_doc_td"><input type="text" value="0" size="6" maxlength="5" class="ser_doc_inp" name="ser_doc[{0}]"></td>'; //Serie
      s_fecsernumdet += '<td class="num_doc_td"><input type="text" value="0" size="9" maxlength="15" class="num_doc_inp" name="num_doc[{0}]"></td>'; //Numero
      s_fecsernumdet += '<td class="det_doc_td">' + veh_t + '<input type="text" value="' + detalle + '" size="14" maxlength="200" class="det_doc_inp" name="det_doc[{0}]"></td>'; //Detalle

      var s_afeconretdet = '<td class="afecto_sel_td"><select id="afecto_sel[{0}]" class="afecto_sel" name="afecto_sel[{0}]">';
      opAfectoIGV.map(opcion => {
        s_afeconretdet += `<option value="${opcion.id}">${opcion.descripcion}</option>`;
      });
      s_afeconretdet += '</select></td>'; //Afecto

      s_afeconretdet += '<td class="afecto_td"><input type="text" value="0" size="8" maxlength="9" id="afecto_inp[{0}]" class="afecto_inp" name="afecto_inp[{0}]"></td>'; //Monto Afecto
      s_afeconretdet += '<td class="noafecto_td"><input type="text" value="' + montoNoAfecto + '" size="8" maxlength="9" id="noafecto_inp[{0}]" class="noafecto_inp" name="noafecto_inp[{0}]" style="display: none;"></td>'; //Monto NoAfecto
      s_afeconretdet += '<td class="montOtro_td"><input type="text" value="0" size="8" maxlength="9" id="montOtro_inp[{0}]" class="montOtro_inp" name="montOtro_inp[{0}]"></td>'; //Monto Otros
      s_afeconretdet += '<td class="montIcbp_td"><input type="text" value="0" size="8" maxlength="9" id="montIcbp_inp[{0}]" class="montIcbp_inp" name="montIcbp_inp[{0}]"></td>'; //Monto ICBP
      s_afeconretdet += '<td class="tc_td" ><div class="tc_div" style="display: none;"></div><input size="6" maxlength="9" class="tc_inp" name="tc_inp[{0}]" value="' + tipoCambio + '"></td>'; //T/C;
      s_afeconretdet += '<td class="conv_afecto_td"><input type="text" value="" size="8" maxlength="9" id="conv_afecto_inp[{0}]" class="conv_afecto_inp" name="conv_afecto_inp[{0}]" style="display: none;" readonly></td>'; //Conversion Afecto
      s_afeconretdet += '<td class="conv_noafecto_td" ><input type="text" value="" size="8" maxlength="9" id="conv_noafecto_inp[{0}]" class="conv_noafecto_inp" name="conv_noafecto_inp[{0}]" style="display: none;" readonly></td>'; //Conversion NoAfecto
      s_afeconretdet += '<td class="conv_otro_td"><input type="text" value="" size="8" maxlength="9" id="conv_otro_inp[{0}]" class="conv_otro_inp" name="conv_otro_inp[{0}]" style="display: none;" readonly></td>'; //Conversion Otros
      s_afeconretdet += '<td class="conv_icbp_td"><input type="text" value="" size="8" maxlength="9" id="conv_icbp_inp[{0}]" class="conv_icbp_inp" name="conv_icbp_inp[{0}]" style="display: none;" readonly></td>'; //Conversion ICBP
      s_afeconretdet += '<td class="aplic_retdet_td"><select class="aplic_retdet" name="aplic_retdet[{0}]"><option value="1">Si</option><option value="0">No</option></select></td>'; //Ret/Det
      s_afeconretdet += '<td class="retdet_td"><div class="retdet_div"></div><input type="hidden" class="retdet_tip" name="retdet_tip[{0}]" value="0"><input type="hidden" class="retdet_monto" name="retdet_monto[{0}]" value="0"></td>'; //Monto Ret:Det
      // s_afeconretdet += "<td class='monto_igv_td'><input type='hidden' value='18' id='monto_igv[$unique_id]' name='monto_igv[$unique_id]' readonly></td>"; //Monto IGV

      var $tbl = $('#otro_body tr:last');

      var ruc_nro_t = $('#ruc_nro_template').html();
      var prov_nom_t = $('#prov_nom_template').html();
      var conc_t = $('#otro_conc_template_dua').html();
      var tipo_doc_t = $('#tipo_doc_template').html();
      var tipo_mon_t = $('#tipo_mon_template').html();
      var dele = '<img src="img/delete.png" class="dele" title="Borrar">';

      var s_add = '<tr class="fila_dato">';
      s_add += '<td class="conc_td">' + conc_t + '</td>'; //Concepto
      s_add += '<td class="tipo_doc_td">' + tipo_doc_t + '</td>'; //Tipo Doc
      s_add += '<td class="ruc_nro_td">' + ruc_nro_t + '</td><td class="prov_nom_td">' + prov_nom_t + '</td>'; //RUC y Nombre Proveedor
      s_add += s_fecsernumdet;
      s_add += '<td class="tipo_mon_td">' + tipo_mon_t + '</td>'; //Moneda
      s_add += s_afeconretdet;
      // s_add += '<td>'+dist_gast+'</td>';
      s_add += '<td>' + dele + '</td>';
      s_add += '</tr>';

      var template = jQuery.validator.format(s_add);

      $tbl.after($(template(i++)));

      document.getElementById('tipo_doc[' + (i - 1) + ']').value = documentoTipoId;
      document.getElementById('tipo_mon[' + (i - 1) + ']').value = 1; //TIPO DE MONEDA EN SOLES
      // document.getElementById('conc_l['+(i-1)+']').value=conceptoId;

      $tbl = $('#otro_body tr:last');
      $tbl.children('.afecto_td').children('.afecto_inp').rules('add', {
        min: 0.01
      });
      $tbl.children('.noafecto_td').children('.noafecto_inp').rules('add', {
        min: 0.01
      });
      $tbl.children('.montOtro_td').children('.montOtro_inp').rules('add', {
        // min: 0.01,
        required: false
      });
      $tbl.children('.montIcbp_td').children('.montIcbp_inp').rules('add', {
        // min: 0.01,
        required: false
      });
      $tbl.children('.tc_td').children('.tc_inp').rules('add', {
        min: 0.01
      });
      $tbl.children('.ser_doc_td').children('.ser_doc_inp').rules('add', {
        required: true
      });
      $tbl.children('.num_doc_td').children('.num_doc_inp').rules('add', {
        required: true
      });
      $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
        required: true
      });
      $tbl.children('.det_doc_td').children('.km_span').children('.km_inp').rules('add', {
        required: true,
        digits: true,
        min: 1
      });
      $tbl.children('.det_doc_td').children('.peaje_span').children('.peaje_inp').rules('add', {
        required: true
      });

      var conc_id = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('conc_id');
      $tbl.children('.conc_td').children('.conc_id_inp').val(conc_id);

      var cve = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('cve');
      $tbl.children('.conc_td').children('.cve_inp').val(cve);
      if (cve == 0 || cve == -100) {
        $tbl.children('.det_doc_td').children('.veh_l').hide();
        $tbl.children('.det_doc_td').children('.km_span').hide();
        $tbl.children('.det_doc_td').children('.peaje_span').hide();
      } else if (cve == 1) {
        $tbl.children('.det_doc_td').children('.det_doc_inp').hide();
        $tbl.children('.det_doc_td').children('.peaje_span').hide();
      } else if (cve == 2) {
        $tbl.children('.det_doc_td').children('.km_span').hide();
      } else if (cve == 3) {
        $tbl.children('.det_doc_td').children('.km_span').hide();
        $tbl.children('.det_doc_td').children('.peaje_span').hide();
      } else if (cve == 4) {
        $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
          validaslash: true
        });
        $tbl.children('.det_doc_td').children('.veh_l').hide();
        $tbl.children('.det_doc_td').children('.km_span').hide();
        $tbl.children('.det_doc_td').children('.peaje_span').hide();
      }

      $tbl.children('.ruc_nro_td').children('.ruc_nro_i').focus();

      //TIPO TAXCODE 6
      $tbl.children('.afecto_sel_td').children('.afecto_sel').hide();
      $tbl.children('.afecto_sel_td').children('.afecto_sel').val(2);
      $tbl.children('.afecto_td').children('.afecto_inp').hide();
      $tbl.children('.afecto_td').children('.afecto_inp').val('');
      $tbl.children('.noafecto_td').children('.noafecto_inp').show();
      // $tbl.children('.noafecto_td').children('.noafecto_inp').val('');
      //FIN TIPO TAXCODE 6

      $(this).ret_det_recalc($tbl);
      // $(this).tc_afec_redraw($tbl);
      // PARA HABILITAR O DESHABILITAR LAS CONVERSIONES igual a tc_afec_redraw pero sin tipo cambio
      var fila = $tbl;
      var mon_id_sel = parseInt(fila.children('.tipo_mon_td').children('.tipo_mon').val());
      var afecto_sel = parseInt(fila.children('.afecto_sel_td').children('.afecto_sel').val());
      fila.children('.conv_afecto_td').children('.conv_afecto_inp').hide();
      fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').hide();
      fila.children('.conv_otro_td').children('.conv_otro_inp').hide();
      switch (afecto_sel) {
        case 1:
        case 2:
          fila.children('.afecto_td').children('.afecto_inp').show();
          fila.children('.noafecto_td').children('.noafecto_inp').hide();
          fila.children('.noafecto_td').children('.noafecto_inp').val('');
          fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').val('');
          fila.children('.montOtro_td').children('.montOtro_inp').show();
          fila.children('.montIcbp_td').children('.montIcbp_inp').show();
          if (mon_id_sel != mon_id) {
            fila.children('.conv_afecto_td').children('.conv_afecto_inp').show();
            fila.children('.conv_otro_td').children('.conv_otro_inp').show();
            fila.children('.conv_icbp_td').children('.conv_icbp_inp').show();
          }
          break;
        case 3:
          fila.children('.afecto_td').children('.afecto_inp').hide();
          fila.children('.afecto_td').children('.afecto_inp').val('');
          fila.children('.conv_afecto_td').children('.conv_afecto_inp').val('');
          fila.children('.noafecto_td').children('.noafecto_inp').show();
          fila.children('.montOtro_td').children('.montOtro_inp').show();
          fila.children('.montIcbp_td').children('.montIcbp_inp').show();
          if (mon_id_sel != mon_id) {
            fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').show();
            fila.children('.conv_otro_td').children('.conv_otro_inp').show();
            fila.children('.conv_icbp_td').children('.conv_icbp_inp').show();
          }
          break;
        case 4:
        case 5:
          fila.children('.afecto_td').children('.afecto_inp').show();
          fila.children('.noafecto_td').children('.noafecto_inp').show();
          fila.children('.montIcbp_td').children('.montIcbp_inp').show();
          if (mon_id_sel != mon_id) {
            fila.children('.conv_afecto_td').children('.conv_afecto_inp').show();
            fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').show();
            fila.children('.conv_otro_td').children('.conv_otro_inp').show();
            fila.children('.conv_icbp_td').children('.conv_icbp_inp').show();
          }
          break;
      }
      // FIN PARA HABILITAR O DESHABILITA
    }

    function onChangeCheckTipoCambio() {
      var checked = document.getElementById('chkTipoCambio').checked;
      if (!checked) {
        $("#txtTipoCambioLiq").prop("disabled", true);
      } else {
        $("#txtTipoCambioLiq").prop("disabled", false);
      }
    }

    function obtenerTipoCambioXFechaLiq() {
      var fechaLiq = $('#fecha_liquidacion').val();
      var fecArr = fechaLiq.split('/');
      var fechaLiqForm = fecArr[2] + '-' + fecArr[1] + '-' + fecArr[0];

      // $('#txtTipoCambioLiq').val(4.0);
      $.when(
        $.getJSON('tipo_cambio.php', {
          fec: fechaLiqForm
        }, function(data) {
          if (data.tc_precio == -1) {
            $('#txtTipoCambioLiq').val('');
          } else {
            $('#txtTipoCambioLiq').val(data.tc_precio);
          }
        })
      );
    }
    var bandera_revision_datos = false;
    $(document).ready(function() {
      $('#fecha_liquidacion').datepicker({
        numberOfMonths: 2,
        maxDate: 0
        // minDate: 0,
        // maxDate: 30
      });


      i = 1;
      is_dua = <?php echo ($isDua ? 1 : 0); ?>;
      mon_id = <?php echo $mon_id; ?>;
      fec_hoy = '<?php echo date('d/m/Y'); ?>';
      pla_exc = <?php echo $pla_exc; ?>;
      dg_header = 0;
      tc_hoy = <?php echo getTipoCambio(2, date('Y-m-d')); ?>;

      var dist_gast = '<?php echo str_replace("'", '"', str_replace("\n", "", str_replace('"', '&quot;', substr(getDistGastTemplate($lid_gti_def, $lid_dg_json_def, "{0}", 1), 6, -8)))); ?> ';

      $.fn.checktopes_redraw = function() {
        var alim_dias = <?php echo $alim_dias; ?>;
        var tope = <?php echo $alim_monto; ?>;
        var fecha = "";
        var afecto = 0.00;
        var noafecto = 0.00;
        var otro = 0.00;
        var monto = 0.00;
        var fila;
        var html = "";
        var arr_fecha = new Array();
        var total = 0.00;
        var tipo_doc = 0;
        $('#advertencia2').hide();

        // Primera barrida, suma los montos de acuerdo a la fecha, y muestra los mensajes de estado de acuerdo al monto ingresado
        $('#alim_body .fecha_inp').each(function() {
          fecha = $(this).val();
          fila = $(this).parent().parent();
          tipo_doc = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('value'));
          afecto = parseFloat(fila.children('.conv_afecto_td').children('.conv_afecto_inp').val()) || 0;
          noafecto = parseFloat(fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').val()) || 0;
          // console.log(noafecto);
          otro = parseFloat(fila.children('.conv_otro_td').children('.conv_otro_inp').val()) || 0;
          // monto = afecto + noafecto-4;
          let factor = (tipo_doc == "19" ? -1 : 1);
          // consola.log(tipo_doc);
          monto = factor * (afecto + noafecto + otro);
          if (fecha.length == 10 && monto > 0) {
            if (typeof arr_fecha[fecha] == 'undefined') {
              arr_fecha[fecha] = monto;
            } else {
              arr_fecha[fecha] += monto;
            }
            total += monto;
          }
          fila.css('background-color', '#FFFFFF');
        });

        // Segunda barrida, pinta de colores los recuadros donde corresponda cuando la suma de la fecha exceda el tope
        $('#alim_body .fecha_inp').each(function() {
          fecha = $(this).val();
          fila = $(this).parent().parent();
          if (arr_fecha[fecha] > tope) {
            fila.css('background-color', '#FFCC66');
            $('#advertencia2').show();
          }
        });
      };

      $(document).on('keyup keypress', 'form input[type="text"]', function(e) {
        if (e.which == 13) {
          e.preventDefault();
          return false;
        }
      });

      $('#sol_via_detalle_btn').click(function() {
        if ($(this).attr('src') === 'img/plus.png') {
          $('#sol_via_detalle_tbl').show();
        } else {
          $('#sol_via_detalle_tbl').hide();
        }

        var src = ($(this).attr('src') === 'img/plus.png') ?
          'img/minus.png' :
          'img/plus.png';
        $(this).attr('src', src);

        var title = ($(this).attr('title') === 'Mostrar') ?
          'Ocultar' :
          'Mostrar';
        $(this).attr('title', title);
      });

      $("#dist_gastos").dialog({
        autoOpen: false,
        height: 600,
        width: 800,
        modal: true,
        buttons: {
          "Copiar dist a todos los doc": function() {
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

            $('.dist_gast_tipo').each(function() {
              $(this).attr('src', gast_img);
              $(this).attr('title', gast_img_tooltip);

              $(this).parent().children('.dist_gast_info').attr('title', gast_info_tooltip);

              $(this).parent().children('.gti_id_i').attr('value', gast_tipo);
              $(this).parent().children('.dist_gast_json_i').attr('value', json_str);
            });

            // Cambia la distribucion por defecto del formulario, aplica para nuevos ingresos de documentos
            dist_gast = '<img src="img/modal.gif" class="modal" title="Abrir Distribuci&oacute;n de Gastos"> ';
            dist_gast += '<img src="' + gast_img + '" class="dist_gast_tipo" title="' + gast_img_tooltip + '"> ';
            dist_gast += '<img src="img/info.gif" class="dist_gast_info" title="' + gast_info_tooltip + '">';
            dist_gast += '<input type="hidden" class="gti_id_i" id="gti_id[{0}]" name="gti_id[{0}]" value="' + gast_tipo + '">';
            dist_gast += '<input type="hidden" class="dist_gast_json_i" id="dist_gast_json[{0}]" name="dist_gast_json[{0}]" value="' + json_str.replace(/"/g, "&quot;") + '">';
            $('#lid_gti_def').attr('value', gast_tipo);
            $('#lid_dg_json_def').attr('value', json_str);
            //

            $(this).dialog("close");
          },
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

            // Cambia la distribucion por defecto del formulario, aplica para nuevos ingresos de documentos
            if (dg_header == 1) {
              dist_gast = '<img src="img/modal.gif" class="modal" title="Abrir Distribuci&oacute;n de Gastos"> ';
              dist_gast += '<img src="' + gast_img + '" class="dist_gast_tipo" title="' + gast_img_tooltip + '"> ';
              dist_gast += '<img src="img/info.gif" class="dist_gast_info" title="' + gast_info_tooltip + '">';
              dist_gast += '<input type="hidden" class="gti_id_i" id="gti_id[{0}]" name="gti_id[{0}]" value="' + gast_tipo + '">';
              dist_gast += '<input type="hidden" class="dist_gast_json_i" id="dist_gast_json[{0}]" name="dist_gast_json[{0}]" value="' + json_str.replace(/"/g, "&quot;") + '">';
              $('#lid_gti_def').attr('value', gast_tipo);
              $('#lid_dg_json_def').attr('value', json_str);
            }
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

      $('#liquidacion').on('click', '.modal', function() {
        dg_header = 0;
        $("#dist_gastos").data('dist_gast_node', $(this).parent()).dialog("open");
        $('#tg').change();
      });

      $('#dist_gast_msj').on('click', '.modal', function() {
        dg_header = 1;
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
      $('#doc_sust_detalle').on('change', '.tipo_doc', function() {
        var fila = $(this).parent().parent();
        var taxcode = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('taxcode'));
        var apliDistribucion = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('apldistri'));
        var id = fila.children('.tipo_doc_td').children('.tipo_doc').attr('id');
        var numeroFila = id.replace('tipo_doc', '');
        if (apliDistribucion == 0) {
          $("input[name='lid_distribucion" + numeroFila + "']").val("");
          $("img[name='op_distribucion" + numeroFila + "']").hide();
        } else {
          $("img[name='op_distribucion" + numeroFila + "']").show();
        }

        if (taxcode == 3) {
          fila.children('.afecto_sel_td').children('.afecto_sel').show();
          fila.children('.montOtro_td').children('.montOtro_inp').show();
          fila.children('.montOtro_td').children('.montOtro_inp').val();
          fila.children('.montIcbp_td').children('.montIcbp_inp').show();
          fila.children('.montIcbp_td').children('.montIcbp_inp').val();
          fila.children('.afecto_sel_td').children('.afecto_sel').val(1);
          $(this).tc_afec_redraw(fila);
        } else if (taxcode == 6) {
          fila.children('.afecto_sel_td').children('.afecto_sel').hide();
          fila.children('.afecto_sel_td').children('.afecto_sel').val(3);
          fila.children('.afecto_td').children('.afecto_inp').hide();
          fila.children('.afecto_td').children('.afecto_inp').val('');
          fila.children('.noafecto_td').children('.noafecto_inp').show();
          fila.children('.noafecto_td').children('.noafecto_inp').val('0');
          fila.children('.montOtro_td').children('.montOtro_inp').hide();
          fila.children('.montOtro_td').children('.montOtro_inp').val('');
          fila.children('.montIcbp_td').children('.montIcbp_inp').hide();
          fila.children('.montIcbp_td').children('.montIcbp_inp').val('');
          fila.children('.monto_igv_td').children('.monto_igv').val(0);

          $(this).ret_det_recalc(fila);
        } else {
          fila.children('.afecto_sel_td').children('.afecto_sel').hide();
          fila.children('.afecto_sel_td').children('.afecto_sel').val(1);
          fila.children('.afecto_td').children('.afecto_inp').show();
          fila.children('.noafecto_td').children('.noafecto_inp').hide();
          fila.children('.noafecto_td').children('.noafecto_inp').val('');
          fila.children('.montOtro_td').children('.montOtro_inp').hide();
          fila.children('.montOtro_td').children('.montOtro_inp').val('');
          fila.children('.montIcbp_td').children('.montIcbp_inp').hide();
          fila.children('.montIcbp_td').children('.montIcbp_inp').val('');

          $(this).ret_det_recalc(fila);
        }
        //CAMBIAR TAMAÑO MAXIMO DE NUMERO A 15 CARACTERES:
        var tipoDocId = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').val());
        if (tipoDocId == 11) {
          fila.children('.num_doc_td').children('.num_doc_inp').attr("maxlength", 15);
        }
      });

      $('#doc_sust_detalle').on('change', '.ruc_nro_i', function() {
        var ruc_nro = $(this).val();
        var prov_nom_id = $(this).parent().parent().children('.prov_nom_td').children('.prov_nom_i');
        var prov_ret = $(this).parent().parent().children('.prov_nom_td').children('.prov_ret');
        var prov_act = $(this).parent().parent().children('.prov_nom_td').children('.prov_act');
        var fila = $(this).parent().parent();

        prov_nom_id.html('<b>Cargando</b> <img src="img/loading.gif">');

        $.when(
          $.getJSON('ruc_validador.php', {
            ruc_nro: ruc_nro
          }, function(data) {
            var prov_msj;

            if (data.ruc_act == -1) {
              prov_msj = "<font color='red'><i>ERROR: RUC no existe. Verificar o cambiar el tipo de documento a Recibo de gastos.</i></font>";
            } else if (data.ruc_act == 0) {
              prov_msj = "<font color='red'><i>ERROR: RUC de " + data.prov_nom + " no esta ACTIVO. Debe cambiar el tipo de documento a Otros.</i></font>";
            } else {
              if (data.ruc_hab == 0) {
                prov_msj = data.prov_nom + " <img src='img/alert.png' title='RUC NO HABIDO' class='iconos'>";
              } else {
                prov_msj = data.prov_nom;
              }
            }
            prov_nom_id.html(prov_msj);
            prov_ret.val(data.ruc_ret);
            prov_act.val(data.ruc_act);
          })
        ).then(function() {
          $(document).ret_det_recalc(fila);
        });
      });

      $('#doc_sust_detalle').on('change', '.tipo_mon', function() {
        $(this).tc_afec_redraw($(this).parent().parent());
      });

      $('#doc_sust_detalle').on('change', '.afecto_sel', function() {
        const thisElement = this.getAttribute('name');
        const numRow = thisElement.split('[')[1].split(']')[0];

        $(this).tc_afec_redraw($(this).parent().parent());
        // $(this).ret_det_recalc($(this).parent().parent());

        const montoIGVElement = document.getElementById(`monto_igv[${numRow}]`);
        montoIGVElement.value = opAfectoIGV.filter(op => op.id == this.value)[0].igv;
      });

      $('#doc_sust_detalle').on('change', '.afecto_inp', function() {
        //                        $(this).tc_afec_redraw($(this).parent().parent());
        $(this).ret_det_recalc($(this).parent().parent());
      });

      $('#doc_sust_detalle').on('change', '.noafecto_inp', function() {
        //                        $(this).tc_afec_redraw($(this).parent().parent());
        $(this).ret_det_recalc($(this).parent().parent());
      });

      $('#doc_sust_detalle').on('change', '.montOtro_inp', function() {
        //                        $(this).tc_afec_redraw($(this).parent().parent());
        $(this).ret_det_recalc($(this).parent().parent());
      });

      $('#doc_sust_detalle').on('change', '.montIcbp_inp', function() {
        //                        $(this).tc_afec_redraw($(this).parent().parent());
        $(this).ret_det_recalc($(this).parent().parent());
      });

      $('#doc_sust_detalle').on('change', '.tc_inp', function() {
        //                        $(this).tc_afec_redraw($(this).parent().parent());
        $(this).ret_det_recalc($(this).parent().parent());
      });
      $('#doc_sust_detalle').on('change', '.aplic_retdet', function() {
        //                        $(this).tc_afec_redraw($(this).parent().parent());
        $(this).ret_det_recalc($(this).parent().parent());
      });
      $('#doc_sust_detalle').on('focus', ".fecha_inp", function() {
        $(this).datepicker({
          numberOfMonths: 2,
          maxDate: 0
        });
      });
      $('#doc_sust_detalle').on('change', ".fecha_inp", function() {
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
          $.getJSON('tipo_cambio.php', {
            fec: fec_doc
          }, function(data) {
            if (data.tc_precio == -1) {
              tc_div.html('Error <img src="img/error.png" title="No se encontr&oacute; el tipo de cambio\npara el dia seleccionado,\nnotificar a Contabilidad.\nDe lo contrario no podr&oacute;\ncompletar el registro." class="iconos">');
            } else {
              if (mon_id_sel != mon_id) {
                tc_div.text(data.tc_precio);
              } else {
                tc_div.text('');
              }
            }
            tc_inp.val(data.tc_precio);
          }),
          $.getJSON('ret_det.php', {
            conc_id: conc_id,
            fec: fec_doc
          }, function(data) {
            ret_tasa_inp.val(data.ret_tasa);
            ret_min_monto_inp.val(data.ret_minmonto);
            det_tasa_inp.val(data.det_tasa);
            det_min_monto_inp.val(data.det_minmonto);
          })
        ).then(function() {
          $(document).ret_det_recalc(fila);
        });
      });

      $('#doc_sust_detalle').on('change', '.conc_l', function() {
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

        var cve = $(this).children('option:selected').attr('cve');
        fila.children('.conc_td').children('.cve_inp').val(cve);
        fila.children('.det_doc_td').children('.det_doc_inp').rules('remove', 'validaslash');
        if (cve == 0 || cve == -100) {
          // Glosa normal
          fila.children('.det_doc_td').children('.veh_l').hide();
          fila.children('.det_doc_td').children('.km_span').hide();
          fila.children('.det_doc_td').children('.peaje_span').hide();
          fila.children('.det_doc_td').children('.det_doc_inp').show();
        } else if (cve == 1) {
          // Placa + km
          fila.children('.det_doc_td').children('.veh_l').show();
          fila.children('.det_doc_td').children('.peaje_span').hide();

          var veh_id = fila.children('.det_doc_td').children('.veh_l').children('option:selected').val();
          if (veh_id == -1) {
            fila.children('.det_doc_td').children('.km_span').hide();
            fila.children('.det_doc_td').children('.det_doc_inp').show();
          } else {
            fila.children('.det_doc_td').children('.km_span').show();
            fila.children('.det_doc_td').children('.det_doc_inp').hide();
          }
        } else if (cve == 2) {
          // Placa + peaje
          fila.children('.det_doc_td').children('.veh_l').show();
          fila.children('.det_doc_td').children('.km_span').hide();
          fila.children('.det_doc_td').children('.peaje_span').show();
          fila.children('.det_doc_td').children('.det_doc_inp').show();

          var veh_id = fila.children('.det_doc_td').children('.veh_l').children('option:selected').val();
          if (veh_id == -1) {
            fila.children('.det_doc_td').children('.det_doc_inp').show();
          } else {
            fila.children('.det_doc_td').children('.det_doc_inp').hide();
          }
        } else if (cve == 3) {
          // Placa + glosa normal sin validacion
          fila.children('.det_doc_td').children('.veh_l').show();
          fila.children('.det_doc_td').children('.km_span').hide();
          fila.children('.det_doc_td').children('.peaje_span').hide();
          fila.children('.det_doc_td').children('.det_doc_inp').show();
        } else if (cve == 4) {
          // Modificacion KJLG - 20160314
          // Validar que la glosa tenga el caracter slash
          fila.children('.det_doc_td').children('.det_doc_inp').rules('add', {
            validaslash: true
          });

          fila.children('.det_doc_td').children('.veh_l').hide();
          fila.children('.det_doc_td').children('.km_span').hide();
          fila.children('.det_doc_td').children('.peaje_span').hide();
          fila.children('.det_doc_td').children('.det_doc_inp').show();
        }
        // Fin modificacion

        $.when(
          $.getJSON('ret_det.php', {
            conc_id: conc_id,
            fec: fec_doc
          }, function(data) {
            ret_tasa_inp.val(data.ret_tasa);
            ret_min_monto_inp.val(data.ret_minmonto);
            det_tasa_inp.val(data.det_tasa);
            det_min_monto_inp.val(data.det_minmonto);
          })
        ).then(function() {
          $(document).ret_det_recalc(fila);
        });

        if (conc_id == 85 || conc_id == 86) {
          fila.children('.tipo_doc_td').children('.tipo_doc').children('option[value=11]').attr("selected", true);
          fila.children('.det_doc_td').children('.det_doc_inp').val('DEVOLUCION DE EAR.');
          fila.children('.num_doc_td').children('.num_doc_inp').attr("maxlength", 15);
          //-----------LA MISMA FUNCION QUE HACE CUANDO CAMBIA EL SELECT DE TIPO DOC----------
          var taxcode = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('taxcode'));

          if (taxcode == 3) {
            fila.children('.afecto_sel_td').children('.afecto_sel').show();
            fila.children('.montOtro_td').children('.montOtro_inp').show();
            fila.children('.montOtro_td').children('.montOtro_inp').val();
            fila.children('.montIcbp_td').children('.montIcbp_inp').show();
            fila.children('.montIcbp_td').children('.montIcbp_inp').val();
            $(this).tc_afec_redraw(fila);
          } else if (taxcode == 6) {
            fila.children('.afecto_sel_td').children('.afecto_sel').hide();
            fila.children('.afecto_sel_td').children('.afecto_sel').val(2);
            fila.children('.afecto_td').children('.afecto_inp').hide();
            fila.children('.afecto_td').children('.afecto_inp').val('');
            fila.children('.noafecto_td').children('.noafecto_inp').show();
            // duda
            fila.children('.montOtro_td').children('.montOtro_inp').hide();
            fila.children('.montOtro_td').children('.montOtro_inp').val('');

            fila.children('.montIcbp_td').children('.montIcbp_inp').hide();
            fila.children('.montIcbp_td').children('.montIcbp_inp').val('');
            // fin duda
            //SETEO EL MONTO DE DEVOLUCION
            var montoDev = $('#mon_saldo_s').html();
            if (montoDev * 1 > 0) {
              fila.children('.noafecto_td').children('.noafecto_inp').val(montoDev);
            }
            //FIN SETEO

            $(this).ret_det_recalc(fila);
          } else {
            fila.children('.afecto_sel_td').children('.afecto_sel').hide();
            fila.children('.afecto_sel_td').children('.afecto_sel').val(1);
            fila.children('.afecto_td').children('.afecto_inp').show();
            fila.children('.noafecto_td').children('.noafecto_inp').hide();
            fila.children('.noafecto_td').children('.noafecto_inp').val('');
            // oculta el campo otro
            fila.children('.montOtro_td').children('.montOtro_inp').hide();
            fila.children('.montOtro_td').children('.montOtro_inp').val('');
            fila.children('.montIcbp_td').children('.montIcbp_inp').hide();
            fila.children('.montIcbp_td').children('.montIcbp_inp').val('');

            $(this).ret_det_recalc(fila);
          }
          //-------------FIN-------------
        }
      });

      $('#doc_sust_detalle').on('change', '.veh_l', function() {
        var fila = $(this).parent();
        var cve = fila.parent().children('.conc_td').children('.conc_l').children('option:selected').attr('cve');

        if (cve == 1) {
          var veh_id = fila.children('.veh_l').children('option:selected').val();
          if (veh_id == -1) {
            fila.children('.km_span').hide();
            fila.children('.det_doc_inp').show();
          } else {
            fila.children('.km_span').show();
            fila.children('.det_doc_inp').hide();
          }
        } else if (cve == 2) {
          var veh_id = fila.children('.veh_l').children('option:selected').val();
          fila.children('.km_span').hide();
          if (veh_id == -1) {
            fila.children('.det_doc_inp').show();
          } else {
            fila.children('.det_doc_inp').hide();
          }
        }
      });

      $.fn.tc_afec_redraw = function(fila) {
        var mon_id_sel = parseInt(fila.children('.tipo_mon_td').children('.tipo_mon').val());
        var afecto_sel = parseInt(fila.children('.afecto_sel_td').children('.afecto_sel').val());
        fila.children('.conv_afecto_td').children('.conv_afecto_inp').hide();
        fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').hide();
        fila.children('.conv_otro_td').children('.conv_otro_inp').hide();
        switch (afecto_sel) {
          case 1:
          case 2:
            fila.children('.afecto_td').children('.afecto_inp').show();
            fila.children('.noafecto_td').children('.noafecto_inp').hide();
            fila.children('.noafecto_td').children('.noafecto_inp').val('');
            fila.children('.montOtro_td').children('.montOtro_inp').show();
            fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').val('');
            //fila.children('.conv_otro_td').children('.conv_otro_inp').val('');
            if (mon_id_sel != mon_id) {
              fila.children('.conv_afecto_td').children('.conv_afecto_inp').show();
              fila.children('.conv_otro_td').children('.conv_otro_inp').show();
              fila.children('.conv_icbp_td').children('.conv_icbp_inp').show();
            }
            break;
          case 3:
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
          case 4:
          case 5:
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
        var ruc_req = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('rucreq'));
        var tipo_doc = parseInt(fila.children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('value'));
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

        if ((ruc_req != 1) || (ruc_ret == 1 && conc_det_tasa == 0)) {
          fila.children('.aplic_retdet_td').children('.aplic_retdet').hide();
        } else {
          fila.children('.aplic_retdet_td').children('.aplic_retdet').show();
        }

        if (mon_id_sel == mon_id) {
          fila.children('.conv_afecto_td').children('.conv_afecto_inp').val(monto_afecto);
          let factor = (tipo_doc == "19" ? -1 : 1);
          fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').val(factor * monto_noafecto);
          fila.children('.conv_otro_td').children('.conv_otro_inp').val(monto_otro);
          fila.children('.conv_icbp_td').children('.conv_icbp_inp').val(monto_icbp);
          if (mon_id == 1) {
            var conv_conc_det_minmonto = conc_det_minmonto;
            var conv_ret_minmonto = ret_minmonto;
          } else if (mon_id == 2) {
            var conv_conc_det_minmonto = conc_det_minmonto / tc_inp;
            var conv_ret_minmonto = ret_minmonto / tc_inp;
          }
        } else if (tc_inp > 0) {
          if (mon_id_sel == 2 && mon_id == 1) {

            var monto_conv_afecto = (monto_afecto * tc_inp) || 0;
            let factor = (tipo_doc == "19" ? -1 : 1);
            var monto_conv_noafecto = factor * (monto_noafecto * tc_inp) || 0;
            var monto_conv_otro = (monto_otro * tc_inp) || 0;
            var monto_conv_icbp = (monto_icbp * tc_inp) || 0;
            var conv_conc_det_minmonto = conc_det_minmonto / tc_inp;
            var conv_ret_minmonto = ret_minmonto / tc_inp;
          } else if (mon_id_sel == 1 && mon_id == 2) {

            var monto_conv_afecto = (monto_afecto / tc_inp) || 0;
            let factor = (tipo_doc == "19" ? -1 : 1);
            var monto_conv_noafecto = factor * (monto_noafecto / tc_inp) || 0;
            var monto_conv_otro = (monto_otro / tc_inp) || 0;
            var monto_conv_icbp = (monto_icbp / tc_inp) || 0;
            var conv_conc_det_minmonto = conc_det_minmonto;
            var conv_ret_minmonto = ret_minmonto;
          }

          fila.children('.conv_afecto_td').children('.conv_afecto_inp').val(monto_conv_afecto.toFixed(2));
          fila.children('.conv_noafecto_td').children('.conv_noafecto_inp').val(monto_conv_noafecto.toFixed(2));
          fila.children('.conv_otro_td').children('.conv_otro_inp').val(monto_conv_otro.toFixed(2));
          fila.children('.conv_icbp_td').children('.conv_icbp_inp').val(monto_conv_icbp.toFixed(2));
        }

        fila.children('.retdet_td').children('.retdet_div').text('Exonerado');
        fila.children('.retdet_td').children('.retdet_tip').val(0);
        fila.children('.retdet_td').children('.retdet_monto').val(0);
        if (ruc_req == 1 && tc_inp > 0) {
          var otra_moneda = '';
          if (conc_det_tasa > 0 && conv_conc_det_minmonto <= monto_afecto && apldet == 1) {
            var monto_det = monto_afecto * (conc_det_tasa / 100);
            if (mon_id_sel == 2 && mon_id == 1) {
              otra_moneda = ' (' + Math.round(monto_det * tc_inp) + ' PEN)';
            } else if (mon_id_sel == 1 && mon_id == 2) {
              otra_moneda = ' (' + (monto_det / tc_inp).toFixed(2) + ' USD)';
            }

            if (mon_id_sel == 1 && mon_id == 1) {
              monto_det = Math.round(monto_det);
            }
            fila.children('.retdet_td').children('.retdet_div').text('Aplica detraccion de ' + monto_det.toFixed(2) + ' ' + mon_nom_sel + otra_moneda);
            fila.children('.retdet_td').children('.retdet_tip').val(1);
            fila.children('.retdet_td').children('.retdet_monto').val(monto_det.toFixed(2));
          } else if (ruc_ret == 0 && conv_ret_minmonto <= monto_afecto && aplret == 1) {
            var monto_ret = monto_afecto * (ret_tasa / 100)
            if (mon_id_sel == 2 && mon_id == 1) {
              otra_moneda = ' (' + (monto_ret * tc_inp).toFixed(2) + ' PEN)';
            } else if (mon_id_sel == 1 && mon_id == 2) {
              otra_moneda = ' (' + (monto_ret / tc_inp).toFixed(2) + ' USD)';
            }
            fila.children('.retdet_td').children('.retdet_div').text('Aplica retencion de ' + monto_ret.toFixed(2) + ' ' + mon_nom_sel + otra_moneda);
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
        var tipo_doc = 0;

        $('.conv_afecto_inp').each(function() {
          tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0; //

          //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
          let factor = (tipo_doc == "19" ? -1 : 1);
          //                        console.log(tipo_doc);
          tot_mon_liq += factor * ((parseFloat($(this).val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);

          retdet_tip = parseInt($(this).parent().parent().children('.retdet_td').children('.retdet_tip').val());
          aplic_retdet = parseInt($(this).parent().parent().children('.aplic_retdet_td').children('.aplic_retdet').val());
          tc_fila = parseFloat($(this).parent().parent().children('.tc_td').children('.tc_inp').val());
          mon_retdet_fila = parseFloat($(this).parent().parent().children('.retdet_td').children('.retdet_monto').val());
          tipo_mon_fila = parseInt($(this).parent().parent().children('.tipo_mon_td').children('.tipo_mon').val());

          if (tipo_mon_fila == 2 && mon_id == 1) {
            mon_retdet_fila = (mon_retdet_fila * tc_fila).toFixed(2);
          } else if (tipo_mon_fila == 1 && mon_id == 2) {
            mon_retdet_fila = (mon_retdet_fila / tc_fila).toFixed(2);
          }

          if (retdet_tip == 1) { // Detraccion
            if (aplic_retdet == 1) {
              tot_mon_det += parseFloat(mon_retdet_fila);
            } else if (aplic_retdet == 0) {
              tot_mon_det_no += parseFloat(mon_retdet_fila);
            }
          } else if (retdet_tip == 2) { // Retencion
            if (aplic_retdet == 1) {
              tot_mon_ret += parseFloat(mon_retdet_fila);
            } else if (aplic_retdet == 0) {
              tot_mon_ret_no += parseFloat(mon_retdet_fila);
            }
          }
        });

        var tot_mon_liq_res = tot_mon_liq - tot_mon_ret - tot_mon_det;
        var mon_saldo = <?php echo $ear_monto; ?> - tot_mon_liq_res;
        if (mon_saldo < 0) {
          $('#mon_saldo_s').css('color', 'red');
        } else {
          $('#mon_saldo_s').css('color', 'black');
        }
        var mon_abodes = mon_saldo; // + tot_mon_ret_no + tot_mon_det_no; // Monto abono o descuento
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
            resul_inp = (mon_abodes * -1).toFixed(2);
            break;
        }

        $('#tot_mon_liq').val(tot_mon_liq_res.toFixed(2));
        $('#mon_saldo').val(mon_saldo.toFixed(2));
        $('#tot_mon_ret').val(tot_mon_ret.toFixed(2));
        $('#tot_mon_ret_no').val(tot_mon_ret_no.toFixed(2));
        $('#tot_mon_det').val(tot_mon_det.toFixed(2));
        $('#tot_mon_det_no').val(tot_mon_det_no.toFixed(2));
        $('#tot_mon_doc').val(tot_mon_liq.toFixed(2));
        $('#resul_inp').val(mon_abodes.toFixed(2));

        $('#tot_mon_liq_s').html(tot_mon_liq_res.toFixed(2));
        $('#mon_saldo_s').html(mon_saldo.toFixed(2));
        $('#tot_mon_ret_s').html(tot_mon_ret.toFixed(2));
        $('#tot_mon_ret_no_s').html(tot_mon_ret_no.toFixed(2));
        $('#tot_mon_det_s').html(tot_mon_det.toFixed(2));
        $('#tot_mon_det_no_s').html(tot_mon_det_no.toFixed(2));
        $('#tot_mon_doc_s').html(tot_mon_liq.toFixed(2));
        $('#resul_msg').html(resul_msg);
        $('#resul_inp_s').html(resul_inp);

        $(document).topes_recalc();
      };

      $.fn.topes_recalc = function() {
        var tot_mon_liq = 0;
        var solsubt = 0;
        var html = '';
        var tipo_doc = 0;
        //                    var noafecto =  0;
        //                    var otro = 0;
        //                    var monto = 0;

        $('#bole_body .conv_afecto_inp').each(function() {
          tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
          //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
          let factor = (tipo_doc == "19" ? -1 : 1);
          tot_mon_liq += factor * ((parseFloat($(this).val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);
        });
        $('#liqsubt01').text(tot_mon_liq.toFixed(2));
        solsubt = parseFloat($('#solsubt01').text());
        if (solsubt >= tot_mon_liq.toFixed(2)) {
          html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
        } else {
          html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
        }
        $('#divsubt01').html(html);

        tot_mon_liq = 0;
        $('#alim_body .conv_afecto_inp').each(function() {
          tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
          //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
          let factor = (tipo_doc == "19" ? -1 : 1);
          tot_mon_liq += factor * ((parseFloat($(this).val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);
        });
        $('#liqsubt02').text(tot_mon_liq.toFixed(2));
        solsubt = parseFloat($('#solsubt02').text());
        if (solsubt >= tot_mon_liq.toFixed(2)) {
          html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
        } else {
          html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
        }
        $('#divsubt02').html(html);

        tot_mon_liq = 0;
        $('#hosp_body .conv_afecto_inp').each(function() {
          tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
          //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
          let factor = (tipo_doc == "19" ? -1 : 1);
          tot_mon_liq += factor * ((parseFloat($(this).val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);
        });
        $('#liqsubt03').text(tot_mon_liq.toFixed(2));
        solsubt = parseFloat($('#solsubt03').text());
        if (solsubt >= tot_mon_liq.toFixed(2)) {
          html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
        } else {
          html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
        }
        $('#divsubt03').html(html);

        tot_mon_liq = 0;
        $('#movi_body .conv_afecto_inp').each(function() {
          tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
          //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
          let factor = (tipo_doc == "19" ? -1 : 1);
          tot_mon_liq += factor * ((parseFloat($(this).val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);
        });
        $('#liqsubt04').text(tot_mon_liq.toFixed(2));
        solsubt = parseFloat($('#solsubt04').text());
        if (solsubt >= tot_mon_liq.toFixed(2)) {
          html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
        } else {
          html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
        }
        $('#divsubt04').html(html);

        tot_mon_liq = 0;
        $('#gast_body .conv_afecto_inp').each(function() {
          tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
          //EN CASO DE SER DOC_TIPO = 19: NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
          let factor = (tipo_doc == "19" ? -1 : 1);
          tot_mon_liq += factor * ((parseFloat($(this).val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);
        });
        $('#liqsubt05').text(tot_mon_liq.toFixed(2));
        solsubt = parseFloat($('#solsubt05').text());
        if (solsubt >= tot_mon_liq.toFixed(2)) {
          html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
        } else {
          html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
        }
        $('#divsubt05').html(html);

        tot_mon_liq = 0;
        $('#otro_body .conv_afecto_inp').each(function() {
          tipo_doc = (parseFloat($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').val())) || 0;
          //EN CASO DE SER DOC_TIPO = 19 : NOTA DE CREDITO, RESTA AL MONTO LIQUIDADO
          let factor = (tipo_doc == "19" ? -1 : 1);
          tot_mon_liq += factor * ((parseFloat($(this).val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_noafecto_td').children('.conv_noafecto_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_otro_td').children('.conv_otro_inp').val())) || 0);
          tot_mon_liq += factor * ((parseFloat($(this).parent().parent().children('.conv_icbp_td').children('.conv_icbp_inp').val())) || 0);
        });
        $('#liqsubt06').text(tot_mon_liq.toFixed(2));
        solsubt = parseFloat($('#solsubt06').text());
        if (solsubt >= tot_mon_liq.toFixed(2)) {
          html = '<div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>';
        } else {
          html = '<div style="font-weight:bold;color:white;background-color:red; text-align:center; width:100px;">Excedido</div>';
        }
        $('#divsubt06').html(html);

        $(document).checktopes_redraw();
      };

      $.fn.tabla_tipo_nota = function($tbl) {

        $tbl.children('.ruc_nro_td').children('.ruc_nro_i').rules('add', {
          required: true
        });
        $tbl.children('.afecto_td').children('.afecto_inp').rules('add', {
          //                            min: 0.01
        });
        $tbl.children('.noafecto_td').children('.noafecto_inp').rules('add', {
          //                            min: 0.01
        });
        $tbl.children('.montOtro_td').children('.montOtro_inp').rules('add', {
          //                            min: 0.01
        });
        $tbl.children('.montIcbp_td').children('.montIcbp_inp').rules('add', {
          //                            min: 0.01
        });
        $tbl.children('.tc_td').children('.tc_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.ser_doc_td').children('.ser_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.num_doc_td').children('.num_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.km_span').children('.km_inp').rules('add', {
          required: true,
          digits: true,
          min: 1
        });
        $tbl.children('.det_doc_td').children('.peaje_span').children('.peaje_inp').rules('add', {
          required: true
        });
        var conc_id = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('conc_id');
        $tbl.children('.conc_td').children('.conc_id_inp').val(conc_id);

        var cve = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('cve');
        $tbl.children('.conc_td').children('.cve_inp').val(cve);
        if (cve == 0) {
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 1) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 2) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
        } else if (cve == 3) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 4) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
            validaslash: true
          });
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        }
        $tbl.children('.ruc_nro_td').children('.ruc_nro_i').focus();
      };
      $.fn.tipo_notaf = function() {
        var IdenBody = $(this).attr('tbodyid');
        var fila_id = $(this).parents("tr");
        var documento_relacion = "";
        var tipo_doc = parseInt($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('value'));
        var ruc = $(this).parent().parent().children('.ruc_nro_td').children('.ruc_nro_i').val();
        var ser = $(this).parent().parent().children('.ser_doc_td').children('.ser_doc_inp').val();
        var num = $(this).parent().parent().children('.num_doc_td').children('.num_doc_inp').val();
        if (tipo_doc === 2 && tipo_doc === 4) {
          alert("Solo se pueden relacionar facturas o boletas.");
          return;
        } else if (ruc == '' || ruc == null) {
          alert("Ingresar el ruc");
          return;
        } else if (ser == '0' || ser == null) {
          alert("Debe ingresar la serie");
          return;
        } else if (num == '0' || num == null) {
          alert("Debe ingresar el numero");
          return;
        }
        documento_relacion = tipo_doc + "|" + ruc + "|" + ser + "|" + num;

        var $tbl = fila_id;
        var ruc_nro_t = $('#ruc_nro_template').html();
        var prov_nom_t = $('#prov_nom_template').html();
        var conc_t = "";
        switch ((IdenBody * 1)) {
          case 1:
            conc_t = $('#bole_conc_template').html();
            break;
          case 2:
            conc_t = $('#alim_conc_template').html();
            break;
          case 3:
            conc_t = $('#hosp_conc_template').html();
            break;
          case 4:
            conc_t = $('#movi_conc_template').html();
            break;
          case 5:
            conc_t = $('#gast_conc_template').html();
            break;
          case 6:
            conc_t = $('#otro_conc_template').html();
            break;
        }


        var tipo_doc_t = $('#tipo_doc_nota_template').html();
        var tipo_mon_t = $('#tipo_mon_template').html();
        var agregarDistribucion = '&nbsp;<input type="hidden" value=""  id="lid_distribucion[{0}]" name="lid_distribucion[{0}]" /><img  id="op_distribucion[{0}]" name="op_distribucion[{0}]" class="op_distribucion" src="img/plus.png"  onclick="abrirModalDistribucion(\'[{0}]\',\'' + IdenBody + '\')" title="Agregar distribucion contable">&nbsp;';
        var dele = '<img src="img/delete.png" class="dele" title="Borrar">&nbsp;';
        var s_add = '<tr class="fila_dato">';
        //                    s_add += '<td class="bandera" hidden><input type="text" value="'+fila_id+'" class="bandera" name="bandera"></td>';
        s_add += '<td class="conc_td">' + conc_t + '</td>'; //Concepto
        s_add += '<td class="tipo_doc_td">' + tipo_doc_t + '</td>'; //Tipo Doc
        s_add += '<td class="ruc_nro_td">' + ruc_nro_t + '</td>'; //RUC
        s_add += '<td class="prov_nom_td">' + prov_nom_t + '</td>'; // Nombre Proveedor
        s_add += s_fecsernumdet;
        s_add += '<td class="tipo_mon_td">' + tipo_mon_t + '</td>'; //Moneda
        s_add += s_afeconretdet;
        s_add += '<td><input type="hidden" value="' + documento_relacion + '" id="documento_relacion[{0}]" name="documento_relacion[{0}]"/> ' + dele + '</td>';
        s_add += '</tr>';
        var template = jQuery.validator.format(s_add);
        $tbl.after($(template(i++)));

        switch ((IdenBody * 1)) {
          case 1:
            $tbl = $('#bole_body tr');
            break;
          case 2:
            $tbl = $('#alim_body tr');
            break;
          case 3:
            $tbl = $('#hosp_body tr');
            break;
          case 4:
            $tbl = $('#movi_body tr');
            break;
          case 5:
            $tbl = $('#gast_body tr');
            break;
          case 6:
            $tbl = $('#otro_body tr');
            break;
        }
        if (!isEmpty($tbl)) {
          $(document).tabla_tipo_nota($tbl);
        }
      };

      $('#doc_sust_detalle').on('click', '.tipo_nota', function() {
        $(this).tipo_notaf($(this).parent().parent());
      });
      $('#doc_sust_detalle').on('click', '.dele', function() {
        $(this).parent().parent().remove();
        $(this).ret_det_recalc($(this).parent().parent());
      });
      $('#table_distribucion').on('click', '.dele', function() {
        $(this).parent().parent().remove();
        reenumerarFilasDetalleDistribucion();
      });


      $(document).ready(function() {

        // executes when HTML-Document is loaded and DOM is ready
        $('#grabar').prop('disabled', false);

        $('#enviar').prop('disabled', false);

        if (pla_exc == 1) {
          $('#advertencia').show();
        }

        // add validation to some input fields
        $('.afecto_inp').each(function() {
          $(this).rules('add', {
            min: 0.01
          });
          $(this).parent().parent().children('.noafecto_td').children('.noafecto_inp').rules('add', {
            min: 0.01
          });
          $(this).parent().parent().children('.montOtro_td').children('.montOtro_inp').rules('add', {
            //                            min: 0.01
          });
          $(this).parent().parent().children('.tc_td').children('.tc_inp').rules('add', {
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
          $(this).parent().parent().children('.det_doc_td').children('.km_span').children('.km_inp').rules('add', {
            required: true,
            digits: true,
            min: 1
          });
          $(this).parent().parent().children('.det_doc_td').children('.peaje_span').children('.peaje_inp').rules('add', {
            required: true
          });
          var cve = $(this).parent().parent().children('.conc_td').children('.conc_l').children('option:selected').attr('cve');
          if (cve == 4) {
            $(this).parent().parent().children('.det_doc_td').children('.det_doc_inp').rules('add', {
              validaslash: true
            });
          }

          var taxcode = parseInt($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('taxcode'));
          if (taxcode != 3) {
            $(this).parent().parent().children('.afecto_sel_td').children('.afecto_sel').hide();
          }
          if(taxcode == 6){
            $(this).parent().parent().children('.noafecto_td').children('.noafecto_inp').rules('add', {
              required: true
            });
          }
        });

        // calculate entire rows
        $(document).totalizar_recalc();
      });

      var veh_t = $('#veh_template').html();

      var s_fecsernumdet = '<td class="fec_doc_td"><input type="text" value="' + fec_hoy + '" size="11" maxlength="10" class="fecha_inp" readonly name="fec_doc[{0}]"></td>'; //Fecha
      s_fecsernumdet += '<td class="ser_doc_td"><input type="text" value="0" size="6" maxlength="5" class="ser_doc_inp" name="ser_doc[{0}]"></td>'; //Serie
      s_fecsernumdet += '<td class="num_doc_td"><input type="text" value="0" size="9" maxlength="15" class="num_doc_inp" name="num_doc[{0}]"></td>'; //Numero
      s_fecsernumdet += '<td class="det_doc_td">' + veh_t + '<input type="text" value="" size="14" maxlength="200" class="det_doc_inp" name="det_doc[{0}]"></td>'; //Detalle

      var s_afeconretdet = '<td class="afecto_sel_td"><select id="afecto_sel[{0}]" class="afecto_sel" name="afecto_sel[{0}]">';
      opAfectoIGV.map(opcion => {
        s_afeconretdet += `<option value="${opcion.id}">${opcion.descripcion}</option>`;
      });
      s_afeconretdet += '</select></td>'; //Afecto

      s_afeconretdet += '<td class="afecto_td"><input type="text" value="0" size="8" maxlength="9" id="afecto_inp[{0}]" class="afecto_inp" name="afecto_inp[{0}]"></td>'; //Monto Afecto
      s_afeconretdet += '<td class="noafecto_td"><input type="text" value="0" size="8" maxlength="9" id="noafecto_inp[{0}]" class="noafecto_inp" name="noafecto_inp[{0}]" style="display: none;"></td>'; //Monto NoAfecto
      s_afeconretdet += '<td class="montOtro_td"><input type="text" value="0" size="8" maxlength="9" id="montOtro_inp[{0}]" class="montOtro_inp" name="montOtro_inp[{0}]"></td>'; //Monto Otros
      s_afeconretdet += '<td class="montIcbp_td"><input type="text" value="0" size="8" maxlength="9" id="montIcbp_inp[{0}]" class="montIcbp_inp" name="montIcbp_inp[{0}]"></td>'; //Monto ICBP
      s_afeconretdet += '<td class="tc_td"><div class="tc_div" style="display: none;"></div><input size="6" maxlength="9" class="tc_inp" name="tc_inp[{0}]" value="' + tc_hoy + '"></td>'; //T/C;
      s_afeconretdet += '<td class="conv_afecto_td"><input type="text" value="" size="8" maxlength="9" id="conv_afecto_inp[{0}]" class="conv_afecto_inp" name="conv_afecto_inp[{0}]" style="display: none;" readonly></td>'; //Conversion Afecto
      s_afeconretdet += '<td class="conv_noafecto_td"><input type="text" value="" size="8" maxlength="9" id="conv_noafecto_inp[{0}]" class="conv_noafecto_inp" name="conv_noafecto_inp[{0}]" style="display: none;" readonly></td>'; //Conversion NoAfecto
      s_afeconretdet += '<td class="conv_otro_td"><input type="text" value="" size="8" maxlength="9" id="conv_otro_inp[{0}]" class="conv_otro_inp" name="conv_otro_inp[{0}]" style="display: none;" readonly></td>'; //Conversion Otros
      s_afeconretdet += '<td class="conv_icbp_td"><input type="text" value="" size="8" maxlength="9" id="conv_icbp_inp[{0}]" class="conv_icbp_inp" name="conv_icbp_inp[{0}]" style="display: none;" readonly></td>'; //Conversion ICBP
      s_afeconretdet += '<td class="aplic_retdet_td"><select class="aplic_retdet" name="aplic_retdet[{0}]"><option value="1">Si</option><option value="0">No</option></select></td>'; //Ret/Det
      s_afeconretdet += '<td class="retdet_td"><div class="retdet_div"></div><input type="hidden" class="retdet_tip" name="retdet_tip[{0}]" value="0"><input type="hidden" class="retdet_monto" name="retdet_monto[{0}]" value="0"></td>'; //Monto Ret:Det

      var fila_id = 0;

      $('#bole_add').click(function() {
        fila_id += 1;
        var $tbl = $('#bole_body tr:last');

        var ruc_nro_t = $('#ruc_nro_template').html();
        var prov_nom_t = $('#prov_nom_template').html();
        var conc_t = $('#bole_conc_template').html();
        var tipo_doc_t = $('#tipo_doc_template').html();
        var orden_t =  '&nbsp;<input type="hidden" value=""  id="lid_orden_trabajo[{0}]" name="lid_orden_trabajo[{0}]" /><label id="des_orden_trabajo[{0}]" name="des_orden_trabajo[{0}]">Agregar Orden de Trabajo</label>&nbsp;<img class="orden_trabajo" src="img/plus.png"  onclick="abrirModalOrdenTrabajo(\'[{0}]\')" title="Agregar Orden de Trabajo">&nbsp;';
        var tipo_mon_t = $('#tipo_mon_template').html();
        var dele = '<img src="img/delete.png" class="dele" title="Borrar">&nbsp;';
        var agregarDistribucion = '&nbsp;<input type="hidden" value=""  id="lid_distribucion[{0}]" name="lid_distribucion[{0}]" /><img id="op_distribucion[{0}]" name="op_distribucion[{0}]" class="op_distribucion" src="img/plus.png"  onclick="abrirModalDistribucion(\'[{0}]\',\'01\')" title="Agregar distribucion contable">&nbsp;';
        var agregarTipoDoc = '<img src="img/n.png" class="tipo_nota" Id= title="NC|ND" tbodyId="1">';
        //                    id="'+fila_id+'"
        var s_add = '<tr class="fila_dato " bandera_id="' + fila_id + '">';
        s_add += '<td class="conc_td">' + conc_t + '</td>'; //Concepto
        s_add += '<td class="tipo_doc_td">' + tipo_doc_t + '</td>'; //Tipo Doc
        s_add += '<td class="orden_t" style="white-space: nowrap;">' + orden_t + '</td>'; //Orden trabajo
        s_add += '<td class="ruc_nro_td">' + ruc_nro_t + '</td><td class="prov_nom_td">' + prov_nom_t + '</td>'; //RUC y Nombre Proveedor
        s_add += s_fecsernumdet;
        s_add += '<td class="tipo_mon_td">' + tipo_mon_t + '</td>'; //Moneda
        s_add += s_afeconretdet;
        //		s_add += '<td>'+dist_gast+'</td>';
        s_add += '<td>' + agregarTipoDoc + agregarDistribucion + dele + '</td>';
        s_add += `<td class='monto_igv_td'><input type='hidden' value='18' id='monto_igv[${fila_id}]' name='monto_igv[${fila_id}]' readonly></td>`;
        s_add += '</tr>';

        var template = jQuery.validator.format(s_add);

        $tbl.after($(template(i++)));

        $tbl = $('#bole_body tr:last');
        $tbl.children('.afecto_td').children('.afecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.noafecto_td').children('.noafecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.montOtro_td').children('.montOtro_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.montIcbp_td').children('.montIcbp_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.tc_td').children('.tc_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.ser_doc_td').children('.ser_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.num_doc_td').children('.num_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.km_span').children('.km_inp').rules('add', {
          required: true,
          digits: true,
          min: 1
        });
        $tbl.children('.det_doc_td').children('.peaje_span').children('.peaje_inp').rules('add', {
          required: true
        });

        var conc_id = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('conc_id');
        $tbl.children('.conc_td').children('.conc_id_inp').val(conc_id);

        var cve = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('cve');
        $tbl.children('.conc_td').children('.cve_inp').val(cve);
        if (cve == 0) {
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 1) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 2) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
        } else if (cve == 3) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 4) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
            validaslash: true
          });
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        }

        $tbl.children('.ruc_nro_td').children('.ruc_nro_i').focus();
      });

      $('#alim_add').click(function() {
        fila_id += 1;

        var $tbl = $('#alim_body tr:last');
        var ruc_nro_t = $('#ruc_nro_template').html();
        var prov_nom_t = $('#prov_nom_template').html();
        var conc_t = $('#alim_conc_template').html();
        var tipo_doc_t = $('#tipo_doc_template').html();
        var orden_t =  '&nbsp;<input type="hidden" value=""  id="lid_orden_trabajo[{0}]" name="lid_orden_trabajo[{0}]" /><label id="des_orden_trabajo[{0}]" name="des_orden_trabajo[{0}]">Agregar Orden de Trabajo</label>&nbsp;<img class="orden_trabajo" src="img/plus.png"  onclick="abrirModalOrdenTrabajo(\'[{0}]\')" title="Agregar Orden de Trabajo">&nbsp;';
        var tipo_mon_t = $('#tipo_mon_template').html();
        var dele = '<img src="img/delete.png" class="dele" title="Borrar">&nbsp;';
        var agregarDistribucion = '&nbsp;<input type="hidden" value=""  id="lid_distribucion[{0}]" name="lid_distribucion[{0}]" /><img id="op_distribucion[{0}]" name="op_distribucion[{0}]" class="op_distribucion" src="img/plus.png"  onclick="abrirModalDistribucion(\'[{0}]\',\'02\')" title="Agregar distribucion contable">&nbsp;';
        var agregarTipoDoc = '<img src="img/n.png" class="tipo_nota"  title="NC|ND" tbodyId="2">';
        var s_add = '<tr class="fila_dato" bandera_id="' + fila_id + '">';
        s_add += '<td class="conc_td">' + conc_t + '</td>'; //Concepto
        s_add += '<td class="tipo_doc_td">' + tipo_doc_t + '</td>'; //Tipo Doc
        s_add += '<td class="orden_t" style="white-space: nowrap;">' + orden_t + '</td>'; //Orden trabajo
        s_add += '<td class="ruc_nro_td">' + ruc_nro_t + '</td><td class="prov_nom_td">' + prov_nom_t + '</td>'; //RUC y Nombre Proveedor
        s_add += s_fecsernumdet;
        s_add += '<td class="tipo_mon_td">' + tipo_mon_t + '</td>'; //Moneda
        s_add += s_afeconretdet;
        //		s_add += '<td>'+dist_gast+'</td>';
        s_add += '<td>' + agregarTipoDoc + agregarDistribucion + dele + '</td>';
        s_add += `<td class='monto_igv_td'><input type='hidden' value='18' id='monto_igv[${fila_id}]' name='monto_igv[${fila_id}]' readonly></td>`;
        s_add += '</tr>';

        var template = jQuery.validator.format(s_add);

        $tbl.after($(template(i++)));

        $tbl = $('#alim_body tr:last');

        $tbl.children('.afecto_td').children('.afecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.noafecto_td').children('.noafecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.montOtro_td').children('.montOtro_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.montIcbp_td').children('.montIcbp_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.tc_td').children('.tc_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.ser_doc_td').children('.ser_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.num_doc_td').children('.num_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.km_span').children('.km_inp').rules('add', {
          required: true,
          digits: true,
          min: 1
        });
        $tbl.children('.det_doc_td').children('.peaje_span').children('.peaje_inp').rules('add', {
          required: true
        });

        var conc_id = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('conc_id');
        $tbl.children('.conc_td').children('.conc_id_inp').val(conc_id);

        var cve = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('cve');
        $tbl.children('.conc_td').children('.cve_inp').val(cve);
        if (cve == 0) {
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 1) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 2) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
        } else if (cve == 3) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 4) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
            validaslash: true
          });
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        }

        $tbl.children('.ruc_nro_td').children('.ruc_nro_i').focus();

      });

      $('#hosp_add').click(function() {
        fila_id += 1;
        var $tbl = $('#hosp_body tr:last');
        var ruc_nro_t = $('#ruc_nro_template').html();
        var prov_nom_t = $('#prov_nom_template').html();
        var conc_t = $('#hosp_conc_template').html();
        var tipo_doc_t = $('#tipo_doc_template').html();
        var orden_t =  '&nbsp;<input type="hidden" value=""  id="lid_orden_trabajo[{0}]" name="lid_orden_trabajo[{0}]" /><label id="des_orden_trabajo[{0}]" name="des_orden_trabajo[{0}]">Agregar Orden de Trabajo</label>&nbsp;<img class="orden_trabajo" src="img/plus.png"  onclick="abrirModalOrdenTrabajo(\'[{0}]\')" title="Agregar Orden de Trabajo">&nbsp;';
        var tipo_mon_t = $('#tipo_mon_template').html();
        var dele = '<img src="img/delete.png" class="dele" title="Borrar">&nbsp;';
        var agregarDistribucion = '&nbsp;<input type="hidden" value=""  id="lid_distribucion[{0}]" name="lid_distribucion[{0}]" /><img  id="op_distribucion[{0}]" name="op_distribucion[{0}]" class="op_distribucion" src="img/plus.png"  onclick="abrirModalDistribucion(\'[{0}]\',\'03\')" title="Agregar distribucion contable">&nbsp;';
        var agregarTipoDoc = '<img src="img/n.png" class="tipo_nota"  title="NC|ND" tbodyId="3">';
        var s_add = '<tr class="fila_dato" bandera_id="' + fila_id + '">';
        s_add += '<td class="conc_td">' + conc_t + '</td>'; //Concepto
        s_add += '<td class="tipo_doc_td">' + tipo_doc_t + '</td>'; //Tipo Doc
        s_add += '<td class="orden_t" style="white-space: nowrap;">' + orden_t + '</td>'; //Orden trabajo
        s_add += '<td class="ruc_nro_td">' + ruc_nro_t + '</td><td class="prov_nom_td">' + prov_nom_t + '</td>'; //RUC y Nombre Proveedor
        s_add += s_fecsernumdet;
        s_add += '<td class="tipo_mon_td">' + tipo_mon_t + '</td>'; //Moneda
        s_add += s_afeconretdet;
        //		s_add += '<td>'+dist_gast+'</td>';
        s_add += '<td>' + agregarTipoDoc + agregarDistribucion + dele + '</td>';
        s_add += `<td class='monto_igv_td'><input type='hidden' value='18' id='monto_igv[${fila_id}]' name='monto_igv[${fila_id}]' readonly></td>`;
        s_add += '</tr>';

        var template = jQuery.validator.format(s_add);

        $tbl.after($(template(i++)));

        $tbl = $('#hosp_body tr:last');
        $tbl.children('.afecto_td').children('.afecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.noafecto_td').children('.noafecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.montOtro_td').children('.montOtro_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.montIcbp_td').children('.montIcbp_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.tc_td').children('.tc_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.ser_doc_td').children('.ser_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.num_doc_td').children('.num_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.km_span').children('.km_inp').rules('add', {
          required: true,
          digits: true,
          min: 1
        });
        $tbl.children('.det_doc_td').children('.peaje_span').children('.peaje_inp').rules('add', {
          required: true
        });

        var conc_id = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('conc_id');
        $tbl.children('.conc_td').children('.conc_id_inp').val(conc_id);

        var cve = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('cve');
        $tbl.children('.conc_td').children('.cve_inp').val(cve);
        if (cve == 0) {
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 1) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 2) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
        } else if (cve == 3) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 4) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
            validaslash: true
          });
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        }

        $tbl.children('.ruc_nro_td').children('.ruc_nro_i').focus();
      });

      $('#movi_add').click(function() {
        fila_id += 1;
        var $tbl = $('#movi_body tr:last');
        var ruc_nro_t = $('#ruc_nro_template').html();
        var prov_nom_t = $('#prov_nom_template').html();
        var conc_t = $('#movi_conc_template').html();
        var tipo_doc_t = $('#tipo_doc_template').html();
        var orden_t =  '&nbsp;<input type="hidden" value=""  id="lid_orden_trabajo[{0}]" name="lid_orden_trabajo[{0}]" /><label id="des_orden_trabajo[{0}]" name="des_orden_trabajo[{0}]">Agregar Orden de Trabajo</label>&nbsp;<img class="orden_trabajo" src="img/plus.png"  onclick="abrirModalOrdenTrabajo(\'[{0}]\')" title="Agregar Orden de Trabajo">&nbsp;';
        var tipo_mon_t = $('#tipo_mon_template').html();
        var dele = '<img src="img/delete.png" class="dele" title="Borrar">&nbsp;';
        var agregarDistribucion = '&nbsp;<input type="hidden" value=""  id="lid_distribucion[{0}]" name="lid_distribucion[{0}]" /><img  id="op_distribucion[{0}]" name="op_distribucion[{0}]" class="op_distribucion" src="img/plus.png"  onclick="abrirModalDistribucion(\'[{0}]\',\'04\')" title="Agregar distribucion contable">&nbsp;';
        var agregarTipoDoc = '<img src="img/n.png" class="tipo_nota"  title="NC|ND" tbodyId="4">';
        var s_add = '<tr class="fila_dato" bandera_id="' + fila_id + '">';
        s_add += '<td class="conc_td">' + conc_t + '</td>'; //Concepto
        s_add += '<td class="tipo_doc_td">' + tipo_doc_t + '</td>'; //Tipo Doc
        s_add += '<td class="orden_t" style="white-space: nowrap;">' + orden_t + '</td>'; //Orden trabajo
        s_add += '<td class="ruc_nro_td">' + ruc_nro_t + '</td><td class="prov_nom_td">' + prov_nom_t + '</td>'; //RUC y Nombre Proveedor
        s_add += s_fecsernumdet;
        s_add += '<td class="tipo_mon_td">' + tipo_mon_t + '</td>'; //Moneda
        s_add += s_afeconretdet;
        //		s_add += '<td>'+dist_gast+'</td>';
        s_add += '<td>' + agregarTipoDoc + agregarDistribucion + dele + '</td>';
        s_add += `<td class='monto_igv_td'><input type='hidden' value='18' id='monto_igv[${fila_id}]' name='monto_igv[${fila_id}]' readonly></td>`;
        s_add += '</tr>';

        var template = jQuery.validator.format(s_add);

        $tbl.after($(template(i++)));

        $tbl = $('#movi_body tr:last');
        $tbl.children('.afecto_td').children('.afecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.noafecto_td').children('.noafecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.montOtro_td').children('.montOtro_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.montIcbp_td').children('.montIcbp_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.tc_td').children('.tc_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.ser_doc_td').children('.ser_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.num_doc_td').children('.num_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.km_span').children('.km_inp').rules('add', {
          required: true,
          digits: true,
          min: 1
        });
        $tbl.children('.det_doc_td').children('.peaje_span').children('.peaje_inp').rules('add', {
          required: true
        });

        var conc_id = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('conc_id');
        $tbl.children('.conc_td').children('.conc_id_inp').val(conc_id);

        var cve = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('cve');
        $tbl.children('.conc_td').children('.cve_inp').val(cve);
        if (cve == 0) {
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 1) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 2) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
        } else if (cve == 3) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 4) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
            validaslash: true
          });
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        }

        $tbl.children('.ruc_nro_td').children('.ruc_nro_i').focus();
      });

      $('#gast_add').click(function() {
        fila_id += 1;
        var $tbl = $('#gast_body tr:last');
        var ruc_nro_t = $('#ruc_nro_template').html();
        var prov_nom_t = $('#prov_nom_template').html();
        var conc_t = $('#gast_conc_template').html();
        var tipo_doc_t = $('#tipo_doc_template').html();
        var orden_t =  '&nbsp;<input type="hidden" value=""  id="lid_orden_trabajo[{0}]" name="lid_orden_trabajo[{0}]" /><label id="des_orden_trabajo[{0}]" name="des_orden_trabajo[{0}]">Agregar Orden de Trabajo</label>&nbsp;<img class="orden_trabajo" src="img/plus.png"  onclick="abrirModalOrdenTrabajo(\'[{0}]\')" title="Agregar Orden de Trabajo">&nbsp;';
        var tipo_mon_t = $('#tipo_mon_template').html();
        var dele = '<img src="img/delete.png" class="dele" title="Borrar">&nbsp;';
        var agregarDistribucion = '&nbsp;<input type="hidden" value=""  id="lid_distribucion[{0}]" name="lid_distribucion[{0}]" /><img  id="op_distribucion[{0}]" name="op_distribucion[{0}]" class="op_distribucion" src="img/plus.png"  onclick="abrirModalDistribucion(\'[{0}]\',\'05\')" title="Agregar distribucion contable">&nbsp;';
        var agregarTipoDoc = '<img src="img/n.png" class="tipo_nota"  title="NC|ND" tbodyId="5">';
        var s_add = '<tr class="fila_dato" bandera_id="' + fila_id + '">';
        s_add += '<td class="conc_td">' + conc_t + '</td>'; //Concepto
        s_add += '<td class="tipo_doc_td">' + tipo_doc_t + '</td>'; //Tipo Doc
        s_add += '<td class="orden_t" style="white-space: nowrap;">' + orden_t + '</td>'; //Orden trabajo
        s_add += '<td class="ruc_nro_td">' + ruc_nro_t + '</td><td class="prov_nom_td">' + prov_nom_t + '</td>'; //RUC y Nombre Proveedor
        s_add += s_fecsernumdet;
        s_add += '<td class="tipo_mon_td">' + tipo_mon_t + '</td>'; //Moneda
        s_add += s_afeconretdet;
        //		s_add += '<td>'+dist_gast+'</td>';
        s_add += '<td>' + agregarTipoDoc + agregarDistribucion + dele + '</td>';
        s_add += `<td class='monto_igv_td'><input type='hidden' value='18' id='monto_igv[${fila_id}]' name='monto_igv[${fila_id}]' readonly></td>`;
        s_add += '</tr>';

        var template = jQuery.validator.format(s_add);

        $tbl.after($(template(i++)));

        $tbl = $('#gast_body tr:last');
        $tbl.children('.afecto_td').children('.afecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.noafecto_td').children('.noafecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.montOtro_td').children('.montOtro_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.montIcbp_td').children('.montIcbp_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.tc_td').children('.tc_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.ser_doc_td').children('.ser_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.num_doc_td').children('.num_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.km_span').children('.km_inp').rules('add', {
          required: true,
          digits: true,
          min: 1
        });
        $tbl.children('.det_doc_td').children('.peaje_span').children('.peaje_inp').rules('add', {
          required: true
        });

        var conc_id = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('conc_id');
        $tbl.children('.conc_td').children('.conc_id_inp').val(conc_id);

        var cve = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('cve');
        $tbl.children('.conc_td').children('.cve_inp').val(cve);
        if (cve == 0) {
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 1) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 2) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
        } else if (cve == 3) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 4) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
            validaslash: true
          });
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        }

        $tbl.children('.ruc_nro_td').children('.ruc_nro_i').focus();
      });

      $('#otro_add').click(function() {
        fila_id += 1;
        //                    console.log(1);
        var $tbl = $('#otro_body tr:last');
        var ruc_nro_t = $('#ruc_nro_template').html();
        var prov_nom_t = $('#prov_nom_template').html();
        var conc_t = $('#otro_conc_template').html();
        var tipo_doc_t = $('#tipo_doc_template').html();
        var tipo_mon_t = $('#tipo_mon_template').html();
        var orden_t =  '&nbsp;<input type="hidden" value=""  id="lid_orden_trabajo[{0}]" name="lid_orden_trabajo[{0}]" /><label id="des_orden_trabajo[{0}]" name="des_orden_trabajo[{0}]">Agregar Orden de Trabajo</label>&nbsp;<img class="orden_trabajo" src="img/plus.png"  onclick="abrirModalOrdenTrabajo(\'[{0}]\')" title="Agregar Orden de Trabajo">&nbsp;';
        var dele = '<img src="img/delete.png" class="dele" title="Borrar">&nbsp;';
        var agregarDistribucion = '&nbsp;<input type="hidden" value=""  id="lid_distribucion[{0}]" name="lid_distribucion[{0}]" /><img  id="op_distribucion[{0}]" name="op_distribucion[{0}]" class="op_distribucion" src="img/plus.png"  onclick="abrirModalDistribucion(\'[{0}]\',\'06\')" title="Agregar distribucion contable">&nbsp;';
        var agregarTipoDoc = '<img src="img/n.png" class="tipo_nota"  title="NC|ND" tbodyId="6">';
        var s_add = '<tr class="fila_dato" bandera_id="' + fila_id + '">';
        s_add += '<td class="conc_td">' + conc_t + '</td>'; //Concepto
        s_add += '<td class="tipo_doc_td">' + tipo_doc_t + '</td>'; //Tipo Doc
        s_add += '<td class="orden_t" style="white-space: nowrap;">' + orden_t + '</td>'; //Orden trabajo
        s_add += '<td class="ruc_nro_td">' + ruc_nro_t + '</td><td class="prov_nom_td">' + prov_nom_t + '</td>'; //RUC y Nombre Proveedor
        s_add += s_fecsernumdet;
        s_add += '<td class="tipo_mon_td">' + tipo_mon_t + '</td>'; //Moneda
        s_add += s_afeconretdet;
        //		s_add += '<td>'+dist_gast+'</td>';
        s_add += '<td>' + agregarTipoDoc + agregarDistribucion + dele + '</td>';
        s_add += `<td class='monto_igv_td'><input type='hidden' value='18' id='monto_igv[${fila_id}]' name='monto_igv[${fila_id}]' readonly></td>`;
        s_add += '</tr>';

        var template = jQuery.validator.format(s_add);

        $tbl.after($(template(i++)));

        $tbl = $('#otro_body tr:last');
        $tbl.children('.afecto_td').children('.afecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.noafecto_td').children('.noafecto_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.montOtro_td').children('.montOtro_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.montIcbp_td').children('.montIcbp_inp').rules('add', {
          //                        min: 0.01
        });
        $tbl.children('.tc_td').children('.tc_inp').rules('add', {
          min: 0.01
        });
        $tbl.children('.ser_doc_td').children('.ser_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.num_doc_td').children('.num_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
          required: true
        });
        $tbl.children('.det_doc_td').children('.km_span').children('.km_inp').rules('add', {
          required: true,
          digits: true,
          min: 1
        });
        $tbl.children('.det_doc_td').children('.peaje_span').children('.peaje_inp').rules('add', {
          required: true
        });

        var conc_id = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('conc_id');
        $tbl.children('.conc_td').children('.conc_id_inp').val(conc_id);

        var cve = $tbl.children('.conc_td').children('.conc_l').children('option:selected').attr('cve');
        $tbl.children('.conc_td').children('.cve_inp').val(cve);
        if (cve == 0 || cve == -100) {
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 1) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 2) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
        } else if (cve == 3) {
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        } else if (cve == 4) {
          $tbl.children('.det_doc_td').children('.det_doc_inp').rules('add', {
            validaslash: true
          });
          $tbl.children('.det_doc_td').children('.veh_l').hide();
          $tbl.children('.det_doc_td').children('.km_span').hide();
          $tbl.children('.det_doc_td').children('.peaje_span').hide();
        }

        $tbl.children('.ruc_nro_td').children('.ruc_nro_i').focus();
      });

      $("#liquidacion").validate({
        rules: {
          "txtTipoCambioLiq": {
            required: true,
            number: true,
            min: 0.01
          },
          "dua_serie": {
            required: true,
            digits: true
          },
          "dua_numero": {
            required: true,
            digits: true
          }
        }
      });

      var accion = "";
      $('#grabar').click(function(event) {
        var saltar = 0;
        $("#doc_sust_detalle .conc_l").each(function() {
          if ($(this).val() * 1 == -100) {
            alert('ERROR: Debe seleccionar un concepto.');
            saltar = 1;
            return false;
          }
        });
        if (saltar == 1) {
          return false; // Sale de la secuencia principal
        }
        $('#liquidacion #txtTipoCambioLiq').removeAttr("disabled");
        accion = "grabar";
      });

      $('#enviar').click(function(event) {
        accion = "enviar";
        var montoDev = $('#mon_saldo_s').html();

        if (montoDev * 1 > 0 && is_dua != 1) {
          alert('ALERTA: El monto liquidado debe ser mayor o igual al monto solicitado.');
          return false;
        }

        var ruc_act = -1;
        var ruc_req = -1;
        var tipo_doc = -1;
        var saltar = -1;
        var saltoDistribucion = -1;
        $("#doc_sust_detalle .conc_l").each(function() {
          if ($(this).val() * 1 == -100) {
            alert('ERROR: Debe seleccionar un concepto.');
            saltar = 1;
            return false;
          }

          var id = $(this).attr("id");
          id = id.replace("conc_l", "");
          if (!validarDistribucion(id)) {
            saltoDistribucion = 1;
          }
        });

        if (saltar == 1) {
          return false; // Sale de la secuencia principal
        }
        if (bandera_revision_datos == false)
          if (saltoDistribucion == 1) {
            if (!confirm('A\u00fan no completa la distribuci\u00f3n contable, desea continuar?')) {
              return false;
            }
          }


        $('#liquidacion .prov_act').each(function() {
          ruc_act = (parseInt($(this).val())) || 0;
          ruc_req = parseInt($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').children('option:selected').attr('rucreq'));
          tipo_doc = parseInt($(this).parent().parent().children('.tipo_doc_td').children('.tipo_doc').children('option:selected').val());
          if (ruc_act == 0 && ruc_req == 1 && tipo_doc != 1) {
            alert('ERROR: Existen documentos con RUC inactivo, no se puede continuar.\nVerifique que este seleccionado correctamente el tipo de documento\no cambielo a Otros.');
            saltar = 1;
            return false; // Solo sale del loop each
          } else if (ruc_act == -1 && ruc_req == 1) {
            alert('ERROR: Existen documentos con RUC invalido, no se puede continuar.\nVerifique que este seleccionado correctamente el tipo de documento\no cambielo a Recibo de gastos.');
            saltar = 1;
            return false; // Solo sale del loop each
          }
        });
        if (saltar == 1) {
          return false; // Sale de la secuencia principal
        }
        //                        var duaId = document.getElementById("dua").value;
        //                        if (duaId*1 == -1){
        //                            alert('ERROR: Debe seleccionar una DUA válida.');
        //                            return false;
        //                        }

        var total_liq = parseFloat($('#tot_mon_liq').val());

        if (total_liq == 0) {
          var valor = confirm('ALERTA: El monto del total liquidado es cero. Est\u00e1 seguro de continuar?.');
          if (valor == false) {
            return false;
          }
        }
        if (bandera_revision_datos == false)
          if (!confirm('Est\u00e1 seguro de enviar la liquidacion?')) {
            return false;
          }
        $('#liquidacion #txtTipoCambioLiq').removeAttr("disabled");
      });

      $('#buscar_orden').click(function(event) {
        $("#dialog-confirm").dialog("open");
        $("#detalle_orden").html('');
        var fecha_orden = $('#fecha_orden2').val();
        var numero_orden = $('#numero_orden').val();
        $.ajax({
          url: 'func_orden_trabajo.php',
          type: 'POST',
          data: {
              parametro1: fecha_orden,
              parametro2: numero_orden
          },
          success: function(response) {
              var arrayData = JSON.parse(response);
              var tableHTML = '';
              arrayData.forEach(function(element, i) {
                tableHTML += '<tr>';
                tableHTML +=  '<td width=5% hidden><input type="hidden" id="ordenTrabajoId'+i+'" name="ordenTrabajoId'+i+'" value="'+element[0]+'">'+element[0]+'</td>';
                tableHTML +=  '<td width=5%>'+element[1]+'</td>';
                tableHTML +=  '<td width=5%>'+element[2]+'</td>';
                tableHTML +=  '<td width=5%>'+element[3]+'</td>';
                tableHTML +=  '<td width=5%><input type="hidden" id="orden_trabajo_'+i+'" name="orden_trabajo_'+i+'" value="'+element[0]+'"><input type="hidden" id="orden_trabajo_desc_'+i+'" name="orden_trabajo_desc_'+i+'" value="'+element[1]+'"><img class="orden_trabajo" src="img/plus.png"  onclick="agregar_orden_trabajo('+i+')" title="Agregar Orden de Trabajo"></td>';
                tableHTML += '</tr>';
              });
              $("#detalle_orden").html(tableHTML);
              $("#dialog-confirm").dialog("close");
          },
          error: function(error) {
              console.error('Error:', error);
              $("#dialog-confirm").dialog("close");
          }
      });
      });

      $.validator.addMethod("validaslash", function(value, element) {
        // Valida que la cadena contenga un slash
        return this.optional(element) || /^([a-z0-9&oacute;&oacute;]+){1}((\s|\/)[a-z0-9&oacute;&oacute;]+)+$/i.test(value);
      }, "La glosa debe estar en el formato: ORIGEN/DESTINO");

      let contadorDocumentosValidar = 0;
      let dataDocument = $(".tipo_doc_td .tipo_doc");
      $.each(dataDocument, function(indexDoc, itemDoc) {
        let validacionSunat = dataDocument[indexDoc].options[dataDocument[indexDoc].selectedIndex].getAttribute('aplvalidacion');
        let validacionSgi = dataDocument[indexDoc].options[dataDocument[indexDoc].selectedIndex].getAttribute('aplvalidacionsgi');
        if (validacionSunat == 1 || validacionSgi == 1) {
          contadorDocumentosValidar++;
        }
      });
      // jQuery plugin to prevent double submission of forms
      jQuery.fn.preventDoubleSubmission = function() {
        $(this).on('submit', function(e) {
          var $form = $(this);
          if ($form.data('submitted') === true) {
            // Previously submitted - don't submit again
            e.preventDefault();
          } else if (bandera_revision_datos == false && accion == "enviar" && contadorDocumentosValidar > 0) {
            e.preventDefault();
            // if ($form.valid()) {
            $form.data('submitted', false);
            $("#dialog-confirm").dialog("open");
            let dataPost = $('#liquidacion').serialize();
            $.ajax({
              url: "validar_comprobante_pago.php",
              dataType: "JSON",
              data: dataPost,
              type: 'POST',
              success: function(dataRespuesta) {
                $("#dialog-confirm").dialog("close");
                var bandera_respuesta = true;
                if (!isEmpty(dataRespuesta)) {
                  let htmlDetalle = "";
                  $.each(dataRespuesta, function(indexRespuesta, itemRespuesta) {
                    if (itemRespuesta.vout_estado == 0) {
                      htmlDetalle = htmlDetalle + "<tr>";
                      htmlDetalle = htmlDetalle + "<td>" + itemRespuesta.ruc + "</td>";
                      htmlDetalle = htmlDetalle + "<td>" + itemRespuesta.documentoTipoDescripcion + "</td>";
                      htmlDetalle = htmlDetalle + "<td>" + itemRespuesta.serie + "</td>";
                      htmlDetalle = htmlDetalle + "<td>" + itemRespuesta.numero + "</td>";
                      htmlDetalle = htmlDetalle + "<td>" + itemRespuesta.vout_mensaje + "</td>";
                      htmlDetalle = htmlDetalle + "</tr>";

                      bandera_respuesta = false;
                    }
                  });
                  /*if (bandera_respuesta == false) {
                    $("#table_respuesta_validacion tbody").html(htmlDetalle);
                    document.querySelector('#modalRespuestaValidacion').style.display = 'block';
                  }
                  if (bandera_respuesta) {*/
                    bandera_revision_datos = true;
                    setTimeout(function() {
                      $("#enviar").click();
                    }, 600);
                  //}
                }
              },
              error: function(jqXHR, status, error) {
                $("#dialog-confirm").dialog("close");
                // var bandera_respuesta = false;
              },
            });

            /*
            $.getJSON('validar_comprobante_pago.php',dataPost, function (dataRespuesta) {
                $("#dialog-confirm").dialog("close");
                var bandera_respuesta = true;
                if(!isEmpty(dataRespuesta)){
                    let htmlDetalle = "";
                    $.each(dataRespuesta, function (indexRespuesta, itemRespuesta) {
                        if(itemRespuesta.vout_estado == 0){
                            htmlDetalle = htmlDetalle + "<tr>";
                            htmlDetalle = htmlDetalle + "<td>"+itemRespuesta.ruc  +"</td>";
                            htmlDetalle = htmlDetalle + "<td>"+itemRespuesta.documentoTipoDescripcion  +"</td>";
                            htmlDetalle = htmlDetalle + "<td>"+itemRespuesta.serie  +"</td>";
                            htmlDetalle = htmlDetalle + "<td>"+itemRespuesta.numero  +"</td>";
                            htmlDetalle = htmlDetalle + "<td>"+itemRespuesta.vout_mensaje  +"</td>";
                            htmlDetalle = htmlDetalle + "</tr>";

                            bandera_respuesta = false;
                        }
                    });
                    if(bandera_respuesta == false){
                        $("#table_respuesta_validacion tbody").html(htmlDetalle);
                        document.querySelector('#modalRespuestaValidacion').style.display = 'block';
                    }
                }
                if(bandera_respuesta){
                    bandera_revision_datos = true;
                    setTimeout(function(){
                        $("#enviar").click();
                    }, 600);
                }

            });*/
            // }
          } else {
            // Mark it so that the next submit can be ignored
            if ($form.valid()) {
              $("#dialog-confirm").dialog("open");
              $form.data('submitted', true);
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

    function abrirModalDistribucion(fila, tipo) {
      $("#distribucion_body tr.fila_dato").remove();
      dist_fila = fila;
      dist_tipo = tipo;
      if (obtenerMontoTotalesXFila(fila) > 0) {
        cargarDistribucion(fila);
        document.querySelector('#modalDistribucion').style.display = 'block';
      } else {
        alert('Primero debe ingresar los montos para llenar la distribucion contable.');
      }
    }

    function abrirModalOrdenTrabajo(fila) {
      document.querySelector('#modalOrdenTrabajo').style.display = 'block';
      document.getElementById("index_id").value = fila;
      $("#detalle_orden").html('');
      $("#fecha_orden").val("");
      $("#fecha_orden2").val("");
    }
    function agregar_orden_trabajo(index){
      cerrarModal('modalOrdenTrabajo');
      var idextemp = $("#index_id").val();
      idextemp = idextemp.replace('[','\\[').replace(']','\\]');
      var dataor = document.getElementById('orden_trabajo_desc_'+index).value;
      var dataorId = document.getElementById('ordenTrabajoId'+index).value;
      $("#des_orden_trabajo"+idextemp).text(dataor);//revisar
      $("#lid_orden_trabajo"+idextemp).val(dataorId);//revisar
    }

    function obtenerMontoTotalesXFila(fila) {
      var montoafecto = $("input[name='afecto_inp" + fila + "']").val();
      var montonoafecto = $("input[name='noafecto_inp" + fila + "']").val();
      var montootro = $("input[name='montOtro_inp" + fila + "']").val();
      var montoicbp = $("input[name='montIcbp_inp" + fila + "']").val();

      const inputIGV = document.getElementById(`afecto_sel${fila}`).value;
      const cantIGV = 1 + ((inputIGV == '2' || inputIGV == '5') ? 0.10 : 0.18);

      montoafecto = (!isEmpty(montoafecto) ? (montoafecto * 1) / cantIGV : 0);
      montonoafecto = (!isEmpty(montonoafecto) ? montonoafecto * 1 : 0);
      montootro = (!isEmpty(montootro) ? montootro * 1 : 0);
      montoicbp = (!isEmpty(montoicbp) ? montoicbp * 1 : 0);

      return redondearDosDecimales(montoafecto + montonoafecto + montootro + montoicbp) * 1;
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
      var dataDistribucion = document.getElementById("lid_distribucion" + fila).value;
      var combo = document.getElementById("tipo_doc" + fila);
      var nombre_documento = combo.options[combo.selectedIndex].text;
      if (combo.options[combo.selectedIndex].getAttribute('apldistri') == '0') {
        return true;
      }
      var serie_documento = $("input[name='ser_doc" + fila + "']").val();
      var correlativo_documento = $("input[name='num_doc" + fila + "']").val();

      var documento_seleccionado = (!isEmpty(nombre_documento) ? nombre_documento + " : " : "") + (!isEmpty(serie_documento) ? serie_documento + " - " : "") + (!isEmpty(correlativo_documento) ? correlativo_documento : "");
      if (isEmpty(dataDistribucion)) {
        //                    alert('Aun no llena la distribucion contable para '+ documento_seleccionado);
        return false;
      }
      var data = JSON.parse(dataDistribucion);
      var sumaPorcentaje = 0;
      var sumaMontos = 0;
      var bandera_error = false;
      $.each(data, function(index, item) {
        if (isEmpty(item.centro_costo) && is_dua != 1) {
          //                        alert('Aun no selecciona el centro de costo que corresponde en la fila '+(index+1)+' para ' + documento_seleccionado);
          bandera_error = true;
          return false;
        }

        if (isEmpty(item.cuenta_contable)) {
          //                        alert('Aun no selecciona la cuenta contable que corresponde en la fila '+(index+1)+' para ' + documento_seleccionado);
          bandera_error = true;
          return false;
        }

        if (isEmpty(item.porcentaje) || item.porcentaje * 1 <= 0) {
          //                        alert('El porcentaje debe ser mayor que cero en la fila '+(index+1)+' para ' + documento_seleccionado);
          bandera_error = true;
          return false;
        }

        sumaPorcentaje += ((item.porcentaje * 1).toFixed(2)) * 1;

        if (isEmpty(item.monto) || item.monto * 1 <= 0) {
          //                        alert('El porcentaje debe ser mayor que cero en la fila '+(index+1)+' para ' + documento_seleccionado);
          bandera_error = true;
          return false;
        }

        sumaMontos += ((item.monto * 1).toFixed(2)) * 1;
      });

      if (bandera_error) {
        return false;
      }

      if (sumaMontos.toFixed(2) != obtenerMontoTotalesXFila(fila)) {
        //                    alert('El total de los montos ingresados deben ser igual a ' + obtenerMontoTotalesXFila(fila) + ' para ' + documento_seleccionado);
        return false;
      }

      if (sumaPorcentaje.toFixed(2) != 100) {
        //                    alert('El total de los porcentajes ingresados deben ser igual a 100% para ' + documento_seleccionado );
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
        //                    console.log(m_fila);
        var m_centro_costo = $(this).children('td.centro_costo_td').children('select').val();
        if (isEmpty(m_centro_costo) && is_dua != 1) {
          alert('Aun no selecciona el centro de costo que corresponde en la fila ' + m_fila);
          bandera_error = true;
          return false;
        }

        var m_cuenta_contable = $(this).children('td.cuenta_contable_td').children('select').val();
        //                    if (isEmpty(m_cuenta_contable)) {
        //                        alert('Aun no selecciona la cuenta contable que corresponde en la fila ' + m_fila);
        //                        bandera_error = true;
        //                        return false;
        //                    }

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

    function cargarDistribucion(fila) {
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
          s_add += '<td class="indice_distribucion_td">' + indice + '</td>';
          s_add += '<td class="cuenta_contable_td">' + cuenta_contable_t + '</td>'; //Cuenta Contable
          s_add += (is_dua != 1 ? '<td class="centro_costo_td">' + centro_costo_t + '</td>' : ''); //Centro Costo
          s_add += '<td class="porcentaje_td"><input type="number" value="' + (!isEmpty(item.porcentaje) ? (item.porcentaje * 1).toFixed(2) : 0) + '" min="0.1" max="100" id="porcentaje_distribucion[{0}]" name="porcentaje_distribucion[{0}]" onkeyup="obtenerMontoXPorcentaje(\'[{0}]\',\'' + fila + '\')"></td>';
          s_add += '<td class="monto_td"><input type="number" value="' + (!isEmpty(item.monto) ? (item.monto * 1).toFixed(2) : 0) + '" min="0.1" id="monto_distribucion[{0}]" name="monto_distribucion[{0}]" onkeyup="obtenerPorcentajeXMonto(\'[{0}]\',\'' + fila + '\')"></td>';
          s_add += '<td>' + dele + '</td>';
          s_add += '</tr>';
          var template = jQuery.validator.format(s_add);
          $tbl.after($(template(i++)));

          document.getElementById("cuenta_contable[" + identificador + "]").value = item.cuenta_contable;
          if (is_dua != 1) {
            document.getElementById("centro_costo[" + identificador + "]").value = item.centro_costo;
          }
          indice--;
        });
      } else {
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
      document.getElementById("cuenta_contable[" + identificador + "]").value = "";
      if (is_dua != 1) {
        document.getElementById("centro_costo[" + identificador + "]").value = "";
      }
    }
  </script>

  <style>
    #tg1,
    #tg2,
    #tg3,
    #tg4,
    #tipo_doc_template,
    #tipo_doc_nota_template,
    #tipo_mon_template,
    #ruc_nro_template,
    #dist_gastos,
    #bandera {
      display: none;
    }

    #bole_conc_template,
    #alim_conc_template,
    #hosp_conc_template,
    #movi_conc_template,
    #gast_conc_template,
    #otro_conc_template,
    #veh_template,
    #otro_conc_template_dua,
    #centro_costo_template,
    #cuenta_contable_template {
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
    }

    .iconos,
    .modal,
    .dele,
    .tipo_nota,
    .dist_gast_dele,
    #dist_gast_add {
      vertical-align: text-top;
      cursor: pointer;
    }

    .dist_gast_info,
    .dist_gast_tipo {
      vertical-align: text-top;
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

    .no-close .ui-dialog-titlebar-close {
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

  <h1>Registrar liquidaci&oacute;n <?php echo strtolower($zona_nom); ?></h1>

  <form id="liquidacion" action="ear_liq_registro_p.php" method="post" enctype="multipart/form-data;charset=UTF-8">

    <table>
      <tr>
        <td>N&uacute;mero Liquidaci&oacute;n</td>
        <td><?php echo $ear_numero; ?></td>
      </tr>
      <tr>
        <td>Fecha Solicitud</td>
        <td><?php echo $ear_sol_fec; ?></td>
      </tr>
      <tr>
        <td>Estado Solicitud</td>
        <td><?php echo $est_nom . $sol_msj; ?></td>
      </tr>
      <tr>
        <td>Nombre</td>
        <td><?php echo $ear_tra_nombres . (is_null($master_usu_id) ? "" : " (Registrado por " . getUsuarioNombre($master_usu_id) . ")"); ?></td>
      </tr>
      <tr>
        <td>DNI</td>
        <td><?php echo $ear_tra_dni; ?></td>
      </tr>
      <!--<tr><td>Cargo</td><td><?php // echo $ear_tra_cargo;
                                ?></td></tr>-->
      <!--<tr><td>Area</td><td><?php // echo $ear_tra_area;
                                ?></td></tr>-->
      <!--<tr><td>Sucursal</td><td><?php // echo $ear_tra_sucursal;
                                    ?></td></tr>-->
      <tr>
        <td>Fecha de Liquidacion</td>
        <td>
          <?php
          //                    $fechaLiqArray = explode('-', $ear_liq_fec);
          //                    $fechaLiqFormato = $fechaLiqArray[2] . '/' . $fechaLiqArray[1] . '/' . $fechaLiqArray[0];
          //                    if ($isDua) {
          //                        $tcFechaLiq =getTipoCambio(2, $ear_liq_fec);
          ?>
          <!--                    <input type="text" value="<?php // echo $fechaLiqFormato;
                                                            ?>" size="11" maxlength="10" class="fecha_inp" name="fecha_liquidacion" id="fecha_liquidacion" readonly onchange="obtenerTipoCambioXFechaLiq()">
                            <span style="cursor: pointer" onclick="obtenerTipoCambioXFechaLiq()"><img src="img/reload.gif" border="0" title="Actualizar tipo de cambio" class="iconos"></img></span>                        -->
          <?php
          //                    } else {
          echo $ear_liq_fec;
          //                    }
          ?>
        </td>
      </tr>
      <tr>
        <td>Monto Solicitado</td>
        <td><?php echo $ear_monto; ?></td>
      </tr>
      <tr>
        <td>Moneda</td>
        <td><?php echo "$mon_nom ($mon_simb) ($mon_iso) <img src='$mon_img' style='vertical-align:text-top'>"; ?></td>
      </tr>
      <?php
      //                    if ($isDua) {
      //                        if($tipoCambioFechaLiq==null){
      //                            $tipoCambioFechaLiq=$tcFechaLiq;
      //                        }
      //                        $checkedHtml='checked';
      //                        if($guardarTcSgi==1){
      //                            $checkedHtml='checked';
      //                        }else{
      //                            $checkedHtml='';
      //                        }
      ?>
      <!--            <tr><td>
                        TC Personalizado
                        <input type="checkbox" name="chkTipoCambio" id="chkTipoCambio" title="Si el check est&aacute; activo se va a guardar el tipo de cambio en los documentos de SGI"  style="transform: scale(1.2);"  <?php // echo $checkedHtml;
                                                                                                                                                                                                                          ?> onchange="onChangeCheckTipoCambio()" ></input>
                    </td>
                    <td>
                        <input type="text" value="<?php // echo round($tipoCambioFechaLiq, 4);
                                                  ?>" size="5" maxlength="9" id="txtTipoCambioLiq" class="valid" name="txtTipoCambioLiq" disabled></input>
                    </td>
                </tr>-->
      <?php // }
      ?>
      <tr>
        <td>Motivo</td>
        <td><?php echo $ear_sol_motivo; ?></td>
      </tr>
      <!--<tr><td>Numero de cuenta<br>para la transferencia</td><td><?php // echo $ear_tra_cta;
                                                                    ?></td></tr>-->
      <!--<tr><td>Distrib. de gasto<br>por defecto</td><td><span id="dist_gast_msj"><?php // echo substr(getDistGastTemplate($lid_gti_def, $lid_dg_json_def, "def"), 6, -8);
                                                                                    ?></span></td></tr>-->

      <?php
      if ($isDua) {
        //                echo $dua_id;
      ?>
        <tr>
          <td>DUA *</td>
          <td>
            <?php
            $duaSerie = $dua_serie;
            $duaNumero = $dua_numero;
            if ($dua_id != null) {
              $arrDua = getDuaXDuaId($dua_id);
              $duaSerie = $arrDua[0][4];
              $duaNumero = $arrDua[0][5];
            }
            ?>

            <input type="text" size="4" maxlength="4" id="dua_serie" name="dua_serie" value="<?php echo $duaSerie; ?>" />
            <input type="text" size="8" maxlength="8" id="dua_numero" name="dua_numero" value="<?php echo $duaNumero; ?>" />
          </td>
          <!--                <td>
                            <select id="dua" name="dua" onchange="onChangeDua()">
                        <?php
                        //                    echo "<option value='-1'>Seleccione DUA</option>\n";
                        //                    $arr = getDua($id);
                        //                    foreach ($arr as $v) {
                        //                        $usado=($v[6]*1 == 1)?"*":"";
                        //                        $selected = ($v[0]*1 == $dua_id*1)?"selected":"";
                        //                    echo "\t\t\t\t<option value='$v[0]' $selected>".$v[4]."-".$v[5]." | ".$v[3]." ".$usado."</option>\n";
                        //                    }
                        ?>
                            </select>

                        </td>-->
        </tr>
      <?php
      }
      ?>
    </table>

    <!--<form id="liquidacion" action="ear_liq_registro_p.php" method="post" enctype="multipart/form-data;charset=UTF-8">-->

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
        <td>
          <div id='divsubt01'>
            <div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>
          </div>
        </td>
      </tr>
      <tr>
        <td>02</td>
        <td>Alimentacion / Pension</td>
        <td align='right' id='solsubt02'><?php echo isset($arrSolSubt['02']) ? $arrSolSubt['02'] : '0.00'; ?></td>
        <td align='right' id='liqsubt02'>0.00</td>
        <td>
          <div id='divsubt02'>
            <div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>
          </div>
        </td>
      </tr>
      <tr>
        <td>03</td>
        <td>Hospedaje</td>
        <td align='right' id='solsubt03'><?php echo isset($arrSolSubt['03']) ? $arrSolSubt['03'] : '0.00'; ?></td>
        <td align='right' id='liqsubt03'>0.00</td>
        <td>
          <div id='divsubt03'>
            <div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>
          </div>
        </td>
      </tr>
      <tr>
        <td>04</td>
        <td>Movilidad / Combustible</td>
        <td align='right' id='solsubt04'><?php echo isset($arrSolSubt['04']) ? $arrSolSubt['04'] : '0.00'; ?></td>
        <td align='right' id='liqsubt04'>0.00</td>
        <td>
          <div id='divsubt04'>
            <div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>
          </div>
        </td>
      </tr>
      <!--<tr>
                            <td>05</td>
                            <td>Gastos de Representaci&oacute;n</td>
                            <td align='right' id='solsubt05'><?php // echo isset($arrSolSubt['05']) ? $arrSolSubt['05'] : '0.00';
                                                              ?></td>
                            <td align='right' id='liqsubt05'>0.00</td>
                            <td><div id='divsubt05'><div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div></div></td>
                    </tr>-->
      <tr>
        <td>05</td>
        <td>Otros</td>
        <td align='right' id='solsubt06'><?php echo isset($arrSolSubt['06']) ? $arrSolSubt['06'] : '0.00'; ?></td>
        <td align='right' id='liqsubt06'>0.00</td>
        <td>
          <div id='divsubt06'>
            <div style="font-weight:bold;color:white;background-color:green; text-align:center; width:100px;">Normal</div>
          </div>
        </td>
      </tr>
    </table>

    <br>

    <div>Detalle de los documentos sustentatorios:</div>
    <table border="1" id="doc_sust_detalle">
      <tbody id="bole_body">
        <tr>
          <td colspan="23">Boletos de Viaje / Pasajes A&eacute;reos</td>
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
          <td class="encabezado_h">Monto Otros</td>
          <td class="encabezado_h">Monto ICBP</td>
          <td class="encabezado_h" title="Tipo de cambio">T/C</td>
          <td class="encabezado_h">Conversi&oacute;n Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n no Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n Otros</td>
          <td class="encabezado_h">Conversi&oacute;n Monto ICBP</td>
          <td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
          <td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
          <!--<td class="encabezado_h" title="Distribuci&oacute;n de gasto">Dist de Gasto</td>-->
          <td class="encabezado_h">Acciones</td>
        </tr>
        <?php echo getFilasPrevias($arrLiqDet, $mon_id, '01', 1, 1, $arrDistribucionDetalle,1); ?>
      </tbody>
      <tbody>
        <tr>
          <td colspan="23">Nueva fila Boletos de Viaje / Pasajes A&eacute;reos
            <img src="img/plus.png" id="bole_add" title="Agregar" class="iconos">
          </td>

        </tr>
      </tbody>

      <tr>
        <td colspan="23">&nbsp;</td>
      </tr>

      <tbody id="alim_body">
        <tr>
          <td colspan="23">Alimentacion / Pension</td>
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
          <td class="encabezado_h">Monto Otros</td>
          <td class="encabezado_h">Monto ICBP</td>
          <td class="encabezado_h" title="Tipo de cambio">T/C</td>
          <td class="encabezado_h">Conversi&oacute;n Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n no Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n Otros</td>
          <td class="encabezado_h">Conversi&oacute;n Monto ICBP</td>
          <td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
          <td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
          <!--	<td class="encabezado_h" title="Distribuci&oacute;n de gasto">Dist de Gasto</td>-->
          <td class="encabezado_h">Acciones</td>
        </tr>
        <?php echo getFilasPrevias($arrLiqDet, $mon_id, '02', 1, 1, $arrDistribucionDetalle,1); ?>
      </tbody>
      <tbody>
        <tr>
          <td colspan="23">Nueva fila Alimentacion / Pension <img src="img/plus.png" id="alim_add" title="Agregar" class="iconos"></td>
        </tr>
      </tbody>

      <tr>
        <td colspan="23">&nbsp;</td>
      </tr>

      <tbody id="hosp_body">
        <tr>
          <td colspan="23">Hospedaje</td>
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
          <td class="encabezado_h">Monto Otros</td>
          <td class="encabezado_h">Monto ICBP</td>
          <td class="encabezado_h" title="Tipo de cambio">T/C</td>
          <td class="encabezado_h">Conversi&oacute;n Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n no Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n Monto Otros</td>
          <td class="encabezado_h">Conversi&oacute;n Monto ICBP</td>
          <td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
          <td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
          <!--	<td class="encabezado_h" title="Distribuci&oacute;n de gasto">Dist de Gasto</td>-->
          <td class="encabezado_h">Acciones</td>
        </tr>
        <?php echo getFilasPrevias($arrLiqDet, $mon_id, '03', 1, 1, $arrDistribucionDetalle,1); ?>
      </tbody>
      <tbody>
        <tr>
          <td colspan="23">Nueva fila Hospedaje <img src="img/plus.png" id="hosp_add" title="Agregar" class="iconos"></td>
        </tr>
      </tbody>

      <tr>
        <td colspan="23">&nbsp;</td>
      </tr>

      <tbody id="movi_body">
        <tr>
          <td colspan="23">Movilidad / Combustible</td>
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
          <td class="encabezado_h">Monto Otros</td>
          <td class="encabezado_h">Monto ICBP</td>
          <td class="encabezado_h" title="Tipo de cambio">T/C</td>
          <td class="encabezado_h">Conversi&oacute;n Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n no Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n Monto Otros</td>
          <td class="encabezado_h">Conversi&oacute;n Monto ICBP</td>
          <td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
          <td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
          <!--	<td class="encabezado_h" title="Distribuci&oacute;n de gasto">Dist de Gasto</td>-->
          <td class="encabezado_h">Acciones</td>
        </tr>
        <?php
        if (!is_null($pla_id))
          echo getFilaPlanillaMovilidad($pla_id, $mon_id, 1, $rec_usu_nombre, $adm_usu_gco_cobj);
        else
          echo getFilaVaciaPlaMov(1);
        ?>
        <?php echo getFilasPrevias($arrLiqDet, $mon_id, '04', 1, 1, $arrDistribucionDetalle,1); ?>
      </tbody>
      <tbody>
        <tr>
          <td colspan="23">Nueva fila Movilidad / Combustible <img src="img/plus.png" id="movi_add" title="Agregar" class="iconos"></td>
        </tr>
      </tbody>

      <!--GASTOS DE REPRESENTACION NO SE MUESTRA-->
      <tr style="display: none;">
        <td colspan="23">&nbsp;</td>
      </tr>

      <tbody id="gast_body" style="display: none;">
        <tr>
          <td colspan="23">Gastos de Representaci&oacute;n</td>
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
          <td class="encabezado_h">Monto Otros</td>
          <td class="encabezado_h">Monto ICBP</td>
          <td class="encabezado_h" title="Tipo de cambio">T/C</td>
          <td class="encabezado_h">Conversi&oacute;n Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n no Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n Monto Otros</td>
          <td class="encabezado_h">Conversi&oacute;n Monto ICBP</td>
          <td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
          <td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
          <!--	<td class="encabezado_h" title="Distribuci&oacute;n de gasto">Dist de Gasto</td>-->
          <td class="encabezado_h">Acciones</td>
        </tr>
        <?php echo getFilasPrevias($arrLiqDet, $mon_id, '05', 1, 1, $arrDistribucionDetalle,1); ?>
      </tbody>
      <tbody style="display: none;">
        <tr>
          <td colspan="23">Nueva fila Gastos de Representaci&oacute;n <img src="img/plus.png" id="gast_add" title="Agregar" class="iconos"></td>
        </tr>
      </tbody>

      <tr>
        <td colspan="23">&nbsp;</td>
      </tr>

      <tbody id="otro_body">
        <tr>
          <td colspan="23">Otros</td>
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
          <td class="encabezado_h">Monto Otros</td>
          <td class="encabezado_h">Monto ICBP</td>
          <td class="encabezado_h" title="Tipo de cambio">T/C</td>
          <td class="encabezado_h">Conversi&oacute;n Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n no Afecto</td>
          <td class="encabezado_h">Conversi&oacute;n Monto Otros</td>
          <td class="encabezado_h">Conversi&oacute;n Monto ICBP</td>
          <td class="encabezado_h">Efectu&oacute; Retenci&oacute;n Detracci&oacute;n</td>
          <td class="encabezado_h" title="Monto de retenci&oacute;n o detracci&oacute;n">Monto Ret / Detr</td>
          <!--	<td class="encabezado_h" title="Distribuci&oacute;n de gasto">Dist de Gasto</td>-->
          <td class="encabezado_h">Acciones</td>
        </tr>
        <?php echo getFilasPrevias($arrLiqDet, $mon_id, '06', 1, 1, $arrDistribucionDetalle,1); ?>
      </tbody>
      <tbody>
        <tr>
          <td colspan="23">Nueva fila Otros <img src="img/plus.png" id="otro_add" title="Agregar" class="iconos"></td>
        </tr>
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
        <td align="right">Resultado: <span id="resul_msg"><?php echo $resul_msg; ?></span></td>
        <td class="calc_span"><span id="resul_inp_s"><?php echo number_format($resul_inp_s, 2, '.', ''); ?></span><input type="hidden" name="resul_inp" id="resul_inp" value="<?php echo $ear_liq_dcto; ?>"></td>
        <td><?php echo $mon_nom; ?></td>
      </tr>
    </table>

    <p>Nota: Los montos calculados son referenciales y podr&aacute;n ser reajustados por la administraci&oacute;n.</p>

    <div style="display:none;" id="advertencia">
      <div style="background-image:url('img/yell_bl.gif'); height:28px; width:100%;"></div>
      <div style="background-color:yellow; text-align:center; font-weight:bold; width:100%;">PRECAUCION: Se han excedido los montos permitidos diarios de la planilla de movilidad.</div>
      <br>
    </div>

    <div style="display:none;" id="advertencia2">
      <div style="background-image:url('img/yell_bl.gif'); height:28px; width:100%;"></div>
      <div style="background-color:yellow; text-align:center; font-weight:bold; width:100%;">PRECAUCION: Se han excedido los montos permitidos diarios del rubro de alimentacion.</div>
      <br>
    </div>

    <input type="hidden" value="<?php echo $id; ?>" name="id" />
    <input type="hidden" value="<?php echo $lid_gti_def; ?>" name="lid_gti_def" id="lid_gti_def" />
    <input type="hidden" value='<?php echo $lid_dg_json_def; ?>' name="lid_dg_json_def" id="lid_dg_json_def" />
    <input type="submit" value="Grabar liquidacion" name="grabar" id="grabar" disabled />
    <input type="submit" value="Enviar liquidacion" name="enviar" id="enviar" disabled />

    <p>Descripci&oacute;n de los botones:<br>
      <b>Grabar</b> almacena los datos ingresados para continuar despu&eacute;s.<br>
      <b>Enviar</b> transmite la liquidaci&oacute;n a jefatura/gerencia para su revisi&oacute;n y aprobaci&oacute;n (Ya no podr&aacute; hacer m&aacute;s modificaciones).
    </p>

  </form>

  <div id="ruc_nro_template">
    <input type="text" class="ruc_nro_i" size="13" maxlength="11" id="ruc_nro[{0}]" name="ruc_nro[{0}]">
  </div>

  <!--                                    <div id="ruc_nro_nota_template">
                                        <input type="text" class="ruc_nro_i" size="13" maxlength="11" id="ruc_nro[{0}]" name="ruc_nro[{0}]">
                                    </div>-->


  <div id="prov_nom_template">
    <div class="prov_nom_i" id="prov_nom[{0}]" name="prov_nom[{0}]"></div>
    <input type="hidden" class="prov_ret" value="-1">
    <input type="hidden" class="prov_act" value="-1">
  </div>

  <div id="dist_gastos" title="Distribuci&oacute;n de Gastos">
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

  <div id="bole_conc_template">
    <select class="conc_l" id="conc_l[{0}]" name="conc_l[{0}]">
      <?php
      $arr = getLiqConceptos($mon_id, '01');

      foreach ($arr as $v) {
        echo "<option value='$v[0]' conc_id='$v[3]' cve='$v[4]'>$v[1]</option>\n";
      }
      ?>
    </select>
    <input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
    <input type="hidden" class="cve_inp" name="cve[{0}]">
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
        echo "<option value='$v[0]' conc_id='$v[3]' cve='$v[4]'>$v[1]</option>\n";
      }
      ?>
    </select>
    <input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
    <input type="hidden" class="cve_inp" name="cve[{0}]">
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
        echo "<option value='$v[0]' conc_id='$v[3]' cve='$v[4]'>$v[1]</option>\n";
      }
      ?>
    </select>
    <input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
    <input type="hidden" class="cve_inp" name="cve[{0}]">
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
        echo "<option value='$v[0]' conc_id='$v[3]' cve='$v[4]'>$v[1]</option>\n";
      }
      ?>
    </select>
    <input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
    <input type="hidden" class="cve_inp" name="cve[{0}]">
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
        echo "<option value='$v[0]' conc_id='$v[3]' cve='$v[4]'>$v[1]</option>\n";
      }
      ?>
    </select>
    <input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
    <input type="hidden" class="cve_inp" name="cve[{0}]">
    <input type="hidden" class="ret_tasa_inp" name="ret_tasa[{0}]">
    <input type="hidden" class="ret_min_monto_inp" name="ret_min_monto[{0}]">
    <input type="hidden" class="det_tasa_inp" name="det_tasa[{0}]">
    <input type="hidden" class="det_min_monto_inp" name="det_min_monto[{0}]">
  </div>
  <div id="otro_conc_template">
    <select class="conc_l" id="conc_l[{0}]" name="conc_l[{0}]">
      <option value='-100' conc_id='-100' cve='-100'>Seleccione</option>
      <?php
      $arr = getLiqConceptos($mon_id, '06');

      foreach ($arr as $v) {
        echo "<option value='$v[0]' conc_id='$v[3]' cve='$v[4]'>$v[1]</option>\n";
      }
      ?>
    </select>
    <input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
    <input type="hidden" class="cve_inp" name="cve[{0}]">
    <input type="hidden" class="ret_tasa_inp" name="ret_tasa[{0}]">
    <input type="hidden" class="ret_min_monto_inp" name="ret_min_monto[{0}]">
    <input type="hidden" class="det_tasa_inp" name="det_tasa[{0}]">
    <input type="hidden" class="det_min_monto_inp" name="det_min_monto[{0}]">
  </div>

  <!--CONCEPTO PARA OTROS GASTOS CUANDO SELECCIONA DUA-->
  <div id="otro_conc_template_dua">
    <select class="conc_l" id="conc_l[{0}]" name="conc_l[{0}]">
      <option value='-100' conc_id='-100' cve='-100'>Seleccione</option>
      <?php
      $arr = getLiqConceptos($mon_id, '06');

      foreach ($arr as $v) {
        $selected = "";
        if ($v[3] * 1 == 58 || $v[3] * 1 == 57) {
          $selected = "selected";
        }

        echo "<option value='$v[0]' conc_id='$v[3]' cve='$v[4]' $selected >$v[1]</option>\n";
      }
      ?>
    </select>
    <input type="hidden" class="conc_id_inp" name="conc_id[{0}]">
    <input type="hidden" class="cve_inp" name="cve[{0}]">
    <input type="hidden" class="ret_tasa_inp" name="ret_tasa[{0}]">
    <input type="hidden" class="ret_min_monto_inp" name="ret_min_monto[{0}]">
    <input type="hidden" class="det_tasa_inp" name="det_tasa[{0}]">
    <input type="hidden" class="det_min_monto_inp" name="det_min_monto[{0}]">
  </div>
  <!--FIN CONCEPTO PARA OTROS GASTOS CUANDO SELECCIONA DUA-->

  <div id="tipo_doc_template">
    <select class="tipo_doc" id="tipo_doc[{0}]" name="tipo_doc[{0}]">
      <?php
      $arr = getTipoDoc();
      

      foreach ($arr as $v) {
        if ($v[11] == 1) {
          echo "<option value='$v[0]' rucreq='$v[2]' aplret='$v[3]' apldet='$v[4]' taxcode='$v[8]' apldistri='$v[12]' sgidocumentoid='$v[13]' aplvalidacion='$v[14]' aplvalidacionsgi='$v[15]'  " . ($v[0] == "2" ? " selected" : "") . ">$v[1]</option>\n";
        }
      }
      ?>
    </select>
  </div>
  <div id="tipo_doc_nota_template">
    <select class="tipo_doc" id="tipo_doc[{0}]" name="tipo_doc[{0}]">
      <?php
      $arr = getTipoDocNota();

      foreach ($arr as $v) {
        if ($v[11] == 1) {
          echo "<option value='$v[0]' rucreq='$v[2]' aplret='$v[3]' apldet='$v[4]' taxcode='$v[8]' apldistri='$v[12]' sgidocumentoid='$v[13]' aplvalidacion='$v[14]' aplvalidacionsgi='$v[15]' " . ($v[0] == "2" ? " selected" : "") . ">$v[1]</option>\n";
        }
      }
      ?>
    </select>
  </div>


  <div id="tipo_mon_template">
    <select class="tipo_mon" id="tipo_mon[{0}]" name="tipo_mon[{0}]">
      <?php
      $arr = getTipoMon();

      foreach ($arr as $v) {
        echo "<option value='$v[0]'" . ($mon_id == $v[0] ? " selected" : "") . ">$v[1]</option>\n";
      }
      ?>
    </select>
  </div>
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
      $arr = getCuentasContables($isDua);
      foreach ($arr as $cuenta) {
        echo "<option value='$cuenta[0]' " . ($cuenta[3] == 1 ? "disabled" : "") . "  > $cuenta[1]</option>\n";
      }
      ?>
    </select>
  </div>
  <div id="veh_template">
    <span class="peaje_span">Peaje: <input type="text" value="" size="10" maxlength="100" class="peaje_inp" name="peaje[{0}]"></span>
    <select class="veh_l" id="veh_l[{0}]" name="veh_l[{0}]">
      <?php
      $veh_asig = getVehiculoAsignado($usu_id);
      $arr = getVehiculosActivosLista();

      foreach ($arr as $v) {
        echo "<option value='$v[0]'" . ($veh_asig == $v[0] ? " selected" : "") . ">$v[1]</option>\n";
      }
      ?>
      <option value='-1'>Otros</option>
    </select>
    <span class="km_span"><input type="text" value="" size="10" maxlength="10" class="km_inp" name="km[{0}]"> km</span>
  </div>

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
              <td class="encabezado_h" width=30%>Cuenta Contable</td>
              <?php echo (!$isDua ? ' <td class="encabezado_h" width=30%>Centro Costo</td>' : '') ?>
              <td class="encabezado_h" width=15%>Porcentaje(%)</td>
              <td class="encabezado_h" width=15%>Monto</td>
              <td class="encabezado_h" width=5%>Borrar</td>
            </tr>
          </tbody>
          <tbody>
            <tr>
              <td colspan="<?php echo (!$isDua ? '6' : '5') ?>">Nueva fila <img src="img/plus.png" title="Agregar" class="iconos" onclick="agregarFilaDistribucion();" /></td>
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

  <div id="modalOrdenTrabajo" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <span class="close-modal" onclick="cerrarModal('modalOrdenTrabajo');">&times;</span>
        <h2>Selecciona Orden de compra</h2>
      </div>
      <div class="modal-body">
        <input type="hidden" id="index_id"name="index_id">
        <table>
          <tr>
            <td>Fecha</td>
            <td><input type="text" id="fecha_orden" name="fecha_orden" readonly /><input type="hidden" id="fecha_orden2" name="fecha_orden2" /></td>
          </tr>
          <tr>
            <td>N&uacute;mero de Orden</td>
            <td>
              <input type="text" id="numero_orden" name="numero_orden" />
            </td>
          </tr>
          <tr>
            <td>
              <input type="submit" value="Buscar Orden de Trabajo" name="buscar_orden" id="buscar_orden">
            </td>
          </tr>
          <tr>
        </table>
        </br>
        </br>
        <table border="1" id="table_orden" cellpadding="0" cellspacing="0" border="0" class="display" width="100%">
          <tbody>
            <tr>
              <td class="encabezado_h" width=5% hidden>id</td>
              <td class="encabezado_h" width=30%>Correlativo</td>
              <td class="encabezado_h" width=30%>Cliente</td>
              <td class="encabezado_h" width=30%>Descripcion</td>
              <td class="encabezado_h" width=30%>Acción</td>
            </tr>
          </tbody>
          <tbody id="detalle_orden">
              <?php
              /*$arr = getOrdenTrabajo();
              foreach ($arr as $a => $v) {
                echo '<tr>';
                echo '<td width=5%><input type="hidden" id="ordenTrabajoId'.$a.'" name="ordenTrabajoId'.$a.'" value="'.$v[0].'">'.$v[0].'</td>';
                echo '<td width=5%>'.$v[1].'</td>';
                echo '<td width=5%>'.$v[2].'</td>';
                echo '<td width=5%>'.$v[3].'</td>';
                echo '<td width=5%><input type="hidden" id="orden_trabajo_'.$a.'" name="orden_trabajo_'.$a.'" value="'.$v[0].'"><input type="hidden" id="orden_trabajo_desc_'.$a.'" name="orden_trabajo_desc_'.$a.'" value="'.$v[1].'"><img class="orden_trabajo" src="img/plus.png"  onclick="agregar_orden_trabajo('.$a.')" title="Agregar Orden de Trabajo"></td>';
                echo '</tr>';
              }*/
              ?>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button id="btnCerrarModal" class="button-danger" onclick="cerrarModal('modalOrdenTrabajo');">Cerrar</button>
      </div>
    </div>
  </div>

  <div id="modalRespuestaValidacion" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <span class="close-modal" onclick="cerrarModal('modalRespuestaValidacion');">&times;</span>
        <h2>Validaci&oacute;n de documento</h2>
      </div>
      <div class="modal-body">
        <table border="1" id="table_respuesta_validacion" width=100%>
          <thead>
            <tr>
              <th>Proveedor</th>
              <th>Tipo Doc</th>
              <th>Serie</th>
              <th>Numero</th>
              <th>Error</th>
            </tr>
          </thead>
          <tbody>

          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button id="btnCerrarModal" class="button-danger" onclick="cerrarModal('modalRespuestaValidacion');">Cerrar</button>
        <!--<button id="btnGuardarModal" class="button-info" onclick="guardarDistribucionContable();">Guardar</button>-->
      </div>
    </div>
  </div>
  <?php include("footer.php"); ?>
</body>

</html>
