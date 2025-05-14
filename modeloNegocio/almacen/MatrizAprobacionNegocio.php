<?php
session_start();

require_once __DIR__ . '/../../modelo/almacen/Persona.php';
require_once __DIR__ . '/../../modelo/almacen/MatrizAprobacion.php';
require_once __DIR__ . '/../../modelo/almacen/Invitacion.php';
require_once __DIR__ . '/../../modelo/almacen/ActaRetiro.php';
require_once __DIR__ . '/../../modelo/almacen/Vehiculo.php';
require_once __DIR__ . '/../../modelo/almacen/Zona.php';
require_once __DIR__ . '/../../modelo/almacen/Documento.php';
require_once __DIR__ . '/../../modelo/itec/Usuario.php';
require_once __DIR__ . '/../../modelo/almacen/ConsultaWs.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/commons/ConstantesNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/SunatTablaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/CentroCostoNegocio.php';

class MatrizAprobacionNegocio extends ModeloNegocioBase
{

  /**
   *
   * @return MatrizAprobacionNegocio
   */
  static function create()
  {
    return parent::create();
  }



  public function getAllMatriz( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$documento,$usuarioAprobador,$planta,$zona)
  {
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
   
    return MatrizAprobacion::create()->getAllMatriz($columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$documento,$usuarioAprobador,$planta,$zona);
  }

  public function getCantidadAllMatriz( $elemntosFiltrados, $columns, $order, $start, $usuarioId,$documento,$usuarioAprobador,$planta,$zona )
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return MatrizAprobacion::create()->getCantidadAllMatriz($columnaOrdenar, $formaOrdenar, $usuarioId,$documento,$usuarioAprobador,$planta,$zona);
  }

  public function datosInicialesModal($idMatriz)
  {
    $respuesta = new ObjectUtil();
    //$respuesta->empresa = EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    
    //        $respuesta->persona_clase = Persona::create()->getAllPersonaClaseByTipo($personaTipoId);

    //contactos
    $respuesta->matriz = ($idMatriz > 0) ? MatrizAprobacion::create()->getMatrizXId($idMatriz ) : null;
    $respuesta->zonas = Zona::create()->getAllZonas();
    $respuesta->plantas = Persona::create()->obtenerPersonasXClase(25);
    $respuesta->usuarios=Usuario::create()->getDataUsuarioPersona();
    $respuesta->documentos=Documento::create()->obtenerTiposDocumentoXMatriz();
    return $respuesta;
  }

  public function guardarAprobador($documento,$usuario,$zona,$planta,$file ,$nivel,$usuarioId,$comentario)
  {
    if($documento==null){
        throw new WarningException("Seleccione un documento");  
      }

      if($usuario==null){
        throw new WarningException("Seleccione un usuario aprobador");  
      }
      if($zona==0){
        $zona=null;
      }
      if($planta==0){
        $planta=null;
      }

      list($type, $imageData) = explode(';', $file);
      list(, $imageData) = explode(',', $imageData);
      list(, $tipo) = explode('/', $type);
      $extension=$tipo;
      // Decodificar los datos base64
      $imageData = base64_decode($imageData);
  
      // Crear un nombre único para la imagen
      $imageName = uniqid() . '.'.$extension;
  
      // Especificar la ruta donde se guardará la imagen
      $imagePath = '../../vistas/com/persona/firmas/' . $imageName;
  
      // Guardar la imagen en el servidor
      file_put_contents($imagePath, $imageData);

      $persona=Persona::create()->obtenerPersonaXUsuarioId($usuario);
      if($persona==null){
        throw new WarningException("Usuario no tiene asignada persona"); 
      }
      $personaId=$persona[0]['id'];
      $persona=Persona::create()->actualizarFirmaDigital($personaId,$imageName);
      
      if($persona[0]['vout_exito']!='1'){
        if($imageName!=null){
            unlink($imagePath);
        }
        throw new WarningException("No se pudo registrar la firma"); 
      }
 
      $response = MatrizAprobacion::create()->guardarAprobador( $documento,$usuario,$zona,$planta,$file ,$nivel,$usuarioId,$comentario);
      if($response[0]['vout_exito']==0){
        throw new WarningException($response[0]['vout_mensaje']); 
      }
      return $response;
    
  }

  public function actualizarAprobador($documento,$usuario,$zona,$planta,$file ,$nivel,$usuarioId,$comentario,$id)
  {
    if($documento==null){
        throw new WarningException("Seleccione un documento");  
      }

      if($usuario==null){
        throw new WarningException("Seleccione un usuario aprobador");  
      }
      if($zona==0){
        $zona=null;
      }
      if($planta==0){
        $planta=null;
      }

      list($type, $imageData) = explode(';', $file);
      list(, $imageData) = explode(',', $imageData);
      list(, $tipo) = explode('/', $type);
      $extension=$tipo;
      // Decodificar los datos base64
      $imageData = base64_decode($imageData);
  
      // Crear un nombre único para la imagen
      $imageName = uniqid() . '.'.$extension;
  
      // Especificar la ruta donde se guardará la imagen
      $imagePath = '../../vistas/com/persona/firmas/' . $imageName;
  
      // Guardar la imagen en el servidor
      file_put_contents($imagePath, $imageData);

      $persona=Persona::create()->obtenerPersonaXUsuarioId($usuario);
      if($persona==null){
        throw new WarningException("Usuario no tiene asignada persona"); 
      }
      $personaId=$persona[0]['id'];
      if($persona[0]['firma_digital']!=null){
        $personaFirma='../../vistas/com/persona/firmas/' . $persona[0]['firma_digital'];
        unlink($personaFirma);
      }
      $persona=Persona::create()->actualizarFirmaDigital($personaId,$imageName);
      
      if($persona[0]['vout_exito']!='1'){
        if($imageName!=null){
            unlink($imagePath);
        }
        throw new WarningException("No se pudo registrar la firma"); 
      }
 
      $response = MatrizAprobacion::create()->actualizarAprobador( $documento,$usuario,$zona,$planta,$file ,$nivel,$usuarioId,$comentario,$id);
      if($response[0]['vout_exito']==0){
        throw new WarningException($response[0]['vout_mensaje']); 
      }
      return $response;
    
  }

  public function deleteElementoMatriz($id, $usuarioSesion, $estado){

  $response = MatrizAprobacion::create()->deleteElementoMatriz( $id, $usuarioSesion, $estado);
      if($response[0]['vout_exito']==0){
        throw new WarningException($response[0]['vout_mensaje']); 
      }
      return $response;

  }

  public function obtenerMatrizXDocumentoTipoXArea($documentoTipoId, $areaId = null){
    return MatrizAprobacion::create()->obtenerMatrizXDocumentoTipoXArea($documentoTipoId, $areaId);
  }

   
  public function obtenerMatrizXDocumentoTipoUrgente($documentoTipoId, $estado_negocioid){
    return MatrizAprobacion::create()->obtenerMatrizXDocumentoTipoUrgente($documentoTipoId, $estado_negocioid);
  }

  public function obtenerMatrizXRequerimientoServicio($documentoTipoId){
    return MatrizAprobacion::create()->obtenerMatrizXRequerimientoServicio($documentoTipoId);
  }
}
