<?php

ini_set("memory_limit","256M");
set_time_limit(300);



if ( $_GET["to"]  )
{
	if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
		$daterange = "  (facture.date_insert >= '{$_GET['from']} 00:00:00' AND facture.date_insert <= '{$_GET['to']} 23:59:59') and ";
	} else if ( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) ){
		$daterange = "  facture.date_insert >= '{$_GET['from']} 00:00:00' and ";
	} else if ( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
		$daterange = "  facture.date_insert <= '{$_GET['to']} 23:59:59' and ";
	}
	
	if ( preg_match('#^\d+$#',$_GET['limit']) ){
		$limit = $_GET['limit'];
	}

	$data = [];
	$queryRaport = "select facture.num_avantages, facture_item.montant `totalsanstaxe`, facture_item.id_departement, facture_item.id_article,
						   COALESCE(facture_item.id_departement,article.id_departement) `id_departement_commun`, max(article_categorie.is_bouffe) `is_bouffe`
					  from facture_item 
					  		left join facture USING(id_facture)
					  		left join article on(article.id_article = facture_item.id_article)
					  		LEFT JOIN animoetc_caisse_default.article_categorie_link on(animoetc_caisse_default.article_categorie_link.id_article = facture_item.id_article)
					  		LEFT JOIN animoetc_caisse_default.article_categorie USING(id_categorie)
					  where $daterange num_avantages is not null
				   GROUP BY facture_item.id_facture_item";

	$resultTop = $dbAnimoCaisse->query($queryRaport) or die($dbAnimoCaisse->error);
	while( $uneLigneTop = $resultTop->fetch_assoc() ){
		//Greffer le nom du client
		if ( !isset($data[$uneLigneTop["num_avantages"]]) ){
			$data[$uneLigneTop["num_avantages"]] = ["client"=>null,"total_by_dep"=>[],"total_bouffe"=>0,"total_acc"=>0,"total"=>0];
			
			$resultClient = query("select * from CLIENT where cartedepoint = ? order by ID_CLIENT limit 1",[$uneLigneTop["num_avantages"],],$mysqli);
			if ( $uneLigneClient = $resultClient->fetch_assoc() ){
				$data[$uneLigneTop["num_avantages"]]["client"] = $uneLigneClient;
			}
		}
		
		if (!isset($data[$uneLigneTop["num_avantages"]]["total_by_dep"])){
			$data[$uneLigneTop["num_avantages"]]["total_by_dep"][$uneLigneTop["id_departement_commun"]] = 0;
		}
		$data[$uneLigneTop["num_avantages"]]["total_by_dep"][$uneLigneTop["id_departement_commun"]] += $uneLigneTop["totalsanstaxe"];
		
		if ( $uneLigneTop["is_bouffe"] == "1" ){
			$data[$uneLigneTop["num_avantages"]]["total_bouffe"] += $uneLigneTop["totalsanstaxe"];
		} elseif ( !in_array($uneLigneTop["id_departement_commun"], [9,10]) ){  //J'hésite entre   id_departement_commun et id_departement
			$data[$uneLigneTop["num_avantages"]]["total_acc"] += $uneLigneTop["totalsanstaxe"];
		}
		$data[$uneLigneTop["num_avantages"]]["total"] += $uneLigneTop["totalsanstaxe"];
	}
	
	//orderBy
	//   NOTE : pas défaut, c'est l'ordre inverse.
	$orderBy = $_GET["orderby"];
	if ( $orderBy == "chien" ){
		uasort($data,function($a,$b){
			if ($a["total_by_dep"]["3"] > $b["total_by_dep"]["3"]){
				return -1;
			} elseif ($a["total_by_dep"]["3"] < $b["total_by_dep"]["3"]){
				return 1;
			}
			return 0;
		});
	} else if ( $orderBy == "chat" ){
		uasort($data,function($a,$b){
			if ($a["total_by_dep"]["2"] > $b["total_by_dep"]["2"]){
				return -1;
			} elseif ($a["total_by_dep"]["2"] < $b["total_by_dep"]["2"]){
				return 1;
			}
			return 0;
		});
	} else if ( $orderBy == "rongeur" ){
		uasort($data,function($a,$b){
			if ($a["total_by_dep"]["6"] > $b["total_by_dep"]["6"]){
				return -1;
			} elseif ($a["total_by_dep"]["6"] < $b["total_by_dep"]["6"]){
				return 1;
			}
			return 0;
		});
	} else if ( $orderBy == "toilettage" ){
		uasort($data,function($a,$b){
			if ($a["total_by_dep"]["9"]+$a["total_by_dep"]["10"] > $b["total_by_dep"]["9"]+$b["total_by_dep"]["10"]){
				return -1;
			} elseif ($a["total_by_dep"]["9"]+$a["total_by_dep"]["10"] < $b["total_by_dep"]["9"]+$b["total_by_dep"]["10"]){
				return 1;
			}
			return 0;
		});
	} else if ( $orderBy == "bouffe" ){
		uasort($data,function($a,$b){
			if ($a["total_bouffe"] > $b["total_bouffe"]){
				return -1;
			} elseif ($a["total_bouffe"] < $b["total_bouffe"]){
				return 1;
			}
			return 0;
		});
	} else if ( $orderBy == "acc" ){
		uasort($data,function($a,$b){
			if ($a["total_acc"] > $b["total_acc"]){
				return -1;
			} elseif ($a["total_acc"] < $b["total_acc"]){
				return 1;
			}
			return 0;
		});
	} else {
		uasort($data,function($a,$b){
			if ($a["total"] > $b["total"]){
				return -1;
			} elseif ($a["total"] < $b["total"]){
				return 1;
			}
			return 0;
		});
	}
	
	//Set les top
	$limit = intval($_GET["limit"],10);
	$count=0;
	foreach( $data as $k => $uneLigne ){
		if ( $count >= $limit ){
			unset($data[$k]);
			continue;
		}
		$data[$k]["top"] = ++$count;
	}
	
	//Revserse si ordre croissant
	if ( $_GET["ordersens"] == "asc"){
		$data = array_reverse($data,true);
	}
	
}

