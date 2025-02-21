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

list($ccl_id, $cch_nombre, $ccl_numero, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ccl_monto_ini, $ccl_gti, $ccl_dg_json, $ccl_cta_bco,
	$ccl_ape_fec, $ape_usu_nombre, $ccl_cie_fec, $cie_usu_nombre,
	$ccl_aprob_fec, $aprob_usu_nombre, $ccl_act_fec, $act_usu_nombre,
	$ccl_monto_usado, $est_id, $est_nom, $suc_nombre,
	$ccl_ret, $ccl_ret_no, $ccl_det, $ccl_det_no, $ccl_gast_asum, $ccl_pend, $cch_id, $liq_mon_id,
	$ccl_ape_usu, $ccl_cie_usu, $ccl_aprob_usu, $ccl_act_usu,
	$ccl_cuadre, $ccl_banco) = getLoteCajaChicaInfo($id);

$arrLiqDet = getLoteDetalle($id);

$ear_numero = "CCH_".str_replace("/", "_", $ccl_numero)."_DET";

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
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', $ear_numero.' '.$cch_nombre)
			->mergeCells('A1:K1');

$columna = "A";
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($columna.'3', 'Tipo Doc SUNAT')
            ->setCellValue(++$columna.'3', 'RUC')
            ->setCellValue(++$columna.'3', 'Proveedor')
            ->setCellValue(++$columna.'3', 'Fecha')
            ->setCellValue(++$columna.'3', 'Serie y Numero')
            ->setCellValue(++$columna.'3', 'Moneda')
            ->setCellValue(++$columna.'3', 'TC')
            ->setCellValue(++$columna.'3', 'MontoAfecto')
            ->setCellValue(++$columna.'3', 'MontoNoAfecto')
            ->setCellValue(++$columna.'3', 'Detraccion');

// Miscellaneous glyphs, UTF-8
$i=4;
foreach ($arrLiqDet as $k => $v) {
	// [16]:1 = Detraccion
	// [21]:1 = Aprobado
	if ($v[16]==1 && $v[21]==1) {
		$pzas = explode("-", $v[6]);
		$fec_doc = $pzas[2]."/".$pzas[1]."/".$pzas[0];

		$columna = "A";
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValueExplicit($columna.$i, $v[25], PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValue(++$columna.$i, $v[3])
			->setCellValue(++$columna.$i, utf8_encode($v[4]))
			->setCellValue(++$columna.$i, $fec_doc)
			->setCellValue(++$columna.$i, str_pad($v[7], 5, "0", STR_PAD_LEFT).'-'.str_pad($v[8], 7, "0", STR_PAD_LEFT))
			->setCellValue(++$columna.$i, $v[24])
			->setCellValue(++$columna.$i, $v[14])
			->setCellValue(++$columna.$i, $v[12])
			->setCellValue(++$columna.$i, $v[13])
			->setCellValue(++$columna.$i, $v[17]);
		$i++;
	}
}

$columnaFinal = ++$columna;
// Set column width
for ($col = 'A'; $col != $columnaFinal; $col++) {
	$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
}

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Detracciones');


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
