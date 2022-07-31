<?php

define("RPDF_SHOWCOL_ALLPAGE",true);
define("RPDF_NOTSHOWCOL_ALLPAGE",false);
define("RPDF_PAGE_LANDSCAPE",true);
define("RPDF_PAGE_PORTRAIT",false);
define("RPDF_HIDE_TOO_LONG_CELL",true);
define("RPDF_SHOW_TOO_LONG_CELL",false);

define("RPDF_SKIP_PRINT_ENTETE",true);

require_once __DIR__ . "/PHPExcel-1.8/PHPExcel.php";
require_once __DIR__ . "/tcpdf/tcpdf.php";

class FacturePDF extends TCPDF {
	public function __construct( $id_cart, $orientation = 'P',  $unit = 'mm', $format = 'Letter', $unicode = true, $encoding = 'UTF-8', $diskcache = false){
		$this->id_cart = $id_cart;
		$this->titre = L("commande","u")." #".$this->id_cart;
		$this->cumulAddPage = 0;
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);
	}
	
	public function moncheckPageBreak($h=0,$y='',$addPage=true){
		return $this->checkPageBreak($h,$y,$addPage);
	}
	//Page header
	public function Header() {
		$this->cumulAddPage += 1;
		//var_export($this->getPageDimensions());
		//die();
		// Logo
		$fileLogo = __DIR__."/../img/animoLogoBlack.jpg";
		if ( file_exists($fileLogo) ){
			$this->Image($fileLogo, $this->getPageDimensions()["wk"]-PDF_MARGIN_RIGHT-50, 8, 50, 20, '', '', '', 2, 300, '', false, false, 0, "RT" );
		}
		
		// Title
		$this->SetFont('helvetica', 'B', 36);
		$this->SetColor( "text", 96, 96, 96 );
		$this->Cell(0, 21, $this->titre, 0, 1, 'L', false, '', 0, false, 'T', 'B');
				
		$this->SetY( max( $this->GetY(), 28), false );
		
		//Ligne de fin
		$marginLine = 2;
		$this->Line(PDF_MARGIN_LEFT,$this->GetY()+$marginLine,$this->getPageDimensions()["wk"]-PDF_MARGIN_RIGHT,$this->GetY()+$marginLine, array("width"=>0.2,"color"=>array(0,0,0)));
		$this->SetTopMargin( $this->GetY() + $marginLine + 1 );
	}
	
	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15,true);
		$this->SetFont('helvetica', '', 8);
		
		$largeur = $this->getPageDimensions()["wk"]-(PDF_MARGIN_RIGHT*2);

		$this->Cell( $largeur*0.7 , 10, $this->titre . " | " . formatDateUTF8(date("Y-m-d")) . " | TPS : 845172337 | TVQ : 1219394051", 0, 0, 'L', 0, '', 0, false, 'T', 'M');
		
		$textPage = L("page","o") . ' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages();
		$this->Cell($largeur*0.3, 10, $textPage, 0, 0, 'R', 0, '', 0, false, 'T', 'M');
	}
}

function faireLigneDetails($pdf,$pdfTest,$txt1,$txt2,$largeur2=null){
	$paddingX = 0.5;
	$paddingY = 1.7;
	$spacing  = 0.6;
	$pdf->SetFont('helvetica', '', 8);
	$pdfTest->SetFont('helvetica', '', 8);
	$colorFill = array(228,228,228);
	$pdf->SetColor( "draw", 128, 128, 128 );
	
	if ( $txt2 != "" ){
		$width1 = $width2 = ($pdf->getPageDimensions()["wk"]-PDF_MARGIN_RIGHT-PDF_MARGIN_LEFT-$spacing)/2;
		if ($largeur2){
			$width1 -= $largeur2 - $width2;
			$width2 += $largeur2 - $width2;
		}
		
		$h1 = testHeightHtml($pdfTest,$txt1,$width1-$paddingX-$paddingX);
		$h2 = testHeightHtml($pdfTest,$txt2,$width2-$paddingX-$paddingX);
		
		$hauteur = max($h1,$h2);
		
		if ( $hauteur < ($pdf->getPageDimensions()["hk"] - $pdf->getBreakMargin() - $pdf->getPageDimensions()["tm"]) ){
			$pdf->moncheckPageBreak($hauteur+$paddingY+$paddingY);
		}
		//
		
		
		$noPageInit = $pdf->getPage();
		$yInit = $pdf->GetY();
		
		
		//TEST
		$pdf->cumulAddPage = 0;
		$pdf->startTransaction();
		
		$pdf->writeHTMLCell($width1-$paddingX-$paddingX,0,$pdf->GetX()+$paddingX,$pdf->GetY()+$paddingY,$txt1,0,1,false,true,"L",false);
		if ( $h1 >= $h2 ){
			$yEnd = $pdf->GetY();
			$pageEnd = $pdf->getPage();
		}
		$pdf->setPage( $noPageInit );
		$pdf->SetY($yInit,true);
		$pdf->SetX( $pdf->GetX() + $width1 + $spacing );
		
		$pdf->writeHTMLCell($width2-$paddingX-$paddingX,0,$pdf->GetX()+$paddingX,$pdf->GetY()+$paddingY,$txt2,0,1,false,true,"L",false);
		if ( $h1 < $h2 ){
			$yEnd = $pdf->GetY();
			$pageEnd = $pdf->getPage();
		}
		$nbPage = $pdf->cumulAddPage;
		$pdf->rollbackTransaction(true);
		
		
		for($i=0;$i<$nbPage;$i++){
			$pdf->AddPage();
		}
		//vex([$yEnd,$pageEnd]);
		//vex($nbPage);
		
		for ( $page = $noPageInit; $page <= $pageEnd; $page++ ){
			//$pdf->tMargin    $pdf->bMargin
			
			$pdf->setPage($page);
			
			if ( $page == $noPageInit and $page == $pageEnd ){
				$pdf->RoundedRect($pdf->GetX(),$pdf->GetY(),$width1,$hauteur+$paddingY+$paddingY,3, '0011','DF',["width"=>"0.1","dash"=>"0"],$colorFill );
				$pdf->RoundedRect($pdf->GetX() + $width1 + $spacing,$pdf->GetY(),$width2,$hauteur+$paddingY+$paddingY,3, '1100','DF',["width"=>"0.1","dash"=>"0"],$colorFill );
			} elseif ( $page == $noPageInit ){
				$yMax = $pdf->getPageDimensions()["hk"] - $pdf->getBreakMargin();
				
				$pdf->RoundedRect($pdf->GetX()                    , $yInit, $width1, $yMax - $yInit, 3, '0001','DF',["width"=>"0.1","dash"=>"0"],$colorFill );
				$pdf->RoundedRect($pdf->GetX()+ $width1 + $spacing, $yInit, $width2, $yMax - $yInit, 3, '1000','DF',["width"=>"0.1","dash"=>"0"],$colorFill );
			} elseif ( $page == $pageEnd ){
				$pdf->SetY( $pdf->getPageDimensions()["tm"], true );
				
				$pdf->RoundedRect($pdf->GetX()                    , $pdf->GetY() - 0.5,$width1, $yEnd - $pdf->GetY() + $paddingY,3, '0010','DF',["width"=>"0.1","dash"=>"0"],$colorFill );
				$pdf->RoundedRect($pdf->GetX()+ $width1 + $spacing, $pdf->GetY() - 0.5,$width2, $yEnd - $pdf->GetY() + $paddingY,3, '0100','DF',["width"=>"0.1","dash"=>"0"],$colorFill );
			} else {
				$yMax = $pdf->getPageDimensions()["hk"] - $pdf->getBreakMargin();
				$pdf->SetY( $pdf->getPageDimensions()["tm"], true );
				
				$pdf->RoundedRect($pdf->GetX()                    , $pdf->GetY() - 0.5, $width1, $yMax - $pdf->GetY(), 3, "",'DF',["width"=>"0.1","dash"=>"0"],$colorFill );
				$pdf->RoundedRect($pdf->GetX()+ $width1 + $spacing, $pdf->GetY() - 0.5, $width2, $yMax - $pdf->GetY(), 3, "",'DF',["width"=>"0.1","dash"=>"0"],$colorFill );
			}
		}
		
		
		$pdf->setPage( $noPageInit );
		$pdf->SetY($yInit,true);
		$pdf->writeHTMLCell($width1-$paddingX-$paddingX,0,$pdf->GetX()+$paddingX,$pdf->GetY()+$paddingY,$txt1,0,0,false,true,"L",false);
		
		
		$pdf->setPage( $noPageInit );
		$pdf->SetY($yInit,true);
		$pdf->SetX( $pdf->GetX() + $width1 + $spacing );
		
		$pdf->writeHTMLCell($width2-$paddingX-$paddingX,0,$pdf->GetX()+$paddingX,$pdf->GetY()+$paddingY,$txt2,0,0,false,true,"L",false);
		
		$pdf->setPage( $pageEnd );
		$pdf->SetY($yEnd + $paddingY + $paddingY,true);
		
	} else {
		
		$width1 = $pdf->getPageDimensions()["wk"]-PDF_MARGIN_RIGHT-PDF_MARGIN_LEFT;
		$hauteur = testHeightHtml($pdfTest,$txt1,$width1-$paddingX-$paddingX);
		
		
		/*
		 $pdf->moncheckPageBreak($hauteur+$paddingY+$paddingY);
		 
		 $pdf->RoundedRect($pdf->GetX(),$pdf->GetY(),$width,$hauteur+$paddingY+$paddingY,3,'1111','DF',["width"=>"0.1","dash"=>"0"],$colorFill );
		 $pdf->writeHTMLCell($width-$paddingX-$paddingX,0,$pdf->GetX()+$paddingX,$pdf->GetY()+$paddingY,$txt1,0,0,false,true,"L",false);
		 
		 $pdf->Ln();
		 $pdf->Ln($paddingY+$paddingY+$spacing);
		 */
		
		if ( $hauteur < ($pdf->getPageDimensions()["hk"] - $pdf->getBreakMargin() - $pdf->getPageDimensions()["tm"]) ){
			$pdf->moncheckPageBreak($hauteur+$paddingY+$paddingY);
		} else {
			$pdf->moncheckPageBreak(50);
		}
		
		
		$noPageInit = $pdf->getPage();
		$yInit = $pdf->GetY();
		
		//TEST
		$pdf->cumulAddPage = 0;
		$pdf->startTransaction();
		
		$pdf->writeHTMLCell($width1-$paddingX-$paddingX,0,$pdf->GetX()+$paddingX,$pdf->GetY()+$paddingY,$txt1,0,1,false,true,"L",false);
		$yEnd = $pdf->GetY();
		$pageEnd = $pdf->getPage();
		
		$nbPage = $pdf->cumulAddPage;
		$pdf->rollbackTransaction(true);
		
		
		for($i=0;$i<$nbPage;$i++){
			$pdf->AddPage();
		}
		
		
		//vex(["nbPage"=>$nbPage,"cumulAddPage"=>$pdf->cumulAddPage,"noPageInit"=>$noPageInit,"pageEnd"=>$pageEnd]);
		
		for ( $page = $noPageInit; $page <= $pageEnd; $page++ ){
			//$pdf->tMargin    $pdf->bMargin
			
			$pdf->setPage($page);
			//vex(["GetYa"=>$pdf->GetY(),"cumulAddPage"=>$pdf->cumulAddPage]);
			
			if ( $page == $noPageInit and $page == $pageEnd ){
				//vex(["yInit"=>$yInit]);
				$pdf->SetY($yInit,true);
				$pdf->RoundedRect($pdf->GetX(),$pdf->GetY(),$width1,$hauteur+$paddingY+$paddingY,3, '1111','DF',["width"=>"0.1","dash"=>"0"],$colorFill );
			} elseif ( $page == $noPageInit ){
				$yMax = $pdf->getPageDimensions()["hk"] - $pdf->getBreakMargin();
				
				$pdf->RoundedRect($pdf->GetX()                    , $yInit, $width1, $yMax - $yInit, 3, '1001','DF',["width"=>"0.1","dash"=>"0"],$colorFill );
			} elseif ( $page == $pageEnd ){
				$pdf->SetY( $pdf->getPageDimensions()["tm"], true );
				
				$pdf->RoundedRect($pdf->GetX()                    , $pdf->GetY() - 0.5,$width1, $yEnd - $pdf->GetY() + $paddingY,3, '0110','DF',["width"=>"0.1","dash"=>"0"],$colorFill );
			} else {
				$yMax = $pdf->getPageDimensions()["hk"] - $pdf->getBreakMargin();
				$pdf->SetY( $pdf->getPageDimensions()["tm"], true );
				
				$pdf->RoundedRect($pdf->GetX()                    , $pdf->GetY() - 0.5, $width1, $yMax - $pdf->GetY(), 3, "",'DF',["width"=>"0.1","dash"=>"0"],$colorFill );
			}
			//vex(["GetYb"=>$pdf->GetY(),"cumulAddPage"=>$pdf->cumulAddPage]);
		}
		
		//vex(["c"=>$pdf->cumulAddPage]);
		
		$pdf->setPage( $noPageInit );
		$pdf->SetY($yInit,true);
		
		$pdf->writeHTMLCell($width1-$paddingX-$paddingX,0,$pdf->GetX()+$paddingX,$pdf->GetY()+$paddingY,$txt1,0,0,false,true,"L",false);
		
		//echo "-=C";
		//echo "<br />";			echo "<br />";			echo "<br />";echo "<br />"; echo $txt1;
		//vex(["noPageInit"=>$noPageInit,"cumulAddPage"=>$pdf->cumulAddPage,"nbPage"=>$nbPage,"getNumPages"=>$pdf->getNumPages()]);
		//$pdf->setPage( $noPageInit + $pdf->cumulAddPage );
		$pdf->setPage( $pageEnd );
		//	echo "-=";
		$pdf->SetY($yEnd + $paddingY + $paddingY,true);
	}
	
	
}

