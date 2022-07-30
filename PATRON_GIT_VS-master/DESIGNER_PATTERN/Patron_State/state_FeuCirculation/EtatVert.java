package state_FeuCirculation;

public class EtatVert extends EtatFeu {

	
	public void rougeToVert(FeuCirculationContext fc) 	{
		System.out.println("je suis deja en etat vert");
	}
	
	public void orangeToRouge(FeuCirculationContext fc) {
		System.out.println("Pas concerné. Je suis vert!");				
	}	
	
	public void vertToOrange(FeuCirculationContext fc){
		System.out.println("Ok, je passe du vert à orange");
		fc.setEtatFeu(new EtatOrange());
	}	
	
}
