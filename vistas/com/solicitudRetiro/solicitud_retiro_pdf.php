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

$datoDocumento = SolicitudRetiro::create()->obtenerSolicitudFormatoXID($idComprob);

if (ObjectUtil::isEmpty($datoDocumento)) {
    echo("No se encontró el documento");
    exit();
}
$fecha_retiro = $datoDocumento[0]['fecha_entrega'];
$date = new DateTime($fecha_retiro);

// Array con los nombres de los meses en español
$meses = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
    7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
];

// Obtener el día, mes y año de la fecha
$dia = $date->format('d');
$mes = $meses[(int)$date->format('m')];  // Usar el número del mes como índice
$año = $date->format('Y');

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
$pdf->SetFont('Times','B',11);
$pdf->Cell(80);
$pdf->Cell(190,7,utf8_decode('Pampamarca, ').$fechaFormateada,0,1);

$pdf->SetFont('Arial', 'BU', 10);  // Establecer la fuente con subrayado ('U')
$pdf->Cell(193, 4, utf8_decode('CARTA Nº'.$nroSolicitud.'-APOP-').$año, 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(193, 4, utf8_decode('Señor:'), 0, 1);
$pdf->Cell(193, 4, utf8_decode('Wilder Valderrama Huillcaya '), 0, 1);
$pdf->Cell(193, 4, utf8_decode('PRESIDENTE DE LA ASOCIACIÓN DE MINEROS ARTESANALES '), 0, 1);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(193, 4, utf8_decode('PEPAS DE ORO DE PAMPAMARCA '), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(193, 4, utf8_decode('Presente. -  '), 0, 1);
$pdf->Ln(7);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60);
$pdf->Cell(190,7,utf8_decode('ASUNTO: SOLICITO AUTORIZACIÓN PARA RETIRO DE MINERAL AURÍFERO. '),0,1);
$pdf->SetFont('Arial', '', 10);
$pdf->Ln(7);
$pdf->Cell(193, 4, utf8_decode('De mi especial consideración:'), 0, 1);
$pdf->Ln(5);
$html='Por la presente, me dirijo a usted con el fin de solicitar autorización para el retiro de mineral aurífero sin procesar, propiedad de la ASOCIACIÓN DE MINEROS ARTESANALES PEPAS DE ORO DE PAMPAMARCA con RUC N° 20490115804, '.$descripcionZona;
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
$pdf->Cell($linea_width, 4, utf8_decode('Área de comercialización'), 0, 1, 'C');

// Puedes agregar un salto de línea si deseas más espacio después del texto
$pdf->Ln(10);

        
$pdf->Output();
?>