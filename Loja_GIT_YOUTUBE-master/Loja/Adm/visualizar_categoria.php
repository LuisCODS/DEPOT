<!-- =================  VISUALIZAR USUARIO ================= -->
<?php    
    $id = $_GET['id'];
    $result = $connection->query("SELECT * FROM categorias WHERE id = '$id' LIMIT 1")
    or  die($connection->error()); 
    $resultado = mysqli_fetch_assoc($result);    
?> 
<div class="container theme-showcase" role="main">
    <div class="page-header">
        <h2>Detalhes da categoria</h2>
    </div>    
    <!--    BUTTONS-->
    <div class="row">
        <div class="pull-right">
            <a href='administrativo.php?link=7&id=<?php echo $resultado['id']; ?> '>
               <button type='button' class='btn btn-sm btn-info'>Listar</button> 
           </a>            
            <a href='administrativo.php?link=8&id=<?php echo $resultado['id']; ?> '> 
               <button type='button' class='btn btn-sm btn-warning'>Editar</button> 
           </a>
             <a href='process/proc_apagar_categoria.php?id=<?php echo $resultado['id']; ?>'> 
               <button type='button' class='btn btn-sm btn-danger'>Apagar</button> 
            </a>
        </div>
    </div>    
    <div class="row">
        <div class="col-md-12"> 
            <div class="col-md-11">
                <b>Id:</b>
                <?php echo $resultado['id']; ?>
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
     <a href="administrativo.php?link=7">Voltar</a>
</div> 
<br/>

