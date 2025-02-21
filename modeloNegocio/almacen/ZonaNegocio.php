<?php

require_once __DIR__ . '/../../modelo/almacen/Zona.php';
require_once __DIR__ . '/../../modelo/almacen/Persona.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/PersonaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/commons/TablaNegocio.php';

class ZonaNegocio extends ModeloNegocioBase {

    /**
     * 
     * @return ZonaNegocio
     */
    static function create() {
        return parent::create();
    }


    public function listarZona() {
        $respuesta = new ObjectUtil();
        $respuesta->zona=Zona::create()->getAllZonas();
        $respuesta->dataUbigeo = Persona::create()->obtenerUbigeoActivos();
        return $respuesta;
    }

    public function guardarZona($id,$nombre,$codigo, $estado, $usuarioId) {

    
        $respuestaGuardar = Zona::create()->guardarZona( $id, $nombre,$codigo, $estado,  $usuarioId);



        return $respuestaGuardar;
    }





    public function actualizarEstadoZona($id,  $nombre,$codigo, $estado) {
        return Zona::create()->actualizarEstadoZona($id,  $nombre,$codigo, $estado);
    }











    public function listarAgenciaActiva($empresaId) {
        return Agencia::create()->listarAgenciaActiva($empresaId);
    }



    public function obterConfiguracionInicialForm($id) {
       
        $data->dataZona = Zona::create()->listarZonasXId($id);
        
        return $data;
    }
 
    public function actualizarBotonEstadoZona($id, $estado) {
        return Zona::create()->actualizarBotonEstadoZona($id, $estado);
    }


}
