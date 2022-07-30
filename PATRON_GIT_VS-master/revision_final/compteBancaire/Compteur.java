package compteBancaire;


import java.time.LocalDate;
//import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
//import java.util.Calendar;

public class Compteur implements Iobserver,Isubject{

	
	CompteBancaire c;
	LocalDate localDate;
	ArrayList<Iobserver> allObservers;
    String message;
	
	
	

	public Compteur(CompteBancaire c) {

	//initiliser local date par la date d'aujourdhui
		this.localDate =dateToday();
		this.c = c;
		allObservers = new ArrayList<Iobserver>();
		
		this.Subscribe(Log.getInstance());
		this.Subscribe(Controleur.getinstance());

	}


	private LocalDate dateToday() {
		
		return LocalDate.now();
	}
	
	
	public CompteBancaire getC() {
		return c;
	}


	public void setC(CompteBancaire c) {
		this.c = c;
	}


	public LocalDate getlocalDate() {
		return localDate;
	}


	public void setlocalDate(LocalDate newLocalDate) {
		this.localDate = newLocalDate;
	}


	public void NotifyMe(CompteBancaire c) {
		
		if(c==this.c)
		{
		
		//recommencer a partir de la date d'aujourdhui
		setlocalDate(LocalDate.now());
		//LocalDate yearLater = this.localDate.plusYears ( 1 );
		
		
		
		//while(this.localDate.isBefore(yearLater))
		//{
		//	System.out.println("le compte est toujours actif");
		//}
		
		
		notifier() ;
		}
				
		}
		public void notifier() {
			for(Iobserver o:allObservers)
			{
				o.NotifyMe(this.c);
			}
			
		
		
		
		
		}


		
		public void Subscribe(Iobserver o) {
			
			this.allObservers.add(o);
		}


		
		public void unsbscribe(Iobserver o) {
			this.allObservers.remove(o);
			
		}

	

}
