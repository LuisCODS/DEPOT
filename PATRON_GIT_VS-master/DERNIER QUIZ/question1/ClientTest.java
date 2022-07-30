package question1;

public class ClientTest {

	public static void main(String[] args)  {
		
		/*
		 Par h�ritage tous les instances (f) suivantes ont acces � la m�thode
		 etablirConnection() et aussi � la composition SGBD fournie par la 
		 classe FactorySGBD. Ainsi, il suffit que  les  fabriques specifiques
		  implementnts son factorymethod() returnant l'instance de son type.		  
		*/
		FactorySGBD f1 = new FactoryMySQL();
		FactorySGBD f2 = new FactoryOracle();
		FactorySGBD f3 = new FactoryPostGreSQL();
		
		f1.etablirConnection();
		System.out.println(" ");
		
		f2.etablirConnection();
		System.out.println(" ");
		
		f3.etablirConnection();
		System.out.println(" ");
	}

}
