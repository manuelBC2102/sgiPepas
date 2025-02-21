{
<?php
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$duaIdDoc = 0;

if (isset($f_duaIdDoc)) $duaIdDoc = (int) filter_var($f_duaIdDoc, FILTER_SANITIZE_NUMBER_INT);

$data = duaObtenerOtrosGastosAdvalorem($duaIdDoc);

echo '"fecha_emision" : "'.$data[0][0].'",'.PHP_EOL;
echo '"tipo_cambio" : "'.$data[0][1].'",'.PHP_EOL;
//echo '"tipo_cambio" : "3.11",'.PHP_EOL;
echo '"importe" : "'.$data[0][2].'"'.PHP_EOL;
?>
}
