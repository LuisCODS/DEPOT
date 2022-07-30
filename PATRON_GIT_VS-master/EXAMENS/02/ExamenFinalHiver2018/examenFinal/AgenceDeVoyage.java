package examenFinal;  

  public class AgenceDeVoyage implements Observer{

	  BD bd = BD.getInstance(); 	

	@Override
	public void UpDate(Vol newVol) 	{
		if (newVol instanceof Vol)		
			this.Add(newVol);				
	}  		
  	public void Add(Vol  v)  	{
  		bd.Add(v);
  	}    	
  }
