<?php
ini_set("memory_limit","256M");
set_time_limit(300);

//Get all magasins: sert à remplir les noms des magasin au PDF/EXCEL et aussi remplir le tableau d'acces aux niveaux < 2
$allMag = [];
$queryAllMag = query("select * from MAGASIN where caisse_db is not null order by M_NOM asc",[],$mysqli);
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}

/* ===========================  NIVEAU ACCÈS =========================*/

$listID_MAGASINcanaccess = [];
// Si franchisé  ou un des  ses employés
if ( $_SESSION["utilisateur"]["security"] >= 2 ){
    //  limite l'acces à son magasin
	$listID_MAGASINcanaccess = array_keys($_SESSION["magasins"]);
} else {
    //  Acces complet à toutes les magasins(Solutions66 et Animo)
	$listID_MAGASINcanaccess = array_keys($allMag);
}

/* ====== SOLUTIONS66 ET ANIMO (Option pour selectionner des magasins) ==============*/

$listID_MAGASIN = [];
// SI  magasins sélectionnées 
if ( isset($_GET["ID_MAGASIN"]) ){
    // Verifie le droit d'acces
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) {
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanaccess) ){
		    // récupère only les id magasins dont l'user a acces 
			$listID_MAGASIN[] = $ID_MAGASIN;
		}
	}
}

/* ======  FRANCHISÉES  ==============*/
if ( sizeof($listID_MAGASIN) < 1 ){
    // Accès  limité à son propre magasin
	$listID_MAGASIN = $listID_MAGASINcanaccess;
}

/* ==================  LA RECOLTE DE DONNES - QUERY =======================*/ 

//stock ce que ca prend pour concatener la QUERY
$listAND = [];  
$listPARAM = [];

/*
Au cas où les dates ne sont pas fournies:set $from par défauts.
$from: set la date de départs 30 jours avant la date du jour courrante;
$to: set la date du jour courrant.
*/
$from = date("Y-m-d",strtotime('-30 day'));
$to = date("Y-m-d");

// Si les 2 dates sont fournies
if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) ){
	$listAND[] = '(POINTS.DATE_INSERT >= ? AND POINTS.DATE_INSERT <= ?) ';
	$from = $_GET['from'];
	$to = $_GET['to'];
	//Formate les dates et heures au complet
	$listPARAM[] = $from . ' 00:00:00';
	$listPARAM[] = $to . ' 23:59:59';

// Si only date de depart fournie: affiche de cette derniere jusqu'à la date du jour courante 
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) ){
	$from = $_GET['from'];
	$to = "";
	$listAND[] = '(POINTS.DATE_INSERT >= ? ) ';
	$listPARAM[] = $from . ' 00:00:00';
	
// Si only date de la fin fournie: affiche cette derniere jusqu'à la toute première date du début 
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) ){
	$from = "";
	$to = $_GET['to'];
	$listAND[] = '( POINTS.DATE_INSERT <= ? ) ';
	$listPARAM[] = $to . ' 23:59:59';
// Si aucune date fournie: set les dates défauts 	
} else {
	//Failsafe
	$listAND[] = '(POINTS.DATE_INSERT >= ? AND POINTS.DATE_INSERT <= ?) ';
	$listPARAM[] = $from . ' 00:00:00';
	$listPARAM[] = $to . ' 23:59:59';
}

//Stock l'ensemble des donnés pour generer le PDF/Excel et le rapport  
$arrayData = [];
// recolte de donnés pour la QUERY
$listAND[] = 'POINTS.ID_MAGASIN in (?)';
$listPARAM[] = $listID_MAGASIN;
// regroupe l'ensemble des conditions de la QUERY pour matcher avec les parametres
$and = implode(' and ', $listAND);

