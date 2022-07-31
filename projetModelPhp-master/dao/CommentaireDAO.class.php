<?php
include'../includes/Connection.class.php';

	Class CommentaireDAO 
	{

		private $cn;

		function __Construct()
		{
			$pdo = new Connection();
			$this->cn = $pdo->getConnection();
		}

	// ______________________________ CDRUD ___________________________
		function insert(Commentaire $com)
		{
			try {
					$Post_ID 	 = $com->getPostID();
					$Commentaire = $com->getCommentaire();
					$DateDebut 	 = $com->getDateDebut();

					$sql = 'insert into commentaire (Post_ID,Commentaire,DateDebut) values(?,?,?)';

					$stmt = $this->cn->prepare($sql);

					$stmt->bindParam(1, $Post_ID);
					$stmt->bindParam(2, $Commentaire );
					$stmt->bindParam(3, $DateDebut );

					return $stmt->execute();

			} catch (PDOException $e) {
				echo 'Erro: '. $e;
			}
		}

		function upDate(Commentaire $com)
		{
			try {

					$Post_ID 	 	= $com->getPostID();
					$Commentaire 	= $com->getCommentaire();
					$DateDebut 	 	= $com->getDateDebut();
					$Commentaire_ID = $com->getCommentaireID();

					$sql = 'update commentaire set
						    Post_ID = ?,
						    Commentaire = ?,
						    DateDebut = ?
						    where Commentaire_ID = ? ';

					$stmt = $this->cn->prepare($sql);

					$stmt->bindParam(1, $Post_ID);
					$stmt->bindParam(2, $Commentaire);
					$stmt->bindParam(3, $DateDebut);
					$stmt->bindParam(4, $Commentaire_ID);

					return $stmt->execute();

			} catch (PDOException $e) {
				echo "Erro: ". $eCommentaire;
			}
		}

		function delete($Commentaire_ID)
		{
			try {
					$sql = 'delete from commentaire where Commentaire_ID = ? ';
					$stmt = $this->cn->prepare($sql);
					$stmt->bindParam(1, $Commentaire_ID);
					return $stmt->execute();					
			} catch (PDOException $e) {
				echo "Erro: ". $e;
			}
		}

		function getCommentaire()
		{
			$sql = 'select Commentaire_ID, Post_ID, Commentaire,DateDebut  from commentaire';
			$stmt = $this->cn->prepare($sql);
			$stmt->execute();
			//pega o resutado da consulta
			$rs = $stmt->fetchall(PDO::FETCH_ASSOC); 
 			//Retourn un array en json,car HTML(browser) only ready string.
			 return json_encode($rs);
		}
	}

?>