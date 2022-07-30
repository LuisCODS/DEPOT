package pizzafactory;

public class FactorySausagePizza extends FactoryPizza {
	
	protected Pizza FactoryMethod() {
		return new SausagePizza();
	}

}
