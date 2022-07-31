<?php

//Set la date du jour par défaut 
$date_start = date("Y-m-d") . " 00:00:00";
$date_end = date("Y-m-d") . " 23:59:59";

//Change la date choisie par l'utilisateur
if(!empty($_REQUEST["from"])){
	$date_start = date("Y-m-d",strtotime($_REQUEST["from"])) . " 00:00:00";
}
if(!empty($_REQUEST["to"])){
	$date_end = date("Y-m-d",strtotime($_REQUEST["to"])) . " 23:59:59";
}

$LIST_OPERATION = [
	"OUV" => "Ouverture de la caisse (%s)",
	"FER" => "Fermeture de la caisse",
	"PUNCH_IN" => "Connexion à la caisse",
	"PUNCH_OUT" => "Déconnexion de la caisse",
	"FACT" => "Facture %s",
	"FACT_RENV" => "Renversement de la facture %s",
	"FACT_REMB" => "Facture %s - Remboursement",
	"TIR" => "Ouverture du tiroir"
];
$arrayData = array();

// Tiroir
$enonce = "SELECT cashstart, date_insert FROM ouverture WHERE ouverture.date_insert >= ? AND ouverture.date_insert <= ?";
$resultOuverture = query($enonce,[$date_start,$date_end],$dbAnimoCaisse);
if($resultOuverture->num_rows > 0){
	while($uneOuverture = $resultOuverture->fetch_assoc()){
		$arrayData[] = [
			"TS" => strtotime($uneOuverture["date_insert"]),
			"USER" => null,
			"ACTION" => sprintf($LIST_OPERATION["OUV"],formatPrix($uneOuverture["cashstart"]))
		];
	}
}

// fermeture
$enonce = "SELECT date_insert FROM fermeture WHERE fermeture.date_insert >= ? AND fermeture.date_insert <= ?";
$resultFermerture = query($enonce,[$date_start,$date_end],$dbAnimoCaisse);
if($resultFermerture->num_rows > 0){
	while($uneFermerture = $resultFermerture->fetch_assoc()){
		$arrayData[] = [
			"TS" => strtotime($uneFermerture["date_insert"]),
			"USER" => null,
			"ACTION" => $LIST_OPERATION["FER"]
		];
	}
}

// facture
$enonce = "SELECT paiementtotal, facture.date_insert,id_facture,id_intranet,notes 
			 FROM facture JOIN utilisateur using(id_utilisateur) 
			WHERE facture.date_insert >= ? AND facture.date_insert <= ?";
$resultFacture = query($enonce,[$date_start,$date_end],$dbAnimoCaisse);
if($resultFacture->num_rows > 0){
	while($uneFacture = $resultFacture->fetch_assoc()){
		if(preg_match('#^Renversement facture \#(\d+)$#',$uneFacture["notes"],$matches)){
			// renversement
			$id_facture = $matches[1];
			$arrayData[] = [
				"TS" => strtotime($uneFacture["date_insert"]),
				"USER" => $uneFacture["id_intranet"],
				"ACTION" => sprintf($LIST_OPERATION["FACT_RENV"],$id_facture),
				"ID_FACTURE" => $id_facture
			];
		}else if($uneFacture["paiementtotal"] < 0){
			$id_facture = $uneFacture["id_facture"];
			$arrayData[] = [
				"TS" => strtotime($uneFacture["date_insert"]),
				"USER" => $uneFacture["id_intranet"],
				"ACTION" => sprintf($LIST_OPERATION["FACT_REMB"],$id_facture),
				"ID_FACTURE" => $id_facture
			];
		}else{
			$id_facture = $uneFacture["id_facture"];
			$arrayData[] = [
				"TS" => strtotime($uneFacture["date_insert"]),
				"USER" => $uneFacture["id_intranet"],
				"ACTION" => sprintf($LIST_OPERATION["FACT"],$id_facture),
				"ID_FACTURE" => $id_facture
			];
		}
	}
}

// punch
$enonce = "SELECT punch.date_insert,id_intranet,type FROM punch JOIN utilisateur using(id_utilisateur) WHERE punch.date_insert >= ? AND punch.date_insert <= ?";
$resultPunch = query($enonce,[$date_start,$date_end],$dbAnimoCaisse);
if($resultPunch->num_rows > 0){
	while($unePunch = $resultPunch->fetch_assoc()){
		if($unePunch["type"] == "IN"){
			// punch in
			$arrayData[] = [
				"TS" => strtotime($unePunch["date_insert"]),
				"USER" => $unePunch["id_intranet"],
				"ACTION" => $LIST_OPERATION["PUNCH_IN"]
			];
		}else{
			// punch out
			$arrayData[] = [
				"TS" => strtotime($unePunch["date_insert"]),
				"USER" => $unePunch["id_intranet"],
				"ACTION" => $LIST_OPERATION["PUNCH_OUT"]
			];
		}
	}
}

// TIROIR
$enonce = "SELECT tiroir.date_insert,id_intranet FROM tiroir JOIN utilisateur using(id_utilisateur) WHERE tiroir.date_insert >= ? AND tiroir.date_insert <= ?";
$resultTiroir = query($enonce,[$date_start,$date_end],$dbAnimoCaisse);
if ($resultTiroir->num_rows > 0) {
	while ($uneTiroir = $resultTiroir->fetch_assoc()) {
		$arrayData[] = [
			"TS" => strtotime($uneTiroir["date_insert"]),
			"USER" => $uneTiroir["id_intranet"],
			"ACTION" => $LIST_OPERATION["TIR"]
		];
	}
}

