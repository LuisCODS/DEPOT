<?php
    session_start();
    echo "Bienvenue usuario ".$_SESSION['userName']."<br/>"."<br/>";   
?>
<a href=login.php>Sair</a>