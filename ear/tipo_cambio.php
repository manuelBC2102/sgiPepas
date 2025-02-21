{
<?php
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$fec = "";
if (isset($f_fec)) $fec = filter_var($f_fec, FILTER_SANITIZE_STRING);

$tc_precio = getTipoCambio(2, $fec);

echo '"tc_precio" : '.$tc_precio.PHP_EOL;
?>
}
