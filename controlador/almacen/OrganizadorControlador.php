<?php

require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/OrganizadorNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/EmpresaNegocio.php';

class OrganizadorControlador extends AlmacenIndexControlador {

    public function getDataGridOrganizadorTipo() {
        return OrganizadorNegocio::create()->getDataOrganizadorTipo();
    }

    public function insertOrganizadorTipo() {
        $descripcion = $this->getParametro("descripcion");
        $codigo = $this->getParametro("codigo");
        $comentario = $this->getParametro("comentario");
        $estado = $this->getParametro("estado");
        $usu_creacion =$this->getUsuarioId();
        return OrganizadorNegocio::create()->insertOrganizadorTipo($descripcion, $codigo, $comentario, $estado, $usu_creacion);
    }

    public function getOrganizadorTipo() {
        $id_organizador_tipo = $this->getParametro("id_organizador_tipo");
        return OrganizadorNegocio::create()->getOrganizadorTipo($id_organizador_tipo);
    }

    public function updateOrganizadorTipo() {
        $id_alm_tipo = $usu_nombre = $this->getParametro("id_alm_tipo");
        $descripcion = $this->getParametro("descripcion");
        $codigo = $this->getParametro("codigo");
        $comentario = $this->getParametro("comentario");
        $estado = $this->getParametro("estado");
        return OrganizadorNegocio::create()->updateOrganizadorTipo($id_alm_tipo, $descripcion, $codigo, $comentario, $estado);
    }

    public function deleteOrganizadorTipo() {
        $id_alm_tipo = $this->getParametro("id_alm_tipo");
        $nom = $this->getParametro("nom");
        return OrganizadorNegocio::create()->deleteOrganizadorTipo($id_alm_tipo, $nom);
    }

    public function cambiarTipoEstado() {
        $id_estado = $this->getParametro("id_estado");
        return OrganizadorNegocio::create()->cambiarTipoEstado($id_estado);
    }
    
    /*
     * aca empiezo lo de organizador
     */

    public function getDataGridOrganizador() {
        return OrganizadorNegocio::create()->getDataOrganizador();
    }

    public function insertOrganizador() {
        $descripcion = $this->getParametro("descripcion");
        $codigo = $this->getParametro("codigo");
        $tipo = $this->getParametro("tipo");
        $estado = $this->getParametro("estado");
        $usu_creacion = $this->getUsuarioId();
        $comentario = $this->getParametro("comentario");
        $empresa = $this->getParametro("empresa");
        $padre = $this->getParametro("padre");
        return OrganizadorNegocio::create()->insertOrganizador($descripcion, $codigo,$padre, $tipo, $estado, $usu_creacion, $comentario,$empresa);
    }

    public function getOrganizador() {
        $id_organizador = $this->getParametro("id_organizador");
        return OrganizadorNegocio::create()->getOrganizador($id_organizador);
    }

    public function updateOrganizador() {
        $id_alm = $usu_nombre = $this->getParametro("id_alm");
        $descripcion = $this->getParametro("descripcion");
        $codigo = $this->getParametro("codigo");
        $tipo = $this->getParametro("tipo");
        $estado = $this->getParametro("estado");
        $comentario = $this->getParametro("comentario");
        $empresa = $this->getParametro("empresa");
        $padre = $this->getParametro("padre");
        return OrganizadorNegocio::create()->updateOrganizador($id_alm, $descripcion, $codigo, $padre,$tipo, $estado, $comentario,$empresa);
    }

    public function deleteOrganizador() {
        $id_alm = $this->getParametro("id_alm");
        $nom = $this->getParametro("nom");
        return OrganizadorNegocio::create()->deleteOrganizador($id_alm, $nom);
    }

    public function getAllOrganizadorTipo() {
        return OrganizadorNegocio::create()->getDataComboOrganizadorTipo();
    }

    public function cambiarEstado() {
        $id_estado = $this->getParametro("id_estado");
        return OrganizadorNegocio::create()->cambiarEstado($id_estado);
    }
    
    
    public function obtenerOrganizadorActivo()
    {
        $id = $this->getParametro("id");
        return OrganizadorNegocio::create()->obtenerOrganizadorActivo($id);
    }

    public function getAllEmpresa() {
        $usuarioId = $this->getUsuarioId();
        return EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    }
    
    public function organizadorEsPadre() {
        $id = $this->getParametro("id");
        $nombre = $this->getParametro("nombre");
        return OrganizadorNegocio::create()->organizadorEsPadre($id,$nombre);
    }

}