usort($arrayData, function ($a, $b) {
	if ($a['TS'] == $b['TS']) return 0;
	return $a['TS'] < $b['TS'] ? -1 : 1;
});


	
foreach($arrayData as $i => $entry){
	if($entry["USER"] != ""){
		$enonce = "SELECT CONCAT(prenom,' ',nom) FROM utilisateur WHERE id_utilisateur = ? LIMIT 1";
		$resultNomUser = query($enonce,[$entry["USER"]],$mysqli);
		if($resultNomUser->num_rows === 1){
			$arrayData[$i]["USER_NOM"] = $resultNomUser->fetch_row()[0];
		}
	}else{
		$arrayData[$i]["USER_NOM"] = "(N/D)";
	}
}

// ======================   GESTION TRI  =================================

$listColonneTri = ['TS','USER_NOM'];

if ( !in_array($_GET["orderby"],$listColonneTri) ){
	//Set by default
	$_GET["orderby"] = $listColonneTri[0];
}
if ( $_GET["sens"] == 'asc' ){
	$_GET["sens"] = "asc";
} else {
	$_GET["sens"] = "desc";
}

usort($arrayData, function($a,$b){
    if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
    	return ($_GET["sens"] == "desc") ? 1 : -1;//1 c plus petit, -1 plus grand 
    } elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
    	return ($_GET["sens"] == "desc") ? -1 : 1;
    }
    return 0;
});  


// ============== GESTION PDF/EXCEL  ================= 

if ( $_GET["getFile"] == "1" and $arrayData ){
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}


	$titre = L("rap_caisse","o");

	$listSoustitre = [];
	if ( $date_start and $date_end ){
		$listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $date_start) . " " . L("au") . " " . formatDateUTF8nonHTML( $date_end ) ];
	} elseif( $date_start ) {
		$listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $date_start ) ];
	} elseif( $date_end ) {
		$listSoustitre[] = ["date", L("jusqu'au") . " " . formatDateUTF8nonHTML( $date_end ) ];
	} else {
		$listSoustitre[] = ["date", "tous" ];
	}
	$listEnteteColonne = [[
		["text"=>L("Date",'o'),"width"=>60,"align"=>"L"],
		["text"=>L("Utilisateur",'o'),"width"=>60,"align"=>"L"],
		["text"=>L("Action",'o'),"width"=>60,"align"=>"L"]
	]];
	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	foreach($arrayData as $i => $entry){
		$listChamps = [];
		$listChamps[0] = date("Y-m-d H:i:s",$entry["TS"]);
		$listChamps[1] = $entry["USER_NOM"];
		$listChamps[2] = $entry["ACTION"];
		$rapport->writeLigneRapport3wrap( $listChamps );
	}
	ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");
}else{
	?>
	<section id="main" class="main-wrap bgc-white-darkest print" role="main">
		<!-- Start SubHeader-->
		<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
			<h1 class="page-title pull-left fs-4 fw-light">
				<i class="fa fa-bar-chart icon-mr fs-4"></i>
				<span class="hidden-xs-down"><?= L("rap_caisse","o");?></span>
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
											<form method="get" id="formListRapToilettage">
												<input type="hidden" name="p" value="<?= $_GET["p"]?>">
    											<input type="hidden" name="sens" value="<?= $_GET["sens"] ?>">
    											<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">
												<div class="row" style="margin-bottom:15px;">
													<div class="col-md-8">
														<div class="input-group bs-datepicker input-daterange picker-range">
															<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities(date("Y-m-d",strtotime($date_start)))?>">
															<span class="input-group-addon px-3"><?= L("to"); ?></span>
															<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities(date("Y-m-d",strtotime($date_end)))?>">
														</div>
													</div>
												</div>
												<div class="columns columns-right btn-group pull-right no-print">
													<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('afficher');?></button>
												</div>
											</form>
										</div>
										<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
											<div class="fixed-table-header" style="display: none;">
												<table></table>
											</div>
											<div class="fixed-table-body">
												<table class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
													<thead>
														<tr>
        													<th>
        														<a href="index.php?<?= rebuildQueryString(['orderby'=>'TS','sens'=>($_GET["orderby"] == 'TS' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
        														    Date <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'TS' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    														    </a>
        													</th>
        													<th>
        														<a href="index.php?<?= rebuildQueryString(['orderby'=>'USER_NOM','sens'=>($_GET["orderby"] == 'USER_NOM' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
        														    Utilisateur <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'USER_NOM' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    														    </a>
        													</th>
															<th>
																Action
															</th>
														</tr>
													</thead>
													<tbody>
														<?php
														foreach($arrayData as $i => $entry){
															?>
															<tr>
																<td>
																	<?= date("Y-m-d H:i:s",$entry["TS"])?>
																</td>
																<td>
																	<?= $entry["USER_NOM"] ?>
																</td>
																<td>
																	<?php
																	if($entry["ID_FACTURE"]){
																		echo str_replace($entry["ID_FACTURE"],'<a class="ajaxModal" data-modal-url="ajax/modals/viewFacture.php?id_facture='.$entry["ID_FACTURE"].'&ID_MAGASIN='.$_SESSION["mag"].'">'.$entry["ID_FACTURE"].'</a>',$entry["ACTION"]);
																	}else{
																		echo $entry["ACTION"];
																	}
																	?>
																</td>
															</tr>
															<?php
														}
														?>
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
<?php }?>