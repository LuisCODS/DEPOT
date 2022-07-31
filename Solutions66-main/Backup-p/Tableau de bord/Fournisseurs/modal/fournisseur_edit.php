<?php
require_once('../../req/init.php');
ob_start();
if ( preg_match('#^\d+$#',$_GET['id_fournisseur']) ){
    $getFour = query('SELECT * FROM FOURNISSEURS WHERE inactif IS NULL AND id_fournisseur = ?',[$_GET['id_fournisseur']],$mysqli);
    if($getFour->num_rows === 1){
        $rowFour = $getFour->fetch_assoc();
        $rowFourDesc = null;
        $getDescFour = query('SELECT * FROM FOURNISSEURS_DESC WHERE id_fournisseur = ?',[$rowFour['id_fournisseur']],$mysqli);
        if($getDescFour->num_rows === 1){
            $rowFourDesc = $getDescFour->fetch_assoc();
        }
        ?>
		<form method="POST" enctype="multipart/form-data" action="index.php?p=fournisseurs" style="width: 100%;max-width: 1000px;">
         	<input type="hidden" name="mode" value="save" />
        	<input type="hidden" name="id_fournisseur" value="<?= $rowFour['id_fournisseur']?>" />
        	<div class="pb-2">
        		<h4>Modifier <?= $rowFour['nom']?></h4>
        	</div>
        	<div class="pb-3">
        		<div>
        			<div class="row">
        				<div class="col-12">
        					<div class="form-group">
        						<label class="control-label">Détails</label>
        						<textarea rows="15" class="form-control editor" name="comm_desc_fr"><?= $rowFourDesc['comm_desc_fr']?></textarea>
        					</div>
        				</div>
        				<div class="col-12">
        					<div class="form-group">
        						<label class="control-label">Logo</label>
        						<?php if(!empty($rowFourDesc['logo'])){?>
        							<div class="py-2">
        								<img src="//animoetc.com/upimg/four/<?= $rowFourDesc['logo']?>?t=1&f=1&w=300&h=150">
        							</div>
        						<?php }?>
        						<div class="py-2">
        							<input type="file" name="logo" accept="image/jpeg,image/png,image/gif"/>
        						</div>
        					</div>
        				</div>        			
        			</div>
        		</div>
        	</div>
            <div class="text-right">
        		<button type="button" class="btn btn-default btn-close">Annuler</button>
        		<button type="submit" class="btn btn-primary">Enregistrer</button>
        	</div>
        </form>
        <?php
    }
}
die(json_encode(['status'=>'success','html'=>ob_get_clean()]));
?>