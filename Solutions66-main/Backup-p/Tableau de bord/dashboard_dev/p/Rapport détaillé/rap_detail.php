<?php

ini_set("memory_limit","256M");
set_time_limit(300);

$data = ["carteCadeauEmis"=>["nb"=>0,"montant"=>0],"carteCadeauRecu"=>["nb"=>0,"montant"=>0],"paidout"=>["montant"=>0,"nb"=>0], "animodollars"=>["montant"=>0,"nb"=>0], "escompte"=>["lignes"=>[],"nb"=>0,"montant"=>0],
		"paiement"=>["lignes"=>[],"nb"=>0,"montant"=>0], "departement"=>["lignes"=>[],"nb"=>0,"montant"=>0], "user"=>["lignes"=>[],"nb"=>0,"montant"=>0]
		];

$a = floatval( date("Y") );
$m = floatval( date("m") );
$m -= 1;
if ($m<1){$m = 12;$a -= 1;}

if ( $_GET['from'] == "" ){
	$_GET['from'] = sprintf( "%04d-%02d-01", $a,$m );
}
if ( $_GET['to'] == "" ){
	$_GET['to'] = getDateLastDayMonth( $a, $m );
}



if ( $_GET["from"] or $_GET["to"] ){
	if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
		$daterange = " where (facture.date_insert >= '{$_GET['from']} 00:00:00' AND facture.date_insert <= '{$_GET['to']} 23:59:59') ";
		$daterangeCommande = " where (commande_speciale_paiement.date_insert >= '{$_GET['from']} 00:00:00' AND commande_speciale_paiement.date_insert <= '{$_GET['to']} 23:59:59') ";
	} else if ( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) ){
		$daterange = " where (facture.date_insert >= '{$_GET['from']} 00:00:00' ";
		$daterangeCommande= " where (commande_speciale_paiement.date_insert >= '{$_GET['from']} 00:00:00' ";
	} else if ( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
		$daterange = " where facture.date_insert <= '{$_GET['to']} 23:59:59') ";
		$daterangeCommande= " where commande_speciale_paiement.date_insert <= '{$_GET['to']} 23:59:59') ";
	}


	//Générale
	$queryRaport = "SELECT sum(soustotal) `totalsanstaxe`, sum(grandtotal) `totalavectaxe`, sum(taxe1) TPS, sum(taxe2) TVQ,  sum(remise) `remise`, sum(remiseBrute) `remiseBrute`
					FROM facture ".$daterange;
	$resulRaport = $dbAnimoCaisse->query($queryRaport) or die($dbAnimoCaisse->error);;
	$data["general"] = $resulRaport->fetch_assoc();

 
	//Escompte
	$data["escompte"] = ["lignes"=>[],"montant"=>0,"nb"=>0];
	$queryEscompte = "SELECT label, sum(montant) `montant`, count(id_facture_item) `nb` FROM facture INNER JOIN facture_item USING(id_facture) $daterange AND (type LIKE 'ESCOMPTE%' or type LIKE 'SPECIAUX%')  GROUP BY label";
	$resulEscompte = $dbAnimoCaisse->query($queryEscompte);
	while ( $uneLigne = $resulEscompte->fetch_assoc() ){
		//$uneLigne["label"] = trim($uneLigne["label"]);
		$data["escompte"]["lignes"][] = $uneLigne;
		$data["escompte"]["montant"] += $uneLigne["montant"];
		$data["escompte"]["nb"] += $uneLigne["nb"];
	}


	//Paid out
	//$queryPayout = "SELECT sum(montant) `montant`, count(id_facture_item) `nb` FROM facture INNER JOIN facture_item USING(id_facture) $daterange AND id_departement = 999 GROUP BY id_departement";
	//$resulPayout = $dbAnimoCaisse->query($queryPayout);
	//$data["paidout"] = $resulPayout->fetch_assoc();

	//Paiement
	$data["paiement"] = ["lignes"=>[],"montant"=>0,"nb"=>0];
	$queryPaiement = "SELECT `type`, `compagnie`, sum(montant) `montant`, count(id_facture_paiement) `nb` FROM facture JOIN facture_paiement USING(id_facture)  $daterange  group by `type`, `compagnie`";
	$resulpaiement = $dbAnimoCaisse->query($queryPaiement);
	while ( $uneLigne = $resulpaiement->fetch_assoc() ){
		if ( $uneLigne["type"]=="cash" and preg_match('#^CC.+$#',$uneLigne["compagnie"]) ){
			$data["carteCadeauRecu"]["montant"] += round($uneLigne["montant"] * -1, 2);
			$data["carteCadeauRecu"]["nb"] += $uneLigne["nb"];
			continue; //skip
		}

		if ( $uneLigne["type"]=="cash" and $uneLigne["compagnie"] == "points" ){
			$data["animodollars"]["montant"] += round($uneLigne["montant"] * -1, 2);
			$data["animodollars"]["nb"] += $uneLigne["nb"];
			continue; //skip
		}



		$uneLigne["label"] = L("paiement:".$uneLigne["type"].":".$uneLigne["compagnie"]);

		//"Soustraire" remise au paiement comptant
		if ( $uneLigne["type"] == "cash" and $uneLigne["compagnie"] == ""){
			$uneLigne["montant"] += $data["general"]["remise"];
		}


		$data["paiement"]["lignes"][] = $uneLigne;
		$data["paiement"]["montant"] += $uneLigne["montant"];
		$data["paiement"]["nb"] += $uneLigne["nb"];
	}



	//DEPOT recevable
	$data["depot"] = ["lignes"=>[],"montant"=>0,"nb"=>0];
	$queryDepotResevable = "SELECT sum(montant) `montant`, count(id_commande_speciale_paiement) `nb`, type, compagnie
							FROM commande_speciale_paiement
							$daterangeCommande
						GROUP BY type, compagnie";
	$resulDepotRecv = $dbAnimoCaisse->query($queryDepotResevable) or die("SQL".$dbAnimoCaisse->error);
	while ( $uneLigne = $resulDepotRecv->fetch_assoc() ){
		$uneLigne["label"] = L("paiement:".$uneLigne["type"].":".$uneLigne["compagnie"]);

		//if ( $uneLigne["type"]=="cash" and preg_match('#^CC.+$#',$uneLigne["compagnie"]) ){
		//	$uneLigne["label"] = L("Carte-cadeau #") . $uneLigne["compagnie"];
		//}

		$data["depot"]["lignes"][] = $uneLigne;
		$data["depot"]["montant"] += $uneLigne["montant"];
		$data["depot"]["nb"] += $uneLigne["nb"];
	}


	//DEPARTEMENTS
	$data["departement"] = ["lignes"=>[],"montant"=>0,"nb"=>0];
	$queryDepartement = "select facture_item.montant, facture_item.nb, facture_item.type, COALESCE(depA.nom, depB.nom) `label`, COALESCE(depA.id_departement, depB.id_departement) `id_dep`
						from facture_item
								join facture USING(id_facture)
								left join article using( id_article )
								left join departement `depA` on ( depA.id_departement = article.id_departement )
								left join departement `depB` on ( depB.id_departement = facture_item.id_departement )
						$daterange
							AND COALESCE(depA.id_departement, depB.id_departement) IS NOT NULL ";
	//echo $queryDepartement;
	$resulDepartement = $dbAnimoCaisse->query($queryDepartement);
	while ( $uneLigne = $resulDepartement->fetch_assoc() ){
		if ( $uneLigne["id_dep"] == "998" ){ //carte cadeau
			$data["carteCadeauEmis"]["montant"] += $uneLigne["montant"];
			$data["carteCadeauEmis"]["nb"] += $uneLigne["nb"];

			$data["general"]["totalsanstaxe"] -= $uneLigne["montant"];
			$data["general"]["totalavectaxe"] -= $uneLigne["montant"];
			continue;
		}
		if ( $uneLigne["id_dep"] == "999" ){ //paidout
			$data["paidout"]["montant"] += $uneLigne["montant"];
			$data["paidout"]["nb"] += $uneLigne["nb"];

			//$data["general"]["totalsanstaxe"] -= $uneLigne["montant"];
			//$data["general"]["totalavectaxe"] -= $uneLigne["montant"];
			continue;
		}

		if ( !isset($data["departement"]["lignes"][$uneLigne["id_dep"]]) )
			$data["departement"]["lignes"][$uneLigne["id_dep"]] = ["id_dep"=>$uneLigne["id_dep"],"label"=>$uneLigne["label"],"montant"=>0,"nb"=>0];

		$data["departement"]["lignes"][$uneLigne["id_dep"]]["montant"] += $uneLigne["montant"];
		if ( in_array($uneLigne["type"], ["DEP","PLU"] ) ){
			$data["departement"]["lignes"][$uneLigne["id_dep"]]["nb"] += $uneLigne["nb"];
		}

		$data["departement"]["montant"] += $uneLigne["montant"];
		$data["departement"]["nb"] += $uneLigne["nb"];
	}


	//UTILISATEURS
	$data["user"] = ["lignes"=>[],"montant"=>0,"nb"=>0];
	$queryUsers = "SELECT concat(prenom, ' ', nom) `label`, sum(facture_item.montant) montant, count(distinct id_facture) nb
					FROM facture_item
						join facture USING(id_facture)
						JOIN utilisateur USING(id_utilisateur)
					$daterange
					and (facture_item.id_departement is null or facture_item.id_departement < 990)
				GROUP BY id_utilisateur";
	$resulUsers = $dbAnimoCaisse->query($queryUsers);
	while ( $uneLigne = $resulUsers->fetch_assoc() ){
		$data["user"]["lignes"][] = $uneLigne;
		$data["user"]["montant"] += $uneLigne["montant"];
		$data["user"]["nb"] += $uneLigne["nb"];
	}



	//ORDER ESCOMPTE
	uasort( $data["escompte"]["lignes"], function($a,$b){

		if ( mb_substr($a["label"],0,8) == "Escompte" and mb_substr($b["label"],0,8) == "Escompte"){
			$aPourcent = 0;
			$bPourcent = 0;
			if ( preg_match('#(\d+)%#',$a["label"],$matches) ){
				$aPourcent = intval($matches[1]);
			}
			if ( preg_match('#(\d+)%#',$b["label"],$matches) ){
				$bPourcent = intval($matches[1]);
			}

			if ( $aPourcent < $bPourcent ){
				return -1;
			} elseif( $aPourcent > $bPourcent ){
				return 1;
			}
		} else if( mb_substr($a["label"],0,8) == "Escompte"){
			return -1;
		} else if( mb_substr($b["label"],0,8) == "Escompte" ) {
			return 1;
		} else {
			if ( strtoupper($a["label"]) < strtoupper($b["label"]) ){
				return -1;
			} elseif( strtoupper($a["label"]) > strtoupper($b["label"]) ){
				return 1;
			}
		}
		return 0;
	});

	//vex($data["escompte"]["lignes"]);


	uasort( $data["departement"]["lignes"], function($a,$b){
		if ( mb_strtoupper($a["label"]) < mb_strtoupper($b["label"])){
			return -1;
		} elseif ( mb_strtoupper($a["label"]) > mb_strtoupper($b["label"])){
			return 1;
		}
		return 0;
	});

	uasort( $data["paiement"]["lignes"], function($a,$b){
		if ( mb_strtoupper($a["label"]) < mb_strtoupper($b["label"])){
			return -1;
		} elseif ( mb_strtoupper($a["label"]) > mb_strtoupper($b["label"])){
			return 1;
		}
		return 0;
	});


	//vex($data);
}


