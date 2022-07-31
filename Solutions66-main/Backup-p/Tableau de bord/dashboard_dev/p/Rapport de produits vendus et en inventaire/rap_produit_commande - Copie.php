<?php

// Get  all magasins 
$allMag = [];

$queryAllMag = query("SELECT * FROM MAGASIN
                        WHERE caisse_db IS NOT NULL 
                        ORDER BY M_NOM asc",[],$mysqli);
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}

$listID_MAGASINcanaccess = [];
// Limite le droit d'acces aux magasins selon niveau de security de l'utilisateur
if ( $_SESSION["utilisateur"]["security"] >= 2 ){
	$listID_MAGASINcanaccess = array_keys($_SESSION["magasins"]);
} else {
	$listID_MAGASINcanaccess = array_keys($allMag);
}
 
$listID_MAGASIN = [];
// Select : vérifier si l'utilisateur à le droit d'acces aux magasins choisies
if ( isset($_GET["ID_MAGASIN"]) ){
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) {
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanaccess) ){
			$listID_MAGASIN[] = $ID_MAGASIN;
		}
	}
}

// Si pas de choix de magasin de la part de l'utilisateur
if ( count($listID_MAGASIN) < 1 ){
    // Donne seulement celles qu'il a le droit d'accès
	$listID_MAGASIN = $listID_MAGASINcanaccess;
}
$listID_MAGASINstr = implode(",",$listID_MAGASIN);

//  ========================= Soumission du Formulaire ==============================
if ( $_GET["search"] == "1" ){  
    
	ini_set("memory_limit","256M");
	set_time_limit(300);
    
	$data = [];
	$listAND = [];
	$listPARAM = [];
	$listANDgroup = [];
	$listPARAMgroup = [];
    
    // =============================== RECOLTE DE DONNÉS =======================
    
    //Si date FROM choisi  
	if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) ){
		$listAND[] = " facture.date_insert >= '{$_GET['from']} 00:00:00' ";
		$listANDgroup[] = " facture.date_insert >= '{$_GET['from']} 00:00:00' ";
	}
	//Si date TO choisi  
	if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
		$listAND[] = " facture.date_insert <= '{$_GET['to']} 23:59:59' ";
		$listANDgroup[] = " facture.date_insert <= '{$_GET['to']} 23:59:59' ";
	}
	//Si fournisseur choisi
	if( $_GET["fournisseur"] != "" ){
		$listAND[] = "article.id_distributeur = ? ";
		$listPARAM[] = $_GET['fournisseur'];
    }
    //Si distributeur choisi 
	if( $_GET["distributeur"] != "" ){
		$listAND[] = "link_article_four.id_fournisseur = ? ";
		$listPARAM[] = $_GET['distributeur'];
    }
    //Si nom article choisi 
	if($_GET["keyWord"] != ""){
	    /* 
	    Puisqu'il peut y avoir plus qu'un mot dans la recherche, on retourne 
	    un tableau avec les chaînes de caractères envoyés en utilisant le séparateur.
	    */
		$arrayOfWords = explode(' ',$_GET["keyWord"]);
		//Pour chaque indice des chaînes
		foreach($arrayOfWords as $keyWord){
		    //Beetwen '%' : alows to finds any values that have "$keyWord" in any position
			$keyWord = '%'.$keyWord.'%';//'%Hero%'
			$listAND[] = "(article.desc_fr like ? or article.desc_en like ? ) ";
			$listPARAM[] = $keyWord;
			$listPARAM[] = $keyWord;
			$listANDgroup[] = "(article.desc_fr like ? or article.desc_en like ? ) ";
			$listPARAMgroup[] = $keyWord;
			$listPARAMgroup[] = $keyWord;
		}
	}
