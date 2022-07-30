package bedExempleAvion;

public class TestAvion {

	public static void main(String[] args) {
		
		StateAvion stateAvion = StateAvion.EnlAir;

		Avion avion = new Avion(stateAvion);
		avion.doAction();



	}

}
