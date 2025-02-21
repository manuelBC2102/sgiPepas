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

if (isset($f_close)) {
	$close = abs((int) filter_var($f_close, FILTER_SANITIZE_NUMBER_INT));
}
else {
	$close = 0;
}

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
	$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
	$usu_act, $ear_act_fec, $ear_act_motivo, $mon_id, $zona_id, $est_id, $usu_id,
	$ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
	$ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
	$master_usu_id,$comodin1, $comodin2, $dua_id,$tipoCambioFechaLiq,$guardarTcSgi,$periodo_id,
        $dua_serie,$dua_numero,$ear_ord_trabajo) = getSolicitudInfo($id);
$arrDet = getSolicitudDetalle($id);
$arrAct = getSolicitudActualizaciones($id);
$arrLiqDet = getLiqDetalle($id);//falta revisar

//$isDua = ($usu_id == $pAXISADUANA || $usu_id == $pAXISGLOBAL);
$contadorPerfil=obtenerPerfilContador($pPERFIL_PROVEEDOR_DUA,$usu_id);
//$isDua=($contadorPerfil>0);
$isDua = false;

$sol_msj="";
if(!is_null($usu_act)) {
	$sol_msj=" por ".$usu_act." el ".$ear_act_fec;
}
if(!is_null($ear_act_motivo)) {
	$sol_msj.=" (Motivo: ".$ear_act_motivo.")";
}

