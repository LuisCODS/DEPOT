package pizzafactory;

public abstract class Pizza {
	
	String pâte;
	String sauce;
	String garniture;
	public Pizza() {

		this.pâte = "patte pizza";
		this.sauce = "tomate";
	
	}
	
	void prepare (){System.out.println("preparing");}
	void bake(){System.out.println("baking");}
	void cut(){System.out.println("cuting");}
	void box(){System.out.println("boxing");}

}
