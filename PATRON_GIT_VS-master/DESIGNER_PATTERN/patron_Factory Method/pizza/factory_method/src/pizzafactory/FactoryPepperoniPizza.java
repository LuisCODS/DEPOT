package pizzafactory;

public class FactoryPepperoniPizza extends FactoryPizza{

	
	protected Pizza FactoryMethod() {
		return new PepperoniPizza();
	}

}
