<?php

$listSoustitre = [];

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

unset($_SESSION["product_search"]);
ini_set("memory_limit","256M");
set_time_limit(300);

$allMag = [];
$queryAllMag = query("select * from MAGASIN where caisse_db is not null order by M_NOM asc",[],$mysqli);
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}

//Gestion des accès aux magasin
$listID_MAGASINcanaccess = [];
if ( $_SESSION["utilisateur"]["security"] >= 2 ){
    //Acces restreint à certains magasins seulement
	$listID_MAGASINcanaccess = array_keys($_SESSION["magasins"]);
} else {
    //Acces total
	$listID_MAGASINcanaccess = array_keys($allMag);
}

//Stock les IDs des magasin avec droit d'accès 
$listID_MAGASIN = [];
// Si choix de magasins au select
if ( isset($_GET["ID_MAGASIN"]) ){
    //Pour chaque magasin 
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) {
	    //Vérifie si les magasins choisis font partir de celles qu'il a le droit d'accès 
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanaccess) ){
		    //Stoke les magasins
			$listID_MAGASIN[] = $ID_MAGASIN;
		}
	}
}
if ( sizeof($listID_MAGASIN) < 1 ){
	$listID_MAGASIN = $listID_MAGASINcanaccess;
} 

$listID_MAGASINstr = implode(",",$listID_MAGASIN);    

//Pour le filtre  WHERE entre facture_item et Article
$listAND = [];
//Pour nourrir les paramètres de $listAND
$listPARAM = [];
//Pour le filtre  WHERE  link_article_four
$listAND_laf = [];
//Pour  nourrir les paramètres  de $listAND_laf
$listPARAM_laf = [];

//Filtre Article by UPC
if( !empty($_REQUEST['upcsearch'])){
	$listPARAM[] = '%'.$_REQUEST['upcsearch'].'%';
	$listPARAM[] = '%'.$_REQUEST['upcsearch'].'%';
	$listPARAM[] = '%'.$_REQUEST['upcsearch'].'%';
	$listPARAM[] = '%'.$_REQUEST['upcsearch'].'%';
	$listAND[] = '(article.PLU like ? or article.boite_PLU like ? or article.PLU2 like ? or article.PLU3 like ?)';
	//Pour le PDF
	$listSoustitre[] = [L('upc','o'),$_REQUEST['upcsearch']];
}
//Filtre Article by Label
if( !empty($_REQUEST['keywrd'])){
	$listPARAM[] = '%'.$_REQUEST['keywrd'].'%';
	$listPARAM[] = '%'.$_REQUEST['keywrd'].'%';
	$listAND[] = '(article.desc_fr like ? or article.desc_en like ?)';
	$listSoustitre[] = [L('mots clés','o'),$_REQUEST['keywrd']];
}
//Filtre link_article_four by num_four   
if( !empty( $_REQUEST['foursearch'])){
	$num_four = preg_replace('/[^\da-z]/i', '',$_REQUEST['foursearch']);   
	$listPARAM_laf[] = '%'.$num_four.'%';
	$listPARAM_laf[] = '%'.$num_four.'%';
	$listPARAM_laf[] = '%'.$num_four.'%';
	//Enlève les signes suivantes  pour la recherche du $num_four
	$listAND_laf[] = "(REPLACE(f2.num_four, '-','') like ? OR
		    	       REPLACE(f2.num_four, '/','') like ? OR
			   	       REPLACE(f2.num_four, '_','') like ?)";
	$listSoustitre[] = [L('code four.','o'),$num_four];
}

//Filtre article by ID  fournisseur 
if(!empty($_REQUEST['fournisseur'])){
	$listPARAM[] = $_REQUEST['fournisseur'];
	$listAND[] = 'article.id_distributeur = ?';
	$listSoustitre[] = [L('fournisseur','o'),getFourDistName($_REQUEST['fournisseur'])];
}
//Filtre article by ID  distributeur 
if(!empty($_REQUEST['distributeur'])){
	$listPARAM_laf[] = $_REQUEST['distributeur'];
	$listAND_laf[] = 'f2.id_fournisseur = ?';
	$listSoustitre[] = [L('distributeur','o'),getFourDistName($_REQUEST['distributeur'])];
}

$listAND_laf[] = 'f2.discontinued IS NULL';

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

$arrayData = [
                'total_NbArticles'=>0,
                'totalMontant'=>0,
                'data'=>[]
              ];

$and = implode(' and ',$listAND);
$and_laf = implode(' and ',$listAND_laf); 

