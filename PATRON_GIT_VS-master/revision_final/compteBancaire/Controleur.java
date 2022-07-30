package compteBancaire;

public class Controleur implements Iobserver{
	
	private static Controleur instance;
	
	private Controleur(){System.out.println("creation d'un controleur");}
	
	public static Controleur getinstance()
	{
		if(instance==null)
			instance=new Controleur();
		return instance;
	
	}

	@Override
	public void NotifyMe(CompteBancaire c) {
		caisseDepot.getinstance().deposerArgent(c.getSolde());
		c.setSolde(0);
		System.out.println("le montant a ete retire du compte"+c.getNumero()+" et a ete depose dans le compte caisse depot"+caisseDepot.getinstance().getNumero());
		System.out.println("le compte doit etre desactive");
		c.getState().desactiver(c);
		System.out.println("le solde dans la caisse depot est"+caisseDepot.getinstance().getSolde());
		
		
	}
	
	
	
	

}
