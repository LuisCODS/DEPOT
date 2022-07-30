package exemple1_comptebancaire;

import java.sql.Date;

import java.text.*;


public class log implements Iobserver
{
        private static log uniqueInstance;// Stockage de l'unique instance de cette classe.
        private String log;// Chaine de caractères représentant les messages de log.
        
        // Constructeur en privé (donc inaccessible à l'extérieur de la classe).
        private log()
        {
                log = new String();
        }
        
        // Méthode statique qui sert de pseudo-constructeur (utilisation du mot clef "synchronized" pour le multithread).
        public static synchronized log getInstance()
        {
                if(uniqueInstance==null)
                {
                        uniqueInstance = new log();
                }
                return uniqueInstance;
        }
        
        // Méthode qui permet d'ajouter un message de log.
        public void NotifyMe(String log)
        {
                // On ajoute également la date du message.
                Date d = new Date(0);
                DateFormat dateFormat = new SimpleDateFormat("dd/MM/yy HH'h'mm");
                this.log+="["+dateFormat.format(d)+"] "+log+"\n";
        }
        
        // Méthode qui retourne tous les messages de log.
        public String afficherLog()
        {
                return log;
        }

		
}
