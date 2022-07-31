<?php
ini_set("memory_limit","256M");
set_time_limit(300);

$listAND = [];
$listPARM = [];
$listAND[] = "ID_MAGASIN = ?";
//Get ID magasin
$listPARM[] = $_SESSION['mag'];
$addLeftJoin = "
JOIN animoetc_caisse_default.groupe_fidele USING(id_groupe_fidele)
JOIN animoetc_caisse_default.groupe USING(id_groupe)
";

$CURRENT_MARQUE = null;

// ========================= GESTION MARQUE ================================
if(preg_match('#^\d+$#',$_GET['id_marques']))
{
    $addLeftJoin .= "
                JOIN animoetc_caisse_default.link_article_groupe USING(id_groupe)
                JOIN animoetc_caisse_default.article_desc USING(id_article)
    ";
    $listAND[] = "animoetc_caisse_default.article_desc.marque = ?";
    $listPARM[] = $_GET['id_marques'];
    
    $queryMark = "SELECT nom FROM marques where id_marques = ? limit 1";
    $getMark = query($queryMark,[$_GET['id_marques']],$mysqli);
    $CURRENT_MARQUE = $getMark->fetch_row()[0];
}

$CURRENT_FOUR = null;

// ========================= CHOIX SELECT  =================================
if(preg_match('#^\d+$#',$_GET['id_fournisseur']))
{
    $listAND[] = "animoetc_caisse_default.groupe_fidele.id_fournisseur = ?";
    $listPARM[] = $_GET['id_fournisseur'];

    $queryMark = "SELECT nom FROM fournisseur where id_fournisseur = ? limit 1";
    $getMark = query($queryMark,[$_GET['id_fournisseur']],$dbAnimoCaisse);
    $CURRENT_FOUR = $getMark->fetch_row()[0];
}

$and = implode(' and ',$listAND);// vex($and); ID_MAGASIN = ?

$querygratuit = "SELECT achat_fidele_gratuit.*, groupe.nom 
                FROM achat_fidele_gratuit
                $addLeftJoin
                WHERE $and
                AND id_groupe_fidele >= 0 and groupe.inactif is null and groupe_fidele.inactif is null
                GROUP BY achat_fidele_gratuit.id_achat_fidele_gratuit";
$resultgratuit = query($querygratuit,$listPARM,$mysqli);

$arrayData = [];

while ($rowgratuit = $resultgratuit->fetch_assoc())
{
	$arrayData[] = $rowgratuit;
}


// ======================   GESTION TRI  =================================

$listCononneTri = ['nom','date_insert','id_facture'];

if(in_array($_GET['order'],$listCononneTri)){
    $order = $mysqli->real_escape_string($_GET['order']); 
}else{
    $order = 'achat_fidele_gratuit.date_insert';
}
if($_GET['sens']==''){
    $sens = 'desc';
}else{ 
    $sens = $mysqli->real_escape_string($_GET['sens']);
}

usort($arrayData, function($a,$b)
{
    if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
    	return ($_GET["sens"] == "desc") ? 1 : -1;//1 c plus petit, -1 plus grand 
    } elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
    	return ($_GET["sens"] == "desc") ? -1 : 1;
    }
    return 0;
}); 

//  ============== GESTION PDF/EXCEL  =================

