<?php
require_once __DIR__ . '/../../modeloNegocio/contabilidad/LibroMayorNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/LibroDiarioNegocio.php';
// require_once __DIR__ . '/../../modeloNegocio/almacen/MovimientoNegocio.php';
require_once __DIR__ . '/../core/ControladorBase.php';
// require_once __DIR__ . '/../../util/ImportacionExcel.php';
// require_once __DIR__ . '/../../util/PHPExcel/PHPExcel/IOFactory.php';

class LibroMayorControlador extends ControladorBase
{
  public function obtenerConfiguracionInicialLibroMayorAuxiliar()
  {
    $empresaId = $this->getParametro("id_empresa");
    return LibroMayorNegocio::create()->obtenerConfiguracionInicialLibroMayorAuxiliar($empresaId);
  }

  public function obtenerConfiguracionInicialLibroMayorGeneral()
  {
    $empresaId = $this->getParametro("id_empresa");
    return LibroMayorNegocio::create()->obtenerConfiguracionInicialLibroMayorGeneral($empresaId);
  }

  public function listarLibroMayorXCriterios()
  {
    $criterios = $this->getParametro("criterios");
    return LibroMayorNegocio::create()->listarLibroMayorXCriterios($criterios);
  }

  public function exportarLibroMayorAuxiliar()
  {
    $criterios = $this->getParametro("criterios");
    $repuesta = LibroMayorNegocio::create()->obtenerLibroMayorAuxiliarExcel($criterios);
    return $repuesta;
  }

  public function exportarLibroMayorGeneral()
  {
    $criterios = $this->getParametro("criterios");
    $tipo = $this->getParametro("tipo");
    if ($tipo == 'excel') {
      $repuesta = LibroMayorNegocio::create()->obtenerLibroMayorGeneralExcel($criterios);
    } elseif ($tipo == 'txt') {
      $repuesta = LibroMayorNegocio::create()->obtenerLibroMayorGeneralTxt($criterios);
    }
    return $repuesta;
  }

  public function listarLibroDiarioXCriterios()
  {
    $criterios = $this->getParametro("criterios");
    return LibroDiarioNegocio::create()->listarLibroDiarioXCriterios($criterios);
  }

  public function obtenerContVoucher()
  {
    $voucherId = $this->getParametro("voucher_id");
    $data = new stdClass();
    $data->dataVoucher = ContVoucherNegocio::create()->obtenerContVoucherXId($voucherId);
    $data->dataVoucherDetalle = ContVoucherDetalle::create()->obtenerContVoucherDetalleXVoucherId($voucherId);
    return $data;
  }
}
