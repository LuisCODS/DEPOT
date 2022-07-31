<?php
ini_set("memory_limit","256M");
set_time_limit(300);

//_____________________________________________________________________
 
//On recupere toutes les entités magasins 
$allMag = [];
$queryAllMag = query("select * 
                      from MAGASIN 
                      where caisse_db is not null
                      order by M_NOM asc",[],$mysqli);
                      
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}

// ______________________ GESTION D'ACCÈS AUX MAGASINS______________________________________

//Stock les IDs des magasins dont l'utilisateur a droit d'acceder
$listID_MAGASINcanaccess = [];
// S'il s'agit du propriétaire ou ses employés
if ( $_SESSION["utilisateur"]["security"] >= 2 ){
    //Acces limité à son magasin
	$listID_MAGASINcanaccess = array_keys($_SESSION["magasins"]);
//niveau Solutions66 et Siège social
} else {
    //Accès à tous les magasins 
	$listID_MAGASINcanaccess = array_keys($allMag);
}

 //____________________ Traite le select magasin________________________________

 //Stock les IDs des magasins qui seront affichés
$listID_MAGASIN = [];

// Si choix de magasin dans le dropdown
if ( isset($_GET["ID_MAGASIN"]) ){
    //....pour chaque choix
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) {
	    //...si le choix du magasin fait parti de ceux qu'il a le droit d'accéder 
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanaccess) ){
		    //Stock son id 
		    $listID_MAGASIN[] = $ID_MAGASIN;
		}
	}
}

// Affichage par défaut selon niveau d'acces 
if ( sizeof($listID_MAGASIN) < 1 ){
	$listID_MAGASIN = $listID_MAGASINcanaccess;
}

//recolte de données pour SQL
$listAND = [];
$listPARAM = [];
$listAND['dummy'] = '1=1';
//Date défault
$from = date("Y-m-d",strtotime('-180 day'));
$to = date("Y-m-d");

 //____________________ GESTION DES DATE _______________________________
 
//Si les 2 dates sont fournies
if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) ){
	$listAND['date'] = '(facture.date_insert >= ? AND facture.date_insert <= ?) ';
	$from = $_GET['from'];
	$to = $_GET['to'];
	$listPARAM['from'] = $from . ' 00:00:00';
	$listPARAM['to'] = $to . ' 23:59:59';
	
//Si seulement la date de départ est fournie
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) ){
	$from = $_GET['from'];
	$to = "";
	$listAND['date'] = '(facture.date_insert >= ? ) ';
	$listPARAM['from'] = $from . ' 00:00:00';
	
//Si seulement la date de fin est fournie
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) ){
	$from = "";
	$to = $_GET['to'];
	$listAND['date'] = '( facture.date_insert <= ? ) ';
	$listPARAM['to'] = $to . ' 23:59:59';
	
} else {
	// si aucune date est fournie, affiche celle par défault
	$listAND['date'] = '(facture.date_insert >= ? AND facture.date_insert <= ?) ';
	$listPARAM['from'] = $from . ' 00:00:00';
	$listPARAM['to'] = $to . ' 23:59:59';
}


//Stock le nom de la BD du magasin
$caisse_db              = "";
$listAND['item_type']   = "facture_item.id_departement='998'"; // Carte cadeau
$arrayData              = [];
//Pour le PDF/EXCEL
$listSoustitre          = [];

