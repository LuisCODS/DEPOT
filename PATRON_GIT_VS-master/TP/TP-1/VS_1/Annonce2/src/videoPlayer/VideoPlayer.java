package videoPlayer;

/**
 * @Description:application qui lit une vid�o.Elle peut en plus capturer
 * e sauvegarder. En outre, cette application permet � l�utilisateur de
 * changer les �tats de la vid�o.
 */
public class VideoPlayer {

	protected static Strategy strategie;
	protected Video video ;

	/**
	 * @Constructer VideoPlayer est initialis� avec un Mode et un Video par default. 
	 * @param mode: Mode lecture.
	 * @param video: La video avec son respective etat(au depart � Stop).
	 */
	public  VideoPlayer(Video video, Strategy stratege) 
	{
		this.video = video;
		setStratege(stratege);
	}

	// _____________________________ M�THODES _____________________________
	/**
	 * La m�thode fait rouler la video en affichant au d�part son �tat courant.
	 */
	public void Play() 
	{    
		if (strategie instanceof  Lecture ) {
				showEtat();
				strategie.play(this.video, this.strategie);
		}
		else{
			System.out.println("PLAY INDISPONIBLE EN MODE ENREGISTREMENT ");
		}

	}
	/**
	 * @Desciption: 
	 * La m�thode fait pauser la video en affichant au d�part son �tat courant.
	 */
	public void Pause() 
	{    	
		showEtat();
		strategie.pause(video, this.strategie);
	}
	/**
	 * @Desciption: 
	 * La m�thode fait recouler la video en affichant au d�part son �tat courant.
	 */
	public void Reculer() 
	{  	
		if (strategie instanceof  Lecture ) {
			showEtat();
			strategie.reculer(this.video, this.strategie);
		}
		else{
			System.out.println("Reculer INDISPONIBLE EN MODE ENREGISTREMENT ");
		}
	}
	/**
	 * @Desciption: 
	 * La m�thode fait avancer la video en affichant au d�part son �tat courant.
	 */
	public void Avancer() 
	{    	
		
		if (strategie instanceof  Lecture ) {
			showEtat();
			strategie.avancer(this.video, this.strategie);
		}
		else{
			System.out.println("Avancer INDISPONIBLE EN MODE ENREGISTREMENT ");
		}
	}
	/**
	 * @Desciption: 
	 * La m�thode fait stoper la video en affichant au d�part son �tat courant.
	 */
	public void Stop() 
	{    	
		
		if (strategie instanceof  Enregistrement ) {
			showEtat();
			strategie.stop(video, this.strategie);
		}
		else{
			System.out.println("Record INDISPONIBLE EN MODE LECTURE ");
		}
	}  
	/**
	 * @Desciption: 
	 * La m�thode fait l'enregistrement de la video en affichant au d�part son �tat courant.
	 */
	public void Record()
	{		
		
		if (strategie instanceof  Enregistrement ) {
			showEtat();
			strategie.record(video, this.strategie);
		}
		else{
			System.out.println("Record INDISPONIBLE EN MODE LECTURE ");
		}
	} 
	/**
	 * M�thode de support pour afficher l'�tat courant avant chaque changement d'�tat de la video.
	 */
	private void showEtat()
	{
		System.out.println("(�TAT ACTUELE:) :"+this.video.state.toString()+"\n");	
	}	
	// _____________________________ GETS & SET _____________________________
	public static Strategy getStratege() {
		return strategie;
	}
	public static void setStratege(Strategy stratege) {
		VideoPlayer.strategie = stratege;
	}
}//fin class