function testHeightHtml(&$pdf2,$txt,$width){
	$pdf2->startTransaction();
	
	$initY = $pdf2->GetY();
	
	$pdf2->writeHTMLCell($width,0,$pdf2->GetX(),$pdf2->GetY(),$txt,0,1,false,true,"L",false);
	$diff = $pdf2->GetY() - $initY;
	
	$pdf2->rollbackTransaction(true);

	return $diff;
}

function faireLigneResume($pdf,$pdfTest,$txtArray,$listLargeur){
	$paddingX = 0.5;
	$paddingY = 1.7;
	$spacing = 0.6;
	$pdf->SetFont('helvetica', '', 8);
	$pdfTest->SetFont('helvetica', '', 8);
	$colorFill = array(228,228,228);
	$pdf->SetColor( "draw", 128, 128, 128 );
	
	
	$hauteur = max( testHeightHtml($pdfTest,$txtArray[6],$listLargeur[6]-$paddingX-$paddingX), testHeightHtml($pdfTest,$txtArray[0],$listLargeur[0]-$paddingX-$paddingX) );
	
	$pdf->moncheckPageBreak($hauteur+$paddingY+$paddingY);
	
	
	$width = $listLargeur[0];
	$txt = $txtArray[0];
	$h = testHeightHtml($pdfTest,$txt,$width-$paddingX-$paddingX);
	$pdf->RoundedRect($pdf->GetX(),$pdf->GetY(),$width,$hauteur+$paddingY+$paddingY,3,'0011','DF',["width"=>"0.1","dash"=>"0"],$colorFill );
	$pdf->writeHTMLCell($width-$paddingX-$paddingX,0,$pdf->GetX()+$paddingX,$pdf->GetY()+$paddingY+(($hauteur-$h)/2),$txt,0,0,false,true,"L",false);
	$pdf->SetX( $pdf->GetX() + $paddingX + $spacing );
	$pdf->SetY( $pdf->GetY() - $paddingY - (($hauteur-$h)/2), false );
	
	$width = $listLargeur[1];
	$txt = $txtArray[1];
	$h = testHeightHtml($pdfTest,$txt,$width-$paddingX-$paddingX);
	$pdf->Rect($pdf->GetX(),$pdf->GetY(),$width,$hauteur+$paddingY+$paddingY,'DF',array("LTRB"=>array("width"=>"0.1","dash"=>"0")),$colorFill );
	$pdf->writeHTMLCell($width-$paddingX-$paddingX,0,$pdf->GetX()+$paddingX,$pdf->GetY()+$paddingY+(($hauteur-$h)/2),$txt,0,0,false,true,"C",false);
	$pdf->SetX( $pdf->GetX() + $paddingX + $spacing );
	$pdf->SetY( $pdf->GetY() - $paddingY - (($hauteur-$h)/2), false );
	
	$width = $listLargeur[2];
	$txt = $txtArray[2];
	$h = testHeightHtml($pdfTest,$txt,$width-$paddingX-$paddingX);
	$pdf->Rect($pdf->GetX(),$pdf->GetY(),$width,$hauteur+$paddingY+$paddingY,'DF',array("LTRB"=>array("width"=>"0.1","dash"=>"0")),$colorFill );
	$pdf->writeHTMLCell($width-$paddingX-$paddingX,0,$pdf->GetX()+$paddingX,$pdf->GetY()+$paddingY+(($hauteur-$h)/2),$txt,0,0,false,true,"R",false);
	$pdf->SetX( $pdf->GetX() + $paddingX + $spacing );
	$pdf->SetY( $pdf->GetY() - $paddingY - (($hauteur-$h)/2), false );
	
	
	$width = $listLargeur[3];
	$txt = $txtArray[3];
	$h = testHeightHtml($pdfTest,$txt,$width-$paddingX-$paddingX);
	$pdf->RoundedRect($pdf->GetX(),$pdf->GetY(),$width,$hauteur+$paddingY+$paddingY,3,'1100','DF',array("LTRB"=>array("width"=>"0.1","dash"=>"0")),$colorFill );
	$pdf->writeHTMLCell($width-$paddingX-$paddingX,0,$pdf->GetX()+$paddingX,$pdf->GetY()+$paddingY+(($hauteur-$h)/2),$txt,0,0,false,true,"R",false);
	
	
	$pdf->SetY( $pdf->GetY() + $hauteur + $paddingY - (($hauteur-$h)/2) + $paddingY, true );
}

