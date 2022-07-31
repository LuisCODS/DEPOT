<?php

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


if ( $_GET["search"] == "1" ){
    
	ini_set("memory_limit","256M");
	set_time_limit(300);

	$data = [];
	$listAND = [];
	$listPARAM = [];

	if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) ){
		$listAND[] = " facture.date_insert >= '{$_GET['from']} 00:00:00' ";
	}
	if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
		$listAND[] = " facture.date_insert <= '{$_GET['to']} 23:59:59' ";
	}

	if($_GET["fournisseur"] != ""){
		$listAND[] = "article.id_distributeur = ? ";
		$listPARAM[] = $_GET['fournisseur'];
	}

	$listAND[] = " facture_item.id_article IS NOT NULL and facture_item.type = 'PLU'";
	
	$resultMag = query("SELECT * FROM MAGASIN WHERE ID_MAGASIN in ($listID_MAGASINstr) order by M_NOM",[],$mysqli);
	
	while( $uneLigneAgence = $resultMag->fetch_assoc() ){
	    
		$ID_MAGASIN = $uneLigneAgence["ID_MAGASIN"];
		$nomDB = $uneLigneAgence["caisse_db"];
		
		$data[$ID_MAGASIN] = ["lignes"=>[], mode=>"all", "nb"=>0, "montant"=>0];
		
		if($_GET["fournisseur"] != ""){
			$data[$ID_MAGASIN]["mode"] = "un";
		}
		
		// ================================ Mode UN fournisseur ======================================
		if ( $data[$ID_MAGASIN]["mode"] == "un" ){
		    
			$listAndStr = implode(" AND ", $listAND);
			$enonce = "SELECT sum(facture_item.montant) `montant`, sum(facture_item.nb) `nb`, facture.id_facture, facture.date_insert `date_facture`
						FROM $nomDB.facture_item
							JOIN $nomDB.facture using(id_facture)
							JOIN $nomDB.article using(id_article)
						WHERE $listAndStr
					group by facture.id_facture";
			$resultUn = query($enonce,$listPARAM,$dbAnimoCaisse);
			while ( $uneLigne = $resultUn->fetch_assoc() ){
				$data[$ID_MAGASIN]["lignes"][] = $uneLigne;
				$data[$ID_MAGASIN]["nb"] 	  += $uneLigne["nb"];
				$data[$ID_MAGASIN]["montant"]  += $uneLigne["montant"];
			}
	
			$listTriPosible = ["id_facture","date_facture","nb","montant"];
		} else {
			// ================================ Mode all fournisseurs ======================================
			$data[$ID_MAGASIN]["nb_facture"] = 0;
			
			$resultFour= query("SELECT id_fournisseur, nom `label` 
			                    FROM fournisseur 
			                    WHERE est_distributeur IS NOT NULL ORDER BY nom asc",[],$dbAnimoCaisse);
			while ($uneLigneFour = $resultFour->fetch_assoc() ){
				$uneLigneFour["nb"] = 0;
				$uneLigneFour["montant_moyenne"] = 0;
				$uneLigneFour["nb_facture"] = 0;
				$uneLigneFour["montant"] = 0;
	
				$listAndStr = implode(" AND ", $listAND);
	
				$enonce = "SELECT sum(facture_item.montant) `montant`, sum(facture_item.nb) `nb`, count(facture.id_facture) `nb_facture`, article.id_distributeur
							FROM $nomDB.facture_item
								JOIN $nomDB.facture using(id_facture)
								JOIN $nomDB.article using(id_article)
							WHERE $listAndStr
							and article.id_distributeur = {$uneLigneFour["id_fournisseur"]}
						group by article.id_distributeur";
				$resultSumByFour = query($enonce,$listPARAM,$dbAnimoCaisse);
	
				if($uneLigneSumFour = $resultSumByFour->fetch_assoc()){
				    
					if ( $uneLigneSumFour["nb"] != 0 ){
						$uneLigneSumFour["montant_moyenne"] = round($uneLigneSumFour["montant"] / $uneLigneSumFour["nb"],2);
					} else {
						$uneLigneSumFour["montant_moyenne"] = 0;
					}
	
					foreach( $uneLigneSumFour as $k=>$v){
						$uneLigneFour[$k] = $uneLigneSumFour[$k];
					}
	
					$data[$ID_MAGASIN]["nb"] 		+= $uneLigneSumFour["nb"];
					$data[$ID_MAGASIN]["nb_facture"] += $uneLigneSumFour["nb_facture"];
					$data[$ID_MAGASIN]["montant"] 	+= $uneLigneSumFour["montant"];
				} else {
					//Si cacher ceux à zero
					if ( $_GET["hideFourToZero"] == "1"){
						continue;
					}
				}
	
				$data[$ID_MAGASIN]["lignes"][] = $uneLigneFour;
			}
			$listTriPosible = ["label","nb","nb_facture","montant","montant_moyenne"];
		}
		
        // ======================   GESTION TRIAGE TABLEAU  =================================
        
		if ( !in_array($_GET["orderby"],$listTriPosible) ){
			$_GET["orderby"] = $listTriPosible[0];
		}
		if ( $_GET["sens"] == 'desc' ){
			$_GET["sens"] = "desc";
		} else {
			$_GET["sens"] = "asc";
		}
		
		if ( $data ){
    		//Un fournisseur
    		if ($dataMag["mode"] = "un"){ 
    		     foreach($data as $ID_MAGASIN => $dataMag){
    		        foreach ($dataMag["lignes"] as $rowRapport) {
                		uasort( $data[$ID_MAGASIN]["lignes"], function($a,$b){
                			if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
                				return ($_GET["sens"]=="desc")?1:-1;
                			} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
                				return ($_GET["sens"]=="desc")?-1:1;
                			}
                			return 0;
                		});	  
    		        }
                }
    		//All fournisseurs
    		}else{
        		uasort( $data[$ID_MAGASIN]["lignes"], function($a,$b){
        			if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
        				return ($_GET["sens"]=="desc")?1:-1;
        			} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
        				return ($_GET["sens"]=="desc")?-1:1;
        			}
        			return 0;
        		});		    
    		}		    
		}
	}//fin while
}//fin if
/*

*/

