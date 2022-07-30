package gumBallSolution;

public class Sold extends StateGanball {

	/**
	 * @Description: cette méthode permet d'ajouter de l'argent.
	 * @param gumBall
	 */
	@Override
	public void insertQuarter(GumBall gumBall) {
		System.out.println("Ne concerne pas");
	}
	/**
	 * @Description: cette méthode permet de tourner pour faire sortir le bonbon.
	 * @param gumBall
	 */
	@Override
	public void turnCrank(GumBall gumBall) {
		System.out.println("Ne concerne pas");
	}
	/**
	 * @Description: cette méthode permet de retirer l'argent.
	 * @param gumBall
	 */
	@Override
	public void ejectQuarter(GumBall gumBall) {
		System.out.println("Ne concerne pas");
	}
	/**
	 * @Description: cette méthode met fin à l'aplication car il n'y a plus des bonbons.
	 * Il faut la Réapprovisionner.
	 * @param gumBall
	 */
	@Override
	public void dispense(GumBall gumBall) {
			
		if(gumBall.getTotalGumballs() > 0 )
		{
			gumBall.setState(new PasDeSous());	
			gumBall.setTotalGumballs();
			System.out.println("Veuillez introduire à nouveau l'argent.");			
		}				
		else {
			System.out.println("Pas de bonbon! Hors service.");
			gumBall.setState(new OutOfOrder());	
		}			
	}   

}//fin class