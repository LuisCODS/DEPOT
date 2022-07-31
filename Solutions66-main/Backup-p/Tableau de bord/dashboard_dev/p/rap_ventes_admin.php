<?php
ini_set("memory_limit","256M");
set_time_limit(300);


$allMag = [];
$queryAllMag = query("select * from MAGASIN where ouvert = 1 and caisse_db is not null order by M_NOM asc",[],$mysqli);
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}


//
$listID_MAGASINcanaccess = [];
if ( $_SESSION["utilisateur"]["security"] >= 2 ){
	$listID_MAGASINcanaccess = array_keys($_SESSION["magasins"]);
} else {
	$listID_MAGASINcanaccess = array_keys($allMag);
}
//$listID_MAGASIN
$listID_MAGASIN = [];
if ( is_array($_GET["ID_MAGASIN"]) ){
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) {
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanaccess) ){
			$listID_MAGASIN[] = $ID_MAGASIN;
		}
	}
}
if ( sizeof($listID_MAGASIN) < 1 ){
	$listID_MAGASIN = $listID_MAGASINcanaccess;
}
$listAND = [];
$listPARAM = [];
$date_start = null;
$date_end = null;
if($_GET['affichage'] == 'year'){
	$listAND[] = "1=1";
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to'])){
	
	$date_start = $_GET['from'];
	$date_end = $_GET['to'];
	$listAND[] = "( facture.date_insert >= ? AND facture.date_insert <= ? ) ";
	$listPARAM[] = $date_start . ' 00:00:00';
	$listPARAM[] = $date_end . " 23:59:59";
} else if(preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from'])){
	$date_start = $_GET['from'];
	$listAND[] = "( facture.date_insert >= ? ) ";
	$listPARAM[] = $date_start . ' 00:00:00';
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to'])){
	$date_end = $_GET['to'];
	$listAND[] = "( facture.date_insert <= ? ) ";
	$listPARAM[] = $date_end . " 23:59:59";
} else {
	$date_start = date("Y-m-d",strtotime("-30 days"));
	$date_end = date('Y-m-d');
	$listAND[] = "( facture.date_insert >= ? AND facture.date_insert <= ? ) ";
	$listPARAM[] = $date_start . ' 00:00:00';
	$listPARAM[] = $date_end . " 23:59:59";
}
$arrayData = [];
$arrayTotaux = [];
$listFrequence = [];
$and = implode(' and ',$listAND);

