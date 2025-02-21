<?php
session_start();

require_once __DIR__ . '/../../modelo/almacen/Persona.php';
require_once __DIR__ . '/../../modelo/almacen/Invitacion.php';
require_once __DIR__ . '/../../modelo/almacen/ActaRetiro.php';
require_once __DIR__ . '/../../modelo/almacen/Vehiculo.php';
require_once __DIR__ . '/../../modelo/almacen/MatrizAprobacion.php';
require_once __DIR__ . '/../../modelo/almacen/Zona.php';
require_once __DIR__ . '/../../modelo/almacen/ConsultaWs.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/commons/ConstantesNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/SunatTablaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/CentroCostoNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/SolicitudRetiroNegocio.php';

class InvitacionNegocio extends ModeloNegocioBase
{

  /**
   *
   * @return InvitacionNegocio
   */
  static function create()
  {
    return parent::create();
  }


  public function obtenerDataREINFO($usuarioId,$ruc,$codigo)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar la busqueda");  
      }
    $data=[];
    $url= 'http://161.132.56.121:8000/reinfo_ruc/';
    $ch = curl_init();
    $endpointUrl = $url . urlencode($ruc);
    curl_setopt($ch, CURLOPT_URL, $endpointUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Límite de tiempo para la solicitud completa
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    $response = json_decode(curl_exec($ch));
    curl_close($ch);
    if ($response == null ) {
        throw new WarningException("No se encontro data para ese RUC y código unico.");  
    } 
    $filteredData = array_filter($response, function($item) use ($codigo) {
        return $item->codigo === $codigo;
    });   

    if (ObjectUtil::isEmpty($filteredData )) {
        return false; 
    } 
    $data = array_values($filteredData); 
    return $data ;
  }


  public function obtenerDataDNI($dni, $tipoDNI)
{
    if ($dni == null) {
        throw new WarningException("Escriba un DNI válido para realizar la busqueda");  
    }

    // Consulta al primer endpoint
    $url = 'http://161.132.56.121:8000/consulta_dni/'.$tipoDNI.'/'.$dni;
    $response = $this->realizarConsultaAPI($url);

    if ($response == null) {
        throw new WarningException("No se encontró data para ese DNI y tipo DNI.");  
    }

    // Consulta al segundo endpoint con reintentos en caso de error
    $url = 'http://161.132.56.121:8000/consulta_dnidb/'.$dni;
    $response2 = null;
    $intentos = 0;
    
    while ($intentos < 2) {
        $response2 = $this->realizarConsultaAPI($url);
        
        if ($response2 != null) {
            break; // Si la respuesta es válida, salimos del ciclo
        }

        $intentos++;
        // Esperamos un poco antes de reintentar
        sleep(2); // Tiempo de espera antes de volver a intentar
    }

    if ($response2 == null) {
        throw new WarningException("No se encontró data para ese DNI en el segundo servicio después de reintentar.");  
    }

    // Unimos ambos resultados
    $dataFinal = $this->unirDatos($response, $response2);

    return $dataFinal;
}


public function obtenerDataDNI2($dni, $tipoDNI)
{
    if ($dni == null) {
        throw new WarningException("Escriba un DNI válido para realizar la busqueda");  
    }
    if ($tipoDNI == null) {
      throw new WarningException("Es necesario seleccionar el tipo de DNI");  
  }
    $padron=Persona::create()->obtenerDocumentoIdentidadPadron($dni);
  
    if ($padron == null) {
      throw new WarningException("Este DNI no se encuentra dentro del PADRON");  
  }

    $response=null;
    // Consulta al segundo endpoint con reintentos en caso de error
    $url = 'http://161.132.56.121:8000/consulta_dnidb/'.$dni;
    $response2 = null;
    $intentos = 0;
    
    while ($intentos < 2) {
        $response2 = $this->realizarConsultaAPI($url);
        
        if ($response2 != null) {
            break; // Si la respuesta es válida, salimos del ciclo
        }

        $intentos++;
        // Esperamos un poco antes de reintentar
       
    }

    if ($response2 == null) {
        throw new WarningException("No se encontró data para ese DNI en el segundo servicio después de reintentar.");  
    }

    // Unimos ambos resultados
    $dataFinal = $this->unirDatos($response, $response2);

    return $dataFinal;
}
private function realizarConsultaAPI($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Límite de tiempo para la solicitud completa
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    $response = json_decode(curl_exec($ch));
    curl_close($ch);
    return $response;
}

private function unirDatos($response1, $response2)
{
    // Convertimos las claves de la respuesta a minúsculas para evitar problemas de mayúsculas
    $response2 = (object) array_change_key_case((array)$response2, CASE_LOWER);

    // Verificamos si el campo 'dni' existe en la respuesta del segundo servicio
    if (isset($response2->dni) && !empty($response2->dni)) {
        // El formato del campo 'dni' es '70370538 - 9', lo separamos en dos partes
        $dni_completo = $response2->dni;

        // Comprobamos que el formato contenga " - " para separarlo
        if (strpos($dni_completo, ' - ') !== false) {
            $dni_parts = explode(" - ", $dni_completo);
            $response2->dni = $dni_parts[0]; // Guardamos solo el número del DNI
            $response2->codigo_secreto = $dni_parts[1]; // Guardamos el código secreto
        } else {
            // Si no está en el formato correcto, lanzamos un error
            throw new WarningException("El formato del DNI recibido no es válido.");
        }
    } else {
        // Si no se encuentra el campo 'dni' o está vacío, lanzamos un error
        throw new WarningException("No se encontró el campo 'dni' en la respuesta del segundo servicio.");
    }

    // Unimos los datos de ambos endpoints (asegurándonos de que ambas respuestas sean arrays)
    $dataFinal = (object) array_merge((array)$response1, (array)$response2);

    return $dataFinal;
}

  public function obtenerDataTransportista($usuarioId,$ruc)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar la busqueda");  
      }
      $data=[];
      $url= 'http://161.132.56.121:8000/mercaderia_ruc/';
      $ch = curl_init();
      $endpointUrl = $url . urlencode($ruc);
      curl_setopt($ch, CURLOPT_URL, $endpointUrl);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Límite de tiempo para la solicitud completa
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
      $response2 = json_decode(curl_exec($ch));
      $response = json_decode($response2);
      curl_close($ch);
      if ($response == null ) {
          throw new WarningException("No se encontró data para este transportista con este RUC: $ruc.");  
      }
      $data = $response;
      $structuredData = [];