// requête pour la table POINTS: le "date()" enleve les heures dans la requete
$resultPoints = query("SELECT date(POINTS.DATE_INSERT) as `jour`, POINTS.*
						FROM POINTS
						WHERE $and
						ORDER BY POINTS.DATE_INSERT DESC",$listPARAM,$mysqli);

while ( $uneLignePoints = $resultPoints->fetch_assoc() ){
    
	$ID_MAGASIN = $uneLignePoints["ID_MAGASIN"];
	
	// =========================== initialisation =================================
	if ( !isset($arrayData[$ID_MAGASIN]) ){
	    //initialisation (si non existant)
		$arrayData[$ID_MAGASIN] = [
			'data'=>[],
			'totalFactures'=>0,
			'totalEmis'=>0,
			'totalEmisBrut'=>0,
			'totalRecu'=>0,
			'totalPoints'=>0,
			'jour'=>0,
			'totalPointsBrut'=>0
		];
	}
	if ( !isset($arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]) ){
	    //initialisation des clés 
		$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]] = [
			"totalEmis" => 0,
			'totalEmisBrut'=>0,
			"totalRecu" => 0,
			"totalPoints" => 0,
			"totalPointsBrut" => 0,
			"totalFactures" => 0,
			"jour" => 0,
			"listFacture" => []
		];
	}
	
	
	// =================================== GESTION DES TOTEAUX ==========================================
	
	// Les points ont changé leurs calcule à partir de cette date
	if ( $uneLignePoints["DATE_INSERT"] >= "2018-08-01 00:00:00" ){
		if ( $uneLignePoints["pointsBrut"] != "" ){
		    //fait le total  pour chaque   jour
			$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["totalEmis"]     += $uneLignePoints["points"]; 
			$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["totalEmisBrut"] += $uneLignePoints["pointsBrut"];
			$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["jour"] = $uneLignePoints["jour"];//Pour faciliter le tri
			// fait le total  pour l'ensemble de jours
			$arrayData[$ID_MAGASIN]["totalEmis"]                                      += $uneLignePoints["points"];
			$arrayData[$ID_MAGASIN]["totalEmisBrut"]                                  += $uneLignePoints["pointsBrut"];
			
		} else {
			$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["totalRecu"] += $uneLignePoints["points"];
			$arrayData[$ID_MAGASIN]["totalRecu"]                                  += $uneLignePoints["points"];
		}
	} else {
		if ( $uneLignePoints["points"] >= 0 ){
		    //fait le total  pour chaque   jour
			$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["totalEmis"]     += $uneLignePoints["points"];
			$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["totalEmisBrut"] += $uneLignePoints["pointsBrut"];
			$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["jour"] = $uneLignePoints["jour"];//Pour faciliter le tri
			// fait le total  pour l'ensemble de jours
			$arrayData[$ID_MAGASIN]["totalEmis"]                                      += $uneLignePoints["points"];
			$arrayData[$ID_MAGASIN]["totalEmisBrut"]                                  += $uneLignePoints["pointsBrut"];
		} else {
			$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["totalRecu"] += $uneLignePoints["points"];
			$arrayData[$ID_MAGASIN]["totalRecu"]                                  += $uneLignePoints["points"];
		}
	}
	//fait le total  pour chaque   jour
	$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["totalPoints"]     += $uneLignePoints["points"];
	$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["totalPointsBrut"] += $uneLignePoints["pointsBrut"] ? : $uneLignePoints["points"];
	$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["jour"] = $uneLignePoints["jour"];//Pour faciliter le tri
	// fait le total  pour l'ensemble de jours
	$arrayData[$ID_MAGASIN]["totalPoints"]     += $uneLignePoints["points"];
	$arrayData[$ID_MAGASIN]["totalPointsBrut"] += $uneLignePoints["pointsBrut"] ? : $uneLignePoints["points"];
	
	// Si la facture n'est  pas dans l'indice listFacture
	if ( !in_array($uneLignePoints["id_facture"],$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["listFacture"]) ){
	    // ajoute les factures
		$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["listFacture"][] = $uneLignePoints["id_facture"];
		//fait le total de factures pour chaque jour
		$arrayData[$ID_MAGASIN]["data"][$uneLignePoints["jour"]]["totalFactures"]++;
		// fait le total de factures pour l'ensemble de jours
		$arrayData[$ID_MAGASIN]["totalFactures"]++;
	}
	

}// end while


// ======================   GESTION TRI   =================================
$listTriPosible = ["jour","totalFactures","totalEmis","totalRecu","totalPointsBrut","totalEmisBrut"];

