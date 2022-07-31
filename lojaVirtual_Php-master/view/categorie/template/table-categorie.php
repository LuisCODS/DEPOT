<?php 

	//PERTE ELEBORADA APENAS PARA ERROS DO JSON
	//extract($_POST);  
	//var_dump($obj);
	//$decode = (array)json_decode($obj);
	// $decode = json_decode($obj);	
	// var_dump($decode);

	 // Retorna o erro (se houver) ocorrido durante o último JSON encoding/decoding.
 //    switch (json_last_error()) 
 //    {
	//     case JSON_ERROR_NONE:
	//         echo ' - No errors';
	//     break;
	//     case JSON_ERROR_DEPTH:
	//         echo ' - Maximum stack depth exceeded';
	//     break;
	//     case JSON_ERROR_STATE_MISMATCH:
	//         echo ' - Underflow or the modes mismatch';
	//     break;
	//     case JSON_ERROR_CTRL_CHAR:
	//         echo ' - Unexpected control character found';
	//     break;
	//     case JSON_ERROR_SYNTAX:
	//         echo ' - Syntax error, malformed JSON';
	//     break;
	//     case JSON_ERROR_UTF8:
	//         echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
	//     break;
	//     default:
	//         echo ' - Unknown error';
	//     break;
	// }
	// end of switch
?>	



<?php extract($_POST);  ?>	
<table class="table table-hover">
	<thead class="thead-dark">
	    <tr>
	        <th>Nom</th>
	        <th>Action</th>
	    </tr>
	</thead>
	<tbody>	
	<?php  foreach ( json_decode($obj) as $list) { ?> <!-- JSON into a PHP objet -->			
    <tr>
        <td><?php echo  $list->NomCategorie  ?></td>
        <td>
        	<button type="button" class="btn btn-dark btnEditer" obj='<?php echo json_encode($list); ?>'>
        			<i class="fas fa-user-edit"></i> Editer
       		 </button>
        </td>
    </tr> 
	<?php } ?>
	</tbody>
</table>





