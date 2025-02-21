<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

$arr = getCajasChicasInfo($id);
if (empty($arr)) {
	echo "<font color='red'><b>ERROR: Valor no existe</b></font><br>";
	exit;
}
list($cch_id, $cch_nombre, $suc_nombre, $mon_nom, $mon_iso, $mon_img, $cch_monto,
	$cch_abrv, $cch_gti, $cch_dg_json, $cch_cta_bco, $cch_act,
	$suc_id, $mon_id) = $arr;

$arrSuc = getSucursalesLista();

$lid_gti_def = $cch_gti;
$lid_dg_json_def = $cch_dg_json;

$usu_id = $_SESSION['rec_usu_id'];
$rec_usu_nombre = getUsuarioNombre($usu_id);
$adm_usu_gco_cobj = getUsuGcoObj($usu_id);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Editar caja chica - Administraci�n - Minapp</title>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
</style>
<script type="text/javascript" language="javascript" src="js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.validate.min.js"></script>
<script type="text/javascript" language="javascript" src="js/messages_es.js"></script>

<script src="js/jquery-ui-1.9.2/ui/jquery-ui.js"></script>
<link href="js/jquery-ui-1.9.2/themes/ui-lightness/jquery-ui.css" rel="stylesheet">

<script>
$(document).ready(function()
{
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

});
</script>

<style>
.iconos {
	vertical-align:text-top;
	cursor: pointer;
}

#tg1, #tg2, #tg3, #tg4, #dist_gastos {
	display:none;
}

</style>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Editar caja chica</h1>

<b>Datos de la caja chica:</b><br>

<br>

<form action="mant_cajas_chicas_edit_p.php" method="post" enctype="multipart/form-data">
<table>
<tr><td align="right">Nombre:</td><td><input type="text" id="nom" name="nom" maxlength="80" size="120" value="<?php echo $cch_nombre; ?>" /></td></tr>
<tr>
	<td align="right">Sucursal:</td>
	<td>
		<select name="suc">
			<option value='<?php echo $suc_id; ?>'><?php echo $suc_nombre; ?></option>
			<option disabled>-----</option>
<?php
foreach ($arrSuc as $v) {
	echo "\t\t\t<option value='$v[0]'>$v[1]</option>\n";
}
?>
		</select>
	</td>
</tr>
<tr>
	<td align="right">Moneda:</td>
	<td>
		<select name="moneda">
			<option value="1"<?php echo ($mon_id==1?' selected':''); ?>>PEN</option>
			<option value="2"<?php echo ($mon_id==2?' selected':''); ?>>USD</option>
		</select>
	</td>
</tr>
<tr><td align="right">Monto inicial:</td><td><input type="text" id="mont" name="mont" maxlength="13" size="20" value="<?php echo $cch_monto; ?>" /></td></tr>
<tr><td align="right">Abreviatura:</td><td><input type="text" id="abrv" name="abrv" maxlength="32" size="20" value="<?php echo $cch_abrv; ?>" /></td></tr>
<tr><td align="right">Distrib. de gasto por defecto:</td><td><span id="dist_gast_msj"><?php echo substr(getDistGastTemplate($lid_gti_def, $lid_dg_json_def, "def"), 6, -8); ?></span></td></tr>
<tr><td align="right">Cuenta bancaria:</td><td><input type="text" id="cta" name="cta" maxlength="32" size="20" value="<?php echo $cch_cta_bco; ?>" /></td></tr>
<tr>
	<td align="right">Activo:</td>
	<td>
		<select name="act">
			<option value="1"<?php echo ($cch_act==1?' selected':''); ?>>Si</option>
			<option value="0"<?php echo ($cch_act==0?' selected':''); ?>>No</option>
		</select>
	</td>
</tr>
<tr><td></td><td><input type="submit" value="Enviar solicitud"></td></tr>
</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" value="<?php echo $lid_gti_def; ?>" name="lid_gti_def" id="lid_gti_def">
<input type="hidden" value='<?php echo $lid_dg_json_def; ?>' name="lid_dg_json_def" id="lid_dg_json_def">
</form>

<div id="dist_gastos" title="Distribuci�n de Gastos">
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

<br>

<?php include ("footer.php"); ?>
</body>
</html>
