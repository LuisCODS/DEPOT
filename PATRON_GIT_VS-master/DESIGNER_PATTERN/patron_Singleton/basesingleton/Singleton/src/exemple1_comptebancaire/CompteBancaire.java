package exemple1_comptebancaire;

import java.util.ArrayList;

public class CompteBancaire implements Isubject{
	private int numero;// Num�ro du compte.
    private double solde;// Argent disponible sur le compte.
    ArrayList<Iobserver> allObservers;
    String message;
    
    
    
    // Constructeur d'un CompteBancaire � partir de son num�ro.
    public CompteBancaire(int numero)
    
    {
    	allObservers = new ArrayList<Iobserver>();
    	    this.numero=numero;
            this.solde=0.0;
    }
    
    // M�thode qui permet de d�poser de l'argent sur le compte.
    public void deposerArgent(double depot)
    
    {
    	message="D�p�t de "+depot+"$ sur le compte "+numero+".";
    	
            if(depot>0.0)
            {       
                    solde+=depot;// On ajoute la somme d�pos�e au solde.
                    notifier();
            }
            
    }
    
    // M�thode qui permet de retirer de l'argent sur le compte.
    public void retirerArgent(double retrait)
    {
            if(retrait>0.0)
            {
                    if(solde>=retrait)
                    {
                            solde-=retrait;// On retranche la somme retir�e au solde.
                            message="Retrait de "+retrait+"$ sur le compte "+numero+".";
                            notifier();
                    }
                    else
                    {
                    	message="/!\\ La banque n'autorise pas de d�couvert ("+numero+").";
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
