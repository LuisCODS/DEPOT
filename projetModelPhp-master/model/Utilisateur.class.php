<?php

	Class Utilisateur 
	{

		private $Utilisateur_ID;
		private $Profil_ID;
		private $UtilisateurName;
		private $UtilisateurNickName;
		private $UtilisateurMDP;
		private $UtilisateurEmail;

		function __Construct($Utilisateur_ID,$Profil_ID,$UtilisateurName,
							$UtilisateurNickName,$UtilisateurMDP,$UtilisateurEmail)
		{
			$this->Utilisateur_ID 	     = $Utilisateur_ID;
			$this->Profil_ID 	     	 = $Profil_ID;
			$this->UtilisateurName	     = $UtilisateurName;
			$this->UtilisateurNickName   = $UtilisateurNickName;
			$this->UtilisateurMDP		 = $UtilisateurMDP;
			$this->UtilisateurEmail	     = $UtilisateurEmail;
		}

		

		function getUtilisateurID(){
			return $this->Utilisateur_ID;
		}
		// function setUtilisateurID($Utilisateur_ID){
		// 	 $this->Utilisateur_ID = $Utilisateur_ID;
		// }
		function getProfilID(){
			return $this->Profil_ID;
		}
		function stProfilID($Profil_ID){
			 $this->Profil_ID = $Profil_ID;
		}
		function getUtilisateurName(){
			return $this->UtilisateurName;
		}
		function setUtilisateurName($UtilisateurName){
			 $this->UtilisateurName = $UtilisateurName;
		}
		function getUtilisateurNickName(){
			return $this->UtilisateurNickName;
		}
		function setUtilisateurNickName($UtilisateurNickName){
			 $this->UtilisateurNickName = $UtilisateurNickName;
		}
		function getUtilisateurMDP(){
			return $this->UtilisateurMDP;
		}
		function setUtilisateurMDP($UtilisateurMDP){
			 $this->UtilisateurMDP = $UtilisateurMDP;
		}
		function getUtilisateurEmail(){
			return $this->UtilisateurEmail;
		}
		function setUtilisateurEmail($UtilisateurEmail){
			 $this->UtilisateurEmail = $UtilisateurEmail;
		}

	}

?>