package Compteur;

public class test {

	public static void main(String[] args) {
		Produit1 p1=new Produit1("lampe");
		Visiteur v= new Visiteur();
		v.visiter(p1);
		v.visiter(p1);
		//v.visiter(p2);
		
		CompteurIncrement.getinstance().AfficheCompteur();

	}

}
