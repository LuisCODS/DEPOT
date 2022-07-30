package Lettre.src;


public class LettreInformelle extends Lettre {

	LettreInformelle(String corps,ToFrom toFrom)
	{
		super(corps, toFrom);		
	}

	@Override
	public void Appellation() {
		System.out.println("Chère "+ this.toFrom.getTo());
	}
	@Override
	public void Formulefinale() {
		System.out.println("Bisous ");		
	}
}
