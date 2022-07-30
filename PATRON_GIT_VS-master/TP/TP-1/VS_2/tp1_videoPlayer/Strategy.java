package tp1_videoPlayer;

public abstract class Strategy {

 	public abstract void play(Video video, Strategy strategy);
    public abstract void pause(Video video, Strategy strategy);
    public abstract void avancer(Video video, Strategy strategy);
    public abstract void reculer(Video video, Strategy strategy);
    public abstract void stop(Video video, Strategy strategy);    
	public abstract void record(Video video, Strategy strategy);
	

    
}//fin class