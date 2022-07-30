package posteCanada;

public class Personneobservateur extends Person implements Iobserver{

	
	
	

	public Personneobservateur(String fname, String lname, String emailadress) {
		super(fname, lname, emailadress);
		
	}


	public void notifyMe() {
		System.out.println(Fname+Lname+"vous avez un nouveau email");
		
	}

	
	public String getMail() {
		
		return super.getEmailadress();
	}


	


}
