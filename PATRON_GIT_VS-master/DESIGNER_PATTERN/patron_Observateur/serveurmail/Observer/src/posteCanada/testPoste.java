package posteCanada;

public class testPoste {

	public static void main(String[] args) {
		Person p1 = new Person("fname1", "lname1", "adresseemail1");
		Person p2 = new Person("fname2", "lname2", "adresseemail2");
		
		
		
		Mail m1= new Mail(p1, p2, "salut");
		
		Mail m2= new Mail(p2, p1,"Felicitation");
		Personneobservateur pm1= new Personneobservateur("fname1", "lname1", "adresseemail1");
		Personneobservateur pm2= new Personneobservateur("fname2", "lname2", "adresseemail2");
		
		
		
		ServeurMail serveurMail = new ServeurMail();
		
		serveurMail.subscribe(pm1);
		serveurMail.subscribe(pm2);
		
		
		
		serveurMail.addNewMail(m1);
		serveurMail.addNewMail(m2);
		
		
		

	}

}
