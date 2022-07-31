<?php
ini_set("memory_limit","512M");
set_time_limit(300);

if($_SESSION["utilisateur"]["security"] > 5)
{
    redirect('index.php');
}

$_GET["date"] = (preg_match("#\d{4,5}\-\d{2}\-\d{2}#",$_GET["date"]))?$_GET["date"]:date("Y-m-d");

$allMag = [];
$queryAllMag = query("select * from MAGASIN where caisse_db is not null order by M_NOM asc",[],$mysqli);
while( $uneLigneMag = $queryAllMag->fetch_assoc() )
{
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}

$listID_MAGASINcanaccess = [];
if ( $_SESSION["utilisateur"]["security"] >= 2 )
{
	$listID_MAGASINcanaccess = array_keys($_SESSION["magasins"]);
} 
else 
{
	$listID_MAGASINcanaccess = array_keys($allMag);
}

$listID_MAGASIN = [];
if ( isset($_GET["ID_MAGASIN"]) )
{
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) 
	{
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanaccess) )
		{
			$listID_MAGASIN[] = $ID_MAGASIN;
		}
	}
}

if ( sizeof($listID_MAGASIN) < 1 )
{
	$listID_MAGASIN = $listID_MAGASINcanaccess;
}

$listUserCanAccess = [];
foreach ( $listID_MAGASINcanaccess as $ID_MAGASIN )
{
	$enonce = "SELECT * FROM {$allMag[$ID_MAGASIN]["caisse_db"]}.utilisateur where inactif is null and (nom != '' or prenom != '') ORDER BY prenom, nom";
	$resultUser = query($enonce,[],$dbAnimoCaisse);
	while( $uneLigneUser = $resultUser->fetch_assoc() )
	{
		if ( !isset($listUserCanAccess[$uneLigneUser["id_intranet"]]) )
		{
			$listUserCanAccess[$uneLigneUser["id_intranet"]] = $uneLigneUser;
		}
	}
}

uasort( $listUserCanAccess, function($a,$b)
{
	if ( strtoupper($a["prenom"]) < strtoupper($b["prenom"]) )
	{
		return -1;
	} 
	elseif( strtoupper($a["prenom"]) > strtoupper($b["prenom"]) )
	{
		return 1;
	}
	if ( strtoupper($a["nom"]) < strtoupper($b["nom"]) )
	{
		return -1;
	} 
	elseif( strtoupper($a["nom"]) > strtoupper($b["nom"]) )
	{
		return 1;
	}
	return 0;
});

$data = [];

