package pkVoiture;

import java.util.ArrayList;

/**
 * Default constructor
 */
public class FeuDeCirculation implements IObservable {

	ArrayList<IObservateur> observers = new ArrayList<IObservateur>();
	StateFeu state = null;

	// MÉTHODES
	@Override
	public void Add(IObservateur o) {
		observers.add(o);
	}

	@Override
	public void Remove(IObservateur o) {
		observers.remove(o);
	}

	@Override
	public void Notify() {
		// Notifie tous les observateurs
		for (IObservateur iObservateur : observers) {
			iObservateur.upDate(this.getState());
		}
	}

	public StateFeu getState() {
		return state;
	}

	public void setState(StateFeu state) {
		this.state = state;
		this.Notify();
	}

}
