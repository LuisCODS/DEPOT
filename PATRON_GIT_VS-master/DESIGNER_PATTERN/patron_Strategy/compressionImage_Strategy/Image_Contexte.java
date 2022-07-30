package compressionImage_Strategy;

/**
 * 
 */
public class Image_Contexte {

	//CHAMP
    private IformatStrategy typeFormat;

    
    /**
     * Constructor: permet de batîr le type de format initial.
     */
    public Image_Contexte(IformatStrategy typeImage)
    {
    	this.typeFormat = typeImage;
    	//OU setType(typeImage)
    }


    // MÉTHODES 
    /**
     * @param type: c'est le format à être compressé.
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