<?php

//require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/RequerimientoNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/MatrizAprobacionNegocio.php';

class RequermientoControlador extends AlmacenIndexControlador
{

    public function obtenerConfiguracionInicialListado()
    {
        $usuarioId = $this->getUsuarioId();
        $opcionId = $this->getOpcionId();
        $data = RequerimientoNegocio::create()->obtenerConfiguracionInicialListado($usuarioId, $opcionId);
        return $data;
    }

    public function obtenerRequerimientos()
    {
        $usuarioId = $this->getUsuarioId();
        $criterios = $this->getParametro("criterios");
        $elementosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");

        $data = RequerimientoNegocio::create()->obtenerRequerimientosXCriterios($criterios, $elementosFiltrados, $usuarioId, $columns, $order, $start);
        $response_cantidad_total = RequerimientoNegocio::create()->obtenerCantidadRequerimientosXCriterios($criterios, $columns, $order);
        $response_cantidad_total[0]['total'];
        $elementosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];

        $tamanio = count($data);
        $stringProgressBar = "";
        $stringAcciones = "";
        for ($i = 0; $i < $tamanio; $i++) {
            $stringAcciones = "";
            $matrizUsuario = null;

            if($data[$i]['documento_tipo_id'] == 280){
                $dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($data[$i]['id']);
                foreach ($dataDocumento as $key => $value) {
                    switch ($value['tipo']) {
                        case '43':
                            $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoXArea($data[$i]['documento_tipo_id'], $value['valorid']);
                            break;
                    }
                }
            }else{
                $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoXArea($data[$i]['documento_tipo_id']);
            }
            $movimientoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($data[$i]['movimiento_id']);

            $sumaDetalle = array_reduce($movimientoDetalle, function ($acumulador, $seleccion) {
                return $acumulador + ($seleccion['cantidad'] * $seleccion['valor_monetario']);
            }, 0);
            
            if($data[$i]['urgencia'] != "Si" && $data[$i]['urgencia'] != ""){
                $nivelM=1;
                $matrizUsuario = array_filter($matrizUsuario, function($item) use ($nivelM) {
                    return $item['nivel'] <= $nivelM;
                });
            }
            if($data[$i]['documento_tipo_id'] == 281 && $sumaDetalle){

            }

            $usuario_estado = DocumentoNegocio::create()->obtenerDocumentoDocumentoEstadoXdocumentoId($data[$i]['id'], "0,1");

            $tamanioMatriz = count($matrizUsuario);
            $porcentajeDividido = 100 / $tamanioMatriz;
            $stringProgressBar = "<div class='progress'>";
            $arrayAprobadores = [];
            $arrayAprobaciones = [];
            $sinAprobar = [];
            foreach ($matrizUsuario as $key => $value) {
                $colorBar = "";
                $colorBar_none = "background-color:white;";
                $mensaje = "";

                if ($usuario_estado[$key]["estado_descripcion"] == "Registrado") {
                    $colorBar = "progress-bar-success";
                    $colorBar_none = "";
                    $value["nombre"] = $usuario_estado[$key]["nombre"];
                    $mensaje = $usuario_estado[$key]["estado_descripcion"] .' por';
                } else {
                    $arrayAprobadores[] = $value["usuario_aprobador_id"];
                    $mensaje = "Por aprobar de,";
                    switch ($value["nivel"]) {
                        case "1":
                            $andera_sinaprobar = true;
                            foreach ($usuario_estado as $val) {
                                if($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado"){
                                    $colorBar = "progress-bar-info";
                                    $colorBar_none = "";
                                    $mensaje = $usuario_estado[$key]["estado_descripcion"]. " por,";
                                    $arrayAprobaciones[] = $usuario_estado[$key]["usuario_creacion"];
                                    $andera_sinaprobar = true;
                                }else{
                                    $andera_sinaprobar = false;
                                }
                            }
                            if($andera_sinaprobar == false){
                                $sinAprobar [] = array("usuario_aprobador_id" => $value["usuario_aprobador_id"], "nivel"=> $value["nivel"]);
                            }
                            break;
                        case "2":
                            $andera_sinaprobar = true;
                            foreach ($usuario_estado as $val) {
                                if($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado"){
                                    $colorBar = "progress-bar-warning";
                                    $colorBar_none = "";
                                    $mensaje = $usuario_estado[$key]["estado_descripcion"]. " por,";
                                    $arrayAprobaciones[] = $usuario_estado[$key]["usuario_creacion"];
                                    $andera_sinaprobar = true;
                                }else{
                                    $andera_sinaprobar = false;
                                }
                            }
                            if($andera_sinaprobar == false){
                                $sinAprobar [] = array("usuario_aprobador_id" => $value["usuario_aprobador_id"], "nivel"=> $value["nivel"]);
                            }
                            break;
                        case "3":
                            $andera_sinaprobar = true;
                            foreach ($usuario_estado as $val) {
                                if($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado"){
                                    $colorBar = "progress-bar-danger";
                                    $colorBar_none = "";
                                    $mensaje = $usuario_estado[$key]["estado_descripcion"]. " por,";
                                    $arrayAprobaciones[] = $usuario_estado[$key]["usuario_creacion"];
                                    $andera_sinaprobar = true;
                                }else{
                                    $andera_sinaprobar = false;
                                }
                            }
                            if($andera_sinaprobar == false){
                                $sinAprobar [] = array("usuario_aprobador_id" => $value["usuario_aprobador_id"], "nivel"=> $value["nivel"]);
                            }
                            break;
                    }
                }
                $stringProgressBar .= "<div class='progress-bar " . $colorBar . " progress-bar-striped' role='progressbar' aria-valuenow='" . $porcentajeDividido . "' aria-valuemin='0' aria-valuemax='100' style='width: " . $porcentajeDividido . "% ;border: 1px solid #000;" . $colorBar_none . "' title='" .$mensaje.' '. $value['nombre'] . "'>" .
                    "</div>";
            }
            $stringProgressBar .= "</div>";

            $niveles = array_column($sinAprobar, 'nivel'); // Extrae todos los valores de 'nivel' en un array
            $nivel_minimo = min($niveles); // Encuentra el valor mínimo
            
            // Paso 2: Filtrar los elementos que tengan el 'nivel' mínimo
            $filtrado = array_filter($sinAprobar, function($item) use ($nivel_minimo) {
                return $item['nivel'] == $nivel_minimo;
            });

            if (in_array($usuarioId, $arrayAprobadores) && !in_array($usuarioId, $arrayAprobaciones) && $filtrado[0]['usuario_aprobador_id'] == $usuarioId) {
                // $stringAcciones .= "<a href='#' onclick='aprobar(" . $data[$i]['id'] . ", " . $data[$i]['movimiento_id']. ", " . $data[$i]['documento_tipo_id']. ")'><i class='fa fa-check' style='color:blue;' title='Aprobar'></i></a>&nbsp;";
                // $stringAcciones .= "<a href='#' onclick='rechazar(" . $data[$i]['id'] . ", " . $data[$i]['movimiento_id'].  ", " . $data[$i]['documento_tipo_id']. ")'><i class='fa fa-times' style='color:red;' title='Rechazar '></i></a>&nbsp;";
                if($criterios['documento_tipo'] == 282){
                    $stringAcciones .= "<a href='#' onclick='archivosAdjuntos(" . $data[$i]['id'] . ", " . $data[$i]['movimiento_id']. ")'><i class='fa fa-cloud-upload' style='color:blue;' title='Revisar archivos adjuntos'></i></a>&nbsp;";
                }
                $data[$i]['estado_descripcion'] = "Por Aprobar";
            }

            $stringAcciones .= "<a href='#' onclick='visualizar(" . $data[$i]['id'] . ", " . $data[$i]['movimiento_id']. ", " . $data[$i]['documento_estado_id']. ", \"".$data[$i]['estado_descripcion']."\", " . $data[$i]['documento_tipo_id']. ", \"".$data[$i]['documento_tipo_descripcion']."\")'><i class='fa fa-eye' style='color:green;' title='Ver detalle'></i></a>&nbsp;";
            $data[$i]['progreso'] = $stringProgressBar;
            $data[$i]['acciones'] = $stringAcciones;
        }

