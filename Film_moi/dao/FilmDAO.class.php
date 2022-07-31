<!-- <?php

/* 
DAO (Data acce objet): classes responsables de la création du CRUD 
et de la persistance des données dans la base de données; 
*/

include '../includes/Connection.class.php';

Class FilmDAO 
{
	private $cn;

	function __Construct()	{
		$pdo = new Connection();
		$this->cn = $pdo->getConnection();
	}
 // ______________________________ CDRUD ___________________________

	function insert(Film $f)
	{		
		try {
			$sql = 'insert into Film 
					(titre,
					prix,
					realisateur,
					categorie,
					pochette,
					description)
					values(?,?,?,?,?,?)';

				$stmt = $this->cn->prepare($sql);

				$stmt->bindValue(1, $f->getTitre() );
				$stmt->bindValue(2, $f->getPrix() );
				$stmt->bindValue(3, $f->getRealisateur() );
				$stmt->bindValue(4, $f->getCategorie() );
				$stmt->bindValue(5, $f->getPochette() );
				$stmt->bindValue(6, $f->getDescription() );
				$stmt->execute();
				unset($stmt);//libere la memoire

		} catch (PDOException $e) {
			echo 'Erro: '. $e;
		}
	}


	function update(Film $f)
	{
		try {
				$sql =  'update Film set
                        titre = ?,
						prix = ?,
						realisateur = ?,
						categorie = ?,
						pochette = ?,	
						description = ?,
						url = ?								
						where PK_ID_Film = ?';

				$stmt = $this->cn->prepare($sql);

				
				$stmt->bindValue(1, $f->getTitre() );
				$stmt->bindValue(2, $f->getPrix() );
				$stmt->bindValue(3, $f->getRealisateur() );
				$stmt->bindValue(4, $f->getCategorie() );
				$stmt->bindValue(5, $f->getPochette() );
				$stmt->bindValue(6, $f->getDescription() );
				$stmt->bindValue(7, $f->getFilmID() );
				$stmt->bindValue(8, $f->getUrl() );
				$stmt->execute();
				unset($stmt);//libere la memoire

		} catch (PDOException $e) {
			echo "Erro: ". $e;
		}
	}

	function delete($PK_ID_Film)
	{
		try {
				$sql = 'delete from Film where PK_ID_Film = ? ';
				$stmt = $this->cn->prepare($sql);
				$stmt->bindValue(1, $PK_ID_Film);					
				//return $stmt->execute();// true/False
				$stmt->execute();// true/False
				unset($stmt);
								
		} catch (PDOException $e) {
			echo "Erro: ". $e;
		}
	}

	function list()
	{
        $sql = 'select PK_ID_Film,titre,prix,realisateur,categorie,pochette,description from Film';
		$stmt = $this->cn->prepare($sql);
		$stmt->execute();
		$rs = $stmt->fetch(PDO::FETCH_OBJ); 
		return($rs);
	}

	// function getFilm($PK_ID_Film)
	// {
 //        $sql = 'select * from Film where PK_ID_Film = ? ';
	// 	$stmt = $this->cn->prepare($sql);
	// 	$stmt->bindValue(1, $PK_ID_Film);	
	// 	$stmt->execute();
	// 	$tableau = $stmt->fetchall(PDO::FETCH_ASSOC); 
	// 	return($tableau);
	// }

}

 