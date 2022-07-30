package gumBallSolution;

public class PasDeSous extends StateGanball {
	
	/**
	 * @Description: cette méthode permet d'ajouter de l'argent.
	 * @param gumBall
	 */
	@Override
	public void insertQuarter(GumBall gumBall) {
		//Changement obligatoire
		System.out.println("Ok, Cash inserted!");
		gumBall.setState(new AvecSous());		
	}
	@Override
	public void turnCrank(GumBall gumBall) {
		System.out.println("Pas d'argent, veuillez inserer S.V.P");
	}
	@Override
	public void ejectQuarter(GumBall gumBall) {
		System.out.println("Vous n'avaez pas d'argent");
	}
	@Override
	public void dispense(GumBall gumBall) {
		System.out.println("Ne concerne pas");
	}

}