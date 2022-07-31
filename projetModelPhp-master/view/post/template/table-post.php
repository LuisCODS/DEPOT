<table class="table table-hover">
	<thead class="thead-dark">
	    <tr>
	        <th>Nom</th>
	        <th>Action</th>
	    </tr>
	</thead>
	<tbody>
<?php extract($_POST); ?>	
	
<?php foreach( json_decode($obj) as $list){ ?>			
	    <tr>
	        <td><?php echo $list->Title; ?></td>
	        <td>
	        	<button type="button" 
	        			class="btn btn-dark btnEditer" 
	        			obj='<?php echo json_encode($list); ?>'>
	        			<i class="fas fa-user-edit"></i>
	        		 	Editer
	       		 </button>
	        </td>
	    </tr> 
<?php } ?>
	</tbody>
</table>

