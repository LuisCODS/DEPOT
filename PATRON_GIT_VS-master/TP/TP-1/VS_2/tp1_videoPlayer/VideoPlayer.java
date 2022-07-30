package tp1_videoPlayer;

/**
 * @Description:application qui lit une vidéo.Elle peut en plus capturer
 * e sauvegarder. En outre, cette application permet à l’utilisateur de
 * changer les états de la vidéo.
 */
public class VideoPlayer {

	protected static Strategy strategie = null;
	protected Video video ;

	/**
	 * @Constructer VideoPlayer est initialisé avec un Mode et un Video par default. 
	 * @param mode: Mode lecture.
	 * @param video: La video avec son respective etat(au depart à Stop).
	 */
	public  VideoPlayer(Video video, Strategy stratege) 
	{
		this.video = video;
		setStratege(stratege);
	}

	// _____________________________ MÉTHODES _____________________________
	/**
	 * La méthode fait rouler la video en affichant au départ son état courant.
	 */
	public void Play() 
	{    
		showEtat();
		strategie.play(this.video, this.strategie);
	}
	/**
	 * @Desciption: 
	 * La méthode fait pauser la video en affichant au départ son état courant.
	 */
	public void Pause() 
	{    	
		showEtat();
		strategie.pause(video, this.strategie);
	}
	/**
	 * @Desciption: 
	 * La méthode fait recouler la video en affichant au départ son état courant.
	 */
	public void Reculer() 
	{    	
		showEtat();
		strategie.reculer(video, this.strategie);
	}
	/**
	 * @Desciption: 
	 * La méthode fait avancer la video en affichant au départ son état courant.
	 */
	public void Avancer() 
	{    	
		showEtat();
		strategie.avancer(video, this.strategie);
	}
	/**
	 * @Desciption: 
	 * La méthode fait stoper la video en affichant au départ son état courant.
	 */
	public void Stop() 
	{    	
		strategie.stop(video, this.strategie);
	}  
	/**
	 * @Desciption: 
	 * La méthode fait l'enregistrement de la video en affichant au départ son état courant.
	 */
	public void Record()
	{		
		strategie.record(video, this.strategie);
	} 
	/**
	 * Méthode de support pour afficher l'état courant avant chaque changement d'état de la video.
	 */
	private void showEtat()
	{
		System.out.println("(ÉTAT ACTUELE:) :"+this.video.state.toString()+"\n");	
	}	
	// _____________________________ GETS & SET _____________________________
	public static Strategy getStratege() {
		return strategie;
	}
	public static void setStratege(Strategy stratege) {
		VideoPlayer.strategie = stratege;
	}
}//fin class