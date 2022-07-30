package telecommandeSOLUTION;

public class off extends State{

	
	public void allumer(Device device) {
		System.out.println("ok je vais passer de off a on");
		device.setState(new on());
		
	}

	public void eteindre(Device device) {
		System.out.println("je suis deja en mode off");
		
		
		
	}

}
