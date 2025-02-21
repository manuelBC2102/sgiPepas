<?php
include ("seguridad.php");
include 'func.php';
include 'reportesPDF.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$id = "";
if (isset($f_id)) $id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));

getDocPendPDF($id, 'D');
?>
