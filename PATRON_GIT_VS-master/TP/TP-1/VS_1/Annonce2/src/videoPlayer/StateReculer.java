package videoPlayer;

public class StateReculer extends State {

	/**
	 * 
	 */
	@Override
	public void PassToAnnule(Video video) {
		System.out.println("VIDEO EN ÉTAT RECULER: FONCTION ANNULER DÉSACTIVÉE  "+"\n");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToEnregistre(Video video) {
		System.out.println("VIDEO EN ÉTAT RECULER: FONCTION ENREGISTRER DÉSACTIVÉE  "+"\n");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToPause(Video video) {
		System.out.println("VIDEO EN MODE RECULER: NOW EN PAUSE!");	
		video.setState(new StatePause());		
	}
	/**
	 * 
	 */
	@Override
	public void PassToAvancer(Video video) {
/*		System.out.println("EN TRAIN D'AVANCER...");	
		video.setState(new StateAvancer());	*/
//		System.out.println("(ÉTAT ACTUELE:) :"+video.state.toString());	
		video.setState(new StateAvancer() );
		System.out.println("AVANCER...");
	}
	/**
	 * 
	 */
	@Override
	public void PassToReculer(Video video) {
		System.out.println("VIDEO DEJA EN  ÉTAT RECULER");			
	}
	/**
	 * 
	 */
	@Override
	public void PassToPlay(Video video) {
		System.out.println("VIDEO EN MODE RECULER: NOW EN PLAY!");	
		video.setState(new StatePlay());
	}
	/**
	 * 
	 */
	@Override
	public void PassToStop(Video video) {
		System.out.println("VIDEO EN ÉTAT RECULER: NOW EN STOP "+"\n");	
		video.setState(new StateStop());		
	}
	/**
	 * 
	 */
	@Override
	public void PassToRecord(Video video) {
		System.out.println("VIDEO EN ÉTAT RECULER: FONCTION RECORD DÉSACTIVÉE  "+"\n");	
		
	}

	
}//fin class