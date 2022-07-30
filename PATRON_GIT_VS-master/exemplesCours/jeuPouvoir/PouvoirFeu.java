package jeuPouvoir;

public class PouvoirFeu extends Pouvoir {

	@Override
	public void TranformerEnDragon(Joueur joueur) {
		// TODO le joueur se transforme en dragon
		joueur.setState(new StateDragon());
		joueur.state.TranformerEnDragon(joueur);
	}
	@Override
	public void DevenirInvisible(Joueur joueur) {
		// TODO Auto-generated method stub		
	}
	@Override
	public void Voler(Joueur joueur) {
		// TODO Auto-generated method stub		
	}

}