package bedExempleAvion;

public class Avion {

	StateAvion stateAvion;
	
	
	public Avion(StateAvion stateAvion) 
	{
		this.stateAvion = stateAvion;
	}
	
	
	
	void sortirDuGarage(){
		System.out.println("Sortir du garage");
	}
	void entrerAuGarage(){
		System.out.println("Entrer du garage");
	}
	void decoller(){
		System.out.println("Decoller");
	}
	void atterir(){
		System.out.println("Atterir");
	}
	public void doAction()
	{
		switch(stateAvion)
		{
			case Augarage: sortirDuGarage();
				decoller();
			break;
			case AuPiste: decoller();
			break;
			case EnlAir:atterir();
			entrerAuGarage();
			break;
			default:
				break;
		}
	}
}
