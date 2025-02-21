<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once('../../../fpdf/fpdf.php');
require_once('../../../modeloNegocio/almacen/SolicitudRetiroNegocio.php');
require_once('../../../modeloNegocio/almacen/PersonaNegocio.php');
require_once('../../../modeloNegocio/almacen/EmpresaNegocio.php');


 isset($_GET["id"]) ? $idComprob = $_GET["id"] : "";
 if (ObjectUtil::isEmpty($_GET["id"]) || $idComprob == "") {
     echo("No se encontró el acta");
     exit();
 }

$igv = 18;
$arrayDetalle = array();

$datoDocumento = SolicitudRetiro::create()->obtenerActaFormatoXID($idComprob);

if (ObjectUtil::isEmpty($datoDocumento)) {
    echo("No se encontró el documento");
    exit();
}


// Crear la fecha formateada
$fechaFormateada = $dia . ' de ' . $mes . ' del ' . $año;
$nroSolicitud=$datoDocumento[0]['id'];
$codigoZona=$datoDocumento[0]['codigo_zona'];
$nombreZona=$datoDocumento[0]['nombre_zona'];
$destino=$datoDocumento[0]['destino_final'];
$plantaRUC=$datoDocumento[0]['planta_ruc'];
$plantaNombre=$datoDocumento[0]['planta_nombre'];
$placa=$datoDocumento[0]['placa'];
$transportistaNombre=$datoDocumento[0]['transportista_nombre'];
$conductorNombre=$datoDocumento[0]['conductor_nombre'];
$conductorLicencia=$datoDocumento[0]['licencia'];
$firma=$datoDocumento[0]['firma_usuario'];
$fechaActa=$datoDocumento[0]['fecha_acta'];
$pesaje=$datoDocumento[0]['pesaje'];
$remision=$datoDocumento[0]['serie_guia'].'-'.$datoDocumento[0]['correlativo_guia'];
$transportista=$datoDocumento[0]['serie_transportista'].'-'.$datoDocumento[0]['correlativo_transportista'];
$balanza=$datoDocumento[0]['balanza_nombre'];
$partes = explode(" ", $fechaActa);
$pesajeInicial=$datoDocumento[0]['pesaje_inicial'];
$pesajeFinal=$datoDocumento[0]['pesaje_final'];;
$fechaInicial=$datoDocumento[0]['fecha_inicial'];;
$fechaFinal=$datoDocumento[0]['fecha_final'];;

// Formatear la fecha en el formato deseado
$fecha = DateTime::createFromFormat('Y-m-d', $partes[0]);
$fechaFormateada = $fecha->format('d/m/Y');
$hora = $partes[1];
if($codigoZona=='040009709'){
$descripcionZona='quien cuenta con autorización para la explotación de este recurso, según la resolución RD-262-2020-GR-DREM-APURIMAC.';
}
else{
$descripcionZona='quien cuenta con inscripción VIGENTE en el registro integral de formalización minera - REINFO.';	
}
class PDF extends FPDF
{
    
    
    protected $B = 0;
protected $I = 0;
protected $U = 0;
protected $HREF = '';

function WriteHTML($html)
{
	// Int�rprete de HTML
	$html = str_replace("\n",' ',$html);
	$a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	foreach($a as $i=>$e)
	{
		if($i%2==0)
		{
			// Text
			if($this->HREF)
				$this->PutLink($this->HREF,$e);
			else
				$this->Write(5,$e);
		}
		else
		{
			// Etiqueta
			if($e[0]=='/')
				$this->CloseTag(strtoupper(substr($e,1)));
			else
			{
				// Extraer atributos
				$a2 = explode(' ',$e);
				$tag = strtoupper(array_shift($a2));
				$attr = array();
				foreach($a2 as $v)
				{
					if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
						$attr[strtoupper($a3[1])] = $a3[2];
				}
				$this->OpenTag($tag,$attr);
			}
		}
	}
}

function OpenTag($tag, $attr)
{
	// Etiqueta de apertura
	if($tag=='B' || $tag=='I' || $tag=='U')
		$this->SetStyle($tag,true);
	if($tag=='A')
		$this->HREF = $attr['HREF'];
	if($tag=='BR')
		$this->Ln(5);
}

function CloseTag($tag)
{
	// Etiqueta de cierre
	if($tag=='B' || $tag=='I' || $tag=='U')
		$this->SetStyle($tag,false);
	if($tag=='A')
		$this->HREF = '';
}

function SetStyle($tag, $enable)
{
	// Modificar estilo y escoger la fuente correspondiente
	$this->$tag += ($enable ? 1 : -1);
	$style = '';
	foreach(array('B', 'I', 'U') as $s)
	{
		if($this->$s>0)
			$style .= $s;
	}
	$this->SetFont('',$style);
}

function PutLink($URL, $txt)
{
	// Escribir un hiper-enlace
	$this->SetTextColor(0,0,255);
	$this->SetStyle('U',true);
	$this->Write(5,$txt,$URL);
	$this->SetStyle('U',false);
	$this->SetTextColor(0);
}
    
    
    
    
// Cabecera de página
function Header()
{
    // Logo
    $this->Image('../../images/membretadoPepas.png',0,0,210);

    // Arial bold 15
   
 
    // Movernos a la derecha
  
    
      
    // Título






  
    // Salto de línea
  


}

// Pie de página
function Footer()
{
    // Posición: a 1,5 cm del final
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Times','I',7.5);
    // Número de página

}
}


