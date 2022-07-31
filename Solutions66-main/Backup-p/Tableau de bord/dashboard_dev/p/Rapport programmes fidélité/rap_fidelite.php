<?php
ini_set("memory_limit","256M");
set_time_limit(300);

$listAND = [];
$listVALUE = [];

$listAND[] = "ID_MAGASIN = ?";
$listVALUE[] = $_SESSION['mag'];
$listCononneTri = ['groupe.nom','achat_fidele_gratuit.date_insert','id_facture'];

if(in_array($_GET['order'],$listCononneTri)){ $order = $mysqli->real_escape_string($_GET['order']); }else{ $order = 'achat_fidele_gratuit.date_insert';}
if($_GET['sens']==''){ $sens = 'desc';}else{ $sens = $mysqli->real_escape_string($_GET['sens']);}

$addLeftJoin = "
JOIN animoetc_caisse_default.groupe_fidele USING(id_groupe_fidele)
JOIN animoetc_caisse_default.groupe USING(id_groupe)
";
$CURRENT_MARQUE = null;
if(preg_match('#^\d+$#',$_GET['id_marques'])){
    $addLeftJoin = "
                JOIN animoetc_caisse_default.groupe_fidele USING(id_groupe_fidele)
                JOIN animoetc_caisse_default.groupe USING(id_groupe)
                JOIN animoetc_caisse_default.link_article_groupe USING(id_groupe)
                JOIN animoetc_caisse_default.article_desc USING(id_article)
    ";
    $listAND[] = "animoetc_caisse_default.article_desc.marque = ?";
    $listVALUE[] = $_GET['id_marques'];
    
    $queryMark = "SELECT nom FROM marques where id_marques = ? limit 1";
    $getMark = query($queryMark,[$_GET['id_marques']],$mysqli);
    $CURRENT_MARQUE = $getMark->fetch_row()[0];
}
$CURRENT_FOUR = null;
if(preg_match('#^\d+$#',$_GET['id_fournisseur'])){
    $listAND[] = "animoetc_caisse_default.groupe_fidele.id_fournisseur = ?";
    $listVALUE[] = $_GET['id_fournisseur'];

    $queryMark = "SELECT nom FROM fournisseur where id_fournisseur = ? limit 1";
    $getMark = query($queryMark,[$_GET['id_fournisseur']],$dbAnimoCaisse);
    $CURRENT_FOUR = $getMark->fetch_row()[0];
}

$and = implode(' and ',$listAND);
/*
 and groupe_fidele.inactif is null
 and groupe.inactif is null
 */
$querygratuit = "SELECT achat_fidele_gratuit.*, groupe.nom FROM achat_fidele_gratuit
                $addLeftJoin
				 WHERE $and
				  AND id_groupe_fidele >= 0 and groupe.inactif is null and groupe_fidele.inactif is null
                 GROUP BY achat_fidele_gratuit.id_achat_fidele_gratuit
				ORDER BY {$order} {$sens}";

$resultgratuit = query($querygratuit,$listVALUE,$mysqli);

