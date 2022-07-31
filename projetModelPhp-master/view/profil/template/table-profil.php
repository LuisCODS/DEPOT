<!--
 ____________________________________________________________________
  CETTE PAGE RECOIT LA  REQUISITION ASSINCRONE AJAX (moduleScript.js) 
 ... POUR AFFICHER UNE LIST DE PROFIL.
 ____________________________________________________________________
 -->

<table class="table table-hover">
	<thead class="thead-dark">
	    <tr>
	        <th>Niveau</th>
	        <th>Action</th>
	    </tr>
	</thead>
	<tbody>

<?php
	//recupere la variable obj(tableau json du profil) 
	//du callback(moduleScript.js) 
	extract($_POST);
	
	//Decodes a JSON string into a PHP objet
	foreach( json_decode($obj) as $profil)	
	{
?>			
	    <tr>
	        <td><?php echo $profil->ProfilNom ?></td>
	        <td>
	        	<button type="button" 
	        			class="btn btn-dark btnEditer" 
	        			obj='<?php echo json_encode($profil); ?>'>
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
