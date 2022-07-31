<?php extract($_POST); ?>
<option value="" selected="selected">Type</option>  
<?php foreach( json_decode($obj) as $list) { ?>						  
	  <option value="<?php echo $list->Categorie_ID;?>"> 
	  		<?php echo $list->NomCategorie; ?> 
	  </option>  	
<?php } ?>	




