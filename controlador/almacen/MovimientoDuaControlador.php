<?php

require_once __DIR__ . '/MovimientoControlador.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/MovimientoDuaNegocio.php';

class MovimientoDuaControlador extends MovimientoControlador {
    
    public function obtenerPlanillaImportacion(){
        $documentoId = $this->getParametro("documentoId");
        return MovimientoDuaNegocio::create()->obtenerPlanillaImportacion($documentoId);        
    }
    
    public function obtenerDocumentoRelacionVisualizar() {
        $data = parent::obtenerDocumentoRelacionVisualizar();
        $data = MovimientoDuaNegocio::create()->obtenerContabilizacion($data);
        
        $documentoId = $this->getParametro("documentoId");
        $movimientoId = $this->getParametro("movimientoId");
        
        $cif = MovimientoDuaNegocio::create()->obtenerCostoCifPorMovimientoId($movimientoId);
        
        if (!ObjectUtil::isEmpty($cif)){
            $data->cif = $cif;
        }
        
        return $data;
    }

    public function obtenerDocumentoRelacionDUA() {
        $data = parent::obtenerDocumentoRelacionVisualizar();
        return $data;
    }
    
    public function obtenerDocumentoRelacion() {
        $opcionId = $this->getOpcionId();
        $documentoTipoOrigenId = $this->getParametro("documento_id_origen");
        $documentoTipoDestinoId = $this->getParametro("documento_id_destino");
        $movimientoId = $this->getParametro("movimiento_id");
        $documentoId = $this->getParametro("documento_id");
        $documentoRelacionados = $this->getParametro("documentos_relacinados");
        $tempDocumentosRelacionados=array();
        
        foreach ($documentoRelacionados as $index => $item) {
                if($item['tipo']==1){
                    array_push($tempDocumentosRelacionados, $item);
                }
            }
            
        $documentoRelacionados= $tempDocumentosRelacionados;

        $data = MovimientoNegocio::create()->obtenerDocumentoRelacionDua($documentoTipoOrigenId, $documentoTipoDestinoId, $movimientoId, $documentoId, $opcionId, $documentoRelacionados);
        return $data;
    }
    
    public function obtenerDocumentosEarDua(){        
        $documentoId = $this->getParametro("documentoId");
        return MovimientoDuaNegocio::create()->obtenerDocumentosEarXDocumentoDuaId($documentoId);
    }
    
    public function relacionarDuaEar(){        
        $this->setTransaction();
        $documentoDuaId = $this->getParametro("documentoDuaId");
        $earSeleccionados = $this->getParametro("earSeleccionados");
        $usuarioId=$this->getUsuarioId();
        
        return MovimientoDuaNegocio::create()->relacionarDuaEar($documentoDuaId,$earSeleccionados,$usuarioId);
    }
        
    public function enviar() {
        $resDocumento = parent::enviar();
        
        $duaId=$resDocumento->documentoId;
        $dataEar=MovimientoDuaNegocio::create()->obtenerDocumentosEarXDocumentoDuaId($duaId);
        
        $respuesta = new stdClass();
        if(ObjectUtil::isEmpty($dataEar->documentoEar)){
            $respuesta=$resDocumento;
        }else{
            $respuesta=$dataEar;
            $respuesta->documentoId=$duaId;
        }
        
        return $respuesta;
    }
}
