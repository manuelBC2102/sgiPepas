<?php

require_once __DIR__ . '/../../modelo/almacen/ProgramacionPagos.php';
require_once __DIR__ . '/../../modelo/almacen/MovimientoBien.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/commons/TablaNegocio.php';
require_once __DIR__ . '/PersonaNegocio.php';
require_once __DIR__ . '/DocumentoTipoNegocio.php';
require_once __DIR__ . '/DocumentoNegocio.php';
require_once __DIR__ . '/MonedaNegocio.php';
require_once __DIR__ . '/MovimientoTipoNegocio.php';
require_once __DIR__ . '/OrganizadorNegocio.php';
require_once __DIR__ . '/EmailPlantillaNegocio.php';
require_once __DIR__ . '/EmailEnvioNegocio.php';

class ProgramacionPagosNegocio extends ModeloNegocioBase
{
    /**
     *
     * @return ProgramacionPagosNegocio
     */
    static function create()
    {
        return parent::create();
    }

    public function obtenerConfiguracionInicialListado()
    {
        $respuesta = new stdClass();
        $respuesta->persona_activa = PersonaNegocio::create()->obtenerActivas();
        $respuesta->moneda = MonedaNegocio::create()->obtenerComboMoneda();
        $respuesta->personasMayorDocumentos = PersonaNegocio::create()->obtenerPersonasMayorDocumentosPPagoXTipos('(4)');

        return $respuesta;
    }

