package lecteurFlash;

public class Audio extends LecteurMedia{

	// S�lection d�un outil media
	@Override
	public void choisirLecteurMedia() {
		System.out.println("CHOIX : AUDIO ");		
	}
	
	//Lecture d�un fichier
	@Override
	public void lecteur() {
		System.out.println("PLAYING...");		
	}

}
