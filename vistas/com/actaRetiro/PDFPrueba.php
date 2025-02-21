<?php

$token = isset($_GET['token']) ? $_GET['token'] : null; 
$efact = isset($_GET['efact']) ? $_GET['efact'] : null;

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://ose-gw1.efact.pe/api-efact-ose/v1/pdf/1865ec69-8e74-4847-93a4-d73c261845bf',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer '. $token
    ),
));

$response = curl_exec($curl);

if (curl_errno($curl)) {
    echo 'Error: ' . curl_error($curl);
} else {
    // Cerrar la sesión cURL
    curl_close($curl);

    // Establecer las cabeceras adecuadas para el PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="documento.pdf"');
    header('Content-Length: ' . strlen($response));

    // Imprimir el contenido del PDF
    echo $response;
}
?>
