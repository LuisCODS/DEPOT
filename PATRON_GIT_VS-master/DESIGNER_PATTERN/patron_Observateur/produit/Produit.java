package ObserverProduit;

import java.util.ArrayList;

public class Produit implements IObservable{
	
	ArrayList<IObservateur> allObservers;
	String nome;
	double prix;
	boolean available;	
	double oldPrix  ;
	
	// ========================================================= 
	Produit(String nome, double prix) 
	{		
		allObservers = new ArrayList<IObservateur>();
		this.nome=nome;
		available=false;
		this.prix = prix;
	}

	// ==========================MÉTHODES =============================== 
	@Override
	public void Subscribe(IObservateur o) {	
		allObservers.add(o);
	}
	@Override
	public void unsbscribe(IObservateur o) {
		allObservers.remove(o);
	}
	@Override
	public void notifier(IObservateur o) 
	{
			o.UpDateMe(this);		
	}	

	
	// ==========================GET & SET =============================== 

	public boolean isAvailable() {
		return available;	
	}
	public void getAvailable(boolean available)
	{	
		this.available = available;
	
		if ( this.available==true) {
			for(IObservateur obs:allObservers)
			{
				if(obs instanceof ObservateurDisponibilite)		
				notifier(obs);
			}	
		}		
		
	}
	public String getProduit() {
		return nome;
	}

	public double getPrix() {
		return prix;
	}

	public void setPrix( double newPrix)
	{
		double oldPrix = this.prix;
		this.prix = newPrix;
		
		if (this.prix != oldPrix) {
			for(IObservateur obs:allObservers)
			{
				if(obs instanceof ObservateurPrix)		
				notifier(obs);
			}	
		}
	
	}

	public String getNome() {
		return nome;
	}

	public void setNome(String nome) {
		this.nome = nome;
	}




}//fn class
