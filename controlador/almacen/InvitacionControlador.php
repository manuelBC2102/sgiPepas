<?php

require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/SolicitudRetiroNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/ActaRetiroNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/ZonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/VehiculoNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/EmpresaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/InvitacionNegocio.php';
require_once __DIR__ . '/../../util/Configuraciones.php';
require_once __DIR__ . '/../../util/ImportacionExcel.php';
require_once __DIR__ . '/../../modelo/almacen/Vehiculo.php';

class InvitacionControlador extends AlmacenIndexControlador {

    //funciones sobre la tabla persona clase




    public function getDataGridInvitacion() {
        $idPersona = $this->getParametro("id");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = InvitacionNegocio::create()->getAllInvitaciones($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona );
        $response_cantidad_total = InvitacionNegocio::create()->getCantidadAllInvitaciones($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona );
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }

    public function getDataGridInvitacionTransportista() {
        $idPersona = $this->getParametro("id");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = InvitacionNegocio::create()->getAllInvitacionesTransportista($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona );
        $response_cantidad_total = InvitacionNegocio::create()->getCantidadAllInvitacionesTransportista($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona );
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }

    public function getDataGridInvitacionPlanta() {
        $idPersona = $this->getParametro("id");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = InvitacionNegocio::create()->getAllInvitacionesPlanta($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona );
        $response_cantidad_total = InvitacionNegocio::create()->getCantidadAllInvitacionesPlanta($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona );
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }

    public function getDataGridInvitacionPrincipal() {
        $idPersona = $this->getParametro("id");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = InvitacionNegocio::create()->getAllInvitacionesPrincipal($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona );
        $response_cantidad_total = InvitacionNegocio::create()->getCantidadAllInvitacionesPrincipal($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona );
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }

    public function getDataGridInvitacionSecundario() {
        $idPersona = $this->getParametro("id");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $tipo=2;
        $usuarioId = $this->getUsuarioId();
        $data = InvitacionNegocio::create()->getAllInvitacionesPrincipal($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$tipo );
        $response_cantidad_total = InvitacionNegocio::create()->getCantidadAllInvitacionesPrincipal($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona,$tipo  );
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }

    public function listarInvitacion() {
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->listarInvitacion($usuarioId );
    }

    public function listarInvitacionTransportista() {
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->listarInvitacionTransportista($usuarioId );
    }

    public function listarInvitacionPlanta() {
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->listarInvitacionPlanta($usuarioId );
    }

    public function listarInvitacionAsociativa() {
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->listarInvitacionAsociativa($usuarioId );
    }
    
    
    public function getDataGridActaRetiroPlanta() {
        $idPersona = $this->getParametro("id");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = ActaRetiroNegocio::create()->getAllActasRetiroPlanta($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona);
        $response_cantidad_total = ActaRetiroNegocio::create()->getCantidadAllActasRetiroPlanta($elemntosFiltrados, $columns, $order, $start,$usuarioId,$idPersona);
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }
    


    public function guardarInvitacion() {
        $this->setTransaction();
        $ruc = $this->getParametro("ruc");
        $codigo = $this->getParametro("codigo");
        $nombre = $this->getParametro("nombre");
        $sector = $this->getParametro("sector");
        $estado = $this->getParametro("estado");
        $departamento = $this->getParametro("departamento");
        $provincia = $this->getParametro("provincia");
        $distrito = $this->getParametro("distrito");
        $ubigeo = $this->getParametro("ubigeo");
        $direccion = $this->getParametro("direccion");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $zona = $this->getParametro("zona");
        $organizacion = $this->getParametro("organizacion");
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->guardarInvitacion($ruc,$codigo,
        $nombre,$sector,$estado ,$departamento,$provincia,$distrito,
        $ubigeo,$direccion,$telefono,$correo,$usuarioId,$zona,$organizacion);
    }


