<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../modelo/almacen/Bien.php';
require_once __DIR__ . '/../../modelo/almacen/BienTipo.php';
require_once __DIR__ . '/../../modelo/almacen/UnidadMedida.php';
require_once __DIR__ . '/../../modelo/almacen/Unidad.php';
require_once __DIR__ . '/../../modelo/almacen/MovimientoBien.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../util/PHPExcel/PHPExcel.php';
require_once __DIR__ . '/OrganizadorNegocio.php';
require_once __DIR__ . '/PersonaNegocio.php';
require_once __DIR__ . '/OrganizadorNegocio.php';
require_once __DIR__ . '/UnidadNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/SunatTablaNegocio.php';
require_once __DIR__ . '/EmailPlantillaNegocio.php';
require_once __DIR__ . '/EmailEnvioNegocio.php';

//require_once __DIR__ . '/../../modeloNegocio/almacen/barcode.inc.php';

class BienNegocio extends ModeloNegocioBase {

    const PRECIO_COMPRA = 1;
    const PRECIO_VENTA = 2;
    const PARAMETRO_DESCUENTO = 0.36; // En realidad el  descuento es de 64 %
    const PARAMETRO_IGV = 1.18;

    /**
     * 
     * @return BienNegocio
     */
    static function create() {
        return parent::create();
    }

    public function getAllBienTipo() {
        return Bien::create()->getAllBienTipo();
    }

