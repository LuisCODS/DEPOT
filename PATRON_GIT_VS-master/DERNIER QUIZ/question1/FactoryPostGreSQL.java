package question1;

public class FactoryPostGreSQL extends FactorySGBD{

	/*
    VISIBLE PAR H�RITAGE...
	  SGBD sgbd;
	  etablirConnection()
	 */
	
	
	/*
	 Cette m�thode est appel�e par etablirConnection(h�ritage).
	 Elle implemente une reference de type "this" dans la composition sgbd
	*/
	@Override
	protected SGBD factorymethod() {
		System.out.println("Connection  PostGreSQL");
		return new PostGreSQL();
	}

}
