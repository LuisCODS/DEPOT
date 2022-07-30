package jeuPouvoir;

public class StateInvisible extends JoueurState {

	@Override
	public void TranformerEnDragon(Joueur joueur) {
		System.out.println(" PAS CONCERN�! ");	
	}

	@Override
	public void DevenirInvisible(Joueur joueur) {
		System.out.println("�tat : Invisible");
		joueur.setState(new StateInvisible());	}

	@Override
	public void Voler(Joueur joueur) {
		System.out.println(" PAS CONCERN�! ");	
	}

}