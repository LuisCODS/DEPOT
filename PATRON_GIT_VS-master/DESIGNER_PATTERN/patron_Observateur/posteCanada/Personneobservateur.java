package posteCanada;

import ObserverProduit.IObservateur;

public class Personneobservateur extends Person implements IObservateur{

	
	
	

	public Personneobservateur(String fname, String lname, String emailadress) {
		super(fname, lname, emailadress);
		
	}


	public void notifyMe() {
		System.out.println(Fname+Lname+"vous avez un nouveau email");
		
	}

	
	public String getMail() {
		
		return super.getEmailadress();
	}


	@Override
	public void UpDateMe(Object o) {
		// TODO Auto-generated method stub
		
	}


	


}
