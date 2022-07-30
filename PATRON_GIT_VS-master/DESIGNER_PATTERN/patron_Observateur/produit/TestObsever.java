package ObserverProduit;


public class TestObsever {

	public static void main(String[] args) {	
		
		Produit p1=new Produit("Iphone8", 1000);
		
		IObservateur clientPourLePrix = new ObservateurPrix("jean","emailjean");
		IObservateur clientPourLaDispo = new ObservateurDisponibilite("michel","emailmichel");
		
		p1.Subscribe(clientPourLePrix);
		p1.Subscribe(clientPourLaDispo);
		//p1.setAvailable(false);
		p1.getAvailable(true);
		p1.setPrix(700);
		//p1.setPrix(1000);
	
	}
}
