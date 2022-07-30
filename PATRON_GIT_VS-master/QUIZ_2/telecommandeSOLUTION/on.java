package telecommandeSOLUTION;

public class on extends State{

	
	public void allumer(Device device) {
		System.out.println("je suis deja en mode on");
		}
public void eteindre(Device device) {
	device.setState(new off());
		
	}

}
