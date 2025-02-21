
<?php

$token = isset($_GET['token']) ? $_GET['token'] : null;
$efact = isset($_GET['efact']) ? $_GET['efact'] : null;
$file_type = isset($_GET['file_type']) ? $_GET['file_type'] : 'pdf'; // Este parámetro indica si queremos 'pdf', 'cdr' o 'xml'

// Determinar la URL dependiendo del tipo de archivo
if ($file_type == 'pdf') {
    $url = 'https://ose.efact.pe/api-efact-ose/v1/pdf/' . $efact;
    $content_type = 'application/pdf';
    $file_extension = 'pdf';
} elseif ($file_type == 'cdr') {
    $url = 'https://ose.efact.pe/api-efact-ose/v1/cdr/' . $efact; // Cambiar la URL para obtener el CDR
    $content_type = 'application/xml';  // El tipo de contenido para CDR es XML (aunque también puedes ajustarlo según lo que el servidor responda)
    $file_extension = 'xml';
} elseif ($file_type == 'xml') {
    $url = 'https://ose.efact.pe/api-efact-ose/v1/xml/' . $efact; // Cambiar la URL para obtener el XML
    $content_type = 'application/xml';
    $file_extension = 'xml';
} else {
    // Si no se pasa un tipo válido, devolvemos un error
    die('Tipo de archivo no válido.');
}

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer ' . $token
    ),
));

$response = curl_exec($curl);

if (curl_errno($curl)) {
    echo 'Error: ' . curl_error($curl);
} else {
    // Cerrar la sesión cURL
    curl_close($curl);

    // Establecer las cabeceras adecuadas para el archivo que queremos descargar
    header('Content-Type: ' . $content_type);
    header('Content-Disposition: attachment; filename="documento '.$file_type.'.' . $file_extension . '"');
    header('Content-Length: ' . strlen($response));

    // Imprimir el contenido del archivo
    echo $response;
}
?>
