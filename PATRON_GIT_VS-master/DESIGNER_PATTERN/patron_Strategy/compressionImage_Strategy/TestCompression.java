package compressionImage_Strategy;

public class TestCompression {

	public static void main(String[] args) {

		
		Image_Contexte tif = new Image_Contexte(new StrategyImageTIF());
		tif.Compression();
		System.out.println("______________________________");	
		
		Image_Contexte gif = new Image_Contexte(new StrategyImageGIF());
		gif.Compression();
		System.out.println("______________________________");	
		
		Image_Contexte jpg = new Image_Contexte(new StrategyImageJPGeg());
		jpg.Compression();
		System.out.println("______________________________");	
		
	}
}
