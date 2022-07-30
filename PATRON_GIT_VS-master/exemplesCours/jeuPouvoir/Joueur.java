package jeuPouvoir;

public class Joueur  {

	//CHAMP
	protected Pouvoir pouvoirStrategy= null;	
	protected JoueurState state = null;
	

	public Joueur(Pouvoir pouvoir)
	{
		setPouvoir(pouvoir);
	}

//===================MÉTHODES =======================================
    public void pouvoirFeu() 
    {
/*    	setPouvoir(new PouvoirFeu());
    	//le joueur se transforme en dragon
    	*/
    	pouvoirStrategy.TranformerEnDragon(this);
    }
    public void pouvoirInvisible() 
    {
        // TODO implement here
    }
    public void pouvoirVoler() 
    {
        // TODO implement here
    }

    
  //=================== GET & SET =======================================

	public Pouvoir getPouvoir() {
		return pouvoirStrategy;
	}
	public void setPouvoir(Pouvoir pouvoir) {
		this.pouvoirStrategy = pouvoir;
	}
	public void setState(JoueurState state) {
		this.state = state;
	}	

}//fin class