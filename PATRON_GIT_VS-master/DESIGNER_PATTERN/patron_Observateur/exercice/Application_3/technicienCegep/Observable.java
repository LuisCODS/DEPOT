package technicienCegep;

import java.util.ArrayList;

public abstract class  Observable {

	ArrayList<IObservateur> observers = new ArrayList<IObservateur>();
	
	public void Subscribe(IObservateur o){
		observers.add(o);	
	};
	public void unsbscribe (IObservateur o){
		observers.remove(o);	
	};
	public void notifier()
	{
		for (IObservateur iObservateur : observers) {
			
		}
	};
	
}
