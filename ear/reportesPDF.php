<?php
//require_once('../Classes/tcpdf/config/lang/eng.php');
//require_once('../Classes/tcpdf/tcpdf.php');
include 'parametros.php';
require_once('../' . $carpetaSGI . '/controlador/commons/tcpdf/config/lang/eng.php');
require_once('../' . $carpetaSGI . '/controlador/commons/tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	public $nro_serie; // Para que reciba el numero de serie del documento
	public $fec_gen; // Para que reciba la fecha de generacion del documento

	//Page header
	public function Header() {
		// Set font
		$this->SetFont('helvetica', 'B', 12);
		// Title
		$this->Write($h=0, " ABC MULTISERVICIOS GENERALES S.A.C.\nRUC: 20600759141", $link='', $fill=0, $align='', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
		// Logo
		$image_file = 'img/logoSGI.png';
//                $image_file = 'img/logo-netafim.gif';
		$this->Image($image_file, 0, 5, 40, 0, '', '', 'T', false, 100, 'R', false, false, 0, false, false, false);
	}

	// Page footer
	public function Footer() {
		$cur_y = $this->y;
		$this->SetTextColorArray($this->footer_text_color);
		//set style for cell border
		$line_width = (0.85 / $this->k);
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));

		$this->SetY(-16);

		$style = array(
			'position' => 'L',
			'align' => 'L',
			'stretch' => false,
			'fitwidth' => true,
			'cellfitalign' => '',
			'border' => false,
			'hpadding' => 'auto',
			'vpadding' => 'auto',
			'fgcolor' => array(0,0,0),
			'bgcolor' => false, //array(255,255,255),
			'text' => true,
			'font' => 'helvetica',
			'fontsize' => 8,
			'stretchtext' => 4
		);

		//$this->write1DBarcode($this->nro_serie, 'C128', '', '', '', 12, 0.4, $style, 'T');
//		$this->write2DBarcode($this->nro_serie, 'QRCODE', '', '', '', 16, $style, 'T');

		$pagenumtxt = utf8_encode('Pagina ').$this->getAliasNumPage().' de '.$this->getAliasNbPages();
		//$this->SetY($cur_y);
		$this->SetY(-15);
		//Print page number
		$this->SetX($this->original_lMargin);
		$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'R');
		$this->Ln();
		$this->Cell(0, 0, 'Documento generado el '.$this->fec_gen, '', 0, 'R');
	}
}

function getCartaEarSol($id, $output,$url=null) {
        include 'parametros.php';

	$id = abs((int) filter_var($id, FILTER_SANITIZE_NUMBER_INT));

	list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
		$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
		$usu_act, $ear_act_fec, $ear_act_motivo, $liq_mon_id, $zona_id, $est_id, $usu_id,
		$ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
		$ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
		$master_usu_id,$usuIniciales,$personaId, $dua_id,$tipoCambioFechaLiq,$guardarTcSgi,$periodo_id,
                $dua_serie,$dua_numero) = getSolicitudInfo($id);

//        $isDua = ($usu_id == $pAXISADUANA || $usu_id == $pAXISGLOBAL);
        $contadorPerfil=obtenerPerfilContador($pPERFIL_PROVEEDOR_DUA,$usu_id);
        $isDua=($contadorPerfil>0);

	$ear_numero = "EAR_".str_replace("/", "_", $ear_numero);

	$arrDet = getSolicitudDetalle($id);

	$hosp_otros_id = getIdHospOtros($liq_mon_id);

	list($dni0, $nombres0, $apePaterno0,$apeMaterno0,$cargo_id0, $fecha_ing0,
		$cargo_desc0, $area_id0, $area_desc0, $idccosto0, $banco0, $ctacte0, $sucursal0) = getInfoTrabajador($personaId);

	if ($ctacte0 !== $ear_tra_cta) {
		$resaltado = 1;
	}
	else {
		$resaltado = 0;
	}

	// Seccion que genera el PDF
	////////////////////////////

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->nro_serie = $ear_numero; // Define el numero de serie para el codigo de barras
	$pdf->fec_gen = date('d/m/Y h:i:s A');

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Administracion  ABC MULTISERVICIOS GENERALES S.A.C.');
	$pdf->SetTitle('SOLICITUD DE ENTREGAS A RENDIR Y/O VIATICOS');
	$pdf->SetSubject('De '.$ear_tra_nombres);

	// set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	//set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetMargins(30, 25, 10, true);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// ---------------------------------------------------------

	// Resaltados en color amarillo
	$pdf->SetFillColor(255, 255, 0); // Yellow

	// add a page
	$pdf->AddPage();

	$pdf->SetFont('helvetica', 'B', 12);
	$pdf->Write($h=0, 'SOLICITUD DE ENTREGAS A RENDIR Y/O VIATICOS', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
	$pdf->Ln();


	$a_c1 = 18; // ancho primera columna
	$a_c2 = 66; // ancho segunda columna
	$a_c3 = 28; // ancho tercera columna
	$t_me = 7; // tamano de letra mensaje

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, 'FECHA SOL.:', '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, diaconfecha($ear_sol_fec)." ".date('h:i:s A', strtotime($ear_sol_fec)), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, 'EAR No.:', '', 0, 'L');
	$pdf->Cell(30, 0, $ear_numero, 'LTRB', 0, 'C', true);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, 'NOMBRE:', '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, utf8_encode($ear_tra_nombres), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, '', '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell(0, 0, '', '', 0, 'L');
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, 'DNI:', '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, $ear_tra_dni, '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, 'FECHA LIQUIDACION:', '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell(0, 0, diaconfecha($ear_liq_fec), '', 0, 'L');
	$pdf->Ln();

//	$pdf->SetFont('helvetica', 'B', $t_me);
//	$pdf->Cell($a_c1, 0, 'CARGO:', '', 0, 'L');
//	$pdf->SetFont('helvetica', '', $t_me);
//	$pdf->Cell($a_c2, 0, utf8_encode($ear_tra_cargo), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, 'MONEDA:', '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, $mon_nom, '', 0, 'L');

        //DUA
        if($isDua){
            $duaSerie = $dua_serie;
            $duaNumero = $dua_numero;
            if ($dua_id != null) {
                $arrDua = getDuaXDuaId($dua_id);
                $duaSerie = $arrDua[0][4];
                $duaNumero = $arrDua[0][5];
            }
            $duaSN=$duaSerie."-".$duaNumero;
//            $arr = getDuaXDuaId($dua_id);
//            foreach ($arr as $v) {
//                $duaSN = $v[4]."-".$v[5];
//            }
            $pdf->SetFont('helvetica', 'B', $t_me);
            $pdf->Cell($a_c3, 0, "DUA No:", '', 0, 'L');
            $pdf->SetFont('helvetica', '', $t_me);
            $pdf->Cell(0, 0, $duaSN, '', 0, 'L');
        }

	$pdf->Ln();

//	$pdf->SetFont('helvetica', 'B', $t_me);
//	$pdf->Cell($a_c1, 0, 'SUCURSAL:', '', 0, 'L');
//	$pdf->SetFont('helvetica', '', $t_me);
//	$pdf->Cell($a_c2, 0, utf8_encode($ear_tra_sucursal), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, '', '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell(0, 0, '', '', 0, 'L');
	$pdf->Ln();

//	$pdf->SetFont('helvetica', 'B', $t_me);
//	$pdf->Cell($a_c1, 0, 'No. CUENTA:', '', 0, 'L');
	if ($resaltado==0) {
//		$pdf->SetFont('helvetica', '', $t_me);
//		$pdf->Cell($a_c2, 0, $ear_tra_cta, '', 0, 'L');
	}
	else {
		$largo_cta = $pdf->GetStringWidth($ear_tra_cta, 'helvetica', 'B', $t_me);
		$largo_cta = $largo_cta+2; // Para agregar el espacio del margen
//		$pdf->Cell($largo_cta, 0, $ear_tra_cta, '', 0, 'L', true);
	}
	$pdf->Ln();

//	if(!is_null($master_usu_id)) {
//		$pdf->SetFont('helvetica', 'B', $t_me);
//		$pdf->Cell($a_c1, 0, 'HECHA POR:', '', 0, 'L');
//		$pdf->SetFont('helvetica', '', $t_me);
//		$pdf->Cell($a_c2, 0, utf8_encode(getNombreTrabajador(getCodigoGeneral(getUsuAd($master_usu_id)))), '', 0, 'L');
//		$pdf->Ln();
//	}

	$pdf->Ln();

	// draw some reference lines
	$linestyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '', 'phase' => 0, 'color' => array(0, 0, 0));
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, "DETALLE DE LOS VIATICOS", '', 0, 'L');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();


	$a_c1 = 53; // ancho primera columna
	$a_c2 = 60; // ancho segunda columna
	$a_c3 = 10; // ancho tercera columna
	$a_c4 = 14; // ancho cuarta columna
	$a_c5 = 14; // ancho quinta columna
	$t_me = 6; // tamano de letra mensaje
	$monto_total = 0;

	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, '1. Boletos de viaje', '', 0, 'L');
	$pdf->Cell($a_c2, 0, '', '', 0, 'L');
	$pdf->Cell($a_c3, 0, '', '', 0, 'R');
	$pdf->Cell($a_c4, 0, 'Monto', '', 0, 'R');
	$pdf->Cell($a_c5, 0, 'Subtotal', '', 0, 'R');
	$pdf->Ln();
	foreach ($arrDet as $v) {
		if (substr($v[0], 0, 2)=='01') {
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c1, 0, utf8_encode(mb_substr($v[1], 0, 40)), '', 0, 'L');
			$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($v[2], 0, 48)), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c3, 0, $v[3], '', 0, 'R');
			$pdf->Cell($a_c4, 0, $v[4], '', 0, 'R');
			if (is_null($v[3])) $subtotal = $v[4];
			else $subtotal = $v[3]*$v[4];
			$monto_total += $subtotal;
			$pdf->Cell($a_c5, 0, number_format($subtotal, 2, '.', ','), '', 0, 'R');
			$pdf->Ln();
		}
	}
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 30+$a_c1+$a_c2+$a_c3+$a_c4+$a_c5, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, utf8_encode('2. Alimentacion / Pension'), '', 0, 'L');
	$pdf->Cell($a_c2, 0, '', '', 0, 'L');
	$pdf->Cell($a_c3, 0, 'Dias', '', 0, 'R');
	$pdf->Cell($a_c4, 0, 'Monto', '', 0, 'R');
	$pdf->Cell($a_c5, 0, 'Subtotal', '', 0, 'R');
	$pdf->Ln();
	foreach ($arrDet as $v) {
		if (substr($v[0], 0, 2)=='02') {
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c1, 0, utf8_encode(mb_substr($v[1], 0, 40)), '', 0, 'L');
			$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($v[2], 0, 48)), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c3, 0, $v[3], '', 0, 'R');
			$pdf->Cell($a_c4, 0, $v[4], '', 0, 'R');
			if (is_null($v[3])) $subtotal = $v[4];
			else $subtotal = $v[3]*$v[4];
			$monto_total += $subtotal;
			$pdf->Cell($a_c5, 0, number_format($subtotal, 2, '.', ','), '', 0, 'R');
			$pdf->Ln();
		}
	}
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 30+$a_c1+$a_c2+$a_c3+$a_c4+$a_c5, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, '3. Hospedaje', '', 0, 'L');
	$pdf->Cell($a_c2, 0, 'Ciudad', '', 0, 'L');
	$pdf->Cell($a_c3, 0, 'Dias', '', 0, 'R');
	$pdf->Cell($a_c4, 0, 'Monto', '', 0, 'R');
	$pdf->Cell($a_c5, 0, 'Subtotal', '', 0, 'R');
	$pdf->Ln();
	foreach ($arrDet as $v) {
		if (substr($v[0], 0, 2)=='03') {
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c1, 0, utf8_encode(mb_substr(getViaticoNom(substr($v[0], 0, 4)), 0, 40)), '', 0, 'L');
			if ($hosp_otros_id != $v[5]) {
				$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($v[1], 0, 48)), '', 0, 'L');
			}
			else {
				$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($v[1].' - '.$v[2], 0, 48)), '', 0, 'L');
			}
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c3, 0, $v[3], '', 0, 'R');
			$pdf->Cell($a_c4, 0, $v[4], '', 0, 'R');
			if (is_null($v[3])) $subtotal = $v[4];
			else $subtotal = $v[3]*$v[4];
			$monto_total += $subtotal;
			$pdf->Cell($a_c5, 0, number_format($subtotal, 2, '.', ','), '', 0, 'R');
			$pdf->Ln();
		}
	}
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 30+$a_c1+$a_c2+$a_c3+$a_c4+$a_c5, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, '4. Movilidad / Combustible', '', 0, 'L');
	$pdf->Cell($a_c2, 0, '', '', 0, 'L');
	$pdf->Cell($a_c3, 0, 'Dias', '', 0, 'R');
	$pdf->Cell($a_c4, 0, 'Monto', '', 0, 'R');
	$pdf->Cell($a_c5, 0, 'Subtotal', '', 0, 'R');
	$pdf->Ln();
	foreach ($arrDet as $v) {
		if (substr($v[0], 0, 2)=='04') {
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c1, 0, utf8_encode(mb_substr($v[1], 0, 40)), '', 0, 'L');
			$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($v[2], 0, 48)), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c3, 0, $v[3], '', 0, 'R');
			$pdf->Cell($a_c4, 0, $v[4], '', 0, 'R');
			if (is_null($v[3])) $subtotal = $v[4];
			else $subtotal = $v[3]*$v[4];
			$monto_total += $subtotal;
			$pdf->Cell($a_c5, 0, number_format($subtotal, 2, '.', ','), '', 0, 'R');
			$pdf->Ln();
		}
	}
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 30+$a_c1+$a_c2+$a_c3+$a_c4+$a_c5, $pdf->GetY(), $linestyle);
	$pdf->Ln();

