package CalculPaie;

//import java.util.Date;

public   class Emplyee {
	String nom;
	String prenom;
	String dateEmbauche;
	String dateNaissance;
	Salary salaire;
	int heureTravaillee;
	int heuresSupplementaires;
	double noteDeFrais;
	int heuresAbscences;
		public Emplyee(String nom, String prenom, String dateEmbauche, String dateNaissance, Salary salaire,
			int heureTravaillee, int heuresSupplementaires, double noteDeFrais, int heuresAbscences) {
		
		this.nom = nom;
		this.prenom = prenom;
		this.dateEmbauche = dateEmbauche;
		this.dateNaissance = dateNaissance;
		this.salaire = salaire;
		this.heureTravaillee = heureTravaillee;
		this.heuresSupplementaires = heuresSupplementaires;
		this.noteDeFrais = noteDeFrais;
		this.heuresAbscences = heuresAbscences;
	}



	public  void getSalaire() {
		 System.out.print(salaire.calculSalaireEmployee(this));
	}



	public void setSalaire(Salary salaire) {
		this.salaire = salaire;
	}



	public int getHeuresSupplementaires() {
		return heuresSupplementaires;
	}



	public void setHeuresSupplementaires(int heuresSupplementaires) {
		this.heuresSupplementaires = heuresSupplementaires;
	}



	public double getNoteDeFrais() {
		return noteDeFrais;
	}



	public void setNoteDeFrais(double noteDeFrais) {
		this.noteDeFrais = noteDeFrais;
	}



	public int getHeuresAbscences() {
		return heuresAbscences;
	}



	public void setHeuresAbscences(int heuresAbscences) {
		this.heuresAbscences = heuresAbscences;
	}



	public int getHeureTravaillee() {
		return heureTravaillee;
	}



	public void setHeureTravaillee(int heureTravaillee) {
		this.heureTravaillee = heureTravaillee;
	}

	
	

}