if ( $_GET["getFile"] == "1" and $data ){
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}


	$titre = L("rap_detail","o");

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


	#Fake préparation, utile pour le xlsx
	$listEnteteColonne = [
			[ ["text"=>"","width"=>45,"align"=>"L"], ["text"=>"","width"=>25,"align"=>"C"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
			[ ["text"=>"paiement","width"=>45,"align"=>"L"], ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
			[ ["text"=>"escompte","width"=>45,"align"=>"L"], ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
			[ ["text"=>"paiement","width"=>45,"align"=>"L"], ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
			[ ["text"=>"département","width"=>45,"align"=>"L"], ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne, RPDF_SKIP_PRINT_ENTETE);
	$rapport->setInfoCols(-1);


	//Sommaire
	$rapport->SetFont("helvetica","B",14);
	$rapport->Cell(0,0,L("sommaire","o"), 0, 1);


	$rapport->listLigneEnteteColonne = [
		[ ["text"=>"","width"=>45,"align"=>"L"], ["text"=>"","width"=>25,"align"=>"C"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
	];
	$rapport->setInfoCols(-1);
	$rapport->printEntetes(-1);

	$listChamps = [];
	$listChamps[] = L("Total des ventes");
	$listChamps[] = "";
	$listChamps[] = nfs($data["general"]["totalsanstaxe"] - $data["escompte"]["montant"]);
	$rapport->writeLigneRapport3wrap( $listChamps );


	//escomptes
	foreach ( $data["escompte"]["lignes"] as $uneLignePaiement){
		$listChamps = [];
		$listChamps[0] = $uneLignePaiement["label"];
		$listChamps[1] = $uneLignePaiement["nb"];
		$listChamps[2] = nfs($uneLignePaiement["montant"]);

		$rapport->writeLigneRapport3wrap( $listChamps );
	}

	$rapport->SetFont('helvetica', 'B', 8);
	$rapport->writeLigneRapportWrap( [ L("total des escomptes","o"),$data["escompte"]["nb"],nfs($data["escompte"]["montant"])], true );

	$rapport->SetFont('helvetica', 'B', 8);
	$rapport->writeLigneRapportWrap( [ L("total des ventes","o"),"",nfs($data["general"]["totalsanstaxe"])], true);

	$listChamps = [];
	$listChamps[] = L("taxes:1");
	$listChamps[] = "";
	$listChamps[] = nfs($data["general"]["TPS"]);
	$rapport->writeLigneRapportWrap( $listChamps );

	$listChamps = [];
	$listChamps[] = L("taxes:2");
	$listChamps[] = "";
	$listChamps[] = nfs($data["general"]["TVQ"]);
	$rapport->writeLigneRapportWrap( $listChamps );



	$rapport->SetFont('helvetica', 'B', 8);
	$rapport->writeLigneRapportWrap( [ L("total des ventes (avec taxes)","o"),"",nfs($data["general"]["totalsanstaxe"] + $data["general"]["TPS"] + $data["general"]["TVQ"])], true );



	$listChamps = [];
	$listChamps[] = L("carte cadeau émises","o");
	$listChamps[] = $data["carteCadeauEmis"]["nb"];
	$listChamps[] = nfs($data["carteCadeauEmis"]["montant"]);
	$rapport->writeLigneRapportWrap( $listChamps );

	$listChamps = [];
	$listChamps[] = L("carte cadeau reçues","o");
	$listChamps[] = $data["carteCadeauRecu"]["nb"];
	$listChamps[] = nfs($data["carteCadeauRecu"]["montant"]);
	$rapport->writeLigneRapportWrap( $listChamps );

	$listChamps = [];
	$listChamps[] = L("total des cartes cadeaux","o");
	$listChamps[] = "";
	$listChamps[] = nfs($data["carteCadeauEmis"]["montant"] + $data["carteCadeauRecu"]["montant"]);
	$rapport->SetFont('helvetica', 'B', 8);
	$rapport->writeLigneRapportWrap( $listChamps, true );

	$listChamps = [];
	$listChamps[] = L("Animo Dollars reçus","o");
	$listChamps[] = $data["animodollars"]["nb"];
	$listChamps[] = nfs($data["animodollars"]["montant"]);
	$rapport->writeLigneRapportWrap( $listChamps );

	$listChamps = [];
	$listChamps[] = L("paidout","o");
	$listChamps[] = $data["paidout"]["nb"];
	$listChamps[] = nfs($data["paidout"]["montant"]);
	$rapport->writeLigneRapportWrap( $listChamps );

	$listChamps = [];
	$listChamps[] = L("ajustement monnaie","o");
	$listChamps[] = "";
	$listChamps[] = nfs($data["general"]["remise"] - $data["general"]["remiseBrute"]);
	$rapport->writeLigneRapportWrap( $listChamps );


	$listChamps = [];
	$listChamps[] = L("grand total","o");
	$listChamps[] = "";
	$listChamps[] = nfs( $data["general"]["totalsanstaxe"] + $data["general"]["TPS"] + $data["general"]["TVQ"] +
					$data["carteCadeauEmis"]["montant"] + $data["carteCadeauRecu"]["montant"] + $data["paidout"]["montant"] + $data["animodollars"]["montant"] +
					($data["general"]["remise"] - $data["general"]["remiseBrute"]) );
	//$rapport->SetFont('helvetica', 'B', 10);
	$rapport->writeLigneGrandTotal( $listChamps, [false,false,true] );




	//Sommaire des paiements
	$rapport->Ln(5);
	$rapport->SetFont("helvetica","B",14);
	$rapport->Cell(0,0,L("sommaire des paiements","o"), 0, 1);


	$rapport->listLigneEnteteColonne = [
			[ ["text"=>"paiement","width"=>45,"align"=>"L"], ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
	];
	$rapport->setInfoCols(-1);
	$rapport->printEntetes(-1);

	foreach ( $data["paiement"]["lignes"] as $uneLignePaiement){
		$listChamps = [];
		$listChamps[0] = $uneLignePaiement["label"];
		$listChamps[1] = $uneLignePaiement["nb"];
		$listChamps[2] = nfs($uneLignePaiement["montant"]);

		$rapport->writeLigneRapport3wrap( $listChamps );
	}

	$rapport->writeLigneGrandTotal( [ null,$data["paiement"]["nb"],nfs($data["paiement"]["montant"])], [false,true,true] );




	//Ventes par départements
	$rapport->Ln(5);
	$rapport->SetFont("helvetica","B",14);
	$rapport->Cell(0,0,L("ventes par départements","o"), 0, 1);

	$rapport->listLigneEnteteColonne = [
			[ ["text"=>"département","width"=>45,"align"=>"L"], ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
	];
	$rapport->setInfoCols(-1);
	$rapport->printEntetes(-1);

	foreach ( $data["departement"]["lignes"] as $uneLignePaiement){
		$listChamps = [];
		$listChamps[0] = $uneLignePaiement["label"];
		$listChamps[1] = $uneLignePaiement["nb"];
		$listChamps[2] = nfs($uneLignePaiement["montant"]);

		$rapport->writeLigneRapport3wrap( $listChamps );
	}

	$rapport->writeLigneGrandTotal( [ null,$data["departement"]["nb"],nfs($data["departement"]["montant"])], [false,true,true] );




	//Ventes par utilisateurs
	$rapport->Ln(5);
	$rapport->SetFont("helvetica","B",14);
	$rapport->Cell(0,0,L("ventes par utilisateurs","o"), 0, 1);
	$rapport->Ln(2);

	$rapport->listLigneEnteteColonne = [
			[ ["text"=>"utilisateur","width"=>45,"align"=>"L"], ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
	];
	$rapport->setInfoCols(-1);
	$rapport->printEntetes(-1);

	foreach ( $data["user"]["lignes"] as $uneLignePaiement){
		$listChamps = [];
		$listChamps[0] = $uneLignePaiement["label"];
		$listChamps[1] = $uneLignePaiement["nb"];
		$listChamps[2] = nfs($uneLignePaiement["montant"]);

		$rapport->writeLigneRapport3wrap( $listChamps );
	}

	$rapport->writeLigneGrandTotal( [ null,$data["user"]["nb"],nfs($data["user"]["montant"])], [false,true,true] );






	if ( sizeof($data["depot"]["lignes"]) > 0  ){
		//Sommaire des dépôts
		$rapport->Ln(5);
		$rapport->SetFont("helvetica","B",14);
		$rapport->Cell(0,0,L("sommaire des dépôts","o"), 0, 1);

		$rapport->listLigneEnteteColonne = [
				[ ["text"=>"paiement","width"=>45,"align"=>"L"], ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
		];
		$rapport->setInfoCols(-1);
		$rapport->printEntetes(-1);

		foreach ( $data["depot"]["lignes"] as $uneLignePaiement){
			$listChamps = [];
			$listChamps[0] = $uneLignePaiement["label"];
			$listChamps[1] = $uneLignePaiement["nb"];
			$listChamps[2] = nfs($uneLignePaiement["montant"]);

			$rapport->writeLigneRapport3wrap( $listChamps );
		}

		$rapport->writeLigneGrandTotal( [ null,$data["depot"]["nb"],nfs($data["depot"]["montant"])], [false,true,true] );
	}




	ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");
} else {
	?>
	<section id="main" class="main-wrap bgc-white-darkest print" role="main">
		<!-- Start SubHeader-->
		<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
			<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
				<span class="hidden-xs-down"><?= L("rap_detail","o");?></span>
			</h1>
			<h1 id="date_label" class="page-title pull-right fs-4 fw-light print-only"></h1>
			<div class="smart-links no-print">
				<ul class="nav" role="tablist">
					<?php /*?><li class="nav-item">
						<a class="nav-link clear-style aside-trigger" onclick="window.print();" href="javascript:;">
							<i class="fa fa-print"></i>
						</a>
					</li>*/?>
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
											<form method="get" id="formListRapToilettage">
												<input type="hidden" name="p" value="<?= $_GET["p"]?>">
												<div class="row" style="margin-bottom:15px;">
													<div class="col-md-8">
														<div class="input-group bs-datepicker input-daterange picker-range">
															<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities($_GET["from"])?>">
															<span class="input-group-addon px-3"><?= L("to"); ?></span>
															<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($_GET["to"])?>">
														</div>
													</div>
												</div>
												<div class="columns columns-right btn-group pull-right no-print">
													<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('afficher');?></button>
												</div>
											</form>
										</div>

										<?php
										if ( $data ){
											?>
											<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
												<div class="fixed-table-header" style="display: none;">
													<table></table>
												</div>
												<div class="fixed-table-body">

													<table id="" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
														<tbody>
															<tr>
																<td colspan="3"><h3><?= L("sommaire","o") ?></h3></td>
															</tr>

															<tr>
																<td><?= L("total des ventes","o") ?></td>
																<td></td>
																<td style="text-align:right"><?= formatPrix($data["general"]["totalsanstaxe"] - $data["escompte"]["montant"])?></td>
															</tr>



															<?php foreach ( $data["escompte"]["lignes"] as $uneLigneEsc ){
																?>
																<tr>
																	<td><?= $uneLigneEsc["label"] ?></td>
																	<td style="text-align:center"><?= $uneLigneEsc["nb"]?></td>
																	<td style="text-align:right"><?= formatPrix($uneLigneEsc["montant"])?></td>
																</tr>
																<?php
															} ?>
															<tr style="font-weight:bold">
																<td><?= L("total des escomptes","o") ?></td>
																<td style="text-align:center"><?= $data["escompte"]["nb"]?></td>
																<td style="text-align:right"><?= formatPrix($data["escompte"]["montant"])?></td>
															</tr>


															<tr style="font-weight:bold">
																<td><?= L("total des ventes","o") ?></td>
																<td style="text-align:center"></td>
																<td style="text-align:right"><?= formatPrix($data["general"]["totalsanstaxe"])?></td>
															</tr>



															<tr>
																<td><?= L("taxes:1") ?></td>
																<td></td>
																<td style="text-align:right"><?= formatPrix($data["general"]["TPS"])?></td>
															</tr>
															<tr>
																<td><?= L("taxes:2") ?></td>
																<td></td>
																<td style="text-align:right"><?= formatPrix($data["general"]["TVQ"])?></td>
															</tr>

															<tr style="font-weight:bold">
																<td><?= L("total des ventes (avec taxes)","o") ?></td>
																<td style="text-align:center"></td>
																<td style="text-align:right"><?= formatPrix($data["general"]["totalsanstaxe"] + $data["general"]["TPS"] + $data["general"]["TVQ"])?></td>
															</tr>


															<tr>
																<td><?= L("carte cadeau émises","o") ?></td>
																<td style="text-align:center"><?= $data["carteCadeauEmis"]["nb"] ?></td>
																<td style="text-align:right"><?= formatPrix($data["carteCadeauEmis"]["montant"])?></td>
															</tr>
															<tr>
																<td><?= L("carte cadeau reçues","o")?></td>
																<td style="text-align:center"><?= $data["carteCadeauRecu"]["nb"] ?></td>
																<td style="text-align:right"><?= formatPrix($data["carteCadeauRecu"]["montant"])?></td>
															</tr>
															<tr style="font-weight:bold">
																<td><?= L("total des cartes cadeaux","o") ?></td>
																<td style="text-align:center"></td>
																<td style="text-align:right"><?= formatPrix($data["carteCadeauEmis"]["montant"] + $data["carteCadeauRecu"]["montant"] )?></td>
															</tr>



															<tr>
																<td><?= L("Animo Dollars reçus","o") ?></td>
																<td style="text-align:center"><?= $data["animodollars"]["nb"] ?></td>
																<td style="text-align:right"><?= formatPrix($data["animodollars"]["montant"])?></td>
															</tr>
															<tr>
																<td><?= L("paidout","o") ?></td>
																<td style="text-align:center"><?= $data["paidout"]["nb"] ?></td>
																<td style="text-align:right"><?= formatPrix($data["paidout"]["montant"])?></td>
															</tr>
															<tr>
																<td><?= L("ajustement_monnaie","o") ?></td>
																<td></td>
																<td style="text-align:right"><?= formatPrix($data["general"]["remise"] - $data["general"]["remiseBrute"])?></td>
															</tr>

															<tr style="font-weight:bold">
																<td>Grand-total</td>
																<td></td>
																<td style="text-align:right;"><?= formatPrix( $data["general"]["totalsanstaxe"] + $data["general"]["TPS"] + $data["general"]["TVQ"] +
																											$data["carteCadeauEmis"]["montant"] + $data["carteCadeauRecu"]["montant"] + $data["paidout"]["montant"] + $data["animodollars"]["montant"] +
																											($data["general"]["remise"] - $data["general"]["remiseBrute"]) );?></td>
															</tr>
















															<tr>
																<td colspan="3"><h3 class="mt-3"><?= L("sommaire des paiements","o") ?></h3></td>
															</tr>

															<?php foreach ( $data["paiement"]["lignes"] as $uneLignePaiement ){
																?>
																<tr>
																	<td><?= $uneLignePaiement["label"] ?></td>
																	<td style="text-align:center"><?= $uneLignePaiement["nb"]?></td>
																	<td style="text-align:right"><?= formatPrix($uneLignePaiement["montant"])?></td>
																</tr>
																<?php
															} ?>

															<tr style="font-weight:bold">
																<td>Total</td>
																<td style="text-align:center;"><?=  $data["paiement"]["nb"] ?></td>
																<td style="text-align:right;"><?= formatPrix( $data["paiement"]["montant"]);?></td>
															</tr>














															<tr>
																<td colspan="3"><h3 class="mt-3"><?= L("ventes par départements","o") ?></h3></td>
															</tr>

															<?php
															foreach ( $data["departement"]["lignes"] as $uneLigneDep ){
																?>
																<tr>
																	<td><?= $uneLigneDep["label"] ?></td>
																	<td style="text-align:center"><?= $uneLigneDep["nb"]?></td>
																	<td style="text-align:right"><?= formatPrix($uneLigneDep["montant"])?></td>
																</tr>
																<?php
															} ?>

															<tr style="font-weight:bold">
																<td>Grand-total</td>
																<td style="text-align:center;"><?=  $data["departement"]["nb"] ?></td>
																<td style="text-align:right;"><?= formatPrix( $data["departement"]["montant"]);?></td>
															</tr>









															<tr>
																<td colspan="3"><h3 class="mt-3"><?= L("ventes par utilisateurs","o") ?></h3></td>
															</tr>


															<?php foreach ( $data["user"]["lignes"] as $uneLigneUser ){
																?>
																<tr>
																	<td><?= $uneLigneUser["label"] ?></td>
																	<td style="text-align:center"><?= $uneLigneUser["nb"]?></td>
																	<td style="text-align:right"><?= formatPrix($uneLigneUser["montant"])?></td>
																</tr>
																<?php
															} ?>

															<tr style="font-weight:bold">
																<td>Grand-total</td>
																<td style="text-align:center;"><?=  $data["user"]["nb"] ?></td>
																<td style="text-align:right;"><?= formatPrix( $data["user"]["montant"]);?></td>
															</tr>

														</tbody>
													</table>










													<?php if ( sizeof( $data["depot"]["lignes"] ) > 0 ){ ?>
													<h3 class="mt-3"><?= L("sommaire des dépôts","o") ?></h3>
													<table id="" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
														<thead>
															<tr>
																<th><?= L("type de paiement") ?></th>
																<th style="text-align:center">Nombre</th>
																<th style="text-align:right">Montant</th>
															</tr>
														</thead>

														<tbody>
															<?php foreach ( $data["depot"]["lignes"] as $uneLigneDepot ){
																?>
																<tr>
																	<td><?= $uneLigneDepot["label"] ?></td>
																	<td style="text-align:center"><?= $uneLigneDepot["nb"]?></td>
																	<td style="text-align:right"><?= formatPrix($uneLigneDepot["montant"])?></td>
																</tr>
																<?php
															} ?>

														</tbody>

														<tfoot>
															<tr style="font-weight:bold">
																<td>Grand-total</td>
																<td style="text-align:center;"><?=  $data["depot"]["nb"] ?></td>
																<td style="text-align:right;"><?= formatPrix( $data["depot"]["montant"]);?></td>
															</tr>
														</tfoot>
													</table>
													<?php } ?>






													<h3 class="mt-3"></h3>
												</div>
											</div>
											<?php
										} ?>
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
