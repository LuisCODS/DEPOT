package videoPlayer;

public class StatePause extends State {

	/**
	 * La m�thode change l'�tat d'une video.
	 * @param: la video � changer.
	 */
	@Override
	public void PassToAnnule(Video video) {
		System.out.println("VIDEO EN �TAT PAUSE: FONCTION ANNULER D�SACTIV�");	
	}
	/**
	 * La m�thode affiche une message pour avertir que le changement 
	 * pause/enregistrement n'est pas possible (interdit en mode lecture).
	 * @param: la video � changer.
	 */
	@Override
	public void PassToEnregistre(Video video) {
		System.out.println("VIDEO EN �TAT PAUSE: FONCTION ENREGISTRER D�SACTIV�");	
	}
	/**
	 * La m�thode affiche une message pour avertir qu'il est deja au mode de changement souhait�. 
	 * @param: la video � changer.
	 */
	@Override
	public void PassToPause(Video video) {
		System.out.println("DEJA EN PAUSE");	
	}
	/**
	 * La m�thode affiche une message pour avertir que le changement d'�tat est impossible. 
	 * @param: la video � changer.
	 */
	@Override
	public void PassToAvancer(Video video) {
		System.out.println("VIDEO EN �TAT PAUSE: FONCTION AVANCER D�SACTIV�");	
	}
	/**
	 * La m�thode affiche une message pour avertir que le changement d'�tat est impossible. 
	 * @param: la video � changer.
	 */
	@Override
	public void PassToReculer(Video video) {
		System.out.println("VIDEO EN �TAT PAUSE: FONCTION RECULER D�SACTIV�");	
	}
	/**
	 * La m�thode affiche une message pour avertir le changement d'�tat vers play. 
	 * @param: la video � changer.
	 */
	@Override
	public void PassToPlay(Video video)
	{
		System.out.println("PLAYING...");	
		video.setState(new StatePlay());			
	}
	/**
	 * La m�thode affiche une message pour avertir le changement d'�tat vers stop. 
	 * @param: la video � changer.
	 */
	@Override
	public void PassToStop(Video video) 
	{
		video.setState(new StateStop());
		System.out.println("STOP");	
		System.out.println("(�TAT ACTUELE:) :"+video.state.toString()+"\n");	
	}
	/**
	 * La m�thode affiche une message pour avertir le changement d'�tat vers record. 
	 * @param: la video � changer.
	 */
	@Override
	public void PassToRecord(Video video) {
		video.setState(new StateRecord());	
		System.out.println("EECORDING...");	
		System.out.println("(�TAT ACTUELE:) :"+video.state.toString()+"\n");	
	}
}//fin class
