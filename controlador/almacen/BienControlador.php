<?php

require_once __DIR__ . '/AlmacenIndexControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/BienNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/UnidadNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/EmpresaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/OrganizadorNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/BienPrecioNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/MonedaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/PlanContableNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/CentroCostoNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/ActivoFijoNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/SunatTablaNegocio.php';
require_once __DIR__ . '/../../util/Configuraciones.php';
require_once __DIR__ . '/../../util/ImportacionExcel.php';
require_once __DIR__ . '/../../util/PHPExcel/PHPExcel/IOFactory.php';

class BienControlador extends AlmacenIndexControlador {

    public function getDataGridBienTipo() {
        return BienNegocio::create()->getDataBienTipo();
    }

    public function insertBienTipo() {
        $descripcion = $this->getParametro("descripcion");
        $codigo = $this->getParametro("codigo");
        $comentario = $this->getParametro("comentario");
        $estado = $this->getParametro("estado");
        $tipo = $this->getParametro("tipo");
        $bienTipoPadreId = $this->getParametro("bienTipoPadreId");
        $usu_creacion = $this->getUsuarioId();
        $codigoSunatId = $this->getParametro("codigoSunatId");
        $codigoSunatId2 = $this->getParametro("codigoSunatId2");
        return BienNegocio::create()->insertBienTipo($codigo, $descripcion, $comentario, $estado, $tipo, $usu_creacion, $bienTipoPadreId, $codigoSunatId, $codigoSunatId2);
    }

    public function getBienTipo() {
        $id_bien_tipo = $this->getParametro("id_bien_tipo");
        $resultado->dataBienTipo = BienNegocio::create()->getBienTipo($id_bien_tipo);
        $resultado->dataBienTipoPadres = BienNegocio::create()->obtenerBienTipoPadresDisponibles($id_bien_tipo);
        return $resultado;
    }

    public function updateBienTipo() {
        $id_bien_tipo = $usu_nombre = $this->getParametro("id_bien_tipo");
        $descripcion = $this->getParametro("descripcion");
        $codigo = $this->getParametro("codigo");
        $comentario = $this->getParametro("comentario");
        $estado = $this->getParametro("estado");
        $bienTipoPadreId = $this->getParametro("bienTipoPadreId");
        $codigoSunatId = $this->getParametro("codigoSunatId");
        $codigoSunatId2 = $this->getParametro("codigoSunatId2");
        return BienNegocio::create()->updateBienTipo($id_bien_tipo, $descripcion, $codigo, $comentario, $estado, $bienTipoPadreId, $codigoSunatId, $codigoSunatId2);
    }

    public function deleteBienTipo() {
        $id_bien_tipo = $this->getParametro("id_bien_tipo");
        return ActivoFijo::create()->deleteBienTipo($id_bien_tipo);
    }

    public function darDeBajaActivoFijo() {
        $bienId = $this->getParametro("bien_id");
        $periodoId = $this->getParametro("periodo_id");
        $fechaContable = $this->getParametro("fecha_contable");
        $cuentaContable = $this->getParametro("cuenta_contable");
        $usuarioId = $this->getUsuarioId();
        $this->setTransaction();
        return ActivoFijoNegocio::create()->darDeBajaActivoFijo($bienId, $periodoId, $usuarioId, $cuentaContable, $fechaContable);
    }

    public function cambiarTipoEstado() {
        $id_estado = $this->getParametro("id_estado");
        return BienNegocio::create()->cambiarTipoEstado($id_estado);
    }

    public function getComboEmpresaTipo() {
        $id_tipo = $this->getParametro("id_tipo");
        if ($id_tipo == null) {
            return EmpresaNegocio::create()->getDataEmpresa($id_tipo);
        } else {
            return EmpresaNegocio::create()->getDataEmpresaBienTipo($id_tipo);
        }
    }

    //////////////////////////////////////////////////////////////////////////

