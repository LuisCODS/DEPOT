package Lettre.src;


public class Test {

	public static void main(String[] args) {
		
		System.out.println("LETTRE FORMELLE "+"\n");	
		String corps1 = "Ce courriel a pour finalité d'avertir que je suis arrivé en paix";
		ToFrom luisToLayla = new ToFrom("Layla", "Luis");		
		Lettre lettre = new LettreFormelle(corps1,luisToLayla);		
		lettre.Pint();		
		System.out.println("____________________________________________________");
		System.out.println("LETTRE INFORMELLE "+"\n");			
		String corps2 = "Salut ma belle, je t'écrire pour raconter mon voyage au Brésil. Tu ne va pas croire ce que m'est arrivé...";
		ToFrom luisToAnne = new ToFrom("Anne", "Luis");		
		Lettre lettre2 = new LettreInformelle(corps2,luisToAnne);		
		lettre2.Pint();
		
	}//fin 

}
