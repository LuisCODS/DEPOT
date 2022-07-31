<?php 
include '../../includes/head.php'; 
include '../../includes/interfaceAdmin.php'; 
require_once("../../includes/ConnectionPDO.php");
?>

<div class="container">    
      <div class="row mb-3">
		        <!--  ICONE + TITLE -->
		      <div class="col-md-8">
		         <h2> <i class="fas fa-film"></i>   Liste des membres </h2> 
		      </div>  
      </div>    
      <div class="row">
          <div  class="col-md-12">
				<!--TABLE DES MEMBRE-->
				<div class="col-md-12">
				    <table class="table table-hover ">
				        <thead class="thead-dark">
				            <tr>
				                <th>Nom</th>
				                <th>Prenom</th>
				                <th>Profil</th>
				            </tr>
				        </thead>
                            <tbody>   

				                <?php 
				                  $requette="SELECT * FROM membre";
				                  $stmt = $connexion->prepare($requette);
				                  $stmt->execute();
				                 while($ligne=$stmt->fetch(PDO::FETCH_OBJ))
				                 {
				                 ?>

				                <tr>
				                    <td><?php echo($ligne->nom) ?></td>
				                    <td><?php echo($ligne->prenom)?></td>
				                    <td><?php echo($ligne->profil)?></td>                        
				                </tr>    
				             <?php } ?>

                            </tbody>

				    </table>
				    <hr>  
				</div>
             	<!-- FIN TABLE -->
          </div>
      </div>    

</div> 

 <?php include '../../includes/footer.php'; ?>
