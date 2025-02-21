<?php

require_once __DIR__ . "/../core/ModeloBase.php";

class LibroDetalleTemp extends ModeloBase {
    /**
     * 
     * @return LibroDetalleTemp
     */
    static function create() {
        return parent::create();
    }

    public function guardar($libroTempId, 
                    $cuo,
                    $mcuo,
                    $existenciaTipo,
                    $existenciaCodigo,
                    $existenciaOsce,
                    $fechaEmision,
                    $documentoTipo,
                    $serie,
                    $numero,
                    $operacionTipo,
                    $existenciaDescripcion,
                    $ingresoCantidad,
                    $ingresoCosto,
                    $ingresoTotal,
                    $retiroCantidad,
                    $retiroCosto,
                    $retiroTotal,
                    $saldoCantidad,
                    $saldoCosto,
                    $saldoTotal,
                    $unidadMedida,
                    $operacionEstado) {
//        echo "<br>$libroTempId, 
//                    $cuo,
//                    $existenciaTipo,
//                    $existenciaCodigo,
//                    $existenciaOsce,
//                    $fechaEmision,
//                    $documentoTipo,
//                    $serie,
//                    $numero,
//                    $operacionTipo,
//                    $existenciaDescripcion,
//                    $ingresoCantidad,
//                    $ingresoCosto,
//                    $ingresoTotal,
//                    $retiroCantidad,
//                    $retiroCosto,
//                    $retiroTotal,
//                    $saldoCantidad,
//                    $saldoCosto,
//                    $saldoTotal,
//                    $operacionEstado";
        $this->commandPrepare("sp_libro_detalle_temp_insert");
        $this->commandAddParameter(":vin_libro_temp_id", $libroTempId);
        $this->commandAddParameter(":vin_cuo", $cuo);
        $this->commandAddParameter(":vin_mcuo", $mcuo);
        $this->commandAddParameter(":vin_existencia_tipo", $existenciaTipo);
        $this->commandAddParameter(":vin_existencia_codigo", $existenciaCodigo);
        $this->commandAddParameter(":vin_existencia_osce", $existenciaOsce);
        $this->commandAddParameter(":vin_fecha_emision", $fechaEmision);
        $this->commandAddParameter(":vin_documento_tipo", $documentoTipo);
        $this->commandAddParameter(":vin_serie", $serie);
        $this->commandAddParameter(":vin_numero", $numero);
        $this->commandAddParameter(":vin_operación_tipo", $operacionTipo);
        $this->commandAddParameter(":vin_existencia_descripcion", $existenciaDescripcion);
        $this->commandAddParameter(":vin_ingreso_cantidad", $ingresoCantidad);
        $this->commandAddParameter(":vin_ingreso_costo", $ingresoCosto);
        $this->commandAddParameter(":vin_ingreso_total", $ingresoTotal);
        $this->commandAddParameter(":vin_retiro_cantidad", $retiroCantidad);
        $this->commandAddParameter(":vin_retiro_costo", $retiroCosto);
        $this->commandAddParameter(":vin_retiro_total", $retiroTotal);
        $this->commandAddParameter(":vin_saldo_cantidad", $saldoCantidad);
        $this->commandAddParameter(":vin_saldo_costo", $saldoCosto);
        $this->commandAddParameter(":vin_saldo_total", $saldoTotal);
        $this->commandAddParameter(":vin_unidad_medida", $unidadMedida);
        $this->commandAddParameter(":vin_operacion_estado", $operacionEstado);
        return $this->commandGetData();
    }
    public function listar($libroTempId) {
        $this->commandPrepare("sp_libro_detalle_temp_listar");
        $this->commandAddParameter(":vin_libro_temp_id", $libroTempId);
        return $this->commandGetData();
    }
    
}