//      PARTE DE GASTOS DE REPRESENTACION SE QUITO.
//	$pdf->SetFont('helvetica', 'BIU', $t_me);
//	$pdf->Cell($a_c1, 0, utf8_encode('5. Gastos de Representacion'), '', 0, 'L');
//	$pdf->Cell($a_c2, 0, '', '', 0, 'L');
//	$pdf->Cell($a_c3, 0, '', '', 0, 'R');
//	$pdf->Cell($a_c4, 0, 'Monto', '', 0, 'R');
//	$pdf->Cell($a_c5, 0, 'Subtotal', '', 0, 'R');
//	$pdf->Ln();
//	foreach ($arrDet as $v) {
//		if (substr($v[0], 0, 2)=='05') {
//			$pdf->SetFont('courier', '', $t_me);
//			$pdf->Cell($a_c1, 0, utf8_encode(mb_substr($v[1], 0, 40)), '', 0, 'L');
//			$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($v[2], 0, 48)), '', 0, 'L');
//			$pdf->SetFont('helvetica', '', $t_me);
//			$pdf->Cell($a_c3, 0, $v[3], '', 0, 'R');
//			$pdf->Cell($a_c4, 0, $v[4], '', 0, 'R');
//			if (is_null($v[3])) $subtotal = $v[4];
//			else $subtotal = $v[3]*$v[4];
//			$monto_total += $subtotal;
//			$pdf->Cell($a_c5, 0, number_format($subtotal, 2, '.', ','), '', 0, 'R');
//			$pdf->Ln();
//		}
//	}
//	$pdf->Ln();
//	$pdf->Line(30, $pdf->GetY(), 30+$a_c1+$a_c2+$a_c3+$a_c4+$a_c5, $pdf->GetY(), $linestyle);
//	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, '5. Otros ', '', 0, 'L');
	$pdf->Cell($a_c2, 0, utf8_encode('Item / Descripcion'), '', 0, 'L');
	$pdf->Cell($a_c3, 0, '', '', 0, 'R');
	$pdf->Cell($a_c4, 0, 'Monto', '', 0, 'R');
	$pdf->Cell($a_c5, 0, 'Subtotal', '', 0, 'R');
	$pdf->Ln();
	foreach ($arrDet as $v) {
		if (substr($v[0], 0, 2)=='06') {
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c1, 0, utf8_encode(mb_substr($v[1], 0, 40)), '', 0, 'L');
			$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($v[2], 0, 48)), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c3, 0, $v[3], '', 0, 'R');
			$pdf->Cell($a_c4, 0, $v[4], '', 0, 'R');
			if (is_null($v[3])) $subtotal = $v[4];
			else $subtotal = $v[3]*$v[4];
			$monto_total += $subtotal;
			$pdf->Cell($a_c5, 0, number_format($subtotal, 2, '.', ','), '', 0, 'R');
			$pdf->Ln();
		}
	}
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 30+$a_c1+$a_c2+$a_c3+$a_c4+$a_c5, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2, 0, 'Total Solicitado para Transferencia', '', 0, 'R');
	$pdf->Cell($a_c3, 0, '', '', 0, 'R');
	$pdf->Cell($a_c4, 0, '', '', 0, 'R');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c5, 0, $mon_simb.' '.number_format($monto_total, 2, '.', ','), '', 0, 'R');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 30+$a_c1+$a_c2+$a_c3+$a_c4+$a_c5, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', 7);
	$pdf->Cell(0, 0, "MOTIVO DE LA SOLICITUD:", '', 0, 'L');
	$pdf->Ln();
	$pdf->SetFont('helvetica', '', 7);
	$pdf->Multicell(0, 0, utf8_encode($ear_sol_motivo."\n"), 'LTRB');
	$pdf->Ln();

	// Seccion de firmas
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();

	list($dni, $nombres,$apePaterno,$apeMaterno, $cargo_id, $fecha_ing,$cargo_desc, $area_id, $area_desc,
        $idccosto,$banco, $ctacte, $sucursal) = getInfoTrabajador(obtenerPersonaIdSGI($ear_aprob_usu));

	$pdf->SetFont('helvetica', 'B', 7);
	$pdf->Cell(80, 0, utf8_encode($ear_tra_nombres), 'T', 0, 'C');
	$pdf->Cell(10, 0, '', '', 0, 'L');
	$pdf->Cell(80, 0, utf8_encode($nombres.' '.$apePaterno.' '.$apeMaterno), 'T', 0, 'C');
	$pdf->Ln();
	$pdf->Cell(80, 0, utf8_encode('Responsable de la Solicitud'), '', 0, 'C');
	$pdf->Cell(10, 0, '', '', 0, 'L');
//	$pdf->Cell(80, 0, utf8_encode($cargo_desc.' - '.$area_desc), '', 0, 'C');

	// ---------------------------------------------------------

	//Close and output PDF document
	// S: devuelve string, I: inline display, D: download pdf
	//$attach = $pdf->Output('example_003.pdf', 'S');
	//$attach = $pdf->Output('example_003.pdf', 'I');

//	return $pdf->Output($ear_numero.".pdf", $output);

        if ($output == 'F') {
            $resultado= $pdf->Output($url, $output);
            return 'Se guardo PDF en: '.$url;
        }else{
            return $pdf->Output($ear_numero.".pdf", $output);
        }
}

function getCartaEarLiq($id, $output,$url=null) {
        include 'parametros.php';
	$id = abs((int) filter_var($id, FILTER_SANITIZE_NUMBER_INT));

	list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
		$ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
		$usu_act, $ear_act_fec, $ear_act_motivo, $liq_mon_id, $zona_id, $est_id, $usu_id,
		$ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
		$ear_liq_gast_asum, $pla_id, $ear_act_obs1, $ear_aprob_usu,
                $master_usu_id,$comodin1, $comodin2, $dua_id,$tipoCambioFechaLiq,$guardarTcSgi,$periodo_id,
                $dua_serie,$dua_numero) = getSolicitudInfo($id);

//        $isDua = ($usu_id == $pAXISADUANA || $usu_id == $pAXISGLOBAL);
        $contadorPerfil=obtenerPerfilContador($pPERFIL_PROVEEDOR_DUA,$usu_id);
        $isDua=($contadorPerfil>0);

	$ear_fec_env = getFechaEnvioLiq($id);

	if ($est_id>=51 && $est_id<=53) {
		$arrLiqDet = array();
	}
	else {
		$arrLiqDet = getLiqDetalle($id);
	}

	$ear_numero = "LGS_".str_replace("/", "_", $ear_numero);

	list($fec_ini, $fec_fin) = getPeriodoLiq($id);
	$fec_ini = date('d/m/Y', strtotime($fec_ini));
	$fec_fin = date('d/m/Y', strtotime($fec_fin));

	list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
        $dataUsuario=obtenerUsuariosIdXPerfil($pGERENTE);
	list($dni, $nombres,$apePaterno,$apeMaterno, $cargo_id, $fecha_ing,
		$cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador(obtenerPersonaIdSGI($dataUsuario[0]));

	if(!is_null($pla_id)) {
		list($pla_numero, $est_id_2, $pla_reg_fec, $ear_numero_2, $tope_maximo, $usu_id_2, $ear_id, $est_nom_2, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec) = getPlanillaMovilidadInfo($pla_id);
		$arrPlaMovDet = getPlanillaMovDetalle($pla_id);
	}

	// Seccion que genera el PDF
	////////////////////////////

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->nro_serie = $ear_numero; // Define el numero de serie para el codigo de barras
	$pdf->fec_gen = date('d/m/Y h:i:s A');

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Administracion  ABC MULTISERVICIOS GENERALES S.A.C.');
	$pdf->SetTitle('LIQUIDACION DE ENTREGAS A RENDIR Y/O VIATICOS');
	$pdf->SetSubject('De '.$ear_tra_nombres);

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	//set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetMargins(30, 25, 10, true);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// ---------------------------------------------------------

	// Resaltados en color amarillo
	$pdf->SetFillColor(255, 255, 0); // Yellow

	// add a page
	$pdf->AddPage();

	$pdf->SetFont('helvetica', 'B', 12);
	$pdf->Write($h=0, 'LIQUIDACION DE ENTREGAS A RENDIR Y/O VIATICOS', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
	//$pdf->Ln();
	$pdf->SetFont('helvetica', 'I', 7);
	$pdf->Write($h=0, '(Expresado en '.$mon_nom.' '.$mon_simb.')', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
	$pdf->Ln();


	$a_c1 = 26; // ancho primera columna
	$a_c2 = 58; // ancho segunda columna
	$a_c3 = 24; // ancho tercera columna
	$t_me = 7; // tamano de letra mensaje

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, "FECHA:", '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, diaconfecha($ear_fec_env), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, "LIQUIDACION No.:", '', 0, 'L');
	$pdf->Cell(30, 0, $ear_numero, 'LTRB', 0, 'C', true);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, "RESPONSABLE:", '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, utf8_encode($ear_tra_nombres), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, "PERIODO:", '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell(0, 0, $fec_ini.' al '.$fec_fin, '', 0, 'L');
	$pdf->Ln();

//	$pdf->SetFont('helvetica', 'B', $t_me);
//	$pdf->Cell($a_c1, 0, "OFICINA SUCURSAL:", '', 0, 'L');
//	$pdf->SetFont('helvetica', '', $t_me);
//	$pdf->Cell($a_c2, 0, utf8_encode($ear_tra_sucursal), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, "DESTINATARIO:", '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, utf8_encode($nombres.' '.$apePaterno.' '.$apeMaterno), '', 0, 'L');

        //DUA
        if($isDua){
            $duaSerie = $dua_serie;
            $duaNumero = $dua_numero;
            if ($dua_id != null) {
                $arrDua = getDuaXDuaId($dua_id);
                $duaSerie = $arrDua[0][4];
                $duaNumero = $arrDua[0][5];
            }
            $duaSN=$duaSerie."-".$duaNumero;
//            $arr = getDuaXDuaId($dua_id);
//            foreach ($arr as $v) {
//                $duaSN = $v[4]."-".$v[5];
//            }
            $pdf->SetFont('helvetica', 'B', $t_me);
            $pdf->Cell($a_c3, 0, "DUA No:", '', 0, 'L');
            $pdf->SetFont('helvetica', '', $t_me);
            $pdf->Cell(0, 0, $duaSN, '', 0, 'L');
        }

	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, "MOTIVO SOLICITUD:", '', 0, 'L');
	$pdf->Cell(1, 0, " ", '', 0, 'L');//Espacio
	$pdf->SetFont('helvetica', '', $t_me);
//	$pdf->Cell($a_c2, 0, utf8_encode($ear_sol_motivo), '', 0, 'L');
        $pdf->writeHTML('<table cellspacing="0" cellpadding="1" border="0"><tr>'
                . '<td style="text-align:left;padding-left: 20px;">' . utf8_encode($ear_sol_motivo) . '</td>'
                . '</tr></table>', true, false, false, false, 'C');
	$pdf->Ln();

	$usu_liq = getUsuLiquidador($id);
	if ($usu_id != $usu_liq) {
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, 'HECHA POR:', '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
//		$pdf->Cell($a_c2, 0, utf8_encode(getNombreTrabajador(getCodigoGeneral(getUsuAd($usu_liq)))), '', 0, 'L');
		$pdf->Cell($a_c2, 0, utf8_encode(getUsuarioNombre($usu_liq)), '', 0, 'L');
		$pdf->Ln();
	}

	$pdf->Ln();

	// draw some reference lines
	$linestyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '', 'phase' => 0, 'color' => array(0, 0, 0));
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, "DETALLE", '', 0, 'L');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$a_c1 = 15; // ancho primera columna
	$a_c2 = 41; // ancho segunda columna
	$a_c3 = 13; // ancho tercera columna
	$a_c4 = 5; // ancho cuarta columna
	$a_c5 = 16; // ancho quinta columna
	$a_c6 = 35; // ancho sexta columna
	$a_c7 = 11; // ancho setima columna
	$a_c8 = 12; // ancho octava columna
	$t_me = 6; // tamano de letra mensaje
	$arr_ac = array($a_c1, $a_c2, $a_c3, $a_c4, $a_c5, $a_c6, $a_c7, $a_c8, $t_me);

	$subtotal1 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '01');

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+3*$a_c7, 0, utf8_encode("1. Boletos de Viaje / Pasajes Aereos"), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c8, 0, conComas($subtotal1), 'B', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
	$pdf->Cell($a_c7, 0, "Sub total", '', 0, 'R');
	$pdf->Cell($a_c7, 0, "IGV", '', 0, 'R');
	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	dibujarFilas($pdf, $arrLiqDet, '01', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres);
	$pdf->Ln();

	$pdf->Line(30, $pdf->GetY(), 187, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$subtotal2 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '02');

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+3*$a_c7, 0, utf8_encode("2. Alimentacion / Pension"), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c8, 0, conComas($subtotal2), 'B', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
	$pdf->Cell($a_c7, 0, "Sub total", '', 0, 'R');
	$pdf->Cell($a_c7, 0, "IGV", '', 0, 'R');
	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	dibujarFilas($pdf, $arrLiqDet, '02', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres);
	$pdf->Ln();

	$pdf->Line(30, $pdf->GetY(), 187, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$subtotal3 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '03');

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+3*$a_c7, 0, utf8_encode("3. Hospedaje"), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c8, 0, conComas($subtotal3), 'B', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
	$pdf->Cell($a_c7, 0, "Sub total", '', 0, 'R');
	$pdf->Cell($a_c7, 0, "IGV", '', 0, 'R');
	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	dibujarFilas($pdf, $arrLiqDet, '03', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres);
	$pdf->Ln();

	$pdf->Line(30, $pdf->GetY(), 187, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$subtotal4 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '04');
	$subtotal4 = str_replace(',', '', $subtotal4);
	if(!is_null($pla_id)) {
		if ($liq_mon_id == 2) {
			$pzas = explode("-", substr($pla_env_fec, 0, 10));
			$fec_doc = $pzas[2]."/".$pzas[1]."/".$pzas[0];
			$tc = getTipoCambio(2, $pla_env_fec);
			$pla_monto = $pla_monto / $tc;
		}
		$subtotal4 += $pla_monto;
	}

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+3*$a_c7, 0, utf8_encode("4. Movilidad / Combustible"), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c8, 0, conComas($subtotal4), 'B', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
	$pdf->Cell($a_c7, 0, "Sub total", '', 0, 'R');
	$pdf->Cell($a_c7, 0, "IGV", '', 0, 'R');
	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	// Insertar datos de la planilla de movilidad si es que existe
	if(!is_null($pla_id)) {
                $subTotalPM='';
                $igvPM='';

		$fec_pla = date('d/m/Y', strtotime($pla_env_fec));

		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c1, 0, $ear_tra_dni, '', 0, 'L');
		$pdf->SetFont('courier', '', $t_me);
		$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($ear_tra_nombres, 0, 31)), '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c3, 0, $fec_pla, '', 0, 'L');
		$pdf->Cell($a_c4, 0, 'PLM', '', 0, 'L');
		$pdf->Cell($a_c5, 0, $pla_numero, '', 0, 'L');
		$pdf->SetFont('courier', '', $t_me);
		$pdf->Cell($a_c6, 0, 'PLANILLA MOVILIDAD', '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c7, 0, number_format($subTotalPM, 2, '.', ','), '', 0, 'R');
		$pdf->Cell($a_c7, 0, number_format($igvPM, 2, '.', ','), '', 0, 'R');
		$pdf->Cell($a_c7, 0, number_format($pla_monto, 2, '.', ','), '', 0, 'R');
		$pdf->Ln();
	}

	dibujarFilas($pdf, $arrLiqDet, '04', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres);
	$pdf->Ln();

	$pdf->Line(30, $pdf->GetY(), 187, $pdf->GetY(), $linestyle);
	$pdf->Ln();

