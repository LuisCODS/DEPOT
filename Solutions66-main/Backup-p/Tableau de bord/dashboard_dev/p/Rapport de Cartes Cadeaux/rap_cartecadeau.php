
<?php 

/*
$DEBUG_DB = 1;

$enonce = "select CARTE_OPERATION.*, CARTECADEAU.numCarte, group_concat(utilisateur_magasin.ID_MAGASIN) `listMag`
			 from CARTE_OPERATION
			 left join CARTECADEAU on (CARTECADEAU.ID_CARTECADEAU = CARTE_OPERATION.ID_CARTECADEAU)
			 left join utilisateur_magasin on (utilisateur_magasin.id_utilisateur = CARTE_OPERATION.ID_STAFF)

			where CARTE_OPERATION.ID_MAGASIN is null
		group by ID_CARTE_OPERATION";
$resultFix = query($enonce,[],$mysqli);
while( $uneLigneFix = $resultFix->fetch_assoc() ){
	vex($uneLigneFix);
	$listMag = explode(',',$uneLigneFix["listMag"]);
	$ID_MAGASIN = null;
	
	if ( sizeof($listMag) > 1 ){
		$numCarte = strtoupper($uneLigneFix["numCarte"]);
		$listMagFinded = [];
		
		$resultFixMag = query("select * from MAGASIN where ID_MAGASIN in (?)",[$listMag,],$mysqli);
		while( $uneLigneFixMag = $resultFixMag->fetch_assoc() ){
			
			$resultFixFactItem = query("SELECT facture_item.* FROM {$uneLigneFixMag['caisse_db']}.facture_item WHERE(facture_item.label = 'Carte Cadeau [$numCarte]')",[],$dbAnimoCaisse);
			if ( $resultFixFactItem->num_rows > 0 ){
				$listMagFinded[] = [$uneLigneFixMag['ID_MAGASIN'],"item"];
				echo("FINDED:".$uneLigneFixMag['caisse_db']."<br />");
				continue;
			}
			
			$resultFixFactItem = query("SELECT facture_paiement.* FROM {$uneLigneFixMag['caisse_db']}.facture_paiement WHERE(facture_paiement.compagnie = '$numCarte')",[],$dbAnimoCaisse);
			if ( $resultFixFactItem->num_rows > 0 ){
				$listMagFinded[] = [$uneLigneFixMag['ID_MAGASIN'],"paiement"];
				echo("FINDED:".$uneLigneFixMag['caisse_db']."<br />");
				continue;
			}
		}
		
		if ( sizeof($listMagFinded) == 1 ){
			$ID_MAGASIN = $listMagFinded[0][0];
		} else {
			vex($listMagFinded);
			vex("ERROR!"); die();
		}
		
		
	} elseif ( sizeof($listMag) == 1 ){
		$ID_MAGASIN = $listMag[0];
	}  else {
		die("error1");
	}
	
	if ( $ID_MAGASIN ){
		faireUpdate_i(["ID_CARTE_OPERATION"=>$uneLigneFix["ID_CARTE_OPERATION"],"ID_MAGASIN"=>$ID_MAGASIN],"CARTE_OPERATION","ID_CARTE_OPERATION",$mysqli,$DEBUG_DB);
	}
	
}

$enonce = "select CARTECADEAU.*,  group_concat(utilisateur_magasin.ID_MAGASIN) `listMag`
					 from CARTECADEAU
			 left join utilisateur_magasin on (utilisateur_magasin.id_utilisateur = CARTECADEAU.ID_STAFF)

			where CARTECADEAU.ID_MAGASIN is null
		group by ID_CARTECADEAU";
$resultFix = query($enonce,[],$mysqli);
while( $uneLigneFix = $resultFix->fetch_assoc() ){
	vex($uneLigneFix);
	$listMag = explode(',',$uneLigneFix["listMag"]);
	$ID_MAGASIN = null;
	
	if ( sizeof($listMag) > 1 ){
		$numCarte = strtoupper($uneLigneFix["numCarte"]);
		$listMagFinded = [];
		
		$resultFixMag = query("select * from MAGASIN where ID_MAGASIN in (?)",[$listMag,],$mysqli);
		while( $uneLigneFixMag = $resultFixMag->fetch_assoc() ){
			
			$resultFixFactItem = query("SELECT facture_item.* FROM {$uneLigneFixMag['caisse_db']}.facture_item WHERE(facture_item.label = 'Carte Cadeau [$numCarte]')",[],$dbAnimoCaisse);
			if ( $resultFixFactItem->num_rows > 0 ){
				$listMagFinded[] = [$uneLigneFixMag['ID_MAGASIN'],"item"];
				echo("FINDED:".$uneLigneFixMag['caisse_db']."<br />");
				continue;
			}
			
			$resultFixFactItem = query("SELECT facture_paiement.* FROM {$uneLigneFixMag['caisse_db']}.facture_paiement WHERE(facture_paiement.compagnie = '$numCarte')",[],$dbAnimoCaisse);
			if ( $resultFixFactItem->num_rows > 0 ){
				$listMagFinded[] = [$uneLigneFixMag['ID_MAGASIN'],"paiement"];
				echo("FINDED:".$uneLigneFixMag['caisse_db']."<br />");
				continue;
			}
		}
		
		if ( sizeof($listMagFinded) == 1 ){
			$ID_MAGASIN = $listMagFinded[0][0];
		} else {
			vex($listMagFinded);
			vex("ERROR!"); die();
		}
		
		
	} elseif ( sizeof($listMag) == 1 ){
		$ID_MAGASIN = $listMag[0];
	}  else {
		die("error1");
	}
	
	if ( $ID_MAGASIN ){
		faireUpdate_i(["ID_CARTECADEAU"=>$uneLigneFix["ID_CARTECADEAU"],"ID_MAGASIN"=>$ID_MAGASIN],"CARTECADEAU","ID_CARTECADEAU",$mysqli,$DEBUG_DB);
	}
}


die("-=-");
*/
?>