$pdf = new PDF('P','mm','A4');
$pdf->AddPage();
$pdf->AliasNbPages();
$pdf->Ln(40);
 $pdf->SetFont('Times','B',17);
   
           $pdf->Cell(32,10,' ');
$pdf->SetFont('Times','B',13);
$pdf->Cell(35);
$pdf->Cell(190,7,utf8_decode('ACTA DE RETIRO DE MINERAL'),0,1);

$pdf->SetFont('Arial', '', 10);


$pdf->SetFont('Arial', '', 10);

$pdf->Ln(5);
$html='En la Comunidad Campesina Pampamarca, siendo las '.$hora.' del día '.$fechaFormateada.', y en mérito a la solicitud de retiro de mineral N° '.$nroSolicitud.', se procede a levantar la presente acta para dejar constancia del retiro de mineral aurífero sin procesar propiedad de la ASOCIACIÓN DE MINEROS ARTESANALES PEPAS DE ORO DE PAMPAMARCA con RUC N° 20490106804, '.$descripcionZona;
$pdf->WriteHTML(utf8_decode($html));

$pdf->Ln(9);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(190,7,utf8_decode('DATOS DEL RETIRO'),0,1);
$pdf->SetFont('Arial', '', 10);

$html2='<b>* Procedencia:</b>  Derecho minero '.$nombreZona.', con código '.$codigoZona;
$pdf->WriteHTML(utf8_decode($html2));
$pdf->Ln(6);
$html3='<b>* Destino:</b>  Establecimiento autorizado ubicado en '.$destino.', propiedad de la empresa '.$plantaNombre.', con RUC N° '.$plantaRUC.'.';
$pdf->WriteHTML(utf8_decode($html3));

$pdf->Ln(6);
$html4='<b>* Vehículo:</b>  Placa de rodaje '.$placa.', de propiedad de '.$transportistaNombre.'.';
$pdf->WriteHTML(utf8_decode($html4));
  
$pdf->Ln(6);
$html5='<b>* Conductor:</b>  '.$conductorNombre.', con licencia Nº '.$conductorLicencia.'.';
$pdf->WriteHTML(utf8_decode($html5));

$pdf->Ln(6);
$html6='<b>* Peso:</b>  '.$pesaje.', TMH según Ticket de Balanza Nº .';
$pdf->WriteHTML(utf8_decode($html6));


$pdf->Ln(6);
$html7='<b>* Guías:</b> GRR Nº '.$remision.'- GRT Nº  '.$transportista.'.';
$pdf->WriteHTML(utf8_decode($html7));
$pdf->Ln(40);



// Calcular el ancho total de la línea de guiones
$linea_width = 193; // Ancho de la línea de guiones (ajústalo a tu necesidad)

// Posicionar la firma (imagen) centrada sobre los guiones y encima del texto
$imagen_x = ($pdf->GetPageWidth() - 50) / 2; // Para centrar la imagen en la página
$imagen_y = $pdf->GetY()-22; // Posición Y de la firma (se usa la posición actual en Y)
$pdf->Image('../persona/firmas/'.$firma, $imagen_x, $imagen_y, 50); // Ajuste de la posición Y para que quede encima del texto

// Agregar la línea de guiones centrada
$pdf->Cell($linea_width, 4, utf8_decode('--------------------------------------------------'), 0, 1, 'C');

// Agregar el texto "Área de comercialización" centrado
$pdf->Cell($linea_width, 4, utf8_decode('').$balanza, 0, 1, 'C');
$pdf->Cell($linea_width, 4, utf8_decode('Operador de Balanza'), 0, 1, 'C');


// Puedes agregar un salto de línea si deseas más espacio después del texto
$pdf->Ln(10);

$pdf->AddPage();

