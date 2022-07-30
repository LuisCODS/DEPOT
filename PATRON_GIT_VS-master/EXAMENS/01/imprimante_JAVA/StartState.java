package imprimante;

public class StartState extends State {


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
		System.out.print("NE CONCERNE PAS !");
		
	}

	@Override
	public void Cancel(Imprimante i) {
		System.out.print("End...");
		i.setState(new EndState());
		
		this.cancel= true;
		
		System.out.print("Ready...");
		i.setState(new ReadyState());
		
	}
	@Override
	public void ReadyToPrint(Imprimante i)
	{				
		if (this.cancel == false) 
		{
			System.out.print("Printing...");
			i.setState(new PrintingState());
			
			System.out.print("End...");
			i.setState(new EndState());	
			
			System.out.print("Ready...");
			i.setState(new ReadyState());
		}
	
	}

	
	
}
