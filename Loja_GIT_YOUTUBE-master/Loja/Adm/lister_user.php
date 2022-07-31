<?php 
    $result = $connection->query("SELECT * FROM usuarios ORDER BY 'id' ")
    or  die($connection->error()); 
    $num_rows = mysqli_affected_rows($connection);
?> 
<!-- container-->
<div class="container theme-showcase" role="main">
    <div class="page-header">
        <h2>Liste de usuarios</h2>
    </div>
    <!--BUTTONS-->
    <div class="row">
        <div class="pull-left">
            <a href="administrativo.php?link=3">
               <button type='button' class='btn btn-sm btn-success'>
                   Cadastrar
               </button> 
           </a>            
        </div>
    </div>
    </br> 
    <p>Total de usuarios(<?php echo $num_rows ?>)</p>
    <!--TABLE-->
    <div class="row">
        <div class="col-md-12">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Id</th>
                <th>Nom</th>
                <th>E-mail</th>
                <th>Niveau d'acces</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()) 
                {
                    echo"<tr>";
                    echo"<td>".$row['id']."</td>";
                    echo"<td>".$row['nome']."</td>";
                    echo"<td>".$row['email']."</td>";
                    echo"<td>".$row['nivel_acesso_id'].
                        "</td>";                    
                    ?>
                        <td>
                        <a href='administrativo.php?link=5&id=<?php echo $row['id']; ?> '> 
                            <button type='button' class='btn btn-sm btn-primary'>
                                Visualizar
                            </button> 
                        </a>                               
                        <a href='administrativo.php?link=4&id=<?php echo $row['id']; ?> '> 
                            <button type='button' class='btn btn-sm btn-warning'>
                                Editar
                            </button> 
                        </a>
                        <a href='process/proc_apagar_usuario.php?id=<?php echo $row['id']; ?>'> 
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
