package vihicule;

public class FactoryScooterEssence extends Factoryscooter{
	
	FactoryScooterEssence(){}
	protected Vehicule factorymethod() {
		
		System.out.println("scooter essence");
		return new ScooterEssence();
	
}

}
