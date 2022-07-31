<?php 
    $result = $connection->query("SELECT * FROM produtos ORDER BY 'id' ")
    or  die($connection->error()); 
    //show total produits
    $num_rows = mysqli_affected_rows($connection);
?> 
<!-- container-->
<div class="container theme-showcase" role="main">
    <div class="page-header">
        <h2>Liste de produtos</h2>
    </div>
    <!--BUTTONS-->
    <div class="row">
        <div class="pull-left">
            <a href="administrativo.php?link=11">
               <button type='button' class='btn btn-sm btn-success'>
                   Cadastrar
               </button> 
           </a>            
        </div>
    </div>
    </br> 
    <p>Total de produtos(<?php echo $num_rows ?>)</p>
    <!--TABLE-->
    <div class="row">
        <div class="col-md-12">
          <table class="table table-striped">
            <thead>
              <tr>                
                <th>Id</th>
                <th>Image</th>
                <th>Nom</th>
                <th>Preco</th>
                <th>Situacao</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            
            <?php
                $result->data_seek(0);
                 while ($row = $result->fetch_array()) 
                {
                    echo"<tr>";                                
                    echo"<td>".$row['id']."</td>";
                    echo"<td><img src='images/".$row['imagem']."' width=100px height=100px></td>";
                    echo"<td>".$row['nome']."</td>";
                    echo"<td>".$row['preco']."</td>";
                    echo"<td>".$row['situacao_id']."</td>";                    
                    ?>
                        <td>
                        <a href='administrativo.php?link=12&id=<?php echo $row['id']; ?> '> 
                            <button type='button' class='btn btn-sm btn-primary'>
                                Visualizar
                            </button> 
                        </a>                               
                        <a href='administrativo.php?link=13&id=<?php echo $row['id']; ?> '> 
                            <button type='button' class='btn btn-sm btn-warning'>
                                Editar
                            </button> 
                        </a>
                        <a href='process/proc_apagar_produto.php?id=<?php echo $row['id']; ?>'> 
                             <button type='button'  class='btn btn-sm btn-danger'>
                                 Apagar
                            </button> 
                        </a>                        
                    <?php
                     echo"</tr>";
                }
            ?>
            </tbody>
          </table>
        </div>
    </div>  
    <!--END-TABLE-->
    <br/>
    <hr>
     <a href="administrativo.php">Voltar</a>
</div> 
<!-- END container  --> 
