package technicienCegep;

import java.util.Iterator;

public class Technicien implements IObservateur{

	String nome = "";
	
	
	public Technicien(String nome)
	{
		this.nome= nome;
	}
	
	
	@Override
	public void UpDateMe(Object o) 
	{			
		if(o instanceof Imprimante)
		{
			//Imprimante o = (Imprimante)o;
/*				System.out.println("Dear costomor "+ this.()
						                           +" le produit "+p.()
						                           +" is now on sold: "+p.());	*/	
		}
		
	}
}
