package question1;

public class FactoryOracle extends FactorySGBD{

	/*
    VISIBLE PAR HÉRITAGE...
	  SGBD sgbd;
	  etablirConnection()
	 */
	
	
	/*
	 Cette méthode est appelée par etablirConnection(héritage).
	 Elle implemente une reference de type "this" dans la composition sgbd
	*/
	@Override
	protected SGBD factorymethod() {
		System.out.println("Connection  Oracle");
		return new Oracle();
	}

}
