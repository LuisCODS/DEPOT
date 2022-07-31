<?php
include'../includes/Connection.class.php';

	Class PostDAO 
	{

		private $cn;

		function __Construct()
		{
			$pdo = new Connection();
			$this->cn = $pdo->getConnection();
		}

	// ______________________________ CDRUD ___________________________
		function insert(Post $post)
		{
			try {

			$sql = 'insert into post 
					(Categorie_ID,
					Title,
					Resume,
					Contenu,
					DateDebut,
					DateFin)
					values(?,?,?,?,?,?)';

					$stmt = $this->cn->prepare($sql);

					$stmt->bindValue(1, $post->getCategorieID() );
					$stmt->bindValue(2, $post->getTitle() );
					$stmt->bindValue(3, $post->getResume() );
					$stmt->bindValue(4, $post->getContenu() );
					$stmt->bindValue(5, $post->getDateDebut() );
					$stmt->bindValue(6, $post->getDateFin()  );
					
					return $stmt->execute();
					
			} catch (PDOException $e) {
				echo 'Erro: '. $e;
			}
		}

		function upDate(Post $post)
		{
			try {
					$sql =  'update post set
						     Categorie_ID = ?,
						     Title = ?, 
						     Resume = ?,
						     Contenu = ?,
						     DateDebut = ?,
						     DateFin = ?						     
						     where Post_ID = ? ';

					$stmt = $this->cn->prepare($sql);

					$stmt->bindValue(1, $post->getCategorieID() );
					$stmt->bindValue(2, $post->getTitle() );
					$stmt->bindValue(3, $post->getResume() );
					$stmt->bindValue(4, $post->getContenu() );
					$stmt->bindValue(5, $post->getDateDebut() );
					$stmt->bindValue(6, $post->getDateFin()  );
					$stmt->bindValue(7, $post->getPostID() );

					return $stmt->execute();

			} catch (PDOException $e) {
				echo "Erro: ". $e;
			}
		}

		function delete($Post_ID)
		{
			try {
					$sql = 'delete from post where Post_ID = ? ';
					$stmt = $this->cn->prepare($sql);
					$stmt->bindValue(1, $Post_ID);
					return $stmt->execute();					
			} catch (PDOException $e) {
				echo "Erro: ". $e;
			}
		}

		function getPost($txtInput)
		{
			$sql = "select
					 Post_ID, 
					 Categorie_ID, 
					 Title, 
					 Resume,
					 Contenu, 
					 date_format(DateDebut, '%d/%m/%Y') asDateDebut,
					 date_format(DateFin, '%d/%m/%Y') DateFin					  
					 from post WHERE Title like '%$txtInput%'  
				     ORDER BY Title ASC";
					 
			$stmt = $this->cn->prepare($sql);
			$stmt->execute();
			$rs = $stmt->fetchall(PDO::FETCH_ASSOC); 
			 return json_encode($rs);
		}
	}

?>