// loop tous les magasins
foreach($listID_MAGASIN as $ID_MAGASIN)
{
	$caisse_db = $allMag[$ID_MAGASIN]['caisse_db'];
	// créer une copie du connecteur de la caisse 
	$dbAnimoUneCaisse = $dbAnimoCaisse;
	$dbAnimoUneCaisse->select_db($caisse_db);

	$resulQuery = query("SELECT sum(facture_item.nb) as nbArticle, 
                                 	article.id_article,
                                	article.PLU, 
                                	article.PLU2, 
                                    article.PLU3,
                                    facture_item.date_insert,
                                    sum(facture_item.montant) as totalMontant,
                                    article.desc_fr
                                    FROM facture_item
                                    JOIN article using(id_article)
                                    join (
        									select f2.*
        									FROM link_article_four `f2`
        									where $and_laf
        									group by f2.id_article
        								) as laf 
    								 on (laf.id_article = facture_item.id_article)
        							 WHERE $and 
        							 group by facture_item.id_article 
        							 ORDER BY nbArticle DESC LIMIT 1000",
    							 	array_merge($listPARAM_laf,$listPARAM),$dbAnimoUneCaisse);
						  
	if($resulQuery->num_rows > 0)
	{ 
		while($rowFacItem = $resulQuery->fetch_assoc())
		{ 
		    //TOTEAUX DES COLONNES
			$arrayData['total_NbArticles'] += intval($rowFacItem['nbArticle']);
			$arrayData['totalMontant']     += floatval($rowFacItem['totalMontant']);
			
		    if(!isset($arrayData['data'][$rowFacItem["id_article"]] )){
    		    $arrayData['data'][$rowFacItem["id_article"]] = $rowFacItem;			        
		    }
		    //TOTEAUX DES LIGNES
			$arrayData['data'][$rowFacItem["id_article"]]['totalMontant']     += floatval($rowFacItem['totalMontant']); 
	        $arrayData['data'][$rowFacItem["id_article"]]['total_NbArticles'] += intval($rowFacItem['nbArticle']);
		    
		    if( $arrayData['data'][$rowFacItem["id_article"]]['total_NbArticles'] != 0){
		        //Set le prix moyen 	
		        $arrayData['data'][$rowFacItem["id_article"]]['prix'] = $arrayData['data'][$rowFacItem["id_article"]]['totalMontant'] / $arrayData['data'][$rowFacItem["id_article"]]['total_NbArticles'];
		    }else{
		        $arrayData['data'][$rowFacItem["id_article"]]['prix'] = 0;
		    }
		}
	}
	// garbage collection  
	unset($dbAnimoUneCaisse);
}//fin foreach


// ======================   GESTION TRIAGE TABLEAU  =================================

$listColonneTri = ['nbArticle','prix','totalMontant','desc_fr'];

if ( !in_array($_GET["orderby"],$listColonneTri) ){
	//Set by default
	$_GET["orderby"] = $listColonneTri[0];
}
if ( $_GET["sens"] == 'asc' ){
	$_GET["sens"] = "asc";
} else {
	$_GET["sens"] = "desc";
}

usort($arrayData["data"], function($a,$b){
    if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
    	return ($_GET["sens"] == "desc") ? 1 : -1;//1 c plus petit, -1 plus grand 
    } elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
    	return ($_GET["sens"] == "desc") ? -1 : 1;
    }
    return 0;
});  


/* ============== GESTION PDF/EXCEL  ================= */

