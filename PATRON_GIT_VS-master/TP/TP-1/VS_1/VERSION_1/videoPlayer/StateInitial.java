package videoPlayer;

/**
 * Cette classe sert de swuite entre les deux strategies. 
 * Cela permet aux deux stratégies de réaliser leurs implémentations qui leur concerne.
 *
 */
public class StateInitial extends State{

	/**
	 * Change l'état de la video à Play.
	 * @param: la video dont l'état doit être mis à jours.
	 */
	@Override
	public void PassToPlay(Video video) {
		video.setState(new StatePlay() );
		System.out.println("PLAY...");	
/*		System.out.println("(ÉTAT ACTUELE:) :"+video.state.toString()+"\n");	
*/	}
	/**
	 * Change l'état de la video à Record.
	 * @param: la video dont l'état doit être mis à jours.
	 */
	@Override
	public void PassToRecord(Video video) {
		video.setState(new StateRecord() );
		System.out.println("RECORDING...");	
		System.out.println("(ÉTAT ACTUELE:) :"+video.state.toString()+"\n");
	}
	@Override
	public void PassToPause(Video video) {
/*		System.out.println("NE CONCERNE PAS"+"\n");	
*/		
	}

	@Override
	public void PassToStop(Video video) {
		System.out.println("NE CONCERNE PAS"+"\n");	
		
	}

	@Override
	public void PassToAvancer(Video video) {
		System.out.println("NE CONCERNE PAS"+"\n");	
		
	}

	@Override
	public void PassToReculer(Video video) {
		System.out.println("NE CONCERNE PAS"+"\n");	
		
	}

	@Override
	public void PassToAnnule(Video video) {
		System.out.println("NE CONCERNE PAS"+"\n");	
		
	}

	@Override
	public void PassToEnregistre(Video video) {
		System.out.println("NE CONCERNE PAS"+"\n");	
		
	}

}
