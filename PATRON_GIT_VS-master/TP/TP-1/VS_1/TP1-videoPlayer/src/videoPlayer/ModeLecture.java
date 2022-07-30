package videoPlayer;

public class ModeLecture implements StrategyMode {

	//CHAMPS
	protected State state = null;
	
	
	@Override
	public void pause(Video video, StrategyMode strategy) {
		// TODO Auto-generated method stub
		
	}

	@Override
	public void stop(Video video, StrategyMode strategy) {
		// TODO Auto-generated method stub
		
	}
	
	public void play(Video video, StrategyMode strategy) {
		// TODO Auto-generated method stub
		video.setState(new Stop());
		System.out.println("PLAY: ACTION INDISPONIBLE EN MODE ENREGISTREMENT: "
				+"\n" 
				+"(" +strategy.getClass()+ ")"
				+"\n"
				+ ""); 	
	}
	
	public void Avancer(Video video, StrategyMode strategy) {
		// TODO Auto-generated method stub
		
	}
	public void Reculer(Video video, StrategyMode strategy) {
		// TODO Auto-generated method stub
		
	}


	
}//fin class
