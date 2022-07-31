<?php
session_start();
include_once ('../conexao.php');

        $id               = $_POST["id"];
        $nome            = $_POST["nome"];
        $email           = $_POST["email"];
        $login           = $_POST["login"];
        $senha           = $_POST["senha"];
        $nivel_acesso_id = $_POST["nivel_acesso_id"];

    try {
        $connection->query("UPDATE  usuarios SET nome = '$nome',
                                                email = '$email', 
                                                login = '$login', 
                                                senha = '$senha', 
                                                nivel_acesso_id = '$nivel_acesso_id', 
                                                modified = NOW() WHERE id='$id' ");
        $num_rows = mysqli_affected_rows($connection);
    } catch (Exception $ex)  {
        echo $exc->getTraceAsString();
    }    
 ?> 
<!--descomentar a estrutura html se tiver problema com a acentuacao na hora de mostrar-->
<!--<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"></head>
<body>    -->
<?php

    if ($num_rows!= 0) 
    {
        echo "
                <META HTTP-EQUIV=REFRESH CONTENT = '0;URL
                = http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=2'>
                <script type=\"text/javascript\">
                    alert(\"Usuario editado com sucesso.\");
                 </script>
             ";             
    }else { /*renvois a l'index*/
        echo "
        <META HTTP-EQUIV=REFRESH CONTENT = '0;URL = 
        http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=2'>
        <script type=\"text/javascript\">
            alert(\"Falha na edicao.\");
         </script>
     ";
    }        
?>
<!--</body>
</html> -->
    