function columnIndexFromString($pString){
	static $_columnLookup = array(
			'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9, 'J' => 10, 'K' => 11, 'L' => 12, 'M' => 13,
			'N' => 14, 'O' => 15, 'P' => 16, 'Q' => 17, 'R' => 18, 'S' => 19, 'T' => 20, 'U' => 21, 'V' => 22, 'W' => 23, 'X' => 24, 'Y' => 25, 'Z' => 26,
			'a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6, 'g' => 7, 'h' => 8, 'i' => 9, 'j' => 10, 'k' => 11, 'l' => 12, 'm' => 13,
			'n' => 14, 'o' => 15, 'p' => 16, 'q' => 17, 'r' => 18, 's' => 19, 't' => 20, 'u' => 21, 'v' => 22, 'w' => 23, 'x' => 24, 'y' => 25, 'z' => 26
	);

	//      We also use the language construct isset() rather than the more costly strlen() function to match the length of $pString
	//              for improved performance
	if (isset($pString{0})) {
		if (!isset($pString{1})) {
			return $_columnLookup[$pString];
		} elseif(!isset($pString{2})) {
			return $_columnLookup[$pString{0}] * 26 + $_columnLookup[$pString{1}];
		} elseif(!isset($pString{3})) {
			return $_columnLookup[$pString{0}] * 676 + $_columnLookup[$pString{1}] * 26 + $_columnLookup[$pString{2}];
		}
	}
	throw new Exception("Column string index can not be " . ((isset($pString{0})) ? "longer than 3 characters" : "empty") . ".");
}

function stringFromColumnIndex($pColumnIndex = 0){
	// Determine column string
	if ($pColumnIndex < 26) {
		return chr(65 + $pColumnIndex);
	} elseif ($pColumnIndex < 702) {
		return chr(64 + ($pColumnIndex / 26)).chr(65 + $pColumnIndex % 26);
	}
	return chr(64 + (($pColumnIndex - 26) / 676)).chr(65 + ((($pColumnIndex - 26) % 676) / 26)).chr(65 + $pColumnIndex % 26);
}

class RapportXLS extends PHPExcel {
	
