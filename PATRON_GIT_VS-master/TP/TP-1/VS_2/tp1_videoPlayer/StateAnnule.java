package tp1_videoPlayer;

public class StateAnnule extends State {

	@Override
	public void PassToPlay(Video video) {
		System.out.println("NE CONCERNE PAS!");	
		
	}

	@Override
	public void PassToPause(Video video) {
		System.out.println("NE CONCERNE PAS!");	
		
	}

	@Override
	public void PassToStop(Video video) {
		System.out.println("NE CONCERNE PAS!");	
		
	}

	@Override
	public void PassToAvancer(Video video) {
		System.out.println("NE CONCERNE PAS!");	
		
	}

	@Override
	public void PassToReculer(Video video) {
		System.out.println("NE CONCERNE PAS!");	
		
	}

	@Override
	public void PassToAnnule(Video video) {
		System.out.println("LA VIDEO A ÉTÉ ANNULÉE");	
		
	}

	@Override
	public void PassToEnregistre(Video video) {
		System.out.println("LA VIDEO A ÉTÉ ANNULÉE");	
		
	}

	@Override
	public void PassToRecord(Video video) {
		System.out.println("LA VIDEO A ÉTÉ ANNULÉE");	
		
	}

}
