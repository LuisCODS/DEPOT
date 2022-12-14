<?php

ini_set("memory_limit","256M");
set_time_limit(300);

$data = ["lignes"=>[],];

if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET["to"]) ){
	$dateTo = $_GET["to"] . ' 00:00:00';
} else {
	$_GET["to"] = date("Y-m-d",strtotime("-30 days"));
	$dateTo = $_GET["to"] . ' 00:00:00';
}

$result = query(" select max(TA.`DATE`) `LAST_DATE_TOIL`, ANIMAL.ID_CLIENT, GROUP_CONCAT(DISTINCT ANIMAL.ID_ANIMAL) `list_ID_ANIMAL`,
								`CLIENT`.`NOM`, `CLIENT`.`PRENOM`, `CLIENT`.`NOM2`, `CLIENT`.`PRENOM2`, `CLIENT`.`TEL_MAISON`,
								`CLIENT`.`CELL`, `CLIENT`.`TEL_2`, `CLIENT`.`CELL_2`, `CLIENT`.`EMAIL`, `CLIENT`.`EMAIL2`
						from TOILETTAGE `TA`
							left join ANIMAL using(ID_ANIMAL)
							left join `CLIENT` on (`CLIENT`.`ID_CLIENT` = `ANIMAL`.`ID_CLIENT`)

					where `TA`.`ID_ANIMAL` not in (
								select TB.ID_ANIMAL
								from TOILETTAGE `TB`
								where `TB`.`NO_SHOW` is null and `TB`.`DATE` >= '$dateTo' group by TB.ID_ANIMAL
							)
						and `TA`.`ID_ANIMAL` not in (
								select ARV.ID_ANIMAL
								from ANIMAL_RENDEZVOUS `ARV`
								where `ARV`.`ID_TOIL` is null and `ARV`.`DATE_DEBUT` >= '$dateTo' group by ARV.ID_ANIMAL
							)
						and `TA`.ID_MAG = ? and `TA`.NO_SHOW is null
						and ANIMAL.MORT is null
				group by ANIMAL.ID_CLIENT",[$_SESSION["mag"],],$mysqli);
				
while ($uneLigne = $result->fetch_assoc() ){
    
	$uneLigne["animaux"] = [];

	if ( $uneLigne["list_ID_ANIMAL"] != "" ){
		$listID_ANIMAL = explode(",",$uneLigne["list_ID_ANIMAL"]);
		$resultAnimal = query("select * from ANIMAL where ID_ANIMAL in (?) order by A_NOM asc",[$listID_ANIMAL,],$mysqli);
		while ($uneLigneAnimal = $resultAnimal->fetch_assoc() ){
			if ( trim($uneLigneAnimal["A_NOM"]) == "" ) $uneLigneAnimal["A_NOM"] = L("[inconnu]");
			$uneLigne["animaux"][] = $uneLigneAnimal;
		}
	}

	$data["lignes"][] = $uneLigne;
}


$listTriPosible = ["LAST_DATE_TOIL",];



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

// ============================ GESTION FICHIER =============================================
if ( $_GET["getFile"] == "1" and $data ){
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}

	$titre = L("rap_longtimenosee","o");

	$listSoustitre = [];
	if( $_GET['to'] ) {
		$listSoustitre[] = ["date", L("jusqu'au") . " " . formatDateUTF8nonHTML( $_GET['to'] ) ];
	}

	$listEnteteColonne = [
		[
			["text"=>L("nom client",'o'),"width"=>45,"align"=>"L"],["text"=>L("t??l??phone",'o'),"width"=>35,"align"=>"C"],["text"=>"courriel","width"=>45,"align"=>"C"],
			["text"=>L("animal",'o'),"width"=>40,"align"=>"L"],["text"=>L("dernier toilettage",'o'),"width"=>29,"align"=>"L"],
		],
	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	//label nb nb_facture montant_moyenne montant
	foreach ( $data["lignes"] as $rowRapport){
		$listChamps = [];

		$txt = "";
		if ( $rowRapport["NOM"] != "" or $rowRapport["PRENOM"] != ""){

			$txt .= $rowRapport["PRENOM"] . ($rowRapport["PRENOM"]!=""?" ":"") . $rowRapport["NOM"];
		}
		if ( $rowRapport["NOM2"] != "" or $rowRapport["PRENOM2"] != ""){
			if ( $txt!= "" ) $txt.= "\n";
			$txt .= $rowRapport["PRENOM2"] . ($rowRapport["PRENOM2"]!=""?" ":"") . $rowRapport["NOM2"];
		}
		$listChamps[] = $txt;


		$txt = "";
		if ( $rowRapport["TEL_MAISON"] != ""){
			$txt.= ' T??l:' . $rowRapport["TEL_MAISON"];
		}
		if ( $rowRapport["TEL_2"] != ""){
			if ( $txt!= "" ) $txt .= "\n";
			$txt.= ' T??l:' . $rowRapport["TEL_2"];
		}
		if ( $rowRapport["CELL"] != ""){
			if ( $txt!= "" ) $txt .= "\n";
			$txt.= ' Cell:' . $rowRapport["CELL"];
		}
		if ( $rowRapport["CELL_2"] != ""){
			if ( $txt!= "" ) $txt .= "\n";
			$txt.= ' Cell:' . $rowRapport["CELL_2"];
		}
		$listChamps[] = $txt;



		$txt = "";
		if ( $rowRapport["EMAIL"] != ""){
			$txt.= $rowRapport["EMAIL"];
		}
		if ( $rowRapport["EMAIL2"] != ""){
			if ( $txt!= "" ) $txt.= "\n";
			$txt.= $rowRapport["EMAIL2"];
		}
		$listChamps[] = $txt;


		$txt = "";
		foreach ( $rowRapport["animaux"] as $uneLigneAnimal ){
			if ( $txt!= "" ) $txt .= ", ";
			$txt .= $uneLigneAnimal["A_NOM"];
		}
		$listChamps[] = $txt;


		$listChamps[] = formatDateUTF8nonHTML($rowRapport["LAST_DATE_TOIL"]);

		$rapport->writeLigneRapport3wrap( $listChamps );
	}

	$rapport->writeLigneGrandTotal( [ null,$data["nb"],$data["nb_facture"],null,nfs($data["montant"])], [false,true,true,false,true] );





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
				<span class="hidden-xs-down"><?= L("rap_longtimenosee","o");?></span>
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
			<h6><?= formatDateutf8($_GET["to"])?></h6>
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
													<div class="col-md-2">
														<div class="input-group date bs-datepicker picker-format">
															<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($_GET["to"])?>">
															<div class="input-group-addon" style="">
																<span class="fa fa-th"></span>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<button type="button" class="btn btn-small" onclick="setDateTo(this,'<?= date("Y-m-d",strtotime("-30 days")) ?>')">30 jours</button>
														<button type="button" class="btn btn-small" onclick="setDateTo(this,'<?= date("Y-m-d",strtotime("-60 days")) ?>')">60 jours</button>
														<button type="button" class="btn btn-small" onclick="setDateTo(this,'<?= date("Y-m-d",strtotime("-90 days")) ?>')">90 jours</button>
														<script>
														function setDateTo(srcOb,strDate){
															var f = $(srcOb).closest("form");
															f.find("input[name=to]").val(strDate);
															f.submit();
														}
														</script>
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
													<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
														<thead>
															<tr>
																<th>Nom client</th>
																<th>T??l??phone</th>
																<th style="text-align:center;">&nbsp;Courriel&nbsp;</th>
																<th>Animal</th>
																<th>Dernier toilettage enregistr??</th>
															</tr>
														</thead>
														<tbody>
															<?php
															foreach ($data["lignes"] as $rowRapport) {
																?>
																<tr>
																	<td>
																		<a href="?p=client_detail&id=<?= $rowRapport['ID_CLIENT'] ?>">
    																		<?php
    																		$txt = "";
    																		if ( $rowRapport["NOM"] != "" or $rowRapport["PRENOM"] != ""){
    																			$txt .= $rowRapport["PRENOM"] . " " . $rowRapport["NOM"];
    																		}
    																		if ( $rowRapport["NOM2"] != "" or $rowRapport["PRENOM2"] != ""){
    																			if ( $txt!= "" ) $txt.= " / ";
    																			$txt .= $rowRapport["PRENOM2"] . " " . $rowRapport["NOM2"];
    																		}
    																		echo $txt;
    																		?>
																		</a>
																	</td>
																	<td>
																		<?php
																		$txt = "";
																		if ( $rowRapport["TEL_MAISON"] != ""){
																			$txt.= ' <i class="fa f fa-phone "></i>&nbsp;' . $rowRapport["TEL_MAISON"];
																		}
																		if ( $rowRapport["TEL_2"] != ""){
																			if ( $txt!= "" ) $txt.= " &nbsp;";
																			$txt.= ' <i class="fa f fa-phone "></i>&nbsp;' . $rowRapport["TEL_2"];
																		}
																		if ( $rowRapport["CELL"] != ""){
																			if ( $txt!= "" ) $txt.= " &nbsp;";
																			$txt.= ' <i class="fa f fa-mobile "></i>&nbsp;' . $rowRapport["CELL"];
																		}
																		if ( $rowRapport["CELL_2"] != ""){
																			if ( $txt!= "" ) $txt.= " &nbsp;";
																			$txt.= ' <i class="fa f fa-mobile "></i>&nbsp;' . $rowRapport["CELL_2"];
																		}
																		echo $txt;
																		?>
																	</td>
																	<td style="text-align:center;">
																		<?php
																		$txt = "";
																		if ( $rowRapport["EMAIL"] != ""){
																			$txt.= sprintf('<a href="mailto:%s" title="%s"><i class="fa fa-envelope"></i></a>',attrEncode($rowRapport["EMAIL"]),attrEncode($rowRapport["EMAIL"]));
																		}
																		if ( $rowRapport["EMAIL2"] != ""){
																			if ( $txt!= "" ) $txt.= " ";
																			$txt.= sprintf('<a href="mailto:%s" title="%s"><i class="fa fa-envelope"></i></a>',attrEncode($rowRapport["EMAIL2"]),attrEncode($rowRapport["EMAIL2"]));
																		}
																		echo $txt;
																		?>
																	</td>
																	<td >
																		<?php
																		$txt = "";
																		foreach ( $rowRapport["animaux"] as $uneLigneAnimal ){
																			if ( $txt!= "" ) $txt .= ", ";
																			$txt .= sprintf('<a href="index.php?p=client_detail&id=%d&id_animal=%d" >%s</a>',$rowRapport["ID_CLIENT"],$uneLigneAnimal["ID_ANIMAL"],$uneLigneAnimal["A_NOM"]);
																		}
																		echo $txt;
																		?>
																	</td>
																	<td>
																		<?= formatDateUTF8($rowRapport["LAST_DATE_TOIL"]) ?>
																	</td>
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
