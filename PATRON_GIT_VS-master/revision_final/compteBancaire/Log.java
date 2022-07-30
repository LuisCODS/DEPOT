package compteBancaire;

import java.sql.Date;

import java.text.*;


public class Log implements Iobserver
{
        private static Log uniqueInstance;// Stockage de l'unique instance de cette classe.
        private String log;// Chaine de caractères représentant les messages de log.
        
        // Constructeur en privé (donc inaccessible à l'extérieur de la classe).
        private Log()
        {
                log = new String();
        }
        
        // Méthode statique qui sert de pseudo-constructeur (utilisation du mot clef "synchronized" pour le multithread).
        public static synchronized Log getInstance()
        {
                if(uniqueInstance==null)
                {
                        uniqueInstance = new Log();
                }
                return uniqueInstance;
        }
        
        // Méthode qui permet d'ajouter les comptes desactives
        public void NotifyMe(CompteBancaire c)
        {
                // On ajoute également la date du message.
                Date d = new Date(0);
                DateFormat dateFormat = new SimpleDateFormat("dd/MM/yy HH'h'mm");
                this.log+="["+dateFormat.format(d)+"] "+c.getNumero()+"est desactive"+"\n";
        }
        
        // Méthode qui retourne tous les messages de log.
        public String afficherLog()
        {
                return log;
        }

		
}
