package vihicule;

public abstract class  FactoryVehicule {
	
	protected Vehicule vehicule;
	abstract protected Vehicule factorymethod();
	
	//Toutes les classes filles ont cette m�thode
	public void  Commandervehicule()
	{		
		vehicule=factorymethod();	
		System.out.println("vous avez commande un veheicule de type"+ vehicule.getClass().getName());
	}

}