    public function getDataGridBien() {
        $usuarioCreacion = $this->getUsuarioId();
        $empresaId = $this->getParametro("empresaId");

        $resultado->dataBien = BienNegocio::create()->getDataBien($usuarioCreacion, $empresaId);
        $resultado->dataPeriodo = PeriodoNegocio::create()->obtenerPeriodoAbiertoXEmpresa($empresaId);
        $resultado->dataCuentaContable = PlanContableNegocio::create()->obtenerXEmpresaId($empresaId);
        $resultado->dataPrecioTipo = BienNegocio::create()->obtenerPrecioTipoXIndicador(1);

        return $resultado;
    }

    public function insertBien() {
        $this->setTransaction();
        $descripcion = $this->getParametro("descripcion");
        $codigo = $this->getParametro("codigo");
        $tipo = $this->getParametro("tipo");
        $cant_minima = $this->getParametro("cant_minima");
        $estado = $this->getParametro("estado");
        $comentario = $this->getParametro("comentario");
        $empresa = $this->getParametro("empresa");
        $file = $this->getParametro("file");
        $unidad_tipo = $this->getParametro("unidad_tipo");
//        $agregado_precio_venta = $this->getParametro("agregado_precio_venta");
        $agregado_precio_venta_tipo = $this->getParametro("agregado_precio_venta_tipo");
        $usu_creacion = $this->getUsuarioId();
        $unidad_control_id = $this->getParametro("unidad_control_id");

        //Proveedores        
        $listaProveedorId = $this->getParametro("listaProveedorId");
        $listaPrioridad = $this->getParametro("listaPrioridad");

        //Codigo fabricante        
        $codigoFabricante = $this->getParametro("codigoFabricante");
        $codigoBarras = $this->getParametro("codigoBarras");
//        $precioCompra = $this->getParametro("precioCompra");
        $marca = $this->getParametro("marca");
        $maquinaria = $this->getParametro("maquinaria");
        $codigoSunatId = $this->getParametro("codigoSunatId");
        $objCamposBien = $this->getParametro("objCamposBien");

        //precio detalle
        $listaPrecioDetalle = $this->getParametro("listaPrecioDetalle");


        return BienNegocio::create()->insertBien($descripcion, $codigo, $tipo, $cant_minima, $estado, $usu_creacion, $comentario, $empresa, $file, $unidad_tipo, $agregado_precio_venta, $agregado_precio_venta_tipo, $unidad_control_id, $listaProveedorId, $listaPrioridad, $codigoFabricante, $precioCompra, $marca, $codigoBarras, $listaPrecioDetalle, $maquinaria, $codigoSunatId, $objCamposBien);
    }

    public function getBien() {
        $id_bien = $this->getParametro("id_bien");
        return BienNegocio::create()->getBien($id_bien);
    }

    public function updateBien() {
        $this->setTransaction();
        $id_bien = $usu_nombre = $this->getParametro("id_bien");
        $descripcion = $this->getParametro("descripcion");
        $codigo = $this->getParametro("codigo");
        $tipo = $this->getParametro("tipo");
        $cant_minima = $this->getParametro("cant_minima");
        $estado = $this->getParametro("estado");
        $comentario = $this->getParametro("comentario");
        $empresa = $this->getParametro("empresa");
        $file = $this->getParametro("file");
        $unidad_tipo = $this->getParametro("unidad_tipo");
//        $agregado_precio_venta = $this->getParametro("agregado_precio_venta");
        $agregado_precio_venta_tipo = $this->getParametro("agregado_precio_venta_tipo");
        $usuarioId = $this->getUsuarioId();
        $unidad_control_id = $this->getParametro("unidad_control_id");

        //Proveedores        
        $listaProveedorId = $this->getParametro("listaProveedorId");
        $listaPrioridad = $this->getParametro("listaPrioridad");

        //Codigo fabricante        
        $codigoFabricante = $this->getParametro("codigoFabricante");
        $codigoBarras = $this->getParametro("codigoBarras");
//        $precioCompra = $this->getParametro("precioCompra");
        $marca = $this->getParametro("marca");
        $maquinaria = $this->getParametro("maquinaria");
        $codigoSunatId = $this->getParametro("codigoSunatId");
        $objCamposBien = $this->getParametro("objCamposBien");

        //precio detalle
        $listaPrecioDetalle = $this->getParametro("listaPrecioDetalle");
        $listaBienPrecioEliminado = $this->getParametro("listaBienPrecioEliminado");

        return BienNegocio::create()->updateBien($id_bien, $descripcion, $codigo, $tipo, $cant_minima, $estado, $comentario, $empresa, $file, $unidad_tipo, $usuarioId, $agregado_precio_venta, $agregado_precio_venta_tipo, $unidad_control_id, $listaProveedorId, $listaPrioridad, $codigoFabricante, $precioCompra, $marca, $codigoBarras, $listaPrecioDetalle, $listaBienPrecioEliminado, $maquinaria, $codigoSunatId, $objCamposBien);
    }

