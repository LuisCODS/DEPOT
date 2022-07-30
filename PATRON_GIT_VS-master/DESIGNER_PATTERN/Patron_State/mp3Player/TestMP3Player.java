package mp3Player;

public class TestMP3Player {

	public static void main(String[] args) {
		// TODO Auto-generated method stub
		
		MP3Player mp3player= new MP3Player(new StateStandBy());
		mp3player.ChangerEtat();
		mp3player.ChangerEtat();
		
		

	}

}
