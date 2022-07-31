<?php
    session_start();
    include_once ('../conexao.php');   
    //Get inputs 
    $nome = $_POST["nome"];
    $connection->query("INSERT INTO situacao (nome, created) VALUES('$nome', NOW())");
     //Returns the number of affected rows in a previous MySQL operation
    $num_rows = mysqli_affected_rows($connection);
 ?>   
<?php
    if ($num_rows!= 0) 
    {
        echo "
                <META HTTP-EQUIV=REFRESH CONTENT = '0;URL
                 = http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=14'>
                <script type=\"text/javascript\">
                    alert(\"Situacao cadastrada com sucesso.\");
                 </script>
             ";             
    }else { /*renvois a l'index*/
        echo "
        <META HTTP-EQUIV=REFRESH CONTENT = '0;URL = http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=14'>
        <script type=\"text/javascript\">
            alert(\"Falha no cadastrado da situacao.\");
         </script>
     ";
    }        
?>
    
