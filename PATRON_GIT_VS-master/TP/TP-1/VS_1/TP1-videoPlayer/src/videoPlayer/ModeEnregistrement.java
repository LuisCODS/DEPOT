/*package videoPlayer;

import java.util.Scanner;

public class ModeEnregistrement extends StrategyMode {
	
	//CHAMPS
	protected State state = null;
    Scanner s = new Scanner(System.in);
    private int choix = 0;
    
    
	*//**
	 * La m�thode change l'�tat de la video � Pause.
	 * @param: la video dont l'�tat doit �tre mis � jours.
	 *//*
	@Override
	public void pause(Video video, StrategyMode strategy) 
	{	
		//si la video est en mode enregistrement...
		if (video.getState().equals(new StateRecord())) {
			//video.state.PassToPause(video);
		}else
			System.out.println("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");	
	}	
	*//**
	 * La m�thode arrete l'enregistrement en proposant
	 * � l'utilisateur d'enregistrer ou d'annuler la video avant d'arreter.
	 * @param video: la video;
	 * @param strategy: enregistrement 
	 *//*
	@Override
	public void stop(Video video, StrategyMode strategy) 
	{		
		System.out.println("POUR ARR�TER LA VID�O VEUILLEZ CHOISIR :");	
		System.out.println("(1) POUR LE SAUVEGARDER OU (2) POUR ANNULER L'ENREGISTREMENT EN COURS!"+"\n");			
		
		choix = s.nextInt();
		
		switch(choix)
		{
			case 1: //video est enregistr�e
				video.state.PassToEnregistre(video);
				break;
			case 2: //pas enregistr�
				video.state.PassToAnnule(video);
				break;
		}
	}
	*//**
	 * La m�thode fait  l'enregistrement de la video.
	 * @param video: la Video;
	 * @param strategy: enregistrement.
	 *//*
	public void record(Video video, StrategyMode strategy)
	{
		video.state.PassToRecord(video);
	}
	


}// fin class*/