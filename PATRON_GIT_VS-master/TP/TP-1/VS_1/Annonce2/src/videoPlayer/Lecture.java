package videoPlayer;

public class Lecture extends Strategy {

	
	/**
	 * La m�thode jouer la video. 
	 * @param video: la video;
	 * @param strategy: lecture
	 */
	@Override
	public void play(Video video, Strategy strategy)
	{		
		video.state.PassToPlay(video);			
	}	
	/**
	 * La m�thode met la video en mode pause.
	 * @param : la video.
	 * @param strategy: lecture.
	 */
	@Override
	public void pause(Video video, Strategy strategy) {
		video.state.PassToPause(video);	
	}
	/**
	 * La m�thode fait avancer la video.
	 * @param : la video.
	 * @param strategy: lecture.
	 */
	@Override
	public void avancer(Video video, Strategy strategy) {
		video.state.PassToAvancer(video);	
	}
	/**
	 * La m�thode fait avancer la video.
	 * @param : la video.
	 * @param strategy: lecture.
	 */
	@Override
	public void reculer(Video video, Strategy strategy) {
		video.state.PassToReculer(video);	
	}
	/**
	 * La m�thode stop la video.
	 * @param video: la Video � stoper;
	 * @param strategy: lecture.
	 */
	@Override
	public void stop(Video video, Strategy strategy) {
		video.state.PassToStop(video);		
	}	
	/**
	 * La m�thode empeche l'enregistrement de la video en mode lecture.
	 * @param video: la Video;
	 * @param strategy: lecture.
	 */
	@Override
	public void record(Video video, Strategy strategy) 
	{			
		System.out.println("NE CONCERNE PAS!");	    				
	}	
	
}// fin class