package ocpStatePattern;

public class MP3Player {

	//CHAMP
	StateMP3Player state;

	
	//CONSTRUCTEUR
	public MP3Player(StateMP3Player state) {
		this.state = state;
	}

	
	//MÉTHODES
	public StateMP3Player getState() {
		return state;
	}

	public void setState(StateMP3Player state) {
		this.state = state;
	}
	
	public void ChangerEtat(){
		state.handle(this);
	}
	
}
