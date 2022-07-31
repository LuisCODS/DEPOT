<?php
require_once('../../req/init.php');
ob_start();
if ( preg_match('#^\d+$#',$_GET['id_fournisseur']) ){  ?>
    <form method="POST"  action="index.php?p=fournisseurs" style="width: 100%;max-width: 1000px;">
     	<input type="hidden" name="mode" value="delete" />
    	<input type="hidden" name="id_fournisseur" value="<?= $_GET['id_fournisseur']?>" />
    	<div class="pb-2">
    		<h4>Voulez-vous vraiment supprimer ce fournisseur?</h4>  
    	</div>
    	<div class="pb-3">
			<div class="row">
				<div class="col-12">
					<div class="form-group">
						<label class="control-label">Ce changement est irréversible!</label>
					</div>
				</div>
			</div>
    	</div>
        <div class="text-right">
    		<button type="button" class="btn btn-default btn-close">Annuler</button>
    		<button type="submit" class="btn btn-danger">Supprimer</button>
    	</div>
    </form>
<?php }
die(json_encode(['status'=>'success','html'=>ob_get_clean()]));
?>