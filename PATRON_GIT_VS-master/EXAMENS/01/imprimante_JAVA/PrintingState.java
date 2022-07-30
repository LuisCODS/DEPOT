package imprimante;

public class PrintingState extends State {

	
	private boolean cancel = false;
	
	@Override
	public void GoToReady(Imprimante i) {
		System.out.print("NE CONCERNE PAS !");		
	}

	@Override
	public void ReadyToStart(Imprimante i) {
		System.out.print("NE CONCERNE PAS !");		
	}

	@Override
	public void PrintingDonne(Imprimante i) {
		
		if (cancel == false) 
		{
			System.out.print("End...");
			i.setState(new EndState());	
			
			System.out.print("Ready");
			i.setState(new ReadyState());
		}
	}

	@Override
	public void Cancel(Imprimante i) {
		System.out.print("End...");
		this.cancel = true;
		i.setState(new EndState());	
		
		System.out.print("Ready");
		i.setState(new ReadyState());
	}
	@Override
	public void ReadyToPrint(Imprimante i) {
		System.out.print("NE CONCERNE PAS !");
		
	}

	
	
}