if ( !in_array($_GET["orderby"],$listTriPosible) ){
    //Set by default
	$_GET["orderby"] = $listTriPosible[0];
}
if ( $_GET["sens"] == 'asc' ){
	$_GET["sens"] = "asc";
} else {
	$_GET["sens"] = "desc";
}

foreach ( $arrayData as $ID_MAGASIN => $data){   
    uasort($arrayData[$ID_MAGASIN]["data"] , function($a,$b){
    	if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
    		return ($_GET["sens"] == "desc") ? 1 : -1;//1 c plus petit, -1 plus grand 
    	} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
    		return ($_GET["sens"] == "desc") ? -1 : 1;
    	}
    	return 0;
    });    
}   
 //echo '<pre>' , print_r($arrayData) , '</pre>'; 


/*
$arrayData[$ID_MAGASIN] = [];

$listAND['mag'] = 'POINTS.ID_MAGASIN = ?';
$listPARAM['mag'] = $ID_MAGASIN;

$and = implode(' and ', array_values($listAND));

$resultPoints = query("SELECT date(DATE_INSERT) as `jour`,
						sum(points) as `totalPoint`,
						sum(ifnull(pointsBrut,points)) as `totalPointBrut`,
						count(ID_FACTURE) as `nbrefact`
						FROM POINTS
						WHERE $and
						GROUP BY date(DATE_INSERT) DESC
						ORDER BY POINTS.DATE_INSERT DESC",array_values($listPARAM),$mysqli);
if($resultPoints->num_rows > 0){
	$arrayData[$ID_MAGASIN] = [
		'data'=>[],
		'totalFactures'=>0,
		'totalPoints'=>0,
		'totalPointsBrut'=>0
	];
	while($rowRaport = $resultPoints->fetch_assoc()){
		$arrayData[$ID_MAGASIN]['totalFactures'] += $rowRaport['nbrefact'];
		$arrayData[$ID_MAGASIN]['totalPoints'] += $rowRaport['totalPoint'];
		$arrayData[$ID_MAGASIN]['totalPointsBrut'] += $rowRaport['totalPointBrut'];

		$arrayData[$ID_MAGASIN]['data'][] = $rowRaport;
	}
}
*/

 /* ==============================  PDF ou EXCEL =========================*/
