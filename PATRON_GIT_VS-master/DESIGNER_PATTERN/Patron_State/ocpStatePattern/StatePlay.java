package ocpStatePattern;

public class StatePlay extends StateMP3Player{

	@Override
	public void handle(MP3Player mp3player) {
		System.out.println("Playing to standby");
		mp3player.setState(new StateStandBy());

	}

}
