<?php

ini_set("memory_limit","256M");
set_time_limit(300);

$arrayMois = array("01"=>"Janvier","02"=>"Février","03"=>"Mars","04"=>"Avril","05"=>"Mai","06"=>"Juin",
                     "07"=>"Juillet","08"=>"Août","09"=>"Septembre","10"=>"Octobre","11"=>"Novembre","12"=>"Décembre");
$data = [];  
$dateStart = $dateEnd = "";


// =================================== GESTION DES ACCES  =================================================

//Pour les affichages seulement
$allMag = [];
$queryAllMag = query("select * from MAGASIN where caisse_db is not null order by M_NOM asc",[],$mysqli);

while( $uneLigneMag = $queryAllMag->fetch_assoc() ){
	$allMag[$uneLigneMag["ID_MAGASIN"]] = $uneLigneMag;
}
//Stock les IDs des magasin selon le niveau d'accès 
$listID_MAGASINcanaccess = [];
if ( $_SESSION["utilisateur"]["security"] >= 2 ){
	$listID_MAGASINcanaccess = array_keys($_SESSION["magasins"]);
} else {
	$listID_MAGASINcanaccess = array_keys($allMag);
}
//Stock le sélect des magasins et verifie si le user a le droit d'accès pour ces magasins
$listMagasinAvecDroitAcces = [];
if ( isset($_GET["ID_MAGASIN"]) ){
	foreach( $_GET["ID_MAGASIN"] as $ID_MAGASIN ) {
		if ( in_array($ID_MAGASIN,$listID_MAGASINcanaccess) ){
			$listMagasinAvecDroitAcces[] = $ID_MAGASIN;
		}
	}
}
//Si pas de sélection de magasin
if ( sizeof($listMagasinAvecDroitAcces) < 1 ){
	//Affiche par défaut toutes les magasin dont le user à droit d'acces
	$listMagasinAvecDroitAcces = $listID_MAGASINcanaccess;
}

// =================================== RÉCOLTE DE DONNÉES  =================================