    public function guardarInvitacionTransportista() {
        $this->setTransaction();
        $ruc = $this->getParametro("ruc");
        $codigo = $this->getParametro("codigo");
        $nombre = $this->getParametro("nombre");
        $direccion = $this->getParametro("direccion");
        $estado = $this->getParametro("estado");
        $modalidad = $this->getParametro("modalidad");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->guardarInvitacionTransportista($ruc,$codigo,$nombre,$direccion,$estado ,$modalidad,$telefono,$correo,$usuarioId);
    }

    
    public function guardarInvitacionPlanta() {
        $this->setTransaction();
        $ruc = $this->getParametro("ruc");
        $fecha = $this->getParametro("fecha");
        $nombre = $this->getParametro("nombre");
        $direccion = $this->getParametro("direccion");
        $estado = $this->getParametro("estado");
        $modalidad = $this->getParametro("modalidad");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->guardarInvitacionPlanta($ruc,$fecha,$nombre,$direccion,$estado ,$modalidad,$telefono,$correo,$usuarioId);
    }

    public function guardarInvitacionPrincipal() {
        $this->setTransaction();
        $ruc = $this->getParametro("ruc");
        // $fecha = $this->getParametro("fecha");
        $nombre = $this->getParametro("nombre");
        $direccion = $this->getParametro("direccion");
        $ubigeo = $this->getParametro("ubigeo");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->guardarInvitacionPrincipal($ruc,$nombre,$direccion,$ubigeo ,$telefono,$correo,$usuarioId);
    }

    
    public function guardarInvitacionSecundario() {
        $this->setTransaction();
        $ruc = $this->getParametro("ruc");
        // $fecha = $this->getParametro("fecha");
        $nombre = $this->getParametro("nombre");
        $direccion = $this->getParametro("direccion");
        $ubigeo = $this->getParametro("ubigeo");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->guardarInvitacionSecundario($ruc,$nombre,$direccion,$ubigeo ,$telefono,$correo,$usuarioId);
    }
    
    public function actualizarInvitacion() {
        $this->setTransaction();
        $invitacionId= $this->getParametro("invitacionId");
        $ruc = $this->getParametro("ruc");
        $codigo = $this->getParametro("codigo");
        $nombre = $this->getParametro("nombre");
        $sector = $this->getParametro("sector");
        $estado = $this->getParametro("estado");
        $departamento = $this->getParametro("departamento");
        $provincia = $this->getParametro("provincia");
        $distrito = $this->getParametro("distrito");
        $ubigeo = $this->getParametro("ubigeo");
        $direccion = $this->getParametro("direccion");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $zona = $this->getParametro("zona");
        $organizacion = $this->getParametro("organizacion");
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->actualizarInvitacion($ruc,$codigo,$nombre,$sector,$estado ,$departamento,$provincia,$distrito,$ubigeo,$direccion,$telefono,$correo,$usuarioId,$invitacionId,$zona,$organizacion);
    }
    
    public function actualizarInvitacionTransportista() {
        $this->setTransaction();
        $invitacionId= $this->getParametro("invitacionId");
        $ruc = $this->getParametro("ruc");
        $codigo = $this->getParametro("codigo");
        $nombre = $this->getParametro("nombre");
        $direccion = $this->getParametro("direccion");
        $estado = $this->getParametro("estado");
        $modalidad = $this->getParametro("modalidad");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->actualizarInvitacionTransportista($ruc,$codigo,$nombre,$direccion,$estado ,$modalidad,$telefono,$correo,$usuarioId,$invitacionId);
    }

    public function actualizarInvitacionPlanta() {
        $this->setTransaction();
        $invitacionId= $this->getParametro("invitacionId");
        $ruc = $this->getParametro("ruc");
        $fecha = $this->getParametro("fecha");
        $nombre = $this->getParametro("nombre");
        $direccion = $this->getParametro("direccion");
        $estado = $this->getParametro("estado");
        $modalidad = $this->getParametro("modalidad");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->actualizarInvitacionPlanta($ruc,$fecha,$nombre,$direccion,$estado ,$modalidad,$telefono,$correo,$usuarioId,$invitacionId);
    }

    public function actualizarInvitacionPrincipal() {
        $this->setTransaction();
        $invitacionId= $this->getParametro("invitacionId");
        $ruc = $this->getParametro("ruc");
        $nombre = $this->getParametro("nombre");
        $direccion = $this->getParametro("direccion");
        $ubigeo = $this->getParametro("ubigeo");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->actualizarInvitacionPrincipal($ruc,$nombre,$direccion ,$ubigeo,$telefono,$correo,$usuarioId,$invitacionId);
    }


