<?php



if ( preg_match('#^\d+$#',$_GET["id_achat_fidele_gratuit"]) ){
	$querycarticle = "select *, groupe.nom
								from achat_fidele_gratuit
								JOIN animoetc_caisse_default.groupe_fidele USING(id_groupe_fidele)
								JOIN animoetc_caisse_default.groupe USING(id_groupe)
							where id_achat_fidele_gratuit = ? and groupe.inactif is null and groupe_fidele.inactif is null limit 1";
	$result_achatFid = query($querycarticle,[$_GET["id_achat_fidele_gratuit"]],$mysqli);
	if($result_achatFid->num_rows === 1){
		$uneLigneAchatFidGratuit = $result_achatFid->fetch_assoc();
		
		$caisse_db = getInfoMagDB($uneLigneAchatFidGratuit["ID_MAGASIN"])["caisse_db"];
		
		$queryg = "
				SELECT article.*, facture_item.montant
				FROM $caisse_db.facture_item left join $caisse_db.article using(id_article)
				WHERE id_facture = ? and `type` like 'ESCOMPTE:FID:{$uneLigneAchatFidGratuit['id_groupe_fidele']}'";
		$resultG = query($queryg,[$uneLigneAchatFidGratuit['id_facture']],$mysqli);
		if ( $uneLigne = $resultG->fetch_assoc() ){
			foreach( $uneLigne as $k => $v){
				 $uneLigneAchatFidGratuit[$k] = $v;
			}
		}
	}else{
		include __DIR__."/404.php";
		return;
	}
}


// liste gratuité
$list_id_article = [];
$dataGratuitFidele = [];

$querygratuit = "
		SELECT article.id_article, article.desc_fr, article.PLU,
			achat_fidele.id_facture, achat_fidele.ID_MAGASIN, achat_fidele.num_avantages,
			achat_fidele.id_groupe_fidele,achat_fidele.date_insert
		FROM achat_fidele
			JOIN animoetc_caisse_default.article ON achat_fidele.id_article = article.id_article
		WHERE id_achat_fidele_gratuit = ?
	group by achat_fidele.id_achat_fidele
	ORDER BY achat_fidele.date_insert ASC;";
