package videoPlayer;

/**
 * Classe qui initialise un Video avec un �tat Initial:
 * permet aux deux strat�gies de faire leur switch. 
 */
public class Video {

	State state = null;	
	
	public Video ()
	{
		setState(new StateInitial());
	}	
	

	public void setState(State state) {
		this.state = state;
	}	
	public State getState() {
		return state;
	}
		
}//fin class
