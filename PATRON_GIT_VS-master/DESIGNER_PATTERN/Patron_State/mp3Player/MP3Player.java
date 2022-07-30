package mp3Player;

public class MP3Player {

	State state;

	
	public MP3Player(State state)
	{
		this.state = state;
	}
	
	

	public State getState() {
		return state;
	}
	public void setState(State state) {
		this.state = state;
	}	
	public void ChangerEtat(){
		state.handle(this);
	}	
}
