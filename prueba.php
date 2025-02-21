<?php
$destinatarioTelefono = '51973470584';
$accessToken = 'EABwSSycmXmcBOZBt3d5gPKlfs5gmM1LILQAwj3hmRvZBZCE7pnenXau1hFKeIOIXzE6tZCN3T3WgtlmpXEA7FoDqc6Yj1RIZBd12E2fNxHud5Y1YROKPAHdwc7qNsE7n6FnYtbRaZCZCYboS1JsQXQIc9ZC2yCK7jT9RFZA2NUlkqODeZBKrfxwpeKfDXTUzcRVAuAKgGBQGASvs7igTLD';

$bodyNotificacion = json_encode([
    "messaging_product" => "whatsapp",
    "to" => $destinatarioTelefono,
    "type" => "template",
    "template" => [
        "name" => "hello_world",
        "language" => [
            "code" => "en_US"
        ]
    ]
]);

$whatsappUrl = 'https://graph.facebook.com/';
$whatsappVersion = 'v20.0';
$phoneNumberId = '378283208693956';

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
    CURLOPT_POSTFIELDS => $bodyNotificacion,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken,
    ],
]);

$response = curl_exec($curl);
$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
}
curl_close($curl);

if ($response === false) {
    echo "cURL Error: " . $error_msg;
    return ['status' => '0', 'code' => $statusCode, 'message' => $error_msg];
}

$responseJson = json_decode($response, true);

if ($statusCode == 200) {
    return ['status' => '1', 'code' => 0, 'message' => $responseJson['messages'][0]['id']];
} else {
    return ['status' => '0', 'code' => $statusCode, 'message' => $responseJson['error']['message']];
}
