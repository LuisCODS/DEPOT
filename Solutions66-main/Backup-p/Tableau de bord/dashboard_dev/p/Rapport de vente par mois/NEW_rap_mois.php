<?php

ini_set("memory_limit","256M");
set_time_limit(300);

// =================================== GESTION DES ACCES  =================================================

//Pour les affichages seulement
$allMag = [];
$queryAllMag = query("select * from MAGASIN where caisse_db is not null order by M_NOM asc",[],$mysqli);
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}

//Stock les IDs des magasin selon le niveau d'accès 
$listID_MAGASINcanaccess = [];
if ( $_SESSION["utilisateur"]["security"] >= 2 ){
	$listID_MAGASINcanaccess = array_keys($_SESSION["magasins"]);
} else {
	$listID_MAGASINcanaccess = array_keys($allMag);
}

//Stock le sélect des magasins et verifie si le user a le droit d'accès pour ces magasins
$listMagasinAvecDroitAcces = [];
if ( isset($_GET["ID_MAGASIN"]) )
{
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) {
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanaccess) ){
			$listMagasinAvecDroitAcces[] = $ID_MAGASIN;
		}
	}
}

//Si pas de sélection de magasin
if ( sizeof($listMagasinAvecDroitAcces) < 1 )
{
	//Affiche par défaut toutes les magasin dont le user à droit d'acces
	$listMagasinAvecDroitAcces = $listID_MAGASINcanaccess;
}

// =================================== Recolte DE DONNÉES  =================================================

$data = [];  
//Pour le case à cocher (option comparative) : cette option compare les mois au mois de l'année passé
//$toCompare = false;


//Pour chaque ID du magasin
foreach ($listMagasinAvecDroitAcces as $ID_MAGASIN )
{
	//Cree un array avec l'ID de chaque magasin
	$data[$ID_MAGASIN] = ["lignes"=>[],"nbrefact"=>0,"totalsanstaxe"=>0 ];
	
	// Accès la BD de chaque magasin
	$dbAnimoCaisse->select_db($allMag[$ID_MAGASIN]["caisse_db"]);
 

	// =================================== GESTION DATE ==================================
	//Cas AVEC choix de data
	if ( $_GET["from"] or $_GET["to"] )
	{
		// Si les 2 dates sont fournis 
		if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
			
			$daterange = " where (facture.date_insert >= '{$_GET['from']} 00:00:00' AND facture.date_insert <= '{$_GET['to']} 23:59:59') ";

		// Seulement date début 	
		} else if ( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) ){
			
			$daterange = " where (facture.date_insert >= '{$_GET['from']} 00:00:00' ";

		// Seulement date fin	
		} else if ( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
			
			$daterange = " where facture.date_insert <= '{$_GET['to']} 23:59:59') ";
		}
		
		$enonce = "SELECT sum(facture.soustotal) `totalsanstaxe`, sum(facture.grandtotal) `totalavectaxe`, count(facture.id_facture) `nbrefact`, facture.date_insert
				   FROM facture".$daterange."
				   GROUP BY Year(facture.date_insert), Month(facture.date_insert)";
		$resultFactItem = query($enonce,[],$dbAnimoCaisse);    

	}else {
		 //Sans choix de date - default
		$enonce = "SELECT sum(facture.soustotal) `totalsanstaxe`, sum(facture.grandtotal) `totalavectaxe`, count(facture.id_facture) `nbrefact`, facture.date_insert
					FROM facture
					GROUP BY Year(facture.date_insert), Month(facture.date_insert)";
		$resultFactItem = query($enonce,[],$dbAnimoCaisse);        
	}

	while( $uneLigneFact = $resultFactItem->fetch_assoc() )  {
	    //Extrait l'annee de la date
		$uneLigneFact["annee"] = date("Y",strtotime($uneLigneFact["date_insert"]));
		//Extrait le mois de la date
		$uneLigneFact["mois"] = date("m",strtotime($uneLigneFact["date_insert"]));
		// Set annee et mois
		$uneLigneFact["anneemois"] = date("Y-m",strtotime($uneLigneFact["date_insert"]));
		
		$data[$ID_MAGASIN]["lignes"][] = $uneLigneFact;
		$data[$ID_MAGASIN]["totalsanstaxe"] += $uneLigneFact["totalsanstaxe"];
		$data[$ID_MAGASIN]["nbrefact"] += $uneLigneFact["nbrefact"];    
	}// fin while
	
	$listTriPosible = ["anneemois","nbrefact","totalsanstaxe"];
	if ( !in_array($_GET["orderby"],$listTriPosible) )
	{
		$_GET["orderby"] = $listTriPosible[0];
	}
	
	if ( $_GET["sens"] == 'asc' )
	{
		$_GET["sens"] = "asc";
	} else 
	{
		$_GET["sens"] = "desc";
	}

	uasort( $data[$ID_MAGASIN]["lignes"], function($a,$b)
	{
		if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] )
		{
			return ($_GET["sens"]=="desc")?1:-1;
			
		}elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] )
		{
			return ($_GET["sens"]=="desc")?-1:1;
		}
		return 0;
	});   
	
}//foreach


