package compressionImage_Strategy;

/**
 * 
 */
public class Image_Contexte {

	//CHAMP
    private IformatStrategy typeFormat;

    
    /**
     * Constructor: permet de bat�r le type de format initial.
     */
    public Image_Contexte(IformatStrategy typeImage)
    {
    	this.typeFormat = typeImage;
    	//OU setType(typeImage)
    }


    // M�THODES 
    /**
     * @param type: c'est le format � �tre compress�.
     */
    public void Compression()
    {
        typeFormat.Compression();
    }
    
    // GETS & SETS
	public IformatStrategy getType() {
		return typeFormat;
	}
	public void setType(IformatStrategy type) {
		this.typeFormat = type;
	}  
    
}//FIN CLASS