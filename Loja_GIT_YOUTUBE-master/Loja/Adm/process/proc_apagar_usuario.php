<?php
    session_start();
    include_once ('../conexao.php');
    $id = $_GET["id"];
    $connection->query("DELETE  FROM usuarios WHERE id='$id' ");
    $num_rows = mysqli_affected_rows($connection);   
 ?>
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
                    alert(\"Usuario apagado com sucesso.\");
                 </script>
             ";             
    }else { /*renvois a l'index*/
        echo "
        <META HTTP-EQUIV=REFRESH CONTENT = '0;URL = 
        http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=2'>
        <script type=\"text/javascript\">
            alert(\"Usuario nao foi apagado.\");
         </script>
     ";
    }        
?>
<!--</body>
</html> -->
    
