<?php

//require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/EmpresaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PerfilNegocio.php';

class PerfilControlador extends AlmacenIndexControlador {

    public function getDataGridPerfil() {
        return PerfilNegocio::create()->getDataPerfil();
    }

    public function getMenu() {
        $id_perfil = $this->getParametro("id_perfil");
        return PerfilNegocio::create()->getMenu($id_perfil);
    }
//    
    public function insertPerfil() {
        $nombre = $this->getParametro("descripcion");
        $descripcion = $this->getParametro("comentario");
        $estado = $this->getParametro("estado");
        $dashboard = $this->getParametro("dashboard");
        $email = $this->getParametro("email");
        $monetaria = $this->getParametro("monetaria");
        $usuario = $this->getUsuarioId();
        $pant_principal = $this->getParametro("pant_principal");
        $opcionT = $this->getParametro("opcionT");
        $opcionM = $this->getParametro("opcionM");
        $totalOpcionesMovimiento = $this->getParametro("totalOpcionesMovimiento");
        $totalMovimientoSeleccionado = $this->getParametro("totalMovimientoSeleccionado");
        $personaClase = $this->getParametro("personaClase");
        
        return PerfilNegocio::create()->insertPerfil($nombre, $descripcion, $estado, $dashboard, $email, $monetaria, $usuario,
                    $pant_principal, $opcionT, $opcionM,$totalOpcionesMovimiento,$totalMovimientoSeleccionado,$personaClase);
    }

    public function getPerfil() {
        $usuarioId = $this->getUsuarioId();
        $id_perfil = $this->getParametro("id_perfil");
        return PerfilNegocio::create()->getPerfil($id_perfil,$usuarioId);
    }

    public function updatePerfil() {
        $id_perfil = $this->getParametro("id_perfil");
        $nombre = $this->getParametro("descripcion");
        $descripcion = $this->getParametro("comentario");
        $estado = $this->getParametro("estado");
        $dashboard = $this->getParametro("dashboard");
        $email = $this->getParametro("email");
        $monetaria = $this->getParametro("monetaria");
        $pant_principal = $this->getParametro("pant_principal");
        $usuarioId = $this->getUsuarioId();
        $totalOpcionesMovimiento = $this->getParametro("totalOpcionesMovimiento");
        $totalMovimientoSeleccionado = $this->getParametro("totalMovimientoSeleccionado");
//        $empresa = $this->getParametro("empresa");
//        $combo = $this->getParametro("combo");
        $personaClase = $this->getParametro("personaClase");
        return PerfilNegocio::create()->updatePerfil($id_perfil, $nombre, $descripcion, $estado, $dashboard, $email, $monetaria, 
                    $pant_principal, $usuarioId,$totalOpcionesMovimiento,$totalMovimientoSeleccionado,$personaClase);
    }

    public function deletePerfil() {
        
        $id_perfil = $this->getParametro("id_perfil");
        $usurioId = $this->getUsuarioId();
        $nom = $this->getParametro("nom");
        return PerfilNegocio::create()->deletePerfil($id_perfil, $nom,$usurioId);
    }

    public function insertDetOpcPerfil() {
        $id_per = $this->getParametro("id_per");
        $id_usu = $this->getParametro("id_usu");
        $id_opcion = $this->getParametro("id_opcion");
        $estado = $this->getParametro("estado");
        return PerfilNegocio::create()->insertDetOpcPerfil($id_per, $id_opcion, $id_usu, $estado);
    }

    public function updateDetOpcPerfil() {
        $opcionEdicion = $this->getParametro("opcionEdicion");        
        $id_per = $this->getParametro("id_per");
//        $id_opcion = $this->getParametro("id_opcion");
//        $estado = $this->getParametro("estado");
        $usuario_creacion = $this->getUsuarioId();
        
        foreach ($opcionEdicion as $item) {
            $id_opcion=$item['opcionId'];
            $estado=$item['estado'];
                        
            $res= PerfilNegocio::create()->updateDetOpcPerfil($id_per, $id_opcion, $estado,$usuario_creacion);            
        }
        
        return 1;
    }

    
//    public function obtenerPantallaXToken() {
//        $token = $this->getParametro("token");
//        $id = $this->getUsuarioId();
//        $data= PerfilNegocio::create()->obtenerPantallaXToken($token,$id);
//        return $data;
//    }

    public function obtenerImagenPerfil($id_per, $id_usu) {
        return PerfilNegocio::create()->obtenerImagenPerfil($id_per, $id_usu);
    }

    public function cambiarEstado() {
        $id_estado = $this->getParametro("id_estado");
        return PerfilNegocio::create()->cambiarEstado($id_estado);
    }
    public function obterTipoMovimiento() {
        return PerfilNegocio::create()->obterTipoMovimiento();
    }

    public function obtenerImagenXUsuario($usuario) {
        return PerfilNegocio::create()->obtenerImagenXUsuario($usuario);
    }
    
    public function obtenerPersonaClase(){
        return PerfilNegocio::create()->obtenerPersonaClase();
    }
}
