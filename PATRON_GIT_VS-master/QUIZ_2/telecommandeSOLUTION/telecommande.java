package telecommandeSOLUTION;

public class telecommande {

	
	public void telecommandeswitch(TelecommandeStrategy telecommande_stg , Device device)
	{
		//a estrategia liga a TV
		telecommande_stg.handle(device);
	
	}
}
