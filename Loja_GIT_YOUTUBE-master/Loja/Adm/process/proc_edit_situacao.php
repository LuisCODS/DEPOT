<?php
    session_start();
    include_once ('../conexao.php');
    $id   = $_POST["id"];
    $nome = $_POST["nome"];

    $connection->query("UPDATE  situacao SET nome = '$nome', modified = NOW() WHERE situacao_id='$id' ");
    $num_rows = mysqli_affected_rows($connection);
  
 ?> 

<?php

    if ($num_rows!= 0) 
    {
        echo "
                <META HTTP-EQUIV=REFRESH CONTENT = '0;URL
                = http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=14'>
                <script type=\"text/javascript\">
                    alert(\"Situacao editada com sucesso.\");
                 </script>
             ";             
    }else { /*renvois a l'index*/
        echo "
        <META HTTP-EQUIV=REFRESH CONTENT = '0;URL = 
        http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=14'>
        <script type=\"text/javascript\">
            alert(\"Falha na edicao.\");
         </script>
     ";
    }        
?>
    
