package gestionDeSalaire;

public  class Salaires {


	// ================ MAIN ================ 
	public static void main(String[] args) {
		
		
		
		Vendeurs emp1 = new Vendeurs
		(				
				900.00f,
				35, 
				"Marc", 
				"Santos",
				"22-08-1983"
		);		
		// ----------------------------------------------------
		System.out.println(emp1.getNom());
		System.out.println("");
		System.out.println(emp1.calculerSalaire() );
						
		ManutentionARisque employerARisque = new ManutentionARisque
		(	
			40,
			35, 
			"Robert", 
			"Olivier",
			"22-08-1983"
		);
	
		System.out.println(employerARisque.getNom());
		System.out.println("");
		System.out.println(employerARisque.calculerSalaire() );
		// ----------------------------------------------------
	
	
	}// FIN MAIN
		
}// FIN CLASS