if ( $_GET["getFile"] == "1"  ){
	require_once(__DIR__."/../req/print.php"); 
    
	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}
	
	//NIVEAU 1 DU FICHIER: titre
	$titreFichier = L("rap_pointsAnimo","o");
    //NIVEAU 2 DU FICHIER: label 
    $listSoustitreFichier = [];
    
    //SI les 2 dates ont été fournies 
	if ( !empty($from) and !empty($to) ){
		$listSoustitreFichier[] = ["Date :", L("du") . " " . formatDateUTF8nonHTML( $from ) . " " . L("au") . " " . formatDateUTF8nonHTML( $to ) ];
	// Si seulemtn la date de départs    
	} elseif( !empty($from) ) {
		$listSoustitreFichier[] = ["Date :", L("du") . " " . formatDateUTF8nonHTML( $from ) ];
	// Si seulement la date de fin    
	} elseif( !empty($to) ) {
		$listSoustitreFichier[] = ["Date :", L("jusqu'au") . " " . formatDateUTF8nonHTML( $to ) ];
	// Prend toutes les dates  
	} else {
		$listSoustitreFichier[] = ["Date :", "tous" ];
	}

	$listNomMag = [];
	
	foreach( $listID_MAGASIN as $ID_MAGASIN ){
		$listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
	}
	
	//ENCORE NIVEAU 2 DU FICHIER: Rassemble les noms de toutes magasins séparés par virgule
	$listSoustitreFichier[] = ["magasin(s) :", implode(", ",$listNomMag) ];
    
    //NIVEAU 3 DU FICHIER: les colonnes
	$listEnteteColonneFichier = [
		[
			["text"=>L('Date',"o"),"width"=>30,"align"=>"L"],["text"=>L('nbrefact',"o"),"width"=>21,"align"=>"R"],
			["text"=>L('émis',"o"),"width"=>25,"align"=>"R"],["text"=>L('émis (prélevé)',"o"),"width"=>25,"align"=>"R"],
			["text"=>L('reçu',"o"),"width"=>25,"align"=>"R"],["text"=>L('reçu (prélevé)',"o"),"width"=>25,"align"=>"R"],
			["text"=>L('total (prélevé)',"o"),"width"=>25,"align"=>"R"],
		]
	];
	// TJRS UTILISER debutSection3, PAS 1 ni 2!									
	$rapport->debutSection3($titreFichier,$listSoustitreFichier,$listEnteteColonneFichier);
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
			// display les lignes pour chaque jour
			foreach ( $data['data'] as $jour => $rowRaport){
				$listChamps = [];
				$listChamps[] = formatDateUTF8nonHTML($jour);
				$listChamps[] = nfsnd($rowRaport['totalFactures']);
				$listChamps[] = nfs($rowRaport['totalEmis']);
				$listChamps[] = nfs($rowRaport['totalEmisBrut']);
				$listChamps[] = nfs($rowRaport['totalRecu']);
				$listChamps[] = nfs($rowRaport['totalRecu']);
				$listChamps[] = nfs($rowRaport['totalPointsBrut']);
				$rapport->writeLigneRapport3wrap( $listChamps );
			}
			// display la ligne total 
			$listTOTAL = [];
			$listTOTAL[] = "TOTAL";
			$listTOTAL[] = nfsnd($data['totalFactures']);
			$listTOTAL[] = nfs($data['totalEmis']);
			$listTOTAL[] = nfs($data['totalEmisBrut']);
			$listTOTAL[] = nfs($data['totalRecu']);
			$listTOTAL[] = nfs($data['totalRecu']);
			$listTOTAL[] = nfs($data['totalPointsBrut']);
			//PArm 2: affcihage des lignes
			$rapport->writeLigneGrandTotal($listTOTAL,[false,true,true,true,true,true,true]);

		}
	}
	ob_clean();
	$rapport->Output( formatFileName($titreFichier).'.pdf', 'I');
	die("");
}


