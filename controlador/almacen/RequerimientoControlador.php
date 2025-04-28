<?php

//require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/RequerimientoNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/MatrizAprobacionNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/OrdenCompraServicioNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/MovimientoNegocio.php';

class RequerimientoControlador extends AlmacenIndexControlador
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
        $estado = $criterios['estado'];

        $tamanio = count($data);
        $stringProgressBar = "";
        $stringAcciones = "";
        for ($i = 0; $i < $tamanio; $i++) {
            $stringAcciones = "";
            $matrizUsuario = null;
            $areaId = null;
            $esUrgencia = null;

            if($data[$i]['documento_tipo_id'] == Configuraciones::SOLICITUD_REQUERIMIENTO){
                $dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($data[$i]['id']);
                foreach ($dataDocumento as $key => $value) {
                    if($value['descripcion'] == "Urgencia" && $value['valor'] == "Si") {
                        $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoUrgente($data[$i]['documento_tipo_id']);
                    }
                    if($value['tipo'] == "43") {
                        $areaId = $value['valorid'];
                    }
                }
                if($matrizUsuario == null){
                    foreach ($dataDocumento as $key => $value) {
                        switch ($value['tipo']) {
                            case '43':
                                $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoXArea($data[$i]['documento_tipo_id'], $value['valorid']);
                                break;
                        }
                        if($value['descripcion'] == "Tipo de requerimiento" && $value['valor'] == "Servicio") {
                            $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXRequerimientoServicio($data[$i]['documento_tipo_id'], 2, $areaId);
                        }
                    }
                }
            }else{
                $matrizUsuario = MatrizAprobacionNegocio::create()->obtenerMatrizXDocumentoTipoXArea($data[$i]['documento_tipo_id']);
            }
            $movimientoDetalle = MovimientoBien::create()->obtenerXIdMovimiento($data[$i]['movimiento_id']);

            $sumaDetalle = array_reduce($movimientoDetalle, function ($acumulador, $seleccion) {
                return $acumulador + ($seleccion['cantidad'] * $seleccion['valor_monetario']);
            }, 0);
            
            if(($data[$i]['documento_tipo_id'] == Configuraciones::ORDEN_COMPRA || $data[$i]['documento_tipo_id'] == Configuraciones::ORDEN_SERVICIO)&& 30000 > $sumaDetalle){
                $nivelM=1;
                $matrizUsuario = array_filter($matrizUsuario, function($item) use ($nivelM) {
                    return $item['nivel'] <= $nivelM;
                });
            }

            $usuario_estado = DocumentoNegocio::create()->obtenerDocumentoDocumentoEstadoXdocumentoId($data[$i]['id'], "0,1");

            $stringProgressBar = "<div class='progress'>";
            $arrayAprobadores = [];
            $arrayAprobaciones = [];
            $sinAprobar = [];

            $agrupados = [];

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
            $porcentajeDividido = 100 / ($tamanioMatriz == 1 ? 2 : $tamanioMatriz);

            if($tamanioMatriz == 1){
                $arrayMatriz = array(
                    "usuario_aprobador_id" => $data[$i]["usuario_id"],  "nivel" => 1, "nombre" => $usuario_estado[0]['nombre']);
                array_push($matrizUsuario, $arrayMatriz);
            }

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
                    if($data[$i]['estado_descripcion'] != "Rechazado"){
                        $mensaje = "Por aprobar de,";
                    }
                    switch ($value["nivel"]) {
                        case "1":
                            $andera_sinaprobar = true;
                            foreach ($usuario_estado as $val) {
                                // if($value["usuario_aprobador_id"] == $val["usuario_creacion"] && $val["estado_descripcion"] != "Registrado"){
                                $valores = explode(',', $value["usuario_aprobador_id"]);
                                if(in_array($val["usuario_creacion"], $valores) && $val["estado_descripcion"] != "Registrado"){
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
                if ($mensaje == "Aprobado por,") {
                    $valoresAp = explode(',', $value['usuario_aprobador_id']);
                    foreach ($usuario_estado as $val) {
                        if (in_array($val['usuario_creacion'], $valoresAp)) {
                            $value['nombre'] = $val['nombre'];
                        }
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

            $result = [];
            foreach ($arrayAprobadores as $item) {
                if (strpos($item, ',') !== false) {
                    $result = explode(',', $item);
                } else {
                    array_push($result ,$item);
                }
            }
            $arrayAprobadores = array_values($result);
            $valoresFiltro = explode(',', $filtrado[0]['usuario_aprobador_id']);

            $icon_aprobacion = "<i class='fa fa-eye' style='color:green;' title='Ver detalle'></i>";
            if (in_array($usuarioId, $arrayAprobadores) && !in_array($usuarioId, $arrayAprobaciones) && in_array($usuarioId, $valoresFiltro) && $data[$i]['estado_descripcion'] != "Rechazado") {
                $data[$i]['uasurio_estado_descripcion'] = "Por Aprobar";
                $icon_aprobacion = "<i class='fa fa-check' style='color:blue;' title='Aprobar'></i>";

                // if($data[$i]['documento_tipo_id'] == Configuraciones::SOLICITUD_REQUERIMIENTO){
                //     $stringAcciones .= "<a href='#' onclick='editarDocumento(" . $data[$i]['id'] . ", 392)'><i class='fa fa-edit' style='color:purple;' title='Editar documento'></i></a>&nbsp;";
                // }
            }

            if(count($usuario_estado) < count($matrizUsuario) && $data[$i]['estado_descripcion'] != "Rechazado"){
                $data[$i]['estado_descripcion'] = "Por Aprobar";
            }
            $stringAcciones .= "<a href='#' onclick='visualizar(" . $data[$i]['id'] . ", " . $data[$i]['movimiento_id']. ", " . $data[$i]['documento_estado_id']. ", \"".$data[$i]['uasurio_estado_descripcion']."\", " . $data[$i]['documento_tipo_id']. ", \"".$data[$i]['documento_tipo_descripcion']."\")'>". $icon_aprobacion."</a>&nbsp;";
            $data[$i]['progreso'] = $stringProgressBar;
            $data[$i]['acciones'] = $stringAcciones;
        }

        if($estado == "0"){
            $filtrados = $data;
        }else{
            $filtrados = array_values(array_filter($data, function($item) use($estado){
                return $item['estado_descripcion'] === $estado;
            }));  
        }

        return $this->obtenerRespuestaDataTable($filtrados, $elementosFiltrados, $elementosTotales);
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
        $documentoId = $this->getParametro("documentoId");
        return RequerimientoNegocio::create()->visualizarConsolidado($documentoId);
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

    public function visualizarDistribucionPagos(){
        $documentoId = $this->getParametro("documentoId");
        return OrdenCompraServicioNegocio::create()->visualizarDistribucionPagos($documentoId);
    }

    public function obtenerDocumentoAdjuntoXDistribucionPagos(){
        $distribucionPagoId = $this->getParametro("distribucionPagoId");
        return OrdenCompraServicioNegocio::create()->obtenerDocumentoAdjuntoXDistribucionPagos($distribucionPagoId);
    }

    public function abrirPdfCuadroComparativoCotizacion(){
        $documentoId = $this->getParametro("documentoId");
        $usuarioId = $this->getUsuarioId();
        $dataRelacionada = DocumentoNegocio::create()->obtenerDocumentosRelacionadosXDocumentoId($documentoId);
        foreach($dataRelacionada as $itemRelacion){
          if($itemRelacion['documento_tipo_id'] == Configuraciones::GENERAR_COTIZACION){
            return MovimientoNegocio::create()->imprimirExportarPDFDocumento(Configuraciones::GENERAR_COTIZACION, $itemRelacion['documento_relacionado_id'], $usuarioId);
          }
        }
    }

    public function eliminarPDF()
    {
      /** @var string */
      $url = __DIR__. '/../../'.$this->getParametro("url");
      unlink($url);
      return 1;
    }
  
    // EDICION
    public function validarDocumentoEdicion()
    {
        $documentoId = $this->getParametro("documentoId");
        return MovimientoNegocio::create()->validarDocumentoEdicion($documentoId);
    }
}
