<?php
$_SESSION["product_search"] = [];
unset($_SESSION["product_search"]);

if((isset($_POST["link_article_groupe"])) && ($_POST["link_article_groupe"] == "5")) {
	$arrayDB["id_groupe"] = $_POST["id_groupe"];
	$arrayDB["id_article"] = $_POST['id_article'];
	$arrayDB["date_insert"] = date('Y-m-d H:i:s');
	$arrayDB["date_update"] = date('Y-m-d H:i:s');
	if($arrayDB["id_groupe"] !='' && $arrayDB["id_article"] !=''){
		faireInsert_i($arrayDB, "link_article_groupe", $dbAnimoCaisseDefault, 0);
	}
}
?>

<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-barcode icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?php echo L('listedesproduits',"o");?></span>
		</h1>
		<div class="smart-links no-print">
			<ul class="nav" role="tablist">
				<li class="nav-item">
					<a href="javascript:window.print();" class="nav-link clear-style">
						<i class="fa fa-print"></i>
					</a>
				</li>
				<li class="nav-item">
					<span class="dropdown">
						<a href="javascript:;" data-toggle="dropdown" class="nav-link clear-style">
							<i class="fa fa-ellipsis-v"></i>
						</a>
						<div class="dropdown-menu dropdown-menu-right">
							<div>
								<a class="dropdown-item with-icon" href="javascript:;" onclick="searchWeb(this)"><i class="fa fa-globe"></i> Produits Web seulement</a>
								<a class="dropdown-item with-icon" href="javascript:;" onclick="searchEnVedette(this)"><i class="fa fa-star"></i> Produits en vedette seulement</a>
								<a class="dropdown-item with-icon" href="javascript:;" onclick="searchIndispensable(this)"><i class="fa fa-info-circle"></i> Produits indispensables seulement</a>
								<?php if(has_rights("admin_article")){?>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item with-icon" href="javascript:;" onclick="searchDoublons('four','num_four')"><i class="fa fa-clone"></i> Afficher doublons par code four.</a>
								<a class="dropdown-item with-icon" href="javascript:;" onclick="searchDoublons('upc','plu')"><i class="fa fa-clone"></i> Afficher doublons par UPC</a>
								<?php }?>
							</div>
						</div>
					</span>
				</li>
				<?php if(has_rights("admin_article")){?>
				<li class="nav-item">
					<a href="?p=produits_edit" class="nav-link clear-style">
						<i class="fa fa-plus-circle"></i>
					</a>
				</li><?php }?>
			</ul>
		</div>
	</div>
	<!-- End SubHeader-->
	<!-- Start Content-->
	<div class="row">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<div class="panel bgc-white-dark" style="box-shadow: none">
				<div class="panel-body panel-body-p">
					<div class="page-size-table">

						<div class="bootstrap-table">
						    
							<div class="fixed-table-toolbar no-print">
								<form action="ajax/list_produits" onsubmit="return sendFormAjax(this)" id="formListProduit">
									<input type="hidden" value="10" name="limit" />
									<input type="hidden" value="1" name="pageNum" />
									<input type="hidden" value="asc" name="sens" />
									<input type="hidden" value="" name="doublons" />
									<input type="hidden" value="" name="webvalue"/>
									<input type="hidden" value="" name="enVedette"/>
									<input type="hidden" value="" name="isIndispensable"/>
									<input type="hidden" value="nomArticledesc" name="order" />
									<div class="row">
										<div class="col-md-6 col-lg-4 col-xl-2">
											<input value="<?= htmlspecialchars($_GET["id"]) ?: attrEncode($_SESSION["product_search"]["idsearch"])?>" type="text" class="form-control mb-1 mr-sm-2" placeholder="<?php echo L('articleid',"o"); ?>" name="idsearch">
										</div>
										<div class="col-md-6 col-lg-4 col-xl-2">
											<input value="<?= htmlspecialchars($_GET["four"]) ?: attrEncode($_SESSION["product_search"]["codeproduit"])?>" type="text" class="form-control mb-1 mr-sm-2" placeholder="<?php echo L('codefour',"o"); ?>" name="codeproduit">
										</div>
										<div class="col-md-6 col-lg-4 col-xl-2">
											<?php $getDistributeur = query('select nom, id_fournisseur from FOURNISSEURS where est_fournisseur = 1 order by nom asc',[],$mysqli);?>
											<select class="ui search simple-select select-dropdown fluid" name="distributeur">
												<option value="" disabled selected><?php echo L('distributeur',"o"); ?></option>
												<?php while ( $rowDistributeur = $getDistributeur->fetch_assoc()){?>
												<option <?php echo $rowDistributeur['id_fournisseur'] == $_REQUEST["distributeur"] ? "selected " : ""?>value="<?php echo $rowDistributeur['id_fournisseur']?>"><?php echo $rowDistributeur['nom']?></option>
												<?php }?>
											</select>
										</div>
										<div class="col-md-6 col-lg-4 col-xl-2">
											<?php $getFournisseur = query('select nom, id_fournisseur from FOURNISSEURS where est_distributeur = 1 order by nom asc',[],$mysqli);?>
											<select class="ui search simple-select select-dropdown fluid" name="fournisseur">
												<option value="" disabled selected><?php echo L('fournisseur',"o"); ?></option>
												<?php while ( $rowFournisseur = $getFournisseur->fetch_assoc()){?>
												<option <?php echo $rowFournisseur['id_fournisseur'] == $_REQUEST["fournisseur"] ? "selected " : ""?>value="<?php echo $rowFournisseur['id_fournisseur']?>"><?php echo $rowFournisseur['nom']?></option>
												<?php }?>
											</select>
										</div>
										<div class="col-md-6 col-lg-4 col-xl-2">
											<?php $getMarques = query('select id_marques, nom from marques order by nom asc',[],$mysqli);?>
											<select class="ui search simple-select select-dropdown fluid" name="marque">
												<option value="" disabled selected><?php echo L('marques',"o"); ?></option>
												<?php while ( $rowMarques = $getMarques->fetch_assoc()){?>
												<option <?php echo $rowMarques['id_marques'] == $_REQUEST["marque"] ? "selected " : ""?>value="<?php echo $rowMarques['id_marques']?>"><?php echo $rowMarques['nom']?></option>
												<?php }?>
											</select>
										</div>
										<div class="col-md-6 col-lg-4 col-xl-2">
											<input onclick="if(this.value.length > 11){this.value = '';}" value="<?= htmlspecialchars($_GET["upc"]) ?: attrEncode($_SESSION["product_search"]["codeupc"])?>" type="text" class="form-control mb-1 mr-sm-2" placeholder="<?php echo L('codeupc',"o"); ?>" name="codeupc">
										</div>
										<div class="col-md-6 col-lg-4 col-xl-2">
											<input value="<?= htmlspecialchars($_GET["keywrd"]) ?: attrEncode($_SESSION["product_search"]["keywrd"])?>" type="text" class="form-control mb-1 mr-sm-2" name="keywrd" placeholder="<?php echo L('keyword',"o"); ?>">
										</div>
										<div class="col-md-6 col-lg-4 col-xl-2">
											<div class="form-group">
												<label class="sr-only" for="rightUsernameFormInputGroup"><?php echo L('poidmin',"o"); ?></label>
												<div class="input-group mb-2 mr-sm-2 mb-sm-0">
													<input value="<?= attrEncode($_SESSION["product_search"]["poidmin"]) ?>" type="text" class="form-control" id="" name="poidmin" placeholder="<?php echo L('poidmin',"o"); ?>">
													<div class="input-group-addon p-0" id="poidmin_unit"></div>
												</div>
											</div>
										</div>
										<div class="col-md-6 col-lg-4 col-xl-2">
											<div class="form-group">
												<label class="sr-only" for="rightUsernameFormInputGroup"><?php echo L('poidmax',"o"); ?></label>
												<div class="input-group mb-2 mr-sm-2 mb-sm-0">
													<input value="<?= attrEncode($_SESSION["product_search"]["poidmax"]) ?>" type="text" class="form-control" id="" name="poidmax" placeholder="<?php echo L('poidmax',"o"); ?>">
													<div class="input-group-addon p-0" id="poidmax_unit"></div>
												</div>
											</div>
										</div>
										<div class="col-md-6 col-lg-4 col-xl-2">
											<div class="form-group">
												<div class="input-group mb-2 mr-sm-2 mb-sm-0">
													<label class="" >
														<input value="1" type="checkbox" name="showInactif" />
														<?php echo L('afficher inactifs',"o"); ?>
													</label>
												</div>
											</div>
										</div>
										<div class="col-md-12 col-lg-8 col-xl-4 text-right">
											<button class="btn btn-default" type="reset" name="refresh" aria-label="refresh" title="<?php echo L('refresh',"o"); ?>" onclick="$(this).closest('form').find('.ui.select-dropdown').dropdown('clear');" ><i class="icon-refresh"></i></button>
											<button type="submit" class="btn btn-primary"><?php echo L("soumettre","o") ?></button>
										</div>
									</div>
								</form>
							</div>
							
							<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
								<div class="fixed-table-header" style="display: none;">
									<table></table>
								</div>
								<div class="fixed-table-body">
									<div class="fixed-table-loading" style="top: 57px; display: none;">Loading, please wait...</div>
									<table id="tableListProduit" class="card-view-no-edit page-size-table table table-no-bordered table-condensed table-hover">
										<thead>
											<tr>
												<th class="detail" rowspan="1"><div class="fht-cell"></div></th>
												<th><div class="th-inner sortable both" data-orderby="id_article">ID</div><div class="fht-cell"></div></th>
												<th><div class="th-inner sortable both" data-orderby="nomArticledesc"><?php echo L('article',"o"); ?></div><div class="fht-cell"></div></th>
												<th class="text-center"><?php echo L('groupe',"o"); ?>(s)</th>
												<th class="text-center"><div class="th-inner sortable both" data-orderby="nom_four"><?php echo L('distributeur',"o"); ?></div><div class="fht-cell"></div></th>
												<?php if(has_rights("inventaire")){?>
												<th class="text-center">En <?php echo L('stock',"o"); ?></th>
												<th class="text-center">Stock minimum</th>
												<?php }?>
												<th class="text-right"><div class="th-inner sortable both" data-orderby="min_prix"><?php echo L('prix',"o"); ?></div><div class="fht-cell"></div></th>
												<th></th>
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
						</div>
						<div class="clearfix"></div>
					</div>
				</div>
			</div>
		</section>
	</div>




	<!-- End Content-->
</section>