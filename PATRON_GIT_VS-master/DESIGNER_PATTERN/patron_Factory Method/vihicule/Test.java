package vihicule;

public class Test {
	
	public static void main(String[] args) {
	
	FactoryVehicule f1=new FactoryScooterElectric();
	FactoryVehicule f2=new FactoryScooterEssence();
	FactoryVehicule f3=new FactoryVoitureEssence();
	FactoryVehicule f4=new FactoryVoitureEelectric();
	
	/*
	 Par héritage tous les instances (f) suivantes ont acces à la méthode
	 Commandervehicule() et aussi à la composition Vehicule fournie par la 
	 classe FactoryVehicule. Ainsi, il suffit que  les  fabriques specifiques
	  implementnts son factorymethod() returnant l'instance de son type.
	  
	*/
	f1.Commandervehicule();
	f2.Commandervehicule();
	f3.Commandervehicule();
	f4.Commandervehicule();
	
	}
	

}
