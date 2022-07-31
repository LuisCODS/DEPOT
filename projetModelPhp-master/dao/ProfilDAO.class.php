

<?php
	include'../includes/Connection.class.php';

	Class ProfilDAO 
	{

		private $cn;

		function __Construct()
		{
			$pdo = new Connection();
			$this->cn = $pdo->getConnection();
		}

	// ______________________________ CDRUD ___________________________

		//Add a new Profil member
		function insert(Profil $profil)
		{
			try {
					$profilNom = $profil->getProfilNom();
					$sql = 'insert into profil(ProfilNom) values(?)';
					$stmt = $this->cn->prepare($sql);
					$stmt->bindValue(1, $profilNom);
					return $stmt->execute();//Return true/False	
			} catch (PDOException $e) {
				echo 'Erreur insertion: '. $e;
			}
		}

		// Update a current Profile member
		function update(Profil $p)
		{
			try {
					$profilNom = $p->getProfilNom();
					$ProfilID = $p->getProfilID();

					$sql = 'update profil set ProfilNom = ? where Profil_ID = ? ';
					
					$stmt = $this->cn->prepare($sql);
					$stmt->bindValue(1, $profilNom );
					$stmt->bindValue(2, $ProfilID);					
					return $stmt->execute();//Return true/False						
				} catch (PDOException $e) {
					echo "Erro: ". $e;
				}
		}

		//Delete a current Profile member
		function delete($Profil_ID)
		{
			try {
					$sql = 'delete from profil where Profil_ID = ? ';
					$stmt = $this->cn->prepare($sql);
					$stmt->bindValue(1, $Profil_ID);					
					return $stmt->execute();//Return true/False					
			} catch (PDOException $e) {
				echo "Erro: ". $e;
			}
		}

		// Method that returns a list of Profile in crescent order.
		//Retour: tableau en format json.
		function getProfil($txtInput)
		{
			$sql = "select Profil_ID, ProfilNom from profil WHERE ProfilNom like '%$txtInput%'  ORDER BY ProfilNom ASC ";
			$stmt = $this->cn->prepare($sql);
			$stmt->execute();//Return 1 si ok	
			// Contient un array de Profil
			$tableau = $stmt->fetchall(PDO::FETCH_ASSOC); 
			//Retourn un array en json,car HTML(browser) only ready string.
			 return json_encode($tableau);
		}
	}

?>