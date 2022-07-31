<?php
    include_once ('../conexao.php');
    
    
    $nome              = strip_tags($_POST["nome"]);
    $descricao_curta   = strip_tags($_POST["descricao_curta"]);
    $descricao_longa   = strip_tags($_POST["descricao_longa"]);            
    $preco             = strip_tags($_POST["preco"]);
    $tag               = strip_tags($_POST["tag"]);
    $description       = strip_tags($_POST["description"]);
//    $pic               = $_FILES["pic"]["name"];
    $categoria_id      = $_POST["categoria_id"];
    $situacao_id       = $_POST["situacao_id"];
//    $sizeFile          = $_POST["sizeFile"];
   
// ======================== GESTION IMAGE ======================== 
//     if ($_FILES['pic']['size'] > 1000000) {
//        throw new RuntimeException('Exceeded filesize limit.');
//    }
        //if upload button is pressed
        if( isset($_FILES['pic']) )
        {
            
               //extrai os ultimo 4 caracteres a partir do nome do arquivo colocando-os em caixa baixa
              $extension = strtolower(substr($_FILES['pic']['name'],-4));              
              //Definindo um novo nome unico 
               $new_name = date("Y.m.d-H.i.s").$extension; 
//              $new_name = md5(time().$extension ); //outra possibilidade
              //Diretório para uploads 
              $diretorio = '../images/'; 
              
              //executa o  upload do arquivo.
              //Parm 1 : fileName, parm 2: location
              //...move o arquivo temporario criado pelo PHP para o diretorio atribuindo ao arquivo um novo nome
              move_uploaded_file($_FILES['pic']['tmp_name'], $diretorio.$new_name); 
              @unlink($_FILES['pic']['tmp_name']); //effacer le fichier temporaire                         
        }
        
        $connection->query("INSERT INTO produtos (nome,descricao_curta,descricao_longa,preco,tag,description,imagem,categoria_id,situacao_id,created)                       
        VALUES('$nome','$descricao_curta','$descricao_longa','$preco','$tag','$description','$new_name','$categoria_id','$situacao_id', NOW())");
        //Returns the number of affected rows in a previous MySQL operation
        $num_rows = mysqli_affected_rows($connection);
        
 ?>  
<!--======================== END GESTION IMAGE ========================-->

<?php
    if ($num_rows!= 0) 
    {           
        echo "
                <META HTTP-EQUIV=REFRESH CONTENT = '0;URL
                 = http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=10'>
                <script type=\"text/javascript\">
                    alert(\"Produto cadastrado com sucesso.\");                   
                 </script>
             ";             
    }else 
    { 
    
        echo "
        <META HTTP-EQUIV=REFRESH CONTENT = '0;URL = http://localhost/YOUTUBE/Loja_GIT/Loja/Adm/administrativo.php?link=10'>
        <script type=\"text/javascript\">
            alert(\"Falha no cadastrado.\");
         </script>
     ";
    }        
?>  
