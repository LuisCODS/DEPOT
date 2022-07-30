
package product;

abstract public class Type {

	//CHAMPS
    public String marque ="" ;
	public double price = 0;

	
	//GET & SET
    public String getMarque() {
		return marque;
	}
	public void setMarque(String marque) {
		this.marque = marque;
	}
	public double getPrice() {
		return price;
	}
	public void setPrice(double price) {
		this.price = price;
	}
	
}