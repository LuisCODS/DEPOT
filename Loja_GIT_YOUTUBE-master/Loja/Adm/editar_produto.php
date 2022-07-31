<?php    
    $id = $_GET['id'];    
    //echo "</br></br></br></br></br>".$id; // teste to get id
    $result = $connection->query("SELECT * FROM produtos WHERE id = '$id' LIMIT 1")
    or  die($connection->error()); 
    $resultado = mysqli_fetch_assoc($result);     
   
?> 
</br>
<div class="container theme-showcase" role="main">
    <div class="page-header">
        <h2>Editar Produto</h2>
    </div>    
    <div class="row">
        <div class="col-md-10">                       
            <!--BEGIN FORM-->
            <form class="form-horizontal" method="POST" 
                  action="process/proc_edit_produto.php" 
                  enctype="multipart/form-data">
                
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
                
                <!--DESCRICAO CURTA-->
                <div class="form-group row">
                    <label for="descricao_curta" class="col-sm-2 col-form-label">Descricao Curta</label>
                    <div class="col-sm-10">
                        <textarea class="form-control ckeditor" rows="2" 
                                  name="descricao_curta"
                                  placeholder="descricao curta do produto"
                                  <?php echo $resultado['descricao_curta']; ?> >                                      
                        </textarea>
                  </div>
                </div>
                
                 <!--DESCRICAO LONGA-->
                <div class="form-group row">
                    <label for="descricao_longa" class="col-sm-2 col-form-label">Descricao Longa</label>
                    <div class="col-sm-10">
                        <textarea class="form-control ckeditor" rows="2" 
                                  name="descricao_longa"
                                  placeholder="descricao longa do produto" 
                                  <?php echo $resultado['descricao_longa']; ?> >                                      
                        </textarea>
                  </div>
                </div>       
                 
                 <!--PRECO-->
                  <div class="form-group row">
                    <label for="preco" class="col-sm-2 col-form-label">Preco</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="preco" placeholder="preco"
                                value="<?php echo $resultado['preco']; ?>" >
                  </div>
                </div>  
                 
                <!--TAG-->
                <div class="form-group row">
                    <label for="tag" class="col-sm-2 col-form-label">Tag</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="tag" placeholder="tag"
                                value=" <?php echo $resultado['tag']; ?>" >
                  </div>
                </div>     
                
                <!--DESCRIPTION-->
                <div class="form-group row">
                    <label for="description" class="col-sm-2 col-form-label">Description</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="description" placeholder="Description"
                                value=" <?php echo $resultado['description']; ?>" >
                  </div>
                </div> 
                
                 <!--NEW UPLOAD -->
                <div class="form-group row">
                    <label for="arquivo" class="col-sm-2 col-form-label">New Unpload</label>
                    <div class="col-sm-10">
                        <input type="file" name="pic" accept="image/*">
                    </div>                           
                </div>         
                <?php  
                    $foto = $resultado['imagem']; 
                ?>
                 
                 <!-- FOTO ATUAL-->
                 <div class="form-group row">
                    <label for="arquivo" class="col-sm-2 control-label">Foto atual</label>                      
                    <div class="col-sm-10">
                        <img src="<?php echo "images/".$resultado['imagem']." "; ?>" width="100"  height="100">
<!--                        <input type="hidden" name="nome_img_atual" value="<?php echo $resultado['imagem']; ?>" > -->
                    </div> 
                </div>
             
                 <!--CATEGORIE-->
               <div class="form-group">
                    <label for="categoria_id" class="col-sm-2 col-form-label">Categorias</label>
                    <div class="col-sm-4">
                        <select class="form-control" name="categoria_id">
                            <option>Selecione</option>
                            <?php
                                $result = $connection->query("SELECT * FROM categorias")
                                or  die($connection->error());                                
                                while ($dados = mysqli_fetch_assoc($result)) {
                            ?>
                                <option value="<?php echo $dados['id'];?>"
                            <?php 
                                 // SI ID DA TAB categorias É = A categoria_id DA TAB PRODUTO 
                                if($dados['id'] == $resultado['categoria_id'])
                                {echo 'selected';} 
                            ?>
                                 >
                            <?php echo $dados['nome']; ?>
                                </option>
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
                        <select class="form-control" name="situacao_id">
                            <option>Selecione</option>
                            <?php
                                $result = $connection->query("SELECT * FROM situacao")
                                or  die($connection->error()); 
                               while ($dados = mysqli_fetch_assoc($result)) {
                            ?>
                                <option value="<?php echo $dados['situacao_id']; ?>"
                            <?php 
                                // comparaison des cle etrangeres 
                                if($dados['situacao_id'] == $resultado['situacao_id'])
                                {echo 'selected';} 
                            ?>  
                                >
                            <?php echo $dados['nome']; ?>
                                </option>
                            <?php                                    
                                }  
                            ?>
                    </select>
                  </div>
                </div>  
                
                 <!--BUTTON-->
                <div class="form-group">
                  <div class="col-sm-10">
                    <button type="submit" name="save" class="btn btn-success">Confirmar</button>
                  </div>
                </div>  
            </form>   
                <!--  BUTTONS-->
                <div class="row">
                    <div class="pull-left">
                        <a href='administrativo.php?link=10'>
                           <button type='button' class='btn btn-sm btn-info'>Listar</button> 
                       </a>            
                         <a href='administrativo.php?link=4&id=<?php echo $resultado['id']; ?> '>
                           <button type='button' class='btn btn-sm btn-danger'>Apagar</button> 
                       </a>
                    </div>
                </div>
                
            <a href="administrativo.php?link=10">Voltar</a>
        </div>
    </div>      
</div> 
