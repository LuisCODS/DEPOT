<?php 


//echo '<pre> ', print_r($_REQUEST) , '</pre>';

// =================================== GESTION form pour  PDF et Excel ======================
if ( isset($_GET["getFile"]) &&  $_GET["getFile"] == "1"  ){
    
    // ===========================================  GESTION TRI  1  =========================================== 
    $listColonneTri = ['prix_change.change_date_exp,prix_change.id_article,prix_change.id_prix_change','prix_change.change_date_exp',
    					'article.PLU','article.desc_fr','prix_change.id_prix_change','prix_change.id_article'];
    					
    if ( !in_array($_GET["order"],$listColonneTri) ){
    	$_GET["order"] = $listColonneTri[0];
    }
    if($_GET['sens']==''){
        $sens = 'asc';
    }else{
        $sens = $_GET['sens'];
    }
    $orderByList = [];
    foreach( explode(',',$_GET['order']) as $champ ){
    	$orderByList[] = $champ . " " . $sens;
    }
    $orderBy = implode(', ',array_filter($orderByList));
    
    $listAND = [];
    $listVALUE= [];
    
    if($_REQUEST["nostock"] == "1"){
        
    }else{
    	$listAND[] = "article.stock > 0";
    }
    
    if (sizeof($listAND) == 0){ 
        $listAND[] = "1=1";
        unset($_SESSION["product_search"]);
    }
    
    $and = implode(' and ', $listAND);
    
    $enonce = "SELECT prix_change.*, article.stock, article.PLU, article.desc_fr as ARTICLE_NOM, article_desc.poid, 
    				  prix.prix as OLDPRICE, prix_change.prix as NEWPRICE 
    			 FROM prix_change
    				  INNER JOIN article USING(id_article)
    				  left JOIN prix USING(id_article)
    				  left JOIN animoetc_caisse_default.article_desc USING(id_article)
    			WHERE prix_change.change_accepted IS NULL AND prix_change.change_refused is null AND $and
    		    ORDER BY $orderBy";
    $query_limit = query($enonce,$listVALUE,$dbAnimoCaisse);
    $data = [];
    while ($rowRaport = $query_limit->fetch_assoc()) {
        $data[] =  $rowRaport;
    }
    // =================================== GESTION  PDF et Excel ======================    
	require_once(__DIR__."/../req/print.php"); 
	
	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}
	//NIVEAU 1 DU FICHIER: titre
	$titre = L("changement de prix","o");
    //NIVEAU 2 DU FICHIER: label 
    $listSoustitreFichier = [];
    //$listSoustitreFichier[] = ["Date :", "tous" ];
    //NIVEAU 3 DU FICHIER: les colonnes
	$listEnteteColonne = [
		[
			["text"=>L('date d\'entrée en vigueur',"o"),"width"=>40,"align"=>"L"],
			["text"=>L('PLU',"o"),"width"=>25,"align"=>"L"],
			["text"=>L("description","o"),"width"=>25,"align"=>"L"],
			["text"=>L('poids',"o"),"width"=>25,"align"=>"C"],
			["text"=>L('En stock',"o"),"width"=>25,"align"=>"C"],
			["text"=>L('Prix courant',"o"),"width"=>25,"align"=>"R"],
			["text"=>L('Nouveau prix',"o"),"width"=>25,"align"=>"R"],
		]
	];
	// TJRS UTILISER debutSection3, PAS 1 ni 2!									
	$rapport->debutSection3($titre,$listSoustitreFichier,$listEnteteColonne);
	//@parm: Prend les config de l'indice du tableau souhaité
	$rapport->setInfoCols(0);
	$isfirst = true;
	if(count($data) > 0){
	    //SI choix d'afficher les produits en rupture de stock
	    if( $_GET["nostock"] != "" ){
			foreach($data as $rowRaport){
			    $listChamps = [];
				if (!$isfirst){
					$rapport->Ln(1);
				}
				$isfirst = false;
				$rapport->SetFont('helvetica', 'B', 9);
				
				$listChamps[] = formatDateUTF8nonHTML($rowRaport["change_date_exp"]);
				$listChamps[] = $rowRaport["PLU"];
				$listChamps[] = $rowRaport["ARTICLE_NOM"];
				$listChamps[] = $rowRaport['poid'] !='' ?  setPoid($rowRaport['poid']) : "-";
				$listChamps[] = $rowRaport["stock"];
				$listChamps[] = formatPrix($rowRaport["OLDPRICE"]);
				$listChamps[] = formatPrix($rowRaport["NEWPRICE"]);
				$rapport->writeLigneRapport3wrap( $listChamps );
			}	    
	    }else{
			foreach($data as $rowRaport){
			    $listChamps = [];
				if (!$isfirst){
					$rapport->Ln(1);
				}
				$isfirst = false;
				$rapport->SetFont('helvetica', 'B', 9);
				
				$listChamps[] = formatDateUTF8nonHTML($rowRaport["change_date_exp"]);
				$listChamps[] = $rowRaport["PLU"];
				$listChamps[] = $rowRaport["ARTICLE_NOM"];
				$listChamps[] = $rowRaport['poid'] !='' ?  setPoid($rowRaport['poid']) : "-";
				$listChamps[] = $rowRaport["stock"];
				$listChamps[] = formatPrix($rowRaport["OLDPRICE"]);
				$listChamps[] = formatPrix($rowRaport["NEWPRICE"]);
				$rapport->writeLigneRapport3wrap( $listChamps );
			}
	    }
	}
	ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");
}

