package ocpStatePattern;

public class StateStandBy extends StateMP3Player{

	@Override
	public void handle(MP3Player mp3player) {
		System.out.println("Stand By to play");
		mp3player.setState(new StatePlay());
		
	}

}
