<!--   MENU   ADMIN -->
 <nav class="navbar navbar-inverse navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed"
              data-toggle="collapse" 
              data-target="#navbar" 
              aria-expanded="false" 
              aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
        <a class="navbar-brand" href="administrativo.php">Nom site</a>
    </div>
      
    <div id="navbar" class="navbar-collapse collapse">
      <ul class="nav navbar-nav">  
          
          <!--GESTION USUARIOS-->
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"  role="button"  
             aria-haspopup="true"  aria-expanded="false">Usuarios<span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li><a href="administrativo.php?link=2">Listar</a></li>
            <li><a href="administrativo.php?link=3">Cadastrar</a></li>            
          </ul>
        </li> 
        
        <!--GESTION PRODUIT-->
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" 
             data-toggle="dropdown" 
             role="button" 
             aria-haspopup="true" 
             aria-expanded="false">Produto<span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li><a href="administrativo.php?link=7">Listar categoria</a></li>
            <li><a href="administrativo.php?link=10">Listar produtos</a></li>            
          </ul>
        </li>  
        
        <!--GESTION CONFIG-->
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" 
             data-toggle="dropdown" 
             role="button" 
             aria-haspopup="true" 
             aria-expanded="false">Configuracao<span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li><a href="administrativo.php?link=14">Situacao</a></li>
          </ul>
        </li>  
        
        <li>
             <a href="login.php">Sair</a>
        </li>
        
      </ul>
    </div>
  </div>
</nav>
<br/>
<br/>