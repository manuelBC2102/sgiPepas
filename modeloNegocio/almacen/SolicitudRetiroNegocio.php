<?php
session_start();

require_once __DIR__ . '/../../modelo/almacen/Persona.php';
require_once __DIR__ . '/../../modelo/almacen/MatrizAprobacion.php';
require_once __DIR__ . '/../../modelo/almacen/SolicitudRetiro.php';
require_once __DIR__ . '/../../modelo/almacen/ActaRetiro.php';
require_once __DIR__ . '/../../modeloNegocio/almacen/ActaRetiroNegocio.php';
require_once __DIR__ . '/../../modelo/almacen/Vehiculo.php';
require_once __DIR__ . '/../../modelo/almacen/Zona.php';
require_once __DIR__ . '/../../modelo/almacen/ConsultaWs.php';
require_once __DIR__ . '/../../modeloNegocio/core/ModeloNegocioBase.php';
require_once __DIR__ . '/../../modeloNegocio/commons/ConstantesNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/SunatTablaNegocio.php';
require_once __DIR__ . '/../../modeloNegocio/contabilidad/CentroCostoNegocio.php';
require_once __DIR__ . '/../../util/Configuraciones.php';
class SolicitudRetiroNegocio extends ModeloNegocioBase
{

  /**
   *
   * @return SolicitudRetiroNegocio
   */
  static function create()
  {
    return parent::create();
  }

  public function listarSolicitudes($usuarioId)
  {
      $documento = 276;
      $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXDocumento($usuarioId, $documento);
      $invitaciones = [];
  
      if ($matrizUsuario == null) {
          throw new WarningException("Usuario no tiene perfil de aprobador para aprobar solicitudes de retiro.");
      }
  
      foreach ($matrizUsuario as $item) {
          $nivel = $item['nivel'];
          if ($nivel == 1) {
            
            $result = SolicitudRetiro::create()->obtenerSolicitudNivel($nivel,null);
            if ($result !== null) {
                $invitaciones = array_merge($invitaciones, $result);
            }
        }
          else if ($nivel == 2) {
              $plantaId = $item['persona_planta_id'];
              $result = SolicitudRetiro::create()->obtenerSolicitudNivel($nivel,$plantaId);
              if ($result !== null) {
                  $invitaciones = array_merge($invitaciones, $result);
              }
          } else if ($nivel == 3) {
              $zonaId = $item['zona_id'];
              $result = SolicitudRetiro::create()->obtenerSolicitudNivel($nivel,$zonaId);
              if ($result !== null) {
                  $invitaciones = array_merge($invitaciones, $result);
              }
          } else if ($nivel == 4) {
            $result = SolicitudRetiro::create()->obtenerSolicitudNivel($nivel);
            if ($result !== null) {
                $invitaciones = array_merge($invitaciones, $result);
            }
        }
      }
  
      return $invitaciones;
  }

  public function obtenerArchivos($usuarioId,$solicitudId){
    $respuesta=($solicitudId > 0) ? SolicitudRetiro::create()->getSolicituRetiroXId($solicitudId ) : null;
    $personaId=$respuesta[0]['persona_id'];
    $dataArchivos=Persona::create()->obtenerArchivos($personaId);
  return $dataArchivos;

}

public function obtenerConfiguracionesPersona($solicitudId, $usuarioId)
{
  $respuesta2=($solicitudId > 0) ? SolicitudRetiro::create()->getSolicituRetiroXId($solicitudId ) : null;
  $personaId=$respuesta2[0]['persona_id'];
  $respuesta = new ObjectUtil();
  //$respuesta->empresa = EmpresaNegocio::create()->getAllEmpresaByUsuarioId($usuarioId);
  $respuesta->empresa = EmpresaNegocio::create()->getEmpresaActivas();
  //        $respuesta->persona_clase = Persona::create()->getAllPersonaClaseByTipo($personaTipoId);
  $respuesta->persona = ($personaId > 0) ? $this->obtenerPersonaXId($personaId) : null;

  //contactos
  $respuesta->personaNatural = Persona::create()->obtenerPersonasXPersonaTipo(2); // 2-> natural
  $respuesta->contactoTipo = Persona::create()->obtenerContactoTipoActivos();
  $respuesta->personaContacto = ($personaId > 0) ? $this->obtenerPersonaContactoXPersonaId($personaId) : null;

  //direcciones
  $respuesta->direccionTipo = Persona::create()->obtenerDireccionTipoActivos();
  $respuesta->dataUbigeo = Persona::create()->obtenerUbigeoActivos();
  $respuesta->personaDireccion = ($personaId > 0) ? $this->obtenerPersonaDireccionXPersonaId($personaId) : null;

  //persona clase asociada al usuario
  $respuesta->personaClaseXUsuario = Persona::create()->obtenerPersonaClaseXUsuarioId($usuarioId);

  //tablas sunat
  return $respuesta;
}

  public function obtenerSolicitudesUsuario($usuarioId){
    $persona=Persona::create()->obtenerPersonaXUsuarioId($usuarioId);
    $personaId=$persona[0]['id'];
    return SolicitudRetiro::create()->obtenerSolicitudesUsuario($personaId);
  }

  public function obtenerSolicitudesPesajesUsuario($usuarioId){
    $persona=Persona::create()->obtenerPersonaXUsuarioId($usuarioId);
    $personaId=$persona[0]['id'];
    return SolicitudRetiro::create()->obtenerSolicitudesPesajesUsuario($personaId);
  }

  public function obtenerPesajeXSolicitud($usuarioId,$solicitudId){

    return SolicitudRetiro::create()->obtenerPesajeXSolicitud($solicitudId);
  }

  public function registrarConformidadPesaje($usuarioId,$solicitudId){

    $solicitudInfo = SolicitudRetiro::create()->getSolicituRetiroXId($solicitudId);
      
    $zonaId=$solicitudInfo[0]['zona_id'];
    $plantaId=$solicitudInfo[0]['persona_planta_id'];
    $transportistaId=$solicitudInfo[0]['persona_transportista_id'];
   
    //FACTURADOR EMISOR
    $personaMinero=Persona::create()->obtenerPersonaGetByIdAll($transportistaId);
    $facturadorId=$personaMinero[0]['id'];
    $facturadorCodigo=$personaMinero[0]['codigo_identificacion'];
    $facturadorRazon=$personaMinero[0]['nombre'];
    $facturadorEmail=$personaMinero[0]['email'];
    $facturadorSerie=$personaMinero[0]['serie_factura'];
    $facturadorCorrelativo=$personaMinero[0]['correlativo_factura'];
    $personaMineroDireccion=Persona::create()->obtenerDireccionXPersonaId($facturadorId);

     //RECEPTOR
     $personaPlanta=Persona::create()->obtenerPersonaGetByIdAll(3164);
     $plantaCodigo=$personaPlanta[0]['codigo_identificacion'];
     $plantaRazon=$personaPlanta[0]['nombre'];
     
     $personaPlantaDireccion=Persona::create()->obtenerDireccionXPersonaId(3164);

         //DIRECCION PARTIDA
    $zonaInfo = Zona::create()->listarZonasXId($zonaId);

    //DIRECCION LLEGADA
    $direccionLlegada=Persona::create()->obtenerDireccionXPersonaId($plantaId,1);

    $montoTonelada=54.7458;
    $tipoCambio=SolicitudRetiroNegocio::create()->obtenerTipoCambio2($usuarioId);
    $pesoTotal=ActaRetiro::create()->obtenerPesajeTotalSolicitud($solicitudId);
    $pesoTotal=$pesoTotal[0]['suma'];
    $subtotal=round($pesoTotal*$montoTonelada,2);
    $igv=$subtotal*0.18;
    $totalFactura=$subtotal*1.18;
    $detraccion=round($totalFactura/10,0);
    $netoPago=$totalFactura-$detraccion;
    $textoLotes='POR EL SERVICIO DE TRANSPORTE DE MINERAL AURIFERO EN BRUTO GRANEL SIN PROCESAR';

    $montoLetrasFactura=utf8_decode(ActaRetiroNegocio::create()->convertir_a_texto($totalFactura));
   
     // $datoPalabra=self::convertir_a_palabras($totalFactura*1);
 
         $fecha = date('Y-m-d'); // Fecha en formato YYYY-MM-DD
         $nueva_fecha = date('Y-m-d', strtotime($fecha . ' +30 days')); 
         $hora = date('H:i:s');
         $token= SolicitudRetiroNegocio::create()->generarTokenEfact('20600739256','e24243d460d9d29bddcafdef34c7f4cf853e719d5e217984d2149150d52397e2');
         $token = $token->access_token;
 
 
         //FACTURA MINERAL
       
         $jsonFactura = SolicitudRetiroNegocio::create()->generarJsonFacturaTransportista($fecha,$hora,$subtotal,$igv,$totalFactura,
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
          throw new WarningException("No se pudo registrar la factura de transportista."); 
        }
 
         $valorizacion=ActaRetiro::create()->registrarFacturaProveedor($facturadorSerie,$facturadorCorrelativo,$subtotal,$igv,$totalFactura,
         $detraccion,$netoPago,$usuarioId,$comentarioEfact,$facturarDocumento,$transportistaId,
         $solicitudId);
         $incrementado = (int)$facturadorCorrelativo + 1;
         $correlativoNuevo = sprintf('%06d', $incrementado);

          $facturador=ActaRetiro::create()->actualizarCorrelativoFacturador($facturadorId,$correlativoNuevo);

    return SolicitudRetiro::create()->registrarConformidadPesaje($solicitudId);
  }

  
  public function registrarrechazarPesaje($usuarioId,$solicitudId){

    return SolicitudRetiro::create()->registrarrechazarPesaje($solicitudId);
  }
  

  public function obtenerValidacionTransportista($usuarioId,$transportista){
        if($transportista==null){
          throw new WarningException("Escriba una placa válida para realizar la validación");  
        }

        $persona=Persona::create()->obtenerPersonaGetById($transportista);
        $ruc=$persona[0]['codigo_identificacion'];
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
      return $data ;
  }

  public function obtenerTipoCambio($usuarioId){
    $fecha = date('Y-m-d');
     $tipoCambio=TipoCambio::create()->obtenerTipoCambioXfecha($fecha);
    if($tipoCambio!=null) {
        $data=$tipoCambio[0]['equivalencia_venta'];}
        else{
  $data=[];
  $url= 'http://161.132.56.121:8000/tipo_cambio_sunat';
  $ch = curl_init();
  $endpointUrl = $url ;
  curl_setopt($ch, CURLOPT_URL, $endpointUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Límite de tiempo para la solicitud completa
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
  $response2 = json_decode(curl_exec($ch));
  curl_close($ch);
  if ($response2 == null ) {
      throw new WarningException("No se encontró tipo de Cambio.");  
  }
  $precioVenta=$response2->venta;
  $precioCompra=$response2->compra ;
  $captura=$response2->captura ;
  TipoCambio::create()->insertarActualizarTipoCambio(null,4,$fecha,$precioCompra ,$precioVenta,$usuarioId);
  $data = $precioVenta; }
  return $data ;
}

public function obtenerTipoCambio2($usuarioId){
    $fecha = date('Y-m-d');
     $tipoCambio=TipoCambio::create()->obtenerTipoCambioXfecha($fecha);
    if($tipoCambio!=null) {
        $data=$tipoCambio[0]['equivalencia_venta'];}
        else{
  $data=[];
  $url= 'http://161.132.56.121:8000/tipo_cambio_sunat2';
  $ch = curl_init();
  $endpointUrl = $url ;
  curl_setopt($ch, CURLOPT_URL, $endpointUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Límite de tiempo para la solicitud completa
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
  $response2 = json_decode(curl_exec($ch));
  curl_close($ch);
  if ($response2 == null ) {
      throw new WarningException("No se encontró tipo de Cambio.");  
  }
  $precioVenta=$response2->venta;
  $precioCompra=$response2->compra ;
  $captura=$response2->captura ;
  $data = $precioVenta; }
  return $data ;
}

public function obtenerUbigeo($usuarioId, $ruc) {
    $data = [];
    $url = 'http://161.132.56.121:8000/obtener_ubigeo_ruc/';
    $maxIntentos = 3; // Número máximo de intentos
    $intentos = 0; // Contador de intentos
    $success = false; // Variable para indicar si la solicitud fue exitosa

    while ($intentos < $maxIntentos && !$success) {
        try {
            $ch = curl_init();
            $endpointUrl = $url . urlencode($ruc);
            curl_setopt($ch, CURLOPT_URL, $endpointUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Límite de tiempo para la solicitud completa
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 40);
            $response2 = json_decode(curl_exec($ch));
            curl_close($ch);

            // Verificar si la respuesta es válida y si contiene los datos esperados
            if ($response2 == null ) {
                throw new Exception("No se encontró ubigeo para este RUC o la respuesta es inválida.");
            }

            // Si la respuesta es válida, procesamos los datos
            $razonSocial = $response2->razon_social;
            if($razonSocial==null){
                throw new Exception("No se encontró ubigeo para este RUC o la respuesta es inválida.");
            }
            $partes = explode(" - ", $razonSocial);

            // Verificar que el arreglo resultante tenga al menos dos partes
            if (count($partes) >= 2) {
                $ruc = trim($partes[0]);        // El primer elemento será el RUC
                $razonSocial = trim($partes[1]); // El segundo elemento será la razón social
            }

            $domicilioFiscal = $response2->domicilio_fiscal;
            $departamento = $response2->departamento;
            if($departamento=='Libertad' || $departamento=='LIBERTAD' ){
             $departamento='LA LIBERTAD';
            }
            if($departamento=='Dios' || $departamento=='DIOS'){
             $departamento='MADRE DE DIOS';
            }
            if($departamento=='Martín' || $departamento=='MARTÍN'){
             $departamento='SAN MARTÍN';
            }
            $provincia = $response2->provincia;
            $distrito = $response2->distrito;

            // Insertar los datos
            $persona=Persona::create()->insertPersona(4, $ruc, $razonSocial, null, null,
             null, null, null, $domicilioFiscal, $domicilioFiscal,null, 1, $usuarioId, null, 
             null, null, null, null, null,null, 
             null, null,null,null);
             $personaId=$persona[0]['id'];
             Persona::create()->savePersonaClasePersona(-1, $personaId, $usuarioId);
             $ubigeoPersona=Persona::create()->obtenerUbigeoPersona($departamento ,$provincia,$distrito);
             $ubigeoId=$ubigeoPersona[0]['id'];
             $ubigeoCodigo=$ubigeoPersona[0]['codigo_ubigeo'];

             $response2->ubigeoCodigo = $ubigeoCodigo;
             $response2->ruc = $ruc;
             $response2->razonSocial = $razonSocial;
             $response2->departamento = $departamento;
             $direccion=Persona::create()->guardarPersonaDireccionMinapp($personaId, 1, $domicilioFiscal, $usuarioId, '-1', $ubigeoId ,$departamento,$provincia,
             $distrito,$ubigeoCodigo);
           
            $data = $response2;

            $success = true; // Marca como exitoso si los datos son correctos
        } catch (Exception $e) {
            // Si ocurre un error (como respuesta inválida), incrementamos el contador de intentos
            $intentos++;
            if ($intentos >= $maxIntentos) {
                // Si llegamos al número máximo de intentos, lanzamos una excepción
                throw new WarningException("Error al obtener ubigeo para el RUC {$ruc}. Intentos fallidos: {$maxIntentos}.");
            }

            // Si no es el último intento, podemos esperar un poco antes de reintentar
            sleep(2); // Espera de 2 segundos entre intentos
        }
    }

    return $data;
}

  public function obtenerValidacionVehiculo($usuarioId,$vehiculo){
      if($vehiculo==null){
        throw new WarningException("Escriba una placa válida para realizar la validación");  
      }

      $datosVehiculo=Vehiculo::create()->listarvehiculosXId($vehiculo);
      $placa=$datosVehiculo[0]['placa'];
      $placa = str_replace("-", "", $placa);
      $cantidadCaracteres = strlen($placa);
      if($cantidadCaracteres!=6){
        throw new WarningException("Cantidad de caracteres de la placa no coincide con lo requerido.");  
      }
    $data=[];
    $url= 'http://161.132.56.121:8000/mercaderia_placa/';
    $ch = curl_init();
    $endpointUrl = $url . urlencode($placa);
    curl_setopt($ch, CURLOPT_URL, $endpointUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Límite de tiempo para la solicitud completa
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    $response2 = json_decode(curl_exec($ch));
    $response = json_decode($response2);
    curl_close($ch);
    if ($response == null ) {
        throw new WarningException("No se encontro data para ese RUC y código unico.");  
    }
    $data = $response; 
    return $data ;
  }

  public function obtenerValidacionConductor($usuarioId,$conductor){
      if($conductor==null){
        throw new WarningException("Escriba una placa válida para realizar la validación");  
      }

      $persona=Persona::create()->obtenerPersonaGetById($conductor);
      $dni=$persona[0]['codigo_identificacion'];
    $data=[];
    $url= 'http://161.132.56.121:8000/licencia/';
    $ch = curl_init();
    $endpointUrl = $url . urlencode($dni);
    curl_setopt($ch, CURLOPT_URL, $endpointUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Límite de tiempo para la solicitud completa
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    $response2 = json_decode(curl_exec($ch));
    $response = json_decode($response2);
    curl_close($ch);
    if ($response == null ) {
        throw new WarningException("No se encontró data para este transportista con este DNI: $dni.");  
    }
    $data = $response; 
    return $data ;
  }

  
  
  public function getAllSolicitudesAprobacion( $elemntosFiltrados, $columns, $order, $start, $usuarioId,$idPersona )
  {
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
 
    //        $fechaVencimientoDesde = DateUtil::formatearCadenaACadenaBD($valor['inicio']);

    $personaClaseXUsuario = Persona::create()->obtenerPersonaClaseXUsuarioId2($usuarioId);
    $persona=Persona::create()->obtenerPersonaXUsuarioId($usuarioId);
    $personaId=$persona[0]['id'];
    $valoresBuscados = ["25", "27", "28"];

      // Extraer todos los valores de la columna 'id'
      $ids = array_column($personaClaseXUsuario, 'id');

      // Variables para almacenar el estado de cada valor buscado
      $planta = false;
      $zona = false;
      $junta = false;

      // Verificar por separado si cada valor buscado existe en la columna 'id'
      foreach ($valoresBuscados as $valor) {
          if (in_array($valor, $ids)) {
              switch ($valor) {
                  case "25":
                      $planta = true;
                      break;
                  case "27":
                      $zona = true;
                      break;
                  case "28":
                      $junta = true;
                      break;
              }
          }
      }

      $persona_planta_id=null;
      $documento_estado_id=null;
      $zona_id=null;

      // Imprimir los resultados
      if ($planta) {
        $persona_planta_id=$personaId;
        $documento_estado_id=1;
        $usuarioId=null;
      } 

      else if ($zona) {
        $zona_id=1;
        $documento_estado_id=11;
        $usuarioId=null;
      } 

      else if ($junta) {
        $documento_estado_id=12;
        $usuarioId=null;
      } 

      else{
        
        $elemntosFiltrados=0;
        
      }

    return SolicitudRetiro::create()->getAllSolicitudesAprobacion($columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona,$persona_planta_id,$zona_id,$documento_estado_id);
  }


  public function insertAprobacionSolicitud( $idSolicitud, $usuarioId ){
    
    try{
      $respuesta=($idSolicitud > 0) ? SolicitudRetiro::create()->getSolicituRetiroXId($idSolicitud ) : null;
      $nivel=$respuesta[0]['nivel'];
      $fecha=$respuesta[0]['fecha_entrega'];
      $fecha = date("d-m-Y", strtotime($fecha));
      $documento = 276;
      
      if($nivel==1)
      {   
        $documento_estado_id=15;
        $nivelActualizar=$nivel+1;
        try{
       
        $persona= Persona::create()->obtenerPersonaXId($respuesta[0]['persona_planta_id']);
$destinatarioTelefono = '51'.$persona[0]['telefono']; // Reemplaza con el n迆mero de tel谷fono del destinatario
$nombre = $persona[0]['nombre'];
$fechaEntrega = date("d-m-Y", strtotime($fecha));

$bodyNotificacion = '
{
   "messaging_product": "whatsapp",
   "to": "[|phone|]",
   "type": "template",
   "template": {
       "name": "notificacion_aprobador_planta",
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
                   },
                   {
                       "type": "text",
                       "text": "[|fecha|]"
                   }
               ]
           }
       ]
   }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", $destinatarioTelefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|nombre|]", $nombre, $bodyNotificacion);
$bodyNotificacion = str_replace("[|nro|]", $idSolicitud, $bodyNotificacion);
$bodyNotificacion = str_replace("[|fecha|]", $fechaEntrega, $bodyNotificacion);
$this->notificacionWsp($bodyNotificacion);

       }
       catch(Exception $e){  throw new WarningException("Error al enviar notificación aprobador. " . $e->getMessage());}           
        }      // Imprimir los resultados
     
       else if ($nivel==2) {
        $documento_estado_id=11;
        $zona=$respuesta[0]['zona_id'];
        
        $nivelActualizar=$nivel+1;
        $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXZona($nivelActualizar, $documento,$zona);
        foreach($matrizUsuario as $usuario){
          $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "notificacion_aprobador_planta",
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
                    },
                    {
                        "type": "text",
                        "text": "[|fecha|]"
                    }
                ]
            }
        ]
    }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", '51'.$usuario['telefono'], $bodyNotificacion);
