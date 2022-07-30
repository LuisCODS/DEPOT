package jeuPouvoir;

public class StateVole extends JoueurState {

	@Override
	public void TranformerEnDragon(Joueur joueur) {
		System.out.println(" PAS CONCERN�! ");	
	}
	@Override
	public void DevenirInvisible(Joueur joueur) {
		System.out.println(" PAS CONCERN�! ");	
	}
	@Override
	public void Voler(Joueur joueur) {
		System.out.println("�tat : vole");
		joueur.setState(new StateVole());			
	}

}