	public function __construct( $landscape = null, $hasHeadersColsAllPages=false ){
		global $mysqli;
		$enonce = sprintf("select * from MAGASIN where ID_MAGASIN = %s",$_SESSION["mag"]);
		$resultMag = $mysqli->query($enonce) or die("ERRROR SQL:".__LINE__);
		$this->uneLigneMag = $resultMag->fetch_assoc();

		parent::__construct();

		$this->hasHeadersColsAllPages = $hasHeadersColsAllPages;
		$this->headersColsData = null;
		$this->headersColsDataEx = null;
		$this->headersColsData3 = null;
		$this->listLigneEnteteColonne = null;
		
		$this->page = 0;
		$this->masqueNbPage = "{{~NB_PAGE~}}";
		$this->listCelluleToReplaceNbPage = [];
		$this->skipAutoMerge = false;
		
		$this->SetFont('helvetica', '', 8);
		$this->SetLineStyle( array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)) );

		//$this->$paddingX = 1.7;
		//$this->$paddingY = 1.7;
		$this->getProperties()->setCreator("AnimoEtc");
		
		
		//Base
		if($landscape === true){ //
			$this->size = [279, 216];
			$this->listAncreCols = [ 0, 93, 186, 279];
		}else if($landscape === false){ 
			$this->size = [216, 279];
			$this->listAncreCols = [ 0, 72, 144, 216 ];
		}
		$this->largeur = $this->size[0];
		
		$this->currentPos = "A1";
		$this->_ratioMMToPoints = 0.63465;
		$this->_ratioPxToPoints = 0.75;
		
		$this->_listFileToDelete = [];
	}

	function Output($name,$typeOutput){
		//Inclure le dernier footer
		if ($this->page != 0) {
			$this->Footer();
		}
	
		//Remplacer les flags de nbpage dans les cellules appropriées
		foreach ( $this->listCelluleToReplaceNbPage as $cellPos ){
			$txt = $this->getActiveSheet()->getCell( $cellPos )->getValue();
			$newStr = str_replace($this->masqueNbPage, (string)$this->page, $txt);
				
			if ( $newStr != $txt ){
				$this->getActiveSheet()->setCellValue( $cellPos, $newStr );
			}
		}
	
		$name = pathinfo($name,PATHINFO_FILENAME) . ".xlsx";
		
		//OUTPUT!
		ini_set("memory_limit","300M");
		
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="'.$name.'"');
		header('Cache-Control: max-age=0');
		$writer = PHPExcel_IOFactory::createWriter($this, 'Excel2007');
		//$writer->save('php://output');
		
		$filePath = sys_get_temp_dir() . "/" . rand(0, getrandmax()) . rand(0, getrandmax()) . ".tmp";
		$writer->save($filePath);
		readfile($filePath);
		unlink($filePath);
		
		
		//Clear cache image
		foreach ( $this->_listFileToDelete as $filename ){
			if ( file_exists($filename) ) unlink($filename);
		}
	}
	
	
	public function msg_error( $t ){
		$this->SetFont('helvetica', 'B', 10);
		$this->Ln();
		$this->MultiCell(0,0," <!> <!> ". $t." <!> <!> ",0,"L",false,1);
		$this->Ln();
	}
	
	public function writeHTMLCell( $w, $h, $x, $y, $html = '', $border = 0, $ln = 0, $fill = 0, $reseth = true, $align = '', $autopadding = true){
		die("excel:writeHTMLCell");
	}
	public function writeHTML( $html, $ln = true, $fill = false, $reseth = false, $cell = false, $align = ''){
		global $INDEV;

		
		do{
			$nomtmpHtml = '/tmp/animo_htmltoimage_i_'.mt_rand().'.html';
		} while(file_exists($nomtmpHtml));
		file_put_contents($nomtmpHtml, $html );
			
		do{
			$nomtmpImg = '/tmp/animo_htmltoimage_o_'.mt_rand().'.jpg';
		} while(file_exists($nomtmpImg));
		
		$dataImg = null;
		try {
			$html = iconv("UTF-8", "Latin1//IGNORE", $html);
			file_put_contents($nomtmpHtml,$html);
			//$cmd = sprintf( 'wkhtmltoimage --width %d', (int)$_POST["width"]);
			$cmd  = sprintf('wkhtmltoimage');
			$cmd .= ' "'.$nomtmpHtml.'"';
			$cmd .= ' "'.$nomtmpImg.'"';
			
			$proc=proc_open($cmd,array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
			fwrite($pipes[0],$input);
			fclose($pipes[0]);
			$stdout=stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			$stderr=stream_get_contents($pipes[2]);
			fclose($pipes[2]);
			$rtn=proc_close($proc);
			
			$objDrawing = new PHPExcel_Worksheet_Drawing();
			$objDrawing->setPath( $nomtmpImg );
			
			$objDrawing->setCoordinates( $this->currentPos );
			list($widthImg,$heightImg) = getimagesize($nomtmpImg);
			$objDrawing->setWidth($widthImg); 
			$objDrawing->setHeight($heightImg);
			$objDrawing->setWorksheet($this->getActiveSheet());
			
			$this->_listFileToDelete[] = $nomtmpImg;
			
			
			//set row height
			$dim = $this->getActiveSheet()->getRowDimension( $this->getCurrentLigne() );
			$newHeight = $heightImg * $this->_ratioPxToPoints;
			if ( $dim->getRowHeight() < $newHeight ){
				$dim->setRowHeight( $newHeight );
			}
			
			//$this->Ln();
		} catch (Exception $e) {
			echo("Erreur durant la procédure.");
			if( $INDEV ) {wisePrintStack($e);}
			die();
		} finally {
			if ( file_exists($nomtmpHtml) ) unlink($nomtmpHtml);
			//if ( file_exists($nomtmpImg) ) unlink($nomtmpImg);
		}
		
		
		
	}
	

	
	public function SetFont( $name, $style, $size ){
		$style = strtoupper($style);
		
		$isBold = strpos($style,'B') !== false;
		$isItalic = strpos($style,'I') !== false;
		
		$this->fontEnCours = [
						        'font' => [
							        'bold'  => $isBold,
							        'size'  => $size,
							        'name'  => $name,
						            'italic'=> $isItalic
						        ]
				             ];
		
	}
	

	
	public function AddPage($orientation = "", $format = "", $keepmargins = false, $tocpage = false ){
		if ($this->page >= 1) {
			$this->Footer();
		} 
		
		$this->page += 1;
		
		if ($this->page == 1) {
			//First page, set largeur
			$lastX = 0;
			foreach ($this->listAncreCols as $k => $startX){
				if ( $k === 0 ) continue;
				$this->getActiveSheet()->getColumnDimension( stringFromColumnIndex($k-1) )->setWidth(  ($startX-$lastX) * $this->_ratioMMToPoints  );
				$lastX = $startX;
			}
		}
		
		$this->Header();
	}

	public function getY(){
		return 0;
	}
	public function moncheckPageBreak($h=0,$y='',$addPage=true){
		return false;
	}
	
	public function MultiCell($w, $h=0, $txt='', $border=0, $align='', $fill=0, $ln=1){
		$this->Cell($w,$h,$txt,$border,$ln,$align,$fill);
	}
	public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M'){

		//Merge Colonnes
		if ( !$this->skipAutoMerge ){
			if ( $w === 0 ){
				$colA = $this->getCurrentColonne();
				$colB = $this->getMaxColonne();
				$lig = $this->getCurrentLigne();
				$this->getActiveSheet()->mergeCells($colA.$lig.":".$colB.$lig);
			} else {
				$nextX = $this->listAncreCols[columnIndexFromString($this->getCurrentColonne())-1] + $w;
				foreach ($this->listAncreCols as $k => $startX){
					if ( $startX >= $nextX ){
						if ( $k-1 > $this->getCurrentColonne() ){
							$colA = $this->getCurrentColonne();
							$colB = stringFromColumnIndex($k-1);
							$lig = $this->getCurrentLigne();
							$this->getActiveSheet()->mergeCells($colA.$lig.":".$colB.$lig);
						}
						break;
					}
				}
			}
		}
		
		//set font
		$font = $this->fontEnCours;
		if ( $align ){
			if ( $align == "R" ){
				$font["alignment"] = ["horizontal"=>"right",];
			}
			if ( $align == "C" ){
				$font["alignment"] = ["horizontal"=>"center",];
			}
		}
		
		if ( $calign ){
			if ( $align == "T" ){
				$font["alignment"] = ["vertical"=>"top",];
			}
			if ( $align == "M" ){
				$font["alignment"] = ["vertical"=>"center",];
			}
			if ( $align == "B" ){
				$font["alignment"] = ["vertical"=>"bottom",];
			}
		}
		
		if ( !$this->skipAutoMerge ){
			if ( $border and $this->monLineStyle ){
				$font["borders"] = [];
				list($styleBorder,$colorBorder) = $this->convertBorderStyleToXLS($this->monLineStyle);
				
				if ( strpos($border,"T") !== false ){
					$font["borders"]["top"] = ["style"=>$styleBorder, "color"=>["rgb"=>$colorBorder] ];
				}
				if ( strpos($border,"B") !== false ){
					$font["borders"]["bottom"] = ["style"=>$styleBorder, "color"=>["rgb"=>$colorBorder]];
				}
				if ( strpos($border,"L") !== false ){
					$font["borders"]["left"] = ["style"=>$styleBorder, "color"=>["rgb"=>$colorBorder]];
				}
				if ( strpos($border,"R") !== false ){
					$font["borders"]["right"] = ["style"=>$styleBorder, "color"=>["rgb"=>$colorBorder]];
				}
			}
		}
		
		
		if ( $colA and $colB and $lig ){
			$this->getActiveSheet()->getStyle( $colA.$lig.":".$colB.$lig )->applyFromArray($font);
		} else {
			$this->getActiveSheet()->getStyle( $this->currentPos )->applyFromArray($font);
		}
		
		
		//set row height
		$dim = $this->getActiveSheet()->getRowDimension( $this->getCurrentLigne() );
		$newHeight = $font["font"]["size"] + 6;
		if ( $dim->getRowHeight() < $newHeight ){
			$dim->setRowHeight( $newHeight );
		}
		
		if ( is_nfs($txt) ){
			$this->getActiveSheet()->getStyle($this->currentPos)->getNumberFormat()->setFormatCode("# ##0.00");
			$txt = reverse_nfs($txt);
		}
		
		//Voir si flag ""
		if ( mb_strpos($txt,$this->masqueNbPage) !== false ){
			$this->listCelluleToReplaceNbPage[] = $this->currentPos;
		}
		$this->getActiveSheet()->setCellValue( $this->currentPos, $txt );
		
		
		
		if ( $ln == 2 ){
			die("todo:ln:2");
		} elseif ( $ln == 1 ){
			$this->Ln();
		} else { //0
			if ( $w ){
				$w = ceil($w);
				$nextX = $this->listAncreCols[columnIndexFromString($this->getCurrentColonne())-1] + $w;
				foreach ($this->listAncreCols as $k => $startX){
					if ( $startX >= $nextX ){
						$this->currentPos = stringFromColumnIndex($k) . $this->getCurrentLigne();
						break;
					}
				}
			} else{
				$this->currentPos = stringFromColumnIndex(columnIndexFromString($this->getCurrentColonne())+1) . $this->getCurrentLigne();
			}
		}
		
	}
	
	function convertBorderStyleToXLS($style){
		//array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
		
		if ( $style["width"] == 0.5 ){
			$styleBorder = PHPExcel_Style_Border::BORDER_THICK;
		} else if ( $style["width"] == 0.2 ){
			$styleBorder = PHPExcel_Style_Border::BORDER_THIN;
		} else {
			$styleBorder = PHPExcel_Style_Border::BORDER_NONE;
		}
		
		
		if ( $style["color"] ){
			if ( is_array($style["color"]) ){
				if ( sizeof($style["color"]) > 3 ){
					$colorBorder = zeropad(dechex($style["color"][3])) . zeropad(dechex($style["color"][0])) . zeropad(dechex($style["color"][1])) . zeropad(dechex($style["color"][2]));
				} else {
					$colorBorder = "FF" . zeropad(dechex($style["color"][0])) . zeropad(dechex($style["color"][1])) . zeropad(dechex($style["color"][2]));
				}
			} elseif( is_string($style["color"]) and preg_match('/^#([0-9A-F]{2}[0-9A-F]{2}[0-9A-F]{2})$/i',$style["color"],$matches) ){
				$colorBorder = "FF" . $matches[1];
			} else {
				$colorBorder = PHPExcel_Style_Color::COLOR_BLACK;
			}
		} else {
			$colorBorder = PHPExcel_Style_Color::COLOR_BLACK;
		}
		
		return [$styleBorder,$colorBorder];
	}
	function getMaxColonne(){
		return stringFromColumnIndex( sizeof($this->listAncreCols) - 1 - 1 );  //le deuxieme -1 est parce qu'il y a par defaut un index pour la fin
	}
	
	
	function getCurrentColonne(){
		if ( preg_match('#^([A-Z]+)(\d+)$#',$this->currentPos,$matches) ){
			return $matches[1];
		}
		die("error getCurrentColonne:".$this->currentPos);
	}
	function getCurrentLigne(){
		if ( preg_match('#^([A-Z]+)(\d+)$#',$this->currentPos,$matches) ){
			return $matches[2];
		}
		die("error getCurrentLigne:".$this->currentPos);
	}
	
	function Ln($height=null){
		if ( !$height or $height > 1 ){
			$this->currentPos = "A" . ($this->getCurrentLigne() + 1);
		}
		
		if ( $height and $height > 10 ){
			$this->currentPos = "A" . ($this->getCurrentLigne() + 1);
		}
	}
	
	
	public function Header() {
		$this->SetFont('helvetica', '', 8);
		$this->Cell(0, 10, $this->uneLigneMag["M_NOM"]);
		$this->Ln();
	
		if ( $this->newPage ){
			if ( $this->hasHeadersColsAllPages and $this->headersColsData ){
				call_user_func_array( [$this,"debutSection"], $this->headersColsData );
				
			}
			if ( $this->hasHeadersColsAllPages and $this->headersColsDataEx ){
				call_user_func_array( [$this,"debutSectionEx"], $this->headersColsDataEx );
				
			}
			if ( $this->hasHeadersColsAllPages and $this->headersColsData3 ){
				call_user_func_array( [$this,"debutSection3"], $this->headersColsData3 );
				
			}
	
			$this->newPage = false;
		}
			
	}
	
	public function Footer() {
		$this->SetFont('helvetica', '', 8);
		$this->Ln();
		
		$this->Cell( $this->largeur*0.33, 0, "AnimoEtc", "", 0, "L", false, "", 0, false, "T", "T");
		$this->Cell( $this->largeur*0.33, 0, strftime("%d/%m/%Y %H:%M"), "", 0, "C", false, "", 0, false, "T", "T");
		$this->Cell( $this->largeur*0.33, 0, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), "", 0, "R", false, "", 0, false, "T", "T");
		$this->Ln();
	}
	
	function getAliasNumPage(){
		return $this->page;
	}
	function getAliasNbPages(){
		return $this->masqueNbPage;
	}
	
	public function addListAncreCols($listLigneEnteteColonne){
		//Ajouter les heads liste
		foreach ( $listLigneEnteteColonne as $uneLigne ){
			$cumulX = 0;
			foreach ( $uneLigne as $uneCase ){
				if ( !in_array($cumulX+$uneCase["width"],$this->listAncreCols) ){
					$this->listAncreCols[] = $cumulX + $uneCase["width"];
				}
				$cumulX += $uneCase["width"];
			}
		}
		
		sort($this->listAncreCols);
	}
	

	public function debutSectionEx($titre,$lignesGras,$listLigneEnteteColonne,$infoCols){
		
		//Ajouter les heads titre
		if ( !in_array(40,$this->listAncreCols) ){
			$this->listAncreCols[] = 40;
		}
		
		//Ajouter les heads liste
		foreach ( $listLigneEnteteColonne as $uneLigne ){
			$cumulX = 0;
			foreach ( $uneLigne as $uneCase ){
				if ( !in_array($cumulX+$uneCase["width"],$this->listAncreCols) ){
					$this->listAncreCols[] = $cumulX + $uneCase["width"];
				}
				$cumulX += $uneCase["width"];
			}
		}
		
		sort($this->listAncreCols);
		if ( $this->page == 0 ){
			$this->AddPage();
		}
		
		$this->headersColsDataEx = ["","",$listLigneEnteteColonne,$infoCols];
		
		
		if($titre != ""){
			//Titre
			$this->SetFont('helvetica', 'B', 16);
			$this->Cell(0,0,$titre,0,1,"C");
			$this->Ln(5);
		}
		
		if(is_array($lignesGras)){
			//Lignes gras
			$this->SetFont('helvetica', 'B', 10);
			foreach ( $lignesGras as $uneLigne ){
				if (sizeof($uneLigne) >= 2){
					$this->Cell(40,0,$uneLigne[0],0,0,"L",false,"",0,false,"T","T");
					$this->MultiCell(0,0,$uneLigne[1],0,"L",false,1);
				}
			}
			$this->Ln(2);
		}
		
		$this->infoCols = $infoCols;
		$this->nbCol = sizeof($infoCols);
			
		$this->SetFont('helvetica', 'B', 8);
		for($l=0; $l < sizeof($listLigneEnteteColonne); $l++){
			$listEnteteColonne = $listLigneEnteteColonne[$l];
		
			for($c=0; $c < sizeof($listEnteteColonne); $c++){
				$infoCol = $listEnteteColonne[$c];
		
				//si top
				$border = "";
		
				if ( $l == 0 ){
					$border .= "T";
				}
				if ( $l+1 == sizeof($listLigneEnteteColonne) ){
					$border .= "B";
				}
				if ( $c == 0 ){
					$border .= "L";
				}
				if ( $c+1 == sizeof($listEnteteColonne) ){
					$border .= "R";
				}
		
				if ( $infoCol["addborder"] ){
					$border .= $infoCol["addborder"];
				}
				if ( $infoCol["border"] ){
					$border = $infoCol["border"];
				}
					
				$this->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		
				$width  = $infoCol["width"] ? $infoCol["width"] : 10;
				$align = $infoCol["align"] ? $infoCol["align"] : "L";
					
				$ln = ($c+1 == sizeof($listEnteteColonne)) ? 1 : 0;
		
				$this->Cell($width, 0, $infoCol["text"], $border, $ln, $align, false, "", 0, false, "T", "T");
			}
		
		}
		
		$this->SetFont('helvetica', '', 8);
	}
	
	public function debutSection($titre,$lignesGras,$listEnteteColonne,$infoCols){
		
		//Ajouter les heads titre
		if ( !in_array(40,$this->listAncreCols) ){
			$this->listAncreCols[] = 40;
		}
		
		//Ajouter les heads liste
		$cumulX = 0;
		foreach ( $infoCols as $uneCase ){
			if ( !in_array($cumulX+$uneCase["width"],$this->listAncreCols) ){
				$this->listAncreCols[] = $cumulX + $uneCase["width"];
			}
			$cumulX += $uneCase["width"];
		}
		
		
		sort($this->listAncreCols);
		if ( $this->page == 0 ){
			$this->AddPage();
		}
		
		
		
		
		
		
		$this->headersColsData = ["","",$listLigneEnteteColonne,$infoCols];
		//$this->setCellPaddings( $this->cellPaddingHeadCol["L"], $this->cellPaddingHeadCol["T"] , $this->cellPaddingHeadCol["R"], $this->cellPaddingHeadCol["B"] );
		
		if($titre != ""){
			//Titre
			$this->SetFont('helvetica', 'B', 16);
			$this->Cell(0,0,$titre,0,1,"C");
			$this->Ln(5);
		}
		
		if(is_array($lignesGras)){
			//Lignes gras
			$this->SetFont('helvetica', 'B', 10);
			foreach ( $lignesGras as $uneLigne ){
				if (sizeof($uneLigne) >= 2){
					$this->Cell(40,0,$uneLigne[0],0,0,"L",false,"",0,false,"T","T");
					$this->MultiCell(0,0,$uneLigne[1],0,"L",false,1);
				}
			}
			$this->Ln(2);
		}
		
		if(is_array($listEnteteColonne)){
			$this->SetFont('helvetica', 'B', 8);
			$this->nbCol = sizeof($listEnteteColonne);
			$this->infoCols = $infoCols;
			if ( $this->nbCol > 0 ){
					
				for($i=0; $i < $this->nbCol; $i++){
		
					$border = ($i+1 == $this->nbCol) ? "LTBR" : "LTB";
		
					$this->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
					$width  = $this->infoCols[$i]["width"] ? $this->infoCols[$i]["width"] : 10;
					$align = $this->infoCols[$i]["align"] ? $this->infoCols[$i]["align"] : "L";
		
					$ln = $i+1 == $this->nbCol ? 1 : 0;
		
					$this->Cell($width, 0, $listEnteteColonne[$i], $border, $ln, $align, false, "", 0, false, "T", "T");
				}
			}
		}
		
		$this->SetFont('helvetica', '', 8);
	}
	
	public function debutSection3($titre,$lignesGras,$listLigneEnteteColonne,$skipPrintEntete=false){
		//Ajouter les heads titre
		if ( !in_array(40,$this->listAncreCols) ){
			$this->listAncreCols[] = 40;
		}
		
		//Ajouter les heads liste
		foreach ( $listLigneEnteteColonne as $uneLigne ){
			$cumulX = 0;
			foreach ( $uneLigne as $uneCase ){
				if ( !in_array($cumulX+$uneCase["width"],$this->listAncreCols) ){
					$this->listAncreCols[] = $cumulX + $uneCase["width"];
				}
				$cumulX += $uneCase["width"];
			}
		}
		
		sort($this->listAncreCols);
		if ( $this->page == 0 ){
			$this->AddPage();
		}
		
		
		$this->headersColsData3 = ["","",$listLigneEnteteColonne];
		$this->listLigneEnteteColonne = $listLigneEnteteColonne;

		//$this->setCellPaddings( $this->cellPaddingHeadCol["L"], $this->cellPaddingHeadCol["T"], $this->cellPaddingHeadCol["R"], $this->cellPaddingHeadCol["B"] );

		if($titre != ""){
			//Titre
			$this->SetFont('helvetica', 'B', 16);
			$this->Cell(0,0,$titre,0,1,"C");
			$this->Ln(5);
		}

		if(is_array($lignesGras)){
			//Lignes gras
			$this->SetFont('helvetica', 'B', 10);
			foreach ( $lignesGras as $uneLigne ){
				if (sizeof($uneLigne) >= 2){
					$this->Cell(40,0,$uneLigne[0],0,0,"L",false,"",0,false,"T","T");
					$this->MultiCell(0,0,$uneLigne[1],0,"L",false,1);
				}
			}
			$this->Ln(2);
		}
	
		//$this->infoCols = $infoCols;
		//$this->nbCol = sizeof($infoCols);
		if ( !$skipPrintEntete ) $this->printEntetes();
		
		$this->SetFont('helvetica', '', 8);
	}
	
	
	public function printEntetes(){
		
		
		$this->SetFont('helvetica', 'B', 8);
		for($l=0; $l < sizeof($this->listLigneEnteteColonne); $l++){
			$listEnteteColonne = $this->listLigneEnteteColonne[$l];
		
			for($c=0; $c < sizeof($listEnteteColonne); $c++){
				$infoCol = $listEnteteColonne[$c];
		
				//si top
				$border = "";
		
				if ( $l == 0 ){
					$border .= "T";
				}
				if ( $l+1 == sizeof($this->listLigneEnteteColonne) ){
					$border .= "B";
				}
				if ( $c == 0 ){
					$border .= "L";
				}
				if ( $c+1 == sizeof($listEnteteColonne) ){
					$border .= "R";
				}
		
				if ( $infoCol["addborder"] ){
					$border .= $infoCol["addborder"];
				}
				if ( $infoCol["border"] ){
					$border = $infoCol["border"];
				}
					
				$this->SetLineStyle( array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)) );
		
				$width  = $infoCol["width"] ? $infoCol["width"] : 10;
				$align = $infoCol["align"] ? $infoCol["align"] : "L";
					
				$ln = ($c+1 == sizeof($listEnteteColonne)) ? 1 : 0;
		
				$this->Cell($width, 0, $infoCol["prefixe"] . $infoCol["text"] . $infoCol["suffixe"], $border, $ln, $align, false, "", 0, false, "T", "T");
			}
		}
		
	}
	
	public function SetLineStyle( $style ){
		$this->monLineStyle = $style;
	}


	public function setInfoCols( $iLigne=-1 ){
		if ( $iLigne < 0 ){
			$this->infoCols = $this->listLigneEnteteColonne[sizeof($this->listLigneEnteteColonne)+$iLigne];
		} else {
			$this->infoCols = $this->listLigneEnteteColonne[$iLigne];
		}
		$this->nbCol = sizeof($this->infoCols);
	}

	public function writeLigneRapport3wrap($listChamps, $iLigne=-1, $skipDefaultFont = false, $nbMaxLine=null){
		$this->setInfoCols($iLigne);
		$this->writeLigneRapportWrap($listChamps, $skipDefaultFont, $nbMaxLine);
	}
	public function writeLigneRapport3($listChamps, $iLigne=-1, $skipDefaultFont = false, $hideTooLongCell=false){
		if ( $iLigne < 0 ){
			$this->infoCols = $this->listLigneEnteteColonne[sizeof($this->listLigneEnteteColonne)+$iLigne];
		} else {
			$this->infoCols = $this->listLigneEnteteColonne[$iLigne];
		}

		$this->nbCol = sizeof($this->infoCols);
		$this->writeLigneRapport($listChamps, $skipDefaultFont, $hideTooLongCell);
	}

	public function writeLigneRapport($listChamps, $skipDefaultFont = false, $hideTooLongCell=false){
		
		if(!$skipDefaultFont)
			$this->SetFont('helvetica', '', 8);

		for($i=0; $i < $this->nbCol; $i++){
				
			$width  = $this->infoCols[$i]["width"] ? $this->infoCols[$i]["width"] : 10;
			$align = $this->infoCols[$i]["align"] ? $this->infoCols[$i]["align"] : "L";
			
			$ln = $i+1 == $this->nbCol ? 1 : 0;
				
			if ( isset($listChamps[$i]) ){
				$t = $this->infoCols[$i]["prefixe"] . $listChamps[$i] . $this->infoCols[$i]["suffixe"];
				//if ( $hideTooLongCell ){$t = $this->substringTofitWidth($t,$width);}

				$this->Cell($width, 0, $t, "", $ln, $align, false, "", 0, false, "T", "T");
			} else {
				$this->Cell($width, 0, "", "", $ln, $align, false, "", 0, false, "T", "T");
			}
		}
		
	}
	public function writeLigneRapportWrap($listChamps, $skipDefaultFont = false, $nbMaxLine=null){
		if ( empty($this->infoCols) ){
			$this->setInfoCols();
		}
		$this->writeLigneRapport($listChamps,$skipDefaultFont,$nbMaxLine);
	}
	public function writeLigneGrandTotal($listChamps, $topBorders=null, $isBold=true, $convertFnctOnTopBordersLabel=null){
		$convertFnctOnTopBordersLabel = null;
		$this->SetFont('helvetica', 'B', 9);
		$this->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		for($i=0; $i < $this->nbCol; $i++){
			$width  = $this->infoCols[$i]["width"] ? $this->infoCols[$i]["width"] : 10;
			$align = $this->infoCols[$i]["align"] ? $this->infoCols[$i]["align"] : "L";
	
			$ln = $i+1 == $this->nbCol ? 1 : 0;
	
			if ( isset($listChamps[$i]) ){
				if ( $convertFnctOnTopBordersLabel and sizeof($topBorders) > $i and $topBorders[$i] ){
					$listChamps[$i] = $convertFnctOnTopBordersLabel($listChamps[$i]);
				}
	
				if ( !$topBorders or (sizeof($topBorders) > $i and $topBorders[$i]) ){
					$this->Cell($width, 0, $listChamps[$i], "T", $ln, $align, false, "", 0, false, "T", "T");
				} else {
					$this->Cell($width, 0, $listChamps[$i], "", $ln, $align, false, "", 0, false, "T", "T");
				}
			} else {
				$this->Cell($width, 0, $listChamps[$i], "", $ln, $align, false, "", 0, false, "T", "T");
			}
		}
	}
	public function writeLigneTotaux($listChamps, $topBorders=null, $isBold=true, $convertFnctOnTopBordersLabel=null){
		$convertFnctOnTopBordersLabel = null;
		
		$this->SetFont('helvetica', ($isBold?"B":""), 8);
	
		for($i=0; $i < $this->nbCol; $i++){
			$width  = $this->infoCols[$i]["width"] ? $this->infoCols[$i]["width"] : 10;
			$align = $this->infoCols[$i]["align"] ? $this->infoCols[$i]["align"] : "L";
	
			$ln = $i+1 == $this->nbCol ? 1 : 0;
	
			$this->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
	
			if ( isset($listChamps[$i]) ){
				if ( $convertFnctOnTopBordersLabel and sizeof($topBorders) > $i and $topBorders[$i] ){
					$listChamps[$i] = $convertFnctOnTopBordersLabel($listChamps[$i]);
				}
	
				if ( !$topBorders or (sizeof($topBorders) > $i and $topBorders[$i]) ){
					$this->Cell($width, 0, $listChamps[$i], "T", $ln, $align, false, "", 0, false, "T", "T");
				} else {
					$this->Cell($width, 0, $listChamps[$i], "",  $ln, $align, false, "", 0, false, "T", "T");
				}
			} else {
				$this->Cell($width, 0, "", "", $ln, $align, false, "", 0, false, "T", "T");
			}
		}
	}

	public function SetFillColor($a=null,$b=null,$c=null,$d=null){
		
	}
	public function setAlterneBG($a=null){
		
	}
}

