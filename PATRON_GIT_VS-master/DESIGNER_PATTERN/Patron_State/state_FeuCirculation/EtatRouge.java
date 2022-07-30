package state_FeuCirculation;

public class EtatRouge extends EtatFeu{

	
	public void rougeToVert(FeuCirculationContext fc) {
		System.out.println("Ok, je passe du rouge à vert");
		fc.setEtatFeu(new EtatVert());		
	}	
	public void orangeToRouge(FeuCirculationContext fc) {
		System.out.println("je suis deja en etat rouge.");	
		
	}	
	public void vertToOrange(FeuCirculationContext fc) {
		System.out.println("Pas concerne.Je suis rouge!");		
	}	
		
	

}
