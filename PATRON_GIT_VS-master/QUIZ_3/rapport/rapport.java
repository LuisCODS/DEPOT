package rapport;


	
	public class rapport{
		
		String name;
		int nbreDePage;
		
		rapport(String name,int nbreDePage)
		{
			this.name=name;
			this.nbreDePage=nbreDePage;
		}
		
		void printPrinter(String CombienDePageAimprimer){
			
			System.out.println("ok.je vais imprimer le rapport en papier ");
			
			if(ChercherImprimante()) 
				switch (CombienDePageAimprimer){
				case "All": printAllPrinter();
				case "CurrentPage": printCurrentPrinter();
				case "FromPageToPage": printFromToPrinter(5,10);}
						
			}
			
		void printPDF(String CombienDePageAimprimer){
			System.out.println("ok.je vais imprimer le rapport en format PDF");
			int from=2; int to=13;
			String chemin="mondossier";
			switch (CombienDePageAimprimer){	
				case "All": {save(printAllPDF(),chemin);}
				case "CurrentPage": {save(printcurrentpagePDF(),chemin); }
				case "FromPageToPage": {from=12;to=20;save(printFromPagetoPagePDF(from,to),chemin);}
			}
			
		}
	
		String printcurrentpagePDF(){
			String data="current page";
			System.out.println("j'imprime page courrante en format pdf");
			return data;
		}
		
		String printAllPDF(){
			System.out.println("j'imprime tout le doc en format pdf");
		String data="All pages";
		return data;
	}
		
		String printFromPagetoPagePDF(int from,int to)
		{String data="fromto";
		if(to>from && to<nbreDePage) System.out.println("Lancement d'impression de"+from +"a"+to);
		return data;
		}	
		
		
		void save(String data, String chemin)
		{
			System.out.println("saving data"+data);
		}
		
		
		
		boolean ChercherImprimante()
		{
		System.out.println("Je cherche une imprimante");
		return true;
		}
		
		void printAllPrinter(){System.out.println(" printing all printer");}
		void printCurrentPrinter(){System.out.println(" printing current page printer");}
		void printFromToPrinter(int from,int to){System.out.println(" printing printer from"+from+"to"+to);}
		
		
	}


