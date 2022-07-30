package feuCirculation_State;

public class StateRouge extends StateFeu{

	
	public void rougeToVert(FeuCirculation fc) {
		System.out.println("Ok, je passe du rouge à vert");
		fc.setEtatFeu(new StateVert());		
	}	
	public void orangeToRouge(FeuCirculation fc) {
		System.out.println("je suis deja en etat rouge.");	
		
	}	
	public void vertToOrange(FeuCirculation fc) {
		System.out.println("Pas concerne.Je suis rouge!");		
	}	
		
	

}
