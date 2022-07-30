
package jeuPouvoir;


public class StateDragon extends JoueurState {

	@Override
	public void TranformerEnDragon(Joueur joueur) {
		System.out.println("�Etat: Dragon");
		joueur.setState(new StateDragon());			
	}
	@Override
	public void DevenirInvisible(Joueur joueur) {
		System.out.println(" PAS CONCERN�! ");	
	}
	@Override
	public void Voler(Joueur joueur) {
		System.out.println(" PAS CONCERN�! ");	
	}

}