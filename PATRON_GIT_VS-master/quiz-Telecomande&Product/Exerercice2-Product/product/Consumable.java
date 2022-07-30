package product;

public class Consumable extends Type implements Iexpiration {

	//CHAMP
	private String expirationDate = "";	
	//private final static String CAT = "Produit consumable";

	//CONTRUCTEUR
	public Consumable(String date)
	{
		this.expirationDate = date;
	}
    
	//MÉTHODES
	@Override
	public String GetExpiration() {
		return this.expirationDate;
	}


}