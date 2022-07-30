package question1;

public abstract class FactorySGBD {

	protected SGBD sgbd;
	abstract protected SGBD factorymethod();
	
	//Toutes les classes filles ont cette méthode
	public void  etablirConnection()
	{		
		sgbd = factorymethod();	
		System.out.println("vous avez établit la connection de type "+ sgbd.getClass().getName());
	}
}