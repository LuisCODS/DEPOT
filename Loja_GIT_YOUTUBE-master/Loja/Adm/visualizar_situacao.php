<!-- =================  VISUALIZAR USUARIO ================= -->
<?php    
    $id = $_GET['id'];
    $result = $connection->query("SELECT * FROM situacao WHERE situacao_id = '$id' LIMIT 1") or  die($connection->error()); 
    $resultado = mysqli_fetch_assoc($result);    
?> 
<div class="container theme-showcase" role="main">
   
    <div class="page-header">
        <h2>Detalhes situacao</h2>
    </div> 
    
    <!-- BUTTONS-->
    <div class="row">
        <div class="pull-right">
            <a href='administrativo.php?link=14&id=<?php echo $resultado['situacao_id']; ?> '>
               <button type='button' class='btn btn-sm btn-info'>Listar</button> 
           </a>            
            <a href='administrativo.php?link=17&id=<?php echo $resultado['situacao_id']; ?> '> 
               <button type='button' class='btn btn-sm btn-warning'>Editar</button> 
           </a>
            <a href='process/proc_apagar_situacao.php?id=<?php echo $resultado['situacao_id']; ?>'> 
               <button type='button' class='btn btn-sm btn-danger'>Apagar</button> 
            </a>
        </div>
    </div>  
      
    <!--DETAILS-->
    <div class="row">
        <div class="col-md-12"> 
            <div class="col-md-11">
                <b>Id:</b>
                <?php echo $resultado['situacao_id']; ?>
            </div>
            <div class="col-md-11">
                <b>Nome:</b>    
                <?php echo $resultado['nome']; ?>
            </div>
            <div class="col-md-11">
                <b>Data criacao:</b>  
                <?php echo $resultado['created']; ?>
            </div>
            <div class="col-md-4">
                <b>Data modificacao:</b> 
                <?php echo $resultado['modified']; ?>
            </div>
        </div>
    </div>
    
    <br/>
    <hr>
     <a href="administrativo.php?link=14">Voltar</a>
</div> 


