package mp3Player;

public class StatePlay extends State{

	
	public void handle(MP3Player mp3player) {
		System.out.println("Playing to standby");
		mp3player.setState(new StateStandBy());
		
	}
	
	
	

}
