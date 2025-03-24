<?php

require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/EmpresaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/CuentaNegocio.php';
require_once __DIR__ . '/../../util/Configuraciones.php';
require_once __DIR__ . '/../../util/ImportacionExcel.php';

class PersonaControlador extends AlmacenIndexControlador {

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


    public function getDataGridPersona() {
        $nombres = $this->getParametro("nombres");
        $codigo = $this->getParametro("codigo");
        $tipoPersona = $this->getParametro("tipoPersona");
        $clasePersona = $this->getParametro("clasePersona");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = PersonaNegocio::create()->getAllPersona($nombres, $codigo, $tipoPersona, $clasePersona, $elemntosFiltrados, $columns, $order, $start,$usuarioId);
        $response_cantidad_total = PersonaNegocio::create()->getCantidadAllPersona($nombres, $codigo, $tipoPersona, $clasePersona, $elemntosFiltrados, $columns, $order, $start,$usuarioId);
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }

    public function insertPersona() {
        $this->setTransaction();
        $PersonaTipoId = $this->getParametro("PersonaTipoId");
        $codigoIdentificacion = $this->getParametro("codigoIdentificacion");
        $nombre = $this->getParametro("nombre");
        $apellidoPaterno = $this->getParametro("apellido_paterno");
        $apellidoMaterno = $this->getParametro("apellido_materno");
        $telefono = $this->getParametro("telefono");
        $celular = $this->getParametro("celular");
        $email = $this->getParametro("email");
        $file = $this->getParametro("file");
        $estado = $this->getParametro("estado");
        $empresa = $this->getParametro("empresa");
        $clase = $this->getParametro("clase");
        $usuarioCreacion = $this->getUsuarioId();

        $listaContactoDetalle = $this->getParametro("listaContactoDetalle");
        $listaDireccionDetalle = $this->getParametro("listaDireccionDetalle");

        //tablas sunat
        $codigoSunatId = $this->getParametro("codigoSunatId");
        $codigoSunatId2 = $this->getParametro("codigoSunatId2");
        $codigoSunatId3 = $this->getParametro("codigoSunatId3");

        $nombreBCP = $this->getParametro("nombreBCP");
        $numero_cuenta_bcp = $this->getParametro("numero_cuenta_bcp");
        $cci = $this->getParametro("cci");
        $listaCentroCostoPersona = $this->getParametro("listaCentroCostoPersona");
        $planContableId = $this->getParametro("plan_contable_id");
        $licenciaAuto = $this->getParametro("licenciaAuto");
        $licenciaMoto = $this->getParametro("licenciaMoto");
        return PersonaNegocio::create()->insertPersona($PersonaTipoId, $codigoIdentificacion, $nombre, $apellidoPaterno, $apellidoMaterno, $telefono, $celular, $email, $file, $estado, $usuarioCreacion, $empresa, $clase, $listaContactoDetalle, $listaDireccionDetalle, $codigoSunatId, $codigoSunatId2, $codigoSunatId3, $nombreBCP, $numero_cuenta_bcp, $cci, $listaCentroCostoPersona, $planContableId, $licenciaAuto, $licenciaMoto);
    }

    public function obtenerPersona() {
        $id = $this->getParametro("id");
        return PersonaNegocio::create()->obtenerPersonaXId($id);
    }

    public function updatePersona() {
        $this->setTransaction();
        $id = $this->getParametro("id");
        $codigoIdentificacion = $this->getParametro("codigoIdentificacion");
        $nombre = $this->getParametro("nombre");
        $apellidoPaterno = $this->getParametro("apellido_paterno");
        $apellidoMaterno = $this->getParametro("apellido_materno");
        $telefono = $this->getParametro("telefono");
        $celular = $this->getParametro("celular");
        $email = $this->getParametro("email");
        $file = $this->getParametro("file");
        $estado = $this->getParametro("estado");
        $empresa = $this->getParametro("empresa");
        $clase = $this->getParametro("clase");
        $usuarioSesion = $this->getUsuarioId();

        $listaContactoDetalle = $this->getParametro("listaContactoDetalle");
        $listaPersonaContactoEliminado = $this->getParametro("listaPersonaContactoEliminado");
        $listaDireccionDetalle = $this->getParametro("listaDireccionDetalle");
        $listaPersonaDireccionEliminado = $this->getParametro("listaPersonaDireccionEliminado");

        //tablas sunat
        $codigoSunatId = $this->getParametro("codigoSunatId");
        $codigoSunatId2 = $this->getParametro("codigoSunatId2");
        $codigoSunatId3 = $this->getParametro("codigoSunatId3");

        $nombreBCP = $this->getParametro("nombreBCP");
        $numero_cuenta_bcp = $this->getParametro("numero_cuenta_bcp");
        $cci = $this->getParametro("cci");
        $listaCentroCostoPersona = $this->getParametro("listaCentroCostoPersona");
        $planContableId = $this->getParametro("plan_contable_id");
        $licenciaAuto = $this->getParametro("licenciaAuto");
        $licenciaMoto = $this->getParametro("licenciaMoto");
        return PersonaNegocio::create()->updatePersona($id, $codigoIdentificacion, $nombre, $apellidoPaterno, $apellidoMaterno, $telefono, $celular, $email, $file, $estado, $empresa, $clase, $usuarioSesion, $listaContactoDetalle, $listaPersonaContactoEliminado, $listaDireccionDetalle, $listaPersonaDireccionEliminado, $codigoSunatId, $codigoSunatId2, $codigoSunatId3, $nombreBCP, $numero_cuenta_bcp, $cci, $listaCentroCostoPersona,$planContableId, $licenciaAuto, $licenciaMoto);
    }

