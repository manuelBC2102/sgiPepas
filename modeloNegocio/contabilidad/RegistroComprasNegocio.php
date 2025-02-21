<?php

require_once __DIR__ . '/../../modelo/contabilidad/RegistroCompras.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/MovimientoNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/ContLibroNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/EarNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../util/PHPExcel/PHPExcel.php';

class RegistroComprasNegocio extends ModeloNegocioBase
{

    /**
     *
     * @return RegistroComprasNegocio
     */
    public $estadoComprobantePago = array(
        "-" => "INVALIDO",
        "0" => "NO EXISTE",
        "1" => "ACEPTADO",
        "2" => "ANULADO",
        "3" => "AUTORIZADO",
        "4" => "NO AUTORIZADO"
    );
    public $estadoRUC = array(
        "-" => "-",
        "00" => "ACTIVO",
        "01" => "BAJA PROVISIONAL",
        "02" => "BAJA PROV. POR OFICIO",
        "03" => "SUSPENSION TEMPORAL",
        "10" => "BAJA DEFINITIVA",
        "11" => "BAJA DE OFICIO",
        "12" => "BAJA MULT.INSCR. Y OTROS ",
        "20" => "NUM. INTERNO IDENTIF.",
        "21" => "OTROS OBLIGADOS",
        "22" => "INHABILITADO-VENT.UNICA",
        "30" => "ANULACION - ERROR SUNAT   "
    );
    public $estadoCondicion = array(
        "-" => "-",
        "00" => "HABIDO",
        "01" => "NO HALLADO SE MUDO DE DOMICILIO",
        "02" => "NO HALLADO FALLECIO",
        "03" => "NO HALLADO NO EXISTE DOMICILIO",
        "04" => "NO HALLADO CERRADO",
        "05" => "NO HALLADO NRO.PUERTA NO EXISTE",
        "06" => "NO HALLADO DESTINATARIO DESCONOCIDO",
        "07" => "NO HALLADO RECHAZADO",
        "08" => "NO HALLADO OTROS MOTIVOS",
        "09" => "PENDIENTE",
        "10" => "NO APLICABLE",
        "11" => "POR VERIFICAR",
        "12" => "NO HABIDO",
        "20" => "NO HALLADO",
        "21" => "NO EXISTE LA DIRECCION DECLARADA",
        "22" => "DOMICILIO CERRADO",
        "23" => "NEGATIVA RECEPCION X PERSONA CAPAZ",
        "24" => "AUSENCIA DE PERSONA CAPAZ",
        "25" => "NO APLICABLE X TRAMITE DE REVERSION",
        "40" => "DEVUELTO"
    );
    public $comboValidarSUNAT = array(
        "01" => "01",
        "03" => "03",
        "02" => "R1",
        "07" => "07",
        "08" => "08"
    );

    static function create()
    {
        return parent::create();
    }

    public function validarDocumentoSistema($dataDocumento, $documentoNombre, $documentoTipoId)
    {


        // hora actual
        $fechaActual = new DateTime();
        $formatoFechaActual = $fechaActual->format("Ymdhis");

        $decode = Util::base64ToImage($dataDocumento);
        $dataRespuesta = array();
        $banderaExcel = false;
        if (strpos($documentoNombre, 'xls') !== false) {
            $archivoNombre = "ImportFileTempCompras_$formatoFechaActual.xls";
            $banderaExcel = true;
        } elseif (strpos($documentoNombre, 'txt') !== false) {
            $archivoNombre = "ImportFileTempCompras_$formatoFechaActual.txt";
        }

        $fileName = __DIR__ . "/../../util/uploads/$archivoNombre";
        file_put_contents($fileName, $decode);

        if ($banderaExcel) {
            $excel = new Spreadsheet_Excel_Reader();
            $excel->setUTFEncoder('iconv');
            $excel->setOutputEncoding('UTF-8');
            $excel->read($fileName);
            $cells = $excel->sheets[0]["cells"];
            if (ObjectUtil::isEmpty($cells)) {
                throw new WarningException("No se ha especificado un excel correcto");
            }

            foreach ($cells as $key => $value) {
                $serieNumero = str_replace(' ', '', trim($this->obtenerCelda($cells, 1, $key, false)));
                $serie = (explode("-", $serieNumero)[0]);
                $numero = (explode("-", $serieNumero)[1]);

                if (array_key_exists($key, $cells)) {
                    $proveedor = $cells[$key][2];
                    $ruc = trim(explode("-", $proveedor)[0]);
                    $personaDescripcion = trim(explode("-", $proveedor)[1]);
                }
                $fechaEmision = $this->obtenerCelda($cells, 4, $key, false);
                $estadoRespuesta = 1;
                $respuestaSunat = "";
                //VALIDAMOS SI EXISTE EN LA BASE DE DATOS SGI
                $respuestaValidacionSGI = EarNegocio::create()->obtenerDocumentoXRucXSerieNumero(2, $documentoTipoId, $ruc, $serie . "-" . $numero);
                if (ObjectUtil::isEmpty($respuestaValidacionSGI)) {
                    $estadoRespuesta = 0;
                    $respuestaSunat .= "Este documento no esta registrado en el sistema.";
                }

                $dataRespuesta[] = array(
                    "serie_numero" => $serieNumero,
                    "fecha_emision" => $fechaEmision,
                    "ruc" => $ruc,
                    "persona_descripcion" => $personaDescripcion,
                    "vout_estado" => $estadoRespuesta,
                    "vout_respuesta" => $respuestaSunat
                );
            }
        } else {

            $archivoTxt = fopen($fileName, 'r');
            while ($line = fgets($archivoTxt)) {

                if (!ObjectUtil::isEmpty($line)) {
                    $dataLine = explode("|", trim($line));
                    if (count($dataLine) > 1 && strpos($dataLine[0], '/') !== false && $dataLine[1] == "RH") {
                        $fechaEmision = $dataLine[0];
                        $serieNumero = str_replace(' ', '', trim($dataLine[2]));
                        $serie = (explode("-", $serieNumero)[0]);
                        $numero = (explode("-", $serieNumero)[1]);

                        $estado = $dataLine[3];
                        $ruc = $dataLine[5];
                        $personaDescripcion = $dataLine[6];
                        $estadoRespuesta = 1;
                        if ($estado != "NO ANULADO") {
                            $estadoRespuesta = 0;
                        }
                        $respuestaSunat = "Estado SUNAT: " . $estado . "<br>";
                        $banderaExiste = 1;
                        //VALIDAMOS SI EXISTE EN LA BASE DE DATOS SGI
                        $respuestaValidacionSGI = EarNegocio::create()->obtenerDocumentoXRucXSerieNumero(2, $documentoTipoId, $ruc, $serie . "-" . $numero);
                        if (ObjectUtil::isEmpty($respuestaValidacionSGI)) {
                            $estadoRespuesta = 0;
                            $banderaExiste = 0;
                        }

                        $dataRespuesta[] = array(
                            "serie_numero" => $serieNumero,
                            "fecha_emision" => $fechaEmision,
                            "ruc" => $ruc,
                            "persona_descripcion" => $personaDescripcion,
                            "vout_estado" => $estadoRespuesta,
                            "vout_respuesta" => $respuestaSunat,
                            "vout_existe_documento" => $banderaExiste
                        );
                    }
                }
            }
            fclose($archivoTxt);
        }

        unlink($fileName);

        return $dataRespuesta;
    }

    public function obtenerConfiguracionInicial($empresaId)
    {
        $respuesta->dataPeriodo = PeriodoNegocio::create()->obtenerPeriodoXEmpresa($empresaId);
        $dataPeriodoActual = PeriodoNegocio::create()->obtenerUltimoPeriodoActivoXEmpresa($empresaId);
        $respuesta->dataPeriodoActual = $dataPeriodoActual;
        $respuesta->dataPersonaActiva = PersonaNegocio::create()->obtenerActivas();
        $respuesta->dataTipoDocumento = array(
            array("id" => 9, "descripcion" => "Factura"),
            array("id" => 8, "descripcion" => "Boleta"),
            array("id" => 234, "descripcion" => "Recibo por honorarios"),
            array("id" => 267, "descripcion" => "Nota de crédito"),
            //                                              array("id" => 1, "descripcion" => "Nota de débito"),
        );

        $respuesta->dataLibro = ContLibroNegocio::create()->obtenerXClasificacion(ContLibroNegocio::CLASIFICACION_COMPRAS);
        $respuesta->dataRegistroCompras = self::listarRegistroComprasXCriterios(array(array("empresa" => $empresaId, "periodoInicio" => $dataPeriodoActual[0]['id'])));
        return $respuesta;
    }

