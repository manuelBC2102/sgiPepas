<?php
include ("seguridad.php");
include 'func.php';

// $count = getPermisosAdministrativos($_SESSION['ldap_user'], 'RRHH');
// $count += getPermisosAdministrativos($_SESSION['ldap_user'], 'COMP');
// $aprob = 0;
// if ($count==0) {
	// $aprob = getPermisosAdministrativos($_SESSION['ldap_user'], 'GERENTEINMEDIATO');
	// $aprob += getPermisosAdministrativos($_SESSION['ldap_user'], 'JEFEINMEDIATO');
// }
// if ($aprob > 0) $count += $aprob;
// if ($count==0) {
	// echo "<b>ERROR:</b> P&aacute;gina no existe";
	// exit;
// }

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));
$posting_date = trim(filter_var($f_pd, FILTER_SANITIZE_STRING));
$pzas = explode("-", $posting_date);

list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
	$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
	$usu_act, $ear_act_fec, $ear_act_motivo, $liq_mon_id, $zona_id, $est_id, $usu_id,
	$ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
	$ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
	$master_usu_id, $usu_iniciales) = getSolicitudInfo($id);

$arrLiqDet = getLiqDetalle($id);

$ear_numero = "LGS_".str_replace("/", "_", $ear_numero);

/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2012 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2012 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.8, 2012-10-12
 */

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once '../Classes/PHPExcel.php';


// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Minapp")
							 ->setLastModifiedBy("Minapp")
							 ->setTitle("Minapp Office 2007 XLSX Test Document")
							 ->setSubject("Minapp Office 2007 XLSX Test Document")
							 ->setDescription("Minapp Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Minapp Test result file");


// Add some data
// El SAP pide fecha del header en formato mm/dd/yy
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', 'BKPF')
            ->setCellValue('F2', 'BLART')
            ->setCellValue('G2', 'BUKRS')
            ->setCellValue('H2', 'BUDAT')
            ->setCellValue('I2', 'BKTXT')
            ->setCellValue('J2', 'MONAT')
            ->setCellValue('K2', 'XMWST')
            ->setCellValue('A3', 'Header:')
            ->setCellValue('F3', 'Document Type')
            ->setCellValue('G3', 'Company Code')
            ->setCellValue('H3', 'Posting Date')
            ->setCellValue('I3', 'Doc.Header Text')
            ->setCellValue('J3', 'Period')
            ->setCellValue('K3', 'Calculate tax')
            ->setCellValue('F4', 'KR')
            ->setCellValue('G4', '2100')
            ->setCellValue('H4', date2serial($posting_date))
            ->setCellValue('I4', $ear_numero)
            ->setCellValue('J4', $pzas[1])
            ->setCellValue('K4', 'X')
            ->setCellValue('A6', 'BSEG')
            ->setCellValue('B6', 'BLDAT')
            ->setCellValue('C6', 'XBLNR')
            ->setCellValue('D6', 'NEWBS')
            ->setCellValue('E6', 'NEWKO')
            ->setCellValue('F6', 'WRBTR')
            ->setCellValue('G6', 'DMBTR')
            ->setCellValue('H6', 'SGTXT')
            ->setCellValue('I6', 'WAERS')
            ->setCellValue('J6', 'MWSKZ')
            ->setCellValue('K6', 'KOSTL')
            ->setCellValue('L6', 'PROJK')
            ->setCellValue('M6', 'AUFNR')
            ->setCellValue('N6', 'STCD1')
            ->setCellValue('O6', 'NAME1')
            ->setCellValue('P6', 'ORT01')
            ->setCellValue('Q6', 'LAND1')
            ->setCellValue('A7', 'Line Items:')
            ->setCellValue('B7', 'Document Date')
            ->setCellValue('C7', 'Reference')
            ->setCellValue('D7', 'Posting key')
            ->setCellValue('E7', 'Account')
            ->setCellValue('F7', 'Amount')
            ->setCellValue('G7', 'Amount in LC')
            ->setCellValue('H7', 'Text')
            ->setCellValue('I7', 'Currency')
            ->setCellValue('J7', 'Tax Code')
            ->setCellValue('K7', 'Cost Center')
            ->setCellValue('L7', 'WBS Element')
            ->setCellValue('M7', 'Order')
            ->setCellValue('N7', 'Tax Number 1')
            ->setCellValue('O7', 'Name')
            ->setCellValue('P7', 'City')
            ->setCellValue('Q7', 'Country');