// Recorrer los datos y procesarlos
foreach ($data as $item) {
    if (isset($item->razon_social)) {
        // Dividir la razon_social en código y nombre
        list($codigo, $nombre) = explode('-', $item->razon_social, 2);

        // Eliminar espacios en blanco innecesarios
        $codigo = trim($codigo);
        $nombre = trim($nombre);

        // Añadir los datos divididos al array estructurado
        $structuredData['codigo'] = $codigo;
        $structuredData['nombre'] = $nombre;
    } elseif (isset($item->ruc)) {
        $structuredData['ruc'] = $item->ruc;
    } elseif (isset($item->direccion)) {
        $structuredData['direccion'] = $item->direccion;
    } elseif (isset($item->ciudad_inscrita)) {
        $structuredData['telefono'] = $item->ciudad_inscrita;
    } elseif (isset($item->tipo_personeria)) {
        $structuredData['ciudad'] = $item->tipo_personeria;
    } elseif (isset($item->modalidad_empresa)) {
        $structuredData['tipoPersona'] = $item->modalidad_empresa;
    } elseif (isset($item->estado)) {
        $structuredData['modalidad'] = $item->estado;
    } elseif (isset($item->vigente_hasta)) {
        $structuredData['estado'] = $item->vigente_hasta;
    }
}
    return $structuredData ;
  }

  public function obtenerDataPlanta($usuarioId,$ruc)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar la busqueda");  
      }
      try {
        $conexion = new SoapClient("http://44.194.84.229/ConsultaSunatWS/ConsultaSunatWS.asmx?WSDL");
        $parametros = new stdClass();
        $parametros->ruc = $ruc;
        $resultadoSap = $conexion->consultaRUC2((array) $parametros)->consultaRUC2Result;
        $respuesta = json_decode($resultadoSap);
        $arrayRespuesta = array();
        if ($respuesta->status == 'OK') {
          if ($respuesta->data != null) {
            foreach ($respuesta->data as $index => $item) {
              foreach ($item as $index => $itemString) {
                $arrayRespuesta = array_merge($arrayRespuesta, array(($itemString[0]) => ($itemString[1])));
              }
            }
          }
        }
      } catch (Exception $exc) {
      }
    return $arrayRespuesta;
  }
  

  public function guardarInvitacion($ruc,$codigo,
  $nombre,$sector,$estado ,$departamento,$provincia,$distrito,
  $ubigeo,$direccion,$telefono,$correo,$usuarioId,$zona,$organizacion)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar el registro");  
      }
      $ubicacion=$departamento.'-'.$provincia.'-'.$distrito;
      $persona = Persona::create()->obtenerPersonaXCodigoIdentificacion($ruc);
      if($persona==null){
      $persona = Persona::create()->insertPersona(4, $ruc, $nombre, null, null, $telefono, null, $correo, null, null, null, 1, $usuarioId, null, null, null, null, null, null, null,null,null,null,$zona);
      if($persona[0]['vout_exito']==0){
        throw new WarningException($persona[0]['vout_mensaje']);  
      }
      $personaId=$persona[0]['id'];
      Persona::create()->savePersonaClasePersona(26, $personaId, $usuarioId);
        Persona::create()->guardarPersonaDireccionMinapp($personaId, 
        1, $direccion, $usuarioId,-1, $ubigeo,$departamento,$provincia,
      $distrito);
    }
      else{
        $personaId=$persona[0]['id'];
        Persona::create()->guardarPersonaDireccionMinapp($personaId, 
        1, $direccion, $usuarioId,-1, $ubigeo,$departamento,$provincia,
      $distrito);  
      }
      $token = bin2hex(openssl_random_pseudo_bytes(16));
      $timestamp=time();
      $token = $token . $timestamp;
      $duracionSegundos = 5 * 24 * 60 * 60;
      $expiracion = $timestamp + $duracionSegundos;
      $expiracionFecha = date('Y-m-d H:i:s', $expiracion);
      $response = Invitacion::create()->insertInvitacion( $personaId, $codigo, $sector, $ubicacion, 1,0, $usuarioId,$token,$expiracion,$zona,$organizacion);
      if($response[0]['vout_exito']==0){
        throw new WarningException($response[0]['vout_mensaje']); 
      }
      else{
        $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "plantilla_invitacion_reinfo",
        "language": {
            "code": "es",
            "policy": "deterministic"
        },
        "components": [
            {
                "type": "header",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|title|]"
                    }
                ]
            },
            {
                "type": "button",
                "sub_type": "url",
                "index": 0,
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|code|]"
                    }
                ]
            }
        ]
    }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", $telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|code|]", $token, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
      }
      return $response;
    
  }


    public function guardarInvitacionTransportista($ruc,$codigo,$nombre,$direccion,$estado ,$modalidad,$telefono,$correo,$usuarioId)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar el registro");  
      }
      $persona = Persona::create()->obtenerPersonaXCodigoIdentificacion($ruc);
      if($persona==null){
      $persona = Persona::create()->insertPersona(4, $ruc, $nombre, null, null, $telefono, null, $correo, null, null, null, 1, $usuarioId, null, null, null, null, null, null, null,null,null,null);
      if($persona[0]['vout_exito']==0){
        throw new WarningException($persona[0]['vout_mensaje']);  
      }
      $personaId=$persona[0]['id'];
      Persona::create()->savePersonaClasePersona(23, $personaId, $usuarioId);
      }
      else{
        $personaId=$persona[0]['id'];
      }
      $token = bin2hex(openssl_random_pseudo_bytes(16));
      $timestamp=time();
      $token = $token . $timestamp;
      $duracionSegundos = 5 * 24 * 60 * 60;
      $expiracion = $timestamp + $duracionSegundos;
      $expiracionFecha = date('Y-m-d H:i:s', $expiracion);
      $response = Invitacion::create()->insertInvitacionTransportista( $personaId, $codigo, $direccion, $modalidad, 1,0, $usuarioId,$token,$expiracion);
      if($response[0]['vout_exito']==0){
        throw new WarningException($response[0]['vout_mensaje']); 
      }
      else{
        $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "plantilla_invitacion_transportista",
        "language": {
            "code": "es",
            "policy": "deterministic"
        },
        "components": [
            {
                "type": "header",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|title|]"
                    }
                ]
            },
            {
                "type": "button",
                "sub_type": "url",
                "index": 0,
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|code|]"
                    }
                ]
            }
        ]
    }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", $telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|code|]", $token, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
      }
      return $response;
    
  }


  public function guardarInvitacionPlanta($ruc,$fecha,$nombre,$direccion,$estado ,$modalidad,$telefono,$correo,$usuarioId)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar el registro");  
      }
      $persona = Persona::create()->obtenerPersonaXCodigoIdentificacion($ruc);
      if($persona==null){
      $persona = Persona::create()->insertPersona(4, $ruc, $nombre, null, null, $telefono, null, $correo, null, null, null, 1, $usuarioId, null, null, null, null, null, null, null,null,null,null);
      if($persona[0]['vout_exito']==0){
        throw new WarningException($persona[0]['vout_mensaje']);  
      }
      $personaId=$persona[0]['id'];
      Persona::create()->savePersonaClasePersona(25, $personaId, $usuarioId);
      }
      else{
        $personaId=$persona[0]['id'];
      }
      $token = bin2hex(openssl_random_pseudo_bytes(16));
      $timestamp=time();
      $token = $token . $timestamp;
      $duracionSegundos = 5 * 24 * 60 * 60;
      $expiracion = $timestamp + $duracionSegundos;
      $expiracionFecha = date('Y-m-d H:i:s', $expiracion);
      $response = Invitacion::create()->insertInvitacionPlanta( $personaId, $fecha, $direccion, $modalidad, 1,0, $usuarioId,$token,$expiracion);
      if($response[0]['vout_exito']==0){
        throw new WarningException($response[0]['vout_mensaje']); 
      }
      else{
        $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "plantilla_invitacion_planta",
        "language": {
            "code": "es",
            "policy": "deterministic"
        },
        "components": [
            {
                "type": "header",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|title|]"
                    }
                ]
            },
            {
                "type": "button",
                "sub_type": "url",
                "index": 0,
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|code|]"
                    }
                ]
            }
        ]
    }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", $telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|code|]", $token, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
      }
      return $response;
    
  }


   public function guardarInvitacionPrincipal($ruc,$nombre,$direccion ,$ubigeo,$telefono,$correo,$usuarioId)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar el registro");  
      }
      $persona = Persona::create()->obtenerPersonaXCodigoIdentificacion($ruc);
      if($persona==null){
      $persona = Persona::create()->insertPersona(4, $ruc, $nombre, null, null, $telefono, null, $correo, null, null, null, 1, $usuarioId, null, null, null, null, null, null, null,null,null,null);
      if($persona[0]['vout_exito']==0){
        throw new WarningException($persona[0]['vout_mensaje']);  
      }
      $personaId=$persona[0]['id'];
      Persona::create()->savePersonaClasePersona(30, $personaId, $usuarioId);
      }
      else{
        $personaId=$persona[0]['id'];
      }
      $token = bin2hex(openssl_random_pseudo_bytes(16));
      $timestamp=time();
      $token = $token . $timestamp;
      $duracionSegundos = 5 * 24 * 60 * 60;
      $expiracion = $timestamp + $duracionSegundos;
      $expiracionFecha = date('Y-m-d H:i:s', $expiracion);
      $response = Invitacion::create()->insertInvitacionPrincipal( $personaId, $direccion, $ubigeo, 1,0, $usuarioId,$token,$expiracion,1);
      if($response[0]['vout_exito']==0){
        throw new WarningException($response[0]['vout_mensaje']); 
      }
      else{
        $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "plantilla_invitacion_principal",
        "language": {
            "code": "es",
            "policy": "deterministic"
        },
        "components": [
            {
                "type": "header",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|title|]"
                    }
                ]
            },
            {
                "type": "button",
                "sub_type": "url",
                "index": 0,
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|code|]"
                    }
                ]
            }
        ]
    }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", $telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|code|]", $token, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
      }
      return $response;
    
  }

  public function guardarInvitacionSecundario($ruc,$nombre,$direccion ,$ubigeo,$telefono,$correo,$usuarioId)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar el registro");  
      }
      $persona = Persona::create()->obtenerPersonaXCodigoIdentificacion($ruc);
      if($persona==null){
      $persona = Persona::create()->insertPersona(4, $ruc, $nombre, null, null, $telefono, null, $correo, null, null, null, 1, $usuarioId, null, null, null, null, null, null, null,null,null,null);
      if($persona[0]['vout_exito']==0){
        throw new WarningException($persona[0]['vout_mensaje']);  
      }
      $personaId=$persona[0]['id'];
      Persona::create()->savePersonaClasePersona(30, $personaId, $usuarioId);
      }
      else{
        $personaId=$persona[0]['id'];
      }
      $token = bin2hex(openssl_random_pseudo_bytes(16));
      $timestamp=time();
      $token = $token . $timestamp;
      $duracionSegundos = 5 * 24 * 60 * 60;
      $expiracion = $timestamp + $duracionSegundos;
      $expiracionFecha = date('Y-m-d H:i:s', $expiracion);
      $response = Invitacion::create()->insertInvitacionSecundario( $personaId, $direccion, $ubigeo, 1,0, $usuarioId,$token,$expiracion,1);
      if($response[0]['vout_exito']==0){
        throw new WarningException($response[0]['vout_mensaje']); 
      }
      else{
        $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "plantilla_invitacion_principal",
        "language": {
            "code": "es",
            "policy": "deterministic"
        },
        "components": [
            {
                "type": "header",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|title|]"
                    }
                ]
            },
            {
                "type": "button",
                "sub_type": "url",
                "index": 0,
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|code|]"
                    }
                ]
            }
        ]
    }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", $telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|code|]", $token, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
      }
      return $response;
    
  }
  public function actualizarInvitacion($ruc,$codigo,$nombre,$sector,$estado ,$departamento,$provincia,$distrito,$ubigeo,$direccion,$telefono,$correo,$usuarioId,$invitacionId,$zona,$organizacion)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar el registro");  
      }
      $ubicacion=$departamento.'-'.$provincia.'-'.$distrito;
      $persona = Persona::create()->obtenerPersonaXCodigoIdentificacion($ruc);
      if($persona==null){
      $persona = Persona::create()->insertPersona(4, $ruc, $nombre, null, null, $telefono, null, $correo, null, null, null, $estado, $usuarioId);
      $personaId=$persona[0]['id'];
      Persona::create()->savePersonaClasePersona(26, $personaId, $usuarioId);
      Persona::create()->guardarPersonaDireccionMinapp($personaId, 
      1, $direccion, $usuarioId,-1, $ubigeo,$departamento,$provincia,
    $distrito);  
    }
      else{
        $personaId=$persona[0]['id'];
        Persona::create()->guardarPersonaDireccionMinapp($personaId, 
        1, $direccion, $usuarioId,-1, $ubigeo,$departamento,$provincia,
      $distrito);  
      }
      $token = bin2hex(openssl_random_pseudo_bytes(16));
      $timestamp=time();
      $token = $token . $timestamp;
      $duracionSegundos = 5 * 24 * 60 * 60;
      $expiracion = $timestamp + $duracionSegundos;
      $expiracionFecha = date('Y-m-d H:i:s', $expiracion);
      $response = Invitacion::create()->actualizarInvitacion( $personaId, $codigo, $sector, $ubicacion, 1,0, $usuarioId,$token,$expiracion,$invitacionId,$zona,$organizacion);
      if($response[0]['vout_exito']==1){
      $response = Invitacion::create()->actualizarPersona( $personaId,$telefono, $correo);}
      return $response;
    
  }

  public function actualizarInvitacionTransportista($ruc,$codigo,$nombre,$direccion,$estado ,$modalidad,$telefono,$correo,$usuarioId,$invitacionId)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar el registro");  
      }
      $persona = Persona::create()->obtenerPersonaXCodigoIdentificacion($ruc);
      if($persona==null){
      $persona = Persona::create()->insertPersona(4, $ruc, $nombre, null, null, $telefono, null, $correo, null, null, null, $estado, $usuarioId);
      $personaId=$persona[0]['id'];
      Persona::create()->savePersonaClasePersona(23, $personaId, $usuarioId);
      }
      else{
        $personaId=$persona[0]['id'];
      }
      $token = bin2hex(openssl_random_pseudo_bytes(16));
      $timestamp=time();
      $token = $token . $timestamp;
      $duracionSegundos = 5 * 24 * 60 * 60;
      $expiracion = $timestamp + $duracionSegundos;
      $expiracionFecha = date('Y-m-d H:i:s', $expiracion);
      $response = Invitacion::create()->actualizarInvitacionTransportista( $personaId, $codigo, $direccion, $modalidad, 1,0, $usuarioId,$token,$expiracion,$invitacionId);
      if($response[0]['vout_exito']==1){
      $response = Invitacion::create()->actualizarPersona( $personaId,$telefono, $correo);}
      return $response;
    
  }

  public function actualizarInvitacionPlanta($ruc,$fecha,$nombre,$direccion,$estado ,$modalidad,$telefono,$correo,$usuarioId,$invitacionId)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar el registro");  
      }
      $persona = Persona::create()->obtenerPersonaXCodigoIdentificacion($ruc);
      if($persona==null){
      $persona = Persona::create()->insertPersona(4, $ruc, $nombre, null, null, $telefono, null, $correo, null, null, null, $estado, $usuarioId);
      $personaId=$persona[0]['id'];
      Persona::create()->savePersonaClasePersona(25, $personaId, $usuarioId);
      }
      else{
        $personaId=$persona[0]['id'];
      }
      $token = bin2hex(openssl_random_pseudo_bytes(16));
      $timestamp=time();
      $token = $token . $timestamp;
      $duracionSegundos = 5 * 24 * 60 * 60;
      $expiracion = $timestamp + $duracionSegundos;
      $expiracionFecha = date('Y-m-d H:i:s', $expiracion);
      $response = Invitacion::create()->actualizarInvitacionPlantaEmpresa( $personaId, $fecha, $direccion, $modalidad, 1,0, $usuarioId,$token,$expiracion,$invitacionId);
      if($response[0]['vout_exito']==1){
      $response = Invitacion::create()->actualizarPersona( $personaId,$telefono, $correo);}
      return $response;
    
  }

  public function actualizarInvitacionPrincipal($ruc,$nombre,$direccion ,$ubigeo,$telefono,$correo,$usuarioId,$invitacionId)
  {
    if($ruc==null){
        throw new WarningException("Escriba un RUC válido para realizar el registro");  
      }
      $persona = Persona::create()->obtenerPersonaXCodigoIdentificacion($ruc);
      if($persona==null){
      $persona = Persona::create()->insertPersona(4, $ruc, $nombre, null, null, $telefono, null, $correo, null, null, null, 1, $usuarioId);
      $personaId=$persona[0]['id'];
      Persona::create()->savePersonaClasePersona(30, $personaId, $usuarioId);
      }
      else{
        $personaId=$persona[0]['id'];
      }
      $token = bin2hex(openssl_random_pseudo_bytes(16));
      $timestamp=time();
      $token = $token . $timestamp;
      $duracionSegundos = 5 * 24 * 60 * 60;
      $expiracion = $timestamp + $duracionSegundos;
      $expiracionFecha = date('Y-m-d H:i:s', $expiracion);
      $response = Invitacion::create()->actualizarInvitacionPrincipal( $personaId, $direccion, $ubigeo, 1,0, $usuarioId,$token,$expiracion,$invitacionId,1);
      if($response[0]['vout_exito']==1){
      $response = Invitacion::create()->actualizarPersona( $personaId,$telefono, $correo);}
      return $response;
    
  }

  public function getAllInvitaciones( $elemntosFiltrados, $columns, $order, $start, $usuarioId,$idPersona )
  {
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
   
    return Invitacion::create()->getAllInvitaciones($columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona);
  }

  public function getAllInvitacionesTransportista( $elemntosFiltrados, $columns, $order, $start, $usuarioId,$idPersona )
  {
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
   
    return Invitacion::create()->getAllInvitacionesTransportista($columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona);
  }

    public function getAllInvitacionesPlanta( $elemntosFiltrados, $columns, $order, $start, $usuarioId,$idPersona )
  {
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
   
    return Invitacion::create()->getAllInvitacionesPlanta($columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona);
  }

  public function getAllInvitacionesPrincipal( $elemntosFiltrados, $columns, $order, $start, $usuarioId,$idPersona,$tipo=null )
  {
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
   
    return Invitacion::create()->getAllInvitacionesPrincipal($columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona,$tipo);
  }

  public function listarInvitacion($usuarioId)
  {
      $documento = 275;
      $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
      $invitaciones = [];
  
      if ($matrizUsuario == null) {
          throw new WarningException("Usuario no tiene perfil de aprobador para preinscripciones.");
      }
  
      foreach ($matrizUsuario as $item) {
          $nivel = $item['nivel'];
  
          if ($nivel == 1) {
              $result = Invitacion::create()->obtenerInvitacionesNivel($nivel);
              if ($result !== null) {
                  $invitaciones = array_merge($invitaciones, $result);
              }
          } else if ($nivel == 2) {
              $plantaId = $item['persona_planta_id'];
              $result = Invitacion::create()->obtenerInvitacionesNivelPlanta($nivel, $plantaId);
              if ($result !== null) {
                  $invitaciones = array_merge($invitaciones, $result);
              }
          }
      }
  
      return $invitaciones;
  }

  public function listarInvitacionTransportista($usuarioId)
  {
      $documento = 277;
      $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
      $invitaciones = [];
  
      if ($matrizUsuario == null) {
          throw new WarningException("Usuario no tiene perfil de aprobador para preinscripciones.");
      }
  
      foreach ($matrizUsuario as $item) {
          $nivel = $item['nivel'];
  
          if ($nivel == 1) {
              $result = Invitacion::create()->obtenerInvitacionesNivelTransportista($nivel);
              if ($result !== null) {
                  $invitaciones = array_merge($invitaciones, $result);
              }
          } 
      }
  
      return $invitaciones;
  }


  public function listarInvitacionPlanta($usuarioId)
  {
      $documento = 278;
      $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
      $invitaciones = [];
  
      if ($matrizUsuario == null) {
          throw new WarningException("Usuario no tiene perfil de aprobador para preinscripciones.");
      }
  
      foreach ($matrizUsuario as $item) {
          $nivel = $item['nivel'];
  
          if ($nivel == 1) {
              $result = Invitacion::create()->obtenerInvitacionesNivelPlanta2($nivel);
              if ($result !== null) {
                  $invitaciones = array_merge($invitaciones, $result);
              }
          } 
      }
  
      return $invitaciones;
  }

  public function listarInvitacionAsociativa($usuarioId)
  {
      $documento = 279;
      $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
      $invitaciones = [];
  
      if ($matrizUsuario == null) {
          throw new WarningException("Usuario no tiene perfil de aprobador para preinscripciones usuarios asociativos.");
      }
  
      foreach ($matrizUsuario as $item) {
          $nivel = $item['nivel'];
  
          if ($nivel == 1) {
              $result = Invitacion::create()->obtenerInvitacionesNivelAsociativo($nivel);
              if ($result !== null) {
                  $invitaciones = array_merge($invitaciones, $result);
              }
          } else if ($nivel == 2) {
              $plantaId = $item['persona_planta_id'];
              $result = Invitacion::create()->obtenerInvitacionesNivelPlantaAsociativa($nivel, $plantaId);
              if ($result !== null) {
                  $invitaciones = array_merge($invitaciones, $result);
              }
          }
      }
  
      return $invitaciones;
  }
  public function getCantidadAllInvitaciones( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$idPersona)
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Invitacion::create()->getCantidadAllActasRetiro($columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona);
  }

  public function getCantidadAllInvitacionesTransportista( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$idPersona)
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Invitacion::create()->getCantidadAllActasRetiroTransportista($columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona);
  }

    public function getCantidadAllInvitacionesPlanta( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$idPersona)
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Invitacion::create()->getCantidadAllActasRetiroPlanta($columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona);
  }

  public function getCantidadAllInvitacionesPrincipal( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$idPersona,$tipo=null)
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return Invitacion::create()->getCantidadAllActasRetiroPrincipal($columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,$tipo);
  }

  public function obtenerConfiguracionesInvitacion( $usuarioId,$invitacionId)
  {
    $respuesta = new ObjectUtil();
    //$respuesta->empresa = EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    
    //        $respuesta->persona_clase = Persona::create()->getAllPersonaClaseByTipo($personaTipoId);

    //contactos
    $respuesta->invitacion = ($invitacionId > 0) ? Invitacion::create()->getInvitacionXId($invitacionId ) : null;

    return $respuesta;
  }

  public function obtenerConfiguracionesInvitacionTransportista( $usuarioId,$invitacionId)
  {
    $respuesta = new ObjectUtil();
    //$respuesta->empresa = EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    
    //        $respuesta->persona_clase = Persona::create()->getAllPersonaClaseByTipo($personaTipoId);

    //contactos
    $respuesta->invitacion = ($invitacionId > 0) ? Invitacion::create()->getInvitacionXIdTransportista($invitacionId ) : null;

    return $respuesta;
  }

    public function obtenerConfiguracionesInvitacionPlanta( $usuarioId,$invitacionId)
  {
    $respuesta = new ObjectUtil();
    //$respuesta->empresa = EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    
    //        $respuesta->persona_clase = Persona::create()->getAllPersonaClaseByTipo($personaTipoId);

    //contactos
    $respuesta->invitacion = ($invitacionId > 0) ? Invitacion::create()->getInvitacionXIdPlanta($invitacionId ) : null;

    return $respuesta;
  }


  public function obtenerConfiguracionesInvitacionPrincipal( $usuarioId,$invitacionId)
  {
    $respuesta = new ObjectUtil();
    //$respuesta->empresa = EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    
    //        $respuesta->persona_clase = Persona::create()->getAllPersonaClaseByTipo($personaTipoId);

    //contactos
    $respuesta->invitacion = ($invitacionId > 0) ? Invitacion::create()->getInvitacionXIdPrincipal($invitacionId ) : null;

    return $respuesta;
  }
  public function obtenerParametrosIniciales( $usuarioId,$parametros)
  { 
    $respuesta = new ObjectUtil();
    $partes = explode("=", $parametros);
    $token = $partes[1];

    $datos= Invitacion::create()->getInvitacionXToken($token );
    $timestamp_actual = time();

    $documento = 275;
    $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
    if($matrizUsuario==null){
    if($datos[0]['expiracion']<$timestamp_actual){
      throw new 
      WarningException("Ya expiró la fecha limite para registrar datos de la invitación.");  
    } 
    if($datos[0]['nivel']!=0){ 
      return 0;
    }     }
    //contactos
    $respuesta-> datos= $datos;
    $invitacionId=$datos[0]['id'];
    if($usuarioId==67){
    $respuesta-> plantas= Persona::create()->obtenerPersonasXClase(25); }
    else {
      if($datos[0]['nivel']==1){
    $matrizUsuario2 = Invitacion::create()->obtenerInvitacionesPlanta($usuarioId, $datos[0]['id']);
      }
      if($datos[0]['nivel']==2){
        $matrizUsuario2 = Invitacion::create()->obtenerInvitacionesPlantaMatriz($usuarioId, $datos[0]['id']);
      }
    $invitaciones = [];
    foreach ($matrizUsuario2 as $item) {
        $planta = $item['persona_planta_id'];

            $result = Persona::create()->obtenerPersonaXId($planta);
            if ($result !== null) {
                $invitaciones = array_merge($invitaciones, $result);
            }
        
    }
    $respuesta-> plantas=$invitaciones; 

    }
    return $respuesta;
  }

  public function obtenerParametrosInicialesTransportista( $usuarioId,$parametros)
  { 
    $respuesta = new ObjectUtil();
    $partes = explode("=", $parametros);
    $token = $partes[1];

    $datos= Invitacion::create()->getInvitacionXTokenTransportista($token );
    $timestamp_actual = time();

    $documento = 277;
    $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
    if($matrizUsuario==null){
    if($datos[0]['expiracion']<$timestamp_actual){
      throw new 
      WarningException("Ya expiró la fecha limite para registrar datos de la invitación.");  
    } 
    if($datos[0]['nivel']!=0){ 
      return 0;
    }     }
    //contactos
    $respuesta-> datos= $datos;
    $invitacionId=$datos[0]['id'];

    return $respuesta;
  }

  public function obtenerParametrosInicialesPlanta( $usuarioId,$parametros)
  { 
    $respuesta = new ObjectUtil();
    $partes = explode("=", $parametros);
    $token = $partes[1];

    $datos= Invitacion::create()->getInvitacionXTokenPlanta($token );
    $timestamp_actual = time();

    $documento = 278;
    $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
    if($matrizUsuario==null){
    if($datos[0]['expiracion']<$timestamp_actual){
      throw new 
      WarningException("Ya expiró la fecha limite para registrar datos de la invitación.");  
    } 
    if($datos[0]['nivel']!=0){ 
      return 0;
    }     }
    //contactos
    $respuesta-> datos= $datos;
    $invitacionId=$datos[0]['id'];

    return $respuesta;
  }



  public function obtenerParametrosInicialesPrincipal( $usuarioId,$parametros)
  { 
    $respuesta = new ObjectUtil();
    $partes = explode("=", $parametros);
    $token = $partes[1];

    $datos= Invitacion::create()->getInvitacionXTokenPrincipal($token );
    $timestamp_actual = time();

    $documento = 279;
    $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
    if($matrizUsuario==null){
    if($datos[0]['expiracion']<$timestamp_actual){
      throw new 
      WarningException("Ya expiró la fecha limite para registrar datos de la invitación.");  
    } 
    if($datos[0]['nivel']!=0){ 
      return 0;
    }     }
    //contactos
    $respuesta-> datos= $datos;
    $invitacionId=$datos[0]['id'];
    if($usuarioId==67){
    $respuesta-> plantas= Persona::create()->obtenerPersonasXClase(25); }
    else {
      if($datos[0]['nivel']==1){
    $matrizUsuario2 = Invitacion::create()->obtenerInvitacionesPlanta($usuarioId, $datos[0]['id']);
      }
      if($datos[0]['nivel']==2){
        $matrizUsuario2 = Invitacion::create()->obtenerInvitacionesPlantaMatriz($usuarioId, $datos[0]['id']);
      }
    $invitaciones = [];
    foreach ($matrizUsuario2 as $item) {
        $planta = $item['persona_planta_id'];

            $result = Persona::create()->obtenerPersonaXId($planta);
            if ($result !== null) {
                $invitaciones = array_merge($invitaciones, $result);
            }
        
    }
    $respuesta-> plantas=$invitaciones; 

    }
    return $respuesta;
  }

  public function obtenerDocumentosPlanta( $usuarioId,$persona,$planta)
  { 
    $respuesta = new ObjectUtil();

    //$respuesta->empresa = EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
    
    //        $respuesta->persona_clase = Persona::create()->getAllPersonaClaseByTipo($personaTipoId);

    //contactos
    
    $respuesta-> plantas= Invitacion::create()->obtenerDocumentosPlantaXPersona($persona,$planta);
    return $respuesta;
  }

  public function obtenerDocumentosAdministracion( $usuarioId,$persona)
  { 
    $respuesta = new ObjectUtil();
    
    $respuesta-> plantas= Invitacion::create()->obtenerDocumentosAdministracion($persona);
    return $respuesta;
  }

  public function obtenerDocumentosAdministracionTransportista( $usuarioId,$persona)
  { 
    $respuesta = new ObjectUtil();
    
    $respuesta-> plantas= Invitacion::create()->obtenerDocumentosAdministracionTransportista($persona);
    return $respuesta;
  }


  public function obtenerDocumentosAdministracionPlanta( $usuarioId,$persona)
  { 
    $respuesta = new ObjectUtil();
    
    $respuesta-> plantas= Invitacion::create()->obtenerDocumentosAdministracionPlanta($persona);
    return $respuesta;
  }


  
  public function obtenerDocumentosAdministracionAsociativo( $usuarioId,$persona)
  { 
    $respuesta = new ObjectUtil();
    
    $respuesta-> plantas= Invitacion::create()->obtenerDocumentosAdministracionAsociativo($persona);
    return $respuesta;
  }

  
  public function obtenerCoordenadas( $usuarioId,$persona)
  { 
    $respuesta = new ObjectUtil();
    
    $respuesta= Invitacion::create()->obtenerCoordenadas($persona,1);
    return $respuesta;
  }


  public function obtenerCoordenadasVehiculos( $usuarioId,$persona)
  { 
    $respuesta = new ObjectUtil();
    
    $respuesta= Invitacion::create()->obtenerCoordenadasVehiculos($persona,1);
    return $respuesta;
  }
  public function subirArchivo($id,$file,$tipo,$name,$persona,$usuarioId,$planta) {

    $extension = pathinfo($name, PATHINFO_EXTENSION);
    list($type, $imageData) = explode(';', $file);
        list(, $imageData) = explode(',', $imageData);
    
        // Decodificar los datos base64
        $imageData = base64_decode($imageData);
    
        // Crear un nombre único para la imagen
        $imageName = uniqid() .'.'. $extension;
    
        // Especificar la ruta donde se guardará la imagen
        $imagePath = '../../vistas/com/persona/documentos/' . $imageName;

        // Guardar la imagen en el servidor
        file_put_contents($imagePath, $imageData);
    
        $Archivo=Persona::create()->insertArchivo($usuarioId,$persona,$id,$imageName,$name );
    return $planta;
  }

  public function eliminarArchivo($id,$archivo,$tipo,$persona,$planta) {

    if($archivo==null){
    }
    else {
      $ruta='../../vistas/com/persona/documentos/'.$archivo;
     unlink($ruta);
    }
    $dataArchivos= Persona::create()->eliminarArchivos($persona);
    return $dataArchivos;
  }
  

  public function obtenerPlantasXPersona($persona, $usuarioId) {
    // Obtener los archivos de la persona
    $dataArchivosPersona = Persona::create()->obtenerArchivosA($persona);
    // Crear un array con los IDs de los archivos de la persona
    $archivosPersonaIds = array_column($dataArchivosPersona, 'archivo_id');
    
    // Obtener todas las plantas

    $documentoXAdministracion = Persona::create()->obtenerArchivosAdministracion();
    $archivosAdministracionIds = array_column($documentoXAdministracion, 'archivo_id');

    $todosArchivosPresentesA = !array_diff($archivosAdministracionIds, $archivosPersonaIds);

    if (!$todosArchivosPresentesA) {
      throw new 
      WarningException("Faltan cargar documentos Administrativos."); 
  
  }

    $plantas = Persona::create()->obtenerPersonasXClase(25);

    $plantasSeleccionadas = [];

    // Iterar sobre todas las plantas
    foreach ($plantas as $planta) {
        $plantaId = $planta['id'];
        // Obtener los archivos requeridos por la planta
        $documentoXPlanta = Persona::create()->obtenerArchivosPlantaA($plantaId);

        // Crear un array con los IDs de los archivos requeridos por la planta
        $archivosPlantaIds = array_column($documentoXPlanta, 'archivo_id');

        // Verificar si todos los archivos requeridos por la planta están en los archivos de la persona
        $todosArchivosPresentes = !array_diff($archivosPlantaIds, $archivosPersonaIds);

        if ($todosArchivosPresentes) {
            // Si todos los archivos requeridos están presentes, agregar la planta al array
            $plantasSeleccionadas[] = $planta;
        }
    }

    return $plantasSeleccionadas;
}

