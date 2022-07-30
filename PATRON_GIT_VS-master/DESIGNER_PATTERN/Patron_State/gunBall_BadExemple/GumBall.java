package gunBall_BadExemple;

public class GumBall {
	
	
	
	final static int SOLD_OUT = 0;
	final static int NO_QUARTER = 1;
	final static int HAS_QUARTER = 2;
	final static int SOLD = 3;
				 int state = SOLD_OUT;
				 int count = 0;
	
	
	
	public GumBall (int count ) {
		this.count = count;
		if (count > 0) {
			state = NO_QUARTER;
		}
	}
	
	
	public void insertQuarter ( ) 
	{
		if (state == HAS_QUARTER) {
			System.out.println("Can’t insert another quarter");
		} else if (state == SOLD_OUT) {
			System.out.println("Can’t insert a quarter, the machine is sold out");
		} else if (state == SOLD) {
			System.out.println ("Please wait we are getting you a gumball");
		} else if (state == NO_QUARTER) {
			state = HAS_QUARTER;
			System.out.println("You inserted a quarter");
		}
	}

	public void ejectQuarter ( ){
		// code eject
	}
	public void turnCrank ( ) {// code turn
	}

	public void dispense (){// code dispense
	}
}