?>
<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("changement de prix","o");?></span>
		</h1>
		<h1 id="date_label" class="page-title pull-right fs-4 fw-light print-only"></h1>
		<? /* ICONS PDF & EXCEL*/?>
		<div class="smart-links no-print">
			<ul class="nav" role="tablist">
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" href="javascript:;"   onclick="envoyerFormToRapporFormat('pdf');" >
						<i class="fa fa-file-pdf-o "></i>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" href="javascript:;"   onclick="envoyerFormToRapporFormat('xlsx');">
						<i class="fa fa-file-excel-o "></i>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="print-only">
		<div class="px-3">
			<h5>Animo etc <?= getInfoMag("succursale")?></h5>
		</div>
	</div>
	<?php msg_output("<h4>{$L['ATTENTION']}</h4>{$L['completechangeprice']}","warning", 0, 15)?>
	<!-- Start Content-->
	<div class="row">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<div class="panel bgc-white-dark transition visible pb-2">
				<div class="panel-body panel-body-p">
					<div class="page-size-table">
						<div class="bootstrap-table">
						    <? /* ===========================================  FORM 1  =========================================== */?>
							<div class="fixed-table-toolbar">
							    <!-- onsubmit: Exécuté lorsque le form est soumis  -->
							    <form action="ajax/price_change" onsubmit="return sendFormAjax(this)" id="formListProduit">
    								<input type="hidden" value="50"     name="limit" />
    								<input type="hidden" value="1"      name="pageNum" />
    								<input type="hidden" value="prix_change.change_date_exp,prix_change.id_article,prix_change.id_prix_change" name="order" />
    								<input type="hidden" value="asc"    name="sens" />
    								<div class="row no-print">
    									<div class="col-md-12">
    										<div class="ui dynamic checkbox">
    										    <!-- lorsque checked checkbox envoie le form -->
    											<input onchange='sendFormAjax( getEl("formListProduit") );' type="checkbox" name="nostock" value="1" >
    											<label>Afficher également produits en rupture de stock</label>
    										</div>
    									</div>
    									<div class="col-md-12 text-right">
    									    <? /*  ============== LIEN CHANGER ... ======= */?>
    										<a class="btn btn-warning btn" href="javascript:;" onclick="submitPCNS()" >
    											<i class="fa fa-check fa-2x" style="color:green;font-size:1.4em"></i> 
    											<?= ( $_SESSION["brand"] != "animo" )?L("Accepté tous les prix des produits en rupture de stock"): $L['changeallnostock']; ?>
    										</a>
    										<? /*  ============== LIEN COMPLETER ... ======= */?>
    										<?php if ( $_SESSION["brand"] != "animo" ){
    											?>
    											<a class="btn btn-warning btn" href="javascript:;" onclick="submitPCNS({'refuse':'1'})">
    												<i class="fa fa-close" style="color:red;font-size:1.4em"></i> 
    												<?= L("Refuser tous les prix des produits en rupture de stock"); ?>
    											</a>
    											<?php 
    										}?>
    									</div>
    									<div class="col-md-12 text-right">
    										<a class="btn btn-success" href="javascript:;" style="margin:10px 0 20px;" onclick="$('#prcChange').attr('data-mode','accept').trigger('submit');">
    											<i class="fa fa-check" style="color:green;font-size:1.4em"></i> 
    											<?= ( $_SESSION["brand"] != "animo" )?L("Accepté les changements des prix cochés"):$L['completechecked']; ?>
    										</a>
    										<?php if ( $_SESSION["brand"] != "animo" ){
    											?>
    											<a class="btn btn-success" href="javascript:;" style="margin:10px 0 20px;" onclick="$('#prcChange').attr('data-mode','refuse').trigger('submit');">
    												<i class="fa fa-close" style="color:red;font-size:1.4em"></i> 
    												<?= L("Refusé les changements de prix cochés") ?>
    											</a>
    											<?php 
    										}?>
    									</div>
    								</div>
							    </form>
							</div>
							
							<? /* ==================================  FORM 3 pour le rapport =============================== 
							    Ce form va cherche certaines valeurs envoyés par le form 1 
							*/?>
						    <form  id="formToRapport" target="_blank" >
						        <input type="hidden" value="<?= $_GET["p"]?>" name="p" >
								<input type="hidden" value="prix_change.change_date_exp,prix_change.id_article,prix_change.id_prix_change" name="order" />
								<input type="hidden" value=""       name="sens" />
								<input type="hidden" value=""       name="format"  />
								<input type="hidden" value="1"      name="getFile" />
								<input type="hidden" value=""       name="nostock" >
							</form>
							<? /* ================script=============== */?>
							<script language='javascript'>
							    function envoyerFormToRapporFormat(formatRapport){
                                    var formToRapport = document.getElementById('formToRapport');
                                    <? // Verifie si le checkbox est cochés ?>
                                    if (getEl("formListProduit").nostock.checked ){
                                        formToRapport.nostock.value = "1";
                                    }else{
                                        formToRapport.nostock.value = "";
                                    }
                                    <? //Set le format du rapport ?>
                                    formToRapport.format.value = formatRapport;
                                    <? //Set le sens tel que sur le form 1 ?>
                                    formToRapport.sens.value = document.getElementById('formListProduit').sens.value;
                                    formToRapport.submit();
                                    //console.log(formToRapport);
							    }
                            </script>
                            
                            <? /* ===========================================  FORM 2 =========================================== */?>
                            <? // onsubmit: se produit lorsqu'un formulaire est soumis.  ?>
							<form method="POST" id="prcChange" onsubmit="submitPC(this,event);return false;">
								<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
									<div class="fixed-table-header" style="display: none;">
										<table></table>
									</div>
									<div class="fixed-table-body">
										<table id="tableListProduit" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
											<thead>
												<tr>
													<th valign="middle" style="text-align:center;" align="center" class="chkall_boxes no-print">
														<span class="ui dynamic checkbox my-2 mb-1">
															<input type="checkbox"/>
														</span>
													</th>
													<th class="sortable no-print" data-next-orderby="prix_change.change_date_exp" data-next-ordersens="asc" ><?= L("Date d'entrée en vigueur") ?></th>
													<th class="sortable" data-next-orderby="article.PLU" data-next-ordersens="asc"><?= L("plu") ?></th>
													<th class="sortable" data-next-orderby="article.desc_fr" data-next-ordersens="asc"><?= L("acdesc") ?></th>
													<th>
														<?= L("poids","o");?>
													</th>
													<th style="text-align:center">
														<?php echo $L["Enstock"];?>
													</th>
													<th style="text-align:right">
														<?php echo $L["oldprice"];?>
													</th>
													<th style="text-align:right">
														<?php echo $L["newprice"];?>
													</th>
												</tr>
											</thead>
											<tbody id="listProduits">

											</tbody>
										</table>
									</div>
									<div class="fixed-table-footer" style="display: none;"><table><tbody><tr></tr></tbody></table></div>
									<div class="fixed-table-pagination" style="">
										<div class="pull-left pagination-detail">
											<span class="pagination-info"><?php /*Showing 1 to 12 of 60 rows*/?></span>
											<span class="page-list">
												<?php /*
												Remplacer par un select

												<span class="btn-group dropup">
													<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
														<span class="page-size">12</span>
														<span class="caret"></span>
													</button>
													<ul class="dropdown-menu" role="menu">
														<li role="menuitem"><a href="#">All</a></li>
														<li role="menuitem"><a href="#">30</a></li>
														<li role="menuitem"><a href="#">20</a></li>
														<li role="menuitem" class="active"><a href="#">12</a></li>
													</ul>
												</span>
												rows per page
												*/ ?>
											</span>
										</div>
										<div class="pull-right pagination">
											<ul class="pagination">
												<?php /*
												<li class="page-pre"><a href="#">‹</a></li>
												<li class="page-number active"><a href="#">1</a></li>
												<li class="page-number"><a href="#">2</a></li>
												<li class="page-number"><a href="#">3</a></li>
												<li class="page-number"><a href="#">4</a></li>
												<li class="page-number"><a href="#">5</a></li>
												<li class="page-next"><a href="#">›</a></li>
												*/ ?>
											</ul>
										</div>
									</div>
								</div>
								<div class="col-md-12 text-right">
								    <? // Lors du click, set l'attibut data-mode = accept et declanche la soumission du form (this) ?>
									<a class="btn btn-success" href="javascript:;" style="margin:10px 0 20px;" onclick="$('#prcChange').attr('data-mode','accept').trigger('submit');">
										<i class="fa fa-check" style="color:green;font-size:1.4em"></i> 
										<?= ( $_SESSION["brand"] != "animo" )?L("Accepté les changements des prix cochés"):$L['completechecked']; ?>
									</a>
									<?php if ( $_SESSION["brand"] != "animo" ){
										?>
										<? // Lors du click, set l'attibut data-mode = refuse et declanche la soumission du form (this) ?>
										<a class="btn btn-success" href="javascript:;" style="margin:10px 0 20px;" onclick="$('#prcChange').attr('data-mode','refuse').trigger('submit');">
											<i class="fa fa-close" style="color:red;font-size:1.4em"></i> 
											<?= L("Refusé les changements de prix cochés") ?>
										</a>
										<?php 
									}?>
								</div>
								<input name="form_PC" type="hidden" value="sendok" />
							</form>
							
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	<!-- End Content-->
</section>