    public function obtenerConfiguracionesInvitacion() {
        $usuarioId = $this->getUsuarioId();
        $invitacionId = $this->getParametro("invitacionId");
        return InvitacionNegocio::create()->obtenerConfiguracionesInvitacion( $usuarioId,$invitacionId); 
    }

    public function obtenerConfiguracionesInvitacionTransportista() {
        $usuarioId = $this->getUsuarioId();
        $invitacionId = $this->getParametro("invitacionId");
        return InvitacionNegocio::create()->obtenerConfiguracionesInvitacionTransportista( $usuarioId,$invitacionId); 
    }


    
    public function obtenerConfiguracionesInvitacionPlanta() {
        $usuarioId = $this->getUsuarioId();
        $invitacionId = $this->getParametro("invitacionId");
        return InvitacionNegocio::create()->obtenerConfiguracionesInvitacionPlanta( $usuarioId,$invitacionId); 
    }

    public function obtenerConfiguracionesInvitacionPrincipal() {
        $usuarioId = $this->getUsuarioId();
        $invitacionId = $this->getParametro("invitacionId");
        return InvitacionNegocio::create()->obtenerConfiguracionesInvitacionPrincipal( $usuarioId,$invitacionId); 
    }
    public function finalizarAprobacion(){
        $usuarioId = $this->getUsuarioId();
        $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        return InvitacionNegocio::create()->finalizarAprobacion( $usuarioId,$nivel,$invitacionId); 
    }

    public function finalizarAprobacionTransportista(){
        $usuarioId = $this->getUsuarioId();
        // $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        return InvitacionNegocio::create()->finalizarAprobacionTransportista( $usuarioId,$invitacionId); 
    }

    public function finalizarAprobacionPlanta(){
        $usuarioId = $this->getUsuarioId();
        // $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        return InvitacionNegocio::create()->finalizarAprobacionPlanta( $usuarioId,$invitacionId); 
    }
  

    public function finalizarAprobacionAsociativo(){
        $usuarioId = $this->getUsuarioId();
        $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        return InvitacionNegocio::create()->finalizarAprobacionAsociativo( $usuarioId,$nivel,$invitacionId); 
    }
    public function obtenerParametrosIniciales() {
        $this->setTransaction();
        $parametros = $this->getParametro("parametros");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerParametrosIniciales( $usuarioId,$parametros); 

    }

    public function obtenerParametrosInicialesTransportista() {
        $this->setTransaction();
        $parametros = $this->getParametro("parametros");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerParametrosInicialesTransportista( $usuarioId,$parametros); 

    }
    public function obtenerParametrosInicialesPlanta() {
        $this->setTransaction();
        $parametros = $this->getParametro("parametros");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerParametrosInicialesPlanta( $usuarioId,$parametros); 

    }

    
    public function obtenerParametrosInicialesPrincipal() {
        $this->setTransaction();
        $parametros = $this->getParametro("parametros");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerParametrosInicialesPrincipal( $usuarioId,$parametros); 

    }
      
    public function obtenerDocumentosPlanta() {
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $planta = $this->getParametro("planta");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerDocumentosPlanta( $usuarioId,$persona,$planta); 

    }

    public function obtenerDocumentosAdministracion() {
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerDocumentosAdministracion( $usuarioId,$persona); 

    }

    public function obtenerDocumentosAdministracionTransportista() {
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerDocumentosAdministracionTransportista( $usuarioId,$persona); 

    }

    public function obtenerDocumentosAdministracionPlanta() {
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerDocumentosAdministracionPlanta( $usuarioId,$persona); 

    }

    public function obtenerDocumentosAdministracionAsociativo() {
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerDocumentosAdministracionAsociativo( $usuarioId,$persona); 

    }

    public function obtenerTipoDocumento23(){
        return PersonaNegocio::create()->obtenerTipoDocumento();
    }

    public function obtenerArchivosPlanta(){
        $usuarioId = $this->getUsuarioId();
        $personaId = $this->getParametro("personaId");
        return PersonaNegocio::create()->obtenerArchivosPlanta($usuarioId,$personaId);

    }

