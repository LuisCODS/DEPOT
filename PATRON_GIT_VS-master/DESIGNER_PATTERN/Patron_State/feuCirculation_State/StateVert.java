package feuCirculation_State;

public class StateVert extends StateFeu {

	
	public void rougeToVert(FeuCirculation fc) 	{
		System.out.println("je suis deja en etat vert");
	}
	
	public void orangeToRouge(FeuCirculation fc) {
		System.out.println("Pas concern�. Je suis vert!");				
	}	
	
	public void vertToOrange(FeuCirculation fc){
		System.out.println("Ok, je passe du vert � orange");
		fc.setEtatFeu(new StateOrange());
	}	
	
}
