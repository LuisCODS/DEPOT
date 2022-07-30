package videoPlayer;

/**
 * Cette classe sert de swuite entre les deux strategies. 
 * Cela permet aux deux strat�gies de r�aliser leurs impl�mentations qui leur concerne.
 *
 */
public class StateInitial extends State{

	/**
	 * Change l'�tat de la video � Play.
	 * @param: la video dont l'�tat doit �tre mis � jours.
	 */
	@Override
	public void PassToPlay(Video video) {
		video.setState(new StatePlay() );
		System.out.println("PLAY...");	
/*		System.out.println("(�TAT ACTUELE:) :"+video.state.toString()+"\n");	
*/	}
	/**
	 * Change l'�tat de la video � Record.
	 * @param: la video dont l'�tat doit �tre mis � jours.
	 */
	@Override
	public void PassToRecord(Video video) {
		video.setState(new StateRecord() );
		System.out.println("RECORDING...");	
		System.out.println("(�TAT ACTUELE:) :"+video.state.toString()+"\n");
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