    public function getDataBienTipo() {
        $data = Bien::create()->getDataBienTipo();
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

    public function insertBienTipo($codigo, $descripcion, $comentario, $estado, $tipo, $usuarioCreacion, $bienTipoPadreId, $codigoSunatId, $codigoSunatId2) {

        $response = Bien::create()->insertBienTipo($codigo, $descripcion, $comentario, $estado, $tipo, $usuarioCreacion, $bienTipoPadreId, $codigoSunatId, $codigoSunatId2);
        return $response;
    }

    public function getBienTipo($id) {
        return Bien::create()->getBienTipo($id);
    }

    public function updateBienTipo($id_bien_tipo, $descripcion, $codigo, $comentario, $estado, $tipo, $bienTipoPadreId, $codigoSunatId, $codigoSunatId2) {
        $response = Bien::create()->updateBienTipo($id_bien_tipo, $descripcion, $codigo, $comentario, $estado, $tipo, $bienTipoPadreId, $codigoSunatId, $codigoSunatId2);

        return $response;
    }

    public function deleteBienTipo($id_bien_tipo) {
        $response = Bien::create()->deleteBienTipo($id_bien_tipo);
        return $response;
    }

    public function cambiarTipoEstado($id_estado) {
        $data = Bien::create()->cambiarTipoEstado($id_estado);
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
    //bienes
    /////////////////////////////////////
    public function getDataBien($usuarioId, $empresaId) {

        return Bien::create()->getDataBien($usuarioId, $empresaId);
    }

    public function insertarBienPersona($bienId, $proveedorId, $prioridad, $usu_creacion) {
        $res = Bien::create()->insertarBienPersona($bienId, $proveedorId, $prioridad, $usu_creacion);

        return $res;
    }

    public function insertBien($descripcion, $codigo, $tipo, $cant_minima, $estado, $usu_creacion, $comentario, $empresa, $file, $unidad_tipo, $agregado_precio_venta, $agregado_precio_venta_tipo, $unidad_control_id, $listaProveedorId, $listaPrioridad, $codigoFabricante, $precioCompra, $marca, $codigoBarras, $listaPrecioDetalle, $maquinaria, $codigoSunatId, $objCamposBien) {
        if ($tipo == -1) {
            $unidad_tipo[0] = -1;
        }

        //si existe marca obtengo el id de lo contrario inserto la marca.
        $marca = trim($marca);
        if ($marca != '') {
            $resMarca = Bien::create()->obtenerMarcaXDescripcion($marca);

            if (ObjectUtil::isEmpty($resMarca)) {
                $resMarca = Bien::create()->insertarMarca($marca, $usu_creacion);
            }

            $marcaId = $resMarca[0]['id'];
        } else {
            $marcaId = null;
        }

        //si existe maquinaria obtengo el id de lo contrario inserto la maquinaria.
        $maquinaria = trim($maquinaria);
        if ($maquinaria != '') {
            $resMaquinaria = Bien::create()->obtenerMaquinariaXDescripcion($maquinaria);

            if (ObjectUtil::isEmpty($resMaquinaria)) {
                $resMaquinaria = Bien::create()->insertarMaquinaria($maquinaria, $usu_creacion);
            }

            $maquinariaId = $resMaquinaria[0]['id'];
        } else {
            $maquinariaId = null;
        }

        $response = Bien::create()->insertBien($descripcion, $codigo, $tipo, $estado, $usu_creacion, $comentario, $agregado_precio_venta, $agregado_precio_venta_tipo, $codigoFabricante, $marcaId, $codigoBarras, $maquinariaId, $codigoSunatId, $objCamposBien['cuentaContableId'], $objCamposBien['costoInical'], $objCamposBien['codigoCuenta'], $objCamposBien['codigoInternacional'], $objCamposBien['modelo'], $objCamposBien['serieNumero'], $objCamposBien['depreciacionMetodo'], $objCamposBien['depreciacionPorcentaje'], $objCamposBien['fechaAdquisicion'], $objCamposBien['fechaInicioUso'], $objCamposBien['cuentaContableGasto'], $objCamposBien['cuentaContableDepreciacion'], $objCamposBien['cuentaContableVenta']);

        if ($response[0]['vout_exito'] == 0) {
            throw new WarningException($response[0]['vout_mensaje']);
        }
        $bienId = $response[0]['id'];

        foreach ($objCamposBien['distribucionContable'] as $item) {
            $respuestaGuardaCentroCostoBien = self::guardarDistribucionXBienId($bienId, $item['centro_costo_id'], $item['porcentaje'], $usu_creacion);
            if ($respuestaGuardaCentroCostoBien[0]['vout_exito'] == 0) {
                throw new WarningException($response[0]['vout_mensaje']);
            }
        }

        foreach ($listaProveedorId as $indice => $proveedor) {
            $proveedorId = $proveedor;
            $prioridad = $listaPrioridad[$indice];

            $r = $this->insertarBienPersona($bienId, $proveedorId, $prioridad, $usu_creacion);
        }

        // guardar bien precio detalle de lista
        foreach ($listaPrecioDetalle as $indice => $item) {
            $bienPrecioId = $item[8];
            $monedaId = $item[4];
            $precioTipoId = $item[0];
            $unidadMedidaId = $item[2];
            $precio = $item[6];
            $descuento = $item[7];
            $incluyeIGV = $item[9];
            $checkIGV = $item[10];

            $res = Bien::create()->guardarBienPrecioDetalle($bienPrecioId, $bienId, $monedaId, $precioTipoId, $unidadMedidaId, $precio, $descuento, $usu_creacion, $incluyeIGV, $checkIGV);

            if ($res[0]['vout_exito'] == 0) {
                throw new WarningException("Error al guardar el precio del bien. " . $res[0]['vout_mensaje']);
            }
        }

//        //guardar precio en bien_precio (VENTA)
//        $respuesta = Bien::create()->guardarBienPrecio($bienId, $agregado_precio_venta, self::PRECIO_VENTA, $usu_creacion);
//
//        if ($respuesta[0]['vout_exito'] == 0) {
//            throw new WarningException("Error al guardar el precio de venta del bien.");
//        }
//        
//        //guardar precio en bien_precio (COMPRA)
//        $respuesta = Bien::create()->guardarBienPrecio($bienId, $precioCompra, self::PRECIO_COMPRA, $usu_creacion);
//
//        if ($respuesta[0]['vout_exito'] == 0) {
//            throw new WarningException("Error al guardar el precio de compra del bien.");
//        }

        $decode = Util::base64ToImage($file);
        if ($file != null || $file != '') {
            $imagen = $bienId . '.jpg';
            file_put_contents(__DIR__ . '/../../vistas/com/bien/imagen/' . $imagen, $decode);
        }

        if ($response[0]["vout_exito"] == 0) {
            return $response;
        }

        $response_empresa = Empresa::create()->getDataEmpresaTotal();
        $response_unidad_medida = Unidad::create()->getDataUnidadTipo();

        for ($i = 0; $i < count($response_empresa); $i++) {
            $estadoep = 0;
            $id_emp = $response_empresa[$i]['id'];
            for ($j = 0; $j < count($empresa); $j++) {
                if ($id_emp == $empresa[$j]) {
                    $estadoep = 1;
                }
            }
            Bien::create()->insertBienEmpresa($bienId, $id_emp, $cant_minima, $estadoep, $unidad_control_id);
        }
        for ($ii = 0; $ii < count($response_unidad_medida); $ii++) {
            $estadou = 0;
            $id_unidad_tipo = $response_unidad_medida[$ii]['id'];
            for ($jj = 0; $jj < count($unidad_tipo); $jj++) {
                if ($id_unidad_tipo == $unidad_tipo[$jj]) {
                    $estadou = 1;
                }
            }
            Bien::create()->insertBienUnidadTipo($bienId, $id_unidad_tipo, $estadou, $usu_creacion);
        }
        return $response;
    }

    public function getBien($id) {
        $rutaImagenBien = __DIR__ . '/../../vistas/com/bien/imagen/';
        $extensionImagen = ".jpg";
        $imagenPorDefecto = "bienNone";

        $data = Bien::create()->getBien($id);
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

            $imagen = $rutaImagenBien . $data[$i]['imagen'] . $extensionImagen;

            if (file_exists($imagen)) {
                $data[$i]['imagen'] = $data[$i]['imagen'] . $extensionImagen;
            } else {
                $data[$i]['imagen'] = $imagenPorDefecto . $extensionImagen;
            }
        }
        return $data;
    }

    public function updateBien($id_bien, $descripcion, $codigo, $tipo, $cant_minima, $estado, $comentario, $empresa, $file, $unidad_tipo, $usuarioId, $agregado_precio_venta, $agregado_precio_venta_tipo, $unidad_control_id, $listaProveedorId, $listaPrioridad, $codigoFabricante, $precioCompra, $marca, $codigoBarras, $listaPrecioDetalle, $listaBienPrecioEliminado, $maquinaria, $codigoSunatId, $objCamposBien) {
//        throw new WarningException(count($unidad_tipo));

        $decode = Util::base64ToImage($file);
        if ($file != null || $file != '') {
            $imagen = $id_bien . '.jpg';
            $direccion_imagen = __DIR__ . '/../../vistas/com/bien/imagen/' . $imagen;
            unlink($direccion_imagen);
            file_put_contents($direccion_imagen, $decode);
        }

        if ($tipo == -1) {
            $unidad_tipo[0] = -1;
        }

        //si existe marca obtengo el id de lo contrario inserto la marca.
        $marca = trim($marca);
        if ($marca != '') {
            $resMarca = Bien::create()->obtenerMarcaXDescripcion($marca);

            if (ObjectUtil::isEmpty($resMarca)) {
                $resMarca = Bien::create()->insertarMarca($marca, $usuarioId);
            }

            $marcaId = $resMarca[0]['id'];
        } else {
            $marcaId = null;
        }

        //si existe maquinaria obtengo el id de lo contrario inserto la maquinaria.
        $maquinaria = trim($maquinaria);
        if ($maquinaria != '') {
            $resMaquinaria = Bien::create()->obtenerMaquinariaXDescripcion($maquinaria);

            if (ObjectUtil::isEmpty($resMaquinaria)) {
                $resMaquinaria = Bien::create()->insertarMaquinaria($maquinaria, $usuarioId);
            }

            $maquinariaId = $resMaquinaria[0]['id'];
        } else {
            $maquinariaId = null;
        }

        $response = Bien::create()->updateBien($id_bien, $descripcion, $codigo, $tipo, $estado, $comentario, $agregado_precio_venta, $agregado_precio_venta_tipo, $codigoFabricante, $marcaId, $codigoBarras, $maquinariaId, $codigoSunatId, $objCamposBien['cuentaContableId'], $objCamposBien['costoInical'], $objCamposBien['codigoCuenta'], $objCamposBien['codigoInternacional'], $objCamposBien['modelo'], $objCamposBien['serieNumero'], $objCamposBien['depreciacionMetodo'], $objCamposBien['depreciacionPorcentaje'], $objCamposBien['fechaAdquisicion'], $objCamposBien['fechaInicioUso'], $objCamposBien['cuentaContableGasto'], $objCamposBien['cuentaContableDepreciacion'], $objCamposBien['cuentaContableVenta']
        );

        if ($response[0]['vout_exito'] == 0) {
            throw new WarningException($response[0]['vout_mensaje']);
        }

        $respuestaEliminarDistribucion = self::eliminarDistribucionXBienId($id_bien);
        foreach ($objCamposBien['distribucionContable'] as $item) {
            $respuestaGuardaCentroCostoBien = self::guardarDistribucionXBienId($id_bien, $item['centro_costo_id'], $item['porcentaje'], $usuarioId);
            if ($respuestaGuardaCentroCostoBien[0]['vout_exito'] == 0) {
                throw new WarningException($response[0]['vout_mensaje']);
            }
        }

        array_multisort($listaPrioridad, $listaProveedorId);
        foreach ($listaProveedorId as $indice => $proveedor) {
            $proveedorId = $proveedor;
            $prioridad = $listaPrioridad[$indice];

            $r = $this->insertarBienPersona($id_bien, $proveedorId, $prioridad, $usuarioId);
        }

        // guardar bien precio detalle de lista
        foreach ($listaPrecioDetalle as $indice => $item) {
            $bienPrecioId = $item[8];
            $monedaId = $item[4];
            $precioTipoId = $item[0];
            $unidadMedidaId = $item[2];
            $precio = $item[6];
            $descuento = $item[7];
            $incluyeIGV = $item[9];
            $checkIGV = $item[10];

            $res = Bien::create()->guardarBienPrecioDetalle($bienPrecioId, $id_bien, $monedaId, $precioTipoId, $unidadMedidaId, $precio, $descuento, $usuarioId, $incluyeIGV, $checkIGV);

            if ($res[0]['vout_exito'] == 0) {
                throw new WarningException("Error al guardar el precio del bien. " . $res[0]['vout_mensaje']);
            }
        }

        foreach ($listaBienPrecioEliminado as $indice => $item) {
            $bienPrecioId = $item[0];

            $res2 = Bien::create()->eliminarBienPrecio($bienPrecioId);
        }

//        //guardar precio en bien_precio (VENTA)
//        $respuesta = Bien::create()->guardarBienPrecio($id_bien, $agregado_precio_venta, self::PRECIO_VENTA, $usuarioId);
//
//        if ($respuesta[0]['vout_exito'] == 0) {
//            throw new WarningException("Error al guardar el precio de venta del bien.");
//        }
//        
//        //guardar precio en bien_precio (COMPRA)
//        $respuesta = Bien::create()->guardarBienPrecio($id_bien, $precioCompra, self::PRECIO_COMPRA, $usuarioId);
//
//        if ($respuesta[0]['vout_exito'] == 0) {
//            throw new WarningException("Error al guardar el precio de compra del bien.");
//        }     
        //$precioCompra = $this->obtenerPrecioSugeridoCompraXCodigo($codigo, $id_bien, 0);
        //if ($precioCompra != FALSE) {
        //$this->obtenerPrecioSugeridoVenta($id_bien, $precioCompra, $agregado_precio_venta, $agregado_precio_venta_tipo, 0);
        //}

        if ($response[0]["vout_exito"] == 0) {
            return $response;
        } else {
            $response_empresa = Empresa::create()->getDataEmpresaTotal();
            $response_unidad_medida = Unidad::create()->getDataUnidadTipo();

            for ($i = 0; $i < count($response_empresa); $i++) {
                $estadop = 0;
                $id_emp = $response_empresa[$i]['id'];
//                for ($j = 0; $j < count($empresa); $j++) {
//                foreach ($empresa as $emp) {
//                    if ($id_emp == $emp) {
//                        $estadop = 1;
//                    }
//                }
                for ($j = 0; $j < count($empresa); $j++) {
                    if ($id_emp == $empresa[$j]) {
                        $estadop = 1;
                    }
                }
                $res = Bien::create()->updateBienEmpresa($id_bien, $id_emp, $cant_minima, $estadop, $unidad_control_id);
            }

            for ($ii = 0; $ii < count($response_unidad_medida); $ii++) {
                $estadou = 0;
                $id_unidad_tipo = $response_unidad_medida[$ii]['id'];
                for ($jj = 0; $jj < count($unidad_tipo); $jj++) {
                    if ($id_unidad_tipo == $unidad_tipo[$jj]) {
                        $estadou = 1;
                    }
                }
                Bien::create()->updateBienUnidadTipo($id_bien, $id_unidad_tipo, $estadou, $usuarioId);
            }
            return $response;
        }
    }

    public function deleteBien($id_bien, $nom) {
        $response = Bien::create()->deleteBien($id_bien);
        $response[0]['nombre'] = $nom;
        return $response;
    }

    public function cambiarEstado($id_estado) {
        $data = Bien::create()->cambiarEstado($id_estado);
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

    /*
     * funciones para importar un excel
     * 1. importBien
     * 2. importarBienXML
     * 3. isValid
     */

    private function getKeyProveedores($str, $proveedores) {
        foreach ($proveedores as $id => $nombre) {
            if (strpos(strtolower($str, strtolower($nombre)) !== false)) {
                return $id;
            }
        }
        return false;
    }

    private function easeElement($e) {
        $parseUnidad = array("unidad", "und.", "und", "unid", "unid.", "undades");
        $parseBolsas = array("bolsa", "bolsas");
        $parseJuegos = array("jgo.", "jgo", "juego", "juegos");
        $parseKilogs = array("kg.", "kg", "kilos", "kilogramos");
        $parseGramos = array("gr.", "gr", "gramos");
        $parseMetros = array("mts.", "mts.", "mts,", "metros");
        $parsePiezas = array("pza.", "pza,", "pza", "piezas");
        //$parseProvIn = array("BEC", "ELCOPE", "EPLI", "FARCESA", "HUEMURA", "MANELSA", "METICO", "PROMATISA", "SIGELEC", "STAR ELEC");
        //$parseProvee = array("pza.", "pza,", "pza", "piezas");
        $e->unidadcontrol = strtolower($e->unidadcontrol);
        $e->unidadcontrol = str_replace($parseUnidad, "Unidad(es)", $e->unidadcontrol);
        $e->unidadcontrol = str_replace($parseBolsas, "Bolsa(s)", $e->unidadcontrol);
        $e->unidadcontrol = str_replace($parseJuegos, "Juego(s)", $e->unidadcontrol);
        $e->unidadcontrol = str_replace($parseKilogs, "Kilogramo(s)", $e->unidadcontrol);
        $e->unidadcontrol = str_replace($parseGramos, "Gramo(s)", $e->unidadcontrol);
        $e->unidadcontrol = str_replace($parseMetros, "Metro(s)", $e->unidadcontrol);
        $e->unidadcontrol = str_replace($parsePiezas, "Pieza(s)", $e->unidadcontrol);
        return $e;
    }

    private function easeBien($p) {
        $e = new stdClass();
        $e->codigo = trim((string) $p->codigo);
        $e->descripcion = trim((string) $p->descripcion);
        $e->tipoBien = trim((string) $p->tipobien); // $tipo
        $e->tipoUnidad = trim((string) $p->tipounidad);
        $e->cantidadMinima = trim((string) $p->cantidadMinima) * 1; // $cantidad_minina
        $e->unidadControl = trim((string) $p->unidadControl);
        $e->precioCompra = trim((string) $p->precioCompra);
        $e->precioVenta = trim((string) $p->precioVenta);
        $e->prioridades = array(1 => trim($p->proveedorprioridad1),
            2 => trim($p->proveedorprioridad2),
            3 => trim($p->proveedorprioridad3),
            4 => trim($p->proveedorprioridad4));
        foreach ($p as $key => $value) {
            if (strpos($key, "stock") !== false) {
                $e->$key = $value;
            }
        }
        return $e;
    }

    public function importaBienXML($xml, $usuarioCreacion, $empresaId) {
        return Bien::create()->importBienXML($xml, $usuarioCreacion, $empresaId);
    }

    /*
     * fin de funciones para importar un excel
     */

    public function ExportarBienExcel($usuarioId, $empresaId) {
        $estiloTituloReporte = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'strike' => false,
                'size' => 10
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );

        $estiloTituloColumnas = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'size' => 10
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => FALSE
            )
        );

