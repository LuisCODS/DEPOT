<?php    
    $id = $_GET['id'];
    $result = $connection->query("SELECT * FROM usuarios WHERE id = '$id' LIMIT 1")
    or  die($connection->error()); 
    $resultado = mysqli_fetch_assoc($result);    
?> 
<div class="container theme-showcase" role="main">
    <div class="page-header">
        <h2>Visualizar Usuario</h2>
    </div>
    
    <!--  BUTTONS-->
    <div class="row">
        <div class="pull-right">
            <a href='administrativo.php?link=2&id=<?php echo $resultado['id']; ?> '>
               <button type='button' class='btn btn-sm btn-info'>Listar</button> 
           </a>            
              <a href='administrativo.php?link=4&id=<?php echo $resultado['id']; ?> '> 
               <button type='button' class='btn btn-sm btn-warning'>Editar</button> 
           </a>
             <a href='administrativo.php?link=4&id=<?php echo $resultado['id']; ?> '>
               <button type='button' class='btn btn-sm btn-danger'>Apagar</button> 
           </a>
        </div>
    </div> 
    
    <div class="row">
        <div class="col-md-12"> 
            <!--ID-->
                <div class="col-xs-3 col-sm-1 col-md-1">
                    <b>Id:</b>             
                </div>
                <div class="col-xs-9 col-sm-11 col-md-11">
                    <?php echo $resultado['id']; ?>
                </div>
            <!--NOM-->
                <div class="col-xs-3 col-sm-1 col-md-1">
                    <b>Nome:</b>             
                </div>
                <div class="col-xs-9 col-sm-11 col-md-11">
                    <?php echo $resultado['nome']; ?>
                </div>
            <!--EMAIL-->
                <div class="col-xs-3 col-sm-1 col-md-1">
                    <b>E-mail:</b>             
                </div>
                <div class="col-xs-9 col-sm-11 col-md-11">
                    <?php echo $resultado['email']; ?>
                </div>
            <!--USUARIO-->
                <div class="col-xs-3 col-sm-1 col-md-1">
                    <b>Usuario:</b>             
                </div>
               <div class="col-xs-9 col-sm-11 col-md-11">
                    <?php echo $resultado['login']; ?>
                </div>
            <!--NIVEL ACESSE-->
                <div class="col-xs-3 col-sm-1 col-md-1">
                    <b>Nivel de acesso:</b>             
                </div>
                <div class="col-xs-9 col-sm-11 col-md-11">
                    <?php echo $resultado['nivel_acesso_id']; ?>
                </div>
        </div>
    </div>  
    <br/>
    <hr>
     <a href="administrativo.php?link=2">Voltar</a>
</div> 
<br/>

