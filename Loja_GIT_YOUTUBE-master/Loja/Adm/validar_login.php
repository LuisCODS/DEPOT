<?php
    session_start();    
    include_once("conexao.php");
    
    //GETS VALUES FROM FORM
    $login = $_POST['usuario'];
    $senha = $_POST['senha']; 
    
     $result = $connection->query("SELECT * FROM usuarios WHERE login= '$login' AND senha = '$senha' LIMIT 1") or die($connection->error); 
     $row = $result->fetch_assoc();
     
     //use not exist
    if (empty($row)) {
        //create global var
        $_SESSION['loginErro']= "Usuario ou senha invalida";
        //redirect user to login
        header("location: login.php"); 
    }else {
        //USER EXISTS AND LOAD HIS DATAS!
        $_SESSION['userID']          = $row['id'];
        $_SESSION['userName']        = $row['nome'];
        $_SESSION['userEmail']       = $row['email'];
        $_SESSION['userLogin']       = $row['login'];
        $_SESSION['userSenha']       = $row['senha'];    
        $_SESSION['userAcesso']      = $row['nivel_acesso_id'];  
                
        if ( $_SESSION['userAcesso'] == 1)        
           header("Location: administrativo.php");  
        else            
           header("Location: usuario.php");   
    }
//    @mysql_close($connection);
?>

