package transport_Strategy;

public class TestTransport {

	public static void main(String[] args) {
		
		
		TransportGood byBycyclete =new TransportGood( new StrategyBycyclette());
		byBycyclete.deplacer();
		
		TransportGood byAvion =new TransportGood( new StrattegyAvion());
		byAvion.deplacer();
		
		//...

	}

}
