package Projet_CompteBancaire.src;

public class Test {

	public static void main(String[] args) {

		//Get instance unique
		LogFille logf = LogFille.getInstance();
		
		CompteBancaire compte = new CompteBancaire();
		Client client = new Client(compte);
		
		compte.Add(logf);	
		compte.Add(client);

		
	


	}

}
