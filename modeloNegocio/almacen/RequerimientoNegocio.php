<?php

require_once __DIR__ . '/../../modelo/almacen/Requerimiento.php';
require_once __DIR__ . '/../../modelo/almacen/MovimientoBien.php';
require_once __DIR__ . '/../../modelo/almacen/MatrizAprobacion.php';
require_once __DIR__ . '/../../modelo/almacen/ActaRetiro.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/commons/TablaNegocio.php';
require_once __DIR__ . '/PersonaNegocio.php';
require_once __DIR__ . '/DocumentoTipoNegocio.php';
require_once __DIR__ . '/DocumentoNegocio.php';
require_once __DIR__ . '/MonedaNegocio.php';
require_once __DIR__ . '/MovimientoTipoNegocio.php';
require_once __DIR__ . '/OrganizadorNegocio.php';
require_once __DIR__ . '/EmailPlantillaNegocio.php';
require_once __DIR__ . '/EmailEnvioNegocio.php';


class RequerimientoNegocio extends ModeloNegocioBase
{
    /**
     *
     * @return RequerimientoNegocio
     */
    static function create()
    {
        return parent::create();
    }

    public function obtenerConfiguracionInicialListado($usuarioId, $opcionId)
    {
        $respuesta = new stdClass();

        switch ($opcionId) {
            case 387:
                $movimientoTipoId = 144;
                break;
            case 390:
                $movimientoTipoId = 147;
                break;
        }

        if ($opcionId == 387 || $opcionId = 390) {
            $mostrarTodasAreas = 0;
            $dataPerfil = PerfilNegocio::create()->obtenerPerfilXUsuarioId($usuarioId);
            foreach ($dataPerfil as $itemPerfil) {
                if ($itemPerfil['id'] == PerfilNegocio::PERFIL_ADMINISTRADOR_ID || $itemPerfil['id'] == PerfilNegocio::PERFIL_ADMINISTRADOR_TI_ID || $itemPerfil['id'] == PerfilNegocio::PERFIL_JEFE_LOGISTA || $itemPerfil['id'] == PerfilNegocio::PERFIL_APROBADOR_SOLICITANTE_REQUERIMIENTO_URGENTE) {
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
        return $respuesta;
    }

    public function obtenerRequerimientosXCriterios($criterios, $elementosFiltrados, $usuarioId = null, $columns, $order, $start)
    {
        $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
        $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
        $documentoTipoId = $criterios['documento_tipo'];
        $areaId = $criterios['area'];
        $requerimiento_tipo = $criterios['requerimiento_tipo'];
        $tipo = $criterios['tipo'];
        if (!ObjectUtil::isEmpty($tipo)) {
            if ($tipo != 0) {
                $documentoTipoId = $tipo;
            }
        }

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return Requerimiento::create()->obtenerRequerimientosXCriterios($fechaEmisionInicio, $fechaEmisionFin, $documentoTipoId, $areaId, $requerimiento_tipo, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
    }

    public function obtenerCantidadRequerimientosXCriterios($criterios, $columns, $order)
    {
        $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
        $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
        $documentoTipoId = $criterios['documento_tipo'];
        $areaId = $criterios['area'];
        $requerimiento_tipo = $criterios['requerimiento_tipo'];

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];


        return Requerimiento::create()->obtenerCantidadRequerimientosXCriterios($fechaEmisionInicio, $fechaEmisionFin, $documentoTipoId, $areaId, $requerimiento_tipo, $columnaOrdenar, $formaOrdenar);
    }

    private function formatearFechaBD($cadena)
    {
        if (!ObjectUtil::isEmpty($cadena)) {
            return DateUtil::formatearCadenaACadenaBD($cadena);
        }
        return "";
    }


    public function visualizarSolicitudRequerimiento($id, $movimientoId)
    {
        $respuesta = new stdClass();
        $detalle =  MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);
        $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($id);

        $respuesta->detalle = $detalle;
        return $respuesta;
    }

    public function visualizarRequerimiento($id, $movimientoId)
    {
        $respuesta = new stdClass();
        $detalle =  MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);
        $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($id);

        $respuesta->detalle = $detalle;
        return $respuesta;
    }

    public function visualizarConsolidado($id, $movimientoId)
    {
        $respuesta = new stdClass();
        $detalle =  MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);
        $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($id);

        foreach ($detalle as $index => $item) {
            $detalle[$index]["subTotal_precio_postor1"] = $item["cantidad"] * $item["precio_postor1"];
            $detalle[$index]["subTotal_precio_postor2"] = $item["cantidad"] * $item["precio_postor2"];
            $detalle[$index]["subTotal_precio_postor3"] = $item["cantidad"] * $item["precio_postor3"];
        }

        $respuesta->detalle = $detalle;

        return $respuesta;
    }