?>
<?php /*=========================== ZONE INTERFACE =========================*/ ?> 
<section id="main" class="main-wrap bgc-white-darkest print" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("rap_pointsAnimo","o");?></span>
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
										<form method="get">
											<input type="hidden" name="sens" value="<?= $_GET["sens"] ?>">
											<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">
											<input type="hidden" name="p" value="<?= attrEncode($_GET['p'])?>" />
											<div class="row" style="margin-bottom:15px;">
												<div class="col-md-8">
													<div class="input-group bs-datepicker input-daterange picker-range" data-date-start-date="2018-08-01" >
														<input type="text" class="form-control" name="from" id="from" value="<?= attrEncode($from)?>">
														<span class="input-group-addon px-3"><?= L("to"); ?></span>
														<input type="text" class="form-control" name="to" id="to" value="<?= attrEncode($to)?>">
													</div>
												</div>
												<div class="col-md-4 text-right">
													<button type="submit" class="btn btn-primary" id="btn_submit"><?= L("genererrapport","o") ?></button>
												</div>
												<?php if( sizeof($listID_MAGASINcanaccess) > 1 ){ $isMultiMag = true;?>
													<div class="col-12">
														<div class="pt-3">
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
											</div>
										</form>
									</div>
									<?php
									if(count($arrayData) > 0){
										foreach($arrayData as $ID_MAGASIN => $data){
											?>
											<div class="col-md-12 mb-5" id="<?= $allMag[$ID_MAGASIN]["CONTACT_SUFFIX"]?>">
												<h3 class="">Animo Etc <?= $allMag[$ID_MAGASIN]['M_NOM']?></h3>
												<div class="table-responsive">
													<table class="table table-condensed">
														<thead>
															<tr>
															    <!--  ==================================================================== ROWS ====================================================================================== -->
            													<td class="text-left">
   	    															<a href="index.php?<?= rebuildQueryString(['orderby'=>'jour','sens'=>($_GET["orderby"] == 'jour' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
															        	<?= L('date');?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'jour' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>  
																</th>																
																<td class="text-right">
   	    															<a href="index.php?<?= rebuildQueryString(['orderby'=>'totalFactures','sens'=>($_GET["orderby"] == 'totalFactures' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
															        	<?= L('nbrefact');?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalFactures' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>  
																</th>
																<td class="text-right">
   	    															<a href="index.php?<?= rebuildQueryString(['orderby'=>'totalEmis','sens'=>($_GET["orderby"] == 'totalEmis' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
															        	<?= L('émis');?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalEmis' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>  
																</th>
																<td class="text-right">
   	    															<a href="index.php?<?= rebuildQueryString(['orderby'=>'totalEmisBrut','sens'=>($_GET["orderby"] == 'totalEmisBrut' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
															        	<?= L('émis (prélevé)');?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalEmisBrut' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>  
																</th>
																<td class="text-right">
   	    															<a href="index.php?<?= rebuildQueryString(['orderby'=>'totalRecu','sens'=>($_GET["orderby"] == 'totalRecu' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
															        	<?= L('reçu');?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalRecu' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>  
																</th>
																<td class="text-right">
   	    															<a href="index.php?<?= rebuildQueryString(['orderby'=>'totalRecu','sens'=>($_GET["orderby"] == 'totalRecu' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
															        	<?= L('reçu (prélevé)');?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalRecu' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>  
																</th>
																<td class="text-right">
   	    															<a href="index.php?<?= rebuildQueryString(['orderby'=>'totalPointsBrut','sens'=>($_GET["orderby"] == 'totalPointsBrut' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
															        	<?= L('total (prélevé)');?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalPointsBrut' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	</a>  
																</th>
																 <!--  ==================================================================== fin ROWS ====================================================================================== -->
															</tr>
														</thead>
														<tbody>
															<?php
															if(count($data['data']) > 0){
																foreach ( $data['data'] as $jour => $rowRaport){
																	?>
																	<tr>
																		<?php if(has_rights('rap_journalier')){?>
																		<td>
																		    <a href="?p=rap_journalier&id=<?= $ID_MAGASIN?>&date=<?php echo $jour;?>">
																		        <?= formatDateUTF8($jour)?>
																		    </a>
																	    </td>
																		<?php }else{?>
																		<td>
																		    <?= formatDateUTF8($jour)?>
																	    </td>
																		<?php }?>
																		<td class="text-right"><?= nfsnd($rowRaport['totalFactures'])?></td>
																		<td class="text-right"><?= nfs($rowRaport['totalEmis'])?></td>
																		<td class="text-right"><?= nfs($rowRaport['totalEmisBrut'])?></td>
																		<td class="text-right"><?= nfs($rowRaport['totalRecu'])?></td>
																		<td class="text-right"><?= nfs($rowRaport['totalRecu'])?></td>
																		<td class="text-right"><?= nfs($rowRaport['totalPointsBrut'])?></td>
																		<?php /*
																		<td class="text-right"><?= nfs($rowRaport['totalPoints'])?></td>
																		*/ ?>
																	</tr>
																	<?php
																}
															}else{
															?>
															<tr>
																<td colspan="4">
																	Aucune donnée
																</td>
															</tr>
															<?php
															}?>
														</tbody>
														<?php
														if(count($data['data']) > 0){
															?>
															<tfoot>
																<tr>
																	<td class="text-right">
																		<strong>TOTAL</strong>
																	</td>
																	<td class="text-right"><strong><?= nfsnd($data['totalFactures'])?></strong></td>
																	<td class="text-right"><strong><?= nfs($data['totalEmis'])?></strong></td>
																	<td class="text-right"><strong><?= nfs($data['totalEmisBrut'])?></strong></td>
																	<td class="text-right"><strong><?= nfs($data['totalRecu'])?></strong></td>
																	<td class="text-right"><strong><?= nfs($data['totalRecu'])?></strong></td>
																	<td class="text-right"><strong><?= nfs($data['totalPointsBrut'])?></strong></td>
																</tr>
															</tfoot>
															<?php
														}?>
													</table>
												</div>
											</div>
											<?php
										}
									}?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</section>

