package compteBancaire;

public class StateActive extends StateCompte{

	public StateActive(){
		System.out.println("create active account");
		}
	public void desactiver(CompteBancaire c) {
		System.out.println("ok, je vais desactiver le compte");
		c.setState(new StateDesactive());
		
	}

}
