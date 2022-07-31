<?php
include '../model/Profil.class.php';
include '../dao/ProfilDAO.class.php';

// $profil = new Profil(33,'Admin');
$profilDAO = new ProfilDAO();
// $profilDAO->insert($profil);
 // $profilDAO->upDate($profil);
//  $profilDAO->delete(34);



// TEST SELECT
$rs = $profilDAO->getProfil();
try {	
	foreach ($rs as $row) {
	echo $row["ProfilNom"] . "<br>";
}
} catch (Exception $e) {
	echo "string ". $e;
}


?>