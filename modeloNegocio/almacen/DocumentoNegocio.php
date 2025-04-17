<?php

require_once __DIR__ . '/../../modelo/almacen/Documento.php';
require_once __DIR__ . '/../../modelo/almacen/MovimientoTipoDocumentoTipo.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/DocumentoDatoValorNegocio.php';
require_once __DIR__ . '/DocumentoTipoNegocio.php';
require_once __DIR__ . '/PeriodoNegocio.php';
require_once __DIR__ . '/../../util/DateUtil.php';
require_once __DIR__ . '/EmailPlantillaNegocio.php';
require_once __DIR__ . '/EmailEnvioNegocio.php';

class DocumentoNegocio extends ModeloNegocioBase
{
  /**
   *
   * @return DocumentoNegocio
   */
  static function create()
  {
    return parent::create();
  }

  // TODO: Fin Guardar Documento - Percepcion
  public function guardar($documentoTipoId, $movimientoId, $adjuntoId, $camposDinamicos, $estado, $usuarioCreacionId, $monedaId, $comentarioDoc = null, $descripcionDoc = null, $utilidadTotal = null, $utilidadPorcentajeTotal = null, $tipoPago = null, $periodoId = null, $datosExtras = null, $contOperacionTipoId = null, $esEar = 0, $igv_porcentaje = null)
  {
    $personaId = null;
    $codigo = null;
    $serie = null;
    $numero = null;
    $fechaEmision = null;
    $fechaVencimiento = null;
    $fechaTentativa = null;
    $descripcion = null;
    $comentario = null;
    $importeFlete = null;
    $importeSeguro = null;
    $importeOtros = null;
    $importeExoneracion = null;
    $importeTotal = null;
    $importeIgv = null;
    $importeIcbp = null;
    $importeSubTotal = null;
    $importeNoAfecto = null;
    $organizadorId = null;
    $direccionId = null;
    $percepcion = null;
    $cuentaId = null;
    $actividadId = null;
    $retencionDetraccionId = null;
    $cambioPersonalizado = null;
    $archivoAdjunto = null;
    $archivoAdjuntoMulti = null;
    $banderaProductoDuplicado = 0;
    $detraccionId = null;
    $es_rq = null;

    $numeros = array();
    $cadenas = array();
    $fechas = array();
    $listas = array();
    $validarSerieNumero = false;
    $notaCreditoTipo13 = false;
    $grupoUnico = array();
    // 1. Obtenemos la configuracion actual del tipo de documento
    $configuraciones = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDatoSimple($documentoTipoId);
    if (!ObjectUtil::isEmpty($configuraciones)) {
      if (ObjectUtil::isEmpty($camposDinamicos)) {
        throw new WarningException("No se especificaron los campos minimos necesarios para guardar el documento");
      }
      foreach ($configuraciones as $itemDtd) {
        foreach ($camposDinamicos as $valorDtd) {
          if ((int) $itemDtd["id"] === (int) $valorDtd["id"]) {
            $valor = ObjectUtil::isEmpty($valorDtd["valor"]) ? null : $valorDtd["valor"];
            if ((int) $itemDtd["opcional"] === 0 && ObjectUtil::isEmpty($valor)) {
              throw new WarningException("No se especificó un valor válido para " . $itemDtd["descripcion"]);
            }

            if (!ObjectUtil::isEmpty($itemDtd["grupo_unico"])) {
              $descripcionUnicoGrupo = '';
              if ($itemDtd["unico"] == 1) {
                $descripcionUnicoGrupo = $itemDtd["descripcion"];
              }

              if (ObjectUtil::isEmpty($grupoUnico)) {
                $grupo = array('grupo_unico' => $itemDtd["grupo_unico"], 'valor' => (is_numeric($valor) ? (int) $valor : $valor), 'descripcion' => $descripcionUnicoGrupo);
                array_push($grupoUnico, $grupo);
              } else {
                $banderaGrupo = false;

                foreach ($grupoUnico as $indGrupo => $itemGrupo) {
                  if ($itemGrupo['grupo_unico'] == $itemDtd["grupo_unico"]) {
                    $banderaGrupo = true;
                    $grupoUnico[$indGrupo]['valor'] = $itemGrupo['valor'] . "-" . (is_numeric($valor) ? (int) $valor : $valor);

                    if (!ObjectUtil::isEmpty($descripcionUnicoGrupo)) {
                      $grupoUnico[$indGrupo]['descripcion'] = $descripcionUnicoGrupo;
                    }
                  }
                }
                if (!$banderaGrupo) {
                  $grupo = array('grupo_unico' => $itemDtd["grupo_unico"], 'valor' => (is_numeric($valor) ? (int) $valor : $valor), 'descripcion' => $descripcionUnicoGrupo);
                  array_push($grupoUnico, $grupo);
                }
              }
            }

            switch ((int) $valorDtd["tipo"]) {
              case DocumentoTipoNegocio::DATO_CODIGO:
                $codigo = $valor;
                break;
              case DocumentoTipoNegocio::DATO_PERSONA:
                $personaId = $valor;
                break;
              case DocumentoTipoNegocio::DATO_SERIE:
                $serie = $valor;
                break;
              case DocumentoTipoNegocio::DATO_NUMERO:
                $validarSerieNumero = ($itemDtd["unico"] == 1);
                $numero = $valor;
                break;
              case DocumentoTipoNegocio::DATO_FECHA_EMISION:
                $fechaEmision = DateUtil::formatearCadenaACadenaBD($valor);
                break;
              case DocumentoTipoNegocio::DATO_FECHA_VENCIMIENTO:
                $fechaVencimiento = DateUtil::formatearCadenaACadenaBD($valor);
                break;
              case DocumentoTipoNegocio::DATO_FECHA_TENTATIVA:
                $fechaTentativa = DateUtil::formatearCadenaACadenaBD($valor);
                break;
              case DocumentoTipoNegocio::DATO_DESCRIPCION:
                $descripcion = $valor;
                break;
              case DocumentoTipoNegocio::DATO_COMENTARIO:
                $comentario = $valor;
                break;
              case DocumentoTipoNegocio::DATO_FLETE_DOCUMENTO:
                $importeFlete = $valor;
                break;
              case DocumentoTipoNegocio::DATO_SEGURO_DOCUMENTO:
                $importeSeguro = $valor;
                break;
              case DocumentoTipoNegocio::DATO_IMPORTE_TOTAL:
                $importeTotal = $valor;
                break;
              case DocumentoTipoNegocio::DATO_IMPORTE_IGV:
                $importeIgv = $valor;
                break;
              case DocumentoTipoNegocio::DATO_IMPORTE_ICBP:
                $importeIcbp = $valor;
                break;
              case DocumentoTipoNegocio::DATO_IMPORTE_SUB_TOTAL:
                $importeSubTotal = $valor;
                break;
              case DocumentoTipoNegocio::DATO_IMPORTE_OTROS:
                $importeOtros = $valor;
                break;
              case DocumentoTipoNegocio::DATO_IMPORTE_EXONERADO:
                $importeExoneracion = $valor;
                break;
              case DocumentoTipoNegocio::DATO_ENTERO:
              case DocumentoTipoNegocio::DATO_FOB:
              case DocumentoTipoNegocio::DATO_CIF:
              case DocumentoTipoNegocio::DATO_FLETE_SUNAT:
                array_push($numeros, $valorDtd);
                break;
              case DocumentoTipoNegocio::DATO_CADENA:
                array_push($cadenas, $valorDtd);
                break;
              case DocumentoTipoNegocio::DATO_FECHA:
                array_push($fechas, $valorDtd);
                break;
              case DocumentoTipoNegocio::DATO_LISTA:
                array_push($listas, $valorDtd);
                //Nota de credito tipo13
                $idValor = $valorDtd['valor'] == ""? null:$valorDtd['valor'];
                $resDTDL = DocumentoTipoDatoLista::create()->obtenerPorId($idValor);
                if ($resDTDL[0]['valor'] == 13) {
                  $notaCreditoTipo13 = true;
                }
                break;
              case DocumentoTipoNegocio::DATO_ORGANIZADOR_DESTINO:
                $organizadorId = $valor;
                break;
              case DocumentoTipoNegocio::DATO_DIRECCION:
                $direccionId = $valor;
                break;
              case DocumentoTipoNegocio::DATO_PERCEPCION:
                // array_push($numeros, $valorDtd);
                // $importeNoAfecto = $valor;
                $percepcion = $valor;
                break;
              case DocumentoTipoNegocio::DATO_CUENTA:
                $cuentaId = $valor;
                break;
              case DocumentoTipoNegocio::DATO_ACTIVIDAD:
                $actividadId = $valor;
                break;
              case DocumentoTipoNegocio::DATO_RETENCION_DETRACCION:
                $retencionDetraccionId = $valor;
                break;
              case DocumentoTipoNegocio::DATO_OTRA_PERSONA:
                array_push($listas, $valorDtd);
                if($documentoTipoId == Configuraciones::COTIZACIONES){
                  $personaId = $valor;
                }
                break;
              case DocumentoTipoNegocio::DATO_CAMBIO_PERSONALIZADO:
                $cambioPersonalizado = $valor;
                break;
              case DocumentoTipoNegocio::DATO_VENDEDOR:
                array_push($listas, $valorDtd);
                break;
              case DocumentoTipoNegocio::DATO_ARCHIVO_ADJUNTO:
                $archivoAdjunto = $valor;
                break;
              case DocumentoTipoNegocio::DATO_ARCHIVO_ADJUNTO_MULTI:
                $archivoAdjuntoMulti = $valor;
                break;
              case DocumentoTipoNegocio::DATO_PRODUCTO_DUPLICADO:
                $banderaProductoDuplicado = $valor;
                array_push($cadenas, $valorDtd);
                break;
              case DocumentoTipoNegocio::DATO_DETRACCION_TIPO:
                $detraccionId = $valor;
                break;
              case 39: //es cotizacion tottus
                array_push($cadenas, $valorDtd);
                break;
              case 40: //lista tottus
                array_push($listas, $valorDtd);
                break;
              case DocumentoTipoNegocio::DATO_NUM_LICENCIA_CONDUCIR: //Conductor
                array_push($cadenas, $valorDtd);
                break;
              case DocumentoTipoNegocio::DATO_TIPO_REQUERIMIENTO: //Tipo requerimiento
                if($documentoTipoId ==  Configuraciones::SOLICITUD_REQUERIMIENTO || $documentoTipoId == Configuraciones::ORDEN_SERVICIO || $documentoTipoId == Configuraciones::REQUERIMIENTO_AREA || $documentoTipoId == Configuraciones::GENERAR_COTIZACION){
                  $es_rq = $valor;
                  array_push($listas, $valorDtd);
                }
                break;
              case DocumentoTipoNegocio::DATO_AREA: //Area
                  array_push($cadenas, $valorDtd);
                break;
              case DocumentoTipoNegocio::DATO_GRUPO_PRODUCTO: //Area
                  array_push($cadenas, $valorDtd);
                break;
              case DocumentoTipoNegocio::DATO_ENTREGA_EN_DESTINO: //
                  array_push($cadenas, $valorDtd);
                break;
              case DocumentoTipoNegocio::DATO_UO:
                  array_push($cadenas, $valorDtd);
                break;
              case DocumentoTipoNegocio::DATO_CUENTA_PROVEEDOR: //
                  array_push($cadenas, $valorDtd);
                break;
              case DocumentoTipoNegocio::CONDICION_PAGO:
                  array_push($listas, $valorDtd);
                break;
              case DocumentoTipoNegocio::UNIDAD_MINERA:
                  array_push($listas, $valorDtd);
                break;
              case DocumentoTipoNegocio::CUENTA_GASTOS:
                  array_push($listas, $valorDtd);
                break;                              
              default:
            }
            break;
          }
        }
      }
    }
    // if ($validarSerieNumero) {
    // $documentosRepetidos = Documento::create()->obtenerXSerieNumero($documentoTipoId, $serie, $numero);
    // if (!ObjectUtil::isEmpty($documentosRepetidos)) {
    // $nuevoNumero = DocumentoNegocio::create()->obtenerNumeroAutoIncrementalXDocumentoTipo($documentoTipoId);
    // throw new WarningException("El número del documento que desea registrar está duplicado. "." Usar el siguiente: ".$serie."-".$nuevoNumero);
    // }
    // }

    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);

