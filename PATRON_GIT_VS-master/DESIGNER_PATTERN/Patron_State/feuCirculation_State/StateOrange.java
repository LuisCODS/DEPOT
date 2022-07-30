package feuCirculation_State;

public class StateOrange extends StateFeu {

	public void rougeToVert(FeuCirculation fc) {
		System.out.println("Pas conerne. Je suis orange !");		
	}
	
	public void orangeToRouge(FeuCirculation fc) {
		System.out.println("Ok, je passe d'orange à rouge");
		fc.setEtatFeu(new StateRouge());		
	}
	
	public void vertToOrange(FeuCirculation fc) {
		System.out.println("Je suis deja en etat orange");
		
	}

}
