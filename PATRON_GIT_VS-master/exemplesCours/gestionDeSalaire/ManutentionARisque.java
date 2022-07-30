package gestionDeSalaire;

public class ManutentionARisque extends Manutention implements ProduitsDangereux{

	
	// ============= CONSTRUCTEUR =============
	public ManutentionARisque(int nbHeuresTravaillees, int age, String nom, String preNom, String dateEntree) 			
	{
		super(nbHeuresTravaillees, age, nom, preNom, dateEntree);		
	}
	

	// ============= MÉTHODES =============	
	@Override
	public String getNom()
	{
		return "Manutention à risque : " 
				+ super.getNom().replaceAll("Manutention : ", "") ;
	}

	/**
	 * @Description: Leur salaire vaut leur nombre d'heures de travail mensuel multipliées par 100$. 
	 * @return: le salaire d'un employé.
	 */
	@Override
	public float calculerSalaire()
	{
		return super.calculerSalaire() + primeDeRisque;
	}
	
}//fin classe
