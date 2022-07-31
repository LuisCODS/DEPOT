<?php


$daterange = " 1=1 ";
if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
	$daterange = " (facture.date_insert >= '{$_GET['from']} 00:00:00' AND facture.date_insert <= '{$_GET['to']} 23:59:59') ";
} else {
	$a = floatval( date("Y") );
	$m = floatval( date("m") );
	$m -= 1;
	if ( $m < 1 ) $m = 12;

	$_GET['from'] = sprintf( "%04d-%02d-01", $a,$m );
	$_GET['to'] = getDateLastDayMonth( $a, $m );
	$daterange = " (facture.date_insert >= '{$_GET['from']} 00:00:00' AND facture.date_insert <= '{$_GET['to']} 23:59:59') ";
}

$data = ["lignes"=>[],
        "nbfacture"=>0,
        "nbitem"=>0,
        "totalsanstaxe"=>0,
        "total"=>0,
        "nbes"=>0,
        "totales"=>0];

$cache_getPourcentTaxeFromDate = [];
function getPourcentTaxeFromDate($date)
{
	global $cache_getPourcentTaxeFromDate,$dbAnimoCaisse;

	if ( preg_match('#^(\d{4,5})\-(\d{2})\-(\d{2})$#',$date,$matches) or preg_match('#^(\d{4,5})\-(\d{2})\-(\d{2}) \d{2}:\d{2}:\d{2}$#',$date,$matches) )
	{
		$aa = $matches[1];
		$mm = $matches[2];
		if ( isset($cache_getPourcentTaxeFromDate[$aa."-".$mm]) ){
			return $cache_getPourcentTaxeFromDate[$aa."-".$mm];
		}

		$result = query("select * from config where date_debut <= '$aa-$mm-01' or id_config = 1 order by date_debut desc, date_update desc",[],$dbAnimoCaisse);
		if ( $uneLigneConfig = $result->fetch_assoc() ){
			$cache_getPourcentTaxeFromDate[$aa."-".$mm] = ["taxe1"=>$uneLigneConfig["taxe1"],
                                                			"taxe2"=>$uneLigneConfig["taxe2"],
                                                			"taxe3"=>$uneLigneConfig["taxe3"],
                                                			"taxe4"=>$uneLigneConfig["taxe4"]];
			return $cache_getPourcentTaxeFromDate[$aa."-".$mm];
		}
	}

	throw new Exception("Non-valid date : ".$date);
}


$resulFact = query("SELECT * FROM facture WHERE $daterange",[],$dbAnimoCaisse);

