package ObserverProduit;

public class ObservateurPrix extends Client implements IObservateur{

	public ObservateurPrix(String name, String email)
	{
		super(email, email);
		this.email = email;
		this.name = name;
	}

	public void UpDateMe(Object o)
	{
		if(o instanceof Produit)
		{
			Produit p=(Produit)o;
				System.out.println("Dear costomor "+ this.getName()
						                           +" le produit "+p.getNome()
						                           +" is now on sold: "+p.getPrix());		
		}
	}
}
