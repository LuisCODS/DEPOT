<?php
ini_set("memory_limit","512M");
set_time_limit(300);

if($_SESSION["utilisateur"]["security"] > 5){redirect('index.php');}

$_GET["date"] = (preg_match("#\d{4,5}\-\d{2}\-\d{2}#",$_GET["date"]))?$_GET["date"]:date("Y-m-d");

$allMag = [];
$queryAllMag = query("select * from MAGASIN where caisse_db is not null order by M_NOM asc",[],$mysqli);
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}

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

$listUserCanAccess = [];
foreach ( $listID_MAGASINcanaccess as $ID_MAGASIN ){
	$enonce = "SELECT * FROM {$allMag[$ID_MAGASIN]["caisse_db"]}.utilisateur where inactif is null and (nom != '' or prenom != '') ORDER BY prenom, nom";
	$resultUser = query($enonce,[],$dbAnimoCaisse);
	while( $uneLigneUser = $resultUser->fetch_assoc() ){
		if ( !isset($listUserCanAccess[$uneLigneUser["id_intranet"]]) ){
			$listUserCanAccess[$uneLigneUser["id_intranet"]] = $uneLigneUser;
		}
	}
}

uasort( $listUserCanAccess, function($a,$b){
	if ( strtoupper($a["prenom"]) < strtoupper($b["prenom"]) ){
		return -1;
	} elseif( strtoupper($a["prenom"]) > strtoupper($b["prenom"]) ){
		return 1;
	}
	if ( strtoupper($a["nom"]) < strtoupper($b["nom"]) ){
		return -1;
	} elseif( strtoupper($a["nom"]) > strtoupper($b["nom"]) ){
		return 1;
	}
	return 0;
});

$data = [];

