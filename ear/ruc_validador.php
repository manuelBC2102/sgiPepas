{
<?php
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$ruc_nro = "";
if (isset($f_ruc_nro)) $ruc_nro = filter_var($f_ruc_nro, FILTER_SANITIZE_STRING);
$tipo_doc = null;
if (isset($f_tipo_doc)) $tipo_doc = filter_var($f_tipo_doc, FILTER_SANITIZE_STRING);

list($prov_nom, $ruc_act, $ruc_ret, $ruc_hab) = getRucDatos($ruc_nro, $tipo_doc);

echo '"prov_nom" : "'.utf8_encode($prov_nom).'",'.PHP_EOL;
echo '"ruc_act" : "'.$ruc_act.'",'.PHP_EOL;
echo '"ruc_ret" : "'.$ruc_ret.'",'.PHP_EOL;
echo '"ruc_hab" : "'.$ruc_hab.'"'.PHP_EOL;
?>
}
