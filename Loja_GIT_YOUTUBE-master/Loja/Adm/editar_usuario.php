<!-- =================  EDITAR USUARIO ================= -->
<?php    
    $id = $_GET['id'];    
    //echo "</br></br></br></br></br>".$id; // teste to get id
    $result = $connection->query("SELECT * FROM usuarios WHERE id = '$id' LIMIT 1")
    or  die($connection->error()); 
    $resultado = mysqli_fetch_assoc($result);    
?> 
</br>
<div class="container theme-showcase" role="main">
    <div class="page-header">
        <h2>Editar usuario</h2>
    </div>    
    <div class="row">
        <div class="col-md-10">               
            <!--BEGIN FORM-->
            <form class="form-horizontal" method="POST" action="process/proc_edit_usuario.php">
                <!--ID -->
                 <input type="hidden"  name="id" value="<?php echo $resultado['id']; ?>" >            
                <!--NOM-->
                <div class="form-group">
                    <label for="nome" class="col-sm-2 col-form-label">Nome</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  
                               name="nome" 
                               value="<?php echo $resultado['nome']; ?>">
                    </div>
                </div>
                <!--E-MAIL-->
                <div class="form-group ">
                    <label for="email" class="col-sm-2 col-form-label">E-mail</label>
                    <div class="col-sm-4">
                    <input type="email" class="form-control" name="email" 
                           value="<?php echo $resultado['email']; ?>">
                  </div>
                </div>
                <!--USER-->
                <div class="form-group ">
                    <label for="login" class="col-sm-2 col-form-label">Usuario</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control" name="login" 
                               value="<?php echo $resultado['login']; ?>">
                  </div>
                </div>
                <!--MDP-->
                <div class="form-group ">
                    <label for="senha" class="col-sm-2 col-form-label">Senha</label>
                    <div class="col-sm-3">
                        <input type="password" class="form-control" name="senha" 
                               value="<?php echo $resultado['senha']; ?>">
                     </div>
                </div>                                
                <!--NIVEAU D'ACCES-->
                <div class="form-group ">
                    <label for="nivel_acesso_id" class="col-sm-2 col-form-label">Niveau d'acces</label>
                    <div class="col-sm-3">
                        <select class="form-control" name="nivel_acesso_id">
                            <option>Selecione</option>
                                <option value="1"
                                  <?php 
                                        if ($resultado['nivel_acesso_id'] == 1) 
                                        {
                                            echo 'selected';
                                        }
                                  ?>    
                                  >Admin</option>
                                <option value="2"
                                  <?php 
                                        if ($resultado['nivel_acesso_id'] == 2) 
                                        {
                                            echo 'selected';
                                        }
                                  ?>                                                                              
                                 >Membre</option>
                          </select>
                  </div>
                </div>                                                        
                <!--BUTTON-->
                <div class="form-group">
                  <div class="col-sm-10">
                    <button type="submit" name="save" class="btn btn-success">Editar</button>
                  </div>
                </div>                
            </form>
            <a href="administrativo.php?link=2">Voltar</a>
        </div>
    </div>  
</div> 
