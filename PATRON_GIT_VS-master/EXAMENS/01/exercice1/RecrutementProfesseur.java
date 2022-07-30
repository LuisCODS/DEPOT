package exercice1;

public class RecrutementProfesseur extends RecrutementEmploye {

	Boolean admission(Personne personne)
	{
		super.admission(personne);
		if (personne.hasDoctorat()) 
			return true;
		else
			return false;
	}
}




