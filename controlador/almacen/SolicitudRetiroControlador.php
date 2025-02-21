<?php

require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/SolicitudRetiroNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/EmpresaNegocio.php';
require_once __DIR__ . '/../../modelo/almacen/Persona.php';
require_once __DIR__ . '/../../util/Configuraciones.php';
require_once __DIR__ . '/../../util/ImportacionExcel.php';

class SolicitudRetiroControlador extends AlmacenIndexControlador {

    //funciones sobre la tabla persona clase
    public function getDataGridPersonaClase() {

        return PersonaNegocio::create()->getAllPersonaClase();
    }

    public function insertPersonaClase() {
        $descripcion = $this->getParametro("descripcion");
        $tipo = $this->getParametro("tipo");
        $estado = $this->getParametro("estado");
        $usuarioCreacion = $this->getUsuarioId();
        return PersonaNegocio::create()->insertPersonaClase($descripcion, $tipo, $estado, $usuarioCreacion);
    }

    public function ExportarPersonaExcel() {
        $usuarioCreacion = $this->getUsuarioId();
        return PersonaNegocio::create()->ExportarPersonaExcel($usuarioCreacion);
    }

    public function updatePersonaClase() {
        $id = $this->getParametro("id");
        $descripcion = $this->getParametro("descripcion");
        $tipo = $this->getParametro("tipo");
        $estado = $this->getParametro("estado");
        return PersonaNegocio::create()->updatePersonaClase($id, $descripcion, $tipo, $estado);
    }

    public function deletePersonaClase() {
        $id = $this->getParametro("id");
        return PersonaNegocio::create()->cambiarEstadoPersonaClase($id, $estado = 2);
    }

    public function cambiarEstadoPersonaClase() {
        $id = $this->getParametro("id");
        return PersonaNegocio::create()->cambiarEstadoPersonaClase($id);
    }

    //funciones sobre la tabla persona
//    
//    public function getDataGridPersona() {
//        return PersonaNegocio::create()->getAllPersona();
//    }

        public function obtenerSolicitudesUsuario() {
            $usuarioId = $this->getUsuarioId();
            return SolicitudRetiroNegocio::create()->obtenerSolicitudesUsuario($usuarioId);
        }

        public function obtenerSolicitudesPesajesUsuario() {
            $usuarioId = $this->getUsuarioId();
            return SolicitudRetiroNegocio::create()->obtenerSolicitudesPesajesUsuario($usuarioId);
        }

        public function obtenerPesajeXSolicitud() {
            $usuarioId = $this->getUsuarioId();
            $solicitudId = $this->getParametro("solicitud_id");
            return SolicitudRetiroNegocio::create()->obtenerPesajeXSolicitud($usuarioId,$solicitudId);
        }

        public function registrarConformidadPesaje() {
            $usuarioId = $this->getUsuarioId();
            $solicitudId = $this->getParametro("solicitud_id");
            return SolicitudRetiroNegocio::create()->registrarConformidadPesaje($usuarioId,$solicitudId);
        }

        public function registrarrechazarPesaje() {
            $usuarioId = $this->getUsuarioId();
            $solicitudId = $this->getParametro("solicitud_id");
            return SolicitudRetiroNegocio::create()->registrarrechazarPesaje($usuarioId,$solicitudId);
        }

        public function obtenerValidacionTransportista() {
            $usuarioId = $this->getUsuarioId();
            $transportista = $this->getParametro("transportista_id");
            return SolicitudRetiroNegocio::create()->obtenerValidacionTransportista($usuarioId,$transportista);
        }

        public function obtenerValidacionVehiculo() {
            $usuarioId = $this->getUsuarioId();
            $vehiculo = $this->getParametro("vehiculo_id");
            return SolicitudRetiroNegocio::create()->obtenerValidacionVehiculo($usuarioId,$vehiculo);
        }

        public function obtenerValidacionConductor() {
            $usuarioId = $this->getUsuarioId();
            $conductor = $this->getParametro("conductor_id");
            return SolicitudRetiroNegocio::create()->obtenerValidacionConductor($usuarioId,$conductor);
        }

        public function listarSolicitudes() {
            $usuarioId = $this->getUsuarioId();
            return SolicitudRetiroNegocio::create()->listarSolicitudes($usuarioId );
        }
        
        public function obtenerTipoDocumento23(){
            return PersonaNegocio::create()->obtenerTipoDocumento();
        }

        public function obtenerArchivos(){
            $usuarioId = $this->getUsuarioId();
            $solicitudId = $this->getParametro("solicitudId");
            return SolicitudRetiroNegocio::create()->obtenerArchivos($usuarioId,$solicitudId);
    
        }

