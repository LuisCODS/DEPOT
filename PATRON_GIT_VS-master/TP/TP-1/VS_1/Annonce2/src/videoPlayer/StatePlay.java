package videoPlayer;

public class StatePlay extends State {

	/**
	 * 
	 */
	@Override
	public void PassToPause(Video video) {
		System.out.println("VIDEO EN ÉTAT PLAY: NOW EN PAUSE "+"\n");	
		video.setState(new StatePause());		
	}
	/**
	 * 
	 */
	@Override
	public void PassToAvancer(Video video) {
		System.out.println("VIDEO EN ÉTAT PLAY: NOW EN AVANT");	
		video.setState(new StateAvancer());	
	}
	/**
	 * 
	 */
	@Override
	public void PassToReculer(Video video) 
	{
//		System.out.println("(ÉTAT ACTUELE:) :"+video.state.toString());	
		video.setState(new StateReculer() );
		System.out.println("RECULER...");
	}
	/**
	 * 
	 */
	@Override
	public void PassToPlay(Video video) {
		System.out.println("LA VIDEO EST DEJA EN MODE PLAY");			
	}
	@Override
	public void PassToStop(Video video) {
		System.out.println("VIDEO EN ÉTAT PLAY: NOW EN STOP ");	
		video.setState(new StateStop());		
	}
	/**
	 * 
	 */
	@Override
	public void PassToAnnule(Video video) {
		System.out.println("NE CONCERNE PAS!");			
	}
	/**
	 * 
	 */
	@Override
	public void PassToEnregistre(Video video) {
		System.out.println("ENREGISTREMENT EN COURS...");	
		video.setState(new StateRecord());			
	}
	/**
	 * 
	 */
	@Override
	public void PassToRecord(Video video) {
		System.out.println("NE CONCERNE PAS!");	
	}

}//fin class