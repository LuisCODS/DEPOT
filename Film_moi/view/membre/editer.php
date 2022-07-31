<?php
session_start(); 
include_once '../../includes/head.php'; 
include_once '../../includes/interfaceMembre.php';
include_once '../../model/Membre.class.php';


    // GESTION SESSION
    $membre = new Membre(null,null,null,null,null,null,null,);

    if (isset ($_SESSION["membre"]) )
     {
      $membre = unserialize($_SESSION["membre"]);   
     } else {
      header("location: ../../controller/login.php");
      exit();
     }
 ?>


<!-- SHOW SESSION -->
<div class="alert alert-success " role="alert">
  Session : <strong><?php  echo $membre->getCourriel();?></strong>
</div>

<!-- CONTAINER -->
<div class="container">
       <!--  LIGNE 1 -->
        <div class="row mb-3">
            <div class="col-md-8">
            </div>  
            <div class="col-md-3">
              <a class="btn btn-primary" href="index.php" role="button"><i class="fas fa-backward"></i></a>
            </div> 
      </div> 

  <!-- _________________  FORM EDITER MEMBRE _________________ --> 
 <form id="formCreate" action="../../controller/membre.php" method="POST" ">
           <h2>Formulaire d'édition</h2>
                <input
                 type="hidden" 
                 id="PK_ID_Membre" 
                 value="<?php  echo $membre->getMembreID();?>"
                 name="PK_ID_Membre" >
                <input
                 type="hidden" 
                 id="profil" 
                 value="<?php  echo $membre->getProfil();?>"
                 name="profil" >
           <div class="form-group">
                <label for="profil"></label>
                <input
                 type="hidden" 
                 class="form-control" 
                 id="profil" 
                 name="profil" 
                 value="membre">
          </div>
          <!-- FORNECE O TIPO DE ACAO AO CONTROLLER -->
          <div class="form-group">
                <input 
                type="hidden" 
                class="form-control"
                readonly="true" 
                id="action" 
                name="action" 
                value="update" >
          </div>
          <div class="form-group">
                <label for="nom">Nom</label>
                <input
                 value="<?php  echo $membre->getNom();?>"
                 size="40"
                 type="text" 
                 class="form-control" 
                 name="nom"
                 id="nom" 
                 required>
                <p id="erreurNom"></p>
          </div>
          <div class="form-group">
                <label for="prenom">Prenom</label>
                <input 
                value="<?php  echo $membre->getPrenom();?>"
                size="40"
                type="text" 
                class="form-control" 
                name="prenom" 
                required>
          </div>
          <div class="form-group">
                <label for="courriel">Courriel</label>
                <input
                value="<?php  echo $membre->getCourriel();?>" 
                required
                id="courriel"
                size="40"
                type="email" 
                class="form-control" 
                name="courriel">
          </div>
          <div class="form-group">
                <label for="telephone">Telephone</label>
                <input 
                size=""
                value="<?php  echo $membre->getTelMembre();?>"
                class="form-control" 
                id="tel_membre" 
                name="tel_membre">
                <p id="erreurPassword" style='color:red'></p>
          </div>
          <div class="form-group">
                <label for="MDP_membre">Mot de passe</label>
                <input 
                size=""
                type="password" 
                class="form-control" 
                id="MDP_membre" 
                name="MDP_membre" 
                required>
                <p id="erreurPassword" style='color:red'></p>
          </div>
          <div class="form-group">
                <label for="MDP_membreConfirm">Confirmation mot de passe</label>
                <input 
                size="40"
                type="password"
                class="form-control" 
                id="MDP_membreConfirm" 
                name="MDP_membreConfirm" 
                required>
                 <p id="erreurPasswordConfirm" style='color:red'></p>
          </div> 
          <button             
              type="submit" 
              onclick="return validerForm( )" 
              class="btn btn-primary">
              Editer
          </button>
    </form> 
</div>  
<!-- FIN CONTAINER -->

<!--  FOOTER  --> 
<?php 
include '../../includes/footer.php'; 
?> 