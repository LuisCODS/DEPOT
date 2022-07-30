package videoPlayer;

/**
 * @Description:application qui lit une vid�o.Elle peut en plus capturer
 * e sauvegarder. En outre, cette application permet � l�utilisateur de
 * changer les �tats de la vid�o.
 */
public class VideoPlayer {

	protected static StrategyMode strategie = null;
	protected Video video = null;

	/**
	 * @Constructer VideoPlayer est initialis� avec un Mode(Lecture ou Enregistrement) et avec un Video par default en �tat stop. 
	 * @param mode: Mode(Lecture ou Enregistrement)
	 * @param video: La video 
	 */
	public  VideoPlayer(Video v, StrategyMode s) 
	{
		this.video = v.setState(new Stop);;
		this.strategie = s;
	}

	// _____________________________ M�THODES _____________________________
	/**
	 * La m�thode fait rouler la video en affichant au d�part son �tat courant.
	 */
	public void Play() 
	{    
		//showEtat();
		this.strategie.play(this.video, this.strategie);
	}
	/**
	 * @Desciption: 
	 * La m�thode fait pauser la video en affichant au d�part son �tat courant.
	 */
	public void Pause() 
	{    	
		//showEtat();
		strategie.pause(video, this.strategie);
	}
	/**
	 * @Desciption: 
	 * La m�thode fait recouler la video en affichant au d�part son �tat courant.
	 */
	public void Reculer() 
	{    	
		//showEtat();
		//strategie.reculer(video, this.strategie);
	}
	/**
	 * @Desciption: 
	 * La m�thode fait avancer la video en affichant au d�part son �tat courant.
	 */
	public void Avancer() 
	{    	
		//showEtat();
		//strategie.avancer(video, this.strategie);
	}
	/**
	 * @Desciption: 
	 * La m�thode fait stoper la video en affichant au d�part son �tat courant.
	 */
	public void Stop() 
	{    	
		strategie.stop(video, this.strategie);
	}  
	/**
	 * @Desciption: 
	 * La m�thode fait l'enregistrement de la video en affichant au d�part son �tat courant.
	 */
	public void Record()
	{		
		//strategie.record(video, this.strategie);
	} 
	/**
	 * M�thode de support pour afficher l'�tat courant avant chaque changement d'�tat de la video.
	 */
/*	private void showEtat()
	{
		System.out.println("(�TAT ACTUELE:) :"+this.video.state.toString()+"\n");	
	}*/	
	// _____________________________ GETS & SET _____________________________
	public static StrategyMode getStratege() {
		return strategie;
	}
	public static void setStratege(StrategyMode s) {
		VideoPlayer.strategie = s;
	}
	public void setVideo(Video v) {
		this.video = v;
	}
	
	
}//fin class