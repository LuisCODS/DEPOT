<?php
	include '../model/Post.class.php';
	include '../dao/PostDAO.class.php';

	extract($_POST);

	//GLOBAL
	$postDAO = new postDAO();	


	switch ($action) 
	{
		case 'insert':
			$post = new Post(null,$Categorie_ID,$Title,$Resume,$Contenu,$DateDebut,$DateFin);		
			echo $postDAO->insert($post);//Si ok return 1
		    break;

		case 'update':
			$post = new Post($Post_ID,$Categorie_ID,$Title,$Resume,$Contenu,$DateDebut,$DateFin);
			echo $postDAO->update($post);//Si ok return 1
			break;

		case 'delete':
				echo $postDAO->delete($Post_ID);//Si ok return 1
			break;

		case 'getPost':
			echo $postDAO->getPost($txtInput);//Si ok return 1
			break;
			
		default:
			echo "Aucun action trouvée";
			break;
	}

?>

