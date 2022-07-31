<?php
session_start();
require_once("../includes/ConnectionPDO.php");
include '../model/Membre.class.php';

// =================== CONTROLEUR LOGIN =======================

//GET ALL INPUTS FROM FORM
extract($_POST);

// LOGIN

//Evite de tricher le url
if (  isset($_POST["action"])   &  $_POST["action"] == "login")
{
		//echo "test envois form";
		$requette="SELECT * FROM membre WHERE courriel=? AND MDP_membre=? ";
		$stmt = $connexion->prepare($requette);
		$stmt->execute(array($courriel, $MDP_membre));
		$user=$stmt->fetch(PDO::FETCH_OBJ);
		//echo $user->MDP_membre;  
		//print_r($user);

		//Le membre existe
		// if(!empty($user))
		if ($user == true) 
		{
			$membre = new Membre($user->PK_ID_Membre,$user->nom,$user->prenom,$user->profil,$user->courriel,$user->tel_membre,$user->MDP_membre);

			//CREE LA SESSION AVCE L'OBJET EDITÉ
			$_SESSION["membre"] = serialize($membre);

			//GESTION INTERFACE
			if ($user->profil == "admin"){
				header("location: ../view/admin/index.php");
			}else{
				header("location: ../view/membre/index.php");
			}			

		}else{

			//$_SESSION['loginErreur'] = "Courriel ou mot de passe invalide!"
			header("location: ../view/login/index.php");
		}

}else{
	header("location: ../view/login/index.php");
}




 