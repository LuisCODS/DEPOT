package godExempleAvion;

public class StateAir extends StateAvion {

	

	
	public void sortirDuGarage(Avion avion) {
		System.out.println("je  suis en air pas au garage");
		
		
	}

	
	public void entrerAuGarage(Avion avion) {
		System.out.println("je suis en aire je dois atterir");
		
	}

	public void decoller(Avion avion) {
		System.out.println("je suis deja en air");

		
	}

	
	public void atterir(Avion avion) {
		System.out.println("ok!je vais atterir");
		StateAvion stateAvion=new StatePiste();
		avion.setStateAvion(stateAvion);
		}
	

}