$resultgratuit = query($querygratuit,[$uneLigneAchatFidGratuit['id_achat_fidele_gratuit']],$mysqli);
if($resultgratuit->num_rows > 0){
	while ($rowgratuit = $resultgratuit->fetch_assoc()){
		$caisse_db = getInfoMagDB($rowgratuit["ID_MAGASIN"])["caisse_db"];
		
		$rowfacture = null;
		if ( $rowgratuit["id_facture"] and $rowgratuit["id_facture"] != "-1" ){
			$enonce = "select * from $caisse_db.facture where id_facture = ?";
			$resultfact = query($enonce,[$rowgratuit["id_facture"]],$mysqli);
			if($resultfact->num_rows === 1){
				$rowfacture = $resultfact->fetch_assoc();
			}
		}
		// aller chercher le distributeur
		$getDist = query('select fournisseur.nom,link_article_four.num_four from fournisseur
							join link_article_four using(id_fournisseur)
							where est_fournisseur = 1 and id_article = ?
							order by prix_coutant asc limit 1
							',[$rowgratuit['id_article']],$dbAnimoCaisse);
		if($getDist->num_rows === 1){
			$rowDist = $getDist->fetch_assoc();
			$rowgratuit['distributeur'] = $rowDist['nom'];
			$rowgratuit['num_four'] = $rowDist['num_four'];
		}else{
			$rowgratuit['distributeur'] = '';
			$rowgratuit['num_four'] = '';
		}
		$list_id_article[$caisse_db][] = $rowgratuit["id_article"];
		$unique_table_id = "";
		$dataGratuitFidele[] = [
			"caisse_db"=>$caisse_db,
			"id_article"=>$rowgratuit["id_article"],
			"PLU"=>$rowgratuit['PLU'],
			"ID_MAGASIN"=>$rowgratuit['ID_MAGASIN'],
			"desc_fr"=>$rowgratuit['desc_fr'] . ' ('.$rowgratuit['num_four'].')',
			"date"=>$rowfacture['date_insert'] ?: $rowgratuit['date_insert'],
			"id_facture"=>$rowgratuit['id_facture'] ?: null,
			"cartedepoint"=>$rowgratuit['num_avantages']
		];
		$ca = $rowgratuit['num_avantages'];
	}
}

$querycarticle = "SELECT *
				FROM $caisse_db.facture_item
					LEFT JOIN $caisse_db.article USING (id_article)
				WHERE type = CONCAT('ESCOMPTE:FID:',?)
				and facture_item.id_facture = ?";
$resultarticle = query($querycarticle,[$uneLigneAchatFidGratuit['id_groupe_fidele'], $uneLigneAchatFidGratuit['id_facture']],$mysqli);
if($resultarticle->num_rows > 0){
	$rowarticle = $resultarticle->fetch_assoc();
}
//si pas de ESCOMPTE:FID: dans la facture, chercher l'article simplement avec les id deja ajouter a fid
$caisse_db = getInfoMag("caisse_db");
if ( !$rowarticle and sizeof($list_id_article[$caisse_db]) > 0 ){
	$querycarticle = sprintf( "SELECT *
							FROM $caisse_db.facture_item
								LEFT JOIN $caisse_db.article USING (id_article)
							WHERE id_article in (%s)
							and facture_item.id_facture = ?
						order by facture_item.montant asc",
		implode(',',$list_id_article[$caisse_db]) );

	$resultarticle = query($querycarticle,[$uneLigneAchatFidGratuit['id_facture']],$mysqli);
	if($resultarticle->num_rows > 0){
		$rowarticle = $resultarticle->fetch_assoc();
	}
}

//si pas de ESCOMPTE:FID: dans la facture, chercher l'article avec les id du groupe fid
if ( !$rowarticle ){
	$querycarticle = "SELECT *
						FROM $caisse_db.facture_item
							 LEFT JOIN $caisse_db.article USING (id_article)
					   WHERE id_article in (
								select link_article_groupe.id_article
								  from $caisse_db.groupe_fidele
									   left join $caisse_db.groupe using(id_groupe)
									   left join $caisse_db.link_article_groupe using(id_groupe)
								 where groupe_fidele.id_groupe_fidele = ?
							)
						and facture_item.id_facture = ?
						 order by facture_item.montant asc";

	$resultarticle = query($querycarticle,[$uneLigneAchatFidGratuit['id_groupe_fidele'],$uneLigneAchatFidGratuit['id_facture']],$mysqli);
	if($resultarticle->num_rows > 0){
		$rowarticle = $resultarticle->fetch_assoc();
	}
}

// client
$queryclient = 'SELECT * FROM CLIENT WHERE CLIENT.cartedepoint = ?';
$resultclient = query($queryclient,[$ca],$mysqli);
if($resultclient->num_rows === 1){
	$rowclient = $resultclient->fetch_assoc();
}

uasort($dataGratuitFidele,function($a,$b){

	if(strtotime($a["date"]) < strtotime($b["date"])){
		return -1;
	}else{
		return 1;
	}
});
if ( $_GET["getFile"] == "1" and $dataGratuitFidele ){
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}


	$titre = L("rap_fidelite","o");

	$listSoustitre = [];
	$listSoustitre[] = ["Groupe fidélité:", $uneLigneAchatFidGratuit["nom"] ];



	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne, RPDF_SKIP_PRINT_ENTETE);
	$rapport->setInfoCols(-1);

	//Sommaire
	$rapport->SetFont("helvetica","B",14);
	$rapport->Cell(0,0,L("Produits applicables vendus","o"), 0, 1);


	$rapport->listLigneEnteteColonne = [
		[
			["text"=>"N'","width"=>8,"align"=>"L"],
			["text"=>"Article","width"=>77,"align"=>"L"],
			["text"=>L("UPC"),"width"=>25,"align"=>"L"],
			["text"=>L("Magasin"),"width"=>30,"align"=>"L"],
			["text"=>L("Date"),"width"=>20,"align"=>"L"],
			["text"=>L("Facture"),"width"=>18,"align"=>"L"],
		],
	];
	$rapport->setInfoCols(-1);
	$rapport->printEntetes(-1);

	$i=1;

	foreach($dataGratuitFidele as $rowgratuit){
		$listChamps = [];
		$listChamps[0] = $i;
		$listChamps[1] = $rowgratuit['desc_fr'];
		$listChamps[2] = $rowgratuit['PLU'];
		$listChamps[3] = getInfoMagDB($rowgratuit["ID_MAGASIN"])["M_NOM"];
		if ( $rowgratuit["date"] ){
			$listChamps[4] = date("Y-m-d",strtotime($rowgratuit['date']));
		} else {
			$listChamps[4] = "-";
		}
		if($rowgratuit['id_facture'] and $rowgratuit['id_facture'] != "-1"){
			$listChamps[5] = $rowgratuit['ID_MAGASIN'].'-'.$rowgratuit['id_facture'];;
		}else{
			$listChamps[5] = "-";
		}

		$rapport->writeLigneRapport3wrap( $listChamps );
		$i++;
	}

	if($uneLigneAchatFidGratuit){
		$j = $i + 1;
		//Sommaire des paiements
		$rapport->Ln(5);
		$rapport->SetFont("helvetica","B",14);
		$rapport->Cell(0,0,L("Gratuité reçue","o"), 0, 1);


		$rapport->listLigneEnteteColonne = [
			[
				["text"=>"Article","width"=>108,"align"=>"L"],
				["text"=>L("UPC"),"width"=>30,"align"=>"L"],
				["text"=>L("Facture"),"width"=>20,"align"=>"L"],
				["text"=>L("Prix"),"width"=>20,"align"=>"R"],
			]
		];
		$rapport->setInfoCols(-1);
		$rapport->printEntetes(-1);

		$listChamps = [];
		$listChamps[0] = $rowarticle["desc_fr"];
		$listChamps[1] = $rowarticle["PLU"];
		$listChamps[2] = $uneLigneAchatFidGratuit["ID_MAGASIN"].'-'.$uneLigneAchatFidGratuit['id_facture'];
		$listChamps[3] = nfs($rowarticle['montant']);
		$rapport->writeLigneRapport3wrap( $listChamps );
	}

	if($rowclient){
		//Sommaire des paiements
		$rapport->Ln(5);
		$rapport->SetFont("helvetica","B",14);
		$rapport->Cell(0,0,L("Client","o"), 0, 1);


		$rapport->listLigneEnteteColonne = [
			[
				["text"=>L("clientname"),"width"=>70,"align"=>"L"],
				["text"=>L("adresse"),"width"=>82,"align"=>"L"],
				["text"=>L("carteanimo"),"width"=>26,"align"=>"L"],
			]
		];
		$rapport->setInfoCols(-1);
		$rapport->printEntetes(-1);

		$listChamps = [];
		$listChamps[0] = implode(", ",[$rowclient["PRENOM"],$rowclient["NOM"]]);
		$listChamps[1] = "";
		
		$listElement = [];
		if ( $rowclient['ADRESSE'] )
			$listElement[] = $rowclient['ADRESSE'];

		if ( $rowclient['VILLE'] )
			$listElement[] = $rowclient['VILLE'];

		if ( $rowclient['PROVINCE'] )
			$listElement[] = $rowclient['PROVINCE'];
		
		if ( $rowclient['CP'] )
			$listElement[] = $rowclient['CP'];
		
		$listChamps[1] = implode(', ',$listElement);
			
		$listChamps[2] = $ca;
		$rapport->writeLigneRapport3wrap( $listChamps );
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
			<span class="hidden-xs-down"><?= L("rapportfidelite","o");?></span>
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
	<!-- End SubHeader-->
<!-- BEGIN CONTENT -->
	<div class="row pl-3 pr-3 mb-3 mt-3 print-top">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
					<div class="panel bgc-white-dark transition visible">
						<div class="panel-body panel-body-p">
							<div class="page-size-table">
								<div class="bootstrap-table">
									<div class="row">
										<div class="col-md-12 p-2">
											<h1 class="no-print">Animo etc <?= getInfoMag('succursale')?></h1>
										</div>
										<div class="col-md-12 p-2">
											<h3><?= L('Produitsappvendu');?></h3>
											<div class="table-responsive">
											<table class="table table-hover table-condensed tableprint printsml">
												<thead>
													<tr>
														<th></th>
														<th><?= L('articlenom');?></th>
														<th><?= L('plu');?></th>
														<th><?= L("magasin")?></th>
														<th><?= L('date');?></th>
														<th><?= L('facture');?></th>
													</tr>
												</thead>
												<tbody>
													<?php

													$i=1;

													foreach($dataGratuitFidele as $rowgratuit){
														?>
														<tr>
															<td><b><?php echo $i; ?></b></td>
															<td><a href="?p=produits&upc=<?php echo $rowgratuit['PLU']?>"><?php echo $rowgratuit['desc_fr'];?></a></td>
															<td><a href="?p=produits&upc=<?php echo $rowgratuit['PLU']?>"><?php echo $rowgratuit['PLU'];?></a></td>
															<td>
																<?= getInfoMagDB($rowgratuit["ID_MAGASIN"])["M_NOM"]; ?>
															</td>
															<td>
																<?php

																if ( $rowgratuit["date"] ){
																	echo formatDateUTF8($rowgratuit['date']);
																} else {
																	?>
																	N/A
																	<?php
																}
																?>
															</td>
															<td>
																<?php 
																if ( $rowgratuit["id_facture"] and $rowgratuit["id_facture"] != "-1" ){
																	$id_magasin_check = $_SESSION["mag"];
																	$canSeeFacture = false;
																	if($_SESSION["mag"] == "5"){
																		$id_magasin_check = $rowgratuit["ID_MAGASIN"];
																		$canSeeFacture = true;
																	}else if($id_magasin_check == $rowgratuit["ID_MAGASIN"]){
																		$canSeeFacture = true;
																	}
																	if($canSeeFacture){
																		?>
																		<a class="ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?php echo $rowgratuit['id_facture']; ?>&ID_MAGASIN=<?php echo $id_magasin_check;?>">
																			<?php echo $rowgratuit['ID_MAGASIN'].'-'.$rowgratuit['id_facture']; ?>
																		</a>
																		<?php
																	}else{
																		echo $rowgratuit['ID_MAGASIN'].'-'.$rowgratuit['id_facture'];
																	}
																} else {
																	?>
																	N/A
																	<?php
																} ?>
															</td>
														</tr>
														<?php
														$i++;
														}
													?>
												</tbody>
											</table>
											</div>
										</div>
										<div class="col-md-12 p-2">
											<h3><?php echo $L['gratuiteremise'];?></h3>
											<div class="table-responsive">
											<table class="table table-hover table-condensed tableprint printsml">
												<thead>
													<tr>
														<th><?= L('articlenom');?></th>
														<th><?= L('plu');?></th>
														<th><?= L('prix');?></th>
														<th><?= L('facture');?></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td><a href="?p=produits&upc=<?php echo $uneLigneAchatFidGratuit['PLU']?>"><?php echo $uneLigneAchatFidGratuit['desc_fr']?></a></td>
														<td><a href="?p=produits&upc=<?php echo $uneLigneAchatFidGratuit['PLU']?>"><?php echo $uneLigneAchatFidGratuit['PLU']?></a></td>
														<td><?= formatPrix($uneLigneAchatFidGratuit['montant'])?></td>
														<td><a class="ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?php echo $uneLigneAchatFidGratuit['id_facture']; ?>&ID_MAGASIN=<?php echo $uneLigneAchatFidGratuit["ID_MAGASIN"];?>"><?php echo $uneLigneAchatFidGratuit["ID_MAGASIN"].'-'.$uneLigneAchatFidGratuit['id_facture']; ?></a></td>
													</tr>
												</tbody>
											</table>
											</div>
										</div>
										<?php if(has_rights('client_detail')){?>
										<div class="col-md-12 p-2">
											<h3>Client</h3>
											<div class="table-responsive">
												<table class="table table-hover table-condensed">
													<thead>
														<tr>
															<th><?= L('clientname');?></th>
															<th><?= L('adresse');?></th>
															<th><?= L('carteanimo');?></th>
														</tr>
													</thead>
													<tbody>
														<tr onclick="location = '?p=client_detail&id=<?php echo $rowclient['ID_CLIENT']?>'">
															<td><?php echo $rowclient['PRENOM']?> <?php echo $rowclient['NOM']?></td>
															<td>
															<?php
															$listElement = [];
															
															if ( $rowclient['ADRESSE'] )
																$listElement[] = $rowclient['ADRESSE'];
	
															if ( $rowclient['VILLE'] )
																$listElement[] = $rowclient['VILLE'];
	
															if ( $rowclient['PROVINCE'] )
																$listElement[] = $rowclient['PROVINCE'];
															
															if ( $rowclient['CP'] )
																$listElement[] = $rowclient['CP'];
															echo implode(', ',$listElement);
															?>
															</td>
															<td><?php echo $ca;?></td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
										<?php }?>
									</div>
								</div>
							</div>
						</div>
					</div>
		</section>
	</div>
</section>
<?php }?>