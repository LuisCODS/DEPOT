<?php    
    $id = $_GET['id'];    
    $result = $connection->query("SELECT * FROM situacao WHERE situacao_id = '$id' LIMIT 1")
    or  die($connection->error()); 
    $resultado = mysqli_fetch_assoc($result);    
?> 
</br>
<div class="container theme-showcase" role="main">
    <div class="page-header">
        <h2>Editar situacao</h2>
    </div>    
    <div class="row">
        <div class="col-md-10">               
            <!--BEGIN FORM-->
            <form class="form-horizontal" method="POST" action="process/proc_edit_situacao.php">
               
                <!--ID -->
                 <input type="hidden"  name="id" value="<?php echo $resultado['situacao_id']; ?>" >            
                
                 <!--NOM-->
                <div class="form-group">
                    <label for="nome" class="col-sm-2 col-form-label">Nome</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="nome" value="<?php echo $resultado['nome']; ?>">
                    </div>
                </div>  
                
                <!--BUTTON-->
                <div class="form-group">
                  <div class="col-sm-10">
                    <button type="submit" name="save" class="btn btn-success">Editar</button>
                  </div>
                </div> 
                
            </form>
            <a href="administrativo.php?link=14">Voltar</a>
        </div>
    </div>  
</div> 