foreach($listID_MAGASIN as $ID_MAGASIN){
	$arrayTotaux[$ID_MAGASIN] = 0;
	$dbAnimoCaisse->select_db($allMag[$ID_MAGASIN]['caisse_db']);
	$queryRaport = "SELECT date(date_insert) `jour`, sum(soustotal) `totalsanstaxe`, sum(grandtotal) `totalavectaxe`, count(id_facture) `nbrefact`
					  FROM facture where $and
				  GROUP BY date(date_insert)
				  ORDER BY date_insert desc";
	$resultRaport = query($queryRaport,$listPARAM,$dbAnimoCaisse);
	if($resultRaport->num_rows > 0){
		while( $uneLigneFact = $resultRaport->fetch_assoc() ){
			$arrayTotaux[$ID_MAGASIN] += $uneLigneFact["totalavectaxe"];
			
			
			if($_GET['affichage'] == 'day'){
				$arrayData[$ID_MAGASIN][$uneLigneFact["jour"]] = $uneLigneFact["totalavectaxe"];
				if ( !in_array($uneLigneFact["jour"],$listFrequence) ){
					$listFrequence[] = $uneLigneFact["jour"];
				}
			} else if($_GET['affichage'] == 'month'){
				//Split date
				$splitDate = explode("-",$uneLigneFact["jour"]);
				$freq = $splitDate[0]."-".$splitDate[1];
				//Add
				if ( !isset($arrayData[$ID_MAGASIN][$freq]) ){
					$arrayData[$ID_MAGASIN][$freq] = 0;
				}
				$arrayData[$ID_MAGASIN][$freq] += $uneLigneFact["totalavectaxe"];
				if ( !in_array($freq,$listFrequence) ){
					$listFrequence[] = $freq;
				}
			} else if($_GET['affichage'] == 'year'){
				//Split date
				$splitDate = explode("-",$uneLigneFact["jour"]);
				$freq = $splitDate[0];
				//Add
				if ( !isset($arrayData[$ID_MAGASIN][$freq]) ){
					$arrayData[$ID_MAGASIN][$freq] = 0;
				}
				$arrayData[$ID_MAGASIN][$freq] += $uneLigneFact["totalavectaxe"];
				if ( !in_array($freq,$listFrequence) ){
					$listFrequence[] = $freq;
				}
			}
		}
	}
}
if ( $_GET["getFile"] == "1"  ){
	require_once(__DIR__."/../req/print.php");
	
	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_LANDSCAPE, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_LANDSCAPE, RPDF_SHOWCOL_ALLPAGE );
	}
	
	$titre = L("rap_ventes_admin","o");
	//$listSoustitre[] = ["En date du", date("Y-m-d") ];
	$listNomMag = [];
	
	if ( !empty($date_start) and !empty($date_end) ){
		$listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $date_start ) . " " . L("au") . " " . formatDateUTF8nonHTML( $date_end ) ];
	} elseif( !empty($date_start) ) {
		$listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $date_start ) ];
	} elseif( !empty($date_end) ) {
		$listSoustitre[] = ["date", L("jusqu'au") . " " . formatDateUTF8nonHTML( $date_end ) ];
	} else {
		$listSoustitre[] = ["date", "tous" ];
	}
	
	foreach( $listID_MAGASIN as $ID_MAGASIN ){
		$listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
	}
	$listSoustitre[] = ["magasin(s)", implode(", ",$listNomMag) ];
	
	$listEnteteColonne = [];
	$listEnteteColonne[0] = [];
	$listEnteteColonne[0][] = ["text"=>L('Date',"o"),"width"=>20,"align"=>"L"];
	foreach( $listNomMag as $nom_mag ){
		// récupérer le nom entre parenthèses (ex. Blainville (Fontainebleau), Mirabel (St-Canut))
		preg_match('#\(([a-z0-9-\ ]+)\)#i',$nom_mag,$matches);
		if(!empty($matches[1])){
			$nom_mag = trim($matches[1]);
		}
		$nom_mag = substr($nom_mag,0,6) . '.';
		$listEnteteColonne[0][] = ["text"=>$nom_mag,"width"=>17,"align"=>"R"];
	}
	$listEnteteColonne[0][] = ["text"=>L("moyenne"),"width"=>17,"align"=>"R"];
	
	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);
	
	$isfirst = true;
	if($_GET['affichage'] != ''){
		foreach($listFrequence as $freq){
			$listChamps = [];
			$moyenneSum = 0;
			$moyenneNb = 0;
			
			if($_GET['affichage'] == 'day'){
				$listChamps[] = date("Y-m-d",strtotime($freq));
			} else if($_GET['affichage'] == 'month'){
				$listChamps[] = $freq;
			} else if($_GET['affichage'] == 'year'){
				$listChamps[] = $freq;
			}
			foreach($listID_MAGASIN as $ID_MAGASIN){
				if ( isset($arrayData[$ID_MAGASIN][$freq]) ){
					$listChamps[] = nfs($arrayData[$ID_MAGASIN][$freq]);
					if ( round($arrayData[$ID_MAGASIN][$freq],2) != 0){
						$moyenneSum += $arrayData[$ID_MAGASIN][$freq];
						$moyenneNb++;
					}
				} else {
					$listChamps[] = "";
				}
			}
			
			if ( $moyenneNb != 0 ){
				$listChamps[] = nfs($moyenneSum/$moyenneNb);
			} else {
				$listChamps[] = "N/A";
			}
			
			$rapport->writeLigneRapport3wrap( $listChamps );
		}
	}
	$listChamps = [];
	$listChamps[] = 'TOTAL';
	foreach($listID_MAGASIN as $ID_MAGASIN){
		$listChamps[] = nfs($arrayTotaux[$ID_MAGASIN]);
	}
	$rapport->writeLigneGrandTotal( $listChamps, null, true, null, true );
	
	ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");
}
?>
<section id="main" class="main-wrap bgc-white-dark print" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("rap_ventes_admin","o");?></span>
		</h1>
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
	<div class="row">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<div class="panel p-3">
				<div class="panel-body">
					<form method="get" id="formListRapToilettage">
						<input type="hidden" name="p" value="<?= $_GET["p"]?>">
						<input type="hidden" name="sens" value="<?= $_GET["sens"] ?>">
						<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">

						<div class="row pb-2" >
							<div class="col-md-3 form-group">
								<select name="affichage" class="form-control">
									<option value="" <?= ($_GET['affichage'] == '')?"selected":"" ?>>Affichage sommaire</option>
									<option value="day" <?= ($_GET['affichage'] == 'day')?"selected":"" ?>>Affichage journalier</option>
									<option value="month" <?= ($_GET['affichage'] == 'month')?"selected":"" ?>>Affichage mensuel</option>
									<option value="year" <?= ($_GET['affichage'] == 'year')?"selected":"" ?>>Affichage annuel</option>
								</select>
								<?php /*
								<label><input type="checkbox" name="details" <?= $_GET['details'] == '1' ? 'checked ':''?>value="1" /> Rapport détaillé</label>
								*/ ?>
							</div>
							<div class="col-md-9 form-group">
								<div class="input-group bs-datepicker input-daterange picker-range">
									<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities($date_start)?>">
									<span class="input-group-addon px-3"><?= L("to"); ?></span>
									<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($date_end)?>">
								</div>
							</div>
							
							<?php if( sizeof($listID_MAGASINcanaccess) > 1 ){?>
								<div class="col-md-12">
									<div class="form-group">
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
						<div class="columns columns-right btn-group pull-right no-print">
							<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('afficher');?></button>
						</div>
					</form>
				</div>
				
				<div class="row">
					<div class="col-12 pt-3">
					</div>
					<div class="col-12">
						<?php if (!empty($arrayTotaux)) {?>
							<div class="table-responsive">
								
								<table class="table table-condensed table-bordered">
									<thead>
										<tr>
											<th></th>
											<?php
											foreach( $listID_MAGASIN as $ID_MAGASIN){
												$infoMag = query("select * from MAGASIN where ID_MAGASIN = ?",[$ID_MAGASIN,],$mysqli)->fetch_assoc();
												?>
												<th class="text-right">
													<?= $infoMag["M_NOM"] ?>
												</th>
												<?php
											}
											
											if (!empty($arrayData)){
												?>
												<th class="text-right"><?= L("moyenne") ?></th>
												<?php 
											} ?>
										</tr>
									</thead>
									<tbody>
										<?php
										if($_GET['affichage'] != '' and !empty($arrayData)){
											foreach ($listFrequence as $freq){
												?>
												<tr>
													<td style="font-weight:bold;">
														<?php 
														if($_GET['affichage'] == 'day'){
															echo date("Y-m-d",strtotime($freq));
														} else if($_GET['affichage'] == 'month'){
															echo $freq;
														} else if($_GET['affichage'] == 'year'){
															echo $freq;
														}?>
													</td>
													<?php
													$moyenneSum = 0;
													$moyenneNb = 0;
													foreach( $listID_MAGASIN as $ID_MAGASIN){
														?>
														<td class="text-right">
															<?php
															if ( isset($arrayData[$ID_MAGASIN][$freq]) ){
																echo nfs($arrayData[$ID_MAGASIN][$freq]);
																
																if ( round($arrayData[$ID_MAGASIN][$freq],2) != 0){
																	$moyenneSum += $arrayData[$ID_MAGASIN][$freq];
																	$moyenneNb++;
																}
															} else {
																
															}
															?>
														</td>
														<?php
													}
													?>
													<td class="text-right">
														<?php
														if ( $moyenneNb != 0 ){
															echo nfs($moyenneSum/$moyenneNb);
														} else {
															echo "N/A";
														}
														?>
													</td>
												</tr>
												<?php
											}
										}
										?>	
									</tbody>
									<tfoot>
										<tr>
											<th>
												TOTAL	
											</th>
											<?php 
											foreach( $listID_MAGASIN as $ID_MAGASIN){
											?>
												<th class="text-right">
													<?= nfs($arrayTotaux[$ID_MAGASIN])?>
												</th>
												<?php
											}
											?>
										</tr>
									</tfoot>
								</table>
							</div>
						<?php }?>
					</div>
				</div>
			</div>
		</section>
	</div>
</section>