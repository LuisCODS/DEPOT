<?php
$daterange = "";
if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
	$daterange = " (facture.date_insert >= '{$_GET['from']} 00:00:00' AND facture.date_insert <= '{$_GET['to']} 23:59:59') ";
} else if ( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) ){
	$daterange = " (facture.date_insert >= '{$_GET['from']} 00:00:00' ";
} else if ( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
	$daterange = " facture.date_insert <= '{$_GET['to']} 23:59:59') ";
}

$data = [];
$_GET["id_departement"] = $_GET["id_departement"] ? : 9;

if ( $daterange ){
    
	$data = [
        	    "lignes"=>[],
        	    "totalsanstaxe"=>0,
        	    "nb"=>0,
        	    "nom"=>""
    	    ];

	$resulDepartements = query("SELECT * FROM departement 
	                           where id_departement = ? 
	                           order by nom asc",[$_GET["id_departement"],],$dbAnimoCaisse);
	$data["nom"] = $resulDepartements->fetch_assoc()["nom"];

	$resulFact = query("SELECT * FROM facture WHERE $daterange order by date_insert desc",[],$dbAnimoCaisse);
	
	while( $uneLigneFact = $resulFact->fetch_assoc() ){

		$resulFactItem = query("SELECT facture_item.*, article.id_departement `article_id_departement`
								FROM facture_item
									left join article using (id_article)
								WHERE facture_item.id_facture = ?
								and (article.id_departement = ? or facture_item.id_departement = ?)
							order by facture_item.ordre asc",[$uneLigneFact["id_facture"],$_GET["id_departement"],$_GET["id_departement"]],$dbAnimoCaisse);
		while( $uneLigneFactItem = $resulFactItem->fetch_assoc() ){
			$uneLigneFactItem["date_facture"] = $uneLigneFact["date_insert"];

			if ( preg_match('#^ESCOMPTE#',$uneLigneFactItem["type"]) or preg_match('#^SPECIAUX#',$uneLigneFactItem["type"]) ){

			} else {
				$data["lignes"][] = $uneLigneFactItem;
				$data["totalsanstaxe"] += $uneLigneFactItem["montant"];
				$data["nb"] += $uneLigneFactItem["nb"];
			}
		}
	}

    // ======================   GESTION TRI TABLEAU  =================================
    
	$listTriPosible = ["date_facture","id_facture","nb","montant"];
	
	if ( !in_array($_GET["orderby"],$listTriPosible) ){
		$_GET["orderby"] = $listTriPosible[0];
	}
	if ( $_GET["sens"] == 'asc' ){
		$_GET["sens"] = "asc";
	} else {
		$_GET["sens"] = "desc";
	}

	usort( $data["lignes"], function($a,$b){
		if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
			return ($_GET["sens"]=="desc")?1:-1;
		} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
			return ($_GET["sens"]=="desc")?-1:1;
		}
		return 0;
	});
}


/* ============== GESTION PDF/EXCEL  ================= */

