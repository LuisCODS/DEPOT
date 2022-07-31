<?php
 // ______________________________________________________________________________________________
 // CONTROLLEUR: l'objectif de ce fichier est de traiter les requêtes provenant de la vue et d'interagir  
 // ... avec les modules Model et DAO pour obtenir les informations de la base de données.
 // ______________________________________________________________________________________________ 
	include '../model/Categorie.class.php';
	include '../dao/CategorieDAO.class.php';

	// Get form data(action/txtInput) from moduleFunction.js
	extract($_POST);

	//GLOBAL
	$categorieDAO = new categorieDAO();	


	switch ($action) 
	{
		case 'insert':
			$categorie = new Categorie(null, $NomCategorie);		
			echo $categorieDAO->insert($categorie);//Si ok return 1
		    break;

		case 'update':
			$categorie = new Categorie($Categorie_ID, $NomCategorie);		
			echo $categorieDAO->update($categorie);//Si ok return 1
			break;

		case 'delete':
			echo $categorieDAO->delete($Categorie_ID);//Si ok return 1
			break;

		case 'getCategorie':
			echo $categorieDAO->getCategorie($txtInput);//Si ok return 1
			break;
			
		default:
			echo "Aucun action trouvée";
			break;
	}

?>

