package Projet_CompteBancaire.src;
import java.util.ArrayList;

public class CompteBancaire implements IObservable {

	double Sold;
	int numero;	
	ArrayList<IObservateur> observables = new ArrayList<IObservateur>();
	
	
	public void Deposer(Double montant)
	{
		if (this.Sold >= montant) {
			
		}
	}
	
	public void Retirer(Double montant)
	{
		if (this.Sold >= montant) {
			this.Sold = this.Sold - montant;
		}
		else
			System.out.println("Solde insufisant");

	}
	
	@Override
	public void Add(IObservateur o)
	{
		observables.add(o);
	}
	@Override
	public void Remove(IObservateur o) 
	{
		observables.remove(o);
	}
	@Override
	public void Notify() 
	{
		for (IObservateur obs : observables) {
			obs.UpDateMe(this);	
		}	
	}
	
	public double getSold() {
		return Sold;
	}
	public void setSold(double sold) {
		Sold = sold;
	}
}