<section id="main" class="main-wrap bgc-white-darkest print" role="main">
<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("rap_cartecadeaux");?></span>
			<small id="search_label" class="print-only"></small>
		</h1>
		<h1 id="date_label" class="page-title pull-right fs-4 fw-light print-only"></h1>
		<div class="smart-links no-print">
			<ul class="nav" role="tablist">
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" onclick="window.print();" href="javascript:;">
						<i class="fa fa-print "></i>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<!-- End SubHeader-->
	<!-- Start Content-->
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
										<form method="get" id="formListCartecadeaux">
											<input type="hidden" name="p" value="<?= $_GET["p"]?>"/>
											<div class="row" style="margin-bottom:15px;">
												<div class="col-md-8">
													<div class="input-group bs-datepicker input-daterange picker-range">
														<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities($_GET["from"])? : date('Y-m-01')?>">
														<span class="input-group-addon px-3"><?= L("to"); ?></span>
														<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($_GET["to"])? : date('Y-m-d')?>">
													</div>
												</div>
											</div>
											<div class="row no-print">
												<div class="col-md-12"><input type="hidden" name="rap" value="utilisateurs"></div>
											</div>
											<div class="columns columns-right btn-group pull-right no-print">
												<button class="btn btn-default" type="reset" name="refresh" aria-label="refresh" title="<?php echo L('refresh',o); ?>" onclick="$(this).closest('form').find('.ui.select-dropdown').dropdown('clear');" ><i class="icon-refresh"></i></button>
												<button type="submit" class="btn btn-primary" id="btn_submit"><?= L("genererrapport","o") ?></button>
											</div>
										</form>
									</div>
									<?php if($_GET['from'] !='' && $_GET['to'] !=''){?>
										<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
											<div class="fixed-table-header" style="display: none;">
												<table></table>
											</div>
											<div class="fixed-table-body">
												<h3 class="topictitle"><?php echo formatDateUTF8($_GET['from']) ?> - <?php echo formatDateUTF8($_GET['to']) ?></h3>

												<table id="tableCartecadeaux" class="card-view-no-edit page-size-table table table-condensed table-no-bordered">
													<?php
													$enonce_cond = array();

													//CONDITIONS AGENT
													if( preg_match('#^\d+$#',$_GET["ID_STAFF"]) ){
														$enonce_cond[] = sprintf(" CARTE_OPERATION.ID_STAFF='%s' ",$_GET["ID_STAFF"]);
													}

													$matches = null;
													if ( preg_match('#(\d{4,5})-(\d{2})-(\d{2})#',$_GET['from'],$matches) ){
														$enonce_cond[] = sprintf(" CARTE_OPERATION.date_operation >= DATE('%s-%s-%s 00:00:00')",$matches[1],$matches[2],$matches[3]);
													}

													$matches = null;
													if ( preg_match('#(\d{4,5})-(\d{2})-(\d{2})#',$_GET['to'],$matches) ){
														$enonce_cond[] = sprintf(" CARTE_OPERATION.date_operation <= '%s-%s-%s 23:59:59'",$matches[1],$matches[2],$matches[3]);
													}

													//LISTE DES MAGASIN AVEC UNE DB
													if ( sizeof($enonce_cond) == 0 )
														$enonce_cond[] = "1=1";

													$enonce = sprintf("select CARTE_OPERATION.ID_MAGASIN, M_NOM, CARTE_OPERATION.*, CARTECADEAU.NUMCARTE
																		from CARTE_OPERATION
																			left join CARTECADEAU using(ID_CARTECADEAU)
																			left join MAGASIN on(MAGASIN.ID_MAGASIN = CARTE_OPERATION.ID_MAGASIN)
																		where %s
																	order by ID_MAGASIN asc, date_operation asc", implode(" and ",$enonce_cond));

													$resultHis = $mysqli->query( $enonce ) or die('Erreur SQL !'. $mysqli->error);
													?>
													<colgroup>
														<col class="width33" />
														<col class="width33" />
														<col class="width33" />
													</colgroup>
													<thead>
														<tr>
															<th>Numéro</th>
															<th>Date</th>
															<th class="text-right">Montant</th>
														</tr>
													</thead>

													<tbody>
														<?php
														$TOTALMONEY = 0;
														$ligneAgenceOld = "";
														$ligneAgenceNew = "";
														while ($uneLigneHistory = $resultHis->fetch_assoc()) {
															$ligneAgenceNew = $uneLigneHistory["ID_MAGASIN"];
															if($ligneAgenceOld != $ligneAgenceNew){
																$classsp = ($TOTALMONEY < 0)?"red":"";
																if($ligneAgenceOld != ''){
																	?>
																	<tr>
																		<td></td>
																		<td></td>
																		<td class="text-right"><?php  echo '<b>'.money_format('%n',$TOTALMONEY).'</b>'; ?></td>
																	</tr>
																	<?php
																} ?>
																<tr>
																	<td colspan="6" style="height:60px; vertical-align: bottom;"><h3><?php echo $uneLigneHistory["M_NOM"] ?></h3></td>
																</tr>
																<?php
																$TOTALMONEY = 0;
															}?>

															<tr>
																<td>
																	<?php echo $uneLigneHistory["NUMCARTE"] ?><br />
																</td>

																<td>
																	<?php echo formatDateUTF8(  $uneLigneHistory["date_operation"]  ); ?>
																</td>
																<td class="text-right">
																	<?php echo money_format('%n',$uneLigneHistory["montant"]) ?>
																</td>
															</tr>
															<?php
															$ligneAgenceOld = $uneLigneHistory["ID_MAGASIN"];
															$TOTALMONEY += $uneLigneHistory["montant"];
														}
														if( $ligneAgenceNew ){
															$classsp = ($TOTALMONEY < 0)?"red":"";
															?>
															<tr>
																<td></td>
																<td></td>
																<td class="text-right"> <?php  echo '<b>'.money_format('%n',$TOTALMONEY).'</b>'; ?></td>
															</tr>
															<?php
														}
														?>

													</tbody>
												</table>
											</div>
										</div>
										<?php
									}?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</section>