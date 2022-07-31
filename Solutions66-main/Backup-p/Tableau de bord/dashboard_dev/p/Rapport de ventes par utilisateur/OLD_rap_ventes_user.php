<?php
$daterange = "";
$limitrange = "";
//SI les dates ont été remplies 
if($_REQUEST['from'] !='' && $_REQUEST['to'] !=''){
	$daterange = " WHERE (facture.date_insert >= '".$mysqli->real_escape_string($_REQUEST['from'])." 00:00:00' AND facture.date_insert <= '".$mysqli->real_escape_string($_REQUEST['to'])." 23:59:59.997') ";
}else{
	$limitrange = "";
}

$d_order = 'desc';
if(isset($_GET['d_order'])){ $d_order = $_GET['d_order']; }

$queryRaport = "SELECT date(facture.date_insert) `jour`, sum(soustotal) `totalsanstaxe`, sum(grandtotal) `totalavectaxe`, count(id_facture) `nbrefact`, utilisateur.*  FROM ".$rowThismagasin['caisse_db'].".facture JOIN utilisateur USING(id_utilisateur) ".$daterange." GROUP BY utilisateur.id_utilisateur ORDER BY sum(grandtotal) ".$d_order.' '.$limitrange;
$resulRaport = $dbAnimoCaisse->query($queryRaport) or die($dbAnimoCaisse->error);
//CRÉATION DE L'ARRAY POUR LE GRAPH
$dataArray =[];
while ($rowRaport = $resulRaport->fetch_assoc()) {
	$utilisateur = $rowRaport["prenom"];
	$utilisateurs[] = [$rowRaport["id_utilisateur"],$rowRaport["prenom"]];
	$dataArray[] = [$rowRaport["id_utilisateur"], $rowRaport['totalavectaxe']];
}
?>
<section id="main" class="main-wrap bgc-white-darkest print" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("Rapport de ventes par utilisateur","o");?></span>

		</h1>
		
		<h1 id="date_label" class="page-title pull-right fs-4 fw-light print-only"></h1>
		<div class="smart-links no-print">
			<ul class="nav" role="tablist">
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" onclick="window.print();" href="javascript:;">
						<i class="fa fa-print"></i>
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
											<div class="row" style="margin-bottom:15px;">
												<div class="col-md-8">
													<div class="input-group bs-datepicker input-daterange picker-range">
														<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities($_GET["from"])? : date('2014-01-01')?>">
														<span class="input-group-addon px-3"><?= L("to"); ?></span>
														<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($_GET["to"])? : date('Y-m-d')?>">
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
										<div class="fixed-table-body">
											<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
												<thead>
													<tr>
														<th>
															Utilisateur
														</th>
														<th style="text-align:center;">
															<?php echo $L['nbrefact'];?>
														</th>
														<th style="text-align:center;">
															Nombre d'escomptes accordées
														</th>
														<th style="text-align:center;">
															Total des escomptes
														</th>
														<th style="text-align:right;">
															<?php echo $L['totalsanstaxes'];?>
														</th>
														<th style="text-align:right;">
															<?php echo $L['totaltaxes'];?>
														</th>
													</tr>
												</thead>
												<tbody id="listToilettages">
													<?php $totfacture=0; $totaktxes=0; $totsanstxes=0;$totRabais=0;$nbRabais=0; $resulRaport->data_seek(0); while ($rowRaport = $resulRaport->fetch_assoc()) {
													if($_REQUEST['from'] !='' && $_REQUEST['to'] !=''){
														$queryRabais = sprintf("SELECT COUNT(*) as nbRabais, SUM(montant) as totalRabais, id_utilisateur FROM facture_item
														JOIN facture using(id_facture) JOIN utilisateur using(id_utilisateur)
														$daterange AND type = 'ESCOMPTE' AND id_utilisateur = %s GROUP BY id_utilisateur ORDER BY sum(grandtotal) desc", $rowRaport["id_utilisateur"]);
													}else{
														$queryRabais = sprintf("SELECT COUNT(*) as nbRabais, SUM(montant) as totalRabais, id_utilisateur FROM facture_item
																		JOIN facture using(id_facture) JOIN utilisateur using(id_utilisateur)
																		WHERE type = 'ESCOMPTE' AND id_utilisateur = %s GROUP BY id_utilisateur ORDER BY sum(grandtotal) DESC", $rowRaport["id_utilisateur"]);
														}
													$resultRabais = $dbAnimoCaisse->query($queryRabais);
													$resultRabais->data_seek(0);
													if($rowRabais = $resultRabais->fetch_assoc()){
														$nbes = $rowRabais["nbRabais"];
														$nbRabais += $nbes;
														$totalRabais += abs($rowRabais["totalRabais"]);
														$totales = formatPrix(abs($rowRabais["totalRabais"]));
													}else{
														$nbes = "N/A";
														$totales = "N/A";
													}
													?>
													<tr>
														<td>
															<a href="?p=rap_journalier&staff=<?= $rowRabais["id_utilisateur"]?>&<?php if($_GET['id']!=''){echo '&id='.$_GET['id'];}?>&date=<?php echo $rowRaport['jour'];?>"><?= $rowRaport["prenom"] . " " . $rowRaport["nom"]?></a>
														</td>
														<td style="text-align:center;">
															<?php $totfacture += $rowRaport['nbrefact']; echo $rowRaport['nbrefact'];?>
														</td>
														<td style="text-align:center;">
															<?= $nbes?>
														</td>
														<td style="text-align:center;">
															<?= $totales?>
														</td>
														<td style="text-align:right;">
															<?php $totsanstxes += $rowRaport['totalsanstaxe']; echo money_format('%n', $rowRaport['totalsanstaxe']);?>
														</td>
														<td style="text-align:right;">
															<?php $totaktxes += $rowRaport['totalavectaxe']; echo money_format('%n', $rowRaport['totalavectaxe']);?>
														</td>
													</tr>
												<?php } ?>
											</tbody>
											<tfoot>
												<tr style="font-weight:bold">
													<td></td>

													<td style="text-align:center;"><?php echo $totfacture; ?></td>
													<td style="text-align:center;"><?= $nbRabais?></td>
													<td style="text-align:center;"><?= formatPrix($totalRabais)?></td>
													<td style="text-align:right;"><?php echo  money_format('%n', $totsanstxes);?></td>
													<td style="text-align:right;"><?php echo  money_format('%n', $totaktxes);?></td>
												</tr>
											</tfoot>

											</table>

										</div>

										<div class="fixed-table-footer" style="display: none;"<table><tbody><tr></tr></tbody></table>
										</div>
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