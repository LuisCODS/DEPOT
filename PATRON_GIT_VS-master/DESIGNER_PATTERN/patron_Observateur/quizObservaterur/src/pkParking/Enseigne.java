package pkParking;

public class Enseigne implements IObservateur {

	@Override
	public void UpDateMe() {
		System.out.println("ENSEIGNE: << Il n'y a plus de place >>");
		
	}

}