$arraySolicitudes = [
    ['comunero' => 'Carlos Silva', 'numero_solicitud' => 'SOL-456', 'zona' => 'Llacuabamba', 'cantidad_lotes' => 'JORDAN 2008'],
    ['comunero' => 'Ana Gómez', 'numero_solicitud' => 'SOL-457', 'zona' => 'Zona 2', 'cantidad_lotes' => 'JORDAN 2008'],
];

$pdf->SetFont('Arial', '', 12);

// Título
// Detalles del ticket (tabla con encabezado dorado)
$pdf->Ln(37);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'TICKET DE BALANZA', 0, 1, 'C');



$pdf->Ln(10);

// Encabezado de la tabla con color dorado
$pdf->SetFillColor(255, 223, 0);  // Color dorado
$pdf->SetFont('Arial', 'B', 10);

$pdf->Cell(70, 5, utf8_decode('Nº Ticket'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(106, 5, '25', 1, 1, 'C');  // Valor

$pdf->Cell(70, 5, utf8_decode('Usuario registro'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(106, 5, utf8_decode(''.$balanza), 1, 1, 'C');  // Valor

$pdf->Cell(70, 5, utf8_decode('Código de balanza'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(106, 5, 'BLC1-0001', 1, 1, 'C');  // Valor

$pdf->Cell(70, 5, utf8_decode('Pesaje Inicial'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(106, 5, ''.$pesajeInicial, 1, 1, 'C');  // Valor

$pdf->Cell(70, 5, utf8_decode('Fecha pesaje inicial'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(106, 5, ''.$fechaInicial, 1, 1, 'C');  // Valor

$pdf->Cell(70, 5, utf8_decode('Pesaje Final'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(106, 5, ''.$pesajeFinal, 1, 1, 'C');  // Valor

$pdf->Cell(70, 5, utf8_decode('Fecha pesaje final'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(106, 5, ''.$fechaFinal, 1, 1, 'C');  // Valor

$pdf->Cell(70, 5, utf8_decode('Vehiculo'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(106, 5, ''.$placa, 1, 1, 'C');  // Valor

$pdf->Cell(70, 5, utf8_decode('Carreta'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(106, 5, '', 1, 1, 'C');  // Sin valor

$pdf->Cell(70, 5, utf8_decode('Conductor'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell( 106, 5, utf8_decode(''.$conductorNombre), 1, 1, 'C');  // Valor

$max_length = 44;  // Establecer el límite de caracteres

// Limitar la cadena a los primeros $max_length caracteres
$transportistaNombre2 = (strlen($transportistaNombre) > $max_length) ? substr($transportistaNombre, 0, $max_length) . '...' : $transportistaNombre;
$pdf->Cell(70, 5, utf8_decode('Transportista'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(106, 5, utf8_decode(''.$transportistaNombre2), 1, 1, 'C'); 

// Solicitudes de Retiro (tabla con formato más detallado)
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(60, 5, utf8_decode('Asociación'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(116, 5, utf8_decode('ASOCIACIÓN DE MINEROS ARTESANALES PEPAS DE ORO DE PAMPAMARCA'), 1, 1, 'C');  // Valor

$pdf->Cell(60, 5, utf8_decode('Nro Solicitud Retiro'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(116, 5, ''.$nroSolicitud, 1, 1, 'C');  // Valor

$pdf->Cell(60, 5, utf8_decode('Derecho Minero'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell(116, 5, utf8_decode(''.$nombreZona), 1, 1, 'C');  // Sin valor

$pdf->Cell(60, 5, utf8_decode('Destino'), 1, 0, 'C', 1);  // Etiqueta
$pdf->Cell( 116, 5, utf8_decode(''.$destino), 1, 1, 'C');  // Valor

$unidad=$datoDocumento[0]['imagen_unidad'];
$unidad2=$datoDocumento[0]['imagen_unidad2'];

$pdf->Image('../actaRetiro/imagenes/'.$unidad, 32, 180, 40);

$pdf->Image('../actaRetiro/imagenes/'.$unidad, 128, 180, 40);

$pdf->AddPage();

$transportista=$datoDocumento[0]['imagen_transportista'];

$pdf->Image('../solicitudRetiro/validaciones/'.$transportista, 32, 40, 150);



$pdf->AddPage();

$vehiculo=$datoDocumento[0]['imagen_vehiculo'];

$pdf->Image('../solicitudRetiro/validaciones/'.$vehiculo, 32, 40, 150);


$pdf->AddPage();

$conductor=$datoDocumento[0]['imagen_conductor'];


$pdf->Image('../solicitudRetiro/validaciones/'.$conductor, 10, 60, 190);
   
$pdf->Output();
?>