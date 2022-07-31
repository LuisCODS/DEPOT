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

$listColonneTri = ['nbArticle','prix','totalPrix','desc_fr'];
if(in_array($_GET['orderby'],$listColonneTri)){ $orderby = $_GET['orderby']; }else{ $orderby = 'nbArticle';}
if($_GET['sens']==''){ $sens = 'desc';}else{ $sens = $_GET['sens'];}

$allMag = [];
$queryAllMag = query("select * from MAGASIN where caisse_db is not null order by M_NOM asc",[],$mysqli);
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}

//$listID_MAGASINcanaccess
$listID_MAGASINcanaccess = [];
if ( $_SESSION["utilisateur"]["security"] >= 2 ){
	$listID_MAGASINcanaccess = array_keys($_SESSION["magasins"]);
} else {
	$listID_MAGASINcanaccess = array_keys($allMag);
}
//$listID_MAGASIN
$listID_MAGASIN = [];
if ( isset($_GET["ID_MAGASIN"]) ){
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) {
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanaccess) ){
			$listID_MAGASIN[] = $ID_MAGASIN;
		}
	}
}
if ( sizeof($listID_MAGASIN) < 1 ){
	$listID_MAGASIN = $listID_MAGASINcanaccess;
}


$listID_MAGASINstr = implode(",",$listID_MAGASIN);



$listAND = [];
$listPARAM = [];

if( !empty($_REQUEST['upcsearch'])){
	$listPARAM[] = '%'.$_REQUEST['upcsearch'].'%';
	$listPARAM[] = '%'.$_REQUEST['upcsearch'].'%';
	$listPARAM[] = '%'.$_REQUEST['upcsearch'].'%';
	$listPARAM[] = '%'.$_REQUEST['upcsearch'].'%';
	$listAND[] = '(article.PLU like ? or article.boite_PLU like ?
								or article.PLU2 like ? or article.PLU3 like ?)';
	$listSoustitre[] = [L('upc','o'),$_REQUEST['upcsearch']];
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
	$listAND[] = "(REPLACE(link_article_four.num_four, '-','') like ? OR
								REPLACE(link_article_four.num_four, '/','') like ? OR
								REPLACE(link_article_four.num_four, '_','') like ?)";
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
$arrayDataFinal = [
    'data'=>[],
    'totalArticles'=>0,
    'totalMontant'=>0
];

$and = implode(' and ',$listAND);

