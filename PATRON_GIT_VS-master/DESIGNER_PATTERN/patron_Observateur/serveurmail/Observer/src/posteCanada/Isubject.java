package posteCanada;

public interface Isubject {
	public void subscribe(Iobserver observer);
	public void unsubscribe(Iobserver observer);
	public void notifyobservers();

}
