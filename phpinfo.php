<?php

function convertir_a_palabras($numero) {
    // Arreglos de palabras para cada nivel de número
    $unidades = [
        "", "uno", "dos", "tres", "cuatro", "cinco", "seis", "siete", "ocho", "nueve", 
        "diez", "once", "doce", "trece", "catorce", "quince", "dieciséis", "diecisiete", 
        "dieciocho", "diecinueve"
    ];
    $decenas = [
        "", "", "veinte", "treinta", "cuarenta", "cincuenta", "sesenta", "setenta", "ochenta", "noventa"
    ];
    $centenas = [
        "", "cien", "doscientos", "trescientos", "cuatrocientos", "quinientos", "seiscientos", 
        "setecientos", "ochocientos", "novecientos"
    ];
    $miles = [
        "", "mil", "millón", "mil millones", "billón"
    ];

    // Función que convierte números de hasta tres dígitos
    function convertir_tres_digitos($numero) {
        global $unidades, $decenas, $centenas;
        $unidades = [
            "", "uno", "dos", "tres", "cuatro", "cinco", "seis", "siete", "ocho", "nueve", 
            "diez", "once", "doce", "trece", "catorce", "quince", "dieciséis", "diecisiete", 
            "dieciocho", "diecinueve"
        ];
        $decenas = [
            "", "", "veinte", "treinta", "cuarenta", "cincuenta", "sesenta", "setenta", "ochenta", "noventa"
        ];
        $centenas = [
            "", "cien", "doscientos", "trescientos", "cuatrocientos", "quinientos", "seiscientos", 
            "setecientos", "ochocientos", "novecientos"
        ];
        $miles = [
            "", "mil", "millón", "mil millones", "billón"
        ];
    
        
        $resultado = "";
        
        // Centenas
        if ($numero >= 100) {
            $centena = floor($numero / 100);
            $numero -= $centena * 100;
            if ($centena == 1 && $numero == 0) {
                $resultado .= "cien ";
            } else {
                $resultado .= $centenas[$centena] . " ";
            }
        }
        
        // Decenas
        if ($numero >= 20) {
            $decena = floor($numero / 10);
            $unidad = $numero % 10;
            $resultado .= $decenas[$decena] . " ";
            if ($unidad > 0) {
                $resultado .= $unidades[$unidad];
            }
        } else if ($numero > 0) {
            $resultado .= $unidades[$numero];
        }
        
        return trim($resultado);
    }

    // Función para convertir números grandes (miles, millones, etc.)
    function convertir_numero($numero) {
        global $miles;
        
        if ($numero == 0) return "cero";
        
        $resultado = "";
        $nivel = 0;
        
        // Partir el número en bloques de 3 dígitos (miles, millones, etc.)
        while ($numero > 0) {
            $partido = $numero % 1000;
            if ($partido > 0) {
                $resultado = convertir_tres_digitos($partido) . " " . ($nivel > 0 ? $miles[$nivel] . " " : "") . $resultado;
            }
            $numero = floor($numero / 1000);
            $nivel++;
        }
        
        return trim($resultado);
    }

    // Convertir la parte entera del número
    $entero = floor($numero);
    $entero_palabras = convertir_numero($entero);
    
    // Convertir la parte decimal (centavos) si existe
    $decimal = round(($numero - $entero) * 100);
    $decimal_palabras = $decimal > 0 ? " Y " . $decimal . "/100" : "";

    // El resultado final (con "DÓLAR AMERICANO")
    return strtoupper($entero_palabras . $decimal_palabras . " DÓLAR AMERICANO");
}

// Ejemplo de uso
$numero = 157964.89;
echo convertir_a_palabras($numero); // Debería devolver "CIENTO CINCUENTA Y SIETE MIL NOVECIENTOS SESENTA Y CUATRO Y 89/100 DÓLAR AMERICANO"

?>
