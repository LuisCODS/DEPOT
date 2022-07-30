package compteBancaire;

public class test {

	public static void main(String[] args) {
		CompteBancaire c1=new CompteBancaire(123456);
		CompteBancaire c2=new CompteBancaire(789101112);
		
		Log log=Log.getInstance();
		
		Compteur d1= new Compteur(c1);
		
		
		Compteur d2= new Compteur(c2);
		
		
	
		
		c1.Subscribe(d1);
		c2.Subscribe(d2);
		
		c1.deposerArgent(200);
		c2.deposerArgent(400);
		
		System.out.println(log.afficherLog());
		

	}

}
