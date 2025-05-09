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

        return OrdenCompraServicio::create()->obtenerOrdenCompraServicioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, null, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
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


        return OrdenCompraServicio::create()->obtenerCantidadOrdenCompraServicioXCriterios($fechaEmisionInicio, $fechaEmisionFin, $estadoId, $tipoId, null, $columnaOrdenar, $formaOrdenar);
    }

    private function formatearFechaBD($cadena)
    {
        if (!ObjectUtil::isEmpty($cadena)) {
            return DateUtil::formatearCadenaACadenaBD($cadena);
        }
        return "";
    }

    public function visualizarOrdenCompraServicio($id, $movimientoId)
    {
        $respuesta = new stdClass();
        $detalle =  MovimientoBien::create()->obtenerXIdMovimiento($movimientoId);
        $respuesta->dataDocumento = DocumentoNegocio::create()->obtenerDetalleDocumento($id);

        $respuesta->detalle = $detalle;
        return $respuesta;
    }

    public function cargarArchivosAdjuntos($documentoId, $lstDocumentoArchivos, $lstDocEliminado, $usuarioId)
    {
        $documento = Documento::create()->obtenerDocumentoDatos($documentoId);
        $documentoAdjuntos = DocumentoNegocio::create()->obtenerDocumentoAdjuntoXDocumentoId($documentoId);

        $sumaMontos  = array_reduce($documentoAdjuntos, function ($acumulador, $seleccion) {
            if($seleccion['estado'] == 2 || $seleccion['estado'] == 1){
                return $acumulador + $seleccion['contenido_archivo'];
            }
          }, 0);

        foreach ($lstDocumentoArchivos as $index => $item) {
            if ($item['tipo_archivoId'] == 4) {
                if (strpos($item['id'], 't') !== false) {
                    $decode = Util::base64ToImage($item['data']);
                    $xml = simplexml_load_string($decode);
                    if ($xml === false) {
                        throw new WarningException("Error al leer archivo XML");
                    } else {
                        // Buscar el RUC del emisor (Proveedor) en el XML
                        $ruc_emisor = (string) $xml->children('cac', true)->AccountingSupplierParty->children('cac', true)->Party->children('cac', true)->PartyIdentification->children('cbc', true)->ID;
                        //validar ruc
                        $mensaje = '';
                        if (trim($ruc_emisor) != trim($documento[0]['persona_ruc'])) {
                            $mensaje = 'El ruc no coicide con la orden de compra';
                        }
                        //falta validar monto total con la Orden de compra
                        $montoTotalFactura = (string) $xml->children('cac', true)->TaxTotal->children('cbc', true)->TaxAmount;

                        $montoTotal = $sumaMontos + $montoTotalFactura;

                        $total = $documento[0]["total"];
                        if($total <= $montoTotal){
                            $mensaje = 'La suma de las facturas no coicide con la orden de compra';
                        }

                        $lstDocumentoArchivos[$index]["contenido_archivo"] = $montoTotalFactura;
                    }

                    if (!ObjectUtil::isEmpty($mensaje)) {
                        throw new WarningException($mensaje);
                    }
                }
                // }else{
                //     $xml = simplexml_load_file(__DIR__ . '/../../'.$item['data']);
                //     if ($xml === false) {
                //         throw new WarningException("Error al leer archivo XML");
                //     }else{
                //         $ruc_emisor = (string)$xml->xpath("//cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID")[0];
                //             //validar ruc
                //         $mensaje = '';
                //         if(trim($ruc_emisor) != trim($documento[0]['persona_ruc'])){
                //             $mensaje = 'El ruc no coicide con la orden de compra';
                //         }

                //         //falta validar monto total con la Orden de compra

                //         if(!ObjectUtil::isEmpty($mensaje)){
                //             throw new WarningException($mensaje);
                //         }
                //     }
                // }
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

    public function aprobarRechazar($documentoAdjuntoId, $accion, $razonRechazo, $usuarioId, $documentoId)
    {
        if ($accion == 'AP') {
            $res = OrdenCompraServicio::create()->aprobarRechazarDocumentoAdjunto($documentoAdjuntoId, 2, $razonRechazo);
        } elseif ($accion == 'RE') {
            $res = OrdenCompraServicio::create()->aprobarRechazarDocumentoAdjunto($documentoAdjuntoId, 3, $razonRechazo);
        }

        $respuesta =  new stdClass();
        $respuesta->data = DocumentoNegocio::create()->obtenerDocumentoAdjuntoXDocumentoId($documentoId);
        $respuesta->accion = $accion == "AP"? "aprobado":"rechazado";
        return  $respuesta;
    }

    // public function visualizarDistribucionPagos($documentoId){
    //     return OrdenCompraServicio::create()->obtenerDistribucionPagos($documentoId);
    // }

    // public function obtenerDocumentoAdjuntoXDistribucionPagos($distribucionPagoId){
    //     return OrdenCompraServicio::create()->obtenerDocumentoAdjuntoXDistribucionPagos($distribucionPagoId);
    // }

    // public function cargarArchivosAdjuntosDistribucionPagos($distribucionPagoId ,$documentoId, $lstDocumentoArchivos, $lstDocEliminado,$usuarioId){
    //     $respuesta =  new stdClass();

    //     $detalleDistribucionPagos = OrdenCompraServicio::create()->obtenerDistribucionPagos($documentoId, $distribucionPagoId);
    //     $documento = Documento::create()->obtenerDocumentoDatos($detalleDistribucionPagos[0]['documento_id']);

    //     foreach($lstDocumentoArchivos as $index => $item){
    //         if($item['tipo_archivoId'] == 4){
    //             if (strpos($item['id'], 't') == 0) {
    //                     $decode = Util::base64ToImage($item['data']);
    //                     $xml = simplexml_load_string($decode);
    //                     if ($xml === false) {
    //                         throw new WarningException("Error al leer archivo XML");
    //                     }else{
    //                         // Buscar el RUC del emisor (Proveedor) en el XML
    //                         $ruc_emisor = (string) $xml->children('cac', true)->AccountingSupplierParty->children('cac', true)->Party->children('cac', true)->PartyIdentification->children('cbc', true)->ID;
    //                         //validar ruc
    //                         $mensaje = '';
    //                         if(trim($ruc_emisor) != trim($documento[0]['persona_ruc'])){
    //                             $mensaje = 'El ruc no coicide con la orden de compra';
    //                         }
    //                         $montoTotal = (string) $xml->children('cac', true)->TaxTotal->children('cbc', true)->TaxAmount;

    //                         if($detalleDistribucionPagos[0]['importe'] != $montoTotal){
    //                             $salto = "";
    //                             if($mensaje != null){
    //                                 $salto = "<br>";
    //                             }
    //                             $mensaje .= $salto.'El importe de la factura no coicide con el monto de la distribución de pagos';
    //                         }
    //                         //falta validar monto total con la Orden de compra
    //                     }

    //                     if(!ObjectUtil::isEmpty($mensaje)){
    //                         throw new WarningException($mensaje);
    //                     }

    //             }else{
    //                 $xml = simplexml_load_file(__DIR__ . '/../../'.$item['data']);
    //                 if ($xml === false) {
    //                     throw new WarningException("Error al leer archivo XML");
    //                 }else{
    //                     $ruc_emisor = (string)$xml->xpath("//cac:AccountingSupplierParty/cac:Party/cac:PartyTaxScheme/cbc:CompanyID")[0];
    //                         //validar ruc
    //                     $mensaje = '';
    //                     if(trim($ruc_emisor) != trim($documento[0]['persona_ruc'])){
    //                         $mensaje = 'El ruc no coicide con la orden de compra';
    //                     }

    //                     //falta validar monto total con la Orden de compra

    //                     if(!ObjectUtil::isEmpty($mensaje)){
    //                         throw new WarningException($mensaje);
    //                     }
    //                 }
    //             }
    //         }

    //     }

    //     $resAdjunto = $this->guardarArchivosXDistribucionPagoID($distribucionPagoId, $lstDocumentoArchivos, $lstDocEliminado, $usuarioId);

    //     if ($resAdjunto[0]['vout_exito'] != 1) {
    //         throw new WarningException($resAdjunto[0]['vout_mensaje']);
    //     }

    //     $respuesta->mensaje = $resAdjunto[0]['vout_mensaje'];
    //     $respuesta->data = OrdenCompraServicio::create()->obtenerDocumentoAdjuntoXDistribucionPagos($distribucionPagoId);
    //     return  $respuesta;
    // }

    // function guardarArchivosXDistribucionPagoID($distribucionPagoId, $lstDocumento, $lstDocEliminado, $usuCreacion)
    // {
    //   if ($distribucionPagoId != null) {
    //     //Eliminando archivos
    //     foreach ($lstDocEliminado as $d) {
    //       //Dando de baja en documento_adjunto
    //       if (!strpos($d[0]['id'], 't')) {
    //         $resAdjunto = OrdenCompraServicio::create()->insertarActualizarDocumentoAdjunto($d[0]['id'], null, null, null, null, 0);
    //         if ($resAdjunto[0]['vout_exito'] != 1) {
    //           throw new WarningException($resAdjunto[0]['vout_mensaje']);
    //         }
    //       }
    //     }
    //     //Insertando documento_adjunto
    //     foreach ($lstDocumento as $d) {
    //       //Se valida que el ID contenga el prefijo temporal "t" para que se opere, si no lo encuentra ya estaría registrado

    //       if (strpos($d['id'], 't') !== false) {

    //         //DOCUMENTO ADJUNTO
    //         if (!ObjectUtil::isEmpty($d['data'])) {

    //           $decode = Util::base64ToImage($d['data']);
    //           $nombreArchivo = $d['archivo'];
    //           $pos = strripos($nombreArchivo, '.');
    //           $ext = substr($nombreArchivo, $pos);

    //           $hoy = date("YmdHis").substr((string)microtime(), 2, 3);;
    //           $nombreGenerado = $distribucionPagoId . $hoy . $usuCreacion . $ext;
    //           $url = __DIR__ . '/../../util/uploads/documentoAdjunto/' . $nombreGenerado;

    //           file_put_contents($url, $decode);
    //           $tipo_archivoId = $d["tipo_archivoId"];

    //           $contenido_archivo = $d["contenido_archivo"];
    //           $resAdjunto = OrdenCompraServicio::create()->insertarActualizarDocumentoAdjunto(null, $distribucionPagoId, $nombreArchivo, $nombreGenerado, $usuCreacion, null,$tipo_archivoId, $contenido_archivo);
    //           if ($resAdjunto[0]['vout_exito'] != 1) {
    //             throw new WarningException($resAdjunto[0]['vout_mensaje']);
    //           }
    //         }
    //       }
    //     }
    //   } else {
    //     throw new WarningException("No existe documento para relacionar con el archivo adjunto");
    //   }
    //   return $resAdjunto;
    // }
}
