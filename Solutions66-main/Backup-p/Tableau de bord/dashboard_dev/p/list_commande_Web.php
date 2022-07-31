<?php

//echo '<pre>' , print_r($_REQUEST) , '</pre>';

// ======================   VARIABLES  =================================

$data = [];//Contiendra tous les valeurs à afficher sur web et le rapport
$listAND = []; //pour la requete
$listPARAM = []; //pour la requete
$data = ["row"=>[], "totalCommande"=>0];

// ================================ GESTION DES DATES ===================================================================

//if both dates sent 
if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) !='' and preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to']) !='' ){
	$listAND[] = '( date_complete >= ? AND date_complete <= ? ) ';
	$listPARAM[] = $_GET['from'] . ' 00:00:00';
	$listPARAM[] = $_GET['to'] . ' 23:59:59';
// If only date from sent
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['from']) !='' ){
	$listAND[] = '( date_complete >= ? ) ';
	$listPARAM[] = $_GET['from'] . ' 00:00:00';
// If only date to sent
} else if( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_GET['to'] ) !='' ){
	$listAND[] = '( date_complete <= ? ) ';
	$listPARAM[] = $_GET['to'] . ' 23:59:59';
} else {
    // Cherche seulement le dernier mois
	$listAND[] = '( date_complete >= ? ) ';
	$listPARAM[] = date("Y-m-d",strtotime("-30 days")) . ' 00:00:00';
}

// ======================   REQUETE  =================================
/*
SELECT * FROM shop_cart_done WHERE ( date_complete >= '2021-08-01 00:00:00' AND date_complete <= '2021-08-31 23:59:59' ) 
*/
//Le AND  est ajouté qu'à partir du 2ieme
$listAndToString = implode(" and ",$listAND);

$result = query("SELECT date(date_complete) as date_complete, id_cart , id_client
                 FROM animoetc_db.shop_cart_done
                 WHERE $listAndToString ",$listPARAM,$mysqli); 
//Set number of rows find
$data["totalCommande"] = $result->num_rows;

while( $uneLigneCart = $result->fetch_assoc() ) {
    $data["row"][$uneLigneCart["id_cart"]] = $uneLigneCart;
}   

// ======================   GESTION TRI  =================================

$listColonneTri = ['date_complete','id_cart','id_client'];

if ( !in_array($_GET["orderby"],$listColonneTri) ){
	//Set by default
	$_GET["orderby"] = $listColonneTri[0];
}
if ( $_GET["sens"] == 'asc' ){
	$_GET["sens"] = "asc";
} 
else {
	$_GET["sens"] = "desc";
}

usort($data["row"], function($a,$b) {
    if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ) {
    	return ($_GET["sens"] == "desc") ? 1 : -1;//1 c plus petit, -1 plus grand 
    }
    elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ) {
    	return ($_GET["sens"] == "desc") ? -1 : 1;
    }
    return 0;
});  

// ========================== GESTION  PDF et Excel ======================  

if ( isset($_GET["getFile"]) &&  $_GET["getFile"] == "1"  ){
    
  	require_once(__DIR__."/../req/print.php"); 
	
	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}
	//________________________ NIVEAU 1  ________________
	
	$titre = L("rapport Commande Web","o");
	
    //NIVEAU 2 DU FICHIER 
	$listSoustitre = [];
	
	if ( $_GET['from'] and $_GET['to'] ){
		$listSoustitre[] = ["Date :", L("du") . " " . formatDateUTF8nonHTML( $_GET['from'] ) . " " . L("au") . " " . formatDateUTF8nonHTML( $_GET['to'] ) ];
	} elseif( $_GET['from'] ) {
		$listSoustitre[] = ["Date :", L("du") . " " . formatDateUTF8nonHTML( $_GET['from'] ) ];
	} elseif( $_GET['to'] ) {
		$listSoustitre[] = ["Date :", L("jusqu'au") . " " . formatDateUTF8nonHTML( $_GET['to'] ) ];
	} else {
		$listSoustitre[] = ["Date :", L("du") . " " . formatDateUTF8nonHTML( empty(htmlentities($_GET["from"])) ? date("Y-m-d", strtotime(date("Y-m-d"). ' - 30 days')) : htmlentities($_GET["from"]) ) . " " . L("au") . " " 
	                                              . formatDateUTF8nonHTML( empty(htmlentities($_GET["to"])) ? date("Y-m-d") : htmlentities($_GET["to"]) ) ];
	}
	$listSoustitre[] = ["Total des commandes :", " " . "(".$data["totalCommande"].")"  ];

    //Positionnement des clonnes
	$listEnteteColonne = [
		[
			["text"=>L('Id client',"o"),    "width"=>60,"align"=>"L"],
			["text"=>L('ID commande',"o"),  "width"=>60,"align"=>"C"],
			["text"=>L("date d'achat","o"), "width"=>60,"align"=>"R"],
		]
	];
	// TJRS UTILISER debutSection3, PAS 1 ni 2!									
	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	//@parm: Prend les config de l'indice du tableau ($listEnteteColonne) 
	$rapport->setInfoCols(-1);
	//$rapport->Ln(1);
	$isfirst = true;
	//________________________ NIVEAU 2  ________________
	if(count($data) > 0){
		foreach ($data["row"]  as $cle => $value) { 
    	    
    		if (!$isfirst){
    			$rapport->Ln(1);
    		}
    		$isfirst = false;
    		$rapport->SetFont('helvetica', 'B', 9);
    		//Positionnement des lignes 
          	$rapport->listLigneEnteteColonne = [
        		[ 
        		    ["text"=>"","width"=>60,"align"=>"L"],
        		    ["text"=>"","width"=>60,"align"=>"C"],
        	    	["text"=>"","width"=>60,"align"=>"R"],
        		]
        	];  		
    	    $listChamps = [];
			$listChamps[] = $value["id_client"];
			$listChamps[] = $value["id_cart"];
			$listChamps[] = formatDateUTF8nonHTML($value["date_complete"]);
			$rapport->writeLigneRapport3wrap( $listChamps );
		}
	}
	ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");      
}