$columnaFinal = "Q";

$objPHPExcel->setActiveSheetIndex(0)
			->getStyle('H4')
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);

// Miscellaneous glyphs, UTF-8
$i=8;
foreach ($arrLiqDet as $k => $v) {
	// [21]:1 = Aprobado
	// [16]:0 = No tiene ni retencion ni detraccion
	// [27]:0 = No es activo fijo
	// [32]   = TAX Code
	if ($v[21]==1 && $v[16]==0 && $v[27]==0) {
		if($v[25]=='XX') {
			if (strlen($v[3])>0) $STCD1 = $v[3]; else $STCD1 = $ear_tra_dni;
			if (strlen($v[4])>0) $NAME1 = $v[4]; else $NAME1 = $ear_tra_nombres;
			$ORT01 = $ear_tra_sucursal;
		}
		else {
			$STCD1 = $v[3];
			$NAME1 = $v[4];
			$ORT01 = $v[31];
		}
		
		// Regla 1: Si el RUC empieza con 20 y registra un documento BOL estas deben considerarse GND - TIPO DOC XX y C9 al exportar
		if($v[25]=='03' && strlen($v[3])==11 && strpos($v[3], '20')!==false ) {
			$v[25]='XX';
		}
		// Regla 2: Utilizar la Lista de Documentos por RUC, si en la lista asociada al RUC se cuenta con FACTURA y el documento registrado
		// es boleta debe considerarse a este GND- TIPO DOC XX y C9 al exportar
		else if ($v[25]=='03' && strlen($v[3])==11 && $v[30]==1) {
			$v[25]='XX';
		}
		// Regla 3: Toda BOLETA que no este dentro de los otros 2 puntos anteriores, debe exportarse TIPO DOC 03 y C0
		else if ($v[25]=='03') {
			$v[13] += $v[12];
			$v[12] = 0;
		}
		// Regla 4: Si el TAX Code es 1 todo el monto se va a C1
		else if ($v[32]==1) {
			$v[12] += $v[13];
			$v[13] = 0;
		}
		// Regla 5: Si el TAX Code es 2 todo el monto se va a C0
		else if ($v[32]==2) {
			$v[13] += $v[12];
			$v[12] = 0;
		}
		// Regla 6: Si el TAX Code es 4 todo el monto se va a C9
		else if ($v[32]==4) {
			$v[25]='XX';
		}
		
		$arr_dg = json_decode(utf8_encode($v[19]));
				
		// INICIO SECCION CONTROL Y MODIFICACION DE GLOSAS - Modificado por KJLG 20160310
		
		if(strlen($v[37])>0) {
			if (!is_null($v[34])) {
				list($veh_id, $veh_placa) = getVehiculosInfo($v[34]);
			}
			else {
				$veh_placa = $v[9].' (OTROS)';
			}
			
			if ($v[18]==1) {
				$proyecto = 'PERSONAL';
			}
			else {
				$proyecto = strtoupper(substr($arr_dg[0][0], strpos($arr_dg[0][0], ' - ')+3));
			}
			
			// %PL - placa, %PE - peaje, %G - glosa original, %U - iniciales del usuario, %K - kilometraje
			// %PR - proyecto, %C - ciudad del proveedor
			$variables = array('%PL', '%PE', '%G', '%U', '%K', '%PR', '%C');
			$valores = array($veh_placa, $v[36], $v[9], $usu_iniciales, $v[35], $proyecto, $v[31]);

			$v[9] = str_ireplace($variables, $valores, $v[37]);
		}
		
		/*
		// Concepto de alimentacion salga asi (CONSUMO - LUGAR QUE CONSUMIO sale de la ciudad del proveedor - LAS PERSONAS (SUS INICIALES) del que hizo la liquidacion)
		if($v[1]=='0201' || $v[1]=='0202') {
			$v[9] = 'CONSUMO - '.$v[31].' - '.$usu_iniciales;
		}
		
		// Concepto de alojamiento salga asi (CONSUMO - LUGAR QUE SE ALOJO sale de la ciudad del proveedor - LAS PERSONAS (SUS INICIALES) del que hizo la liquidacion)
		if($v[1]=='0301') {
			$v[9] = 'ALOJAMIENTO - '.$v[31].' - '.$usu_iniciales;
		}
		
		// Concepto de movilidad
		if($v[1]=='0401') {
			$v[9] = 'MOVILIDAD - '.$v[9].' - '.$usu_iniciales;
		}
		
		// Concepto de pasajes interprovinciales (terrestres)
		if($v[1]=='0101') {
			$v[9] = 'PASAJES - '.$v[9].' - '.$usu_iniciales;
		}
		
		// Concepto de peajes
		if($v[1]=='0403') {
			if (!is_null($v[34])) {
				list($veh_id, $veh_placa) = getVehiculosInfo($v[34]);
			}
			else {
				$veh_placa = 'OTROS';
			}
			$v[9] = $veh_placa.' - '.$v[9].' - '.$usu_iniciales;
		}
		
		// Concepto de combustibles
		if($v[1]=='0402') {
			$v[9] .= ' - '.$usu_iniciales;
		}
		
		// Concepto de mantenimiento
		if($v[1]=='0404') {
			if (!is_null($v[34])) {
				list($veh_id, $veh_placa) = getVehiculosInfo($v[34]);
			}
			else {
				$veh_placa = 'OTROS';
			}
			$v[9] = $veh_placa.' - MANTENIMIENTO - '.$usu_iniciales;
		}
		
		// Concepto de estacionamiento
		if($v[1]=='0405') {
			if (!is_null($v[34])) {
				list($veh_id, $veh_placa) = getVehiculosInfo($v[34]);
			}
			else {
				$veh_placa = 'OTROS';
			}
			$v[9] = $veh_placa.' - ESTACIONAMIENTO - '.$usu_iniciales;
		}
		
		// Concepto de obras civiles (materiales, accesorios o herramientas)
		if($v[1]=='0610') {
			if ($v[18]==1) {
				$v[9] .= ' - PERSONAL - '.$usu_iniciales;
			}
			else {
				$v[9] .= ' - '.strtoupper(substr($arr_dg[0][0], strpos($arr_dg[0][0], ' - ')+3)).' - '.$usu_iniciales;
			}
		}
		
		// Concepto de flete (Cajas)
		if($v[1]=='0601') {
			$v[9] = 'CAJA - '.$v[9].' - '.$usu_iniciales;
		}
		
		// Concepto de sobres
		if($v[1]=='0602') {
			$v[9] = 'SOBRE - '.$v[9].' - '.$usu_iniciales;
		}
		
		// Agrega 'Otros' a la glosa si es Control Vehicular y la placa es Otros
		if($v[33]==1 && $v[34]==null) {
			$v[9] = 'OTROS '.$v[9];
		}
		*/
		
		// FIN SECCION CONTROL DE GLOSAS
		
		$columna = "B";
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue($columna.$i, date2serial($v[6]))
			->setCellValue(++$columna.$i, $v[25].'-'.str_pad($v[7], 5, "0", STR_PAD_LEFT).'-'.str_pad($v[8], 7, "0", STR_PAD_LEFT))
			->setCellValue(++$columna.$i, '31')
			->setCellValue(++$columna.$i, 'EMPLOYEE')
			->setCellValue(++$columna.$i, $v[12]+$v[13])
			->setCellValue(++$columna.$i, ($v[10]==1 ? '' : number_format( ($v[12]+$v[13])*$v[14], 2, '.', '') ) )
			->setCellValue(++$columna.$i, substr(($v[25]=='XX'?'GND-':'').$v[9], 0, 40))
			->setCellValue(++$columna.$i, $v[24])
			->setCellValue(++$columna.$i, '')
			->setCellValue(++$columna.$i, '')
			->setCellValue(++$columna.$i, '')
			->setCellValue(++$columna.$i, '')
			->setCellValue(++$columna.$i, $STCD1)
			->setCellValue(++$columna.$i, utf8_encode(substr($NAME1, 0, 35)))
			->setCellValue(++$columna.$i, $ORT01)
			->setCellValue(++$columna.$i, 'PE');
		
		$copia = $v[12]+$v[13];
		if ($v[10]==2) $copiaLC = number_format( ($v[12]+$v[13])*$v[14], 2, '.', '');
		foreach ($arr_dg as $w) {
			if($v[25]=='XX') {
				$copia -= number_format( ($v[12]+$v[13])*$w[2]/100 , 2, '.', '');
				if ($v[10]==2) $copiaLC -= number_format( (($v[12]+$v[13])*$w[2]/100)*$v[14], 2, '.', '');
				$i++;
				$columna = "D";
				if ($v[18]==1) $v[18]=getTipoGCO($w[1]);
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($columna.$i, '40')
					->setCellValue(++$columna.$i, $v[20])
					->setCellValue(++$columna.$i, number_format( ($v[12]+$v[13])*$w[2]/100 , 2, '.', '') )
					->setCellValue(++$columna.$i, ($v[10]==1 ? '' : number_format( (($v[12]+$v[13])*$w[2]/100)*$v[14], 2, '.', '') ) )
					->setCellValue(++$columna.$i, substr('GND-'.$v[9], 0, 40))
					->setCellValue(++$columna.$i, $v[24])
					->setCellValue(++$columna.$i, 'C9')
					->setCellValue(++$columna.$i, ($v[18]==1 || $v[18]==2?$w[1]:''))
					->setCellValue(++$columna.$i, ($v[18]==3?$w[1]:''))
					->setCellValue(++$columna.$i, ($v[18]==4?$w[1]:''))
					->setCellValue(++$columna.$i, '')
					->setCellValue(++$columna.$i, '')
					->setCellValue(++$columna.$i, '')
					->setCellValue(++$columna.$i, '');
			}
			else {
				if($v[12]>0) {
					$copia -= number_format( $v[12]*$w[2]/100 , 2, '.', '');
					if ($v[10]==2) $copiaLC -= number_format( ($v[12]*$w[2]/100)*$v[14], 2, '.', '');
					$i++;
					$columna = "D";
					if ($v[18]==1) $v[18]=getTipoGCO($w[1]);
					$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($columna.$i, '40')
						->setCellValue(++$columna.$i, $v[20])
						->setCellValue(++$columna.$i, number_format( $v[12]*$w[2]/100 , 2, '.', '') )
						->setCellValue(++$columna.$i, ($v[10]==1 ? '' : number_format( ($v[12]*$w[2]/100)*$v[14], 2, '.', '') ) )
						->setCellValue(++$columna.$i, substr($v[9], 0, 40))
						->setCellValue(++$columna.$i, $v[24])
						->setCellValue(++$columna.$i, 'C1')
						->setCellValue(++$columna.$i, ($v[18]==1 || $v[18]==2?$w[1]:''))
						->setCellValue(++$columna.$i, ($v[18]==3?$w[1]:''))
						->setCellValue(++$columna.$i, ($v[18]==4?$w[1]:''))
						->setCellValue(++$columna.$i, '')
						->setCellValue(++$columna.$i, '')
						->setCellValue(++$columna.$i, '')
						->setCellValue(++$columna.$i, '');
				}

				if($v[13]>0) {
					$copia -= number_format( $v[13]*$w[2]/100 , 2, '.', '');
					if ($v[10]==2) $copiaLC -= number_format( ($v[13]*$w[2]/100)*$v[14], 2, '.', '');
					$i++;
					$columna = "D";
					if ($v[18]==1) $v[18]=getTipoGCO($w[1]);
					$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($columna.$i, '40')
						->setCellValue(++$columna.$i, $v[20])
						->setCellValue(++$columna.$i, number_format( $v[13]*$w[2]/100 , 2, '.', '') )
						->setCellValue(++$columna.$i, ($v[10]==1 ? '' : number_format( ($v[13]*$w[2]/100)*$v[14], 2, '.', '') ) )
						->setCellValue(++$columna.$i, substr($v[9], 0, 40))
						->setCellValue(++$columna.$i, $v[24])
						->setCellValue(++$columna.$i, 'C0')
						->setCellValue(++$columna.$i, ($v[18]==1 || $v[18]==2?$w[1]:''))
						->setCellValue(++$columna.$i, ($v[18]==3?$w[1]:''))
						->setCellValue(++$columna.$i, ($v[18]==4?$w[1]:''))
						->setCellValue(++$columna.$i, '')
						->setCellValue(++$columna.$i, '')
						->setCellValue(++$columna.$i, '')
						->setCellValue(++$columna.$i, '');
				}
			}
		}
		$copia += $objPHPExcel->setActiveSheetIndex(0)->getCell('F'.$i)->getValue();
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$i, number_format( $copia , 2, '.', '') );
		if ($v[10]==2) {
			$copiaLC += $objPHPExcel->setActiveSheetIndex(0)->getCell('G'.$i)->getValue();
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$i, number_format( $copiaLC , 2, '.', '') );
		}
			
		$i++;
	}
}

