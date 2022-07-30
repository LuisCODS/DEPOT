package vihicule;

public class FactoryVoitureEssence extends factoryvoiture {

	FactoryVoitureEssence(){}
	protected Vehicule factorymethod() {
		System.out.println("voiture essence");
		return new VoitureEssence();
	}

}
