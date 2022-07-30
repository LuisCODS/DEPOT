package compteBancaire;

public class caisseDepot extends CompteBancaire{
	private static caisseDepot instance;
	
  private caisseDepot(int numero) {
		super(numero);
		
	}
  public static caisseDepot getinstance()
  {
	  if(instance==null)
		  instance= new caisseDepot(000000);
	  
	  return instance;
	  }

  
  

}
