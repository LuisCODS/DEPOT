package gumBallSolution;

public class AvecSous extends StateGanball {

	/**
	 * @Description: cette méthode permet d'ajouter de l'argent.
	 * @param gumBall
	 */
	@Override
	public void insertQuarter(GumBall gumBall) {
		System.out.println("Sorry, il y a deja l'argent dans la machine");		
	}
	/**
	 * @Description: cette méthode permet de tourner pour faire sortir le bonbon.
	 * @param gumBall
	 */
	
	@Override
	public void turnCrank(GumBall gumBall) {
		//Changement obligatoire d'état
		System.out.println("Veuillez retirer ton bonbon");
		gumBall.setState(new Sold() );
	}
	
	/**
	 * @Description: cette méthode permet de retirer l'argent.
	 * @param gumBall
	 */
	@Override
	public void ejectQuarter(GumBall gumBall) {
		//Changement obligatoire
		System.out.println("Veuillez ramasser l'argent");
		gumBall.setState(new PasDeSous() );
	}
	
	/**
	 * @Description: cette méthode met fin à l'aplication car il n'y a plus des bonbons.
	 * Il faut la Réapprovisionner.
	 * @param gumBall
	 */
	@Override
	public void dispense(GumBall gumBall) {
		System.out.println("Ne concerne pas");
	}



}//fin class