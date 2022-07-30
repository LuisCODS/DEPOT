package tp1_videoPlayer;

public class StateStop extends State {

	/**
	 * Change l'�tat de la video � Play.
	 * @param: la video dont l'�tat doit �tre mis � jours.
	 */
	@Override
	public void PassToPlay(Video video) 
	{
		video.setState(new StatePlay() );
		System.out.println("PLAY...");	
	}
	/**
	 * La m�thode affiche une message pour avertir que la video a �t� bien enregistr�e 
	 * @param: la video � changer.
	 */
	@Override
	public void PassToEnregistre(Video video) {
		video.setState(new stateEnregistre());
		System.out.println("VIDEO ENREGISTR�E AVEC SUCCC�S");	
	}
	/**
	 * La m�thode affiche une message pour avertir que la video ne peut pas passe � pause
	 * une fois qu'elle est en Stop. 
	 * @param: la video � changer.
	 */
	@Override
	public void PassToPause(Video video) {
		System.out.println("VIDEO EN �TAT STOP: FONCTION PAUSE D�SACTIV�E"+"\n");	
	}
	/**
	 * La m�thode affiche une message pour avertir que la video ne peut pas passe � Avancer
	 * une fois qu'elle est en Stop. 
	 * @param: la video � changer.
	 */
	@Override
	public void PassToAvancer(Video video) {
		System.out.println("VIDEO EN �TAT STOP: FONCTION AVANCER D�SACTIV�E"+"\n");	
	}
	/**
	 * La m�thode affiche une message pour avertir que la video ne peut pas passe � Reculer
	 * une fois qu'elle est en Stop. 
	 * @param: la video � changer.
	 */
	@Override
	public void PassToReculer(Video video) {
		System.out.println("VIDEO EN �TAT STOP: FONCTION RECULER D�SACTIV�E"+"\n");	
	}
	/**
	 * La m�thode affiche une message pour avertir que la video ne peut pas passe � Stop
	 * une fois qu'elle est deja en �tat Stop. 
	 * @param: la video � changer.
	 */
	@Override
	public void PassToStop(Video video) {
		System.out.println("VIDEO DEJA EN MODE STOP!");	
	}
	/**
	 * La m�thode change l'�tat de la video � ANNUL� et affiche � l'utilisateur le changement.
	 * @param: la video dont l'�tat doit �tre mis � jours.
	 */
	@Override
	public void PassToAnnule(Video video) {
		video.setState(new StateAnnule());
		System.out.println("ENREGISTREMENT ANNUL�");
	}
	/**
	 * Change l'�tat de la video � RECORD.
	 * @param: la video dont l'�tat doit �tre mis � jours.
	 */
	@Override
	public void PassToRecord(Video video) {
		video.setState(new StateRecord());
		System.out.println("RECORDING...");			
	}

}//fin class