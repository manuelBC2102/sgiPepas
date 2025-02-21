<?php

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://ose-gw1.efact.pe/api-efact-ose/v1/pdf/b113f99b-8b32-41aa-9d96-66db595eef7a',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE3MzA4NjkxNTIsInVzZXJfbmFtZSI6IjIwNjAwNzM5MjU2IiwiYXV0aG9yaXRpZXMiOlsiUk9MRV9DTElFTlQiXSwianRpIjoiODQwZmNiMDItYzBjMi00YjNiLWJkMzMtNjE5ZThmMjgwMDBiIiwiY2xpZW50X2lkIjoiY2xpZW50Iiwic2NvcGUiOlsicmVhZCIsIndyaXRlIl19.HIDFW6wDKYifiyJ8jsGkc3V_BKuf-SIewRkYeAuRPLY'
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
