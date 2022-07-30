package lecteurFlash;

/**
 *patron template method
 */
public abstract class LecteurMedia {
	
	//FAIT DES APPELS
	public void lire()
	{
		affichageMessage();
		choisirLecteurMedia();
		lecteur();		
	}	
	//IMPLEMENT� ICI: actions en commun
	void affichageMessage()
	{
		System.out.println("Welcome to template media ");	
	}		
	
	//IMPLEMENT� AILLEURS : actions sp�cifiques
	public abstract void choisirLecteurMedia();
 	public abstract void lecteur();
	
}//fin
