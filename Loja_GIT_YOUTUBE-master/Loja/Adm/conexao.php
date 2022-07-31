<!--
Se connecter et sélectioner une base de données MySQL nommé tcc
@parm: Hostname:localhost, username: root, password:  , dbName: tcc
-->
<?php
    $connection = new mysqli('localhost','root','','tcc') or die(mysqli_error($connection));
?>