        $estiloTxtInformacion = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 9
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_HAIR,
                    'color' => array(
                        'rgb' => '000000'
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => FALSE
            )
        );

        $estiloNumInformacion = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 8
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_HAIR,
                    'color' => array(
                        'rgb' => '000000'
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => FALSE
            )
        );

        $objPHPExcel = new PHPExcel();

        $i = 1;
        $j = 2;
        $objPHPExcel->setActiveSheetIndex(0)
                ->mergeCells('B' . $i . ':I' . $i);

//        $response
        // Agregar Informacion
        $objPHPExcel->setActiveSheetIndex()
                ->setCellValue('B' . $i, 'Lista de Productos');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($estiloTituloReporte);
//        $objPHPExcel->getActiveSheet()->setSharedStyle($estiloTituloReporte, "A" . $i . ":C" . $i);
        $i += 2;
        //$j++;
        $j += 2;

        //Código	Descripción	Tipo Unidad	Control	Precio sugerido compra	Precio sugerido venta	Estado	Opciones
        $response = $this->getDataBien($usuarioId, $empresaId);

        $objPHPExcel->setActiveSheetIndex()
                ->setCellValue('A' . $i, '      ');
        $objPHPExcel->setActiveSheetIndex()
                ->setCellValue('B' . $i, 'Código');
        $objPHPExcel->setActiveSheetIndex()
                ->setCellValue('C' . $i, 'Descripcion');
