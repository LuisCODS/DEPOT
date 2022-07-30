package videoPlayer;

public class StateAvancer extends State {


	/**
	 * 
	 */
	@Override
	public void PassToAnnule(Video video) {
		System.out.println("VIDEO EN AVANCER: FONCTION ANNULER DÉSACTIVÉ");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToEnregistre(Video video) {
		System.out.println("VIDEO EN ÉTAT AVANCER : FONCTION ENREGISTREMENT DÉSACTIVÉ");	
	}
	/**
	 * Passe la video de l'état Avancer en Pause.
	 */	
	@Override
	public void PassToPause(Video video) {
		video.setState(new StatePause() );
		System.out.println("PAUSE");
		System.out.println("(ÉTAT ACTUELE:) :"+video.state.toString()+"\n");	

	}
	/**
	 * 
	 */
	@Override
	public void PassToAvancer(Video video) {
		System.out.println("DEJA EN ÉTAT AVANCÉ "+"\n");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToReculer(Video video) {
		System.out.println("VIDEO EN ÉTAT AVANCER : PASSE À RECULER"+"\n");	
		video.setState(new StateReculer());			
	}
	/**
	 * 
	 */
	@Override
	public void PassToPlay(Video video) {
		System.out.println("VIDEO EN ÉTAT AVANCER : PASSE À PLAY"+"\n");	
		video.setState(new StatePlay());		
	}
	/**
	 * 
	 */
	@Override
	public void PassToStop(Video video) {
		System.out.println("VIDEO EN ÉTAT AVANCER : PASSE À STOP"+"\n");	
		video.setState(new StateStop());		
	}
	/**
	 * 
	 */
	@Override
	public void PassToRecord(Video video) {
		System.out.println("NE CONCERNE PAS"+"\n");	
		
	}
}//fin class