        return $this->obtenerRespuestaDataTable($data, $elementosFiltrados, $elementosTotales);
    }


    public function visualizarSolicitudRequerimiento()
    {
        $id = $this->getParametro("id");
        $movimientoId = $this->getParametro("movimientoId");
        return RequerimientoNegocio::create()->visualizarSolicitudRequerimiento($id, $movimientoId);
    }

    public function visualizarRequerimiento()
    {
        $id = $this->getParametro("id");
        $movimientoId = $this->getParametro("movimientoId");
        return RequerimientoNegocio::create()->visualizarRequerimiento($id, $movimientoId);
    }

    public function visualizarOrdenCompraServicio()
    {
        $id = $this->getParametro("id");
        $movimientoId = $this->getParametro("movimientoId");
        return RequerimientoNegocio::create()->visualizarRequerimiento($id, $movimientoId);
    }

    public function visualizarConsolidado()
    {
        $id = $this->getParametro("id");
        $movimientoId = $this->getParametro("movimientoId");
        return RequerimientoNegocio::create()->visualizarConsolidado($id, $movimientoId);
    }

    public function aprobarRequerimiento()
    {
        $this->setTransaction();
        $id = $this->getParametro("id");
        $usuarioId = $this->getUsuarioId();
        return RequerimientoNegocio::create()->aprobarRequerimiento($id, $usuarioId);
    }

    public function aprobarConsolidado()
    {
        $this->setTransaction();
        $id = $this->getParametro("id");
        $checked1 = $this->getParametro("checked1");
        $checked2 = $this->getParametro("checked2");
        $checked3 = $this->getParametro("checked3");
        $usuarioId = $this->getUsuarioId();
        return RequerimientoNegocio::create()->aprobarConsolidado($id, $usuarioId, $checked1, $checked2, $checked3);
    }

    public function rechazar()
    {
        $this->setTransaction();
        $documentoId = $this->getParametro("documentoId");
        $motivoRechazo = $this->getParametro("motivoRechazo");
        $usuarioId = $this->getUsuarioId();
        return RequerimientoNegocio::create()->rechazarConsolidado($documentoId, $usuarioId, $motivoRechazo);
    }

    public function obtenerDocumentoAdjuntoXDocumentoId(){
        $documentoId = $this->getParametro("documentoId");
        return DocumentoNegocio::create()->obtenerDocumentoAdjuntoXDocumentoId($documentoId);
    }


    public function aprobarOrdenCompraServicio(){
        $this->setTransaction();
        $id = $this->getParametro("id");
        $usuarioId = $this->getUsuarioId();
        return RequerimientoNegocio::create()->aprobarOrdenCompraServicio($id, $usuarioId);
    }
}
