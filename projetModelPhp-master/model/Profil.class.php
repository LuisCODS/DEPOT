<?php

Class Profil
{

	private $Profil_ID;
	private $ProfilNom;

	function __Construct($Profil_ID, $ProfilNom)
	{
		$this->Profil_ID   = $Profil_ID;
		$this->ProfilNom   = $ProfilNom;
	}



	function getProfilID(){
		return $this->Profil_ID;
	}
	// function setProfilID($Profil_ID){
	// 	 $this->Profil_ID = $Profil_ID;
	// }
	function getProfilNom(){
		return $this->ProfilNom;
	}
	function setProfilNom($ProfilNom){
		 $this->ProfilNom = $ProfilNom;
	}

}
?>