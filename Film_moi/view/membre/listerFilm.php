<?php
    session_start();
    include_once '../../includes/head.php'; 
    include_once '../../includes/interfaceMembre.php'; 
    include_once '../../model/Membre.class.php';
    require_once("../../includes/ConnectionPDO.php");

// =================== GESTION SESSION MEMBRE ===========

    $membre = new Membre(null,null,null,null,null,null,null);

    if (isset ($_SESSION["membre"]) )
     {
        $membre = unserialize($_SESSION["membre"]);   
     }
    else {
        header("location: ../../controller/login.php");
        exit();
     }
?>

<!-- DISPLAY SESSION -->
<div class="alert alert-success " role="alert">
  Session : <strong><?php  echo $membre->getCourriel();?></strong>
</div>

<!-- ========================= CARD ZONE ========================== -->

<div class="container">  
    <div class="row mb-5">
              <!--  ICONE + TITLE -->
            <div class="col-md-8">
               <h2> <i class="fas fa-film"></i>   Nos Films </h2> 
            </div>  
            <div class="col-md-3">
            </div> 
    </div> 
        <!-- CARDS--> 
         <div class="flex-container" >
             
            <?php 
           // ===============  GESTION LISTAGE DES FILMS ===============

            // select par categorie

            if (isset($_GET['cat']) && $_GET['cat'] != "") {
                    //Get categorie name
                    $nomCat = $_GET['cat'];
                    $requette="SELECT * FROM film WHERE categorie=?";
                    $stmt = $connexion->prepare($requette);
                    $stmt->execute(array($nomCat));
            }else{
                  //select normal

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
                      
                                <!-- BUTTON AJOUTER PANIER -->         
                                <form method='post' action='gestionPanier.php'>
                                    <input type='hidden' name='id_film'   value="<?php echo $film->PK_ID_Film; ?>" />
                                    <input type='hidden' name='idMembre'  value='<?php  echo $membre->getMembreID();?>'/>
                                    <button type='submit' class='btn btn-primary'>Panier<i class="far fa-heart"></i></button>
                                </form>
                             </div>                     
                          </div>
                 </div>

            <!--  FIN while -->
            <?php  } ?> 
         </div>       
</div>  

<?php
// ================ CREATE SESSION PANIER =====================
   //Premiere fois sur la page (panier vide)
   if (!isset ($_SESSION['panier']) )
   {
      //Create session card
      $_SESSION['panier'] = array();
   }
?>



<?php include '../../includes/footer.php'; ?>



