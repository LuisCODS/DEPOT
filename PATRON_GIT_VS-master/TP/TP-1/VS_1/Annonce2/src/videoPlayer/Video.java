package videoPlayer;

/**
 * Classe qui initialise un Video avec un état Initial:
 * permet aux deux stratégies de faire leur switch. 
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
