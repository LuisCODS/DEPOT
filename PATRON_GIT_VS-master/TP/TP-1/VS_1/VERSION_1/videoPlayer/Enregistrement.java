package videoPlayer;

import java.util.Scanner;

public class Enregistrement extends Strategy {

	
	//CHAMPS
    Scanner s = new Scanner(System.in);
    private int choix = 0;

    
    
    
    
	/**
	 * La méthode change l'état de la video à Pause.
	 * @param: la video dont l'état doit être mis à jours.
	 */
	@Override
	public void pause(Video video, Strategy strategy) 
	{	
		//si la video est en mode enregistrement...
		if (video.getState().equals(new StateRecord())) {
			video.state.PassToPause(video);
		}else
			System.out.println("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");	
	}	
	/**
	 * La méthode arrete l'enregistrement en proposant
	 * à l'utilisateur d'enregistrer ou d'annuler la video avant d'arreter.
	 * @param video: la video;
	 * @param strategy: enregistrement 
	 */
	@Override
	public void stop(Video video, Strategy strategy) 
	{		
		System.out.println("POUR ARRÊTER LA VIDÉO VEUILLEZ CHOISIR :");	
		System.out.println("(1) POUR LE SAUVEGARDER OU (2) POUR ANNULER L'ENREGISTREMENT EN COURS!"+"\n");			
		
		choix = s.nextInt();
		
		switch(choix)
		{
			case 1: //video est enregistrée
				video.state.PassToEnregistre(video);
				break;
			case 2: //pas enregistré
				video.state.PassToAnnule(video);
				break;
		}
	}
	/**
	 * La méthode fait  l'enregistrement de la video.
	 * @param video: la Video;
	 * @param strategy: enregistrement.
	 */
	@Override
	public void record(Video video, Strategy strategy)
	{
		video.state.PassToRecord(video);
	}
	/**
	 * La méthode ne peut pas lire puisque le mode 
	 * enregistrement est en cours.
	 * @param video: la video;
	 * @param strategy: enregistrement
	 */
	@Override
	public void play(Video video, Strategy strategy) 
	{		
		System.out.println("PLAY: ACTION INDISPONIBLE EN MODE ENREGISTREMENT: "
				+"\n" 
				+"(" +strategy.getClass()+ ")"
				+"\n"
				+ ""); 		
	}
	/**
	 * 
	 */
	@Override
	public void avancer(Video video, Strategy strategy) {
		System.out.println("AVANCER: ACTION INDISPONIBLE EN MODE ENREGISTRMENT !");	
	}
	/**
	 * 
	 */
	@Override
	public void reculer(Video video, Strategy strategy) {
		System.out.println("RECULER: ACTION INDISPONIBLE EN MODE ENREGISTRMENT !");	
	}

}// fin class