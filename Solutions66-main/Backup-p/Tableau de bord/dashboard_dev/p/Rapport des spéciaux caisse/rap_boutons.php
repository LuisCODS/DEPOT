<?php
ini_set("memory_limit","512M");
set_time_limit(300);

$listSoustitre = [];
$allMag = [];
$queryAllMag = query("select * from MAGASIN where caisse_db is not null order by M_NOM asc",[],$mysqli);
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
    $allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}

//$listID_MAGASINcanaccess
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


$listID_MAGASINstr = implode(",",$listID_MAGASIN);

$listAND = [];
$listPARAM = [];

if ($_REQUEST["boutons"]){	
	$listAND['dummy'] = 'i.type = ?';
	$listPARAM[] = $_REQUEST["boutons"];
}

$from = "";
$to = "";
if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) ){
    $listAND['date'] = '(f.date_insert >= ? AND f.date_insert <= ?) ';
    $from = $_GET['from'];
    $to = $_GET['to'];
    $listPARAM[] = $from . ' 00:00:00';
    $listPARAM[] = $to . ' 23:59:59';
    
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) ){
    $from = $_GET['from'];
    $to = "";
    $listAND['date'] = '(f.date_insert >= ? ) ';
    $listPARAM[] = $from . ' 00:00:00';
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) ){
    $from = "";
    $to = $_GET['to'];
    $listAND['date'] = '( f.date_insert <= ? ) ';
    $listPARAM[] = $to . ' 23:59:59';
} else {
    //Failsafe
    //$listAND['date'] = '(facture.date_insert >= ? AND facture.date_insert <= ?) ';
    //$listPARAM['from'] = $from . ' 00:00:00';
    //$listPARAM['to'] = $to . ' 23:59:59';
}

$arrayData = [];

