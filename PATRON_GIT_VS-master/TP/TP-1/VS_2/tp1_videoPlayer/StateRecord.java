package tp1_videoPlayer;

public class StateRecord extends State {

	/**
	*La m�thode r�initialise l'�tat de la video de d�part, car le client n'a pas voulu le sauvegarder..
	*/
	@Override
	public void PassToAnnule(Video video) {
		video.setState(new StateInitial());
		System.out.println("SAUVEGARDE ANNUL�E"+"\n");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToStop(Video video) {			
/*		System.out.println("NE CONCERNE PAS");
*/	}
	/**
	 * La m�thode arrete la video et l'enregistre par la suite.
	 */
	@Override
	public void PassToEnregistre(Video video) {
		System.out.println("VIDEO ENREGISTR�E AVEC SUCC�S");	
		video.setState(new stateEnregistre());
	}
	/**
	 * La m�thode  pause la video e montre l'�tat actuel de la video apres changement..
	 */
	@Override
	public void PassToPause(Video video) {
		video.setState(new StatePause());
		System.out.println("ENREGISTREMENT EN PAUSE");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToAvancer(Video video) {
		System.out.println("NE CONCERNE PAS");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToReculer(Video video) {
		System.out.println("NE CONCERNE PAS");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToPlay(Video video) {
		System.out.println("NE CONCERNE PAS");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToRecord(Video video) {
		System.out.println("NE CONCERNE PAS");	
		
	}

}