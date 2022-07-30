package pkParking;
import java.util.ArrayList;



public class Parking implements IObservable{
	
	//CHAMPS
	static final int PLACES_DISPONNIBLES = 100 ;
	int nbVoitureIN ;
	ArrayList<IObservateur> observers = new ArrayList<IObservateur>();
	
	
	//MÉTHODES
	@Override
	public void Subscribe(IObservateur o) {
		observers.add(o);			
	}
	@Override
	public void unsbscribe(IObservateur o) {
		observers.remove(o);			
	}
	@Override
	public void notifier() {
		for (IObservateur iObservateur : observers) {
			iObservateur.UpDateMe();	
		}			
	}	

	public int getNbVoitureIN() {
		return this.nbVoitureIN-this.PLACES_DISPONNIBLES ;
	}
	public void setNbVoitureIN(int nbVoitureIN) 
	{
		// ENCORE DE LA PLACE
		if (nbVoitureIN <= this.PLACES_DISPONNIBLES) 
		{
			this.nbVoitureIN = nbVoitureIN;
			System.out.println("Places disponibles :"+ this.getNbVoitureIN());
			}
		else
		{
			// CAPACITE MAX
			System.out.println("Le stationnement est plein!"+ "\n");
			notifier();	
		}
	}	
	
	
}
