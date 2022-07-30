package imprimante;

public class EndState extends State {

	@Override
	public void GoToReady(Imprimante i) {
		System.out.print("Ready...");
		i.setState(new ReadyState());		
	}

	@Override
	public void ReadyToStart(Imprimante i) {
		System.out.print("NE CONCERNE PAS !");
		
	}

	@Override
	public void PrintingDonne(Imprimante i) {
		System.out.print("NE CONCERNE PAS !");
		
	}

	@Override
	public void Cancel(Imprimante i) {
		System.out.print("NE CONCERNE PAS !");
		
	}

	@Override
	public void ReadyToPrint(Imprimante i) {
		System.out.print("NE CONCERNE PAS !");
		
	}

	
	
}