    public function deleteBien() {
        $id_bien = $this->getParametro("id_bien");
        $nom = $this->getParametro("nom");
        return BienNegocio::create()->deleteBien($id_bien, $nom);
    }

    public function getAllBienTipo() {
        return BienNegocio::create()->getAllBienTipo();
    }

    public function cambiarEstado() {
        $id_estado = $this->getParametro("id_estado");
        return BienNegocio::create()->cambiarEstado($id_estado);
    }

    public function getAllEmpresa() {
        $usuarioId = $this->getUsuarioId();
        return EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    }

    public function getAllEmpresaImport() {
        $usuarioId = $this->getUsuarioId();
//        return EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
        return EmpresaNegocio::create()->getEmpresaActivas();
    }

    public function obtenerConfiguracionesIniciales() {
        $usuarioId = $this->getUsuarioId();
        $bienId = $this->getParametro("bienId");
        $bienTipoId = $this->getParametro("bienTipoId");
        $empresaId = $this->getParametro("empresaId");
        $respuesta = new stdClass();
        // Obtengo las configuraciones comunes 
        $respuesta->unidadMedidaTipo = ($bienTipoId == -1) ? UnidadNegocio::create()->obtenerUnidadMedidaTipoXId(-1) : UnidadNegocio::create()->obtenerUnidadMedidaTipo(); // UnidadMedidaTipo
        //$respuesta->empresa = EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId); // Empresas
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas(); //todas las empresas
        $respuesta->bienTipo = BienNegocio::create()->obtenerXIdPadre($bienTipoId); // BienTipo 
        $respuesta->bien = ($bienId > -2) ? BienNegocio::create()->getBien($bienId) : null; // > -2 , porque el id de comment = -1
        $respuesta->bienPrecio = ($bienId > 0) ? BienPrecioNegocio::create()->obtenerBienPrecioXBienId($bienId) : null;

        //$proveedores=BienNegocio::create()->obtenerBienPersonaXBienId($bienId);
        $respuesta->bienPersona = ($bienId > 0) ? BienNegocio::create()->obtenerBienPersonaXBienId($bienId) : null;

        // datos para combos de pestaña de precio
        $respuesta->precioTipo = BienPrecioNegocio::create()->obtenerPrecioTipoActivo();
        $respuesta->moneda = MonedaNegocio::create()->obtenerComboMoneda();
        $respuesta->unidadMedidaUnidades = UnidadNegocio::create()->getUnidad(-1);
        $respuesta->marcas = BienNegocio::create()->obtenerMarcas();
        $respuesta->maquinarias = BienNegocio::create()->obtenerMaquinarias();
        $respuesta->dataSunatDetalle = SunatTablaNegocio::create()->obtenerDetalleXSunatTablaId(5);
//        $respuesta->cuentaContable=  PlanContableNegocio::create()->obtenerXCodigoInicial('20');
//        $respuesta->cuentaContable=  PlanContableNegocio::create()->obtenerXCodigo(Configuraciones::CUENTA_CONTABLE_PRODUCTO);
        $respuesta->cuentaContable = PlanContableNegocio::create()->obtenerXEmpresaId($empresaId);
        $respuesta->dataCentroCosto = CentroCostoNegocio::create()->listarCentroCosto($empresaId);
        $respuesta->dataDistribucion = BienNegocio::create()->obtenerDistribucionXBienId($bienId);
        $respuesta->dataDepreciacion = BienNegocio::create()->obtenerDepreaciacionPorcentaje();
        $respuesta->dataDepreciacionMetodo = BienNegocio::create()->obtenerMetodosDepreciacion();
        return $respuesta;
    }

