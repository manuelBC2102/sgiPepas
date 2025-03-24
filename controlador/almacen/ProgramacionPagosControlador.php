<?php

require_once __DIR__ . '/../../modeloNegocio/almacen/ProgramacionPagosNegocio.php';
require_once __DIR__ . '/../core/ControladorBase.php';

class ProgramacionPagosControlador extends ControladorBase
{

    public function obtenerConfiguracionInicialListado()
    {
        $data = ProgramacionPagosNegocio::create()->obtenerConfiguracionInicialListado();
        return $data;
    }

    public function obtenerPPagos()
    {
        $criterios = $this->getParametro("criterios");
        $elementosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $data = ProgramacionPagosNegocio::create()->obtenerPPagosXCriterios($criterios, $elementosFiltrados, $columns, $order, $start);
        $response_cantidad_total = ProgramacionPagosNegocio::create()->obtenerCantidadPPagosXCriterios($criterios, $columns, $order);
        $response_cantidad_total[0]['total'];
        $elementosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];

        return $this->obtenerRespuestaDataTable($data, $elementosFiltrados, $elementosTotales);
    }

    public function obtenerDocumentosPagos()
    {
        $tipo_operacion = $this->getParametro("tipo_operacion");
        $personaId = $this->getParametro("persona_id");
        $monedaId = $this->getParametro("moneda_id");

        $elementosFiltrados = $this->getParametro("length");
        $order = $this->getParametro("order");
        $columns = $this->getParametro("columns");
        $start = $this->getParametro("start");
        $data = ProgramacionPagosNegocio::create()->obtenerFacturacion_proveedorXCriterios($tipo_operacion, $personaId, $monedaId, $elementosFiltrados, $columns, $order, $start);
        $response_cantidad_total = ProgramacionPagosNegocio::create()->obtenerCantidadfacturacion_proveedorXCriterios($tipo_operacion, $personaId, $monedaId, $columns, $order);

        $elementosFiltrados = $response_cantidad_total[0]['total'];
        $elementosTotales = $response_cantidad_total[0]['total'];
        return $this->obtenerRespuestaDataTable($data, $elementosFiltrados, $elementosTotales);
    }

    public function registrarProgramacionPagos()
    {
        $this->setTransaction();
        $filasSeleccionadas = $this->getParametro("filasSeleccionadas");
        $tipo = $this->getParametro("tipo");
        $moneda = $this->getParametro("moneda");
        $fecha_programación = $this->getParametro("fecha_programación");
        $usuario = $this->getUsuarioId();
        return ProgramacionPagosNegocio::create()->registrarProgramacionPagos($filasSeleccionadas, $fecha_programación, $tipo, $moneda, $usuario);
    }

    public function visualizarProgramacion()
    {
        $id = $this->getParametro("id");
        return ProgramacionPagosNegocio::create()->visualizarProgramacion($id);
    }

    public function generarTXTPagos()
    {
        $this->setTransaction();
        $id = $this->getParametro("id");
        return ProgramacionPagosNegocio::create()->generarTXTPagos($id);
    }

    public function generarTXTPagosDetraccion()
    {
        $this->setTransaction();
        $id = $this->getParametro("id");
        $usuario = $this->getUsuarioId();
        return ProgramacionPagosNegocio::create()->generarTXTPagosDetraccion($id, $usuario);
    }

    public function anularProgramacion()
    {
        $id = $this->getParametro("id");
        return ProgramacionPagosNegocio::create()->anularProgramacion($id);
    }

    public function subirAdjunto(){
        $usuario = $this->getUsuarioId();
        $programacionId = $this->getParametro("programacionId");
        $base64archivoAdjunto = $this->getParametro("base64archivoAdjunto");
        return ProgramacionPagosNegocio::create()->subirAdjunto($usuario, $programacionId, $base64archivoAdjunto);
    }
}
