<?php

header('Content-Type: text/html; charset=UTF-8');
include 'func.php';
include 'parametros.php';
require_once('../' . $carpetaSGI . '/modeloNegocio/almacen/EarNegocio.php');
$estadoComprobantePago = array(
    "-" => "INVALIDO",
    "0" => "NO EXISTE",
    "1" => "ACEPTADO",
    "2" => "ANULADO",
    "3" => "AUTORIZADO",
    "4" => "NO AUTORIZADO"
);
extract($_REQUEST, EXTR_PREFIX_ALL, "f");
$earId = $f_id;
$dataRespuestaValidacion = array();
$indexRespuesta = 0;
if (isset($f_conc_l)) {
    $dataValidacionSunat = array();
    foreach ($f_conc_l as $k => $v) {
        $conc_id = $f_conc_id[$k];
        $doc_id = $f_tipo_doc[$k];
        $ruc_nro = $f_ruc_nro[$k];
        $lid_fec = $f_fec_doc[$k];
        $lid_ser = $f_ser_doc[$k];
        $lid_nro = $f_num_doc[$k];
        $lid_glo = $f_det_doc[$k];
        $mon_id = $f_tipo_mon[$k]; // la moneda en el formulario esta habilitado
        $lid_afe = $f_afecto_sel[$k];
        $lid_mon_afe = $f_afecto_inp[$k];
        $lid_mon_naf = $f_noafecto_inp[$k];
        $lid_mon_otro = $f_montOtro_inp[$k];
        $lid_mon_icbp = $f_montIcbp_inp[$k];


        $dataDocumentoTipo = getTipoDocInfo($doc_id);

        $montoTotal = round($lid_mon_afe + $lid_mon_naf + $lid_mon_otro + $lid_mon_icbp, 2);
        $documentoTipo = "";
        if ($dataDocumentoTipo[13] == 1 && !is_numeric($lid_ser)) {

//            $fechaEmision = date_format($lid_fec,"Y/m/d H:i:s");
            switch ($doc_id * 1) {
                case 2:
                    $documentoTipo = "01";
                    break;
                case 3:
                    $documentoTipo = "R1";
                    break;
                case 4:
                    $documentoTipo = "03";
                    break;
                case 18:
                    $documentoTipo = "08";
                    break;
                case 19:
                    $documentoTipo = "07";
                    break;
            }
            $dataValidacionSunat[] = array($ruc_nro, $documentoTipo, $lid_ser, ($lid_nro * 1), $lid_fec, $montoTotal);
                                        //   0            1              2         3             4          5
        }

        if ($dataDocumentoTipo[14] == 1) {
            $dataRespuestaValidacion[$indexRespuesta] = array("ruc" => $ruc_nro, "documentoTipoId" => $doc_id, "serie" => $lid_ser, "numero" => $lid_nro, "documentoTipoDescripcion" => $dataDocumentoTipo[1], "documentoTipoSunat" => $documentoTipo, "vout_estado" => 1, "vout_mensaje" => "");

            //VALIDAMOS SI EXISTE EN LA BASE DE DATOS EAR
            $respuestaValidacionEar = obtenerDocumentoRegistrado($doc_id, $ruc_nro, $lid_ser, $lid_nro, $earId);
            if (!isEmpty($respuestaValidacionEar)) {
                $dataRespuestaValidacion[$indexRespuesta]["vout_estado"] = 0;
                $dataRespuestaValidacion[$indexRespuesta]["vout_mensaje"] = $dataDocumentoTipo[1] . " " . $lid_ser . "-" . $lid_nro . " del proveedor " . $ruc_nro . " ya fue registrado en el EAR " . $respuestaValidacionEar[1] . ".";
            } else {
                //VALIDAMOS SI EXISTE EN LA BASE DE DATOS SGI
                $respuestaValidacionSGI = EarNegocio::create()->obtenerDocumentoXRucXSerieNumero(2, $dataDocumentoTipo[12], $ruc_nro, $lid_ser . "-" . $lid_nro);
                if (!isEmpty($respuestaValidacionSGI)) {
                    $dataRespuestaValidacion[$indexRespuesta]["vout_estado"] = 0;
                    $dataRespuestaValidacion[$indexRespuesta]["vout_mensaje"] = $dataDocumentoTipo[1] . " " . $lid_ser . "-" . $lid_nro . " del proveedor " . $ruc_nro . " ya fue registrado.";
                }
            }
            $indexRespuesta++;
        }
    }
    if (!isEmpty($dataRespuestaValidacion) && !isEmpty($dataValidacionSunat)) {
        try {
            $respuestaSunat = EarNegocio::create()->consultaComprobantePagoSunatMultiple($dataValidacionSunat);
            foreach ($dataRespuestaValidacion as $indexRespuesta => $itemRespuesta) {
                foreach ($respuestaSunat as $itemSunat) {
                    if ($itemRespuesta['documentoTipoSunat'] == $itemSunat["tipoDocumento"] && $itemRespuesta['ruc'] == $itemSunat["rucEmisior"] && $itemRespuesta['serie'] == $itemSunat["serie"] && ($itemRespuesta['numero'] * 1) == $itemSunat["numero"] && $itemSunat['estadoCp'] != 1) {
                        $dataRespuestaValidacion[$indexRespuesta]["vout_estado"] = 0;
                        $dataRespuestaValidacion[$indexRespuesta]["vout_mensaje"] .= "<br>Estado en SUNAT: " . $estadoComprobantePago[$itemSunat['estadoCp']];
                        break;
                    }
                }
            }
        } catch (Exception $exc) {
            foreach ($dataRespuestaValidacion as $indexRespuesta => $itemRespuesta) {
                foreach ($dataValidacionSunat as $itemSunat) {
                    if ($itemRespuesta['documentoTipoSunat'] == $itemSunat[1] && $itemRespuesta['ruc'] == $itemSunat[0] && $itemRespuesta['serie'] == $itemSunat[2] && ($itemRespuesta['numero'] * 1) == $itemSunat[3]) {
                        $dataRespuestaValidacion[$indexRespuesta]["vout_estado"] = 0;
                        $dataRespuestaValidacion[$indexRespuesta]["vout_mensaje"] .= "<br>Estado en SUNAT: " . $exc->getMessage();
                        break;
                    }
                }
            }
        }
    }
}

$buff = json_encode($dataRespuestaValidacion);
$contentType = "application/json; charset=utf-8";
header("Content-Type: {$contentType}");
header("Content-Size: " . strlen($buff));
echo $buff;
//echo json_encode($dataRespuestaValidacion);
?>

