<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$slave_usu_id = abs((int) filter_var($f_slave_usu_id, FILTER_SANITIZE_NUMBER_INT));
$count = getUsuRegOtroValidarAsig($_SESSION['rec_usu_id'], $slave_usu_id);
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

if (getHabilitadoRegistrarEAR($slave_usu_id) == 0) {
	echo "<font color='red'><b>ERROR: No puede registrar m&aacute;s solicitudes, se ha excedido el m&aacute;ximo de EAR desembolsados sin liquidar.</b></font><br>";
	exit;
}

if (!isset($f_zona_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$zona_id = filter_var($f_zona_id, FILTER_SANITIZE_STRING);
}
if (isset($f_mon_id)) $mon_id = abs((int) filter_var($f_mon_id, FILTER_SANITIZE_NUMBER_INT)); else $mon_id=2;
if ($f_zona_id!="01") $mon_id=2;

list($mon_nom, $mon_iso, $mon_simb, $mon_img) = getNomMoneda($mon_id);
if ($mon_nom=="Sin definir") {
	echo "<font color='red'><b>ERROR: Moneda inv�lida / sin definir</b></font><br>";
	exit;
}

list($dni, $nombres, $cargo_id, $fecha_ing,
	$cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador(getCodigoGeneral(getUsuAd($slave_usu_id)));
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Registrar Solicitud EAR Cero - Administraci�n - Minapp</title>
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

	$( "#solicitud" ).validate({
		rules: {
			"motivo": {
				required: true
			},
			"fecha": {
				required: true
			},
			"cta_dolares": {
				required: true
			}
		}
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

.no-close .ui-dialog-titlebar-close {
display: none;
}
</style>
</head>
<body onload="document.getElementById('enviaform').disabled=false;">
<?php include ("header.php"); ?>

<h1>Registrar Solicitud de Vi�ticos y/o Entregas a Rendir (EAR) Cero <?php echo getNomZona($zona_id); ?></h1>

<p>Esta opci�n permite generar una solicitud con monto cero para que inmediatamente pueda ser utilizada para registrar la PLM y la liquidaci�n correspondiente.</p>

<form id="solicitud" action="ear_sol_lss_uro_p.php" method="post">

<table>
<tr><td>Nombre</td><td><?php echo $nombres; ?></td></tr>
<tr><td>DNI</td><td><?php echo $dni; ?></td></tr>
<tr><td>Cargo</td><td><?php echo $cargo_desc; ?></td></tr>
<tr><td>Area</td><td><?php echo $area_desc; ?></td></tr>
<tr><td>Sucursal</td><td><?php echo $sucursal; ?></td></tr>
<tr><td>Fecha de Liquidacion</td><td><input type="text" id="fecha" name="fecha" readonly /><input type="hidden" id="fecha2" name="fecha2" /></td></tr>
<tr><td>Moneda</td><td><?php echo "$mon_nom ($mon_simb) ($mon_iso) <img src='$mon_img' style='vertical-align:text-top'>"; ?></td></tr>
<tr>
	<td>Numero de cuenta<br>para la transferencia</td>
	<td><?php if ($mon_id==1) echo $ctacte; else echo "<input type='text' name='cta_dolares' size='25' maxlength='32'>"; echo " Cuenta en $mon_nom";?></td>
</tr>
</table>

<br>

Motivo de la solicitud: (m�ximo 300 caracteres, no puede estar en blanco)<br>
<textarea name="motivo" cols="80" rows="6" maxlength="300"></textarea>

<br>

<input type="hidden" name="zona_id" value="<?php echo $zona_id; ?>">
<input type="hidden" name="mon_id" value="<?php echo $mon_id; ?>">
<input type="hidden" name="slave_usu_id" value="<?php echo $slave_usu_id; ?>">
<input type="submit" value="Registrar solicitud" disabled id="enviaform">

</form>

<div id="dialog-confirm" title="Espere que complete el proceso" style="display:none; text-align:center;">
<p>Por favor espere hasta que se complete la transaccion, procesando...<br><br><img src="img/circle-loader.gif" title="Procesando..." class="iconos"></p>
</div>

<?php include ("footer.php"); ?>
</body>
</html>
