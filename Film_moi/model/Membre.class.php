<?php

	Class Membre 
	{

		private $PK_ID_Membre;
		private $nom;
		private $prenom;
		private $profil;
		private $courriel;
		private $tel_membre;
		private $MDP_membre;

		function __Construct($PK_ID_Membre,$nom,$prenom,$profil,$courriel,$tel_membre,$MDP_membre)
		{
			$this->PK_ID_Membre = $PK_ID_Membre;
            $this->nom 	      = $nom;
			$this->prenom	  = $prenom;
			$this->profil     = $profil;
			$this->courriel	  = $courriel;
            $this->tel_membre = $tel_membre;
			$this->MDP_membre = $MDP_membre;
		}

		

		function getMembreID(){
			return $this->PK_ID_Membre;
		}
		function setMembreID($PK_ID_Membre){
			 $this->PK_ID_Membre = $PK_ID_Membre;
		}
		function getNom(){
			return $this->nom;
		}
		function setNom($nom){
			 $this->nom = $nom;
		}
		function getPrenom(){
			return $this->prenom;
		}
		function setPrenom($prenom){
			 $this->prenom = $prenom;
		}
		function getProfil(){
			return $this->profil;
		}
		function stProfil($profil){
			 $this->profil = $profil;
		}
		function getCourriel(){
			return $this->courriel;
		}
		function setCourriel($courriel){
			 $this->courriel = $courriel;
		}
		function getTelMembre(){
			return $this->tel_membre;
		}
		function setTelMembre($tel_membre){
			 $this->tel_membre = $tel_membre;
		}
		function getMdpMembre(){
			return $this->MDP_membre;
		}
		function setMdpMembre($MDP_membre){
			 $this->MDP_membre = $MDP_membre;
		}

		
	}//fin class

