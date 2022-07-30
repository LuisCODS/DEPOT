package gestionDeSalaire;

public class ManutentionARisque extends Manutention implements ProduitsDangereux{

	
	// ============= CONSTRUCTEUR =============
	public ManutentionARisque(int nbHeuresTravaillees, int age, String nom, String preNom, String dateEntree) 			
	{
		super(nbHeuresTravaillees, age, nom, preNom, dateEntree);		
	}
	

	// ============= M�THODES =============	
	@Override
	public String getNom()
	{
		return "Manutention � risque : " 
				+ super.getNom().replaceAll("Manutention : ", "") ;
	}

	/**
	 * @Description: Leur salaire vaut leur nombre d'heures de travail mensuel multipli�es par 100$. 
	 * @return: le salaire d'un employ�.
	 */
	@Override
	public float calculerSalaire()
	{
		return super.calculerSalaire() + primeDeRisque;
	}
	
}//fin classe