    public function insertTipoDocumentoPlanta(){
        $this->setTransaction();
        $nombreDocumento = $this->getParametro("nombreDocumento");
        return PersonaNegocio::create()->insertTipoDocumentoPlanta(1,$nombreDocumento);

    }
    public  function insertTipoDocumentoPLantaXPersona(){
        
        $this->setTransaction();
        // $usuarioId = $this->getUsuarioId();
        $tipoDocumentoPlanta = $this->getParametro("tipoDocumentoPlanta");
        $personaId = $this->getParametro("personaId");
        $inputFile = $this->getParametro("inputFile");
        $fileName = $this->getParametro("fileName");




        return PersonaNegocio::create()->insertTipoDocumentoPLantaXPersona(1,$tipoDocumentoPlanta,$personaId,$inputFile,$fileName);


    }

    public function eliminarTipoDocumentoPLantaXPersona(){
        $usuarioId = $this->getUsuarioId();
        $id = $this->getParametro("id");
        return PersonaNegocio::create()->eliminarTipoDocumentoPLantaXPersona($usuarioId ,$id  );
    }

    public function obtenerCoordenadas() {
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerCoordenadas( $usuarioId,$persona); 

    }

    public function obtenerCoordenadasVehiculos() {
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerCoordenadasVehiculos( $usuarioId,$persona); 

    }

    public function validarPlaca(){
        $this->setTransaction();
        $placa = $this->getParametro("placa");
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        $placa2 = str_replace('-', '', $placa);
        $respuesta=VehiculoNegocio::create()->validarPlacaEndPoint($placa2); 
        $actualizaVehiculo=Vehiculo::create()->actualizarVehiculoPlaca($placa,$persona,$respuesta[0]->nro_constancia,$respuesta[0]->carga_util); 
        return $respuesta;

    }

    public function subirArchivo() {
        $id = $this->getParametro("id");
        $file = $this->getParametro("file");
        $tipo = $this->getParametro("tipo");
        $name = $this->getParametro("name");
        $persona = $this->getParametro("persona");
        $planta = $this->getParametro("planta");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->subirArchivo($id,$file,$tipo,$name,$persona,$usuarioId,$planta);
    }

    public function eliminarArchivo() {
        $id = $this->getParametro("id");
        $archivo = $this->getParametro("archivo");
        $tipo = $this->getParametro("tipo");
        $persona = $this->getParametro("persona");
        $planta = $this->getParametro("planta");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->eliminarArchivo($id,$archivo,$tipo,$persona,$planta);
    }

    public function subirArchivo2() {
        $id = $this->getParametro("id");
        $file = $this->getParametro("file");
        $tipo = $this->getParametro("tipo");
        $name = $this->getParametro("name");
        $persona = $this->getParametro("persona");
        $planta = $this->getParametro("planta");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->subirArchivo($id,$file,$tipo,$name,$persona,$usuarioId,$planta);
    }

    public function eliminarArchivo2() {
        $id = $this->getParametro("id");
        $archivo = $this->getParametro("archivo");
        $tipo = $this->getParametro("tipo");
        $persona = $this->getParametro("persona");
        $planta = $this->getParametro("planta");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->eliminarArchivo($id,$archivo,$tipo,$persona,$planta);
    }


    public function deleteSolicitud() {
        $id = $this->getParametro("id");
        $usuarioSesion = $this->getUsuarioId();
        return InvitacionNegocio::create()->deleteSolicitud($id, $usuarioSesion, $estado = 2);
    }

    public function deleteSolicitudTransportista() {
        $id = $this->getParametro("id");
        $usuarioSesion = $this->getUsuarioId();
        return InvitacionNegocio::create()->deleteSolicitudTransportista($id, $usuarioSesion, $estado = 2);
    }

    public function deleteSolicitudPlanta() {
        $id = $this->getParametro("id");
        $usuarioSesion = $this->getUsuarioId();
        return InvitacionNegocio::create()->deleteSolicitudPlanta($id, $usuarioSesion, $estado = 2);
    }