public function obtenerPlantasXPersonaXAsociativa($persona, $usuarioId) {
  // Obtener los archivos de la persona
  $dataArchivosPersona = Persona::create()->obtenerArchivosA($persona);
  // Crear un array con los IDs de los archivos de la persona
  $archivosPersonaIds = array_column($dataArchivosPersona, 'archivo_id');
  
  // Obtener todas las plantas

  $documentoXAdministracion = Persona::create()->obtenerArchivosAdministracionAsociativa();
  $archivosAdministracionIds = array_column($documentoXAdministracion, 'archivo_id');

  $todosArchivosPresentesA = !array_diff($archivosAdministracionIds, $archivosPersonaIds);

  if (!$todosArchivosPresentesA) {
    throw new 
    WarningException("Faltan cargar documentos Administrativos."); 

}

  $plantas = Persona::create()->obtenerPersonasXClase(25);

  $plantasSeleccionadas = [];

  // Iterar sobre todas las plantas
  foreach ($plantas as $planta) {
      $plantaId = $planta['id'];
      // Obtener los archivos requeridos por la planta
      $documentoXPlanta = Persona::create()->obtenerArchivosPlantaA($plantaId);

      // Crear un array con los IDs de los archivos requeridos por la planta
      $archivosPlantaIds = array_column($documentoXPlanta, 'archivo_id');

      // Verificar si todos los archivos requeridos por la planta están en los archivos de la persona
      $todosArchivosPresentes = !array_diff($archivosPlantaIds, $archivosPersonaIds);

      if ($todosArchivosPresentes) {
          // Si todos los archivos requeridos están presentes, agregar la planta al array
          $plantasSeleccionadas[] = $planta;
      }
  }

  return $plantasSeleccionadas;
}

