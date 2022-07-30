package gumBallSolution;
/**
 * @author Luis Santos
 * @DateDeCreation: 23/01/2018
 * @Description: Cette application permet de creer un GumBall
 */
public class ClientTest {

	public static void main(String[] args) {
	
		
	//On commence avec 100 bunbun et avec l'état NoQuater par defaut
	GumBall gumBall = new GumBall(100);
	gumBall.ajouterCash();
	//gumBall.dispense();
	gumBall.tourner();	
		
	}

}