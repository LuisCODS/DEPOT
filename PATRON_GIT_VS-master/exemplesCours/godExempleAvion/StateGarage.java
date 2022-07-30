package godExempleAvion;


public class StateGarage extends StateAvion{

	
	

	
	public void sortirDuGarage(Avion avion) {
		System.out.println("ok! je vais sortir du garage");
		StateAvion stateAvion=new StatePiste();
		avion.setStateAvion(stateAvion);
		
		}

	
	public void entrerAuGarage(Avion avion) {
		System.out.println("je suis deja au garage");
		
	}

	public void decoller(Avion avion) {
		System.out.println("je suis au garage je ne peux decoller!");

		
	}

	
	public void atterir(Avion avion) {
		System.out.println("je suis au garage je ne peux atterir!");
		
		}


}

