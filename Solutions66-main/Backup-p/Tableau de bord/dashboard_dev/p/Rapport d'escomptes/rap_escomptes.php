<?php
ini_set("memory_limit","256M");
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

$listColonneTri = ['id_facture','date_insert','id_utilisateur','id_article','montant'];
if(in_array($_GET['orderby'],$listColonneTri)){ $orderby = $_GET['orderby']; }else{ $orderby = 'nb';}
if($_GET['sens']==''){ $sens = 'desc';}else{ $sens = $_GET['sens'];}

$listAND = [];
$listPARAM = [];

$listAND['dummy'] = '1=1';

$from = date("Y-m-d",strtotime('-30 day'));
$to = date("Y-m-d");
if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) ){
    $listAND['date'] = '(facture.date_insert >= ? AND facture.date_insert <= ?) ';
    $from = $_GET['from'];
    $to = $_GET['to'];
    $listPARAM['from'] = $from . ' 00:00:00';
    $listPARAM['to'] = $to . ' 23:59:59';
    
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) ){
    $from = $_GET['from'];
    $to = "";
    $listAND['date'] = '(facture.date_insert >= ? ) ';
    $listPARAM['from'] = $from . ' 00:00:00';
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) ){
    $from = "";
    $to = $_GET['to'];
    $listAND['date'] = '( facture.date_insert <= ? ) ';
    $listPARAM['to'] = $to . ' 23:59:59';
} else {
    //Failsafe
    $listAND['date'] = '(facture.date_insert >= ? AND facture.date_insert <= ?) ';
    $listPARAM['from'] = $from . ' 00:00:00';
    $listPARAM['to'] = $to . ' 23:59:59';
}

$caisse_db = 'animoetc_caisse_dummy';

$listAND['item_type'] = "( facture_item.type like 'ESCOMPTE%' or facture_item.type like 'SPECIAUX%' )";

$arrayData = [];

