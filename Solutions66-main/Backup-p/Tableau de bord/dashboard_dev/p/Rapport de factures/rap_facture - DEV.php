<?php

ini_set("memory_limit","256M");
set_time_limit(300);

$allMag = [];
$queryAllMag = query("select * from MAGASIN where caisse_db is not null order by M_NOM asc",[],$mysqli);
while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}


$listID_MAGASINcanaccess = [];
if ( $_SESSION["utilisateur"]["security"] >= 2 ){
	$listID_MAGASINcanaccess = array_keys($_SESSION["magasins"]);
} else {
	$listID_MAGASINcanaccess = array_keys($allMag);
}
$listID_MAGASIN = [];
if ( isset($_GET["ID_MAGASIN"]) ){
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) {
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanaccess) ){
			$listID_MAGASIN[] = $ID_MAGASIN;
		}
	}
}
if ( count($listID_MAGASIN) < 1 ){
	$listID_MAGASIN = $listID_MAGASINcanaccess;
}

$data = [];
$listAND = [];
$listPARAM = [];

//date range | default: 1 jour
if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) !='' and preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) !='' ){
	$listAND[] = "( facture.date_insert >= ? AND facture.date_insert <= ? ) ";
	$listPARAM[] = $_GET['from'] . " 00:00:00";
	$listPARAM[] = $_GET['to'] . " 23:59:59";
} else if(preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) !='' ){
	$listAND[] = "( facture.date_insert >= ? ) ";
	$listPARAM[] = $_GET['from'] . " 00:00:00";
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) !='' ){
	$listAND[] = "( facture.date_insert <= ? ) ";
	$listPARAM[] = $_GET['to'] . " 23:59:59";
} else {
	$listAND[] = "( facture.date_insert >= ? ) ";
	$listPARAM[] = date("Y-m-d",strtotime("-1 days")) . " 00:00:00";
}

$listOR = [];

if( isset($_GET['rangeFacture']) ){
    $arrayRange = explode( ',' , preg_replace('/\s+/', '', $_GET['rangeFacture']) );
    foreach ($arrayRange as $range){
        if( preg_match('#^(\d+)\-(\d+)$#', $range, $matches )){
            $listOR[] = "( facture.id_facture BETWEEN ? AND ? ) ";
            $listPARAM[] = intval($matches[1],10);
            $listPARAM[] = intval($matches[2],10);
        } else if( preg_match('#^\d+$#', $range) ){
            $listOR[] = "( facture.id_facture = ? ) ";
            $listPARAM[] = $range;
        }
    }
    
    if( count($listOR) >= 1 ){
        $listAND[] = "(".implode(" OR ", $listOR).")";    
    }
}

$listANDstr = implode(" AND ", $listAND);