if ($_REQUEST["boutons"]){	
	foreach($listID_MAGASIN as $ID_MAGASIN){
        $caisse_db = $allMag[$ID_MAGASIN]['caisse_db'];
        $dbAnimoUneCaisse = $dbAnimoCaisse;
        $dbAnimoUneCaisse->select_db($caisse_db);
        $stmt = query("SELECT i.id_facture, sum(i.montant) `montant`, f.date_insert, i.ordre
                        FROM facture_item i 
                            JOIN facture f ON f.id_facture = i.id_facture 
        				WHERE ".implode(' and ',$listAND)." GROUP BY i.id_facture_item",$listPARAM,$dbAnimoUneCaisse);
        
    	$arrayData[$ID_MAGASIN] = ["nb" => 0, "total" => 0, "liste"=>[]];
        while($row = $stmt->fetch_assoc()){
            
            $resItem = query("SELECT SUM(nb) `nb`
                            FROM facture_item
                            WHERE id_facture = ".$row["id_facture"]." AND ordre = ".(--$row["ordre"]),[],$dbAnimoUneCaisse);
            while($rowItem = $resItem->fetch_assoc()){
                $row["nb"] += $rowItem["nb"];
            }
            
        	$arrayData[$ID_MAGASIN]["nb"] += $row["nb"];
        	$arrayData[$ID_MAGASIN]["total"] += $row["montant"];
            $arrayData[$ID_MAGASIN]["liste"][$row["id_facture"]]["nb"] += $row["nb"];
            $arrayData[$ID_MAGASIN]["liste"][$row["id_facture"]]["montant"] += $row["montant"];
            $arrayData[$ID_MAGASIN]["liste"][$row["id_facture"]]["date_insert"] = $row["date_insert"];
        }
    }
}
//vex($arrayData);
//die();

if ( $_GET["getFile"] == "1"  ){
    require_once(__DIR__."/../req/print.php");
    
    if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
        $rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
    } else {
        $rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
    }
    
    $titre = L("rapport des spéciaux caisse","o");
    //$listSoustitre[] = ["En date du", date("Y-m-d") ];
    $listNomMag = [];
    
    if ( !empty($from) and !empty($to) ){
        $listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $from ) . " " . L("au") . " " . formatDateUTF8nonHTML( $to ) ];
    } elseif( !empty($from) ) {
        $listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $from ) ];
    } elseif( !empty($to) ) {
        $listSoustitre[] = ["date", L("jusqu'au") . " " . formatDateUTF8nonHTML( $to ) ];
    } else {
        $listSoustitre[] = ["date", "tous" ];
    }
    
    foreach( $listID_MAGASIN as $ID_MAGASIN ){
        $listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
    }
    $listSoustitre[] = [L("magasin(s)"), implode(", ",$listNomMag) ];
    
    if( $_GET['details']=="1" ){
        $listEnteteColonne = [
            [
                ["text"=>L('fact#',"o"),"width"=>20,"align"=>"L"],
                ["text"=>L('Date transac.',"o"),"width"=>30,"align"=>"L"],
                ["text"=>L('nombre de fois',"o"),"width"=>45,"align"=>"R"],
                ["text"=>L('total escompte',"o"),"width"=>35,"align"=>"R"],
            ]
        ];
    } else {
        $listEnteteColonne = [
            [
                ["text"=>L('magasin',"o"),"width"=>35,"align"=>"L"],
                ["text"=>L('nombre de fois',"o"),"width"=>35,"align"=>"R"],
                ["text"=>L('total escompte',"o"),"width"=>25,"align"=>"R"],
            ]
        ];
    }
    $rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
    $rapport->setInfoCols(-1);
    
	$grand_nb = 0;
	$grand_total = 0;
	
    if($_GET['details'] == '1'){
    	$isfirst = true;
		foreach($arrayData as $ID_MAGASIN => $dataMag ){
			$grand_nb += $dataMag["nb"];
			$grand_total += $dataMag["total"];
			
			if (!$isfirst){
				$rapport->addPage();
			}
			$isfirst = false;
			
			if( sizeof($listID_MAGASINcanaccess) > 1 ){
				$rapport->SetFont('helvetica', 'B', 12);
				$rapport->Cell(0, 0, $allMag[$ID_MAGASIN]["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
			}
			foreach ( $dataMag['liste'] as $id_factu => $rowRabais){
				$listChamps = [];
				$listChamps[] = $id_factu;
				$listChamps[] = $rowRabais['date_insert'];
				$listChamps[] = $rowRabais["nb"];
				$listChamps[] = nfs($rowRabais['montant']);
				$rapport->writeLigneRapport3wrap( $listChamps );
			}
			
			$rapport->writeLigneTotaux(["","",$dataMag["nb"],$dataMag["total"]],[false,false,true,true]);
		}
		
		$rapport->Ln(10);
		$listTOTAL = [];
		$listTOTAL[] = "";
		$listTOTAL[] = "";
		$listTOTAL[] = $grand_nb;
		$listTOTAL[] = nfs($grand_total);
		$rapport->writeLigneGrandTotal($listTOTAL,[false,false,true,true]);
	} else {
		foreach($arrayData as $ID_MAGASIN => $dataMag ){
			$grand_nb += $dataMag["nb"];
			$grand_total += $dataMag["total"];
			
			$listChamps = [];
			$listChamps[] = $allMag[$ID_MAGASIN]["M_NOM"];
			$listChamps[] = $dataMag["nb"];
			$listChamps[] = nfs($dataMag['total']);
			$rapport->writeLigneRapport3wrap( $listChamps );
		}
			
		$listTOTAL = [];
		$listTOTAL[] = "";
		$listTOTAL[] = $grand_nb;
		$listTOTAL[] = nfs($grand_total);
		$rapport->writeLigneGrandTotal($listTOTAL,[false,true,true]);
	}
    
    ob_clean();
    $rapport->Output( formatFileName($titre).'.pdf', 'I');
    die("");
}
?>
<section id="main" class="main-wrap bgc-white-darkest print" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("rapport des spéciaux caisse","o");?></span>
		</h1>
		<h1 id="date_label" class="page-title pull-right fs-4 fw-light print-only"></h1>
		<div class="smart-links no-print">
			<ul class="nav" role="tablist">
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
	<div class="row pl-3 pr-3 mb-3 mt-3 print-top">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<div class="panel pb-0">
				<div class="panel-body">
					<div class="panel bgc-white-dark transition visible">
						<div class="panel-body panel-body-p">
							<div class="page-size-table">
								<div class="bootstrap-table">
									<div class="fixed-table-toolbar no-print">
										<form method="get">
											<input type="hidden" name="p" value="<?= $_REQUEST["p"]?>">
											<div class="row" style="margin-bottom:15px;">
												<div class="col-md-4">
													<select class="ui fluid search simple-select select-dropdown" name="boutons">
														<option value="all">Liste des boutons</option>
														<?php
														$enonce ='SELECT * FROM escompte_btn where escompte_btn.efface is null ORDER BY escompte_btn.date_debut desc';
														$resultBtn = query($enonce,[],$dbAnimoCaisseDefault);
														while($rowBtn = $resultBtn->fetch_assoc()){
														    $date_debut = strtotime($rowBtn["date_debut"]);
														    $date_fin = strtotime($rowBtn["date_fin"]);
														    $leTemps = date('Y-m-d',$date_debut) . " au " . date('Y-m-d',$date_fin) ;
														    
														    if(!empty($_GET["boutons"]) && $_GET["boutons"] == "ESCOMPTE:".$rowBtn["id_escompte_btn"]){
														       printf("<option value=%s selected>%s %s</option>", "ESCOMPTE:" . $rowBtn["id_escompte_btn"],$leTemps . " : ",$rowBtn["label"]);
														    }else{
														      printf("<option value=%s>%s %s</option>", "ESCOMPTE:" . $rowBtn["id_escompte_btn"],$leTemps . " : ",$rowBtn["label"]);
														    }
														}
														?>
													</select>
												</div>
												<div class="col-md-8">
													<div class="input-group bs-datepicker input-daterange picker-range">
														<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities($from)?>">
														<span class="input-group-addon px-3"><?= L("to"); ?></span>
														<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($to)?>">
													</div>
												</div>
												<?php 
												if( sizeof($listID_MAGASINcanaccess) > 1 ){
													?>
													<script>
														var formSubmitted = false;
														function onchangeSelectMag(ob,e){
															e = e || window.event;
															if(formSubmitted){
																return;
															}
															formSubmitted = true;
															blockUI();
															$(ob).closest('form').submit();
														}
													</script>
													<div class="col-12">
														<div class="pt-3">
															<select class="ui fluid normal multi-selection select-dropdown form-control" name="ID_MAGASIN[]" multiple onchange="onchangeSelectMag(this,event)">
																<?php
																foreach( $listID_MAGASINcanaccess as $ID_MAGASIN){
																	$infoMag = query("select * from MAGASIN where ID_MAGASIN = ?",[$ID_MAGASIN,],$mysqli)->fetch_assoc();
																	printf("<option value='%s'%s>%s</option>", $ID_MAGASIN,( in_array($ID_MAGASIN,$listID_MAGASIN)?" selected":""),$infoMag["M_NOM"]);
																}
																?>
															</select>
														</div>
													</div>
												<?php } ?>
											</div>
											<div class="col-md-12">
												<label><input type="checkbox" name="details" <?= $_GET['details'] == '1' ? 'checked ':''?>value="1" /> Rapport détaillé</label>
											</div>
											<div class="columns columns-right btn-group pull-right no-print">
												<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('afficher');?></button>
											</div>
										</form>
									</div>
									<div class="row">
										<div class='col-12 p-1'>
											<hr />
										</div>
										<?php
										if(count($arrayData) > 0){
											$grand_nb = 0;
											$grand_total = 0;
											
											if($_GET['details'] == '1'){
												foreach($arrayData as $ID_MAGASIN => $dataMag){
													$grand_nb += $dataMag["nb"];
													$grand_total += $dataMag["total"];
													?>
													<div class="col-12 pb-5" id="<?= $allMag[$ID_MAGASIN]["CONTACT_SUFFIX"]?>">
														<h3 class="">Animo etc <?= $allMag[$ID_MAGASIN]['M_NOM']?></h3>
														<div class="table-responsive">
															<table class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
																<thead>
																	<tr>
																		<th style="width:25%">
																			<?= L("fact#","o") ?>
																		</th>
																		<th style="width:25%">
																			<?= L("Date transac.","o") ?>
																		</th>
																		<th style="text-align:right;width:25%">
																			<?= L("nombre de fois","o") ?>
																		</th>
																		<th style="text-align:right;width:25%">
																			<?= L("total escompte","o") ?>
																		</th>
																	</tr>
																</thead>
																
																<tbody>
																	<?php 
																	foreach( $dataMag["liste"] as $id_fact => $uneLigneItem ){
																		?>
																		<tr>
																			<td><a class="ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?= $id_fact;?>&ID_MAGASIN=<?= $ID_MAGASIN ?>"><?= $id_fact;?></a></td>
																			<td><?= $uneLigneItem["date_insert"]; ?></td>
																			<td class="droit text-right">
																				<?= $uneLigneItem["nb"]; ?>
																			</td>
																			<td class="droit text-right"><?= nfs($uneLigneItem["montant"]); ?></td>
																		</tr>
																		<?php 
																	}
																	?>
																</tbody>
																
																<tfoot style="font-weight:bold;">
																	<tr>
																		<td></td>
																		<td></td>
																		<td class="droit text-right">
																			<?= $dataMag["nb"]; ?>
																		</td>
																		<td class="droit text-right"><?= nfs($dataMag["total"]); ?></td>
																	</tr>
																</tfoot>
															</table>
														</div>
													</div>
													<?php
												}
											} else {
												?>
												<div class="col-12 pb-5" >
													<div class="table-responsive">
														<table class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
															<thead>
																<tr>
																	<th style="text-align:left; width:40%;">
																		<?= L("nom du magasin","o") ?>
																	</th>
																	<th style="text-align:right;">
																		<?= L("nombre de fois","o") ?>
																	</th>
																	<th style="text-align:right;">
																		<?= L("total escompte","o") ?>
																	</th>
																</tr>
															</thead>
															
															<tbody>
															<?php 
															foreach($arrayData as $ID_MAGASIN => $dataMag){
																?>
																<tr>
																	<td style="text-align:left;">
																		<?= $allMag[$ID_MAGASIN]['M_NOM'] ?>
																	</td>
																	<td style="text-align:right;">
																		<?= $dataMag["nb"] ?>
																	</td>
																	<td style="text-align:right;">
																		<?= nfs($dataMag["total"]) ?>
																	</td>
																</tr>
																<?php
																$grand_nb += $dataMag["nb"];
																$grand_total += $dataMag["total"];
															}
															?>
															</tbody>
															
															<tfoot style="font-weight:bold;">
																<tr>
																	<td></td>
																	<td class="droit text-right">
																		<?= $grand_nb; ?>
																	</td>
																	<td class="droit text-right"><?= nfs($grand_total); ?></td>
																</tr>
															</tfoot>
														</table>
													</div>
												</div>
												<?php
												
											}
										
										}
										?>
										<div class="clearfix"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</section>