public function guardarInvitacionConformidad($persona,$selectedItems,$file,$name,$usuarioId,$parametros,$coordenadas){
   if($coordenadas==null){
    throw new WarningException("Las coordenadas del IGAFOM no puede ir vacias."); 
   }
   if(count($coordenadas)<3){
    throw new WarningException("Las coordenadas del IGAFOM no pueden ser menos a 3."); 
   }
  if($selectedItems==null){
    throw new WarningException("No tienes archivos suficientes para postular a una planta."); 
  }
  

  if($parametros==null){
    throw new WarningException("Problemas con la sesión."); 
  } 
  else{

     $orden=1;
    foreach($coordenadas as $coordenada){
      $ubicacion=Persona::create()->registrarCoordenadasXPersona($persona,$coordenada['x'],$coordenada['y'],$orden,$usuarioId);
      $ordern=$orden+1;
    }

    $partes = explode("=", $parametros);
    $token = $partes[1];
    $invitacion=Invitacion::create()->getInvitacionXToken($token);
    $invitacionId=$invitacion[0]['id'];
    $edit=$invitacion[0]['edit'];
    if($edit==null){
      if($name==null){
        throw new WarningException("No se cargo la firma Digital, error al guardar.");  
      }
    $token=Invitacion::create()->actualizarInvitacionNivel($token,1); 

    $extension = pathinfo($name, PATHINFO_EXTENSION);
    list($type, $imageData) = explode(';', $file);
        list(, $imageData) = explode(',', $imageData);
    
        // Decodificar los datos base64
        $imageData = base64_decode($imageData);
    
        // Crear un nombre único para la imagen
        $imageName = uniqid() .'.'. $extension;
    
        // Especificar la ruta donde se guardará la imagen
        $imagePath = '../../vistas/com/persona/firmas/' . $imageName;

        // Guardar la imagen en el servidor
        file_put_contents($imagePath, $imageData);
  
      $archivo=Persona::create()->actualizarFirmaDigital($persona,$imageName);
  }
    else {
    $nivel=$invitacion[0]['nivel_rechazo'];
    $token=Invitacion::create()->actualizarInvitacionNivel($token,$nivel);  
    }
    
  }

  foreach ($selectedItems as $items) {
    $plantaId = $items;
    $plantaXPersona=Persona::create()->relacionarPlantaXPersona($persona,$plantaId,$invitacionId);
    }
    

    return $archivo;
}
public function guardarInvitacionConformidadAsociativa($persona,$selectedItems,$file,$name,$usuarioId,$parametros,$coordenadas){
  if($coordenadas==null){
   throw new WarningException("Las coordenadas del IGAFOM no puede ir vacias."); 
  }
  if(count($coordenadas)<3){
   throw new WarningException("Las coordenadas del IGAFOM no pueden ser menos a 3."); 
  }
 if($selectedItems==null){
   throw new WarningException("No tienes archivos suficientes para postular a una planta."); 
 }
 

 if($parametros==null){
   throw new WarningException("Problemas con la sesión."); 
 } 
 else{

    $orden=1;
   foreach($coordenadas as $coordenada){
     $ubicacion=Persona::create()->registrarCoordenadasXPersona($persona,$coordenada['x'],$coordenada['y'],$orden,$usuarioId);
     $ordern=$orden+1;
   }

   $partes = explode("=", $parametros);
   $token = $partes[1];
   $invitacion=Invitacion::create()->getInvitacionXTokenPrincipal($token);
   $invitacionId=$invitacion[0]['id'];
   $edit=$invitacion[0]['edit'];
   if($edit==null){
     if($name==null){
       throw new WarningException("No se cargo la firma Digital, error al guardar.");  
     }
   $token=Invitacion::create()->actualizarInvitacionNivelAsociativa($token,1); 

   $extension = pathinfo($name, PATHINFO_EXTENSION);
   list($type, $imageData) = explode(';', $file);
       list(, $imageData) = explode(',', $imageData);
   
       // Decodificar los datos base64
       $imageData = base64_decode($imageData);
   
       // Crear un nombre único para la imagen
       $imageName = uniqid() .'.'. $extension;
   
       // Especificar la ruta donde se guardará la imagen
       $imagePath = '../../vistas/com/persona/firmas/' . $imageName;

       // Guardar la imagen en el servidor
       file_put_contents($imagePath, $imageData);
 
     $archivo=Persona::create()->actualizarFirmaDigital($persona,$imageName);
 }
   else {
   $nivel=$invitacion[0]['nivel_rechazo'];
   $token=Invitacion::create()->actualizarInvitacionNivelAsociativa($token,$nivel);  
   }
   
 }

 foreach ($selectedItems as $items) {
   $plantaId = $items;
   $plantaXPersona=Persona::create()->relacionarPlantaXPersona($persona,$plantaId,$invitacionId);
   }
   

   return $archivo;
}
public function guardarInvitacionTransportistaC($persona,$file,$name,$usuarioId,$parametros,$coordenadas){
  if($coordenadas==null){
   throw new WarningException("Las coordenadas del IGAFOM no puede ir vacias."); 
  }
  if(count($coordenadas)<1){
   throw new WarningException("Las coordenadas del IGAFOM no pueden ser menos a 3."); 
  } 

 if($parametros==null){
   throw new WarningException("Problemas con la sesión."); 
 }

 $dataArchivosPersona = Persona::create()->obtenerArchivosA($persona);

 $archivosPersonaIds = array_column($dataArchivosPersona, 'archivo_id');
 
 // Obtener todas las plantas

 $documentoXAdministracion = Persona::create()->obtenerArchivosAdministracionT();
 $archivosAdministracionIds = array_column($documentoXAdministracion, 'archivo_id');

 $todosArchivosPresentesA = !array_diff($archivosAdministracionIds, $archivosPersonaIds);

 if (!$todosArchivosPresentesA) {
   throw new 
   WarningException("Faltan cargar documentos Administrativos."); 

} 
 else{
   foreach($coordenadas as $coordenada){
     $ubicacion=Vehiculo::create()->guardarVehiculo(null,$coordenada['placa'],$coordenada['carga'],null,null,$coordenada['marca']
     ,$coordenada['modelo'],1,$usuarioId,$persona); 
   }

   $partes = explode("=", $parametros);
   $token = $partes[1];
   $invitacion=Invitacion::create()->getInvitacionXTokenTransportista($token);
   $invitacionId=$invitacion[0]['id'];
   $edit=$invitacion[0]['edit'];
   if($edit==null){
     if($name==null){
       throw new WarningException("No se cargo la firma Digital, error al guardar.");  
     }
   $token=Invitacion::create()->actualizarInvitacionNivelTransportista($token,1); 

   $extension = pathinfo($name, PATHINFO_EXTENSION);
   list($type, $imageData) = explode(';', $file);
       list(, $imageData) = explode(',', $imageData);
   
       // Decodificar los datos base64
       $imageData = base64_decode($imageData);
   
       // Crear un nombre único para la imagen
       $imageName = uniqid() .'.'. $extension;
   
       // Especificar la ruta donde se guardará la imagen
       $imagePath = '../../vistas/com/persona/firmas/' . $imageName;

       // Guardar la imagen en el servidor
       file_put_contents($imagePath, $imageData);
 
     $archivo=Persona::create()->actualizarFirmaDigital($persona,$imageName);
 }
   else {
   $nivel=$invitacion[0]['nivel_rechazo'];
   $token=Invitacion::create()->actualizarInvitacionNivelTransportista($token,$nivel);  
   }
   
 }
   return $archivo;
}


