package exemple1_comptebancaire;

import java.sql.Date;

import java.text.*;


public class log implements Iobserver
{
        private static log uniqueInstance;// Stockage de l'unique instance de cette classe.
        private String log;// Chaine de caract�res repr�sentant les messages de log.
        
        // Constructeur en priv� (donc inaccessible � l'ext�rieur de la classe).
        private log()
        {
                log = new String();
        }
        
        // M�thode statique qui sert de pseudo-constructeur (utilisation du mot clef "synchronized" pour le multithread).
        public static synchronized log getInstance()
        {
                if(uniqueInstance==null)
                {
                        uniqueInstance = new log();
                }
                return uniqueInstance;
        }
        
        // M�thode qui permet d'ajouter un message de log.
        public void NotifyMe(String log)
        {
                // On ajoute �galement la date du message.
                Date d = new Date(0);
                DateFormat dateFormat = new SimpleDateFormat("dd/MM/yy HH'h'mm");
                this.log+="["+dateFormat.format(d)+"] "+log+"\n";
        }
        
        // M�thode qui retourne tous les messages de log.
        public String afficherLog()
        {
                return log;
        }

		
}
