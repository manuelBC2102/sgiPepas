{
<?php
include 'func.php';

extract($_REQUEST, EXTR_PREFIX_ALL, "f");

$id = "";
if (isset($f_id)) $id = abs((int) filter_var($f_id, FILTER_SANITIZE_NUMBER_INT));

list($pla_numero, $est_id, $pla_reg_fec, $ear_numero, $tope_maximo, $usu_id, $ear_id,
	$est_nom, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec,
	$pla_exc, $pla_com1, $pla_com2, $pla_com3) = getPlanillaMovilidadInfo($id);

echo '"est_id" : "'.$est_id.'",'.PHP_EOL;
echo '"pla_monto" : "'.$pla_monto.'",'.PHP_EOL;
echo '"pla_exc" : "'.$pla_exc.'"'.PHP_EOL;
?>
}