        public function obtenerConfiguracionesSolicitud() {
            $solicitudId = $this->getParametro("solicitudId");
            $usuarioId = $this->getUsuarioId();
            return SolicitudRetiroNegocio::create()->obtenerConfiguracionesPersona($solicitudId, $usuarioId);
        }


    public function getDataGridSolicitudes() {
        $idPersona = $this->getParametro("id");
        $planta = $this->getParametro("planta");
        $zona = $this->getParametro("zona");
        $vehiculo = $this->getParametro("vehiculo");
        $transportista = $this->getParametro("transportista");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = SolicitudRetiroNegocio::create()->getAllSolicitudes($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$planta,$zona,$vehiculo,$transportista);
        $response_cantidad_total = SolicitudRetiroNegocio::create()->getCantidadAllSolicitudes($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$planta,$zona,$vehiculo,$transportista);
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }

    public function getDataGridSolicitudesAprobacion() {
        $idPersona = $this->getParametro("id");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = SolicitudRetiroNegocio::create()->getAllSolicitudesAprobacion($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona);
        $response_cantidad_total = SolicitudRetiroNegocio::create()->getCantidadAllSolicitudesAprobacion($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona);
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }
    
    
    public function insertAprobacionSolicitud() {
        $idSolicitud = $this->getParametro("id");
        $usuarioId = $this->getUsuarioId();
        return SolicitudRetiroNegocio::create()->insertAprobacionSolicitud($idSolicitud,$usuarioId);
    }

    public function insertDesaprobacionSolicitud() {
        $idSolicitud = $this->getParametro("solicitud_id");
        $motivo = $this->getParametro("motivo");
        $usuarioId = $this->getUsuarioId();
        return SolicitudRetiroNegocio::create()->insertDesaprobacionSolicitud($idSolicitud,$motivo,$usuarioId);
    }

    public function insertSolicitud() {
        $this->setTransaction();
        $fechaEntrega = $this->getParametro("fechaEntrega");
        $capacidad = $this->getParametro("capacidad");
        $constancia = $this->getParametro("constancia");
        $transportista = $this->getParametro("transportista");
        $conductor = $this->getParametro("conductor");
        $vehiculo = $this->getParametro("vehiculo");
        $zona = $this->getParametro("zona");
        $planta = $this->getParametro("planta");
        $reinfo = $this->getParametro("reinfo");
        $lotes = $this->getParametro("lotes");
        $usuarioId = $this->getUsuarioId();
 
        return SolicitudRetiroNegocio::create()->insertSolicitud($fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId,$reinfo,$lotes );
    }

    public function registrarSolicitudRetiro() {
        $this->setTransaction();
        $fechaEntrega = $this->getParametro("selectedDate");
        $fechaLlegada = $this->getParametro("selectedSecondDate");
         $capacidad = '15200';
         $constancia = 'ABCDE';
        $transportista = $this->getParametro("value1");
        $vehiculo = $this->getParametro("value2");
        $conductor = $this->getParametro("value3");
        $zona = $this->getParametro("value5");
        $planta = $this->getParametro("value4");
        $lotes = $this->getParametro("value6");
        // $reinfo = $this->getParametro("reinfo");
        $usuarioId = $this->getUsuarioId();
 
        return SolicitudRetiroNegocio::create()->insertSolicitudMovil($fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId,$fechaLlegada,$lotes );
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
        $reinfo = $this->getParametro("reinfo");
        $lotes = $this->getParametro("lotes");
        $usuarioId = $this->getUsuarioId();
        return SolicitudRetiroNegocio::create()->updateSolicitud($id,$fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId,$reinfo,$lotes);
    }

    public function obtenerPersona() {
        $id = $this->getParametro("id");
        return PersonaNegocio::create()->obtenerPersonaXId($id);
    }

    public function deleteSolicitud() {
        $id = $this->getParametro("id");
        $usuarioSesion = $this->getUsuarioId();
        return SolicitudRetiroNegocio::create()->cambiarEstadoSolicitud($id, $usuarioSesion, $estado = 2);
    }



    public function cambiarEstadoPersona() {
        $id = $this->getParametro("id");
        $usuarioSesion = $this->getUsuarioId();
        return PersonaNegocio::create()->cambiarEstadoPersona($id, $usuarioSesion);
    }

    public function obtenerConfiguracionesPersona() {
        $personaTipoId = $this->getParametro("personaTipoId");
        $personaId = $this->getParametro("personaId");
        $usuarioId = $this->getUsuarioId();
        $empresaId = $this->getParametro("empresaId");
        return PersonaNegocio::create()->obtenerConfiguracionesPersona($personaId, $personaTipoId, $usuarioId, $empresaId);
    }

