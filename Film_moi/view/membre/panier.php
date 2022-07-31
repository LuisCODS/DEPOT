<?php
session_start(); 
require_once '../../includes/head.php'; 
require_once '../../includes/interfaceMembre.php';
require_once("../../includes/ConnectionPDO.php");
include_once '../../model/Membre.class.php';
include_once '../../dao/Film_LocationDAO.class.php';


// =================== SESSION MEMBRE ===========

  $membre = new Membre(null,null,null,null,null,null,null);

  if (isset ($_SESSION["membre"]) ) 
  {
     //Charge la session
      $membre = unserialize($_SESSION["membre"]);   
  }
   else 
   {
      header("location: ../../controller/login.php");
      exit();
   }
 ?>
 
<!-- DISPLAY SESSION MEMBRE -->
<div class="alert alert-success " role="alert">
  Session : <strong><?php  echo $membre->getCourriel();?></strong>
</div>

<?php
// =============== SESSION  PANIER ===============

   //Si pas de session panier (panier vide)
   if (!isset ($_SESSION['panier']) )
   {
      //Cree un session 
      $_SESSION['panier'] = array();
   }


?>
<div class="container"> 

  <!--   LIGNE 1 -->
  <div class="row ">
        <div class="col-md-10">
            <h2>Votre panier(<?php echo count($_SESSION['panier']); ?>)</h2>
        </div>  
        <!--===============  VIDER PANIER  ===============-->         
        <div class="col-md-2">
            <form method='post' action='gestionPanier.php'>
                <input type='hidden' name='action'  value='videPanier'/>
                <button type='submit' class='btn btn-danger'>Vider panier</button>
            </form>
        </div> 
</div>

<!--  LIGNE 2 -->
<div class="row mb-3"> 
      <div class="col-md-10">
      </div>  
     <!--  Retour page -->
      <div class="col-md-2">
           <a class="btn btn-primary" href="index.php" role="button">
           <i class="fas fa-backward"></i>  Retourner  </a>
      </div> 
</div> 

<!--   LIGNE 3 -->
<div class="row">
      <div  class="col-md-12">  

          <!--TABLE DES FILM-->
          <div class="col-md-12"  >
                <table class="table table-hover ">
                    <thead class="thead-dark">
                          <tr>
                              <th>Pochette</th>
                              <th>Titre</th>
                              <th>Quantite</th>
                              <th>Prix</th>
                              <th>Action</th>
                          </tr>
                      </thead>
                      <tbody> 
<?php
 // =========================== ZONE AFFICHAGE===========================
    /* portée globale */
    $subtotal=0; ; 

   //Si pas de film ajouté
   if (count($_SESSION['panier']) == 0) 
   {
          $subtotal = 0;
          $TPS = 0;
          $TVQ = 0;
          $total = 0;

   }else {

              ///PARCOUR LA SESSION DU PANIER
             global  $subtotal;

             foreach ( $_SESSION['panier'] as $idFilm => $quantite) 
             {
                $requette="SELECT * FROM film WHERE PK_ID_Film =?";
                $stmt = $connexion->prepare($requette);
                $stmt->bindParam(1, $idFilm);
                $stmt->execute();
                //recupere la ligne etant qu'objet
                $films  = $stmt->fetch(PDO::FETCH_OBJ);

                //var_dump($films); 

                $prix = $quantite * $films->prix;
                $subtotal = $prix + $subtotal;

                //Gestion TPS

                $TPS_Full = $subtotal * 1.05;
                $TPS      = $TPS_Full - $subtotal;

                //Gestion TVQ

                $TVQ_Full = $subtotal * 1.10;
                $TVQ      = $TVQ_Full - $subtotal;

                $total = $subtotal + $TPS + $TVQ;


// =========================== FIN  PHP ZONE ===========================
?> 
  
        <tr>
          <td><img src="../../img/<?php echo $films->pochette ?>" width=80 height=80></td>
          <td><?php  echo $films->titre ?></td>
          <td><?php  echo $quantite     ?></td>  
          <td>$<?php echo $prix         ?></td>
          <td>
            <!--===============  ENLEVER ITEN  ===============-->    
           <a href="gestionPanier.php?remove=item&id=<?php echo $films->PK_ID_Film; ?> " 
              class="btn btn-danger">Enlever</a>
          </td>                         
      </tr> 


              <!-- foreach -->
              <?php } ?>  

      <!--else -->
      <?php } ?>


                            </tbody>
                      </table>                  
                </div>
                <!-- FIN TABLE -->

            </div> 
      </div>  
      <hr>    
</div>  

 <!-- LIGNE 4 -->
<div class="row">
        <div class="col-md-9">
        </div> 
        <div  class="col-md-3">            
            Subtotal: $ <?php echo $subtotal ?> <br/>
            TPS: $<?php echo $TPS ?><br/>
            TVQ: $<?php echo $TVQ ?><br/>
            Total: $<?php echo $total ?><br/>
           <!-- ======================= BOUTTON FINALIZER ET PAYER ======================= -->         
            <form method='post' action=''>
                <input type='hidden' name='idFilm'    value='<?php if(isset( $films->PK_ID_Film)) ?>'/>
                <input type='hidden' name='idMembre'  value='<?php  $membre->getMembreID();?>'/>
                <button type='submit' class='btn btn-success'>Finalizer et payer</button> 
            </form>    
        </div>       
</div>        





<!--  FOOTER  --> 
<?php require_once '../../includes/footer.php'; ?>