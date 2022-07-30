package technicienCegep;

public class Imprimante extends Observable{

	boolean bacVide = false;		
	


	public boolean isBacVide() {
		return bacVide;
	}
	public void setBacVide(boolean bacVide)
	{
		this.bacVide = bacVide;	
		
		if (this.bacVide == true) {
			this.notifier();
		}
	}
	
	
}
