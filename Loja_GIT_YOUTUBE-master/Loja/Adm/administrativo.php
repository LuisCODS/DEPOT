<?php
    session_start();
    include_once ("conexao.php"); 
?>
<!doctype html> 
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="images/favicon.ico">
    <title>Page Admin</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="theme.css" rel="stylesheet">
    <script src="js/ie-emulation-modes-warning.js"></script>
  </head>
  <body role="document">       
    <?php      
        // =================  MENU ================= 
        include_once ("menu_admin.php"); 
        
        try {
            // o @ retira a msn de erro
            $link = @$_GET["link"];      
//            filter_input(INPUT_GET, $link);
            
            $pag[1]="bem_vindo.php";
            // USUARIO - CRUD
            $pag[2]="lister_user.php";
            $pag[3]="cad_usuario.php"; 
            $pag[4]="editar_usuario.php"; 
            $pag[5]="visual_usuario.php";
            // CATEGORIE - CRUD
            $pag[6]="cad_categoria.php";  
            $pag[7]="listar_categoria.php";
            $pag[8]="editar_categoria.php"; 
            $pag[9]="visualizar_categoria.php";
            //PRODUTO - CRUD
            $pag[10]="listar_produto.php";
            $pag[11]="cad_produto.php";             
            $pag[12]="visualizar_produto.php"; 
            $pag[13]="editar_produto.php"; 
            //SITUACAO - CRUD
            $pag[14]="listar_situacao.php"; 
            $pag[15]="cad_situacao.php"; 
            $pag[16]="visualizar_situacao.php"; 
            $pag[17]="editar_situacao.php"; 
            
            
            //si pas vide
            if (!empty($link))  {
                if(file_exists($pag[$link]))
                {
                   include $pag[$link];
                }else{
                    include "bem_vindo.php"; 
                }
            }else {
                include "bem_vindo.php"; 
            }
        } 
        catch (Exception $exc){
            echo $exc->getMessage();
        }
    ?>   
        <!--// =================  CONTENT =================--> 
<!--    <div class="container theme-showcase" role="main">

    </div>       -->
    <!-- =================  FOOTER ================= -->
    
    <script src="js/jquery-1.12.4.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/docs.min.js"></script>
    <!--editor de text-->
    <script src="js/ckeditor/ckeditor.js"></script>
    <script src="js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>
