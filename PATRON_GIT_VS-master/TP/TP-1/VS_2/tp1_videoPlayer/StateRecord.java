package tp1_videoPlayer;

public class StateRecord extends State {

	/**
	*La méthode réinitialise l'état de la video de départ, car le client n'a pas voulu le sauvegarder..
	*/
	@Override
	public void PassToAnnule(Video video) {
		video.setState(new StateInitial());
		System.out.println("SAUVEGARDE ANNULÉE"+"\n");	
	}
	/**
	 * 
	 */
	@Override
	public void PassToStop(Video video) {			
/*		System.out.println("NE CONCERNE PAS");
*/	}
	/**
	 * La méthode arrete la video et l'enregistre par la suite.
	 */
	@Override
	public void PassToEnregistre(Video video) {
		System.out.println("VIDEO ENREGISTRÉE AVEC SUCCÈS");	
		video.setState(new stateEnregistre());
	}
	/**
	 * La méthode  pause la video e montre l'état actuel de la video apres changement..
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