//        $objPHPExcel->setActiveSheetIndex()
//                ->setCellValue('D' . $i, 'CodigoFabricante');
//        $objPHPExcel->setActiveSheetIndex()
//                ->setCellValue('E' . $i, 'CodigoBarras');
        $objPHPExcel->setActiveSheetIndex()
                ->setCellValue('D' . $i, 'Marca');
        $objPHPExcel->setActiveSheetIndex()
                ->setCellValue('E' . $i, 'Maquinaria');
        $objPHPExcel->setActiveSheetIndex()
                ->setCellValue('F' . $i, 'Tipo Producto');
        $objPHPExcel->setActiveSheetIndex()
                ->setCellValue('G' . $i, 'Tipo Unidad');
        $objPHPExcel->setActiveSheetIndex()
                ->setCellValue('H' . $i, 'Cantidad Mínima');
        $objPHPExcel->setActiveSheetIndex()
                ->setCellValue('I' . $i, 'Unidad Control');
//        $objPHPExcel->setActiveSheetIndex()
//                ->setCellValue('K' . $i, 'Precio sugerido compra');
//        $objPHPExcel->setActiveSheetIndex()
//                ->setCellValue('L' . $i, 'Precio sugerido venta');
        $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':I' . $i)->applyFromArray($estiloTituloColumnas);


        foreach ($response as $campo) {
            $objPHPExcel->setActiveSheetIndex()
//                ->setCellValue('A' . $i, 'Lista de Bienes')
                    ->setCellValue('B' . $j, $campo['codigo'])
                    ->setCellValue('C' . $j, $campo['b_descripcion'])
//                    ->setCellValue('D' . $j, $campo['codigo_fabricante'])
//                    ->setCellValue('E' . $j, $campo['codigo_barra'])
                    ->setCellValue('D' . $j, $campo['marca'])
                    ->setCellValue('E' . $j, $campo['maquinaria'])
                    ->setCellValue('F' . $j, $campo['tb_descripcion'])
                    ->setCellValue('G' . $j, $campo['unidad_medida_tipo_descripcion'])
                    ->setCellValue('H' . $j, $campo['cantidad_minima'])
                    ->setCellValue('I' . $j, $campo['unidad_control'])
//                    ->setCellValue('K' . $j, $campo['precio_compra'])
//                    ->setCellValue('L' . $j, $campo['precio_venta'])
            ;
//            $objPHPExcel->getActiveSheet()->getStyle('B' . $j)->applyFromArray($estiloTituloColumnas);
            $i += 1;
            $j++;
//        $objPHPExcel->setActiveSheetIndex()
//                ->setCellValue('A' . $i, 'No Respondieron')
//                ->setCellValue('B' . $i, 'dato2');
//        $i +=1;
//        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i - 2) . ':A' . $i)->applyFromArray($estiloTituloColumnas);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':G' . $i)->applyFromArray($estiloTxtInformacion);
            $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->applyFromArray($estiloNumInformacion);
            $objPHPExcel->getActiveSheet()->getStyle('I' . $i . ':I' . $i)->applyFromArray($estiloTxtInformacion);
//        $i +=1;
//        $i +=2;
        }


        for ($i = 'A'; $i <= 'I'; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }
        // Renombrar Hoja
        $objPHPExcel->getActiveSheet()->setTitle('Bienes');

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/lista_de_bienes.xlsx');
        return 1;
    }

    //funcion para generar codigo de Barras 
