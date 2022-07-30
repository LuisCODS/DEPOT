package ObserverProduit;


public interface IObservable {
	
	public void Subscribe(IObservateur o);
	public void unsbscribe (IObservateur o);
	public void notifier(IObservateur o);
	

}
