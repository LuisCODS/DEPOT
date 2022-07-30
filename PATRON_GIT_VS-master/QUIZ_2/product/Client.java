package product;

public class Client {

	public static void main(String[] args) {

		//CRÉE LE TIPE
		NonConsumable nonConsumable = new NonConsumable();
		nonConsumable.setMarque("Diesel");
		nonConsumable.setPrice(750);
			
		//ASSOCIE LE TIPE AU PRODUIT
		Product pantalon = new Product(nonConsumable);	
		pantalon.Print(new Html());	
		System.out.println(pantalon.toString());	
		
		System.out.println(" ");	

		Consumable consumable = new Consumable("10.10.2025");
		nonConsumable.setMarque("Riz");
		nonConsumable.setPrice(3.89);
		
		Product riz = new Product(nonConsumable);	
		riz.Print(new Txt());	
		System.out.println(riz.toString());
	
	
	
	
	
	}//fin main
}//fin class
