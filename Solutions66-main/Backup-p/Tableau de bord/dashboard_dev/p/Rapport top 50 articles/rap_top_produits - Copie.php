<?php

unset($_SESSION["product_search"]);
ini_set("memory_limit","256M");
set_time_limit(300);

$listSoustitre = [];

// Get nom fournisseur
function getFourDistName($id){
	global $dbAnimoCaisse;
	try{
		$getName = query('SELECT nom FROM fournisseur WHERE id_fournisseur = ?',[$id],$dbAnimoCaisse);
		if($getName->num_rows === 1){
			return $getName->fetch_row()[0];
		}
	}catch(Exception $e){}
	return "(n/d)";
}
// Get nom departement
function getDepName($id){
	global $dbAnimoCaisse;
	try{
		$getName = query('SELECT nom FROM departement WHERE id_departement = ?',[$id],$dbAnimoCaisse);
		if($getName->num_rows === 1){
			return $getName->fetch_row()[0];
		}
	}catch(Exception $e){}
	return "(n/d)";
}

//Stocke all magasins pour avoir acces aux connecteurs des caisses, noms des magasin et les IDs.
$allMag = [];

$queryAllMag = query("select * from MAGASIN where caisse_db is not null order by M_NOM asc",[],$mysqli);
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}

$listID_MAGASINcanAccess = [];

if ( $_SESSION["utilisateur"]["security"] >= 2 ){
	// acces franchisés: il a le droit au magasins qu'ils ont
	$listID_MAGASINcanAccess = array_keys($_SESSION["magasins"]);
} else {
	// acces tous les magasins: Solutions66 staff  + Animo staff
	$listID_MAGASINcanAccess = array_keys($allMag);
}

// Stock les IDs des magasins selectionnés
$listID_MAGASIN = [];

// SELECT des magasins: verifie si les magasins selectionnés match avec celles que l'utilisateur a le droit d'acces. 
if ( is_array($_GET["ID_MAGASIN"]) ){
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) {
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanAccess) ){
			$listID_MAGASIN[] = $ID_MAGASIN;
		}
	}
}

//Si pas de selection, $listID_MAGASIN recoit par default le magasin selon le  droit
if ( sizeof($listID_MAGASIN) < 1 ){
	$listID_MAGASIN = $listID_MAGASINcanAccess;
}

//Case à cocher
if($_GET["getTotalProduit"] == "1" ){
	//Set le case à cocher à "cheked"
	$_GET["magasin_groupe"] = "1";
	//Clean la list pour stocker toutes les magasins
	$listID_MAGASIN = [];
	$queryAllMag = query("select * from MAGASIN where caisse_db is not null AND RESERVED_DEV = 0",[],$mysqli);
	while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
		//En créé une nouvelle liste e iDs
		$listID_MAGASIN[] = $uneLigneMag["ID_MAGASIN"];
	}
}

$limit = 50;
if(preg_match('#^\d+$#',$_REQUEST['limit'])){
	$limit = intval($_REQUEST['limit']);
}

$listAND = [];
$listPARAM = [];

if( !empty($_REQUEST['upcSearch'])){
	$listPARAM[] = '%'.$_REQUEST['upcSearch'].'%';
	$listPARAM[] = '%'.$_REQUEST['upcSearch'].'%';
	$listPARAM[] = '%'.$_REQUEST['upcSearch'].'%';
	$listPARAM[] = '%'.$_REQUEST['upcSearch'].'%';
	$listAND[] = '(article.PLU like ? or article.boite_PLU like ? or article.PLU2 like ? or article.PLU3 like ?)';
	$listSoustitre[] = [L('upc','o'),$_REQUEST['upcSearch']];
}
if( !empty($_REQUEST['keywrd'])){
	$listPARAM[] = '%'.$_REQUEST['keywrd'].'%';
	$listPARAM[] = '%'.$_REQUEST['keywrd'].'%';
	$listAND[] = '(article.desc_fr like ?
				or article.desc_en like ?)';
	$listSoustitre[] = [L('mots clés','o'),$_REQUEST['keywrd']];
}
if( !empty( $_REQUEST['foursearch'])){
	$foursearch = preg_replace('/[^\da-z]/i', '',$_REQUEST['foursearch']);
	$listPARAM[] = '%'.$foursearch.'%';
	$listPARAM[] = '%'.$foursearch.'%';
	$listPARAM[] = '%'.$foursearch.'%';
	$listAND[] = "(REPLACE(laf.num_four, '-','') like ? OR REPLACE(laf.num_four, '/','') like ? OR	REPLACE(laf.num_four, '_','') like ?)";
	$listSoustitre[] = [L('code four.','o'),$foursearch];
}
if(!empty($_REQUEST['fournisseur'])){
	$listPARAM[] = $_REQUEST['fournisseur'];
	$listAND[] = 'article.id_distributeur = ?';
	$listSoustitre[] = [L('fournisseur','o'),getFourDistName($_REQUEST['fournisseur'])];
}
if(!empty($_REQUEST['distributeur'])){
	$listPARAM[] = $_REQUEST['distributeur'];
	$listAND[] = 'laf.id_fournisseur = ?';
	$listSoustitre[] = [L('distributeur','o'),getFourDistName($_REQUEST['distributeur'])];
}
if(!empty($_REQUEST['id_departement'])){
	$listPARAM[] = $_REQUEST['id_departement'];
	$listAND[] = 'article.id_departement = ?';
	$listSoustitre[] = [L('département','o'),getDepName($_REQUEST['distributeur'])];
}

