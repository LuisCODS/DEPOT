package pizzafactory;

public abstract class Pizza {
	
	String p�te;
	String sauce;
	String garniture;
	public Pizza() {

		this.p�te = "patte pizza";
		this.sauce = "tomate";
	
	}
	
	void prepare (){System.out.println("preparing");}
	void bake(){System.out.println("baking");}
	void cut(){System.out.println("cuting");}
	void box(){System.out.println("boxing");}

}
