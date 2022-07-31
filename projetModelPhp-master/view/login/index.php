<!doctype html>
<html lang="en">
  <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">     
        <!-- Bootstrap CSS -->   
        <link rel="stylesheet" href="../../plugins/bootstrap-4.3.1-dist/css/bootstrap.min.css">
        <!-- Pour les Icons -->
        <link rel="stylesheet" href="../../plugins/icons/fontawesome-free-5.12.1-web/css/all.css">
        <!-- CSS DU MODULE -->  
        <link rel="stylesheet" type="text/css" href="css/moduleStyle.css">     
        <title>Blog PHP -Login</title>
  </head>
  <body class="text-center">
        <div class="container">
               <h1 class="mb-4">Login page</h1>            
               <form>
                    <input type="text" placeholder="Nom utilisateur" 
                           name="UtilisateurNickName" 
                           id="UtilisateurNickName" 
                           class="form-control mb-4">  
                    <input type="password" 
                           autocomplete ="on" 
                           placeholder="Mot de passe" 
                           class="form-control mb-4" 
                           id="UtilisateurMDP">
                    <button type="button" id="btnLogin" 
                            class="form-control btn btn-primary"  
                            onclick="callRedirection()" >
                            Login
                    </button> 
              </form> 
        </div> 
        <script src="../../plugins/jquery/jquery-3.5.1.min.js"></script>
        <script src="../../plugins/popper.min.js"></script>        
        <script src="../../plugins/bootstrap-4.3.1-dist/js/bootstrap.min.js"></script><!-- Bootstrap js -->        
        <script type="text/javascript" src="js/moduleFunction.js"></script> <!-- JS DU MODULE-->
  </body>
</html>