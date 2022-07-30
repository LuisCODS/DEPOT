package midterm;

public class CreditCard {
	
	
/*
 * 	1)	Quel est le(s) probl�me(s) dans l�impl�mentation de la classe creditcard.java? (1 points)
 * R: le probleme c'est = open/close, car � chaque fois qu'on va rajouter un nouveau type de carte
 * la classe doit �tre modifier. Aussi on a le probleme single responsability, car c'est ne pas sa responsabilit� de valider 
 * par soi meme.
 * 
 * */			
			
			
	public String cardNumber;
	public String date;
	public String cvv;
	public String type;
	
	public CreditCard(String cardNumber, String date, String cvv, String type) {
		this.cardNumber = cardNumber;
		this.date = date;
		this.cvv = cvv;
		this.type = type;
	}

	
	
	public boolean isValid()
	{
		boolean isValid=false;
		switch(type){
		
			case "visa":{
				if (cardNumber.startsWith("4")&&
						cardNumber.length()==15 )
					isValid=true;        
			}
			case "mastercard":{
				if (cardNumber.startsWith("3")&&
						cardNumber.length()==16 )
				        isValid=true;
				        }
		}
		return isValid;
	}
	

}