if(!is_null($pla_id)) {
	list($pla_numero, $est_id_2, $pla_reg_fec, $ear_numero_2, $tope_maximo, $usu_id_2, $ear_id, $est_nom_2, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec) = getPlanillaMovilidadInfo($pla_id);
	
	// Convierte a dolares si es necesario
	if ($liq_mon_id == 2) {
		$tc = getTipoCambio(2, $pla_env_fec);
		$pla_monto = $pla_monto / $tc;
	}
	
	$columna = "B";
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue($columna.$i, date2serial($pla_env_fec))
		->setCellValue(++$columna.$i, 'XX-'.$pla_numero)
		->setCellValue(++$columna.$i, '31')
		->setCellValue(++$columna.$i, 'EMPLOYEE')
		->setCellValue(++$columna.$i, $pla_monto)
		->setCellValue(++$columna.$i, '')
		->setCellValue(++$columna.$i, 'PLANILLA MOVILIDAD')
		->setCellValue(++$columna.$i, 'PEN')
		->setCellValue(++$columna.$i, '')
		->setCellValue(++$columna.$i, '')
		->setCellValue(++$columna.$i, '')
		->setCellValue(++$columna.$i, '')
		->setCellValue(++$columna.$i, $ear_tra_dni)
		->setCellValue(++$columna.$i, substr($ear_tra_nombres, 0, 35))
		->setCellValue(++$columna.$i, $ear_tra_sucursal)
		->setCellValue(++$columna.$i, 'PE');

	$copia = $pla_monto;
	$arr_dg = json_decode(utf8_encode($pla_dg_json));
	foreach ($arr_dg as $w) {
		$copia -= number_format( $pla_monto*$w[2]/100 , 2, '.', '');
		$i++;
		$columna = "D";
		if ($pla_gti==1) $pla_gti=getTipoGCO($w[1]);
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue($columna.$i, '40')
			->setCellValue(++$columna.$i, getCtaContPlaMov(1))
			->setCellValue(++$columna.$i, number_format( $pla_monto*$w[2]/100 , 2, '.', '') )
			->setCellValue(++$columna.$i, '')
			->setCellValue(++$columna.$i, 'PLANILLA MOVILIDAD')
			->setCellValue(++$columna.$i, 'PEN')
			->setCellValue(++$columna.$i, 'C9')
			->setCellValue(++$columna.$i, ($pla_gti==1 || $pla_gti==2?$w[1]:''))
			->setCellValue(++$columna.$i, ($pla_gti==3?$w[1]:''))
			->setCellValue(++$columna.$i, ($pla_gti==4?$w[1]:''))
			->setCellValue(++$columna.$i, '')
			->setCellValue(++$columna.$i, '')
			->setCellValue(++$columna.$i, '')
			->setCellValue(++$columna.$i, '');
	}
	$copia += $objPHPExcel->setActiveSheetIndex(0)->getCell('F'.$i)->getValue();
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$i, number_format( $copia , 2, '.', '') );
	
	$i++;
}

// Set column width
for ($col = 'A'; $col != $columnaFinal; $col++) {
	$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
}

$objPHPExcel->setActiveSheetIndex(0)
			->getStyle('B8:B'.$i)
			->getNumberFormat()
			->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('FORMATO');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setSelectedCell('A1');


// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$ear_numero.'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>
