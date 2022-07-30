package videoPlayer;

public class StateAvancer extends State {


	/**
	 * 
	 */
	@Override
	public void PassToAnnule(Video video) {
		System.out.println("VIDEO EN AVANCER: FONCTION ANNULER D�SACTIV�");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToEnregistre(Video video) {
		System.out.println("VIDEO EN �TAT AVANCER : FONCTION ENREGISTREMENT D�SACTIV�");	
	}
	/**
	 * Passe la video de l'�tat Avancer en Pause.
	 */	
	@Override
	public void PassToPause(Video video) {
		video.setState(new StatePause() );
		System.out.println("PAUSE");
		System.out.println("(�TAT ACTUELE:) :"+video.state.toString()+"\n");	

	}
	/**
	 * 
	 */
	@Override
	public void PassToAvancer(Video video) {
		System.out.println("DEJA EN �TAT AVANC� "+"\n");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToReculer(Video video) {
		System.out.println("VIDEO EN �TAT AVANCER : PASSE � RECULER"+"\n");	
		video.setState(new StateReculer());			
	}
	/**
	 * 
	 */
	@Override
	public void PassToPlay(Video video) {
		System.out.println("VIDEO EN �TAT AVANCER : PASSE � PLAY"+"\n");	
		video.setState(new StatePlay());		
	}
	/**
	 * 
	 */
	@Override
	public void PassToStop(Video video) {
		System.out.println("VIDEO EN �TAT AVANCER : PASSE � STOP"+"\n");	
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