if ( $_GET["getFile"] == "1" and $arrayData )
{
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}
   
	$titre = L("rap_fidelite","o");

	$listSoustitre = [];
	$listSoustitre[] = ["En date du", date("Y-m-d") ];
	
	if($CURRENT_MARQUE){
	    $listSoustitre[] = ["Marque", $CURRENT_MARQUE];
	}
	if($CURRENT_FOUR){
	    $listSoustitre[] = ["Fournisseur", $CURRENT_FOUR];
	}

	$listEnteteColonne = [
                    		[ 
                        	    ["text"=>L('Programme Fidélité',"o"),"width"=>80,"align"=>"L"],
                        		["text"=>L('Date',"o"),"width"=>50,"align"=>"L"],
                        		["text"=>L('Facture',"o"),"width"=>35,"align"=>"L"]
                	    	],
                    	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);
	
	foreach ($arrayData as $uneLigne)
	{
		$listChamps = [];
		$listChamps[] = $uneLigne["nom"];
		$listChamps[] = formatDateUTF8nonHTML($uneLigne["date_insert"]);
		$listChamps[] = $uneLigne["id_facture"];

		$rapport->writeLigneRapport3wrap( $listChamps );
	}
	ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");
} else { ?>

<section id="main" class="main-wrap bgc-white-darkest print" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("rapportfidelite","o");?><?php if($CURRENT_MARQUE){?> pour <?php echo $CURRENT_MARQUE; }?></span>
		</h1>
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
			<!--Start Panel-->
			<div class="panel c-white-dark pb-0">
				<div class="panel-body">
					<div class="panel bgc-white-dark transition visible">
						<div class="panel-body panel-body-p">
							<div class="page-size-table">
								<div class="bootstrap-table">
									<div class="fixed-table-toolbar no-print">
								        <!-- ================= FORM  ================= -->
										<form method="get" >
											<input type="hidden" name="p" value="<?= $_GET["p"]?>">
											<div class="row" style="margin-bottom:15px;">
											    <!-- ================= SELECT ================= -->
												<div class="col-md-8">
													<div class="form-group input-group">
                                            			<select class="form-control"  name="id_fournisseur">
                                            				<option value="">Tous les fournisseurs</option>
                                        						<div>
                                        							<?php
                                            						$queryFour = "SELECT fournisseur.id_fournisseur,fournisseur.nom 
                                            						                FROM fournisseur 
                                                                                    join groupe_fidele using(id_fournisseur) 
                                                                                    join groupe using(id_groupe)
                                                                                    where groupe.inactif is null and groupe.type = 'fidelite' and groupe_fidele.inactif is null and fournisseur.inactif is null and fournisseur.est_distributeur = 1
                                                                                    group by fournisseur.id_fournisseur
                                                                                    order by nom ASC";
                                        							$resulFour = $dbAnimoCaisseDefault->query($queryFour);
                                        							while ($rowFour = $resulFour->fetch_assoc()) 
                                        							{   
                                        								printf("<option value='%s'%s>%s</option>", $rowFour["id_fournisseur"], ($rowFour["id_fournisseur"] == $_GET["id_fournisseur"] ? "selected" : ""), $rowFour["nom"]);
                                        						    }
                                        						  ?>
                                        						</div>
                                            			</select>
													</div>
												</div>
												<!-- ================= Button ================= -->
												<div class="col-md-4 text-right">
													<button type="submit" class="applyBtn btn btn-small btn-success" id="btn_submit"><?= L('genererrapport');?></button>
												</div>
											</div>
										</form>
									</div>
									<!-- ======================================== TABLE ========================================= -->
									<div class="row">
										<div class="table-responsive">
											<table class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
												<thead>
													<tr>
														<th>
			    											<a href="index.php?<?= rebuildQueryString(['orderby'=>'nom','sens'=>($_GET["orderby"] == 'nom' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    														    <?= L('Programme Fidélité')?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nom' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
														    </a>
														</th>
														<th>
			    											<a href="index.php?<?= rebuildQueryString(['orderby'=>'date_insert','sens'=>($_GET["orderby"] == 'date_insert' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    														    <?= Date ?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'date_insert' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
														    </a>
														</th>
														<th>
			    											<a href="index.php?<?= rebuildQueryString(['orderby'=>'id_facture','sens'=>($_GET["orderby"] == 'id_facture' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    														    <?= Facture ?> <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'id_facture' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
														    </a>
														</th>
													</tr>
												</thead>
												<tbody>
													<?php
													if(count($arrayData) > 0)
													{
														foreach($arrayData as $rowRaport){?>
															<tr>
																<td>
																	<?= $rowRaport["nom"]; ?>
																</td>
																<td>
                                    						        <?= formatDateUTF8($rowRaport["date_insert"]) ?>  
																</td>
																<td>
																	<a class="ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?= $rowRaport["id_facture"]?>&ID_MAGASIN=<?= $rowRaport['ID_MAGASIN']?>">
																	    <?=  $rowRaport["id_facture"];?>
																    </a>
																</td>
																<td>
                                    						        <a class="text-right" href="index.php?p=rap_fidelite_details&id_achat_fidele_gratuit=<?= $rowRaport["id_achat_fidele_gratuit"]?>&id_groupe_fidele=<?= $rowRaport["id_groupe_fidele"]?>">
                                    						            <i class='fa fa-print'></i>
                                						            </a>
																</td>
															</tr>
														<?php
														}
													}else{?>
														<tr>
															<td colspan="7">
																Aucune donnée
															</td>
														</tr>
													<?php }?>
												</tbody>
											</table>
										</div>
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




<?php }?>