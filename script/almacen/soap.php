<?php
// Definir la URL del WSDL del servicio web
$wsdl = "http://vhzeaqs4ci.sap.zeusol.com.pe:8000/sap/bc/srt/wsdl/flv_10002A111AD1/bndg_url/sap/bc/srt/rfc/sap/zosws_imagina_wfaprob/220/zws_imagina_wfaprob/zbn_imagina_wfaprob?sap-client=220";

// Credenciales de usuario y contraseña
$username = "ABAPIMAG";
$password = "AbapImg2024**";


// Opciones para la autenticación básica
$options = array(
    'login' => $username,          // Usuario
    'password' => $password,       // Contraseña
    'trace' => 1,                  // Habilita el seguimiento para debugging
    'exception' => 1,              // Lanza excepciones si ocurre un error
    'cache_wsdl' => WSDL_CACHE_NONE // Desactiva la caché del WSDL (opcional)
);

try {
    // Crear un cliente SOAP con las opciones configuradas
    $client = new SoapClient($wsdl, $options);

    // Llamada al método del servicio web (por ejemplo, "nombreMetodo")
    // Este método depende de los métodos disponibles en el servicio que consumes
    $response = $client->__soapCall("nombreMetodo", array("param1" => "valor1", "param2" => "valor2"));

    // Mostrar la respuesta
    echo "Respuesta del servicio web: ";
    var_dump($response);
} catch (SoapFault $e) {
    // Captura los errores SOAP
    echo "Error: " . $e->getMessage();
}
?>
