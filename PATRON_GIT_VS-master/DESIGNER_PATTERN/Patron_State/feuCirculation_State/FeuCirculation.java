package feuCirculation_State;

/**
 * @author Luis
 *CLASSE Context
 */
public class FeuCirculation {
	
	//CHAMP
	StateFeu etatFeu;

	//CONSTRUCTEUR
	public FeuCirculation(StateFeu state)
	{
		this.etatFeu = state;
	}
	
	// MÉTHODES
	public void rougeToVert()
	{
		etatFeu.rougeToVert(this);		
	}
	public void vertToOrange()
	{	
		etatFeu.vertToOrange(this);
	}
	public void orangeToRouge()
	{	
		etatFeu.orangeToRouge(this);
	}
	
	// GET & SET
	public StateFeu getEtatfeu()	{
		return etatFeu;
	}
	public void setEtatFeu(StateFeu nouveauEtat)	{
		this.etatFeu = nouveauEtat;
	}

}
