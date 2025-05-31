<?php

require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/OrdenCompraServicioNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/AlmacenesNegocio.php';


class AlmacenesControlador extends AlmacenIndexControlador
{

    public function obtenerConfiguracionInicialListadoDocumentos()
    {
        $usuarioId = $this->getUsuarioId();
        $documento_tipo = $this->getParametro("documento_tipo");
        if (!ObjectUtil::isEmpty($documento_tipo)) {
            $data->almacenes = OrganizadorNegocio::create()->getOrganizador(Configuraciones::ALMACEN_LIMA);
        } else {
            $data = AlmacenesNegocio::create()->obtenerConfiguracionInicialListadoDocumentos($usuarioId);
        }

        return $data;
    }

    public function obtenerOrdenCompra()
    {
        $criterios = $this->getParametro("criterios");
        $elementosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");

        $data = AlmacenesNegocio::create()->obtenerOrdenCompraXCriterios($criterios, $elementosFiltrados, $columns, $order, $start);
        $response_cantidad_total = AlmacenesNegocio::create()->obtenerCantidadOrdenCompraXCriterios($criterios, $columns, $order);
        $response_cantidad_total[0]['total'];
        $elementosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];

        return $this->obtenerRespuestaDataTable($data, $elementosFiltrados, $elementosTotales);
    }

    public function visualizarDetalle()
    {
        $id = $this->getParametro("id");
        $movimientoId = $this->getParametro("movimientoId");
        return AlmacenesNegocio::create()->visualizarDetalle($id, $movimientoId);
    }

    public function guardarDetalleRecepcion()
    {
        $this->setTransaction();
        $filasSeleccionadas = $this->getParametro("filasSeleccionadas");
        return AlmacenesNegocio::create()->guardarDetalleRecepcion($filasSeleccionadas);
    }

    public function obtenerRecepcion()
    {
        $usuarioId = $this->getUsuarioId();
        $criterios = $this->getParametro("criterios");
        $elementosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");

        $data = AlmacenesNegocio::create()->obtenerRecepcionXCriterios($criterios, $elementosFiltrados, $columns, $order, $start, $usuarioId);
        $response_cantidad_total = AlmacenesNegocio::create()->obtenerCantidadRecepcionXCriterios($criterios, $columns, $order, $usuarioId);
        $response_cantidad_total[0]['total'];
        $elementosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];

        return $this->obtenerRespuestaDataTable($data, $elementosFiltrados, $elementosTotales);
    }

    public function obtenerConfiguracionInicialListadoPaqueteRecepcion()
    {
        $usuarioId = $this->getUsuarioId();
        $data = AlmacenesNegocio::create()->obtenerConfiguracionInicialListadoPaqueteRecepcion($usuarioId);
        return $data;
    }

    public function obtenerPaqueteAlmacenado()
    {
        $usuarioId = $this->getUsuarioId();
        $criterios = $this->getParametro("criterios");
        $elementosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");

        $data = AlmacenesNegocio::create()->obtenerPaqueteAlmacenadoXCriterios($criterios, $elementosFiltrados, $columns, $order, $start, $usuarioId);
        $response_cantidad_total = AlmacenesNegocio::create()->obtenerCantidadPaqueteAlmacenadoXCriterios($criterios, $columns, $order, $usuarioId);
        $response_cantidad_total[0]['total'];
        $elementosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];

        return $this->obtenerRespuestaDataTable($data, $elementosFiltrados, $elementosTotales);
    }

    public function obtenerPaqueteTrakingDetalleXBienId()
    {
        $bienId = $this->getParametro("bienId");
        $almacen = $this->getParametro("almacen");
        return AlmacenesNegocio::create()->obtenerPaqueteTrakingDetalleXBienId($bienId, $almacen);
    }

    public function obtenerMovimientoPaqueteTraking()
    {
        $id = $this->getParametro("id");
        return AlmacenesNegocio::create()->obtenerMovimientoPaqueteTraking($id);
    }

    public function generarDistribucionQR()
    {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $arrayDatoFila = $this->getParametro("arrayDatoFila");
        $documentoId = $this->getParametro("documentoId");
        $almacenId = $this->getParametro("almacenId");
        $dataFilasSeleccionadas = $this->getParametro("dataFilasSeleccionadas");
        $empresaId = $this->getParametro("empresaId");
        $datosGuia = $this->getParametro("datosGuia");
        return AlmacenesNegocio::create()->generarDistribucionQR($arrayDatoFila, $usuarioId, $documentoId, $almacenId, $dataFilasSeleccionadas, $empresaId, $datosGuia);
    }

    public function uploadAdjunto()
    {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $dataUpload = $this->getParametro("dataUpload");
        return AlmacenesNegocio::create()->uploadAdjunto($dataUpload, $usuarioId);
    }

    //Despacho Lima
    public function obtenerDespacho()
    {
        $usuarioId = $this->getUsuarioId();
        $criterios = $this->getParametro("criterios");
        $elementosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");

        $data = AlmacenesNegocio::create()->obtenerDespachoXCriterios($criterios, $elementosFiltrados, $columns, $order, $start, $usuarioId);
        $response_cantidad_total = AlmacenesNegocio::create()->obtenerCantidadDespachoXCriterios($criterios, $columns, $order, $usuarioId);
        $response_cantidad_total[0]['total'];
        $elementosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];

        return $this->obtenerRespuestaDataTable($data, $elementosFiltrados, $elementosTotales);
    }

    public function visualizarDetalleDespacho()
    {
        $id = $this->getParametro("id");
        $movimientoId = $this->getParametro("movimientoId");
        return AlmacenesNegocio::create()->visualizarDetalleDespacho($id, $movimientoId);
    }

    public function obtenerPaqueteDespachos()
    {
        $usuarioId = $this->getUsuarioId();
        $criterios = $this->getParametro("criterios");
        $elementosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");

        $data = AlmacenesNegocio::create()->obtenerPaqueteDespachoXCriterios($criterios, $elementosFiltrados, $columns, $order, $start, $usuarioId);
        $response_cantidad_total = AlmacenesNegocio::create()->obtenerCantidadPaqueteDespachoXCriterios($criterios, $columns, $order, $usuarioId);
        $response_cantidad_total[0]['total'];
        $elementosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];

        return $this->obtenerRespuestaDataTable($data, $elementosFiltrados, $elementosTotales);
    }

    public function obtenerConfiguracionesIniciales()
    {
        $opcionId = $this->getOpcionId();
        $empresaId = $this->getParametro("empresaId");
        $documentoId = $this->getParametro("documentoId");
        $usuarioId = $this->getUsuarioId();
        if ($opcionId != 416) {
            return AlmacenesNegocio::create()->obtenerConfiguracionInicial($opcionId, $empresaId, $usuarioId, $documentoId);
        } else {
            return MovimientoNegocio::create()->obtenerConfiguracionInicial($opcionId, $empresaId, $usuarioId, $documentoId);
        }
    }

    public function obtenerPlacaVehiculo()
    {
        $id = $this->getParametro("id");
        return AlmacenesNegocio::create()->obtenerPlacaVehiculo($id);
    }

    public function obtenerPaqueteXAlmacenId()
    {
        $id = $this->getParametro("id");
        return AlmacenesNegocio::create()->obtenerPaqueteXAlmacenId($id);
    }

    public function generarDespacho()
    {
        $this->setTransaction();
        $almacenDestino = $this->getParametro("almacenDestino");
        $vehiculoId = $this->getParametro("vehiculoId");
        $pesaje = $this->getParametro("pesaje");
        $usuarioId = $this->getUsuarioId();
        $detalle = $this->getParametro("detalle");

        return AlmacenesNegocio::create()->generarDespacho($almacenDestino, $vehiculoId, $pesaje, $usuarioId, $detalle);
    }

    //Recepcion despacho
    public function obtenerPaqueteRecepcionDespacho()
    {
        $usuarioId = $this->getUsuarioId();
        $criterios = $this->getParametro("criterios");
        $elementosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");

        $data = AlmacenesNegocio::create()->obtenerPaqueteRecepcionDespachoXCriterios($criterios, $elementosFiltrados, $columns, $order, $start, $usuarioId);
        $response_cantidad_total = AlmacenesNegocio::create()->obtenerCantidadPaqueteRecepcionDespachoXCriterios($criterios, $columns, $order, $usuarioId);
        $response_cantidad_total[0]['total'];
        $elementosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];

        return $this->obtenerRespuestaDataTable($data, $elementosFiltrados, $elementosTotales);
    }

    public function getDataOrganizadoresHijos()
    {
        $almacenId = $this->getParametro("almacenId");
        return AlmacenesNegocio::create()->getDataOrganizadoresHijos($almacenId);
    }

    public function generarRecepcionDespacho()
    {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $documentoId = $this->getParametro("documentoId");
        $almacenId = $this->getParametro("almacenId");
        $dataFilasSeleccionadas = $this->getParametro("dataFilasSeleccionadas");
        $empresaId = $this->getParametro("empresaId");
        return AlmacenesNegocio::create()->generarRecepcionDespacho($documentoId, $almacenId, $dataFilasSeleccionadas, $usuarioId, $empresaId);
    }

    //Entrega
    public function obtenerEntrega()
    {
        $opcionId = $this->getOpcionId();
        $usuarioId = $this->getUsuarioId();
        $criterios = $this->getParametro("criterios");
        $elementosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");

        $data = AlmacenesNegocio::create()->obtenerEntregaXCriterios($criterios, $elementosFiltrados, $opcionId, $columns, $order, $start, $usuarioId);
        $response_cantidad_total = AlmacenesNegocio::create()->obtenerCantidadEntregaXCriterios($criterios, $opcionId, $columns, $order, $usuarioId);
        $response_cantidad_total[0]['total'];
        $elementosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];

        return $this->obtenerRespuestaDataTable($data, $elementosFiltrados, $elementosTotales);
    }

    public function obtenerAreRequerimiento()
    {
        $id = $this->getParametro("id");
        return AlmacenesNegocio::create()->obtenerAreRequerimiento($id);
    }

    public function obtenerDetalleRequerimiento()
    {
        $opcionId = $this->getOpcionId();
        $documentoId = $this->getParametro("documentoId");
        $almacenId = $this->getParametro("almacenId");
        $movimientoId = Documento::create()->obtenerDocumentoDatos($documentoId)[0]['movimiento_id'];

        $data =  MovimientoNegocio::create()->obtenerDocumentoRelacion(Configuraciones::SOLICITUD_REQUERIMIENTO, Configuraciones::ENTREGA, $movimientoId, $documentoId, $opcionId, null);
        $data->organizadoresDetalle = AlmacenesNegocio::create()->getDataOrganizadoresHijos($almacenId);
        return $data;
    }

    public function cargarOrganizadoresDetalle()
    {
        $almacenId = $this->getParametro("almacenId");
        return AlmacenesNegocio::create()->getDataOrganizadoresHijos($almacenId);
    }

    public function obtenerStockParaProductosDeCopia()
    {
        $almacenId = $this->getParametro("almacenId");
        $detalle = $this->getParametro("detalle");

        $data = AlmacenesNegocio::create()->obtenerStockParaProductosDeCopia($detalle, $almacenId);
        return $data;
    }

    public function obtenerStockActual()
    {
        //$opcionId = $this->getOpcionId();
        $organizadorId = $this->getParametro("organizadorId");
        $bienId = $this->getParametro("bienId");
        $indice = $this->getParametro("indice");
        $unidadMedidaId = $this->getParametro("unidadMedidaId");
        $organizadorIds = AlmacenesNegocio::create()->obtenerOrganizadorHijos($organizadorId);
        $stock = AlmacenesNegocio::create()->obtenerStockActual($bienId, $indice, $unidadMedidaId, $organizadorIds, 1, $organizadorId);
        return $stock;
    }

    public function obtenerStockPorBien()
    {
        $bienId = $this->getParametro("bienId");
        $unidadMedidaId = $this->getParametro("unidadMedidaId");
        $indice = $this->getParametro("indice");
        $empresaId = $this->getParametro("empresaId");
        $almacenId = $this->getParametro("almacenId");
        return AlmacenesNegocio::create()->obtenerStockPorBien($bienId, $unidadMedidaId, $indice, $almacenId);
    }


    public function enviar()
    {
        // return $this->params;
        $this->setTransaction();
        $opcionId = $this->getOpcionId();
        $usuarioId = $this->getUsuarioId();
        $documentoTipoId = $this->getParametro("documentoTipoId");
        $contOperacionTipoId = $this->getParametro("contOperacionTipoId");
        $camposDinamicos = $this->getParametro("camposDinamicos");
        $detalle = $this->getParametro("detalle");
        $documentoARelacionar = $this->getParametro("documentoARelacionar");
        $valorCheck = $this->getParametro("valor_check");
        $comentario = $this->getParametro("comentario");
        $checkIgv = $this->getParametro("checkIgv");
        $igv_porcentaje = $this->getParametro("igv_porcentaje");
        $monedaId = $this->getParametro("monedaId");
        $empresaId = $this->getParametro("empresaId");
        $accionEnvio = $this->getParametro("accionEnvio");
        // gclv: campo de tipo de pago (contado, credito)
        $tipoPago = $this->getParametro("tipoPago");
        $listaPagoProgramacion = $this->getParametro("listaPagoProgramacion");
        $anticiposAAplicar = $this->getParametro("anticiposAAplicar");
        $percepcion = $this->getParametro("percepcion");
        $periodoId = $this->getParametro("periodoId");
        $origen_destino = $this->getParametro("origen_destino");
        $importeTotalInafectas = $this->getParametro("importeTotalInafectas");
        $datosExtras = $this->getParametro("datosExtras");
        $detalleDistribucion = $this->getParametro("detalleDistribucion");
        $distribucionObligatoria = $this->getParametro("distribucionObligatoria");
        $dataStockReservaOk = $this->getParametro("dataStockOk");
        $dataPostorProveedor = $this->getParametro("dataPostorProveedor");
        $listaPagoProgramacionPostores = $this->getParametro("listaPagoProgramacionPostores");

        $respuestaGuardar = MovimientoNegocio::create()->validarGenerarDocumentoAdicional($opcionId, $usuarioId, $documentoTipoId, $camposDinamicos, $detalle, $documentoARelacionar, $valorCheck, $comentario, $checkIgv, $monedaId, $accionEnvio, $tipoPago, $listaPagoProgramacion, $anticiposAAplicar, $periodoId, $percepcion, $origen_destino, $importeTotalInafectas, $datosExtras, $detalleDistribucion, $contOperacionTipoId, $distribucionObligatoria, $igv_porcentaje, $dataStockReservaOk, $dataPostorProveedor, $listaPagoProgramacionPostores);

        return $respuestaGuardar;
    }

    public function visualizarDetalleEntrega()
    {
        $id = $this->getParametro("id");
        $movimientoId = $this->getParametro("movimientoId");
        return AlmacenesNegocio::create()->visualizarDetalleEntrega($id, $movimientoId);
    }

    public function obtenerUnidadMedida()
    {
        //        $indice = $this->getParametro("indice");
        $bienId = $this->getParametro("bienId");
        $unidadMedidaId = $this->getParametro("unidadMedidaId");
        $precioTipoId = $this->getParametro("precioTipoId");
        $monedaId = $this->getParametro("monedaId");
        $fechaEmision = $this->getParametro("fechaEmision");
        //        $opcionId = $this->getOpcionId();

        return MovimientoNegocio::create()->obtenerUnidadMedida($bienId, $unidadMedidaId, $precioTipoId, $monedaId, $fechaEmision);
    }

    public function generarSalidaSolicitud(){
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $dataStockOk = $this->getParametro("dataStockOk");
        return AlmacenesNegocio::create()->generarSalidaSolicitud($dataStockOk, $usuarioId);
    }
    //MinaApp
    public function obtener_datosOrganizador()
    {
        $organizadorId = $this->getParametro("organizadorId");
        return AlmacenesNegocio::create()->obtener_datosOrganizador($organizadorId);
    }

    public function obtener_datosPaquete()
    {
        $paqueteId = $this->getParametro("paqueteId");
        return AlmacenesNegocio::create()->obtener_datosPaquete($paqueteId);
    }

    public function alamacenarPaquete()
    {
        $usuarioId = $this->getUsuarioId();
        $organizadorId = $this->getParametro("organizadorId");
        $paqueteDetalleId = $this->getParametro("paqueteDetalleId");
        return AlmacenesNegocio::create()->almacenarPaquete($organizadorId, $paqueteDetalleId, $usuarioId);
    }
}