    public function obtenerUnidadControl() {

        $unidadMedidaTipoId = $this->getParametro("id_unidad_medida_tipo");

        $respuesta = new stdClass();

        $respuesta->unidadMedida = BienNegocio::create()->obtenerUnidadControlXUnidadMedidaTipoId($unidadMedidaTipoId);

        return $respuesta;
    }

    public function getAllUnidadMedidaTipoCombo() {
        return BienNegocio::create()->getAllUnidadMedidaTipoCombo();
    }

    public function getComboEmpresaAll() {
        return EmpresaNegocio::create()->getAllEmpresaByUsuarioId(null);
    }

    /*
     * funcion para importar un excel
     */

    public function importBien() {
        $this->setTransaction();
        $error_xml = false;

        $file = $this->getParametro("file");
        $usuarioCreacion = $this->getUsuarioId();
        $empresaId = $this->getParametro("empresa_id");

        $decode = Util::base64ToImage($file);

        $direccion = __DIR__ . '/../../util/formatos/subida.xls';
        if (file_exists($direccion)) {
            unlink($direccion);
        }
        file_put_contents($direccion, $decode);


        //validar que la cabecera del excel importado sea el mismo que la cabecera del formato.
        $cabeceraImporte = ImportacionExcel::obtenerCabeceraExcel("formatos/subida.xls", 1);
        $res = $this->getFormatoImportar();

        $cabeceraFormato = ImportacionExcel::obtenerCabeceraExcel("formatos/formato_bien.xls", 0);

        if ($cabeceraImporte != $cabeceraFormato) {
            throw new WarningException("Formato de importación incorrecto.");
        }
        // fin validacion

        if (strlen($file) < 1) {
            throw new WarningException("No se ha seleccionado ningun archivo.");
        }
        $parse = ImportacionExcel::parseExcelToSTD("formatos/subida.xls", $usuarioCreacion, "Bien");
        if (array_key_exists("xml", $parse)) {
            $data = $parse["data"];
            $result = BienNegocio::create()->importaBienXML($parse["xml"], $usuarioCreacion, $empresaId);
            if (strlen($result[0]["errores"]) == 0 || $result[0]["count"] == 0) {
                return "Se importaron correctamente todas las filas";
            } else {
                $bien = "Se detectaron " . $result[0]["count"] . " filas con errores";
                $errores = $bien . "<br><br>No fue posible importar una o varias filas:<br>";
                $json = $result[0]["errores"];
                $json = str_replace("IDENT_INIT,", "", $json);
                $err = json_decode($json, true);
                $excel = ImportacionExcel::getExcelwithErrors($err, "formato_bien", $data);
                if (strlen($excel) > 0) {
                    $errores .= "<br><p><a href='util/$excel'>"
                            . "<div class='alert alert-danger' style='cursor : pointer; text-align:center;'>Descarge el documento de errores con el detalle aquí</div>"
                            . "</a></p>";
                }
                return $errores;
            }
        } else {
            if ($errores !== "") {
                $errores = $bien . "<br><br>No fue posible importar una o varias filas:<br>";
                $excel = ImportacionExcel::getExcelwithErrors($parse, "formato_bien");
                if (strlen($excel) > 0) {
                    $errores .= "<br><p><a href='util/$excel'>"
                            . "<div class='alert alert-danger' style='cursor : pointer; text-align:center;'>Descarge el documento de errores con el detalle aquí</div>"
                            . "</a></p>";
                }
                return $errores;
            }
        }
    }

