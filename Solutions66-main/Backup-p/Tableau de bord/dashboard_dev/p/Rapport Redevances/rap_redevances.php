<?php
$listVALUE = [];
$listAND1 = [];
$listTotal1 =0;
$listAND2 = [];
$listTotal2 =0;
$listAND3 = [];
$listTotal3 =0;
?>
<section id="main" class="main-wrap bgc-white-darkest print" role="main">
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-bar-chart icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= L("rap_redevances","t");?></span>
		</h1>	
	</div>
	<div class="row pl-3 pr-3 mb-3 mt-3 print-top">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<div class="panel c-white-dark pb-0">
				<div class="panel-body">
					<div class="panel bgc-white-dark transition visible">
						<div class="panel-body panel-body-p">
							<div class="page-size-table">
								<div class="bootstrap-table">
									<div class="fixed-table-toolbar no-print">
										<form method="get">
											<input type="hidden" name="p" value="<?= $_GET["p"]?>">
											<div class="row" style="margin-bottom:15px;">
												<div class="col-md-9">
													<div class="input-group bs-datepicker input-daterange picker-range">
														<input type="text" class="form-control" name="from" id="from" value="<?= attrEncode($_GET["from"])? : date('Y-m-01')?>">
														<span class="input-group-addon px-3"><?= L("to"); ?></span>
														<input type="text" class="form-control" name="to" id="to" value="<?= attrEncode($_GET["to"])? : date('Y-m-d')?>">
													</div>
												</div>
											</div>
											<div class="columns columns-right btn-group pull-right no-print">
												<button type="submit" class="btn btn-primary" id="btn_submit"><?= L("genererrapport","o") ?></button>
											</div>
											
										</form>
									</div>
							<?php if($_GET['from'] !='' && $_GET['to'] !=''){
									 if( preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['from']) and preg_match('#^\d{4,5}\-\d{2}\-\d{2}$#',$_GET['to']) ){
									   $listVALUE[] = $_GET['from'] . " 00:00:00";
									   $listVALUE[] = $_GET['to'] . " 23:59:59";
									   $listAND1[] = "(facture.date_insert >= ? AND facture.date_insert <= ?)";
									   $listTotal1 = implode(" AND ", $listAND1);
									   $listAND2[] = "(POINTS.DATE_INSERT >= ? AND POINTS.DATE_INSERT <= ?)";
									   $listTotal2 = implode(" AND ", $listAND2);
									   $listAND3[] = "(CARTE_OPERATION.date_operation >= ? AND CARTE_OPERATION.date_operation <= ?)";
									   $listTotal3 = implode(" AND ", $listAND3);
									?>
									<div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
										<div class="fixed-table-header" style="display: none;">
											<table></table>
										</div>
										<div class="fixed-table-body">
											
											<h5><?= L('periode').' '.L('du').' '. formatDateUTF8($_GET['from']).' '.L('au').' '. formatDateUTF8($_GET['to']);?></h5>
											</br>
											<table class="card-view-no-edit page-size-table table-hover-new table  table-striped table-no-bordered table-condensed">
												<thead>
													<tr>
														<th style='text-align:left'><?= L('magasin',"o")?></th>
														<th style='text-align:right'><?= L('revenus_BRUT',"o")?></th>
														<th style='text-align:right'><?= L('redevances',"o")?></th>
														<th style='text-align:right'><?= L('red_pub',"o")?></th>
														<th style='text-align:right'><?= L('points',"o")?> <br/><small><?= L('animoEmis',"o")?></small></th>
														<th style='text-align:right'><?= L('points',"o")?> <br/><small><?= L('animoRecu',"o")?></small></th>
														<th style='text-align:right'><?= L('points',"o")?> <br/><small><?= L('total',"o")?></small></th>
														<th style='text-align:right'><?= L("carte_cadeau","o")?></th>
														<th style='text-align:right'><?= L("grand_total","o")?></th>
													</tr>
												</thead>
											
												<tbody>
													<?php 
													$tot8 = $tot7 = $tot6 = $tot5 = $tot4 = $tot3 = $tot2 = $tot1 = 0;
													
													$querymagasin = "SELECT M_NOM,ID_MAGASIN,caisse_db
																		 FROM MAGASIN
																		 WHERE caisse_db IS NOT NULL
																		 AND ID_MAGASIN !=5 and ID_MAGASIN != 13
																		 ORDER BY M_NOM";
													$resulmagasin= $mysqli->query($querymagasin);
													
													while ($rowmagasin = $resulmagasin->fetch_assoc()){
																												
														$queryRaport1 = "SELECT SUM(facture_item.montant) `totalsanstaxe`
																			FROM {$rowmagasin['caisse_db']}.facture_item    
																				left join {$rowmagasin['caisse_db']}.article using(id_article)
																				left join {$rowmagasin['caisse_db']}.facture using (id_facture)  
																			WHERE( coalesce(facture_item.id_departement,article.id_departement) < 990 )
																			AND $listTotal1";
														$resulRaport1 =query($queryRaport1,$listVALUE,$dbAnimoCaisse);
														$rowRaport1 = $resulRaport1->fetch_assoc();
														
														$queryRaport2 = "SELECT SUM(facture_item.montant) `totalsanstaxe`
																			FROM {$rowmagasin['caisse_db']}.facture_item  
																				left join {$rowmagasin['caisse_db']}.article using(id_article)
																				left join {$rowmagasin['caisse_db']}.facture using (id_facture)
																			WHERE ( coalesce(facture_item.id_departement,article.id_departement) < 900 
																					or coalesce(facture_item.id_departement,article.id_departement) >= 920)
																			  and coalesce(facture_item.id_departement,article.id_departement) < 990
																			AND $listTotal1";
													   
														$resulRaport2 = query($queryRaport2,$listVALUE,$dbAnimoCaisse);
														$rowRaport2 = $resulRaport2->fetch_assoc();
														
														
																								
														$queryPointIN = "SELECT SUM( ifnull(pointsBrut,points) ) `totalPoint` 
																			FROM POINTS 
																			WHERE ID_MAGASIN = {$rowmagasin['ID_MAGASIN']}
																			AND ifnull(pointsBrut,points) >0 
																			AND $listTotal2"; 
														$resulPointIN = query($queryPointIN,$listVALUE,$mysqli);
														$rowPointIn = $resulPointIN->fetch_assoc();
														
														
														$queryPointOUT = "SELECT SUM(ifnull(pointsBrut,points)) `totalPoint` 
																			FROM POINTS 
																			WHERE ID_MAGASIN = {$rowmagasin['ID_MAGASIN']}
																			AND ifnull(pointsBrut,points) < 0 
																			AND $listTotal2";
														$resulPointOUT = query($queryPointOUT,$listVALUE,$mysqli) or die($mysqli->error);
														$rowPointOUT = $resulPointOUT->fetch_assoc();
														
														
														$queryCarteCad = "SELECT SUM(montant) `total` 
																			FROM CARTE_OPERATION 
																			WHERE ID_MAGASIN ={$rowmagasin['ID_MAGASIN']}
																			AND $listTotal3";
														$resulCarteCadeau =query($queryCarteCad, $listVALUE, $mysqli) or die( $mysqli->error );
														$rowCarteCadeau = $resulCarteCadeau->fetch_assoc();
														
														//Si tout zéro, skip 
														if ( ($rowCarteCadeau['total'] + ($rowRaport2['totalsanstaxe']*0.03) + ($rowRaport2['totalsanstaxe']* 0.015) + $rowPointIn['totalPoint'] + $rowPointOUT['totalPoint']) == 0 ){
															continue;
														}
														?>
														<tr>
															<td style='text-align:left'><?php echo $rowmagasin['M_NOM']?></td>
															<td style='text-align:right'><?php echo formatPrix($rowRaport2['totalsanstaxe']);?><br/><small>(<?php echo formatPrix($rowRaport1['totalsanstaxe']);?>)</small></td>
																	<?php  $tot1 += $rowRaport2['totalsanstaxe']?>                                               	
															<td style='text-align:right'><?php echo formatPrix($rowRaport2['totalsanstaxe']*0.03);?></td>
																	<?php $tot2 += $rowRaport2['totalsanstaxe']*0.03?> 
															<td style='text-align:right'><?php echo formatPrix($rowRaport2['totalsanstaxe']* 0.015);?> </td>
																	<?php $tot3 += $rowRaport2['totalsanstaxe']*0.015?>                                           			
															<td style='text-align:right'><?php echo formatPrix($rowPointIn['totalPoint']);?></td>
																	<?php $tot4 += $rowPointIn['totalPoint']?>
															<td style='text-align:right'><?php echo formatPrix($rowPointOUT['totalPoint']);?></td>
																	<?php $tot5 += $rowPointOUT['totalPoint']?>
															<td style='text-align:right'><?php echo formatPrix($rowPointIn['totalPoint'] + $rowPointOUT['totalPoint']);?></td>
																	<?php $tot6 += $rowPointIn['totalPoint'] + $rowPointOUT['totalPoint']?>
															<td style='text-align:right'><?php echo formatPrix($rowCarteCadeau['total']);?></td> 
																	<?php $tot8 += $rowCarteCadeau['total']?>
															<td style='text-align:right'><?php echo formatPrix($rowCarteCadeau['total'] + ($rowRaport2['totalsanstaxe']*0.03) + ($rowRaport2['totalsanstaxe']* 0.015) + $rowPointIn['totalPoint'] + $rowPointOUT['totalPoint']);?></td> 
																	<?php $tot7 += $rowCarteCadeau['total'] + ($rowRaport2['totalsanstaxe']*0.03) + ($rowRaport2['totalsanstaxe']* 0.015) + $rowPointIn['totalPoint'] + $rowPointOUT['totalPoint']?>
														</tr>                                             		
												<?php }?>                                         
														 <tr>
															<td></td>
															<td style='text-align:left'> <h4><?= formatPrix($tot1)?></h4></td>
															<td style='text-align:right'><h4><?= formatPrix($tot2);?></h4></td>
															<td style='text-align:right'><h4><?= formatPrix($tot3);?></h4></td>
															<td style='text-align:right'><h4><?= formatPrix($tot4);?></h4></td>
															<td style='text-align:right'><h4><?= formatPrix($tot5);?></h4></td>
															<td style='text-align:right'><h4><?= formatPrix($tot6);?></h4></td>
															<td style='text-align:right'><h4><?= formatPrix($tot8);?></h4></td>
															<td style='text-align:right'><h4><?= formatPrix($tot7);?></h4></td>
														</tr>
												
												</tbody>
											</table> 
										</div>
									</div>
								<?php }?>
							<?php }?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</section>
