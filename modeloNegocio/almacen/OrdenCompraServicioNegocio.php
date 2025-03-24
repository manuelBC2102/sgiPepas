<?php

require_once __DIR__ . '/../../modelo/almacen/OrdenCompraServicio.php';
require_once __DIR__ . '/../../modelo/almacen/MovimientoBien.php';
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

class OrdenCompraServicioNegocio extends ModeloNegocioBase
{
    /**
     *
     * @return OrdenCompraServicioNegocio
     */
    static function create()
    {
        return parent::create();
    }

    public function obtenerConfiguracionInicialListadoDocumentos()
    {
        $respuesta = new stdClass();

        $responseMovimientoTipo = Movimiento::create()->ObtenerMovimientoTipoPorOpcion();
        return $respuesta;
    }

    public function obtenerOrdenCompraServicioXCriterios($criterios, $elementosFiltrados, $columns, $order, $start)
    {
        $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
        $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
        $estadoId = $criterios['estadoId'];
        $tipoId = $criterios['tipoId'];

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return OrdenCompraServicio::create()->obtenerOrdenCompraServicioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
    }

    public function obtenerCantidadOrdenCompraServicioXCriterios($criterios, $columns, $order)
    {
        $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
        $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);
        $estadoId = $criterios['estadoId'];
        $tipoId = $criterios['tipoId'];

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];


        return OrdenCompraServicio::create()->obtenerCantidadOrdenCompraServicioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, $columnaOrdenar, $formaOrdenar);
    }

    private function formatearFechaBD($cadena)
    {
        if (!ObjectUtil::isEmpty($cadena)) {
            return DateUtil::formatearCadenaACadenaBD($cadena);
        }
        return "";
    }

    public function visualizarOrdenCompraServicio($id, $movimientoId){
        $respuesta = new stdClass();
        $detalle =  MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);
        $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($id);

        foreach($detalle as $index => $item){
            $detalle[$index]["subTotal_precio_postor1"] = $item["cantidad"] * $item["precio_postor1"];
            $detalle[$index]["subTotal_precio_postor2"] = $item["cantidad"] * $item["precio_postor2"];
            $detalle[$index]["subTotal_precio_postor3"] = $item["cantidad"] * $item["precio_postor3"];
        }

        $respuesta->detalle = $detalle;

        return $respuesta;
    }

    public function cargarArchivosAdjuntos($documentoId, $lstDocumentoArchivos, $lstDocEliminado,$usuarioId){
        $documento = Documento::create()->obtenerDocumentoDatos($documentoId);

        foreach($lstDocumentoArchivos as $index => $item){
            if($item['tipo_archivoId'] == 4){
                if (strpos($item['id'], 't') == 0) {
                        $decode = Util::base64ToImage($item['data']);
                        $xml = simplexml_load_string($decode);
                        if ($xml === false) {
                            throw new WarningException("Error al leer archivo XML");
                        }else{
                            // Buscar el RUC del emisor (Proveedor) en el XML
                            $ruc_emisor = (string) $xml->children('cac', true)->AccountingSupplierParty->children('cac', true)->Party->children('cac', true)->PartyIdentification->children('cbc', true)->ID;

                            //validar ruc
                            $mensaje = '';
                            if(trim($ruc_emisor) != trim($documento[0]['persona_ruc'])){
                                $mensaje = 'El ruc no coicide con la orden de compra';
                            }
                            //falta validar monto total con la Orden de compra
                        }

                        if(!ObjectUtil::isEmpty($mensaje)){
                            throw new WarningException($mensaje);
                        }

                }else{
                    $xml = simplexml_load_file(__DIR__ . '/../../'.$item['data']);
                    if ($xml === false) {
                        throw new WarningException("Error al leer archivo XML");
                    }else{
                        $ruc_emisor = (string)$xml->xpath("//cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID")[0];
                            //validar ruc
                        $mensaje = '';
                        if(trim($ruc_emisor) != trim($documento[0]['persona_ruc'])){
                            $mensaje = 'El ruc no coicide con la orden de compra';
                        }

                        //falta validar monto total con la Orden de compra

                        if(!ObjectUtil::isEmpty($mensaje)){
                            throw new WarningException($mensaje);
                        }
                    }
                }
            }

        }
        
        $resAdjunto = MovimientoNegocio::create()->guardarArchivosXDocumentoID($documentoId, $lstDocumentoArchivos, $lstDocEliminado, $usuarioId);

        if ($resAdjunto[0]['vout_exito'] != 1) {
            throw new WarningException($resAdjunto[0]['vout_mensaje']);
        }
        
        $respuesta =  new stdClass();
        $respuesta->mensaje = $resAdjunto[0]['vout_mensaje'];
        $respuesta->data = DocumentoNegocio::create()->obtenerDocumentoAdjuntoXDocumentoId($documentoId);
        return  $respuesta;
    }
 
}
