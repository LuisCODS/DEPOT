<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
        <meta charset="utf-8">
        <meta name="description" content="Page de connection">
        <link rel="icon" href="images/favicon.ico">
        <title>Page de connection</title>
        <!-- Bootstrap CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <!-- Custom styles for this template -->
        <link href="css/signin.css" rel="stylesheet">
        <script src="js/ie-emulation-modes-warning.js"></script>
        
  </head>
  <!-- SOURCE https://www.youtube.com/watch?v=s7qtAnH5YkY -->
  <body class="text-center">
      <div class="container">
      <form method="POST" action="validar_login.php" class="form-signin">            
            <h1 class="h3 mb-3 font-weight-normal">Connection</h1>            
            <label for="usuario" class="sr-only">Endereço de email</label>
            <input type="text" 
                   name="usuario" 
                   class="form-control" 
                   placeholder="usuario" 
                   required autofocus>
            <label for="senha"  class="sr-only">Mot de passe</label>
            <input type="password" 
                   name="senha" 
                   class="form-control" 
                   placeholder="senha" 
                   required>
<!--            <div class="checkbox mb-3">
                <label><input type="checkbox" value="remember-me"> Lembrar de mim</label>
            </div>-->
            <button type="submit" class="btn btn-lg btn-primary btn-block">Enter</button>            
            <?php
              echo( " <br>"." <br>"." <a href=javascript:history.back()>Retourner</a> "   );
            // header("location: ../index.php"); //redirect user to index
             ?>            
            <h3 class="text-danger" >                      
                <?php
                  if(isset($_SESSION['loginErro']))
                  {
                      echo $_SESSION['loginErro'];
                      unset($_SESSION['loginErro']);
                  }
                ?>
            </h3>            
            <p class="mt-5 mb-3 text-muted">&copy; by LuisCODS - 2019</p>
        </form>
    </div>
  </body>
</html>
