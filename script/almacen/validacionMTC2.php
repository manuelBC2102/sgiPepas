<?php function numero_a_texto($numero) {
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
        return $centenas[$centena] . ($resto ? " " . numero_a_texto($resto) : "");
    } elseif ($numero < 1000000) {
        $miles = floor($numero / 1000);
        $resto = $numero % 1000;
        return numero_a_texto($miles) . " MIL" . ($resto ? " " . numero_a_texto($resto) : "");
    } elseif ($numero < 1000000000) {
        $millones = floor($numero / 1000000);
        $resto = $numero % 1000000;
        return numero_a_texto($millones) . " MILLONES" . ($resto ? " " . numero_a_texto($resto) : "");
    }
    
    return "Número fuera de rango"; // Si el número es más grande, agregar más lógica si es necesario
}

function convertir_a_texto($numero) {
    // Obtener la parte decimal y entera
    $parte_decimal = substr($numero, strpos($numero, '.') + 1);
    $parte_entera = floor($numero);
    
    // Convertir la parte entera a texto
    $texto_entero = numero_a_texto($parte_entera);
    
    // Convertir la parte decimal a formato fraccionario
    $texto_decimal = $parte_decimal ? " Y " . $parte_decimal . "/100" : '';
    
    return strtoupper($texto_entero . $texto_decimal . " DÓLAR AMERICANO");
}

$numero = 937964.89;
echo utf8_decode(convertir_a_texto($numero));

  ?>