// =============================== SOUMISSION DU FORM ====================================
if ( $_GET["search"] == "1" ){
    
	//Faire une liste de retrait
	$listVendu = [];
	if ( $_SESSION["utilisateur"]["security"] < 2 and $_GET["HAS_DATE_RETIRE"] == "1" and preg_match("#\d{4}\-\d{2}\-\d{2}#",$_GET["DATE_RETIRE"]) ){
		$_GET["DATE_RETIRE"] .= " 00:00:00";
		//$_GET["DATE_RETIRE"] = "2020-01-07 17:00:00";
		
		foreach ( $listID_MAGASIN as $ID_MAGASIN ){
			$resulThismagasin= $mysqli->query("SELECT * FROM MAGASIN WHERE ID_MAGASIN = '".$ID_MAGASIN."'");
			$rowThismagasin = $resulThismagasin->fetch_assoc();
	
			$listVendu[$ID_MAGASIN] = [];
			$query = "select facture_item.id_article, facture_item.nb
					  from {$rowThismagasin['caisse_db']}.facture_item
					  join {$rowThismagasin['caisse_db']}.facture using (id_facture)
			 	  	  where facture.date_insert >= '{$_GET["DATE_RETIRE"]}' and facture_item.id_article is not null";
			$result = $mysqli->query($query  );
			while( $uneLigneArticleFact = $result->fetch_assoc() ){
				if ( !isset($listVendu[$ID_MAGASIN][$uneLigneArticleFact["id_article"]])  ){
					$listVendu[$ID_MAGASIN][$uneLigneArticleFact["id_article"]] = 0;
				}
				$listVendu[$ID_MAGASIN][$uneLigneArticleFact["id_article"]] += $uneLigneArticleFact["nb"];
			}
		}
	} 
	
	$listWhereAnd = [];
	$listWhereAnd[] = "1=1";
	
	if ( preg_match("#^\d+$#",$_GET["id_distributeur"]) ){
		$listWhereAnd[] = "link_article_four.id_fournisseur = " . $_GET["id_distributeur"];
	}
	if ( preg_match("#^\d+$#",$_GET["id_departement"]) ){
	    $listWhereAnd[] = "(article.id_departement = " . $_GET["id_departement"]."
                            and article.id_departement < 900 and article.id_departement NOT IN(1,8,9,10)
                            )";
	}

	$strWhereAnd = implode(" and ",$listWhereAnd);
	
	foreach ( $listID_MAGASIN as $ID_MAGASIN ){
		$resulThismagasin= $mysqli->query("SELECT * FROM MAGASIN WHERE ID_MAGASIN = '".$ID_MAGASIN."'");
		$rowThismagasin = $resulThismagasin->fetch_assoc();
	
		//$listColonneTri = ["article.desc_fr"];
		if($_POST['order']!=''){ $order = $_POST['order']; }else{ $order = 'article.id_article';}
		if($_POST['sens']==''){ $sens = 'asc';}else{ $sens = $_POST['sens'];}
		$articles_query = "select article.*
							from ".$rowThismagasin['caisse_db'].".article
								left join link_article_four using(id_article)
							where article.stock > 0 and $strWhereAnd
							GROUP BY article.id_article
							order by ".$order.' '.$sens;
		
		$resultArt= $dbAnimoCaisse->query($articles_query) or die($dbAnimoCaisse->error);
		
		$data[$ID_MAGASIN] = [
		                        "liste"=>[],
		                        "totalCost"=>0,
		                        "totalStock"=>0
	                        ];
		
		while ($uneLigneArticle = $resultArt->fetch_assoc()){
			$uneLigneArticle["listPLU"] = [];
			if ( $uneLigneArticle["PLU"] ) $uneLigneArticle["listPLU"][] = $uneLigneArticle["PLU"];
			if ( $uneLigneArticle["PLU2"] ) $uneLigneArticle["listPLU"][] = $uneLigneArticle["PLU2"];
			if ( $uneLigneArticle["PLU3"] ) $uneLigneArticle["listPLU"][] = $uneLigneArticle["PLU3"];
		
			if ( isset($listVendu[$ID_MAGASIN][$uneLigneArticle["id_article"]]) ){
				$uneLigneArticle["stock"] -= $listVendu[$ID_MAGASIN][$uneLigneArticle["id_article"]];
			}
			if ( $uneLigneArticle["stock"] < 0 ){
				$uneLigneArticle["stock"] = 0;
			}
			
			$uneLigneArticle["totalCost"] = 0;
		
			$enonce =  "select fournisseur.*, link_article_four.num_four, link_article_four.prix_coutant, link_article_four.prix_caisse, link_article_four.id_link_article_four
						from fournisseur
							   join link_article_four using(id_fournisseur)
						where link_article_four.id_article = ? AND link_article_four.prix_coutant != '' and  discontinued is null
					order by date_update desc
						limit 1";
			$resultFour = query($enonce,[$uneLigneArticle["id_article"],],$dbAnimoCaisse) or die($dbAnimoCaisse->error);
			if ( $uneLigneFour = $resultFour->fetch_assoc() ){
		
				if($uneLigneArticle["boite_nb"] > 1){
					$uneLigneFour["costUnitaire"] = round($uneLigneFour["prix_caisse"] / $uneLigneArticle["boite_nb"], 2) ;
				}else{
					$uneLigneFour["costUnitaire"] = $uneLigneFour["prix_coutant"];
				}
				$uneLigneArticle["uneLigneFour"] = $uneLigneFour;
				$uneLigneArticle["totalCost"] += $uneLigneFour["costUnitaire"] * $uneLigneArticle["stock"];
		
			}
			$data[$ID_MAGASIN]["totalCost"] += $uneLigneArticle["totalCost"];
			$data[$ID_MAGASIN]["totalStock"] += $uneLigneArticle["stock"];
			$data[$ID_MAGASIN]["liste"][] = $uneLigneArticle;
		}
	}
	
    // ======================   GESTION TRI================================		
    $listColonneTri = ["desc_fr","stock","totalCost"];
    
    if($_POST['order']!=''){ 
        $order = $_POST['order'];
    }else{ 
        $order = $listColonneTri[0];
    }
    if($_POST['sens']==''){
        $sens = 'asc';
    }else{ 
        $sens = $_POST['sens'];
    }
    
    foreach($data as $ID_MAGASIN=>$dataMag){
        uasort( $data[$ID_MAGASIN]["liste"], function($a,$b){
        	if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
        		return ($_GET["sens"] == "desc") ? 1 : -1;//1 c plus petit, -1 plus grand 
        	} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
        		return ($_GET["sens"] == "desc") ? -1 : 1;
        	}
        	return 0;
        });    
    }      
    
}//fin form soumission


