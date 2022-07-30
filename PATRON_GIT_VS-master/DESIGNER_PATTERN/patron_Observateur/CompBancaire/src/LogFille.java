package Projet_CompteBancaire.src;
public class LogFille implements IObservateur{
	
	private static LogFille instance = null;
	CompteBancaire compte = null;

	private LogFille() {    }
	
	public static LogFille getInstance()
	{
		//si pas crée
		if(instance == null)
		{
			instance = new LogFille();//Créée ne nouvelle		
		}
		//Retourne l'exitente
		return instance;		
	}

	@Override
	public void UpDateMe(Object o) {
		// TODO Auto-generated method stub
		
	}
	
}