$arrayData = [
	"headings"=>[
		'groupe.nom'=>L('programmefidelite'),
		'achat_fidele_gratuit.date_insert'=>L('date'),
		'id_facture'=>L('facture'),
		""=>null
	],
	"rows"=>[]
];
$arrayDataPDF = [
	"headings"=>[
		'groupe.nom'=>L('programmefidelite'),
		'achat_fidele_gratuit.date_insert'=>L('date'),
		'id_facture'=>L('facture')
	],
	"rows"=>[]
];
while ($rowgratuit = $resultgratuit->fetch_assoc()){
	$arrayData["rows"][] = [
		$rowgratuit['nom'],
		formatDateUTF8($rowgratuit['date_insert']),
	    $rowgratuit['id_facture'],
	    $rowgratuit['id_groupe_fidele'],
	    $rowgratuit['id_achat_fidele_gratuit']
	];
	$arrayDataPDF["rows"][] = [
		$rowgratuit['nom'],
		utf8_encode(strftime("%e %B %Y",strtotime($rowgratuit['date_insert']))),
		$rowgratuit['id_facture']
	];
}
if ( $_GET["getFile"] == "1" and $arrayDataPDF ){
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
		[ ["text"=>$arrayDataPDF["headings"]['groupe.nom'],"width"=>80,"align"=>"L"], ["text"=>$arrayDataPDF["headings"]['achat_fidele_gratuit.date_insert'],"width"=>50,"align"=>"L"],["text"=>$arrayDataPDF["headings"]['id_facture'],"width"=>35,"align"=>"L"]],
	];

	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	//
	foreach ( $arrayDataPDF["rows"] as $uneLigne){
		$listChamps = [];
		$listChamps[0] = $uneLigne[0];
		$listChamps[1] = $uneLigne[1];
		$listChamps[2] = $uneLigne[2];

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
			<span class="hidden-xs-down"><?= L("rapportfidelite","o");?><?php if($CURRENT_MARQUE){?> pour <?php echo $CURRENT_MARQUE; }?></span>
		</h1>
		<div class="smart-links no-print">
			<ul class="nav" role="tablist">
				<li class="nav-item dropdown" style="position:relative;">
					<a href="#" class="nav-link dropdown-toggle" id="panelDropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?= $CURRENT_FOUR ?: 'Fournisseurs' ?>
					</a>
					<div class="dropdown-menu lh-5 dropdown-menu-right" style="height: 500px;overflow: auto;" aria-labelledby="panelDropdownMenu1">
						<div>
							<a href="index.php?<?= rebuildQueryString([],["id_fournisseur"]) ?>" class="dropdown-item" tabindex="-1">Tous les fournisseurs</a>
							<?php
							$queryFour = "SELECT fournisseur.id_fournisseur,fournisseur.nom FROM fournisseur 
                                        join groupe_fidele using(id_fournisseur) 
                                        join groupe using(id_groupe)
                                        where groupe.inactif is null and groupe.type = 'fidelite' and groupe_fidele.inactif is null and fournisseur.inactif is null
                                        and fournisseur.est_distributeur = 1
                                        group by fournisseur.id_fournisseur
                                        order by nom ASC";
							$resulFour = $dbAnimoCaisseDefault->query($queryFour);
							while ($rowFour = $resulFour->fetch_assoc()) {
							?>
								<a class="dropdown-item<?= $_GET['id_fournisseur'] == $rowFour['id_fournisseur'] ? ' active' : ''?>" tabindex="-1" href="index.php?<?= rebuildQueryString(["id_fournisseur"=>$rowFour['id_fournisseur']]) ?>"><?php echo $rowFour['nom']?></a>
							<?php }?>
						</div>
					</div>
				</li>
				<?php /*?>
				<li class="nav-item dropdown" style="position:relative;">
					<a href="#" class="nav-link dropdown-toggle" id="panelDropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?= $CURRENT_MARQUE ?: 'Marques'?>
					</a>
					<div class="dropdown-menu lh-5 dropdown-menu-right" style="height: 500px;overflow: auto;" aria-labelledby="panelDropdownMenu1">
						<div>
							<a href="index.php?<?= rebuildQueryString([],["id_marques"]) ?>" class="dropdown-item" tabindex="-1">Toutes les marques</a>
							<?php
							$queryFour = "SELECT marques.id_marques,marques.nom FROM marques 
                                        left join article_desc on(marques.id_marques = article_desc.marque)
                                        left join link_article_groupe using(id_article)
                                        left join groupe using(id_groupe)
                                        where groupe.inactif is null and groupe.type = 'fidelite'
                                        group by marques.id_marques
                                        order by nom ASC";
							$resulFour = $dbAnimoCaisseDefault->query($queryFour);
							while ($rowFour = $resulFour->fetch_assoc()) {
							?>
								<a class="dropdown-item<?= $_GET['id_marques'] == $rowFour['id_marques'] ? ' active' : ''?>" tabindex="-1" href="index.php?<?= rebuildQueryString(["id_marques"=>$rowFour['id_marques']]) ?>"><?php echo $rowFour['nom']?></a>
							<?php }?>
						</div>
					</div>
				</li>
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" onclick="window.print();" href="javascript:;">
						<i class="fa fa-print"></i>
					</a>
				</li><?php */?>
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
	<div class="row">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<div class="panel bgc-white-dark">
				<div class="panel-body" style="position:relative;height: -webkit-calc(100vh - 115px);height: -moz-calc(100vh - 115px);height: -ms-calc(100vh - 115px);height: calc(100vh - 115px);overflow:auto;">
					<div class="table-responsive slimscroll">
						<div class="rTable">
							<div class="rTableHeading">
								<div class="rTableRow">
									<?php foreach($arrayData["headings"] as $orderby => $th){
									    if($th == null){
									        printf('<div class="rTableHead">%s</div>',$orderby);
									    }else{
									    $realsens = ($orderby == $order ? ($sens == 'desc' ? 'asc' : 'desc') : $sens);
									    printf('<a class="rTableHead" href="index.php?'.rebuildQueryString(["order"=>$orderby,"sens"=>$realsens]).'">%s <i class="fa fa-sort'.($orderby == $order ? '-'.$realsens : '' ).'"></i></a>',$th);
									    }
									}
									?>
								</div>
							</div>
							<div class="rTableBody">
    							<div class="rTableRow rTableHeadingClone">
    								<?php foreach($arrayData["headings"] as $orderby => $th){
									    if($th == null){
									        printf('<div class="rTableHead">%s</div>',$orderby);
									    }else{
									    $realsens = ($orderby == $order ? ($sens == 'desc' ? 'asc' : 'desc') : $sens);
									    printf('<a class="rTableHead" href="index.php?'.rebuildQueryString(["order"=>$orderby,"sens"=>$realsens]).'">%s <i class="fa fa-sort'.($orderby == $order ? '-'.$realsens : '' ).'"></i></a>',$th);
									    }
									}
									?>
    							</div>
								<?php foreach($arrayData["rows"] as $data){?>
    								<div class="rTableRow">
    									<div class="rTableCell"><?php echo $data[0];?></div>
    									<div class="rTableCell"><?php echo $data[1];?></div>
    									<a class="rTableCell ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?= $data[2]?>&ID_MAGASIN=<?= $_SESSION['mag']?>"><?php echo $data[2];?></a>
    									<a class="rTableCell text-right" href="index.php?p=rap_fidelite_details&id_achat_fidele_gratuit=<?= $data[4]?>&id_groupe_fidele=<?= $data[3]?>"><i class='fa fa-print'></i></a>
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