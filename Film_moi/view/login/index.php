<?php
session_start();
include_once '../../includes/head.php';
?>

<!-- =======================  FORM LOGIN ========================== -->

<div class="container"> 
      <form id="formLogin" method="POST" action="../../controller/login.php" >

            <div class="iconeBlog">  
                  <i class="fas fa-film"></i> 
            </div> 

            <h1 class="mb-4">Page Login</h1> 

            <!-- FORNECE O TIPO DE ACAO AO CONTROLLER -->
            <input 
                type="hidden"
                readonly="true"
                id="action" 
                name="action" 
                value="login" >

            <input type="text" 
                   autofocus
                   required
                   placeholder="Courriel" 
                   name="courriel" 
                   id="courriel"
                   class="form-control mb-4">  
                   
            <input type="password"
                   required
                   autocomplete ="on"
                   placeholder="Mot de passe" 
                   class="form-control mb-4"
                   name="MDP_membre" 
                   id="MDP_membre">

              <!-- GESTION ERREUR LOGIN -->
              <div 
                  id="erreurLogin" 
                  class="alert alert-warning" 
                  role="alert" 
                  style="display:none;">
                  <p id="msgErreurLogin"></p>
              </div>

            <button type="submit" 
                    id="btnLogin"                          
                    class="form-control btn btn-primary">
                    Login
            </button>  
              <a href="../membre/create.php">Pas encore membre?</a>
                   
      </form> 
</div> 


<!--  FOOTER  --> 
<?php include '../../includes/footer.php'; ?>