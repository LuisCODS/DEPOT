package pizzafactory;

abstract public class FactoryPizza {
	Pizza pizza;
	protected abstract Pizza FactoryMethod();
	
	public void commanderPizza()
	{
		pizza=FactoryMethod();
		System.out.println("vous avez commande pizza de type "+pizza.getClass().getName());
	}
	

}