// ================================== ZONE PDF & EXCEL ==========================================

if ( $_GET["getFile"] == "1" and $data ){
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_LANDSCAPE, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_LANDSCAPE, RPDF_SHOWCOL_ALLPAGE );
	}


	$titre = L("rap_topClient","o");

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

	$listEnteteColonne = [
		[ 
			["text"=>"#","width"=>11,"align"=>"C"], 
			["text"=>L("numéro de carte",'o'),"width"=>25,"align"=>"L"],
			["text"=>L("nom_client",'o'),"width"=>76,"align"=>"L"],
			["text"=>L("chien",'o'),"width"=>20,"align"=>"R"],
			["text"=>L("chat",'o'),"width"=>20,"align"=>"R"],
			["text"=>L("rongeur",'o'),"width"=>20,"align"=>"R"],
			["text"=>L("toilettage",'o'),"width"=>20,"align"=>"R"],
			["text"=>L("nourriture",'o'),"width"=>20,"align"=>"R"],
			["text"=>L("accessoire",'o'),"width"=>20,"align"=>"R"],
			["text"=>L("ventes globales",'o'),"width"=>24,"align"=>"R"],
		],
	];
	
	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	foreach ( $data as $num_avantages => $uneLigneTop){
		$listChamps = [];
		$listChamps[] = $uneLigneTop["top"];
		$listChamps[] = $num_avantages;
		
		$nomComplet = ucfirst($uneLigneTop["client"]['NOM']);
		if($uneLigneTop["client"]['PRENOM'] !=''){ 
			$nomComplet .= ', '.ucfirst($uneLigneTop["client"]['PRENOM']); 
		}
		if($uneLigneTop["client"]['NOM2']){ 
			$nomComplet .= ' | '.ucfirst($uneLigneTop["client"]['NOM2']);
			if($uneLigneTop["client"]['PRENOM2'] !=''){ 
				$nomComplet .= ', '.ucfirst($uneLigneTop["client"]['PRENOM2']);
			}
		};
		$listChamps[] = $nomComplet;
		$listChamps[] = nfs($uneLigneTop["total_by_dep"]["3"]);
		$listChamps[] = nfs($uneLigneTop["total_by_dep"]["2"]);
		$listChamps[] = nfs($uneLigneTop["total_by_dep"]["6"]);
		$listChamps[] = nfs($uneLigneTop["total_by_dep"]["9"]+$uneLigneTop["total_by_dep"]["10"]);
		$listChamps[] = nfs($uneLigneTop["total_bouffe"]);
		$listChamps[] = nfs($uneLigneTop["total_acc"]);
		$listChamps[] = nfs($uneLigneTop["total"]);
														
		$rapport->writeLigneRapport3wrap( $listChamps );
	}

	//$rapport->writeLigneGrandTotal( [ null,$data["depot"]["nb"],nfs($data["depot"]["montant"])], [false,true,true] );

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
				<span class="hidden-xs-down"><?= L("rap_topClient","o");?></span>
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
			<?php if(!empty($_GET["to"])){?>
			<h6>Au <?= formatDateutf8($_GET["to"])?></h6>
			<?php }?>
			</div>
		</div>
		<!-- End SubHeader-->
		<!-- BEGIN PAGE CONTENT-->
		<div class="row pl-3 pr-3 pb-3 mb-3 mt-3 print-top">
			<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
				<!--Start Panel-->
				<div class="panel c-white-dark pb-0">
					<div class="panel-body">
						<div class="panel bgc-white-dark transition visible">
							<div class="panel-body panel-body-p">
								<div class="page-size-table">
									<div class="bootstrap-table">
										<div class="fixed-table-toolbar no-print">
											<form method="get" id="formTopClient">
												<input type="hidden" name="p" value="<?= $_GET["p"]?>">
												<div class="row" style="margin-bottom:15px;">
													<div class="col-md-9">
														<div class="input-group bs-datepicker input-daterange picker-range">
															<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities($_GET["from"])? : '2014-01-01'?>">
															<span class="input-group-addon px-3"><?= L("to"); ?></span>
															<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($_GET["to"])? : date('Y-m-d')?>">
														</div>
													</div>
													<div class="col-md-3">
														<select name="limit" class="form-control">
															<?php 
															$listLimit = [500,400,300,200,100,50,25,10];
															foreach($listLimit as $uneLimite){
																$selected = ($_GET["limit"]==$uneLimite)?"selected":"";
																?><option value="<?= $uneLimite ?>" <?= $selected ?>>Top <?= $uneLimite ?></option><?php 
															}
															?>
														</select>
													</div>
												</div>
												
												<div class="columns columns-right btn-group pull-right no-print">
													<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('afficher');?></button>
												</div>
												<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?:"total" ?>">
												<input type="hidden" name="ordersens" value="<?= $_GET["ordersens"]?:"desc" ?>">
											</form>
										</div>

										<?php
										if ( $data ){
											?> 
											<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
												<div class="fixed-table-header" >
													<table>
														
													</table>
												</div>
												<div class="fixed-table-body">

													<table id="listTopClient" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
														<thead>
															<tr>
																<th>Top</th>
																<th class="text-left"  data-orderby="xxxx"><?= L("numéro de carte","o") ?> <i class="fa fa-sort"></i></th>
																<th class="text-left"  data-orderby="nom"><?= L("nom_client","o") ?> <i class="fa fa-sort"></i></th>
																<th class="text-right" data-orderby="chien"><?= L("chien","o") ?> <i class="fa fa-sort"></i></th>
																<th class="text-right" data-orderby="chat"><?= L("chat","o") ?> <i class="fa fa-sort"></i></th>
																<th class="text-right" data-orderby="rongeur"><?= L("rongeur","o") ?> <i class="fa fa-sort"></i></th>
																<th class="text-right" data-orderby="toilettage"><?= L("toilettage","o") ?> <i class="fa fa-sort"></i></th>
																<th class="text-right" data-orderby="bouffe"><?= L("nourriture","o") ?> <i class="fa fa-sort"></i></th>
																<th class="text-right" data-orderby="acc"><?= L("accessoires","o") ?> <i class="fa fa-sort"></i></th>
																<th class="text-right" data-orderby="total"><?= L("ventes globales","o") ?> <i class="fa fa-sort"></i></th>
															</tr>
														</thead>
														<tbody>
															<?php 
															foreach( $data as $num_avantages => $uneLigneTop ){
																?>
																<tr>
																	<td><?= $uneLigneTop["top"] ?></td>
																	<td><?= $num_avantages ?></td>
																	<td>
																		<?php if ($uneLigneTop["client"]){ ?>
																		<a href="index.php?p=client_detail&id=<?= $uneLigneTop["client"]["ID_CLIENT"] ?>">
																			<?php echo ucfirst($uneLigneTop["client"]['NOM']); if($uneLigneTop["client"]['PRENOM'] !=''){ echo ', '.ucfirst($uneLigneTop["client"]['PRENOM']); }?> <?php if($uneLigneTop["client"]['NOM2']){ echo ' | '.ucfirst($uneLigneTop["client"]['NOM2']); if($uneLigneTop["client"]['PRENOM2'] !=''){ echo ', '.ucfirst($uneLigneTop["client"]['PRENOM2']); }}?>
																		</a>
																		<?php } else { ?>
																		<i style="color:#ccc;">aucun client lié à la carte</i>
																		<?php } ?>
																	</td>
																	<td class="text-right"><?= nfs($uneLigneTop["total_by_dep"]["3"]) ?></td>
																	<td class="text-right"><?= nfs($uneLigneTop["total_by_dep"]["2"]) ?></td>
																	<td class="text-right"><?= nfs($uneLigneTop["total_by_dep"]["6"]) ?></td>
																	<td class="text-right"><?= nfs($uneLigneTop["total_by_dep"]["9"]+$uneLigneTop["total_by_dep"]["10"]) ?></td>
																	<td class="text-right"><?= nfs($uneLigneTop["total_bouffe"]) ?></td>
																	<td class="text-right"><?= nfs($uneLigneTop["total_acc"]) ?></td>
																	<td class="text-right"><?= nfs($uneLigneTop["total"]) ?></td>
																</tr>
																<?php 
															} ?>
														</tbody>
													</table>

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