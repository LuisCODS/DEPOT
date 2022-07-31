<?php
include'../includes/Connection.class.php';

	Class UtilisateurDAO 
	{

		private $cn;

		function __Construct()
		{
			$pdo = new Connection();
			$this->cn = $pdo->getConnection();
		}

	// ______________________________ CDRUD ___________________________
		function insert(Utilisateur $user)
		{
			try {
					$sql = 'insert into utilisateur 
							(Profil_ID,
							 UtilisateurName,
							 UtilisateurNickName,
							 UtilisateurMDP,
							 UtilisateurEmail)
							 values(?,?,?,?,?)';

					$stmt = $this->cn->prepare($sql);

					$stmt->bindValue(1, $user->getProfilID() );
					$stmt->bindValue(2, $user->getUtilisateurName() );
					$stmt->bindValue(3, $user->getUtilisateurNickName() );
					$stmt->bindValue(4, $user->getUtilisateurMDP() );
					$stmt->bindValue(5, $user->getUtilisateurEmail() );

					return $stmt->execute();

			} catch (PDOException $e) {
				echo 'Erro: '. $e;
			}
		}

		function upDate(Utilisateur $user)
		{
			try {
				   $sql =  'update utilisateur set
							Profil_ID = ?,
							UtilisateurName = ?,
							UtilisateurNickName = ?,
							UtilisateurMDP = ?,
							UtilisateurEmail = ?							
							where Utilisateur_ID = ?';

					$stmt = $this->cn->prepare($sql);

					$stmt->bindValue(1, $user->getProfilID() );
					$stmt->bindValue(2, $user->getUtilisateurName() );
					$stmt->bindValue(3, $user->getUtilisateurNickName());
					$stmt->bindValue(4, $user->getUtilisateurMDP());
					$stmt->bindValue(5, $user->getUtilisateurEmail());
					$stmt->bindValue(6, $user->getUtilisateurID() );

					return $stmt->execute();

			} catch (PDOException $e) {
				echo "Erro: ". $e;
			}
		}

		function delete($user_ID)
		{			
			try {
					$sql = 'delete from utilisateur 
							where Utilisateur_ID = ? ';
					$stmt = $this->cn->prepare($sql);
					$stmt->bindValue(1, $user_ID);
					return $stmt->execute();	

			} catch (PDOException $e) {
				echo "Erro: ". $e;
			}
		}

		function getUtilisateur($txtInput)
		{
			$sql = "select
					Utilisateur_ID, 
					Profil_ID,
					UtilisateurName, 
					UtilisateurNickName,
					UtilisateurMDP,	
					UtilisateurEmail			
				    from utilisateur
				    WHERE UtilisateurName like '%$txtInput%'  
				    ORDER BY UtilisateurName ASC  ";

			$stmt = $this->cn->prepare($sql);
			$stmt->execute();
			//pega o resutado da consulta
			$rs = $stmt->fetchall(PDO::FETCH_ASSOC);  			
			 return json_encode($rs);//Retourn un array en json
		}
		
	}

?>