public function guardarInvitacionPlantaC($persona,$file,$name,$usuarioId,$parametros){

 if($parametros==null){
   throw new WarningException("Problemas con la sesión."); 
 }

 $dataArchivosPersona = Persona::create()->obtenerArchivosA($persona);

 $archivosPersonaIds = array_column($dataArchivosPersona, 'archivo_id');
 
 // Obtener todas las plantas

 $documentoXAdministracion = Persona::create()->obtenerArchivosAdministracionP();
 $archivosAdministracionIds = array_column($documentoXAdministracion, 'archivo_id');

 $todosArchivosPresentesA = !array_diff($archivosAdministracionIds, $archivosPersonaIds);

 if (!$todosArchivosPresentesA) {
   throw new 
   WarningException("Faltan cargar documentos Administrativos."); 

} 
 else{
 
   $partes = explode("=", $parametros);
   $token = $partes[1];
   $invitacion=Invitacion::create()->getInvitacionXTokenPlanta($token);
   $invitacionId=$invitacion[0]['id'];
   $edit=$invitacion[0]['edit'];
   if($edit==null){
     if($name==null){
       throw new WarningException("No se cargo la firma Digital, error al guardar.");  
     }
   $token=Invitacion::create()->actualizarInvitacionNivelPlanta($token,1); 

   $extension = pathinfo($name, PATHINFO_EXTENSION);
   list($type, $imageData) = explode(';', $file);
       list(, $imageData) = explode(',', $imageData);
   
       // Decodificar los datos base64
       $imageData = base64_decode($imageData);
   
       // Crear un nombre único para la imagen
       $imageName = uniqid() .'.'. $extension;
   
       // Especificar la ruta donde se guardará la imagen
       $imagePath = '../../vistas/com/persona/firmas/' . $imageName;

       // Guardar la imagen en el servidor
       file_put_contents($imagePath, $imageData);
 
     $archivo=Persona::create()->actualizarFirmaDigital($persona,$imageName);
 }
   else {
   $nivel=$invitacion[0]['nivel_rechazo'];
   $token=Invitacion::create()->actualizarInvitacionNivelPlanta($token,$nivel);  
   }
   
 }
   return $archivo;
}



public function deleteSolicitud($id, $usuarioSesion, $estado){
  $invitacion=Invitacion::create()->getInvitacionXId($id);
  $delete=Invitacion::create()->eliminarInvitacion($id,$estado);
  
  $telefono=$invitacion[0]['telefono'];
  $nombre=$invitacion[0]['nombre'];

  $bodyNotificacion = '
  {
      "messaging_product": "whatsapp",
      "to": "[|phone|]",
      "type": "template",
      "template": {
          "name": "cancelar_invitacion_reinfo",
          "language": {
              "code": "es",
              "policy": "deterministic"
          },
          "components": [
              {
                  "type": "header",
                  "parameters": [
                      {
                          "type": "text",
                          "text": "[|title|]"
                      }
                  ]
              }
                       
          ]
      }
  }
  ';
  
  // Reemplaza las variables en el JSON
  $bodyNotificacion = str_replace("[|phone|]",'51'.$telefono, $bodyNotificacion);
  $bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
  SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
  

  return $delete;

}


public function deleteSolicitudTransportista($id, $usuarioSesion, $estado){
  $invitacion=Invitacion::create()->getInvitacionXIdTransportista($id);
  $delete=Invitacion::create()->eliminarInvitacionTransportista($id,$estado);
  
  $telefono=$invitacion[0]['telefono'];
  $nombre=$invitacion[0]['nombre'];

  $bodyNotificacion = '
  {
      "messaging_product": "whatsapp",
      "to": "[|phone|]",
      "type": "template",
      "template": {
          "name": "cancelar_invitacion_reinfo",
          "language": {
              "code": "es",
              "policy": "deterministic"
          },
          "components": [
              {
                  "type": "header",
                  "parameters": [
                      {
                          "type": "text",
                          "text": "[|title|]"
                      }
                  ]
              }
                       
          ]
      }
  }
  ';
  
  // Reemplaza las variables en el JSON
  $bodyNotificacion = str_replace("[|phone|]",'51'.$telefono, $bodyNotificacion);
  $bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
  SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
  

  return $delete;

}


