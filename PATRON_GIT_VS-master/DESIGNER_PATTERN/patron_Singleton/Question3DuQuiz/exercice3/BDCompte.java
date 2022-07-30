package exercice3;
import java.util.ArrayList;

//Classe singleton
public class BDCompte {


	public static BDCompte INSTANCE = null;
	ArrayList<CompteBancaire> comptes = new ArrayList<CompteBancaire>();
	
	private BDCompte(){  }
	
	
	
	public void Add(CompteBancaire c)
	{
		comptes.add(c);
	}
	
	public void Remove(CompteBancaire c) 
	{
		comptes.remove(c);
	}
	
	public void Edit(CompteBancaire c) 
	{
		this.Add(c);
	}
	
	public static BDCompte getInstance() 
	{
		if ( INSTANCE == null) 		
			INSTANCE = new BDCompte();		
		return INSTANCE;
	}


	public ArrayList<CompteBancaire> getComptes() {
		return comptes;
	}		
	
	

}
