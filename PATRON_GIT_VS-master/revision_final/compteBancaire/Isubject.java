package compteBancaire;

public interface Isubject {
	
	public void Subscribe(Iobserver o);
	public void unsbscribe (Iobserver o);
	public void notifier();
	

}
