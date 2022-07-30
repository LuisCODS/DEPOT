package videoPlayer;

/**
 * @author Luis-Claudio, Oliveira dos Santos
 * @Date 18/02/2018
 */
public class Test {

	public static void main(String[] args) {

	    /* 	Le lecteur est initialisé avec une video en état STOP et avec une strategy LECTURE */
		System.out.println("\n"+"=======================(TEST 1: MODE-LECTURE) ======================= "+"\n");	
		VideoPlayer enLecture = new VideoPlayer(new Video(), new Lecture());

		//_______ INTERFACE EN MODE LECTURE________
/*		enLecture.Play();	
		enLecture.Reculer();
		enLecture.Avancer();
		enLecture.Pause();
		enLecture.Stop();*/
		
	    /* Le lecteur est initialisé avec une video en état STOP et avec une strategy ENREGISTREMENT */
		System.out.println("\n"+"=======================(TEST 2: MODE-ENREGISTREMENT) ======================= "+"\n");	
		VideoPlayer enRegistrement = new VideoPlayer(new Video(),new Enregistrement());
		
		//_______ INTERFACE EN MODE ENREGISTREMENT________
		enRegistrement.Record();
		enRegistrement.Stop();
		enRegistrement.Pause(); 
		 		

	}//FIN MAIN 
}
