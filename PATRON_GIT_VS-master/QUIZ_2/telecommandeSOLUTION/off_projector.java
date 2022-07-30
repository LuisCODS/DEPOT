package telecommandeSOLUTION;

public class off_projector extends TelecommandeStrategy {
	public void handle(Device device){
		device.turnoff();
		System.out.println("eteindre le projecteur");
	}

}
