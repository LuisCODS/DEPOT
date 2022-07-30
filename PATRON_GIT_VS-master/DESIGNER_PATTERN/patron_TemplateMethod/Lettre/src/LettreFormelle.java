package Lettre.src;


public class LettreFormelle extends Lettre {

	LettreFormelle(String corps,ToFrom toFrom)
	{
		super(corps, toFrom);
	}
	
	@Override
	public void Appellation() {
		System.out.println("Mme. "+ this.toFrom.getTo());
	}
	@Override
	public void Formulefinale() {
		System.out.println("Mes salutations les plus distinguées");
	}
}