    public function obtenerPPagosXCriterios($criterios, $elementosFiltrados, $columns, $order, $start)
    {
        $tipo_operacionPP = $criterios['tipo_operacionPP'];
        $monedaId = $criterios['monedaId'];
        $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
        $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return ProgramacionPagos::create()->obtenerPPagosXCriterios($tipo_operacionPP, $fechaEmisionInicio, $fechaEmisionFin, $monedaId, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
    }

    public function obtenerCantidadPPagosXCriterios($criterios, $columns, $order)
    {
        $tipo_operacionPP = $criterios['tipo_operacionPP'];
        $monedaId = $criterios['monedaId'];
        $fechaEmisionInicio = $this->formatearFechaBD($criterios['fechaEmision']['inicio']);
        $fechaEmisionFin = $this->formatearFechaBD($criterios['fechaEmision']['fin']);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return ProgramacionPagos::create()->obtenerCantidadPPagosXCriterios($tipo_operacionPP, $fechaEmisionInicio, $fechaEmisionFin, $monedaId, $columnaOrdenar, $formaOrdenar);
    }

    private function formatearFechaBD($cadena)
    {
        if (!ObjectUtil::isEmpty($cadena)) {
            return DateUtil::formatearCadenaACadenaBD($cadena);
        }
        return "";
    }
    //Documentos
    public function obtenerFacturacion_proveedorXCriterios($tipo_operacion, $personaId, $monedaId, $elementosFiltrados, $columns, $order, $start)
    {
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return ProgramacionPagos::create()->obtenerFacturacion_proveedorXCriterios($tipo_operacion, $personaId, $monedaId, $columnaOrdenar, $formaOrdenar, $elementosFiltrados, $start);
    }

    public function obtenerCantidadfacturacion_proveedorXCriterios($tipo_operacion, $personaId, $monedaId, $columns, $order)
    {
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return ProgramacionPagos::create()->obtenerCantidadfacturacion_proveedorXCriterios($tipo_operacion, $personaId, $monedaId, $columnaOrdenar, $formaOrdenar);
    }


    public function registrarProgramacionPagos($filasSeleccionadas, $fecha_programacion, $tipo, $moneda, $usuario)
    {
        $sumaMontos = array_reduce($filasSeleccionadas, function ($acumulador, $seleccion) {
            return $acumulador + $seleccion['total'];
        }, 0);

        if (ObjectUtil::isEmpty($filasSeleccionadas)) {
            throw new WarningException("No se a seleccionado documentos para su programación");
        }

        //validar cuentas
        $cuentas_usuario = '';
        $mensaje = '';
        $tipo_mensaje = '';
        $bandera_cuentas = true;
        foreach ($filasSeleccionadas as $row) {
            $cuenta_persona = ProgramacionPagos::create()->obtenerCuentaPrincipalxPersonaId($row['persona_proveedor_id'], $tipo, $moneda);
            if (ObjectUtil::isEmpty($cuenta_persona)) {
                $cuentas_usuario .=  '' . ($row['persona']) . '<br>';
                $bandera_cuentas = false;
                $cuentas_message = "Proveedores";
            }
        }


        if ($bandera_cuentas) {
            $respuestapagos = ProgramacionPagos::create()->registrarppagos($tipo,  $this->formatearFechaBD($fecha_programacion), $sumaMontos, $moneda, $usuario);
            if (ObjectUtil::isEmpty($respuestapagos)) {
                throw new WarningException("Error al registrar programacion de Pagos");
            }

            foreach ($filasSeleccionadas as $row) {
                $tipo_abono = null;
                if ($tipo == 1) {
                    $cuenta_persona = ProgramacionPagos::create()->obtenerCuentaPrincipalxPersonaId($row['persona_proveedor_id'], $tipo, $moneda);
                    $tipo_abono = $cuenta_persona[0]['tipo_transferencia'];
                    $respuestappagos_detalle = ProgramacionPagos::create()->registrarppagos_detalle($row['facturacion_proveedor_id'], $respuestapagos[0]['vout_id'], $cuenta_persona[0]['id'], $row['total'], $tipo_abono, $this->formatearFechaBD($fecha_programacion), $usuario);
                    if (ObjectUtil::isEmpty($respuestappagos_detalle)) {
                        throw new WarningException("Error al registrar detalle programacion de Pagos");
                    }
                    $respuestappagos_detalle = ProgramacionPagos::create()->cambiarBanderaPP_facturacion_proveedor($row['facturacion_proveedor_id'], $tipo, 1);
                } else {
                    $cuenta_persona = ProgramacionPagos::create()->obtenerCuentaPrincipalxPersonaId($row['persona_proveedor_id'], $tipo, $moneda);
                    $respuestappagos_detalle = ProgramacionPagos::create()->registrarppagos_detalle($row['facturacion_proveedor_id'], $respuestapagos[0]['vout_id'], $cuenta_persona[0]['id'], $row['total'], $tipo_abono, $this->formatearFechaBD($fecha_programacion), $usuario);
                    if (ObjectUtil::isEmpty($respuestappagos_detalle)) {
                        throw new WarningException("Error al registrar detalle programacion de Pagos");
                    }
                    $respuestappagos_detalle = ProgramacionPagos::create()->cambiarBanderaPP_facturacion_proveedor($row['facturacion_proveedor_id'], $tipo, 1);
                }
            }
            $mensaje = 'Operacion registrada Exitosamente';
            $tipo_mensaje = 1;
        } else {
            $mensaje = 'Los siguientes ' . $cuentas_message . ' no tienen registros de cuentas bancarias: <br>' . $cuentas_usuario;
            $tipo_mensaje = 0;
        }

        $respuesta = new stdClass();
        $respuesta->tipo_mensaje = $tipo_mensaje;
        $respuesta->mensaje = $mensaje;
        return $respuesta;
    }

    public function visualizarProgramacion($id)
    {
        $ppagos = ProgramacionPagos::create()->obtener_ppagosXId($id);
        return ProgramacionPagos::create()->obtener_ppagos_detalleXId($id, $ppagos[0]['tipo_operacion']);
    }

    public function generarTXTPagos($id)
    {
        $ppagos = ProgramacionPagos::create()->obtener_ppagosXId($id);
        $ppagos_detalle = ProgramacionPagos::create()->obtener_ppagos_detalleXId($id, $ppagos[0]['tipo_operacion']);
        $tipoDocumento = '11'; //para proveedores
        if (!ObjectUtil::isEmpty($ppagos[0]['url_txt'])) {
            $archivo_txt = Configuraciones::url_base() . 'vistas/com/programacionPagos/txt/' . $ppagos[0]['url_txt'] . ".txt";
            $nombreArchivo = $ppagos[0]['url_txt'];
        } else {
            $sumaMontos = array_reduce($ppagos_detalle, function ($acumulador, $seleccion) {
                return $acumulador + $seleccion['monto_pagado'];
            }, 0);
            $nombreArchivo = str_replace(" 00:00:00", "", str_replace("-", "", $ppagos[0]['fecha_programacion'])) .  $tipoDocumento . "-" . date('YmdHis');
            $archivo = __DIR__ . '/../../vistas/com/programacionPagos/txt/' . $nombreArchivo;
            $archivo_txt = Configuraciones::url_base() . 'vistas/com/programacionPagos/txt/' . $nombreArchivo . ".txt";


            $tipoRegistro = Util::completarCadena('0103', 40, ' ', 'D');
            $fecha = Util::completarCadena(date('YmdHis'), 23, ' ', 'D');
            $totalRecords = count($ppagos_detalle);
            $cantidad_reg = Util::completarCadena($totalRecords, 6, '0', 'I');
            $monto_total = Util::completarCadena(Util::eliminarCaracterDeCadena(number_format($sumaMontos, 2, '.', ''), '.'), 15, '0', 'I'); //revisar
            $complemento = Util::completarCadena('MC001', 20, '0', 'I');

            $file = fopen($archivo . ".txt", "w");
            fwrite($file, $tipoRegistro . '');
            fwrite($file, $fecha . '');
            fwrite($file, $cantidad_reg . $monto_total . $complemento . PHP_EOL);


            //detalle
            foreach ($ppagos_detalle as $row) {
                //02 ruc, 01 dni
                $tipo_documento = Util::completarCadena(('02' . $row['tipo_doc'] . ($row['ruc'])), 22, ' ', 'D');
                $documento_pago = Util::completarCadena(('F' . $row['serie'] . $row['correlativo']), 29, ' ', 'D');
                //$fecha_vencimiento = str_replace("-", "", $row['fecha_vencimiento']);

                $monto_abonado = Util::completarCadena(Util::eliminarCaracterDeCadena(number_format($row['monto_pagado'], 2, '.', ''), '.'), 15, '0', 'I');
                $moneda_abono_monto_abono = Util::completarCadena(($row['moneda_abono'] . $monto_abonado), 18, ' ', 'D');

                $identificador = 'P';
                $identificador = substr(strval($row['ruc']), 0, 2) == "20" ? 'C' : 'P';

                if ($row['tipo_abono'] == "99") {
                    $tipo_abono = Util::completarCadena($row['tipo_abono'], 10, ' ', 'D');
                    $numero_cuenta_proveedor = Util::completarCadena(($row['cci'] . $identificador . $row['tipo_doc'] . ($row['ruc'])), 38, ' ', 'D'); //revisar
                    $nombreprovedor = Util::completarCadena(substr(Util::normaliza($row['persona_nombre']), 0, 60), 263, ' ', 'D');
                } else { //09
                    $tipo_abono = '09';
                    $tipo_abono = Util::completarCadena(($tipo_abono . $row['tipo_cuenta'] . $row['moneda_abono'] . $row['numero_cuenta']), 30, ' ', 'D');
                    $numero_cuenta_proveedor = Util::completarCadena(($identificador . $row['tipo_doc']) . ($row['ruc']), 18, ' ', 'D'); //revisar
                    $nombreprovedor = Util::completarCadena(substr(Util::normaliza($row['persona_nombre']), 0, 60), 263, ' ', 'D');
                }

                fwrite($file, $tipo_documento . '');
                fwrite($file, $documento_pago . '');
                fwrite($file, $moneda_abono_monto_abono . '');
                fwrite($file, $tipo_abono . '');
                fwrite($file, $numero_cuenta_proveedor . '');
                fwrite($file, $nombreprovedor . PHP_EOL);
            }

            ProgramacionPagos::create()->actualizar_ppagos_Urltxt($id, $nombreArchivo);

            fclose($file);
        }
        $respuesta = new stdClass();
        $respuesta->archivo = $archivo_txt;
        $respuesta->nombreArchivo = $nombreArchivo;
        return $respuesta;
    }

    public function generarTXTPagosDetraccion($id, $usuario)
    {
        $empresa = PerfilNegocio::create()->ObtenerEmpresasXUsuarioId($usuario);
        $ppagos = ProgramacionPagos::create()->obtener_ppagosXId($id);
        $ppagos_detalle = ProgramacionPagos::create()->obtener_ppagos_detalleXId($id, $ppagos[0]['tipo_operacion']);
        $nlote = date('ymd', strtotime($ppagos[0]['fecha_creacion']));

        if (!ObjectUtil::isEmpty($ppagos[0]['url_txt'])) {
            $archivo_txt = Configuraciones::url_base() . 'vistas/com/programacionPagos/txt/' . $ppagos[0]['url_txt'] . ".txt";
            $nombreArchivo = $ppagos[0]['url_txt'];
        } else {
            $nombreArchivo = "D" . $empresa[0]['ruc']  . $nlote; //revisar nombre de archivo
            $archivo = __DIR__ . '/../../vistas/com/programacionPagos/txt/' . $nombreArchivo;
            $archivo_txt = Configuraciones::url_base() . 'vistas/com/programacionPagos/txt/' . $nombreArchivo . ".txt";

            $ruc_razon = Util::completarCadena('*' . $empresa[0]['ruc'] . $empresa[0]['razon_social'], 47, ' ', 'D');


            $sumaMontos = array_reduce($ppagos_detalle, function ($acumulador, $seleccion) {
                return $acumulador + $seleccion['monto_pagado'];
            }, 0);

            $monto_total = Util::completarCadena(Util::eliminarCaracterDeCadena(number_format($sumaMontos, 2, '.', ''), '.'), 15, '0', 'I'); //revisar
            $file = fopen($archivo . ".txt", "w");
            fwrite($file, $ruc_razon . '');
            fwrite($file, $nlote . $monto_total . PHP_EOL);

            foreach ($ppagos_detalle as $row) {
                $ruc = Util::completarCadena(('6' . $row['ruc']), 47, ' ', 'D');
                $tipobienservicio_cuentadetraccion = (Util::completarCadena('27', '12', '0', 'I')) . $row['numero_cuenta'];
                $importe = Util::completarCadena(Util::eliminarCaracterDeCadena(number_format($row['monto_pagado'], 2, '.', ''), '.'), 15, '0', 'I');
                $tipooperacion_periodo = '01' . '' . date('Ym', strtotime($row['fecha_creacion']));
                $tipo_serienumero = '01' . $row['serie'] . (Util::completarCadena($row['correlativo'], '8', '0', 'I'));

                fwrite($file, $ruc . '');
                fwrite($file, $tipobienservicio_cuentadetraccion . $importe . $tipooperacion_periodo . $tipo_serienumero . PHP_EOL);
            }
            fclose($file);
        }
        ProgramacionPagos::create()->actualizar_ppagos_Urltxt($id, $nombreArchivo);

        $respuesta = new stdClass();
        $respuesta->archivo = $archivo_txt;
        $respuesta->nombreArchivo = $nombreArchivo;
        return $respuesta;
    }

    public function anularProgramacion($id)
    {
        $ppagos = ProgramacionPagos::create()->obtener_ppagosXId($id);
        $ppagos_detalle = ProgramacionPagos::create()->obtener_ppagos_detalleXId($id, $ppagos[0]['tipo_operacion']);
        ProgramacionPagos::create()->anularProgramacion($id);

        foreach ($ppagos_detalle as $row) {
            $respuestappagos_detalle = ProgramacionPagos::create()->cambiarBanderaPP_facturacion_proveedor($row['facturacion_proveedor_id'], $ppagos[0]['tipo_operacion'], null);
        }
        $respuesta = new stdClass();
        $respuesta->tipo_mensaje = 1;
        $respuesta->mensaje = "Anulación de programación Exitosa!";
        return $respuesta;
    }

    public function subirAdjunto($usuario, $programacionId, $base64archivoAdjunto)
    {
        $decode = Util::base64ToImage($base64archivoAdjunto);
        // Verificar los "números mágicos" de varios tipos de archivos
        if (substr($decode, 0, 3) == "\xFF\xD8\xFF") {
            // JPEG
            $ext = 'jpg';
        } elseif (substr($decode, 0, 4) == "\x89PNG") {
            // PNG
            $ext = 'png';
        } elseif (substr($decode, 0, 4) == "%PDF") {
            // PDF
            $ext = 'pdf';
        } elseif (substr($decode, 0, 4) == "GIF8") {
            // GIF
            $ext = 'gif';
        } elseif (substr($decode, 0, 2) == "PK") {
            // ZIP / DOCX / XLSX
            $ext = 'zip';
        }
        $hoy = date("YmdHis");
        $nombreGenerado = $programacionId . $hoy . $usuario .".". $ext;
        if($ext == "pdf"){
            $url = __DIR__ . '/../../util/uploads/documentoAdjunto/' . $nombreGenerado;
        }else{
            $url = __DIR__ . '/../../util/uploads/imagenAdjunto/' . $nombreGenerado;
        }

        file_put_contents($url, $decode);

        $ppagos = ProgramacionPagos::create()->subirAdjunto($programacionId, $nombreGenerado);

        $respuesta = new stdClass();
        $respuesta->tipo_mensaje = 1;
        $respuesta->mensaje = "Operación Exitosa!";
        return $respuesta;
    }
}
