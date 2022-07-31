<?php 
	//session_start();
	include '../../includes/head.php'; 
	include '../../includes/interfaceAdmin.php'; 
    // include '../../dao/FilmDAO.class.php';
	require_once("../../includes/ConnectionPDO.php");


	// $filmDAO = new FilmDAO();

	//SI le bouton supprimer est pesé
	if (isset($_GET['delete'])) 
	{	
		//Recupere la valeur ID envoyé par GET dans l'url
		$id_Url = $_GET['delete'];
		$requette="SELECT * FROM film WHERE PK_ID_Film=?";
		$stmt = $connexion->prepare($requette);
		$stmt->execute(array($id_Url));
		
		if(!$ligne=$stmt->fetch(PDO::FETCH_OBJ)){
			echo "Film ".$id_Url." introuvable";
			unset($connexion);
			unset($stmt);
			exit;
		}

		$pochette=$ligne->pochette;
		
		if($pochette!="avatar.jpg"){
			$rmPoc='../../img/'.$pochette;
			$tabFichiers = glob('../../img/*');
			//print_r($tabFichiers);
			// parcourir les fichier
			foreach($tabFichiers as $fichier){
			  if(is_file($fichier) && $fichier==trim($rmPoc)) {
				// enlever le fichier
				unlink($fichier);
				break;
			  }
			}

		}
		// $filmDAO->delete($id_Url);//Si ok return 1

		$requette="DELETE FROM film WHERE PK_ID_Film=?";
		$stmt = $connexion->prepare($requette);
		$stmt->execute(array($id_Url));
		unset($connexion);
		unset($stmt);
		header("location:../admin/listerFilm.php");
	   

	}
?>