// Si  SOUMISSION DU FORM
if( $_GET["search"] == "1" )
{
    
	$listWhereAnd = [];
	
	if ( preg_match("#^\d+$#",$_GET["id_distributeur"]) )
	{
		$listWhereAnd[] = "link_article_four.id_fournisseur = " . $_GET["id_distributeur"];
	}
	if ( preg_match("#^\d+$#",$_GET["id_departement"]) )
	{
	    $listWhereAnd[] = "(article.id_departement = " . $_GET["id_departement"]."
                            and article.id_departement < 900 and article.id_departement NOT IN(1,8,9,10)
                            )";
	}
	$listWhereAnd[] = "article.hold_min is not null and article.hold_min > 0";
	$strWhereAnd = implode(" and ",$listWhereAnd);
	
	foreach ( $listID_MAGASIN as $ID_MAGASIN )
	{
		$resulThismagasin= $mysqli->query("SELECT * FROM MAGASIN WHERE ID_MAGASIN = '".$ID_MAGASIN."'");
		$rowThismagasin = $resulThismagasin->fetch_assoc();
		
		
		$articles_query = "select article.*, fournisseur.nom `fournisseur_nom`, link_article_four.num_four `num_four`, link_article_four.id_fournisseur
							 from {$rowThismagasin['caisse_db']}.article
								  left join {$rowThismagasin['caisse_db']}.link_article_four using(id_article)
								  left join {$rowThismagasin['caisse_db']}.fournisseur using(id_fournisseur)
							where article.inactif is NULL and $strWhereAnd and link_article_four.discontinued is null
						 GROUP BY article.id_article ";
		
		$resultArt= $dbAnimoCaisse->query($articles_query) or die($dbAnimoCaisse->error);
		
		$data[$ID_MAGASIN] = [
		                        "liste_fournisseur"=>[],
                        		"totalCost"=>0,
                        		"totalStock"=>0
                    		 ];
		
		while ($uneLigneArticle = $resultArt->fetch_assoc())
		{
			$uneLigneArticle["listPLU"] = [];
			if ( $uneLigneArticle["PLU"] ) $uneLigneArticle["listPLU"][] = $uneLigneArticle["PLU"];
			if ( $uneLigneArticle["PLU2"] ) $uneLigneArticle["listPLU"][] = $uneLigneArticle["PLU2"];
			if ( $uneLigneArticle["PLU3"] ) $uneLigneArticle["listPLU"][] = $uneLigneArticle["PLU3"];
		
			//if ( $uneLigneArticle["stock"] < 0 ){$uneLigneArticle["stock"] = 0;	}
			
			
			//$data["totalStock"] += $uneLigneArticle["stock"];
			if ( $uneLigneArticle["stock"] - $uneLigneArticle["hold_min"] > 0 )
			{
				$uneLigneArticle["a_commander"] = 0;
			} 
			else
			{
				$uneLigneArticle["a_commander"] = $uneLigneArticle["hold_min"] - $uneLigneArticle["stock"];
			}
			
			if ( !isset($data[$ID_MAGASIN]["liste_fournisseur"][$uneLigneArticle["id_fournisseur"]]) ){
				// A voir si besoin plus d'information sur le fournisseur
				$data[$ID_MAGASIN]["liste_fournisseur"][$uneLigneArticle["id_fournisseur"]] = [ 
                																				"liste_produits"=>[], 
                																				"nom"=>$uneLigneArticle["fournisseur_nom"], 
                																				"id_fournisseur"=>$uneLigneArticle["id_fournisseur"],
                																				"total_a_commander"=>0,
                																				"total_stock"=>0,
                																				"total_hold_min"=>0
            																				 ];
			}
			//aller chercher le distributeur
			$getDist = query('select fournisseur.nom,link_article_four.num_four 
			                 from fournisseur
                             join link_article_four using(id_fournisseur)
                             where est_fournisseur = 1 and id_article = ? and link_article_four.discontinued is null
                             order by prix_coutant asc
                            ',[$uneLigneArticle['id_article']],$dbAnimoCaisse);
			$uneLigneArticle['dists'] = [];
			if($getDist->num_rows > 0){
			    while($rowDist = $getDist->fetch_assoc()){
			        $uneLigneArticle['dists'][] = $rowDist;
			    }
			}
			
			$data[$uneLigneArticle["ID_MAGASIN"]]["liste_produits"][] = $uneLigneArticle;
			
			$data[$ID_MAGASIN]["liste_fournisseur"][$uneLigneArticle["id_fournisseur"]]["liste_produits"][] = $uneLigneArticle;
			$data[$ID_MAGASIN]["liste_fournisseur"][$uneLigneArticle["id_fournisseur"]]["total_a_commander"] += $uneLigneArticle["a_commander"];
			$data[$ID_MAGASIN]["liste_fournisseur"][$uneLigneArticle["id_fournisseur"]]["total_stock"] += $uneLigneArticle["stock"];
			$data[$ID_MAGASIN]["liste_fournisseur"][$uneLigneArticle["id_fournisseur"]]["total_hold_min"] += $uneLigneArticle["hold_min"];
		}
	}
	
    // ======================   GESTION TRI ================================	
    
    /*
    
    */
    $listColonneTri = ["desc_fr","stock","hold_min","a_commander"];
    
    if($_POST['order']!='') { 
        $order = $_POST['order'];
    }
    else{ 
        $order = $listColonneTri[0];
    }
    
    if($_POST['sens']=='') {
        $sens = 'asc';
    }
    else{ 
        $sens = $_POST['sens'];
    }
  
    ///On recupere chaque clés  des magasins
    foreach(array_keys($data) as $ID_MAGASIN)   
    {
        if(!empty($ID_MAGASIN))
        {
            //On recupere chaque clés fournisseur
            foreach(array_keys($data[$ID_MAGASIN]["liste_fournisseur"]) as $id_fournisseur)   
            {
                //Finalement, on accède le array liste_produits et on lui passe étant que paramentre à la fonction
                 usort(  $data[$ID_MAGASIN]["liste_fournisseur"][$id_fournisseur]["liste_produits"] , function($a,$b)
                 {
                	if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] )
                	{
                		return ($_GET["sens"] == "desc") ? 1 : -1;//1 c plus petit, -1 plus grand 
                	} 
                	elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] )
                	{
                		return ($_GET["sens"] == "desc") ? -1 : 1;
                	}
                	return 0;
                });  
            }           
        }
    } 
  
}//fin if form