class RapportPDF extends TCPDF {
	public function __construct($landscape = null, $hasHeadersColsAllPages=false){
		global $mysqli;
		
		$enonce = sprintf("select * from MAGASIN where ID_MAGASIN = %s",$_SESSION["mag"]);
		$resultMag = $mysqli->query($enonce) or die("ERRROR SQL:".__LINE__);
		$this->uneLigneMag = $resultMag->fetch_assoc();

		
		if($landscape === true){
			parent::__construct("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		}else if($landscape === false){
			parent::__construct("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		}else{
			parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		}
		
		mb_internal_encoding("UTF-8");
		
		$this->hasHeadersColsAllPages = $hasHeadersColsAllPages;
		$this->headersColsData = null;
		$this->headersColsDataEx = null;
		$this->headersColsData3 = null;
		$this->listLigneEnteteColonne = null;
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$this->SetFont('helvetica', '', 8);
		$this->SetFillColor( 255, 255, 255 );

		//$this->$paddingX = 1.7;
		//$this->$paddingY = 1.7;

		$this->SetCreator(PDF_CREATOR);
		$this->SetAuthor('AnimoEtc');
		$this->SetTitle( "" );

		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$this->SetMargins(PDF_MARGIN_LEFT, 13, PDF_MARGIN_RIGHT);
		$this->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->SetFooterMargin(PDF_MARGIN_FOOTER);

		$this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		$this->largeur = $this->getPageDimensions()["wk"]-(PDF_MARGIN_RIGHT*2);
		$this->alterneBG = false;
		$this->alterneBGcurrent = false;
		$this->countLine = 0;
		
		$l = Array();
		// PAGE META DESCRIPTORS --------------------------------------
		$l['a_meta_charset'] = 'UTF-8';
		$l['a_meta_dir'] = 'ltr';
		$l['a_meta_language'] = 'fr';
		// TRANSLATIONS --------------------------------------
		$l['w_page'] = 'page';
		$this->setLanguageArray($l);

		$this->cellPaddingHeadCol = array ('T' => 0,'R' => 1.000125,'B' => 0,'L' => 1.000125);

		$this->original_tMargin = $this->tMargin;
		$this->AddPage();
		//$this->newPage = false;
	}

	public function AddPage($orientation = "", $format = "", $keepmargins = false, $tocpage = false ){
		parent::AddPage($orientation, $format, $keepmargins, $tocpage);
		$this->newPage = true;
	}

	
	public function msg_error( $t ){
		$this->SetFont('helvetica', 'B', 10);
		$this->Ln();
		$this->MultiCell(0,0," <!> <!> ". $t." <!> <!> ",0,"L",false,1);
		$this->Ln();
	}
	

	public function printEntetes(){
		$this->setCellPaddings( $this->cellPaddingHeadCol["L"], $this->cellPaddingHeadCol["T"] ,
				$this->cellPaddingHeadCol["R"], $this->cellPaddingHeadCol["B"] );
		
		//Si asser de place, sinon ajouter une page et skip (pour ne pas afficher deux fois)
		if ( $this->moncheckPageBreak( sizeof($this->listLigneEnteteColonne) * 10, "", false) ){
			$this->AddPage();
			return;
		}
		
		
		$this->SetFont('helvetica', 'B', 8);
		for($l=0; $l < sizeof($this->listLigneEnteteColonne); $l++){
			$listEnteteColonne = $this->listLigneEnteteColonne[$l];
	
			for($c=0; $c < sizeof($listEnteteColonne); $c++){
				$infoCol = $listEnteteColonne[$c];
	
				//si top
				$border = "";
	
				if ( $l == 0 ){
					$border .= "T";
				}
				if ( $l+1 == sizeof($this->listLigneEnteteColonne) ){
					$border .= "B";
				}
				if ( $c == 0 ){
					$border .= "L";
				}
				if ( $c+1 == sizeof($listEnteteColonne) ){
					$border .= "R";
				}
	
				if ( $infoCol["addborder"] ){
					$border .= $infoCol["addborder"];
				}
				if ( $infoCol["border"] ){
					$border = $infoCol["border"];
				}
					
				$this->SetLineStyle( array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)) );
	
				$width  = $infoCol["width"] ? $infoCol["width"] : 10;
				$align = $infoCol["align"] ? $infoCol["align"] : "L";
					
				$ln = ($c+1 == sizeof($listEnteteColonne)) ? 1 : 0;
	
				$this->Cell($width, 0, $infoCol["prefixe"] . $infoCol["text"] . $infoCol["suffixe"], $border, $ln, $align, false, "", 0, false, "T", "T");
			}
		}
		
		
	
	}


