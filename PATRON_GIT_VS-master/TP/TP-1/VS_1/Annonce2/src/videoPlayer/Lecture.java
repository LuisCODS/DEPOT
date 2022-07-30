package videoPlayer;

public class Lecture extends Strategy {

	
	/**
	 * La méthode jouer la video. 
	 * @param video: la video;
	 * @param strategy: lecture
	 */
	@Override
	public void play(Video video, Strategy strategy)
	{		
		video.state.PassToPlay(video);			
	}	
	/**
	 * La méthode met la video en mode pause.
	 * @param : la video.
	 * @param strategy: lecture.
	 */
	@Override
	public void pause(Video video, Strategy strategy) {
		video.state.PassToPause(video);	
	}
	/**
	 * La méthode fait avancer la video.
	 * @param : la video.
	 * @param strategy: lecture.
	 */
	@Override
	public void avancer(Video video, Strategy strategy) {
		video.state.PassToAvancer(video);	
	}
	/**
	 * La méthode fait avancer la video.
	 * @param : la video.
	 * @param strategy: lecture.
	 */
	@Override
	public void reculer(Video video, Strategy strategy) {
		video.state.PassToReculer(video);	
	}
	/**
	 * La méthode stop la video.
	 * @param video: la Video à stoper;
	 * @param strategy: lecture.
	 */
	@Override
	public void stop(Video video, Strategy strategy) {
		video.state.PassToStop(video);		
	}	
	/**
	 * La méthode empeche l'enregistrement de la video en mode lecture.
	 * @param video: la Video;
	 * @param strategy: lecture.
	 */
	@Override
	public void record(Video video, Strategy strategy) 
	{			
		System.out.println("NE CONCERNE PAS!");	    				
	}	
	
}// fin class