//Pour chaque magasin...
foreach ($listMagasinAvecDroitAcces as $ID_MAGASIN ){
    
	$data[$ID_MAGASIN] = ["lignes"=>[],"nbrefact"=>0,"totalsanstaxe"=>0,"nbFactToCompare"=>0,"totalsanstaxeToCompare"=>0];  
	// Accès la BD de chaque magasin
	$dbAnimoCaisse->select_db($allMag[$ID_MAGASIN]["caisse_db"]);
	
    // =================================== GESTION DE DATE ==================================

    // SI pas de date envoyée, set data de départ et date de fin...
    
	if(!preg_match('#^\d+$#',$_GET["from_mois"])){
	    $_GET["from_mois"] = date('m',time());   
	}
	if(!preg_match('#^\d+$#',$_GET["from_annee"])){
	    $_GET["from_annee"] = date('Y',strtotime('-1 year'));  
	}
	if(!preg_match('#^\d+$#',$_GET["to_mois"])){
	    $_GET["to_mois"] = date('m',strtotime(' - 1 month'));  
	}
	if(!preg_match('#^\d+$#',$_GET["to_annee"])){
	    $_GET["to_annee"] = date('Y',time());
	}

    // Set autant la date par défault que celle Postée 
    $dateStart = $_GET["from_annee"]."-".$_GET["from_mois"]."-01";         
    //Extrait le nombre de jours pour le mois et année: "t" égal le nombre de jours dans le mois
    $nbJours = date("t", strtotime($_GET["to_annee"]."-".$_GET["to_mois"]) );
    // Set date fin par default
    $dateEnd = $_GET["to_annee"]."-".$_GET["to_mois"]."-".$nbJours; 
    
    $daterange = " where (facture.date_insert >= '{$dateStart} 00:00:00' AND facture.date_insert <= '{$dateEnd} 23:59:59') ";    //vex($daterange); //die(); where (facture.date_insert >= \'2020-01-01 00:00:00\' AND facture.date_insert <= \'2020-12-31 23:59:59\') '
  
    if(isset($_GET["from_mois"]) && isset($_GET["to_mois"]) ){ 
    	$enonce = "SELECT sum(facture.soustotal) `totalsanstaxe`, sum(facture.grandtotal) `totalavectaxe`, count(facture.id_facture) `nbrefact`, facture.date_insert
    			   FROM facture".$daterange."
    			   GROUP BY Year(facture.date_insert), Month(facture.date_insert)";
    	$resultFactItem = query($enonce,[],$dbAnimoCaisse);   	    
	}	    
	
    // =================================== ANNÉE NORMAl  ==================================
    
	while( $uneLigneFact = $resultFactItem->fetch_assoc() )  {  
        
        $cle     = date("Y-m", strtotime($uneLigneFact["date_insert"]) );  
        $annee   = date("Y", strtotime($uneLigneFact["date_insert"]) );
        //$nbJours = date( "t", strtotime($uneLigneFact["date_insert"]) );

        //Définitions 
  		if(!isset($data[$ID_MAGASIN]["lignes"][$cle]) ){ 
  		    
            $data[$ID_MAGASIN]["lignes"][$cle]=[];
            $data[$ID_MAGASIN]["lignes"][$cle]["mois"]                   = ""; 
            $data[$ID_MAGASIN]["lignes"][$cle]["annee"]                  = "";
            $data[$ID_MAGASIN]["lignes"][$cle]["nbrefact"]               = 0;
    		$data[$ID_MAGASIN]["lignes"][$cle]["totalsanstaxe"]          = 0;
            $data[$ID_MAGASIN]["lignes"][$cle]["moisToCompare"]          = "";
            $data[$ID_MAGASIN]["lignes"][$cle]["anneeToCompare"]         = "";
            $data[$ID_MAGASIN]["lignes"][$cle]["anneeMois"]              = "";
            $data[$ID_MAGASIN]["lignes"][$cle]["difference"]             = 0;
            $data[$ID_MAGASIN]["lignes"][$cle]["nbFactToCompare"]        = 0; 
            $data[$ID_MAGASIN]["lignes"][$cle]["nbJours"]                = 0; 
            $data[$ID_MAGASIN]["lignes"][$cle]["nbJoursToCompare"]       = 0; 

		}		
	
		$data[$ID_MAGASIN]["lignes"][$cle]["mois"]            = date("m", strtotime($uneLigneFact["date_insert"]) );
		$data[$ID_MAGASIN]["lignes"][$cle]["annee"]           = $annee;
		$data[$ID_MAGASIN]["lignes"][$cle]["nbrefact"]        = $uneLigneFact["nbrefact"]; 
		$data[$ID_MAGASIN]["lignes"][$cle]["totalsanstaxe"]   = $uneLigneFact["totalsanstaxe"];
        $data[$ID_MAGASIN]["lignes"][$cle]["anneeMois"]       = $cle;
        $data[$ID_MAGASIN]["lignes"][$cle]["difference"]      = $uneLigneFact["totalsanstaxe"];
        $data[$ID_MAGASIN]["lignes"][$cle]["moisToCompare"]   = date("m", strtotime($uneLigneFact["date_insert"]) );
        $data[$ID_MAGASIN]["lignes"][$cle]["anneeToCompare"]  = $annee-1;
        $data[$ID_MAGASIN]["lignes"][$cle]["nbJoursToCompare"] = date( "t", strtotime( ($annee-1)."-".$data[$ID_MAGASIN]["lignes"][$cle]["mois"]) );
        $data[$ID_MAGASIN]["lignes"][$cle]["nbJours"]         = date( "t", strtotime($uneLigneFact["date_insert"]) );
        $data[$ID_MAGASIN]["totalsanstaxe"]                   += $uneLigneFact["totalsanstaxe"];
        $data[$ID_MAGASIN]["nbrefact"]                        += $uneLigneFact["nbrefact"]; 		
		
	}// fin while
     
    // =================================== ANNÉE AVEC COMPARAISON  =======================
    
	if ( isset( $_GET['toCompare']) )	{   
         	
        //DATA DE DÉBUT...		
        $fromanneeToCompare    = $_GET["from_annee"] - 1; 
        $dateStartToCompare  = $fromanneeToCompare."-".$_GET["from_mois"]."-01";
        //DATA DE FIN...
        $toanneeToCompare    = $_GET["to_annee"] - 1;    
        $nbJoursToCompare    = date("t", strtotime($toanneeToCompare."-".$_GET["to_mois"]) );
        $dateEndToCompare    = $toanneeToCompare."-".$_GET["to_mois"]."-".$nbJoursToCompare;	 
       
    	$daterangeCompare    = " where (facture.date_insert >= '{$dateStartToCompare} 00:00:00' AND facture.date_insert <= '{$dateEndToCompare} 23:59:59') ";  
    	
		$enonce = "SELECT sum(facture.soustotal) `totalsanstaxe`, sum(facture.grandtotal) `totalavectaxe`, count(facture.id_facture) `nbrefact`, facture.date_insert
				   FROM facture".$daterangeCompare."
				   GROUP BY Year(facture.date_insert), Month(facture.date_insert)";
		$resultFactItemCompare = query($enonce,[],$dbAnimoCaisse); 
		
        $arreyCle = [];
        
    	while( $uneLigneFactCompare = $resultFactItemCompare->fetch_assoc() )  {     
        
            //Pour stocker au meme indice que "lignes"
		    $arreyCle = explode("-", date("Y-m", strtotime($uneLigneFactCompare["date_insert"]) ) );
            $cle = $arreyCle[0]+1 ."-". $arreyCle[1];
            $fromanneeToCompare = date("Y", strtotime($uneLigneFactCompare["date_insert"]) );
            
        	if(!isset($data[$ID_MAGASIN]["lignes"][$cle]) ){
        	    
                $data[$ID_MAGASIN]["lignes"][$cle]=[];
                $data[$ID_MAGASIN]["lignes"][$cle]["mois"]                   = ""; 
                $data[$ID_MAGASIN]["lignes"][$cle]["annee"]                  = "";
                $data[$ID_MAGASIN]["lignes"][$cle]["nbrefact"]               = 0;
        		$data[$ID_MAGASIN]["lignes"][$cle]["totalsanstaxe"]          = 0;
                $data[$ID_MAGASIN]["lignes"][$cle]["moisToCompare"]          = "";
                $data[$ID_MAGASIN]["lignes"][$cle]["anneeToCompare"]         = "";
                $data[$ID_MAGASIN]["lignes"][$cle]["anneeMois"]              = "";
                $data[$ID_MAGASIN]["lignes"][$cle]["difference"]             = 0;
                $data[$ID_MAGASIN]["lignes"][$cle]["totalsanstaxeToCompare"] = 0;
                $data[$ID_MAGASIN]["lignes"][$cle]["nbFactToCompare"]        = 0; 
                $data[$ID_MAGASIN]["lignes"][$cle]["nbJours"]                = 0;
                $data[$ID_MAGASIN]["lignes"][$cle]["nbJoursToCompare"]       = 0; 
        	}
            
    		$data[$ID_MAGASIN]["lignes"][$cle]["mois"]                   = date("m", strtotime($uneLigneFactCompare["date_insert"]) );
    		$data[$ID_MAGASIN]["lignes"][$cle]["annee"]                  = date('Y',strtotime($uneLigneFactCompare["date_insert"].'+1 year')); 
            $data[$ID_MAGASIN]["lignes"][$cle]["anneeMois"]              = $cle;
            $data[$ID_MAGASIN]["lignes"][$cle]["moisToCompare"]          = date("m", strtotime($uneLigneFactCompare["date_insert"]) );
            $data[$ID_MAGASIN]["lignes"][$cle]["anneeToCompare"]         = date('Y',strtotime($uneLigneFactCompare["date_insert"]));
            $data[$ID_MAGASIN]["lignes"][$cle]["nbFactToCompare"]        = $uneLigneFactCompare["nbrefact"];
            $data[$ID_MAGASIN]["lignes"][$cle]["totalsanstaxeToCompare"] = $uneLigneFactCompare["totalsanstaxe"];
            $data[$ID_MAGASIN]["lignes"][$cle]["nbJoursToCompare"]       = date( "t", strtotime($uneLigneFactCompare["date_insert"]) );
            $data[$ID_MAGASIN]["lignes"][$cle]["difference"]             -= $data[$ID_MAGASIN]["lignes"][$cle]["totalsanstaxeToCompare"];
            $data[$ID_MAGASIN]["nbFactToCompare"]                        += $uneLigneFactCompare["nbrefact"]; 
            $data[$ID_MAGASIN]["totalsanstaxeToCompare"]                 +=$uneLigneFactCompare["totalsanstaxe"];
  
	    }//fin while
	  
    }//fin if   
    
	// =============================== GESTION DE TRI ====================================

	$listTriPosible = ["anneeMois","nbrefact","totalsanstaxe","nbFactToCompare","totalsanstaxeToCompare", "difference"];
	
	// SI pas d'ordre définit  
	if ( !in_array($_GET["orderby"],$listTriPosible) )	{
	    //Set le $_GET avec l'ordre par mois
		$_GET["orderby"] = $listTriPosible[0];
	}
	if ( $_GET["sens"] == 'desc' )	{
		$_GET["sens"] = "desc";
	} else 	{
		$_GET["sens"] = "asc";
	}
	
    /*
    La methode va comparer, à partir de la clé "lignes", les valeurs selon l'ordre de tri choisie.
    @parm1: le tableau à trier
    @parm2: $a et $b font reference aux valeurs de la clé qui sont comparés 2 à la fois.   
    */
	uasort( $data[$ID_MAGASIN]["lignes"], function($a,$b)	{ 
	    
		if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){  
			return ($_GET["sens"]=="desc") ? 1 : -1; 
		}elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
			return ($_GET["sens"]=="desc")? -1 : 1;
		}
		return 0;
	});   
	
}//foreach

