package telecommandeSOLUTION;

public  class Device {
	
	
	State state;
	
	public Device(State state)
	{
		this.state = state;
	}
	
	
	
	
	public State getState() {
		return state;
	}

	public void setState(State state) {
		this.state = state;
	}

	 public void turnon()
	 {
		 state.allumer(this);
	 }
	 public void turnoff()
	 {
		 state.eteindre(this);
	 }

}
