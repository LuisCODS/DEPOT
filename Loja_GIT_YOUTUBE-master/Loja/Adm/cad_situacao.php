<div class="container theme-showcase" role="main">
    </br>
    <div class="page-header">
        <h2>Cadastrar situacao</h2>      
    </div>
    <div class="row espace">
        <div class="col-md-8">                
            <!--BEGIN FORM-->
            <form class="form-horizontal" method="POST" action="process/proc_cad_situacao.php">                
                
                <!--NOM-->
                <div class="form-group row">
                    <label for="nome" class="col-sm-2 col-form-label">Nome da situacao</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control"  name="nome" placeholder="nome" required>
                    </div>
                </div>
                
                <!--BUTTON CADASTRAR-->
                <div class="form-group row">
                  <div class="col-sm-10">
                    <button type="submit" name="save" class="btn btn-success">Cadastrar</button>
                  </div>
                </div> 
                
            </form>
            <!--END FORM-->
        </div>
    </div>  
    <hr/>
     <a href="administrativo.php?link=14">Voltar</a>
</div> 
