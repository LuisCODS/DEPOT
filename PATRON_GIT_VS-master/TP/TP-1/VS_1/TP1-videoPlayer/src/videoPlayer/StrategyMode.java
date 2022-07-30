package videoPlayer;

public interface StrategyMode {
    
     public void play(Video video, StrategyMode strategy);    
     public void pause(Video video, StrategyMode strategy);
     public void stop(Video video, StrategyMode strategy);    
    
}//fin class