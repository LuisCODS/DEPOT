package ObserverProduit;

public class ObservateurDisponibilite extends Client implements IObservateur{

	public ObservateurDisponibilite(String name, String email)
	{
		super(name, email);
		this.email = email;
		this.name = name;
	}

	@Override
	public void UpDateMe(Object o)
	{
		if(o instanceof Produit)
		{
			Produit p=(Produit)o;
			System.out.println("Dear costomor "+getName()
								+" le produit "+p.getNome()
								+" is now available ");
		}
		
	}
	
	
	
	
}