    // TODO: Inicio Validar Documento Revisar
    public function validarComprobanteSUNATXDocumentoId($documentoId)
    {
        $dataDocumento = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($documentoId);
        if (ObjectUtil::isEmpty($dataDocumento)) {
            throw new WarningException("Documento no encontrado.");
        }
        $tipoDocumentoValidacion = $this->comboValidarSUNAT[$dataDocumento[0]['documento_tipo_codigo_sunat']];
        if (ObjectUtil::isEmpty($tipoDocumentoValidacion)) {
            throw new WarningException("Este documento no esta dentro de la lista de documentos permitidos para validar en SUNAT.");
        }
        $serieNumeroReal = $dataDocumento[0]["serie_numero_real"];
        $serieNumero = explode("-", $serieNumeroReal);
        $serie = trim($serieNumero[0]);
        if (strlen($serie) <= 3 && $dataDocumento[0]["documento_tipo_codigo_sunat"] != '05' && $dataDocumento[0]["documento_tipo_codigo_sunat"] != '50') {
            $serie = "0" . $serie;
        } elseif ($dataDocumento[0]["documento_tipo_codigo_sunat"] == '50') {
            $serie = $serie * 1;
        }

        if (is_numeric($serie)) {
            throw new WarningException("$serieNumeroReal, no es un documento electronico.");
        }

        $percepcion = !ObjectUtil::isEmpty($dataDocumento[0]["percepcion"]) ? $dataDocumento[0]["percepcion"] : 0;

        $numero = trim($serieNumero[1]) * 1;
        $fechaEmision = DateUtil::formatearFechaBDAaCadenaVw($dataDocumento[0]['fecha_emision']);
        $montoTotal = round(($dataDocumento[0]["total"] * 1) - $percepcion, 2);
        // $montoTotal = round($dataDocumento[0]["total"] * 1, 2);
        $rucEmisior = $dataDocumento[0]["codigo_identificacion"];

        // Para los recibos por honorarios, el sub total es el total.
        if ($tipoDocumentoValidacion == "R1") {
            if (!ObjectUtil::isEmpty($dataDocumento[0]["subtotal"]) && $dataDocumento[0]["subtotal"] * 1 > 0) {
                $montoTotal = round(($dataDocumento[0]["subtotal"] * 1) - $percepcion, 2);
                // $montoTotal = round($dataDocumento[0]["subtotal"] * 1, 2);
            }
        }

        $respuesta = array();
        try {
            $respuestaWS = ConsultaWs::create()->validarComprobantePagoTokenSunat($dataDocumento[0]["empresa_ruc"], Configuraciones::SUNAT_CLIENTE_ID, Configuraciones::SUNAT_CLIENTE_PASS, $rucEmisior, $tipoDocumentoValidacion, $serie, $numero, $fechaEmision, $montoTotal);
            $respuesta[0]["vout_mensaje"] = "Estado SUNAT de $serieNumeroReal: " . $this->estadoComprobantePago[$respuestaWS[0]['estadoCp']];
            if ($respuestaWS[0]['estadoCp'] == 1) {
                $respuesta[0]["vout_exito"] = 1;
            } else {
                $respuesta[0]["vout_exito"] = 0;
            }
        } catch (Exception $exc) {
            $respuesta[0]["vout_exito"] = 0;
            $respuesta[0]["vout_mensaje"] = "Estado SUNAT de $serieNumeroReal: " . $exc->getMessage();
        }

        return $respuesta;
    }
    // TODO: Inicio Validar Documento Revisar

    public function validarComprobanteSUNAT($criterios)
    {
        $dataRegistroCompras = self::listarRegistroComprasXCriterios($criterios);
        $dataDocumentoTipoValidacion = array();
        $indexAdd = 0;
        if (!ObjectUtil::isEmpty($dataRegistroCompras)) {
            foreach ($dataRegistroCompras as $index => $item) {
                $tipoDocumentoValidacion = $this->comboValidarSUNAT[$item['tipo_documento']];
                if (!ObjectUtil::isEmpty($tipoDocumentoValidacion)) {
                    $serieNumero = explode("-", $item["serie_numero"]);
                    $serie = trim($serieNumero[0]);
                    if (strlen($serie) <= 3 && $item["tipo_documento"] != '05' && $item["tipo_documento"] != '50') {
                        $serie = "0" . $serie;
                    } elseif ($item["tipo_documento"] == '50') {
                        $serie = $serie * 1;
                    }
                    if (!is_numeric($serie)) {
                        $numero = trim($serieNumero[1]) * 1;
                        $fechaEmision = DateUtil::formatearFechaBDAaCadenaVw($item['fecha_emision']);
                        $montoTotal = round($item["documento_monto_total"] * 1, 2);
                        $rucEmisior = $item["codigo_identificacion_persona"];

                        //Para los recibos por honorarios, el sub total es el total.
                        if ($tipoDocumentoValidacion == "R1") {
                            if (!ObjectUtil::isEmpty($item["documento_monto_sub_total"]) && $item["documento_monto_sub_total"] * 1 > 0) {
                                $montoTotal = round($item["documento_monto_sub_total"] * 1, 2);
                            }
                        }

                        $dataDocumentoTipoValidacion[$indexAdd] = $item;
                        try {
                            $respuestaWS = ConsultaWs::create()->validarComprobantePagoTokenSunat($item["empresa_ruc"], Configuraciones::SUNAT_CLIENTE_ID, Configuraciones::SUNAT_CLIENTE_PASS, $rucEmisior, $tipoDocumentoValidacion, $serie, $numero, $fechaEmision, $montoTotal);
                            $dataDocumentoTipoValidacion[$indexAdd]["vout_respuesta"] = $this->estadoComprobantePago[$respuestaWS[0]['estadoCp']];
                        } catch (Exception $exc) {
                            $dataDocumentoTipoValidacion[$indexAdd]["vout_respuesta"] = $exc->getMessage();
                        }
                        $indexAdd++;
                    }
                }
            }
        }

        return $dataDocumentoTipoValidacion;
    }

    public function listarRegistroComprasXCriterios($criterios)
    {
        if (!ObjectUtil::isEmpty($criterios[0]['empresa'])) {
            $empresaId = $criterios[0]['empresa'];
        }

        if (!ObjectUtil::isEmpty($criterios[0]['persona'])) {
            $personaId = $criterios[0]['persona'];
        } else {
            $personaId = null;
        }

        if (!ObjectUtil::isEmpty($criterios[0]['libro'])) {
            $contLibroId = $criterios[0]['libro'];
        }

        if (!ObjectUtil::isEmpty($criterios[0]['periodoInicio'])) {
            $periodoIdInicio = $criterios[0]['periodoInicio'];
        }

        if (!ObjectUtil::isEmpty($criterios[0]['periodoFin'])) {
            $periodoIdFin = $criterios[0]['periodoFin'];
        }

        if (!ObjectUtil::isEmpty($criterios[0]['fechaEmisionDesde'])) {
            $fechaInicio = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaEmisionDesde']);
        }

        if (!ObjectUtil::isEmpty($criterios[0]['fechaEmisionHasta'])) {
            $fechaFin = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaEmisionHasta']);
        }

        return RegistroCompras::create()->listarRegistroComprasXCriterios($empresaId, $personaId, $contLibroId, $periodoIdInicio, $periodoIdFin, $fechaInicio, $fechaFin);
    }

    // TODO: Inicio Exportar Excel
    public function obtenerRegistroComprasExcel($criterios)
    {
        switch ($criterios[0]['libro']) {
            case ContLibroNegocio::LIBRO_RH_ID:
            case ContLibroNegocio::LIBRO_RH_PRODUCCION_ID:
                $criterios[0]['libro'] = ContLibroNegocio::LIBRO_RH_ID . "," . ContLibroNegocio::LIBRO_RH_PRODUCCION_ID;
                return $this->obtenerRegistroComprasExcelRH($criterios);
            case ContLibroNegocio::LIBRO_ARRENDAMIENTO_ID:
                return $this->obtenerRegistroComprasExcelArrendamiento($criterios);
            default:
                return $this->obtenerRegistroComprasExcelTodo($criterios);
        }
    }
    // TODO: Fin Exportar Excel

