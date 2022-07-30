/**
 * 
 */

/**
 * @author Luis SANTOS e1103907
 * @DateDeCreation: 20/12/2017
 *@Description: Classe qui permettra de calculer le prix total d'un article incluant la TPS et la TVQ. 
 */
public abstract class Article {

	
	int id = 0;
	String nom ="";
	float prixHorsTaxe = 0;
	String marque = "";
	
	public Article()
	{
		
	}
		
	
	
	public abstract float calculerTaxe();
	
	public abstract float calculerPrixTotalAPayer();

	
	

	
	
	
	
}