//    public function generarCodigoBarras()
//    {
//        $code_number = '125689365472365458';
////        throw new WarningException("hola como estas");
//        new barCodeGenrator($code_number,0,'barra.gif', 190, 130, true);
//    }
    //motivo de saluida del bien

    public function getDataBienMotivoSalida($id_bandera) {
        $data = Bien::create()->getDataBienMotivoSalida();
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

    public function insertBienMotivoSalida($codigo, $descripcion, $comentario, $estado, $usuarioCreacion) {

        $response = Bien::create()->insertBienMotivoSalida($codigo, $descripcion, $comentario, $estado, $usuarioCreacion);
        if ($response[0]["vout_exito"] == 0) {
            return $response;
        } else {
            return $response;
        }
    }

    public function getBienMotivoSalida($id) {
        return Bien::create()->getBienMotivoSalida($id);
    }

    public function updateBienMotivoSalida($id, $descripcion, $codigo, $comentario, $estado) {
        $response = Bien::create()->updateBienMotivoSalida($id, $descripcion, $codigo, $comentario, $estado);
        if ($response[0]["vout_exito"] == 0) {
            return $response[0]["vout_mensaje"];
        } else {
            return $response;
        }
    }

    public function deleteBienMotivoSalida($id, $nom) {
        $response = Bien::create()->deleteBienMotivoSalida($id);
        $response[0]['nombre'] = $nom;
        return $response;
    }

    public function cambiarBienMotivoSalidaEstado($id_estado) {
        $data = Bien::create()->cambiarBienMotivoSalidaEstado($id_estado);
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

    public function getAllUnidadMedidaTipoCombo() {
        return Unidad::create()->getDataComboUnidadTipo();
    }

    public function obtenerActivos($empresaId = NULL) {
        return Bien::create()->obtenerActivos($empresaId);
    }

    public function obtenerActivosXMovimientoTipoId($empresaId = NULL, $movimientoTipoId) {
        return Bien::create()->obtenerActivosXMovimientoTipoId($empresaId, $movimientoTipoId);
    }

    public function obtenerActivosStock() {
        return Bien::create()->obtenerActivosStock();
    }

    public function obtenerBienXEmpresa($idEmpresa) {
        return Bien::create()->obtenerBienXEmpresa($idEmpresa);
    }

    public function obtenerBienKardexXEmpresa($idEmpresa) {
        return Bien::create()->obtenerBienKardexXEmpresa($idEmpresa);
    }

    public function obtenerServicioXEmpresa($idEmpresa) {
        return Bien::create()->obtenerServicioXEmpresa($idEmpresa);
    }

    public function obtenerBienTipoXEmpresa($idEmpresa) {
        return Bien::create()->obtenerBienTipoXEmpresa($idEmpresa);
    }

    public function obtenerBienTipoKardexXEmpresa($idEmpresa) {
        return Bien::create()->obtenerBienTipoKardexXEmpresa($idEmpresa);
    }

    public function obtenerStockPorBien($bienId, $empresaId) {
        $responseTipoBien = $this->getBien($bienId);
        if ($responseTipoBien[0]['bien_tipo_id'] == -1) {
            return null;
        } else {
            $stockBien = Bien::create()->obtenerStock(NULL, $bienId, NULL, '', '', $empresaId);
            return $stockBien;
        }
    }

    public function obtenerStockBase($organizadorId, $bienId) {
        return Bien::create()->obtenerStock($organizadorId, $bienId, NULL, '', '', NULL);
    }

    public function obtenerPrecioPorBien($bienId) {
        return $response = Bien::create()->obtenerPrecioPorBien($bienId);
    }

    //funcion para sacar el precio del bien a travez de su codigo
    private function obtenerPrecioSugeridoCompraXCodigo($bienCodigo, $bienId, $usuarioCreacion) {

        $bienCodigo = strtoupper($bienCodigo);
        $bandera = 1;
        $precio = 0;
        if (!ObjectUtil::isEmpty($bienCodigo)) {
            $arrayCodigo = explode(" ", trim($bienCodigo));

            if (!ObjectUtil::isEmpty($arrayCodigo[0])) {
                if (strlen(trim($arrayCodigo[0])) == 6) {

                    $alto = substr($arrayCodigo[0], 0, 2);
                    $ancho = substr($arrayCodigo[0], 2, 2);
                    $equivalenciaAlto = substr($arrayCodigo[0], 4, 1);
//                    $equivalenciaAlto = strtoupper($equivalenciaAlto);

                    $equivalenciaAncho = substr($arrayCodigo[0], 5, 1);
//                    $equivalenciaAncho = strtoupper($equivalenciaAncho);

                    $respuestaEquivalenciaAlto = Bien::create()->obtenerBienEquivalencia($equivalenciaAlto, 1);

                    if ($respuestaEquivalenciaAlto[0]['vout_exito'] == 1) {
                        $valorEquivalenciaAlto = $respuestaEquivalenciaAlto[0]['valor'];

                        $respuestaEquivalenciaAncho = Bien::create()->obtenerBienEquivalencia($equivalenciaAncho, 1);

                        if ($respuestaEquivalenciaAncho[0]['vout_exito'] == 1) {
                            $valorEquivalenciaAncho = $respuestaEquivalenciaAncho[0]['valor'];

                            if (!ObjectUtil::isEmpty($arrayCodigo[1])) {
                                $coeficiente = trim($arrayCodigo[1]);
                                $respuestaCoeficiente = Bien::create()->obtenerBienEquivalencia($coeficiente, 2);

                                if ($respuestaCoeficiente[0]['vout_exito'] == 1) {
                                    $valorCoeficiente = $respuestaCoeficiente[0]['valor'];

                                    $precio = ((($alto + $valorEquivalenciaAlto) * ($ancho + $valorEquivalenciaAncho)) * $valorCoeficiente);
                                    $precio = $precio * self::PARAMETRO_DESCUENTO * self::PARAMETRO_IGV;

                                    $respuesta = Bien::create()->guardarBienPrecio($bienId, $precio, self::PRECIO_COMPRA, $usuarioCreacion);

                                    if ($respuesta[0]['vout_exito'] == 0) {
                                        throw new WarningException("Error al guardar el precio del bien.");
                                    } else {
                                        return $precio;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $precio;
//        return false;
    }

    function obtenerPrecioSugeridoVenta($bienId, $precioCompra, $agregado_precio_venta, $agregado_precio_venta_tipo, $usuarioCreacion) {
        /**
         * $agregado_precio_venta_tipo = puede ser de 2 tipos
         *  1 = importe
         *  2 = porcentaje
         */
        if ($agregado_precio_venta_tipo == 1) {
            $precioVenta = $precioCompra + $agregado_precio_venta;
        } else {
            $precioVenta = $precioCompra + ($precioCompra * ($agregado_precio_venta / 100));
        }

        $respuesta = Bien::create()->guardarBienPrecio($bienId, $precioVenta, self::PRECIO_VENTA, $usuarioCreacion);

        if ($respuesta[0]['vout_exito'] == 0) {
            throw new WarningException("Error al guardar el precio del bien.");
        }
    }

    function obtenerBienMovimientoEmpresa($empresaId) {
        return Bien::create()->obtenerBienMovimientoEmpresa($empresaId);
    }

    function obtenerBienXMovimientosActivos() {
        return Bien::create()->obtenerBienXMovimientosActivos();
    }

    function obtenerStockOrganizadoresXEmpresa($bienId, $unidadMedida, $movimientoTipoId) {

        $arrayStockXOrganizador = array();

        $respuestaOrganizador = $respuesta->organizador = OrganizadorNegocio::create()->obtenerXMovimientoTipo($movimientoTipoId);

        foreach ($respuestaOrganizador as $organizador) {

            if (!ObjectUtil::isEmpty($organizador['id'])) {
                $respuestaStockOrganizador = Bien::create()->obtenerStockXOrganizador($bienId, $organizador['id'], $unidadMedida);
            }

            if (!ObjectUtil::isEmpty($respuestaStockOrganizador[0]['stock'])) {
                if ($respuestaStockOrganizador[0]['stock'] > 0) {
                    array_push($arrayStockXOrganizador, $this->getStockXOrganizador($organizador['id'], $organizador['descripcion'], $respuestaStockOrganizador[0]['stock']));
                }
            }
        }
        return $arrayStockXOrganizador;
    }

    private function getStockXOrganizador($organizadorId, $organizadorNombre, $stock) {

        $data = new stdClass();
        $data->organizadorId = $organizadorId;
        $data->organizadorDescripcion = $organizadorNombre;
        $data->stock = $stock;

        return $data;
    }

    public function obtenerBienTipo() {
        return BienTipo::create()->obtener();
    }

    public function obtenerBienTipoXId($id) {
        return BienTipo::create()->obtenerXId($id);
    }

    public function obtenerUnidadControlXUnidadMedidaTipoId($id) {
        $unidaMedidasTipos = 0;

        if (!ObjectUtil::isEmpty($id)) {
            $unidaMedidasTipos = $id[0];

            for ($i = 1; $i < count($id); $i++) {
                $unidaMedidasTipos = $id[$i] . "," . $unidaMedidasTipos;
            }
        }

        return UnidadMedida::create()->obtenerUnidadControlXUnidadMedidaTipoId($unidaMedidasTipos);
    }

    public function obtenerCantidadMinima($bienId, $unidadMedidaId) {
        return Bien::create()->obtenerBienCantidadMinima($bienId, $unidadMedidaId);
    }

    public function obtenerStockActual($bienId, $organizadorId, $unidadMedidaId, $organizadorDestinoId = null) {
        return Bien::create()->obtenerStockActual($bienId, $organizadorId, $unidadMedidaId, $organizadorDestinoId);
    }

    public function obtenerBienActivoXDescripcion($bienDescripcion) {
        return Bien::create()->obtenerBienActivoXDescripcion($bienDescripcion);
    }

    public function obtenerBienPersonaXBienId($id) {
        return Bien::create()->obtenerBienPersonaXBienId($id);
    }

    public function obtenerActivosFijosXEmpresa($idEmpresa) {
        return Bien::create()->obtenerActivosFijosXEmpresa($idEmpresa);
    }

    public function obtenerCantidadMinimaConvertido($bienId, $organizadorId, $unidadMedidaId) {
        return Bien::create()->obtenerCantidadMinimaConvertido($bienId, $organizadorId, $unidadMedidaId);
    }

    public function obtenerBienesConStockMenorACantidadMinima($personaId, $organizadorId, $empresaId, $monedaId, $operador) {
        return Bien::create()->obtenerBienesConStockMenorACantidadMinima($personaId, $organizadorId, $empresaId, $monedaId, $operador);
    }

    public function obtenerStockTotalXBienIDXUnidadMedidaId($bienId, $unidadMedidaId) {
        return Bien::create()->obtenerStockTotalXBienIDXUnidadMedidaId($bienId, $unidadMedidaId);
    }

    public function obtenerMarcas() {
        return Bien::create()->obtenerMarcas();
    }

    public function obtenerBienTipoPadres() {
        return Bien::create()->getDataBienTipo();
    }

    public function obtenerBienTipoPadresDisponibles($bienTipoId) {
        return Bien::create()->obtenerBienTipoPadresDisponibles($bienTipoId);
    }

    public function obtenerMaquinarias() {
        return Bien::create()->obtenerMaquinarias();
    }

    public function obtenerConfiguracionesInicialesBienTipo($bienTipoId) {
        $respuesta->dataSunatDetalle = SunatTablaNegocio::create()->obtenerDetalleXSunatTablaId(5);
        $respuesta->dataSunatDetalle2 = SunatTablaNegocio::create()->obtenerDetalleXSunatTablaId(30);

        if (ObjectUtil::isEmpty($bienTipoId)) {
            $respuesta->dataBienTipoPadres = $this->obtenerBienTipoPadres();
        } else {
            $respuesta->dataBienTipo = $this->getBienTipo($bienTipoId);
            $respuesta->dataBienTipoPadres = $this->obtenerBienTipoPadresDisponibles($bienTipoId);
        }

        return $respuesta;
    }

    public function obtenerStockEntreFechasXBienIdXOrganizadorIdXUnidadMedidaId($bienId, $organizadorId, $unidadMedidaId, $fechaInicial, $fechaFinal, $organizadorDestinoId = null) {
        return Bien::create()->obtenerStockEntreFechasXBienIdXOrganizadorIdXUnidadMedidaId($bienId, $organizadorId, $unidadMedidaId, $fechaInicial, $fechaFinal, $organizadorDestinoId);
    }

    public function obtenerPrecioTipoXIndicador($indicador) {
        return Bien::create()->obtenerPrecioTipoXIndicador($indicador);
    }

    public function obtenerBienTipoPadre() {
        return BienTipo::create()->obtenerBienTipoPadre();
    }

    public function obtenerBienTipoHijosXBienTipoPadreId($bienTipoPadreId) {
        $respuesta = array();
        $bienTipoPadreId = Util::convertirArrayXCadena($bienTipoPadreId);
        $data = $this->obtenerBienTipoXBienTipoPadreId($bienTipoPadreId);

        if (!ObjectUtil::isEmpty($data)) {
            $respuesta = $data;
            $respuesta = $this->obtenerBienTipoRecursivo($respuesta, $data);
        }

        foreach ($respuesta as $index => $item) {
            $aux[$index] = $item['codigo'];
        }

        array_multisort($aux, SORT_ASC, $respuesta);

        return $respuesta;
    }

    public function obtenerBienTipoRecursivo($respuesta, $data) {

        foreach ($data as $item) {
            $data2 = $this->obtenerBienTipoXBienTipoPadreId($item['id']);

            if (!ObjectUtil::isEmpty($data2)) {
                $respuesta = array_merge($respuesta, $data2);
                $respuesta = $this->obtenerBienTipoRecursivo($respuesta, $data2);
            }
        }

        return $respuesta;
    }

    public function obtenerBienTipoXBienTipoPadreId($bienTipoPadreId) {
        return BienTipo::create()->obtenerBienTipoXBienTipoPadreId($bienTipoPadreId);
    }

    public function enviarNotificacionActivosFijosNoInternados() {
        $data = Bien::create()->obtenerActivosFijosNoInternados();

        if (!ObjectUtil::isEmpty($data)) {
            $plantilla = EmailPlantillaNegocio::create()->obtenerEmailPlantillaXID(16);
            $correosPlantilla = EmailPlantillaNegocio::create()->obtenerEmailXDestinatario($plantilla[0]["destinatario"], null, null);

            $correos = implode(";", $correosPlantilla);
//            foreach ($correosPlantilla as $email) {
//                $correos = $correos . $email . ';';
//            }
            //dibujando la cabecera
            $dataDetalle = '<table  border=1 cellspacing=0 cellpadding=5 bordercolor="000000"  width="91.7%">
                        <thead>';

            $html = '<tr>';
            $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Item</th>";
            $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>F.Emisión</th>";
            $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Tipo de documento</th>";
            $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>S/N</th>";
            $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Bien</th>";
            $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>Cantidad</th>";
            $html = $html . "<th style='text-align:center;font-size:14px;line-height:1.5;'>U.M.</th>";
            $html = $html . '</tr>';

            $dataDetalle = $dataDetalle . $html;
            $dataDetalle = $dataDetalle . '<thead>';
            $dataDetalle = $dataDetalle . '<tbody>';

            foreach ($data as $index => $item) {
                $html = '<tr>';
                $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . ($index + 1);
                $html = $html . '<td style=\'text-align:center;font-size:14px;line-height:1.5;\'>' . DateUtil::formatearFechaBDAaCadenaVw($item['fecha_emision']);
                $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['documento_tipo_descripcion'];
                $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['serie'] . '-' . $item['numero'];
                $html = $html . '<td style=\'text-align:left;font-size:14px;line-height:1.5;\'>' . $item['bien_descripcion'];
                $html = $html . '<td style=\'text-align:right;font-size:14px;line-height:1.5;\'>' . number_format($item['cantidad'], 2, ".", ",");
                $html = $html . '<td style=\'text-align:center;font-size:14px;line-height:1.5;\'>' . $item['unidad_medida_descripcion'];
                $html = $html . '</tr>';

                $dataDetalle = $dataDetalle . $html;
            }

            $dataDetalle = $dataDetalle . '</tbody></table>';
            $descripcion = 'Detalle de activos fijos pendientes de internar';

            //logica correo:             
            $asunto = $plantilla[0]["asunto"];
            $cuerpo = $plantilla[0]["cuerpo"];

            $cuerpo = str_replace("[|titulo_email|]", 'ACTIVOS FIJOS PENDIENTES DE INTERNAR', $cuerpo);
            $cuerpo = str_replace("[|descripcion|]", $descripcion, $cuerpo);
            $cuerpo = str_replace("[|detalle_programacion|]", $dataDetalle, $cuerpo);

            $res = EmailEnvioNegocio::create()->insertarEmailEnvio($correos, $asunto, $cuerpo, 1, 1);
//            return $cuerpo;
            return 'Pendiente por aprobar. ' . $res[0]['vout_mensaje'] . ' Id: ' . $res[0]['id'] . ' <br>';
        } else {
            return '';
        }
    }

    public function obtenerMetodosDepreciacion() {
        return Bien::create()->obtenerMetodosDepreciacion();
    }

    public function obtenerDepreaciacionPorcentaje() {
        return Bien::create()->obtenerDepreaciacionPorcentaje();
    }

    public function obtenerDistribucionXBienId($bienId) {
        return Bien::create()->obtenerDistribucionXBienId($bienId);
    }

    public function eliminarDistribucionXBienId($bienId) {
        return Bien::create()->eliminarDistribucionXBienId($bienId);
    }

    public function guardarDistribucionXBienId($bienId, $centroCostoId, $porcentaje, $usuarioId) {
        return Bien::create()->guardarDistribucionXBienId($bienId, $centroCostoId, $porcentaje, $usuarioId);
    }

    public function actualizarEstadoDepreciado($bienId) {
        return Bien::create()->actualizarEstadoDepreciado($bienId);
    }

    public function obtenerBienXTexto($texto1, $texto2, $empresa, $movimiento_tipoId, $bien_tipo = null){
        $response = Bien::create()->obtenerBienXTexto($texto1, $texto2, $empresa, $movimiento_tipoId, $bien_tipo);
        return $response;
    }
    
    public function obtenerActivosXMovimientoTipoIdBienId($empresaId = NULL, $movimientoTipoId, $bienId)
    {
      return Bien::create()->obtenerActivosXMovimientoTipoIdBienId($empresaId, $movimientoTipoId, $bienId);
    }

    public function obtenerXIdPadre($id) {
        return BienTipo::create()->obtenerXIdPadre($id);
    }

    public function obtenerBienActivosInventario() {
        return Bien::create()->obtenerBienActivosInventario();
    }
}
