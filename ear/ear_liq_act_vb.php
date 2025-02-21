<?php
header('Content-Type: text/html; charset=UTF-8');
include("seguridad.php");
include 'func.php';
include 'parametros.php';

$es_rrhh = 1;
// $es_rrhh = getPermisosAdministrativos($_SESSION['ldap_user'], 'RRHH');
$count = $es_rrhh;
// $count += getPermisosAdministrativos($_SESSION['ldap_user'], 'COMP');
$aprob = 0;
if ($count == 0) {
  // $aprob = getPermisosAdministrativos($_SESSION['ldap_user'], 'GERENTEINMEDIATO');
  // $aprob += getPermisosAdministrativos($_SESSION['ldap_user'], 'JEFEINMEDIATO');
}
if ($aprob > 0) $count += $aprob;
if ($count == 0) {
  echo "<b>ERROR:</b> P&aacute;gina no existe";
  exit;
}

$arrListSol = getListaSolicitudes(6); // antes era 8.
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <!--<title>EAR Visto Bueno de Liquidaciones Actualizadas por Contabilidad - Administraci�n - Minapp</title>-->
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <style type="text/css">
    body {
      font-size: 10pt;
      font-family: arial, helvetica
    }
  </style>
  <style type="text/css" media="screen">
    @import "css/demo_page.css";
    @import "css/demo_table_jui.css";
    @import "css/ui-lightness/jquery-ui-1.9.2.custom.css";

    /*
	 * Override styles needed due to the mix of three different CSS sources! For proper examples
	 * please see the themes example in the 'Examples' section of this site
	 */
    .dataTables_info {
      padding-top: 0;
    }

    .dataTables_paginate {
      padding-top: 0;
    }

    .css_right {
      float: right;
    }

    #example_wrapper .fg-toolbar {
      font-size: 0.8em
    }

    #theme_links span {
      float: left;
      padding: 2px 10px;
    }
  </style>

  <link href="css/style.css" rel="stylesheet">
  </link>
  <script type="text/javascript" language="javascript" src="js/jquery-1.8.3.min.js"></script>
  <script type="text/javascript" language="javascript" src="js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
      var oTable = $('#example').dataTable({
        "bJQueryUI": true,
        "sPaginationType": "full_numbers",
        "aoColumnDefs": [{
          "bSortable": false,
          "aTargets": [8]
        }],
        "bStateSave": true,
        "aaSorting": [
          [6, "desc"]
        ],
        "bProcessing": true,
        "iDisplayLength": 25,
        "oLanguage": {
          "sUrl": "i18n/dataTables.spanish.txt"
        }
      });
    });
  </script>
  <script>
    $(document).ready(function() {
      $('.ear_liq_vb_anacont').click(function() {
        var msj = 'Antes de continuar asegurese de haber realizado las siguientes acciones:\n\n';
        //		msj += '- CARGA A SAP\n';
        msj += '- APLICACIONES (descuento o reembolsos)\n';
        msj += '- RETENCIONES\n';
        msj += '- DETRACCIONES\n';
        msj += '- REGISTRO DE ACTIVOS FIJOS\n\n';
        msj += 'Esta seguro de dar visto bueno?\n';
        msj += '(Una vez aceptado no se puede regresar a esta etapa!)';
        if (!confirm(msj)) {
          return false;
        }

        $('body').append('<div id="loaderImagina" class="panel-disabled-imagina"><div class="loader-1"></div></div>');

        var ear_id = $(this).attr('fila');
        var bandera_error = true;
        var serie_numero ="";
        var coma = ",";
        $.ajax({
          url: 'func_liq_detalle.php',
          type: 'POST',
          data: {
              parametro1: ear_id
          },
          success: function(response) {
            var arrayData = JSON.parse(response);
            arrayData.forEach(function(element, i) {
              if(element[46] == "0" || element[46] =="" || element[46]==null){
                if(element[41] == "1"){
                  serie_numero += element[7]+"-"+element[8]+", ";
                  bandera_error = false;
                }
              }
            });
            if(!bandera_error){
              alert('Aun no selecciona Operación Tipo o distribución contable para ' + serie_numero);
              $("#loaderImagina").remove();
            }


            if(bandera_error){
              location.href = "ear_liq_act_vb_p.php?id=" + ear_id;
            }
          },
          error: function(error) {
              console.error('Error:', error);
              $("#dialog-confirm").dialog("close");
          }
        });
      });

      $('.ear_liq_vb_regresar').click(function() {
        //		var msj = 'Esta seguro de regresar el flujo a liquidar por parte del colaborador de la liquidaci\u00F3n seleccionada\n';

        var motivo = prompt("Ingrese el motivo por el cual se esta retornando al proceso de liquidacion");
        motivo = motivo.trim();

        if (motivo.length == 0) {
          motivo = null;
          alert("ERROR: No se puede continuar, el motivo no puede estar vacio.");
        }

        var ear_id = $(this).attr('fila');

        if (motivo != null) {
          location.href = "ear_liq_act_vb_rl.php?id=" + ear_id + "&motivo=" + motivo;
        }

      });
    });
  </script>

  <style>
    .iconos {
      vertical-align: text-top;
    }
  </style>
