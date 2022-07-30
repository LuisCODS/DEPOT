package mp3Player;

public class StateStandBy extends State{


	public void handle(MP3Player mp3player) {
		System.out.println("Stand By to play");
		mp3player.setState(new StatePlay());
		
	}
	
	

}
