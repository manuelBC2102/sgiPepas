<?php
header('Content-Type: text/html; charset=UTF-8');
include ("seguridad.php");
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

if (!isset($f_id)) {
	echo "<font color='red'><b>ERROR: Solicitud err&oacute;nea</b></font><br>";
	exit;
}
else {
	$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
}

if (isset($f_close)) {
	$close = abs((int) filter_var($f_close, FILTER_SANITIZE_NUMBER_INT));
}
else {
	$close = 0;
}

$arr = getLoteCajaChicaInfo($id);
// Si no existe el lote de esa caja chica se genera error
if (count($arr) == 0) {
	echo "<font color='red'><b>ERROR: No se encuentra liquidacion de caja chica.</b></font><br>";
	exit;
}
else {
	list($ccl_id, $cch_nombre, $ccl_numero, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ccl_monto_ini, $ccl_gti, $ccl_dg_json, $ccl_cta_bco,
		$ccl_ape_fec, $ape_usu_nombre, $ccl_cie_fec, $cie_usu_nombre,
		$ccl_aprob_fec, $aprob_usu_nombre, $ccl_act_fec, $act_usu_nombre,
		$ccl_monto_usado, $est_id, $est_nom, $suc_nombre,
		$ccl_ret, $ccl_ret_no, $ccl_det, $ccl_det_no, $ccl_gast_asum, $ccl_pend, $cch_id, $mon_id,
		$ccl_ape_usu, $ccl_cie_usu, $ccl_aprob_usu, $ccl_act_usu,
		$ccl_cuadre, $ccl_banco, $ccl_aju) = $arr;
	$pla_id = null;
}
$arrLiqDet = getLoteDetalleHist($ccl_id, 1);
list($ccl_monto_usado_hist, $ccl_ret_hist, $ccl_ret_no_hist, $ccl_det_hist, $ccl_det_no_hist) = getLoteCajaChicaInfoHist($ccl_id);