// Pour chaque magasin
foreach($listID_MAGASIN as $ID_MAGASIN){
    
    // ...on crée un tableau specifique 
	$arrayData[$ID_MAGASIN] = [];
	// ...on récupère le nom de la DB
	$caisse_db = $allMag[$ID_MAGASIN]['caisse_db'];   
    // ...on crée une copie du connecteur: c'est l'instace de mysqli provenant du require dans req/sql.php
	$dbAnimoUneCaisse = $dbAnimoCaisse;  
    // ...on selectionne la BD pour la connexion
	$dbAnimoUneCaisse->select_db($caisse_db); 

    //____________________ Select utilisateurs _____________________
	// si l'utiliser a le choix de plusieurs magasins, on vérifie si le user est bien dans les magasins sélectionnées
	
	//Si select d'utilisateur
	if(preg_match('#^\d+$#', $_REQUEST["staff"])){
	    //...on stock son id
		$listPARAM['staff'] = $_REQUEST["staff"];
		//...l'id du magasin en cours
		$listPARAM['mag']   = $ID_MAGASIN;  //vex($ID_MAGASIN); die();
		// si plusieurs magasins et recherche user, valide si l'utilisateur existe dans ces magasins
		// ainsi la recherche est un peu logique
		$listAND['staff_u'] = 'utilisateur.inactif is null and utilisateur.id_intranet IN( SELECT u2.id_utilisateur 
                                                                        				   from animoetc_dashboard.utilisateur_magasin as u2
                                                                        				   WHERE u2.id_utilisateur = ? AND u2.id_magasin = ?)';
	}

	$and = implode(' and ',array_values($listAND)); 

	$resultCC = query("SELECT id_facture AS NOFACT,
                              facture_item.date_insert AS DATE, 
                              label AS ARTICLE, 
                              CONCAT(prenom,' ',nom) AS NOM ,
                              montant AS MIT
        		       FROM facture_item
            		   JOIN facture USING(id_facture)
            		   JOIN utilisateur USING(id_utilisateur)
            		   WHERE $and 
            		   ORDER BY facture.id_facture DESC",array_values($listPARAM),$dbAnimoUneCaisse);
            		   

	if($resultCC->num_rows > 0){
		$arrayData[$ID_MAGASIN] = [
                        			'data'=>[],
                        			'total'=>0
                        		  ];
		$isFirstLine = true;
		while($rowRaport = $resultCC->fetch_assoc()){
			// si un utilisateur spécifique est sélectionné le mettre dans les header du PDF
			if($isFirstLine){
				$isFirstLine = false;
				if(!empty($listPARAM['staff'])){
					$listSoustitre[] = [L('utilisateur'),$rowRaport['NOM']];
				}
			}
			$arrayData[$ID_MAGASIN]['total'] += $rowRaport["MIT"];
			$arrayData[$ID_MAGASIN]['data'][] = $rowRaport;
		}
	}
	unset($dbAnimoUneCaisse);
}

