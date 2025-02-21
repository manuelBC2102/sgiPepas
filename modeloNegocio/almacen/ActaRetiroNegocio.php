<?php
session_start();

require_once __DIR__ . '/../../modelo/almacen/Persona.php';

require_once __DIR__ . '/../../modelo/almacen/ActaRetiro.php';
require_once __DIR__ . '/../../modelo/almacen/Vehiculo.php';
require_once __DIR__ . '/../../modelo/almacen/Zona.php';
require_once __DIR__ . '/../../modelo/almacen/ConsultaWs.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/commons/ConstantesNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/SunatTablaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/CentroCostoNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/SolicitudRetiroNegocio.php';
class ActaRetiroNegocio extends ModeloNegocioBase
{

  /**
   *
   * @return ActaRetiroNegocio
   */
  static function create()
  {
    return parent::create();
  }


  public function obtenerPesajeSuminco($variable){
    // Define tus credenciales
$usuario = 'pepas'; // Reemplaza con tu nombre de usuario
$password = 'P3p4sd30r0';  // Reemplaza con tu contraseña

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://www.wspesaje.com/Pepasdeoro/hbmpesaje.php?codigo=1',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET', // Cambia a 'GET' si es necesario
    CURLOPT_POSTFIELDS => http_build_query(array(
        'param_opcion_id' => '8',
        'action_name' => 'aceptarTerminosYCondiciones',
        'param_sid' => '{{param_sid}}',
        'usuario_token' => '{{usuario_token}}'
    )),
    CURLOPT_HTTPHEADER => array(
        'Authorization: Basic ' . base64_encode("$usuario:$password"),
    ),
));

