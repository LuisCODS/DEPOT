package exercice1;

public class RecrutementEmploye {
	

		Boolean admission(Personne personne)
		{
		if (personne.hasBaccalaureat()) 
			return true;
		else 
			return false;
		}

}
