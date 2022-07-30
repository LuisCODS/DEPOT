package gestionDeSalaire;

/**
 * @author: 		Luis SANTOS
 * @DateDeCreation: 14/12/2017
 * @Description: 	Cette classe permet de modeliser un type "Employe" afin de pouvoir.
 *              	cr�er certains types d'employ�s.
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
	
	
	
	// ============= M�THODE =============		
	/**
	 * @Description Cette m�thode permet � chaque employe de calculer ses respectives salaires.
	 * @return: le salaire d'un employ�.
	 */
	public abstract float calculerSalaire ();		
	

	/**
	 * @Description : Cette m�thode permet de returner une chaine de caract�re obtenue en concat�nant 
	 * 				  la chaine de caract�res "L'employ� " avec le pr�nom et le nom.
	 * @return: une chaine de caract�re.
	 */
	public String getNom()
	{
		return  "L'employe "+ this.preNom + " "+ this.nom;
	}
	
	
}//fin class
