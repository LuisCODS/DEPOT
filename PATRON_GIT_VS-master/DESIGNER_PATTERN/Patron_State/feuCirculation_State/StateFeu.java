package feuCirculation_State;

abstract public class StateFeu {
	
	abstract public void rougeToVert(FeuCirculation fc);
	abstract public void orangeToRouge(FeuCirculation fc);
	abstract public void vertToOrange(FeuCirculation fc);
	

}