//	$subtotal5 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '05');
//
//	$pdf->SetFont('helvetica', 'BI', $t_me);
//	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+3*$a_c7, 0, utf8_encode("5. Gastos de Representacion"), '', 0, 'L');
//	$pdf->SetFont('helvetica', 'B', $t_me);
//	$pdf->Cell($a_c8, 0, conComas($subtotal5), 'B', 0, 'R');
//	$pdf->Ln();
//	$pdf->SetFont('helvetica', 'BIU', $t_me);
//	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
//	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
//	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
//	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
//	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
//	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
//	$pdf->Cell($a_c7, 0, "Sub total", '', 0, 'R');
//	$pdf->Cell($a_c7, 0, "IGV", '', 0, 'R');
//	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
//	$pdf->Ln();
//
//	dibujarFilas($pdf, $arrLiqDet, '05', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres);
//	$pdf->Ln();
//
//	$pdf->Line(30, $pdf->GetY(), 187, $pdf->GetY(), $linestyle);
//	$pdf->Ln();

	$subtotal6 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '06');

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+3*$a_c7, 0, utf8_encode("5. Otros"), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c8, 0, conComas($subtotal6), 'B', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
	$pdf->Cell($a_c7, 0, "Sub total", '', 0, 'R');
	$pdf->Cell($a_c7, 0, "IGV", '', 0, 'R');
	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	dibujarFilas($pdf, $arrLiqDet, '06', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres);
	$pdf->Ln();

	$pdf->Line(30, $pdf->GetY(), 187, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$total_rg = $subtotal1+$subtotal2+$subtotal3+$subtotal4+$subtotal5+$subtotal6;
	//$saldo = $ear_monto-$total_rg;
	$saldo = $ear_liq_dcto;
	$msg_saldo = "";
	$red_text = 0;
	if ($saldo < 0) {
		$msg_saldo = " a reembolsar";
		$saldo = $saldo*-1;
	}
	else if ($saldo > 0) {
		$msg_saldo = " por rendir";
		$red_text = 1;
	}

//        $subTotalRG=$total_rg/1.18;
//        $igvRG=$total_rg-$subTotalRG;
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total Rendicion de Gastos", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($total_rg), '', 0, 'R');
//	$pdf->Cell(0, 0, $mon_simb." ".conComas($subTotalRG)."    ".$mon_simb." ".conComas($igvRG)."    ".$mon_simb." ".conComas($total_rg), '', 0, 'R');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total dinero entregado en custodia - Entrega a Rendir", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ear_monto), '', 0, 'R');
	$pdf->Ln();

	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total retenciones efectuadas", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ear_liq_ret), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total retenciones no efectuadas", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ear_liq_ret_no), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total detracciones efectuadas", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ear_liq_det), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total detracciones no efectuadas", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ear_liq_det_no), '', 0, 'R');
	$pdf->Ln();

	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total documentos", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ear_liq_mon+$ear_liq_ret+$ear_liq_det), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total asumido por  ABC MULTISERVICIOS GENERALES S.A.C.", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ear_liq_gast_asum), '', 0, 'R');
	$pdf->Ln();

	$pdf->Ln();

	$pdf->Line(103, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->SetFont('helvetica', 'BI', $t_me);
	if ($red_text == 1) $pdf->SetTextColor(255, 0, 0);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Saldo".$msg_saldo, '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($saldo), '', 0, 'R');
	if ($red_text == 1) $pdf->SetTextColor(0, 0, 0);
	$pdf->Ln();

	// Seccion de firmas
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', 7);
	$pdf->Cell(80, 0, '', '', 0, 'C');
	$pdf->Cell(10, 0, '', '', 0, 'L');
	$pdf->Cell(80, 0, utf8_encode($ear_tra_nombres), 'T', 0, 'C');
//	$pdf->Cell(80, 0, utf8_encode($nombres.' '.$apePaterno.' '.$apeMaterno), 'T', 0, 'C');
	$pdf->Ln();
	$pdf->Cell(80, 0, '', '', 0, 'C');
	$pdf->Cell(10, 0, '', '', 0, 'L');
	$pdf->Cell(80, 0, utf8_encode('Responsable de la Liquidacion'), '', 0, 'C');
//	$pdf->Cell(80, 0, utf8_encode($cargo_desc), '', 0, 'C');
	$pdf->Ln();
	$pdf->Cell(80, 0, '', '', 0, 'C');
	$pdf->Cell(10, 0, '', '', 0, 'L');
//	$pdf->Cell(80, 0, utf8_encode($area_desc), '', 0, 'C');


	// Agrega la planilla de movilidad solo si existe
	if(!is_null($pla_id)) {
		// add a page
		$pdf->AddPage();

		$pdf->SetFont('helvetica', 'B', 12);
		$pdf->Write($h=0, 'PLANILLA DE GASTOS DE MOVILIDAD', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
		//$pdf->Ln();
		$pdf->SetFont('helvetica', 'I', 7);
		$pdf->Write($h=0, utf8_encode('(inciso a1 del articulo 37 de la Ley del Impuesto a la Renta)'), $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
		$pdf->Ln();

		list($fec_ini, $fec_fin) = getPeriodoPlaMov($pla_id);
		$periodo = date('n', strtotime($fec_ini));
		$fec_ini = date('d/m/Y', strtotime($fec_ini));
		$fec_fin = date('d/m/Y', strtotime($fec_fin));

                $a_c1 = 26; // ancho primera columna
                $a_c2 = 58; // ancho segunda columna
                $a_c3 = 24; // ancho tercera columna
                $t_me = 7; // tamano de letra mensaje

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "Trabajador:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, utf8_encode($ear_tra_nombres), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, utf8_encode("No."), '', 0, 'L');
		$pdf->Cell(30, 0, $pla_numero, 'LTRB', 0, 'C', true);
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "DNI:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, $ear_tra_dni, '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, utf8_encode("Periodo:"), '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell(0, 0, nombreMes($periodo), '', 0, 'L');
		$pdf->Ln();

//		$pdf->SetFont('helvetica', 'B', $t_me);
//		$pdf->Cell($a_c1, 0, "Cargo:", '', 0, 'L');
//		$pdf->SetFont('helvetica', '', $t_me);
//		$pdf->Cell($a_c2, 0, utf8_encode($ear_tra_cargo), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "Desde - Hasta:  ", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, $fec_ini.' al '.$fec_fin, '', 0, 'L');
//		$pdf->Ln();

//		$pdf->SetFont('helvetica', 'B', $t_me);
//		$pdf->Cell($a_c1, 0, "Sucursal:", '', 0, 'L');
//		$pdf->SetFont('helvetica', '', $t_me);
//		$pdf->Cell($a_c2, 0, utf8_encode($ear_tra_sucursal), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, utf8_encode("Fecha de emision:"), '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell(0, 0, $fec_pla, '', 0, 'L');
		$pdf->Ln();

		$pdf->Ln();

		// Resaltados en color amarillo
		$pdf->SetFillColor(190, 190, 190); // Gray

		$a_c1 = 60; // ancho primera columna
		$a_c2 = 16; // ancho segunda columna
		$a_c3 = 38; // ancho tercera columna
		$a_c4 = 38; // ancho cuarta columna
		$a_c5 = 18; // ancho quinta columna
		$t_me = 7; // tamano de letra mensaje

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "", 'LTR', 0, 'L', true);
		$pdf->Cell($a_c2, 0, "", 'LTR', 0, 'L', true);
		$pdf->Cell($a_c3+$a_c4+$a_c5, 0, "Desplazamiento", 'LTRB', 0, 'C', true);
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "MOTIVO", 'LRB', 0, 'C', true);
		$pdf->Cell($a_c2, 0, "FECHA", 'LRB', 0, 'C', true);
		$pdf->Cell($a_c3, 0, "Salida", 'LTRB', 0, 'C', true);
		$pdf->Cell($a_c4, 0, "Destino", 'LTRB', 0, 'C', true);
		$pdf->Cell($a_c5, 0, "Monto", 'LTRB', 0, 'C', true);
		$pdf->Ln();

		$subtotalPla = 0;

		foreach ($arrPlaMovDet as $k => $v) {
			$fec_mov = date('d/m/Y', strtotime($v[1]));

			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c1, 0, utf8_encode(mb_substr($v[0], 0, 39)), 'LTRB', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, $fec_mov, 'LTRB', 0, 'C');
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c3, 0, utf8_encode(mb_substr($v[2], 0, 24)), 'LTRB', 0, 'L');
			$pdf->Cell($a_c4, 0, utf8_encode(mb_substr($v[3], 0, 24)), 'LTRB', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c5, 0, number_format($v[6], 2, '.', ','), 'LTRB', 0, 'R');
			$pdf->Ln();

			$subtotalPla += $v[6];
		}

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1+$a_c2+$a_c3, 0, "", '', 0, 'L');
		$pdf->Cell($a_c4, 0, "Total S/.", '', 0, 'R');
		$pdf->Cell($a_c5, 0, number_format($subtotalPla, 2, '.', ','), 'TB', 0, 'R');
		$pdf->Ln();

		// Seccion de firmas
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', 7);
		$pdf->Cell(80, 0, '', '', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
		$pdf->Cell(80, 0, utf8_encode($ear_tra_nombres), 'T', 0, 'C');
//		$pdf->Cell(80, 0, utf8_encode($nombres.' '.$apePaterno.' '.$apeMaterno), 'T', 0, 'C');
		$pdf->Ln();
		$pdf->Cell(80, 0, '', '', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
		$pdf->Cell(80, 0, 'DNI '.$ear_tra_dni, '', 0, 'C');
//		$pdf->Cell(80, 0, utf8_encode($cargo_desc), '', 0, 'C');
		$pdf->Ln();
		$pdf->Cell(80, 0, '', '', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
//		$pdf->Cell(80, 0, utf8_encode($area_desc), '', 0, 'C');
	}


	// Agrega los recibos de gastos si existen
	$rgs = 0;
	foreach ($arrLiqDet as $k => $v) {
		if ($v[26]=='RGS') {

			// add a page
			list($mon_nom, $mon_iso, $mon_simb, $mon_img) = getNomMoneda($v[10]);

//			if ($rgs % 2 == 0) {
				$pdf->AddPage();
//			}
//			else {
//				$linestyle = array('T' => array('dash' => 1));
//				$pdf->Cell(0, 0, '',  $linestyle, 0, 'C');
//				$pdf->Ln();
//				$pdf->Ln();
//				$linestyle = array('dash' => 0);
//				$pdf->SetLineStyle($linestyle);
//			}

			$pdf->SetFont('helvetica', 'B', 12);
			$pdf->Write($h=0, 'DECLARACION JURADA', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
			$pdf->Ln();

			$a_c1 = 45; // ancho primera columna
			$a_c2 = 81; // ancho segunda columna
			$a_c3 = 12; // ancho tercera columna
			$t_me = 10; // tamano de letra mensaje

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1, 0, '', '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, '', '', 0, 'L');
			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c3, 0, utf8_encode("No."), '', 0, 'L');
			//$pdf->Cell(30, 0, str_pad($v[7], 5, "0", STR_PAD_LEFT).'-'.str_pad($v[8], 7, "0", STR_PAD_LEFT), 'LTRB', 0, 'C', true);
			$pdf->Cell(30, 0, $v[7].'-'.$v[8], 'LTRB', 0, 'C', true);
			$pdf->Ln();

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1, 0, '', '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, '', '', 0, 'L');
			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c3, 0, 'Por:', '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell(30, 0, $mon_simb.' '.conComas($v[12]+$v[13]), 'LTRB', 0, 'R');
			$pdf->Ln();

			$pdf->Ln();

			$t_me = 12; // tamano de letra mensaje

			$parrafo1 = 'Yo, '.$ear_tra_nombres.' con DNI '.$ear_tra_dni.' declaro haber gastado la suma de '.MontoMonetarioEnLetras($v[12]+$v[13]).' '.$mon_nom.' por concepto de '.$v[9].".\n\n";
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->MultiCell(0, 0, utf8_encode($parrafo1), 'LTR', 'J');

			$fec_doc = date('d/m/Y', strtotime($v[6]));
			$pzas = explode("/", $fec_doc);
			$parrafo2 = 'Trujillo'.', '.$pzas[0].' de '.nombreMes($pzas[1]).' del '.$pzas[2];
			$pdf->Cell(0, 0, utf8_encode($parrafo2), 'LRB', 0, 'R');
			$pdf->Ln();

			// Seccion de firmas
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();

			$pdf->SetFont('helvetica', 'B', 7);
			$pdf->Cell(80, 0, '', '', 0, 'C');
			$pdf->Cell(10, 0, '', '', 0, 'L');
			$pdf->Cell(80, 0, utf8_encode($ear_tra_nombres), 'T', 0, 'C');
			$pdf->Ln();
			$pdf->Cell(80, 0, '', '', 0, 'C');
			$pdf->Cell(10, 0, '', '', 0, 'L');
			$pdf->Cell(80, 0, 'DNI '.$ear_tra_dni, '', 0, 'C');

			$pdf->Ln(50);

			$t_me = 10; // tamano de letra mensaje
			$texto = 'Formulo la presente declaración jurada en virtud del principio de presunción de veracidad previsto'
                                . ' en el artículo IV numeral 1, 7 y 42 de la Ley del Procedimiento Administrativo General, aprobada'
                                . ' por la Ley N° 27444, sujetándome a las acciones legales y/o penales que corresponden a la'
                                . ' legislación nacional vigente.';

			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->MultiCell(0, 0, $texto, '', 'L');

			$rgs++;
		}
	}


	// ---------------------------------------------------------

	//Close and output PDF document
	// S: devuelve string, I: inline display, D: download pdf
	//$attach = $pdf->Output('example_003.pdf', 'S');
	//$attach = $pdf->Output('example_003.pdf', 'I');

//	return $pdf->Output($ear_numero.".pdf", $output);

        if ($output == 'F') {
            $resultado= $pdf->Output($url, $output);
            return 'Se guardo PDF en: '.$url;
        }else{
            return $pdf->Output($ear_numero.".pdf", $output);
        }

}

function dibujarFilas($pdf, $arrLiqDet, $conc_cod, $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres, $cch=0) {
	list($a_c1, $a_c2, $a_c3, $a_c4, $a_c5, $a_c6, $a_c7, $a_c8, $t_me) = $arr_ac;
	foreach ($arrLiqDet as $k => $v) {
		if (startsWith($v[1], $conc_cod) && $v[21]==1) {
			$total_doc = $v[12]+$v[13];
                        // $subTotal=$total_doc/1.18;
                        // $igv=$total_doc-$subTotal;
                        $subTotalAfecto=$v[12]/(1 + ($v[47] == "0.10" ? floatval($v[47]) : 0.18));
                        $igvAfecto=$v[12]-$subTotalAfecto;
                        $subTotalNoAfecto=$v[13];
                        //configuracion sin IGV
                        $subTotal=$subTotalAfecto+$subTotalNoAfecto;
                        $igv=$igvAfecto;
                        //6 -> doc_tax_code de doc_tipos
                        if($v[32]==6 || $v[12]*1==0){
                            // $total_doc = $v[13];
                            $subTotal='';
                            $igv='';
                        }
			if ($liq_mon_id == 1 && $v[10] == 2) $total_doc = $total_doc * $v[14];
			else if ($liq_mon_id == 2 && $v[10] == 1) $total_doc = $total_doc / $v[14];
			$fec_doc = date('d/m/Y', strtotime($v[6]));

			$pdf->SetFont('helvetica', '', $t_me);
			if ($v[26]!='RGS') {
				$pdf->Cell($a_c1, 0, $v[3], '', 0, 'L');
				$pdf->SetFont('courier', '', $t_me);
				$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($pdf->unhtmlentities($v[4]), 0, 31)), '', 0, 'L');
			}
			else {
				if ($cch==0) {
					$pdf->Cell($a_c1, 0, $ear_tra_dni, '', 0, 'L');
					$pdf->SetFont('courier', '', $t_me);
					$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($ear_tra_nombres, 0, 31)), '', 0, 'L');
				}
				else if ($cch==1 && strlen($v[3])==8) {
					// Obtiene datos del trabajador a traves de su dni
					list($usu_dni_rgs, $usu_nombres_rgs, $cargo_id_rgs, $fecha_ing_rgs,
						$usu_cargo_desc_rgs, $area_id_rgs, $area_desc_rgs, $idccosto_rgs, $banco_rgs, $ctacte_rgs, $usu_sucursal_rgs) = getInfoTrabajador($v[3]);

					$pdf->Cell($a_c1, 0, $usu_dni_rgs, '', 0, 'L');
					$pdf->SetFont('courier', '', $t_me);
					$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($usu_nombres_rgs, 0, 31)), '', 0, 'L');
				}
				else if ($cch==1 && strlen($v[3])!=8) {
					$pdf->Cell($a_c1, 0, $v[3], '', 0, 'L');
					$pdf->SetFont('courier', '', $t_me);
					$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($pdf->unhtmlentities($v[4]), 0, 31)), '', 0, 'L');
				}
			}
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c3, 0, $fec_doc, '', 0, 'L');
			$pdf->Cell($a_c4, 0, $v[26], '', 0, 'L');

                        $parteSerie=str_pad($v[7], 5, "0", STR_PAD_LEFT).'-';
                        if($v[2]==11){
                            if(str_pad($v[7], 5, "0", STR_PAD_LEFT)=='00000'){
                                $parteSerie='';
                            }else{
                                $parteSerie=$v[7].'-';
                            }
                        }
			$pdf->Cell($a_c5, 0, $parteSerie.str_pad($v[8], 7, "0", STR_PAD_LEFT), '', 0, 'L');
			$pdf->SetFont('courier', '', $t_me);
			//if ($v[26]=='OTR' || $v[26]=='RGS') {

			// Regla 1: Si el RUC empieza con 20 y registra un documento BOL estas deben considerarse GND - TIPO DOC XX y C9 al exportar
			if($v[25]=='03' && strlen($v[3])==11 && strpos($v[3], '20')!==false ) {
				$v[25]='XX';
			}
			// Regla 2: Utilizar la Lista de Documentos por RUC, si en la lista asociada al RUC se cuenta con FACTURA y el documento registrado
			// es boleta debe considerarse a este GND- TIPO DOC XX y C9 al exportar
			else if ($v[25]=='03' && strlen($v[3])==11 && $v[30]==1) {
				$v[25]='XX';
			}
			// Regla 6: Si el TAX Code es 4 todo el monto se va a C9
			else if ($v[32]==4) {
				$v[25]='XX';
			}

			// Agrega 'Otros' a la glosa si es Control Vehicular y la placa es Otros
			if($v[33]==1 && $v[34]==null) {
				$v[9] = 'Otros '.$v[9];
			}

			if($v[25]=='XX') {
				$pdf->Cell($a_c6, 0, utf8_encode(mb_substr('GND-'.$v[9], 0, 30)), '', 0, 'L');
			}
			else {
				$pdf->Cell($a_c6, 0, utf8_encode(mb_substr($v[9], 0, 30)), '', 0, 'L');
			}
			$pdf->SetFont('helvetica', '', $t_me);
                        if($v[32]==6){
                            $pdf->Cell($a_c7, 0, '', '', 0, 'R');
                            $pdf->Cell($a_c7, 0, '', '', 0, 'R');
                        }else{
                            $pdf->Cell($a_c7, 0, number_format($subTotal, 2, '.', ','), '', 0, 'R');
                            $pdf->Cell($a_c7, 0, number_format($igv, 2, '.', ','), '', 0, 'R');
                        }
			$pdf->Cell($a_c7, 0, number_format($total_doc, 2, '.', ','), '', 0, 'R');
			$pdf->Ln();

			if ($v[16]==2 || $v[16]==1) {
				list($ret_tasa, $ret_minmonto, $det_tasa, $det_minmonto) = getLiqConceptosRetDet($v[0], $v[6]);
				$mon_fila = $v[17];
				if ($liq_mon_id == 1 && $v[10] == 2) $mon_fila = $mon_fila * $v[14];
				else if ($liq_mon_id == 2 && $v[10] == 1) $mon_fila = $mon_fila / $v[14];

				$pdf->SetFont('helvetica', '', $t_me);
				$pdf->Cell($a_c1+$a_c2+$a_c3, 0, '', '', 0, 'L');
				$pdf->Cell($a_c4, 0, $v[26], '', 0, 'L');
				$pdf->Cell($a_c5, 0, str_pad($v[7], 5, "0", STR_PAD_LEFT).'-'.str_pad($v[8], 7, "0", STR_PAD_LEFT), '', 0, 'L');
				$pdf->SetFont('courier', 'I', $t_me);
				if ($v[16]==2) {
					$pdf->Cell($a_c6, 0, 'RETENCION ('.$ret_tasa.'%)', '', 0, 'L');
				}
				else {
					$pdf->Cell($a_c6, 0, 'DETRACCION ('.$det_tasa.'%)', '', 0, 'L');
				}
				$pdf->SetFont('helvetica', '', $t_me);
				$pdf->SetTextColor(255, 0, 0);
				$pdf->Cell($a_c7, 0, conComas($mon_fila*-1), '', 0, 'R');
				$pdf->SetTextColor(0, 0, 0);
				$pdf->Ln();

				if ($v[15] == 0) {
					$pdf->SetFont('helvetica', '', $t_me);
					$pdf->Cell($a_c1+$a_c2+$a_c3, 0, '', '', 0, 'L');
					$pdf->Cell($a_c4, 0, $v[26], '', 0, 'L');
					$pdf->Cell($a_c5, 0, str_pad($v[7], 5, "0", STR_PAD_LEFT).'-'.str_pad($v[8], 7, "0", STR_PAD_LEFT), '', 0, 'L');
					$pdf->SetFont('courier', 'I', $t_me);
					if ($v[16]==2) {
						$pdf->Cell($a_c6, 0, 'RETENCION NO EFECTUADA', '', 0, 'L');
					}
					else {
						$pdf->Cell($a_c6, 0, 'DETRACCION NO EFECTUADA', '', 0, 'L');
					}
					$pdf->SetFont('helvetica', '', $t_me);
					$pdf->Cell($a_c7, 0, conComas($mon_fila), '', 0, 'R');
					$pdf->Ln();
				}
			}

			$monto_no_asumido = round($v[12]+$v[13]-$v[22]);
                        $subTotalMontoNA=$monto_no_asumido/1.18;
                        $igvMontoNA=$monto_no_asumido-$subTotalMontoNA;
			if ($monto_no_asumido>0) {
				if ($liq_mon_id == 1 && $v[10] == 2) $monto_no_asumido = $monto_no_asumido * $v[14];
				else if ($liq_mon_id == 2 && $v[10] == 1) $monto_no_asumido = $monto_no_asumido / $v[14];

				$pdf->SetFont('helvetica', '', $t_me);
				$pdf->Cell($a_c1+$a_c2+$a_c3, 0, '', '', 0, 'L');
				$pdf->Cell($a_c4, 0, $v[26], '', 0, 'L');
				$pdf->Cell($a_c5, 0, str_pad($v[7], 5, "0", STR_PAD_LEFT).'-'.str_pad($v[8], 7, "0", STR_PAD_LEFT), '', 0, 'L');
				$pdf->SetFont('courier', 'I', $t_me);
				$pdf->Cell($a_c6, 0, 'MONTO NO ASUMIDO', '', 0, 'L');
				$pdf->SetFont('helvetica', '', $t_me);
				$pdf->SetTextColor(255, 0, 0);
				$pdf->Cell($a_c7, 0, conComas($subTotalMontoNA*-1), '', 0, 'R');
				$pdf->Cell($a_c7, 0, conComas($igvMontoNA*-1), '', 0, 'R');
				$pdf->Cell($a_c7, 0, conComas($monto_no_asumido*-1), '', 0, 'R');
				$pdf->SetTextColor(0, 0, 0);
				$pdf->Ln();
			}
		}
	}
}

