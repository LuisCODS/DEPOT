package pkVoiture;

public class Voiture implements IObservateur {

	StateVoiture state = null;

	@Override
	public void upDate(Object o) 
	{
		if (o instanceof Rouge) // L'auto s'arrete
		{			
			this.setState(new Stop());
			ShowState();
		} else if (o instanceof Vert) // L'auto roule
		{
			this.setState(new Run());
			ShowState();
		} else if (o instanceof Jaune) // L'auto pert sa vitesse
		{
			this.setState(new Attention());
			ShowState();
		}
	}

	private void ShowState() {
		System.out.println("ÉTAT ACTUEL DE LA VOITURE : " + this.getState().getClass()+"\n");
	}

	public StateVoiture getState() {
		return state;
	}

	public void setState(StateVoiture state) {
		this.state = state;
	}

}