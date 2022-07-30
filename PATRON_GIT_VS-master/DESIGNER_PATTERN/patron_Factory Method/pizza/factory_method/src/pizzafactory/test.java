package pizzafactory;

import vihicule.FactoryVehicule;
import vihicule.factoryscooterelectric;
import vihicule.factoryscooteressence;
import vihicule.factoryvoitureelectric;
import vihicule.factoryvoitureessence;

public class test {

	public static void main(String[] args) {
		FactoryPizza f1=new FactoryCheesePizza();
		FactoryPizza f2=new FactorySausagePizza();
		FactoryPizza f3=new FactoryVeggiePizza();
		FactoryPizza f4=new FactoryPepperoniPizza();
		
		f1.commanderPizza();
		f2.commanderPizza();
		f3.commanderPizza();
		f4.commanderPizza();

	}

}
