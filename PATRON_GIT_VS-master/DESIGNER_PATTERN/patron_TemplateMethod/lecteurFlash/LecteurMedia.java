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
	//IMPLEMENTÉ ICI: actions en commun
	void affichageMessage()
	{
		System.out.println("Welcome to template media ");	
	}		
	
	//IMPLEMENTÉ AILLEURS : actions spécifiques
	public abstract void choisirLecteurMedia();
 	public abstract void lecteur();
	
}//fin