//Pour chaque magasin sélectionnée 
foreach ( $listID_MAGASIN as $ID_MAGASIN )
{
    
    $data[$ID_MAGASIN] = [ "lignes"=>[],   
                            "nbrefact"=>0, 
                            "totalsanstaxe"=>0,
                            "totalavectaxe"=>0 
                        ];
    
    $enonce = "SELECT facture.*
			   FROM {$allMag[$ID_MAGASIN]["caisse_db"]}.facture
			   LEFT JOIN {$allMag[$ID_MAGASIN]["caisse_db"]}.utilisateur USING(id_utilisateur)
			   WHERE $listANDstr 
			   order by facture.date_insert";
			   
	$resultFactItem = query($enonce,$listPARAM,$dbAnimoCaisse);
	while( $uneLigneFact = $resultFactItem->fetch_assoc() )
	{
	    $uneLigneFact["paiement"] = ["lignes"=>[],
                                    "montant"=>0];

	    $queryPaiement = "SELECT `type`, `compagnie`, sum(montant) `montant`, count(id_facture_paiement) `nb` 
	                        FROM {$allMag[$ID_MAGASIN]["caisse_db"]}.facture_paiement 
	                        WHERE id_facture = ?  
	                        GROUP BY `type`, `compagnie`";
	                        
	    $resulpaiement = query($queryPaiement,[$uneLigneFact["id_facture"],],$dbAnimoCaisse);
	    
	    while ( $uneLigne = $resulpaiement->fetch_assoc() )
	    {
		    $uneLigne["label"] = L("paiement:".$uneLigne["type"].":".$uneLigne["compagnie"]);
		    if ( $uneLigne["type"]=="cash" and preg_match('#^CC[0-9]+$#i',$uneLigne["compagnie"]) ){
			    $uneLigne["label"] = L("Carte-cadeau #") . $uneLigne["compagnie"];
		    }

		    $uneLigneFact["paiement"]["lignes"][] = $uneLigne;
		    $uneLigneFact["paiement"]["montant"] += $uneLigne["montant"];
	    }

	    $uneLigneFact["points"] = ["lignes"=>[],"montant"=>0];
	    
	    $queryPaiement = "SELECT * FROM POINTS  WHERE id_facture = ? and ID_MAGASIN = ?";
	    
	    $resulpoints= query($queryPaiement,[$uneLigneFact["id_facture"],$ID_MAGASIN],$mysqli);
	    
	    while ( $uneLigne = $resulpoints->fetch_assoc() ){
		    $uneLigneFact["points"]["lignes"][] = $uneLigne;
		    $uneLigneFact["points"]["montant"] += $uneLigne["points"];
	    }
	   
	    $data[$ID_MAGASIN]["lignes"][] = $uneLigneFact;
	    $data[$ID_MAGASIN]["grandtotal"] += $uneLigneFact["grandtotal"];
	    $data[$ID_MAGASIN]["soustotal"] += $uneLigneFact["soustotal"];
	    $data[$ID_MAGASIN]["totalpoints"] += $uneLigneFact["points"]["montant"];
	    $data[$ID_MAGASIN]["nbrefact"] += 1;
    }
}

//echo '<pre>' , print_r($_REQUEST) , '</pre>';
// ======================   GESTION TRI   =================================

$listTriPosible = ["date_insert","id_facture","grandtotal","soustotal","num_avantages","points"];

if ( !in_array($_GET["orderby"],$listTriPosible) ){
	$_GET["orderby"] = $listTriPosible[0];
}

if ( $_GET["sens"] == 'desc' ){
	$_GET["sens"] = "desc";
} else {
	$_GET["sens"] = "asc";
}

foreach($data as $ID_MAGASIN=>$dataMag){
    
	usort( $data[$ID_MAGASIN]["lignes"], function($a,$b){
		if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
			return ($_GET["sens"]=="desc")?1:-1;
		} elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
			return ($_GET["sens"]=="desc")?-1:1;
		}
		return 0;
	});
	
}
// ============== GESTION PDF/EXCEL  ================= 

