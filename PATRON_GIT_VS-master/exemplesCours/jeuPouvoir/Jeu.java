
package jeuPouvoir;

public class Jeu {
	
	protected Joueur jouer = null;
    
	
    /**
     * Au d�but d�un jeu, le joueur a le droit de choisir 
     * un des pouvoir pour combattre les ennemis 
     */
    public Jeu(Joueur  jouer) 
    {
    	this.jouer = jouer;
    }

  //===================M�THODES =======================================
    public void pouvoirFeu() 
    {
    	jouer.pouvoirFeu();
    }
    public void pouvoirInvisible() 
    {
        // TODO implement here
    }
    public void pouvoirVoler() 
    {
        // TODO implement here
    }
    
    
}//fin class