package videoPlayer;

public class StatePause extends State {

	/**
	 * La méthode change l'état d'une video.
	 * @param: la video à changer.
	 */
	@Override
	public void PassToAnnule(Video video) {
		System.out.println("VIDEO EN ÉTAT PAUSE: FONCTION ANNULER DÉSACTIVÉ");	
	}
	/**
	 * La méthode affiche une message pour avertir que le changement 
	 * pause/enregistrement n'est pas possible (interdit en mode lecture).
	 * @param: la video à changer.
	 */
	@Override
	public void PassToEnregistre(Video video) {
		System.out.println("VIDEO EN ÉTAT PAUSE: FONCTION ENREGISTRER DÉSACTIVÉ");	
	}
	/**
	 * La méthode affiche une message pour avertir qu'il est deja au mode de changement souhaité. 
	 * @param: la video à changer.
	 */
	@Override
	public void PassToPause(Video video) {
		System.out.println("DEJA EN PAUSE");	
	}
	/**
	 * La méthode affiche une message pour avertir que le changement d'état est impossible. 
	 * @param: la video à changer.
	 */
	@Override
	public void PassToAvancer(Video video) {
		System.out.println("VIDEO EN ÉTAT PAUSE: FONCTION AVANCER DÉSACTIVÉ");	
	}
	/**
	 * La méthode affiche une message pour avertir que le changement d'état est impossible. 
	 * @param: la video à changer.
	 */
	@Override
	public void PassToReculer(Video video) {
		System.out.println("VIDEO EN ÉTAT PAUSE: FONCTION RECULER DÉSACTIVÉ");	
	}
	/**
	 * La méthode affiche une message pour avertir le changement d'état vers play. 
	 * @param: la video à changer.
	 */
	@Override
	public void PassToPlay(Video video)
	{
		System.out.println("PLAYING...");	
		video.setState(new StatePlay());			
	}
	/**
	 * La méthode affiche une message pour avertir le changement d'état vers stop. 
	 * @param: la video à changer.
	 */
	@Override
	public void PassToStop(Video video) 
	{
		video.setState(new StateStop());
		System.out.println("STOP");	
		System.out.println("(ÉTAT ACTUELE:) :"+video.state.toString()+"\n");	
	}
	/**
	 * La méthode affiche une message pour avertir le changement d'état vers record. 
	 * @param: la video à changer.
	 */
	@Override
	public void PassToRecord(Video video) {
		video.setState(new StateRecord());	
		System.out.println("EECORDING...");	
		System.out.println("(ÉTAT ACTUELE:) :"+video.state.toString()+"\n");	
	}
}//fin class