if ( $_GET["getFile"] == "1" and $data ){
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_LANDSCAPE, RPDF_SHOWCOL_ALLPAGE );
		#$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}

	$titre = L("rap_inventaire","o");

	$listSoustitre = [];
	$listSoustitre[] = ["en dates du", formatDateUTF8nonHTML( $_GET["date"] ) ];
	//$listSoustitre[] = ["devise",  $uneLigneTaxePaye["DEVISE_ABBR"] ];

	$listEnteteColonne = [
			[ ["text"=>"Nom du produit","width"=>150,"align"=>"L"], ["text"=>L("Distrubuteur(s)",'o'),"width"=>60,"align"=>"L"],["text"=>L("Qté",'o'),"width"=>15,"align"=>"C"],["text"=>L("total",'o'),"width"=>25,"align"=>"R"],],
	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	//
	$isfirst = true;
	foreach ( $data as $ID_MAGASIN => $dataMag ){
		if (!$isfirst){
    		$rapport->Ln(2);
    	}
    	$isfirst = false;
    
    	if( sizeof($listID_MAGASINcanaccess) > 1 ){
    		$rapport->SetFont('helvetica', 'B', 12);
    		$rapport->Cell(0, 0, $allMag[$ID_MAGASIN]["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
    	}
    	
		foreach ( $dataMag["liste"] as $uneLigneArticle){
			$listChamps = [];
			$listChamps[0] = $uneLigneArticle["desc_fr"]." (".implode("|",$uneLigneArticle["listPLU"]).")";
	
			if ( $uneLigneArticle["uneLigneFour"] ){
				if ( $uneLigneArticle["boite_nb"] > 1){
					$listChamps[1] = $uneLigneArticle["uneLigneFour"]["nom"] .' - '.$uneLigneArticle["uneLigneFour"]["num_four"].' - '.formatPrix($uneLigneArticle["uneLigneFour"]["prix_caisse"]).' ('.$uneLigneArticle["boite_nb"].')';
				}else{
					$listChamps[1] = $uneLigneArticle["uneLigneFour"]["nom"].' - '.$uneLigneArticle["uneLigneFour"]["num_four"].' - '.formatPrix($uneLigneArticle["uneLigneFour"]["prix_coutant"]);
				}
			}
	
			$listChamps[2] = $uneLigneArticle["stock"];
			$listChamps[3] = nfs($uneLigneArticle["totalCost"]);
	
			$rapport->writeLigneRapport3wrap( $listChamps );
		}
		
		$rapport->writeLigneGrandTotal( [ L("nombre de produit")." : ".sizeof($dataMag["liste"]),null, $dataMag["totalStock"], nfs($dataMag["totalCost"])], [false,false,true,true] );
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
				<span class="hidden-xs-down"><?= L("rap_inventaire","o");?></span>
			</h1>
			<h1 id="date_label" class="page-title pull-right fs-4 fw-light print-only"></h1>
			<div class="smart-links no-print">
				<ul class="nav" role="tablist">
					<?php /*?><li class="nav-item">
						<a class="nav-link clear-style aside-trigger" onclick="window.print();" href="javascript:;">
							<i class="fa fa-print "></i>
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
													<div class="col-md-3">
														<div class="form-group input-group">
															<select name="id_distributeur" class="form-control">
																<option value="">Tous les distributeurs</option>
																<?php
																$enonce = "SELECT * FROM fournisseur WHERE est_fournisseur IS NOT NULL ORDER BY nom";
																$resultFour = $dbAnimoCaisse->query($enonce);
																while($rowFour = $resultFour->fetch_assoc()){
																	printf("<option value='%s'%s>%s</option>", $rowFour["id_fournisseur"], ($rowFour["id_fournisseur"] == $_GET["id_distributeur"] ? " selected" : ""), $rowFour["nom"]);
																}
																?>
															</select>
														</div>
													</div>
													<div class="col-md-3">
														<div>
															<select class="form-control" name="id_departement">
																<option value="">Tous les départements</option>
																<?php 
																$getdep = query('select id_departement, nom from departement where id_departement < 900 and id_departement not in(1,8,9,10)',[],$dbAnimoCaisseDefault);
																while($rowdep = $getdep->fetch_assoc()){
																    printf('<option value="%s"%s>%s</option>',$rowdep['id_departement'],($rowdep['id_departement'] == $_GET['id_departement'] ? ' selected' : ''),$rowdep['nom']);
																}
																?>
															</select>
														</div>
													</div>
													<?php if( sizeof($listID_MAGASINcanaccess) > 1 ){?>
													<div class="col-md-6">
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
												</div>
												
												<?php if ( $_SESSION["utilisateur"]["security"] < 2 ){ ?>
												<div class="row" style="margin-bottom:15px;">
													<div class="col-md-3 col-xl-2">
														<label>
															<input type="checkbox" name="HAS_DATE_RETIRE" value="1" <?= $_GET["HAS_DATE_RETIRE"]=="1"?"checked":"" ?> /> retirer les ventes depuis
														</label>
													</div>
													<div class="col-md-3">
														<input type="text" class="form-control datepicker" name="DATE_RETIRE" value="<?= ($_GET["HAS_DATE_RETIRE"] == "1")?htmlentities($_GET["DATE_RETIRE"]):"" ?>" />
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
											foreach ( $data as $ID_MAGASIN => $dataMag ){
											?>
											<h3 class="">Animo Etc <?= $allMag[$ID_MAGASIN]['M_NOM']?></h3>
											<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
												<div class="fixed-table-body">
													<table id="tableListPointsAnimo" class="card-view-no-edit page-size-table table table-no-bordered">
														<thead>
															<tr>
																<th class="text-left">
																	<div><?= L("id"); ?></div>
																</th>
    															<th style="text-align:left">
    															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'desc_fr', 'sens'=>($_GET["orderby"] == 'desc_fr' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															         <?= L("articlenom","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'desc_fr' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    															    </a>
    														    </th> 
																<th class="text-left">
																	<div> <?= L('Distributeur(s)');?></div>
																	<div class="fht-cell"></div>
																</th>
    															<th style="text-align:center">
    															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'stock', 'sens'=>($_GET["orderby"] == 'stock' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															         <?= L("stock","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'stock' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    															    </a>
    														    </th> 
    															<th style="text-align:right">
    															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'totalCost', 'sens'=>($_GET["orderby"] == 'totalCost' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															         <?= L("total","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalCost' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    															    </a>
    														    </th> 
															</tr>
														</thead>
														<tbody >
															<?php
															foreach ( $dataMag["liste"] as $uneLigneArticle){?>
																<tr>
																	<th >
																		<?php echo $uneLigneArticle["id_article"];?>
																	</td>
																	<td>
																		<?php
																		//echo $uneLigneArticle["desc_fr"]."<br/><span class='no-print'>".implode("|",$uneLigneArticle["listPLU"])."</span>";
																		?>
																		<a href="?p=produits&id=<?= $uneLigneArticle["id_article"]?>"><?= $uneLigneArticle["desc_fr"]."<br/><span class='no-print'>".implode("|",$uneLigneArticle["listPLU"])."</span>"  ?></a>
																	</td>
																	<td nowrap>
																		<?php
																		if ( $uneLigneArticle["uneLigneFour"] ){
	
																			if ( $uneLigneArticle["boite_nb"] > 1){
																				echo $uneLigneArticle["uneLigneFour"]["nom"] .' - <i>'.$uneLigneArticle["uneLigneFour"]["num_four"].'</i> - '.formatPrix($uneLigneArticle["uneLigneFour"]["prix_caisse"]).' ('.$uneLigneArticle["boite_nb"].')';
																			}else{
																				echo $uneLigneArticle["uneLigneFour"]["nom"].' - <i>'.$uneLigneArticle["uneLigneFour"]["num_four"].'</i> - '.formatPrix($uneLigneArticle["uneLigneFour"]["prix_coutant"]);
																			}
																		}
																		?>
																	</td>
																	<td class="center">
																		<?php echo $uneLigneArticle["stock"];	?>
																	</td>
																	<td class="right">
																		<?php echo  money_format('%n',  $uneLigneArticle["totalCost"]);?>
																	</td>
																</tr>
																<?php }?>
														</tbody>
														<tfoot>
															<tr style="font-weight:bold">
																<td style="text-align:left;"><?php echo sizeof($dataMag["liste"]);?></td>
																<td></td>
																<td></td>
																<td class="center"><?php echo $dataMag["totalStock"]; ?></td>
																<td class="right"><?php echo  money_format('%n', $dataMag["totalCost"]);?></td>
															</tr>
														</tfoot>
													</table>
												</div>
											</div>
											<?php 
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
}?>