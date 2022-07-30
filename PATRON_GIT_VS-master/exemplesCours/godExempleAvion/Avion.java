package godExempleAvion;


public class Avion {
	
	StateAvion currentstateAvion;


	public Avion()
	{
		System.out.println("Par defaut je suis au garage!");
		currentstateAvion = new StateGarage();
	}



	public StateAvion getStateAvion() {
		return currentstateAvion;
	}
	public void setStateAvion(StateAvion stateAvion) {
		this.currentstateAvion = stateAvion;
	}
	public void sortirDuGarage()
	{
		currentstateAvion.sortirDuGarage(this);
	}
	public void entrerAuGarage()
	{
		currentstateAvion.entrerAuGarage(this);
	}
	public void decoller()
	{
		currentstateAvion.decoller(this);
	}
	public void atterir()
	{
		currentstateAvion.atterir(this);
	}



}