if ( $_GET["getFile"] == "1" and $data ){
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		#$rapport = new RapportPDF( RPDF_PAGE_LANDSCAPE, RPDF_SHOWCOL_ALLPAGE );
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}

	$titre = L("rap_inventaire_quantitatif","o");

	$listSoustitre = [];
	$listSoustitre[] = ["en dates du", formatDateUTF8nonHTML( $_GET["date"] ) ];
	//$listSoustitre[] = ["devise",  $uneLigneTaxePaye["DEVISE_ABBR"] ];

	$listEnteteColonne = [
		[ 
			["text"=>"Nom du produit","width"=>90,"align"=>"L"], ["text"=>L("Distributeur(s)",'o'),"width"=>40,"align"=>"L"],
			["text"=>L("qté",'o'),"width"=>15,"align"=>"C"],["text"=>L("minimum",'o'),"width"=>15,"align"=>"C"],["text"=>L("à commander",'o'),"width"=>22,"align"=>"C"],
		],
	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);
	
	
	$rapport->SetFillColor( 245, 245, 245);
	
	//
	$isfirst = true;
	foreach ( $data as $ID_MAGASIN => $dataMag ){
		if (!$isfirst){
    		$rapport->Ln(2);
    	}
    	$isfirst = false;
    
    	if( sizeof($listID_MAGASINcanaccess) > 1 ){
    		$rapport->SetFont('helvetica', 'B', 12);
    		$rapport->Cell(0, 0, $allMag[$ID_MAGASIN]["M_NOM"], 0, 1, 'L', false, '', 0, false, 'T', 'B');
    	}
    	
    	if ( isset($dataMag["liste_fournisseur"]) ){
			foreach ( $dataMag["liste_fournisseur"] as $fournisseur ){
				$rapport->setAlterneBG(true);
				
				$rapport->SetFont('helvetica', 'B', 10);
				$rapport->Cell(0,0,$fournisseur["nom"],0,1);
				
				$rapport->SetFont('helvetica', '', 8);
				
				foreach ( $fournisseur["liste_produits"] as $uneLigneArticle){
					$listChamps = [];
					$listChamps[] = $uneLigneArticle["desc_fr"]." (".implode("|",$uneLigneArticle["listPLU"]).")";
			
					$arrayStrDist = [];
					if(count($uneLigneArticle['dists']) > 0){
					    foreach($uneLigneArticle['dists'] as $rowDist){
					        $arrayStrDist[] = $rowDist['nom'] . ': ' . $rowDist['num_four'];
					    }
					}
					$listChamps[] = implode("\n",$arrayStrDist);
					
					
					//$listChamps[] = $uneLigneArticle["num_four"];
			
					$listChamps[] = $uneLigneArticle["stock"];
					$listChamps[] = $uneLigneArticle["hold_min"];
					
					$listChamps[] = $uneLigneArticle["a_commander"];
			
					$rapport->writeLigneRapport3wrap( $listChamps, -1, true );
				}
				
				$listChamps = [];
				$listChamps[] = null;
				$listChamps[] = null;
				$listChamps[] = $fournisseur["total_stock"];
				$listChamps[] = $fournisseur["total_hold_min"];
				$listChamps[] = $fournisseur["total_a_commander"];
				$rapport->writeLigneTotaux($listChamps);
				
				$rapport->Ln();
			}
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
				<span class="hidden-xs-down"><?= L("rap_inventaire_quantitatif","o");?></span>
			</h1>
			<h1 id="date_label" class="page-title pull-right fs-4 fw-light print-only"></h1>
			<div class="smart-links no-print">
				<ul class="nav" role="tablist">
					<?php /*?><li class="nav-item">
						<a class="nav-link clear-style aside-trigger" onclick="window.print();" href="javascript:;">
							<i class="fa fa-print "></i>
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
												<input type="hidden" name="search" value="1">
												<input type="hidden" name="sens" value="<?= $_GET["sens"] ?>">
												<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">
												
												<div class="row" style="margin-bottom:15px;">
													<div class="col-md-3">
														<div class="form-group input-group">
															<select name="id_distributeur" class="form-control">
																<option value="">Tous les distributeurs</option>
																<?php
																$enonce = "SELECT * FROM fournisseur WHERE est_fournisseur IS NOT NULL ORDER BY nom";
																$resultFour = $dbAnimoCaisse->query($enonce);
																while($rowFour = $resultFour->fetch_assoc()){
																	printf("<option value='%s'%s>%s</option>", $rowFour["id_fournisseur"], ($rowFour["id_fournisseur"] == $_GET["id_distributeur"] ? " selected" : ""), $rowFour["nom"]);
																}
																?>
															</select>
														</div>
													</div>
													<div class="col-md-3">
														<div>
															<select class="form-control" name="id_departement">
																<option value="">Tous les départements</option>
																<?php 
																$getdep = query('select id_departement, nom from departement where id_departement < 900 and id_departement not in(1,8,9,10)',[],$dbAnimoCaisseDefault);
																while($rowdep = $getdep->fetch_assoc()){
																    printf('<option value="%s"%s>%s</option>',$rowdep['id_departement'],($rowdep['id_departement'] == $_GET['id_departement'] ? ' selected' : ''),$rowdep['nom']);
																}
																?>
															</select>
														</div>
													</div>
													<?php if( sizeof($listID_MAGASINcanaccess) > 1 ){?>
													<div class="col-md-6">
														<div>
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
										<?php 
										if ($data){
											//$data["liste_fournisseur"][$uneLigneArticle["id_fournisseur"]]["liste_produits"][] = $uneLigneArticle;
											foreach ( $data as $ID_MAGASIN => $dataMag ){
												?>
												<h3 class="">Animo Etc <?= $allMag[$ID_MAGASIN]['M_NOM']?></h3>
												<?php 
												if ( !$dataMag["liste_fournisseur"] ){
													?><div style="margin-bottom:40px;">Aucune donnée</div><?php 
												}
												foreach ( $dataMag["liste_fournisseur"] as $fournisseur ){  //vex($fournisseur); die();
													?>
													<h4><?= $fournisseur["nom"] ?></h4>
													<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
														<div class="fixed-table-body">
															<table id="tableListPointsAnimo" class="card-view-no-edit page-size-table table table-no-bordered">
															    <!-- =================================================================== thead ================================================================================================= -->
																<thead>
																	<tr>
				                                                        <th style="text-align:left">
            															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'desc_fr', 'sens'=>($_GET["orderby"] == 'desc_fr' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
            															         <?= L("articlenom","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'desc_fr' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            															    </a>
            														    </th> 
																		<th class="text-left" style="width:200px">
																			<div> <?= L('Distributeur(s)');?></div>
																			<div class="fht-cell"></div>
																		</th>
																		<th class="text-center" style="width:120px">
        	    														    <a href="index.php?<?= rebuildQueryString(['orderby'=>'stock', 'sens'=>($_GET["orderby"] == 'stock' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
        															         <?= L("stock","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'stock' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            															    </a>
            														    </th> 
																		<th class="text-center" style="width:120px">
        	    														    <a href="index.php?<?= rebuildQueryString(['orderby'=>'hold_min', 'sens'=>($_GET["orderby"] == 'hold_min' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
        															         <?= L("minimum","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'hold_min' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            															    </a>
            														    </th> 																		
																		<th class="text-center" style="width:120px">
        	    														    <a href="index.php?<?= rebuildQueryString(['orderby'=>'a_commander', 'sens'=>($_GET["orderby"] == 'a_commander' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
        															            <?= L("à commander","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'a_commander' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
            															    </a>
            														    </th> 
																	</tr>
																</thead>
																 <!-- ===================================================================  tbody =============================================================================================== -->
																<tbody >
																	<?php
																	foreach ( $fournisseur["liste_produits"] as $uneLigneArticle ){ ?>
																		<tr>
																			<td>
																				<a href="?p=produits&id=<?= $uneLigneArticle["id_article"]?>">
																				    <?= $uneLigneArticle["desc_fr"]."<br/><span class='no-print'>".implode("|",$uneLigneArticle["listPLU"])."</span>"  ?>
																				</a>
																			</td>
																			<td>
                    															<?php 
                    															if(count($uneLigneArticle['dists']) > 0){
                    															    foreach($uneLigneArticle['dists'] as $rowDist){
                    															        ?>
                    															        <div><?= $rowDist['nom']?>: <?= $rowDist['num_four']?></div>
                    															        <?php
                    															    }
                    															}
                    															?>
                    														</td>
																			<td class="center">
																				<?php
																				echo $uneLigneArticle["stock"];
																				?>
																			</td>
																			<td class="center">
																				<?php
																				echo $uneLigneArticle["hold_min"];
																				?>
																			</td>
																			<td class="center">
																				<?php
																				echo $uneLigneArticle["a_commander"];
																				?>
																			</td>
																		</tr>
																		<?php }
																	?>
																</tbody>
																<!-- =================================================================== tfoot =========================================== -->
																<tfoot>
																	<tr style="font-weight:bold">
																		<td style="text-align:left;"></td>
																		<td class="center"></td>
																		<td class="center"><?= $fournisseur["total_stock"] ?></td>
																		<td class="center"><?= $fournisseur["total_hold_min"] ?></td>
																		<td class="center"><?= $fournisseur["total_a_commander"] ?></td>
																	</tr>
																</tfoot>
															</table>
			
														</div>
			
														<div class="fixed-table-footer" style="display: none;"<table><tbody><tr></tr></tbody></table>
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
}?>