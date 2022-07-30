package Compteur;

public class Visiteur {
	
	public void visiter(Produit1 o)
	{
		System.out.println("je visite"+o.toString());
		CompteurIncrement.getinstance().incrementCompteur();
	}

}
