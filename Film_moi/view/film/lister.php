<?php
include_once '../../includes/head.php'; 
include_once '../../includes/interfaceVisiteur.php'; 
require_once("../../includes/ConnectionPDO.php");
?>
<div class="container">  
  
  <div class="row mb-5">
            <!--  ICONE + TITLE -->
          <div class="col-md-8">
             <h2> <i class="fas fa-film"></i>   Liste des Films </h2> 
          </div>  
  </div> 

     <div class="flex-container" >
        <?php 
           // ===============  GESTION LISTAGE DES FILMS ===============

          if (isset($_GET['cat']) && $_GET['cat'] != "") 
          {
                  //SELECT BY CATEGORY

                  $nomCat = $_GET['cat'];
                  $requette="SELECT * FROM film WHERE categorie=?";
                  $stmt = $connexion->prepare($requette);
                  $stmt->execute(array($nomCat));
          }else{
                // NORMAL SELECT
            
                $requette="SELECT * FROM film";
                $stmt = $connexion->prepare($requette);
                $stmt->execute();
            }
        
           while($film=$stmt->fetch(PDO::FETCH_OBJ))
           {
         ?>  
          <!--  CARD FILM -->
          <div  style="display: inline-block" >
                  <div class="card" style="width: 18rem;">
                     <img class="card-img-top" src="../../img/<?php echo $film->pochette; ?>" alt="Imagem de capa do card">
                     <div class="card-body">
                        <h5 class="card-title">Titre: <?php echo $film->titre; ?></h5>
                        <p class="card-text" ><span>Realisateur: <?php echo $film->realisateur; ?></span></p>
                        <p class="card-text"><span>Prix: <?php echo $film->prix; ?>$</span></p>
                        <p class="card-text"><span>Categorie: <?php echo $film->categorie; ?></span></p>
                        <p class="card-text"><span>Description: <?php echo $film->description; ?></span></p>
              
                        <a href="#" class="btn btn-primary">Visitar</a>
                     </div>                     
                  </div>
         </div>
        <!--  CARD FILM -->
        <!-- <div class="card flex-container" style="width: 20rem;" >
                    
                     <button type="button"  id="btlOpenPreview">
                            <img class="card-img-top"
                             src="../../img/<?php echo $film->pochette; ?>"
                             width="200"
                             height="300"
                             id="preview">
                    </button> 

                 <div class="card-body">
                    <h5 class="card-title">Titre: <?php echo $film->titre; ?></h5>
                     <p class="card-text" ><strong>Realisateur: <?php echo $film->realisateur; ?></strong></p>
                     <p class="card-text"><strong>Prix: <?php echo $film->prix; ?>$</strong></p>
                     <p class="card-text"><strong>Categorie: <?php echo $film->categorie; ?></strong></p>
                     <p class="card-text"><strong>Description: <?php echo $film->description; ?></strong></p>
                </div>
         </div> -->

        <?php  } ?>
    </div>
</div>  

<?php include '../../includes/footer.php'; ?>