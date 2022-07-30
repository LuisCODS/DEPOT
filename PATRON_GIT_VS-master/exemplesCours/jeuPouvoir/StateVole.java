package jeuPouvoir;

public class StateVole extends JoueurState {

	@Override
	public void TranformerEnDragon(Joueur joueur) {
		System.out.println(" PAS CONCERNÉ! ");	
	}
	@Override
	public void DevenirInvisible(Joueur joueur) {
		System.out.println(" PAS CONCERNÉ! ");	
	}
	@Override
	public void Voler(Joueur joueur) {
		System.out.println("État : vole");
		joueur.setState(new StateVole());			
	}

}