// dummy query
$listAND[] = " article.id_article IS NOT NULL ";
$listAND[] = " type = 'PLU'";

$from = date("Y-m-d",strtotime('-30 day'));
$to = date("Y-m-d");

if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) ){
	$listAND[] = '(facture_item.date_insert >= ? AND facture_item.date_insert <= ?) ';
	$from = $_GET['from'];
	$to = $_GET['to'];
	$listPARAM[] = $from . ' 00:00:00';
	$listPARAM[] = $to . ' 23:59:59';

} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) ){
	$from = $_GET['from'];
	$to = "";
	$listAND[] = '(facture_item.date_insert >= ? ) ';
	$listPARAM[] = $from . ' 00:00:00';
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) ){
	$from = "";
	$to = $_GET['to'];
	$listAND[] = '( facture_item.date_insert <= ? ) ';
	$listPARAM[] = $to . ' 23:59:59';
} else {
	//Failsafe
	$listAND[] = '(facture_item.date_insert >= ? AND facture_item.date_insert <= ?) ';
	$listPARAM[] = $from . ' 00:00:00';
	$listPARAM[] = $to . ' 23:59:59';
}

if(count($listAND) < 1){
	$listAND[] = '1=1';
}

$caisse_db = 'animoetc_caisse_dummy';
$arrayData = [];
$arrayData_group = [];
$and = implode(' and ',$listAND); 

$listID_ARTICLE_REFUSED = [];
$cached_row_article = [];

