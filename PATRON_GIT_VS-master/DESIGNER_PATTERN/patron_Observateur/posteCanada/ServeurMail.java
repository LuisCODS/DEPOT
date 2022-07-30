/*package posteCanada;

import java.util.ArrayList;

import ObserverProduit.IObservable;
import ObserverProduit.IObservateur;

public class ServeurMail implements IObservable{
	
	ArrayList<Mail> emails;
	ArrayList<IObservateur> observers;
	
	String emailadresse;
	
	
	
	public ServeurMail() {
		
		this.emails = new ArrayList<Mail>();
		this.observers = new ArrayList<IObservateur>();
		this.emailadresse = null;
	}

	public void subscribe(IObservateur observer) {
		observers.add(observer);
		
	}
	
	public void unsubscribe(IObservateur observer) {
		observers.remove(observer);
		
	}
	
	public void notifyobservers() {
		
		
			for (IObservateur observer:observers)
				if(observer.getMail()==emailadresse)
					observer.notifyMe();
	}
	
		
	public void addNewMail(Mail mail)
	    {this.emailadresse=mail.getEmail();	
		emails.add(mail);
		notifyobservers();
	}

	@Override
	public void Subscribe(IObservateur o) {
		// TODO Auto-generated method stub
		
	}

	@Override
	public void unsbscribe(IObservateur o) {
		// TODO Auto-generated method stub
		
	}

	@Override
	public void notifier(IObservateur o) {
		// TODO Auto-generated method stub
		
	}
		
		
	
	
	

}
*/