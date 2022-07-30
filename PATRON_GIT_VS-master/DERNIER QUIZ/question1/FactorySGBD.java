package question1;

public abstract class FactorySGBD {

	protected SGBD sgbd;
	abstract protected SGBD factorymethod();
	
	//Toutes les classes filles ont cette m�thode
	public void  etablirConnection()
	{		
		sgbd = factorymethod();	
		System.out.println("vous avez �tablit la connection de type "+ sgbd.getClass().getName());
	}
}