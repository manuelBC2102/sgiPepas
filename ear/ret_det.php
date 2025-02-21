{
<?php
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$conc_id = 0;
$fec = date('Y-m-d');
if (isset($f_conc_id)) $conc_id = (int) filter_var($f_conc_id, FILTER_SANITIZE_NUMBER_INT);
if (isset($f_fec)) $fec = filter_var($f_fec, FILTER_SANITIZE_STRING);

list($ret_tasa, $ret_minmonto, $det_tasa, $det_minmonto) = getLiqConceptosRetDet($conc_id, $fec);

echo '"ret_tasa" : "'.$ret_tasa.'",'.PHP_EOL;
echo '"ret_minmonto" : "'.$ret_minmonto.'",'.PHP_EOL;
echo '"det_tasa" : "'.$det_tasa.'",'.PHP_EOL;
echo '"det_minmonto" : "'.$det_minmonto.'"'.PHP_EOL;
?>
}
