package exemple1_comptebancaire;

import java.util.ArrayList;

public class CompteBancaire implements Isubject{
	private int numero;// Numéro du compte.
    private double solde;// Argent disponible sur le compte.
    ArrayList<Iobserver> allObservers;
    String message;
    
    
    
    // Constructeur d'un CompteBancaire à partir de son numéro.
    public CompteBancaire(int numero)
    
    {
    	allObservers = new ArrayList<Iobserver>();
    	    this.numero=numero;
            this.solde=0.0;
    }
    
    // Méthode qui permet de déposer de l'argent sur le compte.
    public void deposerArgent(double depot)
    
    {
    	message="Dépôt de "+depot+"$ sur le compte "+numero+".";
    	
            if(depot>0.0)
            {       
                    solde+=depot;// On ajoute la somme déposée au solde.
                    notifier();
            }
            
    }
    
    // Méthode qui permet de retirer de l'argent sur le compte.
    public void retirerArgent(double retrait)
    {
            if(retrait>0.0)
            {
                    if(solde>=retrait)
                    {
                            solde-=retrait;// On retranche la somme retirée au solde.
                            message="Retrait de "+retrait+"$ sur le compte "+numero+".";
                            notifier();
                    }
                    else
                    {
                    	message="/!\\ La banque n'autorise pas de découvert ("+numero+").";
                            notifier();
                    }
            }
            
    }

	@Override
	public void Subscribe(Iobserver o) {
		
		allObservers.add(o);
		
	}


	public void unsbscribe(Iobserver o) {
		allObservers.remove(o);
		
	}

	@Override
	public void notifier() {
		for(Iobserver o:allObservers)
		{
			o.NotifyMe(message);
		}
		
	}

}