    public function deletePersona() {
        $id = $this->getParametro("id");
        $usuarioSesion = $this->getUsuarioId();
        return PersonaNegocio::create()->cambiarEstadoPersona($id, $usuarioSesion, $estado = 2);
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

    public function buscarCriteriosBusquedaPersona() {
        $busqueda = $this->getParametro("busqueda");
        $usuarioId = $this->getUsuarioId();

        $dataPersona = PersonaNegocio::create()->buscarPersonaListarXNombreXDocumento($busqueda, $usuarioId);
        $resultado->dataPersona = $dataPersona;

        $dataPersonaClase = PersonaNegocio::create()->buscarPersonaClaseXDescripcion($busqueda, $usuarioId);
        $resultado->dataPersonaClase = $dataPersonaClase;

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

     
    public function obtenerTipoDocumento23(){
        return PersonaNegocio::create()->obtenerTipoDocumento();
    }
    public function listarPersonaDocumentos() {
        $usuarioId = $this->getUsuarioId();
        return PersonaNegocio::create()->listarPersonaDocumentos($usuarioId );
    }
    
    public function insertArchivo() {
        $usuarioId = $this->getUsuarioId();
        $personaId = $this->getParametro("personaId");
        $personaTipoArchivo = $this->getParametro("personaTipoArchivo");
        $inputFile = $this->getParametro("inputFile");
        $fileName = $this->getParametro("fileName");
        
        return PersonaNegocio::create()->insertArchivo($usuarioId,$personaId,$personaTipoArchivo,$inputFile,$fileName);
    }

    public function obtenerArchivos(){
        $usuarioId = $this->getUsuarioId();
        $personaId = $this->getParametro("personaId");
        return PersonaNegocio::create()->obtenerArchivos($usuarioId,$personaId);

    }

    public function eliminarArchivos(){
        $usuarioId = $this->getUsuarioId();
        $id = $this->getParametro("id");
        $archivo = $this->getParametro("archivo");
        return PersonaNegocio::create()->eliminarArchivos($usuarioId ,$id ,$archivo );
    }

    // PLANTA
    public function obtenerArchivosPlanta(){
        $usuarioId = $this->getUsuarioId();
        $personaId = $this->getParametro("personaId");
        return PersonaNegocio::create()->obtenerArchivosPlanta($usuarioId,$personaId);

    }
    public function insertTipoDocumentoPlanta(){
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $nombreDocumento = $this->getParametro("nombreDocumento");
        return PersonaNegocio::create()->insertTipoDocumentoPlanta($usuarioId,$nombreDocumento);

    }
    

    public  function insertTipoDocumentoPLantaXPersona(){
        
        $this->setTransaction();
        $usuarioId = $this->getUsuarioId();
        $tipoDocumentoPlanta = $this->getParametro("tipoDocumentoPlanta");
        $personaId = $this->getParametro("personaId");
        $inputFile = $this->getParametro("inputFile");
        $fileName = $this->getParametro("fileName");




        return PersonaNegocio::create()->insertTipoDocumentoPLantaXPersona($usuarioId,$tipoDocumentoPlanta,$personaId,$inputFile,$fileName);


    }

    public function eliminarTipoDocumentoPLantaXPersona(){
        $usuarioId = $this->getUsuarioId();
        $id = $this->getParametro("id");
        return PersonaNegocio::create()->eliminarTipoDocumentoPLantaXPersona($usuarioId ,$id  );
    }
    

    public function getDataGridPersonaCuenta() {
        $usuarioId = $this->getUsuarioId();
        return PersonaNegocio::create()->obtenerCuentaPersonaXUsuarioId($usuarioId);
    }

    public function getAllBancos(){
        return CuentaNegocio::create()->obtenerCuentasActivas();
    }

    public function insertPersonaCuenta() {
        $numero = $this->getParametro("numero");
        $cci = $this->getParametro("cci");
        $bancoId = $this->getParametro("bancoId");
        $tipo = $this->getParametro("tipo");
        $tipo_cuenta = $this->getParametro("tipo_cuenta");
        $usuarioCreacion = $this->getUsuarioId();
        return PersonaNegocio::create()->insertPersonaCuenta($numero, $cci, $bancoId, $tipo, $tipo_cuenta, $usuarioCreacion);
    }

    public function updatePersonaCuenta() {
        $id = $this->getParametro("id");
        $numero = $this->getParametro("numero");
        $cci = $this->getParametro("cci");
        $bancoId = $this->getParametro("bancoId");
        $tipo = $this->getParametro("tipo");
        $tipo_cuenta = $this->getParametro("tipo_cuenta");
        return PersonaNegocio::create()->updatePersonaCuenta($id, $numero, $cci, $bancoId, $tipo, $tipo_cuenta);
    }

}
