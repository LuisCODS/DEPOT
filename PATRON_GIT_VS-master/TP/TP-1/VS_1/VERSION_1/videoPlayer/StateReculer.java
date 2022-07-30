package videoPlayer;

public class StateReculer extends State {

	/**
	 * 
	 */
	@Override
	public void PassToAnnule(Video video) {
		System.out.println("VIDEO EN �TAT RECULER: FONCTION ANNULER D�SACTIV�E  "+"\n");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToEnregistre(Video video) {
		System.out.println("VIDEO EN �TAT RECULER: FONCTION ENREGISTRER D�SACTIV�E  "+"\n");	
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
//		System.out.println("(�TAT ACTUELE:) :"+video.state.toString());	
		video.setState(new StateAvancer() );
		System.out.println("AVANCER...");
	}
	/**
	 * 
	 */
	@Override
	public void PassToReculer(Video video) {
		System.out.println("VIDEO DEJA EN  �TAT RECULER");			
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
		System.out.println("VIDEO EN �TAT RECULER: NOW EN STOP "+"\n");	
		video.setState(new StateStop());		
	}
	/**
	 * 
	 */
	@Override
	public void PassToRecord(Video video) {
		System.out.println("VIDEO EN �TAT RECULER: FONCTION RECORD D�SACTIV�E  "+"\n");	
		
	}

	
}//fin class