function getCartaCajaChicaLote($id, $output, $hist=0) {
	$id = abs((int) filter_var($id, FILTER_SANITIZE_NUMBER_INT));

	list($ccl_id, $cch_nombre, $ccl_numero, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ccl_monto_ini, $ccl_gti, $ccl_dg_json, $ccl_cta_bco,
		$ccl_ape_fec, $ape_usu_nombre, $ccl_cie_fec, $cie_usu_nombre,
		$ccl_aprob_fec, $aprob_usu_nombre, $ccl_act_fec, $act_usu_nombre,
		$ccl_monto_usado, $est_id, $est_nom, $suc_nombre,
		$ccl_ret, $ccl_ret_no, $ccl_det, $ccl_det_no, $ccl_gast_asum, $ccl_pend, $cch_id, $liq_mon_id,
		$ccl_ape_usu, $ccl_cie_usu, $ccl_aprob_usu, $ccl_act_usu,
		$ccl_cuadre, $ccl_banco) = getLoteCajaChicaInfo($id);
	// $pla_id = null;

	// list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
		// $ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
		// $usu_act, $ear_act_fec, $ear_act_motivo, $liq_mon_id, $zona_id, $est_id, $usu_id,
		// $ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
		// $ear_liq_gast_asum, $pla_id) = getSolicitudInfo($id);
	$ear_tra_dni = "DNI_REPLACE";
	$ear_tra_nombres = "NOM_REPLACE";

	//$ear_fec_env = getFechaEnvioLiq($id);

	// if ($est_id>=51 && $est_id<=53) {
		// $arrLiqDet = array();
	// }
	// else {
		// $arrLiqDet = getLiqDetalle($id);
	// }
	if ($hist==1) {
		list($ccl_monto_usado, $ccl_ret, $ccl_ret_no, $ccl_det, $ccl_det_no) = getLoteCajaChicaInfoHist($ccl_id);
		$arrLiqDet = getLoteDetalleHist($ccl_id, 1);
	}
	else {
		$arrLiqDet = getLoteDetalle($ccl_id);
	}

	$doc_numero = "CCH_".str_replace("/", "_", $ccl_numero).($hist==1?'_HIST':'');

	//list($fec_ini, $fec_fin) = getPeriodoLiq($id);
	$fec_ini = date('d/m/Y', strtotime($ccl_ape_fec));
	$fec_fin = date('d/m/Y', strtotime($ccl_cie_fec));

	// list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
	// list($dni, $nombres, $cargo_id, $fecha_ing,
		// $cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador(getCodigoGeneral(getUsuAd($usu_id_jefe)));
	list($dni_1, $nombres_1, $cargo_id_1, $fecha_ing_1,
		$cargo_desc_1, $area_id_1, $area_desc_1, $idccosto_1, $banco_1, $ctacte_1, $sucursal_1) = getInfoTrabajador(getCodigoGeneral(getUsuAd($ccl_cie_usu)));
	list($dni_2, $nombres_2, $cargo_id_2, $fecha_ing_2,
		$cargo_desc_2, $area_id_2, $area_desc_2, $idccosto_2, $banco_2, $ctacte_2, $sucursal_2) = getInfoTrabajador(getCodigoGeneral(getUsuAd($ccl_aprob_usu)));

	// if(!is_null($pla_id)) {
		// list($pla_numero, $est_id_2, $pla_reg_fec, $ear_numero_2, $tope_maximo, $usu_id_2, $ear_id, $est_nom_2, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec) = getPlanillaMovilidadInfo($pla_id);
		// $arrPlaMovDet = getPlanillaMovDetalle($pla_id);
	// }
	$arrPla = getPlanillasMovilidadCCL($ccl_id);
	$arrDP = getDocPend($ccl_id);

	// Seccion que genera el PDF
	////////////////////////////

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->nro_serie = $doc_numero; // Define el numero de serie para el codigo de barras
	$pdf->fec_gen = date('d/m/Y h:i:s A');

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Administracion  ABC MULTISERVICIOS GENERALES S.A.C.');
	$pdf->SetTitle('LIQUIDACION DE CAJA CHICA');
	$pdf->SetSubject('De '.$cch_nombre);

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	//set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetMargins(30, 25, 10, true);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// ---------------------------------------------------------

	// Resaltados en color amarillo
	$pdf->SetFillColor(255, 255, 0); // Yellow

	// add a page
	$pdf->AddPage();

	// Agrega marca de agua a la pagina si el estado del lote no esta aprobado
		// if ($est_id<3) {
		// $pdf->SetFont('helvetica', 'B', 40);
		// $pdf->SetTextColor(255, 153, 153);
		// Start Transformation
		// $pdf->StartTransform();
		// Rotate 20 degrees counter-clockwise centered by (70,110) which is the lower left corner of the rectangle
		// $pdf->Rotate(60, 60, 220);
		// $pdf->Text(60, 220, 'PROVISIONAL - SIN VALOR');
		// Stop Transformation
		// $pdf->StopTransform();
		// $pdf->SetXY(30, 25);
		// $pdf->SetTextColor(0, 0, 0);
	// }

	$pdf->SetFont('helvetica', 'B', 12);
	$pdf->Write($h=0, 'LIQUIDACION DE CAJA CHICA', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
	//$pdf->Ln();
	$pdf->SetFont('helvetica', 'I', 7);
	$pdf->Write($h=0, '(Expresado en '.$mon_nom.' '.$mon_simb.')', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
	$pdf->Ln();


	$a_c1 = 21; // ancho primera columna
	$a_c2 = 59; // ancho segunda columna
	$a_c3 = 26; // ancho tercera columna
	$t_me = 7; // tamano de letra mensaje

	if (!is_null($ccl_aprob_fec)) {
		$fecha = $ccl_aprob_fec;
	}
	else {
		$fecha = 'SIN APROBAR';
	}

	if (!is_null($ccl_aprob_usu)) {
		$responsable = $nombres_2;
	}
	else {
		$responsable = 'SIN APROBAR';
	}

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, "FECHA:", '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, $fecha, '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, "CAJA CHICA No.:", '', 0, 'L');
	$largo_doc = $pdf->GetStringWidth($doc_numero, 'helvetica', 'B', $t_me);
	$largo_doc = $largo_doc+2; // Para agregar el espacio del margen
	$pdf->Cell($largo_doc, 0, $doc_numero, 'LTRB', 0, 'C', true);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, "ENCARGADO:", '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, utf8_encode($nombres_1), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, "PERIODO:", '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell(0, 0, $fec_ini.' al '.$fec_fin, '', 0, 'L');
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, "RESPONSABLE:", '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, utf8_encode($responsable), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, "OFICINA SUCURSAL:", '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell(0, 0, utf8_encode($suc_nombre), '', 0, 'L');
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, "CAJA CHICA:", '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, utf8_encode($cch_nombre), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, '', '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell(0, 0, '', '', 0, 'L');
	$pdf->Ln();

	// draw some reference lines
	$linestyle = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '', 'phase' => 0, 'color' => array(0, 0, 0));
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, "I. DETALLE RENDICION DE GASTOS", '', 0, 'L');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$a_c1 = 15; // ancho primera columna
	$a_c2 = 41; // ancho segunda columna
	$a_c3 = 13; // ancho tercera columna
	$a_c4 = 5; // ancho cuarta columna
	$a_c5 = 16; // ancho quinta columna
	$a_c6 = 40; // ancho sexta columna
	$a_c7 = 12; // ancho setima columna
	$a_c8 = 12; // ancho octava columna
	$t_me = 6; // tamano de letra mensaje
	$arr_ac = array($a_c1, $a_c2, $a_c3, $a_c4, $a_c5, $a_c6, $a_c7, $a_c8, $t_me);

	$subtotal1 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '01');

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+$a_c7, 0, utf8_encode("1. Boletos de Viaje / Pasajes Aereos"), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c8, 0, conComas($subtotal1), 'B', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	dibujarFilas($pdf, $arrLiqDet, '01', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres, 1);
	$pdf->Ln();

	$pdf->Line(30, $pdf->GetY(), 184, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$subtotal2 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '02');

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+$a_c7, 0, utf8_encode("2. Alimentacion / Pension"), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c8, 0, conComas($subtotal2), 'B', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	dibujarFilas($pdf, $arrLiqDet, '02', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres, 1);
	$pdf->Ln();

	$pdf->Line(30, $pdf->GetY(), 184, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$subtotal3 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '03');

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+$a_c7, 0, utf8_encode("3. Hospedaje"), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c8, 0, conComas($subtotal3), 'B', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	dibujarFilas($pdf, $arrLiqDet, '03', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres, 1);
	$pdf->Ln();

	$pdf->Line(30, $pdf->GetY(), 184, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$subtotal4 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '04');
	$subtotal4 = str_replace(',', '', $subtotal4);
	if(count($arrPla)>0) {
		foreach ($arrPla as $v) {
			$pla_monto = $v[8];
			if ($liq_mon_id == 2) {
				$pzas = explode("-", substr($v[11], 0, 10));
				$fec_doc = $pzas[2]."/".$pzas[1]."/".$pzas[0];
				$tc = getTipoCambio(2, $v[11]);
				$pla_monto = $v[8] / $tc;
			}
			$subtotal4 += $pla_monto;
		}
	}
	// if(!is_null($pla_id)) {
		// if ($liq_mon_id == 2) {
			// $pzas = explode("-", substr($pla_env_fec, 0, 10));
			// $fec_doc = $pzas[2]."/".$pzas[1]."/".$pzas[0];
			// $tc = getTipoCambio(2, $pla_env_fec);
			// $pla_monto = $pla_monto / $tc;
		// }
		// $subtotal4 += $pla_monto;
	// }

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+$a_c7, 0, utf8_encode("4. Movilidad / Combustible"), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c8, 0, conComas($subtotal4), 'B', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	// Insertar datos de la planilla de movilidad si es que existe
	if(count($arrPla)>0) {
		foreach ($arrPla as $v) {
			// Convierte a dolares si es necesario
			if ($liq_mon_id == 2) {
				$tc = getTipoCambio(2, $v[11]);
				$pla_monto = $v[8] / $tc;
			}
			else {
				$pla_monto = $v[8];
			}

			// Obtiene datos del trabajador a traves de su dni
			list($usu_dni_pla, $usu_nombres_pla, $cargo_id_pla, $fecha_ing_pla,
				$usu_cargo_desc_pla, $area_id_pla, $area_desc_pla, $idccosto_pla, $banco_pla, $ctacte_pla, $usu_sucursal_pla) = getInfoTrabajador(getCodigoGeneral(getUsuAd($v[5])));

			$fec_pla = date('d/m/Y', strtotime($v[11]));

			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c1, 0, $usu_dni_pla, '', 0, 'L');
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c2, 0, utf8_encode(mb_substr($usu_nombres_pla, 0, 31)), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c3, 0, $fec_pla, '', 0, 'L');
			$pdf->Cell($a_c4, 0, 'PLM', '', 0, 'L');
			$pdf->Cell($a_c5, 0, $v[0], '', 0, 'L');
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c6, 0, 'PLANILLA MOVILIDAD', '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c7, 0, number_format($pla_monto, 2, '.', ','), '', 0, 'R');
			$pdf->Ln();
		}
	}
	// if(!is_null($pla_id)) {
		// $fec_pla = date('d/m/Y', strtotime($pla_env_fec));

		// $pdf->SetFont('helvetica', '', $t_me);
		// $pdf->Cell($a_c1, 0, $ear_tra_dni, '', 0, 'L');
		// $pdf->SetFont('courier', '', $t_me);
		// $pdf->Cell($a_c2, 0, utf8_encode(mb_substr($ear_tra_nombres, 0, 31)), '', 0, 'L');
		// $pdf->SetFont('helvetica', '', $t_me);
		// $pdf->Cell($a_c3, 0, $fec_pla, '', 0, 'L');
		// $pdf->Cell($a_c4, 0, 'PLM', '', 0, 'L');
		// $pdf->Cell($a_c5, 0, $pla_numero, '', 0, 'L');
		// $pdf->SetFont('courier', '', $t_me);
		// $pdf->Cell($a_c6, 0, 'PLANILLA MOVILIDAD', '', 0, 'L');
		// $pdf->SetFont('helvetica', '', $t_me);
		// $pdf->Cell($a_c7, 0, number_format($pla_monto, 2, '.', ','), '', 0, 'R');
		// $pdf->Ln();
	// }

	dibujarFilas($pdf, $arrLiqDet, '04', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres, 1);
	$pdf->Ln();

	$pdf->Line(30, $pdf->GetY(), 184, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$subtotal5 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '05');

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+$a_c7, 0, utf8_encode("5. Gastos de Representacion"), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c8, 0, conComas($subtotal5), 'B', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	dibujarFilas($pdf, $arrLiqDet, '05', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres, 1);
	$pdf->Ln();

	$pdf->Line(30, $pdf->GetY(), 184, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$subtotal6 = getSubTotalConcepto($arrLiqDet, $liq_mon_id, '06');

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4+$a_c5+$a_c6+$a_c7, 0, utf8_encode("6. Otros"), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c8, 0, conComas($subtotal6), 'B', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "RUC", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Proveedor", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "Fecha", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Doc", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Serie y Nro", '', 0, 'L');
	$pdf->Cell($a_c6, 0, "Detalle", '', 0, 'L');
	$pdf->Cell($a_c7, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	dibujarFilas($pdf, $arrLiqDet, '06', $liq_mon_id, $arr_ac, $ear_tra_dni, $ear_tra_nombres, 1);
	$pdf->Ln();

	$pdf->Line(30, $pdf->GetY(), 184, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$total_rg = $subtotal1+$subtotal2+$subtotal3+$subtotal4+$subtotal5+$subtotal6;
	$saldo = $total_rg;
	//$saldo = $ear_liq_dcto;
	$msg_saldo = "";
	$red_text = 0;
	if ($saldo > 0) {
		$msg_saldo = " a Desembolsar";
		// $saldo = $saldo*-1;
	}
	// else if ($saldo > 0) {
		// $msg_saldo = " por Rendir";
		// $red_text = 1;
	// }

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total Rendicion de Gastos", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($total_rg), '', 0, 'R');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	// SECCION DETALLE RECIBOS PENDIENTES POR LIQUIDAR
	$t_me = 7; // tamano de letra mensaje
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, "II. DETALLE RECIBOS PENDIENTES POR LIQUIDAR", '', 0, 'L');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$a_c1 = 31; // ancho primera columna
	$a_c2 = 21; // ancho segunda columna
	$a_c3 = 13; // ancho tercera columna
	$a_c4 = 40; // ancho cuarta columna
	$a_c5 = 12; // ancho quinta columna
	$a_c6 = 37; // ancho sexta columna
	$t_me = 6; // tamano de letra mensaje

	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "Receptor", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Nro. Doc.", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "F. Entrega", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Concepto", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	$tot_dpp = 0;
	foreach ($arrDP as $v) {
		if ($v[6]==1) {
			$fec_rcc = date('d/m/Y', strtotime($v[3]));

			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c1, 0, utf8_encode(mb_substr(strtoupper($v[1]), 0, 23)), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, 'RCC '.$v[2], '', 0, 'L');
			$pdf->Cell($a_c3, 0, $fec_rcc, '', 0, 'L');
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c4, 0, utf8_encode(mb_substr($v[4], 0, 30)), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c5, 0, $v[5], '', 0, 'R');
			$pdf->Ln();
			$tot_dpp += $v[5];
		}
	}
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell(74, 0, "", '', 0, 'L');
	$pdf->Cell(80, 0, "Total Recibos Pendientes por Liquidar", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($tot_dpp), '', 0, 'R');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	// SECCION EFECTIVO EN CAJA
	$t_me = 7; // tamano de letra mensaje
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, "III. EFECTIVO EN CAJA", '', 0, 'L');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();
	$t_me = 6; // tamano de letra mensaje

	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "Detalle Billetes", '', 0, 'R');
	$pdf->Cell($a_c2, 0, "Denominacion", '', 0, 'R');
	$pdf->Cell($a_c3, 0, "Cantidad", '', 0, 'R');
	$pdf->Cell($a_c4, 0, '', '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	$tot_cua = 0;
	$arrCuadre = getCuadreGrabadoBD(1, $ccl_id);
	foreach ($arrCuadre as $v) {
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c1, 0, '', '', 0, 'R');
		$pdf->Cell($a_c2, 0, $v[1], '', 0, 'R');
		$pdf->Cell($a_c3, 0, $v[2], '', 0, 'R');
		$pdf->Cell($a_c4, 0, '', '', 0, 'L');
		$pdf->Cell($a_c5, 0, conComas($v[1]*$v[2]), '', 0, 'R');
		$pdf->Ln();
		$tot_cua += ($v[1]*$v[2]);
	}
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "Detalle Monedas", '', 0, 'R');
	$pdf->Cell($a_c2, 0, "Denominacion", '', 0, 'R');
	$pdf->Cell($a_c3, 0, "Cantidad", '', 0, 'R');
	$pdf->Cell($a_c4, 0, '', '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Monto", '', 0, 'R');
	$pdf->Ln();

	$arrCuadre = getCuadreGrabadoBD(2, $ccl_id);
	foreach ($arrCuadre as $v) {
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c1, 0, '', '', 0, 'R');
		$pdf->Cell($a_c2, 0, $v[1], '', 0, 'R');
		$pdf->Cell($a_c3, 0, $v[2], '', 0, 'R');
		$pdf->Cell($a_c4, 0, '', '', 0, 'L');
		$pdf->Cell($a_c5, 0, conComas($v[1]*$v[2]), '', 0, 'R');
		$pdf->Ln();
		$tot_cua += ($v[1]*$v[2]);
	}
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell(74, 0, "", '', 0, 'L');
	$pdf->Cell(80, 0, "Total Efectivo en Caja", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($tot_cua), '', 0, 'R');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	// SECCION SALDO CONTABLE EN CUENTA BCP
	$t_me = 7; // tamano de letra mensaje
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(154, 0, "IV. SALDO CONTABLE EN CUENTA BCP (No incluye ITF, ni diferencias por tipo de cambio)", '', 0, 'L');
	$t_me = 6; // tamano de letra mensaje
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_banco), '', 0, 'R');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$a_c1 = 15; // ancho primera columna
	$a_c2 = 41; // ancho segunda columna
	$a_c3 = 13; // ancho tercera columna
	$a_c4 = 5; // ancho cuarta columna
	$a_c5 = 16; // ancho quinta columna
	$a_c6 = 40; // ancho sexta columna
	$a_c7 = 12; // ancho setima columna
	$a_c8 = 12; // ancho octava columna

	// SECCION TOTALES
	$pdf->SetFont('helvetica', 'BI', $t_me);
	if ($red_text == 1) $pdf->SetTextColor(255, 0, 0);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Monto".$msg_saldo, '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($saldo), '', 0, 'R');
	$pdf->Ln();

	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total documentos", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_monto_usado+$ccl_ret+$ccl_det), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total retenciones efectuadas", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_ret), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total retenciones no efectuadas", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_ret_no), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total detracciones efectuadas", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_det), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total detracciones no efectuadas", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_det_no), '', 0, 'R');
	$pdf->Ln();

	$pdf->Ln();

	$saldo2 = $ccl_monto_ini-$ccl_monto_usado-$ccl_pend;
	if ($est_id==2) $ccl_gast_asum = $ccl_monto_usado+$ccl_ret+$ccl_det;

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total asumido por  ABC MULTISERVICIOS GENERALES S.A.C.", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_gast_asum), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total pendientes", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_pend), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total efectivo fisico en caja", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_cuadre), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Saldo en cuenta BCP", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_banco), '', 0, 'R');
	$pdf->Ln();

	$pdf->Ln();

	$pdf->Line(103, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total cuadre Caja Chica", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_gast_asum+$ccl_pend+$ccl_cuadre+$ccl_banco), '', 0, 'R');
	$pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Total dinero entregado en custodia - Caja Chica", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_monto_ini), '', 0, 'R');
	$pdf->Ln();

	// $pdf->Ln();
	// $pdf->Ln();
	// $pdf->Ln();

	// $pdf->SetFont('helvetica', 'BI', $t_me);
	// $pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	// $pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Monto de efectivo caja+cuenta", '', 0, 'L');
	// $pdf->SetFont('helvetica', 'B', $t_me);
	// $pdf->Cell(0, 0, $mon_simb." ".conComas($ccl_cuadre+$ccl_banco), '', 0, 'R');
	// $pdf->Ln();
	// $pdf->SetFont('helvetica', 'BI', $t_me);
	// $pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	// $pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Monto de efectivo calculado", '', 0, 'L');
	// $pdf->SetFont('helvetica', 'B', $t_me);
	// $pdf->Cell(0, 0, $mon_simb." ".conComas($saldo2), '', 0, 'R');
	// $pdf->Ln();
	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
	$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "Diferencia", 'T', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($saldo2-$ccl_cuadre-$ccl_banco), 'T', 0, 'R');
	$pdf->Ln();
	if ($red_text == 1) $pdf->SetTextColor(0, 0, 0);

	$dif2 = $saldo2-$ccl_cuadre-$ccl_banco;
	$msj2 = '';
	switch (true) {
		case ($saldo2 < 0):
			$dif2 = $saldo2*-1;
			$msj2 = "Se ha excedido del efectivo asignado por el monto de $mon_simb ".conComas($dif2);
			break;
		case ($dif2 == 0):
			break;
		case ($dif2 > 0):
			$msj2 = "Falta efectivo $mon_simb ".conComas($dif2);
			break;
		case ($dif2 < 0):
			$dif2 = $dif2*-1;
			$msj2 = "Sobra efectivo $mon_simb ".conComas($dif2);
			break;
	}
	if (strlen($msj2)>0) {
		$pdf->SetTextColor(255, 0, 0);
		$pdf->SetFont('helvetica', 'BI', $t_me);
		$pdf->Cell($a_c1+$a_c2+$a_c3+$a_c4, 0, "", '', 0, 'L');
		$pdf->Cell($a_c5+$a_c6+$a_c7+$a_c8, 0, "*Nota: $msj2", '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->Ln();
	}

	$pdf->Ln();
	$pdf->Ln();

	// SECCION DETALLE RECIBOS PENDIENTES LIQUIDADOS / ANULADOS
	$t_me = 7; // tamano de letra mensaje
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, "V. DETALLE RECIBOS PENDIENTES LIQUIDADOS / ANULADOS", '', 0, 'L');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	$a_c1 = 31; // ancho primera columna
	$a_c2 = 21; // ancho segunda columna
	$a_c3 = 13; // ancho tercera columna
	$a_c4 = 40; // ancho cuarta columna
	$a_c5 = 12; // ancho quinta columna
	$a_c6 = 37; // ancho sexta columna
	$t_me = 6; // tamano de letra mensaje

	$pdf->SetFont('helvetica', 'BIU', $t_me);
	$pdf->Cell($a_c1, 0, "Receptor", '', 0, 'L');
	$pdf->Cell($a_c2, 0, "Nro. Doc.", '', 0, 'L');
	$pdf->Cell($a_c3, 0, "F. Entrega", '', 0, 'L');
	$pdf->Cell($a_c4, 0, "Concepto", '', 0, 'L');
	$pdf->Cell($a_c5, 0, "Monto", '', 0, 'R');
	$pdf->Cell($a_c6, 0, "Comentario", '', 0, 'L');
	$pdf->Ln();

	$tot_dpl = 0;
	foreach ($arrDP as $v) {
		if ($v[6]==2 || $v[6]==3) {
			$fec_rcc = date('d/m/Y', strtotime($v[3]));
			if ($v[6]==2) {
				$com_dp = $v[12].' '.$v[10].'-'.$v[11].', '.$v[7];
			}
			else if ($v[6]==3) {
				$com_dp = "ANULADO, $v[7]";
			}

			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c1, 0, utf8_encode(mb_substr(strtoupper($v[1]), 0, 23)), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, 'RCC '.$v[2], '', 0, 'L');
			$pdf->Cell($a_c3, 0, $fec_rcc, '', 0, 'L');
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c4, 0, utf8_encode(mb_substr($v[4], 0, 30)), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c5, 0, $v[5], '', 0, 'R');
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c6, 0, utf8_encode(mb_substr($com_dp, 0, 32)), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Ln();
			$tot_dpl += $v[5];
		}
	}
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'BI', $t_me);
	$pdf->Cell(74, 0, "", '', 0, 'L');
	$pdf->Cell(80, 0, "Total Recibos Pendientes Liquidados", '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell(0, 0, $mon_simb." ".conComas($tot_dpl), '', 0, 'R');
	$pdf->Ln();
	$pdf->Line(30, $pdf->GetY(), 200, $pdf->GetY(), $linestyle);
	$pdf->Ln();

	// Seccion de firmas
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();


	if ($hist == 1 || ($hist == 0 && $est_id <= 2)) {
		$regis_msg = '';
		$regis_fir = 'T';
		$aprob_msg = 'SIN APROBAR';
	}
	else {
		$regis_msg = 'REGISTRADO EL '.date('d/m/Y h:i:s A', strtotime($ccl_cie_fec));
		$regis_fir = '';
		$aprob_msg = 'APROBADO ELECTRONICAMENTE EL '.date('d/m/Y h:i:s A', strtotime($ccl_aprob_fec));
	}

	$pdf->SetFont('helvetica', 'B', 7);
	$pdf->Cell(80, 0, $regis_msg, '', 0, 'C');
	$pdf->Cell(10, 0, '', '', 0, 'L');
	$pdf->Cell(80, 0, $aprob_msg, '', 0, 'C');
	$pdf->Ln();
	$pdf->Cell(80, 0, utf8_encode($nombres_1), $regis_fir, 0, 'C');
	$pdf->Cell(10, 0, '', '', 0, 'L');
	$pdf->Cell(80, 0, utf8_encode($nombres_2), '', 0, 'C');
	$pdf->Ln();
	$pdf->Cell(80, 0, utf8_encode('Encargado'), '', 0, 'C');
	$pdf->Cell(10, 0, '', '', 0, 'L');
	$pdf->Cell(80, 0, utf8_encode('Responsable'), '', 0, 'C');
	$pdf->Ln();


	// Agrega la planilla de movilidad solo si existe
	$pla_id = null;
	if(!is_null($pla_id)) {
		// add a page
		$pdf->AddPage();

		$pdf->SetFont('helvetica', 'B', 12);
		$pdf->Write($h=0, 'PLANILLA DE GASTOS DE MOVILIDAD', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
		//$pdf->Ln();
		$pdf->SetFont('helvetica', 'I', 7);
		$pdf->Write($h=0, utf8_encode('(inciso a1 del articulo 37 de la Ley del Impuesto a la Renta)'), $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
		$pdf->Ln();

		list($fec_ini, $fec_fin) = getPeriodoPlaMov($pla_id);
		$periodo = date('n', strtotime($fec_ini));
		$fec_ini = date('d/m/Y', strtotime($fec_ini));
		$fec_fin = date('d/m/Y', strtotime($fec_fin));

		$a_c1 = 15; // ancho primera columna
		$a_c2 = 69; // ancho segunda columna
		$a_c3 = 24; // ancho tercera columna
		$t_me = 7; // tamano de letra mensaje

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "Trabajador:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, utf8_encode($ear_tra_nombres), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, utf8_encode("No."), '', 0, 'L');
		$pdf->Cell(30, 0, $pla_numero, 'LTRB', 0, 'C', true);
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "DNI:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, $ear_tra_dni, '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, utf8_encode("Periodo:"), '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell(0, 0, nombreMes($periodo), '', 0, 'L');
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "Cargo:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, utf8_encode($ear_tra_cargo), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, "Desde - Hasta:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell(0, 0, $fec_ini.' al '.$fec_fin, '', 0, 'L');
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "Sucursal:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, utf8_encode($ear_tra_sucursal), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, utf8_encode("Fecha de emision:"), '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell(0, 0, $fec_pla, '', 0, 'L');
		$pdf->Ln();

		$pdf->Ln();

		// Resaltados en color amarillo
		$pdf->SetFillColor(190, 190, 190); // Gray

		$a_c1 = 60; // ancho primera columna
		$a_c2 = 16; // ancho segunda columna
		$a_c3 = 38; // ancho tercera columna
		$a_c4 = 38; // ancho cuarta columna
		$a_c5 = 18; // ancho quinta columna
		$t_me = 7; // tamano de letra mensaje

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "", 'LTR', 0, 'L', true);
		$pdf->Cell($a_c2, 0, "", 'LTR', 0, 'L', true);
		$pdf->Cell($a_c3+$a_c4+$a_c5, 0, "Desplazamiento", 'LTRB', 0, 'C', true);
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "MOTIVO", 'LRB', 0, 'C', true);
		$pdf->Cell($a_c2, 0, "FECHA", 'LRB', 0, 'C', true);
		$pdf->Cell($a_c3, 0, "Salida", 'LTRB', 0, 'C', true);
		$pdf->Cell($a_c4, 0, "Destino", 'LTRB', 0, 'C', true);
		$pdf->Cell($a_c5, 0, "Monto", 'LTRB', 0, 'C', true);
		$pdf->Ln();

		$subtotalPla = 0;

		foreach ($arrPlaMovDet as $k => $v) {
			$fec_mov = date('d/m/Y', strtotime($v[1]));

			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c1, 0, utf8_encode(mb_substr($v[0], 0, 39)), 'LTRB', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, $fec_mov, 'LTRB', 0, 'C');
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c3, 0, utf8_encode(mb_substr($v[2], 0, 24)), 'LTRB', 0, 'L');
			$pdf->Cell($a_c4, 0, utf8_encode(mb_substr($v[3], 0, 24)), 'LTRB', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c5, 0, number_format($v[4], 2, '.', ','), 'LTRB', 0, 'R');
			$pdf->Ln();

			$subtotalPla += $v[4];
		}

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1+$a_c2+$a_c3, 0, "", '', 0, 'L');
		$pdf->Cell($a_c4, 0, "Total S/.", '', 0, 'R');
		$pdf->Cell($a_c5, 0, number_format($subtotalPla, 2, '.', ','), 'TB', 0, 'R');
		$pdf->Ln();

		// Seccion de firmas
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', 7);
		$pdf->Cell(80, 0, utf8_encode($ear_tra_nombres), 'T', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
		$pdf->Cell(80, 0, utf8_encode($nombres), 'T', 0, 'C');
		$pdf->Ln();
		$pdf->Cell(80, 0, 'DNI '.$ear_tra_dni, '', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
		$pdf->Cell(80, 0, utf8_encode($cargo_desc), '', 0, 'C');
		$pdf->Ln();
		$pdf->Cell(80, 0, '', '', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
		$pdf->Cell(80, 0, utf8_encode($area_desc), '', 0, 'C');
	}


	// Agrega los recibos de gastos si existen
	$rgs = 0;
	foreach ($arrLiqDet as $k => $v) {
		if ($v[26]=='RGS' && strlen($v[3])==8) {
			// Obtiene datos del trabajador a traves de su dni
			list($usu_dni_rgs, $usu_nombres_rgs, $cargo_id_rgs, $fecha_ing_rgs,
				$usu_cargo_desc_rgs, $area_id_rgs, $area_desc_rgs, $idccosto_rgs, $banco_rgs, $ctacte_rgs, $usu_sucursal_rgs) = getInfoTrabajador($v[3]);

			// add a page
			list($mon_nom, $mon_iso, $mon_simb, $mon_img) = getNomMoneda($v[10]);

			if ($rgs % 2 == 0) {
				$pdf->AddPage();
			}
			else {
				$linestyle = array('T' => array('dash' => 1));
				$pdf->Cell(0, 0, '',  $linestyle, 0, 'C');
				$pdf->Ln();
				$pdf->Ln();
				$linestyle = array('dash' => 0);
				$pdf->SetLineStyle($linestyle);
			}

			$pdf->SetFont('helvetica', 'B', 12);
			$pdf->Write($h=0, 'CONSTANCIA DE GASTO', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
			$pdf->Ln();

			$a_c1 = 28; // ancho primera columna
			$a_c2 = 98; // ancho segunda columna
			$a_c3 = 12; // ancho tercera columna
			$t_me = 10; // tamano de letra mensaje

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1, 0, 'Caja Chica:', '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, utf8_encode($cch_nombre), '', 0, 'L');
			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c3, 0, utf8_encode("No."), '', 0, 'L');
			$pdf->Cell(30, 0, str_pad($v[7], 5, "0", STR_PAD_LEFT).'-'.str_pad($v[8], 7, "0", STR_PAD_LEFT), 'LTRB', 0, 'C', true);
			$pdf->Ln();

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1, 0, '', '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, '', '', 0, 'L');
			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c3, 0, 'Por:', '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell(30, 0, $mon_simb.' '.conComas($v[12]+$v[13]), 'LTRB', 0, 'R');
			$pdf->Ln();

			$pdf->Ln();

			$t_me = 12; // tamano de letra mensaje

			$parrafo1 = 'Yo, '.$usu_nombres_rgs.' con DNI '.$usu_dni_rgs.' declaro haber gastado la suma de '.MontoMonetarioEnLetras($v[12]+$v[13]).' '.$mon_nom.' por concepto de '.$v[9].".\n\n";
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->MultiCell(0, 0, utf8_encode($parrafo1), 'LTR', 'J');

			$fec_doc = date('d/m/Y', strtotime($v[6]));
			$pzas = explode("/", $fec_doc);
			$parrafo2 = $usu_sucursal_rgs.', '.$pzas[0].' de '.nombreMes($pzas[1]).' del '.$pzas[2];
			$pdf->Cell(0, 0, utf8_encode($parrafo2), 'LRB', 0, 'R');
			$pdf->Ln();

			// Seccion de firmas
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();

			$pdf->SetFont('helvetica', 'B', 7);
			$pdf->Cell(80, 0, '', '', 0, 'C');
			$pdf->Cell(10, 0, '', '', 0, 'L');
			$pdf->Cell(80, 0, utf8_encode($usu_nombres_rgs), 'T', 0, 'C');
			$pdf->Ln();
			$pdf->Cell(80, 0, '', '', 0, 'C');
			$pdf->Cell(10, 0, '', '', 0, 'L');
			$pdf->Cell(80, 0, 'DNI '.$usu_dni_rgs, '', 0, 'C');

			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();

			$rgs++;
		}
	}


	// ---------------------------------------------------------

	//Close and output PDF document
	// S: devuelve string, I: inline display, D: download pdf
	//$attach = $pdf->Output('example_003.pdf', 'S');
	//$attach = $pdf->Output('example_003.pdf', 'I');

	return $pdf->Output($doc_numero.".pdf", $output);
}

function getPlanillaMov($id, $output) {
	$id = abs((int) filter_var($id, FILTER_SANITIZE_NUMBER_INT));

	list($pla_numero, $est_id, $pla_reg_fec, $ear_numero, $tope_maximo, $usu_id, $ear_id,
		$est_nom, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec,
		$pla_exc, $pla_com1, $pla_com2, $pla_com3,
		$pla_tipo, $ccl_id, $cch_id) = getPlanillaMovilidadInfo($id);

	list($cch_id, $cch_nombre, $suc_nombre, $mon_nom, $mon_iso, $mon_img, $cch_monto,
		$cch_abrv, $cch_gti, $cch_dg_json, $cch_cta_bco, $cch_act,
		$suc_id, $mon_id) = getCajasChicasInfo($cch_id);

	// list($ccl_id, $cch_nombre, $ccl_numero, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ccl_monto_ini, $ccl_gti, $ccl_dg_json, $ccl_cta_bco,
		// $ccl_ape_fec, $ape_usu_nombre, $ccl_cie_fec, $cie_usu_nombre,
		// $ccl_aprob_fec, $aprob_usu_nombre, $ccl_act_fec, $act_usu_nombre,
		// $ccl_monto_usado, $est_id, $est_nom, $suc_nombre,
		// $ccl_ret, $ccl_ret_no, $ccl_det, $ccl_det_no, $ccl_gast_asum, $ccl_pend, $cch_id, $liq_mon_id,
		// $ccl_ape_usu, $ccl_cie_usu, $ccl_aprob_usu, $ccl_act_usu) = getLoteCajaChicaInfo($id);
	$pla_id = $id;
	$fec_pla = date('d/m/Y', strtotime($pla_env_fec));

	// list($ear_tra_nombres, $ear_numero, $zona_nom, $mon_nom, $mon_iso, $mon_simb, $mon_img, $ear_monto, $est_nom, $ear_sol_fec, $ear_liq_fec,
		// $ear_sol_motivo, $ear_tra_dni, $ear_tra_cargo, $ear_tra_area, $ear_tra_sucursal, $ear_tra_cta,
		// $usu_act, $ear_act_fec, $ear_act_motivo, $liq_mon_id, $zona_id, $est_id, $usu_id,
		// $ear_liq_mon, $ear_liq_ret, $ear_liq_ret_no, $ear_liq_det, $ear_liq_det_no, $ear_liq_dcto,
		// $ear_liq_gast_asum, $pla_id) = getSolicitudInfo($id);
	// $ear_tra_dni = "DNI_REPLACE";
	// $ear_tra_nombres = "NOM_REPLACE";

	//$ear_fec_env = getFechaEnvioLiq($id);

	// if ($est_id>=51 && $est_id<=53) {
		// $arrLiqDet = array();
	// }
	// else {
		// $arrLiqDet = getLiqDetalle($id);
	// }
	//$arrLiqDet = getLoteDetalle($ccl_id);

	$doc_numero = "PLA_".str_replace("/", "_", $pla_numero);

	//list($fec_ini, $fec_fin) = getPeriodoLiq($id);
	// $fec_ini = date('d/m/Y', strtotime($ccl_ape_fec));
	// $fec_fin = date('d/m/Y', strtotime($ccl_cie_fec));

	list($usu_dni, $usu_nombres, $cargo_id, $fecha_ing,
		$usu_cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $usu_sucursal) = getInfoTrabajador(getCodigoGeneral(getUsuAd($usu_id)));
	list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
	list($dni, $jefe_nombres, $cargo_id, $fecha_ing,
		$jefe_cargo_desc, $area_id, $jefe_area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador(getCodigoGeneral(getUsuAd($usu_id_jefe)));
	// list($dni_1, $nombres_1, $cargo_id_1, $fecha_ing_1,
		// $cargo_desc_1, $area_id_1, $area_desc_1, $idccosto_1, $banco_1, $ctacte_1, $sucursal_1) = getInfoTrabajador(getCodigoGeneral(getUsuAd($ccl_cie_usu)));
	// list($dni_2, $nombres_2, $cargo_id_2, $fecha_ing_2,
		// $cargo_desc_2, $area_id_2, $area_desc_2, $idccosto_2, $banco_2, $ctacte_2, $sucursal_2) = getInfoTrabajador(getCodigoGeneral(getUsuAd($ccl_aprob_usu)));

	// if(!is_null($pla_id)) {
		// list($pla_numero, $est_id_2, $pla_reg_fec, $ear_numero_2, $tope_maximo, $usu_id_2, $ear_id, $est_nom_2, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec) = getPlanillaMovilidadInfo($pla_id);
		// $arrPlaMovDet = getPlanillaMovDetalle($pla_id);
	// }
	$arrPlaMovDet = getPlanillaMovDetalle($pla_id);

	// Seccion que genera el PDF
	////////////////////////////

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->nro_serie = $doc_numero; // Define el numero de serie para el codigo de barras
	$pdf->fec_gen = date('d/m/Y h:i:s A');

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Administracion  ABC MULTISERVICIOS GENERALES S.A.C.');
	$pdf->SetTitle('PLANILLA DE MOVILIDAD');
	$pdf->SetSubject('De '.$usu_nombres);

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	//set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetMargins(30, 25, 10, true);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// ---------------------------------------------------------

	// Resaltados en color amarillo
	$pdf->SetFillColor(255, 255, 0); // Yellow

	// Agrega la planilla de movilidad
	if(!is_null($pla_id)) {
		// add a page
		$pdf->AddPage();

		$pdf->SetFont('helvetica', 'B', 12);
		$pdf->Write($h=0, 'PLANILLA DE GASTOS DE MOVILIDAD', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
		//$pdf->Ln();
		$pdf->SetFont('helvetica', 'I', 7);
		$pdf->Write($h=0, utf8_encode('(inciso a1 del articulo 37 de la Ley del Impuesto a la Renta)'), $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
		$pdf->Ln();

		list($fec_ini, $fec_fin) = getPeriodoPlaMov($pla_id);
		$periodo = date('n', strtotime($fec_ini));
		$fec_ini = date('d/m/Y', strtotime($fec_ini));
		$fec_fin = date('d/m/Y', strtotime($fec_fin));

		$a_c1 = 15; // ancho primera columna
		$a_c2 = 69; // ancho segunda columna
		$a_c3 = 24; // ancho tercera columna
		$t_me = 7; // tamano de letra mensaje

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "Trabajador:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, utf8_encode($usu_nombres), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, utf8_encode("No."), '', 0, 'L');
		$pdf->Cell(30, 0, $pla_numero, 'LTRB', 0, 'C', true);
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "DNI:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, $usu_dni, '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, utf8_encode("Per�odo:"), '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell(0, 0, nombreMes($periodo), '', 0, 'L');
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "Cargo:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, utf8_encode($usu_cargo_desc), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, "Desde - Hasta:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell(0, 0, $fec_ini.' al '.$fec_fin, '', 0, 'L');
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "Sucursal:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, utf8_encode($usu_sucursal), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, utf8_encode("Fecha de emisi�n:"), '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell(0, 0, $fec_pla, '', 0, 'L');
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "Caja Chica:", '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, utf8_encode($cch_nombre), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, '', '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell(0, 0, '', '', 0, 'L');
		$pdf->Ln();

		$pdf->Ln();

		// Resaltados en color amarillo
		$pdf->SetFillColor(190, 190, 190); // Gray

		$a_c1 = 60; // ancho primera columna
		$a_c2 = 16; // ancho segunda columna
		$a_c3 = 38; // ancho tercera columna
		$a_c4 = 38; // ancho cuarta columna
		$a_c5 = 18; // ancho quinta columna
		$t_me = 7; // tamano de letra mensaje

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "", 'LTR', 0, 'L', true);
		$pdf->Cell($a_c2, 0, "", 'LTR', 0, 'L', true);
		$pdf->Cell($a_c3+$a_c4+$a_c5, 0, "Desplazamiento", 'LTRB', 0, 'C', true);
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, "MOTIVO", 'LRB', 0, 'C', true);
		$pdf->Cell($a_c2, 0, "FECHA", 'LRB', 0, 'C', true);
		$pdf->Cell($a_c3, 0, "Salida", 'LTRB', 0, 'C', true);
		$pdf->Cell($a_c4, 0, "Destino", 'LTRB', 0, 'C', true);
		$pdf->Cell($a_c5, 0, "Monto", 'LTRB', 0, 'C', true);
		$pdf->Ln();

		$subtotalPla = 0;

		foreach ($arrPlaMovDet as $k => $v) {
			$fec_mov = date('d/m/Y', strtotime($v[1]));

			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c1, 0, utf8_encode(mb_substr($v[0], 0, 39)), 'LTRB', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, $fec_mov, 'LTRB', 0, 'C');
			$pdf->SetFont('courier', '', $t_me);
			$pdf->Cell($a_c3, 0, utf8_encode(mb_substr($v[2], 0, 24)), 'LTRB', 0, 'L');
			$pdf->Cell($a_c4, 0, utf8_encode(mb_substr($v[3], 0, 24)), 'LTRB', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c5, 0, number_format($v[4], 2, '.', ','), 'LTRB', 0, 'R');
			$pdf->Ln();

			$subtotalPla += $v[4];
		}

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1+$a_c2+$a_c3, 0, "", '', 0, 'L');
		$pdf->Cell($a_c4, 0, "Total S/.", '', 0, 'R');
		$pdf->Cell($a_c5, 0, number_format($subtotalPla, 2, '.', ','), 'TB', 0, 'R');
		$pdf->Ln();

		// Seccion de firmas
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();


		if (!is_null($pla_env_fec)) {
			$aprob_msg = 'APROBADO ELECTRONICAMENTE EL '.date('d/m/Y h:i:s A', strtotime($pla_env_fec));
		}
		else {
			$aprob_msg = 'SIN APROBAR';
		}

		$pdf->SetFont('helvetica', 'B', 7);
		$pdf->Cell(80, 0, '', '', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
		$pdf->Cell(80, 0, $aprob_msg, '', 0, 'C');
		$pdf->Ln();
		$pdf->Cell(80, 0, utf8_encode($usu_nombres), 'T', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
		$pdf->Cell(80, 0, utf8_encode($jefe_nombres), '', 0, 'C');
		$pdf->Ln();
		$pdf->Cell(80, 0, 'DNI '.$usu_dni, '', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
		$pdf->Cell(80, 0, utf8_encode($jefe_cargo_desc), '', 0, 'C');
		$pdf->Ln();
		$pdf->Cell(80, 0, '', '', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
		$pdf->Cell(80, 0, utf8_encode($jefe_area_desc), '', 0, 'C');
	}

	// ---------------------------------------------------------

	//Close and output PDF document
	// S: devuelve string, I: inline display, D: download pdf
	//$attach = $pdf->Output('example_003.pdf', 'S');
	//$attach = $pdf->Output('example_003.pdf', 'I');

	return $pdf->Output($doc_numero.".pdf", $output);
}

function getDocPendPDF($id, $output) {
	$id = abs((int) filter_var($id, FILTER_SANITIZE_NUMBER_INT));

	list($usu_id, $dp_numero, $cldp_ent_fec, $cldp_conc, $cldp_monto, $est_id, $cldp_com1, $cch_nombre, $cldp_reg_fec, $mon_nom, $mon_simb) = getDocPendInfo($id);

	$doc_numero = "DOP_".str_replace("/", "_", $dp_numero);

	//list($fec_ini, $fec_fin) = getPeriodoLiq($id);
	// $fec_ini = date('d/m/Y', strtotime($ccl_ape_fec));
	// $fec_fin = date('d/m/Y', strtotime($ccl_cie_fec));

	// list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
	list($dni, $nombres, $cargo_id, $fecha_ing,
		$cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador(getCodigoGeneral(getUsuAd($usu_id)));
	// list($dni_1, $nombres_1, $cargo_id_1, $fecha_ing_1,
		// $cargo_desc_1, $area_id_1, $area_desc_1, $idccosto_1, $banco_1, $ctacte_1, $sucursal_1) = getInfoTrabajador(getCodigoGeneral(getUsuAd($ccl_cie_usu)));
	// list($dni_2, $nombres_2, $cargo_id_2, $fecha_ing_2,
		// $cargo_desc_2, $area_id_2, $area_desc_2, $idccosto_2, $banco_2, $ctacte_2, $sucursal_2) = getInfoTrabajador(getCodigoGeneral(getUsuAd($ccl_aprob_usu)));

	// if(!is_null($pla_id)) {
		// list($pla_numero, $est_id_2, $pla_reg_fec, $ear_numero_2, $tope_maximo, $usu_id_2, $ear_id, $est_nom_2, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec) = getPlanillaMovilidadInfo($pla_id);
		// $arrPlaMovDet = getPlanillaMovDetalle($pla_id);
	// }

	// Seccion que genera el PDF
	////////////////////////////

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->nro_serie = $doc_numero; // Define el numero de serie para el codigo de barras
	$pdf->fec_gen = date('d/m/Y h:i:s A');

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Administracion  ABC MULTISERVICIOS GENERALES S.A.C.');
	$pdf->SetTitle('RECIBO PENDIENTE POR LIQUIDAR DE CAJA CHICA');
	$pdf->SetSubject('De '.$cch_nombre);

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	//set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetMargins(30, 25, 10, true);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// ---------------------------------------------------------

	// Resaltados en color amarillo
	$pdf->SetFillColor(255, 255, 0); // Yellow

	// Agrega los documentos pendientes

	// add a page
	//list($mon_nom, $mon_iso, $mon_simb, $mon_img) = getNomMoneda($v[10]);

	$pdf->AddPage();
	// if ($rgs % 2 == 0) {
		// $pdf->AddPage();
	// }
	// else {
		// $linestyle = array('T' => array('dash' => 1));
		// $pdf->Cell(0, 0, '',  $linestyle, 0, 'C');
		// $pdf->Ln();
		// $pdf->Ln();
		// $linestyle = array('dash' => 0);
		// $pdf->SetLineStyle($linestyle);
	// }

	$pdf->SetFont('helvetica', 'B', 12);
	$pdf->Write($h=0, 'RECIBO PENDIENTE POR LIQUIDAR', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
	$pdf->Ln();

	$a_c1 = 28; // ancho primera columna
	$a_c2 = 98; // ancho segunda columna
	$a_c3 = 12; // ancho tercera columna
	$t_me = 10; // tamano de letra mensaje

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, 'Caja Chica:', '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, utf8_encode($cch_nombre), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, utf8_encode("N�"), '', 0, 'L');
	$pdf->Cell(30, 0, $dp_numero, 'LTRB', 0, 'C', true);
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c1, 0, 'Fecha Registro:', '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell($a_c2, 0, date('d/m/Y h:i:s A', strtotime($cldp_reg_fec)), '', 0, 'L');
	$pdf->SetFont('helvetica', 'B', $t_me);
	$pdf->Cell($a_c3, 0, 'Por:', '', 0, 'L');
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->Cell(30, 0, $mon_simb.' '.conComas($cldp_monto), 'LTRB', 0, 'R');
	$pdf->Ln();

	$pdf->Ln();

	$t_me = 12; // tamano de letra mensaje

	$parrafo1 = 'Yo, '.$nombres.' con DNI '.$dni.' declaro haber recibido la suma de '.MontoMonetarioEnLetras($cldp_monto).' '.$mon_nom.' por concepto de '.$cldp_conc.".\n\n";
	$pdf->SetFont('helvetica', '', $t_me);
	$pdf->MultiCell(0, 0, utf8_encode($parrafo1), 'LTR', 'J');

	$fec_doc = date('d/m/Y', strtotime($cldp_ent_fec));
	$pzas = explode("/", $fec_doc);
	$parrafo2 = $sucursal.', '.$pzas[0].' de '.nombreMes($pzas[1]).' del '.$pzas[2];
	$pdf->Cell(0, 0, utf8_encode($parrafo2), 'LRB', 0, 'R');
	$pdf->Ln();

	// Seccion de firmas
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();

	$pdf->SetFont('helvetica', 'B', 7);
	$pdf->Cell(80, 0, '', '', 0, 'C');
	$pdf->Cell(10, 0, '', '', 0, 'L');
	$pdf->Cell(80, 0, utf8_encode($nombres), 'T', 0, 'C');
	$pdf->Ln();
	$pdf->Cell(80, 0, '', '', 0, 'C');
	$pdf->Cell(10, 0, '', '', 0, 'L');
	$pdf->Cell(80, 0, 'DNI '.$dni, '', 0, 'C');

	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();


	// ---------------------------------------------------------

	//Close and output PDF document
	// S: devuelve string, I: inline display, D: download pdf
	//$attach = $pdf->Output('example_003.pdf', 'S');
	//$attach = $pdf->Output('example_003.pdf', 'I');

	return $pdf->Output($doc_numero.".pdf", $output);
}

function getPlanillaMovAll($id, $output) {
	// $id es el id del lote de caja chica
	$id = abs((int) filter_var($id, FILTER_SANITIZE_NUMBER_INT));

	// Obtener el arreglo de planillas que pertenezcan a dicha caja chica (verificar estado correcto de la planilla de movilidad: est_id = 15)
	$arrPlaId = getPlanillasId($id);

	$arrLoteInfo = getLoteCajaChicaInfo($id);
	$cch_nombre = $arrLoteInfo[1];
	$ccl_numero = $arrLoteInfo[2];

	$file_name = "PLA_CCH_".str_replace("/", "_", $ccl_numero);

	// Seccion que genera el PDF
	////////////////////////////

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->fec_gen = date('d/m/Y h:i:s A');

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Administracion  ABC MULTISERVICIOS GENERALES S.A.C.');
	$pdf->SetTitle('PLANILLAS DE MOVILIDAD DE CAJA CHICA');
	$pdf->SetSubject('De '.$cch_nombre);

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	//set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetMargins(30, 25, 10, true);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// ---------------------------------------------------------

	foreach ($arrPlaId as $v) {
		list($pla_numero, $est_id, $pla_reg_fec, $ear_numero, $tope_maximo, $usu_id, $ear_id,
			$est_nom, $pla_monto, $pla_gti, $pla_dg_json, $pla_env_fec,
			$pla_exc, $pla_com1, $pla_com2, $pla_com3,
			$pla_tipo, $ccl_id, $cch_id) = getPlanillaMovilidadInfo($v);

		$pla_id = $v;
		$fec_pla = date('d/m/Y', strtotime($pla_env_fec));

		$doc_numero = "PLA_".str_replace("/", "_", $pla_numero);

		list($usu_dni, $usu_nombres, $cargo_id, $fecha_ing,
			$usu_cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $usu_sucursal) = getInfoTrabajador(getCodigoGeneral(getUsuAd($usu_id)));
		list($usu_id_jefe, $usu_id_gerente) = getUsuJefeYGerenteDirecto($usu_id);
		list($dni, $jefe_nombres, $cargo_id, $fecha_ing,
			$jefe_cargo_desc, $area_id, $jefe_area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador(getCodigoGeneral(getUsuAd($usu_id_jefe)));
		$arrPlaMovDet = getPlanillaMovDetalle($pla_id);

		// Resaltados en color amarillo
		$pdf->SetFillColor(255, 255, 0); // Yellow

		// Agrega la planilla de movilidad
		if(!is_null($pla_id)) {
			// add a page
			$pdf->AddPage();

			$pdf->nro_serie = $doc_numero; // Define el numero de serie para el codigo de barras

			$pdf->SetFont('helvetica', 'B', 12);
			$pdf->Write($h=0, 'PLANILLA DE GASTOS DE MOVILIDAD', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
			//$pdf->Ln();
			$pdf->SetFont('helvetica', 'I', 7);
			$pdf->Write($h=0, utf8_encode('(inciso a1 del articulo 37� de la Ley del Impuesto a la Renta)'), $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
			$pdf->Ln();

			list($fec_ini, $fec_fin) = getPeriodoPlaMov($pla_id);
			$periodo = date('n', strtotime($fec_ini));
			$fec_ini = date('d/m/Y', strtotime($fec_ini));
			$fec_fin = date('d/m/Y', strtotime($fec_fin));

			$a_c1 = 15; // ancho primera columna
			$a_c2 = 69; // ancho segunda columna
			$a_c3 = 24; // ancho tercera columna
			$t_me = 7; // tamano de letra mensaje

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1, 0, "Trabajador:", '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, utf8_encode($usu_nombres), '', 0, 'L');
			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c3, 0, utf8_encode("N�"), '', 0, 'L');
			$pdf->Cell(30, 0, $pla_numero, 'LTRB', 0, 'C', true);
			$pdf->Ln();

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1, 0, "DNI:", '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, $usu_dni, '', 0, 'L');
			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c3, 0, utf8_encode("Per�odo:"), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell(0, 0, nombreMes($periodo), '', 0, 'L');
			$pdf->Ln();

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1, 0, "Cargo:", '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, utf8_encode($usu_cargo_desc), '', 0, 'L');
			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c3, 0, "Desde - Hasta:", '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell(0, 0, $fec_ini.' al '.$fec_fin, '', 0, 'L');
			$pdf->Ln();

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1, 0, "Sucursal:", '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, utf8_encode($usu_sucursal), '', 0, 'L');
			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c3, 0, utf8_encode("Fecha de emisi�n:"), '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell(0, 0, $fec_pla, '', 0, 'L');
			$pdf->Ln();

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1, 0, "Caja Chica:", '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell($a_c2, 0, utf8_encode($cch_nombre), '', 0, 'L');
			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c3, 0, '', '', 0, 'L');
			$pdf->SetFont('helvetica', '', $t_me);
			$pdf->Cell(0, 0, '', '', 0, 'L');
			$pdf->Ln();

			$pdf->Ln();

			// Resaltados en color amarillo
			$pdf->SetFillColor(190, 190, 190); // Gray

			$a_c1 = 60; // ancho primera columna
			$a_c2 = 16; // ancho segunda columna
			$a_c3 = 38; // ancho tercera columna
			$a_c4 = 38; // ancho cuarta columna
			$a_c5 = 18; // ancho quinta columna
			$t_me = 7; // tamano de letra mensaje

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1, 0, "", 'LTR', 0, 'L', true);
			$pdf->Cell($a_c2, 0, "", 'LTR', 0, 'L', true);
			$pdf->Cell($a_c3+$a_c4+$a_c5, 0, "Desplazamiento", 'LTRB', 0, 'C', true);
			$pdf->Ln();

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1, 0, "MOTIVO", 'LRB', 0, 'C', true);
			$pdf->Cell($a_c2, 0, "FECHA", 'LRB', 0, 'C', true);
			$pdf->Cell($a_c3, 0, "Salida", 'LTRB', 0, 'C', true);
			$pdf->Cell($a_c4, 0, "Destino", 'LTRB', 0, 'C', true);
			$pdf->Cell($a_c5, 0, "Monto", 'LTRB', 0, 'C', true);
			$pdf->Ln();

			$subtotalPla = 0;

			foreach ($arrPlaMovDet as $k => $v) {
				$fec_mov = date('d/m/Y', strtotime($v[1]));

				$pdf->SetFont('courier', '', $t_me);
				$pdf->Cell($a_c1, 0, utf8_encode(mb_substr($v[0], 0, 39)), 'LTRB', 0, 'L');
				$pdf->SetFont('helvetica', '', $t_me);
				$pdf->Cell($a_c2, 0, $fec_mov, 'LTRB', 0, 'C');
				$pdf->SetFont('courier', '', $t_me);
				$pdf->Cell($a_c3, 0, utf8_encode(mb_substr($v[2], 0, 24)), 'LTRB', 0, 'L');
				$pdf->Cell($a_c4, 0, utf8_encode(mb_substr($v[3], 0, 24)), 'LTRB', 0, 'L');
				$pdf->SetFont('helvetica', '', $t_me);
				$pdf->Cell($a_c5, 0, number_format($v[4], 2, '.', ','), 'LTRB', 0, 'R');
				$pdf->Ln();

				$subtotalPla += $v[4];
			}

			$pdf->SetFont('helvetica', 'B', $t_me);
			$pdf->Cell($a_c1+$a_c2+$a_c3, 0, "", '', 0, 'L');
			$pdf->Cell($a_c4, 0, "Total S/.", '', 0, 'R');
			$pdf->Cell($a_c5, 0, number_format($subtotalPla, 2, '.', ','), 'TB', 0, 'R');
			$pdf->Ln();

			// Seccion de firmas
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();


			if (!is_null($pla_env_fec)) {
				$aprob_msg = 'APROBADO ELECTRONICAMENTE EL '.date('d/m/Y h:i:s A', strtotime($pla_env_fec));
			}
			else {
				$aprob_msg = 'SIN APROBAR';
			}

			$pdf->SetFont('helvetica', 'B', 7);
			$pdf->Cell(80, 0, '', '', 0, 'C');
			$pdf->Cell(10, 0, '', '', 0, 'L');
			$pdf->Cell(80, 0, $aprob_msg, '', 0, 'C');
			$pdf->Ln();
			$pdf->Cell(80, 0, utf8_encode($usu_nombres), 'T', 0, 'C');
			$pdf->Cell(10, 0, '', '', 0, 'L');
			$pdf->Cell(80, 0, utf8_encode($jefe_nombres), '', 0, 'C');
			$pdf->Ln();
			$pdf->Cell(80, 0, 'DNI '.$usu_dni, '', 0, 'C');
			$pdf->Cell(10, 0, '', '', 0, 'L');
			$pdf->Cell(80, 0, utf8_encode($jefe_cargo_desc), '', 0, 'C');
			$pdf->Ln();
			$pdf->Cell(80, 0, '', '', 0, 'C');
			$pdf->Cell(10, 0, '', '', 0, 'L');
			$pdf->Cell(80, 0, utf8_encode($jefe_area_desc), '', 0, 'C');
		}

	}

	// ---------------------------------------------------------

	//Close and output PDF document
	// S: devuelve string, I: inline display, D: download pdf
	//$attach = $pdf->Output('example_003.pdf', 'S');
	//$attach = $pdf->Output('example_003.pdf', 'I');

	return $pdf->Output($file_name.".pdf", $output);
}

function getDocPendPDFAll($id, $output) {
	// $id es el id del lote de caja chica
	$id = abs((int) filter_var($id, FILTER_SANITIZE_NUMBER_INT));

	// Obtener el arreglo de documentos pendientes que pertenezcan a dicha caja chica
	$arrDPId = getDocPendId($id);

	$arrLoteInfo = getLoteCajaChicaInfo($id);
	$cch_nombre = $arrLoteInfo[1];
	$ccl_numero = $arrLoteInfo[2];

	$file_name = "DOP_CCH_".str_replace("/", "_", $ccl_numero);

	// Seccion que genera el PDF
	////////////////////////////

	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->fec_gen = date('d/m/Y h:i:s A');

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Administracion  ABC MULTISERVICIOS GENERALES S.A.C.');
	$pdf->SetTitle('RECIBOOS PENDIENTES POR LIQUIDAR DE CAJA CHICA');
	$pdf->SetSubject('De '.$cch_nombre);

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	//set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetMargins(30, 25, 10, true);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// ---------------------------------------------------------

	foreach ($arrDPId as $v) {
		list($usu_id, $dp_numero, $cldp_ent_fec, $cldp_conc, $cldp_monto, $est_id, $cldp_com1, $cch_nombre, $cldp_reg_fec, $mon_nom, $mon_simb) = getDocPendInfo($v);

		$doc_numero = "DOP_".str_replace("/", "_", $dp_numero);

		list($dni, $nombres, $cargo_id, $fecha_ing,
			$cargo_desc, $area_id, $area_desc, $idccosto, $banco, $ctacte, $sucursal) = getInfoTrabajador(getCodigoGeneral(getUsuAd($usu_id)));

		// Resaltados en color amarillo
		$pdf->SetFillColor(255, 255, 0); // Yellow

		// Agrega los documentos pendientes

		// add a page
		$pdf->AddPage();

		$pdf->nro_serie = $doc_numero; // Define el numero de serie para el codigo de barras

		$pdf->SetFont('helvetica', 'B', 12);
		$pdf->Write($h=0, 'RECIBO PENDIENTE POR LIQUIDAR', $link='', $fill=0, $align='C', $ln=true, $stretch=0, $firstline=false, $firstblock=false, $maxh=0);
		$pdf->Ln();

		$a_c1 = 28; // ancho primera columna
		$a_c2 = 98; // ancho segunda columna
		$a_c3 = 12; // ancho tercera columna
		$t_me = 10; // tamano de letra mensaje

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, 'Caja Chica:', '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, utf8_encode($cch_nombre), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, utf8_encode("N�"), '', 0, 'L');
		$pdf->Cell(30, 0, $dp_numero, 'LTRB', 0, 'C', true);
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c1, 0, 'Fecha Registro:', '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell($a_c2, 0, date('d/m/Y h:i:s A', strtotime($cldp_reg_fec)), '', 0, 'L');
		$pdf->SetFont('helvetica', 'B', $t_me);
		$pdf->Cell($a_c3, 0, 'Por:', '', 0, 'L');
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->Cell(30, 0, $mon_simb.' '.conComas($cldp_monto), 'LTRB', 0, 'R');
		$pdf->Ln();

		$pdf->Ln();

		$t_me = 12; // tamano de letra mensaje

		$parrafo1 = 'Yo, '.$nombres.' con DNI '.$dni.' declaro haber recibido la suma de '.MontoMonetarioEnLetras($cldp_monto).' '.$mon_nom.' por concepto de '.$cldp_conc.".\n\n";
		$pdf->SetFont('helvetica', '', $t_me);
		$pdf->MultiCell(0, 0, utf8_encode($parrafo1), 'LTR', 'J');

		$fec_doc = date('d/m/Y', strtotime($cldp_ent_fec));
		$pzas = explode("/", $fec_doc);
		$parrafo2 = $sucursal.', '.$pzas[0].' de '.nombreMes($pzas[1]).' del '.$pzas[2];
		$pdf->Cell(0, 0, utf8_encode($parrafo2), 'LRB', 0, 'R');
		$pdf->Ln();

		// Seccion de firmas
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();

		$pdf->SetFont('helvetica', 'B', 7);
		$pdf->Cell(80, 0, '', '', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
		$pdf->Cell(80, 0, utf8_encode($nombres), 'T', 0, 'C');
		$pdf->Ln();
		$pdf->Cell(80, 0, '', '', 0, 'C');
		$pdf->Cell(10, 0, '', '', 0, 'L');
		$pdf->Cell(80, 0, 'DNI '.$dni, '', 0, 'C');

		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();

	}



	// ---------------------------------------------------------

	//Close and output PDF document
	// S: devuelve string, I: inline display, D: download pdf
	//$attach = $pdf->Output('example_003.pdf', 'S');
	//$attach = $pdf->Output('example_003.pdf', 'I');

	return $pdf->Output($file_name.".pdf", $output);
}
?>
