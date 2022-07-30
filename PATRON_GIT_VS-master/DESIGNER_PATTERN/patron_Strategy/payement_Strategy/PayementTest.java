package payement_Strategy;

public class PayementTest {

	public static void main(String[] args) {

		Commande cmd = new Commande(1, new CarteCredit());
		cmd.doPayement();
		
		Commande cmd2 = new Commande(1, new CarteDebit());
		cmd2.doPayement();		
	}
}