$response = curl_exec($curl);

    
    if ($response === false) {
        echo 'Error en cURL: ' . curl_error($curl);
        return null; 
    }
    curl_close($curl);
    $data = json_decode($response, true); 
   
    if (!empty($data)) {

      $respuesta = new ObjectUtil();
      $respuesta->pesaje = $data[0]['peso'];
      $respuesta->variable=$variable;

        return $respuesta; // Devuelve el pesaje del primer elemento
    }

    return null; // Devuelve null si no hay datos

  }


  public function getAllActasRetiro( $elemntosFiltrados, $columns, $order, $start, $usuarioId,$idPersona,$fecha,$usuario,$vehiculo )
  {
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
    // $obtenerZonaxUsuario=Usuario::create()->obtenerZonaXUsuarioId($usuarioId);
    
    // if ($obtenerZonaxUsuario == null) {
    //   $zona_id=null;
      
    // }
    // else{
    //   $zona_id=$obtenerZonaxUsuario[0]['zona_id'];
    // }
    return ActaRetiro::create()->getAllActasRetiro($columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona,null,$fecha,$usuario,$vehiculo);
  }
  public function getAllRetenciones( $elemntosFiltrados, $columns, $order, $start, $usuarioId,$idPersona,$fecha,$factura,$proveedor )
  {
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
   
    return ActaRetiro::create()->getAllRetenciones($columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona,null,$fecha,$factura,$proveedor);
  }

  public function getAllActasRetiroPlanta( $elemntosFiltrados, $columns, $order, $start, 
  $usuarioId,$idPersona,$fecha,$usuario,$vehiculo,$solicitud )
  {
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
    // $obtenerPlantaxUsuario=Usuario::create()->obtenerPlantaXUsuarioId($usuarioId);
    $documento = 276;
    $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
    $planta_id = [];
    if ($matrizUsuario == null) {
      throw new WarningException("Usuario no tiene zonas asignadas.");
  }

  foreach ($matrizUsuario as $item) {
      $nivel = $item['nivel'];

         if ($nivel == 2) {
          $plantaId = $item['persona_planta_id'];
          if ($plantaId !== null) {
            $planta_id[] = $plantaId;
          }
      } 
  }
       $planta_id = implode(',', $planta_id);
    // if ($obtenerPlantaxUsuario == null) {
    //   $planta_id=null;
      
    // }
    // else{
    //   $planta_id=$obtenerPlantaxUsuario[0]['id'];
    // }
    return ActaRetiro::create()->getAllActasRetiroPlanta($columnaOrdenar, $formaOrdenar, $elemntosFiltrados,
     $start, $usuarioId,$idPersona,$planta_id,$fecha,$usuario,$vehiculo,$solicitud);
  }
  
  public function obtenerZonas( $usuarioId){
    $documento = 276;
    $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
    $invitaciones = [];

    if ($matrizUsuario == null) {
        throw new WarningException("Usuario no tiene zonas asignadas.");
    }

    foreach ($matrizUsuario as $item) {
        $nivel = $item['nivel'];

           if ($nivel == 3) {
            $zonaId = $item['zona_id'];
            $result = zona::create()->listarZonasXId($zonaId);
            if ($result !== null) {
                $invitaciones = array_merge($invitaciones, $result);
            }
        } 
    }

    return $invitaciones;
  }
  public function obtenerDataPlaca(  $usuarioId,$placa,$zona )
  {
     
    $dataPlacas=ActaRetiro::create()->obtenerDataPlaca($placa,$zona);
    if ($dataPlacas == null) {
      throw new WarningException("Esta placa no tiene solicitudes asignadas");  
    }
    return $dataPlacas;
  }

  

  public function getCantidadAllActasRetiro( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$idPersona,$fecha,$usuario,$vehiculo)
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
    // $obtenerZonaxUsuario=Usuario::create()->obtenerZonaXUsuarioId($usuarioId);
    // $obtenerZonaxUsuario=Usuario::create()->obtenerZonaXUsuarioId($usuarioId);
    
    // if ($obtenerZonaxUsuario == null) {
    //   $zona_id=null;
      
    // }
    // else{
    //   $zona_id=$obtenerZonaxUsuario[0]['zona_id'];
    // }

    return ActaRetiro::create()->getCantidadAllActasRetiro($columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,null,$fecha,$usuario,$vehiculo);
  }

  public function getCantidadAllRetenciones( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$idPersona,$fecha,$factura,$proveedor)
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
    return ActaRetiro::create()->getCantidadAllRetenciones($columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,null,$fecha,$factura,$proveedor);
  }

  
  public function getCantidadAllActasRetiroPlanta( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$idPersona,$fecha,$usuario,$vehiculo,$solicitud)
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
    // $obtenerPlantaxUsuario=Usuario::create()->obtenerPlantaXUsuarioId($usuarioId);
    $documento = 276;
    $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
    $planta_id = [];
    if ($matrizUsuario == null) {
      throw new WarningException("Usuario no tiene zonas asignadas.");
  }

  foreach ($matrizUsuario as $item) {
      $nivel = $item['nivel'];

         if ($nivel == 2) {
          $plantaId = $item['persona_planta_id'];
          if ($plantaId !== null) {
            $planta_id[] = $plantaId;
          }
      } 
  }
       $planta_id = implode(',', $planta_id);
    // if ($obtenerPlantaxUsuario == null) {
    //   $planta_id=null;
      
    // }
    // else{
    //   $planta_id=$obtenerPlantaxUsuario[0]['zona_id'];
    // }

    return ActaRetiro::create()->getCantidadAllActasRetiroPlanta($columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,$planta_id,$fecha,$usuario,$vehiculo,$solicitud);
  }
 
  public function guardarActaRetiroTotal($placa,$file,$items,$comentario,$usuarioId,$pesaje,$zona,$pesajeFinal,$fechaInicio,$fechaFinal,$carreta )
  {
    try {

      if($items==null){
        throw new WarningException("Esta acta no tiene solicitudes asignadas");  
      }
      

      list($type, $imageData) = explode(';', $file);
      list(, $imageData) = explode(',', $imageData);
  
      // Decodificar los datos base64
      $imageData = base64_decode($imageData);
  
      // Crear un nombre único para la imagen
      $imageName = uniqid() . '.png';
  
      // Especificar la ruta donde se guardará la imagen
      $imagePath = '../../vistas/com/actaRetiro/imagenes/' . $imageName;
  
      // Guardar la imagen en el servidor
      file_put_contents($imagePath, $imageData);
      
      
      $vehiculo = Vehiculo::create()->getVehiculoXPlaca($placa);
      
      if(!ObjectUtil::isEmpty($vehiculo)){
        $vehiculoId=$vehiculo[0]['id'];
      }

      else{
        throw new WarningException("No existe la placa");
      }

      
     if($pesaje==null){

      if($carreta==null){
      $pesaje = $vehiculo[0]['capacidad']/1000; 
    }
      else {
        $carreta = Vehiculo::create()->listarvehiculosXId($carreta);
        $pesaje = $carreta[0]['capacidad']/1000; 
      
      }

    }

    else{
      $pesajeInicial=$pesaje;
      $pesaje = ($pesajeFinal-$pesaje)/1000;
    } 
    
    //FACTURADOR EMISOR
    $personaMinero=Persona::create()->obtenerPersonaGetByIdAll(3164);
    $facturadorId=$personaMinero[0]['id'];
    $facturadorCodigo=$personaMinero[0]['codigo_identificacion'];
    $facturadorRazon=$personaMinero[0]['nombre'];
    $facturadorEmail=$personaMinero[0]['email'];
    $facturadorSerie=$personaMinero[0]['serie_guia'];
    $facturadorCorrelativo=$personaMinero[0]['correlativo_guia'];
    $personaMineroDireccion=Persona::create()->obtenerDireccionXPersonaId($facturadorId);
    

    // Obtén la información de la solicitud
    // $solicitudArray = json_decode($items, true); 
    foreach ($items as $solicitud) {
      
      $solicitudInfo = SolicitudRetiro::create()->getSolicituRetiroXId($solicitud);
      
      $zonaId=$solicitudInfo[0]['zona_id'];
      $transportistaId=$solicitudInfo[0]['persona_transportista_id'];
      $plantaId=$solicitudInfo[0]['persona_planta_id'];
      $constancia=$solicitudInfo[0]['constancia'];
      $conductorId=$solicitudInfo[0]['persona_conductor_id'];
  }

    //RECEPTOR
    $personaPlanta=Persona::create()->obtenerPersonaGetByIdAll($plantaId);
    $plantaCodigo=$personaPlanta[0]['codigo_identificacion'];
    $plantaRazon=$personaPlanta[0]['nombre'];
    
    $personaPlantaDireccion=Persona::create()->obtenerDireccionXPersonaId($plantaId);

    //TRANSPORTISTA
    $personaTransportista=Persona::create()->obtenerPersonaGetByIdAll($transportistaId);
    $transportistaCodigo=$personaTransportista[0]['codigo_identificacion'];
    $transportistaRazon=$personaTransportista[0]['nombre'];
    $transportistaMTC=$personaTransportista[0]['registro_mtc'];
    $transportistaSerie=$personaTransportista[0]['serie_transportista'];
    $transportistaCorrelativo=$personaTransportista[0]['correlativo_transportista'];

    $personaTransportistaDireccion=Persona::create()->obtenerDireccionXPersonaId($transportistaId);

    //CONDUCTOR 
    $personaConductor=Persona::create()->obtenerPersonaGetByIdAll($conductorId);

    //DIRECCION PARTIDA
    $zonaInfo = Zona::create()->listarZonasXId($zonaId);

    //DIRECCION LLEGADA
    $direccionLlegada=Persona::create()->obtenerDireccionXPersonaId($plantaId,1);

    $incrementado = (int)$facturadorCorrelativo + 1;
   $correlativoNuevo = sprintf('%06d', $incrementado);

   $incrementadoTransportista = (int)$transportistaCorrelativo + 1;
   $correlativoNuevoTransportista = sprintf('%06d', $incrementadoTransportista);
   
   $fecha = date('Y-m-d'); // Fecha en formato YYYY-MM-DD
   $hora = date('H:i:s');


     //efact consumir guia remision
     $token= SolicitudRetiroNegocio::create()->generarTokenEfact('20600739256','e24243d460d9d29bddcafdef34c7f4cf853e719d5e217984d2149150d52397e2');
     $token = $token->access_token;

     $jsonGRR = SolicitudRetiroNegocio::create()->generarJsonGuiaRemisionEfact($fecha,$hora,$facturadorSerie,$facturadorCorrelativo,$facturadorCodigo,
     $facturadorRazon,$facturadorEmail,$personaMineroDireccion,$plantaCodigo,$plantaRazon,$personaPlantaDireccion,$transportistaCodigo,$transportistaRazon,
     $transportistaMTC,$zonaInfo,$direccionLlegada,$pesaje);


    $facturarDocumento=SolicitudRetiroNegocio::create()->enviarEfactDocumentoElectronico($token,$jsonGRR);
    $codeFactura=$facturarDocumento->code;
    $facturarDocumento=$facturarDocumento->description;
    
    
    if($codeFactura=='0'){
    $comentarioEfact=SolicitudRetiroNegocio::create()->consultarDocumentoEfact($token,$facturarDocumento);
    $comentarioEfact=$comentarioEfact->description;
    }
    else {
      $comentarioEfact=$facturarDocumento;
      $facturarDocumento='';  
    }
    $facturador=ActaRetiro::create()->actualizarCorrelativoRemitente($facturadorId,$correlativoNuevo);


    // efact consumir guia transportista
     $jsonGRT = SolicitudRetiroNegocio::create()->generarJsonGuiaTransportistaEfact($fecha,$hora,$transportistaSerie,$transportistaCorrelativo,$transportistaCodigo,
     $transportistaRazon,$facturadorEmail,$personaTransportistaDireccion,$plantaCodigo,$plantaRazon,$personaPlantaDireccion,$facturadorCodigo,$facturadorRazon,
     $transportistaMTC,$zonaInfo,$direccionLlegada,$pesaje,$personaMineroDireccion,$placa,$constancia,$carreta[0]['placa'],$carreta[0]['nro_contancia'],$personaConductor,
    $facturadorSerie.'-'.$facturadorCorrelativo);


    $facturarDocumentoTransportista=SolicitudRetiroNegocio::create()->enviarEfactDocumentoElectronico($token,$jsonGRT);
    $codeFacturaTransportista=$facturarDocumentoTransportista->code;
    $facturarDocumentoTransportista=$facturarDocumentoTransportista->description;
    
    
    if($codeFacturaTransportista=='0'){
    $comentarioEfactTransportista=SolicitudRetiroNegocio::create()->consultarDocumentoEfact($token,$facturarDocumentoTransportista);
    $comentarioEfactTransportista=$comentarioEfactTransportista->description;
    }
    else {
      $comentarioEfactTransportista=$facturarDocumentoTransportista;
      $facturarDocumentoTransportista='';  
    }

    $facturador=ActaRetiro::create()->actualizarCorrelativoTransportista($transportistaId,$correlativoNuevoTransportista);

      $registroActa = ActaRetiro::create()->guardarActaRetiro($imageName,$vehiculoId,$comentario,$usuarioId,$zona,$pesaje,
      $facturarDocumento,$comentarioEfact, $pesajeInicial,$pesajeFinal,$fechaInicio,$fechaFinal,$carreta,
      $facturarDocumentoTransportista,$comentarioEfactTransportista,
      $facturadorSerie,$facturadorCorrelativo,$transportistaSerie,$transportistaCorrelativo
       );
      if ($registroActa[0]['vout_exito'] == 0) {
        throw new WarningException("Error al guardar la acta de retiro");
      }
      else{
        $actaId=$registroActa[0]['id'];
        $registroTicket = ActaRetiro::create()->guardarTicket($imageName,$vehiculoId,$usuarioId,$pesaje,
            $pesajeInicial,$pesajeFinal,$fechaInicio,$fechaFinal,$carreta,$actaId);
        foreach ($items as $id) { 

          $res = ActaRetiro::create()->guardarActaXSolicitudRetiro($actaId,$id,$usuarioId);
          if ($res[0]['vout_exito'] == 0) {
            throw new WarningException("Error al guardar la solicitud");
          }
          else{
            SolicitudRetiro::create()->guardarEstadoSolicitud($id,14,$usuarioId);
            ActaRetiro::create()->actualizarEstadoSolicitud($id,14);
          }
        }
      }
     
    return $registroActa;

  } catch (Exception $e) {

    throw new WarningException("Error al guardar. " . $e->getMessage());

  }  
  }

  public function guardarActaRetiroInicial($placa,$file,$items,$usuarioId,$pesaje,$zona,$fechaInicio,$carreta )
  {
    try {

      if($items==null){
        throw new WarningException("Esta acta no tiene solicitudes asignadas");  
      }
      

      list($type, $imageData) = explode(';', $file);
      list(, $imageData) = explode(',', $imageData);
  
      // Decodificar los datos base64
      $imageData = base64_decode($imageData);
  
      // Crear un nombre único para la imagen
      $imageName = uniqid() . '.png';
  
      // Especificar la ruta donde se guardará la imagen
      $imagePath = '../../vistas/com/actaRetiro/imagenes/' . $imageName;
  
      // Guardar la imagen en el servidor
      file_put_contents($imagePath, $imageData);
      
      
      $vehiculo = Vehiculo::create()->getVehiculoXPlaca($placa);
      
      if(!ObjectUtil::isEmpty($vehiculo)){
        $vehiculoId=$vehiculo[0]['id'];
      }

      else{
        throw new WarningException("No existe la placa");
      }

    
      $registroActa = ActaRetiro::create()->guardarActaRetiroInicial($imageName,$vehiculoId,$usuarioId,$zona,
      $pesaje,$fechaInicio,$carreta);
      if ($registroActa[0]['vout_exito'] == 0) {
        throw new WarningException("Error al guardar la acta de retiro");
      }
      else{
        $actaId=$registroActa[0]['id'];
        
        foreach ($items as $id) { 

          $res = ActaRetiro::create()->guardarActaXSolicitudRetiro($actaId,$id,$usuarioId);
          if ($res[0]['vout_exito'] == 0) {
            throw new WarningException("Error al guardar la solicitud");
          }
          else{
            SolicitudRetiro::create()->guardarEstadoSolicitud($id,14,$usuarioId);
            ActaRetiro::create()->actualizarEstadoSolicitud($id,14);
          }
        }
      }
     
    return $registroActa;

  } catch (Exception $e) {

    throw new WarningException("Error al guardar. " . $e->getMessage());

  }  
  }
  public function guardarActaRetiro($placa,$file,
  $usuarioId ,$pesaje,$zona,$pesajeFinal,$fechaInicio,$fechaFinal,$carreta ,$items,$acta)
  {
    try {

      $pesajeFinal=$pesajeFinal*1.18;
      
      $comentario='';
      list($type, $imageData) = explode(';', $file);
      list(, $imageData) = explode(',', $imageData);
  
      // Decodificar los datos base64
      $imageData = base64_decode($imageData);
  
      // Crear un nombre único para la imagen
      $imageName = uniqid() . '.png';
  
      // Especificar la ruta donde se guardará la imagen
      $imagePath = '../../vistas/com/actaRetiro/imagenes/' . $imageName;
  
      // Guardar la imagen en el servidor
      file_put_contents($imagePath, $imageData);
      
      
      $vehiculo = Vehiculo::create()->getVehiculoXPlaca($placa);
      
      if(!ObjectUtil::isEmpty($vehiculo)){
        $vehiculoId=$vehiculo[0]['id'];
      }

      else{
        throw new WarningException("No existe la placa");
      }

      
     if($pesaje==null){

      if($carreta==null){
      $pesaje = $vehiculo[0]['capacidad']/1000; 
    }
      else {
        $carreta = Vehiculo::create()->listarvehiculosXId($carreta);
        $pesaje = $carreta[0]['capacidad']/1000; 
      
      }

    }

    else{
      $pesajeInicial=$pesaje;
      $pesaje = ($pesajeFinal-$pesaje)/1000;
    } 
    
    //FACTURADOR EMISOR
    $personaMinero=Persona::create()->obtenerPersonaGetByIdAll(3164);
    $facturadorId=$personaMinero[0]['id'];
    $facturadorCodigo=$personaMinero[0]['codigo_identificacion'];
    $facturadorRazon=$personaMinero[0]['nombre'];
    $facturadorEmail=$personaMinero[0]['email'];
    $facturadorSerie=$personaMinero[0]['serie_guia'];
    $facturadorCorrelativo=$personaMinero[0]['correlativo_guia'];
    $personaMineroDireccion=Persona::create()->obtenerDireccionXPersonaId($facturadorId);
    

    // Obtén la información de la solicitud
    // $solicitudArray = json_decode($items, true); 
       $solicitudRetiro=ActaRetiro::create()->obtenerActaRetiroXId($acta);
      
      $solicitudInfo = SolicitudRetiro::create()->getSolicituRetiroXId($solicitudRetiro['0']['solicitud_id']);
      
      $zonaId=$solicitudInfo[0]['zona_id'];
      $transportistaId=$solicitudInfo[0]['persona_transportista_id'];
      $plantaId=$solicitudInfo[0]['persona_planta_id'];
      $constancia=$solicitudInfo[0]['constancia'];
      $conductorId=$solicitudInfo[0]['persona_conductor_id'];
  

    //RECEPTOR
    $personaPlanta=Persona::create()->obtenerPersonaGetByIdAll($plantaId);
    $plantaCodigo=$personaPlanta[0]['codigo_identificacion'];
    $plantaRazon=$personaPlanta[0]['nombre'];
    
    $personaPlantaDireccion=Persona::create()->obtenerDireccionXPersonaId($plantaId);

    //TRANSPORTISTA
    $personaTransportista=Persona::create()->obtenerPersonaGetByIdAll($transportistaId);
    $transportistaCodigo=$personaTransportista[0]['codigo_identificacion'];
    $transportistaRazon=$personaTransportista[0]['nombre'];
    $transportistaMTC=$personaTransportista[0]['registro_mtc'];
    $transportistaSerie=$personaTransportista[0]['serie_transportista'];
    $transportistaCorrelativo=$personaTransportista[0]['correlativo_transportista'];

    $personaTransportistaDireccion=Persona::create()->obtenerDireccionXPersonaId($transportistaId);

    //CONDUCTOR 
    $personaConductor=Persona::create()->obtenerPersonaGetByIdAll($conductorId);

    //DIRECCION PARTIDA
    $zonaInfo = Zona::create()->listarZonasXId($zonaId);

    //DIRECCION LLEGADA
    $direccionLlegada=Persona::create()->obtenerDireccionXPersonaId($plantaId,1);

    $incrementado = (int)$facturadorCorrelativo + 1;
   $correlativoNuevo = sprintf('%06d', $incrementado);

   $incrementadoTransportista = (int)$transportistaCorrelativo + 1;
   $correlativoNuevoTransportista = sprintf('%06d', $incrementadoTransportista);
   
   $fecha = date('Y-m-d'); // Fecha en formato YYYY-MM-DD
   $hora = date('H:i:s');


     //efact consumir guia remision
     $token= SolicitudRetiroNegocio::create()->generarTokenEfact('20600739256','e24243d460d9d29bddcafdef34c7f4cf853e719d5e217984d2149150d52397e2');
     $token = $token->access_token;

     $jsonGRR = SolicitudRetiroNegocio::create()->generarJsonGuiaRemisionEfact($fecha,$hora,$facturadorSerie,$facturadorCorrelativo,$facturadorCodigo,
     $facturadorRazon,$facturadorEmail,$personaMineroDireccion,$plantaCodigo,$plantaRazon,$personaPlantaDireccion,$transportistaCodigo,$transportistaRazon,
     $transportistaMTC,$zonaInfo,$direccionLlegada,$pesaje);


    $facturarDocumento=SolicitudRetiroNegocio::create()->enviarEfactDocumentoElectronico($token,$jsonGRR);
    $codeFactura=$facturarDocumento->code;
    $facturarDocumento=$facturarDocumento->description;
    
    
    if($codeFactura=='0'){
    $comentarioEfact=SolicitudRetiroNegocio::create()->consultarDocumentoEfact($token,$facturarDocumento);
    $comentarioEfact=$comentarioEfact->description;
    }
    else {
      $comentarioEfact=$facturarDocumento;
      $facturarDocumento='';  
    }
    $facturador=ActaRetiro::create()->actualizarCorrelativoRemitente($facturadorId,$correlativoNuevo);


    // efact consumir guia transportista
     $jsonGRT = SolicitudRetiroNegocio::create()->generarJsonGuiaTransportistaEfact($fecha,$hora,$transportistaSerie,$transportistaCorrelativo,$transportistaCodigo,
     $transportistaRazon,$facturadorEmail,$personaTransportistaDireccion,$plantaCodigo,$plantaRazon,$personaPlantaDireccion,$facturadorCodigo,$facturadorRazon,
     $transportistaMTC,$zonaInfo,$direccionLlegada,$pesaje,$personaMineroDireccion,$placa,$constancia,$carreta[0]['placa'],$carreta[0]['nro_contancia'],$personaConductor,
    $facturadorSerie.'-'.$facturadorCorrelativo);


    $facturarDocumentoTransportista=SolicitudRetiroNegocio::create()->enviarEfactDocumentoElectronico($token,$jsonGRT);
    $codeFacturaTransportista=$facturarDocumentoTransportista->code;
    $facturarDocumentoTransportista=$facturarDocumentoTransportista->description;
    
    
    if($codeFacturaTransportista=='0'){
    $comentarioEfactTransportista=SolicitudRetiroNegocio::create()->consultarDocumentoEfact($token,$facturarDocumentoTransportista);
    $comentarioEfactTransportista=$comentarioEfactTransportista->description;
    }
    else {
      $comentarioEfactTransportista=$facturarDocumentoTransportista;
      $facturarDocumentoTransportista='';  
    }

    $facturador=ActaRetiro::create()->actualizarCorrelativoTransportista($transportistaId,$correlativoNuevoTransportista);

      $registroActa = ActaRetiro::create()->updateActaRetiro($imageName,$vehiculoId,$comentario,$usuarioId,$zona,$pesaje,
      $facturarDocumento,$comentarioEfact, $pesajeInicial,$pesajeFinal,$fechaInicio,$fechaFinal,$carreta,
      $facturarDocumentoTransportista,$comentarioEfactTransportista,
      $facturadorSerie,$facturadorCorrelativo,$transportistaSerie,$transportistaCorrelativo,$acta
       );
      if ($registroActa[0]['vout_exito'] == 0) {
        throw new WarningException("Error al guardar la acta de retiro");
      }
      else{
        $actaId=$registroActa[0]['id'];
        $registroTicket = ActaRetiro::create()->guardarTicket($imageName,$vehiculoId,$usuarioId,$pesaje,
            $pesajeInicial,$pesajeFinal,$fechaInicio,$fechaFinal,$carreta,$actaId);
        
      }
     
    return $registroActa;

  } catch (Exception $e) {

    throw new WarningException("Error al guardar. " . $e->getMessage());

  }  
  }
  
  public function updateSolicitud($id,$fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId )
  {
    try {
      

      // fin direccion tipo

      $res = SolicitudRetiro::create()->actualizarSolicitud($id,$fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId);

      if ($res[0]['vout_exito'] == 0) {
        throw new WarningException("Error al actuaizar la solicitud");
      }

    
    return $res;

  } catch (Exception $e) {

    throw new WarningException("Error al guardar. " . $e->getMessage());

  }  
  }

  public function obtenerConfiguracionesSolicitudRetiro( $usuarioId,$solicitudId)
  {
    $respuesta = new ObjectUtil();
    //$respuesta->empresa = EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    
    //        $respuesta->persona_clase = Persona::create()->getAllPersonaClaseByTipo($personaTipoId);

    //contactos
    $respuesta->solicitud = ($solicitudId > 0) ? SolicitudRetiro::create()->getSolicituRetiroXId($solicitudId ) : null;
    $respuesta->vehiculos = Vehiculo::create()->getAllVehiculos(); // 2-> natural
    $respuesta->transportistas = Persona::create()->obtenerPersonasXClase(23);
    $respuesta->conductores = Persona::create()->obtenerPersonasXClase(22);
    $respuesta->plantas = Persona::create()->obtenerPersonasXClase(25);
    $respuesta->zonas = Zona::create()->getAllZonas();
    return $respuesta;
  }

  public function cambiarEstadoSolicitud($id, $usuarioSesion, $estado)
  {
    try {
      $this->beginTransaction();

      $solicitudesRetiro=ActaRetiro::create()->obtenerSolicitudesXActaId($id);
      $actaRetiroDatos=ActaRetiro::create()->obtenerActaRetiroXId($id);
        $res = ActaRetiro::create()->eliminarActaRetiro($id);
        if ($res[0]['vout_exito'] == 0) {
          throw new WarningException("Error al eliminar el acta retiro");
        }
        else{
        $file=$actaRetiroDatos[0]['archivo'];
        $ruta='../../vistas/com/actaRetiro/imagenes/'.$file;
        unlink($ruta);
        $res = ActaRetiro::create()->cambiarEstadoSolicitudDetalle($id);

        

        foreach ($solicitudesRetiro as $dato) {
          $id = $dato['solicitud_retiro_id'];
      
          ActaRetiro::create()->actualizarEstadoSolicitud($id,$estado=13);
          ActaRetiro::create()->actualizarXUltimoEstadoSolicitud($id);
        }

        $this->commitTransaction();
      return $res;
      }
    } catch (Exception $e) {
      $this->rollbackTransaction();
      throw new WarningException("Error al guardar. " . $e->getMessage());
  
    }  
  }

  public function buscarSolicitudXSociedadXVehiculo($busqueda, $usuarioId )
  {
    return SolicitudRetiro::create()->buscarSolicitudXSociedadXVehiculo($busqueda, $usuarioId);
  }


  public function obtenerDataActa(  $usuarioId,$acta )
  {

    $dataActas=ActaRetiro::create()->obtenerDataActa($acta);
    if ($dataActas == null) {
      throw new WarningException("Esta acta no tiene solicitudes asignadas");  
    }
    return $dataActas;
  }

  public function obtenerDataActaSolicitud(  $usuarioId,$solicitud )
  {

    $dataActas=ActaRetiro::create()->obtenerDataActaSolicitud($solicitud);
    if ($dataActas == null) {
      throw new WarningException("Esta solicitud no tiene pesos asignados");  
    }
    return $dataActas;
  }

  
 
  public function  guardarLotes($usuarioId,$solicitudId,$ticket1,$ticket2,
        $peso_bruto,$peso_tara,$peso_neto,$nombre_lote,$archivo_lote){

          list($type, $imageData) = explode(';', $archivo_lote);
          list(, $imageData) = explode(',', $imageData);
          list(, $tipo) = explode('/', $type);
          $extension=$tipo;
          // Decodificar los datos base64
          $imageData = base64_decode($imageData);
      
          // Crear un nombre único para la imagen
          $imageName = uniqid() . '.'.$extension;
      
          // Especificar la ruta donde se guardará la imagen
          $imagePath = '../../vistas/com/solicitudRetiro/lotes/' . $imageName;
      
          // Guardar la imagen en el servidor
          file_put_contents($imagePath, $imageData);
      
      $Solicitud=ActaRetiro::create()->guardarLotes($usuarioId,$solicitudId,$ticket1,$ticket2,
      $peso_bruto,$peso_tara,$peso_neto,$nombre_lote,$imageName);

      return $solicitudId;
  }
  public function obtenerLotes($usuarioId,$id)
  {
    $dataActas=ActaRetiro::create()->obtenerLotes($id);
    return $dataActas;
  }
  public function eliminarLotes($usuarioId,$id,$archivo,$solicitudId)
  { 
    if($archivo==null){
    }
    else {
    $ruta='../../vistas/com/solicitudRetiro/lotes/'.$archivo;
    unlink($ruta);
    }
    $dataActas=ActaRetiro::create()->eliminarLotes($id);
    return $solicitudId;
  }


  public function guardarPesajesActaRetiro($items,$usuarioId,$actaId)
  { 
    if($items==null){
      throw new WarningException("Esta acta no tiene solicitudes asignadas.");  
    }
    else {
      foreach ($items as $id) {
        $lotes=SolicitudRetiro::create()->obtenerLotesXSolicitudId($id);
        if($lotes==null){
          throw new WarningException("Revisa las solicitudes no todas tienen lotes asignados.");
        }
    }
    $pesajeSumatoria=SolicitudRetiro::create()->obtenerActaSumatoriaPesaje($actaId);
    $pesajeSumatoria=$pesajeSumatoria[0]['suma'];
    $pesajeActa=SolicitudRetiro::create()->obtenerActaRetiroComparativo($actaId);
    $pesajeActa=$pesajeActa[0]['pesaje'];
    $margen20 = $pesajeActa * 0.02;

        // Verificar si la diferencia entre $pesajeSumatoria y $pesajeActa está dentro del margen del 20%
        if ($pesajeSumatoria < $pesajeActa && abs($pesajeSumatoria - $pesajeActa) > $margen20) {
          throw new WarningException("Existe un excedente del 2% entre el pesaje del acta de retiro y la suma de lotes por planta.");
      }
    $dataActas=ActaRetiro::create()->actualizarActaRetiroRecepcion($actaId);

    foreach ($items as $id) {
      $respuesta=($id > 0) ? SolicitudRetiro::create()->getSolicituRetiroXId($id ) : null;
      $persona=Persona::create()->obtenerPersonaXId($respuesta[0]['persona_reinfo_id']);
      $nombre=$persona[0]['nombre'];
      $telefono=$persona[0]['telefono'];
      $fecha=$respuesta[0]['fecha_entrega'];
      $fecha = date("d-m-Y", strtotime($fecha));
      
      $bodyNotificacion = '
      {
          "messaging_product": "whatsapp",
          "to": "[|phone|]",
          "type": "template",
          "template": {
              "name": "plantila_pesaje_planta",
              "language": {
                  "code": "es",
                  "policy": "deterministic"
              },
              "components": [
      
                  {
                      "type": "body",
                      "parameters": [
                          {
                              "type": "text",
                              "text": "[|nombre|]"
                          },
                                              {
                              "type": "text",
                              "text": "[|nro|]"
                          }
                      ]
                  }
              ]
          }
      }
      ';
      
      // Reemplaza las variables en el JSON
      $bodyNotificacion = str_replace("[|phone|]", '51'.$telefono, $bodyNotificacion);
      $bodyNotificacion = str_replace("[|nombre|]", $nombre, $bodyNotificacion);
      $bodyNotificacion = str_replace("[|nro|]", $id, $bodyNotificacion);
      // $bodyNotificacion = str_replace("[|motivo|]", $motivo, $bodyNotificacion);
      SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
  }
    return $dataActas;
  }

   }

   public function guardarPesajeSolicitudRetiro($usuarioId,$solicitud)
   { 
     $dataActas=ActaRetiro::create()->actualizarPesajeSolicitudRetiro($solicitud);
     return $dataActas;
   
 
    }

    public function obtenerSolicitudesRetiroEntregaResultados($usuarioId)
    { 
      $documento = 276;
      $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
      $planta_id = [];
      if ($matrizUsuario == null) {
        throw new WarningException("Usuario no tiene plantas asignadas.");
    }
  
    foreach ($matrizUsuario as $item) {
        $nivel = $item['nivel'];
  
           if ($nivel == 2) {
            $plantaId = $item['persona_planta_id'];
            if ($plantaId !== null) {
              $planta_id[] = $plantaId;
            }
        } 
    }
         $planta_id = implode(',', $planta_id);
    
         $dataActas=ActaRetiro::create()->obtenerSolicitudesRetiroEntregaResultados($planta_id);
         return $dataActas;
     }

     public function registrarResultadosLote($data, $usuarioId)
     {
         if ($data == null) {
             throw new WarningException("No se puede guardar los datos de los lotes.");
         } else {
             // Recorremos todos los lotes
             foreach ($data as $lote) {
                 // Procesamiento de datos generales del lote
                 $file = $lote['file'];
                 $name = $lote['name'];
                 $idLote = $lote['id'];
                 $solicitud_id = $lote['solicitud_id'];
                 $tmh = $lote['tmh'];
                 $porcentagua = $lote['porcentagua'];
                 $merma = $lote['merma'];
                 $tms = $lote['tms'];
     
                 // Validación de imagen (base64)
                 if ($file) {
                     $extension = pathinfo($name, PATHINFO_EXTENSION);
                     list($type, $imageData) = explode(';', $file);
                     list(, $imageData) = explode(',', $imageData);
     
                     // Decodificar los datos base64
                     $imageData = base64_decode($imageData);
     
                     // Crear un nombre único para la imagen
                     $imageName = uniqid() . '.' . $extension;
     
                     // Especificar la ruta donde se guardará la imagen
                     $imagePath = '../../vistas/com/entregaResultados/resultados/' . $imageName;
     
                     // Guardar la imagen en el servidor
                     file_put_contents($imagePath, $imageData);
                 } else {
                     // Si no hay imagen, asignar un valor predeterminado para imageName
                     $imageName = null;
                 }
     
                 // Validación de minerales
                 $tiposMinerales = [];
                 $totalMineralCalculadoSum = 0;
                 $totalMineralSum = 0;
                 $tipoMineralPrincipal = null;
                 $errores = [];
     
                 // Detalle de los minerales
                 $mineralesDetalle = [];
     
                 foreach ($lote['minerales'] as $mineral) {
                     // Verificamos si el tipo de mineral ya existe en el array de tipos de minerales
                     if (in_array($mineral['tipo_mineral'], $tiposMinerales)) {
                         $errores[] = "El mineral tipo '{$mineral['tipo_mineral']}' se repite en este lote.";
                     }
                     // Añadimos el tipo de mineral al array para futuras comprobaciones
                     $tiposMinerales[] = $mineral['tipo_mineral'];
     
                     // Verificamos si el total mineral calculado es válido
                     if (!is_numeric($mineral['total_mineral_calculado'])) {
                         $errores[] = "El valor de 'total_mineral_calculado' para el mineral '{$mineral['tipo_mineral']}' es inválido.";
                     }
     
                     // Acumulamos los totales calculados
                     $totalMineralCalculadoSum += $mineral['total_mineral_calculado'];
                     $totalMineralSum +=$mineral['total_mineral'];
     
                     // Si el mineral es de tipo "oro", lo guardamos como el principal
                     if ($mineral['tipo_mineral'] === 'Oro' && $tipoMineralPrincipal === null) {
                         $tipoMineralPrincipal = $mineral;
                     }
     
                     // Agregar detalle de mineral
                     $mineralesDetalle[] = [
                         'tipo_mineral' => $mineral['tipo_mineral'],
                         'ley' => $mineral['ley'],
                         'unidad' => $mineral['unidad'],
                         'recuperacion' => $mineral['recuperacion'],
                         'precio_internacional' => $mineral['precio_internacional'],
                         'descuento_internacional' => $mineral['descuento_internacional'],
                         'maquila' => $mineral['maquila'],
                         'penalidad' => $mineral['penalidad'],
                         'flete' => $mineral['flete'],
                         'total_mineral' => $mineral['total_mineral'],
                         'total_mineral_calculado' => $mineral['total_mineral_calculado']
                     ];
                 }
     
                 // Si encontramos errores, lanzamos una excepción y no registramos nada
                 if (count($errores) > 0) {
                     $errorMessages = implode(' ', $errores);
                     throw new WarningException("Error en los minerales del lote: " . $errorMessages);
                 }
     
                 // Si no encontramos el tipo de mineral principal ("oro"), lanzamos un error
                 if ($tipoMineralPrincipal === null) {
                     throw new WarningException("Se debe incluir al menos un mineral de tipo 'oro' en el lote.");
                 }
     
                 // Aquí guardamos los resultados del lote en la base de datos
                 $lotes = SolicitudRetiro::create()->actualizarLoteResultados(
                     $idLote, $tmh, $porcentagua, $merma, $tms,
                     $tipoMineralPrincipal['tipo_mineral'], $tipoMineralPrincipal['ley'],
                     $tipoMineralPrincipal['unidad'], $tipoMineralPrincipal['recuperacion'],
                     $tipoMineralPrincipal['precio_internacional'], $tipoMineralPrincipal['descuento_internacional'],
                     $tipoMineralPrincipal['maquila'], $tipoMineralPrincipal['penalidad'], $tipoMineralPrincipal['flete'],
                     $totalMineralSum, $totalMineralCalculadoSum, $imageName
                 );
     
                 // Si el lote no se guardó correctamente, lanzamos un error
                 if ($lotes == null) {
                     throw new WarningException("Revisa las solicitudes no todas tienen lotes asignados.");
                 }
     
                 // Guardar el detalle de los minerales en la base de datos
                 foreach ($mineralesDetalle as $detalle) {
                     $resultadoDetalle = SolicitudRetiro::create()->guardarDetalleMineral($idLote, $detalle['tipo_mineral'], $detalle['ley'],
                     $detalle['unidad'], $detalle['recuperacion'],
                     $detalle['precio_internacional'], $detalle['descuento_internacional'],
                     $detalle['maquila'], $detalle['penalidad'], $detalle['flete'],
                     $detalle['total_mineral'], $detalle['total_mineral_calculado']);
                     if ($resultadoDetalle == null) {
                         throw new WarningException("Error al guardar el detalle del mineral para el lote {$idLote}.");
                     }
                 }
     
                 // Si todo está bien, actualizamos la entrega de la solicitud
                 SolicitudRetiro::create()->actualizarEntregaSolicitud($solicitud_id);
             }
     
             return $lotes;
         }
     }
     

      public function obtenerLotesDirimencia($usuarioId)
      { 
        $documento = 276;
        $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
        $planta_id = [];
        if ($matrizUsuario == null) {
          throw new WarningException("Usuario no tiene plantas asignadas.");
      }
    
      foreach ($matrizUsuario as $item) {
          $nivel = $item['nivel'];
    
             if ($nivel == 2) {
              $plantaId = $item['persona_planta_id'];
              if ($plantaId !== null) {
                $planta_id[] = $plantaId;
              }
          } 
      }
           $planta_id = implode(',', $planta_id);
      
           $dataActas=ActaRetiro::create()->obtenerSolicitudesRetiroDirimencia($planta_id);
           return $dataActas;
       }

       public function obtenerLotesNegociar($usuarioId)
       { 
         $documento = 276;
         $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
         $planta_id = [];
         if ($matrizUsuario == null) {
           throw new WarningException("Usuario no tiene plantas asignadas.");
       }
     
       foreach ($matrizUsuario as $item) {
           $nivel = $item['nivel'];
     
              if ($nivel == 2) {
               $plantaId = $item['persona_planta_id'];
               if ($plantaId !== null) {
                 $planta_id[] = $plantaId;
               }
           } 
       }
            $planta_id = implode(',', $planta_id);
       
            $dataActas=ActaRetiro::create()->obtenerSolicitudesRetiroNegociar($planta_id);
            return $dataActas;
        }

       public function obtenerLotesConfirmados($usuarioId)
       { 
         $documento = 276;
         $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
         $planta_id = [];
         if ($matrizUsuario == null) {
           throw new WarningException("Usuario no tiene plantas asignadas.");
       }
     
       foreach ($matrizUsuario as $item) {
           $nivel = $item['nivel'];
     
              if ($nivel == 2) {
               $plantaId = $item['persona_planta_id'];
               if ($plantaId !== null) {
                 $planta_id[] = $plantaId;
               }
           } 
       }
            $planta_id = implode(',', $planta_id);
       
            $dataActas=ActaRetiro::create()->obtenerSolicitudesRetiroConfirmadosResultados($planta_id);
            return $dataActas;
        }
       
     
    
       public function guardarActualizacionDirimencia($id,$file,$ley,$lote,$usuarioId,$monto)
       { 
        $lote_sumatoria=ActaRetiro::create()->obtenerLoteSecundarios($lote);
        $lote_info=ActaRetiro::create()->obtenerLotesXId($lote);
        $tms = $lote_info[0]['tms'];  // Ejemplo de valor para tms
$ley = $ley;  // Ejemplo de valor para ley
$recuperacion = $lote_info[0]['procentaje_recuperacion'];  // Ejemplo de valor para recuperacion
$precio_internacional = $lote_info[0]['precio_internacional'];;  // Ejemplo de valor para precio_internacional
$descuento_internacional = $lote_info[0]['descuento_internacional'];;  // Ejemplo de valor para descuento_internacional
$maquila = $lote_info[0]['maquila'];  // Ejemplo de valor para maquila
$penalidad = $lote_info[0]['penalidad'];  // Ejemplo de valor para penalidad
$flete = $lote_info[0]['flete'];;  // Ejemplo de valor para flete

// Calculo de las onzas
$tempOnzas = $tms * $ley * ($recuperacion / 100);
$totalOnzas = round($tempOnzas, 3);  // Redondeado a 3 decimales

// Calculo de la cotización
$cotizacion = $precio_internacional - $descuento_internacional;

// Total pagable
$totalPagable = $totalOnzas * $cotizacion;

// Deducciones
$maquilaTotal = $maquila * $tms;
$penalidadTotal = $penalidad * $tms;
$transporteTotal = $flete * $tms;

$totalDeducible = $maquilaTotal + $penalidadTotal + $transporteTotal;

// Total con descuento
$totalConDescuento = $totalPagable - $totalDeducible;

// Resultado final con ajuste
$result = $totalConDescuento * 1.1023;

// Total calculado redondeado a 3 decimales
$totalCalculado = round($result, 3);
$totalSecundario=$lote_sumatoria[0]['total_calculado'];
$totalCalculado2=$totalCalculado+$totalSecundario;
$limiteCalculado=$totalCalculado2-1;
$montoReal=$monto-$totalSecundario;
if($monto<$limiteCalculado){
  $excedente=$limiteCalculado-$monto;
  throw new WarningException("El monto ingresado : ".$monto.", es inferior en : ".$excedente." al monto calculado.");
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
        $imagePath = '../../vistas/com/dirimencia/resultados/' . $imageName;
        $imagePath2 = '../../vistas/com/entregaResultados/resultados/' . $imageName;
        // Guardar la imagen en el servidor
        file_put_contents($imagePath, $imageData);
        file_put_contents($imagePath2, $imageData);
      
    
        $dirimencia=ActaRetiro::create()->guardarResultadoFinalLoteDirimencia($id,$usuarioId,$ley,$imageName);

        if($dirimencia[0]['vout_exito']==0){
          unlink($imagePath);
          unlink($imagePath2);
          throw new WarningException("No se pudo registrar la dirimencia.");
        }

        $lotes=ActaRetiro::create()->actualizarLeyLoteDirimencia($lote,$ley,$totalCalculado2,$monto);
        $mineral=ActaRetiro::create()->actualizarLeyLoteDirimenciaMineral($lote,$ley,$totalCalculado,$montoReal);
        return $dirimencia[0]['vout_mensaje'];

       }

       public function guardarActualizacionNegociar($id,$file,$ley,$lote,$usuarioId,$monto)
       { 
        $lote_sumatoria=ActaRetiro::create()->obtenerLoteSecundarios($lote);
        $lote_info=ActaRetiro::create()->obtenerLotesXId($lote);
        $tms = $lote_info[0]['tms'];  // Ejemplo de valor para tms
$ley = $ley;  // Ejemplo de valor para ley
$recuperacion = $lote_info[0]['procentaje_recuperacion'];  // Ejemplo de valor para recuperacion
$precio_internacional = $lote_info[0]['precio_internacional'];;  // Ejemplo de valor para precio_internacional
$descuento_internacional = $lote_info[0]['descuento_internacional'];;  // Ejemplo de valor para descuento_internacional
$maquila = $lote_info[0]['maquila'];  // Ejemplo de valor para maquila
$penalidad = $lote_info[0]['penalidad'];  // Ejemplo de valor para penalidad
$flete = $lote_info[0]['flete'];;  // Ejemplo de valor para flete

// Calculo de las onzas
$tempOnzas = $tms * $ley * ($recuperacion / 100);
$totalOnzas = round($tempOnzas, 3);  // Redondeado a 3 decimales

// Calculo de la cotización
$cotizacion = $precio_internacional - $descuento_internacional;

// Total pagable
$totalPagable = $totalOnzas * $cotizacion;

// Deducciones
$maquilaTotal = $maquila * $tms;
$penalidadTotal = $penalidad * $tms;
$transporteTotal = $flete * $tms;

$totalDeducible = $maquilaTotal + $penalidadTotal + $transporteTotal;

// Total con descuento
$totalConDescuento = $totalPagable - $totalDeducible;

// Resultado final con ajuste
$result = $totalConDescuento * 1.1023;

// Total calculado redondeado a 3 decimales
$totalCalculado = round($result, 3);
$totalSecundario=$lote_sumatoria[0]['total_calculado'];
$totalCalculado2=$totalCalculado+$totalSecundario;
$limiteCalculado=$totalCalculado2-1;
$montoReal=$monto-$totalSecundario;
if($monto<$limiteCalculado){
  $excedente=$limiteCalculado-$monto;
  throw new WarningException("El monto ingresado : ".$monto.", es inferior en : ".$excedente." al monto calculado.");
}

        // list($type, $imageData) = explode(';', $file);
        // list(, $imageData) = explode(',', $imageData);
        // list(, $tipo) = explode('/', $type);
        // $extension=$tipo;
        // // Decodificar los datos base64
        // $imageData = base64_decode($imageData);
    
        // // Crear un nombre único para la imagen
        // $imageName = uniqid() . '.'.$extension;
    
        // // Especificar la ruta donde se guardará la imagen
        // $imagePath = '../../vistas/com/dirimencia/resultados/' . $imageName;
        // $imagePath2 = '../../vistas/com/entregaResultados/resultados/' . $imageName;
        // // Guardar la imagen en el servidor
        // file_put_contents($imagePath, $imageData);
        // file_put_contents($imagePath2, $imageData);
        $imageName='';
    
        $dirimencia=ActaRetiro::create()->guardarResultadoFinalLoteDirimencia($id,$usuarioId,$ley,$imageName);

        if($dirimencia[0]['vout_exito']==0){
          // unlink($imagePath);
          // unlink($imagePath2);
          throw new WarningException("No se pudo registrar la dirimencia.");
        }

        $lotes=ActaRetiro::create()->actualizarLeyLoteNegociar($lote,$ley,$totalCalculado2,$monto,$imageName);
        $mineral=ActaRetiro::create()->actualizarLeyLoteDirimenciaMineral($lote,$ley,$totalCalculado,$montoReal);
        return $dirimencia[0]['vout_mensaje'];

       }

       public function obtenerConfiguracionesFiltros(){
        $respuesta = new ObjectUtil();
        // $respuesta->zonas = Zona::create()->getAllZonas();
        // $respuesta->plantas = Persona::create()->obtenerPersonasXClase(25);
        $respuesta->vehiculos = Vehiculo::create()->getAllVehiculos(); // 2-> natural
        $respuesta->usuario = Usuario::create()->getDataUsuario();
      
        return $respuesta;
      }
      public function obtenerConfiguracionesFiltrosRetencion(){
        $respuesta = new ObjectUtil();
        // $respuesta->zonas = Zona::create()->getAllZonas();
        // $respuesta->plantas = Persona::create()->obtenerPersonasXClase(25);
        $respuesta->proveedor = Persona::create()->obtenerComboPersonaProveedores(); // 2-> natural
        $respuesta->usuario = Usuario::create()->getDataUsuario();
      
        return $respuesta;
      }

      public function guardarFactura($serie,$correlativo,$subtotal,$igv,$totalFactura,
      $detraccion,$netoPago,$lotes,$usuarioId,$minero){
     
    //Primero obtenemos la planta por usuarioId

        $documento = 276;
        $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
        $planta_id = null;  
        
        if ($matrizUsuario == null) {
            throw new WarningException("Usuario no tiene plantas asignadas.");
        }
        
        foreach ($matrizUsuario as $item) {
            $nivel = $item['nivel'];
        
            // Verificar que el nivel sea 2 y que la planta esté asignada
            if ($nivel == 2 && $item['persona_planta_id'] !== null) {
                $planta_id = $item['persona_planta_id'];  // Guardar el primer `persona_planta_id` encontrado
                break;  // Salir del bucle ya que solo necesitamos el primer `persona_planta_id`
            }
        }
        
        // Verificar si se encontró un `persona_planta_id`
        if ($planta_id === null) {
            throw new WarningException("No hay planta asignada para este usuario.");
        }

    //DATOS DEL FACTURADOR

       $personaMinero=Persona::create()->obtenerPersonaGetByIdAll($minero);
        if($personaMinero[0]['persona_padre_id']==null)
        {
          $facturadorId=$personaMinero[0]['id'];
          $facturadorCodigo=$personaMinero[0]['codigo_identificacion'];
          $facturadorRazon=$personaMinero[0]['nombre'];
          $facturadorEmail=$personaMinero[0]['email'];
          $facturadorSerie=$personaMinero[0]['serie_factura'];
          $facturadorCorrelativo=$personaMinero[0]['correlativo_factura'];
        }
        else 
        {
          $personaMinero=Persona::create()->obtenerPersonaGetByIdAll($personaMinero[0]['persona_padre_id']);
          $facturadorId=$personaMinero[0]['id'];
          $facturadorCodigo=$personaMinero[0]['codigo_identificacion'];
          $facturadorRazon=$personaMinero[0]['nombre'];
          $facturadorEmail=$personaMinero[0]['email'];
          $facturadorSerie=$personaMinero[0]['serie_factura'];
          $facturadorCorrelativo=$personaMinero[0]['correlativo_factura'];
          
        }
        
        $incrementado = (int)$facturadorCorrelativo + 1;
       $correlativoNuevo = sprintf('%06d', $incrementado);
    //DATOS DEL FACTURADOR DIRECCION
        $personaMineroDireccion=Persona::create()->obtenerDireccionXPersonaId($facturadorId);
    
    //DATOS DE PLANTA
       $personaPlanta=Persona::create()->obtenerPersonaGetByIdAll($planta_id);
       $plantaCodigo=$personaPlanta[0]['codigo_identificacion'];
       $plantaRazon=$personaPlanta[0]['nombre'];
    
    //DATOS DEL FACTURADOR DIRECCION
     $personaPlantaDireccion=Persona::create()->obtenerDireccionXPersonaId($planta_id);

        $codigo=$serie.'-'.$correlativo;
     //COMENTARIO FACTURA
     $lotesConcatenados = [];

// Decodifica el JSON
$lotesArray = json_decode($lotes, true); 

// Itera sobre cada lote
$totalTms = 0;
foreach ($lotesArray as $lote) {
    // Obtén la información del lote
    $loteInfo = ActaRetiro::create()->obtenerLotesXId($lote);
    
    // Asegúrate de que $loteInfo contenga el campo 'lote' y 'tms' con los valores esperados
    if (isset($loteInfo[0]['lote']) && isset($loteInfo[0]['tms'])) {
        // Concatenar el texto "MINERAL AURIFERO LOTE" con el valor del lote y tms
        $lotesConcatenados[] = "MINERAL AURIFERO LOTE " . $loteInfo[0]['lote'] . " - TMS: " . $loteInfo[0]['tms'];
        $lotesConcatenadosTransporte[] =  $loteInfo[0]['lote'] ;
        $totalTms += $loteInfo[0]['tms'];
    }
}

// Convertir el arreglo en una cadena separada por comas
   $textoLotes = implode(", ", $lotesConcatenados);
   $textoLotesTransporte = implode(", ", $lotesConcatenadosTransporte);
   $montoLetrasFactura=utf8_decode(self::convertir_a_texto($totalFactura));
   $tipoCambio=SolicitudRetiroNegocio::create()->obtenerTipoCambio2($usuarioId);
    // $datoPalabra=self::convertir_a_palabras($totalFactura*1);

        $fecha = date('Y-m-d'); // Fecha en formato YYYY-MM-DD
        $nueva_fecha = date('Y-m-d', strtotime($fecha . ' +5 days')); 
        $hora = date('H:i:s');
        $token= SolicitudRetiroNegocio::create()->generarTokenEfact('20600739256','e24243d460d9d29bddcafdef34c7f4cf853e719d5e217984d2149150d52397e2');
        $token = $token->access_token;


        //FACTURA MINERAL
      
        $jsonFactura = SolicitudRetiroNegocio::create()->generarJsonFactura($fecha,$hora,$subtotal,$igv,$totalFactura,
        $detraccion*$tipoCambio,$netoPago,$facturadorCodigo,$facturadorRazon,$facturadorEmail,$facturadorSerie,$facturadorCorrelativo,
        $plantaCodigo,$plantaRazon,$personaMineroDireccion,$personaPlantaDireccion,$nueva_fecha,$textoLotes,$montoLetrasFactura
      );
      
       $facturarDocumento=SolicitudRetiroNegocio::create()->enviarEfactDocumentoElectronico($token,$jsonFactura);
       $codeFactura=$facturarDocumento->code;
       $facturarDocumento=$facturarDocumento->description;
       
       
       if($codeFactura=='0'){
       $comentarioEfact=SolicitudRetiroNegocio::create()->consultarDocumentoEfact($token,$facturarDocumento);
       $comentarioEfact=$comentarioEfact->description;
       }
       else {
         $comentarioEfact=$facturarDocumento;
         $facturarDocumento=''; 
         throw new WarningException("No se pudo registrar la liquidación."); 
       }

        $valorizacion=ActaRetiro::create()->registrarValorizacion($facturadorSerie,$facturadorCorrelativo,$subtotal,$igv,$totalFactura,
        $detraccion,$netoPago,$usuarioId,$comentarioEfact,$facturarDocumento,$planta_id,
        $codigo,$minero);
       $valorizacionId=$valorizacion[0]['id'];

      
       $valorizacionDetalle=ActaRetiro::create()->registrarValorizacionDetalle($facturadorSerie,$facturadorCorrelativo,$subtotal,$igv,$totalFactura,
       $detraccion,$netoPago,$usuarioId,$comentarioEfact,$facturarDocumento,'Factura Mineral',$valorizacionId);
       
       //FACTURA CARGUIO MINERAL
       $pesoLotes=round($totalTms,2);
       $constanteCarguio=15;
       $subtotalCarguio=round($pesoLotes*$constanteCarguio,2);
       $igvCarguio=round($subtotalCarguio*0.18,2);
       $totalFacturaCarguio=round($subtotalCarguio*1.18,2);
       $detraccionCarguio=round($totalFacturaCarguio/10,0);
       $netoPagoCarguio=$totalFacturaCarguio-$detraccionCarguio;
       
       $montoLetrasFactura=utf8_decode(self::convertir_a_texto2($totalFacturaCarguio));

       $jsonFacturaCarguio = SolicitudRetiroNegocio::create()->generarJsonFacturaCarguio($fecha,$hora,$subtotalCarguio,$igvCarguio,$totalFacturaCarguio,
       $detraccionCarguio,$netoPagoCarguio,$facturadorCodigo,$facturadorRazon,$facturadorEmail,$facturadorSerie,$correlativoNuevo,
       $plantaCodigo,$plantaRazon,$personaMineroDireccion,$personaPlantaDireccion,$nueva_fecha,'POR EL SERVICIO DE CARGUIO EN MINA',$montoLetrasFactura,$pesoLotes
     );

     $facturarDocumentoCarguio=SolicitudRetiroNegocio::create()->enviarEfactDocumentoElectronico($token,$jsonFacturaCarguio);
     $codeFacturaCarguio=$facturarDocumentoCarguio->code;
     $facturarDocumentoCarguio=$facturarDocumentoCarguio->description;
     
     
     if($codeFacturaCarguio=='0'){
     $comentarioEfactCarguio=SolicitudRetiroNegocio::create()->consultarDocumentoEfact($token,$facturarDocumentoCarguio);
     $comentarioEfactCarguio=$comentarioEfactCarguio->description;
     }
     else {
       $comentarioEfactCarguio=$facturarDocumentoCarguio;
       $facturarDocumentoCarguio=''; 
       throw new WarningException("No se pudo registrar la liquidación."); 
     }
     $valorizacionDetalle=ActaRetiro::create()->registrarValorizacionDetalle($facturadorSerie,$correlativoNuevo,$subtotalCarguio,$igvCarguio,$totalFacturaCarguio,
     $detraccionCarguio,$netoPagoCarguio,$usuarioId,$comentarioEfactCarguio,$facturarDocumentoCarguio,'Factura Carguio',$valorizacionId);
     
        $incrementado = (int)$correlativoNuevo + 1;
        $correlativoNuevo = sprintf('%06d', $incrementado);

    //FACTURA SUBVENCION TRANSPORTE

    $pesoLotes=round($totalTms,2);
    $constanteTransporte=20;
    $subtotalTransporte=round($pesoLotes*$constanteTransporte,2);
    $igvTransporte=round($subtotalTransporte*0.18,2);
    $totalFacturaTransporte=round($subtotalTransporte*1.18,2);
   
   
    $montoLetrasFactura=utf8_decode(self::convertir_a_texto($totalFacturaTransporte));

    $jsonFacturaTransporte = SolicitudRetiroNegocio::create()->generarJsonFacturaTransporte($fecha,$hora,$subtotalTransporte,$igvTransporte,$totalFacturaTransporte,
    $facturadorCodigo,$facturadorRazon,$facturadorEmail,$facturadorSerie,$correlativoNuevo,
    $plantaCodigo,$plantaRazon,$personaMineroDireccion,$personaPlantaDireccion,$nueva_fecha,'LIQUIDACION DE COSTOS DE TRANSPORTE DE MINERAL ASUMIDO SEGUN CONTRATO, LOTES '.$textoLotesTransporte.', FACTURA NRO '.$facturadorSerie.'-'.$facturadorCorrelativo,
    $montoLetrasFactura,$pesoLotes
  );

  $facturarDocumentoTransporte=SolicitudRetiroNegocio::create()->enviarEfactDocumentoElectronico($token,$jsonFacturaTransporte);
  $codeFacturaTransporte=$facturarDocumentoTransporte->code;
  $facturarDocumentoTransporte=$facturarDocumentoTransporte->description;
  
  
  if($codeFacturaTransporte=='0'){
  $comentarioEfactTransporte=SolicitudRetiroNegocio::create()->consultarDocumentoEfact($token,$facturarDocumentoTransporte);
  $comentarioEfactTransporte=$comentarioEfactTransporte->description;
  }
  else {
    $comentarioEfactTransporte=$facturarDocumentoTransporte;
    $facturarDocumentoTransporte=''; 
    throw new WarningException("No se pudo registrar la liquidación."); 
  }
  $valorizacionDetalle=ActaRetiro::create()->registrarValorizacionDetalle($facturadorSerie,$correlativoNuevo,$subtotalTransporte,$igvTransporte,$totalFacturaTransporte,
  0,$totalFacturaTransporte,$usuarioId,$comentarioEfactTransporte,$facturarDocumentoTransporte,'Factura Subvencion Transporte',$valorizacionId);
  
     $incrementado = (int)$correlativoNuevo + 1;
     $correlativoNuevo = sprintf('%06d', $incrementado);

   $actualizarValorizacionEfact=ActaRetiro::create()->actualizarValorizacionEfact($comentarioEfactTransporte,$facturarDocumentoTransporte
   ,$comentarioEfactCarguio,$facturarDocumentoCarguio,$valorizacionId);
   
       // Ahora puedes recorrerlo con foreach
       foreach ($lotesArray as $lote) {
           $comentarioEfact = ActaRetiro::create()->actualizarLotesValorizados($valorizacionId, $lote);
           // Aquí puedes hacer algo con $comentarioEfact si es necesario
       }
          $facturador=ActaRetiro::create()->actualizarCorrelativoFacturador($facturadorId,$correlativoNuevo);
      
        return $valorizacion;
      }

      public function numero_a_texto($numero) {
        $unidad = array(
            0 => "CERO", 1 => "UNO", 2 => "DOS", 3 => "TRES", 4 => "CUATRO", 5 => "CINCO",
            6 => "SEIS", 7 => "SIETE", 8 => "OCHO", 9 => "NUEVE", 10 => "DIEZ", 11 => "ONCE",
            12 => "DOCE", 13 => "TRECE", 14 => "CATORCE", 15 => "QUINCE", 16 => "DIECISÉIS",
            17 => "DIECISIETE", 18 => "DIECIOCHO", 19 => "DIECINUEVE", 20 => "VEINTE"
        );
        
        $decenas = array(
            30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 
            70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA"
        );
        
        $centenas = array(
            100 => "CIENTO", 200 => "DOCIENTOS", 300 => "TRESCIENTOS", 400 => "CUATROCIENTOS",
            500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 
            800 => "OCHOCIENTOS", 900 => "NOVECIENTOS"
        );
        
        // Lógica para unidades, decenas y centenas
        if ($numero < 21) {
            return $unidad[$numero];
        } elseif ($numero < 100) {
            $decena = floor($numero / 10) * 10;
            $unidad_restante = $numero % 10;
            return $decenas[$decena] . ($unidad_restante ? " Y " . $unidad[$unidad_restante] : "");
        } elseif ($numero < 1000) {
            $centena = floor($numero / 100) * 100;
            $resto = $numero % 100;
            return $centenas[$centena] . ($resto ? " " . self::numero_a_texto($resto) : "");
        } elseif ($numero < 1000000) {
            $miles = floor($numero / 1000);
            $resto = $numero % 1000;
            return self::numero_a_texto($miles) . " MIL" . ($resto ? " " . self::numero_a_texto($resto) : "");
        } elseif ($numero < 1000000000) {
            $millones = floor($numero / 1000000);
            $resto = $numero % 1000000;
            return self::numero_a_texto($millones) . " MILLONES" . ($resto ? " " . self::numero_a_texto($resto) : "");
        }
        
        return "Número fuera de rango"; // Si el número es más grande, agregar más lógica si es necesario
    }
    
    public function convertir_a_texto($numero) {
        // Obtener la parte decimal y entera
        $parte_decimal = substr($numero, strpos($numero, '.') + 1);
        $parte_entera = floor($numero);
        
        // Convertir la parte entera a texto
        $texto_entero = self::numero_a_texto($parte_entera);
        
        // Convertir la parte decimal a formato fraccionario
        $texto_decimal = $parte_decimal ? " Y " . $parte_decimal . "/100" : '';
        
        return strtoupper($texto_entero . $texto_decimal . " DOLAR AMERICANO");
    }

    public function convertir_a_texto2($numero) {
      // Obtener la parte decimal y entera
      $parte_decimal = substr($numero, strpos($numero, '.') + 1);
      $parte_entera = floor($numero);
      
      // Convertir la parte entera a texto
      $texto_entero = self::numero_a_texto($parte_entera);
      
      // Convertir la parte decimal a formato fraccionario
      $texto_decimal = $parte_decimal ? " Y " . $parte_decimal . "/100" : '';
      
      return strtoupper($texto_entero . $texto_decimal . " SOLES");
  }
    
      public function getAllValorizacion( $elemntosFiltrados, $columns, $order, $start, $usuarioId,$idPersona,$fecha )
      {
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        $documento = 276;
        $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
        $planta_id = null;  
        
        if ($matrizUsuario == null) {
            throw new WarningException("Usuario no tiene plantas asignadas.");
        }
        
        foreach ($matrizUsuario as $item) {
            $nivel = $item['nivel'];
        
            // Verificar que el nivel sea 2 y que la planta esté asignada
            if ($nivel == 2 && $item['persona_planta_id'] !== null) {
                $planta_id = $item['persona_planta_id'];  // Guardar el primer `persona_planta_id` encontrado
                break;  // Salir del bucle ya que solo necesitamos el primer `persona_planta_id`
            }
        }
        
        // Verificar si se encontró un `persona_planta_id`
        if ($planta_id === null) {
            throw new WarningException("No hay planta asignada para este usuario.");
        }
        return ActaRetiro::create()->getAllValorizacion($columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$planta_id,$fecha);
      }
      
      public function getCantidadAllValorizacion( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$idPersona,$fecha)
      {
    
        $columnaOrdenarIndice = $order[0]['column'];
        $formaOrdenar = $order[0]['dir'];
        $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
        $documento = 276;
        $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
        $planta_id = null;  
        
        if ($matrizUsuario == null) {
            throw new WarningException("Usuario no tiene plantas asignadas.");
        }
        
        foreach ($matrizUsuario as $item) {
            $nivel = $item['nivel'];
        
            // Verificar que el nivel sea 2 y que la planta esté asignada
            if ($nivel == 2 && $item['persona_planta_id'] !== null) {
                $planta_id = $item['persona_planta_id'];  // Guardar el primer `persona_planta_id` encontrado
                break;  // Salir del bucle ya que solo necesitamos el primer `persona_planta_id`
            }
        }
        
        // Verificar si se encontró un `persona_planta_id`
        if ($planta_id === null) {
            throw new WarningException("No hay planta asignada para este usuario.");
        }
        return ActaRetiro::create()->getCantidadAllValorizacion($columnaOrdenar, $formaOrdenar, $usuarioId,$planta_id,$fecha);
      }

      public function obtenerValorizacionesGeneradas($tipo,$usuarioId)
       { 
         $documento = 276;
         $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
         $planta_id = [];
         if ($matrizUsuario == null) {
           throw new WarningException("Usuario no tiene plantas asignadas.");
       }
     
       foreach ($matrizUsuario as $item) {
           $nivel = $item['nivel'];
     
              if ($nivel == 2) {
               $plantaId = $item['persona_planta_id'];
               if ($plantaId !== null) {
                 $planta_id[] = $plantaId;
               }
           } 
       }
            $planta_id = implode(',', $planta_id);
       
            $dataActas=ActaRetiro::create()->obtenerValorizacionesXPlantasXTipo($planta_id,$tipo);
            return $dataActas;
        }

        public function guardarPago($tipo,$subtotal,$fileBase64,$fileExtension,
        $lotes,$usuarioId,$minero,$numeroOperacion){
       
      //Primero obtenemos la planta por usuarioId
  
          $documento = 276;
          $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
          $planta_id = null;  
          
          if ($matrizUsuario == null) {
              throw new WarningException("Usuario no tiene plantas asignadas.");
          }
          
          foreach ($matrizUsuario as $item) {
              $nivel = $item['nivel'];
          
              // Verificar que el nivel sea 2 y que la planta esté asignada
              if ($nivel == 2 && $item['persona_planta_id'] !== null) {
                  $planta_id = $item['persona_planta_id'];  // Guardar el primer `persona_planta_id` encontrado
                  break;  // Salir del bucle ya que solo necesitamos el primer `persona_planta_id`
              }
          }
          
          // Verificar si se encontró un `persona_planta_id`
          if ($planta_id === null) {
              throw new WarningException("No hay planta asignada para este usuario.");
          }
         
          list($type, $imageData) = explode(';', $fileBase64);
          list(, $imageData) = explode(',', $imageData);
      
          // Decodificar los datos base64
          $imageData = base64_decode($imageData);
      
          // Crear un nombre único para la imagen
          $imageName = uniqid() .'.'.$fileExtension;
      
          // Especificar la ruta donde se guardará la imagen
          $imagePath = '../../vistas/com/pago_planta/pagos/' . $imageName;
   
          // Guardar la imagen en el servidor
          file_put_contents($imagePath, $imageData);
          $valorizacion=ActaRetiro::create()->registrarPagoPlanta($subtotal,$imageName,
          $planta_id,$minero,$numeroOperacion,$usuarioId,$tipo);
         $valorizacionId=$valorizacion[0]['id'];
  
         $lotesArray = json_decode($lotes, true); // El segundo parámetro "true" convierte el JSON en un arreglo asociativo
  
         // Ahora puedes recorrerlo con foreach
         foreach ($lotesArray as $lote) {
             $comentarioEfact = ActaRetiro::create()->actualizarValorizacionesPagadas($valorizacionId, $lote,$tipo);
             // Aquí puedes hacer algo con $comentarioEfact si es necesario
         }
          
        
          return $valorizacion;
        }

        public function getAllPagoPlanta( $elemntosFiltrados, $columns, $order, $start, 
        $usuarioId,$fecha,$factura )
        {
          $columnaOrdenarIndice = $order[0]['column'];
          $formaOrdenar = $order[0]['dir'];
          $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
          // $obtenerPlantaxUsuario=Usuario::create()->obtenerPlantaXUsuarioId($usuarioId);
          $documento = 276;
          $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
          $planta_id = [];
          if ($matrizUsuario == null) {
            throw new WarningException("Usuario no tiene zonas asignadas.");
        }
      
        foreach ($matrizUsuario as $item) {
            $nivel = $item['nivel'];
      
               if ($nivel == 2) {
                $plantaId = $item['persona_planta_id'];
                if ($plantaId !== null) {
                  $planta_id[] = $plantaId;
                }
            } 
        }
             $planta_id = implode(',', $planta_id);
         
          return ActaRetiro::create()->getAllPagoPlanta($columnaOrdenar, $formaOrdenar, $elemntosFiltrados,
           $start, $usuarioId,$planta_id,$fecha,$factura);
        }

        public function getCantidadAllPagoPlanta( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$fecha,$factura)
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
    // $obtenerPlantaxUsuario=Usuario::create()->obtenerPlantaXUsuarioId($usuarioId);
    $documento = 276;
    $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
    $planta_id = [];
    if ($matrizUsuario == null) {
      throw new WarningException("Usuario no tiene zonas asignadas.");
  }

  foreach ($matrizUsuario as $item) {
      $nivel = $item['nivel'];

         if ($nivel == 2) {
          $plantaId = $item['persona_planta_id'];
          if ($plantaId !== null) {
            $planta_id[] = $plantaId;
          }
      } 
  }
       $planta_id = implode(',', $planta_id);
  
    return ActaRetiro::create()->getCantidadAllPagoPlanta($columnaOrdenar, $formaOrdenar, $usuarioId,$planta_id,$fecha,$factura);
  }

  public function obtenerConfiguracionesRetenciones( $usuarioId,$solicitudId)
  {
    $respuesta = new ObjectUtil();
    //$respuesta->empresa = EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    
    //        $respuesta->persona_clase = Persona::create()->getAllPersonaClaseByTipo($personaTipoId);
    $persona=Persona::create()->obtenerPersonaXUsuarioId($usuarioId);
    //contactos
    $respuesta->tipoCambio = SolicitudRetiroNegocio::create()->obtenerTipoCambio($usuarioId );
    $respuesta->persona = $persona ;// 2-> natural
    $respuesta->fecha=date('d/m/Y')   ;
  
    return $respuesta;
  }
  public function obtenerDataProveedor(  $usuarioId,$ruc )
  {
    if (strlen($ruc) !== 11) {
      throw new WarningException("El RUC del proveedor no cuenta con 11 caracteres."); 
    }
    $respuesta = new ObjectUtil();
    $dataRUC=Persona::create()->obtenerPersonaXCodigoIdentificacion($ruc);

    if($dataRUC==null){
    $respuesta= SolicitudRetiroNegocio::create()->obtenerUbigeo($usuarioId ,$ruc);
    }
    else{
    $proveedorId=$dataRUC[0]['id'];
    $personaProveedor=Persona::create()->obtenerPersonaGetByIdAll($proveedorId);
    $respuesta->ruc=$personaProveedor[0]['codigo_identificacion'];
    $respuesta->razonSocial=$personaProveedor[0]['nombre'];
    $proveedorDireccion=Persona::create()->obtenerDireccionXPersonaId($proveedorId);
    $respuesta->ubigeoCodigo=$proveedorDireccion[0]['ubigeo'];
    $respuesta->departamento=$proveedorDireccion[0]['departamento'];
    $respuesta->provincia=$proveedorDireccion[0]['provincia'] ;
    $respuesta->distrito=$proveedorDireccion[0]['distrito'] ;
    $respuesta->domicilio_fiscal=$proveedorDireccion[0]['direccion'];
    }
    return $respuesta;
  }

  public function insertRetencionFacturas($usuarioId,$tipoCambio,$ruc,$razonSocial,$ubigeo,$departamento,
  $provincia,$distrito,$direccion,$factura ,$fechaFactura,$montoFactura,$porcentajeRetencion,$fechaPago,$moneda){
     
          $personaMinero=Persona::create()->obtenerPersonaGetByIdAll(3164);
          $facturadorId=$personaMinero[0]['id'];
          $facturadorCodigo=$personaMinero[0]['codigo_identificacion'];
          $facturadorRazon=$personaMinero[0]['nombre'];
          $facturadorEmail=$personaMinero[0]['email'];
          $facturadorSerie=$personaMinero[0]['serie_retencion'];
          $facturadorCorrelativo=$personaMinero[0]['correlativo_retencion'];
          
          $personaMineroDireccion=Persona::create()->obtenerDireccionXPersonaId($facturadorId);

        $incrementado = (int)$facturadorCorrelativo + 1;
        $correlativoNuevo = sprintf('%06d', $incrementado);
       
        if($moneda=='soles'){
          $monedaId=2;
          $tipoCambio2=round(1,3);
          $simbolo='PEN';
          
        }
        else{
          $monedaId=4;
          $tipoCambio2=round($tipoCambio,3);
          $simbolo='USD';
        }

        $montoFacturaSoles=round($montoFactura*$tipoCambio2,2);
        $montoRetencion=round($montoFacturaSoles*$porcentajeRetencion/100,2);
        $montoquitadoretencion=round($montoFacturaSoles-$montoRetencion,2);
     //COMENTARIO FACTURA

        $fecha = date('Y-m-d'); // Fecha en formato YYYY-MM-DD
        
       
        $token= SolicitudRetiroNegocio::create()->generarTokenEfactProduccion('20600739256','6c8891e5029fdfb77edae3a0daf06ba99749b88a1c2c3956f12c712d288c920c');
        $token = $token->access_token;
      
        $jsonFactura = SolicitudRetiroNegocio::create()->generarJsonRetencion($fecha,$facturadorSerie,$facturadorCorrelativo,$personaMineroDireccion,
        $tipoCambio2,$simbolo,$montoFactura,$montoRetencion,$montoquitadoretencion,
        $ruc,$razonSocial,$ubigeo,$departamento,
  $provincia,$distrito,$direccion,$factura ,$fechaFactura,$fechaPago,$facturadorCodigo,$facturadorRazon,$porcentajeRetencion
      );
      
       $facturarDocumento=SolicitudRetiroNegocio::create()->enviarEfactDocumentoElectronicoProduccion($token,$jsonFactura);
       $codeFactura=$facturarDocumento->code;
       $facturarDocumento=$facturarDocumento->description;
       
       
       if($codeFactura=='0'){
       $comentarioEfact=SolicitudRetiroNegocio::create()->consultarDocumentoEfactProduccion($token,$facturarDocumento);
       $comentarioEfact=$comentarioEfact->description;
       }
       else {
         $comentarioEfact=$facturarDocumento;
         $facturarDocumento=''; 
         throw new WarningException("No se pudo registrar la retención."); 
       }
        
       $dataRUC=Persona::create()->obtenerPersonaXCodigoIdentificacion($ruc);
       $proveedorId=$dataRUC[0]['id'];
       $retencion=$facturadorSerie.'-'.$facturadorCorrelativo;
        $valorizacion=ActaRetiro::create()->registrarRetencion($facturadorId,$proveedorId,$factura,$montoFactura
        ,$monedaId,$fechaFactura,$montoRetencion,$porcentajeRetencion,$tipoCambio2,$fechaPago,
        1,$usuarioId,$facturarDocumento,$comentarioEfact,$retencion);
       $retencionId=$valorizacion[0]['id'];

       
          $facturador=ActaRetiro::create()->actualizarCorrelativoRetenedor($facturadorId,$correlativoNuevo);
      
        return $valorizacion;
      }

    
      public function ExportarRetenciones()
      {
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
          ->mergeCells('B' . $i . ':N' . $i);
    
        //        $response
        // Agregar Informacion
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('B' . $i, 'Lista de Retenciones');
    
        $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':N' . $i)->applyFromArray($estiloTituloReporte);
        //        $objPHPExcel->getActiveSheet()->setSharedStyle($estiloTituloReporte, "A" . $i . ":C" . $i);
        $i += 2;
        //$j++;
        $j += 2;
    
        //Código	Descripción	Tipo Unidad	Control	Precio sugerido compra	Precio sugerido venta	Estado	Opciones
        $response = ActaRetiro::create()->getAllRetenciones2();
    
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('A' . $i, '      ');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('B' . $i, 'Fecha Retención');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('C' . $i, 'Emisor');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('D' . $i, 'Número Retención');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('E' . $i, 'Monto Retención');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('F' . $i, 'Porcentaje Retención');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('G' . $i, 'Proveedor');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('H' . $i, 'Factura');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('I' . $i, 'Monto Factura');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('J' . $i, 'Fecha Factura');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('K' . $i, 'Moneda Factura');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('L' . $i, 'Tipo Cambio');
        $objPHPExcel->setActiveSheetIndex()
          ->setCellValue('M' . $i, 'Comentario');
     
        $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':N' . $i)->applyFromArray($estiloTituloColumnas);
    
        //
        foreach ($response as $campo) {
          $objPHPExcel->setActiveSheetIndex()
            //                ->setCellValue('A' . $i, 'Lista de Bienes')
            ->setCellValue('B' . $j, $campo['fecha_retencion'])
            ->setCellValue('C' . $j, $campo['emisor'])
            ->setCellValue('D' . $j, $campo['retencion'])
            ->setCellValue('E' . $j, $campo['monto_retencion'])
            ->setCellValue('F' . $j, $campo['porcentaje_retencion'])
            ->setCellValue('G' . $j, $campo['proveedor'])
            ->setCellValue('H' . $j, $campo['factura'])
            ->setCellValue('I' . $j, $campo['monto_factura'])
            ->setCellValue('J' . $j, $campo['fecha_factura'])
            ->setCellValue('K' . $j, $campo['moneda_factura'])
            ->setCellValue('L' . $j, $campo['tipo_cambio'])
            ->setCellValue('M' . $j, $campo['comentario']);
            
          //            $objPHPExcel->getActiveSheet()->getStyle('B' . $j)->applyFromArray($estiloTituloColumnas);
          $i += 1;
          $j++;
          //        $objPHPExcel->setActiveSheetIndex()
          //                ->setCellValue('A' . $i, 'No Respondieron')
          //                ->setCellValue('B' . $i, 'dato2');
          //        $i +=1;
          //        $objPHPExcel->getActiveSheet()->getStyle('A' . ($i - 2) . ':A' . $i)->applyFromArray($estiloTituloColumnas);
          $objPHPExcel->getActiveSheet()->getStyle('B' . $i . ':M' . $i)->applyFromArray($estiloTxtInformacion);
          //        $objPHPExcel->getActiveSheet()->getStyle('I' . $i . ':L' . $i)->applyFromArray($estiloNumInformacion);
          //        $objPHPExcel->getActiveSheet()->getStyle('J' . $i . ':J' . $i)->applyFromArray($estiloTxtInformacion);
          //        $i +=1;
          //        $i +=2;
        }
    
    
        for ($i = 'A'; $i <= 'M'; $i++) {
          $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
        }
        // Renombrar Hoja
        $objPHPExcel->getActiveSheet()->setTitle('Retenciones');
    
        // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
        $objPHPExcel->setActiveSheetIndex(0);
    
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(__DIR__ . '/../../util/formatos/lista_de_retenciones.xlsx');
        return 1;
      }
}