</head>

<body class="ex_highlight_row">
  <?php include("header.php"); ?>

  <h1>Visto bueno de liquidaciones actualizadas por contabilidad</h1>

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
          echo "\t<tr class='gradeA'>\n";
          echo "\t\t<td>" . (is_null($v[17]) ? $v[1] : "<span title='Registrado por " . getUsuarioNombre($v[17]) . "'>" . $v[1] . " <img src='img/por_otro.png' class='iconos'></span>") . "</td>\n";
          echo "\t\t<td>" . $v[2] . "</td>\n";
          echo "\t\t<td " . (strlen($v[18]) > $longMotivo ? "title='" . $v[18] . "'" : "") . ">" . obtenerCadenaXNumLetras($v[18], $longMotivo) . "</td>\n";
          echo "\t\t<td><span title='" . $v[4] . "'>" . $v[5] . " <img src='" . $v[6] . "' class='iconos'></span></td>\n";
          echo "\t\t<td align='right'>" . $v[7] . "</td>\n";

          echo "\t\t<td>" . $v[9];

          $est_msj = "Ultima actualizaci&oacute;n por " . $v[12] . "\nFecha: " . $v[13];
          echo " <img src='img/info.gif' title='$est_msj' class='iconos'>";

          echo "</td>\n";

          echo "\t\t<td>" . $v[10] . "</td>\n";
          //echo "\t\t<td>".$v[11]."</td>\n";
          echo "\t\t<td>" . getFechaEnvioLiq($v[0]) . "</td>\n";

          echo "\t\t<td>";
          echo "<a href='ear_consulta_detalle.php?id=" . $v[0] . "'><img src='img/search.png' border='0' title='Detalle de la solicitud' class='iconos'></a>\n";
          echo "<a href='ear_liq_act_vb_edit.php?id=" . $v[0] . "'><img src='img/liquidar.png' title='Editar Liquidaci&oacute;n' border='0' class='iconos'></a> ";
          echo "<span class='ear_liq_vb_anacont' fila='" . $v[0] . "' style='cursor: pointer'><img src='img/opc-si.gif' border='0' title='Visto bueno' class='iconos'></span>\n";
          echo "<span class='ear_liq_vb_regresar' fila='" . $v[0] . "' style='cursor: pointer'><img src='img/back.png' border='0' title='Regresar el flujo a liquidar' class='iconos'></span>\n";
          echo "</td>\n";

          echo "\t</tr>\n";
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
          <th>Fecha dep</th>
          <th>Acciones</th>
        </tr>
      </tfoot>
    </table>
  </div>

  <div style="clear:left">
    <p><b>Leyenda:</b>
      <img src='img/search.png' title='Detalle' class='iconos'> Detalle de la solicitud &nbsp;&nbsp;&nbsp;
      <img src='img/liquidar.png' title='Detalle' class='iconos'> Editar Liquidaci&oacute;n &nbsp;&nbsp;&nbsp;
      <img src='img/opc-si.gif' title='Visto Bueno' class='iconos'> Dar visto bueno &nbsp;&nbsp;&nbsp;
      <img src='img/back.png' title='Realizado por otro usuario' class='iconos'> Regresar el flujo a liquidar &nbsp;&nbsp;&nbsp;
    </p>
    <b>Nota:</b> M&aacute;ximo se muestran 2000 registros por consulta web.<br>
  </div>

  <?php include("footer.php"); ?>
</body>

</html>
