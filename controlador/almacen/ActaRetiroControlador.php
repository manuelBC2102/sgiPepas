<?php

require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/SolicitudRetiroNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/ActaRetiroNegocio.php';
require_once __DIR__ . '/../../modelo/almacen/Vehiculo.php';
require_once __DIR__ . '/../../modelo/almacen/ActaRetiro.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/EmpresaNegocio.php';
require_once __DIR__ . '/../../util/Configuraciones.php';
require_once __DIR__ . '/../../util/ImportacionExcel.php';

class ActaRetiroControlador extends AlmacenIndexControlador {

    //funciones sobre la tabla persona clase


    public function getDataGridActaRetiro() {
        $idPersona = $this->getParametro("id");
        $fecha = $this->getParametro("fecha");
        $usuario = $this->getParametro("usuario");
        $vehiculo = $this->getParametro("vehiculo");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = ActaRetiroNegocio::create()->getAllActasRetiro($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$fecha,$usuario,$vehiculo);
        $response_cantidad_total = ActaRetiroNegocio::create()->getCantidadAllActasRetiro($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$fecha,$usuario,$vehiculo);
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }
    
    
    public function getDataGridActaRetiroPlanta() {
        $idPersona = $this->getParametro("id");
        $fecha = $this->getParametro("fecha");
        $usuario = $this->getParametro("usuario");
        $vehiculo = $this->getParametro("vehiculo");
        $solicitud = $this->getParametro("solicitud");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = ActaRetiroNegocio::create()->getAllActasRetiroPlanta($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$fecha,$usuario,$vehiculo,$solicitud);
        $response_cantidad_total = ActaRetiroNegocio::create()->getCantidadAllActasRetiroPlanta($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$fecha,$usuario,$vehiculo,$solicitud);
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }
    
    public function guardarActaRetiroTotal() {
        $this->setTransaction();
        $placa = $this->getParametro("placa");
        $file = $this->getParametro("file");
        $items = $this->getParametro("selectedItems");
        $comentario = $this->getParametro("comentario");
        $pesaje = $this->getParametro("pesaje");
        $zona = $this->getParametro("zona");
        $pesajeFinal = $this->getParametro("pesajeFinal");
        $fechaInicio = $this->getParametro("fechaInicio");
        $fechaFinal = $this->getParametro("fechaFinal");
        $carreta = $this->getParametro("carreta");
        $usuarioId = $this->getUsuarioId();
 
        return ActaRetiroNegocio::create()->guardarActaRetiroTotal($placa,$file,
        $items,$comentario,$usuarioId ,$pesaje,$zona,$pesajeFinal,$fechaInicio,$fechaFinal,$carreta );
    }

    public function guardarActaRetiro() {
        $this->setTransaction();
        $placa = $this->getParametro("placa");
        $file = $this->getParametro("file");
         $items = $this->getParametro("selectedItems");
        // $comentario = $this->getParametro("comentario");
        $pesaje = $this->getParametro("pesaje");
        $zona = $this->getParametro("zona");
        $pesajeFinal = $this->getParametro("pesajeFinal");
         $fechaInicio = $this->getParametro("fechaInicio");
        $fechaFinal = $this->getParametro("fechaFinal");
        $carreta = $this->getParametro("carreta");
        $acta = $this->getParametro("acta");
        
        $usuarioId = $this->getUsuarioId();
 
        return ActaRetiroNegocio::create()->guardarActaRetiro($placa,$file,
        $usuarioId ,$pesaje,$zona,$pesajeFinal,$fechaInicio,$fechaFinal,$carreta,$items,$acta );
    }

    public function guardarActaRetiroInicial() {
        $this->setTransaction();
        $placa = $this->getParametro("placa");
        $file = $this->getParametro("file");
        $items = $this->getParametro("selectedItems");
        $pesaje = $this->getParametro("pesaje");
        $zona = $this->getParametro("zona");
        $fechaInicio = $this->getParametro("fechaInicio");
        $carreta = $this->getParametro("carreta");
        $usuarioId = $this->getUsuarioId();
 
        return ActaRetiroNegocio::create()->guardarActaRetiroInicial($placa,$file,
        $items,$usuarioId ,$pesaje,$zona,$fechaInicio,$carreta );
    }
  
