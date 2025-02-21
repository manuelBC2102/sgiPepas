<?php

require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/MatrizAprobacionNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/SolicitudRetiroNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/ActaRetiroNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/EmpresaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/InvitacionNegocio.php';
require_once __DIR__ . '/../../util/Configuraciones.php';
require_once __DIR__ . '/../../util/ImportacionExcel.php';

class MatrizAprobacionControlador extends AlmacenIndexControlador {

    //funciones sobre la tabla persona clase

    public function datosInicialesModal(){
      $idMatriz = $this->getParametro("idMatriz");
      $usuarioId = $this->getUsuarioId();
      return MatrizAprobacionNegocio::create()->datosInicialesModal($idMatriz);
    }

    public function datosInicialesFiltros(){
        $idMatriz = $this->getParametro("idMatriz");
        $usuarioId = $this->getUsuarioId();
        return MatrizAprobacionNegocio::create()->datosInicialesModal($idMatriz);
      }


    public function getDataGridMatriz() {
        $idPersona = $this->getParametro("id");
        $documento = $this->getParametro("documentoF");
        $usuarioAprobador = $this->getParametro("usuarioF");
        $planta = $this->getParametro("plantaF");
        $zona = $this->getParametro("zonaF");
        $elemntosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $usuarioId = $this->getUsuarioId();
        $data = MatrizAprobacionNegocio::create()->getAllMatriz($elemntosFiltrados, $columns, $order, $start,$usuarioId,$documento,$usuarioAprobador,$planta,$zona);
        $response_cantidad_total = MatrizAprobacionNegocio::create()->getCantidadAllMatriz($elemntosFiltrados, $columns, $order, $start,$usuarioId,$documento,$usuarioAprobador,$planta,$zona);
        $response_cantidad_total[0]['total'];
        $elemntosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elemntosFiltrados, $elementosTotales);
    }



    public function obtenerSelectPersonas() {
        $stringBusqueda = $this->getParametro("querySelect");
        $personaId = $this->getParametro("personaId");
        return PersonaNegocio::create()->obtenerPersonaActivoXStringBusqueda($stringBusqueda, $personaId);
    }

    public function guardarAprobador() {
        $this->setTransaction();

        $documento = $this->getParametro("documento");
        $usuario = $this->getParametro("usuario");
        $zona = $this->getParametro("zona");
        $planta = $this->getParametro("planta");
        $file = $this->getParametro("file");
        $nivel = $this->getParametro("nivel");
        $comentario = $this->getParametro("comentario");
        $usuarioId = $this->getUsuarioId();
 
        return MatrizAprobacionNegocio::create()->guardarAprobador($documento,$usuario,$zona,$planta,$file ,$nivel,$usuarioId,$comentario);
    }

    public function actualizarAprobador() {
        $this->setTransaction();
        $id = $this->getParametro("id");
        $documento = $this->getParametro("documento");
        $usuario = $this->getParametro("usuario");
        $zona = $this->getParametro("zona");
        $planta = $this->getParametro("planta");
        $file = $this->getParametro("file");
        $nivel = $this->getParametro("nivel");
        $comentario = $this->getParametro("comentario");
        $comentario = $this->getParametro("comentario");
        $usuarioId = $this->getUsuarioId();
 
        return MatrizAprobacionNegocio::create()->actualizarAprobador($documento,$usuario,$zona,$planta,$file ,$nivel,$usuarioId,$comentario,$id);
    }

    
 
    public function obtenerConfiguracionesInvitacion() {
        $usuarioId = $this->getUsuarioId();
        $invitacionId = $this->getParametro("invitacionId");
        return InvitacionNegocio::create()->obtenerConfiguracionesInvitacion( $usuarioId,$invitacionId); 
    }
  
    public function obtenerParametrosIniciales() {
        $this->setTransaction();
        $parametros = $this->getParametro("parametros");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerParametrosIniciales( $usuarioId,$parametros); 

    }

      
    public function obtenerDocumentosPlanta() {
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $planta = $this->getParametro("planta");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerDocumentosPlanta( $usuarioId,$persona,$planta); 

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


    public function deleteSolicitud() {
        $id = $this->getParametro("id");
        $usuarioSesion = $this->getUsuarioId();
        return MatrizAprobacionNegocio::create()->deleteElementoMatriz($id, $usuarioSesion, $estado = 2);
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


    public function obtenerPlantasXPersona(){
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerPlantasXPersona($persona,$usuarioId);
   

    }

    public function guardarInvitacionConformidad(){
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $selectedItems = $this->getParametro("selectedItems");
        $file = $this->getParametro("file");
        $name = $this->getParametro("name");
        $parametros = $this->getParametro("parametros");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->guardarInvitacionConformidad($persona,$selectedItems,$file,$name,$usuarioId,$parametros);
    }


    
}
