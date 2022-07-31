<?php

	Class Categorie
	{

		private $Categorie_ID;
		private $NomCategorie;

		function __Construct($Categorie_ID,$NomCategorie)
		{
			$this->Categorie_ID = $Categorie_ID;
			$this->NomCategorie   = $NomCategorie;
		}

		function getCategorieID(){
			return $this->Categorie_ID;
		}
		// function setCategorieID($Categorie_ID){
		// 	 $this->Categorie_ID = $Categorie_ID;
		// }
		function getNomCategorie(){
			return $this->NomCategorie;
		}
		function setNomCategorie($NomCategorie){
			 $this->NomCategorie = $NomCategorie;
		}


	}

?>