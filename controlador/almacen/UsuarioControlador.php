<?php

require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/UsuarioNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/ColaboradorNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/InvitacionNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PerfilNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/EmpresaNegocio.php';

class UsuarioControlador extends AlmacenIndexControlador {

    public function validateLogin($usuario, $contrasena) {
        return UsuarioNegocio::create()->validateLogin($usuario, $contrasena);
    }

    public function autenticarUsuario() {
        $usuario = $this->getParametro("usuario");
        $contrasena = Util::encripta($this->getParametro("contrasena"));
        $tokenUser = $this->getParametro("tokenUser");
        
        return UsuarioNegocio::create()->validateLogin($usuario, $contrasena,"movil",$tokenUser);
    }

    public function getDataGridUsuario() {
        return UsuarioNegocio::create()->getDataUsuario();
    }

    public function getComboColaborador() {
        return UsuarioNegocio::create()->getComboColaborador();
    }

    public function getComboPerfil() {
        return UsuarioNegocio::create()->getComboPerfil();
    }
   

    public function insertUsuario() {
        $usu_nombre = $this->getParametro("usu_nombre");
        $id_colaborador = $this->getParametro("id_colaborador");
        $id_perfil = $this->getParametro("id_perfil");
        $id_zona = $this->getParametro("id_zona");
        $estado = $this->getParametro("estado");
        $empresa = $this->getParametro("empresa");
        $combo = $this->getParametro("combo");
        $usu_creacion = $this->getUsuarioId();
        $jefeId = $this->getParametro("jefeId");
        return UsuarioNegocio::create()->insertUsuario($usu_nombre, $id_colaborador, $id_perfil, $usu_creacion,
                $estado, $empresa, $combo,$jefeId,$id_zona);
    }

    public function getUsuario() {
        $id_usuario = $this->getParametro("id_usuario");
        $usuarioId = $this->getUsuarioId();
        return UsuarioNegocio::create()->getUsuario($id_usuario, $usuarioId);
    }

    public function updateUsuario() {
        $id = $usu_nombre = $this->getParametro("id_usuario");
        $usu_nombre = $this->getParametro("usu_nombre");
        $id_colaborador = $this->getParametro("id_colaborador");
        $id_perfil = $this->getParametro("id_perfil");
        $estado = $this->getParametro("estado");
        $empresa = $this->getParametro("empresa");
        $combo = $this->getParametro("combo");
        $usuarioId = $this->getUsuarioId();
        $jefeId = $this->getParametro("jefeId");
        $id_zona = $this->getParametro("id_zona");
        
        return UsuarioNegocio::create()->updateUsuario($id, $usu_nombre, $id_colaborador, $id_perfil, $estado,
                $empresa, $combo, $usuarioId,$jefeId,$id_zona);
    }

    public function deleteUsuario() {

        $id_usuario = $this->getParametro("id_usuario");
        $nom = $this->getParametro("nom");
        $usuarioId = $this->getUsuarioId();
//        throw new WarningException("hola");
        return UsuarioNegocio::create()->deleteUsuario($id_usuario, $nom, $usuarioId);
    }

    public function cambiarEstado() {
        $id_estado = $this->getParametro("id_estado");
        $usuarioId = $this->getUsuarioId();
        return UsuarioNegocio::create()->cambiarEstado($id_estado, $usuarioId);
    }

    public function colaboradorPorUsuario($id_usuario) {
        return UsuarioNegocio::create()->colaboradorPorUsuario($id_usuario);
    }

    public function recuperarContrasena($usu_email) {
        return UsuarioNegocio::create()->recuperarContrasena($usu_email);
    }

    public function obtenerContrasenaActual() {
        $usu = $this->getParametro("usuario");
        return UsuarioNegocio::create()->obtenerContrasenaActual($usu);
    }

    public function obtenerPantallaPrincipalUsuario() {
        return UsuarioNegocio::create()->obtenerPantallaPrincipalUsuario();
    }

    public function getComboEmpresa() {
        $usuarioId = $this->getUsuarioId();
        return EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    }
    
    public function obtenerUsuarios(){        
        return UsuarioNegocio::create()->getDataUsuario();
    }
    public function getComboZona() {
        return UsuarioNegocio::create()->getComboZona();
    }
    public function obtenerParametrosIniciales() {
        $this->setTransaction();
        $parametros = $this->getParametro("parametros");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerParametrosIniciales( $usuarioId,$parametros); 

    }

    public function obtenerParametrosInicialesPrincipal() {
        $this->setTransaction();
        $parametros = $this->getParametro("parametros");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerParametrosInicialesPrincipal( $usuarioId,$parametros); 

    }
    public function listarInvitacion() {
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->listarInvitacion($usuarioId );
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
        $ubicacion = $this->getParametro("ubicacion");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->guardarInvitacion($ruc,$codigo,$nombre,$sector,$estado ,$ubicacion,$telefono,$correo,$usuarioId);
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
    public function actualizarInvitacion() {
        $this->setTransaction();
        $invitacionId= $this->getParametro("invitacionId");
        $ruc = $this->getParametro("ruc");
        $codigo = $this->getParametro("codigo");
        $nombre = $this->getParametro("nombre");
        $sector = $this->getParametro("sector");
        $estado = $this->getParametro("estado");
        $ubicacion = $this->getParametro("ubicacion");
        $telefono = $this->getParametro("telefono");
        $correo = $this->getParametro("correo");
        $usuarioId = $this->getUsuarioId();
 
        return InvitacionNegocio::create()->actualizarInvitacion($ruc,$codigo,$nombre,$sector,$estado ,$ubicacion,$telefono,$correo,$usuarioId,$invitacionId);
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
  
    public function finalizarAprobacionAsociativo(){
        $usuarioId = $this->getUsuarioId();
        $nivel = $this->getParametro("nivel");
        $invitacionId = $this->getParametro("invitacion");
        return InvitacionNegocio::create()->finalizarAprobacionAsociativo( $usuarioId,$nivel,$invitacionId); 
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
    public function obtenerDocumentosAdministracionAsociativo() {
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerDocumentosAdministracionAsociativo( $usuarioId,$persona); 

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
    public function obtenerCoordenadas() {
        $this->setTransaction();
        $persona = $this->getParametro("persona");
        $usuarioId = $this->getUsuarioId();
        return InvitacionNegocio::create()->obtenerCoordenadas( $usuarioId,$persona); 

    }
}
