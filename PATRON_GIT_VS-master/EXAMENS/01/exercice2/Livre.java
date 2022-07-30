package exercice2;

class Livre{
	
	
	//ICI ON A UN PROBLEME (OPEN/CLOSE), CAR À CHAQUE FOIS QU'ON SOUHAITE RAJOUTER UN NOUVEAU GENRE, L'INSTANCE THIS(Livre) EST MODIFIÉE.
	//CE QUI BRISE LE PRINCIPE "CLOSE"
	String name;
	StrategyGenre StrategyGenre;
	//String genre;

	
	public Livre(StrategyGenre g)
	{
		this.StrategyGenre = g;
	}
	
	
	
	public void getGenre()
	{
		StrategyGenre.GetGenre(this);
	}
	
	
/*	void  getGenre()
	{
		String result=null;
		
		switch(genre) 
		{
	
			case  "ScienceFictionNovel":  result= "Science Fiction";
				break;
			case "CrimeNovel": result="Crime";
				break;
			default:
				break;
		}
		System.out.println(result);
	}*/
}