//vex($listPARAM); die();
	//Affiche que les produits en rupture de stock 
	if( $_GET["showOutOfStockOnly"] != "" ){
	    $listAND[] = "article.stock = 0 ";
	    $listANDgroup[] = "article.stock = 0 ";
	} else {
	    // Display stock max par produit selon choix
	    if( preg_match('#^[0-9]+$#',$_GET["maxItemsEnInventaire"]) ){
	        $listAND[] = "article.stock <= ? ";
	        $listPARAM[] = $_GET["maxItemsEnInventaire"];
	        $listANDgroup[] = "article.stock <= ? ";
	        $listPARAMgroup[] = $_GET["maxItemsEnInventaire"];
	    }
	}
    //VA cherche les articles qui sont encore reliés au fournisseur et les article qui ont été achetés et de type PLU
	$listAND[] = " link_article_four.discontinued IS NULL AND facture_item.id_article IS NOT NULL AND facture_item.type = 'PLU' ";  
    $listAndStr = implode(" AND ", $listAND);  

    $listANDgroup[] = " link_article_four.discontinued IS NULL AND facture_item.id_article IS NOT NULL AND facture_item.type = 'PLU' ";
    $listANDgroupStr = implode(" AND ", $listANDgroup);

	$resultMag = query("SELECT * FROM MAGASIN 
                        WHERE ID_MAGASIN IN ($listID_MAGASINstr) 
                        ORDER BY M_NOM",[],$mysqli);
	                        
	while( $uneLigneMag = $resultMag->fetch_assoc() ){
	    
		$ID_MAGASIN = $uneLigneMag["ID_MAGASIN"];
		$nomDB      = $uneLigneMag["caisse_db"];
		
		$data[$ID_MAGASIN] = [
		                      "mode"=>"all",
		                      "nb_vendu"=>0, 
		                      "montant_vendu"=>0, 
		                      "nb_stock"=>0, 
		                      "empty"=>true,
		                      "fournisseurs"=>[],
		                      "articles"=>[]
	                         ];
	    //Definition du filtre de recherche
		if($_GET["fournisseur"] != "" || $_GET["distributeur"] != "" || $_GET["groupByFD"] == "aucun"){
			$data[$ID_MAGASIN]["mode"] = "groupeBy";
		}
		
	    //Mode un fournisseur/distributeur
		if ( $data[$ID_MAGASIN]["mode"] == "groupeBy"){
        		    
            $enonce = "SELECT facture_item.montant `montant_vendu`, facture_item.nb `nb_vendu`, facture.id_facture,
                        facture.date_insert `date_facture`, article.stock `nb_stock`, article.desc_fr `nom_fr`, 
                        prix.prix `prix_vente`, article.PLU , article.PLU2 , article.PLU3  , article.id_article
                        FROM $nomDB.facture_item
                        JOIN $nomDB.facture using(id_facture)
                        LEFT JOIN $nomDB.article using(id_article)
                        LEFT JOIN $nomDB.prix using(id_article)
                        LEFT JOIN $nomDB.link_article_four using(id_article) 
                        WHERE $listAndStr
                        GROUP BY facture_item.id_facture_item";
            $resultUn = query($enonce,$listPARAM,$dbAnimoCaisse);  
			
			while ( $uneLigne = $resultUn->fetch_assoc() ){   
			    
				$data[$ID_MAGASIN]["articles"][$uneLigne["id_article"]]["nb_stock"]   = $uneLigne["nb_stock"];				
				$data[$ID_MAGASIN]["articles"][$uneLigne["id_article"]]["nom_fr"]     = $uneLigne["nom_fr"];
				$data[$ID_MAGASIN]["articles"][$uneLigne["id_article"]]["prix_vente"] = $uneLigne["prix_vente"];
				$data[$ID_MAGASIN]["articles"][$uneLigne["id_article"]]["PLU"]        = $uneLigne["PLU"];
				$data[$ID_MAGASIN]["articles"][$uneLigne["id_article"]]["PLU2"]       = $uneLigne["PLU2"];
				$data[$ID_MAGASIN]["articles"][$uneLigne["id_article"]]["PLU3"]       = $uneLigne["PLU3"];
   			    //TOTAUX articles
				$data[$ID_MAGASIN]["articles"][$uneLigne["id_article"]]["montant_vendu"] += $uneLigne["montant_vendu"];
				$data[$ID_MAGASIN]["articles"][$uneLigne["id_article"]]["nb_vendu"]      += $uneLigne["nb_vendu"];				
				$data[$ID_MAGASIN]["empty"] = false;
				//TOTAUX Magasin
				$data[$ID_MAGASIN]["nb_vendu"]         += $uneLigne["nb_vendu"];
   			    $data[$ID_MAGASIN]["montant_vendu"]    += $uneLigne["montant_vendu"];

				
			    if( empty($data[$ID_MAGASIN]["articles"][$uneLigne["id_article"]]["distributeurs"]) ){
			        
    				if (preg_match('#^[0-9]+$#',$_GET["distributeur"])){
    					$enonce = "SELECT link_article_four.prix_coutant `prix_four`, link_article_four.num_four
    	        		            FROM $nomDB.link_article_four
    	        		            WHERE link_article_four.id_article = {$uneLigne["id_article"]} AND link_article_four.id_fournisseur = ?
    	        		            GROUP BY link_article_four.id_article";
            	    	$resultLinkList = query($enonce,[$_GET["distributeur"]],$dbAnimoCaisse);
     				} else {
    					$enonce = "SELECT link_article_four.prix_coutant `prix_four`, link_article_four.num_four, fournisseur.nom  as nomFournisseur
    	        		            FROM $nomDB.link_article_four
    	        		            INNER JOIN fournisseur USING(id_fournisseur)
    	        		            WHERE link_article_four.id_article = {$uneLigne["id_article"]} AND link_article_four.discontinued IS NULL
    	        		            order by link_article_four.date_update desc";
            	    	$resultLinkList = query($enonce,[],$dbAnimoCaisse);
     				}
            		while( $uneLigneLink = $resultLinkList->fetch_assoc() ){
                        $data[$ID_MAGASIN]["articles"][$uneLigne["id_article"]]["distributeurs"][]  = $uneLigneLink;
            		}			        
			    }
			}
			//Calcul du total de nb produits en stock par magasin
			foreach($data[$ID_MAGASIN]["articles"] as $id_articleMag => $rowArticleMag){
			    $data[$ID_MAGASIN]["nb_stock"] += $rowArticleMag["nb_stock"];
			}

    	//Mode all fournisseurs/distributeurs
		} else {
			$resultFour= query("SELECT id_fournisseur, nom `label`
                                FROM fournisseur 
                                WHERE inactif IS NULL " .
                                ($_GET["groupByFD"] == "dist" ? " AND fournisseur.est_fournisseur IS NOT NULL " : "" ) ." ORDER BY id_fournisseur asc",[],$dbAnimoCaisse); 
                                    
			while ( $uneLigneFour = $resultFour->fetch_assoc() ){   
			    
			    //Pour chaque fournisseur un indice  est enregistré avec sa respective clé
			    $data[$ID_MAGASIN]["fournisseurs"][ $uneLigneFour["id_fournisseur"] ] = [
                        		                                                          "nb_vendu"=>0,
                        		                                                          "montant_vendu"=>0,
                        		                                                          "nb_stock"=>0, 
                        		                                                          "nom_fournisseur"=>$uneLigneFour["label"],
                        		                                                          "articles"=>[] 
                    		                                                             ];	
    		    $enonce = "SELECT facture_item.montant `montant_vendu`, facture_item.nb `nb_vendu`, facture.id_facture, article.stock `nb_stock`, article.id_article
                			FROM $nomDB.facture_item
                			JOIN $nomDB.facture using(id_facture)
                			LEFT JOIN $nomDB.article using(id_article)
                			LEFT JOIN $nomDB.prix using(id_article)
                			LEFT JOIN $nomDB.link_article_four using(id_article)
                			WHERE $listANDgroupStr " . 
                			( $_GET["groupByFD"] == "four" ? " AND article.id_distributeur =" . $uneLigneFour["id_fournisseur"] : "" ) .
                			( $_GET["groupByFD"] == "dist" ? " AND link_article_four.id_fournisseur =" . $uneLigneFour["id_fournisseur"] : "" ) . 
                			" GROUP BY facture_item.id_facture_item";

                $resultUn = query($enonce,$listPARAMgroup,$dbAnimoCaisse);
                
    			while ( $uneLigne = $resultUn->fetch_assoc() ){
    				$data[$ID_MAGASIN]["fournisseurs"][$uneLigneFour["id_fournisseur"]]["montant_vendu"] += $uneLigne["montant_vendu"];
    				$data[$ID_MAGASIN]["fournisseurs"][$uneLigneFour["id_fournisseur"]]["nb_vendu"] += $uneLigne["nb_vendu"];
    				$data[$ID_MAGASIN]["fournisseurs"][$uneLigneFour["id_fournisseur"]]["articles"][$uneLigne["id_article"]]["nb_stock"] = $uneLigne["nb_stock"];
    				$data[$ID_MAGASIN]["nb_vendu"] += $uneLigne["nb_vendu"];
   				    $data[$ID_MAGASIN]["montant_vendu"] += $uneLigne["montant_vendu"];
    				$data[$ID_MAGASIN]["empty"] = false;
    				
            	}
            	foreach($data[$ID_MAGASIN]["fournisseurs"][$uneLigneFour["id_fournisseur"]]["articles"] as $id_Artfour => $rowArtFour){
            	        $data[$ID_MAGASIN]["fournisseurs"][$uneLigneFour["id_fournisseur"]]["nb_stock"] += $rowArtFour["nb_stock"];
			    }
			}
			//Calcul du total de nb produits en stock par magasin
			foreach($data[$ID_MAGASIN]["fournisseurs"] as $id_four => $rowFourMag){
			    $data[$ID_MAGASIN]["nb_stock"] += $rowFourMag["nb_stock"];
			}
		}
			
        // ======================   GESTION TRI  =================================
		$listTriPosible = ['nb_vendu','nb_stock','montant_vendu','nom_fr','nom_fournisseur'];
		
        if(in_array($_GET['orderby'],$listTriPosible)){
            $orderby = $_GET['orderby'];
        }else{ 
            $orderby = ($data[$ID_MAGASIN]["mode"] == "groupeBy") ? 'nom_fr' : 'nom_fournisseur';
        }
        if($_GET['sens']==''){
            $sens = 'desc';
        }else{ 
            $sens = $_GET['sens'];
        }
        //Si mode groupé
		if ( $data[$ID_MAGASIN]["mode"] == "groupeBy" ){
		    //Trie le tableau en conservant la correspondance entre les index et leurs valeurs.
    		uasort( $data[$ID_MAGASIN]["articles"], function($a,$b){
    			if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
    				return ($_GET["sens"]=="desc")?1:-1;
    			} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
    				return ($_GET["sens"]=="desc")?-1:1;
    			}
    			return 0;
    		});
    	} else{
    	    //Si  PAS groupé
    	    uasort( $data[$ID_MAGASIN]["fournisseurs"], function($a,$b){
    			if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
    				return ($_GET["sens"]=="desc")?1:-1;
    			} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
    				return ($_GET["sens"]=="desc")?-1:1;
    			}
    			return 0;
    	    });
    	}
	}
}
/*
echo '<pre>' , print_r($_REQUEST) , '</pre>';
echo '<pre>' , print_r($data) , '</pre>';
*/


