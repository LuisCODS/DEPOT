  <?php
  session_start(); 
 // --------------------------------------------------------------
 // CONTROLLEUR - MEMBRE  
 //--------------------------------------------------------------- 
include '../dao/MembreDAO.class.php';

	//FORM CHAMPS
	extract($_POST);
	
	//var_dump($action);

	//CRUD
	$membreDAO = new MembreDAO();


	switch ($action) //get hiddin input from form
	{
		case 'insert':

			//print_r($profil);

			$membre = new Membre(null,$nom,$prenom,$profil,$courriel,null,$MDP_membre );
			$membreDAO->insert($membre);//Si ok return 1	
			header('Location: ../view/login/index.php');				
			
			break;

 		case 'update':
			$membre = new Membre($PK_ID_Membre,$nom,$prenom,$profil,$courriel,$tel_membre,$MDP_membre);

			 $membreDAO->update($membre);//Si ok return 1

			 //CREE LA SESSION AVCE L'OBJET MEMBRE
			 $_SESSION["membre"] = serialize($membre);

			 header('Location: ../view/membre/index.php');
			break;

/*		case 'delete':
				echo $membreDAO->delete($PK_ID_Membre);//Si ok return 1
			break;*/
			
		default:
			//echo "Aucun action trouvée";					
			break; 
	 } 



?>