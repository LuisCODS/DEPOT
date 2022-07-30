package compteBancaire;

import java.sql.Date;

import java.text.*;


public class Log implements Iobserver
{
        private static Log uniqueInstance;// Stockage de l'unique instance de cette classe.
        private String log;// Chaine de caract�res repr�sentant les messages de log.
        
        // Constructeur en priv� (donc inaccessible � l'ext�rieur de la classe).
        private Log()
        {
                log = new String();
        }
        
        // M�thode statique qui sert de pseudo-constructeur (utilisation du mot clef "synchronized" pour le multithread).
        public static synchronized Log getInstance()
        {
                if(uniqueInstance==null)
                {
                        uniqueInstance = new Log();
                }
                return uniqueInstance;
        }
        
        // M�thode qui permet d'ajouter les comptes desactives
        public void NotifyMe(CompteBancaire c)
        {
                // On ajoute �galement la date du message.
                Date d = new Date(0);
                DateFormat dateFormat = new SimpleDateFormat("dd/MM/yy HH'h'mm");
                this.log+="["+dateFormat.format(d)+"] "+c.getNumero()+"est desactive"+"\n";
        }
        
        // M�thode qui retourne tous les messages de log.
        public String afficherLog()
        {
                return log;
        }

		
}
