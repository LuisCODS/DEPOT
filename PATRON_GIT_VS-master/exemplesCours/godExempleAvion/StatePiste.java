package godExempleAvion;

public class StatePiste extends StateAvion{





	public void sortirDuGarage(Avion avion) {
		System.out.println("je suis deja au piste");


	}


	public void entrerAuGarage(Avion avion) {
		System.out.println("ok je vais rentrer au garage");
		StateAvion stateAvion=new StateGarage();
		avion.setStateAvion(stateAvion);


	}

	public void decoller(Avion avion) {
		System.out.println("ok je vais decoller");
		StateAvion stateAvion=new StateAir();
		avion.setStateAvion(stateAvion);
	}


	public void atterir(Avion avion) {
		System.out.println("je ne suis pas en air!je suis au piste");

	}




}