    // Validación de correlatividad numérica con la fecha de emisión
    if ($dataDocumentoTipo[0]["tipo"] == 1 || $dataDocumentoTipo[0]["identificador_negocio"] == 6) {
      $dataRes = Documento::create()->validarCorrelatividadNumericaConFechaEmision($documentoTipoId, $serie, $numero, $fechaEmision);

      if ($dataRes[0]['validacion'] == 0) {
        throw new WarningException("Validación de correlatividad numérica: cambie la fecha de emisión o el número del documento.");
      }
    }

    if (!ObjectUtil::isEmpty($grupoUnico)) {
      foreach ($grupoUnico as $indGrupo => $itemGrupo) {
        $resSN = strpos(strtoupper($itemGrupo['valor']), 'S/N');
        if ($resSN === false) {
          $documentoRepetido = Documento::create()->obtenerXDocumentoTipoXGrupoUnico($documentoTipoId, $itemGrupo['grupo_unico'], $itemGrupo['valor']);
          if (!ObjectUtil::isEmpty($documentoRepetido)) {
            if($documentoTipoId == Configuraciones::SOLICITUD_REQUERIMIENTO || $documentoTipoId == Configuraciones::REQUERIMIENTO_AREA){
              $banderaNumero = false;
              while($banderaNumero == false){
                $nuevocorrelativo = DocumentoNegocio::create()->obtenerNumeroAutoXDocumentoTipo($documentoTipoId);
                $documentosRepetidos = Documento::create()->obtenerXSerieNumero($documentoTipoId, $serie, $nuevocorrelativo);                
                if (ObjectUtil::isEmpty($documentosRepetidos)) {
                  $banderaNumero = true;
                  $numero = $nuevocorrelativo;
                }
              }
            }else{
              throw new WarningException($itemGrupo['descripcion'] . " está duplicado");
            }
          }
        }
      }
    }

    if (ObjectUtil::isEmpty($descripcion)) {
      $descripcion = $descripcionDoc;
    }

    // $mes_curso = getdate()["mon"];
    //$anio_curso = getdate()["year"];
    $arrayFecha = $fechaEmision !== null ? explode("-", $fechaEmision) : []; //Dividimos la fecha, para obetener el Año-Mes-Dia.
    $serieAnio = $arrayFecha[0];
    $serieMes = $arrayFecha[1];

    if (strlen($serieMes) == 1) {
      $serieMes = "0" . $serieMes;
    }
    // $dataDocumentoTipo[0]["tipo"] == 19
    if ($dataDocumentoTipo[0]["tipo"] == 5 || $dataDocumentoTipo[0]["tipo"] == 6) {
      // PARA ESTOS DOCUMENTO EL PRIMER CARACTER DE LA SERIE EMPIEZA POR "P".
      $serieCorrealativa = Documento::create()->obtenerNumeroSerieCorelativoPagosCobransa($dataDocumentoTipo[0]["tipo"], "P" . $serieAnio . $serieMes); //P20161000001
      if (!ObjectUtil::isEmpty($serieCorrealativa)) {
        $cadenaSerie = substr($serieCorrealativa[0]["codigo"], 7, 11);
        $ultimoNumero = (int) $cadenaSerie;
        $ultimoNumero = $this->generaCeros($ultimoNumero + (int) 1);
      } else {
        $ultimoNumero = $this->generaCeros(1);
      }
      $codigo = "P" . $serieAnio . $serieMes . $ultimoNumero;
    } else if ($dataDocumentoTipo[0]["tipo"] == 2 || $dataDocumentoTipo[0]["tipo"] == 3) {
      // PARA ESTOS DOCUMENTO EL PRIMER CARACTER DE LA SERIE EMPIEZA POR "C".
      $serieCorrealativa = Documento::create()->obtenerNumeroSerieCorelativoPagosCobransa($dataDocumentoTipo[0]["tipo"], "C" . $serieAnio . $serieMes); //C20161000001
      if (!ObjectUtil::isEmpty($serieCorrealativa)) {
        $ultimoNumero = (int) substr($serieCorrealativa[0]["codigo"], 7, 11);
        $ultimoNumero = $this->generaCeros($ultimoNumero + (int) 1);
      } else {
        $ultimoNumero = $this->generaCeros(1);
      }
      $codigo = "C" . $serieAnio . $serieMes . $ultimoNumero;
    }

    //$documento = Documento::create()->guardar($documentoTipoId, $movimientoId, $personaId,$direccionId,$organizadorId, $adjuntoId, $codigo, $serie, $numero, $fechaEmision, $fechaVencimiento, $fechaTentativa, $descripcion, $comentario, $importeTotal, $importeIgv, $importeSubTotal, $estado, $usuarioCreacionId);
    if (ObjectUtil::isEmpty($periodoId)) {
      throw new WarningException('Periodo inválido');
    } else {
      // VALIDAR QUE EL PERIODO ESTE ABIERTO
      $dataPeriodo = PeriodoNegocio::create()->obtenerPeriodoXid($periodoId);
      $dataPeriodo = $dataPeriodo->dataPeriodo;
      if ($dataPeriodo[0]['indicador'] != 2) {
        throw new WarningException('El periodo ' . $dataPeriodo[0]['anio'] . '-' . ($dataPeriodo[0]['mes'] * 1 < 10 ? '0' . $dataPeriodo[0]['mes'] : $dataPeriodo[0]['mes']) . ' no está abierto');
      }
    }

