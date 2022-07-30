package state_FeuCirculation;

abstract public class EtatFeu {
	
	abstract public void rougeToVert(FeuCirculationContext fc);
	abstract public void orangeToRouge(FeuCirculationContext fc);
	abstract public void vertToOrange(FeuCirculationContext fc);
	

}
