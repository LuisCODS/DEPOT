package vihicule;

public class FactoryVoitureEelectric extends factoryvoiture {
	
	// Vehicule vehicule;
/*	public void  Commandervehicule()
	{		
		vehicule=factorymethod();	
		System.out.println("vous avez commande un veheicule de type"+ vehicule.getClass().getName());
	}*/

	protected Vehicule factorymethod()
	{
		System.out.println("voiture electric");		
		return new VoitureElectric();
	}

}
