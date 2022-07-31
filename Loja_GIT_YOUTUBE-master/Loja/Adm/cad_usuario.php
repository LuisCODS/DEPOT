<div class="container theme-showcase" role="main">
    </br>
    <div class="page-header">
        <h2>Cadastrar Usuario</h2>      
    <!--BUTTONS-->
    </div>
    <div class="row">
        <div class="col-md-10">                 
            <!--BEGIN FORM-->
            <form class="form-horizontal" method="POST" action="process/proc_cad_usuario.php">              
                
                <!--NOM-->
                  <div class="form-group row">
                    <label for="nome" class="col-sm-2 col-form-label">Nome</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="nome"  placeholder="nome" required>
                  </div>
                </div>
                
                <!--E-MAIL-->
                <div class="form-group row">
                    <label for="email" class="col-sm-2 col-form-label">E-mail</label>
                    <div class="col-sm-4">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                  </div>
                </div>
                
                <!--USER-->
                <div class="form-group row">
                    <label for="login" class="col-sm-2 col-form-label">Login</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control" name="login" placeholder="login" required>
                  </div>
                </div>
              
                <!--MDP-->
                <div class="form-group row">
                    <label for="senha" class="col-sm-2 col-form-label">Senha</label>
                    <div class="col-sm-3">
                    <input type="password" class="form-control" name="senha" placeholder="Senha" required>
                  </div>
                </div>
               
                <!--CONFIRMATION MDP-->
<!--                    <div class="form-group row">
                    <label for="mdp_confirmation" class="col-sm-2 col-form-label">Confirmation senha</label>
                    <div class="col-sm-3">
                    <input type="password" class="form-control" name="confirme_senha" 
                           placeholder="Confirmer">
                  </div>
                </div>-->
               
                <!--NIVEAU D'ACCES-->
                <div class="form-group row">
                    <label for="nivel_acesso_id" class="col-sm-2 col-form-label">Niveau d'acces</label>
                    <div class="col-sm-3">
                        <select required class="form-control" name="nivel_acesso_id" >
                            <option value=""></option>
                            <option value="1">Admin</option>
                            <option value="2">Membre</option>
                          </select>
                  </div>
                </div>
               
                <!--BUTTON-->
                <div class="form-group row">
                  <div class="col-sm-10">
                    <button type="submit" name="save" class="btn btn-success">Ajouter</button>
                  </div>             
                </div>
                
            </form>
            <!--END FORM-->
        </div>
    </div>    
     <a href="administrativo.php">Voltar</a>
</div> 
