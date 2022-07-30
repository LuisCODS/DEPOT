package telecommandeSOLUTION;

public class on_projector extends TelecommandeStrategy {
	public void handle(Device device){
		device.turnon();
		System.out.println("allumer le projecteur");
	}

}
