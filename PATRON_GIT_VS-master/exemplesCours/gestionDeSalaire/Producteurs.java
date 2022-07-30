package gestionDeSalaire;

public class Producteurs extends Employe {

	// ============= ATTRIBUTS =============
	private int nbUniteProduiteMois = 0; 
	private final int PRIME = 5; 
		
	// ============= CONSTRUCTEUR =============	
	public Producteurs(int nombreUnite, int age, String nom, String preNom, String dateEntree) 
	{
		super(age, nom, preNom, dateEntree);
		this.nbUniteProduiteMois = nombreUnite;
	}

	// ============= M�THODES =============	
	@Override
	public String getNom()
	{
		return "Producteurs : " + this.preNom + " "+ this.nom;
	}

	/**
	 * @Description Leur salaire vaut le nombre d'unit�s produites mensuellement multipli�es par 5$. 
	 * @return: le salaire d'un employ�.
	 */
	@Override
	public float calculerSalaire()
	{
		return this.nbUniteProduiteMois * this.PRIME;
	}

}//fin class
