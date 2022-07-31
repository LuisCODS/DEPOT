<?php

require_once '../../includes/Connection.class.php';

Class Film_LocationDAO 
{
	private $cn;

	function __Construct()
	{
		$pdo = new Connection();
		$this->cn = $pdo->getConnection();
	}

	// ______________________________ CDRUD ___________________________

	function insert( $idFilm, $idMembre)
	{
		try {
				$sql = 'insert into Film_Location 
						fk_id_film, 
						fk_id_membre
						values(?,?)';

				$stmt = $this->cn->prepare($sql);
				$stmt->bindValue(1, $idFilm);
				$stmt->bindValue(2, $idMembre);
				$stmt->execute();
				unset($cn);//close  connexion
				unset($stmt);//libere la memoire					

		} catch (PDOException $e) {
			echo 'Erro: '. $e;
		}
	}


}