// =======================loop tous les magasins ========================
if(count($listID_MAGASIN) > 0){
    
	foreach($listID_MAGASIN as $ID_MAGASIN){
	    
		$arrayData[$ID_MAGASIN] = [];
		$caisse_db = $allMag[$ID_MAGASIN]['caisse_db'];
		// créer une copie du connecteur de la caisse
		$dbAnimoUneCaisse = $dbAnimoCaisse;
		$dbAnimoUneCaisse->select_db($caisse_db);
	
		$resulRaportPLU = query("SELECT sum(facture_item.nb) as nbArticle, article.id_article, article.PLU, article.PLU2, article.PLU3,
								facture_item.date_insert, sum(facture_item.montant) as totalPrix, article.desc_fr, article.stock
							   FROM facture_item
							   JOIN article using(id_article)
							   join (
    									select f2.*
    									from link_article_four `f2`
    									where f2.discontinued IS NULL
    									group by f2.id_article
    							     )  as laf on (laf.id_article = facture_item.id_article)
							  WHERE $and group by facture_item.id_article ORDER BY nbArticle DESC, facture_item.date_insert desc, facture_item.ordre desc ". ( $_GET["magasin_groupe"] != "" ? "" : "LIMIT $limit") ,
					$listPARAM,$dbAnimoUneCaisse);
		if($resulRaportPLU->num_rows > 0){
			
			$arrayData[$ID_MAGASIN] = [
										'totalArticles'=>0,
										'totalMontant'=>0,
										'data'=>[],
									  ];

			while($rowRaportPLU = $resulRaportPLU->fetch_assoc()){
				//$rowRaportPLU['totalPrix'] = $rowRaportPLU['prix'] * $rowRaportPLU['nbArticle'];
				$rowRaportPLU['prix'] = $rowRaportPLU['nbArticle'] != 0 ? ($rowRaportPLU['totalPrix'] / $rowRaportPLU['nbArticle']) : 0;

				//aller chercher le distributeur
				$getDist = query('select fournisseur.nom,link_article_four.num_four 
									from fournisseur
									join link_article_four using(id_fournisseur)
									where est_fournisseur = 1 and link_article_four.discontinued IS NULL and link_article_four.num_four is not NULL and id_article = ?
									order by prix_coutant asc
									',[$rowRaportPLU['id_article']],$dbAnimoCaisse);
				$rowRaportPLU['dists'] = [];
				
				if($getDist->num_rows > 0){
					while($rowDist = $getDist->fetch_assoc()){
						$rowRaportPLU['dists'][] = $rowDist;
					}
				}
				
				/////////////////////////// DONNÉS PAS GROUPÉS/////////////////////////////////////////
				$arrayData[$ID_MAGASIN]['data'][] = $rowRaportPLU;
				$arrayData[$ID_MAGASIN]['totalArticles'] += intval($rowRaportPLU['nbArticle']);
				$arrayData[$ID_MAGASIN]['totalMontant']  += floatval($rowRaportPLU['totalPrix']);
				$arrayData[$ID_MAGASIN]['totalStock']    += floatval( $rowRaportPLU['stock'] );
				
				/////////////////////////// DONNÉS  GROUPÉS/////////////////////////////////////////
				$arrayData_group["articles"][$rowRaportPLU["id_article"]]['nbArticle'] += intval( $rowRaportPLU['nbArticle'] );
				$arrayData_group["articles"][$rowRaportPLU["id_article"]]['totalPrix'] += floatval( $rowRaportPLU['totalPrix'] );
				$arrayData_group["articles"][$rowRaportPLU["id_article"]]['desc_fr'] = $rowRaportPLU['desc_fr'];
				$arrayData_group["articles"][$rowRaportPLU["id_article"]]['prix'] = $rowRaportPLU['prix'];
				$arrayData_group["articles"][$rowRaportPLU["id_article"]]['stock'] += $rowRaportPLU['stock'];
				$arrayData_group["articles"][$rowRaportPLU["id_article"]]['data'] = $rowRaportPLU;
				
				$arrayData_group['totalArticles'] += intval( $rowRaportPLU['nbArticle'] );
				$arrayData_group['totalMontant']  += floatval( $rowRaportPLU['totalPrix'] );
				$arrayData_group['totalStock']    += floatval( $rowRaportPLU['stock'] );
			}//fin while
		}
		// garbage collection            JLT:lol
		unset($dbAnimoUneCaisse);
	}
}

// ======================   GESTION TRIAGE TABLEAU  =================================
$listTriPosible = ["nbArticle","desc_fr","stock","prix","totalPrix"];

if ( !in_array($_GET["orderby"],$listTriPosible) ){
	//Set by default
	$_GET["orderby"] = $listTriPosible[0];
}
if ( $_GET["sens"] == 'asc' ){
	$_GET["sens"] = "asc";
} else {
	$_GET["sens"] = "desc";
}

//Tri  Groupé
if( $_GET["magasin_groupe"] != "" ||  $_GET["getTotalProduit"] != ""){  
		uasort( $arrayData_group["articles"], function($a,$b){
			if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
				return ($_GET["sens"] == "desc") ? 1 : -1;//1 c plus petit, -1 plus grand 
			} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
				return ($_GET["sens"] == "desc") ? -1 : 1;
			}
			return 0;
		});
}else{   
	//Tri SANS Groupe
	foreach($arrayData as $ID_MAGASIN => $data){
		if(count($data['data']) > 0){
			 usort( $arrayData[$ID_MAGASIN]["data"], function($a,$b){
				if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
					return ($_GET["sens"] == "desc") ? 1 : -1;//1 c plus petit, -1 plus grand 
				} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
					return ($_GET["sens"] == "desc") ? -1 : 1;
				}
				return 0;
			});   
		}
	}   
}
//echo '<pre>' , print_r( 	$arrayData_group["articles"] ) , '</pre>';
// ================================== ZONE PDF & EXCEL ==========================================
if ( $_GET["getFile"] == "1"  ){
	
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}

	$titre = L("rap_top_produits","o")." - $limit articles";
	//$listSoustitre[] = ["En date du", date("Y-m-d") ];
	$listNomMag = [];

	if ( !empty($from) and !empty($to) ){
		$listSoustitre[] = ["Date", L("du") . " " . formatDateUTF8nonHTML( $from ) . " " . L("au") . " " . formatDateUTF8nonHTML( $to ) ];
	} elseif( !empty($from) ) {
		$listSoustitre[] = ["Date", L("du") . " " . formatDateUTF8nonHTML( $from ) ];
	} elseif( !empty($to) ) {
		$listSoustitre[] = ["Date", L("jusqu'au") . " " . formatDateUTF8nonHTML( $to ) ];
	} else {
		$listSoustitre[] = ["Date", "tous" ];
	}
	if( $_GET["magasin_groupe"] != "" ){
		$listSoustitre[] = [ "Paramètre(s)", L("magasin_groupe","o") ];
	}
	if(count($listID_MAGASIN) > 0){
		foreach( $listID_MAGASIN as $ID_MAGASIN ){
			$listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
		}
	}

	if($_GET["getTotalProduit"] == "1"){
		$listSoustitre[] = ["Magasin(s)", "Tous les magasins" ];
	}else{
		$listSoustitre[] = ["Magasin(s)", implode(", ",$listNomMag) ];
	}

	$listEnteteColonne = [
		[
			["text"=>L('Article',"o"),"width"=>70,"align"=>"L"],
			['text'=>'Distributeurs(s)','width'=>35,'align'=>'L'],
			["text"=>L('UPC',"o"),"width"=>25,"align"=>"C"],
			["text"=>L('Nb. V.',"o"),"width"=>12,"align"=>"R"],
			["text"=>L('stock',"o"),"width"=>12,"align"=>"R"],
			["text"=>L('Prix',"o"),"width"=>18,"align"=>"R"],
			["text"=>L('Total',"o"),"width"=>18,"align"=>"R"]
		],
	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	$isfirst = true;
	$countIt = 0;
	
	if(count($arrayData) > 0){
		//======================= MODE  GROUPÉ ============================
		if( $_GET["magasin_groupe"] != "" ){
			
			$rapport->SetFont('helvetica', 'B', 9);
			$rapport->Cell(0, 0, L("succursales","o"), 0, 1, 'L', false, '', 0, false, 'T', 'B');
			
			if(count($arrayData_group) > 0){
				
				foreach ( $arrayData_group["articles"] as $id_arti => $rowArticle){
					
					if($countIt < $limit){
						// créer une string avec les PLU
						$listPLU = [];
						if(!empty($rowArticle['data']["PLU"])){
							$listPLU[] = $rowArticle['data']["PLU"];
						}
						if(!empty($rowArticle['data']["PLU2"])){
							$listPLU[] = $rowArticle['data']["PLU2"];
						}
						if(!empty($rowArticle['data']["PLU3"])){
							$listPLU[] = $rowArticle['data']["PLU3"];
						}
						$listChamps = [];
						$listChamps[] = $rowArticle['data']['desc_fr'];
						$arrayStrDist = [];
						if(count($rowArticle['data']['dists']) > 0){
							foreach($rowArticle['data']['dists'] as $rowDist){
								$arrayStrDist[] = $rowDist['nom'] . ': ' . $rowDist['num_four'];
							}
						}
						$listChamps[] = implode("\n",$arrayStrDist);
						//$listChamps[] = implode("\n",$listPLU);
						$listChamps[] = $rowArticle['data']["PLU"];
						$listChamps[] = $rowArticle['nbArticle'];
						$listChamps[] = $rowArticle['stock'];
						$listChamps[] = nfs($rowArticle['data']["prix"]);
						$listChamps[] = nfs($rowArticle["totalPrix"]);
						$rapport->writeLigneRapport3wrap( $listChamps );
						$countIt++;
					} else{
						break;
					}
				}
				//TOTAUX GROUPÉ
				$listTOTAL = [];
				$listTOTAL[] = "";
				$listTOTAL[] = "";
				$listTOTAL[] = "";
				$listTOTAL[] = $arrayData_group['totalArticles'];
				$listTOTAL[] = $arrayData_group['totalStock'];
				$listTOTAL[] = "";
				$listTOTAL[] = nfs($arrayData_group["totalMontant"]);
				$rapport->writeLigneGrandTotal($listTOTAL,[false,false,false,true,true,false,true]);
			}else{
				$listChamps = [];
				$listChamps[] = "Aucune donnée";
				$listChamps[] = "";
				$listChamps[] = "";
				$listChamps[] = "";
				$listChamps[] = "";
				$listChamps[] = "";
				$rapport->writeLigneRapport3wrap( $listChamps );
			}
		//======================= MODE PAS GROUPÉ ============================
		} else {
			foreach($arrayData as $ID_MAGASIN => $data){
				if (!$isfirst){
					$rapport->Ln(6);
				}
				$isfirst = false;
				$rapport->SetFont('helvetica', 'B', 9);
				
				if( sizeof($listID_MAGASINcanAccess) > 1 ){
					$rapport->Cell(0, 0, $allMag[$ID_MAGASIN]["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
				}
				if(count($data['data']) > 0){
					foreach ( $data['data'] as $rowRaportPLU){
						// créer une string avec les PLU
						$listPLU = [];
						if(!empty($rowRaportPLU["PLU"])){
							$listPLU[] = $rowRaportPLU["PLU"];
						}
						if(!empty($rowRaportPLU["PLU2"])){
							$listPLU[] = $rowRaportPLU["PLU2"];
						}
						if(!empty($rowRaportPLU["PLU3"])){
							$listPLU[] = $rowRaportPLU["PLU3"];
						}
						$listChamps = [];
						$listChamps[] = $rowRaportPLU['desc_fr'];
						$arrayStrDist = [];
						if(count($rowRaportPLU['dists']) > 0){
							foreach($rowRaportPLU['dists'] as $rowDist){
								$arrayStrDist[] = $rowDist['nom'] . ': ' . $rowDist['num_four'];
							}
						}
						$listChamps[] = implode("\n",$arrayStrDist);
						//$listChamps[] = implode("\n",$listPLU);
						$listChamps[] = $rowRaportPLU["PLU"];
						$listChamps[] = $rowRaportPLU['nbArticle'];
						$listChamps[] = $rowRaportPLU['stock'];
						$listChamps[] = nfs($rowRaportPLU["prix"]);
						$listChamps[] = nfs($rowRaportPLU["totalPrix"]);
						$rapport->writeLigneRapport3wrap( $listChamps );
		
					}
					$listTOTAL = [];
					$listTOTAL[] = "";
					$listTOTAL[] = "";
					$listTOTAL[] = "";
					$listTOTAL[] = nfsnd($data["totalArticles"]);
					$listTOTAL[] = nfsnd($data["totalStock"]);
					$listTOTAL[] = "";
					$listTOTAL[] = nfs($data["totalMontant"]);
		
					$rapport->writeLigneGrandTotal($listTOTAL,[false,false,false,true,true,false,true]);
		
				}else{
					$listChamps = [];
					$listChamps[] = "Aucune donnée";
					$listChamps[] = "";
					$listChamps[] = "";
					$listChamps[] = "";
					$listChamps[] = "";
					$listChamps[] = "";
					$rapport->writeLigneRapport3wrap( $listChamps );
				}
			}
		}
	}
	ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");
}


// ================================== ZONE HTML ==========================================
?>
<section id="main" class="main-wrap bgc-white-darkest print" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("rapport top $limit articles","o");?></span>
		</h1>
		<h1 id="date_label" class="page-title pull-right fs-4 fw-light print-only"></h1>
		<div class="smart-links no-print">
			<ul class="nav" role="tablist">
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" href="index.php?<?= rebuildQueryString(["format"=>"pdf","getFile"=>"1"]) ?>" target="_blank">
						<i class="fa fa-file-pdf-o "></i>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" href="index.php?<?= rebuildQueryString(["format"=>"xlsx","getFile"=>"1"]) ?>" target="_blank">
						<i class="fa fa-file-excel-o "></i>
					</a>
				</li>
				<?php /*?>
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" href="index.php?<?= rebuildQueryString(["format"=>"pdf","getFile"=>"1"]) ?>" target="_blank">
						<i class="fa fa-file-pdf-o "></i>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" href="index.php?<?= rebuildQueryString(["format"=>"xlsx","getFile"=>"1"]) ?>" target="_blank">
						<i class="fa fa-file-excel-o "></i>
					</a>
				</li><?php */?>
			</ul>
		</div>
	</div>
	<!-- End SubHeader-->
<!-- BEGIN PAGE CONTENT-->
	<div class="row pl-3 pr-3 mb-3 mt-3 print-top">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<!--Start Panel-->
			<div class="panel c-white-dark pb-0">
				<div class="panel-body">
					<div class="panel bgc-white-dark transition visible">
						<div class="panel-body panel-body-p">
							<div class="page-size-table">
								<div class="bootstrap-table">
									<div class="no-print">
										<form method="get" id="formListRapToilettage">
											<input type="hidden" name="p" value="<?= $_GET["p"]?>">
											<input type="hidden" name="sens" value="<?= $_GET["sens"] ?>">
											<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">
											<div class="row" style="margin-bottom:15px;">
												<div class="col-md-8">
													<div class="input-group bs-datepicker input-daterange picker-range">
														<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities($from)?>">
														<span class="input-group-addon px-3"><?= L("to"); ?></span>
														<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($to)?>">
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group input-group ">
														<input type="text" class="form-control" name="upcSearch" id="upcSearch" placeholder="<?php echo $L['upccode'];?>" value="<?php echo $_REQUEST['upcSearch']?>">
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group input-group">
														<input type="text" class="form-control" name="foursearch" id="foursearch" placeholder="<?php echo $L['foursearch'];?>" value="<?php echo $_REQUEST['foursearch']?>">
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group input-group">
														<input type="text" class="form-control" name="keywrd" id="keywrd" placeholder="<?php echo $L['keyword'];?>" value="<?php echo $_REQUEST['keywrd']?>">
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group input-group">
														<select name="fournisseur" class="form-control">
															<option value="">Par fournisseur</option>
															<?php
															$enonce = "SELECT * FROM fournisseur WHERE est_distributeur IS NOT NULL AND inactif is NULL order by fournisseur.nom";
															$resultFour = query($enonce,[],$dbAnimoCaisse);
															while($rowFour = $resultFour->fetch_assoc()){
																printf("<option value='%s'%s>%s</option>", $rowFour["id_fournisseur"], ($rowFour["id_fournisseur"] == $_REQUEST["fournisseur"] ? " selected" : ""), $rowFour["nom"]);
															}
															?>
														</select>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group input-group">
														<select name="distributeur" class="form-control">
															<option value="">Par distributeur</option>
															<?php
															$enonce = "SELECT * FROM fournisseur WHERE est_fournisseur IS NOT NULL order by fournisseur.nom";
															$resultFour = query($enonce,[],$dbAnimoCaisse);
															while($rowFour = $resultFour->fetch_assoc()){
																printf("<option value='%s'%s>%s</option>", $rowFour["id_fournisseur"], ($rowFour["id_fournisseur"] == $_REQUEST["distributeur"] ? " selected" : ""), $rowFour["nom"]);
															}
															?>
														</select>
													</div>
												</div>
												
												<div class="col-md-4">
													<div class="form-group input-group">
														<select name="id_departement" class="form-control">
															<option value=""><?= L("par département","o") ?></option>
															<?php
															$enonce = "SELECT * FROM departement where id_departement > 1 and id_departement < 900 order by nom";
															$resultDep = query($enonce,[],$dbAnimoCaisse);
															while($rowDep = $resultDep->fetch_assoc()){
																printf("<option value='%s'%s>%s</option>", $rowDep["id_departement"], ($rowDep["id_departement"] == $_REQUEST["id_departement"] ? " selected" : ""), $rowDep["nom"]);
															}
															?>
														</select>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group input-group">
														<input type="number" placeholder="xx" class="form-control" name="limit" value="<?= attrEncode($limit)?>" />
														<span class="input-group-addon">
															résultats
														</span>
													</div>
												</div>
												<?php if( count($listID_MAGASINcanAccess) > 1 ){ $isMultiMag = true;?>
													<div class="col-md-8">
														<div>
															<select class="ui fluid normal multi-selection select-dropdown form-control" name="ID_MAGASIN[]" multiple>
																<?php
																foreach( $listID_MAGASINcanAccess as $ID_MAGASIN){
																	if(!INDEV and $allMag[$ID_MAGASIN]["RESERVED_DEV"] == 1){
																			continue;
																	}
																	$infoMag = query("select * from MAGASIN where ID_MAGASIN = ?",[$ID_MAGASIN,],$mysqli)->fetch_assoc();
																	printf("<option value='%s'%s>%s</option>", $ID_MAGASIN,( in_array($ID_MAGASIN,$listID_MAGASIN)?" selected":""),$infoMag["M_NOM"]);
																}
																?>
															</select>
															<button class="btn btn-xs" onclick="$(this).closest('div').find('.multi-selection').selectDropdown('set selected', <?= str_replace('"', "'",json_encode(array_map(strval,$listID_MAGASINcanAccess))) ?> )" type="button"><?= L("tous sélectionner") ?></button>
															<button class="btn btn-xs" onclick="$(this).closest('div').find('.multi-selection').selectDropdown('clear')" type="button"><?= L("tous dé-sélectionner") ?></button>
														</div>
													</div>
												<?php } 
												if( count($listID_MAGASINcanAccess) > 1 ){
												?>
													<div class="col-md-12 col-lg-2">
														<div class="ui dynamic checkbox checked pt-1">
															<input type="checkbox" name="magasin_groupe" value="1" class="form-control" <?= ($_GET["magasin_groupe"]!="")?"checked":"" ?> />
															<label> <?= L("magasin_groupe","o") ?> </label>
														</div>
													</div>
													<?php
												} ?>
												<!--checkbox -->
												<div class="col-md-3">
													<div class="ui dynamic checkbox checked pt-1">
														<input type="checkbox" name="getTotalProduit" value="1" class="form-control" <?= ($_GET["getTotalProduit"]=="1")?"checked":"" ?> />
														<label>Afficher total de toutes les magasins pour tous les produits.</label>
													</div>
												</div>
												<div class="col-md-<?= $isMultiMag ? 12 : 8?> text-right">
													<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('afficher');?></button>
												</div>
											</div>
										</form>
									</div>
									<?php
									if(count($arrayData) > 0){
										// ________________________ MODE GROUPÉ ______________________
										if( $_GET["magasin_groupe"] != "" ){
										   ?>
											<div class="col-md-12 mb-5" id="<?= ( count($listID_MAGASIN) > 1 ) ? L("succursales","o") : L("succursale","o") ?>">
												<h3 class=""><?= ( count($listID_MAGASIN) > 1 ) ? L("succursales","o") : L("succursale","o") ?></h3>
												<div class="table-responsive">
													<table class="card-view-no-edit page-size-table table table-no-bordered table-condensed table-striped">
														<thead>
															<tr>
																<!-- ================================================================== ROWS ====================================================================================-->
																<th style="text-align:left">
																	<a href="index.php?<?= rebuildQueryString(['orderby'=>'nbArticle', 'sens'=>($_GET["orderby"] == 'nbArticle' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
																		<?= $L['NBproduitvendu'];?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nbArticle' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>
																</th>     																
																<th style="text-align:left">
																	<a href="index.php?<?= rebuildQueryString(['orderby'=>'desc_fr', 'sens'=>($_GET["orderby"] == 'desc_fr' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
																		<?= $L['item'];?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'desc_fr' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>
																</th>
																<th>
																	Distributeur(s)
																</th>
																<th>
																	UPCs
																</th>
																<th style="text-align:left">
																	<a href="index.php?<?= rebuildQueryString(['orderby'=>'stock', 'sens'=>($_GET["orderby"] == 'stock' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
																	   Stock <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'stock' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>
																</th>  																
																<th style="text-align:right">
																	<a href="index.php?<?= rebuildQueryString(['orderby'=>'prix', 'sens'=>($_GET["orderby"] == 'prix' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
																	   Prix unitaire <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'prix' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>
																</th> 
																<th style="text-align:right">
																	<a href="index.php?<?= rebuildQueryString(['orderby'=>'totalPrix', 'sens'=>($_GET["orderby"] == 'totalPrix' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
																	   Total <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalPrix' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>
																</th> 
																<!-- ================================================================== and ROWS ====================================================================================-->
															</tr>
														</thead>
														<tbody>
															<?php
															if(count($arrayData_group) > 0){ 
																$countIt = 0;
																foreach ( $arrayData_group["articles"] as $id_arti => $rowArticle){
																	if($countIt < $limit){
																		?>
																		
																		<tr>
																			<!-- ========================================= ROWS =================================================== -->
																			<td><?= $rowArticle["nbArticle"] ?></td>
																			<td class='gauche'><a href="?p=produits&id=<?= urlencode($id_arti)?>"><?php echo $rowArticle['data']['desc_fr'];?></a></td>
																			<td>
																				<?php 
																				if(count($rowArticle['data']['dists']) > 0){
																					foreach($rowArticle['data']['dists'] as $rowDist){
																						?>
																						<div><?= $rowDist['nom']?>: <?= $rowDist['num_four']?></div>
																						<?php
																					}
																				}
																				?>
																			</td>
																			<td>
																				<a href="?p=produits&upc=<?= $rowArticle['data']["PLU"]?>"><?= $rowArticle['data']["PLU"]?></a>
																				<?= ($rowArticle['data']["PLU2"] != "" ? ",<a href='?p=produits&upc=".$rowArticle['data']["PLU2"]."'>".$rowArticle['data']["PLU2"]."</a>":"")?>
																				<?= ($rowArticle['data']["PLU3"] != "" ? ",<a href='?p=produits&upc=".$rowArticle['data']["PLU3"]."'>".$rowArticle['data']["PLU3"]."</a>":"")?>
																			</td>
																			<td>
																				<?= $rowArticle["stock"] ?>
																			</td>
																			<td class="text-right nowrap">
																				<?= nfs($rowArticle['data']["prix"])?>
																			</td>
																			<td class="text-right nowrap">
																				<?= nfs($rowArticle["totalPrix"])?>
																			</td>
																			<!-- ========================================= fin ROWS =================================================== -->
																		</tr>
																		<?php
																		$countIt++;
																	} else{
																		break;
																	}
																}
															}else{ ?>
															<tr>
																<td colspan="5">
																	Aucune donnée
																</td>
															</tr>
															<?php }?>
														</tbody>
														<tfoot>
															<tr style="font-weight:bold">
																<td><?= $arrayData_group['totalArticles'] ?></td>
																<td></td>
																<td></td>
																<td></td>
																<td><?= $arrayData_group['totalStock'] ?></td>
																<td></td>
																<td style="text-align:right"><?= formatPrix($arrayData_group['totalMontant']) ?></td>
															</tr>
														</tfoot>
													</table>
												</div>
											</div>
											<?php
										// ______________ MODE PAS GROUPÉ ______________________    
									    // ________________________ MODE PAS GROUPÉ ______________________
										} else{
											foreach($arrayData as $ID_MAGASIN => $data){
												//case à cocher all magasins
												if($_GET["getTotalProduit"] == 1 ){
													echo "<p>Total de produits vendu dans l'ensemble des magasins: 000 ;</p><p>Total de ventes dans l'ensemble des magasins: 000 ;</p>";
												}
												?>
												<div class="col-md-12 mb-5" id="<?= $allMag[$ID_MAGASIN]["CONTACT_SUFFIX"]?>">
													<h3 class="">Animo Etc <?= $allMag[$ID_MAGASIN]['M_NOM']?></h3>
													<div class="table-responsive">
														<table class="card-view-no-edit page-size-table table table-no-bordered table-condensed table-striped">
															<thead>
																<tr>
																	<!-- ================================================================== ROWS ====================================================================================-->
																	<th style="text-align:left">
																		<a href="index.php?<?= rebuildQueryString(['orderby'=>'nbArticle', 'sens'=>($_GET["orderby"] == 'nbArticle' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
																			<?= $L['NBproduitvendu'];?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nbArticle' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																		</a>
																	</th>     																
																	<th style="text-align:left">
																		<a href="index.php?<?= rebuildQueryString(['orderby'=>'desc_fr', 'sens'=>($_GET["orderby"] == 'desc_fr' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
																			<?= $L['item'];?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'desc_fr' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																		</a>
																	</th>
																	<th>
																		Distributeur(s)
																	</th>
																	<th>
																		UPCs
																	</th>
																	<th style="text-align:left">
																		<a href="index.php?<?= rebuildQueryString(['orderby'=>'stock', 'sens'=>($_GET["orderby"] == 'stock' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
																		   Stock <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'stock' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																		</a>
																	</th>  																
																	<th style="text-align:right">
																		<a href="index.php?<?= rebuildQueryString(['orderby'=>'prix', 'sens'=>($_GET["orderby"] == 'prix' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
																		   Prix unitaire <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'prix' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																		</a>
																	</th> 
																	<th style="text-align:right">
																		<a href="index.php?<?= rebuildQueryString(['orderby'=>'totalPrix', 'sens'=>($_GET["orderby"] == 'totalPrix' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
																		   Total <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalPrix' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																		</a>
																	</th> 
																	<!-- ================================================================== and ROWS ====================================================================================-->
																</tr>
															</thead>
															<tbody>
																<?php
																if(count($data['data']) > 0){
																	foreach ( $data['data'] as $rowRaportPLU){ ?>
																		<tr>
																			<!-- =========================================  ROWS =================================================== -->
																			<td><?php echo $rowRaportPLU['nbArticle']?></td>
																			<td class='gauche'><a href="?p=produits&id=<?= urlencode($rowRaportPLU['id_article'])?>"><?php echo $rowRaportPLU['desc_fr'];?></a></td>
																			<td>
																				<?php 
																				if(count($rowRaportPLU['dists']) > 0){
																					foreach($rowRaportPLU['dists'] as $rowDist){
																						?>
																						<div><?= $rowDist['nom']?>: <?= $rowDist['num_four']?></div>
																						<?php
																					}
																				}
																				?>
																			</td>
																			<td>
																				<a href="?p=produits&upc=<?= $rowRaportPLU["PLU"]?>"><?= $rowRaportPLU["PLU"]?></a>
																				<?= ($rowRaportPLU["PLU2"] != "" ? ",<a href='?p=produits&upc=".$rowRaportPLU["PLU2"]."'>".$rowRaportPLU["PLU2"]."</a>":"")?>
																				<?= ($rowRaportPLU["PLU3"] != "" ? ",<a href='?p=produits&upc=".$rowRaportPLU["PLU3"]."'>".$rowRaportPLU["PLU3"]."</a>":"")?>
																			</td>
																			<td>
																				<?= $rowRaportPLU["stock"] ?>
																			</td>
																			<td class="text-right nowrap">
																				<?= nfs($rowRaportPLU["prix"])?>
																			</td>
																			<td class="text-right nowrap">
																				<?= nfs($rowRaportPLU["totalPrix"])?>
																			</td>
																			<!-- ========================================= fin ROWS =================================================== -->
																		</tr>
																		
																	<?php
																	}
																}else{ ?>
																<tr>
																	<td colspan="5">
																		Aucune donnée
																	</td>
																</tr>
																<?php } ?>
															</tbody>
															<tfoot>
																<tr style="font-weight:bold">
																	<td><?= $data["totalArticles"] ?></td>
																	<td></td>
																	<td></td>
																	<td></td>
																	<td><?= $data['totalStock'] ?></td>
																	<td></td>
																	<td style="text-align:right"><?= formatPrix($data["totalMontant"]) ?></td>
																</tr>
															</tfoot>
														</table>
													</div>
												</div>
												<?php
											}
										}
									}//fin if count
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</section>