$bodyNotificacion = str_replace("[|nombre|]", $usuario['nombre'], $bodyNotificacion);
$bodyNotificacion = str_replace("[|nro|]", $idSolicitud, $bodyNotificacion);
$bodyNotificacion = str_replace("[|fecha|]", $fecha, $bodyNotificacion);
$this->notificacionWsp($bodyNotificacion);
        }
      } 
      else if ($nivel==3) {
        $documento_estado_id=12;
        $nivelActualizar=$nivel+1;
        $matrizUsuario = MatrizAprobacion::create()->getMatrizXUsuarioXJunta($nivelActualizar, $documento);
        foreach($matrizUsuario as $usuario){
          $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "notificacion_aprobador_planta",
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
                    },
                    {
                        "type": "text",
                        "text": "[|fecha|]"
                    }
                ]
            }
        ]
    }
}
';

// Reemplaza las variables en el JSON
$bodyNotificacion = str_replace("[|phone|]", '51'.$usuario['telefono'], $bodyNotificacion);
$bodyNotificacion = str_replace("[|nombre|]", $usuario['nombre'], $bodyNotificacion);
$bodyNotificacion = str_replace("[|nro|]", $idSolicitud, $bodyNotificacion);
$bodyNotificacion = str_replace("[|fecha|]", $fecha, $bodyNotificacion);
$this->notificacionWsp($bodyNotificacion);
        }
      } 
      else if ($nivel==4) {
        $documento_estado_id=13;
        $nivelActualizar=$nivel+1;
        $datoDocumento = SolicitudRetiro::create()->obtenerSolicitudFormatoXID($idSolicitud);
        $bodyNotificacion = '
        {
            "messaging_product": "whatsapp",
            "to": "[|phone|]",
            "type": "template",
            "template": {
                "name": "notificacion_aviso_aprobacion",
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
                            },
                            {
                                "type": "text",
                                "text": "[|fecha|]"
                            },
                             {
                                "type": "text",
                                "text": "[|transportista|]"
                            },
                             {
                                "type": "text",
                                "text": "[|vehiculo|]"
                            },
                             {
                                "type": "text",
                                "text": "[|conductor|]"
                            },
                             {
                                "type": "text",
                                "text": "[|origen|]"
                            },
                             {
                                "type": "text",
                                "text": "[|destino|]"
                            }
                        ]
                    }
                ]
            }
        }
        ';
        
        // Reemplaza las variables en el JSON
        $bodyNotificacion = str_replace("[|phone|]", '51'.$datoDocumento[0]['celular'], $bodyNotificacion);
        $bodyNotificacion = str_replace("[|nombre|]", $datoDocumento[0]['persona_nombre'], $bodyNotificacion);
        $bodyNotificacion = str_replace("[|nro|]", $idSolicitud, $bodyNotificacion);
        $bodyNotificacion = str_replace("[|fecha|]", $fecha, $bodyNotificacion);
        $bodyNotificacion = str_replace("[|transportista|]", $datoDocumento[0]['transportista_nombre'], $bodyNotificacion);
        $bodyNotificacion = str_replace("[|vehiculo|]", $datoDocumento[0]['placa'], $bodyNotificacion);
        $bodyNotificacion = str_replace("[|conductor|]", $datoDocumento[0]['conductor_nombre'], $bodyNotificacion);
        $bodyNotificacion = str_replace("[|origen|]", $datoDocumento[0]['nombre_zona'], $bodyNotificacion);
           $bodyNotificacion = str_replace("[|destino|]", $datoDocumento[0]['destino_final'], $bodyNotificacion);
        $this->notificacionWsp($bodyNotificacion);
      } 
      else{
        throw new WarningException("Tu usuario no cuenta con permisos de aprobación. ");
      }
    
    $actualizarSolicitud= SolicitudRetiro::create()->insertAprobacionSolicitud($idSolicitud,$documento_estado_id,$nivelActualizar);
    SolicitudRetiro::create()->guardarEstadoSolicitud($idSolicitud,$documento_estado_id,$usuarioId);
    return $actualizarSolicitud;
    }
    catch (Exception $e) {

      throw new WarningException("Error al guardar. " . $e->getMessage());
  
    } 
  }

  public function insertDesaprobacionSolicitud( $idSolicitud, $motivo,$usuarioId ){
    if($motivo==null || $motivo ==''){
      $motivo='Actualiza los datos de la solicitud , ya que encontramos errores';
    }
    try{
    $respuesta=($idSolicitud > 0) ? SolicitudRetiro::create()->getSolicituRetiroXId($idSolicitud ) : null;
    $persona=Persona::create()->obtenerPersonaXId($respuesta[0]['persona_reinfo_id']);
    $nombre=$persona[0]['nombre'];
    $telefono=$persona[0]['telefono'];
    
    $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "plantilla_desaprobacion_dinamica",
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
                    },
                    {
                        "type": "text",
                        "text": "[|motivo|]"
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
$bodyNotificacion = str_replace("[|nro|]", $idSolicitud, $bodyNotificacion);
$bodyNotificacion = str_replace("[|motivo|]", $motivo, $bodyNotificacion);
$this->notificacionWsp($bodyNotificacion);
    
    $actualizarSolicitud= SolicitudRetiro::create()->insertDesaprobacionSolicitud($idSolicitud,9,$motivo);
    SolicitudRetiro::create()->guardarEstadoSolicitud($idSolicitud,9,$usuarioId);
    return $actualizarSolicitud;
    }
    catch (Exception $e) {

      throw new WarningException("Error al desaprobar. " . $e->getMessage());
  
    } 
  }

  public function getAllSolicitudes( $elemntosFiltrados, $columns, $order, $start, $usuarioId,$idPersona,$planta,$zona,$vehiculo,$transportista )
  {
    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];
 
    return SolicitudRetiro::create()->getAllSolicitudes($columnaOrdenar, $formaOrdenar, $elemntosFiltrados, $start, $usuarioId,$idPersona,$planta,$zona,$vehiculo,$transportista);
  }

  public function getCantidadAllSolicitudes( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$idPersona,$planta,$zona,$vehiculo,$transportista)
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];

    return SolicitudRetiro::create()->getCantidadAllSolicitudes($columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,$planta,$zona,$vehiculo,$transportista);
  }
 
  public function getCantidadAllSolicitudesAprobacion( $elemntosFiltrados, $columns, $order, $start, $usuarioId ,$idPersona)
  {

    $columnaOrdenarIndice = $order[0]['column'];
    $formaOrdenar = $order[0]['dir'];
    $columnaOrdenar = $columns[$columnaOrdenarIndice]['data'];


    $personaClaseXUsuario = Persona::create()->obtenerPersonaClaseXUsuarioId2($usuarioId);
    $persona=Persona::create()->obtenerPersonaXUsuarioId($usuarioId);
    $personaId=$persona[0]['id'];
    $valoresBuscados = ["25", "27", "28"];
        $ids = array_column($personaClaseXUsuario, 'id');
        // Variables para almacenar el estado de cada valor buscado
        $planta = false;
        $zona = false;
        $junta = false;
        // Verificar por separado si cada valor buscado existe en la columna 'id'
        foreach ($valoresBuscados as $valor) {
            if (in_array($valor, $ids)) {
                switch ($valor) {
                    case "25":
                        $planta = true;
                        break;
                    case "27":
                        $zona = true;
                        break;
                    case "28":
                        $junta = true;
                        break;
                }
            }
        }

        $persona_planta_id=null;
        $documento_estado_id=null;
        $zona_id=null;

        // Imprimir los resultados
        if ($planta) {
          $persona_planta_id=$personaId;
          $documento_estado_id=1;
          $usuarioId=null;
        } 

       else if ($zona) {
          $zona_id=1;
          $documento_estado_id=11;
          $usuarioId=null;
        } 

        else if ($junta) {
          $documento_estado_id=12;
          $usuarioId=null;
        } 

        else{
          $array[0]['total'] = 0;
          return $array;
        
      }
    return SolicitudRetiro::create()->getCantidadAllSolicitudesAprobacion($columnaOrdenar, $formaOrdenar, $usuarioId,$idPersona,$persona_planta_id,$zona_id,$documento_estado_id);
    
  }
  
  public function insertSolicitud($fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId,$reinfo,$lotes )
  {
    try {
      

      // fin direccion tipo
      $fechaLlegada = date('Y-m-d', strtotime($fechaEntrega . ' +2 days'));
      $res = SolicitudRetiro::create()->guardarSolicitud($fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId,$reinfo,$fechaLlegada,$lotes);

      if ($res[0]['vout_exito'] == 0) {
        throw new WarningException("Error al guardar la solicitud");
      }

      else{
        $solicitudId=$res[0]['id'];
        SolicitudRetiro::create()->guardarEstadoSolicitud($solicitudId,1,$usuarioId);

       
      }
    
    return $res;

  } catch (Exception $e) {

    throw new WarningException("Error al guardar. " . $e->getMessage());

  }  
  }

  public function notificacionWsp($bodyNotificacion) {
    
    $accessToken = Configuraciones::TOKEN_WSP;
    $phoneNumberId = Configuraciones::PHONENUMBERID;
    $whatsappUrl = Configuraciones::WHATSAPP_URL;
    $whatsappVersion = Configuraciones::WHATSAPP_VERSION;

    // Decodificar y volver a codificar el cuerpo de la notificación
    $bodyNotificacion = json_decode($bodyNotificacion, true);
    $body = json_encode($bodyNotificacion);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['status' => '0', 'code' => 0, 'message' => 'Invalid JSON encoding'];
    }

    $url = $whatsappUrl . $whatsappVersion . "/" . $phoneNumberId . "/messages";

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ],
       // Ruta completa al archivo CA
    ]);

    $response = curl_exec($curl);
    $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
        curl_close($curl);
        return ['status' => '0', 'code' => $statusCode, 'message' => $error_msg];
    }
    curl_close($curl);

    $responseJson = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['status' => '0', 'code' => $statusCode, 'message' => 'Invalid JSON response'];
    }
    if ($statusCode == 200) {
        return ['status' => '1', 'code' => 0, 'message' => $responseJson['messages'][0]['id']];
    } else {
        return ['status' => '0', 'code' => $statusCode, 'message' => $responseJson['error']['message']];
    }
}


  public function insertSolicitudMovil($fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId,$fechaLlegada,$lotes )
  {
    try {
      $persona=Persona::create()->obtenerPersonaXUsuarioId($usuarioId);
      $reinfo=$persona[0]['id'];
      $fechaEntrega = date("Y-m-d", strtotime($fechaEntrega));
      $fechaLlegada = date("Y-m-d", strtotime($fechaLlegada));
      
      // fin direccion tipo

      $res = SolicitudRetiro::create()->guardarSolicitud($fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId,$reinfo,$fechaLlegada,$lotes);

      if ($res[0]['vout_exito'] == 0) {
        throw new WarningException("Error al guardar la solicitud");
      }

      else{
        $solicitudId=$res[0]['id'];
        SolicitudRetiro::create()->guardarEstadoSolicitud($solicitudId,1,$usuarioId);
        $requerimiento = SolicitudRetiro::create()->guardarRequerimiento($solicitudId,$fechaEntrega,$transportista,$usuarioId,$reinfo,$fechaLlegada);
        SolicitudRetiro::create()->guardarValidacionMTC($solicitudId,$usuarioId);
        //         try{
       
//           $persona= Persona::create()->obtenerPersonaXId($planta);
//  $destinatarioTelefono = '51'.$persona[0]['telefono']; // Reemplaza con el n迆mero de tel谷fono del destinatario
//  $nombre = $persona[0]['nombre'];
//  $fechaEntrega = date("d-m-Y", strtotime($fechaEntrega));
 
//  $bodyNotificacion = '
//  {
//      "messaging_product": "whatsapp",
//      "to": "[|phone|]",
//      "type": "template",
//      "template": {
//          "name": "notificacion_aprobador_planta",
//          "language": {
//              "code": "es",
//              "policy": "deterministic"
//          },
//          "components": [
 
//              {
//                  "type": "body",
//                  "parameters": [
//                      {
//                          "type": "text",
//                          "text": "[|nombre|]"
//                      },
//                                          {
//                          "type": "text",
//                          "text": "[|nro|]"
//                      },
//                      {
//                          "type": "text",
//                          "text": "[|fecha|]"
//                      }
//                  ]
//              }
//          ]
//      }
//  }
//  ';
 
//  // Reemplaza las variables en el JSON
//  $bodyNotificacion = str_replace("[|phone|]", $destinatarioTelefono, $bodyNotificacion);
//  $bodyNotificacion = str_replace("[|nombre|]", $nombre, $bodyNotificacion);
//  $bodyNotificacion = str_replace("[|nro|]", $solicitudId, $bodyNotificacion);
//  $bodyNotificacion = str_replace("[|fecha|]", $fechaEntrega, $bodyNotificacion);
//  $this->notificacionWsp($bodyNotificacion);
  
//          }
//          catch(Exception $e){  throw new WarningException("Error al enviar notificación aprobador. " . $e->getMessage());}
      }
    
    return $res;

  } catch (Exception $e) {

    throw new WarningException("Error al guardar. " . $e->getMessage());

  }  
  }
  
  public function updateSolicitud($id,$fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId,$reinfo,$lotes )
  {
    try {
      
      $fechaLlegada = date('Y-m-d', strtotime($fechaEntrega . ' +2 days'));
      // fin direccion tipo

      $res = SolicitudRetiro::create()->actualizarSolicitud($id,$fechaEntrega,$capacidad,$constancia,$transportista,$conductor,$vehiculo,$zona,$planta,$usuarioId,$reinfo,$lotes,$fechaLlegada);

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
    $persona=Persona::create()->obtenerPersonaXUsuarioId($usuarioId);
    //contactos
    $respuesta->solicitud = ($solicitudId > 0) ? SolicitudRetiro::create()->getSolicituRetiroXId($solicitudId ) : null;
    $respuesta->vehiculos = Vehiculo::create()->getAllVehiculos(); // 2-> natural
    $respuesta->transportistas = Persona::create()->obtenerPersonasXClase(23);
    $respuesta->conductores = Persona::create()->obtenerPersonasXClase(22);
    $respuesta->plantas = Persona::create()->obtenerPersonasXClase(25);
    $respuesta->reinfo = Persona::create()->obtenerPersonasXClasexUsuario($usuarioId);
    // $respuesta->zonas = Zona::create()->getAllZonasReinfoInvitacion($persona[0]['zona_id']);
    // if($respuesta->zonas==null){

        $respuesta->zonas = Zona::create()->getAllZonas();
    
    //   }  
    $respuesta->zonasTodas = Zona::create()->getAllZonas();
    return $respuesta;
  }

  public function cambiarEstadoSolicitud($id, $usuarioSesion, $estado)
  {
    try {
      

        // fin direccion tipo
  
        $res = SolicitudRetiro::create()->cambiarEstadoSolicitud($id, $usuarioSesion, $estado);
  
        if ($res[0]['vout_exito'] == 0) {
          throw new WarningException("Error al eliminar la solicitud");
        }
  
        else{
          $solicitudId=$res[0]['id'];
          SolicitudRetiro::create()->guardarEstadoSolicitud($id,5,$usuarioSesion);
        }
      
      return $res;
  
    } catch (Exception $e) {
  
      throw new WarningException("Error al guardar. " . $e->getMessage());
  
    }  
  }

  public function buscarSolicitudXSociedadXVehiculo($busqueda, $usuarioId )
  {
    return SolicitudRetiro::create()->buscarSolicitudXSociedadXVehiculo($busqueda, $usuarioId);
  }
  
  public function listarSolicitudesDocumentario($usuarioId ) {
    $solicitudes=SolicitudRetiro::create()->listarSolicitudesDocumentario($usuarioId );
    return $solicitudes;
}

public function listarSolicitudesPorAprobacionPesaje($usuarioId ) {
  $solicitudes=SolicitudRetiro::create()->listarSolicitudesPorAprobacionPesaje($usuarioId );
  return $solicitudes;
}

public function subirArchivo($id,$file,$tipo ) {
  if($tipo=='factura_transporte'){
    $tipo=1;
  }
  else if($tipo=='guia_remision_transp'){
    $tipo=2;
  }
  else{
    $tipo=3;
  }

  list($type, $imageData) = explode(';', $file);
      list(, $imageData) = explode(',', $imageData);
  
      // Decodificar los datos base64
      $imageData = base64_decode($imageData);
  
      // Crear un nombre único para la imagen
      $imageName = uniqid() . '.png';
  
      // Especificar la ruta donde se guardará la imagen
      $imagePath = '../../vistas/com/solicitudRetiro/documento/' . $imageName;
  
      // Guardar la imagen en el servidor
      file_put_contents($imagePath, $imageData);

  $solicitudes=SolicitudRetiro::create()->subirArchivo($id,$imageName,$tipo );
  return $solicitudes;
}


public function eliminarArchivo($id,$archivo,$tipo ) {
  if($tipo=='factura_transporte'){
    $tipo=1;
  }
  else if($tipo=='guia_remision_transp'){
    $tipo=2;
  }
  else{
    $tipo=3;
  }
  $ruta='../../vistas/com/solicitudRetiro/documento/'.$archivo;
  unlink($ruta);
  $solicitudes=SolicitudRetiro::create()->eliminarArchivo($id,$tipo );
  return $solicitudes;
}



public function obtenerSolicitudesPendienteResultados($usuarioId){
  $persona=Persona::create()->obtenerPersonaXUsuarioId($usuarioId);
  $personaId=$persona[0]['id'];
  return SolicitudRetiro::create()->obtenerSolicitudesPendienteResultados($personaId);
}

public function obtenerResultadoXSolicitud($usuarioId,$solicitudId){

  return SolicitudRetiro::create()->obtenerResultadoXSolicitud($solicitudId);
}

public function obtenerResultadoXSolicitudDemo($usuarioId,$solicitudId){

    return SolicitudRetiro::create()->obtenerResultadoXSolicitudDemo($solicitudId);
  }

public function dirimenciaResultados($usuarioId,$loteId,$ley_antigua,$ley,$archivo)
{
  $pdfBinary = base64_decode($archivo);
  $pdfName = uniqid() . '.pdf';
  $pdfPath = '../../vistas/com/dirimencia/resultados/' . $pdfName;
  
  // Guardar la imagen en el servidor
  file_put_contents($pdfPath, $pdfBinary);

  $lote=SolicitudRetiro::create()->actualizarDirimenciaLote($loteId,2);
  
  if ($lote[0]['vout_exito'] == 0) {
    throw new WarningException("Error al guardar la dirimencia del lote");
  }

  $dirimencia=SolicitudRetiro::create()->registrarDirimenciaLote($loteId,$ley_antigua,$ley,$pdfName,$usuarioId);
   
  if ($dirimencia[0]['vout_exito'] == 0) {
    unlink($pdfPath);
    throw new WarningException($dirimencia[0]['vout_mensaje']);
    
  }

  return $dirimencia[0]['vout_mensaje'];
}

public function negociarResultados($usuarioId,$loteId,$ley_antigua,$ley,$archivo)
{
  $pdfBinary = base64_decode($archivo);
  $pdfName = uniqid() . '.pdf';
  $pdfPath = '../../vistas/com/dirimencia/resultados/' . $pdfName;
  
  // Guardar la imagen en el servidor
  file_put_contents($pdfPath, $pdfBinary);
  $dirimencia=SolicitudRetiro::create()->registrarDirimenciaLote($loteId,$ley_antigua,$ley,$pdfName,$usuarioId,'Negociar');
   
  if ($dirimencia[0]['vout_exito'] == 0) {
    unlink($pdfPath);
    throw new WarningException($dirimencia[0]['vout_mensaje']);
    
  }
  $lote=SolicitudRetiro::create()->actualizarDirimenciaLote($loteId,3);
  
  if ($lote[0]['vout_exito'] == 0) {
    throw new WarningException("Error al guardar la negociación del lote");
  }

 

  return $dirimencia[0]['vout_mensaje'];
}

public function registrarAprobacionResultados($usuarioId,$loteId)
{
 

  $lote=SolicitudRetiro::create()->actualizarDirimenciaLote($loteId,1);
  
  if ($lote[0]['vout_exito'] == 0) {
    throw new WarningException("Error al guardar la conformidad del lote");
  }

 $comentarioEfact='';
 $facturarDocumento='';

 $lote=SolicitudRetiro::create()->actualizarFacturacionLote($loteId,$comentarioEfact,$facturarDocumento);

  return $lote[0]['vout_mensaje'];
}

public function obtenerConfiguracionesFiltros(){
  $respuesta = new ObjectUtil();
  $respuesta->zonas = Zona::create()->getAllZonas();
  $respuesta->plantas = Persona::create()->obtenerPersonasXClase(25);
  $respuesta->vehiculos = Vehiculo::create()->getAllVehiculos(); // 2-> natural
  $respuesta->transportistas = Persona::create()->obtenerPersonasXClase(23);

  return $respuesta;
}

public function generarTokenEfact($username,$password){
  $curl = curl_init();

  // Configurar la solicitud cURL
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://ose-gw1.efact.pe/api-efact-ose/oauth/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => http_build_query(array(
        'username' => $username,      // Pasamos el valor de $username
        'password' => $password,      // Pasamos el valor de $password
        'grant_type' => 'password'    // Este parámetro no cambia
    )),
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/x-www-form-urlencoded',
      'Authorization: Basic Y2xpZW50OnNlY3JldA=='  // Aquí sigue siendo la autorización básica
    ),
  ));
  
  $response = json_decode(curl_exec($curl));
  
  curl_close($curl);

  return $response;
}

