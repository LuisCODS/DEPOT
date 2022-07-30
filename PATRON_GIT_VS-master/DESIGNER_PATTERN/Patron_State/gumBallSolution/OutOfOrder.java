package gumBallSolution;

public class OutOfOrder extends StateGanball {

	@Override
	public void insertQuarter(GumBall gumBall) {
		System.out.println("Ne concerne pas");		
	}
	@Override
	public void turnCrank(GumBall gumBall) {
		System.out.println("Ne concerne pas");	
	}
	@Override
	public void ejectQuarter(GumBall gumBall) {
		System.out.println("Ne concerne pas");	
	}
	@Override
	public void dispense(GumBall gumBall) {
		System.out.println("Ne concerne pas");	
	}

}