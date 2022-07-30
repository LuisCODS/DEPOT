package imprimante;

public abstract class State {

	public abstract void GoToReady(Imprimante i);
	public abstract void ReadyToStart(Imprimante i);
	public abstract void ReadyToPrint(Imprimante i);
	public abstract void PrintingDonne(Imprimante i);
	public abstract void Cancel(Imprimante i);
	//public abstract void StartToEnd(Imprimante i);

	
}