    public function obtenerConfiguracionesSolicitudRetiro() {
        $usuarioId = $this->getUsuarioId();
        $solicitudId = $this->getParametro("solicitudId");
        return SolicitudRetiroNegocio::create()->obtenerConfiguracionesSolicitudRetiro( $usuarioId,$solicitudId); 
    }

    public function obtenerConfiguracionesListar() {
        return PersonaNegocio::create()->obtenerConfiguracionesListar();
    }

    //otras funciones de sus tablas pivote

    public function getAllPersonaClaseByTipo() {
        $idTipo = $this->getParametro("id_tipo");
        return PersonaNegocio::create()->getAllPersonaClaseByTipo($idTipo);
    }

    public function getAllPersonaTipo() {
        return PersonaNegocio::create()->getAllPersonaTipo();
    }

    public function getAllPersonaTipoCombo() {
        return PersonaNegocio::create()->getAllPersonaTipo();
    }

    public function obtenerPersonaClaseActivas() {
        return PersonaNegocio::create()->obtenerPersonaClaseActivas();
    }

    //funciones de otros controladores
    public function getAllEmpresa() {
        $usuarioId = $this->getUsuarioId();
        //return EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
        return EmpresaNegocio::create()->getEmpresaActivas();
    }

    //nuevas configuraciones 

    public function configuracionesInicialesPersonaListar() {
        $usuarioId = $this->getUsuarioId();
        return PersonaNegocio::create()->configuracionesInicialesPersonaListar($usuarioId);
    }

    public function obtenerConsultaRUC() {
        $codigoIdentificacion = $this->getParametro("codigoIdentificacion");
        return PersonaNegocio::create()->getDatosProveedor($codigoIdentificacion);
    }

    public function importPersona() {
        $this->setTransaction();
        $file = $this->getParametro("file");
        $usuarioCreacion = $this->getUsuarioId();
        $empresaId = $this->getParametro("empresa_id");

        $decode = Util::base64ToImage($file);

        $direccion = __DIR__ . '/../../util/formatos/persona_subida.xls';
        if (file_exists($direccion)) {
            unlink($direccion);
        }
        file_put_contents($direccion, $decode);


        if (strlen($file) < 1) {
            throw new WarningException("No se ha seleccionado ningun archivo.");
        }
        $parse = ImportacionExcel::parsePersonaExcelToXML("formatos/persona_subida.xls");
        if (array_key_exists("xml", $parse)) {
            $data = $parse["data"];
            $result = PersonaNegocio::create()->importaPersonaXML($parse["xml"], $usuarioCreacion, $empresaId);
            if (strlen($result[0]["errores"]) == 0 || $result[0]["count"] == 0) {
                return "Se importaron correctamente todas las filas";
            } else {
                $bien = "Se detectaron " . $result[0]["count"] . " filas con errores";
                $errores = $bien . "<br><br>No fue posible importar una o varias filas:<br>";
                $json = $result[0]["errores"];
                $json = str_replace("IDENT_INIT,", "", $json);
                $err = json_decode($json, true);
                $excel = ImportacionExcel::getExcelwithErrors($err, "formato_persona", $data);
                if (strlen($excel) > 0) {
                    $errores .= "<br><p><a href='util/$excel'>"
                            . "<div class='alert alert-danger' style='cursor : pointer; text-align:center;'>Descarge el documento de errores con el detalle aquí</div>"
                            . "</a></p>";
                }
                return $errores;
            }
        } else {
            if ($errores !== "") {
                $errores = $bien . "<br><br>No fue posible importar una o varias filas:<br>";
                $excel = ImportacionExcel::getExcelwithErrors($result, "formato_persona");
                if (strlen($excel) > 0) {
                    $errores .= "<br><p><a href='util/$excel'>"
                            . "<div class='alert alert-danger' style='cursor : pointer; text-align:center;'>Descarge el documento de errores con el detalle aquí</div>"
                            . "</a></p>";
                }
                return $errores;
            }
        }
    }

    public function buscarCriteriosBusquedaSolicitud() {
        $busqueda = $this->getParametro("busqueda");
        $usuarioId = $this->getUsuarioId();

        $dataPersona = SolicitudRetiroNegocio::create()->buscarSolicitudXSociedadXVehiculo($busqueda, $usuarioId);
        $resultado->dataPersona = $dataPersona;

        return $resultado;
    }

