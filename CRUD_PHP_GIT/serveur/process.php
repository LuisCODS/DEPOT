<?php
//Ouverture de session: esta função deve ser chamada antes de qualquer tags HTML.
session_start();
// echo session_id();  teste only

//open a connection
$mysqli = new mysqli('localhost','root','','crud') 
or die(mysqli_error($mysqli));



$id = 0;
$update = false;
$name = '';
$location = '';

// =============== BUTTON SAVE ===============
if (isset($_POST['save']))// if button was pressed
{    
    //Get inputs 
    $name     = $_POST['name']; 
    $location = $_POST['location']; 
	
     //insert into data base
    $mysqli->query("INSERT INTO data(name, location)
	VALUES('$name','$location')") or die($mysqli->error());          
    $_SESSION["message"] = "Record has been saved! ";
    $_SESSION["msg_type"] = "success";  
    /* Fermeture de la connexion 
    $mysqli->close();
    */    
    //echo( " <br>"." <br>"." <a href=javascript:history.back()>Go Back</a> "   );
    header("location: ../index.php"); //redirect user to index
}

// =============== BUTTON DELETE ===============
if (isset($_GET['delete'])) // if button was pressed
{    
    //Get input
    $id = $_GET['delete'];    
    $mysqli->query("DELETE FROM data WHERE id=$id") 
	or  die($mysqli->error());  
    
    $_SESSION["message"] = "Record has been deleted! ";
    $_SESSION["msg_type"] = "danger";
        
    /* Fermeture de la connexion 
    $mysqli->close();
    */
    header("location: ../index.php"); //redirect user to index
}

// =============== BUTTON EDIT ===============
if (isset($_GET['edit'])){    
    $id = $_GET['edit'];
    $update = true;
    $result = $mysqli->query("SELECT * FROM data WHERE id=$id") 
	or  die($mysqli->error()); 
    //echo print_r($result->fetch_array()); // test only
    //si le registre existe
    $row = $result->fetch_array();
    if ( $row['name'] != '') {
        $name = $row['name'];
        $location = $row['location'];
    }   
}

// =============== BUTTON UPDATE ===============
if (isset($_POST['update']))
{    
    $id = $_POST['id'];
    $name = $_POST['name'];
    $location = $_POST['location']; 
    $mysqli->query("UPDATE data  SET name='$name', location='$location' WHERE id=$id")
            or  die($mysqli->error()); 

    $_SESSION["message"] = "Record has been update! ";
    $_SESSION["msg_type"] = "warning";
    header("location: ../index.php"); //redirect user to index

}






