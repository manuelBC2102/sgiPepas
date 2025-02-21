<?php

require_once __DIR__ . "/../core/ModeloBase.php";
require_once __DIR__ . "/../enumeraciones/EstadoGenerico.php";

/**
 * Description of Perfil
 *
 * @author JSC7
 */
class Perfil extends ModeloBase {

    const DEFAULT_ALIAS = "perfil";

    public function __construct() {
        parent::__construct();
        $this->schema_name = Schema::cbp;
        $this->table_name = 'perfil';
        $this->fields = array('id', 'codigo', 'nombre', 'descripcion', 'estado',
            'visible', 'fec_creacion', 'usu_creacion');
    }

    static function create() {
        return parent::create();
    }

    public function getMenuHijoPerfil($opcion_id_predecesor, $perfil_id) {

        $this->commandPrepare("sp_perfil_getMenuHijo");
        $this->commandAddParameter(":vin_predecesor_id", $opcion_id_predecesor);
        $this->commandAddParameter(":vin_perfil_id", $perfil_id);
        return $this->commandGetData();
    }

    public function getDataPerfil() {
        $this->commandPrepare("sp_perfil_getAll");
        return $this->commandGetData();
    }

    public function insertPerfil($nombre, $descripcion, $estado, $dashboard, $email, $monetaria, $visible, $usuario, $pant_principal) {
        $this->commandPrepare("sp_perfil_insert");
        $this->commandAddParameter(":vin_nombre", $nombre);
        $this->commandAddParameter(":vin_descripcion", $descripcion);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_dashboard", $dashboard);
        $this->commandAddParameter(":vin_email", $email);
        $this->commandAddParameter(":vin_monetaria", $monetaria);
        $this->commandAddParameter(":vin_usu_creacion", $usuario);
        $this->commandAddParameter(":vin_pant_principal", $pant_principal);
        return $this->commandGetData();
    }
    public function getPerfil($id) {
        $this->commandPrepare("sp_perfil_getById");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function updatePerfil($id, $nombre, $descripcion, $estado, $dashboard, $email, $monetaria, $pant_principal, $PerfilId) {
        $this->commandPrepare("sp_perfil_update");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_nombre", $nombre);
        $this->commandAddParameter(":vin_descripcion", $descripcion);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_dashboard", $dashboard);
        $this->commandAddParameter(":vin_email", $email);
        $this->commandAddParameter(":vin_monetaria", $monetaria);
        $this->commandAddParameter(":vin_pantalla_principal", $pant_principal);
        $this->commandAddParameter(":vin_perfil_id", $PerfilId);
        return $this->commandGetData();
    }

    public function deletePerfil($id, $id_per_ensesion) {
        $this->commandPrepare("sp_perfil_delete");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_id_per_sesion", $id_per_ensesion);
        return $this->commandGetData();
    }

    public function getMenuPadre() {
        $this->commandPrepare("sp_perfil_getOpcionMenuPadre");
        return $this->commandGetData();
    }

    public function getMenuHijo($opcion_id_predecesor) {
        $this->commandPrepare("sp_perfil_getOpcionMenuHijo");
        $this->commandAddParameter(":vin_predecesor_id", $opcion_id_predecesor);
        return $this->commandGetData();
    }

    public function insertDetOpcPerfil($id_per, $id_opcion, $estado, $id_usu) {
        $this->commandPrepare("sp_opcion_perfil_insert");
        $this->commandAddParameter(":vin_perifl_id", $id_per);
        $this->commandAddParameter(":vin_opcion_id", $id_opcion);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario_id", $id_usu);
        return $this->commandGetData();
    }

    public function getDetOpcPer($id_perfil) {
        $this->commandPrepare("sp_seg_obtener_opcion_por_perfil");
        $this->commandAddParameter(":vin_perifl_id", $id_perfil);
    }

    public function updateDetOpcPerfil($id_per, $id_opcion, $estado,$usuario_creacion) {
        $this->commandPrepare("sp_opcion_perfil_update");
        $this->commandAddParameter(":vin_perfil_id", $id_per);
        $this->commandAddParameter(":vin_opcion_id", $id_opcion);
        $this->commandAddParameter(":vin_estado", $estado);
        $this->commandAddParameter(":vin_usuario_creacion", $usuario_creacion);
        return $this->commandGetData();
    }

//    public function insertDetUsuarioPerfil($id_usuario, $id_perfil, $usu_creacion, $estado, $visible) {
//        $this->commandPrepare("sp_seg_insertar_det_usu_perfil");
//        $this->commandAddParameter(":vin_usuario_id", $id_usuario);
//        $this->commandAddParameter(":vin_perfil_id", $id_perfil);
//        $this->commandAddParameter(":vin_usu_creacion", $usu_creacion);
//        $this->commandAddParameter(":vin_estado", $estado);
//        $this->commandAddParameter(":vin_visible", $visible);
//        return $this->commandGetData();
//    }

    public function obtenerPantallaPrincipal($id) {
        $this->commandPrepare("sp_perfil_getPantallaPrincipal");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }
    
    public function obtenerPantallaXToken($token,$id) {
        $this->commandPrepare("sp_perfil_obtenerPantallaXToken");
        $this->commandAddParameter(":vin_id", $token);
        $this->commandAddParameter(":vin_id_usuario", $id);
        return $this->commandGetData();
    }

    public function obtenerImagenPerfil($id_per, $id_usu) {
        $this->commandPrepare("sp_usuario_getImagen");
        $this->commandAddParameter(":vin_id_perfil", $id_per);
        $this->commandAddParameter(":vin_id_usuario", $id_usu);
        return $this->commandGetData();
    }

//    public function insertPerfilEmpresa($id_p, $id_e,$visible,$estadoep) {
//        $this->commandPrepare("sp_seg_insertar_perfil_empresa");
//        $this->commandAddParameter(":vin_id_p", $id_p);
//        $this->commandAddParameter(":vin_id_e", $id_e);
//        $this->commandAddParameter(":vin_visible", $visible);
//        $this->commandAddParameter(":vin_estado", $estadoep);
//        return $this->commandGetData();
//    }
//    public function updatePerfilEmpresa($id,$id_emp,$estado)
//    {
//      $this->commandPrepare("sp_seg_actualizar_perfil_empresa");
//        $this->commandAddParameter(":vin_id", $id);
//        $this->commandAddParameter(":vin_id_emp", $id_emp);
//        $this->commandAddParameter(":vin_estado", $estado);
//        return $this->commandGetData();  
//    }
    public function getDataComboPerfil() {
        $this->commandPrepare("sp_perfil_getCombo");
        return $this->commandGetData();
    }

    public function cambiarEstado($id_estado, $id_per_ensesion) {
        $this->commandPrepare("sp_perfil_UpdateEstado");
        $this->commandAddParameter(":vin_id", $id_estado);
        $this->commandAddParameter(":vin_id_per_sesion", $id_per_ensesion);
//        $this->commandAddParameter(":vin_estado", $estado);
        return $this->commandGetData();
    }

    public function obtnerPerfilXUsuarioId($usuarioId) {
        $this->commandPrepare("sp_perfil_getByUsuario");
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        return $this->commandGetData();
    }
    
    public function obterTipoMovimiento()
    {
        $this->commandPrepare("sp_movimiento_tipo_obtener");
        return $this->commandGetData();
    }
    
    public function insertarMovimientoTipoPerfil($id_p, $id_opcionMT, $estadoopMT, $usuario)
    {
        $this->commandPrepare("sp_movimiento_tipo_perfil_insertar");
        $this->commandAddParameter(":vin_perfil_id", $id_p);
        $this->commandAddParameter(":vin_movimiento_tipo_id", $id_opcionMT);
        $this->commandAddParameter(":vin_estado", $estadoopMT);
        $this->commandAddParameter(":vin_usuario", $usuario);
        return $this->commandGetData();
    }
    
    public function updateMovimientoTipoPerfil($id, $id_opcionMT, $estadoopMT)
    {
        $this->commandPrepare("sp_movimiento_tipo_perfil_actualizar");
        $this->commandAddParameter(":vin_perfil_id", $id);
        $this->commandAddParameter(":vin_movimiento_tipo_id", $id_opcionMT);
        $this->commandAddParameter(":vin_estado", $estadoopMT);
        return $this->commandGetData();
    }
    
    public function ObtenerEmpresasXUsuarioId($id) {
        $this->commandPrepare("sp_empresa_obtenerXUsuarioId");
        $this->commandAddParameter(":vin_usuario_id", $id);
        return $this->commandGetData();
    }
    
    public function obtenerPadreMenuXEmpresaXusuario($empresaId, $usuarioId) {
        $this->commandPrepare("sp_opcion_obtenerPadreMenuXEmpresaXusuario");
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        return $this->commandGetData();
    }
    
    public function obtenerHijoMenuXEmpresaXusuario($idPadre, $empresaId, $usuarioId) {

        $this->commandPrepare("sp_opcion_obtenerHijoMenuXEmpresaXusuario");
        $this->commandAddParameter(":vin_predecesor_id", $idPadre);
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        return $this->commandGetData();
    }
    
    public function obtenerMovimientoTipo($empresaId, $usuarioId) {
        $this->commandPrepare("sp_movimiento_tipo_obtenerMovimientoTipo");
        $this->commandAddParameter(":vin_usuario_id", $usuarioId);
        $this->commandAddParameter(":vin_empresa_id", $empresaId);
        return $this->commandGetData();
    }

    public function obtenerImagenXUsuario($usuario) {
        $this->commandPrepare("sp_usuario_obtenerImagen");
        $this->commandAddParameter(":vin_usuario", $usuario);
        return $this->commandGetData();
    }
    
    public function eliminarPerfilPersonaClaseXPerfilId($perfilId){
        $this->commandPrepare("sp_perfil_persona_clase_eliminarXPerfilId");
        $this->commandAddParameter(":vin_perfil_id", $perfilId);
        return $this->commandGetData();        
    }
    
    public function guardarPerfilPersonaClaseXPerfilId($claseId, $perfilId, $usuarioCreacion){
        $this->commandPrepare("sp_perfil_persona_clase_guardar");
        $this->commandAddParameter(":vin_persona_clase_id", $claseId);
        $this->commandAddParameter(":vin_perfil_id", $perfilId);
        $this->commandAddParameter(":vin_usuario_creacion", $usuarioCreacion);
        return $this->commandGetData();        
    }
    
    public function obtenerCorreosDeUsuarioXNombrePerfil($descripcion){
        $this->commandPrepare("sp_perfil_ObtenerCorreosXDescripcion");
        $this->commandAddParameter(":vin_descripcion", $descripcion);
        return $this->commandGetData();                
    }
}

