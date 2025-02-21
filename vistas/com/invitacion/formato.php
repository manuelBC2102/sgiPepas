<html lang="en">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <link rel="shortcut icon" href="../../images/icono_ittsa.ico">
        <title>ITTSA CARGO - INTERNACIONAL DE TRANSPORTE TURISTICO Y SERVICIOS SRL</title>
    </head>

    <?php
    require_once('../../../util/Configuraciones.php');
    require_once('../../../modeloNegocio/almacen/ActaRetiroNegocio.php');
    require_once('../../../modeloNegocio/almacen/PersonaNegocio.php');
    require_once('../../../modelo/almacen/MovimientoBien.php');
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

    $fecha_retiro=$datoDocumento[0]['fecha_retiro'];
    $datetime = new DateTime($fecha_retiro);
    $fecha = $datetime->format('d-m-Y');
    $hora = $datetime->format('H:i:s');
    $urlComprobanteQr='imagenes/'.$datoDocumento[0]['archivo'];
    $zona=$datoDocumento[0]['zona'];
    $usuario=$datoDocumento[0]['usuario'];
    $vehiculo=$datoDocumento[0]['vehiculo'];
    $estado=$datoDocumento[0]['estado'];
    $pesaje=$datoDocumento[0]['pesaje'];
    require_once('../../../controlador/commons/TCPDF-main/tcpdf.php');

    // $medidas = array(75, 600); // Ajustar aqui segun los milimetros necesarios;
    // $pdf = new TCPDF('P', 'mm', $medidas, true, 'UTF-8', false);
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetMargins(5, 5, 5);
    $pdf->startPageGroup();

    $pdf->AddPage();
    
    $html = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Acta de Retiro de Vehículo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            padding: 20px;
            border: 1px solid #000;
        }
        .header, .footer {
            text-align: center;
        }
        .header h1, .footer p {
            margin: 0;
        }
        .details {
            margin: 20px 0;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
       
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details table, .details th, .details td {
            border: 1px solid #000;
        }
        .details th, .details td {
            padding: 42px;
            text-align: left;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
   
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Acta de Retiro de Vehículo</h1>
          
        </div>

        <div class="details">
            <table>
                <tr>
                    <th>Fecha</th>
                    <td>[Fecha]</td>
                </tr>
                <tr>
                    <th>Hora</th>
                    <td>[Hora]</td>
                </tr>
                <tr>
                    <th>Zona</th>
                    <td>[Zona]</td>
                </tr>
                <tr>
                    <th>Vehículo</th>
                    <td>[Vehículo]</td>
                </tr>

                <tr>
                    <th>Peso</th>
                    <td>[Peso Inicial]</td>
                </tr>
                <tr>
                <th>Usuario Registro</th>
                <td>[Usuario]</td>
                </tr>
                <tr>
                    <th>Estado</th>
                    <td>[Observaciones]</td>
                </tr>
            </table>
        </div>
       
        <div style="width:50%;background-color:white;text-align:center;">
						<img style="height:200px" src="$urlComprobanteQr">
					</div>	
        <div class="footer">
            <p>Firma del Responsable</p>
            <p>________________________</p>
        </div>
    </div>
</body>
</html>
EOF;

// Reemplazar los placeholders con valores reales
$html = str_replace('[Fecha]', $fecha, $html);
$html = str_replace('[Hora]', $hora, $html);
$html = str_replace('[Zona]',$zona, $html);
$html = str_replace('[Vehículo]',$vehiculo, $html);
$html = str_replace('[Usuario]', $usuario, $html);
$html = str_replace('[Peso Inicial]', $pesaje, $html);
$html = str_replace('[Observaciones]', $estado, $html);

// Añadir el HTML al PDF
$pdf->writeHTML($html, true, false, true, false, '');


    ob_clean();
    $pdf->Output('actaRetiro'.$vehiculo.'-'.$fecha_retiro.'.pdf', 'I');

 
    ?>

</html>