package telecommandeSOLUTION;

public class off_tv extends TelecommandeStrategy {
	
	public void handle(Device device){
		device.turnoff();
		System.out.println("eteindre le tv");
	}

}