    public function updateSolicitud() {
        $this->setTransaction();
        $id = $this->getParametro("id");
        $fechaEntrega = $this->getParametro("fechaEntrega");
        $capacidad = $this->getParametro("capacidad");
        $constancia = $this->getParametro("constancia");
        $transportista = $this->getParametro("transportista");
        $conductor = $this->getParametro("conductor");
        $vehiculo = $this->getParametro("vehiculo");
        $zona = $this->getParametro("zona");
        $planta = $this->getParametro("planta");
        $usuarioId = $this->getUsuarioId();
        return SolicitudRetiroNegocio::create()->updateSolicitud($id,$fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId);
    }


    public function deleteSolicitud() {
        $id = $this->getParametro("id");
        $usuarioSesion = $this->getUsuarioId();
        return ActaRetiroNegocio::create()->cambiarEstadoSolicitud($id, $usuarioSesion, $estado = 2);
    }

    public function obtenerPesajeSuminco() {
        $usuarioSesion = $this->getUsuarioId();
        $variable = $this->getParametro("variable");
        return ActaRetiroNegocio::create()->obtenerPesajeSuminco($variable);
    }


    public function obtenerConfiguracionesSolicitudRetiro() {
        $usuarioId = $this->getUsuarioId();
        $solicitudId = $this->getParametro("solicitudId");
        return SolicitudRetiroNegocio::create()->obtenerConfiguracionesSolicitudRetiro( $usuarioId,$solicitudId); 
    }


    public function configuracionesInicialesPersonaListar() {
        $usuarioId = $this->getUsuarioId();
        return PersonaNegocio::create()->configuracionesInicialesPersonaListar($usuarioId);
    }
    
    public function obtenerZonas(){
        $usuarioId = $this->getUsuarioId();
        return ActaRetiroNegocio::create()->obtenerZonas($usuarioId);
    }
    
    public function obtenerCarretas(){
        $usuarioId = $this->getUsuarioId();
        return Vehiculo::create()->obtenerCarretas($usuarioId);
    }
    public function obtenerDataPlaca() {
        $usuarioId = $this->getUsuarioId();
        $placa = $this->getParametro("placa");
        $zona = $this->getParametro("zona");
        return ActaRetiroNegocio::create()->obtenerDataPlaca($usuarioId,$placa,$zona);
    }

    public function obtenerDataActa() {
        $usuarioId = $this->getUsuarioId();
        $acta = $this->getParametro("acta");
        return ActaRetiroNegocio::create()->obtenerDataActa($usuarioId,$acta);
    }

    public function obtenerDataActaSolicitud() {
        $usuarioId = $this->getUsuarioId();
        $solicitud = $this->getParametro("solicitud_id");
        return ActaRetiroNegocio::create()->obtenerDataActaSolicitud($usuarioId,$solicitud);
    }
    public function guardarLotes() {
        $usuarioId = $this->getUsuarioId();
        $solicitudId = $this->getParametro("solicitudId");
        $ticket1 = $this->getParametro("ticket1");
        $ticket2 = $this->getParametro("ticket2");
        $peso_bruto = $this->getParametro("peso_bruto");
        $peso_tara = $this->getParametro("peso_tara");
        $peso_neto = $this->getParametro("peso_neto");
        $nombre_lote = $this->getParametro("nombre_lote");
        $archivo_lote = $this->getParametro("archivo_lote");
        return ActaRetiroNegocio::create()->guardarLotes($usuarioId,$solicitudId,$ticket1,$ticket2,
        $peso_bruto,$peso_tara,$peso_neto,$nombre_lote,$archivo_lote);
    }

    public function obtenerLotes() {
        $usuarioId = $this->getUsuarioId();
        $id = $this->getParametro("id");
        return ActaRetiroNegocio::create()->obtenerLotes($usuarioId,$id);
    }

    public function eliminarLotes() {
        $usuarioId = $this->getUsuarioId();
        $id = $this->getParametro("id");
        $archivo = $this->getParametro("archivo");
        $solicitudId = $this->getParametro("solicitudId");
        return ActaRetiroNegocio::create()->eliminarLotes($usuarioId,$id,$archivo,$solicitudId);
    }


        public function guardarPesajesActaRetiro() {
        $this->setTransaction();
        $items = $this->getParametro("selectedItems");
        $actaId = $this->getParametro("acta_id");
        $usuarioId = $this->getUsuarioId();
 
        return ActaRetiroNegocio::create()->guardarPesajesActaRetiro($items,$usuarioId,$actaId);
    }

