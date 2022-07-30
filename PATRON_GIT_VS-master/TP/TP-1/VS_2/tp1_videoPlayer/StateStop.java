package tp1_videoPlayer;

public class StateStop extends State {

	/**
	 * Change l'état de la video à Play.
	 * @param: la video dont l'état doit être mis à jours.
	 */
	@Override
	public void PassToPlay(Video video) 
	{
		video.setState(new StatePlay() );
		System.out.println("PLAY...");	
	}
	/**
	 * La méthode affiche une message pour avertir que la video a été bien enregistrée 
	 * @param: la video à changer.
	 */
	@Override
	public void PassToEnregistre(Video video) {
		video.setState(new stateEnregistre());
		System.out.println("VIDEO ENREGISTRÉE AVEC SUCCCÈS");	
	}
	/**
	 * La méthode affiche une message pour avertir que la video ne peut pas passe à pause
	 * une fois qu'elle est en Stop. 
	 * @param: la video à changer.
	 */
	@Override
	public void PassToPause(Video video) {
		System.out.println("VIDEO EN ÉTAT STOP: FONCTION PAUSE DÉSACTIVÉE"+"\n");	
	}
	/**
	 * La méthode affiche une message pour avertir que la video ne peut pas passe à Avancer
	 * une fois qu'elle est en Stop. 
	 * @param: la video à changer.
	 */
	@Override
	public void PassToAvancer(Video video) {
		System.out.println("VIDEO EN ÉTAT STOP: FONCTION AVANCER DÉSACTIVÉE"+"\n");	
	}
	/**
	 * La méthode affiche une message pour avertir que la video ne peut pas passe à Reculer
	 * une fois qu'elle est en Stop. 
	 * @param: la video à changer.
	 */
	@Override
	public void PassToReculer(Video video) {
		System.out.println("VIDEO EN ÉTAT STOP: FONCTION RECULER DÉSACTIVÉE"+"\n");	
	}
	/**
	 * La méthode affiche une message pour avertir que la video ne peut pas passe à Stop
	 * une fois qu'elle est deja en état Stop. 
	 * @param: la video à changer.
	 */
	@Override
	public void PassToStop(Video video) {
		System.out.println("VIDEO DEJA EN MODE STOP!");	
	}
	/**
	 * La méthode change l'état de la video à ANNULÉ et affiche à l'utilisateur le changement.
	 * @param: la video dont l'état doit être mis à jours.
	 */
	@Override
	public void PassToAnnule(Video video) {
		video.setState(new StateAnnule());
		System.out.println("ENREGISTREMENT ANNULÉ");
	}
	/**
	 * Change l'état de la video à RECORD.
	 * @param: la video dont l'état doit être mis à jours.
	 */
	@Override
	public void PassToRecord(Video video) {
		video.setState(new StateRecord());
		System.out.println("RECORDING...");			
	}

}//fin class