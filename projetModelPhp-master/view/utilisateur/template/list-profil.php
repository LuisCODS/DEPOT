<!--  
_____________________________________________________________________________
  CETTE PAGE RECOIT UN OBJET (obj) DU FICHIER moduleFunction.js provenant 
  ... de la methode lister(txtInput) pour remplir les option du select.
 _____________________________________________________________________________
-->



<!--  Get form inputs field -->
<?php extract($_POST); ?>

<option value="" selected="selected">Type</option>  
<?php foreach( json_decode($obj) as $list) { ?>						  
<option value="<?php echo $list->Profil_ID;?>"> <?php echo $list->ProfilNom; ?> </option>  		
<?php } ?>	




