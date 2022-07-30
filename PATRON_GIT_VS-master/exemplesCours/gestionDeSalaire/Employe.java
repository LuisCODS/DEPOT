package gestionDeSalaire;

/**
 * @author: 		Luis SANTOS
 * @DateDeCreation: 14/12/2017
 * @Description: 	Cette classe permet de modeliser un type "Employe" afin de pouvoir.
 *              	créer certains types d'employés.
 */
public abstract class Employe {	

	// ============= ATTRIBUTS =============	
	protected int age = 0;
	protected String nom = "";
	protected String preNom = "";	
	protected String dateEntree ="" ;	
	
	
	
	// ============= CONSTRUCTEUR =============	
	public Employe( int age, String nom, String preNom, String dateEntree)
	{	
		this.age = age;
		this.nom = nom;
		this.preNom =  preNom;
		this.dateEntree = dateEntree;
	}
	
	
	
	// ============= MÉTHODE =============		
	/**
	 * @Description Cette méthode permet à chaque employe de calculer ses respectives salaires.
	 * @return: le salaire d'un employé.
	 */
	public abstract float calculerSalaire ();		
	

	/**
	 * @Description : Cette méthode permet de returner une chaine de caractère obtenue en concaténant 
	 * 				  la chaine de caractères "L'employé " avec le prénom et le nom.
	 * @return: une chaine de caractère.
	 */
	public String getNom()
	{
		return  "L'employe "+ this.preNom + " "+ this.nom;
	}
	
	
}//fin class
