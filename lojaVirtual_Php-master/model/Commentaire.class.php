<?php

	Class 
	{

		private $Commentaire_ID;
		private $Post_ID;
		private $Commentaire;
		private $DateDebut;

		function __Construct($Commentaire_ID,$Post_ID,$Commentaire,$DateDebut)
		{
			$this->Commentaire_ID  = $Commentaire_ID;
			$this->Post_ID         = $Post_ID;
			$this->Commentaire	    = $Commentaire;
			$this->DateDebut  	    = $DateDebut;
		}

		function getCommentaireID(){
			return $this->Commentaire_ID;
		}
		// function setCommentaireID($Commentaire_ID){
		// 	 $this->Commentaire_ID = $Commentaire_ID;
		// }
		function getPostID(){
			return $this->Post_ID;
		}
		function setPostID($Post_ID){
			 $this->Post_ID = $Post_ID;
		}
		function getCommentaire(){
			return $this->Commentaire;
		}
		function setCommentaire($Commentaire){
			 $this->Commentaire = $Commentaire;
		}
		function getDateDebut(){
			return $this->DateDebut;
		}
		function setDateDebut($DateDebut){
			 $this->DateDebut = $DateDebut;
		}


	}

?>