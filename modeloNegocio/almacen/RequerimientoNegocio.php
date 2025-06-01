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
                if ($itemPerfil['id'] == PerfilNegocio::PERFIL_ADMINISTRADOR_ID || $itemPerfil['id'] == PerfilNegocio::PERFIL_ADMINISTRADOR_TI_ID || $itemPerfil['id'] == PerfilNegocio::PERFIL_JEFE_LOGISTA || $itemPerfil['id'] == PerfilNegocio::PERFIL_APROBADOR_SOLICITANTE_REQUERIMIENTO_URGENTE || $itemPerfil['id'] == PerfilNegocio::PERFIL_LOGISTA || $itemPerfil['id'] == PerfilNegocio::PERFIL_TODAS_AREAS) {
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

    public function generarMatrizDocumento($documentoTipoId, $documentoId, $movimientoId, $usuarioId, $datosDocumento = null)
    {
        $matrizUsuario = null;
        $areaId = null;
        $esUrgencia = null;

        if ($documentoTipoId == Configuraciones::SOLICITUD_REQUERIMIENTO) {
            $dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($documentoId);
            foreach ($dataDocumento as $key => $value) {
                if ($value['descripcion'] == "Urgencia" && $value['valor'] == "Si") {
                    $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoUrgente($documentoTipoId, 1);
                }
                if ($value['descripcion'] == "Urgencia" && $value['valor'] == "Si Junta") {
                    $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoUrgente($documentoTipoId, 3);
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
        $total = $datosDocumento[0]['total'];
        if ($datosDocumento[0]['moneda_id'] == 4) {
            $total = $datosDocumento[0]['total'] * $datosDocumento[0]['tipo_cambio'];
        }
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
        // $respuesta->bien = BienNegocio::create()->obtenerBienKardexXEmpresa($idEmpresa);
        $respuesta->bien = [["id" => "", "text" => ""]];
        $respuesta->bien_tipo = BienTipo::create()->obtener();
        return $respuesta;
    }

    public function obtenerSeguimientoRequerimientoXCriterios($bienIds, $bienTipoIds, $fechaInicio, $fechaFin, $serie, $numero, $bandera = null)
    {
        $respuesta = new stdClass();
        $bienIds = Util::convertirArrayXCadena($bienIds);
        $bienTipoIds = Util::convertirArrayXCadena($bienTipoIds);
        $fechaInicio = $this->formatearFechaBD($fechaInicio);
        $fechaFin = $this->formatearFechaBD($fechaFin);
        $data = Requerimiento::create()->obtenerSeguimientoRequerimientoXCriterios($bienIds, $bienTipoIds, $fechaInicio, $fechaFin, $serie, $numero);
        $ids = array_values(array_unique(array_column($data, 'documento_id')));
        $bien_ids = array_values(array_unique(array_column($data, 'bien_id')));
            
        $arrayDocumentos = [];
        foreach ($ids as $item) {
            $usuario_estado = DocumentoNegocio::create()->obtenerDocumentoDocumentoEstadoXdocumentoId($item, "0,1");
            $dataEsatdoRQ = "";
            $dataEsatdoRQFecha = "";
            foreach ($usuario_estado as $indexUsuarioEstado => $usuario_estadoItem) {
                if ($bandera == 1) {
                    $dataEsatdoRQ .= ($indexUsuarioEstado + 1) . "." . $usuario_estadoItem['estado_descripcion'] . ": " . $usuario_estadoItem['nombre'] . "\n";
                    $dataEsatdoRQFecha .= ($indexUsuarioEstado + 1) . ": " . $usuario_estadoItem['fecha_creacion'] . "\n";
                } else {
                    $dataEsatdoRQ .= "<strong>" . ($indexUsuarioEstado + 1) . "." . $usuario_estadoItem['estado_descripcion'] . ":</strong> " . $usuario_estadoItem['nombre'] . "<br>";
                    $dataEsatdoRQFecha .= "<strong>" . ($indexUsuarioEstado + 1) . ".</strong> " . $usuario_estadoItem['fecha_creacion'] . "<br>";
                }
            }
            $arrayDocumentos [$item ] = array("aprobacionRQ" => $dataEsatdoRQ, "aprobacionRQFecha" => $dataEsatdoRQFecha);
        }

        $convertirCadena = str_replace(["(", ")"], "",  Util::convertirArrayXCadena(array_values($bien_ids)));
        foreach ($data as $index => $dataItem) {
            $documentoId = $dataItem['documento_id'];
                        
            $data[$index]['aprobacionRQ'] = $arrayDocumentos[$documentoId]['aprobacionRQ'];
            $data[$index]['aprobacionRQFecha'] = $arrayDocumentos[$documentoId]['aprobacionRQFecha'];

            $resMovimientoArea_CuadroComparativo = MovimientoBien::create()->movimientoBienDetalleXValorCadena($dataItem['movimiento_bien_id']);
            $dataUsuarioGenerador = "";
            $dataUsuarioGeneradorEstado = "";
            $dataUsuarioGeneradorFecha = "";
            $dataOC = "";
            $dataOCEstado = "";    
            $dataOCFecha = "";
            $arrayDocumentosCotizacion = [];
            $arrayDocumentosOC = [];
            if(!ObjectUtil::isEmpty($resMovimientoArea_CuadroComparativo)){
                if($resMovimientoArea_CuadroComparativo[0]['documento_tipo_id'] == Configuraciones::REQUERIMIENTO_AREA){
                    $resMovimientoBienDetalle_ = MovimientoBien::create()->movimientoBienDetalleXValorCadena($resMovimientoArea_CuadroComparativo[0]['id']);
                    foreach($resMovimientoBienDetalle_ as $indexDetalle => $itemMovimientoBienDetalle){
                        $movimientoBien = MovimientoBien::create()->obtenerXIdMovimientoXBienId($itemMovimientoBienDetalle['movimiento_id'], $convertirCadena);
                        $bandera_agregar = false;
                        if(!ObjectUtil::isEmpty($movimientoBien)) {
                            $bandera_agregar = true;
                        }
                        if ($bandera == 1) {
                            $dataUsuarioGenerador .= $itemMovimientoBienDetalle['nombre_completo'] . "\n";
                            $dataUsuarioGeneradorEstado .= $itemMovimientoBienDetalle['documento_estado'] . "\n";
                            $dataUsuarioGeneradorFecha .= $itemMovimientoBienDetalle['fecha_creacion'] . "\n";
                        } else {
                            $dataUsuarioGenerador .= "<strong>" . ($indexDetalle + 1) . ".</strong> " . $itemMovimientoBienDetalle['nombre_completo'] . "<br>";
                            $dataUsuarioGeneradorEstado .= "<strong>" . ($indexDetalle + 1) . ".</strong> " . $itemMovimientoBienDetalle['documento_estado'] . "<br>";
                            $dataUsuarioGeneradorFecha .= "<strong>" . ($indexDetalle + 1) . ".</strong> " . $itemMovimientoBienDetalle['fecha_creacion'] . "<br>";
                        }
                        $arrayDocumentosCotizacion [$documentoId ] = array("usuarioGenerador" => $dataUsuarioGenerador, "usuarioGeneradorEstado" => $dataUsuarioGeneradorEstado, "usuarioGeneradorFecha" => $dataUsuarioGeneradorFecha);

                        $resMovimientoBienDetalleOC = MovimientoBien::create()->movimientoBienDetalleXValorCadena($itemMovimientoBienDetalle['id']);
                        foreach($resMovimientoBienDetalleOC as $itemOC){
                            $movimientoBien = MovimientoBien::create()->obtenerXIdMovimientoXBienId($itemOC['movimiento_id'], $convertirCadena);
                            $bandera_agregar = false;
                            if(!ObjectUtil::isEmpty($movimientoBien)) {
                                $bandera_agregar = true;
                            }
                            if($bandera_agregar){
                                if($bandera == 1){
                                    $dataOC .= $itemOC['serie_numero']. "\n";
                                    $dataOCEstado .= $itemOC['documento_estado']. "\n";
                                    $dataOCFecha .= $itemOC['fecha_creacion']. "\n";
                                }else{
                                    $dataOC .= "<strong>" . ($indexDetalle + 1) . ".</strong> " . $itemOC['serie_numero']. "<br>";
                                    $dataOCEstado .= "<strong>" . ($indexDetalle + 1) . ".</strong> " . $itemOC['documento_estado']. "<br>";
                                    $dataOCFecha .= "<strong>" . ($indexDetalle + 1) . ".</strong> " . $itemOC['fecha_creacion']. "<br>";
                                }
                                $arrayDocumentosOC [$documentoId ] = array("OC" => $dataOC, "OCEstado" => $dataOCEstado, "OCFecha" => $dataOCFecha);
                            }
                        }
                    }
                }else{
                    // $resMovimientoBienDetalleOC = MovimientoBien::create()->movimientoBienDetalleXValorCadena($resMovimientoArea_CuadroComparativo[0]['id']);
                    $movimientoBien = MovimientoBien::create()->obtenerXIdMovimientoXBienId($resMovimientoArea_CuadroComparativo[0]['movimiento_id'], $convertirCadena);
                    $bandera_agregar = false;
                    if(!ObjectUtil::isEmpty($movimientoBien)) {
                        $bandera_agregar = true;
                    }
                    if ($bandera == 1) {
                        $dataUsuarioGenerador .= $resMovimientoArea_CuadroComparativo[0]['nombre_completo'] . "\n";
                        $dataUsuarioGeneradorEstado .= $resMovimientoArea_CuadroComparativo[0]['documento_estado'] . "\n";
                        $dataUsuarioGeneradorFecha .= $resMovimientoArea_CuadroComparativo[0]['fecha_creacion'] . "\n";
                    } else {
                        $dataUsuarioGenerador .= $resMovimientoArea_CuadroComparativo[0]['nombre_completo'] . "<br>";
                        $dataUsuarioGeneradorEstado .= $resMovimientoArea_CuadroComparativo[0]['documento_estado'] . "<br>";
                        $dataUsuarioGeneradorFecha .= $resMovimientoArea_CuadroComparativo[0]['fecha_creacion'] . "<br>";
                    }
                    $arrayDocumentosCotizacion [$documentoId ] = array("usuarioGenerador" => $dataUsuarioGenerador, "usuarioGeneradorEstado" => $dataUsuarioGeneradorEstado, "usuarioGeneradorFecha" => $dataUsuarioGeneradorFecha);

                    $resMovimientoBienDetalleOC = MovimientoBien::create()->movimientoBienDetalleXValorCadena($resMovimientoArea_CuadroComparativo[0]['id']);
                    foreach($resMovimientoBienDetalleOC as $indexDetalle => $itemMovimientoBienDetalle){
                        $movimientoBien = MovimientoBien::create()->obtenerXIdMovimientoXBienId($itemMovimientoBienDetalle['movimiento_id'], $convertirCadena);
                        $bandera_agregar = false;
                        if(!ObjectUtil::isEmpty($movimientoBien)) {
                            $bandera_agregar = true;
                        }
                        if($bandera_agregar){
                            if($bandera == 1){
                                $dataOC .= $itemMovimientoBienDetalle['serie_numero']. "\n";
                                $dataOCEstado .= $itemMovimientoBienDetalle['documento_estado']. "\n";
                                $dataOCFecha .= $itemMovimientoBienDetalle['fecha_creacion']. "\n";
                            }else{
                                $dataOC .= "<strong>" . ($indexDetalle + 1) . ".</strong> " . $itemMovimientoBienDetalle['serie_numero']. "<br>";
                                $dataOCEstado .= "<strong>" . ($indexDetalle + 1) . ".</strong> " . $itemMovimientoBienDetalle['documento_estado']. "<br>";
                                $dataOCFecha .= "<strong>" . ($indexDetalle + 1) . ".</strong> " . $itemMovimientoBienDetalle['fecha_creacion']. "<br>";
                            }
                            $arrayDocumentosOC [$documentoId ] = array("OC" => $dataOC, "OCEstado" => $dataOCEstado, "OCFecha" => $dataOCFecha);
                        }
                    }
                }
            }

            $data[$index]['usuarioGenerador'] = $arrayDocumentosCotizacion[$documentoId]['usuarioGenerador'];
            $data[$index]['usuarioGeneradorEstado'] = $arrayDocumentosCotizacion[$documentoId]['usuarioGeneradorEstado'];
            $data[$index]['usuarioGeneradorFecha'] = $arrayDocumentosCotizacion[$documentoId]['usuarioGeneradorFecha'];

            $data[$index]['OC'] = $arrayDocumentosOC[$documentoId]['OC'];
            $data[$index]['OCEstado'] = $arrayDocumentosOC[$documentoId]['OCEstado'];
            $data[$index]['OCFecha'] = $arrayDocumentosOC[$documentoId]['OCFecha'];
        }

        return $respuesta->data = $data;
    }

    public function exportarSeguimientoRequerimientoXCriterios($bienIds, $bienTipoIds, $fechaInicio, $fechaFin, $serie, $numero)
    {
        $data = $this->obtenerSeguimientoRequerimientoXCriterios($bienIds, $bienTipoIds, $fechaInicio, $fechaFin, $serie, $numero, 1);

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
        $drawing->setCoordinates('K' . $i); // Celda donde se insertará
        $drawing->setWorksheet($objPHPExcel->getActiveSheet());


        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Reporte de Seguimiento');
        $objPHPExcel->getActiveSheet()->mergeCells("B$i:M$i");
        $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':T' . $i)->applyFromArray($estiloDetCabecera);
        $i++;

        //ANCHOS DE COLUMNAS        
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(5.7);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(100);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(12.14);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(24.29);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setWidth(50);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setWidth(60);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setWidth(50);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('O')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('P')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('Q')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('R')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('S')->setWidth(30);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('T')->setWidth(30);

        $i = $i + 3;
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, '#');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'COD. PRODUCTO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'PRODUCTO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'UNIDAD MEDIDA');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'CANTIDAD');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'GRUPO PRODUCTO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'GENERADOR');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'N° DE RQ');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'TIPO DE RQ');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, 'F. CREACIÓN');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'AREA');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . ($i - 1), 'CONDICION 1 APROBACION DE RQ');
        $objPHPExcel->getActiveSheet()->mergeCells("M5:N5");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, 'ESTADO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, 'FECHA');

        $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . ($i - 1), 'CONDICION 2 GENERACION DE COTIZACION');
        $objPHPExcel->getActiveSheet()->mergeCells("O5:Q5");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, 'USUARIO GENERADOR');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, 'ESTADO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, 'FECHA');

        $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . ($i - 1), 'Condicion 3 Generacion de OC');
        $objPHPExcel->getActiveSheet()->mergeCells("R5:T5");
        $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, 'N° OC/OS');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, 'ESTADO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('T' . $i, 'FECHA');

        $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':T' . $i)->applyFromArray($estilos_tabla);
        $objPHPExcel->getActiveSheet()->getStyle('O' . ($i - 1) . ':T' . ($i - 1))->applyFromArray($estilos_tabla);
        $objPHPExcel->getActiveSheet()->getStyle("B$i:T$i")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle("O" . ($i - 1) . ":T" . ($i - 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $i++;

        foreach ($data as $index => $item) {
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, ($index + 1));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $item['bien_codigo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $item['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $item['unidad_medida_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $item['cantidad']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $item['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $item['generador']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $item['serie_numero_requerimiento']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, $item['tipo_requerimiento']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, $item['fecha_creacion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, $item['area_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, $item['aprobacionRQ']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, $item['aprobacionRQFecha']);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, $item['usuarioGenerador']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, $item['usuarioGeneradorEstado']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, $item['usuarioGeneradorFecha']);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, $item['OC']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, $item['OCEstado']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('T' . $i, $item['OCFecha']);


            $objPHPExcel->getActiveSheet()->getStyle('M' . $i)->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('N' . $i)->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('O' . $i)->getAlignment()->setWrapText(true);

            $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':T' . $i)->applyFromArray($estilos_filas);

            $i++;
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporteSeguimiento.xlsx');
        return 1;
    }
}
