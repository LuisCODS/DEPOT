<?php 

$listLIKE = [];
$listPARAM = [];

if(isset($_GET["keywrd"]) && $_GET["keywrd"] != "" ){
    
    $_GET["keywrd"] =  htmlspecialchars( trim($_GET["keywrd"]) );
    $listKeyword = explode(' ',$_GET["keywrd"]);
    
    foreach($listKeyword as $keyword){
    $keyword = escapeForSqlLike($keyword);
        if ( $keyword ){
        	$keyword = '%'.$keyword.'%';
        	$listLIKE[] = "(ERREURPRIX.upc like ? or ERREURPRIX.code_four like ? or ERREURPRIX.fournisseur like ?  or ERREURPRIX.error_type like ? or ERREURPRIX.description like ? or ERREURPRIX.cost like ? or ERREURPRIX.date_sent like ?) ";
        	$listPARAM[] = $keyword;
        	$listPARAM[] = $keyword;
        	$listPARAM[] = $keyword;
        	$listPARAM[] = $keyword;
        	$listPARAM[] = $keyword;
        	$listPARAM[] = $keyword;
        	$listPARAM[] = $keyword;
        }
    }
    $queryErrorPrix = "SELECT ERREURPRIX.*, MAGASIN.M_NOM 
                       FROM ERREURPRIX
                       LEFT JOIN MAGASIN USING(ID_MAGASIN) 
                       WHERE ". implode(" AND ", $listLIKE) . " 
                       ORDER BY date_done is null DESC, date_sent DESC LIMIT 200";
    $resulErrorPrix = query($queryErrorPrix,$listPARAM,$mysqli);
}else{
    //Affichage par défault  
    $queryErrorPrix = "SELECT ERREURPRIX.*, MAGASIN.M_NOM 
                       FROM ERREURPRIX
                       LEFT JOIN MAGASIN USING(ID_MAGASIN) 
                       ORDER BY date_done is null DESC, date_sent DESC LIMIT 200"; 
    $resulErrorPrix = query($queryErrorPrix,[],$mysqli);         
}
// ============================= Form changement de prix  =============================

if(isset($_POST['formChangerPrix']) and $_POST['formChangerPrix'] == "1" && has_rights("admin_price_error")){   
    if(!empty($_POST['checkbox_list_erreur_id'])){
        // Loop to store values of individual checked checkbox.
        foreach($_POST['checkbox_list_erreur_id'] as $erreur_id){
            $arrayDB = array();
            $arrayDB["erreur_id"] = $erreur_id;
            $arrayDB["date_done"] = date('Y-m-d H:i:s');
            faireUpdate_i($arrayDB,"ERREURPRIX","erreur_id",$mysqli, 0);
        }
        // msg_output('Opération effectuée avec succès','success');
        $_SESSION['msnSuccess'] = " <div  style='text-align:center' class='alert alert-success' role='alert'>
                                    <h3 class='alert-heading'>Merci!</h3>
                                    <h4><i class='fa fa-info-circle fa-3x' aria-hidden='true'></i> Opération effectuée avec succès!</h4>
                                    </div>";
        redirect("index.php?p=price_error");
    }
}

$arrayData = [];

if($resulErrorPrix->num_rows > 0){
    // déplace le pointeur sur la ligne spécifiée par offset. 
    $resulErrorPrix->data_seek(0);
    while ($rowErrorPrix = $resulErrorPrix->fetch_assoc()) {
        //Stock chaque registre unique
        $arrayData[ $rowErrorPrix["erreur_id"] ] = $rowErrorPrix;
    }    
}

// ======================   GESTION TRI  =================================

$listTriPosible = ["date_done","error_type","description", "fournisseur", "date_sent","M_NOM","upc"];