//echo '<pre>' , print_r($data) , '</pre>';

?>
<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
	    <? /* ______________________ TITLE ____________________________*/?>
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-table icon-mr fs-4"></i>
			<span class="hidden-xs-down"> List Commande Web</span>
		</h1>
		<? /* ______________________ LIEN  PDF & EXCEL ____________________________*/?>
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
		<div class="print-only">
			<div class="px-3">
				<h5>Animo etc <?= getInfoMag("succursale")?></h5>
			</div>
		</div>
	</div>
	<!-- End SubHeader-->
	<!-- Start Content-->
	<div class="row pl-3 pr-3 mb-3 mt-3 print-top">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<!--Start Panel-->
			<div class="panel c-white-dark pb-0">
				<div class="panel-body">
					<div class="panel bgc-white-dark transition visible pb-2">
                        <div class="panel-body panel-body-p">
							<div class="page-size-table">
								<div class="bootstrap-table">
									<div class="fixed-table-toolbar no-print">
									    <? /* _______________ FORM _________*/?>
										<form method="GET">
										    <!-- 
											<input type="hidden" name="sens" value="<?= ""//$_GET["sens"] ?>">
											<input type="hidden" name="orderby" value="<?= ""//$_GET["orderby"]?>">    
										    -->
											<input type="hidden" name="p" value="<?= $_GET["p"]?>">
											<div class="row pb-2" >
												<div class="col-md-8">
													<div class="input-group bs-datepicker input-daterange picker-range">
														<input type="text" class="form-control" name="from"  value="<?= empty(htmlentities($_GET["from"])) ? date("Y-m-d", strtotime(date("Y-m-d"). ' - 30 days')) : htmlentities($_GET["from"]) ?>">
														<span class="input-group-addon px-3"><?= L("to"); ?></span>
														<input type="text" class="form-control" name="to" value="<?= empty(htmlentities($_GET["to"])) ? date("Y-m-d") : htmlentities($_GET["to"]) ?>">
													</div>
												</div>
											</div>
											<div class="columns columns-right btn-group pull-right no-print">
												<button type="submit" class="applyBtn btn btn-small btn-success"><?= L('afficher');?></button>
											</div>
										</form>
									</div>
									<?php
									/* _______________ WEB DATE _________*/
									if ( $data ){ ?>
                                    <div class="alert alert-info mb-3" role="alert" style="border-style: solid; border-color: #95a5a6 ; width:290px; height: 50px" >
                                      	<h4 class="mb-3">Total des commandes : <strong><?= htmlspecialchars($data["totalCommande"]) ?></strong> </h4>
                                    </div>
		                            <? /* _______________ TABLE _________*/?>
                                    <div class="fixed-table-container table-no-bordered" style="padding-bottom: 0px;">
                                        <div class="fixed-table-body" style="min-height: 200px;">
                                            <table id="tableListToilettage" class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align:left">
                                                            <a href="index.php?<?= rebuildQueryString(['orderby'=>'id_client','sens'=>($_GET["orderby"] == 'id_client' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                                                                <?= "ID client" ?> 
                                                                <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'id_client' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
                                                            </a>  
                                                        </th>
                                                        <th style="text-align:center">
                                                            <a href="index.php?<?= rebuildQueryString(['orderby'=>'id_cart','sens'=>($_GET["orderby"] == 'id_cart' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                                                                <?= "ID commande" ?>  
                                                                <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'id_cart' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
                                                            </a>  
                                                        </th>
                                                        <th style="text-align:center">
                                                            <a href="index.php?<?= rebuildQueryString(['orderby'=>'date_complete','sens'=>($_GET["orderby"] == 'date_complete' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
                                                                <?= L("Date d'achat"); ?>  
                                                                <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'date_complete' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
                                                            </a>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($data["row"]  as $cle => $value) { ?>
                                                    <tr>
                                                        <td style="text-align:left"> 
                                                           <abbr title="  Cliquez pour voir les détails du client"><a href="?p=client_detail&id=<?= htmlspecialchars($value["id_client"]) ?>"><?= $value["id_client"] ?></a>  </abbr> 
                                                        </td>
                                                        <td style="text-align:center"> 
                                                            <?= htmlspecialchars($value["id_cart"]) ?>
                                                        </td>
                                                        <td style="text-align:center;">
                                                            <?= htmlspecialchars($value["date_complete"]) ?> 
                                                        </td>
                                                    </tr>
                                                    <?php }//fin foreach ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
								    <?php }//fin if ?>
									<!-- END PAGE CONTENT-->
								</div>
							</div>
                        </div>
					</div>
				</div>
			</div>
		</section>
	</div>
	<!-- End Content-->
</section>