/*
echo '<pre>';
echo '$_REQUEST '.htmlspecialchars(print_r($_REQUEST, true)); 
echo'<br><br><br>';
echo '$data '.htmlspecialchars(print_r($data, true)); 
echo '</pre>';
*/

// =================================== GESTION PDF EXCEL =================================================

if ( $_GET["getFile"] == "1" and $data ){
	
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}
	
	//  =================== DÉFINITION DES DONNES =======================
	
	//Titre 
	$titre = L("rap_mois","o");
	//Sous titre
	$listSoustitre = [];
	
	if ( $_GET['from'] and $_GET['to'] ){
		$listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $_GET['from'] ) . " " . L("au") . " " . formatDateUTF8nonHTML( $_GET['to'] ) ];
	} elseif( $_GET['from'] ) {
		$listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $_GET['from'] ) ];
	} elseif( $_GET['to'] ) {
		$listSoustitre[] = ["date", L("jusqu'au") . " " . formatDateUTF8nonHTML( $_GET['to'] ) ];
	} else {
		$listSoustitre[] = ["date", "tous" ];
	}
	
	//Tous les magasin du select
	$listNomMag = [];
	foreach( $listMagasinAvecDroitAcces as $ID_MAGASIN ){
		$listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
	}
	//Rassemble les noms de toutes magasins séparés par virgule
	$listSoustitre[] = ["magasin(s) :", implode(", ",$listNomMag) ];
	
	//Titre colonnes 
	$listEnteteColonne = [
			[ 
				["text"=>L("mois","o"),"width"=>60,"align"=>"L"],
				["text"=>L("nbrefact",'o'),"width"=>60,"align"=>"C"],
				["text"=>L("total",'o'),"width"=>60,"align"=>"R"],
			],
	];
	//Écrire sur le fichier
	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);
	
	// ======================= ÉCRITURE DES DONNÉS ================================
		
	$first = true;
	foreach ($data as $ID_MAGASIN => $dataUnMagasin)
	{
		if(!$first){
			$rapport->AddPage();
		}else{
			$first = false;
		}
		//All magasin name
		$rapport->SetFont("helvetica","B",14);
		$rapport->Cell(0,0,$allMag[$ID_MAGASIN]["M_NOM"], 0, 1);  
		
		// display les lignes
		foreach ($data[$ID_MAGASIN]["lignes"]  as  $ligneValue)
		{
			$listChamps = [];
			$listChamps[0] = utf8_encode(strftime("%B %Y", strtotime(sprintf("%04d-%02d-02",$ligneValue["annee"],$ligneValue["mois"]))));
			$listChamps[1] = $ligneValue["nbrefact"];
			$listChamps[2] = nfs($ligneValue["totalsanstaxe"]);
			$rapport->writeLigneRapport3wrap( $listChamps );  
		}
		// display les lignes total 
		$listTOTAL = [];
		$listTOTAL[] = "TOTAL";
		$listTOTAL[] = nfsnd($data[$ID_MAGASIN]["nbrefact"]);
		$listTOTAL[] = nfs($data[$ID_MAGASIN]["totalsanstaxe"]);
		$rapport->writeLigneGrandTotal($listTOTAL, [false,true,true] );
	}//fin foreach

	ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");

