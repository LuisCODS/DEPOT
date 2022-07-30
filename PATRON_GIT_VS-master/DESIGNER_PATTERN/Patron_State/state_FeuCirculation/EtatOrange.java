package state_FeuCirculation;

public class EtatOrange extends EtatFeu {

	public void rougeToVert(FeuCirculationContext fc) {
		System.out.println("Pas conerne. Je suis orange !");		
	}
	
	public void orangeToRouge(FeuCirculationContext fc) {
		System.out.println("Ok, je passe d'orange à rouge");
		fc.setEtatFeu(new EtatRouge());		
	}
	
	public void vertToOrange(FeuCirculationContext fc) {
		System.out.println("Je suis deja en etat orange");
		
	}

}
