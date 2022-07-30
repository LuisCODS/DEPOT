package gestionDeSalaire;
public class Representants extends Employe {

	
	// ============= ATTRIBUTS =============	
	private float chiffreDaffaire = 0f;
	private final int PRIME = 1400; 
	private final float TAUX = 0.2f;
	
	// ============= CONSTRUCTEUR =============	
	public Representants(float chiffreDaffaire, int age, String nom, String preNom, String dateEntree)
	{
		super(age, nom, preNom, dateEntree);
		this.chiffreDaffaire = chiffreDaffaire;
	}
	
	
	// ============= MÉTHODES =============	
	@Override
	public String getNom()
	{
		return "Representants : " + super.getNom();
	}

	/**
	 * @Description: Leur salaire mensuel est de le 20 % du chiffre 
	 * 				 d'affaire qu'ils réalisent mensuellement, plus 1400 $.
	 * @return: le salaire d'un employé.  
	 */
	@Override
	public float calculerSalaire()
	{
		return this.chiffreDaffaire * this.TAUX + this.PRIME;
	}
	
}//fin classe