    public function guardarPesajeSolicitudRetiro() {
        $this->setTransaction();
        $solicitud = $this->getParametro("solicitud_id");
        $usuarioId = $this->getUsuarioId();
 
        return ActaRetiroNegocio::create()->guardarPesajeSolicitudRetiro($usuarioId,$solicitud);
    }

    public function obtenerSolicitudesRetiroEntregaResultados() {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        return ActaRetiroNegocio::create()->obtenerSolicitudesRetiroEntregaResultados($usuarioId);
    }

    public function registrarResultadosLote() {
        $this->setTransaction();
        $data = $this->getParametro("data");
        $usuarioId = $this->getUsuarioId();
        return ActaRetiroNegocio::create()->registrarResultadosLote($data,$usuarioId);
    }
    
    public function obtenerLotesDirimencia() {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        return ActaRetiroNegocio::create()->obtenerLotesDirimencia($usuarioId);
    }

    public function obtenerLotesNegociar() {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        return ActaRetiroNegocio::create()->obtenerLotesNegociar($usuarioId);
    }
    
    public function obtenerLotesConfirmados() {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        return ActaRetiroNegocio::create()->obtenerLotesConfirmados($usuarioId);
    }
    public function guardarActualizacionDirimencia() {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $id = $this->getParametro("id");
        $file = $this->getParametro("file");
        $ley = $this->getParametro("ley");
        $monto = $this->getParametro("monto");
        $lote = $this->getParametro("lote");
        return ActaRetiroNegocio::create()->guardarActualizacionDirimencia($id,$file,$ley,$lote,$usuarioId,$monto);
    }

    public function guardarActualizacionNegociar() {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $id = $this->getParametro("id");
        $file = $this->getParametro("file");
        $ley = $this->getParametro("ley");
        $monto = $this->getParametro("monto");
        $lote = $this->getParametro("lote");
        return ActaRetiroNegocio::create()->guardarActualizacionNegociar($id,$file,$ley,$lote,$usuarioId,$monto);
    }
    
    public function obtenerConfiguracionesFiltros(){
        $usuarioId = $this->getUsuarioId();
        return ActaRetiroNegocio::create()->obtenerConfiguracionesFiltros($usuarioId);
  
    }

    public function obtenerToken(){
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $respuesta = new ObjectUtil();
        $efact=$this->getParametro("efact");
        $token= SolicitudRetiroNegocio::create()->generarTokenEfact('20600739256','e24243d460d9d29bddcafdef34c7f4cf853e719d5e217984d2149150d52397e2');
        $token = $token->access_token;
        $respuesta->efact=$efact;
        $respuesta->token=$token;
        return $respuesta;
        
    }

    public function obtenerTokenProduccion(){
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $respuesta = new ObjectUtil();
        $efact=$this->getParametro("efact");
        $tipo=$this->getParametro("tipo");
        $token= SolicitudRetiroNegocio::create()->generarTokenEfactProduccion('20600739256','6c8891e5029fdfb77edae3a0daf06ba99749b88a1c2c3956f12c712d288c920c');
        $token = $token->access_token;
        $respuesta->efact=$efact;
        $respuesta->token=$token;
        $respuesta->tipo=$tipo;
        return $respuesta;
        
    }

    
    public function guardarFactura() {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $serie = $this->getParametro("serie");
        $correlativo = $this->getParametro("correlativo");
        $subtotal = $this->getParametro("subtotal");
        $igv = $this->getParametro("igv");
        $totalFactura = $this->getParametro("totalFactura");
        $detraccion = $this->getParametro("detraccion");
        $netoPago = $this->getParametro("netoPago");
        $lotes = $this->getParametro("lotes");
        $minero = $this->getParametro("minero");

        return ActaRetiroNegocio::create()->guardarFactura($serie,$correlativo,$subtotal,$igv,$totalFactura,
        $detraccion,$netoPago,$lotes,$usuarioId,$minero);
    }

    public function getDataGridValorizacion() {
        $idPersona = $this->getParametro("id");
        $fecha = $this->getParametro("fecha");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = ActaRetiroNegocio::create()->getAllValorizacion($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$fecha);
        $response_cantidad_total = ActaRetiroNegocio::create()->getCantidadAllValorizacion($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$fecha);
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }
    

