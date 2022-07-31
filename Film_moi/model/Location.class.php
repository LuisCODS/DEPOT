<?php

	Class Location 
	{

		private $PK_ID_Location;
		private $dateDebut;
		private $dateFin;
		private $PK_ID_Membre;


		function __Construct($PK_ID_Location,$dateDebut,$dateFin,$PK_ID_Membre)
		{
			$this->PK_ID_Location = $PK_ID_Location;
            $this->dateDebut 	  = $dateDebut;
			$this->dateFin	      = $dateFin;
			$this->PK_ID_Membre   = $PK_ID_Membre;
		}

		
		function getLocationID(){
			return $this->PK_ID_Location;
		}
		function setLocationID($PK_ID_Location){
			 $this->PK_ID_Location = $PK_ID_Location;
		}
		function getPK_ID_MembreID(){
			return $this->PK_ID_Membre;
		}
		function setPK_ID_MembreID($PK_ID_Membre){
			 $this->PK_ID_Membre = $PK_ID_Membre;
		}
		function getDateFin(){
			return $this->dateFin;
		}
		function setDateFin($dateFin){
			 $this->dateFin = $dateFin;
		}
		function getDateDebut(){
			return $this->dateDebut;
		}
		function setDateDebut($dateDebut){
			 $this->dateDebut = $dateDebut;
		}



	}//fin class

