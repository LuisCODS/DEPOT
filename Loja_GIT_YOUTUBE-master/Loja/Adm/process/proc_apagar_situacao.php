<?php
    session_start();
    include_once ('../conexao.php');
    $id = $_GET["id"];
    $connection->query("DELETE  FROM Situacao WHERE situacao_id='$id' ");
    $num_rows = mysqli_affected_rows($connection);   
 ?>
<?php
    if ($num_rows!= 0) 
    {
        echo "
                <META HTTP-EQUIV=REFRESH CONTENT = '0;URL
                = http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=14'>
                <script type=\"text/javascript\">
                    alert(\"Situacao apagada com sucesso.\");
                 </script>
             ";             
    }else { /*renvois a l'index*/
        echo "
        <META HTTP-EQUIV=REFRESH CONTENT = '0;URL = 
        http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=14'>
        <script type=\"text/javascript\">
            alert(\"Situacao nao foi apagada.\");
         </script>
     ";
    }        
?>
<!--</body>
</html> -->
    