	public function debutSection3($titre,$lignesGras,$listLigneEnteteColonne,$skipPrintEntete=false){
		$this->headersColsData3 = ["","",$listLigneEnteteColonne];
		$this->listLigneEnteteColonne = $listLigneEnteteColonne;

		$this->setCellPaddings( $this->cellPaddingHeadCol["L"], $this->cellPaddingHeadCol["T"] ,
				$this->cellPaddingHeadCol["R"], $this->cellPaddingHeadCol["B"] );

		if($titre != ""){
			//Titre
			$this->SetFont('helvetica', 'B', 16);
			$this->Cell(0,0,$titre,0,1,"C");
			$this->Ln(5);
		}

		if(is_array($lignesGras)){
			//Lignes gras
			$this->SetFont('helvetica', 'B', 10);
			foreach ( $lignesGras as $uneLigne ){
				if (sizeof($uneLigne) >= 2){
					$this->Cell(40,0,$uneLigne[0],0,0,"L",false,"",0,false,"T","T");
					$this->MultiCell(0,0,$uneLigne[1],0,"L",false,1);
				}
			}
			$this->Ln(2);
		}

		//$this->infoCols = $infoCols;
		//$this->nbCol = sizeof($infoCols);
			
		if ( !$skipPrintEntete ) $this->printEntetes();

		$this->SetFont('helvetica', '', 8);
	}

