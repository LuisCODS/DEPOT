package pkParking;

public class Test {

	public static void main(String[] args) {

				
		IObservateur porte = new Porte();
		IObservateur enseigne = new Enseigne();
		IObservateur agent = new AgentDeSecurite();
		
		Parking parking = new Parking();
		parking.Subscribe(porte);
		parking.Subscribe(enseigne);
		parking.Subscribe(agent);
		
		System.out.println("TEST HORS LIMITE" +"\n");
		parking.setNbVoitureIN(101);
		System.out.println("\n");
		
		System.out.println("TEST DANS LA LIMITE" +"\n");
		parking.setNbVoitureIN(100);

	}

}
