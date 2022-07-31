<?php

ini_set("memory_limit","256M");
set_time_limit(300);

//$data = ["lignes"=>[],"nbrefact"=>0,"totalsanstaxe"=>0];
$data = ["nbrefact"=>0,"totalsanstaxe"=>0,"lignes"=>[]];
$magasin_id = $_SESSION["mag"];

$enonce = "SELECT sum(facture.soustotal) `totalsanstaxe`, sum(facture.grandtotal) `totalavectaxe`, count(facture.id_facture) `nbrefact`, facture.date_insert
			FROM facture
		    GROUP BY Year(facture.date_insert), Month(facture.date_insert)";
$resultFactItem = query($enonce,[],$dbAnimoCaisse);
while( $uneLigneFact = $resultFactItem->fetch_assoc() ){
    
	$uneLigneFact["annee"] = date("Y",strtotime($uneLigneFact["date_insert"]));
	$uneLigneFact["mois"] = date("m",strtotime($uneLigneFact["date_insert"]));
	$uneLigneFact["anneemois"] = date("Y-m",strtotime($uneLigneFact["date_insert"]));
	$data["lignes"][] = $uneLigneFact;

	$data["totalsanstaxe"] += $uneLigneFact["totalsanstaxe"];
	$data["nbrefact"] += $uneLigneFact["nbrefact"];
}


$listTriPosible = ["anneemois","nbrefact","totalsanstaxe"];
if ( !in_array($_GET["orderby"],$listTriPosible) ){
	$_GET["orderby"] = $listTriPosible[0];
}

if ( $_GET["sens"] == 'asc' ){
	$_GET["sens"] = "asc";
} else {
	$_GET["sens"] = "desc";
}

uasort( $data["lignes"], function($a,$b){
	if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
		return ($_GET["sens"]=="desc")?1:-1;
	} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
		return ($_GET["sens"]=="desc")?-1:1;
	}
	return 0;
});

/*
echo '<pre>';
echo htmlspecialchars(print_r($_REQUEST, true));
echo '$data : '.htmlspecialchars(print_r($data, true));
echo '</pre>';
*/

//vex($data);
//die();


if ( $_GET["getFile"] == "1" and $data ){
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}


	$titre = L("rap_mois","o");

	$listSoustitre = [];

	$listEnteteColonne = [
			[ ["text"=>L("mois","o"),"width"=>45,"align"=>"L"], ["text"=>L("nbrefact",'o'),"width"=>25,"align"=>"C"],["text"=>L("total",'o'),"width"=>25,"align"=>"R"],],
	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	//
	foreach ( $data["lignes"] as $uneLigneFactItem){
		$listChamps = [];
		$listChamps[0] = utf8_encode(strftime("%B %Y", strtotime(sprintf("%04d-%02d-02",$uneLigneFactItem["annee"],$uneLigneFactItem["mois"]))));
		$listChamps[1] = $uneLigneFactItem["nbrefact"];
		$listChamps[2] = nfs($uneLigneFactItem["totalsanstaxe"]);

		$rapport->writeLigneRapport3wrap( $listChamps );
	}

	$rapport->writeLigneGrandTotal( [ null,$data["nbrefact"],nfs($data["totalsanstaxe"])], [false,true,true] );

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
				<span class="hidden-xs-down"><?= L("rap_mois","o");?></span>
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

												<div class="columns columns-right btn-group pull-right no-print">
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
													<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
														<thead>
															<tr>
																<th><?= L("mois"); ?></th>
																<th style="text-align:center"><?= L("nbrefact","o"); ?></th>
																<th style="text-align:right"><?= L("total","o"); ?></th>
															</tr>
														</thead>
														<tbody>
															<?php
															foreach ($data["lignes"] as $rowRapport) {
																$link = "?p=rap_ventes&from=" . sprintf("%04d-%02d-01",$rowRapport["annee"],$rowRapport["mois"]) . "&to=" . 
																getDateLastDayMonth($rowRapport["annee"],$rowRapport["mois"]) . "&ID_MAGASIN[]=". $magasin_id;
																?>
																<tr>
																	<td><a href="<?= $link ?>"><b><?php echo utf8_encode(strftime("%B %Y", strtotime(sprintf("%04d-%02d-02",$rowRapport["annee"],$rowRapport["mois"])))); ?></b></a></td>
																	<td style="text-align:center"><?= $rowRapport["nbrefact"]?></td>
																	<td style="text-align:right"><a href="<?= $link ?>"><?= formatPrix($rowRapport["totalsanstaxe"])?></a></td>
																</tr>
																<?php
															} ?>
													</tbody>
													<tfoot>
														<tr style="font-weight:bold">
															<td></td>
															<td style="text-align:center;"><?= $data["nbrefact"];?></td>
															<td style="text-align:right;"><?= formatPrix($data["totalsanstaxe"]);?></td>
														</tr>
													</tfoot>
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