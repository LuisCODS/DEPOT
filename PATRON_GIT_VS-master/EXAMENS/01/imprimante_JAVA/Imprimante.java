package imprimante;

public class Imprimante {

	
	State state= null;
	
	
	public Imprimante(State state)
	{
		this.state = state;
	}	
	
	
	public void Print()
	{
		//ready to start
		state.ReadyToStart(this);
	}
	public void Cancel()
	{
		//START TO END ou PRITING TO END
		state.Cancel(this);		
	}
	
	public void ReadyToPrint()
	{
		//START TO PRINTING
		state.ReadyToPrint(this);
	}
	public void GoToReady()
	{
		// END TO READY
		state.GoToReady(this);
	}


	public void PrintingDonne()
	{
		//PRITING TO END
		state.PrintingDonne(this);
	}

	public void setState(State state) {
		this.state = state;
	}
	
	
	
}