public function generarTokenEfactProduccion($username,$password){
    $curl = curl_init();
  
    // Configurar la solicitud cURL
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://ose.efact.pe/api-efact-ose/oauth/token',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => http_build_query(array(
          'username' => $username,      // Pasamos el valor de $username
          'password' => $password,      // Pasamos el valor de $password
          'grant_type' => 'password'    // Este parámetro no cambia
      )),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic Y2xpZW50OnNlY3JldA=='  // Aquí sigue siendo la autorización básica
      ),
    ));
    
    $response = json_decode(curl_exec($curl));
    
    curl_close($curl);
  
    return $response;
  }

public function generarJsonGuiaRemisionEfact($fecha,$hora,$facturadorSerie,$facturadorCorrelativo,$facturadorCodigo,
$facturadorRazon,$facturadorEmail,$personaMineroDireccion,$plantaCodigo,$plantaRazon,$personaPlantaDireccion,$transportistaCodigo,$transportistaRazon,
$transportistaMTC,$zonaInfo,$direccionLlegada,$pesaje){
    $templateJson = '{
    "_D": "urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2",
    "_S": "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
    "_B": "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
    "_E": "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2",
    "DespatchAdvice": [
        {
            "UBLVersionID": [
                {
                    "IdentifierContent": "2.1"
                }
            ],
            "CustomizationID": [
                {
                    "IdentifierContent": "2.0"
                }
            ],
            "ID": [
                {
                    "IdentifierContent": "<ID>"
                }
            ],
            "IssueDate": [
                {
                    "DateContent": "<IssueDate>"
                }
            ],
            "IssueTime": [
                {
                    "DateTimeContent": "<IssueTime>"
                }
            ],
            "DespatchAdviceTypeCode": [
                {
                    "IdentifierContent": "09"
                }
            ],
            "Note": [
                {
                    "TextContent": "<observacion>"
                }
            ],
            "LineCountNumeric": [
                {
                    "TextContent": "2"
                }
            ],
            "AdditionalDocumentReference": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "<documentoRelacionado>"
                        }
                    ],
                    "DocumentTypeCode": [
                        {
                            "CodeContent": ""
                        }
                    ],
                    "DocumentType": [
                        {
                            "TextContent": "<descripcionDocumentoRelacionado>"
                        }
                    ],
                    "IssuerParty": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucEmisorDocumentoRelacionado>",
                                            "IdentificationSchemeIdentifier": "6"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "Signature": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "IDSignature"
                        }
                    ],
                    "SignatoryParty": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucEmisor>"
                                        }
                                    ]
                                }
                            ],
                            "PartyName": [
                                {
                                    "Name": [
                                        {
                                            "TextContent": "<razonSocialEmisor>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "DigitalSignatureAttachment": [
                        {
                            "ExternalReference": [
                                {
                                    "URI": [
                                        {
                                            "TextContent": "IDSignature"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "DespatchSupplierParty": [
                {
                    "Party": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucEmisor>",
                                            "IdentificationSchemeIdentifier": "6"
                                        }
                                    ]
                                }
                            ],
                            "PostalAddress": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<ubigeo>"
                                        }
                                    ],
                                    "StreetName": [
                                        {
                                            "TextContent": "<direccionEmisor>"
                                        }
                                    ],
                                    "CitySubdivisionName": [
                                        {
                                            "TextContent": "<urbanizacion>"
                                        }
                                    ],
                                    "CityName": [
                                        {
                                            "TextContent": "<provincia>"
                                        }
                                    ],
                                    "CountrySubentity": [
                                        {
                                            "TextContent": "<departamento>"
                                        }
                                    ],
                                    "District": [
                                        {
                                            "TextContent": "<distrito>"
                                        }
                                    ],
                                    "Country": [
                                        {
                                            "IdentificationCode": [
                                                {
                                                    "IdentifierContent": "PE"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ],
                            "PartyLegalEntity": [
                                {
                                    "RegistrationName": [
                                        {
                                            "TextContent": "<razonSocialEmisor>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "DeliveryCustomerParty": [
                {
                    "Party": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucReceptor>",
                                            "IdentificationSchemeIdentifier": "6"
                                        }
                                    ]
                                }
                            ],
                            "PostalAddress": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<ubigeoReceptor>"
                                        }
                                    ],
                                    "StreetName": [
                                        {
                                            "TextContent": "<direccionReceptor>"
                                        }
                                    ],
                                    "CitySubdivisionName": [
                                        {
                                            "TextContent": "<urbanizacionReceptor>"
                                        }
                                    ],
                                    "CityName": [
                                        {
                                            "TextContent": "<provinciaReceptor>"
                                        }
                                    ],
                                    "CountrySubentity": [
                                        {
                                            "TextContent": "<departamentoReceptor>"
                                        }
                                    ],
                                    "District": [
                                        {
                                            "TextContent": "<distritoReceptor>"
                                        }
                                    ],
                                    "Country": [
                                        {
                                            "IdentificationCode": [
                                                {
                                                    "IdentifierContent": "PE"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ],
                            "PartyLegalEntity": [
                                {
                                    "RegistrationName": [
                                        {
                                            "TextContent": "<razonSocialReceptor>"
                                        }
                                    ]
                                }
                            ],
                            "Contact": [
                                {
                                    "ElectronicMail": [
                                        {
                                            "TextContent": "<electronicMail>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "Shipment": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "SUNAT_Envio"
                        }
                    ],
                    "HandlingCode": [
                        {
                            "IdentifierContent": "01"
                        }
                    ],
                    "HandlingInstructions": [
                        {
                            "TextContent": "<motivoTraslado>"
                        }
                    ],
                    "GrossWeightMeasure": [
                        {
                            "MeasureContent": "<peso>",
                            "MeasureUnitCode": "TNE"
                        }
                    ],
                    "ShipmentStage": [
                        {
                            "TransportModeCode": [
                                {
                                    "IdentifierContent": "01"
                                }
                            ],
                            "TransitPeriod": [
                                {
                                    "StartDate": [
                                        {
                                            "DateContent": "<fechaTraslado>"
                                        }
                                    ]
                                }
                            ],
                            "CarrierParty" : [ 
                                {
                                    "PartyIdentification" : [ 
                                        {
                                            "ID" : [ 
                                                {
                                                    "IdentifierContent" : "<rucTransportista>",
                                                    "IdentificationSchemeIdentifier" : "6"
                                                }
                                            ]
                                        }
                                    ],
                                    "PartyLegalEntity" : [ 
                                        {
                                            "RegistrationName" : [ 
                                                {
                                                    "TextContent" : "<razonSocialTransportista>"
                                                }
                                            ],
                                            "CompanyID" : [ 
                                                {
                                                    "IdentifierContent" : "<registroTransportista>"
                                                }
                                            ]
                                        }
                                    ],
                                    "AgentParty" : [ 
                                        {
                                            "PartyLegalEntity" : [ 
                                                {
                                                    "CompanyID" : [ 
                                                        {
                                                            "IdentifierContent" : "<rucTransportista>",
                                                            "IdentificationSchemeIdentifier" : "06"
                                                        }
                                                    ]
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "Delivery": [
                        {
                           "DeliveryAddress": [
                                  {
                                      "ID": [
                                          {
                                              "IdentifierContent": "<ubigeoLlegada>"
                                          }
                                      ],
                                      "CitySubdivisionName": [
                                          {
                                              "TextContent": "<urbanizacionLlegada>"
                                          }
                                      ],
                                      "CityName": [
                                          {
                                              "TextContent": "<provinciaLlegada>"
                                          }
                                      ],
                                      "CountrySubentity": [
                                          {
                                              "TextContent": "<departamentoLlegada>"
                                          }
                                      ],
                                      "District": [
                                          {
                                              "TextContent": "<distritoLlegada>"
                                          }
                                      ],
                                      "AddressLine": [
                                          {
                                              "Line": [
                                                  {
                                                      "TextContent": "<direccionLlegada>"
                                                  }
                                              ]
                                          }
                                      ],
                                      "Country": [
                                          {
                                              "IdentificationCode": [
                                                  {
                                                      "IdentifierContent": "PE"
                                                  }
                                              ]
                                          }
                                      ]
                                  }
                              ],
                            "Despatch": [
                                {
                                    "DespatchAddress": [
                                        {
                                            "ID": [
                                                {
                                                   "IdentifierContent": "<ubigeoPartida>"
                                                }
                                            ],
                                            "CitySubdivisionName": [
                                                {
                                                   "TextContent": "<urbanizacionPartida>"
                                                }
                                            ],
                                            "CityName": [
                                                {
                                                     "TextContent": "<provinciaPartida>"
                                                }
                                            ],
                                            "CountrySubentity": [
                                                {
                                                   "TextContent": "<departamentoPartida>"
                                                }
                                            ],
                                            "District": [
                                                {
                                                    "TextContent": "<distritoPartida>"
                                                }
                                            ],
                                            "AddressLine": [
                                                {
                                                    "Line": [
                                                        {
                                                            "TextContent": "<direccionPartida>"
                                                        }
                                                    ]
                                                }
                                            ],
                                            "Country": [
                                                {
                                                    "IdentificationCode": [
                                                        {
                                                            "IdentifierContent": "PE"
                                                        }
                                                    ]
                                                }
                                            ]
                                        }
                                    ],
                                    "DespatchParty": [
                                        {
                                            "AgentParty": [
                                                {
                                                    "PartyLegalEntity": [
                                                        {
                                                            "CompanyID": [
                                                                {
                                                                    "IdentifierContent": "<rucEmisor>",
                                                                    "IdentificationSchemeIdentifier": "06"
                                                                }
                                                            ]
                                                        }
                                                    ]
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "DespatchLine": [
                {
                    "ID": [
                        {
                            "IdentifierContent": 1
                        }
                    ],
                    "Note": [
                        {
                            "TextContent": "TONELADAS"
                        }
                    ],
                    "DeliveredQuantity": [
                        {
                            "QuantityContent": "<cantidadItem>",
                            "QuantityUnitCode": "ZZ"
                        }
                    ],
                    "OrderLineReference": [
                        {
                            "LineID": [
                                {
                                    "IdentifierContent": 1
                                }
                            ]
                        }
                    ],
                    "Item": [
                        {
                            "Description": [
                                {
                                    "TextContent": "<descripcionItem>"
                                }
                            ],
                            "SellersItemIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<codigo>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ]
}
    ';

  // Datos de la venta
    $data = [
        '<ID>' => $facturadorSerie.'-'.$facturadorCorrelativo,
        '<IssueDate>' => $fecha,
        '<IssueTime>' => $hora,
        '<observacion>' =>$zonaInfo[0]['direccion'],
        '<documentoRelacionado>'=>'',
        '<descripcionDocumentoRelacionado>' => ' ',
        '<rucEmisorDocumentoRelacionado>' => '',
        '<motivoTraslado>' => 'Venta sujeta a confirmación del comprador',
        '<rucEmisor>' => $facturadorCodigo,
        '<razonSocialEmisor>' => $facturadorRazon,
      '<ubigeo>'=>$personaMineroDireccion[0]['ubigeo'],
      '<departamento>' => $personaMineroDireccion[0]['departamento'],
      '<provincia>' => $personaMineroDireccion[0]['provincia'] ,
      '<urbanizacion>' => ' ' ,
      '<distrito>' => $personaMineroDireccion[0]['distrito'] ,
      '<direccionEmisor>' => $personaMineroDireccion[0]['direccion'], 
      '<rucReceptor>' => $plantaCodigo,
      '<razonSocialReceptor>' => $plantaRazon,
      '<ubigeoReceptor>'=>$personaPlantaDireccion[0]['ubigeo'],
      '<departamentoReceptor>' => $personaPlantaDireccion[0]['departamento'],
      '<provinciaReceptor>' => $personaPlantaDireccion[0]['provincia'] ,
      '<distritoReceptor>' => $personaPlantaDireccion[0]['distrito'] ,
      '<urbanizacionReceptor>' => ' ' ,
      '<direccionReceptor>' =>$personaPlantaDireccion[0]['direccion'],
      '<electronicMail>' => $facturadorEmail,
      '<rucTransportista>'  => $transportistaCodigo,
      '<razonSocialTransportista>'  => $transportistaRazon,
      '<peso>'  => $pesaje,
      '<fechaTraslado>'  => $fecha,
      '<ubigeoLlegada>'=>$direccionLlegada[0]['ubigeo'],
      '<departamentoLlegada>' => $direccionLlegada[0]['departamento'],
      '<provinciaLlegada>' => $direccionLlegada[0]['provincia'],
      '<distritoLlegada>' => $direccionLlegada[0]['distrito'] ,
      '<urbanizacionLlegada>' => ' ' ,
      '<direccionLlegada>' =>$direccionLlegada[0]['direccion'],
      '<ubigeoPartida>'=>$zonaInfo[0]['ubigeo'],
      '<departamentoPartida>' => $zonaInfo[0]['departamento'],
      '<provinciaPartida>' => $zonaInfo[0]['provincia'] ,
      '<urbanizacionPartida>' => '-' ,
      '<distritoPartida>' => $zonaInfo[0]['distrito'] ,
      '<direccionPartida>' => $zonaInfo[0]['direccion'], 
      '<cantidadItem>' => $pesaje ,
      '<descripcionItem>' => 'MINERAL AURIFERO EN BRUTO A GRANEL, PESO A CONFIRMAR POR EL COMPRADOR' ,
      '<codigo>' => '01A',
      '<registroTransportista>'=> $transportistaMTC
    ];

    // Reemplazar los datos en la plantilla
    $json = str_replace(array_keys($data), array_values($data), $templateJson);

    // Guardar el JSON en un archivo
    $fileName = '../../GRR/'.$data['<rucEmisor>'].'-09-' . $data['<ID>'] . '.json';
    file_put_contents($fileName, $json);

    return $fileName;
}


public function generarJsonGuiaTransportistaEfact($fecha,$hora,$transportistaSerie,$transportistaCorrelativo,$transportistaCodigo,
$transportistaRazon,$facturadorEmail,$personaTransportistaDireccion,$plantaCodigo,$plantaRazon,$personaPlantaDireccion,$facturadorCodigo,$facturadorRazon,
$transportistaMTC,$zonaInfo,$direccionLlegada,$pesaje,$personaMineroDireccion,$placa,$constancia,$carretaPlaca,
$carretaConstancia,$personaConductor,$guiaRemision){
    $templateJson = '{
    "_D": "urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2",
    "_S": "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
    "_B": "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
    "_E": "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2",
    "DespatchAdvice": [
        {
            "UBLVersionID": [
                {
                    "IdentifierContent": "2.1"
                }
            ],
            "CustomizationID": [
                {
                    "IdentifierContent": "2.0"
                }
            ],
            "ID": [
                {
                    "IdentifierContent": "<ID>"
                }
            ],
            "IssueDate": [
                {
                    "DateContent": "<IssueDate>"
                }
            ],
            "IssueTime": [
                {
                    "DateTimeContent": "<IssueTime>"
                }
            ],
            "DespatchAdviceTypeCode": [
                {
                    "IdentifierContent": "31"
                }
            ],
            "Note": [
                {
                    "TextContent": "<observacion>"
                }
            ],
            "LineCountNumeric": [
                {
                    "TextContent": "2"
                }
            ],
            "AdditionalDocumentReference": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "<documentoRelacionado>"
                        }
                    ],
                    "DocumentTypeCode": [
                        {
                            "CodeContent": "09"
                        }
                    ],
                    "DocumentType": [
                        {
                            "TextContent": "Guia de Remisión Remitente"
                        }
                    ],
                    "IssuerParty": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucEmisorDocumentoRelacionado>",
                                            "IdentificationSchemeIdentifier": "6"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "Signature": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "IDSignature"
                        }
                    ],
                    "SignatoryParty": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucEmisor>"
                                        }
                                    ]
                                }
                            ],
                            "PartyName": [
                                {
                                    "Name": [
                                        {
                                            "TextContent": "<razonSocialEmisor>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "DigitalSignatureAttachment": [
                        {
                            "ExternalReference": [
                                {
                                    "URI": [
                                        {
                                            "TextContent": "IDSignature"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "DespatchSupplierParty": [
                {
                    "Party": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucEmisor>",
                                            "IdentificationSchemeIdentifier": "6"
                                        }
                                    ]
                                }
                            ],
                            "PostalAddress": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<ubigeo>"
                                        }
                                    ],
                                    "StreetName": [
                                        {
                                            "TextContent": "<direccionEmisor>"
                                        }
                                    ],
                                    "CitySubdivisionName": [
                                        {
                                            "TextContent": "<urbanizacion>"
                                        }
                                    ],
                                    "CityName": [
                                        {
                                            "TextContent": "<provincia>"
                                        }
                                    ],
                                    "CountrySubentity": [
                                        {
                                            "TextContent": "<departamento>"
                                        }
                                    ],
                                    "District": [
                                        {
                                            "TextContent": "<distrito>"
                                        }
                                    ],
                                    "Country": [
                                        {
                                            "IdentificationCode": [
                                                {
                                                    "IdentifierContent": "PE"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ],
                            "PartyLegalEntity": [
                                {
                                    "RegistrationName": [
                                        {
                                            "TextContent": "<razonSocialEmisor>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "DeliveryCustomerParty": [
                {
                    "Party": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<rucReceptor>",
                                            "IdentificationSchemeIdentifier": "6"
                                        }
                                    ]
                                }
                            ],
                            "PostalAddress": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<ubigeoReceptor>"
                                        }
                                    ],
                                    "StreetName": [
                                        {
                                            "TextContent": "<direccionReceptor>"
                                        }
                                    ],
                                    "CitySubdivisionName": [
                                        {
                                            "TextContent": "<urbanizacionReceptor>"
                                        }
                                    ],
                                    "CityName": [
                                        {
                                            "TextContent": "<provinciaReceptor>"
                                        }
                                    ],
                                    "CountrySubentity": [
                                        {
                                            "TextContent": "<departamentoReceptor>"
                                        }
                                    ],
                                    "District": [
                                        {
                                            "TextContent": "<distritoReceptor>"
                                        }
                                    ],
                                    "Country": [
                                        {
                                            "IdentificationCode": [
                                                {
                                                    "IdentifierContent": "PE"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ],
                            "PartyLegalEntity": [
                                {
                                    "RegistrationName": [
                                        {
                                            "TextContent": "<razonSocialReceptor>"
                                        }
                                    ]
                                }
                            ],
                            "Contact": [
                                {
                                    "ElectronicMail": [
                                        {
                                            "TextContent": "<electronicMail>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "Shipment": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "SUNAT_Envio"
                        }
                    ],
                    "GrossWeightMeasure": [
                        {
                            "MeasureContent": "<peso>",
                            "MeasureUnitCode": "TNE"
                        }
                    ],
                    "SpecialInstructions": [
                        {
                            "TextContent": "SUNAT_Envio_IndicadorRetornoVehiculoEnvaseVacio"
                        }
                    ],
                    "SpecialInstructions": [
                        {
                            "TextContent": "SUNAT_Envio_IndicadorPagadorFlete_Remitente"
                        }
                    ],
                    "ShipmentStage": [
                        {
                            "TransitPeriod": [
                                {
                                    "StartDate": [
                                        {
                                            "DateContent": "<fechaTraslado>"
                                        }
                                    ]
                                }
                            ],
                            "CarrierParty" : [ 
                                {
                                    "PartyLegalEntity" : [ 
                                        {
                                            "CompanyID" : [ 
                                                {
                                                    "IdentifierContent" : "<registroTransportista>"
                                                }
                                            ]
                                        }
                                    ],
                                    "AgentParty" : [ 
                                        {
                                            "PartyLegalEntity" : [ 
                                                {
                                                    "CompanyID" : [ 
                                                        {
                                                            "IdentifierContent" : "<rucTransportista>",
                                                            "IdentificationSchemeIdentifier" : "06"
                                                        }
                                                    ]
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ],
                            "DriverPerson": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<dniConductor>",
                                            "IdentificationSchemeIdentifier": "1"
                                        }
                                    ],
                                    "FirstName": [
                                        {
                                            "TextContent": "<nombreConductor>"
                                        }
                                    ],
                                    "FamilyName": [
                                        {
                                            "TextContent": "<apellidoConductor>"
                                        }
                                    ],
                                    "JobTitle": [
                                        {
                                            "TextContent": "Principal"
                                        }
                                    ],
                                    "IdentityDocumentReference": [
                                        {
                                            "ID": [
                                                {
                                                    "IdentifierContent": "<licenciaConductor>"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "Delivery": [
                        {
                            "DeliveryAddress": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<ubigeoLlegada>"
                                        }
                                    ],
                                    "AddressLine": [
                                        {
                                            "Line": [
                                                {
                                                    "TextContent": "<direccionLlegada>"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ],
                            "Despatch": [
                                {
                                    "DespatchAddress": [
                                        {
                                            "ID": [
                                                {
                                                     "IdentifierContent": "<ubigeoPartida>"
                                                }
                                            ],
                                            "AddressLine": [
                                                {
                                                    "Line": [
                                                        {
                                                            "TextContent": "<direccionPartida>"
                                                        }
                                                    ]
                                                }
                                            ]
                                        }
                                    ],
                                    "DespatchParty": [
                                        {
                                            "PartyIdentification": [
				                                {
				                                    "ID": [
				                                        {
				                                            "IdentifierContent": "<rucRemitente>",
				                                            "IdentificationSchemeIdentifier": "6"
				                                        }
				                                    ]
				                                }
				                            ],
				                            "PostalAddress": [
				                                {
				                                    "ID": [
				                                        {
				                                            "IdentifierContent": "<ubigeoRemitente>"
				                                        }
				                                    ],
				                                    "StreetName": [
				                                        {
				                                            "TextContent": "<direccionRemitente>"
				                                        }
				                                    ],
				                                    "CitySubdivisionName": [
				                                        {
				                                            "TextContent": "<urbanizacionRemitente>"
				                                        }
				                                    ],
				                                    "CityName": [
				                                        {
				                                            "TextContent": "<provinciaRemitente>"
				                                        }
				                                    ],
				                                    "CountrySubentity": [
				                                        {
				                                            "TextContent": "<departamentoRemitente>"
				                                        }
				                                    ],
				                                    "District": [
				                                        {
				                                            "TextContent": "<distritoRemitente>"
				                                        }
				                                    ],
				                                    "Country": [
				                                        {
				                                            "IdentificationCode": [
				                                                {
				                                                    "IdentifierContent": "PE"
				                                                }
				                                            ]
				                                        }
				                                    ]
				                                }
				                            ],
				                            "PartyLegalEntity": [
				                                {
				                                    "RegistrationName": [
				                                        {
				                                            "TextContent": "<razonSocialRemitente>"
				                                        }
				                                    ]
				                                }
				                            ],
				                            "Contact": [
				                                {
				                                    "ElectronicMail": [
				                                        {
				                                            "TextContent": "<correoRemitente>"
				                                        }
				                                    ]
				                                }
				                            ]
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "TransportHandlingUnit": [
                        {
                            "TransportEquipment": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<placa>"
                                        }
                                    ],
                                    "ApplicableTransportMeans": [
                                        {
                                            "RegistrationNationalityID": [
		                                        {
		                                            "IdentifierContent": "<tuce>"
		                                        }
		                                    ]
                                        }
                                    ],
                                    "ShipmentDocumentReference": [
                                        {
                                            "ID": [
		                                        {
		                                            "IdentifierContent": "<tarjetaCirculacion>",
		                                            "IdentificationSchemeIdentifier": "06"
		                                        }
		                                    ]
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "DespatchLine": [
                {
                    "ID": [
                        {
                            "IdentifierContent": 1
                        }
                    ],
                    "Note": [
                        {
                            "TextContent": "TONELADAS"
                        }
                    ],
                    "DeliveredQuantity": [
                        {
                            "QuantityContent": "<cantidadItem>",
                            "QuantityUnitCode": "ZZ"
                        }
                    ],
                    "OrderLineReference": [
                        {
                            "LineID": [
                                {
                                    "IdentifierContent": 1
                                }
                            ]
                        }
                    ],
                    "Item": [
                        {
                            "Description": [
                                {
                                    "TextContent": "<descripcionItem>"
                                }
                            ],
                            "SellersItemIdentification": [
                                {
                                    "ID": [
                                        {
                                            "IdentifierContent": "<codigo>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ]
}';
$placa = str_replace("-", "", $placa);
  // Datos de la venta
    $data = [
        '<ID>' => $transportistaSerie.'-'.$transportistaCorrelativo,
        '<IssueDate>' => $fecha,
        '<IssueTime>' => $hora,
        '<observacion>' =>$zonaInfo[0]['direccion'],
        '<documentoRelacionado>'=>$guiaRemision,
        '<descripcionDocumentoRelacionado>' => ' ',
        '<rucEmisorDocumentoRelacionado>' => $facturadorCodigo,
        '<rucEmisor>' => $transportistaCodigo,
        '<razonSocialEmisor>' => $transportistaRazon,
      '<ubigeo>'=>$personaTransportistaDireccion[0]['ubigeo'],
      '<departamento>' => $personaTransportistaDireccion[0]['departamento'],
      '<provincia>' => $personaTransportistaDireccion[0]['provincia'] ,
      '<urbanizacion>' => ' ' ,
      '<distrito>' => $personaTransportistaDireccion[0]['distrito'] ,
      '<direccionEmisor>' => $personaTransportistaDireccion[0]['direccion'], 
      '<rucReceptor>' => $plantaCodigo,
      '<razonSocialReceptor>' => $plantaRazon,
      '<ubigeoReceptor>'=>$personaPlantaDireccion[0]['ubigeo'],
      '<departamentoReceptor>' => $personaPlantaDireccion[0]['departamento'],
      '<provinciaReceptor>' => $personaPlantaDireccion[0]['provincia'] ,
      '<distritoReceptor>' => $personaPlantaDireccion[0]['distrito'] ,
      '<urbanizacionReceptor>' => ' ' ,
      '<direccionReceptor>' =>$personaPlantaDireccion[0]['direccion'],
      '<electronicMail>' => $facturadorEmail,
      '<rucRemitente>'  => $facturadorCodigo,
      '<razonSocialRemitente>'  => $facturadorRazon,
      '<correoRemitente>'  => $facturadorEmail,
      '<peso>'  => $pesaje,
      '<fechaTraslado>'  => $fecha,
      '<ubigeoLlegada>'=>$direccionLlegada[0]['ubigeo'],
      '<direccionLlegada>' =>$direccionLlegada[0]['direccion'],
      '<ubigeoPartida>'=>$zonaInfo[0]['ubigeo'],
      '<direccionPartida>' => $zonaInfo[0]['direccion'], 
      '<cantidadItem>' => $pesaje ,
      '<descripcionItem>' => 'MINERAL AURIFERO EN BRUTO A GRANEL' ,
      '<codigo>' => '01A',
      '<registroTransportista>'=> $transportistaMTC,
        '<dniConductor>'=>$personaConductor[0]['codigo_identificacion'],
        '<nombreConductor>'=>$personaConductor[0]['nombre'],
        '<apellidoConductor>'=>$personaConductor[0]['apellido_paterno'].' '.$personaConductor[0]['apellido_materno'],
        '<licenciaConductor>'=>$personaConductor[0]['num_licencia_conducir_auto'],
        '<ubigeoRemitente>'=>$personaMineroDireccion[0]['ubigeo'],
        '<direccionRemitente>'=>$personaMineroDireccion[0]['direccion'],
        '<urbanizacionRemitente>'=>'',
        '<provinciaRemitente>'=>$personaMineroDireccion[0]['provincia'] ,
        '<departamentoRemitente>'=>$personaMineroDireccion[0]['departamento'],
        '<distritoRemitente>'=>$personaMineroDireccion[0]['distrito'],
        '<tarjetaCirculacion>'=>'',
        '<tuce>'=>$constancia,
        '<placa>'=>$placa
    ];

    // Reemplazar los datos en la plantilla
    $json = str_replace(array_keys($data), array_values($data), $templateJson);

    // Guardar el JSON en un archivo
    $fileName = '../../GRT/'.$data['<rucEmisor>'].'-31-' . $data['<ID>'] . '.json';
    file_put_contents($fileName, $json);

    return $fileName;
}
public function enviarEfactDocumentoElectronico($token, $ubicacion) {

  // Asegúrate de que la ruta sea válida
  $ubicacion = realpath($ubicacion);
  if (!$ubicacion) {
      echo "El archivo no existe o la ruta es incorrecta.";
      return;
  }

  if (!is_readable($ubicacion)) {
      echo "El archivo no es accesible o no tiene permisos de lectura.";
      return;
  }

  // Obtén el nombre del archivo
  $fileName = basename($ubicacion);  // Esto conserva el nombre del archivo

  // Inicializar cURL
  $curl = curl_init();

  // Configurar los datos POST con multipart/form-data
  curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://ose-gw1.efact.pe/api-efact-ose/v1/document/',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_HTTPHEADER => array(
          'Authorization: Bearer ' . $token,  // Añadir el token de autorización
      ),
      CURLOPT_POSTFIELDS => array(
          'file' => new CURLFile($ubicacion, 'application/json', $fileName)  // Aquí se especifica el nombre del archivo y su tipo
      ),
  ));

  // Ejecutar la solicitud
  $response = curl_exec($curl);

  // Verifica si hubo un error en la ejecución de la solicitud cURL
  if(curl_errno($curl)) {
      // Mostrar el error de cURL si existe
  } else {
      // Si la solicitud fue exitosa, muestra la respuesta
    
  }

  // Cerrar cURL
  curl_close($curl);

  return json_decode($response);
}

public function enviarEfactDocumentoElectronicoProduccion($token, $ubicacion) {

    // Asegúrate de que la ruta sea válida
    $ubicacion = realpath($ubicacion);
    if (!$ubicacion) {
        echo "El archivo no existe o la ruta es incorrecta.";
        return;
    }
  
    if (!is_readable($ubicacion)) {
        echo "El archivo no es accesible o no tiene permisos de lectura.";
        return;
    }
  
    // Obtén el nombre del archivo
    $fileName = basename($ubicacion);  // Esto conserva el nombre del archivo
  
    // Inicializar cURL
    $curl = curl_init();
  
    // Configurar los datos POST con multipart/form-data
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://ose.efact.pe/api-efact-ose/v1/document/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $token,  // Añadir el token de autorización
        ),
        CURLOPT_POSTFIELDS => array(
            'file' => new CURLFile($ubicacion, 'application/json', $fileName)  // Aquí se especifica el nombre del archivo y su tipo
        ),
    ));
  
    // Ejecutar la solicitud
    $response = curl_exec($curl);
  
    // Verifica si hubo un error en la ejecución de la solicitud cURL
    if(curl_errno($curl)) {
        // Mostrar el error de cURL si existe
    } else {
        // Si la solicitud fue exitosa, muestra la respuesta
      
    }
  
    // Cerrar cURL
    curl_close($curl);
  
    return json_decode($response);
  }
public function consultarDocumentoEfact($token, $facturarDocumento) {


  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://ose-gw1.efact.pe/api-efact-ose/v1/pdf/'.$facturarDocumento,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'Authorization: Bearer ' . $token,
    ),
  ));
  
  $response = curl_exec($curl);
  
  curl_close($curl);
  return json_decode($response);

}

public function consultarDocumentoEfactProduccion($token, $facturarDocumento) {


    $curl = curl_init();
  
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://ose.efact.pe/api-efact-ose/v1/pdf/'.$facturarDocumento,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer ' . $token,
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    return json_decode($response);
  
  }

public function generarJsonFactura($fecha,$hora,$subtotal,$igv,$totalFactura,
$detraccion,$netoPago,$facturadorCodigo,$facturadorRazon,$facturadorEmail,$facturadorSerie,$facturadorCorrelativo,
$plantaCodigo,$plantaRazon,$personaMineroDireccion,$personaPlantaDireccion,$nueva_fecha,$textoLotes,$montoLetrasFactura) {

  

// Plantilla JSON como un string (recuerda que debe estar bien formateado)
 $templateJson = '{
        "_D" : "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2",
        "_S" : "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
        "_B" : "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
        "_E" : "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2",
        "Invoice" : [ 
            {
                "UBLVersionID" : [ 
                    {
                        "IdentifierContent" : "2.1"
                    }
                ],
                "CustomizationID" : [ 
                    {
                        "IdentifierContent" : "2.0"
                    }
                ],
                "ID" : [ 
                    {
                        "IdentifierContent" : "<ID>"
                    }
                ],
                "IssueDate" : [ 
                    {
                        "DateContent" : "<IssueDate>"
                    }
                ],
		"IssueTime" : [ 
                    {
                        "DateTimeContent" : "<IssueTime>"
                    }
                ],
                "InvoiceTypeCode" : [ 
                    {
                        "CodeContent" : "01",
                        "CodeListNameText" : "Tipo de Documento",
                        "CodeListSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51",
                        "CodeListIdentifier" : "1001",
                        "CodeNameText" : "Tipo de Operacion",
                        "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01",
                        "CodeListAgencyNameText" : "PE:SUNAT"
                    }
                ],
                "Note" : [ 
                    {
                        "TextContent" : "<montoLetras>",
                        "LanguageLocaleIdentifier" : "1000"
                    },
                     {
                    "TextContent": "Operación sujeta a detracción",
                    "LanguageLocaleIdentifier": "2006"
                    }
                ],
                "DocumentCurrencyCode" : [ 
                    {
                        "CodeContent" : "<moneda>",
                        "CodeListIdentifier" : "ISO 4217 Alpha",
                        "CodeListNameText" : "Currency",
                        "CodeListAgencyNameText" : "United Nations Economic Commission for Europe"
                    }
                ],
                "LineCountNumeric" : [ 
                    {
                        "NumericContent" : 2
                    }
                ],
                "Signature" : [ 
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "IDSignature"
                            }
                        ],
                        "SignatoryParty" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "TextContent" : "<rucEmisor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "DigitalSignatureAttachment" : [ 
                            {
                                "ExternalReference" : [ 
                                    {
                                        "URI" : [ 
                                            {
                                                "TextContent" : "IDSignature"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "AccountingSupplierParty" : [ 
                    {
                        "Party" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<rucEmisor>",
                                                "IdentificationSchemeIdentifier" : "6",
                                                "IdentificationSchemeNameText" : "Documento de Identidad",
                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT",
                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"
                                            }
                                        ]
                                    }
                                ],
                                "PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyLegalEntity" : [ 
                                    {
                                        "RegistrationName" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ],
                                        "RegistrationAddress" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "<ubigeo>",
                                                        "IdentificationSchemeAgencyNameText" : "PE:INEI",
                                                        "IdentificationSchemeNameText" : "Ubigeos"
                                                    }
                                                ],
                                                "AddressTypeCode" : [ 
                                                    {
                                                        "CodeContent" : "0000",
                                                        "CodeListAgencyNameText" : "PE:SUNAT",
                                                        "CodeListNameText" : "Establecimientos anexos"
                                                    }
                                                ],
                                                "CityName" : [ 
                                                    {
                                                        "TextContent" : "<departamento>"
                                                    }
                                                ],
                                                "CountrySubentity" : [ 
                                                    {
                                                        "TextContent" : "<ciudad>"
                                                    }
                                                ],
                                                "District" : [ 
                                                    {
                                                        "TextContent" : "<distrito>"
                                                    }
                                                ],
                                                "AddressLine" : [ 
                                                    {
                                                        "Line" : [ 
                                                            {
                                                                "TextContent" : "<direccionEmisor>"
                                                            }
                                                        ]
                                                    }
                                                ],
                                                "Country" : [ 
                                                    {
                                                        "IdentificationCode" : [ 
                                                            {
                                                                "CodeContent" : "PE",
                                                                "CodeListIdentifier" : "ISO 3166-1",
                                                                "CodeListAgencyNameText" : "United Nations Economic Commission for Europe",
                                                                "CodeListNameText" : "Country"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "AccountingCustomerParty" : [ 
                    {
                        "Party" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<rucReceptor>",
                                                "IdentificationSchemeIdentifier" : "6",
                                                "IdentificationSchemeNameText" : "Documento de Identidad",
                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT",
                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"
                                            }
                                        ]
                                    }
                                ],
				"PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonReceptor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyLegalEntity" : [ 
                                    {
                                        "RegistrationName" : [ 
                                            {
                                                "TextContent" : "<razonReceptor>"
                                            }
                                        ],
                                        "RegistrationAddress" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "<ubigeoReceptor>",
                                                        "IdentificationSchemeAgencyNameText" : "PE:INEI",
                                                        "IdentificationSchemeNameText" : "Ubigeos"
                                                    }
                                                ],
                                                "CityName" : [ 
                                                    {
                                                        "TextContent" : "<departamentoReceptor>"
                                                    }
                                                ],
                                                "CountrySubentity" : [ 
                                                    {
                                                        "TextContent" : "<ciudadReceptor>"
                                                    }
                                                ],
                                                "District" : [ 
                                                    {
                                                        "TextContent" : "<distritoReceptor>"
                                                    }
                                                ],
                                                "AddressLine" : [ 
                                                    {
                                                        "Line" : [ 
                                                            {
                                                                "TextContent" : "<direccionReceptor>"
                                                            }
                                                        ]
                                                    }
                                                ],
                                                "Country" : [ 
                                                    {
                                                        "IdentificationCode" : [ 
                                                            {
                                                                "CodeContent" : "PE",
                                                                "CodeListIdentifier" : "ISO 3166-1",
                                                                "CodeListAgencyNameText" : "United Nations Economic Commission for Europe",
                                                                "CodeListNameText" : "Country"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ],
                                "Contact" : [ 
                                    {
                                        "ElectronicMail" : [ 
                                            {
                                                "TextContent" : "<electronicMail>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "PaymentMeans": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "Detraccion"
                        }
                    ],
                    "PaymentMeansCode": [
                        {
                            "CodeContent": "999"
                        }
                    ],
                    "PayeeFinancialAccount": [
                        {
                            "ID": [
                                {
                                    "IdentifierContent": "00181066180"
                                }
                            ]
                        }
                    ]
                }
            ],
            "PaymentTerms": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "Detraccion"
                        }
                    ],
                    "PaymentMeansID": [
                        {
                            "IdentifierContent": "031"
                        }
                    ],
                    "Note": [
                        {
                            "TextContent": "<netoPagar>"
                        }
                    ],
                    "PaymentPercent": [
                        {
                            "NumericContent": "10.00"
                        }
                    ],
                    "Amount": [
                        {
                            "AmountContent": "<montoDetraccion>",
                            "AmountCurrencyIdentifier": "PEN"
                        }
                    ]
                },
                {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "FormaPago"
                            }
                        ],
                        "PaymentMeansID" : [ 
                            {
                                "IdentifierContent" : "Credito"
                            }
                        ],
                        "Amount" : [ 
                            {
                                "AmountContent" : "<netoPagar>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ]
                    },
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "FormaPago"
                            }
                        ],
                        "PaymentMeansID" : [ 
                            {
                                "IdentifierContent" : "Cuota001"
                            }
                        ],
                        "Amount" : [ 
                            {
                                "AmountContent" : "<netoPagar>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PaymentDueDate" : [ 
                            {
                                "DateContent" : "<fechaCredito>"
                            }
                        ]
                    }
            ],
                "TaxTotal" : [ 
                    {
                        "TaxAmount" : [ 
                            {
                                "AmountContent" : "<montoIGV>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "TaxSubtotal" : [ 
                            {
                                "TaxableAmount" : [ 
                                    {
                                        "AmountContent" : "<subTotal>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxAmount" : [ 
                                    {
                                        "AmountContent" : "<montoIGV>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxCategory" : [ 
                                    {
                                        "TaxScheme" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "1000",
                                                        "IdentificationSchemeNameText" : "Codigo de tributos",
                                                        "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05",
                                                        "IdentificationSchemeAgencyNameText" : "PE:SUNAT"
                                                    }
                                                ],
                                                "Name" : [ 
                                                    {
                                                        "TextContent" : "IGV"
                                                    }
                                                ],
                                                "TaxTypeCode" : [ 
                                                    {
                                                        "CodeContent" : "VAT"
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "LegalMonetaryTotal" : [ 
                    {
                        "LineExtensionAmount" : [ 
                            {
                                "AmountContent" : "<subTotal>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "TaxInclusiveAmount" : [ 
                            {
                                "AmountContent" : "<total>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PayableAmount" : [ 
                            {
                                "AmountContent" : "<total>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ]
                    }
                ],
                "InvoiceLine" : [ 
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "1"
                            }
                        ],
                        "Note" : [ 
                            {
                                "TextContent" : "UNIDAD"
                            }
                        ],
                        "InvoicedQuantity" : [ 
                            {
                                "QuantityContent" : "<cantidadItems>",
                                "QuantityUnitCode" : "ZZ",
                                "QuantityUnitCodeListIdentifier" : "UN/ECE rec 20",
                                "QuantityUnitCodeListAgencyNameText" : "United Nations Economic Commission for Europe"
                            }
                        ],
                        "LineExtensionAmount" : [ 
                            {
                                "AmountContent" : "<subTotal>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PricingReference" : [ 
                            {
                                "AlternativeConditionPrice" : [ 
                                    {
                                        "PriceAmount" : [ 
                                            {
                                                "AmountContent" : "<total>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "PriceTypeCode" : [ 
                                            {
                                                "CodeContent" : "01",
                                                "CodeListNameText" : "Tipo de Precio",
                                                "CodeListAgencyNameText" : "PE:SUNAT",
                                                "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "TaxTotal" : [ 
                            {
                                "TaxAmount" : [ 
                                    {
                                        "AmountContent" : "<montoIGV>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxSubtotal" : [ 
                                    {
                                        "TaxableAmount" : [ 
                                            {
                                                "AmountContent" : "<subTotal>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "TaxAmount" : [ 
                                            {
                                                "AmountContent" : "<montoIGV>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "TaxCategory" : [ 
                                            {
                                                "Percent" : [ 
                                                    {
                                                        "NumericContent" : 18.00
                                                    }
                                                ],
                                                "TaxExemptionReasonCode" : [ 
                                                    {
                                                        "CodeContent" : "10",
                                                        "CodeListAgencyNameText" : "PE:SUNAT",
                                                        "CodeListNameText" : "Afectacion del IGV",
                                                        "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07"
                                                    }
                                                ],
                                                "TaxScheme" : [ 
                                                    {
                                                        "ID" : [ 
                                                            {
                                                                "IdentifierContent" : "1000",
                                                                "IdentificationSchemeNameText" : "Codigo de tributos",
                                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05",
                                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT"
                                                            }
                                                        ],
                                                        "Name" : [ 
                                                            {
                                                                "TextContent" : "IGV"
                                                            }
                                                        ],
                                                        "TaxTypeCode" : [ 
                                                            {
                                                                "CodeContent" : "VAT"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "Item" : [ 
                            {
                                "Description" : [ 
                                    {
                                        "TextContent" : "<descripcionItemFactura>"
                                    }
                                ],
                                "SellersItemIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<codigoFactura>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "Price" : [ 
                            {
                                "PriceAmount" : [ 
                                    {
                                        "AmountContent" : "<subTotal>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ]
                            }
                        ]
                    }
                  
                ]
            }
        ]
}
';

// Datos de la venta
$data = [
    '<ID>' => $facturadorSerie.'-'.$facturadorCorrelativo,
    '<IssueDate>' => $fecha,
    '<IssueTime>' => $hora,
    '<moneda>' => "USD",
    '<rucEmisor>' => $facturadorCodigo,
    '<razonEmisor>' => $facturadorRazon,
   '<ubigeo>'=>$personaMineroDireccion[0]['ubigeo'],
   '<departamento>' => $personaMineroDireccion[0]['departamento'],
   '<ciudad>' => $personaMineroDireccion[0]['provincia'] ,
   '<distrito>' => $personaMineroDireccion[0]['distrito'] ,
   '<direccionEmisor>' => $personaMineroDireccion[0]['direccion'], 
   '<rucReceptor>' => $plantaCodigo,
   '<razonReceptor>' => $plantaRazon,
  '<ubigeoReceptor>'=>$personaPlantaDireccion[0]['ubigeo'],
  '<departamentoReceptor>' => $personaPlantaDireccion[0]['departamento'],
   '<ciudadReceptor>' => $personaPlantaDireccion[0]['provincia'] ,
   '<distritoReceptor>' => $personaPlantaDireccion[0]['distrito'] ,
   '<direccionReceptor>' =>$personaPlantaDireccion[0]['direccion'],
   '<electronicMail>' => $facturadorEmail,
   '<montoIGV>'  => round($igv,2),
   '<subTotal>'  => round($subtotal,2),
   '<total>'  => round($totalFactura,2),
   '<cantidadItems>'  => '1',
   '<descripcionItemFactura>'  => $textoLotes,
   '<montoDetraccion>'  => round($detraccion,2),
   '<netoPagar>'  => round($netoPago,2),
   '<codigoFactura>' => '01',
   '<fechaCredito>' =>$nueva_fecha,
   '<montoLetras>'=>$montoLetrasFactura
];

// Reemplazar los datos en la plantilla
$json = str_replace(array_keys($data), array_values($data), $templateJson);

// Guardar el JSON en un archivo
$fileName = '../../factura/'.$data['<rucEmisor>'] .'-01-'. $data['<ID>'] . '.json';
file_put_contents($fileName, $json);

return $fileName;



}

public function generarJsonFacturaTransportista($fecha,$hora,$subtotal,$igv,$totalFactura,
$detraccion,$netoPago,$facturadorCodigo,$facturadorRazon,$facturadorEmail,$facturadorSerie,$facturadorCorrelativo,
$plantaCodigo,$plantaRazon,$personaMineroDireccion,$personaPlantaDireccion,$nueva_fecha,$textoLotes,$montoLetrasFactura) {

  

// Plantilla JSON como un string (recuerda que debe estar bien formateado)
 $templateJson = '{
        "_D" : "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2",
        "_S" : "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
        "_B" : "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
        "_E" : "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2",
        "Invoice" : [ 
            {
                "UBLVersionID" : [ 
                    {
                        "IdentifierContent" : "2.1"
                    }
                ],
                "CustomizationID" : [ 
                    {
                        "IdentifierContent" : "2.0"
                    }
                ],
                "ID" : [ 
                    {
                        "IdentifierContent" : "<ID>"
                    }
                ],
                "IssueDate" : [ 
                    {
                        "DateContent" : "<IssueDate>"
                    }
                ],
		"IssueTime" : [ 
                    {
                        "DateTimeContent" : "<IssueTime>"
                    }
                ],
                "InvoiceTypeCode" : [ 
                    {
                        "CodeContent" : "01",
                        "CodeListNameText" : "Tipo de Documento",
                        "CodeListSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51",
                        "CodeListIdentifier" : "1001",
                        "CodeNameText" : "Tipo de Operacion",
                        "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01",
                        "CodeListAgencyNameText" : "PE:SUNAT"
                    }
                ],
                "Note" : [ 
                    {
                        "TextContent" : "<montoLetras>",
                        "LanguageLocaleIdentifier" : "1000"
                    },
                     {
                    "TextContent": "Operación sujeta a detracción",
                    "LanguageLocaleIdentifier": "2006"
                    }
                ],
                "DocumentCurrencyCode" : [ 
                    {
                        "CodeContent" : "<moneda>",
                        "CodeListIdentifier" : "ISO 4217 Alpha",
                        "CodeListNameText" : "Currency",
                        "CodeListAgencyNameText" : "United Nations Economic Commission for Europe"
                    }
                ],
                "LineCountNumeric" : [ 
                    {
                        "NumericContent" : 2
                    }
                ],
                "Signature" : [ 
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "IDSignature"
                            }
                        ],
                        "SignatoryParty" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "TextContent" : "<rucEmisor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "DigitalSignatureAttachment" : [ 
                            {
                                "ExternalReference" : [ 
                                    {
                                        "URI" : [ 
                                            {
                                                "TextContent" : "IDSignature"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "AccountingSupplierParty" : [ 
                    {
                        "Party" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<rucEmisor>",
                                                "IdentificationSchemeIdentifier" : "6",
                                                "IdentificationSchemeNameText" : "Documento de Identidad",
                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT",
                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"
                                            }
                                        ]
                                    }
                                ],
                                "PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyLegalEntity" : [ 
                                    {
                                        "RegistrationName" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ],
                                        "RegistrationAddress" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "<ubigeo>",
                                                        "IdentificationSchemeAgencyNameText" : "PE:INEI",
                                                        "IdentificationSchemeNameText" : "Ubigeos"
                                                    }
                                                ],
                                                "AddressTypeCode" : [ 
                                                    {
                                                        "CodeContent" : "0000",
                                                        "CodeListAgencyNameText" : "PE:SUNAT",
                                                        "CodeListNameText" : "Establecimientos anexos"
                                                    }
                                                ],
                                                "CityName" : [ 
                                                    {
                                                        "TextContent" : "<departamento>"
                                                    }
                                                ],
                                                "CountrySubentity" : [ 
                                                    {
                                                        "TextContent" : "<ciudad>"
                                                    }
                                                ],
                                                "District" : [ 
                                                    {
                                                        "TextContent" : "<distrito>"
                                                    }
                                                ],
                                                "AddressLine" : [ 
                                                    {
                                                        "Line" : [ 
                                                            {
                                                                "TextContent" : "<direccionEmisor>"
                                                            }
                                                        ]
                                                    }
                                                ],
                                                "Country" : [ 
                                                    {
                                                        "IdentificationCode" : [ 
                                                            {
                                                                "CodeContent" : "PE",
                                                                "CodeListIdentifier" : "ISO 3166-1",
                                                                "CodeListAgencyNameText" : "United Nations Economic Commission for Europe",
                                                                "CodeListNameText" : "Country"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "AccountingCustomerParty" : [ 
                    {
                        "Party" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<rucReceptor>",
                                                "IdentificationSchemeIdentifier" : "6",
                                                "IdentificationSchemeNameText" : "Documento de Identidad",
                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT",
                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"
                                            }
                                        ]
                                    }
                                ],
				"PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonReceptor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyLegalEntity" : [ 
                                    {
                                        "RegistrationName" : [ 
                                            {
                                                "TextContent" : "<razonReceptor>"
                                            }
                                        ],
                                        "RegistrationAddress" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "<ubigeoReceptor>",
                                                        "IdentificationSchemeAgencyNameText" : "PE:INEI",
                                                        "IdentificationSchemeNameText" : "Ubigeos"
                                                    }
                                                ],
                                                "CityName" : [ 
                                                    {
                                                        "TextContent" : "<departamentoReceptor>"
                                                    }
                                                ],
                                                "CountrySubentity" : [ 
                                                    {
                                                        "TextContent" : "<ciudadReceptor>"
                                                    }
                                                ],
                                                "District" : [ 
                                                    {
                                                        "TextContent" : "<distritoReceptor>"
                                                    }
                                                ],
                                                "AddressLine" : [ 
                                                    {
                                                        "Line" : [ 
                                                            {
                                                                "TextContent" : "<direccionReceptor>"
                                                            }
                                                        ]
                                                    }
                                                ],
                                                "Country" : [ 
                                                    {
                                                        "IdentificationCode" : [ 
                                                            {
                                                                "CodeContent" : "PE",
                                                                "CodeListIdentifier" : "ISO 3166-1",
                                                                "CodeListAgencyNameText" : "United Nations Economic Commission for Europe",
                                                                "CodeListNameText" : "Country"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ],
                                "Contact" : [ 
                                    {
                                        "ElectronicMail" : [ 
                                            {
                                                "TextContent" : "<electronicMail>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "PaymentMeans": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "Detraccion"
                        }
                    ],
                    "PaymentMeansCode": [
                        {
                            "CodeContent": "999"
                        }
                    ],
                    "PayeeFinancialAccount": [
                        {
                            "ID": [
                                {
                                    "IdentifierContent": "00181066180"
                                }
                            ]
                        }
                    ]
                }
            ],
            "PaymentTerms": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "Detraccion"
                        }
                    ],
                    "PaymentMeansID": [
                        {
                            "IdentifierContent": "027"
                        }
                    ],
                    "Note": [
                        {
                            "TextContent": "<netoPagar>"
                        }
                    ],
                    "PaymentPercent": [
                        {
                            "NumericContent": "4.00"
                        }
                    ],
                    "Amount": [
                        {
                            "AmountContent": "<montoDetraccion>",
                            "AmountCurrencyIdentifier": "PEN"
                        }
                    ]
                },
                {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "FormaPago"
                            }
                        ],
                        "PaymentMeansID" : [ 
                            {
                                "IdentifierContent" : "Credito"
                            }
                        ],
                        "Amount" : [ 
                            {
                                "AmountContent" : "<netoPagar>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ]
                    },
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "FormaPago"
                            }
                        ],
                        "PaymentMeansID" : [ 
                            {
                                "IdentifierContent" : "Cuota001"
                            }
                        ],
                        "Amount" : [ 
                            {
                                "AmountContent" : "<netoPagar>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PaymentDueDate" : [ 
                            {
                                "DateContent" : "<fechaCredito>"
                            }
                        ]
                    }
            ],
                "TaxTotal" : [ 
                    {
                        "TaxAmount" : [ 
                            {
                                "AmountContent" : "<montoIGV>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "TaxSubtotal" : [ 
                            {
                                "TaxableAmount" : [ 
                                    {
                                        "AmountContent" : "<subTotal>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxAmount" : [ 
                                    {
                                        "AmountContent" : "<montoIGV>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxCategory" : [ 
                                    {
                                        "TaxScheme" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "1000",
                                                        "IdentificationSchemeNameText" : "Codigo de tributos",
                                                        "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05",
                                                        "IdentificationSchemeAgencyNameText" : "PE:SUNAT"
                                                    }
                                                ],
                                                "Name" : [ 
                                                    {
                                                        "TextContent" : "IGV"
                                                    }
                                                ],
                                                "TaxTypeCode" : [ 
                                                    {
                                                        "CodeContent" : "VAT"
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "LegalMonetaryTotal" : [ 
                    {
                        "LineExtensionAmount" : [ 
                            {
                                "AmountContent" : "<subTotal>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "TaxInclusiveAmount" : [ 
                            {
                                "AmountContent" : "<total>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PayableAmount" : [ 
                            {
                                "AmountContent" : "<total>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ]
                    }
                ],
                "InvoiceLine" : [ 
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "1"
                            }
                        ],
                        "Note" : [ 
                            {
                                "TextContent" : "UNIDAD"
                            }
                        ],
                        "InvoicedQuantity" : [ 
                            {
                                "QuantityContent" : "<cantidadItems>",
                                "QuantityUnitCode" : "ZZ",
                                "QuantityUnitCodeListIdentifier" : "UN/ECE rec 20",
                                "QuantityUnitCodeListAgencyNameText" : "United Nations Economic Commission for Europe"
                            }
                        ],
                        "LineExtensionAmount" : [ 
                            {
                                "AmountContent" : "<subTotal>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PricingReference" : [ 
                            {
                                "AlternativeConditionPrice" : [ 
                                    {
                                        "PriceAmount" : [ 
                                            {
                                                "AmountContent" : "<total>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "PriceTypeCode" : [ 
                                            {
                                                "CodeContent" : "01",
                                                "CodeListNameText" : "Tipo de Precio",
                                                "CodeListAgencyNameText" : "PE:SUNAT",
                                                "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "TaxTotal" : [ 
                            {
                                "TaxAmount" : [ 
                                    {
                                        "AmountContent" : "<montoIGV>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxSubtotal" : [ 
                                    {
                                        "TaxableAmount" : [ 
                                            {
                                                "AmountContent" : "<subTotal>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "TaxAmount" : [ 
                                            {
                                                "AmountContent" : "<montoIGV>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "TaxCategory" : [ 
                                            {
                                                "Percent" : [ 
                                                    {
                                                        "NumericContent" : 18.00
                                                    }
                                                ],
                                                "TaxExemptionReasonCode" : [ 
                                                    {
                                                        "CodeContent" : "10",
                                                        "CodeListAgencyNameText" : "PE:SUNAT",
                                                        "CodeListNameText" : "Afectacion del IGV",
                                                        "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07"
                                                    }
                                                ],
                                                "TaxScheme" : [ 
                                                    {
                                                        "ID" : [ 
                                                            {
                                                                "IdentifierContent" : "1000",
                                                                "IdentificationSchemeNameText" : "Codigo de tributos",
                                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05",
                                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT"
                                                            }
                                                        ],
                                                        "Name" : [ 
                                                            {
                                                                "TextContent" : "IGV"
                                                            }
                                                        ],
                                                        "TaxTypeCode" : [ 
                                                            {
                                                                "CodeContent" : "VAT"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "Item" : [ 
                            {
                                "Description" : [ 
                                    {
                                        "TextContent" : "<descripcionItemFactura>"
                                    }
                                ],
                                "SellersItemIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<codigoFactura>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "Price" : [ 
                            {
                                "PriceAmount" : [ 
                                    {
                                        "AmountContent" : "<subTotal>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ]
                            }
                        ]
                    }
                  
                ]
            }
        ]
}
';

// Datos de la venta
$data = [
    '<ID>' => $facturadorSerie.'-'.$facturadorCorrelativo,
    '<IssueDate>' => $fecha,
    '<IssueTime>' => $hora,
    '<moneda>' => "USD",
    '<rucEmisor>' => $facturadorCodigo,
    '<razonEmisor>' => $facturadorRazon,
   '<ubigeo>'=>$personaMineroDireccion[0]['ubigeo'],
   '<departamento>' => $personaMineroDireccion[0]['departamento'],
   '<ciudad>' => $personaMineroDireccion[0]['provincia'] ,
   '<distrito>' => $personaMineroDireccion[0]['distrito'] ,
   '<direccionEmisor>' => $personaMineroDireccion[0]['direccion'], 
   '<rucReceptor>' => $plantaCodigo,
   '<razonReceptor>' => $plantaRazon,
  '<ubigeoReceptor>'=>$personaPlantaDireccion[0]['ubigeo'],
  '<departamentoReceptor>' => $personaPlantaDireccion[0]['departamento'],
   '<ciudadReceptor>' => $personaPlantaDireccion[0]['provincia'] ,
   '<distritoReceptor>' => $personaPlantaDireccion[0]['distrito'] ,
   '<direccionReceptor>' =>$personaPlantaDireccion[0]['direccion'],
   '<electronicMail>' => $facturadorEmail,
   '<montoIGV>'  => round($igv,2),
   '<subTotal>'  => round($subtotal,2),
   '<total>'  => round($totalFactura,2),
   '<cantidadItems>'  => '1',
   '<descripcionItemFactura>'  => $textoLotes,
   '<montoDetraccion>'  => round($detraccion,2),
   '<netoPagar>'  => round($netoPago,2),
   '<codigoFactura>' => '01',
   '<fechaCredito>' =>$nueva_fecha,
   '<montoLetras>'=>$montoLetrasFactura
];

// Reemplazar los datos en la plantilla
$json = str_replace(array_keys($data), array_values($data), $templateJson);

// Guardar el JSON en un archivo
$fileName = '../../factura/'.$data['<rucEmisor>'] .'-01-'. $data['<ID>'] . '.json';
file_put_contents($fileName, $json);

return $fileName;



}

public function generarJsonFacturaCarguio($fecha,$hora,$subtotal,$igv,$totalFactura,
$detraccion,$netoPago,$facturadorCodigo,$facturadorRazon,$facturadorEmail,$facturadorSerie,$facturadorCorrelativo,
$plantaCodigo,$plantaRazon,$personaMineroDireccion,$personaPlantaDireccion,$nueva_fecha,$textoLotes,$montoLetrasFactura,$pesoLotes) {

  

// Plantilla JSON como un string (recuerda que debe estar bien formateado)
 $templateJson = '{
        "_D" : "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2",
        "_S" : "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
        "_B" : "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
        "_E" : "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2",
        "Invoice" : [ 
            {
                "UBLVersionID" : [ 
                    {
                        "IdentifierContent" : "2.1"
                    }
                ],
                "CustomizationID" : [ 
                    {
                        "IdentifierContent" : "2.0"
                    }
                ],
                "ID" : [ 
                    {
                        "IdentifierContent" : "<ID>"
                    }
                ],
                "IssueDate" : [ 
                    {
                        "DateContent" : "<IssueDate>"
                    }
                ],
		"IssueTime" : [ 
                    {
                        "DateTimeContent" : "<IssueTime>"
                    }
                ],
                "InvoiceTypeCode" : [ 
                    {
                        "CodeContent" : "01",
                        "CodeListNameText" : "Tipo de Documento",
                        "CodeListSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51",
                        "CodeListIdentifier" : "1001",
                        "CodeNameText" : "Tipo de Operacion",
                        "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01",
                        "CodeListAgencyNameText" : "PE:SUNAT"
                    }
                ],
                "Note" : [ 
                    {
                        "TextContent" : "<montoLetras>",
                        "LanguageLocaleIdentifier" : "1000"
                    },
                     {
                    "TextContent": "Operación sujeta a detracción",
                    "LanguageLocaleIdentifier": "2006"
                    }
                ],
                "DocumentCurrencyCode" : [ 
                    {
                        "CodeContent" : "<moneda>",
                        "CodeListIdentifier" : "ISO 4217 Alpha",
                        "CodeListNameText" : "Currency",
                        "CodeListAgencyNameText" : "United Nations Economic Commission for Europe"
                    }
                ],
                "LineCountNumeric" : [ 
                    {
                        "NumericContent" : 2
                    }
                ],
                "Signature" : [ 
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "IDSignature"
                            }
                        ],
                        "SignatoryParty" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "TextContent" : "<rucEmisor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "DigitalSignatureAttachment" : [ 
                            {
                                "ExternalReference" : [ 
                                    {
                                        "URI" : [ 
                                            {
                                                "TextContent" : "IDSignature"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "AccountingSupplierParty" : [ 
                    {
                        "Party" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<rucEmisor>",
                                                "IdentificationSchemeIdentifier" : "6",
                                                "IdentificationSchemeNameText" : "Documento de Identidad",
                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT",
                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"
                                            }
                                        ]
                                    }
                                ],
                                "PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyLegalEntity" : [ 
                                    {
                                        "RegistrationName" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ],
                                        "RegistrationAddress" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "<ubigeo>",
                                                        "IdentificationSchemeAgencyNameText" : "PE:INEI",
                                                        "IdentificationSchemeNameText" : "Ubigeos"
                                                    }
                                                ],
                                                "AddressTypeCode" : [ 
                                                    {
                                                        "CodeContent" : "0000",
                                                        "CodeListAgencyNameText" : "PE:SUNAT",
                                                        "CodeListNameText" : "Establecimientos anexos"
                                                    }
                                                ],
                                                "CityName" : [ 
                                                    {
                                                        "TextContent" : "<departamento>"
                                                    }
                                                ],
                                                "CountrySubentity" : [ 
                                                    {
                                                        "TextContent" : "<ciudad>"
                                                    }
                                                ],
                                                "District" : [ 
                                                    {
                                                        "TextContent" : "<distrito>"
                                                    }
                                                ],
                                                "AddressLine" : [ 
                                                    {
                                                        "Line" : [ 
                                                            {
                                                                "TextContent" : "<direccionEmisor>"
                                                            }
                                                        ]
                                                    }
                                                ],
                                                "Country" : [ 
                                                    {
                                                        "IdentificationCode" : [ 
                                                            {
                                                                "CodeContent" : "PE",
                                                                "CodeListIdentifier" : "ISO 3166-1",
                                                                "CodeListAgencyNameText" : "United Nations Economic Commission for Europe",
                                                                "CodeListNameText" : "Country"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "AccountingCustomerParty" : [ 
                    {
                        "Party" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<rucReceptor>",
                                                "IdentificationSchemeIdentifier" : "6",
                                                "IdentificationSchemeNameText" : "Documento de Identidad",
                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT",
                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"
                                            }
                                        ]
                                    }
                                ],
				"PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonReceptor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyLegalEntity" : [ 
                                    {
                                        "RegistrationName" : [ 
                                            {
                                                "TextContent" : "<razonReceptor>"
                                            }
                                        ],
                                        "RegistrationAddress" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "<ubigeoReceptor>",
                                                        "IdentificationSchemeAgencyNameText" : "PE:INEI",
                                                        "IdentificationSchemeNameText" : "Ubigeos"
                                                    }
                                                ],
                                                "CityName" : [ 
                                                    {
                                                        "TextContent" : "<departamentoReceptor>"
                                                    }
                                                ],
                                                "CountrySubentity" : [ 
                                                    {
                                                        "TextContent" : "<ciudadReceptor>"
                                                    }
                                                ],
                                                "District" : [ 
                                                    {
                                                        "TextContent" : "<distritoReceptor>"
                                                    }
                                                ],
                                                "AddressLine" : [ 
                                                    {
                                                        "Line" : [ 
                                                            {
                                                                "TextContent" : "<direccionReceptor>"
                                                            }
                                                        ]
                                                    }
                                                ],
                                                "Country" : [ 
                                                    {
                                                        "IdentificationCode" : [ 
                                                            {
                                                                "CodeContent" : "PE",
                                                                "CodeListIdentifier" : "ISO 3166-1",
                                                                "CodeListAgencyNameText" : "United Nations Economic Commission for Europe",
                                                                "CodeListNameText" : "Country"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ],
                                "Contact" : [ 
                                    {
                                        "ElectronicMail" : [ 
                                            {
                                                "TextContent" : "<electronicMail>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "PaymentMeans": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "Detraccion"
                        }
                    ],
                    "PaymentMeansCode": [
                        {
                            "CodeContent": "999"
                        }
                    ],
                    "PayeeFinancialAccount": [
                        {
                            "ID": [
                                {
                                    "IdentifierContent": "00181066180"
                                }
                            ]
                        }
                    ]
                }
            ],
            "PaymentTerms": [
                {
                    "ID": [
                        {
                            "IdentifierContent": "Detraccion"
                        }
                    ],
                    "PaymentMeansID": [
                        {
                            "IdentifierContent": "021"
                        }
                    ],
                    "Note": [
                        {
                            "TextContent": "<netoPagar>"
                        }
                    ],
                    "PaymentPercent": [
                        {
                            "NumericContent": "10.00"
                        }
                    ],
                    "Amount": [
                        {
                            "AmountContent": "<montoDetraccion>",
                            "AmountCurrencyIdentifier": "PEN"
                        }
                    ]
                },
                {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "FormaPago"
                            }
                        ],
                        "PaymentMeansID" : [ 
                            {
                                "IdentifierContent" : "Credito"
                            }
                        ],
                        "Amount" : [ 
                            {
                                "AmountContent" : "<netoPagar>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ]
                    },
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "FormaPago"
                            }
                        ],
                        "PaymentMeansID" : [ 
                            {
                                "IdentifierContent" : "Cuota001"
                            }
                        ],
                        "Amount" : [ 
                            {
                                "AmountContent" : "<netoPagar>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PaymentDueDate" : [ 
                            {
                                "DateContent" : "<fechaCredito>"
                            }
                        ]
                    }
            ],
                "TaxTotal" : [ 
                    {
                        "TaxAmount" : [ 
                            {
                                "AmountContent" : "<montoIGV>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "TaxSubtotal" : [ 
                            {
                                "TaxableAmount" : [ 
                                    {
                                        "AmountContent" : "<subTotal>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxAmount" : [ 
                                    {
                                        "AmountContent" : "<montoIGV>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxCategory" : [ 
                                    {
                                        "TaxScheme" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "1000",
                                                        "IdentificationSchemeNameText" : "Codigo de tributos",
                                                        "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05",
                                                        "IdentificationSchemeAgencyNameText" : "PE:SUNAT"
                                                    }
                                                ],
                                                "Name" : [ 
                                                    {
                                                        "TextContent" : "IGV"
                                                    }
                                                ],
                                                "TaxTypeCode" : [ 
                                                    {
                                                        "CodeContent" : "VAT"
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "LegalMonetaryTotal" : [ 
                    {
                        "LineExtensionAmount" : [ 
                            {
                                "AmountContent" : "<subTotal>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "TaxInclusiveAmount" : [ 
                            {
                                "AmountContent" : "<total>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PayableAmount" : [ 
                            {
                                "AmountContent" : "<total>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ]
                    }
                ],
                "InvoiceLine" : [ 
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "1"
                            }
                        ],
                        "Note" : [ 
                            {
                                "TextContent" : "TONELADAS"
                            }
                        ],
                        "InvoicedQuantity" : [ 
                            {
                                "QuantityContent" : "<cantidadItems>",
                                "QuantityUnitCode" : "ZZ",
                                "QuantityUnitCodeListIdentifier" : "UN/ECE rec 20",
                                "QuantityUnitCodeListAgencyNameText" : "United Nations Economic Commission for Europe"
                            }
                        ],
                        "LineExtensionAmount" : [ 
                            {
                                "AmountContent" : "<subTotalLinea>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PricingReference" : [ 
                            {
                                "AlternativeConditionPrice" : [ 
                                    {
                                        "PriceAmount" : [ 
                                            {
                                                "AmountContent" : "<totalLineaX>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "PriceTypeCode" : [ 
                                            {
                                                "CodeContent" : "01",
                                                "CodeListNameText" : "Tipo de Precio",
                                                "CodeListAgencyNameText" : "PE:SUNAT",
                                                "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "TaxTotal" : [ 
                            {
                                "TaxAmount" : [ 
                                    {
                                        "AmountContent" : "<montoIGV>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxSubtotal" : [ 
                                    {
                                        "TaxableAmount" : [ 
                                            {
                                                "AmountContent" : "<subTotal>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "TaxAmount" : [ 
                                            {
                                                "AmountContent" : "<montoIGV>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "TaxCategory" : [ 
                                            {
                                                "Percent" : [ 
                                                    {
                                                        "NumericContent" : 18.00
                                                    }
                                                ],
                                                "TaxExemptionReasonCode" : [ 
                                                    {
                                                        "CodeContent" : "10",
                                                        "CodeListAgencyNameText" : "PE:SUNAT",
                                                        "CodeListNameText" : "Afectacion del IGV",
                                                        "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07"
                                                    }
                                                ],
                                                "TaxScheme" : [ 
                                                    {
                                                        "ID" : [ 
                                                            {
                                                                "IdentifierContent" : "1000",
                                                                "IdentificationSchemeNameText" : "Codigo de tributos",
                                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05",
                                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT"
                                                            }
                                                        ],
                                                        "Name" : [ 
                                                            {
                                                                "TextContent" : "IGV"
                                                            }
                                                        ],
                                                        "TaxTypeCode" : [ 
                                                            {
                                                                "CodeContent" : "VAT"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "Item" : [ 
                            {
                                "Description" : [ 
                                    {
                                        "TextContent" : "<descripcionItemFactura>"
                                    }
                                ],
                                "SellersItemIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<codigoFactura>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "Price" : [ 
                            {
                                "PriceAmount" : [ 
                                    {
                                        "AmountContent" : "<subTotalX>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ]
                            }
                        ]
                    }
                  
                ]
            }
        ]
}
';
$subTotalLinea=round($totalFactura/$pesoLotes,2);
$subTotalLineaX=round($subtotal/$pesoLotes,2);
// Datos de la venta
$data = [
    '<ID>' => $facturadorSerie.'-'.$facturadorCorrelativo,
    '<IssueDate>' => $fecha,
    '<IssueTime>' => $hora,
    '<moneda>' => "PEN",
    '<rucEmisor>' => $facturadorCodigo,
    '<razonEmisor>' => $facturadorRazon,
   '<ubigeo>'=>$personaMineroDireccion[0]['ubigeo'],
   '<departamento>' => $personaMineroDireccion[0]['departamento'],
   '<ciudad>' => $personaMineroDireccion[0]['provincia'] ,
   '<distrito>' => $personaMineroDireccion[0]['distrito'] ,
   '<direccionEmisor>' => $personaMineroDireccion[0]['direccion'], 
   '<rucReceptor>' => $plantaCodigo,
   '<razonReceptor>' => $plantaRazon,
  '<ubigeoReceptor>'=>$personaPlantaDireccion[0]['ubigeo'],
  '<departamentoReceptor>' => $personaPlantaDireccion[0]['departamento'],
   '<ciudadReceptor>' => $personaPlantaDireccion[0]['provincia'] ,
   '<distritoReceptor>' => $personaPlantaDireccion[0]['distrito'] ,
   '<direccionReceptor>' =>$personaPlantaDireccion[0]['direccion'],
   '<electronicMail>' => $facturadorEmail,
   '<montoIGV>'  => round($igv,2),
   '<subTotal>'  => round($subtotal,2),
   '<total>'  => round($totalFactura,2),
   '<cantidadItems>'  => $pesoLotes,
   '<descripcionItemFactura>'  => $textoLotes,
   '<montoDetraccion>'  => round($detraccion,2),
   '<netoPagar>'  => round($netoPago,2),
   '<codigoFactura>' => '01',
   '<fechaCredito>' =>$nueva_fecha,
   '<montoLetras>'=>$montoLetrasFactura,
   '<subTotalLinea>'  => round($subtotal,2),
   '<totalLinea>'  => round($totalFactura,2),
   '<totalLineaX>' => $subTotalLinea,
   '<subTotalX>' => $subTotalLineaX
];

// Reemplazar los datos en la plantilla
$json = str_replace(array_keys($data), array_values($data), $templateJson);

// Guardar el JSON en un archivo
$fileName = '../../factura/'.$data['<rucEmisor>'] .'-01-'. $data['<ID>'] . '.json';
file_put_contents($fileName, $json);

return $fileName;



}


public function generarJsonFacturaTransporte($fecha,$hora,$subtotal,$igv,$totalFactura,
$facturadorCodigo,$facturadorRazon,$facturadorEmail,$facturadorSerie,$facturadorCorrelativo,
$plantaCodigo,$plantaRazon,$personaMineroDireccion,$personaPlantaDireccion,$nueva_fecha,$textoLotes,$montoLetrasFactura,$pesoLotes) {

  

// Plantilla JSON como un string (recuerda que debe estar bien formateado)
 $templateJson = '{
        "_D" : "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2",
        "_S" : "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
        "_B" : "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
        "_E" : "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2",
        "Invoice" : [ 
            {
                "UBLVersionID" : [ 
                    {
                        "IdentifierContent" : "2.1"
                    }
                ],
                "CustomizationID" : [ 
                    {
                        "IdentifierContent" : "2.0"
                    }
                ],
                "ID" : [ 
                    {
                        "IdentifierContent" : "<ID>"
                    }
                ],
                "IssueDate" : [ 
                    {
                        "DateContent" : "<IssueDate>"
                    }
                ],
		"IssueTime" : [ 
                    {
                        "DateTimeContent" : "<IssueTime>"
                    }
                ],
                "InvoiceTypeCode" : [ 
                    {
                        "CodeContent" : "01",
                        "CodeListNameText" : "Tipo de Documento",
                        "CodeListSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51",
                        "CodeListIdentifier" : "0101",
                        "CodeNameText" : "Tipo de Operacion",
                        "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01",
                        "CodeListAgencyNameText" : "PE:SUNAT"
                    }
                ],
                "Note" : [ 
                    {
                        "TextContent" : "<montoLetras>",
                        "LanguageLocaleIdentifier" : "1000"
                    }
                ],
                "DocumentCurrencyCode" : [ 
                    {
                        "CodeContent" : "<moneda>",
                        "CodeListIdentifier" : "ISO 4217 Alpha",
                        "CodeListNameText" : "Currency",
                        "CodeListAgencyNameText" : "United Nations Economic Commission for Europe"
                    }
                ],
                "LineCountNumeric" : [ 
                    {
                        "NumericContent" : 2
                    }
                ],
                "Signature" : [ 
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "IDSignature"
                            }
                        ],
                        "SignatoryParty" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "TextContent" : "<rucEmisor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "DigitalSignatureAttachment" : [ 
                            {
                                "ExternalReference" : [ 
                                    {
                                        "URI" : [ 
                                            {
                                                "TextContent" : "IDSignature"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "AccountingSupplierParty" : [ 
                    {
                        "Party" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<rucEmisor>",
                                                "IdentificationSchemeIdentifier" : "6",
                                                "IdentificationSchemeNameText" : "Documento de Identidad",
                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT",
                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"
                                            }
                                        ]
                                    }
                                ],
                                "PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyLegalEntity" : [ 
                                    {
                                        "RegistrationName" : [ 
                                            {
                                                "TextContent" : "<razonEmisor>"
                                            }
                                        ],
                                        "RegistrationAddress" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "<ubigeo>",
                                                        "IdentificationSchemeAgencyNameText" : "PE:INEI",
                                                        "IdentificationSchemeNameText" : "Ubigeos"
                                                    }
                                                ],
                                                "AddressTypeCode" : [ 
                                                    {
                                                        "CodeContent" : "0000",
                                                        "CodeListAgencyNameText" : "PE:SUNAT",
                                                        "CodeListNameText" : "Establecimientos anexos"
                                                    }
                                                ],
                                                "CityName" : [ 
                                                    {
                                                        "TextContent" : "<departamento>"
                                                    }
                                                ],
                                                "CountrySubentity" : [ 
                                                    {
                                                        "TextContent" : "<ciudad>"
                                                    }
                                                ],
                                                "District" : [ 
                                                    {
                                                        "TextContent" : "<distrito>"
                                                    }
                                                ],
                                                "AddressLine" : [ 
                                                    {
                                                        "Line" : [ 
                                                            {
                                                                "TextContent" : "<direccionEmisor>"
                                                            }
                                                        ]
                                                    }
                                                ],
                                                "Country" : [ 
                                                    {
                                                        "IdentificationCode" : [ 
                                                            {
                                                                "CodeContent" : "PE",
                                                                "CodeListIdentifier" : "ISO 3166-1",
                                                                "CodeListAgencyNameText" : "United Nations Economic Commission for Europe",
                                                                "CodeListNameText" : "Country"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "AccountingCustomerParty" : [ 
                    {
                        "Party" : [ 
                            {
                                "PartyIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<rucReceptor>",
                                                "IdentificationSchemeIdentifier" : "6",
                                                "IdentificationSchemeNameText" : "Documento de Identidad",
                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT",
                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06"
                                            }
                                        ]
                                    }
                                ],
				"PartyName" : [ 
                                    {
                                        "Name" : [ 
                                            {
                                                "TextContent" : "<razonReceptor>"
                                            }
                                        ]
                                    }
                                ],
                                "PartyLegalEntity" : [ 
                                    {
                                        "RegistrationName" : [ 
                                            {
                                                "TextContent" : "<razonReceptor>"
                                            }
                                        ],
                                        "RegistrationAddress" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "<ubigeoReceptor>",
                                                        "IdentificationSchemeAgencyNameText" : "PE:INEI",
                                                        "IdentificationSchemeNameText" : "Ubigeos"
                                                    }
                                                ],
                                                "CityName" : [ 
                                                    {
                                                        "TextContent" : "<departamentoReceptor>"
                                                    }
                                                ],
                                                "CountrySubentity" : [ 
                                                    {
                                                        "TextContent" : "<ciudadReceptor>"
                                                    }
                                                ],
                                                "District" : [ 
                                                    {
                                                        "TextContent" : "<distritoReceptor>"
                                                    }
                                                ],
                                                "AddressLine" : [ 
                                                    {
                                                        "Line" : [ 
                                                            {
                                                                "TextContent" : "<direccionReceptor>"
                                                            }
                                                        ]
                                                    }
                                                ],
                                                "Country" : [ 
                                                    {
                                                        "IdentificationCode" : [ 
                                                            {
                                                                "CodeContent" : "PE",
                                                                "CodeListIdentifier" : "ISO 3166-1",
                                                                "CodeListAgencyNameText" : "United Nations Economic Commission for Europe",
                                                                "CodeListNameText" : "Country"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ],
                                "Contact" : [ 
                                    {
                                        "ElectronicMail" : [ 
                                            {
                                                "TextContent" : "<electronicMail>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                
            "PaymentTerms": [
               
                {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "FormaPago"
                            }
                        ],
                        "PaymentMeansID" : [ 
                            {
                                "IdentifierContent" : "Credito"
                            }
                        ],
                        "Amount" : [ 
                            {
                                "AmountContent" : "<netoPagar>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ]
                    },
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "FormaPago"
                            }
                        ],
                        "PaymentMeansID" : [ 
                            {
                                "IdentifierContent" : "Cuota001"
                            }
                        ],
                        "Amount" : [ 
                            {
                                "AmountContent" : "<netoPagar>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PaymentDueDate" : [ 
                            {
                                "DateContent" : "<fechaCredito>"
                            }
                        ]
                    }
            ],
                "TaxTotal" : [ 
                    {
                        "TaxAmount" : [ 
                            {
                                "AmountContent" : "<montoIGV>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "TaxSubtotal" : [ 
                            {
                                "TaxableAmount" : [ 
                                    {
                                        "AmountContent" : "<subTotal>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxAmount" : [ 
                                    {
                                        "AmountContent" : "<montoIGV>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxCategory" : [ 
                                    {
                                        "TaxScheme" : [ 
                                            {
                                                "ID" : [ 
                                                    {
                                                        "IdentifierContent" : "1000",
                                                        "IdentificationSchemeNameText" : "Codigo de tributos",
                                                        "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05",
                                                        "IdentificationSchemeAgencyNameText" : "PE:SUNAT"
                                                    }
                                                ],
                                                "Name" : [ 
                                                    {
                                                        "TextContent" : "IGV"
                                                    }
                                                ],
                                                "TaxTypeCode" : [ 
                                                    {
                                                        "CodeContent" : "VAT"
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ],
                "LegalMonetaryTotal" : [ 
                    {
                        "LineExtensionAmount" : [ 
                            {
                                "AmountContent" : "<subTotal>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "TaxInclusiveAmount" : [ 
                            {
                                "AmountContent" : "<total>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PayableAmount" : [ 
                            {
                                "AmountContent" : "<total>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ]
                    }
                ],
                "InvoiceLine" : [ 
                    {
                        "ID" : [ 
                            {
                                "IdentifierContent" : "1"
                            }
                        ],
                        "Note" : [ 
                            {
                                "TextContent" : "TONELADAS"
                            }
                        ],
                        "InvoicedQuantity" : [ 
                            {
                                "QuantityContent" : "<cantidadItems>",
                                "QuantityUnitCode" : "ZZ",
                                "QuantityUnitCodeListIdentifier" : "UN/ECE rec 20",
                                "QuantityUnitCodeListAgencyNameText" : "United Nations Economic Commission for Europe"
                            }
                        ],
                        "LineExtensionAmount" : [ 
                            {
                                "AmountContent" : "<subTotalLinea>",
                                "AmountCurrencyIdentifier" : "<moneda>"
                            }
                        ],
                        "PricingReference" : [ 
                            {
                                "AlternativeConditionPrice" : [ 
                                    {
                                        "PriceAmount" : [ 
                                            {
                                                "AmountContent" : "<totalLineaX>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "PriceTypeCode" : [ 
                                            {
                                                "CodeContent" : "01",
                                                "CodeListNameText" : "Tipo de Precio",
                                                "CodeListAgencyNameText" : "PE:SUNAT",
                                                "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "TaxTotal" : [ 
                            {
                                "TaxAmount" : [ 
                                    {
                                        "AmountContent" : "<montoIGV>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ],
                                "TaxSubtotal" : [ 
                                    {
                                        "TaxableAmount" : [ 
                                            {
                                                "AmountContent" : "<subTotal>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "TaxAmount" : [ 
                                            {
                                                "AmountContent" : "<montoIGV>",
                                                "AmountCurrencyIdentifier" : "<moneda>"
                                            }
                                        ],
                                        "TaxCategory" : [ 
                                            {
                                                "Percent" : [ 
                                                    {
                                                        "NumericContent" : 18.00
                                                    }
                                                ],
                                                "TaxExemptionReasonCode" : [ 
                                                    {
                                                        "CodeContent" : "10",
                                                        "CodeListAgencyNameText" : "PE:SUNAT",
                                                        "CodeListNameText" : "Afectacion del IGV",
                                                        "CodeListUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07"
                                                    }
                                                ],
                                                "TaxScheme" : [ 
                                                    {
                                                        "ID" : [ 
                                                            {
                                                                "IdentifierContent" : "1000",
                                                                "IdentificationSchemeNameText" : "Codigo de tributos",
                                                                "IdentificationSchemeUniformResourceIdentifier" : "urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05",
                                                                "IdentificationSchemeAgencyNameText" : "PE:SUNAT"
                                                            }
                                                        ],
                                                        "Name" : [ 
                                                            {
                                                                "TextContent" : "IGV"
                                                            }
                                                        ],
                                                        "TaxTypeCode" : [ 
                                                            {
                                                                "CodeContent" : "VAT"
                                                            }
                                                        ]
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "Item" : [ 
                            {
                                "Description" : [ 
                                    {
                                        "TextContent" : "<descripcionItemFactura>"
                                    }
                                ],
                                "SellersItemIdentification" : [ 
                                    {
                                        "ID" : [ 
                                            {
                                                "IdentifierContent" : "<codigoFactura>"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        "Price" : [ 
                            {
                                "PriceAmount" : [ 
                                    {
                                        "AmountContent" : "<subTotalX>",
                                        "AmountCurrencyIdentifier" : "<moneda>"
                                    }
                                ]
                            }
                        ]
                    }
                  
                ]
            }
        ]
}
';
$subTotalLinea=round($totalFactura/$pesoLotes,2);
$subTotalLineaX=round($subtotal/$pesoLotes,2);
// Datos de la venta
$data = [
    '<ID>' => $facturadorSerie.'-'.$facturadorCorrelativo,
    '<IssueDate>' => $fecha,
    '<IssueTime>' => $hora,
    '<moneda>' => "USD",
    '<rucEmisor>' => $facturadorCodigo,
    '<razonEmisor>' => $facturadorRazon,
   '<ubigeo>'=>$personaMineroDireccion[0]['ubigeo'],
   '<departamento>' => $personaMineroDireccion[0]['departamento'],
   '<ciudad>' => $personaMineroDireccion[0]['provincia'] ,
   '<distrito>' => $personaMineroDireccion[0]['distrito'] ,
   '<direccionEmisor>' => $personaMineroDireccion[0]['direccion'], 
   '<rucReceptor>' => $plantaCodigo,
   '<razonReceptor>' => $plantaRazon,
  '<ubigeoReceptor>'=>$personaPlantaDireccion[0]['ubigeo'],
  '<departamentoReceptor>' => $personaPlantaDireccion[0]['departamento'],
   '<ciudadReceptor>' => $personaPlantaDireccion[0]['provincia'] ,
   '<distritoReceptor>' => $personaPlantaDireccion[0]['distrito'] ,
   '<direccionReceptor>' =>$personaPlantaDireccion[0]['direccion'],
   '<electronicMail>' => $facturadorEmail,
   '<montoIGV>'  => round($igv,2),
   '<subTotal>'  => round($subtotal,2),
   '<total>'  => round($totalFactura,2),
   '<cantidadItems>'  => $pesoLotes,
   '<descripcionItemFactura>'  => $textoLotes,
   '<netoPagar>'  => round($totalFactura,2),
   '<codigoFactura>' => '01',
   '<fechaCredito>' =>$nueva_fecha,
   '<montoLetras>'=>$montoLetrasFactura,
   '<subTotalLinea>'  => round($subtotal,2),
   '<totalLinea>'  => round($totalFactura,2),
   '<totalLineaX>' => $subTotalLinea,
   '<subTotalX>' => $subTotalLineaX
];

// Reemplazar los datos en la plantilla
$json = str_replace(array_keys($data), array_values($data), $templateJson);

// Guardar el JSON en un archivo
$fileName = '../../factura/'.$data['<rucEmisor>'] .'-01-'. $data['<ID>'] . '.json';
file_put_contents($fileName, $json);

return $fileName;



}
public function obtenerRequerimientoXSolicitudId($solicitudId){

    return SolicitudRetiro::create()->obtenerRequerimientoXSolicitudId($solicitudId);
  }

  public function obtenerHistorialEstadosXSolicitudId($solicitudId){

    return SolicitudRetiro::create()->obtenerHistorialEstadosXSolicitudId($solicitudId);
  }
  

  public function obtenerSolicitudRetiroXIDMTC(){
      
    $validaciones=SolicitudRetiro::create()->obtenerSolicitudesPendienteValidacion();
    if($validaciones==null){
        throw new WarningException("No existen registros.");
    } else {
    foreach ($validaciones as $item) {

        $solicitud=SolicitudRetiro::create()->obtenerSolicitudXID($item['solicitud_retiro_id']);
        $validacionTransportista=$solicitud[0]['imagen_transportista'];
        $validacionVehiculo=$solicitud[0]['imagen_vehiculo'];
        $validacionConductor=$solicitud[0]['imagen_conductor'];
        $transportistaCodigo=$solicitud[0]['transportista_ruc'];
        $conductorCodigo=$solicitud[0]['conductor_dni'];
        $minero=$solicitud[0]['persona_reinfo_id'];
        $vehiculo=$solicitud[0]['placa'];
        $intentoFuturo=$item['intentos']+1;
        if($validacionTransportista==null){
            
            if($transportistaCodigo==null){
              
                    self::desaprobarSolicitudRetiro($item['solicitud_retiro_id'],'Fallo validación transportista',1,$minero);
                
                SolicitudRetiro::create()->actualizarIntentosSolicitud($item['id'],'Fallo en la verificación',4); 
                break;
            }
              $ruc=$transportistaCodigo;
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
            if ($response2 == null ) {
                if($item['intentos']==3){
                self::desaprobarSolicitudRetiro($item['solicitud_retiro_id'],'No se encontró data para este transportista con este RUC: $ruc.',1,$minero);
                }
                SolicitudRetiro::create()->actualizarIntentosSolicitud($item['id'],'Fallo en la verificación',$intentoFuturo); 
                break;
            }
            $captura = $response2->captura;
            $razon=$response2->data[0]->razon_social;
            $partes = explode('-', $razon);
            $codigo = trim($partes[0]); 
            $imageData = base64_decode($captura);
    
            // Crear un nombre único para la imagen
            $imageName = uniqid() .'.png';
        
            // Especificar la ruta donde se guardará la imagen
            $imagePath = '../../vistas/com/solicitudRetiro/validaciones/' . $imageName;
     
            // Guardar la imagen en el servidor
            file_put_contents($imagePath, $imageData);
            SolicitudRetiro::create()->actualizarCapturaSoicitudRetiro($item['solicitud_retiro_id'],$imageName,1);
            SolicitudRetiro::create()->actualizarMTCTransportista($ruc,$codigo);
        }

        if($validacionVehiculo==null){
            $placa = str_replace("-", "", $vehiculo);
            $cantidadCaracteres = strlen($placa);
            if($cantidadCaracteres!=6){
              
                   
                        self::desaprobarSolicitudRetiro($item['solicitud_retiro_id'],'Placa vehiculo no cuenta con los caracteres correspondientes',1,$minero);
                    
                    SolicitudRetiro::create()->actualizarIntentosSolicitud($item['id'],'Fallo en la verificación',4); 
                  
                  break;
            }
          $data=[];
          $url= 'http://161.132.56.121:8000/mercaderia_placa/';
          $ch = curl_init();
          $endpointUrl = $url . urlencode($placa);
          curl_setopt($ch, CURLOPT_URL, $endpointUrl);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Límite de tiempo para la solicitud completa
          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
          $response2 = json_decode(curl_exec($ch));
          $response = json_decode($response2);
          curl_close($ch);
          if ($response == null ) {
            if($item['intentos']==3){
                self::desaprobarSolicitudRetiro($item['solicitud_retiro_id'],'Datos del vehiculo no se encuentran registrados en MTC',1,$minero);
            }
            SolicitudRetiro::create()->actualizarIntentosSolicitud($item['id'],'Datos del vehiculo no se encuentran registrados en MTC',$intentoFuturo); 
            break;
          }
         
          $obj = $response[0];
          $placa = $obj->placa;
            $nro_constancia = $obj->nro_constancia;
            $carga_util = $obj->carga_util;
            $captura = $obj->captura;
            $imageData = base64_decode($captura);
    
            // Crear un nombre único para la imagen
            $imageName = uniqid() .'.png';
        
            // Especificar la ruta donde se guardará la imagen
            $imagePath = '../../vistas/com/solicitudRetiro/validaciones/' . $imageName;
     
            // Guardar la imagen en el servidor
            file_put_contents($imagePath, $imageData);
          SolicitudRetiro::create()->actualizarCapturaSoicitudRetiro($item['solicitud_retiro_id'],$imageName,2);
          SolicitudRetiro::create()->actualizarconstanciaCargaPlaca($item['solicitud_retiro_id'],$nro_constancia,$carga_util);
        }


        if($validacionConductor==null){
            $data=[];
            $url= 'http://161.132.56.121:8000/licencia/';
            $ch = curl_init();
            $endpointUrl = $url . urlencode($conductorCodigo);
            curl_setopt($ch, CURLOPT_URL, $endpointUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Límite de tiempo para la solicitud completa
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
            $response2 = json_decode(curl_exec($ch));
            $response = json_decode($response2);
            curl_close($ch);
            if ($response == null ) {
                if($item['intentos']==3){
                    self::desaprobarSolicitudRetiro($item['solicitud_retiro_id'],'No se encontro licencia registrada',1,$minero);
                }
                SolicitudRetiro::create()->actualizarIntentosSolicitud($item['id'],'No se encontro licencia registrada',$intentoFuturo); 
                break;
            }
            $data = $response; 
            $conductor = $data[0][1];
            $licencia = $data[2][1];  // El valor "19/08/2017"
                $estado_licencia = $data[5][1];  // El valor "CANCELADA/CONDUCTOR INHABILITADO"
            $captura = $data[6][1];
            

            if($estado_licencia!='VIGENTE'){
                self::desaprobarSolicitudRetiro($item['solicitud_retiro_id'],'Conductor : '.$conductor.'- con licencia: '.$licencia.' se encuentra: '.$estado_licencia,1,$minero);
                SolicitudRetiro::create()->actualizarIntentosSolicitud($item['id'],'Conductor : '.$conductor.'- con licencia: '.$licencia.' se encuentra: '.$estado_licencia,4); 
                break;
            }
            else{
            $imageData = base64_decode($captura);
    
            // Crear un nombre único para la imagen
            $imageName = uniqid() .'.png';
        
            // Especificar la ruta donde se guardará la imagen
            $imagePath = '../../vistas/com/solicitudRetiro/validaciones/' . $imageName;
     
            // Guardar la imagen en el servidor
            file_put_contents($imagePath, $imageData);
            SolicitudRetiro::create()->actualizarCapturaSoicitudRetiro($item['solicitud_retiro_id'],$imageName,3);
            }
        }

        if($validacionConductor!=null && $validacionVehiculo!=null && $validacionTransportista!=null ){
        SolicitudRetiro::create()->actualizarIntentosSolicitud($item['id'],'Se valido correctamente',4);
        $actualizarSolicitud= SolicitudRetiro::create()->insertAprobacionSolicitud($item['solicitud_retiro_id'],12,4);
        SolicitudRetiro::create()->guardarEstadoSolicitud($item['solicitud_retiro_id'],12,1);
        self::insertAprobacionSolicitud( $item['solicitud_retiro_id'], 1 );
     }

    }
}
  }
  
  public function desaprobarSolicitudRetiro($solicitudId,$motivo,$usuarioId,$minero){
    $persona=Persona::create()->obtenerPersonaXId($minero);
    $nombre=$persona[0]['nombre'];
    $telefono=$persona[0]['telefono'];
    
    $bodyNotificacion = '
{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "plantilla_desaprobacion_dinamica",
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
                    },
                    {
                        "type": "text",
                        "text": "[|motivo|]"
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
$bodyNotificacion = str_replace("[|nro|]", $solicitudId, $bodyNotificacion);
$bodyNotificacion = str_replace("[|motivo|]", $motivo, $bodyNotificacion);
$this->notificacionWsp($bodyNotificacion);
    
    $actualizarSolicitud= SolicitudRetiro::create()->insertDesaprobacionSolicitud($solicitudId,9,$motivo);
    SolicitudRetiro::create()->guardarEstadoSolicitud($solicitudId,9,$usuarioId);

  }

  public function generarJsonRetencion($fecha,$facturadorSerie,$facturadorCorrelativo,$personaMineroDireccion,
  $tipoCambio2,$simbolo,$montoFactura,$montoRetencion,$montoquitadoretencion,
  $ruc,$razonSocial,$ubigeo,$departamento,
$provincia,$distrito,$direccion,$factura ,$fechaFactura,$fechaPago,$facturadorCodigo,$facturadorRazon,$porcentajeRetencion) {

  

// Plantilla JSON como un string (recuerda que debe estar bien formateado)
 $templateJson = '{
    "_D": "urn:sunat:names:specification:ubl:peru:schema:xsd:Retention-1",
    "_A": "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
    "_B": "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
    "_E": "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2",
    "_SUNAT": "urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1",
    "Retention": [
        {
            "UBLVersionID": [
                {
                    "_": "2.0"
                }
            ],
            "CustomizationID": [
                {
                    "_": "1.0"
                }
            ],
	    "Signature": [
                {
                    "ID": [
                        {
                            "_": "IDSignature"
                        }
                    ],
                    "SignatoryParty": [
                        {
                            "PartyIdentification": [
                                {
                                    "ID": [
                                        {
                                            "_": "<rucEmisor>"
                                        }
                                    ]
                                }
                            ],
                            "PartyName": [
                                {
                                    "Name": [
                                        {
                                            "_": "<razonSocialEmisor>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "DigitalSignatureAttachment": [
                        {
                            "ExternalReference": [
                                {
                                    "URI": [
                                        {
                                            "_": "IDSignature"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ],
            "ID": [
                {
                    "_": "<ID>"
                }
            ],
            "IssueDate": [
                {
                    "_": "<fechaDocumento>"
                }
            ],
            "AgentParty": [
                {
                    "PartyIdentification": [
                        {
                            "ID": [
                                {
                                    "_": "<rucEmisor>",
                                    "schemeID": "6"
                                }
                            ]
                        }
                    ],
                    "PartyName": [
                        {
                            "Name": [
                                {
                                    "_": "<razonSocialEmisor>"
                                }
                            ]
                        }
                    ],
                    "PostalAddress": [
                        {
                            "ID": [
                                {
                                    "_": "<ubigeoEmisor>"
                                }
                            ],
                            "StreetName": [
                                {
                                    "_": "<direccionEmisor>"
                                }
                            ],
                            "CityName": [
                                {
                                    "_": "<provinciaEmisor>"
                                }
                            ],
                            "CountrySubentity": [
                                {
                                    "_": "<departamentoEmisor>"
                                }
                            ],
                            "District": [
                                {
                                    "_": "<distritoEmisor>"
                                }
                            ],
                            "Country": [
                                {
                                    "IdentificationCode": [
                                        {
                                            "_": "PE"
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "PartyLegalEntity": [
                        {
                            "RegistrationName": [
                                {
                                    "_": "<razonSocialEmisor>"
                                }
                            ]
                        }
                    ]
                }
            ],
            "ReceiverParty": [
                {
                    "PartyIdentification": [
                        {
                            "ID": [
                                {
                                    "_": "<rucReceptor>",
                                    "schemeID": "6"
                                }
                            ]
                        }
                    ],
                    "PartyName": [
                        {
                            "Name": [
                                {
                                    "_": "<razonSocialReceptor>"
                                }
                            ]
                        }
                    ],
                    "PostalAddress": [
                        {
                            "ID": [
                                {
                                    "_": "<ubigeoReceptor>"
                                }
                            ],
                            "StreetName": [
                                {
                                    "_": "<direccionReceptor>"
                                }
                            ],
                            "CityName": [
                                {
                                    "_": "<provinciaReceptor>"
                                }
                            ],
                            "CountrySubentity": [
                                {
                                    "_": "<departamentoReceptor>"
                                }
                            ],
                            "District": [
                                {
                                    "_": "<distritoReceptor>"
                                }
                            ],
                            "Country": [
                                {
                                    "IdentificationCode": [
                                        {
                                            "_": "PE"
                                        }
                                    ]
                                }
                            ]
                        }
                    ],
                    "PartyLegalEntity": [
                        {
                            "RegistrationName": [
                                {
                                    "_": "<razonSocialReceptor>"
                                }
                            ]
                        }
                    ],
                    "Contact": [
                        {
                            "ElectronicMail": [
                                {
                                    "_": "contabilidad.amapo@pepasdeoro.com.pe"
                                }
                            ]
                        }
                    ]
                }
            ],
            "SUNATRetentionSystemCode": [
                {
                    "_": "01"
                }
            ],
            "SUNATRetentionPercent": [
                {
                    "_": "<porcentajeRetencion>"
                }
            ],
            "Note": [
                {
                    "_": ""
                }
            ],
            "TotalInvoiceAmount": [
                {
                    "_": "<montoRetencion>",
                    "currencyID": "PEN"
                }
            ],
            "SUNATTotalPaid": [
                {
                    "_": "<montoquitadoretencion>",
                    "currencyID": "PEN"
                }
            ],
            "SUNATRetentionDocumentReference": [
                {
                    "ID": [
                        {
                            "_": "<facturaRelacionada>",
                            "schemeID": "01"
                        }
                    ],
                    "IssueDate": [
                        {
                            "_": "<fechaFacturaRelacionada>"
                        }
                    ],
                    "TotalInvoiceAmount": [
                        {
                            "_": "<totalFacturaRelacionada>",
                            "currencyID": "<monedaFacturaRelacionada>"
                        }
                    ],
                    "Payment": [
                        {
                            "ID": [
                                {
                                    "_": "1"
                                }
                            ],
                            "PaidAmount": [
                                {
                                    "_": "<totalFacturaRelacionada>",
                                    "currencyID": "<monedaFacturaRelacionada>"
                                }
                            ],
                            "PaidDate": [
                                {
                                    "_": "<fechaPago>"
                                }
                            ]
                        }
                    ],
                    "SUNATRetentionInformation": [
                        {
                            "SUNATRetentionAmount": [
                                {
                                    "_": "<montoRetencion>",
                                    "currencyID": "PEN"
                                }
                            ],
                            "SUNATRetentionDate": [
                                {
                                    "_": "<fechaDocumento>"
                                }
                            ],
                            "SUNATNetTotalPaid": [
                                {
                                    "_": "<montoquitadoretencion>",
                                    "currencyID": "PEN"
                                }
                            ],
                            "ExchangeRate": [
                                {
                                    "SourceCurrencyCode": [
                                        {
                                            "_": "<monedaFacturaRelacionada>"
                                        }
                                    ],
                                    "TargetCurrencyCode": [
                                        {
                                            "_": "PEN"
                                        }
                                    ],
                                    "CalculationRate": [
                                        {
                                            "_": "<tipoCambio>"
                                        }
                                    ],
                                    "Date": [
                                        {
                                            "_": "<fechaDocumento>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ]
}
';

// Datos de la venta
$data = [
    '<ID>' => $facturadorSerie.'-'.$facturadorCorrelativo,
    '<fechaDocumento>' => $fecha,
    '<rucEmisor>' => $facturadorCodigo,
    '<razonSocialEmisor>' => $facturadorRazon,
   '<ubigeoEmisor>'=>$personaMineroDireccion[0]['ubigeo'],
   '<departamentoEmisor>' => $personaMineroDireccion[0]['departamento'],
   '<provinciaEmisor>' => $personaMineroDireccion[0]['provincia'] ,
   '<distritoEmisor>' => $personaMineroDireccion[0]['distrito'] ,
   '<direccionEmisor>' => $personaMineroDireccion[0]['direccion'], 
   '<rucReceptor>' => $ruc,
   '<razonSocialReceptor>' => $razonSocial,
  '<ubigeoReceptor>'=>$ubigeo,
  '<departamentoReceptor>' => $departamento,
   '<provinciaReceptor>' => $provincia ,
   '<distritoReceptor>' => $distrito ,
   '<direccionReceptor>' =>$direccion,
   '<porcentajeRetencion>'  => $porcentajeRetencion,
   '<montoRetencion>'  => $montoRetencion,
   '<montoquitadoretencion>'  => $montoquitadoretencion,
   '<facturaRelacionada>'  => $factura,
   '<fechaFacturaRelacionada>'  => $fechaFactura,
   '<totalFacturaRelacionada>'  => round($montoFactura,2),
   '<monedaFacturaRelacionada>'  => $simbolo,
   '<fechaPago>' => $fechaPago,
   '<tipoCambio>' =>$tipoCambio2
];

// Reemplazar los datos en la plantilla
$json = str_replace(array_keys($data), array_values($data), $templateJson);

// Guardar el JSON en un archivo
$fileName = '../../retencion/'.$data['<rucEmisor>'] .'-20-'. $data['<ID>'] . '.json';
file_put_contents($fileName, $json);

return $fileName;



}
}
