<?php
    include_once ('../conexao.php');
                     
    $id                = $_POST["id"];
    $nome              = $_POST["nome"];
    $descricao_curta   = $_POST["descricao_curta"];
    $descricao_longa   = $_POST["descricao_longa"]; 
    $preco             = $_POST["preco"];
    $tag               = $_POST["tag"];
    $description       = $_POST["description"];
//    $description       = strip_tags($_POST["description"]);
    $pic               = $_FILES["pic"]["name"];
    $categoria_id      = $_POST["categoria_id"];
    $situacao_id       = $_POST["situacao_id"];

        if($pic == "")  
        {  
            $connection->query("UPDATE  produtos SET "
                                . "nome='$nome',"
                                . "descricao_curta='$descricao_curta',"
                                . " descricao_longa='$descricao_longa',"
                                . " preco='$preco',"
                                . " tag='$tag',"
                                . "description='$description',"
                                . " categoria_id='$categoria_id',"
                                . " situacao_id='$situacao_id',"
                                . " modified = NOW() WHERE id='$id' ");                
            $num_rows = mysqli_affected_rows($connection);
        } else{
                //extrai os ultimo 4 caracteres a partir do nome do arquivo colocando-os em caixa baixa
              $extension = strtolower(substr($_FILES['pic']['name'],-4));              
               $new_name = date("Y.m.d-H.i.s").$extension; 
              $diretorio = '../images/';               
              move_uploaded_file($_FILES['pic']['tmp_name'], $diretorio.$new_name); 
              @unlink($_FILES['pic']['tmp_name']); //effacer le fichier temporaire   
              $pic =  $new_name;
            
            $connection->query("UPDATE  produtos SET "
                                . "nome='$nome',"
                                . "descricao_curta='$descricao_curta',"
                                . " descricao_longa='$descricao_longa',"
                                . " preco='$preco',"
                                . " tag='$tag',"
                                . "description='$description',"
                                . "imagem='$pic',"
                                . " categoria_id='$categoria_id',"
                                . " situacao_id='$situacao_id',"
                                . " modified = NOW() WHERE id='$id' ");                
            $num_rows = mysqli_affected_rows($connection);
        }    
 ?>  

<?php
    if ($num_rows!= 0) 
    {           
        echo "
                <META HTTP-EQUIV=REFRESH CONTENT = '0;URL
                 = http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=10'>
                <script type=\"text/javascript\">
                    alert(\"Produto editado com sucesso.\");                   
                 </script>
             ";             
    }else 
    { 
    
        echo "
        <META HTTP-EQUIV=REFRESH CONTENT = '0;URL = http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=10'>
        <script type=\"text/javascript\">
            alert(\"Falha na edicao.\");
         </script>
     ";
    }        
?>  
