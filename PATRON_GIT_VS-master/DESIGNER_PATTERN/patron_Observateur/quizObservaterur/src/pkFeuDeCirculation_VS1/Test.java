package pkVoiture;

public class Test {

	public static void main(String[] args) {

		Voiture v = new Voiture();

		FeuDeCirculation feu = new FeuDeCirculation();
		feu.Add(v);
		feu.setState(new Rouge());
		System.out.println("_________________________________________________");
		feu.setState(new Vert());
		System.out.println("_________________________________________________");
		feu.setState(new Jaune());
		System.out.println("_________________________________________________");
	}

}
