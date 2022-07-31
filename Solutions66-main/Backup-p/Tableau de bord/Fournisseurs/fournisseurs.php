<?php 
if($_POST['mode'] == 'save' && preg_match('#^\d+$#',$_POST['id_fournisseur']) && has_rights('admin')){
    $arrayDB = array();
    $arrayDB['id_fournisseur'] = $_POST['id_fournisseur'];
    $arrayDB['comm_desc_fr'] = $_POST['comm_desc_fr'];
    $arrayDB['date_update'] = date('Y-m-d H:i:s');
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
?>
<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-industry icon-mr fs-4"></i>
			<span class="hidden-xs-down"> Fournisseurs</span>
		</h1>
	</div>
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
									<a class="ajaxPopup btn btn-success" href="javascript:;" data-modal-url="ajax/modals/fournisseur.php?id_fournisseur=<?= $rowFour['id_fournisseur']?>">
										Détails
									</a>
									<?php if(has_rights('admin')){?>
										<a class="ajaxPopup btn btn-warning" href="javascript:;" data-modal-url="ajax/modals/fournisseur_edit.php?id_fournisseur=<?= $rowFour['id_fournisseur']?>">
											<i class="fa fa-edit"></i>
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