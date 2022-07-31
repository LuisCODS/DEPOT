<?php 
  //session_start();
  include '../../includes/head.php'; 
  include '../../includes/interfaceAdmin.php'; 
  require_once("../../includes/ConnectionPDO.php");
?>


<div class="container"> 
      <div class="row mb-3">
            <!--  ICONE + TITLE -->
            <div class="col-md-8">
               <h2> <i class="fas fa-film"></i>   Liste des Film </h2> 
            </div>  
            <div class="col-md-3">

            </div> 
            <!--  BOUTTON NOUVEAU -->
            <div class="col-md-1">
                  <a class="btn btn-outline-success" 
                     href="../film/create.php" 
                     role="button">Nouveau
                  </a>
            </div> 
      </div> 

      <div class="row">
            <div  class="col-md-12">               
                <!--TABLE DES FILM-->
                <div class="col-md-12"  >
                      <table class="table table-hover ">
                          <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Pochette</th>
                                    <th scope="col">Titre</th>
                                    <th scope="col">Prix</th>
                                    <th scope="col">Categorie</th>
                                    <th scope="col">Realisateur</th>
                                    <th scope="col">Action</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>   

                <?php 


    $requette="SELECT * FROM film";
    $stmt = $connexion->prepare($requette);
    $stmt->execute();                   
        
     while($ligne=$stmt->fetch(PDO::FETCH_OBJ))
     {
     ?>

    <tr>
        <td><img src="../../img/<?php echo($ligne->pochette)?>" width=80 height=80></td>
        <td><?php echo($ligne->titre) ?></td>
        <td>$ <?php echo($ligne->prix)?></td>
        <td><?php echo($ligne->categorie)?></td>  
        <td><?php echo($ligne->realisateur)?></td>   
        <td>
          <a 
            class="btn btn-outline-primary " 
            href="../film/editer.php?id=<?php echo ($ligne->PK_ID_Film); ?>"
            role="button">Editer
          </a>                         
        </td>                              
        <td>              
          <a 
          class="btn btn-outline-danger " 
          href="../../controller/film.php?delete=<?php echo ($ligne->PK_ID_Film); ?>"
          role="button">Supprimer
          </a>
        </td>                         
    </tr>    

     <?php } ?>

                            </tbody>
                      </table>                  
                </div>
                <!-- FIN TABLE -->

            </div> 
      </div>      
</div>     


<?php include '../../includes/footer.php'; ?>
