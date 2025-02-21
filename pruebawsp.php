<?php
    $destinatarioTelefono='51973470584';
    $nombre='Carlos';
    $buttonText='fff';
    $buttonUrl='https://itecsac.com/#focus';
    $bodyNotificacion = '{
    "messaging_product": "whatsapp",
    "to": "[|phone|]",
    "type": "template",
    "template": {
        "name": "notificacion_registro_provedor",
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
                        "text": "[|name|]"
                    }
                ]
            },
           
            {
                "type": "button",
                "sub_type": "url",
                "index": "0",
                "parameters": [

                    {
                        "type": "text",
                        "text": "[|button_url|]"
                    }
                ]
            }
        ]
    }
}';

// Reemplaza las variables en el cuerpo de la notificación
$bodyNotificacion = str_replace("[|phone|]", $destinatarioTelefono, $bodyNotificacion);
$bodyNotificacion = str_replace("[|name|]", $nombre, $bodyNotificacion);
// $bodyNotificacion = str_replace("[|button_text|]", $buttonText, $bodyNotificacion);
$bodyNotificacion = str_replace("[|button_url|]", $buttonUrl, $bodyNotificacion);

// Ejemplo de uso:
$body = $bodyNotificacion;

$whatsappUrl = 'https://graph.facebook.com/';
$whatsappVersion = 'v20.0';
$phoneNumberId = '378283208693956';
$accessToken = 'EABwSSycmXmcBOZBt3d5gPKlfs5gmM1LILQAwj3hmRvZBZCE7pnenXau1hFKeIOIXzE6tZCN3T3WgtlmpXEA7FoDqc6Yj1RIZBd12E2fNxHud5Y1YROKPAHdwc7qNsE7n6FnYtbRaZCZCYboS1JsQXQIc9ZC2yCK7jT9RFZA2NUlkqODeZBKrfxwpeKfDXTUzcRVAuAKgGBQGASvs7igTLD';

$url = $whatsappUrl . $whatsappVersion . "/" . $phoneNumberId . "/messages";

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken,
    ),
));

$response = curl_exec($curl);
$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
$responseJson = json_decode($response, true);

if ($statusCode == 200) {
    return ['status' => '1', 'code' => 0, 'message' => $responseJson['messages'][0]['id']];
} else {
    return ['status' => '0', 'code' => $statusCode, 'message' => $responseJson['error']['message']];
}