	public function debutSectionEx($titre,$lignesGras,$listLigneEnteteColonne,$infoCols){
		//var_export($listLigneEnteteColonne);die();
		
		$this->headersColsDataEx = ["","",$listLigneEnteteColonne,$infoCols];

		$this->setCellPaddings( $this->cellPaddingHeadCol["L"], $this->cellPaddingHeadCol["T"] ,
				$this->cellPaddingHeadCol["R"], $this->cellPaddingHeadCol["B"] );

		if($titre != ""){
			//Titre
			$this->SetFont('helvetica', 'B', 16);
			$this->Cell(0,0,$titre,0,1,"C");
			$this->Ln(5);
		}

		if(is_array($lignesGras)){
			//Lignes gras
			$this->SetFont('helvetica', 'B', 10);
			foreach ( $lignesGras as $uneLigne ){
				if (sizeof($uneLigne) >= 2){
					$this->Cell(40,0,$uneLigne[0],0,0,"L",false,"",0,false,"T","T");
					$this->MultiCell(0,0,$uneLigne[1],0,"L",false,1);
				}
			}
			$this->Ln(2);
		}

		$this->infoCols = $infoCols;
		$this->nbCol = sizeof($infoCols);
			
		$this->printEntetes();

		$this->SetFont('helvetica', '', 8);
	}

	public function debutSection($titre,$lignesGras,$listEnteteColonne,$infoCols){
		$this->headersColsData = ["","",$listLigneEnteteColonne,$infoCols];
		$this->setCellPaddings( $this->cellPaddingHeadCol["L"], $this->cellPaddingHeadCol["T"] , $this->cellPaddingHeadCol["R"], $this->cellPaddingHeadCol["B"] );

		if($titre != ""){
			//Titre
			$this->SetFont('helvetica', 'B', 16);
			$this->Cell(0,0,$titre,0,1,"C");
			$this->Ln(5);
		}

		if(is_array($lignesGras)){
			//Lignes gras
			$this->SetFont('helvetica', 'B', 10);
			foreach ( $lignesGras as $uneLigne ){
				if (sizeof($uneLigne) >= 2){
					$this->Cell(40,0,$uneLigne[0],0,0,"L",false,"",0,false,"T","T");
					$this->MultiCell(0,0,$uneLigne[1],0,"L",false,1);
				}
			}
			$this->Ln(2);
		}

		if(is_array($listEnteteColonne)){
			$this->SetFont('helvetica', 'B', 8);
			$this->nbCol = sizeof($listEnteteColonne);
			$this->infoCols = $infoCols;
			if ( $this->nbCol > 0 ){
					
				for($i=0; $i < $this->nbCol; $i++){
						
					$border = ($i+1 == $this->nbCol) ? "LTBR" : "LTB";
						
					$this->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
					$width  = $this->infoCols[$i]["width"] ? $this->infoCols[$i]["width"] : 10;
					$align = $this->infoCols[$i]["align"] ? $this->infoCols[$i]["align"] : "L";
						
					$ln = $i+1 == $this->nbCol ? 1 : 0;
						
					$this->Cell($width, 0, $listEnteteColonne[$i], $border, $ln, $align, false, "", 0, false, "T", "T");
				}
			}
		}

		$this->SetFont('helvetica', '', 8);
	}

	public function substringTofitWidth( $text, $widthMax ){
		if ( $text == "" ){return "";}
		
		$isOk = false;

		$celPaddings = $this->getCellPaddings();
		$widthMax = $widthMax - $celPaddings["L"] - $celPaddings["R"];

		$w = $this->GetStringWidth($text);
		if ( $w <= $widthMax ){
			//vex( [$text,$w , $widthMax] );
			return $text;
		}
		$troispoint_t = "[...]";
		$troispoint_w = $this->GetStringWidth($troispoint_t);
		$failsave = 0;

		while ( $w + $troispoint_w > $widthMax and $failsave < 1000 ){
			$text = mb_substr($text,0,mb_strlen($text)-1);
				
			$w = $this->GetStringWidth($text);
			$failsave++;
		}
		
		return $text . $troispoint_t;
	}

	public function setInfoCols( $iLigne=-1 ){
		if ( $iLigne < 0 ){
			$this->infoCols = $this->listLigneEnteteColonne[sizeof($this->listLigneEnteteColonne)+$iLigne];
		} else {
			$this->infoCols = $this->listLigneEnteteColonne[$iLigne];
		}
		$this->nbCol = sizeof($this->infoCols);
	}

	public function writeLigneRapport3wrap($listChamps, $iLigne=-1, $skipDefaultFont = false, $nbMaxLine=null){
		$this->setInfoCols($iLigne);
		$this->writeLigneRapportWrap($listChamps, $skipDefaultFont, $nbMaxLine);
	}
	public function writeLigneRapport3($listChamps, $iLigne=-1, $skipDefaultFont = false, $hideTooLongCell=false){
		if ( $iLigne < 0 ){
			$this->infoCols = $this->listLigneEnteteColonne[sizeof($this->listLigneEnteteColonne)+$iLigne];
		} else {
			$this->infoCols = $this->listLigneEnteteColonne[$iLigne];
		}

		$this->nbCol = sizeof($this->infoCols);
		$this->writeLigneRapport($listChamps, $skipDefaultFont, $hideTooLongCell);
	}



