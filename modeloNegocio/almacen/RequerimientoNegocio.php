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
            case 397:
                $movimientoTipoId = 144;
                break;
            case 400:
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
        $estadoId = $criterios['estado'];
        if (!ObjectUtil::isEmpty($tipo)) {
            if ($tipo != 0) {
                $documentoTipoId = $tipo;
            }
        }
        if ($documentoTipoId == Configuraciones::ORDEN_COMPRA || $documentoTipoId == Configuraciones::ORDEN_SERVICIO) {
            $areaId = null;
        }

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return Requerimiento::create()->obtenerRequerimientosXCriterios($fechaEmisionInicio, $fechaEmisionFin, $documentoTipoId, $areaId, $requerimiento_tipo, $estadoId, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
    }

    public function obtenerCantidadRequerimientosXCriterios($criterios, $columns, $order)
    {
        $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
        $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
        $documentoTipoId = $criterios['documento_tipo'];
        $areaId = $criterios['area'];
        $requerimiento_tipo = $criterios['requerimiento_tipo'];
        $tipo = $criterios['tipo'];
        $estadoId = $criterios['estado'];
        if (!ObjectUtil::isEmpty($tipo)) {
            if ($tipo != 0) {
                $documentoTipoId = $tipo;
            }
        }
        if ($documentoTipoId == Configuraciones::ORDEN_COMPRA || $documentoTipoId == Configuraciones::ORDEN_SERVICIO) {
            $areaId = null;
        }

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return Requerimiento::create()->obtenerCantidadRequerimientosXCriterios($fechaEmisionInicio, $fechaEmisionFin, $documentoTipoId, $areaId, $requerimiento_tipo, $estadoId, $columnaOrdenar, $formaOrdenar);
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
        foreach ($detalle as $index => $item) {
            $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleXMovimientoBienId($item['movimiento_bien_id']);
            $detalle[$index]["movimiento_bien_detalle"] = $resMovimientoBienDetalle;
        }
        $respuesta->detalle = $detalle;
        return $respuesta;
    }

    public function visualizarRequerimiento($id, $movimientoId)
    {
        $respuesta = new stdClass();
        $detalle =  MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);
        $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($id);
        $respuesta->documentoId = $id;

        $respuesta->detalle = $detalle;
        return $respuesta;
    }

    public function visualizarConsolidado($documentoId)
    {
        $respuesta = new stdClass();

        $dataRelacionada = DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoId($documentoId);
        foreach ($dataRelacionada as $itemRelacion) {
            if ($itemRelacion['documento_tipo_id'] == Configuraciones::GENERAR_COTIZACION || $itemRelacion['documento_tipo_id'] == Configuraciones::GENERAR_COTIZACION_SERVICIO) {
                $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($itemRelacion['documento_relacionado_id']);
                $respuesta->dataDocumentoCabecera = DocumentoNegocio::create()->obtenerDocumentoXDocumentoId($itemRelacion['documento_relacionado_id']);
                $detalle =  MovimientoBien::create()->obtenerXIdMovimiento($respuesta->dataDocumentoCabecera[0]['movimiento_id']);
                $documento_detalle = Documento::create()->obtenerDocumentoDetalleDatos($itemRelacion['documento_relacionado_id']);
            }
        }



        foreach ($detalle as $index => $item) {
            $resMovimientoBienDetalle = MovimientoBien::create()->obtenerMovimientoBienDetalleXMovimientoBienId($item['movimiento_bien_id']);
            $detalle[$index]["movimiento_bien_detalle"] = $resMovimientoBienDetalle;
        }

        $respuesta->detalle = $detalle;
        $respuesta->documento_detalle = $documento_detalle;

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

        $sumaDetalle = $dataDocumento[0]['total'];

        $data = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($documentoId, 3, $usuarioId); // 3 = Aprobado
        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("Hubo un problema al aporbar Orden de compra");
        }

        $validarDocumento = MatrizAprobacion::create()->validarDocumentoAprobacionUltimoNivelXMonto($documentoId, $dataDocumento[0]['documento_tipo_id'], $sumaDetalle);
        if ($validarDocumento[0]["validar"] == 1) {
            $dataRelacionada = DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoId($documentoId);
            foreach ($dataRelacionada as $itemRelacion) {
                if (($itemRelacion['documento_tipo_id'] == Configuraciones::COTIZACIONES || $itemRelacion['documento_tipo_id'] == Configuraciones::COTIZACION_SERVICIO) && $itemRelacion['documento_estado_id'] == 16) {
                    $data = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($itemRelacion['documento_relacionado_id'], 3, $usuarioId); // 3 = Aprobado
                }
                if (($itemRelacion['documento_tipo_id'] == Configuraciones::GENERAR_COTIZACION || $itemRelacion['documento_tipo_id'] == Configuraciones::GENERAR_COTIZACION_SERVICIO) && $itemRelacion['documento_estado_id'] == 17) {
                    $data = DocumentoNegocio::create()->ActualizarDocumentoEstadoId($itemRelacion['documento_relacionado_id'], 3, $usuarioId); // 3 = Aprobado
                }
            }
        }

        $respuesta =  new stdClass();
        $respuesta->mensaje = "La operación se generó con éxito";
        return  $respuesta;
    }

    public function generarMatrizDocumento($documentoTipoId, $documentoId, $movimientoId, $usuarioId, $total = null)
    {
        $matrizUsuario = null;
        $areaId = null;
        $esUrgencia = null;

        if ($documentoTipoId == Configuraciones::SOLICITUD_REQUERIMIENTO) {
            $dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($documentoId);
            foreach ($dataDocumento as $key => $value) {
                if ($value['descripcion'] == "Urgencia" && $value['valor'] == "Si") {
                    $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoUrgente($documentoTipoId);
                }
                if ($value['tipo'] == "43") {
                    $areaId = $value['valorid'];
                }
            }
            if ($matrizUsuario == null) {
                foreach ($dataDocumento as $key => $value) {
                    switch ($value['tipo']) {
                        case '43':
                            $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoXArea($documentoTipoId, $value['valorid']);
                            break;
                    }
                    if ($value['descripcion'] == "Tipo de requerimiento" && $value['valor'] == "Servicio") {
                        $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXRequerimientoServicio($documentoTipoId);
                    }
                }
            }
        } else {
            $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoXArea($documentoTipoId);
        }

        $filtrado = array_values(array_filter($matrizUsuario, function ($item) {
            return $item['nivel'] == 2;
        }))[0]['monto_aprobacion_max'];
        if (($documentoTipoId == Configuraciones::ORDEN_COMPRA || $documentoTipoId == Configuraciones::ORDEN_SERVICIO) && $filtrado > $total) {
            $nivelM = 2;
            $matrizUsuario = array_filter($matrizUsuario, function ($item) use ($nivelM) {
                return $item['nivel'] <= $nivelM;
            });
        }

        $usuario_estado = DocumentoNegocio::create()->obtenerDocumentoDocumentoEstadoXdocumentoId($documentoId, "0,1");
        $agrupados = [];

        usort($matrizUsuario, function ($a, $b) {
            if ($a['nivel'] == $b['nivel']) {
                return 0;
            }
            return ($a['nivel'] < $b['nivel']) ? -1 : 1;
        });
        foreach ($matrizUsuario as $usuario) {
            $key = $usuario['nivel'] . '-' . $usuario['area_id'];

            if (!isset($agrupados[$key])) {
                // Si no existe aún esa combinación, la agregamos tal cual
                $agrupados[$key] = $usuario;
            } else {
                // Si ya existe, concatenamos nombre y usuario_aprobador_id
                $agrupados[$key]['nombre'] .= ' | ' . $usuario['nombre'];
                $agrupados[$key]['usuario_aprobador_id'] .= ',' . $usuario['usuario_aprobador_id'];
            }
        }

        // Resultado final
        $matrizUsuario = array_values($agrupados); // para tener índice limpio si lo necesitas

        $tamanioMatriz = count($matrizUsuario);

        if ($tamanioMatriz == 1) {
            $arrayMatriz = array(
                "usuario_aprobador_id" => $usuarioId,
                "nivel" => 1,
                "nombre" => $usuario_estado[0]['nombre']
            );
            array_push($matrizUsuario, $arrayMatriz);
        }

        return $matrizUsuario;
    }

    //
    public function obtenerConfiguracionesInicialesSeguimientoRequerimiento($idEmpresa)
    {
        $respuesta = new stdClass();
        $respuesta->bien = BienNegocio::create()->obtenerBienKardexXEmpresa($idEmpresa);
        $respuesta->bien_tipo = BienTipo::create()->obtener();
        return $respuesta;
    }

    public function obtenerSeguimientoRequerimientoXCriterios($bienIds, $bienTipoIds, $fechaInicio, $fechaFin, $serie, $numero)
    {
        $respuesta = new stdClass();
        $bienIds = Util::convertirArrayXCadena($bienIds);
        $bienTipoIds = Util::convertirArrayXCadena($bienTipoIds);
        $data = Requerimiento::create()->obtenerSeguimientoRequerimientoXCriterios($bienIds, $bienTipoIds, $fechaInicio, $fechaFin, $serie, $numero);
        foreach ($data as $index => $dataItem) {
            $usuario_estado = DocumentoNegocio::create()->obtenerDocumentoDocumentoEstadoXdocumentoId($dataItem['documento_id'], "0,1");
            $dataEsatdoRQ = "";
            $dataEsatdoRQFecha = "";
            foreach ($usuario_estado as $indexUsuarioEstado => $usuario_estadoItem) {
                $dataEsatdoRQ .= "<br><strong>".($indexUsuarioEstado + 1) . "." . $usuario_estadoItem['estado_descripcion'] . ":</strong> " . $usuario_estadoItem['nombre'];
                $dataEsatdoRQFecha .= "<br><strong>".($indexUsuarioEstado + 1) . ".</strong> " . $usuario_estadoItem['fecha_creacion'];
            }
            $data[$index]['aprobacionRQ'] = $dataEsatdoRQ;
            $data[$index]['aprobacionRQFecha'] = $dataEsatdoRQFecha;

            $dataUsuarioGenerador = "";
            $dataUsuarioGeneradorEstado = "";
            $dataUsuarioGeneradorFecha = "";
            $dataOC = "";
            $dataOCEstado = "";
            $dataOCFecha = "";
            $dataRelacionada = DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoIdSeguimiento($dataItem['documento_id']);
            foreach ($dataRelacionada as $itemRelacion) {
                if ($itemRelacion['documento_tipo_id'] == Configuraciones::GENERAR_COTIZACION || $itemRelacion['documento_tipo_id'] == Configuraciones::GENERAR_COTIZACION_SERVICIO) {
                    $dataUsuarioGenerador .= $itemRelacion['solicitante_nombre_completo'];
                    $dataUsuarioGeneradorEstado .= $itemRelacion['documento_estado'];
                    $dataUsuarioGeneradorFecha .= $itemRelacion['fecha_creacion'];
                }
                if ($itemRelacion['documento_tipo_id'] == Configuraciones::ORDEN_COMPRA || $itemRelacion['documento_tipo_id'] == Configuraciones::ORDEN_SERVICIO) {
                    $dataOC .= $itemRelacion['serie_numero'];
                    $dataOCEstado .= $itemRelacion['documento_estado'];
                    $dataOCFecha .= $itemRelacion['fecha_creacion'];
                }
            }
            $data[$index]['usuarioGenerador'] = $dataUsuarioGenerador;
            $data[$index]['usuarioGeneradorEstado'] = $dataUsuarioGeneradorEstado;
            $data[$index]['usuarioGeneradorFecha'] = $dataUsuarioGeneradorFecha;
            $data[$index]['OC'] = $dataOC;
            $data[$index]['OCEstado'] = $dataOCEstado;
            $data[$index]['OCFecha'] = $dataOCFecha;
        }


        return $respuesta->data = $data;
    }
}
