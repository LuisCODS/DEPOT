package feuCirculation_State;

public class ClientTest {
	
	/**
	 * @param args
	 */
	public static void main(String[] args) {
		
		// INSTANCE VERT
		FeuCirculation feuVert = new FeuCirculation(new StateVert());
		feuVert.orangeToRouge();
		feuVert.rougeToVert();
		feuVert.vertToOrange();			
		feuVert.orangeToRouge();
		System.out.println("_______________________________________");

		// INSTANCE ROUGE
		FeuCirculation feuRouge = new FeuCirculation(new StateRouge());
		feuRouge.vertToOrange();	
		feuRouge.orangeToRouge();
		feuRouge.rougeToVert();
		feuRouge.vertToOrange();	
		System.out.println("_______________________________________");
		
		// INSTANCE ORANGE
		FeuCirculation  feuOrange= new FeuCirculation(new StateOrange());
		feuOrange.rougeToVert();
		feuOrange.vertToOrange();			
		feuOrange.orangeToRouge();
		feuOrange.rougeToVert();
		System.out.println("_______________________________________");
		
		
	}

}
