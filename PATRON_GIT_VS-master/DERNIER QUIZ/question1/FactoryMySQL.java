package question1;
public class FactoryMySQL extends FactorySGBD{
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
		System.out.println("Connection  MySQL");
		return new MySQL();
	}
}