    public function getFormatoImportar() {
        $base = __DIR__ . '/../../util/formatos/formato_bien_base.xls';
        $path = __DIR__ . '/../../util/formatos/formato_bien.xls';
        $objPHPExcel = PHPExcel_IOFactory::load($base);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $colProv = "N"; //antes L
        $organizadores = OrganizadorNegocio::create()->getDataOrganizador();
        foreach ($organizadores as $organizador) {
            if ($organizador["organizador_padre"] != null) {
                $nombre = "Stock" . $organizador["a_descripcion"];
                $objWorksheet->insertNewColumnBefore($colProv, 1);
                $objWorksheet->duplicateStyle($objWorksheet->getStyle('M1'), $colProv . '1'); //antes K1
                $objWorksheet->getCell($colProv . "1")->setValue($nombre);
                $objWorksheet->getColumnDimension($colProv)->setWidth(15);
                $colProv++;
            }
        }

        $moneda = MonedaNegocio::create()->obtenerComboMoneda();
        $monedaLista = '';
        foreach ($moneda as $item) {
            $descripcion = $item['descripcion'];
            if ($monedaLista != '') {
                $descripcion = ',' . $descripcion;
            }
            $monedaLista = $monedaLista . $descripcion;
        }
        $monedaLista = '"' . $monedaLista . '"';

        for ($i = 2; $i <= 50; $i++) {
            $objValidation = $objPHPExcel->getActiveSheet()->getCell('K' . $i)->getDataValidation();
            $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
            $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
            $objValidation->setAllowBlank(false);
            $objValidation->setShowInputMessage(true);
            $objValidation->setShowErrorMessage(true);
            $objValidation->setShowDropDown(true);
            $objValidation->setErrorTitle('Error');
            $objValidation->setError('El valor no está en la lista.');
//           $objValidation->setPromptTitle('Pick from list');
//           $objValidation->setPrompt('Please pick a value from the drop-down list.');
//           $objValidation->setFormula1('"Item A,Item B,Item C"');
            $objValidation->setFormula1($monedaLista);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save($path);
        return true;
    }

    public function ExportarBienExcel() {
        $usuarioId = $this->getUsuarioId();
        $empresaId = $this->getParametro("empresaId");
        return BienNegocio::create()->ExportarBienExcel($usuarioId, $empresaId);
    }

    ///motivo de salida del bien 
    public function getDataGridBienMotivoSalida() {
        return BienNegocio::create()->getDataBienMotivoSalida();
    }

    public function insertBienMotivoSalida() {
        $descripcion = $this->getParametro("descripcion");
        $codigo = $this->getParametro("codigo");
        $comentario = $this->getParametro("comentario");
        $estado = $this->getParametro("estado");
        $usu_creacion = $this->getUsuarioId();
        return BienNegocio::create()->insertBienMotivoSalida($codigo, $descripcion, $comentario, $estado, $usu_creacion);
    }

    public function getBienMotivoSalida() {
        $id = $this->getParametro("id");
        return BienNegocio::create()->getBienMotivoSalida($id);
    }

    public function updateBienMotivoSalida() {
        $id = $usu_nombre = $this->getParametro("id");
        $descripcion = $this->getParametro("descripcion");
        $codigo = $this->getParametro("codigo");
        $comentario = $this->getParametro("comentario");
        $estado = $this->getParametro("estado");
        return BienNegocio::create()->updateBienMotivoSalida($id, $descripcion, $codigo, $comentario, $estado);
    }

    public function deleteBienMotivoSalida() {
        $id = $this->getParametro("id");
        $nom = $this->getParametro("nom");
        return BienNegocio::create()->deleteBienMotivoSalida($id, $nom);
    }

    public function cambiarBienMotivoSalidaEstado() {
        $id_estado = $this->getParametro("id_estado");
        return BienNegocio::create()->cambiarBienMotivoSalidaEstado($id_estado);
    }

    // modal proveedores
    public function obtenerComboProveedores() {
        //$data=PersonaNegocio::create()->obtenerComboPersonaXPersonaClaseId(-1);
        return PersonaNegocio::create()->obtenerComboPersonaProveedores();
    }

    // bien tipo padre
    public function obtenerBienTipoPadres() {
        return BienNegocio::create()->obtenerBienTipoPadres();
    }

    public function obtenerBienTipoPadresDisponibles() {
        $bienTipoId = $this->getParametro("bienTipoId");
        $data = BienNegocio::create()->obtenerBienTipoPadresDisponibles($bienTipoId);
        return $data;
    }

    public function obtenerConfiguracionesInicialesBienTipo() {
        $bienTipoId = $this->getParametro("bienTipoId");
        return BienNegocio::create()->obtenerConfiguracionesInicialesBienTipo($bienTipoId);
    }

    public function obtenerBienTipoXId() {
        $bienTipoId = $this->getParametro("bienTipoId");
        return BienNegocio::create()->obtenerConfiguracionesInicialesBienTipo($bienTipoId);
    }
}