if ( $_GET["getFile"] == "1"  )
{
    
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}

	$titre = L("rap_produit_vendu","o");
	//$listSoustitre[] = ["En date du", date("Y-m-d") ];
	$listNomMag = [];

	if ( !empty($from) and !empty($to) ){
		$listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $from ) . " " . L("au") . " " . formatDateUTF8nonHTML( $to ) ];
	} elseif( !empty($from) ) {
		$listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $from ) ];
	} elseif( !empty($to) ) {
		$listSoustitre[] = ["date", L("jusqu'au") . " " . formatDateUTF8nonHTML( $to ) ];
	} else {
		$listSoustitre[] = ["date", "tous" ];
	}

	foreach( $listID_MAGASIN as $ID_MAGASIN ){
		$listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
	}
	$listSoustitre[] = ["magasin(s)", implode(", ",$listNomMag) ];

	$listEnteteColonne = [
                    		[
                    		    ["text"=>L('Nb.',"o"),"width"=>10,"align"=>"L"],
                    		    ["text"=>L('Article',"o"),"width"=>80,"align"=>"L"],
                        	    ['text'=>'Distributeurs(s)','width'=>40,'align'=>'L'],
                        	    ["text"=>L('UPC',"o"),"width"=>25,"align"=>"C"],
                        	    ["text"=>L('Prix',"o"),"width"=>10,"align"=>"R"],
                        	    ["text"=>L('Total',"o"),"width"=>20,"align"=>"R"]
                    	    ],
                    	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	$isfirst = true;
	$i = 0;
	
	foreach($arrayData['data'] as $rowFacItem)
	{
	    if($i == 100){
	        break;
	    }
	    $isfirst = false;
		// créer une string avec les UPC
		$listPLU = [];
		
		$getDist = query('select fournisseur.nom,link_article_four.num_four 
		                  from fournisseur
                          join link_article_four using(id_fournisseur)
                          where est_fournisseur = 1 and id_article = ? and link_article_four.discontinued IS NULL and link_article_four.num_four is not NULL
                          order by prix_coutant asc',[$rowFacItem["id_article"]],$dbAnimoCaisse);		
		$dists = [];
		
		if($getDist->num_rows > 0){
		    while($rowDist = $getDist->fetch_assoc()){
		        $dists[] = $rowDist;
		    }
		}

		if(!empty($rowFacItem["PLU"])){
		    $listPLU[] = $rowFacItem["PLU"];
		}
		if(!empty($rowFacItem["PLU2"])){
		    $listPLU[] = $rowFacItem["PLU2"];
	    }
		if(!empty($rowFacItem["PLU3"])){
		    $listPLU[] = $rowFacItem["PLU3"];
		}
	
		$listChamps = [];
		$listChamps[] = $rowFacItem['nbArticle'];
		$listChamps[] = $rowFacItem['desc_fr'];
		$arrayStrDist = [];
		if(count($dists) > 0){
		    foreach($dists as $rowDist){
		        $arrayStrDist[] = $rowDist['nom'] . ': ' . $rowDist['num_four'];
		    }
		}
		$listChamps[] = implode("\n",$arrayStrDist);
		$listChamps[] = $rowFacItem["PLU"];
		$listChamps[] = nfs($rowFacItem["prix"]);
		$listChamps[] = nfs($rowFacItem["totalMontant"]);
		
		$rapport->writeLigneRapport3wrap( $listChamps );
        $i++;
	}//fin foreach
	
	$listTOTAL = [];
	$listTOTAL[] = nfsnd($arrayData["total_NbArticles"]);
	$listTOTAL[] = "";
	$listTOTAL[] = "";
	$listTOTAL[] = "";
	$listTOTAL[] = "";
	$listTOTAL[] = nfs($arrayData["totalMontant"]);
    $rapport->writeLigneGrandTotal($listTOTAL,[true, false, false,false,false,true]);

	ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");
}

?>
<section id="main" class="main-wrap bgc-white-darkest print" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("rap_produit_vendu","o");?></span>
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
														<input type="text" class="form-control" name="upcsearch" id="upcsearch" placeholder="<?php echo $L['upccode'];?>" value="<?php echo $_REQUEST['upcsearch']?>">
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group input-group">
														<input type="text" class="form-control" name="foursearch" id="foursearch" placeholder="Par code distributeur" value="<?php echo $_REQUEST['foursearch']?>">
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
															$enonce = "SELECT * FROM fournisseur WHERE est_distributeur IS NOT NULL AND inactif IS NULL ORDER BY nom";
															$resultFour = query($enonce,[],$dbAnimoCaisseDefault);
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
															$enonce = "SELECT * FROM fournisseur WHERE est_fournisseur IS NOT NULL";
															$resultFour = query($enonce,[],$dbAnimoCaisse);
															while($rowFour = $resultFour->fetch_assoc()){
																printf("<option value='%s'%s>%s</option>", $rowFour["id_fournisseur"], ($rowFour["id_fournisseur"] == $_REQUEST["distributeur"] ? " selected" : ""), $rowFour["nom"]);
															}
															?>
														</select>
													</div>
												</div>
												<?php if( sizeof($listID_MAGASINcanaccess) > 1 ){ $isMultiMag = true;?>
													<div class="col-md-8">
														<div>
															<select class="ui fluid normal multi-selection select-dropdown form-control" name="ID_MAGASIN[]" multiple>
																<?php
																foreach( $listID_MAGASINcanaccess as $ID_MAGASIN){
																	$infoMag = query("select * from MAGASIN where ID_MAGASIN = ?",[$ID_MAGASIN,],$mysqli)->fetch_assoc();
																	printf("<option value='%s'%s>%s</option>", $ID_MAGASIN,( in_array($ID_MAGASIN,$listID_MAGASIN)?" selected":""),$infoMag["M_NOM"]);
																}
																?>
															</select>
															<button class="btn btn-xs" onclick="$(this).closest('div').find('.multi-selection').selectDropdown('set selected', <?= str_replace('"', "'",json_encode(array_map(strval,$listID_MAGASINcanaccess))) ?> )" type="button"><?= L("tous sélectionner") ?></button>
															<button class="btn btn-xs" onclick="$(this).closest('div').find('.multi-selection').selectDropdown('clear')" type="button"><?= L("tous dé-sélectionner") ?></button>
														</div>
													</div>
												<?php } ?>
												<div class="col-md-12 text-right">
													<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('afficher');?></button>
												</div>
											</div>
										</form>
									</div>
									<div class="col-md-12 mb-5" id="<?= $allMag[$ID_MAGASIN]["CONTACT_SUFFIX"]?>">
    									<div class="table-responsive">
    										<table class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
    											<!-- ========================  thead ==========================-->
    											<thead>
    												<tr>
    													<th>
    														<a href="index.php?<?= rebuildQueryString(['orderby'=>'nbArticle','sens'=>($_GET["orderby"] == 'nbArticle' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    														    <?= L('NBproduitvendu')?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nbArticle' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
														    </a>
    													</th>
     													<th class='gauche'>
    														<a href="index.php?<?= rebuildQueryString(['orderby'=>'desc_fr','sens'=>($_GET["orderby"] == 'desc_fr' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    														    <?= L('item')?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'desc_fr' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
														    </a>
    													</th>   
    													<td>
    													    Distributeur(s)
    													</th>
    													<td>
    													    UPCs
													    </th>
														<th style="text-align:left">
														    <a href="index.php?<?= rebuildQueryString(['orderby'=>'prix', 'sens'=>($_GET["orderby"] == 'prix' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
														         <?= L('Prix moyen')?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'prix' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
														    </a>
													    </th>     													
														<th style="text-align:right">
														    <a href="index.php?<?= rebuildQueryString(['orderby'=>'totalMontant', 'sens'=>($_GET["orderby"] == 'totalMontant' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
														         <?= L('Total')?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalMontant' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
														    </a>
													    </th>    													
    												</tr>
    											</thead>
												<?php 
												if(count($arrayData['data']) > 0){
												?>
    											<!-- =====================  tbody ========================-->
    											<tbody>	
    											    <?php 
    											    $i = 0;
            									    foreach($arrayData['data'] as $rowFacItem )
            									    {
            									        if($i === 100){
            									            break;
            									        }
                                                 		$getDist = query('select fournisseur.nom,link_article_four.num_four 
                                            		                  from fournisseur
                                                                      join link_article_four using(id_fournisseur)
                                                                      where est_fournisseur = 1 and id_article = ? and link_article_four.discontinued IS NULL and link_article_four.num_four is not NULL
                                                                      order by prix_coutant asc',[$rowFacItem["id_article"]],$dbAnimoCaisse);       									        
            				
            									        $dists = [];
            									        if($getDist->num_rows > 0){
            									            while($rowDist = $getDist->fetch_assoc()){
            									                $dists[] = $rowDist;
            									            }
            									        }
            									        ?>
            											<tr>
            											    <!-- =====================================================  ROWS ===============================================-->
															<td> <?= $rowFacItem["nbArticle"] ?> </td>
															<td class='gauche'> <a href="?p=produits&id=<?= urlencode($id_article)?>"> <?= $rowFacItem['desc_fr'];?></a></td>
															<td>
    															<?php 
    															if(count($dists) > 0){
    															    foreach($dists as $rowDist){
    															        ?>
    															        <div><?= $rowDist['nom']?>: <?= $rowDist['num_four']?></div>
    															        <?php
    															    }
    															}
    															?>
    														</td>
															<td>
																<?php  
																$listPLU = [];
																$listChamp = ["PLU","PLU2","PLU3"];
																foreach ($listChamp as $champ){
																	if ($rowFacItem[$champ]) 
																		$listPLU[] = "<a href='?p=produits&upc=".$rowFacItem[$champ]."'>".$rowFacItem[$champ]."</a>";
																}
																echo implode(', ',$listPLU);
																?>
															</td>
															<td style="text-align:right"><?= nfs($rowFacItem['prix'])?></td>
															<td class="text-right nowrap">	<?= nfs($rowFacItem['totalMontant'])?></td>
														</tr>
														<!-- ===================================================== end  ROWS ===============================================-->
													<?php
													   $i++;
													}
												?> 
											    </tbody>
												<tfoot>
													<tr style="font-weight:bold">
														<td><?= $arrayData["total_NbArticles"] ?></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td style="text-align:right"><?= formatPrix($arrayData["totalMontant"]) ?></td>
													</tr>
												</tfoot> 
												<?php     
												}
												?>
    										</table>    									    
    									</div>
    								</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</section>