    // throw new WarningException('Periodo inválido');
    // guardamos total en subtotal
    $importeSubTotal = (!ObjectUtil::isEmpty($importeSubTotal) ? $importeSubTotal : $importeTotal);
    if ($notaCreditoTipo13) {
      $importeTotal = 0.0;
      $importeIgv = 0.0;
      $importeSubTotal = 0.0;
    }

    if($documentoTipoId == 280 || $documentoTipoId == 281 || $documentoTipoId == 283){
      $personaId = PersonaNegocio::create()->obtenerPersonaXUsuarioId($usuarioCreacionId)[0]['id'];
    }
    
    $documento = Documento::create()->guardar($documentoTipoId, $movimientoId, $personaId, $direccionId, $organizadorId, $adjuntoId, $codigo, $serie, $numero, $fechaEmision, $fechaVencimiento, $fechaTentativa, $descripcion, $comentarioDoc, $importeTotal, $importeIgv, $importeSubTotal, $estado, $monedaId, $usuarioCreacionId, $cuentaId, $actividadId, $retencionDetraccionId, $utilidadTotal, $utilidadPorcentajeTotal, $cambioPersonalizado, $tipoPago, $importeNoAfecto, $periodoId, $banderaProductoDuplicado, $detraccionId, $datosExtras['afecto_detraccion_retencion'], $datosExtras['porcentaje_afecto'], $datosExtras['monto_detraccion_retencion'], $contOperacionTipoId, $esEar, $importeOtros, $importeExoneracion, $importeIcbp, $datosExtras['afecto_impuesto'], $percepcion, $igv_porcentaje, $es_rq);

    $documentoId = $this->validateResponse($documento);
    if (ObjectUtil::isEmpty($documentoId)) {
      throw new WarningException("No se pudo guardar el documento");
    }
    // Ahora guardamos los campos dinámicos
    // Campos numéricos
    foreach ($numeros as $item) {
      DocumentoDatoValorNegocio::create()->guardarNumero($documentoId, $item["id"], $item["valor"], $usuarioCreacionId);
    }

    // Campos cadenas
    foreach ($cadenas as $item) {
      DocumentoDatoValorNegocio::create()->guardarCadena($documentoId, $item["id"], $item["valor"], $usuarioCreacionId);
    }

    // Campos fechas
    foreach ($fechas as $item) {
      DocumentoDatoValorNegocio::create()->guardarFecha($documentoId, $item["id"], DateUtil::formatearCadenaACadenaBD($item["valor"]), $usuarioCreacionId);
    }

    // Campos listas
    foreach ($listas as $item) {
      DocumentoDatoValorNegocio::create()->guardarLista($documentoId, $item["id"], $item["valor"], $usuarioCreacionId);
    }

    // DOCUMENTO ADJUNTO
    if (!ObjectUtil::isEmpty($archivoAdjunto['data'])) {
      $decode = Util::base64ToImage($archivoAdjunto['data']);
      $nombreArchivo = $archivoAdjunto['nombre'];
      $contenidoArchivo = $archivoAdjunto['contenido_archivo'];
      $pos = strripos($nombreArchivo, '.');
      $ext = substr($nombreArchivo, $pos);

      $hoy = date("YmdHis");
      $nombreGenerado = $documentoId . $hoy . $usuarioCreacionId . $ext;
      $url = __DIR__ . '/../../util/uploads/documentoAdjunto/' . $nombreGenerado;

      file_put_contents($url, $decode);

      $resAdjunto = Documento::create()->insertarDocumentoAdjunto($documentoId, $nombreArchivo, $nombreGenerado, $usuarioCreacionId, $contenidoArchivo);
    }

    // DOCUMENTO ADJUNTO MULTIPLE
    if (!ObjectUtil::isEmpty($archivoAdjuntoMulti)) {
      $resAdjunto = $this->guardarArchivosXDocumentoID($documentoId, $archivoAdjuntoMulti, null, $usuarioCreacionId);
    }

    //4. Insertar documento_documento_estado
    if (ObjectUtil::isEmpty($movimientoId)) {
      $movimientoTipoDocumentoTipo = MovimientoTipoDocumentoTipo::create()->obtenerXDocumentoTipo($documentoTipoId);
    } else {
      $movimientoTipoDocumentoTipo = MovimientoTipoDocumentoTipo::create()->obtenerXMovimiento($movimientoId, $documentoTipoId);
    }

    $documento_estado = $movimientoTipoDocumentoTipo['0']['documento_estado_id'];
    if (!ObjectUtil::isEmpty($contOperacionTipoId)) {
      $documento_estado = 8;
    } elseif (ObjectUtil::isEmpty($documento_estado)) {
      //throw new WarningException("No se encontro estado en movimiento tipo documento tipo");
      $documento_estado = 1;
    }
    $this->insertarDocumentoDocumentoEstado($documentoId, $documento_estado, $usuarioCreacionId);