public function deleteSolicitudPlanta($id, $usuarioSesion, $estado){
  $invitacion=Invitacion::create()->getInvitacionXIdPlanta($id);
  $delete=Invitacion::create()->eliminarInvitacionPlanta($id,$estado);
  
  $telefono=$invitacion[0]['telefono'];
  $nombre=$invitacion[0]['nombre'];

  $bodyNotificacion = '
  {
      "messaging_product": "whatsapp",
      "to": "[|phone|]",
      "type": "template",
      "template": {
          "name": "cancelar_invitacion_reinfo",
          "language": {
              "code": "es",
              "policy": "deterministic"
          },
          "components": [
              {
                  "type": "header",
                  "parameters": [
                      {
                          "type": "text",
                          "text": "[|title|]"
                      }
                  ]
              }
                       
          ]
      }
  }
  ';
  
  // Reemplaza las variables en el JSON
  $bodyNotificacion = str_replace("[|phone|]",'51'.$telefono, $bodyNotificacion);
  $bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
  SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
  

  return $delete;

}

public function deleteSolicitudPrincipal($id, $usuarioSesion, $estado){
  $invitacion=Invitacion::create()->getInvitacionXIdPrincipal($id);
  $delete=Invitacion::create()->eliminarInvitacionPrincipal($id,$estado);
  
  $telefono=$invitacion[0]['telefono'];
  $nombre=$invitacion[0]['nombre'];

  $bodyNotificacion = '
  {
      "messaging_product": "whatsapp",
      "to": "[|phone|]",
      "type": "template",
      "template": {
          "name": "cancelar_invitacion_reinfo",
          "language": {
              "code": "es",
              "policy": "deterministic"
          },
          "components": [
              {
                  "type": "header",
                  "parameters": [
                      {
                          "type": "text",
                          "text": "[|title|]"
                      }
                  ]
              }
                       
          ]
      }
  }
  ';
  
  // Reemplaza las variables en el JSON
  $bodyNotificacion = str_replace("[|phone|]",'51'.$telefono, $bodyNotificacion);
  $bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
  SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
  

  return $delete;

}


public function finalizarAprobacion( $usuarioId,$nivel,$invitacionId){
  $documento=275;
  $validacion=MatrizAprobacion::create()->getMatrizXUsuarioXDocumentoPlantas($usuarioId,$documento,$nivel);
  if ($validacion == null) {
    throw new WarningException("Ya se aprobo esa invitación, este usuario no tiene permisos para seguir aprobando.");
  }
  $invitacion=Invitacion::create()->getInvitacionXId($invitacionId );
  $concluido=$invitacion[0]['culminado'];

  
  


  if($nivel==2){
    $plantaId=$validacion[0]['persona_planta_id'];
    $validacion2=MatrizAprobacion::create()->obtenerConformidadInvitacionPlanta( $plantaId,$invitacionId);

    if($validacion2[0]['confirmacion']==1){
      throw new WarningException("Ya se aprobo esa invitación, este usuario no tiene permisos para seguir aprobando.");
    }
    $nivel=$nivel+1;
  }

  if($concluido!=1 && $nivel==1){  
    $nivel=$nivel+1;}

    if($nivel==3){
      $invitacion=Invitacion::create()->getInvitacionXId($invitacionId );
      $personaId=$invitacion[0]['persona_id'];
      $persona=Persona::create()->obtenerPersonaXId($personaId);
      
      // $documento=275;
      $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);

      if ($matrizUsuario == null) {
          throw new WarningException("Usuario no tiene perfil de aprobador planta.");
      }
  
      foreach ($matrizUsuario as $item) {
          $nivelP = $item['nivel'];
           if ($nivelP == 2) {
              $plantaId = $item['persona_planta_id'];
              $result = Invitacion::create()->actualizarInvitacionPlanta($invitacionId, $plantaId);
          }
      }
      $actualizar2=Invitacion::create()->culminarAprobacion($invitacionId);
      $concluido=$invitacion[0]['culminado'];
      $actualizar=Invitacion::create()->finalizarAprobacion($usuarioId,$nivel,$invitacionId);
      if($concluido!=1){
      $usuario_nombre=$persona[0]['codigo_identificacion'];
      $clave_generada = Util::generateCode();
      $clave = Util::encripta($clave_generada);
      $usuario=Usuario::create()->insertUsuario($usuario_nombre, $personaId, $usuarioId, 1, $clave); 
      $registroPerfilUsuario=Usuario::create()->insertDetUsuarioPerfil($usuario[0]['id'], 136, 2, $usuarioId, 1);

      $invitacion=Invitacion::create()->getInvitacionXId($invitacionId);

    
      $telefono=$invitacion[0]['telefono'];
      $nombre=$invitacion[0]['nombre'];
      $bodyNotificacion = '
  {
      "messaging_product": "whatsapp",
      "to": "[|phone|]",
      "type": "template",
      "template": {
          "name": "plantilla_credenciales_usuario",
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
                          "text": "[|usuario|]"
                      },
                                          {
                          "type": "text",
                          "text": "[|clave|]"
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
  $bodyNotificacion = str_replace("[|usuario|]", $usuario_nombre, $bodyNotificacion);
  $bodyNotificacion = str_replace("[|clave|]", $clave_generada, $bodyNotificacion);
  SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
     }
      
     }
     else{
      $actualizar=Invitacion::create()->finalizarAprobacion($usuarioId,$nivel,$invitacionId);
     }

  return $actualizar;

}

public function finalizarAprobacionAsociativo( $usuarioId,$nivel,$invitacionId){
  $documento=279;
  $validacion=MatrizAprobacion::create()->getMatrizXUsuarioXDocumentoPlantas($usuarioId,$documento,$nivel);
  if ($validacion == null) {
    throw new WarningException("Ya se aprobo esa invitación, este usuario no tiene permisos para seguir aprobando.");
  }
  $invitacion=Invitacion::create()->getInvitacionXIdPrincipal($invitacionId );
  $concluido=$invitacion[0]['culminado'];

  



  if($nivel==2){
    $plantaId=$validacion[0]['persona_planta_id'];
    $validacion2=MatrizAprobacion::create()->obtenerConformidadInvitacionPlanta( $plantaId,$invitacionId);

    if($validacion2[0]['confirmacion']==1){
      throw new WarningException("Ya se aprobo esa invitación, este usuario no tiene permisos para seguir aprobando.");
    }
    $nivel=$nivel+1;
  }

  

    if($nivel==3){
      $invitacion=Invitacion::create()->getInvitacionXIdPrincipal($invitacionId );
      $personaId=$invitacion[0]['persona_id'];
      $persona=Persona::create()->obtenerPersonaXId($personaId);
      
      // $documento=275;
      $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);

      if ($matrizUsuario == null) {
          throw new WarningException("Usuario no tiene perfil de aprobador planta.");
      }
  
      foreach ($matrizUsuario as $item) {
          $nivel = $item['nivel'];
           if ($nivel == 2) {
              $plantaId = $item['persona_planta_id'];
              $result = Invitacion::create()->actualizarInvitacionPlanta($invitacionId, $plantaId);
          }
      }
      $actualizar=Invitacion::create()->culminarAprobacionAsociativa($invitacionId);
      $concluido=$invitacion[0]['culminado'];
      
      if($concluido!=1){
      $usuario_nombre=$persona[0]['codigo_identificacion'];
      $clave_generada = Util::generateCode();
      $clave = Util::encripta($clave_generada);
      $usuario=Usuario::create()->insertUsuario($usuario_nombre, $personaId, $usuarioId, 1, $clave); 
      $registroPerfilUsuario=Usuario::create()->insertDetUsuarioPerfil($usuario[0]['id'], 141, 2, $usuarioId, 1);

      $invitacion=Invitacion::create()->getInvitacionXIdPrincipal($invitacionId);

    
      $telefono=$invitacion[0]['telefono'];
      $nombre=$invitacion[0]['nombre'];
      $bodyNotificacion = '
  {
      "messaging_product": "whatsapp",
      "to": "[|phone|]",
      "type": "template",
      "template": {
          "name": "plantilla_credenciales_usuario",
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
                          "text": "[|usuario|]"
                      },
                                          {
                          "type": "text",
                          "text": "[|clave|]"
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
  $bodyNotificacion = str_replace("[|usuario|]", $usuario_nombre, $bodyNotificacion);
  $bodyNotificacion = str_replace("[|clave|]", $clave_generada, $bodyNotificacion);
  SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
     }
      
     }
     else{
       if($concluido!=1 && $nivel==1){  
     $nivel=$nivel+1;}
      $actualizar=Invitacion::create()->finalizarAprobacionAsociativa($usuarioId,$nivel,$invitacionId);
     }

  return $actualizar;

}
public function finalizarAprobacionTransportista( $usuarioId,$invitacionId){
  $documento=277;
  $nivel=1;
  $validacion=MatrizAprobacion::create()->getMatrizXUsuarioXDocumentoPlantas($usuarioId,$documento,$nivel);
  if ($validacion == null) {
    throw new WarningException("Ya se aprobo esa invitación, este usuario no tiene permisos para seguir aprobando.");
  }
  $invitacion=Invitacion::create()->getInvitacionXIdTransportista($invitacionId );
  $concluido=$invitacion[0]['culminado'];
 

  if($concluido!=1){  
  $nivel=$nivel+1;}


      $invitacion=Invitacion::create()->getInvitacionXIdTransportista($invitacionId );
      $personaId=$invitacion[0]['persona_id'];
      $persona=Persona::create()->obtenerPersonaXId($personaId);
      
      // $documento=275;
      $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);

      if ($matrizUsuario == null) {
          throw new WarningException("Usuario no tiene perfil de aprobador .");
      }
  

      $actualizar=Invitacion::create()->culminarAprobacionTransportista($invitacionId);
      $concluido=$invitacion[0]['culminado'];
      
      if($concluido!=1){
      $usuario_nombre=$persona[0]['codigo_identificacion'];
      $clave_generada = Util::generateCode();
      $clave = Util::encripta($clave_generada);
      $usuario=Usuario::create()->insertUsuario($usuario_nombre, $personaId, $usuarioId, 1, $clave); 
      if ($usuario[0]['vout_exito'] == '0') {
        throw new WarningException($usuario[0]['vout_mensaje']);
    }
      $registroPerfilUsuario=Usuario::create()->insertDetUsuarioPerfil($usuario[0]['id'], 140, 2, $usuarioId, 1);

      $invitacion=Invitacion::create()->getInvitacionXIdTransportista($invitacionId);

    
      $telefono=$invitacion[0]['telefono'];
      $nombre=$invitacion[0]['nombre'];
      $bodyNotificacion = '
  {
      "messaging_product": "whatsapp",
      "to": "[|phone|]",
      "type": "template",
      "template": {
          "name": "plantilla_credenciales_usuario",
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
                          "text": "[|usuario|]"
                      },
                                          {
                          "type": "text",
                          "text": "[|clave|]"
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
  $bodyNotificacion = str_replace("[|usuario|]", $usuario_nombre, $bodyNotificacion);
  $bodyNotificacion = str_replace("[|clave|]", $clave_generada, $bodyNotificacion);
  SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
     }
      
     $actualizar=Invitacion::create()->finalizarAprobacionTransportista($usuarioId,$nivel,$invitacionId);

  return $actualizar;

}


public function finalizarAprobacionPlanta( $usuarioId,$invitacionId){
  $documento=278;
  $nivel=1;
  $validacion=MatrizAprobacion::create()->getMatrizXUsuarioXDocumentoPlantas($usuarioId,$documento,$nivel);
  if ($validacion == null) {
    throw new WarningException("Ya se aprobo esa invitación, este usuario no tiene permisos para seguir aprobando.");
  }
  $invitacion=Invitacion::create()->getInvitacionXIdPlanta($invitacionId );
  $concluido=$invitacion[0]['culminado'];
 

  if($concluido!=1){  
  $nivel=$nivel+1;}


      $invitacion=Invitacion::create()->getInvitacionXIdPlanta($invitacionId );
      $personaId=$invitacion[0]['persona_id'];
      $persona=Persona::create()->obtenerPersonaXId($personaId);
      
      // $documento=275;
      $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);

      if ($matrizUsuario == null) {
          throw new WarningException("Usuario no tiene perfil de aprobador .");
      }
  

      $actualizar=Invitacion::create()->culminarAprobacionPlanta($invitacionId);
      $concluido=$invitacion[0]['culminado'];
      
      if($concluido!=1){
      $usuario_nombre=$persona[0]['codigo_identificacion'];
      $clave_generada = Util::generateCode();
      $clave = Util::encripta($clave_generada);
      $usuario=Usuario::create()->insertUsuario($usuario_nombre, $personaId, $usuarioId, 1, $clave); 
      if ($usuario[0]['vout_exito'] == '0') {
        throw new WarningException($usuario[0]['vout_mensaje']);
    }
      $registroPerfilUsuario=Usuario::create()->insertDetUsuarioPerfil($usuario[0]['id'], 134, 2, $usuarioId, 1);
      $registroPerfilUsuario2=Usuario::create()->insertDetUsuarioPerfil($usuario[0]['id'], 139, 2, $usuarioId, 1);
      $invitacion=Invitacion::create()->getInvitacionXIdPlanta($invitacionId);

    
      $telefono=$invitacion[0]['telefono'];
      $nombre=$invitacion[0]['nombre'];
      $bodyNotificacion = '
  {
      "messaging_product": "whatsapp",
      "to": "[|phone|]",
      "type": "template",
      "template": {
          "name": "plantilla_credenciales_usuario",
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
                          "text": "[|usuario|]"
                      },
                                          {
                          "type": "text",
                          "text": "[|clave|]"
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
  $bodyNotificacion = str_replace("[|usuario|]", $usuario_nombre, $bodyNotificacion);
  $bodyNotificacion = str_replace("[|clave|]", $clave_generada, $bodyNotificacion);
  SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
     }
      
     $actualizar=Invitacion::create()->finalizarAprobacionPlanta($usuarioId,$nivel,$invitacionId);

  return $actualizar;

}
public function finalizarRechazo( $usuarioId,$nivel,$invitacionId,$comentario){

  $nivel=$nivel+1;
  $tipo=2;
  $invitacion=Invitacion::create()->getInvitacionXId($invitacionId);
  if($nivel==3){
    $area='Planta';
    $documento = 275;
    $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
    $invitaciones = [];

    if ($matrizUsuario == null) {
        throw new WarningException("Usuario no tiene perfil de aprobador para preinscripciones.");
    }

    foreach ($matrizUsuario as $item) {
      $nivel = $item['nivel'];
       if ($nivel == 2) {
          $plantaId = $item['persona_planta_id'];
          $result = Invitacion::create()->actualizarInvitacionPlantaRechazar($invitacionId, $plantaId);
      }
    }
    $resultado = Invitacion::create()->obtenerPostulacionPlantasXSolicitudId($invitacionId);
    $cantidad=$resultado[0]['cantidad'];
    if($cantidad==0){
    $delate=Invitacion::create()->eliminarInvitacion($invitacionId,2);
    if($delate[0]['vout_exito']==1){
      Invitacion::create()->registrarLogInvitacion($invitacionId,'Rechazado por planta',$comentario,$usuarioId,$tipo);
    } 
    $mensajeWsp='Valido';
  }
   }


  else{
    $area='Administración';
    $delate=Invitacion::create()->eliminarInvitacion($invitacionId,2);
    if($delate[0]['vout_exito']==1){
      Invitacion::create()->registrarLogInvitacion($invitacionId,'Rechazado por administración',$comentario,$usuarioId,$tipo);
    } 
    $mensajeWsp='Valido';
  }

  if($mensajeWsp=='Valido'){
    

    
    $telefono=$invitacion[0]['telefono'];
    $nombre=$invitacion[0]['nombre'];
    $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "rechazo_definitivo",
        "language": {
            "code": "es",
            "policy": "deterministic"
        },
        "components": [
            {
                "type": "header",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|title|]"
                    }
                ]
            },
                {
                "type": "body",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|area|]"
                    },
                                        {
                        "type": "text",
                        "text": "[|comentario|]"
                    }
                ]
            }

        ]
    }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", '51'.$telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|area|]", $area, $bodyNotificacion);