if ( $_GET["getFile"] == "1" and $data ){
    
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}

	$titre = L("rapport de ventes par département détailé","o");

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

	$listSoustitre[] = [L("département"), $data["nom"]];

	$listEnteteColonne = [
			[ ["text"=>"NB","width"=>15,"align"=>"C"], ["text"=>L("nom du produit",'o'),"width"=>60,"align"=>"L"],["text"=>L("# Facture",'o'),"width"=>30,"align"=>"C"],
			["text"=>L("Date",'o'),"width"=>25,"align"=>"L"],["text"=>L("montant",'o'),"width"=>25,"align"=>"R"],],
	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	//
	foreach ( $data["lignes"] as $uneLigneDep){
		$listChamps = [];
		$listChamps[0] = $uneLigneDep["nb"];
		$listChamps[1] = $uneLigneDep["label"];
		$listChamps[2] = $uneLigneDep["id_facture"];
		$listChamps[3] = formatDateUTF8nonHTML($uneLigneDep["date_facture"]);
		$listChamps[4] = nfs($uneLigneDep["montant"]);

		$rapport->writeLigneRapport3wrap( $listChamps );
	}

	$rapport->writeLigneGrandTotal( [ $data["nb"],null,null,null , nfs($data["totalsanstaxe"])], [true,false,false,false,true] );

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
				<span class="hidden-xs-down"><?= L("rapport de ventes par département détailé","o");?></span>
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
												<input type="hidden" name="sens" value="<?= $_GET["sens"] ?>">
												<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">
												<div class="row" style="margin-bottom:15px;">
													<div class="col-md-4">
														<select name="id_departement" class="ui fluid simple-select select-dropdown"> <?php /* onchange="$(this).closest('form').submit();" */ ?>
															<?php
															$resulDepartements = query("SELECT * FROM departement order by nom asc",[],$dbAnimoCaisse);
															while($rowDepartements = $resulDepartements->fetch_assoc()){
																?>
																<option value="<?php echo $rowDepartements['id_departement']?>" <?php if($_GET['id_departement'] == $rowDepartements['id_departement']){ echo 'selected'; }?>><?php echo $rowDepartements['nom']?></option>
																<?php
															}?>
														</select>
													</div>
													<div class="col-md-8">
														<div class="input-group bs-datepicker input-daterange picker-range">
															<input type="text" class="form-control" name="from" id="from" value="<?= empty(htmlentities($_GET["from"])) ? date("Y-m-d", strtotime(date("Y-m-d"). ' - 30 days')):htmlentities($_GET["from"])?>">
															<span class="input-group-addon px-3"><?= L("to"); ?></span>
															<input type="text" class="form-control" name="to" id="to" value="<?= empty(htmlentities($_GET["to"])) ? date("Y-m-d") : htmlentities($_GET["to"])?>">
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
											<?php if ( $data  ){ ?>
											<div class="fixed-table-body">
												<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
													<thead>
														<tr>
															<th style="text-align:center">
       															<a href="index.php?<?= rebuildQueryString(['orderby'=>'nb','sens'=>($_GET["orderby"] == 'nb' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															    NB  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nb' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																</a>
															</th>															
															<th style="text-align:left">
       															<a href="index.php?<?= rebuildQueryString(['orderby'=>'label','sens'=>($_GET["orderby"] == 'label' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															    <?php echo $L['articlenom'];?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'label' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																</a>
															</th>
															<th style="text-align:center">
       															<a href="index.php?<?= rebuildQueryString(['orderby'=>'id_facture','sens'=>($_GET["orderby"] == 'id_facture' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															    <?php echo $L['fact#'];?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'id_facture' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																</a>
															</th>
															<th>
       															<a href="index.php?<?= rebuildQueryString(['orderby'=>'date_facture','sens'=>($_GET["orderby"] == 'date_facture' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															    Date  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'date_facture' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																</a>
															</th>
															<th style="text-align:right">
       															<a href="index.php?<?= rebuildQueryString(['orderby'=>'montant','sens'=>($_GET["orderby"] == 'montant' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															    <?php echo $L['montant'];?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'montant' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
																</a>
															</th>
														</tr>
													</thead>
													<tbody>
														<?php
														foreach ($data["lignes"] as $rowRaport) {
															?>
															<tr>
																<td style="text-align:center">
																	<?= $rowRaport["nb"] ?>
																</td>
																<td>
																	<?= $rowRaport['label']; ?>
																</td>
																<td style="text-align:center;">
																	<a class="ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?php echo $rowRaport['id_facture'];?>&ID_MAGASIN=<?= $_SESSION["mag"]?>"><?= $rowRaport["id_facture"]?></a>
																</td>
																<td >
																	<?= formatDateUTF8($rowRaport["date_facture"]) ?>
																</td>
																<td style="text-align:right;">
																	<?= formatPrix($rowRaport['montant']); ?>
																</td>
															</tr>
															<?php
														} ?>
													</tbody>
													<tfoot>
														<tr style="font-weight:bold">
															<td style="text-align:center;"><?php echo $data["nb"]; ?></td>
															<td style="text-align:center;"> </td>
															<td style="text-align:center;"> </td>
															<td style="text-align:center;"> </td>
															<td style="text-align:right;"><?= formatPrix($data["totalsanstaxe"]);?></td>
														</tr>
													</tfoot>
												</table>
											</div>
											<?php } ?>
										</div>
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