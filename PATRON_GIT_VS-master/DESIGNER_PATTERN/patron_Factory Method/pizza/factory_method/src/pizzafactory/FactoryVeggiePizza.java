package pizzafactory;

public class FactoryVeggiePizza extends FactoryPizza {
	protected Pizza FactoryMethod() {
		return new VeggiePizza();
	}

}
