<?php
session_start(); 
require_once("../../includes/ConnectionPDO.php");
include_once '../../dao/Film_LocationDAO.class.php';

/*GLOBAL*/
$locationDAO = new Film_LocationDAO();


// ================ AJOUTER PANIER =====================
   if (isset($_POST['id_film']) && $_POST['id_film'] != "")
   {  
       //Get id from form post
       $idFilm = $_POST['id_film'];

      //print_r($idFilm);
      
       //Si panier vide
       if (!isset ($_SESSION['panier'][$idFilm]) )
       {  
            //Affecte l'indice(idFilm) à 1(premiere fois intem ajouté)
            $_SESSION['panier'][$idFilm] = 1;
            header("location: listerFilm.php");
       }else{
            //increment la quantité
            $_SESSION['panier'][$idFilm] += 1;
              header("location: listerFilm.php");
       } 
   }

//===============  ENLEVER ITEM DU PANIER  ===============    
if ( isset($_GET['remove'])  &&  $_GET['remove'] == "item")
{
	//Get id film inside url
	$idFilm = $_GET['id'];
	//Delete seulement la session que contient l'id do film
	unset($_SESSION['panier'][$idFilm]);
	//echo '<META HTTP-EQUIV="REFRESH" CONTENT="0; URL=panie.php"/> ';
	header("location: panier.php");
}


// ======================= VIDER PANIER =======================
if (isset($_POST['action']) && $_POST['action'] =="videPanier")
{
    //Si pas vide 
    if(!empty($_SESSION["panier"])) 
    {
      unset($_SESSION["panier"]);      
      //refresh page
      //echo("<meta http-equiv='refresh' content='0'>"); 
      	header("location: panier.php");
    }
}

// ======================= AJOUTER DANS LOCATION ================

// if ( isset($_GET['ajouter'])  &&  $_GET['ajouter'] == "location")
// {
//   //Get id film et membre

//   // $idFilm = $_GET['idFilm'];
//   // $idMembre = $_GET['idMembre'];

//   print_r($idMembre);
//   //CREATE A LOCATION
//   $location = new Film_Location(null,$idFilm, $idMembre);

//   //ADD LOCATION DANS LA BD
//  global  $locationDAO->insert($idFilm, $idMembre);

//   //header("location: index.php");
// }






?>