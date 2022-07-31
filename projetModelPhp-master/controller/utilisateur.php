<?php
 // --------------------------------------------------------------
 // CONTROLLEUR - UTILISATEUR  
 //--------------------------------------------------------------- 
	include '../model/Utilisateur.class.php';
	include '../dao/UtilisateurDAO.class.php';

	// Get data(action/txtInput) from moduleFunction.js
	extract($_POST);

	//GLOBAL
	$utilisateurDAO = new UtilisateurDAO();	


	switch ($action) 
	{
		case 'insert':
			$user = new Utilisateur(null,$Profil_ID,$UtilisateurName,$UtilisateurNickName,$UtilisateurMDP,$UtilisateurEmail);		
			echo $utilisateurDAO->insert($user);//Si ok return 1
		    break;

		case 'update':
			$user = new Utilisateur($Utilisateur_ID,$Profil_ID,$UtilisateurName,$UtilisateurNickName,$UtilisateurMDP,$UtilisateurEmail);
			echo $utilisateurDAO->update($user);//Si ok return 1
			break;

		case 'delete':
				echo $utilisateurDAO->delete($Utilisateur_ID);//Si ok return 1
			break;

		case 'getUtilisateur':
			echo $utilisateurDAO->getUtilisateur($txtInput);//Si ok return 1
			break;
			
		default:
			echo "Aucun action trouvée";
			break;
	}

?>

