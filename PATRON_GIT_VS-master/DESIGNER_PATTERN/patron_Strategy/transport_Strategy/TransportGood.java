package transport_Strategy;

public class TransportGood {

	TransportStrattegy strategy;




	public TransportGood(TransportStrattegy strategy) {
		this.strategy = strategy;
	}



	public void deplacer()
	{
		strategy.voyager();
	}


}
