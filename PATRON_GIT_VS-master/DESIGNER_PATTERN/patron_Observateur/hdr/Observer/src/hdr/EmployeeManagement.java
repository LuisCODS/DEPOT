package hdr;

import java.util.ArrayList;

public class EmployeeManagement implements Iobservable{
	ArrayList<Employe> employes;
	ArrayList<Iobserver> allObservers;
	
	String message;
	Employe employe;
	
	public EmployeeManagement() {
		this.employes=new ArrayList<Employe>();
		this.allObservers=new ArrayList<Iobserver>();
	
	}

	public void Subscribe(Iobserver o) {
		allObservers.add(o);
		
	}
	public void unsbscribe(Iobserver o) {
		allObservers.remove(o);
		
		
	}
	
	public void notifier() {
		for(Iobserver o:allObservers)
		{
			o.update(employe, message);
			
		}
	}
		
public void addEmploye(Employe e)
    {message="nouveau employe";
    this.employe=e;
	employes.add(e);
	notifier();
	
    }
public void removeEmploye(Employe e)
{message="employe parti";
this.employe=e;
notifier();
employes.remove(e);

}

	
	
	
	
	
	

}
