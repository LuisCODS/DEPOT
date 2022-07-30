package state_FeuCirculation;

/**
 * @author exemple prof
 *
 */

public class FeuCirculationContext {
	
	//COMPOSITION
	EtatFeu etatFeu;

	//CONSTRUCTEUR
	public FeuCirculationContext(EtatFeu etatFeuCourrant)
	{
		this.etatFeu = etatFeuCourrant;
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
	public EtatFeu getEtatfeu()	{
		return etatFeu;
	}
	public void setEtatFeu(EtatFeu nouveauEtat)	{
		this.etatFeu = nouveauEtat;
	}

}
