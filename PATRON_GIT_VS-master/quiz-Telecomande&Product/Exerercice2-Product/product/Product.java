
package product;

//CLASSE CONTEXT
public class Product {

	//CHAMPS
    private Type type = null;    

	//CONSTRUCTEUR
    public Product(Type t) 
    {
    	this.type = t;
    }


    //MÉTHODES
    public void Print(Iformat format) 
    {
        format.Print();
    }


	@Override
	public String toString() {
		return "Product : " + type.marque
							+ ", "+ type.price;
	}
  
    
   

}//FIN CLASS