while( $uneLigneFact = $resulFact->fetch_assoc() )
{
    
	$factIncountDep = [];

	$resulFactItem = query("SELECT facture_item.*, article.id_departement `article_id_departement`
							FROM facture_item
							left join article using (id_article)
							WHERE facture_item.id_facture = ?
							order by facture_item.ordre asc",[$uneLigneFact["id_facture"],],$dbAnimoCaisse);
							
	while( $uneLigneFactItem = $resulFactItem->fetch_assoc() ){    //vex($uneLigneFactItem); die();
	    
		if ( $uneLigneFactItem["article_id_departement"] != "" ){
			$id_departement = $uneLigneFactItem["article_id_departement"];
		} else if ( $uneLigneFactItem["id_departement"] != "" ){
			$id_departement = $uneLigneFactItem["id_departement"];
		} else {
			continue;
		}
		if ( $id_departement >= 990 ){
			continue;
		}

		$factIncountDep[$id_departement] = true;

		if ( !isset($data["lignes"][$id_departement]) ){
			$uneLigneDep = query("select * from departement where id_departement = ?",[$id_departement,],$dbAnimoCaisse)->fetch_assoc();

			$data["lignes"][$id_departement] = ["nbfacture"=>0,
                                    			"nbitem"=>0,
                                    			"totalsanstaxe"=>0,
                                    			"total"=>0,
                                    			"nbes"=>0,
                                    			"totales"=>0,
                                    			"nom"=>(($uneLigneDep["nom"])?:"Département #".$id_departement)];
		}

		//Si escompte
		if ( preg_match('#^ESCOMPTE#',$uneLigneFactItem["type"]) or preg_match('#^SPECIAUX#',$uneLigneFactItem["type"]) )
		{
            $data["lignes"][$id_departement]["nbes"] += $uneLigneFactItem["nb"];
			$sumPourcantTaxes = 0;
			for ( $i=1; $i <= 4; $i++ ){
				$listTaxesConfig = getPourcentTaxeFromDate($uneLigneFact["date_insert"]);
				if ( $uneLigneFactItem["ataxe".$i] == "1" ){
					$sumPourcantTaxes += $listTaxesConfig["taxe".$i];
				}
			}
			//$data["lignes"][$id_departement]["totales"] += round($uneLigneFactItem["montant"] + round($uneLigneFactItem["montant"] * $sumPourcantTaxes / 100, 2), 2);
			$data["lignes"][$id_departement]["totales"] += $uneLigneFactItem["montant"];
			$data["lignes"][$id_departement]["total"]   +=  $uneLigneFactItem["montant"];//added
		} 
		else 
		{
			$data["lignes"][$id_departement]["nbitem"]        += $uneLigneFactItem["nb"];
			$data["lignes"][$id_departement]["totalsanstaxe"] += $uneLigneFactItem["montant"];
			$data["lignes"][$id_departement]["total"]         +=  $uneLigneFactItem["montant"];//added
			
			$sumPourcantTaxes = 0;
			for ( $i=1; $i <= 4; $i++ ){
				$listTaxesConfig = getPourcentTaxeFromDate($uneLigneFact["date_insert"]);
				if ( $uneLigneFactItem["ataxe".$i] == "1" ){
					$sumPourcantTaxes += $listTaxesConfig["taxe".$i];
				}
			}
			//$data["lignes"][$id_departement]["totalavectaxe"] += round($uneLigneFactItem["montant"] + round($uneLigneFactItem["montant"] * $sumPourcantTaxes / 100, 2), 2);
		}

	}// fin while

	//nbfacture
	if ( sizeof($factIncountDep) > 0 ){
		foreach( $factIncountDep as $id_departement=>$isCounted){
			$data["lignes"][$id_departement]["nbfacture"]++;
		}
		$data["nbfacturereel"]++;
	}
}// fin while

foreach ( $data["lignes"] as $id_departement => $infoDep )                     
{
	$data["nbfacture"]     += $infoDep["nbfacture"];
	$data["nbes"]          += $infoDep["nbes"];
	$data["totales"]       += round($infoDep["totales"],2);
	$data["nbitem"]        += $infoDep["nbitem"];
	$data["totalsanstaxe"] += round($infoDep["totalsanstaxe"],2);
    $data["total"]         +=  round( ($infoDep['totalsanstaxe'] - $infoDep["totales"]),2 );//added

}

// ======================   GESTION TRIAGE TABLEAU  =================================

$listTriPosible = ["nom","total","totalsanstaxe","nbitem","totales","nbes","nbfacture"];
if ( !in_array($_GET["orderby"],$listTriPosible) ){
	$_GET["orderby"] = $listTriPosible[0];
}

if ( $_GET["sens"] == 'desc' ){
	$_GET["sens"] = "desc";
} else {
	$_GET["sens"] = "asc";
}

uasort( $data["lignes"], function($a,$b)
{
	if ( ($a[$_GET["orderby"]]) < ($b[$_GET["orderby"]]) ){
		return ($_GET["sens"]=="desc")?1:-1;
	} elseif( ($a[$_GET["orderby"]]) > ($b[$_GET["orderby"]]) ){
		return ($_GET["sens"]=="desc")?-1:1;
	}
	return 0;
});

//echo '<pre>' , print_r($data) , '</pre>';


/* ============== GESTION PDF/EXCEL  ================= */
if ( $_GET["getFile"] == "1" ){
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}


	$titre = L("rapport de ventes par départements","o");

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
			[ ["text"=>"Département","width"=>45,"align"=>"L"], ["text"=>L("nb. article",'o'),"width"=>30,"align"=>"C"],["text"=>L("Nb. escomptes",'o'),"width"=>30,"align"=>"C"],
			["text"=>L("Total avant escompte",'o'),"width"=>30,"align"=>"R"],["text"=>L("Total des escomptes",'o'),"width"=>30,"align"=>"R"],["text"=>L("total (sans tx.)",'o'),"width"=>30,"align"=>"R"],],
	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	//
	foreach ($data["lignes"] as $id_departement => $rowRaport) {
		$listChamps = [];
		$listChamps[0] = $rowRaport["nom"];
		$listChamps[1] = $rowRaport["nbitem"];
		$listChamps[2] = $rowRaport["nbes"];
		$listChamps[3] = nfs($rowRaport['totalsanstaxe']);
		$listChamps[4] = ($rowRaport["totales"])?nfs($rowRaport["totales"]):"N/A";
		$listChamps[5] = nfs($rowRaport['totalsanstaxe'] + $rowRaport["totales"]);

		$rapport->writeLigneRapport3wrap( $listChamps );
	}

	$rapport->writeLigneGrandTotal( [ null, $data["nbitem"], $data["nbes"], nfs($data["totalsanstaxe"]), nfs($data["totales"]),  nfs($data["totalsanstaxe"]+$data["totales"])], [false,true,true,true,true,true] );

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
				<span class="hidden-xs-down"><?= L("rapport de ventes par départements","o");?></span>
			</h1>
			<h1 id="date_label" class="page-title pull-right fs-4 fw-light print-only"></h1>
			<div class="smart-links no-print">
				<ul class="nav" role="tablist">
					<?php /* ?><li class="nav-item">
						<a class="nav-link clear-style aside-trigger" onclick="window.print();" href="javascript:;">
							<i class="fa fa-print"></i>
						</a>
					</li> */?>
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
												<input type="hidden" name="sens" value="<?= $_GET["sens"] ?>">
												<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">
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
										<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
											<div class="fixed-table-body">
												<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
													<thead>
														<tr>
															<th>
       															<a href="index.php?<?= rebuildQueryString(['orderby'=>'nom','sens'=>($_GET["orderby"] == 'nom' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															    Département <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nom' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																</a>  
															</th>															
															<th style="text-align:center;">
       															<a href="index.php?<?= rebuildQueryString(['orderby'=>'nbitem','sens'=>($_GET["orderby"] == 'nbitem' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															    <?php echo L("nb. article","o");?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nbitem' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																</a>  
															</th>
															<th style="text-align:center;">
       															<a href="index.php?<?= rebuildQueryString(['orderby'=>'nbes','sens'=>($_GET["orderby"] == 'nbes' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															    Nb. escomptes <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nbes' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																</a>  
															</th>
															<th style="text-align:right;">
       															<a href="index.php?<?= rebuildQueryString(['orderby'=>'totalsanstaxe','sens'=>($_GET["orderby"] == 'totalsanstaxe' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															    Total avant escompte <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalsanstaxe' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																</a>  
															</th>
															<th style="text-align:right;">
       															<a href="index.php?<?= rebuildQueryString(['orderby'=>'totales','sens'=>($_GET["orderby"] == 'totales' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															    Total des escomptes <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totales' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																</a>  
															</th>															
															<th style="text-align:right;">
       															<a href="index.php?<?= rebuildQueryString(['orderby'=>'total','sens'=>($_GET["orderby"] == 'total' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															    Total (sans tx.) <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'total' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																</a>  
															</th>	
														</tr>
													</thead>
													<tbody>
														<?php
														foreach ($data["lignes"] as $id_departement => $rowRaport) {
															?>
															<tr>
																<td><?= $rowRaport["nom"] ?></td>
																<td style="text-align:center;"><?= $rowRaport['nbitem']; ?></td>
																<td style="text-align:center;"><?= ($rowRaport["nbes"])?$rowRaport["nbes"]:"N/A" ?>	</td>
																<td style="text-align:right;"><a href="index.php?<?= rebuildQueryString(["p"=>"rap_ventes_dep_details","id_departement"=>$id_departement],["orderby",]) ?>"><?= formatPrix($rowRaport['totalsanstaxe']); ?></a></td>
																<td style="text-align:right;"><?= ($rowRaport["totales"])?formatPrix($rowRaport["totales"]):"N/A"?></td>
																<td style="text-align:right;"><?= formatPrix($rowRaport['total']); ?></td>
															</tr>
															<?php
														} ?>
												    </tbody>
    											    <tfoot>
    													<tr style="font-weight:bold">
    														<td></td>
    														<td style="text-align:center;"><?php echo $data["nbitem"] ?></td>
    														<td style="text-align:center;"><?= $data["nbes"] ?></td>
    														<td style="text-align:right;"><?= formatPrix($data["totalsanstaxe"]);?></td>
    														<td style="text-align:right;"><?= formatPrix($data["totales"])?></td>
    														<td style="text-align:right;"><?= formatPrix($data["totales"]+$data["totalsanstaxe"]);?></td>
    													</tr>
    												</tfoot>
												</table>
											</div>
										</div>
										<!-- END PAGE CONTENT-->
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