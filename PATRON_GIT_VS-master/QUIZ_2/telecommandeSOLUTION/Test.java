package telecommandeSOLUTION;

public class Test {

	public static void main(String[] args) {
		
		
		//cria o controle
		telecommande tel= new telecommande();
		//cria  a TV desligada
		Device device = new TV(new off());
		//liga a TV
		TelecommandeStrategy telecommandeStrategy = new on_tv();
		
		tel.telecommandeswitch(telecommandeStrategy , device);
		
		


	}

}
