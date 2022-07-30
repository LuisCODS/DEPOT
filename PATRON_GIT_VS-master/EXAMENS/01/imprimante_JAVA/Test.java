package imprimante;

public class Test {

	public static void main(String[] args) {
		
		
		// VOUZ DEVEZ ESSAYER, CHAQUE APPEL DES MÉTHODES POUR L'INSTANCE IMPRIMANTE, 1 À LA FOIS!
		
		//TESTE 1 TOUT EST NORMAL
		Imprimante ip1 = new Imprimante(new ReadyState());
		//ip1.Print();	
		//ip1.Cancel();
		//ip1.ReadyToPrint();
		//ip1.GoToReady();
		//ip1.PrintingDonne();			
		Imprimante ip2 = new Imprimante(new StartState());
		//ip2.Print();	
		//ip2.Cancel();
		//ip2.ReadyToPrint();
		//ip2.GoToReady();
		//ip2.PrintingDonne();
		
		Imprimante ip3 = new Imprimante(new PrintingState());
		//ip3.Print();	
		//ip3.Cancel();
		//ip3.ReadyToPrint();
		//ip3.GoToReady();
		//ip3.PrintingDonne();
		
		Imprimante ip4 = new Imprimante(new EndState());
		//ip4.Print();	
		//ip4.Cancel();
		//ip4.ReadyToPrint();
		//ip4.GoToReady();
		//ip4.PrintingDonne();
		
	
		
	}
}