    return $documento;
  }
  // TODO: Fin Guardar Documento - Percepcion

  function guardarArchivosXDocumentoID($documentoId, $lstDocumento, $lstDocEliminado, $usuCreacion)
  {
    if ($documentoId != null) {
      //Eliminando archivos
      foreach ($lstDocEliminado as $d) {
        //Dando de baja en documento_adjunto
        if (!strpos($d[0]['id'], 't')) {
          $resAdjunto = Documento::create()->insertarActualizarDocumentoAdjunto($d[0]['id'], null, null, null, null, 0);
          if ($resAdjunto[0]['vout_exito'] != 1) {
            throw new WarningException($resAdjunto[0]['vout_mensaje']);
          }
        }
      }
      //Insertando documento_adjunto
      foreach ($lstDocumento as $d) {
        //Se valida que el ID contenga el prefijo temporal "t" para que se opere, si no lo encuentra ya estaría registrado

        if (strpos($d['id'], 't') !== false) {

          //DOCUMENTO ADJUNTO
          if (!ObjectUtil::isEmpty($d['data'])) {

            $decode = Util::base64ToImage($d['data']);
            $nombreArchivo = $d['archivo'];
            $pos = strripos($nombreArchivo, '.');
            $ext = substr($nombreArchivo, $pos);

            $hoy = date("YmdHis").substr((string)microtime(), 2, 3);;
            $nombreGenerado = $documentoId . $hoy . $usuCreacion . $ext;
            $url = __DIR__ . '/../../util/uploads/documentoAdjunto/' . $nombreGenerado;

            file_put_contents($url, $decode);
            $tipo_archivoId = $d["tipo_archivoId"];

            $contenido_archivo = $d["contenido_archivo"];
            $resAdjunto = Documento::create()->insertarActualizarDocumentoAdjunto(null, $documentoId, $nombreArchivo, $nombreGenerado, $usuCreacion, null,$tipo_archivoId, $contenido_archivo);
            if ($resAdjunto[0]['vout_exito'] != 1) {
              throw new WarningException($resAdjunto[0]['vout_mensaje']);
            }
          }
        }
      }
    } else {
      throw new WarningException("No existe documento para relacionar con el archivo adjunto");
    }
    return $resAdjunto;
  }

  function obtenerXId($documentoId, $documentoTipoId)
  {
    return Documento::create()->obtenerXId($documentoId, $documentoTipoId);
  }

  function anular($documentoId)
  {
    return Documento::create()->anular($documentoId);
  }

  function eliminar($documentoId)
  {
    return Documento::create()->eliminar($documentoId);
  }

  function obtenerDocumentoAPagar($documentoId, $fechaPago = null)
  {
    if (!ObjectUtil::isEmpty($fechaPago)) {
      $fechaPago = DateUtil::formatearCadenaACadenaBD($fechaPago);
    }

    return Documento::create()->obtenerDocumentoAPagar($documentoId, $fechaPago);
  }
    //agregado
  function ActualizarDocumentoEar($earId, $documentoIdPagoReembolso) 
  {
        return Documento::create()->actualizarEARDocumentoReembolso($earId, $documentoIdPagoReembolso);
  }
  function obtenerFechaPrimerDocumento()
  {
    return Documento::create()->obtenerFechaPrimerDocumento();
  }

  function obtenerDetalleDocumento($documentoId)
  {
    return Documento::create()->obtenerDetalleDocumento($documentoId);
  }

  function obtenerComentarioDocumento($documentoId)
  {
    return Documento::create()->obtenerComentarioDocumento($documentoId);
  }

  function obtenerNumeroAutoXDocumentoTipo($documentoTipoId, $serie = NULL)
  {
    $numero = Documento::create()->obtenerNumeroAutoXDocumentoTipo($documentoTipoId, $serie);
    return (ObjectUtil::isEmpty($numero)) ? '' : $numero[0]["numero"];
  }

  function obtenerNumeroAutoIncrementalXDocumentoTipo($documentoTipoId)
  {
    $numero = Documento::create()->obtenerNumeroAutoIncrementalXDocumentoTipo($documentoTipoId);
    return (ObjectUtil::isEmpty($numero)) ? '' : $numero[0]["numero"];
  }

  function obtenerDetalleDocumentoPago($documentoId)
  {
    return Documento::create()->obtenerDetalleDocumentoPago($documentoId);
  }

  function obtenerDataDocumentoACopiar($documentoTipoDestinoId, $documentoTipoOrigenId, $documentoId)
  {
    return Documento::create()->obtenerDataDocumentoACopiar($documentoTipoOrigenId, $documentoTipoDestinoId, $documentoId);
  }

  function guardarDocumentoRelacionado($documentoId, $documentoRelacionadoId, $valorCheck, $estado, $usuarioCreacion, $relacionEar = null)
  {
    return Documento::create()->guardarDocumentoRelacionado($documentoId, $documentoRelacionadoId, $valorCheck, $estado, $usuarioCreacion, $relacionEar);
  }

  function insertarDocumentoDocumentoEstado($documentoId, $documento_estado, $usuarioId,  $comentario = NULL)
  {
    return Documento::create()->insertarDocumentoDocumentoEstado($documentoId, $documento_estado, $usuarioId,  $comentario);
  }

  function ActualizarDocumentoEstadoId($documentoId, $documentoEstadoId, $usuarioId, $accion = NULL, $comentario = NULL)
  {
    return Documento::create()->ActualizarDocumentoEstadoId($documentoId, $documentoEstadoId, $usuarioId, $accion, $comentario);
  }

  function obtenerDocumentosRelacionadosXDocumentoId($documentoId)
  {
    return Documento::create()->obtenerDocumentosRelacionadosXDocumentoId($documentoId);
  }

  function obtenerSoloDocumentosRelacionados($documentoId)
  {
    return Documento::create()->obtenerSoloDocumentosRelacionados($documentoId);
  }

  function obtenerDocumentosRelacionados($documentoId)
  {
    return Documento::create()->obtenerDocumentosRelacionados($documentoId);
  }

  function obtenerDataDocumentoACopiarRelacionada($documentoOrigenId, $documentoDestinoId, $documentoId)
  {
    return Documento::create()->obtenerDataDocumentoACopiarRelacionada($documentoOrigenId, $documentoDestinoId, $documentoId);
  }

  function obtenerDireccionEmpresa($documentoId)
  {
    return Documento::create()->obtenerDireccionEmpresa($documentoId);
  }

  function actualizarTipoRetencionDetraccion($documentoId, $tipoRetencionDetraccion)
  {
    return Documento::create()->actualizarTipoRetencionDetraccion($documentoId, $tipoRetencionDetraccion);
  }

  function actualizarComentarioDocumento($documentoId, $comentario)
  {
    return Documento::create()->actualizarComentarioDocumento($documentoId, $comentario);
  }

  function buscarDocumentosXOpcionXSerieNumero($opcionId, $busqueda)
  {
    return Documento::create()->buscarDocumentosXOpcionXSerieNumero($opcionId, $busqueda);
  }

  // TODO: Inicio Guardar Edicion
  function guardarEdicionDocumento($documentoId, $camposDinamicos, $comentario = null, $periodoId = null, $tipoPago = null, $monedaId = null, $usuarioCreacionId = null, $datosExtras = null, $contOperacionTipoId = null, $igv_porcentaje = null)
  {
    $dataDocumento = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($documentoId);

    $personaId = null;
    $codigo = null;
    $serie = null;
    $numero = null;
    $fechaEmision = null;
    $fechaVencimiento = null;
    $fechaTentativa = null;
    $descripcion = null;
    // $comentario = null;
    $importeFlete = null;
    $importeSeguro = null;
    $importeOtros = null;
    $importeExoneracion = null;
    $importeTotal = null;
    $importeIgv = null;
    $importeIcbp = null;
    $importeSubTotal = null;
    $organizadorId = null;
    $direccionId = null;
    // $importeNoAfecto = null;
    $percepcion = null;
    $cuentaId = null;
    $actividadId = null;
    $retencionDetraccionId = null;
    $numeros = array();
    $cadenas = array();
    $fechas = array();
    $listas = array();
    $archivoAdjunto = null;
    $archivoAdjuntoMulti = null;
    $banderaProductoDuplicado = 0;
    $detraccionId = null;

    if (ObjectUtil::isEmpty($camposDinamicos)) {
      throw new WarningException("No se especificaron los campos mínimos necesarios para guardar el documento");
    }

    foreach ($camposDinamicos as $valorDtd) {
      $valor = ObjectUtil::isEmpty($valorDtd["valor"]) ? null : $valorDtd["valor"];
      if ((int) $valorDtd["opcional"] === 0 && ObjectUtil::isEmpty($valor)) {
        throw new WarningException("No se especificó un valor válido para " . $valorDtd["descripcion"]);
      }
      switch ((int) $valorDtd["tipo"]) {
        case DocumentoTipoNegocio::DATO_CODIGO:
          $codigo = $valor;
          break;
        case DocumentoTipoNegocio::DATO_PERSONA:
          $personaId = $valor;
          break;
        case DocumentoTipoNegocio::DATO_SERIE:
          $serie = $valor;
          break;
        case DocumentoTipoNegocio::DATO_NUMERO:
          // $validarSerieNumero = ($itemDtd["unico"] == 1);
          $numero = $valor;
          break;
        case DocumentoTipoNegocio::DATO_FECHA_EMISION:
          $fechaEmision = DateUtil::formatearCadenaACadenaBD($valor);
          break;
        case DocumentoTipoNegocio::DATO_FECHA_VENCIMIENTO:
        case DocumentoTipoNegocio::FECHA_VENCIMIENTO_COTIZACION:
          $fechaVencimiento = DateUtil::formatearCadenaACadenaBD($valor);
          break;
        case DocumentoTipoNegocio::DATO_FECHA_TENTATIVA:
          $fechaTentativa = DateUtil::formatearCadenaACadenaBD($valor);
          break;
        case DocumentoTipoNegocio::DATO_DESCRIPCION:
          $descripcion = $valor;
          break;
          // case DocumentoTipoNegocio::DATO_COMENTARIO:
          // $comentario = $valor;
          // break;
        case DocumentoTipoNegocio::DATO_IMPORTE_TOTAL:
          $importeTotal = $valor;
          break;
        case DocumentoTipoNegocio::DATO_IMPORTE_IGV:
          $importeIgv = $valor;
          break;
        case DocumentoTipoNegocio::DATO_IMPORTE_ICBP:
          $importeIcbp = $valor;
          break;
        case DocumentoTipoNegocio::DATO_IMPORTE_SUB_TOTAL:
          $importeSubTotal = $valor;
          break;
        case DocumentoTipoNegocio::DATO_IMPORTE_OTROS:
          $importeOtros = $valor;
          break;
        case DocumentoTipoNegocio::DATO_IMPORTE_EXONERADO:
          $importeExoneracion = $valor;
          break;
        case DocumentoTipoNegocio::DATO_ENTERO:
          array_push($numeros, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_CADENA:
          array_push($cadenas, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_FECHA:
          array_push($fechas, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_LISTA:
          array_push($listas, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_ORGANIZADOR_DESTINO:
          $organizadorId = $valor;
          break;
        case DocumentoTipoNegocio::DATO_DIRECCION:
          $direccionId = $valor;
          break;
        case DocumentoTipoNegocio::DATO_PERCEPCION:
          // $importeNoAfecto = $valor;
          $percepcion = $valor;
          break;
        case DocumentoTipoNegocio::DATO_CUENTA:
          $cuentaId = $valor;
          break;
        case DocumentoTipoNegocio::DATO_ACTIVIDAD:
          $actividadId = $valor;
          break;
        case DocumentoTipoNegocio::DATO_RETENCION_DETRACCION:
          $retencionDetraccionId = $valor;
          break;
        case DocumentoTipoNegocio::DATO_OTRA_PERSONA:
          array_push($listas, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_PRODUCTO_DUPLICADO:
          $banderaProductoDuplicado = $valor;
          array_push($cadenas, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_VENDEDOR:
          array_push($listas, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_ARCHIVO_ADJUNTO:
          $archivoAdjunto = $valor;
          break;
        case DocumentoTipoNegocio::DATO_ARCHIVO_ADJUNTO_MULTI:
          $archivoAdjuntoMulti = $valor;
          break;
        case DocumentoTipoNegocio::DATO_DETRACCION_TIPO:
          $detraccionId = $valor;
          break;
        case DocumentoTipoNegocio::DATO_NUM_LICENCIA_CONDUCIR:
          array_push($cadenas, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_TIPO_REQUERIMIENTO: //Tipo requerimiento
          if($dataDocumento[0]['documento_tipo_id'] ==  Configuraciones::SOLICITUD_REQUERIMIENTO || $dataDocumento[0]['documento_tipo_id'] == Configuraciones::ORDEN_SERVICIO || $dataDocumento[0]['documento_tipo_id']  == Configuraciones::REQUERIMIENTO_AREA || $dataDocumento[0]['documento_tipo_id']  == Configuraciones::GENERAR_COTIZACION){
            $es_rq = $valor;
            array_push($listas, $valorDtd);
          }
          break;
        case DocumentoTipoNegocio::DATO_AREA: //Area
            array_push($cadenas, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_GRUPO_PRODUCTO: //Area
            array_push($cadenas, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_ENTREGA_EN_DESTINO: //
            array_push($cadenas, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_UO:
            array_push($cadenas, $valorDtd);
          break;
        case DocumentoTipoNegocio::DATO_CUENTA_PROVEEDOR: //
            array_push($cadenas, $valorDtd);
          break;   
        case DocumentoTipoNegocio::CONDICION_PAGO: //
            array_push($listas, $valorDtd);
          break;                   
        default:
      }
    }

    $grupoUnico = array();
    $documentoTipoId = $dataDocumento[0]['documento_tipo_id'];
    // 1. Obtenemos la configuracion actual del tipo de documento
    $configuraciones = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDatoSimple($documentoTipoId);
    if (!ObjectUtil::isEmpty($configuraciones)) {
      if (ObjectUtil::isEmpty($camposDinamicos)) {
        throw new WarningException("No se especificaron los campos minimos necesarios para guardar el documento");
      }
      foreach ($configuraciones as $itemDtd) {
        foreach ($camposDinamicos as $valorDtd) {
          if ((int) $itemDtd["id"] === (int) $valorDtd["id"]) {
            $valor = ObjectUtil::isEmpty($valorDtd["valor"]) ? null : $valorDtd["valor"];
            if ((int) $itemDtd["opcional"] === 0 && ObjectUtil::isEmpty($valor)) {
              throw new WarningException("No se especificó un valor válido para " . $itemDtd["descripcion"]);
            }

            if (!ObjectUtil::isEmpty($itemDtd["grupo_unico"])) {
              $descripcionUnicoGrupo = '';
              if ($itemDtd["unico"] == 1) {
                $descripcionUnicoGrupo = $itemDtd["descripcion"];
              }

              if (ObjectUtil::isEmpty($grupoUnico)) {
                $grupo = array('grupo_unico' => $itemDtd["grupo_unico"], 'valor' => (is_numeric($valor) ? (int) $valor : $valor), 'descripcion' => $descripcionUnicoGrupo);
                array_push($grupoUnico, $grupo);
              } else {
                $banderaGrupo = false;

                foreach ($grupoUnico as $indGrupo => $itemGrupo) {
                  if ($itemGrupo['grupo_unico'] == $itemDtd["grupo_unico"]) {
                    $banderaGrupo = true;
                    $grupoUnico[$indGrupo]['valor'] = $itemGrupo['valor'] . "-" . (is_numeric($valor) ? (int) $valor : $valor);

                    if (!ObjectUtil::isEmpty($descripcionUnicoGrupo)) {
                      $grupoUnico[$indGrupo]['descripcion'] = $descripcionUnicoGrupo;
                    }
                  }
                }
                if (!$banderaGrupo) {
                  $grupo = array('grupo_unico' => $itemDtd["grupo_unico"], 'valor' => (is_numeric($valor) ? (int) $valor : $valor), 'descripcion' => $descripcionUnicoGrupo);
                  array_push($grupoUnico, $grupo);
                }
              }
            }
          }
        }
      }
    }

    // Validación de correlatividad numérica con la fecha de emisión
    // INACTIVO DOCUMENTO TEMPORALMENTE
    $resInac = DocumentoNegocio::create()->actualizarEstadoXDocumentoIdXEstado($documentoId, 0);

    // $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);
    // if ($dataDocumentoTipo[0]["tipo"] == 1 || $dataDocumentoTipo[0]["identificador_negocio"] == 6) {
    // $dataRes = Documento::create()->validarCorrelatividadNumericaConFechaEmision($documentoTipoId, $serie, $numero, $fechaEmision);

    // if ($dataRes[0]['validacion'] == 0) {
    //   throw new WarningException("Validación de correlatividad numérica: cambie la fecha de emisión o el número del documento.");
    // }
    // }

    if (!ObjectUtil::isEmpty($grupoUnico)) {
      foreach ($grupoUnico as $indGrupo => $itemGrupo) {
        $resSN = strpos(strtoupper($itemGrupo['valor']), 'S/N');
        if ($resSN === false) {
          $documentoRepetido = Documento::create()->obtenerXDocumentoTipoXGrupoUnico($documentoTipoId, $itemGrupo['grupo_unico'], $itemGrupo['valor']);
          if (!ObjectUtil::isEmpty($documentoRepetido)) {
            throw new WarningException($itemGrupo['descripcion'] . " está duplicado");
          }
        }
      }
    }
    // ACTIVO DOCUMENTO
    $resAct = DocumentoNegocio::create()->actualizarEstadoXDocumentoIdXEstado($documentoId, 1);

    if (!ObjectUtil::isEmpty($periodoId) && $dataDocumento[0]['periodo_id'] != $periodoId) {
      //VALIDAR QUE EL PERIODO ESTE ABIERTO
      $dataPeriodo = PeriodoNegocio::create()->obtenerPeriodoXid($periodoId);
      $dataPeriodo = $dataPeriodo->dataPeriodo;
      if ($dataPeriodo[0]['indicador'] != 2) {
        throw new WarningException('El periodo ' . $dataPeriodo[0]['anio'] . '-' . ($dataPeriodo[0]['mes'] * 1 < 10 ? '0' . $dataPeriodo[0]['mes'] : $dataPeriodo[0]['mes']) . ' no está abierto');
      }
    }

    $adjuntoId = null;

    $documento = Documento::create()->editarDocumento($documentoId, $personaId, $direccionId, $organizadorId, $adjuntoId, $codigo, $serie, $numero, $fechaEmision, $fechaVencimiento, $fechaTentativa, $descripcion, $comentario, $importeTotal, $importeIgv, $importeSubTotal, $monedaId, $cuentaId, $actividadId, $retencionDetraccionId, $percepcion, $periodoId, $tipoPago, $banderaProductoDuplicado, $detraccionId, $datosExtras['afecto_detraccion_retencion'], $datosExtras['porcentaje_afecto'], $datosExtras['monto_detraccion_retencion'], $contOperacionTipoId, $importeOtros, $importeExoneracion, $importeIcbp, $datosExtras['afecto_impuesto'], $igv_porcentaje);

    // Ahora guardamos los campos dinámicos
    // Campos numéricos
    foreach ($numeros as $item) {
      $res = DocumentoDatoValorNegocio::create()->editarNumero($documentoId, $item["id"], $item["valor"]);
    }

    // Campos cadenas
    foreach ($cadenas as $item) {
      $res = DocumentoDatoValorNegocio::create()->editarCadena($documentoId, $item["id"], $item["valor"]);
    }

    // Campos fechas
    foreach ($fechas as $item) {
      $res = DocumentoDatoValorNegocio::create()->editarFecha($documentoId, $item["id"], DateUtil::formatearCadenaACadenaBD($item["valor"]));
    }

    // Campos listas
    foreach ($listas as $item) {
      $res = DocumentoDatoValorNegocio::create()->editarLista($documentoId, $item["id"], $item["valor"]);
    }

    // DOCUMENTO ADJUNTO
    if (!ObjectUtil::isEmpty($archivoAdjunto['data'])) {
      $decode = Util::base64ToImage($archivoAdjunto['data']);
      $nombreArchivo = $archivoAdjunto['nombre'];
      $contenidoArchivo = $archivoAdjunto['contenido_archivo'];
      $pos = strripos($nombreArchivo, '.');
      $ext = substr($nombreArchivo, $pos);

      $hoy = date("YmdHis");
      $nombreGenerado = $documentoId . $hoy . $ext;
      $url = __DIR__ . '/../../util/uploads/documentoAdjunto/' . $nombreGenerado;

      file_put_contents($url, $decode);
      $eliminarArchivo = Documento::create()->eliminarDocumentosAdjuntosXDocumentoId($documentoId);

      $resAdjunto = Documento::create()->insertarDocumentoAdjunto($documentoId, $nombreArchivo, $nombreGenerado, $usuarioCreacionId, $contenidoArchivo);
    }

    // DOCUMENTO ADJUNTO MULTIPLE
    if (!ObjectUtil::isEmpty($archivoAdjuntoMulti)) {
      foreach($archivoAdjuntoMulti  as $item){
        if($dataDocumento[0]['documento_tipo_id'] ==  Configuraciones::GENERAR_COTIZACION && strpos($item['id'], 't') !== false){
          $respuestaAdjunto = Documento::create()->cambiarEstadoArchivoAdjunto($documentoId, $item['contenido_archivo']);
        }
      }
      $resAdjunto = $this->guardarArchivosXDocumentoID($documentoId, $archivoAdjuntoMulti, null, $usuarioCreacionId);
    }

    return $documento;
  }

  function buscarDocumentosOperacionXOpcionXSerieNumero($opcionId, $busqueda)
  {
    return Documento::create()->buscarDocumentosOperacionXOpcionXSerieNumero($opcionId, $busqueda);
  }

  function obtenerPersonaXDocumentoId($documentoId)
  {
    return Documento::create()->obtenerPersonaXDocumentoId($documentoId);
  }

  function buscarDocumentosXTipoDocumentoXSerieNumero($documentoTipoIdArray, $busqueda)
  {
    return Documento::create()->buscarDocumentosXTipoDocumentoXSerieNumero(Util::fromArraytoString($documentoTipoIdArray), $busqueda);
  }

  function buscarDocumentosXDocumentoPagar($empresaId, $tipo, $tipoProvisionPago, $busqueda)
  {
    return Documento::create()->buscarDocumentosXDocumentoPagar($empresaId, $tipo, $tipoProvisionPago, $busqueda);
  }

  function buscarDocumentosXDocumentoPago($empresaId, $tipo, $tipoProvisionPago, $busqueda)
  {
    return Documento::create()->buscarDocumentosXDocumentoPago($empresaId, $tipo, $tipoProvisionPago, $busqueda);
  }

  function buscarDocumentosXDocumentoPagado($empresaId, $tipo, $tipoProvisionPago, $busqueda)
  {
    return Documento::create()->buscarDocumentosXDocumentoPagado($empresaId, $tipo, $tipoProvisionPago, $busqueda);
  }

  function obtenerRelacionesDocumento($documentoId)
  {
    return Documento::create()->obtenerRelacionesDocumento($documentoId);
  }

  function obtenerDocumentoRelacionadoImpresion($documentoId)
  {
    return Documento::create()->obtenerDocumentoRelacionadoImpresion($documentoId);
  }

  // TODO: Inicio Obtener para Editar
  function obtenerDocumentoXDocumentoId($documentoId)
  {
    return Documento::create()->obtenerDocumentoXDocumentoId($documentoId);
  }
  // TODO: Fin Obtener para Editar

  public function obtenerFechasPosterioresDocumentosSalidas($fechaEmision, $bienId, $organizadorId)
  {
    return Documento::create()->obtenerFechasPosterioresDocumentosSalidas($fechaEmision, $bienId, $organizadorId);
  }

  //operaciones: modal de busqueda
  function buscarDocumentosOperacionXTipoDocumentoXSerieNumero($documentoTipoIdArray, $busqueda)
  {
    return Documento::create()->buscarDocumentosOperacionXTipoDocumentoXSerieNumero(Util::fromArraytoString($documentoTipoIdArray), $busqueda);
  }

  function generaCeros($numero)
  {
    //obtengop el largo del numero
    $largo_numero = strlen($numero);
    //especifico el largo maximo de la cadena
    $largo_maximo = 5;
    //tomo la cantidad de ceros a agregar
    $agregar = $largo_maximo - $largo_numero;
    //agrego los ceros
    for ($i = 0; $i < $agregar; $i++) {
      $numero = "0" . $numero;
    }
    //retorno el valor con ceros
    return $numero;
  }

  function obtenerIdTipoDocumentoXIdDocumento($idDocumento)
  {
    return Documento::create()->obtenerIdTipoDocumentoXIdDocumento($idDocumento);
  }

  function actualizarEstadoQRXDocumentoId($documentoId, $estadoQR)
  {
    return Documento::create()->actualizarEstadoQRXDocumentoId($documentoId, $estadoQR);
  }

  function obtenerDocumentoIdXMovimientoBienId($movimientoBienId)
  {
    return Documento::create()->obtenerDocumentoIdXMovimientoBienId($movimientoBienId);
  }

  function obtenerDocumentoRelacion($documentoOrigenId, $documentoDestinoId, $documentoId)
  {
    $respuesta = new ObjectUtil();
    $documentoACopiar = $this->obtenerDataDocumentoACopiar($documentoDestinoId, $documentoOrigenId, $documentoId);

    if (ObjectUtil::isEmpty($documentoACopiar)) {
      throw new WarningException("No se encontró el documento");
    }

    $respuesta->documentoACopiar = $documentoACopiar;
    $respuesta->dataDocumentoRelacionada = DocumentoNegocio::create()->obtenerDataDocumentoACopiarRelacionada($documentoOrigenId, $documentoDestinoId, $documentoId);

    if ($documentoDestinoId != $documentoOrigenId) {
      $respuesta->documentoCopiaRelaciones = DocumentoNegocio::create()->obtenerRelacionesDocumento($documentoId);
    } else {
      $respuesta->documentoCopiaRelaciones = 1;
    }

    return $respuesta;
  }

  function obtenerDocumentoAdjuntoXDocumentoId($documentoId)
  {
    return Documento::create()->obtenerDocumentoAdjuntoXDocumentoId($documentoId);
  }

  function obtenerAnticiposPendientesXPersonaId($personaId, $monedaId)
  {
    return Documento::create()->obtenerAnticiposPendientesXPersonaId($personaId, $monedaId);
  }

  public function obtenerPlanillaImportacionXDocumentoId($documentoId)
  {
    return Documento::create()->obtenerPlanillaImportacionXDocumentoId($documentoId);
  }

  public function obtenerDuaXTicketEXT($documentoId)
  {
    return Documento::create()->obtenerDuaXTicketEXT($documentoId);
  }

  public function actualizarTipoCambioMontoNoAfectoXDocumentoId($documentoId, $tc, $montoNoAfecto)
  {
    return Documento::create()->actualizarTipoCambioMontoNoAfectoXDocumentoId($documentoId, $tc, $montoNoAfecto);
  }

  public function obtenerDocumentoRelacionadoXDocumentoIdXDocumentoRelacionadoId($documentoId, $documentoRelacionId)
  {
    return Documento::create()->obtenerDocumentoRelacionadoXDocumentoIdXDocumentoRelacionadoId($documentoId, $documentoRelacionId);
  }

  public function obtenerDataAlmacenVirtualXDocumentoId($documentoTipoId)
  {
    return Documento::create()->obtenerDataAlmacenVirtualXDocumentoId($documentoTipoId);
  }

  public function obtenerDocumentoDuaXDocumentoId($documentoId)
  {
    return Documento::create()->obtenerDocumentoDuaXDocumentoId($documentoId);
  }

  public function obtenerDocumentoRelacionadoActivoXDocumentoId($documentoId)
  {
    return Documento::create()->obtenerDocumentoRelacionadoActivoXDocumentoId($documentoId);
  }

  public function obtenerDocumentoFEXId($documentoId)
  {
    return Documento::create()->obtenerDocumentoFEXId($documentoId);
  }

  public function obtenerSerieNumeroXDocumentoId($documentoId)
  {
    return Documento::create()->obtenerSerieNumeroXDocumentoId($documentoId);
  }

  public function actualizarNroSecuencialBajaXDocumentoId($documentoId, $nroSecuencialBaja, $ticket)
  {
    return Documento::create()->actualizarNroSecuencialBajaXDocumentoId($documentoId, $nroSecuencialBaja, $ticket);
  }

  public function obtenerNumeroNotaCredito($documentoTipoId, $documentoRelacionadoTipo)
  {
    return Documento::create()->obtenerNumeroNotaCredito($documentoTipoId, $documentoRelacionadoTipo);
  }

  public function obtenerIdDocumentosResumenDiario()
  {
    return Documento::create()->obtenerIdDocumentosResumenDiario();
  }

  public function actualizarEstadoEfactAnulacionXDocumentoId($documentoId, $estado, $ticket)
  {
    return Documento::create()->actualizarEstadoEfactAnulacionXDocumentoId($documentoId, $estado, $ticket);
  }

  //EDICION
  function obtenerDataDocumentoACopiarEdicion($documentoTipoDestinoId, $documentoTipoOrigenId, $documentoId)
  {
    return Documento::create()->obtenerDataDocumentoACopiarEdicion($documentoTipoOrigenId, $documentoTipoDestinoId, $documentoId);
  }

  function actualizarEstadoXDocumentoIdXEstado($documentoId, $estado)
  {
    return Documento::create()->actualizarEstadoXDocumentoIdXEstado($documentoId, $estado);
  }

  //DOCUMENTOS EAR
  function validarImportePago($documentoIdSumaImporte, $documentoId)
  {
    return Documento::create()->validarImportePago($documentoIdSumaImporte, $documentoId);
  }

  //SCRIPTS MEJORAS DE EFACT
  public function actualizarEstadoXId($documentoId, $estado)
  {
    return Documento::create()->actualizarEstadoXId($documentoId, $estado);
  }

  public function actualizarEfactPdfNombre($documentoId, $nombrePDF)
  {
    return Documento::create()->actualizarEfactPdfNombre($documentoId, $nombrePDF);
  }

  public function actualizarEfactEstadoRegistro($documentoId, $estadoRegistro, $resultado)
  {
    return Documento::create()->actualizarEfactEstadoRegistro($documentoId, $estadoRegistro, $resultado);
  }

  //REPORTE ORDEN DE TRABAJO
  function obtenerFechaPrimeraOrdenTrabajo()
  {
    return Documento::create()->obtenerFechaPrimeraOrdenTrabajo();
  }

  function verCabeceraPorOrdenTrabajo($documentoId)
  {
    return Documento::create()->obtenerCabeceraOrdenTrabajo($documentoId);
  }

  function calcularTotales($data)
  {
    $subtotal = 0;
    $igv = 0;
    $total = 0;
    $tamanio = count($data);

    for ($i = 0; $i < $tamanio; $i++) {
      $subtotal = $subtotal + $data[$i]['subtotal'];
      $igv = $igv + $data[$i]['igv'];
      $total = $total + $data[$i]['total'];
    }

    $totales = new stdClass();
    $totales->subtotal = $subtotal;
    $totales->igv = $igv;
    $totales->total = $total;

    return $totales;
  }

  function verDetalleFacturacionPorOrdenTrabajo($documentoId)
  {
    $data = new stdClass();
    $data->datosDetalle = Documento::create()->obtenerDetalleFacturacionOrdenTrabajo($documentoId);
    $totales = DocumentoNegocio::create()->calcularTotales($data->datosDetalle);
    $data->subtotalDetalle = $totales->subtotal;
    $data->igvDetalle = $totales->igv;
    $data->totalDetalle = $totales->total;

    return $data;
  }

  function verDetalleSolicitadoPorOrdenTrabajo($documentoId)
  {
    $data = new stdClass();
    $data->datosDetalle = Documento::create()->obtenerDetalleSolicitadoOrdenTrabajo($documentoId);
    $totales = DocumentoNegocio::create()->calcularTotales($data->datosDetalle);
    $data->subtotalDetalle = $totales->subtotal;
    $data->igvDetalle = $totales->igv;
    $data->totalDetalle = $totales->total;
    return $data;
  }

  function verDetalleEARPorOrdenTrabajo($documentoId)
  {
    $data = new stdClass();
    $data->datosDetalle = Documento::create()->obtenerDetalleEAROrdenTrabajo($documentoId);
    $totales = DocumentoNegocio::create()->calcularTotales($data->datosDetalle);
    $data->subtotalDetalle = $totales->subtotal;
    $data->igvDetalle = $totales->igv;
    $data->totalDetalle = $totales->total;
    return $data;
  }

  public function obtenerListaComprobacion($documentoId)
  {

    return Documento::create()->obtenerListaComprobacion($documentoId);
  }

  public function obtenerDocumentoHistorial($documentoId)
  {

    return Documento::create()->obtenerDocumentoHistorial($documentoId);
  }

  public function obtenerDocumentoHistorialXId($id)
  {

    return Documento::create()->obtenerDocumentoHistorialXId($id);
  }

  public function notificacionesPendientesFacturacion()
  {
    // 1.onbtener data necesaria para el envío
    // 2. obtener plantilla
    //         $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID($plantillaId);
    //        $cuerpo = $plantilla[0]["cuerpo"];
    //        $cuerpo = str_replace("[|detalle_programacion|]", EmailPlantillaNegocio::create()->construirTablaCobranzas($cobranzas), $cuerpo);
    //        $cuerpo = str_replace("[|varios|]", $cantidad === 1 ? '' : 'S', $cuerpo);
    //        $asunto = str_replace("[|varios|]", $cantidad === 1 ? '' : 'S', $plantilla[0]["asunto"]);
    // 3. hacer insert en la tabla de emailEnvio
    //        $res = EmailEnvioNegocio::create()->insertarEmailEnvio($destinatario, $asunto, $cuerpo, $estado, $usuarioId); // ($plantilla[0]["destinatario"], count($cobranzas) . " " . $asunto, $cuerpo, 1, 1);
    // 4. Enviar correo

    date_default_timezone_set("America/Lima"); //Zona horaria de Peru

    $notificaciones_anteriores = array();
    $fechaActual = date("Y-m-d");
    $fecha_inicio = date("2021-10-28");
    $dias = (strtotime($fechaActual) - strtotime($fecha_inicio)) / 86400;
    $dias = abs($dias);
    // $dias = floor($dias);

    $correosEnviar = Documento::create()->obtenerCorreosSegunPerfilAsistenteyGerente();

    $empresaId = 2;

    for ($i = $dias; $i >= 1; $i--) {
      /** @var Countable|array */
      $cobranzas_anteriores = Pago::create()->obtenerCobranzasParaEmail($empresaId, $i * -1);
      if (!ObjectUtil::isEmpty($cobranzas_anteriores)) {
        for ($j = 0; $j < count($cobranzas_anteriores); $j++) {
          array_push($notificaciones_anteriores, $cobranzas_anteriores[$j]);
        }
      }
    }
    /** @var Countable|array */
    $cobranzas_hoy = Pago::create()->obtenerCobranzasParaEmail(2, 0);
    //  $cobranzas_mañana = Pago::create()->obtenerCobranzasParaEmail(2, 1);
    $notificaciones_sgte_semana = array();


    for ($i = 1; $i <= 7; $i++) {
      /** @var Countable|array */
      $cobranzas_sgte_semana = Pago::create()->obtenerCobranzasParaEmail(2, $i);
      if (!ObjectUtil::isEmpty($cobranzas_sgte_semana)) {
        for ($j = 0; $j < count($cobranzas_sgte_semana); $j++) {
          array_push($notificaciones_sgte_semana, $cobranzas_sgte_semana[$j]);
        }
      }
    }
    $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID(Configuraciones::REPORTE_PENDIENTES_FACTURACION_ID);
    $cuerpo = $plantilla[0]["cuerpo"];

    if (!ObjectUtil::isEmpty($notificaciones_anteriores)) {
      $cantidad_retraso = count($notificaciones_anteriores);

      $cuerpo = str_replace("[|detalle_programacion_retraso|]", EmailPlantillaNegocio::create()->construirTablaFacturaciones($notificaciones_anteriores), $cuerpo);
    }
    if (!ObjectUtil::isEmpty($cobranzas_hoy)) {
      $cantidad_hoy = count($cobranzas_hoy);

      $cuerpo = str_replace("[|detalle_programacion_hoy|]", EmailPlantillaNegocio::create()->construirTablaFacturaciones($cobranzas_hoy), $cuerpo);
    } else {
      $cuerpo = str_replace("[|detalle_programacion_hoy|]", "No hay data que mostrar", $cuerpo);
    }

    if (!ObjectUtil::isEmpty($notificaciones_sgte_semana)) {
      $cantidad_sgte_semana = count($notificaciones_sgte_semana);

      $cuerpo = str_replace("[|detalle_programacion_vencer|]", EmailPlantillaNegocio::create()->construirTablaFacturaciones($notificaciones_sgte_semana), $cuerpo);
    }

    $cuerpo = str_replace("[|fecha_actual|]", date("d/m/Y"), $cuerpo);

    $cantidad = $cantidad_retraso + $cantidad_hoy + $cantidad_sgte_semana;
    $cuerpo = str_replace("[|varios|]", $cantidad === 1 ? '' : 'S', $cuerpo);
    $asunto = str_replace("[|varios|]", $cantidad === 1 ? '' : 'S', $plantilla[0]["asunto"]);

    $correosEnviar[0]["correos"] = "ccabanillas";
    $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correosEnviar[0]["correos"], $asunto, $cuerpo, 1, 1);
    EmailEnvioNegocio::create()->enviarPendientesEnvio();
  }

  public function enviarResumenOT()
  {
    date_default_timezone_set("America/Lima"); //Zona horaria de Peru

    $notificaciones_anteriores = array();
    $responsables = array();
    $responsableN = array();
    $fechaActual = date("Y-m-d");
    $fecha_inicio = date("2021-11-19");
    $dias = (strtotime($fechaActual) - strtotime($fecha_inicio)) / 86400;
    $dias = abs($dias);

    for ($i = $dias; $i >= 1; $i--) {
      /** @var Countable|array */
      $not_anteriores = Documento::create()->obtenerNotificacionesOTParaEmail(2, $i * -1);
      if (!ObjectUtil::isEmpty($not_anteriores)) {
        for ($j = 0; $j < count($not_anteriores); $j++) {
          array_push($notificaciones_anteriores, $not_anteriores[$j]);
          array_push($responsables, $not_anteriores[$j]['responsable']);
        }
      }
    }

    $notificaciones_hoy = array();
    /** @var Countable|array */
    $not_hoy = Documento::create()->obtenerNotificacionesOTParaEmail(2, 0);
    if (!ObjectUtil::isEmpty($not_hoy)) {
      for ($j = 0; $j < count($not_hoy); $j++) {
        array_push($notificaciones_hoy, $not_hoy[$j]);
        array_push($responsables, $not_hoy[$j]['responsable']);
      }
    }

    $notificaciones_sgte_semana = array();

    for ($i = 1; $i <= 7; $i++) {
      /** @var Countable|array */
      $not_sgte_semana = Documento::create()->obtenerNotificacionesOTParaEmail(2, $i);
      if (!ObjectUtil::isEmpty($not_sgte_semana)) {
        for ($j = 0; $j < count($not_sgte_semana); $j++) {
          array_push($notificaciones_sgte_semana, $not_sgte_semana[$j]);
          array_push($responsables, $not_sgte_semana[$j]['responsable']);
        }
      }
    }
    $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID(Configuraciones::REPORTE_ORDEN_TRABAJO_ID);

    $correosEnviar = Documento::create()->obtenerCorreosSegunPerfilAsistente();


    $responsables = array_unique($responsables);

    foreach ($responsables as $index => $responsable) {
      array_push($responsableN, $responsable);
    }

    for ($i = 0; $i < count($responsableN); $i++) {
      $cuerpo = $plantilla[0]["cuerpo"];
      if (!ObjectUtil::isEmpty($notificaciones_anteriores)) {
        $cantidad_retraso = count($notificaciones_anteriores);

        $cuerpo = str_replace("[|detalle_programacion_retraso|]", EmailPlantillaNegocio::create()->construirTablaOrdenTrabajo($notificaciones_anteriores, $responsableN[$i]), $cuerpo);
      } else {
        $cuerpo = str_replace("[|detalle_programacion_retraso|]", "No hay data que mostrar", $cuerpo);
      }
      if (!ObjectUtil::isEmpty($not_hoy)) {
        $cantidad_hoy = count($not_hoy);

        $cuerpo = str_replace("[|detalle_programacion_hoy|]", EmailPlantillaNegocio::create()->construirTablaOrdenTrabajo($not_hoy, $responsableN[$i]), $cuerpo);
      } else {
        $cuerpo = str_replace("[|detalle_programacion_hoy|]", "No hay data que mostrar", $cuerpo);
      }

      if (!ObjectUtil::isEmpty($notificaciones_sgte_semana)) {
        $cantidad_sgte_semana = count($notificaciones_sgte_semana);

        $cuerpo = str_replace("[|detalle_programacion_vencer|]", EmailPlantillaNegocio::create()->construirTablaOrdenTrabajo($notificaciones_sgte_semana, $responsableN[$i]), $cuerpo);
      } else {
        $cuerpo = str_replace("[|detalle_programacion_vencer|]", "No hay data que mostrar", $cuerpo);
      }


      $cuerpo = str_replace("[|fecha_actual|]", date("d/m/Y"), $cuerpo);

      $cantidad = $cantidad_retraso + $cantidad_hoy + $cantidad_sgte_semana;
      $cuerpo = str_replace("[|varios|]", $cantidad === 1 ? '' : 'S', $cuerpo);
      $asunto = str_replace("[|varios|]", $cantidad === 1 ? '' : 'S', $plantilla[0]["asunto"]);

      $correoResponsable = Documento::create()->obtenerCorreoResponsableEnvioEmail($responsableN[$i]);


      $correoResponsable[0]["correos"] = "ccabanillas";
      $correosEnviar[0]["correos"] = "ccabanillas";
      $correoResponsable = "ccabanillas";

      if (!ObjectUtil::isEmpty($correoResponsable)) {
        if (!ObjectUtil::isEmpty($correoResponsable[0]['email'])) {
          $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correoResponsable[0]['email'], $asunto, $cuerpo, 1, 1, null, null, $correosEnviar[0]["correos"]);
        } else {
          $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correosEnviar[0]["correos"], $asunto, $cuerpo, 1, 1, null, null, null);
        }
      } else {
        $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correosEnviar[0]["correos"], $asunto, $cuerpo, 1, 1, null, null, null);
      }

      EmailEnvioNegocio::create()->enviarPendientesEnvio();
    }
  }

  public function obtenerDocumentoLiquidacionDetalle($documentId)
  {
    return Documento::create()->obtenerDocumentoLiquidacionDetalle($documentId);
  }

  public function obtenerDocumentoCotizacionDetalle($documentId)
  {
    return Documento::create()->obtenerDocumentoCotizacionDetalle($documentId);
  }

  public function eliminarDocumentoRelacionado($documentoIdOrigen, $documentoIdARelacionar, $usuarioId)
  {
    return Documento::create()->eliminarDocumentoRelacionado($documentoIdOrigen, $documentoIdARelacionar, $usuarioId);
  }

  public function obtenerDocumentoEstadoLista()
  {
    return Documento::create()->obtenerDocumentoEstadoLista();
  }

  public function obtenerDocumentoXRucXSerieNumero($empresaId, $documentoTipoId, $codigoIdentifacion, $serieNumero)
  {
    return Documento::create()->obtenerDocumentoXRucXSerieNumero($empresaId, $documentoTipoId, $codigoIdentifacion, $serieNumero);
  }

  public function verDetallePorOrdenTrabajo($documentoId)
  {
    $data = new stdClass();

    //cabecera
    $data->cabecera = DocumentoNegocio::create()->verCabeceraPorOrdenTrabajo($documentoId);
    //detalle
    $data->detalleFacturacion = DocumentoNegocio::create()->verDetalleFacturacionPorOrdenTrabajo($documentoId);
    $data->detalleSolicitado = DocumentoNegocio::create()->verDetalleSolicitadoPorOrdenTrabajo($documentoId);

    /** @var array */
    $detalleDocumentos = Documento::create()->obtenerDetalleEAROrdenTrabajo($documentoId);
    //detalle EAR
    $detalleEAR = array_merge(array_filter($detalleDocumentos, function ($item) {
      return $item['num_ear'] != '-';
    }));
    $data->detalleEAR->datosDetalle = $detalleEAR;
    $totales = DocumentoNegocio::create()->calcularTotales($detalleEAR);
    $data->detalleEAR->subtotalDetalle = $totales->subtotal;
    $data->detalleEAR->igvDetalle = $totales->igv;
    $data->detalleEAR->totalDetalle = $totales->total;

    //detalle OTROS
    $detalleOtrosDocumentos = array_merge(array_filter($detalleDocumentos, function ($item) {
      return $item['num_ear'] == '-';
    }));
    $data->detalleOtros->datosDetalle = $detalleOtrosDocumentos;
    $totales = DocumentoNegocio::create()->calcularTotales($detalleOtrosDocumentos);
    $data->detalleOtros->subtotalDetalle = $totales->subtotal;
    $data->detalleOtros->igvDetalle = $totales->igv;
    $data->detalleOtros->totalDetalle = $totales->total;


    //detalle RRHH
    $detalleRH = array_merge(array_filter($detalleDocumentos, function ($item) {
      return $item['documento_tipo_id'] == '234';
    }));
    $data->detalleRH->datosDetalle = $detalleRH;
    $totales = DocumentoNegocio::create()->calcularTotales($detalleRH);
    $data->detalleRH->subtotalDetalle = $totales->subtotal;
    $data->detalleRH->igvDetalle = $totales->igv;
    $data->detalleRH->totalDetalle = $totales->total;
    return $data;
  }

  public function obtenerInvoiceCommercialXDUA($documentId)
  {
  }

  function obtenerDocumentoPagoImportacionXInvoiceComercialXTipoDocumentoSUNAT($documentoId, $documentoTipoSunat) {
    return Documento::create()->obtenerDocumentoPagoImportacionXInvoiceComercialXTipoDocumentoSUNAT($documentoId, $documentoTipoSunat);
  }

  public function obtenerDocumentoDocumentoEstadoXdocumentoId($documentoId, $estado){
    return Documento::create()->obtenerDocumentoDocumentoEstadoXdocumentoId($documentoId, $estado);

  }
  
  // public function obtenerDocumentosXAreaId($areaId, $tipoRequerimiento, $urgencia)
  // {
  //   return Documento::create()->obtenerDocumentosXAreaId($areaId, $tipoRequerimiento, $urgencia);
  // }

  function obtenerDocumentosRelacionadosXIngresoSalidaReserva($documentoId)
  {
    return Documento::create()->obtenerDocumentosRelacionadosXIngresoSalidaReserva($documentoId);
  }  
}
