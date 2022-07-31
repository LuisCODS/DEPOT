<?php
session_start();
include_once ('../conexao.php');
   
    //Get inputs 
    $nome            = $_POST["nome"];
    $email           = $_POST["email"];
    $login           = $_POST["login"];
    $senha           = $_POST["senha"];
    $nivel_acesso_id = $_POST["nivel_acesso_id"];

    $connection->query("INSERT INTO usuarios (nome, email, login, senha, nivel_acesso_id, createdDate)
    VALUES('$nome','$email','$login','$senha','$nivel_acesso_id', NOW())");

     //Returns the number of affected rows in a previous MySQL operation
    $num_rows = mysqli_affected_rows($connection);
 ?> 
<!--================== END PHP ======================================-->
<!--descomentar a estrutura html se tiver problema com a acentuacao na hora de mostrar-->
<!--<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"></head>
<body>-->
<!--================== BEGIN PHP ======================================-->    
<?php

    if ($num_rows!= 0) 
    {
        echo "
                <META HTTP-EQUIV=REFRESH CONTENT = '0;URL
                 = http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=2'>
                <script type=\"text/javascript\">
                    alert(\"Usuario cadastrado com sucesso.\");
                 </script>
             ";             
    }else { /*renvois a l'index*/
        echo "
        <META HTTP-EQUIV=REFRESH CONTENT = '0;URL = http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/index.php'>
        <script type=\"text/javascript\">
            alert(\"Falha no cadastrado.\");
         </script>
     ";
    }        
?>
<!--</body>
</html> -->
    
