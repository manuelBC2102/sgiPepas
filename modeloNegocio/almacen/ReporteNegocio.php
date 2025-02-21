<?php

require_once __DIR__ . '/../../modelo/almacen/Reporte.php';
require_once __DIR__ . '/OrganizadorNegocio.php';
require_once __DIR__ . '/DocumentoTipoNegocio.php';
require_once __DIR__ . '/BienNegocio.php';
require_once __DIR__ . '/DocumentoNegocio.php';
require_once __DIR__ . '/OrganizadorNegocio.php';
require_once __DIR__ . '/PersonaNegocio.php';
require_once __DIR__ . '/EmpresaNegocio.php';
require_once __DIR__ . '/BienPrecioNegocio.php';
require_once __DIR__ . '/DocumentoTipoDatoListaNegocio.php';
require_once __DIR__ . '/MovimientoNegocio.php';
require_once __DIR__ . '/OperacionNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../util/PHPExcel/PHPExcel.php';
require_once __DIR__ . '/GraficoNegocio.php';

class ReporteNegocio extends ModeloNegocioBase
{

    private $estiloTituloReporte, $estiloTituloColumnas, $estiloInformacion;

    /**
     *
     * @return ReporteNegocio
     */
    static function create()
    {
        return parent::create();
    }

    public function obtenerConfiguracionInicial($idEmpresa)
    {

        $respuesta = new ObjectUtil();
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXEmpresa($idEmpresa);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        return $respuesta;
    }