//fin PDF EXCEL    
} else {
	?>
	<section id="main" class="main-wrap bgc-white-darkest print" role="main">
		<!-- Start SubHeader-->
		<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
			<h1 class="page-title pull-left fs-4 fw-light">
				<i class="fa fa-bar-chart icon-mr fs-4"></i>
				<span class="hidden-xs-down"><?= L("rap_mois","o");?></span>
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
		<div class="print-only">
			<div class="px-3">
				<h5>Animo etc <?= getInfoMag("succursale")?></h5>
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
										<div class="fixed-table-toolbar no-print">
											<!--form -->
											<form method="get" id="formListRapToilettage">
												<input type="hidden" name="p" value="<?= $_GET["p"]?>">
												<input type="hidden" name="sens" value="<?= $_GET["sens"] ?>">
												<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">
												<!--BOUTON DATE -->
												<div class="row" style="margin-bottom:15px;">
													<div class="col-md-8">
														<div class="input-group bs-datepicker input-daterange picker-range">
															<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities($_GET["from"])?>">
															<span class="input-group-addon px-3"><?= L("to"); ?></span>
															<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($_GET["to"])?>">
														</div>
													</div>
												</div>
												<!--SELECT -->
												<?php if( sizeof($listID_MAGASINcanaccess) > 1 ){ $isMultiMag = true;?>
													<div class="col-12">
														<div class="pt-3">
															<select class="ui fluid normal multi-selection select-dropdown form-control" name="ID_MAGASIN[]" multiple>
																<?php
																foreach( $listID_MAGASINcanaccess as $ID_MAGASIN){
																	$infoMag = query("select * from MAGASIN where ID_MAGASIN = ?",[$ID_MAGASIN,],$mysqli)->fetch_assoc();
																	printf("<option value='%s'%s>%s</option>", $ID_MAGASIN,( in_array($ID_MAGASIN,$listMagasinAvecDroitAcces)?" selected":""),$infoMag["M_NOM"]);
																}
																?>
															</select>
															<button class="btn btn-xs" onclick="$(this).closest('div').find('.multi-selection').selectDropdown('set selected', <?= str_replace('"', "'",json_encode(array_map(strval,$listID_MAGASINcanaccess))) ?> )" type="button"><?= L("tous sélectionner") ?></button>
															<button class="btn btn-xs" onclick="$(this).closest('div').find('.multi-selection').selectDropdown('clear')" type="button"><?= L("tous dé-sélectionner") ?></button>
														</div>
													</div>
												<?php } ?>
				
												<!--BOUTON submit -->
												<div class="columns columns-right btn-group pull-right no-print">
													<button type="submit" class="applyBtn btn btn-small btn-success" name="SendFormAfficher"  id="btn_submit"><?= L('afficher');?></button>
												</div>
											</form>
										</div>
										<?php
										if ( $data )
										{
											//	foreach ( $data[$ID_MAGASIN]["lignes"] as $uneLigneFactItem){
											foreach ($data as $ID_MAGASIN => $dataUnMagasin)  
											{
										?>
												<br><br>
												<h3><b>Animo Etc <?= $allMag[$ID_MAGASIN]["M_NOM"]?></b></h3>
												<!-- TABLE -->
												<table id="" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
													<!-- table head -->
													<thead>
														<tr>
															<th><?= L("mois"); ?></th>
															<th style="text-align:center"><?= L("nbrefact","o"); ?></th>
															<th style="text-align:right"><?= L("total","o"); ?></th>
														</tr>
													</thead>
													<!-- table body -->
													<tbody>
													<?php 
													//foreach ($data["lignes"] as $rowRapport) {
													foreach ($data[$ID_MAGASIN]["lignes"]  as  $ligneValue)
													{	
														$link = "?p=rap_ventes&from=" . sprintf("%04d-%02d-01",$ligneValue["annee"],$ligneValue["mois"]) . "&to=" . 
														getDateLastDayMonth($ligneValue["annee"],$ligneValue["mois"]) . "&ID_MAGASIN[]=". $ID_MAGASIN;
													?>
													
													<tr>
														<td><a href="<?= $link ?>"><b><?php echo utf8_encode(strftime("%B %Y", strtotime(sprintf("%04d-%02d-02",$ligneValue["annee"],$ligneValue["mois"])))); ?></b></a></td>
														<td style="text-align:center"><?= $ligneValue["nbrefact"]?></td>
														<td style="text-align:right"><a href="<?= $link ?>"><?= formatPrix($ligneValue["totalsanstaxe"])?></a></td>
													</tr>
													
													<?php 
													}//fin foreach?>    	
													
													</tbody>
													<!-- table foot -->
													<tfoot>
														<tr style="font-weight:bold">
															<td></td>
															<td style="text-align:center;"><?= $data[$ID_MAGASIN]["nbrefact"];?></td>
															<td style="text-align:right;"><?= formatPrix($data[$ID_MAGASIN]["totalsanstaxe"]);?></td>
														</tr>
													</tfoot>
												</table>
											<?php 
											}//fin foreach 
											?>
										<?php
										}//fin if $data
										?>
										<!-- END PAGE CONTENT-->
										<div class="clearfix"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</section>
	<?php
} ?>