/* ============== GESTION PDF/EXCEL  ================= */
if ( $_GET["getFile"] == "1" and $data ){
    
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}

	$titre = L("Rapport Ventes par fournisseur","o");

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

	if($_GET["fournisseur"] != ""){
		$uneLigneFournisseur = query("select * from fournisseur where id_fournisseur = ?",[$_GET["fournisseur"],],$dbAnimoCaisse)->fetch_assoc();
		$listSoustitre[] = ["fournisseur", $uneLigneFournisseur["nom"] ];
	} else {
		if ( $_GET["hideFourToZero"] == "1"){
			$listSoustitre[] = ["fournisseur", "tous (sauf à zero)" ];
		} else {
			$listSoustitre[] = ["fournisseur", "tous" ];
		}
	}

	foreach( $listID_MAGASIN as $ID_MAGASIN ){
		$listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
	}
	$listSoustitre[] = ["magasin(s)", implode(", ",$listNomMag) ];
	
	if($_GET["fournisseur"] != ""){
		$listEnteteColonne = [
				[ ["text"=>L("fact#",'o'),"width"=>25,"align"=>"C"],["text"=>L("date",'o'),"width"=>30,"align"=>"L"],["text"=>"NB","width"=>15,"align"=>"C"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
		];
		
		$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
		$rapport->setInfoCols(-1);
	} else {
		$listEnteteColonne = [
			[
				["text"=>L("nom fournisseur",'o'),"width"=>60,"align"=>"L"],["text"=>L("nombre articles vendus",'o'),"width"=>35,"align"=>"C"],["text"=>"nombre de facture","width"=>35,"align"=>"C"],["text"=>L("moyenne prix",'o'),"width"=>25,"align"=>"R"],["text"=>L("montant vente",'o'),"width"=>25,"align"=>"R"],
			],
		];
		
		$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
		$rapport->setInfoCols(-1);
	}
	
	foreach($data as $ID_MAGASIN => $dataMag){
		$infomag= query("select * from MAGASIN where ID_MAGASIN = ?",[$ID_MAGASIN,],$mysqli)->fetch_assoc();
		if( sizeof($listID_MAGASINcanaccess) > 1 ){
			$rapport->SetFont('helvetica', 'B', 12);
			$rapport->Cell(0, 0, $infomag["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
		}
	
		if ( $dataMag["mode"] == "un" ){
			//
			foreach ( $dataMag["lignes"] as $uneLigneFactItem){
				$listChamps = [];
				$listChamps[] = $uneLigneFactItem["id_facture"];
				$listChamps[] = formatDateUTF8nonHTML($uneLigneFactItem["date_facture"]);
				$listChamps[] = $uneLigneFactItem["nb"];
				$listChamps[] = nfs($uneLigneFactItem["montant"]);
	
				$rapport->writeLigneRapport3wrap( $listChamps );
			}
	
			$rapport->writeLigneGrandTotal( [ null,null,$dataMag["nb"],nfs($dataMag["montant"])], [false,false,true,true] );
		} else {
			//label nb nb_facture montant_moyenne montant
			foreach ( $dataMag["lignes"] as $uneLigneFactItem){
				$listChamps = [];
				$listChamps[] = $uneLigneFactItem["label"];
				$listChamps[] = $uneLigneFactItem["nb"];
				$listChamps[] = $uneLigneFactItem["nb_facture"];
				$listChamps[] = nfs($uneLigneFactItem["montant_moyenne"]);
				$listChamps[] = nfs($uneLigneFactItem["montant"]);
	
				$rapport->writeLigneRapport3wrap( $listChamps );
			}
	
			$rapport->writeLigneGrandTotal( [ null,$dataMag["nb"],$dataMag["nb_facture"],null,nfs($dataMag["montant"])], [false,true,true,false,true] );
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
				<span class="hidden-xs-down"><?= L("Rapport Ventes par fournisseur","o");?></span>
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
													<div class="col-md-6">
														<div class="input-group bs-datepicker input-daterange picker-range">
															<input type="text" class="form-control" name="from" id="from" value="<?= empty(htmlentities($_GET["from"])) ? date("Y-m-d", strtotime(date("Y-m-d"). ' - 30 days')):htmlentities($_GET["from"])?>">
															<span class="input-group-addon px-3"><?= L("to"); ?></span>
															<input type="text" class="form-control" name="to" id="to" value="<?= empty(htmlentities($_GET["to"])) ? date("Y-m-d") : htmlentities($_GET["to"])?>">
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
													
													<div class="col-md-3">
														<div class="form-group input-group">
															<select name="fournisseur" class="form-control">
																<option value="">Tous les fournisseurs</option>
																<?php
																$enonce = "SELECT * FROM fournisseur WHERE est_distributeur IS NOT NULL AND inactif IS NULL ORDER BY nom";
																$resultFour = $dbAnimoCaisse->query($enonce);
																while($rowFour = $resultFour->fetch_assoc()){
																	printf("<option value='%s'%s>%s</option>", $rowFour["id_fournisseur"], ($rowFour["id_fournisseur"] == $_GET["fournisseur"] ? " selected" : ""), $rowFour["nom"]);
																}
																?>
															</select>
														</div>
													</div>
													<div class="col-md-3">
														<div class="ui dynamic checkbox checked pt-1">
															<input type="checkbox" name="hideFourToZero" value="1" class="form-control" <?= ($_GET["hideFourToZero"]=="1")?"checked":"" ?> />
															<label>Masquer les résultats à zero</label>
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
											    
												$infomag= query("select * from MAGASIN where ID_MAGASIN = ?",[$ID_MAGASIN,],$mysqli)->fetch_assoc();
												
												if( sizeof($listID_MAGASINcanaccess) > 1 ){ 
												    ?><h2><?= $infomag["M_NOM"] ?></h2><?php
											    }
												//Mode un fournisseur
												if ($dataMag["mode"] == "un"){
													?>
													<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
														<div class="fixed-table-header" style="display: none;">
															<table></table>
														</div>
														<div class="fixed-table-body">
															<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
																<thead>
																	<tr>
             															<th style="text-align:center;">
                   															<a href="index.php?<?= rebuildQueryString(['orderby'=>'id_facture','sens'=>($_GET["orderby"] == 'id_facture' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															     # Facture <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'id_facture' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            																</a>  
            															</th> 																		
                														<th style="text-align:left;">
                   															<a href="index.php?<?= rebuildQueryString(['orderby'=>'date_facture','sens'=>($_GET["orderby"] == 'date_facture' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															    Date <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'date_facture' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            																</a>  
            															</th> 
													
                 														<th style="text-align:center;">
                   															<a href="index.php?<?= rebuildQueryString(['orderby'=>'nb','sens'=>($_GET["orderby"] == 'nb' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															    Nombre d'articles <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nb' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            																</a>  
            															</th> 
                 														<th style="text-align:right;">
                   															<a href="index.php?<?= rebuildQueryString(['orderby'=>'montant','sens'=>($_GET["orderby"] == 'montant' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															    Montant vente <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'montant' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            																</a>  
            															</th> 		
																	</tr>
																</thead>
																<tbody>
																	<?php
																	foreach ($dataMag["lignes"] as $rowRapport) {
																		?>
																		<tr>
																			<td style="text-align:center">
																				<a class="ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?php echo $rowRapport["id_facture"];?>&ID_MAGASIN=<?php echo $_SESSION["mag"];?>"><?= $rowRapport["id_facture"]; ?></a>
																			</td>
																			<td>
																				<?= formatDateUTF8($rowRapport["date_facture"]) ?>
																			</td>
																			<td style="text-align:center;">
																				<?= $rowRapport["nb"] ?>
																			</td>
																			<td style="text-align:right;">
																				<?= formatPrix($rowRapport['montant']); ?>
																			</td>
																		</tr>
																		<?php
																	} ?>
															</tbody>
															<tfoot>
																<tr style="font-weight:bold">
																	<td></td>
																	<td></td>
																	<td style="text-align:center;"><?= $dataMag["nb"] ?></td>
																	<td style="text-align:right;"><?= formatPrix($dataMag["montant"]);?></td>
																</tr>
															</tfoot>
															</table>
														</div>
													</div>
													<?php
												
											    //All fournisseurs   
												} else {
													?>
													<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
														<div class="fixed-table-header" style="display: none;">
															<table></table>
														</div>
														<div class="fixed-table-body">
															<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
																<thead>
																	<tr>
             															<th style="text-align:left;">
                   															<a href="index.php?<?= rebuildQueryString(['orderby'=>'label','sens'=>($_GET["orderby"] == 'label' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															     Nom fournisseur  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'label' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            																</a>  
            															</th> 
             															<th style="text-align:center;">
                   															<a href="index.php?<?= rebuildQueryString(['orderby'=>'nb','sens'=>($_GET["orderby"] == 'nb' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															     Nombre articles vendus  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nb' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            																</a>  
            															</th>             															
               															<th style="text-align:center;">
                   															<a href="index.php?<?= rebuildQueryString(['orderby'=>'nb_facture','sens'=>($_GET["orderby"] == 'nb_facture' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															     Nombre de factures <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nb_facture' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            																</a>  
            															</th>             															
            															<th style="text-align:right;">
                   															<a href="index.php?<?= rebuildQueryString(['orderby'=>'montant_moyenne','sens'=>($_GET["orderby"] == 'montant_moyenne' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															    Moyenne Prix<?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'montant_moyenne' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            																</a>  
            															</th>             															
               															<th style="text-align:right;">
                   															<a href="index.php?<?= rebuildQueryString(['orderby'=>'montant','sens'=>($_GET["orderby"] == 'montant' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                															    Montant vente <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'montant' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            																</a>  
            															</th> 
																	</tr>
																</thead>
																<tbody>
																	<?php
																	foreach ($dataMag["lignes"] as $rowRapport) {
																		?>
																		<tr>
																			<td>
																				<?= $rowRapport["label"]; ?>
																			</td>
																			<td style="text-align:center;">
																				<?= $rowRapport["nb"] ?>
																			</td>
																			<td style="text-align:center;">
																				<?= $rowRapport["nb_facture"] ?>
																			</td>
																			<td style="text-align:right;">
																				<?= formatPrix($rowRapport['montant_moyenne']); ?>
																			</td>
																			<td style="text-align:right;">
																				<?= formatPrix($rowRapport['montant']); ?>
																			</td>
																		</tr>
																		<?php
																	} ?>
															</tbody>
															<tfoot>
																<tr style="font-weight:bold">
																	<td></td>
																	<td style="text-align:center;"><?= $dataMag["nb"] ?></td>
																	<td style="text-align:center;"><?= $dataMag["nb_facture"] ?></td>
																	<td></td>
																	<td style="text-align:right;"><?= formatPrix($dataMag["montant"]);?></td>
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
