<?php

require_once __DIR__ . "/../core/ModeloBase.php";

class ProgramacionPagos extends ModeloBase
{
    /**
     *
     * @return ProgramacionPagos
     */
    static function create()
    {
        return parent::create();
    }

    public function obtenerPPagosXCriterios($tipo_operacionPP, $fechaEmisionInicio, $fechaEmisionFin, $monedaId, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start)
    {
        $this->commandPrepare("sp_ppagos_obtenerXCriterios");
        $this->commandAddParameter(":vin_tipo_operacion", $tipo_operacionPP);
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        return $this->commandGetData();
    }

    public function obtenerCantidadPPagosXCriterios($tipo_operacionPP, $fechaEmisionInicio, $fechaEmisionFin, $monedaId, $columnaOrdenar, $formaOrdenar)
    {
        $this->commandPrepare("sp_ppagos_obtenerXCriterios_contador");
        $this->commandAddParameter(":vin_tipo_operacion", $tipo_operacionPP);
        $this->commandAddParameter(":vin_fecha_emision_desde", $fechaEmisionInicio);
        $this->commandAddParameter(":vin_fecha_emision_hasta", $fechaEmisionFin);
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        return $this->commandGetData();
    }

    //Documentos facturacion_proveedor
    public function obtenerFacturacion_proveedorXCriterios($tipo_operacion, $personaId, $monedaId, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start)
    {
        $this->commandPrepare("sp_facturacion_proveedor_documentosXCriterios");
        $this->commandAddParameter(":vin_tipo_operacion", $tipo_operacion);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        $this->commandAddParameter(":vin_limite", $elementosFiltrados);
        $this->commandAddParameter(":vin_tamanio", $start);
        return $this->commandGetData();
    }

    public function obtenerCantidadfacturacion_proveedorXCriterios($tipo_operacion, $personaId, $monedaId, $columnaOrdenar, $formaOrdenar)
    {
        $this->commandPrepare("sp_facturacion_proveedor_documentosXCriterios_contador");
        $this->commandAddParameter(":vin_tipo_operacion", $tipo_operacion);
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_moneda_id", $monedaId);
        $this->commandAddParameter(":vin_columna_ordenar", $columnaOrdenar);
        $this->commandAddParameter(":vin_forma_ordenar", $formaOrdenar);
        return $this->commandGetData();
    }

    public function obtenerCuentaPrincipalxPersonaId($personaId,$bandera_cuenta, $moneda)
    {
        $this->commandPrepare("sp_obtener_cuentaXpersona_id");
        $this->commandAddParameter(":vin_persona_id", $personaId);
        $this->commandAddParameter(":vin_bandera_cuenta", $bandera_cuenta);
        $this->commandAddParameter(":vin_moneda_id", $moneda);
        return $this->commandGetData();
    }

    public function registrarppagos($tipo, $fecha_programación, $monto_total, $moneda, $usuario)
    {
        $this->commandPrepare("sp_ppagos_registrar");
        $this->commandAddParameter(":vin_tipo_operacion", $tipo);
        $this->commandAddParameter(":vin_fecha_programacion", $fecha_programación);
        $this->commandAddParameter(":vin_monto_total", $monto_total);
        $this->commandAddParameter(":vin_moneda_id", $moneda);
        $this->commandAddParameter(":vin_usuario", $usuario);
        return $this->commandGetData();
    }

    public function registrarppagos_detalle($facturacion_proveedorId, $ppagosId, $cuenta_personaId, $monto_pagado, $tipo_abono, $fecha_pago, $usuario)
    {
        $this->commandPrepare("sp_ppagos_detalle_registrar");
        $this->commandAddParameter(":vin_facturacion_proveedor_id", $facturacion_proveedorId);
        $this->commandAddParameter(":vin_ppagos_id", $ppagosId);
        $this->commandAddParameter(":vin_cuenta_persona_id", $cuenta_personaId);
        $this->commandAddParameter(":vin_monto_pagado", $monto_pagado);
        $this->commandAddParameter(":vin_tipo_abono", $tipo_abono);
        $this->commandAddParameter(":vin_fecha_programacion", $fecha_pago);
        $this->commandAddParameter(":vin_usuario", $usuario);
        return $this->commandGetData();
    }

    public function cambiarBanderaPP_facturacion_proveedor($facturacion_proveedorId, $tipo, $bandera_programacion_pago)
    {
        $this->commandPrepare("sp_facturacion_proveedoCambiarBanderaPP");
        $this->commandAddParameter(":vin_facturacion_proveedor_id", $facturacion_proveedorId);
        $this->commandAddParameter(":vin_tipo_operacion", $tipo);
        $this->commandAddParameter(":vin_bandera_programacion_pago", $bandera_programacion_pago);
        return $this->commandGetData();
    }

    public function obtener_ppagosXId($id)
    {
        $this->commandPrepare("sp_obtener_ppagosXId");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function obtener_ppagos_detalleXId($id, $tipo_operacion)
    {
        $this->commandPrepare("sp_obtener_ppagos_detalleXId");
        $this->commandAddParameter(":vin_ppago_id", $id);
        $this->commandAddParameter(":vin_tipo_operacion", $tipo_operacion);
        return $this->commandGetData();
    }

    public function actualizar_ppagos_Urltxt($id, $url_txt)
    {
        $this->commandPrepare("sp_ppagos_actualizarUrltxt");
        $this->commandAddParameter(":vin_id", $id);
        $this->commandAddParameter(":vin_url_txt", $url_txt);
        return $this->commandGetData();
    }

    public function anularProgramacion($id){
        $this->commandPrepare("sp_ppagos_anular");
        $this->commandAddParameter(":vin_id", $id);
        return $this->commandGetData();
    }

    public function subirAdjunto($programacionId, $nombreGenerado)
    {
        $this->commandPrepare("sp_ppagos_actualizarUrlPdf");
        $this->commandAddParameter(":vin_ppago_id", $programacionId);
        $this->commandAddParameter(":vin_url_archivo", $nombreGenerado);
        return $this->commandGetData();
    }
}
