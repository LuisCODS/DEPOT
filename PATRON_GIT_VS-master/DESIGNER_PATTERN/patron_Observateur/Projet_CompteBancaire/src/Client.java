package Projet_CompteBancaire.src;


public class Client implements IObservateur {

	CompteBancaire compte = null;
	String nom = "";

	public Client(CompteBancaire newCompte)
	{
		compte = newCompte;
	}
	@Override
	public void UpDateMe(Object o) {
		// TODO Auto-generated method stub
		
	}	
	
}