    public function obtenerValorizacionesGeneradas() {
        $this->setTransaction();
        $tipo = $this->getParametro("tipoPago");
        $usuarioId = $this->getUsuarioId();
        return ActaRetiroNegocio::create()->obtenerValorizacionesGeneradas($tipo,$usuarioId);
    }

    public function guardarPago() {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $tipo = $this->getParametro("tipo");
        $subtotal = $this->getParametro("subtotal");
        $fileBase64 = $this->getParametro("fileBase64");
        $fileExtension = $this->getParametro("fileExtension");
        $lotes = $this->getParametro("lotes");
        $minero = $this->getParametro("minero");
        $numeroOperacion = $this->getParametro("numeroOperacion");
        
       

        return ActaRetiroNegocio::create()->guardarPago($tipo,$subtotal,$fileBase64,$fileExtension,
        $lotes,$usuarioId,$minero,$numeroOperacion);
    }

    public function getDataGridPagoPlanta() {
        
        $fecha = $this->getParametro("fecha");
        $factura = $this->getParametro("factura");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = ActaRetiroNegocio::create()->getAllPagoPlanta($elemntosFiltrados, $columns, $order, $start,$usuarioId,$fecha,$factura);
        $response_cantidad_total = ActaRetiroNegocio::create()->getCantidadAllPagoPlanta($elemntosFiltrados, $columns, $order, $start,$usuarioId,$fecha,$factura);
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }

    public function obtenerConfiguracionesRetenciones() {
        $usuarioId = $this->getUsuarioId();
        $solicitudId = $this->getParametro("solicitudId");
        return ActaRetiroNegocio::create()->obtenerConfiguracionesRetenciones( $usuarioId,$solicitudId); 
    }

    public function obtenerDataProveedor() {
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $ruc = $this->getParametro("ruc");
        return ActaRetiroNegocio::create()->obtenerDataProveedor($usuarioId,$ruc);
    }
    
    public function insertRetencionFacturas(){

        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $tipoCambio = $this->getParametro("tipoCambio");
        $ruc = $this->getParametro("ruc");
        $razonSocial = $this->getParametro("razonSocial");
        $ubigeo = $this->getParametro("ubigeo");
        $departamento = $this->getParametro("departamento");
        $provincia = $this->getParametro("provincia");
        $distrito = $this->getParametro("distrito");
        $direccion = $this->getParametro("direccion");
        $factura = $this->getParametro("factura");
        $fechaFactura = $this->getParametro("fechaFactura");
        $montoFactura = $this->getParametro("montoFactura");
        $porcentajeRetencion = $this->getParametro("porcentajeRetencion");
        $fechaPago = $this->getParametro("fechaPago");
        $moneda = $this->getParametro("moneda");

    
        return ActaRetiroNegocio::create()->insertRetencionFacturas($usuarioId,$tipoCambio,$ruc,$razonSocial,$ubigeo,$departamento,
        $provincia,$distrito,$direccion,$factura ,$fechaFactura,$montoFactura,$porcentajeRetencion,$fechaPago,$moneda);

    }

   
     public function getDataGridRetenciones44(){
        $idPersona = $this->getParametro("id");
        $fecha = $this->getParametro("fecha");
        $factura = $this->getParametro("factura");
        $proveedor = $this->getParametro("proveedor");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = ActaRetiroNegocio::create()->getAllRetenciones($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$fecha,$factura,$proveedor);
        $response_cantidad_total = ActaRetiroNegocio::create()->getCantidadAllRetenciones($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$fecha,$factura,$proveedor);
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }

    public function obtenerConfiguracionesFiltrosRetencion(){
        $usuarioId = $this->getUsuarioId();
        return ActaRetiroNegocio::create()->obtenerConfiguracionesFiltrosRetencion($usuarioId);
  
    }

    public function obtenerActaRetiroXId(){
        $id = $this->getParametro("id");  
        $usuarioId = $this->getUsuarioId();
        return ActaRetiro::create()->obtenerActaRetiroXId($id);
    }

    public function ExportarPersonaExcel() {
        $usuarioCreacion = $this->getUsuarioId();
        return ActaRetiroNegocio::create()->ExportarRetenciones($usuarioCreacion);
    }
}
