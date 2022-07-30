package gumBallSolution;

public class GumBall {

	//CHAMPS
	private StateGanball stateGanball;
	private static int count = 0;
	private final static int SOLD = 3;
	
	/*
	 *CONSTRUCTEUR: le client decidra combien de gumball il aura au depart.
	 */
    public GumBall(int quantiteGumballs) 
    {
    	//No cash in the begin state
    	this.stateGanball = new PasDeSous();
    	//On commence avec 100 gumballs 
    	this.count = quantiteGumballs;
    }    
    
    //MÉTHODES
	public void ajouterCash()
	{
		stateGanball.insertQuarter(this);		
	}
	public void  tourner()
	{
		stateGanball.turnCrank(this);		
	}
	public void dispense()
	{
		stateGanball.ejectQuarter(this);		
	}
    
    
    // GETS & SETS
	public StateGanball getState() {
		return stateGanball;
	}
	public void setState(StateGanball newState) {
		this.stateGanball = newState;
	}
	public static int getTotalGumballs() {
		return count;
	}
	public static void setTotalGumballs() {
		//Mise à jours des bobons
		count = count - SOLD;
	}

  



}//FIN CLASS