    public function reporteBalance($criterios, $elementosFiltrados, $orden, $columnas, $tamanio)
    {

        $tipoDocumentoId = $criterios[0]['tipoDocumento'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);
        $vencimientoInicio = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['inicio']);
        $vencimientoFin = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['fin']);
        $bandera = $criterios[0]['bandera']; // si es 0 es balance, si es 1 es cantidad
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $columnaOrdenarIndice = $orden[0]['column'];
        $formaOrdenar = $orden[0]['dir'];

        $columnaOrdenar = $columnas[$columnaOrdenarIndice]['data'];

        $respuesta = new ObjectUtil();

        $respuesta->data = Reporte::create()->reporteBalance($tipoDocumentoIdFormateado, $emisionInicio, $emisionFin, $vencimientoInicio, $vencimientoFin, $elementosFiltrados, $formaOrdenar, $columnaOrdenar, $tamanio, $bandera);

        $respuesta->contador = Reporte::create()->totalReporteBalance($tipoDocumentoIdFormateado, $emisionInicio, $emisionFin, $vencimientoInicio, $vencimientoFin, $formaOrdenar, $columnaOrdenar, $bandera);

        return $respuesta;
    }

    public function obtenerConfiguracionesInicialesKardex($idEmpresa)
    {

        $respuesta = new ObjectUtil();
        $respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivoXEmpresa($idEmpresa);
        $respuesta->bien = BienNegocio::create()->obtenerBienKardexXEmpresa($idEmpresa);
        //        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipoKardexXEmpresa($idEmpresa);//antes
        $respuesta->bien_tipo = BienTipo::create()->obtener();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        return $respuesta;
    }

    public function obtenerConfiguracionesInicialesRankingServicios($idEmpresa)
    {

        $respuesta = new ObjectUtil();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        return $respuesta;
    }

    public function obtenerConfiguracionesInicialesEntradaSalidaAlmacen($idEmpresa)
    {

        $respuesta = new ObjectUtil();
        $respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivoXEmpresa($idEmpresa); //descomentar por empresa
        //        $respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivo(56);// todas
        $respuesta->bien = BienNegocio::create()->obtenerBienKardexXEmpresa($idEmpresa);
        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipoKardexXEmpresa($idEmpresa);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXEmpresa($idEmpresa);
        //  $respuesta->tipo_frecuencia = DocumentoTipoDatoListaNegocio::create()->obtenerXDocumentoTipoDato(505);;
        return $respuesta;
    }

    public function obtenerConfiguracionesInicialesEntradaSalidaAlmacenVirtual()
    {

        $respuesta = new ObjectUtil();
        //$respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivoXEmpresa($idEmpresa); //descomentar por empresa
        $respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivo(56); // todas
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->bien = BienNegocio::create()->obtenerActivos(null);
        return $respuesta;
    }

    public function obtenerConfiguracionesInicialesRankingColaboradores($idEmpresa)
    {

        $respuesta = new ObjectUtil();
        $respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivoXEmpresa($idEmpresa);
        $respuesta->bien = BienNegocio::create()->obtenerBienKardexXEmpresa($idEmpresa);
        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipoKardexXEmpresa($idEmpresa);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->persona_tipo = PersonaNegocio::create()->getAllPersonaTipo();
        return $respuesta;
    }

    public function obtenerConfiguracionesInicialesServiciosAtendidos($idEmpresa)
    {
        $respuesta = new ObjectUtil();
        $respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivoXEmpresa($idEmpresa);
        $respuesta->bien = BienNegocio::create()->obtenerServicioXEmpresa($idEmpresa);
        //$respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipoKardexXEmpresa($idEmpresa);
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXEmpresa($idEmpresa);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        return $respuesta;
    }

    //    public function reporteKardex($criterios,$elementosFiltrados,$orden,$columnas,$tamanio) {
    public function reporteKardex($criterios)
    {

        $organizadorId = $this->obtenerHijosOrganizador($criterios[0]['organizador']);
        $bienId = $criterios[0]['bien'];
        $bienTipoId = $criterios[0]['bienTipo'];

        //obtener bien tipo hijos
        if (!ObjectUtil::isEmpty($bienTipoId)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipoId);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipoId, $item['id']);
            }
        }
        //fin

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $empresaId = $criterios[0]['empresaId'];

        $organizadorIdFormateado = Util::convertirArrayXCadena($organizadorId);
        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $BienTipoIdFormateado = Util::convertirArrayXCadena($bienTipoId);

        return Reporte::create()->reporteKardex($organizadorIdFormateado, $bienIdFormateado, $BienTipoIdFormateado, $emisionInicio, $emisionFin, $empresaId);
    }

    public function reporteBienesMayorRotacion($criterios)
    {

        $organizadorId = $this->obtenerHijosOrganizador($criterios[0]['organizador']);
        $bienId = $criterios[0]['bien'];
        $bienTipoId = $criterios[0]['bienTipo'];

        //obtener bien tipo hijos
        if (!ObjectUtil::isEmpty($bienTipoId)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipoId);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipoId, $item['id']);
            }
        }
        //fin	

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $empresaId = $criterios[0]['empresaId'];

        $organizadorIdFormateado = Util::convertirArrayXCadena($organizadorId);
        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $BienTipoIdFormateado = Util::convertirArrayXCadena($bienTipoId);

        return Reporte::create()->reporteBienesMayorRotacion($organizadorIdFormateado, $bienIdFormateado, $BienTipoIdFormateado, $emisionInicio, $emisionFin, $empresaId);
    }

    // 

    public function reporteComprometidosDia($criterios)
    {

        $organizadorId = $this->obtenerHijosOrganizador($criterios[0]['organizador']);
        $bienId = $criterios[0]['bien'];
        $bienTipoId = $criterios[0]['bienTipo'];

        //obtener bien tipo hijos
        if (!ObjectUtil::isEmpty($bienTipoId)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipoId);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipoId, $item['id']);
            }
        }
        //fin	

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $empresaId = $criterios[0]['empresaId'];

        $organizadorIdFormateado = Util::convertirArrayXCadena($organizadorId);
        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $BienTipoIdFormateado = Util::convertirArrayXCadena($bienTipoId);

        return Reporte::create()->reporteComprometidosDia($organizadorIdFormateado, $bienIdFormateado, $BienTipoIdFormateado, $emisionInicio, $emisionFin, $empresaId);
    }

    public function reporteRankingServicios($criterios)
    {
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $empresaId = $criterios[0]['empresaId'];

        return Reporte::create()->reporteRankingServicios($emisionInicio, $emisionFin, $empresaId);
    }

    public function reporteDetalleEntradaSalidaAlmacen($criterios, $indicador)
    {

        $organizadorId = $this->obtenerHijosOrganizador($criterios[0]['organizador']);
        $bienId = $criterios[0]['bien'];
        $bienTipoId = $criterios[0]['bienTipo'];

        $emisionInicio = $criterios[0]['fechaEmision']['inicio'];
        $emisionFin = $criterios[0]['fechaEmision']['fin'];

        $empresaId = $criterios[0]['empresaId'];

        $organizadorIdFormateado = Util::convertirArrayXCadena($organizadorId);
        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $BienTipoIdFormateado = Util::convertirArrayXCadena($bienTipoId);

        return Reporte::create()->reporteDetalleEntradaSalidaAlmacen($organizadorIdFormateado, $bienIdFormateado, $BienTipoIdFormateado, $emisionInicio, $emisionFin, $empresaId, $indicador);
    }

    public function reporteEntradaSalidaAlmacen($criterios)
    {

        $organizadorId = $this->obtenerHijosOrganizador($criterios[0]['organizador']);
        $bienId = $criterios[0]['bien'];
        $bienTipoId = $criterios[0]['bienTipo'];
        //obtener bien tipo hijos
        if (!ObjectUtil::isEmpty($bienTipoId)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipoId);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipoId, $item['id']);
            }
        }
        //fin	
        $documentoTipoId = $criterios[0]['documentoTipo'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $empresaId = $criterios[0]['empresaId'];

        $organizadorIdFormateado = Util::convertirArrayXCadena($organizadorId);
        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $BienTipoIdFormateado = Util::convertirArrayXCadena($bienTipoId);
        $documentoTipoIdFormateado = Util::convertirArrayXCadena($documentoTipoId);

        return Reporte::create()->reporteEntradaSalidaAlmacen($organizadorIdFormateado, $bienIdFormateado, $BienTipoIdFormateado, $emisionInicio, $emisionFin, $empresaId, $documentoTipoIdFormateado);
    }

    public function obtenerDataEntradaSalidaAlmacenVirtualXCriterios($criterios)
    {

        $organizadorOrigenId = $this->obtenerHijosOrganizador($criterios[0]['origen']);
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $organizadorOrigenIdFormateado = Util::convertirArrayXCadena($organizadorOrigenId);
        $productoIdFormateado = Util::convertirArrayXCadena($criterios[0]['producto']);

        return Reporte::create()->obtenerDataEntradaSalidaAlmacenVirtualXCriterios($organizadorOrigenIdFormateado, $productoIdFormateado, $emisionInicio, $emisionFin);
    }

    public function obtenerDataEntradaSalidaAlmacenVirtualDetalle($documentoId, $bienId)
    {
        return Reporte::create()->obtenerDataEntradaSalidaAlmacenVirtualDetalle($documentoId, $bienId);
    }

    public function reporteDispersionBienes($criterios)
    {

        $organizadorId = $this->obtenerHijosOrganizador($criterios[0]['organizador']);
        $bienId = $criterios[0]['bien'];
        $bienTipoId = $criterios[0]['bienTipo'];

        //obtener bien tipo hijos
        if (!ObjectUtil::isEmpty($bienTipoId)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipoId);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipoId, $item['id']);
            }
        }
        //fin	

        $documentoTipoId = $criterios[0]['documentoTipo'];
        $tipoFrecuenciaId = $criterios[0]['tipoFrecuencia'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        //$empresaId = $criterios[0]['empresaId']; descomentar para busqueda por empresa
        $empresaId = null;

        if ($tipoFrecuenciaId == 1) {
            $dias = (strtotime($emisionInicio) - strtotime($emisionFin)) / 86400;
            $dias = abs($dias);
            $dias = floor($dias);

            if ($dias > 30) {
                throw new WarningException("Intérvalo de días superior a 30");
            }
        }
        if ($tipoFrecuenciaId == 2) {
            $meses = (strtotime($emisionInicio) - strtotime($emisionFin)) / (86400 * 30);
            $meses = abs($meses);
            $meses = floor($meses);

            if ($meses > 30) {
                throw new WarningException("Intérvalo de meses superior a 30");
            }
        }


        $organizadorIdFormateado = Util::convertirArrayXCadena($organizadorId);
        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $BienTipoIdFormateado = Util::convertirArrayXCadena($bienTipoId);
        $documentoTipoIdFormateado = Util::convertirArrayXCadena($documentoTipoId);

        //$reporte=Reporte::create()->reporteDispersionBienes($organizadorIdFormateado, $bienIdFormateado, $BienTipoIdFormateado, $emisionInicio, $emisionFin, $empresaId,$documentoTipoIdFormateado);
        return Reporte::create()->reporteDispersionBienes($organizadorIdFormateado, $bienIdFormateado, $BienTipoIdFormateado, $emisionInicio, $emisionFin, $empresaId, $documentoTipoIdFormateado, $tipoFrecuenciaId);
    }

    public function reporteEntradaSalida($criterios)
    {

        $organizadorId = $this->obtenerHijosOrganizador($criterios[0]['organizador']);
        $bienId = $criterios[0]['bien'];
        $bienTipoId = $criterios[0]['bienTipo'];
        //obtener bien tipo hijos
        if (!ObjectUtil::isEmpty($bienTipoId)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipoId);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipoId, $item['id']);
            }
        }
        //fin	
        $documentoTipoId = $criterios[0]['documentoTipo'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $empresaId = $criterios[0]['empresaId'];

        $organizadorIdFormateado = Util::convertirArrayXCadena($organizadorId);
        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $BienTipoIdFormateado = Util::convertirArrayXCadena($bienTipoId);
        $documentoTipoIdFormateado = Util::convertirArrayXCadena($documentoTipoId);

        return Reporte::create()->reporteEntradaSalida($organizadorIdFormateado, $bienIdFormateado, $BienTipoIdFormateado, $emisionInicio, $emisionFin, $empresaId, $documentoTipoIdFormateado);
    }

    public function reporteRankingColaboradores($criterios)
    {

        $organizadorId = $this->obtenerHijosOrganizador($criterios[0]['organizador']);
        $bienId = $criterios[0]['bien'];
        $bienTipoId = $criterios[0]['bienTipo'];
        $personaTipoId = $criterios[0]['personaTipo'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $empresaId = $criterios[0]['empresaId'];

        $organizadorIdFormateado = Util::convertirArrayXCadena($organizadorId);
        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $BienTipoIdFormateado = Util::convertirArrayXCadena($bienTipoId);
        $personaTipoIdFormateado = Util::convertirArrayXCadena($personaTipoId);

        return Reporte::create()->reporteRankingColaboradores($organizadorIdFormateado, $bienIdFormateado, $BienTipoIdFormateado, $emisionInicio, $emisionFin, $empresaId, $personaTipoIdFormateado);
    }

    public function reporteServicios($criterios)
    {

        $organizadorId = $this->obtenerHijosOrganizador($criterios[0]['organizador']);
        $bienId = $criterios[0]['bien'];
        $documentoTipoId = $criterios[0]['documentoTipo'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $empresaId = $criterios[0]['empresaId'];

        $organizadorIdFormateado = Util::convertirArrayXCadena($organizadorId);
        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $documentoTipoIdFormateado = Util::convertirArrayXCadena($documentoTipoId);

        //return Reporte::create()->reporteKardex($organizadorIdFormateado, $bienIdFormateado, $BienTipoIdFormateado, $emisionInicio, $emisionFin, $empresaId);
        return Reporte::create()->reporteServiciosAtendidos($organizadorIdFormateado, $bienIdFormateado, $documentoTipoIdFormateado, $emisionInicio, $emisionFin, $empresaId);
    }

    public function obtenerDetalleKardex($idBien, $idOrganizador, $fechaInicio, $fechaFin)
    {
        return Reporte::create()->obtenerDetalleKardex($idBien, $idOrganizador, $fechaInicio, $fechaFin);
    }

    public function obtenerDocumentoServicios($idBien, $fechaInicio, $fechaFin)
    {
        return Reporte::create()->obtenerDocumentoServicios($idBien, $fechaInicio, $fechaFin);
    }

    public function obtenerDetalleBienesMayorRotacion($idBien, $idOrganizador, $idUnidadMedida, $fechaInicio, $fechaFin)
    {
        return Reporte::create()->obtenerDetalleBienesMayorRotacion($idBien, $idOrganizador, $idUnidadMedida, $fechaInicio, $fechaFin);
    }

    public function obtenerDetalleComprometidosDia($idBien, $fechaInicio, $fechaFin, $empresaId)
    {
        return Reporte::create()->obtenerDetalleComprometidosDia($idBien, $fechaInicio, $fechaFin, $empresaId);
    }

    public function obtenerDetalleRankingServicios($idBien, $fechaInicio, $fechaFin)
    {
        return Reporte::create()->obtenerDetalleRankingServicios($idBien, $fechaInicio, $fechaFin);
    }

    // funciones extras

    private function formatearFechaBD($cadena)
    {
        if (!ObjectUtil::isEmpty($cadena)) {
            return DateUtil::formatearCadenaACadenaBD($cadena);
        }
        return "";
    }

    public function obtenerHijosOrganizador($arrayOrganizadores)
    {
        $arrayOrganizador = array();
        foreach ($arrayOrganizadores as $organizador) {

            $responseOrganizador = OrganizadorNegocio::create()->organizadorEsPadre($organizador, "");
            array_push($arrayOrganizador, $organizador);
            if (!ObjectUtil::isEmpty($responseOrganizador)) {
                if ($responseOrganizador[0]['vout_exito'] == 1) {
                    if (!ObjectUtil::isEmpty($responseOrganizador[0]['hijo'])) {
                        $arrayHijos = explode(';', $responseOrganizador[0]['hijo']);
                        foreach ($arrayHijos as $hijo) {
                            array_push($arrayOrganizador, $hijo);
                        }
                    }
                }
            }
        }

        if (!ObjectUtil::isEmpty($arrayOrganizador)) {
            return array_unique($arrayOrganizador);
        }

        return $arrayOrganizador;
    }

    //Reporte de deudas
    public function obtenerReporteDeudaXCriterios($tipo1, $tipo2, $criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $personaId = (ObjectUtil::isEmpty($personaId) || $personaId * 1 == -1) ? null : $personaId;
        $mostrarPagados = $criterios[0]['mostrar'];
        $mostrarLib = $criterios[0]['mostrarLib'];
        if ($criterios[0]['fechaVencimientoDesde'] != '') {
            $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaVencimientoDesde']);
        }

        if ($criterios[0]['fechaVencimientoHasta'] != '') {
            $fechaVencimientoHasta = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaVencimientoHasta']);
        }
        $empresa = $criterios[0]['empresa'];
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];

        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        //        $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);;
        //        echo "$mostrarPagados,$mostrarLib, $tipo1, $tipo2, $empresa, $personaId, $fechaVencimientoDesde, $fechaVencimientoHasta, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start";
        return Reporte::create()->obtenerReporteDeudaXCriterios($mostrarPagados, $mostrarLib, $tipo1, $tipo2, $empresa, $personaId, $fechaVencimientoDesde, $fechaVencimientoHasta, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadReporteDeudaXCriterio($tipo1, $tipo2, $criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $personaId = (ObjectUtil::isEmpty($personaId) || $personaId * 1 == -1) ? null : $personaId;
        $mostrarPagados = $criterios[0]['mostrar'];
        $mostrarLib = $criterios[0]['mostrarLib'];
        if ($criterios[0]['fechaVencimientoDesde'] != '') {
            $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaVencimientoDesde']);
        }
        if ($criterios[0]['fechaVencimientoHasta'] != '') {
            $fechaVencimientoHasta = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaVencimientoHasta']);
        }
        $empresa = $criterios[0]['empresa'];
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];

        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteDeudaXCriterios($mostrarPagados, $mostrarLib, $tipo1, $tipo2, $empresa, $personaId, $fechaVencimientoDesde, $fechaVencimientoHasta, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    //Reporte de deudas general
    public function obtenerConfiguracionesInicialesDeudaGeneral()
    {
        $response->persona = PersonaNegocio::create()->obtenerActivas();
        $response->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        return $response;
    }

    public function obtenerReporteDeudaGeneralXCriterios($tipo1, $tipo2, $criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $mostrar = $criterios[0]['mostrar'];
        $fecha = '';
        $empresa = Util::convertirArrayXCadena($criterios[0]['empresa']);

        if ($criterios[0]['fecha'] != '') {
            $fecha = DateUtil::formatearCadenaACadenaBD($criterios[0]['fecha']);
        }

        //$empresa = $criterios[0]['empresa'];
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];

        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        //        $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);;

        return Reporte::create()->obtenerReporteDeudaGeneralXCriterios($mostrar, $tipo1, $tipo2, $empresa, $personaId, $fecha, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadReporteDeudaGeneralXCriterio($tipo1, $tipo2, $criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $mostrar = $criterios[0]['mostrar'];
        $fecha = '';
        if ($criterios[0]['fecha'] != '') {
            $fecha = DateUtil::formatearCadenaACadenaBD($criterios[0]['fecha']);
        }

        $empresa = Util::convertirArrayXCadena($criterios[0]['empresa']);
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteDeudaGeneralXCriterios($mostrar, $tipo1, $tipo2, $empresa, $personaId, $fecha, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerReporteBalanceExcel($criterios)
    {

        $tipoDocumentoId = $criterios[0]['tipoDocumento'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);
        $vencimientoInicio = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['inicio']);
        $vencimientoFin = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['fin']);

        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $respuestaReporteBalanceExcel = Reporte::create()->reporteBalanceExcel($tipoDocumentoIdFormateado, $emisionInicio, $emisionFin, $vencimientoInicio, $vencimientoFin);

        if (ObjectUtil::isEmpty($respuestaReporteBalanceExcel)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteBalanceExcel($respuestaReporteBalanceExcel, "Reporte balance");
        }
    }

    private function crearReporteBalanceExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':G' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':G' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Fecha de emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Tipo de documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Persona');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Serie');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Numero');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Fecha Vencimiento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Importe');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':G' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, DateUtil::formatearFechaBDAaCadenaVw($reporte['fecha_emision']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['persona_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['serie']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, DateUtil::formatearFechaBDAaCadenaVw($reporte['fecha_vencimiento']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['importe']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':G' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'G'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function obtenerReporteServiciosAtendidosExcel($criterios, $tipo)
    {

        $respuestaReporteServiciosAtendidosExcel = $this->reporteServicios($criterios);

        if (ObjectUtil::isEmpty($respuestaReporteServiciosAtendidosExcel)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            if ($tipo == 1) {
                $this->crearReporteServiciosAtendidosExcel($respuestaReporteServiciosAtendidosExcel, "Reporte Servicios Atendidos");
            } else {
                $this->crearReporteServiciosAtendidosExcel($respuestaReporteServiciosAtendidosExcel, "Reporte Servicios Atendidos General");
            }
        }
    }

    private function crearReporteServiciosAtendidosExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':C' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':C' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Código');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Servicio');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Atenciones');
        //$objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Unidad de medida');
        //$objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Stock');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':C' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_codigo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['cantidad']);
            //$objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['unidad_medida_descripcion']);
            //$objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['stock']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':C' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'E'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function obtenerReporteBienesMayorRotacionExcel($criterios)
    {

        $respuestaBienesMayorRotacionExcel = $this->reporteBienesMayorRotacion($criterios);

        if (ObjectUtil::isEmpty($respuestaBienesMayorRotacionExcel)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteBienesMayorRotacionExcel($respuestaBienesMayorRotacionExcel, "Reporte Bienes Mayor Rotación");
        }
    }

    public function obtenerReporteRankingColaboradoresExcel($criterios)
    {

        $respuestaRankingColaboradoresExcel = $this->reporteRankingColaboradores($criterios);

        if (ObjectUtil::isEmpty($respuestaRankingColaboradoresExcel)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteRankingColaboradoresExcel($respuestaRankingColaboradoresExcel, "Reporte Raking de Colaboradores");
        }
    }

    private function crearReporteRankingColaboradoresExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':E' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* Colaborador	F. Entrada	F. Salida	F. Total */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'N°');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Colaborador');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'F. Entrada');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'F. Salida');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'F. Total');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $index => $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $index + 1);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['persona_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['frecuencia_ingreso']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['frecuencia_salida']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['frecuencia_total']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'E'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function obtenerReporteRankingServiciosExcel($criterios)
    {

        $respuestaRankingServiciosExcel = $this->reporteRankingServicios($criterios);

        if (ObjectUtil::isEmpty($respuestaRankingServiciosExcel)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteRankingServiciosExcel($respuestaRankingServiciosExcel, "Reporte Ranking de Servicios");
        }
    }

    private function crearReporteRankingServiciosExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':B' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* Servicio	Cantidad	Opciones */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Servicio');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Cantidad Bienes');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':B' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['cantidad_bienes']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':B' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'B'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function obtenerReporteComprometidosDiaExcel($criterios)
    {

        $respuestaComprometidosDiaExcel = $this->reporteComprometidosDia($criterios);

        if (ObjectUtil::isEmpty($respuestaComprometidosDiaExcel)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteComprometidosDiaExcel($respuestaComprometidosDiaExcel, "Reporte comprometidos en el día");
        }
    }

    private function crearReporteComprometidosDiaExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':E' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* F. Emisión	Bien	Bien Tipo	Unidad Medida	Cantidad */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Bien');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Bien Tipo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Unidad de medida');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Cantidad');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_emision']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['unidad_control_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['cantidad_control']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'E'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function obtenerReporteEntradaSalidaExcel($criterios)
    {

        $respuestaEntradaSalidaExcel = $this->reporteEntradaSalida($criterios);

        if (ObjectUtil::isEmpty($respuestaEntradaSalidaExcel)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteEntradaSalidaExcel($respuestaEntradaSalidaExcel, "Reporte Entrada Salida");
        }
    }

    private function crearReporteEntradaSalidaExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':I' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* F. Creación	F. Emisión	Documento Tipo	Numero	Organizador	Bien	Bien Tipo	Unidad Medida	Cantidad
         */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'F. Creación');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Documento Tipo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Organizador');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Bien');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Bien Tipo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Unidad Medida');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Cantidad');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_creacion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['fecha_emision']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['documento_numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['organizador_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['unidad_control_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $reporte['cantidad_control']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'I'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function obtenerReporteEntradaSalidaAlmacenExcel($criterios)
    {

        $respuestaEntradaSalidaAlmacenExcel = $this->reporteEntradaSalidaAlmacen($criterios);

        if (ObjectUtil::isEmpty($respuestaEntradaSalidaAlmacenExcel)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteEntradaSalidaAlmacenExcel($respuestaEntradaSalidaAlmacenExcel, "Reporte Entrada Salida Almacen");
        }
    }

    private function crearReporteEntradaSalidaAlmacenExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':C' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':C' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* Organizador	Tipo Frecuencia	Frecuencia	Opciones
         */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Organizador');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Tipo Frecuencia');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Frecuencia');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':C' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['organizador_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['tipo_frecuencia']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['frecuencia']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':C' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'C'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    private function crearReporteBienesMayorRotacionExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':F' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Bien');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Organizador');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Tipo bien');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Unidad de medida');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Stock');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Frecuencia');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['organizador_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['unidad_medida_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['stock']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['frecuencia']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'F'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function obtenerReporteKardexExcel($criterios, $tipo)
    {

        $respuestaReporteKardexExcel = $this->reporteKardex($criterios);

        if (ObjectUtil::isEmpty($respuestaReporteKardexExcel)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            if ($tipo == 1) {
                $this->crearReporteKardexExcel($respuestaReporteKardexExcel, "Reporte inventario");
            } else {
                $this->crearReporteKardexExcel($respuestaReporteKardexExcel, "Reporte inventario general");
            }
        }
    }

    private function crearReporteKardexExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':E' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Bien');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Organizador');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Tipo bien');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Unidad de medida');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Stock');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['organizador_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['unidad_medida_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['stock']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'E'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function obtenerConfiguracionesInicialesReporteXOrganizador($idEmpresa)
    {

        $respuesta = new ObjectUtil();
        $respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivoXEmpresa($idEmpresa);
        $respuesta->bien = BienNegocio::create()->obtenerBienKardexXEmpresa($idEmpresa);
        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipoKardexXEmpresa($idEmpresa);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        return $respuesta;
    }

    public function ReporteXOrganizador($criterios)
    {

        $dataRespuesta = $this->reporteKardex($criterios);

        $tamanho = count($dataRespuesta);

        for ($i = 0; $i < $tamanho; $i++) {
            $precioCompra = BienPrecioNegocio::create()->obtenerPrecioCompra($dataRespuesta[$i]['bien_id']);
            if (ObjectUtil::isEmpty($precioCompra)) {
                $dataRespuesta[$i]['total_monetario'] = 0;
            } else {
                $dataRespuesta[$i]['total_monetario'] = $dataRespuesta[$i]['stock'] * $precioCompra;
            }
        }

        return $dataRespuesta;
    }

    public function obtenerTotalBalance($criterios)
    {

        $tipoDocumentoId = $criterios[0]['tipoDocumento'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);
        $vencimientoInicio = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['inicio']);
        $vencimientoFin = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['fin']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $respuesta = new ObjectUtil();

        $importeTotal = Reporte::create()->importeTotal($tipoDocumentoIdFormateado, $emisionInicio, $emisionFin, $vencimientoInicio, $vencimientoFin);

        $respuesta->total = $importeTotal[0]['SUM(tabla.importe)'];

        return $respuesta;
    }

    private function estilosExcel()
    {

        $this->estiloTituloReporte = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'strike' => false,
                'size' => 14
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => FALSE
            )
        );

        $this->estiloTituloColumnas = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'size' => 12
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

        $this->estiloInformacion = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 10
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_HAIR,
                    'color' => array(
                        'rgb' => '000000'
                    )
                )
            ),
        );

        $this->estiloNumInformacion = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 10
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

        $this->estiloTextoInformacion = array(
            'font' => array(
                'name' => 'Arial',
                'size' => 10
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
    }

    public function obtenerConfiguracionesInicialesBalanceConsolidado($idEmpresa, $idTipos)
    {

        $respuesta = new ObjectUtil();
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXEmpresaXTipo($idEmpresa, $idTipos);
        $respuesta->persona = PersonaNegocio::create()->obtenerActivas();
        return $respuesta;
    }

    //Reporte de balance consolidado

    public function obtenerReporteBalanceConsolidadoXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['tipoDocumento'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);
        $vencimientoInicio = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['inicio']);
        $vencimientoFin = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['fin']);
        $empresa = $criterios[0]['empresa'];
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaId = Util::convertirArrayXCadena($empresa);
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        //        $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);;

        return Reporte::create()->obtenerReporteReporteBalanceConsolidadoXCriterios($empresaId, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $vencimientoInicio, $vencimientoFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadReporteBalanceConsolidadoXCriterio($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['tipoDocumento'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);
        $vencimientoInicio = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['inicio']);
        $vencimientoFin = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['fin']);
        $empresa = $criterios[0]['empresa'];
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaId = Util::convertirArrayXCadena($empresa);
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteBalanceConsolidadoXCriterios($empresaId, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $vencimientoInicio, $vencimientoFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadesTotalesBalanceConsolidado($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['tipoDocumento'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);
        $vencimientoInicio = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['inicio']);
        $vencimientoFin = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['fin']);
        $empresa = $criterios[0]['empresa'];
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $respuesta = new ObjectUtil();

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesBalanceConsolidado($empresa, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $vencimientoInicio, $vencimientoFin);

        $respuesta->total = $importeTotal[0]['total'];
        $respuesta->importe_pendiente = $importeTotal[0]['importe_pendiente'];
        $respuesta->importe_pagado = $importeTotal[0]['importe_pagado'];

        return $respuesta;
    }

    //fin de reporte balance consolidado
    //Reporte movimiento vienes
    public function obtenerConfiguracionesInicialesMovimientoPersona($idEmpresa)
    {
        $respuesta = new ObjectUtil();
        //$tipo = '(1)';
        //        $respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivoXEmpresa($idEmpresa);
        //$respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXEmpresaXTipo($idEmpresa, $tipo);
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXEmpresa($idEmpresa);
        $respuesta->persona = PersonaNegocio::create()->obtenerActivas();
        $respuesta->bien = BienNegocio::create()->obtenerBienMovimientoEmpresa($idEmpresa);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();

        return $respuesta;
    }

    public function obtenerMovimientoPersonaXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $bienId = $criterios[0]['bien'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $empresa = $criterios[0]['empresa'];
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaId = Util::convertirArrayXCadena($empresa);
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        //        $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);;

        return Reporte::create()->obtenerMovimientoPersonaXCriterios($empresaId, $tipoDocumentoIdFormateado, $personaId, $bienId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadMovimientoPersonaXCriterio($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $bienId = $criterios[0]['bien'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $empresa = $criterios[0]['empresa'];
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        $empresaId = Util::convertirArrayXCadena($empresa);
        return Reporte::create()->obtenerCantidadMovimientoPersonaXCriterio($empresaId, $tipoDocumentoIdFormateado, $personaId, $bienId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadesTotalesMovimientoPersona($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $bienId = $criterios[0]['bien'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $empresa = $criterios[0]['empresa'];
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaId = Util::convertirArrayXCadena($empresa);

        $respuesta = new ObjectUtil();

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesMovimientoPersona($empresaId, $tipoDocumentoIdFormateado, $personaId, $bienId, $emisionInicio, $emisionFin);

        $respuesta->total = $importeTotal[0]['total'];
        $respuesta->cantidad_total = $importeTotal[0]['cantidad_total'];

        return $respuesta;
    }

    //Fin reporte movimiento bienes
    //Reporte balance consolidado general
    public function obtenerConfiguracionesInicialesBalanceConsolidadoGeneral($idTipos)
    {

        $respuesta = new ObjectUtil();
        //        $respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivoXEmpresa($idEmpresa);
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXTipos($idTipos);
        $respuesta->persona = PersonaNegocio::create()->obtenerActivas();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        //        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        return $respuesta;
    }

    public function obtenerReporteBalanceConsolidadoGeneralXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['tipoDocumento'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);
        $vencimientoInicio = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['inicio']);
        $vencimientoFin = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['fin']);
        $empresa = $criterios[0]['empresa'];
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaId = Util::convertirArrayXCadena($empresa);
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        //        $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);;
        return Reporte::create()->obtenerReporteReporteBalanceConsolidadoXCriterios($empresaId, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $vencimientoInicio, $vencimientoFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadReporteBalanceConsolidadoGeneralXCriterio($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['tipoDocumento'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);
        $vencimientoInicio = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['inicio']);
        $vencimientoFin = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['fin']);
        $empresa = $criterios[0]['empresa'];
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        $empresaId = Util::convertirArrayXCadena($empresa);
        return Reporte::create()->obtenerCantidadReporteBalanceConsolidadoXCriterios($empresaId, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $vencimientoInicio, $vencimientoFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadesTotalesBalanceConsolidadoGeneral($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['tipoDocumento'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);
        $vencimientoInicio = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['inicio']);
        $vencimientoFin = $this->formatearFechaBD($criterios[0]['fechaVencimiento']['fin']);
        $empresa = $criterios[0]['empresa'];
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaId = Util::convertirArrayXCadena($empresa);
        $respuesta = new ObjectUtil();

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesBalanceConsolidado($empresaId, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $vencimientoInicio, $vencimientoFin);

        $respuesta->total = $importeTotal[0]['total'];
        $respuesta->importe_pendiente = $importeTotal[0]['importe_pendiente'];
        $respuesta->importe_pagado = $importeTotal[0]['importe_pagado'];
        return $respuesta;
    }

    //fin reporte balance consolidado general 
    //Reporte movimiento persona 
    public function obtenerConfiguracionesInicialesMovimientoPersonaGeneral()
    {

        $respuesta = new ObjectUtil();
        //        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXTipos($idTipos);        
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXEmpresa(-1);
        $respuesta->persona = PersonaNegocio::create()->obtenerActivas();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->bien = BienNegocio::create()->obtenerBienXMovimientosActivos();
        return $respuesta;
    }

    public function obtenerCantidadesTotalesMovimientoPersonaGeneral($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $bienId = $criterios[0]['bien'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $empresa = $criterios[0]['empresa'];
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaId = Util::convertirArrayXCadena($empresa);
        $respuesta = new ObjectUtil();
        $importeTotal = Reporte::create()->obtenerCantidadesTotalesMovimientoPersona($empresaId, $tipoDocumentoIdFormateado, $personaId, $bienId, $emisionInicio, $emisionFin);
        $respuesta->total = $importeTotal[0]['total'];
        $respuesta->cantidad_total = $importeTotal[0]['cantidad_total'];

        return $respuesta;
    }

    //fin reporte mocimiento persona

    public function obtenerBienesCantMinimaAlcanzada($criterios)
    {
        $bienId = $criterios[0]['bien'];
        $empresaId = $criterios[0]['empresaId'];
        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        return Reporte::create()->obtenerBienesCantMinimaAlcanzada($bienIdFormateado, $empresaId);
    }

    public function obtenerReporteBienesCantMinimaAlcanzadaExcel($criterios)
    {
        $respuestaReporteBienesCantMinimaAlcanzada = $this->obtenerBienesCantMinimaAlcanzada($criterios);
        if (ObjectUtil::isEmpty($respuestaReporteBienesCantMinimaAlcanzada)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteBienesCantMinimaAlcanzadaExcel($respuestaReporteBienesCantMinimaAlcanzada, "Cotización de compra");
        }
    }

    private function crearReporteBienesCantMinimaAlcanzadaExcel($reportes, $titulo)
    {
        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':F' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Bien');
        //        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Organizador');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Tipo bien');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Unidad de medida');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Stock actual');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Stock mínimo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Proveedor');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;
        foreach ($reportes as $reporte) {
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_descripcion']);
            //            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['organizador_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['unidad_medida_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['stock']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['cantidad_minima']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, str_replace('<br/>', "\n", $reporte['proveedor']));
            $objPHPExcel->getActiveSheet()->getStyle('F' . $i)->getAlignment()->setWrapText(true);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloInformacion);
            $i += 1;
        }

        for ($i = 'A'; $i <= 'F'; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);
        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/ReporteBienCantMinAlcanzada.xlsx');
        return 1;
    }

    //reporte ventas por vendedor
    public function obtenerConfiguracionesInicialesVentasPorVendedor()
    {
        $respuesta = new ObjectUtil();
        $tipo = '(1)';
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXTipos($tipo);
        //        $respuesta->persona = PersonaNegocio::create()->obtenerPersonaPerfilVendedor();
        $respuesta->persona = PersonaNegocio::create()->obtenerComboPersonaXPersonaClaseId(-2);
        $respuesta->bien = BienNegocio::create()->obtenerBienMovimientoEmpresa($idEmpresa);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipoPadre();

        return $respuesta;
    }

    public function obtenerCantidadesTotalesVentasPorVendedor($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        //seleccion de bien tipo e hijos        
        $bienTipo = $criterios[0]['bienTipo'];

        if (ObjectUtil::isEmpty($bienTipo)) {
            $bienTipo = $criterios[0]['bienTipoPadre'];
        }

        if (!ObjectUtil::isEmpty($bienTipo)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipo);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipo, $item['id']);
            }
        }
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);
        // fin 

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesVentasPorVendedor($empresaIdFormateado, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $bienTipoIdFormateado);

        //        $respuesta->total = $importeTotal[0]['total'];

        return $importeTotal;
    }

    public function obtenerReporteVentasPorVendedorXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        //seleccion de bien tipo e hijos        
        $bienTipo = $criterios[0]['bienTipo'];

        if (ObjectUtil::isEmpty($bienTipo)) {
            $bienTipo = $criterios[0]['bienTipoPadre'];
        }

        if (!ObjectUtil::isEmpty($bienTipo)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipo);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipo, $item['id']);
            }
        }
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);
        // fin 

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return Reporte::create()->obtenerReporteVentasPorVendedorXCriterios($empresaIdFormateado, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $bienTipoIdFormateado);
    }

    public function obtenerCantidadReporteVentasPorVendedorXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        //seleccion de bien tipo e hijos        
        $bienTipo = $criterios[0]['bienTipo'];

        if (ObjectUtil::isEmpty($bienTipo)) {
            $bienTipo = $criterios[0]['bienTipoPadre'];
        }

        if (!ObjectUtil::isEmpty($bienTipo)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipo);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipo, $item['id']);
            }
        }
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);
        // fin 

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteVentasPorVendedorXCriterios($empresaIdFormateado, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $bienTipoIdFormateado);
    }

    public function obtenerReporteVentasPorVendedorExcel($data)
    {
        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteVentasPorVendedorExcel($data, "REPORTE DE VENTAS POR VENDEDOR");
        }
    }

    private function crearReporteVentasPorVendedorExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':I' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        //Vendedor G.P. Principal G.P. Secundario F. Emisión Tipo documento Cliente S|N	Total S/.	Total $
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Vendedor');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'G.P. Principal');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'G.P. Secundario');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Cliente');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'S|N');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Total S/.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Total $');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['vendedor_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['bien_tipo_padre_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, str_replace(' 00:00:00', '', $reporte['fecha_emision']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['persona_nombre_completo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['serie_numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['total_soles']);
            $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $reporte['total_dolares']);
            $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->getNumberFormat()->setFormatCode('#,##0.00');


            $i += 1;
        }

        for ($i = 'A'; $i <= 'I'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle('reporte');

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //reporte ventas por tienda
    public function obtenerConfiguracionesInicialesVentasPorTienda()
    {
        $respuesta = new ObjectUtil();
        $tipo = '(1)';
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXTipos($tipo);
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->bien = BienNegocio::create()->obtenerBienMovimientoEmpresa($idEmpresa);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();

        return $respuesta;
    }

    public function obtenerCantidadesTotalesVentasPorTienda($criterios)
    {
        $empresaId = $criterios[0]['empresa'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $respuesta = new ObjectUtil();

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesVentasPorTienda($tipoDocumentoIdFormateado, $empresaIdFormateado, $emisionInicio, $emisionFin);

        $respuesta = $importeTotal;

        return $respuesta;
    }

    public function obtenerReporteVentasPorTiendaXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $empresaId = $criterios[0]['empresa'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return Reporte::create()->obtenerReporteVentasPorTiendaXCriterios($tipoDocumentoIdFormateado, $empresaIdFormateado, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadReporteVentasPorTiendaXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $empresaId = $criterios[0]['empresa'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteVentasPorTiendaXCriterios($tipoDocumentoIdFormateado, $empresaIdFormateado, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerReporteVentasPorTiendaExcel($data)
    {
        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteVentasPorTiendaExcel($data, "Reporte de Ventas por Empresa");
        }
    }

    private function crearReporteVentasPorTiendaExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':I' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* F. Creación	F. Emisión	Vendedor	Tipo documento	Cliente	Serie	Número	Total
         */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'F. Creación');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Empresa');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Cliente');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Serie');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Total S/.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Total $');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_creacion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['fecha_emision']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['razon_social']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['persona_nombre_completo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['serie']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['total_soles']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $reporte['total_dolares']);


            $i += 1;
        }

        for ($i = 'A'; $i <= 'I'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //Reporte ventas comision por vendedor
    public function obtenerConfiguracionesInicialesComisionVendedor()
    {
        $respuesta = new ObjectUtil();
        //        $respuesta->persona = PersonaNegocio::create()->obtenerPersonaPerfilVendedor();
        $respuesta->persona = PersonaNegocio::create()->obtenerComboPersonaXPersonaClaseId(-2);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();

        return $respuesta;
    }

    public function reporteComisionVendedor($criterios)
    {

        $vendedor = $criterios[0]['vendedor'];
        $porcentaje = $criterios[0]['porcentaje'];

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $vendedorIdFormateado = Util::convertirArrayXCadena($vendedor);

        return Reporte::create()->reporteVentasComisionVendedor($empresaIdFormateado, $porcentaje, $vendedorIdFormateado, $emisionInicio, $emisionFin);
    }

    public function obtenerReporteComisionVendedorExcel($criterios)
    {

        $respuesta = $this->reporteComisionVendedor($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteComisionVendedorExcel($respuesta, "Reporte de Comisión por Vendedor");
        }
    }

    private function crearReporteComisionVendedorExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':C' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':C' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Vendedor');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Total ventas');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Comisión');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':C' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['vendedor_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, round($reporte['total_ventas'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, round($reporte['comision'], 2));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':C' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'C'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //reporte ventas por tiempo
    public function obtenerConfiguracionesInicialesPorTiempo()
    {
        $respuesta = new ObjectUtil();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();

        return $respuesta;
    }

    public function reportePorTiempo($criterios)
    {

        $tienda = $criterios[0]['tienda'];
        $tiempo = $criterios[0]['tiempo'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $tiendaIdFormateado = Util::convertirArrayXCadena($tienda);

        return Reporte::create()->reporteVentasPorTiempo($tiempo, $tiendaIdFormateado, $emisionInicio, $emisionFin);
    }

    public function obtenerCantidadesTotalesVentasPorTiempo($criterios)
    {
        $tienda = $criterios[0]['tienda'];
        $tiempo = $criterios[0]['tiempo'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $tiendaIdFormateado = Util::convertirArrayXCadena($tienda);

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesVentasPorTiempo($tiempo, $tiendaIdFormateado, $emisionInicio, $emisionFin);

        return $importeTotal;
    }

    public function obtenerReportePorTiempoExcel($criterios)
    {

        $respuesta = $this->reportePorTiempo($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReportePorTiempoExcel($respuesta, "Reporte de Ventas por Tiempo");
        }
    }

    private function crearReportePorTiempoExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':E' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        $objPHPExcel->getActiveSheet()->mergeCells('B' . $i . ':C' . $i);
        $objPHPExcel->getActiveSheet()->mergeCells('D' . $i . ':E' . $i);

        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'SOLES');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'DOLARES');

        $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':E' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Tiempo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Núm. ventas');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Total ventas');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Núm. ventas');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Total ventas');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_tiempo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['numero_soles']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, round($reporte['total_soles'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['numero_dolares']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, round($reporte['total_dolares'], 2));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'E'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //Reporte ventas productos mas vendidos
    public function obtenerConfiguracionesInicialesProductosMasVendidos()
    {
        $respuesta = new ObjectUtil();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();

        return $respuesta;
    }

    public function reporteProductosMasVendidos($criterios)
    {

        $tienda = $criterios[0]['tienda'];
        $limite = $criterios[0]['limite'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $tiendaIdFormateado = Util::convertirArrayXCadena($tienda);

        return Reporte::create()->reporteVentasProductosMasVendidos($limite, $tiendaIdFormateado, $emisionInicio, $emisionFin);
    }

    public function obtenerReporteProductosMasVendidosExcel($criterios)
    {

        $respuesta = $this->reporteProductosMasVendidos($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteProductosMasVendidosExcel($respuesta, "Reporte de Productos más Vendidos");
        }
    }

    private function crearReporteProductosMasVendidosExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':D' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        $objPHPExcel->getActiveSheet()->mergeCells('B' . $i . ':C' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Núm. de ventas');
        $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':C' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Producto');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Soles');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Dólares');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Total');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['productos_soles']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['productos_dolares']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['productos_vendidos']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'D'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //reporte ventas y compras por producto
    public function obtenerConfiguracionesInicialesPorProducto()
    {
        $respuesta = new ObjectUtil();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->bien = BienNegocio::create()->obtenerBienKardexXEmpresa(-1);
        //        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipo();
        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipoPadre();

        return $respuesta;
    }

    public function reportePorProducto($criterios, $tipo)
    {

        $tienda = $criterios[0]['tienda'];
        $bien = $criterios[0]['bien'];
        $bienTipo = $criterios[0]['bienTipo'];

        if (ObjectUtil::isEmpty($bienTipo)) {
            $bienTipo = $criterios[0]['bienTipoPadre'];
        }

        if (!ObjectUtil::isEmpty($bienTipo)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipo);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipo, $item['id']);
            }
        }

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $tiendaIdFormateado = Util::convertirArrayXCadena($tienda);
        $bienIdFormateado = Util::convertirArrayXCadena($bien);
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);

        return Reporte::create()->reporteVentasPorProducto($bienIdFormateado, $bienTipoIdFormateado, $tiendaIdFormateado, $emisionInicio, $emisionFin, $tipo);
    }

    public function obtenerReportePorProductoExcel($criterios, $tipo)
    {

        $respuesta = $this->reportePorProducto($criterios, $tipo);

        if ($tipo == 1) {
            $parametro->titulo = "REPORTE DE VENTAS POR PRODUCTO";
            $parametro->columnaImporte = "Importe vendido";
        }
        if ($tipo == 4) {
            $parametro->titulo = "REPORTE DE COMPRAS POR PRODUCTO";
            $parametro->columnaImporte = "Importe comprado";
        }

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReportePorProductoExcel($respuesta, $parametro);
        }
    }

    private function crearReportePorProductoExcel($reportes, $parametro)
    {
        $titulo = $parametro->titulo;

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':H' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        //Cód. Bien	Bien	Tipo bien		Importe vendido
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Código');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Producto');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'G.P. Principal');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'G.P. Secundario');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Cantidad');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Unidad control');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $parametro->columnaImporte . ' S/.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $parametro->columnaImporte . ' $');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_codigo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['bien_tipo_padre_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['cantidad_conv']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['unidad_control']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, round($reporte['importe_total_soles'], 2));
            $objPHPExcel->getActiveSheet()->getStyle('G' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, round($reporte['importe_total_dolares'], 2));
            $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0.00');

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->applyFromArray($this->estiloTextoInformacion);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':H' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'H'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //reporte ventas por stock valorizado
    public function obtenerConfiguracionesInicialesStockValorizado()
    {
        $respuesta = new ObjectUtil();
        $respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivo(56);
        $respuesta->bien = BienNegocio::create()->obtenerBienKardexXEmpresa(-1);
        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipo();

        return $respuesta;
    }

    public function reporteStockValorizado($criterios)
    {

        $organizador = $criterios[0]['organizador'];
        $bien = $criterios[0]['bien'];
        $bienTipo = $criterios[0]['bienTipo'];

        $organizadorIdFormateado = Util::convertirArrayXCadena($organizador);
        $bienIdFormateado = Util::convertirArrayXCadena($bien);
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);

        return Reporte::create()->reporteVentasStockValorizado($bienIdFormateado, $bienTipoIdFormateado, $organizadorIdFormateado);
    }

    public function obtenerReporteStockValorizadoExcel($criterios)
    {

        $respuesta = $this->reporteStockValorizado($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteStockValorizadoExcel($respuesta, "Reporte de Stock Valorizado");
        }
    }

    private function crearReporteStockValorizadoExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':E' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        //Tipo bien	Bien	Stock	Unidad control	Stock valorizado
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Tipo producto');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Producto');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Stock');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Unidad control');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Stock valorizado');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, round($reporte['stock'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['unidad_control']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, round($reporte['stock_valorizado'], 2));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'E'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //reporte compras
    public function obtenerConfiguracionesInicialesReporteCompras()
    {
        $respuesta = new ObjectUtil();
        $tipo = '(4)';
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXTipos($tipo);
        $respuesta->persona = PersonaNegocio::create()->obtenerComboPersonaXPersonaClaseId(-1);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();

        return $respuesta;
    }

    public function obtenerCantidadesTotalesReporteCompras($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $respuesta = new ObjectUtil();

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesReporteCompras($tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin);

        $respuesta->total = $importeTotal[0]['total'];

        return $respuesta;
    }

    public function obtenerReporteReporteComprasXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return Reporte::create()->obtenerReporteReporteComprasXCriterios($tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerReporteReporteAtenciones($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        //        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        if ($tipoDocumentoIdFormateado == "")
            $tipoDocumentoIdFormateado = "-1";
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        $datita = Reporte::create()->obtenerReporteReporteAtenciones($tipoDocumentoIdFormateado, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);

        //        foreach ($datita as $index => $data)
        //        {
        //            $data[$index]['total'] = ""
        //        }

        return $datita;
    }

    public function obtenerCantidadReporteReporteComprasXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteReporteComprasXCriterios($tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadReporteReporteAtencionesXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteReporteAtencionesXCriterios($tipoDocumentoIdFormateado, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerReporteReporteComprasExcel($data)
    {
        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteReporteComprasExcel($data, "Reporte de Compras");
        }
    }

    private function crearReporteReporteComprasExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':H' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* F. Creación	F. Emisión	Vendedor	Tipo documento	Cliente	Serie	Número	Total
         */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'F. Creación');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Usuario');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Proveedor');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Serie');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Total');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_creacion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['fecha_emision']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['usuario_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['persona_nombre_completo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['serie']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['total']);


            $i += 1;
        }

        for ($i = 'A'; $i <= 'H'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //reporte activos fijos
    public function obtenerConfiguracionesInicialesActivosFijos()
    {
        $respuesta = new ObjectUtil();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->bien = BienNegocio::create()->obtenerActivosFijosXEmpresa(-1);
        $respuesta->motivo = DocumentoTipoDatoListaNegocio::create()->obtenerXDocumentoTipoDato(624);

        return $respuesta;
    }

    public function reporteActivosFijos($criterios)
    {

        $tienda = $criterios[0]['tienda'];
        $bien = $criterios[0]['bien'];
        $motivo = $criterios[0]['motivo'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $tiendaIdFormateado = Util::convertirArrayXCadena($tienda);
        $bienIdFormateado = Util::convertirArrayXCadena($bien);
        $motivoIdFormateado = Util::convertirArrayXCadena($motivo);

        return Reporte::create()->reporteVentasActivosFijos($bienIdFormateado, $motivoIdFormateado, $tiendaIdFormateado, $emisionInicio, $emisionFin);
    }

    public function obtenerReporteActivosFijosExcel($criterios)
    {

        $respuesta = $this->reporteActivosFijos($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteActivosFijosExcel($respuesta, "Reporte de Activos Fijos");
        }
    }

    private function crearReporteActivosFijosExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':G' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':G' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        //Bien	Motivo	Proveedor	Tipo documento	Serie	Número	Precio
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Bien');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Motivo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Proveedor');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Serie');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Precio');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':G' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['tipo_lista_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['persona_nombre_completo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['serie']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, round($reporte['valor_monetario'], 2));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':G' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'G'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //reporte estadistico de ventas
    public function obtenerConfiguracionesInicialesReporteEstadisticoVentas()
    {

        $respuesta = new ObjectUtil();
        //$respuesta->organizador = OrganizadorNegocio::create()->obtenerOrganizadorActivo(56);        
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->bien = BienNegocio::create()->obtenerBienKardexXEmpresa(-1);
        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipo();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $tipo = '(1)';
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXTipos($tipo);
        //  $respuesta->tipo_frecuencia = DocumentoTipoDatoListaNegocio::create()->obtenerXDocumentoTipoDato(505);;        
        $respuesta->dataMoneda = MonedaNegocio::create()->obtenerComboMoneda();
        return $respuesta;
    }

    public function reporteReporteEstadisticoVentas($criterios)
    {

        $empresaId = $criterios[0]['empresa'];
        $bienId = $criterios[0]['bien'];
        $bienTipoId = $criterios[0]['bienTipo'];
        $documentoTipoId = $criterios[0]['documentoTipo'];
        $tipoFrecuenciaId = $criterios[0]['tipoFrecuencia'];
        $monedaId = $criterios[0]['monedaId'];

        if (!ObjectUtil::isEmpty($bienTipoId)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipoId);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipoId, $item['id']);
            }
        }

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        if ($tipoFrecuenciaId == 1) {
            $dias = (strtotime($emisionInicio) - strtotime($emisionFin)) / 86400;
            $dias = abs($dias);
            $dias = floor($dias);

            if ($dias > 30) {
                throw new WarningException("Intérvalo de días superior a 30");
            }
        }
        if ($tipoFrecuenciaId == 2) {
            $meses = (strtotime($emisionInicio) - strtotime($emisionFin)) / (86400 * 30);
            $meses = abs($meses);
            $meses = floor($meses);

            if ($meses > 30) {
                throw new WarningException("Intérvalo de meses superior a 30");
            }
        }


        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);
        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipoId);
        $documentoTipoIdFormateado = Util::convertirArrayXCadena($documentoTipoId);

        return Reporte::create()->reporteReporteEstadisticoVentas($empresaIdFormateado, $bienIdFormateado, $bienTipoIdFormateado, $emisionInicio, $emisionFin, $documentoTipoIdFormateado, $tipoFrecuenciaId, $monedaId);
    }

    //reporte ventas por cliente
    public function obtenerConfiguracionesInicialesVentasPorCliente()
    {
        $respuesta = new ObjectUtil();
        $tipo = '(1)';
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXTipos($tipo);
        //        $respuesta->persona = PersonaNegocio::create()->obtenerComboPersonaXPersonaClaseId(16);// 16: cliente
        $respuesta->persona = PersonaNegocio::create()->obtenerActivas();
        $respuesta->bien = BienNegocio::create()->obtenerBienMovimientoEmpresa($idEmpresa);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipoPadre();

        return $respuesta;
    }

    public function obtenerCantidadesTotalesVentasPorCliente($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        //seleccion de bien tipo e hijos        
        $bienTipo = $criterios[0]['bienTipo'];

        if (ObjectUtil::isEmpty($bienTipo)) {
            $bienTipo = $criterios[0]['bienTipoPadre'];
        }

        if (!ObjectUtil::isEmpty($bienTipo)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipo);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipo, $item['id']);
            }
        }
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);
        // fin         

        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $respuesta = new ObjectUtil();

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesVentasPorCliente($empresaIdFormateado, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $bienTipoIdFormateado);

        $respuesta->totalSoles = $importeTotal[0]['total_soles'];
        $respuesta->totalDolares = $importeTotal[0]['total_dolares'];

        return $respuesta;
    }

    public function obtenerReporteVentasPorClienteXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        //seleccion de bien tipo e hijos        
        $bienTipo = $criterios[0]['bienTipo'];

        if (ObjectUtil::isEmpty($bienTipo)) {
            $bienTipo = $criterios[0]['bienTipoPadre'];
        }

        if (!ObjectUtil::isEmpty($bienTipo)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipo);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipo, $item['id']);
            }
        }
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);
        // fin         

        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        $data = Reporte::create()->obtenerReporteVentasPorClienteXCriterios($empresaIdFormateado, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $bienTipoIdFormateado);

        $tamanio = count($data);
        for ($i = 0; $i < $tamanio; $i++) {
            $stringAcciones = '';
            if ($data[$i]['documento_id'] != '' && $data[$i]['movimiento_id'] != '') {
                $stringAcciones = '<a onclick="verDetallePorCliente(' . $data[$i]['documento_id'] . ',' . $data[$i]['movimiento_id'] . ')"><b><i class="fa fa-eye"" style="color:b"></i><b></a>';
            }
            $data[$i]['acciones'] = $stringAcciones;
        }

        return $data;
    }

    public function obtenerCantidadReporteVentasPorClienteXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        //seleccion de bien tipo e hijos        
        $bienTipo = $criterios[0]['bienTipo'];

        if (ObjectUtil::isEmpty($bienTipo)) {
            $bienTipo = $criterios[0]['bienTipoPadre'];
        }

        if (!ObjectUtil::isEmpty($bienTipo)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipo);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipo, $item['id']);
            }
        }
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);
        // fin        

        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteVentasPorClienteXCriterios($empresaIdFormateado, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $bienTipoIdFormateado);
    }

    public function obtenerReporteVentasPorClienteExcel($data)
    {
        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteVentasPorClienteExcel($data, "REPORTE DE VENTAS POR CLIENTE");
        }
    }

    private function crearReporteVentasPorClienteExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':H' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        //        Cliente G.P. Principal G.P. Secundario F. Emisión	Tipo documento	S|N	Total S/.	Total $
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Cliente');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'G.P. Principal');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'G.P. Secundario');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'S|N');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Total S/.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Total $');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['persona_nombre_completo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['bien_tipo_padre_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['fecha_emision']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['serie_numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['total_soles']);
            $objPHPExcel->getActiveSheet()->getStyle('G' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['total_dolares']);
            $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0.00');

            $i += 1;
        }

        for ($i = 'A'; $i <= 'H'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle('reporte');

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function verDetallePorCliente($documentoId, $movimientoId)
    {
        return MovimientoNegocio::create()->visualizarDocumento($documentoId, $movimientoId);
    }

    //reporte ventas reporte de utilidades
    public function obtenerConfiguracionesInicialesReporteUtilidades()
    {
        $respuesta = new ObjectUtil();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();

        return $respuesta;
    }

    public function reporteReporteUtilidades($criterios)
    {

        $tienda = $criterios[0]['tienda'];
        $tiempo = $criterios[0]['tiempo'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $tiendaIdFormateado = Util::convertirArrayXCadena($tienda);

        return Reporte::create()->reporteVentasReporteUtilidades($tiempo, $tiendaIdFormateado, $emisionInicio, $emisionFin);
    }

    public function obtenerReporteReporteUtilidadesExcel($criterios)
    {

        $respuesta = $this->reporteReporteUtilidades($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteReporteUtilidadesExcel($respuesta, "Reporte de Utilidades");
        }
    }

    private function crearReporteReporteUtilidadesExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':D' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Tiempo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Utilidad (%)');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Utilidad soles');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Utilidad dólares');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_tiempo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, round($reporte['utilidad_porcentaje_total'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, round($reporte['utilidad_total_soles'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, round($reporte['utilidad_dolares_soles'], 2));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'D'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //reporte tributario
    public function obtenerConfiguracionesInicialesReporteTributario()
    {
        $respuesta = new ObjectUtil();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();

        return $respuesta;
    }

    public function reporteTributario($criterios)
    {

        $tipoTributo = $criterios[0]['tipoTributo'];
        $empresaId = $criterios[0]['empresaId'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);


        return Reporte::create()->reporteTributario($tipoTributo, $emisionInicio, $emisionFin, $empresaId);
    }

    public function obtenerCantidadesTotalesReporteTributario($criterios)
    {

        $tipoTributo = $criterios[0]['tipoTributo'];
        $empresaId = $criterios[0]['empresaId'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);


        $importeTotal = Reporte::create()->obtenerCantidadesTotalesReporteTributario($tipoTributo, $emisionInicio, $emisionFin, $empresaId);

        return $importeTotal[0]['total'];
    }

    public function obtenerReporteTributarioExcel($criterios)
    {

        $respuesta = $this->reporteTributario($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteReporteTributarioExcel($respuesta, "Reporte Tributario");
        }
    }

    private function crearReporteReporteTributarioExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':F' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        //Fecha	Tipo	S|N Tipo	Documento	S|N Documento	Importe
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Fecha');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Tipo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'S|N Tipo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'S|N Documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Importe');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_emision']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['tipo_comprobante_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['serie_num_comprobante']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['tipo_documento_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['serie_num_documento']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, round($reporte['importe'], 2));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'F'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //reporte notas credito y debito
    public function obtenerConfiguracionesInicialesNotasCreditoDebito()
    {
        $respuesta = new ObjectUtil();
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoNotasCreditoDebito();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->bien = BienNegocio::create()->obtenerBienMovimientoEmpresa($idEmpresa);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();

        return $respuesta;
    }

    public function obtenerCantidadesTotalesNotasCreditoDebito($criterios)
    {
        $empresaId = $criterios[0]['empresa'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $respuesta = new ObjectUtil();

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesNotasCreditoDebito($tipoDocumentoIdFormateado, $empresaIdFormateado, $emisionInicio, $emisionFin);

        $respuesta->pagado_soles_reporte = $importeTotal[0]['pagado_soles_reporte'];
        $respuesta->total_soles_reporte = $importeTotal[0]['total_soles_reporte'];
        $respuesta->pagado_dolares_reporte = $importeTotal[0]['pagado_dolares_reporte'];
        $respuesta->total_dolares_reporte = $importeTotal[0]['total_dolares_reporte'];

        return $respuesta;
    }

    public function obtenerReporteNotasCreditoDebitoXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $empresaId = $criterios[0]['empresa'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        $data = Reporte::create()->obtenerReporteNotasCreditoDebitoXCriterios($tipoDocumentoIdFormateado, $empresaIdFormateado, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);

        $tamanio = count($data);
        for ($i = 0; $i < $tamanio; $i++) {
            $estadoNotas = "";
            if ($data[$i]['total'] == $data[$i]['importe_utilizado'])
                $estadoNotas = 'Uso total';

            if ($data[$i]['total'] > $data[$i]['importe_utilizado'] && $data[$i]['importe_utilizado'] != 0)
                $estadoNotas = 'Uso parcial';

            if ($data[$i]['importe_utilizado'] == 0)
                $estadoNotas = 'Pendiente de uso';

            $data[$i]['estado_nota'] = $estadoNotas;
        }
        return $data;
    }

    public function obtenerCantidadReporteNotasCreditoDebitoXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $empresaId = $criterios[0]['empresa'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteNotasCreditoDebitoXCriterios($tipoDocumentoIdFormateado, $empresaIdFormateado, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerReporteNotasCreditoDebitoExcel($data)
    {
        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteNotasCreditoDebitoExcel($data, "Reporte de Notas de Crédito y Débito");
        }
    }

    private function crearReporteNotasCreditoDebitoExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':H' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* F. Creación	F. Emisión	Vendedor	Tipo documento	Cliente	Serie	Número	Total
         */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'F. Creación');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Tienda');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Cliente');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Serie');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Total');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':H' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_creacion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['fecha_emision']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['razon_social']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['persona_nombre_completo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['serie']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['total']);


            $i += 1;
        }

        for ($i = 'A'; $i <= 'H'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte_notas");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function obtenerCantidadesTotalesCuentasPorCobrar($tipo1, $tipo2, $criterios)
    {
        $personaId = $criterios[0]['persona'];
        $personaId = (ObjectUtil::isEmpty($personaId) || $personaId * 1 == -1) ? null : $personaId;
        $mostrarPagados = $criterios[0]['mostrar'];
        $mostrarLib = $criterios[0]['mostrarLib'];
        if ($criterios[0]['fechaVencimientoDesde'] != '') {
            $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaVencimientoDesde']);
        }

        if ($criterios[0]['fechaVencimientoHasta'] != '') {
            $fechaVencimientoHasta = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaVencimientoHasta']);
        }
        $empresa = $criterios[0]['empresa'];

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesCuentasPorCobrar($mostrarPagados, $mostrarLib, $tipo1, $tipo2, $empresa, $personaId, $fechaVencimientoDesde, $fechaVencimientoHasta);

        return $importeTotal;
    }

    public function obtenerCantidadesTotalesCuentasPorCobrarGeneral($tipo1, $tipo2, $criterios)
    {
        $personaId = $criterios[0]['persona'];
        $mostrar = $criterios[0]['mostrar'];
        $fecha = '';
        $empresa = Util::convertirArrayXCadena($criterios[0]['empresa']);

        if ($criterios[0]['fecha'] != '') {
            $fecha = DateUtil::formatearCadenaACadenaBD($criterios[0]['fecha']);
        }

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesCuentasPorCobrarGeneral($mostrar, $tipo1, $tipo2, $empresa, $personaId, $fecha, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);

        return $importeTotal;
    }

    //reporte ventas IGV VENTAS
    public function obtenerConfiguracionesInicialesVentasIgvVentas()
    {
        $respuesta = new ObjectUtil();
        $tipo = '(1)';
        $descripcion = 'Factura';
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXTiposxDescripcion($tipo, $descripcion);
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->bien = BienNegocio::create()->obtenerBienMovimientoEmpresa($idEmpresa);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();

        return $respuesta;
    }

    public function obtenerCantidadesTotalesVentasIgvVentas($criterios)
    {
        $empresaId = $criterios[0]['empresa'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $respuesta = new ObjectUtil();

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesVentasIgvVentas($tipoDocumentoIdFormateado, $empresaIdFormateado, $emisionInicio, $emisionFin);

        return $importeTotal;
    }

    public function obtenerReporteVentasIgvVentasXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $empresaId = $criterios[0]['empresa'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return Reporte::create()->obtenerReporteVentasIgvVentasXCriterios($tipoDocumentoIdFormateado, $empresaIdFormateado, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadReporteVentasIgvVentasXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $empresaId = $criterios[0]['empresa'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteVentasIgvVentasXCriterios($tipoDocumentoIdFormateado, $empresaIdFormateado, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerReporteVentasIgvVentasExcel($data)
    {
        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteVentasIgvVentasExcel($data, "Reporte de Ventas IGV");
        }
    }

    private function crearReporteVentasIgvVentasExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':I' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* F. Creación	F. Emisión	Vendedor	Tipo documento	Cliente	Serie	Número	Total
         */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'F. Creación');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Tienda');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Cliente');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Serie');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'IGV S/.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'IGV $');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_creacion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['fecha_emision']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['razon_social']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['persona_nombre_completo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['serie']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['igv_soles']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $reporte['igv_dolares']);


            $i += 1;
        }

        for ($i = 'A'; $i <= 'I'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //Reporte Caja Bancos por actividad
    public function obtenerConfiguracionesInicialesPorActividad()
    {
        $respuesta = new ObjectUtil();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->actividad_tipo = ActividadNegocio::create()->obtenerActividadTipoActivas();
        $respuesta->actividad = ActividadNegocio::create()->obtenerActividadesActivasTodo();

        return $respuesta;
    }

    public function reportePorActividad($criterios)
    {

        $tienda = $criterios[0]['tienda'];
        $actividad = $criterios[0]['actividad'];
        $actividadTipo = $criterios[0]['actividadTipo'];
        $mes = $criterios[0]['mes'];
        $anio = $criterios[0]['anio'];

        //        $tiendaIdFormateado = Util::convertirArrayXCadena($tienda);
        $actividadIdFormateado = Util::convertirArrayXCadena($actividad);
        $actividadTipoIdFormateado = Util::convertirArrayXCadena($actividadTipo);

        return Reporte::create()->reporteCajaBancosPorActividad($actividadIdFormateado, $actividadTipoIdFormateado, $tienda, $mes, $anio);
    }

    public function obtenerReportePorActividadExcel($criterios)
    {

        $respuesta = $this->reportePorActividad($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReportePorActividadExcel($respuesta, "Reporte de Actividades");
        }
    }

    private function crearReportePorActividadExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':D' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        //Cód.	Tipo actividad	Actividad	Total
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Código');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Tipo actividad');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Actividad');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Total');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['codigo_actividad']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['actividad_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['actividad_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, round($reporte['total'], 2));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'D'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //Reporte Caja Bancos por cuenta
    public function obtenerConfiguracionesInicialesPorCuenta()
    {
        $respuesta = new ObjectUtil();
        //Ingreso: 7,2,3 -- Salida: 8,5,6

        $tipoDato = 20; // tipo cuenta en documento_tipo_dato
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoTipoDatoXTipo($tipoDato);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->cuenta = CuentaNegocio::create()->obtenerCuentasActivas();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();

        return $respuesta;
    }

    public function reportePorCuenta($criterios)
    {

        $documentoTipo = $criterios[0]['documentoTipo'];
        $cuenta = $criterios[0]['cuenta'];

        $mes = $criterios[0]['mes'];
        $anio = $criterios[0]['anio'];

        $documentoTipoIdFormateado = Util::convertirArrayXCadena($documentoTipo);
        $cuentaIdFormateado = Util::convertirArrayXCadena($cuenta);

        $empresaId = $criterios[0]['empresaId'];
        return Reporte::create()->reporteVentasPorCuenta($documentoTipoIdFormateado, $mes, $anio, $cuentaIdFormateado, $empresaId);
    }

    public function reportePorCuentaTotales($criterios)
    {

        $documentoTipo = $criterios[0]['documentoTipo'];
        $cuenta = $criterios[0]['cuenta'];

        $mes = $criterios[0]['mes'];
        $anio = $criterios[0]['anio'];

        $documentoTipoIdFormateado = Util::convertirArrayXCadena($documentoTipo);
        $cuentaIdFormateado = Util::convertirArrayXCadena($cuenta);

        $empresaId = $criterios[0]['empresaId'];
        return Reporte::create()->reportePorCuentaTotales($documentoTipoIdFormateado, $mes, $anio, $cuentaIdFormateado, $empresaId);
    }

    public function obtenerReportePorCuentaExcel($criterios)
    {

        $respuesta = $this->reportePorCuenta($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReportePorCuentaExcel($respuesta, "Reporte de Cuentas");
        }
    }

    private function crearReportePorCuentaExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':T' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':T' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* Fecha emisión	Tipo documento	S | N	COD	Encargado	Tercero	Detalle	Cuenta	Caja chica	BCP (5701728785048)	Banco de la nación
          Ing.	Sal.	SALDO	Ing.	Sal.	SALDO	Ing.	Sal.	SALDO */
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':H' . $i);
        $objPHPExcel->getActiveSheet()->mergeCells('I' . $i . ':K' . $i);
        $objPHPExcel->getActiveSheet()->mergeCells('L' . $i . ':N' . $i);
        $objPHPExcel->getActiveSheet()->mergeCells('O' . $i . ':Q' . $i);
        $objPHPExcel->getActiveSheet()->mergeCells('R' . $i . ':T' . $i);

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, ' ');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Caja chica');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'BCP');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, 'BBVA');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, 'ScotiaBank');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':T' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Fecha emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'S | N');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'COD');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Encargado');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Tercero');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Detalle');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Cuenta');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Ing.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'Sal.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, 'SALDO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'Ing.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, 'Sal.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, 'SALDO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, 'Ing.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, 'Sal.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, 'SALDO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, 'Ing.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, 'Sal.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('T' . $i, 'SALDO');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':T' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_emision']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, ' ' . $reporte['serie_numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['actividad_codigo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['usuario_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['persona_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['cuenta_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, round($reporte['total_caja_ingreso'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, round($reporte['total_caja_salida'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, round($reporte['total_caja_saldo'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, round($reporte['total_bcp_ingreso'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, round($reporte['total_bcp_salida'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, round($reporte['total_bcp_saldo'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, round($reporte['total_bn_ingreso'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, round($reporte['total_bn_salida'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, round($reporte['total_bn_saldo'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, round($reporte['total_ret_ingreso'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, round($reporte['total_ret_salida'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('T' . $i, round($reporte['total_ret_saldo'], 2));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':T' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->getActiveSheet()->getStyle('I' . $i . ':T' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $i += 1;
        }

        for ($i = 'A'; $i <= 'T'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //Reporte Caja Bancos cierre caja
    public function obtenerConfiguracionesInicialesCierreCaja()
    {
        $respuesta = new ObjectUtil();
        //Ingreso: 7,2,3 -- Salida: 8,5,6

        $tipoDato = 20; // tipo cuenta en documento_tipo_dato
        //        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoTipoDatoXTipo($tipoDato);
        $respuesta->actividad_tipo = ActividadNegocio::create()->obtenerActividadTipoActivas();
        $respuesta->actividad = ActividadNegocio::create()->obtenerActividadesActivasTodo();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->cuenta = CuentaNegocio::create()->obtenerCuentasActivas();

        return $respuesta;
    }

    public function reporteCierreCaja($criterios)
    {

        $actividad = $criterios[0]['actividad'];
        $actividadTipo = $criterios[0]['actividadTipo'];
        $cuenta = $criterios[0]['cuenta'];

        $fechaEmision = $this->formatearFechaBD($criterios[0]['fechaEmision']);

        $actividadIdFormateado = Util::convertirArrayXCadena($actividad);
        $actividadTipoIdFormateado = Util::convertirArrayXCadena($actividadTipo);
        $cuentaIdFormateado = Util::convertirArrayXCadena($cuenta);

        $empresaId = $criterios[0]['empresaId'];
        return Reporte::create()->reporteCierreCaja($actividadIdFormateado, $actividadTipoIdFormateado, $fechaEmision, $cuentaIdFormateado, $empresaId);
    }

    public function reporteCierreCajaTotales($criterios)
    {

        $actividad = $criterios[0]['actividad'];
        $actividadTipo = $criterios[0]['actividadTipo'];
        $cuenta = $criterios[0]['cuenta'];

        $fechaEmision = $this->formatearFechaBD($criterios[0]['fechaEmision']);

        $actividadIdFormateado = Util::convertirArrayXCadena($actividad);
        $actividadTipoIdFormateado = Util::convertirArrayXCadena($actividadTipo);
        $cuentaIdFormateado = Util::convertirArrayXCadena($cuenta);

        $empresaId = $criterios[0]['empresaId'];
        return Reporte::create()->reporteCierreCajaTotales($actividadIdFormateado, $actividadTipoIdFormateado, $fechaEmision, $cuentaIdFormateado, $empresaId);
    }

    public function obtenerReporteCierreCajaExcel($criterios)
    {

        $respuesta = $this->reporteCierreCaja($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteCierreCajaExcel($respuesta, "Reporte de Cuentas");
        }
    }

    private function crearReporteCierreCajaExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':J' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':J' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* Fecha creación	Tipo documento	S|N	Tipo doc. pago	S|N pago	Cliente/Proveedor	COD	Actividad	Cuenta	Total
         */

        $i += 1;
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Fecha creación');
        //        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Fecha emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'S | N');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Tipo doc. pago');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'S|N pago');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Cliente/Proveedor');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'COD');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Actividad');
        //        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Usuario');
        //        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Detalle');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Cuenta');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'Total');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':J' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_creacion']);
            //            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['fecha_emision']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, ' ' . $reporte['serie_numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['documento_tipo_desc_pago']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, ' ' . $reporte['serie_numero_pago']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['persona_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['actividad_codigo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['actividad_descripcion']);
            //            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['usuario_nombre']);
            //            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $reporte['descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $reporte['cuenta_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, round($reporte['total_conversion'], 2));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':J' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->getActiveSheet()->getStyle('J' . $i)->getNumberFormat()->setFormatCode('#,##0.00');

            $i += 1;
        }

        for ($i = 'A'; $i <= 'J'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //reporte orden compra
    public function obtenerConfiguracionesInicialesReporteOrdenCompra()
    {
        $respuesta = new ObjectUtil();
        $respuesta->persona = PersonaNegocio::create()->obtenerComboPersonaXPersonaClaseId(-1);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();

        return $respuesta;
    }

    public function obtenerCantidadesTotalesReporteOrdenCompra($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $empresaId = $criterios[0]['empresa'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        $respuesta = new ObjectUtil();

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesReporteOrdenCompra($empresaId, $personaId, $emisionInicio, $emisionFin);

        $respuesta->total = $importeTotal[0]['total'];

        return $respuesta;
    }

    public function obtenerReporteReporteOrdenCompraXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $empresaId = $criterios[0]['empresa'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return Reporte::create()->obtenerReporteReporteOrdenCompraXCriterios($empresaId, $personaId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadReporteReporteOrdenCompraXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $empresaId = $criterios[0]['empresa'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteReporteOrdenCompraXCriterios($empresaId, $personaId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerReporteReporteOrdenCompraExcel($data)
    {
        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteReporteOrdenCompraExcel($data, "Reporte de Productos por llegar");
        }
    }

    private function crearReporteReporteOrdenCompraExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':I' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* F. Tentativa	F. Emisión	Tipo documento	Proveedor	Serie	Número	Producto	Cantidad	Unidad medida	Total
         */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'F. Tentativa');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Proveedor');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Serie');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Producto');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Cantidad');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Unidad medida');
        //        $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'Total');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_tentativa']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['fecha_emision']);
            //            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['usuario_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['persona_nombre_completo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['serie']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['cantidad']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $reporte['unidad_medida_descripcion']);
            //            $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, $reporte['total']);


            $i += 1;
        }

        for ($i = 'A'; $i <= 'I'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //Reporte retencion detraccion
    public function obtenerConfiguracionesInicialesRetencionDetraccion()
    {
        $respuesta = new ObjectUtil();
        $respuesta->persona = PersonaNegocio::create()->obtenerComboPersonaXPersonaClaseId(16); // 16: cliente
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();

        return $respuesta;
    }

    public function reporteRetencionDetraccion($criterios)
    {

        $cliente = $criterios[0]['cliente'];
        $tipoRD = $criterios[0]['tipoRD'];
        $empresaId = $criterios[0]['empresa'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $clienteIdFormateado = Util::convertirArrayXCadena($cliente);

        return Reporte::create()->reporteVentasRetencionDetraccion($empresaId, $tipoRD, $clienteIdFormateado, $emisionInicio, $emisionFin);
    }

    public function obtenerReporteRetencionDetraccionExcel($criterios)
    {

        $respuesta = $this->reporteRetencionDetraccion($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteRetencionDetraccionExcel($respuesta, "Reporte de Retención/Detracción");
        }
    }

    private function crearReporteRetencionDetraccionExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':D' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Producto');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Número de ventas');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':B' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['productos_vendidos']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':B' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'B'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //Reporte Caja Bancos por actividad por fecha
    public function obtenerConfiguracionesInicialesPorActividadPorFecha()
    {
        $respuesta = new ObjectUtil();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->actividad_tipo = ActividadNegocio::create()->obtenerActividadTipoActivas();
        $respuesta->actividad = ActividadNegocio::create()->obtenerActividadesActivasTodo();

        return $respuesta;
    }

    public function reportePorActividadPorFecha($criterios)
    {

        $tienda = $criterios[0]['tienda'];
        $actividad = $criterios[0]['actividad'];
        $actividadTipo = $criterios[0]['actividadTipo'];
        $fechaEmision = $this->formatearFechaBD($criterios[0]['fechaEmision']);

        //        $tiendaIdFormateado = Util::convertirArrayXCadena($tienda);
        $actividadIdFormateado = Util::convertirArrayXCadena($actividad);
        $actividadTipoIdFormateado = Util::convertirArrayXCadena($actividadTipo);

        return Reporte::create()->reporteCajaBancosPorActividadPorFecha($actividadIdFormateado, $actividadTipoIdFormateado, $tienda, $fechaEmision);
    }

    public function obtenerReportePorActividadPorFechaExcel($criterios)
    {

        $respuesta = $this->reportePorActividadPorFecha($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReportePorActividadPorFechaExcel($respuesta, "Reporte de Actividades");
        }
    }

    private function crearReportePorActividadPorFechaExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':D' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        //Cód.	Tipo actividad	Actividad	Total
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Código');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Tipo actividad');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Actividad');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Total');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['codigo_actividad']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['actividad_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['actividad_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, round($reporte['total'], 2));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':D' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'D'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //Reporte Caja Bancos por cuenta fecha
    public function obtenerConfiguracionesInicialesPorCuentaFecha()
    {
        $respuesta = new ObjectUtil();
        //Ingreso: 7,2,3 -- Salida: 8,5,6
        //        $tipoDato = 20;// tipo cuenta en documento_tipo_dato
        //        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoXDocumentoTipoDatoXTipo($tipoDato);
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->cuenta = CuentaNegocio::create()->obtenerCuentasActivas();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();

        return $respuesta;
    }

    public function reportePorCuentaFecha($criterios)
    {

        $documentoTipo = $criterios[0]['documentoTipo'];
        $cuenta = $criterios[0]['cuenta'];

        $fechaEmision = $this->formatearFechaBD($criterios[0]['fechaEmision']);

        $documentoTipoIdFormateado = Util::convertirArrayXCadena($documentoTipo);
        $cuentaIdFormateado = Util::convertirArrayXCadena($cuenta);

        $empresaId = $criterios[0]['empresaId'];
        return Reporte::create()->reporteVentasPorCuentaFecha($documentoTipoIdFormateado, $fechaEmision, $cuentaIdFormateado, $empresaId);
    }

    public function reportePorCuentaFechaTotales($criterios)
    {

        $documentoTipo = $criterios[0]['documentoTipo'];
        $cuenta = $criterios[0]['cuenta'];

        $fechaEmision = $this->formatearFechaBD($criterios[0]['fechaEmision']);

        $documentoTipoIdFormateado = Util::convertirArrayXCadena($documentoTipo);
        $cuentaIdFormateado = Util::convertirArrayXCadena($cuenta);

        $empresaId = $criterios[0]['empresaId'];
        return Reporte::create()->reportePorCuentaFechaTotales($documentoTipoIdFormateado, $fechaEmision, $cuentaIdFormateado, $empresaId);
    }

    public function obtenerReportePorCuentaFechaExcel($criterios)
    {

        $respuesta = $this->reportePorCuentaFecha($criterios);

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReportePorCuentaFechaExcel($respuesta, "Reporte de Cuentas");
        }
    }

    private function crearReportePorCuentaFechaExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':T' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':T' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* Fecha emisión	Tipo documento	S | N	COD	Encargado	Tercero	Detalle	Cuenta	Caja chica	BCP (5701728785048)	Banco de la nación
          Ing.	Sal.	SALDO	Ing.	Sal.	SALDO	Ing.	Sal.	SALDO */
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':H' . $i);
        $objPHPExcel->getActiveSheet()->mergeCells('I' . $i . ':K' . $i);
        $objPHPExcel->getActiveSheet()->mergeCells('L' . $i . ':N' . $i);
        $objPHPExcel->getActiveSheet()->mergeCells('O' . $i . ':Q' . $i);
        $objPHPExcel->getActiveSheet()->mergeCells('R' . $i . ':T' . $i);

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, ' ');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Caja chica');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'BCP');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, 'BBVA');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, 'ScotiaBank');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':T' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Fecha emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'S | N');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'COD');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Encargado');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Tercero');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Detalle');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Cuenta');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Ing.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'Sal.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, 'SALDO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'Ing.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, 'Sal.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, 'SALDO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, 'Ing.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, 'Sal.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, 'SALDO');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, 'Ing.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, 'Sal.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('T' . $i, 'SALDO');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':T' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_emision']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, ' ' . $reporte['serie_numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['actividad_codigo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['usuario_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['persona_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['cuenta_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, round($reporte['total_caja_ingreso'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, round($reporte['total_caja_salida'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, round($reporte['total_caja_saldo'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, round($reporte['total_bcp_ingreso'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, round($reporte['total_bcp_salida'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, round($reporte['total_bcp_saldo'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, round($reporte['total_bn_ingreso'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, round($reporte['total_bn_salida'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, round($reporte['total_bn_saldo'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, round($reporte['total_ret_ingreso'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, round($reporte['total_ret_salida'], 2));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('T' . $i, round($reporte['total_ret_saldo'], 2));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':T' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->getActiveSheet()->getStyle('I' . $i . ':T' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $i += 1;
        }

        for ($i = 'A'; $i <= 'T'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function reporteKardexReporte($criterios)
    {
        $empresaId = $criterios[0]['empresaId'];
        $bienId = $criterios[0]['bien'];
        $bienTipoId = $criterios[0]['bienTipo'];

        //obtener bien tipo hijos
        if (!ObjectUtil::isEmpty($bienTipoId)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipoId);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipoId, $item['id']);
            }
        }
        //fin	

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipoId);

        return Reporte::create()->reporteKardexReporte($empresaId, $bienIdFormateado, $bienTipoIdFormateado, $emisionInicio, $emisionFin);
    }

    public function obtenerKardexReporteExcel($criterios, $tipo)
    {
        $respuestaReporteKardexExcel = $this->reporteKardexReporte($criterios);

        if (ObjectUtil::isEmpty($respuestaReporteKardexExcel)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearKardexReporteExcel($respuestaReporteKardexExcel, "REPORTE DE KARDEX");
        }
    }

    private function crearKardexReporteExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':F' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Producto');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Cód. Cont.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Tipo producto');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Stock');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Unidad de medida');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Costo unit.');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['codigo_contable']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['unidad_medida_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['stock']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['costo_inicial']);

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':F' . $i)->applyFromArray($this->estiloInformacion);
            $objPHPExcel->getActiveSheet()->getStyle('E' . $i . ':F' . $i)->applyFromArray($this->estiloNumInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'F'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    //kardex valorizado
    public function reporteKardexValorizado($criterios)
    {
        $empresaId = $criterios[0]['empresaId'];
        $bienId = $criterios[0]['bien'];
        $bienTipoId = $criterios[0]['bienTipo'];

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $bienIdFormateado = Util::convertirArrayXCadena($bienId);
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipoId);

        return Reporte::create()->reporteKardexValorizado($empresaId, $bienIdFormateado, $bienTipoIdFormateado, $emisionInicio, $emisionFin);
    }

    public function obtenerKardexValorizadoExcel($criterios, $tipo)
    {
        $respuestaReporteKardexExcel = $this->reporteKardexValorizado($criterios);

        if (ObjectUtil::isEmpty($respuestaReporteKardexExcel)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearKardexValorizadoExcel($respuestaReporteKardexExcel, "REPORTE DE KARDEX VALORIZADO");
        }
    }

    private function crearKardexValorizadoExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':E' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;

        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Producto');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Tipo producto');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Unidad de medida');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Stock');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Stock valorizado');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':E' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['unidad_medida_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['stock']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, number_format($reporte['stock_valorizado'], 2, ".", ","));

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':C' . $i)->applyFromArray($this->estiloInformacion);
            $objPHPExcel->getActiveSheet()->getStyle('D' . $i . ':E' . $i)->applyFromArray($this->estiloNumInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'E'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    function obtenerConfiguracionesInicialesListadoAtencion($usuarioId, $empresaId)
    {
        $respuesta = new ObjectUtil();
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerTiposParaReporteAtenciones();
        $respuesta->cboProductoData = BienNegocio::create()->getDataBien($usuarioId, $empresaId);
        $respuesta->cboProductoTipoData = Bien::create()->getDataBienTipo();
        $respuesta->cboPersonaData = PersonaNegocio::create()->obtenerComboPersonaXPersonaClaseId(-1);
        return $respuesta;
    }

    //reporte OPERACIONES
    public function obtenerConfiguracionesInicialesReporteOperaciones()
    {
        $respuesta = new ObjectUtil();
        $operacionTipoIds = ''; //tipos: (1),(2) o '': para todos
        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoReporteXOperacionTipos($operacionTipoIds);
        $respuesta->persona = PersonaNegocio::create()->obtenerActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();

        return $respuesta;
    }

    public function obtenerCantidadesTotalesReporteOperaciones($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $respuesta = new ObjectUtil();

        $importeTotal = Reporte::create()->obtenerCantidadesTotalesReporteOperaciones($empresaIdFormateado, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin);

        $respuesta->totalSoles = $importeTotal[0]['total_soles'];
        $respuesta->totalDolares = $importeTotal[0]['total_dolares'];

        return $respuesta;
    }

    public function obtenerReporteReporteOperacionesXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        $data = Reporte::create()->obtenerReporteReporteOperacionesXCriterios($empresaIdFormateado, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);

        $tamanio = count($data);
        for ($i = 0; $i < $tamanio; $i++) {
            $stringAcciones = '';
            if ($data[$i]['documento_id'] != '') {
                $stringAcciones = '<a onclick="verDetallePorOperacion(' . $data[$i]['documento_id'] . ')"><b><i class="fa fa-eye"" style="color:b"></i><b></a>';
            }
            $data[$i]['acciones'] = $stringAcciones;
        }

        return $data;
    }

    public function obtenerCantidadReporteReporteOperacionesXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $tipoDocumentoIdFormateado = Util::convertirArrayXCadena($tipoDocumentoId);

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayXCadena($empresaId);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteReporteOperacionesXCriterios($empresaIdFormateado, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerReporteReporteOperacionesExcel($data)
    {
        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteReporteOperacionesExcel($data, "Reporte de Operaciones");
        }
    }

    private function crearReporteReporteOperacionesExcel($reportes, $titulo)
    {

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':I' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* F. Creación	F. Emisión	Vendedor	Tipo documento	Cliente	Serie	Número	Total
         */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'F. Creación');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Tipo documento');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Persona');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Serie');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Número');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Descripción');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Total S/.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Total $');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['fecha_creacion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, str_replace('00:00:00', '', $reporte['fecha_emision']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['documento_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['persona_nombre_completo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, ' ' . $reporte['serie']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, ' ' . $reporte['numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $reporte['total_soles']);
            $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $reporte['total_dolares']);
            $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->getNumberFormat()->setFormatCode('#,##0.00');


            $i += 1;
        }

        for ($i = 'A'; $i <= 'I'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function verDetallePorOperacion($documentoId)
    {
        return OperacionNegocio::create()->visualizarDocumento($documentoId);
    }

    //reporte ventas producto por periodo
    public function obtenerConfiguracionesInicialesProductoPorPeriodo()
    {
        $respuesta = new ObjectUtil();
        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->bien = BienNegocio::create()->obtenerBienKardexXEmpresa(-1);
        //        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipo();
        $respuesta->bien_tipo = BienNegocio::create()->obtenerBienTipoPadre();

        return $respuesta;
    }

    public function reporteProductoPorPeriodo($criterios, $tipo)
    {
        $bienTipo = $criterios[0]['bienTipo'];
        $tienda = $criterios[0]['tienda'];
        $bien = $criterios[0]['bien'];
        $periodo = $criterios[0]['periodo'];

        if (ObjectUtil::isEmpty($bienTipo)) {
            $bienTipo = $criterios[0]['bienTipoPadre'];
        }

        if (!ObjectUtil::isEmpty($bienTipo)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipo);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipo, $item['id']);
            }
        }

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmision']['inicio']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmision']['fin']);

        $tiendaIdFormateado = Util::convertirArrayXCadena($tienda);
        $bienIdFormateado = Util::convertirArrayXCadena($bien);
        $bienTipoIdFormateado = Util::convertirArrayXCadena($bienTipo);

        return Reporte::create()->reporteProductoPorPeriodo($bienIdFormateado, $bienTipoIdFormateado, $tiendaIdFormateado, $emisionInicio, $emisionFin, $tipo, $periodo);
    }

    public function obtenerReporteProductoPorPeriodoExcel($criterios, $tipo)
    {

        $respuesta = $this->reporteProductoPorPeriodo($criterios, $tipo);

        if ($tipo == 1) {
            $parametro->titulo = "REPORTE DE VENTAS DE PRODUCTOS POR PERIODO";
            $parametro->columnaImporte = "Importe vendido";
        }
        if ($tipo == 4) {
            $parametro->titulo = "REPORTE DE COMPRAS DE PRODUCTOS POR PERIODO";
            $parametro->columnaImporte = "Importe comprado";
        }

        if (ObjectUtil::isEmpty($respuesta)) {
            throw new WarningException("No existe datos para exportar");
        } else {
            $this->crearReporteProductoPorPeriodoExcel($respuesta, $parametro);
        }
    }

    private function crearReporteProductoPorPeriodoExcel($reportes, $parametro)
    {
        $titulo = $parametro->titulo;

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;

        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':I' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        //Cód. Bien	Bien	Tipo bien		Importe vendido
        $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Código');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Producto');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Periodo');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'G.P. Principal');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'G.P. Secundario');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Cantidad');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Unidad control');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $parametro->columnaImporte . ' S/.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $parametro->columnaImporte . ' $');

        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':I' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $reporte['bien_codigo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $reporte['bien_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['fecha_tiempo']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $reporte['bien_tipo_padre_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['bien_tipo_descripcion']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['cantidad_conv']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['unidad_control']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, round($reporte['importe_total_soles'], 2));
            $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, round($reporte['importe_total_dolares'], 2));
            $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->getNumberFormat()->setFormatCode('#,##0.00');

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->applyFromArray($this->estiloTextoInformacion);
            $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':I' . $i)->applyFromArray($this->estiloInformacion);

            $i += 1;
        }

        for ($i = 'A'; $i <= 'I'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle("reporte");

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);


        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/reporte.xlsx');

        return 1;
    }

    public function reportePorClienteObtenerGraficoClientesDolares($criterios, $sumatoria)
    {
        return $this->reportePorClienteObtenerGraficoClientes($criterios, $sumatoria, 4);
    }

    public function reportePorClienteObtenerGraficoClientesSoles($criterios, $sumatoria)
    {
        return $this->reportePorClienteObtenerGraficoClientes($criterios, $sumatoria, 2);
    }

    public function reportePorClienteObtenerGraficoClientes($criterios, $sumatoria, $monedaId)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        //seleccion de bien tipo e hijos        
        $bienTipo = $criterios[0]['bienTipo'];

        if (ObjectUtil::isEmpty($bienTipo)) {
            $bienTipo = $criterios[0]['bienTipoPadre'];
        }

        if (!ObjectUtil::isEmpty($bienTipo)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipo);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipo, $item['id']);
            }
        }
        $bienTipoIdFormateado = Util::convertirArrayEnCadena($bienTipo);
        // fin         

        $tipoDocumentoIdFormateado = Util::convertirArrayEnCadena($tipoDocumentoId);

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayEnCadena($empresaId);

        // Solo mostramos a los clientes que hayan representado más del 5% de ventas
        $importeMinimo = $sumatoria * 0.05;

        $reporte = Reporte::create()->reportePorClienteObtenerGraficoClientes($empresaIdFormateado, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $bienTipoIdFormateado, $monedaId, $importeMinimo);

        // Sumamos los totales
        if (!ObjectUtil::isEmpty($reporte)) {
            $sumatoriaClientes = 0;
            foreach ($reporte as $item) {
                $sumatoriaClientes = $sumatoriaClientes + $item['total'];
            }
            if ($sumatoria - $sumatoriaClientes > 0) {
                array_push($reporte, array("id" => 0, "persona_nombre_completo" => "Otros", "total" => $sumatoria - $sumatoriaClientes));
            }
        }

        return $reporte;
    }

    public function reportePorClienteObtenerGraficoProductosDolares($criterios, $sumatoria)
    {
        return $this->reportePorClienteObtenerGraficoProductos($criterios, $sumatoria, 4);
    }

    public function reportePorClienteObtenerGraficoProductosSoles($criterios, $sumatoria)
    {
        return $this->reportePorClienteObtenerGraficoProductos($criterios, $sumatoria, 2);
    }

    public function reportePorClienteObtenerGraficoProductos($criterios, $sumatoria, $monedaId)
    {
        $personaId = $criterios[0]['persona'];
        $tipoDocumentoId = $criterios[0]['documentoTipo'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        //seleccion de bien tipo e hijos        
        $bienTipo = $criterios[0]['bienTipo'];

        if (ObjectUtil::isEmpty($bienTipo)) {
            $bienTipo = $criterios[0]['bienTipoPadre'];
        }

        if (!ObjectUtil::isEmpty($bienTipo)) {
            $dataBienTipoHijos = BienNegocio::create()->obtenerBienTipoHijosXBienTipoPadreId($bienTipo);
        }

        if (!ObjectUtil::isEmpty($dataBienTipoHijos)) {
            foreach ($dataBienTipoHijos as $item) {
                array_push($bienTipo, $item['id']);
            }
        }
        $bienTipoIdFormateado = Util::convertirArrayEnCadena($bienTipo);
        // fin         

        $tipoDocumentoIdFormateado = Util::convertirArrayEnCadena($tipoDocumentoId);

        $empresaId = $criterios[0]['empresa'];
        $empresaIdFormateado = Util::convertirArrayEnCadena($empresaId);

        // Solo mostramos a los clientes que hayan representado más del 5% de ventas
        $importeMinimo = $sumatoria * 0.05;

        $reporte = Reporte::create()->reportePorClienteObtenerGraficoProductos($empresaIdFormateado, $tipoDocumentoIdFormateado, $personaId, $emisionInicio, $emisionFin, $bienTipoIdFormateado, $monedaId, $importeMinimo);

        // Sumamos los totales
        if (!ObjectUtil::isEmpty($reporte)) {
            $sumatoriaClientes = 0;
            foreach ($reporte as $item) {
                $sumatoriaClientes = $sumatoriaClientes + $item['total'];
            }
            if ($sumatoria - $sumatoriaClientes > 0) {
                array_push($reporte, array("id" => 0, "bien_tipo_descripcion" => "Otros", "bien_tipo_padre_descripcion" => "", "total" => $sumatoria - $sumatoriaClientes));
            }
        }

        return $reporte;
    }

    public function obtenerDataCotizaciones()
    {
        return Reporte::create()->obtenerDataCotizaciones();
    }

    public function obtenerCotizacionesDetalle($bienId)
    {
        return Reporte::create()->obtenerCotizacionesDetalle($bienId);
    }

    public function obtenerDataCotizacionesExt()
    {
        return Reporte::create()->obtenerDataCotizacionesExt();
    }

    public function obtenerCotizacionesDetalleExt($bienId)
    {
        return Reporte::create()->obtenerCotizacionesDetalleExt($bienId);
    }

    //TRANSFERENCIA TRANSFORMACION NO ATENDIDAS
    public function obtenerConfiguracionesInicialesTransferenciaTransformacionNoAtendida()
    {
        $respuesta = new ObjectUtil();
        $respuesta->dataMotivoTraslado = DocumentoTipoDatoListaNegocio::create()->obtenerXIds('321,322');
        //        $respuesta->dataPersona=  null;        
        $respuesta->dataFecha = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();

        return $respuesta;
    }

    public function obtenerReporteTransferenciaTransformacionNoAtendidaXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $motivoTraslado = $criterios[0]['motivoTraslado'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $motivoTrasladoIds = Util::convertirArrayXCadena($motivoTraslado);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return Reporte::create()->obtenerReporteTransferenciaTransformacionNoAtendidaXCriterios($motivoTrasladoIds, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadReporteTransferenciaTransformacionNoAtendidaXCriterios($criterios, $elemntosFiltrados, $columns, $order, $start)
    {
        $motivoTraslado = $criterios[0]['motivoTraslado'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $motivoTrasladoIds = Util::convertirArrayXCadena($motivoTraslado);

        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        return Reporte::create()->obtenerCantidadReporteTransferenciaTransformacionNoAtendidaXCriterios($motivoTrasladoIds, $emisionInicio, $emisionFin, $columnaOrdenar, $formaOrdenar);
    }

    //fin TRANSFERENCIA TRANSFORMACION NO ATENDIDAS
    // TRANSFERENCIA DE PRODUCTOS DIFERENTES
    public function obtenerReporteTransferenciaDiferenteXCriterios($elemntosFiltrados, $columns, $order, $start)
    {
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return Reporte::create()->obtenerReporteTransferenciaDiferenteXCriterios($columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start);
    }

    public function obtenerCantidadReporteTransferenciaDiferenteXCriterios($elemntosFiltrados, $columns, $order, $start)
    {
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

        return Reporte::create()->obtenerCantidadReporteTransferenciaDiferenteXCriterios($columnaOrdenar, $formaOrdenar);
    }

    //FIN TRANSFERENCIA DE PRODUCTOS DIFERENTES
    public function estiloTituloColumnasConParametros($fuenteNombre = 'Arial', $fuenteTamanio = '10', $bordeEstilo = 'thin', $colorCelda = 'FFFFFF', $rellenoEstilo = 'solid')
    {
        return
            array(
                'font' => array(
                    'name' => $fuenteNombre,
                    'bold' => true,
                    'size' => $fuenteTamanio
                ),
                'borders' => array(
                    'allborders' => array(
                        //                    'style' => PHPExcel_Style_Border::BORDER_HAIR, 
                        'style' => $bordeEstilo,
                        'color' => array(
                            'rgb' => '000000'
                        )
                    )
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'wrap' => FALSE
                ),
                'fill' => array(
                    //                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'type' => $rellenoEstilo,
                    'color' => array('rgb' => $colorCelda)
                )
            );
    }

    public function exportarReporteVentas($proveedor)
    {

        $data = Reporte::create()->exportarReporteVentas($proveedor);

        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe información para exportar");
        } else {
            $data_grafico_vencidas = Reporte::create()->obtenerDataVencidasGraficoReporteVentas();
            $data_grafico_vigentes = Reporte::create()->obtenerDataVigentesGraficoReporteVentas();



            $this->estilosExcel();
            $objPHPExcel = new PHPExcel();
            $worksheet = $objPHPExcel->getSheet(0);

            $i = 1;
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $i . ':Q' . $i);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'REPORTE  DE FACTURAS DE VENTA');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':Q' . $i)->applyFromArray($this->estiloTituloReporte);

            $i += 2;

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'Fecha Emisión');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Tipo');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Número');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Cliente');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Moneda');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Valor venta');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Precio venta');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Total pagado');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Fecha pago Precio Venta');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'Importe por Nota de Crédito');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, 'Estado pago');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'Retención 3%');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, 'Fecha pago Retención');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, 'Fecha Recepción');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, 'Días Credito');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, 'Fecha Vencimiento');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, 'Días vencidos');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':Q' . $i)->applyFromArray($this->estiloTituloColumnasConParametros());

            $i += 1;
            foreach ($data as $value) {
                $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $value['fecha_emision']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $value['tipo']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $value['numero']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $value['nombre']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $value['moneda']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $value['subtotal']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $value['total']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $value['importe_pagado']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $value['fecha_total']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, $value['importe_nota']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, $value['estado_pago']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, $value['retencion']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, $value['fecha_retencion']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, $value['fecha_recepcion']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, $value['dias_credito']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, $value['fecha_vencimiento']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, $value['dias_vencidos']);
                $i += 1;
            }

            $objPHPExcel->getActiveSheet()->getStyle('A4' . ':Q' . ($i - 1))->applyFromArray($this->estiloInformacion);
            $objPHPExcel->getActiveSheet()->getStyle('E4:H' . ($i - 1))->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle('J4:J' . ($i - 1))->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->getActiveSheet()->getStyle('L4:L' . ($i - 1))->getNumberFormat()->setFormatCode('#,##0.00');

            //        for ($i = 'A'; $i <= 'I'; $i++) {
            //
            //            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
            //        }
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(100);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);

            if (!ObjectUtil::isEmpty($data_grafico_vencidas)) {
                $grafico_vencidas = GraficoNegocio::create()->graficarDeudasVencidasxCliente($data_grafico_vencidas);
                $objDrawingPType = new PHPExcel_Worksheet_Drawing();
                $objDrawingPType->setWorksheet($worksheet);
                $objDrawingPType->setName("grafico1");
                $objDrawingPType->setPath($grafico_vencidas);
                $celda1 = 'D' . ($i + 5);
                $objDrawingPType->setCoordinates($celda1);
                $objDrawingPType->setHeight(1400);
                $objDrawingPType->setWidth(700);
            }
            if (!ObjectUtil::isEmpty($data_grafico_vigentes)) {
                $grafico_vigentes = GraficoNegocio::create()->graficarDeudasVigentesxCliente($data_grafico_vigentes);
                $objDrawingPType = new PHPExcel_Worksheet_Drawing();
                $objDrawingPType->setWorksheet($worksheet);
                $objDrawingPType->setName("grafico2");
                $objDrawingPType->setPath($grafico_vigentes);
                $celda1 = 'D' . ($i + 30);
                $objDrawingPType->setCoordinates($celda1);
                $objDrawingPType->setHeight(1400);
                $objDrawingPType->setWidth(700);
            }

            $x = $i;
            for ($a = 1; $a <= $x; $a++) {
                $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
            }

            $objPHPExcel->getActiveSheet()->setTitle("Reporte Facturas de Venta");
            $objPHPExcel->setActiveSheetIndex(0);

            //        $fecReporte = date("d-m-Y_h-i_a");
            $nombre = "Reporte_Facturas_de_Venta.xlsx";
            $ruta_archivo = __DIR__ . '/../../util/formatos/' . $nombre;
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save($ruta_archivo);

            $url = explode(__DIR__ . "/../../", $ruta_archivo);
            $url = "modeloNegocio\almacen/../../" . $url[1];
            return $url;
        }
    }

    public function exportarReporteVentasConFormato($proveedor)
    {

        $data = Reporte::create()->exportarReporteVentas($proveedor);

        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe información para exportar");
        } else {
            $data_grafico_vencidas = Reporte::create()->obtenerDataVencidasGraficoReporteVentas();
            $data_grafico_vigentes = Reporte::create()->obtenerDataVigentesGraficoReporteVentas();

            $estilos_cabecera = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => true,
                    'italic' => false,
                    'strike' => false,
                    'size' => 16,
                    'color' => array('rgb' => 'FFFFFF'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4d70b5')
                )
            );

            $estilos_columna = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => true,
                    'italic' => false,
                    'strike' => false,
                    'size' => 11,
                    'color' => array('rgb' => 'FFFFFF'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4d70b5'),
                )
            );

            $estilos_tabla = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => true,
                    'italic' => false,
                    'strike' => false,
                    'size' => 11,
                    'color' => array('rgb' => 'FFFFFF'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4d70b5'),
                ), 'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            $estilos_retencion = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => false,
                    'italic' => false,
                    'strike' => false,
                    'size' => 11,
                    'color' => array('rgb' => '000000'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'A9D08E'),
                ), 'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            $estilos_filas = array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFBE5'),
                ), 'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );

            $aplicar_estilo = function ($estado) {
                switch ($estado) {
                    case 'Pagada':
                        $color = '006100';
                        $fondo = 'C6EFCE';
                        break;
                    case 'Anulada':
                        $color = '9C6500';
                        $fondo = 'FFEB9C';
                        break;
                    case 'Por cobrar':
                        $color = '375623';
                        $fondo = 'CCFF66';
                        break;
                    case 'Transferencia gratuita':
                        $color = '305496';
                        $fondo = 'DDEBF7';
                        break;
                    case 'Vencida':
                    case 'Vencida Parcialmente':
                        $color = '960006';
                        $fondo = 'FFC7CE';
                        break;
                }
                return array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('rgb' => $color)
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => $fondo)
                    ),
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
            };
            $objPHPExcel = new PHPExcel();
            $i = 1;
            $worksheet = $objPHPExcel->getSheet(0);
            $objPHPExcel->getActiveSheet()->getCell('A' . $i)->setValue('REPORTE FACTURAS DE VENTAS');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->applyFromArray($estilos_cabecera);
            //            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':S' . $i);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':Y' . $i);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $i++;
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':S' . $i);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'MES');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'CLIENTE');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Nº FACTURA');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'NOMBRE DE PROYECTO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'TIPO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'FECHA EMISION');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'MONEDA');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'SUBTOTAL (SIN IGV)');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'TOTAL (CON IGV)');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'TIPO DE AFECTO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, 'VALOR AFECTO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'FECHA RECEP.');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, 'COMP. RET.');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, 'FECHA PAGO RET.');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, 'IMPORTE NOTA CRED.');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, 'PAGO NETO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, 'CREDITO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, 'FECHA VCTO.');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, 'ESTADO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('T' . $i, 'DIAS MOROSIDAD');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('U' . $i, 'FECHA CANCELACION');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('V' . $i, 'BANCO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('W' . $i, 'DIAS DE PAGO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('X' . $i, 'TOTAL PAGADO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('Y' . $i, 'TOTAL DEUDA');

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':Y' . $i)->applyFromArray($estilos_tabla);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':Y' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':Y' . $i)->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(9);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(9);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(9);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(13);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getStyle('A:Y')->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('B:E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('L')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('M')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('H:I')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('K')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('O:Q')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('R')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('S')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('T')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('U')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('V')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('W')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('A:Y')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(50);
            $i++;
            $iInicioDetalle = $i;

            foreach ($data as $value) {

                $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $value['mes_emision']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $value['persona_nombre_completo']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $value['numero']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $value['proyecto']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $value['tipo']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $value['fecha_emision']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $value['moneda']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $value['subtotal']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $value['total']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, $value['tipo_afecto']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, $value['detraccion_retencion']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, $value['fecha_recepcion_1']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, $value['comprobante_retencion']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, is_null($value['fecha_retencion']) ? '-' : $value['fecha_retencion']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, is_null($value['importe_nota']) ? '0' : $value['importe_nota']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, $value['pago_neto_1']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, $value['dias_credito']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, $value['fecha_vencimiento']);

                $estado = $value['estado_pago'];
                $morosidad = is_null($value['fecha_emision']) || in_array(strtoupper($estado), ["PAGADA", "ANULADA", "TRANSFERENCIA GRATUITA"]);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('T' . $i, $morosidad ? '-' : ('=(TODAY()-F' . $i . ')-Q' . $i));
                //               CHL: Descomentar $objPHPExcel->getActiveSheet()->getStyle('T' . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_GENERAL);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('U' . $i, is_null($value['fecha_total']) ? '-' : $value['fecha_total']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('V' . $i, is_null($value['bancos']) ? '-' : $value['bancos']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('W' . $i, (!is_null($value['fecha_total']) && !is_null($value['fecha_emision'])) ? ('=U' . $i . '-L' . $i) : '-');
                $objPHPExcel->setActiveSheetIndex()->setCellValue('X' . $i, ($value['pagos_varios']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('Y' . $i, ($value['total']) - ($value['pagos_varios']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, $estado);
                //               CHL: Descomentar $objPHPExcel->getActiveSheet()->getStyle('S' . $i)->applyFromArray($aplicar_estilo($estado));

                /* CHL: Descomentar
                if ($value['simbolo'] === '$') {
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $i . ':I' . $i)->getNumberFormat()->setFormatCode("[$$-es-US]#,##0.00");
                    $objPHPExcel->getActiveSheet()->getStyle('O' . $i . ':P' . $i)->getNumberFormat()->setFormatCode("[$$-es-US]#,##0.00");
                    $objPHPExcel->getActiveSheet()->getStyle('K' . $i)->getNumberFormat()->setFormatCode("[$$-es-US]#,##0.00");
                } else {
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $i . ':I' . $i)->getNumberFormat()->setFormatCode("\"S/.\"#,##0.00");
                    $objPHPExcel->getActiveSheet()->getStyle('O' . $i . ':P' . $i)->getNumberFormat()->setFormatCode("\"S/.\"#,##0.00");
                    $objPHPExcel->getActiveSheet()->getStyle('K' . $i)->getNumberFormat()->setFormatCode("\"S/.\"#,##0.00");
                }
                 */
                $i += 1;
            }

            $objPHPExcel->getActiveSheet()->getStyle('T' . $iInicioDetalle . ':T' . ($i - 1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_GENERAL);
            $objPHPExcel->getActiveSheet()->getStyle('H' . $iInicioDetalle . ':I' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");
            $objPHPExcel->getActiveSheet()->getStyle('K' . $iInicioDetalle . ':K' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");
            $objPHPExcel->getActiveSheet()->getStyle('O' . $iInicioDetalle . ':P' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");
            $objPHPExcel->getActiveSheet()->getStyle('O' . $iInicioDetalle . ':Y' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");
            $objPHPExcel->getActiveSheet()->getStyle('O' . $iInicioDetalle . ':S' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");

            // CHL: Descomentar:
            $objPHPExcel->getActiveSheet()->getStyle('A' . $iInicioDetalle . ':R' . ($i - 1))->applyFromArray($estilos_filas);
            $objPHPExcel->getActiveSheet()->getStyle('T' . $iInicioDetalle . ':Y' . ($i - 1))->applyFromArray($estilos_filas);
            $objPHPExcel->getActiveSheet()->freezePane('D' . $iInicioDetalle);


            //            CHL: Descomentar
            if (!ObjectUtil::isEmpty($data_grafico_vencidas)) {
                $grafico_vencidas = GraficoNegocio::create()->graficarDeudasVencidasxCliente($data_grafico_vencidas);
                $objDrawingPType = new PHPExcel_Worksheet_Drawing();
                $objDrawingPType->setWorksheet($worksheet);
                $objDrawingPType->setName("grafico1");
                $objDrawingPType->setPath($grafico_vencidas);
                $celda1 = 'D' . ($i + 5);
                $objDrawingPType->setCoordinates($celda1);
                $objDrawingPType->setHeight(1400);
                $objDrawingPType->setWidth(700);
            }
            //CHL: Descomentar
            if (!ObjectUtil::isEmpty($data_grafico_vigentes)) {
                $grafico_vigentes = GraficoNegocio::create()->graficarDeudasVigentesxCliente($data_grafico_vigentes);
                $objDrawingPType = new PHPExcel_Worksheet_Drawing();
                $objDrawingPType->setWorksheet($worksheet);
                $objDrawingPType->setName("grafico2");
                $objDrawingPType->setPath($grafico_vigentes);
                $celda1 = 'D' . ($i + 45);
                $objDrawingPType->setCoordinates($celda1);
                $objDrawingPType->setHeight(1400);
                $objDrawingPType->setWidth(700);
            }

            $objPHPExcel->getActiveSheet()->setTitle("Reporte Facturas de Venta");
            $objPHPExcel->setActiveSheetIndex(0);
            $nombre = "Reporte_Facturas_de_Venta.xlsx";
            $ruta_archivo = __DIR__ . '/../../util/formatos/' . $nombre;
            $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->setPreCalculateFormulas(true);
            $objWriter->save($ruta_archivo);

            $url = explode(__DIR__ . "/../../", $ruta_archivo);
            $url = "modeloNegocio\almacen/../../" . $url[1];
            return $url;
        }
    }

    //PARA REPORTE ORDEN DE TRABAJO
    public function obtenerConfiguracionesInicialesReporteOrdenTrabajo()
    {
        $respuesta = new ObjectUtil();
        //        $operacionTipoIds = ''; //tipos: (1),(2) o '': para todos
        //        $respuesta->documento_tipo = DocumentoTipoNegocio::create()->obtenerDocumentoTipoReporteXOperacionTipos($operacionTipoIds);
        $respuesta->persona = PersonaNegocio::create()->obtenerActivas();
        //        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimeraOrdenTrabajo();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        //        $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
        return $respuesta;
    }

    public function obtenerReporteOrdenTrabajoXCriterios($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $serie = $criterios[0]['serie'];
        $numero = $criterios[0]['numero'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        return Reporte::create()->obtenerReporteOrdenTrabajoXCriterios($personaId, $serie, $numero, $emisionInicio, $emisionFin);
    }
    public function obtenerReporteIngresosVSGastosExcel($criterios)
    {
        $i = 1;
        $personaId = $criterios[0]['persona'];
        $serie = $criterios[0]['serie'];
        $numero = $criterios[0]['numero'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $titulo = "Reporte de Ordenes de Trabajo";
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B' . $i . ':J' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':J' . $i)->applyFromArray($this->estiloTituloReporte);
        $dataOT = Reporte::create()->obtenerReporteOrdenTrabajoXCriterios($personaId, $serie, $numero, $emisionInicio, $emisionFin);
        foreach ($dataOT as $dataItem) {
            $datos = DocumentoNegocio::create()->verDetallePorOrdenTrabajo($dataItem['documento_id']); 

            if(!ObjectUtil::isEmpty($datos->detalleFacturacion->datosDetalle) || !ObjectUtil::isEmpty($datos->detalleSolicitado->datosDetalle) || !ObjectUtil::isEmpty($datos->detalleEAR->datosDetalle) || !ObjectUtil::isEmpty($datos->detalleOtros->datosDetalle) || !ObjectUtil::isEmpty($datos->detalleRH->datosDetalle)){
                $i += 3;

                foreach ($datos->cabecera as $item) {
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':C' . $i)->applyFromArray($this->estiloTituloColumnas);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Documento N°');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $item['serie_numero']);
                    $i += 1;
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':C' . $i)->applyFromArray($this->estiloTituloColumnas);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Total documento');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $item['total']);
                    $i += 2;
                }
                /* F. Creación	F. Emisión	Vendedor	Tipo documento	Cliente	Serie	Número	Total
                */
                $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'Documento Tipo');
                $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Documento');
                $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Tipo');
                $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'F. Emisión');
                $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Sub Total');
                $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Igv');
                $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'Ingresos');
                $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'Gastos');
                $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'Margen');

                $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':J' . $i)->applyFromArray($this->estiloTituloColumnas);

                $i += 1;
                $totalesIngresos = 0;
                $totalesGastos = 0;
                //Facturado
                foreach ($datos->detalleFacturacion->datosDetalle as $item) {
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':J' . $i)->applyFromArray($this->estiloTituloColumnasConParametros());
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, ' ' . $item['documento_tipo']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, ' ' . $item['serie_numero']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Facturado');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, str_replace('00:00:00', '', $item['fecha_emision']));
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $item['subtotal']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $item['igv']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $item['total']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, '');
                    $objPHPExcel->getActiveSheet()->getStyle('F' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('G' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $i += 1;
                    $totalesIngresos = $totalesIngresos + $item['total'];
                }
                //Ear solicitado
                foreach ($datos->detalleSolicitado->datosDetalle as $item) {
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':J' . $i)->applyFromArray($this->estiloTituloColumnasConParametros());
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, ' ');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, ' ' . $item['ear_numero']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Ear Solicitado');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, str_replace('00:00:00', '', $item['fecha_ear_sol']));
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, '');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, '');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, '');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $item['total']);
                    $objPHPExcel->getActiveSheet()->getStyle('F' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('G' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $i += 1;
                    $totalesGastos = $totalesGastos + $item['total'];
                }
                //Ear Rendido
                foreach ($datos->detalleEAR->datosDetalle as $item) {
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':J' . $i)->applyFromArray($this->estiloTituloColumnasConParametros());
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $item['documento_tipo_descripcion']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, ' ' . $item['serie_numero']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Rendido EAR');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, str_replace('00:00:00', '', $item['fecha_emision_ord']));
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $item['subtotal']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $item['igv']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, '');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $item['total']);
                    $objPHPExcel->getActiveSheet()->getStyle('F' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('G' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $i += 1;
                    $totalesGastos = $totalesGastos + $item['total'];
                }
                //Adicionales Orden de trabajo
                foreach ($datos->detalleOtros->datosDetalle as $item) {
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':J' . $i)->applyFromArray($this->estiloTituloColumnasConParametros());
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $item['documento_tipo_descripcion']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, ' ' . $item['serie_numero']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Costos adicionales');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, str_replace('00:00:00', '', $item['fecha_emision_ord']));
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $item['subtotal']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $item['igv']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, '');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $item['total']);
                    $objPHPExcel->getActiveSheet()->getStyle('F' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('G' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $i += 1;
                    $totalesGastos = $totalesGastos + $item['total'];
                }
                //Adicionaes Orde de Trabajo
                foreach ($datos->detalleRH->datosDetalle as $item) {
                    $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':J' . $i)->applyFromArray($this->estiloTituloColumnasConParametros());
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, ' ');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, ' ' . $item['num_ear']);
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'Recibo por honorarios (Adicionales + EAR)');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, str_replace('00:00:00', '', $item['fecha_emision_ord']));
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, '');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, '');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, '');
                    $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $item['total']);
                    $objPHPExcel->getActiveSheet()->getStyle('F' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('G' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $i += 1;
                    $totalesGastos = $totalesGastos + $item['total'];
                }
                $objPHPExcel->getActiveSheet()->getStyle('H' . $i . ':J' . $i)->applyFromArray($this->estiloTituloColumnasConParametros());
                $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $totalesIngresos);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $totalesGastos);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, ($totalesIngresos - $totalesGastos));
                $objPHPExcel->getActiveSheet()->getStyle('H' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle('I' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                $objPHPExcel->getActiveSheet()->getStyle('J' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                $i += 1;
            }
        }
        for ($i = 'B'; $i <= 'I'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);

        $ruta_archivo = __DIR__ . '/../../util/formatos/reporteIngresosVSGastos.xlsx';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($ruta_archivo);

        $url = explode(__DIR__ . "/../../", $ruta_archivo);
        $url = "modeloNegocio\almacen/../../" . $url[1];
        return $url;
    }
    //PARA REPORTE COTIZACION POR ESTADO
    public function obtenerConfiguracionesInicialesCotizacion()
    {
        $respuesta = new stdClass();
        $respuesta->persona = PersonaNegocio::create()->obtenerActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        return $respuesta;
    }
    public function obtenerReporteCotizacionXCriterios($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $serie = $criterios[0]['serie'];
        $numero = $criterios[0]['numero'];
        $estado = $criterios[0]['estado'];
        if($estado == 1){
            $estado = "(1,3,6,7)";
        }else if($estado == 2){
            $estado = "(2,9)";
        }else{
            $estado = "(1,2,3,6,7,9)";
        }

        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        return Reporte::create()->obtenerReporteCotizacionXCriterios($personaId, $serie, $numero, $estado, $emisionInicio, $emisionFin);
    }
    public function verDetallePorCotizacion($documentoId, $movimientoId)
    {
        return MovimientoNegocio::create()->visualizarDocumento($documentoId, $movimientoId);
    }
    public function crearReporteReporteCotizacionesExcel($criterios)
    {

        $personaId = $criterios[0]['persona'];
        $serie = $criterios[0]['serie'];
        $numero = $criterios[0]['numero'];
        $estado = $criterios[0]['estado'];
        if($estado == 1){
            $estado = "(1,3,6,7)";
        }else if($estado == 2){
            $estado = "(2,9)";
        }else{
            $estado = "(1,2,3,6,7,9)";
        }
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        $reportes = Reporte::create()->obtenerReporteCotizacionXCriterios($personaId, $serie, $numero, $estado, $emisionInicio, $emisionFin);

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;
        $titulo = "Reporte de Cotizaciones";
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B' . $i . ':G' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':G' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* F. Creación	F. Emisión	Vendedor	Tipo documento	Cliente	Serie	Número	Total
         */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Persona');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'S/N');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Moneda');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Total S/.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Estado');

        $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':G' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':G' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, str_replace('00:00:00', '', $reporte['fecha_emision']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['persona_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, ' ' . $reporte['serie_numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['moneda']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['total']);
            $objPHPExcel->getActiveSheet()->getStyle('F' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['estado']);

            $i += 1;
        }

        for ($i = 'B'; $i <= 'G'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);

        $ruta_archivo = __DIR__ . '/../../util/formatos/reporteCotizacion.xlsx';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($ruta_archivo);

        $url = explode(__DIR__ . "/../../", $ruta_archivo);
        $url = "modeloNegocio\almacen/../../" . $url[1];
        return $url;
    }
    //PARA REPORTE ORDEN DE TRABAJO POR ESTADO
    public function obtenerConfiguracionesInicialesOrdenTrabajo()
    {
        $respuesta = new stdClass();
        $respuesta->persona = PersonaNegocio::create()->obtenerActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->progreso = Movimiento::create()->obtenerProgresoXMovimientoTipo(140);
        return $respuesta;
    }
    public function obtenerDataReporteOrdenTrabajoPorEstado($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $serie = $criterios[0]['serie'];
        $numero = $criterios[0]['numero'];
        $progreso = $criterios[0]['progreso'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        return Reporte::create()->obtenerReporteOrdenTrabajoPorEstadoXCriterios($personaId, $serie, $numero, $progreso, $emisionInicio, $emisionFin);
    }
    public function verDetallePorOrdenTrabajoPorEstado($documentoId, $movimientoId)
    {
        return MovimientoNegocio::create()->visualizarDocumento($documentoId, $movimientoId);
    }
    public function crearReporteReporteOrdenTrabajoPorEstadoExcel($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $serie = $criterios[0]['serie'];
        $numero = $criterios[0]['numero'];
        $progreso = $criterios[0]['progreso'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        $reportes =  Reporte::create()->obtenerReporteOrdenTrabajoPorEstadoXCriterios($personaId, $serie, $numero, $progreso, $emisionInicio, $emisionFin);

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        $i = 1;
        $titulo = "Reporte de Cotizaciones";
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B' . $i . ':G' . $i);
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $titulo);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':G' . $i)->applyFromArray($this->estiloTituloReporte);

        $i += 2;
        /* F. Creación	F. Emisión	Vendedor	Tipo documento	Cliente	Serie	Número	Total
         */
        $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'F. Emisión');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Persona');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'S/N');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'Moneda');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'Total S/.');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Estado');
        $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'Porcentaje de avance (%)');

        $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':G' . $i)->applyFromArray($this->estiloTituloColumnas);

        $i += 1;

        foreach ($reportes as $reporte) {

            $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':G' . $i)->applyFromArray($this->estiloInformacion);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, str_replace('00:00:00', '', $reporte['fecha_emision']));
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $reporte['persona_nombre']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, ' ' . $reporte['serie_numero']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $reporte['moneda']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $reporte['total']);
            $objPHPExcel->getActiveSheet()->getStyle('F' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['estado']);
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $reporte['porcentaje']);

            $i += 1;
        }

        for ($i = 'B'; $i <= 'G'; $i++) {

            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $x = $i;
        for ($a = 1; $a <= $x; $a++) {
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(-1);
        }

        $objPHPExcel->getActiveSheet()->setTitle($titulo);

        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);

        $ruta_archivo = __DIR__ . '/../../util/formatos/reporteOrdenTrabajo.xlsx';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($ruta_archivo);

        $url = explode(__DIR__ . "/../../", $ruta_archivo);
        $url = "modeloNegocio\almacen/../../" . $url[1];
        return $url;
    }
    //REPORTE CONSOLIDADO DE COTIZACION
    public function obtenerConfiguracionesInicialesConsolidadoCotizacion()
    {
        $respuesta = new stdClass();
        $respuesta->persona = PersonaNegocio::create()->obtenerActivas();
        $respuesta->fecha_primer_documento = DocumentoNegocio::create()->obtenerFechaPrimerDocumento();
        $respuesta->segun = DocumentoTipoDatoListaNegocio::create()->obtenerXDocumentoTipoDato(2945);
        return $respuesta;
    }
    public function obtenerDataReporteConsolidadoCotizacion($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $serie = $criterios[0]['serie'];
        $numero = $criterios[0]['numero'];
        $segun = $criterios[0]['segun'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        return Reporte::create()->obtenerReporteConsolidadoCotizacionXCriterios($personaId, $serie, $numero,$segun, $emisionInicio, $emisionFin);
    }

    public function crearReporteConsolidadoCotizacionExcel($criterios)
    {
        $personaId = $criterios[0]['persona'];
        $serie = $criterios[0]['serie'];
        $numero = $criterios[0]['numero'];
        $segun = $criterios[0]['segun'];
        $emisionInicio = $this->formatearFechaBD($criterios[0]['fechaEmisionDesde']);
        $emisionFin = $this->formatearFechaBD($criterios[0]['fechaEmisionHasta']);

        $reportes = Reporte::create()->obtenerReporteConsolidadoCotizacionXCriterios($personaId, $serie, $numero,$segun, $emisionInicio, $emisionFin);
    
        $segun = DocumentoTipoDatoListaNegocio::create()->obtenerXDocumentoTipoDato(2945);

        $this->estilosExcel();
        $objPHPExcel = new PHPExcel();

        foreach ($segun as $segunItem) {
            $nuevaHoja = new PHPExcel_Worksheet($objPHPExcel);
            $objPHPExcel->addSheet($nuevaHoja);
            // Establecer el título de la hoja
            $nuevaHoja->setTitle($segunItem['descripcion']);

            $i = 2;
            $nuevaHoja->setCellValue('B' . $i, 'TICKET');
            $nuevaHoja->setCellValue('C' . $i, 'CC');
            $nuevaHoja->setCellValue('D' . $i, 'AGENCIA');
            $nuevaHoja->setCellValue('E' . $i, 'ZONA');
            $nuevaHoja->setCellValue('F' . $i, 'RESPONSABLE');
            $nuevaHoja->setCellValue('G' . $i, 'FECHA DE SOLIC');
            $nuevaHoja->setCellValue('H' . $i, 'SSGG/MOB&EQUI');
            $nuevaHoja->setCellValue('I' . $i, 'CONCEPTO');
            $nuevaHoja->setCellValue('J' . $i, 'SUB CATEGORIA');
            $nuevaHoja->setCellValue('K' . $i, 'DETALLE');
            $nuevaHoja->setCellValue('L' . $i, 'FUENTE');
            $nuevaHoja->setCellValue('M' . $i, 'TIPO');
            $nuevaHoja->setCellValue('N' . $i, 'COTIZACION');
            $nuevaHoja->setCellValue('O' . $i, 'PREV/CORRECT.');
            $nuevaHoja->setCellValue('P' . $i, 'PROVEEDOR');
            $nuevaHoja->setCellValue('Q' . $i, 'COSTO DIRECTO ');
            $nuevaHoja->setCellValue('R' . $i, 'IGV');
            $nuevaHoja->setCellValue('S' . $i, 'TOTAL');

            $estiloTitulo = array(
                'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'e20076')),
                'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
            );
            $nuevaHoja->getStyle('B' . $i . ':S' . $i)->applyFromArray($estiloTitulo);
            $nuevaHoja->setAutoFilter('B' . $i . ':S' . $i);

            $i += 1;
            foreach ($reportes as $reporte) {
                $nuevaHoja->getStyle('B' . $i . ':S' . $i)->applyFromArray($this->estiloInformacion);
                if($reporte['tipo'] == $segunItem['descripcion']){
                    $nuevaHoja->setCellValue('B' . $i, $reporte['ticket']);
                    $nuevaHoja->setCellValue('C' . $i, $reporte['agencia_codigo']);
                    $nuevaHoja->setCellValue('D' . $i, $reporte['agencia']);
                    $nuevaHoja->setCellValue('E' . $i, $reporte['agencia_zona']);
                    $nuevaHoja->setCellValue('F' . $i,$reporte['responsable']);
                    $nuevaHoja->setCellValue('G' . $i, str_replace('00:00:00', '', $reporte['fecha_emision']));
                    $nuevaHoja->setCellValue('H' . $i, $reporte['categoria']);
                    $nuevaHoja->setCellValue('I' . $i, '');
                    $nuevaHoja->setCellValue('J' . $i, '');
                    $nuevaHoja->setCellValue('K' . $i, trim(ltrim(str_replace('&nbsp;', ' ',strip_tags($reporte['detalle_producto'] .' '.$reporte['detalle'])), '.')));
                    $nuevaHoja->setCellValue('L' . $i, $reporte['fuente']);
                    $nuevaHoja->setCellValue('M' . $i, $reporte['tipo']);
                    $nuevaHoja->setCellValue('N' . $i, $reporte['serie_numero']);
                    $nuevaHoja->setCellValue('P' . $i, 'ABC');
                    $nuevaHoja->setCellValue('Q' . $i, $reporte['costo_directo']);
                    $nuevaHoja->setCellValue('R' . $i, $reporte['igv']);
                    $nuevaHoja->setCellValue('S' . $i, $reporte['total']);
                    $nuevaHoja->getStyle('Q' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $nuevaHoja->getStyle('R' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $nuevaHoja->getStyle('S' . $i)->getNumberFormat()->setFormatCode('#,##0.00');
                    $i += 1;
                }
            }
            foreach (range('A', 'S') as $columna) {
                $nuevaHoja->getColumnDimension($columna)->setAutoSize(true);
            }
            //$nuevaHoja->getColumnDimensionByColumn('E')->setAutoSize(true);
            /*for ($i = 'B'; $i <= 'S'; $i++) {
                $nuevaHoja->getColumnDimension($i)->setAutoSize(TRUE);
            }
            $x = $i;
            for ($a = 1; $a <= $x; $a++) {
                $nuevaHoja->getRowDimension($i)->setRowHeight(-1);
            }*/

        }
        // Eliminar la hoja por defecto
        $objPHPExcel->removeSheetByIndex(0);

        $ruta_archivo = __DIR__ . '/../../util/formatos/reporteConsolidadoCotizaciones.xlsx';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($ruta_archivo);

        $url = explode(__DIR__ . "/../../", $ruta_archivo);
        $url = "modeloNegocio\almacen/../../" . $url[1];
        return $url;
    }

    public function crearReporteCXPExcel($criterios)
    {
        $tipo1 = 4;
        $tipo2 = 6;
        $personaId = $criterios[0]['persona'];
        $personaId = (ObjectUtil::isEmpty($personaId) || $personaId * 1 == -1) ? null : $personaId;
        $mostrarPagados = $criterios[0]['mostrar'];
        $mostrarLib = $criterios[0]['mostrarLib'];
        if ($criterios[0]['fechaVencimientoDesde'] != '') {
            $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaVencimientoDesde']);
        }

        if ($criterios[0]['fechaVencimientoHasta'] != '') {
            $fechaVencimientoHasta = DateUtil::formatearCadenaACadenaBD($criterios[0]['fechaVencimientoHasta']);
        }
        $empresa = $criterios[0]['empresa'];
        $data = Reporte::create()->obtenerReporteDeudaExcelXCriterios($mostrarPagados, $mostrarLib, $tipo1, $tipo2, $empresa, $personaId, $fechaVencimientoDesde, $fechaVencimientoHasta);


        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe información para exportar");
        } else {
            $data_grafico_vencidas = Reporte::create()->obtenerDataVencidasGraficoReporteCompras();
            $data_grafico_vigentes = Reporte::create()->obtenerDataVigentesGraficoReporteCompras();

            $estilos_cabecera = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => true,
                    'italic' => false,
                    'strike' => false,
                    'size' => 16,
                    'color' => array('rgb' => 'FFFFFF'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4d70b5')
                )
            );

            $estilos_columna = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => true,
                    'italic' => false,
                    'strike' => false,
                    'size' => 11,
                    'color' => array('rgb' => 'FFFFFF'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4d70b5'),
                )
            );

            $estilos_tabla = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => true,
                    'italic' => false,
                    'strike' => false,
                    'size' => 11,
                    'color' => array('rgb' => 'FFFFFF'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4d70b5'),
                ), 'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            $estilos_retencion = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => false,
                    'italic' => false,
                    'strike' => false,
                    'size' => 11,
                    'color' => array('rgb' => '000000'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'A9D08E'),
                ), 'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            $estilos_filas = array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFBE5'),
                ), 'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );

            $aplicar_estilo = function ($estado) {
                switch ($estado) {
                    case 'Pagada':
                        $color = '006100';
                        $fondo = 'C6EFCE';
                        break;
                    case 'Anulada':
                        $color = '9C6500';
                        $fondo = 'FFEB9C';
                        break;
                    case 'Por cobrar':
                        $color = '375623';
                        $fondo = 'CCFF66';
                        break;
                    case 'Transferencia gratuita':
                        $color = '305496';
                        $fondo = 'DDEBF7';
                        break;
                    case 'Vencida':
                    case 'Vencida Parcialmente':
                        $color = '960006';
                        $fondo = 'FFC7CE';
                        break;
                }
                return array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('rgb' => $color)
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => $fondo)
                    ),
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
            };
            $objPHPExcel = new PHPExcel();
            $i = 1;
            $worksheet = $objPHPExcel->getSheet(0);
            $objPHPExcel->getActiveSheet()->getCell('A' . $i)->setValue('REPORTE DE CUENTAS POR PAGAR');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->applyFromArray($estilos_cabecera);
            //            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':S' . $i);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':S' . $i);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $i++;
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':S' . $i);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'MES');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'CLIENTE');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'Nº FACTURA');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'NOMBRE DE PROYECTO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'TIPO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'FECHA EMISION');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'MONEDA');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'SUBTOTAL (SIN IGV)');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'TOTAL (CON IGV)');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'TIPO DE AFECTO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, 'VALOR AFECTO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'PAGO NETO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, 'CREDITO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, 'FECHA VCTO.');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, 'ESTADO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, 'DIAS MOROSIDAD');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, 'BANCO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, 'TOTAL PAGADO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, 'TOTAL DEUDA');

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':S' . $i)->applyFromArray($estilos_tabla);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':S' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':S' . $i)->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(9);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(9);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getStyle('A:S')->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('B:E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('L')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('M')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('H:I')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('K')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('O:Q')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('O')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('P')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('Q')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('R')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('S')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('A:S')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(50);
            $i++;
            $iInicioDetalle = $i;

            foreach ($data as $value) {

                $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, $value['mes_emision']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $value['persona_nombre_completo']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $value['sn_documento']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $value['proyecto']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, $value['tipo']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, str_replace('00:00:00', '', $value['fecha_emision']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $value['moneda']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $value['subtotal']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $value['total']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, $value['tipo_afecto']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, $value['detraccion_retencion']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, $value['pago_neto_1']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, $value['dias_credito']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('N' . $i, str_replace('00:00:00', '', $value['fecha_vencimiento']));

                $estado = $value['estado_pago'];
                $morosidad = is_null($value['fecha_emision']) || in_array(strtoupper($estado), ["PAGADA", "ANULADA", "TRANSFERENCIA GRATUITA"]);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('O' . $i, $estado);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('P' . $i, $morosidad ? '-' : ('=(TODAY()-F' . $i . ')-M' . $i));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('Q' . $i, is_null($value['bancos']) ? '-' : $value['bancos']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('R' . $i, ($value['otros_pagos'] + $value['importe_pagado']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('S' . $i, ($value['total']) - ($value['otros_pagos'] + $value['importe_pagado']));

                /* CHL: Descomentar
                if ($value['simbolo'] === '$') {
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $i . ':I' . $i)->getNumberFormat()->setFormatCode("[$$-es-US]#,##0.00");
                    $objPHPExcel->getActiveSheet()->getStyle('O' . $i . ':P' . $i)->getNumberFormat()->setFormatCode("[$$-es-US]#,##0.00");
                    $objPHPExcel->getActiveSheet()->getStyle('K' . $i)->getNumberFormat()->setFormatCode("[$$-es-US]#,##0.00");
                } else {
                    $objPHPExcel->getActiveSheet()->getStyle('H' . $i . ':I' . $i)->getNumberFormat()->setFormatCode("\"S/.\"#,##0.00");
                    $objPHPExcel->getActiveSheet()->getStyle('O' . $i . ':P' . $i)->getNumberFormat()->setFormatCode("\"S/.\"#,##0.00");
                    $objPHPExcel->getActiveSheet()->getStyle('K' . $i)->getNumberFormat()->setFormatCode("\"S/.\"#,##0.00");
                }
                 */
                $i += 1;
            }

            $objPHPExcel->getActiveSheet()->getStyle('T' . $iInicioDetalle . ':T' . ($i - 1))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_GENERAL);
            $objPHPExcel->getActiveSheet()->getStyle('H' . $iInicioDetalle . ':H' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");
            $objPHPExcel->getActiveSheet()->getStyle('I' . $iInicioDetalle . ':I' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");
            $objPHPExcel->getActiveSheet()->getStyle('K' . $iInicioDetalle . ':K' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");
            $objPHPExcel->getActiveSheet()->getStyle('L' . $iInicioDetalle . ':L' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");
            $objPHPExcel->getActiveSheet()->getStyle('R' . $iInicioDetalle . ':R' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");
            $objPHPExcel->getActiveSheet()->getStyle('S' . $iInicioDetalle . ':S' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");

            // CHL: Descomentar:
            $objPHPExcel->getActiveSheet()->getStyle('A' . $iInicioDetalle . ':S' . ($i - 1))->applyFromArray($estilos_filas);
            $objPHPExcel->getActiveSheet()->freezePane('D' . $iInicioDetalle);


            //            CHL: Descomentar
            if (!ObjectUtil::isEmpty($data_grafico_vencidas)) {
                $grafico_vencidas = GraficoNegocio::create()->graficarDeudasVencidasxCliente($data_grafico_vencidas);
                $objDrawingPType = new PHPExcel_Worksheet_Drawing();
                $objDrawingPType->setWorksheet($worksheet);
                $objDrawingPType->setName("grafico1");
                $objDrawingPType->setPath($grafico_vencidas);
                $celda1 = 'D' . ($i + 5);
                $objDrawingPType->setCoordinates($celda1);
                $objDrawingPType->setHeight(1400);
                $objDrawingPType->setWidth(700);
            }
            //CHL: Descomentar
            if (!ObjectUtil::isEmpty($data_grafico_vigentes)) {
                $grafico_vigentes = GraficoNegocio::create()->graficarDeudasVigentesxCliente($data_grafico_vigentes);
                $objDrawingPType = new PHPExcel_Worksheet_Drawing();
                $objDrawingPType->setWorksheet($worksheet);
                $objDrawingPType->setName("grafico2");
                $objDrawingPType->setPath($grafico_vigentes);
                $celda1 = 'D' . ($i + 45);
                $objDrawingPType->setCoordinates($celda1);
                $objDrawingPType->setHeight(1400);
                $objDrawingPType->setWidth(700);
            }

            $objPHPExcel->getActiveSheet()->setTitle("Reporte Cuentas por Pagar");
            $objPHPExcel->setActiveSheetIndex(0);
            $nombre = "Reporte_Cuentas_porPagar.xlsx";
            $ruta_archivo = __DIR__ . '/../../util/formatos/' . $nombre;
            $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->setPreCalculateFormulas(true);
            $objWriter->save($ruta_archivo);

            $url = explode(__DIR__ . "/../../", $ruta_archivo);
            $url = "modeloNegocio\almacen/../../" . $url[1];
            return $url;
        }
    }

    public function crearReportePagoDetraccionExcel($data)
    {
        if (ObjectUtil::isEmpty($data)) {
            throw new WarningException("No existe información para exportar");
        } else {

            $estilos_cabecera = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => true,
                    'italic' => false,
                    'strike' => false,
                    'size' => 16,
                    'color' => array('rgb' => 'FFFFFF'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4d70b5')
                )
            );

            $estilos_columna = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => true,
                    'italic' => false,
                    'strike' => false,
                    'size' => 11,
                    'color' => array('rgb' => 'FFFFFF'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4d70b5'),
                )
            );

            $estilos_tabla = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => true,
                    'italic' => false,
                    'strike' => false,
                    'size' => 11,
                    'color' => array('rgb' => 'FFFFFF'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '4d70b5'),
                ), 'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            $estilos_retencion = array(
                'font' => array(
                    'name' => 'Calibri',
                    'bold' => false,
                    'italic' => false,
                    'strike' => false,
                    'size' => 11,
                    'color' => array('rgb' => '000000'),
                ), 'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'A9D08E'),
                ), 'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            $estilos_filas = array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FFFBE5'),
                ), 'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );

            $aplicar_estilo = function ($estado) {
                switch ($estado) {
                    case 'Pagada':
                        $color = '006100';
                        $fondo = 'C6EFCE';
                        break;
                    case 'Anulada':
                        $color = '9C6500';
                        $fondo = 'FFEB9C';
                        break;
                    case 'Por cobrar':
                        $color = '375623';
                        $fondo = 'CCFF66';
                        break;
                    case 'Transferencia gratuita':
                        $color = '305496';
                        $fondo = 'DDEBF7';
                        break;
                    case 'Vencida':
                    case 'Vencida Parcialmente':
                        $color = '960006';
                        $fondo = 'FFC7CE';
                        break;
                }
                return array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('rgb' => $color)
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => $fondo)
                    ),
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
            };
            $objPHPExcel = new PHPExcel();
            $i = 1;
            $worksheet = $objPHPExcel->getSheet(0);
            $objPHPExcel->getActiveSheet()->getCell('A' . $i)->setValue('REPORTE FACTURAS DE DETRACCIONES');
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->applyFromArray($estilos_cabecera);
            //            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':S' . $i);
            $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':M' . $i);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $i++;
            $objPHPExcel->getActiveSheet()->setAutoFilter('A' . $i . ':M' . $i);

            $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, 'F. EMISIÓN');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, 'TIPO DOC.');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, 'RUC');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, 'RAZÓN SOCIAL');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('E' . $i, 'C. SERV. / BIEN');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, 'CTA. CTE.');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, 'MONEDA');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, 'IMPORTE FACTURADO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, 'PARTE ENTERA');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, 'PERIODO TRIBUTARIO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('K' . $i, 'TIPO');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, 'SERIE');
            $objPHPExcel->setActiveSheetIndex()->setCellValue('M' . $i, 'N°');

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':M' . $i)->applyFromArray($estilos_tabla);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':M' . $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':M' . $i)->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(9);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(9);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(16);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(9);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getStyle('A:M')->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('D:E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->getStyle('L')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('M')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('N')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('H:I')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('K')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle('A:M')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(50);
            $i++;
            $iInicioDetalle = $i;

            foreach ($data as $value) {

                $objPHPExcel->setActiveSheetIndex()->setCellValue('A' . $i, str_replace('00:00:00', '', $value['fecha_emision']));
                $objPHPExcel->setActiveSheetIndex()->setCellValue('B' . $i, $value['tipo_doc_persona']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('C' . $i, $value['codigo_identificacion_persona']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('D' . $i, $value['nombre_persona']);
                $objPHPExcel->setActiveSheetIndex()->setCellValueExplicit('E' . $i, $value['detra_codigo']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('F' . $i, $value['numero_cuenta']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('G' . $i, $value['desc_moneda']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('H' . $i, $value['total']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('I' . $i, $value['detra_total']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('J' . $i, $value['periodo_documento']);
                $objPHPExcel->setActiveSheetIndex()->setCellValueExplicit('K' . $i, $value['tipo_documento']);
                $serie_numero = explode("-",$value['serie_numero']);
                $objPHPExcel->setActiveSheetIndex()->setCellValue('L' . $i, $serie_numero[0]);
                $objPHPExcel->setActiveSheetIndex()->setCellValueExplicit('M' . $i, $serie_numero[1]);

                $i += 1;
            }

            $objPHPExcel->getActiveSheet()->getStyle('H' . $iInicioDetalle . ':H' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");
            $objPHPExcel->getActiveSheet()->getStyle('I' . $iInicioDetalle . ':I' . ($i - 1))->getNumberFormat()->setFormatCode("#,##0.00");

            $objPHPExcel->getActiveSheet()->getStyle('A' . $iInicioDetalle . ':M' . ($i - 1))->applyFromArray($estilos_filas);
            $objPHPExcel->getActiveSheet()->freezePane('E' . $iInicioDetalle);


            $objPHPExcel->getActiveSheet()->setTitle("Reporte de Pago Detracción");
            $objPHPExcel->setActiveSheetIndex(0);
            $nombre = "Reporte_PagoDetraccion.xlsx";
            $ruta_archivo = __DIR__ . '/../../util/formatos/' . $nombre;
            $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
            $objWriter->setPreCalculateFormulas(true);
            $objWriter->save($ruta_archivo);

            $url = explode(__DIR__ . "/../../", $ruta_archivo);
            $url = "modeloNegocio\almacen/../../" . $url[1];
            return $url;
        }
    }
}