$bodyNotificacion = str_replace("[|comentario|]", $comentario, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
  }
  
return $delate;

}
public function finalizarRechazoAsociativo( $usuarioId,$nivel,$invitacionId,$comentario){

  $nivel=$nivel+1;
  $tipo=2;
  $invitacion=Invitacion::create()->getInvitacionXIdPrincipal($invitacionId);
  if($nivel==3){
    $area='Planta';
    $documento = 279;
    $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
    $invitaciones = [];

    if ($matrizUsuario == null) {
        throw new WarningException("Usuario no tiene perfil de aprobador para preinscripciones.");
    }

    foreach ($matrizUsuario as $item) {
      $nivel = $item['nivel'];
       if ($nivel == 2) {
          $plantaId = $item['persona_planta_id'];
          $result = Invitacion::create()->actualizarInvitacionPlantaRechazar($invitacionId, $plantaId);
      }
    }
    $resultado = Invitacion::create()->obtenerPostulacionPlantasXSolicitudId($invitacionId);
    $cantidad=$resultado[0]['cantidad'];
    if($cantidad==0){
    $delate=Invitacion::create()->eliminarInvitacionAsociativo($invitacionId,2);
    if($delate[0]['vout_exito']==1){
      // Invitacion::create()->registrarLogInvitacion($invitacionId,'Rechazado por planta',$comentario,$usuarioId,$tipo);
    } 
    $mensajeWsp='Valido';
  }
   }


  else{
    $area='Administración';
    $delate=Invitacion::create()->eliminarInvitacionAsociativo($invitacionId,2);
    if($delate[0]['vout_exito']==1){
      // Invitacion::create()->registrarLogInvitacion($invitacionId,'Rechazado por administración',$comentario,$usuarioId,$tipo);
    } 
    $mensajeWsp='Valido';
  }

  if($mensajeWsp=='Valido'){
    

    
    $telefono=$invitacion[0]['telefono'];
    $nombre=$invitacion[0]['nombre'];
    $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "rechazo_definitivo",
        "language": {
            "code": "es",
            "policy": "deterministic"
        },
        "components": [
            {
                "type": "header",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|title|]"
                    }
                ]
            },
                {
                "type": "body",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|area|]"
                    },
                                        {
                        "type": "text",
                        "text": "[|comentario|]"
                    }
                ]
            }

        ]
    }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", '51'.$telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|area|]", $area, $bodyNotificacion);
$bodyNotificacion = str_replace("[|comentario|]", $comentario, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
  }
  
return $delate;

}

public function finalizarRechazoTransportista( $usuarioId,$invitacionId,$comentario){


  $tipo=2;
  $invitacion=Invitacion::create()->getInvitacionXIdTransportista($invitacionId);
 
    $area='Administración';
    $delate=Invitacion::create()->eliminarInvitacionTransportista($invitacionId,2);
    if($delate[0]['vout_exito']==1){
      
    } 
    $telefono=$invitacion[0]['telefono'];
    $nombre=$invitacion[0]['nombre'];
    $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "rechazo_definitivo",
        "language": {
            "code": "es",
            "policy": "deterministic"
        },
        "components": [
            {
                "type": "header",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|title|]"
                    }
                ]
            },
                {
                "type": "body",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|area|]"
                    },
                                        {
                        "type": "text",
                        "text": "[|comentario|]"
                    }
                ]
            }

        ]
    }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", '51'.$telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|area|]", $area, $bodyNotificacion);
$bodyNotificacion = str_replace("[|comentario|]", $comentario, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
  
  
return $delate;

}

public function finalizarRechazoPlanta( $usuarioId,$invitacionId,$comentario){


  $tipo=2;
  $invitacion=Invitacion::create()->getInvitacionXIdPlanta($invitacionId);
 
    $area='Administración';
    $delate=Invitacion::create()->eliminarInvitacionPlanta($invitacionId,2);
    if($delate[0]['vout_exito']==1){
      
    } 
    $telefono=$invitacion[0]['telefono'];
    $nombre=$invitacion[0]['nombre'];
    $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "rechazo_definitivo",
        "language": {
            "code": "es",
            "policy": "deterministic"
        },
        "components": [
            {
                "type": "header",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|title|]"
                    }
                ]
            },
                {
                "type": "body",
                "parameters": [
                    {
                        "type": "text",
                        "text": "[|area|]"
                    },
                                        {
                        "type": "text",
                        "text": "[|comentario|]"
                    }
                ]
            }

        ]
    }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", '51'.$telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|area|]", $area, $bodyNotificacion);
$bodyNotificacion = str_replace("[|comentario|]", $comentario, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
  
  
return $delate;

}