    public function deleteSolicitudPrincipal() {
        $id = $this->getParametro("id");
        $usuarioSesion = $this->getUsuarioId();
        return InvitacionNegocio::create()->deleteSolicitudPrincipal($id, $usuarioSesion, $estado = 2);
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
    

    public function obtenerDataREINFO() {
        $usuarioId = $this->getUsuarioId();
        $ruc = $this->getParametro("ruc");
        $codigo = $this->getParametro("codigo");
        $data=InvitacionNegocio::create()->obtenerDataREINFO($usuarioId,$ruc,$codigo);
        return $data;
    }
    
    public function obtenerDataDNI() {
        $usuarioId = $this->getUsuarioId();
        $dni = $this->getParametro("dni");
        $tipoDNI = $this->getParametro("tipoDNI");
        $data=InvitacionNegocio::create()->obtenerDataDNI($dni,$tipoDNI);
        return $data;
    }

    public function obtenerDataDNI2() {
        $usuarioId = $this->getUsuarioId();
        $dni = $this->getParametro("dni");
        $tipoDNI = $this->getParametro("tipoDNI");
        $data=InvitacionNegocio::create()->obtenerDataDNI2($dni,$tipoDNI);
        return $data;
    }
    public function obtenerDataTransportista() {
        $usuarioId = $this->getUsuarioId();
        $ruc = $this->getParametro("ruc");
        $data=InvitacionNegocio::create()->obtenerDataTransportista($usuarioId,$ruc);
        return $data;
    }

    public function obtenerDataPlanta() {
        $usuarioId = $this->getUsuarioId();
        $ruc = $this->getParametro("ruc");
        $data=InvitacionNegocio::create()->obtenerDataPlanta($usuarioId,$ruc);
        return $data;
    }


    public function obtenerPlantasXPersona(){
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerPlantasXPersona($persona,$usuarioId);
   

    }

    public function obtenerPlantasXPersonaXAsociativa(){
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerPlantasXPersonaXAsociativa($persona,$usuarioId);
   

    }

    public function guardarInvitacionConformidad(){
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $selectedItems = $this->getParametro("selectedItems");
        $file = $this->getParametro("file");
        $name = $this->getParametro("name");
        $parametros = $this->getParametro("parametros");
        $coordenadas = $this->getParametro("coordenadas");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->guardarInvitacionConformidad($persona,$selectedItems,$file,$name,$usuarioId,$parametros,$coordenadas);
    }

    public function guardarInvitacionConformidadAsociativa(){
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $selectedItems = $this->getParametro("selectedItems");
        $file = $this->getParametro("file");
        $name = $this->getParametro("name");
        $parametros = $this->getParametro("parametros");
        $coordenadas = $this->getParametro("coordenadas");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->guardarInvitacionConformidadAsociativa($persona,$selectedItems,$file,$name,$usuarioId,$parametros,$coordenadas);
    }

    public function guardarInvitacionTransportistaC(){
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        // $selectedItems = $this->getParametro("selectedItems");
        $file = $this->getParametro("file");
        $name = $this->getParametro("name");
        $parametros = $this->getParametro("parametros");
        $coordenadas = $this->getParametro("coordenadas");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->guardarInvitacionTransportistaC($persona,$file,$name,$usuarioId,$parametros,$coordenadas);
    }


    public function guardarInvitacionPlantaC(){
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        // $selectedItems = $this->getParametro("selectedItems");
        $file = $this->getParametro("file");
        $name = $this->getParametro("name");
        $parametros = $this->getParametro("parametros");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->guardarInvitacionPlantaC($persona,$file,$name,$usuarioId,$parametros);
    }


    public function finalizarRechazo(){
        $usuarioId = $this->getUsuarioId();
        $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        $comentario= $this->getParametro("comentario");
        return InvitacionNegocio::create()->finalizarRechazo( $usuarioId,$nivel,$invitacionId,$comentario); 
    }

    public function finalizarRechazoAsociativo(){
        $usuarioId = $this->getUsuarioId();
        $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        $comentario= $this->getParametro("comentario");
        return InvitacionNegocio::create()->finalizarRechazoAsociativo( $usuarioId,$nivel,$invitacionId,$comentario); 
    }
    public function finalizarRechazoTransportista(){
        $usuarioId = $this->getUsuarioId();
        // $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        $comentario= $this->getParametro("comentario");
        return InvitacionNegocio::create()->finalizarRechazoTransportista( $usuarioId,$invitacionId,$comentario); 
    }

    public function finalizarRechazoPlanta(){
        $usuarioId = $this->getUsuarioId();
        // $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        $comentario= $this->getParametro("comentario");
        return InvitacionNegocio::create()->finalizarRechazoPlanta( $usuarioId,$invitacionId,$comentario); 
    }

    public function solicitarActualizacion(){
        $usuarioId = $this->getUsuarioId();
        $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        $comentario= $this->getParametro("comentario");
        return InvitacionNegocio::create()->solicitarActualizacion( $usuarioId,$nivel,$invitacionId,$comentario); 
    }

    public function solicitarActualizacionAsociativo(){
        $usuarioId = $this->getUsuarioId();
        $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        $comentario= $this->getParametro("comentario");
        return InvitacionNegocio::create()->solicitarActualizacionAsociativo( $usuarioId,$nivel,$invitacionId,$comentario); 
    }

    public function solicitarActualizacionTransportista(){
        $usuarioId = $this->getUsuarioId();
        // $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        $comentario= $this->getParametro("comentario");
        return InvitacionNegocio::create()->solicitarActualizacionTransportista( $usuarioId,$invitacionId,$comentario); 
    }

    public function solicitarActualizacionPlanta(){
        $usuarioId = $this->getUsuarioId();
        // $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        $comentario= $this->getParametro("comentario");
        return InvitacionNegocio::create()->solicitarActualizacionPlanta( $usuarioId,$invitacionId,$comentario); 
    }

    public function obtenerZonas(){
        $usuarioId = $this->getUsuarioId();
        return ZonaNegocio::create()->listarZona();
    }

    public function buscarCriteriosBusquedaSolicitud() {
        $busqueda = $this->getParametro("busqueda");
        $usuarioId = $this->getUsuarioId();

        $dataPersona = InvitacionNegocio::create()->buscarCriteriosBusquedaSolicitud($busqueda, $usuarioId);
        $resultado->dataPersona = $dataPersona;

        return $resultado;
    }

    public function buscarCriteriosBusquedaSolicitudTransportista() {
        $busqueda = $this->getParametro("busqueda");
        $usuarioId = $this->getUsuarioId();

        $dataPersona = InvitacionNegocio::create()->buscarCriteriosBusquedaSolicitudTransportista($busqueda, $usuarioId);
        $resultado->dataPersona = $dataPersona;

        return $resultado;
    }

    public function buscarCriteriosBusquedaSolicitudPrincipal() {
        $busqueda = $this->getParametro("busqueda");
        $usuarioId = $this->getUsuarioId();

        $dataPersona = InvitacionNegocio::create()->buscarCriteriosBusquedaSolicitudPrincipal($busqueda, $usuarioId);
        $resultado->dataPersona = $dataPersona;

        return $resultado;
    }
    
    public function obtenerBancos() {
        
        $usuarioId = $this->getUsuarioId();

        $resultado = InvitacionNegocio::create()->obtenerCuentasBancos($usuarioId);

        return $resultado;
    }


    
    public function guardarComunero() {
        $this->setTransaction();
        $dni = $this->getParametro("dni");
        $tipo = $this->getParametro("tipo");
        $foto = $this->getParametro("foto");
        $codigo = $this->getParametro("codigo");
        $nombre = $this->getParametro("nombre");
        $lugarNacimiento = $this->getParametro("lugarNacimiento");
        $fechaNacimiento = $this->getParametro("fechaNacimiento");
        $direccion = $this->getParametro("direccion");
        $estadoCivil = $this->getParametro("estadoCivil");
        $hijo = $this->getParametro("hijo");
        $estatura = $this->getParametro("estatura");
        $madre = $this->getParametro("madre");
        $padre = $this->getParametro("padre");
        $restriccion = $this->getParametro("restriccion");
        $sexo = $this->getParametro("sexo");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $cuenta = $this->getParametro("cuenta");
        $numeroCuenta = $this->getParametro("numeroCuenta");
        $cci = $this->getParametro("cci");
        $firma = $this->getParametro("firma");
        
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->guardarComunero($dni,$tipo,
        $foto,$codigo,$nombre ,$lugarNacimiento,$fechaNacimiento,$direccion,
        $estadoCivil,$hijo,$estatura,$madre,$padre,$restriccion,$sexo,
        $telefono,$correo,$cuenta,$numeroCuenta,$cci,$firma,$usuarioId);
    }
         
    
}
