<?php
/*
echo '<pre>';
echo htmlspecialchars(print_r($_REQUEST, true));
var_export($_REQUEST);
echo '</pre>';
*/

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
if ( isset($_GET["ID_MAGASIN"]) ){
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) {
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanaccess) ){
			$listMagasinAvecDroitAcces[] = $ID_MAGASIN;
		}
	}
}
//Si pas de sélection de magasin
if ( sizeof($listMagasinAvecDroitAcces) < 1 ){
    //Affiche par défaut toutes les magasin dont le user à droit d'acces
	$listMagasinAvecDroitAcces = $listID_MAGASINcanaccess;
}
// ====================== RECUPERATION DE DONNÉES  =======================================
$data = [];
foreach ($listMagasinAvecDroitAcces as $ID_MAGASIN ){
    
    // Accès la BD de  chaque magasin
    $dbAnimoCaisse->select_db($allMag[$ID_MAGASIN]["caisse_db"]);
    
    // _________________ GESTION DATE PAR DÉFAUT  _________________
    
    // Set la date à partir du mois et années courante 
    $a = floatval( date("Y") );
    $m = floatval( date("m") );
    $m -= 1; 
    
    if ($m<1){
        $m = 12;
        $a -= 1;
    }
    if ( $_GET['from'] == "" ){
        //  0000-00-01 
    	$_GET['from'] = sprintf( "%04d-%02d-01", $a,$m );
    }
    if ( $_GET['to'] == "" ){
    	$_GET['to'] = getDateLastDayMonth( $a, $m );
    }
    $daterange = "";
    // Si une des dates sont envoyée
    if ( $_GET["from"] or $_GET["to"] ){
    
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
    }    
    /*
    SELECT sum(facture.soustotal) `totalsanstaxe`, sum(facture.grandtotal) `totalavectaxe`, 
    count(facture.id_facture) `nbreFactures`, utilisateur.nom, utilisateur.prenom, utilisateur.id_utilisateur
    FROM facture JOIN utilisateur USING(id_utilisateur)
    where facture.date_insert >= '2021-05-01 00:00:00'
    AND facture.date_insert <= '2021-05-31 23:59:59'
    GROUP BY utilisateur.id_utilisateur ORDER BY totalavectaxe DESC
    
    SELECT count(facture.id_facture) `nbreFactures`, utilisateur.nom, utilisateur.prenom, utilisateur.id_utilisateur
    FROM facture 
    JOIN utilisateur USING(id_utilisateur) where facture.date_insert >= '2021-03-01 00:00:00' AND facture.date_insert <= '2021-03-31 23:59:59' AND utilisateur.id_utilisateur = 41
    
    
    */
    $queryRaport = "SELECT utilisateur.nom, utilisateur.prenom,utilisateur.id_utilisateur, 
                    count(facture.id_facture) `nbreFactures`, sum(facture.soustotal) `totalsanstaxe`, sum(facture.grandtotal) `totalavectaxe`
                    FROM facture  
                    JOIN utilisateur USING(id_utilisateur)  
                    ".$daterange."
                    GROUP BY utilisateur.id_utilisateur ORDER BY totalavectaxe desc";
    $resulRaport = $dbAnimoCaisse->query($queryRaport) or die($dbAnimoCaisse->error);

	//$data[$ID_MAGASIN] = ["" => "", ... ];
	$data[$ID_MAGASIN] = [];
	$data[$ID_MAGASIN]["nb_Factures"] = 0;
	$data[$ID_MAGASIN]["total_sansTaxe"] = 0;
	$data[$ID_MAGASIN]["total_avecTaxe"] = 0;
    $data[$ID_MAGASIN]["nbEscomptes"] = 0;
    $data[$ID_MAGASIN]["totalEscomptes"] =0;
    $data[$ID_MAGASIN]["lignes"] = [];
	
	//colonnes rows from facture : totalsanstaxe, totalavectaxe, nbreFactures
	//colonnes rows from utilisateur : nom, prenom  
	while ($rowRaport = $resulRaport->fetch_assoc()) {
	    $user= [];
	     
    	if( $daterange){
    	    	
    		$queryRabais = sprintf("SELECT COUNT(facture_item.id_facture_item) as nbEscomptes, SUM(montant) as totalEscomptes
    								FROM facture_item
    								JOIN facture using(id_facture) 
    								JOIN utilisateur using(id_utilisateur)
    								$daterange 
    								AND type = 'ESCOMPTE' AND id_utilisateur = %s 
    								GROUP BY id_utilisateur 
    								ORDER BY sum(grandtotal) desc", $rowRaport["id_utilisateur"]);
    	}else{
    		$queryRabais = sprintf("SELECT COUNT(*) as nbEscomptes, SUM(montant) as totalEscomptes
    		                        FROM facture_item
    								JOIN facture using(id_facture)
    								JOIN utilisateur using(id_utilisateur)
    								WHERE type = 'ESCOMPTE' AND id_utilisateur = %s 
    								GROUP BY id_utilisateur
    								ORDER BY sum(grandtotal) DESC", $rowRaport["id_utilisateur"]);
    	}	   
    	
	    $resultRabais = query($queryRabais,[],$dbAnimoCaisse);
	    
    	if($rowRabais = $resultRabais->fetch_assoc() ){
    	    
    	    $user["nbEscomptes"]  += $rowRabais["nbEscomptes"];                         // Nbre des escomptes par utilisateur	                           
    	    $user["totalEscomptes"]  += $rowRabais["totalEscomptes"] * -1 ;             // TOTAL  d'escomptes par utilisateur         
            $data[$ID_MAGASIN]["nbEscomptes"] += $rowRabais["nbEscomptes"];             // GRAND TOTAL Nbre  d'escomptes  	                		
            $data[$ID_MAGASIN]["totalEscomptes"] += $rowRabais["totalEscomptes"] * -1 ; // GRAND TOTAL des escomptes   
            
    	}else{
    	    
            $user["nbEscomptes"]=0;
            $user["totalEscomptes"]=0;
    	}	   
    	//UTILISATEUR data
	    $user["id_utilisateur"] = $rowRaport["id_utilisateur"];
	    $user["nom"] = $rowRaport["nom"];
	    $user["prenom"] = $rowRaport["prenom"];
	    $user["totalsanstaxe"] = $rowRaport["totalsanstaxe"];
	    $user["totalavectaxe"] = $rowRaport["totalavectaxe"];
	    $user["nbreFactures"] = $rowRaport["nbreFactures"];
	    //MAGASIN data
	    $data[$ID_MAGASIN]["nb_Factures"]        += $rowRaport["nbreFactures"]; // TOTAL  Nbre de factures	
        $data[$ID_MAGASIN]["total_sansTaxe"]     += $rowRaport["totalsanstaxe"];// TOTAL  avant taxes		
		$data[$ID_MAGASIN]["total_avecTaxe"]     += $rowRaport["totalavectaxe"];// TOTAL  avec taxes
		$data[$ID_MAGASIN]["lignes"][] = $user;
	}// fin while	
	
	
}//fin foreach
/*
echo '<pre>';
echo " ".htmlspecialchars(print_r($data, true));
echo '</pre>';

*/

// =================================== GESTION PDF et Excel =================================================

//Au clique PDF ou EXCEL
if ( $_GET["getFile"] == "1"  ){
	require_once(__DIR__."/../req/print.php"); 
    
	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}

   /* ============================== COLETE DE DONNÉS FICHIER =========================*/
	
	//NIVEAU 1 DU FICHIER: titre
	$titreFichier = L("Rapport de ventes par utilisateur","o");
    //NIVEAU 2 DU FICHIER: label 
    $listSoustitreFichier = [];
    
    //SI les 2 dates ont été fournies 
	if ( !empty($_GET["from"]) and !empty($_GET["to"]) ){
		$listSoustitreFichier[] = ["Date :", L("du") . " " . formatDateUTF8nonHTML( $_GET["from"] ) . " " . L("au") . " " . formatDateUTF8nonHTML( $_GET["to"] ) ];
	// Si seulemtn la date de départs    
	} elseif( !empty($_GET["from"]) ) {
		$listSoustitreFichier[] = ["Date :", L("du") . " " . formatDateUTF8nonHTML( $_GET["from"] ) ];
	// Si seulement la date de fin    
	} elseif( !empty($_GET["to"]) ) {
		$listSoustitreFichier[] = ["Date :", L("jusqu'au") . " " . formatDateUTF8nonHTML( $_GET["to"] ) ];
	// Prend toutes les dates  
	} else {
		$listSoustitreFichier[] = ["Date :", "tous" ];
	}

	$listNomMag = [];
	
	foreach( $listMagasinAvecDroitAcces as $ID_MAGASIN ){
		$listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
	}
	
	//ENCORE NIVEAU 2 DU FICHIER: Rassemble les noms de toutes magasins séparés par virgule
	$listSoustitreFichier[] = ["magasin(s) :", implode(", ",$listNomMag) ];
    
    //NIVEAU 3 DU FICHIER: les colonnes
	$listEnteteColonneFichier = [
		[
			["text"=>L('Utilisateurs',"o"),"width"=>35,"align"=>"L"],
			["text"=>L('Nbre de',"o"),"width"=>25,"align"=>"C"],
			["text"=>L("Nbre d'escomptes","o"),"width"=>25,"align"=>"C"],
			["text"=>L('Total des',"o"),"width"=>25,"align"=>"R"],
			["text"=>L('Total',"o"),"width"=>25,"align"=>"R"],
			["text"=>L('Total',"o"),"width"=>25,"align"=>"R"],
		],
		[
			["text"=>"","width"=>35,"align"=>"L"],
			["text"=>"factures","width"=>25,"align"=>"C"],
			["text"=>L("accordées"),"width"=>25,"align"=>"C"],
			["text"=>L('escomptes'),"width"=>25,"align"=>"R"],
			["text"=>"avant taxes","width"=>25,"align"=>"R"],
			["text"=>"avec taxes","width"=>25,"align"=>"R"],
		]
		
	];
	// TJRS UTILISER debutSection3, PAS 1 ni 2!									
	$rapport->debutSection3($titreFichier,$listSoustitreFichier,$listEnteteColonneFichier);
	//@parm: Prend les config de l'indice du tableau souhaité
	$rapport->setInfoCols(0);

	$isfirst = true;
	
	foreach($data as $ID_MAGASIN => $dataMagasin){
	    // $arrayData[$ID_MAGASIN]["data"]
		    
			if (!$isfirst){
				$rapport->addPage();
			}
			$isfirst = false;

			if( sizeof($listID_MAGASINcanaccess) > 1 ){
				$rapport->SetFont('helvetica', 'B', 12);
				$rapport->Cell(0, 0, $allMag[$ID_MAGASIN]["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
			}
			// Va chercher les données UTILISATEUR
			foreach ( $dataMagasin["lignes"] as  $dataFromUser){
			    
				$listChamps = [];
				$listChamps[] = $dataFromUser["prenom"]." ".$dataFromUser["nom"];
				$listChamps[] = nfsnd($dataFromUser['nbreFactures']);
				$listChamps[] = nfsnd($dataFromUser['nbEscomptes']); //nfsnd:por les $$ 
				$listChamps[] = nfs($dataFromUser['totalEscomptes']);//nfs:por les integer 
				$listChamps[] = nfs($dataFromUser['totalsanstaxe']);
				$listChamps[] = nfs($dataFromUser['totalavectaxe']);
				//Écrire les lignes au PDF
				$rapport->writeLigneRapport3wrap( $listChamps );
			}
			// Va chercher les données MAGASIN  
			$listTOTAL = [];
			$listTOTAL[] = "";
			$listTOTAL[] = nfsnd($dataMagasin['nb_Factures']);
			$listTOTAL[] = nfsnd($dataMagasin['nbEscomptes']);
			$listTOTAL[] = nfs($dataMagasin['totalEscomptes']);
			$listTOTAL[] = nfs($dataMagasin['total_sansTaxe']);
			$listTOTAL[] = nfs($dataMagasin['total_avecTaxe']);
			//Écrire les lignes au PDF
			$rapport->writeLigneGrandTotal($listTOTAL,[false,true,true,true,true,true]);
	}
	
	ob_clean();
	$rapport->Output( formatFileName($titreFichier).'.pdf', 'I');
	die("");
}
?>
<!--DEBUT SESSION-->
<section id="main" class="main-wrap bgc-white-darkest print" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("Rapport de ventes par utilisateur","o");?></span>
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
    		<?php if(!empty($_GET["from"]) && !empty($_GET["to"])){?>
    		<h6>Du <?= formatDateUTF8($_GET["from"])?> au <?= formatDateutf8($_GET["to"])?></h6>
    		<?php }?>
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
									    <!--FORM -->
										<form method="get" id="formListRapToilettage">
											<input type="hidden" name="p" value="<?= $_GET["p"]?>">
											<div class="row" style="margin-bottom:15px;">
												<div class="col-md-8">
													<div class="input-group bs-datepicker input-daterange picker-range">
														<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities($_GET["from"])?>">
														<span class="input-group-addon px-3"><?= L("to"); ?></span>
														<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($_GET["to"]) ?>">  
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
											<div class="columns columns-right btn-group pull-right no-print">
												<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('afficher');?></button>
											</div>
										</form>
									</div>
									<?php
    								if ( $data ){
    									foreach ($data as $ID_MAGASIN => $dataUnMagasin){
                                            ?>  
    										<h3><b>Animo Etc <?= $allMag[$ID_MAGASIN]["M_NOM"]?></b></h3>
    								       	<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
    										    <!-- Table  -->
    											<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
        											    <!-- Table thead -->
        												<thead>
        													<tr>
        														<th>
    														    	<?=  L("utilisateurs","o") ?>    
        														</th>
        														<th style="text-align:center;">
        															<?=  L('nbrefact'); ?>
        														</th>
        														<th style="text-align:center;">
        															Nbre d'escomptes accordées
        														</th>
        														<th style="text-align:center;">
        															<?=  L('totalEscomptes'); ?>
        														</th>
        														<th style="text-align:right;">
        															<?=  $L['totalsanstaxes']; ?>
        														</th>
        														<th style="text-align:right;">
        															<?=  $L['totaltaxes']; ?>
        														</th>
        													</tr>
        												</thead>
        												<!-- Table body -->
        												<tbody id="listToilettages">
        												    <?php // Pour chaque données d'un utilisateur ?>
        												    <?php foreach ( $dataUnMagasin["lignes"] as $user ){ ?>
        													<tr>
        														<td>
                                                                    <?= $user["prenom"] . " " . $user["nom"] ?>
        														</td>
        														<td style="text-align:center;">
        															<?=  $user['nbreFactures']; ?>
        														</td>
        														<td style="text-align:center;">
        														    <!--Nbre d'escomptes accordées	-->
        														    <?=  $user["nbEscomptes"]; ?>
        														</td>
        														<td style="text-align:center;">
        															<?= money_format('%n', $user['totalEscomptes']); ?>
        														</td>
        														<td style="text-align:right;">
        															<?php 
        															echo money_format('%n', $user['totalsanstaxe']);
        															?>
        														</td>
        														<td style="text-align:right;">
        															<?php 
        															echo money_format('%n', $user['totalavectaxe']);
        															?>
        														</td>
        													</tr>
        													<?php }?>
            											</tbody>
            											<!-- table foot nfsnd  -->
            											<tfoot>
            												<tr style="font-weight:bold">
            													<td></td>
            													<td style="text-align:center;"><?= $dataUnMagasin["nb_Factures"]; ?></td>
            													<td style="text-align:center;"><?= $dataUnMagasin["nbEscomptes"];  ?></td>
            													<td style="text-align:center;"><?= formatPrix($dataUnMagasin["totalEscomptes"])?></td>
            												    <td style="text-align:right;"><?php echo  money_format('%n', $dataUnMagasin["total_sansTaxe"]);?></td>
            													<td style="text-align:right;"><?php echo  money_format('%n', $dataUnMagasin["total_avecTaxe"]);?></td>
            												</tr>
            											</tfoot>
        											</table>
        									</div> 
        									<br><br>
         									<?php
								        }//fin foreatc
									} // fin if
									?><!-- End foreach -->
								
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
<!--FIN SESSION-->