	public function writeLigneRapport($listChamps, $skipDefaultFont=false, $hideTooLongCell=false, $listStyleCell=[] ){
		if(!$skipDefaultFont) $this->SetFont('helvetica', '', 8);

		for($i=0; $i < $this->nbCol; $i++){
			$width  = $this->infoCols[$i]["width"] ? $this->infoCols[$i]["width"] : 10;
			$align = $this->infoCols[$i]["align"] ? $this->infoCols[$i]["align"] : "L";
			
			$ln = $i+1 == $this->nbCol ? 1 : 0;
			
			$border = "";
			if ( isset($listStyleCell[$i]) and isset($listStyleCell[$i]["border"]) ){
				$border = $listStyleCell[$i]["border"];
			}
				
			if ( isset($listChamps[$i]) ){
				$t = $this->infoCols[$i]["prefixe"] . $listChamps[$i] . $this->infoCols[$i]["suffixe"];
				
				if ( $hideTooLongCell ){
					$t = $this->substringTofitWidth($t,$width);
				}
				
				$this->Cell($width, 0, $t, $border, $ln, $align, false, "", 0, false, "T", "T");
			} else {
				$this->Cell($width, 0, "", $border, $ln, $align, false, "", 0, false, "T", "T");
			}
		}
	}
	public function writeLigneRapportWrap($listChamps, $skipDefaultFont = false, $nbMaxLine=null){
		if(!$skipDefaultFont) $this->SetFont('helvetica', '', 8);
		
		if ( empty($this->infoCols) ){
			$this->setInfoCols();
		}
		// fucking MultiCell est positionné de façon absolute avec un X/Y donc il faut tout calculer

		// start x et start y
		$x = $this->getX();
		$y = $this->getY();

		// pour calculer la hauteur par défaut de la multicell pour une ligne simuler la création de cell pour un petit texte et la largeur de la col
		// aller chercher le champ le plus haut
		$maxheight = 0;
		foreach($listChamps as $champ){
		    $height = $this->getStringHeight(189,$champ);
		    if($height > $maxheight){
		        $maxheight = $height;
		    }
		}
		$h_min = $maxheight;

		$start_x = $x;
		$start_y = $y;

		$h_max = 0;

		// déterminer la hauteur maximum d'une ligne
		for($i=0; $i < $this->nbCol; $i++){

			$width  = $this->infoCols[$i]["width"] ? $this->infoCols[$i]["width"] : 10;

			if($h_max < $this->getStringHeight($width,$listChamps[$i],false,false,0.8)){
				$h_max = $this->getStringHeight($width,$listChamps[$i],false,false,0.8);
			}
		}


		if ( $nbMaxLine ){
			$listTemp = [];
			for( $i=0; $i < $nbMaxLine; $i++ ){
				$listTemp[] = "dummy";
			}
				
			$h_nbMaxLine = $this->getStringHeight(189, implode("\n",$listTemp));
			if ( $h_max > $h_nbMaxLine ){
				$h_max = $h_nbMaxLine;
			}
		}



		// si nécessite un page break recalculer le x/y
		if($this->checkPageBreak($h_max, $y, true)){
			$x = $this->getX();
			$y = $this->getY();
			$start_x = $x;
			$start_y = $y;
		}

		/*
		 // si la hauteur minimum est >= que la hauteur max ajuster le line-height en conséquence
		 if($h_min >= $h_max){
		 $h_max += 0.4;
		 }else{
		 $h_max -= 0.1;
		 }*/

		$this->countLine++;
		$filled = false;
		if ( $this->alterneBG ){
			$this->alterneBGcurrent = !$this->alterneBGcurrent;
			$filled = $this->alterneBGcurrent;
		}
		
		
		$width = 0;
		for($i=0; $i < $this->nbCol; $i++){
			$ln = 0;
			$width = $this->infoCols[$i]["width"] ? $this->infoCols[$i]["width"] : 10;
			$align = $this->infoCols[$i]["align"] ? $this->infoCols[$i]["align"] : "L";
				
			$x = $this->getX();
			$y = $this->getY();
				
			if ( isset($listChamps[$i]) ){
				$this->MultiCell($width, $h_min, $listChamps[$i],"", $align, $filled, $ln,$x,$y,true,0,false,true,$h_max + .4,"T",false);
			} else {
				$this->MultiCell($width, $h_min, "",             "", $align, $filled, $ln,$x,$y,true,0,false,true,$h_max + .4,"T",false);
			}
			// déplacer le curseur
			$this->setXY($x + $width,$y);
		}

		// réinitialiser les valeurs modifiées
		$this->setXY($start_x,$start_y + $h_max);

		//
	}
	public function writeLigneGrandTotal($listChamps, $topBorders=null, $isBold=true, $convertFnctOnTopBordersLabel=null, $doubleLine=false){
		$this->SetFont('helvetica', 'B', 9);
		$this->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		
		if ( $doubleLine ){
			$this->moncheckPageBreak(9);
		}
		for($i=0; $i < $this->nbCol; $i++){
			$t = $listChamps[$i];
			if ( $doubleLine and $i % 2 == 1 ){
				$t = "";
			}
			
			$width  = $this->infoCols[$i]["width"] ? $this->infoCols[$i]["width"] : 10;
			$align = $this->infoCols[$i]["align"] ? $this->infoCols[$i]["align"] : "L";

			$ln = $i+1 == $this->nbCol ? 1 : 0;
				
			if ( isset($listChamps[$i]) ){
				if ( $convertFnctOnTopBordersLabel and sizeof($topBorders) > $i and $topBorders[$i] and (!$doubleLine or $i % 2 == 0) ){
					$t = $convertFnctOnTopBordersLabel($t);
				}
				
				if ( !$topBorders or (sizeof($topBorders) > $i and $topBorders[$i]) ){
					$this->Cell($width, 0, $t, "T", $ln, $align, false, "", 0, false, "T", "T");
				} else {
					$this->Cell($width, 0, $t, "", $ln, $align, false, "", 0, false, "T", "T");
				}
			} else {
				$this->Cell($width, 0, $t, "", $ln, $align, false, "", 0, false, "T", "T");
			}
		}
		
		if ( $doubleLine ){
			for($i=0; $i < $this->nbCol; $i++){
				$t = $listChamps[$i];
				if ( $doubleLine and $i % 2 == 0 ){
					$t = "";
				}
				
				$width  = $this->infoCols[$i]["width"] ? $this->infoCols[$i]["width"] : 10;
				$align = $this->infoCols[$i]["align"] ? $this->infoCols[$i]["align"] : "L";
				
				$ln = $i+1 == $this->nbCol ? 1 : 0;
				
				if ( isset($listChamps[$i]) ){
					if ( $convertFnctOnTopBordersLabel and sizeof($topBorders) > $i and $topBorders[$i] and $i % 2 == 1 ){
						$t = $convertFnctOnTopBordersLabel($t);
					}
				
					if ( !$topBorders or (sizeof($topBorders) > $i and $topBorders[$i]) ){
						$this->Cell($width, 0, $t, "", $ln, $align, false, "", 0, false, "T", "T");
					} else {
						$this->Cell($width, 0, $t, "", $ln, $align, false, "", 0, false, "T", "T");
					}
				} else {
					$this->Cell($width, 0, $t, "", $ln, $align, false, "", 0, false, "T", "T");
				}
			}
		}
	}
	
	public function writeLigneTotaux($listChamps, $topBorders=null, $isBold=true, $convertFnctOnTopBordersLabel=null){
		$this->SetFont('helvetica', ($isBold?"B":""), 8);
		
		for($i=0; $i < $this->nbCol; $i++){
			$width  = $this->infoCols[$i]["width"] ? $this->infoCols[$i]["width"] : 10;
			$align = $this->infoCols[$i]["align"] ? $this->infoCols[$i]["align"] : "L";

			$ln = $i+1 == $this->nbCol ? 1 : 0;
				
			$this->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
				
			
			if ( isset($listChamps[$i]) ){
				if ( $convertFnctOnTopBordersLabel and sizeof($topBorders) > $i and $topBorders[$i] ){
					$listChamps[$i] = $convertFnctOnTopBordersLabel($listChamps[$i]);
				}

				$border = "";
				if ( !$topBorders or (sizeof($topBorders) > $i and $topBorders[$i]) ){
					$border = "T";
				}
				$this->Cell($width, 0, $listChamps[$i], $border, $ln, $align, 0, "", 0, false, "T", "T");
			} else {
				$this->Cell($width, 0, "", "", $ln, $align, false, "", 0, false, "T", "T");
			}
		}
		
	}

	public function moncheckPageBreak($h=0,$y='',$addPage=true){
		return $this->checkPageBreak($h,$y,$addPage);
	}

	public function Header() {
		$this->SetFont('helvetica', '', 8);
		$this->Cell(0, 10, $this->uneLigneMag["M_NOM"]);
		$this->Ln();


		if ( $this->newPage ){
			if ( $this->hasHeadersColsAllPages ){
				$this->printEntetes();
				$this->tMargin = $this->GetY();
			}
			/*
			if ( $this->hasHeadersColsAllPages and $this->headersColsData ){
				call_user_func_array( [$this,"debutSection"], $this->headersColsData );
				$this->tMargin = $this->GetY();
			} elseif ( $this->hasHeadersColsAllPages and $this->headersColsDataEx ){
				call_user_func_array( [$this,"debutSectionEx"], $this->headersColsDataEx );
				$this->tMargin = $this->GetY();
			} elseif ( $this->hasHeadersColsAllPages and $this->headersColsData3 ){
				call_user_func_array( [$this,"debutSection3"], $this->headersColsData3 );
				$this->tMargin = $this->GetY();
			} elseif ( $this->hasHeadersColsAllPages and $this->listLigneEnteteColonne ) {
				$this->printEntetes();
				$this->tMargin = $this->GetY();
			}
				*/
			$this->newPage = false;
		}
			
	}

	public function Footer() {
		$this->SetFont('helvetica', '', 8);
		
		$this->Cell( $this->largeur*0.33, 0, "Animo etc - v" . VERSION_STR . "." . VERSION, "", 0, "L", false, "", 0, false, "T", "T");
		$this->Cell( $this->largeur*0.33, 0, strftime("%d/%m/%Y %H:%M"), "", 0, "C", false, "", 0, false, "T", "T");
		$this->Cell( $this->largeur*0.33, 0, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), "", 0, "R", false, "", 0, false, "T", "T");
	}
	
	public function addListAncreCols($_){
		return;
	}
	
	public function setAlterneBG($tf){
		$this->alterneBG = $tf;
		$this->alterneBGcurrent = true;
	}
}

?>