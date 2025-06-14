<?php

require_once __DIR__ . '/../../modelo/almacen/Almacenes.php';
require_once __DIR__ . '/../../modelo/almacen/OrdenCompraServicio.php';
require_once __DIR__ . '/../../modelo/almacen/Organizador.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/UsuarioNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/MovimientoNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';

class AlmacenesNegocio extends ModeloNegocioBase
{
  /**
   *
   * @return AlmacenesNegocio
   */
  static function create()
  {
    return parent::create();
  }

  public function obtenerConfiguracionInicialListadoDocumentos($usuarioId)
  {
    $respuesta = new stdClass();

    $persona_id = UsuarioNegocio::create()->getUsuario($usuarioId)[0]['persona_id'];
    $res = Almacenes::create()->obtenerPersonaOrganizadorXOrganizadorTipo($persona_id, 10); //10 = Almacén General

    if (ObjectUtil::isEmpty($res)) {
      throw new WarningException("El usuario en sesión no tiene organizador relacionado.");
    }
    $respuesta->almacenes =  $res;

    return $respuesta;
  }

  private function formatearFechaBD($cadena)
  {
    if (!ObjectUtil::isEmpty($cadena)) {
      return DateUtil::formatearCadenaACadenaBD($cadena);
    }
    return "";
  }

  public function obtenerOrganizadorHijos($almacenId)
  {
    $resultadoOrganizadorIds = [];
    $pendientes[] = $almacenId; // Cola de IDs a procesar
    while (!empty($pendientes)) {
      $idActual = array_shift($pendientes); // Tomamos el primero de la cola

      // Obtener hijos del organizador actual
      $hijos = OrganizadorNegocio::create()->getDataOrganizadorHijos($idActual);
      if (!ObjectUtil::isEmpty($hijos)) {
        $clave = array_search($idActual, $resultadoOrganizadorIds);
        if ($clave !== false) {
          unset($resultadoOrganizadorIds[$clave]);
        }
      }
      foreach ($hijos as $hijo) {
        // if ($hijo['organizador_tipo_id'] != 14) {
        $resultadoOrganizadorIds[] = $hijo['id'];              // Agregamos el hijo al resultado
        $pendientes[] = $hijo['id'];       // Agregamos el hijo a la cola para procesar sus hijos
        // }
      }
    }
    return Util::convertirArrayXCadena(array_values($resultadoOrganizadorIds));
  }