// ========================= PDF / EXCEL ==========================
if ( $_GET["getFile"] == "1"  ){
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}

	$titre = L("rap_cartecadeaux","o");
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

	$listEnteteColonne = [[
		["text"=>L('fact#',"o"),"width"=>20,"align"=>"L"],
		["text"=>L('Date transac.',"o"),"width"=>30,"align"=>"L"],
		["text"=>L('utilisateur',"o"),"width"=>45,"align"=>"L"],
		["text"=>L('Article',"o"),"width"=>65,"align"=>"L"],
		["text"=>L('montant',"o"),"width"=>20,"align"=>"R"],
	]];
	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	$isfirst = true;
	foreach($arrayData as $ID_MAGASIN => $data){
		if(count($data['data']) > 0){
			if (!$isfirst){
				$rapport->addPage();
			}
			$isfirst = false;

			if( sizeof($listID_MAGASINcanaccess) > 1 ){
				$rapport->SetFont('helvetica', 'B', 12);
				$rapport->Cell(0, 0, $allMag[$ID_MAGASIN]["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
			}
			foreach ( $data['data'] as $rowRaport){
				$listChamps = [];
				$listChamps[] = $rowRaport['NOFACT'];
				$listChamps[] = $rowRaport['DATE'];
				$listChamps[] = $rowRaport['NOM'];
				$listChamps[] = $rowRaport["ARTICLE"];
				$listChamps[] = nfs($rowRaport['MIT']);
				$rapport->writeLigneRapport3wrap( $listChamps );
			}
			$listTOTAL = [];
			$listTOTAL[] = "";
			$listTOTAL[] = "";
			$listTOTAL[] = "";
			$listTOTAL[] = "TOTAL";
			$listTOTAL[] = nfs($data['total']);
			$rapport->writeLigneGrandTotal($listTOTAL,[false,false,false,true,true]);
		}
	}
	ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");
}

/*
echo '<pre>';
echo htmlspecialchars(print_r($_REQUEST, true)); 
echo '</pre>';
*/

?>
<section id="main" class="main-wrap bgc-white-darkest print" role="main">
    <!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<!-- ======================= Titre ===============================-->
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("rap_cartecadeaux");?></span>
			<small id="search_label" class="print-only"></small>
		</h1>
		<h1 class="page-title pull-right fs-4 fw-light print-only"></h1>
		<!-- ======================= icons PDF Excel ===============================-->
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
	<div class="row pl-3 pr-3 mb-3 mt-3 print-top">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<div class="panel pb-0">
				<div class="panel-body">
					<div class="panel bgc-white-dark transition visible">
						<div class="panel-body panel-body-p">
							<div class="page-size-table">
							    <div style:vertical-align; class="mb-4">
                                	<p title="Ce rapport montre que les cartes cadeaux émises."><i class="fa fa-3x fa-info-circle"></i></p>
                                </div>
								<div class="bootstrap-table">
									<div class="fixed-table-toolbar no-print">
									    <!-- ============== Form ===================-->
										<form method="get">
											<input type="hidden" name="p" value="<?= attrEncode($_GET['p'])?>" />
											<div class="row" style="margin-bottom:15px;">
												 <!-- Les dates-->
												<div class="col-md-6">
													<div class="input-group bs-datepicker input-daterange picker-range">
														<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities($from)?>">
														<span class="input-group-addon px-3"><?= L("to"); ?></span>
														<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($to)?>">
													</div>
												</div>
												<!-- Select utilisateurs-->
												<div class="col-md-3">
													<select class="ui fluid search simple-select select-dropdown" name="staff">
														<option selected value="all">Tous les utilisateurs</option>
														<?php
														$enonce = 'SELECT DISTINCT(utilisateur.id_utilisateur),prenom,nom 
														            FROM utilisateur 
														            JOIN utilisateur_magasin USING(id_utilisateur)
														            where utilisateur.inactif is null 
														            AND utilisateur.security >= ? 
														            AND utilisateur_magasin.id_magasin IN('.implode(',',$listID_MAGASIN).') 
														            ORDER BY utilisateur.prenom,utilisateur.nom';
														            
														$resultUser = query($enonce,[get_current_security_level()],$mysqli);
														while($rowUser = $resultUser->fetch_assoc()){
															printf("<option value='%s'%s>%s</option>", $rowUser["id_utilisateur"],($rowUser["id_utilisateur"] == $_REQUEST["staff"] ? " selected" : ""),$rowUser["prenom"] . " " . $rowUser["nom"]);
														}
														?>
													</select>
												</div>
												<!-- Select magasins-->
												<?php if( sizeof($listID_MAGASINcanaccess) > 1 ){ $isMultiMag = true; ?>
													<div class="col-12">
														<div class="pt-3">
															<select class="ui fluid normal multi-selection select-dropdown form-control" name="ID_MAGASIN[]" multiple onchange="onchangeSelectMag(this,event)">
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
    											<div class="columns columns-right btn-group pull-right no-print">
    												<button type="submit" class="applyBtn btn btn-small btn-success"><?= L('afficher');?></button>
    											</div>
												
											</div>
										</form>
										<!-- ============== fin Form ===================-->
									</div>
									<div class="row">
										<div class='col-12 p-1'>
											<hr />
										</div>
										<?php
										if(count($arrayData) > 0){
											foreach($arrayData as $ID_MAGASIN => $data){
												if(count($data['data']) > 0){
													?>
													<div class="col-12 pb-5" id="<?= $allMag[$ID_MAGASIN]["CONTACT_SUFFIX"]?>">
														<h3 class="">Animo Etc <?= $allMag[$ID_MAGASIN]['M_NOM']?></h3>
														<div class="table-responsive">
														    <!-- ============== table ===================-->
															<table class="table table-condensed">
																<thead>
																	<tr>
																		<th class="text-left"><?= L("no_facture","o") ?></th>
																		<th class="text-left"><?= L("tr_date","o") ?></th>
																		<th class="text-left"><?= L("util_caisse","o") ?></th>
																		<th class="text-left"><?= L("article","o") ?></th>
																		<th class="text-right"><?= L("montant") ?></th>
																	</tr>
																</thead>
																<tbody>
																	<?php
																	foreach($data['data'] as $rowRaport){?>
																		<tr>
																			<td style="text-align:left;">
																				<a class="ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?= $rowRaport["NOFACT"]?>&ID_MAGASIN=<?php echo $ID_MAGASIN?>"><?= $rowRaport["NOFACT"]?></a>
																			</td>
																			<td style="text-align:left;">
																				<?= $rowRaport["DATE"]?>
																			</td>
																			<td style="text-align:left;">
																				<?= $rowRaport["NOM"] ?>
																			</td>
																			<td style="text-align:left;">
																				<?= $rowRaport["ARTICLE"]?>
																			</td>
																			<td style="text-align:right;">
																				<?= nfs($rowRaport["MIT"]);?>
																			</td>
																		</tr>
																		<?php
																	}
																	if(count($data['data']) > 0){
																		?>
																		<tfoot style="font-weight:bold;">
																			<tr>
																				<td></td>
																				<td></td>
																				<td></td>
																				<td class="droit">
																					Total
																				</td>
																				<td style="text-align:right;"><?= nfs($data['total'])?></td>
																			</tr>
																		</tfoot>
																		<?php
																	}?>
																</tbody>
															</table>
														</div>
													</div>
													<?php
												}
											}
										}?>
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
	<!-- END CONTENT -->
