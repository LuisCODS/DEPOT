package pkVoiture;

public interface IObservable {
	
	public void Add(IObservateur o);	
	public void Remove (IObservateur o);
	public void Notify();
}

