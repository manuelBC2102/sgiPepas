<?php
require_once('../../../util/Configuraciones.php');
require_once('../../../modeloNegocio/almacen/ActaRetiroNegocio.php');
require_once('../../../modeloNegocio/almacen/PersonaNegocio.php');
require_once('../../../modeloNegocio/almacen/EmpresaNegocio.php');

isset($_GET["id"]) ? $idComprob = $_GET["id"] : "";

if (ObjectUtil::isEmpty($_GET["id"]) || $idComprob == "") {
    echo("No se encontró el acta");
    exit();
}

$igv = 18;
$arrayDetalle = array();

$datoDocumento = ActaRetiro::create()->obtenerActaRetiroXId($idComprob);

if (ObjectUtil::isEmpty($datoDocumento)) {
    echo("No se encontró el documento");
    exit();
}

$fecha_retiro = $datoDocumento[0]['fecha_retiro'];
$datetime = new DateTime($fecha_retiro);
$fecha = $datetime->format('d-m-Y');
$hora = $datetime->format('H:i:s');
$urlComprobanteQr = 'imagenes/' . $datoDocumento[0]['archivo'];
$zona = $datoDocumento[0]['zona'];
$usuario = $datoDocumento[0]['usuario'];
$vehiculo = $datoDocumento[0]['vehiculo'];
$carreta = $datoDocumento[0]['carreta'];
$estado = $datoDocumento[0]['estado'];
$pesaje = $datoDocumento[0]['pesaje'];
$ticket = $datoDocumento[0]['ticket'];
$pesajeInicial = $datoDocumento[0]['pesaje_inicial'];
$pesajeFinal = $datoDocumento[0]['pesaje_final'];
$fechaInicial = $datoDocumento[0]['fecha_inicial'];
$fechaFinal = $datoDocumento[0]['fecha_final'];
$transportista=$datoDocumento[0]['transportista'];
$conductor=$datoDocumento[0]['conductor'];

// Obtener las solicitudes de retiro asociadas al ticket
$arraySolicitudes = [
    ['comunero' => 'Carlos Silva', 'numero_solicitud' => 'SOL-456', 'zona' => 'Llacuabamba', 'cantidad_lotes' => 'JORDAN 2008'],
    ['comunero' => 'Ana Gómez', 'numero_solicitud' => 'SOL-457', 'zona' => 'Zona 2', 'cantidad_lotes' => 'JORDAN 2008'],
    // Más solicitudes de retiro según sea necesario
];

$bloquesSolicitudes = '';  // Variable para almacenar los bloques de solicitudes

// Crear los bloques de solicitudes de retiro en formato tarjetas móviles
foreach ($arraySolicitudes as $solicitud) {
    $bloquesSolicitudes .= '
    <div style="margin-bottom: 1px; padding: 1px; background-color: #fff; border-left: 4px solid #388e3c; border-radius: 8px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
        <div style="font-size: 11px; font-weight: bold; color: #388e3c;">
            TITULAR MINERO: ' . htmlspecialchars($solicitud['comunero']) . '
        </div>
          <div style="font-size: 10px; color: #444; margin-top: 1px; border-top: 1px solid #388e3c; padding-top: 1px;">
            <img src="https://img.icons8.com/ios/50/388e3c/document.png" style="width: 10px; vertical-align: middle; margin-right: 2px;">
            N° Solicitud retiro: ' . htmlspecialchars($solicitud['numero_solicitud']) . '
        </div>
        <div style="font-size: 10px; color: #444; margin-top: 1px; border-top: 1px solid #388e3c; padding-top: 1px;">
            <img src="https://img.icons8.com/ios/50/388e3c/box.png" style="width: 10px; vertical-align: middle; margin-right: 2px;"> 
            Derecho minero: ' . htmlspecialchars($solicitud['cantidad_lotes']) . '
        </div>
      
        <div style="font-size: 10px; color: #444; margin-top: 1px; border-top: 1px solid #388e3c; padding-top: 1px;">
            <img src="https://img.icons8.com/ios/50/388e3c/map-pin.png" style="width: 10px; vertical-align: middle; margin-right: 2px;">
            Zona: ' . htmlspecialchars($solicitud['zona']) . '
        </div>
    </div>';
}

require_once('../../../controlador/commons/TCPDF-main/tcpdf.php');

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(true, 10);
$pdf->startPageGroup();