    public function obtenerRegistroComprasExcelRH($criterios)
    {
        $reportes = self::listarRegistroComprasXCriterios($criterios);

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':E' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Registro de Honorarios');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . ($i + 1))->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':D' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'DOCUMENTO');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('E' . $i . ':F' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'PROVEEDOR');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('G' . $i . ':G' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Tipo de Cambio');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('H' . $i . ':H' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Monto en Dólares');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('I' . $i . ':I' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Valor Compra');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('J' . $i . ':K' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'Impuestos');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L' . $i . ':L' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'Precio Compra');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('M' . $i . ':M' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, 'Glosa');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':M' . $i)->applyFromArray($this->estiloTituloColumnas);
        $i += 1;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Fecha');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Registro');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Tipo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'R.U.C.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Nombre');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'Cuarta');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, 'Resolución');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':M' . $i)->applyFromArray($this->estiloTituloColumnas);
        $i += 1;
        $inicioCalculo = $i;
        if (!ObjectUtil::isEmpty($reportes)) {
            foreach ($reportes as $reporte) {

                $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, DateUtil::formatearFechaBDAaCadenaVw($reporte['fecha_emision']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['cuo']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['tipo_documento']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['serie_numero']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['codigo_identificacion_persona']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['nombre_persona']);

                if ($reporte['moneda_id'] == ContVoucherNegocio::MONEDA_ID_DOLARES) {
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['tipo_cambio']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['total_dolares']);
                }

                $montoSubTotal = $reporte['sub_total'] * 1;
                $montoTotal = $reporte['total'] * 1;
                $montoRenta = $reporte['igv'] * 1;

                if (ObjectUtil::isEmpty($montoSubTotal) || $montoSubTotal == 0) {
                    $montoSubTotal = $montoTotal;
                }

                $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $montoSubTotal);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, $montoRenta);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, $montoTotal);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, $reporte['glosa']);
                $objPHPExcel->getActiveSheet()->getStyle('G' . $i . ':L' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':M' . $i)->applyFromArray($this->estiloInformacion);
                $i += 1;
            }
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, "Total");
            $objPHPExcel->setActiveSheetIndex()->setCellValue("H$i", "=SUM(H$inicioCalculo:H" . ($i - 1) . ")");
            $objPHPExcel->setActiveSheetIndex()->setCellValue("I$i", "=SUM(I$inicioCalculo:I" . ($i - 1) . ")");
            $objPHPExcel->setActiveSheetIndex()->setCellValue("J$i", "=SUM(J$inicioCalculo:J" . ($i - 1) . ")");
            $objPHPExcel->setActiveSheetIndex()->setCellValue("L$i", "=SUM(L$inicioCalculo:L" . ($i - 1) . ")");

            $objPHPExcel->getActiveSheet()->getStyle('H' . $i . ':L' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':M' . $i)->applyFromArray($this->estiloInformacion);
        }
        for ($i = 'A'; $i <= 'M'; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle('Registro de Honorarios');

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/registroCompras.xlsx');

        return 1;
    }

    public function obtenerRegistroComprasExcelArrendamiento($criterios)
    {
        $reportes = self::listarRegistroComprasXCriterios($criterios);

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':E' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Registro por arrendamiento');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . ($i + 1))->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':D' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'DOCUMENTO');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('E' . $i . ':F' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'PROVEEDOR');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('G' . $i . ':G' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Tipo de Cambio');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('H' . $i . ':H' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Monto en Dólares');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('I' . $i . ':I' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Valor Compra');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('J' . $i . ':J' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'Impuestos');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('K' . $i . ':K' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, 'Precio Compra');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('L' . $i . ':L' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'Glosa');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':L' . $i)->applyFromArray($this->estiloTituloColumnas);
        $i += 1;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Fecha');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Registro');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Tipo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'R.U.C.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Nombre');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':L' . $i)->applyFromArray($this->estiloTituloColumnas);
        $i += 1;
        $inicioCalculo = $i;
        if (!ObjectUtil::isEmpty($reportes)) {
            foreach ($reportes as $reporte) {

                $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, DateUtil::formatearFechaBDAaCadenaVw($reporte['fecha_emision']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['cuo']);
                $objPHPExcel->setActiveSheetIndex()->setCellValueExplicit('C' . $i, $reporte['tipo_documento'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->setActiveSheetIndex()->setCellValueExplicit('D' . $i, $reporte['serie_numero'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->setActiveSheetIndex()->setCellValueExplicit('E' . $i, $reporte['codigo_identificacion_persona'], PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->setActiveSheetIndex()->setCellValueExplicit('F' . $i, $reporte['nombre_persona'], PHPExcel_Cell_DataType::TYPE_STRING);

                if ($reporte['moneda_id'] == ContVoucherNegocio::MONEDA_ID_DOLARES) {
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['tipo_cambio']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['total_dolares']);
                }

                $montoSubTotal = $reporte['total'] * 1;
                $montoTotal = $reporte['total'] * 1;
                $montoImpuesto = 0;

                $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $montoTotal);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, $montoImpuesto);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, $montoSubTotal);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, $reporte['glosa']);
                $objPHPExcel->getActiveSheet()->getStyle('G' . $i . ':K' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':L' . $i)->applyFromArray($this->estiloInformacion);
                $i += 1;
            }
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, "Total");
            $objPHPExcel->setActiveSheetIndex()->setCellValue("H$i", "=SUM(H$inicioCalculo:H" . ($i - 1) . ")");
            $objPHPExcel->setActiveSheetIndex()->setCellValue("I$i", "=SUM(I$inicioCalculo:I" . ($i - 1) . ")");
            $objPHPExcel->setActiveSheetIndex()->setCellValue("J$i", "=SUM(J$inicioCalculo:J" . ($i - 1) . ")");
            $objPHPExcel->setActiveSheetIndex()->setCellValue("K$i", "=SUM(L$inicioCalculo:K" . ($i - 1) . ")");

            $objPHPExcel->getActiveSheet()->getStyle('H' . $i . ':K' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':L' . $i)->applyFromArray($this->estiloInformacion);
        }
        for ($i = 'A'; $i <= 'L'; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle('Registro de arrendamiento');

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/registroCompras.xlsx');

        return 1;
    }

    // TODO: Inicio Exportar Excel
    public function obtenerRegistroComprasExcelTodo($criterios)
    {
        /** @var array */
        $data = self::listarRegistroComprasXCriterios($criterios);

        $reportes = array_filter($data, function ($item) {
            return $item['cont_libro_id'] !== ContLibroNegocio::LIBRO_RH_ID && $item['cont_libro_id'] !== ContLibroNegocio::LIBRO_RH_PRODUCCION_ID && $item['cont_libro_id'] !== ContLibroNegocio::LIBRO_ARRENDAMIENTO_ID;
        });

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':E' . ($i + 1));
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Registro de compras');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . ($i + 1))->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':F' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'DOCUMENTO');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('G' . $i . ':H' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'PROVEEDOR');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('K' . $i . ':Q' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, 'VALOR DE COMPRA');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('R' . $i . ':S' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, 'DEPÓSITO DETRACCIÓN');

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('T' . $i . ':V' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('T' . $i, 'COMPROBANTE DE PAGO QUE MODIFICA');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':V' . $i)->applyFromArray($this->estiloTituloColumnas);
        $i += 1;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Fecha Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Fecha Vencimiento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Fecha de Pago');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'N° de Ingreso');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Tipo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'R.U.C.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Nombre');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Tipo de Cambio');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'Monto en Dólares');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, 'Gravadas');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'No Gravadas');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, 'Sin Crédito');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, 'Otros');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, 'I.C.B.P.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, 'I.G.V.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, 'Precio de compra');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, 'Fecha E.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('T' . $i, 'Fecha');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('U' . $i, 'Tipo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('V' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('W' . $i, 'Glosa');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':W' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;
        $dataLibros = ContLibroNegocio::create()->obtenerXClasificacion(ContLibroNegocio::CLASIFICACION_COMPRAS);
        $montoTotalDolares = 0;
        $montoTotalGravada = 0;
        $montoTotalIgv = 0;
        $montoTotalCompras = 0;
        $montoTotalOtros = 0;
        $montoTotalExonerado = 0;
        $montoTotalNoGravada = 0;
        $montoTotalIcbp = 0;
        foreach ($dataLibros as $libro) {
            $montoTotalDolaresLibro = 0;
            $montoTotalGravadaLibro = 0;
            $montoTotalOtrosLibro = 0;
            $montoTotalExoneradoLibro = 0;
            $montoTotalIgvLibro = 0;
            $montoTotalIcbpLibro = 0;
            $montoTotalComprasLibro = 0;
            $montoTotalNoGravadaLibro = 0;
            $dataFiltrada = Util::filtrarArrayPorColumna($reportes, "cont_libro_id", $libro['id']);
            foreach ($dataFiltrada as $reporte) {
                $montoAfecto = 0;
                $montoIgv = 0;
                $montoNoAfecto = 0;
                $montoPercepcion = 0;
                $montoOtros = 0;
                $montoTotal = 0;
                $montoIcbp = 0;
                if ($reporte['tipo_documento'] == "50") {
                    $montoIgv = $reporte['igv'];
                    $montoTotal = $reporte['igv'];
                } elseif ($reporte['tipo_documento'] == "91") {
                    $montoOtros = $reporte['sub_total'];
                    $montoTotal = $reporte['total'];
                } else {
                    $montoAfecto = $reporte['tipo_documento'] == "03" ? 0 : $reporte['sub_total'];
                    $montoIgv = $reporte['igv'];
                    $montoNoAfecto = $reporte['noafecto'];
                    $montoPercepcion = $reporte['percepcion'];
                    $montoOtros = $reporte['monto_otro'];
                    $montoTotal = $reporte['total'];
                    $montoIcbp = $reporte['icbp'];
                }

                $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, DateUtil::formatearFechaBDAaCadenaVw($reporte['fecha_emision']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, DateUtil::formatearFechaBDAaCadenaVw($reporte['fecha_vencimiento']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, DateUtil::formatearFechaBDAaCadenaVw($reporte['fecha_pago']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['cuo']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['tipo_documento']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['serie_numero']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['codigo_identificacion_persona']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['nombre_persona']);

                if ($reporte['moneda_id'] == ContVoucherNegocio::MONEDA_ID_DOLARES) {
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $reporte['tipo_cambio']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, $reporte['total_dolares']);
                }

                $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, Util::redondearNumero($montoAfecto, 2));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, $montoNoAfecto);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, 0);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, Util::redondearNumero(($montoOtros + $montoPercepcion), 2));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, Util::redondearNumero($montoIcbp, 2));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, Util::redondearNumero($montoIgv, 2));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, Util::redondearNumero($montoTotal, 2));
                $objPHPExcel->getActiveSheet()->getStyle('I' . $i . ':Q' . $i)->getNumberFormat()->setFormatCode('#,##0.00');

                $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, DateUtil::formatearFechaBDAaCadenaVw($reporte['documento_detracion_fecha_emision']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, $reporte['documento_detraccion_numero']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('T' . $i, DateUtil::formatearFechaBDAaCadenaVw($reporte['documento_relacion_fecha_emision']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('U' . $i, $reporte['documento_relacion_tipo_documento']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('V' . $i, $reporte['documento_relacion_serie_numero']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('W' . $i, $reporte['glosa']);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':W' . $i)->applyFromArray($this->estiloInformacion);

                $montoTotalDolaresLibro = Util::redondearNumero($montoTotalDolaresLibro + ($reporte['total_dolares'] * 1), 6);
                $montoTotalOtrosLibro = Util::redondearNumero($montoTotalOtrosLibro + ($montoOtros * 1), 6);
                $montoTotalGravadaLibro = Util::redondearNumero($montoTotalGravadaLibro + ($montoAfecto * 1), 6);
                $montoTotalIgvLibro = Util::redondearNumero($montoTotalIgvLibro + ($montoIgv * 1), 6);
                $montoTotalComprasLibro = Util::redondearNumero($montoTotalComprasLibro + ($montoTotal * 1), 6);
                $montoTotalIcbpLibro = Util::redondearNumero($montoTotalIcbpLibro + ($montoIcbp * 1), 6);
                $i += 1;
            }
            if ($montoTotalComprasLibro > 0) {
                $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, "Sub - Total " . $libro['codigo']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, $montoTotalDolaresLibro);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, $montoTotalGravadaLibro);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, $montoTotalNoGravadaLibro);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, $montoTotalExoneradoLibro);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, $montoTotalOtrosLibro);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, $montoTotalIcbpLibro);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, $montoTotalIgvLibro);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, $montoTotalComprasLibro);
                $objPHPExcel->getActiveSheet()->getStyle('J' . $i . ':Q' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                $i += 1;
            }

            $montoTotalDolares = Util::redondearNumero($montoTotalDolaresLibro + $montoTotalDolares, 6);
            $montoTotalOtros = Util::redondearNumero($montoTotalOtros + $montoTotalOtrosLibro, 6);
            $montoTotalExonerado = Util::redondearNumero($montoTotalExonerado + $montoTotalExoneradoLibro, 6);
            $montoTotalGravada = Util::redondearNumero($montoTotalGravada + $montoTotalGravadaLibro, 6);
            $montoTotalNoGravada = Util::redondearNumero($montoTotalNoGravada + $montoTotalNoGravadaLibro, 6);
            $montoTotalIcbp = Util::redondearNumero($montoTotalIcbp + $montoTotalIcbpLibro, 6);
            $montoTotalIgv = Util::redondearNumero($montoTotalIgvLibro + $montoTotalIgv, 6);
            $montoTotalCompras = Util::redondearNumero($montoTotalComprasLibro + $montoTotalCompras, 6);
        }

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, "Total");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, $montoTotalDolares);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, $montoTotalGravada);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, $montoTotalNoGravada);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, $montoTotalExonerado);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, $montoTotalOtros);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, $montoTotalIcbp);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, $montoTotalIgv);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, $montoTotalCompras);
        $objPHPExcel->getActiveSheet()->getStyle('J' . $i . ':Q' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
        $i += 1;

        for ($i = 'A'; $i <= 'Q'; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle('Reporte de compras');

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/registroCompras.xlsx');

        return 1;
    }
    // TODO: Fin Exportar Excel

    // TODO: Inicio Exportar TXT
    public function obtenerRegistroComprasSire($criterios)
    {
        $data = self::listarRegistroComprasXCriterios($criterios);
        $dataPeriodo = PeriodoNegocio::create()->obtenerPeriodoXid($criterios[0]['periodoInicio']);
        if ($criterios[0]['libro'] === ContLibroNegocio::LIBRO_EXTRANJERO_ID) {
            return $this->obtenerRegistroComprasNoDocimiliadosSire($data, $dataPeriodo->dataPeriodo);
        } else {
            return $this->obtenerRegistroComprasDomiciliadosSire($data, $dataPeriodo->dataPeriodo);
        }
    }
    // TODO: Fin Exportar TXT


    public function obtenerRegistroComprasDomiciliadosSire($data, $dataPeriodo)
    {
        $periodo = $dataPeriodo[0]['anio'] . str_pad($dataPeriodo[0]['mes'], 2, '0', STR_PAD_LEFT);
        $empresaRuc = $dataPeriodo[0]['empresa_ruc'];
        $empresaRazonSocial = $dataPeriodo[0]['empresa_nombre'];
        $dataFiltrado = array_filter($data, function ($item) {
            return !in_array($item['cont_libro_id'], array(ContLibroNegocio::LIBRO_EXTRANJERO_ID, ContLibroNegocio::LIBRO_RH_PRODUCCION_ID, ContLibroNegocio::LIBRO_RH_ID, ContLibroNegocio::LIBRO_ARRENDAMIENTO_ID));
        });

        $banderaExisteRegistros = "1";
        if (ObjectUtil::isEmpty($data) || ObjectUtil::isEmpty($dataFiltrado)) {
            $banderaExisteRegistros = "0";
        }
        $archivoNombre = "LE$empresaRuc$periodo" . "00080400021" . $banderaExisteRegistros . "12.TXT";

        $direccion = __DIR__ . "/../../util/uploads/$archivoNombre";
        file_put_contents($direccion, null);
        $file = fopen($direccion, "w");
        $direccion = "\xEF\xBB\xBF" . $direccion;

        if (!ObjectUtil::isEmpty($data)) {
            $dataFiltrado = array_filter($data, function ($item) {
                return $item['cont_libro_id'] !== ContLibroNegocio::LIBRO_EXTRANJERO_ID && $item['cont_libro_id'] !== ContLibroNegocio::LIBRO_RH_ID && $item['cont_libro_id'] !== ContLibroNegocio::LIBRO_ARRENDAMIENTO_ID;
            });

            foreach ($dataFiltrado as $item) {


                $correlativo = explode($item["libro_codigo"] . "-", $item["cuo"])[1];
                $correlativoMovimiento = substr(str_replace("-", "", $correlativo), 1);

                $serieNumero = explode("-", $item["serie_numero"]);
                $serie = trim($serieNumero[0]);
                if (strlen($serie) <= 3 && $item["tipo_documento"] != '05' && $item["tipo_documento"] != '50') {
                    $serie = "0" . $serie;
                } elseif ($item["tipo_documento"] == '50') {
                    $serie = $serie * 1;
                }
                $numero = trim($serieNumero[1]) * 1;


                $linea = "";
                $linea .= $empresaRuc . "|";
                $linea .= $empresaRazonSocial . "|";
                $linea .= $item["periodo"] . "|";
                $linea .= "|";
                $linea .= DateUtil::formatearFechaBDAaCadenaVw($item['fecha_emision']) . "|";
                $linea .= DateUtil::formatearFechaBDAaCadenaVw($item['fecha_emision']) . "|";
                $linea .= $item["tipo_documento"] . "|";
                $linea .= $serie . "|";
                $linea .= "0|";
                $linea .= $numero . "|";
                $linea .= "|";
                $linea .= $item["persona_tipo_codigo"] . "|";
                $linea .= $item["codigo_identificacion_persona"] . "|";
                $linea .= $item["nombre_persona"] . "|";
                $montoAfecto = 0;
                $montoIgv = 0;
                $montoNoAfecto = 0;
                $montoPercepcion = 0;
                $montoOtros = 0;
                $montoTotal = 0;
                $montoIcbp = 0;
                if ($item['tipo_documento'] == "50") {
                    $montoIgv = $item['igv'];
                    $montoTotal = $item['igv'];
                } else {
                    $montoAfecto = $item['tipo_documento'] == "03" ? 0 : $item['sub_total'];
                    $montoIgv = $item['igv'];
                    $montoNoAfecto = $item['noafecto'];
                    $montoPercepcion = $item['percepcion'];
                    $montoOtros = $item['monto_otro'];
                    $montoTotal = $item['total'];
                    $montoIcbp = $item['icbp'];
                }

                $linea .= str_replace(",", "", (number_format($montoAfecto, 2))) . "|";
                $linea .= str_replace(",", "", (number_format($montoIgv, 2))) . "|";
                $linea .= number_format(0, 2) . "|";
                $linea .= number_format(0, 2) . "|";
                $linea .= number_format(0, 2) . "|";
                $linea .= number_format(0, 2) . "|";
                $linea .= str_replace(",", "", (number_format($montoNoAfecto, 2))) . "|"; // Exonerado              
                $linea .= number_format(0, 2) . "|";
                $linea .= str_replace(",", "", (number_format($montoIcbp, 2))) . "|"; // Bolsa consumo de plastico
                $linea .= str_replace(",", "", (number_format(Util::redondearNumero(($montoOtros + $montoPercepcion), 2), 2))) . "|"; // Otros
                $linea .= str_replace(",", "", (number_format($montoTotal, 2))) . "|";
                if ($item['moneda_id'] == ContVoucherNegocio::MONEDA_ID_DOLARES) {
                    $linea .= "USD|" . number_format($item['tipo_cambio'], 3) . "|";
                } elseif ($item['moneda_id'] == ContVoucherNegocio::MONEDA_ID_SOLES) {
                    $linea .= "PEN||";
                }                
                #COMPROBANTE QUE MODIFICAR NOTA DE CRÉDITO / DÉBITO
                if (!ObjectUtil::isEmpty($item['documento_relacion_fecha_emision'])) {
                    $serieNumeroRelacion = explode("-", $item["documento_relacion_serie_numero"]);
                    // linea = 26 // FECHA DE EMISIÓN
                    $linea .= DateUtil::formatearFechaBDAaCadenaVw($item['documento_relacion_fecha_emision']) . "|";
                    // linea = 27
                    $linea .= $item['documento_relacion_tipo_documento'] . "|"; // TIPO COMPROBANTE
                    // linea = 28
                    $linea .= trim($serieNumeroRelacion[0]) . "|"; // SERIE
                    // linea = 29
                    $linea .= "|"; // EN CASO DE SERE DUA O DUA SIMPLICADA - TIPO DOC SUNAT = 50 - 52
                    // linea = 30
                    $linea .= (int) $serieNumeroRelacion[1] . "|"; // CORRELATIVO
                    $linea .= "|";
                    $linea .= "|";
                } else {
                    $linea .= "|";
                    $linea .= "|";
                    $linea .= "|";
                    $linea .= "|";
                    $linea .= "|";
                    $linea .= "|";
                    $linea .= "5|";
                }

                $linea .= "|";
                $linea .= "|";
                $linea .= "|";

                #COMPROBANTE DE DETRACIÓN
                // if (!ObjectUtil::isEmpty($item['documento_detracion_fecha_emision'])) {
                //     // linea = 31 // FECHA DE EMISIÓN DE LA CONSTACIA DE DETRACIÓN
                //     $linea .= DateUtil::formatearFechaBDAaCadenaVw($item['documento_detracion_fecha_emision']) . "|";
                //     // linea = 32
                //     $linea .= str_pad((int) $item['documento_detraccion_numero'], 24, Util::rellenarEspacios(24)) . "|"; // NÚMERO DE DETRACCIÓN,ENTERO Y POSITIVO
                // } else {
                //     $linea .= "01/01/0001|";
                //     $linea .= str_pad("-", 24, Util::rellenarEspacios(24)) . "|";
                // }

                $lineaSalida = mb_convert_encoding($linea, "ISO-8859-1");
                fwrite($file, $lineaSalida . "\r\n");
            }
        }
        fclose($file);
        return $archivoNombre;
    }


    public function obtenerRegistroComprasNoDocimiliadosSire($data, $dataPeriodo)
    {
        $periodo = $dataPeriodo[0]['anio'] . str_pad($dataPeriodo[0]['mes'], 2, '0', STR_PAD_LEFT);
        $empresaRuc = $dataPeriodo[0]['empresa_ruc'];
        $banderaExisteRegistros = '1';
        if (ObjectUtil::isEmpty($data)) {
            $banderaExisteRegistros = '0';
        }

        $archivoNombre = "LE$empresaRuc$periodo" . "00080500001" . $banderaExisteRegistros . "12.TXT";

        $direccion = __DIR__ . "/../../util/uploads/$archivoNombre";
        file_put_contents($direccion, null);
        $file = fopen($direccion, "w");
        $direccion = "\xEF\xBB\xBF" . $direccion;
        if (!ObjectUtil::isEmpty($data)) {
            foreach ($data as $item) {

                $linea = "";
                // linea = 1
                $linea .= $item['periodo'] . "|";
                $linea .= "|";
                $correlativo = explode($item["libro_codigo"] . "-", $item["cuo"])[1];
                $correlativoMovimiento = substr(str_replace("-", "", $correlativo), 1);

                $serie = "0";
                $numero = (preg_replace('/[^0-9]+/', '', $item["serie_numero"])) * 1;

                $fechaEmisionDUA = "";
                $tipoDocumentoDUA = "";
                $serieDua = "";
                $numeroDua = "";

                $tipoDocumentoPagoSUNAT = "50";
                if ($item["cont_operacion_tipo_id"] == ContVoucherNegocio::OPERACION_TIPO_ID_SERVICIO_EXTRANJERA) {
                    $tipoDocumentoPagoSUNAT = "46";
                }

                $dataDUA = DocumentoNegocio::create()->obtenerDocumentoPagoImportacionXInvoiceComercialXTipoDocumentoSUNAT($item["id"], $tipoDocumentoPagoSUNAT);
                if (!ObjectUtil::isEmpty($dataDUA)) {
                    $serieNumeroDua = explode("-", $dataDUA[0]["serie_numero"]);
                    $serieDua = trim($serieNumeroDua[0]) * 1;
                    $numeroDua = trim($serieNumeroDua[1]) * 1;

                    $tipoDocumentoDUA = $dataDUA[0]["tipo_documento_sunat"];
                    $fechaEmisionDUA = $dataDUA[0]["fecha_emision"];
                }

                $linea .= DateUtil::formatearFechaBDAaCadenaVw($item['fecha_emision']) . "|";
                $linea .= $item["tipo_documento"] . "|";
                $linea .= $serie . "|";
                $linea .= $numero . "|";
                $linea .= number_format(0, 2) . "|";
                $linea .= str_replace(",", "", number_format($item['total'], 2)) . "|";
                $linea .= str_replace(",", "", number_format($item['total'], 2)) . "|";

                //DUA
                $linea .= $tipoDocumentoDUA . "|";
                $linea .= $serieDua . "|";
                $linea .= substr(DateUtil::formatearFechaBDAaCadenaVw($fechaEmisionDUA), 6) . "|";
                $linea .= $numeroDua . "|";
                $linea .= str_replace(",", "", (number_format($item['igv'], 2))) . "|";
                // linea = 16 y 17
                if ($item['moneda_id'] == ContVoucherNegocio::MONEDA_ID_DOLARES) {
                    $linea .= "USD|" . number_format($item['tipo_cambio'], 3) . "|";
                } elseif ($item['moneda_id'] == ContVoucherNegocio::MONEDA_ID_SOLES) {
                    $linea .= "PEN|" . number_format($item['tipo_cambio'], 3) . "|";
                }
                $linea .= $item['pais_codigo'] . "|";
                $linea .= $item["nombre_persona"] . "|";
                $linea .= "|";
                $linea .= "3|";
                $linea .= "3|";
                $linea .= $item["nombre_persona"] . "|";
                $linea .= $item['pais_codigo'] . "|";
                $linea .= "00|";
                $linea .= number_format(0, 2) . "|";
                $linea .= number_format(0, 2) . "|";
                $linea .= number_format(0, 2) . "|";
                $linea .= number_format(0, 2) . "|";
                $linea .= number_format(0, 2) . "|";
                $linea .= "00|";
                $linea .= "|";
                $linea .= "00|";
                $linea .= "1|";
                $linea .= "|";
                $linea .= "|";
                $lineaSalida = mb_convert_encoding($linea, "ISO-8859-1");
                fwrite($file, $lineaSalida . "\r\n");
            }
        }
        fclose($file);
        return $archivoNombre;
    }



    // TODO: Inicio Exportar TXT
    public function obtenerRegistroComprasTxt($criterios)
    {
        $data = self::listarRegistroComprasXCriterios($criterios);
        $dataPeriodo = PeriodoNegocio::create()->obtenerPeriodoXid($criterios[0]['periodoInicio']);
        if ($criterios[0]['libro'] === ContLibroNegocio::LIBRO_EXTRANJERO_ID) {
            return $this->obtenerRegistroComprasNoDocimiliadosTxt($data, $dataPeriodo->dataPeriodo);
        } else {
            return $this->obtenerRegistroComprasDomiciliadosTxt($data, $dataPeriodo->dataPeriodo);
        }
    }
    // TODO: Fin Exportar TXT

    // TODO: Inicio Exportar TXT
    public function obtenerRegistroComprasDomiciliadosTxt($data, $dataPeriodo)
    {
        $periodo = $dataPeriodo[0]['anio'] . str_pad($dataPeriodo[0]['mes'], 2, '0', STR_PAD_LEFT);
        $empresaRuc = $dataPeriodo[0]['empresa_ruc'];
        $dataFiltrado = array_filter($data, function ($item) {
            return !in_array($item['cont_libro_id'], array(ContLibroNegocio::LIBRO_EXTRANJERO_ID, ContLibroNegocio::LIBRO_RH_PRODUCCION_ID, ContLibroNegocio::LIBRO_RH_ID, ContLibroNegocio::LIBRO_ARRENDAMIENTO_ID));
        });

        $banderaExisteRegistros = "1";
        if (ObjectUtil::isEmpty($data) || ObjectUtil::isEmpty($dataFiltrado)) {
            $banderaExisteRegistros = "0";
        }
        $archivoNombre = "LE$empresaRuc$periodo" . "00080100001" . $banderaExisteRegistros . "11.TXT";

        $direccion = __DIR__ . "/../../util/uploads/$archivoNombre";
        file_put_contents($direccion, null);
        $file = fopen($direccion, "w");
        $direccion = "\xEF\xBB\xBF" . $direccion;

        if (!ObjectUtil::isEmpty($data)) {
            $dataFiltrado = array_filter($data, function ($item) {
                return $item['cont_libro_id'] !== ContLibroNegocio::LIBRO_EXTRANJERO_ID && $item['cont_libro_id'] !== ContLibroNegocio::LIBRO_RH_ID && $item['cont_libro_id'] !== ContLibroNegocio::LIBRO_ARRENDAMIENTO_ID;
            });

            foreach ($dataFiltrado as $item) {
                $linea = "";
                // linea = 1
                $linea .= $item['periodo'] . "00" . "|";

                $correlativo = explode($item["libro_codigo"] . "-", $item["cuo"])[1];
                $correlativoMovimiento = substr(str_replace("-", "", $correlativo), 1);

                $serieNumero = explode("-", $item["serie_numero"]);
                $serie = trim($serieNumero[0]);
                if (strlen($serie) <= 3 && $item["tipo_documento"] != '05' && $item["tipo_documento"] != '50') {
                    $serie = "0" . $serie;
                } elseif ($item["tipo_documento"] == '50') {
                    $serie = $serie * 1;
                }
                $numero = trim($serieNumero[1]) * 1;

                // linea = 2
                $linea .= str_pad($item["cuo"], 40, Util::rellenarEspacios(40)) . "|";
                // linea = 3
                $linea .= str_pad('M' . $correlativoMovimiento, 10, Util::rellenarEspacios(10)) . "|";
                // linea = 4
                $linea .= DateUtil::formatearFechaBDAaCadenaVw($item['fecha_emision']) . "|";
                // linea = 5
                $linea .= DateUtil::formatearFechaBDAaCadenaVw((ObjectUtil::isEmpty($item['fecha_vencimiento']) ? '0001-01-01' : $item['fecha_vencimiento'])) . "|";
                // linea = 6
                $linea .= $item["tipo_documento"] . "|";
                // linea = 7
                $linea .= str_pad($serie, 6, Util::rellenarEspacios(6)) . "|";
                //linea = duda
                $anio = 0;
                // linea = 8
                if ($item['tipo_documento'] == "50") {
                    $anio = substr(DateUtil::formatearFechaBDAaCadenaVw($item['fecha_emision']), 6);
                }
                $linea .= str_pad($anio, 4, Util::rellenarEspacios(4), STR_PAD_LEFT) . "|";

                // $linea .= substr(DateUtil::formatearFechaBDAaCadenaVw($item['fecha_emision']),6). "|";
                // $linea .= str_pad(0, 4, Util::rellenarEspacios(4), STR_PAD_LEFT) . "|";
                // linea = 9
                $linea .= str_pad($numero, 20, Util::rellenarEspacios(20)) . "|";

                // linea = 10
                $linea .= str_pad("", 20, Util::rellenarEspacios(20)) . "|";
                // linea = 11
                $linea .= $item["persona_tipo_codigo"] . "|";
                // linea = 12
                $linea .= str_pad($item["codigo_identificacion_persona"], 15, Util::rellenarEspacios(15)) . "|";
                // linea = 13
                $linea .= str_pad($item["nombre_persona"], 100, Util::rellenarEspacios(100 + substr_count(strtoupper($item["nombre_persona"]), 'Ñ'))) . "|";

                $montoAfecto = 0;
                $montoIgv = 0;
                $montoNoAfecto = 0;
                $montoPercepcion = 0;
                $montoOtros = 0;
                $montoTotal = 0;
                $montoIcbp = 0;
                if ($item['tipo_documento'] == "50") {
                    $montoIgv = $item['igv'];
                    $montoTotal = $item['igv'];
                } else {
                    $montoAfecto = $item['tipo_documento'] == "03" ? 0 : $item['sub_total'];
                    $montoIgv = $item['igv'];
                    $montoNoAfecto = $item['noafecto'];
                    $montoPercepcion = $item['percepcion'];
                    $montoOtros = $item['monto_otro'];
                    $montoTotal = $item['total'];
                    $montoIcbp = $item['icbp'];
                }

                // linea = 14
                $linea .= str_pad(str_replace(",", "", (number_format($montoAfecto, 2))), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|";
                // linea = 15
                $linea .= str_pad(str_replace(",", "", (number_format($montoIgv, 2))), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|";
                // linea = 16
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                // linea = 17
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                // linea = 18
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                // linea = 19
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                // linea = 20
                $linea .= str_pad(str_replace(",", "", (number_format($montoNoAfecto, 2))), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; // Exonerado
                // $linea .= str_pad(str_replace(",", "", (number_format($item['monto_exonerado'], 2))), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; // Exonerado
                // $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; // Exonerado
                // linea = 21
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; // Deducibles al consumo
                // linea = 22
                $linea .= str_pad(str_replace(",", "", (number_format($montoIcbp, 2))), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; // Bolsa consumo de plastico
                // linea = 22
                $linea .= str_pad(str_replace(",", "", (number_format(Util::redondearNumero(($montoOtros + $montoPercepcion), 2), 2))), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; // Otros
                // $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; // Otros
                // linea = 23
                $linea .= str_pad(str_replace(",", "", (number_format($montoTotal, 2))), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|";
                // linea = 24,25
                if ($item['moneda_id'] == ContVoucherNegocio::MONEDA_ID_DOLARES) {
                    $linea .= "USD|" . number_format($item['tipo_cambio'], 3) . "|";
                } elseif ($item['moneda_id'] == ContVoucherNegocio::MONEDA_ID_SOLES) {
                    $linea .= "PEN|" . number_format($item['tipo_cambio'], 3) . "|";
                }

                #COMPROBANTE QUE MODIFICAR NOTA DE CRÉDITO / DÉBITO
                if (!ObjectUtil::isEmpty($item['documento_relacion_fecha_emision'])) {
                    $serieNumeroRelacion = explode("-", $item["documento_relacion_serie_numero"]);
                    // linea = 26 // FECHA DE EMISIÓN
                    $linea .= DateUtil::formatearFechaBDAaCadenaVw($item['documento_relacion_fecha_emision']) . "|";
                    // linea = 27
                    $linea .= $item['documento_relacion_tipo_documento'] . "|"; // TIPO COMPROBANTE
                    // linea = 28
                    $linea .= str_pad(trim($serieNumeroRelacion[0]), 20, Util::rellenarEspacios(20)) . "|"; // SERIE
                    // linea = 29
                    $linea .= "   |"; // EN CASO DE SERE DUA O DUA SIMPLICADA - TIPO DOC SUNAT = 50 - 52
                    // linea = 30
                    $linea .= str_pad((int) $serieNumeroRelacion[1], 20, Util::rellenarEspacios(20)) . "|"; // CORRELATIVO
                } else {
                    $linea .= "01/01/0001|";
                    $linea .= "00|";
                    $linea .= str_pad("-", 20, Util::rellenarEspacios(20)) . "|";
                    $linea .= "   |";
                    $linea .= str_pad("-", 20, Util::rellenarEspacios(20)) . "|";
                }

                #COMPROBANTE DE DETRACIÓN
                if (!ObjectUtil::isEmpty($item['documento_detracion_fecha_emision'])) {
                    // linea = 31 // FECHA DE EMISIÓN DE LA CONSTACIA DE DETRACIÓN
                    $linea .= DateUtil::formatearFechaBDAaCadenaVw($item['documento_detracion_fecha_emision']) . "|";
                    // linea = 32
                    $linea .= str_pad((int) $item['documento_detraccion_numero'], 24, Util::rellenarEspacios(24)) . "|"; // NÚMERO DE DETRACCIÓN,ENTERO Y POSITIVO
                } else {
                    $linea .= "01/01/0001|";
                    $linea .= str_pad("-", 24, Util::rellenarEspacios(24)) . "|";
                }
                // linea = 33
                $linea .= " |"; // EN CASO DE EL COMPROBANTE ESTA SUJETO A RETENCIÓN = 1
                // linea = 34
                $linea .= " |"; // EN CASO 1500 UIT EN EL EJERCICIO ANTERIOR
                // linea = 35
                $linea .= str_pad("", 12, Util::rellenarEspacios(12)) . "|";
                // linea = 36
                $linea .= " |";
                // linea = 37
                $linea .= " |";
                // linea = 38
                $linea .= " |";
                // linea = 39
                $linea .= " |";
                // linea = 40
                $linea .= " |";
                // linea = 41
                if (!ObjectUtil::isEmpty($item['igv']) && abs($item['igv'] * 1) > 0) {
                    $fechaEmisionExplode = explode("-", $item["fecha_emision"]);
                    // 0 => año 1=>mes
                    if (($fechaEmisionExplode[0] . $fechaEmisionExplode[1]) == $item['periodo']) {
                        $linea .= "1|";
                    } else {
                        $linea .= "6|";
                    }
                } else {
                    $linea .= "0|";
                }
                // linea = 42 => Libre
                $linea .= str_pad("", 200, Util::rellenarEspacios(200));
                $lineaSalida = mb_convert_encoding($linea, "ISO-8859-1");
                fwrite($file, $lineaSalida . "\r\n");
            }
        }
        fclose($file);
        return $archivoNombre;
    }
    // TODO: Inicio Exportar TXT

    public function obtenerRegistroComprasNoDocimiliadosTxt($data, $dataPeriodo)
    {

        $periodo = $dataPeriodo[0]['anio'] . str_pad($dataPeriodo[0]['mes'], 2, '0', STR_PAD_LEFT);
        $empresaRuc = $dataPeriodo[0]['empresa_ruc'];
        $banderaExisteRegistros = '1';
        if (ObjectUtil::isEmpty($data)) {
            $banderaExisteRegistros = '0';
        }

        $archivoNombre = "LE$empresaRuc$periodo" . "00080200001" . $banderaExisteRegistros . "11.TXT";

        $direccion = __DIR__ . "/../../util/uploads/$archivoNombre";
        file_put_contents($direccion, null);
        $file = fopen($direccion, "w");
        $direccion = "\xEF\xBB\xBF" . $direccion;
        if (!ObjectUtil::isEmpty($data)) {
            foreach ($data as $item) {

                $linea = "";
                // linea = 1
                $linea .= $item['periodo'] . "00" . "|";

                $correlativo = explode($item["libro_codigo"] . "-", $item["cuo"])[1];
                $correlativoMovimiento = substr(str_replace("-", "", $correlativo), 1);

                $serie = "0";
                $numero = (preg_replace('/[^0-9]+/', '', $item["serie_numero"])) * 1;

                $fechaEmisionDUA = "0001-01-01";
                $tipoDocumentoDUA = "0";
                $serieDua = "0";
                $numeroDua = "0";

                $tipoDocumentoPagoSUNAT = "50";
                if ($item["cont_operacion_tipo_id"] == ContVoucherNegocio::OPERACION_TIPO_ID_SERVICIO_EXTRANJERA) {
                    $tipoDocumentoPagoSUNAT = "46";
                }

                $dataDUA = DocumentoNegocio::create()->obtenerDocumentoPagoImportacionXInvoiceComercialXTipoDocumentoSUNAT($item["id"], $tipoDocumentoPagoSUNAT);
                if (!ObjectUtil::isEmpty($dataDUA)) {
                    $serieNumeroDua = explode("-", $dataDUA[0]["serie_numero"]);
                    $serieDua = trim($serieNumeroDua[0]) * 1;
                    $numeroDua = trim($serieNumeroDua[1]) * 1;

                    $tipoDocumentoDUA = $dataDUA[0]["tipo_documento_sunat"];
                    $fechaEmisionDUA = $dataDUA[0]["fecha_emision"];
                }


                // linea = 2
                $linea .= str_pad($item["cuo"], 40, Util::rellenarEspacios(40)) . "|";
                // linea = 3
                $linea .= str_pad('M' . $correlativoMovimiento, 10, Util::rellenarEspacios(10)) . "|";
                // linea = 4
                $linea .= DateUtil::formatearFechaBDAaCadenaVw($item['fecha_emision']) . "|";
                // linea = 5
                $linea .= $item["tipo_documento"] . "|";
                // linea = 6 ==> serie del comprobante de pago o documento
                $linea .= str_pad($serie, 6, Util::rellenarEspacios(20)) . "|";
                // linea = 7
                $linea .= str_pad($numero, 20, Util::rellenarEspacios(20)) . "|";
                // linea = 8
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                // linea = 9
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                // linea = 10
                $linea .= str_pad(str_replace(",", "", number_format($item['total'], 2)), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //str_pad(str_replace(",", "", (number_format($item['total'], 2))), 12, Util::rellenarEspacios(12)) . "|";
                // linea = 11
                //DUA

                $linea .= str_pad($tipoDocumentoDUA, 2, Util::rellenarEspacios(2)) . "|";
                // linea = 12
                $linea .= str_pad($serieDua, 20, Util::rellenarEspacios(20), STR_PAD_LEFT) . "|"; //
                //$linea .= str_pad($serieDua, 20, Util::rellenarEspacios(20)) . "|";
                // linea = 13
                $linea .= substr(DateUtil::formatearFechaBDAaCadenaVw($fechaEmisionDUA), 6) . "|";
                // linea = 14
                $linea .= str_pad($numeroDua, 20, Util::rellenarEspacios(20), STR_PAD_LEFT) . "|"; //
                //$linea .= str_pad("", 20, Util::rellenarEspacios(20)) . "|";
                // linea = 15
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                //$linea .= str_pad(str_replace(",", "", (number_format($item['igv'], 2))), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|";
                // linea = 16 y 17
                if ($item['moneda_id'] == ContVoucherNegocio::MONEDA_ID_DOLARES) {
                    $linea .= "USD|" . number_format($item['tipo_cambio'], 3) . "|";
                } elseif ($item['moneda_id'] == ContVoucherNegocio::MONEDA_ID_SOLES) {
                    $linea .= "PEN|" . number_format($item['tipo_cambio'], 3) . "|";
                }
                // linea = 18
                $linea .= str_pad($item['pais_codigo'], 4, Util::rellenarEspacios(4)) . "|"; //
                // linea = 19
                $linea .= str_pad($item["nombre_persona"], 100, Util::rellenarEspacios(100 + substr_count(strtoupper($item["nombre_persona"]), 'Ñ'))) . "|"; //
                // linea = 20  ==> Domicilio en el extranjero del sujeto no docimiciado
                $linea .= str_pad("", 100, Util::rellenarEspacios(100)) . "|"; //
                // linea = 21
                $linea .= str_pad($item["codigo_identificacion_persona"], 15, Util::rellenarEspacios(15)) . "|"; //
                // linea = 22 ==> Numero de identificacion fiscal del beneficiario efectivo de los pagos
                $linea .= str_pad("0", 15, Util::rellenarEspacios(15)) . "|"; //
                // linea = 23 ==> denominación o razón social del beneficiario efectivo de los pagos.
                $linea .= str_pad("", 100, Util::rellenarEspacios(100)) . "|"; //
                // linea = 24 ==> pais de recidencia de beneficiario
                $linea .= str_pad("", 4, Util::rellenarEspacios(4)) . "|"; //
                // linea = 25 ==> vinculo entre el contribuyente y residente extranjero
                $linea .= "00|"; //str_pad("00",2, Util::rellenarEspacios(2)) . "|"; //
                // linea = 26 ==> renta bruta
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                // linea = 27 ==> deduccion costo de enajenacion
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                // linea = 28 ==> renta neta
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                // linea = 29 ==> tasa de retencion
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                // linea = 30 ==> impuesto retenido
                $linea .= str_pad(number_format(0, 2), 12, Util::rellenarEspacios(12), STR_PAD_LEFT) . "|"; //
                // linea = 31 ==> tabla 25
                $linea .= "00|"; //str_pad("00",2, Util::rellenarEspacios(2)) . "|"; //
                // linea = 32 ==> exoneracion aplicada
                $linea .= str_pad("", 1, Util::rellenarEspacios(1)) . "|"; //
                // linea = 33
                $linea .= str_pad("18", 2, Util::rellenarEspacios(2)) . "|"; //
                // linea = 34 ==> modalidad del servicio prestado tabla 32
                $linea .= "1|"; //
                // linea = 35 ==> aplicacion ultimo parrafo del articulo 76
                $linea .= str_pad("", 1, Util::rellenarEspacios(1)) . "|";
                // linea = 36
                $linea .= "0|"; //str_pad("duda",12, Util::rellenarEspacios(12)) . "|"; //
                // linea = 37 => Libre
                $linea .= str_pad("", 200, Util::rellenarEspacios(200));
                $lineaSalida = mb_convert_encoding($linea, "ISO-8859-1");
                fwrite($file, $lineaSalida . "\r\n");
            }
        }
        fclose($file);
        return $archivoNombre;
    }

    private function estilosExcel()
    {

        $this->estiloTituloReporte = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'strike' => false,
                'size' => 14
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => FALSE
            )
        );

        $this->estiloTituloColumnas = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'size' => 12
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => FALSE
            )
        );

        $this->estiloInformacion = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 10
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_HAIR,
                    'color' => array(
                        'rgb' => '000000'
                    )
                )
            ),
        );

        $this->estiloNumInformacion = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 10
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_HAIR,
                    'color' => array(
                        'rgb' => '000000'
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => FALSE
            )
        );

        $this->estiloTextoInformacion = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 10
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_HAIR,
                    'color' => array(
                        'rgb' => '000000'
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => FALSE
            )
        );
    }

    /**
     *
     * @param PHPExcel_IOFactory $excel
     * @param type $excelNombre
     * @param type $anio
     * @param type $mes
     * @param type $usuarioId
     * @throws WarningException
     */
    public function genera($path, $excelNombre, $anio, $mes, $usuarioId)
    {
        $excel = new Spreadsheet_Excel_Reader();
        $excel->setUTFEncoder('iconv');
        $excel->setOutputEncoding('UTF-8');
        $excel->read($path);
        $cells = $excel->sheets[0]["cells"];
        //var_dump($cells);
        //return;
        if (ObjectUtil::isEmpty($cells)) {
            throw new WarningException("No se ha especificado un excel correcto");
        }

        LibroTemp::create()->eliminar8();

        foreach ($cells as $key => $value) {
            if ($key > 1) {
                LibroTemp::create()->guardar8(
                    $anio . $mes . "00",
                    $this->obtenerCelda($cells, 1, $key),
                    "",
                    $this->obtenerCelda($cells, 2, $key, false),
                    $this->obtenerCelda($cells, 3, $key),
                    $this->obtenerCelda($cells, 4, $key),
                    $this->obtenerCelda($cells, 5, $key),
                    $this->obtenerImporte($this->obtenerCelda($cells, 6, $key, false)),
                    $this->obtenerImporte($this->obtenerCelda($cells, 7, $key, false)),
                    $this->obtenerImporte($this->obtenerCelda($cells, 8, $key, false)),
                    $this->obtenerCelda($cells, 9, $key),
                    $this->obtenerCelda($cells, 10, $key),
                    $this->obtenerCelda($cells, 11, $key),
                    $this->obtenerCelda($cells, 12, $key),
                    $this->obtenerImporte($this->obtenerCelda($cells, 13, $key, false)),
                    $this->obtenerCelda($cells, 14, $key),
                    $this->obtenerImporte($this->obtenerCelda($cells, 15, $key, false)),
                    $this->obtenerCelda($cells, 16, $key),
                    $this->obtenerCelda($cells, 17, $key, false),
                    $this->obtenerCelda($cells, 18, $key, false),
                    $this->obtenerCelda($cells, 19, $key, false),
                    $this->obtenerCelda($cells, 20, $key, false),
                    $this->obtenerCelda($cells, 21, $key, false),
                    $this->obtenerCelda($cells, 22, $key),
                    $this->obtenerCelda($cells, 23, $key),
                    $this->obtenerImporte($this->obtenerCelda($cells, 24, $key, false)),
                    $this->obtenerImporte($this->obtenerCelda($cells, 25, $key, false)),
                    $this->obtenerImporte($this->obtenerCelda($cells, 26, $key, false)),
                    $this->obtenerImporte($this->obtenerCelda($cells, 27, $key, false)),
                    $this->obtenerImporte($this->obtenerCelda($cells, 28, $key, false)),
                    $this->obtenerCelda($cells, 29, $key),
                    $this->obtenerCelda($cells, 30, $key),
                    $this->obtenerCelda($cells, 31, $key),
                    $this->obtenerCelda($cells, 32, $key),
                    $this->obtenerCelda($cells, 33, $key),
                    $this->obtenerCelda($cells, 34, $key),
                    $this->obtenerCelda($cells, 35, $key)
                );
            }
        }

        $archivoNombre = "LE20600143361$anio$mes" . "00080200001111.TXT";
        $direccion = __DIR__ . "/../../util/uploads/$archivoNombre";
        file_put_contents($direccion, null);
        $file = fopen($direccion, "w");
        $direccion = "\xEF\xBB\xBF" . $direccion;
        $lista = LibroTemp::create()->listar8();
        foreach ($lista as $linea) {
            fwrite($file, $linea["fila"] . PHP_EOL);
        }
        //fwrite($file, "Otra más" . PHP_EOL);
        fclose($file);

        return $archivoNombre;
    }

    private function obtenerCelda($cells, $col, $row, $limpiaString = true)
    {
        if (!array_key_exists($row, $cells))
            return "";
        $tildes = array("á" => "a", "é" => "e", "í" => "i", "ó" => "o", "ú" => "u", "." => "_", " " => "");
        $valor = ($limpiaString) ? $this->limpiarString($cells[$row][$col]) : $cells[$row][$col];
        return trim(strtr(trim($valor), $tildes) . "");
        //        return trim($this->limpiarString($cells[$row][$col]));
    }

    private function limpiarString($texto)
    {
        $textoLimpio = preg_replace('([^A-Za-z0-9])', '', $texto);
        return $textoLimpio;
    }

    private function obtenerImporte($importe)
    {
        if (strlen("" . importe) < 2) {
            $numero = str_replace(array("-", ",", "*", "?"), "", "" . $importe);
        } elseif (strpos($importe, "(")) {
            $numero = "-" . str_repeat(array("(", ")"), "", "" . $importe);
        } else {
            $numero = str_replace(array(",", "*", "?"), "", "" . $importe);
        }
        $numero = str_replace("_", ".", $numero);
        $numero = (ObjectUtil::isEmpty($numero)) ? (float) 0.00 : (float) $numero;
        return $numero;
    }

    private function formatoFecha($sFecha)
    {
        $aFecha = explode("/", $sFecha);
        return $aFecha[1] . "/" . $aFecha[0] . "/" . $aFecha[2];
    }

    //    ===================== Inicio Pago Documento Detraccion======================

    public function listarRegistroComprasXCriterioDetraccion($criterios)
    {
        if (!ObjectUtil::isEmpty($criterios[0]['empresa'])) {
            $empresaId = $criterios[0]['empresa'];
        }
        if (!ObjectUtil::isEmpty($criterios[0]['persona'])) {
            $personaId = $criterios[0]['persona'];
        } else {
            $personaId = null;
        }
        if (!ObjectUtil::isEmpty($criterios[0]['serie'])) {
            $serie = $criterios[0]['serie'];
        }
        if (!ObjectUtil::isEmpty($criterios[0]['numero'])) {
            $numero = $criterios[0]['numero'];
        }
        if (!ObjectUtil::isEmpty($criterios[0]['fechaEmisionDesde'])) {
            $fechaInicio = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaEmisionDesde']);
        }
        if (!ObjectUtil::isEmpty($criterios[0]['fechaEmisionHasta'])) {
            $fechaFin = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaEmisionHasta']);
        }
        if (!ObjectUtil::isEmpty($criterios[0]['mostrar'])) {
            $mostrarPagados = $criterios[0]['mostrar'];
        }
        return RegistroCompras::create()->listaRegistroComprasXCriterios($empresaId, $personaId, $serie, $numero, $fechaInicio, $fechaFin, $mostrarPagados);
    }

    //    ===================== Inicio Pago Documento Detraccion======================
}
