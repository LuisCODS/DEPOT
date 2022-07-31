<?php
require_once('../../req/init.php');
/*
    Ce fichier recoit le post de la fonction javascript displayModal() provenant de appr_materiel.php.
    Le modal en question contien un form qui envoie l'id du produit à supprimer au appr_materiel.php
    ...qui va ensuite supprimer le produit.
*/

?>
<!-- =============================  MODAL ================================= -->
<div class="modal bounceInDown" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="text-bold modal-title">
	                Message de Confirmation
				</h4>
			</div>
            <form action="" method="POST">
                <div class="modal-body">  
                    <h4>Voulez-vous supprimer ce produit?</h4>
                    <input type="hidden" name="idPorduit" value="<?= $_POST['id_mat'] ?>">
                </div>  
                <div class="modal-footer">
                    <button type="submit" name="btnFormDelete" class="btn btn-danger" >Oui</button> 
                    <button type="button" class="btn btn-primary modalClose" data-dismiss="modal">Non</button>
                </div>                
            </form>
		</div>
	</div>
</div>





