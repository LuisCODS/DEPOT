package payement_Strategy;
//CLASSE CONTEXT QUI POSSEDE UNE COMPOSITION POLIMORFIQUE DES MODES DE PAYMENT
public class Commande {

	private int Id;
	private Payement type;	
	
	public Commande(int id, Payement newType)	{
		this.Id=id;
		this.type = newType;
	}	
	public void doPayement(){
		type.Payer();
	}
}