public function solicitarActualizacion( $usuarioId,$nivel,$invitacionId,$comentario){

  $nivel=$nivel+1;
  $tipo=0;
  if($nivel==3){
    $area='Planta';
    $delate=Invitacion::create()->solicitudactualizacionDatos($invitacionId,2);
    if($delate[0]['vout_exito']==1){
      Invitacion::create()->registrarLogInvitacion($invitacionId,'Actualizar datos por planta',$comentario,$usuarioId,$tipo);
    }
   }


  else{
    $area='Administración';
    $delate=Invitacion::create()->solicitudactualizacionDatos($invitacionId,1);
    if($delate[0]['vout_exito']==1){
      Invitacion::create()->registrarLogInvitacion($invitacionId,'Actualizar datos por administración',$comentario,$usuarioId,$tipo);
    }

  }

  $invitacion=Invitacion::create()->getInvitacionXId($invitacionId);

    
  $telefono=$invitacion[0]['telefono'];
  $nombre=$invitacion[0]['nombre'];
  $bodyNotificacion = '
{
  "messaging_product": "whatsapp",
  "to": "[|phone|]",
  "type": "template",
  "template": {
      "name": "solicitud_actualizacion_datos",
      "language": {
          "code": "es",
          "policy": "deterministic"
      },
      "components": [
          {
              "type": "header",
              "parameters": [
                  {
                      "type": "text",
                      "text": "[|title|]"
                  }
              ]
          },
              {
              "type": "body",
              "parameters": [
                  {
                      "type": "text",
                      "text": "[|area|]"
                  },
                                      {
                      "type": "text",
                      "text": "[|comentario|]"
                  }
              ]
          }

      ]
  }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", '51'.$telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|area|]", $area, $bodyNotificacion);
$bodyNotificacion = str_replace("[|comentario|]", $comentario, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
return $delate;

}

public function solicitarActualizacionAsociativo( $usuarioId,$nivel,$invitacionId,$comentario){

  $nivel=$nivel+1;
  $tipo=0;
  if($nivel==3){
    $area='Planta';
    $delate=Invitacion::create()->solicitudactualizacionDatosAsociativo($invitacionId,2);
    if($delate[0]['vout_exito']==1){
      // Invitacion::create()->registrarLogInvitacion($invitacionId,'Actualizar datos por planta',$comentario,$usuarioId,$tipo);
    }
   }


  else{
    $area='Administración';
    $delate=Invitacion::create()->solicitudactualizacionDatosAsociativo($invitacionId,1);
    if($delate[0]['vout_exito']==1){
      // Invitacion::create()->registrarLogInvitacion($invitacionId,'Actualizar datos por administración',$comentario,$usuarioId,$tipo);
    }

  }

  $invitacion=Invitacion::create()->getInvitacionXIdPrincipal($invitacionId);

    
  $telefono=$invitacion[0]['telefono'];
  $nombre=$invitacion[0]['nombre'];
  $bodyNotificacion = '
{
  "messaging_product": "whatsapp",
  "to": "[|phone|]",
  "type": "template",
  "template": {
      "name": "solicitud_actualizacion_datos",
      "language": {
          "code": "es",
          "policy": "deterministic"
      },
      "components": [
          {
              "type": "header",
              "parameters": [
                  {
                      "type": "text",
                      "text": "[|title|]"
                  }
              ]
          },
              {
              "type": "body",
              "parameters": [
                  {
                      "type": "text",
                      "text": "[|area|]"
                  },
                                      {
                      "type": "text",
                      "text": "[|comentario|]"
                  }
              ]
          }

      ]
  }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", '51'.$telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|area|]", $area, $bodyNotificacion);
$bodyNotificacion = str_replace("[|comentario|]", $comentario, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
return $delate;

}
public function solicitarActualizacionTransportista( $usuarioId,$invitacionId,$comentario){


    $area='Administración';
    $delate=Invitacion::create()->solicitudactualizacionDatosTransportisa($invitacionId,1);
    if($delate[0]['vout_exito']==1){
      // Invitacion::create()->registrarLogInvitacion($invitacionId,'Actualizar datos por administración',$comentario,$usuarioId,$tipo);
    }

  

  $invitacion=Invitacion::create()->getInvitacionXIdTransportista($invitacionId);

    
  $telefono=$invitacion[0]['telefono'];
  $nombre=$invitacion[0]['nombre'];
  $bodyNotificacion = '
{
  "messaging_product": "whatsapp",
  "to": "[|phone|]",
  "type": "template",
  "template": {
      "name": "solicitud_actualizacion_datos",
      "language": {
          "code": "es",
          "policy": "deterministic"
      },
      "components": [
          {
              "type": "header",
              "parameters": [
                  {
                      "type": "text",
                      "text": "[|title|]"
                  }
              ]
          },
              {
              "type": "body",
              "parameters": [
                  {
                      "type": "text",
                      "text": "[|area|]"
                  },
                                      {
                      "type": "text",
                      "text": "[|comentario|]"
                  }
              ]
          }

      ]
  }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", '51'.$telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|area|]", $area, $bodyNotificacion);
$bodyNotificacion = str_replace("[|comentario|]", $comentario, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
return $delate;

}

public function solicitarActualizacionPlanta( $usuarioId,$invitacionId,$comentario){


  $area='Administración';
  $delate=Invitacion::create()->solicitudactualizacionDatosPlanta($invitacionId,1);
  if($delate[0]['vout_exito']==1){
    // Invitacion::create()->registrarLogInvitacion($invitacionId,'Actualizar datos por administración',$comentario,$usuarioId,$tipo);
  }



$invitacion=Invitacion::create()->getInvitacionXIdPlanta($invitacionId);

  
$telefono=$invitacion[0]['telefono'];
$nombre=$invitacion[0]['nombre'];
$bodyNotificacion = '
{
"messaging_product": "whatsapp",
"to": "[|phone|]",
"type": "template",
"template": {
    "name": "solicitud_actualizacion_datos",
    "language": {
        "code": "es",
        "policy": "deterministic"
    },
    "components": [
        {
            "type": "header",
            "parameters": [
                {
                    "type": "text",
                    "text": "[|title|]"
                }
            ]
        },
            {
            "type": "body",
            "parameters": [
                {
                    "type": "text",
                    "text": "[|area|]"
                },
                                    {
                    "type": "text",
                    "text": "[|comentario|]"
                }
            ]
        }

    ]
}
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", '51'.$telefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|title|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|area|]", $area, $bodyNotificacion);
$bodyNotificacion = str_replace("[|comentario|]", $comentario, $bodyNotificacion);
SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
return $delate;

}
public function buscarCriteriosBusquedaSolicitud($busqueda, $usuarioId )
{
   $resultado=Invitacion::create()->buscarCriteriosBusquedaSolicitud($busqueda, $usuarioId);
   return $resultado;
}

public function buscarCriteriosBusquedaSolicitudTransportista($busqueda, $usuarioId )
{
   $resultado=Invitacion::create()->buscarCriteriosBusquedaSolicitudTransportista($busqueda, $usuarioId);
   return $resultado;
}

public function buscarCriteriosBusquedaSolicitudPrincipal($busqueda, $usuarioId )
{
   $resultado=Invitacion::create()->buscarCriteriosBusquedaSolicitudPrincipal($busqueda, $usuarioId);
   return $resultado;
}

public function obtenerCuentasBancos( $usuarioId )
{
   $resultado=Invitacion::create()->obtenerCuentasBancos( $usuarioId);
   return $resultado;
}


public function guardarComunero( $dni,$tipo,
$foto,$codigo,$nombre ,$lugarNacimiento,$fechaNacimiento,$direccion,
$estadoCivil,$hijo,$estatura,$madre,$padre,$restriccion,$sexo,
$telefono,$correo,$cuenta,$numeroCuenta,$cci,$firma,$usuarioId){
  
  if($dni==null){
    throw new WarningException("Escriba un DNI válido para realizar el registro");  
  }

  if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $fechaNacimiento, $matches)) {
    $fechaNacimiento = $matches[1];  
  
  }
    list($type, $imageData) = explode(';', $foto);
        list(, $imageData) = explode(',', $imageData);
    
        // Decodificar los datos base64
        $imageData = base64_decode($imageData);
    
        // Crear un nombre único para la imagen
        $imageName = uniqid() .'.png';
    
        // Especificar la ruta donde se guardará la imagen
        $imagePath = '../../vistas/com/persona/imagen/' . $imageName;
 
        // Guardar la imagen en el servidor
        file_put_contents($imagePath, $imageData);

        if($imageData==null){
          throw new WarningException('La imagen del minero no se pudo guardar correctamente');  
        }
    
        
    list($type, $imageData2) = explode(';', $firma);
    list(, $imageData2) = explode(',', $imageData2);

    // Decodificar los datos base64
    $imageData2 = base64_decode($imageData2);

    // Crear un nombre único para la imagen
    $imageName2 = uniqid() .'.png';

    // Especificar la ruta donde se guardará la imagen
    $imagePath2 = '../../vistas/com/persona/firmas/' . $imageName2;

    // Guardar la imagen en el servidor
    file_put_contents($imagePath2, $imageData2);
  
    $firma=$imageName2;
    $foto=$imageName;
  $persona = Persona::create()->obtenerPersonaXCodigoIdentificacion($dni);
  $personaPadre = Persona::create()->obtenerPersonaXUsuarioId($usuarioId);
  if($persona==null){
  $persona = Persona::create()->insertPersonaMinero(2, $dni, $nombre, null, null, 
  $telefono, null, $correo, $direccion, null, $foto, 1, $usuarioId, null, 
  null, null, null, null, null, null,null,null,$firma,null,
  $lugarNacimiento,$fechaNacimiento,$estadoCivil,$hijo,$estatura,$madre,$padre,
  $restriccion,$sexo,$codigo,$personaPadre[0]['id'],$tipo);
  if($persona[0]['vout_exito']==0){
    throw new WarningException($persona[0]['vout_mensaje']);  
  }
  $personaId=$persona[0]['id'];
  Persona::create()->savePersonaClasePersona(31, $personaId, $usuarioId);
    Persona::create()->guardarCuentasPersona($personaId,$cuenta,$numeroCuenta,$cci,$usuarioId);
    Persona::create()->guardarCapturasDNI($personaId,$usuarioId);
    Invitacion::create()->insertInvitacionSecundario($personaId, $direccion, $ubigeo=null, 1,2, $usuarioId,null,null,2);
  }
else {
  throw new WarningException('Persona ya registrada, contactar con soporte');  
}
      $clave_generada = Util::generateCode();
      $clave = Util::encripta($clave_generada);
      $usuario=Usuario::create()->insertUsuario($dni, $personaId, $usuarioId, 1, $clave); 
      $registroPerfilUsuario=Usuario::create()->insertDetUsuarioPerfil($usuario[0]['id'], 142, 2, $usuarioId, 1);

     

    
     
      $bodyNotificacion = '
  {
      "messaging_product": "whatsapp",
      "to": "[|phone|]",
      "type": "template",
      "template": {
          "name": "plantilla_credenciales_usuario",
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
                          "text": "[|usuario|]"
                      },
                                          {
                          "type": "text",
                          "text": "[|clave|]"
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
  $bodyNotificacion = str_replace("[|usuario|]", $dni, $bodyNotificacion);
  $bodyNotificacion = str_replace("[|clave|]", $clave_generada, $bodyNotificacion);
  $respuesta=SolicitudRetiroNegocio::create()->notificacionWsp($bodyNotificacion);
}
}