/*
echo '<pre>';
echo '$_REQUEST '.htmlspecialchars(print_r($_REQUEST, true)); 
echo'<br><br><br>';
echo '$data '.htmlspecialchars(print_r($data, true)); 
echo '</pre>'; 
*/

// =================================== GESTION PDF EXCEL =================================================

if ( $_GET["getFile"] == "1" and $data ){
	
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}
	
	//  =================== DÉFINITION DES DONNES =======================
	
	//Titre 
	$titre = L("rap_mois","o");
	//Sous titre
	$listSoustitre = [];

	if ( !empty($_GET['from_mois']) || !empty($_GET['from_annee']) || !empty($_GET['to_mois']) || !empty($_GET['to_annee'])  ){  
		$listSoustitre[] = ["date", L("du") . " " . formatDateUTF8nonHTML( $dateStart ) . " " . L("au") . " " . formatDateUTF8nonHTML( $dateEnd ) ];
	} 
	
	//Tous les magasin du select
	$listNomMag = [];
	foreach( $listMagasinAvecDroitAcces as $ID_MAGASIN ){
		$listNomMag[] = $allMag[$ID_MAGASIN]["M_NOM"];
	}
	//Rassemble les noms de toutes magasins séparés par virgule
	$listSoustitre[] = ["magasin(s) :", implode(", ",$listNomMag) ];
	
	// Verification de la demande de comparaison de l'année
	if($_GET["toCompare"] == 1){ 
	
    	$listEnteteColonne = [
    			[ 
    				["text"=>L("mois","o"),"width"=>30,"align"=>"L"],
    				["text"=>L("nbrefact",'o'),"width"=>30,"align"=>"C"],
    				["text"=>L("total",'o'),"width"=>25,"align"=>"R"],
    				["text"=>L("mois","o"),"width"=>30,"align"=>"L"],
    				["text"=>L("nbrefact",'o'),"width"=>15,"align"=>"C"],
    				["text"=>L("total",'o'),"width"=>30,"align"=>"R"],
    				["text"=>L("difference",'o'),"width"=>30,"align"=>"R"],
    			]
    	];    
	}else{
    	$listEnteteColonne = [
    			[ 
    				["text"=>L("mois","o"),"width"=>60,"align"=>"L"],
    				["text"=>L("nbrefact",'o'),"width"=>60,"align"=>"C"],
    				["text"=>L("total",'o'),"width"=>60,"align"=>"R"],
    			]
    	];	    
	}	
    				
	//Écrire sur le fichier
	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);

	// ======================= ÉCRITURE DES DONNÉS ================================
		
	$first = true;
	foreach ($data as $ID_MAGASIN => $dataUnMagasin){
	    
		if(!$first){
			$rapport->AddPage();
		}else{
			$first = false;
		}
		
		$rapport->SetFont("helvetica","B",14);
		$rapport->Cell(0,0,$allMag[$ID_MAGASIN]["M_NOM"], 0, 1);  
		
		foreach ($data[$ID_MAGASIN]["lignes"]  as  $ligneValue)
		{
			$listChamps = [];
			
			if($_GET["toCompare"] == 1){ 
		    	$listChamps[0] = utf8_encode(strftime("%B %Y", strtotime(sprintf("%04d-%02d-02",$ligneValue["annee"],$ligneValue["mois"]))));
    			$listChamps[1] = $ligneValue["nbrefact"];
    			$listChamps[2] = nfs($ligneValue["totalsanstaxe"]);
		    	$listChamps[3] = utf8_encode(strftime("%B %Y", strtotime(sprintf("%04d-%02d-02",$ligneValue["anneeToCompare"],$ligneValue["mois"]))));
    			$listChamps[4] = $ligneValue["nbFactToCompare"];
    			$listChamps[5] = nfs($ligneValue["totalsanstaxeToCompare"]);
    			$listChamps[6] = nfs($ligneValue["difference"]);
			}else{
    			$listChamps[0] = utf8_encode(strftime("%B %Y", strtotime(sprintf("%04d-%02d-02",$ligneValue["annee"],$ligneValue["mois"]))));
    			$listChamps[1] = $ligneValue["nbrefact"];
    			$listChamps[2] = nfs($ligneValue["totalsanstaxe"]);
			}
			$rapport->writeLigneRapport3wrap( $listChamps );  
		}
		
		$listTOTAL = [];	
		
		if($_GET["toCompare"] == 1){ 
    		$listTOTAL[] = "";
    		$listTOTAL[] = nfsnd($data[$ID_MAGASIN]["nbrefact"]);
    		$listTOTAL[] = nfs($data[$ID_MAGASIN]["totalsanstaxe"]);
    		$listTOTAL[] = "";
    		$listTOTAL[] = nfsnd($data[$ID_MAGASIN]["nbFactToCompare"]);
    		$listTOTAL[] = nfs($data[$ID_MAGASIN]["totalsanstaxeToCompare"]);
    		$listTOTAL[] = nfs($data[$ID_MAGASIN]["totalsanstaxe"] - $data[$ID_MAGASIN]["totalsanstaxeToCompare"]);
		}else{
	    	$listTOTAL[] = "";
    		$listTOTAL[] = nfsnd($data[$ID_MAGASIN]["nbrefact"]);
    		$listTOTAL[] = nfs($data[$ID_MAGASIN]["totalsanstaxe"]);		    
		}
		$rapport->writeLigneGrandTotal($listTOTAL, [false,true,true,false,true,true,true] );
		
	}//fin foreach

	ob_clean();
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	die("");


