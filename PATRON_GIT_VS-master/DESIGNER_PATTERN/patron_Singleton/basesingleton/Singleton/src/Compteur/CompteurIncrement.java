package Compteur;

public class CompteurIncrement {
	private static CompteurIncrement instance = new CompteurIncrement();
	private int compteur=0;
	private CompteurIncrement(){
		System.out.println("test compteur singleton");
	}
	public static CompteurIncrement getinstance()
	{
		return instance;
	}
	
	public void incrementCompteur(){compteur++ ;}
	public void AfficheCompteur(){System.out.println(" compteur ="+compteur);}
	

}