if ( !in_array($_GET["orderby"],$listTriPosible) ){
	$_GET["orderby"] = $listTriPosible[0];
}
if ( $_GET["sens"] == 'desc' ){
	$_GET["sens"] = "desc";
} else {
	$_GET["sens"] = "asc";
}
//tri par nom  fournisseur en respectant les accents et/ou minuscule
if ( $_GET["orderby"] == "fournisseur"){
    usort( $arrayData, function($a,$b){
        if (strtoupper(iconv('UTF-8','ASCII//TRANSLIT',$a["fournisseur"])) > strtoupper(iconv('UTF-8','ASCII//TRANSLIT',$b["fournisseur"])) ){
        return ($_GET["sens"]=="desc")?-1:1;
        } elseif (strtoupper(iconv('UTF-8','ASCII//TRANSLIT',$a["fournisseur"])) < strtoupper(iconv('UTF-8','ASCII//TRANSLIT',$b["fournisseur"]))){
        return ($_GET["sens"]=="desc")?1:-1;
        }
        return 0;
    });  
//tri par nom  des magasins en respectant les accents et/ou minuscule
}else if ( $_GET["orderby"] == "M_NOM"){
    usort( $arrayData, function($a,$b){
        if (strtoupper(iconv('UTF-8','ASCII//TRANSLIT',$a["M_NOM"])) > strtoupper(iconv('UTF-8','ASCII//TRANSLIT',$b["M_NOM"])) ){
        return ($_GET["sens"]=="desc")?-1:1;
        } elseif (strtoupper(iconv('UTF-8','ASCII//TRANSLIT',$a["M_NOM"])) < strtoupper(iconv('UTF-8','ASCII//TRANSLIT',$b["M_NOM"]))){
        return ($_GET["sens"]=="desc")?1:-1;
        }
        return 0;
    }); 
//tri pour les autres champs
}else{
    uasort( $arrayData, function($a,$b){
        if ( $a[$_GET["orderby"]] < $b[$_GET["orderby"]] ){
            return ($_GET["sens"] == "desc") ? 1 : -1;//1 c plus petit, -1 plus grand 
        } elseif( $a[$_GET["orderby"]] > $b[$_GET["orderby"]] ){
            return ($_GET["sens"] == "desc") ? -1 : 1;
        }
        return 0;
    });  
}

$data = ["liste"=>[]];

//========================= Gestion PDF excel ===================================
if ( $_GET["getFile"] == "1"){
    
    foreach($arrayData as $cle => $rowErrorPrix){
        $data2 = array();
        $data2[0] = $rowErrorPrix['fournisseur'];
        $data2[1] = $rowErrorPrix['code_four'];
        $data2[2] = $rowErrorPrix['cost'];
        $data2[3] = $rowErrorPrix['upc'];
        $data2[4] = $rowErrorPrix['error_type'];
        $data2[5] = $rowErrorPrix['M_NOM'];
        $data2[6] = date("Y-m-d",strtotime($rowErrorPrix['date_sent']));
        $data2[7] = $rowErrorPrix['date_done'] != "" ? date("Y-m-d",strtotime($rowErrorPrix['date_done'])) : "";
        $data2[8] = $rowErrorPrix['date_done'] != "" ? "Oui" : "Non";
        $data["liste"][] = $data2;
    }

    require_once(__DIR__."/../req/print.php");
    
    if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
        $rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
    } else {
        $rapport = new RapportPDF( RPDF_PAGE_LANDSCAPE, RPDF_SHOWCOL_ALLPAGE );
    }
    
    $titre = L("Erreurs de prix","o");
    
    $listSoustitre = [];
    $listSoustitre[] = ["en date du", formatDateUTF8( date("Y-m-d") ) ];

    $listEnteteColonne = [
                            [ 
                                ["text"=>"Fournisseur","width"=>35,"align"=>"L"],
                                ["text"=>L("code four.",'o'),"width"=>35,"align"=>"L"],
                                ["text"=>L("coûtant",'o'),"width"=>25,"align"=>"R"],
                                ["text"=>L("upc",'o'),"width"=>30,"align"=>"L"],
                                ["text"=>L("Type",'o'),"width"=>40,"align"=>"L"],
                                ["text"=>L("Magasin",'o'),"width"=>25,"align"=>"L"],
                                ["text"=>"Date soum.","width"=>20,"align"=>"L"],
                                ["text"=>"Date compl.","width"=>20,"align"=>"L"],
                                ["text"=>"Completé","width"=>15,"align"=>"L"],
                            ],
                        ];
    
    $rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
    $rapport->setInfoCols(-1);

    foreach ( $data["liste"] as $uneLigneArticle){
    //foreach($arrayData as $cle => $uneLigneArticle){ 
        $listChamps = $uneLigneArticle;
        $rapport->writeLigneRapport3wrap( $listChamps );
    }
    ob_clean();
    $rapport->Output( formatFileName($titre).'.pdf', 'I');
    die("");
}

