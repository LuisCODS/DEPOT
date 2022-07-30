package telecommandeSOLUTION;

public class on_tv extends TelecommandeStrategy {
	
	
	public void handle(Device device){
		device.turnon();
		System.out.println("allumer le tv");
	}

}
