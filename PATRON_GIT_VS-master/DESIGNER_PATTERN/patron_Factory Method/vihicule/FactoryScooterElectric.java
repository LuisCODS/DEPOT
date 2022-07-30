package vihicule;

public class FactoryScooterElectric extends Factoryscooter{
	FactoryScooterElectric(){}

	protected Vehicule factorymethod() {
		System.out.println("scooter electric");
		return new ScooterElectric();
	
}
	

}
