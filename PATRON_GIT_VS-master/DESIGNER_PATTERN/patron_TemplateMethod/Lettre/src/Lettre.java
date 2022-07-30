package Lettre.src;

import java.text.SimpleDateFormat;
import java.util.Date;


public abstract class Lettre {

	String corps ="";
	ToFrom toFrom = null;
	
	public Lettre(String corps,ToFrom toFrom)
	{		
		setCorps(corps);
		setToFrom(toFrom);
	}
		
	public final void Pint()
	{
		AfficherDate();
		Appellation();
		Corps();
		Formulefinale();
		AfficherNom();
	}

	//====================== COMMUN ======================
	private  void AfficherDate(){		
		SimpleDateFormat dt1 = new SimpleDateFormat("yyyy-mm-dd");
		Date date = new Date();
		System.out.println("Montréal, le "+ dt1.format(date) +"\n");
	}
	private  void Corps() 	{		
		System.out.println(corps +"\n");		
	}
	private  void AfficherNom() {		
		System.out.println(this.toFrom.from);
	}	
	//====================== SPECIFIQUE ======================	
	public abstract void Appellation();
	public abstract void Formulefinale();	
	
	//====================== GET & SET ======================
	public String getCorps() {
		return corps;
	}
	public ToFrom getToFrom() {
		return toFrom;
	}
	public void setCorps(String corps) {
		this.corps = corps;
	}
	public void setToFrom(ToFrom toFrom) {
		this.toFrom = toFrom;
	}		
}
