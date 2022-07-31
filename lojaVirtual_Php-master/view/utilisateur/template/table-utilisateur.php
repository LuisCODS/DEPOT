<!--  --------------------------------------------------------------------
  CETTE PAGE RECOIT LA  REQUISITION ASSINCRONE AJAX (moduleScript.js) 
 ... POUR AFFICHER UNE LIST DES ENTITÉS.
 --------------------------------------------------------------------- -->

<table class="table table-hover">
	<thead class="thead-dark">
	    <tr>
	        <th>Nom</th>
	        <th>Action</th>
	    </tr>
	</thead>
	<tbody>

<?php
	//Recupere la variable obj(tableau json du utilisateur) 
	//...du callback(moduleScript.js) 
	extract($_POST);
	
	//Decodes a JSON string into a PHP objet
	foreach( json_decode($obj) as $utilisateur)	
	{
?>			
	    <tr>
	        <td><?php echo $utilisateur->UtilisateurName ?></td>
	        <td>
	        	<button type="button" 
	        			class="btn btn-dark btnEditer" 
	        			obj='<?php echo json_encode($utilisateur); ?>'>
	        			<i class="fas fa-user-edit"></i>
	        		 Editer
	       		 </button>
	        </td>
	    </tr> 

<?php
	}

?>

	</tbody>
</table>

