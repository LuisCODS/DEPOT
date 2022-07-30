package gestionDeSalaire;

public class Vendeurs extends Employe {
    
	
	// ============= ATTRIBUTS =============
	private float chiffreDaffaire = 0;
	private final int PRIME = 1000; 
	private final float TAUX = 0.2f;
	
	// ============= CONSTRUCTEUR =============		
	public Vendeurs(float chiffreDaffaire, int age, String nom, String preNom, String dateEntree) 
	{
		super(age, nom, preNom, dateEntree);
		this.chiffreDaffaire = chiffreDaffaire;
	}
	

	// ============= MÉTHODES =============	
	@Override
	public String getNom() 
	{
		return "Vendeurs : " + super.getNom();
	}

	/**
	 * @Description: Leur salaire mensuel est le 20 % du chiffre d'affaire
	 * 				 qu'ils réalisent mensuellement, plus 1000$.  
	 * @return: le salaire d'un employé.
	 */
	@Override
	public float calculerSalaire()
	{
		return this.chiffreDaffaire * this.TAUX + this.PRIME;
	}
	
}
