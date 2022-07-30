package pizzafactory;

public class FactoryCheesePizza extends FactoryPizza {
	
	
	protected Pizza FactoryMethod() {
		return new CheesePizza();
	}
	

}