//fin PDF EXCEL    
} else {
	?>
	<section id="main" class="main-wrap bgc-white-darkest print" role="main">
		<!-- Start SubHeader-->
		<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
			<h1 class="page-title pull-left fs-4 fw-light">
				<i class="fa fa-bar-chart icon-mr fs-4"></i>
				<span class="hidden-xs-down"><?= L("rap_mois","o");?></span>
			</h1>
			<h1 id="date_label" class="page-title pull-right fs-4 fw-light print-only"></h1>
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
		<div class="print-only">
			<div class="px-3">
				<h5>Animo etc <?= getInfoMag("succursale")?></h5>
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
										    <?php // ================= form ===========================  ?>
											<form action="" method="get">
												<input type="hidden" name="p" value="<?= $_GET["p"]?>">
												<input type="hidden" name="sens" value="<?= $_GET["sens"] ?>">
												<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">
												<?php // ================= SELECT DATES ===========================  ?>
												<div class="row" style="margin-bottom:15px;">
													<div class="col-md-5">
														<div class="input-group ">
                                                            <!-- ===============================================  -->
                                                            <select class="form-control" name="from_mois" required>
                                                            <option  value='' selected>Mois...</option>
                                                            <?php 
                                                            foreach ($arrayMois as $key =>$value)  { ?>
                                                                <!-- echo "<option value='$key' >"  .$value. "</option>";-->
                                                                <option value="<?php echo $key?>" <?php if($key == $_GET["from_mois"]){ print ' selected'; }?>  ><?php echo $value?></option>
                                                            <?php  }?>
                                                            </select>
                                                            <!-- ================== SELECT from année =====================  -->    
                                                            <?php 
                                                            $already_selected_value = date('Y');
                                                            $earliest_year = 2014;
                                                            ?>
                                                            <select class="form-control" name="from_annee" required>
                                                            <?php    
                                                            foreach (range(date('Y'), $earliest_year) as $year) { ?>
                                                                <option value="<?php echo $year ?>" <?php if($year == $_GET["from_annee"]){ print ' selected'; }?>  ><?php echo $year ?></option>
                                                            <?php  } ?>
                                                            </select>
                                                            <!-- ===============================================  -->  
                                    					    <span class="input-group-addon px-3"><?= L("to"); ?></span>
                                    					    <!-- ===============================================  -->  
                                                            <select class="form-control" name="to_mois" required>
                                                            <option  value='' selected>Mois...</option>
                                                            <?php 
                                                            foreach ($arrayMois as $key =>$value)  { ?>
                                                                <!-- echo "<option value='$key' >"  .$value. "</option>";-->
                                                                <option value="<?php echo $key?>" <?php if($key == $_GET["to_mois"]){ print ' selected'; }?>  > <?php echo $value?></option>
                                                            <?php  }?>
                                                            </select>
                                                            <!-- ================ SELECT to année =======================  --> 
                                                            <?php 
                                                            $already_selected_value = date('Y');
                                                            $earliest_year = 2014;
                                                            ?>
                                                            <select class="form-control" name="to_annee" required>
                                                            <?php    
                                                            foreach (range(date('Y'), $earliest_year) as $year) { ?>
                                                                <option value="<?php echo $year ?>" <?php if($year == $_GET["to_annee"]){ print ' selected'; }?>  ><?php echo $year ?></option>
                                                            <?php  } ?>
                                                            </select>
														</div>
													</div>
												</div>
												<?php // ===================== SELECT MAGASINS =======================  ?>
												<?php if( sizeof($listID_MAGASINcanaccess) > 1 ){ $isMultiMag = true;?>
												<div class="row">
													<div class="col-12">
														<div class="pt-3">
															<select class="ui fluid normal multi-selection select-dropdown form-control" name="ID_MAGASIN[]" multiple>
																<?php
																foreach( $listID_MAGASINcanaccess as $ID_MAGASIN){
																	$infoMag = query("select * from MAGASIN where ID_MAGASIN = ?",[$ID_MAGASIN,],$mysqli)->fetch_assoc();
																	printf("<option value='%s'%s>%s</option>", $ID_MAGASIN,( in_array($ID_MAGASIN,$listMagasinAvecDroitAcces)?" selected":""),$infoMag["M_NOM"]);
																}
																?>
															</select>
															<button class="btn btn-xs" onclick="$(this).closest('div').find('.multi-selection').selectDropdown('set selected', <?= str_replace('"', "'",json_encode(array_map(strval,$listID_MAGASINcanaccess))) ?> )" type="button"><?= L("tous sélectionner") ?></button>
															<button class="btn btn-xs" onclick="$(this).closest('div').find('.multi-selection').selectDropdown('clear')" type="button"><?= L("tous dé-sélectionner") ?></button>
														</div>
													</div>
												</div>	
												<?php } ?>
												<br>
												<?php // ===================== checkbox =======================  ?>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" name="toCompare" value="1" <?= $_GET["toCompare"] == 1  ? 'checked' : '';?>  >
                                                    Option comparative : cette option compare mois au mois de l'année précédent.
                                                 </label>
												<?php // ===================== BOUTON submit =======================  ?>
												<div class="columns columns-right btn-group pull-right no-print">
													<button type="submit" class="applyBtn btn btn-small btn-success" name="SendFormAfficher"  id="btn_submit"><?= L('afficher');?></button>
												</div>
											</form>
										</div>
										<?php
										if ( $data )
										{
											foreach ($data as $ID_MAGASIN => $dataUnMagasin)     
											{ 
											?> 
												<br><br>
												<h3><b>Animo Etc <?= $allMag[$ID_MAGASIN]["M_NOM"]?></b></h3>
												<!--  ================================  TABLE ================================ -->
												<table  class="card-view-no-edit page-size-table table table-no-bordered table-condensed">
													<!-- ================================ TABLE EN-TETE ================================ -->
													<thead>
														<tr>
															<th>
															    <a href="index.php?<?= rebuildQueryString([ 'orderby'=>'anneeMois','sens'=>( $_GET["orderby"] == 'anneeMois' ? ( $_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"] )]) ?>"> 
															         <?= L("mois"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'anneeMois' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
															    </a>
														    </th>
															<th style="text-align:center">
															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'nbrefact', 'sens'=>($_GET["orderby"] == 'nbrefact' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
															         <?= L("nbrefact","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nbrefact' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
															    </a>
														    </th>
															<th style="text-align:right">
															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'totalsanstaxe', 'sens'=>($_GET["orderby"] == 'totalsanstaxe' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
															         <?= L("total","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalsanstaxe' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
															    </a>
														    </th>
														    
                                                            <!-- ================================ ZONE COMPARE ================================ -->
															<?php
														    if(isset( $_GET['toCompare'])){ ?>
      															<th style="text-align:center">
    															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'anneeMois', 'sens'=>($_GET["orderby"] == 'anneeMois' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															         <?= L("mois"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'anneeMois' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    															    </a>
    														    </th>  															
    															<th style="text-align:center">
    															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'nbFactToCompare', 'sens'=>($_GET["orderby"] == 'nbFactToCompare' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															         <?= L("nbrefact","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'nbFactToCompare' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    															    </a>
    														    </th>    															
    															<th style="text-align:right">
    															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'totalsanstaxeToCompare','sens'=>($_GET["orderby"] == 'totalsanstaxeToCompare' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															         <?= L("total","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'totalsanstaxeToCompare' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    															    </a>
    														    </th> 
    															<th style="text-align:right">
    															    <a href="index.php?<?= rebuildQueryString(['orderby'=>'difference', 'sens'=>($_GET["orderby"] == 'difference' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    															         <?= L("Différence","o"); ?>  <?= '<i class="fa fa-sort'.(($_GET["orderby"] == 'difference' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    															    </a>
    														    </th>     														    
														    <?php }	?>
														</tr>
													</thead>
													<!-- ================================ TABLE body ================================ -->
													<tbody>
													<?php  
                                                    foreach ( $dataUnMagasin["lignes"] as  $key => $value ) {  
                                                		$link = "?p=rap_ventes&from=" . $data[$ID_MAGASIN]["lignes"][$key]["anneeMois"]."-01&to=" .$data[$ID_MAGASIN]["lignes"][$key]["anneeMois"]."-".$data[$ID_MAGASIN]["lignes"][$key]["nbJours"]. "&ID_MAGASIN[]=". $ID_MAGASIN;
                                        		        ?>
                                            		    <tr>
                                            		        <td><a href="<?= $link ?>"><b> <?= $arrayMois[$data[$ID_MAGASIN]["lignes"][$key]["mois"]]." ".$data[$ID_MAGASIN]["lignes"][$key]["annee"]; ?></b></a>
                                        		            </td>   
                                                			<td style="text-align:center"><?=  $data[$ID_MAGASIN]["lignes"][$key]["nbrefact"] ?></td>
                                                			<td style="text-align:right"><?= formatPrix($data[$ID_MAGASIN]["lignes"][$key]["totalsanstaxe"])?></a></td>
                                                			<!-- ============================== CAS COMPARE ============================= -->
                                                			<?php if(isset( $_GET['toCompare'])){ 
                                                			  	$link = "?p=rap_ventes&from=" . $data[$ID_MAGASIN]["lignes"][$key]["anneeToCompare"]."-"
                                                                                			  	.$data[$ID_MAGASIN]["lignes"][$key]["moisToCompare"]."-01&to=" 
                                                                                			  	.$data[$ID_MAGASIN]["lignes"][$key]["anneeToCompare"]."-".$data[$ID_MAGASIN]["lignes"][$key]["moisToCompare"]."-"
                                                                                			  	.$data[$ID_MAGASIN]["lignes"][$key]["nbJoursToCompare"]. "&ID_MAGASIN[]=". $ID_MAGASIN;
                                            				?>
                                                                <td style="text-align:left ; padding-left: 100px;"><a href="<?= $link ?>"><b> 
                                                                <?= $arrayMois[$data[$ID_MAGASIN]["lignes"][$key]["moisToCompare"]]." ".$data[$ID_MAGASIN]["lignes"][$key]["anneeToCompare"]; ?></b></a></td>  
                                                				<td style="text-align:center"><?= $data[$ID_MAGASIN]["lignes"][$key]["nbFactToCompare"] ?></td>
                                                				<td style="text-align:right"><?= formatPrix($data[$ID_MAGASIN]["lignes"][$key]["totalsanstaxeToCompare"])?></td> 
                                                				<td style="text-align:right"><?= formatPrix($data[$ID_MAGASIN]["lignes"][$key]["difference"]) ?></td> 
                                                			<?php }	?>
                                                		</tr>
                                        		    <?php } ?>
													</tbody>
													<!-- ================================ table foot ================================ --> 
													<tfoot>
														<tr style="font-weight:bold">
															<td><!--Ligne vide--></td>
															<td style="text-align:center;"><?= $data[$ID_MAGASIN]["nbrefact"]; ?></td>
															<td style="text-align:right;"><?= formatPrix($data[$ID_MAGASIN]["totalsanstaxe"]); ?></td>
    												        <?php if(isset( $_GET['toCompare'])){ ?>
    												            <td><!--Ligne vide--></td>
    															<td style="text-align:center;"><?= $data[$ID_MAGASIN]["nbFactToCompare"]; ?></td>
    															<td style="text-align:right;"><?= formatPrix($data[$ID_MAGASIN]["totalsanstaxeToCompare"]); ?></td>	
    															<td style="text-align:right;"><?= formatPrix( $data[$ID_MAGASIN]["totalsanstaxe"] - $data[$ID_MAGASIN]["totalsanstaxeToCompare"] ); ?></td>												
    												        <?php }	?>
														</tr>
													</tfoot>
												</table>
											<?php
											}//fin foreach 1 
											?>
										<?php 
										}//fin if $data
										?>
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
} ?>