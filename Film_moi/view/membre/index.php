<?php 
session_start();
include_once '../../includes/head.php'; 
include_once '../../includes/interfaceMembre.php'; 
include_once '../../model/Membre.class.php';


// =============  INDEX MEMBRE ======================

	// GESTION SESSION
	$membre = new Membre(null,null,null,null,null,null,null);

	if (isset ($_SESSION["membre"]) )
	 {
		$membre = unserialize($_SESSION["membre"]);		
	 }
	else {
		header("location: ../../controller/login.php");
		exit();
	 }
 ?>

<!-- SHOW SESSION -->
<div class="alert alert-success " role="alert">
  Session : <strong><?php  echo $membre->getCourriel();?></strong>
</div>


<div class="container">
      <div class="jumbotron">
          <h1 class="display-4">Bienvenue  <strong><?php  echo $membre->getNom();?></strong> </h1>
          <p class="lead"></p>
          <hr class="my-4">
          <p>Le menu en haut vous permet de gerer votre location.</p>
     </div>
</div>      


<!--  FOOTER  --> 
<?php require_once '../../includes/footer.php';?>

