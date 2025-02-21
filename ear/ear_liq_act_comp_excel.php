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

$arrListSol = getListaSolicitudes(9);
$arrListSol = array_merge($arrListSol, getListaSolicitudes(51));

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
//require_once '../Classes/PHPExcel.php';
require_once('../sgi/util/PHPExcel/PHPExcel.php');


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
$columna = "A";
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($columna.'1', 'idcodigeneral')
            ->setCellValue(++$columna.'1', 'Apelllidos y Nombres')
            ->setCellValue(++$columna.'1', 'concepto')
            ->setCellValue(++$columna.'1', 'valor');

// Miscellaneous glyphs, UTF-8
$i=2;
foreach ($arrListSol as $k => $v) {
	if ($v[15]>0) {
		$columna = "A";
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValueExplicit($columna.$i, obtenerPersonaIdSGI($v[16]), PHPExcel_Cell_DataType::TYPE_STRING)
			->setCellValue(++$columna.$i, $v[1])
			->setCellValue(++$columna.$i, 'DE0027')
			->setCellValue(++$columna.$i, $v[15]);
		$i++;
	}
}

$columnaFinal = ++$columna;
// Set column width
for ($col = 'A'; $col != $columnaFinal; $col++) {
	$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
}

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Devoluciones');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setSelectedCell('A1');


// Redirect output to a client�s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ear_liq_act_comp_excel.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>