    public function aprobarRequerimiento($documentoId, $usuarioId)
    {
        $data = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 3, $usuarioId); // 3 = Aprobado
        $respuesta =  new stdClass();
        $respuesta->mensaje = "La operación se generó con éxito";
        return  $respuesta;
    }

    public function aprobarConsolidado($documentoId, $usuarioId, $checked1 = null, $checked2 = null, $checked3 = null)
    {
        $dataDocumento = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($documentoId);
        $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $dataDocumento[0]['documento_tipo_id']);

        $movimientoId = MovimientoBien::create()->obtenerMovimientoIdXDocumentoId($documentoId);
        $movimientoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($movimientoId[0]['movimiento_id']);

        if (ObjectUtil::isEmpty($matrizUsuario)) {
            throw new WarningException("El usuario no esta registrado en la matriz de aprobación, comunicarse con el Administrador");
        }

        $postor_ganador_id = null;
        if ($checked1 == "true") {
            $postor_ganador_id = 1;
        }
        if ($checked2 == "true") {
            $postor_ganador_id = 2;
        }
        if ($checked3 == "true") {
            $postor_ganador_id = 3;
        }

        $data = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 3, $usuarioId); // 3 = Aprobado
        foreach ($movimientoDetalle as $item) {
            MovimientoBien::create()->editarMovimientoBienPostorGanadorXId($item['movimiento_bien_id'], $postor_ganador_id);
        }

        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("Hubo un problema al aprobar Consolidado");
        }

        $respuesta =  new stdClass();
        $respuesta->mensaje = "La operación se generó con éxito";
        return  $respuesta;
    }

    public function rechazarConsolidado($documentoId, $usuarioId, $motivoRechazo)
    {
        $data = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 9, $usuarioId, null, $motivoRechazo); // 16 = Aprobado por Jefe de Área

        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("Hubo un problema al aporbar Consolidado");
        }

        $respuesta =  new stdClass();

        $respuesta->mensaje = "La operación se generó con éxito";

        return  $respuesta;
    }

    public function aprobarOrdenCompraServicio($documentoId, $usuarioId)
    {
        $dataDocumento = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($documentoId);
        $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $dataDocumento[0]['documento_tipo_id']);

        $movimientoId = MovimientoBien::create()->obtenerMovimientoIdXDocumentoId($documentoId);
        $movimientoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($movimientoId[0]['movimiento_id']);

        if (ObjectUtil::isEmpty($matrizUsuario)) {
            throw new WarningException("El usuario no esta registrado en la matriz de aprobación, comunicarse con el Administrador");
        }

        $sumaDetalle = array_reduce($movimientoDetalle, function ($acumulador, $seleccion) {
            return $acumulador + ($seleccion['cantidad'] * $seleccion['valor_monetario']);
        }, 0);

        $bandera_matriz = false;
        foreach ($matrizUsuario as $item) {
            if (intval($sumaDetalle) <= intval($item['monto_aprobacion_max'])) {
                $bandera_matriz = true;
            }
        }

        if ($bandera_matriz == false) {
            throw new WarningException("El monto del consolidado supera al habilitado para el usuario , comunicarse con el Administrador");
        } else {
            $data = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 3, $usuarioId); // 3 = Aprobado

            if (ObjectUtil::isEmpty($data)) {
                throw new WarningException("Hubo un problema al aporbar Consolidado");
            }


            $validarDocumento = MatrizAprobacion::create()->validarDocumentoAprobacionUltimoNivel($dataDocumento[0]['documento_tipo_id']);

            if ($validarDocumento[0]["validar"] == 1) {
                $documentoAdjuntos = DocumentoNegocio::create()->obtenerDocumentoAdjuntoXDocumentoId($documentoId);
                foreach ($documentoAdjuntos as $item) {
                    if ($item['tipo_archivoId'] == 4) {
                        $xml = simplexml_load_file(__DIR__ . '/../../util/uploads/documentoAdjunto/' . $item['nombre']);
                        if ($xml === false) {
                            throw new WarningException("Error al leer archivo XML");
                        } else {
                            $ruc_emisor = (string)$xml->xpath("//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID")[0];
                            $serie_numero = (string) $xml->xpath("//cbc:ID")[0];
                            $serie_numero = explode("-", $serie_numero);
                            $serie = $serie_numero[0];
                            $numero = $serie_numero[1];
                            $subTotal = (string)$xml->xpath("//cac:LegalMonetaryTotal/cbc:LineExtensionAmount ")[0];
                            $igv = (string)$xml->xpath("//cac:TaxTotal/cbc:TaxAmount")[0];
                            $total = (string)$xml->xpath("//cac:LegalMonetaryTotal/cbc:PayableAmount")[0];
                            $personaId = PersonaNegocio::create()->obtenerPersonaXCodigoIdentificacion($ruc_emisor)[0]['id'];

                            $detraccion = 0; //Falta extraer detracción
                            $netoPago = $total - $detraccion;

                            $validar = ActaRetiro::create()->validarFacturacion_proveedor($personaId, $serie, $numero)[0];

                            if ($validar['validacion'] == 1) {
                                throw new WarningException("Existe una factura registrada con los mismo datos");
                            }
                            $valorizacion = ActaRetiro::create()->registrarFacturaProveedor(
                                $serie,
                                $numero,
                                $subTotal,
                                $igv,
                                $total,
                                $detraccion,
                                $netoPago,
                                $usuarioId,
                                null,
                                null,
                                $personaId,
                                null,
                                $dataDocumento[0]['moneda_id']
                            );
                        }
                    }
                }
            }

            $respuesta =  new stdClass();
            $respuesta->mensaje = "La operación se generó con éxito";
            return  $respuesta;
        }
    }
}
