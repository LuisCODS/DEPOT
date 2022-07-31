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

// =================================== RECUPERATION DE DONNÉES  =================================================

$data = [];
foreach ($listMagasinAvecDroitAcces as $ID_MAGASIN ){
    
    // Accès la BD de  chaque magasin
    $dbAnimoCaisse->select_db($allMag[$ID_MAGASIN]["caisse_db"]);
    // Declare  la structure de stockage des donnés 
    $data[$ID_MAGASIN] = [
                          "carteCadeauEmis"=>["nb"=>0,"montant"=>0],
                          "carteCadeauRecu"=>["nb"=>0,"montant"=>0],
                          "paidout"=>["montant"=>0,"nb"=>0],
                          "animodollars"=>["montant"=>0,"nb"=>0],
                          "escompte"=>["lignes"=>[],"nb"=>0,"montant"=>0],
    	                  "paiement"=>["lignes"=>[],"nb"=>0,"montant"=>0], 
    	                  "departement"=>["lignes"=>[],"nb"=>0,"montant"=>0],
    	                  "user"=>["lignes"=>[],"nb"=>0,"montant"=>0]
    	             	];
    
    // =================================== GESTION DATE PAR DÉFAUT  =================================================
    // Set le  mois OU l'années en arrière à partir du mois et année courante 
    
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
    

    if ( $_GET["from"] or $_GET["to"] ){
        
        //  Clasure WHERE pour les tables facture et commande_speciale_paiement
        
        // Si les 2 dates sont fournis 
    	if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
    		$daterange = " where (facture.date_insert >= '{$_GET['from']} 00:00:00' AND facture.date_insert <= '{$_GET['to']} 23:59:59') ";
    		$daterangeCommande = " where (commande_speciale_paiement.date_insert >= '{$_GET['from']} 00:00:00' AND commande_speciale_paiement.date_insert <= '{$_GET['to']} 23:59:59') ";
    	// Seulement date début 	
    	} else if ( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) ){
    		$daterange = " where (facture.date_insert >= '{$_GET['from']} 00:00:00' ";
    		$daterangeCommande= " where (commande_speciale_paiement.date_insert >= '{$_GET['from']} 00:00:00' ";
    	// Seulement date fin	
    	} else if ( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
    	    
    		$daterange = " where facture.date_insert <= '{$_GET['to']} 23:59:59') ";
    		$daterangeCommande= " where commande_speciale_paiement.date_insert <= '{$_GET['to']} 23:59:59') ";
    	}
    
        // ============================= RECUPERATION ET  STOCKAGE DE DONNÉES FROM BD ================================================

    	// set data for general :  Requêtes  pour la table facture  
    	$queryRaport = "SELECT sum(soustotal) `totalsanstaxe`, sum(grandtotal) `totalavectaxe`, sum(taxe1) TPS, sum(taxe2) TVQ,  sum(remise) `remise`, sum(remiseBrute) `remiseBrute`, COUNT(id_facture)`nbFacture`
    					FROM facture ".$daterange;
    	$resulRaport = $dbAnimoCaisse->query($queryRaport) or die($dbAnimoCaisse->error);
    	$data[$ID_MAGASIN]["general"] = $resulRaport->fetch_assoc();
    	
    	
        // ======================== escompte ===================

    	$data[$ID_MAGASIN]["escompte"] = ["lignes"=>[],"montant"=>0,"nb"=>0];
    	
    	//set data for Escompte :  Requêtes  pour la table facture et facture_item
    	$queryEscompte = "SELECT label, sum(montant) `montant`, sum(facture_item.nb) `nb` 
	                      FROM facture
	                      INNER JOIN facture_item USING(id_facture)
	                      $daterange AND (type LIKE 'ESCOMPTE%' or type LIKE 'SPECIAUX%')  GROUP BY label";
    	$resulEscompte = $dbAnimoCaisse->query($queryEscompte);
    	while ( $uneLigne = $resulEscompte->fetch_assoc() ){
    	
    		$data[$ID_MAGASIN]["escompte"]["lignes"][] = $uneLigne;
    		$data[$ID_MAGASIN]["escompte"]["montant"] += $uneLigne["montant"];
    		$data[$ID_MAGASIN]["escompte"]["nb"] += $uneLigne["nb"];
    	}
        // ======================== Paiement ===================
    	
    	$data[$ID_MAGASIN]["paiement"] = ["lignes"=>[],"montant"=>0,"nb"=>0,"tip"=>0];
    	
    	$queryPaiement = "SELECT facture_paiement.`type`, facture_paiement.`compagnie`, 
    							 sum(facture_paiement.montant) `montant`, count(id_facture_paiement) `nb` , sum(facture_tip.montant) `tip`
	                        FROM facture 
	                             JOIN facture_paiement USING(id_facture) 
	                             left join facture_tip using (id_facture_paiement) 
	                      $daterange  group by `type`, `compagnie`";
    	$resulpaiement = query($queryPaiement,[],$dbAnimoCaisse);
    	
    	while ( $uneLigne = $resulpaiement->fetch_assoc() ){
    	    
    		if ( $uneLigne["type"]=="cash" and preg_match('#^CC.+$#',$uneLigne["compagnie"]) ){
    			$data[$ID_MAGASIN]["carteCadeauRecu"]["montant"] += round($uneLigne["montant"] * -1, 2);
    			$data[$ID_MAGASIN]["carteCadeauRecu"]["nb"] += $uneLigne["nb"];
    			continue; //skip
    		}
    		if ( $uneLigne["type"]=="cash" and $uneLigne["compagnie"] == "points" ){
    			$data[$ID_MAGASIN]["animodollars"]["montant"] += round($uneLigne["montant"] * -1, 2);
    			$data[$ID_MAGASIN]["animodollars"]["nb"] += $uneLigne["nb"];
    			continue; //skip
    		}
    		$uneLigne["label"] = L("paiement:".$uneLigne["type"].":".$uneLigne["compagnie"]);
    
    		//"Soustraire" remise au paiement comptant
    		if ( $uneLigne["type"] == "cash" and $uneLigne["compagnie"] == ""){
    			$uneLigne["montant"] += $data[$ID_MAGASIN]["general"]["remise"];
    		}
    		$data[$ID_MAGASIN]["paiement"]["lignes"][] = $uneLigne;
    		$data[$ID_MAGASIN]["paiement"]["montant"] += $uneLigne["montant"];
    		$data[$ID_MAGASIN]["paiement"]["nb"] += $uneLigne["nb"];
    		$data[$ID_MAGASIN]["paiement"]["tip"] += $uneLigne["tip"];
    	}
        // ======================== DEPOT recevable ===================
    	
    	$data[$ID_MAGASIN]["depot"] = ["lignes"=>[],"montant"=>0,"nb"=>0];
    	$queryDepotResevable = "SELECT sum(montant) `montant`, count(id_commande_speciale_paiement) `nb`, type, compagnie
    							FROM commande_speciale_paiement $daterangeCommande  GROUP BY type, compagnie";
    	$resulDepotRecv = $dbAnimoCaisse->query($queryDepotResevable) or die("SQL".$dbAnimoCaisse->error);
    	while ( $uneLigne = $resulDepotRecv->fetch_assoc() ){
    		$uneLigne["label"] = L("paiement:".$uneLigne["type"].":".$uneLigne["compagnie"]);
    		$data[$ID_MAGASIN]["depot"]["lignes"][] = $uneLigne;
    		$data[$ID_MAGASIN]["depot"]["montant"] += $uneLigne["montant"];
    		$data[$ID_MAGASIN]["depot"]["nb"] += $uneLigne["nb"];
    	}
    
        // ======================== DEPARTEMENTS ===================
    	
    	$data[$ID_MAGASIN]["departement"] = ["lignes"=>[],"montant"=>0,"nb"=>0];
    	$queryDepartement = "select facture_item.montant, facture_item.nb, facture_item.type, COALESCE(depA.nom, depB.nom) `label`, COALESCE(depA.id_departement, depB.id_departement) `id_dep`
    						from facture_item
    								join facture USING(id_facture)
    								left join article using( id_article )
    								left join departement `depA` on ( depA.id_departement = article.id_departement )
    								left join departement `depB` on ( depB.id_departement = facture_item.id_departement )
    						        $daterange AND COALESCE(depA.id_departement, depB.id_departement) IS NOT NULL ";
    	//echo $queryDepartement;
    	$resulDepartement = $dbAnimoCaisse->query($queryDepartement);
    	while ( $uneLigne = $resulDepartement->fetch_assoc() ){
    		if ( $uneLigne["id_dep"] == "998" ){ //carte cadeau
    			$data[$ID_MAGASIN]["carteCadeauEmis"]["montant"] += $uneLigne["montant"];
    			$data[$ID_MAGASIN]["carteCadeauEmis"]["nb"] += $uneLigne["nb"];
    			$data[$ID_MAGASIN]["general"]["totalsanstaxe"] -= $uneLigne["montant"];
    			$data[$ID_MAGASIN]["general"]["totalavectaxe"] -= $uneLigne["montant"];
    			continue;
    		}
    		if ( $uneLigne["id_dep"] == "999" ){ //paidout
    			$data[$ID_MAGASIN]["paidout"]["montant"] += $uneLigne["montant"];
    			$data[$ID_MAGASIN]["paidout"]["nb"] += $uneLigne["nb"];
    			continue;
    		}
    		if ( !isset($data[$ID_MAGASIN]["departement"]["lignes"][$uneLigne["id_dep"]]) )
    			$data[$ID_MAGASIN]["departement"]["lignes"][$uneLigne["id_dep"]] = ["id_dep"=>$uneLigne["id_dep"],"label"=>$uneLigne["label"],"montant"=>0,"nb"=>0];
    
    		$data[$ID_MAGASIN]["departement"]["lignes"][$uneLigne["id_dep"]]["montant"] += $uneLigne["montant"];
    		if ( in_array($uneLigne["type"], ["DEP","PLU"] ) ){
    			$data[$ID_MAGASIN]["departement"]["lignes"][$uneLigne["id_dep"]]["nb"] += $uneLigne["nb"];
    			$data[$ID_MAGASIN]["departement"]["nb"] += $uneLigne["nb"]; 
    		}
    		$data[$ID_MAGASIN]["departement"]["montant"] += $uneLigne["montant"];
    	}
    	
        // ======================== UTILISATEURS ===================

    	$data[$ID_MAGASIN]["user"] = ["lignes"=>[],"montant"=>0,"nb"=>0];
    	
    	$queryUsers = "SELECT concat(prenom, ' ', nom) `label`, sum(facture_item.montant) montant, count(distinct id_facture) nb
    					FROM facture_item
						join facture USING(id_facture)
						JOIN utilisateur USING(id_utilisateur)
    					$daterange
    					and (facture_item.id_departement is null or facture_item.id_departement < 990)
    				    GROUP BY id_utilisateur";
    
    	$resulUsers = $dbAnimoCaisse->query($queryUsers);
    	
    	
    	while ( $uneLigne = $resulUsers->fetch_assoc() ){
    	    
    		$data[$ID_MAGASIN]["user"]["lignes"][] = $uneLigne;
    		$data[$ID_MAGASIN]["user"]["montant"] += $uneLigne["montant"];
    		$data[$ID_MAGASIN]["user"]["nb"] += $uneLigne["nb"];
    	}
    	
    	//ORDER ESCOMPTE: Sort the array by values using a comparison function
    	uasort( $data[$ID_MAGASIN]["escompte"]["lignes"], function($a,$b){

    		if ( mb_substr($a["label"],0,8) == "Escompte" and mb_substr($b["label"],0,8) == "Escompte"){
    			$aPourcent = 0;
    			$bPourcent = 0;
    			
    			// $matches sera rempli par les résultats de la recherche.
    			if ( preg_match('#(\d+)%#',$a["label"],$matches) ){
    			    //$matches[1] contiendra le texte qui satisfait la première parenthèse capturante
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
    
    	uasort( $data[$ID_MAGASIN]["departement"]["lignes"], function($a,$b){
    		if ( mb_strtoupper($a["label"]) < mb_strtoupper($b["label"])){
    			return -1;
    		} elseif ( mb_strtoupper($a["label"]) > mb_strtoupper($b["label"])){
    			return 1;
    		}
    		return 0;
    	});
    
    	uasort( $data[$ID_MAGASIN]["paiement"]["lignes"], function($a,$b){
    		if ( mb_strtoupper($a["label"]) < mb_strtoupper($b["label"])){
    			return -1;
    		} elseif ( mb_strtoupper($a["label"]) > mb_strtoupper($b["label"])){
    			return 1;
    		}
    		return 0;
    	});
    }
    
}// fin foreach


/*
echo '<pre>';
echo htmlspecialchars(print_r($_REQUEST, true));
echo 'DATA '.htmlspecialchars(print_r($data, true));
echo '</pre>';
*/



// =================================== GESTION PDF et Excel =================================================

if ( $_GET["getFile"] == "1" and $data ){
    //Pour la manipulation de FIchier
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$objFichier = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$objFichier = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
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
        [
            ["text"=>"","width"=>45,"align"=>"L"],
            ["text"=>"","width"=>25,"align"=>"C"],
            ["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],
        ],
        [ 
            ["text"=>"paiement","width"=>45,"align"=>"L"], 
            ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],
            ["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],
        ],
        [ 
            ["text"=>"escompte","width"=>45,"align"=>"L"],
            ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],
            ["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],
        ],
        [
            ["text"=>"paiement","width"=>45,"align"=>"L"],
            ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],
            ["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],
        ],
        [ 
            ["text"=>"département","width"=>45,"align"=>"L"],
            ["text"=>L("nb",'o'),"width"=>25,"align"=>"C"],
            ["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],
        ]
	];

	$objFichier->debutSection3($titre,$listSoustitre,$listEnteteColonne, RPDF_SKIP_PRINT_ENTETE);
	$objFichier->setInfoCols(-1);

    $first = true;
    foreach ($data as $ID_MAGASIN => $dataUnMagasin){
        
        if(!$first){
            $objFichier->AddPage();
        }else{
            $first = false;
        }
    	$objFichier->SetFont("helvetica","B",14);
    	$objFichier->Ln();
    	$objFichier->Cell(0,0,$allMag[$ID_MAGASIN]["M_NOM"], 0, 1);        
        
        //Sommaire
    	$objFichier->SetFont("helvetica","B",14);
    	$objFichier->Cell(0,0,L("sommaire","o"), 0, 1);
    
    	$objFichier->listLigneEnteteColonne = [
    		[ 
    		    ["text"=>"","width"=>60,"align"=>"L"],
    		    ["text"=>"","width"=>60,"align"=>"C"],
    	    	["text"=>L("montant",'o'),"width"=>60,"align"=>"R"],
    		],
    	];
    	$objFichier->setInfoCols(-1);
    	$objFichier->printEntetes(-1);
    
    	$listChamps = [];
    	$listChamps[] = L("Total des ventes");
    	$listChamps[] = "";
    	$listChamps[] = nfs($dataUnMagasin["general"]["totalsanstaxe"] - $dataUnMagasin["escompte"]["montant"]);
    	$objFichier->writeLigneRapport3wrap( $listChamps );
    
    	//======================================= escomptes ================================== 
    	
    	if($_GET["detailsEscompte"] == "1"){
          	foreach ( $dataUnMagasin["escompte"]["lignes"] as $uneLignePaiement){
        		$listChamps = [];
        		$listChamps[0] = $uneLignePaiement["label"];
        		$listChamps[1] = $uneLignePaiement["nb"];
        		$listChamps[2] = nfs($uneLignePaiement["montant"]);
        		$objFichier->writeLigneRapport3wrap( $listChamps );
        	}  	    
    	}
    
    	$objFichier->SetFont('helvetica', 'B', 8);
    	//$objFichier->Ln();
    	$objFichier->writeLigneRapportWrap( [ L("total des escomptes","o"),$dataUnMagasin["escompte"]["nb"],nfs($dataUnMagasin["escompte"]["montant"])], true );
    	
        //======================================= ventes ================================== 
        
    	$objFichier->SetFont('helvetica', 'B', 8);
    	$objFichier->Ln();
    	$objFichier->writeLigneRapportWrap( [ L("total des ventes","o"), $dataUnMagasin["general"]["nbFacture"], nfs($dataUnMagasin["general"]["totalsanstaxe"])], true);
    
    	$listChamps = [];
    	$listChamps[] = L("taxes:1");
    	$listChamps[] = "";
    	$listChamps[] = nfs($dataUnMagasin["general"]["TPS"]);
    	$objFichier->writeLigneRapportWrap( $listChamps );
    
    	$listChamps = [];
    	$listChamps[] = L("taxes:2");
    	$listChamps[] = "";
    	$listChamps[] = nfs($dataUnMagasin["general"]["TVQ"]);
    	$objFichier->writeLigneRapportWrap( $listChamps );
    
    	$objFichier->SetFont('helvetica', 'B', 8);
    	$objFichier->Ln();
    	$objFichier->writeLigneRapportWrap( [ L("total des ventes (avec taxes)","o"),"",nfs($dataUnMagasin["general"]["totalsanstaxe"] + $dataUnMagasin["general"]["TPS"] + $dataUnMagasin["general"]["TVQ"])], true );
    
    	$listChamps = [];
    	$listChamps[] = L("carte cadeau émises","o");
    	$listChamps[] = $dataUnMagasin["carteCadeauEmis"]["nb"];
    	$listChamps[] = nfs($dataUnMagasin["carteCadeauEmis"]["montant"]);
    	$objFichier->writeLigneRapportWrap( $listChamps );
    
    	$listChamps = [];
    	$listChamps[] = L("carte cadeau reçues","o");
    	$listChamps[] = $dataUnMagasin["carteCadeauRecu"]["nb"];
    	$listChamps[] = nfs($dataUnMagasin["carteCadeauRecu"]["montant"]);
    	$objFichier->writeLigneRapportWrap( $listChamps );
    
    	$listChamps = [];
    	$listChamps[] = L("total des cartes cadeaux","o");
    	$listChamps[] = "";
    	$listChamps[] = nfs($dataUnMagasin["carteCadeauEmis"]["montant"] + $dataUnMagasin["carteCadeauRecu"]["montant"]);
    	$objFichier->SetFont('helvetica', 'B', 8);
    	$objFichier->Ln();
    	$objFichier->writeLigneRapportWrap( $listChamps, true );
    
    	$listChamps = [];
    	$listChamps[] = L("Animo Dollars reçus","o");
    	$listChamps[] = $dataUnMagasin["animodollars"]["nb"];
    	$listChamps[] = nfs($dataUnMagasin["animodollars"]["montant"]);
    	$objFichier->writeLigneRapportWrap( $listChamps );
    
    	$listChamps = [];
    	$listChamps[] = L("paidout","o");
    	$listChamps[] = $dataUnMagasin["paidout"]["nb"];
    	$listChamps[] = nfs($dataUnMagasin["paidout"]["montant"]);
    	$objFichier->writeLigneRapportWrap( $listChamps );
    
    	$listChamps = [];
    	$listChamps[] = L("ajustement monnaie","o");
    	$listChamps[] = "";
    	$listChamps[] = nfs($dataUnMagasin["general"]["remise"] - $dataUnMagasin["general"]["remiseBrute"]);
    	$objFichier->writeLigneRapportWrap( $listChamps );
    
    	$listChamps = [];
    	$listChamps[] = L("grand total","o");
    	$listChamps[] = "";
    	$listChamps[] = nfs( $dataUnMagasin["general"]["totalsanstaxe"] + $dataUnMagasin["general"]["TPS"] + $dataUnMagasin["general"]["TVQ"] +
    					$dataUnMagasin["carteCadeauEmis"]["montant"] + $dataUnMagasin["carteCadeauRecu"]["montant"] + $dataUnMagasin["paidout"]["montant"] + $dataUnMagasin["animodollars"]["montant"] +
    					($dataUnMagasin["general"]["remise"] - $dataUnMagasin["general"]["remiseBrute"]) );
    	//$objFichier->SetFont('helvetica', 'B', 10);
    	$objFichier->writeLigneGrandTotal( $listChamps, [false,false,true] );
    
    	//Sommaire des paiements
    	$objFichier->Ln(5);
    	$objFichier->SetFont("helvetica","B",14);
    	$objFichier->Ln();
    	$objFichier->Cell(0,0,L("sommaire des paiements","o"), 0, 1);
    
    	$objFichier->listLigneEnteteColonne = [
        			[ 
        			    ["text"=>L("paiement",'o'),"width"=>60,"align"=>"L"],
        			    ["text"=>L("nb",'o'),"width"=>60,"align"=>"C"],
        		    	["text"=>L("montant",'o'),"width"=>60,"align"=>"R"],
    		    	],
    	];
    	$objFichier->setInfoCols(-1);
    	$objFichier->printEntetes(-1);
    
    	foreach ( $dataUnMagasin["paiement"]["lignes"] as $uneLignePaiement){
    		$listChamps = [];
    		$listChamps[0] = $uneLignePaiement["label"];
    		$listChamps[1] = $uneLignePaiement["nb"];
    		$listChamps[2] = nfs($uneLignePaiement["montant"]);
    		$objFichier->writeLigneRapport3wrap( $listChamps );
    	}
    	
    	if ( round($dataUnMagasin["paiement"]["tip"],2) != 0 ){
    		$dataUnMagasin["paiement"]["montant"] -= $dataUnMagasin["paiement"]["tip"];
    		$objFichier->writeLigneRapport3wrap( [ "Moins : Pourboires","",nfs($dataUnMagasin["paiement"]["tip"]*-1)] );
    	}
    	
    	$objFichier->writeLigneGrandTotal( [ null,$dataUnMagasin["paiement"]["nb"],nfs($dataUnMagasin["paiement"]["montant"])], [false,true,true] );
    	
    	
    	//Ventes par départements
    	$objFichier->Ln(5);
    	$objFichier->SetFont("helvetica","B",14);
    	$objFichier->Cell(0,0,L("ventes par départements","o"), 0, 1);
    	$objFichier->listLigneEnteteColonne = [
        			[ 
        			    ["text"=>L("département",'o'),"width"=>60,"align"=>"L"],
        			    ["text"=>L("nb",'o'),"width"=>60,"align"=>"C"],
        			    ["text"=>L("montant",'o'),"width"=>60,"align"=>"R"],
        			],
    	];
    	$objFichier->setInfoCols(-1);
    	$objFichier->printEntetes(-1);
    
    	foreach ( $dataUnMagasin["departement"]["lignes"] as $uneLignePaiement){
    		$listChamps = [];
    		$listChamps[0] = $uneLignePaiement["label"];
    		$listChamps[1] = $uneLignePaiement["nb"];
    		$listChamps[2] = nfs($uneLignePaiement["montant"]);
    
    		$objFichier->writeLigneRapport3wrap( $listChamps );
    	}
    
    	$objFichier->writeLigneGrandTotal( [ null,$dataUnMagasin["departement"]["nb"],nfs($dataUnMagasin["departement"]["montant"])], [false,true,true] );
    
    	//Ventes par utilisateurs
    	$objFichier->Ln(5);
    	$objFichier->SetFont("helvetica","B",14);
    	$objFichier->Ln();
    	$objFichier->Cell(0,0,L("ventes par utilisateurs","o"), 0, 1);
    	$objFichier->Ln(2);
    
    	$objFichier->listLigneEnteteColonne = [
    			    [ 
    			        ["text"=>L("utilisateur",'o'),"width"=>60,"align"=>"L"],
    			        ["text"=>L("nb",'o'),"width"=>60,"align"=>"C"],
    			        ["text"=>L("montant",'o'),"width"=>60,"align"=>"R"],
			        ],
    	];
    	$objFichier->setInfoCols(-1);
    	$objFichier->printEntetes(-1);
    
    	foreach ( $dataUnMagasin["user"]["lignes"] as $uneLignePaiement){
    		$listChamps = [];
    		$listChamps[0] = $uneLignePaiement["label"];
    		$listChamps[1] = $uneLignePaiement["nb"];
    		$listChamps[2] = nfs($uneLignePaiement["montant"]);
    
    		$objFichier->writeLigneRapport3wrap( $listChamps );
    	}
    
    	$objFichier->writeLigneGrandTotal( [ null,$dataUnMagasin["user"]["nb"],nfs($dataUnMagasin["user"]["montant"])], [false,true,true] );
    
    	if ( sizeof($dataUnMagasin["depot"]["lignes"]) > 0  ){
    		//Sommaire des dépôts
    		$objFichier->Ln(5);
    		$objFichier->SetFont("helvetica","B",14);
    		$objFichier->Ln();
    		$objFichier->Cell(0,0,L("sommaire des dépôts","o"), 0, 1);
    
    		$objFichier->listLigneEnteteColonne = [
        				[
        				    ["text"=>"paiement","width"=>60,"align"=>"L"],
        				    ["text"=>L("nb",'o'),"width"=>60,"align"=>"C"],
        				    ["text"=>L("montant",'o'),"width"=>60,"align"=>"R"],
        				],
    		];
    		$objFichier->setInfoCols(-1);
    		$objFichier->printEntetes(-1);
    
    		foreach ( $dataUnMagasin["depot"]["lignes"] as $uneLignePaiement){
    			$listChamps = [];
    			$listChamps[0] = $uneLignePaiement["label"];
    			$listChamps[1] = $uneLignePaiement["nb"];
    			$listChamps[2] = nfs($uneLignePaiement["montant"]);
    
    			$objFichier->writeLigneRapport3wrap( $listChamps );
    		}
    		$objFichier->writeLigneGrandTotal( [ null,$dataUnMagasin["depot"]["nb"],nfs($dataUnMagasin["depot"]["montant"])], [false,true,true] );
    	}
    }//fin foreach

	ob_clean();
	$objFichier->Output( formatFileName($titre).'.pdf', 'I');
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
										    <!-- =============================================== FORM  ==================================================== -->
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
									            <!-- =============================================== SELECT  ==================================================== -->
												<?php if( sizeof($listID_MAGASINcanaccess) > 1 ){ $isMultiMag = true; ?>
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
												<!-- =============================================== checkbox  ==================================================== -->
												<div class="col-12">
												    <div class="pt-3">
                    								    <label><input type="checkbox" name="detailsEscompte" <?= $_GET['detailsEscompte'] == '1' ? 'checked ':''?>value="1" /> Escompte  détaillé</label>
                    							    </div>
                    							</div>
                    							<!-- =============================================== button  ==================================================== -->
												<div class="columns columns-right btn-group pull-right no-print">
													<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('afficher');?></button>
												</div>
											</form>
										</div>
										<?php
										if ($data){
										    foreach ($data as $ID_MAGASIN => $dataUnMagasin){  ?>
										    </br></br>
    										<h3><b>Animo Etc <?= $allMag[$ID_MAGASIN]["M_NOM"]?></b></h3>
    										<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
    											<div class="fixed-table-body">
    												<table  class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
    													<tbody>
    														<tr>
    															<td colspan="3"><h3><?= L("sommaire","o") ?></h3></td>
    														</tr>
    														<tr style="font-weight:bold">
    															<td><?= L("total des ventes","o") ?></td>
    															<td></td>
    															<td style="text-align:right"><?= formatPrix( $dataUnMagasin["general"]["totalsanstaxe"] - $dataUnMagasin["escompte"]["montant"] ) ?></td>
    														</tr>
    														<!-- ================================ ESCOMPTES ================================  -->
    														<?php 
    														if($_GET['detailsEscompte'] == "1" ){
        														foreach ( $dataUnMagasin["escompte"]["lignes"] as $uneLigneEsc ){
        															?>
        															<tr>
        																<td><?= $uneLigneEsc["label"] ?></td>
        																<td style="text-align:center"><?= $uneLigneEsc["nb"]?></td>
        																<td style="text-align:right"><?= formatPrix($uneLigneEsc["montant"])?></td>
        															</tr>
        															<?php
        														}
        													} ?>
    														<tr style="font-weight:bold">
    															<td><?= L("total des escomptes","o") ?></td>
    															<td style="text-align:center"><?= $dataUnMagasin["escompte"]["nb"]?></td>
    															<td style="text-align:right"><?= formatPrix($dataUnMagasin["escompte"]["montant"])?></td>
    														</tr>
    														<!-- ================================ FACTURE ================================  -->
    														<tr style="font-weight:bold">
    															<td><?= L("total des ventes","o") ?></td>
    															<td style="text-align:center"><?= $dataUnMagasin["general"]["nbFacture"] ?></td>
    															<td style="text-align:right"><?= formatPrix($dataUnMagasin["general"]["totalsanstaxe"])?></td>
    														</tr>
    														<tr>
    															<td><?= L("taxes:1") ?></td>
    															<td></td>
    															<td style="text-align:right"><?= formatPrix($dataUnMagasin["general"]["TPS"])?></td>
    														</tr>
    														<tr>
    															<td><?= L("taxes:2") ?></td>
    															<td></td>
    															<td style="text-align:right"><?= formatPrix($dataUnMagasin["general"]["TVQ"])?></td>
    														</tr>
    
    														<tr style="font-weight:bold">
    															<td><?= L("total des ventes (avec taxes)","o") ?></td>
    															<td style="text-align:center"></td>
    															<td style="text-align:right"><?= formatPrix($dataUnMagasin["general"]["totalsanstaxe"] + $dataUnMagasin["general"]["TPS"] + $dataUnMagasin["general"]["TVQ"])?></td>
    														</tr>
    														<!-- ==============================================================================  -->
    														<tr>
    															<td><?= L("carte cadeau émises","o") ?></td>
    															<td style="text-align:center"><?= $dataUnMagasin["carteCadeauEmis"]["nb"] ?></td>
    															<td style="text-align:right"><?= formatPrix($dataUnMagasin["carteCadeauEmis"]["montant"])?></td>
    														</tr>
    														<tr>
    															<td><?= L("carte cadeau reçues","o")?></td>
    															<td style="text-align:center"><?= $dataUnMagasin["carteCadeauRecu"]["nb"] ?></td>
    															<td style="text-align:right"><?= formatPrix($dataUnMagasin["carteCadeauRecu"]["montant"])?></td>
    														</tr>
    														<tr style="font-weight:bold">
    															<td><?= L("total des cartes cadeaux","o") ?></td>
    															<td style="text-align:center"></td>
    															<td style="text-align:right"><?= formatPrix($dataUnMagasin["carteCadeauEmis"]["montant"] + $dataUnMagasin["carteCadeauRecu"]["montant"] )?></td>
    														</tr>
    														<?php //========================= Animo Dollars reçus ================================ ?>
    														<tr>
    															<td><?= L("animo Dollars reçus","o") ?></td>
    															<td style="text-align:center"><?= $dataUnMagasin["animodollars"]["nb"] ?></td>
    															<td style="text-align:right"><?= formatPrix($dataUnMagasin["animodollars"]["montant"])?></td>
    														</tr>
    														<!-- ================================= paidout =============================================  -->
    														<tr>
    															<td><?= L("paidout","o") ?></td>
    															<td style="text-align:center"><?= $dataUnMagasin["paidout"]["nb"] ?></td>
    															<td style="text-align:right"><?= formatPrix($dataUnMagasin["paidout"]["montant"])?></td>
    														</tr>
    														<tr>
    															<td><?= L("ajustement_monnaie","o") ?></td>
    															<td></td>
    															<td style="text-align:right"><?= formatPrix($dataUnMagasin["general"]["remise"] - $dataUnMagasin["general"]["remiseBrute"])?></td>
    														</tr>
    														<tr style="font-weight:bold">
    															<td>Grand-total</td>
    															<td></td>
    															<td style="text-align:right;"><?= formatPrix( $dataUnMagasin["general"]["totalsanstaxe"] + $dataUnMagasin["general"]["TPS"] + $dataUnMagasin["general"]["TVQ"] +
    																										$dataUnMagasin["carteCadeauEmis"]["montant"] + $dataUnMagasin["carteCadeauRecu"]["montant"] + $dataUnMagasin["paidout"]["montant"] + $dataUnMagasin["animodollars"]["montant"] +
    																										($dataUnMagasin["general"]["remise"] - $dataUnMagasin["general"]["remiseBrute"]) );?>
																</td>
    														</tr>
    														<tr>
    															<td colspan="3"><h3 class="mt-3"><?= L("sommaire des paiements","o") ?></h3></td>
    														</tr>
    
    														<?php foreach ( $dataUnMagasin["paiement"]["lignes"] as $uneLignePaiement ){
    															?>
    															<tr>
    																<td><?= $uneLignePaiement["label"] ?></td>
    																<td style="text-align:center"><?= $uneLignePaiement["nb"]?></td>
    																<td style="text-align:right"><?= formatPrix($uneLignePaiement["montant"])?></td>
    															</tr>
    															<?php
    														} ?>
    														
    														<?php if( round($dataUnMagasin["paiement"]["tip"],2) != 0 ){ 
    															$dataUnMagasin["paiement"]["montant"] -= $dataUnMagasin["paiement"]["tip"];
	    														?>
	    														<tr>
	    															<td><?= L("Moins : Pourboires") ?></td>
	    															<td></td>
	    															<td style="text-align:right;"><?= formatPrix( $dataUnMagasin["paiement"]["tip"] * -1 );?></td>
	    														</tr>
	    														<?php 
	    													} ?>
    														
    														<tr style="font-weight:bold">
    															<td>Total</td>
    															<td style="text-align:center;"><?=  $dataUnMagasin["paiement"]["nb"] ?></td>
    															<td style="text-align:right;"><?= formatPrix( $dataUnMagasin["paiement"]["montant"]);?></td>
    														</tr>
    														
    														<tr>
    															<td colspan="3"><h3 class="mt-3"><?= L("ventes par départements","o") ?></h3></td>
    														</tr>
    														<?php
    														foreach ( $dataUnMagasin["departement"]["lignes"] as $uneLigneDep ){
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
    															<td style="text-align:center;"><?=  $dataUnMagasin["departement"]["nb"] ?></td>
    															<td style="text-align:right;"><?= formatPrix( $dataUnMagasin["departement"]["montant"]);?></td>
    														</tr>
    														<tr>
    															<td colspan="3"><h3 class="mt-3"><?= L("ventes par utilisateurs","o") ?></h3></td>
    														</tr>
    														<?php foreach ( $dataUnMagasin["user"]["lignes"] as $uneLigneUser ){
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
    															<td style="text-align:center;"><?=  $dataUnMagasin["user"]["nb"] ?></td>
    															<td style="text-align:right;"><?= formatPrix( $dataUnMagasin["user"]["montant"]);?></td>
    														</tr>
    													</tbody>
    												</table>
    												<?php if ( sizeof( $dataUnMagasin["depot"]["lignes"] ) > 0 ){ ?>
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
    														<?php foreach ( $dataUnMagasin["depot"]["lignes"] as $uneLigneDepot ){
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
    															<td style="text-align:center;"><?=  $dataUnMagasin["depot"]["nb"] ?></td>
    															<td style="text-align:right;"><?= formatPrix( $dataUnMagasin["depot"]["montant"]);?></td>
    														</tr>
    													</tfoot>
    												</table>
    												<?php } ?>
    											</div>
    										</div>
    										<?php
								           }//fin foreatc
								    	} // fin if
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
	<?php
} ?>
