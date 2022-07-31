<?php 
// ===================================== SAVE =====================================

if($_POST['mode'] == 'save' && preg_match('#^\d+$#',$_POST['id_fournisseur']) && has_rights('admin')){
    
    $arrayDB = array();
    $arrayDB['id_fournisseur'] = $_POST['id_fournisseur'];
    $arrayDB['comm_desc_fr'] = $_POST['comm_desc_fr'];
    $arrayDB['date_update'] = date('Y-m-d H:i:s');
    $arrayDB['hidden'] = !empty($_POST['hidden']) ? 1 : 0;
    $namefile = stockFileSFTP($file,"%f","private/img/animal");
    $getDescFour = query('SELECT * FROM FOURNISSEURS_DESC WHERE id_fournisseur = ?',[$arrayDB['id_fournisseur']],$mysqli);
    
    if (isset($_FILES["logo"]) && $_FILES["logo"]["name"] != "") {
        $arrayDB["logo"] = stockFileSFTP($_FILES["logo"],"%f","public_html/upimg/four");
    }
    
    if($getDescFour->num_rows === 1){
        faireUpdate_i($arrayDB,'FOURNISSEURS_DESC','id_fournisseur',$mysqli);
    }else{
        faireInsert_i($arrayDB,'FOURNISSEURS_DESC',$mysqli);
    }
    
}

// ===================================== DELETE  =====================================

if($_POST['mode'] == 'delete' && preg_match('#^\d+$#',$_POST['id_fournisseur']) && has_rights('admin')){
   
	$getFournisseurs = query('SELECT * FROM animoetc_caisse_default.fournisseur WHERE id_fournisseur = ? LIMIT 1',[$_POST['id_fournisseur']],$dbAnimoCaisseDefault);
	
	if($getFournisseurs->num_rows === 1){
	   $rowFourToUpDate = [];
	   $rowFourToUpDate["inactif"] = 1;
	   $rowFourToUpDate["date_update"] = date("Y-m-d H:i:s");
	   $rowFourToUpDate["id_fournisseur"] = $_POST['id_fournisseur'];
       faireUpdate_i($rowFourToUpDate,"animoetc_caisse_default.fournisseur","id_fournisseur",$dbAnimoCaisseDefault, 0);
	}
    msg_output('Fournisseur supprimé avec success','success');
}
?>

<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-industry icon-mr fs-4"></i>
			<span class="hidden-xs-down"> Fournisseurs</span>
		</h1>
	</div>
	<?php if(has_rights("document_add")){
	    msg_output('Pour ajouter, modifier ou supprimer des documents reliés à un fournisseur, naviguez dans Administration <i class="fa fa-arrow-right"></i> Documents.','info');
	}?>
	<div class="p-2">   
		<?php 
		$getFournisseurs = query('SELECT * FROM FOURNISSEURS WHERE inactif IS NULL ORDER BY nom ASC',[],$mysqli);
		if($getFournisseurs->num_rows > 0){
		    ?>
		    <div class="row align-items-top">
		    	<?php 
		    	while($rowFour = $getFournisseurs->fetch_assoc()){
		    	    // voir si les desc fournisseur existe
		    	    $rowFourDesc = null;
		    	    $getDescFour = query('SELECT * FROM FOURNISSEURS_DESC WHERE id_fournisseur = ?',[$rowFour['id_fournisseur']],$mysqli);
		    	    if($getDescFour->num_rows === 1){
		    	        $rowFourDesc = $getDescFour->fetch_assoc();
		    	        if($rowFourDesc['hidden'] == '1'){
		    	            continue;
		    	        }
		    	    }
		    	    ?>
		    	    <div class="col-12 col-sm-6 col-md-4 col-lg-3 p-1">
		    	    	<div class="panel bg-white p-1">
							<div class="text-center panel-body">
								<?php if(!empty($rowFourDesc['logo'])){?>
									<img src="//animoetc.com/upimg/four/<?= $rowFourDesc['logo'] ?>?t=1&f=1&w=100&h=57">
								<?php }else{?>
									<i class="fa fa-4x fa-industry"></i>
								<?php }?>
								<br />
								<h4 class="text-center"><?= $rowFour['nom']?></h4>
								<?php /*?>
								<h6 class="text-center">
									<?php 
									$listAttr = [];
									if($rowFour['est_fournisseur']){
									   $listAttr[] = 'Distributeur';
									}
									if($rowFour['est_distributeur']){
									    $listAttr[] = 'Fournisseur';
									}
									echo implode(' et ',$listAttr);
									?>
								</h6>*/?>
								<?php if($rowFourDesc){?>
									<small>Dernière mise à jour: <?= formatDateUTF8($rowFourDesc['date_update'])?></small>
								<?php }?>
								<div class="p-1 text-center">
									<a class="ajaxPopup" href="javascript:;" data-modal-url="ajax/modals/fournisseur.php?id_fournisseur=<?= $rowFour['id_fournisseur']?>">
										<i class="fa fa-search" style="font-size: 20px;" aria-hidden="true"></i>
									</a>
									<?php if(has_rights('admin')){?>
										<a class="ajaxPopup" href="javascript:;" data-modal-url="ajax/modals/fournisseur_edit.php?id_fournisseur=<?= $rowFour['id_fournisseur']?>">
											<i class="fa fa-pencil" style="font-size: 20px;  margin-right: 20px;  margin-left: 20px;" aria-hidden="true"></i>
										</a>
										<a class="ajaxPopup"  href="javascript:;" data-modal-url="ajax/modals/fournisseur_delete.php?id_fournisseur=<?= $rowFour['id_fournisseur']?>">
		                                    <i class="fa fa-trash-o" style="font-size: 20px" aria-hidden="true"></i>
										</a>
									<?php }?>
								</div>
							</div>
						</div>
		    	    </div>
		    	    <?php
		    	}
		    	?>
		    </div>
		    <?php
		}
		?>
	</div>
</section>