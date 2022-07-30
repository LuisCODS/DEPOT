package exercice3;

public class Test {

	public static void main(String[] args) {
	
		
		CompteBancaire cp1 = new CompteBancaire();
		cp1.nomeClient = "Luis";
		cp1.nbCompte = 1;
		cp1.setSolde(100);
		
		CompteBancaire cp2 = new CompteBancaire();
		cp2.nomeClient = "Bob";
		cp2.nbCompte = 2;
		cp2.setSolde(200);
		
		CompteBancaire cp3 = new CompteBancaire();
		cp3.nomeClient = "Mike";
		cp3.nbCompte = 3;
		cp3.setSolde(300);
		
		// try first instance
		BDCompte bd = BDCompte.getInstance();			
		bd.Add(cp1);
		bd.Add(cp2);
		bd.Add(cp3);
		
		for (CompteBancaire c : bd.getComptes()) 
		{
			System.out.println(c.nbCompte);
		}	
		
		// try second instance
		BDCompte bd2 = BDCompte.getInstance();	
		
		for (CompteBancaire c : bd2.getComptes()) 
		{
			System.out.println(c.nomeClient);
		}	

	}

}
