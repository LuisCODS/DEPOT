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
		<div style="width: 100%;max-width: 800px;">
        	<div class="pb-2">
        		<h4><?= $rowFour['nom']?></h4>
        	</div>
        	<div class="pb-3">
        		<div>
        			<div class="row">
        				<div class="col-12">
        					<?php if($rowFourDesc){
        					   echo $rowFourDesc['comm_desc_fr'];
        					}else{
        					   echo 'Aucune information sur ce fournisseur ou distributeur.';
        					}?>
        				</div>
        				<div class="col-12 pt-4">
        					<small>
        					<?php if($rowFourDesc){
        					   echo 'Dernière modification le '.formatDateUTF8($rowFourDesc['date_update']);
        					}?>
        					</small>
        				</div>
        			</div>
        		</div>
        	</div>
            <div class="text-right">
        		<button type="button" class="btn btn-success btn-close">OK</button>
        	</div>
        </div>
        <?php
    }
}
die(json_encode(['status'=>'success','html'=>ob_get_clean()]));
?>