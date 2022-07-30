package src;


public class Manutention extends Employe {

	// ============= ATTRIBUTS =============
	
	private int nbHeuresTravaillees = 0;
	private final int PRIME = 100;
	
	// ============= CONSTRUCTEUR =============
	
	public Manutention(int nbHeuresTravaillees, int age, String nom, String preNom, String dateEntree)
	{
		super(age, nom, preNom, dateEntree);
		this.nbHeuresTravaillees = nbHeuresTravaillees;
	}
	
	// ============= MÉTHODES =============
	
	@Override
	public String getNom()
	{
		return "Manutention : " + super.getNom();
	}
	
	@Override
	public float calculerSalaire() 
	{		
		return this.nbHeuresTravaillees * this.PRIME;
	}

}
