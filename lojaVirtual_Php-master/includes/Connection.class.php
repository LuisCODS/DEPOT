<?php
	Class Connection
	{
		private $cn;

		function getConnection()
		{
			try	{
				  $this->cn = new PDO('mysql:host=localhost;dbname=projet_model_php','root','');
				  $this->cn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			} 
			catch(PDOException $e){
			    // echo $e->getMessage();
				echo 'Probleme de connexion au serveur de bd '.$e;
				//exit();
			}
			return $this->cn;
		}
		
	}//FIN CLASS




?>