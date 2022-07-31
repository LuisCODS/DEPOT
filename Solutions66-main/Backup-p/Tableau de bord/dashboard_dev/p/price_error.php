<?php 

if(preg_match('#^\d+$#',$_GET['erreur_id']) and has_rights("admin_price_error") ) {
    
    $arrayDB = array();
    $arrayDB["erreur_id"] = $_GET['erreur_id'];
    $arrayDB["date_done"] = date('Y-m-d H:i:s');
    faireUpdate_i($arrayDB,"ERREURPRIX","erreur_id",$mysqli);
}


$queryErrorPrix = "SELECT * 
                   FROM ERREURPRIX
                   LEFT JOIN MAGASIN USING(ID_MAGASIN) 
                   order by date_done is null DESC, date_sent DESC LIMIT 150";
                   
$resulErrorPrix = query($queryErrorPrix,[],$mysqli);

$data = ["liste"=>[]];

//========================= Gestion PDF excel ===================================
if ( $_GET["getFile"] == "1" ){
    
    while($rowErrorPrix = $resulErrorPrix->fetch_assoc()){
        $data2 = array();
        $data2[0] = $rowErrorPrix['fournisseur'];
        $data2[1] = $rowErrorPrix['code_four'];
        $data2[2] = $rowErrorPrix['cost'];
        $data2[3] = $rowErrorPrix['upc'];
        $data2[4] = $rowErrorPrix['error_type'];
        $data2[5] = $rowErrorPrix['M_NOM'];
        $data2[6] = date("Y-m-d",strtotime($rowErrorPrix['date_sent']));
        $data2[7] = date("Y-m-d",strtotime($rowErrorPrix['date_done']));
        $data2[8] = $rowErrorPrix['date_done'] != "" ? "Oui" : "Non";
        $data["liste"][] = $data2;
    }
    require_once(__DIR__."/../req/print.php");
    
    if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
        $rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
    } else {
        $rapport = new RapportPDF( RPDF_PAGE_LANDSCAPE, RPDF_SHOWCOL_ALLPAGE );
        #$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
    }
    
    $titre = L("Erreurs de prix","o");
    
    $listSoustitre = [];
    $listSoustitre[] = ["en date du", formatDateUTF8( date("Y-m-d") ) ];
    //$listSoustitre[] = ["devise",  $uneLigneTaxePaye["DEVISE_ABBR"] ];
    
    $listEnteteColonne = [
        [ ["text"=>"Fournisseur","width"=>35,"align"=>"L"],["text"=>L("code four.",'o'),"width"=>35,"align"=>"L"],["text"=>L("coûtant",'o'),"width"=>25,"align"=>"R"], ["text"=>L("upc",'o'),"width"=>30,"align"=>"L"],["text"=>L("Type",'o'),"width"=>40,"align"=>"L"],["text"=>L("Magasin",'o'),"width"=>25,"align"=>"L"],["text"=>"Date soum.","width"=>20,"align"=>"L"],["text"=>"Date compl.","width"=>20,"align"=>"L"],["text"=>"Completé","width"=>15,"align"=>"L"],],
    ];
    
    $rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
    $rapport->setInfoCols(-1);

    foreach ( $data["liste"] as $uneLigneArticle){
        $listChamps = $uneLigneArticle;
        $rapport->writeLigneRapport3wrap( $listChamps );
    }
    ob_clean();
    $rapport->Output( formatFileName($titre).'.pdf', 'I');
    die("");
}

/*
echo '<pre>';
echo htmlspecialchars(print_r($_REQUIRED, true)); 
echo htmlspecialchars(print_r($arrayDB, true)); 
echo htmlspecialchars(print_r($data, true)); 
echo '</pre>';
*/