  public function obtenerOrdenCompraXCriterios($criterios, $elementosFiltrados, $columns, $order, $start)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
    $estadoId = $criterios['estadoId'];
    $tipoId = $criterios['tipoId'];
    $almacenId = $criterios['almacen'] == "" ? null : $criterios['almacen'];
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return OrdenCompraServicio::create()->obtenerOrdenCompraServicioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId, 1, $serie, $numero, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
  }

  public function obtenerCantidadOrdenCompraXCriterios($criterios, $columns, $order)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
    $estadoId = $criterios['estadoId'];
    $tipoId = $criterios['tipoId'];
    $almacenId = $criterios['almacen'];
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return OrdenCompraServicio::create()->obtenerCantidadOrdenCompraServicioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId, 1, $serie, $numero, $columnaOrdenar, $formaOrdenar);
  }

  public function visualizarDetalle($id, $movimientoId)
  {
    $respuesta = new stdClass();

    $banderaUrgencia = 0;
    $banderaDespacho = false;
    $dataRelacionada = DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoId($id);
    foreach ($dataRelacionada as $itemRelacion) {
      if ($itemRelacion['documento_tipo_id'] == Configuraciones::SOLICITUD_REQUERIMIENTO) {
        $documentoDatoValor = DocumentoDatoValorNegocio::create()->obtenerXIdDocumento($itemRelacion["documento_relacionado_id"]);
        foreach ($documentoDatoValor as $index => $item) {
          switch ($item['tipo'] * 1) {
            case 4:
              if ($item['descripcion'] == "Urgencia" && $item['valor'] == "Si") {
                $banderaUrgencia = 1;
              }
              break;
          }
        }
      }
      if ($itemRelacion['documento_tipo_id'] == Configuraciones::COTIZACION_SERVICIO) {
        $banderaUrgencia = 1;
      }
      if ($itemRelacion['documento_tipo_id'] == Configuraciones::DESPACHO) {
        $banderaDespacho = true;
      }
    }

    $detalle =  MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);
    $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($id);
    foreach ($detalle as $index => $itemDetalle) {
      $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleObtenerUnidadMinera($itemDetalle['movimiento_bien_id'], $banderaUrgencia);

      $cantidadRecepcionada = Almacenes::create()->paquete_trakingObtenerRecepcionXmovimientoBienId($itemDetalle['movimiento_bien_id']);
      $resultado = [];
      $distribucionUnidadMInera = [];

      foreach ($resMovimientoBienDetalle as $dato) {
        $unidad_minera_id = $dato['unidad_minera_id'];
        $filtradoUnidad_minera = array_values(array_filter($cantidadRecepcionada, function ($item) use ($unidad_minera_id) {
          return $item['unidad_minera_id'] == $unidad_minera_id;
        }));


        $unidad = $dato['unidad_minera'];
        $cantidad = floatval($dato['cantidad_requerimiento']);

        if (!isset($resultado[$unidad])) {
          $resultado[$unidad] = 0;
        }
        $resultado[$unidad] += $cantidad;
        if (($resultado[$unidad] - $filtradoUnidad_minera[0]['cantidad']) > 0) {
          array_push($distribucionUnidadMInera, array("unidad_minera_id" => $dato['unidad_minera_id'], "cantidad" => ($resultado[$unidad] - $filtradoUnidad_minera[0]['cantidad']), "unidad_minera" => $unidad));
        }
      }
      // Crear la cadena formateada
      $textoFinal = "";
      foreach ($resultado as $unidad => $total) {
        $textoFinal .= "$unidad: <strong>" . rtrim(number_format($total, 0, '.', '')) . "</strong>" . "<br>";
      }

      $cantidad = $itemDetalle['cantidad'];
      if ($respuesta->dataDocumento[0]['doc_tipo_id'] ==  Configuraciones::DESPACHO || $banderaDespacho) {
        $res = Almacenes::create()->paquete_trakingObtenerXmovimientoBienId($itemDetalle['movimiento_bien_id']);
        $detalle[$index]['cantidad'] = $res[0]['cantidad'];
        $cantidad = $res[0]['cantidad'];
      }

      if ($respuesta->dataDocumento[0]['doc_tipo_id'] ==  Configuraciones::RECEPCION) {
        $respuesta->dataDocumentoAdjunto = DocumentoNegocio::create()->obtenerDocumentoAdjuntoXDocumentoId($id);
      }

      $detalle[$index]['distribucion_unidad_minera'] = $distribucionUnidadMInera;
      $detalle[$index]['distribucion_requerimiento'] = $textoFinal;
      $detalle[$index]['cantidad_distribucion_requerimiento'] = count($resultado);
      $detalle[$index]['cantidad_recepcion'] = ObjectUtil::isEmpty($detalle[$index]['cantidad_recepcion']) ? $detalle[$index]['cantidad_por_recepcionar'] : $detalle[$index]['cantidad_recepcion'];

      $cantidad_recepcionada = Almacenes::create()->cantidadRecepcion($itemDetalle['movimiento_bien_id'])[0]['cantidad_recepcionada'];
      if (!ObjectUtil::isEmpty($cantidad_recepcionada)) {
        $detalle[$index]['cantidad_por_recepcionar'] = $cantidad - $cantidad_recepcionada;
        $detalle[$index]['cantidad_recepcionada'] = $cantidad_recepcionada;
        $detalle[$index]['cantidad_recepcion'] = ObjectUtil::isEmpty($detalle[$index]['cantidad_recepcion']) ? $detalle[$index]['cantidad_por_recepcionar'] : $detalle[$index]['cantidad_recepcion'];
        $detalle[$index]['doc_tipo_id'] = $respuesta->dataDocumento[0]['doc_tipo_id'];
        $detalle[$index]['bandera_despacho'] = $banderaDespacho;
        $detalle[$index]['bandera_despacho_cantidad'] = 1;
      } else {
        $detalle[$index]['cantidad_por_recepcionar'] = $cantidad - floatval($detalle[$index]['cantidad_recepcion']);
        $detalle[$index]['cantidad_recepcionada'] = $detalle[$index]['cantidad_recepcion'];
        $detalle[$index]['bandera_despacho_cantidad'] = 0;
      }
    }

    $respuesta->detalle = $detalle;
    $respuesta->dataOrganizadorXUnidadMinera = Organizador::create()->getDataOrganizadorXUnidadMinera();
    return $respuesta;
  }

  public function guardarDetalleRecepcion($filasSeleccionadas)
  {
    $resEstado = Almacenes::create()->guardarDetalleRecepcionEstado($filasSeleccionadas[0]['movimiento_id']);
    foreach ($filasSeleccionadas as $item) {
      $res = Almacenes::create()->guardarDetalleRecepcion($item['movimiento_bien_id'], $item['cantidad_recepcion'], 2);
    }
    $mensaje = 'Operacion registrada Exitosamente';
    $tipo_mensaje = 1;

    $respuesta = new stdClass();
    $respuesta->tipo_mensaje = $tipo_mensaje;
    $respuesta->mensaje = $mensaje;
    return $respuesta;
  }

  public function generarRecepcion($documentoId, $almacenId, $filasSeleccionadas, $usuarioId, $empresaId, $datosGuia = null)
  {
    $resEstado = Almacenes::create()->guardarDetalleRecepcionEstado($filasSeleccionadas[0]['movimiento_id']);

    $documentoTipoId = 265;
    $opcionId = 318;

    $configuraciones = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDato($documentoTipoId, $usuarioId);

    $periodoId = PeriodoNegocio::create()->obtenerUltimoPeriodoActivoXEmpresa($empresaId)[0]['id'];

    if (ObjectUtil::isEmpty($periodoId)) {
      throw new WarningException("No existe periodo abierto.");
    }

    $persona_id = UsuarioNegocio::create()->getUsuario($usuarioId)[0]['persona_id'];
    $almacenId = Almacenes::create()->obtenerPersonaOrganizadorXOrganizadorPadreXOrganizadorTipo($persona_id, $almacenId, 12)[0]['id']; //12 = Recepción

    if (ObjectUtil::isEmpty($almacenId)) {
      throw new WarningException("No existe almacén recepción.");
    }

    $camposDinamicos = [];

    foreach ($configuraciones as $item) {
      $valor = null;

      switch ($item['tipo']) {
        case 2:
          if ($item['descripcion'] == "Serie numero guia") {
            $valor = $datosGuia[0]['serie_numeroGuia'];
          } else if ($item['descripcion'] == "Peso") {
            $valor = $datosGuia[0]['peso'];
          } else {
            $valor = $datosGuia[0]['volumen'];
          }
          break;
        case 5: // Recepcionado por
          $valor = UsuarioNegocio::create()->getUsuario($usuarioId)[0]['persona_id'];
          break;
        case 7: // Serie
          $valor = $item['cadena_defecto'];
          break;
        case 8: // Número
        case 9: // Fecha
          $valor = $item['data'];
          break;
        case 17: //
          $valor = $almacenId;
          break;
        default:
          continue 2; // Salta al siguiente $item si el tipo no es manejado
      }

      $camposDinamicos[] = [
        "id" => $item['id'],
        "tipo" => $item['tipo'],
        "opcional" => "0",
        "descripcion" => $item['descripcion'],
        "codigo" => "",
        "valor" => $valor
      ];
    }


    $detalleRecepcion = [];
    foreach ($filasSeleccionadas as $i => $itemFilasSeleccionadas) {
      if ($itemFilasSeleccionadas['cantidad_recepcion'] > 0) {
        $arrayItem = array(
          "bienId" => $itemFilasSeleccionadas['bien_id'],
          "bienDesc" => $itemFilasSeleccionadas['bien_descripcion'],
          "cantidadAceptada" => null,
          "cantidad" => $itemFilasSeleccionadas['doc_tipo_id'] == Configuraciones::DESPACHO ? 1 : $itemFilasSeleccionadas['cantidad_recepcion'],
          "unidadMedidaId" => $itemFilasSeleccionadas['unidad_medida_id'],
          "unidadMedidaDesc" => "",
          "esCompra" => null,
          "compraDesc" => "",
          "stockBien" => "",
          "bienTramoId" => "",
          "subTotal" => "",
          "index" => $i,
          "precioCompra" => "",
          "organizadorId" => $almacenId,
          "detalle" => array(array("columnaCodigo" => 38, "valorDet" => $itemFilasSeleccionadas['movimiento_bien_id'], "valorExtra" => null))
        );
        $detalleRecepcion[] = $arrayItem;
      }
    }
    $documentoARelacionar[] = array(
      "documentoId" => $documentoId,
      "movimientoId" => "",
      "tipo" => "1",
      "documentoPadreId" => ""
    );

    $documentoId = MovimientoNegocio::create()->guardar($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalleRecepcion, $documentoARelacionar, 1, "Movimiento de recepción", 1, 2, null, $periodoId, null, null, null, null);

    $resAdjunto = MovimientoNegocio::create()->guardarArchivosXDocumentoID($documentoId[0]['vout_id'], $datosGuia[0]['pdfGuia'], null, $usuarioId);

    $documento_movimientoId =  MovimientoBien::create()->obtenerMovimientoIdXDocumentoId($documentoId[0]['vout_id']);
    $movimiento_detalle =  MovimientoBien::create()->obtenerXIdMovimiento($documento_movimientoId[0]['movimiento_id']);

    $respuesta = new stdClass();
    $respuesta->documentoId = $documentoId;
    $respuesta->movimiento_detalle = $movimiento_detalle;

    return $respuesta;
  }

  public function obtenerRecepcionXCriterios($criterios, $elementosFiltrados, $columns, $order, $start, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
    $estadoId = $criterios['estadoId'];
    $tipoId = $criterios['tipoId'];
    $almacenId = $criterios['almacen'] == "" ? null : $criterios['almacen'];
    $persona_id = UsuarioNegocio::create()->getUsuario($usuarioId)[0]['persona_id'];
    $almacenId = Almacenes::create()->obtenerPersonaOrganizadorXOrganizadorPadreXOrganizadorTipo($persona_id, $almacenId, 12)[0]['id']; //12 = Recepcion
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Almacenes::create()->obtenerRecepcionXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId, $serie, $numero, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
  }

  public function obtenerCantidadRecepcionXCriterios($criterios, $columns, $order, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
    $estadoId = $criterios['estadoId'];
    $tipoId = $criterios['tipoId'];
    $almacenId = $criterios['almacen'] == "" ? null : $criterios['almacen'];
    $persona_id = UsuarioNegocio::create()->getUsuario($usuarioId)[0]['persona_id'];
    $almacenId = Almacenes::create()->obtenerPersonaOrganizadorXOrganizadorPadreXOrganizadorTipo($persona_id, $almacenId, 12)[0]['id']; //12 = Recepcion
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];


    return Almacenes::create()->obtenerCantidadRecepcionXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId, $serie, $numero, $columnaOrdenar, $formaOrdenar);
  }

  //Almacenar
  public function obtenerConfiguracionInicialListadoPaqueteRecepcion($usuarioId)
  {
    $respuesta = new stdClass();
    $persona_id = UsuarioNegocio::create()->getUsuario($usuarioId)[0]['persona_id'];
    $res = Almacenes::create()->obtenerPersonaOrganizadorXOrganizadorTipo($persona_id, 10);
    if (ObjectUtil::isEmpty($res)) {
      throw new WarningException("El usuario en sesión no tiene organizador relacionado.");
    }
    $respuesta->almacenes =  $res;
    return $respuesta;
  }

  public function generarDistribucionQR($arrayDatoFila, $usuarioId, $documentoId, $almacenId, $dataFilasSeleccionadas, $empresaId, $datosGuia)
  {
    $respuesta = AlmacenesNegocio::create()->generarRecepcion($documentoId, $almacenId, $dataFilasSeleccionadas, $usuarioId, $empresaId, $datosGuia);

    $tipo = 1; //Almacenaje
    $persona_id = UsuarioNegocio::create()->getUsuario($usuarioId)[0]['persona_id'];
    $almacenRecepcionId = Almacenes::create()->obtenerPersonaOrganizadorXOrganizadorPadreXOrganizadorTipo($persona_id, $almacenId, 12)[0]['id']; //12 = Recepción
    $max_grupo_qr = Almacenes::create()->obtenerMaximoGrupoPaquete()[0]['grupo_qr'];
    foreach ($arrayDatoFila as $index => $arrayDatoFilaItem) {
      $bien_id = $arrayDatoFilaItem['bien_id'];
      $filtradoXBienId = array_values(array_filter($respuesta->movimiento_detalle, function ($item) use ($bien_id) {
        return $item['bien_id'] == $bien_id;
      }));
      $arrayDatoFilaItem['movimiento_bien_id'] = $filtradoXBienId[0]['movimiento_bien_id'];
      foreach ($arrayDatoFilaItem['arrayDistribucionQR'] as $indexArrayDistribucionQR => $arrayDistribucionQRItem) {
        foreach ($arrayDistribucionQRItem['distribucion'] as $distribucionItem) {
          if (intval($distribucionItem['tipo']) == 1) {
            for ($i = 0; $i < intval($distribucionItem['indice2']); $i++) {
              $respuestaPaquete = Almacenes::create()->registrarPaquete($arrayDatoFilaItem['bien_id'], $distribucionItem['organizador_destino_id'], $arrayDistribucionQRItem['unidad_minera_id'], 1, $distribucionItem['indice1'], $usuarioId, $max_grupo_qr);
              $respuestaPaqueteTraking = Almacenes::create()->registrarPaqueteTraking($arrayDatoFilaItem['movimiento_bien_id'], $respuestaPaquete[0]['vout_id'], $almacenRecepcionId, $tipo, null, $usuarioId);
            }
          } else {
            for ($i = 0; $i < intval($distribucionItem['indice1']); $i++) {
              $respuestaPaquete = Almacenes::create()->registrarPaquete($arrayDatoFilaItem['bien_id'], $distribucionItem['organizador_destino_id'], $arrayDistribucionQRItem['unidad_minera_id'], 1, $distribucionItem['indice2'], $usuarioId, $max_grupo_qr);
              $respuestaPaqueteTraking = Almacenes::create()->registrarPaqueteTraking($arrayDatoFilaItem['movimiento_bien_id'], $respuestaPaquete[0]['vout_id'], $almacenRecepcionId, $tipo, null, $usuarioId);
            }
          }
        }
      }
    }

    $respuesta = new stdClass();
    if (ObjectUtil::isEmpty($respuestaPaqueteTraking)) {
      throw new WarningException("Hubo un problema al relaizar la operación");
    } else {
      $mensaje = 'Operacion registrada Exitosamente';
      $tipo_mensaje = 1;

      $respuesta->tipo_mensaje = $tipo_mensaje;
      $respuesta->mensaje = $mensaje;
    }
    return $respuesta;
  }

  public function editarDistribucionQR($arrayDatoFila, $usuarioId)
  {
    $tipo = 1; //Almacenaje
    $max_grupo_qr = Almacenes::create()->obtenerMaximoGrupoPaquete()[0]['grupo_qr'];
    foreach ($arrayDatoFila as $index => $arrayDatoFilaItem) {
      $respuestaEstado = Almacenes::create()->paquete_traking_cambiarEstadoXPaqueteId($arrayDatoFilaItem['paquete_id'], 0);
      $respuestaEstadoPaquete = Almacenes::create()->paquete_cambiarEstadoXPaqueteId($arrayDatoFilaItem['paquete_id'], 0);
      foreach ($arrayDatoFilaItem['arrayDistribucionQR'] as $indexArrayDistribucionQR => $arrayDistribucionQRItem) {
        foreach ($arrayDistribucionQRItem['distribucion'] as $distribucionItem) {
          if (intval($distribucionItem['tipo']) == 1) {
            for ($i = 0; $i < intval($distribucionItem['indice2']); $i++) {
              $respuestaPaquete = Almacenes::create()->registrarPaquete($arrayDatoFilaItem['bien_id'], $arrayDatoFilaItem['organizador_destino_id'], $arrayDistribucionQRItem['unidad_minera_id'], 1, $distribucionItem['indice1'], $usuarioId, $max_grupo_qr);
              $respuestaPaqueteTraking = Almacenes::create()->registrarPaqueteTraking($arrayDatoFilaItem['movimiento_bien_id'], $respuestaPaquete[0]['vout_id'], $arrayDatoFilaItem['organizador_id'], $tipo, null, $usuarioId);
            }
          } else {
            for ($i = 0; $i < intval($distribucionItem['indice1']); $i++) {
              $respuestaPaquete = Almacenes::create()->registrarPaquete($arrayDatoFilaItem['bien_id'], $arrayDatoFilaItem['organizador_destino_id'], $arrayDistribucionQRItem['unidad_minera_id'], 1, $distribucionItem['indice2'], $usuarioId, $max_grupo_qr);
              $respuestaPaqueteTraking = Almacenes::create()->registrarPaqueteTraking($arrayDatoFilaItem['movimiento_bien_id'], $respuestaPaquete[0]['vout_id'], $arrayDatoFilaItem['organizador_id'], $tipo, null, $usuarioId);
            }
          }
        }
      }
    }

    $respuesta = new stdClass();
    if (ObjectUtil::isEmpty($respuestaPaqueteTraking)) {
      throw new WarningException("Hubo un problema al relaizar la operación");
    } else {
      $mensaje = 'Operacion registrada Exitosamente';
      $tipo_mensaje = 1;

      $respuesta->tipo_mensaje = $tipo_mensaje;
      $respuesta->mensaje = $mensaje;
      $respuesta->bien_id = $arrayDatoFila[0]['bien_id'];
    }
    return $respuesta;
  }

  //Almacenado
  public function obtenerPaqueteAlmacenadoXCriterios($criterios, $elementosFiltrados, $columns, $order, $start, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);

    $resultadoOrganizadorIds = $this->obtenerOrganizadorHijos($criterios['almacen']);
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Almacenes::create()->obtenerPaqueteAlmacenadoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
  }

  public function obtenerCantidadPaqueteAlmacenadoXCriterios($criterios, $columns, $order, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);

    $resultadoOrganizadorIds = $this->obtenerOrganizadorHijos($criterios['almacen']);
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Almacenes::create()->obtenerCantidadPaqueteAlmacenadoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $columnaOrdenar, $formaOrdenar);
  }

  public function obtenerPaqueteTrakingDetalleXBienId($bienId, $almacen)
  {
    $resultadoOrganizadorIds = $this->obtenerOrganizadorHijos($almacen);
    return Almacenes::create()->paquete_trakingObtenerDetalle($bienId, $resultadoOrganizadorIds);
  }

  public function obtenerMovimientoPaqueteTraking($id)
  {
    $data = Almacenes::create()->obtenerMovimientoPaqueteTraking($id);
    $data1 = Almacenes::create()->obtenerMovimientoPaqueteDetalleTraking($id);

    foreach ($data as $index => $dataItem) {
      $dataOrganizador = Organizador::create()->getOrganizador($dataItem['organizador_id']);
      $organziador_id = $dataOrganizador[0]['organizador_padre_id'];
      $dataPadre = null;
      $bandera_organizador = true;

      while ($bandera_organizador) {
        $id_ = $organziador_id;
        $dataPadre = Organizador::create()->getOrganizador($id_);
        if (ObjectUtil::isEmpty($dataPadre)) {
          break;
        }
        $id_ = $dataPadre[0]['organizador_padre_id'];
        $organziador_id = $id_;
        if ($dataPadre[0]['organizador_tipo_id'] == 10) {
          $bandera_organizador = false;
          $organziador_id = $id_;
        }
      }

      $data[$index]['almacen'] = ObjectUtil::isEmpty($dataPadre) ? "" : ($dataPadre[0]['codigo'] . " | " . $dataPadre[0]['descripcion']);
    }

    $resultado = ObjectUtil::isEmpty($data1) ? $data : array_merge($data, $data1);

    return $resultado;
  }

  //Despacho
  public function obtenerDespachoXCriterios($criterios, $elementosFiltrados, $columns, $order, $start, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
    $estadoId = $criterios['estadoId'];
    $tipoId = $criterios['tipoId'];
    $almacenId = $criterios['almacen'] == "" ? null : $criterios['almacen'];
    // $resultadoOrganizadorIds = $this->obtenerOrganizadorHijos($almacenId);
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];

    $almacenTransitoId = Organizador::create()->getDataOrganizadorXOrganizadorTipo(15)[0]['id']; //15 = Transito

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Almacenes::create()->obtenerDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenTransitoId, $almacenId, $serie, $numero, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
  }

  public function obtenerCantidadDespachoXCriterios($criterios, $columns, $order, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
    $estadoId = $criterios['estadoId'];
    $tipoId = $criterios['tipoId'];
    $almacenId = $criterios['almacen'] == "" ? null : $criterios['almacen'];
    // $resultadoOrganizadorIds = $this->obtenerOrganizadorHijos($almacenId);
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];

    $almacenTransitoId = Organizador::create()->getDataOrganizadorXOrganizadorTipo(15)[0]['id']; //15 = Transito

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Almacenes::create()->obtenerCantidadDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenTransitoId, $almacenId, $serie, $numero, $columnaOrdenar, $formaOrdenar);
  }

  public function visualizarDetalleDespacho($id, $movimientoId)
  {
    $respuesta = new stdClass();
    $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($id);
    $respuesta->detalle = MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);

    foreach ($respuesta->detalle as $index => $itemDetalle) {
      $res = Almacenes::create()->paquete_trakingObtenerXmovimientoBienId($itemDetalle['movimiento_bien_id']);
      $respuesta->detalle[$index]['cantidadDespacho'] = $res[0]['cantidad'];
    }

    $respuesta->dataOrganizadorXUnidadMinera = Organizador::create()->getDataOrganizadorXUnidadMinera();
    return $respuesta;
  }

  public function obtenerPaqueteDespachoXCriterios($criterios, $elementosFiltrados, $columns, $order, $start, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
    $estadoId = $criterios['estadoId'];

    $resultadoOrganizadorIds = Organizador::create()->getDataOrganizadorXOrganizadorTipo(15)[0]['id']; //15 = Transito
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Almacenes::create()->obtenerPaqueteDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $estadoId, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
  }

  public function obtenerCantidadPaqueteDespachoXCriterios($criterios, $columns, $order, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
    $estadoId = $criterios['estadoId'];

    $resultadoOrganizadorIds = $this->obtenerOrganizadorHijos($criterios['almacen']);
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Almacenes::create()->obtenerCantidadPaqueteDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $estadoId, $columnaOrdenar, $formaOrdenar);
  }

  public function obtenerConfiguracionInicial($opcionId, $empresaId, $usuarioId, $documentoId = null)
  {
    // obtenemos el id del movimiento tipo que utiliza la opcion
    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    if (ObjectUtil::isEmpty($movimientoTipo)) {
      throw new WarningException("No se encontró el movimiento asociado a esta opción");
    }
    $movimientoTipoId = $movimientoTipo[0]["id"];
    $respuesta = new stdClass();
    // $respuesta->movimiento_tipo = MovimientoTipoNegocio::create()->getMovimientoTipo($movimientoTipoId);
    $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXMovimientoTipo($movimientoTipoId);
    if (ObjectUtil::isEmpty($respuesta->documento_tipo)) {
      throw new WarningException("El movimiento no cuenta con tipos de documentos asociados");
    }

    // identificador_negocio
    $documentoTipoDefectoId = $respuesta->documento_tipo[0]["id"];
    if (!ObjectUtil::isEmpty($movimientoTipo[0]['documento_tipo_defecto_id'])) {
      $documentoTipoDefectoId = $movimientoTipo[0]['documento_tipo_defecto_id'];
    }

    $respuesta->dataDocumento = null;
    if (!ObjectUtil::isEmpty($documentoId)) {
      $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($documentoId);
      $documentoTipoDefectoId = $respuesta->dataDocumento[0]['documento_tipo_id'];
    }

    $respuesta->documento_tipo_conf = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDato($documentoTipoDefectoId, $usuarioId);

    $respuesta->bien = [["id" => "", "text" => ""]];;

    $respuesta->movimientoTipo = $movimientoTipo;

    $dataEmpresa = EmpresaNegocio::create()->obtenerEmpresaXId($empresaId);
    $respuesta->dataEmpresa = $dataEmpresa;

    $respuesta->accionesEnvio = Movimiento::create()->obtenerMovimientoTipoAcciones($movimientoTipoId, 2);
    $respuesta->accionEnvioPredeterminado = Movimiento::create()->obtenerMovimientoTipoAccionEnvioPredeterminado($movimientoTipoId);

    // obtener datos para las columnas del detalle
    $respuesta->movimientoTipoColumna = MovimientoTipoNegocio::create()->obtenerMovimientoTipoColumna($movimientoTipoId);
    $respuesta->periodo = null;
    if (!ObjectUtil::isEmpty($documentoId)) {
      $respuesta->periodo = PeriodoNegocio::create()->obtenerPeriodoXEmpresa($empresaId);
    } else {
      $respuesta->periodo = PeriodoNegocio::create()->obtenerPeriodoAbiertoXEmpresa($empresaId);
    }

    if (ObjectUtil::isEmpty($respuesta->periodo)) {
      throw new WarningException("No existe periodo abierto.");
    }
    $organizadores = null;
    if ($documentoTipoDefectoId == Configuraciones::DESPACHO) {
      $organizadores->almacenes = OrganizadorNegocio::create()->getOrganizador(Configuraciones::ALMACEN_LIMA);
    } else {
      $organizadores = AlmacenesNegocio::create()->obtenerConfiguracionInicialListadoDocumentos($usuarioId);
    }
    $respuesta->organizador = $organizadores->almacenes;

    return $respuesta;
  }

  public function obtenerPlacaVehiculo($id)
  {
    return Almacenes::create()->obtener_vehiculoTransportistaId($id);
  }

  public function obtenerPaqueteXAlmacenId($id)
  {
    $resultadoOrganizadorIds = str_replace(["(", ")"], "", $this->obtenerOrganizadorHijos($id));
    $respuesta = Almacenes::create()->obtener_paqueteXAlmacenId($resultadoOrganizadorIds);
    if (ObjectUtil::isEmpty($respuesta)) {
      $respuesta = [["id" => "", "text" => ""]];;
    }
    return $respuesta;
  }

  //Recepcion despacho
  public function obtenerPaqueteRecepcionDespachoXCriterios($criterios, $elementosFiltrados, $columns, $order, $start, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);

    $resultadoOrganizadorIds = $criterios['almacen'];
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Almacenes::create()->obtenerPaqueteRecepcionDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $serie, $numero, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
  }

  public function obtenerCantidadPaqueteRecepcionDespachoXCriterios($criterios, $columns, $order, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);

    $resultadoOrganizadorIds = $criterios['almacen'];
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Almacenes::create()->obtenerCantidadPaqueteRecepcionDespachoXCriterios($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $serie, $numero, $columnaOrdenar, $formaOrdenar);
  }

  public function getDataOrganizadoresHijos($almacenId)
  {
    return OrganizadorNegocio::create()->getDataOrganizadoresHijos($almacenId);
  }

  public function generarRecepcionDespacho($documentoId, $almacenId, $dataFilasSeleccionadas, $usuarioId, $empresaId)
  {
    $respuesta = AlmacenesNegocio::create()->generarRecepcion($documentoId, $almacenId, $dataFilasSeleccionadas, $usuarioId, $empresaId);
    $tipo = 1; //Almacenaje
    $persona_id = UsuarioNegocio::create()->getUsuario($usuarioId)[0]['persona_id'];
    $almacenRecepcionId = Almacenes::create()->obtenerPersonaOrganizadorXOrganizadorPadreXOrganizadorTipo($persona_id, $almacenId, 12)[0]['id']; //12 = Recepción
    foreach ($dataFilasSeleccionadas as $index => $dataFilasSeleccionadasItem) {
      $bien_id = $dataFilasSeleccionadasItem['bien_id'];
      $filtradoXBienId = array_values(array_filter($respuesta->movimiento_detalle, function ($item) use ($bien_id) {
        return $item['bien_id'] == $bien_id;
      }));
      $arrayDatoFilaItem['movimiento_bien_id'] = $filtradoXBienId[0]['movimiento_bien_id'];
      $res = Almacenes::create()->paquete_trakingObtenerXmovimientoBienId($dataFilasSeleccionadasItem['movimiento_bien_id']);
      $respuestaEstado = Almacenes::create()->paquete_traking_cambiarEstadoXPaqueteId($res[0]['paquete_id'], 0);
      $respuestaPaqueteTraking = Almacenes::create()->registrarPaqueteTraking($arrayDatoFilaItem['movimiento_bien_id'], $res[0]['paquete_id'], $almacenRecepcionId, $tipo, null, $usuarioId);
      // $respuestaEstado = Almacenes::create()->paquete_traking_cambiarEstadoXPaqueteId($res[0]['paquete_id'], 2);
    }

    // $unidadId = UnidadNegocio::create()->obtenerActivasXBien($dataFilasSeleccionadasItem['bien_id'])[0]['id'];
    // foreach ($dataFilasSeleccionadas[0] as $index => $dataFilasSeleccionadasItem) {
    //   $respuestPaqueteDetalle = Almacenes::create()->registrarPaqueteDetalle(null, $dataFilasSeleccionadasItem['paquete_id'], $dataFilasSeleccionadasItem['bien_id'], $dataFilasSeleccionadasItem['organizador_id'], 1, $dataFilasSeleccionadasItem['cantidad_recepcion'], $unidadId, null, $usuarioId);
    // }
    $respuestaDocumentoEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 18, $usuarioId); //18 = recepcionado
  }

  public function registrarPaqueteDetalle($movimientoId, $paqueteId, $bienId, $organizadorId, $tipo, $cantidad, $unidadMedidaId, $comentario, $usuarioId)
  {
    return Almacenes::create()->registrarPaqueteDetalle($movimientoId, $paqueteId, $bienId, $organizadorId, $tipo, $cantidad, $unidadMedidaId, $comentario, $usuarioId);
  }

  //Entrega
  public function obtenerEntregaXCriterios($criterios, $elementosFiltrados, $opcionId, $columns, $order, $start, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
    $estadoId = $criterios['estadoId'];
    $tipoId = $criterios['tipoId'];
    $almacenId = $criterios['almacen'] == "" ? null : $criterios['almacen'];
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    $bandera = null;
    if ($opcionId == 416) {
      $bandera = $usuarioId;
    }

    return Almacenes::create()->obtenerEntregaXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId, $bandera, $serie, $numero, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
  }

  public function obtenerCantidadEntregaXCriterios($criterios, $opcionId, $columns, $order, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
    $estadoId = $criterios['estadoId'];
    $tipoId = $criterios['tipoId'];
    $almacenId = $criterios['almacen'] == "" ? null : $criterios['almacen'];
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    $dataPerfil = PerfilNegocio::create()->obtenerPerfilXUsuarioId($usuarioId);
    $filtradosPerfil = array_values(array_filter($dataPerfil, function ($itemPerfil) {
      return ($itemPerfil['id'] == PerfilNegocio::PERFIL_ADMINISTRADOR_ID || $itemPerfil['id'] == PerfilNegocio::PERFIL_ADMINISTRADOR_TI_ID || $itemPerfil['id'] == PerfilNegocio::PERFIL_JEFE_LOGISTA);
    }));

    $bandera = null;
    if ($opcionId == 416 && ObjectUtil::isEmpty($filtradosPerfil)) {
      $bandera = $usuarioId;
    }

    return Almacenes::create()->obtenerCantidadEntregaXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $almacenId, $bandera, $serie, $numero, $columnaOrdenar, $formaOrdenar);
  }

  public function obtenerAreRequerimiento($id)
  {
    $dataArea = Almacenes::create()->getAllAreaXPersonaId($id);
    $dataRequerimiento = Almacenes::create()->getAllRequerimientoXatenderXPersonaId($id);

    $respuesta = new stdClass();
    $respuesta->area = $dataArea;
    $respuesta->requerimiento = $dataRequerimiento;
    return $respuesta;
  }

  public function obtenerDetalleRequerimiento($documentoId)
  {
    $movimientoId = Documento::create()->obtenerDocumentoDatos($documentoId)[0]['movimiento_id'];
    $documentoDetalle = new stdClass();
    $documentoDetalle->detalleRequerimientos = MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);
    return $documentoDetalle;
  }

  public function obtenerStockActual($bienId, $indice, $unidadMedidaId, $organizadorId = null, $bandera, $banderaLogico = null)
  {
    $stock = Almacenes::create()->obtenerStockActual($bienId, $organizadorId, $unidadMedidaId, $bandera);
    $stock = ObjectUtil::isEmpty($stock) ? [array("stock" => 0)] : $stock;

    $stock[0]['indice'] = $indice;
    if (!ObjectUtil::isEmpty($banderaLogico)) {
      $stock[0]['stockLogico'] = Almacenes::create()->obtenerStockActualLogico($bienId, $banderaLogico)[0]['cantidad'];
    }

    return $stock;
  }

  public function obtenerStockParaProductosDeCopia($detalle, $almacenId)
  {
    $dataStock = array();
    $organizadoresids = $this->obtenerOrganizadorHijos($almacenId);
    foreach ($detalle as $item) {
      //TIENE QUE SER SIMILAR AL METODO DEL CONTROLADOR: obtenerStockActual
      $bienId = $item['bienId'];
      $unidadMedidaId = $item['unidadMedidaId'];
      // $organizadorId = $item['organizadorId'];
      // if (!ObjectUtil::isEmpty($organizadorDefectoId) && $organizadorDefectoId != 0) {
      //   $organizadorId = $organizadorDefectoId;
      // }
      $stock = $this->obtenerStockActual($bienId, $item['index'], $unidadMedidaId, $organizadoresids, 1);

      array_push($dataStock, $stock);
    }

    return $dataStock;
  }

  public function obtenerStockPorBien($bienId, $unidadMedidaId, $indice, $almacenId)
  {
    $organizadoresids = $this->obtenerOrganizadorHijos($almacenId);
    return $this->obtenerStockActual($bienId, $indice, $unidadMedidaId, $organizadoresids, 0);
  }

  public function  visualizarDetalleEntrega($id, $movimientoId)
  {
    $respuesta = new stdClass();
    $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($id);
    // $respuesta->detalle = Almacenes::create()->obtenerpaquete_detalleXIdMovimiento($movimientoId);
    $respuesta->detalle = MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);

    return $respuesta;
  }

  public function generarDespacho($almacenDestino, $vehiculoId, $pesaje, $usuarioId, $detalle)
  {
    $alamcenOrigen = Configuraciones::ALMACEN_LIMA;
    $organizadorOrigen =  OrganizadorNegocio::create()->getOrganizador($alamcenOrigen);
    $ubigeoOrigen = PersonaNegocio::create()->obtenerUbigeoXId($organizadorOrigen[0]["ubigeo_id"]);

    $organizadorDestino =  OrganizadorNegocio::create()->getOrganizador($almacenDestino);
    $ubigeoDestino = PersonaNegocio::create()->obtenerUbigeoXId($organizadorDestino[0]["ubigeo_id"]);

    $datosVehiculoTransportista = Almacenes::create()->obtener_vehiculoTransportistaXVehiculoId($vehiculoId);

    $documentoTipoId = 289;
    $opcionId = 414;
    $empresaId = 2;
    $configuraciones = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDato($documentoTipoId, $usuarioId);
    $periodoId = PeriodoNegocio::create()->obtenerUltimoPeriodoActivoXEmpresa($empresaId)[0]['id'];

    if (ObjectUtil::isEmpty($periodoId)) {
      throw new WarningException("No existe periodo abierto.");
    }

    $camposDinamicos = [];

    foreach ($configuraciones as $item) {
      $valor = null;
      switch ($item['tipo']) {
        case 7: // Serie
          $valor = $item['cadena_defecto'];
          break;
        case 8: // Número
        case 9: // Fecha
          $valor = $item['data'];
          break;
        case 2:
          $valor = $vehiculoId;
          break;
        case 23:
          $valor = $datosVehiculoTransportista[0]['transportista_id'];
          break;
        case 53: //Almacén origen
          $valor = $alamcenOrigen;
          break;
        case 54: //Almacén destino
          $valor = $almacenDestino;
          break;
        default:
          continue 2; // Salta al siguiente $item si el tipo no es manejado
      }
      $camposDinamicos[] = [
        "id" => $item['id'],
        "tipo" => $item['tipo'],
        "opcional" =>  $item['opcional'],
        "descripcion" => $item['descripcion'],
        "codigo" => $item['codigo'],
        "valor" => $valor
      ];
    }

    $documentoARelacionar = [];
    // $documentoARelacionar[] = array(
    //   "documentoId" => $documentoId,
    //   "movimientoId" => "",
    //   "tipo" => "1",
    //   "documentoPadreId" => ""
    // );


    $detalleDespacho = [];
    $detalleGuia = [];
    foreach ($detalle as $i => $itemDetalle) {
      $datosPaquete = Almacenes::create()->obtener_datosPaquete($itemDetalle['id']);

      $arrayItem = array(
        "bienId" => $datosPaquete[0]['bien_id'],
        "bienDesc" => $datosPaquete[0]['bien_descripcion'],
        "cantidadAceptada" => null,
        "cantidad" => 1,
        "unidadMedidaId" => $datosPaquete[0]['unidad_medida_id'],
        "unidadMedidaDesc" => "",
        "esCompra" => null,
        "compraDesc" => "",
        "stockBien" => "",
        "bienTramoId" => "",
        "subTotal" => "",
        "index" => $i,
        "precioCompra" => "",
        "organizadorId" => null,
        "paqueteId" => $itemDetalle['id'],
      );
      $detalleDespacho[] = $arrayItem;

      $arrayItemGuia = array(
        "bien_id" => $datosPaquete[0]['bien_id'],
        "bien_descripcion" => $datosPaquete[0]['bien_descripcion'],
        "distribucion" => $datosPaquete[0]['indice'] . "x" . $datosPaquete[0]['cantidad'],
        "paqueteId" => $itemDetalle['id']
      );
      $detalleGuia[] = $arrayItemGuia;
    }
    $documentoId = MovimientoNegocio::create()->guardar($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalleDespacho, $documentoARelacionar, 1, "Movimiento de despacho", 1, 2, null, $periodoId, null, null, null, null);

    $respuesta = new stdClass();
    $respuesta->detalle = $detalleGuia;
    $respuesta->ubigeoOrigen = $ubigeoOrigen;
    $respuesta->ubigeoDestino = $ubigeoDestino;
    $respuesta->datosVehiculoTransportista = $datosVehiculoTransportista;


    if (ObjectUtil::isEmpty($documentoId)) {
      throw new WarningException("Hubo un problema al relaizar la operación");
    } else {
      $mensaje = 'Operacion registrada Exitosamente';
      $tipo_mensaje = 1;

      $respuesta->tipo_mensaje = $tipo_mensaje;
      $respuesta->mensaje = $mensaje;
    }
    return $respuesta;
  }

  public function obtenerBienXTextoXOrganizadorIds($texto1, $texto2, $almacenId)
  {
    $organizadorIds = str_replace(["(", ")"], "", $this->obtenerOrganizadorHijos($almacenId));
    return Almacenes::create()->obtenerBienXTextoXOrganizadorIds($texto1, $texto2, $organizadorIds);
  }

  public function generarSalidaSolicitud($dataStockOk, $usuarioId)
  {
    //estado de bandera_entrega
    //1 = registro
    //2 = parcialmente atemndido
    //3 = atendido
    $respuesta = new stdClass();
    $banderaEntrega = 1;
    foreach ($dataStockOk as $item) {
      $dataMoviBien = MovimientoBien::create()->obtenerMovimientoBienXId($item['movimiento_bien_id']);
      $cantidadEntrega = $dataMoviBien[0]['cantidad_entrega'] + $item['reserva'];
      if ($cantidadEntrega == $dataMoviBien[0]['cantidad']) {
        $banderaEntrega = 3;
      } else {
        $banderaEntrega = 2;
      }
      $resEdit = MovimientoBien::create()->editarBanderaCantidadEntrega($item['movimiento_bien_id'], $banderaEntrega, $cantidadEntrega);
      $respuestPaqueteDetalle = Almacenes::create()->registrarPaqueteDetalle($dataStockOk[0]['movimiento_id'], null, $item['bien_id'], $item['organizador_id'], 2, $item['reserva'], $item['unidad_medida_id'], null, $usuarioId);
    }

    $respuestaDetalle = MovimientoBien::create()->obtenerXIdMovimiento($dataStockOk[0]['movimiento_id']);

    $filtraEstado2 = array_values(array_filter($respuestaDetalle, function ($item) {
      return $item['bandera_entrega'] == 2;
    }));
    $filtraEstado3 = array_values(array_filter($respuestaDetalle, function ($item) {
      return $item['bandera_entrega'] == 3;
    }));

    if (!ObjectUtil::isEmpty($filtraEstado2) && !ObjectUtil::isEmpty($filtraEstado3)) {
      $respuestaDocumentoEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($dataStockOk[0]['documento_id'], 19, $usuarioId);
    }
    if (ObjectUtil::isEmpty($filtraEstado2) && !ObjectUtil::isEmpty($filtraEstado3)) {
      $respuestaDocumentoEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($dataStockOk[0]['documento_id'], 20, $usuarioId);
    } else {
      $respuestaDocumentoEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($dataStockOk[0]['documento_id'], 19, $usuarioId);
    }


    if (ObjectUtil::isEmpty($respuestPaqueteDetalle)) {
      throw new WarningException("Hubo un problema al relaizar la operación");
    } else {
      $mensaje = 'Operacion registrada Exitosamente';
      $tipo_mensaje = 1;

      $respuesta->tipo_mensaje = $tipo_mensaje;
      $respuesta->mensaje = $mensaje;
    }
    return $respuesta;
  }

  //Reporte inventario
  public function obtenerConfiguracionesInicialesInventario($usuarioId)
  {
    $respuesta = new stdClass();
    $respuesta->bien = BienNegocio::create()->obtenerBienActivosInventario();
    $respuesta->bien_tipo = BienTipo::create()->obtenerBienTipoXTipo();
    $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
    $persona_id = UsuarioNegocio::create()->getUsuario($usuarioId)[0]['persona_id'];
    $res = Almacenes::create()->obtenerPersonaOrganizadorXOrganizadorTipo($persona_id, 10); //10 = Almacén General

    if (ObjectUtil::isEmpty($res)) {
      throw new WarningException("El usuario en sesión no tiene organizador relacionado.");
    }
    $respuesta->almacenes =  $res;
    return $respuesta;
  }

  public function obtenerDataInventarioXCriterios($criterios, $elementosFiltrados, $columns, $order, $start)
  {
    $almacen = $criterios[0]['almacen'];
    $bien = $criterios[0]['bien'];
    $bienTipo = $criterios[0]['bienTipo'];
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);

    $resultadoOrganizadorIds = str_replace(["(", ")"], "", $this->obtenerOrganizadorHijos($almacen));
    $bienIdFormateado = Util::convertirArrayXCadena($bien);
    $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Almacenes::create()->obtenerDataInventarioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $bienIdFormateado, $bienTipoIdFormateado, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
  }

  public function obtenerCantidadDataInventarioXCriterios($criterios, $columns, $order)
  {
    $almacen = $criterios[0]['almacen'];
    $bien = $criterios[0]['bien'];
    $bienTipo = $criterios[0]['bienTipo'];
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);

    $resultadoOrganizadorIds = str_replace(["(", ")"], "", $this->obtenerOrganizadorHijos($almacen));
    $bienIdFormateado = Util::convertirArrayXCadena($bien);
    $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
    return Almacenes::create()->obtenerCantidadDataInventarioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $bienIdFormateado, $bienTipoIdFormateado, $columnaOrdenar, $formaOrdenar);
  }

  public function obtenerReporteStockExcel($criterios)
  {
    $almacen = $criterios[0]['almacen'];
    $bien = $criterios[0]['bien'];
    $bienTipo = $criterios[0]['bienTipo'];
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);

    $resultadoOrganizadorIds = str_replace(["(", ")"], "", $this->obtenerOrganizadorHijos($almacen));
    $bienIdFormateado = Util::convertirArrayXCadena($bien);
    $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);

    $formaOrdenar = 'desc';
    $columnaOrdenar = 'codigo_descripcion';

    $data = Almacenes::create()->obtenerDataInventarioXCriteriosExcel($fechaEmisionInicio, $fechaEmisionFin, $resultadoOrganizadorIds, $bienIdFormateado, $bienTipoIdFormateado, $columnaOrdenar, $formaOrdenar);

    $objPHPExcel = new PHPExcel();
    $i = 1;

    $estilos_tabla = array(
      'font' => array(
        'name' => 'Arial',
        'bold' => true,
        'italic' => false,
        'strike' => false,
        'size' => 11,
        'color' => array('rgb' => 'FFFFFF'),
      ),
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      ),
      'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'febf00'))
    );

    $estilos_filas = array(
      'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'FFFBE5'),
      ),
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      )
    );

    $estiloDetCabecera = array(
      'font' => array(
        'name' => 'Arial',
        'bold' => true,
        'size' => 17,
        'color' => array('rgb' => '000000'),
      ),
      'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'wrap' => FALSE
      )
    );

    $i++;

    // Insertar una imagen en la celda A$i
    $drawing = new PHPExcel_Worksheet_Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Logo');
    $drawing->setPath(__DIR__ . '/../../vistas/images/logo_pepas_de_oro.png'); // Ruta a la imagen
    $drawing->setHeight(70); // Altura en píxeles
    $drawing->setCoordinates('H' . $i); // Celda donde se insertará
    $drawing->setWorksheet($objPHPExcel->getActiveSheet());


    $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Solicitud de cotización');
    $objPHPExcel->getActiveSheet()->mergeCells("B$i:I$i");
    $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':B' . $i)->applyFromArray($estiloDetCabecera);
    $i++;

    //ANCHOS DE COLUMNAS        
    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(50);
    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(40);
    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(100);
    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(12.14);


    $i = $i + 3;
    $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'PRODUCTO');
    $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'TIPO PRODUCTO');
    $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'UNIDAD MEDIDA');
    $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'STOCK');

    $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':E' . $i)->applyFromArray($estilos_tabla);
    $objPHPExcel->getActiveSheet()->getStyle("B$i:E$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $i++;

    foreach ($data as $index => $item) {
      $cantidad = abs($item['cantidad'] - $item['cantidad_atendida']);
      $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $item['codigo_descripcion']);
      $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $item['codigo_descripcion_tipo']);
      $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $item['unidad_medida_descripcion']);
      $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $item['stock']);

      $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->getAlignment()->setWrapText(true);

      $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':E' . $i)->applyFromArray($estilos_filas);

      $i++;
    }

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save(__DIR__ . '/../../util/formatos/reporteStock.xlsx');
    return 1;
  }

  //Recepcion Mina
  public function obtenerPaqueteRecepcionMinaXCriterios($criterios, $elementosFiltrados, $columns, $order, $start, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);

    $resultadoOrganizadorIds = $criterios['almacen'];
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    $persona_id = UsuarioNegocio::create()->getUsuario($usuarioId)[0]['persona_id'];
    $almacenId = Almacenes::create()->obtenerPersonaOrganizadorXOrganizadorPadreXOrganizadorTipo($persona_id, $resultadoOrganizadorIds, 12)[0]['id']; //12 = Recepcion

    return Almacenes::create()->obtenerPaqueteRecepcionMinaXCriterios($fechaEmisionInicio, $fechaEmisionFin, $almacenId, $serie, $numero, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
  }

  public function obtenerCantidadPaqueteRecepcionMinaXCriterios($criterios, $columns, $order, $usuarioId)
  {
    $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
    $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);

    $resultadoOrganizadorIds = $criterios['almacen'];
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    $persona_id = UsuarioNegocio::create()->getUsuario($usuarioId)[0]['persona_id'];
    $almacenId = Almacenes::create()->obtenerPersonaOrganizadorXOrganizadorPadreXOrganizadorTipo($persona_id, $resultadoOrganizadorIds, 12)[0]['id']; //12 = Recepcion

    return Almacenes::create()->obtenerCantidadPaqueteRecepcionMinaXCriterios($fechaEmisionInicio, $fechaEmisionFin, $almacenId, $serie, $numero, $columnaOrdenar, $formaOrdenar);
  }

  public function generarDistribucionRecepcionMina($documentoId, $dataFilasSeleccionadas, $usuarioId)
  {
    foreach ($dataFilasSeleccionadas[0] as $index => $dataFilasSeleccionadasItem) {
      $respuestaEstado = Almacenes::create()->paquete_traking_cambiarEstadoXPaqueteId($dataFilasSeleccionadasItem['paquete_id'], 2);

      $unidadId = UnidadNegocio::create()->obtenerActivasXBien($dataFilasSeleccionadasItem['bien_id'])[0]['id'];
      $respuestPaqueteDetalle = Almacenes::create()->registrarPaqueteDetalle(null, $dataFilasSeleccionadasItem['paquete_id'], $dataFilasSeleccionadasItem['bien_id'], $dataFilasSeleccionadasItem['organizador_id'], 1, $dataFilasSeleccionadasItem['cantidad_recepcion'], $unidadId, null, $usuarioId);
    }
    $respuestaDocumentoEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 18, $usuarioId); //18 = recepcionado
  }


  //MinaApp
  public function obtener_datosOrganizador($organizadorId)
  {
    return Organizador::create()->getOrganizador($organizadorId);
  }

  public function obtener_datosPaquete($paqueteId)
  {
    return Almacenes::create()->obtener_datosPaquete($paqueteId);
  }

  public function almacenarPaquete($organizadorId, $paqueteId, $usuarioId)
  {
    $tipo = 2;
    $respuestaEstado = Almacenes::create()->paquete_traking_cambiarEstadoXdetallePaqueteId($paqueteId);
    return Almacenes::create()->registrarPaqueteTraking(null, $paqueteId, $organizadorId, $tipo, null, $usuarioId);
  }
}
