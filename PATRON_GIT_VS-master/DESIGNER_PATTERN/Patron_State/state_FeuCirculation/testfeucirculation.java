package state_FeuCirculation;

public class testfeucirculation {
	
	/**
	 * @param args
	 */
	public static void main(String[] args) {
		
		// INSTANCE VERT
		FeuCirculationContext feuVert = new FeuCirculationContext(new EtatVert());
		feuVert.orangeToRouge();
		feuVert.rougeToVert();
		feuVert.vertToOrange();			
		feuVert.orangeToRouge();
		System.out.println("_______________________________________");

		// INSTANCE ROUGE
		FeuCirculationContext feuRouge = new FeuCirculationContext(new EtatRouge());
		feuRouge.vertToOrange();	
		feuRouge.orangeToRouge();
		feuRouge.rougeToVert();
		feuRouge.vertToOrange();	
		System.out.println("_______________________________________");
		
		// INSTANCE ORANGE
		FeuCirculationContext  feuOrange= new FeuCirculationContext(new EtatOrange());
		feuOrange.rougeToVert();
		feuOrange.vertToOrange();			
		feuOrange.orangeToRouge();
		feuOrange.rougeToVert();
		System.out.println("_______________________________________");
		
		
	}

}
