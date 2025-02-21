<?php

require_once __DIR__ . '/../../modelo/almacen/Organizador.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';

class OrganizadorNegocio extends ModeloNegocioBase {
    
    public function getDataOrganizadorTipo($id_bandera) {
        $data = Organizador::create()->getDataOrganizadorTipo();
        $tamanio = count($data);
        for ($i = 0; $i < $tamanio; $i++) {
            if ($data[$i]['estado'] == 1) {
                $data[$i]['icono'] = "ion-checkmark-circled";
                $data[$i]['color'] = "#5cb85c";
            } else {
                $data[$i]['icono'] = "ion-flash-off";
                $data[$i]['color'] = "#cb2a2a";
            }
            if (id_usuario != null) {
                $data[$i]['id_bandera'] = $id_bandera;
            }
        }
        return $data;
    }

    public function insertOrganizadorTipo($descripcion, $codigo, $comentario, $estado, $usu_creacion, $empresa, $combo) {
        $response = Organizador::create()->insertOrganizadorTipo($descripcion, $codigo, $comentario, $estado, $usu_creacion);
        if ($response[0]["vout_exito"] == 0) {
            return $response;
        } else {
            return $response;
        }
    }

    public function getOrganizadorTipo($id) {
        return Organizador::create()->getOrganizadorTipo($id);
    }

    public function updateOrganizadorTipo($id_alm_tipo, $descripcion, $codigo, $comentario, $estado, $empresa, $combo) {
        $response = Organizador::create()->updateOrganizadorTipo($id_alm_tipo, $descripcion, $codigo, $comentario, $estado);
        if ($response[0]["vout_exito"] == 0) {
            return $response;
        } else {
            return $response;
        }
    }

    public function deleteOrganizadorTipo($id_alm_tipo, $nom) {
        $response = Organizador::create()->deleteOrganizadorTipo($id_alm_tipo);
        $response[0]['nombre'] = $nom;
        return $response;
    }

    public function getDataComboOrganizadorTipo() {
        return Organizador::create()->getDataComboOrganizadorTipo();
    }

    public function cambiarTipoEstado($id_estado) {
        $data = Organizador::create()->cambiarTipoEstado($id_estado);
        $tamanio = count($data);
        for ($i = 0; $i < $tamanio; $i++) {
            if ($data[$i]['estado_nuevo'] == 1) {
                $data[$i]['icono'] = "ion-checkmark-circled";
                $data[$i]['color'] = "#5cb85c";
            } else {
                $data[$i]['icono'] = "ion-flash-off";
                $data[$i]['color'] = "#cb2a2a";
            }
        }
        return $data;
    }

    //////////////////////////////////////
    //organizadores
    /////////////////////////////////////
    public function getDataOrganizador($id_bandera) {
        $data = Organizador::create()->getDataOrganizador();
        $tamanio = count($data);
        for ($i = 0; $i < $tamanio; $i++) {
            if ($data[$i]['estado'] == 1) {
                $data[$i]['icono'] = "ion-checkmark-circled";
                $data[$i]['color'] = "#5cb85c";
            } else {
                $data[$i]['icono'] = "ion-flash-off";
                $data[$i]['color'] = "#cb2a2a";
            }
            if ($id_bandera != null) {
                $data[$i]['id_bandera'] = $id_bandera;
            }
        }
        return $data;
    }

    public function insertOrganizador($descripcion, $codigo, $padre,$tipo, $estado, $usu_creacion, $comentario, $empresa) {

        $response = Organizador::create()->insertOrganizador($descripcion, $codigo,$padre, $tipo, $estado, $usu_creacion, $comentario);
        if ($response[0]["vout_exito"] == 0) {
            return $response;
        } else {
            $id_p = $response[0]['id'];
            $response_empresa = Empresa::create()->getDataEmpresaTotal();
            for ($i = 0; $i < count($response_empresa); $i++) {
                
                $estadoep = 0;
                
                $id_emp = $response_empresa[$i]['id'];
                
                for ($j = 0; $j < count($empresa); $j++) {
                    if ($id_emp == $empresa[$j]) {
                        $estadoep = 1;
                    }
                }
                Organizador::create()->saveOrganizadorEmpresa($id_p, $id_emp, $estadoep);
            }
            return $response;
        }
    }

    public function getOrganizador($id) {
        return Organizador::create()->getOrganizador($id);
    }

    public function updateOrganizador($id_alm, $descripcion, $codigo, $padre,$tipo, $estado, $comentario, $empresa, $combo) {
        $response = Organizador::create()->updateOrganizador($id_alm, $descripcion, $codigo, $padre,$tipo, $estado, $comentario);
        if ($response[0]["vout_exito"] == 0) {
            return $response;
        } else {
            $responseEmpresa = Empresa::create()->getDataEmpresaTotal();
            for ($i = 0; $i < count($responseEmpresa); $i++) {
                $estadop = 0;
                $id_emp = $responseEmpresa[$i]['id'];
                for ($j = 0; $j < count($empresa); $j++) {
                    if ($id_emp == $empresa[$j]) {
                        $estadop = 1;
                    }
                }
                Organizador::create()->saveOrganizadorEmpresa($id_alm, $id_emp, $estadop);
            }
            return $response;
        }
    }

    public function deleteOrganizador($id_alm, $nom) {
        $response = Organizador::create()->deleteOrganizador($id_alm);
        $response[0]['nombre'] = $nom;
        return $response;
    }

    public function cambiarEstado($id_estado) {
        $data = Organizador::create()->cambiarEstado($id_estado);
        if($data[0]['vout_exito']==0)
        {
            throw new WarningException($data[0]["vout_mensaje"]);
        }
        $tamanio = count($data);
        for ($i = 0; $i < $tamanio; $i++) {
            if ($data[$i]['estado_nuevo'] == 1) {
                $data[$i]['icono'] = "ion-checkmark-circled";
                $data[$i]['color'] = "#5cb85c";
            } else {
                $data[$i]['icono'] = "ion-flash-off";
                $data[$i]['color'] = "#cb2a2a";
            }
        }
        return $data;
    }
    
    public function obtenerOrganizadorActivo($id)
    {
        return Organizador::create()->obtenerOrganizadorActivo($id);
    }
    
    public function organizadorEsPadre($id,$nombre) {
        
        $response = Organizador::create()->organizadorEsPadre($id);
        $response[0]['nombre'] = $nombre;
        $response[0]['id'] = $id;
        return $response;
    }
    
    public function obtenerXMovimientoTipo($movimientoTipoId){
        return Organizador::create()->obtenerXMovimientoTipo($movimientoTipoId);
    }
    
    public function obtenerXMovimientoTipo2($movimientoTipoId,$organizadoresIds,$comodinMostrar=null){
        return Organizador::create()->obtenerXMovimientoTipo2($movimientoTipoId,$organizadoresIds,$comodinMostrar);
    }
    
    public function obtenerOrganizadorActivoXEmpresa($idEmpresa){
        return Organizador::create()->obtenerOrganizadorActivoXEmpresa($idEmpresa);
    }
    
    public function obtenerOrganizadorActivoXDescripcion($organizadorDescripcion){
        return Organizador::create()->obtenerOrganizadorActivoXDescripcion($organizadorDescripcion);
    }
    
    public function obtenerEmpresaXOrganizadorId($organizadorId){
        return Organizador::create()->obtenerEmpresaXOrganizadorId($organizadorId);        
    }
}
