package imprimante;

public class ReadyState extends State {

	@Override
	public void GoToReady(Imprimante i) {
		System.out.print("NE CONCERNE PAS !");		
	}
	@Override
	public void ReadyToStart(Imprimante i) 
	{	
		
		System.out.print("Start...");
		i.setState(new StartState());
		
		System.out.print("Printing...");
		i.setState(new PrintingState());
		
		System.out.print("End...");
		i.setState(new EndState());
		
		System.out.print("Ready...");
		i.setState(new ReadyState());
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