list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
//$arrTI = getUsuTI();
$arrTI=obtenerUsuariosIdXPerfil($pTI);
$arrAdmin=obtenerUsuariosIdXPerfil($pADMINIST);
$arrTesoreria=obtenerUsuariosIdXPerfil($pTESO);
$arrContabilidad=obtenerUsuariosIdXPerfil($pSUP_CONT);
//$valid_users = array($usu_id, $usu_id_jefe, $usu_id_gerente, getUsuAdmin(), getUsuTesoreria(), getUsuSupCont(), getUsuRegCont(), getUsuAnaCont(), getUsuCompensaciones());
$valid_users = array($usu_id, $usu_id_jefe, $usu_id_gerente);
$valid_users = array_merge($valid_users, $arrTI);
$valid_users = array_merge($valid_users, $arrAdmin);
$valid_users = array_merge($valid_users, $arrTesoreria);
$valid_users = array_merge($valid_users, $arrContabilidad);
//$valid_users = array_merge($valid_users, getUsuRegOtroMastersJefesIds($_SESSION['rec_usu_id']));
if(!in_array($_SESSION['rec_usu_id'], $valid_users)) {
	echo "<font color='red'><b>ERROR: No tiene acceso a esta pagina. Su intento de acceso ha sido reportado.</b></font><br>";
	exit;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!--<title>Detalle Solicitud EAR - Administraci&oacute;n - Minapp</title>-->
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
	.titulo {font-size: 14pt; font-family: arial,helvetica}
</style>

<style>
.encabezado_h {
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

    <h1>Detalle de solicitud  <?php echo strtolower($zona_nom); ?></h1>

<table>
    <tr><td>N&uacute;mero Solicitud</td><td><?php echo $ear_numero; ?></td></tr>
<tr><td>Fecha Solicitud</td><td><?php echo $ear_sol_fec; ?></td></tr>
<tr><td>Estado Solicitud</td><td><?php echo $est_nom.$sol_msj; ?></td></tr>
<tr><td>Nombre</td><td><?php echo $ear_tra_nombres.(is_null($master_usu_id)?"":" (Registrado por ".getUsuarioNombre($master_usu_id).")"); ?></td></tr>
<tr><td>DNI</td><td><?php echo $ear_tra_dni; ?></td></tr>
<!--<tr><td>Cargo</td><td><?php // echo $ear_tra_cargo; ?></td></tr>
<tr><td>Area</td><td><?php // echo $ear_tra_area; ?></td></tr>
<tr><td>Sucursal</td><td><?php // echo $ear_tra_sucursal; ?></td></tr>-->
<tr><td>Fecha de dep&oacute;sito</td><td><?php echo $ear_liq_fec; ?></td></tr>
<tr><td>Moneda</td><td><?php echo "$mon_nom ($mon_simb) ($mon_iso) <img src='$mon_img' style='vertical-align:text-top'>"; ?></td></tr>
<tr><td>Orden de trabajo</td><td><?php echo $ear_ord_trabajo; ?></td></tr>;
<!--<tr><td>Numero de cuenta<br>para la transferencia</td><td><?php // echo $ear_tra_cta; ?></td></tr>-->
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
<!--                <td>
                    <?php
//                    $arr = getDuaXDuaId($dua_id);
//                    foreach ($arr as $v) {
//                        echo $v[4]."-".$v[5]." | ".$v[3];
//                    }
                    ?>
                </td>-->
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

<?php
if (count($arrDet) > 0 ) {
?>
<br>
<div>Detalle de los viaticos:</div>
<table border="1">
<tr>
        <td class="encabezado_h">C&oacute;digo</td>
	<td class="encabezado_h">Nombre</td>
        <td class="encabezado_h">Descripci&oacute;n</td>
        <td class="encabezado_h">D&iacute;as</td>
	<td class="encabezado_h">Monto</td>
	<td class="encabezado_h">Subtotal</td>
</tr>
<?php
$hosp=0;
foreach ($arrDet as $v) {
	if ($hosp==0 && substr($v[0], 0, 2)=='03') {
		echo "<tr><td>".substr($v[0], 0, 2)."</td><td>".getViaticoNom(substr($v[0], 0, 2))."</td><td></td><td></td><td></td><td></td></tr>\n";
		echo "<tr><td>".substr($v[0], 0, 4)."</td><td>".getViaticoNom(substr($v[0], 0, 4))."</td><td></td><td></td><td></td><td></td></tr>\n";
		$hosp=1;
	}
	echo "<tr>\n";
	echo "\t<td>$v[0]</td>\n";
	echo "\t<td>$v[1]</td>\n";
	echo "\t<td>$v[2]</td>\n";
	echo "\t<td align='right'>$v[3]</td>\n";
	echo "\t<td align='right'>$v[4]</td>\n";
	echo "\t<td align='right'>".( is_null($v[3]) ? $v[4] : number_format($v[3]*$v[4], 2, '.', '') )."</td>\n";
	echo "</tr>\n";
}
?>
</table>
<?php
}
?>

<br>

   <div id="total">Total vi&aacute;ticos solicitado para transferencia: <b><?php echo $ear_monto." ".$mon_nom; ?></b></div>

<br>

<div>Motivo de la solicitud:</div>
<textarea name="motivo" cols="80" rows="6" maxlength="300" readonly><?php echo $ear_sol_motivo; ?></textarea>

<br>

<?php
if (strlen($ear_act_obs1)>0) {
?>

<br>

<div>Solicitud modificada, lista de observaciones:</div>
<textarea name="motivo" cols="80" rows="6" maxlength="300" readonly><?php echo $ear_act_obs1; ?></textarea>

<br>

<?php
}

$liqsubt01 = 0;
$liqsubt02 = 0;
$liqsubt03 = 0;
$liqsubt04 = 0;
$liqsubt05 = 0;
$liqsubt06 = 0;

if (count($arrLiqDet) > 0 ) {
	echo "<br>\n";
	echo "<div>Detalle de gastos de la liquidacion del EAR:</div>\n";
	echo "<table border='1'>\n";
	echo "<tr>\n";
	echo "<td class='encabezado_h'>Orden de Trabajo</td>\n";
	echo "<td class='encabezado_h'>RUC</td>\n";
	echo "<td class='encabezado_h'>Proveedor</td>\n";
	echo "<td class='encabezado_h'>Fecha</td>\n";
	echo "<td class='encabezado_h'>Ser Nro</td>\n";
	echo "<td class='encabezado_h'>Glosa</td>\n";
	echo "<td class='encabezado_h'>Moneda</td>\n";
	echo "<td class='encabezado_h'>Monto AF</td>\n";
	echo "<td class='encabezado_h'>Monto NAF</td>\n";
//	echo "<td class='encabezado_h'>T/C</td>\n";
//	echo "<td class='encabezado_h'>Conv AF</td>\n";
//	echo "<td class='encabezado_h'>Conv NAF</td>\n";
	echo "<td class='encabezado_h'>Efec Ret/Det</td>\n";
	echo "<td class='encabezado_h'>Monto Ret/Det</td>\n";
	echo "<td class='encabezado_h'>No Asumido</td>\n";
	echo "</tr>\n";

	foreach($arrLiqDet as $v) {
		echo "<tr>\n";
		echo "<td>$v[49]</td>";
		echo "<td>$v[3]</td>";
		echo "<td>$v[4]</td>";
		echo "<td>$v[6]</td>";
		echo "<td>$v[26] $v[7]-$v[8]</td>";
		echo "<td>$v[9]</td>";
		echo "<td>$v[24]</td>";
		echo "<td align='right'>$v[12]</td>";
		echo "<td align='right'>$v[13]</td>";

//		if ($v[10]==2) {
//			echo "<td>$v[14]</td>";
//		}
//		else {
//			echo "<td></td>";
//		}

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
//		echo "<td align='right'>$conv_afecto</td>";
//		echo "<td align='right'>$conv_noafecto</td>";

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
if(!is_null($pla_id)) {
	list($pla_numero, $pla_est_id, $pla_reg_fec, $ear_numero, $tope_maximo, $usu_id, $ear_id,
		$est_nom, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec,
		$pla_exc, $pla_com1, $pla_com2, $pla_com3) = getPlanillaMovilidadInfo($pla_id);

	$tc = getTipoCambio(2, $pla_env_fec);
	$arrPlaMovDet = getPlanillaMovDetalle($pla_id);
?>
<br>
<div>Informaci&oacute;n de la planilla de movilidad:</div>
<table border="1">
<tr>
	<td class="encabezado_h">Fecha registro</td>
	<td class="encabezado_h">Fecha de envio</td>
	<td class="encabezado_h">Numero</td>
	<td class="encabezado_h">Moneda</td>
	<td class="encabezado_h">Monto</td>
	<td class="encabezado_h">TC</td>
</tr>
<tr>
	<td><?php echo $pla_reg_fec; ?></td>
	<td><?php echo $pla_env_fec; ?></td>
	<td><?php echo $pla_numero; ?></td>
	<td>PEN</td>
	<td align="right"><?php echo $pla_monto; ?></td>
<?php
if ($mon_id == 1) {
	$liqsubt04 += $pla_monto;
?>
	<td align="right">N/A</td>
<?php
}
else if ($mon_id == 2 && $tc == -1) {
?>
	<td align="right">ERROR</td>
<?php
}
else if ($mon_id == 2) {
	$liqsubt04 += number_format($pla_monto/$tc, 2, '.', '');
?>
	<td align="right"><?php echo $tc; ?></td>
<?php
}
?>
</tr>
</table>

<br>
<div>Detalle de la planilla de movilidad:</div>
<table border="1">
<tr>
	<td class="encabezado_h" rowspan="2">Motivo</td>
	<td class="encabezado_h" rowspan="2">Fecha</td>
	<td class="encabezado_h" colspan="5">Desplazamiento</td>
	<td class="encabezado_h" rowspan="2">Aprobado</td>
</tr>
<tr>
	<td class="encabezado_h">Salida</td>
	<td class="encabezado_h">Destino</td>
	<td class="encabezado_h">Moneda</td>
	<td class="encabezado_h">Monto Ingresado</td>
	<td class="encabezado_h">Monto no Asumido</td>
</tr>
<?php
foreach ($arrPlaMovDet as $k => $v) {
?>
<tr>
	<td><?php echo $v[0]; ?></td>
	<td><?php echo $v[1]; ?></td>
	<td><?php echo $v[2]; ?></td>
	<td><?php echo $v[3]; ?></td>
	<td>PEN</td>
	<td align="right"><?php echo $v[4]; ?></td>
	<td align="right"><?php echo $v[7]; ?></td>
	<td align="center"><?php echo getSiNo($v[5]); ?></td>
</tr>
<?php
}
?>
</table>
<?php
}
?>

<?php
if ($est_id>=4) {
	$arrSolSubt = getSolicitudSubtotales($id);

	$solsubt01 = isset($arrSolSubt['01']) ? $arrSolSubt['01'] : '0.00';
	$solsubt02 = isset($arrSolSubt['02']) ? $arrSolSubt['02'] : '0.00';
	$solsubt03 = isset($arrSolSubt['03']) ? $arrSolSubt['03'] : '0.00';
	$solsubt04 = isset($arrSolSubt['04']) ? $arrSolSubt['04'] : '0.00';
	$solsubt05 = isset($arrSolSubt['05']) ? $arrSolSubt['05'] : '0.00';
	$solsubt06 = isset($arrSolSubt['06']) ? $arrSolSubt['06'] : '0.00';

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
?>
<br>
    <div>Subtotal de los viaticos registrados en la solicitud y la liquidaci&oacute;n (topes)</div>
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
	<td align='right' id='solsubt01'><?php echo $solsubt01; ?></td>
	<td align='right' id='liqsubt01'><?php echo conComas($liqsubt01); ?></td>
	<td><div id='divsubt01'><?php echo getMensajeEstadoTopes($solsubt01, $liqsubt01); ?></div></td>
</tr>
<tr>
	<td>02</td>
	<td>Alimentacion / Pension</td>
	<td align='right' id='solsubt02'><?php echo $solsubt02; ?></td>
	<td align='right' id='liqsubt02'><?php echo conComas($liqsubt02); ?></td>
	<td><div id='divsubt02'><?php echo getMensajeEstadoTopes($solsubt02, $liqsubt02); ?></div></td>
</tr>
<tr>
	<td>03</td>
	<td>Hospedaje</td>
	<td align='right' id='solsubt03'><?php echo $solsubt03; ?></td>
	<td align='right' id='liqsubt03'><?php echo conComas($liqsubt03); ?></td>
	<td><div id='divsubt03'><?php echo getMensajeEstadoTopes($solsubt03, $liqsubt03); ?></div></td>
</tr>
<tr>
	<td>04</td>
	<td>Movilidad / Combustible</td>
	<td align='right' id='solsubt04'><?php echo $solsubt04; ?></td>
	<td align='right' id='liqsubt04'><?php echo conComas($liqsubt04); ?></td>
	<td><div id='divsubt04'><?php echo getMensajeEstadoTopes($solsubt04, $liqsubt04); ?></div></td>
</tr>
<!--<tr>
	<td>05</td>
        <td>Gastos de Representaci&oacute;n</td>
	<td align='right' id='solsubt05'><?php // echo $solsubt05; ?></td>
	<td align='right' id='liqsubt05'><?php // echo conComas($liqsubt05); ?></td>
	<td><div id='divsubt05'><?php // echo getMensajeEstadoTopes($solsubt05, $liqsubt05); ?></div></td>
</tr>-->
<tr>
	<td>05</td>
	<td>Otros</td>
	<td align='right' id='solsubt06'><?php echo $solsubt06; ?></td>
	<td align='right' id='liqsubt06'><?php echo conComas($liqsubt06); ?></td>
	<td><div id='divsubt06'><?php echo getMensajeEstadoTopes($solsubt06, $liqsubt06); ?></div></td>
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
<tr>
	<td align="right">Monto solicitado:</td>
	<td class="calc_span"><span id="mon_sol_s"><?php echo $ear_monto; ?></span></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto liquidado:</td>
	<td class="calc_span"><span id="tot_mon_liq_s"><?php echo $ear_liq_mon; ?></span></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Saldo en efectivo:</td>
	<td class="calc_span"><span id="mon_saldo_s"><?php echo $mon_saldo_s; ?></span></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto retenciones efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_ret_s"><?php echo $ear_liq_ret; ?></span></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto retenciones no efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_ret_no_s"><?php echo $ear_liq_ret_no; ?></span></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto detracciones efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_det_s"><?php echo $ear_liq_det; ?></span></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto detracciones no efectuadas:</td>
	<td class="calc_span"><span id="tot_mon_det_no_s"><?php echo $ear_liq_det_no; ?></span></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total monto documentos:</td>
	<td class="calc_span"><span id="tot_mon_doc_s"><?php echo $tot_mon_doc_s; ?></span></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total gasto asumido por ABC MULTISERVICIOS GENERALES S.A.C.:</td>
	<td class="calc_span"><span id="tot_mon_gast_asum_s"><?php echo $ear_liq_gast_asum; ?></span></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Total gasto asumido por el colaborador:</td>
	<td class="calc_span"><span id="tot_mon_gast_asum2_s"><?php echo $gast_asum_cola_s; ?></span></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
<tr>
	<td align="right">Resultado: <span id="resul_msg"><?php echo $resul_msg; ?></span></td>
	<td class="calc_span"><span id="resul_inp_s"><?php echo number_format($resul_inp_s, 2, '.', ''); ?></span></td>
	<td><?php echo $mon_nom; ?></td>
</tr>
</table>

<?php
}
?>

<?php
if (count($arrAct) > 0 ) {
?>
<br>
<div>Seguimiento del EAR:</div>
<table border="1">
<tr>
	<td class="encabezado_h">Estado</td>
	<td class="encabezado_h">Usuario</td>
	<td class="encabezado_h">Fecha</td>
	<td class="encabezado_h">Motivo</td>
</tr>
<?php
$hosp=0;
foreach ($arrAct as $v) {
	echo "<tr>\n";
	echo "\t<td>$v[0]</td>\n";
	echo "\t<td>$v[1]</td>\n";
	echo "\t<td>$v[2]</td>\n";
	echo "\t<td>$v[3]</td>\n";
	echo "</tr>\n";
}
?>
</table>
<?php
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
