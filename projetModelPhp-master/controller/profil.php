<?php
 // --------------------------------------------------------------
 // CONTROLLEUR DU PROFIL
 //--------------------------------------------------------------- 
	include '../model/Profil.class.php';
	include '../dao/ProfilDAO.class.php';

	// Get data(action/txtInput) from moduleFunction.js
	extract($_POST);

	//GLOBAL
	$profilDAO = new ProfilDAO();	


	switch ($action) 
	{
		case 'insert':
			// removes whitespace from both sides of a string
			//var $strNoWhiteSpace = $ProfilNom.trim();

			$newProfil = new Profil(null, $ProfilNom);		
			echo $profilDAO->insert($newProfil);//Si ok return 1
		    break;

		case 'update':
			$profil = new Profil($Profil_ID, $ProfilNom);		
			echo $profilDAO->update($profil);//Si ok return 1
			break;

		case 'delete':
			echo $profilDAO->delete($Profil_ID);//Si ok return 1
			break;

		case 'getProfil':
			echo $profilDAO->getProfil($txtInput);//Si ok return 1
			break;
			
		default:
			echo "Aucun action trouvée";
			break;
	}

?>