?>
<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa icon-people icon-mr fs-4"></i>
			<span class="hidden-xs-down"> Erreurs de prix</span>
		</h1>
		<div class="smart-links">
			<ul class="nav" role="tablist">
				<li class="nav-item">
					<a class="nav-link clear-style" href="javascript:window.print();" role="tab">
						<i class="fa fa-print "></i>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" href="index.php?p=price_error&getFile=1&format=pdf" target="_blank">
						<i class="fa fa-file-pdf-o "></i>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" href="index.php?p=price_error&getFile=1&format=xlsx" target="_blank">
						<i class="fa fa-file-excel-o "></i>
					</a>
				</li>

				<li class="nav-item">
					<a class="nav-link clear-style" href="?p=produit_error" role="tab">
						<i class="fa fa-plus-circle "></i>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<!-- End SubHeader-->
	<!-- Start Content-->
	<div class="row pl-3 pr-3 mb-3 mt-3">   
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<!--Start Panel-->
			<div class="panel c-white-dark pb-0">
				<div class="panel-body">
					<div class="panel bgc-white-dark transition visible pb-2">
						<div class="panel-body panel-body-p">
							<div class="page-size-table">
        						<form method="POST">
        							<div>
        								<table class="table table-hover">
        									<thead>
        										<tr>
        											<th></th>
        											<th><?php echo $L['fournisseur'];?></th>
        											<th><?php echo $L["codeupc"];?></th>
        											<th>Type d'erreur</th>
        											<th><?php echo $L['magasin'];?></th>
        											<th><?php echo $L["dateSoumission"];?></th>
        											<th><?php echo $L["dateDone"];?></th>
        											<th></th>
        										</tr>
        									</thead>
        									<tbody>
        										<?php 
        										$resulErrorPrix->data_seek(0);
        										while ($rowErrorPrix = $resulErrorPrix->fetch_assoc()) {
        											$ico = '';
        											if($rowErrorPrix['date_done'] !=''){
        											    $ico = '<span class="label label-sm label-icon label-success"><i class="fa fa-check"></i>&nbsp;</span>';
    											    }
           							                $lpage = "produits"; 
        											?>
            										<tr>
            											<td></td>
            											<td>
            												<b><?php echo $rowErrorPrix['fournisseur'];?></b><br/>
            												<a href="index.php?p=<?php echo $lpage; ?>&four=<?php echo $rowErrorPrix['code_four'];?>"><?php echo $rowErrorPrix['code_four'];?></a> (<?php echo $rowErrorPrix['cost'];?>)
            											</td>
            											<td>
            											    <a href="index.php?p=<?php echo $lpage; ?>&upc=<?php echo $rowErrorPrix['upc'];?>" class="popovers" data-trigger="hover" data-placement="bottom" data-html="true" data-content="<?php echo $rowErrorPrix['description'];?>" data-container="body"><?php echo $rowErrorPrix['upc'];?>
            											    </a>
        											    </td>
            											<td><?php echo $rowErrorPrix['error_type'];?></td>
            											<td><a href="mailto:<?php echo $rowErrorPrix['M_EMAIL'];?>?subject=Information supplémentaire - erreur de prix&body=Fournisseur : <?php echo $rowErrorPrix['fournisseur'];?> %0D%0A Code Fournisseur : <?php echo $rowErrorPrix['code_four'];?> %0D%0A Code UPC : <?php echo $rowErrorPrix['upc'];?> %0D%0A Description : <?php echo $rowErrorPrix['description'];?> %0D%0A"><?php echo $rowErrorPrix['M_NOM'];?></a></td>
            											<td><?php echo $rowErrorPrix['date_sent'];?></td>
            											<td><?php echo $rowErrorPrix['date_done'];?></td>
            											<td class="droit">
            												<?php if(has_rights("admin_price_error")){ ?>
            												<div class="btn-group">
            													<?php if(!$rowErrorPrix['date_done']){?>
            													<a href="index.php?p=price_error&erreur_id=<?php echo $rowErrorPrix['erreur_id'];?>" title="<?php echo $L['markascompleted'];?>"><i class="fa fa-check fa-lg"></i></a>
            													<?php }?>
            												</div>
            												<?php }?>
            												<?php echo $ico;?>
            											</td>
            										</tr>
        										<?php } ?>
        									</tbody>
        								</table>
        							</div>
        						</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	<!-- End Content-->
</section>




