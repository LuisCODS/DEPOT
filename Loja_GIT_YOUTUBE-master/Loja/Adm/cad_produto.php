<div class="container theme-showcase" role="main">
    </br>
    <div class="page-header">
        <h2>Cadastrar Produto</h2>      
    </div>
    <div class="row">
        <div class="col-md-10">                 
            <!--BEGIN FORM-->
            <form enctype="multipart/form-data" class="form-horizontal" 
                  method="POST" 
                  action="process/proc_cad_produto.php">              
                
                <!--NOM-->
                  <div class="form-group row">
                    <label for="nome" class="col-sm-2 col-form-label">Nome do produto</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="nome" placeholder="nome do produto" required>
                  </div>
                </div>
                
                <!--DESCRICAO CURTA-->
                <div class="form-group row">
                    <label for="descricao_curta" class="col-sm-2 col-form-label">Descricao Curta</label>
                    <div class="col-sm-10">
                        <textarea class="form-control ckeditor" rows="2" cols="30" 
                                  name="descricao_curta"
                                  placeholder="descricao curta do produto" >                                      
                        </textarea>
                  </div>
                </div>
                
                 <!--DESCRICAO LONGA-->
                <div class="form-group row">
                    <label for="descricao_longa" class="col-sm-2 col-form-label">Descricao Longa</label>
                    <div class="col-sm-10">
                        <textarea class="form-control ckeditor" rows="2" cols="30"  name="descricao_longa"
                                  placeholder="descricao longa do produto" >                                      
                        </textarea>
                  </div>
                </div>
                 
                 <!--PRECO-->
                  <div class="form-group row">
                    <label for="preco" class="col-sm-2 col-form-label">Preco</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="preco" placeholder="preco" required>
                  </div>
                </div>  
                 
                <!--TAG-->
                <div class="form-group row">
                    <label for="tag" class="col-sm-2 col-form-label">Tag</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="tag" placeholder="tag" required>
                  </div>
                </div>    
                
                <!--DESCRIPTION-->
                <div class="form-group row">
                    <label for="description" class="col-sm-2 col-form-label">Description</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="description" placeholder="Description" required>
                  </div>
                </div> 
                
                 <!--IMAGE-->
                <div class="form-group">
                    <label for="arquivo" class="col-sm-2 col-form-label">Upload photo</label>
                    <input type="file" name="pic" accept="image/*" required>
<!--                    <input type="hidden" name="sizeFile" value="1000000">-->
                </div>
                 
                <!--CATEGORIE-->
               <div class="form-group">
                    <label for="categoria_id" class="col-sm-2 col-form-label">Categorias</label>
                    <div class="col-sm-4">
                        <select class="form-control" name="categoria_id" required >
<!--                            <option>Selecione</option>-->
                            <option value=""></option>
                            <?php
                                $result = $connection->query("SELECT * FROM categorias"); 
//                                $result->data_seek(0);
                                while ($data = $result->fetch_assoc()) 
                                {
                            ?>
                                <option value="<?php echo $data['id']; ?>"><?php echo $data['nome']; ?></option>
                            <?php                                    
                                }  
                            ?>
                    </select>
                  </div>
                </div>
               
                <!--SITUACAO-->
               <div class="form-group">
                    <label for="$situacao_id" class="col-sm-2 col-form-label">Situacao</label>
                    <div class="col-sm-4">
                        <select required  class="form-control" name="situacao_id">
                            <option value=""></option>
                            <?php
                                $result = $connection->query("SELECT * FROM situacao"); 
                                $result->data_seek(0);
                                while ($data = $result->fetch_assoc()) 
                                {
                            ?>
                                <option value="<?php echo $data['situacao_id']; ?>"><?php echo $data['nome']; ?></option>
                            <?php                                    
                                }  
                            ?>
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