// loop tous les magasins
foreach($listID_MAGASIN as $ID_MAGASIN){
	$arrayData[$ID_MAGASIN] = [];
	$caisse_db = $allMag[$ID_MAGASIN]['caisse_db'];
	// créer une copie du connecteur de la caisse
	$dbAnimoUneCaisse = $dbAnimoCaisse;
	$dbAnimoUneCaisse->select_db($caisse_db);

	$resulRaportPLU = query("SELECT sum(facture_item.nb) as nbArticle, article.id_article, article.PLU, article.PLU2, article.PLU3,facture_item.date_insert,sum(facture_item.montant) as totalPrix, article.desc_fr
							   FROM facture_item
							   JOIN article using(id_article)
								join (
										select f2.*
										from link_article_four `f2`
										where f2.discontinued IS NULL
										group by f2.id_article
									) as laf on (laf.id_article = facture_item.id_article)
							  WHERE $and group by facture_item.id_article ORDER BY nbArticle DESC LIMIT 1000",
				$listPARAM,$dbAnimoUneCaisse);
	if($resulRaportPLU->num_rows > 0){
		$arrayData[$ID_MAGASIN] = [
			'data'=>[],
			'totalArticles'=>0,
			'totalMontant'=>0
		];
		while($rowRaportPLU = $resulRaportPLU->fetch_assoc()){
			//$rowRaportPLU['totalPrix'] = $rowRaportPLU['prix'] * $rowRaportPLU['nbArticle'];
		    if($rowRaportPLU['nbArticle'] != 0){
		        $rowRaportPLU['prix'] = $rowRaportPLU['totalPrix'] / $rowRaportPLU['nbArticle'];
		    }else{
		        $rowRaportPLU['prix'] = 0;
		    }
		    $arrayData[$ID_MAGASIN]['data'][] = $rowRaportPLU;
			$arrayData[$ID_MAGASIN]['totalArticles'] += intval($rowRaportPLU['nbArticle']);
			$arrayData[$ID_MAGASIN]['totalMontant'] += floatval($rowRaportPLU['totalPrix']);
		}
	}
	// garbage collection            JLT:lol
	unset($dbAnimoUneCaisse);
}
foreach($arrayData as $ID_MAGASIN => $data){
    if(is_array($data['data'])){
        foreach($data['data'] as $rowRaportPLU){
            //aller chercher le distributeur
            
            
            $arrayDataFinal['data'][$rowRaportPLU['id_article']] += $rowRaportPLU['nbArticle'];
            $arrayDataFinal['totalArticles'] += $rowRaportPLU['nbArticle'];
            $arrayDataFinal['totalMontant'] += $rowRaportPLU['totalPrix'];
        }
    }
}
uasort( $arrayDataFinal['data'], function($a,$b){
    global $orderby,$sens;
    if ( $a < $b ){
        return ($sens=="desc")?1:-1;
    } elseif( $a > $b ){
        return ($sens=="desc")?-1:1;
    }
    return 0;
});
if ( $_GET["getFile"] == "1"  ){
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
		[["text"=>L('Article',"o"),"width"=>70,"align"=>"L"],
		    ['text'=>'Distributeurs(s)','width'=>35,'align'=>'L'],
		    ["text"=>L('CUP',"o"),"width"=>25,"align"=>"C"],
		    ["text"=>L('Nb.',"o"),"width"=>10,"align"=>"R"],
		    ["text"=>L('Prix',"o"),"width"=>20,"align"=>"R"],
		    ["text"=>L('Total',"o"),"width"=>20,"align"=>"R"]],
	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	$isfirst = true;
	$i = 0;
	foreach($arrayDataFinal['data'] as $id_article => $numVendus){
	    if($i == 100){
	        break;
	    }
	    $isfirst = false;
		// créer une string avec les PLU
		$listPLU = [];
		$getDist = query('select fournisseur.nom,link_article_four.num_four from fournisseur
        join link_article_four using(id_fournisseur)
        where est_fournisseur = 1 and id_article = ? and link_article_four.discontinued IS NULL and link_article_four.num_four is not NULL
        order by prix_coutant asc
        ',[$id_article],$dbAnimoCaisse);
		$dists = [];
		if($getDist->num_rows > 0){
		    while($rowDist = $getDist->fetch_assoc()){
		        $dists[] = $rowDist;
		    }
		}
		$produit = query('select desc_fr,PLU,PLU2,PLU3 from article where id_article = ?',[$id_article],$dbAnimoCaisseDefault)->fetch_assoc();
		$prix = query('select prix.prix from prix where id_article = ? order by date_update desc limit 1',[$id_article],$dbAnimoCaisseDefault)->fetch_row()[0];
		
		if(!empty($produit["PLU"])){
		    $listPLU[] = $produit["PLU"];
		}
		if(!empty($produit["PLU2"])){
		    $listPLU[] = $produit["PLU2"];
	    }
		if(!empty($produit["PLU3"])){
		    $listPLU[] = $produit["PLU3"];
		}
		

		$listChamps = [];
		$listChamps[] = $produit['desc_fr'];
		$arrayStrDist = [];
		if(count($dists) > 0){
		    foreach($dists as $rowDist){
		        $arrayStrDist[] = $rowDist['nom'] . ': ' . $rowDist['num_four'];
		    }
		}
		$listChamps[] = implode("\n",$arrayStrDist);
		//$listChamps[] = implode("\n",$listPLU);
		$listChamps[] = $produit["PLU"];
		$listChamps[] = $numVendus;
		$listChamps[] = nfs($prix);
		$listChamps[] = nfs($prix * $numVendus);
		$rapport->writeLigneRapport3wrap( $listChamps );
        $i++;
	}
	$listTOTAL = [];
	$listTOTAL[] = "";
	$listTOTAL[] = "";
	$listTOTAL[] = "TOTAL";
	//$listTOTAL[] = "";
	$listTOTAL[] = nfsnd($arrayDataFinal["totalArticles"]);
	$listTOTAL[] = "";
	$listTOTAL[] = nfs($arrayDataFinal["totalMontant"]);
    $rapport->writeLigneGrandTotal($listTOTAL,[false, false, true,true,true,true]);

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
														<input type="text" class="form-control" name="upcsearch" id="upcsearch" placeholder="<?php echo $L['upccode'];?>" value="<?php echo $_REQUEST['upcsearch']?>">
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
															$enonce = "SELECT * FROM fournisseur WHERE est_distributeur IS NOT NULL";
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
												<div class="col-md-<?= $isMultiMag ? 8 : 12?> text-right">
													<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('afficher');?></button>
												</div>
											</div>
										</form>
									</div>
									<div class="col-md-12 mb-5" id="<?= $allMag[$ID_MAGASIN]["CONTACT_SUFFIX"]?>">
    									<div class="table-responsive">
    										<table class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
    											<thead>
    												<tr>
    													<th>
    														<a href="index.php?<?= rebuildQueryString(['orderby'=>'nbArticle','sens'=>($orderby == 'nbArticle' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> <?= L('NBproduitvendu')?> <?= '<i class="fa fa-sort'.(($orderby == 'nbArticle' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>
    													</th>
    													<th class='gauche'>
    														<?php /*?><a href="index.php?<?= rebuildQueryString(['orderby'=>'desc_fr','sens'=>($orderby == 'desc_fr' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>">*/?> <?= L('item')?> <?php /*?><?= '<i class="fa fa-sort'.(($orderby == 'desc_fr' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>*/?>
    													</th>
    													<th>Distributeur(s)</th>
    													<th>UPCs</th>
    													<th class="text-right">
    														<?php /*?><a href="index.php?<?= rebuildQueryString(['orderby'=>'prix','sens'=>($orderby == 'prix' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>">*/?> <?= L('Prix unitaire')?> <?php /*?><?= '<i class="fa fa-sort'.(($orderby == 'prix' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>*/?>
    													</th>
    													<th class="text-right">
    														<?php /*?><a href="index.php?<?= rebuildQueryString(['orderby'=>'totalPrix','sens'=>($orderby == 'totalPrix' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>">*/?> <?= L('Total')?> <?php /*?><?= '<i class="fa fa-sort'.(($orderby == 'totalPrix' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>*/?>
    													</th>
    												</tr>
    											</thead>
    											<tbody>
    												<?php
                									if(count($arrayDataFinal['data']) > 0){
                									    $i = 0;
                									    foreach($arrayDataFinal['data'] as $id_article => $nbVendus){
                									        if($i === 100){
                									            break;
                									        }
                									        $getDist = query('select fournisseur.nom,link_article_four.num_four from fournisseur
                                                            join link_article_four using(id_fournisseur)
                                                            where est_fournisseur = 1 and id_article = ?
                                                            order by prix_coutant asc
                                                            ',[$id_article],$dbAnimoCaisse);
                									        $dists = [];
                									        if($getDist->num_rows > 0){
                									            while($rowDist = $getDist->fetch_assoc()){
                									                $dists[] = $rowDist;
                									            }
                									        }
                									        $produit = query('select desc_fr,PLU,PLU2,PLU3 from article where id_article = ?',[$id_article],$dbAnimoCaisseDefault)->fetch_assoc();
                									        $prix = query('select prix.prix from prix where id_article = ? order by date_update desc limit 1',[$id_article],$dbAnimoCaisseDefault)->fetch_row()[0];
                									        ?>
                											<tr>
    															<td><?php echo $nbVendus?></td>
    															<td class='gauche'><a href="?p=produits&id=<?= urlencode($id_article)?>"><?php echo $produit['desc_fr'];?></a></td>
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
    																<a href="?p=produits&upc=<?= $produit["PLU"]?>"><?= $produit["PLU"]?></a>
    																<?= ($produit["PLU2"] != "" ? ",<a href='?p=produits&upc=".$produit["PLU2"]."'>".$produit["PLU2"]."</a>":"")?>
    																<?= ($produit["PLU3"] != "" ? ",<a href='?p=produits&upc=".$produit["PLU3"]."'>".$produit["PLU3"]."</a>":"")?>
    															</td>
    															<?php /*?><td>
    																<a href="?p=produits&four=<?= $rowRaportPLU["num_four"]?>"><?= $rowRaportPLU["num_four"]?></a>
    															</td>*/?>
    															<td class="text-right nowrap">
    																<?= nfs($prix)?>
    															</td>
    															<td class="text-right nowrap">
    																<?= nfs($prix * $nbVendus)?>
    															</td>
    														</tr>
    													<?php
    													   $i++;
    													}
    												}else{?>
    												<tr>
    													<td colspan="5">
    														Aucune donnée
    													</td>
    												</tr>
    												<?php }?>
    											</tbody>
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