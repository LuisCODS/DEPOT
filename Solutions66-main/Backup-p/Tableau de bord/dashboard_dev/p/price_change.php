<?php 
/*
if ( $_SESSION["brand"] != "animo" ){
	redirect("index.php?p=price_manage");
}
*/
?>

<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-tags icon-mr fs-4"></i>
			<span class="hidden-xs-down"> <?php echo $L['Changementprix'];?></span>
		</h1>
	</div>
	<?php msg_output("<h4>{$L['ATTENTION']}</h4>
							{$L['completechangeprice']}
						","warning", 0, 15)?>
	<!-- Start Content-->
	<div class="row">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<div class="panel bgc-white-dark transition visible pb-2">
				<div class="panel-body panel-body-p">
					<div class="page-size-table">
						<div class="bootstrap-table">
							<div class="fixed-table-toolbar">
								<form action="ajax/price_change" onsubmit="return sendFormAjax(this)" id="formListProduit">
									<input type="hidden" value="50" name="limit" />
									<input type="hidden" value="1" name="pageNum" />
									<input type="hidden" value="prix_change.change_date_exp,prix_change.id_article,prix_change.id_prix_change" name="order" />
									<input type="hidden" value="asc" name="sens" />
									<div class="row no-print">
										<div class="col-md-12">
											<div class="ui dynamic checkbox">
												<input onchange='sendFormAjax( getEl("formListProduit") );' type="checkbox" name="nostock" value="1">
												<label>Afficher également produits en rupture de stock</label>
											</div>
										</div>
										<div class="col-md-12 text-right">
											<a class="btn btn-warning btn" href="javascript:;" onclick="submitPCNS()" >
												<i class="fa fa-check fa-2x" style="color:green;font-size:1.4em"></i> 
												<?= ( $_SESSION["brand"] != "animo" )?L("Accepté tous les prix des produits en rupture de stock"): $L['changeallnostock']; ?>
											</a>
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



