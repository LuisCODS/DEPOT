package lecteurFlash;

public class Video extends LecteurMedia{

	// S�lection d�un outil media
	@Override
	public void choisirLecteurMedia() {
		System.out.println("CHOIX : VIDEO ");		
	}
	
	//Lecture d�un fichier
	@Override
	public void lecteur() {
		System.out.println("PLAYING...");		
	}
}