$pdf->AddPage();

$html = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
   
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f3f3f3;
            margin: 0;
            padding: 0;
            color: #444;
        }
        .container {
            max-width: 100px;
            padding: 2px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
      
        .solicitudes {
            margin-top: 1px;
        }
        .solicitudes h3 {
            font-size: 11px;
            color: #388e3c;
            margin-bottom: 1px;
            font-weight: 200;
        }
        .solicitudes div {
            background-color: #fff;
            padding: 1px;
            border-left: 4px solid #388e3c;  /* Aumento del grosor del borde verde */
            border-radius: 4px;
            margin-bottom: 1px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }
        .details {
            margin-top: 1px;
            background-color: #fafafa;
            padding: 1px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .details th, .details td {
            padding: 4px;  /* Ajuste del padding */
            text-align: left;
            border-bottom: 1px solid #ddd;
            color: #444;
            font-size: 10px;
        }
        .details th {
            background-color: #388e3c;
            color: #fff;
            font-weight: bold;
        }
        .details .line {
            height: 2px;
            background-color: #388e3c;
            
        }
      
       
    </style>
</head>
<body>

    <div class="container">
    <table style="width: 100%; border: none; border-collapse: collapse;">
   <br><br>
    <tr>
       
        <td style="text-align: center; width: 40%; ">
              <img src="../../images/01.png" style="width: 120px;" alt="Imagen del vehículo">
        </td>

        
        <td style="text-align: left; width: 60%; ">
        <br> <h3>TICKET DE BALANZA</h3>
        </td>
    </tr>
</table>
<div class="details">
<br><br>
       <table>

     
                <tr><th><b>Nº Ticket</b></th><td>$ticket</td> <th><b>Código de balanza</b></th><td>BLC1-0001</td></tr>
                <tr><th><b>Usuario registro</b></th><td>$usuario</td><th></th><td></td></tr>
               <tr><th><b>Pesaje Tara </b></th><td>$pesajeInicial</td> <th><b>Fecha pesaje inicial</b></th><td>$fechaInicial</td></tr>
                <tr><th><b>Pesaje Bruto </b></th><td>$pesajeFinal</td><th><b>Fecha pesaje final</b></th><td>$fechaFinal</td></tr>
                <tr><th><b>Peso Neto</b></th><td>$pesaje</td><th></th><td></td></tr>
                <tr><th><b>Vehiculo</b></th><td>$vehiculo</td><th><b>Carreta</b></th><td>$carreta</td></tr>
              <tr>
    <th><b>Conductor</b></th>
    <!-- Colspan para ocupar toda la fila -->
    <td colspan="3">$conductor</td>
</tr>
<tr>
    <th><b>Transportista</b></th>
    <!-- Colspan para ocupar toda la fila -->
    <td colspan="3">$transportista</td>
</tr>
            </table>
            <div class="line"></div>
</div>

    
        
            
            $bloquesSolicitudes
        

        
               
   <table style="width: 100%; border: none; border-collapse: collapse;">
   <br><br>
    <tr>
        <!-- Celda para la imagen -->
        <td style="text-align: center; width: 60%; ">
            <img src="$urlComprobanteQr" style="height: 185px;" alt="Imagen del vehículo">
        </td>

        <!-- Celda para la firma -->
        <td style="text-align: center; width: 40%; ">
        <br><br><br><br>
            <p>Firma del Responsable</p>
            <p>________________________</p>
        </td>
    </tr>
</table>
           
        </div>

       
       
    </div>
</body>
</html>
EOF;

// Reemplazar los placeholders con los valores reales
$html = str_replace('[Fecha]', $fecha, $html);
$html = str_replace('[Hora]', $hora, $html);
$html = str_replace('[Zona]', $zona, $html);
$html = str_replace('[Vehículo]', $vehiculo, $html);
$html = str_replace('[Usuario]', $usuario, $html);
$html = str_replace('[Peso Inicial]', $pesaje, $html);
$html = str_replace('[Observaciones]', $estado, $html);

// Añadir el HTML al PDF
$pdf->writeHTML($html, true, false, true, false, '');

ob_clean();
$pdf->Output('actaRetiro-' . $vehiculo . '-' . $fecha_retiro . '.pdf', 'I');
?>
