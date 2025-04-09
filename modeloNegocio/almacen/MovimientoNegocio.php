<?php
require_once __DIR__ . '/../../modelo/almacen/Movimiento.php';
require_once __DIR__ . '/../../modelo/almacen/MovimientoBien.php';
require_once __DIR__ . '/../../modelo/almacen/BienUnico.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/DetraccionNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/CentroCostoNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/PlanContableNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/ContDistribucionContableNegocio.php';
require_once __DIR__ . '/../commons/ConstantesNegocio.php';
require_once __DIR__ . '/MovimientoTipoNegocio.php';
require_once __DIR__ . '/DocumentoTipoNegocio.php';
require_once __DIR__ . '/OrganizadorNegocio.php';
require_once __DIR__ . '/UnidadNegocio.php';
require_once __DIR__ . '/BienNegocio.php';
require_once __DIR__ . '/EmailPlantillaNegocio.php';
require_once __DIR__ . '/EmailEnvioNegocio.php';
require_once __DIR__ . '/DocumentoNegocio.php';
require_once __DIR__ . '/ExcelNegocio.php';
require_once __DIR__ . '/PersonaNegocio.php';
require_once __DIR__ . '/DocumentoTipoDatoListaNegocio.php';
require_once __DIR__ . '/DocumentoDatoValorNegocio.php';
require_once __DIR__ . '/BienPrecioNegocio.php';
require_once __DIR__ . '/EmpresaNegocio.php';
require_once __DIR__ . '/MonedaNegocio.php';
require_once __DIR__ . '/PagoNegocio.php';
require_once __DIR__ . '/BienUnicoNegocio.php';
require_once __DIR__ . '/MovimientoDuaNegocio.php';
require_once __DIR__ . '/PeriodoNegocio.php';
require_once __DIR__ . '/ProgramacionAtencionNegocio.php';
require_once __DIR__ . '/AgenciaNegocio.php';
require_once __DIR__ . '/../../util/NumeroALetra/EnLetras.php';
require_once __DIR__ . '/../../util/Configuraciones.php';
require_once __DIR__ . '/../../modelo/almacen/Actividad.php';
require_once __DIR__ . '/../../modelo/almacen/Pago.php';
require_once __DIR__ . '/../../modelo/contabilidad/Tabla.php';
require_once __DIR__ . '/MatrizAprobacionNegocio.php';
require_once __DIR__ . '/../../modelo/almacen/ProgramacionPagos.php';
require_once __DIR__ . '/../../modelo/almacen/OrdenCompraServicio.php';



$objPHPExcel = null;
$objWorkSheet = null;
$i = 0;
$j = 0;
$h = null;
$documentoTipoIdAnterior = null;

class MovimientoNegocio extends ModeloNegocioBase
{
  const HISTORICO_ACCION_CREACION = 1;
  const HISTORICO_ACCION_EDICION = 2;
  const HISTORICO_ACCION_ADICION_LC = 3;
  const HISTORICO_ACCION_ORDENAR_LC = 4;
  const HISTORICO_ACCION_ELIMINAR_LC = 5;
  const HISTORICO_ACCION_MARCAR_LC = 6;
  const HISTORICO_ACCION_ANULACION = 7;
  const HISTORICO_ACCION_REGISTRA_RELACION = 8;
  const HISTORICO_ACCION_ELIMINA_RELACION = 9;

  var $dtDuaId = 256;
  var $dataRetencion = array("id" => 1, "descripcion" => "001 | Retención", "monto_minimo" => 700.00, "porcentaje" => 3.0);
  var $arrayDocumentoTipoSinDetalle = array(270);

  /**
   *
   * @return MovimientoNegocio
   */
  static function create()
  {
    return parent::create();
  }

  private $docInafectas;

  public function obtenerConfiguracionInicial($opcionId, $empresaId, $usuarioId, $documentoId = null)
  {
    // obtenemos el id del movimiento tipo que utiliza la opcion
    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    if (ObjectUtil::isEmpty($movimientoTipo)) {
      throw new WarningException("No se encontró el movimiento asociado a esta opción");
    }
    $movimientoTipoId = $movimientoTipo[0]["id"];
    $respuesta = new ObjectUtil();
    // $respuesta->movimiento_tipo = MovimientoTipoNegocio::create()->getMovimientoTipo($movimientoTipoId);
    $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXMovimientoTipo($movimientoTipoId);
    if (ObjectUtil::isEmpty($respuesta->documento_tipo)) {
      throw new WarningException("El movimiento no cuenta con tipos de documentos asociados");
    }

    if($respuesta->documento_tipo[0]["id"] == Configuraciones::SOLICITUD_REQUERIMIENTO || $respuesta->documento_tipo[0]["id"] == Configuraciones::REQUERIMIENTO_AREA  || $respuesta->documento_tipo[0]["id"] == Configuraciones::GENERAR_COTIZACION){
      //validar perfil
      $mostrarAccNuevo = 0;
      $dataPerfil = PerfilNegocio::create()->obtenerPerfilXUsuarioId($usuarioId);
      foreach ($dataPerfil as $itemPerfil) {
        if ($itemPerfil['id'] == PerfilNegocio::PERFIL_ADMINISTRADOR_ID || $itemPerfil['id'] == PerfilNegocio::PERFIL_ADMINISTRADOR_TI_ID || $itemPerfil['id'] == PerfilNegocio::PERFIL_JEFE_LOGISTA || $itemPerfil['id'] == PerfilNegocio::PERFIL_SOLICITANTE_REQUERIMIENTO || $itemPerfil['id'] == PerfilNegocio::PERFIL_LOGISTA) {
          $mostrarAccNuevo = 1;
        }
      }
      if($mostrarAccNuevo != 1){
        throw new WarningException("No tiene perfil necesario para realizar esta acción");
      }
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

    if (!ObjectUtil::isEmpty($respuesta->documento_tipo_conf)) {
      foreach ($respuesta->documento_tipo_conf as $index => $itemDtd) {
        switch ($itemDtd["tipo"]) {
          case 17:
            $organizador_ids = $itemDtd['cadena_defecto'];
            break;
        }
      }
    }
    // $respuesta->bien = BienNegocio::create()->obtenerActivos($empresaId);
    //$respuesta->bien = BienNegocio::create()->obtenerActivosXMovimientoTipoId($empresaId, $movimientoTipoId);
    $respuesta->bien = [["id" => "", "text" => ""]];;
    if (!ObjectUtil::isEmpty($organizador_ids)) {
      $respuesta->organizador = OrganizadorNegocio::create()->obtenerXMovimientoTipo2($movimientoTipoId, $organizador_ids, 1);
    } else {
      if ($movimientoTipoId != "146" && $movimientoTipoId != "68" && $movimientoTipoId != "148" && $movimientoTipoId != "149" && $movimientoTipoId != "145") {
        $respuesta->organizador = OrganizadorNegocio::create()->obtenerXMovimientoTipo($movimientoTipoId);
      }
    }
    $respuesta->dataAgencia = AgenciaNegocio::create()->listarAgenciaActiva($empresaId);
    $respuesta->dataAgrupador = Tabla::create()->obtenerXPadreId(88);
    $respuesta->movimientoTipo = $movimientoTipo;
    // $respuesta->precioTipo = BienPrecioNegocio::create()->obtenerPrecioTipoActivo();

    $respuesta->precioTipo = BienPrecioNegocio::create()->obtenerPrecioTipoXMovimientoTipo($movimientoTipoId);

    $dataEmpresa = EmpresaNegocio::create()->obtenerEmpresaXId($empresaId);
    $respuesta->dataEmpresa = $dataEmpresa;
    $respuesta->moneda = MonedaNegocio::create()->obtenerComboMoneda();

    $respuesta->accionesEnvio = Movimiento::create()->obtenerMovimientoTipoAcciones($movimientoTipoId, 2);
    $respuesta->accionEnvioPredeterminado = Movimiento::create()->obtenerMovimientoTipoAccionEnvioPredeterminado($movimientoTipoId);

    // obtener datos para las columnas del detalle
    $respuesta->movimientoTipoColumna = MovimientoTipoNegocio::create()->obtenerMovimientoTipoColumna($movimientoTipoId);
    $respuesta->contOperacionTipo = ContOperacionTipoNegocio::create()->obtenerDocumentoTipoContOperacionTipoXMovimientoTipoId($movimientoTipoId);
    $respuesta->cuentaContable = PlanContableNegocio::create()->obtenerXEmpresaId($empresaId);
    $respuesta->centroCosto = CentroCostoNegocio::create()->listarCentroCosto($empresaId);
    $respuesta->bienActivoFijo = BienNegocio::create()->obtenerActivosFijosXEmpresa($empresaId);
    $respuesta->dataDetraccion = DetraccionNegocio::create()->obtenerDetraccionXEmpresaId($empresaId);
    $respuesta->dataRetencion = $this->dataRetencion;
    $respuesta->periodo = null;
    if (!ObjectUtil::isEmpty($documentoId)) {
      $respuesta->periodo = PeriodoNegocio::create()->obtenerPeriodoXEmpresa($empresaId);
    } else {
      $respuesta->periodo = PeriodoNegocio::create()->obtenerPeriodoAbiertoXEmpresa($empresaId);
    }

    if (ObjectUtil::isEmpty($respuesta->periodo)) {
      throw new WarningException("No existe periodo abierto.");
    }

    $respuesta->dataTipoCambio = null;
    if ($documentoTipoDefectoId == Configuraciones::GENERAR_COTIZACION) {
      $respuesta->postores = PersonaNegocio::create()->obtenerComboPersonaXPersonaClaseId('-1');
      $fechaActual = DateUtil::formatearCadenaACadenaBD(date("d/m/Y"));
      $respuesta->dataTipoCambio = TipoCambioNegocio::create()->obtenerTipoCambioXfecha($fechaActual);
      if (ObjectUtil::isEmpty($respuesta->dataTipoCambio)) {
        throw new WarningException("No existe tipo de cambio para la fecha actual.");
      }
    }
    $respuesta->centroCostoRequerimiento = CentroCostoNegocio::create()->listarCentroCostoXArea($empresaId, $usuarioId);
    return $respuesta;
  }

  public function obtenerBienPrecioXBienId($bienId, $unidadMedidaId, $monedaId, $opcionId)
  {
    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    $movimientoTipoId = $movimientoTipo[0]["id"];

    $data = BienPrecioNegocio::create()->obtenerBienPrecioXBienIdXMovimientoTipoId($bienId, $unidadMedidaId, $monedaId, $movimientoTipoId);
    return $data;
  }

  public function obtenerDocumentoTipo($opcionId, $usuarioId = null)
  {
    // obtenemos el id del movimiento tipo que utiliza la opcion
    $contador = 0;
    $movimientoTipoId = $this->obtenerIdXOpcion($opcionId);
    $respuesta = new ObjectUtil();
    //  $respuesta->movimiento_tipo = MovimientoTipoNegocio::create()->getMovimientoTipo($movimientoTipoId);
    $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXMovimientoTipo($movimientoTipoId);
    if (!ObjectUtil::isEmpty($respuesta->documento_tipo)) {
      $respuesta->documento_tipo_dato = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDatoXMovimientoTipo($movimientoTipoId);
    }

    $respuesta->progreso = Movimiento::create()->obtenerProgresoXMovimientoTipo($movimientoTipoId);
    $respuesta->prioridad = Movimiento::create()->obtenerPrioridadxMovimientoTipo($movimientoTipoId);
    $respuesta->responsable = PersonaNegocio::create()->obtenerComboPersonaXPersonaClaseId(-2);

    if ($movimientoTipoId == Configuraciones::MOVIMIENTO_TIPO_SOLICITUD_REQUERIMIENTO) {
      $mostrarTodasAreas = 0;
      $dataPerfil = PerfilNegocio::create()->obtenerPerfilXUsuarioId($usuarioId);
      foreach ($dataPerfil as $itemPerfil) {
        if ($itemPerfil['id'] == PerfilNegocio::PERFIL_ADMINISTRADOR_ID || $itemPerfil['id'] == PerfilNegocio::PERFIL_ADMINISTRADOR_TI_ID || $itemPerfil['id'] == PerfilNegocio::PERFIL_JEFE_LOGISTA) {
          $mostrarTodasAreas = 1;
        }
      }
      if ($mostrarTodasAreas == 1) {
        $respuesta->area = PersonaNegocio::create()->getAllArea();
        $respuesta->getarea = null;
      } else {
        $respuesta->area = PersonaNegocio::create()->getAllAreaXUsuarioId($usuarioId);
        if (ObjectUtil::isEmpty($respuesta->area)) {
          throw new WarningException("No se encontró área para el usuario en sesión");
        }
        $respuesta->getarea = $respuesta->area[0]['id'];
      }

      $respuesta->tipo_requerimiento = Movimiento::create()->obtenerTipoRequerimientoXMovimientoTipo($movimientoTipoId);
    }


    if (!ObjectUtil::isEmpty($respuesta->documento_tipo_dato)) {
      $tamanio = count($respuesta->documento_tipo_dato);

      for ($i = 0; $i < $tamanio; $i++) {
        switch ((int) $respuesta->documento_tipo_dato[$i]['tipo']) {
          case 5:
            $respuesta->documento_tipo_dato[$i]['descripcion'] = "Persona";
            break;
          case 6:
            $respuesta->documento_tipo_dato[$i]['descripcion'] = "Código";
            break;
          case 7:
            $respuesta->documento_tipo_dato[$i]['descripcion'] = "Serie";
            break;
          case 8:
            $respuesta->documento_tipo_dato[$i]['descripcion'] = "Número";
            break;
          case 9:
            $respuesta->documento_tipo_dato[$i]['descripcion'] = "Fecha de emisión";
            break;
          case 10:
            $respuesta->documento_tipo_dato[$i]['descripcion'] = "Fecha de vencimiento";
            break;
          case 11:
            $respuesta->documento_tipo_dato[$i]['descripcion'] = "Fecha de tentativa";
            break;
          case 12:
            $respuesta->documento_tipo_dato[$i]['descripcion'] = "Descripción";
            break;
          case 13:
            $respuesta->documento_tipo_dato[$i]['descripcion'] = "Comentario";
            break;
          case 14:
            $respuesta->documento_tipo_dato[$i]['descripcion'] = "Importe";
            break;
          case 17:
            $respuesta->documento_tipo_dato[$i]['descripcion'] = "Organizador Destino";
            break;
        }
      }

      // foreach ($respuesta->documento_tipo_dato as $documento) {
      //   $documento['descripcion'] = "hola";
      // }

      foreach ($respuesta->documento_tipo_dato as $documento) {
        if ($documento['tipo'] == 4) {
          $respuesta->documento_tipo_dato_lista[$contador]['id'] = $documento['id'];
          $respuesta->documento_tipo_dato_lista[$contador]['data'] = DocumentoTipoDatoListaNegocio::create()->obtenerXDocumentoTipoDato($documento['id']);
          $contador++;
        }
      }
    }

    $respuesta->persona_activa = PersonaNegocio::create()->obtenerActivas();
    return $respuesta;
  }

  public function obtenerIdXOpcion($opcionId)
  {
    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    if (ObjectUtil::isEmpty($movimientoTipo)) {
      throw new WarningException("No se encontró el movimiento asociado a esta opción");
    }
    return $movimientoTipo[0]["id"];
  }

  private function obtenerValorCampoDinamicoPorTipo($camposDinamicos, $tipo)
  {
    foreach ($camposDinamicos as $campo) {
      if ($campo["tipo"] == $tipo) {
        return $campo["valor"];
      }
    }
  }

  public function validarGenerarDocumentoAdicional($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario = NULL, $checkIgv = 1, $monedaId = null, $accionEnvio, $tipoPago, $listaPagoProgramacion, $anticiposAAplicar = null, $periodoId = null, $percepcion = null, $origen_destino = null, $importeTotalInafectas = null, $datosExtras = null, $detalleDistribucion = null, $contOperacionTipoId = null, $distribucionObligatoria = null, $igv_porcentaje = null, $dataStockReservaOk = null, $dataPostorProveedor = null, $listaPagoProgramacionPostores = null)
  {
    // validacion en caso de bienes faltantes
    $documentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);

    // SI ES TRANSFERENCIA INTERNA - DT: GUIA INTERNA
    if ($documentoTipo[0]['identificador_negocio'] == 23) {
      //VALIDAR QUE EL MOTIVO SEA  Pendiente de reposicion o Reposicion O PARA VALIDAR COPIA OBLIGATORIA
      $validarCopia = false;
      foreach ($camposDinamicos as $item) {
        if ($item['tipo'] == 4 && ($item['valor'] == Configuraciones::DTDL_GUIA_INTERNA_REPOSICION || $item['valor'] == Configuraciones::DTDL_GUIA_INTERNA_PENDIENTE_REPOSICION)) {
          $validarCopia = true;
        }
      }

      if ($validarCopia && !ObjectUtil::isEmpty($origen_destino)) {
        if (ObjectUtil::isEmpty($documentoARelacionar)) {
          if ($origen_destino == "O") {
            throw new WarningException("Debe relacionar una guía interna para poder guardar");
          } else if ($origen_destino == "D") {
            throw new WarningException("Debe relacionar una guía de recepción para poder guardar");
          }
        } else {
          $copiaAlmVirtual = false;
          foreach ($documentoARelacionar as $item) {
            if (!ObjectUtil::isEmpty($item['documentoId'])) {
              // buscando guia de recepcion en las copias.
              $dataDocCopia = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($item['documentoId']);

              if ($origen_destino == "O") {
                if ($dataDocCopia[0]['identificador_negocio'] == 23) { //Guia interna / Transferencia Interna
                  $copiaAlmVirtual = true;
                }
              } else if ($origen_destino == "D") {
                if (
                  $dataDocCopia[0]['identificador_negocio'] == 6 || //Guia de remision BH
                  $dataDocCopia[0]['identificador_negocio'] == 22
                ) { //Guia de recepcion
                  $copiaAlmVirtual = true;
                }
              }
            }
          }

          if (!$copiaAlmVirtual) {
            if ($origen_destino == "O") {
              throw new WarningException("Debe relacionar una guía interna para poder guardar");
            } else if ($origen_destino == "D") {
              throw new WarningException("Debe relacionar una guía de recepción para poder guardar");
            }
          }
        }
      }

      // throw new WarningException("PASO TODO BIEN");
    }

    if ($documentoTipo[0]["generar_documento_adicional"] == 1 && ObjectUtil::isEmpty($documentoARelacionar)) {
      $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
      $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoGenerarXMovimientoTipoId($movimientoTipo[0]["id"]);

      $dataProveedor = array();
      $dataOrganizador = array();

      $j = 0;
      foreach ($detalle as $indice => $item) {
        $cantidadFaltante = $item["cantidad"];
        // validar que no sea servicio
        $bien = BienNegocio::create()->getBien($item["bienId"]);

        if ($cantidadFaltante > 0 && $bien[0]['bien_tipo_id'] != -1) {
          $dataOrganizador[$j] = array();
          $dataP = BienNegocio::create()->obtenerBienPersonaXBienId($item["bienId"]);
          array_push($dataProveedor, $dataP);
          $dataStockBien = BienNegocio::create()->obtenerStockPorBien($item["bienId"], null);

          foreach ($dataStockBien as $ind => $itemDataStock) {
            if ($cantidadFaltante <= $itemDataStock["stock"] && $item["unidadMedidaId"] == $itemDataStock["unidad_medida_id"]) {
              array_push($dataOrganizador[$j], array('organizadorId' => $itemDataStock["organizador_id"], 'descripcion' => $itemDataStock["organizador_descripcion"]));
            }
          }

          $j++;
        }
      }

      $respuesta = new stdClass();
      $respuesta->generarDocumentoAdicional = 1;
      $respuesta->dataDocumentoTipo = $dataDocumentoTipo;
      $respuesta->dataOrganizador = $dataOrganizador;
      $respuesta->dataProveedor = $dataProveedor;
      $respuesta->dataDetalle = $detalle;

      return $respuesta;
    }

    // fin validaacion
    // validar si tipo de pago es contado
    // obtenemos valor del total
    $total = 0;
    foreach ($camposDinamicos as $item) {
      if ($item['tipo'] == 14) {
        $total = $item['valor'] * 1;
      }
    }

    if ($tipoPago == '1' && $total != 0) {
      $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);
      $empresaId = $dataDocumentoTipo[0]['empresa_id'];
      $tipoDocumento = $dataDocumentoTipo[0]['tipo'];
      $banderaGenerarPago = $dataDocumentoTipo[0]['bandera_generar_documento_pago'];

      if (($tipoDocumento == 1 || $tipoDocumento == 3 || $tipoDocumento == 4 || $tipoDocumento == 6) && (ObjectUtil::isEmpty($banderaGenerarPago) || $banderaGenerarPago == 1)) {
        if ($tipoDocumento == 1 || $tipoDocumento == 3) {
          $tipo = 2;
          $tipo2 = 3;
          $tipoCobranzaPago = 1;
        }
        if ($tipoDocumento == 4 || $tipoDocumento == 6) {
          $tipo = 5;
          $tipo2 = 6;
          $tipoCobranzaPago = 2;
        }

        $res = new stdClass();
        $res->dataDocumentoPago = PagoNegocio::create()->obtenerConfiguracionInicialNuevoDocumento($empresaId, $tipo, $tipo2, $usuarioId);
        $res->actividad = Pago::create()->obtenerActividades($tipoCobranzaPago, $empresaId);
        return $res;
      }
    }
    // fin validacion tipo pago contado.
    // ATENCION DE SOLICITUDES(QUITAR EL FALSE PARA HABILITAR ATENCIONES)
    $bandAtiende = null;
    $habilitarAtencion = false;
    if ($habilitarAtencion) {
      foreach ($documentoARelacionar as $index => $item) {
        if ($item['tipo'] == 1) {
          $dataMovBien = MovimientoBien::create()->obtenerXIdMovimiento($item['movimientoId']);
          $documentoARelacionar[$index]['detalleBien'] = $dataMovBien;

          foreach ($detalle as $indexDet => $itemDet) {
            foreach ($dataMovBien as $itemMovBien) {
              if ($itemDet['bienId'] == $itemMovBien['bien_id']) {
                if (ObjectUtil::isEmpty($detalle[$indexDet]['cantidadSol'])) {
                  $detalle[$indexDet]['cantidadSol'] = $itemMovBien['cantidad'] * 1;
                } else {
                  $detalle[$indexDet]['cantidadSol'] += $itemMovBien['cantidad'] * 1;
                }
              }
            }
          }

          $bandAtiende = false;
          $bandExterna = $bandAtiende;
          foreach ($detalle as $itemDeta) {
            if ($itemDeta['cantidad'] < $itemDeta['cantidadSol']) {
              $bandAtiende = true;
            }
          }

          if ($bandAtiende) {
            $res = new stdClass();
            $res->dataAtencionSolicitud = $documentoARelacionar;
            return $res;
          }
        }
      }
    }
    // FIN ATENCION SOLICITUDES
    // Validación de anticipos
    // En el caso que sea un documento pendiente de pago, validamos si el proveedor tiene algún anticipo por aplicar
    if ($anticiposAAplicar["validacion"] * 1 == 0 && $documentoTipo[0]["tipo"] == 4) {
      // obtenemos el id del proveedor
      $proveedorId = $this->obtenerValorCampoDinamicoPorTipo($camposDinamicos, 5);
      if (!ObjectUtil::isEmpty($proveedorId)) {
        $anticipos = DocumentoNegocio::create()->obtenerAnticiposPendientesXPersonaId($proveedorId, $monedaId);
        if (!ObjectUtil::isEmpty($anticipos)) {
          $respuesta = new stdClass();
          $respuesta->anticipos = $anticipos;
          // $respuesta->actividades = Pago::create()->obtenerActividades(2, $$anticiposAAplicar->empresaId);
          return $respuesta;
        }
      }
    }

    $respuesta = $this->guardarXAccionEnvio($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $accionEnvio, $tipoPago, $listaPagoProgramacion, $bandAtiende, $periodoId, $percepcion, $datosExtras, $contOperacionTipoId, $igv_porcentaje);
    $respuesta->bandera_historial = $documentoTipo[0]['bandera_historial'];

    if (!ObjectUtil::isEmpty($detalleDistribucion)) {
      $respuestaGuardarDistribucion = ContDistribucionContableNegocio::create()->guardarContDistribucionContable($respuesta->documentoId, $contOperacionTipoId, $detalleDistribucion, $usuarioId);
    }
    // $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    // $operacionTipoMovimiento = ContOperacionTipoNegocio::create()->obtenerContOperacionTipoXMovimientoTipoId($movimientoTipo[0]['id']);
    // if (!ObjectUtil::isEmpty($operacionTipoMovimiento)) {
    //   if (array_search($contOperacionTipoId, array_column($operacionTipoMovimiento, 'id')) === false) {
    //     throw new WarningException("La operación tipo seleccionada no pertenece al movimiento tipo.");
    //   }
    if ($distribucionObligatoria == 1) {
      $respuestaValidarDistribucion = ContDistribucionContableNegocio::create()->validarDistribucionContable($respuesta->documentoId, $detalleDistribucion, $contOperacionTipoId);
    }
    // }

    //Validacion de postores
    if(!ObjectUtil::isEmpty($dataPostorProveedor)){
      foreach($dataPostorProveedor as $itemPostor){
        $respuestaDataPostorProveedor = Documento::create()->guardar_documento_detalle($respuesta->documentoId, $itemPostor['proveedor_id'], $itemPostor['monedaId'], $itemPostor['tipoCambio'], $itemPostor['igv'], $itemPostor['tiempoEntrega'], $itemPostor['tiempo'], $itemPostor['condicionPago'], $itemPostor['sumilla'], $usuarioId);
        
        $pagoProgramacion = $listaPagoProgramacionPostores[intval($itemPostor['indice'])];
        foreach($pagoProgramacion as $itemPagoProgramacionPostores){
          $fechaPago = DateUtil::formatearCadenaACadenaBD($itemPagoProgramacionPostores[0]);
          $importePago = $itemPagoProgramacionPostores[1];
          $dias = $itemPagoProgramacionPostores[2];
          $porcentaje = $itemPagoProgramacionPostores[3];
          $glosa = $itemPagoProgramacionPostores[4];
          $res = Documento::create()->guardarDocumentoDetalleDistribucionPagos($respuestaDataPostorProveedor[0]['vout_id'], $fechaPago, $importePago, $dias, $porcentaje, $glosa, $usuarioId);
        }
      }
    }

    //Reservar stock
    if (!ObjectUtil::isEmpty($dataStockReservaOk) && $documentoTipoId == Configuraciones::REQUERIMIENTO_AREA) {
      //generar salida
      $this->guardarDocumnentoReservaEntradaSalida($usuarioId, Configuraciones::INGRESO_RESERVA_STOCK,$dataStockReservaOk, $respuesta, $periodoId, 403, 64, 1);
      //Generar ingreso a almacen Reserva
      $this->guardarDocumnentoReservaEntradaSalida($usuarioId, Configuraciones::SALIDA_RESERVA_STOCK,$dataStockReservaOk, $respuesta, $periodoId, 404, 78, 2);

    }

    //Se usa para Servicio
    if ($documentoTipoId == Configuraciones::COTIZACION_SERVICIO) {
      //generamos OS
      $this->guardarDocumentoCotizacion($camposDinamicos, $usuarioId, Configuraciones::ORDEN_SERVICIO,$detalle, $respuesta, $periodoId, 401, 3, $listaPagoProgramacion, null, $monedaId);
    }

    $this->guardarAnticipos($respuesta, $anticiposAAplicar, $usuarioId, $camposDinamicos, $monedaId);

    $this->docInafectas = (!ObjectUtil::isEmpty($importeTotalInafectas)) ? $importeTotalInafectas : 0.0;
    // GENERAR DOCUMENTO ELECTRONICO - SUNAT
    $dataEmpresa = EmpresaNegocio::create()->obtenerEmpresaXDocumentoId($respuesta->documentoId);
    if ($dataEmpresa[0]['efactura'] == 1 || $dataEmpresa[0]['efactura'] == 2) {
      $resEfact = $this->generarDocumentoElectronico($respuesta->documentoId, $documentoTipo[0]['identificador_negocio']);
      $respuesta->resEfact = $resEfact;
    }

    return $respuesta;
  }

  public function generarDocumentoElectronico($documentoId, $identificadorNegocio, $soloPDF = 0, $tipoUso = 1, $efactura = 1)
  {
    // soloPDF = 1 -> solo generar PDF
    // tipoUso = 1 -> por sistema
    // tipoUso = 2 -> por script

    if (ObjectUtil::isEmpty($soloPDF)) {
      $soloPDF = 0;
    }

    if (ObjectUtil::isEmpty($tipoUso)) {
      $tipoUso = 1;
    }

    $esDocElectronico = 0;
    $respDocElectronico = null;
    $dataEmpresa = EmpresaNegocio::create()->obtenerEmpresaXDocumentoId($documentoId);
    if ($dataEmpresa[0]['efactura'] == 1) {
      switch ($identificadorNegocio * 1) {
        case DocumentoTipoNegocio::IN_FACTURA_VENTA:
          $respDocElectronico = $this->generarFacturaElectronica($documentoId, $soloPDF, $tipoUso);
          $esDocElectronico = 1;
          break;
        case DocumentoTipoNegocio::IN_BOLETA_VENTA:
          $respDocElectronico = $this->generarBoletaElectronica($documentoId, $soloPDF, $tipoUso);
          $esDocElectronico = 1;
          break;
        case DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA:
          $respDocElectronico = $this->generarNotaCreditoElectronica($documentoId, $soloPDF, $tipoUso);
          $esDocElectronico = 1;
          break;
        case DocumentoTipoNegocio::IN_NOTA_DEBITO_COMPRA:
          $respDocElectronico = $this->generarNotaDebitoElectronica($documentoId, $soloPDF, $tipoUso);
          $esDocElectronico = 1;
          break;
      }
    } else if ($dataEmpresa[0]['efactura'] == 2) {
      switch ($identificadorNegocio * 1) {
        case DocumentoTipoNegocio::IN_FACTURA_VENTA:
          $respDocElectronico = $this->generarFacturaElectronicaNubefact($documentoId, $soloPDF, $tipoUso);
          $esDocElectronico = 1;
          break;
        case DocumentoTipoNegocio::IN_BOLETA_VENTA:
          $respDocElectronico = $this->generarBoletaElectronicaNubefact($documentoId, $soloPDF, $tipoUso);
          $esDocElectronico = 1;
          break;
        case DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA:
          $respDocElectronico = $this->generarNotaCreditoElectronicaNubefact($documentoId, $soloPDF, $tipoUso);
          $esDocElectronico = 1;
          break;
        case DocumentoTipoNegocio::IN_NOTA_DEBITO_COMPRA:
          $respDocElectronico = $this->generarNotaDebitoElectronicaNubefact($documentoId, $soloPDF, $tipoUso);
          $esDocElectronico = 1;
          break;
        case DocumentoTipoNegocio::IN_GUIA_REMISION:
          $respDocElectronico = $this->generarGuiaRemisionElectronicaNubefact($documentoId, $soloPDF, $tipoUso);
          $esDocElectronico = 1;
          break;
      }
    }

    $respuesta = new stdClass();
    $respuesta->esDocElectronico = $esDocElectronico;
    $respuesta->respDocElectronico = $respDocElectronico;

    return $respuesta;
  }

  public function validarResultadoEfactura($resultado)
  {
    $mensaje = "Resultado EFACT: " . $resultado;

    if (ObjectUtil::isEmpty($resultado)) {
      throw new WarningException("Se generó un error al registrar el documento electrónico.");
    } else if (strpos($mensaje, '[Cod: IMA01]') === false) {
      throw new WarningException($mensaje);
    }

    $this->setMensajeEmergente($mensaje);
    //  echo $mensaje;
  }

  public function validarResultadoEfacturaDocumento($resultado, $documentoId, $tipoUso)
  {
    $mensaje = $resultado;
    $urlPDF = '';
    $nombrePDF = '';

    switch (true) {
      // EXCEPCIONES DE LA WS - EFAC
      case strpos($mensaje, '[Cod: IMAEX') !== false:
        $tipoMensaje = DocumentoTipoNegocio::EFACT_ERROR_DESCONOCIDO;
        $mensaje = "Resultado EFACT: " . $resultado;
        // throw new WarningException("Resultado EFACT: " . $resultado);
        break;
      // REGISTRO EN LA WS - PENDIENTE DE ENVIO A SUNAT U OSE
      case strpos($mensaje, '[Cod: IMA00]') !== false:
        $tipoMensaje = DocumentoTipoNegocio::EFACT_PENDIENTE_ENVIO;
        $mensaje = "Resultado EFACT: " . $resultado;
        break;
      //REGISTRO CORRECTO
      case strpos($mensaje, '[Cod: IMA01]') !== false:
        $tipoMensaje = DocumentoTipoNegocio::EFACT_CORRECTO;
        $mensaje = "Resultado EFACT: " . $resultado;
        break;
      //ERROR CONTROLADO QUE GENERA EXCEPCION
      case strpos($mensaje, '[Cod: IMA02]') !== false:
        $tipoMensaje = DocumentoTipoNegocio::EFACT_ERROR_CONTROLADO;
        $mensaje = "Resultado EFACT (ERROR): " . $resultado;
        break;
      //ERROR CONTROLADO QUE GENERA RECHAZO EN SUNAT
      case strpos($mensaje, '[Cod: IMA03]') !== false:
        $tipoMensaje = DocumentoTipoNegocio::EFACT_ERROR_RECHAZADO;
        $mensaje = "Resultado EFACT (ERROR): " . $resultado;
        //CAMBIAR ESTADO ANULADO :
        $resDocEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 2, 1);
        break;
      //ERROR DESCONOCIDO
      case strpos($mensaje, '[Cod: IMA04]') !== false:
        $tipoMensaje = DocumentoTipoNegocio::EFACT_ERROR_DESCONOCIDO;
        $mensaje = "Se generó un error al registrar el documento electrónico. Resultado EFACT: " . $resultado;
        break;

      default:
        throw new WarningException("Resultado EFACT (ERROR): " . $resultado);
    }

    // if ($tipoMensaje == DocumentoTipoNegocio::EFACT_ERROR_CONTROLADO && $tipoUso == 1) {
    //   throw new WarningException($mensaje);
    // }

    $resEstadoRegistro = DocumentoNegocio::create()->actualizarEfactEstadoRegistro($documentoId, $tipoMensaje, $resultado); // guardar $resultado
    if (strpos($resultado, '[FN:') > -1) {
      $indInicial = strpos($resultado, '[FN:');
      $indFinal = strpos($resultado, '.pdf]');

      $nombrePDF = substr($resultado, ($indInicial + 5), ($indFinal - $indInicial - 1));

      if (!ObjectUtil::isEmpty($nombrePDF)) {
        $urlPDF = Configuraciones::EFACT_CONTENEDOR_PDF . $nombrePDF;

        $resActNombrePDF = DocumentoNegocio::create()->actualizarEfactPdfNombre($documentoId, $nombrePDF);
      } else {
        $urlPDF = '';
      }
    }

    if ($tipoMensaje != DocumentoTipoNegocio::EFACT_CORRECTO) {
      $mensaje = "Se registró correctamente en el SGI, pero se ha presentado un problema en el envió a SUNAT<br>Detalle: " . $mensaje;
      $titulo = ", pendiente de emisión a SUNAT";
    }

    $respEfact = new stdClass();
    $respEfact->tipoMensaje = $tipoMensaje; //[Cod: IMAEX05] |  Error la generar el documento : Comprobante: F001-000349 presenta el error: Se ha especificado un tipo de proveedor no válido.
    $respEfact->mensaje = $mensaje;
    $respEfact->urlPDF = $urlPDF;
    $respEfact->nombrePDF = $nombrePDF;
    $respEfact->titulo = $titulo; //titulo que en caso de reenvio de comprobante  no será nulo
    return $respEfact;
  }

  public function generarFacturaElectronica($documentoId, $soloPDF, $tipoUso)
  {
    // Obtenemos Datos
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }

    $empresa = EmpresaNegocio::create()->obtenerEmpresaXId($documento[0]['empresa_id']);

    $ubigeoEmpresa = PersonaNegocio::create()->obtenerUbigeoXId($empresa[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeoEmpresa)) {
      throw new WarningException("No se especificó el ubigeo del emisor");
    }

    $persona = PersonaNegocio::create()->obtenerPersonaXId($documento[0]["persona_id"]);
    if (ObjectUtil::isEmpty($persona)) {
      throw new WarningException("No se encontró a la persona del documento");
    }
    $ubigeo = PersonaNegocio::create()->obtenerUbigeoXId($documento[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeo)) {
      throw new WarningException("No se especificó el ubigeo del receptor");
    }
    $enLetras = new EnLetras();
    $importeLetras = $enLetras->ValorEnLetras($documento[0]["total"], $documento[0]['moneda_id']);

    $comprobanteElectronico = new stdClass();
    $comprobanteElectronico->emisorNroDocumento = $empresa[0]['ruc'];
    $comprobanteElectronico->emisorTipoDocumento = $empresa[0]['sunat_tipo_documento'];
    $comprobanteElectronico->emisorUbigeo = $ubigeoEmpresa[0]['ubigeo_codigo'];
    $comprobanteElectronico->emisorDireccion = $empresa[0]['direccion_fe'];
    $comprobanteElectronico->emisorUrbanizacion = $empresa[0]['urbanizacion'];
    $comprobanteElectronico->emisorDepartamento = $ubigeoEmpresa[0]['ubigeo_dep'];
    $comprobanteElectronico->emisorProvincia = $ubigeoEmpresa[0]['ubigeo_prov'];
    $comprobanteElectronico->emisorDistrito = $ubigeoEmpresa[0]['ubigeo_dist'];
    $comprobanteElectronico->emisorNombreLegal = $empresa[0]['razon_social'];
    //        $comprobanteElectronico->emisorNombreLegal = 'DISTRIBUIDORA FARMACOS DEL NORTE SAC';
    //        $comprobanteElectronico->emisorNombreLegal = 'BHDT';
    $comprobanteElectronico->emisorNombreComercial = $empresa[0]['nombre_comercial'];
    $comprobanteElectronico->emisorNroTelefono = $empresa[0]['telefono'];

    // receptor
    $comprobanteElectronico->receptorNroDocumento = $persona[0]["codigo_identificacion"];
    $comprobanteElectronico->receptorTipoDocumento = $persona[0]["sunat_tipo_documento"];
    $comprobanteElectronico->receptorNombreLegal = $persona[0]["persona_nombre_completo"];
    $comprobanteElectronico->receptorUbigeo = $ubigeo[0]["ubigeo_codigo"];
    $comprobanteElectronico->receptorDireccion = $documento[0]["direccion"];
    $comprobanteElectronico->receptorUrbanizacion = '';
    $comprobanteElectronico->receptorDepartamento = $ubigeo[0]["ubigeo_dep"];
    $comprobanteElectronico->receptorProvincia = $ubigeo[0]["ubigeo_prov"];
    $comprobanteElectronico->receptorDistrito = $ubigeo[0]["ubigeo_dist"];

    $correos = PersonaNegocio::create()->obtenerCorreosEFACT();
    $comprobanteElectronico->receptorEmail = str_replace(';', ',', $persona[0]["email"] . "," . $correos[0]["correos"] . "," . Configuraciones::EFACT_CORREO);
    // $comprobanteElectronico->receptorEmail = 'nleon';
    // factura
    // VALIDA SERIE
    if ($documento[0]["serie"][0] != 'F') {
      throw new WarningException("La serie del documento debe empezar con F");
    }

    $comprobanteElectronico->docSerie = $documento[0]["serie"];
    $comprobanteElectronico->docNumero = $documento[0]["numero"];
    $comprobanteElectronico->docFechaEmision = substr($documento[0]["fecha_emision"], 0, 10);
    $comprobanteElectronico->docMoneda = $documento[0]["sunat_moneda"];
    $comprobanteElectronico->docMontoEnLetras = $importeLetras; // $Veintiun Mil Doscientos Veinte Con 96/100 SolesY CINCO SOLES CON 0/100';
    $comprobanteElectronico->docTotalIgv = $documento[0]["igv"] * 1.0;
    $comprobanteElectronico->docTotalVenta = $documento[0]["total"] * 1.0;
    $comprobanteElectronico->docGravadas = $documento[0]["subtotal"] * 1.0;
    $comprobanteElectronico->docExoneradas = 0.0;
    // $comprobanteElectronico->docInafectas = 0.0;
    $montoGratuito = (!ObjectUtil::isEmpty($this->docInafectas)) ? $this->docInafectas * 1 : 0.0;
    $comprobanteElectronico->docInafectas = 0.0;
    $comprobanteElectronico->docGratuitas = $montoGratuito;
    $comprobanteElectronico->docDescuentoGlobal = 0.0;
    $comprobanteElectronico->icbper = 0.0;

    // Detalle
    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($documento[0]["movimiento_id"]);
    foreach ($documentoDetalle as $index => $fila) {
      $tipoPrecio = "01";
      $tipoAfectacion = "10";
      $valorMonetario = $fila['valor_monetario'] * 1;
      $valorMonetarioRef = $fila['valor_monetario'] * 1;
      if ($fila['incluye_igv'] == 1) {
        $valorMonetario = $fila['valor_monetario'] / 1.18;
      } else {
        $valorMonetarioRef = $fila['valor_monetario'] * (1.18);
      }
      $precio = $valorMonetario;
      $precioReferencial = $valorMonetarioRef;
      $totalItem = $precio * $fila['cantidad'];
      $totalImpuesto = $precio * $fila['cantidad'] * 0.18;
      if ($montoGratuito > 0) {
        $tipoAfectacion = "21";
        $tipoPrecio = "02";
        $precio = 0;
        $totalImpuesto = 0;
        $precioReferencial = $valorMonetario;
        $totalItem = $precioReferencial * $fila['cantidad'];
      }

      $items[$index][0] = $index + 1;
      $items[$index][1] = $fila['cantidad'] * 1;
      $items[$index][2] = $precio;
      $items[$index][3] = $precioReferencial; //Precio refencial
      $items[$index][4] = $tipoPrecio; //Tipos de precio
      $items[$index][5] = $fila['bien_codigo'];
      // $items[$index][6] = $fila['bien_descripcion'];
      $items[$index][6] = (!ObjectUtil::isEmpty($fila['bien_descripcion_editada'])) ? $fila['bien_descripcion_editada'] : $fila['bien_descripcion'];
      $items[$index][7] = $fila['sunat_unidad_medida'];
      $items[$index][8] = $totalImpuesto; //Impuesto
      $items[$index][9] = $tipoAfectacion; //Tipo de impuesto
      $items[$index][10] = $totalItem;
      $items[$index][11] = 0; //Descuento
      $items[$index][12] = Configuraciones::IGV_PORCENTAJE; //porcentaje IGV
      $items[$index][13] = ''; //codigo sunat para ws
      $items[$index][14] = 0.0; //ICBPER
    }

    $comprobanteElectronico->items = $items;

    // $comprobanteElectronico->anticipos = $anticipos;
    // OBTENEMOS LOS DOCUMENTOS RELACIONADOS
    // guias de remision
    // $docRelacion = DocumentoNegocio::create()->obtenerDocumentoRelacionadoActivoXDocumentoId($documentoId);
    // $i = 0;
    // foreach ($docRelacion as $index => $guias) {
    //   switch ($guias['identificador_negocio_relacion'] * 1) {
    //     case 6: {
    //         $guiasRemision[$i][0] = $guias["serie_relacion"] . '-' . $guias["numero_relacion"];
    //         $guiasRemision[$i][1] = $guias["fecha_emision_relacion"];
    //         $guiasRemision[$i][2] = $guias["sunat_tipo_doc_rel"];
    //         $i++;
    //       }
    //   }
    // }
    // $comprobanteElectronico->guiasRemision = $guiasRemision;
    // orden de compra
    $docOrdenCompra = DocumentoNegocio::create()->obtenerDocumentoRelacionadoImpresion($documentoId);
    foreach ($docOrdenCompra as $index => $ordenCompra) {
      switch ($ordenCompra['identificador_negocio'] * 1) {
        case 2: {
            $orden = $ordenCompra['serie_numero_original'];
            if (!ObjectUtil::isEmpty($orden)) {
              $ordenArray = explode("-", $orden);
              if (count($ordenArray) > 1 && ObjectUtil::isEmpty(trim($ordenArray[0]))) {
                $orden = trim($ordenArray[1]);
              } elseif (count($ordenArray) == 1) {
                $orden = trim($ordenArray[0]);
              }
            }
          }
      }
    }

    $orden = null;
    $comprobanteElectronico->ordenCompra = $orden;
    $tipoPago = ($documento[0]["tipo_pago"] * 1);

    $afectoDetraccionRetencion = ($documento[0]["afecto_detraccion_retencion"] * 1);
    $porcentajeDetraccionRetencion = ($documento[0]["porcentaje_afecto"] * 1);
    $montoRetencionDetraccion = ($documento[0]["monto_detraccion_retencion"] * 1);
    $codigoDetraccion = $documento[0]["detraccion_codigo"];

    $datoAdicional = array();

    // OBTENER DATOS ADICIONALES
    $datoAdicional[] = array('Fecha vencimiento', $documento[0]['fecha_vencimiento']);
    $datoAdicional[] = array('Forma pago', $tipoPago == 1 ? "Contado" : "Crédito");
    $formaPago = array();

    if ($tipoPago == 2) {
      if ($afectoDetraccionRetencion == 1) {
        $formaPago[] = array("Detraccion" . $codigoDetraccion, $montoRetencionDetraccion, "", $porcentajeDetraccionRetencion * 0.01, "00741486970");
        // $formaPago[] = array("Detraccion" . $codigoDetraccion, $montoRetencionDetraccion * 1.0, "", $porcentajeDetraccionRetencion * 1.0, "00741486970");
        $afectoDescripcion = "DETRACCIÓN / OPERACION SUJETA AL SPOT D.L 940 - " . number_format($porcentajeDetraccionRetencion, 2) . "% ( " . $comprobanteElectronico->docMoneda . " " . number_format($montoRetencionDetraccion, 2) . ") CTA BN: 00741486970";
        $datoAdicional[] = array('Afecto a', $afectoDescripcion);
        $comprobanteElectronico->tipoOperacion = "1001"; // Tipo de operación afecto a detracción.
      } elseif ($afectoDetraccionRetencion == 2) {
        $formaPago[] = array("Retencion", $montoRetencionDetraccion * 1.0, "", $porcentajeDetraccionRetencion * 0.01);
        $afectoDescripcion = "RETENCIÓN - " . number_format($porcentajeDetraccionRetencion, 2) . "% ( " . $comprobanteElectronico->docMoneda . " " . number_format($montoRetencionDetraccion, 2) . ")";
        $datoAdicional[] = array('Afecto a', $afectoDescripcion);
      }

      $montoNetoPago = round($comprobanteElectronico->docTotalVenta - ($montoRetencionDetraccion * 1), 2);
      $formaPago[] = array("Credito", $montoNetoPago, "");
      $formaPagoDetalle = PagoNegocio::create()->obtenerPagoProgramacionXDocumentoId($documentoId);
      if (ObjectUtil::isEmpty($formaPagoDetalle)) {
        throw new WarningException("Se requiere de la programación de pago de la factura.");
      }

      $arrayFechaVencimiento = array();
      foreach ($formaPagoDetalle as $indexFormaPago => $itemFormaPago) {
        $arrayFechaVencimiento[] = substr($itemFormaPago['fecha_pago'], 0, 10);
        $formaPago[] = array("Cuota" . str_pad(($indexFormaPago + 1), 3, "0", STR_PAD_LEFT), $itemFormaPago['importe'] * 1.0, substr($itemFormaPago['fecha_pago'], 0, 10));
      }
      $fechaMaximaCuota = date("Y-m-d", max(array_map('strtotime', $arrayFechaVencimiento)));
      if (substr($documento[0]['fecha_vencimiento'], 0, 10) != $fechaMaximaCuota) {
        throw new WarningException("La fecha de vencimiento de la factura (" . substr($documento[0]['fecha_vencimiento'], 0, 10) . ") debe ser igual a la última cuota de la programación de pagos ($fechaMaximaCuota)");
      }
    } elseif ($tipoPago == 1) {
      $formaPago[] = array("Contado", 0.0, "");
    } else {
      throw new WarningException("No se identifica la forma de pago para esta factura.");
    }

    if (!ObjectUtil::isEmpty($documento[0]['comentario'])) {
      $datoAdicional[] = array('Observación', $documento[0]['comentario']);
    }

    $comprobanteElectronico->formaPago = $formaPago;
    $comprobanteElectronico->extras = $datoAdicional;
    //FIN DATOS ADICIONALES

    $comprobanteElectronico->usuarioSunatSOL = $empresa[0]['usuario_sunat_sol'];
    $comprobanteElectronico->claveSunatSOL = $empresa[0]['clave_sunat_sol'];
    var_dump($comprobanteElectronico);
    $client = self::conexionEFAC();
    try {

      if ($soloPDF == 1) {
        $resultado = $client->procesarFacturaPDF((array) $comprobanteElectronico)->procesarFacturaPDFResult;
      } else if ($soloPDF == 2) {
        $comprobanteElectronico->tipoDocumento = $documento[0]["documento_tipo_sunat_codigo"];
        $resultado = $client->reenviarComprobanteElectronico((array) $comprobanteElectronico)->reenviarComprobanteElectronicoResult;
      } else {
        $resultado = $client->procesarFactura((array) $comprobanteElectronico)->procesarFacturaResult;
      }

      // DESCOMENTAR PARA PROBAR RESPUESTAS
      // $resultado = "Resultado EFACT: [Cod: IMA01] | La Factura numero F001-000052, ha sido aceptada | [FN: 2018101517134720600143361-01-F001-000049.pdf]";
      // $resultado = "Resultado EFACT: [Cod: IMA02] | ERROR SUNAT .. ... -_-";
    } catch (Exception $e) {
      $resultado = $e->getMessage();
    }

    $resEfact = $this->validarResultadoEfacturaDocumento($resultado, $documentoId, $tipoUso);
    // var_dump($comprobanteElectronico);
    return $resEfact;
  }

  public function generarBoletaElectronica($documentoId, $soloPDF, $tipoUso)
  {
    // Obtenemos Datos
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }

    $empresa = EmpresaNegocio::create()->obtenerEmpresaXId($documento[0]['empresa_id']);

    $ubigeoEmpresa = PersonaNegocio::create()->obtenerUbigeoXId($empresa[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeoEmpresa)) {
      throw new WarningException("No se especificó el ubigeo del emisor");
    }

    $persona = PersonaNegocio::create()->obtenerPersonaXId($documento[0]["persona_id"]);
    if (ObjectUtil::isEmpty($persona)) {
      throw new WarningException("No se encontró a la persona del documento");
    }
    $ubigeo = PersonaNegocio::create()->obtenerUbigeoXId($documento[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeo)) {
      throw new WarningException("No se especificó el ubigeo del receptor");
    }
    $enLetras = new EnLetras();
    $importeLetras = $enLetras->ValorEnLetras($documento[0]["total"], $documento[0]['moneda_id']);

    $comprobanteElectronico = new stdClass();
    $comprobanteElectronico->emisorNroDocumento = $empresa[0]['ruc'];
    $comprobanteElectronico->emisorTipoDocumento = $empresa[0]['sunat_tipo_documento'];
    $comprobanteElectronico->emisorUbigeo = $ubigeoEmpresa[0]['ubigeo_codigo'];
    $comprobanteElectronico->emisorDireccion = $empresa[0]['direccion_fe'];
    $comprobanteElectronico->emisorUrbanizacion = $empresa[0]['urbanizacion'];
    $comprobanteElectronico->emisorDepartamento = $ubigeoEmpresa[0]['ubigeo_dep'];
    $comprobanteElectronico->emisorProvincia = $ubigeoEmpresa[0]['ubigeo_prov'];
    $comprobanteElectronico->emisorDistrito = $ubigeoEmpresa[0]['ubigeo_dist'];
    $comprobanteElectronico->emisorNombreLegal = $empresa[0]['razon_social'];
    $comprobanteElectronico->emisorNombreComercial = $empresa[0]['nombre_comercial'];
    $comprobanteElectronico->emisorNroTelefono = $empresa[0]['telefono'];

    // receptor
    $comprobanteElectronico->receptorNroDocumento = $persona[0]["codigo_identificacion"];
    $comprobanteElectronico->receptorTipoDocumento = $persona[0]["sunat_tipo_documento"];
    $comprobanteElectronico->receptorNombreLegal = $persona[0]["persona_nombre_completo"];
    $comprobanteElectronico->receptorUbigeo = $ubigeo[0]["ubigeo_codigo"];
    $comprobanteElectronico->receptorDireccion = $documento[0]["direccion"];
    $comprobanteElectronico->receptorUrbanizacion = '';
    $comprobanteElectronico->receptorDepartamento = $ubigeo[0]["ubigeo_dep"];
    $comprobanteElectronico->receptorProvincia = $ubigeo[0]["ubigeo_prov"];
    $comprobanteElectronico->receptorDistrito = $ubigeo[0]["ubigeo_dist"];

    $correos = PersonaNegocio::create()->obtenerCorreosEFACT();
    $comprobanteElectronico->receptorEmail = str_replace(';', ',', $persona[0]["email"] . "," . $correos[0]["correos"] . "," . Configuraciones::EFACT_CORREO);
    // $comprobanteElectronico->receptorEmail = 'nleon';
    // factura
    //VALIDA SERIE
    if ($documento[0]["serie"][0] != 'B') {
      throw new WarningException("La serie del documento debe empezar con B");
    }

    $montoTotal = $documento[0]["total"] * 1;
    $montoAfecto = round($montoTotal / 1.18, 2);
    $montoIgv = round($montoTotal - $montoAfecto, 2);
    $montoGratuito = 0;
    if ($documento[0]["movimiento_tipo_codigo"] == "18") {
      $montoGratuito = $documento[0]["total"] * 1.0;
      //Venta gratuita obsequio
      if (($this->docInafectas * 1) > 0) {
        $montoGratuito = $this->docInafectas;
      }
      $montoAfecto = 0;
      $montoIgv = 0;
      $montoTotal = 0;
    }

    $comprobanteElectronico->docSerie = $documento[0]["serie"];
    $comprobanteElectronico->docNumero = $documento[0]["numero"];
    $comprobanteElectronico->docFechaEmision = substr($documento[0]["fecha_emision"], 0, 10);
    $comprobanteElectronico->docMoneda = $documento[0]["sunat_moneda"];
    $comprobanteElectronico->docMontoEnLetras = $importeLetras; //'TRES MIL OCHOCIENTOS TREINTA Y CINCO SOLES CON 0/100';
    $comprobanteElectronico->docTotalIgv = $montoIgv;
    $comprobanteElectronico->docTotalVenta = $montoTotal;
    $comprobanteElectronico->docGravadas = $montoAfecto;
    $comprobanteElectronico->docExoneradas = 0.0;
    $comprobanteElectronico->docInafectas = 0.0;
    $comprobanteElectronico->docGratuitas = $montoGratuito;
    $comprobanteElectronico->docDescuentoGlobal = 0.0;
    $comprobanteElectronico->icbper = 0.0;

    // Detalle
    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($documento[0]["movimiento_id"]);
    foreach ($documentoDetalle as $index => $fila) {
      $porcentajeIgv = Configuraciones::IGV_PORCENTAJE;
      $tipoPrecio = "01";
      $tipoAfectacion = "10";
      $valorMonetario = $fila['valor_monetario'] * 1;
      $valorMonetarioRef = $fila['valor_monetario'] * 1;
      if ($fila['incluye_igv'] == 1) {
        $valorMonetario = $fila['valor_monetario'] / 1.18;
      } else {
        $valorMonetarioRef = $fila['valor_monetario'] * (1.18);
      }
      $precio = $valorMonetario;
      $precioReferencial = $valorMonetarioRef;
      $totalItem = $precio * $fila['cantidad'];
      $totalImpuesto = $precio * $fila['cantidad'] * 0.18;

      if ($montoGratuito > 0) {
        if ($fila['incluye_igv'] * 1 == 1 && ($documento[0]["igv"] * 1) > 0) {
          $valorMonetario = $fila['valor_monetario'] / (1 + ($porcentajeIgv / 100));
        } else {
          $valorMonetario = $fila['valor_monetario'] * 1;
        }

        $precio = 0;
        $precioReferencial = $valorMonetario;
        $totalImpuesto = 0;
        $tipoAfectacion = "21";
        $tipoPrecio = "02";
        $totalItem = $precioReferencial * $fila['cantidad'];
      }

      $items[$index][0] = $index + 1;
      $items[$index][1] = $fila['cantidad'] * 1;
      $items[$index][2] = $precio;
      $items[$index][3] = $precioReferencial; //Precio refencial
      $items[$index][4] = $tipoPrecio; //Tipos de precio
      $items[$index][5] = $fila['bien_codigo'];
      // $items[$index][6] = $fila['bien_descripcion'];
      $items[$index][6] = (!ObjectUtil::isEmpty($fila['bien_descripcion_editada'])) ? $fila['bien_descripcion_editada'] : $fila['bien_descripcion'];
      $items[$index][7] = $fila['sunat_unidad_medida'];
      $items[$index][8] = $totalImpuesto; //Impuesto
      $items[$index][9] = $tipoAfectacion; //Tipo de impuesto
      $items[$index][10] = $totalItem;
      $items[$index][11] = 0; //Descuento
      $items[$index][12] = Configuraciones::IGV_PORCENTAJE; //porcentaje IGV
      $items[$index][13] = ''; //codigo sunat para ws
      $items[$index][14] = 0.0; //ICBPER
    }

    $comprobanteElectronico->items = $items;
    $comprobanteElectronico->usuarioSunatSOL = $empresa[0]['usuario_sunat_sol'];
    $comprobanteElectronico->claveSunatSOL = $empresa[0]['clave_sunat_sol'];

    $client = self::conexionEFAC();

    try {
      if ($soloPDF == 1) {
        $resultado = $client->procesarBoletaPDF((array) $comprobanteElectronico)->procesarBoletaPDFResult;
      } else if ($soloPDF == 2) {
        $comprobanteElectronico->tipoDocumento = $documento[0]["documento_tipo_sunat_codigo"];
        $resultado = $client->reenviarComprobanteElectronico((array) $comprobanteElectronico)->reenviarComprobanteElectronicoResult;
      } else {
        $resultado = $client->procesarBoleta((array) $comprobanteElectronico)->procesarBoletaResult;
      }
    } catch (Exception $e) {
      $resultado = $e->getMessage();
    }

    $resEfact = $this->validarResultadoEfacturaDocumento($resultado, $documentoId, $tipoUso);

    // var_dump($comprobanteElectronico);
    return $resEfact;
  }

  public function generarNotaCreditoElectronica($documentoId, $soloPDF, $tipoUso)
  {
    // Obtenemos Datos
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }

    $empresa = EmpresaNegocio::create()->obtenerEmpresaXId($documento[0]['empresa_id']);

    $ubigeoEmpresa = PersonaNegocio::create()->obtenerUbigeoXId($empresa[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeoEmpresa)) {
      throw new WarningException("No se especificó el ubigeo del emisor");
    }

    $persona = PersonaNegocio::create()->obtenerPersonaXId($documento[0]["persona_id"]);
    if (ObjectUtil::isEmpty($persona)) {
      throw new WarningException("No se encontró a la persona del documento");
    }
    $ubigeo = PersonaNegocio::create()->obtenerUbigeoXId($documento[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeo)) {
      throw new WarningException("No se especificó el ubigeo del receptor");
    }
    $enLetras = new EnLetras();
    //$importeLetras = $enLetras->ValorEnLetras($documento[0]["total"], $documento[0]['moneda_id']);
    $importeTotalLetras = $documento[0]["total"];
    if ($documento[0]["motivo_codigo"] == 13) {
      $importeTotalLetras = 0.0;
    }
    $importeLetras = $enLetras->ValorEnLetras($importeTotalLetras, $documento[0]['moneda_id']);

    $comprobanteElectronico = new stdClass();
    $comprobanteElectronico->emisorNroDocumento = $empresa[0]['ruc'];
    $comprobanteElectronico->emisorTipoDocumento = $empresa[0]['sunat_tipo_documento'];
    $comprobanteElectronico->emisorUbigeo = $ubigeoEmpresa[0]['ubigeo_codigo'];
    $comprobanteElectronico->emisorDireccion = $empresa[0]['direccion_fe'];
    $comprobanteElectronico->emisorUrbanizacion = $empresa[0]['urbanizacion'];
    $comprobanteElectronico->emisorDepartamento = $ubigeoEmpresa[0]['ubigeo_dep'];
    $comprobanteElectronico->emisorProvincia = $ubigeoEmpresa[0]['ubigeo_prov'];
    $comprobanteElectronico->emisorDistrito = $ubigeoEmpresa[0]['ubigeo_dist'];
    $comprobanteElectronico->emisorNombreLegal = $empresa[0]['razon_social'];
    $comprobanteElectronico->emisorNombreComercial = $empresa[0]['nombre_comercial'];
    $comprobanteElectronico->emisorNroTelefono = $empresa[0]['telefono'];

    // receptor
    $comprobanteElectronico->receptorNroDocumento = $persona[0]["codigo_identificacion"];
    $comprobanteElectronico->receptorTipoDocumento = $persona[0]["sunat_tipo_documento"];
    $comprobanteElectronico->receptorNombreLegal = $persona[0]["persona_nombre_completo"];
    $comprobanteElectronico->receptorUbigeo = $ubigeo[0]["ubigeo_codigo"];
    $comprobanteElectronico->receptorDireccion = $documento[0]["direccion"];
    $comprobanteElectronico->receptorUrbanizacion = '';
    $comprobanteElectronico->receptorDepartamento = $ubigeo[0]["ubigeo_dep"];
    $comprobanteElectronico->receptorProvincia = $ubigeo[0]["ubigeo_prov"];
    $comprobanteElectronico->receptorDistrito = $ubigeo[0]["ubigeo_dist"];

    $correos = PersonaNegocio::create()->obtenerCorreosEFACT();
    $comprobanteElectronico->receptorEmail = str_replace(';', ',', $persona[0]["email"] . "," . $correos[0]["correos"] . "," . Configuraciones::EFACT_CORREO);

    // $comprobanteElectronico->receptorEmail = 'nleon';
    // factura
    // $serieNum =  DocumentoNegocio::create()->obtenerSerieNumeroXDocumentoId($documentoId);

    //VALIDA SERIE
    // if ($serieNum[0]["serie"][0] != 'F' && $serieNum[0]["serie"][0] != 'B') {
    //   throw new WarningException("La serie del documento debe empezar con F o B");
    // }
    // factura
    // VALIDA SERIE
    if ($documento[0]["serie"][0] != 'F' && $documento[0]["serie"][0] != 'B') {
      throw new WarningException("La serie del documento debe empezar con F o B");
    }

    // $comprobanteElectronico->docSerie = $serieNum[0]["serie"];
    // $comprobanteElectronico->docNumero = $serieNum[0]["numero"];
    $comprobanteElectronico->docSerie = $documento[0]["serie"];
    $comprobanteElectronico->docNumero = $documento[0]["numero"];
    $comprobanteElectronico->docFechaEmision = substr($documento[0]["fecha_emision"], 0, 10);
    $comprobanteElectronico->docMoneda = $documento[0]["sunat_moneda"];
    $comprobanteElectronico->docMontoEnLetras = $importeLetras; //'TRES MIL OCHOCIENTOS TREINTA Y CINCO SOLES CON 0/100';
    // $comprobanteElectronico->docTotalIgv = $documento[0]["igv"] * 1.0;
    // $comprobanteElectronico->docTotalVenta = $documento[0]["total"] * 1;
    // $comprobanteElectronico->docGravadas = $documento[0]["subtotal"] * 1.0;
    $docTotalIgv = $documento[0]["total"] * 1 - $documento[0]["total"] / 1.18;
    $docTotalVenta = $documento[0]["total"] * 1;
    $docGravadas = $documento[0]["total"] / 1.18;
    if ($documento[0]["motivo_codigo"] == 13) { // Cambio de importes para la NC tipo 13
      $docTotalIgv = 0;
      $docTotalVenta = 0;
      $docGravadas = 0;
    }
    $comprobanteElectronico->docTotalIgv = $docTotalIgv;
    $comprobanteElectronico->docTotalVenta = $docTotalVenta;
    $comprobanteElectronico->docGravadas = $docGravadas;
    $comprobanteElectronico->docExoneradas = 0.0;
    $comprobanteElectronico->docInafectas = 0.0;
    $comprobanteElectronico->docGratuitas = 0.0;
    $comprobanteElectronico->docDescuentoGlobal = 0.0;

    // Detalle
    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($documento[0]["movimiento_id"]);
    foreach ($documentoDetalle as $index => $fila) {
      $valorMonetario = $fila['valor_monetario'] * 1;
      if ($fila['incluye_igv'] == 1) {
        $valorMonetario = $fila['valor_monetario'] / 1.18;
      }

      $impuestoIgv = Configuraciones::IGV_PORCENTAJE / 100;
      $totalVentaItem = $valorMonetario * $fila['cantidad'];
      $impuestoItem = $valorMonetario * $fila['cantidad'] * $impuestoIgv;
      if ($documento[0]["motivo_codigo"] == 13) { // Cambio de importes para la NC tipo 13
        $valorMonetario = 0.0;
        $totalVentaItem = 0.0;
        $impuestoItem = 0.0;
      }

      $items[$index][0] = $index + 1;
      $items[$index][1] = $fila['cantidad'] * 1;
      $items[$index][2] = $valorMonetario;
      $items[$index][3] = 0; //Precio refencial
      $items[$index][4] = '01'; //Tipos de precio
      $items[$index][5] = $fila['bien_codigo'];
      //            $items[$index][6] = $fila['bien_descripcion'];
      $items[$index][6] = (!ObjectUtil::isEmpty($fila['bien_descripcion_editada'])) ? $fila['bien_descripcion_editada'] : $fila['bien_descripcion'];
      $items[$index][7] = $fila['sunat_unidad_medida'];
      $items[$index][8] = $impuestoItem; //Impuesto
      $items[$index][9] = '10'; //Tipo de impuesto
      $items[$index][10] = $totalVentaItem;
      $items[$index][11] = 0; //Descuento
      $items[$index][12] = Configuraciones::IGV_PORCENTAJE; //porcentaje IGV
    }

    $comprobanteElectronico->items = $items;

    //VALIDO EL COMENTARIO
    if (ObjectUtil::isEmpty($documento[0]["comentario"])) {
      throw new WarningException("Ingrese comentario (Sustento por el que se emite la NC)");
    }

    //OBTENEMOS LOS DOCUMENTOS RELACIONADOS
    $docRelacion = DocumentoNegocio::create()->obtenerDocumentoRelacionadoActivoXDocumentoId($documentoId);

    $discrepancias = array();
    foreach ($docRelacion as $indRel => $itemRel) {
      if (
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_BOLETA_VENTA ||
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_FACTURA_VENTA
      ) {
        //VALIDA SERIE
        if ($itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_BOLETA_VENTA && $itemRel["serie_relacion"][0] != 'B') {
          throw new WarningException("La serie de la boleta relacionada debe empezar con B");
        } else if ($itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_FACTURA_VENTA && $itemRel["serie_relacion"][0] != 'F') {
          throw new WarningException("La serie de la factura relacionada debe empezar con F");
        }

        $itemDiscrepancia[0] = $documento[0]["comentario"];
        $itemDiscrepancia[1] = $itemRel["serie_relacion"] . '-' . $itemRel["numero_relacion"];
        $itemDiscrepancia[2] = $documento[0]["motivo_codigo"];

        array_push($discrepancias, $itemDiscrepancia);
      }
    }

    //VALIDO QUE HAYA DISCREPANCIAS
    if (ObjectUtil::isEmpty($discrepancias)) {
      throw new WarningException("Relacione un documento de venta (factura o boleta)");
    }

    $comprobanteElectronico->discrepancias = $discrepancias;

    $docRelacionados = array();
    foreach ($docRelacion as $indRel => $itemRel) {
      if (
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_BOLETA_VENTA ||
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_FACTURA_VENTA
      ) {
        $itemRelacion[0] = $itemRel["serie_relacion"] . '-' . $itemRel["numero_relacion"];
        $itemRelacion[1] = $itemRel['sunat_tipo_doc_rel'];

        array_push($docRelacionados, $itemRelacion);
      }
    }

    $tipoPago = ($documento[0]["tipo_pago"] * 1);
    if ($documento[0]["motivo_codigo"] == 13) {
      $datoAdicional = array();
      //OBTENER DATOS ADICIONALES
      $datoAdicional[] = array('Fecha vencimiento', $documento[0]['fecha_vencimiento']);
      $datoAdicional[] = array('Forma pago', $tipoPago == 1 ? "Contado" : "Crédito");
      $comprobanteElectronico->extras = $datoAdicional;

      $formaPago = array();

      if ($tipoPago == 2) {
        $formaPagoDetalle = PagoNegocio::create()->obtenerPagoProgramacionXDocumentoId($documentoId);
        if (ObjectUtil::isEmpty($formaPagoDetalle)) {
          throw new WarningException("Se requiere de la programación de pago de la factura.");
        }
        $montoNetoPago = 0.0;
        foreach ($formaPagoDetalle as $indexFormaPago => $itemFormaPago) {
          $montoNetoPago += $itemFormaPago['importe'] * 1.0;
        }
        $formaPago[] = array("Credito", $montoNetoPago);

        $arrayFechaVencimiento = array();
        foreach ($formaPagoDetalle as $indexFormaPago => $itemFormaPago) {
          $arrayFechaVencimiento[] = substr($itemFormaPago['fecha_pago'], 0, 10);
          $formaPago[] = array("Cuota" . str_pad(($indexFormaPago + 1), 3, "0", STR_PAD_LEFT), $itemFormaPago['importe'] * 1.0, substr($itemFormaPago['fecha_pago'], 0, 10));
        }
      } elseif ($tipoPago == 1) {
        throw new WarningException("Nota de credito tipo 13 debe ser con forma de pago CREDITO.Se requiere de la programación de pago de la factura.");

        $formaPago[] = array("Contado", 0.0, "");
      } else {
        throw new WarningException("No se identifica la forma de pago para este comprobante.");
      }
      $comprobanteElectronico->formaPago = $formaPago;
    }

    $comprobanteElectronico->docRelacionados = $docRelacionados;

    $comprobanteElectronico->usuarioSunatSOL = $empresa[0]['usuario_sunat_sol'];
    $comprobanteElectronico->claveSunatSOL = $empresa[0]['clave_sunat_sol'];
    $client = self::conexionEFAC();

    try {
      if ($soloPDF == 1) {
        $resultado = $client->procesarNotaCreditoPDF((array) $comprobanteElectronico)->procesarNotaCreditoPDFResult;
      } else if ($soloPDF == 2) {
        $comprobanteElectronico->icbper = 0.0;
        $comprobanteElectronico->tipoDocumento = $documento[0]["documento_tipo_sunat_codigo"];
        $resultado = $client->reenviarComprobanteElectronico((array) $comprobanteElectronico)->reenviarComprobanteElectronicoResult;
      } else {
        $resultado = $client->procesarNotaCredito((array) $comprobanteElectronico)->procesarNotaCreditoResult;
      }
    } catch (Exception $e) {
      $resultado = $e->getMessage();
    }

    $resEfact = $this->validarResultadoEfacturaDocumento($resultado, $documentoId, $tipoUso);
    // var_dump($comprobanteElectronico);
    return $resEfact;
  }

  public function generarNotaDebitoElectronica($documentoId, $soloPDF, $tipoUso)
  {
    // Obtenemos Datos
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }

    $empresa = EmpresaNegocio::create()->obtenerEmpresaXId($documento[0]['empresa_id']);

    $ubigeoEmpresa = PersonaNegocio::create()->obtenerUbigeoXId($empresa[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeoEmpresa)) {
      throw new WarningException("No se especificó el ubigeo del emisor");
    }

    $persona = PersonaNegocio::create()->obtenerPersonaXId($documento[0]["persona_id"]);
    if (ObjectUtil::isEmpty($persona)) {
      throw new WarningException("No se encontró a la persona del documento");
    }
    $ubigeo = PersonaNegocio::create()->obtenerUbigeoXId($documento[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeo)) {
      throw new WarningException("No se especificó el ubigeo del receptor");
    }
    $enLetras = new EnLetras();
    $importeLetras = $enLetras->ValorEnLetras($documento[0]["total"], $documento[0]['moneda_id']);

    $comprobanteElectronico = new stdClass();
    $comprobanteElectronico->emisorNroDocumento = $empresa[0]['ruc'];
    $comprobanteElectronico->emisorTipoDocumento = $empresa[0]['sunat_tipo_documento'];
    $comprobanteElectronico->emisorUbigeo = $ubigeoEmpresa[0]['ubigeo_codigo'];
    $comprobanteElectronico->emisorDireccion = $empresa[0]['direccion_fe'];
    $comprobanteElectronico->emisorUrbanizacion = $empresa[0]['urbanizacion'];
    $comprobanteElectronico->emisorDepartamento = $ubigeoEmpresa[0]['ubigeo_dep'];
    $comprobanteElectronico->emisorProvincia = $ubigeoEmpresa[0]['ubigeo_prov'];
    $comprobanteElectronico->emisorDistrito = $ubigeoEmpresa[0]['ubigeo_dist'];
    $comprobanteElectronico->emisorNombreLegal = $empresa[0]['razon_social'];
    $comprobanteElectronico->emisorNombreComercial = $empresa[0]['nombre_comercial'];
    $comprobanteElectronico->emisorNroTelefono = $empresa[0]['telefono'];

    // receptor
    $comprobanteElectronico->receptorNroDocumento = $persona[0]["codigo_identificacion"];
    $comprobanteElectronico->receptorTipoDocumento = $persona[0]["sunat_tipo_documento"];
    $comprobanteElectronico->receptorNombreLegal = $persona[0]["persona_nombre_completo"];
    $comprobanteElectronico->receptorUbigeo = $ubigeo[0]["ubigeo_codigo"];
    $comprobanteElectronico->receptorDireccion = $documento[0]["direccion"];
    $comprobanteElectronico->receptorUrbanizacion = '';
    $comprobanteElectronico->receptorDepartamento = $ubigeo[0]["ubigeo_dep"];
    $comprobanteElectronico->receptorProvincia = $ubigeo[0]["ubigeo_prov"];
    $comprobanteElectronico->receptorDistrito = $ubigeo[0]["ubigeo_dist"];

    $correos = PersonaNegocio::create()->obtenerCorreosEFACT();
    $comprobanteElectronico->receptorEmail = str_replace(';', ',', $persona[0]["email"] . "," . $correos[0]["correos"] . "," . Configuraciones::EFACT_CORREO);
    // $comprobanteElectronico->receptorEmail = 'nleon';
    // factura
    $serieNum = DocumentoNegocio::create()->obtenerSerieNumeroXDocumentoId($documentoId);

    //VALIDA SERIE
    if ($serieNum[0]["serie"][0] != 'F') {
      throw new WarningException("La serie del documento debe empezar con F");
    }

    $comprobanteElectronico->docSerie = $serieNum[0]["serie"];
    $comprobanteElectronico->docNumero = $serieNum[0]["numero"];
    $comprobanteElectronico->docFechaEmision = substr($documento[0]["fecha_emision"], 0, 10);
    $comprobanteElectronico->docMoneda = $documento[0]["sunat_moneda"];
    $comprobanteElectronico->docMontoEnLetras = $importeLetras; //'TRES MIL OCHOCIENTOS TREINTA Y CINCO SOLES CON 0/100';
    $comprobanteElectronico->docTotalIgv = $documento[0]["igv"] * 1.0;
    $comprobanteElectronico->docTotalVenta = $documento[0]["total"] * 1;
    $comprobanteElectronico->docGravadas = $documento[0]["subtotal"] * 1.0;
    $comprobanteElectronico->docExoneradas = 0.0;
    $comprobanteElectronico->docInafectas = 0.0;
    $comprobanteElectronico->docGratuitas = 0.0;
    $comprobanteElectronico->docDescuentoGlobal = 0.0;

    // Detalle
    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($documento[0]["movimiento_id"]);
    foreach ($documentoDetalle as $index => $fila) {
      $valorMonetario = $fila['valor_monetario'] * 1;
      if ($fila['incluye_igv'] == 1) {
        $valorMonetario = $fila['valor_monetario'] / 1.18;
      }

      $items[$index][0] = $index + 1;
      $items[$index][1] = $fila['cantidad'] * 1;
      $items[$index][2] = $valorMonetario;
      $items[$index][3] = 0; //Precio refencial
      $items[$index][4] = '01'; //Tipos de precio
      $items[$index][5] = $fila['bien_codigo'];
      $items[$index][6] = $fila['bien_descripcion'];
      $items[$index][7] = $fila['sunat_unidad_medida'];
      $items[$index][8] = $valorMonetario * $fila['cantidad'] * 0.18; //Impuesto
      $items[$index][9] = '10'; //Tipo de impuesto
      $items[$index][10] = $valorMonetario * $fila['cantidad'];
      $items[$index][11] = NULL; //Descuento
      $items[$index][12] = Configuraciones::IGV_PORCENTAJE; //porcentaje IGV
    }

    $comprobanteElectronico->items = $items;

    // VALIDO EL COMENTARIO
    if (ObjectUtil::isEmpty($documento[0]["comentario"])) {
      throw new WarningException("Ingrese comentario (Sustento por el que se emite la ND)");
    }

    // OBTENEMOS LOS DOCUMENTOS RELACIONADOS
    $docRelacion = DocumentoNegocio::create()->obtenerDocumentoRelacionadoActivoXDocumentoId($documentoId);

    $discrepancias = array();
    foreach ($docRelacion as $indRel => $itemRel) {
      if (
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_BOLETA_COMPRA ||
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_FACTURA_COMPRA
      ) {
        $serieNumRel = DocumentoNegocio::create()->obtenerSerieNumeroXDocumentoId($itemRel['documento_relacionado_id']);

        //VALIDA SERIE
        if ($itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_BOLETA_COMPRA && $serieNumRel[0]["serie"][0] != 'B') {
          throw new WarningException("La serie de la boleta relacionada debe empezar con B");
        } else if ($itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_FACTURA_COMPRA && $serieNumRel[0]["serie"][0] != 'F') {
          throw new WarningException("La serie de la factura relacionada debe empezar con F");
        }

        $itemDiscrepancia[0] = $documento[0]["comentario"];
        $itemDiscrepancia[1] = $serieNumRel[0]["serie"] . '-' . $serieNumRel[0]["numero"];
        $itemDiscrepancia[2] = $documento[0]["motivo_codigo"];

        array_push($discrepancias, $itemDiscrepancia);
      }
    }

    // VALIDO QUE HAYA DISCREPANCIAS
    if (ObjectUtil::isEmpty($discrepancias)) {
      throw new WarningException("Relacione un documento de compra (factura o boleta)");
    }

    $comprobanteElectronico->discrepancias = $discrepancias;

    $docRelacionados = array();
    foreach ($docRelacion as $indRel => $itemRel) {
      if (
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_BOLETA_COMPRA ||
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_FACTURA_COMPRA
      ) {
        $serieNumRel = DocumentoNegocio::create()->obtenerSerieNumeroXDocumentoId($itemRel['documento_relacionado_id']);

        $itemRelacion[0] = $serieNumRel[0]["serie"] . '-' . $serieNumRel[0]["numero"];
        $itemRelacion[1] = $itemRel['sunat_tipo_doc_rel'];

        array_push($docRelacionados, $itemRelacion);
      }
    }

    $comprobanteElectronico->docRelacionados = $docRelacionados;

    $comprobanteElectronico->usuarioSunatSOL = $empresa[0]['usuario_sunat_sol'];
    $comprobanteElectronico->claveSunatSOL = $empresa[0]['clave_sunat_sol'];
    $client = self::conexionEFAC();

    try {
      if ($soloPDF == 1) {
        $resultado = $client->procesarNotaDebitoPDF((array) $comprobanteElectronico)->procesarNotaDebitoPDFResult;
      } else if ($soloPDF == 2) {
        $comprobanteElectronico->icbper = 0.0;
        $comprobanteElectronico->tipoDocumento = $documento[0]["documento_tipo_sunat_codigo"];
        $resultado = $client->reenviarComprobanteElectronico((array) $comprobanteElectronico)->reenviarComprobanteElectronicoResult;
      } else {
        $resultado = $client->procesarNotaDebito((array) $comprobanteElectronico)->procesarNotaDebitoResult;
      }
    } catch (Exception $e) {
      $resultado = $e->getMessage();
    }

    $resEfact = $this->validarResultadoEfacturaDocumento($resultado, $documentoId, $tipoUso);

    // var_dump($comprobanteElectronico);
    return $resEfact;
  }

  public function validarBienesFaltantes($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario = NULL, $checkIgv = 1, $monedaId = null, $accionEnvio)
  {
    // validacion en caso de bienes faltantes
    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    $bandera = false;
    $dataOrganizador = array();
    $dataProveedor = array();

    if ($movimientoTipo[0]["indicador"] == MovimientoTipoNegocio::INDICADOR_SALIDA) {
      $j = 0;
      foreach ($detalle as $indice => $item) {
        $stockData = BienNegocio::create()->obtenerStockActual($item["bienId"], $item["organizadorId"], $item["unidadMedidaId"]);
        $stock = $stockData[0]["stock"];
        if ($stock < 0) {
          $stock = 0;
        }

        $cantidadFaltante = $stock - $item["cantidad"];
        $cantidadFaltante = $cantidadFaltante * -1;
        // validar que no sea servicio
        $bien = BienNegocio::create()->getBien($item["bienId"]);

        if ($cantidadFaltante > 0 && $bien[0]['bien_tipo_id'] != -1) {
          $dataOrganizador[$j] = array();
          // $dataProveedor[$j]=array();

          $dataP = BienNegocio::create()->obtenerBienPersonaXBienId($item["bienId"]);
          array_push($dataProveedor, $dataP);

          $bandera = true;
          $detalleFaltantes[$j] = $item;

          $detalle[$indice]["cantidad"] = $stock;
          $detalleFaltantes[$j]["cantidad"] = $cantidadFaltante;
          $detalleFaltantes[$j]["organizadorId"] = '';

          // $dataStockBien=BienNegocio::create()->obtenerStockPorBien($item["bienId"], $movimientoTipo[0]["empresa_id"]);
          $dataStockBien = BienNegocio::create()->obtenerStockPorBien($item["bienId"], null);

          foreach ($dataStockBien->stockBien as $ind => $itemDataStock) {
            if ($cantidadFaltante <= $itemDataStock["stock"] && $item["unidadMedidaId"] == $itemDataStock["unidad_medida_id"]) {
              array_push($dataOrganizador[$j], array('organizadorId' => $itemDataStock["organizador_id"], 'descripcion' => $itemDataStock["organizador_descripcion"]));
            }
          }

          $j++;
        }
      }

      $respuesta = new stdClass();
      $respuesta->detalleFaltantes = $detalleFaltantes;
      $respuesta->dataOrganizador = $dataOrganizador;
      $respuesta->dataProveedor = $dataProveedor;
      $respuesta->dataDetalle = $detalle;
    }

    if ($bandera) {
      return $respuesta;
    }

    return $this->guardarXAccionEnvio($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $accionEnvio);
  }

  public function guardarNormalmente($documentoId, $dataMovBienPRM, $usuarioIdPRM)
  {
    $relacionadosDocumentoActual = DocumentoNegocio::create()->obtenerDocumentosRelacionados($documentoId);

    foreach ($relacionadosDocumentoActual as $item) {
      $docRelacionadoId = $item['documento_relacionado_id'];

      $movimientoIdActual = MovimientoBien::create()->obtenerMovimientoIdXDocumentoId($documentoId);
      $dataMovBien = $dataMovBienPRM;
      $bienesIdRelacionados = MovimientoBien::create()->obtenerBienesIdRelacionadosXDocumentoId($docRelacionadoId);
      foreach ($dataMovBien as $dataMovBienActual) {
        foreach ($bienesIdRelacionados as $bienesIdRelacionado) {

          if ($dataMovBienActual['bien_id'] == $bienesIdRelacionado['bien_id']) {

            $movimiento_bien_anterior = $bienesIdRelacionado['movimiento_bien_anterior'];
            $movimiento_bien_destino = $dataMovBienActual['movimiento_bien_id'];
            $cantidad = $bienesIdRelacionado['cantidad_solicitada'];
            MovimientoBien::create()->guardarDocumentoAtencionSolicitud($movimiento_bien_anterior, $movimiento_bien_destino, $cantidad, $usuarioIdPRM);
          }
        }
      }
    }
  }

  public function guardarDocumentoPercepcion($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $tipoPago, $percepcion, $periodoId)
  {
    // OBTENIENDO PERCEPCION
    $percepcionMonto = $percepcion["importeSoles"];
    foreach ($camposDinamicos as $indexCampos => $valorDtd) {
      // if ($valorDtd["tipo"] == 19) {
      //   $percepcionMonto = $valorDtd["valor"];
      // }
      if ($valorDtd["tipo"] == 9) {
        $fechaEmisionDtd = $valorDtd["valor"];
      }
    }

    if ($opcionId == Configuraciones::OPCION_ID_DUA && $percepcionMonto * 1 != 0 && !ObjectUtil::isEmpty($percepcionMonto)) {
      $opcionIdPer = null;
      $documentoTipoIdPer = Configuraciones::DOCUMENTO_TIPO_ID_PERCEPCION;

      //cabecera del documento
      $configuracionesDtd = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDatoSimple($documentoTipoIdPer);

      foreach ($configuracionesDtd as $indexConfig => $itemDtd) {
        if ($itemDtd["tipo"] == 8) {
          $configuracionesDtd[$indexConfig]["valor"] = DocumentoNegocio::create()->obtenerNumeroAutoIncrementalXDocumentoTipo($documentoTipoIdPer);
        }
        if ($itemDtd["tipo"] == 9) {
          $configuracionesDtd[$indexConfig]["valor"] = $fechaEmisionDtd;
        }
        if ($itemDtd["tipo"] == 7) {
          $configuracionesDtd[$indexConfig]["valor"] = $itemDtd['cadena_defecto'];
        }
        if ($itemDtd["tipo"] == 14) {
          $configuracionesDtd[$indexConfig]["valor"] = $percepcionMonto;
        }
      }
      $monedaId = 2;
      $documentoId = PagoNegocio::create()->guardar($opcionIdPer, $usuarioId, $documentoTipoIdPer, $configuracionesDtd, $monedaId, $periodoId);

      $documentoARelacionarRecep = array('documentoId' => $documentoId, 'movimientoId' => '', 'detalleLink' => '', 'posicion' => '');

      $respuesta = new stdClass();
      $respuesta->documentoIdRecep = $documentoId;
      $respuesta->documentoARelacionarPercepcion = $documentoARelacionarRecep;
    } else {
      $respuesta = null;
    }
    return $respuesta;
  }

  public function validarDocumentoARelacionar($opcionId, $documentoTipoId, $documentoARelacionar, $valorCheck)
  {
    // Validación de relaciones de DUA con GR y con OC   - GR: GUIA DE REMISION BH
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);
    if ($dataDocumentoTipo[0]['identificador_negocio'] == 21) {
      // ES DUA
      if (ObjectUtil::isEmpty($documentoARelacionar)) {
        throw new WarningException("Debe relacionar una orden de compra para poder guardar");
      } else {
        $copiaGuiaRec = false;
        foreach ($documentoARelacionar as $item) {
          if (!ObjectUtil::isEmpty($item['documentoId'])) {
            // buscando orden de compra para ver sus relaciones.
            $dataDocCopia = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($item['documentoId']);
            $dataDocTipoCopia = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($dataDocCopia[0]['documento_tipo_id']);

            if ($dataDocTipoCopia[0]['identificador_negocio'] == 10) { //ES ORDEN DE COMPRA
              // OBTENEMOS LAS RELACIONES DE O.C.
              // $dataRelaciones =  DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoId($item['documentoId']);
              $dataRelaciones = DocumentoNegocio::create()->obtenerDocumentoRelacionadoImpresion($item['documentoId']);
              foreach ($dataRelaciones as $itemRel) {
                // VALIDAMOS SI ES UNA GUIA DE REMISION  o GUIA DE RECEPCION (SE QUITO LA GR DE ENTRADA)
                if ($itemRel['identificador_negocio'] == 6 || $itemRel['identificador_negocio'] == 22) {
                  $copiaGuiaRec = true;
                }
              }
            }
          }
        }

        if (!$copiaGuiaRec) {
          throw new WarningException("Debe relacionar una guia de recepción con la orden de compra para poder guardar");
        }
      }
    }

    // SI ES RECEPCION DE TRANSFERENCIA - DT: RECEPCION
    if ($dataDocumentoTipo[0]['identificador_negocio'] == 9) {
      if (ObjectUtil::isEmpty($documentoARelacionar)) {
        throw new WarningException("Debe relacionar la Guía de remisión para poder guardar");
      } else {
        $copiaGuiaRem = false;
        foreach ($documentoARelacionar as $item) {
          if (!ObjectUtil::isEmpty($item['documentoId'])) {
            // buscando guia de remision en las copias.
            $dataDocCopia = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($item['documentoId']);

            if ($dataDocCopia[0]['identificador_negocio'] == 6) { //ES GUIA DE REMISION
              $copiaGuiaRem = true;
            }
          }
        }

        if (!$copiaGuiaRem) {
          throw new WarningException("Debe relacionar la Guía de remisión para poder guardar");
        }
      }
    }

    // throw new WarningException("ERROR...PASO TODO BIEN");
  }

  public function validarDetalleContenidoEnDocumentoRelacion($opcionId, $documentoTipoId, $documentoARelacionar, $valorCheck, $detalle)
  {
    $bandera = false; //NO HAY ERRORES

    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);
    if ($dataDocumentoTipo[0]['identificador_negocio'] == 23) {
      //ES GUIA INTERNA DE TRASLADO EN UN SOLO PASO
      if (!ObjectUtil::isEmpty($documentoARelacionar)) {
        $movimientoIds = '0';
        $contador = 0;
        foreach ($documentoARelacionar as $item) {
          //                    $item['tipo']==1; ES EL PADRE DE LOS DOCUMENTOS RELACIONADO
          if (!ObjectUtil::isEmpty($item['documentoId']) && $item['tipo'] == 1) {
            $movimientoIds = $movimientoIds . ',' . $item['movimientoId'];
            $contador++;
          }
        }
        //obtenemos la data del detalle de la copia
        $bandera = false; //NO HAY ERRORES
        $df = $detalle;
        // $dr = MovimientoBien::create()->obtenerXIdMovimiento($item['movimientoId']);
        $dr = MovimientoBien::create()->obtenerXMovimientoIds($movimientoIds);

        foreach ($df as $i => $itemDoc) {
          $bandera2 = false; //HAY ERROR
          foreach ($dr as $j => $itemRel) {
            if ($itemDoc['bienId'] == $itemRel['bien_id'] && $itemDoc['cantidad'] * 1 <= $itemRel['cantidad'] * 1 && $itemDoc['unidadMedidaId'] == $itemRel['unidad_medida_id']) {
              $bandera2 = true; //CORRECTO
              break;
            }
          }
          if (!$bandera2) { //SI HAY ERROR
            $bandera = true; //ERROR
            break;
          }
        }
      }
    }

    if ($bandera) {
      $mensaje = "El detalle del formulario debe estar contenido en el detalle del documento relacionado";
      if ($contador > 1) {
        $mensaje = "El detalle del formulario debe estar contenido en el detalle de los documentos relacionados";
      }
      throw new WarningException($mensaje);
    }

    // throw new WarningException("TODO BIEN");
  }

  public function guardarXAccionEnvio($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $accionEnvio, $tipoPago = null, $listaPagoProgramacion = null, $atiende = null, $periodoId = null, $percepcion = null, $datosExtras = null, $contOperacionTipoId = null, $igv_porcentaje = null)
  {
    // VALIDAR RELACIONES DE DOCUMENTOS
    $res = MovimientoNegocio::create()->validarDocumentoARelacionar($opcionId, $documentoTipoId, $documentoARelacionar, $valorCheck);

    // VALIDAR DETALLE DE DOCUMENTOS CON LA COPIA
    $resValDet = MovimientoNegocio::create()->validarDetalleContenidoEnDocumentoRelacion($opcionId, $documentoTipoId, $documentoARelacionar, $valorCheck, $detalle);

    $puedeGuardar = false;
    $obligatorio = $this->verificarDocumentoEsObligatorioXOpcionID($opcionId);

    if ($obligatorio[0]['movimiento_tipo_anterior_relacion'] == 1 && ObjectUtil::isEmpty($documentoARelacionar)) {
      throw new WarningException("Se requiere una " . $obligatorio[0]['anterior_descripcion'] . ", copie alguna.");
    }

    // REGISTRAR LA RECEPCION DE TRANSFERENCIA DE UN SOLO PASO
    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    if ($movimientoTipo[0]["transferencia_tipo"] == MovimientoTipoNegocio::TRANSFERENCIA_TIPO_SALIDA && $movimientoTipo[0]["codigo"] == Configuraciones::MOVIMIENTO_TIPO_CODIGO_TRANSFERENCIA) {
      $dataRecepcion = $this->guardarDocumentoRecepcion($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $tipoPago, $periodoId);

      if (ObjectUtil::isEmpty($documentoARelacionar)) {
        $documentoARelacionar = array();
        $valorCheck = 1;
      }
      array_push($documentoARelacionar, $dataRecepcion->documentoARelacionarRecep);
    }
    // FIN REGISTRAR RECEPCION
    //----------------- GUARDAR DOCUMENTO DE PERCEPCION -------------------
    $dataPercepcion = $this->guardarDocumentoPercepcion($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $tipoPago, $percepcion, $periodoId);
    if (!ObjectUtil::isEmpty($dataPercepcion)) {
      if (ObjectUtil::isEmpty($documentoARelacionar)) {
        $documentoARelacionar = array();
        $valorCheck = 1;
      }
      array_push($documentoARelacionar, $dataPercepcion->documentoARelacionarPercepcion);
    }
    //----------------- FIN GUARDAR PERCEPCION ----------------------------

    if (
      $documentoTipoId == ContDistribucionContableNegocio::DOCUMENTO_TIPO_ID_FACTURA_VENTA ||
      $documentoTipoId == ContDistribucionContableNegocio::DOCUMENTO_TIPO_ID_BOLETA_VENTA ||
      $documentoTipoId == ContDistribucionContableNegocio::DOCUMENTO_TIPO_ID_NOTA_CREDITO_VENTA ||
      $documentoTipoId == ContDistribucionContableNegocio::DOCUMENTO_TIPO_ID_NOTA_DEBITO_VENTA
    ) {
      $contOperacionTipoId = ContVoucherNegocio::OPERACION_TIPO_ID_VENTAS;
    }

    // Guardar documento
    $documento = $this->guardar($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $tipoPago, $periodoId, $datosExtras, $contOperacionTipoId, null, $igv_porcentaje);

    if (
      $documentoTipoId == ContDistribucionContableNegocio::DOCUMENTO_TIPO_ID_FACTURA_VENTA ||
      $documentoTipoId == ContDistribucionContableNegocio::DOCUMENTO_TIPO_ID_BOLETA_VENTA ||
      $documentoTipoId == ContDistribucionContableNegocio::DOCUMENTO_TIPO_ID_NOTA_CREDITO_VENTA ||
      $documentoTipoId == ContDistribucionContableNegocio::DOCUMENTO_TIPO_ID_NOTA_DEBITO_VENTA
    ) {
      $respuestaContVoucher = ContVoucherNegocio::create()->registrarContVoucherRegistroVentas($documento[0]['vout_id'], $usuarioId);
      $respuestaActualizarEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documento[0]['vout_id'], NULL, $usuarioId, 'AP', NULL);
      if ($respuestaActualizarEstado[0]['vout_exito'] != 1) {
        throw new WarningException($respuestaActualizarEstado[0]['vout_mensaje']);
      }
    }
    if (!ObjectUtil::isEmpty($atiende)) {
      if ($atiende == false) {
        // ---GUARDAR VALORES AUTOMATICOS
        $relacionadosDocumentoActual = DocumentoNegocio::create()->obtenerDocumentosRelacionados($documento[0]['vout_id']);

        foreach ($relacionadosDocumentoActual as $item) {
          $docRelacionadoId = $item['documento_relacionado_id'];
          $movimientoIdActual = MovimientoBien::create()->obtenerMovimientoIdXDocumentoId($documento[0]['vout_id']);
          $dataMovBien = MovimientoBien::create()->obtenerXIdMovimiento($movimientoIdActual[0]['movimiento_id']);
          $bienesIdRelacionados = MovimientoBien::create()->obtenerBienesIdRelacionadosXDocumentoId($docRelacionadoId);
          foreach ($dataMovBien as $dataMovBienActual) {
            foreach ($bienesIdRelacionados as $bienesIdRelacionado) {
              if ($dataMovBienActual['bien_id'] == $bienesIdRelacionado['bien_id']) {
                $movimiento_bien_anterior = $bienesIdRelacionado['movimiento_bien_anterior'];
                $movimiento_bien_destino = $dataMovBienActual['movimiento_bien_id'];
                $cantidad = $bienesIdRelacionado['cantidad_solicitada'];
                MovimientoBien::create()->guardarDocumentoAtencionSolicitud($movimiento_bien_anterior, $movimiento_bien_destino, $cantidad, $usuarioId);
              }
            }
          }
        }
      } else {
        // Guardar asignaciones que hace el usuario
        $relacionadosDocumentoActual = DocumentoNegocio::create()->obtenerDocumentosRelacionados($documento[0]['vout_id']);
        $detallesMovimientoBien = $camposDinamicos['atencionesRef'];
        // foreach ($relacionadosDocumentoActual as $item) {
        // $docRelacionadoId = $item['documento_relacionado_id'];

        $movimientoIdActual = MovimientoBien::create()->obtenerMovimientoIdXDocumentoId($documento[0]['vout_id']);
        $dataMovBien = MovimientoBien::create()->obtenerXIdMovimiento($movimientoIdActual[0]['movimiento_id']);
        $guardadoNormal = array();
        $guardadoNormal = $dataMovBien;
        foreach ($detallesMovimientoBien as $detalleMB) {
          foreach ($dataMovBien as $indiceMB => $dataMovBienActual) {
            if ($dataMovBienActual['bien_id'] == $detalleMB[0]) {
              // array_splice($guardadoNormal, $indiceMB,1,null);
              unset($guardadoNormal[$indiceMB]);
              // $guardadoNormal = $dataMovBien;
            } else {
              // array_push($guardadoNormal, $dataMovBienActual);
            }
          }
        }

        if (!ObjectUtil::isEmpty($guardadoNormal)) {
          $this->guardarNormalmente($documento[0]['vout_id'], $guardadoNormal, $usuarioId);
        }

        foreach ($dataMovBien as $dataMovBienActual) {
          foreach ($detallesMovimientoBien as $detalleMB) {
            if ($dataMovBienActual['bien_id'] == $detalleMB[0]) {
              $detalleArray = $detalleMB[1];
              $length = count($detalleMB[1]);
              for ($x = 0; $x < $length; $x++) {
                $movimiento_bien_anterior = $detalleMB[1][$x]['mov_bien_ant_id'];
                $movimiento_bien_destino = $dataMovBienActual['movimiento_bien_id'];
                $cantidad = $detalleMB[1][$x]['cantidad'];
                MovimientoBien::create()->guardarDocumentoAtencionSolicitud($movimiento_bien_anterior, $movimiento_bien_destino, $cantidad, $usuarioId);
              }
            }
          }
        }
        // }
      }
    }

    if (!ObjectUtil::isEmpty($listaPagoProgramacion) && $documentoTipoId != Configuraciones::GENERAR_COTIZACION) {
      foreach ($listaPagoProgramacion as $ind => $item) {
        //listaPagoProgramacion.push([ fechaPago, importePago, dias, porcentaje,glosa,pagoProgramacionId]);
        $fechaPago = DateUtil::formatearCadenaACadenaBD($item[0]);
        $importePago = $item[1];
        $dias = $item[2];
        $porcentaje = $item[3];
        $glosa = $item[4];

        $res = Pago::create()->guardarPagoProgramacion($documento[0]['vout_id'], $fechaPago, $importePago, $dias, $porcentaje, $glosa, $usuarioId);
      }
    }

    $accionBusquedad = preg_quote('enviar', '/') . '.*';
    if ((bool) preg_match("/^{$accionBusquedad}$/i", $accionEnvio)) {
      // SI EL MOVIMIENTO ES COTIZACION DE VENTA
      if ($movimientoTipo[0]['codigo'] == 7 && $documentoTipoId == 23) {
        DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documento[0]['vout_id'], 1, $usuarioId);
      }
    }

    $dataMovimientoDocumento = MovimientoBien::create()->obtenerMovimientoIdXDocumentoId($documento[0]['vout_id']);
    $respuesta = new stdClass();
    $respuesta->movimientoId = $dataMovimientoDocumento[0]['movimiento_id'];

    if ($accionEnvio == 'guardar') {
      $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);
      $respuesta->documentoId = $documento[0]['vout_id'];
      $respuesta->serieNumero = $documento[0]['serie']."-".$documento[0]['numero'];
      $respuesta->documentoTipoDescripcion = $dataDocumentoTipo[0]['descripcion'];
      return $respuesta;
    }

    if ($accionEnvio == 'enviar') {
      $respuesta->documentoId = $documento[0]['vout_id'];
      return $respuesta;
    }

    if ($accionEnvio == 'enviarEImprimir') {
      $respuesta->dataImprimir = $this->imprimirExportarPDFDocumento($documentoTipoId, $documento[0]['vout_id'], $usuarioId);
      $respuesta->documentoId = $documento[0]['vout_id'];
      return $respuesta;
    } else {
      // obtener email de plantilla
      $movimientoTipoId = $this->obtenerIdXOpcion($opcionId);
      $plantilla = EmailPlantillaNegocio::create()->obtenerPlantillaDestinatarioXAccionDescripcionXMovimientoTipoId($accionEnvio, $movimientoTipoId);
      $dataPersona = DocumentoNegocio::create()->obtenerPersonaXDocumentoId($documento[0]['vout_id']);

      $correosPlantilla = EmailPlantillaNegocio::create()->obtenerEmailXDestinatario($plantilla[0]["destinatario"], $documento[0]['vout_id'], $dataPersona[0]['id']);

      //validar si se muestra el modal de confirmacion de emails.
      if ($plantilla[0]["confirmacion"] == 1) {
        $respuesta->dataPlantilla = $plantilla;
        $respuesta->dataCorreos = $correosPlantilla;
        $respuesta->documentoId = $documento[0]['vout_id'];
        return $respuesta;
      }

      if (ObjectUtil::isEmpty($correosPlantilla)) {
        $this->setMensajeEmergente("Email en blanco, nose pudo enviar correo.", null, Configuraciones::MENSAJE_WARNING);
        $respuesta->documentoId = $documento[0]['vout_id'];
        return $respuesta;
      }

      $correos = '';
      foreach ($correosPlantilla as $email) {
        $correos = $correos . $email . ';';
      }

      $plantillaId = $plantilla[0]["email_plantilla_id"];
      $respuesta->dataEnvioCorreo = $this->enviarCorreoXAccion($correos, $comentario, $accionEnvio, $documento[0]['vout_id'], $plantillaId, $usuarioId);
      $respuesta->documentoId = $documento[0]['vout_id'];
      return $respuesta;
    }
  }

  public function guardarDocumentoRecepcion($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $tipoPago, $periodoId)
  {
    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    $empresaDestinoId = $movimientoTipo[0]["empresa_id"];

    // OBTENIENDO ORGANIZADOR DESTINO
    foreach ($camposDinamicos as $indexCampos => $valorDtd) {
      if ($valorDtd["tipo"] == 17) {
        $organanizadorDestinoId = $valorDtd["valor"];
      }
    }

    // OBTENIENDO EMPRESA DESTINO SEGUN ORGANIZADOR
    $dataEmpresa = OrganizadorNegocio::create()->obtenerEmpresaXOrganizadorId($organanizadorDestinoId);
    if (!ObjectUtil::isEmpty($dataEmpresa)) {
      $empresaDestinoId = $dataEmpresa[0]['empresa_id'];
    }

    $res = Movimiento::create()->obtenerMovimientoTipoRecepcionXEmpresaIdXCodigo($empresaDestinoId, Configuraciones::MOVIMIENTO_TIPO_CODIGO_RECEPCION);

    $opcionIdR = $res[0]['opcion_id'];
    $documentoTipoIdR = $res[0]['documento_tipo_id'];

    // cabecera del documento
    $camposDinamicosGuia = $camposDinamicos;
    $configuraciones = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDatoSimple($documentoTipoIdR);

    foreach ($configuraciones as $indexConfig => $itemDtd) {
      foreach ($camposDinamicosGuia as $indexCampos => $valorDtd) {
        if ((int) $itemDtd["tipo"] === (int) $valorDtd["tipo"]) {
          $camposDinamicosGuia[$indexCampos]["id"] = $itemDtd["id"];
          if ($itemDtd["tipo"] == 8) {
            $camposDinamicosGuia[$indexCampos]["valor"] = DocumentoNegocio::create()->obtenerNumeroAutoIncrementalXDocumentoTipo($documentoTipoIdR);
          }
        }
      }
    }

    foreach ($detalle as $indexDet => $item) {
      $detalle[$indexDet]['organizadorId'] = $organanizadorDestinoId;
    }

    $documento = $this->guardar($opcionIdR, $usuarioId, $documentoTipoIdR, $camposDinamicosGuia, $detalle, null, 1, $comentario, $checkIgv, $monedaId, $tipoPago, $periodoId);
    $documentoARelacionarRecep = array('documentoId' => $documento[0]['vout_id'], 'movimientoId' => '', 'detalleLink' => '', 'posicion' => '');

    $respuesta = new stdClass();
    $respuesta->documentoIdRecep = $documento[0]['vout_id'];
    $respuesta->documentoARelacionarRecep = $documentoARelacionarRecep;

    return $respuesta;
  }

  public function imprimirExportarPDFDocumento($documentoTipoId, $documentoId, $usuarioId)
  {
    $respuesta = new stdClass();
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);
    $indicadorImprimir = $dataDocumentoTipo[0]['indicador_imprimir'] == 1;
    if ($indicadorImprimir == 1) { //genera pdf
      $hoy = date("Y_m_d_H_i_s");
      $pdf = 'documento_' . $hoy . '_' . $usuarioId . '.pdf';
      $url = __DIR__ . '/../../vistas/com/movimiento/documentos/' . $pdf;
      $data = MovimientoNegocio::create()->imprimir($documentoId, $documentoTipoId);

      $nombre = MovimientoNegocio::create()->generarDocumentoPDF($documentoId, '', 'F', $url, $data);
      $url = Configuraciones::url_base() . 'vistas/com/movimiento/documentos/' . $pdf;

      $respuesta->url = $url;
      $respuesta->nombre = $nombre;
      $respuesta->pdf = $pdf;
      return $respuesta;
    } else {
      // VALIDAMOS QUE TENGA PDF LA FACTURA ELECTRONICA
      $resDocElectronico = null;
      if (
        $dataDocumentoTipo[0]['identificador_negocio'] == DocumentoTipoNegocio::IN_FACTURA_VENTA ||
        $dataDocumentoTipo[0]['identificador_negocio'] == DocumentoTipoNegocio::IN_BOLETA_VENTA ||
        $dataDocumentoTipo[0]['identificador_negocio'] == DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA ||
        $dataDocumentoTipo[0]['identificador_negocio'] == DocumentoTipoNegocio::IN_NOTA_DEBITO_COMPRA ||
        $dataDocumentoTipo[0]['identificador_negocio'] == DocumentoTipoNegocio::IN_GUIA_REMISION
      ) {
        $resDocElectronico = $this->imprimirDocumentoElectronico($documentoId);
      }

      if (!ObjectUtil::isEmpty($resDocElectronico) && !ObjectUtil::isEmpty($resDocElectronico->urlPDF)) {
        $respuesta->url = $resDocElectronico->urlPDF;
        $respuesta->nombre = $resDocElectronico->nombrePDF;
        $respuesta->contenedor = $resDocElectronico->contenedor;
        $respuesta->pdfSunat = 1;
        $respuesta->descargar = $resDocElectronico->descargar;
        return $respuesta;
      } else {
        // SI ES BOLETA o NOTA DE CREDITO EXPORTAMOS EN PDF
        if ($dataDocumentoTipo[0]['identificador_negocio'] == 3 || $dataDocumentoTipo[0]['identificador_negocio'] == 5) {
          $hoy = date("Y_m_d_H_i_s");
          $pdf = 'documento_' . $hoy . '_' . $usuarioId . '.pdf';
          $url = __DIR__ . '/../../reporteJasper/documentos/' . $pdf;
          $data = MovimientoNegocio::create()->imprimir($documentoId, $documentoTipoId);

          $nombre = MovimientoNegocio::create()->generarDocumentoImpresionPDF($documentoId, $url, $data);

          $respuesta->url = $url;
          $respuesta->nombre = $nombre;
          $respuesta->pdf = $pdf;
          $respuesta->iReport = 1;
          return $respuesta;
        } else {
          return MovimientoNegocio::create()->imprimir($documentoId, $documentoTipoId);
        }
      }
    }
  }

  public function ExportarXMLDocumento($documentoTipoId, $tipo, $documentoId, $usuarioId)
  {
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);

    // VALIDAMOS QUE TENGA PDF LA FACTURA ELECTRONICA
    if (
      $dataDocumentoTipo[0]['identificador_negocio'] == DocumentoTipoNegocio::IN_FACTURA_VENTA ||
      $dataDocumentoTipo[0]['identificador_negocio'] == DocumentoTipoNegocio::IN_BOLETA_VENTA ||
      $dataDocumentoTipo[0]['identificador_negocio'] == DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA ||
      $dataDocumentoTipo[0]['identificador_negocio'] == DocumentoTipoNegocio::IN_NOTA_DEBITO_COMPRA ||
      $dataDocumentoTipo[0]['identificador_negocio'] == DocumentoTipoNegocio::IN_GUIA_REMISION
    ) {
      $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
      $url = $documento[0]["identificador_negocio"]  != DocumentoTipoNegocio::IN_GUIA_REMISION ? Configuraciones::NUBEFACT_CONTENEDOR_PDF : Configuraciones::NUBEFACT_CONTENEDOR_PDF_GUIA;
      if (!ObjectUtil::isEmpty($documento[0]['efact_pdf_nombre'])) {
        return $url . $documento[0]['efact_pdf_nombre'] . ($tipo == 1 ? ".xml" : ".cdr");
      } else {
        throw new WarningException("No hay registros para el documento.");
      }
    }
  }
  public function imprimirDocumentoElectronico($documentoId)
  {
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    $descargar = 0;
    $url = $documento[0]["identificador_negocio"]  != DocumentoTipoNegocio::IN_GUIA_REMISION ? Configuraciones::NUBEFACT_CONTENEDOR_PDF : Configuraciones::NUBEFACT_CONTENEDOR_PDF_GUIA;
    if (!ObjectUtil::isEmpty($documento[0]['efact_pdf_nombre'])) {
      $dataEmpresa = EmpresaNegocio::create()->obtenerEmpresaXDocumentoId($documentoId);
      if ($dataEmpresa[0]['efactura'] == 1) {
        if ($documento[0]['fecha_creacion'] < '2019-09-06 01:00:00') {
          $urlPDF = $url . $documento[0]['efact_pdf_nombre'];
        } else {
          $urlPDF = $url . $documento[0]['efact_pdf_nombre'];
        }
      } else {
        $descargar = 1;
        $urlPDF = $url . $documento[0]['efact_pdf_nombre'] . ".pdf";
      }
    } else {
      $urlPDF = null;
    }

    $respuesta = new stdClass();
    $respuesta->descargar = $descargar;
    $respuesta->urlPDF = $urlPDF;
    $respuesta->nombrePDF = $documento[0]['efact_pdf_nombre'];
    $respuesta->contenedor = $url;

    return $respuesta;
  }

  public function existeColumnaCodigo($dataColumna, $codigo)
  {
    if (!ObjectUtil::isEmpty($dataColumna)) {
      foreach ($dataColumna as $item) {
        if ($item['codigo'] == $codigo) {
          return true;
        }
      }
    }

    return false;
  }

  public function guardar($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario = NULL, $checkIgv = 1, $monedaId = null, $tipoPago = null, $periodoId = null, $datosExtras = null, $contOperacionTipoId = null, $afectoAImpuesto = null, $igv_porcentaje = null)
  {
    $movimientoTipoId = $this->obtenerIdXOpcion($opcionId);
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);

    // 1. Insertamos el movimiento
    $movimiento = Movimiento::create()->guardar($movimientoTipoId, 1, $usuarioId);
    $movimientoId = $this->validateResponse($movimiento);
    if (ObjectUtil::isEmpty($movimientoId) || $movimientoId < 1) {
      throw new WarningException("No se pudo guardar el movimiento");
    }

    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);

    if (ObjectUtil::isEmpty($monedaId)) {
      $monedaId = $movimientoTipo[0]["moneda_id"];
    }

    // 2. Insertamos el documento
    $documento = DocumentoNegocio::create()->guardar($documentoTipoId, $movimientoId, null, $camposDinamicos, 1, $usuarioId, $monedaId, $comentario, null, $detalle[0]['utilidadTotal'], $detalle[0]['utilidadPorcentajeTotal'], $tipoPago, $periodoId, $datosExtras, $contOperacionTipoId, $afectoAImpuesto, $igv_porcentaje);

    $documentoId = $this->validateResponse($documento);
    if (ObjectUtil::isEmpty($documentoId) || $documentoId < 1) {
      throw new WarningException("No se pudo guardar el documento");
    }

    if (!in_array($documentoTipoId * 1, $this->arrayDocumentoTipoSinDetalle)) {
      // 3. Insertamos el detalle
      foreach ($detalle as $item) {
        // validaciones
        if ($item["bienId"] == NULL) {
          throw new WarningException("No se especificó un valor válido para un producto. ");
        }
        // if ($item["organizadorId"] == NULL) {
        //   throw new WarningException("No se especificó un valor válido para Organizador. ");
        // }
        if ($item["unidadMedidaId"] == NULL) {
          throw new WarningException("No se especificó un valor válido para Unidad Medida. ");
        }
        if($documentoTipoId == Configuraciones::REQUERIMIENTO_AREA){//Si es requerimiento por área
          if ($item["cantidad"] == NULL) {
            throw new WarningException("No se especificó un valor válido para Cantidad. ");
          }
        }else{
          if ($item["cantidad"] == NULL or $item["cantidad"] <= 0) {
            throw new WarningException("No se especificó un valor válido para Cantidad. ");
          }
        }


        //obtengo la fecha de emision
        $fechaEmision = null;
        $organizadorDestinoId = null;
        foreach ($camposDinamicos as $valorCampo) {
          if ($valorCampo["tipo"] == 9) {
            $fechaEmision = DateUtil::formatearCadenaACadenaBD($valorCampo["valor"]);
          }
          if ($valorCampo["tipo"] == 17) { //ALMACEN DE LLEGADA
            $organizadorDestinoId = $valorCampo["valor"];
          }
        }

        MovimientoNegocio::create()->obtenerStockAControlar($opcionId, $item["bienId"], $item["organizadorId"], $item["unidadMedidaId"], $item["cantidad"], $fechaEmision, $organizadorDestinoId);

        //validacion el precio unitario tiene que ser mayor al precio de compra.
        $precioCompra = 0;
        $validarPrecios = false;
        if ($item["precio"] * 1 == 0) {
          $validarPrecios = false;
        }

        if (!ObjectUtil::isEmpty($item["precioCompra"])) {
          $precioCompra = $item["precioCompra"];
        }
        if ($dataDocumentoTipo[0]["validacion"] == 1 && $validarPrecios) {
          $precioUnitario = $item["precio"];
          // $precioCompra = $item["precioCompra"];

          if ($precioUnitario <= $precioCompra) {
            throw new WarningException("No se pudo guardar un detalle del movimiento, precio unitario tiene que ser mayor al precio de compra."
              . "<br> Producto: " . $item["bienDesc"]
              . "<br> Precio compra: " . $precioCompra);
          }
        }

        // validacion: el precio minimo (descuento) no tiene que ser menor al precio unitaio
        // if($movimientoTipo[0]["indicador"]==MovimientoTipoNegocio::INDICADOR_SALIDA){
        if ($dataDocumentoTipo[0]["tipo"] == 1 && $validarPrecios) {
          $precioUnitario = $item["precio"];
          // calculo de precio minimo (descuento)
          // $precioCompra = $item["precioCompra"];

          $dataPrecio = BienPrecioNegocio::create()->obtenerBienPrecioXBienIdXUnidadMedidaIdXPrecioTipoIdXMonedaId($item["bienId"], $item["unidadMedidaId"], $item["precioTipoId"], $monedaId);
          if (!ObjectUtil::isEmpty($dataPrecio)) {
            if ($checkIgv == 1) {
              $precioVenta = $dataPrecio[0]["incluye_igv"];
            } else {
              $precioVenta = $dataPrecio[0]["precio"];
            }
            $cantidad = $item["cantidad"];
            $utilidadSoles = ($precioVenta - $precioCompra) * $cantidad;
            $subTotal = $precioVenta * $cantidad;
            $utilidadPorcentaje = 0;
            if ($subTotal != 0) {
              $utilidadPorcentaje = ($utilidadSoles / $subTotal) * 100;
            }

            $descuentoPorcentaje = ($dataPrecio[0]["descuento"] / 100) * ($utilidadPorcentaje);
            $precioMinimo = $precioVenta - ($descuentoPorcentaje / 100) * $precioVenta;
            $precioMinimo = round($precioMinimo, 2);  // 1.96

            if ($precioUnitario < $precioMinimo) {
              throw new WarningException("No se pudo guardar un detalle del movimiento, precio unitario tiene que ser mayor o igual al precio mínimo (descuento)"
                . "<br> Producto: " . $item["bienDesc"]
                . "<br> Precio mínimo: " . $precioMinimo);
            }
          }
        }

        // fin validaciones
        if (ObjectUtil::isEmpty($item["adValorem"])) {
          $item["adValorem"] = 0;
        }
        $agrupadorDetalle = "";
        if ($documentoTipoId == 23 || $documentoTipoId == 133) {
          if (!ObjectUtil::isEmpty($item["agrupadorId"])) {
            $agrupadorDetalle = $item["agrupadorId"];
          }
        }
        $ticket = "";
        if ($documentoTipoId == 23) {
          if (!ObjectUtil::isEmpty($item["ticket"])) {
            $ticket = $item["ticket"];
          }
        }
        foreach ($camposDinamicos as $campoDinam) {
          $idValor = $campoDinam['id'];
          $resDTDL = DocumentoTipoDatoLista::create()->obtenerPorId($idValor);
          if (!ObjectUtil::isEmpty($resDTDL) && $resDTDL[0]['valor'] == 13) {
            $notaCreditoTipo13 = 1;
          }
        }
        $itemPrecio = $item["precio"];
        if ($notaCreditoTipo13 == 1) {
          $itemPrecio = 0.0;
        }

        $movimientoBien = MovimientoBien::create()->guardar($movimientoId, $item["organizadorId"], $item["bienId"], $item["unidadMedidaId"], $item["cantidad"], $itemPrecio, 1, $usuarioId, $item["precioTipoId"], $item["utilidad"], $item["utilidadPorcentaje"], $checkIgv, $item["adValorem"], $item["comentarioBien"], $item["agenciaId"], $agrupadorDetalle, $ticket, $item["CeCoId"], ($item["precioPostor1"] == "" ? null : $item["precioPostor1"]), ($item["precioPostor2"] == "" ? null : $item["precioPostor2"]), ($item["precioPostor3"] == "" ? null : $item["precioPostor3"]), $item["esCompra"], $item["cantidadAceptada"], $item["postor_ganador_id"]);
        $movimientoBienId = $this->validateResponse($movimientoBien);
        if (ObjectUtil::isEmpty($movimientoBienId) || $movimientoBienId < 1) {
          throw new WarningException("No se pudo guardar un detalle del movimiento");
        }

        if ($documentoTipoId == Configuraciones::GENERAR_COTIZACION || $documentoTipoId == Configuraciones::REQUERIMIENTO_AREA || $documentoTipoId == Configuraciones::ORDEN_COMPRA) {
          $arrayIds = explode(',', $item["movimiento_bien_ids"]);
          foreach ($arrayIds as $itemarrayIds) {
            $resDetalle = MovimientoNegocio::create()->movimientoBienDetalleGuardarCadena($movimientoBienId, 35, $itemarrayIds, $usuarioId);
          }
        }

        // guardar el detalle del detalle del movimiento en movimiento_bien_detalle
        if (!ObjectUtil::isEmpty($item["detalle"])) {
          foreach ($item["detalle"] as $valor) {
            if (!ObjectUtil::isEmpty($valor['valorDet'])) {
              if ($valor['columnaCodigo'] == 16 || $valor['columnaCodigo'] == 17) {
                $resDetalle = MovimientoNegocio::create()->movimientoBienDetalleGuardarCadena($movimientoBienId, $valor['columnaCodigo'], $valor['valorDet'], $usuarioId);
              }

              if ($valor['columnaCodigo'] == 18) {
                $fechaVencimiento = DateUtil::formatearCadenaACadenaBD($valor['valorDet']);
                $resDetalle = MovimientoNegocio::create()->movimientoBienDetalleGuardarFecha($movimientoBienId, $valor['columnaCodigo'], $fechaVencimiento, $usuarioId);
              }
              if ($valor['columnaCodigo'] == 36) {
                $decode = Util::base64ToImage($valor['valorDet']);
                $nombreArchivo = $valor['nombreArchivo'];
                $pos = strripos($nombreArchivo, '.');
                $ext = substr($nombreArchivo, $pos);
    
                $hoy = date("YmdHis").substr((string)microtime(), 2, 3);;
                $nombreGenerado = $documentoId . $hoy . $usuarioId . $ext;
                if($ext == ".pdf" || $ext == ".PDF"){
                  $url = __DIR__ . '/../../util/uploads/documentoAdjunto/' . $nombreGenerado;
                }else{
                  $url = __DIR__ . '/../../util/uploads/imagenAdjunto/' . $nombreGenerado;
                }
    
                file_put_contents($url, $decode);
                $resDetalle = MovimientoNegocio::create()->movimientoBienDetalleGuardarCadena($movimientoBienId, $valor['columnaCodigo'], $nombreGenerado, $usuarioId);
              }

              if($documentoTipoId == Configuraciones::GENERAR_COTIZACION){
                if ($valor['columnaCodigo'] == 37) {
                  $resDetalle = MovimientoNegocio::create()->movimientoBienDetalleGuardarCadena($movimientoBienId, $valor['columnaCodigo'], $valor['valorDet'], $usuarioId, $valor['valorExtra']);
                }
              }
            }
          }
        }

        //Logica de correo
        //$movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);

        if ($movimientoTipo[0]["indicador"] == MovimientoTipoNegocio::INDICADOR_SALIDA) {
          $bien = BienNegocio::create()->obtenerCantidadMinima($item["bienId"], $item["unidadMedidaId"]);
          // $stockA = BienNegocio::create()->obtenerStockActual($item["bienId"], $item["organizadorId"], $bien[0]["unidad_control_id"]);
          $stockA = BienNegocio::create()->obtenerStockTotalXBienIDXUnidadMedidaId($item["bienId"], $bien[0]["unidad_control_id"]);

          if ($bien[0]["cantidad_minima"] > $stockA[0]["stock"]) {
            $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID(1);

            $bienDesc = $bien[0]["bien_desc"];
            $umDesc = $bien[0]["um_desc"];
            $asunto = $plantilla[0]["asunto"];
            $cuerpo = $plantilla[0]["cuerpo"];

            $bienDesc = $bien[0]["bien_desc"];
            $umDesc = $bien[0]["um_desc"];
            $asunto = $plantilla[0]["asunto"];
            $cuerpo = $plantilla[0]["cuerpo"];

            $destinatario = $plantilla[0]["destinatario"];
            $correosPlantilla = EmailPlantillaNegocio::create()->obtenerEmailXDestinatario($destinatario, null, null);

            $correos = '';
            foreach ($correosPlantilla as $email) {
              $correos = $correos . $email . ';';
            }
            $asunto = str_replace("[|bien_desc|]", $bienDesc, $asunto);
            $cuerpo = str_replace("[|bien_desc|]", $bienDesc, $cuerpo);
            $cuerpo = str_replace("[|bien_stock|]", number_format($stockA[0]["stock"], 2, ".", ","), $cuerpo);
            $cuerpo = str_replace("[|um_desc|]", $umDesc, $cuerpo);
            $cuerpo = str_replace("[|cantidad_minima|]", number_format($bien[0]["cantidad_minima"], 2, ".", ","), $cuerpo);

            EmailEnvioNegocio::create()->insertarEmailEnvio($correos, $asunto, $cuerpo, 1, $usuarioId);
          }
        }
        //Fin logica de correo
      }
    }
    //si el documento se a copiado guardamos las relaciones
    foreach ($documentoARelacionar as $documentoRelacion) {
      if (!ObjectUtil::isEmpty($documentoRelacion['documentoId'])) {
        if (ObjectUtil::isEmpty($documentoRelacion['documentoPadreId'])) {
          DocumentoNegocio::create()->guardarDocumentoRelacionado($documentoId, $documentoRelacion['documentoId'], $valorCheck, 1, $usuarioId);
        }
      }
    }

    // logica de envio de correo de documento
    foreach ($camposDinamicos as $indexCampos => $valor) {
      if ($valor["tipo"] == 9) {
        $fechaEmision = DateUtil::formatearCadenaACadenaBD($valor["valor"]);
        $hoy = date("Y-m-d");

        if ($fechaEmision < $hoy) {
          $this->enviarCorreoDocumentoConFechaEmisionAnterior($documentoId, $movimientoId, $usuarioId);
        }
      }
    }
    // fin envio de correo de documento
    //logica para tramos
    foreach ($detalle as $item) {
      // validaciones
      if (!ObjectUtil::isEmpty($item["bienTramoId"])) {
        Movimiento::create()->actualizarBienTramoEstado($item["bienTramoId"], $movimientoId);
      }
    }
    // fin logica para tramos

    $this->setMensajeEmergente("La operación se completó de manera satisfactoria");

    if ($documentoTipoId == $this->dtDuaId) {
      MovimientoDuaNegocio::create()->generarPorDocumentoId($documentoId, $usuarioId);
    }

    return $documento;
  }

  public function movimientoBienDetalleGuardarCadena($movimientoBienId, $columnaCodigo, $valorCadena, $usuarioId, $valorExtra = null)
  {
    return MovimientoBien::create()->movimientoBienDetalleGuardar($movimientoBienId, $columnaCodigo, $valorCadena, null, $usuarioId, $valorExtra);
  }

  public function movimientoBienDetalleEditarCadena($movimientoBienId, $columnaCodigo, $valorCadena, $usuarioId, $valorExtra = null)
  {
    return MovimientoBien::create()->movimientoBienDetalleEditarCadena($movimientoBienId, $columnaCodigo, $valorCadena, null, $usuarioId, $valorExtra);
  }

  public function movimientoBienDetalleGuardarFecha($movimientoBienId, $columnaCodigo, $valorFecha, $usuarioId)
  {
    return MovimientoBien::create()->movimientoBienDetalleGuardar($movimientoBienId, $columnaCodigo, null, $valorFecha, $usuarioId);
  }

  public function obtenerDocumentosXCriterios($opcionId, $criterios, $elemntosFiltrados, $columns, $order, $start)
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
    $documentoTipoArray = null;
    $documentoTipoIds = '';
    $columnaOrdenarIndice = '0';
    $columnaOrdenar = '';
    $formaOrdenar = '';
    $proyecto = null;
    $serieCompra = null;
    $numeroCompra = null;
    $agencia = null;

    // obtnemos el id del tipo de movimiento
    $responseMovimientoTipo = Movimiento::create()->ObtenerMovimientoTipoPorOpcion($opcionId);
    $movimientoTipoId = $responseMovimientoTipo[0]['id'];


    // 1. Obtenemos la configuracion actual del tipo de documento
    $documentoTipoArray = $criterios[0]['tipoDocumento'];

    // 2. Obtenemos la moneda
    $monedaId = $criterios[0]['monedaId'];

    // 3. Obtenemos el estado negocio de pago
    $estadoNegocioPago = $criterios[0]['estadoNegocio'];

    // 4. Obtenemos el valor de proyecto
    $proyecto = $criterios[0]['proyecto'];

    $serieCompra = $criterios[0]['serieCompra'];
    $numeroCompra = $criterios[0]['numeroCompra'];

    $progreso = $criterios[0]['progreso'];
    $prioridad = $criterios[0]['prioridad'];
    $responsable = $criterios[0]['responsable'];
    $agencia = $criterios[0]['agencia'];
    $area = $criterios[0]['area'];
    $requerimiento_tipo = $criterios[0]['requerimiento_tipo'];
    $estado_cotizacion = $criterios[0]['estado_cotizacion'];
    // for ($i = 0; count($documentoTipoArray) > $i; $i++) {
    //   $documentoTipoIds = $documentoTipoIds . '(' . $documentoTipoArray[$i] . '),';
    // }
    $documentoTipoIds = Util::convertirArrayXCadena($documentoTipoArray);
    $agenciaIds = Util::convertirArrayXCadena($agencia);
    // $documentoTipoIds = substr($documentoTipoIds, 0, -1);

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];

    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    foreach ($criterios as $item) {
      if ($item['valor'] != null || $item['valor'] != '') {
        $valor = $item['valor'];
        switch ((int) $item["tipo"]) {
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
            $numero = $valor;
            break;
          case DocumentoTipoNegocio::DATO_FECHA_EMISION:

            //                        $valor_fecha_emision = split(" - ", $valor);
            if ($valor['inicio'] != '') {
              $fechaEmisionDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);
            }
            if ($valor['fin'] != '') {
              $fechaEmisionHasta = DateUtil::formatearCadenaACadenaBD($valor['fin']);
            }
            break;
          case DocumentoTipoNegocio::DATO_FECHA_VENCIMIENTO:
            if ($valor['inicio'] != '') {
              $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);
            }
            if ($valor['fin'] != '') {
              $fechaVencimientoHasta = DateUtil::formatearCadenaACadenaBD($valor['fin']);
            }

            break;
          case DocumentoTipoNegocio::DATO_FECHA_TENTATIVA:
            if ($valor['inicio'] != '') {
              $fechaTentativaDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);
            }
            if ($valor['fin'] != '') {
              $fechaTentativaHasta = DateUtil::formatearCadenaACadenaBD($valor['fin']);
            }
            break;
          default:
        }
      }
    }
    return Movimiento::create()->obtenerDocumentosXCriterios($movimientoTipoId, $documentoTipoIds, $personaId, $codigo, $serie, $numero, $fechaEmisionDesde, $fechaEmisionHasta, $fechaVencimientoDesde, $fechaVencimientoHasta, $fechaTentativaDesde, $fechaTentativaHasta, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $monedaId, $estadoNegocioPago, $proyecto, $serieCompra, $numeroCompra, $progreso, $prioridad, $responsable, $agenciaIds, $area, $requerimiento_tipo, $estado_cotizacion);
  }

  public function obtenerDocumentosXCriteriosExcel($opcionId, $criterios)
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
    $documentoTipoArray = null;
    $documentoTipoIds = '';
    $columnaOrdenarIndice = '0';
    $columnaOrdenar = '';
    $formaOrdenar = '';

    // obtnemos el id del tipo de movimiento
    $responseMovimientoTipo = Movimiento::create()->ObtenerMovimientoTipoPorOpcion($opcionId);
    $movimientoTipoId = $responseMovimientoTipo[0]['id'];

    // 1. Obtenemos la configuracion actual del tipo de documento
    $documentoTipoArray = $criterios[0]['tipoDocumento'];

    // for ($i = 0; count($documentoTipoArray) > $i; $i++) {
    //   $documentoTipoIds = $documentoTipoIds . '(' . $documentoTipoArray[$i] . '),';
    // }
    $documentoTipoIds = Util::convertirArrayXCadena($documentoTipoArray);
    // $documentoTipoIds = substr($documentoTipoIds, 0, -1);
    // $columnaOrdenarIndice = $order[0]['column'];
    // $formaOrdenar = $order[0]['dir'];
    // $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    foreach ($criterios as $item) {
      if ($item['valor'] != null || $item['valor'] != '') {
        $valor = $item['valor'];
        switch ((int) $item["tipo"]) {
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
            $numero = $valor;
            break;
          case DocumentoTipoNegocio::DATO_FECHA_EMISION:
            // $valor_fecha_emision = split(" - ", $valor);
            if ($valor['inicio'] != '') {
              $fechaEmisionDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);
            }
            if ($valor['fin'] != '') {
              $fechaEmisionHasta = DateUtil::formatearCadenaACadenaBD($valor['fin']);
            }
            break;
          case DocumentoTipoNegocio::DATO_FECHA_VENCIMIENTO:
            if ($valor['inicio'] != '') {
              $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);
            }
            if ($valor['fin'] != '') {
              $fechaVencimientoHasta = DateUtil::formatearCadenaACadenaBD($valor['fin']);
            }

            break;
          case DocumentoTipoNegocio::DATO_FECHA_TENTATIVA:
            if ($valor['inicio'] != '') {
              $fechaTentativaDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);
            }
            if ($valor['fin'] != '') {
              $fechaTentativaHasta = DateUtil::formatearCadenaACadenaBD($valor['fin']);
            }
            break;
          default:
        }
      }
    }
    return Movimiento::create()->obtenerDocumentosXCriteriosExcel($movimientoTipoId, $documentoTipoIds, $personaId, $codigo, $serie, $numero, $fechaEmisionDesde, $fechaEmisionHasta, $fechaVencimientoDesde, $fechaVencimientoHasta, $fechaTentativaDesde, $fechaTentativaHasta);
  }

  public function ObtenerTotalDeRegistros()
  {
    return Movimiento::create()->ObtenerTotalDeRegistros();
  }

  public function obtenerCantidadDocumentosXCriterio($opcionId, $criterios, $elemntosFiltrados, $columns, $order, $start)
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
    $documentoTipoArray = null;
    $documentoTipoIds = '';
    $columnaOrdenarIndice = '0';
    $columnaOrdenar = '';
    $formaOrdenar = '';

    //obtnemos el id del tipo de movimiento
    $responseMovimientoTipo = Movimiento::create()->ObtenerMovimientoTipoPorOpcion($opcionId);
    $movimientoTipoId = $responseMovimientoTipo[0]['id'];

    // 1. Obtenemos la configuracion actual del tipo de documento
    $documentoTipoArray = $criterios[0]['tipoDocumento'];
    // 2. Obtenemos la moneda
    $monedaId = $criterios[0]['monedaId'];
    // 3. Obtenemos el estado negocio de pago
    $estadoNegocioPago = $criterios[0]['estadoNegocio'];

    // for ($i = 0; count($documentoTipoArray) > $i; $i++) {
    //   $documentoTipoIds = $documentoTipoIds . '(' . $documentoTipoArray[$i] . '),';
    // }
    $documentoTipoIds = Util::convertirArrayXCadena($documentoTipoArray);
    // $documentoTipoIds = substr($documentoTipoIds, 0, -1);

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];

    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    foreach ($criterios as $item) {
      if ($item['valor'] != null || $item['valor'] != '') {
        $valor = $item['valor'];
        switch ((int) $item["tipo"]) {
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
            $numero = $valor;
            break;
          case DocumentoTipoNegocio::DATO_FECHA_EMISION:
            // $valor_fecha_emision = split(" - ", $valor);
            if ($valor['inicio'] != '') {
              $fechaEmisionDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);
            }
            if ($valor['fin'] != '') {
              $fechaEmisionHasta = DateUtil::formatearCadenaACadenaBD($valor['fin']);
            }
            break;
          case DocumentoTipoNegocio::DATO_FECHA_VENCIMIENTO:

            if ($valor['inicio'] != '') {
              $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);
            }
            if ($valor['fin'] != '') {
              $fechaVencimientoHasta = DateUtil::formatearCadenaACadenaBD($valor['fin']);
            }

            break;
          case DocumentoTipoNegocio::DATO_FECHA_TENTATIVA:
            if ($valor['inicio'] != '') {
              $fechaTentativaDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);
            }
            if ($valor['fin'] != '') {
              $fechaTentativaHasta = DateUtil::formatearCadenaACadenaBD($valor['fin']);
            }
            break;
          default:
        }
      }
    }
    return Movimiento::create()->obtenerCantidadDocumentosXCriterios($movimientoTipoId, $documentoTipoIds, $personaId, $codigo, $serie, $numero, $fechaEmisionDesde, $fechaEmisionHasta, $fechaVencimientoDesde, $fechaVencimientoHasta, $fechaTentativaDesde, $fechaTentativaHasta, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $monedaId, $estadoNegocioPago);
  }

  public function obtenerMovimientoTipoAcciones($opcionId)
  {
    $responseMovimientoTipo = Movimiento::create()->ObtenerMovimientoTipoPorOpcion($opcionId);
    $movimientoTipoId = $responseMovimientoTipo[0]['id'];
    return Movimiento::create()->obtenerMovimientoTipoAcciones($movimientoTipoId);
  }

  // obtener busqueda para pagos
  public function enviarEImprimir($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId)
  {
    // $nombre_fichero = __DIR__ . '/../../vistas/com/movimiento/plantillas/' . $documentoTipoId . ".php";

    // if (!file_exists($nombre_fichero)) {
    //   throw new WarningException("No existe el archivo del documento para imprimir.");
    // }
    $documento = $this->guardar($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId);

    return $this->imprimir($documento[0]['vout_id'], $documentoTipoId);
  }

  public function imprimir($documentoId, $documentoTipoId)
  {
    $igv = 18;
    $arrayDetalle = array();
    $respuesta = new ObjectUtil();

    $respuesta->documentoTipoId = $documentoTipoId;
    $datoDocumento = DocumentoNegocio::create()->obtenerXId($documentoId, $documentoTipoId);

    if (ObjectUtil::isEmpty($datoDocumento)) {
      throw new WarningException("No se encontró el documento");
    }

    $respuesta->dataDocumento = $datoDocumento;

    $movimientoId = $datoDocumento[0]["movimiento_id"];

    $documentoDatoValor = DocumentoDatoValorNegocio::create()->obtenerXIdDocumento($documentoId);

    $respuesta->documentoDatoValor = $documentoDatoValor;

    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);

    // SE NECESITA TAL Y COMO DEVUELVE DE LA BASE PARA iREPORT
    $respuesta->documentoDetalle = $documentoDetalle;

    $total = 0.00;
    foreach ($documentoDetalle as $detalle) {
      $subTotal = $detalle['cantidad'] * $detalle['valor_monetario'];
      $descripcionEditada = $detalle['bien_descripcion_editada'];
      array_push($arrayDetalle, $this->getDetalle($detalle['movimiento_bien_id'],"", $detalle['cantidad'], (!ObjectUtil::isEmpty($descripcionEditada)) ? $descripcionEditada : $detalle['bien_descripcion'], $detalle['valor_monetario'], $subTotal, $detalle['unidad_medida_descripcion'], $detalle['simbolo'], $detalle['bien_codigo'], $detalle['unidad_medida_id'], $detalle['bien_id'], $detalle['ad_valorem'], $detalle['movimiento_bien_comentario'], $detalle["bien_tipo_descripcion"], $detalle["codigo_contable"], $detalle["agencia_descripcion"], $detalle['ticket'], $detalle['centro_costo_descripcion'], $detalle['precio_postor1'], $detalle['precio_postor2'], $detalle['precio_postor3'], $detalle['postor_ganador_id'], $detalle['es_compra'], $detalle['cantidad_solicitada'], null, null));
      $total += $subTotal;
    }

    $respuesta->detalle = $arrayDetalle;
    $respuesta->valorIgv = $igv;
    $enLetra = new EnLetras();
    // $respuesta->totalEnTexto = $enLetra->ValorEnLetras($datoDocumento[0]['total']);

    $respuesta->totalEnTexto = $enLetra->ValorEnLetras($datoDocumento[0]['total'], $datoDocumento[0]['moneda_id']);

    // datos empresa
    $empresaId = $datoDocumento[0]["empresa_id"];
    $dataEmpresa = EmpresaNegocio::create()->obtenerEmpresaXId($empresaId);
    $respuesta->dataEmpresa = $dataEmpresa;

    // obtener documentos relacionados
    $respuesta->documentoRelacionado = DocumentoNegocio::create()->obtenerDocumentoRelacionadoImpresion($documentoId);

    // obtener configuracion de las columnas de movimiento_tipo
    $res = MovimientoTipoNegocio::create()->obtenerXDocumentoId($documentoId);
    if (!ObjectUtil::isEmpty($res)) {
      $movimientoTipoId = $res[0]['movimiento_tipo_id'];
      $respuesta->movimientoTipoColumna = MovimientoTipoNegocio::create()->obtenerMovimientoTipoColumna($movimientoTipoId);
    }

    return $respuesta;
  }

  public function anularDocumentoMensaje($documentoId, $motivoAnulacion, $documentoEstadoId, $usuarioId)
  {
    if (ObjectUtil::isEmpty($motivoAnulacion)) {
      throw new WarningException("Ingrese motivo de anulación.");
    }

    Documento::create()->actualizarMotivoAnulacionXDocumentoId($documentoId, $motivoAnulacion);

    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    $idNegocio = $documento[0]['identificador_negocio'];
    $serie = $documento[0]["serie"];

    return MovimientoNegocio::create()->anularDocumento($documentoId, $documentoEstadoId, $usuarioId, $idNegocio, $serie);
  }

  public function anularDocumento($documentoId, $documentoEstadoId, $usuarioId, $idNegocio, $serie)
  {
    // ANULAR LA RECEPCION DE TRANSFERENCIA VIRTUAL O FISICO
    $res = MovimientoNegocio::create()->obtenerDocumentoRelacionadoTipoRecepcion($documentoId);

    if (!ObjectUtil::isEmpty($res)) {
      $res2 = MovimientoNegocio::create()->anular($res[0]['documento_relacionado_id'], $documentoEstadoId, $usuarioId, $idNegocio, $serie);
    }

    $documentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $detalle = MovimientoBien::create()->obtenerMovimientoBienXRelacionConsolidado($documentoId);
    if ($documentoTipo[0]['id'] == Configuraciones::REQUERIMIENTO_AREA) {
      $resReservaIngresoSalida = DocumentoNegocio::create()->obtenerDocumentosRelacionadosXIngresoSalidaReserva($documentoId);
      if (!ObjectUtil::isEmpty($resReservaIngresoSalida)) {
        foreach ($resReservaIngresoSalida as $itemRS) {
          $res2 = MovimientoNegocio::create()->anular($itemRS['documento_id'], 2, $usuarioId, $itemRS['identificador_negocio'], $itemRS['serie']);
        }
      }
      foreach ($detalle as $item) {
        MovimientoBien::create()->editarMovimientoBienConsolidadoRelacionadoxId($item['id'], null);
      }
    }

    return MovimientoNegocio::create()->anular($documentoId, $documentoEstadoId, $usuarioId, $idNegocio, $serie);
  }

  public function anular($documentoId, $documentoEstadoId, $usuarioId, $idNegocio, $serie)
  {
    $dataMovimientoTipo = MovimientoTipoNegocio::create()->obtenerXDocumentoId($documentoId);

    if ($dataMovimientoTipo[0]["indicador"] == MovimientoTipoNegocio::INDICADOR_ENTRADA) {
      // validacion que al eliminar el documento no resulte negativo.
      $dataDocumento = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($documentoId);

      $dataMovimientoBien = MovimientoBien::create()->obtenerXIdMovimiento($dataDocumento[0]['movimiento_id']);
      foreach ($dataMovimientoBien as $index => $item) {
        if ($item['bien_tipo_id'] != -1) {
          // obtener las fechas posteriores de los documentos de salida
          $dataFechas = DocumentoNegocio::create()->obtenerFechasPosterioresDocumentosSalidas(
            $dataMovimientoTipo[0]['fecha_emision'],
            $item['bien_id'],
            $item['organizador_id']
          );

          if (!ObjectUtil::isEmpty($dataFechas)) {
            $dataFechaInicial = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
            $fechaInicial = $dataFechaInicial[0]['primera_fecha'];

            foreach ($dataFechas as $itemFecha) {
              $fechaFinal = $itemFecha['fecha_emision'];
              // obtener stock
              $stock = BienNegocio::create()->obtenerStockEntreFechasXBienIdXOrganizadorIdXUnidadMedidaId($item['bien_id'], $item['organizador_id'], $item['unidad_medida_id'], $fechaInicial, $fechaFinal);

              $stockControlar = (!ObjectUtil::isEmpty($stock)) ? $stock[0]["stock"] : 0;

              if ((floatval($stockControlar) - floatval($item['cantidad'])) < 0) {
                throw new WarningException("No se puede eliminar el documento.<br>"
                  . " Stock en fecha " . date_format((date_create($fechaFinal)), 'd/m/Y') . ": " . number_format($stockControlar, 2, ".", ",") . "<br>"
                  . " Producto: " . $item['bien_descripcion'] . "<br>"
                  . " Cantidad en documento: " . number_format($item['cantidad'], 2, ".", ","));
              }
            }
          }
        }
      }
    }

    // ANULA LOS ASIENTOS Y RELACIONADOS A VENTAS
    if (
      $idNegocio == DocumentoTipoNegocio::IN_FACTURA_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_BOLETA_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_NOTA_DEBITO_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA
    ) {
      $respuestaAnularAsiento = ContVoucherNegocio::create()->anularContVocuherRelacionXIdentificadorIdXIdentificadorNegocio($documentoId, ContVoucherNegocio::IDENTIFICADOR_REGISTRO_VENTAS);
      if ($respuestaAnularAsiento[0]['vout_exito'] != 1) {
        throw new WarningException($respuestaAnularAsiento[0]['vout_mensaje']);
      }
    }

    $respuestaAnular = DocumentoNegocio::create()->anular($documentoId);

    if ($respuestaAnular[0]['vout_exito'] == 1) {
      $this->setMensajeEmergente($respuestaAnular[0]['vout_mensaje']);

      $respuestaAnularDocumentoEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, $documentoEstadoId, $usuarioId);
      if ($respuestaAnularDocumentoEstado[0]['vout_exito'] != 1) {
        throw new WarningException("No se actualizó el estado del documento.");
      }

      if ($dataMovimientoTipo[0]['dtipo_identificador_negocio'] == DocumentoTipoNegocio::IN_LIQUIDACION_VENTA) {
        $dataDocumentoRelacion = MovimientoNegocio::create()->obtenerSoloDocumentosRelacionados($documentoId);
        if (!ObjectUtil::isEmpty($dataDocumentoRelacion)) {
          foreach ($dataDocumentoRelacion as $itemRelacion) {
            if ($itemRelacion['identificador_negocio'] == DocumentoTipoNegocio::IN_COTIZACION_VENTA && !ObjectUtil::isEmpty($itemRelacion['relacion_id'])) {
              $respuestaEstadoRelacionDocumentoEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($itemRelacion['documento_relacionado_id'], 1, $usuarioId);
              if ($respuestaEstadoRelacionDocumentoEstado[0]['vout_exito'] != 1) {
                throw new WarningException("No se actualizó el estado del documento relación " . $itemRelacion['serie_numero']);
              }
            }
            if ($dataMovimientoTipo[0]['movimiento_tipo_id'] == "141" && $dataMovimientoTipo[0]['dtipo_identificador_negocio'] == DocumentoTipoNegocio::IN_LIQUIDACION_VENTA) {
              MovimientoNegocio::create()->eliminarRelacionDocumento($documentoId, $itemRelacion['documento_relacionado_id'], $usuarioId);
            }
          }
        }
      }

      // actualizamos el estado de efact_estado_anulacion a 0 (pendiente)
      if (
        $idNegocio == DocumentoTipoNegocio::IN_BOLETA_VENTA ||
        ($idNegocio == DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA && $serie[0] == 'B')
      ) {
        Documento::create()->actualizarEfactEstadoAnulacionXDocumentoId($documentoId, 0);
      }


      $dataEmpresa = EmpresaNegocio::create()->obtenerEmpresaXDocumentoId($documentoId);
      if ($dataEmpresa[0]['efactura'] == 1 && ($idNegocio == DocumentoTipoNegocio::IN_FACTURA_VENTA ||
        ($idNegocio == DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA && $serie[0] == 'F'))) {
        $this->anularFacturaElectronica($documentoId);
      }
      if ($dataEmpresa[0]['efactura'] == 2 && ($idNegocio == DocumentoTipoNegocio::IN_FACTURA_VENTA ||
        ($idNegocio == DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA && $serie[0] == 'F'))) {
        $this->anularFacturaElectronicaNubefact($documentoId);
      }
    } else {
      throw new WarningException($respuestaAnular[0]['vout_mensaje']);
    }
  }

  public function anularFacturaElectronica($documentoId)
  {
    $comprobanteElectronico = new stdClass();
    // Obtenemos Datos
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);

    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }

    $idNegocio = $documento[0]['identificador_negocio'];

    if (
      $idNegocio == DocumentoTipoNegocio::IN_BOLETA_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_FACTURA_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_NOTA_DEBITO_COMPRA
    ) {

      $empresa = EmpresaNegocio::create()->obtenerEmpresaXId($documento[0]['empresa_id']);

      $ubigeoEmpresa = PersonaNegocio::create()->obtenerUbigeoXId($empresa[0]["ubigeo_id"]);
      if (ObjectUtil::isEmpty($ubigeoEmpresa)) {
        throw new WarningException("No se especificó el ubigeo del emisor");
      }

      $persona = PersonaNegocio::create()->obtenerPersonaXId($documento[0]["persona_id"]);
      if (ObjectUtil::isEmpty($persona)) {
        throw new WarningException("No se encontró a la persona del documento");
      }
      $ubigeo = PersonaNegocio::create()->obtenerUbigeoXId($documento[0]["ubigeo_id"]);
      if (ObjectUtil::isEmpty($ubigeo)) {
        throw new WarningException("No se especificó el ubigeo del receptor");
      }
      $enLetras = new EnLetras();
      $importeLetras = $enLetras->ValorEnLetras($documento[0]["total"], $documento[0]['moneda_id']);

      $comprobanteElectronico->emisorNroDocumento = $empresa[0]['ruc'];
      $comprobanteElectronico->emisorTipoDocumento = $empresa[0]['sunat_tipo_documento'];
      $comprobanteElectronico->emisorUbigeo = $ubigeoEmpresa[0]['ubigeo_codigo'];
      $comprobanteElectronico->emisorDireccion = $empresa[0]['direccion_fe'];
      $comprobanteElectronico->emisorUrbanizacion = $empresa[0]['urbanizacion'];
      $comprobanteElectronico->emisorDepartamento = $ubigeoEmpresa[0]['ubigeo_dep'];
      $comprobanteElectronico->emisorProvincia = $ubigeoEmpresa[0]['ubigeo_prov'];
      $comprobanteElectronico->emisorDistrito = $ubigeoEmpresa[0]['ubigeo_dist'];
      $comprobanteElectronico->emisorNombreLegal = $empresa[0]['razon_social'];
      $comprobanteElectronico->emisorNombreComercial = $empresa[0]['nombre_comercial'];

      // factura
      $comprobanteElectronico->docFechaEmision = date("Y-m-d"); //$documento[0]["fecha_emision"];
      $comprobanteElectronico->docFechaReferencia = substr($documento[0]["fecha_emision"], 0, 10);
      $comprobanteElectronico->docSecuencial = $documento[0]['nro_secuencial_baja'];

      // Detalle
      // Det0
      $serieDoc = $documento[0]["serie"];
      $numeroDoc = $documento[0]["numero"];

      if ($idNegocio == DocumentoTipoNegocio::IN_NOTA_DEBITO_COMPRA) {
        $serieNum = DocumentoNegocio::create()->obtenerSerieNumeroXDocumentoId($documentoId);

        $serieDoc = $serieNum[0]["serie"];
        $numeroDoc = $serieNum[0]["numero"];
      }

      //VALIDA SERIE
      if ($idNegocio == DocumentoTipoNegocio::IN_BOLETA_VENTA && $serieDoc[0] != 'B') {
        throw new WarningException("La serie de la boleta a eliminar debe empezar con B");
      } else if ($idNegocio == DocumentoTipoNegocio::IN_FACTURA_VENTA && $serieDoc[0] != 'F') {
        throw new WarningException("La serie de la factura a eliminar debe empezar con F");
      } else if ($serieDoc[0] != 'B' && $serieDoc[0] != 'F') {
        throw new WarningException("La serie del documento a eliminar debe empezar con F o B");
      }

      //VALIDA MOTIVO ANULACION
      if (ObjectUtil::isEmpty($documento[0]['motivo_anulacion'])) {
        throw new WarningException("Motivo de anulación es obligatorio.");
      }

      $items[0][0] = 1;
      $items[0][1] = $documento[0]["sunat_tipo_doc_rel"];
      $items[0][2] = $serieDoc;
      $items[0][3] = $numeroDoc;
      $items[0][4] = $documento[0]['motivo_anulacion'];

      $comprobanteElectronico->bajas = $items;
      $comprobanteElectronico->usuarioSunatSOL = $empresa[0]['usuario_sunat_sol'];
      $comprobanteElectronico->claveSunatSOL = $empresa[0]['clave_sunat_sol'];

      // $comprobanteElectronico->usuarioOSE = 'IMAGINA_TECHNOLOGIES_20600064941';
      // $comprobanteElectronico->claveOSE = 'IMAGINA_TECHNOLOGIES';
      $comprobanteElectronico = (array) $comprobanteElectronico;

      $client = new SoapClient(Configuraciones::EFACT_URL);
      $resultado = $client->procesarComunicacionBaja($comprobanteElectronico)->procesarComunicacionBajaResult;
      // $this->setMensajeEmergente("Resultado EFACT: ".$resultado);
      // VALIDAR EL RESULTADO
      $this->validarResultadoEfactura($resultado);
      // var_dump($comprobanteElectronico);

      if (strpos($resultado, 'ticket') !== false) {
        $nroticket = explode(':', $resultado);
        $ticket = trim($nroticket[2]);
      }

      // SI TODO ESTA BIEN ACTUALIZAMOS EL NUMERO SECUENCIAL DE BAJA Y EL TICKET QUE SE GENERÓ
      DocumentoNegocio::create()->actualizarNroSecuencialBajaXDocumentoId($documentoId, $documento[0]['nro_secuencial_baja'], $ticket);
    }
  }

  public function anularDocumentoElectronicoPorResumenDiario()
  {
    $comprobanteElectronico = new stdClass();
    // obtenemos id de documentos que se enviaran a resumen
    $documentosResumen = DocumentoNegocio::create()->obtenerIdDocumentosResumenDiario();
    $i = 0;

    if (ObjectUtil::isEmpty($documentosResumen)) {
      throw new WarningException("No se encontraron documentos para realizar baja por resumen diario");
    }

    foreach ($documentosResumen as $index => $fila) {
      //arreglo con los id del documento
      $idDocumentos[$index] = $fila['documentoId'];

      $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($fila['documentoId']);

      $items[$index][0] = $i + 1; //Número de fila
      $serieDoc = $documento[0]["serie"];
      $numeroDoc = $documento[0]["numero"];
      $items[$index][1] = $serieDoc . '-' . $numeroDoc; //Número de serie del documento – Numero correlativo
      $items[$index][2] = $documento[0]["sunat_tipo_doc_rel"]; //Tipo de documento
      $items[$index][3] = $documento[0]["sunat_moneda"]; //moneda

      $persona = PersonaNegocio::create()->obtenerPersonaXId($documento[0]["persona_id"]);

      $items[$index][4] = $persona[0]["codigo_identificacion"]; //Número de documento de Identidad del adquirente o usuario
      $items[$index][5] = $persona[0]["sunat_tipo_documento"]; //Tipo de documento de Identidad del adquirente o usuario
      $items[$index][6] = 3; //Estado del ítem (3 es anulado)
      $items[$index][7] = $documento[0]["total"] * 1; //Importe total de la venta
      $items[$index][8] = $documento[0]["total"] / 1.18; //Total valor de venta - operaciones gravadas
      $items[$index][9] = 0.0; //Total valor de venta - operaciones exoneradas
      $items[$index][10] = 0.0; //Total valor de venta - operaciones inafectas
      $items[$index][11] = 0.0; //Total Valor Venta operaciones Gratuitas
      $items[$index][12] = $documento[0]["total"] * 1 - $documento[0]["total"] / 1.18;  //Total IGV

      $items[$index][13] = null; //nroDocumentoRelacionado
      $items[$index][14] = null; //tipoDocumentoRelacionado

      $idNegocio = $documento[0]['identificador_negocio'];

      if ($idNegocio == DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA && $serieDoc[0] == 'B') {

        //OBTENEMOS LOS DOCUMENTOS RELACIONADOS
        $docRelacion = DocumentoNegocio::create()->obtenerDocumentoRelacionadoActivoXDocumentoId($fila['documentoId']);

        foreach ($docRelacion as $indRel => $itemRel) {
          if ($itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_BOLETA_VENTA) {
            $items[$index][13] = $itemRel["serie_relacion"] . '-' . $itemRel["numero_relacion"]; //nroDocumentoRelacionado
            $items[$index][14] = $itemRel['sunat_tipo_doc_rel']; //tipoDocumentoRelacionado
          }
        }
      }
      $i++;
    }
    // Obtenemos Datos de emisor
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentosResumen[0]["documentoId"]);
    $empresa = EmpresaNegocio::create()->obtenerEmpresaXId($documento[0]['empresa_id']);
    $ubigeoEmpresa = PersonaNegocio::create()->obtenerUbigeoXId($empresa[0]["ubigeo_id"]);

    $comprobanteElectronico->emisorNroDocumento = $empresa[0]['ruc'];
    $comprobanteElectronico->emisorTipoDocumento = $empresa[0]['sunat_tipo_documento'];
    $comprobanteElectronico->emisorUbigeo = $ubigeoEmpresa[0]['ubigeo_codigo'];
    $comprobanteElectronico->emisorDireccion = $empresa[0]['direccion_fe'];
    $comprobanteElectronico->emisorUrbanizacion = $empresa[0]['urbanizacion'];
    $comprobanteElectronico->emisorDepartamento = $ubigeoEmpresa[0]['ubigeo_dep'];
    $comprobanteElectronico->emisorProvincia = $ubigeoEmpresa[0]['ubigeo_prov'];
    $comprobanteElectronico->emisorDistrito = $ubigeoEmpresa[0]['ubigeo_dist'];
    //        $comprobanteElectronico->emisorNombreLegal = $empresa[0]['razon_social'];
    $comprobanteElectronico->emisorNombreLegal = 'Minapp S.A';
    $comprobanteElectronico->emisorNombreComercial = $empresa[0]['nombre_comercial'];

    $comprobanteElectronico->docFechaEmision = date('Y-m-d'); //$documento[0]["fecha_emision"];
    $comprobanteElectronico->docFechaReferencia = $documento[0]["fecha_emision"];
    $comprobanteElectronico->docSecuencial = 1;

    $comprobanteElectronico->resumenes = $items;
    $comprobanteElectronico->usuarioSunatSOL = $empresa[0]['usuario_sunat_sol'];
    $comprobanteElectronico->claveSunatSOL = $empresa[0]['clave_sunat_sol'];
    $comprobanteElectronico->usuarioOSE = 'IMAGINA_TECHNOLOGIES_20600064941';
    $comprobanteElectronico->claveOSE = 'IMAGINA_TECHNOLOGIES';
    $comprobanteElectronico = (array) $comprobanteElectronico;
    // var_dump($comprobanteElectronico);
    // exit();
    $client = new SoapClient(Configuraciones::EFACT_URL);

    $resultado = $client->procesarResumenDiarioNuevo($comprobanteElectronico)->procesarResumenDiarioNuevoResult;
    // $this->setMensajeEmergente("Resultado EFACT: " . $resultado);
    // VALIDAR EL RESULTADO
    $this->validarResultadoEfactura($resultado);
    // var_dump($comprobanteElectronico);

    if (strpos($resultado, 'ticket') !== false) {
      $nroticket = explode(':', $resultado);
      $ticket = trim($nroticket[2]);
    }

    for ($j = 0; $j < count($idDocumentos); $j++) {
      DocumentoNegocio::create()->actualizarEstadoEfactAnulacionXDocumentoId($idDocumentos[$j], 1, $ticket);
    }
  }

  public function aprobar($documentoId, $documentoEstadoId, $usuarioId)
  {
    // $respuestaAnular = DocumentoNegocio::create()->anular($documentoId);
    $respuestaAnularDocumentoEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, $documentoEstadoId, $usuarioId);
    if ($respuestaAnularDocumentoEstado[0]['vout_exito'] != 1) {
      throw new WarningException("No se Actualizo Documento estado");
    } else {
      // $this->setMensajeEmergente($respuestaAnular[0]['vout_mensaje']);
      $this->setMensajeEmergente($respuestaAnularDocumentoEstado[0]['vout_mensaje']);
    }
  }

  public function visualizarDocumento($documentoId, $movimientoId)
  {
    $arrayDetalle = array();
    $respuesta = new ObjectUtil();

    $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($documentoId);
    $respuesta->comentarioDocumento = DocumentoNegocio::create()->obtenerComentarioDocumento($documentoId);
    $respuesta->direccionEmpresa = DocumentoNegocio::create()->obtenerDireccionEmpresa($documentoId);
    $respuesta->listaComprobacion = DocumentoNegocio::create()->obtenerListaComprobacion($documentoId);
    $respuesta->historialDocumento = DocumentoNegocio::create()->obtenerDocumentoHistorial($documentoId);
    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);

    // if (ObjectUtil::isEmpty($documentoDetalle)) {
    //   throw new WarningException("No se encontró detalles de este documento");
    // }
    $respuesta->dataPostores = Documento::create()->obtenerDocumentoDetalleDatos($documentoId);

    if (!ObjectUtil::isEmpty($documentoDetalle)) {
      $total = 0.00;
      foreach ($documentoDetalle as $detalle) {
        $resMovimientoBienDetalleReverva = MovimientoBien::create()->movimientoBienDetalleObtenerReservaRequerimientoXMovimientoBienId($detalle['movimiento_bien_id']);
        $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleXMovimientoBienId($detalle['movimiento_bien_id']);
        $subTotal = $detalle['cantidad'] * $detalle['valor_monetario']; // + $detalle['ad_valorem']
        array_push($arrayDetalle, $this->getDetalle($detalle['movimiento_bien_id'] ,$detalle['organizador_descripcion'], $detalle['cantidad'], $detalle['bien_descripcion'], $detalle['valor_monetario'], $subTotal, $detalle['unidad_medida_descripcion'], $detalle['simbolo'], $detalle['bien_codigo'], $detalle['unidad_medida_id'], $detalle["bien_id"], $detalle["ad_valorem"], $detalle["movimiento_bien_comentario"], $detalle["bien_tipo_descripcion"], $detalle["codigo_contable"], $detalle["agencia_descripcion"], $detalle["ticket"], $detalle["centro_costo_descripcion"], $detalle["precio_postor1"], $detalle["precio_postor2"], $detalle["precio_postor3"], $detalle["postor_ganador_id"], $detalle["es_compra"], $detalle["cantidad_solicitada"], $resMovimientoBienDetalle, $resMovimientoBienDetalleReverva));
        $total += $subTotal;
      }
    }

    $respuesta->detalleDocumento = $arrayDetalle;
    return $respuesta;
  }

  private function getDetalle($movimientoBienId ,$organizador, $cantidad, $descripcion, $precioUnitario, $importe, $unidadMedida, $simbolo, $bien_codigo, $unidadMedidaID, $bienId, $adValorem = 0, $movimientoBienComentario = '', $bienTipoDescripcion = '', $codigoContable = '', $agenciaDescripcion = '', $ticket = '', $centro_costo_descripcion = '', $precio_postor1 = '', $precio_postor2 = '', $precio_postor3 = null, $postor_ganador_id = null, $es_compra = '', $cantidad_solicitada = '', $movimientoBienDetalle = null, $estadoReserva = null)
  {
    $detalle = new stdClass();
    $detalle->movimientoBienId = $movimientoBienId;
    $detalle->organizador = $organizador;
    $detalle->cantidad = $cantidad;
    $detalle->descripcion = $descripcion;
    $detalle->precioUnitario = $precioUnitario;
    $detalle->importe = $importe;
    $detalle->unidadMedida = $unidadMedida;
    $detalle->simbolo = $simbolo;
    $detalle->bien_codigo = $bien_codigo;
    $detalle->unidadMedidaId = $unidadMedidaID;
    $detalle->bienId = $bienId;
    $detalle->adValorem = $adValorem;
    $detalle->movimientoBienComentario = $movimientoBienComentario;
    $detalle->bienTipoDescripcion = $bienTipoDescripcion;
    $detalle->codigoContable = $codigoContable;
    $detalle->agenciaDescripcion = $agenciaDescripcion;
    $detalle->ticket = $ticket;
    $detalle->centro_costo_descripcion  = $centro_costo_descripcion;
    $detalle->precio_postor1  = $precio_postor1;
    $detalle->precio_postor2  = $precio_postor2;
    $detalle->precio_postor3  = $precio_postor3;
    $detalle->postor_ganador_id  = $postor_ganador_id;
    $detalle->es_compra  = $es_compra;
    $detalle->cantidad_solicitada  = $cantidad_solicitada;
    $detalle->movimiento_bien_detalle = $movimientoBienDetalle;
    $detalle->estadoReserva = $estadoReserva;
    return $detalle;
  }

  public function obtenerStockAControlar($opcionId, $bienId, $organizadorId, $unidadMedidaId, $cantidad, $fechaEmision = null, $organizadorDestinoId = null)
  {
    if (ObjectUtil::isEmpty($organizadorId))
      return -1;
    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    if (!ObjectUtil::isEmpty($movimientoTipo)) {
      if ($movimientoTipo[0]["indicador"] == MovimientoTipoNegocio::INDICADOR_SALIDA) {
        $bien = BienNegocio::create()->getBien($bienId);
        if ($bien[0]['bien_tipo_id'] == -1) {
          return -1;
        } else {
          $dataFechas = [];
          if (!ObjectUtil::isEmpty($fechaEmision)) {
            $dataFechas = DocumentoNegocio::create()->obtenerFechasPosterioresDocumentosSalidas(
              $fechaEmision,
              $bienId,
              $organizadorId
            );
          }

          if (!ObjectUtil::isEmpty($dataFechas)) {
            $arrayFecha = array("fecha_emision" => $fechaEmision);
            array_push($dataFechas, $arrayFecha);
            array_multisort($dataFechas);

            //validamos stock por fecha posterior o igual a fecha emision
            $dataFechaInicial = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
            $fechaInicial = $dataFechaInicial[0]['primera_fecha'];

            foreach ($dataFechas as $itemFecha) {
              $fechaFinal = $itemFecha['fecha_emision'];
              //obtener stock
              $stock = BienNegocio::create()->obtenerStockEntreFechasXBienIdXOrganizadorIdXUnidadMedidaId($bienId, $organizadorId, $unidadMedidaId, $fechaInicial, $fechaFinal, $organizadorDestinoId);

              $stockControlar = (!ObjectUtil::isEmpty($stock)) ? $stock[0]["stock"] : 0;

              if ((floatval($stockControlar) - floatval($cantidad)) < 0) {
                throw new WarningException("No cuenta con stock suficiente en el almacén seleccionado.<br>"
                  . " Stock en fecha " . date_format((date_create($fechaFinal)), 'd/m/Y') . ": " . number_format($stockControlar, 2, ".", ",") . "<br>"
                  . " Producto: " . $bien[0]['descripcion'] . "<br>"
                  . " Cantidad: " . $cantidad);
              }
            }
          } else {
            // stock hasta fecha actual
            $stock = BienNegocio::create()->obtenerStockActual($bienId, $organizadorId, $unidadMedidaId, $organizadorDestinoId);
            // obtenerStockBase($organizadorId, $bienId);
            $stockControlar = (!ObjectUtil::isEmpty($stock)) ? $stock[0]["stock"] : 0;
            if ((floatval($stockControlar) - floatval($cantidad)) < 0) {
              throw new WarningException("No cuenta con stock suficiente en el almacén seleccionado.<br>"
                . " Stock: " . number_format($stockControlar, 2, ".", ",") . "<br>"
                . " Producto: " . $bien[0]['descripcion'] . "<br>"
                . " Cantidad: " . $cantidad);
            } else {
              return $stockControlar;
            }
          }
        }
      } else {
        return -1;
      }
    } else {
      return 0;
    }
  }

  private function guardarDeExcel($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $guardar)
  {
    $respuesta = new stdClass();
    try {
      $this->beginTransaction();
      MovimientoNegocio::create()->guardar($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, null, null);
      $this->commitTransaction();

      $respuesta->exito = true;
      $respuesta->mensaje = "Éxito";
    } catch (WarningException $we) {
      $this->rollbackTransaction();
      // Registrar en el excel el error
      ExcelNegocio::create()->generarExcelMovimientosErrores($opcionId, $documentoTipoId, $camposDinamicos, $detalle, $we->getMessage());

      $respuesta->exito = false;
      $respuesta->mensaje = $we->getMessage();
    } catch (ModeloException $me) {
      $this->rollbackTransaction();
      // Registrar en el excel el error
      ExcelNegocio::create()->generarExcelMovimientosErrores($opcionId, $documentoTipoId, $camposDinamicos, $detalle, $me->getMessage());

      $respuesta->exito = false;
      $respuesta->mensaje = $me->getMessage();
    } catch (Exception $ex) {
      $this->rollbackTransaction();
      // Registrar en el excel el error
      ExcelNegocio::create()->generarExcelMovimientosErrores($opcionId, $documentoTipoId, $camposDinamicos, $detalle, $ex->getMessage());

      $respuesta->exito = false;
      $respuesta->mensaje = $ex->getMessage();
    }

    if ($guardar == TRUE) {
      ExcelNegocio::create()->guardarExcelMovimientosErrores($opcionId);
    }

    return $respuesta;
  }

  public function importarExcelMovimiento($opcionId, $usuarioId, $xml, $usuCreacion)
  {
    $filasImportadas = 0;
    $row = 7;
    $errors = array();
    $dom = new DOMDocument;
    $xml = "<root>" . $xml . "</root>";
    //Documento tipo
    $xml = str_replace("documento tipo", "documentoTipo", $xml);

    $dom->loadXML($xml);
    $movExcel = simplexml_import_dom($dom);
    $detalle = array();
    for ($i = 0; $i < count($movExcel); $i++) {
      $bandera = false;
      $filaExcel = $movExcel->movi[$i];
      //documentoTipo
      $documentoTipoNombre = trim((string) $filaExcel->documentoTipo);

      $documentoTipo = DocumentoTipoNegocio::create()->obtenerIdXDocumentoTipoDescripcionOpcionId($documentoTipoNombre, $opcionId);
      $documentoTipoId = $documentoTipo[0]['id'];
      $empresaId = $documentoTipo[0]['empresa_id'];

      //Dinamico
      $documentoTipoNombreDato = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDato($documentoTipoId);

      $indice = 0;
      foreach ($documentoTipoNombreDato as $index => $dtd) {
        $nombreColumna = str_replace(' ', '', $dtd['descripcion']);
        if (property_exists($filaExcel, $nombreColumna)) {
          $valorDinamico = trim((string) $filaExcel->{$nombreColumna});

          switch ($dtd["tipo"]) {
            case DocumentoTipoNegocio::DATO_PERSONA:
              // buscar en data
              $valor = null;
              foreach ($dtd["data"] as $persona) {
                if ($persona['nombre'] == $valorDinamico) {
                  $valor = $persona['id'];
                  break;
                }
              }
              $documentoTipoNombreDato[$index]["valor"] = $valor;
              break;
            case DocumentoTipoNegocio::DATO_LISTA:
              $valor = null;
              foreach ($dtd["data"] as $lista) {
                if ($lista['descripcion'] == $valorDinamico) {
                  $valor = $lista['id'];
                  break;
                }
              }
              $documentoTipoNombreDato[$index]["valor"] = $valor;
              break;

            default:
              //$documentoTipoNombreDato[$index]["valor"] = $filaExcel[$dtd["descripcion"]];
              $documentoTipoNombreDato[$index]["valor"] = $valorDinamico;
          }

          $camposDinamicos[$indice] = array(
            'id' => $documentoTipoNombreDato[$index]["id"],
            'tipo' => $documentoTipoNombreDato[$index]["tipo"],
            'opcional' => $documentoTipoNombreDato[$index]["opcional"],
            'descripcion' => $documentoTipoNombreDato[$index]["descripcion"],
            'valor' => $documentoTipoNombreDato[$index]["valor"],
            'valorExcel' => $valorDinamico
          );
          $indice++;
          //$valorDinamicoAntes=$valorDinamico;
        }
      }

      $camposDinamicosGuardar = $camposDinamicosAntes;
      $documentoTipoIdGuardar = $documentoTipoIdAntes;

      if ($i != 0) {
        if ($camposDinamicosAntes != $camposDinamicos) {
          $bandera = true;
        }
      }

      $camposDinamicosAntes = $camposDinamicos;
      $documentoTipoIdAntes = $documentoTipoId;


      //detalle
      $organizador = trim((string) $filaExcel->Organizador);
      $cantidad = trim((string) $filaExcel->Cantidad);
      $unidadMedida = trim((string) $filaExcel->UnidadMedida);
      $bien = trim((string) $filaExcel->Bien);
      $precioUnitario = trim((string) $filaExcel->PrecioUnitario);
      $totalDetalle = trim((string) $filaExcel->TotalDetalle);

      //buscamos Id de los detalles
      $organizadorBuscado = OrganizadorNegocio::create()->obtenerOrganizadorActivoXDescripcion($organizador);
      $organizador_id = $organizadorBuscado[0]['id'];

      $unidadMedidaBuscado = UnidadNegocio::create()->obtenerUnidadMedidaActivoXDescripcion($unidadMedida);
      $unidadMedida_id = $unidadMedidaBuscado[0]['id'];

      $bienBuscado = BienNegocio::create()->obtenerBienActivoXDescripcion($bien);
      $bien_id = $bienBuscado[0]['id'];

      //array detalles
      if ($bandera == false) {
        array_push($detalle, array(
          'organizadorId' => $organizador_id,
          'bienId' => $bien_id,
          'cantidad' => $cantidad,
          'unidadMedidaId' => $unidadMedida_id,
          'precio' => $precioUnitario,
          'organizadorDesc' => $organizador,
          'bienDesc' => $bien,
          'unidadMedidaDesc' => $unidadMedida,
          'subTotal' => $totalDetalle
        ));
      }

      if ($i == (count($movExcel) - 1)) {
        if ($bandera == true) {
          $response = MovimientoNegocio::create()->guardarDeExcel($opcionId, $usuarioId, $documentoTipoIdGuardar, $camposDinamicosGuardar, $detalle, false);
          //Errores
          if ($response > 0) {
            $filasImportadas++;
          } else {
            $cause = $response[0]["vout_mensaje"];
            $errors[] = array("row" => $row, "cause" => $cause);
          }
          $row++;

          $detalle = array();

          array_push($detalle, array(
            'organizadorId' => $organizador_id,
            'bienId' => $bien_id,
            'cantidad' => $cantidad,
            'unidadMedidaId' => $unidadMedida_id,
            'precio' => $precioUnitario,
            'organizadorDesc' => $organizador,
            'bienDesc' => $bien,
            'unidadMedidaDesc' => $unidadMedida,
            'subTotal' => $totalDetalle
          ));


          $response = MovimientoNegocio::create()->guardarDeExcel($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, true);
          //Errores
          if ($response->exito) {
            $filasImportadas++;
          } else {
            $cause = $response->mensaje;
            $errors[] = array("row" => $row, "cause" => $cause);
          }
          $row++;
        } else {
          $response = MovimientoNegocio::create()->guardarDeExcel($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, true);
          //Errores
          if ($response->exito) {
            $filasImportadas++;
          } else {
            $cause = $response->mensaje;
            $errors[] = array("row" => $row, "cause" => $cause);
          }
          $row++;
        }
      } else {
        if ($bandera == true) {
          $response = MovimientoNegocio::create()->guardarDeExcel($opcionId, $usuarioId, $documentoTipoIdGuardar, $camposDinamicosGuardar, $detalle, false);
          //Errores
          if ($response->exito) {
            $filasImportadas++;
          } else {
            $cause = $response->mensaje;
            $errors[] = array("row" => $row, "cause" => $cause);
          }
          $row++;

          $detalle = array();

          array_push($detalle, array(
            'organizadorId' => $organizador_id,
            'bienId' => $bien_id,
            'cantidad' => $cantidad,
            'unidadMedidaId' => $unidadMedida_id,
            'precio' => $precioUnitario,
            'organizadorDesc' => $organizador,
            'bienDesc' => $bien,
            'unidadMedidaDesc' => $unidadMedida,
            'subTotal' => $totalDetalle
          ));
        }
      }
    }
    if ($row == $filasImportadas + 7) {
      $this->setMensajeEmergente("Importacion finalizada. Se procesaron $filasImportadas de " . ($row - 7) . " filas.");
    }

    return $errors;
  }

  //Area de funciones para copiar documento

  function obtenerConfiguracionBuscadorDocumentoRelacion($opcionId, $empresaId)
  {

    // $tipoIds = '(0),(1),(4)';
    $tipoIds = '';
    $respuesta = new ObjectUtil();
    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    $movimientoTipoId = $movimientoTipo[0]["id"];
    if ($movimientoTipoId == 146) {
      $tipoIds = "";
    }

    //$respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXEmpresaXTipo($empresaId, $tipoIds);
    $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXEmpresaXTipoXMovimientoTipo($movimientoTipoId, $empresaId, $tipoIds);
    $respuesta->persona = PersonaNegocio::create()->obtenerActivas();
    $respuesta->estado = DocumentoNegocio::create()->obtenerDocumentoEstadoLista(); // DocumentoNegocio::create->ob  ($movimientoTipoId);
    if ($opcionId == "325" && $movimientoTipoId == "141") {
      $respuesta->segun = DocumentoTipoDatoListaNegocio::create()->obtenerXDocumentoTipoDato(2945);
    }
    return $respuesta;
  }

  function buscarDocumentoACopiar($criterios, $elementosFiltrados, $columnas, $orden, $tamanio, $transferenciaTipo)
  {

    $empresaId = $criterios['empresa_id'];
    $documentoTipoIds = $criterios['documento_tipo_ids'];
    $personaId = $criterios['persona_id'];
    $estadoId = $criterios['estado_id'];
    $serie = $criterios['serie'];
    $numero = $criterios['numero'];
    $fechaEmisionInicio = DateUtil::formatearCadenaACadenaBD($criterios['fecha_emision_inicio']);
    $fechaEmisionFin = DateUtil::formatearCadenaACadenaBD($criterios['fecha_emision_fin']);
    $fechaVencimientoInicio = DateUtil::formatearCadenaACadenaBD($criterios['fecha_vencimiento_inicio']);
    $fechaVencimientoFin = DateUtil::formatearCadenaACadenaBD($criterios['fecha_vencimiento_fin']);

    $movimientoTipoId = $criterios['movimiento_tipo_id'];
    $segun_id = '';
    if ($movimientoTipoId == "141") {
      $segun_id = Util::convertirArrayXCadena($criterios['segun_id']);
    }

    $documentoTipoIdFormateado = Util::convertirArrayXCadena($documentoTipoIds);
    $estadoIdFormateado = Util::convertirArrayXCadena($estadoId);

    $columnaOrdenarIndice = $orden[0]['column'];
    $formaOrdenar = $orden[0]['dir'];

    $columnaOrdenar = $columnas[$columnaOrdenarIndice]['data'];

    $respuesta = new ObjectUtil();

    $respuesta->data = Movimiento::create()->buscarDocumentoACopiar($empresaId, $documentoTipoIdFormateado, $personaId, $serie, $numero, $fechaEmisionInicio, $fechaEmisionFin, $fechaVencimientoInicio, $fechaVencimientoFin, $elementosFiltrados, $formaOrdenar, $columnaOrdenar, $tamanio, $transferenciaTipo, $movimientoTipoId, $estadoIdFormateado, $segun_id);

    $respuesta->contador = Movimiento::create()->buscarDocumentoACopiarTotal($empresaId, $documentoTipoIdFormateado, $personaId, $serie, $numero, $fechaEmisionInicio, $fechaEmisionFin, $fechaVencimientoInicio, $fechaVencimientoFin, $formaOrdenar, $columnaOrdenar, $transferenciaTipo, $movimientoTipoId, $estadoIdFormateado, $segun_id);

    return $respuesta;
  }

  function obtenerDocumentoRelacionCabecera($documentoOrigenId, $documentoDestinoId, $movimientoId, $documentoId, $opcionId, $documentoRelacionados)
  {
    $respuesta = new ObjectUtil();
    $datoDocumento = DocumentoNegocio::create()->obtenerDataDocumentoACopiar($documentoDestinoId, $documentoOrigenId, $documentoId);

    if (ObjectUtil::isEmpty($datoDocumento)) {
      throw new WarningException("No se encontró el documento");
    }

    $respuesta->dataDocumento = $datoDocumento;
    $respuesta->dataDocumentoRelacionada = DocumentoNegocio::create()->obtenerDataDocumentoACopiarRelacionada($documentoOrigenId, $documentoDestinoId, $documentoId);

    if ($documentoDestinoId != $documentoOrigenId) {
      $respuesta->documentoCopiaRelaciones = DocumentoNegocio::create()->obtenerRelacionesDocumento($documentoId);
    } else {
      $respuesta->documentoCopiaRelaciones = 1;
    }

    return $respuesta;
  }

  function obtenerDocumentoRelacion($documentoTipoOrigenId, $documentoTipoDestinoId, $movimientoId, $documentoId, $opcionId, $documentoRelacionados)
  {

    $respuesta = new ObjectUtil();
    $arrayDataBien = array();

    $documentoACopiar = DocumentoNegocio::create()->obtenerDataDocumentoACopiar($documentoTipoDestinoId, $documentoTipoOrigenId, $documentoId);

    if (ObjectUtil::isEmpty($documentoACopiar)) {
      throw new WarningException("No se encontró el documento");
    }

    $respuesta->dataDocumentoRelacionada = DocumentoNegocio::create()->obtenerDataDocumentoACopiarRelacionada($documentoTipoOrigenId, $documentoTipoDestinoId, $documentoId);
    $respuesta->detalleDocumento = $this->obtenerDocumentoRelacionDetalle($movimientoId, $documentoId, $opcionId, $documentoRelacionados);

    $valorProveedor = null;
    if($documentoTipoDestinoId == Configuraciones::COTIZACIONES || $documentoTipoDestinoId == Configuraciones::ORDEN_COMPRA){
      $valores = DocumentoDatoValorNegocio::create()->obtenerXIdDocumentoXTipo($documentoId, 23);
      switch($respuesta->detalleDocumento[0]['postor_ganador_id']){
        case 1:
          foreach($valores as $itemValores){
            if(strpos($itemValores['descripcion'], "1")){
              $valorProveedor = $itemValores['valor_codigo'];
            }
          }
          break;
        case 2:
          foreach($valores as $itemValores){
            if(strpos($itemValores, "1")){
              $valorProveedor = $itemValores['valor_codigo'];
            }
          }          
          break;
        case 3:
          foreach($valores as $itemValores){
            if(strpos($itemValores, "1")){
              $valorProveedor = $itemValores['valor_codigo'];
            }
          }
          break;
      }

      $documentoACopiar [count($documentoACopiar)] = array(
        "id" => "3131",
        "tipo" => "23",
        "documento_tipo_descripcion" => $documentoACopiar[0]['documento_tipo_descripcion'],
        "moneda_id" => $documentoACopiar[0]['moneda_id'],
        "valor" => $valorProveedor,
        "otro_documento_id" => $documentoTipoDestinoId == Configuraciones::COTIZACIONES ? "933": "3131",
        "incluye_igv" => $documentoACopiar[0]['moneda_id'],
        "identificador_negocio" => $documentoACopiar[0]['moneda_id'],
        "persona_usuario_id" => $documentoACopiar[0]['moneda_id']);
    }

    $respuesta->documentoACopiar = $documentoACopiar;

    if ($documentoTipoDestinoId != $documentoTipoOrigenId) {
      $respuesta->documentosRelacionados = DocumentoNegocio::create()->obtenerRelacionesDocumento($documentoId);
    } else {
      $respuesta->documentosRelacionados = 1;
    }

    $respuesta->dataPagoProgramacion = PagoNegocio::create()->obtenerPagoProgramacionXDocumentoId($documentoId);

    //OBTENER DATA DE UNIDAD DE MEDIDA
    $documentoDetalle = $respuesta->detalleDocumento;
    foreach ($documentoDetalle as $index => $item) {
      $bienId = $item['bien_id'];
      $unidadMedidaId = $item['unidad_medida_id'];
      $precioTipoId = $item['precio_tipo_id'];
      $monedaId = $documentoACopiar[0]['moneda_id'];
      $fechaEmision = date("d/m/Y");
      foreach ($documentoACopiar as $itemDato) {
        if ($itemDato['tipo'] == 9) {
          $fechaEmision = date_format((date_create($itemDato['valor'])), 'd/m/Y');
        }
      }

      $data = MovimientoNegocio::create()->obtenerUnidadMedida($bienId, $unidadMedidaId, $precioTipoId, $monedaId, $fechaEmision);
      $documentoDetalle[$index]['dataUnidadMedida'] = $data;
      $dataBien = BienNegocio::create()->obtenerActivosXMovimientoTipoIdBienId($empresaId, $movimientoTipo[0]["id"], $bienId);
      foreach ($dataBien as $datos) {
        array_push($arrayDataBien, $datos);
      }
    }
    $respuesta->detalleDocumento = $documentoDetalle;
    //FIN OBTENER DATA UNIDAD MEDIDA
    $respuesta->datosDocumento = ['documentoIdCopia' => $documentoId, 'movimientoIdCopia' => $movimientoId];
    $respuesta->dataBien = $arrayDataBien;
    return $respuesta;
  }

  function obtenerNumeroNotaCredito($documentoTipoId, $documentoRelacionadoTipo)
  {

    $respuesta = DocumentoNegocio::create()->obtenerNumeroNotaCredito($documentoTipoId, $documentoRelacionadoTipo);

    return $respuesta;
  }

  function obtenerDocumentoRelacionDua($documentoTipoOrigenId, $documentoTipoDestinoId, $movimientoId, $documentoId, $opcionId, $documentoRelacionados)
  {

    $respuesta = $this->obtenerDocumentoRelacion($documentoTipoOrigenId, $documentoTipoDestinoId, $movimientoId, $documentoId, $opcionId, $documentoRelacionados);

    // Obtenemos las cantidades de la GR relacionada a la OC que estamos copiando
    $gr = MovimientoBien::create()->obtenerGRCantidadesPorOCId($documentoId);
    if (!ObjectUtil::isEmpty($gr) && !ObjectUtil::isEmpty($respuesta->detalleDocumento)) {
      foreach ($respuesta->detalleDocumento as $iDD => $itemDD) {
        foreach ($gr as $itemGR) {
          if ($itemDD["bien_id"] == $itemGR["bien_id"] && $itemDD["unidad_medida_id"] == $itemGR["unidad_medida_id"]) {
            $respuesta->detalleDocumento[$iDD]["cantidad"] = $itemGR["cantidad"];
            break;
          }
        }
      }
    }

    return $respuesta;
  }

  private function validarStockDocumento($documentoDetalle, $movimientoTipoId)
  {

    $tamanhoDetalle = count($documentoDetalle);
    $organizadoresEmpresa = OrganizadorNegocio::create()->obtenerXMovimientoTipo($movimientoTipoId);

    for ($i = 0; $i < $tamanhoDetalle; $i++) {

      if ($this->verificarOrganizadorPertenece($documentoDetalle[$i]['organizador_id'], $organizadoresEmpresa)) {
        $stock = BienNegocio::create()->obtenerStockBase($documentoDetalle[$i]['organizador_id'], $documentoDetalle[$i]['bien_id']);
        $stockControlar = (!ObjectUtil::isEmpty($stock)) ? $stock[0]["stock"] : 0;
        if ((floatval($stockControlar) - floatval($documentoDetalle[$i]['cantidad'])) < 0) {
          $stockOrganizadores = BienNegocio::create()->obtenerStockOrganizadoresXEmpresa(
            $documentoDetalle[$i]['bien_id'],
            $documentoDetalle[$i]['unidad_medida_id'],
            $movimientoTipoId
          );

          $documentoDetalle[$i]['stock_organizadores'] = $stockOrganizadores;
        } else {
          $documentoDetalle[$i]['stock_organizadores'] = null;
        }
      } else {
        $stockOrganizadores = BienNegocio::create()->obtenerStockOrganizadoresXEmpresa(
          $documentoDetalle[$i]['bien_id'],
          $documentoDetalle[$i]['unidad_medida_id'],
          $movimientoTipoId
        );

        $documentoDetalle[$i]['stock_organizadores'] = $stockOrganizadores;
      }
    }

    return $documentoDetalle;
  }

  function verificarOrganizadorPertenece($organizador, $organizadores)
  {
    if (ObjectUtil::isEmpty($organizador)) {
      return false;
    }
    $bandera = false;
    foreach ($organizadores as $org) {
      if ($org['id'] == $organizador) {
        $bandera = true;
      }
    }

    return $bandera;
  }

  function obtenerDocumentoRelacionDetalle($movimientoId, $documentoId, $opcionId, $documentoRelacionados)
  {

    $banderaMerge = 0;
    $arrayDetalle = array();

    $tamanhoArrayRelacionado = count($documentoRelacionados);
    if (!ObjectUtil::isEmpty($movimientoId) && !ObjectUtil::isEmpty($documentoId)) {
      $documentoRelacionados[$tamanhoArrayRelacionado]['movimientoId'] = $movimientoId;
      $documentoRelacionados[$tamanhoArrayRelacionado]['documentoId'] = $documentoId;
    }

    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    $transferenciaTipo = $movimientoTipo[0]["transferencia_tipo"];

    foreach ($documentoRelacionados as $documentoRelacion) {
      if ($transferenciaTipo == 2) {
        $documentoDetalle = MovimientoBien::create()->obtenerDetalleTransferenciaXIdMovimiento($documentoRelacion['movimientoId']);
      } else {
        //OBTENEMOS LOS DOCUMENTOS HIJOS DE LA COPIA
        $documentoRelacionHijos = DocumentoNegocio::create()->obtenerRelacionesDocumento($documentoRelacion['documentoId']);
        $movimientoIdHijos = '';
        if (!ObjectUtil::isEmpty($documentoRelacionHijos)) {
          foreach ($documentoRelacionHijos as $itemRel) {
            if (!ObjectUtil::isEmpty($itemRel['movimiento_id'])) {
              $movimientoIdHijos = $movimientoIdHijos . $itemRel['movimiento_id'] . ',';
            }
          }
        }

        if ($movimientoIdHijos != '') {
          //OBTIENE CON PRECIOS DE LOS DOCUMENTOS HIJOS
          $documentoDetalle = MovimientoBien::create()->obtenerXMovimientoIdXMovimientoIdRelacion($documentoRelacion['movimientoId'], $movimientoIdHijos);
        } else {
          $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($documentoRelacion['movimientoId']);
        }
      }

      //$documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($documentoRelacion['movimientoId']);

      $tamanhioArrayDetalle = count($arrayDetalle);

      foreach ($documentoDetalle as $detalle) {
        $i = 0;
        while ($i < $tamanhioArrayDetalle && $banderaMerge == 0) {
          if ($detalle['bien_id'] == $arrayDetalle[$i]['bien_id'] && $detalle['unidad_medida_id'] == $arrayDetalle[$i]['unidad_medida_id']) {
            $arrayDetalle[$i]['cantidad'] = $arrayDetalle[$i]['cantidad'] + $detalle['cantidad'];
            $arrayDetalle[$i]['valor_monetario'] = $detalle['valor_monetario'];
            $banderaMerge = 1;
          }

          $i++;
        }

        if ($banderaMerge == 0) {
          //obtener datos de: movimiento_bien_detalle
          $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleXMovimientoBienId($detalle['movimiento_bien_id']);

          array_push($arrayDetalle, $this->getDocumentoACopiarMerge(
            $detalle['organizador_descripcion'],
            $detalle['organizador_id'],
            $detalle['cantidad'],
            $detalle['bien_descripcion'],
            $detalle['bien_id'],
            $detalle['valor_monetario'],
            $detalle['unidad_medida_id'],
            $detalle['unidad_medida_descripcion'],
            $detalle['precio_tipo_id'],
            $resMovimientoBienDetalle,
            $detalle['movimiento_bien_comentario'],
            $detalle['bien_codigo'],
            $detalle['agencia_id'],
            $detalle['agencia_descripcion'],
            $detalle['agrupador_id'],
            $detalle['agrupador_descripcion'],
            $detalle['ticket'],
            $detalle['centro_costo_id'],
            $detalle['precio_postor1'],
            $detalle['precio_postor2'],
            $detalle['precio_postor3'],
            $detalle['postor_ganador_id'],
            $detalle['movimiento_bien_ids'],
            $detalle['cantidad_atendida']
          ));
        }
        $banderaMerge = 0;
      }
      $banderaMerge = 0;
    }

    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    if (ObjectUtil::isEmpty($movimientoTipo)) {
      throw new WarningException("No se encontró el movimiento asociado a esta opción");
    }
    $movimientoTipoId = $movimientoTipo[0]["id"];

    //        $respuesta = new ObjectUtil();
    //        return $this->validarStockDocumento($arrayDetalle, $movimientoTipoId);
    return $arrayDetalle;
    //        return $respuesta;
  }

  private function getDocumentoACopiarMerge($organizadorDescripcion, $organizadorId, $cantidad, $bienDescripcion, $bienId, $valorMonetario, $unidadMedidaId, $unidadMedidaDescripcion, $precioTipoId, $movimientoBienDetalle, $movimientoBienComentario, $codigoBien, $agenciaId, $agenciaDescripcion, $agrupadorId, $agrupadorDescripcion, $ticket, $CeCoId, $precio_postor1, $precio_postor2, $precio_postor3, $postor_ganador_id, $movimiento_bien_ids, $cantidad_atendida)
  {

    $detalle = array(
      "organizador_descripcion" => $organizadorDescripcion,
      "organizador_id" => $organizadorId,
      "cantidad" => $cantidad,
      "bien_descripcion" => $bienDescripcion,
      "bien_id" => $bienId,
      "unidad_medida_id" => $unidadMedidaId,
      "unidad_medida_descripcion" => $unidadMedidaDescripcion,
      "valor_monetario" => $valorMonetario,
      "precio_tipo_id" => $precioTipoId,
      "movimiento_bien_detalle" => $movimientoBienDetalle,
      "movimiento_bien_comentario" => $movimientoBienComentario,
      "bien_codigo" => $codigoBien,
      "agencia_id" => $agenciaId,
      "agencia_descripcion" => $agenciaDescripcion,
      "agrupador_id" => $agrupadorId,
      "agrupador_descripcion" => $agrupadorDescripcion,
      "ticket" => $ticket,
      "CeCo_id" => $CeCoId,
      "precio_postor1" => $precio_postor1,
      "precio_postor2" => $precio_postor2,
      "precio_postor3" => $precio_postor3,
      "postor_ganador_id" => $postor_ganador_id,
      "movimiento_bien_ids" => $movimiento_bien_ids,
      "cantidad_atendida" => $cantidad_atendida
    );
    return $detalle;
  }

  public function obtenerDocumentosRelacionados($documentoId)
  {

    return DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoId($documentoId);
  }

  public function obtenerSoloDocumentosRelacionados($documentoId)
  {

    return DocumentoNegocio::create()->obtenerSoloDocumentosRelacionados($documentoId);
  }

  public function enviarCorreoDocumentoConFechaEmisionAnterior($documentoId, $movimientoId, $usuarioId)
  {
    $plantillaId = 17;
    $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID($plantillaId);
    $correosPlantilla = EmailPlantillaNegocio::create()->obtenerEmailXDestinatario($plantilla[0]["destinatario"], null, null);

    $correos = '';
    foreach ($correosPlantilla as $email) {
      $correos = $correos . $email . ';';
    }

    $data = MovimientoNegocio::create()->visualizarDocumento($documentoId, $movimientoId);

    //dibujar cuerpo y detalle
    $nombreDocumentoTipo = '';
    $dataDocumento = '';

    // datos de documento
    if (!ObjectUtil::isEmpty($data->dataDocumento)) {

      $nombreDocumentoTipo = $data->dataDocumento[0]['nombre_documento'];

      // Mostraremos la data en filas de dos columnas
      foreach ($data->dataDocumento as $index => $item) {
        $html = '<tr><td style=\'text-align:left;padding:0 55px 5px;font-size:14px;line-height:1.5;width:80%\'><b>' . $item['descripcion'] . ': </b>';

        $valor = $item['valor'];

        if (!ObjectUtil::isEmpty($valor)) {
          switch ((int) $item['tipo']) {
            case 3:
            case 9:
            case 10:
            case 11:
              $time = strtotime($valor);
              $valor = date('d/m/Y', $time);
              break;
            case 1:
            case 14:
            case 15:
            case 16:
            case 19:
              $valor = number_format($valor, 2, ".", ",");
              break;
          }
        }

        $html = $html . $valor;

        $html = $html . '</td></tr>';
        $dataDocumento = $dataDocumento . $html;
      }
    }

    // detalle de documento
    //obtener configuracion de las columnas de movimiento_tipo
    $res = MovimientoTipoNegocio::create()->obtenerXDocumentoId($documentoId);
    if (!ObjectUtil::isEmpty($res)) {
      $movimientoTipoId = $res[0]['movimiento_tipo_id'];
      $dataMovimientoTipoColumna = MovimientoTipoNegocio::create()->obtenerMovimientoTipoColumna($movimientoTipoId);
    }

    //dibujando la cabecera
    $dataDetalle = '<table  border=1 cellspacing=0 cellpadding=5 bordercolor="000000"  width="80%">
                        <thead>';

    $html = '<tr>';
    if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 15)) { //Organizador
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Organizador</th>";
    }
    $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Cantidad</th>";
    $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Unidad</th>";
    $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Descripcion</th>";

    if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 5)) { //Precio unitario
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>PU</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Total</th>";
    }
    $html = $html . '</tr>';

    $dataDetalle = $dataDetalle . $html;
    $dataDetalle = $dataDetalle . '<thead>';
    $dataDetalle = $dataDetalle . '<tbody>';

    if (!ObjectUtil::isEmpty($data->detalleDocumento)) {
      foreach ($data->detalleDocumento as $index => $item) {

        $html = '<tr>';
        if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 15)) { //Organizador
          $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item->organizador;
        }
        $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . number_format($item->cantidad, 2, ".", ",");
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item->unidadMedida;
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item->descripcion;

        if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 5)) { //Precio unitario
          $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . number_format($item->precioUnitario, 2, ".", ",");
          $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . number_format($item->importe, 2, ".", ",");
        }
        $html = $html . '</tr>';

        $dataDetalle = $dataDetalle . $html;
      }
    }
    $dataDetalle = $dataDetalle . '</tbody></table>';

    $comentarioFinalDocumento = '<tr><td style="text-align: left; padding: 0 55px 10px; font-size: 14px; line-height: 1.5; width: 80%">Documento generado con fecha de emisión anterior a la fecha actual, registrado en la empresa '
      . $data->direccionEmpresa[0]['razon_social']
      . ' ubicada en '
      . $data->direccionEmpresa[0]['direccion']
      . '</td></tr>';
    //fin dibujo

    $comentarioDocumento = $data->comentarioDocumento[0]['comentario_documemto'];
    //logica correo:
    $asunto = $plantilla[0]["asunto"];
    $cuerpo = $plantilla[0]["cuerpo"];

    $cuerpo = str_replace("[|documento_tipo|]", $nombreDocumentoTipo, $cuerpo);
    $cuerpo = str_replace("[|dato_documento|]", $dataDocumento, $cuerpo);
    $cuerpo = str_replace("[|detalle_documento|]", $dataDetalle, $cuerpo);
    $cuerpo = str_replace("[|comentario_documento|]", $comentarioDocumento, $cuerpo);
    $cuerpo = str_replace("[|comentario_final_documento|]", $comentarioFinalDocumento, $cuerpo);

    $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correos, $asunto, $cuerpo, 1, $usuarioId);
    return 1;
  }

  public function obtenerBienesConStockMenorACantidadMinima($personaId, $organizadorId, $empresaId, $monedaId, $operador)
  {

    $data = BienNegocio::create()->obtenerBienesConStockMenorACantidadMinima($personaId, $organizadorId, $empresaId, $monedaId, $operador);
    return $data;
  }

  public function verificarTipoUnidadMedidaParaTramo($unidadMedidaId)
  {
    $data = UnidadMedidaTipo::create()->verificarTipoUnidadMedidaParaTramo($unidadMedidaId);
    return $data;
  }

  public function registrarTramoBien($unidadMedidaId, $cantidadTramo, $bienId, $usuCreacion)
  {
    $data = Movimiento::create()->registrarTramoBien($unidadMedidaId, $cantidadTramo, $bienId, $usuCreacion);
    return $data;
  }

  public function obtenerTramoBien($bienId)
  {
    $data = Movimiento::create()->obtenerTramoBienXBienId($bienId);
    return $data;
  }

  public function editarComentarioDocumento($documentoId, $comentario)
  {
    $res = DocumentoNegocio::create()->actualizarComentarioDocumento($documentoId, $comentario);
    return $res;
  }

  public function obtenerDocumentoTipoDatoEditableXDocumentoId($documentoId)
  {
    $data = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDatoEditableXDocumentoId($documentoId);

    return $data;
  }

  // TODO: Inicio Guardar Edicion
  public function guardarEdicionDocumento($documentoId, $camposDinamicos, $comentario = null, $periodoId = null, $tipoPago = null, $monedaId = null, $usuarioCreacionId = null, $datosExtras = null, $contOperacionTipoId = null, $igv_porcentaje = null)
  {
    $documento = DocumentoNegocio::create()->guardarEdicionDocumento($documentoId, $camposDinamicos, $comentario, $periodoId, $tipoPago, $monedaId, $usuarioCreacionId, $datosExtras, $contOperacionTipoId, $igv_porcentaje);
    return $documento;
  }

  public function enviarMovimientoEmailPDF($correo, $documentoId, $comentario, $usuarioId, $plantillaId)
  {

    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];
    $data = MovimientoNegocio::create()->imprimir($documentoId, $documentoTipoId);
    $dataDocumento = $data->dataDocumento;

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }

    $dataPersona = DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId, 5);
    $descripcionPersona = $dataPersona[0]['descripcion'];
    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];

    $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID($plantillaId); // antes 7

    $hoy = date("Y_m_d_H_i_s");
    $url = __DIR__ . '/../../vistas/com/movimiento/documentos/documento_' . $hoy . '_' . $usuarioId . '.pdf';

    //crear PDF
    $this->generarDocumentoPDF($documentoId, $comentario, 'F', $url, $data);

    //envio de email
    $email = new EmailEnvioUtil();

    $asunto = $titulo;
    $cuerpo = $plantilla[0]["cuerpo"];

    $cuerpo = str_replace("[|titulo|]", $dataDocumentoTipo[0]['descripcion'], $cuerpo);
    $cuerpo = str_replace("[|descripcion_persona|]", strtolower($descripcionPersona), $cuerpo);
    $cuerpo = str_replace("[|nombre_persona|]", $dataDocumento[0]['nombre'], $cuerpo);
    $cuerpo = str_replace("[|nombre_documento|]", $dataDocumentoTipo[0]['descripcion'], $cuerpo);
    $cuerpo = str_replace("[|serie_numero|]", $serieDocumento . $dataDocumento[0]['numero'], $cuerpo);
    $nombreArchivo = $dataDocumentoTipo[0]['descripcion'] . ".pdf";

    $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correo, $asunto, $cuerpo, 1, $usuarioId, $url, $nombreArchivo);

    if (!ObjectUtil::isEmpty($res[0]['id'])) {
      $this->setMensajeEmergente("Se enviará el correo aproximadamente en un minuto");
    }

    return $res;
  }

  public function generarDocumentoPDF($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    require_once __DIR__ . '/../../controlador/commons/tcpdf/config/lang/eng.php';
    require_once __DIR__ . '/../../controlador/commons/tcpdf/tcpdf.php';

    //$tipoSalidaPDF: F-> guarda local
    $dataDocumento = $data->dataDocumento;

    // create new PDF document

    $identificadorNegocio = $dataDocumento[0]['identificador_negocio'];

    switch ((int) $identificadorNegocio) {
      case 1: //generar pdf Cotizacion
        return $this->generarDocumentoPDFCotizacion($documentoId, $comentario, $tipoSalidaPDF, $url, $data);

      case 8: //generar pdf orden de compra
        return $this->generarDocumentoPDFOrdenCompra($documentoId, $comentario, $tipoSalidaPDF, $url, $data);

      case 10: //generar pdf orden de compra extranjera
        return $this->generarDocumentoPDFOrdenCompraExt($documentoId, $comentario, $tipoSalidaPDF, $url, $data);

      case 11: //generar pdf solicitud de compra
        return $this->generarDocumentoPDFSolicitudCompra($documentoId, $comentario, $tipoSalidaPDF, $url, $data);

      case 12: //generar pdf solicitud de compra extranjera
        return $this->generarDocumentoPDFSolicitudCompraExt($documentoId, $comentario, $tipoSalidaPDF, $url, $data);

      case 13: //generar pdf Cotizacion compra
        return $this->generarDocumentoPDFCotizacionCompra($documentoId, $comentario, $tipoSalidaPDF, $url, $data);

      case 14: //generar pdf Cotizacion compra extranjera
        return $this->generarDocumentoPDFCotizacionCompraEXT($documentoId, $comentario, $tipoSalidaPDF, $url, $data);

      case 23: //generar pdf Guia interna para transferencia
        return $this->generarDocumentoPDFGuiaInterna($documentoId, $comentario, $tipoSalidaPDF, $url, $data);
      case 35: //generar pdf de Solicitud Requerimiento
        return $this->generarDocumentoPDFSolicitudRequerimiento($documentoId, $comentario, $tipoSalidaPDF, $url, $data);
        break;
      case 37: //generar pdf de Order Compra y Servicio
      case 39:
        return $this->generarDocumentoPDFOrdenCompraServicio($documentoId, $comentario, $tipoSalidaPDF, $url, $data);
        break;
      case 38: //generar pdf Requerimiento por Área
        return $this->generarDocumentoPDFRequerimiento($documentoId, $comentario, $tipoSalidaPDF, $url, $data);
        break;
      case 36:
        return $this->generarDocumentoPDFGenerarCotizacion($documentoId, $comentario, $tipoSalidaPDF, $url, $data);
        break;
      default:
        return $this->generarDocumentoPDFEstandar($documentoId, $comentario, $tipoSalidaPDF, $url, $data);
    }
  }

  public function generarDocumentoPDFGuiaInterna($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;
    $dataMovimientoTipoColumna = $data->movimientoTipoColumna;
    $dataDocumentoRelacion = $data->documentoRelacionado;

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator('Minapp S.A.C.');
    $pdf->SetAuthor('Minapp S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    //        $pdf->SetHeaderData('logo.PNG', PDF_HEADER_LOGO_WIDTH,strtoupper($dataDocumentoTipo[0]['descripcion']),$dataEmpresa[0]['razon_social']."\n".$dataEmpresa[0]['direccion'], array(0,0,0), array(0,0,0));
    $cabeceraPDF1 = $dataEmpresa[0]['razon_social']
      //                ."\nSoluciones Integrales de Perforación"
    ;

    $cabeceraPDF2 = $dataEmpresa[0]['direccion'] . "\n" .
      "Tel: (044) 728609 - Anexo 108 / Cel: 979 754 211\n" .
      "E-mail: lnacarino@abcservicios.pe; Web site: www.abcservicios.pe\n" .
      "RUC: " . $dataEmpresa[0]['ruc'];
    $PDF_HEADER_LOGO_WIDTH = 40;
    $pdf->SetHeaderData('logo.PNG', $PDF_HEADER_LOGO_WIDTH, $cabeceraPDF1, $cabeceraPDF2, array(0, 0, 0), array(0, 0, 0));

    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    $pdf->SetFont('helvetica', '', 9);
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }

    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];

    $pdf->Ln(5);
    $pdf->writeHTMLCell(0, 0, '', '', "<h2>" . $titulo . "</h2>", 0, 1, 0, true, 'C', true);
    $pdf->Ln(5);

    //obtener la descripcion de persona de documento_tipo_dato
    $dataPersona = DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId, 5);
    $descripcionPersona = $dataPersona[0]['descripcion'];

    $dataFechaEmision = DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId, 9);
    $descripcionFechaEmision = $dataFechaEmision[0]['descripcion'];

    $fecha = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y');
    if (!ObjectUtil::isEmpty($dataDocumento[0]['fecha_emision'])) {
      $pdf->Cell(0, 0, $descripcionFechaEmision . ": " . $fecha, 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['nombre'])) {
      $pdf->Cell(0, 0, $descripcionPersona . ": " . $dataDocumento[0]['nombre'], 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['direccion'])) {
      $pdf->Cell(0, 0, "Dirección: " . $dataDocumento[0]['direccion'], 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['codigo_identificacion'])) {
      $pdf->Cell(0, 0, $dataDocumento[0]['persona_documento_tipo'] . ": " . $dataDocumento[0]['codigo_identificacion'], 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['descripcion'])) {
      $pdf->Cell(0, 0, "Descripción: " . $dataDocumento[0]['descripcion'], 0, 1, 'L', 0, '', 0);
    }
    if ($dataDocumento[0]['identificador_negocio'] != 23) {
      $pdf->Cell(0, 0, "Moneda: " . $dataDocumento[0]['moneda_descripcion'], 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['fecha_vencimiento'])) {
      $pdf->Cell(0, 0, "Fecha de vencimiento: " . date_format((date_create($dataDocumento[0]['fecha_vencimiento'])), 'd/m/Y'), 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['fecha_tentativa'])) {
      $pdf->Cell(0, 0, "Fecha tentativa: " . date_format((date_create($dataDocumento[0]['fecha_tentativa'])), 'd/m/Y'), 0, 1, 'L', 0, '', 0);
    }
    $origen = '';
    $destino = '';
    foreach ($documentoDatoValor as $indice => $item) {
      if ($item['documento_tipo_id'] == 2887) {
        $origen = $item['valor'];
      }
      if ($item['documento_tipo_id'] == 2888) {
        $destino = $item['valor'];
      }
    }

    if (ObjectUtil::isEmpty($origen)) {
      $origen = $dataDocumento[0]['org_origen_desc'];
    }
    if (ObjectUtil::isEmpty($destino)) {
      $destino = $dataDocumento[0]['org_destino_desc'];
    }

    $pdf->Cell(0, 0, "Origen: " . $origen, 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Destino: " . $destino, 0, 1, 'L', 0, '', 0);

    $espacioComentario = 0;
    if (!ObjectUtil::isEmpty($comentario)) {
      $pdf->Ln(5);
      $pdf->writeHTMLCell(0, 0, '', '', $comentario, 0, 1, 0, true, 'L', true);
      $espacioComentario = 12;
    }

    $pdf->Ln(5);
    $pdf->writeHTMLCell(0, 0, '', '', "<h4>DETALLE DEL DOCUMENTO</h4>", 0, 1, 0, true, 'L', true);

    //espacio
    $pdf->Ln(5);

    //detalle
    $esp = '&nbsp;&nbsp;'; //espacio en blanco
    $existeColumnaPrecio = $this->existeColumnaCodigo($dataMovimientoTipoColumna, 5);

    $cont = 0;
    if ($existeColumnaPrecio) {
      $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                    <tr>
                        <th style="text-align:center;" width="8%"><b>Cant.</b></th>
                        <th style="text-align:center;" width="12%"><b>Código</b></th>
                        <th style="text-align:center;" width="42%"><b>Descripción.</b></th>
                        <th style="text-align:center;" width="13%"><b>Unid.</b></th>
                        <th style="text-align:center;" width="12%"><b>P. Unit.</b></th>
                        <th style="text-align:center;" width="13%"><b>P. Total</b></th>
                    </tr>
                ';

      foreach ($detalle as $item) {
        $cont++;
        if (strlen($item->descripcion) > 39) {
          $cont++;
        }

        $tabla = $tabla . '<tr>'
          . '<td style="text-align:rigth"  width="8%">' . $esp . round($item->cantidad, 2) . $esp . '</td>'
          . '<td style="text-align:left"  width="12%">' . $esp . $item->bien_codigo . $esp . '</td>'
          . '<td style="text-align:left"  width="42%">' . $esp . $item->descripcion . $esp . '</td>'
          . '<td style="text-align:center"  width="13%">' . $esp . $item->unidadMedida . $esp . '</td>'
          . '<td style="text-align:rigth"  width="12%">' . $esp . number_format($item->precioUnitario, 2) . $esp . '</td>'
          . '<td style="text-align:rigth"  width="13%">' . $esp . number_format($item->importe, 2) . $esp . '</td>'
          . '</tr>';
      };

      if (!ObjectUtil::isEmpty($dataDocumento[0]['total'])) {
        $tabla = $tabla . '<tr>'
          . '<td style="text-align:rigth;;"  width="75%" colspan="4"  ></td>'
          . '<td style="text-align:center"  width="12%">TOTAL ' . $dataDocumento[0]['moneda_simbolo'] . '</td>'
          . '<td style="text-align:rigth"  width="13%">' . $esp . number_format($dataDocumento[0]['total'], 2) . $esp . '</td>'
          . '</tr>';
      }
    } else {
      $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                    <tr>
                        <th style="text-align:center;" width="13%"><b>Cant.</b></th>
                        <th style="text-align:center;" width="16%"><b>Código</b></th>
                        <th style="text-align:center;" width="50%"><b>Descripción.</b></th>
                        <th style="text-align:center;" width="21%"><b>Unid.</b></th>
                    </tr>
                ';

      foreach ($detalle as $item) {
        $cont++;
        if (strlen($item->descripcion) > 39) {
          $cont++;
        }

        $tabla = $tabla . '<tr>'
          . '<td style="text-align:rigth"  width="13%">' . $esp . round($item->cantidad, 2) . $esp . '</td>'
          . '<td style="text-align:left"  width="16%">' . $esp . $item->bien_codigo . $esp . '</td>'
          . '<td style="text-align:left"  width="50%">' . $esp . $item->descripcion . $esp . '</td>'
          . '<td style="text-align:center"  width="21%">' . $esp . $item->unidadMedida . $esp . '</td>'
          . '</tr>';
      };
    }

    //fin tabla detalle
    $tabla = $tabla . '</table>';

    $pdf->writeHTML($tabla, true, false, false, false, 'C');

    $espaciado = 131 - ($cont * 4.4 + $espacioComentario);
    if ($espaciado < 15) {
      //            $espaciado = 15;
      $pdf->AddPage();
    }

    //        $pdf->Ln($espaciado); // cada fila es 4, total=131 , antes:115

    $identificadorNegocio = null;
    if (!ObjectUtil::isEmpty($documentoDatoValor)) {
      $pdf->Ln(5);
      $pdf->Ln(5);

      if ($identificadorNegocio == 1) {
        $pdf->writeHTMLCell(0, 6, '', '', "<h4>TERMINOS Y CONDICIONES</h4>", 'TB', 1, 0, true, 'C', true);
      } else {;
        $pdf->writeHTMLCell(0, 6, '', '', "<h4>OTROS DATOS DEL DOCUMENTO</h4>", 'TB', 1, 0, true, 'C', true);
      }

      $pdf->Ln(1);
      $pdf->SetFillColor(255, 255, 255);
      foreach ($documentoDatoValor as $indice => $item) {
        if ($item['documento_tipo_id'] == 2870) {
          if (
            $dataDocumentoTipo[0]['identificador_negocio'] == 23 &&
            ($item['documento_tipo_id'] == 2887 || $item['documento_tipo_id'] == 2888) && $item['valor'] == 'Virtual'
          ) {
            //NO MOSTRAR LA DIRECCION
            $txtDescripcion = $item['descripcion'];
            $valorItem = $item['valor'];
          } else {
            $txtDescripcion = $item['descripcion'];
            $valorItem = $item['valor'];

            if ($item['tipo'] == 1) {
              $valorItem = number_format($valorItem, 2, ".", ",");
            }

            if ($item['tipo'] == 3) {
              $valorItem = date_format((date_create($valorItem)), 'd/m/Y');
            }

            $pdf->MultiCell(60, 0, $txtDescripcion, 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
            $pdf->MultiCell(110, 0, $valorItem, 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');

            //                        if ($indice < count($documentoDatoValor) - 1 || $identificadorNegocio == 1) {
            //                            if (strlen($valorItem) > 55) {
            //                                $pdf->Ln(10);
            //                            } else {
            //                                $pdf->Ln(6);
            //                            }
            //                        }
            //                        if ($indice == count($documentoDatoValor) - 1) {
            $pdf->Ln(1);
            //                        }
          }
        }
      };
      $pdf->writeHTMLCell(0, 1, '', '', "", 'B', 1, 0, true, 'C', true);
    }


    $documentosRelacionImprimir = array();
    $dataDocumentoTipo;
    if ($dataDocumentoTipo[0]['identificador_negocio'] == 22) { //GUIA DE RECEPCION
      foreach ($dataDocumentoRelacion as $index => $itemR) {
        if ($itemR['identificador_negocio'] == 10) {
          $itemDR = array('serie_num' => $itemR['serie_numero_original'], 'nombre_doc' => $itemR['documento_tipo_descripcion']);
          array_push($documentosRelacionImprimir, $itemDR);

          $relacionOC = MovimientoNegocio::create()->obtenerDocumentosRelacionados($itemR['id']);

          foreach ($relacionOC as $itemROC) {
            if ($itemROC['identificador_negocio'] == 21 || $itemROC['identificador_negocio'] == 24) { //21: DUA  24: Commercial Invoice
              $itemDR = array('serie_num' => $itemROC['serie_numero'], 'nombre_doc' => $itemROC['documento_tipo'], 'serie_numero_original' => $itemROC['serie_numero_original']);
              array_push($documentosRelacionImprimir, $itemDR);
            }
          }
        }
      }
    }

    if ($dataDocumentoTipo[0]['identificador_negocio'] == 23) { //GUIA INTERNA DE TRANSFERENCIA
      $relacionDoc = MovimientoNegocio::create()->obtenerDocumentosRelacionados($documentoId);

      foreach ($relacionDoc as $itemDoc) {
        $itemDR = array('serie_num' => $itemDoc['serie_numero'], 'nombre_doc' => $itemDoc['documento_tipo'], 'serie_numero_original' => $itemDoc['serie_numero_original']);
        array_push($documentosRelacionImprimir, $itemDR);
      }
    }

    if (!ObjectUtil::isEmpty($documentosRelacionImprimir)) {
      $pdf->Ln(5);

      $pdf->Ln(5);

      $pdf->writeHTMLCell(0, 6, '', '', "<h4>DOCUMENTOS RELACIONADOS</h4>", 'TB', 1, 0, true, 'C', true);


      $pdf->Ln(1);
      $pdf->SetFillColor(255, 255, 255);
      foreach ($documentosRelacionImprimir as $indice => $item) {
        $txtDescripcion = $item['nombre_doc'];
        $serieNum = $item['serie_num'];
        $serieNumOriginal = $item['serie_numero_original'];

        $pdf->MultiCell(60, 0, $txtDescripcion, 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
        $pdf->MultiCell(30, 0, $serieNum, 0, 'C', 1, 0, '', '', true, 0, false, true, 40, 'T'); // 110

        if (!ObjectUtil::isEmpty($serieNumOriginal)) {
          $pdf->MultiCell(10, 0, ' | ', 0, 'C', 1, 0, '', '', true, 0, false, true, 40, 'T');
          $pdf->MultiCell(70, 0, $serieNumOriginal, 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
        }

        if ($indice < count($documentosRelacionImprimir) - 1) {
          if (strlen($serieNum) > 55) {
            $pdf->Ln(10);
          } else {
            $pdf->Ln(6);
          }
        }

        if ($indice == count($documentosRelacionImprimir) - 1) {
          $pdf->Ln(1);
        }
      };
      $pdf->writeHTMLCell(0, 1, '', '', "", 'B', 1, 0, true, 'C', true);
    }


    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  public function generarDocumentoPDFEstandar($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;
    $dataMovimientoTipoColumna = $data->movimientoTipoColumna;
    $dataDocumentoRelacion = $data->documentoRelacionado;

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator('Minapp S.A.C.');
    $pdf->SetAuthor('Minapp S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    //        $pdf->SetHeaderData('logo.PNG', PDF_HEADER_LOGO_WIDTH,strtoupper($dataDocumentoTipo[0]['descripcion']),$dataEmpresa[0]['razon_social']."\n".$dataEmpresa[0]['direccion'], array(0,0,0), array(0,0,0));
    $cabeceraPDF1 = $dataEmpresa[0]['razon_social']
      //                ."\nSoluciones Integrales de Perforación"
    ;

    $cabeceraPDF2 = $dataEmpresa[0]['direccion'] . "\n" .
      "Tel: (044) 728609 - Anexo 108 / Cel: 979 754 211\n" .
      "E-mail: lnacarino@abcservicios.pe; Web site: www.abcservicios.pe\n" .
      "RUC: " . $dataEmpresa[0]['ruc'];
    $PDF_HEADER_LOGO_WIDTH = 40;
    $pdf->SetHeaderData('logo.PNG', $PDF_HEADER_LOGO_WIDTH, $cabeceraPDF1, $cabeceraPDF2, array(0, 0, 0), array(0, 0, 0));

    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    $pdf->SetFont('helvetica', '', 9);
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }

    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];

    $pdf->Ln(5);
    $pdf->writeHTMLCell(0, 0, '', '', "<h2>" . $titulo . "</h2>", 0, 1, 0, true, 'C', true);
    $pdf->Ln(5);

    //obtener la descripcion de persona de documento_tipo_dato
    $dataPersona = DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId, 5);
    $descripcionPersona = $dataPersona[0]['descripcion'];

    $dataFechaEmision = DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId, 9);
    $descripcionFechaEmision = $dataFechaEmision[0]['descripcion'];

    $fecha = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y');
    if (!ObjectUtil::isEmpty($dataDocumento[0]['fecha_emision'])) {
      $pdf->Cell(0, 0, $descripcionFechaEmision . ": " . $fecha, 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['nombre'])) {
      $pdf->Cell(0, 0, $descripcionPersona . ": " . $dataDocumento[0]['nombre'], 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['direccion'])) {
      $pdf->Cell(0, 0, "Dirección: " . $dataDocumento[0]['direccion'], 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['codigo_identificacion'])) {
      $pdf->Cell(0, 0, $dataDocumento[0]['persona_documento_tipo'] . ": " . $dataDocumento[0]['codigo_identificacion'], 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['descripcion'])) {
      $pdf->Cell(0, 0, "Descripción: " . $dataDocumento[0]['descripcion'], 0, 1, 'L', 0, '', 0);
    }
    if ($dataDocumento[0]['identificador_negocio'] != 23) {
      $pdf->Cell(0, 0, "Moneda: " . $dataDocumento[0]['moneda_descripcion'], 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['fecha_vencimiento'])) {
      $pdf->Cell(0, 0, "Fecha de vencimiento: " . date_format((date_create($dataDocumento[0]['fecha_vencimiento'])), 'd/m/Y'), 0, 1, 'L', 0, '', 0);
    }
    if (!ObjectUtil::isEmpty($dataDocumento[0]['fecha_tentativa'])) {
      $pdf->Cell(0, 0, "Fecha tentativa: " . date_format((date_create($dataDocumento[0]['fecha_tentativa'])), 'd/m/Y'), 0, 1, 'L', 0, '', 0);
    }
    if ($dataDocumento[0]['identificador_negocio'] == 23) {
      $pdf->Cell(0, 0, "Origen: " . $dataDocumento[0]['org_origen_desc'], 0, 1, 'L', 0, '', 0);
      $pdf->Cell(0, 0, "Destino: " . $dataDocumento[0]['org_destino_desc'], 0, 1, 'L', 0, '', 0);
    }

    $espacioComentario = 0;
    if (!ObjectUtil::isEmpty($comentario)) {
      $pdf->Ln(5);
      $pdf->writeHTMLCell(0, 0, '', '', $comentario, 0, 1, 0, true, 'L', true);
      $espacioComentario = 12;
    }

    $pdf->Ln(5);
    $pdf->writeHTMLCell(0, 0, '', '', "<h4>DETALLE DEL DOCUMENTO</h4>", 0, 1, 0, true, 'L', true);

    //espacio
    $pdf->Ln(5);

    //detalle
    $esp = '&nbsp;&nbsp;'; //espacio en blanco
    $existeColumnaPrecio = $this->existeColumnaCodigo($dataMovimientoTipoColumna, 5);

    $cont = 0;
    if ($existeColumnaPrecio) {
      $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                    <tr>
                        <th style="text-align:center;" width="8%"><b>Cant.</b></th>
                        <th style="text-align:center;" width="12%"><b>Código</b></th>
                        <th style="text-align:center;" width="42%"><b>Descripción</b></th>
                        <th style="text-align:center;" width="13%"><b>Unid.</b></th>
                        <th style="text-align:center;" width="12%"><b>P. Unit.</b></th>
                        <th style="text-align:center;" width="13%"><b>P. Total</b></th>
                    </tr>
                ';

      foreach ($detalle as $item) {
        $cont++;
        if (strlen($item->descripcion) > 39) {
          $cont++;
        }
        $comentario = $item->movimientoBienComentario;
        $classHtml2Text = new Html2Text($comentario);
        $comentario = $classHtml2Text->getText();
        $tabla = $tabla . '<tr>'
          . '<td style="text-align:rigth"  width="8%">' . $esp . round($item->cantidad, 2) . $esp . '</td>'
          . '<td style="text-align:left"  width="12%">' . $esp . $item->bien_codigo . $esp . '</td>'
          . '<td style="text-align:left"  width="42%">' . $esp . $item->descripcion . " " . $comentario . $esp . '</td>'
          . '<td style="text-align:center"  width="13%">' . $esp . $item->unidadMedida . $esp . '</td>'
          . '<td style="text-align:rigth"  width="12%">' . $esp . number_format($item->precioUnitario, 2) . $esp . '</td>'
          . '<td style="text-align:rigth"  width="13%">' . $esp . number_format($item->importe, 2) . $esp . '</td>'
          . '</tr>';
      };

      if (!ObjectUtil::isEmpty($dataDocumento[0]['total'])) {
        $tabla = $tabla . '<tr>'
          . '<td style="text-align:rigth;;"  width="75%" colspan="4"  ></td>'
          . '<td style="text-align:center"  width="12%">TOTAL ' . $dataDocumento[0]['moneda_simbolo'] . '</td>'
          . '<td style="text-align:rigth"  width="13%">' . $esp . number_format($dataDocumento[0]['total'], 2) . $esp . '</td>'
          . '</tr>';
      }
    } else {
      $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                    <tr>
                        <th style="text-align:center;" width="13%"><b>Cant.</b></th>
                        <th style="text-align:center;" width="16%"><b>Código</b></th>
                        <th style="text-align:center;" width="50%"><b>Descripción.</b></th>
                        <th style="text-align:center;" width="21%"><b>Unid.</b></th>
                    </tr>
                ';

      foreach ($detalle as $item) {
        $cont++;
        if (strlen($item->descripcion) > 39) {
          $cont++;
        }

        $tabla = $tabla . '<tr>'
          . '<td style="text-align:rigth"  width="13%">' . $esp . round($item->cantidad, 2) . $esp . '</td>'
          . '<td style="text-align:left"  width="16%">' . $esp . $item->bien_codigo . $esp . '</td>'
          . '<td style="text-align:left"  width="50%">' . $esp . $item->descripcion . $esp . '</td>'
          . '<td style="text-align:center"  width="21%">' . $esp . $item->unidadMedida . $esp . '</td>'
          . '</tr>';
      };
    }

    //fin tabla detalle
    $tabla = $tabla . '</table>';

    $pdf->writeHTML($tabla, true, false, false, false, 'C');

    $espaciado = 131 - ($cont * 4.4 + $espacioComentario);
    if ($espaciado < 15) {
      //            $espaciado = 15;
      $pdf->AddPage();
    }

    //        $pdf->Ln($espaciado); // cada fila es 4, total=131 , antes:115

    $identificadorNegocio = null;
    if (!ObjectUtil::isEmpty($documentoDatoValor)) {
      $pdf->Ln(5);
      //        $borde=array('LTRB' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

      $pdf->Ln(5);

      if ($identificadorNegocio == 1) {
        $pdf->writeHTMLCell(0, 6, '', '', "<h4>TERMINOS Y CONDICIONES</h4>", 'TB', 1, 0, true, 'C', true);
      } else {;
        $pdf->writeHTMLCell(0, 6, '', '', "<h4>OTROS DATOS DEL DOCUMENTO</h4>", 'TB', 1, 0, true, 'C', true);
      }

      $pdf->Ln(1);
      $pdf->SetFillColor(255, 255, 255);
      foreach ($documentoDatoValor as $indice => $item) {
        if (
          $dataDocumentoTipo[0]['identificador_negocio'] == 23 &&
          ($item['documento_tipo_id'] == 2887 || $item['documento_tipo_id'] == 2888) && $item['valor'] == 'Virtual'
        ) {
          //NO MOSTRAR LA DIRECCION
          $txtDescripcion = $item['descripcion'];
          $valorItem = $item['valor'];
        } else {
          $txtDescripcion = $item['descripcion'];
          $valorItem = $item['valor'];

          if ($item['tipo'] == 1) {
            $valorItem = number_format($valorItem, 2, ".", ",");
          }

          if ($item['tipo'] == 3) {
            $valorItem = date_format((date_create($valorItem)), 'd/m/Y');
          }

          $pdf->MultiCell(60, 0, $txtDescripcion, 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
          $pdf->MultiCell(110, 0, $valorItem, 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');

          if ($indice < count($documentoDatoValor) - 1 || $identificadorNegocio == 1) {
            if (strlen($valorItem) > 55) {
              $pdf->Ln(10);
            } else {
              $pdf->Ln(6);
            }
          }

          if ($indice == count($documentoDatoValor) - 1) {
            $pdf->Ln(1);
          }
        }
      };
      $pdf->writeHTMLCell(0, 1, '', '', "", 'B', 1, 0, true, 'C', true);
    }


    $documentosRelacionImprimir = array();
    $dataDocumentoTipo;
    if ($dataDocumentoTipo[0]['identificador_negocio'] == 22) { //GUIA DE RECEPCION
      foreach ($dataDocumentoRelacion as $index => $itemR) {
        if ($itemR['identificador_negocio'] == 10) {
          $itemDR = array('serie_num' => $itemR['serie_numero_original'], 'nombre_doc' => $itemR['documento_tipo_descripcion']);
          array_push($documentosRelacionImprimir, $itemDR);

          $relacionOC = MovimientoNegocio::create()->obtenerDocumentosRelacionados($itemR['id']);

          foreach ($relacionOC as $itemROC) {
            if ($itemROC['identificador_negocio'] == 21 || $itemROC['identificador_negocio'] == 24) { //21: DUA  24: Commercial Invoice
              $itemDR = array('serie_num' => $itemROC['serie_numero'], 'nombre_doc' => $itemROC['documento_tipo'], 'serie_numero_original' => $itemROC['serie_numero_original']);
              array_push($documentosRelacionImprimir, $itemDR);
            }
          }
        }
      }
    }

    if ($dataDocumentoTipo[0]['identificador_negocio'] == 23) { //GUIA INTERNA DE TRANSFERENCIA
      $relacionDoc = MovimientoNegocio::create()->obtenerDocumentosRelacionados($documentoId);

      foreach ($relacionDoc as $itemDoc) {
        $itemDR = array('serie_num' => $itemDoc['serie_numero'], 'nombre_doc' => $itemDoc['documento_tipo'], 'serie_numero_original' => $itemDoc['serie_numero_original']);
        array_push($documentosRelacionImprimir, $itemDR);
      }
    }

    if (!ObjectUtil::isEmpty($documentosRelacionImprimir)) {
      $pdf->Ln(5);

      $pdf->Ln(5);

      $pdf->writeHTMLCell(0, 6, '', '', "<h4>DOCUMENTOS RELACIONADOS</h4>", 'TB', 1, 0, true, 'C', true);


      $pdf->Ln(1);
      $pdf->SetFillColor(255, 255, 255);
      foreach ($documentosRelacionImprimir as $indice => $item) {
        $txtDescripcion = $item['nombre_doc'];
        $serieNum = $item['serie_num'];
        $serieNumOriginal = $item['serie_numero_original'];

        $pdf->MultiCell(60, 0, $txtDescripcion, 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
        $pdf->MultiCell(30, 0, $serieNum, 0, 'C', 1, 0, '', '', true, 0, false, true, 40, 'T'); // 110

        if (!ObjectUtil::isEmpty($serieNumOriginal)) {
          $pdf->MultiCell(10, 0, ' | ', 0, 'C', 1, 0, '', '', true, 0, false, true, 40, 'T');
          $pdf->MultiCell(70, 0, $serieNumOriginal, 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
        }

        if ($indice < count($documentosRelacionImprimir) - 1) {
          if (strlen($serieNum) > 55) {
            $pdf->Ln(10);
          } else {
            $pdf->Ln(6);
          }
        }

        if ($indice == count($documentosRelacionImprimir) - 1) {
          $pdf->Ln(1);
        }
      };
      $pdf->writeHTMLCell(0, 1, '', '', "", 'B', 1, 0, true, 'C', true);
    }


    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  public function generarDocumentoPDFSolicitudCompra($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;

    // create new PDF document
    $pdf = new TCPDF('p', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); //'p' is vertical and it is the default and 'l' is the horizonal.
    // set document information
    $pdf->SetCreator('Minapp S.A.C.');
    $pdf->SetAuthor('Minapp S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    $cabeceraPDF1 = $dataEmpresa[0]['razon_social']
      //                ."\nSoluciones Integrales de Perforación"
    ;

    $cabeceraPDF2 = $dataEmpresa[0]['direccion'] . "\n" .
      "Tel: (044) 728609 - Anexo 108 / Cel: 979 754 211\n" .
      "E-mail: lnacarino@abcservicios.pe; Web site: www.abcservicios.pe\n" .
      "RUC: " . $dataEmpresa[0]['ruc'];
    $PDF_HEADER_LOGO_WIDTH = 40;
    $pdf->SetHeaderData('logo.PNG', $PDF_HEADER_LOGO_WIDTH, $cabeceraPDF1, $cabeceraPDF2, array(0, 0, 0), array(0, 0, 0));
    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $PDF_MARGIN_FOOTER = 10;
    $pdf->SetFooterMargin($PDF_MARGIN_FOOTER);

    // set auto page breaks
    $PDF_MARGIN_BOTTOM = 10;
    $pdf->SetAutoPageBreak(TRUE, $PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    // set font
    $pdf->SetFont('helvetica', '', 9);

    // add a page
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }


    //        foreach ($documentoDatoValor as $indice => $item) {
    //            $txtDescripcion = $item['descripcion'];
    //            $valorItem = $item['valor'];
    //
    //            if ($item['tipo'] == 1) {
    //                $valorItem = number_format($valorItem, 2, ".", ",");
    //            }
    //
    //            if ($item['tipo'] == 3) {
    //                $valorItem = date_format((date_create($valorItem)), 'd/m/Y');
    //            }
    //
    //            switch ((int) $item['id']) {
    //                case 5465:
    //                    $dtdTiempoEntrega = $valorItem;
    //                    break;
    //                case 5466:
    //                    $dtdCondicionPago = $valorItem;
    //                    break;
    //            }
    //        };
    //        $ventaCotizacion=strpos(($dataDocumento[0]['documento_tipo_descripcion']), 'V. Cotizacion');

    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];
    //        $titulo2=strtoupper($dataDocumento[0]['documento_tipo_descripcion'])." <br> ".$serieDocumento.$dataDocumento[0]['numero'];
    $pdf->Ln(5);
    $pdf->writeHTMLCell(0, 0, '', '', "<h2>" . $titulo . "</h2>", 0, 1, 0, true, 'C', true);
    $pdf->Ln(5);
    //        $pdf->Cell(0, 10, $titulo, 0, 1, 'C', 0, '', 1);
    //obtener la descripcion de persona de documento_tipo_dato
    $dataPersona = DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId, 5);
    $descripcionPersona = $dataPersona[0]['descripcion'];

    $fecha = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y');
    $pdf->Cell(0, 0, "Fecha: " . $fecha, 0, 1, 'L', 0, '', 0);


    $pdf->MultiCell(120, 0, $descripcionPersona . ": " . $dataDocumento[0]['nombre'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
    $pdf->MultiCell(60, 0, $dataDocumento[0]['persona_documento_tipo'] . ": " . $dataDocumento[0]['codigo_identificacion'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');

    $pdf->Ln(4);
    //        $pdf->Cell(0, 0, $descripcionPersona.": ".$dataDocumento[0]['nombre'], 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Dirección: " . $dataDocumento[0]['direccion'], 0, 1, 'L', 0, '', 0);
    //        $pdf->Cell(0, 0, $dataDocumento[0]['persona_documento_tipo'].": ".$dataDocumento[0]['codigo_identificacion'], 0, 1, 'L', 0, '', 0);

    if (!ObjectUtil::isEmpty($dataDocumento[0]['descripcion'])) {
      $pdf->Cell(0, 0, "Descripción: " . $dataDocumento[0]['descripcion'], 0, 1, 'L', 0, '', 0);
    }
    //        $pdf->Cell(0, 0, "Moneda: ".$dataDocumento[0]['moneda_descripcion'], 0, 1, 'L', 0, '', 0);

    if (!ObjectUtil::isEmpty($dataDocumento[0]['comentario'])) {
      $pdf->Ln(6);
      $pdf->Cell(0, 0, $dataDocumento[0]['comentario'], 0, 1, 'L', 0, '', 0);
    }

    //espacio
    $pdf->Ln(5);

    //detalle
    $esp = '&nbsp;&nbsp;'; //espacio en blanco

    $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                <tr>
                    <th style="text-align:center;" width="5%"><b>Item</b></th>
                    <th style="text-align:center;" width="18%"><b>Código</b></th>
                    <th style="text-align:center;" width="50%"><b>Descripción</b></th>
                    <th style="text-align:center;" width="11%"><b>Cant.</b></th>
                    <th style="text-align:center;" width="16%"><b>U.M.</b></th>
                </tr>
            ';

    foreach ($detalle as $index => $item) {
      $tabla = $tabla . '<tr>'
        . '<td style="text-align:rigth"  width="5%">' . $esp . ($index + 1) . $esp . '</td>'
        . '<td style="text-align:left"  width="18%">' . $esp . $item->bien_codigo . $esp . '</td>'
        . '<td style="text-align:left"  width="50%">' . $esp . $item->descripcion . $esp . '</td>'
        . '<td style="text-align:rigth"  width="11%">' . $esp . round($item->cantidad, 2) . $esp . '</td>'
        . '<td style="text-align:left"  width="16%">' . $esp . $item->unidadMedida . $esp . '</td>'
        . '</tr>';
    };

    //fin tabla detalle
    $tabla = $tabla . '</table>';

    $pdf->writeHTML($tabla, true, false, false, false, 'C');

    $pdf->Ln(175);

    //Close and output PDF document
    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  public function generarDocumentoPDFCotizacionCompra($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;

    // create new PDF document
    $pdf = new TCPDF('p', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); //'p' is vertical and it is the default and 'l' is the horizonal.
    // set document information
    $pdf->SetCreator('Minapp S.A.C.');
    $pdf->SetAuthor('Minapp S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    $cabeceraPDF1 = $dataEmpresa[0]['razon_social']
      //                ."\nSoluciones Integrales de Perforación"
    ;

    $cabeceraPDF2 = $dataEmpresa[0]['direccion'] . "\n" .
      "Tel: (044) 728609 - Anexo 108 / Cel: 979 754 211\n" .
      "E-mail: lnacarino@abcservicios.pe; Web site: www.abcservicios.pe\n" .
      "RUC: " . $dataEmpresa[0]['ruc'];
    $PDF_HEADER_LOGO_WIDTH = 40;
    $pdf->SetHeaderData('logo.PNG', $PDF_HEADER_LOGO_WIDTH, $cabeceraPDF1, $cabeceraPDF2, array(0, 0, 0), array(0, 0, 0));
    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $PDF_MARGIN_FOOTER = 10;
    $pdf->SetFooterMargin($PDF_MARGIN_FOOTER);

    // set auto page breaks
    $PDF_MARGIN_BOTTOM = 10;
    $pdf->SetAutoPageBreak(TRUE, $PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    // set font
    $pdf->SetFont('helvetica', '', 9);

    // add a page
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }


    foreach ($documentoDatoValor as $indice => $item) {
      $txtDescripcion = $item['descripcion'];
      $valorItem = $item['valor'];

      if ($item['tipo'] == 1) {
        $valorItem = number_format($valorItem, 2, ".", ",");
      }

      if ($item['tipo'] == 3) {
        $valorItem = date_format((date_create($valorItem)), 'd/m/Y');
      }

      switch ((int) $item['documento_tipo_id']) {
        case 2620:
          $dtdTiempoEntrega = $valorItem;
          break;
        case 2621:
          $dtdCondicionComercial = $valorItem;
          break;
        case 2624:
          $dtdLugarEntrega = $valorItem;
          break;
        case 2622:
          $dtdNumeroCuenta = $valorItem;
          break;
      }
    };

    //        $ventaCotizacion=strpos(($dataDocumento[0]['documento_tipo_descripcion']), 'V. Cotizacion');

    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];
    //        $titulo2=strtoupper($dataDocumento[0]['documento_tipo_descripcion'])." <br> ".$serieDocumento.$dataDocumento[0]['numero'];
    $pdf->Ln(5);
    $pdf->writeHTMLCell(0, 0, '', '', "<h2>" . $titulo . "</h2>", 0, 1, 0, true, 'C', true);
    $pdf->Ln(5);
    //        $pdf->Cell(0, 10, $titulo, 0, 1, 'C', 0, '', 1);
    //obtener la descripcion de persona de documento_tipo_dato
    $dataPersona = DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId, 5);
    $descripcionPersona = $dataPersona[0]['descripcion'];

    $fecha = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y');
    $pdf->Cell(0, 0, "Fecha: " . $fecha, 0, 1, 'L', 0, '', 0);


    $pdf->MultiCell(120, 0, $descripcionPersona . ": " . $dataDocumento[0]['nombre'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
    $pdf->MultiCell(60, 0, $dataDocumento[0]['persona_documento_tipo'] . ": " . $dataDocumento[0]['codigo_identificacion'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');

    $pdf->Ln(4);
    //        $pdf->Cell(0, 0, $descripcionPersona.": ".$dataDocumento[0]['nombre'], 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Dirección: " . $dataDocumento[0]['direccion'], 0, 1, 'L', 0, '', 0);
    //        $pdf->Cell(0, 0, $dataDocumento[0]['persona_documento_tipo'].": ".$dataDocumento[0]['codigo_identificacion'], 0, 1, 'L', 0, '', 0);

    if (!ObjectUtil::isEmpty($dataDocumento[0]['descripcion'])) {
      $pdf->Cell(0, 0, "Descripción: " . $dataDocumento[0]['descripcion'], 0, 1, 'L', 0, '', 0);
    }
    $pdf->Cell(0, 0, "Moneda: " . $dataDocumento[0]['moneda_descripcion'], 0, 1, 'L', 0, '', 0);

    $espacioComentario = 0;
    if (!ObjectUtil::isEmpty($dataDocumento[0]['comentario'])) {
      $pdf->Ln(7);
      $pdf->Cell(0, 0, $dataDocumento[0]['comentario'], 0, 1, 'L', 0, '', 0);
      $espacioComentario = 12;
    }

    //espacio
    $pdf->Ln(5);

    //detalle
    $esp = '&nbsp;&nbsp;'; //espacio en blanco

    $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                <tr>
                    <th style="text-align:center;" width="5%"><b>Item</b></th>
                    <th style="text-align:center;" width="13%"><b>Código</b></th>
                    <th style="text-align:center;" width="42%"><b>Descripción</b></th>
                    <th style="text-align:center;" width="6%"><b>Cant.</b></th>
                    <th style="text-align:center;" width="11%"><b>U.M.</b></th>
                    <th style="text-align:center;" width="11%"><b>P. Unit.</b></th>
                    <th style="text-align:center;" width="12%"><b>Sub Total</b></th>
                </tr>
            ';

    $cont = 0;
    foreach ($detalle as $index => $item) {
      $cont++;
      if (strlen($item->descripcion) > 39) {
        $cont++;
      }
      $tabla = $tabla . '<tr>'
        . '<td style="text-align:rigth"  width="5%">' . $esp . ($index + 1) . $esp . '</td>'
        . '<td style="text-align:left"  width="13%">' . $esp . $item->bien_codigo . $esp . '</td>'
        . '<td style="text-align:left"  width="42%">' . $esp . $item->descripcion . $esp . '</td>'
        . '<td style="text-align:rigth"  width="6%">' . $esp . round($item->cantidad, 2) . $esp . '</td>'
        . '<td style="text-align:left"  width="11%">' . $esp . $item->unidadMedida . $esp . '</td>'
        . '<td style="text-align:rigth"  width="11%">' . $esp . number_format($item->precioUnitario, 2) . $esp . '</td>'
        . '<td style="text-align:rigth"  width="12%">' . $esp . number_format($item->importe, 2) . $esp . '</td>'
        . '</tr>';
    };

    //fin tabla detalle
    $tabla = $tabla . '</table>';

    $pdf->writeHTML($tabla, true, false, false, false, 'C');

    $espaciado = 145 - ($cont * 4.4 + $espacioComentario);
    if ($espaciado < 15) {
      $espaciado = 15;
    }

    $pdf->Ln($espaciado); // cada fila es 4, total=131
    //        $pdf->Ln(125);
    //        IF(!ObjectUtil::isEmpty($documentoDatoValor)){
    $pdf->Ln(5);
    //        $borde=array('LTRB' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

    $pdf->Ln(5);
    $pdf->SetFillColor(255, 255, 255);

    $tabla = '<table cellspacing="0" cellpadding="1" style="text-align:left; border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px">
                        <tr>
                          <td width="2%"></td>
                          <td width="31%"></td>
                          <td width="25%"></td>
                          <td width="8%"></td>
                          <td width="14%"></td>
                          <td width="20%"></td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td width="31%"></td>
                          <td width="25%"></td>
                          <td width="8%"></td>
                          <td width="14%">' . $esp . 'Subtotal ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
                          <td style="text-align:rigth" width="20%">' . $esp . number_format($dataDocumento[0]['subtotal'], 2) . $esp . '</td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td style="text-align:center; border-style: solid; border-top-width: 1px;"  width="31%">' . $esp . 'Hecho por:</td>
                          <td width="25%"></td>
                          <td width="8%"></td>
                          <td width="14%">' . $esp . 'IGV 18% ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
                          <td style="text-align:rigth" width="20%">' . $esp . number_format($dataDocumento[0]['igv'], 2) . $esp . '</td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td style="text-align:center;"  width="31%">' . $esp . $dataDocumento[0]['usuario'] . $esp . '</td>
                          <td width="25%"></td>
                          <td width="8%"></td>
                          <td width="14%">' . $esp . 'TOTAL ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
                          <td style="text-align:rigth" width="20%">' . $esp . number_format($dataDocumento[0]['total'], 2) . $esp . '</td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td width="31%"></td>
                          <td width="25%"></td>
                          <td width="8%"></td>
                          <td width="14%"></td>
                          <td width="20%"></td>
                        </tr>
                    </table>';

    $pdf->writeHTML($tabla, true, false, false, false, 'C');
    //        }

    $pdf->Cell(0, 0, "Fecha de entrega: " . date_format((date_create($dataDocumento[0]['fecha_tentativa'])), 'd/m/Y'), 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Tiempo de entrega: " . $dtdTiempoEntrega, 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Número de cuenta: " . $dtdNumeroCuenta, 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Lugar de entrega: " . $dtdLugarEntrega, 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Condiciones comerciales: " . $dtdCondicionComercial, 0, 1, 'L', 0, '', 0);

    //Close and output PDF document
    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  public function generarDocumentoPDFOrdenCompra($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;
    $dataDocumentoRelacion = $data->documentoRelacionado;

    $usuarioRequerimiento = null;
    foreach ($dataDocumentoRelacion as $index => $itemR) {
      if ($itemR['identificador_negocio'] == 13) {
        $usuarioRequerimiento = $itemR['usuario'];
      }
    }

    if (ObjectUtil::isEmpty($usuarioRequerimiento)) {
      $usuarioRequerimiento = $dataDocumento[0]['usuario'];
    }

    // create new PDF document
    $pdf = new TCPDF('p', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); //'p' is vertical and it is the default and 'l' is the horizonal.
    // set document information
    $pdf->SetCreator('Minapp S.A.C.');
    $pdf->SetAuthor('Minapp S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    $cabeceraPDF1 = $dataEmpresa[0]['razon_social']
      //                ."\nSoluciones Integrales de Perforación"
    ;

    $cabeceraPDF2 = $dataEmpresa[0]['direccion'] . "\n" .
      "Tel: (044) 728609 - Anexo 108 / Cel: 979 754 211\n" .
      "E-mail: lnacarino@abcservicios.pe; Web site: www.abcservicios.pe\n" .
      "RUC: " . $dataEmpresa[0]['ruc'];
    $PDF_HEADER_LOGO_WIDTH = 40;
    $pdf->SetHeaderData('logo.PNG', $PDF_HEADER_LOGO_WIDTH, $cabeceraPDF1, $cabeceraPDF2, array(0, 0, 0), array(0, 0, 0));
    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $PDF_MARGIN_FOOTER = 10;
    $pdf->SetFooterMargin($PDF_MARGIN_FOOTER);

    // set auto page breaks
    $PDF_MARGIN_BOTTOM = 10;
    $pdf->SetAutoPageBreak(TRUE, $PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    // set font
    $pdf->SetFont('helvetica', '', 9);

    // add a page
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }


    foreach ($documentoDatoValor as $indice => $item) {
      $txtDescripcion = $item['descripcion'];
      $valorItem = $item['valor'];

      if ($item['tipo'] == 1) {
        $valorItem = number_format($valorItem, 2, ".", ",");
      }

      if ($item['tipo'] == 3) {
        $valorItem = date_format((date_create($valorItem)), 'd/m/Y');
      }

      switch ((int) $item['documento_tipo_id']) {
        case 2520:
          $dtdTiempoEntrega = $valorItem;
          break;
        case 2521:
          $dtdCondicionComercial = $valorItem;
          break;
        case 2618:
          $dtdLugarEntrega = $valorItem;
          break;
        case 2625:
          $dtdNumeroCuenta = $valorItem;
          break;
      }
    };

    //        $ventaCotizacion=strpos(($dataDocumento[0]['documento_tipo_descripcion']), 'V. Cotizacion');

    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];
    //        $titulo2=strtoupper($dataDocumento[0]['documento_tipo_descripcion'])." <br> ".$serieDocumento.$dataDocumento[0]['numero'];
    $pdf->Ln(5);
    $pdf->writeHTMLCell(0, 0, '', '', "<h2>" . $titulo . "</h2>", 0, 1, 0, true, 'C', true);
    $pdf->Ln(5);
    //        $pdf->Cell(0, 10, $titulo, 0, 1, 'C', 0, '', 1);
    //obtener la descripcion de persona de documento_tipo_dato
    $dataPersona = DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId, 5);
    $descripcionPersona = $dataPersona[0]['descripcion'];

    $fecha = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y');
    $pdf->Cell(0, 0, "Fecha: " . $fecha, 0, 1, 'L', 0, '', 0);


    $pdf->MultiCell(120, 0, $descripcionPersona . ": " . $dataDocumento[0]['nombre'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
    $pdf->MultiCell(60, 0, $dataDocumento[0]['persona_documento_tipo'] . ": " . $dataDocumento[0]['codigo_identificacion'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');

    $pdf->Ln(4);
    //        $pdf->Cell(0, 0, $descripcionPersona.": ".$dataDocumento[0]['nombre'], 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Dirección: " . $dataDocumento[0]['direccion'], 0, 1, 'L', 0, '', 0);
    //        $pdf->Cell(0, 0, $dataDocumento[0]['persona_documento_tipo'].": ".$dataDocumento[0]['codigo_identificacion'], 0, 1, 'L', 0, '', 0);

    if (!ObjectUtil::isEmpty($dataDocumento[0]['descripcion'])) {
      $pdf->Cell(0, 0, "Descripción: " . $dataDocumento[0]['descripcion'], 0, 1, 'L', 0, '', 0);
    }
    $pdf->Cell(0, 0, "Moneda: " . $dataDocumento[0]['moneda_descripcion'], 0, 1, 'L', 0, '', 0);

    $espacioComentario = 0;
    if (!ObjectUtil::isEmpty($dataDocumento[0]['comentario'])) {
      $pdf->Ln(7);
      $pdf->writeHTMLCell(0, 6, '', '', "<h4>COMENTARIO</h4>", 0, 1, 0, true, 'L', true); //'TB'->borde:0
      $pdf->Cell(0, 0, $dataDocumento[0]['comentario'], 0, 1, 'L', 0, '', 0);
      $espacioComentario = 12;
    }

    //espacio
    $pdf->Ln(5);

    //detalle
    $esp = '&nbsp;&nbsp;'; //espacio en blanco

    $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                <tr>
                    <th style="text-align:center;" width="5%"><b>Item</b></th>
                    <th style="text-align:center;" width="13%"><b>Código</b></th>
                    <th style="text-align:center;" width="42%"><b>Descripción</b></th>
                    <th style="text-align:center;" width="6%"><b>Cant.</b></th>
                    <th style="text-align:center;" width="11%"><b>U.M.</b></th>
                    <th style="text-align:center;" width="11%"><b>P. Unit.</b></th>
                    <th style="text-align:center;" width="12%"><b>Sub Total</b></th>
                </tr>
            ';

    $cont = 0;
    foreach ($detalle as $index => $item) {
      $cont++;
      if (strlen($item->descripcion) > 39) {
        $cont++;
      }

      $tabla = $tabla . '<tr>'
        . '<td style="text-align:rigth"  width="5%">' . $esp . ($index + 1) . $esp . '</td>'
        . '<td style="text-align:left"  width="13%">' . $esp . $item->bien_codigo . $esp . '</td>'
        . '<td style="text-align:left"  width="42%">' . $esp . $item->descripcion . $esp . '</td>'
        . '<td style="text-align:rigth"  width="6%">' . $esp . round($item->cantidad, 2) . $esp . '</td>'
        . '<td style="text-align:left"  width="11%">' . $esp . $item->unidadMedida . $esp . '</td>'
        . '<td style="text-align:rigth"  width="11%">' . $esp . number_format($item->precioUnitario, 2) . $esp . '</td>'
        . '<td style="text-align:rigth"  width="12%">' . $esp . number_format($item->importe, 2) . $esp . '</td>'
        . '</tr>';
    };

    //fin tabla detalle
    $tabla = $tabla . '</table>';

    $pdf->writeHTML($tabla, true, false, false, false, 'C');

    $espaciado = 131 - ($cont * 4.4 + $espacioComentario);
    if ($espaciado < 15) {
      $espaciado = 15;
    }

    $pdf->Ln($espaciado); // cada fila es 4, total=131 , antes:115
    if (!ObjectUtil::isEmpty($documentoDatoValor)) {
      $pdf->Ln(5);
      //        $borde=array('LTRB' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

      $pdf->Ln(5);
      $pdf->SetFillColor(255, 255, 255);

      $tabla = '<table cellspacing="0" cellpadding="1" style="text-align:left; border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px">
                        <tr>
                          <td width="2%"></td>
                          <td width="23%"></td>
                          <td width="8%"></td>
                          <td width="25%"></td>
                          <td width="8%"></td>
                          <td width="14%"></td>
                          <td width="20%"></td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td width="23%"></td>
                          <td width="8%"></td>
                          <td width="25%"></td>
                          <td width="8%"></td>
                          <td width="14%">' . $esp . 'Subtotal ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
                          <td style="text-align:rigth" width="20%">' . $esp . number_format($dataDocumento[0]['subtotal'], 2) . $esp . '</td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td style="text-align:center; border-style: solid; border-top-width: 1px;"  width="23%">' . $esp . 'Hecho por:</td>
                          <td width="8%"></td>
                          <td style="text-align:center; border-style: solid; border-top-width: 1px;"  width="25%">' . $esp . 'Aprobado por:</td>
                          <td width="8%"></td>
                          <td width="14%">' . $esp . 'IGV 18% ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
                          <td style="text-align:rigth" width="20%">' . $esp . number_format($dataDocumento[0]['igv'], 2) . $esp . '</td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td style="text-align:center;"  width="23%">' . $esp . $usuarioRequerimiento . $esp . '</td>
                          <td width="8%"></td>
                          <td style="text-align:center"  width="25%">' . $esp . $dataDocumento[0]['usuario'] . $esp . '</td>
                          <td width="8%"></td>
                          <td width="14%">' . $esp . 'TOTAL ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
                          <td style="text-align:rigth" width="20%">' . $esp . number_format($dataDocumento[0]['total'], 2) . $esp . '</td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td width="23%"></td>
                          <td width="8%"></td>
                          <td width="25%"></td>
                          <td width="8%"></td>
                          <td width="14%"></td>
                          <td width="20%"></td>
                        </tr>
                    </table>';

      $pdf->writeHTML($tabla, true, false, false, false, 'C');
    }

    $pdf->Cell(0, 0, "Fecha de entrega: " . date_format((date_create($dataDocumento[0]['fecha_tentativa'])), 'd/m/Y'), 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Tiempo de entrega: " . $dtdTiempoEntrega, 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Número de cuenta: " . $dtdNumeroCuenta, 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Lugar de entrega: " . $dtdLugarEntrega, 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Condiciones comerciales: " . $dtdCondicionComercial, 0, 1, 'L', 0, '', 0);

    $pdf->Ln(8);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell(0, 0, "Notas Importantes ", 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, " 1.- Esta O/C tiene validez hasta la fecha de entrega aqui indicada por la parte del PROVEEDOR, de no cumplir esta quedara ANULADA.", 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, " 2.- La FACTURA ORIGINAL se entregará de manera conjunta con copia SUNAT, 01 copia de GUIA DE REMISION y 01 copia de ORDEN DE COMPRA.", 0, 1, 'L', 0, '', 0);

    //Close and output PDF document
    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  public function generarDocumentoPDFSolicitudCompraExt($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;

    // create new PDF document
    $pdf = new TCPDF('p', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); //'p' is vertical and it is the default and 'l' is the horizonal.
    // set document information
    $pdf->SetCreator('Minapp S.A.C.');
    $pdf->SetAuthor('Minapp S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    $cabeceraPDF1 = $dataEmpresa[0]['razon_social']
      //                ."\nSoluciones Integrales de Perforación"
    ;

    $cabeceraPDF2 = $dataEmpresa[0]['direccion'] . "\n" .
      "Tel: (044) 728609 - Anexo 108 / Cel: 979 754 211\n" .
      "E-mail: lnacarino@abcservicios.pe; Web site: www.abcservicios.pe\n" .
      "RUC: " . $dataEmpresa[0]['ruc'];
    $PDF_HEADER_LOGO_WIDTH = 40;
    $pdf->SetHeaderData('logo.PNG', $PDF_HEADER_LOGO_WIDTH, $cabeceraPDF1, $cabeceraPDF2, array(0, 0, 0), array(0, 0, 0));
    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $PDF_MARGIN_FOOTER = 10;
    $pdf->SetFooterMargin($PDF_MARGIN_FOOTER);

    // set auto page breaks
    $PDF_MARGIN_BOTTOM = 10;
    $pdf->SetAutoPageBreak(TRUE, $PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    // set font
    $pdf->SetFont('helvetica', '', 9);

    // add a page
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }


    //        foreach ($documentoDatoValor as $indice => $item) {
    //            $txtDescripcion = $item['descripcion'];
    //            $valorItem = $item['valor'];
    //
    //            if ($item['tipo'] == 1) {
    //                $valorItem = number_format($valorItem, 2, ".", ",");
    //            }
    //
    //            if ($item['tipo'] == 3) {
    //                $valorItem = date_format((date_create($valorItem)), 'd/m/Y');
    //            }
    //
    //            switch ((int) $item['id']) {
    //                case 5541:
    //                    $dtdTiempoEntrega = $valorItem;
    //                    break;
    //                case 5542:
    //                    $dtdCondicionPago = $valorItem;
    //                    break;
    //            }
    //        };
    //        $ventaCotizacion=strpos(($dataDocumento[0]['documento_tipo_descripcion']), 'V. Cotizacion');
    $titulo = "PURCHASE REQUEST " . $serieDocumento . $dataDocumento[0]['numero'];
    //        $titulo=strtoupper($dataDocumento[0]['documento_tipo_descripcion'])." ".$serieDocumento.$dataDocumento[0]['numero'];
    $pdf->Ln(5);
    $pdf->writeHTMLCell(0, 0, '', '', "<h2>" . $titulo . "</h2>", 0, 1, 0, true, 'C', true);
    $pdf->Ln(5);
    //        $pdf->Cell(0, 10, $titulo, 0, 1, 'C', 0, '', 1);
    //obtener la descripcion de persona de documento_tipo_dato
    //        $dataPersona=DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId,5);
    $descripcionPersona = "Provider";

    $fecha = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y');
    $pdf->Cell(0, 0, "Date: " . $fecha, 0, 1, 'L', 0, '', 0);

    $pdf->Cell(0, 0, $descripcionPersona . ": " . $dataDocumento[0]['nombre'], 0, 1, 'L', 0, '', 0);
    //        $pdf->Cell(0, 0, $descripcionPersona.": ".$dataDocumento[0]['nombre'], 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Address: " . $dataDocumento[0]['direccion'], 0, 1, 'L', 0, '', 0);
    //        $pdf->Cell(0, 0, $dataDocumento[0]['persona_documento_tipo'].": ".$dataDocumento[0]['codigo_identificacion'], 0, 1, 'L', 0, '', 0);

    if (!ObjectUtil::isEmpty($dataDocumento[0]['descripcion'])) {
      $pdf->Cell(0, 0, "Description: " . $dataDocumento[0]['descripcion'], 0, 1, 'L', 0, '', 0);
    }

    //        $pdf->Cell(0, 0, "Coin: ".$dataDocumento[0]['moneda_descripcion'], 0, 1, 'L', 0, '', 0);

    if (!ObjectUtil::isEmpty($dataDocumento[0]['comentario'])) {
      $pdf->Ln(7);
      $pdf->Cell(0, 0, $dataDocumento[0]['comentario'], 0, 1, 'L', 0, '', 0);
    }

    //espacio
    $pdf->Ln(5);

    //detalle
    $esp = '&nbsp;&nbsp;'; //espacio en blanco

    $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                <tr>
                    <th style="text-align:center;" width="5%"><b>Item</b></th>
                    <th style="text-align:center;" width="18%"><b>Code</b></th>
                    <th style="text-align:center;" width="50%"><b>Description</b></th>
                    <th style="text-align:center;" width="11%"><b>QTY </b></th>
                    <th style="text-align:center;" width="16%"><b>Unit</b></th>
                </tr>
            ';

    foreach ($detalle as $index => $item) {
      $tabla = $tabla . '<tr>'
        . '<td style="text-align:rigth"  width="5%">' . $esp . ($index + 1) . $esp . '</td>'
        . '<td style="text-align:left"  width="18%">' . $esp . $item->bien_codigo . $esp . '</td>'
        . '<td style="text-align:left"  width="50%">' . $esp . $item->descripcion . $esp . '</td>'
        . '<td style="text-align:rigth"  width="11%">' . $esp . round($item->cantidad, 2) . $esp . '</td>'
        //                        . '<td style="text-align:left"  width="11%">' . $esp . $item->unidadMedida . $esp . '</td>'
        . '<td style="text-align:left"  width="16%">' . $esp . 'Pcs' . $esp . '</td>'
        . '</tr>';
    };

    //fin tabla detalle
    $tabla = $tabla . '</table>';

    $pdf->writeHTML($tabla, true, false, false, false, 'C');

    $pdf->Ln(175);

    //Close and output PDF document
    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  public function generarDocumentoPDFCotizacionCompraExt($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;

    // create new PDF document
    $pdf = new TCPDF('p', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); //'p' is vertical and it is the default and 'l' is the horizonal.
    // set document information
    $pdf->SetCreator('Minapp S.A.C.');
    $pdf->SetAuthor('Minapp S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    $cabeceraPDF1 = $dataEmpresa[0]['razon_social']
      //                ."\nSoluciones Integrales de Perforación"
    ;

    $cabeceraPDF2 = $dataEmpresa[0]['direccion'] . "\n" .
      "Tel: (044) 728609 - Anexo 108 / Cel: 979 754 211\n" .
      "E-mail: lnacarino@abcservicios.pe; Web site: www.abcservicios.pe\n" .
      "RUC: " . $dataEmpresa[0]['ruc'];
    $PDF_HEADER_LOGO_WIDTH = 40;
    $pdf->SetHeaderData('logo.PNG', $PDF_HEADER_LOGO_WIDTH, $cabeceraPDF1, $cabeceraPDF2, array(0, 0, 0), array(0, 0, 0));
    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $PDF_MARGIN_FOOTER = 10;
    $pdf->SetFooterMargin($PDF_MARGIN_FOOTER);

    // set auto page breaks
    $PDF_MARGIN_BOTTOM = 10;
    $pdf->SetAutoPageBreak(TRUE, $PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    // set font
    $pdf->SetFont('helvetica', '', 9);

    // add a page
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }


    foreach ($documentoDatoValor as $indice => $item) {
      $txtDescripcion = $item['descripcion'];
      $valorItem = $item['valor'];

      if ($item['tipo'] == 1) {
        $valorItem = number_format($valorItem, 2, ".", ",");
      }

      if ($item['tipo'] == 3) {
        $valorItem = date_format((date_create($valorItem)), 'd/m/Y');
      }

      switch ((int) $item['documento_tipo_id']) {
        case 2612:
          $dtdFormaPago = $valorItem;
          break;
        case 2627:
          $dtdTiempoEntrega = $valorItem;
          break;
        case 2628:
          $dtdCondicionComercial = $valorItem;
          break;
      }
    };

    //        $ventaCotizacion=strpos(($dataDocumento[0]['documento_tipo_descripcion']), 'V. Cotizacion');
    $titulo = "PURCHASE REQUIREMENT " . $serieDocumento . $dataDocumento[0]['numero'];
    //        $titulo=strtoupper($dataDocumento[0]['documento_tipo_descripcion'])." ".$serieDocumento.$dataDocumento[0]['numero'];
    $pdf->Ln(5);
    $pdf->writeHTMLCell(0, 0, '', '', "<h2>" . $titulo . "</h2>", 0, 1, 0, true, 'C', true);
    $pdf->Ln(5);
    //        $pdf->Cell(0, 10, $titulo, 0, 1, 'C', 0, '', 1);
    //obtener la descripcion de persona de documento_tipo_dato
    //        $dataPersona=DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId,5);
    $descripcionPersona = "Provider";

    $fecha = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y');
    $pdf->Cell(0, 0, "Date: " . $fecha, 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, $descripcionPersona . ": " . $dataDocumento[0]['nombre'], 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Address: " . $dataDocumento[0]['direccion'], 0, 1, 'L', 0, '', 0);
    //        $pdf->Cell(0, 0, $dataDocumento[0]['persona_documento_tipo'].": ".$dataDocumento[0]['codigo_identificacion'], 0, 1, 'L', 0, '', 0);

    if (!ObjectUtil::isEmpty($dataDocumento[0]['descripcion'])) {
      $pdf->Cell(0, 0, "Description: " . $dataDocumento[0]['descripcion'], 0, 1, 'L', 0, '', 0);
    }

    //        $pdf->Cell(0, 0, "Coin: ".$dataDocumento[0]['moneda_descripcion'], 0, 1, 'L', 0, '', 0);

    $espacioComentario = 0;
    if (!ObjectUtil::isEmpty($dataDocumento[0]['comentario'])) {
      $pdf->Ln(7);
      $pdf->Cell(0, 0, $dataDocumento[0]['comentario'], 0, 1, 'L', 0, '', 0);
      $espacioComentario = 12;
    }

    //espacio
    $pdf->Ln(5);

    //detalle
    $esp = '&nbsp;&nbsp;'; //espacio en blanco

    $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                <tr>
                    <th style="text-align:center;" width="5%"><b>Item</b></th>
                    <th style="text-align:center;" width="18%"><b>Code</b></th>
                    <th style="text-align:center;" width="50%"><b>Description</b></th>
                    <th style="text-align:center;" width="11%"><b>QTY </b></th>
                    <th style="text-align:center;" width="16%"><b>Unit</b></th>
                </tr>
            ';

    $cont = 0;
    foreach ($detalle as $index => $item) {
      $cont++;
      if (strlen($item->descripcion) > 39) {
        $cont++;
      }

      $tabla = $tabla . '<tr>'
        . '<td style="text-align:rigth"  width="5%">' . $esp . ($index + 1) . $esp . '</td>'
        . '<td style="text-align:left"  width="18%">' . $esp . $item->bien_codigo . $esp . '</td>'
        . '<td style="text-align:left"  width="50%">' . $esp . $item->descripcion . $esp . '</td>'
        . '<td style="text-align:rigth"  width="11%">' . $esp . round($item->cantidad, 2) . $esp . '</td>'
        //                        . '<td style="text-align:left"  width="11%">' . $esp . $item->unidadMedida . $esp . '</td>'
        . '<td style="text-align:left"  width="16%">' . $esp . 'Pcs' . $esp . '</td>'
        . '</tr>';
    };

    //fin tabla detalle
    $tabla = $tabla . '</table>';

    $pdf->writeHTML($tabla, true, false, false, false, 'C');

    $espaciado = 151 - ($cont * 4.4 + $espacioComentario);
    if ($espaciado < 15) {
      $espaciado = 15;
    }

    $pdf->Ln($espaciado); // cada fila es 4, total=131 , antes:115
    //        $pdf->Ln(135);
    if (!ObjectUtil::isEmpty($documentoDatoValor)) {
      $pdf->Ln(5);
      //        $borde=array('LTRB' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

      $pdf->Ln(5);
      $pdf->SetFillColor(255, 255, 255);

      $tabla = '<table cellspacing="0" cellpadding="1" style="text-align:left; border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px">
                        <tr>
                          <td width="30%"></td>
                          <td width="40%"></td>
                          <td width="30%"></td>
                        </tr>
                        <tr>
                          <td width="30%"></td>
                          <td width="40%"></td>
                          <td width="30%"></td>
                        </tr>
                        <tr>
                          <td width="30%"></td>
                          <td style="text-align:center; border-style: solid; border-top-width: 1px;"  width="40%">' . $esp . 'Made by:</td>
                          <td width="30%"></td>
                        </tr>
                        <tr>
                          <td width="30%"></td>
                          <td style="text-align:center;"  width="40%">' . $esp . $dataDocumento[0]['usuario'] . $esp . '</td>
                          <td width="30%"></td>
                        </tr>
                        <tr>
                          <td width="30%"></td>
                          <td width="40%"></td>
                          <td width="30%"></td>
                        </tr>
                    </table>';

      $pdf->writeHTML($tabla, true, false, false, false, 'C');
    }

    $pdf->Cell(0, 0, "Deadtime: " . date_format((date_create($dataDocumento[0]['fecha_tentativa'])), 'd/m/Y'), 0, 1, 'L', 0, '', 0);

    $pdf->MultiCell(120, 0, "Payment form: " . $dtdFormaPago, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
    $pdf->MultiCell(60, 0, "Delivery time: " . $dtdTiempoEntrega, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');

    $pdf->Ln(4);
    //        $pdf->Cell(0, 0, "Place of delivery: ".$dtdLugarEntrega, 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Incoterms: " . $dtdCondicionComercial, 0, 1, 'L', 0, '', 0);

    //Close and output PDF document
    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  public function generarDocumentoPDFOrdenCompraExt($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;
    $dataDocumentoRelacion = $data->documentoRelacionado;

    $usuarioRequerimiento = null;
    foreach ($dataDocumentoRelacion as $index => $itemR) {
      if ($itemR['identificador_negocio'] == 14) {
        $usuarioRequerimiento = $itemR['usuario'];
      }
    }

    if (ObjectUtil::isEmpty($usuarioRequerimiento)) {
      $usuarioRequerimiento = $dataDocumento[0]['usuario'];
    }

    // create new PDF document
    $pdf = new TCPDF('p', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); //'p' is vertical and it is the default and 'l' is the horizonal.
    // set document information
    $pdf->SetCreator('Minapp S.A.C.');
    $pdf->SetAuthor('Minapp S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    $cabeceraPDF1 = $dataEmpresa[0]['razon_social']
      //                ."\nSoluciones Integrales de Perforación"
    ;

    $cabeceraPDF2 = $dataEmpresa[0]['direccion'] . "\n" .
      "Tel: (044) 728609 - Anexo 108 / Cel: 979 754 211\n" .
      "E-mail: lnacarino@abcservicios.pe; Web site: www.abcservicios.pe\n" .
      "RUC: " . $dataEmpresa[0]['ruc'];
    $PDF_HEADER_LOGO_WIDTH = 40;
    $pdf->SetHeaderData('logo.PNG', $PDF_HEADER_LOGO_WIDTH, $cabeceraPDF1, $cabeceraPDF2, array(0, 0, 0), array(0, 0, 0));
    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $PDF_MARGIN_FOOTER = 10;
    $pdf->SetFooterMargin($PDF_MARGIN_FOOTER);

    // set auto page breaks
    $PDF_MARGIN_BOTTOM = 10;
    $pdf->SetAutoPageBreak(TRUE, $PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    // set font
    $pdf->SetFont('helvetica', '', 9);

    // add a page
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }


    foreach ($documentoDatoValor as $indice => $item) {
      $txtDescripcion = $item['descripcion'];
      $valorItem = $item['valor'];

      if ($item['tipo'] == 1) {
        $valorItem = number_format($valorItem, 2, ".", ",");
      }

      if ($item['tipo'] == 3) {
        $valorItem = date_format((date_create($valorItem)), 'd/m/Y');
      }

      switch ((int) $item['documento_tipo_id']) {
        case 2613:
          $dtdFormaPago = $valorItem;
          break;
        case 2614:
          $dtdCodigoPedido = $valorItem;
          break;
        case 2596:
          $dtdTiempoEntrega = $valorItem;
          break;
        case 2597:
          $dtdCondicionComercial = $valorItem;
          break;
        case 5537:
          $dtdLugarEntrega = $valorItem;
          break;
      }
    };

    //        $ventaCotizacion=strpos(($dataDocumento[0]['documento_tipo_descripcion']), 'V. Cotizacion');
    $titulo = "PURCHASE ORDER " . $serieDocumento . $dataDocumento[0]['numero'];
    //        $titulo=strtoupper($dataDocumento[0]['documento_tipo_descripcion'])." ".$serieDocumento.$dataDocumento[0]['numero'];
    $pdf->Ln(5);
    $pdf->writeHTMLCell(0, 0, '', '', "<h2>" . $titulo . "</h2>", 0, 1, 0, true, 'C', true);
    $pdf->Ln(5);
    //        $pdf->Cell(0, 10, $titulo, 0, 1, 'C', 0, '', 1);
    //obtener la descripcion de persona de documento_tipo_dato
    //        $dataPersona=DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId,5);
    $descripcionPersona = "Provider";

    $fecha = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y');
    $pdf->Cell(0, 0, "Date: " . $fecha, 0, 1, 'L', 0, '', 0);


    $pdf->MultiCell(120, 0, $descripcionPersona . ": " . $dataDocumento[0]['nombre'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
    $pdf->MultiCell(60, 0, "Order: " . $dtdCodigoPedido, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');

    $espacioNombrePersona = 0;
    if (!ObjectUtil::isEmpty($dataDocumento[0]['nombre'])) {
      $lon = strlen($dataDocumento[0]['nombre']);
      if (strlen($dataDocumento[0]['nombre']) >= 55) {
        $pdf->Ln(3);
      }
      $espacioNombrePersona = 3;
    }

    $pdf->Ln(4);
    //        $pdf->Cell(0, 0, $descripcionPersona.": ".$dataDocumento[0]['nombre'], 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Address: " . $dataDocumento[0]['direccion'], 0, 1, 'L', 0, '', 0);
    //        $pdf->Cell(0, 0, $dataDocumento[0]['persona_documento_tipo'].": ".$dataDocumento[0]['codigo_identificacion'], 0, 1, 'L', 0, '', 0);

    if (!ObjectUtil::isEmpty($dataDocumento[0]['descripcion'])) {
      $pdf->Cell(0, 0, "Description: " . $dataDocumento[0]['descripcion'], 0, 1, 'L', 0, '', 0);
    }

    //        $pdf->Cell(0, 0, "Coin: ".$dataDocumento[0]['moneda_descripcion'], 0, 1, 'L', 0, '', 0);

    $espacioComentario = 0;
    if (!ObjectUtil::isEmpty($dataDocumento[0]['comentario'])) {
      $pdf->Ln(7);
      $pdf->Cell(0, 0, $dataDocumento[0]['comentario'], 0, 1, 'L', 0, '', 0);
      $espacioComentario = 12;
    }

    //espacio
    $pdf->Ln(5);

    //detalle
    $esp = '&nbsp;&nbsp;'; //espacio en blanco

    $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                <tr>
                    <th style="text-align:center;" width="5%"><b>Item</b></th>
                    <th style="text-align:center;" width="13%"><b>Code</b></th>
                    <th style="text-align:center;" width="42%"><b>Description</b></th>
                    <th style="text-align:center;" width="6%"><b>QTY </b></th>
                    <th style="text-align:center;" width="11%"><b>Unit</b></th>
                    <th style="text-align:center;" width="11%"><b>Price Unit</b></th>
                    <th style="text-align:center;" width="12%"><b>Total Price</b></th>
                </tr>
            ';

    $cont = 0;
    foreach ($detalle as $index => $item) {
      $cont++;
      if (strlen($item->descripcion) > 39 || strlen($item->bien_codigo) > 11) {
        $cont++;
      }

      $tabla = $tabla . '<tr>'
        . '<td style="text-align:rigth"  width="5%">' . $esp . ($index + 1) . $esp . '</td>'
        . '<td style="text-align:left"  width="13%">' . $esp . $item->bien_codigo . $esp . '</td>'
        . '<td style="text-align:left"  width="42%">' . $esp . $item->descripcion . $esp . '</td>'
        . '<td style="text-align:rigth"  width="6%">' . $esp . round($item->cantidad, 2) . $esp . '</td>'
        //                        . '<td style="text-align:left"  width="11%">' . $esp . $item->unidadMedida . $esp . '</td>'
        . '<td style="text-align:left"  width="11%">' . $esp . 'Pcs' . $esp . '</td>'
        . '<td style="text-align:rigth"  width="11%">' . $esp . number_format($item->precioUnitario, 2) . $esp . '</td>'
        . '<td style="text-align:rigth"  width="12%">' . $esp . number_format($item->importe, 2) . $esp . '</td>'
        . '</tr>';
    };

    //fin tabla detalle
    $tabla = $tabla . '</table>';

    $pdf->writeHTML($tabla, true, false, false, false, 'C');

    $espaciado = 151 - ($cont * 4.1 + $espacioComentario + $espacioNombrePersona);
    //        if ($espaciado < 15) {
    //            $espaciado = 15;
    //        }

    if ($espaciado < 5) {
      $pdf->AddPage();
    } else {
      $pdf->Ln($espaciado); // cada fila es 4, total=131 , antes:115
    }
    //        $pdf->Ln(135);
    if (!ObjectUtil::isEmpty($documentoDatoValor)) {
      $pdf->Ln(5);
      //        $borde=array('LTRB' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

      $pdf->Ln(5);
      $pdf->SetFillColor(255, 255, 255);

      $tabla = '<table cellspacing="0" cellpadding="1" style="text-align:left; border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px">
                        <tr>
                          <td width="2%"></td>
                          <td width="23%"></td>
                          <td width="8%"></td>
                          <td width="25%"></td>
                          <td width="8%"></td>
                          <td width="14%"></td>
                          <td width="20%"></td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td width="23%"></td>
                          <td width="8%"></td>
                          <td width="25%"></td>
                          <td width="8%"></td>
                          <td width="14%"></td>
                          <td style="text-align:rigth" width="20%"></td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td style="text-align:center; border-style: solid; border-top-width: 1px;"  width="23%">' . $esp . 'Made by:</td>
                          <td width="8%"></td>
                          <td style="text-align:center; border-style: solid; border-top-width: 1px;"  width="25%">' . $esp . 'Approved by:</td>
                          <td width="8%"></td>
                          <td width="14%"></td>
                          <td style="text-align:rigth" width="20%"></td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td style="text-align:center;"  width="23%">' . $esp . $usuarioRequerimiento . $esp . '</td>
                          <td width="8%"></td>
                          <td style="text-align:center"  width="25%">' . $esp . $dataDocumento[0]['usuario'] . $esp . '</td>
                          <td width="8%"></td>
                          <td width="14%"  style="text-align:rigth">' . $esp . 'TOTAL ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
                          <td style="text-align:center" width="20%">' . $esp . number_format($dataDocumento[0]['total'], 2) . $esp . '</td>
                        </tr>
                        <tr>
                          <td width="2%"></td>
                          <td width="23%"></td>
                          <td width="8%"></td>
                          <td width="25%"></td>
                          <td width="8%"></td>
                          <td width="14%"></td>
                          <td width="20%"></td>
                        </tr>
                    </table>';

      $pdf->writeHTML($tabla, true, false, false, false, 'C');
    }

    $pdf->Cell(0, 0, "Deadtime: " . date_format((date_create($dataDocumento[0]['fecha_tentativa'])), 'd/m/Y'), 0, 1, 'L', 0, '', 0);

    $pdf->MultiCell(120, 0, "Payment form: " . $dtdFormaPago, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
    $pdf->MultiCell(60, 0, "Delivery time: " . $dtdTiempoEntrega, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');

    $pdf->Ln(4);
    //        $pdf->Cell(0, 0, "Place of delivery: ".$dtdLugarEntrega, 0, 1, 'L', 0, '', 0);
    $pdf->Cell(0, 0, "Incoterms: " . $dtdCondicionComercial, 0, 1, 'L', 0, '', 0);

    //Close and output PDF document
    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  //    public function generarDocumentoPDFCotizacion($documentoId, $comentario, $tipoSalidaPDF, $url, $data) {
  //        //$tipoSalidaPDF: F-> guarda local
  //        //obtenemos la data
  //        $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
  //        $documentoTipoId = $dataDocumentoTipo[0]['id'];
  //
  //        $dataDocumento = $data->dataDocumento;
  //        $documentoDatoValor = $data->documentoDatoValor;
  //        $detalle = $data->detalle;
  //        $dataEmpresa = $data->dataEmpresa;
  //
  //        // create new PDF document
  //        $pdf = new TCPDF('l', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); //'p' is vertical and it is the default and 'l' is the horizonal.
  //        // set document information
  //        $pdf->SetCreator('Minapp S.A.C.');
  //        $pdf->SetAuthor('Minapp S.A.C.');
  //        $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));
  //
  //        $cabeceraPDF1 = $dataEmpresa[0]['razon_social']
  ////                ."\nSoluciones Integrales de Perforación"
  //        ;
  //
  //        $cabeceraPDF2 = $dataEmpresa[0]['direccion'] . "\n" .
  //                "Telfs: 999641546\n" .
  //                "E-mail: ventas@abcservicios.pe; Web site: www.abcservicios.pe\n" .
  //                "RUC: " . $dataEmpresa[0]['ruc']
  //        ;
  //        $PDF_HEADER_LOGO_WIDTH = 40;
  //        $pdf->SetHeaderData('logo.PNG', $PDF_HEADER_LOGO_WIDTH, $cabeceraPDF1, $cabeceraPDF2, array(0, 0, 0), array(0, 0, 0));
  //        $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
  //        // set header and footer fonts
  //        $PDF_FONT_SIZE_MAIN = 9;
  //        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
  //        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
  //
  //        // set default monospaced font
  //        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
  //
  //        // set margins
  //        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
  //        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
  //        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
  //
  //        // set auto page breaks
  //        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
  //
  //        // set image scale factor
  //        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
  //
  //        // --------------GENERAR PDF-------------------------------------------
  //        // set font
  //        $pdf->SetFont('helvetica', '', 9);
  //
  //        // add a page
  //        $pdf->AddPage();
  //
  //        $serieDocumento = '';
  //        if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
  //            $serieDocumento = $dataDocumento[0]['serie'] . " - ";
  //        }
  //
  //
  //        foreach ($documentoDatoValor as $indice => $item) {
  //            $txtDescripcion = $item['descripcion'];
  //            $valorItem = $item['valor'];
  //
  //            if ($item['tipo'] == 1) {
  //                $valorItem = number_format($valorItem, 2, ".", ",");
  //            }
  //
  //            if ($item['tipo'] == 3) {
  //                $valorItem = date_format((date_create($valorItem)), 'd/m/Y');
  //            }
  //
  //            switch ((int) $item['documento_tipo_id']) {
  //                case 1850:
  //                    $dtdModoEntrega = $valorItem;
  //                    break;
  ////                case 2615:
  ////                    $dtdVigencia = $valorItem;
  ////                    break;
  //                case 2880:
  //                    $dtdAtencion = $valorItem;
  //                    break;
  //                case 2616:
  //                    $dtdTiempoEntrega = $valorItem;
  //                    break;
  //                case 2617:
  //                    $dtdNuestraRef = $valorItem;
  //                    break;
  //            }
  //        };
  //
  ////        $ventaCotizacion=strpos(($dataDocumento[0]['documento_tipo_descripcion']), 'V. Cotizacion');
  //
  //        $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];
  //        $titulo2 = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " <br> " . $serieDocumento . $dataDocumento[0]['numero'];
  //        $pdf->Ln(-20);
  //        $pdf->writeHTMLCell(0, 0, '', '', "<h2>" . $titulo2 . "</h2>", 0, 1, 0, true, 'R', true);
  //        $pdf->Ln(17);
  ////        $pdf->Cell(0, 10, $titulo, 0, 1, 'C', 0, '', 1);
  //        //obtener la descripcion de persona de documento_tipo_dato
  //        $dataPersona = DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId, 5);
  //        $descripcionPersona = $dataPersona[0]['descripcion'];
  //
  //        $fecha = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y');
  //        $pdf->Cell(0, 0, "Fecha: " . $fecha, 0, 1, 'L', 0, '', 0);
  //
  //
  //        $pdf->MultiCell(120, 0, $descripcionPersona . ": " . $dataDocumento[0]['nombre'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
  //        $pdf->MultiCell(60, 0, $dataDocumento[0]['persona_documento_tipo'] . ": " . $dataDocumento[0]['codigo_identificacion'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
  //
  //        $pdf->Ln(4);
  ////        $pdf->Cell(0, 0, $descripcionPersona.": ".$dataDocumento[0]['nombre'], 0, 1, 'L', 0, '', 0);
  //        $pdf->Cell(0, 0, "Dirección: " . $dataDocumento[0]['direccion'], 0, 1, 'L', 0, '', 0);
  ////        $pdf->Cell(0, 0, $dataDocumento[0]['persona_documento_tipo'].": ".$dataDocumento[0]['codigo_identificacion'], 0, 1, 'L', 0, '', 0);
  //
  //        if (!ObjectUtil::isEmpty($dataDocumento[0]['descripcion'])) {
  //            $pdf->Cell(0, 0, "Descripción: " . $dataDocumento[0]['descripcion'], 0, 1, 'L', 0, '', 0);
  //        }
  ////        $pdf->Cell(0, 0, "Moneda: ".$dataDocumento[0]['moneda_descripcion'], 0, 1, 'L', 0, '', 0);
  //        $pdf->MultiCell(120, 0, "Nuestra Ref: " . $dtdNuestraRef, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
  //        $pdf->MultiCell(60, 0, "Moneda: " . $dataDocumento[0]['moneda_descripcion'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
  //        $pdf->MultiCell(90, 0, "Tiempo de entrega: " . $dtdTiempoEntrega, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
  //
  //        $pdf->Ln(4);
  //        $pdf->Cell(0, 0, "Atención: " . $dtdAtencion, 0, 1, 'L', 0, '', 0);
  //
  //        $pdf->Ln(8);
  //        $pdf->Cell(0, 0, 'Estimados señores:', 0, 1, 'L', 0, '', 0);
  //        $pdf->Ln(1);
  //        $pdf->Cell(0, 0, 'De acuerdo a vuestro requerimiento nos es grato presentarles nuestra cotización de los siguientes Productos y/o Servicios:', 0, 1, 'L', 0, '', 0);
  //
  //
  //        //espacio
  //        $pdf->Ln(5);
  //
  //        //detalle
  //        $esp = '&nbsp;&nbsp;'; //espacio en blanco
  //
  //        $tabla = '<table cellspacing="0" cellpadding="1" border="1">
  //                <tr>
  //                    <th style="text-align:center;" width="5%"><b>Item</b></th>
  //                    <th style="text-align:center;" width="13%"><b>Código</b></th>
  //                    <th style="text-align:center;" width="56%"><b>Descripción</b></th>
  //                    <th style="text-align:center;" width="8%"><b>Cantidad</b></th>
  //                    <th style="text-align:center;" width="8%"><b>P. Unit.</b></th>
  //                    <th style="text-align:center;" width="10%"><b>Sub Total</b></th>
  //
  //                </tr>
  //            '
  //        ;
  //
  //        foreach ($detalle as $index => $item) {
  //            $dataStock = BienNegocio::create()->obtenerStockTotalXBienIDXUnidadMedidaId($item->bienId, $item->unidadMedidaId);
  //            $stock = $dataStock[0]['stock'];
  //            $disponible = $stock;
  //            if ($stock >= $item->cantidad) {
  //                $disponible = $item->cantidad;
  //            }
  //
  //            $tabla = $tabla . '<tr>'
  //                    . '<td style="text-align:rigth"  width="5%">' . $esp . ($index + 1) . $esp . '</td>'
  //                    . '<td style="text-align:left"  width="13%">' . $esp . $item->bien_codigo . $esp . '</td>'
  //                    . '<td style="text-align:left"  width="56%">' . $esp . $item->descripcion . $esp . '<br>'
  //                    . $item->movimientoBienComentario. $esp . '</td>'
  //                    . '<td style="text-align:rigth"  width="8%">' . $esp . round($item->cantidad, 2) . $esp . '</td>'
  //                    . '<td style="text-align:rigth"  width="8%">' . $esp . number_format($item->precioUnitario, 2) . $esp . '</td>'
  //                    . '<td style="text-align:rigth"  width="10%">' . $esp . number_format($item->importe, 2) . $esp . '</td>'
  //                    . '</tr>'
  //            ;
  //        };
  //
  //        //fin tabla detalle
  //        $tabla = $tabla . '</table>';
  //
  //        $pdf->writeHTML($tabla, true, false, false, false, 'C');
  //
  //
  //        IF (!ObjectUtil::isEmpty($documentoDatoValor)) {
  //            $pdf->Ln(5);
  //            //        $borde=array('LTRB' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
  //
  //            $pdf->Ln(5);
  //
  //
  //            $pdf->writeHTMLCell(0, 6, '', '', "<h4>CONDICIONES COMERCIALES(*)</h4>", 0, 1, 0, true, 'L', true); //'TB'->borde:0
  //
  //
  //            $pdf->Ln(1);
  //            $pdf->SetFillColor(255, 255, 255);
  //
  //            $cuentaNumero = $dataDocumento[0]['cuenta_numero'];
  //            $cuentaData = CuentaNegocio::create()->obtenerCuentaXId($dataDocumento[0]['cuenta_id']);
  //            $valorElaboradoPor = explode(" ", $dataDocumento[0]['perfil_usuario']);
  //            $valorElaboradoPor = $dataDocumento[0]['usuario'] . ' | ' . $valorElaboradoPor[0];
  //
  //            $dtdVigencia = date_format((date_create($dataDocumento[0]['fecha_vencimiento'])), 'd/m/Y');
  //
  //            $diasCredito = Util::diasTranscurridos($dataDocumento[0]['fecha_emision'], $dataDocumento[0]['fecha_vencimiento']);
  //
  //            $dtdFormaPago = $dataDocumento[0]['tipo_pago_descripcion'];
  //
  //            $formaPagoCompl = '';
  //            if ($dataDocumento[0]['tipo_pago'] == 2) {
  //                $formaPagoCompl = ' a ' . $diasCredito . ' días';
  //            }
  //
  //            $dtdFormaPagoDesc = $dtdFormaPago . $formaPagoCompl;
  //
  //            if ($dataDocumento[0]['moneda_id'] != 2) {
  //                $tabla = '<table cellspacing="0" cellpadding="1" border="1"  style="text-align:left">
  //                        <tr>
  //                          <td>' . $esp . 'Modo de entrega</td>
  //                          <td>' . $esp . $dtdModoEntrega . $esp . '</td>
  //                          <td>' . $esp . 'Fecha vencimiento</td>
  //                          <td>' . $esp . $dtdVigencia . $esp . '</td>
  //                          <td>' . $esp . 'Subtotal ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
  //                          <td style="text-align:rigth">' . $esp . number_format($dataDocumento[0]['subtotal'], 2) . $esp . '</td>
  //                        </tr>
  //                        <tr>
  //                          <td>' . $esp . 'Forma de pago</td>
  //                          <td>' . $esp . $dtdFormaPagoDesc . $esp . '</td>
  //                          <td>' . $esp . 'Marca</td>
  //                          <td>' . $esp . 'BH' . $esp . '</td>
  //                          <td>' . $esp . 'IGV 18% ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
  //                          <td style="text-align:rigth">' . $esp . number_format($dataDocumento[0]['igv'], 2) . $esp . '</td>
  //                        </tr>
  //                        <tr>
  //                          <td rowspan="2">' . $esp . 'Observaciones</td>
  //                          <td colspan="3">' . $esp . 'Cuenta: ' . ((!ObjectUtil::isEmpty($cuentaData)) ?
  //                                $cuentaData[0]['descripcion'] . ': ' . $cuentaData[0]['numero'] . '<br>' .
  //                                $esp . $esp . $esp . $esp . $esp . $esp . $esp . '&nbsp; CCI: ' . $cuentaData[0]['cci'] : '') .
  //                        '</td>
  //                          <td rowspan="2">' . $esp . 'TOTAL ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
  //                          <td rowspan="2" style="text-align:rigth">' . $esp . number_format($dataDocumento[0]['total'], 2) . $esp . '</td>
  //                        </tr>
  //                        <tr>
  //                          <td colspan="3">' . $esp . 'T/C: ' . number_format($dataDocumento[0]['cambio_personalizado'], 3) . $esp . ' (El T/C es referencial)</td>
  //                        </tr>
  //                        <tr>
  //                          <td colspan="4">' . $esp . '(*) Las operaciones comerciales al crédito estarán sujetas a evaluación y revisión del Area de Créditos y Cobranzas<br>
  //                                          (*) Disponibilidad: Disponibilidad sujeta a ventas previas
  //                          </td>
  //                          <td>' . $esp . 'TOTAL S/.</td>
  //                          <td style="text-align:rigth;">' . $esp . number_format($dataDocumento[0]['cambio_personalizado'] * $dataDocumento[0]['total'], 2) . $esp . '</td>
  //                        </tr>
  //                    </table>';
  //            } else {
  //                $tabla = '<table cellspacing="0" cellpadding="1" border="1"  style="text-align:left">
  //                        <tr>
  //                          <td>' . $esp . 'Modo de entrega</td>
  //                          <td>' . $esp . $dtdModoEntrega . $esp . '</td>
  //                          <td>' . $esp . 'Fecha vencimiento</td>
  //                          <td>' . $esp . $dtdVigencia . $esp . '</td>
  //                          <td>' . $esp . 'Subtotal ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
  //                          <td style="text-align:rigth">' . $esp . number_format($dataDocumento[0]['subtotal'], 2) . $esp . '</td>
  //                        </tr>
  //                        <tr>
  //                          <td>' . $esp . 'Forma de pago</td>
  //                          <td>' . $esp . $dtdFormaPago . $esp . '</td>
  //                          <td>' . $esp . 'Marca</td>
  //                          <td>' . $esp . 'BH' . $esp . '</td>
  //                          <td>' . $esp . 'IGV 18% ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
  //                          <td style="text-align:rigth">' . $esp . number_format($dataDocumento[0]['igv'], 2) . $esp . '</td>
  //                        </tr>
  //                        <tr>
  //                          <td>' . $esp . 'Observaciones</td>
  //                          <td colspan="3">' . $esp . 'Cuenta: ' . $cuentaNumero . $esp . '</td>
  //                          <td>' . $esp . 'TOTAL ' . $dataDocumento[0]['moneda_simbolo'] . '</td>
  //                          <td style="text-align:rigth">' . $esp . number_format($dataDocumento[0]['total'], 2) . $esp . '</td>
  //                        </tr>
  //                        <tr>
  //                          <td colspan="6">' . $esp . '(*) Las operaciones comerciales al crédito estarán sujetas a evaluación y revisión del Area de Créditos y Cobranzas<br>
  //                                          (*) Disponibilidad: Disponibilidad sujeta a ventas previas
  //                          </td>
  //                        </tr>
  //                    </table>';
  //            }
  //
  //            $pdf->writeHTML($tabla, true, false, false, false, 'C');
  //        }
  //
  ////            $pdf->Ln(5);
  ////            $pdf->Cell(0, 0,'Sin otro particular, agradeciendo su gentil atención, quedamos a la espera de vuestra pronta respuesta.', 0, 1, 'L', 0, '', 0);
  ////            $pdf->Ln(1);
  ////            $pdf->Cell(0, 0,'Atentamente.', 0, 1, 'L', 0, '', 0);
  ////
  ////            //telefonos
  ////            $pdf->Ln(20);
  ////            $borde=array('R' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
  //////            $pdf->MultiCell(145, 0, 'TELEFAX', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  //////            $pdf->MultiCell(3, 0, '', $borde, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  //////            $pdf->MultiCell(30, 0, '044 262811', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  //////            $pdf->Ln();
  ////            $pdf->MultiCell(145, 0, 'FIJO', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->MultiCell(3, 0, '', $borde, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->MultiCell(30, 0, '044 209454', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->Ln();
  ////            $pdf->MultiCell(145, 0, 'RPC', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->MultiCell(3, 0, '', $borde, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->MultiCell(30, 0, '977192256', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->Ln();
  ////            $pdf->MultiCell(145, 0, 'RPM', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->MultiCell(3, 0, '', $borde, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->MultiCell(30, 0, '*445213', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->Ln();
  ////            $pdf->MultiCell(145, 0, 'NEXTEL', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->MultiCell(3, 0, '', $borde, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->MultiCell(30, 0, '836*3196', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->Ln();
  ////            $pdf->MultiCell(145, 0, 'CELULAR', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->MultiCell(3, 0, '', $borde, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->MultiCell(30, 0, '965076817', 0, 'R', 1, 0, '', '', true, 0, false, true, 40, 'T');
  ////            $pdf->Ln();
  //        //agregar pagina
  ////        $pdf->AddPage();
  //        //Close and output PDF document
  //        ob_clean();
  //
  //        if ($tipoSalidaPDF == 'F') {
  //            $pdf->Output($url, $tipoSalidaPDF);
  //        }
  //
  //        return $titulo;
  //    }

  public function generarDocumentoPDFCotizacion($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];
    $dataDocumentoAdjunto = DocumentoNegocio::create()->obtenerDocumentoAdjuntoXDocumentoId($documentoId);
    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;

    $banderaClienteCompartamos = false;
    if ($dataDocumento[0]['codigo_identificacion'] == "20369155360") {
      $banderaClienteCompartamos = true;
    }


    // create new PDF document
    $pdf = new TCPDF('p', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); //'p' is vertical and it is the default and 'l' is the horizonal.
    // set document information
    $pdf->SetCreator('Minapp S.A.C.');
    $pdf->SetAuthor('Minapp S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    $cabeceraPDF1 = $dataEmpresa[0]['razon_social']
      //                ."\nSoluciones Integrales de Perforación"
    ;

    $cabeceraPDF2 = $dataEmpresa[0]['direccion'] . "\n" .
      "Tel: (044) 728609 - Anexo 108 / Cel: 979 754 211\n" .
      "E-mail: lnacarino@abcservicios.pe; Web site: www.abcservicios.pe\n" .
      "RUC: " . $dataEmpresa[0]['ruc'];
    $PDF_HEADER_LOGO_WIDTH = 40;
    $pdf->SetHeaderData('logo.PNG', $PDF_HEADER_LOGO_WIDTH, $cabeceraPDF1, $cabeceraPDF2, array(0, 0, 0), array(0, 0, 0));
    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    // set font
    $pdf->SetFont('helvetica', '', 9);

    // add a page
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . "-";
    }

    $dtdSegun = "";
    $dtdGuia = "";
    $dtdProyecto = "";
    foreach ($documentoDatoValor as $indice => $item) {
      $txtDescripcion = $item['descripcion'];
      $valorItem = $item['valor'];

      if ($item['tipo'] == 1) {
        $valorItem = number_format($valorItem, 2, ".", ",");
      }

      if ($item['tipo'] == 3) {
        $valorItem = date_format((date_create($valorItem)), 'd/m/Y');
      }

      switch ((int) $item['documento_tipo_id']) {
        case 1850:
          $dtdModoEntrega = $valorItem;
          break;
        case 2945:
          $dtdSegun = $valorItem;
          break;
        case 2980:
          $dtdGuia = $valorItem;
          break;
        case 2880:
          $dtdProyecto = $valorItem;
          break;
        case 2616:
          $dtdTiempoEntrega = $valorItem;
          break;
        case 2617:
          $dtdNuestraRef = $valorItem;
          break;
        case 3040:
          $dtdResponsable = $valorItem;
          break;
        case 3041:
          $dtdContrato = $valorItem;
          break;
        case 3042:
          $dtdAtencion = $valorItem;
          break;
      }
    };

    if (!ObjectUtil::isEmpty($dataDocumentoAdjunto)) {
      $dataPartidaJson = json_decode($dataDocumentoAdjunto[0]['contenido_archivo']);
    }

    $serieDocumento .= $dataDocumento[0]['numero'];
    /*$dataUltimaVersion = Movimiento::create()->obtenerDocumentoHistoricoUltimoXDocumentoId($documentoId);
    if (!ObjectUtil::isEmpty($dataUltimaVersion)) {
      $serieDocumento .= "-" . substr($dataUltimaVersion[0]['codigo_version'], 1);
    }*/

    $titulo = "cotizacion1_" . $serieDocumento . "-" . $dtdProyecto;
    $titulo2 = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " N° " . $serieDocumento;

    $dataPersona = DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId, 5);
    $descripcionPersona = $dataPersona[0]['descripcion'];
    $pdf->Ln(20);
    $pdf->Ln(-20);
    $fecha = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y');
    $pdf->writeHTMLCell(0, 0, '', '', "Fecha: " . $fecha . "", 0, 1, 0, true, 'R', true);
    $pdf->Ln(10);

    $pdf->MultiCell(120, 0, $descripcionPersona . ": " . $dataDocumento[0]['nombre'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
    $pdf->MultiCell(60, 0, $dataDocumento[0]['persona_documento_tipo'] . ": " . $dataDocumento[0]['codigo_identificacion'], 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');

    $pdf->Ln(4);
    $pdf->Cell(0, 0, "Dirección: " . $dataDocumento[0]['direccion'], 0, 1, 'L', 0, '', 0);

    $banderaSaltoLinea = false;
    if (!ObjectUtil::isEmpty($dtdSegun)) {
      $pdf->MultiCell(120, 0, "Según: " . $dtdSegun, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
      $banderaSaltoLinea = true;
    }
    //        if (!ObjectUtil::isEmpty($dtdGuia)) {
    //            $pdf->MultiCell(60, 0, "Guía de remisión: " . $dtdGuia, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
    //            $banderaSaltoLinea = true;
    //        }
    if ($banderaSaltoLinea) {
      $pdf->Ln();
    }
    $pdf->MultiCell(16, 0, "Proyecto:", 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->MultiCell(170, 0, $dtdProyecto, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
    $pdf->Ln();
    $pdf->SetFont('helvetica', '', 9);
    if (!ObjectUtil::isEmpty($dtdResponsable)) {
      $pdf->MultiCell(30, 0, "Responsable ticket:", 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
      $pdf->MultiCell(170, 0, $dtdResponsable, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
      $pdf->Ln();
    }
    if (!ObjectUtil::isEmpty($dtdContrato)) {
      $pdf->MultiCell(16, 0, "Contrato:", 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
      $pdf->MultiCell(170, 0, $dtdContrato, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
      $pdf->Ln();
    }
    if (!ObjectUtil::isEmpty($dtdAtencion)) {
      $pdf->MultiCell(18, 0, "Atención a:", 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
      $pdf->MultiCell(170, 0, $dtdAtencion, 0, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
      $pdf->Ln();
    }

    $agencias = implode(",", array_unique(array_map(function ($item) {
      return $item->agenciaDescripcion;
    }, $detalle)));

    if (!ObjectUtil::isEmpty($agencias)) {
      $pdf->Cell(0, 0, "Agencia: " . $agencias, 0, 1, 'L', 0, '', 0);
    }

    $pdf->Ln(4);
    $pdf->writeHTMLCell(0, 0, '', '', "<h3>" . $titulo2 . "</h3>", 0, 1, 0, true, 'C', true);

    $pdf->Ln(5);
    $pdf->Cell(0, 0, 'Estimados señores:', 0, 1, 'L', 0, '', 0);
    $pdf->Ln(1);
    $pdf->Cell(0, 0, 'De acuerdo a vuestro requerimiento nos es grato presentarles nuestra cotización de los siguientes Productos y/o Servicios:', 0, 1, 'L', 0, '', 0);

    //espacio
    $pdf->Ln(5);

    //detalle
    $esp = '&nbsp;&nbsp;'; //espacio en blanco
    $styleLetra = "";

    //Si existe partida.

    if (!ObjectUtil::isEmpty($dataPartidaJson)) {
      $esp = "";
      $tabla = '<table cellspacing="1" cellpadding="2" border="0">
                <tr>
                    <th style="text-align:center;border-bottom: 1px solid #000" width="17%"><b>Item</b></th>
                    <th style="text-align:center;border-bottom: 1px solid #000" width="46%"><b>Descripción</b></th>
                    <th style="text-align:center;border-bottom: 1px solid #000" width="8%"><b>Unidad</b></th>
                    <th style="text-align:center;border-bottom: 1px solid #000" width="9%"><b>Metrado</b></th>
                    <th style="text-align:center;border-bottom: 1px solid #000" width="10%"><b>Precio</b></th>
                    <th style="text-align:center;border-bottom: 1px solid #000" width="10%"><b>Parcial</b></th>
                </tr>';

      foreach ($dataPartidaJson->partidas as $index => $item) {
        $unidadMedida = $item->unidad_medida;
        $metrado = (!ObjectUtil::isEmpty($item->metrado) ? number_format($item->metrado, 2) : "");
        $precio = (!ObjectUtil::isEmpty($item->precio) ? number_format($item->precio, 2) : "");
        $parcial = (!ObjectUtil::isEmpty($item->parcial) ? number_format($item->parcial, 2) : "");
        $bienDescripcion = htmlspecialchars($item->descripcion);

        $styleLetra = ($item->es_padre == 1) ? ";font-weight:bold" : "";
        $tabla = $tabla . '<tr>'
          . '<td style="text-align:left' . $styleLetra . '"  width="17%">' . $esp . $item->codigo . $esp . '</td>'
          . '<td style="text-align:left' . $styleLetra . '"  width="46%">' . $esp . $bienDescripcion . $esp . '</td>'
          . '<td style="text-align:center' . $styleLetra . '"  width="8%">' . $esp . $unidadMedida . $esp . '</td>'
          . '<td style="text-align:right' . $styleLetra . '"  width="9%">' . $esp . $metrado . $esp . '</td>'
          . '<td style="text-align:right' . $styleLetra . '"  width="10%">' . $esp . $precio . $esp . '</td>'
          . '<td style="text-align:right' . $styleLetra . '"  width="10%">' . $esp . $parcial . $esp . '</td>'
          . '</tr>';
      }

      if (!ObjectUtil::isEmpty($dataPartidaJson->totalizados->costo_directo)) {
        $item = $dataPartidaJson->totalizados->costo_directo;
        $styleLetra = ";font-weight:bold";
        $tabla = $tabla . '<tr>'
          . '<td style="text-align:left' . $styleLetra . '"  width="17%"></td>'
          . '<td style="text-align:left' . $styleLetra . ';border-top: 1px solid #000"  width="73%" colspan = "4">' . $esp . $item->nombre . $esp . '</td>'
          . '<td style="text-align:right' . $styleLetra . ';border-top: 1px solid #000"  width="10%">' . $esp . number_format($item->monto, 2) . $esp . '</td>'
          . '</tr>';
      }

      if (!ObjectUtil::isEmpty($dataPartidaJson->totalizados->adicionales)) {
        foreach ($dataPartidaJson->totalizados->adicionales as $index => $item) {
          $styleLetra = ";font-weight:bold";
          $tabla = $tabla . '<tr>'
            . '<td style="text-align:left' . $styleLetra . '"  width="17%"></td>'
            . '<td style="text-align:left' . $styleLetra . '"  width="73%" colspan = "4">' . $esp . $item->nombre . $esp . '</td>'
            . '<td style="text-align:right' . $styleLetra . '"  width="10%">' . $esp . number_format($item->monto, 2) . $esp . '</td>'
            . '</tr>';
        }
      }

      if (!ObjectUtil::isEmpty($dataPartidaJson->totalizados->subtotal)) {
        $item = $dataPartidaJson->totalizados->subtotal;
        $styleLetra = ";font-weight:bold";
        $tabla = $tabla . '<tr>'
          . '<td style="text-align:left' . $styleLetra . '"  width="17%"></td>'
          . '<td style="text-align:left' . $styleLetra . ';border-top: 1px solid #000"  width="73%" colspan = "4">' . $esp . $item->nombre . '&nbsp;&nbsp;' . $dataDocumento[0]['moneda_simbolo'] . '</td>'
          . '<td style="text-align:right' . $styleLetra . ';border-top: 1px solid #000"  width="10%">' . $esp . number_format($item->monto, 2) . $esp . '</td>'
          . '</tr>';
      }

      if (!ObjectUtil::isEmpty($dataPartidaJson->totalizados->igv)) {
        $item = $dataPartidaJson->totalizados->igv;
        $styleLetra = ";font-weight:bold";
        $tabla = $tabla . '<tr>'
          . '<td style="text-align:left' . $styleLetra . '"  width="17%"></td>'
          . '<td style="text-align:left' . $styleLetra . '"  width="73%" colspan = "4">' . $esp . $item->nombre . '&nbsp;&nbsp;' . $dataDocumento[0]['moneda_simbolo'] . '</td>'
          . '<td style="text-align:right' . $styleLetra . '"  width="10%">' . $esp . number_format($item->monto, 2) . $esp . '</td>'
          . '</tr>';
      }

      if (!ObjectUtil::isEmpty($dataPartidaJson->totalizados->total)) {
        $item = $dataPartidaJson->totalizados->total;
        $styleLetra = ";font-weight:bold";
        $tabla = $tabla . '<tr>'
          . '<td style="text-align:left' . $styleLetra . '"  width="17%"></td>'
          . '<td style="text-align:left' . $styleLetra . ';border-top: 1px solid #000"  width="73%" colspan = "4">' . $esp . $item->nombre . '&nbsp;&nbsp;' . $dataDocumento[0]['moneda_simbolo'] . '</td>'
          . '<td style="text-align:right' . $styleLetra . ';border-top: 1px solid #000"  width="10%">' . $esp . number_format($item->monto, 2) . $esp . '</td>'
          . '</tr>';
      }


      $tabla = $tabla . '</table>';
      $pdf->writeHTML($tabla, true, false, false, false, 'C');
    } else {
      if ($banderaClienteCompartamos) {
        $styleLetra = ";font-size:9px";
        $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                <tr>
                    <th style="text-align:center' . $styleLetra . '" width="5%"><b>Item</b></th>
                    <th style="text-align:center' . $styleLetra . '" width="12%"><b>Articulo</b></th>
                    <th style="text-align:center' . $styleLetra . '" width="18%"><b>Descripción</b></th>
                    <th style="text-align:center' . $styleLetra . '" width="18%"><b>Categoría </b></th>
                    <th style="text-align:center' . $styleLetra . '" width="12%"><b>Cuenta contable</b></th>
                    <th style="text-align:center' . $styleLetra . '" width="6%"><b>Unidad</b></th>
                    <th style="text-align:center' . $styleLetra . '" width="9%"><b>Cantidad</b></th>
                    <th style="text-align:center' . $styleLetra . '" width="10%"><b>P. Unit.</b></th>
                    <th style="text-align:center' . $styleLetra . '" width="10%"><b>Sub Total</b></th>
                </tr>';
      } else {
        $tabla = '<table cellspacing="0" cellpadding="1" border="1">
                <tr>
                    <th style="text-align:center;" width="5%"><b>Item</b></th>
                    <th style="text-align:center;" width="12%"><b>Código</b></th>
                    <th style="text-align:center;" width="46%"><b>Descripción</b></th>
                    <th style="text-align:center;" width="8%"><b>Unidad</b></th>
                    <th style="text-align:center;" width="9%"><b>Cantidad</b></th>
                    <th style="text-align:center;" width="10%"><b>P. Unit.</b></th>
                    <th style="text-align:center;" width="10%"><b>Sub Total</b></th>
                </tr>';
      }

      $cantidadColumnas = 6;
      foreach ($detalle as $index => $item) {
        $dataStock = BienNegocio::create()->obtenerStockTotalXBienIDXUnidadMedidaId($item->bienId, $item->unidadMedidaId);
        $stock = $dataStock[0]['stock'];
        $disponible = $stock;
        if ($stock >= $item->cantidad) {
          $disponible = $item->cantidad;
        }
        $linea = $index + 1;
        if ($linea < 10) {
          $linea = str_pad($linea, 2, "0", STR_PAD_LEFT);
        }
        $cantidad = round($item->cantidad, 2);
        if ($cantidad < 10) {
          $cantidad = str_pad($cantidad, 2, "0", STR_PAD_LEFT);
        }

        $bienComentario = $item->movimientoBienComentario;
        $bienComentario = str_replace('<div>', '<div>' . $esp, $bienComentario);
        $bienComentario = str_replace('<br>', '<br>' . $esp, $bienComentario);
        $bienComentario = str_replace('<div>', '<div style="line-height:100%;">', $bienComentario);

        $bienDescripcion = htmlspecialchars($item->descripcion);
        if (!ObjectUtil::isEmpty($item->ticket)) {
          $bienComentario .= "&nbsp;<b>Ticket</b>:&nbsp;&nbsp;" . $item->ticket . "<br>";
        }
        if (!ObjectUtil::isEmpty($item->agenciaDescripcion)) {
          $bienComentario .= "&nbsp;<b>Agencia</b>:&nbsp;&nbsp;" . $item->agenciaDescripcion;
        }


        $tabla = $tabla . '<tr>'
          . '<td style="text-align:center' . $styleLetra . '"  width="5%">' . $esp . $linea . $esp . '</td>'
          . '<td style="text-align:center' . $styleLetra . '"  width="12%">' . $esp . $item->bien_codigo . $esp . '</td>';
        if ($banderaClienteCompartamos) {
          $tabla = $tabla . '<td style="text-align:left' . $styleLetra . '"  width="18%">' . $esp . $bienDescripcion . $esp . '<br>'
            . $esp . $bienComentario . $esp . '</td>'
            . '<td style="text-align:left' . $styleLetra . '"  width="18%">' . $esp . $item->bienTipoDescripcion . $esp . '</td>'
            . '<td style="text-align:left' . $styleLetra . '"  width="12%">' . $esp . $item->codigoContable . $esp . '</td>'
            . '<td style="text-align:center' . $styleLetra . '"  width="6%">' . $esp . $item->simbolo . $esp . '</td>';
          //              $cantidadColumnas += 2;
        } else {
          $tabla = $tabla . '<td style="text-align:left' . $styleLetra . '"  width="46%">' . $esp . $item->descripcion . $esp . '<br>'
            . $esp . $bienComentario . $esp . '</td>'
            . '<td style="text-align:center' . $styleLetra . '"  width="8%">' . $esp . $item->simbolo . $esp . '</td>';
        }


        $tabla = $tabla . '<td style="text-align:rigth' . $styleLetra . '"  width="9%">' . $esp . $cantidad . $esp . '</td>'
          . '<td style="text-align:rigth' . $styleLetra . '"  width="10%">' . $esp . number_format($item->precioUnitario, 2) . $esp . '</td>'
          . '<td style="text-align:rigth' . $styleLetra . '"  width="10%">' . $esp . number_format($item->importe, 2) . $esp . '</td>'
          . '</tr>';
      };

      if ($banderaClienteCompartamos) {
        $cantidadColumnas += 2;
      }
      //fin tabla detalle
      //montos
      $tabla = $tabla . '<tr>'
        . '<td colspan="' . $cantidadColumnas . '" style="text-align:rigth"><b>' . $esp . 'Subtotal ' . $dataDocumento[0]['moneda_simbolo'] . '</b></td>'
        . '<td style="text-align:rigth">' . $esp . number_format($dataDocumento[0]['subtotal'], 2) . $esp . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td colspan="' . $cantidadColumnas . '" style="text-align:rigth"><b>' . $esp . 'IGV 18% ' . $dataDocumento[0]['moneda_simbolo'] . '</b></td>'
        . '<td style="text-align:rigth">' . $esp . number_format($dataDocumento[0]['igv'], 2) . $esp . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td colspan="' . $cantidadColumnas . '" style="text-align:rigth"><b>' . $esp . 'TOTAL ' . $dataDocumento[0]['moneda_simbolo'] . '</b></td>'
        . '<td style="text-align:rigth">' . $esp . number_format($dataDocumento[0]['total'], 2) . $esp . '</td>'
        . '</tr>';

      if ($dataDocumento[0]['moneda_id'] != 2) {
        $tabla = $tabla . '<tr>'
          . '<td colspan="6" style="text-align:rigth"><b>' . $esp . 'TOTAL S/.</b></td>'
          . '<td style="text-align:rigth">' . $esp . number_format($dataDocumento[0]['cambio_personalizado'] * $dataDocumento[0]['total'], 2) . $esp . '</td>'
          . '</tr>';
      }
      //fin montos
      $tabla = $tabla . '</table>';
      $pdf->writeHTML($tabla, true, false, false, false, 'C');
    }

    if (!ObjectUtil::isEmpty($documentoDatoValor)) {
      //            $pdf->Ln(5);
      //        $borde=array('LTRB' => array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
      $pdf->Ln(10);
      if (!ObjectUtil::isEmpty($data->dataDocumento[0]['comentario'])) {
        $pdf->writeHTMLCell(0, 6, '', '', "<h4>COMENTARIO</h4>", 0, 1, 0, true, 'L', true); //'TB'->borde:0
        $pdf->Cell(0, 0, $data->dataDocumento[0]['comentario'], 0, 1, 'L', 0, '', 0);
        $pdf->Ln(5);
      }

      $pdf->writeHTMLCell(0, 6, '', '', "<h4>CONDICIONES COMERCIALES(*)</h4>", 0, 1, 0, true, 'L', true); //'TB'->borde:0

      $pdf->Ln(1);
      $pdf->SetFillColor(255, 255, 255);

      $cuentaNumero = $dataDocumento[0]['cuenta_numero'];
      $cuentaData = CuentaNegocio::create()->obtenerCuentaXId($dataDocumento[0]['cuenta_id']);
      $valorElaboradoPor = explode(" ", $dataDocumento[0]['perfil_usuario']);
      $valorElaboradoPor = $dataDocumento[0]['usuario'] . ' | ' . $valorElaboradoPor[0];

      $dtdVigencia = date_format((date_create($dataDocumento[0]['fecha_vencimiento'])), 'd/m/Y');

      $diasCredito = Util::diasTranscurridos($dataDocumento[0]['fecha_emision'], $dataDocumento[0]['fecha_vencimiento']);

      $dtdFormaPago = $dataDocumento[0]['tipo_pago_descripcion'];

      $formaPagoCompl = '';
      if ($dataDocumento[0]['tipo_pago'] == 2) {
        $formaPagoCompl = ' a ' . $diasCredito . ' días';
      }

      $dtdFormaPagoDesc = $dtdFormaPago . $formaPagoCompl;

      if ($dataDocumento[0]['moneda_id'] != 2) {
        $tabla = '<table cellspacing="0" cellpadding="1" border="1"  style="text-align:left">
                        <tr>
                            <td width="20%">' . $esp . 'Modo de entrega</td>
                            <td width="35%">' . $esp . $dtdModoEntrega . $esp . '</td>
                            <td width="15%">' . $esp . 'Validez de oferta</td>
                            <td width="30%">' . $esp . $dtdVigencia . $esp . '</td>
                        </tr>
                        <tr>
                            <td rowspan="2" width="20%">' . $esp . 'Forma de pago</td>
                            <td rowspan="2" width="35%">' . $esp . $dtdFormaPagoDesc . $esp . '</td>
                            <td width="15%">' . $esp . 'Moneda</td>
                            <td width="30%">' . $esp . $dataDocumento[0]['moneda_descripcion'] . $esp . '</td>
                        </tr>
                        <tr>
                            <td width="15%">' . $esp . 'T/C (referencial)</td>
                            <td width="30%">' . $esp . number_format($dataDocumento[0]['cambio_personalizado'], 3) . '</td>
                        </tr>
                        <tr>
                            <td width="20%">' . $esp . 'Tiempo de entrega</td>
                            <td width="35%">' . $esp . $dtdTiempoEntrega . '</td>
                            <td width="15%">' . $esp . 'Cuenta</td>
                            <td width="30%">' . $esp . ((!ObjectUtil::isEmpty($cuentaData)) ?
          $cuentaData[0]['descripcion'] . ': ' . $esp . $cuentaData[0]['numero'] . '<br>' .
          $esp . 'CCI: ' . $cuentaData[0]['cci'] : '') .
          '</td>
                        </tr>
                        <tr>
                            <td colspan="4">' . $esp . '(*) Las operaciones comerciales al crédito estarán sujetas a evaluación y revisión del Area de Créditos y Cobranzas
                            </td>
                        </tr>
                    </table>';
      } else {
        $tabla = '<table cellspacing="0" cellpadding="1" border="1"  style="text-align:left">
                        <tr>
                            <td width="20%">' . $esp . 'Modo de entrega</td>
                            <td width="35%">' . $esp . $dtdModoEntrega . $esp . '</td>
                            <td width="15%">' . $esp . 'Validez de oferta</td>
                            <td width="30%">' . $esp . $dtdVigencia . $esp . '</td>
                        </tr>
                        <tr>
                            <td width="20%">' . $esp . 'Forma de pago</td>
                            <td width="35%">' . $esp . $dtdFormaPago . $esp . '</td>
                            <td width="15%">' . $esp . 'Moneda</td>
                            <td width="30%">' . $esp . $dataDocumento[0]['moneda_descripcion'] . $esp . '</td>
                        </tr>
                        <tr>
                            <td width="20%">' . $esp . 'Tiempo de entrega</td>
                            <td width="35%">' . $esp . $dtdTiempoEntrega . '</td>
                            <td width="15%">' . $esp . 'Cuenta</td>
                            <td width="30%">' . $esp . ((!ObjectUtil::isEmpty($cuentaData)) ?
          $cuentaData[0]['descripcion'] . ': ' . $cuentaData[0]['numero'] . '<br>' .
          $esp . 'CCI: ' . $cuentaData[0]['cci'] : '') . '</td>
                        </tr>
                        <tr>
                            <td colspan="6">' . $esp . '(*) Las operaciones comerciales al crédito estarán sujetas a evaluación y revisión del Area de Créditos y Cobranzas
                            </td>
                        </tr>
                    </table>';
      }

      $pdf->writeHTML($tabla, true, false, false, false, 'C');
    }
    $x = 150;
    $y = $pdf->GetY() + 10;  // Obtén la posición actual de Y y agrega un espacio
    $width = 40;
    $height = 40;
    $imageFile = Configuraciones::url_base() . 'vistas/images/' . 'firma_jefe_servicios.png';
    $pdf->Image($imageFile, $x, $y, $width, $height);
    $pdf->Ln(45);
    $pdf->writeHTMLCell(0, 6, '', '', "<h4>Atentamente,</h4>", 0, 1, 0, true, 'L', true); //'TB'->borde:0
    $pdf->Ln(1);
    $pdf->writeHTMLCell(0, 6, '', '', "<h4>ABC MULTISERVICIOS</h4>", 0, 1, 0, true, 'L', true); //'TB'->borde:0

    //agregar pagina
    //        $pdf->AddPage();
    //Close and output PDF document
    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  public function enviarCorreoConPrecio($correo, $documentoId, $comentarioDocumento, $usuarioId, $plantillaId)
  {

    $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID($plantillaId); //antes 3

    $documento = Documento::create()->obtenerDocumentoDatos($documentoId);

    $data = MovimientoNegocio::create()->visualizarDocumento($documentoId, $documento[0]['movimiento_id']);

    //dibujar cuerpo y detalle

    $nombreDocumentoTipo = '';
    $dataDocumento = '';

    // datos de documento
    if (!ObjectUtil::isEmpty($data->dataDocumento)) {

      $nombreDocumentoTipo = $data->dataDocumento[0]['nombre_documento'];

      // Mostraremos la data en filas de dos columnas
      foreach ($data->dataDocumento as $index => $item) {
        $html = '<tr><td style=\'text-align:left;padding:0 55px 5px;font-size:14px;line-height:1.5;width:80%\'><b>' . $item['descripcion'] . ': </b>';

        $valor = $item['valor'];

        if (!ObjectUtil::isEmpty($valor)) {
          switch ((int) $item['tipo']) {
            case 3:
            case 9:
            case 10:
            case 11:
              $time = strtotime($valor);
              $valor = date('d/m/Y', $time);
              break;
            case 1:
            case 14:
            case 15:
            case 16:
            case 19:
              $valor = number_format($valor, 2, ".", ",");
              break;
          }
        }

        $html = $html . $valor;

        $html = $html . '</td></tr>';
        $dataDocumento = $dataDocumento . $html;
      }
    }

    // detalle de documento
    //obtener configuracion de las columnas de movimiento_tipo
    $res = MovimientoTipoNegocio::create()->obtenerXDocumentoId($documentoId);
    if (!ObjectUtil::isEmpty($res)) {
      $movimientoTipoId = $res[0]['movimiento_tipo_id'];
      $dataMovimientoTipoColumna = MovimientoTipoNegocio::create()->obtenerMovimientoTipoColumna($movimientoTipoId);
    }

    //dibujando la cabecera
    $dataDetalle = '<table  border=1 cellspacing=0 cellpadding=5 bordercolor="000000"  width="80%">
                        <thead>';

    $html = '<tr>';
    if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 15)) { //Organizador
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Organizador</th>";
    }
    $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Cantidad</th>";
    $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Unidad</th>";
    $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Descripcion</th>";

    if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 5)) { //Precio unitario
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>PU</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Total</th>";
    }
    $html = $html . '</tr>';

    $dataDetalle = $dataDetalle . $html;
    $dataDetalle = $dataDetalle . '<thead>';
    $dataDetalle = $dataDetalle . '<tbody>';

    if (!ObjectUtil::isEmpty($data->detalleDocumento)) {
      foreach ($data->detalleDocumento as $index => $item) {

        $html = '<tr>';
        if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 15)) { //Organizador
          $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item->organizador;
        }
        $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . number_format($item->cantidad, 2, ".", ",");
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item->unidadMedida;
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item->descripcion;

        if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 5)) { //Precio unitario
          $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . number_format($item->precioUnitario, 2, ".", ",");
          $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . number_format($item->importe, 2, ".", ",");
        }
        $html = $html . '</tr>';

        $dataDetalle = $dataDetalle . $html;
      }
    }
    $dataDetalle = $dataDetalle . '</tbody></table>';

    $direccionEmpresa = '<tr><td style="text-align: left; padding: 0 55px 10px; font-size: 14px; line-height: 1.5; width: 80%">Documento generado en la empresa '
      . $data->direccionEmpresa[0]['razon_social']
      . ' ubicada en '
      . $data->direccionEmpresa[0]['direccion']
      . '</td></tr>';

    //fin dibujo
    //envio correo
    $usuarioId;
    $correo;
    $nombreDocumentoTipo;
    $dataDocumento;
    $dataDetalle;
    $comentarioDocumento;
    $direccionEmpresa;


    //logica correo:
    //        $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID(3);

    $asunto = $plantilla[0]["asunto"];
    $cuerpo = $plantilla[0]["cuerpo"];

    $asunto = str_replace("[|documento_tipo|]", $nombreDocumentoTipo, $asunto);
    $cuerpo = str_replace("[|documento_tipo|]", $nombreDocumentoTipo, $cuerpo);
    $cuerpo = str_replace("[|dato_documento|]", $dataDocumento, $cuerpo);
    $cuerpo = str_replace("[|detalle_documento|]", $dataDetalle, $cuerpo);
    $cuerpo = str_replace("[|comentario_documento|]", $comentarioDocumento, $cuerpo);
    $cuerpo = str_replace("[|direccion_empresa|]", $direccionEmpresa, $cuerpo);

    $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correo, $asunto, $cuerpo, 1, $usuarioId);

    $this->setMensajeEmergente("Se enviará el correo aproximadamente en un minuto");
    return 1;
  }

  public function enviarMovimientoEmailCorreoMasPDF($correo, $documentoId, $comentario, $usuarioId, $plantillaId)
  {

    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];
    $data = MovimientoNegocio::create()->imprimir($documentoId, $documentoTipoId);
    $dataDocumento = $data->dataDocumento;

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }

    $dataPersona = DocumentoTipoDatoNegocio::create()->obtenerDocumentoTipoDatoXDocumentoIdXTipo($documentoId, 5);
    $descripcionPersona = $dataPersona[0]['descripcion'];
    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];

    $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID($plantillaId);

    $hoy = date("Y_m_d_H_i_s");
    $url = __DIR__ . '/../../vistas/com/movimiento/documentos/documento_' . $hoy . '_' . $usuarioId . '.pdf';

    //crear PDF
    $this->generarDocumentoPDF($documentoId, $comentario, 'F', $url, $data);

    //---------------------GENERACION DE LA PARTE TEXTUAL DEL CORREO-------------------------------------------

    $documento = Documento::create()->obtenerDocumentoDatos($documentoId);
    $data = MovimientoNegocio::create()->visualizarDocumento($documentoId, $documento[0]['movimiento_id']);

    //dibujar cuerpo y detalle

    $nombreDocumentoTipo = '';
    $dataDocumento = '';

    // datos de documento
    if (!ObjectUtil::isEmpty($data->dataDocumento)) {

      $nombreDocumentoTipo = $data->dataDocumento[0]['nombre_documento'];
      $monedaDescripcionHTML = '<tr><td style=\'text-align:left;padding:0 55px 5px;font-size:14px;line-height:1.5;width:80%\'><b>Moneda: </b>' . $data->dataDocumento[0]['moneda_descripcion'] . '</td></tr>';

      // Mostraremos la data en filas de dos columnas
      foreach ($data->dataDocumento as $index => $item) {
        $html = '<tr><td style=\'text-align:left;padding:0 55px 5px;font-size:14px;line-height:1.5;width:80%\'><b>' . $item['descripcion'] . ': </b>';

        $valor = $item['valor'];

        if (!ObjectUtil::isEmpty($valor)) {
          switch ((int) $item['tipo']) {
            case 3:
            case 9:
            case 10:
            case 11:
              $time = strtotime($valor);
              $valor = date('d/m/Y', $time);
              break;
            case 1:
            case 14:
            case 15:
            case 16:
            case 19:
              $valor = number_format($valor, 2, ".", ",");
              break;
          }
        }

        $html = $html . $valor;

        $html = $html . '</td></tr>';
        $dataDocumento = $dataDocumento . $html;
      }
      $dataDocumento = $dataDocumento . $monedaDescripcionHTML;
    }

    // detalle de documento
    //obtener configuracion de las columnas de movimiento_tipo
    $res = MovimientoTipoNegocio::create()->obtenerXDocumentoId($documentoId);
    if (!ObjectUtil::isEmpty($res)) {
      $movimientoTipoId = $res[0]['movimiento_tipo_id'];
      $dataMovimientoTipoColumna = MovimientoTipoNegocio::create()->obtenerMovimientoTipoColumna($movimientoTipoId);
    }

    //dibujando la cabecera
    $dataDetalle = '<table  border=1 cellspacing=0 cellpadding=5 bordercolor="000000"  width="80%">
                        <thead>';

    $html = '<tr>';
    if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 15)) { //Organizador
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Organizador</th>";
    }
    $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Cantidad</th>";
    $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Unidad</th>";
    $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Descripcion</th>";

    if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 5)) { //Precio unitario
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>PU</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Total</th>";
    }
    $html = $html . '</tr>';

    $dataDetalle = $dataDetalle . $html;
    $dataDetalle = $dataDetalle . '<thead>';
    $dataDetalle = $dataDetalle . '<tbody>';

    if (!ObjectUtil::isEmpty($data->detalleDocumento)) {
      foreach ($data->detalleDocumento as $index => $item) {

        $html = '<tr>';
        if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 15)) { //Organizador
          $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item->organizador;
        }
        $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . number_format($item->cantidad, 2, ".", ",");
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item->unidadMedida;
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item->descripcion;

        if ($this->existeColumnaCodigo($dataMovimientoTipoColumna, 5)) { //Precio unitario
          $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . number_format($item->precioUnitario, 2, ".", ",");
          $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . number_format($item->importe, 2, ".", ",");
        }
        $html = $html . '</tr>';

        $dataDetalle = $dataDetalle . $html;
      }
    }
    $dataDetalle = $dataDetalle . '</tbody></table>';
    $direccionEmpresa = '';

    //fin dibujo
    //logica correo:
    $asunto = $plantilla[0]["asunto"];
    $cuerpo = $plantilla[0]["cuerpo"];

    $asunto = str_replace("[|documento_tipo|]", $nombreDocumentoTipo, $asunto);
    $cuerpo = str_replace("[|documento_tipo|]", $nombreDocumentoTipo, $cuerpo);
    $cuerpo = str_replace("[|dato_documento|]", $dataDocumento, $cuerpo);
    $cuerpo = str_replace("[|detalle_documento|]", $dataDetalle, $cuerpo);
    $cuerpo = str_replace("[|comentario_documento|]", $comentario, $cuerpo);
    $cuerpo = str_replace("[|direccion_empresa|]", $direccionEmpresa, $cuerpo);

    //-----------------------------------------------------------------
    //envio de email
    $nombreArchivo = $dataDocumentoTipo[0]['descripcion'] . ".pdf";

    $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correo, $asunto, $cuerpo, 1, $usuarioId, $url, $nombreArchivo);

    if (!ObjectUtil::isEmpty($res[0]['id'])) {
      $this->setMensajeEmergente("Se enviará el correo aproximadamente en un minuto");
    }

    return $res;
  }

  public function obtenerMovimientoTipoColumnaLista($opcionId)
  {
    $movimientoTipoId = $this->obtenerIdXOpcion($opcionId);
    return Movimiento::create()->obtenerMovimientoTipoColumnaListaXMovimientoTipoId($movimientoTipoId);
  }

  public function enviarCorreosMovimiento($usuarioId, $txtCorreo, $correosSeleccionados, $respuestaCorreo, $comentario)
  {
    $plantilla = $respuestaCorreo['dataPlantilla'];
    $accionEnvio = $plantilla[0]['accion_funcion'];
    $documentoId = $respuestaCorreo['documentoId'];

    $correos = '';
    if (!ObjectUtil::isEmpty($correosSeleccionados)) {
      foreach ($correosSeleccionados as $email) {
        $correos = $correos . $email . ';';
      }
    }
    if (!ObjectUtil::isEmpty($txtCorreo)) {
      $correos = $correos . $txtCorreo;
    }

    $plantillaId = $plantilla[0]["email_plantilla_id"];
    $this->enviarCorreoXAccion($correos, $comentario, $accionEnvio, $documentoId, $plantillaId, $usuarioId);
  }

  public function obtenerEmailsXAccion($opcionId, $accionEnvio, $documentoId)
  {
    //obtener email de plantilla
    $movimientoTipoId = $this->obtenerIdXOpcion($opcionId);
    $plantilla = EmailPlantillaNegocio::create()->obtenerPlantillaDestinatarioXAccionDescripcionXMovimientoTipoId($accionEnvio, $movimientoTipoId);
    $dataPersona = DocumentoNegocio::create()->obtenerPersonaXDocumentoId($documentoId);

    $correosPlantilla = EmailPlantillaNegocio::create()->obtenerEmailXDestinatario($plantilla[0]["destinatario"], $documentoId, $dataPersona[0]['id']);

    $correos = '';
    if (!ObjectUtil::isEmpty($correosPlantilla)) {
      foreach ($correosPlantilla as $email) {
        $correos = $correos . $email . ';';
      }
    }

    $resultado = new stdClass();
    $resultado->correo = $correos;
    $resultado->plantilla = $plantilla;

    return $resultado;
  }

  public function enviarCorreoXAccion($correos, $comentario, $accionEnvio, $documentoId, $plantillaId, $usuarioId)
  {
    switch ($accionEnvio) {
      case "enviarPDF":
        return $this->enviarMovimientoEmailPDF($correos, $documentoId, $comentario, $usuarioId, $plantillaId);
      case "enviarCorreo":
        return $this->enviarCorreoConPrecio($correos, $documentoId, $comentario, $usuarioId, $plantillaId);
      case "enviarCorreoPDF":
        return $this->enviarMovimientoEmailCorreoMasPDF($correos, $documentoId, $comentario, $usuarioId, $plantillaId);
    }
  }

  public function obtenerMovimientoEntradaSalidaXFechaXBienId($fechaEmision, $bienId)
  {
    return Movimiento::create()->obtenerMovimientoEntradaSalidaXFechaXBienId($fechaEmision, $bienId);
  }

  public function getUserEmailByUserId($id)
  {
    return Movimiento::create()->getUserEmailByUserId($id);
  }

  public function verificarDocumentoObligatorioExiste($actualId)
  {
    return Movimiento::create()->verificarDocumentoObligatorioExiste($actualId);
  }

  public function verificarDocumentoEsObligatorioXOpcionID($opcionId)
  {
    return Movimiento::create()->verificarDocumentoEsObligatorioXOpcionID($opcionId);
  }

  public function guardarDocumentoAtencionSolicitud($origenId, $destinoId, $cantidadAtendida, $usuarioId)
  {
    return MovimientoBien::create()->guardarDocumentoAtencionSolicitud($origenId, $destinoId, $cantidadAtendida, $usuarioId);
  }

  public function obtenerEstadoNegocioXMovimientoId($movimientoId)
  {
    return Movimiento::create()->obtenerEstadoNegocioXMovimientoId($movimientoId);
  }

  public function generarBienUnicoXDocumentoId($documentoId, $usuarioId)
  {
    $dataDocumento = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($documentoId);
    if ($dataDocumento[0]['estado'] != 1) {
      throw new WarningException("Documento anulado, no se puede generar los productos únicos");
    }

    $dataBienUnico = BienUnicoNegocio::create()->obtenerMovimientoBienUnicoXDocumentoId($documentoId);
    if (!ObjectUtil::isEmpty($dataBienUnico)) {
      throw new WarningException("Ya se generó los productos únicos, refresque la página.");
    }

    $movimientoId = $dataDocumento[0]['movimiento_id'];
    $dataMovBien = MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);

    $fechaEmision = $dataDocumento[0]['fecha_emision'];

    $anio = date_format((date_create($fechaEmision)), 'Y');
    $mes = date_format((date_create($fechaEmision)), 'm');

    foreach ($dataMovBien as $item) {
      //CODIGO PRODUCTO SIN ESPACIOS (20) + AÑO (4) + MES (2) + CORRELATIVO DE (7)
      //sin concatenacion de ceros, sin BH, + periodo, 5 digitos

      $codigoBien = $item['bien_codigo'];
      $codigoBien = str_replace(' ', '', $codigoBien);
      $codigoBien = str_replace('BH', '', $codigoBien);

      if (strlen($codigoBien) > 20) {
        $codigoBien = substr($codigoBien, 0, 20);
      }

      $codBienUnico = $codigoBien . $anio . $mes;

      $dataCorrelativo = BienUnico::create()->bienUnicoObternerUltimoCodigoCorrelativo($codBienUnico);

      $correlativo = 0;
      if (!ObjectUtil::isEmpty($dataCorrelativo)) {
        $correlativo = $dataCorrelativo[0]['correlativo'] * 1;
      }

      for ($i = 0; $i < $item['cantidad']; $i++) {

        $correlativo++;
        $correlativoCadena = str_pad($correlativo, 5, "0", STR_PAD_LEFT);

        $codigoBU = $codigoBien . $anio . $mes . $correlativoCadena;

        //insertar bien unico

        $resBU = BienUnico::create()->insertarBienUnico($item['bien_id'], $codigoBU, $usuarioId);

        if ($resBU[0]['vout_estado'] == 1) {
          $resMBU = BienUnico::create()->insertarMovimientoBienUnico($item['movimiento_bien_id'], $resBU[0]['vout_id'], 1, $usuarioId);
        } else {
          throw new WarningException("Error al guardar bien unico");
        }
      }
    }

    $r = DocumentoNegocio::create()->actualizarEstadoQRXDocumentoId($documentoId, 2);

    return $resBU;
  }

  function anularBienUnicoXDocumentoId($documentoId)
  {
    return BienUnico::create()->anularBienUnicoXDocumentoId($documentoId);
  }

  function obtenerBienUnicoConfiguracionInicial($documentoId)
  {
    $dataDocumento = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($documentoId);

    $resultado = new stdClass();
    $resultado->dataBienUnicoDisponible = BienUnicoNegocio::create()->obtenerBienUnicoDisponibleXDocumentoId($documentoId);
    $resultado->dataDocumento = $dataDocumento;
    $resultado->dataMovimientoBien = MovimientoBien::create()->obtenerXIdMovimiento($dataDocumento[0]['movimiento_id']);
    $resultado->dataMovimientoBienUnico = BienUnicoNegocio::create()->obtenerMovimientoBienUnicoXDocumentoId($documentoId);

    return $resultado;
  }

  function guardarBienUnicoDetalle($listaBienUnicoDetalle, $listaBienUnicoDetalleEliminado, $usuarioId, $opcionId, $estadoQR)
  {

    if (!ObjectUtil::isEmpty($listaBienUnicoDetalleEliminado)) {
      foreach ($listaBienUnicoDetalleEliminado as $itemEliminar) {
        $res = BienUnico::create()->eliminarMovimientoBienUnico($itemEliminar);

        if ($res[0]['vout_exito'] == 0) {
          throw new WarningException($res[0]['vout_mensaje']);
        }
      }
    }

    $resDocumento = DocumentoNegocio::create()->obtenerDocumentoIdXMovimientoBienId($listaBienUnicoDetalle[0]['movimiento_bien_id']);
    $r = DocumentoNegocio::create()->actualizarEstadoQRXDocumentoId($resDocumento[0]['documento_id'], $estadoQR);

    foreach ($listaBienUnicoDetalle as $item) {
      $bienUnicoId = $item['bien_unico_id'];
      $movimientoBienId = $item['movimiento_bien_id'];

      $res = BienUnico::create()->guardarMovimientoBienUnico($bienUnicoId, $movimientoBienId, $usuarioId);

      if ($res[0]['vout_exito'] == 0) {
        throw new WarningException($res[0]['vout_mensaje']);
      }
    }

    $resultado = new stdClass();
    $resultado->respuesta = $res;

    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    $resultado->indicador = $movimientoTipo[0]["indicador"];

    return $resultado;
  }

  function obtenerDataEstadoNegocioPago()
  {
    return Tabla::create()->obtenerXPadreId(56);
  }

  private function guardarAnticipos($resDataDocumento, $anticiposAAplicar, $usuarioId, $camposDinamicos, $monedaId)
  {
    if (ObjectUtil::isEmpty($anticiposAAplicar["data"]))
      return;

    $documentoId = $resDataDocumento->documentoId;
    $proveedorId = $this->obtenerValorCampoDinamicoPorTipo($camposDinamicos, 5);
    $fecha = $this->obtenerValorCampoDinamicoPorTipo($camposDinamicos, 9);
    $totalDocumento = $this->obtenerValorCampoDinamicoPorTipo($camposDinamicos, 14) * 1;
    //        $actividadEfectivo = $anticiposAAplicar["actividadId"];
    $empresaId = $anticiposAAplicar["empresaId"];

    $retencion = 1;
    $monedaPago = $monedaId;
    $dolares = ($monedaId == 4) ? 1 : 0;

    $documentoAPagar = array(
      array(
        'documentoId' => $documentoId,
        'tipoDocumento' => '',
        'numero' => '',
        'serie' => '',
        'pendiente' => (float) $totalDocumento,
        'total' => (float) $totalDocumento,
        'dolares' => $dolares
      )
    );
    $documentoPagoConDocumento = array();
    $totalPagos = 0;
    foreach ($anticiposAAplicar["data"] as $anticipo) {
      array_push(
        $documentoPagoConDocumento,
        array(
          'documentoId' => $anticipo["documentoId"],
          'tipoDocumento' => '',
          'tipoDocumentoId' => '',
          'numero' => '',
          'serie' => '',
          'pendiente' => (float) $anticipo["pendiente"] * 1,
          'total' => (float) $anticipo["pendiente"] * 1,
          'monto' => (float) $anticipo["pendiente"] * 1,
          'dolares' => $dolares
        )
      );
    }

    // Como todo se hace en la misma moneda, setearemos el tc en 1
    $tipoCambio = 1;
    $pago = PagoNegocio::create()->registrarPago($proveedorId, $fecha, $documentoAPagar, $documentoPagoConDocumento, $usuarioId, 0, $tipoCambio, $monedaPago, $retencion, $empresaId, null);
  }

  public function relacionarDocumento($documentoIdOrigen, $documentoIdARelacionar, $usuarioId)
  {
    //VALIDAR QUE NO RELACIONEN UN DOCUMENTO YA RELACIONADO
    $dataRel = DocumentoNegocio::create()->obtenerDocumentoRelacionadoXDocumentoIdXDocumentoRelacionadoId($documentoIdOrigen, $documentoIdARelacionar);

    if (!ObjectUtil::isEmpty($dataRel)) {
      throw new WarningException('Documento a relacionar duplicado');
    }

    return DocumentoNegocio::create()->guardarDocumentoRelacionado($documentoIdOrigen, $documentoIdARelacionar, 1, 1, $usuarioId);
  }

  public function eliminarRelacionDocumento($documentoIdOrigen, $documentoIdARelacionar, $usuarioId)
  {
    $respuestaEliminar = DocumentoNegocio::create()->eliminarDocumentoRelacionado($documentoIdOrigen, $documentoIdARelacionar, $usuarioId);
    if ($respuestaEliminar[0]['vout_exito'] != 1) {
      throw new WarningException($respuestaEliminar[0]['vout_mensaje']);
    }

    return $respuestaEliminar;
  }

  public function obtenerUnidadMedida($bienId, $unidadMedidaId, $precioTipoId, $monedaId, $fechaEmision)
  {
    $unidad = UnidadNegocio::create()->obtenerActivasXBien($bienId);
    $respuesta = new stdClass();
    $respuesta->unidad_medida = $unidad;

    if ($unidadMedidaId == 0) {
      $unidadMedidaId = $unidad[0]["id"];
    }

    $dataPrecio = BienPrecioNegocio::create()->obtenerBienPrecioXBienIdXUnidadMedidaIdXPrecioTipoIdXMonedaId($bienId, $unidadMedidaId, $precioTipoId, $monedaId);
    if (ObjectUtil::isEmpty($dataPrecio)) {
      $precio = 0;
    } else {
      $precio = $dataPrecio[0]["precio"];
    }

    $precioCompra = BienPrecioNegocio::create()->obtenerPrecioCompraPromedio($bienId, $unidadMedidaId, $fechaEmision);

    if ($monedaId == 4) {
      $equivalenciaDolar = TipoCambioNegocio::create()->obtenerTipoCambioXFechaUltima($fechaEmision);
      $precioCompra = $precioCompra / $equivalenciaDolar[0]['equivalencia_venta'];
    }

    $respuesta->precioCompra = $precioCompra;
    $respuesta->precio = $precio;
    return $respuesta;
  }

  public function obtenerStockActual($bienId, $indice, $organizadorId, $unidadMedidaId, $organizadorDestinoId = null)
  {
    if (!ObjectUtil::isEmpty($organizadorId)) {
      //LA TRANSFERENCIA INTERNA TIENE ORGANIZADOR
      $stock = BienNegocio::create()->obtenerStockActual($bienId, $organizadorId, $unidadMedidaId, $organizadorDestinoId);
    } else {
      $stock = BienNegocio::create()->obtenerStockTotalXBienIDXUnidadMedidaId($bienId, $unidadMedidaId);
    }

    $cantidadMinima = BienNegocio::create()->obtenerCantidadMinimaConvertido($bienId, $organizadorId, $unidadMedidaId);
    $stock[0]['indice'] = $indice;
    $stock[0]['cantidad_minima'] = $cantidadMinima[0]['cantidad_minima'];

    return $stock;
  }

  public function obtenerStockParaProductosDeCopia($organizadorDefectoId, $detalle, $organizadorDestinoId = null)
  {
    $dataStock = array();
    foreach ($detalle as $item) {
      //TIENE QUE SER SIMILAR AL METODO DEL CONTROLADOR: obtenerStockActual
      $bienId = $item['bienId'];
      $unidadMedidaId = $item['unidadMedidaId'];
      $organizadorId = $item['organizadorId'];
      if (!ObjectUtil::isEmpty($organizadorDefectoId) && $organizadorDefectoId != 0) {
        $organizadorId = $organizadorDefectoId;
      }
      $stock = MovimientoNegocio::create()->obtenerStockActual($bienId, $item['index'], $organizadorId, $unidadMedidaId, $organizadorDestinoId);

      array_push($dataStock, $stock);
    }

    return $dataStock;
  }

  public function obtenerDocumentoRelacionadoTipoRecepcion($documentoId)
  {
    return Movimiento::create()->obtenerDocumentoRelacionadoTipoRecepcion($documentoId);
  }

  public function guardarArchivosXDocumentoID($documentoId, $lstDocumento, $lstDocEliminado, $usuCreacion)
  {
    return DocumentoNegocio::create()->guardarArchivosXDocumentoID($documentoId, $lstDocumento, $lstDocEliminado, $usuCreacion);
  }

  public function guardarEmailEnvioPendientesXReposicion($dataPAtencion, $asuntoCorreo, $plantillaId, $descripcionCorreo, $tituloCorreo, $mostrarDocumento = 1)
  {
    if (!ObjectUtil::isEmpty($dataPAtencion)) {
      $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID($plantillaId);
      $correosPlantilla = EmailPlantillaNegocio::create()->obtenerEmailXDestinatario($plantilla[0]["destinatario"], null, null);

      $correos = '';
      foreach ($correosPlantilla as $email) {
        $correos = $correos . $email . ';';
      }
      //dibujando la cabecera
      $dataDetalle = '<table  border=1 cellspacing=0 cellpadding=5 bordercolor="000000"  width="91.7%">
                        <thead>';

      $html = '<tr>';
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Item</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>F.Traslado</th>";
      if ($mostrarDocumento == 1) {
        //                $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Tipo de documento</th>";
        $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>S/N</th>";
      }
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Origen</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Producto</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Cantidad</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Pendiente</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>U. Medida</th>";
      $html = $html . '</tr>';

      $dataDetalle = $dataDetalle . $html;
      $dataDetalle = $dataDetalle . '<thead>';
      $dataDetalle = $dataDetalle . '<tbody>';

      foreach ($dataPAtencion as $index => $item) {
        $html = '<tr>';
        $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . ($index + 1);
        $html = $html . '<td style=\'text-align:center;font-size:14px;line-height:1.5;\'>' . $item['fecha_traslado'];
        if ($mostrarDocumento == 1) {
          //                    $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['documento_tipo_descripcion'];
          $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['serie_numero'];
        }
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['org_origen'];
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['bien_descripcion'];
        $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . $item['cantidad'] * 1;
        $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . ($item['cantidad'] - $item['cant_rep']) * 1;
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['unidad_medidad'];
        $html = $html . '</tr>';

        $dataDetalle = $dataDetalle . $html;
      }

      $dataDetalle = $dataDetalle . '</tbody></table>';
      $descripcion = $descripcionCorreo;

      //logica correo:
      if (ObjectUtil::isEmpty($asuntoCorreo)) {
        $asunto = $plantilla[0]["asunto"];
      } else {
        $asunto = $asuntoCorreo;
      }
      $cuerpo = $plantilla[0]["cuerpo"];

      $cuerpo = str_replace("[|titulo_email|]", $tituloCorreo, $cuerpo);
      $cuerpo = str_replace("[|descripcion|]", $descripcion, $cuerpo);
      $cuerpo = str_replace("[|detalle_programacion|]", $dataDetalle, $cuerpo);

      $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correos, $asunto, $cuerpo, 1, 1);
      //            return $cuerpo;
      return $tituloCorreo . ' ' . $res[0]['vout_mensaje'] . ' Id: ' . $res[0]['id'] . ' <br>';
    } else {
      return '';
    }
  }

  public function generarDocumentoImpresionPDF($documentoId, $url, $data)
  {
    //Import the PhpJasperLibrary
    require_once __DIR__ . '/../../reporteJasper/PhpJasperLibrary/tcpdf/tcpdf.php';
    require_once __DIR__ . '/../../reporteJasper/PhpJasperLibrary/PHPJasperXML.inc.php';

    $dataDocumento = $data->dataDocumento;
    $identificadorNegocio = $dataDocumento[0]['identificador_negocio'];

    switch ((int) $identificadorNegocio) {
      case 3: //boleta
        return $this->generarDocumentoImpresionPDFBoleta($documentoId, $url, $data);
      case 5: //nota de credito
        return $this->generarDocumentoImpresionPDFNotaCredito($documentoId, $url, $data);

        //            default:
        //                return $this->generarDocumentoPDFEstandar($documentoId, $comentario, $tipoSalidaPDF, $url, $data);
    }
  }

  public function generarDocumentoImpresionPDFBoleta($documentoId, $url, $data)
  {
    //CONEXION
    //        $server="localhost";
    //        $db="bhdt_20170901";
    //        $user="root";
    //        $pass="local";
    //DATOS
    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    //        $detalle = $data->detalle;
    $documentoDetalle = $data->documentoDetalle;
    $dataEmpresa = $data->dataEmpresa;
    $dataMovimientoTipoColumna = $data->movimientoTipoColumna;
    $dataDocumentoRelacion = $data->documentoRelacionado;

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }

    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];
    $fechaEmision = DateUtil::formatearBDACadena($dataDocumento[0]['fecha_emision']);

    $dia = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd');
    //        $mesNombre = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");
    $mes = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'm');
    $anio = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'y'); //Y año completo
    //TOTAL EN LETRAS
    $totalLetras = Util::normaliza($data->totalEnTexto);

    //FIN DATOS
    //REPORTE
    $urlJrxml = __DIR__ . '/../../reporteJasper/almacen/boleta.jrxml';
    $xml = simplexml_load_file($urlJrxml);
    $PHPJasperXML = new PHPJasperXML();

    //DETALLE: cantidad,simbolo,bien_descripcion,valor_monetario,sub_total
    $PHPJasperXML->arrayParameter = array(
      //            "vin_movimiento_id"=>$dataDocumento[0]['movimiento_id'],
      "serie_numero" => $serieDocumento . $dataDocumento[0]['numero'],
      "nombre" => $dataDocumento[0]['nombre'],
      "direccion" => $dataDocumento[0]['direccion'],
      "documento" => $dataDocumento[0]['codigo_identificacion'],
      "fecha_dia" => $dia,
      //            "fecha_mes" => $mesNombre[$mes*1-1],
      "fecha_mes" => $mes,
      "fecha_anio" => $anio,
      "total_letras" => strtoupper($totalLetras),
      "total" => $dataDocumento[0]['total'],
      "moneda_simbolo" => $dataDocumento[0]['moneda_simbolo'],
      "fecha_pie" => $fechaEmision,
    );

    $PHPJasperXML->xml_dismantle($xml);
    //        $PHPJasperXML->transferDBtoArray($server,$user,$pass,$db);
    $PHPJasperXML->transferirDataSql($documentoDetalle); //SIN CONEXION
    //        $PHPJasperXML->outpage("I");    //page output method I:standard output  D:Download file

    $PHPJasperXML->outpage('F', $url);
    //FIN REPORTE

    return $titulo;
  }

  public function generarDocumentoImpresionPDFNotaCredito($documentoId, $url, $data)
  {
    //DATOS
    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    //        $detalle = $data->detalle;
    $documentoDetalle = $data->documentoDetalle;
    $dataEmpresa = $data->dataEmpresa;
    $dataMovimientoTipoColumna = $data->movimientoTipoColumna;
    $dataDocumentoRelacion = $data->documentoRelacionado;

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }

    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];
    $fechaEmision = DateUtil::formatearBDACadena($dataDocumento[0]['fecha_emision']);
    $fechaVencimiento = DateUtil::formatearBDACadena($dataDocumento[0]['fecha_vencimiento']);

    $dia = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd');
    $mesNombre = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre");
    $mes = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'm');
    $anio = date_format((date_create($dataDocumento[0]['fecha_emision'])), 'Y'); //Y año completo
    //TOTAL EN LETRAS
    $totalLetras = Util::normaliza($data->totalEnTexto);

    //DOCUMENTO TIPO DATO
    $serieNumero_doc_rel = '';
    $tipo_doc_rel = '';
    $fechaEmision_doc_rel = '';
    if (!ObjectUtil::isEmpty($dataDocumentoRelacion)) {
      foreach ($dataDocumentoRelacion as $item) {
        if ($item['identificador_negocio'] == 3) {
          $serieNumero_doc_rel = $item['serie_numero_original'];
          $fechaEmision_doc_rel = DateUtil::formatearBDACadena($item['fecha_emision']);
          $tipo_doc_rel = 'Boleta';
        }
        if ($item['identificador_negocio'] == 4) {
          $serieNumero_doc_rel = $item['serie_numero_original'];
          $fechaEmision_doc_rel = DateUtil::formatearBDACadena($item['fecha_emision']);
          $tipo_doc_rel = 'Factura';
        }
      }
      $serieNumero_doc_rel = explode(" - ", $serieNumero_doc_rel);
    }


    $anulacion = '';
    $bonificacion = '';
    $descuento = '';
    $devoluciones = '';
    $otros = '';
    foreach ($documentoDatoValor as $item) {
      switch ($item["documento_tipo_id"] * 1) {
        case 2885:
          $serieNota = $item["valor"];
          break;
        case 2886:
          $numeroNota = $item["valor"];
          break;
        case 609:
          if ($item["valor"] == "Anulación") {
            $anulacion = 'X';
          }
          if ($item["valor"] == "Bonificaciones") {
            $bonificacion = 'X';
          }
          if ($item["valor"] == "Descuentos") {
            $descuento = 'X';
          }
          if ($item["valor"] == "Devoluciones") {
            $devoluciones = 'X';
          }
          if ($item["valor"] == "Otros") {
            $otros = 'X';
          }
          break;
        default:
          break;
      }
    }

    //REPORTE
    $urlJrxml = __DIR__ . '/../../reporteJasper/almacen/nota_credito.jrxml';
    $xml = simplexml_load_file($urlJrxml);
    $PHPJasperXML = new PHPJasperXML();

    //DETALLE: cantidad,simbolo,bien_descripcion,valor_monetario,sub_total
    $PHPJasperXML->arrayParameter = array(
      //            "vin_movimiento_id"=>$dataDocumento[0]['movimiento_id'],
      //            "serie_numero" => $serieDocumento . $dataDocumento[0]['numero'],
      "serie_numero" => $serieNota . '-' . $numeroNota,
      "nombre" => $dataDocumento[0]['nombre'],
      "total" => $dataDocumento[0]['total'],
      "documento" => $dataDocumento[0]['codigo_identificacion'],
      "total_letras" => 'SON: ' . strtoupper($totalLetras),
      "fecha_emision" => $dia . ' de ' . $mesNombre[$mes * 1 - 1] . ' del ' . $anio,
      "sub_total" => $dataDocumento[0]['subtotal'],
      "igv" => $dataDocumento[0]['igv'],
      "igv_porcentaje" => 18,
      "fecha_emision_doc_rel" => $fechaEmision_doc_rel,
      "tipo_doc_rel" => $tipo_doc_rel,
      //            "serie_rel" => $serieNumero_doc_rel[0].'-',
      "serie_doc_rel" => $serieNumero_doc_rel[0] . '-' . $serieNumero_doc_rel[1],
      "moneda_simbolo" => $dataDocumento[0]['moneda_simbolo'],
      "mot_anulacion" => $anulacion,
      "mot_bonificacion" => $bonificacion,
      "mot_descuento" => $descuento,
      "mot_devolucion" => $devoluciones,
      "mot_otros" => $otros,
    );

    $PHPJasperXML->xml_dismantle($xml);
    //        $PHPJasperXML->transferDBtoArray($server,$user,$pass,$db);
    $PHPJasperXML->transferirDataSql($documentoDetalle); //SIN CONEXION
    //        $PHPJasperXML->outpage("I");    //page output method I:standard output  D:Download file

    $PHPJasperXML->outpage('F', $url);
    //FIN REPORTE

    return $titulo;
  }

  //EDICION
  public function validarDocumentoEdicion($documentoId)
  {
    $respuesta = new stdClass();
    $respuesta->exito = 1;

    $dataDocumento = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($documentoId);

    //ORDEN DE VENTA
    if ($dataDocumento[0]['identificador_negocio'] == 2) {
      $atencionEstado = ProgramacionAtencionNegocio::create()->obtenerDocumentoAtencionEstadoLogico($documentoId);

      if ($atencionEstado[0]['estado_atencion'] == 4) { //ATENCION COMPLETA
        $respuesta->exito = 0;
        $respuesta->mensaje = 'No se puede editar la orden de venta porque fue atendida completamente';
      }
    }
    //COTIZACION DE VENTA
    if ($dataDocumento[0]['identificador_negocio'] == 1 && $dataDocumento[0]['documento_estado_id'] != 7) {
      $respuesta->exito = 0;
      $respuesta->mensaje = 'No se puede editar la cotizacion porque el estado actual no lo permite, por favor actualice la página.';
    }

    return $respuesta;
  }

  // TODO: Inicio Obtener para Editar
  function obtenerDocumentoRelacionEdicion($documentoTipoOrigenId, $documentoTipoDestinoId, $movimientoId, $documentoId, $opcionId, $documentoRelacionados)
  {
    $respuesta = new ObjectUtil();
    $arrayDataBien = array();

    $documentoACopiar = DocumentoNegocio::create()->obtenerDataDocumentoACopiarEdicion($documentoTipoDestinoId, $documentoTipoOrigenId, $documentoId);

    if (ObjectUtil::isEmpty($documentoACopiar)) {
      throw new WarningException("No se encontró el documento");
    }

    $respuesta->documentoACopiar = $documentoACopiar;
    // $respuesta->dataDocumentoRelacionada = DocumentoNegocio::create()->obtenerDataDocumentoACopiarRelacionada($documentoTipoOrigenId, $documentoTipoDestinoId, $documentoId);
    $respuesta->detalleDocumento = $this->obtenerDocumentoRelacionDetalleEdicion($movimientoId, $documentoId, $opcionId, $documentoRelacionados);

    if ($documentoTipoDestinoId != $documentoTipoOrigenId) {
      $respuesta->documentosRelacionados = DocumentoNegocio::create()->obtenerRelacionesDocumento($documentoId);
    } else {
      $respuesta->documentosRelacionados = 1;
    }

    $respuesta->dataPagoProgramacion = PagoNegocio::create()->obtenerPagoProgramacionXDocumentoId($documentoId);
    $respuesta->dataDocumentoAdjunto = DocumentoNegocio::create()->obtenerDocumentoAdjuntoXDocumentoId($documentoId);
    $respuesta->dataDistribucionContable = ContDistribucionContableNegocio::create()->obtenerContDistribucionContableXDocumentoId($documentoId);
    //OBTENER DATA DE UNIDAD DE MEDIDA
    $documentoDetalle = $respuesta->detalleDocumento;
    foreach ($documentoDetalle as $index => $item) {
      $bienId = $item['bien_id'];
      $unidadMedidaId = $item['unidad_medida_id'];
      $precioTipoId = $item['precio_tipo_id'];
      $monedaId = $documentoACopiar[0]['moneda_id'];
      $fechaEmision = date("d/m/Y");
      foreach ($documentoACopiar as $itemDato) {
        if ($itemDato['tipo'] == 9) {
          $fechaEmision = date_format((date_create($itemDato['valor'])), 'd/m/Y');
        }
      }

      $data = MovimientoNegocio::create()->obtenerUnidadMedida($bienId, $unidadMedidaId, $precioTipoId, $monedaId, $fechaEmision);
      $documentoDetalle[$index]['dataUnidadMedida'] = $data;
      $dataBien = BienNegocio::create()->obtenerActivosXMovimientoTipoIdBienId($empresaId, $movimientoTipo[0]["id"], $bienId);
      foreach ($dataBien as $datos) {
        array_push($arrayDataBien, $datos);
      }
    }
    $respuesta->detalleDocumento = $documentoDetalle;
    $respuesta->dataBien = $arrayDataBien;
    // FIN OBTENER DATA UNIDAD MEDIDA

    $respuesta->dataPostores = null;
    $respuesta->listaPagoProgramacionPostores = null;
    $listaPagoProgramacionPostores = [];
    if($documentoTipoDestinoId == Configuraciones::GENERAR_COTIZACION){
      $respuesta->dataPostores = Documento::create()->obtenerDocumentoDetalleDatos($documentoId);
      foreach($respuesta->dataPostores as $index => $item ){
        $res = Documento::create()->obtenerDocumentoDetalledistribucionPagoxId($item['id']);
        $arrayPagos = [];
        foreach($res as $itemRes){
          array_push($arrayPagos, array($itemRes['fecha_pago'], $itemRes['importe'], $itemRes['dias'], $itemRes['porcentaje'], $itemRes['glosa'], $itemRes['id']));
        }
        if(!ObjectUtil::isEmpty($arrayPagos)){
          $listaPagoProgramacionPostores[$index] = $arrayPagos;
        }
      }
      $respuesta->listaPagoProgramacionPostores = $listaPagoProgramacionPostores;
    }

    return $respuesta;
  }
  // TODO: Fin Obtener para Editar

  function obtenerDocumentoRelacionDetalleEdicion($movimientoId, $documentoId, $opcionId, $documentoRelacionados)
  {
    //solo va a ver un documento a copiar/editar
    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);

    foreach ($documentoDetalle as $index => $detalle) {
      //obtener datos de: movimiento_bien_detalle
      $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleXMovimientoBienId($detalle['movimiento_bien_id']);

      $documentoDetalle[$index]['movimiento_bien_detalle'] = $resMovimientoBienDetalle;
    }

    return $documentoDetalle;
  }

  // TODO: Inicio Guardar Edicion
  public function guardarXAccionEnvioEdicion($documentoId, $opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $listaDetalleEliminar, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $accionEnvio, $tipoPago, $listaPagoProgramacion, $anticiposAAplicar = null, $periodoId = null, $percepcion = null, $datosExtras = null, $detalleDistribucion = null, $contOperacionTipoId = null, $distribucionObligatoria = null, $igv_porcentaje = null, $dataStockReservaOk = null, $dataPostorProveedor = null, $listaPagoProgramacionPostores = null)
  {
    $resEdicion = $this->guardarEdicion($documentoId, $opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $listaDetalleEliminar, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $tipoPago, $periodoId, $datosExtras, $detalleDistribucion, $contOperacionTipoId, $distribucionObligatoria, $igv_porcentaje);

    //ACTUALIZAMOS IMPORTE DE PROGRAMACION DE PAGO
    if ($tipoPago == 2) {
      $resPP = Pago::create()->actualizarPagoProgramacionImporteXDocumentoId($documentoId);
    }

    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);

    $accionBusquedad = preg_quote('enviar', '/') . '.*';
    if ((bool) preg_match("/^{$accionBusquedad}$/i", $accionEnvio)) {
      //SI EL MOVIMIENTO ES COTIZACION DE VENTA
      if ($movimientoTipo[0]['codigo'] == 7 && $documentoTipoId == 23) {
        DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 1, $usuarioId);
      }
    }

    $documentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);
    $respuesta = new stdClass();
    $respuesta->bandera_historial = $documentoTipo[0]['bandera_historial'];

    $dataMovimientoDocumento = MovimientoBien::create()->obtenerMovimientoIdXDocumentoId($documentoId);
    $respuesta->movimientoId = $dataMovimientoDocumento[0]['movimiento_id'];


    //Validacion de postores
    if(!ObjectUtil::isEmpty($dataPostorProveedor)){
      foreach($dataPostorProveedor as $itemPostor){
        $respuestaDataPostorProveedor = Documento::create()->editar_documento_detalle($documentoId, $itemPostor['proveedor_id'], $itemPostor['monedaId'], $itemPostor['tipoCambio'], $itemPostor['igv'], $itemPostor['tiempoEntrega'], $itemPostor['tiempo'], $itemPostor['condicionPago'], $itemPostor['sumilla'], $usuarioId);
        
        $pagoProgramacion = $listaPagoProgramacionPostores[intval($itemPostor['indice'])];
        foreach($pagoProgramacion as $itemPagoProgramacionPostores){
          if (strpos($itemPagoProgramacionPostores[0], '/') !== false) {
            $fechaPago = DateUtil::formatearCadenaACadenaBD($itemPagoProgramacionPostores[0]);
          }else{
            $fechaPago = $itemPagoProgramacionPostores[0];
          }

          $importePago = $itemPagoProgramacionPostores[1];
          $dias = $itemPagoProgramacionPostores[2];
          $porcentaje = $itemPagoProgramacionPostores[3];
          $glosa = $itemPagoProgramacionPostores[4];
          $id = $itemPagoProgramacionPostores[5];
          $res = Documento::create()->editarDocumentoDetalleDistribucionPagos($respuestaDataPostorProveedor[0]['vout_id'], $fechaPago, $importePago, $dias, $porcentaje, $glosa, $usuarioId, $id);
        }
      }
    }
    
    if($accionEnvio == 'generar'){
      $respuesta->documentoId = $documentoId;
      //se usa para compras
      $arraydetalleXpostor = [];
      if ($documentoTipoId == Configuraciones::GENERAR_COTIZACION) {
        foreach($dataPostorProveedor as $index => $itemPostores){
          $proveedor_id = $itemPostores['proveedor_id'];
          $filtrados = array_values(array_filter($detalle, function($item) use($proveedor_id){
            return $item['postor_ganador_id'] === $proveedor_id;
          }));
          $arraydetalleXpostor[$index] = array($filtrados, $proveedor_id, $itemPostores['monedaId'], $itemPostores['tipoCambio'], $itemPostores['igv'], $itemPostores['tiempoEntrega'], $itemPostores['tiempo'], $itemPostores['condicionPago']);
        }

        $cont = count($camposDinamicos);
        foreach($arraydetalleXpostor as $indexPostor => $itemPostor){
          $camposDinamicos [$cont] = array(
            "id" =>"",
            "tipo" => "23",
            "opcional" => "1",
            "descripcion" => "Adjuntar archivos",
            "codigo" => "",
            "valor" => $itemPostor[1]);
          $camposDinamicos [$cont + 2] = array(
            "id" =>"",
            "tipo" => "2",
            "opcional" => "1",
            "descripcion" => "Tiempo de entrega",
            "codigo" => "",
            "valor" => $itemPostor[5]);
          $camposDinamicos [$cont + 3] = array(
            "id" =>"",
            "tipo" => "50",
            "opcional" => "1",
            "descripcion" => "Condición de pago",
            "codigo" => "",
            "valor" => $itemPostor[7] == 1? 501:502); 

          $respuestaCotizacion = $this->guardarDocumentoCotizacion($camposDinamicos, $usuarioId, Configuraciones::COTIZACIONES,$itemPostor[0], $respuesta, $periodoId, 160, 1, null, null, null,$arraydetalleXpostor[$indexPostor]);
          //generamos OC
          $this->guardarDocumentoCotizacion($camposDinamicos, $usuarioId, Configuraciones::ORDEN_COMPRA,$itemPostor[0], $respuesta, $periodoId, 395, 2, $listaPagoProgramacionPostores[$indexPostor], $respuestaCotizacion, null,$arraydetalleXpostor[$indexPostor]);
        
          DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 17, $usuarioId);
        }


      }
      return $respuesta;
    }
    if ($accionEnvio == 'guardar') {
      $respuesta->documentoId = $documentoId;
      return $respuesta;
    }

    if ($accionEnvio == 'enviar') {
      $respuesta->documentoId = $documentoId;
      return $respuesta;
    }

    if ($accionEnvio == 'enviarEImprimir') {
      $respuesta->dataImprimir = $this->imprimirExportarPDFDocumento($documentoTipoId, $documentoId, $usuarioId);
      $respuesta->documentoId = $documentoId;
      return $respuesta;
    } else {
      //obtener email de plantilla
      $movimientoTipoId = $this->obtenerIdXOpcion($opcionId);
      $plantilla = EmailPlantillaNegocio::create()->obtenerPlantillaDestinatarioXAccionDescripcionXMovimientoTipoId($accionEnvio, $movimientoTipoId);
      $dataPersona = DocumentoNegocio::create()->obtenerPersonaXDocumentoId($documentoId);

      $correosPlantilla = EmailPlantillaNegocio::create()->obtenerEmailXDestinatario($plantilla[0]["destinatario"], $documentoId, $dataPersona[0]['id']);

      if (ObjectUtil::isEmpty($correosPlantilla)) {
        $this->setMensajeEmergente("Email en blanco, nose pudo enviar correo.", null, Configuraciones::MENSAJE_WARNING);
        $respuesta->documentoId = $documentoId;
        return $respuesta;
      }

      $correos = '';
      foreach ($correosPlantilla as $email) {
        $correos = $correos . $email . ';';
      }

      $plantillaId = $plantilla[0]["email_plantilla_id"];
      $respuesta->dataEnvioCorreo = $this->enviarCorreoXAccion($correos, $comentario, $accionEnvio, $documentoId, $plantillaId, $usuarioId);
      $respuesta->documentoId = $documentoId;
      return $respuesta;
    }
  }
  // TODO: Fin Guardar Edicion

  // TODO: Inicio Guardar Edicion
  public function guardarEdicion($documentoId, $opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $listaDetalleEliminar, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $tipoPago, $periodoId, $datosExtras, $detalleDistribucion = null, $contOperacionTipoId = null, $distribucionObligatoria = null, $igv_porcentaje = null)
  {
    $dataDocumento = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($documentoId);
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXId($documentoTipoId);

    //valdiar
    if($documentoTipoId == Configuraciones::GENERAR_COTIZACION){
      $mensaje = '';
      $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($dataDocumento[0]['movimiento_id']);
      foreach($documentoDetalle as $itemDetalle){
        foreach($detalle as $itemDetalleEdicion){
          if($itemDetalleEdicion['bienId'] == $itemDetalle['bien_id']){
            if(intval($itemDetalleEdicion['cantidad']) > intval($itemDetalle['cantidad'])){
              $mensaje .= "El detalle no debe ser mayor que ".  round($itemDetalle['cantidad'])." que es la solicitada, para la fila: ". ($itemDetalle['index'] + 1) . "<br>";
            }
  
          }
        }
      }
      if (!ObjectUtil::isEmpty($mensaje)) {
        throw new WarningException($mensaje);
      }
    }

    $respuestaDoc = MovimientoNegocio::create()->guardarEdicionDocumento($documentoId, $camposDinamicos, $comentario, $periodoId, $tipoPago, $monedaId, $usuarioId, $datosExtras, $contOperacionTipoId, $igv_porcentaje);

    //ELIMINAR MOVIMIENTO BIEN
    if (!ObjectUtil::isEmpty($listaDetalleEliminar)) {
      foreach ($listaDetalleEliminar as $itemId) {
        $resElimina = MovimientoBien::create()->actualizarEstadoXId($itemId, 2);
      }
    }

    //Insertamos el detalle
    foreach ($detalle as $item) {
      $valido = $this->validarItemDetalleMovimientoEdicion($item, $opcionId, $dataDocumentoTipo, $camposDinamicos, $monedaId);

      $agrupadorDetalle = "";
      if ($documentoTipoId == 23 || $documentoTipoId == 133) {
        if (!ObjectUtil::isEmpty($item["agrupadorId"])) {
          $agrupadorDetalle = $item["agrupadorId"];
        }
      }
      $ticket = "";
      if ($documentoTipoId == 23) {
        if (!ObjectUtil::isEmpty($item["ticket"])) {
          $ticket = $item["ticket"];
        }
      }

      //REGISTRAR LA EDICION DEL DETALLE
      if (!ObjectUtil::isEmpty($item['movimientoBienId'])) {
        $movimientoBien = MovimientoBien::create()->editar($item['movimientoBienId'], $dataDocumento[0]['movimiento_id'], $item["organizadorId"], $item["bienId"], $item["unidadMedidaId"], $item["cantidad"], $item["precio"], 1, $usuarioId, $item["precioTipoId"], $item["utilidad"], $item["utilidadPorcentaje"], $checkIgv, $item["adValorem"], $item["comentarioBien"], $item["agenciaId"], $agrupadorDetalle, $ticket, $item["CeCoId"], ($item["precioPostor1"] == "" ? null : $item["precioPostor1"]), ($item["precioPostor2"] == "" ? null : $item["precioPostor2"]), ($item["precioPostor3"] == "" ? null : $item["precioPostor3"]), $item["esCompra"], $item["cantidadAceptada"], $item['postor_ganador_id']);
      } else {
        $movimientoBien = MovimientoBien::create()->guardar($dataDocumento[0]['movimiento_id'], $item["organizadorId"], $item["bienId"], $item["unidadMedidaId"], $item["cantidad"], $item["precio"], 1, $usuarioId, $item["precioTipoId"], $item["utilidad"], $item["utilidadPorcentaje"], $checkIgv, $item["adValorem"], $item["comentarioBien"], $item["agenciaId"], $agrupadorDetalle, $ticket);
      }

      $movimientoBienId = $this->validateResponse($movimientoBien);
      if (ObjectUtil::isEmpty($movimientoBienId) || $movimientoBienId < 1) {
        throw new WarningException("No se pudo guardar un detalle del movimiento");
      }

      // guardar el detalle del detalle del movimiento en movimiento_bien_detalle
      if (!ObjectUtil::isEmpty($item["detalle"])) {
        foreach ($item["detalle"] as $valor) {
          if ($valor['columnaCodigo'] == 18) {
            $fechaVencimiento = DateUtil::formatearCadenaACadenaBD($valor['valorDet']);
            //EDITA SI YA EXISTE LA FECHA DE VENCIMIENTO SINO REGISTRA
            $resDetalle = MovimientoNegocio::create()->movimientoBienDetalleEditarFecha($movimientoBienId, $valor['columnaCodigo'], $fechaVencimiento, $usuarioId);
          }
          if($documentoTipoId == Configuraciones::GENERAR_COTIZACION && $valor['columnaCodigo'] == 37){
            $resDetalle = MovimientoNegocio::create()->movimientoBienDetalleEditarCadena($movimientoBienId, $valor['columnaCodigo'], $valor['valorDet'], $usuarioId, $valor['valorExtra']);
          }
        }
      }
    }
    $documento_relacionado_id = null;
    $movimiento_relacionado_id = null;
    /*if($documentoTipoId == 23){
      $relacionadosDocumentoActual = DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoId($documentoId);
      foreach ($relacionadosDocumentoActual as $itemRelacion) {
        if($itemRelacion['documento_tipo_id'] == 269){
          $documento_relacionado_id = $itemRelacion['documento_relacionado_id'];
          $movimiento_relacionado_id = $itemRelacion['movimiento_id'];
        }
      }
      if(!ObjectUtil::isEmpty($documento_relacionado_id)){
        $movimientoBien = MovimientoBien::create()->obtenerXIdMovimiento($movimiento_relacionado_id);
        foreach ($movimientoBien as $i => $item) {

          if (!ObjectUtil::isEmpty($movimientoBien)) {
            foreach ($movimientoBien as $itemId) {
              $resElimina = MovimientoBien::create()->actualizarEstadoXId($itemId["movimiento_bien_id"], 2);
            }
          }
          $valido = $this->validarItemDetalleMovimientoEdicionR($item, $opcionId, $dataDocumentoTipo, $camposDinamicos, $monedaId);
          if (!ObjectUtil::isEmpty($item['movimiento_bien_id'])) {
            $movimientoBien = MovimientoBien::create()->editar($item['movimiento_bien_id'], $movimiento_relacionado_id, $detalle[$i]["organizadorId"], $detalle[$i]["bienId"], $detalle[$i]["unidadMedidaId"], $detalle[$i]["cantidad"], $detalle[$i]["precio"], 1, $usuarioId, $detalle[$i]["precioTipoId"], $detalle[$i]["utilidad"], $detalle[$i]["utilidadPorcentaje"], $checkIgv, $detalle[$i]["adValorem"], $detalle[$i]["comentarioBien"], $detalle[$i]["agenciaId"], null);
          }else{
            $movimientoBien = MovimientoBien::create()->guardar($dataDocumento[0]['movimiento_id'], $item["organizadorId"], $item["bienId"], $item["unidadMedidaId"], $item["cantidad"], $item["precio"], 1, $usuarioId, $item["precioTipoId"], $item["utilidad"], $item["utilidadPorcentaje"], $checkIgv, $item["adValorem"], $item["comentarioBien"], $item["agenciaId"], $agrupadorDetalle);
          }
        }
      }
    }*/

    $eliminarDistribucion = ContDistribucionContableNegocio::create()->anularDistribucionContableXDocumentoId($documentoId);

    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    /** @var array */
    $operacionTipoMovimiento = ContOperacionTipoNegocio::create()->obtenerContOperacionTipoXMovimientoTipoId($movimientoTipo[0]['id']);
    if (!ObjectUtil::isEmpty($operacionTipoMovimiento)) {
      if (array_search($contOperacionTipoId, array_column($operacionTipoMovimiento, 'id')) === false) {
        throw new WarningException("La operación tipo seleccionada no pertenece al movimiento tipo.");
      }
      if ($distribucionObligatoria == 1) {
        $respuestaValidarDistribucion = ContDistribucionContableNegocio::create()->validarDistribucionContable($documentoId, $detalleDistribucion, $contOperacionTipoId);
      }
      $respuestaGuardarDistribucion = ContDistribucionContableNegocio::create()->guardarContDistribucionContable($documentoId, $contOperacionTipoId, $detalleDistribucion, $usuarioId);
    }

    $this->setMensajeEmergente("La operación se completó de manera satisfactoria");

    return $documentoId;
  }
  // TODO: Fin Guardar Edicion

  private function validarItemDetalleMovimientoEdicion($item, $opcionId, $dataDocumentoTipo = null, $camposDinamicos, $monedaId)
  {
    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    // validaciones
    if ($item["bienId"] == NULL) {
      throw new WarningException("No se especificó un valor válido para Bien. ");
    }

    if ($item["unidadMedidaId"] == NULL) {
      throw new WarningException("No se especificó un valor válido para Unidad Medida. ");
    }
    if ($item["cantidad"] == NULL or $item["cantidad"] <= 0) {
      throw new WarningException("No se especificó un valor válido para Cantidad. ");
    }

    //obtengo la fecha de emision
    $fechaEmision = null;
    foreach ($camposDinamicos as $valorCampo) {
      if ($valorCampo["tipo"] == 9) {
        $fechaEmision = DateUtil::formatearCadenaACadenaBD($valorCampo["valor"]);
      }
    }

    if (ObjectUtil::isEmpty($item["movimientoBienId"])) {
      MovimientoNegocio::create()->obtenerStockAControlar($opcionId, $item["bienId"], $item["organizadorId"], $item["unidadMedidaId"], $item["cantidad"], $fechaEmision, $item["detalle"]);
    } else {
      //SI ES ENTRADA O SALIDA VALIDA STOCK
      if ($movimientoTipo[0]["indicador"] == MovimientoTipoNegocio::INDICADOR_SALIDA || $movimientoTipo[0]["indicador"] == MovimientoTipoNegocio::INDICADOR_ENTRADA) {
        //ACTUALIZO TEMPORALMENTE EL MOVIMIENTO BIEN A INACTIVO
        $resEstInac = MovimientoBien::create()->actualizarEstadoXId($item["movimientoBienId"], 0);

        if ($dataDocumentoTipo[0]["id"] != "12") {
          MovimientoNegocio::create()->obtenerStockAControlar($opcionId, $item["bienId"], $item["organizadorId"], $item["unidadMedidaId"], $item["cantidad"], $fechaEmision, $item["detalle"], true);
        }

        //ACTUALIZO MOVIMIENTO BIEN A ACTIVO
        $resEstAc = MovimientoBien::create()->actualizarEstadoXId($item["movimientoBienId"], 1);
      }
    }

    //validacion el precio unitario tiene que ser mayor al precio de compra.
    $precioCompra = 0;
    $validarPrecios = true;
    //        if ($item["precio"] * 1 == 0) {
    //            $validarPrecios = false;
    //        }

    if (!ObjectUtil::isEmpty($item["precioCompra"])) {
      $precioCompra = $item["precioCompra"];
    }

    // Array que los contiene id de los tipos de documentos a los que no debe figurar la validacion
    $noValidarPrecioXDocTipoId = [23];
    $realizarValidacionPrecio = array_search($dataDocumentoTipo[0]["id"], $noValidarPrecioXDocTipoId);

    if (!ObjectUtil::isEmpty($dataDocumentoTipo) && $dataDocumentoTipo[0]["validacion"] == 1 && $validarPrecios && $realizarValidacionPrecio) {
      $precioUnitario = $item["precio"];
      if ($precioUnitario <= $precioCompra) {
        throw new WarningException(
          "No se pudo guardar un detalle del movimiento, precio unitario tiene que ser mayor al precio de compra.<br>"
            . "Producto: " . $item['bienDesc'] . '<br>'
            . "Precio compra: " . number_format($precioCompra, 2, ".", ",") . '<br>'
            . "Precio unitario: " . number_format($precioUnitario, 2, ".", ",") . '<br>'
        );
      }
    }
    if ($dataDocumentoTipo[0]["tipo"] == 1 && $validarPrecios) {
      $precioUnitario = $item["precio"];

      $dataPrecio = BienPrecioNegocio::create()->obtenerBienPrecioXBienIdXUnidadMedidaIdXPrecioTipoIdXMonedaId($item["bienId"], $item["unidadMedidaId"], $item["precioTipoId"], $monedaId);
      if (!ObjectUtil::isEmpty($dataPrecio)) {
        if ($checkIgv == 1) {
          $precioVenta = $dataPrecio[0]["incluye_igv"];
        } else {
          $precioVenta = $dataPrecio[0]["precio"];
        }
        $precioMinimo = $precioVenta * 1 - $dataPrecio[0]["descuento"] * 1;
        $precioMinimo = round($precioMinimo, 2);

        if ($precioUnitario < $precioMinimo) {
          throw new WarningException("No se pudo guardar un detalle del movimiento, precio unitario tiene que ser mayor o igual al precio mínimo (descuento)"
            . "<br> Producto: " . $item["bienDesc"]
            . "<br> Precio mínimo: " . $precioMinimo);
        }
      }
    }
  }
  private function validarItemDetalleMovimientoEdicionR($item, $opcionId, $dataDocumentoTipo = null, $camposDinamicos, $monedaId)
  {
    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    // validaciones
    if ($item["bien_id"] == NULL) {
      throw new WarningException("No se especificó un valor válido para Bien. ");
    }

    if ($item["unidad_medida_id"] == NULL) {
      throw new WarningException("No se especificó un valor válido para Unidad Medida. ");
    }
    if ($item["cantidad"] == NULL or $item["cantidad"] <= 0) {
      throw new WarningException("No se especificó un valor válido para Cantidad. ");
    }

    //obtengo la fecha de emision
    $fechaEmision = null;
    foreach ($camposDinamicos as $valorCampo) {
      if ($valorCampo["tipo"] == 9) {
        $fechaEmision = DateUtil::formatearCadenaACadenaBD($valorCampo["valor"]);
      }
    }

    if (ObjectUtil::isEmpty($item["movimientoBienId"])) {
      MovimientoNegocio::create()->obtenerStockAControlar($opcionId, $item["bienId"], $item["organizadorId"], $item["unidadMedidaId"], $item["cantidad"], $fechaEmision, $item["detalle"]);
    } else {
      //SI ES ENTRADA O SALIDA VALIDA STOCK
      if ($movimientoTipo[0]["indicador"] == MovimientoTipoNegocio::INDICADOR_SALIDA || $movimientoTipo[0]["indicador"] == MovimientoTipoNegocio::INDICADOR_ENTRADA) {
        //ACTUALIZO TEMPORALMENTE EL MOVIMIENTO BIEN A INACTIVO
        $resEstInac = MovimientoBien::create()->actualizarEstadoXId($item["movimientoBienId"], 0);

        MovimientoNegocio::create()->obtenerStockAControlar($opcionId, $item["bienId"], $item["organizadorId"], $item["unidadMedidaId"], $item["cantidad"], $fechaEmision, $item["detalle"], true);

        //ACTUALIZO MOVIMIENTO BIEN A ACTIVO
        $resEstAc = MovimientoBien::create()->actualizarEstadoXId($item["movimientoBienId"], 1);
      }
    }

    //validacion el precio unitario tiene que ser mayor al precio de compra.
    $precioCompra = 0;
    $validarPrecios = true;
    //        if ($item["precio"] * 1 == 0) {
    //            $validarPrecios = false;
    //        }

    if (!ObjectUtil::isEmpty($item["precioCompra"])) {
      $precioCompra = $item["precioCompra"];
    }

    // Array que los contiene id de los tipos de documentos a los que no debe figurar la validacion
    $noValidarPrecioXDocTipoId = [23];
    $realizarValidacionPrecio = array_search($dataDocumentoTipo[0]["id"], $noValidarPrecioXDocTipoId);

    if (!ObjectUtil::isEmpty($dataDocumentoTipo) && $dataDocumentoTipo[0]["validacion"] == 1 && $validarPrecios && $realizarValidacionPrecio) {
      $precioUnitario = $item["precio"];
      if ($precioUnitario <= $precioCompra) {
        throw new WarningException(
          "No se pudo guardar un detalle del movimiento, precio unitario tiene que ser mayor al precio de compra.<br>"
            . "Producto: " . $item['bienDesc'] . '<br>'
            . "Precio compra: " . number_format($precioCompra, 2, ".", ",") . '<br>'
            . "Precio unitario: " . number_format($precioUnitario, 2, ".", ",") . '<br>'
        );
      }
    }
    if ($dataDocumentoTipo[0]["tipo"] == 1 && $validarPrecios) {
      $precioUnitario = $item["precio"];

      $dataPrecio = BienPrecioNegocio::create()->obtenerBienPrecioXBienIdXUnidadMedidaIdXPrecioTipoIdXMonedaId($item["bienId"], $item["unidadMedidaId"], $item["precioTipoId"], $monedaId);
      if (!ObjectUtil::isEmpty($dataPrecio)) {
        if ($checkIgv == 1) {
          $precioVenta = $dataPrecio[0]["incluye_igv"];
        } else {
          $precioVenta = $dataPrecio[0]["precio"];
        }
        $precioMinimo = $precioVenta * 1 - $dataPrecio[0]["descuento"] * 1;
        $precioMinimo = round($precioMinimo, 2);

        if ($precioUnitario < $precioMinimo) {
          throw new WarningException("No se pudo guardar un detalle del movimiento, precio unitario tiene que ser mayor o igual al precio mínimo (descuento)"
            . "<br> Producto: " . $item["bienDesc"]
            . "<br> Precio mínimo: " . $precioMinimo);
        }
      }
    }
  }
  public function validarMovimientoBienEdicionEliminar($documentoId, $item)
  {
    $respuesta->exito = 1;

    if (!ObjectUtil::isEmpty($item['movimientoBienId'])) {
      $dataDocumento = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($documentoId);

      //ORDEN DE VENTA
      if ($dataDocumento[0]['identificador_negocio'] == 2) {
        $cantidadAtendida = ProgramacionAtencionNegocio::create()->obtenerCantidadAtendidaXMovimientoBienId($item['movimientoBienId']);

        if (!ObjectUtil::isEmpty($cantidadAtendida[0]['cantidad_atendida']) && $cantidadAtendida[0]['cantidad_atendida'] > 0) {
          //HAY ATENCION DEL MOVIMIENTO BIEN
          $respuesta->exito = 0;
          $respuesta->mensaje = 'La cantidad atendida del producto es ' . ($cantidadAtendida[0]['cantidad_atendida'] * 1) . ' por ello no se puede eliminar';
        }
      }
    }


    return $respuesta;
  }

  public function obtenerDireccionOrganizador($organizadorId)
  {
    return Organizador::create()->obtenerDireccionOrganizador($organizadorId);
  }

  function consultarTicket($documentoId)
  {

    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);

    $dataEmpresa = EmpresaNegocio::create()->obtenerEmpresaXDocumentoId($documentoId);
    $empresa = EmpresaNegocio::create()->obtenerEmpresaXId($documento[0]['empresa_id']);

    if ($dataEmpresa[0]['efactura'] == 1) {
      $ticket = Movimiento::create()->obtenerNroTicketEFACT($documentoId);
      if (!ObjectUtil::isEmpty($ticket)) {
        $comprobanteElectronico->emisorNroDocumento = $empresa[0]['ruc'];
        $comprobanteElectronico->docNroTicket = $ticket[0]['efact_ticket'];
        $comprobanteElectronico->usuarioSunatSOL = $empresa[0]['usuario_sunat_sol'];
        $comprobanteElectronico->claveSunatSOL = $empresa[0]['clave_sunat_sol'];

        $comprobanteElectronico = (array) $comprobanteElectronico;

        $client = new SoapClient(Configuraciones::EFACT_URL);

        try {
          $resultado = $client->procesarConsultaTicket($comprobanteElectronico)->procesarConsultaTicketResult;
          return $resultado;
        } catch (Exception $e) {
          return $e->getMessage();
        }
      } else {
        return 'Este documento aun no ha sido procesado por el script por favor espere 30 minutos y vuelva a consultar.';
      }
    } else if ($dataEmpresa[0]['efactura'] == 2) {
      $this->consultarTicketNubefact($documentoId, "consultar_anulacion");
    }
  }

  function verificarAnulacionSunat()
  {
    $usuarioId = 1;
    $documentos = Movimiento::create()->obtenerDocumentosPorVerificarAnulacionSunat();

    $documentosCorreo = array();
    foreach ($documentos as $index => $item) {
      $resConsulta = $this->consultarTicket($item['documento_id']);
      //            $resConsulta = 'ERROR PROBANDO';
      if (strpos($resConsulta, '[Cod: IMA01]') === false) {
        //AGREGAR AL ARRAY PARA ENVIAR CORREO
        $item['error_sunat'] = $resConsulta;
        array_push($documentosCorreo, $item);

        //REVERTIR ESTADO
        $documentoEstadoId = 1;
        $resDocEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($item['documento_id'], $documentoEstadoId, $usuarioId);

        $estado = 1;
        $resDoc = DocumentoNegocio::create()->actualizarEstadoXId($item['documento_id'], $estado);
      } else {
        //CORRECTO: ACTUALIZAR ESTADO ANULACION SUNAT
        $resEst = Documento::create()->actualizarEstadoEfactAnulacionValido($item['documento_id'], 1);
      }
    }

    //ENVIAR CORREO
    $resEmailTodos = '';
    if (!ObjectUtil::isEmpty($documentosCorreo)) {
      $documentosGrupo = $this->obtenerDocumentosGrupoXUsuario($documentosCorreo);

      //A EFACT
      $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID(23);
      $correosPlantilla = EmailPlantillaNegocio::create()->obtenerEmailXDestinatario($plantilla[0]["destinatario"], null, null);

      $correos = '';
      foreach ($correosPlantilla as $email) {
        $correos = $correos . $email . ';';
      }

      $descripcion = 'Se restablecieron los siguientes documentos, porque la verificación de la anulación fue incorrecta. Anule los documentos por el sistema.';

      $descripcion = 'El proceso de baja de algún documento no se pudo concretar, según política de SUNAT.<br>
                            Por tal motivo le recomendamos emitir nota de crédito.<br>
                            Asi mismo se restablecieron a un estado activo, los siguientes documentos:';

      foreach ($documentosGrupo as $key => $itemGrupo) {
        $correosEnvio = $correos . $itemGrupo['usuario_email'];
        $resEmail = $this->enviarCorreoDocumentosRevertidosDeAnulacionSunat($itemGrupo['documentos'], $plantilla, $correosEnvio, $descripcion);
        $resEmailTodos = $resEmailTodos . '<br>' . $resEmail;
      }
    }

    return $resEmailTodos;
  }

  function obtenerDocumentosGrupoXUsuario($documentosCorreo)
  {
    $documentosGrupo = array();

    $documentoItem = array();
    foreach ($documentosCorreo as $key => $item) {
      if (!$this->verificarUsuarioExisteEnArray($documentosGrupo, $item['usuario'])) {
        $documentoItem = array('usuario' => $item['usuario'], 'usuario_email' => $item['usuario_email'], 'documentos' => array());

        array_push($documentosGrupo, $documentoItem);
      }
    }

    foreach ($documentosGrupo as $indexGrupo => $itemGrupo) {
      foreach ($documentosCorreo as $index => $item) {
        if ($itemGrupo['usuario'] == $item['usuario']) {
          array_push($documentosGrupo[$indexGrupo]['documentos'], $item);
        }
      }
    }

    return $documentosGrupo;
  }

  function verificarUsuarioExisteEnArray($documentosGrupo, $usuario)
  {
    $bandera = false;

    foreach ($documentosGrupo as $key => $item) {
      if ($item['usuario'] == $usuario) {
        $bandera = true;
      }
    }

    return $bandera;
  }

  function enviarCorreoDocumentosRevertidosDeAnulacionSunat($documentos, $plantilla, $correos, $descripcion)
  {

    if (!ObjectUtil::isEmpty($documentos)) {
      //            $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID(21);
      //            $correosPlantilla = EmailPlantillaNegocio::create()->obtenerEmailXDestinatario($plantilla[0]["destinatario"], null, null);
      //
      //            $correos = '';
      //            foreach ($correosPlantilla as $email) {
      //                $correos = $correos . $email . ';';
      //            }
      //dibujando la cabecera
      $dataDetalle = '<table  border=1 cellspacing=0 cellpadding=5 bordercolor="000000"  width="91.7%">
                        <thead>';

      $html = '<tr>';
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Tipo de documento</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Cliente</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>S/N</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Importe</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>F.Emisión</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Motivo anulación</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>F.Anulación</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Error SUNAT</th>";
      $html = $html . '</tr>';

      $dataDetalle = $dataDetalle . $html;
      $dataDetalle = $dataDetalle . '<thead>';
      $dataDetalle = $dataDetalle . '<tbody>';

      foreach ($documentos as $index => $item) {
        $html = '<tr>';
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['documento_tipo_descripcion'];
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['persona_descripcion'];
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['serie_numero'];
        $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . $item['moneda_simbolo'] . '&nbsp;' . number_format($item['total'], 2, ".", ",");
        $html = $html . '<td style=\'text-align:center;font-size:14px;line-height:1.5;\'>' . DateUtil::formatearFechaBDAaCadenaVw($item['fecha_emision']);
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['motivo_anulacion'];
        $html = $html . '<td style=\'text-align:center;font-size:14px;line-height:1.5;\'>' . DateUtil::formatearFechaBDAaCadenaVw($item['anulacion_fecha']);
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['error_sunat'];
        $html = $html . '</tr>';

        $dataDetalle = $dataDetalle . $html;
      }

      $dataDetalle = $dataDetalle . '</tbody></table>';
      //            $descripcion = 'Se restablecieron los siguientes documentos, porque la verificación de la anulación fue incorrecta. Anule los documentos por el sistema.';
      //logica correo:
      $asunto = '[EFACT] ' . $plantilla[0]["asunto"];
      $cuerpo = $plantilla[0]["cuerpo"];

      $cuerpo = str_replace("[|titulo_email|]", 'DOCUMENTOS RESTABLECIDOS PENDIENTES DE ANULACION', $cuerpo);
      $cuerpo = str_replace("[|descripcion|]", $descripcion, $cuerpo);
      $cuerpo = str_replace("[|documento_detalle|]", $dataDetalle, $cuerpo);

      $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correos, $asunto, $cuerpo, 1, 1);
      //            return $cuerpo;
      return 'Documentos restablecidos. ' . $res[0]['vout_mensaje'] . ' Id: ' . $res[0]['id'];
    } else {
      return '';
    }
  }

  public function generarDocumentosElectronicosPendientes()
  {
    $contadorMaximoRegistro = Configuraciones::EFACT_WS_CONTADOR_MAXIMO;

    $usuarioId = 1;
    $docPendientes = Documento::create()->obtenerDocumentosPendientesDeGeneracionEfact($contadorMaximoRegistro);

    $docCorrectos = array();
    $docErrorControlado = array();
    $docErrorDesconocido = array();

    foreach ($docPendientes as $index => $item) {
      $resValido = $this->consultarDocumentoSUNAT($item['documento_id']);

      //DES COMENTAR PARA PRUEBAS EN BETA:
      $resValido->tipoRespuesta = 0;
      $resValido->mensaje = 'Documento no existe';

      if ($resValido->tipoRespuesta == 1) {
        //GENERO SOLO PDF
        $resEfact = MovimientoNegocio::create()->generarDocumentoElectronico($item['documento_id'], $item['identificador_negocio'], 1, 2);
      } else {
        //GENERAR DOCUMENTO ELECTRONICO
        $resEfact = MovimientoNegocio::create()->generarDocumentoElectronico($item['documento_id'], $item['identificador_negocio'], 0, 2);
      }

      $respDocElectronico = $resEfact->respDocElectronico;

      if ($respDocElectronico->tipoMensaje == 1) {
        $item['url_PDF'] = $respDocElectronico->urlPDF;
        $item['nombre_PDF'] = $respDocElectronico->nombrePDF;

        array_push($docCorrectos, $item);
      }
      if ($respDocElectronico->tipoMensaje == 2) {
        $item['efact_mensaje_respuesta'] = $respDocElectronico->mensaje;

        array_push($docErrorControlado, $item);

        $documentoEstadoId = 5; //ESTADO ELIMINADO
        $resDocEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($item['documento_id'], $documentoEstadoId, $usuarioId);

        $estado = 2; //DOCUMENTO ELIMINADO
        $resDoc = DocumentoNegocio::create()->actualizarEstadoXId($item['documento_id'], $estado);
      }
      if ($respDocElectronico->tipoMensaje == 3) {
        if (($item['efact_ws_contador'] * 1 + 1) == $contadorMaximoRegistro) {
          $item['efact_mensaje_respuesta'] = trim($respDocElectronico->mensaje);
          array_push($docErrorDesconocido, $item);
        }

        $resActContador = Documento::create()->actualizarEfactContadorRegistro($item['documento_id']);
      }

      //            var_dump($resEfact);
    }

    //        return $docPendientes;
    //ENVIAR CORREO
    $resEmailTodos = '';
    if (!ObjectUtil::isEmpty($docPendientes)) {
      //A EFACT
      $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID(24);
      $correosPlantilla = EmailPlantillaNegocio::create()->obtenerEmailXDestinatario($plantilla[0]["destinatario"], null, null);

      $correos = '';
      foreach ($correosPlantilla as $email) {
        $correos = $correos . $email . ';';
      }
    }

    //ENVIAR CORRECTOS
    if (!ObjectUtil::isEmpty($docCorrectos)) {
      $documentosGrupo = $this->obtenerDocumentosGrupoXUsuario($docCorrectos);
      $descripcion = 'Los siguientes documentos, fueron registrados en SUNAT correctamente';
      $tituloEmail = 'DOCUMENTOS GENERADOS CORRECTAMENTE - SUNAT';
      $asuntoEmail = '[EFACT] Documentos generados correctamente - SUNAT';

      foreach ($documentosGrupo as $key => $itemGrupo) {
        $correosEnvio = $correos . $itemGrupo['usuario_email'];
        $resEmail = $this->enviarCorreoDocumentosGeneradosSunat(DocumentoTipoNegocio::EFACT_CORRECTO, $itemGrupo['documentos'], $plantilla, $correosEnvio, $descripcion, $tituloEmail, $asuntoEmail);
        $resEmailTodos = $resEmailTodos . '<br>' . $resEmail;
      }
    }

    //ENVIAR CON ERROR CONTROLADO
    if (!ObjectUtil::isEmpty($docErrorControlado)) {
      $documentosGrupo = $this->obtenerDocumentosGrupoXUsuario($docErrorControlado);
      $descripcion = 'Los siguientes documentos, no se pudieron registrar en SUNAT, devolvieron un error controlado, se eliminaron del sistema, tiene que volver a registrar.';
      $tituloEmail = 'DOCUMENTOS NO GENERADOS CORRECTAMENTE - SUNAT';
      $asuntoEmail = '[EFACT] Documentos no generados correctamente - SUNAT - Eliminados del sistema';

      foreach ($documentosGrupo as $key => $itemGrupo) {
        $correosEnvio = $correos . $itemGrupo['usuario_email'];
        $resEmail = $this->enviarCorreoDocumentosGeneradosSunat(DocumentoTipoNegocio::EFACT_ERROR_CONTROLADO, $itemGrupo['documentos'], $plantilla, $correosEnvio, $descripcion, $tituloEmail, $asuntoEmail);
        $resEmailTodos = $resEmailTodos . '<br>' . $resEmail;
      }
    }

    //ENVIAR CON ERROR DESCONOCIDO
    if (!ObjectUtil::isEmpty($docErrorDesconocido)) {
      $documentosGrupo = $this->obtenerDocumentosGrupoXUsuario($docErrorDesconocido);
      $descripcion = 'Los siguientes documentos, no se pudieron registrar en SUNAT, devolvieron un error, revise los documentos. Se intentó registrar ' . $contadorMaximoRegistro . ' veces';
      $tituloEmail = 'DOCUMENTOS NO GENERADOS CORRECTAMENTE - SUNAT';
      $asuntoEmail = '[EFACT] Documentos no generados correctamente - SUNAT';

      foreach ($documentosGrupo as $key => $itemGrupo) {
        $correosEnvio = $correos . $itemGrupo['usuario_email'];
        $resEmail = $this->enviarCorreoDocumentosGeneradosSunat(DocumentoTipoNegocio::EFACT_ERROR_DESCONOCIDO, $itemGrupo['documentos'], $plantilla, $correosEnvio, $descripcion, $tituloEmail, $asuntoEmail);
        $resEmailTodos = $resEmailTodos . '<br>' . $resEmail;
      }
    }


    return $resEmailTodos;
  }

  function enviarCorreoDocumentosGeneradosSunat($tipoRespuesta, $documentos, $plantilla, $correos, $descripcion, $tituloEmail, $asuntoEmail)
  {

    if (!ObjectUtil::isEmpty($documentos)) {
      //dibujando la cabecera
      $dataDetalle = '<table  border=1 cellspacing=0 cellpadding=5 bordercolor="000000"  width="91.7%">
                        <thead>';

      $html = '<tr>';
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Tipo de documento</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Cliente</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>S/N</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Importe</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>F.Emisión</th>";
      $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>F.Creación</th>";

      if ($tipoRespuesta == DocumentoTipoNegocio::EFACT_CORRECTO) {
        $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>PDF</th>";
      }
      if (
        $tipoRespuesta == DocumentoTipoNegocio::EFACT_ERROR_CONTROLADO ||
        $tipoRespuesta == DocumentoTipoNegocio::EFACT_ERROR_DESCONOCIDO
      ) {
        $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Respuesta error</th>";
      }

      $html = $html . '</tr>';

      $dataDetalle = $dataDetalle . $html;
      $dataDetalle = $dataDetalle . '<thead>';
      $dataDetalle = $dataDetalle . '<tbody>';

      foreach ($documentos as $index => $item) {
        $html = '<tr>';
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['documento_tipo_descripcion'];
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['persona_descripcion'];
        $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['serie_numero'];
        $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . $item['moneda_simbolo'] . '&nbsp;' . number_format($item['total'], 2, ".", ",");
        $html = $html . '<td style=\'text-align:center;font-size:14px;line-height:1.5;\'>' . DateUtil::formatearFechaBDAaCadenaVw($item['fecha_emision']);
        $html = $html . '<td style=\'text-align:center;font-size:14px;line-height:1.5;\'>' . DateUtil::formatearFechaBDAaCadenaVw($item['fecha_creacion']);

        if ($tipoRespuesta == DocumentoTipoNegocio::EFACT_CORRECTO) {
          $urlPdfSgi = Configuraciones::url_base() . 'pdf2.php?url_pdf=' . $item['url_PDF'] . '&nombre_pdf=' . $item['nombre_PDF'];
          $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . '<a href="' . $urlPdfSgi . '" target="_blank">Descargar</a>';
        }
        if (
          $tipoRespuesta == DocumentoTipoNegocio::EFACT_ERROR_CONTROLADO ||
          $tipoRespuesta == DocumentoTipoNegocio::EFACT_ERROR_DESCONOCIDO
        ) {
          $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['efact_mensaje_respuesta'];
        }

        $html = $html . '</tr>';

        $dataDetalle = $dataDetalle . $html;
      }

      $dataDetalle = $dataDetalle . '</tbody></table>';

      $asunto = $asuntoEmail;
      $cuerpo = $plantilla[0]["cuerpo"];

      $cuerpo = str_replace("[|titulo_email|]", $tituloEmail, $cuerpo);
      $cuerpo = str_replace("[|descripcion|]", $descripcion, $cuerpo);
      $cuerpo = str_replace("[|documento_detalle|]", $dataDetalle, $cuerpo);

      $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correos, $asunto, $cuerpo, 1, 1);
      //            return $cuerpo;
      return 'Respuesta tipo: ' . $tipoRespuesta . '. ' . $res[0]['vout_mensaje'] . ' Id: ' . $res[0]['id'];
    } else {
      return '';
    }
  }

  public function consultarDocumentoSUNAT($documentoId)
  {

    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }

    $empresa = EmpresaNegocio::create()->obtenerEmpresaXId($documento[0]['empresa_id']);

    $comprobanteElectronico->emisorNroDocumento = $empresa[0]['ruc'];
    //        $comprobanteElectronico->emisorNroDocumento = '20531807520';
    $comprobanteElectronico->docTipoDocumento = $documento[0]['sunat_tipo_doc_rel'];
    $comprobanteElectronico->docSerie = $documento[0]["serie"];
    $comprobanteElectronico->docNumero = $documento[0]["numero"];

    $comprobanteElectronico->usuarioSunatSOL = $empresa[0]['usuario_sunat_sol'];
    $comprobanteElectronico->claveSunatSOL = $empresa[0]['clave_sunat_sol'];
    //        $comprobanteElectronico->usuarioSunatSOL = 'MARESTA2';
    //        $comprobanteElectronico->claveSunatSOL = 'Maresta2018';

    $comprobanteElectronico = (array) $comprobanteElectronico;

    //        var_dump($comprobanteElectronico);

    try {
      $client = new SoapClient(Configuraciones::EFACT_URL);
      $resultado = $client->procesarConsultaDocumento($comprobanteElectronico)->procesarConsultaDocumentoResult;
    } catch (Exception $e) {
      $resultado = $e->getMessage();
    }

    if (strpos($resultado, '[Cod: IMA01]') === false) {
      $tipoRespuesta = 0;
    } else {
      $tipoRespuesta = 1;
    }

    $respuesta->tipoRespuesta = $tipoRespuesta;
    $respuesta->mensaje = $resultado;

    return $respuesta;
  }

  public function insertarListaComprobacion($documentoId, $descripcion, $orden, $estado)
  {
    Movimiento::create()->insertarListaComprobacion($documentoId, $descripcion, $orden, $estado);
    return DocumentoNegocio::create()->obtenerListaComprobacion($documentoId);
  }

  public function editarEstadoListaComprobacion($documentoId, $documentoListaId, $estado)
  {
    Movimiento::create()->editarEstadoListaComprobacion($documentoListaId, $estado);
    return DocumentoNegocio::create()->obtenerListaComprobacion($documentoId);
  }

  public function ordenarArribaEstadoListaComprobacion($documentoId, $documentoListaIdActual, $documentoListaIdSiguiente, $ordenActual, $ordenSiguiente)
  {
    Movimiento::create()->ordenarArribaEstadoListaComprobacion($documentoListaIdActual, $documentoListaIdSiguiente, $ordenActual, $ordenSiguiente);
    return DocumentoNegocio::create()->obtenerListaComprobacion($documentoId);
  }

  public function obtenerhistoricoAcciones()
  {
    return Movimiento::create()->obtenerhistoricoAcciones();
  }

  public function obtenerHistoricoAccionXId($id)
  {
    return Movimiento::create()->obtenerHistoricoAccionXId($id);
  }

  public function insertarDocumentoHistorico($documentoId, $idAccion, $valoresjson, $usuarioId, $tipo = null)
  {
    return Movimiento::create()->insertarDocumentoHistorico($documentoId, $idAccion, $valoresjson, $usuarioId, $tipo);
  }

  public function obtenerNumeroAutoXDocumentoTipo($documentoTipoId, $serie = NULL)
  {
    return DocumentoNegocio::create()->obtenerNumeroAutoXDocumentoTipo($documentoTipoId, $serie);
  }

  public function aprobarCotizacion($documentoId, $usuarioId)
  {
    return DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 1, $usuarioId);
  }

  public function conexionEFAC()
  {
    try {
      $client = new SoapClient(Configuraciones::EFACT_URL);
    } catch (Exception $e) {
      //            $resultado = $e->getMessage();
      throw new WarningException("Imposible conectarse con el servicio facturador.");
    }
    return $client;
  }

  public function autogenerarNCTipo13XFacturaId($documentoId, $usuarioId)
  {

    $docConfiguracionNC = DocumentoTipoNegocio::create()->obtenerDocumentoTipoNC(5, 2);
    $opcionNCId = $docConfiguracionNC[0]['opcion_id'];
    $documentoTipoNCId = $docConfiguracionNC[0]['documento_tipo_id'];
    $movimientoTipoNCId = $docConfiguracionNC[0]['movimiento_tipo_id'];
    /** @var Countable|array */
    $documento_tipo_conf = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDato($documentoTipoNCId, $usuarioId);
    $documentoARelacionarNC[0]['documentoId'] = $documentoId;

    if (!ObjectUtil::isEmpty($documento_tipo_conf)) {
      for ($i = 0; $i < count($documento_tipo_conf); $i++) {
        // SERIE
        if ($documento_tipo_conf[$i]['tipo'] == 7) {
          $idenSerie = $documento_tipo_conf[$i]['id'];
          $valorSerie = $documento_tipo_conf[$i]['cadena_defecto'];
          continue;
        }
        //  NUMERO
        if ($documento_tipo_conf[$i]['tipo'] == 8) {
          $idenNumero = $documento_tipo_conf[$i]['id'];
          $valorNumero = $documento_tipo_conf[$i]['data'];
          continue;
        }
        //  CLIENTE
        if ($documento_tipo_conf[$i]['tipo'] == 5) {
          $idenPersona = $documento_tipo_conf[$i]['id'];
          $descPersona = $documento_tipo_conf[$i]['descripcion'];
          continue;
        }
        //  FECHA EMISION
        if ($documento_tipo_conf[$i]['tipo'] == 9) {
          $idenFecha = $documento_tipo_conf[$i]['id'];
          $valorFecha = $documento_tipo_conf[$i]['data'];
          continue;
        }
        //  FECHA VENCIMIENTO
        if ($documento_tipo_conf[$i]['tipo'] == 10) {
          $idenFechaVenc = $documento_tipo_conf[$i]['id'];
          $valorFechaVenc = $documento_tipo_conf[$i]['data'];
          continue;
        }
        //  RETENCION
        if ($documento_tipo_conf[$i]['tipo'] == 4 && $documento_tipo_conf[$i]['codigo'] == 10) {
          $idenRentencion = $documento_tipo_conf[$i]['id'];
          $descRetencion = $documento_tipo_conf[$i]['descripcion'];
          continue;
        }
        //  MOTIVO EMISION
        $doctipoTipo = $documento_tipo_conf[$i]['tipo'];
        $docTipoCodigo = $documento_tipo_conf[$i]['codigo'];
        if ($doctipoTipo == 4 && ObjectUtil::isEmpty($docTipoCodigo)) {
          $idenMotivo = $documento_tipo_conf[$i]['id'];
          $descMotivo = $documento_tipo_conf[$i]['descripcion'];
          continue;
        }
        //  PROYECTO
        if ($documento_tipo_conf[$i]['tipo'] == 2) {
          $idenProyecto = $documento_tipo_conf[$i]['id'];
          $descProyecto = $documento_tipo_conf[$i]['descripcion'];
          continue;
        }
        //  DETRACCION
        if ($documento_tipo_conf[$i]['tipo'] == 36) {
          $idenDetraccion = $documento_tipo_conf[$i]['id'];
          $descDetraccion = $documento_tipo_conf[$i]['descripcion'];
          continue;
        }
        //  Importe
        if ($documento_tipo_conf[$i]['tipo'] == 14) {
          $idenImporte = $documento_tipo_conf[$i]['id'];
          $descImporte = $documento_tipo_conf[$i]['descripcion'];
          continue;
        }
        //  Sub total
        if ($documento_tipo_conf[$i]['tipo'] == 16) {
          $idenSubTotal = $documento_tipo_conf[$i]['id'];
          $descSubTotal = $documento_tipo_conf[$i]['descripcion'];
          continue;
        }
        //  IGV
        if ($documento_tipo_conf[$i]['tipo'] == 15) {
          $idenIGV = $documento_tipo_conf[$i]['id'];
          $descIGV = $documento_tipo_conf[$i]['descripcion'];
          continue;
        }
        //  Forma de pago
        if ($documento_tipo_conf[$i]['tipo'] == 12) {
          $idenFormaPago = $documento_tipo_conf[$i]['id'];
          $descFormaPago = $documento_tipo_conf[$i]['descripcion'];
          continue;
        }
      }
    }
    //        obtenemos los datos de la factura para generar la NC
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    $valorPersona = $documento[0]["persona_id"];
    $dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($documentoId);
    $monedaId = $documento[0]["moneda_id"];
    $tipoPago = $documento[0]["tipo_pago"];
    $empresaId = $documento[0]["empresa_id"];
    foreach ($dataDocumento as $index => $item) {
      switch ($item['documento_tipo_id'] * 1) {
        case 2906:
          if ($item['valor'] == 378) { //Si aplica retencion
            $retencionFact = 380;
          } else {
            $retencionFact = 379;
          }
          break;
        case 2935:
          $proyectoFact = $item['valor'];
          break;
        case 2931:
          $detraccionFact = $item['valor'];
          break;
        case 2929:
          $prodDuplicadoFact = $item['valor'];
          break;
      }
    }

    //INICIO DE CABECERA
    $k = 0;
    $camposDinamicosNC[$k]['id'] = $idenSerie;
    $camposDinamicosNC[$k]['tipo'] = 7;
    $camposDinamicosNC[$k]['descripcion'] = "Serie";
    $camposDinamicosNC[$k]['valor'] = $valorSerie;
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenNumero;
    $camposDinamicosNC[$k]['tipo'] = 8;
    $camposDinamicosNC[$k]['descripcion'] = "Número";
    $camposDinamicosNC[$k]['valor'] = $valorNumero;
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenFecha;
    $camposDinamicosNC[$k]['tipo'] = 9;
    $camposDinamicosNC[$k]['descripcion'] = "Fecha";
    $camposDinamicosNC[$k]['valor'] = date("d/m/Y");
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenFechaVenc;
    $camposDinamicosNC[$k]['tipo'] = 10;
    $camposDinamicosNC[$k]['descripcion'] = "Fecha de vencimiento";
    $camposDinamicosNC[$k]['valor'] = date("d/m/Y", strtotime(date("Ymd") . "+ 1 days"));
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenPersona;
    $camposDinamicosNC[$k]['tipo'] = 5;
    $camposDinamicosNC[$k]['descripcion'] = $descPersona;
    $camposDinamicosNC[$k]['valor'] = $valorPersona;
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenMotivo;
    $camposDinamicosNC[$k]['tipo'] = 4;
    $camposDinamicosNC[$k]['descripcion'] = $descMotivo;
    $camposDinamicosNC[$k]['valor'] = 381; //tipo 13
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenRentencion;
    $camposDinamicosNC[$k]['tipo'] = 4;
    $camposDinamicosNC[$k]['descripcion'] = $descRetencion;
    $camposDinamicosNC[$k]['valor'] = $retencionFact;
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenDetraccion;
    $camposDinamicosNC[$k]['tipo'] = 36;
    $camposDinamicosNC[$k]['descripcion'] = $descDetraccion;
    $camposDinamicosNC[$k]['valor'] = ObjectUtil::isEmpty($detraccionFact) ? '' : $detraccionFact;
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenProyecto;
    $camposDinamicosNC[$k]['tipo'] = 2;
    $camposDinamicosNC[$k]['descripcion'] = $descProyecto;
    $camposDinamicosNC[$k]['valor'] = ObjectUtil::isEmpty($proyectoFact) ? '' : $proyectoFact;
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenImporte;
    $camposDinamicosNC[$k]['tipo'] = 14;
    $camposDinamicosNC[$k]['descripcion'] = $descImporte;
    $camposDinamicosNC[$k]['valor'] = 0;
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenSubTotal;
    $camposDinamicosNC[$k]['tipo'] = 16;
    $camposDinamicosNC[$k]['descripcion'] = $descSubTotal;
    $camposDinamicosNC[$k]['valor'] = 0;
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenIGV;
    $camposDinamicosNC[$k]['tipo'] = 15;
    $camposDinamicosNC[$k]['descripcion'] = $descIGV;
    $camposDinamicosNC[$k]['valor'] = 0;
    $k++;
    $camposDinamicosNC[$k]['id'] = $idenFormaPago;
    $camposDinamicosNC[$k]['tipo'] = 12;
    $camposDinamicosNC[$k]['descripcion'] = $descFormaPago;
    $k++;
    $camposDinamicosNC[$k]['id'] = 2930;
    $camposDinamicosNC[$k]['tipo'] = 32;
    $camposDinamicosNC[$k]['descripcion'] = "Producto duplicado";
    $camposDinamicosNC[$k]['valor'] = $prodDuplicadoFact;

    $comentario = 'Nota de credito generada para regularizar fechas de pago';

    $anio = date("Y");
    $mes = date("m");
    $dataPeriodo = Periodo::create()->obtenerPeriodoXEmpresaXAnioXMes($empresaId, $anio, $mes);
    $periodoId = $dataPeriodo[0]['id'];
    $valorCheck = 1;
    $accionEnvio = "guardar";
    //FIN DE CABECERA
    //Datos Extras
    $datosExtras['afecto_detraccion_retencion'] = $documento[0]["afecto_detraccion_retencion"];
    $datosExtras['porcentaje_afecto'] = $documento[0]["porcentaje_afecto"];
    $datosExtras['monto_detraccion_retencion'] = $documento[0]["monto_detraccion_retencion"];

    //Programacion de pago
    $formaPagoDetalle = PagoNegocio::create()->obtenerPagoProgramacionXDocumentoId($documentoId);

    $listaPagoProgramacion[0][0] = date("d/m/Y", strtotime(date("Ymd") . "+ 1 days"));
    $listaPagoProgramacion[0][1] = $formaPagoDetalle[0]['importe'];
    $listaPagoProgramacion[0][2] = $formaPagoDetalle[0]['dias'];
    $listaPagoProgramacion[0][3] = $formaPagoDetalle[0]['porcentaje'];
    $listaPagoProgramacion[0][4] = '';

    // DETALLE
    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($documento[0]["movimiento_id"]);
    foreach ($documentoDetalle as $index => $fila) {

      $items[$index]['organizadorDesc'] = '';
      $items[$index]['unidadMedidaId'] = $fila['unidad_medida_id'];
      $items[$index]['index'] = $index;
      $items[$index]['stockBien'] = 0;
      $items[$index]['bienDesc'] = $fila['bien_descripcion'];
      $items[$index]['cantidad'] = $fila['cantidad'];
      $items[$index]['subTotal'] = $fila['cantidad'];
      $items[$index]['precio'] = $fila['valor_monetario'];
      $items[$index]['precioTipoId'] = $fila['precio_tipo_id'];
      $items[$index]['bienId'] = $fila['bien_id'];
      $items[$index]['unidadMedidaDesc'] = $fila['unidad_medida_descripcion'];
    }
    $detalle = $items;
    //guardarXAccionEnvio($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $accionEnvio, $tipoPago, $listaPagoProgramacion, $atiende = null, $periodoId = null, $percepcion = null, $datosExtras = null)
    $docNCId = $this->guardarXAccionEnvio($opcionNCId, $usuarioId, $documentoTipoNCId, $camposDinamicosNC, $detalle, $documentoARelacionarNC, $valorCheck, $comentario, 0, $monedaId, $accionEnvio, $tipoPago, $listaPagoProgramacion, null, $periodoId, null, $datosExtras);
    $dataEmpresa = EmpresaNegocio::create()->obtenerEmpresaXDocumentoId($docNCId->documentoId);
    if ($dataEmpresa[0]['efactura'] == 1 || $dataEmpresa[0]['efactura'] == 2) {
      $resEfact = $this->generarDocumentoElectronico($docNCId->documentoId, 5);
      $respuesta->resEfact = $resEfact;
      $mensaje = $resEfact->respDocElectronico->mensaje;
      return $mensaje;
    }
  }

  public function obtenerReporteDocumentosAsignaciones($documentoId) {}

  //IMPLEMENTACIÓN NUBEFACT
  public function envioNubefact($data_json)
  {
    try {
      //Invocamos el servicio de NUBEFACT
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, Configuraciones::NUBEFACT_API);
      curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
          'Authorization: Token token="' . Configuraciones::NUBEFACT_TOKEN . '"',
          'Content-Type: application/json',
        )
      );
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $respuesta  = curl_exec($ch);
      curl_close($ch);
      return $respuesta;
    } catch (Exception $e) {
      $resultado = $e->getMessage();
    }
  }
  public function consultaNubefact($serie, $numero, $tipo_comprobante, $operacion)
  {
    try {
      $comprobanteElectronico = new stdClass();
      $comprobanteElectronico->operacion = $operacion;
      $comprobanteElectronico->tipo_de_comprobante = $tipo_comprobante;
      $comprobanteElectronico->serie = $serie;
      $comprobanteElectronico->numero = $numero;

      $data_json = json_encode($comprobanteElectronico);
      //Invocamos el servicio de NUBEFACT
      $respuesta = $this->envioNubefact($data_json);
      //leer respuesta
      return json_decode($respuesta, true);
    } catch (Exception $e) {
      $resultado = $e->getMessage();
    }
  }

  public function validarResultadoNubefactDocumento($resultado, $documentoId, $idNegocio = null)
  {
    $mensaje = '';
    $urlPDF = '';
    $archivo_enlace = '';
    if (!ObjectUtil::isEmpty($resultado['errors'])) {
      $tipoMensaje = DocumentoTipoNegocio::EFACT_ERROR_DESCONOCIDO;
      $mensaje = "Se generó un error al registrar el documento electrónico. Resultado : " . "código " . $resultado['codigo'] . " - " . $resultado['errors'];
    } else {
      if ($resultado['aceptada_por_sunat']) {
        $tipoMensaje = DocumentoTipoNegocio::EFACT_CORRECTO;
        $mensaje = "Resultado : " . $resultado['sunat_description'];
      } else {
        if (!ObjectUtil::isEmpty($resultado['sunat_description']) && !ObjectUtil::isEmpty($resultado['enlace_del_pdf'])) {
          $tipoMensaje = DocumentoTipoNegocio::EFACT_ERROR_RECHAZADO;
          $mensaje = "Resultado (ERROR): " . $resultado['sunat_description'];
          //CAMBIAR ESTADO ANULADO :
          $resDocEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 4, 1);
        } else {
          $tipoMensaje = DocumentoTipoNegocio::EFACT_PENDIENTE_ENVIO;
          $mensaje = "Resultado : Documento pendiente de envio";
          $resDocEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 10, 1);
        }
      }
    }
    $descargar = 0;
    $resEstadoRegistro = DocumentoNegocio::create()->actualizarEfactEstadoRegistro($documentoId, $tipoMensaje, $mensaje);
    if (!ObjectUtil::isEmpty($resultado['enlace'])) {
      //$url = ObjectUtil::isEmpty($idNegocio) ? Configuraciones::NUBEFACT_CONTENEDOR_PDF : Configuraciones::NUBEFACT_CONTENEDOR_PDF_GUIA;
      $url = $idNegocio != DocumentoTipoNegocio::IN_GUIA_REMISION ? Configuraciones::NUBEFACT_CONTENEDOR_PDF : Configuraciones::NUBEFACT_CONTENEDOR_PDF_GUIA;
      $archivo_enlace = str_replace($url, '', $resultado['enlace']);

      if (!ObjectUtil::isEmpty($archivo_enlace)) {
        $urlPDF = $url . $archivo_enlace . ".pdf";
        $resActNombrePDF = DocumentoNegocio::create()->actualizarEfactPdfNombre($documentoId, $archivo_enlace);
        $descargar = 1;
      } else {
        $urlPDF = '';
      }
    }

    if ($tipoMensaje != DocumentoTipoNegocio::EFACT_CORRECTO) {
      $mensaje = "Se registró correctamente en el SGI, pero se ha presentado un problema en el envió a SUNAT<br>Detalle: " . $mensaje;
      $titulo = ", pendiente de emisión a SUNAT";
    }

    $respEfact = new stdClass();
    $respEfact->tipoMensaje = $tipoMensaje; //[Cod: IMAEX05] |  Error la generar el documento : Comprobante: F001-000349 presenta el error: Se ha especificado un tipo de proveedor no válido.
    $respEfact->mensaje = $mensaje;
    $respEfact->urlPDF = $urlPDF;
    $respEfact->nombrePDF = $archivo_enlace;
    $respEfact->descargar = $descargar;
    $respEfact->titulo = $titulo; //titulo que en caso de reenvio de comprobante  no será nulo
    return $respEfact;
  }
  public function validarResultadoNubefact($resultado)
  {
    //"{"numero": 1001, "enlace": "https://imaginatec.pse.pe/anulacion/7e31531e-26e8-4ec0-a038-820857976231", "sunat_ticket_numero": "2024013001184238678", "aceptada_por_sunat": false, "sunat_description": null, "sunat_note": null, "sunat_responsecode": null, "sunat_soap_error": null, "pdf_zip_base64": null, "xml_zip_base64": null, "cdr_zip_base64": null, "enlace_del_pdf": "https://imaginatec.pse.pe/anulacion/7e31531e-26e8-4ec0-a038-820857976231.pdf", "enlace_del_xml": "https://imaginatec.pse.pe/anulacion/7e31531e-26e8-4ec0-a038-820857976231.xml", "enlace_del_cdr": "", "key": "7e31531e-26e8-4ec0-a038-820857976231"}"

    if (!ObjectUtil::isEmpty($resultado['errors'])) {
      throw new WarningException("Se generó un error al registrar el documento electrónico. " . "código " . $resultado['codigo'] . " - " . $resultado['errors']);
    } else if (!ObjectUtil::isEmpty($resultado['sunat_ticket_numero'])) {
      $this->setMensajeEmergente("Resultado: La Comunicación de Baja ha sido ACEPTADA, número de TICKET (SUNAT): " . $resultado['sunat_ticket_numero']);
    } else {
      throw new WarningException("Se generó un error al registrar el documento electrónico.");
    }
  }
  public function generarFacturaElectronicaNubefact($documentoId, $soloPDF, $tipoUso)
  {
    // Obtenemos Datos
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }
    if ($documento[0]["documento_estado"] == "10") {
      $res = $this->consultaNubefact($documento[0]["serie"], $documento[0]["numero"], "1", "consultar_comprobante");
      return $this->validarResultadoNubefactDocumento($res, $documentoId);
    }
    $persona = PersonaNegocio::create()->obtenerPersonaXId($documento[0]["persona_id"]);
    if (ObjectUtil::isEmpty($persona)) {
      throw new WarningException("No se encontró a la persona del documento");
    }
    $ubigeo = PersonaNegocio::create()->obtenerUbigeoXId($documento[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeo)) {
      throw new WarningException("No se especificó el ubigeo del receptor");
    }

    $comprobanteElectronico = new stdClass();
    $comprobanteElectronico->operacion = "generar_comprobante";
    $comprobanteElectronico->tipo_de_comprobante = 1;
    // VALIDA SERIE
    if ($documento[0]["serie"][0] != 'F') {
      throw new WarningException("La serie del documento debe empezar con F");
    }
    $afectoDetraccionRetencion = ($documento[0]["afecto_detraccion_retencion"] * 1); //1 = detracción , 2 = retención

    $comprobanteElectronico->serie = $documento[0]["serie"];
    $comprobanteElectronico->numero = $documento[0]["numero"];
    $comprobanteElectronico->sunat_transaction = $afectoDetraccionRetencion == 1 ? 30 : 1; //revisar
    //Datos de Cliente
    $comprobanteElectronico->cliente_tipo_de_documento = $persona[0]["sunat_tipo_documento"];
    $comprobanteElectronico->cliente_numero_de_documento = $persona[0]["codigo_identificacion"];
    $comprobanteElectronico->cliente_denominacion = $persona[0]["persona_nombre_completo"];
    $comprobanteElectronico->cliente_direccion = $documento[0]["direccion"] . " " . $ubigeo[0]["ubigeo_dist"] . " " . $ubigeo[0]["ubigeo_prov"] . " " . $ubigeo[0]["ubigeo_dep"];
    $correos = PersonaNegocio::create()->obtenerCorreosEFACT();
    $comprobanteElectronico->cliente_email = str_replace(';', '', $persona[0]["email"]);
    $comprobanteElectronico->cliente_email_1 = Configuraciones::EFACT_CORREO;
    $comprobanteElectronico->cliente_email_2 = "";
    //Datos de comprobante
    $comprobanteElectronico->fecha_de_emision = substr($documento[0]["fecha_emision"], 0, 10);
    $comprobanteElectronico->fecha_de_vencimiento = substr($documento[0]['fecha_vencimiento'], 0, 10);
    $comprobanteElectronico->moneda = $documento[0]["sunat_moneda"] == 'PEN' ? 1 : 2;
    $dataTipoCambio = "";
    if ($documento[0]["sunat_moneda"] != 'PEN') {
      $dataTipoCambio = TipoCambioNegocio::create()->obtenerTipoCambioXfecha($documento[0]['fecha_emision']);
    }
    $comprobanteElectronico->tipo_de_cambio = $dataTipoCambio;
    $comprobanteElectronico->porcentaje_de_igv = "18.00"; //revisar
    $comprobanteElectronico->descuento_global = "";
    $comprobanteElectronico->total_descuento = "";
    $comprobanteElectronico->total_anticipo = "";
    $comprobanteElectronico->total_gravada = $documento[0]["subtotal"] * 1.0;
    $comprobanteElectronico->total_inafecta = "";
    $comprobanteElectronico->total_igv = $documento[0]["igv"] * 1.0;
    $montoGratuito = (!ObjectUtil::isEmpty($this->docInafectas)) ? $this->docInafectas * 1 : 0.0;
    $comprobanteElectronico->total_gratuita = $montoGratuito;
    $comprobanteElectronico->total_otros_cargos = "";
    $comprobanteElectronico->total = $documento[0]["total"] * 1.0; //total
    $comprobanteElectronico->percepcion_tipo = "";
    $comprobanteElectronico->percepcion_base_imponible = "";
    $comprobanteElectronico->total_percepcion = "";
    $comprobanteElectronico->total_incluido_percepcion = "";

    if ($afectoDetraccionRetencion == 2) {
      $porcentajeDetraccionRetencion = ($documento[0]["porcentaje_afecto"] * 1);
      $comprobanteElectronico->retencion_tipo = $porcentajeDetraccionRetencion == 3 ? 1 : 2;
      $comprobanteElectronico->retencion_base_imponible = $comprobanteElectronico->docTotalVenta; //revisar
      $comprobanteElectronico->total_retencion = ($documento[0]["monto_detraccion_retencion"] * 1); //revisar
    }

    $comprobanteElectronico->total_impuestos_bolsas = ""; //revisar
    if ($afectoDetraccionRetencion == 1) {
      $comprobanteElectronico->detraccion = $afectoDetraccionRetencion == 1 ? true : false; //revisar
      $comprobanteElectronico->detraccion_tipo = $documento[0]["detraccion_codigo_codigo_nubefact"];
      $comprobanteElectronico->detraccion_total = ($documento[0]["monto_detraccion_retencion"] * 1);
      $comprobanteElectronico->detraccion_porcentaje = ($documento[0]["porcentaje_afecto"] * 1);
    }
    $comprobanteElectronico->observaciones = $documento[0]['comentario'];
    /*$comprobanteElectronico->documento_que_se_modifica_tipo = "";
    $comprobanteElectronico->documento_que_se_modifica_serie = "";
    $comprobanteElectronico->documento_que_se_modifica_numero = "";
    $comprobanteElectronico->tipo_de_nota_de_credito = "";
    $comprobanteElectronico->tipo_de_nota_de_debito = "";*/
    $comprobanteElectronico->enviar_automaticamente_a_la_sunat = true;
    $comprobanteElectronico->enviar_automaticamente_al_cliente = false;
    //$comprobanteElectronico->condiciones_de_pago = "";//revisar
    $tipoPago = ($documento[0]["tipo_pago"] * 1);
    if ($tipoPago == 2) {
      $comprobanteElectronico->medio_de_pago = "credito"; //revisar
    }
    $orden = '';
    $docOrdenCompra = DocumentoNegocio::create()->obtenerDocumentoRelacionadoImpresion($documentoId);
    foreach ($docOrdenCompra as $index => $ordenCompra) {
      switch ($ordenCompra['identificador_negocio'] * 1) {
        case 2: {
            $orden = $ordenCompra['serie_numero_original'];
            if (!ObjectUtil::isEmpty($orden)) {
              $ordenArray = explode("-", $orden);
              if (count($ordenArray) > 1 && ObjectUtil::isEmpty(trim($ordenArray[0]))) {
                $orden = trim($ordenArray[1]);
              } elseif (count($ordenArray) == 1) {
                $orden = trim($ordenArray[0]);
              }
            }
          }
      }
    }
    $comprobanteElectronico->orden_compra_servicio = $orden;
    $comprobanteElectronico->placa_vehiculo = "";
    $comprobanteElectronico->formato_de_pdf = "";

    //$comprobanteElectronico->docMontoEnLetras = $importeLetras; // $Veintiun Mil Doscientos Veinte Con 96/100 SolesY CINCO SOLES CON 0/100';
    // items
    $new_items = array();
    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($documento[0]["movimiento_id"]);
    foreach ($documentoDetalle as $index => $fila) {
      $tipoPrecio = "01";
      $tipoAfectacion = "1"; // 10 = Gravado - Operación Onerosa cat. Sunat , 1 = Gravado - Operación Onerosa cat. Nubefact
      $valorMonetario = $fila['valor_monetario'] * 1;
      $valorMonetarioRef = $fila['valor_monetario'] * 1;
      if ($fila['incluye_igv'] == 1) {
        $valorMonetario = $fila['valor_monetario'] / 1.18;
      } else {
        $valorMonetarioRef = $fila['valor_monetario'] * (1.18);
      }
      $precio = $valorMonetario;
      $precioReferencial = $valorMonetarioRef;
      $totalItem = $precio * $fila['cantidad'];
      $totalImpuesto = $precio * $fila['cantidad'] * 0.18;
      if ($montoGratuito > 0) {
        $tipoAfectacion = "17"; // 21 = Exonerado - Transferencia gratuita cat. SUnat, 17 = Exonerado - Transferencia Gratuita cat. Nubefact
        $tipoPrecio = "02";
        $precio = 0;
        $totalImpuesto = 0;
        $precioReferencial = $valorMonetario;
        $totalItem = $precioReferencial * $fila['cantidad'];
      }
      $items['unidad_de_medida'] = $fila['sunat_unidad_medida'];
      $items['codigo'] = $fila['bien_codigo'];
      $items['descripcion'] = (!ObjectUtil::isEmpty($fila['bien_descripcion_editada'])) ? $fila['bien_descripcion_editada'] : $fila['bien_descripcion'];
      $items['cantidad'] = $fila['cantidad'] * 1;
      $items['valor_unitario'] = $precio; //sin igv
      $items['precio_unitario'] = $precioReferencial; //con igv
      $items['descuento'] = ""; //revisar
      $items['subtotal'] = $totalItem;
      $items['tipo_de_igv'] = $tipoAfectacion;
      $items['igv'] = $totalImpuesto; //Impuesto
      $items['total'] = ($totalItem + $totalImpuesto);
      $items['anticipo_regularizacion'] = false; //revisar
      $items['anticipo_documento_serie'] = ""; //revisar
      $items['anticipo_documento_numero'] = ""; //revisar

      $new_items[] = $items;
    }
    $comprobanteElectronico->items = $new_items;

    if ($tipoPago == 2) {
      $venta_al_credito = array();
      //$formaPago[] = array("Credito", $montoNetoPago, "");
      $formaPagoDetalle = PagoNegocio::create()->obtenerPagoProgramacionXDocumentoId($documentoId);
      if (ObjectUtil::isEmpty($formaPagoDetalle)) {
        throw new WarningException("Se requiere de la programación de pago de la factura.");
      }

      $arrayFechaVencimiento = array();
      $dias = 0;
      foreach ($formaPagoDetalle as $indexFormaPago => $itemFormaPago) {
        $arrayFechaVencimiento[] = substr($itemFormaPago['fecha_pago'], 0, 10);
        $venta_al_credito['cuota'] = $indexFormaPago + 1;
        $venta_al_credito['fecha_de_pago'] = substr($itemFormaPago['fecha_pago'], 0, 10);
        $venta_al_credito['importe'] = $itemFormaPago['importe'] * 1.0;
        $dias = $dias + $itemFormaPago['dias'];

        $new_venta_al_credito[] = $venta_al_credito;
      }
      $comprobanteElectronico->condiciones_de_pago = "CRÉDITO " . $dias . " DÍAS";
      $fechaMaximaCuota = date("Y-m-d", max(array_map('strtotime', $arrayFechaVencimiento)));
      if (substr($documento[0]['fecha_vencimiento'], 0, 10) != $fechaMaximaCuota) {
        throw new WarningException("La fecha de vencimiento de la factura (" . substr($documento[0]['fecha_vencimiento'], 0, 10) . ") debe ser igual a la última cuota de la programación de pagos ($fechaMaximaCuota)");
      }
    } /*elseif ($tipoPago == 1) {
      $formaPago[] = array("Contado", 0.0, "");
    }*/ else if (ObjectUtil::isEmpty($tipoPago)) {
      throw new WarningException("No se identifica la forma de pago para esta factura.");
    }

    $comprobanteElectronico->venta_al_credito = $new_venta_al_credito;

    $data_json = json_encode($comprobanteElectronico);

    try {
      //Invocamos el servicio de NUBEFACT
      $respuesta = $this->envioNubefact($data_json);
      //leer resultado
      $resultado = json_decode($respuesta, true);
      //$res = $this->consultaNubefact($comprobanteElectronico->serie, $comprobanteElectronico->numero);
      $resEfact = $this->validarResultadoNubefactDocumento($resultado, $documentoId);
      return $resEfact;
    } catch (Exception $e) {
      $resultado = $e->getMessage();
    }
  }

  public function generarBoletaElectronicaNubefact($documentoId, $soloPDF, $tipoUso)
  {
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }
    if ($documento[0]["documento_estado"] == "10") {
      $res = $this->consultaNubefact($documento[0]["serie"], $documento[0]["numero"], "1", "consultar_comprobante");
      return $this->validarResultadoNubefactDocumento($res, $documentoId);
    }
    $persona = PersonaNegocio::create()->obtenerPersonaXId($documento[0]["persona_id"]);
    if (ObjectUtil::isEmpty($persona)) {
      throw new WarningException("No se encontró a la persona del documento");
    }
    $ubigeo = PersonaNegocio::create()->obtenerUbigeoXId($documento[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeo)) {
      throw new WarningException("No se especificó el ubigeo del receptor");
    }

    $comprobanteElectronico = new stdClass();
    $comprobanteElectronico->operacion = "generar_comprobante";
    $comprobanteElectronico->tipo_de_comprobante = 2;
    // VALIDA SERIE
    if ($documento[0]["serie"][0] != 'B') {
      throw new WarningException("La serie del documento debe empezar con B");
    }
    $afectoDetraccionRetencion = ($documento[0]["afecto_detraccion_retencion"] * 1); //1 = detracción , 2 = retención

    $comprobanteElectronico->serie = $documento[0]["serie"];
    $comprobanteElectronico->numero = $documento[0]["numero"];
    $comprobanteElectronico->sunat_transaction = $afectoDetraccionRetencion == 1 ? 30 : 1; //revisar
    //Datos de Cliente
    $comprobanteElectronico->cliente_tipo_de_documento = $persona[0]["sunat_tipo_documento"];
    $comprobanteElectronico->cliente_numero_de_documento = $persona[0]["codigo_identificacion"];
    $comprobanteElectronico->cliente_denominacion = $persona[0]["persona_nombre_completo"];
    $comprobanteElectronico->cliente_direccion = $documento[0]["direccion"] . " " . $ubigeo[0]["ubigeo_dist"] . " " . $ubigeo[0]["ubigeo_prov"] . " " . $ubigeo[0]["ubigeo_dep"];
    $correos = PersonaNegocio::create()->obtenerCorreosEFACT();
    $comprobanteElectronico->cliente_email = str_replace(';', '', $persona[0]["email"]);
    $comprobanteElectronico->cliente_email_1 = Configuraciones::EFACT_CORREO;
    $comprobanteElectronico->cliente_email_2 = "";
    //Datos de comprobante
    $comprobanteElectronico->fecha_de_emision = substr($documento[0]["fecha_emision"], 0, 10);
    $comprobanteElectronico->fecha_de_vencimiento = substr($documento[0]['fecha_vencimiento'], 0, 10);
    $comprobanteElectronico->moneda = $documento[0]["sunat_moneda"] == 'PEN' ? 1 : 2;
    $dataTipoCambio = "";
    if ($documento[0]["sunat_moneda"] != 'PEN') {
      $dataTipoCambio = TipoCambioNegocio::create()->obtenerTipoCambioXfecha($documento[0]['fecha_emision']);
    }
    $comprobanteElectronico->tipo_de_cambio = $dataTipoCambio;
    $comprobanteElectronico->porcentaje_de_igv = Configuraciones::IGV_PORCENTAJE;
    $comprobanteElectronico->descuento_global = "";
    $comprobanteElectronico->total_descuento = "";
    $comprobanteElectronico->total_anticipo = "";
    $docTotalIgv = $documento[0]["total"] * 1 - $documento[0]["total"] / 1.18;
    $docGravadas = $documento[0]["total"] / 1.18;
    $comprobanteElectronico->total_gravada = $docGravadas;
    $comprobanteElectronico->total_inafecta = "";
    $comprobanteElectronico->total_igv = $docTotalIgv;
    $montoGratuito = (!ObjectUtil::isEmpty($this->docInafectas)) ? $this->docInafectas * 1 : 0.0;
    $comprobanteElectronico->total_gratuita = $montoGratuito;
    $comprobanteElectronico->total_otros_cargos = "";
    $comprobanteElectronico->total = $documento[0]["total"] * 1.0; //total
    $comprobanteElectronico->percepcion_tipo = "";
    $comprobanteElectronico->percepcion_base_imponible = "";
    $comprobanteElectronico->total_percepcion = "";
    $comprobanteElectronico->total_incluido_percepcion = "";

    if ($afectoDetraccionRetencion == 2) {
      $porcentajeDetraccionRetencion = ($documento[0]["porcentaje_afecto"] * 1);
      $comprobanteElectronico->retencion_tipo = $porcentajeDetraccionRetencion == 3 ? 1 : 2;
      $comprobanteElectronico->retencion_base_imponible = $comprobanteElectronico->docTotalVenta; //revisar
      $comprobanteElectronico->total_retencion = ($documento[0]["monto_detraccion_retencion"] * 1); //revisar
    }
    $codigoDetraccion = $documento[0]["detraccion_codigo"];

    $comprobanteElectronico->total_impuestos_bolsas = ""; //revisar
    if ($afectoDetraccionRetencion == 1) {
      $comprobanteElectronico->detraccion = $afectoDetraccionRetencion == 1 ? true : false; //revisar
      $comprobanteElectronico->detraccion_tipo = $documento[0]["detraccion_codigo"] == '022' ? 20 : 0;
      $comprobanteElectronico->detraccion_total = ($documento[0]["monto_detraccion_retencion"] * 1);
      $comprobanteElectronico->detraccion_porcentaje = ($documento[0]["porcentaje_afecto"] * 1);
    }
    $comprobanteElectronico->observaciones = $documento[0]['comentario'];
    $comprobanteElectronico->documento_que_se_modifica_tipo = "";
    $comprobanteElectronico->documento_que_se_modifica_serie = "";
    $comprobanteElectronico->documento_que_se_modifica_numero = "";
    $comprobanteElectronico->tipo_de_nota_de_credito = "";
    $comprobanteElectronico->tipo_de_nota_de_debito = "";
    $comprobanteElectronico->enviar_automaticamente_a_la_sunat = true;
    $comprobanteElectronico->enviar_automaticamente_al_cliente = false;
    $comprobanteElectronico->condiciones_de_pago = ""; //revisar
    $comprobanteElectronico->medio_de_pago = ""; //revisar
    $orden = '';
    $docOrdenCompra = DocumentoNegocio::create()->obtenerDocumentoRelacionadoImpresion($documentoId);
    foreach ($docOrdenCompra as $index => $ordenCompra) {
      switch ($ordenCompra['identificador_negocio'] * 1) {
        case 2: {
            $orden = $ordenCompra['serie_numero_original'];
            if (!ObjectUtil::isEmpty($orden)) {
              $ordenArray = explode("-", $orden);
              if (count($ordenArray) > 1 && ObjectUtil::isEmpty(trim($ordenArray[0]))) {
                $orden = trim($ordenArray[1]);
              } elseif (count($ordenArray) == 1) {
                $orden = trim($ordenArray[0]);
              }
            }
          }
      }
    }
    $comprobanteElectronico->orden_compra_servicio = $orden;
    $comprobanteElectronico->placa_vehiculo = "";
    $comprobanteElectronico->formato_de_pdf = "";
    $comprobanteElectronico->generado_por_contingencia = "";
    $comprobanteElectronico->bienes_region_selva = "";
    $comprobanteElectronico->servicios_region_selva = "";
    // items
    $new_items = array();
    $impuestoIgv = Configuraciones::IGV_PORCENTAJE / 100;
    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($documento[0]["movimiento_id"]);
    foreach ($documentoDetalle as $index => $fila) {
      $tipoPrecio = "01";
      $tipoAfectacion = "1"; // 10 = Gravado - Operación Onerosa cat. Sunat , 1 = Gravado - Operación Onerosa cat. Nubefact
      $valorMonetario = $fila['valor_monetario'] * 1;
      $valorMonetarioRef = $fila['valor_monetario'] * 1;
      if ($fila['incluye_igv'] == 1) {
        $valorMonetario = $fila['valor_monetario'] / 1.18;
      } else {
        $valorMonetarioRef = $fila['valor_monetario'] * (1.18);
      }
      $precio = $valorMonetario;
      $precioReferencial = $valorMonetarioRef;
      $totalItem = $precio * $fila['cantidad'];
      $totalImpuesto = $precio * $fila['cantidad'] * $impuestoIgv;
      if ($montoGratuito > 0) {
        $tipoAfectacion = "17"; // 21 = Exonerado - Transferencia gratuita cat. SUnat, 17 = Exonerado - Transferencia Gratuita cat. Nubefact
        $tipoPrecio = "02";
        $precio = 0;
        $totalImpuesto = 0;
        $precioReferencial = $valorMonetario;
        $totalItem = $precioReferencial * $fila['cantidad'];
      }
      $items['unidad_de_medida'] = $fila['sunat_unidad_medida'];
      $items['codigo'] = $fila['bien_codigo'];
      $items['descripcion'] = (!ObjectUtil::isEmpty($fila['bien_descripcion_editada'])) ? $fila['bien_descripcion_editada'] : $fila['bien_descripcion'];
      $items['cantidad'] = $fila['cantidad'] * 1;
      $items['valor_unitario'] = $precio; //sin igv
      $items['precio_unitario'] = $precioReferencial; //con igv
      $items['descuento'] = ""; //revisar
      $items['subtotal'] = $totalItem;
      $items['tipo_de_igv'] = $tipoAfectacion;
      $items['igv'] = $totalImpuesto; //Impuesto
      $items['total'] = ($totalItem + $totalImpuesto);
      $items['anticipo_regularizacion'] = false; //revisar
      $items['anticipo_documento_serie'] = ""; //revisar
      $items['anticipo_documento_numero'] = ""; //revisar

      $new_items[] = $items;
    }
    $comprobanteElectronico->items = $new_items;

    $data_json = json_encode($comprobanteElectronico);
    try {
      //Invocamos el servicio de NUBEFACT
      $respuesta = $this->envioNubefact($data_json);
      //leer resultado
      $resultado = json_decode($respuesta, true);
      if (!ObjectUtil::isEmpty($resultado['codigo']) || $resultado['codigo'] == 23) {
        $resultado = $this->consultaNubefact($comprobanteElectronico->serie, $comprobanteElectronico->numero, $comprobanteElectronico->tipo_de_comprobante, "consultar_comprobante");
      }
      $resEfact = $this->validarResultadoNubefactDocumento($resultado, $documentoId);
      return $resEfact;
    } catch (Exception $e) {
      $resultado = $e->getMessage();
    }
  }

  public function generarNotaCreditoElectronicaNubefact($documentoId, $soloPDF, $tipoUso)
  {
    // Obtenemos Datos
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }
    if ($documento[0]["documento_estado"] == "10") {
      $res = $this->consultaNubefact($documento[0]["serie"], $documento[0]["numero"], "3", "consultar_comprobante");
      return $this->validarResultadoNubefactDocumento($res, $documentoId);
    }
    $persona = PersonaNegocio::create()->obtenerPersonaXId($documento[0]["persona_id"]);
    if (ObjectUtil::isEmpty($persona)) {
      throw new WarningException("No se encontró a la persona del documento");
    }
    $ubigeo = PersonaNegocio::create()->obtenerUbigeoXId($documento[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeo)) {
      throw new WarningException("No se especificó el ubigeo del receptor");
    }

    $comprobanteElectronico = new stdClass();
    $comprobanteElectronico->operacion = "generar_comprobante";
    $comprobanteElectronico->tipo_de_comprobante = 3;
    // VALIDA SERIE
    if ($documento[0]["serie"][0] != 'F' && $documento[0]["serie"][0] != 'B') {
      throw new WarningException("La serie del documento debe empezar con F o B");
    }
    $afectoDetraccionRetencion = ($documento[0]["afecto_detraccion_retencion"] * 1); //1 = detracción , 2 = retención

    $comprobanteElectronico->serie = $documento[0]["serie"];
    $comprobanteElectronico->numero = $documento[0]["numero"];
    $comprobanteElectronico->sunat_transaction = $afectoDetraccionRetencion == 1 ? 30 : 1; //revisar
    //Datos de Cliente
    $comprobanteElectronico->cliente_tipo_de_documento = $persona[0]["sunat_tipo_documento"];
    $comprobanteElectronico->cliente_numero_de_documento = $persona[0]["codigo_identificacion"];
    $comprobanteElectronico->cliente_denominacion = $persona[0]["persona_nombre_completo"];
    $comprobanteElectronico->cliente_direccion = $documento[0]["direccion"] . " " . $ubigeo[0]["ubigeo_dist"] . " " . $ubigeo[0]["ubigeo_prov"] . " " . $ubigeo[0]["ubigeo_dep"];
    $correos = PersonaNegocio::create()->obtenerCorreosEFACT();
    $comprobanteElectronico->cliente_email = str_replace(';', '', $persona[0]["email"]);
    $comprobanteElectronico->cliente_email_1 = Configuraciones::EFACT_CORREO;
    $comprobanteElectronico->cliente_email_2 = "";
    //Datos de comprobante
    $comprobanteElectronico->fecha_de_emision = substr($documento[0]["fecha_emision"], 0, 10);
    //$comprobanteElectronico->fecha_de_vencimiento = substr($documento[0]['fecha_vencimiento'], 0, 10);
    $comprobanteElectronico->fecha_de_vencimiento = "";
    $comprobanteElectronico->moneda = $documento[0]["sunat_moneda"] == 'PEN' ? 1 : 2;
    $dataTipoCambio = "";
    if ($documento[0]["sunat_moneda"] != 'PEN') {
      $dataTipoCambio = TipoCambioNegocio::create()->obtenerTipoCambioXfecha($documento[0]['fecha_emision']);
    }
    $comprobanteElectronico->tipo_de_cambio = $dataTipoCambio;
    $comprobanteElectronico->porcentaje_de_igv = "18.00"; //revisar
    $comprobanteElectronico->descuento_global = "";
    $comprobanteElectronico->total_descuento = "";
    $comprobanteElectronico->total_anticipo = "";
    $docTotalIgv = $documento[0]["total"] * 1 - $documento[0]["total"] / 1.18;
    $docTotalVenta = $documento[0]["total"] * 1;
    $docGravadas = $documento[0]["total"] / 1.18;
    if ($documento[0]["motivo_codigo"] == 13) { // Cambio de importes para la NC tipo 13
      $docTotalIgv = 0;
      $docTotalVenta = 0;
      $docGravadas = 0;
    }
    $comprobanteElectronico->total_gravada = $docGravadas;
    $comprobanteElectronico->total_inafecta = "";
    $comprobanteElectronico->total_igv = $docTotalIgv;
    $comprobanteElectronico->total_otros_cargos = "";
    $comprobanteElectronico->total = $docTotalVenta; //total
    $comprobanteElectronico->percepcion_tipo = "";
    $comprobanteElectronico->percepcion_base_imponible = "";
    $comprobanteElectronico->total_percepcion = "";
    $comprobanteElectronico->total_incluido_percepcion = "";
    $comprobanteElectronico->observaciones = $documento[0]['comentario'];
    //OBTENEMOS LOS DOCUMENTOS RELACIONADOS
    $docRelacion = DocumentoNegocio::create()->obtenerDocumentoRelacionadoActivoXDocumentoId($documentoId);
    //VALIDO QUE HAYA DISCREPANCIAS
    if (ObjectUtil::isEmpty($docRelacion)) {
      throw new WarningException("Relacione un documento de venta (factura o boleta)");
    }
    foreach ($docRelacion as $indRel => $itemRel) {
      if (
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_BOLETA_VENTA ||
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_FACTURA_VENTA
      ) {
        //VALIDA SERIE
        if ($itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_BOLETA_VENTA && $itemRel["serie_relacion"][0] != 'B') {
          throw new WarningException("La serie de la boleta relacionada debe empezar con B");
        } else if ($itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_FACTURA_VENTA && $itemRel["serie_relacion"][0] != 'F') {
          throw new WarningException("La serie de la factura relacionada debe empezar con F");
        }
        $comprobanteElectronico->documento_que_se_modifica_tipo = $itemRel['sunat_tipo_doc_rel'];
        $comprobanteElectronico->documento_que_se_modifica_serie = $itemRel["serie_relacion"];
        $comprobanteElectronico->documento_que_se_modifica_numero = $itemRel["numero_relacion"];
      }
    }
    $comprobanteElectronico->tipo_de_nota_de_credito = $documento[0]["motivo_codigo"];
    $comprobanteElectronico->tipo_de_nota_de_debito = "";
    $comprobanteElectronico->enviar_automaticamente_a_la_sunat = true;
    $comprobanteElectronico->enviar_automaticamente_al_cliente = false;
    $comprobanteElectronico->condiciones_de_pago = ""; //revisar
    $comprobanteElectronico->medio_de_pago = ""; //revisar

    // items
    $new_items = array();
    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($documento[0]["movimiento_id"]);
    foreach ($documentoDetalle as $index => $fila) {
      $valorMonetario = $fila['valor_monetario'] * 1;
      if ($fila['incluye_igv'] == 1) {
        $valorMonetario = $fila['valor_monetario'] / 1.18;
      }

      $impuestoIgv = Configuraciones::IGV_PORCENTAJE / 100;
      $subtotal = $valorMonetario * $fila['cantidad'];
      $impuestoItem = $valorMonetario * $fila['cantidad'] * $impuestoIgv;
      if ($documento[0]["motivo_codigo"] == 13) { // Cambio de importes para la NC tipo 13
        $valorMonetario = 0.0;
        $subtotal = 0.0;
        $impuestoItem = 0.0;
      }

      $items['unidad_de_medida'] = $fila['sunat_unidad_medida'];
      $items['codigo'] = $fila['bien_codigo'];
      $items['descripcion'] = (!ObjectUtil::isEmpty($fila['bien_descripcion_editada'])) ? $fila['bien_descripcion_editada'] : $fila['bien_descripcion'];
      $items['cantidad'] = $fila['cantidad'] * 1;
      $items['valor_unitario'] = $valorMonetario; //sin igv
      $items['precio_unitario'] = ($valorMonetario * 1.18); //con igv
      $items['descuento'] = ""; //revisar
      $items['subtotal'] = $subtotal;
      $items['tipo_de_igv'] = 1;
      $items['igv'] = $impuestoItem; //Impuesto
      $items['total'] = ($subtotal + $impuestoItem);
      $items['anticipo_regularizacion'] = false; //revisar
      $items['anticipo_documento_serie'] = ""; //revisar
      $items['anticipo_documento_numero'] = ""; //revisar

      $new_items[] = $items;
    }
    $comprobanteElectronico->items = $new_items;

    $tipoPago = ($documento[0]["tipo_pago"] * 1);
    if ($tipoPago == 2) {
      $venta_al_credito = array();
      //$formaPago[] = array("Credito", $montoNetoPago, "");
      $formaPagoDetalle = PagoNegocio::create()->obtenerPagoProgramacionXDocumentoId($documentoId);
      if (ObjectUtil::isEmpty($formaPagoDetalle)) {
        throw new WarningException("Se requiere de la programación de pago de la factura.");
      }

      $arrayFechaVencimiento = array();
      foreach ($formaPagoDetalle as $indexFormaPago => $itemFormaPago) {
        $arrayFechaVencimiento[] = substr($itemFormaPago['fecha_pago'], 0, 10);
        $venta_al_credito['cuota'] = $indexFormaPago + 1;
        $venta_al_credito['fecha_de_pago'] = substr($itemFormaPago['fecha_pago'], 0, 10);
        $venta_al_credito['importe'] = $itemFormaPago['importe'] * 1.0;

        $new_venta_al_credito[] = $venta_al_credito;
      }
      $fechaMaximaCuota = date("Y-m-d", max(array_map('strtotime', $arrayFechaVencimiento)));
      if (substr($documento[0]['fecha_vencimiento'], 0, 10) != $fechaMaximaCuota) {
        throw new WarningException("La fecha de vencimiento de la factura (" . substr($documento[0]['fecha_vencimiento'], 0, 10) . ") debe ser igual a la última cuota de la programación de pagos ($fechaMaximaCuota)");
      }
    } /*elseif ($tipoPago == 1) {
      $formaPago[] = array("Contado", 0.0, "");
    }*/ else if (ObjectUtil::isEmpty($tipoPago)) {
      throw new WarningException("No se identifica la forma de pago para esta factura.");
    }

    $data_json = json_encode($comprobanteElectronico);
    try {
      //Invocamos el servicio de NUBEFACT
      $respuesta = $this->envioNubefact($data_json);
      //leer resultado
      $resultado = json_decode($respuesta, true);
      //$res = $this->consultaNubefact($comprobanteElectronico->serie, $comprobanteElectronico->numero);
      $resEfact = $this->validarResultadoNubefactDocumento($resultado, $documentoId);
      return $resEfact;
    } catch (Exception $e) {
      $resultado = $e->getMessage();
    }
  }

  public function generarGuiaRemisionElectronicaNubefact($documentoId, $soloPDF, $tipoUso)
  {
    // Obtenemos Datos
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);
    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }
    if ($documento[0]["documento_estado"] == "10") {
      $res = $this->consultaNubefact($documento[0]["serie"], $documento[0]["numero"], "7", "consultar_guia");
      return $this->validarResultadoNubefactDocumento($res, $documentoId);
    }

    $persona = PersonaNegocio::create()->obtenerPersonaXId($documento[0]["persona_id"]);
    if (ObjectUtil::isEmpty($persona)) {
      throw new WarningException("No se encontró a la persona del documento");
    }
    $ubigeo = PersonaNegocio::create()->obtenerUbigeoXId($documento[0]["ubigeo_id"]);
    if (ObjectUtil::isEmpty($ubigeo)) {
      throw new WarningException("No se especificó el ubigeo del receptor");
    }

    $comprobanteElectronico = new stdClass();
    $comprobanteElectronico->operacion = "generar_guia";
    $comprobanteElectronico->tipo_de_comprobante = 7;
    // VALIDA SERIE
    if ($documento[0]["serie"][0] != 'T') {
      throw new WarningException("La serie del documento debe empezar con T");
    }

    $comprobanteElectronico->serie = $documento[0]["serie"];
    $comprobanteElectronico->numero = $documento[0]["numero"];
    //Datos de Cliente
    $comprobanteElectronico->cliente_tipo_de_documento = $persona[0]["sunat_tipo_documento"];
    $comprobanteElectronico->cliente_numero_de_documento = $persona[0]["codigo_identificacion"];
    $comprobanteElectronico->cliente_denominacion = $persona[0]["persona_nombre_completo"];
    $comprobanteElectronico->cliente_direccion = $documento[0]["direccion"] . " " . $ubigeo[0]["ubigeo_dist"] . " " . $ubigeo[0]["ubigeo_prov"] . " " . $ubigeo[0]["ubigeo_dep"];
    $correos = PersonaNegocio::create()->obtenerCorreosEFACT();
    $comprobanteElectronico->cliente_email = str_replace(';', '', $persona[0]["email"]);
    $comprobanteElectronico->cliente_email_1 = Configuraciones::EFACT_CORREO;
    $comprobanteElectronico->cliente_email_2 = "";
    //Datos de comprobante
    $comprobanteElectronico->fecha_de_emision = substr($documento[0]["fecha_emision"], 0, 10);
    $comprobanteElectronico->observaciones = $documento[0]['comentario'];

    $documentoDatoValor = DocumentoDatoValorNegocio::create()->obtenerXIdDocumento($documentoId);
    foreach ($documentoDatoValor as $index => $item) {
      switch ($item['documento_tipo_id'] * 1) {
        case 52:
          $punto_de_partida_direccion = $item['valor'];
          break;
        case 65:
          $fecha_de_inicio_de_traslado = $item['valor'];
          break;
        case 68:
          $transportista_placa_numero = $item['valor'];
          break;
        case 70:
          $personaTransportista = PersonaNegocio::create()->obtenerPersonaXId($item['valor_codigo']);
          break;
        case 855:
          $motivo_de_traslado = $item['valor_codigo'];
          break;
        case 856:
          $motivo_de_traslado_otros_descripcion = $item['valor'];
          break;
        case 3106:
          $peso_bruto_total = $item['valor'];
          break;
        case 3107:
          $numero_de_bultos = $item['valor'];
          break;
        case 3108:
          $tipo_de_transporte = $item['valor_codigo'];
          break;
        case 3109:
          $conductor = PersonaNegocio::create()->obtenerPersonaXLicenciaConducir($item['valor']);
          break;
      }
    }
    if ($tipo_de_transporte == "02") { //Si tipo de transporte es privado
      if (ObjectUtil::isEmpty($conductor)) {
        throw new WarningException("Si la modalidad de traslado es PRIVADO se debe indicar conductor.");
      }

      if (ObjectUtil::isEmpty($transportista_placa_numero)) {
        throw new WarningException("Si la modalidad de traslado es PRIVADO se debe indicar la unidad de transporte.");
      }
    } else { // 01 Si tipo de transporte es público
      if (ObjectUtil::isEmpty($personaTransportista)) {
        throw new WarningException("Si la modalidad de traslado es PÚBLICO se debe seleccionar un transportista.");
      }
    }
    if ($motivo_de_traslado == "02" && $comprobanteElectronico->cliente_numero_de_documento != $documento[0]["ruc_emisor"]) { //Si el motivo de traslado es compra
      throw new WarningException("Si el motivo de traslado es COMPRA, el destinatario cliente debe ser igual al RUC del emisor.");
    }

    $comprobanteElectronico->motivo_de_traslado = $motivo_de_traslado;
    $comprobanteElectronico->motivo_de_traslado_otros_descripcion = $motivo_de_traslado_otros_descripcion;
    $comprobanteElectronico->peso_bruto_total = $peso_bruto_total;
    $comprobanteElectronico->peso_bruto_unidad_de_medida = "KGM";
    $comprobanteElectronico->numero_de_bultos = $numero_de_bultos;
    $comprobanteElectronico->tipo_de_transporte = $tipo_de_transporte;
    $comprobanteElectronico->fecha_de_inicio_de_traslado = $fecha_de_inicio_de_traslado;
    $comprobanteElectronico->transportista_documento_tipo = $personaTransportista[0]["sunat_tipo_documento"];
    $comprobanteElectronico->transportista_documento_numero = $personaTransportista[0]["codigo_identificacion"];
    $comprobanteElectronico->transportista_denominacion = $personaTransportista[0]["persona_nombre_completo"];
    $comprobanteElectronico->transportista_placa_numero = $transportista_placa_numero;
    $comprobanteElectronico->conductor_documento_tipo = $conductor[0]["sunat_tipo_documento"];
    $comprobanteElectronico->conductor_documento_numero = $conductor[0]["codigo_identificacion"];
    if ($tipo_de_transporte == "02") { //Si tipo de transporte es privado
      $comprobanteElectronico->conductor_denominacion = $conductor[0]["conductor_nombre"] . " " . $conductor[0]["conductor_apellido"];
    }
    $comprobanteElectronico->conductor_nombre = $conductor[0]["conductor_nombre"];
    $comprobanteElectronico->conductor_apellidos = $conductor[0]["conductor_apellido"];
    $comprobanteElectronico->conductor_numero_licencia = $conductor[0]["conductor_licencia"];
    $empresa = EmpresaNegocio::create()->obtenerEmpresaXId($documento[0]['empresa_id']);
    $ubigeoPartida = PersonaNegocio::create()->obtenerUbigeoXId($empresa[0]["ubigeo_id"]);
    $ubigeoLlegada = PersonaNegocio::create()->obtenerUbigeoXId($documento[0]["persona_ubigeo_id"]); //revisar
    $comprobanteElectronico->punto_de_partida_ubigeo = $ubigeoPartida[0]["ubigeo_codigo"];
    $comprobanteElectronico->punto_de_partida_direccion = $punto_de_partida_direccion;
    $comprobanteElectronico->punto_de_partida_codigo_establecimiento_sunat = "";
    $comprobanteElectronico->punto_de_llegada_ubigeo = $ubigeoLlegada[0]["ubigeo_codigo"];;
    $comprobanteElectronico->punto_de_llegada_direccion = $documento[0]["direccion_cliente"];
    $comprobanteElectronico->punto_de_llegada_codigo_establecimiento_sunat = "";
    $comprobanteElectronico->enviar_automaticamente_al_cliente = "";
    $comprobanteElectronico->formato_de_pdf = "";

    // items
    $new_items = array();
    $documentoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($documento[0]["movimiento_id"]);
    foreach ($documentoDetalle as $index => $fila) {
      $items['unidad_de_medida'] = $fila['sunat_unidad_medida'];
      $items['codigo'] = $fila['bien_codigo'];
      $items['descripcion'] = (!ObjectUtil::isEmpty($fila['bien_descripcion_editada'])) ? $fila['bien_descripcion_editada'] : $fila['bien_descripcion'];
      $items['cantidad'] = $fila['cantidad'] * 1;
      $new_items[] = $items;
    }
    $comprobanteElectronico->items = $new_items;

    //OBTENEMOS LOS DOCUMENTOS RELACIONADOS
    $documento_relacionado = array();
    $docRelacion = DocumentoNegocio::create()->obtenerDocumentoRelacionadoActivoXDocumentoId($documentoId);
    //VALIDO QUE HAYA DISCREPANCIAS
    /*if (ObjectUtil::isEmpty($docRelacion)) {
      throw new WarningException("Relacione un documento de venta (factura o boleta)");
    }*/
    foreach ($docRelacion as $indRel => $itemRel) {
      if (
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_BOLETA_VENTA ||
        $itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_FACTURA_VENTA
      ) {
        //VALIDA SERIE
        if ($itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_BOLETA_VENTA && $itemRel["serie_relacion"][0] != 'B') {
          throw new WarningException("La serie de la boleta relacionada debe empezar con B");
        } else if ($itemRel['identificador_negocio_relacion'] == DocumentoTipoNegocio::IN_FACTURA_VENTA && $itemRel["serie_relacion"][0] != 'F') {
          throw new WarningException("La serie de la factura relacionada debe empezar con F");
        }
        $documento_relacionados['tipo'] = $itemRel['sunat_tipo_doc_rel'];
        $documento_relacionados['serie'] = $itemRel["serie_relacion"];
        $documento_relacionados['numero'] = $itemRel["numero_relacion"];
        $documento_relacionado[] = $documento_relacionados;
      }
    }
    $comprobanteElectronico->documento_relacionado = $documento_relacionado;

    $comprobanteElectronico->vehiculos_secundarios = "";
    $comprobanteElectronico->conductores_secundarios = "";

    $data_json = json_encode($comprobanteElectronico);
    try {
      //Invocamos el servicio de NUBEFACT
      $respuesta = $this->envioNubefact($data_json);
      //leer resultado
      $resultado = json_decode($respuesta, true);
      //$res = $this->consultaNubefact($comprobanteElectronico->serie, $comprobanteElectronico->numero);
      if (!ObjectUtil::isEmpty($resultado['errors'])) {
        return $this->validarResultadoNubefactDocumento($resultado, $documentoId);
      } else {
        return $this->consultarTicketNubefact($documentoId, "consultar_guia");
      }


      //return $resEfact;
    } catch (Exception $e) {
      $resultado = $e->getMessage();
    }
  }

  public function anularFacturaElectronicaNubefact($documentoId)
  {
    $comprobanteElectronico = new stdClass();
    // Obtenemos Datos
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);

    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }

    $idNegocio = $documento[0]['identificador_negocio'];

    if (
      $idNegocio == DocumentoTipoNegocio::IN_BOLETA_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_FACTURA_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_NOTA_DEBITO_COMPRA
    ) {

      $comprobanteElectronico->operacion = "generar_anulacion";
      $comprobanteElectronico->tipo_de_comprobante = $documento[0]["sunat_tipo_doc_rel"];
      $comprobanteElectronico->serie = $documento[0]["serie"];
      $comprobanteElectronico->numero = $documento[0]["numero"];
      $comprobanteElectronico->motivo = $documento[0]['motivo_anulacion'];
      $comprobanteElectronico->codigo_unico = "";
      //$comprobanteElectronico->codigo_unico = $documento[0]['nro_secuencial_baja'];

      // factura
      /*$comprobanteElectronico->docFechaEmision = date("Y-m-d"); //$documento[0]["fecha_emision"];
      $comprobanteElectronico->docFechaReferencia = substr($documento[0]["fecha_emision"], 0, 10);
      $comprobanteElectronico->docSecuencial = $documento[0]['nro_secuencial_baja'];*/

      // Detalle
      $serieDoc = $documento[0]["serie"];

      if ($idNegocio == DocumentoTipoNegocio::IN_NOTA_DEBITO_COMPRA) {
        $serieNum = DocumentoNegocio::create()->obtenerSerieNumeroXDocumentoId($documentoId);

        $serieDoc = $serieNum[0]["serie"];
      }

      //VALIDA SERIE
      if ($idNegocio == DocumentoTipoNegocio::IN_BOLETA_VENTA && $serieDoc[0] != 'B') {
        throw new WarningException("La serie de la boleta a eliminar debe empezar con B");
      } else if ($idNegocio == DocumentoTipoNegocio::IN_FACTURA_VENTA && $serieDoc[0] != 'F') {
        throw new WarningException("La serie de la factura a eliminar debe empezar con F");
      } else if ($serieDoc[0] != 'B' && $serieDoc[0] != 'F') {
        throw new WarningException("La serie del documento a eliminar debe empezar con F o B");
      }

      //VALIDA MOTIVO ANULACION
      if (ObjectUtil::isEmpty($documento[0]['motivo_anulacion'])) {
        throw new WarningException("Motivo de anulación es obligatorio.");
      }

      $data_json = json_encode($comprobanteElectronico);
      $respuesta = $this->envioNubefact($data_json);
      //leer resultado
      $resultado = json_decode($respuesta, true);
      // VALIDAR EL RESULTADO
      //$this->validarResultadoEfactura($resultado);
      if (!ObjectUtil::isEmpty($resultado['codigo']) || $resultado['codigo'] == 21) {
        $this->consultarTicketNubefact($documentoId, "consultar_anulacion");
      } else {
        $this->validarResultadoNubefact($resultado);
      }
      //"{"numero": 1001, "enlace": "https://imaginatec.pse.pe/anulacion/7e31531e-26e8-4ec0-a038-820857976231", "sunat_ticket_numero": "2024013001184238678", "aceptada_por_sunat": false, "sunat_description": null, "sunat_note": null, "sunat_responsecode": null, "sunat_soap_error": null, "pdf_zip_base64": null, "xml_zip_base64": null, "cdr_zip_base64": null, "enlace_del_pdf": "https://imaginatec.pse.pe/anulacion/7e31531e-26e8-4ec0-a038-820857976231.pdf", "enlace_del_xml": "https://imaginatec.pse.pe/anulacion/7e31531e-26e8-4ec0-a038-820857976231.xml", "enlace_del_cdr": "", "key": "7e31531e-26e8-4ec0-a038-820857976231"}"
      //"{"errors":"El documento no existe o no fue enviado a [PSE.PE]","codigo":24}"
      $ticket = '';
      if (!ObjectUtil::isEmpty($resultado['sunat_ticket_numero'])) {
        $ticket = $resultado['sunat_ticket_numero'];
      }

      // SI TODO ESTA BIEN ACTUALIZAMOS EL NUMERO SECUENCIAL DE BAJA Y EL TICKET QUE SE GENERÓ
      DocumentoNegocio::create()->actualizarNroSecuencialBajaXDocumentoId($documentoId, $documento[0]['nro_secuencial_baja'], $ticket);
    }
  }

  public function consultarTicketNubefact($documentoId, $operacion)
  {
    $comprobanteElectronico = new stdClass();
    // Obtenemos Datos
    $documento = DocumentoNegocio::create()->obtenerDocumentoFEXId($documentoId);

    if (ObjectUtil::isEmpty($documento)) {
      throw new WarningException("No se encontró el documento");
    }

    $idNegocio = $documento[0]['identificador_negocio'];

    if (
      $idNegocio == DocumentoTipoNegocio::IN_BOLETA_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_FACTURA_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_NOTA_CREDITO_VENTA ||
      $idNegocio == DocumentoTipoNegocio::IN_NOTA_DEBITO_COMPRA ||
      $idNegocio == DocumentoTipoNegocio::IN_GUIA_REMISION
    ) {

      $comprobanteElectronico->operacion = $operacion;
      $comprobanteElectronico->tipo_de_comprobante = $documento[0]["sunat_tipo_doc_rel"] == "09" ? "7" : $documento[0]["sunat_tipo_doc_rel"];
      $comprobanteElectronico->serie = $documento[0]["serie"];
      $comprobanteElectronico->numero = $documento[0]["numero"];

      $data_json = json_encode($comprobanteElectronico);
      $respuesta = $this->envioNubefact($data_json);
      //leer resultado
      $resultado = json_decode($respuesta, true);
      // VALIDAR EL RESULTADO
      //$this->validarResultadoEfactura($resultado);
      if ($idNegocio != 6) {
        $this->validarResultadoNubefact($resultado);
        $ticket = '';
        if (!ObjectUtil::isEmpty($resultado['sunat_ticket_numero'])) {
          $ticket = $resultado['sunat_ticket_numero'];
        }
        // SI TODO ESTA BIEN ACTUALIZAMOS EL NUMERO SECUENCIAL DE BAJA Y EL TICKET QUE SE GENERÓ
        DocumentoNegocio::create()->actualizarNroSecuencialBajaXDocumentoId($documentoId, $documento[0]['nro_secuencial_baja'], $ticket);
      } else {
        $resEfact = $this->validarResultadoNubefactDocumento($resultado, $documentoId, $idNegocio);
        return $resEfact;
      }
    }
  }

  public function generarDocumentoPDFSolicitudRequerimiento($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;
    $dataMovimientoTipoColumna = $data->movimientoTipoColumna;
    $dataDocumentoRelacion = $data->documentoRelacionado;

    $unidad_minera = null;
    $otros = null;
    $tipo = null;
    $clase = null;
    $tipo_requerimiento = null;
    $area = null;
    $areaId = null;
    $urgencia = null;

    foreach ($documentoDatoValor as $index => $item) {
      switch ($item['tipo'] * 1) {
        case 2:
          if ($item['descripcion'] == "Otros") {
            $otros = $item['valor'];
          }
          break;
        case 4:
          if ($item['descripcion'] == "Clase") {
            $clase = $item['valor_codigo'];
          } else if ($item['descripcion'] == "Tipo") {
            $tipo = $item['valor_codigo'];
          } else if ($item['descripcion'] == "Unidad Minera") {
            $unidad_minera = $item['valor'];
          } else if ($item['descripcion'] == "Urgencia") {
            $urgencia = $item['valor_codigo'];
          }
          break;
        case 42:
          $tipo_requerimiento = $item['valor'];
          break;
        case 43:
          $area = $item['valor'];
          $areaId = $item['valor_codigo'];
          break;
      }
    }
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator('Soluciones Mineras S.A.C.');
    $pdf->SetAuthor('Soluciones Mineras S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    //        $pdf->SetHeaderData('logo.PNG', PDF_HEADER_LOGO_WIDTH,strtoupper($dataDocumentoTipo[0]['descripcion']),$dataEmpresa[0]['razon_social']."\n".$dataEmpresa[0]['direccion'], array(0,0,0), array(0,0,0));

    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }

    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];

    $pdf->SetFillColor(255, 255, 255);
    $pdf->Image(__DIR__ . '/../../vistas/images/logo_pepas_de_oro.png', 15, 10, 45, 20, '', '', '', false, 300, '', false, false, 1);
    $pdf->MultiCell(90, 5, 'FORMATO', 1, 'C', 1, 0, 60, 10, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(90, 5, 'SOLICITUD DE REQUERIMIENTO DE BIENES Y SERVICIOS', 1, 'C', 1, 0, 60, 15, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'CODIGO: F-COR-LOG-ALM-01', 1, 'L', 1, 0, 60, 20, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'VERSION: 01', 1, 'L', 1, 0, 105, 20, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'AREA: LOGISTICA', 1, 'L', 1, 0, 60, 25, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'PAGINA: 01 de 01', 1, 'L', 1, 0, 105, 25, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 20, 'CORPORATIVO', 1, 'C', 1, 0, 150, 10, true, 0, false, true, 20, 'M');


    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'AREA', 1, 'C', 1, 0, '', 35, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, $area, 1, 'C', 1, 0, 45, 35, true, 0, false, true, 5, 'M');

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'Nº SOL. REQUERIMIENTO', 1, 'C', 1, 0, 115, 35, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, $dataDocumento[0]['serie'] . " - " . $dataDocumento[0]['numero'], 1, 'C', 1, 0, 145, 35, true, 0, false, true, 5, 'M');

    //

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'SOLICITANTE', 1, 'C', 1, 0, '', 42, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, $dataDocumento[0]['nombre'], 1, 'C', 1, 0, 45, 42, true, 0, false, true, 5, 'M');

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'FECHA', 1, 'C', 1, 0, 115, 42, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y'), 1, 'C', 1, 0, 145, 42, true, 0, false, true, 5, 'M');

    //

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'PUESTO', 1, 'C', 1, 0, '', 49, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, $dataDocumento[0]['puesto'], 1, 'C', 1, 0, 45, 49, true, 0, false, true, 5, 'M');

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'UNIDAD MINERA', 1, 'C', 1, 0, 115, 49, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, $unidad_minera, 1, 'C', 1, 0, 145, 49, true, 0, false, true, 5, 'M');

    //

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(180, 5, 'TIPO DE REQUERIMIENTO', 1, 'C', 1, 0, '', 59, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(217, 217, 217);
    $pdf->MultiCell(90, 5, 'COMPRA', 1, 'C', 1, 0, '', 64, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $compra_x = $tipo_requerimiento == "Compra" ? "X" : "";
    $pdf->MultiCell(9, 3, $compra_x, 1, 'C', 1, 0, 80, 65, true, 0, false, true, 3, 'M'); //VALIDAR
    $pdf->SetFillColor(217, 217, 217);
    $pdf->MultiCell(90, 5, 'SERVICIO', 1, 'C', 1, 0, 105, 64, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $servicio_x = $tipo_requerimiento == "Servicio" ? "X" : "";
    $pdf->MultiCell(9, 3, $servicio_x, 1, 'C', 1, 0, 160, 65, true, 0, false, true, 3, 'M'); //VALIDAR
    $pdf->SetFillColor(217, 217, 217);


    $pdf->SetFillColor(217, 217, 217);
    $pdf->MultiCell(30, 20, 'CLASE', 1, 'C', 1, 0, '', 69, true, 0, false, true, 20, 'M');
    $pdf->SetFillColor(255, 255, 255);

    $clase_x1 = "";
    $clase_x2 = "";
    $clase_x3 = "";
    $clase_x4 = "";
    switch ($clase * 1) {
      case 1:
        $clase_x1 = "X";
        break;
      case 2:
        $clase_x2 = "X";
        break;
      case 3:
        $clase_x3 = "X";
        break;
      case 4:
        $clase_x4 = "X";
        break;
    }

    $pdf->SetFont('helvetica', '', 6);
    $pdf->MultiCell(60, 5, 'Reposición de stock en Almacén', 0, 'L', '', 0, 55, 69, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(60, 5, 'Regular: ', 1, 'L', 1, 0, 45, 69, true, 0, false, true, 5, 'M');

    $pdf->MultiCell(7, 3, $clase_x1, 1, 'C', 1, 0, 95, 70, true, 0, false, true, 3, 'M'); //VALIDAR
    $pdf->SetFont('helvetica', '', 6);

    $pdf->MultiCell(60, 5, 'Compra dentro de 15 días a más', 0, 'L', 1, 0, 55, 74, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(60, 5, 'Irregular: ', 1, 'L', 1, 0, 45, 74, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(7, 3, $clase_x2, 1, 'C', 1, 0, 95, 75, true, 0, false, true, 3, 'M'); //VALIDAR
    $pdf->SetFont('helvetica', '', 6);

    $pdf->MultiCell(60, 5, 'Compra dentro de 5 a 10 dias', 1, 'L', 1, 0, 55, 79, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(60, 5, 'Crítico: ', 1, 'L', 1, 0, 45, 79, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(7, 3, $clase_x3, 1, 'C', 1, 0, 95, 80, true, 0, false, true, 3, 'M'); //VALIDAR
    $pdf->SetFont('helvetica', '', 6);

    $pdf->MultiCell(60, 5, 'Compra dentro de 20 días a más', 1, 'L', 1, 0, 55, 84, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(60, 5, 'Activo: ', 1, 'L', 1, 0, 45, 84, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(7, 3, $clase_x4, 1, 'C', 1, 0, 95, 85, true, 0, false, true, 3, 'M'); //VALIDAR
    $pdf->SetFont('helvetica', '', 6);

    $pdf->SetFillColor(217, 217, 217);
    $pdf->MultiCell(15, 20, 'TIPO', 1, 'C', 1, 0, 105, 69, true, 0, false, true, 20, 'M');
    $pdf->SetFillColor(255, 255, 255);

    $tipo_x1 = "";
    $tipo_x2 = "";
    $tipo_x3 = "";
    $tipo_x4 = "";
    switch ($tipo * 1) {
      case 1:
        $tipo_x1 = "X";
        break;
      case 2:
        $tipo_x2 = "X";
        break;
      case 3:
        $tipo_x3 = "X";
        break;
      case 4:
        $tipo_x4 = "X";
        break;
    }
    $pdf->MultiCell(75, 5, 'Alquiler', 1, 'L', 1, 0, 120, 69, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(7, 3, $tipo_x1, 1, 'C', 1, 0, 140, 70, true, 0, false, true, 3, 'M'); //VALIDAR
    $pdf->MultiCell(75, 5, 'Mantenimiento', 1, 'L', 1, 0, 120, 74, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(7, 3, $tipo_x2, 1, 'C', 1, 0, 140, 75, true, 0, false, true, 3, 'M'); //VALIDAR
    $pdf->MultiCell(75, 5, 'Monitoreo', 1, 'L', 1, 0, 120, 79, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(7, 3, $tipo_x3, 1, 'C', 1, 0, 140, 80, true, 0, false, true, 3, 'M'); //VALIDAR
    $pdf->MultiCell(75, 5, 'Otros (Detallar)', 1, 'L', 1, 0, 120, 84, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(7, 3, $tipo_x4, 1, 'C', 1, 0, 140, 85, true, 0, false, true, 3, 'M'); //VALIDAR
    $pdf->MultiCell(7, 3, $otros, 1, 'L', 1, 0, 150, 85, true, 0, false, true, 3, 'M'); //VALIDAR


    //detalle
    // $pdf->SetFillColor(254, 191, 0);
    // $pdf->MultiCell(10, 5, 'N°', 1, 'C', 1, 0, '', 59, true, 0, false, true, 5, 'M');
    // $pdf->MultiCell(10, 5, 'TIPO DE REQUERIMIENTO', 1, 'C', 1, 0, '', 59, true, 0, false, true, 5, 'M');
    $cont = 0;

    $pdf->Ln(10);
    $tabla = '<table cellspacing="0" cellpadding="1" border="1">
        <tr style="background-color:rgb(254, 191, 0);">
            <th style="text-align:center;vertical-align:middle;" width="3%"><b>N°</b></th>
            <th style="text-align:center;vertical-align:middle;" width="12%"><b>CODIGO</b></th>
            <th style="text-align:center;vertical-align:middle;" width="30%"><b>DESCRIPCION</b></th>
            <th style="text-align:center;vertical-align:middle;" width="8%"><b>MARCA</b></th>
            <th style="text-align:center;vertical-align:middle;" width="10%"><b>MODELO</b></th>
            <th style="text-align:center;vertical-align:middle;" width="8%"><b>CANTIDAD</b></th>
            <th style="text-align:center;vertical-align:middle;" width="8%"><b>UNIDAD DE MEDIDA</b></th>
            <th style="text-align:center;vertical-align:middle;" width="10%"><b>CENTRO DE COSTOS</b></th>
            <th style="text-align:center;vertical-align:middle;" width="11%"><b>INFORMACION ADICIONAL</b></th>
        </tr>
    ';
    if (!ObjectUtil::isEmpty($detalle)) {
      foreach ($detalle as $index => $item) {
        $tabla = $tabla . '<tr>'
          . '<td style="text-align:center"  width="3%">' . ($index + 1) . '</td>'
          . '<td align="center" width="12%">' . $item->bien_codigo . '</td>'
          . '<td style="text-align:left; vertical-align:middle; display: table-cell;" width="30%">' . $item->descripcion . '</td>'
          . '<td style="text-align:center"  width="8%"></td>'
          . '<td style="text-align:center"  width="10%"></td>'
          . '<td style="text-align:center"  width="8%">' . number_format($item->cantidad, 2) . '</td>'
          . '<td style="text-align:center"  width="8%">' . $item->simbolo . '</td>'
          . '<td style="text-align:center"  width="10%">' . $item->centro_costo_descripcion . '</td>'
          . '<td style="text-align:center"  width="11%">' . $item->movimientoBienComentario . '</td>'
          . '</tr>';
      }
    }

    for ($i = count($detalle); $i < 20; $i++) {
      $tabla = $tabla . '<tr>'
        . '<td style="text-align:center"  width="3%">' . ($i + 1) . '</td>'
        . '<td style="text-align:left"  width="12%"></td>'
        . '<td style="text-align:left"  width="30%"></td>'
        . '<td style="text-align:center"  width="8%"></td>'
        . '<td style="text-align:center"  width="10%"></td>'
        . '<td style="text-align:center"  width="8%"></td>'
        . '<td style="text-align:center"  width="8%"></td>'
        . '<td style="text-align:center"  width="10%"></td>'
        . '<td style="text-align:center"  width="11%"></td>'
        . '</tr>';
    }


    $tabla = $tabla . '</table>';
    $pdf->writeHTML($tabla, true, false, true, false, '');

    
    $tablaHeight = $pdf->GetY(); 
    $espacio = 0;  // Inicializar el espacio
    $paginaAltura = $pdf->getPageHeight();  // Altura total de la página
    $alturaDisponible = $paginaAltura - $tablaHeight - 20; 
    // Ahora puedes ajustar el valor de $espacio basado en el espacio disponible
    if ($alturaDisponible > 50) {
      // Si hay mucho espacio, usa ese espacio
      $espacio = $tablaHeight + 10;  // Ajusta un pequeño margen después de la tabla
    } else {
      // Si el espacio es limitado, podrías agregar una nueva página
      $pdf->AddPage();
      $espacio = 15;  // Nuevo espacio al inicio de la nueva página
    }
    // Establecer la marca de agua de texto
    // $pdf->SetAlpha(0.3); // Opcional: Ajusta la opacidad (0 es totalmente transparente, 1 es opaco)
    // $pdf->SetFont('helvetica', 'B', 50); // Fuente, estilo y tamaño
    // $pdf->SetTextColor(150, 150, 150); // Color de la marca de agua (gris claro)
    // $pdf->Rotate(45, 105, 150); // Rotar el texto para la marca de agua (45 grados)
    // $pdf->SetDrawColor(255, 255, 255); // Establecer el color de dibujo (blanco, para evitar cualquier borde)
    // $pdf->SetLineWidth(0); // Establecer el grosor del borde a 0
    // $pdf->Text(50, 120, 'Sin aprobar', false, false, true, ''); // Especifica la posición del texto

    $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoXArea(Configuraciones::SOLICITUD_REQUERIMIENTO, $areaId);
    $usuario_estado = DocumentoNegocio::create()->obtenerDocumentoDocumentoEstadoXdocumentoId($documentoId, "0,1");

    $resultadoMatriz = [];

    foreach ($matrizUsuario as $key => $value) {
      if ($usuario_estado[$key]["estado_descripcion"] == "Registrado") {
        $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
        $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" => $usuario_estado[$key]["persona_nombre"], "fecha" => $usuario_estado[$key]["fecha_creacion"]);
      } else {
        switch ($value["nivel"]) {
          case "1":
            foreach ($usuario_estado as $val) {
              if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"], "fecha" => $usuario_estado[$key]["fecha_creacion"]);
              }
            }
            break;
          case "2":
            foreach ($usuario_estado as $val) {
              if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"], "fecha" => $usuario_estado[$key]["fecha_creacion"]);
              }
            }
            break;
          case "3":
            foreach ($usuario_estado as $val) {
              if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"], "fecha" => $usuario_estado[$key]["fecha_creacion"]);
              }
            }
            break;
        }
      }
    }

    $personaFirma0 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[0]['firma_digital'] . "png";
    $personaFirma1 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[1]['firma_digital'] . "png";
    $personaFirma2 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[2]['firma_digital'] . "png";
    $personaFirma3 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[3]['firma_digital'] . "png";


    if ($urgencia == 1) {
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(50, 5, 'Solicitado por', 1, 'C', 1, 0, 35, $espacio, true, 0, false, true, 5, 'M');
      $pdf->MultiCell(50, 30, $dataDocumento[0]['nombre'], 1, 'C', 1, 0, 35, $espacio, true, 0, false, true, 30, 'B');
      $pdf->MultiCell(50, 5, 'Aprobado por', 1, 'C', 1, 0, 85, $espacio, true, 0, false, true, 5, 'M');
      $pdf->MultiCell(50, 30, $resultadoMatriz[1]['nombre'], 1, 'C', 1, 0, 85, $espacio, true, 0, false, true, 30, 'B');
      $pdf->MultiCell(50, 5, 'Recibido por', 1, 'C', 1, 0, 135, $espacio, true, 0, false, true, 5, 'M');
      $pdf->MultiCell(50, 30, $resultadoMatriz[2]['nombre'], 1, 'C', 1, 0, 135, $espacio, true, 0, false, true, 30, 'B');
      $pdf->Image($personaFirma0, 35, $espacio + 5, 50, 20, '', '', '', false, 300, '', false, false, 1);
      $pdf->MultiCell(50, 30, 'Jefe de Area', 1, 'C', 1, 0, 35, $espacio + 5, true, 0, false, true, 30, 'B');
      $pdf->Image($personaFirma1, 85, $espacio + 5, 50, 20, '', '', '', false, 300, '', false, false, 1);
      $pdf->MultiCell(50, 30, 'Gerente General', 1, 'C', 1, 0, 85, $espacio + 5, true, 0, false, true, 30, 'B');
      $pdf->Image($personaFirma2, 135, $espacio + 5, 50, 20, '', '', '', false, 300, '', false, false, 1);
      $pdf->MultiCell(50, 30, 'Jefe de Logistica', 1, 'C', 1, 0, 135, $espacio + 5, true, 0, false, true, 30, 'B');
      $pdf->MultiCell(60, 5, 'Fecha:', 1, 'L', 1, 0, 35, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', '', 6);
      $pdf->MultiCell(50, 5, date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y'), 1, 'L', 1, 0, 45, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(50, 5, 'Fecha:', 1, 'L', 1, 0, 85, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(40, 5, date_format((date_create($dataDocumento[1]['fecha_emision'])), 'd/m/Y'), 1, 'L', 1, 0, 95, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(50, 5, 'Fecha:', 1, 'L', 1, 0, 135, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', '', 6);
      $pdf->MultiCell(40, 5, date_format((date_create($dataDocumento[2]['fecha_emision'])), 'd/m/Y'), 1, 'L', 1, 0, 145, $espacio + 35, true, 0, false, true, 5, 'M');
    } else {
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(60, 5, 'Solicitado por', 1, 'C', 1, 0, 45, $espacio, true, 0, false, true, 5, 'M');
      $pdf->MultiCell(60, 30, $dataDocumento[0]['nombre'], 1, 'C', 1, 0, 45, $espacio, true, 0, false, true, 30, 'B');
      $pdf->MultiCell(60, 5, 'Aprobado por', 1, 'C', 1, 0, 105, $espacio, true, 0, false, true, 5, 'M');
      $pdf->MultiCell(60, 30, $resultadoMatriz[1]['nombre'], 1, 'C', 1, 0, 105, $espacio, true, 0, false, true, 30, 'B');
      $pdf->Image($personaFirma0, 45, $espacio + 5, 60, 20, '', '', '', false, 300, '', false, false, 1);
      $pdf->MultiCell(60, 30, 'Usuario', 1, 'C', 1, 0, 45, $espacio + 5, true, 0, false, true, 30, 'B');
      $pdf->Image($personaFirma1, 105, $espacio + 5, 50, 20, '', '', '', false, 300, '', false, false, 1);
      $pdf->MultiCell(60, 30, 'Jefe de Area', 1, 'C', 1, 0, 105, $espacio + 5, true, 0, false, true, 30, 'B');
      $pdf->MultiCell(60, 5, 'Fecha:', 1, 'L', 1, 0, 45, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', '', 6);
      $pdf->MultiCell(60, 5, date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y'), 1, 'L', 1, 0, 55, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(60, 5, 'Fecha:', 1, 'L', 1, 0, 105, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', '', 6);
      $pdf->MultiCell(50, 5, date_format((date_create($resultadoMatriz[1]['fecha_emision'])), 'd/m/Y'), 1, 'L', 1, 0, 115, $espacio + 35, true, 0, false, true, 5, 'M');
    }
    $pdf->SetFont('helvetica', '', 6);
    $pdf->writeHTMLCell(180, 5, '', $espacio + 41, 'El usuario es responsable de asegurar el uso de los documentos vigentes disponibles en la <strong>plataforma documentaria</strong> o en consulta con el <strong>Coordinador SGI o Analista SGI</strong>', 0, 1, 1, true, 'C', true);

    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  public function generarDocumentoPDFRequerimiento($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;
    $dataMovimientoTipoColumna = $data->movimientoTipoColumna;
    $dataDocumentoRelacion = $data->documentoRelacionado;

    $tipo_requerimiento = null;
    $area = null;
    $areaId = null;
    $urgencia = null;

    foreach ($documentoDatoValor as $index => $item) {
      switch ($item['tipo'] * 1) {
        case 4:
          if ($item['descripcion'] == "Urgencia") {
            $urgencia = $item['valor_codigo'];
          }
          break;
        case 42:
          $tipo_requerimiento = $item['valor'];
          break;
        case 43:
          $area = $item['valor'];
          $areaId = $item['valor_codigo'];
          break;
      }
    }
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator('Soluciones Mineras S.A.C.');
    $pdf->SetAuthor('Soluciones Mineras S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    //        $pdf->SetHeaderData('logo.PNG', PDF_HEADER_LOGO_WIDTH,strtoupper($dataDocumentoTipo[0]['descripcion']),$dataEmpresa[0]['razon_social']."\n".$dataEmpresa[0]['direccion'], array(0,0,0), array(0,0,0));

    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }

    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];

    $pdf->SetFillColor(255, 255, 255);
    $pdf->Image(__DIR__ . '/../../vistas/images/logo_pepas_de_oro.png', 15, 10, 45, 20, '', '', '', false, 300, '', false, false, 1);
    $pdf->MultiCell(90, 5, 'FORMATO', 1, 'C', 1, 0, 60, 10, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(90, 5, 'REQUERIMIENTO DE BIENES Y SERVICIOS', 1, 'C', 1, 0, 60, 15, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'CODIGO: F-COR-LOG-ALM-01', 1, 'L', 1, 0, 60, 20, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'VERSION: 01', 1, 'L', 1, 0, 105, 20, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'AREA: LOGISTICA', 1, 'L', 1, 0, 60, 25, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'PAGINA: 01 de 01', 1, 'L', 1, 0, 105, 25, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 20, 'CORPORATIVO', 1, 'C', 1, 0, 150, 10, true, 0, false, true, 20, 'M');


    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'AREA', 1, 'C', 1, 0, '', 35, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, $area, 1, 'C', 1, 0, 45, 35, true, 0, false, true, 5, 'M');

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'Nº REQUERIMIENTO', 1, 'C', 1, 0, 115, 35, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, $dataDocumento[0]['serie'] . " - " . $dataDocumento[0]['numero'], 1, 'C', 1, 0, 145, 35, true, 0, false, true, 5, 'M');

    //

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'SOLICITANTE', 1, 'C', 1, 0, '', 42, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, $dataDocumento[0]['nombre'], 1, 'C', 1, 0, 45, 42, true, 0, false, true, 5, 'M');

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'FECHA', 1, 'C', 1, 0, 115, 42, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y'), 1, 'C', 1, 0, 145, 42, true, 0, false, true, 5, 'M');

    //

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'PUESTO', 1, 'C', 1, 0, '', 49, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, $dataDocumento[0]['puesto'], 1, 'C', 1, 0, 45, 49, true, 0, false, true, 5, 'M');

    //

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(180, 5, 'TIPO DE REQUERIMIENTO', 1, 'C', 1, 0, '', 59, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(217, 217, 217);
    $pdf->MultiCell(90, 5, 'COMPRA', 1, 'C', 1, 0, '', 64, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $compra_x = $tipo_requerimiento == "Compra" ? "X" : "";
    $pdf->MultiCell(9, 3, $compra_x, 1, 'C', 1, 0, 80, 65, true, 0, false, true, 3, 'M'); //VALIDAR
    $pdf->SetFillColor(217, 217, 217);
    $pdf->MultiCell(90, 5, 'SERVICIO', 1, 'C', 1, 0, 105, 64, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $servicio_x = $tipo_requerimiento == "Servicio" ? "X" : "";
    $pdf->MultiCell(9, 3, $servicio_x, 1, 'C', 1, 0, 160, 65, true, 0, false, true, 3, 'M'); //VALIDAR


    //detalle
    // $pdf->SetFillColor(254, 191, 0);
    // $pdf->MultiCell(10, 5, 'N°', 1, 'C', 1, 0, '', 59, true, 0, false, true, 5, 'M');
    // $pdf->MultiCell(10, 5, 'TIPO DE REQUERIMIENTO', 1, 'C', 1, 0, '', 59, true, 0, false, true, 5, 'M');
    $cont = 0;
    $pdf->SetFont('helvetica', 'B', 5);

    $pdf->Ln(10);
    $tabla = '<table cellspacing="0" cellpadding="1" border="1">
        <tr style="background-color:rgb(254, 191, 0);">
            <th style="text-align:center;vertical-align:middle;" width="3%"><b>N°</b></th>
            <th style="text-align:center;vertical-align:middle;" width="12%"><b>CODIGO</b></th>
            <th style="text-align:center;vertical-align:middle;" width="35%"><b>DESCRIPCION</b></th>
            <th style="text-align:center;vertical-align:middle;" width="8%"><b>MARCA</b></th>
            <th style="text-align:center;vertical-align:middle;" width="8%"><b>MODELO</b></th>
            <th style="text-align:center;vertical-align:middle;" width="8%"><b>CANTIDAD</b></th>
            <th style="text-align:center;vertical-align:middle;" width="8%"><b>UNIDAD DE MEDIDA</b></th>
            <th style="text-align:center;vertical-align:middle;" width="10%"><b>N° RQ</b></th>
            <th style="text-align:center;vertical-align:middle;" width="8%"><b>INFORMACION ADICIONAL</b></th>
        </tr>
    ';
    if (!ObjectUtil::isEmpty($detalle)) {
      foreach ($detalle as $index => $item) {
        $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleObtenerSolicitudR($item->movimientoBienId);

        $cont++;
        // if (strlen($item->descripcion) > 39) {
        //   $cont++;
        // }

        $tabla = $tabla . '<tr>'
          . '<td style="text-align:center"  width="3%">' . ($index + 1) . '</td>'
          . '<td align="center" width="12%">' . $item->bien_codigo . '</td>'
          . '<td style="text-align:left; vertical-align:middle; display: table-cell;" width="35%">' . $item->descripcion . '</td>'
          . '<td style="text-align:center"  width="8%"></td>'
          . '<td style="text-align:center"  width="8%"></td>'
          . '<td style="text-align:center"  width="8%">' . number_format($item->cantidad, 2) . '</td>'
          . '<td style="text-align:center"  width="8%">' . $item->simbolo . '</td>'
          . '<td style="text-align:center"  width="10%">'.$resMovimientoBienDetalle[0]['solicitud_requerimiento'].'</td>'
          . '<td style="text-align:center"  width="8%">' . $item->movimientoBienComentario . '</td>'
          . '</tr>';
      }
    }

    for ($i = count($detalle); $i < 20; $i++) {
      $tabla = $tabla . '<tr>'
        . '<td style="text-align:center"  width="3%">' . ($i + 1) . '</td>'
        . '<td style="text-align:left"  width="12%"></td>'
        . '<td style="text-align:left"  width="35%"></td>'
        . '<td style="text-align:center"  width="8%"></td>'
        . '<td style="text-align:center"  width="8%"></td>'
        . '<td style="text-align:center"  width="8%"></td>'
        . '<td style="text-align:center"  width="8%"></td>'
        . '<td style="text-align:center"  width="10%"></td>'
        . '<td style="text-align:center"  width="8%"></td>'
        . '</tr>';
    }


    $tabla = $tabla . '</table>';
    $pdf->writeHTML($tabla, true, false, true, false, '');


    $tablaHeight = $pdf->GetY(); 
    $espacio = 0;  // Inicializar el espacio
    $paginaAltura = $pdf->getPageHeight();  // Altura total de la página
    $alturaDisponible = $paginaAltura - $tablaHeight - 20; 
    // Ahora puedes ajustar el valor de $espacio basado en el espacio disponible
    if ($alturaDisponible > 50) {
      // Si hay mucho espacio, usa ese espacio
      $espacio = $tablaHeight + 10;  // Ajusta un pequeño margen después de la tabla
    } else {
      // Si el espacio es limitado, podrías agregar una nueva página
      $pdf->AddPage();
      $espacio = 15;  // Nuevo espacio al inicio de la nueva página
    }
    // Establecer la marca de agua de texto
    // $pdf->SetAlpha(0.3); // Opcional: Ajusta la opacidad (0 es totalmente transparente, 1 es opaco)
    // $pdf->SetFont('helvetica', 'B', 50); // Fuente, estilo y tamaño
    // $pdf->SetTextColor(150, 150, 150); // Color de la marca de agua (gris claro)
    // $pdf->Rotate(45, 105, 150); // Rotar el texto para la marca de agua (45 grados)
    // $pdf->SetDrawColor(255, 255, 255); // Establecer el color de dibujo (blanco, para evitar cualquier borde)
    // $pdf->SetLineWidth(0); // Establecer el grosor del borde a 0
    // $pdf->Text(50, 120, 'Sin aprobar', false, false, true, ''); // Especifica la posición del texto

    $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoXArea(Configuraciones::SOLICITUD_REQUERIMIENTO, $areaId);
    $usuario_estado = DocumentoNegocio::create()->obtenerDocumentoDocumentoEstadoXdocumentoId($documentoId, "0,1");

    $resultadoMatriz = [];

    foreach ($matrizUsuario as $key => $value) {
      if ($usuario_estado[$key]["estado_descripcion"] == "Registrado") {
        $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
        $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" => $usuario_estado[$key]["nombre"], "fecha" => $usuario_estado[$key]["usuario_creacion"]);
      } else {
        switch ($value["nivel"]) {
          case "1":
            foreach ($usuario_estado as $val) {
              if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"]);
              }
            }
            break;
          case "2":
            foreach ($usuario_estado as $val) {
              if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"]);
              }
            }
            break;
          case "3":
            foreach ($usuario_estado as $val) {
              if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"]);
              }
            }
            break;
        }
      }
    }

    $personaFirma0 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[0]['firma_digital'] . "png";
    $personaFirma1 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[1]['firma_digital'] . "png";
    $personaFirma2 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[2]['firma_digital'] . "png";
    $personaFirma3 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[3]['firma_digital'] . "png";


    if ($urgencia == 1) {
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(50, 5, 'Solicitado por', 1, 'C', 1, 0, 35, $espacio, true, 0, false, true, 5, 'M');
      $pdf->MultiCell(50, 30, $dataDocumento[0]['nombre'], 1, 'C', 1, 0, 35, $espacio, true, 0, false, true, 30, 'B');
      $pdf->MultiCell(50, 5, 'Aprobado por', 1, 'C', 1, 0, 85, $espacio, true, 0, false, true, 5, 'M');
      $pdf->MultiCell(50, 30, $resultadoMatriz[1]['nombre'], 1, 'C', 1, 0, 85, $espacio, true, 0, false, true, 30, 'B');
      $pdf->MultiCell(50, 5, 'Recibido por', 1, 'C', 1, 0, 135, $espacio, true, 0, false, true, 5, 'M');
      $pdf->MultiCell(50, 30, $resultadoMatriz[2]['nombre'], 1, 'C', 1, 0, 135, $espacio, true, 0, false, true, 30, 'B');
      $pdf->Image($personaFirma0, 35, $espacio + 5, 50, 20, '', '', '', false, 300, '', false, false, 1);
      $pdf->MultiCell(50, 30, 'Jefe de Area', 1, 'C', 1, 0, 35, $espacio + 5, true, 0, false, true, 30, 'B');
      $pdf->Image($personaFirma1, 85, $espacio + 5, 50, 20, '', '', '', false, 300, '', false, false, 1);
      $pdf->MultiCell(50, 30, 'Gerente General', 1, 'C', 1, 0, 85, $espacio + 5, true, 0, false, true, 30, 'B');
      $pdf->Image($personaFirma2, 135, $espacio + 5, 50, 20, '', '', '', false, 300, '', false, false, 1);
      $pdf->MultiCell(50, 30, 'Jefe de Logistica', 1, 'C', 1, 0, 135, $espacio + 5, true, 0, false, true, 30, 'B');
      $pdf->MultiCell(50, 5, 'Fecha:', 1, 'L', 1, 0, 35, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', '', 6);
      $pdf->MultiCell(50, 5, date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y'), 1, 'L', 1, 0, 45, $espacio + 30, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(50, 5, 'Fecha:', 1, 'L', 1, 0, 85, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', '', 6);
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(40, 5, date_format((date_create($dataDocumento[1]['fecha_emision'])), 'd/m/Y'), 1, 'L', 1, 0, 95, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(50, 5, 'Fecha:', 1, 'L', 1, 0, 135, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', '', 6);
      $pdf->MultiCell(40, 5, date_format((date_create($dataDocumento[2]['fecha_emision'])), 'd/m/Y'), 1, 'L', 1, 0, 145, $espacio + 35, true, 0, false, true, 5, 'M');
    } else {
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(60, 5, 'Solicitado por', 1, 'C', 1, 0, 45, $espacio, true, 0, false, true, 5, 'M');
      $pdf->MultiCell(60, 30, $dataDocumento[0]['nombre'], 1, 'C', 1, 0, 45, $espacio, true, 0, false, true, 30, 'B');
      $pdf->MultiCell(60, 5, 'Aprobado por', 1, 'C', 1, 0, 105, $espacio, true, 0, false, true, 5, 'M');
      $pdf->MultiCell(60, 30, $resultadoMatriz[1]['nombre'], 1, 'C', 1, 0, 105, $espacio, true, 0, false, true, 30, 'B');
      $pdf->Image($personaFirma0, 45, $espacio + 5, 60, 20, '', '', '', false, 300, '', false, false, 1);
      $pdf->MultiCell(60, 30, 'Usuario', 1, 'C', 1, 0, 45, $espacio + 5, true, 0, false, true, 30, 'B');
      $pdf->Image($personaFirma1, 105, $espacio + 5, 50, 20, '', '', '', false, 300, '', false, false, 1);
      $pdf->MultiCell(60, 30, 'Gerente General', 1, 'C', 1, 0, 105, $espacio + 5, true, 0, false, true, 30, 'B');
      $pdf->MultiCell(60, 5, 'Fecha:', 1, 'L', 1, 0, 45, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', '', 6);
      $pdf->MultiCell(60, 5, date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y'), 1, 'L', 1, 0, 55, $espacio + 35, true, 0, false, true, 5, 'M');
      $pdf->SetFont('helvetica', 'B', 6);
      $pdf->MultiCell(60, 5, 'Fecha:', 1, 'L', 1, 0, 105, $espacio + 35, true, 0, false, true, 5, 'M');
    }
    $pdf->SetFont('helvetica', '', 6);
    $pdf->writeHTMLCell(180, 5, '', $espacio + 41, 'El usuario es responsable de asegurar el uso de los documentos vigentes disponibles en la <strong>plataforma documentaria</strong> o en consulta con el <strong>Coordinador SGI o Analista SGI</strong>', 0, 1, 1, true, 'C', true);

    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  // public function obtenerDocumentosXAreaId($areaId, $tipoRequerimiento, $urgencia)
  // {
  //   $data = DocumentoNegocio::create()->obtenerDocumentosXAreaId($areaId, $tipoRequerimiento, $urgencia);
  //   return $data;
  // }
  public function obtenerDetalleXAreaId($opcionId, $empresaId, $areaId, $documentoTipoId, $tipoRequerimiento, $urgencia)
  {
    $respuesta = new stdClass();
    $arrayDataBien = array();

    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    $detalleRequerimientos = MovimientoBien::create()->obtenerMovimientoBienXRequerimientoXAreaId($areaId, $tipoRequerimiento, $urgencia);
    //OBTENER DATA DE UNIDAD DE MEDIDA
    foreach ($detalleRequerimientos as $index => $item) {
      $bienId = $item['bien_id'];
      $unidadMedidaId = $item['unidad_medida_id'];
      $precioTipoId = $item['precio_tipo_id'];
      $monedaId = $respuesta->movimientoTipo[0]['moneda_id'];
      $fechaEmision = date("d/m/Y");
      foreach ($respuesta->documento_tipo_conf as $itemDato) {
        if ($itemDato['tipo'] == 9) {
          $fechaEmision = date_format((date_create($itemDato['valor'])), 'd/m/Y');
        }
      }

      $data = MovimientoNegocio::create()->obtenerUnidadMedida($bienId, $unidadMedidaId, $precioTipoId, $monedaId, $fechaEmision);
      $detalleRequerimientos[$index]['dataUnidadMedida'] = $data;
      $dataBien = BienNegocio::create()->obtenerActivosXMovimientoTipoIdBienId($empresaId, $movimientoTipo[0]["id"], $bienId);
      foreach ($dataBien as $datos) {
        array_push($arrayDataBien, $datos);
      }

      $arrayIds = explode(',', $item["movimiento_bien_ids"]);
      foreach ($arrayIds as $itemarrayIds) {
        $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleXMovimientoBienId($itemarrayIds);
      }
    }

    $respuesta->detalleRequerimientos = $detalleRequerimientos;
    $respuesta->count_detalleRequerimientos = count($detalleRequerimientos);
    $DocumentoId = explode(",",$detalleRequerimientos[0]['documento_id']);
    $respuesta->dataDocumentoRelacionada = DocumentoNegocio::create()->obtenerDataDocumentoACopiarRelacionada(Configuraciones::SOLICITUD_REQUERIMIENTO, $documentoTipoId, ($DocumentoId[0] == ""? $detalleRequerimientos[0]['documento_id'] : $DocumentoId[0]));
    $respuesta->dataBien = $arrayDataBien;

    return $respuesta;
  }

  public function obtenerDetalleXGrupoProductoId($opcionId, $empresaId, $grupoProductoId, $tipoRequerimiento, $urgencia)
  {
    $respuesta = new stdClass();
    $arrayDataBien = array();

    $movimientoTipo = MovimientoTipoNegocio::create()->obtenerXOpcion($opcionId);
    $detalleRequerimientos = MovimientoBien::create()->obtenerMovimientoBienXRequerimientoXGrupoProductoxId($grupoProductoId, $tipoRequerimiento, $urgencia);
    //OBTENER DATA DE UNIDAD DE MEDIDA
    foreach ($detalleRequerimientos as $index => $item) {
      $bienId = $item['bien_id'];
      $unidadMedidaId = $item['unidad_medida_id'];
      $precioTipoId = $item['precio_tipo_id'];
      $monedaId = $respuesta->movimientoTipo[0]['moneda_id'];
      $fechaEmision = date("d/m/Y");
      foreach ($respuesta->documento_tipo_conf as $itemDato) {
        if ($itemDato['tipo'] == 9) {
          $fechaEmision = date_format((date_create($itemDato['valor'])), 'd/m/Y');
        }
      }

      $data = MovimientoNegocio::create()->obtenerUnidadMedida($bienId, $unidadMedidaId, $precioTipoId, $monedaId, $fechaEmision);
      $detalleRequerimientos[$index]['dataUnidadMedida'] = $data;
      $dataBien = BienNegocio::create()->obtenerActivosXMovimientoTipoIdBienId($empresaId, $movimientoTipo[0]["id"], $bienId);
      foreach ($dataBien as $datos) {
        array_push($arrayDataBien, $datos);
      }
    }
    $respuesta->detalleRequerimientos = $detalleRequerimientos;
    $respuesta->count_detalleRequerimientos = count($detalleRequerimientos);
    $respuesta->dataBien = $arrayDataBien;

    return $respuesta;
  }

  public function generarDocumentoPDFOrdenCompraServicio($documentoId, $comentario, $tipoSalidaPDF, $url, $data)
  {
    //$tipoSalidaPDF: F-> guarda local
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;
    $dataMovimientoTipoColumna = $data->movimientoTipoColumna;
    $dataDocumentoRelacion = $data->documentoRelacionado;

    $ubigeoProveedor = PersonaNegocio::create()->obtenerUbigeoXId($dataDocumento[0]["ubigeo_id"]);

    $referencia = null;
    $cotizacion = null;
    $terminos_de_pago = null;
    $entrega_en_destino = null;
    $entrega_en_destino_id = null;
    $U_O = null;
    $cuenta = null;

    foreach ($documentoDatoValor as $index => $item) {
      switch ($item['tipo'] * 1) {
        case 2:
          if ($item['descripcion'] == "Referencia") {
            $referencia = $item['valor'];
          } else if ($item['descripcion'] == "Cotización") {
            $cotizacion = $item['valor'];
          }
          break;
        case 50:
          $terminos_de_pago = $item['valor'];
          break;
        case 45:
          $entrega_en_destino = $item['valor'];
          $entrega_en_destino_id = $item["valor_codigo"];
          break;
        case 46:
          $U_O = $item['valor'];
          break;
        case 47:
          $cuenta = $item['valor'];
          break;
      }
    }

    $organizador_entrega =  OrganizadorNegocio::create()->getOrganizador($entrega_en_destino_id);
    $ubigeoProveedor_entrega = PersonaNegocio::create()->obtenerUbigeoXId($dataDocumento[0]["ubigeo_id"]);


    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator('Soluciones Mineras S.A.C.');
    $pdf->SetAuthor('Soluciones Mineras S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    //        $pdf->SetHeaderData('logo.PNG', PDF_HEADER_LOGO_WIDTH,strtoupper($dataDocumentoTipo[0]['descripcion']),$dataEmpresa[0]['razon_social']."\n".$dataEmpresa[0]['direccion'], array(0,0,0), array(0,0,0));

    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }

    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];

    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(150, 40, 'ASOCIACION DE MINEROS ARTESANALES PEPAS DE ORO DE', 0, 'C', 1, 0, '', 10, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(150, 40, 'PAMPAMARCA', 0, 'C', 1, 0, '', 15, true, 0, false, true, 5, 'M');

    $pdf->Image('C:\wamp64\www\minaApp\vistas\images\logo_pepas_de_oro.png', 150, 10, 45, 20, '', '', '', false, 300, '', false, false, 1);

    $pdf->SetFont('helvetica', '', 6);
    $pdf->MultiCell(120, 5, 'PZA.PLAZA DE ARMAS PAMPAMARCA NRO. S/N ANX. PAMPAMARCA', 0, 'L', 1, 0, '', 30, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(120, 5, '(COMUNIDAD DE PAMPAMARCA) APURIMAC - AYMARAES - COTARUSE', 0, 'L', 1, 0, '', 33, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(120, 5, '(051) 950398232', 0, 'L', 1, 0, '', 36, true, 0, false, true, 5, 'M');


    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->MultiCell(30, 5, 'Fecha', 0, 'C', 1, 0, 115, 34, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(50, 5, date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y'), 0, 'C', 1, 0, 145, 34, true, 0, false, true, 5, 'M');

    $pdf->MultiCell(30, 5, 'No.', 0, 'C', 1, 0, 115, 38, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, $dataDocumento[0]['serie'] . " - " . $dataDocumento[0]['numero'], 0, 'C', 1, 0, 145, 38, true, 0, false, true, 5, 'M');

    //
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->MultiCell(30, 5, 'Proveedor', 0, 'L', 1, 0, '', 45, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(90, 4, $dataDocumento[0]['nombre'], 0, 'L', 1, 0, '', 50, true, 0, false, true, 4, 'M');
    $pdf->MultiCell(90, 4, $dataDocumento[0]['codigo_identificacion'], 0, 'L', 1, 0, '', 54, true, 0, false, true, 4, 'M');
    $pdf->MultiCell(90, 4, $dataDocumento[0]['direccion'], 0, 'L', 1, 0, '', 58, true, 0, false, true, 4, 'M');
    $pdf->MultiCell(90, 4, $ubigeoProveedor[0]['ubigeo_dist'], 0, 'L', 1, 0, '', 62, true, 0, false, true, 4, 'M');
    $pdf->MultiCell(90, 4, $ubigeoProveedor[0]['ubigeo_dep'], 0, 'L', 1, 0, '', 66, true, 0, false, true, 4, 'M');

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->MultiCell(50, 5, 'Dirección de entrega', 0, 'L', 1, 0, 115, 45, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(90, 4, 'ASOCIACION DE MINEROS ARTESANALES PEPAS DE ORO DE PAMPAMARCA', 0, 'L', 1, 0, 115, 50, true, 0, false, true, 4, 'M');
    $pdf->MultiCell(90, 4, '20490115804', 0, 'L', 1, 0, 115, 54, true, 0, false, true, 4, 'M');
    $pdf->MultiCell(90, 4, $organizador_entrega[0]["direccion"], 0, 'L', 1, 0, 115, 58, true, 0, false, true, 4, 'M');
    $pdf->MultiCell(90, 4, $ubigeoProveedor_entrega[0]['ubigeo_dist'], 0, 'L', 1, 0, 115, 62, true, 0, false, true, 4, 'M');
    $pdf->MultiCell(90, 4, $ubigeoProveedor_entrega[0]['ubigeo_dep'], 0, 'L', 1, 0, 115, 66, true, 0, false, true, 4, 'M');


    $pdf->SetFillColor(217, 217, 217);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(45, 5, 'Entrega en destino', 1, 'C', 1, 0, '', 74, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(45, 10, $entrega_en_destino, 1, 'C', 1, 0, '', 79, true, 0, false, true, 10, 'M'); //verificar

    $pdf->SetFillColor(217, 217, 217);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(45, 5, 'Términos de pago', 1, 'C', 1, 0, 60, 74, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(45, 10, $terminos_de_pago, 1, 'C', 1, 0, 60, 79, true, 0, false, true, 10, 'M'); //verificar


    $pdf->SetFillColor(217, 217, 217);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(45, 5, 'Solicitado por', 1, 'C', 1, 0, 105, 74, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(45, 10, '', 1, 'C', 1, 0, 105, 79, true, 0, false, true, 10, 'M'); //verificar


    $serieNumeroCotizacion = '';
    $serieNumeroSolicitudRequerimiento = '';
    $dataRelacionada = DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoId($documentoId);
    foreach($dataRelacionada as $itemRelacion){
      if($itemRelacion['documento_tipo_id'] == Configuraciones::COTIZACIONES){
        $serieNumeroCotizacion = $itemRelacion['serie_numero'];
      }
      if($itemRelacion['documento_tipo_id'] == Configuraciones::SOLICITUD_REQUERIMIENTO){
        $serieNumeroSolicitudRequerimiento .= $itemRelacion['serie_numero']. ", ";
      }
    }

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(45, 5, 'REQUERIMIENTO:', 1, 'L', 1, 0, '', 89, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(90, 5, $serieNumeroSolicitudRequerimiento, 1, 'L', 1, 0, 60, 89, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(45, 5, 'U.O:', 1, 'L', 1, 0, '', 94, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(90, 5, $U_O, 1, 'L', 1, 0, 60, 94, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(45, 5, 'REFERENCIA :', 1, 'L', 1, 0, '', 99, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(90, 5, $referencia, 1, 'L', 1, 0, 60, 99, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(45, 5, 'GENERADO POR:', 1, 'L', 1, 0, '', 104, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(90, 5, $dataDocumento[0]['usuario'], 1, 'L', 1, 0, 60, 104, true, 0, false, true, 5, 'M'); //verificar
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(45, 5, 'COTIZACION:', 1, 'L', 1, 0, '', 109, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(90, 5, $serieNumeroCotizacion, 1, 'L', 1, 0, 60, 109, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(45, 5, 'CUENTA:', 1, 'L', 1, 0, '', 114, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', '', 7);
    $pdf->MultiCell(90, 5, 'PEPAS', 1, 'L', 1, 0, 60, 114, true, 0, false, true, 5, 'M');



    $cont = 0;
    $pdf->Ln(8);
    $pdf->SetFont('helvetica', '', 5);
    $tabla = '<table cellspacing="0" cellpadding="1" border="1">
        <tr style="background-color:rgb(254, 191, 0);">
            <th style="text-align:center;vertical-align:middle;" width="5%"><b>Item</b></th>
            <th style="text-align:center;vertical-align:middle;" width="10%"><b>Codigo</b></th>
            <th style="text-align:center;vertical-align:middle;" width="35%"><b>Descripcion</b></th>
            <th style="text-align:center;vertical-align:middle;" width="10%"><b>Cantidad</b></th>
            <th style="text-align:center;vertical-align:middle;" width="6%"><b>U.m</b></th>
            <th style="text-align:center;vertical-align:middle;" width="12%"><b>Valor Unitario</b></th>
            <th style="text-align:center;vertical-align:middle;" width="10%"><b>Totales</b></th>
            <th style="text-align:center;vertical-align:middle;" width="12%"><b>Unidad Minera</b></th>
        </tr>
    ';
    if (!ObjectUtil::isEmpty($detalle)) {
      foreach ($detalle as $index => $item) {
        $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleObtenerUnidadMinera($item->movimientoBienId);
        $cont++;
        // if (strlen($item->descripcion) > 39) {
        //   $cont++;
        // }

        $tabla = $tabla . '<tr>'
          . '<td style="text-align:center"  width="5%">' . ($index + 1) . '</td>'
          . '<td align="center" width="10%">' . $item->bien_codigo . '</td>'
          . '<td style="text-align:left; vertical-align:middle; display: table-cell;" width="35%">' . $item->descripcion . '</td>'
          . '<td style="text-align:center"  width="10%">' . number_format($item->cantidad, 2) . '</td>'
          . '<td style="text-align:center"  width="6%">' . $item->simbolo . '</td>'
          . '<td style="text-align:center"  width="12%">' . number_format($item->precioUnitario, 2) . '</td>'
          . '<td style="text-align:center"  width="10%">' . number_format($item->importe, 2) . '</td>'
          . '<td style="text-align:center"  width="12%">' . $resMovimientoBienDetalle[0]['cantidad_requerimiento'] . '</td>'
          . '</tr>';
      }
    }

    for ($i = count($detalle); $i < 20; $i++) {
      $tabla = $tabla . '<tr>'
        . '<td style="text-align:center"  width="5%">' . ($i + 1) . '</td>'
        . '<td style="text-align:left"  width="10%"></td>'
        . '<td style="text-align:left"  width="35%"></td>'
        . '<td style="text-align:center"  width="10%"></td>'
        . '<td style="text-align:center"  width="6%"></td>'
        . '<td style="text-align:center"  width="12%"></td>'
        . '<td style="text-align:center"  width="10%"></td>'
        . '<td style="text-align:center"  width="12%"></td>'
        . '</tr>';
    }


    $tabla = $tabla . '</table>';

    $pdf->writeHTML($tabla, true, false, true, false, '');
    $tablaHeight = $pdf->GetY(); 
    $espacio = 0;  // Inicializar el espacio
    $paginaAltura = $pdf->getPageHeight();  // Altura total de la página
    $alturaDisponible = $paginaAltura - $tablaHeight - 20; 
    // Ahora puedes ajustar el valor de $espacio basado en el espacio disponible
    if ($alturaDisponible > 70) {
      // Si hay mucho espacio, usa ese espacio
      $espacio = $tablaHeight + 5;  // Ajusta un pequeño margen después de la tabla
    } else {
      // Si el espacio es limitado, podrías agregar una nueva página
      $pdf->AddPage();
      $espacio = 15;  // Nuevo espacio al inicio de la nueva página
    }
    // Establecer la marca de agua de texto
    // $pdf->SetAlpha(0.3); // Opcional: Ajusta la opacidad (0 es totalmente transparente, 1 es opaco)
    // $pdf->SetFont('helvetica', 'B', 50); // Fuente, estilo y tamaño
    // $pdf->SetTextColor(150, 150, 150); // Color de la marca de agua (gris claro)
    // $pdf->Rotate(45, 105, 150); // Rotar el texto para la marca de agua (45 grados)
    // $pdf->SetDrawColor(255, 255, 255); // Establecer el color de dibujo (blanco, para evitar cualquier borde)
    // $pdf->SetLineWidth(0); // Establecer el grosor del borde a 0
    // $pdf->Text(50, 120, 'Sin aprobar', false, false, true, ''); // Especifica la posición del texto

    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(18, 5, 'MONEDA:', 0, 'L', 1, 0, 105, $espacio, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', '', 6);
    $pdf->MultiCell(11, 5, $dataDocumento[0]["moneda_descripcion"], 0, 'L', 1, 0, 123, $espacio, true, 0, false, true, 5, 'M'); //revisar

    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(21, 5, 'SUBTOTAL', 1, 'L', 1, 0, 134, $espacio + 5, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(21, 5, 'IGV 18%', 1, 'L', 1, 0, 134, $espacio + 10, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(21, 5, 'TOTAL', 1, 'L', 1, 0, 134, $espacio + 15, true, 0, false, true, 5, 'M');

    $pdf->MultiCell(22, 5, number_format($dataDocumento[0]['subtotal'], 2), 1, 'R', 1, 0, 155, $espacio + 5, true, 0, false, true, 5, 'M'); //Revisar
    $pdf->MultiCell(22, 5, number_format($dataDocumento[0]['igv'], 2), 1, 'R', 1, 0, 155, $espacio + 10, true, 0, false, true, 5, 'M'); //Revisar
    $pdf->MultiCell(22, 5, number_format($dataDocumento[0]['total'], 2), 1, 'R', 1, 0, 155, $espacio + 15, true, 0, false, true, 5, 'M'); //Revisar



    $pdf->MultiCell(90, 5, 'Intrucciones', 1, 'L', 1, 0, '', $espacio, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(90, 5, '* Entrega del bien con GR,OC/OS  y  FACTURA, sino no se recepcionará.', 1, 'L', 1, 0, '', $espacio + 5, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(90, 5, '* En la guia de remision mencionar el numero de orden de compra', 1, 'L', 1, 0, '', $espacio + 10, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(90, 5, '* incluye IGV', 1, 'L', 1, 0, '', $espacio + 15, true, 0, false, true, 5, 'M');


    $pdf->SetFont('helvetica', '', 4);
    $pdf->MultiCell(70, 3, '*El lugar de entrega se coordinará con el Comprador.', 0, 'L', 1, 0, '', $espacio + 26, true, 0, false, true, 3, 'M');
    $pdf->MultiCell(70, 3, '*Para aclaraciones contactar con el comprador :', 0, 'L', 1, 0, '', $espacio + 29, true, 0, false, true, 3, 'M');
    $pdf->SetFont('helvetica', 'B', 4);
    $pdf->MultiCell(70, 3, 'Procedimiento para presentación de facturas y comprobantes de pago', 0, 'L', 1, 0, '', $espacio + 32, true, 0, false, true, 3, 'M');
    $pdf->SetFont('helvetica', '', 4);
    $pdf->MultiCell(70, 3, '• Validación SUNAT para Comprobantes electrónicos.', 10, 'L', 1, 0, '', $espacio + 35, true, 0, false, true, 3, 'M');
    $pdf->MultiCell(70, 3, '• Validación de emisor electrónicos para Comprobantes físicos.', 0, 'L', 1, 0, '', $espacio + 38, true, 0, false, true, 3, 'M');
    $pdf->MultiCell(70, 3, '• En el caso de facturas electrónicas deben remitir el archivo en pdf y xml.', 0, 'L', 1, 0, '', $espacio + 41, true, 0, false, true, 3, 'M');
    $pdf->MultiCell(70, 3, '• Copia de la Orden de Compra.', 0, 'L', 1, 0, '', $espacio + 44, true, 0, false, true, 3, 'M');
    $pdf->MultiCell(70, 3, '• Acta de conformidad y/o Liquidación en el caso de ser un servicio.', 0, 'L', 1, 0, '', $espacio + 47, true, 0, false, true, 3, 'M');
    $pdf->MultiCell(70, 3, '• Guía de remisión con sello de recepción o conformidad.', 0, 'L', 1, 0, '', $espacio + 50, true, 0, false, true, 3, 'M');


    //
    $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoXArea($documentoTipoId, null);
    $usuario_estado = DocumentoNegocio::create()->obtenerDocumentoDocumentoEstadoXdocumentoId($documentoId, "0,1");

    $resultadoMatriz = [];

    foreach ($matrizUsuario as $key => $value) {
      if ($usuario_estado[$key]["estado_descripcion"] == "Registrado") {
        $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
        $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" => $usuario_estado[$key]["nombre"], "fecha" => $usuario_estado[$key]["usuario_creacion"]);
      } else {
        switch ($value["nivel"]) {
          case "1":
            foreach ($usuario_estado as $val) {
              if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"]);
              }
            }
            break;
          case "2":
            foreach ($usuario_estado as $val) {
              if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"]);
              }
            }
            break;
          case "3":
            foreach ($usuario_estado as $val) {
              if ($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado") {
                $persona = Persona::create()->obtenerPersonaXUsuarioId($usuario_estado[$key]["usuario_creacion"]);
                $resultadoMatriz[$key] = array("usuario_id" => $usuario_estado[$key]["usuario_creacion"], "firma_digital" => $persona[0]['firma_digital'], "nombre" =>  $usuario_estado[$key]["persona_nombre"]);
              }
            }
            break;
        }
      }
    }

    $personaFirma0 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[0]['firma_digital'] . "png";
    $personaFirma1 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[1]['firma_digital'] . "png";
    $personaFirma2 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[2]['firma_digital'] . "png";
    $personaFirma3 = __DIR__ . "/../../vistas/com/persona/firmas/" . $resultadoMatriz[3]['firma_digital'] . "png";

    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(39, 5, 'Autorizado por.', 0, 'C', 1, 0, 90, $espacio + 25, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(47, 8, '', 1, 'C', 1, 0, 130, $espacio + 25, true, 0, false, true, 8, 'M'); //Revisar
    $pdf->Image($personaFirma1, 47, 130, $espacio + 25, 20, '', '', '', false, 300, '', false, false, 1);
    $pdf->SetFont('helvetica', '', 6);
    $pdf->MultiCell(39, 3, 'JEFE DE LOGISTICA', 0, 'C', 1, 0, 90, $espacio + 30, true, 0, false, true, 3, 'M');

    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(39, 5, 'Autorizado por.', 0, 'C', 1, 0, 90, $espacio + 35, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(47, 8, '', 1, 'C', 0, 0, 130, $espacio + 35, true, 0, false, true, 8, 'M'); //Revisar
    $pdf->SetFont('helvetica', '', 6);
    $pdf->MultiCell(39, 3, 'COMPRADOR', 0, 'C', 1, 0, 90, $espacio + 40, true, 0, false, true, 3, 'M');

    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(39, 5, 'Autorizado por.', 0, 'C', 1, 0, 90, $espacio + 45, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(47, 8, '', 1, 'C', 0, 0, 130, $espacio + 45, true, 0, false, true, 8, 'M'); //Revisar
    $pdf->SetFont('helvetica', '', 6);
    $pdf->MultiCell(39, 3, 'GERENTE GENERAL', 0, 'C', 1, 0, 90, $espacio + 50, true, 0, false, true, 3, 'M');

    $pdf->SetFont('helvetica', '', 4);
    $pdf->MultiCell(150, 2, 'El horario de recepción es de lunes a viernes de 8:00 am a 1:00 pm; los documentos que envíen después de este horario o los días sábados, domingos y feriados serán considerados como recibidos a partir', 0, 'L', 1, 0, '', $espacio + 55, true, 0, false, true, 2, 'M');
    $pdf->MultiCell(150, 2, 'del siguiente día hábil y deberán ser remitidos a la siguiente dirección de correo electrónico ', 0, 'L', 1, 0, '', $espacio + 57, true, 0, false, true, 2, 'M');
    $pdf->MultiCell(150, 2, 'El pago es semanal todos los jueves, se programarán todos los comprobantes que cumplan con el procedimiento solicitado y hayan sido emitidos y registrados hasta el martes previo.', 0, 'L', 1, 0, '', $espacio + 59, true, 0, false, true, 2, 'M');


    $html1 = '<strong>1. Generalidades y Objeto</strong> <br>
    1.1. Las presentes Condiciones Generales de Compra (en adelante "Condiciones Generales") tienen por objeto regular las relaciones entre la Persona Jurídica (en adelante, el "COMPRADOR") que ordena la compra de bienes (los "Bienes") objeto de la Orden de Compra (la "OC") a la cual se adjunta las presentes Condiciones Generales   y quien aparece en la OC (en adelante el "PROVEEDOR") a cargo de proveer los Bienes requeridos por el COMPRADOR. <br>
    1.2. La OC emitida por el COMPRADOR constituye una oferta para la compra de los Bienes y la prestación de los Servicios de acuerdo a los términos y condiciones, allí indicados. El PROVEEDOR debe expresar su aceptación a la oferta mediante la firma por el representante legal y envío de la OC al COMPRADOR dentro del plazo máximo de 10 (diez) días de recibida la OC. El Contrato queda perfeccionado en el momento y lugar que la aceptación es conocida por el COMPRADOR. En cualquier caso, el Contrato queda concluido en el momento y lugar en que se inicia la ejecución de la prestación a cargo del PROVEEDOR. <br>
    1.3. La OC podrá ser revocada hasta que se perfeccione el Contrato si la revocación llega al destinatario antes que éste haya enviado la aceptación. <br>
    1.4. La aceptación de la OC no surtirá efectos si no llega al COMPRADOR dentro del plazo señalado en la cláusula 1.2. La aceptación tardía surtirá, sin embargo, efecto como aceptación si el oferente, sin demora, informa verbalmente de ello al destinatario o le envía una comunicación en tal sentido. <br>
    1.5. La respuesta a una oferta que pretenda ser una aceptación y que contenga adiciones, limitaciones u otras modificaciones se considerará como rechazo de la oferta y constituirá una contraoferta. <br>
    1.6. Ninguno de los términos, condiciones, excepciones o aclaraciones indicados por el PROVEEDOR en su cotización, propuesta o aceptación de la OC, serán vinculantes a menos que sean incorporados expresamente y por escrito en el Contrato por el COMPRADOR. <br><br>';

    $html2 = '<strong>2. Ejecución del Contrato</strong> <br>
    2.1. Bienes. - <br>
    2.1.1. El PROVEEDOR deberá entregar Bienes cuya cantidad, calidad y tipo correspondan a los señalados en la OC y que estén envasados o embalados en la forma fijada por la OC. <br>
    2.1.2. Salvo que la OC haya señalado otra cosa, los Bienes no serán conformes a la OC a menos: <br>
    a) Que sean aptos para los usos a que ordinariamente se destinen Bienes del mismo tipo; <br>			
    b) que sean aptos para cualquier uso especial que expresa o tácitamente se haya hecho saber al PROVEEDOR al solicitarle una cotización o en la OC; <br>
    c) que posean las cualidades de la muestra o modelo que el PROVEEDOR haya presentado al COMPRADOR; <br>
    d) que estén envasados o embalados en la forma habitual para tales Bienes o, si no existe tal forma, de una forma adecuada para conservarlos y protegerlos. <br>
    2.2. Información Suficiente. - El PROVEEDOR declara que ha tomado conocimiento de todos los hechos y circunstancias relevantes para la ejecución de sus obligaciones contractuales. En ningún, caso el PROVEEDOR tendrá derecho a beneficio alguno debido a la falta de información con relación a las condiciones para la ejecución de las cuales podría haber obtenido los detalles necesarios o aclaraciones previo requerimiento hecho en su debida oportunidad. <br>
    2.3. Responsabilidad por la Ejecución del Contrato. - El PROVEEDOR será responsable por la interpretación que haga de la documentación e información relacionada con la OC. Cualquier participación del COMPRADOR en la selección del algún proveedor con relación a los Bienes, cualquier documento, información, materiales o software o revisión o aprobación de los mismos por el COMPRADOR no liberará al PROVEEDOR de su obligación de entregar los Bienes conforme lo requerido en la OC. <br>
    2.4. Autorizaciones, Licencias y Permisos. - El PROVEEDOR deberá obtener a su cuenta, costo y riesgo todas las autorizaciones, licencias y permisos necesarios para la ejecución del Contrato. <br>
    2.5. La recepción de los Bienes por el COMPRADOR no supone la conformidad de los mismos. En todo caso, el COMPRADOR perderá el derecho a invocar la falta de conformidad de los Bienes si no lo comunica al PROVEEDOR en un plazo máximo de dos (2) años contados desde la fecha en que los Bienes se pusieron efectivamente en poder del COMPRADOR, a menos que ese plazo sea incompatible con un período de garantía contractual. <br><br>';

    $html3 = '<strong>3. Variaciones</strong> <br>
    El COMPRADOR tendrá derecho en todo momento a requerir cambios o variaciones de los Bienes, mediante el envío de una notificación escrita al PROVEEDOR. En la medida que los cambios o variaciones requeridos por el COMPRADOR razonablemente justifiquen un ajuste del precio, una modificación del cronograma de entrega o una modificación de las presentes Condiciones Generales y si el PROVEEDOR solicita dentro de los 10 (diez) días siguientes a la notificación del COMPRADOR que se efectúe el ajuste respectivo, entonces el COMPRADOR realizará el ajuste razonable que corresponda. El COMPRADOR podrá requerir que el PROVEEDOR comience a ejecutar los cambios o variaciones antes que se termine de realizar cualquier ajuste al Contrato. <br><br>';

    $html4 = '<strong>4. Inspecciones y Control de Calidad</strong> <br>
    4.1. El PROVEEDOR implementará un adecuado y reconocido programa de control de calidad para garantizar que los Bienes cumplan con los requerimientos de la OC y entregar al COMPRADOR todos los certificados de pruebas y otra documentación que sea requerida en virtud del Contrato o que el COMPRADOR razonablemente requiera. El PROVEEDOR informará al COMPRADOR con la debida anticipación de todas las pruebas a realizarse y el COMPRADOR o cualquier tercero autorizado por el COMPRADOR tendrá derecho a participar en dichas pruebas. <br>
    4.2. El COMPRADOR o cualquier tercero autorizado por el COMPRADOR tendrá derecho a realizar inspecciones y pruebas en cualquier momento que sea razonable y el PROVEEDOR les facilitará completo y libre acceso a los respectivos locales del PROVEEDOR, sus subcontratistas o proveedores. Al momento de recepción de los Bienes, el COMPRADOR podrá, según lo considere, inspeccionarlos en dicho momento o en cualquier momento posterior. Si el Contrato incluye la realización de pruebas a los Bienes, entonces la ejecución del Contrato no se considerará completa hasta que dichas pruebas hayan sido superadas a entera satisfacción del COMPRADOR. <br>
    4.3. Ni la aprobación por parte del COMPRADOR de cualquier prueba, ni cualquier inspección o prueba realizada por el COMPRADOR, ni la no realización u omisión de las mismas, liberará al PROVEEDOR de su responsabilidad de cumplir con el Contrato ni implicará la conformidad por parte del COMPRADOR de los Bienes. <br><br>';

    $html5 = '<strong>5. Documentación</strong> <br>
    El PROVEEDOR entregará todos los manuales de operación y mantenimiento, planos, dibujos, cálculos, documentación técnica, diagramas lógicos, reportes de avance, certificados de calidad, cartas de porte, cartas de embarque, certificados de origen, autorizaciones de exportación y licencias, y cualquier otro documento requerido por la OC o por estas Condiciones Generales o la normativa aplicable. En caso que el COMPRADOR lo requiera, el PROVEEDOR deberá entregar cualquiera de dichos documentos al COMPRADOR para su revisión y aprobación. La ejecución del Contrato no se considerará completa hasta que se haya entregado toda la documentación requerida de acuerdo con la OC o con las presentes Condiciones Generales. <br><br>';

    $html6 = '<strong>6. Transporte</strong> <br>
    6.1. El PROVEEDOR, si estuviere obligado a disponer el transporte de los Bienes, deberá concertar los contratos necesarios para que éste se efectúe hasta el lugar señalado por los medios de transporte adecuados a las circunstancias y en las condiciones usuales para tal transporte. <br>
    6.2. Independientemente de quien esté a cargo del transporte, el PROVEEDOR deberá cumplir con las instrucciones de embarque, embalaje y marcas y manejo de materiales provisto por el COMPRADOR, sin perjuicio del cumplimiento de los requerimientos establecidos por la normatividad que resulten aplicables al respectivo tipo de transporte. El PROVEEDOR deberá entregar al COMPRADOR en la debida oportunidad documentación de transporte detallada y exacta en la medida que lo requiera el COMPRADOR. <br><br>';

    $html7 = '<strong>7. Transferencia del Riesgo y Propiedad</strong> <br>
    La transferencia del riesgo de pérdida o daños con relación a los Bienes se transferirá al momento de la entrega de los mismos al COMPRADOR. La propiedad de los Bienes pasará al COMPRADOR al momento de su entrega. <br><br>';

    $html8 = '<strong>8. Cronograma y Demoras</strong> <br>
    8.1. El PROVEEDOR garantiza que ejecutará el Contrato de manera oportuna de conformidad con la OC. El PROVEEDOR deberá comunicar inmediatamente y por escrito al COMPRADOR tan pronto tome conocimiento de algún evento o circunstancia que demore o pueda demorar el cumplimiento de sus obligaciones contractuales más allá de la fecha determinada en la OC. Dicha notificación deberá incluir una propuesta con medidas para acelerar la ejecución contractual para cumplir en la fecha acordada; incluyendo, si se hace necesario, horas de trabajo adicional, trabajo durante fines de semana y feriados, envío por medios más rápidos (vía aérea, etc.). El costo de dichas medidas será asumido por el PROVEEDOR, salvo que dichas demoras sean responsabilidad únicamente del COMPRADOR. <br>
    8.2. En caso que la entrega de los Bienes en el lugar de destino (incluyendo toda la documentación respectiva) se demore más allá de la fecha acordada, el PROVEEDOR deberá pagar, a menos que se acuerde algo distinto por escrito, una penalidad por retraso. <br>
    La penalidad se aplicará al PROVEEDOR hasta por un monto máximo equivalente al diez por ciento (10%) del monto contractual. Cuando se llegue a cubrir el monto máximo de la penalidad, el COMPRADOR podrá resolver el Contrato por incumplimiento. <br>
    El pago de la penalidad es sin perjuicio del derecho del COMPRADOR a la debida compensación por cualquier daño ulterior. <br>
    8.3. En caso que el PROVEEDOR se retrase significativamente en el cumplimiento de sus obligaciones, el COMPRADOR podrá enviarle una comunicación requiriendo que cumpla sus obligaciones retrasadas en el periodo de tiempo máximo que indique el COMPRADOR y, si no indicara plazo, en un periodo máximo de quince (15) días. Si el PROVEEDOR incumple con subsanar dicho incumplimiento o si en cualquier caso el máximo de penalidades por demora es alcanzado el COMPRADOR tendrá el derecho de, previa notificación por escrito al PROVEEDOR a resolver el Contrato en todo o en parte de conformidad con la Cláusula 18.1.1. <br><br>';

    $html9 = '<strong>9. Precio y Pagos</strong> <br>
    9.1. Los precios indicados en la cotización del PROVEEDOR y que se recogen en la OC son firmes y fijos y representan la única contraprestación a la que tiene derecho el PROVEEDOR por la ejecución del Contrato. Dichos precios incluyen: el envío de los Bienes de acuerdo con la OC; todos los tributos, derechos, aranceles y similares aplicables a la ejecución del Contrato; todos los costos y gastos relacionados con la ejecución del Contrato, incluyendo, gastos de viaje, contribuciones o pagos a cualquier organización, prima de seguro y, de manera general, cualquier riesgo, costo, gasto y contingencia del PROVEEDOR. <br>
    9.2. Las facturas presentadas por el PROVEEDOR reunirán todos los requisitos establecidos por la legislación vigente sobre la materia, siendo el PROVEEDOR el único responsable por el cumplimiento de tal obligación y de no cumplir con tales requisitos, el COMPRADOR podrá retener el pago respectivo. <br>
    9.3. El COMPRADOR podrá realizar el pago del precio empleando cualquier medio de pago permitido por la normatividad aplicable. En caso el COMPRADOR vaya a realizar el pago mediante depósito en cuenta, el PROVEEDOR deberá indicar la institución financiera y el número de cuenta cuando le sea requerido por el COMPRADOR. La demora del PROVEEDOR en proporcionar dichos datos se considera causa no imputable al COMPRADOR en el retardo del pago respectivo. <br>
    9.4. Cuando el pago se efectúe mediante cheque, dicha entrega tendrá efecto cancelatorio. <br>
    9.5. A menos que se indique algo distinto en la OC, el PROVEEDOR tendrá derecho a facturar por el pago por los Bienes sólo cuando hayan sido entregados en su totalidad. Los pagos que deba efectuar el COMPRADOR serán realizados en el plazo determinado en la OC, luego de la recepción de la factura del PROVEEDOR adjuntando la documentación relevante en el domicilio del COMPRADOR. El COMPRADOR no estará obligado a efectuar ningún pago al PROVEEDOR si éste se encuentra en incumplimiento del Contrato y por tanto tiempo como dure dicho incumplimiento. El pago del COMPRADOR no será considerado como una conformidad de los Bienes. Si el COMPRADOR se demorase en el pago de cualquier suma vencida y exigible, el PROVEEDOR, como única compensación, tendrá derecho a cobrar intereses sobre el monto impago desde la fecha de vencimiento de la factura respectiva hasta la fecha de pago, aplicando para tales fines la tasa de interés legal establecida por el Banco Central de Reserva (BCR) y publicada por la Superintendencia de Banca y Seguros (SBS) para operaciones ajenas al Sistema Financiero aplicable a la fecha de vencimiento de la factura. <br>
    9.6. El PROVEEDOR no tendrá derecho a compensar ninguno de los montos que adeude al COMPRADOR contra reclamos que pudiera tener contra el COMPRADOR a no ser que dichos reclamos hayan sido consentidos expresamente y por escrito por el COMPRADOR o hayan sido resueltos totalmente a favor del PROVEEDOR, conforme a lo dispuesto en la Cláusula 22 (Resolución de Conflictos). <br>
    9.7. Una vez emitido el comprobante de pago respectivo, el PROVEEDOR tendrá un plazo de quince (15) días calendarios para revisar, aprobar o, de ser el caso, observar de manera sustentada dichos montos. Vencido dicho plazo sin observación formal y por escrito del PROVEEDOR, se tendrán por aceptados todos los montos, facturas y descuentos, sin admitir prueba en contrario, y, en consecuencia, el PROVEEDOR no tendrá derecho a reclamo posterior alguno al COMPRADOR en relación a los mismos. <br><br>';

    $html10 = '<strong>10. Garantía de Calidad</strong> <br>
    10.1. Adicionalmente y sin perjuicio de todas las demás garantías otorgadas por el PROVEEDOR en el Contrato, el PROVEEDOR garantiza que los Bienes: (a) estarán libres de defectos o falta de conformidad con relación al diseño, mano de obra o materiales y que se encontrarán en estricta conformidad con todos los requerimientos de la OC, (b) serán entregados (y de ser necesario , instalados) de manera segura y profesional por personal calificado y eficiente y que serán de la más alta calidad profesional, para lo cual el PROVEEDOR posee la experiencia y conocimientos necesarios, instalaciones y equipos requeridos para ejecutar sus obligaciones de acuerdo con el Contrato, y (c) estarán libres de reclamos y contingencias de cualquier naturaleza, incluyendo reclamos relacionados con su propiedad. <br>
    10.2. Salvo indicación distinta en la OC, el período de garantía será de treinta y seis (36) meses computados desde la recepción de los Bienes por el COMPRADOR en el lugar indicado en la OC. Los Bienes reemplazados o reparados estarán sujetos a un nuevo período de garantía de veinticuatro (24) meses computados desde la fecha en que los Bienes reemplazados o reparados sean aceptados y recibidos en la Unidad de Producción a satisfacción del COMPRADOR. <br>
    10.3. Si durante el período de garantía se identifica que alguna parte de los Bienes es defectuosa o no cumple con lo requerido en la OC, el COMPRADOR podrá a su elección, requerir que el PROVEEDOR: (i) remedie los Bienes defectuosos a la cuenta, costo y riesgo del PROVEEDOR; (ii) exigir la entrega de otros Bienes en sustitución de los defectuosos; u (iii) optar por aceptar los Bienes defectuosos sujeto a una reducción equitativa del precio del Contrato. Si el PROVEEDOR no cumple con remediar el defecto con la debida diligencia y dentro del tiempo indicado por el COMPRADOR (o en caso no se haya indicado, dentro de un período razonable luego del requerimiento del COMPRADOR) o si las circunstancias lo justifican razonablemente, el COMPRADOR podrá remediar los defectos por sí mismo o a través de un tercero a cuenta, costo y riesgo del PROVEEDOR. En caso que el defecto sea de tal envergadura que los Bienes no puedan ser usados para el fin para el cual pretendían ser destinados o dicho uso se encuentra significativamente afectado o en caso de un defecto recurrente, el COMPRADOR podrá rechazar los Bienes y requerir el reembolso de cualquier suma pagada, más intereses. Los remedios indicados en esta Cláusula no excluyen otros derechos y remedios que estén a disposición del COMPRADOR, incluyendo el derecho a resolver el Contrato conforme lo establecido en la Cláusula 18.1.1. <br>
    10.4. El COMPRADOR podrá notificar sobre los defectos descubiertos durante el período de garantía, en cualquier momento, en la medida que lo haga hasta treinta (30) días después del vencimiento del período de garantía. Cualquier reclamo o remedios relacionados con defectos que se notifiquen de acuerdo a lo indicado en este párrafo podrán ser hechos valer por el COMPRADOR durante un período de cinco (5) años posteriores a la fecha en que el COMPRADOR notificó sobre el defecto. <br><br>';

    $html11 = '<strong>11. Caso Fortuito o Fuerza Mayor</strong> <br>
    11.1. Si la ejecución del Contrato, en todo o en parte, es impedida temporalmente debido a un caso fortuito o evento de fuerza mayor, entonces el tiempo para la ejecución de las obligaciones de la Parte afectada será modificado consecuentemente, siempre y cuando la Parte afectada informe a la brevedad posible (pero en ningún caso luego de más de tres (3) días) a la otra Parte sobre el evento y tome todas las medidas razonables que estén a su alcance para reducir la demora respectiva. En todos los casos, la Parte afectada deberá esforzarse al máximo para continuar llevando a cabo sus obligaciones de acuerdo con el Contrato hasta donde sea razonablemente posible. <br>
    11.2. Cualquier extensión de plazo que corresponda en virtud a lo indicado en el numeral 11.1 anterior no podrá ser mayor al plazo que duró el caso fortuito o evento de fuerza mayor. <br>
    11.3. Cualquier costo o gasto adicional en que deba incurrir la Parte afectada como resultado del caso fortuito o evento de fuerza mayor será asumido por ésta. <br>
    11.4. Si por caso fortuito o fuerza mayor, lo cual incluye el cese de operaciones por parte del COMPRADOR, tuviera que paralizarse la ejecución de las prestaciones a cargo de las Partes derivadas del Contrato, por un período igual o mayor a sesenta (60) días calendarios, el COMPRADOR o el PROVEEDOR podrán dar por resuelto el Contrato. El COMPRADOR sólo estará obligado a pagar por los Bienes recibidos hasta la fecha de la referida paralización. <br><br>';

    $html12 = '<strong>12. Confidencialidad</strong> <br>
    12.1. Cada una de las Partes deberá mantener y garantizar que sus trabajadores, subcontratistas, proveedores, asesores y representantes, y cada uno de sus respectivos sucesores y derechohabientes mantengan la confidencialidad de este Contrato y de todos los documentos y demás información técnica de carácter confidencial que le haya sido entregada por o en nombre de la otra Parte en relación con este Contrato y (salvo que lo exija la normatividad aplicable o las normas bursátiles o salvo en el caso de divulgación a agencias de tasación o de créditos a la exportación, entidades crediticias presentes o futuras, aseguradoras o ajustadoras o asesores personales de las Partes o de cualquiera de los terceros mencionados o salvo hasta el punto necesario para resolver una disputa) no lo publicará ni revelará en forma alguna ni lo utilizará para sus propios fines, excepto para cumplir las obligaciones especificadas en este Contrato. <br>
    12.2. Las disposiciones del numeral 12.1 no se aplicarán a: (a) la información que sea de dominio público obtenida de otra forma que no sea mediante el incumplimiento de este Contrato; (b) la información que esté en posesión de la Parte receptora y haya sido obtenida de la otra Parte sin obligación de confidencialidad; y (c) la información que esté en posesión de la Parte receptora y haya sido obtenida de terceros sin obligación de confidencialidad para estos terceros y sin incumplir este Contrato. <br>
    12.3. Las obligaciones de esta Cláusula 12 continuarán en vigor por un período de 5 (cinco) años a partir de la fecha de terminación del último período de garantía de calidad. <br>
    12.4. Excepto cuando se disponga algo diferente en la OC, el PROVEEDOR deberá devolver o destruir, según le indique el COMPRADOR, toda la información confidencial del COMPRADOR que tuviera en su poder. <br><br>';

    $pdf->AddPage();

    $distribucionPagos = OrdenCompraServicio::create()->obtenerDistribucionPagos($documentoId);
    $cont_distribucionPagos = 0;
    $pdf->SetFont('helvetica', '', 7);
    $tabla_distribucionPagos = '<table cellspacing="0" cellpadding="1" border="1">
        <tr style="background-color:rgb(254, 191, 0);">
            <th style="text-align:center;vertical-align:middle;" width="5%"><b>Item</b></th>
            <th style="text-align:center;vertical-align:middle;" width="45%"><b>Importe</b></th>
            <th style="text-align:center;vertical-align:middle;" width="50%"><b>Porcentaje</b></th>
        </tr>
    ';
    if (!ObjectUtil::isEmpty($distribucionPagos)) {
      foreach ($distribucionPagos as $index => $item) {
        $cont_distribucionPagos++;

        $tabla_distribucionPagos = $tabla_distribucionPagos . '<tr>'
          . '<td style="text-align:center"  width="5%">' . ($index + 1) . '</td>'
          . '<td style="text-align:center"  width="45%">' . number_format($item['importe'], 2) . '</td>'
          . '<td style="text-align:center"  width="50%">' . number_format($item['porcentaje'], 2) . '</td>'
          . '</tr>';
      }
    }
    $tabla_distribucionPagos = $tabla_distribucionPagos . '</table>';

    $tabla_distribucionPagosTitulo = '<div style="text-align: center;"> <h3>DISTRIBUCIÓN DE PAGOS</h3></div> <div style="text-align: justify;"></div>';
    $pdf->writeHTML($tabla_distribucionPagosTitulo, true, false, true, false, '');

    $pdf->writeHTML($tabla_distribucionPagos, true, false, true, false, '');

    $html_total = '<div style="text-align: center;"> <h3>CONDICIONES GENERALES DE COMPRA</h3></div> <div style="text-align: justify;">' . $html1 . $html2 . $html3 . $html4 . $html5 . $html6 . $html7 . $html8 . $html9 . $html10 . $html11 . $html12 . '</div>';
    $pdf->AddPage();
    $pdf->writeHTML($html_total, true, 0, true, 0);

    $html13 = '<strong>13. Derechos de Propiedad Intelectual</strong> <br>
    13.1. Los diseños, dibujos, especificaciones, instrucciones, manuales y otros documentos creados, producidos o encargados por el COMPRADOR que el PROVEEDOR necesite para la ejecución del Contrato (colectivamente, los "Documentos del COMPRADOR"), así como los derechos de autor sobre los mismos y todos los demás derechos sobre la propiedad industrial e intelectual relacionados con los mismos, son y serán propiedad del COMPRADOR. Los Documentos    del    COMPRADOR no serán utilizados por el PROVEEDOR para otros fines sin la autorización previa y por escrito del COMPRADOR. Sin embargo, podrán ser utilizados para cualquier propósito relacionado con el Contrato.	serán utilizados por el PROVEEDOR para otros fines sin la autorización previa y por escrito del COMPRADOR. Sin embargo, podrán ser utilizados para cualquier propósito relacionado con el Contrato. <br>
    13.2. Los diseños, dibujos, especificaciones, instrucciones, manuales y otros documentos creados, producidos o encargados por o en nombre del PROVEEDOR en relación con el Contrato (colectivamente, los "Documentos del Contrato"), así como los derechos de autor sobre los mismos, y todos los derechos sobre la propiedad industrial e intelectual relacionados con los mismos pasarán a ser propiedad del COMPRADOR. El PROVEEDOR deberá poner a disposición del COMPRADOR, sin costo alguno, (i) todos los libros, registros e inventarios creados, producidos o encargados por o en nombre del PROVEEDOR en los que se identifiquen los Bienes, y (ii) todas las especificaciones relacionadas con los Bienes. <br>
    13.3. El PROVEEDOR deberá en todo momento eximir de responsabilidad e indemnizar al COMPRADOR frente a todas y cada una de las acciones, reclamaciones, demandas, costos, cargos y gastos incurridos o derivados de la infracción o presunta infracción de patentes, diseños registrados, propiedad industrial e intelectual, marca comercial o nombre comercial o cualquier otro derecho similar protegido en el Perú o en otro lugar por el uso o posesión de cualesquiera materiales, suministros, equipos, software, u otros provistos por el PROVEEDOR. <br>
    13.4. Sin perjuicio de lo anterior, si cualquier Bien o pieza de material, equipo o software o su uso constituye una infracción según lo descrito en el numeral 13.2 y su utilización fuera prohibida, el PROVEEDOR deberá a su cuenta, costo y riesgo y tras consultarlo con el COMPRADOR, bien procurar al COMPRADOR el derecho de continuar utilizando o seguir en posesión o de continuar con el uso o posesión del citado Bien, pieza de material, equipo o software o sustituir la misma con un Bien, pieza de material, equipo o software de equivalente o mejor calidad, que no infrinja los derechos o modificarla de forma que no infrinja los derechos. <br>
    13.5. El COMPRADOR tendrá el derecho irrevocable, libre del pago de regalías o derechos e ilimitado a nivel mundial para usar (incluyendo el derecho de transferir tal derecho de uso a terceros) todos los sistemas, programas, documentos, know-how u otros derechos de propiedad intelectual o industrial relacionados con los Bienes. <br><br>';

    $html14 = '<strong>14. Responsabilidades Laborales en caso de prestación de servicios</strong> <br>
    14.1. EL PROVEEDOR prestará los servicios en forma independiente a través de su propia organización de medios y de personas, siendo responsable de la gestión y control de los mismos. <br>
    14.2. EL PROVEEDOR en su condición de empleador, es el único y directo responsable del pago de los honorarios, remuneraciones y beneficios sociales del personal a su cargo, así como de la0s aportaciones y contribuciones laborales a las que está obligado a cumplir. <br>
    14.3. El incumplimiento de cualquiera de las obligaciones señaladas en el punto 14.2 dará derecho a EL COMPRADOR a la retención del pago de cualquier monto que adeude al PROVEEDOR, hasta que solucione el incumplimiento. <br>
    14.4. EL PROVEEDOR, cuando así sea requerido, nombrará a uno o más Residentes o Supervisores, ya sean, profesionales, técnicos o especialistas, debidamente capacitados, quienes deberán permanecer el tiempo que se requiera para la conformidad de la entrega del bien o servicio correspondiente, respetando la jornada laboral máxima permitida que deberá emplear en el área de las labores en las Unidades de Producción. <br>
    14.5. EL PROVEEDOR, cuando desplace personal a las Unidades de Producción, deberá verificar que dicho personal cuente, por lo menos, con el Seguro Complementario de Trabajo de Riesgo (SCTR) <br><br>';

    $html15 = '<strong>15. Medio Ambiente, Salud, Higiene, Seguridad y Ética</strong> <br>
    15.1. El PROVEEDOR declara y garantiza que: (a) los Bienes (incluyendo su empaque) provistos al COMPRADOR y todas sus Partes y componentes, no incluyen arsénico, asbestos, plomo, o cualquier otra sustancia peligrosa o contaminante prohibida por la regulación del lugar de origen o cualquier destino temporal o final de dichos Bienes o por las buenas prácticas de la industria; (b) el PROVEEDOR no deberá permitir o causar que cualquier trabajador del COMPRADOR, sus representantes o terceros se vean expuestos a sustancias peligrosas o contaminantes; (c) los Bienes y Servicios deberán ser entregados junto con todas sus instrucciones, advertencias y cualquier otra información necesaria para que sean operados de una manera segura y apropiada; (d) los Bienes, sus Partes y componentes se encontrarán en estricto cumplimiento con todas las normas de medio ambiente, salud, higiene y seguridad aplicables del lugar de origen o cualquier destino temporal o final de dichos Bienes. En caso de conflicto entre las distintas normas sobre medio ambiente, salud, higiene y seguridad aplicarán aquellas que sean más estrictas. <br>
    15.2. El PROVEEDOR también declara que los Bienes, sus partes y componentes se encuentran, en estricto cumplimiento, con toda la normativa aplicable y la regulación del lugar de origen o cualquier destino temporal o final de dichos Bienes, así como las buenas prácticas de la industria y los códigos y estándares que sean aplicables. <br>
    15.3. El PROVEDOR declara y garantiza que no ha pagado directa o indirectamente cualquier comisión, monto u otorgado descuento a terceras personas, trabajadores del COMPRADOR o clientes del COMPRADOR o efectuados regalos, invitaciones o cualquier otra forma no monetaria de favores o arreglos para hacer negocios con el COMPRADOR o sus empresas vinculadas. <br>
    15.4. Ambas Partes acuerdan que las obligaciones contenidas en la presente	Cláusula constituyen	obligaciones		esenciales.	El PROVEEDOR indemnizará y mantendrá indemne al COMPRADR y sus afiliadas, trabajadores, directores y agentes de cualquier responsabilidad, reclamos, gastos, pérdidas o daños que puedan surgir como resultado o con relación del incumplimiento del PROVEEDOR de sus obligaciones o declaraciones bajo la presente Cláusula. <br>
    15.5. El PROVEEDOR declara conocer las disposiciones contenidas en el Reglamento de Seguridad y Salud Ocupacional, aprobado mediante el Decreto Supremo N° 055-2010-EM o las normas que son aplicables a su rubro comercial, sus normas reglamentarias y modificatorias y asegura su adaptabilidad y capacidad para cumplirlas y hacerlas cumplir de manera integral y sostenida. Cuando corresponda, pasará los exámenes médicos necesarios o requeridos, programas de inducción, entrenamiento y re-entrenamiento para el personal que destaque y contar con la asesoría profesional necesaria. Si es requerido, deberá someter a su personal al programa de inducción de seguridad que proveerá el COMPRADOR. El PROVEEDOR deberá conocer, cumplir, hacer conocer y hacer cumplir a su personal todas y cada una de las especificaciones técnicas y de seguridad impartidas y hacerle conocer el Reglamento Interno de Seguridad y de Trabajo del COMPRADOR, el Reglamento y Políticas de Seguridad y Salud Ocupacional en Minería y el Reglamento y Políticas sobre Protección al Medio Ambiente "Sistema de Gestión Ambiental ISO 14001" implementados por el COMPRADOR, así como aquellas normas que son de obligatorio cumplimiento dispuestas por las Autoridades competentes, como el Ministerio de Energía y Minas, el Ministerio de Transportes y Comunicaciones, el Ministerio del Ambiente o cualquier otra Autoridad, sea nacional, regional o local, a través de la legislación vigente. <br><br>';

    $html16 = '<strong>16. Reclamos de Terceros y Sanciones</strong> <br>
    El PROVEEDOR indemnizará, mantendrá indemne y defenderá al COMPRADOR, sus agentes, directores, trabajadores y empresas vinculadas frente a cualquier reclamo, responsabilidades, sanciones, gastos (incluyendo gastos legales) que surjan de o en relación con la ejecución o inejecución del Contrato incluyendo aquellos relacionados con daños corporales, muerte, daños o destrucción de la propiedad de terceros e infracciones a la normativa aplicable en material de seguridad, salud, higiene o medio ambiente. <br><br>';

    $html17 = '<strong>17. Suspensión</strong> <br>
    En cualquier momento el COMPRADOR podrá ordenar al PROVEEDOR la suspensión de todo o parte de la ejecución del Contrato mediante notificación al PROVEEDOR. El PROVEEDOR deberá tomar todas las medidas que sean necesarias para minimizar los costos, gastos y demoras relacionados con dicha suspensión. En caso que y en la medida que la suspensión supere los 3 (tres) meses continuos el COMPRADOR reembolsará al PROVEEDOR los costos directos (excluyendo cualquier elemento de lucro o margen) atribuible a dicha suspensión, siempre y cuando los mismos sean razonables y se encuentren debidamente documentados. El PROVEEDOR no suspenderá la ejecución del Contrato bajo ninguna razón, excepto con el consentimiento expreso y por escrito del COMPRADOR. <br><br>';

    $html18 = '<strong>18. Resolución de Contrato</strong> <br>
    18.1. Causales de Resolución <br>						
    18.1.1. Sin perjuicio de cualesquiera otros derechos y remedios del COMPRADOR, el COMPRADOR podrá, sin que ello implique responsabilidad alguna para él, terminar todo o parte del Contrato mediante notificación escrita al PROVEEDOR si: (i) el PROVEEDOR se encuentra en incumplimiento de sus obligaciones e incumple con remediarlo dentro de los quince (15) días siguientes a la fecha en que hubiera sido notificado por el COMPRADOR; (ii) el PROVEEDOR se encontrase significativamente retrasado y el COMPRADOR le haya notificado su intención de resolver el Contrato conforme a la Cláusula 8.3 o el monto máximo de penalidades por demora debidas por el PROVEEDOR es alcanzado o es probable que sea alcanzado; (iii) se inicia al PROVEEDOR un procedimiento concursal ordinario,preventivo o cualquier otro de naturaleza concursal; o si el PROVEEDOR es declarado en quiebra; o si el PROVEEDOR entra en liquidación judicial o extrajudicial; o acuerda disolverse; o si se produjera cualquier situación análoga. <br>
    18.1.2. El COMPRADOR tendrá derecho a, en cualquier momento resolver el Contrato, total o parcialmente, por su conveniencia y sin expresión de causa, mediante el envío al PROVEEDOR de una notificación con cinco (5) días de anticipación. La resolución a que se refiere el presente numeral, no da lugar al pago de indemnización alguna a favor del PROVEEDOR, salvo lo indicado en el numeral 18.2.3. <br>
    18.1.3. Si y dentro de un plazo razonable después de la resolución, el COMPRADOR procede a una compra de reemplazo, el COMPRADOR podrá obtener la diferencia entre el precio del contrato y el precio estipulado en la operación de reemplazo, así como cualesquiera otros daños y perjuicios que el incumplimiento del PROVEEDOR le haya ocasionado. <br>
    18.2. Consecuencias de la Resolución <br>
    18.2.1. Tan pronto como el COMPRADOR lo requiera (y en la media que lo requiera), el PROVEEDOR deberá entregar al COMPRADOR los Bienes (terminados o en proceso de fabricación o adquisición), todos los documentos relacionados, información y derechos relacionados con los Bienes que requiera el COMPRADOR para alcanzar la finalidad original del Contrato, directamente o a través de terceros; y hacer y conseguir todo lo que sea necesario para que el COMPRADOR reciba y pueda mantener la propiedad de los Bienes. El PROVEEDOR tendrá derecho a que el COMPRADOR le pague la parte del precio que sea equivalente a los Bienes entregados conforme el Contrato, en la medida en que dicho pago se encontrase pendiente. <br>
    18.2.2. En caso de terminación por incumplimiento del PROVEEDOR el COMPRADOR podrá, a su elección, rechazar todo o parte de los Bienes o completar los Bienes en todo o en parte directamente o a través de terceros por cuenta, costo y riesgo del PROVEEDOR, sin perjuicio del derecho del COMPRADOR de efectuar las compensaciones a que hubiera lugar por los daños y perjuicios que hubiere sufrido como resultado de la terminación del Contrato. <br>
    18.2.3. En caso de resolución por conveniencia (y en la medida en que no hubiera sido cubierto por lo indicado en el numeral 18.2.1) el PROVEEDOR tendrá derecho a que el COMPRADOR le pague un monto proporcional a los costos directos que fueran inevitables en que hubiera incurrido con anterioridad a la terminación, siempre y cuando dichos montos se encuentren claramente definidos, sustentados y no excedan, en su conjunto, el precio del Contrato. En cualquier caso, el PROVEEDOR procurará minimizar los costos y gastos que se generen como consecuencia de la terminación. Salvo por los pagos indicados en este párrafo, el PROVEEDOR no tendrá derecho a recibir compensación adicional del COMPRADOR. <br><br>';

    $html19 = '<strong>19. Reclamos del PROVEEDOR</strong> <br>
    El PROVEEDOR solo tendrá derecho a efectuar reclamos en aquellos casos expresamente indicados en el Contrato. El PROVEEDOR no tendrá derecho a ejercer ninguna carga, gravamen, o medida cautelar sobre los bienes del COMPRADOR. Como condición precedente para cualquier reclamo, el PROVEEDOR deberá notificar al COMPRADOR de las circunstancias que en opinión del PROVEEDOR podrían dar lugar a un reclamo dentro de los dos (2) días siguientes a su ocurrencia y deberá remitir sin demoras indebidas en un plazo máximo de cinco (5) días calendarios cualquier reclamo por escrito al COMPRADOR incluyendo toda la sustentación y pruebas que sean razonablemente necesarias o pedir una prórroga del plazo. <br><br>';

    $html20 = '<strong>20. Varios</strong> <br>
    20.1. Modificaciones. - Salvo en los casos en que se indica lo contrario en el Contrato, todas las modificaciones, alteraciones y variaciones a este Contrato serán vinculantes sólo si constan por escrito y están firmadas por los correspondientes representantes autorizados de las Partes. <br>
    20.2. Cesiones. - El COMPRADOR podrá ceder libremente total o parcialmente el Contrato a cualquiera de sus empresas vinculadas sin necesidad de autorización previa del PROVEEDOR. El PROVEEDOR no podrá ceder el presente Contrato, ya sea la posición contractual o los derechos, ni total ni parcialmente, sin la previa autorización por escrito del COMPRADOR. <br>
    20.3. Acuerdo Integral. - El Contrato constituye e incorpora el acuerdo y entendimiento total entre las Partes en relación con los asuntos que contiene y sustituye a cualesquier otro acuerdo y declaración anterior, sea verbal o escrita y con independencia de que se hubiera hecho de forma negligente o de buena fe (excluidas expresamente las declaraciones fraudulentas) y que no se hayan incorporado expresamente en los términos del Contrato. <br>
    20.4. Renuncia. - Ninguna renuncia a las acciones legales de cualquiera de las Partes por cualquier falta cometida por la otra en el cumplimiento de las disposiciones del Contrato: (a) se aplicará o se interpretará como una renuncia a las acciones que correspondan por cualquier otra falta distinta o adicional tanto similar como de diferente naturaleza; o (b) entrará en vigor salvo que la haya ejecutado debidamente por escrito un representante autorizado por la Parte respectiva. El hecho que alguna de las Partes no insista en alguna ocasión en el cumplimiento de los términos, condiciones y estipulaciones de este Contrato o el tiempo o cualquier otra indulgencia concedida por una Parte a la otra, no se considerará como una renuncia a las acciones correspondientes por el incumplimiento o como aceptación de cualquier variación al Contrato o como renuncia a alguno de los derechos aquí estipulados, los cuales permanecerán en plena vigencia. <br>
    20.5. Relación entre las Partes. - No se deberá interpretar que este Contrato crea una asociación, empresa conjunta o sociedad entre las Partes ni que impone ningún tipo de obligación o responsabilidad de asociación sobre ninguna de las Partes. Ninguna de las Partes tendrá derecho, poder ni autoridad para firmar o comprometerse mediante cualquier tipo de contrato, ni para actuar en nombre de, ni como agente o representante ni obligar de cualquier otro modo a otra Parte. <br>
    20.6. Notificaciones. - Toda comunicación relacionada con el día a día entre las Partes puede realizarse por correo electrónico. Cualquier notificación que deba ser entregada durante el período de vigencia del presente Contrato será realizada mediante la entrega de la misma en mano, por mensajero, por correo o por fax en las respectivas direcciones designadas a este fin en la OC, con constancia de cargo recibida. <br><br>';

    $html21 = '<strong>21. Legislación Aplicable e Idioma del Contrato</strong> <br>									
    El presente Contrato se regirá e interpretará de acuerdo con la legislación peruana vigente y el idioma será el castellano. <br><br>';

    $html22 = '<strong>22. Resolución de Conflictos</strong> <br>
    22.1. "Todo litigio, controversia, desavenencia o reclamación resultante, relacionada o derivada de este Contrato o que guarde relación con el mismo, incluidas las relativas a su validez, eficacia o terminación incluso las de este convenio arbitral, serán resueltas mediante arbitraje de derecho, cuyo laudo será definitivo e inapelable. El arbitraje será conducido de conformidad con el Reglamento de Arbitraje del Centro de Arbitraje de la Cámara de Comercio de Lima, a cuyas normas, administración y decisión se someten las Partes en forma incondicional, declarando conocerlas y aceptarlas en su integridad. <br>
    El Tribunal Arbitral estará integrado por 1 (un) árbitro. El arbitraje tendrá lugar en la ciudad de Lima y será conducido en el idioma castellano. El procedimiento de ejecución del laudo arbitral también será de competencia del tribunal arbitral."	 <br>
    22.2. Asimismo, para cualquier intervención de los jueces y tribunales ordinarios dentro de la mecánica arbitral, las Partes se someten expresamente a la competencia de los jueces y tribunales de la ciudad de Lima-Cercado, renunciando al fuero de sus domicilios. <br>
    22.3. Si el asunto en controversia sometido por las Partes a arbitraje no implicara la suspensión o terminación del presente Contrato, las Partes deberán seguir cumpliendo los derechos y obligaciones estipulados en el Contrato durante el arbitraje. <br><br>';

    $html23 = '<strong>23. Corrupción de funcionarios, prevención de lavado de activos y financiamiento del terrorismo</strong>  <br>
    23.1. Las Partes certifican con carácter de Declaración Jurada que sus recursos no provienen ni se destinan al ejercicio de ninguna actividad ilícita, minería ilegal, minería informal o de otras actividades de lavado de dinero provenientes de éstas o de actividades relacionadas con la financiación del terrorismo.  <br>
    23.3. Las Partes declaran cumplir con todas las normas relacionadas a temas de prevención de lavado de activos y financiamiento del terrorismo y se obligan a realizar todas las actividades encaminadas a asegurar que todos sus socios, administradores, clientes, proveedores, empleados y los recursos de éstos no se encuentren relacionados o provengan de actividades ilícitas, particularmente de lavado de activos o financiación del terrorismo.  <br>
    23.4. Si durante el plazo de vigencia del Contrato, cualquiera de las Partes o alguno de sus administradores o socios llegaran a resultar involucrados en una investigación de cualquier tipo penal o administrativa relacionada con actividades ilícitas, corrupción de funcionarios, lavado de dinero o financiamiento del terrorismo; o fuesen incluidos en listas de control como las de la ONU, OFAC o cualquier otra que pueda existir en el futuro del mismo carácter que las anteriores, cualquiera de las Partes tiene el derecho de resolver unilateralmente y de manera inmediata los Contratos, mediante notificación enviada a la otra Parte con treinta (30) días de anticipación, quedando ambas Partes liberadas de indemnizar a la otra Parte afectada por cualquier daño o perjuicio que se pudiera alegar. <br><br>';

    $html_total_ = '<div style="text-align: center;"> <div style="text-align: justify;">' . $html13 . $html14 . $html15 . $html16 . $html17 . $html18 . $html19 . $html20 . $html21 . $html22 . $html23 . '</div>';

    $pdf->AddPage();

    $pdf->writeHTML($html_total_, true, 0, true, 0);

    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  public function guardarDocumnentoReservaEntradaSalida($usuarioId, $documentoTipoId, $dataStockReservaOk, $respuesta, $periodoId, $opcionId, $organizadorId, $banderaIngresoSalida)
  {
    //generar salida
    $configuraciones = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDato($documentoTipoId, $usuarioId);
    $camposDinamicosSalida = [];
    foreach ($configuraciones as $item) {
      switch ($item['tipo']) {
        case 7:
          $camposDinamicos[] = array(
            "id" => $item['id'],
            "tipo" => "7",
            "opcional" => "0",
            "descripcion" => $item['descripcion'],
            "codigo" => "",
            "valor" => $item['cadena_defecto']
          );
          break;
        case 8:
          $camposDinamicosSalida[] = array(
            "id" => $item['id'],
            "tipo" => "8",
            "opcional" => "0",
            "descripcion" => $item['descripcion'],
            "codigo" => "",
            "valor" => $item['data']
          );
          break;
        case 9:
          $camposDinamicosSalida[] = array(
            "id" => $item['id'],
            "tipo" => "9",
            "opcional" => "0",
            "descripcion" => $item['descripcion'],
            "codigo" => "",
            "valor" => $item['data']
          );
          break;
        case 45:
          $camposDinamicosSalida[] = array(
            "id" => $item['id'],
            "tipo" => "45",
            "opcional" => "0",
            "descripcion" => $item['descripcion'],
            "codigo" => "",
            "valor" => $organizadorId
          );
          break;
      }
    }

    $detalleSalida = [];
    foreach ($dataStockReservaOk as $i => $itemReserva) {
      $arrayItem = array(
        "bienId" => $itemReserva['bien_id'],
        "bienDesc" => $itemReserva['bien_descripcion'],
        "movimiento_bien_ids" => "",
        "cantidadAceptada" => null,
        "cantidad" => $itemReserva['reserva'],
        "unidadMedidaId" => $itemReserva['unidad_medida_id'],
        "unidadMedidaDesc" => "",
        "esCompra" => null,
        "compraDesc" => "",
        "stockBien" => "150",
        "bienTramoId" => "",
        "subTotal" => "",
        "index" => $i,
        "precioCompra" => "",
        "organizadorId" => $banderaIngresoSalida == 1 ? $itemReserva['organizador_id']: $organizadorId
      );
      $detalleSalida[] = $arrayItem;
    }
    $documentoARelacionarSalida [] = array(
      "documentoId" => $respuesta->documentoId,
      "movimientoId" => $respuesta->movimientoId,
      "tipo" => "1",
      "documentoPadreId" => ""
    );

    $documentoId = $this->guardar($opcionId, $usuarioId, $documentoTipoId, $camposDinamicosSalida, $detalleSalida, $documentoARelacionarSalida, 1, "Movimiento para reservar stock", 1, 2, null, $periodoId, null, null, null, null);
  
  }

  public function guardarDocumentoCotizacion($camposDinamicosConsolidado,$usuarioId, $documentoTipoId, $detalle, $respuesta, $periodoId, $opcionId, $banderaCotizacon, $listaPagoProgramacion = null, $respuestaCotizacion = null, $monedaIdExt = null, $arraydetalleXpostor = null)
  {
    //generar cotizacion
    $checkedOC = ($detalle[0]['checked1'] == "true") ? "1" : 
    (($detalle[0]['checked2'] == "true") ? "2" : 
    (($detalle[0]['checked3'] == "true") ? "3" : null));

    $checked1Moneda = $detalle[0]['checked1Moneda'];
    $checked2Moneda = $detalle[0]['checked2Moneda'];
    $checked3Moneda = $detalle[0]['checked3Moneda'];

    $checkeMoneda1 = $checked1Moneda == "true"? 4: 2;
    $checkeMoneda2 = $checked2Moneda == "true"? 4: 2;
    $checkeMoneda3 = $checked3Moneda == "true"? 4: 2;

    if($banderaCotizacon == 1){
      $filtradosTipo23 = array_filter($camposDinamicosConsolidado, function($item) {
        return $item['tipo'] === "23" && !empty($item['valor']);
      });
    }else if($banderaCotizacon == 2){
      $filtradosTipo23 = array_filter($camposDinamicosConsolidado, function($item) use ($checkedOC){
        return $item['tipo'] === "23" && !empty($item['valor']);
      });
    }else if($banderaCotizacon == 3){
      $filtradosTipo23 = array_filter($camposDinamicosConsolidado, function($item) use ($checkedOC){
        return $item['tipo'] === "5" && !empty($item['valor']);
      });
    }

    $filtradosTipo49 = array_values(array_filter($camposDinamicosConsolidado, function($item) {
      return $item['tipo'] === "49" && !empty($item['valor']);
    }));

    $filtradosTipo46 = array_values(array_filter($camposDinamicosConsolidado, function($item) {
      return $item['tipo'] === "46" && !empty($item['valor']);
    }));

    $filtradosTipo45 = array_values(array_filter($camposDinamicosConsolidado, function($item) {
      return $item['tipo'] === "45" && !empty($item['valor']);
    }));

    $filtradosTipo4 = array_values(array_filter($camposDinamicosConsolidado, function($item) {
      return $item['tipo'] === "4" && !empty($item['valor']);
    }));

    $filtradosTipo14 = array_values(array_filter($camposDinamicosConsolidado, function($item) {
      return $item['tipo'] === "14" && !empty($item['valor']);
    }));

    $filtradosTipo15 = array_values(array_filter($camposDinamicosConsolidado, function($item) {
      return $item['tipo'] === "15" && !empty($item['valor']);
    }));

    $filtradosTipo16 = array_values(array_filter($camposDinamicosConsolidado, function($item) {
      return $item['tipo'] === "16" && !empty($item['valor']);
    }));

    $filtradosTipo50 = array_values(array_filter($camposDinamicosConsolidado, function($item) {
      return $item['tipo'] === "50" && !empty($item['valor']);
    }));    

    $sumaMontosprecioPostor1  = array_reduce($detalle, function ($acumulador, $seleccion) {
      $postor_ganador = $seleccion['postor_ganador_id'];
      $precio = array_values(array_filter($seleccion['detalle'], function($item) use($postor_ganador){
        return $item['valorExtra'] === $postor_ganador;
      }));
      return $acumulador + ($precio[0]['valorDet'] * $seleccion['cantidad']);
    }, 0);

    if($arraydetalleXpostor[0][4] == 1){
      $subTotalPostor1 = $sumaMontosprecioPostor1 /1.18;
      $igvPostor1 = $sumaMontosprecioPostor1 - $subTotalPostor1;
    }else{
      $subTotalPostor1 = $sumaMontosprecioPostor1;
      $sumaMontosprecioPostor1 = $sumaMontosprecioPostor1 * 1.18;
      $igvPostor1 = $sumaMontosprecioPostor1 - $subTotalPostor1;
    }
    // $sumaMontosprecioPostor2  = array_reduce($detalle, function ($acumulador, $seleccion) {
    //   return $acumulador + ($seleccion['precioPostor2'] * $seleccion['cantidad']);
    // }, 0);
    // $subTotalPostor2 = $sumaMontosprecioPostor2 / 1.18;
    // $igvPostor2 = $sumaMontosprecioPostor2 - $subTotalPostor2;
    // $sumaMontosprecioPostor3  = array_reduce($detalle, function ($acumulador, $seleccion) {
    //   return $acumulador + ($seleccion['precioPostor3'] * $seleccion['cantidad']);
    // }, 0);
    // $subTotalPostor3 = $sumaMontosprecioPostor3 / 1.18;
    // $igvPostor3 = $sumaMontosprecioPostor3 - $subTotalPostor3;


    $documentosIdCotizaciones = []; //revisar
    foreach ($filtradosTipo23 as $itemTipo23) {
      $configuraciones = DocumentoTipoNegocio::create()->obtenerDocumentoTipoDato($documentoTipoId, $usuarioId);
      $camposDinamicosCotizacion = [];
      $documentoARelacionarSalida = [];

      $importeTotal = 0;
      $igv = 0;
      $subTotal = 0;
      $monedaId = 2;
      $texto1 = stripos($itemTipo23['descripcion'], "1");
      $texto2 = stripos($itemTipo23['descripcion'], "2");
      $texto3 = stripos($itemTipo23['descripcion'], "3");

      if($banderaCotizacon != 3){
        // if($texto1 == true){
          $importeTotal = $sumaMontosprecioPostor1;
          $igv = $igvPostor1;
          $monedaId = $checkeMoneda1;
          $subTotal = $subTotalPostor1;
        // }else if($texto2 == true){
        //   $importeTotal = $sumaMontosprecioPostor2;
        //   $subTotal = $subTotalPostor2;
        //   $igv = $igvPostor2;
        //   $monedaId = $checkeMoneda2;
        // }else if($texto3 == true){
        //   $importeTotal = $sumaMontosprecioPostor3;
        //   $igv = $igvPostor3;
        //   $subTotal = $subTotalPostor3;
        //   $monedaId = $checkeMoneda3;
        // }
      }else{
        $importeTotal = $filtradosTipo14[0]['valor'];
        $igv = $filtradosTipo15[0]['valor'];
        $subTotal = $filtradosTipo16[0]['valor'];
        $monedaId = $monedaIdExt;
      }

      foreach ($configuraciones as $item) {
        switch ($item['tipo']) {
          case 4:
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $filtradosTipo4[0]['descripcion'],
              "codigo" => $filtradosTipo4[0]['codigo'],
              "valor" => $filtradosTipo4['valor']
            );
            break;
          case 5: //Proveedor, cliente, etc
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $item['descripcion'],
              "codigo" => $itemTipo23['codigo'],
              "valor" => $itemTipo23['valor']
            );
            break;
          case 7: //Serie
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $item['descripcion'],
              "codigo" => "",
              "valor" => $item['cadena_defecto']
            );
            break;
          case 8: //Número
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $item['descripcion'],
              "codigo" => "",
              "valor" => $item['data']
            );
            break;
          case 9: //Fecha de emisión
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $item['descripcion'],
              "codigo" => "",
              "valor" => $item['data']
            );
            break;
          case 14://Importe total
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $item['descripcion'],
              "codigo" => "",
              "valor" => $importeTotal
            );
            break;
          case 15: //IGV
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $item['descripcion'],
              "codigo" => "",
              "valor" => $igv
            );
            break;
          case 16: //Sub total
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $item['descripcion'],
              "codigo" => "",
              "valor" => $subTotal
            );
            break;                              
          case 23: //Proveedor u otros
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $itemTipo23['descripcion'],
              "codigo" => $itemTipo23['codigo'],
              "valor" => $itemTipo23['valor']
            );
            break; 
          case 45: //Entrega en destino
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $filtradosTipo45[0]['descripcion'],
              "codigo" => $filtradosTipo45[0]['codigo'],
              "valor" => $filtradosTipo45[0]['valor']
            );
            break;            
          case 46: //U.O
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $filtradosTipo46[0]['descripcion'],
              "codigo" => $filtradosTipo46[0]['codigo'],
              "valor" => $filtradosTipo46[0]['valor']
            );
            break; 
          case 47: // CUenta
            $cuenta_persona = ProgramacionPagos::create()->obtenerCuentaPrincipalxPersonaId($itemTipo23['valor'], 1, $monedaId);
            if(ObjectUtil::isEmpty($cuenta_persona)){
              throw new WarningException("No existen registros de cuentas para el " . $itemTipo23['descripcion']);
            }
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $item['descripcion'],
              "codigo" => $item['codigo'],
              "valor" => $cuenta_persona[0]['id']
            );
            break;
          case 49: //
            $camposDinamicosCotizacion[] = array(
              "id" => $item['id'],
              "tipo" => $item['tipo'],
              "opcional" => "0",
              "descripcion" => $item['descripcion'],
              "codigo" => "",
              "valor" => $filtradosTipo49['valor']
            );
            break;            
          case 50:
            if($item['descripcion'] == "Condición de pago"){
              $valor = null;
              if($documentoTipoId == Configuraciones::COTIZACIONES && $filtradosTipo50[0]['valor'] == 502){//credito
                $valor = 472;
              }else if($documentoTipoId == Configuraciones::COTIZACIONES && $filtradosTipo50[0]['valor'] == 501){//contado
                $valor = 471;
              }
              
              if($documentoTipoId == Configuraciones::ORDEN_COMPRA && $filtradosTipo50[0]['valor'] == 502){//credito
                $valor = 469;
              }else if($documentoTipoId == Configuraciones::ORDEN_COMPRA && $filtradosTipo50[0]['valor'] == 501){//contado
                $valor = 468;
              }

              if($documentoTipoId == Configuraciones::ORDEN_SERVICIO && $filtradosTipo50[0]['valor'] == 500){//credito
                $valor = 469;
              }else if($documentoTipoId == Configuraciones::ORDEN_SERVICIO && $filtradosTipo50[0]['valor'] == 499){//contado
                $valor = 468;
              }              
              $camposDinamicosCotizacion[] = array(
                "id" => $item['id'],
                "tipo" => $item['tipo'],
                "opcional" => "0",
                "descripcion" => $filtradosTipo50[0]['descripcion'],
                "codigo" => $filtradosTipo50[0]['codigo'],
                "valor" => $valor
              );
            }
            break;            
        }
      }


      $detalleCotizacion = [];
      $$movimiento_bien_ids = null;
      foreach ($detalle as $i => $itemDetalle) {
        $precioItem = 0;
        $texto1 = stripos($itemTipo23['descripcion'], "1");
        $texto2 = stripos($itemTipo23['descripcion'], "2");
        $texto3 = stripos($itemTipo23['descripcion'], "3");
        // if($banderaCotizacon != 3){
          // if($texto1 == true){
          //   $precioItem = $itemDetalle['precioPostor1'];
          // }else if($texto2 == true){
          //   $precioItem = $itemDetalle['precioPostor2'];
          // }else if($texto3 == true){
          //   $precioItem = $itemDetalle['precioPostor3'];
          // }
          $postor_ganador = $itemDetalle['postor_ganador_id'];
          $precioItem = array_values(array_filter($itemDetalle['detalle'], function($item) use($postor_ganador){
            return $item['valorExtra'] === $postor_ganador;
          }))[0]['valorDet'];
          $movimiento_bien_ids = $itemDetalle['movimiento_bien_ids'];
        // }else{
        //   $precioItem = $itemDetalle['precio'];
        // }

        $arrayItem = array(
          "bienId" => $itemDetalle['bienId'],
          "bienDesc" => $itemDetalle['bienDesc'],
          "movimiento_bien_ids" => $movimiento_bien_ids,
          "cantidadAceptada" => null,
          "cantidad" => $itemDetalle['cantidad'],
          "unidadMedidaId" => $itemDetalle['unidadMedidaId'],
          "unidadMedidaDesc" => "",
          "esCompra" => null,
          "compraDesc" => "",
          "stockBien" => $itemDetalle['stockBien'],
          "bienTramoId" => "",
          "subTotal" => "",
          "index" => $i,
          "precioCompra" => "",
          "organizadorId" => null,
          "precioTipoId" => 1,
          "precio" => $precioItem
        );
        $detalleCotizacion[] = $arrayItem;
      }

      if($documentoTipoId == Configuraciones::ORDEN_COMPRA){
        $documentoARelacionarSalida [] = array(
          "documentoId" => $respuestaCotizacion[0],
          "movimientoId" => "",
          "tipo" => "1",
          "documentoPadreId" => ""
        );
      }else{
        $documentoARelacionarSalida [] = array(
          "documentoId" => $respuesta->documentoId,
          "movimientoId" => $respuesta->movimientoId,
          "tipo" => "1",
          "documentoPadreId" => ""
        );
      }

      $documento = $this->guardar($opcionId, $usuarioId, $documentoTipoId, $camposDinamicosCotizacion, $detalleCotizacion, $documentoARelacionarSalida, 1, "", 1, $monedaId, null, $periodoId, null, null, null, null);
    
      if($banderaCotizacon == 1){
        $checkedOC = ($detalle[0]['checked1'] == "true") ? "1" : 
        (($detalle[0]['checked2'] == "true") ? "2" : 
        (($detalle[0]['checked3'] == "true") ? "3" : null));
      }
      // $filtradosTipo23 = array_filter($camposDinamicosCotizacion, function($item) use ($checkedOC){
      //   return $item['tipo'] === "23" && !empty($item['valor']) && stripos($item['descripcion'], $checkedOC) == true;
      // });

      if(!ObjectUtil::isEmpty($filtradosTipo23)){
        $respuestaActualizarDocumentoEstado = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documento[0]['vout_id'], 16, $usuarioId);
        $documentosIdCotizaciones [] = $documento[0]['vout_id'];
      }
    }
  
    if($documentoTipoId == Configuraciones::ORDEN_COMPRA || $documentoTipoId == Configuraciones::ORDEN_SERVICIO){
      if (!ObjectUtil::isEmpty($listaPagoProgramacion)) {
        foreach ($listaPagoProgramacion as $ind => $item) {
          if (strpos($itemPagoProgramacionPostores[0], '/') !== false) {
            $fechaPago = DateUtil::formatearCadenaACadenaBD($itemPagoProgramacionPostores[0]);
          }else{
            $fechaPago = $itemPagoProgramacionPostores[0];
          }
          $importePago = $item[1];
          $dias = $item[2];
          $porcentaje = $item[3];
          $glosa = $item[4];
  
          $res = Pago::create()->guardarDistribucionPagos($documento[0]['vout_id'], $fechaPago, $importePago, $dias, $porcentaje, $glosa, $usuarioId);
        }
      }

    }
    return $documentosIdCotizaciones;
  }

  public function obtenerDetalleBienRequerimiento($movimientoBienId){
    return MovimientoBien::create()->movimientoBienDetalleobtenerDetalleXRequerimientoId($movimientoBienId);
  }

  public function generarDocumentoPDFGenerarCotizacion($documentoId, $comentario, $tipoSalidaPDF, $url, $data){
    //obtenemos la data
    $dataDocumentoTipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoId($documentoId);
    $documentoTipoId = $dataDocumentoTipo[0]['id'];

    $dataDocumento = $data->dataDocumento;
    $documentoDatoValor = $data->documentoDatoValor;
    $detalle = $data->detalle;
    $dataEmpresa = $data->dataEmpresa;
    $dataMovimientoTipoColumna = $data->movimientoTipoColumna;
    $dataDocumentoRelacion = $data->documentoRelacionado;

    $dataTipoCambio = TipoCambioNegocio::create()->obtenerTipoCambioXfecha(substr($dataDocumento[0]["fecha_emision"], 0, 10))[0]['equivalencia_venta'];


    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator('Soluciones Mineras S.A.C.');
    $pdf->SetAuthor('Soluciones Mineras S.A.C.');
    $pdf->SetTitle(strtoupper($dataDocumentoTipo[0]['descripcion']));

    //        $pdf->SetHeaderData('logo.PNG', PDF_HEADER_LOGO_WIDTH,strtoupper($dataDocumentoTipo[0]['descripcion']),$dataEmpresa[0]['razon_social']."\n".$dataEmpresa[0]['direccion'], array(0,0,0), array(0,0,0));

    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();

    $serieDocumento = '';
    if (!ObjectUtil::isEmpty($dataDocumento[0]['serie'])) {
      $serieDocumento = $dataDocumento[0]['serie'] . " - ";
    }

    $titulo = strtoupper($dataDocumento[0]['documento_tipo_descripcion']) . " " . $serieDocumento . $dataDocumento[0]['numero'];

    $pdf->SetFillColor(255, 255, 255);
    $pdf->Image(__DIR__ . '/../../vistas/images/logo_pepas_de_oro.png', 15, 10, 45, 20, '', '', '', false, 300, '', false, false, 1);

    $pdf->MultiCell(180, 40, 'ASOCIACION DE MINEROS ARTESANALES PEPAS DE ORO DE PAMPAMARCA', 0, 'C', 1, 0, 80, 10, true, 0, false, true, 5, 'M');

    $pdf->MultiCell(150, 40, 'LOGISTICA', 0, 'C', 1, 0, 90, 20, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(150, 40, 'CUADRO COMPARATIVO', 0, 'C', 1, 0, 90, 25, true, 0, false, true, 5, 'M');


    $documento_detalle = Documento::create()->obtenerDocumentoDetalleDatos($documentoId);
    $colspan = count($documento_detalle);
    $cantidadPostores = $colspan * 11;
    $alturaTabla = 5;

    $pdf->SetFont('helvetica', 'B', 8);

    $alturaBase = 25;
    foreach($documento_detalle as $index => $item){
      $alturaBase = $alturaBase + 7;
      $textoReducido = strlen($item['persona']) > 50 ? substr($item['persona'], 0, 50) . '...': $item['persona'];
      $pdf->SetFillColor(254, 191, 0);
      $pdf->MultiCell(30, 5, 'Postor N° '.($index + 1), 1, 'C', 1, 0, 15, ($alturaBase), true, 0, false, true, 5, 'M');
      $pdf->SetFillColor(255, 255, 255);
      $pdf->MultiCell(100, 5, $textoReducido, 1, 'C', 1, 0, 45, ($alturaBase), true, 0, false, true, 5, 'M');
      
      $alturaTabla = $alturaTabla + 5;
    }

    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'Nro', 1, 'C', 1, 0, 190, 35, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, $dataDocumento[0]['serie'] . " - " . $dataDocumento[0]['numero'], 1, 'C', 1, 0, 220, 35, true, 0, false, true, 5, 'M');

    //
    $pdf->SetFillColor(254, 191, 0);
    $pdf->MultiCell(30, 5, 'FECHA', 1, 'C', 1, 0, 190, 42, true, 0, false, true, 5, 'M');
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(50, 5, date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y'), 1, 'C', 1, 0, 220, 42, true, 0, false, true, 5, 'M');

    //

    $color_ganador1 = "";

    $cont = 0;
    $pdf->SetFont('helvetica', 'B', 7);

    $pdf->Ln($alturaTabla);
    $tabla = '<table cellspacing="0" cellpadding="1" border="1">
        <tr style="background-color:rgb(254, 191, 0);">
            <th style="text-align:center;vertical-align:middle;" width="3%" rowspan="3"><b>N°</b></th>
            <th style="text-align:center;vertical-align:middle;" width="27%" rowspan="3"><b>DESCRIPCION</b></th>
            <th style="text-align:center;vertical-align:middle;" width="7%" rowspan="3"><b>CANTIDAD</b></th>
            <th style="text-align:center;vertical-align:middle;" width="7%" rowspan="3"><b>UNIDAD DE MEDIDA</b></th>
            <th style="text-align:center;vertical-align:middle;" width="'.$cantidadPostores.'%" colspan="'.$colspan.'"><b>COTIZACION DE PROVEEDORES</b></th>
        </tr>';

    $tabla .='<tr style="background-color:rgb(254, 191, 0);">';
    foreach($documento_detalle as $index => $item){
      $textoReducido = strlen($item['persona']) > 30 ? substr($item['persona'], 0, 30) . '...': $item['persona'];
      $tabla .='<th style="text-align:center;vertical-align:middle;" width="11%" colspan="2"><b>'.$textoReducido.'<br></b></th>';
    }
    $tabla .='</tr>';

    $tabla .='<tr style="background-color:rgb(254, 191, 0);">';
    foreach($documento_detalle as $index => $item){
      $tabla .='<th style="text-align:center;vertical-align:middle;" width="5.5%" ><b>P.U </b></th>
      <th style="text-align:center;vertical-align:middle;" width="5.5%" ><b>P.T </b></th>';
    }
    $tabla .='</tr>';


    $total1 = 0;
    $total2 = 0;
    $total3 = 0;
    $totales = [];
    if (!ObjectUtil::isEmpty($detalle)) {
      foreach ($detalle as $index => $item) {
        $cont++;
        $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleXMovimientoBienId($item->movimientoBienId);

        $filtrados = array_values(array_filter($resMovimientoBienDetalle, function($item) {
          return $item['columna_codigo'] === "37" ;
        }));

        $tabla = $tabla . '<tr>';
        $tabla  .= '<td style="text-align:center"  width="3%">' . ($index + 1) . '</td>'
        . '<td style="text-align:left; vertical-align:middle; display: table-cell;" width="27%">' .  $item->bien_codigo .' | '. $item->descripcion . '</td>'
        . '<td style="text-align:right"  width="7%">' . number_format($item->cantidad, 2) . '</td>'
        . '<td style="text-align:center"  width="7%">' . $item->simbolo . '</td>';
        foreach($filtrados as $index => $itemFiltrados){
          $total_ = (($itemFiltrados['valor_detalle'] * $item->cantidad));
          $totales [$index] = $totales [$index] + $total_;
          $color_ganador = "";
          if($itemFiltrados['valor_extra'] == $item->postor_ganador_id){
            $color_ganador = "background-color:rgb(0, 254, 127);";
          }
          $tabla  .= '<td style="text-align:right;'. $color_ganador .'"  width="5.5%">' . number_format($itemFiltrados['valor_detalle'], 2) . '</td>'
            . '<td style="text-align:right;'. $color_ganador .'"  width="5.5%">' . number_format(($itemFiltrados['valor_detalle'] * $item->cantidad), 2) . '</td>';
        }
        $tabla .= '</tr>';
      }
    }

    for ($i = count($detalle); $i < 10; $i++) {
      $tabla = $tabla . '<tr>'
        . '<td style="text-align:center"  width="3%">' . ($i + 1) . '</td>'
        . '<td style="text-align:left"  width="27%"></td>'
        . '<td style="text-align:center"  width="7%"></td>'
        . '<td style="text-align:center"  width="7%"></td>';
        foreach($filtrados as $index => $item){
          $tabla  .= '<td style="text-align:center;'. $color_ganador1 .'"  width="5.5%"></td>'
          . '<td style="text-align:center;'. $color_ganador1 .'"  width="5.5%"></td>';
        }
        $tabla  .= '</tr>';
    }

    $moneda_tipo1 = $detalle[0]->moneda_postor1;
    $moneda_tipo2 = $detalle[0]->moneda_postor2;
    $moneda_tipo3 = $detalle[0]->moneda_postor3;

    if($moneda_tipo1 == 4){
      $totalDolaresSoles1 = $total1 * $dataTipoCambio;
    }
    if($moneda_tipo2 == 4){
      $totalDolaresSoles2 = $total2 * $dataTipoCambio;
    }
    if($moneda_tipo3 == 4){
      $totalDolaresSoles3 = $total3 * $dataTipoCambio;
    }

    $subtotal1 = $total1 /1.18;
    $subtotal2 = $total2 /1.18;
    $subtotal3 = $total3 /1.18;

    $tabla .= '<tfoot>';
    $tabla .= '<tr>'
                  .'<th colspan="4" style="text-align:right"></th>';
        foreach($documento_detalle as $index => $item){
          $subTotal = 0;
          if($item['igv'] == "1"){
            $subTotal = $totales[$index] /1.18;
          }else{
            $subTotal = $totales[$index];
          }
          $tabla .= '<th style="text-align:right" colspan="2" >'. number_format($subTotal, 2).'</th>';
        }

    $tabla .= '</tr>'; 
    $tabla .= '<tr>'
                  .'<th colspan="4" style="text-align:right">Igv (18 %):</th>';
                  foreach($documento_detalle as $index => $item){
                    $igv = 0;
                    if($item['igv'] == "1"){
                      $subTotal = $totales[$index] /1.18;
                      $igv = $totales[$index] - $subTotal;
                    }else{
                      $subTotal = $totales[$index];
                      $valorTotal = $totales[$index] * 1.18;
                      $igv = $valorTotal - $subTotal;
                    }
                    $tabla .= '<th style="text-align:right" colspan="2" >'. number_format($igv, 2).'</th>';
                  }
    $tabla .= '</tr>'; 
    // $tabla .= '<tr>'
    //               .'<th colspan="4" style="text-align:right">Total Dolares:</th>';
    //               $tabla .= '<th style="text-align:right" colspan="2" >'. number_format($totalDolaresSoles1, 2).'</th>';
    // $tabla .= '</tr>'; 
    $tabla .= '<tr>'
                  .'<th colspan="4" style="text-align:right">Total:</th>';
                  foreach($documento_detalle as $index => $item){
                    if($item['igv'] == "1"){
                      $valorTotal = $totales[$index];
                    }else{
                      $valorTotal = $totales[$index] * 1.18;
                    }
                    $tabla .= '<th style="text-align:right" colspan="2" >'. number_format($valorTotal, 2).'</th>';
                  }
    $tabla .= '</tr>'            
          .'</tfoot>';

    $tabla = $tabla . '</table>';

    $pdf->writeHTML($tabla, true, false, true, false, '');

    $tablaHeight = $pdf->GetY(); 
    $espacio = 0;  // Inicializar el espacio
    $paginaAltura = $pdf->getPageHeight();  // Altura total de la página
    $alturaDisponible = $paginaAltura - $tablaHeight - 20; 
    // Ahora puedes ajustar el valor de $espacio basado en el espacio disponible
    if ($alturaDisponible > 40) {
      // Si hay mucho espacio, usa ese espacio
      $espacio = $tablaHeight + 5;  // Ajusta un pequeño margen después de la tabla
    } else {
      // Si el espacio es limitado, podrías agregar una nueva página
      $pdf->AddPage();
      $espacio = 15;  // Nuevo espacio al inicio de la nueva página
    }


    $persona = Persona::create()->obtenerPersonaXUsuarioId($dataDocumento[0]["usuario_creacion"]);

    $personaFirma0 = __DIR__ . "/../../vistas/com/persona/firmas/" . $persona[0]['firma_digital'] . "png";

    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell(50, 5, 'Generado por', 1, 'C', 1, 0, 185, $espacio, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(50, 30, $dataDocumento[0]['nombre'], 1, 'C', 1, 0, 185, $espacio, true, 0, false, true, 30, 'B');
   
    $pdf->MultiCell(50, 5, 'Fecha:', 1, 'L', 1, 0, 185, $espacio + 30, true, 0, false, true, 5, 'M');
    $pdf->SetFont('helvetica', '', 6);
    $pdf->MultiCell(40, 5, date_format((date_create($dataDocumento[0]['fecha_emision'])), 'd/m/Y'), 1, 'L', 1, 0, 195, $espacio + 30, true, 0, false, true, 5, 'M');

    ob_clean();

    if ($tipoSalidaPDF == 'F') {
      $pdf->Output($url, $tipoSalidaPDF);
    }

    return $titulo;
  }

  public function exportarPdfCotizacion($grupoProductoId, $tipoRequerimiento, $urgencia, $usuarioId)
  {
    require_once __DIR__ . '/../../controlador/commons/tcpdf/config/lang/eng.php';
    require_once __DIR__ . '/../../controlador/commons/tcpdf/tcpdf.php';

    $respuesta = new stdClass();
    $detalleRequerimientos = MovimientoBien::create()->obtenerMovimientoBienXRequerimientoXGrupoProductoxId($grupoProductoId, $tipoRequerimiento, $urgencia);

    //$tipoSalidaPDF: F-> guarda local
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator('Soluciones Mineras S.A.C.');
    $pdf->SetAuthor('Soluciones Mineras S.A.C.');
    $pdf->SetTitle(strtoupper("Solicitud de Cotización"));


    $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0)); // 0,0,0
    // set header and footer fonts
    $PDF_FONT_SIZE_MAIN = 9;
    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', $PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // --------------GENERAR PDF-------------------------------------------
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->AddPage();


    $titulo = "Solicitud de Cotización";

    $pdf->SetFillColor(255, 255, 255);
    $pdf->Image(__DIR__ . '/../../vistas/images/logo_pepas_de_oro.png', 15, 10, 45, 20, '', '', '', false, 300, '', false, false, 1);
    $pdf->MultiCell(90, 5, 'FORMATO', 1, 'C', 1, 0, 60, 10, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(90, 5, 'SOLICITUD DE COTIZACIÓN', 1, 'C', 1, 0, 60, 15, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'CODIGO: F-COR-LOG-ALM-01', 1, 'L', 1, 0, 60, 20, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'VERSION: 01', 1, 'L', 1, 0, 105, 20, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'AREA: LOGISTICA', 1, 'L', 1, 0, 60, 25, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 5, 'PAGINA: 01 de 01', 1, 'L', 1, 0, 105, 25, true, 0, false, true, 5, 'M');
    $pdf->MultiCell(45, 20, 'CORPORATIVO', 1, 'C', 1, 0, 150, 10, true, 0, false, true, 20, 'M');


    $cont = 0;
    $pdf->SetFont('helvetica', 'B', 5);

    $pdf->Ln(30);
    $tabla = '<table cellspacing="0" cellpadding="1" border="1">
        <tr style="background-color:rgb(254, 191, 0);">
            <th style="text-align:center;vertical-align:middle;" width="3%"><b>N°</b></th>
            <th style="text-align:center;vertical-align:middle;" width="16%"><b>CODIGO</b></th>
            <th style="text-align:center;vertical-align:middle;" width="45%"><b>DESCRIPCION</b></th>
            <th style="text-align:center;vertical-align:middle;" width="8%"><b>MARCA</b></th>
            <th style="text-align:center;vertical-align:middle;" width="8%"><b>MODELO</b></th>
            <th style="text-align:center;vertical-align:middle;" width="10%"><b>CANTIDAD</b></th>
            <th style="text-align:center;vertical-align:middle;" width="10%"><b>UNIDAD DE MEDIDA</b></th>
        </tr>
    ';
    if (!ObjectUtil::isEmpty($detalleRequerimientos)) {
      foreach ($detalleRequerimientos as $index => $item) {
        $cont++;

        $tabla = $tabla . '<tr>'
          . '<td style="text-align:center"  width="3%">' . ($index + 1) . '</td>'
          . '<td align="center" width="16%">' . $item['bien_codigo'] . '</td>'
          . '<td style="text-align:left; vertical-align:middle; display: table-cell;" width="45%">' . $item['bien_descripcion']  . '</td>'
          . '<td style="text-align:center"  width="8%"></td>'
          . '<td style="text-align:center"  width="8%"></td>'
          . '<td style="text-align:center"  width="10%">' . number_format($item['cantidad'], 2) . '</td>'
          . '<td style="text-align:center"  width="10%">' . $item['simbolo'] . '</td>'
          . '</tr>';
      }
    }

    for ($i = count($detalleRequerimientos); $i < 20; $i++) {
      $tabla = $tabla . '<tr>'
        . '<td style="text-align:center"  width="3%">' . ($i + 1) . '</td>'
        . '<td style="text-align:left"  width="16%"></td>'
        . '<td style="text-align:left"  width="45%"></td>'
        . '<td style="text-align:center"  width="8%"></td>'
        . '<td style="text-align:center"  width="8%"></td>'
        . '<td style="text-align:center"  width="10%"></td>'
        . '<td style="text-align:center"  width="10%"></td>'
        . '</tr>';
    }


    $tabla = $tabla . '</table>';
    $pdf->writeHTML($tabla, true, false, true, false, '');


    $tablaHeight = $pdf->GetY(); 
    $espacio = 0;  // Inicializar el espacio
    $paginaAltura = $pdf->getPageHeight();  // Altura total de la página
    $alturaDisponible = $paginaAltura - $tablaHeight - 20; 
    // Ahora puedes ajustar el valor de $espacio basado en el espacio disponible
    if ($alturaDisponible > 50) {
      // Si hay mucho espacio, usa ese espacio
      $espacio = $tablaHeight + 10;  // Ajusta un pequeño margen después de la tabla
    } else {
      // Si el espacio es limitado, podrías agregar una nueva página
      $pdf->AddPage();
      $espacio = 15;  // Nuevo espacio al inicio de la nueva página
    }

    $pdf->writeHTMLCell(180, 5, '', $espacio + 41, 'El usuario es responsable de asegurar el uso de los documentos vigentes disponibles en la <strong>plataforma documentaria</strong> o en consulta con el <strong>Coordinador SGI o Analista SGI</strong>', 0, 1, 1, true, 'C', true);

    ob_clean();

    $hoy = date("Y_m_d_H_i_s");
    $pdf_ = 'documento_' . $hoy . '_' . $usuarioId . '.pdf';
    $url = __DIR__ . '/../../vistas/com/movimiento/documentos/documento_' . $hoy . '_' . $usuarioId . '.pdf';
    $pdf->Output($url, 'F');


    $url = Configuraciones::url_base() . 'vistas/com/movimiento/documentos/' . $pdf_;

    $respuesta->url = $url;
    $respuesta->nombre = $titulo;
    $respuesta->pdf = $pdf_;
    return $respuesta;
  }
}
