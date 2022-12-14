<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="loja de moveis">
    <meta name="author" content="LuisCODS">
    <link rel="icon" href="Adm/images/favicon.ico">      
    <title>Loja de moveis</title>  
    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
      
  </head>
  <body>   
    <!--MENU
    ==================================================== -->
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <a class="navbar-brand" href="#">LuisCODS</a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
      <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">
          <!--<li class="nav-item active">-->
          <li class="nav-item">  
            <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="produto.php" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Produtos</a>
            <div class="dropdown-menu" aria-labelledby="dropdown01">
              <a class="dropdown-item" href="produto.php">Cozinha</a>
              <a class="dropdown-item" href="#">Escritorio</a>
              <a class="dropdown-item" href="#">Sala</a>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#about">Empresa</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#contact">Contato</a>
          </li>
        </ul>
      </div>        
      </nav>

    <!--MENU -->
      <?php include_once("menu.php"); ?>
           
      
   <!--CONTAINER 
    ====================================================  -->
    <div class="container marketing">
	
	<h1>Produtos em destaque</h1></br>
        <div class="row">            
          <div class="col-lg-4">
			<img class="first-slide" src="fotos/lit.jpg" alt="First slide" height="150px" width="150">
            <h2>Lit queen</h2>
            <p>Le tr??s grand lit Nell charme par son allure ??l??gante et contemporaine. Sa t??te haute et rembourr??e ?? l???aspect velours s???harmonise parfaitement ?? son ravissant pi??tement de bois aux lignes nettes qui lui conf??rent un air intemporel. Sa base de lit au profil bas ajoute ?? l???exp??rience tout confort que procure Nell. </p>
            <p><a class="btn btn-secondary" href="#" role="button">View details &raquo;</a></p>
          </div><!-- /.col-lg-4 -->
          <div class="col-lg-4">
			<img class="first-slide" src="fotos/bureau.jpg" alt="First slide" height="150px" width="150">
            <h2>Bureau</h2>
            <p>Avec une esth??tique minimaliste et des couleurs au go??t du jour, Adel incarne le bureau parfait. Sa conception, compos??e d???un plateau spacieux, de trois larges tiroirs et d???une base aussi solide que d??licate, r??invente le bureau contemporain dans un esprit Mid-Century Modern. </p>
            <p><a class="btn btn-secondary" href="#" role="button">View details &raquo;</a></p>
          </div><!-- /.col-lg-4 -->
          <div class="col-lg-4">
            <img class="first-slide" src="fotos/pendantlamp_bollia.jpg" alt="First slide" height="150px" width="150">
            <h2>BOLLIA Suspension </h2>
            <p>La suspension Bolia se distingue par son ravissant abat-jour en forme de d??me. Elle est id??ale pour ??clairer un comptoir, une entr??e ou un coin repas dans un d??cor scandinave.
              </p>
            <p><a class="btn btn-secondary" href="#" role="button">View details &raquo;</a></p>
          </div><!-- /.col-lg-4 -->
        </div><!-- /.row -->

        <hr class="featurette-divider">

        <div class="row featurette">
          <div class="col-md-7">
            <h2 class="featurette-heading">FINH Plante artificielle en pot 27cm<span class="text-muted">Supreendente, n??o?!</span></h2>
            <p class="lead">La plante artificielle en pot Finh, pr??sentant un joli m??lange de plantes grasses, ajoute une touche fra??che et color??e dans tout espace contemporain.
              </p>
          </div>
          <div class="col-md-5">
            <img class="featurette-image img-fluid mx-auto" src="fotos/Plante.jpg" alt="Generic placeholder image">
          </div>
        </div>

        <hr class="featurette-divider">

        <div class="row featurette">
          <div class="col-md-7 order-md-2">
            <h2 class="featurette-heading">A??????, moleque! <span class="text-muted">T?? legal ou n??o t???</span></h2>
            <p class="lead">Donec ullamcorper nulla non metus auctor fringilla. Vestibulum id ligula porta felis euismod semper. Praesent commodo cursus magna, vel scelerisque nisl consectetur. Fusce dapibus, tellus ac cursus commodo.</p>
          </div>
          <div class="col-md-5 order-md-1">
            <img class="featurette-image img-fluid mx-auto" src="fotos/Plante.jpg" alt="Generic placeholder image">
          </div>
        </div>

        <hr class="featurette-divider">

        <div class="row featurette">
          <div class="col-md-7">
            <h2 class="featurette-heading">E, por ??ltimo, essa aqui. <span class="text-muted">Xeque-mate!</span></h2>
            <p class="lead">Donec ullamcorper nulla non metus auctor fringilla. Vestibulum id ligula porta felis euismod semper. Praesent commodo cursus magna, vel scelerisque nisl consectetur. Fusce dapibus, tellus ac cursus commodo.</p>
          </div>
          <div class="col-md-5">
            <img class="featurette-image img-fluid mx-auto" src="fotos/Plante.jpg" alt="Generic placeholder image">
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

  
      
	<!-- Optional JavaScript -->
	<!-- jQuery first, then Popper.js, then Bootstrap JS -->
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script> 
</body>
</html>