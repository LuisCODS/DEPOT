<?php

	Class Film 
	{

		private $PK_ID_Film;
		private $titre;
		private $prix;
		private $realisateur;
		private $categorie;
		private $pochette;
        private $description;
        private $url;

		function __Construct($PK_ID_Film,$titre,$prix,$realisateur,$categorie,$pochette,$description,$url)
		{
			$this->PK_ID_Film 		= $PK_ID_Film;
            $this->titre 	        = $titre;
			$this->prix	            = $prix;
			$this->realisateur      = $realisateur;
			$this->categorie	    = $categorie;
            $this->pochette         = $pochette;
            $this->description      = $description;
            $this->url      		= $url;
		}

		

		function getFilmID(){
			return $this->PK_ID_Film;
		}
		function setFilmID($PK_ID_Film){
			 $this->PK_ID_Film = $PK_ID_Film;
		}
		function getTitre(){
			return $this->titre;
		}
		function setTitre($titre){
			 $this->titre = $titre;
		}
		function getPrix(){
			return $this->prix;
		}
		function setPrix($prix){
			 $this->prix = $prix;
		}		
		function getRealisateur(){
			return $this->realisateur;
		}
		function setRealisateur($realisateur){
			 $this->realisateur = $realisateur;
		}
		function getCategorie(){
			return $this->categorie;
		}
		function setCategorie($categorie){
			 $this->categorie = $categorie;
		}
		function getPochette(){
			return $this->pochette;
		}
		function setPochette($pochette){
			$this->pochette = $pochette;
		}
		function getDescription(){
			return $this->description;
		}
		function setDescription($description){
			 $this->description = $description;
		}
		function getUrl(){
			return $this->url;
		}
		function setUrl($url){
			 $this->url = $url;
		}


	}//fin class