//  ======================================= PDF & EXCEL ==========================================

if ( $_GET["getFile"] == "1" and $data ){
    
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
	    if( $data[$ID_MAGASIN]["mode"] == "groupeBy" ){
	        $rapport = new RapportXLS( RPDF_PAGE_LANDSCAPE, RPDF_SHOWCOL_ALLPAGE );
	    } else{
	        $rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	    }
	} else {
	    if( $data[$ID_MAGASIN]["mode"] == "groupeBy" ){
	        $rapport = new RapportPDF( RPDF_PAGE_LANDSCAPE, RPDF_SHOWCOL_ALLPAGE );
	    } else{
	        $rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	    }
	}

	$titre = L("Rapport de produits vendus et en inventaire","o");

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

	if( $data[$ID_MAGASIN]["mode"] == "groupeBy" ){
	    if( $_GET["fournisseur"] != "" ){
		    $uneLigneFournisseur = query("select * from fournisseur where id_fournisseur = ?",[$_GET["fournisseur"],],$dbAnimoCaisse)->fetch_assoc();
		    $listSoustitre[] = ["fournisseur", $uneLigneFournisseur["nom"] ];
	    }
	    if( $_GET["distributeur"] != "" ){
	        $uneLigneDistro = query("select * from fournisseur where id_fournisseur = ?",[$_GET["distributeur"],],$dbAnimoCaisse)->fetch_assoc();
		    $listSoustitre[] = ["distributeur", $uneLigneDistro["nom"] ];
	    }
	} else{
	    // Mode groupé par fournisseur
	    if( $_GET["groupByFD"] == "four"){
	        $listSoustitre[] = ["fournisseur", "tous (sauf fournisseurs à zero)" ];
	        $listSoustitre[] = ["", "groupés par fournisseur"];
	    } else{
	        // Mode groupé par distributeur
	        $listSoustitre[] = ["distributeur", "tous (sauf distributeurs à zero)" ];
	        $listSoustitre[] = ["", "groupés par distributeur"];
	    }
	}
	
	if( $_GET["showOutOfStockOnly"] != "" ){
	    $listSoustitre[] = ["paramètre", "En rupture de stock seulement" ];
	} elseif( $_GET["maxItemsEnInventaire"] != "" ){
	    $listSoustitre[] = ["paramètre", "Produits ayant ".$_GET["maxItemsEnInventaire"]." articles en stock" ];
	}

	foreach( $listID_MAGASIN as $ID_MAGASIN ){
		$listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
	}
	$listSoustitre[] = ["magasin(s)", implode(", ",$listNomMag) ];
	
	if( $data[$ID_MAGASIN]["mode"] == "groupeBy" ){
		$listEnteteColonne = [
			[ 
			    ["text"=>L("nom_produit","o"),"width"=>80,"align"=>"L"], 
			    ["text"=>L("total ventes","o"),"width"=>15,"align"=>"R"],
			    ["text"=>L("Nb Vendu","o"),"width"=>20,"align"=>"C"],
			    ["text"=>L("Nb Stock","o"),"width"=>15,"align"=>"C"], 
			    ["text"=>L("prix distributeur","o") ,"width"=>35,"align"=>"R"], 
			    ["text"=>L("prix vente","o"),"width"=>20,"align"=>"R"],
			    ["text"=>L("plu","o"),"width"=>25,"align"=>"C"], 
			    ["text"=>L("Code distributeur","o"),"width"=>40,"align"=>"L"],
			],
		];
		$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
		$rapport->setInfoCols(-1);
	} else {
		$listEnteteColonne = [
			[
				["text"=>L("nom_fournisseur",'o'),"width"=>60,"align"=>"L"],
				["text"=>L("total_ventes",'o'),"width"=>40,"align"=>"R"],
				["text"=>L("articles_vendus","o"),"width"=>40,"align"=>"C"],
				["text"=>L("articles_inventaire","o"),"width"=>40,"align"=>"C"],
			],
		];
		
		$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
		$rapport->setInfoCols(-1);
	}
	
	$isfirst = true;
	foreach($data as $ID_MAGASIN => $dataMag){
	    if( !$dataMag["empty"] ){
	        
	        if (!$isfirst){
        		$rapport->Ln(6);
        	}
        	$isfirst = false;
        	
    		$infomag= query("select * from MAGASIN where ID_MAGASIN = ?",[$ID_MAGASIN,],$mysqli)->fetch_assoc();
    		if( count($listID_MAGASINcanaccess) > 1 ){
    			$rapport->SetFont('helvetica', 'B', 12);
    			$rapport->Cell(0, 0, $infomag["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
    		}
    	
    		if ( $dataMag["mode"] == "groupeBy" ){
    		    
    			foreach ( $dataMag["articles"] as $id_article => $rowArticle ) {
    				$listChamps = [];
    				$listChamps[] = $rowArticle["nom_fr"];
    				$listChamps[] = nfs($rowArticle["montant_vendu"]);
    				$listChamps[] = $rowArticle["nb_vendu"];
    				$listChamps[] = $rowArticle["nb_stock"];
				    $arrayStrDist = [];
					if(count($rowArticle['distributeurs']) > 0 ){ 
					    foreach($rowArticle['distributeurs'] as $rowDist){
			               empty($rowDist['nomFournisseur']) ? $arrayStrDist[] = nfs($rowDist['prix_four'])  : $arrayStrDist[] =  $rowDist['nomFournisseur'] . ': ' . nfs($rowDist['prix_four']);
					    }
					}
					$listChamps[] = implode("\n",$arrayStrDist);
    				$listChamps[] = nfs($rowArticle["prix_vente"]);
    				$listChamps[] = $rowArticle["PLU"] . ($rowArticle["PLU2"] != "" ? ", ".$rowArticle["PLU2"] :"") . ($rowArticle["PLU3"] != "" ? ", ".$rowArticle["PLU3"] :"");
				    $arrayStrDist = [];
					if(count($rowArticle['distributeurs']) > 0 ){ 
					    foreach($rowArticle['distributeurs'] as $rowDist){
			               empty($rowDist['nomFournisseur']) ? $arrayStrDist[] = $rowDist['num_four']  : $arrayStrDist[] =  $rowDist['nomFournisseur'] . ': ' . $rowDist['num_four'];
					    }
					}
					$listChamps[] = implode("\n",$arrayStrDist);
                    //$rapport->Ln(); // Saut de ligne
    				$rapport->writeLigneRapport3wrap( $listChamps );
    			}
    			$listTotalChamps = [];
    		    $listTotalChamps  [] = count($dataMag["articles"])." produits";
    		    $listTotalChamps  [] = nfs($dataMag["montant_vendu"]);
    		    $listTotalChamps  [] = $dataMag["nb_vendu"];
    		    $listTotalChamps  [] = $dataMag["nb_stock"];
    		    $listTotalChamps  [] = null;
    		    $listTotalChamps  [] = null;
    		    $listTotalChamps  [] = null;
    		    $listTotalChamps  [] = null;
    		   
    			$rapport->writeLigneGrandTotal( $listTotalChamps, [false,true,true,true,false,false,false,false] );

    		} else {
    		    foreach ($dataMag["fournisseurs"] as $id_four => $rowFournisseur) {
    			    if($rowFournisseur["montant_vendu"] == 0 && $rowFournisseur["nb_vendu"] == 0 && $rowFournisseur["nb_stock"] == 0){
    			        continue;
    			    }
    				$listChamps = [];
    				$listChamps[] = $rowFournisseur["nom_fournisseur"];
    				$listChamps[] = nfs($rowFournisseur["montant_vendu"]);
    				$listChamps[] = $rowFournisseur["nb_vendu"];
    				$listChamps[] = $rowFournisseur["nb_stock"];
    	
    				$rapport->writeLigneRapport3wrap( $listChamps );
    			}
    			$rapport->writeLigneGrandTotal( [ null,nfs($dataMag["montant_vendu"]),$dataMag["nb_vendu"],$dataMag["nb_stock"]], [false,true,true,true] );
    		}
	    }
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
				<span class="hidden-xs-down"><?= L("Rapport de produits vendus et en inventaire","o");?></span>
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
												<input type="hidden" name="search" value="1">
												<input type="hidden" name="sens" value="<?= $_GET["sens"] ?>">
												<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">
												<div class="row" style="margin-bottom:15px;">
												    <!--CHOIX DE DATES -->
													<div class="col-md-6">
														<div class="input-group bs-datepicker input-daterange picker-range">
															<input type="text" class="form-control" name="from" id="from" value="<?= empty(htmlentities($_GET["from"])) ? date("Y-m-d", strtotime(date("Y-m-d"). ' - 30 days')) : htmlentities($_GET["from"]) ?>">
															<span class="input-group-addon px-3"><?= L("to"); ?></span>
															<input type="text" class="form-control" name="to" id="to" value="<?= empty(htmlentities($_GET["to"])) ? date("Y-m-d") : htmlentities($_GET["to"])?>">
														</div>
													</div>
													<!--SELECT FOURNISSEUR -->
													<div class="col-md-3">
														<div class="form-group input-group">
															<select name="fournisseur" class="form-control">
																<option value="">Par fournisseur</option>
																<?php
																//Need to add onClick refresh for product selection
																$enonce = "SELECT * FROM fournisseur 
            																WHERE est_distributeur IS NOT NULL AND inactif IS NULL
            																ORDER BY nom";
																$resultFour = $dbAnimoCaisse->query($enonce);
																while($rowFour = $resultFour->fetch_assoc()){
																	printf("<option value='%s'%s>%s</option>", $rowFour["id_fournisseur"], ($rowFour["id_fournisseur"] == $_GET["fournisseur"] ? " selected" : ""), $rowFour["nom"]);
																}
																?>
															</select>
														</div>
													</div>
													<!--SELECT DISTRIBUTEUR -->
													<div class="col-md-3">
														<div class="form-group input-group">
															<select name="distributeur" class="form-control">
																<option value="">Par distributeur</option>
																<?php
																$enonce = "SELECT * FROM fournisseur 
															                WHERE est_fournisseur IS NOT NULL 
															                ORDER BY nom";
																$resultDistr = $dbAnimoCaisse->query($enonce);
																while($rowDistr = $resultDistr->fetch_assoc()){
																	printf("<option value='%s'%s>%s</option>", $rowDistr["id_fournisseur"], ($rowDistr["id_fournisseur"] == $_GET["distributeur"] ? " selected" : ""), $rowDistr["nom"]);
																}
																?>
															</select>
														</div>
													</div>
													<!--SELECT MAGASINS -->
													<?php if( count($listID_MAGASINcanaccess) > 1 ){?>
													<div class="col-md-6">
														<div>
															<select class="ui fluid normal multi-selection select-dropdown form-control" name="ID_MAGASIN[]" multiple>
																<?php
																foreach( $listID_MAGASINcanaccess as $ID_MAGASIN){
																    if(!INDEV and $allMag[$ID_MAGASIN]["RESERVED_DEV"] == 1){
    																        continue;
    																}
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
													<!--INPUT # STOCK -->
    												<div class="col-md-3">
												        <div class="form-group input-group">
															<input type="number" min="0" class="form-control" name="maxItemsEnInventaire" placeholder="Stock maximum par produit" value="<?php echo $_GET['maxItemsEnInventaire']?>">
														</div>
												    </div>
												    <!--Par nom d'article -->
       												<div class="col-md-3">
												        <div class="form-group input-group">
            											    <input value="<?= htmlspecialchars($_GET["keyWord"]) ?>" type="text" class="form-control mb-1 mr-sm-2" name="keyWord" placeholder="Par nom d'article">
														</div>
												    </div>
												    <!--checkbox -->
													<div class="col-md-3">
														<div class="ui dynamic checkbox checked pt-1">
															<input type="checkbox" name="showOutOfStockOnly" value="1" class="form-control" <?= ($_GET["showOutOfStockOnly"]=="1")?"checked":"" ?> />
															<label>Afficher produits en rupture de stock seulement</label>
														</div>
    												</div>
    												<!--checkbox/radio -->
    											    <div class="input-group">
														<div class="ui dynamic checkbox pt-1 col-md-3 ">
															<input type="radio" name="groupByFD" value="four" class="form-control" <?= ( $_GET["groupByFD"] == "four" ) ? "checked":"" ?> />
															<label>Groupé par fournisseur</label>
														</div>
														<div class="ui dynamic checkbox pt-1 col-md-3">
															<input type="radio" name="groupByFD" value="dist" class="form-control" <?= ( $_GET["groupByFD"] == "dist" ) ? "checked":"" ?> />
															<label>Groupé par distributeur</label>
														</div>
														<div class="ui dynamic checkbox pt-1 col-md-3">
															<input type="radio" name="groupByFD" value="aucun" class="form-control" <?= ( $_GET["groupByFD"] == "aucun" || $_GET["groupByFD"] == "" ) ? "checked":"" ?> />
															<label>Aucun groupe</label>
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
    										foreach($data as $ID_MAGASIN => $dataMag){
                                                if( !$dataMag["empty"] ){
    												$infomag = query("SELECT * FROM MAGASIN WHERE ID_MAGASIN = ?",[$ID_MAGASIN,],$mysqli)->fetch_assoc();
    												if( count($listID_MAGASINcanaccess) > 1 ){ 
    												    ?><h2><?= $infomag["M_NOM"] ?></h2><?php
												    }
    												//Mode groupé :fournnisseur/Distributeur/aucun groupe
    												if ($dataMag["mode"] == "groupeBy"){
													    ?>
													    <div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
    														<div class="fixed-table-body" style="min-height: 200px;">
    															<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-striped table-condensed">
    																<thead>
    																	<tr>
    																		<th>
    																		    <a href="index.php?<?= rebuildQueryString(['orderby'=>'nom_fr','sens'=>($orderby == 'nom_fr' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>">
    																		         <?= L("nom_produit","o") ?> <?= '<i class="fa fa-sort'.(($orderby == 'nom_fr' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																	            </a>
    																		</th>
                															<th style="text-align:center">
                															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'montant_vendu', 'sens'=>($_GET["orderby"] == 'montant_vendu' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															         <?= L("total_ventes","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'montant_vendu' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
                															    </a>
                														    </th>
    																		<th style="text-align:right">
    																		    <a href="index.php?<?= rebuildQueryString(['orderby'=>'nb_vendu','sens'=>($orderby == 'nb_vendu' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>">
    																		         <?= L("articles_vendus","o") ?> <?= '<i class="fa fa-sort'.(($orderby == 'nb_vendu' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>
    																		</th>
    																		<th style="text-align:right">
    																		    <a href="index.php?<?= rebuildQueryString(['orderby'=>'nb_stock','sens'=>($orderby == 'nb_stock' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> 
    																		    <?= L("articles_inventaire","o") ?> <?= '<i class="fa fa-sort'.(($orderby == 'nb_stock' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																		    </a>
    																		</th>
    																		<th style="text-align:right">
    																		    <?= L("prix_distributeur","o") ?>
																		    </th>
                 															<th style="text-align:right">
                															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'prix_vente', 'sens'=>($_GET["orderby"] == 'prix_vente' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															         <?= L("prix_vente","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'prix_vente' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
                															    </a>
                														    </th> 
    																		<th style="text-align:center">
    																		    <?= L("plu","o") ?>
																		    </th>
    																		<th style="text-align:center">
    																		    <?= L("Code") ?>
																		    </th>
    																	</tr>
    																</thead>
    																<tbody>
    																	<?php
    																	foreach ( $dataMag["articles"] as $id_article => $rowArticle ) {   
    																	    $link = "?p=produits&id=".urlencode($id_article);
    																		?>
    																		<tr>
    																			<td>
    																			    <a href="<?= $link ?>"><b><?= $rowArticle["nom_fr"] ?></b>
																			    </td>
    																			<td style="text-align:right">
    																			    <?= formatPrix($rowArticle["montant_vendu"]); ?>
																			    </td>
    																			<td style="text-align:right">
    																			    <?= $rowArticle["nb_vendu"] ?>
																			    </td>
    																			<td style="text-align:right">
    																			    <?= $rowArticle["nb_stock"] ?>
																			    </td>
    																			<td style="text-align:right">
																				<?php 	if(count($rowArticle['distributeurs']) > 0 ){ 
                        															    foreach($rowArticle['distributeurs'] as $rowDist){
                    															        ?>
                    															            <div>
                    															               <div><?= empty($rowDist['nomFournisseur']) ? formatPrix($rowDist['prix_four'])  :  $rowDist['nomFournisseur']." : ".formatPrix($rowDist['prix_four'])  ?></div>
                															                </div>
                    															        <?php
                        															    }
                        															}	?>	 
																			    </td>
    																			<td style="text-align:right">
    																			    <?= formatPrix($rowArticle["prix_vente"]); ?>
																			    </td>
    																			<td style="text-align:center; max-width:130px">
                                                                                    <?=  implode(', ',array_filter([$rowArticle["PLU"],$rowArticle["PLU2"],$rowArticle["PLU3"]])) ?>
    																			</td> 
    																			<td style="text-align:center">
																				<?php 
                        															if(count($rowArticle['distributeurs']) > 0 ){
                        															    foreach($rowArticle['distributeurs'] as $rowDist){
                        															        ?>
                        															            <div><?= $rowDist['nomFournisseur']?> <?= $rowDist['num_four']?></div>
                        															        <?php
                        															    }
                        															}
                    															?>
																			    </td>
    																		</tr>
    																		<?php
    																	} ?>
        															</tbody>
        															<tfoot>
        																<tr style="font-weight:bold">
        																	<td></td>
        																	<td style="text-align:right"><?= formatPrix($dataMag["montant_vendu"]);?></td>
        																	<td style="text-align:right"><?= $dataMag["nb_vendu"] ?></td>
        																	<td style="text-align:right"><?= $dataMag["nb_stock"] ?></td>
        																	<td></td>
        																	<td></td>
        																	<td></td>
        																	<td></td>
        																</tr>
        															</tfoot>
    															</table>
    														</div>
    													</div>
    												    <?php
    											    //Mode ALL
    												} else { 
												    
    													?>
    													<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
    														<div class="fixed-table-body" style="min-height: 200px;">
    															<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
    																<thead>
    																	<tr>
    																	    <!-- =========================================== ROWS =============================================================== -->
    																		<th>
    																		    <a href="index.php?<?= rebuildQueryString(['orderby'=>'nom_fournisseur','sens'=>($orderby == 'nom_fournisseur' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> 
        																		    <?= ($_GET["groupByFD"] == "four") ? L("nom_fournisseur","o") : L("nom_distributeur","o") ?>
        																		    <?= '<i class="fa fa-sort'.(($orderby == 'nom_fournisseur' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    																		    </a>
																		    </th>
                															<th style="text-align:center">
                															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'montant_vendu', 'sens'=>($_GET["orderby"] == 'montant_vendu' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															         <?= L("total_ventes","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'montant_vendu' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
                															    </a>
                														    </th>
        																
    																		<th style="text-align:center">
    																		    <a href="index.php?<?= rebuildQueryString(['orderby'=>'nb_vendu','sens'=>($orderby == 'nb_vendu' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>">
    																		         <?= L("articles_vendus","o") ?> <?= '<i class="fa fa-sort'.(($orderby == 'nb_vendu' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																		         </a>
																	         </th>
    																		</th>
    																		<th style="text-align:center">
                                                                                <a href="index.php?<?= rebuildQueryString(['orderby'=>'nb_stock','sens'=>($orderby == 'nb_stock' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>">
                                                                                     <?= L("articles_inventaire","o") ?> <?= '<i class="fa fa-sort'.(($orderby == 'nb_stock' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
                                                                                 </a>
                                                                             </th>
     																	    </th>
     																	    <!-- =========================================== fin ROWS =============================================================== -->
    																	</tr>
    																</thead>
    																<tbody>
    																	<?php
    																	foreach ($dataMag["fournisseurs"] as $id_four => $rowFournisseur) {
        																    if($rowFournisseur["montant_vendu"] == 0 && $rowFournisseur["nb_vendu"] == 0 && $rowFournisseur["nb_stock"] == 0){
        																        continue;
        																    }
    																	    $link = "?p=rap_produit_commande&search=1&orderby=" . $_GET["orderby"] . "&sens=" . $_GET["sens"] ."&from=" . $_GET["from"] . "&to=" . $_GET["to"] . "&ID_MAGASIN[]=" . implode("&ID_MAGASIN[]=",$listID_MAGASIN) .  
    																	            "&fournisseur=" . ( $_GET["groupByFD"] == "four" ? $id_four : "" ) . "&distributeur=" . ( $_GET["groupByFD"] == "dist" ? $id_four : "" ) . 
    																	            "&showOutOfStockOnly=" . $_GET["showOutOfStockOnly"] . "&maxItemsEnInventaire=" . $_GET['maxItemsEnInventaire'];
    																		?>
    																		<tr>
    																			<td style="text-align:left">
    																			    <a href="<?= $link ?>">
																			        <b><?= $rowFournisseur["nom_fournisseur"] ?></b>
																			    </td>
    																			<td style="text-align:center">
    																			    <?= formatPrix($rowFournisseur["montant_vendu"]); ?>
																			    </td>
    																			<td style="text-align:center">
    																			    <?= $rowFournisseur["nb_vendu"] ?></td>
    																			<td style="text-align:center"><?= $rowFournisseur["nb_stock"] ?></td>
    																		</tr>
    																		<?php
    																    } ?>
        															</tbody>
        															<tfoot>
        																<tr style="font-weight:bold">
        																	<td>
        																	    
        																	</td>
        																	<td style="text-align:center;">
        																	    <?= formatPrix($dataMag["montant_vendu"]);?>
    																	    </td>
        																	<td style="text-align:center;">
        																	    <?= $dataMag["nb_vendu"] ?>
    																	    </td>
        																	<td style="text-align:center;">
        																	    <?= $dataMag["nb_stock"] ?>
        																	</td>
        																</tr>
        															</tfoot>
    															</table>
    														</div>
    													</div>
    													<?php
    												}
                                                }
    										}
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
