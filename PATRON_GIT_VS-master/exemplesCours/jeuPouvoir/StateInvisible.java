package jeuPouvoir;

public class StateInvisible extends JoueurState {

	@Override
	public void TranformerEnDragon(Joueur joueur) {
		System.out.println(" PAS CONCERNÉ! ");	
	}

	@Override
	public void DevenirInvisible(Joueur joueur) {
		System.out.println("État : Invisible");
		joueur.setState(new StateInvisible());	}

	@Override
	public void Voler(Joueur joueur) {
		System.out.println(" PAS CONCERNÉ! ");	
	}

}