    public function obtenerPersonasNaturales() {
        return PersonaNegocio::create()->obtenerPersonasXPersonaTipo(2); // 2-> natural
    }

    public function obtenerPersonaClaseAsociada() {
        $personaTipoId = $this->getParametro("personaTipoId");
        $usuarioId = $this->getUsuarioId();
        return PersonaNegocio::create()->obtenerPersonaClaseXpersonaTipoIdXUsuarioId($personaTipoId, $usuarioId);
    }

    public function obtenerDataConvenioSunat() {
        $codigoSunatId = $this->getParametro("codigoSunatId");
        return PersonaNegocio::create()->obtenerSunatTablaDetalleRelacionXTipoXSunatTablaDetalleId(1, $codigoSunatId);
    }

    public function validarSimilitud() {
        $nombre = $this->getParametro("nombre");
        $id = $this->getParametro("personaId");
        //$apellidoMaterno = $this->getParametro("apellidoMaterno");
        $apellidoPaterno = $this->getParametro("apellidoPaterno");

        return PersonaNegocio::create()->validarSimilitud($id, $nombre, $apellidoPaterno);
    }

    public function listarSolicitudesDocumentario() {
        $usuarioId = $this->getUsuarioId();
        return SolicitudRetiroNegocio::create()->listarSolicitudesDocumentario($usuarioId );
    }

    public function listarSolicitudesPorAprobacionPesaje() {
        $usuarioId = $this->getUsuarioId();
        return SolicitudRetiroNegocio::create()->listarSolicitudesPorAprobacionPesaje($usuarioId );
    }

    public function subirArchivo() {
        $id = $this->getParametro("id");
        $file = $this->getParametro("file");
        $tipo = $this->getParametro("tipo");
        $usuarioId = $this->getUsuarioId();
        return SolicitudRetiroNegocio::create()->subirArchivo($id,$file,$tipo);
    }
    

    public function eliminarArchivo() {
        $id = $this->getParametro("id");
        $archivo = $this->getParametro("archivo");
        $tipo = $this->getParametro("tipo");
        $usuarioId = $this->getUsuarioId();
        return SolicitudRetiroNegocio::create()->eliminarArchivo($id,$archivo,$tipo);
    }

    public function obtenerSolicitudesPendienteResultados() {
        $usuarioId = $this->getUsuarioId();
        return SolicitudRetiroNegocio::create()->obtenerSolicitudesPendienteResultados($usuarioId);
    }

    public function obtenerResultadoXSolicitud() {
        $usuarioId = $this->getUsuarioId();
        $solicitudId = $this->getParametro("solicitud_id");
        return SolicitudRetiroNegocio::create()->obtenerResultadoXSolicitud($usuarioId,$solicitudId);
    }

    public function obtenerResultadoXSolicitudDemo() {
        $usuarioId = $this->getUsuarioId();
        $solicitudId = $this->getParametro("solicitud_id");
        return SolicitudRetiroNegocio::create()->obtenerResultadoXSolicitudDemo($usuarioId,$solicitudId);
    }

    public function dirimenciaResultados(){

        $usuarioId = $this->getUsuarioId();
        $loteId = $this->getParametro("lote_id");
        $ley_antigua = $this->getParametro("ley_antigua");
        $ley = $this->getParametro("ley");
        $archivo = $this->getParametro("archivo_pdf");
        return SolicitudRetiroNegocio::create()->dirimenciaResultados($usuarioId,$loteId,$ley_antigua,$ley,$archivo);

    }

    public function negociarResultados(){
        $usuarioId = $this->getUsuarioId();
        $loteId = $this->getParametro("lote_id");
        $ley_antigua = $this->getParametro("ley_antigua");
        $ley = $this->getParametro("ley");
        $archivo = $this->getParametro("archivo_pdf");
        return SolicitudRetiroNegocio::create()->negociarResultados($usuarioId,$loteId,$ley_antigua,$ley,$archivo);
    }

    public function registrarAprobacionResultados(){

        $usuarioId = $this->getUsuarioId();
        $loteId = $this->getParametro("lote_id");
        return SolicitudRetiroNegocio::create()->registrarAprobacionResultados($usuarioId,$loteId);

    }
     

    public function obtenerConfiguracionesFiltros(){
        $usuarioId = $this->getUsuarioId();
        return SolicitudRetiroNegocio::create()->obtenerConfiguracionesFiltros($usuarioId);
  
    }

    public function consultarTrabajador(){
        $usuarioId = $this->getUsuarioId();
        $codigo = $this->getParametro("codigo_barras");
        
        return Persona::create()->obtenerPersonaXCodigoIdentificacion($codigo);
  
    }

}