if ( $_GET["getFile"] == "1" and $data ){
    
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}
	
    $listNomMag = [];
	$titre = L("rap_facture","o");
	

	if ( !empty($_GET['from']) and !empty($_GET['to']) ){
		$listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $_GET['from'] ) . " " . L("au") . " " . formatDateUTF8nonHTML( $_GET['to'] ) ];
	} elseif( !empty($_GET['from']) ) {
		$listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $_GET['from'] ) ];
	} elseif( !empty($_GET['to']) ) {
		$listSoustitre[] = ["date", L("jusqu'au") . " " . formatDateUTF8nonHTML( $_GET['to'] ) ];
	} else {
		$listSoustitre[] = ["date", "tous" ];
	}
	
	foreach( $listID_MAGASIN as $ID_MAGASIN ){
		$listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
	}
	$listSoustitre[] = ["magasin(s)", implode(", ",$listNomMag) ];
	
	$listEnteteColonne = [
                			[ 
                			    ["text"=>"Date","width"=>35,"align"=>"L"], 
                			    ["text"=>L("#Facture",'o'),"width"=>25,"align"=>"C"],
                			    ["text"=>L("Total (sans taxes)",'o'),"width"=>20,"align"=>"R"],
                			    ["text"=>L("mode_paiement",'o'),"width"=>35,"align"=>"R"],
                			    ["text"=>L("Notes",'o'),"width"=>25,"align"=>"R"],
                			    ["text"=>L("carte Animo",'o'),"width"=>22,"align"=>"C"],
                			    ["text"=>L("$ Animo",'o'),"width"=>18,"align"=>"R"],
                			],
                    	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	$isfirst = true;
	foreach( $data as $ID_MAGASIN => $dataMag){
	    if(count($dataMag["lignes"]) > 0){
    		if (!$isfirst){
    			$rapport->Ln(6);
    		}
    		$isfirst = false;
    		
    		if( count($listID_MAGASINcanaccess) > 1 ){
    			$rapport->SetFont('helvetica', 'B', 12);
    			$rapport->Cell(0, 0, $allMag[$ID_MAGASIN]["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
    		}
    		foreach ( $dataMag["lignes"] as $uneLigne){
    			$listChamps = [];
    			$listChamps[] = date("Y-m-d H:i:s",strtotime($uneLigne["date_insert"]));
    			$listChamps[] = $uneLigne["id_facture"];
    			$listChamps[] = nfs($uneLigne["soustotal"]);
    			
    			$txt = [];
    		    foreach($uneLigne["paiement"]["lignes"] as $unPaiement){
    			    $txt[] = $unPaiement["label"];
    		    }
    		    $listChamps[] = implode("\n",$txt);
    		    
    			$listChamps[] = $uneLigne["notes"];
    			$listChamps[] = $uneLigne["num_avantages"];
    			
    			$txt = [];
    		    foreach($uneLigne["points"]["lignes"] as $unPaiement){
    			    $txt[] = nfs($unPaiement["points"]);
    		    }
    		    $listChamps[] = implode("\n",$txt);
    	
    			$rapport->writeLigneRapport3wrap( $listChamps );
    		}
    		$rapport->writeLigneGrandTotal( [ $allMag[$ID_MAGASIN]["M_NOM"],$dataMag["nbrefact"],nfs($dataMag["soustotal"]),null,null,null,nfs($dataMag["totalpoints"])], [false,true,true,false,false,false,true] );
	    }
	}
	
	
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
				<span class="hidden-xs-down"><?= L("rap_facture","o");?>
				</span>
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
		<div class="print-only">
			<div class="px-3">
				<h5>Animo etc <?= $allMag[$ID_MAGASIN]['M_NOM'] ?></h5>
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
												<div class="row pb-2" >
													<div class="col-md-8">
														<div class="input-group bs-datepicker input-daterange picker-range">
															<input type="text" class="form-control" name="from" id="from" value="<?= empty(htmlentities($_GET["from"])) ? date("Y-m-d", strtotime(date("Y-m-d"). ' - 1 days')):htmlentities($_GET["from"])?>">
															<span class="input-group-addon px-3"><?= L("to"); ?></span>
															<input type="text" class="form-control" name="to" id="to" value="<?= empty(htmlentities($_GET["to"])) ? date("Y-m-d") : htmlentities($_GET["to"])?>">
														</div>
													</div>
												    <div class="col-md-4">
												        <div class="form-group input-group">
															<input type="text" class="form-control" name="rangeFacture" id="rangeFacture" placeholder="# de facture (accepte x-y et x,y)" value="<?php echo $_GET['rangeFacture']?>">
														</div>
												    </div>
													<?php 
													if( count($listID_MAGASINcanaccess) > 1 )
													{
													?>
    													<div class="col-md-12">
    														<div>
    															<select class="ui fluid normal multi-selection select-dropdown form-control" name="ID_MAGASIN[]" multiple>
    																<?php
    																foreach( $listID_MAGASINcanaccess as $ID_MAGASIN){
    																    if(!INDEV and $allMag[$ID_MAGASIN]["RESERVED_DEV"] == 1){
    																        continue;
    																    }
    																	$infoMag = query("select * from MAGASIN where ID_MAGASIN = ?",[$ID_MAGASIN,],$mysqli)->fetch_assoc();
    																	printf("<option value='%s'%s>%s</option>", $ID_MAGASIN,( in_array($ID_MAGASIN,$listID_MAGASIN)?" selected":""),$infoMag["M_NOM"]);
    																} ?>
    															</select>
    															<button class="btn btn-xs" onclick="$(this).closest('div').find('.multi-selection').selectDropdown('set selected', <?= str_replace('"', "'",json_encode(array_map(strval,$listID_MAGASINcanaccess))) ?> )" type="button"><?= L("tous sélectionner") ?></button>
    															<button class="btn btn-xs" onclick="$(this).closest('div').find('.multi-selection').selectDropdown('clear')" type="button"><?= L("tous dé-sélectionner") ?></button>
    														</div>
    													</div>
													<?php 
													}
													?>
													<div class="col-md-12 text-right">
														<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('afficher');?></button>
													</div>
												</div>
											</form>
										</div>
										<?php
										if ( $data ){
											foreach( $data as $ID_MAGASIN=>$dataMag  ) { 
												if(count($dataMag["lignes"]) > 0){
												?>
    												<h3><?php echo $allMag[$ID_MAGASIN]["M_NOM"] ?></h3>
    												<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
    													<div class="fixed-table-header" style="display: none;">
    														<table></table>
    													</div>
    													<div class="fixed-table-body" style="min-height: 200px;">
    														<table id="tableListToilettage" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
        														<thead>
        															<tr>
                    													<th>
                    														<a href="index.php?<?= rebuildQueryString(['orderby'=>'date_insert','sens'=>($_GET["orderby"] == 'date_insert' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                    														   <?= L("date") ?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'date_insert' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
                														    </a>
                    													</th>
                    													<th style="text-align:center">
                    														<a href="index.php?<?= rebuildQueryString(['orderby'=>'id_facture','sens'=>($_GET["orderby"] == 'id_facture' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                    														   <?= L("no_facture","o") ?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'id_facture' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
                														    </a>
                    													</th>
        																<th>
        																    <?= L("mode_paiement","o") ?>
    																    </th>
                    													<th style="text-align:right">
                    														<a href="index.php?<?= rebuildQueryString(['orderby'=>'soustotal','sens'=>($_GET["orderby"] == 'soustotal' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                    														   <?= L("totalsanstaxes") ?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'soustotal' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
                														    </a>
                    													</th>
        																<th style="text-align:center">
        																    <?= L("notes") ?>
    																    </th>
                    													<th style="text-align:">
                    														<a href="index.php?<?= rebuildQueryString(['orderby'=>'num_avantages','sens'=>($_GET["orderby"] == 'num_avantages' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                    														   <?= L("carteanimo") ?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'num_avantages' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
                														    </a>
                    													</th>
                    													<th>
        														            $ Animo 
                    													</th>
        											                </tr>
        														</thead>
        														<tbody>
        															<?php
        															$count = 0;
        															foreach ($dataMag["lignes"] as $rowRapport) 
        															{ ?>
        																
        																<tr>
        																	<td>
        																	    <?= date("Y-m-d H:i:s",strtotime($rowRapport["date_insert"])) ?>
    																	    </td>
        																	<td style="text-align:center">
        																	    <a href="javascript:;" class="ajaxModal" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?= $rowRapport["id_facture"]?>&ID_MAGASIN=<?= $ID_MAGASIN?>"><?= $rowRapport["id_facture"]?></a>
    																	    </td>
        																	<td>
            																	<?php
            																	foreach($rowRapport["paiement"]["lignes"] as $unPaiement){
            																		echo $unPaiement["label"]."<br />";
            																	}
            																	?>
        																	</td>
        																	<td style="text-align:right">
        																	    <?= formatPrix($rowRapport["soustotal"])?>
    																	    </td>
        																	<td>
        																	    <?= $rowRapport["notes"]?>
    																	    </td>
        																	<td>
        																	    <a href="?p=list_clients&carte=<?= $rowRapport["num_avantages"]?>"><?= $rowRapport["num_avantages"]?></a>
    																	    </td>
        																	<td style="text-align:right">
            																	<?php
            																	foreach($rowRapport["points"]["lignes"] as $unPaiement){
            																		echo formatPrix($unPaiement["points"])."<br />";
            																	}
            																	?>
        																	</td>
        																</tr>
    																<?php
        											} //fin foreach
        															?>
            													</tbody>
            													<tfoot>
            														<tr style="font-weight:bold">
            															<td></td>
            															<td></td>
            															<td></td>
            															<td style="text-align:right"><?= formatPrix($dataMag["soustotal"])?></td>
            															<td></td>
            															<td></td>
            															<td style="text-align:right"><?= formatPrix($dataMag["totalpoints"])?></td>
            														</tr>
            													</tfoot>
    													    </table>
    													</div>
    												</div>
												<?php
												}
											}
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