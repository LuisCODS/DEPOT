<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="loja de moveis">
    <meta name="author" content="LuisCODS">
    <link rel="icon" href="Adm/images/favicon.ico">      
    <title>Loja de moveis</title>  
     Bootstrap CSS 
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  </head>
  <body>   
    <!--MENU -->
      <?php include_once("menu.php"); ?>
      </br></br></br>           
      
   <!--CONTAINER 
    ====================================================  -->
    <div class="container marketing">	
        <h1>Contato</h1></br>
            <hr class="featurette-divider">
            <div class="row featurette">
              <div class="col-md-6">
                <h2 class="featurette-heading">Contato por email</h2>
               <p class="lead">
                    <form>
                          <div class="form-group">
                                <label for="nome">Nome completo*</label>
                                <input type="text"  name="nome" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Nome completo ici" required>
                          </div>
                          <div class="form-group">
                                <label for="email">Email*</label>
                                <input type="email" nome="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email" required>
                           </div>
                          <div class="form-group">
                                <label for="telefone">Telefone*</label>
                                <input type="text" nome="telefone" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter telefone" required>
                           </div>   
                          <div class="form-group">
                                <label for="assunto">Assunto*</label>
                                <input type="text" nome="assunto" class="form-control" id="assunto" aria-describedby="assunto" placeholder="Assunto" required>
                           </div>
                            <div class="form-group">
                                <label for="msn">Mensagem*</label>
                                <textarea name="msn" class="form-control" rows="5" id="msn" required></textarea>
                            </div>
                          <button type="submit" class="btn btn-success">Enviar</button>
                    </form> 
               </p>
              </div>
              <div class="col-md-6">
                <h2 class="featurette-heading">Contato por telefone</h2>    
                    <p class="lead">
                    La plante artificielle en pot Finh, présentant un joli mélange de plantes grasses, ajoute une touche fraîche et colorée dans tout espace contemporain.
                  </p>
              </div>
            </div>
            <hr class="featurette-divider">    

         <!--  FOOTER
        ===================================================  -->
        <footer class="container">
            <p class="float-right"><a href="#">Voltar ao topo</a></p>
            <p>&copy; LuisCODS-2019</p>
        </footer>     
    </div><!-- /.container -->

