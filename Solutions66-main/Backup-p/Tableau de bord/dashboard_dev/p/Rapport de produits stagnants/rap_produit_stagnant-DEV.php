<?php
$allMag = [];
$queryAllMag = query("SELECT * FROM MAGASIN 
                        WHERE caisse_db IS NOT NULL 
                        ORDER BY M_NOM asc",[],$mysqli);
//Get all magasins
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
    $allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}

$listID_MAGASINcanaccess = [];
if ( $_SESSION["utilisateur"]["security"] >= 2 ){
    $listID_MAGASINcanaccess = array_keys($_SESSION["magasins"]);
} else {
    $listID_MAGASINcanaccess = array_keys($allMag);
}

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

// ______________________________________________________________________
//                   ENVOIE DU FORM 
//_______________________________________________________________________

if ( $_GET["search"] == "1" ){
    
    ini_set("memory_limit","256M");
	set_time_limit(300);
	
	//  GESTION TRI COLONNES 
	$listTriPosible = ['date_vente','nom','nb_stock'];
    if(in_array($_GET['orderby'],$listTriPosible)){ 
        $orderby = $_GET['orderby']; 
    }else{ 
        $orderby = "date_vente";
        $_GET['orderby'] = "date_vente";
    }
    if($_GET['sens']==''){ 
        $sens = 'desc';
        $_GET['sens'] = 'desc';
    }else{ 
        $sens = $_GET['sens'];
    }
	
	$data = [];
	$dataGroup = [];
	$dataSalesToExclude = [];
	$listAND = [];
	$listFD = [];
	$listPARAM_Keywrd = [];

	// ======================== FILTRAGE DES PARAMETRES ===========================
	
	if( preg_match('#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$#',$_GET['from']) ){
    	$listAND[] = " facture.date_insert >= '{$_GET['from']} 00:00:00' ";
    } 
    if( preg_match('#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$#',$_GET['to']) ){
        $listAND[] = " facture.date_insert <= '{$_GET['to']} 23:59:59' ";
    }
    // Par fournisseur
	if( $_GET["fournisseur"] != "" ){
		$listFD[] = "article.id_distributeur = {$_GET['fournisseur']} ";
    }
    // Par distributeur
	if( $_GET["distributeur"] != "" ){
		$listFD[] = "laf.id_fournisseur = {$_GET['distributeur']} ";
    }
    //Par mot clé
	if($_GET["keywrd"] != "" ){
		$listKeyword = explode(' ',$_GET["keywrd"]);
		foreach($listKeyword as $keyword){
			$keyword = escapeForSqlLike(trim($keyword));
			if ( $keyword ){
				$keyword = '%'.$keyword.'%';
				$listFD[] = "(article.desc_fr like ? or article.desc_en like ?) ";
				$listPARAM_Keywrd[] = $keyword;
				$listPARAM_Keywrd[] = $keyword;
			}
		}
	}
	//Par departement
	if( preg_match('#^[0-9]+$#',$_GET['id_departement']) ){
    	$listFD[] = " article.id_departement = {$_GET['id_departement']} ";
    } 
	
	$listANDStr = implode(" AND ", $listAND);
	
	$resultMag = query("SELECT * 
                        FROM MAGASIN 
                        WHERE ID_MAGASIN IN ($listID_MAGASINstr) 
                        ORDER BY M_NOM",[],$mysqli);
	while( $uneLigneAgence = $resultMag->fetch_assoc() ){
	    
	    $ID_MAGASIN = $uneLigneAgence["ID_MAGASIN"];
		$nomDB = $uneLigneAgence["caisse_db"];
		$data[$ID_MAGASIN] = [];
		$dataSalesToExclude = [];
		$joinSoldOnce = "";

        // Filtrer les items sans aucune vente
		if( $_GET["showItemNeverSold"] != "1" ){
		    $joinSoldOnce = " JOIN ( SELECT fac.id_article, fac.type FROM $nomDB.facture_item `fac` ) soldOnceItem ON article.id_article = soldOnceItem.id_article AND soldOnceItem.type = 'PLU' ";
		}
		
		// Articles à exclure
		$enonce = "SELECT facture_item.id_article 
		            FROM $nomDB.facture_item
		            JOIN $nomDB.facture using (id_facture)
		            WHERE $listANDStr AND facture_item.id_article IS NOT NULL AND facture_item.type = 'PLU'
		            GROUP BY facture_item.id_article";
		$resultVentes = query($enonce,[],$dbAnimoCaisse);
		while( $uneLigneVente = $resultVentes->fetch_assoc() ){
		    $dataSalesToExclude[$uneLigneVente["id_article"]] = true;
		}
		
		// Articles stagnants
		$enonce = "SELECT article.id_article, article.desc_fr, article.PLU, article.stock, laf.num_four  
		           FROM $nomDB.article
                   JOIN $nomDB.link_article_four `laf` using (id_article) 
            	   $joinSoldOnce 
                   WHERE  laf.discontinued IS NULL AND article.stock > 0 ". (($listFD)? "AND ".implode(" AND ", $listFD) :"") . " GROUP BY article.id_article";
    	$resultArticle = query($enonce,$listPARAM_Keywrd,$dbAnimoCaisse);
    	while( $uneLigneArticle = $resultArticle->fetch_assoc()){
    	    
    	    if( isset($dataSalesToExclude[$uneLigneArticle["id_article"]]) ){
    	        continue;
    	    }
            $enonce = "SELECT facture.date_insert 
                        FROM $nomDB.facture_item `fi`
                            JOIN $nomDB.facture using (id_facture) 
                        WHERE fi.id_article = {$uneLigneArticle["id_article"]} AND fi.type = 'PLU'
                        ORDER BY facture.date_insert DESC 
                        LIMIT 1";
            $resultDate = query($enonce,[],$dbAnimoCaisse);
            if( $resultDate->num_rows === 1){
                $getDate = $resultDate->fetch_assoc();
                $data[$ID_MAGASIN][$uneLigneArticle["id_article"]]["date_vente"] = $getDate["date_insert"];
                
                $data[$ID_MAGASIN][$uneLigneArticle["id_article"]]["nom"] = $uneLigneArticle["desc_fr"];
    	        $data[$ID_MAGASIN][$uneLigneArticle["id_article"]]["PLU"] = $uneLigneArticle["PLU"];
    	        $data[$ID_MAGASIN][$uneLigneArticle["id_article"]]["nb_stock"] = $uneLigneArticle["stock"];
    	        $data[$ID_MAGASIN][$uneLigneArticle["id_article"]]["num_four"] = $uneLigneArticle["num_four"];
                
            } else{
                $data[$ID_MAGASIN][$uneLigneArticle["id_article"]]["date_vente"] = "0000"; //For uasort
                
                $data[$ID_MAGASIN][$uneLigneArticle["id_article"]]["nom"] = $uneLigneArticle["desc_fr"];
    	        $data[$ID_MAGASIN][$uneLigneArticle["id_article"]]["PLU"] = $uneLigneArticle["PLU"];
    	        $data[$ID_MAGASIN][$uneLigneArticle["id_article"]]["nb_stock"] = $uneLigneArticle["stock"];
    	        $data[$ID_MAGASIN][$uneLigneArticle["id_article"]]["num_four"] = $uneLigneArticle["num_four"];
            }
                
    	}
    	
    	uasort( $data[$ID_MAGASIN], function($a,$b){
    	    if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
    			return ($_GET["sens"]=="desc")?1:-1;
    		} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
    			return ($_GET["sens"]=="desc")?-1:1;
    		}
    		return 0;
    	});
	}
	
	// Magasins groupés
	if( $_GET["magasin_groupe"] != "" ){
	    foreach($data as $ID_MAGASIN => $dataMag){
            foreach ($dataMag as $id_article => $rowArticle){
                $dataGroup[$id_article]["nom"] = $rowArticle["nom"];
        	    $dataGroup[$id_article]["PLU"] = $rowArticle["PLU"];
        	    $dataGroup[$id_article]["num_four"] = $rowArticle["num_four"];
        	    $dataGroup[$id_article]["nb_stock"] += $rowArticle["nb_stock"];
        	    $dataGroup[$id_article]["matched"] += 1; // matched sur 
        	    if( !isset($dataGroup[$id_article]["date_vente"]) || $dataGroup[$id_article]["date_vente"] > $rowArticle["date_vente"] ){
        	        $dataGroup[$id_article]["date_vente"] = $rowArticle["date_vente"];    
        	    }
            }
            uasort( $dataGroup, function($a,$b){
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

// ______________________________________________________________________
//                   GESTION PDF & EXCEL
//_______________________________________________________________________

if ( $_GET["getFile"] == "1" and $data ){
    require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}

	$titre = L("rap_produit_stagnant","o");
    
    $listSoustitre = [];
	if ( $_GET['from'] and $_GET['to'] ){
		$listSoustitre[] = ["date d'exclusion", L("du") . " " . formatDateUTF8nonHTML( $_GET['from'] ) . " " . L("au") . " " . formatDateUTF8nonHTML( $_GET['to'] ) ];
	} elseif( $_GET['from'] ) {
		$listSoustitre[] = ["date d'exclusion", L("du") . " " . formatDateUTF8nonHTML( $_GET['from'] ) ];
	} elseif( $_GET['to'] ) {
		$listSoustitre[] = ["date d'exclusion", L("jusqu'au") . " " . formatDateUTF8nonHTML( $_GET['to'] ) ];
	} else {
		$listSoustitre[] = ["date d'exclusion", "tous" ];
	}
	
	if( $_GET["magasin_groupe"] != "" && $_GET["showItemNeverSold"] != "" ){
	    $listSoustitre[] = [L("parametres"),  L("magasin_groupe").", ".L("articles sans ventes précédentes")];
	} elseif( $_GET["magasin_groupe"] != "" ){
	    $listSoustitre[] = [L("parametres"), L("magasin_groupe")];
	} elseif( $_GET["showItemNeverSold"] != "" ){
	    $listSoustitre[] =  [L("parametres"), L("articles sans ventes précédentes")];
	}
    
    if( $_GET["fournisseur"] != "" ){
	    $uneLigneFournisseur = query("select * from fournisseur where id_fournisseur = ?",[$_GET["fournisseur"],],$dbAnimoCaisse)->fetch_assoc();
	    $listSoustitre[] = ["fournisseur", $uneLigneFournisseur["nom"] ];
	}
	if( $_GET["distributeur"] != "" ){
        $uneLigneDistro = query("select * from fournisseur where id_fournisseur = ?",[$_GET["distributeur"],],$dbAnimoCaisse)->fetch_assoc();
	    $listSoustitre[] = ["distributeur", $uneLigneDistro["nom"] ];
    }
    
    foreach( $listID_MAGASIN as $ID_MAGASIN ){
		$listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
	}
	$listSoustitre[] = ["magasin(s)", implode(", ",$listNomMag) ];
	
	if( $_GET["magasin_groupe"] != "" ){
	    $listSoustitre[] = ["",L("info_champ_m")];

	    $listEnteteColonne = [
			[ 
			    ["text"=>L("nom_produit","o"),"width"=>105,"align"=>"L"], ["text"=>L("#M","o"),"width"=>6,"align"=>"L"],
			    ["text"=>L("derniere_vente","o"),"width"=>28,"align"=>"L"], ["text"=>L("nb stock","o") ,"width"=>10,"align"=>"C"], 
			    ["text"=>L("code","o"),"width"=>18,"align"=>"L"], ["text"=>L("plu","o"),"width"=>24,"align"=>"C"]
			],
		];
	} else{
	     $listEnteteColonne = [
			[ 
			    ["text"=>L("nom_produit","o"),"width"=>115,"align"=>"L"],
			    ["text"=>L("derniere_vente","o"),"width"=>28,"align"=>"L"], ["text"=>L("nb stock","o") ,"width"=>10,"align"=>"C"], 
			    ["text"=>L("code","o"),"width"=>18,"align"=>"L"], ["text"=>L("plu","o"),"width"=>24,"align"=>"C"]
			],
		];
	}
	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);
	
	// Magasin groupés
	if( $_GET["magasin_groupe"] != "" ){ 
        $rapport->SetFont('helvetica', 'B', 10);
    	$rapport->Cell(0, 0, L("succursales","o"), 0, 1, 'L', false, '', 0, false, 'T', 'B');
    	
    	foreach ($dataGroup as $id_article => $rowRapport){
    	    $listChamps = [];
    	    $listChamps[] = $rowRapport["nom"];
    	    $listChamps[] = $rowRapport["matched"];
    	    $listChamps[] = ($rowRapport["date_vente"] != "0000") ? formatDateUTF8nonHTML($rowRapport["date_vente"]) : "N/A";
    	    $listChamps[] = $rowRapport["nb_stock"];
    	    $listChamps[] = $rowRapport["num_four"];
    	    $listChamps[] = $rowRapport["PLU"];
    	    
    	    $rapport->writeLigneRapport3wrap( $listChamps );
        }
        
    	$rapport->writeLigneGrandTotal( [ count($dataGroup)." produits", null,null,null,null,null], [true,false,false,false,false,false] );
	} else{
	    $isfirst = true;
	    foreach($data as $ID_MAGASIN => $dataMag){
	         if (!$isfirst){
        		$rapport->Ln(6);
        	}
        	$isfirst = false;
    		
    		if( count($listID_MAGASINcanaccess) > 1 ){
    			$rapport->SetFont('helvetica', 'B', 10);
    			$rapport->Cell(0, 0, $allMag[$ID_MAGASIN]["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
		    }
	        
	        foreach ($dataMag as $id_article => $rowRapport) {
	            $listChamps = [];
        	    $listChamps[] = $rowRapport["nom"];
        	    $listChamps[] = ($rowRapport["date_vente"] != "0000") ? formatDateUTF8nonHTML($rowRapport["date_vente"]) : "N/A";
        	    $listChamps[] = $rowRapport["nb_stock"];
        	    $listChamps[] = $rowRapport["num_four"];
        	    $listChamps[] = $rowRapport["PLU"];
        	    
        	    $rapport->writeLigneRapport3wrap( $listChamps );
	        }
	        
	        $rapport->writeLigneGrandTotal( [ count($dataMag)." produits", null,null,null,null], [true,false,false,false,false] );
	    }
	}
    
    ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");
} else{
    ?>
    <section id="main" class="main-wrap bgc-white-darkest print" role="main">
		<!-- Start SubHeader-->
		<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
			<h1 class="page-title pull-left fs-4 fw-light">
				<i class="fa fa-bar-chart icon-mr fs-4"></i>
				<span class="hidden-xs-down"><?= L("rap_produit_stagnant","o");?></span>
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
												    <!-- =============================================== DATES ====================================================================--> 
													<div class="col-md-6">
														<div class="input-group bs-datepicker input-daterange picker-range">
															<input type="text" class="form-control" name="from" id="from" value="<?= empty(htmlentities($_GET["from"])) ? date("Y-m-d", strtotime(date("Y-m-d"). ' - 30 days')):htmlentities($_GET["from"])?>">
															<span class="input-group-addon px-3"><?= L("to"); ?></span>
															<input type="text" class="form-control" name="to" id="to" value="<?= empty(htmlentities($_GET["to"])) ? date("Y-m-d") : htmlentities($_GET["to"])?>">
														</div>
													</div>
													<!-- =================================================== Par fournisseur ================================================================--> 
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
													<!-- ============================================= Par distributeur ======================================================================--> 
													<div class="col-md-3">
														<div class="form-group input-group">
															<select name="distributeur" class="form-control">
																<option value="">Par distributeur</option>
																<?php
																$enonce = "SELECT * FROM fournisseur 
																            WHERE est_fournisseur IS NOT NULL 
																            ORDER BY nom";
																$resultFour = $dbAnimoCaisse->query($enonce);
																while($rowFour = $resultFour->fetch_assoc()){
																	printf("<option value='%s'%s>%s</option>", $rowFour["id_fournisseur"], ($rowFour["id_fournisseur"] == $_GET["distributeur"] ? " selected" : ""), $rowFour["nom"]);
																}
																?>
															</select>
														</div>
													</div>
													<!-- ============================================= select magasin ======================================================================--> 
													<?php 
													if( count($listID_MAGASINcanaccess) > 1 ){
													    ?>
    													<div class="col-md-3">
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
													    <?php 
													} ?>
													<!-- =============================================== Par mot clé ====================================================================--> 
													<div class="col-md-3">
														<div class="form-group input-group">
															<input type="text" class="form-control" name="keywrd" id="keywrd" placeholder="<?php echo $L['keyword'];?>" value="<?php echo $_GET['keywrd']?>">
														</div>
													</div>
    												<!-- ========================================================== Par departement =========================================================--> 
    												<div class="col-md-2">
    													<?php $getDepartement = query('select * from departement where id_departement >= 1 AND id_departement <= 989 ORDER BY departement.NOM ASC',[],$dbAnimoCaisseDefault);?>
    													<select class="ui search simple-select select-dropdown fluid" name="id_departement" >
    														<option value="" disabled selected><?php echo L('departement',"o"); ?></option>
    														<?php while ( $rowDepartement = $getDepartement->fetch_assoc()){?>
    														<option value="<?php echo $rowDepartement['id_departement']?>" 
    														    <?php if( $rowArticle['id_departement'] == $rowDepartement['id_departement']){ echo 'selected';}?>>
    														    <?php echo $rowDepartement['nom']?>
    														</option>
    														<?php }?>
    													</select>
    												</div>
													<!-- ===================================================================================================================--> 
													<div class="col-md-2">
														<div class="ui dynamic checkbox checked pt-1">
															<input type="checkbox" name="showItemNeverSold" value="1" class="form-control" <?= ($_GET["showItemNeverSold"]=="1")?"checked":"" ?> />
															<label><?= L("aff_prod_pas_ventes") ?></label>
														</div>
    												</div>
    												<!-- ===================================================================================================================--> 
    												<div class="col-md-2">
														<div class="ui dynamic checkbox checked pt-1">
															<input type="checkbox" name="magasin_groupe" value="1" class="form-control" <?= ($_GET["magasin_groupe"]=="1")?"checked":"" ?> />
															<label> <?= L("magasin_groupe","o") ?> </label>
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
										    
										    // ============================= MODE Magasins groupés ===============================
										    if($_GET["magasin_groupe"] != ""){
                                             msg_output('<h5 class="block">'.L('Avis').'</h5>'.L("info_champ_m").'','info')?>
										        <h3> <?= L("succursales","o") ?> </h3>
    											<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
    												<div class="fixed-table-header" style="display: none;">
    													<table></table>
    												</div>
    												<div class="fixed-table-body" style="min-height: 100px;">
    													<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-striped table-condensed">
    														<thead>
    															<tr>
    															    <th> 
    															        <a href="index.php?<?= rebuildQueryString(['orderby'=>'nom','sens'=>($orderby == 'nom' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> <?= L("nom_produit","o") ?> <?= '<i class="fa fa-sort'.(($orderby == 'nom' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a> 
    															    </th>
    															    <th style="text-align:left; width:6%">
        															    #Match
        															</th>
    																<th>
    																    <a href="index.php?<?= rebuildQueryString(['orderby'=>'date_vente','sens'=>($orderby == 'date_vente' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> <?= L("dernière vente","o") ?> <?= '<i class="fa fa-sort'.(($orderby == 'date_vente' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>
    																</th>
    																<th style="text-align:center">
    																    <a href="index.php?<?= rebuildQueryString(['orderby'=>'nb_stock','sens'=>($orderby == 'nb_stock' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> <?= L("articles_inventaire","o") ?> <?= '<i class="fa fa-sort'.(($orderby == 'nb_stock' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>
    																</th>
    																<th style="text-align:center"><?= L("Code") ?></th>
    																<th style="text-align:center"><?= L("plu","o") ?></th>
    															</tr>
    														</thead>
    														<tbody>
    															<?php
    															foreach ($dataGroup as $id_article => $rowRapport) {   // $rowRapport["nom"];
    																?>
    																<tr>
    																	<td>
    																		<a href="?p=produits&id=<?= urlencode($id_article)?>"><?php echo $rowRapport['nom'];?></a>
    																	</td>
    																	<td style="text-align:center">
        																    <?= $rowRapport["matched"]; ?>
        																</td>
    																	<td style="white-space: nowrap">
    																		<?= ($rowRapport["date_vente"] != "0000") ? formatDateUTF8($rowRapport["date_vente"]) : "N/A" ?>
    																	</td>
    																	<td style="text-align:center;">
    																		<?= $rowRapport["nb_stock"] ?>
    																	</td>
    																	<td style="text-align:center;">
    																		<?= $rowRapport['num_four'] ?>
    																	</td>
    																	<td style="text-align:center;">
    																		<a href="?p=produits&upc=<?= $rowRapport["PLU"]?>"><?= $rowRapport["PLU"]?></a>
    																	</td>
    																</tr>
    																<?php
    															} ?>
    													</tbody>
    													<tfoot>
    														<tr style="font-weight:bold">
    															<td><?= count($dataGroup)." produits"; ?></td>
    															<td></td>
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
										    // Par magasin
										    } else{
										        // ============================= MODE Magasins SANS groupés ===============================
    										    foreach($data as $ID_MAGASIN => $dataMag){
        											?>
        											<h3> <?= $allMag[$ID_MAGASIN]["M_NOM"] ?> </h3>
        											<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
        												<div class="fixed-table-header" style="display: none;">
        													<table></table>
        												</div>
        												<div class="fixed-table-body" style="min-height: 100px;">
        													<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-striped table-condensed">
        														<thead>
        															<tr>
        															    <th> 
        															        <a href="index.php?<?= rebuildQueryString(['orderby'=>'nom','sens'=>($orderby == 'nom' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> <?= L("nom_produit","o") ?> <?= '<i class="fa fa-sort'.(($orderby == 'nom' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a> 
        															    </th>
        																<th>
        																    <a href="index.php?<?= rebuildQueryString(['orderby'=>'date_vente','sens'=>($orderby == 'date_vente' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> <?= L("derniere_vente","o") ?> <?= '<i class="fa fa-sort'.(($orderby == 'date_vente' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>
        																</th>
        																<th style="text-align:center">
        																    <a href="index.php?<?= rebuildQueryString(['orderby'=>'nb_stock','sens'=>($orderby == 'nb_stock' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> <?= L("articles_inventaire","o") ?> <?= '<i class="fa fa-sort'.(($orderby == 'nb_stock' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>
        																</th>
        																<th style="text-align:center"><?= L("Code") ?></th>
        																<th style="text-align:center"><?= L("plu","o") ?></th>
        															</tr>
        														</thead>
        														<tbody>
        															<?php
        															foreach ($dataMag as $id_article => $rowRapport) {
        																?>
        																<tr>
        																	<td>
        																		<a href="?p=produits&id=<?= urlencode($id_article)?>"><?php echo $rowRapport['nom'];?></a>
        																	</td>
        																	<td style="white-space: nowrap">
        																		<?= ($rowRapport["date_vente"] != "0000") ? formatDateUTF8($rowRapport["date_vente"]) : "N/A" ?>
        																	</td>
        																	<td style="text-align:center;">
        																		<?= $rowRapport["nb_stock"] ?>
        																	</td>
        																	<td style="text-align:center;">
        																		<?= $rowRapport['num_four'] ?>
        																	</td>
        																	<td style="text-align:center;">
        																		<a href="?p=produits&upc=<?= $rowRapport["PLU"]?>"><?= $rowRapport["PLU"]?></a>
        																	</td>
        																</tr>
        																<?php
        															} ?>
        													</tbody>
        													<tfoot>
        														<tr style="font-weight:bold">
        															<td><?= count($dataMag)." produits"; ?></td>
        															<td></td>
        															<td></td>
        															<td></td>
        														</tr>
        													</tfoot>
        													</table>
        												</div>
        											</div>
        											<?php
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