//echo '<pre>$arrayData ', print_r($data) , ' </pre>'; 
//echo '<pre>' , print_r($_REQUEST) , '</pre>';
?>
<section id="main" class="main-wrap bgc-white-darkest" role="main">
    <?php
    if (isset($_SESSION['msnSuccess'])) {
        echo $_SESSION['msnSuccess'];
    } 
    unset($_SESSION['msnSuccess']); 
    ?>
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
					<a class="nav-link clear-style aside-trigger" href="index.php?<?= rebuildQueryString([ "format"=>"pdf","getFile"=>"1","keywrd"=> $_GET['keywrd'] ]) ?>" target="_blank">
						<i class="fa fa-file-pdf-o "></i>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link clear-style aside-trigger" href="index.php?<?= rebuildQueryString(["format"=>"xlsx","getFile"=>"1","keywrd"=> $_GET['keywrd'] ]) ?>" target="_blank">
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
							    <!-- ============================== FORM Rechercher par mot clé  ================================--> 
        						<form action="" method="GET" >
        						    <input type="hidden" name="p" value="<?= $_GET["p"]?>">
        						    <input type="hidden" name="formRechercheMotCle" value="1">
        						    <!--  BOUTON zone admin -->
									<?php if(has_rights("admin_price_error")){ ?>
    									<div class="row">
    	   									<div class="col-md-2">
    	   									    <h4>
    	   									        <label> <?= L("mot clé","o") ?> </label>  
    	   									    </h4>
        									</div>
    	   									<div class="col-md-4">
        										<div class="form-group input-group">
        											<input type="text" class="form-control" name="keywrd" id="keywrd" placeholder="Nom fournisseur / Type d'erreur / UPC / Code four. / Cost " value="<?= $_GET['keywrd']?>">
        										</div>
        									</div>
        									<div class="col-md-6">
        										<div class="columns columns-right btn-group pull-right no-print">
            										<button type="submit" class="applyBtn btn btn-small btn-info"><?= L('Rechercher'); ?></button>
            									</div>	
        									</div>	
    									</div>
    									<div class="row mb-3 mt-3">
    									    <div class="col-md-2">
    											<a href="index.php?p=price_error">
                                                     <p style="font-size:17px">
                                                        Réinitialiser la page <i class="fa fa-undo fa-lg" aria-hidden="true"></i>
                                                     </p>
                                                </a> 			    
    									    </div>
    								    </div>
								    <?php } ?>
							    </form>
							    <hr>
							    <!-- ============================== FORM Erreurs de prix  ================================
							    $_GET pour le tri et $_POST pour l'envoie du checkbox
							    --> 
        						<form action="" method="POST" >
									<input type="hidden" name="sens"    value="<?= $_GET["sens"] ?>">
									<input type="hidden" name="orderby" value="<?= $_GET["orderby"]?>">
        						    <input type="hidden" name="p"       value="<?= $_GET["p"]?>">
        						    <input type="hidden" name="formChangerPrix" value="1">
        						    <!--  BOUTON zone admin -->
									<?php if(has_rights("admin_price_error")){ ?>
									<div class="row mb-5">
	   									<div class="col-md-10">
    				                	</div>
    									<div class="col-md-2">
    										<div class="columns columns-right btn-group pull-right no-print">
        										<button type="submit" class="applyBtn btn btn-small btn-success"><?= L('Compléter ');?></button>
        									</div>	
    									</div>	
									</div>
								    <?php } ?>
								    <!--  TABLE -->
        							<div>
        								<table class="table table-hover">
        									<thead>
        										<tr>
        											<th></th>
    											    <th> 
    												    <a href="index.php?<?= rebuildQueryString(['orderby'=>'fournisseur','sens'=>($_GET["orderby"]  == 'fournisseur' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])]) ?>"> 
    												        <?php echo $L['fournisseur'];?>
    												        <?= '<i class="fa fa-sort'.(($_GET["orderby"]  == 'fournisseur' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    												    </a>
    											    </th>
        											<th>
            										    <a href="index.php?<?= rebuildQueryString(['orderby'=>'upc','sens'=>($_GET["orderby"]  == 'upc' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    												        <?php echo $L["codeupc"];?>
    												        <?= '<i class="fa fa-sort'.(($_GET["orderby"]  == 'upc' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    												    </a>
        											</th>
        											<th>
            										    <a href="index.php?<?= rebuildQueryString(['orderby'=>'error_type','sens'=>($_GET["orderby"]  == 'error_type' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    												        <?php echo "Type d'erreur" ?>
    												        <?= '<i class="fa fa-sort'.(($_GET["orderby"]  == 'error_type' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    												    </a>
        											</th>
        											<th>
            										    <a href="index.php?<?= rebuildQueryString(['orderby'=>'M_NOM','sens'=>($_GET["orderby"]  == 'M_NOM' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    												        <?php echo L("magasin","o") ?>
    												        <?= '<i class="fa fa-sort'.(($_GET["orderby"]  == 'M_NOM' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    												    </a>
        											<th>
            										    <a href="index.php?<?= rebuildQueryString(['orderby'=>'date_sent','sens'=>($_GET["orderby"]  == 'date_sent' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    												        <?php echo $L["dateSoumission"];?>
    												        <?= '<i class="fa fa-sort'.(($_GET["orderby"]  == 'date_sent' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    												    </a>
        											</th>
        											<th>
            										    <a href="index.php?<?= rebuildQueryString(['orderby'=>'date_done','sens'=>($_GET["orderby"]  == 'date_done' ? ($_GET["sens"] == 'desc' ? 'asc' : 'desc') : $_GET["sens"])])?>"> 
    												        <?php echo $L["dateDone"];?>
    												        <?= '<i class="fa fa-sort'.(($_GET["orderby"]  == 'date_done' ? ($_GET["sens"] == 'desc' ? '-desc' : '-asc') : '')).'"></i>'?>
    												    </a>
    											    </th>
        											<th></th>
        										</tr>
        									</thead>
        									<tbody>
    										<?php 
    										if(count($arrayData) > 0)
    										{
    										    foreach($arrayData as $cle => $rowErrorPrix)
    										    {
        											$ico = '';
        											if($rowErrorPrix['date_done'] !='')
        											{
        											    $ico = '<span class="label label-sm label-icon label-success"><i class="fa fa-check"></i>&nbsp;</span>';
    											    }
           							                $pageName = "produits";  
           							       	 ?>        
               							                
       							             	<tr>
        											<td></td>
        											<td>
        												<b><?php echo $rowErrorPrix['fournisseur'];?></b><br/>
        												<a href="index.php?p=<?php echo $pageName; ?>&four=<?php echo $rowErrorPrix['code_four'];?>"><?php echo $rowErrorPrix['code_four'];?></a> (<?php echo $rowErrorPrix['cost'];?>)
        											</td>
        											<td>
        											    <a href="index.php?p=<?php echo $pageName; ?>&upc=<?php echo $rowErrorPrix['upc'];?>" class="popovers" data-trigger="hover" data-placement="bottom" data-html="true" data-content="<?php echo $rowErrorPrix['description'];?>" data-container="body"><?php echo $rowErrorPrix['upc'];?>
        											    </a>
    											    </td>
        											<td>
        											    <?php echo $rowErrorPrix['error_type'];?>
    											    </td>
        											<td>
        											    <a href="mailto:<?php echo $rowErrorPrix['M_EMAIL'];?>?subject=Information supplémentaire - erreur de prix&body=Fournisseur : <?php echo $rowErrorPrix['fournisseur'];?> %0D%0A Code Fournisseur : <?php echo $rowErrorPrix['code_four'];?> %0D%0A Code UPC : <?php echo $rowErrorPrix['upc'];?> %0D%0A Description : <?php echo $rowErrorPrix['description'];?> %0D%0A"><?php echo $rowErrorPrix['M_NOM'];?>
    											        </a>
    										        </td>
        											<td>
        											    <?php echo $rowErrorPrix['date_sent'];?>
    											    </td>
        											<td>
        											    <?php echo $rowErrorPrix['date_done'];?>
    											    </td>
        											<td class="droit">
        												<?php if(has_rights("admin_price_error")){ ?>
        												<div class="btn-group">
        													<?php if(!$rowErrorPrix['date_done']){?>
        													<span style="transform: scale(1.5);">
        													    <input type="checkbox" name="checkbox_list_erreur_id[]" title="<?= $L['markascompleted']; ?>" value="<?= $rowErrorPrix['erreur_id'];?>" >
        													</span>
        													<?php }?>
        												</div>
        												<?php }?>
        												<?php echo $ico;?>
        											</td>
        										</tr>  
										    <?php
									            } //fin foreach
                                             }//Fin if
                                            ?>
        									</tbody>
        								</table>
        							</div>
        						    <!--  BOUTON zone admin -->
									<?php if(has_rights("admin_price_error")){ ?>
									<div class="row mt-5">
	   									<div class="col-md-10">
    				                	</div>
    									<div class="col-md-2">
    										<div class="columns columns-right btn-group pull-right no-print">
        										<button type="submit" class="applyBtn btn btn-small btn-success"><?= L('Compléter');?></button>
        									</div>	
    									</div>	
									</div>
								    <?php } ?>
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