// loop tous les magasins
foreach($listID_MAGASIN as $ID_MAGASIN){
    $arrayData[$ID_MAGASIN] = [];
    $caisse_db = $allMag[$ID_MAGASIN]['caisse_db'];
    // créer une copie du connecteur de la caisse
    $dbAnimoUneCaisse = $dbAnimoCaisse;
    $dbAnimoUneCaisse->select_db($caisse_db);
    
    // si l'utiliser a le choix de plusieurs magasins vérifier si le user est bien dans les magasins sélectionnées
    if(preg_match('#^\d+$#', $_REQUEST["staff"])){
        $listPARAM['staff'] = $_REQUEST["staff"];
        $listPARAM['mag'] = $ID_MAGASIN;
        // si plusieurs magasins et recherche user valider si l'utilisateur existe dans ces magasins
        // ainsi la recherche est un peu logique
        $listAND['staff_u'] = 'utilisateur.inactif is null and utilisateur.id_intranet IN(
					SELECT u2.id_utilisateur from animoetc_dashboard.utilisateur_magasin as u2
					WHERE u2.id_utilisateur = ? AND u2.id_magasin = ?)';
    }
    
    $and = implode(' and ',array_values($listAND));
    
    $resultRaport = query("SELECT *, facture_item.montant AS MIT, facture.date_insert AS dateFact
					FROM facture_item JOIN facture USING(id_facture) JOIN utilisateur USING(id_utilisateur)
					WHERE $and
					GROUP BY id_facture_item ORDER BY facture.date_insert"
        ,array_values($listPARAM),$dbAnimoUneCaisse);
    
    if($resultRaport->num_rows > 0){
        $arrayData[$ID_MAGASIN] = [
            'data'=>[],
            'totalRabais'=>0
        ];
        $isFirstLine = true;
        while($rowRaport = $resultRaport->fetch_assoc()){
            // si un utilisateur spécifique est sélectionné le mettre dans les header du PDF
            if($isFirstLine){
                $isFirstLine = false;
                if(!empty($listPARAM['staff'])){
                    $listSoustitre[] = [L('utilisateur'),implode(' ',[$rowRaport['prenom'],$rowRaport['nom']])];
                }
            }
            
            $rowRaport['MIT'] *= -1;
            $arrayData[$ID_MAGASIN]['totalRabais'] += round($rowRaport["montant"]*-1,2);
            $arrayData[$ID_MAGASIN]['data'][] = $rowRaport;
        }
    }
    uasort( $arrayData[$ID_MAGASIN]['data'], function($a,$b){
        global $orderby,$sens;
        if ( $a[$orderby] < $b[$orderby]){
            return ($sens=="desc")?1:-1;
        } elseif( $a[$orderby] > $b[$orderby] ){
            return ($sens=="desc")?-1:1;
        }
        return 0;
    });
        // garbage collection
        unset($dbAnimoUneCaisse);
}
if ( $_GET["getFile"] == "1"  ){
    require_once(__DIR__."/../req/print.php");
    
    if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
        $rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
    } else {
        $rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
    }
    
    $titre = L("rap_escomptes","o");
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
    $listSoustitre[] = ["magasin(s)", implode(", ",$listNomMag) ];
    
    if($_GET['details']==1){
        $listEnteteColonne = [
            [
                ["text"=>L('fact#',"o"),"width"=>20,"align"=>"L"],
                ["text"=>L('Date transac.',"o"),"width"=>30,"align"=>"L"],
                ["text"=>L('utilisateur',"o"),"width"=>45,"align"=>"L"],
                ["text"=>L('Article',"o"),"width"=>65,"align"=>"L"],
                ["text"=>L('Escompte',"o"),"width"=>20,"align"=>"R"],
            ]
        ];
    }else{
        $listEnteteColonne = [
            [
                ["text"=>L('',"o"),"width"=>20,"align"=>"L"],
                ["text"=>L('',"o"),"width"=>30,"align"=>"L"],
                ["text"=>L('',"o"),"width"=>45,"align"=>"L"],
                ["text"=>L('',"o"),"width"=>65,"align"=>"L"],
                ["text"=>L('Escompte',"o"),"width"=>20,"align"=>"R"],
            ]
        ];
    }
    $rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
    $rapport->setInfoCols(-1);
    
    $isfirst = true;
    foreach($arrayData as $ID_MAGASIN => $data){
        if($_GET['details'] == '1'){
            if(count($data['data']) > 0){
                if (!$isfirst){
                    $rapport->addPage();
                }
                $isfirst = false;
                
                if( sizeof($listID_MAGASINcanaccess) > 1 ){
                    $rapport->SetFont('helvetica', 'B', 12);
                    $rapport->Cell(0, 0, $allMag[$ID_MAGASIN]["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
                }
                foreach ( $data['data'] as $rowRaport){
                    $listChamps = [];
                    $listChamps[] = $rowRaport['id_facture'];
                    $listChamps[] = $rowRaport['dateFact'];
                    $listChamps[] = implode(' ',[$rowRaport['prenom'],$rowRaport['nom']]);
                    $listChamps[] = $rowRaport["label"];
                    $listChamps[] = nfs($rowRaport['MIT']);
                    $rapport->writeLigneRapport3wrap( $listChamps );
                }
            }
            $listTOTAL = [];
            $listTOTAL[] = "";
            $listTOTAL[] = "";
            $listTOTAL[] = "";
            $listTOTAL[] = "TOTAL";
            $listTOTAL[] = nfs($data['totalRabais']);
            $rapport->writeLigneGrandTotal($listTOTAL,[false,false,false,true,true]);
        }
        if($_GET['details'] != '1'){
            if( sizeof($listID_MAGASINcanaccess) > 1 ){
                $rapport->SetFont('helvetica', 'B', 12);
                $rapport->Cell(0, 0, $allMag[$ID_MAGASIN]["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
            }
            $listTOTAL = [];
            $listTOTAL[] = "";
            $listTOTAL[] = "";
            $listTOTAL[] = "";
            $listTOTAL[] = "TOTAL";
            $listTOTAL[] = nfs($data['totalRabais']);
            $rapport->writeLigneGrandTotal($listTOTAL,[false,false,false,true,true]);
        }
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
			<span class="hidden-xs-down"><?= L("rap_escomptes","o");?></span>
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
				<?php /*?>
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" href="index.php?<?= rebuildQueryString(["format"=>"pdf","getFile"=>"1"]) ?>" target="_blank">
						<i class="fa fa-file-pdf-o "></i>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" href="index.php?<?= rebuildQueryString(["format"=>"xlsx","getFile"=>"1"]) ?>" target="_blank">
						<i class="fa fa-file-excel-o "></i>
					</a>
				</li><?php */?>
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
											
											<input type="hidden" name="sens" value="<?= $_GET["sens"] ?>">
											<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">
											<div class="row" style="margin-bottom:15px;">
												<div class="col-md-8">
													<div class="input-group bs-datepicker input-daterange picker-range">
														<input type="text" class="form-control" name="from" id="from" value="<?= htmlentities($from)?>">
														<span class="input-group-addon px-3"><?= L("to"); ?></span>
														<input type="text" class="form-control" name="to" id="to" value="<?= htmlentities($to)?>">
													</div>
												</div>
												<div class="col-md-4">
													<select class="ui fluid search simple-select select-dropdown" name="staff">
														<option selected value="all">Tous les utilisateurs</option>
														<?php
														$enonce = 'SELECT DISTINCT(utilisateur.id_utilisateur),prenom,nom FROM utilisateur JOIN utilisateur_magasin USING(id_utilisateur) where utilisateur.inactif is null AND utilisateur.security >= ? AND utilisateur_magasin.id_magasin IN('.implode(',',$listID_MAGASIN).') ORDER BY utilisateur.prenom,utilisateur.nom';
														$resultUser = query($enonce,[get_current_security_level()],$mysqli);
														while($rowUser = $resultUser->fetch_assoc()){
															printf("<option value='%s'%s>%s</option>", $rowUser["id_utilisateur"],($rowUser["id_utilisateur"] == $_REQUEST["staff"] ? " selected" : ""),$rowUser["prenom"] . " " . $rowUser["nom"]);
														}
														?>
													</select>
												</div>
												<div class="col-md-9 form-group"></div>
												<div class="col-md-3 form-group text-right">
                    								<label><input type="checkbox" name="details" <?= $_GET['details'] == '1' ? 'checked ':''?>value="1" /> Rapport détaillé</label>
                    							</div>
												<?php if( sizeof($listID_MAGASINcanaccess) > 1 ){ $isMultiMag = true;?>
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
											foreach($arrayData as $ID_MAGASIN => $data){
												if(count($data['data']) > 0){
													?>
													<div class="col-12 pb-5" id="<?= $allMag[$ID_MAGASIN]["CONTACT_SUFFIX"]?>">
														<h3 class="">Animo etc <?= $allMag[$ID_MAGASIN]['M_NOM']?></h3>
														<div class="table-responsive">
															<table class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
																<?php
																	if($_GET['details'] == '1'){
															    ?>
    																<thead>
    																	<tr>
    																		<th style="text-align:left;">
    																			<a href="index.php?<?= rebuildQueryString(['orderby'=>'id_facture','sens'=>($orderby == 'id_facture' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> <?= $L['fact#']?> <?= '<i class="fa fa-sort'.(($orderby == 'id_facture' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>
    																		</th>
    																		<th style="text-align:left;">
    																			<a href="index.php?<?= rebuildQueryString(['orderby'=>'date_insert','sens'=>($orderby == 'date_insert' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> Date transaction <?= '<i class="fa fa-sort'.(($orderby == 'date_insert' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>
    																		</th>
    																		<th style="text-align:left;">
    																			<a href="index.php?<?= rebuildQueryString(['orderby'=>'id_utilisateur','sens'=>($orderby == 'id_utilisateur' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> Utilisateur caisse <?= '<i class="fa fa-sort'.(($orderby == 'id_utilisateur' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>
    																		</th>
    																		<th style="text-align:left;">
    																			<a href="index.php?<?= rebuildQueryString(['orderby'=>'id_article','sens'=>($orderby == 'id_article' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> Article <?= '<i class="fa fa-sort'.(($orderby == 'id_article' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>
    																		</th>
    																		<th style="text-align:right;">
    																			<a href="index.php?<?= rebuildQueryString(['orderby'=>'montant','sens'=>($orderby == 'montant' ? ($sens == 'desc' ? 'asc' : 'desc') : $sens)])?>"> Escompte <?= '<i class="fa fa-sort'.(($orderby == 'montant' ? ($sens == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?></a>
    																		</th>
    																	</tr>
    																</thead>
																<tbody>
																<?php }?>
																	<?php
																	if($_GET['details'] == '1'){
    																	foreach($data['data'] as $rowRaport){?>
    																		<tr>
    																			<td style="text-align:left;">
    																				<a class="ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?= $rowRaport["id_facture"]?>&ID_MAGASIN=<?php echo $ID_MAGASIN?>"><?= $rowRaport["id_facture"]?></a>
    																			</td>
    																			<td style="text-align:left;">
    																				<?= $rowRaport["dateFact"]?>
    																			</td>
    																			<td style="text-align:left;">
    																				<?= $rowRaport["prenom"] . " " . $rowRaport["nom"]?>
    																			</td>
    																			<td style="text-align:left;">
    																				<?= $rowRaport["label"]?>
    																			</td>
    																			<td style="text-align:right;">
    																				<?= nfs($rowRaport["MIT"]);?>
    																			</td>
    																		</tr>
    																		<?php
    																	}
																	}
																	?>
																</tbody>
																<?php
																if(count($data['data']) > 0){
																	?>
																	<tfoot style="font-weight:bold;">
																		<tr>
																			<td></td>
																			<td></td>
																			<td></td>
																			<td class="droit<?php if($_GET['details'] != '1'){echo ' col-10 text-right';}?>">
																				Total
																			</td>
																			<td class="droit<?php if($_GET['details'] != '1'){echo ' col-2 text-right';}?>" style="text-align:right;"><?= nfs($data['totalRabais'])?></td>
																		</tr>
																	</tfoot>
																	<?php
																}?>

															</table>
														</div>
													</div>
													<?php
												}
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