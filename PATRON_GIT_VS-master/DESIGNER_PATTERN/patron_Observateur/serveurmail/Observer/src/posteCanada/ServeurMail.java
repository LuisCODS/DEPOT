package posteCanada;

import java.util.ArrayList;

public class ServeurMail implements Isubject{
	
	ArrayList<Mail> emails;
	ArrayList<Iobserver> observers;
	
	String emailadresse;
	
	
	
	public ServeurMail() {
		
		this.emails = new ArrayList<Mail>();
		this.observers = new ArrayList<Iobserver>();
		this.emailadresse = null;
	}

	public void subscribe(Iobserver observer) {
		observers.add(observer);
		
	}
	
	public void unsubscribe(Iobserver observer) {
		observers.remove(observer);
		
	}
	
	public void notifyobservers() {
		
		
			for (Iobserver observer:observers)
				if(observer.getMail()==emailadresse)
					observer.notifyMe();
	}
	
		
	public void addNewMail(Mail mail)
	    {this.emailadresse=mail.getSender().getEmailadress();	
		emails.add(mail);
		notifyobservers();
	}
		
		
	
	
	

}