$arrTI = getUsuTI();
$valid_users = array(getUsuAdmin(), getUsuController(), getUsuTesoreria(), getUsuSupCont(), getUsuRegCont(), getUsuAnaCont(), getUsuCompensaciones());
$valid_users = array_merge($valid_users, $arrTI);
$valid_users = array_merge($valid_users, getEncargadosCaja($cch_id));
$valid_users = array_merge($valid_users, getResponsablesCaja($cch_id));
if(!in_array($_SESSION['rec_usu_id'], $valid_users)) {
	echo "<font color='red'><b>ERROR: No tiene acceso a esta pagina. Su intento de acceso ha sido reportado.</b></font><br>";
	exit;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Detalle Historico Lote Caja Chica - Administraci�n - Minapp</title>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
	.titulo {font-size: 14pt; font-family: arial,helvetica}
</style>

<style>
.encabezado_h {
	background-color: silver;
	text-align: center;
	 font-weight: bold;
}

.encabezado_h2 {
	background-color: silver;
	text-align: center;
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

<h1>Detalle Historico Lote Caja Chica registrado por el Encargado</h1>

<table>
<tr><td>N�mero Lote</td><td><?php echo $ccl_numero; ?></td></tr>
<tr><td>Fecha Apertura</td><td><?php echo $ccl_ape_fec; ?></td></tr>
<tr><td>Aperturada por</td><td><?php echo $ape_usu_nombre; ?></td></tr>
<tr><td>Fecha Cierre</td><td><?php echo (!is_null($ccl_cie_fec)?$ccl_cie_fec:'N/A'); ?></td></tr>
<tr><td>Cerrada por</td><td><?php echo (!is_null($cie_usu_nombre)?$cie_usu_nombre:'N/A'); ?></td></tr>
<tr><td>Fecha Aprobacion</td><td><?php echo (!is_null($ccl_aprob_fec)?$ccl_aprob_fec:'N/A'); ?></td></tr>
<tr><td>Aprobada por</td><td><?php echo (!is_null($aprob_usu_nombre)?$aprob_usu_nombre:'N/A'); ?></td></tr>
<tr><td>Fecha Ultima Actualizacion</td><td><?php echo $ccl_act_fec; ?></td></tr>
<tr><td>Ultima Actualizacion por</td><td><?php echo $act_usu_nombre; ?></td></tr>
<tr><td>Estado</td><td><?php echo $est_nom; ?></td></tr>
<tr><td>Nombre</td><td><?php echo $cch_nombre; ?></td></tr>
<tr><td>Sucursal</td><td><?php echo $suc_nombre; ?></td></tr>
<tr><td>Monto Asignado</td><td><?php echo $ccl_monto_ini; ?></td></tr>
<tr><td>Moneda</td><td><?php echo "$mon_nom ($mon_simb) ($mon_iso) <img src='$mon_img' style='vertical-align:text-top'>"; ?></td></tr>
<tr><td>Exportar PDF Historico</td><td><a href="cch_lote_pdf_hist.php?id=<?php echo $ccl_id; ?>"><img src='img/pdf.gif' title='Descargar PDF historico registrado por el Encargado' style='vertical-align:text-top'></a></td></tr>
</table>

<?php
$liqsubt01 = 0;
$liqsubt02 = 0;
$liqsubt03 = 0;
$liqsubt04 = 0;
$liqsubt05 = 0;
$liqsubt06 = 0;

if (count($arrLiqDet) > 0 ) {
	echo "<br>\n";
	echo "<div>Detalle historico de gastos del lote de caja chica:</div>\n";
	echo "<table border='1'>\n";
	echo "<tr>\n";
	echo "<td class='encabezado_h'>RUC</td>\n";
	echo "<td class='encabezado_h'>Proveedor</td>\n";
	echo "<td class='encabezado_h'>Fecha</td>\n";
	echo "<td class='encabezado_h'>Ser Nro</td>\n";
	echo "<td class='encabezado_h'>Glosa</td>\n";
	echo "<td class='encabezado_h'>Moneda</td>\n";
	echo "<td class='encabezado_h'>Monto AF</td>\n";
	echo "<td class='encabezado_h'>Monto NAF</td>\n";
	echo "<td class='encabezado_h'>T/C</td>\n";
	echo "<td class='encabezado_h'>Conv AF</td>\n";
	echo "<td class='encabezado_h'>Conv NAF</td>\n";
	echo "<td class='encabezado_h'>Efec Ret/Det</td>\n";
	echo "<td class='encabezado_h'>Monto Ret/Det</td>\n";
	echo "<td class='encabezado_h'>No Asumido</td>\n";
	echo "</tr>\n";

	foreach($arrLiqDet as $v) {
		echo "<tr>\n";
		echo "<td>$v[3]</td>";
		echo "<td>$v[4]</td>";
		echo "<td>$v[6]</td>";
		echo "<td>$v[26] $v[7]-$v[8]</td>";
		echo "<td>$v[9]</td>";
		echo "<td>$v[24]</td>";
		echo "<td align='right'>$v[12]</td>";
		echo "<td align='right'>$v[13]</td>";

		if ($v[10]==2) {
			echo "<td>$v[14]</td>";
		}
		else {
			echo "<td></td>";
		}

		$mon_id_sel = $v[10];
		$afecto = $v[12];
		$noafecto = $v[13];
		$tc = $v[14];
		if ($mon_id_sel == $mon_id) {
			$conv_afecto = $afecto;
			$conv_noafecto = $noafecto;
		}
		else {
			if ($tc>0) $tc_div = $tc;
			if ($mon_id_sel==2 && $mon_id==1) {
				$conv_afecto = number_format($afecto*$tc, 2, '.', '');
				$conv_noafecto = number_format($noafecto*$tc, 2, '.', '');
			}
			else if ($mon_id_sel==1 && $mon_id==2) {
				$conv_afecto = number_format($afecto/$tc, 2, '.', '');
				$conv_noafecto = number_format($noafecto/$tc, 2, '.', '');
			}
		}
		echo "<td align='right'>$conv_afecto</td>";
		echo "<td align='right'>$conv_noafecto</td>";

		if ($v[15] == 0) {
			$retdet_apl = "Si.";
		}
		else if ($v[15] == 1) {
			$retdet_apl = "No.";
		}
		if ($v[16] == 0) {
			$retdet_tip = "";
		}
		else if ($v[16] == 1) {
			$retdet_tip = "Detraccion.";
		}
		else if ($v[16] == 2) {
			$retdet_tip = "Retencion.";
		}
		echo "<td>$retdet_apl $retdet_tip</td>";
		echo "<td align='right'>$v[17]</td>";

		$noasum = conComas($v[12]+$v[13]-$v[22]);
		echo "<td align='right'>$noasum</td>";
		echo "</tr>\n";

		if (startsWith($v[1], '01')) {
			$liqsubt01 += $conv_afecto+$conv_noafecto;
		}
		else if (startsWith($v[1], '02')) {
			$liqsubt02 += $conv_afecto+$conv_noafecto;
		}
		else if (startsWith($v[1], '03')) {
			$liqsubt03 += $conv_afecto+$conv_noafecto;
		}
		else if (startsWith($v[1], '04')) {
			$liqsubt04 += $conv_afecto+$conv_noafecto;
		}
		else if (startsWith($v[1], '05')) {
			$liqsubt05 += $conv_afecto+$conv_noafecto;
		}
		else if (startsWith($v[1], '06')) {
			$liqsubt06 += $conv_afecto+$conv_noafecto;
		}
	}

	echo "</table>\n";

}
?>

<?php
if ($est_id>=2) {
	$mon_saldo_s = number_format($ccl_monto_ini-$ccl_monto_usado_hist-$ccl_pend, 2, '.', '');
	$tot_mon_doc_s = number_format($ccl_monto_usado_hist+$ccl_ret_hist+$ccl_det_hist, 2, '.', '');
	$gast_asum_cola_s = number_format($tot_mon_doc_s-$ccl_gast_asum, 2, '.', '');
	switch (true) {
		case ($ccl_monto_usado_hist == 0):
			$resul_msg = "(Saldo cero)";
			$resul_inp_s = "0.00";
			break;
		case ($ccl_monto_usado_hist > 0):
			$resul_msg = "<font color='green'><b>(Abonar)</b></font>";
			$resul_inp_s = $ccl_monto_usado_hist;
			break;
	}
?>
<br>
<div>Subtotal de los documentos registrados en la caja chica</div>
<table border="1" id="sol_via_detalle_tbl">
<tr>
	<td class="encabezado_h">C�digo</td>
	<td class="encabezado_h">Nombre</td>
	<td class="encabezado_h">Monto</td>
	<td class="encabezado_h">Estado</td>
</tr>
<tr>
	<td>01</td>
	<td>Boletos de Viaje / Pasajes A�reos</td>
	<td align='right' id='liqsubt01'><?php echo conComas($liqsubt01); ?></td>
	<td><div id='divsubt01'><?php echo getMensajeEstadoTopes(0, 0); ?></div></td>
</tr>
<tr>
	<td>02</td>
	<td>Alimentacion / Pension</td>
	<td align='right' id='liqsubt02'><?php echo conComas($liqsubt02); ?></td>
	<td><div id='divsubt02'><?php echo getMensajeEstadoTopes(0, 0); ?></div></td>
</tr>
<tr>
	<td>03</td>
	<td>Hospedaje</td>
	<td align='right' id='liqsubt03'><?php echo conComas($liqsubt03); ?></td>
	<td><div id='divsubt03'><?php echo getMensajeEstadoTopes(0, 0); ?></div></td>
</tr>
<tr>
	<td>04</td>
	<td>Movilidad / Combustible</td>
	<td align='right' id='liqsubt04'><?php echo conComas($liqsubt04); ?></td>
	<td><div id='divsubt04'><?php echo getMensajeEstadoTopes(0, 0); ?></div></td>
</tr>
<tr>
	<td>05</td>
	<td>Gastos de Representaci�n</td>
	<td align='right' id='liqsubt05'><?php echo conComas($liqsubt05); ?></td>
	<td><div id='divsubt05'><?php echo getMensajeEstadoTopes(0, 0); ?></div></td>
</tr>
<tr>
	<td>06</td>
	<td>Otros</td>
	<td align='right' id='liqsubt06'><?php echo conComas($liqsubt06); ?></td>
	<td><div id='divsubt06'><?php echo getMensajeEstadoTopes(0, 0); ?></div></td>
</tr>
</table>

<br>
<div>Detalle de los totales:</div>
<table border="1">
<tr>
	<td class="encabezado_h">Item</td>
	<td class="encabezado_h">Monto</td>
	<td class="encabezado_h">Moneda</td>
</tr>
<tr style="display: none;">
	<td align="right">Monto asignado:</td>
	<td class="calc_span"><span id="mon_sol_s"><?php echo $ccl_monto_ini; ?></span><input type="hidden" name="mon_sol" id="mon_sol" value="<?php echo $ccl_monto_ini; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto registrado:</td>
	<td class="calc_span"><span id="tot_mon_liq_s"><?php echo $ccl_monto_usado_hist; ?></span><input type="hidden" name="tot_mon_liq" id="tot_mon_liq" value="<?php echo $ccl_monto_usado_hist; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto retenciones efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_ret_s"><?php echo $ccl_ret_hist; ?></span><input type="hidden" name="tot_mon_ret" id="tot_mon_ret" value="<?php echo $ccl_ret_hist; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto retenciones no efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_ret_no_s"><?php echo $ccl_ret_no_hist; ?></span><input type="hidden" name="tot_mon_ret_no" id="tot_mon_ret_no" value="<?php echo $ccl_ret_no_hist; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto detracciones efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_det_s"><?php echo $ccl_det_hist; ?></span><input type="hidden" name="tot_mon_det" id="tot_mon_det" value="<?php echo $ccl_det_hist; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto detracciones no efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_det_no_s"><?php echo $ccl_det_no_hist; ?></span><input type="hidden" name="tot_mon_det_no" id="tot_mon_det_no" value="<?php echo $ccl_det_no_hist; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto documentos:</td>
	<td class="calc_span"><span id="tot_mon_doc_s"><?php echo $tot_mon_doc_s; ?></span><input type="hidden" name="tot_mon_doc" id="tot_mon_doc" value="<?php echo $tot_mon_doc_s; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr style="display: none;">
	<td align="right">Total monto pendientes:</td>
	<td class="calc_span"><span id="tot_mon_pend_s"><?php echo $ccl_pend; ?></span><input type="hidden" name="tot_mon_pend" id="tot_mon_pend" value="<?php echo $ccl_pend; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr style="display: none;">
	<td align="right">Total monto cuadre efectivo caja fisico:</td>
	<td class="calc_span"><span id="tot_mon_cuadre_s"><?php echo $ccl_cuadre; ?></span><input type="hidden" name="tot_mon_cuadre" id="tot_mon_cuadre" value="<?php echo $ccl_cuadre; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr style="display: none;">
	<td align="right">Monto saldo contable en cuenta BCP:</td>
	<td class="calc_span"><span id="tot_mon_banco_s"><?php echo $ccl_banco; ?></span><input type="hidden" name="tot_mon_banco" id="tot_mon_banco" value="<?php echo $ccl_banco; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr style="display: none;">
	<td align="right">Saldo en efectivo calculado:</td>
	<td class="calc_span"><span id="mon_saldo_s"><?php echo $mon_saldo_s; ?></span><input type="hidden" name="mon_saldo" id="mon_saldo" value="<?php echo $mon_saldo_s; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr style="display: none;">
	<td align="right">Total gasto asumido por Minapp:</td>
	<td class="calc_span"><span id="tot_mon_gast_asum_s"><?php echo $ccl_gast_asum; ?></span><input type="hidden" name="tot_mon_gast_asum" id="tot_mon_gast_asum" value="<?php echo $ccl_gast_asum; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr style="display: none;">
	<td align="right">Total gasto asumido por el colaborador:</td>
	<td class="calc_span"><span id="tot_mon_gast_asum2_s"><?php echo $gast_asum_cola_s; ?></span><input type="hidden" name="tot_mon_gast_asum2" id="tot_mon_gast_asum2" value="<?php echo $gast_asum_cola_s; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr style="display: none;">
	<td align="right">Resultado: <span id="resul_msg"><?php echo $resul_msg; ?></span></td>
	<td class="calc_span"><span id="resul_inp_s"><?php echo number_format($resul_inp_s, 2, '.', ''); ?></span><input type="hidden" name="resul_inp" id="resul_inp" value="<?php echo $tot_mon_doc_s; ?>"></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
</table>

<?php
$dif2 = $mon_saldo_s-$ccl_cuadre-$ccl_banco;
$msj2 = '';
switch (true) {
	case ($mon_saldo_s < 0):
		$dif2 = $mon_saldo_s*-1;
		$msj2 = "Se ha excedido del efectivo asignado por el monto de $mon_simb ".conComas($dif2);
		break;
	case ($dif2 == 0):
		break;
	case ($dif2 > 0):
		$msj2 = "Falta efectivo $mon_simb ".conComas($dif2);
		break;
	case ($dif2 < 0):
		$dif2 = $dif2*-1;
		$msj2 = "Sobra efectivo $mon_simb ".conComas($dif2);
		break;
}
if (strlen($msj2)>0) {
	echo "<div style='display: none;'><font color='red'><b>*Nota: $msj2</b></font></div>";
}

}
?>

<br>
<?php
if ($close == 0) {
?>
<a href="javascript:history.back()">Retroceder</a><br>
<?php
}
else {
?>
<input type="submit" value="Cerrar ventana" name="b_close" id="b_close" onclick="window.close();">
<br>
<?php
}
?>

<?php include ("footer.php"); ?>
</body>
</html>
