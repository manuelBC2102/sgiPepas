<?php
include ("seguridad.php");
include 'func.php';

$count = getPermisosAdministrativos($_SESSION['rec_usu_id'], 'ADMINIST');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'TI');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'SUP_CONT');
$count += getPermisosAdministrativos($_SESSION['rec_usu_id'], 'CONTROLLER');
if ($count == 0) {
	echo "<font color='red'><b>ERROR: P&aacute;gina no existe</b></font><br>";
	exit;
}

$flag=0;
$msg_error="";

$mimeTypes = array("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
$allowedExts = array("xlsx");
$ext = explode(".", $_FILES["file"]["name"]);
$extension = end($ext);
$cup_doc_attach=uniqid() . " " . $_FILES["file"]["name"];
if ( (in_array($_FILES["file"]["type"], $mimeTypes)) && ($_FILES["file"]["size"] < 6000000)
	&& in_array($extension, $allowedExts) )
{
	if ($_FILES["file"]["error"] > 0) {
		$flag=1;
		$msg_error.="File Upload Return Code: " . $_FILES["file"]["error"] . "<br>";
	} else {
		//move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $cup_doc_attach);
		$tmp_name = $_FILES["file"]["tmp_name"];
	}
} else {
	$flag=1;
	$msg_error.="Archivo inv&aacute;lido. Los formatos permitidos son xlsx y no podr&aacute; exceder los 6 megabytes de tama&ntilde;o";
}

if ($flag==1) die ($msg_error);

$arr = array();
$arr2 = array();

/**
* PHPExcel
*
* Copyright (C) 2006 - 2014 PHPExcel
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
*
* @category PHPExcel
* @package PHPExcel
* @copyright Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
* @license http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
* @version ##VERSION##, ##DATE##
*/
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
date_default_timezone_set('Europe/London');
/** PHPExcel_IOFactory */
require_once dirname(__FILE__) . '/../Classes/PHPExcel/IOFactory.php';
if (!file_exists($tmp_name)) {
exit("Please run 05featuredemo.php first." . EOL);
}
//echo date('H:i:s') , " Load from Excel2007 file" , EOL;
$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load($tmp_name);
//echo date('H:i:s') , " Iterate worksheets" , EOL;
foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
	//echo 'Worksheet - ' , $worksheet->getTitle() , EOL;
	foreach ($worksheet->getRowIterator() as $row) {
		//echo ' Row number - ' , $row->getRowIndex() , EOL;
		$cellIterator = $row->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
		foreach ($cellIterator as $cell) {
			if (!is_null($cell)) {
				//echo ' Cell - ' , $cell->getCoordinate() , ' - ' , utf8_decode($cell->getCalculatedValue()) , EOL;
				array_push($arr2, trim(utf8_decode($cell->getCalculatedValue())));
			}
		}
		array_push($arr, $arr2);
		$arr2 = array();
	}
}
// Echo memory peak usage
//echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , EOL;

$_SESSION['arr_gco_obj'] = $arr;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Importar Gastos Colobjects (GCO COBJ) - Administraci�n MinappES</title>
<style type="text/css">
	body{font-size: 10pt; font-family: arial,helvetica}
</style>
<style>
.encabezado_h {
	background-color: silver;
	text-align: center;
}

.iconos {
	vertical-align:text-top;
	cursor: pointer;
}
</style>
</head>
<body>
<?php include ("header.php"); ?>

<h1>Importar Gastos Colobjects (GCO COBJ) Formato Excel</h1>

<p>Resultados del analisis del archivo excel:<br></p>
<table border="1">
<tr>
	<td class="encabezado_h">Nombre o descripcion</td>
	<td class="encabezado_h">Codigo</td>
	<td class="encabezado_h">Estado</td>
	<td class="encabezado_h">Resultado</td>
</tr>
<?php
foreach ($arr as $v) {
	if (count($v)==3) {
		$error=0;
		if (is_numeric($v[2])) {
			$est = abs((int) filter_var($v[2], FILTER_SANITIZE_NUMBER_INT));
			if ($est < 0 || $est > 2) {
				$resul = "<font color='red'>Estado invalido</font>";
				$error=1;
			}
			else if ($est == 0) {
				$resul = "<font color='green'>OK - Se desactivara";
			}
			else if ($est == 1) {
				$resul = "<font color='green'>OK - Se activara";
			}
			else if ($est == 2) {
				$resul = "<font color='green'>OK - Se eliminara";
			}			
		}
		else {
			$resul = "<font color='red'>Error</font>";
			$error=1;
		}
		
		if ($error==0) {
			if (substr($v[1], 0, 3) == 'PE-') {
				$resul = $resul.' (WBS)</font>';
			}
			else if (substr($v[1], 0, 2) == 'PE') {
				$resul = $resul.' (CCOSTO)</font>';
			}
			else {
				$resul = $resul.' (IO)</font>';
			}
		}
?>
<tr>
	<td><?php echo $v[0]; ?></td>
	<td><?php echo $v[1]; ?></td>
	<td><?php echo $v[2]; ?></td>
	<td><?php echo $resul; ?></td>
</tr>
<?php
	}
}
?>
</table>

<p>Nota: Solo se procesaran los registros que esten en OK<br></p>

<p>Aun no se han aplicado los cambios, para actualizar la data, haga clic en el siguiente enlace: <a href='colobjects_imp_res.php'>Confirmar cambios</a></p>

<?php include ("footer.php"); ?>
</body>
</html>
