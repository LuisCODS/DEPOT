<?php

include "../includes/function.php";
	Class Post 
	{

		private $Post_ID;
		private $Categorie_ID;
		private $Title;
		private $Resume;
		private $Contenu;
		private $DateDebut;
		private $DateFin;

		function __Construct($Post_ID,$Categorie_ID,$Title,$Resume,$Contenu,$DateDebut,$DateFin)
		{
			$this->Post_ID 			= $Post_ID;
			$this->Categorie_ID  	= $Categorie_ID;
			$this->Title 	    	= $Title;
			$this->Resume 	   	    = $Resume;
			$this->Contenu 	 	  	= $Contenu;
			$this->DateDebut 	  	= $DateDebut;			
			$this->DateFin	  	    = $DateFin;
		}

		function getPostID(){
			return $this->Post_ID;
		}
		function getCategorieID(){
			return $this->Categorie_ID;
		}
		function setCategorieID($Categorie_ID){
			 $this->Categorie_ID = $Categorie_ID;
		}
		function getTitle(){
			return $this->Title;
		}
		function setTitle($Title){
			 $this->Title = $Title;
		}
		function getResume(){
			return $this->Resume;
		}
		function setResume($Resume){
			 $this->Resume = $Resume;
		}
		function getContenu(){
			return $this->Contenu;
		}
		function setContenu($Contenu){
			 $this->Contenu = $Contenu;
		}
		function getDateDebut(){
			return $this->DateDebut;
		}
		function setDateDebut($DateDebut){
			 $this->DateDebut = $DateDebut;
		}
		function getDateFin(){
			return  $this->DateFin;
		}
		function setDateFin($DateFin){
			 $this->DateFin = $DateFin;
		}
	}

?>