<?php
try{
    $handle = curl_init();

//    $url = "http://www.apifact2.facturasunat.com/EfacturaWs.asmx";
    $url = "http://www.apifact2.facturasunat.com/EfacturaWs.asmx";

    // Set the url
    curl_setopt($handle, CURLOPT_URL, $url);
    // Set the result output to be a string.
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    $output = curl_exec($handle);

    curl_close($handle);

    echo $output;
}catch(Exception $e){
    echo $e->getMessage();
}