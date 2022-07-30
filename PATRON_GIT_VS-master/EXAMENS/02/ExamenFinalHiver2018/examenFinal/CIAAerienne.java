package examenFinal;
import java.util.ArrayList;

public class CIAAerienne implements Subject{

	String nome="";
  	ArrayList<Vol> vols = new ArrayList<Vol>();  
  	ArrayList<Observer> observers = new ArrayList<Observer>();  


	public void OpenVol(Vol v)	{
		vols.add(v);
		this.Notify(v);
	}	
	public void Remove(Vol v) 	{
		vols.remove(v);
	}
	
	@Override
	public void Add(Observer o) {
		observers.add(o);		
	}
	@Override
	public void Delete(Observer o) {
		observers.remove(0)	;
	}
	@Override
	public void Notify(Vol v) {
		for (Observer o : observers) {			
			o.UpDate(v);	
		}
		
	}
}
