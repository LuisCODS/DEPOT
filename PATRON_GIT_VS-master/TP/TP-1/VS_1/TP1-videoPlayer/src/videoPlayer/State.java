package videoPlayer;

public abstract class State {

	//changement d'état possibles
	
 	public abstract void PassToPlay(Video video); 
 	public abstract void PassToPause(Video video); 
 	public abstract void PassToStop(Video video);
 	public abstract void PassToAvancer(Video video);
 	public abstract void PassToReculer(Video video);
 	public abstract void PassToAnnule(Video video);
 	public abstract void PassToEnregistre(Video video);
 	public abstract void PassToRecord(Video video); 

 	
}//FIN CLASS