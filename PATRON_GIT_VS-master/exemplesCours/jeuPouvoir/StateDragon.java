
package jeuPouvoir;


public class StateDragon extends JoueurState {

	@Override
	public void TranformerEnDragon(Joueur joueur) {
		System.out.println("ÉEtat: Dragon");
		joueur.setState(new StateDragon());			
	}
	@Override
	public void DevenirInvisible(Joueur joueur) {
		System.out.println(" PAS CONCERNÉ! ");	
	}
	@Override
	public void Voler(Joueur joueur) {
		System.out.println(" PAS CONCERNÉ! ");	
	}

}